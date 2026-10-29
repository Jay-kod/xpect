<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\SyncRun;
use App\Services\DataSources\Understat\UnderstatAdapter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class SyncUnderstat extends Command
{
    protected $signature = 'sync:understat 
                            {competition=premier-league : Competition slug} 
                            {--seasons=2020,2021,2022,2023,2024 : Comma-separated seasons} 
                            {--from-cache : Replay from api_calls without network requests}';

    protected $description = 'Fetch and persist xG match statistics from Understat';

    public function handle(UnderstatAdapter $adapter): int
    {
        $compSlug = (string) $this->argument('competition');
        $competition = Competition::where('slug', $compSlug)->first();

        if (!$competition) {
            $this->error("Competition [{$compSlug}] not found.");
            return self::FAILURE;
        }

        $seasons = array_map('intval', explode(',', (string) $this->option('seasons')));
        $fromCache = (bool) $this->option('from-cache');

        $this->info("Syncing Understat xG for [{$competition->name}] across seasons: " . implode(', ', $seasons));

        $totalImported = 0;
        $totalSkipped = 0;
        $allUnknownTeams = [];

        foreach ($seasons as $season) {
            $this->line("Processing Season {$season}...");

            $syncRun = SyncRun::create([
                'command' => 'sync:understat',
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

                $totalImported += $result['imported'];
                $totalSkipped += $result['skipped'];
                if (!empty($result['unknown_teams'])) {
                    $allUnknownTeams = array_merge($allUnknownTeams, $result['unknown_teams']);
                }

                $this->info("  Season {$season}: {$result['imported']} match stats updated, {$result['skipped']} skipped.");
            } catch (Throwable $e) {
                $this->error("  Season {$season} failed: " . $e->getMessage());

                $syncRun->update([
                    'finished_at' => CarbonImmutable::now('UTC'),
                    'status' => 'failed',
                    'summary' => ['error' => $e->getMessage()],
                ]);

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info("Understat sync finished: {$totalImported} match stats recorded.");
        if (!empty($allUnknownTeams)) {
            $uniqueUnknown = array_unique($allUnknownTeams);
            $this->warn("Unknown teams (" . count($uniqueUnknown) . "): " . implode(', ', $uniqueUnknown));
        }

        return self::SUCCESS;
    }
}
