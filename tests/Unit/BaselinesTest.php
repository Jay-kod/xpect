<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\Evaluation\Baselines;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaselinesTest extends TestCase
{
    use RefreshDatabase;

    protected Baselines $baselines;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baselines = new Baselines();
    }

    public function test_always_home_baseline(): void
    {
        $res = $this->baselines->alwaysHome();
        $this->assertSame('H', $res['predicted']);
        $this->assertEqualsWithDelta(1.0, $res['p_home'] + $res['p_draw'] + $res['p_away'], 0.001);
        $this->assertGreaterThan(0.99, $res['p_home']);
    }

    public function test_uniform_baseline(): void
    {
        $res = $this->baselines->uniform();
        $this->assertSame('H', $res['predicted']);
        $this->assertEqualsWithDelta(1.0 / 3.0, $res['p_home'], 0.0001);
        $this->assertEqualsWithDelta(1.0 / 3.0, $res['p_draw'], 0.0001);
        $this->assertEqualsWithDelta(1.0 / 3.0, $res['p_away'], 0.0001);
    }

    public function test_historical_base_rates_computes_from_prior_matches_only(): void
    {
        $comp = Competition::create([
            'slug' => 'test-league',
            'name' => 'Test League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Team A', 'short_name' => 'A']);
        $away = Team::create(['name' => 'Team B', 'short_name' => 'B']);

        $asOf = CarbonImmutable::parse('2024-05-01 00:00:00');

        // Create 10 prior matches: 5 Home wins, 3 Draws, 2 Away wins
        for ($i = 1; $i <= 10; $i++) {
            $hGoals = ($i <= 5) ? 2 : (($i <= 8) ? 1 : 0);
            $aGoals = ($i <= 5) ? 0 : (($i <= 8) ? 1 : 2);

            FootballMatch::create([
                'competition_id' => $comp->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'season' => 2023,
                'matchday' => $i,
                'kickoff_at' => CarbonImmutable::parse('2024-01-01')->addDays($i),
                'status' => 'finished',
                'home_goals' => $hGoals,
                'away_goals' => $aGoals,
            ]);
        }

        // Add 5 future matches that must NOT be counted (leakage test)
        for ($i = 1; $i <= 5; $i++) {
            FootballMatch::create([
                'competition_id' => $comp->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'season' => 2024,
                'matchday' => $i,
                'kickoff_at' => $asOf->addDays($i),
                'status' => 'finished',
                'home_goals' => 0,
                'away_goals' => 3, // all away wins in the future
            ]);
        }

        $rates = $this->baselines->historicalBaseRates($comp, $asOf);

        // 5/10 = 0.50 Home, 3/10 = 0.30 Draw, 2/10 = 0.20 Away
        $this->assertEqualsWithDelta(0.50, $rates['p_home'], 0.001);
        $this->assertEqualsWithDelta(0.30, $rates['p_draw'], 0.001);
        $this->assertEqualsWithDelta(0.20, $rates['p_away'], 0.001);
        $this->assertSame('H', $rates['predicted']);
    }
}

