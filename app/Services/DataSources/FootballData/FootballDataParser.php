<?php

declare(strict_types=1);

namespace App\Services\DataSources\FootballData;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Services\Teams\TeamResolver;
use Carbon\CarbonImmutable;

class FootballDataParser
{
    public function __construct(
        protected readonly TeamResolver $teamResolver
    ) {
    }

    public function parseAndPersist(
        array|string $rawPayload,
        Competition $competition,
        int $season,
        ?int $sourceApiCallId = null
    ): array {
        $data = is_array($rawPayload) ? $rawPayload : json_decode($rawPayload, true);
        $matches = $data['matches'] ?? [];

        $imported = 0;
        $skipped = 0;
        $unknownTeams = [];

        foreach ($matches as $m) {
            $homeRaw = $m['homeTeam']['name'] ?? '';
            $awayRaw = $m['awayTeam']['name'] ?? '';

            $homeId = $this->teamResolver->resolve('football_data', $homeRaw);
            $awayId = $this->teamResolver->resolve('football_data', $awayRaw);

            if (!$homeId) {
                $unknownTeams[$homeRaw] = true;
            }
            if (!$awayId) {
                $unknownTeams[$awayRaw] = true;
            }

            if (!$homeId || !$awayId) {
                $skipped++;
                continue;
            }

            $status = match ($m['status'] ?? '') {
                'FINISHED' => 'finished',
                'IN_PLAY', 'PAUSED' => 'live',
                'POSTPONED' => 'postponed',
                'CANCELLED' => 'cancelled',
                default => 'scheduled',
            };

            $kickoff = isset($m['utcDate']) 
                ? CarbonImmutable::parse($m['utcDate'])->setTimezone('UTC')
                : null;

            FootballMatch::updateOrCreate(
                [
                    'football_data_id' => (string) $m['id'],
                ],
                [
                    'competition_id' => $competition->id,
                    'season' => $season,
                    'matchday' => $m['matchday'] ?? null,
                    'kickoff_at' => $kickoff,
                    'home_team_id' => $homeId,
                    'away_team_id' => $awayId,
                    'status' => $status,
                    'home_goals' => $m['score']['fullTime']['home'] ?? null,
                    'away_goals' => $m['score']['fullTime']['away'] ?? null,
                    'ht_home_goals' => $m['score']['halfTime']['home'] ?? null,
                    'ht_away_goals' => $m['score']['halfTime']['away'] ?? null,
                    'source_api_call_id' => $sourceApiCallId,
                ]
            );

            $imported++;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'unknown_teams' => array_keys($unknownTeams),
        ];
    }
}

