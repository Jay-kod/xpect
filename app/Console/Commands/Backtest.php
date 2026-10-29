<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Services\Evaluation\Evaluator;
use App\Services\Models\PoissonModel;
use App\Services\Predictions\PredictionService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class Backtest extends Command
{
    protected $signature = 'backtest {competition} {--seasons=2023,2024 : Held-out seasons to evaluate} {--model= : Model version string}';
    protected $description = 'Run chronological walk-forward backtest across held-out seasons and evaluate against baselines';

    public function handle(
        PredictionService $predictionService,
        Evaluator $evaluator
    ): int {
        $compSlug = (string) $this->argument('competition');
        $competition = Competition::where('slug', $compSlug)->first();

        if (!$competition) {
            $this->error("Competition [{$compSlug}] not found.");
            return self::FAILURE;
        }

        $seasonsArg = (string) $this->option('seasons');
        $seasons = array_map('intval', explode(',', $seasonsArg));
        $modelVersion = (string) ($this->option('model') ?: PoissonModel::MODEL_VERSION);

        $this->info("========================================================");
        $this->info(" XPECT WALK-FORWARD MODEL BACKTEST");
        $this->info("========================================================");
        $this->line("Competition:   {$competition->name}");
        $this->line("Test Seasons:  " . implode(', ', $seasons));
        $this->line("Model Version: {$modelVersion}");
        $this->newLine();

        // 1. Fetch finished matches chronologically
        $matches = FootballMatch::with(['homeTeam', 'awayTeam', 'competition'])
            ->where('competition_id', $competition->id)
            ->whereIn('season', $seasons)
            ->where('status', 'finished')
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->orderBy('kickoff_at', 'asc')
            ->get();

        if ($matches->isEmpty()) {
            $this->error("No finished matches found for seasons [" . implode(', ', $seasons) . "]. Run backfill:history first.");
            return self::FAILURE;
        }

        $this->info("Found {$matches->count()} finished matches. Generating walk-forward predictions...");
        $bar = $this->output->createProgressBar($matches->count());
        $bar->start();

        $predictionModels = collect();

        foreach ($matches as $match) {
            // Find or generate backtest prediction
            $pred = Prediction::where('match_id', $match->id)
                ->where('model_version', $modelVersion)
                ->where('is_backtest', true)
                ->first();

            if (!$pred) {
                $asOf = CarbonImmutable::parse($match->kickoff_at);
                $pred = $predictionService->predict(
                    match: $match,
                    asOf: $asOf,
                    isBacktest: true,
                    modelVersion: $modelVersion
                );
            }

            // Bind relationship for evaluator
            $pred->setRelation('match', $match);
            $predictionModels->push($pred);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // 2. Evaluate model & baselines
        $results = $evaluator->evaluate($predictionModels, $competition);

        $this->info("EVALUATION RESULTS (Sample Size: {$results['sample_size']} matches)");
        $tableRows = [
            [
                '<info>Poisson Model (' . $modelVersion . ')</info>',
                number_format($results['model']['accuracy'] * 100, 1) . '%',
                number_format($results['model']['log_loss'], 4),
                number_format($results['model']['brier'], 4),
            ],
            [
                'Baseline: Historical Base Rates',
                number_format($results['baselines']['historical_base_rates']['accuracy'] * 100, 1) . '%',
                number_format($results['baselines']['historical_base_rates']['log_loss'], 4),
                number_format($results['baselines']['historical_base_rates']['brier'], 4),
            ],
            [
                'Baseline: Always Home',
                number_format($results['baselines']['always_home']['accuracy'] * 100, 1) . '%',
                number_format($results['baselines']['always_home']['log_loss'], 4),
                number_format($results['baselines']['always_home']['brier'], 4),
            ],
            [
                'Baseline: Uniform (1/3 each)',
                number_format($results['baselines']['uniform']['accuracy'] * 100, 1) . '%',
                number_format($results['baselines']['uniform']['log_loss'], 4),
                number_format($results['baselines']['uniform']['brier'], 4),
            ],
        ];

        if (!empty($results['benchmarks']['bookmaker_b365'])) {
            $b = $results['benchmarks']['bookmaker_b365'];
            $tableRows[] = [
                '<comment>Benchmark: Bet365 Odds (Consensus)</comment>',
                number_format($b['accuracy'] * 100, 1) . '%',
                number_format($b['log_loss'], 4),
                number_format($b['brier'], 4),
            ];
        }

        if (!empty($results['benchmarks']['bookmaker_market_avg'])) {
            $b = $results['benchmarks']['bookmaker_market_avg'];
            $tableRows[] = [
                '<comment>Benchmark: Market Average Odds</comment>',
                number_format($b['accuracy'] * 100, 1) . '%',
                number_format($b['log_loss'], 4),
                number_format($b['brier'], 4),
            ];
        }

        $this->table(
            ['Strategy / Benchmark', 'Accuracy', 'Log Loss (↓)', 'Brier Score (↓)'],
            $tableRows
        );

        $this->newLine();
        $this->info("BASELINE COMPARISON SUMMARY:");
        $cmp = $results['comparison'];
        $this->line("  Beats Always-Home Baseline:           " . ($cmp['beats_always_home'] ? '<info>YES</info>' : '<error>NO</error>'));
        $this->line("  Beats Uniform Baseline:               " . ($cmp['beats_uniform'] ? '<info>YES</info>' : '<error>NO</error>'));
        $this->line("  Beats Historical Base Rates Baseline: " . ($cmp['beats_historical_base_rate'] ? '<info>YES</info>' : '<error>NO</error>'));
        $this->line("  All Baselines Beaten:                 " . ($cmp['all_baselines_beaten'] ? '<info>YES</info>' : '<error>NO</error>'));

        $this->newLine();
        $this->info("CALIBRATION TABLE (10 Bins):");
        $calRows = [];
        foreach ($results['calibration_table'] as $c) {
            $calRows[] = [
                $c['bin'],
                $c['samples'],
                $c['avg_predicted'] . '%',
                $c['observed_freq'] . '%',
            ];
        }
        $this->table(['Probability Bin', 'Samples', 'Avg Predicted Prob', 'Empirical Observed Freq'], $calRows);

        $this->info("========================================================");

        return self::SUCCESS;
    }
}

