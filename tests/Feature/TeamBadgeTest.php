<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_team_badges(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create([
            'name' => 'Arsenal FC',
            'short_name' => 'Arsenal',
            'crest_url' => 'https://crests.football-data.org/57.png',
        ]);

        $away = Team::create([
            'name' => 'Chelsea FC',
            'short_name' => 'Chelsea',
            'crest_url' => 'https://crests.football-data.org/61.png',
        ]);

        FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::now('UTC')->addDays(1),
            'status' => 'scheduled',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://crests.football-data.org/57.png');
        $response->assertSee('https://crests.football-data.org/61.png');
        $response->assertSee('team-crest-img');
        $response->assertSee('Arsenal FC');
        $response->assertSee('Chelsea FC');
    }

    public function test_sync_badges_artisan_command(): void
    {
        Team::create([
            'name' => 'Liverpool FC',
            'short_name' => 'Liverpool',
            'crest_url' => null,
        ]);

        $this->artisan('sync:badges --show-missing')
            ->expectsOutputToContain('Starting team crest & badge synchronization')
            ->assertExitCode(0);

        $this->assertDatabaseHas('teams', [
            'name' => 'Liverpool FC',
            'crest_url' => 'https://crests.football-data.org/64.png',
        ]);
    }
}

