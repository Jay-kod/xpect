<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
            'kickoff_at' => CarbonImmutable::now()->subDays(2),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);
    }

    public function test_docs_page_renders_successfully(): void
    {
        $response = $this->get(route('docs'));

        $response->assertStatus(200);
        $response->assertSee('System Documentation &amp; Technical Manual', false);
        $response->assertSee('System Overview &amp; Architecture', false);
        $response->assertSee('The Prediction Engine: How Probabilities Are Calculated', false);
        $response->assertSee('Mathematical Formulations');
        $response->assertSee('Man of the Match (MOTM) Evaluation Engine', false);
        $response->assertSee('Artisan CLI Command Reference');
        $response->assertSee('php artisan sync:matches');
        $response->assertSee('Data Sources &amp; Outbound API Catalog', false);
        $response->assertSee('Standing System Invariants (Hard Rules)', false);
    }

    public function test_sidebar_links_point_to_docs(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('docs'));
        $response->assertSee('<span>Documentation</span>', false);
        $response->assertDontSee('sidebar-update-card');
        $response->assertDontSee('Documentation &bull; Analytics', false);
    }
}

