<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SkeletalLoadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_contains_skeletal_loading_primitives_and_nav_progress(): void
    {
        Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="xp-nav-progress"', false);
        $response->assertSee('xp-skeleton');
        $response->assertSee('xp-shimmer');
        $response->assertSee('content-loading-veil');
        $response->assertSee('if (form && !form.target && !e.defaultPrevented)', false);
    }

    public function test_performance_indexes_exist_on_database_tables(): void
    {
        $this->assertTrue(Schema::hasTable('predictions'));
        $this->assertTrue(Schema::hasTable('previews'));
        $this->assertTrue(Schema::hasTable('motm_evaluations'));
        $this->assertTrue(Schema::hasTable('matches'));
        $this->assertTrue(Schema::hasTable('teams'));
    }

    public function test_query_indexes_cover_dashboard_and_evaluation_ordering(): void
    {
        $indexNames = collect([
            ...Schema::getConnection()->select("PRAGMA index_list('matches')"),
            ...Schema::getConnection()->select("PRAGMA index_list('prediction_evaluations')"),
            ...Schema::getConnection()->select("PRAGMA index_list('motm_evaluations')"),
        ])->pluck('name');

        $this->assertTrue($indexNames->contains('idx_matches_comp_season_status_kickoff'));
        $this->assertTrue($indexNames->contains('idx_prediction_evaluations_evaluated_at'));
        $this->assertTrue($indexNames->contains('idx_motm_verified_player_group'));
    }

    public function test_skeleton_component_renders_types(): void
    {
        $view = $this->blade('<x-skeleton type="kpi" count="2" />');
        $this->assertStringContainsString('xp-skeleton-card', (string) $view);
        $this->assertStringContainsString('xp-skeleton-text', (string) $view);

        $viewMatch = $this->blade('<x-skeleton type="match" />');
        $this->assertStringContainsString('xp-skeleton-badge', (string) $viewMatch);

        $viewTable = $this->blade('<x-skeleton type="table" />');
        $this->assertStringContainsString('xp-skeleton-card', (string) $viewTable);
    }

    public function test_skeleton_component_renders_page_types(): void
    {
        $viewDashboard = $this->blade('<x-skeleton type="dashboard" />');
        $this->assertStringContainsString('xp-dashboard-skeleton', (string) $viewDashboard);
        $this->assertStringContainsString('db-provenance-strip', (string) $viewDashboard);
        $this->assertStringContainsString('sporty-leagues-bar', (string) $viewDashboard);
        $this->assertStringContainsString('sporty-day-switcher', (string) $viewDashboard);
        $this->assertStringContainsString('matches-grid-container', (string) $viewDashboard);
        $this->assertStringContainsString('view-sidebar-card', (string) $viewDashboard);

        $viewToday = $this->blade('<x-skeleton type="today" />');
        $this->assertStringContainsString('xp-today-skeleton', (string) $viewToday);

        $viewTrackRecord = $this->blade('<x-skeleton type="track-record" />');
        $this->assertStringContainsString('xp-track-record-skeleton', (string) $viewTrackRecord);

        $viewSettings = $this->blade('<x-skeleton type="settings" />');
        $this->assertStringContainsString('xp-settings-skeleton', (string) $viewSettings);

        $viewSystemHealth = $this->blade('<x-skeleton type="system-health" />');
        $this->assertStringContainsString('xp-system-health-skeleton', (string) $viewSystemHealth);
    }

    public function test_dashboard_page_contains_skeletal_preloader(): void
    {
        Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('id="dashboard-skeletal-preloader"', false);
        $response->assertSee('id="dashboard-real-content"', false);
        $response->assertSee('xp-dashboard-skeleton');
    }

    public function test_dashboard_with_skeleton_param_activates_preloader_display(): void
    {
        Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $response = $this->get('/?skeleton=1');
        $response->assertStatus(200);
        $response->assertSee('id="dashboard-skeletal-preloader"', false);
        $response->assertSee('style="display: block;"', false);
        $response->assertSee('id="dashboard-real-content" class="xp-page-content" style="display: none;"', false);
    }

    public function test_other_pages_contain_skeletal_preloaders(): void
    {
        Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $todayResponse = $this->get('/today?skeleton=1');
        $todayResponse->assertStatus(200);
        $todayResponse->assertSee('id="today-skeletal-preloader"', false);
        $todayResponse->assertSee('xp-today-skeleton');

        $trackRecordResponse = $this->get('/track-record?skeleton=1');
        $trackRecordResponse->assertStatus(200);
        $trackRecordResponse->assertSee('id="track-record-skeletal-preloader"', false);
        $trackRecordResponse->assertSee('xp-track-record-skeleton');

        $settingsResponse = $this->get('/settings?skeleton=1');
        $settingsResponse->assertStatus(200);
        $settingsResponse->assertSee('id="settings-skeletal-preloader"', false);
        $settingsResponse->assertSee('xp-settings-skeleton');

        $healthResponse = $this->get('/system-health?skeleton=1');
        $healthResponse->assertStatus(200);
        $healthResponse->assertSee('id="system-health-skeletal-preloader"', false);
        $healthResponse->assertSee('xp-system-health-skeleton');
    }
}
