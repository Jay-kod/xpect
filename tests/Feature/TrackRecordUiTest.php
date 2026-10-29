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

class TrackRecordUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_record_page_renders_metrics_and_calibration(): void
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
            'as_of' => CarbonImmutable::parse('2024-08-17 12:00:00'),
            'p_home' => 0.6000,
            'p_draw' => 0.2500,
            'p_away' => 0.1500,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 0.90,
            'top_scorelines' => [],
            'features' => [],
        ]);

        PredictionEvaluation::create([
            'prediction_id' => $pred->id,
            'outcome' => 'H',
            'predicted_outcome' => 'H',
            'correct' => true,
            'log_loss' => 0.5108,
            'brier' => 0.2450,
            'evaluated_at' => CarbonImmutable::now('UTC'),
        ]);

        $response = $this->get('/track-record');

        $response->assertStatus(200);
        $response->assertSee('Track Record');
        $response->assertSee('Benchmark Comparison');
        $response->assertSee('Probabilistic Calibration');
        $response->assertSee('Arsenal');
        $response->assertSee('Correct');
    }
}

