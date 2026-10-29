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

class DateFilterTest extends TestCase
{
    use RefreshDatabase;

    private Competition $comp;
    private Team $teamA;
    private Team $teamB;
    private Team $teamC;
    private Team $teamD;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $this->teamA = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $this->teamB = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);
        $this->teamC = Team::create(['name' => 'Liverpool FC', 'short_name' => 'Liverpool']);
        $this->teamD = Team::create(['name' => 'Manchester City FC', 'short_name' => 'Man City']);
    }

    public function test_dashboard_filters_matches_strictly_by_date(): void
    {
        // Match 1: 2026-08-17
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-08-17 19:00:00', 'UTC'),
            'status' => 'scheduled',
        ]);

        // Match 2: 2026-08-24
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamC->id,
            'away_team_id' => $this->teamD->id,
            'season' => 2026,
            'matchday' => 2,
            'kickoff_at' => CarbonImmutable::parse('2026-08-24 15:00:00', 'UTC'),
            'status' => 'scheduled',
        ]);

        // Request specifically for 2026-08-17
        $response = $this->get(route('dashboard', ['date' => '2026-08-17']));

        $response->assertStatus(200);
        $response->assertSee('Arsenal FC');
        $response->assertSee('Chelsea FC');
        $response->assertDontSee('Manchester City FC');
        $response->assertSee('Fixtures on Monday, 17 Aug 2026');
        $response->assertSee('2026-08-17');

        // Request specifically for 2026-08-24
        $response2 = $this->get(route('dashboard', ['date' => '2026-08-24']));

        $response2->assertStatus(200);
        $response2->assertSee('Liverpool FC');
        $response2->assertSee('Manchester City FC');
        $response2->assertDontSee('Arsenal FC');
        $response2->assertSee('Fixtures on Monday, 24 Aug 2026');
    }

    public function test_dashboard_handles_invalid_date_gracefully(): void
    {
        $response = $this->get(route('dashboard', ['date' => 'not-a-valid-date']));

        $response->assertStatus(200);
        // Falls back to regular season view without fatal exception
    }

    public function test_motm_filters_matches_by_date(): void
    {
        // Match 1: 2026-08-17 (finished)
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-08-17 19:00:00', 'UTC'),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 0,
        ]);

        // Match 2: 2026-08-24 (finished)
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamC->id,
            'away_team_id' => $this->teamD->id,
            'season' => 2026,
            'matchday' => 2,
            'kickoff_at' => CarbonImmutable::parse('2026-08-24 15:00:00', 'UTC'),
            'status' => 'finished',
            'home_goals' => 1,
            'away_goals' => 1,
        ]);

        $response = $this->get(route('motm', ['date' => '2026-08-17']));

        $response->assertStatus(200);
        $response->assertSee('Arsenal FC');
        $response->assertSee('Monday, 17 Aug 2026');
        $response->assertDontSee('Manchester City FC');
    }

    public function test_ai_reasoning_studio_filters_available_matches_by_date(): void
    {
        $match1 = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-08-17 19:00:00', 'UTC'),
            'status' => 'scheduled',
        ]);

        Prediction::create([
            'match_id' => $match1->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC')->subHours(2),
            'p_home' => 0.5500,
            'p_draw' => 0.2500,
            'p_away' => 0.2000,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 0.90,
            'features' => [],
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.12]],
        ]);

        $match2 = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamC->id,
            'away_team_id' => $this->teamD->id,
            'season' => 2026,
            'matchday' => 2,
            'kickoff_at' => CarbonImmutable::parse('2026-08-24 15:00:00', 'UTC'),
            'status' => 'scheduled',
        ]);

        Prediction::create([
            'match_id' => $match2->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now('UTC')->subHours(2),
            'p_home' => 0.4000,
            'p_draw' => 0.3000,
            'p_away' => 0.3000,
            'exp_home_goals' => 1.40,
            'exp_away_goals' => 1.20,
            'features' => [],
            'top_scorelines' => [['home' => 1, 'away' => 1, 'prob' => 0.14]],
        ]);

        $response = $this->get(route('ai-reasoning', ['date' => '2026-08-17']));

        $response->assertStatus(200);
        $response->assertSee('Arsenal FC');
        $response->assertSee('2026-08-17');
    }

    public function test_dashboard_filters_by_kickoff_time_slot(): void
    {
        // Early match: 12:30 UTC
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-08-17 12:30:00', 'UTC'),
            'status' => 'scheduled',
        ]);

        // Afternoon match: 15:00 UTC
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamC->id,
            'away_team_id' => $this->teamD->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-08-17 15:00:00', 'UTC'),
            'status' => 'scheduled',
        ]);

        // Filter by afternoon slot
        $response = $this->get(route('dashboard', [
            'date' => '2026-08-17',
            'time_slot' => 'afternoon',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Liverpool FC');
        $response->assertSee('Manchester City FC');
        $response->assertDontSee('Arsenal FC');
        $response->assertSee('Prime Afternoon');

        // Filter by early slot
        $responseEarly = $this->get(route('dashboard', [
            'date' => '2026-08-17',
            'time_slot' => 'early',
        ]));

        $responseEarly->assertStatus(200);
        $responseEarly->assertSee('Arsenal FC');
        $responseEarly->assertSee('Chelsea FC');
        $responseEarly->assertDontSee('Liverpool FC');
        $responseEarly->assertSee('Early Kickoff');
    }

    public function test_dashboard_filters_by_preset(): void
    {
        $responseToday = $this->get(route('dashboard', ['preset' => 'today']));
        $responseToday->assertStatus(200);

        $responseYesterday = $this->get(route('dashboard', ['preset' => 'yesterday']));
        $responseYesterday->assertStatus(200);
    }

    public function test_topbar_renders_universal_date_filter_input(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('temporal-widget-container');
        $response->assertSee('temporal-dropdown-panel');
        $response->assertSee('Quick Presets');
        $response->assertSee('Kickoff Time Window');
        $response->assertSee('<input type="date" name="date"', false);
    }
}
