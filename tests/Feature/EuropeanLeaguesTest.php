<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\Teams\TeamResolver;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\EuropeanLeaguesTeamSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EuropeanLeaguesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);
        $this->seed(EuropeanLeaguesTeamSeeder::class);
    }

    public function test_all_five_top_european_leagues_are_active(): void
    {
        $activeSlugs = Competition::where('is_active', true)->pluck('slug')->all();

        $this->assertContains('premier-league', $activeSlugs);
        $this->assertContains('la-liga', $activeSlugs);
        $this->assertContains('bundesliga', $activeSlugs);
        $this->assertContains('serie-a', $activeSlugs);
        $this->assertContains('ligue-1', $activeSlugs);
        $this->assertContains('champions-league', $activeSlugs);
    }

    public function test_champions_league_and_epl_render_in_sidebar_and_dashboard(): void
    {
        $response = $this->get('/?competition=champions-league');
        $response->assertStatus(200);
        $response->assertSee('Champions League');
        $response->assertSee('Premier League (EPL)');
        $response->assertSee('UCL');
        $response->assertSee('EPL');
    }

    public function test_team_resolver_resolves_teams_across_all_leagues_via_csv_aliases(): void
    {
        $resolver = app(TeamResolver::class);

        // Spain
        $realMadridId = $resolver->resolve('csv', 'Real Madrid');
        $this->assertNotNull($realMadridId);
        $this->assertSame('Real Madrid CF', Team::find($realMadridId)?->name);

        $barcaId = $resolver->resolve('csv', 'Barcelona');
        $this->assertNotNull($barcaId);
        $this->assertSame('FC Barcelona', Team::find($barcaId)?->name);

        // Germany
        $bayernId = $resolver->resolve('csv', 'Bayern Munich');
        $this->assertNotNull($bayernId);
        $this->assertSame('FC Bayern München', Team::find($bayernId)?->name);

        $dortmundId = $resolver->resolve('csv', 'Dortmund');
        $this->assertNotNull($dortmundId);
        $this->assertSame('Borussia Dortmund', Team::find($dortmundId)?->name);

        // Italy
        $interId = $resolver->resolve('csv', 'Inter');
        $this->assertNotNull($interId);
        $this->assertSame('FC Internazionale Milano', Team::find($interId)?->name);

        $juveId = $resolver->resolve('csv', 'Juventus');
        $this->assertNotNull($juveId);
        $this->assertSame('Juventus FC', Team::find($juveId)?->name);

        // France
        $psgId = $resolver->resolve('csv', 'Paris SG');
        $this->assertNotNull($psgId);
        $this->assertSame('Paris Saint-Germain FC', Team::find($psgId)?->name);

        $marseilleId = $resolver->resolve('csv', 'Marseille');
        $this->assertNotNull($marseilleId);
        $this->assertSame('Olympique de Marseille', Team::find($marseilleId)?->name);
    }
}
