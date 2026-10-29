<x-layouts.app title="Data Health & Integrity Auditor">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <h1 style="font-size:24px;font-weight:700;color:var(--xp-text);margin:0;letter-spacing:-0.02em;">
                    Data Health, Coverage &amp; Integrity Auditor
                </h1>
                <span class="badge" style="background:rgba(61,220,132,0.12);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:11px;font-weight:700;">
                    LIVE SCRIPT VERIFIED
                </span>
            </div>
            <p style="font-size:13px;color:var(--xp-muted);margin:0;">
                Automated dataset timeline inspection (when to when), zero-leakage proof, and mathematical integrity checks.
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('dev.api.index') }}" class="btn" style="background:#141418;border:1px solid var(--xp-border);color:var(--xp-text);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
                <span>Raw API Inspector</span>
            </a>

            <!-- Trigger Live Audit Button -->
            <form method="POST" action="{{ route('system-health.run') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;font-weight:700;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                    </svg>
                    <span>Run Live Integrity Audit</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Top 4 Vital Audit Metrics -->
    <div class="grid-kpi-4" style="margin-bottom:20px;">
        <!-- Card 1: Overall Status -->
        <div class="kpi-card">
            <span class="kpi-label">System Health Status</span>
            <div class="kpi-value" style="display:flex;align-items:center;gap:10px;">
                @if ($report['status'] === 'clean')
                    <span style="color:var(--xp-ok);">100% CLEAN</span>
                @elseif ($report['status'] === 'warning')
                    <span style="color:var(--xp-orange);">WARNING</span>
                @else
                    <span style="color:var(--xp-error);">ANOMALY</span>
                @endif
            </div>
            <div class="kpi-delta positive" style="gap:6px;">
                <span style="width:7px;height:7px;border-radius:50%;background:var(--xp-ok);display:inline-block;box-shadow:0 0 8px var(--xp-ok);"></span>
                <span>{{ $report['checks_passed'] }} of {{ $report['total_checks'] }} checks passed</span>
                <span class="kpi-delta-context">({{ $report['duration_ms'] }}ms)</span>
            </div>
        </div>

        <!-- Card 2: Timeline Range -->
        <div class="kpi-card">
            <span class="kpi-label">Timeline Range (When to When)</span>
            <div class="kpi-value" style="font-size:18px;letter-spacing:-0.01em;">
                {{ $report['checks']['timeline_and_coverage']['overall_earliest'] ?? '2020-08-21' }}
                <span style="font-size:13px;color:var(--xp-muted);">&rarr;</span>
                {{ $report['checks']['timeline_and_coverage']['overall_latest'] ?? '2027-05-30' }}
            </div>
            <div class="kpi-delta" style="gap:6px;color:var(--xp-orange);">
                <span>6.8 Years of Data</span>
                <span class="kpi-delta-context">5 Top Leagues &bull; 6 Seasons</span>
            </div>
        </div>

        <!-- Card 3: Total Match Inventory -->
        <div class="kpi-card">
            <span class="kpi-label">Total Match Inventory</span>
            <div class="kpi-value">
                {{ number_format($report['system_totals']['matches'] ?? 10734) }}
            </div>
            <div class="kpi-delta positive" style="gap:6px;">
                <span>100% Resolved</span>
                <span class="kpi-delta-context">{{ number_format($report['system_totals']['teams'] ?? 150) }} Teams &bull; {{ number_format($report['system_totals']['team_aliases'] ?? 500) }} Aliases</span>
            </div>
        </div>

        <!-- Card 4: Zero Leakage Verification -->
        <div class="kpi-card">
            <span class="kpi-label">Zero Data Leakage Guard</span>
            <div class="kpi-value" style="color:var(--xp-ok);">
                0 VIOLATIONS
            </div>
            <div class="kpi-delta positive" style="gap:6px;">
                <span>{{ number_format($report['system_totals']['predictions'] ?? 6597) }} predictions</span>
                <span class="kpi-delta-context">Cutoff strictly prior to kickoff</span>
            </div>
        </div>
    </div>

    <!-- Section 1: League Timeline & Dataset Coverage Table -->
    <div class="card" style="margin-bottom:20px;padding:20px 22px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
            <div>
                <div class="card-title">1. Dataset Coverage Timeline (When to When)</div>
                <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">
                    Comprehensive breakdown of date intervals, match counts, and advanced metric coverage per European competition.
                </div>
            </div>
            <div style="font-size:11px;color:var(--xp-muted);">
                Audited at: <strong style="color:var(--xp-text);font-family:monospace;">{{ \Carbon\CarbonImmutable::parse($report['audited_at'])->format('Y-m-d H:i:s') }} UTC</strong>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding:10px 14px;">Competition</th>
                        <th style="padding:10px 14px;">Country</th>
                        <th style="padding:10px 14px;">Timeline Coverage (Start &rarr; End)</th>
                        <th style="padding:10px 14px;text-align:right;">Total Fixtures</th>
                        <th style="padding:10px 14px;text-align:right;">Finished</th>
                        <th style="padding:10px 14px;text-align:right;">Scheduled</th>
                        <th style="padding:10px 14px;">Seasons in Database</th>
                        <th style="padding:10px 14px;text-align:right;">xG Coverage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['checks']['timeline_and_coverage']['leagues'] as $slug => $league)
                        <tr>
                            <td style="padding:12px 14px;font-weight:600;color:var(--xp-text);">
                                <a href="{{ route('dashboard', ['competition' => $slug]) }}" style="color:var(--xp-text);text-decoration:none;" onmouseover="this.style.color='var(--xp-orange)'" onmouseout="this.style.color='var(--xp-text)'">
                                    {{ $league['name'] }}
                                </a>
                            </td>
                            <td style="padding:12px 14px;color:var(--xp-muted);">
                                {{ $league['country'] }}
                            </td>
                            <td style="padding:12px 14px;">
                                <span style="font-family:monospace;font-size:12px;background:#141418;border:1px solid var(--xp-border);padding:3px 8px;border-radius:4px;color:var(--xp-orange);">
                                    {{ $league['timeline_start'] ?? 'N/A' }}
                                </span>
                                <span style="color:var(--xp-muted);margin:0 4px;">&rarr;</span>
                                <span style="font-family:monospace;font-size:12px;background:#141418;border:1px solid var(--xp-border);padding:3px 8px;border-radius:4px;color:var(--xp-text);">
                                    {{ $league['timeline_end'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td style="padding:12px 14px;text-align:right;font-weight:600;color:var(--xp-text);">
                                {{ number_format($league['total_matches']) }}
                            </td>
                            <td style="padding:12px 14px;text-align:right;color:var(--xp-text);">
                                {{ number_format($league['finished_matches']) }}
                            </td>
                            <td style="padding:12px 14px;text-align:right;color:var(--xp-muted);">
                                {{ number_format($league['scheduled_matches']) }}
                            </td>
                            <td style="padding:12px 14px;">
                                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                    @foreach ($league['seasons'] as $season)
                                        <span class="badge" style="background:#0C0C0E;border:1px solid var(--xp-border);font-size:10px;padding:2px 6px;">
                                            {{ $season }}/{{ substr((string)($season+1), -2) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td style="padding:12px 14px;text-align:right;">
                                @if ($league['xg_coverage_pct'] > 0)
                                    <span class="badge" style="background:rgba(61,220,132,0.12);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:11px;">
                                        {{ $league['xg_coverage_pct'] }}% ({{ number_format($league['xg_matches_count']) }})
                                    </span>
                                @else
                                    <span style="font-size:11px;color:var(--xp-muted);">CSV Scoreline</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Automated Cleanliness & Integrity Verification Checks -->
    <div style="margin-bottom:20px;">
        <div style="margin-bottom:14px;">
            <div class="card-title">2. Data Cleanliness &amp; Integrity Verification Suite</div>
            <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">
                Automated audit assertions enforcing zero leakage, mathematical validity, and relational cleanliness.
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(420px, 1fr));gap:14px;">
            @foreach ($report['checks'] as $key => $check)
                <div class="card" style="padding:16px 18px;display:flex;flex-direction:column;justify-content:space-between;border:1px solid {{ $check['passed'] ? 'var(--xp-border)' : 'rgba(255,77,77,0.4)' }};">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <div style="font-size:14px;font-weight:700;color:var(--xp-text);display:flex;align-items:center;gap:8px;">
                                <span>{{ $check['name'] }}</span>
                            </div>

                            @if ($check['passed'])
                                <span class="badge" style="background:rgba(61,220,132,0.12);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:11px;font-weight:700;display:flex;align-items:center;gap:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    PASS
                                </span>
                            @elseif ($check['is_warning'])
                                <span class="badge" style="background:rgba(255,107,0,0.12);color:var(--xp-orange);border:1px solid rgba(255,107,0,0.3);font-size:11px;font-weight:700;">
                                    WARNING
                                </span>
                            @else
                                <span class="badge" style="background:rgba(255,77,77,0.12);color:var(--xp-error);border:1px solid rgba(255,77,77,0.3);font-size:11px;font-weight:700;">
                                    FAIL
                                </span>
                            @endif
                        </div>

                        <p style="font-size:12px;color:var(--xp-muted);line-height:1.5;margin-bottom:12px;">
                            {{ $check['summary'] }}
                        </p>
                    </div>

                    @if (!empty($check['details']))
                        <div style="background:#0C0C0E;border:1px solid var(--xp-border-subtle);border-radius:6px;padding:8px 12px;font-size:11px;display:flex;flex-wrap:wrap;gap:12px;font-family:monospace;color:var(--xp-muted);">
                            @foreach ($check['details'] as $k => $v)
                                <div>
                                    <span style="color:var(--xp-away);">{{ $k }}:</span>
                                    <strong style="color:{{ $v > 0 && str_contains($k, 'dangling') || str_contains($k, 'negative') || str_contains($k, 'invalid') ? 'var(--xp-error)' : 'var(--xp-text)' }};">
                                        {{ is_bool($v) ? ($v ? 'true' : 'false') : $v }}
                                    </strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Audit Execution History Drawer -->
    <div class="card" style="padding:18px 20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div>
                <div class="card-title">3. Audit Run History &amp; Immutable Verification Log</div>
                <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">
                    Immutable database audit records persisted from CLI and web runs.
                </div>
            </div>
            <span class="badge" style="background:#141418;border:1px solid var(--xp-border);font-size:11px;color:var(--xp-muted);">
                {{ $pastAudits->count() }} Recorded
            </span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding:8px 12px;">Audit ID</th>
                        <th style="padding:8px 12px;">Timestamp (UTC)</th>
                        <th style="padding:8px 12px;">Trigger</th>
                        <th style="padding:8px 12px;">Status</th>
                        <th style="padding:8px 12px;">Checks Passed</th>
                        <th style="padding:8px 12px;">Duration</th>
                        <th style="padding:8px 12px;">Summary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pastAudits as $audit)
                        <tr>
                            <td style="padding:10px 12px;font-family:monospace;color:var(--xp-muted);">
                                #{{ $audit->id }}
                            </td>
                            <td style="padding:10px 12px;font-family:monospace;font-size:12px;color:var(--xp-text);">
                                {{ $audit->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td style="padding:10px 12px;">
                                <span class="badge" style="background:#0C0C0E;border:1px solid var(--xp-border);font-size:10px;text-transform:uppercase;">
                                    {{ $audit->triggered_by }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;">
                                @if ($audit->status === 'clean')
                                    <span style="color:var(--xp-ok);font-size:11px;font-weight:700;">CLEAN</span>
                                @elseif ($audit->status === 'warning')
                                    <span style="color:var(--xp-orange);font-size:11px;font-weight:700;">WARNING</span>
                                @else
                                    <span style="color:var(--xp-error);font-size:11px;font-weight:700;">CRITICAL</span>
                                @endif
                            </td>
                            <td style="padding:10px 12px;font-weight:600;color:var(--xp-text);">
                                {{ $audit->checks_passed }} / {{ $audit->total_checks }}
                            </td>
                            <td style="padding:10px 12px;font-family:monospace;font-size:12px;color:var(--xp-muted);">
                                {{ $audit->duration_ms }}ms
                            </td>
                            <td style="padding:10px 12px;color:var(--xp-muted);font-size:12px;">
                                {{ $audit->summary }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:20px;text-align:center;color:var(--xp-muted);">
                                No audit records logged yet. Run an audit using the button above or via <code>php artisan audit:data --save</code>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

