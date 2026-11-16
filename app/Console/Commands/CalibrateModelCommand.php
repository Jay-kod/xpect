<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Services\Evaluation\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CalibrateModelCommand extends Command
{
    protected $signature = 'model:calibrate
                            {--competition= : Filter to specific competition slug (e.g. premier-league)}
                            {--season= : Filter to specific season}
                            {--save : Persist calibrated parameters to storage/calibration/calibrated_weights.json}';

    protected $description = 'Perform continuous learning & walk-forward parameter calibration against historical DB matches';

    public function handle(Metrics $metrics): int
    {
        $this->info('================================================================');
        $this->info('  XPECT CONTINUOUS LEARNING & CALIBRATION ENGINE (PHP / LARAVEL)');
        $this->info('  Walking forward through SQLite database history (Zero Leakage)');
        $this->info('================================================================');

        $compSlug = $this->option('competition');
        $season = $this->option('season') ? (int) $this->option('season') : null;

        $query = FootballMatch::where('status', 'finished')
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->orderBy('kickoff_at', 'asc');

        if ($compSlug) {
            $comp = Competition::where('slug', $compSlug)->first();
            if ($comp) {
                $query->where('competition_id', $comp->id);
            }
        }

        if ($season) {
            $query->where('season', $season);
        }

        $matches = $query->get(['id', 'competition_id', 'home_team_id', 'away_team_id', 'kickoff_at', 'home_goals', 'away_goals']);
        $totalMatches = $matches->count();

        $this->line("Loaded <fg=cyan>{$totalMatches}</> historical finished matches from SQLite.");

        if ($totalMatches < 15) {
            $this->warn('Insufficient historical matches to calibrate accurately.');
            return self::SUCCESS;
        }

        // Hyperparameter candidates for grid-search
        $kCandidates = [24.0, 32.0, 40.0];
        $advCandidates = [50.0, 65.0, 80.0];
        $drawScaleCandidates = [400.0, 450.0, 500.0];

        $bestLoss = PHP_FLOAT_MAX;
        $bestBrier = PHP_FLOAT_MAX;
        $bestAcc = 0.0;
        $bestParams = [];
        $rows = [];

        foreach ($kCandidates as $k) {
            foreach ($advCandidates as $adv) {
                foreach ($drawScaleCandidates as $ds) {
                    $ratings = [];
                    $totalLogLoss = 0.0;
                    $totalBrier = 0.0;
                    $correctPicks = 0;
                    $count = 0;

                    foreach ($matches as $m) {
                        $hId = (int) $m->home_team_id;
                        $aId = (int) $m->away_team_id;
                        $hGoals = (int) $m->home_goals;
                        $aGoals = (int) $m->away_goals;

                        $rH = $ratings[$hId] ?? 1500.0;
                        $rA = $ratings[$aId] ?? 1500.0;

                        $diff = ($rH + $adv) - $rA;
                        $expH = 1.0 / (1.0 + (10.0 ** (-$diff / 400.0)));
                        $expA = 1.0 - $expH;

                        $baseDraw = 0.255;
                        $pDrawRaw = max(0.16, min(0.30, $baseDraw * exp(-(($diff / $ds) ** 2))));
                        $pHomeRaw = (1.0 - $pDrawRaw) * $expH;
                        $pAwayRaw = (1.0 - $pDrawRaw) * $expA;

                        $sum = $pHomeRaw + $pDrawRaw + $pAwayRaw;
                        $pH = $pHomeRaw / $sum;
                        $pD = $pDrawRaw / $sum;
                        $pA = $pAwayRaw / $sum;

                        $actual = match (true) {
                            $hGoals > $aGoals => 'H',
                            $hGoals < $aGoals => 'A',
                            default => 'D',
                        };

                        $pActual = match ($actual) {
                            'H' => $pH,
                            'D' => $pD,
                            'A' => $pA,
                        };

                        $pick = 'H';
                        if ($pD > $pH && $pD >= $pA) $pick = 'D';
                        elseif ($pA > $pH && $pA > $pD) $pick = 'A';

                        if ($pick === $actual) {
                            $correctPicks++;
                        }

                        $totalLogLoss += $metrics->singleLogLoss($pActual);
                        $totalBrier += $metrics->singleBrier($pH, $pD, $pA, $actual);
                        $count++;

                        // Learn from match outcome for future matches strictly after kickoff
                        $actualScoreH = ($actual === 'H') ? 1.0 : (($actual === 'D') ? 0.5 : 0.0);
                        $actualScoreA = 1.0 - $actualScoreH;

                        $marginMultiplier = match (abs($hGoals - $aGoals)) {
                            0, 1 => 1.0,
                            2 => 1.25,
                            default => 1.50,
                        };

                        $ratings[$hId] = $rH + ($k * $marginMultiplier * ($actualScoreH - $expH));
                        $ratings[$aId] = $rA + ($k * $marginMultiplier * ($actualScoreA - $expA));
                    }

                    $avgLogLoss = $totalLogLoss / max(1, $count);
                    $avgBrier = $totalBrier / max(1, $count);
                    $accuracy = ($correctPicks / max(1, $count)) * 100.0;

                    $rows[] = [
                        $k,
                        "+{$adv}",
                        $ds,
                        number_format($avgLogLoss, 4),
                        number_format($avgBrier, 4),
                        number_format($accuracy, 2) . '%',
                    ];

                    if ($avgLogLoss < $bestLoss) {
                        $bestLoss = $avgLogLoss;
                        $bestBrier = $avgBrier;
                        $bestAcc = $accuracy;
                        $bestParams = [
                            'k_factor' => $k,
                            'home_adv' => $adv,
                            'draw_scale' => $ds,
                            'decay_half_life_days' => 60,
                            'dixon_coles_rho' => -0.06,
                            'shrinkage_k' => 3.0,
                        ];
                    }
                }
            }
        }

        $this->table(
            ['K-Factor', 'Home Adv', 'Draw Scale', 'Log Loss', 'Brier Score', 'Accuracy'],
            array_slice($rows, 0, 10)
        );

        $this->info("... (" . count($rows) . " hyperparameter configurations tested)\n");

        $this->line("<fg=green;options=bold>✓ OPTIMAL CALIBRATED PARAMETERS FOUND:</>");
        $this->line("  • Optimal K-Factor:     <fg=yellow>{$bestParams['k_factor']}</>");
        $this->line("  • Optimal Home Adv:     <fg=yellow>+{$bestParams['home_adv']} Elo points</>");
        $this->line("  • Optimal Draw Scale:   <fg=yellow>{$bestParams['draw_scale']}</>");
        $this->line("  • Historical Accuracy:  <fg=green>" . number_format($bestAcc, 2) . "%</>");
        $this->line("  • Minimum Log Loss:     <fg=cyan>" . number_format($bestLoss, 4) . "</>");
        $this->line("  • Multi-class Brier:    <fg=cyan>" . number_format($bestBrier, 4) . "</>\n");

        // Persist calibration
        $outDir = storage_path('calibration');
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $calibPath = $outDir . '/calibrated_weights.json';
        $payload = [
            'timestamp' => CarbonImmutable::now('UTC')->toIso8601String(),
            'matches_evaluated' => $totalMatches,
            'best_parameters' => $bestParams,
            'metrics' => [
                'log_loss' => round($bestLoss, 4),
                'brier_score' => round($bestBrier, 4),
                'accuracy_pct' => round($bestAcc, 2),
            ],
            'candidates_tested' => count($rows),
        ];

        file_put_contents($calibPath, json_encode($payload, JSON_PRETTY_PRINT));
        $this->info("✓ Calibrated parameters saved to: {$calibPath}");
        $this->line("<fg=gray>Future match predictions will now continuously incorporate these calibrated parameters.</>");

        return self::SUCCESS;
    }
}
