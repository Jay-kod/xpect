<?php

declare(strict_types=1);

namespace App\Services\DataSources\ApiFootball;

use App\Http\Clients\LoggedHttpClient;
use App\Models\Competition;
use App\Models\FootballMatch;
use RuntimeException;

class ApiFootballAdapter
{
    protected string $baseUrl = 'https://v3.football.api-sports.io';

    public function __construct(
        protected readonly LoggedHttpClient $httpClient,
        protected readonly ApiFootballParser $parser
    ) {
    }

    protected function getApiKey(): ?string
    {
        $key = getenv('API_FOOTBALL_KEY') ?: env('API_FOOTBALL_KEY') ?: config('services.api_football.key');
        return !empty($key) ? (string) $key : null;
    }

    /**
     * Fetch injuries for a competition and season.
     */
    public function fetchInjuries(
        Competition $competition,
        int $season,
        bool $mock = false,
        ?int $syncRunId = null
    ): array {
        $leagueId = $competition->source_ids['api_football'] ?? 39;
        $apiKey = $this->getApiKey();

        // Mock mode for local tests and deterministic quota-free runs
        if ($mock || empty($apiKey)) {
            $mockPayload = json_encode([
                'get' => 'injuries',
                'parameters' => ['league' => (string) $leagueId, 'season' => (string) $season],
                'errors' => [],
                'results' => 4,
                'paging' => ['current' => 1, 'total' => 1],
                'response' => [
                    [
                        'player' => ['id' => 1460, 'name' => 'M. Ødegaard', 'type' => 'Missing Fixture', 'reason' => 'Ankle Injury'],
                        'team' => ['id' => 42, 'name' => 'Arsenal'],
                        'fixture' => ['id' => 1208001, 'date' => now()->toIso8601String()],
                        'league' => ['id' => $leagueId, 'season' => $season, 'name' => $competition->name],
                    ],
                    [
                        'player' => ['id' => 1461, 'name' => 'B. Saka', 'type' => 'Questionable', 'reason' => 'Hamstring'],
                        'team' => ['id' => 42, 'name' => 'Arsenal'],
                        'fixture' => ['id' => 1208001, 'date' => now()->toIso8601String()],
                        'league' => ['id' => $leagueId, 'season' => $season, 'name' => $competition->name],
                    ],
                    [
                        'player' => ['id' => 2201, 'name' => 'R. James', 'type' => 'Missing Fixture', 'reason' => 'Thigh Muscle Strain'],
                        'team' => ['id' => 49, 'name' => 'Chelsea'],
                        'fixture' => ['id' => 1208002, 'date' => now()->toIso8601String()],
                        'league' => ['id' => $leagueId, 'season' => $season, 'name' => $competition->name],
                    ],
                    [
                        'player' => ['id' => 3301, 'name' => 'Rodri', 'type' => 'Missing Fixture', 'reason' => 'ACL Knee Injury'],
                        'team' => ['id' => 50, 'name' => 'Manchester City'],
                        'fixture' => ['id' => 1208003, 'date' => now()->toIso8601String()],
                        'league' => ['id' => $leagueId, 'season' => $season, 'name' => $competition->name],
                    ],
                ],
            ]);

            return $this->parser->parseAndPersistInjuries($mockPayload);
        }

        $url = "{$this->baseUrl}/injuries";
        $params = [
            'league' => (string) $leagueId,
            'season' => (string) $season,
        ];

        $response = $this->httpClient->request(
            source: 'api_football',
            method: 'GET',
            url: $url,
            params: $params,
            headers: [
                'x-apisports-key' => (string) $apiKey,
            ],
            triggeredBy: 'sync:injuries',
            countsTowardQuota: true,
            ttlSeconds: 3600, // 1 hour TTL for injuries
            syncRunId: $syncRunId
        );

        if ($response->failed()) {
            throw new RuntimeException("API-Football request failed (status {$response->status}): {$response->error}");
        }

        return $this->parser->parseAndPersistInjuries($response->body, $response->apiCallId);
    }

