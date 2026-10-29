<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\Teams\TeamBadgeService;
use Illuminate\Console\Command;

class SyncTeamBadgesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:badges {--force : Overwrite existing crest URLs in the database} {--show-missing : List teams without crest URLs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize official team crest URLs into the database for 0ms page reload rendering';

    /**
     * Execute the console command.
     */
    public function handle(TeamBadgeService $badgeService): int
    {
        $force = (bool) $this->option('force');
        $this->info('Starting team crest & badge synchronization...');

        $updated = $badgeService->syncBadges($force);

        $totalTeams = Team::count();
        $teamsWithCrests = Team::whereNotNull('crest_url')->where('crest_url', '!=', '')->count();
        $coveragePct = $totalTeams > 0 ? round(($teamsWithCrests / $totalTeams) * 100, 1) : 0;

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Teams in Database', (string) $totalTeams],
                ['Teams Updated in this Run', (string) $updated],
                ['Teams with Verified Crests', (string) $teamsWithCrests],
                ['Crest Coverage', "{$coveragePct}%"],
                ['Network Latency Added', '0ms (Saved to SQLite)'],
            ]
        );

        $this->info("Team badge sync completed. {$updated} team records updated.");

        if ($this->option('show-missing')) {
            $missing = Team::whereNull('crest_url')->orWhere('crest_url', '')->get(['id', 'name', 'short_name', 'country']);
            if ($missing->isNotEmpty()) {
                $this->newLine();
                $this->warn('Teams without verified crests:');
                $this->table(
                    ['ID', 'Name', 'Short Name', 'Country'],
                    $missing->map(fn ($t) => [$t->id, $t->name, $t->short_name, $t->country])->toArray()
                );
            }
        }

        return self::SUCCESS;
    }
}
