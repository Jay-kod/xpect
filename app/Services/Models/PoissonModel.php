<?php

declare(strict_types=1);

namespace App\Services\Models;

use App\Data\Probabilities;
use App\Data\TeamFeatures;

class PoissonModel
{
    public const MODEL_VERSION = 'v1.2.1-h2h';
    public const MODEL_V1_POISSON = 'v1.0.0-poisson';
    public const MODEL_V1_XG = 'v1.1.0-xg';
    public const MODEL_V1_DIXON_COLES = 'v1.2.0-dixon-coles';
    public const MODEL_V1_H2H = 'v1.2.1-h2h';
    public const MODEL_V1_REST = 'v1.2.2-rest';

    public function __construct(
        protected float $dixonColesRho = -0.06
    ) {
    }

    /**
     * Compute win/draw/loss probabilities and expected goals using independent Poisson distributions
     * with optional Dixon-Coles low-score correction and Tier 3 variables.
     */
    public function predict(
        TeamFeatures $homeFeatures,
        TeamFeatures $awayFeatures,
        string $modelVersion = self::MODEL_VERSION,
        ?float $h2hHomeModifier = null
    ): Probabilities {
        // Models leveraging expected goals (xG)
        $usesXg = in_array($modelVersion, [
            self::MODEL_V1_XG,
            self::MODEL_V1_DIXON_COLES,
            self::MODEL_V1_H2H,
            self::MODEL_V1_REST,
        ], true);

        if ($usesXg) {
            $alpha = 0.70; // 70% sustainable xG chance creation, 30% finishing conversion
            $homeAttack = $alpha * $homeFeatures->homeXgAttackStrength + (1.0 - $alpha) * $homeFeatures->homeAttackStrength;
            $awayDefence = $alpha * $awayFeatures->awayXgDefenceStrength + (1.0 - $alpha) * $awayFeatures->awayDefenceStrength;
            $awayAttack = $alpha * $awayFeatures->awayXgAttackStrength + (1.0 - $alpha) * $awayFeatures->awayAttackStrength;
            $homeDefence = $alpha * $homeFeatures->homeXgDefenceStrength + (1.0 - $alpha) * $homeFeatures->homeDefenceStrength;
        } else {
            $homeAttack = $homeFeatures->homeAttackStrength;
            $awayDefence = $awayFeatures->awayDefenceStrength;
            $awayAttack = $awayFeatures->awayAttackStrength;
            $homeDefence = $homeFeatures->homeDefenceStrength;
        }

        // 1. Calculate lambdas (expected goals)
        $lambdaHome = $homeAttack * $awayDefence * $homeFeatures->leagueAvgHomeGoals;
        $lambdaAway = $awayAttack * $homeDefence * $homeFeatures->leagueAvgAwayGoals;

        // Tier 3 variable: Head-to-Head adjustment
        if ($modelVersion === self::MODEL_V1_H2H && $h2hHomeModifier !== null) {
            $lambdaHome *= $h2hHomeModifier;
            $lambdaAway /= $h2hHomeModifier;
        }

        // Tier 3 variable: Rest days differential (fatigue / fixture congestion)
        if ($modelVersion === self::MODEL_V1_REST && $homeFeatures->restDays !== null && $awayFeatures->restDays !== null) {
            $diff = $homeFeatures->restDays - $awayFeatures->restDays;
            if ($diff >= 3) {
                // Home significantly more rested than Away
                $lambdaHome *= 1.04;
                $lambdaAway *= 0.96;
            } elseif ($diff <= -3) {
                // Away significantly more rested than Home
                $lambdaHome *= 0.96;
                $lambdaAway *= 1.04;
            }
        }

        // Floor lambdas to reasonable football range
        $lambdaHome = max(0.2, min($lambdaHome, 5.0));
        $lambdaAway = max(0.2, min($lambdaAway, 5.0));

        // 2. Compute 0..8 probability distributions
        $maxGoals = 8;
        $homeProbs = [];
        $awayProbs = [];

        for ($i = 0; $i <= $maxGoals; $i++) {
            $homeProbs[$i] = $this->poissonProbability($lambdaHome, $i);
            $awayProbs[$i] = $this->poissonProbability($lambdaAway, $i);
        }

        // 3. Score Grid (0..8 x 0..8) with optional Dixon-Coles adjustment
        $scoreGrid = [];
        $gridSum = 0.0;
        $allScorelines = [];
        $applyDixonColes = in_array($modelVersion, [
            self::MODEL_V1_DIXON_COLES,
            self::MODEL_V1_H2H,
            self::MODEL_V1_REST,
        ], true);

        $rho = (function_exists('config') && function_exists('app') && app()->has('config'))
            ? (float) config('football.models.dixon_coles_rho', $this->dixonColesRho)
            : $this->dixonColesRho;

        for ($h = 0; $h <= $maxGoals; $h++) {
            for ($a = 0; $a <= $maxGoals; $a++) {
                $tau = 1.0;
                if ($applyDixonColes) {
                    if ($h === 0 && $a === 0) {
                        $tau = 1.0 - ($lambdaHome * $lambdaAway * $rho);
                    } elseif ($h === 0 && $a === 1) {
                        $tau = 1.0 + ($lambdaHome * $rho);
                    } elseif ($h === 1 && $a === 0) {
                        $tau = 1.0 + ($lambdaAway * $rho);
                    } elseif ($h === 1 && $a === 1) {
                        $tau = 1.0 - $rho;
                    }
                }

                $p = max(0.0, $tau * $homeProbs[$h] * $awayProbs[$a]);
                $scoreGrid[$h][$a] = $p;
                $gridSum += $p;
                $allScorelines[] = [
                    'home' => $h,
                    'away' => $a,
                    'score' => "{$h}-{$a}",
                    'prob' => $p,
                ];
            }
        }

        // 4. Normalize probabilities across grid sum
        $pHome = 0.0;
        $pDraw = 0.0;
        $pAway = 0.0;

        foreach ($scoreGrid as $h => $row) {
            foreach ($row as $a => $p) {
                $normP = $p / $gridSum;
                $scoreGrid[$h][$a] = $normP;

                if ($h > $a) {
                    $pHome += $normP;
                } elseif ($h < $a) {
                    $pAway += $normP;
                } else {
                    $pDraw += $normP;
                }
            }
        }

        // 5. Rank Top Scorelines
        usort($allScorelines, fn ($x, $y) => $y['prob'] <=> $x['prob']);
        $top5 = array_slice($allScorelines, 0, 5);
        $topScorelinesFormatted = array_map(function ($s) use ($gridSum) {
            return [
                'score' => $s['score'],
                'home' => $s['home'],
                'away' => $s['away'],
                'prob' => round($s['prob'] / $gridSum, 4),
            ];
        }, $top5);

        $mostLikely = $topScorelinesFormatted[0];

        return new Probabilities(
            pHome: $pHome,
            pDraw: $pDraw,
            pAway: $pAway,
            expHomeGoals: $lambdaHome,
            expAwayGoals: $lambdaAway,
            mostLikelyScore: $mostLikely,
            topScorelines: $topScorelinesFormatted,
            scoreMatrix: $scoreGrid,
        );
    }

    /**
     * Poisson probability P(X = k) = (lambda^k * e^-lambda) / k!
     */
    public function poissonProbability(float $lambda, int $k): float
    {
        return (pow($lambda, $k) * exp(-$lambda)) / (float) $this->factorial($k);
    }

    protected function factorial(int $n): int
    {
        if ($n <= 1) {
            return 1;
        }

        $fact = 1;
        for ($i = 2; $i <= $n; $i++) {
            $fact *= $i;
        }

        return $fact;
    }
}

