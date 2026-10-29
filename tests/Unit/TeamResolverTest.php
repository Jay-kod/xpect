<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Team;
use App\Models\TeamAlias;
use App\Services\Teams\TeamResolver;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TeamResolverTest extends TestCase
{
    use RefreshDatabase;

    protected TeamResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TeamSeeder::class);
        $this->resolver = new TeamResolver();
    }

    public function test_resolves_football_data_canonical_name(): void
    {
        $teamId = $this->resolver->resolve('football_data', 'Arsenal FC');
        $this->assertNotNull($teamId);

        $team = Team::find($teamId);
        $this->assertEquals('Arsenal FC', $team->name);
    }

    public function test_resolves_csv_alias(): void
    {
        $forestId = $this->resolver->resolve('csv', "Nott'm Forest");
        $this->assertNotNull($forestId);

        $team = Team::find($forestId);
        $this->assertEquals('Nottingham Forest FC', $team->name);

        $manCityId = $this->resolver->resolve('csv', 'Man City');
        $this->assertNotNull($manCityId);
        $this->assertEquals('Manchester City FC', Team::find($manCityId)->name);
    }

    public function test_returns_null_and_never_auto_creates_for_unknown_name(): void
    {
        $teamsCountBefore = Team::count();
        $aliasesCountBefore = TeamAlias::count();

        $resolved = $this->resolver->resolve('football_data', 'NonExistent FC');
        $this->assertNull($resolved);

        $this->assertEquals($teamsCountBefore, Team::count());
        $this->assertEquals($aliasesCountBefore, TeamAlias::count());
    }

    public function test_resolve_or_fail_throws_exception_on_unknown(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown team name [Mystery Team] for source [csv]');

        $this->resolver->resolveOrFail('csv', 'Mystery Team');
    }
}

