<?php

declare(strict_types=1);

namespace App\Services\Evaluation;

use App\Models\Competition;
use App\Models\FootballMatch;
use Carbon\CarbonImmutable;

class Baselines
{
    /**
     * Always Home baseline: Predict 100% Home Win (smoothed slightly for numeric log loss).
     */
    public function alwaysHome(): array
    {
        return [
            'p_home' => 0.9998,
            'p_draw' => 0.0001,
            'p_away' => 0.0001,
            'predicted' => 'H',
        ];
    }

    /**
     * Uniform baseline: 1/3 probability for each outcome.
     */
    public function uniform(): array
    {
        $third = 1.0 / 3.0;
        return [
            'p_home' => $third,
            'p_draw' => $third,
            'p_away' => $third,
            'predicted' => 'H',
        ];
    }

    /**
     * Historical base rates: Calculated strictly from matches before asOf.
     */
    public function historicalBaseRates(Competition $competition, CarbonImmutable $asOf): array
    {
        $matches = FootballMatch::where('competition_id', $competition->id)
            ->where('status', 'finished')
            ->where('kickoff_at', '<', $asOf)
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->get();

        if ($matches->count() < 10) {
            // Sensible historical Premier League base rates fallback
            return [
                'p_home' => 0.45,
                'p_draw' => 0.25,
                'p_away' => 0.30,
                'predicted' => 'H',
            ];
        }

        $total = $matches->count();
        $homeWins = 0;
        $draws = 0;
        $awayWins = 0;

        foreach ($matches as $m) {
            if ($m->home_goals > $m->away_goals) {
                $homeWins++;
            } elseif ($m->home_goals < $m->away_goals) {
                $awayWins++;
            } else {
                $draws++;
            }
        }

        $pH = $homeWins / $total;
        $pD = $draws / $total;
        $pA = $awayWins / $total;

        $predicted = 'H';
        if ($pD > $pH && $pD > $pA) $predicted = 'D';
        elseif ($pA > $pH && $pA > $pD) $predicted = 'A';

        return [
            'p_home' => $pH,
            'p_draw' => $pD,
            'p_away' => $pA,
            'predicted' => $predicted,
        ];
    }
}

