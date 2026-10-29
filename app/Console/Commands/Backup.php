<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class Backup extends Command
{
    protected $signature = 'app:backup';
    protected $description = 'Backup SQLite database to storage/backups and rotate old copies';

    public function handle(): int
    {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath === ':memory:' || empty($dbPath)) {
            $dbPath = database_path('database.sqlite');
            if (!file_exists($dbPath)) {
                touch($dbPath);
            }
        }

        if (!file_exists($dbPath)) {
            $this->error("Database file [{$dbPath}] does not exist.");
            return self::FAILURE;
        }

        $backupDir = storage_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = CarbonImmutable::now('UTC')->format('Ymd-Hi');
        $backupFile = "{$backupDir}/xpect-{$timestamp}.sqlite";

        // Perform copy
        if (!copy($dbPath, $backupFile)) {
            $this->error("Failed to copy database to [{$backupFile}].");
            return self::FAILURE;
        }

        $this->info("Database backed up to [{$backupFile}].");

        // Rotation: Keep last N copies
        $keepCount = config('football.backups.keep_count', 10);
        $files = glob("{$backupDir}/xpect-*.sqlite");

        if (count($files) > $keepCount) {
            // Sort by modified time asc (oldest first)
            usort($files, fn ($a, $b) => filemtime($a) <=> filemtime($b));

            $toDelete = array_slice($files, 0, count($files) - $keepCount);
            foreach ($toDelete as $oldFile) {
                unlink($oldFile);
                $this->line("Rotated out old backup: " . basename($oldFile));
            }
        }

        return self::SUCCESS;
    }
}
