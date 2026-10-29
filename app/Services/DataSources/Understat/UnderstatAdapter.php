<?php

declare(strict_types=1);

namespace App\Services\DataSources\Understat;

use App\Http\Clients\LoggedHttpClient;
use App\Models\ApiCall;
use App\Models\Competition;
use App\Services\DataSources\Contracts\DataSourceAdapter;
use RuntimeException;

class UnderstatAdapter implements DataSourceAdapter
{
    /**
     * Map internal competition slug to Understat league code.
     */
    protected array $leagueMap = [
        'premier-league' => 'EPL',
        'la-liga' => 'La_liga',
        'bundesliga' => 'Bundesliga',
        'serie-a' => 'Serie_A',
        'ligue-1' => 'Ligue_1',
    ];

    public function __construct(
        protected readonly LoggedHttpClient $httpClient,
        protected readonly UnderstatParser $parser
    ) {
    }

    public function importMatches(
        Competition $competition,
        int $season,
        bool $fromCache = false,
        ?int $syncRunId = null
    ): array {
        $league = $this->leagueMap[$competition->slug] ?? null;
        if (!$league) {
            throw new RuntimeException("Competition [{$competition->slug}] has no Understat league mapped.");
        }

        $url = "https://understat.com/getLeagueData/{$league}/{$season}";

        // 1. Replay mode (--from-cache): zero HTTP
        if ($fromCache) {
            $cacheKey = $this->httpClient->generateCacheKey('understat', 'GET', $url, []);
            $cachedCall = ApiCall::where('cache_key', $cacheKey)
                ->where('served_from_cache', false)
                ->whereBetween('response_status', [200, 299])
                ->whereNotNull('response_body')
                ->orderBy('called_at', 'desc')
                ->first();

            if (!$cachedCall) {
                throw new RuntimeException("No saved api_calls response found to replay Understat from cache for [{$competition->slug}] season {$season}.");
            }

            return $this->parser->parseAndPersist(
                $cachedCall->response_body,
                $competition,
                $season,
                $cachedCall->id
            );
        }

        // 2. Outbound request via LoggedHttpClient
        $response = $this->httpClient->request(
            source: 'understat',
            method: 'GET',
            url: $url,
            headers: [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            triggeredBy: 'sync:understat',
            countsTowardQuota: false,
            ttlSeconds: config('football.cache_ttl.current_fixtures', 43200),
            syncRunId: $syncRunId
        );

        if ($response->failed()) {
            throw new RuntimeException("Understat request failed (status {$response->status}): {$response->error}");
        }

        return $this->parser->parseAndPersist(
            $response->body,
            $competition,
            $season,
            $response->apiCallId
        );
    }
}
