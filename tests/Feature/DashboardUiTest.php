<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_fixtures_and_predictions_successfully(): void
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
            'status' => 'scheduled',
        ]);

        $pred = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.0.0-poisson',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2024-08-17 12:00:00'),
            'p_home' => 0.5500,
            'p_draw' => 0.2500,
            'p_away' => 0.2000,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 0.90,
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.12]],
            'features' => [],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Fixtures & Predictions');
        $response->assertSee('Arsenal FC');
        $response->assertSee('Chelsea FC');
        $response->assertSee('55%');
        $response->assertSee('1.80');
        $response->assertDontSee('btn-add-action');
        $response->assertDontSee('sidebar-actions');
        $response->assertDontSee('Predict match');
    }

    public function test_dashboard_renders_upcoming_matches_and_model_calculation_breakdown(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Manchester City FC', 'short_name' => 'Man City']);
        $away = Team::create(['name' => 'Liverpool FC', 'short_name' => 'Liverpool']);

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::parse('2026-11-07 12:30:00'),
            'status' => 'scheduled',
        ]);

        $pred = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2026-11-07 12:30:00'),
            'p_home' => 0.5230,
            'p_draw' => 0.2410,
            'p_away' => 0.2360,
            'exp_home_goals' => 2.10,
            'exp_away_goals' => 1.25,
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.112]],
            'features' => [
                'home' => [
                    'home_attack_strength' => 1.42,
                    'home_defence_strength' => 0.88,
                    'form_points_last_5' => 11,
                    'rest_days' => 6,
                    'league_avg_home_goals' => 1.55,
                    'league_avg_away_goals' => 1.35,
                ],
                'away' => [
                    'away_attack_strength' => 1.15,
                    'away_defence_strength' => 0.95,
                    'form_points_last_5' => 9,
                    'rest_days' => 5,
                ],
            ],
        ]);

        $response = $this->get('/?competition=premier-league');

        $response->assertStatus(200);
        $response->assertSee('Next Matchday &amp; Upcoming Predictions', false);
        $response->assertSee('Man City');
        $response->assertSee('Liverpool');
        $response->assertSee('52.3%');
        $response->assertSee('2.1');
        $response->assertSee('1.25');
        $response->assertSee('How this prediction was calculated (Model Breakdown)');
        $response->assertSee('1. Expected Goals');
        $response->assertSee('2. Form &amp; Fatigue', false);
        $response->assertSee('3. Dixon-Coles Correction');
        $response->assertSee('Zero Data Leakage:');
    }

    public function test_predict_action_computes_new_prediction_and_redirects(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Liverpool FC', 'short_name' => 'Liverpool']);
        $away = Team::create(['name' => 'Everton FC', 'short_name' => 'Everton']);

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2024,
            'matchday' => 5,
            'kickoff_at' => CarbonImmutable::parse('2024-09-14 12:30:00'),
            'status' => 'scheduled',
        ]);

        $response = $this->post(route('matches.predict', $match->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('predictions', [
            'match_id' => $match->id,
            'is_backtest' => 0,
        ]);
    }

    public function test_preview_action_generates_ai_preview_via_claude(): void
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
            'status' => 'scheduled',
        ]);

        $pred = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.0.0-poisson',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2024-08-17 12:00:00'),
            'p_home' => 0.5500,
            'p_draw' => 0.2500,
            'p_away' => 0.2000,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 0.90,
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.12]],
            'features' => [],
        ]);

        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'id' => 'msg_1',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'Arsenal hold 55% win probability vs Chelsea (20%). Expected goals are 1.80 to 0.90.']],
                'model' => 'claude-3-5-sonnet-20241022',
                'usage' => ['input_tokens' => 250, 'output_tokens' => 50],
            ], 200),
        ]);

        config(['services.anthropic.key' => 'test-key']);

        $response = $this->post(route('predictions.preview', $pred->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('previews', [
            'prediction_id' => $pred->id,
        ]);
    }

    public function test_dashboard_renders_view_navigator_sidebar_with_active_status_and_counts(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        // Scheduled future match
        FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addDays(3),
            'status' => 'scheduled',
        ]);

        // Finished past match
        FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 9,
            'kickoff_at' => CarbonImmutable::now('UTC')->subDays(4),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);

        // 1. Default (All) view
        $response = $this->get('/?competition=premier-league&season=2026');
        $response->assertStatus(200);
        $response->assertSee('Currently Viewing');
        $response->assertSee("All Matches");
        $response->assertSee("Today's Matches", false);
        $response->assertSee("Future Matches");
        $response->assertSee("Past Matches");

        // 2. Future status filter
        $futureResponse = $this->get('/?competition=premier-league&season=2026&status=future');
        $futureResponse->assertStatus(200);
        $futureResponse->assertSee('Future Matches');
        $futureResponse->assertSee('view-nav-btn active', false);

        // 3. Today status filter with empty state prompt
        $todayResponse = $this->get('/?competition=premier-league&season=2026&status=today');
        $todayResponse->assertStatus(200);
        $todayResponse->assertSee("Today's Matches", false);
        $todayResponse->assertSee('No matches kicking off today');
        $todayResponse->assertSee('View Future Matches');

        // 4. Past status filter
        $pastResponse = $this->get('/?competition=premier-league&season=2026&status=past');
        $pastResponse->assertStatus(200);
        $pastResponse->assertSee('Past Matches');
        $pastResponse->assertSee('Score: 2 &ndash; 1', false);
    }

    public function test_dashboard_renders_competition_specific_vital_stats_and_scoring_trend(): void
    {
        $comp = Competition::create([
            'slug' => 'bundesliga',
            'name' => 'Bundesliga',
            'country' => 'Germany',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Bayern Munich', 'short_name' => 'Bayern']);
        $away = Team::create(['name' => 'Borussia Dortmund', 'short_name' => 'Dortmund']);

        // 3 finished matches with specific scores to verify exact stats
        FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-08-22 15:30:00'),
            'status' => 'finished',
            'home_goals' => 3,
            'away_goals' => 2,
        ]);

        FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $away->id,
            'away_team_id' => $home->id,
            'season' => 2026,
            'matchday' => 2,
            'kickoff_at' => CarbonImmutable::parse('2026-08-29 15:30:00'),
            'status' => 'finished',
            'home_goals' => 1,
            'away_goals' => 1,
        ]);

        $response = $this->get('/?competition=bundesliga&season=2026');

        $response->assertStatus(200);
        $response->assertSee('Season progress');
        $response->assertSee('Scoring average');
        $response->assertSee('Home advantage');
        $response->assertSee('Matchday Scoring &amp; Goal Intensity Trend', false);
        $response->assertSee('Bundesliga');
        $response->assertSee('Match Dynamics');
        $response->assertSee('Outcome Distribution');
        $response->assertSee('Competition Operations');
        $response->assertSee('MD 1');
        $response->assertSee('MD 2');
    }
}

