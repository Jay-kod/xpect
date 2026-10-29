<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\SyncRun;
use App\Services\DataSources\FootballData\FootballDataAdapter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class SyncMatches extends Command
{
    protected $signature = 'sync:matches {competition} {--season= : Season start year (defaults to current season)} {--from-cache : Replay from cache}';
    protected $description = 'Sync upcoming fixtures and latest results for a competition';

    public function handle(FootballDataAdapter $adapter): int
    {
        $compSlug = (string) $this->argument('competition');
        $competition = Competition::where('slug', $compSlug)->first();

        if (!$competition) {
            $this->error("Competition [{$compSlug}] not found.");
            return self::FAILURE;
        }

        $season = (int) ($this->option('season') ?: 2026);
        $fromCache = (bool) $this->option('from-cache');

        $this->info("Syncing matches for [{$competition->name}], Season {$season}...");

        $syncRun = SyncRun::create([
            'command' => 'sync:matches',
            'arguments' => [
                'competition' => $compSlug,
                'season' => $season,
                'from_cache' => $fromCache,
            ],
            'started_at' => CarbonImmutable::now('UTC'),
            'status' => 'running',
        ]);

        try {
            $result = $adapter->importMatches($competition, $season, $fromCache, $syncRun->id);

            $syncRun->update([
                'finished_at' => CarbonImmutable::now('UTC'),
                'status' => empty($result['unknown_teams']) ? 'success' : 'partial',
                'summary' => $result,
            ]);

            $this->info("Sync complete: {$result['imported']} imported/updated, {$result['skipped']} skipped.");
            if (!empty($result['unknown_teams'])) {
                $this->warn("Unknown teams: " . implode(', ', $result['unknown_teams']));
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Sync failed: " . $e->getMessage());

            $syncRun->update([
                'finished_at' => CarbonImmutable::now('UTC'),
                'status' => 'failed',
                'summary' => ['error' => $e->getMessage()],
            ]);

            return self::FAILURE;
        }
    }
}

