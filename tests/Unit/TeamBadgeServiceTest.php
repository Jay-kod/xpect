<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Team;
use App\Models\TeamAlias;
use App\Services\Teams\TeamBadgeRegistry;
use App\Services\Teams\TeamBadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamBadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_resolves_known_crests(): void
    {
        $this->assertEquals('https://crests.football-data.org/57.png', TeamBadgeRegistry::resolve('Arsenal FC'));
        $this->assertEquals('https://crests.football-data.org/65.png', TeamBadgeRegistry::resolve('Man City'));
        $this->assertEquals('https://crests.football-data.org/86.png', TeamBadgeRegistry::resolve('Real Madrid'));
        $this->assertEquals('https://crests.football-data.org/5.png', TeamBadgeRegistry::resolve('Bayern Munich'));
        $this->assertEquals('https://crests.football-data.org/108.png', TeamBadgeRegistry::resolve('Inter Milan'));
        $this->assertEquals('https://crests.football-data.org/524.png', TeamBadgeRegistry::resolve('Paris Saint-Germain'));
    }

    public function test_registry_normalizes_accented_and_punctuated_names(): void
    {
        $this->assertEquals('atletico madrid', TeamBadgeRegistry::normalize('Atlético Madrid'));
        $this->assertEquals('brighton hove albion', TeamBadgeRegistry::normalize('Brighton & Hove Albion'));
        $this->assertEquals('fc bayern munchen', TeamBadgeRegistry::normalize('FC Bayern München'));
        $this->assertEquals('deportivo alaves', TeamBadgeRegistry::normalize('Deportivo Alavés'));
    }

    public function test_team_model_generates_initials(): void
    {
        $arsenal = new Team(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $this->assertEquals('ARS', $arsenal->initials);

        $manCity = new Team(['name' => 'Manchester City FC', 'short_name' => 'Man City']);
        $this->assertEquals('MC', $manCity->initials);

        $realBetis = new Team(['name' => 'Real Betis Balompié', 'short_name' => 'Real Betis']);
        $this->assertEquals('RB', $realBetis->initials);

        $inter = new Team(['name' => 'FC Internazionale Milano', 'short_name' => 'Inter']);
        $this->assertEquals('INT', $inter->initials);
    }

    public function test_service_resolves_crest_and_syncs_to_database(): void
    {
        $team = Team::create([
            'name' => 'Arsenal FC',
            'short_name' => 'Arsenal',
            'country' => 'England',
            'crest_url' => null,
        ]);

        $service = new TeamBadgeService();
        $resolved = $service->resolveCrest($team);

        $this->assertEquals('https://crests.football-data.org/57.png', $resolved);

        $count = $service->syncBadges(force: false);
        $this->assertEquals(1, $count);

        $team->refresh();
        $this->assertEquals('https://crests.football-data.org/57.png', $team->crest_url);
    }

    public function test_service_resolves_crest_via_alias(): void
    {
        $team = Team::create([
            'name' => 'The Gunners',
            'short_name' => 'Gunners',
            'country' => 'England',
            'crest_url' => null,
        ]);

        TeamAlias::create([
            'team_id' => $team->id,
            'source' => 'football_data',
            'alias' => 'Arsenal FC',
        ]);

        $service = new TeamBadgeService();
        $resolved = $service->resolveCrest($team);

        $this->assertEquals('https://crests.football-data.org/57.png', $resolved);
    }
}
