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

class TodayFixturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_fixtures_hub_renders_empty_state_with_next_fixture_when_no_matches_today(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Manchester City FC', 'short_name' => 'Man City']);
        $away = Team::create(['name' => 'Liverpool FC', 'short_name' => 'Liverpool']);

        // Match scheduled 3 days in the future
        FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addDays(3),
            'status' => 'scheduled',
        ]);

        $response = $this->get('/today');

        $response->assertStatus(200);
        $response->assertSee("Today's Fixtures Hub");
        $response->assertSee('No matches scheduled for');
        $response->assertSee('Manchester City FC vs Liverpool FC');
        $response->assertSee('Next Upcoming Matchday');
    }

    public function test_today_fixtures_hub_renders_matches_grouped_by_competition(): void
    {
        $epl = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $laliga = Competition::create([
            'slug' => 'la-liga',
            'name' => 'La Liga',
            'country' => 'Spain',
            'is_active' => true,
        ]);

        $arsenal = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $chelsea = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);
        $real = Team::create(['name' => 'Real Madrid CF', 'short_name' => 'Real Madrid']);
        $barca = Team::create(['name' => 'FC Barcelona', 'short_name' => 'Barcelona']);

        $todayStart = CarbonImmutable::now('UTC')->startOfDay();

        // EPL match kicking off today
        $m1 = FootballMatch::create([
            'competition_id' => $epl->id,
            'home_team_id' => $arsenal->id,
            'away_team_id' => $chelsea->id,
            'season' => 2026,
            'matchday' => 9,
            'kickoff_at' => $todayStart->addHours(14),
            'status' => 'scheduled',
        ]);

        // La Liga match kicking off today
        $m2 = FootballMatch::create([
            'competition_id' => $laliga->id,
            'home_team_id' => $real->id,
            'away_team_id' => $barca->id,
            'season' => 2026,
            'matchday' => 11,
            'kickoff_at' => $todayStart->addHours(16),
            'status' => 'scheduled',
        ]);

        // Pre-computed Poisson prediction for m1
        Prediction::create([
            'match_id' => $m1->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => $m1->kickoff_at,
            'p_home' => 0.4850,
            'p_draw' => 0.2650,
            'p_away' => 0.2500,
            'exp_home_goals' => 1.85,
            'exp_away_goals' => 1.15,
            'features' => [],
        ]);

        $response = $this->get('/today');

        $response->assertStatus(200);
        $response->assertSee("Today's Fixtures Hub");
        $response->assertSee('Premier League');
        $response->assertSee('La Liga');
        $response->assertSee('Arsenal FC');
        $response->assertSee('Chelsea FC');
        $response->assertSee('Real Madrid CF');
        $response->assertSee('FC Barcelona');
        $response->assertSee('Home 48.5%');
        $response->assertSee('Away 25%');
        $response->assertSee('1.85');
    }

    public function test_predict_all_batch_action_computes_predictions_for_today(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        $todayStart = CarbonImmutable::now('UTC')->startOfDay();

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 9,
            'kickoff_at' => $todayStart->addHours(15),
            'status' => 'scheduled',
        ]);

        $todayDate = $todayStart->format('Y-m-d');

        $response = $this->post(route('today.predict-all'), ['date' => $todayDate]);

        $response->assertRedirect(route('today', ['date' => $todayDate]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('predictions', [
            'match_id' => $match->id,
            'is_backtest' => false,
        ]);
    }
}
