<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemHealthUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_health_page_renders_cleanly(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        FootballMatch::create([
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

        $response = $this->get('/system-health');

        $response->assertStatus(200);
        $response->assertSee('Data Health, Coverage &amp; Integrity Auditor', false);
        $response->assertSee('100% CLEAN');
        $response->assertSee('Dataset Coverage Timeline');
        $response->assertSee('Zero Data Leakage Verification');
        $response->assertSee('Run Live Integrity Audit');
    }

    public function test_run_audit_post_route_executes_and_redirects(): void
    {
        $response = $this->post(route('system-health.run'));

        $response->assertRedirect(route('system-health'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('system_audits', [
            'triggered_by' => 'web',
            'status' => 'clean',
        ]);
    }
}

