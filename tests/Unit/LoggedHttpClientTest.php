<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Clients\LoggedHttpClient;
use App\Http\Clients\RateLimiter\QuotaGuard;
use App\Models\ApiCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class LoggedHttpClientTest extends TestCase
{
    use RefreshDatabase;

    protected LoggedHttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new LoggedHttpClient(new QuotaGuard());
    }

    public function test_keys_never_appear_in_api_calls_headers_or_params(): void
    {
        Http::fake([
            'api.football-data.org/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $secretToken = 'secret-auth-token-xyz987';
        $secretParam = 'private-query-key-123';

        $response = $this->client->request(
            source: 'football_data',
            method: 'GET',
            url: 'https://api.football-data.org/v4/competitions',
            params: ['api_key' => $secretParam, 'regular_param' => 'allowed_value'],
            headers: [
                'X-Auth-Token' => $secretToken,
                'User-Agent' => 'Xpect-Agent',
            ],
            triggeredBy: 'test'
        );

        $this->assertTrue($response->successful());

        // Check the database
        $loggedCall = ApiCall::find($response->apiCallId);
        $this->assertNotNull($loggedCall);

        // Assert secret values are redacted
        $this->assertEquals('[REDACTED]', $loggedCall->request_headers['X-Auth-Token']);
        $this->assertEquals('Xpect-Agent', $loggedCall->request_headers['User-Agent']);
        $this->assertEquals('[REDACTED]', $loggedCall->request_params['api_key']);
        $this->assertEquals('allowed_value', $loggedCall->request_params['regular_param']);

        // Assert secrets NEVER appear anywhere in the database row
        $rawRow = json_encode($loggedCall->toArray());
        $this->assertStringNotContainsString($secretToken, $rawRow);
        $this->assertStringNotContainsString($secretParam, $rawRow);
    }

    public function test_cache_hit_creates_marker_row_and_uses_zero_quota(): void
    {
        Http::fake([
            'api.football-data.org/*' => Http::response(['competition' => 'PL'], 200),
        ]);

        // First call - executes live HTTP
        $res1 = $this->client->request(
            source: 'football_data',
            method: 'GET',
            url: 'https://api.football-data.org/v4/competitions/PL',
            params: ['season' => '2024'],
            triggeredBy: 'test',
            countsTowardQuota: true,
            ttlSeconds: 3600
        );

        $this->assertFalse($res1->servedFromCache);
        $firstCall = ApiCall::find($res1->apiCallId);
        $this->assertTrue($firstCall->counts_toward_quota);
        $this->assertFalse($firstCall->served_from_cache);
        $this->assertNotNull($firstCall->response_body);

        // Second call with same params - served from cache
        $res2 = $this->client->request(
            source: 'football_data',
            method: 'GET',
            url: 'https://api.football-data.org/v4/competitions/PL',
            params: ['season' => '2024'],
            triggeredBy: 'test',
            countsTowardQuota: true,
            ttlSeconds: 3600
        );

        $this->assertTrue($res2->servedFromCache);
        $this->assertEquals(['competition' => 'PL'], $res2->json());

        $marker = ApiCall::find($res2->apiCallId);
        $this->assertTrue($marker->served_from_cache);
        $this->assertFalse($marker->counts_toward_quota);
        $this->assertEquals($firstCall->id, $marker->cached_from_call_id);
        $this->assertNull($marker->response_body); // Marker rows store no body
    }

    public function test_detects_provider_envelope_errors_as_failure(): void
    {
        Http::fake([
            'v3.football.api-sports.io/*' => Http::response([
                'get' => 'fixtures',
                'parameters' => [],
                'errors' => ['token' => 'Invalid API key or suspended account'],
                'results' => 0,
                'response' => [],
            ], 200),
        ]);

        $res = $this->client->request(
            source: 'api_football',
            method: 'GET',
            url: 'https://v3.football.api-sports.io/fixtures',
            headers: ['x-apisports-key' => 'dummy-key'],
            triggeredBy: 'test'
        );

        $this->assertTrue($res->failed());
        $this->assertStringContainsString('Invalid API key', $res->error);

        $call = ApiCall::find($res->apiCallId);
        $this->assertNotNull($call->error);
        $this->assertStringContainsString('Invalid API key', $call->error);
    }

    public function test_api_calls_are_immutable(): void
    {
        $call = ApiCall::create([
            'source' => 'test',
            'method' => 'GET',
            'url' => 'https://example.com',
            'response_status' => 200,
            'duration_ms' => 10,
            'cache_key' => 'sample_hash',
            'triggered_by' => 'test',
            'called_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('api_calls rows are immutable.');

        $call->update(['response_status' => 500]);
    }
}

