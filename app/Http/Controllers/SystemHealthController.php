<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SystemAudit;
use App\Services\Audit\SystemDataAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function index(SystemDataAuditor $auditor): View
    {
        // Fetch the most recent persisted audit, or run an on-demand audit if none exists
        $latestAudit = SystemAudit::latest()->first();

        $auditReport = $latestAudit ? $latestAudit->report_json : $auditor->audit(triggeredBy: 'web', persist: true);
        
        $pastAudits = SystemAudit::orderBy('created_at', 'desc')->take(10)->get();

        return view('system-health.index', [
            'report' => $auditReport,
            'latestAuditModel' => $latestAudit,
            'pastAudits' => $pastAudits,
        ]);
    }

    public function runAudit(Request $request, SystemDataAuditor $auditor): RedirectResponse
    {
        $report = $auditor->audit(triggeredBy: 'web', persist: true);

        $statusMsg = match ($report['status']) {
            'clean' => "System data audit completed: 100% clean across all {$report['total_checks']} domains in {$report['duration_ms']}ms.",
            'warning' => "System data audit completed with warnings: {$report['checks_warning']} warning(s) detected.",
            default => "System data audit completed: {$report['checks_failed']} failure(s) detected!",
        };

        $flashType = $report['status'] === 'critical' ? 'error' : ($report['status'] === 'warning' ? 'info' : 'success');

        return redirect()->route('system-health')->with($flashType, $statusMsg);
    }
}

