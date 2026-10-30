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
    }

    public function test_performance_indexes_exist_on_database_tables(): void
    {
        $this->assertTrue(Schema::hasTable('predictions'));
        $this->assertTrue(Schema::hasTable('previews'));
        $this->assertTrue(Schema::hasTable('motm_evaluations'));
        $this->assertTrue(Schema::hasTable('matches'));
        $this->assertTrue(Schema::hasTable('teams'));
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
}
