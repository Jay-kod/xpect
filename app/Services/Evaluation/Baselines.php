<?php

declare(strict_types=1);

namespace App\Services\Evaluation;

use App\Models\Competition;
use App\Models\FootballMatch;
use Carbon\CarbonImmutable;

class Baselines
{
    /**
     * @var array<string, array{p_home: float, p_draw: float, p_away: float, predicted: string}>
     */
    protected static array $baseRatesCache = [];

    public static function clearCache(): void
    {
        self::$baseRatesCache = [];
    }

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
        $cacheKey = "{$competition->id}_{$asOf->toIso8601String()}";
        if (isset(self::$baseRatesCache[$cacheKey])) {
            return self::$baseRatesCache[$cacheKey];
        }

        $row = FootballMatch::where('competition_id', $competition->id)
            ->where('status', 'finished')
            ->where('kickoff_at', '<', $asOf)
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN home_goals > away_goals THEN 1 ELSE 0 END) as home_wins,
                SUM(CASE WHEN home_goals < away_goals THEN 1 ELSE 0 END) as away_wins,
                SUM(CASE WHEN home_goals = away_goals THEN 1 ELSE 0 END) as draws
            ')
            ->first();

        $total = (int) ($row->total ?? 0);
        if ($total < 10) {
            // Sensible historical Premier League base rates fallback
            return self::$baseRatesCache[$cacheKey] = [
                'p_home' => 0.45,
                'p_draw' => 0.25,
                'p_away' => 0.30,
                'predicted' => 'H',
            ];
        }

        $homeWins = (int) ($row->home_wins ?? 0);
        $draws = (int) ($row->draws ?? 0);
        $awayWins = (int) ($row->away_wins ?? 0);

        $pH = $homeWins / $total;
        $pD = $draws / $total;
        $pA = $awayWins / $total;

        $predicted = 'H';
        if ($pD > $pH && $pD > $pA) $predicted = 'D';
        elseif ($pA > $pH && $pA > $pD) $predicted = 'A';

        return self::$baseRatesCache[$cacheKey] = [
            'p_home' => $pH,
            'p_draw' => $pD,
            'p_away' => $pA,
            'predicted' => $predicted,
        ];
    }
}

