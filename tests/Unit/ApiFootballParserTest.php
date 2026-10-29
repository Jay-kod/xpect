<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Injury;
use App\Models\MatchLineup;
use App\Models\Team;
use App\Services\DataSources\ApiFootball\ApiFootballParser;
use App\Services\Teams\TeamResolver;
use Carbon\CarbonImmutable;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ApiFootballParserTest extends TestCase
{
    use RefreshDatabase;

    protected ApiFootballParser $parser;
    protected Competition $pl;
    protected Team $arsenal;
    protected Team $chelsea;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);

        $this->parser = new ApiFootballParser(app(TeamResolver::class));
        $this->pl = Competition::where('slug', 'premier-league')->first();
        $this->arsenal = Team::where('name', 'Arsenal FC')->first();
        $this->chelsea = Team::where('name', 'Chelsea FC')->first();
    }

    public function test_throws_exception_when_envelope_has_errors(): void
    {
        $payload = json_encode([
            'get' => 'injuries',
            'parameters' => ['league' => '39'],
            'errors' => ['token' => 'Error/Unauthorized'],
            'results' => 0,
            'response' => [],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API-Football reported errors');

        $this->parser->validateEnvelope($payload);
    }

    public function test_parses_and_persists_injuries(): void
    {
        $payload = json_encode([
            'get' => 'injuries',
            'parameters' => ['league' => '39'],
            'errors' => [],
            'results' => 1,
            'response' => [
                [
                    'player' => [
                        'id' => 1460,
                        'name' => 'Martin Ødegaard',
                        'type' => 'Missing Fixture',
                        'reason' => 'Ankle Injury',
                    ],
                    'team' => [
                        'id' => 42,
                        'name' => 'Arsenal',
                    ],
                    'fixture' => [
                        'id' => 852912,
                        'date' => '2024-09-15T13:00:00+00:00',
                    ],
                ],
            ],
        ]);

        $result = $this->parser->parseAndPersistInjuries($payload);

        $this->assertSame(1, $result['imported']);
        $this->assertEmpty($result['unknown_teams']);

        $this->assertDatabaseHas('injuries', [
            'team_id' => $this->arsenal->id,
            'player_name' => 'Martin Ødegaard',
            'player_type' => 'Missing Fixture',
            'reason' => 'Ankle Injury',
        ]);
    }

    public function test_parses_and_persists_lineups(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHour(),
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'scheduled',
        ]);

        $payload = json_encode([
            'get' => 'fixtures/lineups',
            'parameters' => ['fixture' => '123456'],
            'errors' => [],
            'results' => 1,
            'response' => [
                [
                    'team' => ['id' => 42, 'name' => 'Arsenal'],
                    'coach' => ['name' => 'M. Arteta'],
                    'formation' => '4-3-3',
                    'startXI' => [
                        ['player' => ['id' => 1, 'name' => 'Raya', 'number' => 22, 'pos' => 'G']],
                        ['player' => ['id' => 2, 'name' => 'White', 'number' => 4, 'pos' => 'D']],
                    ],
                    'substitutes' => [
                        ['player' => ['id' => 3, 'name' => 'Neto', 'number' => 32, 'pos' => 'G']],
                    ],
                ],
            ],
        ]);

        $result = $this->parser->parseAndPersistLineups($payload, $match);

        $this->assertSame(1, $result['imported']);
        $this->assertDatabaseHas('match_lineups', [
            'match_id' => $match->id,
            'team_id' => $this->arsenal->id,
            'formation' => '4-3-3',
            'coach_name' => 'M. Arteta',
            'confirmed' => true,
        ]);
    }
}
