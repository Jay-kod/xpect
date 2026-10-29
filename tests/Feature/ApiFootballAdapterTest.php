<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Clients\LoggedHttpClient;
use App\Models\ApiCall;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\DataSources\ApiFootball\ApiFootballAdapter;
use Carbon\CarbonImmutable;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiFootballAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected ApiFootballAdapter $adapter;
    protected Competition $pl;
    protected Team $arsenal;
    protected Team $chelsea;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);

        $this->adapter = app(ApiFootballAdapter::class);
        $this->pl = Competition::where('slug', 'premier-league')->first();
        $this->arsenal = Team::where('name', 'Arsenal FC')->first();
        $this->chelsea = Team::where('name', 'Chelsea FC')->first();
    }

    public function test_mock_mode_returns_injuries_without_http(): void
    {
        Http::preventStrayRequests();

        $result = $this->adapter->fetchInjuries($this->pl, 2024, mock: true);

        $this->assertGreaterThanOrEqual(1, $result['imported']);
        $this->assertDatabaseHas('injuries', [
            'player_name' => 'M. Ødegaard',
            'reason' => 'Ankle Injury',
        ]);
    }

    public function test_live_call_via_logged_http_client_redacts_auth_header(): void
    {
        $payload = json_encode([
            'get' => 'injuries',
            'parameters' => ['league' => '39', 'season' => '2024'],
            'errors' => [],
            'results' => 1,
            'response' => [
                [
                    'player' => ['id' => 10, 'name' => 'B. Saka', 'type' => 'Injured', 'reason' => 'Hamstring'],
                    'team' => ['id' => 42, 'name' => 'Arsenal'],
                    'fixture' => ['id' => 999, 'date' => now()->toIso8601String()],
                ],
            ],
        ]);

        Http::fake([
            'https://v3.football.api-sports.io/*' => Http::response($payload, 200, [
                'x-ratelimit-requests-remaining' => '95',
            ]),
        ]);

        config(['app.env' => 'local']);
        putenv('API_FOOTBALL_KEY=secret-token-12345');
        $_ENV['API_FOOTBALL_KEY'] = 'secret-token-12345';

        $result = $this->adapter->fetchInjuries($this->pl, 2024, mock: false);

        $this->assertSame(1, $result['imported']);

        $call = ApiCall::where('source', 'api_football')->latest()->first();
        $this->assertNotNull($call);
        $this->assertSame(200, $call->response_status);

        // Assert secret is redacted in DB
        $headersJson = json_encode($call->request_headers);
        $this->assertStringNotContainsString('secret-token-12345', $headersJson);
        $this->assertSame('[REDACTED]', $call->request_headers['x-apisports-key'] ?? null);

        putenv('API_FOOTBALL_KEY=');
        unset($_ENV['API_FOOTBALL_KEY']);
    }
}
