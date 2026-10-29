<?php

declare(strict_types=1);

namespace App\Services\DataSources\ApiFootball;

use App\Models\FootballMatch;
use App\Models\Injury;
use App\Models\MatchLineup;
use App\Services\Teams\TeamResolver;
use Carbon\CarbonImmutable;
use RuntimeException;

class ApiFootballParser
{
    public function __construct(
        protected readonly TeamResolver $teamResolver
    ) {
    }

    /**
     * Validate the API-Football envelope.
     * Per API_CATALOG.md: Check errors in the body on every call.
     */
    public function validateEnvelope(string $rawBody): array
    {
        $data = json_decode($rawBody, true);
        if (!is_array($data)) {
            throw new RuntimeException("Invalid JSON response from API-Football.");
        }

        if (!empty($data['errors'])) {
            $errorMsg = is_array($data['errors']) 
                ? json_encode($data['errors']) 
                : (string) $data['errors'];
            throw new RuntimeException("API-Football reported errors: {$errorMsg}");
        }

        return $data;
    }

    /**
     * Parse and persist injuries from /injuries response.
     */
    public function parseAndPersistInjuries(string $rawBody, ?int $apiCallId = null): array
    {
        $data = $this->validateEnvelope($rawBody);
        $items = $data['response'] ?? [];

        $imported = 0;
        $unknownTeams = [];

        foreach ($items as $item) {
            $teamName = $item['team']['name'] ?? null;
            if (!$teamName) {
                continue;
            }

            $teamId = $this->teamResolver->resolve('api_football', $teamName);
            if (!$teamId) {
                $unknownTeams[$teamName] = true;
                continue;
            }

            $playerName = $item['player']['name'] ?? 'Unknown Player';
            $playerType = $item['player']['type'] ?? null;
            $reason = $item['player']['reason'] ?? null;
            $fixtureApiId = isset($item['fixture']['id']) ? (string) $item['fixture']['id'] : null;

            $dateStr = $item['fixture']['date'] ?? null;
            $asOf = $dateStr ? CarbonImmutable::parse($dateStr, 'UTC') : CarbonImmutable::now('UTC');

            Injury::create([
                'team_id' => $teamId,
                'fixture_api_id' => $fixtureApiId,
                'player_name' => $playerName,
                'player_type' => $playerType,
                'reason' => $reason,
                'source' => 'api_football',
                'source_api_call_id' => $apiCallId,
                'as_of' => $asOf,
            ]);

            $imported++;
        }

        return [
            'imported' => $imported,
            'unknown_teams' => array_keys($unknownTeams),
        ];
    }

    /**
     * Parse and persist lineups from /fixtures/lineups response for a specific match.
     */
    public function parseAndPersistLineups(string $rawBody, FootballMatch $match, ?int $apiCallId = null): array
    {
        $data = $this->validateEnvelope($rawBody);
        $teamsLineups = $data['response'] ?? [];

        $imported = 0;
        $unknownTeams = [];

        foreach ($teamsLineups as $teamLineup) {
            $teamName = $teamLineup['team']['name'] ?? null;
            if (!$teamName) {
                continue;
            }

            $teamId = $this->teamResolver->resolve('api_football', $teamName);
            if (!$teamId) {
                $unknownTeams[$teamName] = true;
                continue;
            }

            $formation = $teamLineup['formation'] ?? null;
            $startXI = $teamLineup['startXI'] ?? [];
            $substitutes = $teamLineup['substitutes'] ?? [];
            $coachName = $teamLineup['coach']['name'] ?? null;

            MatchLineup::updateOrCreate(
                [
                    'match_id' => $match->id,
                    'team_id' => $teamId,
                    'source' => 'api_football',
                ],
                [
                    'formation' => $formation,
                    'starting_xi' => $startXI,
                    'substitutes' => $substitutes,
                    'coach_name' => $coachName,
                    'confirmed' => !empty($startXI),
                    'source_api_call_id' => $apiCallId,
                ]
            );

            $imported++;
        }

        return [
            'imported' => $imported,
            'unknown_teams' => array_keys($unknownTeams),
        ];
    }
}
