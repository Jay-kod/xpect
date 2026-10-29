<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchOdds;
use App\Models\SyncRun;
use App\Services\DataSources\FootballDataCsv\CsvBackfillAdapter;
use App\Services\Teams\TeamResolver;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class ImportOdds extends Command
{
    protected $signature = 'odds:import {competition? : Competition slug or omit for all} {--seasons= : Comma-separated seasons}';
    protected $description = 'Import bookmaker odds and compute overround-free implied probabilities for matches';

    public function handle(TeamResolver $teamResolver, CsvBackfillAdapter $csvAdapter): int
    {
        $compSlug = $this->argument('competition');
        $competitions = $compSlug
            ? Competition::where('slug', $compSlug)->get()
            : Competition::where('is_active', true)->get();

        if ($competitions->isEmpty()) {
            $this->error("No active competition matching [{$compSlug}].");
            return self::FAILURE;
        }

        $seasonsArg = $this->option('seasons');
        $seasons = $seasonsArg
            ? array_map('intval', explode(',', (string) $seasonsArg))
            : [2020, 2021, 2022, 2023, 2024];

        $this->info("Importing odds for " . $competitions->count() . " competition(s) across seasons: " . implode(', ', $seasons));

        $totalOddsRows = 0;

        foreach ($competitions as $competition) {
            $div = $competition->source_ids['csv'] ?? null;
            if (!$div) {
                $this->warn("Skipping {$competition->name} (no CSV code).");
                continue;
            }

            foreach ($seasons as $season) {
                $startTwoDigits = substr((string) $season, -2);
                $endTwoDigits = str_pad((string) (((int) $startTwoDigits + 1) % 100), 2, '0', STR_PAD_LEFT);
                $seasonCode = $startTwoDigits . $endTwoDigits;

                $localFixture = base_path("tests/Fixtures/csv/{$div}_{$seasonCode}.csv");
                if (!file_exists($localFixture)) {
                    continue;
                }

                $clean = preg_replace('/^\xEF\xBB\xBF/', '', trim((string) file_get_contents($localFixture)));
                $lines = explode("\n", $clean);
                if (count($lines) < 2) {
                    continue;
                }

                $headerRow = str_getcsv(array_shift($lines));
                $headers = array_map(trim(...), $headerRow);

                $seasonOddsCount = 0;

                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') continue;

                    $row = str_getcsv($line);
                    if (count($row) < count($headers)) continue;

                    $data = array_combine($headers, array_slice($row, 0, count($headers)));

                    $homeName = trim($data['HomeTeam'] ?? '');
                    $awayName = trim($data['AwayTeam'] ?? '');
                    if ($homeName === '' || $awayName === '') continue;

                    $homeId = $teamResolver->resolve('csv', $homeName);
                    $awayId = $teamResolver->resolve('csv', $awayName);
                    if (!$homeId || !$awayId) continue;

                    $match = FootballMatch::where('competition_id', $competition->id)
                        ->where('season', $season)
                        ->where('home_team_id', $homeId)
                        ->where('away_team_id', $awayId)
                        ->first();

                    if (!$match) continue;

                    $csvAdapter->parseAndPersistOdds($match, $data);
                    $seasonOddsCount++;
                }

                $totalOddsRows += $seasonOddsCount;
                $this->line("  [{$competition->name}] Season {$season}: processed odds for {$seasonOddsCount} matches.");
            }
        }

        $this->info("Completed odds import. Processed matches with odds: {$totalOddsRows}. Total match_odds in DB: " . MatchOdds::count());

        return self::SUCCESS;
    }
}
