<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacktestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backtest_runs_and_evaluates_against_baselines(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        // Create 15 finished matches in season 2023
        for ($i = 1; $i <= 15; $i++) {
            FootballMatch::create([
                'competition_id' => $comp->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'season' => 2023,
                'matchday' => $i,
                'kickoff_at' => CarbonImmutable::parse('2023-08-01 15:00:00')->addWeeks($i),
                'status' => 'finished',
                'home_goals' => ($i % 2 === 0) ? 2 : 1,
                'away_goals' => ($i % 3 === 0) ? 2 : 0,
            ]);
        }

        $this->artisan('backtest premier-league --seasons=2023')
            ->expectsOutputToContain('XPECT WALK-FORWARD MODEL BACKTEST')
            ->expectsOutputToContain('EVALUATION RESULTS')
            ->expectsOutputToContain('BASELINE COMPARISON SUMMARY')
            ->expectsOutputToContain('CALIBRATION TABLE')
            ->assertExitCode(0);
    }
}

