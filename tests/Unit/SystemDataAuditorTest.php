<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Team;
use App\Services\Audit\SystemDataAuditor;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemDataAuditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_runs_and_passes_on_valid_data(): void
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
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-08-15 15:00:00'),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2026-08-15 15:00:00'),
            'p_home' => 0.5000,
            'p_draw' => 0.2500,
            'p_away' => 0.2500,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 1.10,
            'features' => [],
        ]);

        $auditor = new SystemDataAuditor();
        $report = $auditor->audit(triggeredBy: 'test', persist: true);

        $this->assertEquals('clean', $report['status']);
        $this->assertEquals(8, $report['total_checks']);
        $this->assertEquals(8, $report['checks_passed']);
        $this->assertEquals(0, $report['checks_failed']);
        $this->assertDatabaseHas('system_audits', ['status' => 'clean']);
    }

    public function test_audit_detects_data_leakage(): void
    {
        $comp = Competition::create([
            'slug' => 'la-liga',
            'name' => 'La Liga',
            'country' => 'Spain',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Real Madrid', 'short_name' => 'Real Madrid']);
        $away = Team::create(['name' => 'Barcelona', 'short_name' => 'Barcelona']);

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::parse('2026-10-25 20:00:00'),
            'status' => 'scheduled',
        ]);

        // Deliberate data leakage: as_of is 2 hours AFTER kickoff
        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2026-10-25 22:00:00'),
            'p_home' => 0.6000,
            'p_draw' => 0.2000,
            'p_away' => 0.2000,
            'exp_home_goals' => 2.00,
            'exp_away_goals' => 1.00,
            'features' => [],
        ]);

        $auditor = new SystemDataAuditor();
        $report = $auditor->audit(triggeredBy: 'test', persist: false);

        $this->assertEquals('critical', $report['status']);
        $this->assertFalse($report['checks']['zero_data_leakage']['passed']);
        $this->assertEquals(1, $report['checks']['zero_data_leakage']['violations_count']);
    }
}

