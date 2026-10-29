<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangelogTest extends TestCase
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

    public function test_changelog_page_renders_successfully(): void
    {
        $response = $this->get(route('changelog'));

        $response->assertStatus(200);
        $response->assertSee('Model Changelog &amp; Technical Documentation', false);
        $response->assertSee('v1.2.1-h2h');
        $response->assertSee('Dixon-Coles &rho; Factor', false);
        $response->assertSee('Mathematical Architecture');
        $response->assertSee('Comparison vs. Standard Statistical Baselines');
        $response->assertSee('Application Release History');
    }

    public function test_dashboard_and_sidebar_contain_links_to_changelog_and_docs(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('changelog'));
        $response->assertSee(route('docs'));
        $response->assertSee('<span>Changelog</span>', false);
        $response->assertSee('<span>Documentation</span>', false);
        $response->assertSee('Model Changelog &amp; Docs', false);
        $response->assertDontSee('sidebar-update-card');
    }
}

