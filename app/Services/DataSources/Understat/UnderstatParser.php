<?php

declare(strict_types=1);

namespace App\Services\DataSources\Understat;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchStat;
use App\Services\Teams\TeamResolver;
use Carbon\CarbonImmutable;

class UnderstatParser
{
    public function __construct(
        protected readonly TeamResolver $teamResolver
    ) {
    }

    /**
     * Parse raw Understat payload (JSON from getLeagueData or HTML fallback) and persist match_stats.
     */
    public function parseAndPersist(
        string $payload,
        Competition $competition,
        int $season,
        ?int $apiCallId = null
    ): array {
        $imported = 0;
        $skipped = 0;
        $unmatched = 0;
        $unknownTeams = [];

        $datesData = $this->extractDatesData($payload);
        if (empty($datesData)) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'unmatched' => 0,
                'unknown_teams' => [],
            ];
        }

        foreach ($datesData as $item) {
            $isResult = (bool) ($item['isResult'] ?? false);
            if (!$isResult) {
                $skipped++;
                continue;
            }

            $homeTitle = (string) ($item['h']['title'] ?? '');
            $awayTitle = (string) ($item['a']['title'] ?? '');

            $homeTeamId = $this->teamResolver->resolve('understat', $homeTitle);
            if (!$homeTeamId) {
                $unknownTeams[$homeTitle] = true;
            }

            $awayTeamId = $this->teamResolver->resolve('understat', $awayTitle);
            if (!$awayTeamId) {
                $unknownTeams[$awayTitle] = true;
            }

            if (!$homeTeamId || !$awayTeamId) {
                $skipped++;
                continue;
            }

            $datetime = (string) ($item['datetime'] ?? '');
            $kickoff = $datetime !== '' ? CarbonImmutable::parse($datetime, 'UTC') : null;

            $matchQuery = FootballMatch::where('competition_id', $competition->id)
                ->where('home_team_id', $homeTeamId)
                ->where('away_team_id', $awayTeamId);

            if ($kickoff) {
                $matchQuery->whereBetween('kickoff_at', [
                    $kickoff->subHours(48),
                    $kickoff->addHours(48),
                ]);
            } else {
                $matchQuery->where('season', $season);
            }

            $match = $matchQuery->first();
            if (!$match) {
                // Try fallback by season if schedule had shifted by more than 48 hours
                $match = FootballMatch::where('competition_id', $competition->id)
                    ->where('season', $season)
                    ->where('home_team_id', $homeTeamId)
                    ->where('away_team_id', $awayTeamId)
                    ->first();
            }

            if (!$match) {
                $unmatched++;
                continue;
            }

            $homeXg = isset($item['xG']['h']) ? (float) $item['xG']['h'] : null;
            $awayXg = isset($item['xG']['a']) ? (float) $item['xG']['a'] : null;

            // Home Team Stat
            MatchStat::updateOrCreate(
                [
                    'match_id' => $match->id,
                    'team_id' => $homeTeamId,
                    'source' => 'understat',
                ],
                [
                    'xg' => $homeXg,
                    'xga' => $awayXg,
                    'source_api_call_id' => $apiCallId,
                ]
            );

            // Away Team Stat
            MatchStat::updateOrCreate(
                [
                    'match_id' => $match->id,
                    'team_id' => $awayTeamId,
                    'source' => 'understat',
                ],
                [
                    'xg' => $awayXg,
                    'xga' => $homeXg,
                    'source_api_call_id' => $apiCallId,
                ]
            );

            $imported++;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'unmatched' => $unmatched,
            'unknown_teams' => array_keys($unknownTeams),
        ];
    }

    /**
     * Extract match array from JSON or HTML payload.
     */
    protected function extractDatesData(string $payload): array
    {
        $trimmed = trim($payload);

        // 1. If clean JSON (from getLeagueData)
        if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
            $json = json_decode($trimmed, true);
            if (is_array($json) && isset($json['dates']) && is_array($json['dates'])) {
                return $json['dates'];
            }
        }

        // 2. Fallback: Parse embedded JavaScript from HTML
        if (preg_match("/var\s+datesData\s*=\s*JSON\.parse\('([^']+)'\)/", $payload, $matches)) {
            $raw = $matches[1];
            // Decode hex escape sequences e.g. \x7B
            $decoded = preg_replace_callback('/\\\\x([0-9a-fA-F]{2})/', fn($m) => chr(hexdec($m[1])), $raw);
            $parsed = json_decode($decoded, true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return [];
    }
}
