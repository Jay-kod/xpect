<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationUiTest extends TestCase
{
    use RefreshDatabase;

    private Competition $comp;
    private Team $teamA;
    private Team $teamB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comp = Competition::create([
            'slug' => 'la-liga',
            'name' => 'La Liga',
            'country' => 'Spain',
            'is_active' => true,
        ]);

        $this->teamA = Team::create(['name' => 'Real Madrid CF', 'short_name' => 'Real Madrid']);
        $this->teamB = Team::create(['name' => 'FC Barcelona', 'short_name' => 'Barcelona']);

        // Create 25 matches for pagination (20 per page)
        $now = CarbonImmutable::now('UTC');
        for ($i = 1; $i <= 25; $i++) {
            FootballMatch::create([
                'competition_id' => $this->comp->id,
                'home_team_id' => $this->teamA->id,
                'away_team_id' => $this->teamB->id,
                'season' => 2026,
                'matchday' => $i,
                'kickoff_at' => $now->addDays($i),
                'status' => 'scheduled',
            ]);
        }
    }

    public function test_dashboard_renders_custom_pagination_with_prev_and_next_buttons(): void
    {
        $response = $this->get('/?competition=la-liga&season=2026&status=all');

        $response->assertStatus(200);
        $response->assertSee('xp-pagination-container');
        $response->assertSee('Showing');
        $response->assertSee('1');
        $response->assertSee('20');
        $response->assertSee('25');
        $response->assertSee('Page 1 of 2');

        // On page 1: Previous is disabled, Next is active link
        $response->assertSee('xp-page-btn xp-page-prev disabled', false);
        $response->assertSee('Previous');
        $response->assertSee('xp-page-btn xp-page-next active-btn', false);
        $response->assertSee('Next');
        $response->assertSee('page=2');
    }

    public function test_dashboard_page_two_enables_previous_and_disables_next(): void
    {
        $response = $this->get('/?competition=la-liga&season=2026&status=all&page=2');

        $response->assertStatus(200);
        $response->assertSee('xp-pagination-container');
        $response->assertSee('Showing');
        $response->assertSee('21');
        $response->assertSee('25');
        $response->assertSee('Page 2 of 2');

        // On page 2: Previous is active link, Next is disabled
        $response->assertSee('xp-page-btn xp-page-prev active-btn', false);
        $response->assertSee('page=1');
        $response->assertSee('xp-page-btn xp-page-next disabled', false);
    }
}
