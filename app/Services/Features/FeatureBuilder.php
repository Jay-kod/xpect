<?php

declare(strict_types=1);

namespace App\Services\Features;

use App\Data\TeamFeatures;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;

class FeatureBuilder
{
    /**
     * Build leakage-safe team features strictly as of a cutoff timestamp.
     * All queries strictly require kickoff_at < asOf and status = 'finished'.
     */
    public function build(Team $team, CarbonImmutable $asOf): TeamFeatures
    {
        $windowSize = (int) config('football.features.window_matches', 20);
        $halfLifeDays = (float) config('football.features.half_life_days', 60.0);
        $k = (float) config('football.features.shrinkage_k', 3.0);

        // 1. Calculate League Averages strictly before asOf
        $leagueAverages = $this->calculateLeagueAverages($asOf);
        $avgHomeGoals = $leagueAverages['home_goals'];
        $avgAwayGoals = $leagueAverages['away_goals'];

        // 2. Fetch team's last N finished matches strictly before asOf
        $matches = FootballMatch::with('stats')
            ->where('status', 'finished')
            ->where('kickoff_at', '<', $asOf)
            ->where(function ($q) use ($team) {
                $q->where('home_team_id', $team->id)
                  ->orWhere('away_team_id', $team->id);
            })
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->orderBy('kickoff_at', 'desc')
            ->limit($windowSize)
            ->get();

        $sampleCount = $matches->count();
        $xgCount = 0;

        // Accumulators for weighted home & away stats (actual goals)
        $sumWHomeScored = 0.0;
        $sumWHomeConceded = 0.0;
        $sumWHome = 0.0;

        $sumWAwayScored = 0.0;
        $sumWAwayConceded = 0.0;
        $sumWAway = 0.0;

        // Accumulators for weighted home & away stats (expected goals xG)
        $sumWHomeXgScored = 0.0;
        $sumWHomeXgConceded = 0.0;
        $sumWAwayXgScored = 0.0;
        $sumWAwayXgConceded = 0.0;

        foreach ($matches as $m) {
            $daysAgo = max(0.0, ($asOf->timestamp - $m->kickoff_at->timestamp) / 86400.0);
            $weight = pow(0.5, $daysAgo / $halfLifeDays);

            $teamStat = $m->stats->firstWhere('team_id', $team->id);
            if ($teamStat && $teamStat->xg !== null) {
                $xgCount++;
                $xgScored = (float) $teamStat->xg;
                $xgConceded = (float) $teamStat->xga;
            } else {
                // Graceful fallback to actual goals
                $xgScored = (float) ($m->home_team_id === $team->id ? $m->home_goals : $m->away_goals);
                $xgConceded = (float) ($m->home_team_id === $team->id ? $m->away_goals : $m->home_goals);
            }

            if ($m->home_team_id === $team->id) {
                $sumWHomeScored += $weight * (float) $m->home_goals;
                $sumWHomeConceded += $weight * (float) $m->away_goals;
                $sumWHomeXgScored += $weight * $xgScored;
                $sumWHomeXgConceded += $weight * $xgConceded;
                $sumWHome += $weight;
            } else {
                $sumWAwayScored += $weight * (float) $m->away_goals;
                $sumWAwayConceded += $weight * (float) $m->home_goals;
                $sumWAwayXgScored += $weight * $xgScored;
                $sumWAwayXgConceded += $weight * $xgConceded;
                $sumWAway += $weight;
            }
        }

        // Raw ratios relative to league average
        $rawHomeAttack = $sumWHome > 0 ? ($sumWHomeScored / $sumWHome) / $avgHomeGoals : 1.0;
        $rawHomeDefence = $sumWHome > 0 ? ($sumWHomeConceded / $sumWHome) / $avgAwayGoals : 1.0;

        $rawAwayAttack = $sumWAway > 0 ? ($sumWAwayScored / $sumWAway) / $avgAwayGoals : 1.0;
        $rawAwayDefence = $sumWAway > 0 ? ($sumWAwayConceded / $sumWAway) / $avgHomeGoals : 1.0;

        // Shrinkage toward 1.0: (sum_w * ratio + k) / (sum_w + k)
        $homeAttackStrength = ($sumWHome * $rawHomeAttack + $k) / ($sumWHome + $k);
        $homeDefenceStrength = ($sumWHome * $rawHomeDefence + $k) / ($sumWHome + $k);

        $awayAttackStrength = ($sumWAway * $rawAwayAttack + $k) / ($sumWAway + $k);
        $awayDefenceStrength = ($sumWAway * $rawAwayDefence + $k) / ($sumWAway + $k);

        // xG Strengths
        $rawHomeXgAttack = $sumWHome > 0 ? ($sumWHomeXgScored / $sumWHome) / $avgHomeGoals : 1.0;
        $rawHomeXgDefence = $sumWHome > 0 ? ($sumWHomeXgConceded / $sumWHome) / $avgAwayGoals : 1.0;

        $rawAwayXgAttack = $sumWAway > 0 ? ($sumWAwayXgScored / $sumWAway) / $avgAwayGoals : 1.0;
        $rawAwayXgDefence = $sumWAway > 0 ? ($sumWAwayXgConceded / $sumWAway) / $avgHomeGoals : 1.0;

        $homeXgAttackStrength = ($sumWHome * $rawHomeXgAttack + $k) / ($sumWHome + $k);
        $homeXgDefenceStrength = ($sumWHome * $rawHomeXgDefence + $k) / ($sumWHome + $k);

        $awayXgAttackStrength = ($sumWAway * $rawAwayXgAttack + $k) / ($sumWAway + $k);
        $awayXgDefenceStrength = ($sumWAway * $rawAwayXgDefence + $k) / ($sumWAway + $k);

        // Low confidence flag if small sample
        $lowConfidence = ($sampleCount < 5) || ($sumWHome + $sumWAway < 3.0);

        // Form points in last 5 matches
        $formPoints = 0;
        $last5 = $matches->take(5);
        foreach ($last5 as $m) {
            if ($m->home_team_id === $team->id) {
                if ($m->home_goals > $m->away_goals) $formPoints += 3;
                elseif ($m->home_goals === $m->away_goals) $formPoints += 1;
            } else {
                if ($m->away_goals > $m->home_goals) $formPoints += 3;
                elseif ($m->away_goals === $m->home_goals) $formPoints += 1;
            }
        }

        // Rest days since previous finished match
        $mostRecent = $matches->first();
        $restDays = $mostRecent
            ? (int) floor(($asOf->timestamp - $mostRecent->kickoff_at->timestamp) / 86400)
            : null;

        return new TeamFeatures(
            teamId: $team->id,
            teamName: $team->name,
            asOf: $asOf,
            homeAttackStrength: $homeAttackStrength,
            homeDefenceStrength: $homeDefenceStrength,
            awayAttackStrength: $awayAttackStrength,
            awayDefenceStrength: $awayDefenceStrength,
            matchesSampleCount: $sampleCount,
            lowConfidence: $lowConfidence,
            formPointsLast5: $formPoints,
            restDays: $restDays,
            leagueAvgHomeGoals: $avgHomeGoals,
            leagueAvgAwayGoals: $avgAwayGoals,
            homeXgAttackStrength: $homeXgAttackStrength,
            homeXgDefenceStrength: $homeXgDefenceStrength,
            awayXgAttackStrength: $awayXgAttackStrength,
            awayXgDefenceStrength: $awayXgDefenceStrength,
            xgSampleCount: $xgCount,
        );
    }

    /**
     * Compute league average home and away goals strictly before asOf.
     */
    protected function calculateLeagueAverages(CarbonImmutable $asOf): array
    {
        // Query last 200 league matches prior to asOf
        $recent = FootballMatch::where('status', 'finished')
            ->where('kickoff_at', '<', $asOf)
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->orderBy('kickoff_at', 'desc')
            ->limit(200)
            ->get();

        if ($recent->isEmpty()) {
            return [
                'home_goals' => 1.50,
                'away_goals' => 1.20,
            ];
        }

        $homeGoals = (float) $recent->avg('home_goals');
        $awayGoals = (float) $recent->avg('away_goals');

        return [
            'home_goals' => max($homeGoals, 0.5),
            'away_goals' => max($awayGoals, 0.5),
        ];
    }
}

