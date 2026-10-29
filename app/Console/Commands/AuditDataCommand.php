<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Audit\SystemDataAuditor;
use Illuminate\Console\Command;

class AuditDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:data
                            {--save : Persist the audit report to system_audits table}
                            {--json : Output raw JSON representation}
                            {--strict : Fail with exit code 1 if any warning is present}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit database coverage timeline, data cleanliness, zero leakage, and mathematical integrity';

    public function handle(SystemDataAuditor $auditor): int
    {
        $persist = (bool) $this->option('save');
        $json = (bool) $this->option('json');
        $strict = (bool) $this->option('strict');

        $report = $auditor->audit(triggeredBy: 'cli', persist: $persist);

        if ($json) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $report['checks_failed'] > 0 ? self::FAILURE : self::SUCCESS;
        }

        $this->newLine();
        $this->line(" <fg=white;bg=bright-blue;options=bold> XPECT DATA HEALTH & INTEGRITY AUDIT </>");
        $this->line(" <fg=gray>Automated timeline verification, zero leakage, and cleanliness inspection</>");
        $this->newLine();

        // 1. Overall Status Banner
        $statusColor = match ($report['status']) {
            'clean' => 'green',
            'warning' => 'yellow',
            default => 'red',
        };
        $statusLabel = strtoupper($report['status']);
        $this->line(" <options=bold>STATUS:</> <fg={$statusColor};options=bold>[{$statusLabel}]</> {$report['summary']}");
        $this->newLine();

        // 2. Timeline & Dataset Coverage Table
        $this->info("1. Dataset Coverage Timeline (When to When):");
        $timelineData = $report['checks']['timeline_and_coverage']['leagues'] ?? [];
        $timelineRows = [];
        foreach ($timelineData as $league) {
            $range = ($league['timeline_start'] ?? 'N/A') . ' -> ' . ($league['timeline_end'] ?? 'N/A');
            $timelineRows[] = [
                $league['name'],
                $league['country'],
                $range,
                number_format($league['total_matches']),
                number_format($league['finished_matches']),
                number_format($league['scheduled_matches']),
                implode(', ', $league['seasons']),
                $league['xg_coverage_pct'] . '%',
            ];
        }

        $this->table(
            ['Competition', 'Country', 'Timeline Range', 'Total', 'Finished', 'Scheduled', 'Seasons Covered', 'xG Coverage'],
            $timelineRows
        );
        $this->newLine();

        // 3. Integrity Checks Breakdown Table
        $this->info("2. Data Cleanliness & Constraint Verification:");
        $checkRows = [];
        foreach ($report['checks'] as $key => $check) {
            $badge = $check['passed'] ? '<fg=green;options=bold>PASS</>' : ($check['is_warning'] ? '<fg=yellow;options=bold>WARN</>' : '<fg=red;options=bold>FAIL</>');
            $checkRows[] = [
                $check['name'],
                $badge,
                $check['summary'],
            ];
        }

        $this->table(['Integrity Domain', 'Result', 'Verification Detail'], $checkRows);
        $this->newLine();

        if ($persist) {
            $this->info(" [OK] Audit record persisted to system_audits database table.");
            $this->newLine();
        }

        if ($report['checks_failed'] > 0 || ($strict && $report['checks_warning'] > 0)) {
            $this->error("Audit identified critical anomalies. Please inspect details above.");
            return self::FAILURE;
        }

        $this->info(" All integrity checks verified. Zero data leakage. 100% clean state.");
        return self::SUCCESS;
    }
}

