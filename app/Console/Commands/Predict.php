<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Services\Models\PoissonModel;
use App\Services\Predictions\PredictionService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class Predict extends Command
{
    protected $signature = 'predict {match? : Match ID} {--next : Predict the next upcoming scheduled match} {--competition=premier-league : Competition slug}';
    protected $description = 'Generate and display Poisson model probabilities for a football match';

    public function handle(PredictionService $predictionService): int
    {
        $matchId = $this->argument('match');
        $match = null;

        if ($matchId) {
            $match = FootballMatch::with(['homeTeam', 'awayTeam', 'competition'])->find($matchId);
        } elseif ($this->option('next')) {
            $compSlug = (string) $this->option('competition');
            $comp = Competition::where('slug', $compSlug)->first();

            $query = FootballMatch::with(['homeTeam', 'awayTeam', 'competition'])
                ->where('status', 'scheduled')
                ->orderBy('kickoff_at', 'asc');

            if ($comp) {
                $query->where('competition_id', $comp->id);
            }

            $match = $query->first();

            if (!$match) {
                // If no scheduled match, grab the latest match for previewing
                $match = FootballMatch::with(['homeTeam', 'awayTeam', 'competition'])
                    ->orderBy('kickoff_at', 'desc')
                    ->first();
            }
        }

        if (!$match) {
            $this->error('No match found. Provide a match ID or use --next.');
            return self::FAILURE;
        }

        $this->info("========================================================");
        $this->info(" XPECT MATCH PREDICTION");
        $this->info("========================================================");
        $this->line("Match:       {$match->homeTeam->name} vs {$match->awayTeam->name}");
        $this->line("Competition: {$match->competition->name} (Season {$match->season})");
        $this->line("Kickoff:     {$match->kickoff_at->toIso8601String()} UTC");
        $this->line("Status:      {$match->status}");

        // Check if prediction already exists for this match
        $prediction = Prediction::where('match_id', $match->id)
            ->where('model_version', PoissonModel::MODEL_VERSION)
            ->where('is_backtest', false)
            ->first();

        if (!$prediction) {
            $this->line("Calculating features & Poisson distribution...");
            $asOf = CarbonImmutable::parse($match->kickoff_at);
            $prediction = $predictionService->predict($match, $asOf, false);
        } else {
            $this->line("[Existing prediction retrieved from database]");
        }

        $this->newLine();
        $this->info("PROBABILITIES:");
        $pHomePct = number_format((float) $prediction->p_home * 100, 1);
        $pDrawPct = number_format((float) $prediction->p_draw * 100, 1);
        $pAwayPct = number_format((float) $prediction->p_away * 100, 1);

        $this->table(
            ['Home Win (' . $match->homeTeam->short_name . ')', 'Draw', 'Away Win (' . $match->awayTeam->short_name . ')'],
            [["{$pHomePct}%", "{$pDrawPct}%", "{$pAwayPct}%"]]
        );

        $this->info("EXPECTED GOALS (Lambda):");
        $this->table(
            ['Home Exp Goals', 'Away Exp Goals'],
            [[$prediction->exp_home_goals, $prediction->exp_away_goals]]
        );

        $this->info("TOP 5 SCORELINE PREDICTIONS:");
        $scoreRows = [];
        foreach ($prediction->top_scorelines as $s) {
            $pct = number_format((float) $s['prob'] * 100, 1) . '%';
            $scoreRows[] = [$s['score'], $pct];
        }
        $this->table(['Scoreline', 'Probability'], $scoreRows);

        if ($prediction->low_confidence) {
            $this->warn("⚠️ Note: Low confidence flag active (limited sample history for one or both teams).");
        }

        $this->line("Model Version: {$prediction->model_version} | As of: {$prediction->as_of->toIso8601String()}");
        $this->info("========================================================");

        return self::SUCCESS;
    }
}

