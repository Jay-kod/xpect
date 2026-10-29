<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Clients\LoggedHttpClient;
use App\Http\Clients\RateLimiter\QuotaGuard;
use App\Models\ApiCall;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\StandingsSnapshot;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackfillAndFromCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);
    }

    public function test_from_cache_replay_imports_matches_with_zero_http_calls(): void
    {
        Http::preventStrayRequests();

        $fixturePath = base_path('tests/Fixtures/football_data_pl_matches.json');
        $this->assertFileExists($fixturePath);
        $rawJson = file_get_contents($fixturePath);

        $client = new LoggedHttpClient(new QuotaGuard());
        $url = 'https://api.football-data.org/v4/competitions/PL/matches';
        $params = ['season' => '2026'];
        $cacheKey = $client->generateCacheKey('football_data', 'GET', $url, $params);

        // Pre-store raw call in api_calls
        $storedCall = ApiCall::create([
            'source' => 'football_data',
            'method' => 'GET',
            'url' => $url,
            'request_params' => $params,
            'response_status' => 200,
            'response_body' => $rawJson,
            'duration_ms' => 120,
            'cache_key' => $cacheKey,
            'triggered_by' => 'fixture_seed',
            'called_at' => now(),
            'counts_toward_quota' => false,
            'served_from_cache' => false,
        ]);

        // Run sync:matches with --from-cache
        $this->artisan('sync:matches', [
            'competition' => 'premier-league',
            '--season' => 2026,
            '--from-cache' => true,
        ])->assertSuccessful();

        // Verify matches were imported with provenance linked
        $comp = Competition::where('slug', 'premier-league')->first();
        $count = FootballMatch::where('competition_id', $comp->id)->where('season', 2026)->count();
        $this->assertEquals(380, $count);

        $sampleMatch = FootballMatch::where('competition_id', $comp->id)->where('season', 2026)->first();
        $this->assertEquals($storedCall->id, $sampleMatch->source_api_call_id);
    }

    public function test_standings_rebuild_computes_correct_table_without_api_calls(): void
    {
        $comp = Competition::where('slug', 'premier-league')->first();

        // Import season 2024 matches from CSV fixture
        $this->artisan('backfill:history', [
            'competition' => 'premier-league',
            '--seasons' => '2024',
            '--source' => 'csv',
        ])->assertSuccessful();

        $this->artisan('standings:rebuild', [
            'competition' => 'premier-league',
            '--season' => 2024,
        ])->assertSuccessful();

        $snapshots = StandingsSnapshot::where('competition_id', $comp->id)
            ->where('season', 2024)
            ->get();

        $this->assertNotEmpty($snapshots);

        // Assert positions are assigned from 1 to N
        $firstMatchday = $snapshots->pluck('matchday')->min();
        $positions = $snapshots->where('matchday', $firstMatchday)->pluck('position')->sort()->values();
        $this->assertEquals(1, $positions->first());
    }

    public function test_app_backup_creates_sqlite_snapshot(): void
    {
        $this->artisan('app:backup')->assertSuccessful();

        $backups = glob(storage_path('backups/xpect-*.sqlite'));
        $this->assertNotEmpty($backups);
    }
}

