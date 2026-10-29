<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\SyncRun;
use App\Services\DataSources\FootballData\FootballDataAdapter;
use App\Services\DataSources\FootballDataCsv\CsvBackfillAdapter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class BackfillHistory extends Command
{
    protected $signature = 'backfill:history {competition} {--seasons= : Comma-separated list of seasons, e.g. 2020,2021,2022,2023,2024} {--from-cache : Replay from stored api_calls rows without HTTP} {--source= : Override source (football_data or csv)}';
    protected $description = 'Backfill historical matches for a competition';

    public function handle(
        FootballDataAdapter $footballDataAdapter,
        CsvBackfillAdapter $csvAdapter
    ): int {
        $compSlug = (string) $this->argument('competition');
        $competition = Competition::where('slug', $compSlug)->first();

        if (!$competition) {
            $this->error("Competition [{$compSlug}] not found.");
            return self::FAILURE;
        }

        $seasonsArg = $this->option('seasons');
        $seasons = $seasonsArg 
            ? array_map('intval', explode(',', (string) $seasonsArg))
            : [2020, 2021, 2022, 2023, 2024];

        $fromCache = (bool) $this->option('from-cache');
        $forcedSource = $this->option('source');

        $this->info("Starting historical backfill for [{$competition->name}] across seasons: " . implode(', ', $seasons));

        $syncRun = SyncRun::create([
            'command' => 'backfill:history',
            'arguments' => [
                'competition' => $compSlug,
                'seasons' => $seasons,
                'from_cache' => $fromCache,
                'source' => $forcedSource,
            ],
            'started_at' => CarbonImmutable::now('UTC'),
            'status' => 'running',
        ]);

        $summary = [
            'total_imported' => 0,
            'total_skipped' => 0,
            'unknown_teams' => [],
            'seasons' => [],
        ];

        foreach ($seasons as $season) {
            $this->line("--> Processing season {$season}...");

            try {
                // Decide adapter based on season availability
                // football-data free key only has 2026 and 2023. Other seasons use CSV adapter.
                $adapter = ($forcedSource === 'football_data' || ($season === 2023 && $forcedSource !== 'csv'))
                    ? $footballDataAdapter
                    : $csvAdapter;

                $res = $adapter->importMatches($competition, $season, $fromCache, $syncRun->id);

                $summary['total_imported'] += $res['imported'];
                $summary['total_skipped'] += $res['skipped'];
                if (!empty($res['unknown_teams'])) {
                    $summary['unknown_teams'] = array_unique(array_merge($summary['unknown_teams'], $res['unknown_teams']));
                }
                $summary['seasons'][$season] = $res;

                $this->info("    Season {$season}: {$res['imported']} matches imported ({$res['skipped']} skipped).");
            } catch (Throwable $e) {
                $this->error("    Season {$season} failed: " . $e->getMessage());
                $summary['seasons'][$season] = ['error' => $e->getMessage()];
            }
        }

        $syncRun->update([
            'finished_at' => CarbonImmutable::now('UTC'),
            'status' => empty($summary['unknown_teams']) ? 'success' : 'partial',
            'summary' => $summary,
        ]);

        $this->newLine();
        $this->info("Backfill completed! Total matches imported: {$summary['total_imported']}.");
        if (!empty($summary['unknown_teams'])) {
            $this->warn("Unknown teams encountered: " . implode(', ', $summary['unknown_teams']));
        }

        // Run backup after backfill per ARCHITECTURE.md rule
        $this->call('app:backup');

        return self::SUCCESS;
    }
}
