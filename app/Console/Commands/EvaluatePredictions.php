<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Models\PredictionEvaluation;
use App\Services\Evaluation\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class EvaluatePredictions extends Command
{
    protected $signature = 'evaluate:predictions {--include-backtest : Also evaluate predictions marked as is_backtest} {--limit= : Limit the number of predictions to evaluate}';
    protected $description = 'Evaluate finished match predictions against actual outcomes (computing log loss and Brier score)';

    public function handle(Metrics $metrics): int
    {
        $includeBacktest = (bool) $this->option('include-backtest');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $query = Prediction::query()
            ->doesntHave('evaluation')
            ->whereHas('match', function ($q) {
                $q->where('status', 'finished')
                    ->whereNotNull('home_goals')
                    ->whereNotNull('away_goals');
            })
            ->with(['match.homeTeam', 'match.awayTeam']);

        if (!$includeBacktest) {
            $query->where('is_backtest', false);
        }

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $predictions = $query->get();

        if ($predictions->isEmpty()) {
            $this->info('No unevaluated predictions for finished matches found.');
            return self::SUCCESS;
        }

        $this->info("Found {$predictions->count()} unevaluated prediction(s). Evaluating against actual results...");

        $evaluatedCount = 0;
        $correctCount = 0;
        $totalLogLoss = 0.0;
        $totalBrier = 0.0;

        $tableRows = [];

        foreach ($predictions as $pred) {
            $match = $pred->match;
            if (!$match || $match->home_goals === null || $match->away_goals === null) {
                continue;
            }

            $actualOutcome = match (true) {
                $match->home_goals > $match->away_goals => 'H',
                $match->home_goals < $match->away_goals => 'A',
                default => 'D',
            };

            $pH = (float) $pred->p_home;
            $pD = (float) $pred->p_draw;
            $pA = (float) $pred->p_away;

            $predictedOutcome = 'H';
            if ($pD > $pH && $pD > $pA) {
                $predictedOutcome = 'D';
            } elseif ($pA > $pH && $pA > $pD) {
                $predictedOutcome = 'A';
            }

            $isCorrect = ($actualOutcome === $predictedOutcome);

            $probActual = match ($actualOutcome) {
                'H' => $pH,
                'D' => $pD,
                'A' => $pA,
            };

            $logLoss = $metrics->singleLogLoss($probActual);
            $brier = $metrics->singleBrier($pH, $pD, $pA, $actualOutcome);

            PredictionEvaluation::create([
                'prediction_id' => $pred->id,
                'outcome' => $actualOutcome,
                'predicted_outcome' => $predictedOutcome,
                'correct' => $isCorrect,
                'log_loss' => round($logLoss, 4),
                'brier' => round($brier, 4),
                'evaluated_at' => CarbonImmutable::now('UTC'),
            ]);

            $evaluatedCount++;
            if ($isCorrect) {
                $correctCount++;
            }
            $totalLogLoss += $logLoss;
            $totalBrier += $brier;

            if ($evaluatedCount <= 15) {
                $homeName = $match->homeTeam?->name ?? 'Home';
                $awayName = $match->awayTeam?->name ?? 'Away';
                $score = "{$match->home_goals}-{$match->away_goals}";
                $tableRows[] = [
                    "{$homeName} vs {$awayName}",
                    $score,
                    $actualOutcome,
                    $predictedOutcome,
                    $isCorrect ? '<info>YES</info>' : '<comment>NO</comment>',
                    number_format($logLoss, 4),
                    number_format($brier, 4),
                ];
            }
        }

        $this->newLine();
        if (!empty($tableRows)) {
            $this->table(
                ['Match', 'Score', 'Actual', 'Predicted', 'Correct', 'Log Loss', 'Brier'],
                $tableRows
            );
            if ($evaluatedCount > 15) {
                $this->line("... and " . ($evaluatedCount - 15) . " more evaluated.");
            }
        }

        $avgLogLoss = $evaluatedCount > 0 ? $totalLogLoss / $evaluatedCount : 0.0;
        $avgBrier = $evaluatedCount > 0 ? $totalBrier / $evaluatedCount : 0.0;
        $accuracy = $evaluatedCount > 0 ? ($correctCount / $evaluatedCount) * 100 : 0.0;

        $this->newLine();
        $this->info("========================================");
        $this->info(" PREDICTION EVALUATION SUMMARY");
        $this->info("========================================");
        $this->line("Evaluated: " . $evaluatedCount);
        $this->line("Correct:   " . $correctCount . " (" . number_format($accuracy, 1) . "%)");
        $this->line("Avg Log Loss: " . number_format($avgLogLoss, 4));
        $this->line("Avg Brier:    " . number_format($avgBrier, 4));

        return self::SUCCESS;
    }
}

