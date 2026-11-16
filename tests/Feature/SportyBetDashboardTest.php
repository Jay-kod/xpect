<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportyBetDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Competition $comp;
    private Team $home;
    private Team $away;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-11 12:00:00', 'UTC'));

        $this->comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $this->home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $this->away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_renders_database_provenance_trust_strip(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Xpect Prediction Engine Active');
        $response->assertSee('v1.2.1-h2h');
        $response->assertSee('Zero Leakage Guard');
        $response->assertSee('matches in DB');
        $response->assertSee('seasons verified');
        $response->assertSee('predictions evaluated');
    }

    public function test_dashboard_renders_sportybet_league_switcher_chips(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('sporty-leagues-bar', false);
        $response->assertSee('sporty-league-chip', false);
        $response->assertSee('Premier League');
        $response->assertSee('EPL');
    }

    public function test_dashboard_renders_sportybet_1x2_odds_tiles_with_favored_highlight(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(2),
            'status' => 'scheduled',
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC'),
            'p_home' => 0.6240,
            'p_draw' => 0.2130,
            'p_away' => 0.1630,
            'exp_home_goals' => 2.10,
            'exp_away_goals' => 0.85,
            'top_scorelines' => [['home' => 2, 'away' => 0, 'prob' => 0.142]],
            'features' => [],
        ]);

        $response = $this->get('/?competition=premier-league&status=today');

        $response->assertStatus(200);
        $response->assertSee('sporty-odds-grid', false);
        $response->assertSee('1 &bull; Arsenal', false);
        $response->assertSee('62.4%');
        $response->assertSee('X &bull; Draw', false);
        $response->assertSee('21.3%');
        $response->assertSee('2 &bull; Chelsea', false);
        $response->assertSee('16.3%');
        $response->assertSee('Model Pick');
        $response->assertSee('Most Likely Score:');
        $response->assertSee('2-0');
        $response->assertSee('xG:');
        $response->assertSee('2.1');
        $response->assertSee('0.85');
        $response->assertSee('past H2H matches in DB');
    }

    public function test_dashboard_renders_verified_ai_preview_text_and_implied_fair_odds(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(2),
            'status' => 'scheduled',
        ]);

        $prediction = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC'),
            'p_home' => 0.6240,
            'p_draw' => 0.2130,
            'p_away' => 0.1630,
            'exp_home_goals' => 2.10,
            'exp_away_goals' => 0.85,
            'top_scorelines' => [['home' => 2, 'away' => 0, 'prob' => 0.142]],
            'features' => [],
        ]);

        $apiCall = \App\Models\ApiCall::create([
            'cache_key' => 'gemini:test-preview-key',
            'source' => 'gemini',
            'method' => 'POST',
            'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent',
            'request_params' => [],
            'request_body' => '{"contents":[]}',
            'response_status' => 200,
            'response_body' => '{"candidates":[]}',
            'duration_ms' => 450,
            'triggered_by' => 'preview:generate',
            'called_at' => CarbonImmutable::now('UTC'),
        ]);

        \App\Models\Preview::create([
            'prediction_id' => $prediction->id,
            'api_call_id' => $apiCall->id,
            'model' => 'gemini-3-flash-preview',
            'prompt_version' => 'v1.0.0',
            'text' => 'Arsenal holds a strong mathematical advantage at Emirates Stadium with 62.4% win probability.',
            'flagged' => false,
        ]);

        $response = $this->get('/?competition=premier-league&status=today');

        $response->assertStatus(200);
        // Fair implied odds
        $response->assertSee('Odds 1.60');
        $response->assertSee('Odds 4.69');
        $response->assertSee('Odds 6.13');

        // AI Reasoning text from $preview->text
        $response->assertSee('AI Reasoning Summary (Verified Data)');
        $response->assertSee('Arsenal holds a strong mathematical advantage at Emirates Stadium with 62.4% win probability.');
        $response->assertSee('gemini-3-flash-preview');
        $response->assertSee('Strict DB Facts');
    }

    public function test_dashboard_defaults_to_today_when_today_has_matches(): void
    {
        // Create match for today
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(3),
            'status' => 'scheduled',
        ]);

        $response = $this->get('/?competition=premier-league');

        $response->assertStatus(200);
        $response->assertSee("Today's Matches", false);
        $response->assertSee('Arsenal FC');
        $response->assertSee('Chelsea FC');
    }

    public function test_match_card_renders_time_remaining_countdown_and_btts_over25_markets(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(2)->addMinutes(15),
            'status' => 'scheduled',
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC'),
            'p_home' => 0.50,
            'p_draw' => 0.25,
            'p_away' => 0.25,
            'exp_home_goals' => 1.74,
            'exp_away_goals' => 1.60,
            'top_scorelines' => [['home' => 1, 'away' => 1, 'prob' => 0.105]],
            'features' => [
                'home' => ['form_points_last_5' => 11, 'rest_days' => 6],
                'away' => ['form_points_last_5' => 7, 'rest_days' => 3],
            ],
        ]);

        $response = $this->get('/?competition=premier-league&status=today');

        $response->assertStatus(200);
        // Time remaining countdown badge
        $response->assertSee('Starts in 2h');
        // Analytical markets: Over 2.5 and BTTS
        $response->assertSee('Over 2.5:');
        $response->assertSee('BTTS:');
        // Team form and rest days
        $response->assertSee('Form: 11/15 pts');
        $response->assertSee('6d rest');
        $response->assertSee('Form: 7/15 pts');
        $response->assertSee('3d rest');
    }

    public function test_match_card_renders_live_in_play_active_half_and_goals(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->subMinutes(30),
            'status' => 'in_play',
            'home_goals' => 1,
            'away_goals' => 0,
        ]);

        $response = $this->get('/?competition=premier-league&status=today');

        $response->assertStatus(200);
        // Active half indicator
        $response->assertSee('1st Half');
        $response->assertSee('live-pulse-dot', false);
        $response->assertSee('LIVE');
    }

    public function test_match_card_renders_finished_goals_with_half_time_score(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 9,
            'kickoff_at' => CarbonImmutable::now('UTC')->subDays(2),
            'status' => 'finished',
            'home_goals' => 3,
            'away_goals' => 1,
            'ht_home_goals' => 1,
            'ht_away_goals' => 0,
        ]);

        $response = $this->get('/?competition=premier-league&status=past');

        $response->assertStatus(200);
        $response->assertSee('Score: 3 &ndash; 1', false);
        $response->assertSee('(HT: 1-0)');
        $response->assertSee('HT Score:');
        $response->assertSee('Total Goals:');
        $response->assertSee('FULL TIME');
    }

    public function test_finished_match_displays_prediction_won_verdict_when_favored_pick_wins(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 8,
            'kickoff_at' => CarbonImmutable::now('UTC')->subDays(3),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 0,
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC')->subDays(4),
            'p_home' => 0.6500,
            'p_draw' => 0.2000,
            'p_away' => 0.1500,
            'exp_home_goals' => 2.10,
            'exp_away_goals' => 0.60,
            'top_scorelines' => [['home' => 2, 'away' => 0, 'prob' => 0.16]],
            'features' => [],
        ]);

        $response = $this->get('/?competition=premier-league&status=past');

        $response->assertStatus(200);
        $response->assertSee('PREDICTION WON');
        $response->assertSee('ACCURATE PICK (WON)');
        $response->assertSee('Score: 2 &ndash; 0', false);
    }

    public function test_finished_match_displays_prediction_missed_verdict_when_favored_pick_loses(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 7,
            'kickoff_at' => CarbonImmutable::now('UTC')->subDays(5),
            'status' => 'finished',
            'home_goals' => 0,
            'away_goals' => 2,
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC')->subDays(6),
            'p_home' => 0.6500,
            'p_draw' => 0.2000,
            'p_away' => 0.1500,
            'exp_home_goals' => 2.10,
            'exp_away_goals' => 0.60,
            'top_scorelines' => [['home' => 2, 'away' => 0, 'prob' => 0.16]],
            'features' => [],
        ]);

        $response = $this->get('/?competition=premier-league&status=past');

        $response->assertStatus(200);
        $response->assertSee('PREDICTION MISSED');
        $response->assertSee('MODEL PICK (MISSED)');
        $response->assertSee('ACTUAL WINNER');
    }

    public function test_match_card_renders_three_analytical_systems_breakdown(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(4),
            'status' => 'scheduled',
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC'),
            'p_home' => 0.5000,
            'p_draw' => 0.2500,
            'p_away' => 0.2500,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 1.00,
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.11]],
            'features' => [],
        ]);

        $response = $this->get('/?competition=premier-league&status=today');

        $response->assertStatus(200);
        $response->assertSee('3 SYSTEMS WORKING ON THIS GAME');
        $response->assertSee('SYSTEM 1');
        $response->assertSee('Poisson Engine');
        $response->assertSee('SYSTEM 2');
        $response->assertSee('Elo Rating Engine');
        $response->assertSee('SYSTEM 3');
        $response->assertSee('Consensus');
        $response->assertSee('Consensus:');
    }

    public function test_match_card_renders_match_history_goals_motm_and_player_ratings(): void
    {
        // Past H2H match: Arsenal 2 - 1 Chelsea
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2023,
            'matchday' => 15,
            'kickoff_at' => CarbonImmutable::now('UTC')->subMonths(6),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
            'ht_home_goals' => 1,
            'ht_away_goals' => 0,
        ]);

        // Current target match
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(2),
            'status' => 'scheduled',
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.3.0-calibrated',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC'),
            'p_home' => 0.5000,
            'p_draw' => 0.2500,
            'p_away' => 0.2500,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 1.00,
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.11]],
            'features' => [],
        ]);

        $response = $this->get('/?competition=premier-league&status=today');

        $response->assertStatus(200);
        // Header title
        $response->assertSee('Match History, Goals, Man of the Match &amp; Player Ratings', false);
        // Sub-tabs
        $response->assertSee('📜 Match History (H2H)', false);
        $response->assertSee('⚽ Goals Breakdown', false);
        $response->assertSee('★ Man of the Match', false);
        $response->assertSee('📊 Team Ratings', false);
        $response->assertSee('👥 Individual Players', false);
        // H2H past records
        $response->assertSee('Head-to-Head Clash History');
        $response->assertSee('Zero Data Leakage: Strictly matches prior to kickoff');
        // Team ratings section
        $response->assertSee('Attack Unit (Forwards)');
        $response->assertSee('Midfield Control');
        $response->assertSee('Defence &amp; Goalkeeper', false);
        // Individual players
        $response->assertSee('Individual Player Ratings (Both Starting XIs)');
    }
}
