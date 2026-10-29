<x-layouts.app title="Track Record & Analytics">
    <div class="page-header">
        <div>
            <h1 class="page-title">Track Record & Model Analytics</h1>
            <p class="page-subtitle">Historical walk-forward evaluation, baseline comparison, and probabilistic calibration</p>
        </div>
        <div style="font-size:0.85rem;color:var(--text-muted);">
            Active Model: <strong style="color:var(--accent);">v1.0.0-poisson</strong>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid-4">
        <div class="stat-card">
            <span class="stat-label">Model Accuracy</span>
            <div class="stat-value" style="color:var(--green);">
                {{ number_format(($stats['model']['accuracy'] ?? 0) * 100, 1) }}%
            </div>
            <span class="stat-meta">
                Baselines: {{ number_format(($stats['baselines']['historical_base_rates']['accuracy'] ?? 0) * 100, 1) }}%
                @if (($stats['model']['accuracy'] ?? 0) > ($stats['baselines']['historical_base_rates']['accuracy'] ?? 0))
                    &bull; <span style="color:var(--green);font-weight:600;">+{{ number_format((($stats['model']['accuracy'] ?? 0) - ($stats['baselines']['historical_base_rates']['accuracy'] ?? 0)) * 100, 1) }}%</span>
                @endif
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Multi-class Log Loss (↓)</span>
            <div class="stat-value" style="color:var(--accent);">
                {{ number_format($stats['model']['log_loss'] ?? 0, 4) }}
            </div>
            <span class="stat-meta">
                Base Rate: {{ number_format($stats['baselines']['historical_base_rates']['log_loss'] ?? 0, 4) }}
                @if (($stats['model']['log_loss'] ?? 99) < ($stats['baselines']['historical_base_rates']['log_loss'] ?? 0))
                    &bull; <span style="color:var(--green);font-weight:600;">Beats Baseline</span>
                @endif
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Brier Score (↓)</span>
            <div class="stat-value" style="color:var(--yellow);">
                {{ number_format($stats['model']['brier'] ?? 0, 4) }}
            </div>
            <span class="stat-meta">
                Base Rate: {{ number_format($stats['baselines']['historical_base_rates']['brier'] ?? 0, 4) }}
                @if (($stats['model']['brier'] ?? 99) < ($stats['baselines']['historical_base_rates']['brier'] ?? 0))
                    &bull; <span style="color:var(--green);font-weight:600;">Beats Baseline</span>
                @endif
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Sample Size</span>
            <div class="stat-value">
                {{ number_format($stats['sample_size'] ?? 0) }}
            </div>
            <span class="stat-meta">Held-out finished matches evaluated</span>
        </div>
    </div>

    <!-- Baseline Comparison & Calibration Grid -->
    <div class="grid-2">
        <!-- Baseline Comparison Card -->
        <div class="stat-card">
            <h3 style="margin-bottom:1rem;font-size:1.1rem;">Benchmark Comparison</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Strategy / Baseline</th>
                            <th>Accuracy</th>
                            <th>Log Loss (↓)</th>
                            <th>Brier (↓)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:rgba(56, 189, 248, 0.08);font-weight:600;">
                            <td><strong style="color:var(--accent);">Poisson Model (v1.0.0)</strong></td>
                            <td style="color:var(--green);">{{ number_format(($stats['model']['accuracy'] ?? 0) * 100, 1) }}%</td>
                            <td>{{ number_format($stats['model']['log_loss'] ?? 0, 4) }}</td>
                            <td>{{ number_format($stats['model']['brier'] ?? 0, 4) }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted);">Historical Base Rates</td>
                            <td>{{ number_format(($stats['baselines']['historical_base_rates']['accuracy'] ?? 0) * 100, 1) }}%</td>
                            <td>{{ number_format($stats['baselines']['historical_base_rates']['log_loss'] ?? 0, 4) }}</td>
                            <td>{{ number_format($stats['baselines']['historical_base_rates']['brier'] ?? 0, 4) }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted);">Uniform (1/3 each)</td>
                            <td>{{ number_format(($stats['baselines']['uniform']['accuracy'] ?? 0) * 100, 1) }}%</td>
                            <td>{{ number_format($stats['baselines']['uniform']['log_loss'] ?? 0, 4) }}</td>
                            <td>{{ number_format($stats['baselines']['uniform']['brier'] ?? 0, 4) }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--text-muted);">Always Home</td>
                            <td>{{ number_format(($stats['baselines']['always_home']['accuracy'] ?? 0) * 100, 1) }}%</td>
                            <td>{{ number_format($stats['baselines']['always_home']['log_loss'] ?? 0, 4) }}</td>
                            <td>{{ number_format($stats['baselines']['always_home']['brier'] ?? 0, 4) }}</td>
                        </tr>
                        @if (!empty($stats['benchmarks']['bookmaker_b365']))
                            <tr style="background:rgba(234, 179, 8, 0.05);">
                                <td style="color:#facc15;"><strong>Bet365 Odds Benchmark</strong></td>
                                <td style="color:#facc15;">{{ number_format(($stats['benchmarks']['bookmaker_b365']['accuracy'] ?? 0) * 100, 1) }}%</td>
                                <td>{{ number_format($stats['benchmarks']['bookmaker_b365']['log_loss'] ?? 0, 4) }}</td>
                                <td>{{ number_format($stats['benchmarks']['bookmaker_b365']['brier'] ?? 0, 4) }}</td>
                            </tr>
                        @endif
                        @if (!empty($stats['benchmarks']['bookmaker_market_avg']))
                            <tr style="background:rgba(234, 179, 8, 0.05);">
                                <td style="color:#facc15;"><strong>Market Consensus Odds</strong></td>
                                <td style="color:#facc15;">{{ number_format(($stats['benchmarks']['bookmaker_market_avg']['accuracy'] ?? 0) * 100, 1) }}%</td>
                                <td>{{ number_format($stats['benchmarks']['bookmaker_market_avg']['log_loss'] ?? 0, 4) }}</td>
                                <td>{{ number_format($stats['benchmarks']['bookmaker_market_avg']['brier'] ?? 0, 4) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div style="margin-top:1rem;padding:0.75rem 1rem;background:var(--xp-surface);border-radius:6px;border:1px solid var(--border);">
                <div style="font-size:0.8rem;color:var(--text-muted);">
                    All Baselines Beaten:
                    @if ($stats['comparison']['all_baselines_beaten'] ?? false)
                        <strong style="color:var(--green);">YES (Sub-1.0 Log Loss achieved)</strong>
                    @else
                        <strong style="color:var(--red);">NO</strong>
                    @endif
                </div>
            </div>
        </div>

        <!-- 10-Bin Calibration Table -->
        <div class="stat-card">
            <h3 style="margin-bottom:1rem;font-size:1.1rem;">Probabilistic Calibration (10 Bins)</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Bin</th>
                            <th>Samples</th>
                            <th>Avg Predicted</th>
                            <th>Empirical Observed</th>
                            <th>Alignment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['calibration_table'] ?? [] as $b)
                            <tr>
                                <td style="font-weight:600;">{{ $b['bin'] }}</td>
                                <td style="color:var(--text-muted);">{{ $b['samples'] }}</td>
                                <td>{{ $b['avg_predicted'] }}%</td>
                                <td style="font-weight:600;color:var(--accent);">{{ $b['observed_freq'] }}%</td>
                                <td>
                                    @php
                                        $diff = abs($b['avg_predicted'] - $b['observed_freq']);
                                    @endphp
                                    @if ($b['samples'] === 0)
                                        <span style="color:var(--text-dim);">—</span>
                                    @elseif ($diff <= 3.0)
                                        <span class="badge badge-success">Excellent</span>
                                    @elseif ($diff <= 7.0)
                                        <span class="badge badge-info">Good</span>
                                    @else
                                        <span class="badge badge-warning">Drift</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;color:var(--text-dim);padding:1.5rem;">No calibration data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Recent Evaluated Matches -->
    <div class="stat-card" style="margin-top:1.5rem;">
        <h3 style="margin-bottom:1rem;font-size:1.1rem;">Recent Evaluated Predictions</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Score</th>
                        <th>Actual</th>
                        <th>Model Probabilities</th>
                        <th>Predicted</th>
                        <th>Result</th>
                        <th>Log Loss</th>
                        <th>Brier</th>
                        <th>Evaluated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evaluations as $eval)
                        @php
                            $p = $eval->prediction;
                            $m = $p?->match;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $m?->homeTeam?->short_name }}</strong> vs <strong>{{ $m?->awayTeam?->short_name }}</strong>
                                <div style="font-size:0.75rem;color:var(--text-dim);">{{ $m?->kickoff_at?->format('d M Y') }}</div>
                            </td>
                            <td style="font-weight:700;">
                                {{ $m?->home_goals }} &ndash; {{ $m?->away_goals }}
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $eval->outcome }}</span>
                            </td>
                            <td style="font-size:0.8rem;">
                                H: {{ round((float) $p?->p_home * 100) }}% | D: {{ round((float) $p?->p_draw * 100) }}% | A: {{ round((float) $p?->p_away * 100) }}%
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $eval->predicted_outcome }}</span>
                            </td>
                            <td>
                                @if ($eval->correct)
                                    <span class="badge badge-success">Correct</span>
                                @else
                                    <span class="badge badge-danger">Incorrect</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) $eval->log_loss, 4) }}</td>
                            <td>{{ number_format((float) $eval->brier, 4) }}</td>
                            <td style="font-size:0.75rem;color:var(--text-dim);">
                                {{ $eval->evaluated_at ? $eval->evaluated_at->diffForHumans() : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;color:var(--text-dim);padding:2rem;">
                                No evaluations found. Run <code>php artisan evaluate:predictions</code>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">
            {{ $evaluations->links() }}
        </div>
    </div>
</x-layouts.app>

