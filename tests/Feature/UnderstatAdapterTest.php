<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Clients\LoggedHttpClient;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchStat;
use App\Models\Team;
use App\Services\DataSources\Understat\UnderstatAdapter;
use App\Services\DataSources\Understat\UnderstatParser;
use Carbon\CarbonImmutable;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Database\Seeders\UnderstatTeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UnderstatAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected UnderstatAdapter $adapter;
    protected Competition $pl;
    protected Team $arsenal;
    protected Team $chelsea;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);
        $this->seed(UnderstatTeamSeeder::class);

        $this->adapter = app(UnderstatAdapter::class);
        $this->pl = Competition::where('slug', 'premier-league')->first();
        $this->arsenal = Team::where('name', 'Arsenal FC')->first();
        $this->chelsea = Team::where('name', 'Chelsea FC')->first();
    }

    public function test_adapter_fetches_via_logged_http_client_and_persists_stats(): void
    {
        $kickoff = CarbonImmutable::parse('2024-08-17 14:00:00', 'UTC');
        $match = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => $kickoff,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 3,
            'away_goals' => 0,
        ]);

        $fakeJson = json_encode([
            'dates' => [
                [
                    'id' => '5001',
                    'isResult' => true,
                    'h' => ['title' => 'Arsenal'],
                    'a' => ['title' => 'Chelsea'],
                    'goals' => ['h' => '3', 'a' => '0'],
                    'xG' => ['h' => '2.80', 'a' => '0.40'],
                    'datetime' => '2024-08-17 14:00:00',
                ],
            ],
        ]);

        Http::fake([
            'https://understat.com/getLeagueData/EPL/2024' => Http::response($fakeJson, 200),
        ]);

        $summary = $this->adapter->importMatches($this->pl, 2024);

        $this->assertSame(1, $summary['imported']);
        $this->assertDatabaseHas('match_stats', [
            'match_id' => $match->id,
            'team_id' => $this->arsenal->id,
            'source' => 'understat',
            'xg' => 2.80,
            'xga' => 0.40,
        ]);

        $this->assertDatabaseHas('api_calls', [
            'source' => 'understat',
            'url' => 'https://understat.com/getLeagueData/EPL/2024',
            'response_status' => 200,
        ]);
    }

    public function test_from_cache_replay_uses_zero_http_calls(): void
    {
        $kickoff = CarbonImmutable::parse('2024-08-17 14:00:00', 'UTC');
        $match = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => $kickoff,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 1,
            'away_goals' => 1,
        ]);

        $fakeJson = json_encode([
            'dates' => [
                [
                    'id' => '5002',
                    'isResult' => true,
                    'h' => ['title' => 'Arsenal'],
                    'a' => ['title' => 'Chelsea'],
                    'goals' => ['h' => '1', 'a' => '1'],
                    'xG' => ['h' => '1.10', 'a' => '1.20'],
                    'datetime' => '2024-08-17 14:00:00',
                ],
            ],
        ]);

        $httpClient = app(LoggedHttpClient::class);
        $url = 'https://understat.com/getLeagueData/EPL/2024';
        $cacheKey = $httpClient->generateCacheKey('understat', 'GET', $url, []);

        \App\Models\ApiCall::create([
            'source' => 'understat',
            'method' => 'GET',
            'url' => $url,
            'response_status' => 200,
            'response_body' => $fakeJson,
            'counts_toward_quota' => false,
            'served_from_cache' => false,
            'triggered_by' => 'seed_cache',
            'cache_key' => $cacheKey,
            'called_at' => CarbonImmutable::now('UTC'),
        ]);

        // Disallow any outbound HTTP
        Http::preventStrayRequests();

        $summary = $this->adapter->importMatches($this->pl, 2024, fromCache: true);

        $this->assertSame(1, $summary['imported']);
        $this->assertDatabaseHas('match_stats', [
            'match_id' => $match->id,
            'team_id' => $this->arsenal->id,
            'xg' => 1.10,
        ]);
    }
}
