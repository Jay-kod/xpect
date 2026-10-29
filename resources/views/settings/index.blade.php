<x-layouts.app title="Settings & Keys">
    <div class="page-header">
        <div>
            <h1 class="page-title">Settings & API Keys</h1>
            <p class="page-subtitle">Configure external API credentials, LLM model parameters, and local database maintenance</p>
        </div>
    </div>

    <!-- System Stats -->
    <div class="grid-4">
        <div class="stat-card">
            <span class="stat-label">SQLite Database Size</span>
            <div class="stat-value" style="color:var(--accent);">{{ $stats['db_size'] }}</div>
            <span class="stat-meta">{{ $stats['matches_count'] }} matches in database</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Predictions</span>
            <div class="stat-value">{{ number_format($stats['predictions_count']) }}</div>
            <span class="stat-meta">{{ number_format($stats['evaluations_count']) }} post-match evaluations</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">API Calls Logged</span>
            <div class="stat-value" style="color:var(--yellow);">{{ number_format($stats['api_calls_count']) }}</div>
            <span class="stat-meta">Full raw payloads stored</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Database Backups</span>
            <div class="stat-value" style="color:var(--green);">{{ $stats['backups_count'] }}</div>
            <span class="stat-meta">In storage/backups</span>
        </div>
    </div>

    <div class="grid-2">
        <!-- API Keys Configuration Form -->
        <div class="stat-card">
            <h3 style="margin-bottom:0.5rem;font-size:1.1rem;">API Credentials & Model Setup</h3>
            <p style="font-size:0.8rem;color:var(--text-dim);margin-bottom:1.25rem;">
                Keys are stored strictly in your local <code>.env</code> file. They are masked before display and never committed to git.
            </p>

            <form method="POST" action="{{ route('settings.keys') }}">
                @csrf

                <!-- Football Data API Key -->
                <div class="form-group">
                    <label class="form-label">
                        football-data.org API Key
                        <span style="font-weight:400;color:var(--accent);">({{ $keys['football_data_key'] }})</span>
                    </label>
                    <input type="password" name="football_data_key" class="form-control" placeholder="Enter new key to update...">
                    <div class="form-text">Used for live matchday fixtures and result synchronization.</div>
                </div>

                <!-- API-Football Key -->
                <div class="form-group">
                    <label class="form-label">
                        API-Football Key (api-sports)
                        <span style="font-weight:400;color:var(--accent);">({{ $keys['api_football_key'] }})</span>
                    </label>
                    <input type="password" name="api_football_key" class="form-control" placeholder="Enter API-Football key...">
                    <div class="form-text">Used for lineups and injuries in Phase 7.</div>
                </div>

                <!-- Anthropic API Key -->
                <div class="form-group">
                    <label class="form-label">
                        Anthropic Claude API Key
                        <span style="font-weight:400;color:var(--accent);">({{ $keys['anthropic_key'] }})</span>
                    </label>
                    <input type="password" name="anthropic_key" class="form-control" placeholder="sk-ant-...">
                    <div class="form-text">Used by PreviewGenerator to write match previews from Poisson numbers.</div>
                </div>

                <!-- Anthropic Model Selection -->
                <div class="form-group">
                    <label class="form-label">Anthropic Model String</label>
                    <select name="anthropic_model" class="form-control">
                        <option value="claude-3-5-sonnet-latest" {{ $keys['anthropic_model'] === 'claude-3-5-sonnet-latest' ? 'selected' : '' }}>claude-3-5-sonnet-latest (Recommended)</option>
                        <option value="claude-3-5-haiku-latest" {{ $keys['anthropic_model'] === 'claude-3-5-haiku-latest' ? 'selected' : '' }}>claude-3-5-haiku-latest (Fastest & Cheaper)</option>
                        <option value="claude-3-opus-latest" {{ $keys['anthropic_model'] === 'claude-3-opus-latest' ? 'selected' : '' }}>claude-3-opus-latest</option>
                    </select>
                    <div class="form-text">Current model: <code>{{ $keys['anthropic_model'] }}</code></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">
                    💾 Save API Credentials to .env
                </button>
            </form>
        </div>

        <!-- Local Actions & Maintenance -->
        <div class="stat-card">
            <h3 style="margin-bottom:0.5rem;font-size:1.1rem;">Local System Operations</h3>
            <p style="font-size:0.8rem;color:var(--text-dim);margin-bottom:1.25rem;">
                Trigger local maintenance commands directly through the application.
            </p>

            <div style="display:flex;flex-direction:column;gap:1rem;">
                <!-- Sync Matches -->
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;background:var(--xp-surface);border-radius:6px;border:1px solid var(--border);">
                    <div>
                        <div style="font-weight:600;font-size:0.85rem;">Sync Upcoming Matches</div>
                        <div style="font-size:0.75rem;color:var(--text-dim);">Fetch current Premier League fixtures and recent results</div>
                    </div>
                    <form method="POST" action="{{ route('settings.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="sync_matches">
                        <button type="submit" class="btn btn-secondary btn-sm">Run Sync</button>
                    </form>
                </div>

                <!-- Rebuild Standings -->
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;background:var(--xp-surface);border-radius:6px;border:1px solid var(--border);">
                    <div>
                        <div style="font-weight:600;font-size:0.85rem;">Rebuild Standings Snapshots</div>
                        <div style="font-size:0.75rem;color:var(--text-dim);">Recalculate league tables and positions across all seasons</div>
                    </div>
                    <form method="POST" action="{{ route('settings.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="rebuild_standings">
                        <button type="submit" class="btn btn-secondary btn-sm">Rebuild</button>
                    </form>
                </div>

                <!-- Evaluate Predictions -->
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;background:var(--xp-surface);border-radius:6px;border:1px solid var(--border);">
                    <div>
                        <div style="font-weight:600;font-size:0.85rem;">Evaluate Predictions</div>
                        <div style="font-size:0.75rem;color:var(--text-dim);">Compute Log Loss and Brier scores for finished matches</div>
                    </div>
                    <form method="POST" action="{{ route('settings.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="evaluate_predictions">
                        <button type="submit" class="btn btn-secondary btn-sm">Evaluate</button>
                    </form>
                </div>

                <!-- Backup SQLite Database -->
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;background:var(--xp-surface);border-radius:6px;border:1px solid var(--border);">
                    <div>
                        <div style="font-weight:600;font-size:0.85rem;">Backup Database</div>
                        <div style="font-size:0.75rem;color:var(--text-dim);">Create timestamped SQLite snapshot in storage/backups</div>
                    </div>
                    <form method="POST" action="{{ route('settings.action') }}">
                        @csrf
                        <input type="hidden" name="action" value="app_backup">
                        <button type="submit" class="btn btn-secondary btn-sm">Create Backup</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