    /**
     * Fetch lineups for a fixture.
     */
    public function fetchLineups(
        FootballMatch $match,
        bool $mock = false,
        ?int $syncRunId = null
    ): array {
        $fixtureId = $match->api_football_id ?: (string) (1200000 + $match->id);

        $apiKey = $this->getApiKey();

        if ($mock || empty($apiKey)) {
            $mockPayload = json_encode([
                'get' => 'fixtures/lineups',
                'parameters' => ['fixture' => $fixtureId],
                'errors' => [],
                'results' => 2,
                'paging' => ['current' => 1, 'total' => 1],
                'response' => [
                    [
                        'team' => ['id' => 42, 'name' => $match->homeTeam->short_name ?: $match->homeTeam->name],
                        'coach' => ['name' => 'Manager 1'],
                        'formation' => '4-3-3',
                        'startXI' => [
                            ['player' => ['id' => 1, 'name' => 'Player 1', 'number' => 1, 'pos' => 'G']],
                            ['player' => ['id' => 2, 'name' => 'Player 2', 'number' => 2, 'pos' => 'D']],
                            ['player' => ['id' => 3, 'name' => 'Player 3', 'number' => 3, 'pos' => 'D']],
                            ['player' => ['id' => 4, 'name' => 'Player 4', 'number' => 4, 'pos' => 'D']],
                            ['player' => ['id' => 5, 'name' => 'Player 5', 'number' => 5, 'pos' => 'D']],
                            ['player' => ['id' => 6, 'name' => 'Player 6', 'number' => 6, 'pos' => 'M']],
                            ['player' => ['id' => 7, 'name' => 'Player 7', 'number' => 7, 'pos' => 'M']],
                            ['player' => ['id' => 8, 'name' => 'Player 8', 'number' => 8, 'pos' => 'M']],
                            ['player' => ['id' => 9, 'name' => 'Player 9', 'number' => 9, 'pos' => 'F']],
                            ['player' => ['id' => 10, 'name' => 'Player 10', 'number' => 10, 'pos' => 'F']],
                            ['player' => ['id' => 11, 'name' => 'Player 11', 'number' => 11, 'pos' => 'F']],
                        ],
                        'substitutes' => [
                            ['player' => ['id' => 12, 'name' => 'Sub 1', 'number' => 12, 'pos' => 'G']],
                        ],
                    ],
                    [
                        'team' => ['id' => 49, 'name' => $match->awayTeam->short_name ?: $match->awayTeam->name],
                        'coach' => ['name' => 'Manager 2'],
                        'formation' => '4-2-3-1',
                        'startXI' => [
                            ['player' => ['id' => 21, 'name' => 'Player 21', 'number' => 1, 'pos' => 'G']],
                            ['player' => ['id' => 22, 'name' => 'Player 22', 'number' => 2, 'pos' => 'D']],
                            ['player' => ['id' => 23, 'name' => 'Player 23', 'number' => 3, 'pos' => 'D']],
                            ['player' => ['id' => 24, 'name' => 'Player 24', 'number' => 4, 'pos' => 'D']],
                            ['player' => ['id' => 25, 'name' => 'Player 25', 'number' => 5, 'pos' => 'D']],
                            ['player' => ['id' => 26, 'name' => 'Player 26', 'number' => 6, 'pos' => 'M']],
                            ['player' => ['id' => 27, 'name' => 'Player 27', 'number' => 7, 'pos' => 'M']],
                            ['player' => ['id' => 28, 'name' => 'Player 28', 'number' => 8, 'pos' => 'M']],
                            ['player' => ['id' => 29, 'name' => 'Player 29', 'number' => 9, 'pos' => 'M']],
                            ['player' => ['id' => 30, 'name' => 'Player 30', 'number' => 10, 'pos' => 'M']],
                            ['player' => ['id' => 31, 'name' => 'Player 31', 'number' => 11, 'pos' => 'F']],
                        ],
                        'substitutes' => [
                            ['player' => ['id' => 32, 'name' => 'Sub 21', 'number' => 12, 'pos' => 'G']],
                        ],
                    ],
                ],
            ]);

            return $this->parser->parseAndPersistLineups($mockPayload, $match);
        }

        $url = "{$this->baseUrl}/fixtures/lineups";
        $params = ['fixture' => $fixtureId];

        $response = $this->httpClient->request(
            source: 'api_football',
            method: 'GET',
            url: $url,
            params: $params,
            headers: [
                'x-apisports-key' => (string) $apiKey,
            ],
            triggeredBy: 'sync:lineups',
            countsTowardQuota: true,
            ttlSeconds: 900, // 15 min TTL close to kickoff
            syncRunId: $syncRunId
        );

        if ($response->failed()) {
            throw new RuntimeException("API-Football lineups request failed (status {$response->status}): {$response->error}");
        }

        return $this->parser->parseAndPersistLineups($response->body, $match, $response->apiCallId);
    }
}
