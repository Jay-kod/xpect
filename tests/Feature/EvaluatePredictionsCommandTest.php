<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\PredictionEvaluation;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluatePredictionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluates_finished_predictions_correctly(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2024-08-17 15:00:00'),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);

        $pred = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.0.0-poisson',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2024-08-17 14:00:00'),
            'p_home' => 0.6000,
            'p_draw' => 0.2500,
            'p_away' => 0.1500,
            'exp_home_goals' => 1.8,
            'exp_away_goals' => 0.9,
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.12]],
            'features' => [],
        ]);

        $this->artisan('evaluate:predictions')
            ->expectsOutputToContain('PREDICTION EVALUATION SUMMARY')
            ->assertExitCode(0);

        $this->assertDatabaseHas('prediction_evaluations', [
            'prediction_id' => $pred->id,
            'outcome' => 'H',
            'predicted_outcome' => 'H',
            'correct' => 1,
        ]);

        $eval = PredictionEvaluation::where('prediction_id', $pred->id)->first();
        $this->assertNotNull($eval);
        $this->assertEqualsWithDelta(0.5108, (float) $eval->log_loss, 0.005);
        $this->assertEqualsWithDelta(0.2450, (float) $eval->brier, 0.005);
    }

    public function test_ignores_backtest_predictions_unless_flagged(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2024-08-17 15:00:00'),
            'status' => 'finished',
            'home_goals' => 1,
            'away_goals' => 1,
        ]);

        $backtestPred = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.0.0-poisson',
            'is_backtest' => true,
            'as_of' => CarbonImmutable::parse('2024-08-17 14:00:00'),
            'p_home' => 0.4000,
            'p_draw' => 0.3500,
            'p_away' => 0.2500,
            'exp_home_goals' => 1.2,
            'exp_away_goals' => 1.0,
            'top_scorelines' => [],
            'features' => [],
        ]);

        // Default run does not evaluate backtest
        $this->artisan('evaluate:predictions')
            ->expectsOutputToContain('No unevaluated predictions for finished matches found.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('prediction_evaluations', [
            'prediction_id' => $backtestPred->id,
        ]);

        // With --include-backtest flag
        $this->artisan('evaluate:predictions --include-backtest')
            ->expectsOutputToContain('PREDICTION EVALUATION SUMMARY')
            ->assertExitCode(0);

        $this->assertDatabaseHas('prediction_evaluations', [
            'prediction_id' => $backtestPred->id,
            'outcome' => 'D',
            'predicted_outcome' => 'H', // 0.40 > 0.35
            'correct' => 0,
        ]);
    }
}

