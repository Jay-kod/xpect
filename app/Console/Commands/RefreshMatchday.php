<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Clients\RateLimiter\QuotaGuard;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Services\DataSources\ApiFootball\ApiFootballAdapter;
use App\Services\Models\PoissonModel;
use App\Services\Predictions\PredictionService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class RefreshMatchday extends Command
{
    protected $signature = 'refresh:matchday 
                            {competition=premier-league : Competition slug} 
                            {--date= : YYYY-MM-DD matchday date (defaults to today)} 
                            {--stage=matchday : Prediction stage (matchday or final)} 
                            {--model= : Model version (defaults to v1.1.0-xg)} 
                            {--mock : Use mock data for testing or zero API quota usage}';

    protected $description = 'Refresh matchday injuries, lineups, and stage-specific predictions within quota limits';

    public function handle(
        ApiFootballAdapter $adapter,
        PredictionService $predictionService,
        QuotaGuard $quotaGuard
    ): int {
        $compSlug = (string) $this->argument('competition');
        $competition = Competition::where('slug', $compSlug)->first();

        if (!$competition) {
            $this->error("Competition [{$compSlug}] not found.");
            return self::FAILURE;
        }

        $dateStr = (string) ($this->option('date') ?: CarbonImmutable::now('UTC')->toDateString());
        $date = CarbonImmutable::parse($dateStr, 'UTC');
        $stage = (string) ($this->option('stage') ?: 'matchday');
        $modelVersion = (string) ($this->option('model') ?: PoissonModel::MODEL_V1_XG);
        $mock = (bool) $this->option('mock');

        $this->info("========================================================");
        $this->info(" XPECT MATCHDAY REFRESH PIPELINE");
        $this->info("========================================================");
        $this->line("Competition:   {$competition->name}");
        $this->line("Matchday Date: {$date->toDateString()}");
        $this->line("Target Stage:  {$stage}");
        $this->line("Model Version: {$modelVersion}");
        $this->line("Mode:          " . ($mock ? "MOCK (zero quota)" : "LIVE"));
        $this->newLine();

        // 1. Quota Guard Pre-Flight Check (if live mode)
        if (!$mock && !empty(env('API_FOOTBALL_KEY'))) {
            $usedToday = $quotaGuard->getDailyUsage('api_football');
            $maxDaily = config('football.quotas.api_football.per_day', 100);
            $reserve = config('football.quotas.api_football.reserve', 10);
            $remainingSafe = max(0, $maxDaily - $reserve - $usedToday);

            $this->line("Quota Status:  {$usedToday}/{$maxDaily} calls used today (Safe remaining: {$remainingSafe})");

            if ($remainingSafe <= 0) {
                $this->warn("Quota safety threshold reached (reserve limit: {$reserve}). Stopping refresh to protect quota.");
                return self::SUCCESS;
            }
        }

        // 2. Fetch Latest Injury Reports
        $this->info("1. Syncing latest injury reports...");
        try {
            $season = (int) $date->year;
            $injuryResult = $adapter->fetchInjuries($competition, $season, mock: $mock);
            $this->info("   Injuries synced: {$injuryResult['imported']} records recorded.");
        } catch (Throwable $e) {
            $this->warn("   Injury fetch warning: " . $e->getMessage());
        }

        // 3. Find Fixtures for Matchday Date
        $matches = FootballMatch::with(['homeTeam', 'awayTeam', 'lineups'])
            ->where('competition_id', $competition->id)
            ->whereDate('kickoff_at', $date->toDateString())
            ->get();

        if ($matches->isEmpty()) {
            // If no match on exact date, find upcoming matches within 3 days
            $matches = FootballMatch::with(['homeTeam', 'awayTeam', 'lineups'])
                ->where('competition_id', $competition->id)
                ->whereBetween('kickoff_at', [$date->startOfDay(), $date->addDays(3)->endOfDay()])
                ->limit(10)
                ->get();
        }

        if ($matches->isEmpty()) {
            $this->info("No matches found scheduled for {$date->toDateString()} (or next 3 days).");
            return self::SUCCESS;
        }

        $this->info("2. Processing {$matches->count()} matchday fixtures...");

        $refreshedCount = 0;

        foreach ($matches as $match) {
            $this->line("   Fixture: {$match->homeTeam->name} vs {$match->awayTeam->name} ({$match->kickoff_at->format('H:i')} UTC)");

            // If final stage, sync lineups
            if ($stage === 'final') {
                try {
                    $lineupResult = $adapter->fetchLineups($match, mock: $mock);
                    $this->line("     Lineups synced: {$lineupResult['imported']} teams recorded.");
                } catch (Throwable $e) {
                    $this->warn("     Lineup sync note: " . $e->getMessage());
                }
            }

            // Generate stage-specific prediction (respecting immutability by creating new prediction with stage)
            $existing = $match->predictions()
                ->where('model_version', $modelVersion)
                ->where('stage', $stage)
                ->where('is_backtest', false)
                ->first();

            if ($existing) {
                $this->line("     [stage: {$stage}] already recorded (Prediction #{$existing->id}). Skipping overwrite.");
                continue;
            }

            $pred = $predictionService->predict(
                match: $match,
                asOf: CarbonImmutable::now('UTC'),
                isBacktest: false,
                modelVersion: $modelVersion,
                stage: $stage
            );

            $this->info("     ✓ Generated {$stage} prediction: H: " . round($pred->p_home * 100, 1) . "% | D: " . round($pred->p_draw * 100, 1) . "% | A: " . round($pred->p_away * 100, 1) . "%");
            $refreshedCount++;
        }

        $this->newLine();
        $this->info("========================================================");
        $this->info("Matchday refresh complete! {$refreshedCount} {$stage} predictions generated.");
        $this->info("========================================================");

        return self::SUCCESS;
    }
}
