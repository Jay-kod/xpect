<?php

declare(strict_types=1);

namespace App\Services\DataSources\FootballDataCsv;

use App\Http\Clients\LoggedHttpClient;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchOdds;
use App\Services\DataSources\Contracts\DataSourceAdapter;
use App\Services\Teams\TeamResolver;
use Carbon\CarbonImmutable;
use RuntimeException;
use Throwable;

class CsvBackfillAdapter implements DataSourceAdapter
{
    public function __construct(
        protected readonly LoggedHttpClient $httpClient,
        protected readonly TeamResolver $teamResolver
    ) {
    }

    public function importMatches(
        Competition $competition,
        int $season,
        bool $fromCache = false,
        ?int $syncRunId = null
    ): array {
        $div = $competition->source_ids['csv'] ?? null;
        if (!$div) {
            throw new RuntimeException("Competition [{$competition->slug}] has no CSV division code mapped.");
        }

        // Season code format: 2024 -> 2425, 2023 -> 2324, 2020 -> 2021
        $startTwoDigits = substr((string) $season, -2);
        $endTwoDigits = str_pad((string) (((int) $startTwoDigits + 1) % 100), 2, '0', STR_PAD_LEFT);
        $seasonCode = $startTwoDigits . $endTwoDigits;

        $url = "https://www.football-data.co.uk/mmz4281/{$seasonCode}/{$div}.csv";

        $csvContent = '';
        $sourceApiCallId = null;

        // Check if pre-downloaded fixture exists
        $localFixture = base_path("tests/Fixtures/csv/{$div}_{$seasonCode}.csv");
        if (file_exists($localFixture)) {
            $csvContent = (string) file_get_contents($localFixture);
        } else {
            $response = $this->httpClient->request(
                source: 'csv',
                method: 'GET',
                url: $url,
                triggeredBy: 'backfill:history',
                countsTowardQuota: false,
                ttlSeconds: null, // finished seasons cached forever
                syncRunId: $syncRunId
            );

            if ($response->failed()) {
                throw new RuntimeException("Failed downloading CSV for season {$seasonCode}: {$response->error}");
            }

            $csvContent = $response->body;
            $sourceApiCallId = $response->apiCallId;
        }

        return $this->parseCsvContent($csvContent, $competition, $season, $sourceApiCallId);
    }

    public function parseCsvContent(
        string $csvContent,
        Competition $competition,
        int $season,
        ?int $sourceApiCallId = null
    ): array {
        // Strip BOM if present
        $clean = preg_replace('/^\xEF\xBB\xBF/', '', trim($csvContent));
        $lines = explode("\n", $clean);
        if (count($lines) < 2) {
            return ['imported' => 0, 'skipped' => 0, 'unknown_teams' => []];
        }

        $headerRow = str_getcsv(array_shift($lines));
        $headers = array_map(trim(...), $headerRow);

        $imported = 0;
        $skipped = 0;
        $unknownTeams = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $row = str_getcsv($line);
            if (count($row) < count($headers)) {
                continue;
            }

            $data = array_combine($headers, array_slice($row, 0, count($headers)));

            $homeName = trim($data['HomeTeam'] ?? '');
            $awayName = trim($data['AwayTeam'] ?? '');

            if ($homeName === '' || $awayName === '') {
                continue;
            }

            $homeId = $this->teamResolver->resolve('csv', $homeName);
            $awayId = $this->teamResolver->resolve('csv', $awayName);

            if (!$homeId) {
                $unknownTeams[$homeName] = true;
            }
            if (!$awayId) {
                $unknownTeams[$awayName] = true;
            }

            if (!$homeId || !$awayId) {
                $skipped++;
                continue;
            }

            $kickoff = $this->parseKickoff($data['Date'] ?? '', $data['Time'] ?? null);

            $fthg = isset($data['FTHG']) && $data['FTHG'] !== '' ? (int) $data['FTHG'] : null;
            $ftag = isset($data['FTAG']) && $data['FTAG'] !== '' ? (int) $data['FTAG'] : null;
            $hthg = isset($data['HTHG']) && $data['HTHG'] !== '' ? (int) $data['HTHG'] : null;
            $htag = isset($data['HTAG']) && $data['HTAG'] !== '' ? (int) $data['HTAG'] : null;

            $match = FootballMatch::updateOrCreate(
                [
                    'competition_id' => $competition->id,
                    'season' => $season,
                    'home_team_id' => $homeId,
                    'away_team_id' => $awayId,
                ],
                [
                    'kickoff_at' => $kickoff,
                    'status' => 'finished',
                    'home_goals' => $fthg,
                    'away_goals' => $ftag,
                    'ht_home_goals' => $hthg,
                    'ht_away_goals' => $htag,
                    'source_api_call_id' => $sourceApiCallId,
                ]
            );

            $this->parseAndPersistOdds($match, $data, $sourceApiCallId);

            $imported++;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'unknown_teams' => array_keys($unknownTeams),
        ];
    }

    protected function parseKickoff(string $dateStr, ?string $timeStr = null): CarbonImmutable
    {
        $dateStr = trim($dateStr);
        $timeStr = trim((string) $timeStr) ?: '15:00';

        // Check if date is DD/MM/YYYY or DD/MM/YY
        $format = strlen(explode('/', $dateStr)[2] ?? '') === 4 ? 'd/m/Y H:i' : 'd/m/y H:i';

        try {
            return CarbonImmutable::createFromFormat($format, "{$dateStr} {$timeStr}", 'Europe/London')
                ->setTimezone('UTC');
        } catch (Throwable) {
            return CarbonImmutable::now('UTC');
        }
    }

    /**
     * Parse odds for B365, Market Average, and Pinnacle, normalize implied probabilities, and persist.
     */
    public function parseAndPersistOdds(FootballMatch $match, array $data, ?int $sourceApiCallId = null): void
    {
        $bookmakers = [
            'b365' => ['H' => 'B365H', 'D' => 'B365D', 'A' => 'B365A'],
            'market_avg' => ['H' => 'AvgH', 'D' => 'AvgD', 'A' => 'AvgA'],
            'pinnacle' => ['H' => 'PSH', 'D' => 'PSD', 'A' => 'PSA'],
        ];

        foreach ($bookmakers as $bookie => $cols) {
            $h = isset($data[$cols['H']]) && is_numeric($data[$cols['H']]) ? (float) $data[$cols['H']] : null;
            $d = isset($data[$cols['D']]) && is_numeric($data[$cols['D']]) ? (float) $data[$cols['D']] : null;
            $a = isset($data[$cols['A']]) && is_numeric($data[$cols['A']]) ? (float) $data[$cols['A']] : null;

            if ($h && $d && $a && $h > 1.0 && $d > 1.0 && $a > 1.0) {
                $invH = 1.0 / $h;
                $invD = 1.0 / $d;
                $invA = 1.0 / $a;
                $overround = $invH + $invD + $invA;

                $probH = round($invH / $overround, 4);
                $probD = round($invD / $overround, 4);
                $probA = round($invA / $overround, 4);

                MatchOdds::updateOrCreate(
                    [
                        'match_id' => $match->id,
                        'bookmaker' => $bookie,
                        'source' => 'csv',
                    ],
                    [
                        'home_odds' => $h,
                        'draw_odds' => $d,
                        'away_odds' => $a,
                        'implied_home_prob' => $probH,
                        'implied_draw_prob' => $probD,
                        'implied_away_prob' => $probA,
                        'overround' => round($overround, 4),
                        'source_api_call_id' => $sourceApiCallId,
                    ]
                );
            }
        }
    }
}

