<?php

declare(strict_types=1);

namespace App\Services\DataSources\FootballData;

use App\Http\Clients\LoggedHttpClient;
use App\Models\ApiCall;
use App\Models\Competition;
use App\Services\DataSources\Contracts\DataSourceAdapter;
use RuntimeException;

class FootballDataAdapter implements DataSourceAdapter
{
    public function __construct(
        protected readonly LoggedHttpClient $httpClient,
        protected readonly FootballDataParser $parser
    ) {
    }

    public function importMatches(
        Competition $competition,
        int $season,
        bool $fromCache = false,
        ?int $syncRunId = null
    ): array {
        $code = $competition->source_ids['football_data'] ?? null;
        if (!$code) {
            throw new RuntimeException("Competition [{$competition->slug}] has no football_data code mapped.");
        }

        $url = "https://api.football-data.org/v4/competitions/{$code}/matches";
        $params = ['season' => (string) $season];

        // 1. Replay mode (--from-cache): re-parse from stored raw response with ZERO HTTP
        if ($fromCache) {
            $cacheKey = $this->httpClient->generateCacheKey('football_data', 'GET', $url, $params);
            $cachedCall = ApiCall::where('cache_key', $cacheKey)
                ->where('served_from_cache', false)
                ->whereBetween('response_status', [200, 299])
                ->whereNotNull('response_body')
                ->orderBy('called_at', 'desc')
                ->first();

            if (!$cachedCall) {
                throw new RuntimeException("No saved api_calls response found to replay from cache for [{$competition->slug}] season {$season}.");
            }

            return $this->parser->parseAndPersist(
                $cachedCall->response_body,
                $competition,
                $season,
                $cachedCall->id
            );
        }

        // 2. Normal / Live mode (via LoggedHttpClient)
        $headers = [];
        $key = env('FOOTBALL_DATA_API_KEY');
        if ($key) {
            $headers['X-Auth-Token'] = (string) $key;
        }

        $response = $this->httpClient->request(
            source: 'football_data',
            method: 'GET',
            url: $url,
            params: $params,
            headers: $headers,
            triggeredBy: 'sync:matches',
            countsTowardQuota: true,
            ttlSeconds: config('football.cache_ttl.current_fixtures', 43200),
            syncRunId: $syncRunId
        );

        if ($response->failed()) {
            throw new RuntimeException("football-data.org request failed (status {$response->status}): {$response->error}");
        }

        return $this->parser->parseAndPersist(
            $response->body,
            $competition,
            $season,
            $response->apiCallId
        );
    }
}

