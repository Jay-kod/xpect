<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\StandingsSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class RebuildStandings extends Command
{
    protected $signature = 'standings:rebuild {competition} {--season= : Season start year (optional)}';
    protected $description = 'Rebuild standings snapshots matchday-by-matchday from matches table (zero API cost)';

    public function handle(): int
    {
        $compSlug = (string) $this->argument('competition');
        $competition = Competition::where('slug', $compSlug)->first();

        if (!$competition) {
            $this->error("Competition [{$compSlug}] not found.");
            return self::FAILURE;
        }

        $seasonOption = $this->option('season');
        $seasonsQuery = FootballMatch::where('competition_id', $competition->id)
            ->where('status', 'finished');

        if ($seasonOption) {
            $seasonsQuery->where('season', (int) $seasonOption);
        }

        $seasons = $seasonsQuery->distinct()->pluck('season')->sort()->values();

        if ($seasons->isEmpty()) {
            $this->warn("No finished matches found for [{$competition->name}].");
            return self::SUCCESS;
        }

        $this->info("Rebuilding standings snapshots for [{$competition->name}] across " . $seasons->count() . " seasons...");

        $totalSnapshots = 0;

        foreach ($seasons as $season) {
            $this->line("--> Computing standings for season {$season}...");

            $matches = FootballMatch::where('competition_id', $competition->id)
                ->where('season', $season)
                ->where('status', 'finished')
                ->whereNotNull('home_goals')
                ->whereNotNull('away_goals')
                ->orderBy('kickoff_at', 'asc')
                ->get();

            if ($matches->isEmpty()) {
                continue;
            }

            // Group matches by matchday (or synthesize matchday rounds if null)
            $matchdays = $matches->pluck('matchday')->filter()->unique()->sort()->values();

            // If matchdays are not populated (e.g. from generic CSV), synthesize rounds of ~10 matches
            if ($matchdays->isEmpty()) {
                $round = 1;
                $count = 0;
                foreach ($matches as $m) {
                    $m->matchday = $round;
                    $count++;
                    if ($count % 10 === 0) {
                        $round++;
                    }
                }
                $matchdays = collect(range(1, $round));
            }

            foreach ($matchdays as $md) {
                // All matches up to and including this matchday
                $subMatches = $matches->filter(fn ($m) => ($m->matchday ?? 0) <= $md);
                if ($subMatches->isEmpty()) {
                    continue;
                }

                $asOf = $subMatches->max('kickoff_at') ?: CarbonImmutable::now('UTC');

                // Compute team records
                $table = [];
                foreach ($subMatches as $m) {
                    $hId = $m->home_team_id;
                    $aId = $m->away_team_id;

                    if (!isset($table[$hId])) {
                        $table[$hId] = ['team_id' => $hId, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'points' => 0];
                    }
                    if (!isset($table[$aId])) {
                        $table[$aId] = ['team_id' => $aId, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'points' => 0];
                    }

                    $table[$hId]['played']++;
                    $table[$aId]['played']++;

                    $table[$hId]['goals_for'] += $m->home_goals;
                    $table[$hId]['goals_against'] += $m->away_goals;
                    $table[$aId]['goals_for'] += $m->away_goals;
                    $table[$aId]['goals_against'] += $m->home_goals;

                    if ($m->home_goals > $m->away_goals) {
                        $table[$hId]['won']++;
                        $table[$hId]['points'] += 3;
                        $table[$aId]['lost']++;
                    } elseif ($m->home_goals < $m->away_goals) {
                        $table[$aId]['won']++;
                        $table[$aId]['points'] += 3;
                        $table[$hId]['lost']++;
                    } else {
                        $table[$hId]['drawn']++;
                        $table[$hId]['points'] += 1;
                        $table[$aId]['drawn']++;
                        $table[$aId]['points'] += 1;
                    }
                }

                // Sort: Points DESC, Goal Diff DESC, Goals For DESC, Team ID ASC
                uasort($table, function ($a, $b) {
                    if ($a['points'] !== $b['points']) {
                        return $b['points'] <=> $a['points'];
                    }
                    $gdA = $a['goals_for'] - $a['goals_against'];
                    $gdB = $b['goals_for'] - $b['goals_against'];
                    if ($gdA !== $gdB) {
                        return $gdB <=> $gdA;
                    }
                    if ($a['goals_for'] !== $b['goals_for']) {
                        return $b['goals_for'] <=> $a['goals_for'];
                    }
                    return $a['team_id'] <=> $b['team_id'];
                });

                $position = 1;
                foreach ($table as $row) {
                    StandingsSnapshot::updateOrCreate(
                        [
                            'competition_id' => $competition->id,
                            'season' => $season,
                            'matchday' => $md,
                            'team_id' => $row['team_id'],
                        ],
                        [
                            'position' => $position,
                            'played' => $row['played'],
                            'won' => $row['won'],
                            'drawn' => $row['drawn'],
                            'lost' => $row['lost'],
                            'goals_for' => $row['goals_for'],
                            'goals_against' => $row['goals_against'],
                            'points' => $row['points'],
                            'as_of' => $asOf,
                            'source_api_call_id' => null,
                        ]
                    );

                    $position++;
                    $totalSnapshots++;
                }
            }
        }

        $this->info("Standings rebuild complete! {$totalSnapshots} snapshot rows generated.");

        return self::SUCCESS;
    }
}

