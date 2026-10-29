<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApiCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiInspectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_dev_inspector_is_accessible_in_local_environment(): void
    {
        $response = $this->get('/dev/api');
        $response->assertStatus(200);
        $response->assertSee('API Inspector');
    }

    public function test_dev_inspector_returns_calls_json(): void
    {
        ApiCall::create([
            'source' => 'football_data',
            'method' => 'GET',
            'url' => 'https://api.football-data.org/v4/competitions',
            'response_status' => 200,
            'duration_ms' => 45,
            'cache_key' => 'sample_key',
            'triggered_by' => 'unit-test',
            'called_at' => now(),
        ]);

        $response = $this->getJson('/dev/api/calls');
        $response->assertStatus(200);
        $response->assertJsonFragment(['source' => 'football_data']);
    }

    public function test_dev_inspector_returns_quota_json(): void
    {
        $response = $this->getJson('/dev/api/quota');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'football_data' => ['used_today', 'daily_limit', 'reserve'],
            'api_football' => ['used_today', 'daily_limit', 'reserve'],
        ]);
    }

    public function test_dev_inspector_returns_catalog_json(): void
    {
        $response = $this->getJson('/dev/api/catalog');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'football_data' => ['name', 'base_url', 'endpoints'],
            'api_football' => ['name', 'base_url', 'endpoints'],
        ]);
    }
}

