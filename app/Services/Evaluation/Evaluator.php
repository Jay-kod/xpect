<?php

declare(strict_types=1);

namespace App\Services\Evaluation;

use App\Models\Competition;
use App\Models\Prediction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class Evaluator
{
    public function __construct(
        protected readonly Metrics $metrics,
        protected readonly Baselines $baselines
    ) {
    }

    /**
     * Evaluate a collection of predictions against actual match results.
     * Evaluates both model and all three baselines.
     */
    public function evaluate(Collection $predictions, ?Competition $competition = null): array
    {
        $modelItems = [];
        $alwaysHomeItems = [];
        $uniformItems = [];
        $baseRateItems = [];
        $b365Items = [];
        $marketAvgItems = [];

        $calibrationEvents = [];

        $uniformP = $this->baselines->uniform();
        $alwaysHomeP = $this->baselines->alwaysHome();

        foreach ($predictions as $pred) {
            $match = $pred->match;
            if (!$match || $match->status !== 'finished' || $match->home_goals === null || $match->away_goals === null) {
                continue;
            }

            // Actual outcome
            $actual = match (true) {
                $match->home_goals > $match->away_goals => 'H',
                $match->home_goals < $match->away_goals => 'A',
                default => 'D',
            };

            // Model
            $pH = (float) $pred->p_home;
            $pD = (float) $pred->p_draw;
            $pA = (float) $pred->p_away;

            $predicted = 'H';
            if ($pD > $pH && $pD > $pA) $predicted = 'D';
            elseif ($pA > $pH && $pA > $pD) $predicted = 'A';

            $modelItems[] = [
                'p_home' => $pH,
                'p_draw' => $pD,
                'p_away' => $pA,
                'predicted' => $predicted,
                'actual' => $actual,
            ];

            // Baselines
            $alwaysHomeItems[] = [
                'p_home' => $alwaysHomeP['p_home'],
                'p_draw' => $alwaysHomeP['p_draw'],
                'p_away' => $alwaysHomeP['p_away'],
                'predicted' => $alwaysHomeP['predicted'],
                'actual' => $actual,
            ];

            $uniformItems[] = [
                'p_home' => $uniformP['p_home'],
                'p_draw' => $uniformP['p_draw'],
                'p_away' => $uniformP['p_away'],
                'predicted' => $uniformP['predicted'],
                'actual' => $actual,
            ];

            // Historical base rate as of kickoff
            $comp = $competition ?? $match->competition;
            $histBaseRates = $this->baselines->historicalBaseRates($comp, CarbonImmutable::parse($match->kickoff_at));
            $baseRateItems[] = [
                'p_home' => $histBaseRates['p_home'],
                'p_draw' => $histBaseRates['p_draw'],
                'p_away' => $histBaseRates['p_away'],
                'predicted' => $histBaseRates['predicted'],
                'actual' => $actual,
            ];

            // Bookmaker odds benchmark (Bet365 & Market Average)
            $match->loadMissing('odds');
            $b365Odds = $match->odds->firstWhere('bookmaker', 'b365');
            if ($b365Odds) {
                $bH = (float) $b365Odds->implied_home_prob;
                $bD = (float) $b365Odds->implied_draw_prob;
                $bA = (float) $b365Odds->implied_away_prob;
                $bPred = 'H';
                if ($bD > $bH && $bD > $bA) $bPred = 'D';
                elseif ($bA > $bH && $bA > $bD) $bPred = 'A';

                $b365Items[] = [
                    'p_home' => $bH,
                    'p_draw' => $bD,
                    'p_away' => $bA,
                    'predicted' => $bPred,
                    'actual' => $actual,
                ];
            }

            $avgOdds = $match->odds->firstWhere('bookmaker', 'market_avg');
            if ($avgOdds) {
                $aH = (float) $avgOdds->implied_home_prob;
                $aD = (float) $avgOdds->implied_draw_prob;
                $aA = (float) $avgOdds->implied_away_prob;
                $aPred = 'H';
                if ($aD > $aH && $aD > $aA) $aPred = 'D';
                elseif ($aA > $aH && $aA > $aD) $aPred = 'A';

                $marketAvgItems[] = [
                    'p_home' => $aH,
                    'p_draw' => $aD,
                    'p_away' => $aA,
                    'predicted' => $aPred,
                    'actual' => $actual,
                ];
            }

            // Calibration events (binary event per outcome)
            $calibrationEvents[] = ['prob' => $pH, 'occurred' => $actual === 'H'];
            $calibrationEvents[] = ['prob' => $pD, 'occurred' => $actual === 'D'];
            $calibrationEvents[] = ['prob' => $pA, 'occurred' => $actual === 'A'];
        }

        $sampleSize = count($modelItems);
        if ($sampleSize === 0) {
            return ['sample_size' => 0];
        }

        $modelAccuracy = $this->metrics->accuracy($modelItems);
        $modelLogLoss = $this->metrics->logLoss($modelItems);
        $modelBrier = $this->metrics->brier($modelItems);

        $alwaysHomeAcc = $this->metrics->accuracy($alwaysHomeItems);
        $alwaysHomeLogLoss = $this->metrics->logLoss($alwaysHomeItems);
        $alwaysHomeBrier = $this->metrics->brier($alwaysHomeItems);

        $uniformAcc = $this->metrics->accuracy($uniformItems);
        $uniformLogLoss = $this->metrics->logLoss($uniformItems);
        $uniformBrier = $this->metrics->brier($uniformItems);

        $baseRateAcc = $this->metrics->accuracy($baseRateItems);
        $baseRateLogLoss = $this->metrics->logLoss($baseRateItems);
        $baseRateBrier = $this->metrics->brier($baseRateItems);

        $calibrationTable = $this->metrics->calibration($calibrationEvents);

        $beatsAlwaysHome = ($modelBrier < $alwaysHomeBrier) && ($modelLogLoss < $alwaysHomeLogLoss);
        $beatsUniform = ($modelBrier < $uniformBrier) && ($modelLogLoss < $uniformLogLoss);
        $beatsHistoricalBaseRate = ($modelBrier < $baseRateBrier) && ($modelLogLoss < $baseRateLogLoss);

        return [
            'sample_size' => $sampleSize,
            'model' => [
                'accuracy' => round($modelAccuracy, 4),
                'log_loss' => round($modelLogLoss, 4),
                'brier' => round($modelBrier, 4),
            ],
            'baselines' => [
                'always_home' => [
                    'accuracy' => round($alwaysHomeAcc, 4),
                    'log_loss' => round($alwaysHomeLogLoss, 4),
                    'brier' => round($alwaysHomeBrier, 4),
                ],
                'uniform' => [
                    'accuracy' => round($uniformAcc, 4),
                    'log_loss' => round($uniformLogLoss, 4),
                    'brier' => round($uniformBrier, 4),
                ],
                'historical_base_rates' => [
                    'accuracy' => round($baseRateAcc, 4),
                    'log_loss' => round($baseRateLogLoss, 4),
                    'brier' => round($baseRateBrier, 4),
                ],
            ],
            'comparison' => [
                'beats_always_home' => $beatsAlwaysHome,
                'beats_uniform' => $beatsUniform,
                'beats_historical_base_rate' => $beatsHistoricalBaseRate,
                'all_baselines_beaten' => $beatsAlwaysHome && $beatsUniform && $beatsHistoricalBaseRate,
            ],
            'benchmarks' => [
                'bookmaker_b365' => !empty($b365Items) ? [
                    'accuracy' => round($this->metrics->accuracy($b365Items), 4),
                    'log_loss' => round($this->metrics->logLoss($b365Items), 4),
                    'brier' => round($this->metrics->brier($b365Items), 4),
                    'sample_size' => count($b365Items),
                ] : null,
                'bookmaker_market_avg' => !empty($marketAvgItems) ? [
                    'accuracy' => round($this->metrics->accuracy($marketAvgItems), 4),
                    'log_loss' => round($this->metrics->logLoss($marketAvgItems), 4),
                    'brier' => round($this->metrics->brier($marketAvgItems), 4),
                    'sample_size' => count($marketAvgItems),
                ] : null,
            ],
            'calibration_table' => $calibrationTable,
        ];
    }
}

