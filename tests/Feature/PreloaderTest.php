<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreloaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_preloader_rendered_dormant_for_preactivities(): void
    {
        Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        // Activity preloader is present for background actions & preactivities, dormant on load
        $response->assertSee('id="xp-activity-preloader"', false);
        $response->assertSee('style="display: none;"', false);
        $response->assertSee('xp-preloader-brand-hub');
        $response->assertSee('xp-preloader-ambient-glow');
        $response->assertSee('xp-preloader-ring-outer');
        $response->assertSee('xp-preloader-ring-inner');
        $response->assertSee('xp-runner-sweep');
        $response->assertSee('CALIBRATING POISSON PROBABILITIES');
        $response->assertSee('id="xp-preloader-dismiss-btn"', false);

        // Skeletal loading primitives remain active simultaneously
        $response->assertSee('id="xp-nav-progress"', false);
        $response->assertSee('xp-skeleton');
    }

    public function test_activity_preloader_javascript_controller_registered(): void
    {
        Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        // JS controller API
        $response->assertSee('window.XpectPreloader = {', false);
        $response->assertSee('show: function', false);
        $response->assertSee('hide: function', false);
        $response->assertSee('setText: function', false);
        $response->assertSee('isActive: function', false);

        // Context-aware action labels
        $response->assertSee('CALIBRATING POISSON PROBABILITIES');
        $response->assertSee('SYNTHESIZING AI REASONING PREVIEW');
        $response->assertSee('RUNNING SYSTEM INTEGRITY AUDIT');
        $response->assertSee('SYNCHRONIZING FOOTBALL DATA');
        $response->assertSee('RUNNING AI PREDICTION SIMULATION');
        $response->assertSee('EVALUATING MAN OF THE MATCH');
    }

    public function test_activity_preloader_present_across_core_routes(): void
    {
        Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $routes = ['/today', '/track-record', '/settings', '/system-health'];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
            $response->assertSee('id="xp-activity-preloader"', false);
            $response->assertSee('window.XpectPreloader', false);
        }
    }
}
