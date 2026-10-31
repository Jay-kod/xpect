<x-layouts.app title="Settings & AI Providers">
    <style>
        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--xp-border);
        }
        .settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 960px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
        .settings-card {
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .card-heading-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--xp-border-subtle);
            padding-bottom: 10px;
        }
        .form-row {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-row label {
            font-size: 12px;
            font-weight: 600;
            color: var(--xp-text);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .key-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .key-input-wrapper input {
            width: 100%;
            background: var(--xp-black);
            border: 1px solid var(--xp-border);
            border-radius: 6px;
            padding: 8px 10px;
            color: var(--xp-text);
            font-size: 12px;
            font-family: monospace;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .key-input-wrapper input:focus {
            border-color: var(--xp-orange);
            box-shadow: 0 0 0 2px rgba(217, 78, 0, 0.2);
        }
        .key-input-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-test-key {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--xp-border);
            color: var(--xp-text);
            cursor: pointer;
            border-radius: 6px;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .btn-test-key:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--xp-orange);
            color: var(--xp-orange);
        }
        .btn-test-key:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .form-hint {
            font-size: 11px;
            color: var(--xp-muted);
            line-height: 1.4;
        }
        .op-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            background: var(--xp-black);
            border: 1px solid var(--xp-border-subtle);
            border-radius: 6px;
            transition: border-color 0.15s ease;
        }
        .op-row:hover {
            border-color: var(--xp-border);
        }
        .provider-pill {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .provider-badge-claude {
            background: rgba(217, 78, 0, 0.12);
            color: var(--xp-orange);
            border: 1px solid rgba(217, 78, 0, 0.3);
        }
        .provider-badge-chatgpt {
            background: rgba(16, 163, 127, 0.12);
            color: #10A37F;
            border: 1px solid rgba(16, 163, 127, 0.3);
        }
        .provider-badge-gemini {
            background: rgba(66, 133, 244, 0.12);
            color: #4285F4;
            border: 1px solid rgba(66, 133, 244, 0.3);
        }
    </style>

    <!-- Page Header -->
    <div class="settings-header">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <h1 style="font-size:20px;font-weight:700;color:var(--xp-text);margin:0;letter-spacing:-0.02em;">
                    Settings &amp; AI Providers
                </h1>
                <span class="badge" style="background:var(--xp-surface);border:1px solid var(--xp-border);font-size:10.5px;color:var(--xp-orange);font-weight:700;">
                    v1.2.1-h2h
                </span>
            </div>
            <p style="font-size:12px;color:var(--xp-muted);margin:0;">
                Configure multi-LLM reasoning engines (Claude, ChatGPT, Gemini), sports data credentials, and local database operations
            </p>
        </div>

        <!-- Privacy & Security Badges -->
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <span class="badge" style="background:#0C0C0E;border:1px solid var(--xp-border);color:var(--xp-ok);font-size:10px;">
                <span style="display:inline-block;width:5px;height:5px;border-radius:50%;background:var(--xp-ok);margin-right:4px;"></span>
                Local .env Storage Only
            </span>
            <span class="badge" style="background:#0C0C0E;border:1px solid var(--xp-border);color:var(--xp-muted);font-size:10px;">
                Zero Leakage Guard Active
            </span>
        </div>
    </div>

    <!-- System Vital Stats Strip -->
    <div class="grid-kpi-4" style="margin-bottom:20px;">
        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">SQLite Database Size</span>
            <div class="kpi-value" style="font-size:22px;margin:2px 0;color:var(--xp-orange);">
                {{ $stats['db_size'] }}
            </div>
            <span style="font-size:11px;color:var(--xp-muted);">
                {{ number_format($stats['matches_count']) }} verified matches
            </span>
        </div>

        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">Model Predictions</span>
            <div class="kpi-value" style="font-size:22px;margin:2px 0;">
                {{ number_format($stats['predictions_count']) }}
            </div>
            <span style="font-size:11px;color:var(--xp-muted);">
                {{ number_format($stats['evaluations_count']) }} evaluated results
            </span>
        </div>

        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">Audited API Calls</span>
            <div class="kpi-value" style="font-size:22px;margin:2px 0;color:var(--xp-text);">
                {{ number_format($stats['api_calls_count']) }}
            </div>
            <span style="font-size:11px;color:var(--xp-muted);">
                Full raw payloads &amp; redacted keys
            </span>
        </div>

        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">Database Backups</span>
            <div class="kpi-value" style="font-size:22px;margin:2px 0;color:var(--xp-ok);">
                {{ $stats['backups_count'] }}
            </div>
            <span style="font-size:11px;color:var(--xp-muted);">
                In storage/backups/
            </span>
        </div>
    </div>

    <!-- Main Settings Layout -->
    <div class="settings-grid">
        <!-- Left Column: AI Model & Multi-LLM Engine Configuration -->
        <div class="settings-card">
            <div class="card-heading-row">
                <div>
                    <h2 style="font-size:15px;font-weight:700;color:var(--xp-text);margin:0;">
                        AI Model &amp; Reasoning Engine
                    </h2>
                    <p style="font-size:11px;color:var(--xp-muted);margin:2px 0 0 0;">
                        Select between Claude, ChatGPT, or Gemini to synthesize match previews and tactical insights
                    </p>
                </div>
                <span id="active-provider-badge" class="provider-pill provider-badge-{{ $keys['ai_provider'] }}">
                    {{ $providers[$keys['ai_provider']]['badge'] ?? 'Anthropic Claude' }}
                </span>
            </div>

            <form method="POST" action="{{ route('settings.keys') }}" style="display:flex;flex-direction:column;gap:14px;margin:0;">
                @csrf

                <!-- AI Provider Dropdown -->
                <div class="form-row">
                    <label for="ai_provider_select">
                        <span>Active AI Provider</span>
                        <span style="font-size:11px;color:var(--xp-orange);font-weight:600;">Multi-Model Enabled</span>
                    </label>
                    <select id="ai_provider_select" name="ai_provider" class="select-pill" style="width:100%;padding:8px 10px;font-size:12px;background:var(--xp-black);border:1px solid var(--xp-border);color:var(--xp-text);border-radius:6px;" onchange="updateProviderModels(this.value)">
                        <option value="claude" {{ $keys['ai_provider'] === 'claude' ? 'selected' : '' }}>
                            Anthropic Claude (Sonnet 3.5, Haiku, Opus)
                        </option>
                        <option value="chatgpt" {{ $keys['ai_provider'] === 'chatgpt' ? 'selected' : '' }}>
                            OpenAI ChatGPT (GPT-4o, GPT-4o-mini, o1-preview)
                        </option>
                        <option value="gemini" {{ $keys['ai_provider'] === 'gemini' ? 'selected' : '' }}>
                            Google Gemini (Gemini 1.5 Pro, Flash, 2.0)
                        </option>
                    </select>
                    <div id="provider-description" class="form-hint">
                        {{ $providers[$keys['ai_provider']]['description'] }}
                    </div>
                </div>

                <!-- Dynamic Model String Selector -->
                <div class="form-row">
                    <label for="ai_model_select">
                        <span>AI Model</span>
                        <span style="font-size:11px;color:var(--xp-muted);">Selected Model</span>
                    </label>
                    <select id="ai_model_select" name="ai_model" class="select-pill" style="width:100%;padding:8px 10px;font-size:12px;background:var(--xp-black);border:1px solid var(--xp-border);color:var(--xp-text);border-radius:6px;">
                        @foreach ($providers[$keys['ai_provider']]['models'] as $mKey => $mLabel)
                            <option value="{{ $mKey }}" {{ ($keys['ai_model'] === $mKey || $keys['anthropic_model'] === $mKey) ? 'selected' : '' }}>
                                {{ $mLabel }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">
                        Previews strictly explain Poisson probabilities without fabricating external narratives.
                    </div>
                </div>

                <div style="border-top:1px solid var(--xp-border-subtle);margin:4px 0;"></div>

                <!-- 1. Anthropic Claude API Key -->
                <div id="key-group-claude" class="form-row" style="transition:opacity 0.2s ease;">
                    <label>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <span>Anthropic Claude API Key</span>
                            <a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener noreferrer" style="font-size:10.5px;color:var(--xp-orange);text-decoration:none;font-weight:600;" title="Get Anthropic Claude API Key">Get Key ↗</a>
                        </span>
                        @if ($keys['anthropic_has_key'])
                            <span class="badge" style="background:rgba(61,220,132,0.15);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:10px;">
                                Configured ({{ $keys['anthropic_key'] }})
                            </span>
                        @else
                            <span class="badge" style="background:rgba(217,78,0,0.15);color:var(--xp-orange);border:1px solid rgba(217,78,0,0.3);font-size:10px;">
                                Not Configured
                            </span>
                        @endif
                    </label>
                    <div class="key-input-row">
                        <div class="key-input-wrapper" style="flex:1;">
                            <input type="password" id="input-anthropic-key" name="anthropic_key" placeholder="Enter sk-ant-... to update key">
                        </div>
                        <button type="button" class="btn btn-secondary btn-test-key" data-provider="claude" data-target="input-anthropic-key" title="Test Anthropic Claude API Key">
                            <span class="btn-spinner" style="display:none;width:10px;height:10px;border:2px solid var(--xp-border);border-top-color:var(--xp-orange);border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                            <span class="btn-label">Test Key</span>
                        </button>
                    </div>
                    <div id="test-feedback-claude" class="test-feedback-box" style="display:none;margin-top:5px;font-size:11px;padding:7px 11px;border-radius:6px;line-height:1.4;"></div>
                    <div class="form-hint">Powers Anthropic Claude match previews and detailed statistical breakdown explanations.</div>
                </div>

                <!-- 2. OpenAI ChatGPT API Key -->
                <div id="key-group-chatgpt" class="form-row" style="transition:opacity 0.2s ease;">
                    <label>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <span>OpenAI ChatGPT API Key</span>
                            <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer" style="font-size:10.5px;color:var(--xp-orange);text-decoration:none;font-weight:600;" title="Get OpenAI API Key">Get Key ↗</a>
                        </span>
                        @if ($keys['openai_has_key'])
                            <span class="badge" style="background:rgba(61,220,132,0.15);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:10px;">
                                Configured ({{ $keys['openai_key'] }})
                            </span>
                        @else
                            <span class="badge" style="background:rgba(217,78,0,0.15);color:var(--xp-orange);border:1px solid rgba(217,78,0,0.3);font-size:10px;">
                                Not Configured
                            </span>
                        @endif
                    </label>
                    <div class="key-input-row">
                        <div class="key-input-wrapper" style="flex:1;">
                            <input type="password" id="input-openai-key" name="openai_key" placeholder="Enter sk-proj-... to update key">
                        </div>
                        <button type="button" class="btn btn-secondary btn-test-key" data-provider="chatgpt" data-target="input-openai-key" title="Test OpenAI ChatGPT API Key">
                            <span class="btn-spinner" style="display:none;width:10px;height:10px;border:2px solid var(--xp-border);border-top-color:var(--xp-orange);border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                            <span class="btn-label">Test Key</span>
                        </button>
                    </div>
                    <div id="test-feedback-chatgpt" class="test-feedback-box" style="display:none;margin-top:5px;font-size:11px;padding:7px 11px;border-radius:6px;line-height:1.4;"></div>
                    <div class="form-hint">Powers OpenAI GPT-4o and o1 reasoning models for structured Poisson preview generation.</div>
                </div>

                <!-- 3. Google Gemini API Key -->
                <div id="key-group-gemini" class="form-row" style="transition:opacity 0.2s ease;">
                    <label>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <span>Google Gemini API Key</span>
                            <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" style="font-size:10.5px;color:var(--xp-orange);text-decoration:none;font-weight:600;" title="Get Google Gemini API Key">Get Key ↗</a>
                        </span>
                        @if ($keys['gemini_has_key'])
                            <span class="badge" style="background:rgba(61,220,132,0.15);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:10px;">
                                Configured ({{ $keys['gemini_key'] }})
                            </span>
                        @else
                            <span class="badge" style="background:rgba(217,78,0,0.15);color:var(--xp-orange);border:1px solid rgba(217,78,0,0.3);font-size:10px;">
                                Not Configured
                            </span>
                        @endif
                    </label>
                    <div class="key-input-row">
                        <div class="key-input-wrapper" style="flex:1;">
                            <input type="password" id="input-gemini-key" name="gemini_key" placeholder="Enter AIzaSy... to update key">
                        </div>
                        <button type="button" class="btn btn-secondary btn-test-key" data-provider="gemini" data-target="input-gemini-key" title="Test Google Gemini API Key">
                            <span class="btn-spinner" style="display:none;width:10px;height:10px;border:2px solid var(--xp-border);border-top-color:var(--xp-orange);border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                            <span class="btn-label">Test Key</span>
                        </button>
                    </div>
                    <div id="test-feedback-gemini" class="test-feedback-box" style="display:none;margin-top:5px;font-size:11px;padding:7px 11px;border-radius:6px;line-height:1.4;"></div>
                    <div class="form-hint">Powers Google Gemini 3.8 Flash &amp; 3 Pro for instant contextual commentary.</div>
                </div>

                <div style="border-top:1px solid var(--xp-border-subtle);margin:4px 0;"></div>

                <!-- Football Data Providers -->
                <div class="form-row">
                    <label>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <span>football-data.org API Key</span>
                            <a href="https://www.football-data.org/client/register" target="_blank" rel="noopener noreferrer" style="font-size:10.5px;color:var(--xp-orange);text-decoration:none;font-weight:600;" title="Get Free football-data.org API Key">Get Key ↗</a>
                        </span>
                        @if ($keys['football_data_has_key'])
                            <span class="badge" style="background:rgba(61,220,132,0.15);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:10px;">
                                Configured ({{ $keys['football_data_key'] }})
                            </span>
                        @else
                            <span class="badge" style="background:rgba(217,78,0,0.15);color:var(--xp-orange);border:1px solid rgba(217,78,0,0.3);font-size:10px;">
                                Not Configured
                            </span>
                        @endif
                    </label>
                    <div class="key-input-row">
                        <div class="key-input-wrapper" style="flex:1;">
                            <input type="password" id="input-football-data-key" name="football_data_key" placeholder="Enter football-data token to update">
                        </div>
                        <button type="button" class="btn btn-secondary btn-test-key" data-provider="football_data" data-target="input-football-data-key" title="Test football-data.org API Token">
                            <span class="btn-spinner" style="display:none;width:10px;height:10px;border:2px solid var(--xp-border);border-top-color:var(--xp-orange);border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                            <span class="btn-label">Test Key</span>
                        </button>
                    </div>
                    <div id="test-feedback-football_data" class="test-feedback-box" style="display:none;margin-top:5px;font-size:11px;padding:7px 11px;border-radius:6px;line-height:1.4;"></div>
                    <div class="form-hint">Used for live matchday fixtures and automatic result synchronization.</div>
                </div>

                <div class="form-row">
                    <label>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <span>API-Football Key (api-sports)</span>
                            <a href="https://dashboard.api-football.com/register" target="_blank" rel="noopener noreferrer" style="font-size:10.5px;color:var(--xp-orange);text-decoration:none;font-weight:600;" title="Get API-Football Key">Get Key ↗</a>
                        </span>
                        @if ($keys['api_football_has_key'])
                            <span class="badge" style="background:rgba(61,220,132,0.15);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:10px;">
                                Configured ({{ $keys['api_football_key'] }})
                            </span>
                        @else
                            <span class="badge" style="background:rgba(217,78,0,0.15);color:var(--xp-orange);border:1px solid rgba(217,78,0,0.3);font-size:10px;">
                                Not Configured
                            </span>
                        @endif
                    </label>
                    <div class="key-input-row">
                        <div class="key-input-wrapper" style="flex:1;">
                            <input type="password" id="input-api-football-key" name="api_football_key" placeholder="Enter API-Football key to update">
                        </div>
                        <button type="button" class="btn btn-secondary btn-test-key" data-provider="api_football" data-target="input-api-football-key" title="Test API-Football Key">
                            <span class="btn-spinner" style="display:none;width:10px;height:10px;border:2px solid var(--xp-border);border-top-color:var(--xp-orange);border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                            <span class="btn-label">Test Key</span>
                        </button>
                    </div>
                    <div id="test-feedback-api_football" class="test-feedback-box" style="display:none;margin-top:5px;font-size:11px;padding:7px 11px;border-radius:6px;line-height:1.4;"></div>
                    <div class="form-hint">Used for secondary lineup validation and team metadata enrichment.</div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:8px;padding:9px;font-size:12px;font-weight:700;">
                    Save Credentials &amp; Model Selection to .env
                </button>
            </form>
        </div>

        <!-- Right Column: Operations & Maintenance -->
        <div style="display:flex;flex-direction:column;gap:18px;">
            <!-- System Operations Card -->
            <div class="settings-card">
                <div class="card-heading-row">
                    <div>
                        <h2 style="font-size:15px;font-weight:700;color:var(--xp-text);margin:0;">
                            Local System Operations
                        </h2>
                        <p style="font-size:11px;color:var(--xp-muted);margin:2px 0 0 0;">
                            Execute maintenance routines and database synchronization tasks
                        </p>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:8px;">
                    <!-- Action 1: Sync Matches -->
                    <div class="op-row">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--xp-text);">Sync Upcoming Matches</div>
                            <div style="font-size:10.5px;color:var(--xp-muted);">Fetch current Premier League fixtures and recent results</div>
                        </div>
                        <form method="POST" action="{{ route('settings.action') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="action" value="sync_matches">
                            <button type="submit" class="btn btn-secondary btn-sm" style="font-size:11px;padding:3px 8px;">Run Sync</button>
                        </form>
                    </div>

                    <!-- Action 2: Rebuild Standings -->
                    <div class="op-row">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--xp-text);">Rebuild Standings Snapshots</div>
                            <div style="font-size:10.5px;color:var(--xp-muted);">Recalculate league tables and positions across all seasons</div>
                        </div>
                        <form method="POST" action="{{ route('settings.action') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="action" value="rebuild_standings">
                            <button type="submit" class="btn btn-secondary btn-sm" style="font-size:11px;padding:3px 8px;">Rebuild</button>
                        </form>
                    </div>

                    <!-- Action 3: Evaluate Predictions -->
                    <div class="op-row">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--xp-text);">Evaluate Predictions</div>
                            <div style="font-size:10.5px;color:var(--xp-muted);">Compute Log Loss and Brier scores for finished matches</div>
                        </div>
                        <form method="POST" action="{{ route('settings.action') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="action" value="evaluate_predictions">
                            <button type="submit" class="btn btn-secondary btn-sm" style="font-size:11px;padding:3px 8px;">Evaluate</button>
                        </form>
                    </div>

                    <!-- Action 4: Backup Database -->
                    <div class="op-row">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--xp-text);">Backup SQLite Database</div>
                            <div style="font-size:10.5px;color:var(--xp-muted);">Create timestamped SQLite snapshot in storage/backups/</div>
                        </div>
                        <form method="POST" action="{{ route('settings.action') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="action" value="app_backup">
                            <button type="submit" class="btn btn-secondary btn-sm" style="font-size:11px;padding:3px 8px;">Create Backup</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Architecture & Integrity Overview Card -->
            <div class="settings-card" style="background:#09090C;border-color:var(--xp-border-subtle);">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--xp-orange);">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span style="font-size:13px;font-weight:700;color:var(--xp-text);">Privacy &amp; Data Security Architecture</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:11px;color:var(--xp-muted);line-height:1.5;">
                    <div>
                        <strong style="color:var(--xp-text);">1. Secrets Redaction:</strong> All credentials (API keys, authorization headers) are masked before persisting to <code>api_calls</code> and logs. Keys reside only in <code>.env</code>.
                    </div>
                    <div>
                        <strong style="color:var(--xp-text);">2. Zero Data Leakage:</strong> Features for any fixture are computed exclusively from matches finished strictly prior to kickoff ($t < \text{kickoff}$).
                    </div>
                    <div>
                        <strong style="color:var(--xp-text);">3. Anti-Hallucination Guard:</strong> Previews generated by any LLM are parsed by <code>NumberCheckGuard</code> to guarantee numbers match computed mathematical probabilities.
                    </div>
                </div>
                <div style="margin-top:4px;display:flex;justify-content:flex-end;">
                    <a href="{{ route('docs') }}" style="font-size:11px;color:var(--xp-orange);text-decoration:none;font-weight:600;">
                        Read Technical Docs &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side Model Switching Script -->
    <script>
        const providerData = @json($providers);

        function updateProviderModels(provider) {
            const modelSelect = document.getElementById('ai_model_select');
            const descEl = document.getElementById('provider-description');
            const badgeEl = document.getElementById('active-provider-badge');

            if (!providerData[provider]) return;

            const info = providerData[provider];
            
            // Update badge & description
            badgeEl.textContent = info.badge;
            badgeEl.className = 'provider-pill provider-badge-' + provider;
            descEl.textContent = info.description;

            // Clear and populate model dropdown
            modelSelect.innerHTML = '';
            for (const [key, label] of Object.entries(info.models)) {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = label;
                if (key === info.default_model) {
                    opt.selected = true;
                }
                modelSelect.appendChild(opt);
            }

            // Highlight corresponding key section
            ['claude', 'chatgpt', 'gemini'].forEach(p => {
                const group = document.getElementById('key-group-' + p);
                if (group) {
                    if (p === provider) {
                        group.style.opacity = '1';
                        group.style.borderColor = 'var(--xp-orange)';
                    } else {
                        group.style.opacity = '0.65';
                        group.style.borderColor = 'transparent';
                    }
                }
            });
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            const current = document.getElementById('ai_provider_select').value;
            updateProviderModels(current);

            // Setup API Key testing buttons
            document.querySelectorAll('.btn-test-key').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const provider = btn.getAttribute('data-provider');
                    const targetId = btn.getAttribute('data-target');
                    const feedbackEl = document.getElementById('test-feedback-' + provider);
                    const inputEl = document.getElementById(targetId);
                    const spinner = btn.querySelector('.btn-spinner');
                    const label = btn.querySelector('.btn-label');

                    const candidateKey = inputEl ? inputEl.value.trim() : '';

                    // UI Loading state
                    btn.disabled = true;
                    if (spinner) spinner.style.display = 'inline-block';
                    if (label) label.textContent = 'Testing...';

                    if (feedbackEl) {
                        feedbackEl.style.display = 'block';
                        feedbackEl.style.background = 'rgba(255,255,255,0.04)';
                        feedbackEl.style.border = '1px solid var(--xp-border)';
                        feedbackEl.style.color = 'var(--xp-muted)';
                        feedbackEl.innerHTML = `Sending verification probe to <strong>${provider}</strong> endpoint...`;
                    }

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                            || document.querySelector('input[name="_token"]')?.value;

                        const res = await fetch("{{ route('settings.test-key') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify({
                                provider: provider,
                                key: candidateKey,
                            }),
                        });

                        const data = await res.json();

                        if (data.is_valid) {
                            feedbackEl.style.background = 'rgba(61,220,132,0.12)';
                            feedbackEl.style.border = '1px solid rgba(61,220,132,0.3)';
                            feedbackEl.style.color = 'var(--xp-ok)';
                            let html = `<strong>✓ Active &amp; Verified (HTTP ${data.status_code}, ${data.duration_ms}ms)</strong>: ${data.message}`;
                            if (data.details) {
                                html += `<div style="margin-top:3px;font-size:10.5px;color:rgba(255,255,255,0.7);">${data.details}</div>`;
                            }
                            if (candidateKey) {
                                html += `<div style="margin-top:4px;font-size:10.5px;color:var(--xp-orange);font-weight:600;">Remember to click &ldquo;Save Credentials&rdquo; below to persist this key into .env.</div>`;
                            }
                            feedbackEl.innerHTML = html;
                        } else {
                            feedbackEl.style.background = 'rgba(255,90,95,0.12)';
                            feedbackEl.style.border = '1px solid rgba(255,90,95,0.3)';
                            feedbackEl.style.color = 'var(--xp-danger)';
                            let html = `<strong>✕ Verification Failed (HTTP ${data.status_code || 0}, ${data.duration_ms || 0}ms)</strong>: ${data.message}`;
                            if (data.details && data.details !== data.message) {
                                html += `<div style="margin-top:3px;font-size:10.5px;color:rgba(255,255,255,0.7);">${data.details}</div>`;
                            }
                            feedbackEl.innerHTML = html;
                        }
                    } catch (err) {
                        if (feedbackEl) {
                            feedbackEl.style.background = 'rgba(255,90,95,0.12)';
                            feedbackEl.style.border = '1px solid rgba(255,90,95,0.3)';
                            feedbackEl.style.color = 'var(--xp-danger)';
                            feedbackEl.innerHTML = `<strong>✕ Connection Error</strong>: ${err.message || 'Failed to communicate with verification endpoint.'}`;
                        }
                    } finally {
                        btn.disabled = false;
                        if (spinner) spinner.style.display = 'none';
                        if (label) label.textContent = 'Test Key';
                    }
                });
            });
        });
    </script>
</x-layouts.app>
