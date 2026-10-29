<x-layouts.app title="Dashboard">
    <style>
        .breakdown-details[open] .chevron-icon {
            transform: rotate(180deg);
        }
        .breakdown-details summary::-webkit-details-marker {
            display: none;
        }
        .breakdown-details summary:hover {
            background: rgba(217, 78, 0, 0.10) !important;
        }
        .matches-grid-container {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 20px;
            align-items: start;
            margin-top: 20px;
        }
        .matches-sidebar-col {
            position: -webkit-sticky;
            position: sticky;
            top: 68px; /* 52px sticky topbar + 16px clearance */
            align-self: start;
            z-index: 40;
        }
        @media (max-width: 1080px) {
            .matches-grid-container {
                grid-template-columns: 1fr;
            }
            .matches-sidebar-col {
                position: static;
                top: auto;
            }
        }
        .view-sidebar-card {
            background: #0C0C0E;
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.45);
            max-height: calc(100vh - 84px);
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .view-nav-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid var(--xp-border-subtle);
            background: #060608;
        }
        .view-nav-btn:hover {
            background: #141418;
            border-color: var(--xp-border);
        }
        .view-nav-btn.active {
            background: rgba(217, 78, 0, 0.12) !important;
            border: 1px solid rgba(217, 78, 0, 0.45) !important;
            border-left: 3px solid var(--xp-orange) !important;
        }
        .view-nav-btn.active .nav-btn-title {
            color: var(--xp-orange) !important;
            font-weight: 700;
        }
        .view-nav-btn.active .nav-btn-badge {
            background: var(--xp-orange) !important;
            color: #000000 !important;
            font-weight: 700;
        }
    </style>
    <!-- Top KPI 4 Cards: Real Competition Vital Signs -->
    <div class="grid-kpi-4">
        <!-- Card 1: Season Progress -->
        <div class="kpi-card">
            <span class="kpi-label">Season progress</span>
            <div class="kpi-value">
                {{ $compStats['finished'] }}
                <span style="font-size:16px;color:var(--xp-muted);font-weight:500;">/ {{ $compStats['total'] }}</span>
            </div>
            <div class="kpi-delta positive" style="gap:5px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
                <span>{{ $compStats['progress_pct'] }}%</span>
                <span class="kpi-delta-context">played ({{ $compStats['scheduled'] }} left)</span>
            </div>
        </div>

        <!-- Card 2: Scoring Rate -->
        <div class="kpi-card">
            <span class="kpi-label">Scoring average</span>
            <div class="kpi-value">
                {{ number_format($compStats['goals_per_match'], 2) }}
                <span style="font-size:13px;color:var(--xp-muted);font-weight:500;">goals/game</span>
            </div>
            <div class="kpi-delta" style="gap:5px;color:var(--xp-orange);">
                <span>{{ $compStats['total_goals'] }} goals</span>
                <span class="kpi-delta-context">Home {{ number_format($compStats['home_goals_avg'], 1) }} &bull; Away {{ number_format($compStats['away_goals_avg'], 1) }}</span>
            </div>
        </div>

        <!-- Card 3: Home Advantage -->
        <div class="kpi-card">
            <span class="kpi-label">Home advantage</span>
            <div class="kpi-value">{{ $compStats['home_win_pct'] }}%</div>
            <div class="kpi-delta" style="gap:5px;color:var(--xp-text);">
                <span>{{ $compStats['home_wins'] }}W</span>
                <span class="kpi-delta-context">&bull; {{ $compStats['draws'] }}D &bull; {{ $compStats['away_wins'] }}L (Away {{ $compStats['away_win_pct'] }}%)</span>
            </div>
        </div>

        <!-- Card 4: Model Predictions -->
        <div class="kpi-card">
            <span class="kpi-label">Model predictions</span>
            <div class="kpi-value">
                {{ $compStats['predictions_count'] }}
                <span style="font-size:13px;color:var(--xp-muted);font-weight:500;">active</span>
            </div>
            <a href="{{ route('changelog') }}" class="kpi-delta positive" style="gap:5px; text-decoration: none;" title="View Model Changelog &amp; Architecture">
                <span>Dixon-Coles</span>
                <span class="kpi-delta-context">v1.2.1-h2h &bull; Docs &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Main Chart Card: Real Matchday Scoring & Goal Intensity Trend -->
    <div class="card" style="margin-bottom:14px;padding:20px 22px;">
        <div class="card-header" style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <div class="card-title" style="display:flex;align-items:center;gap:8px;">
                    <span>Matchday Scoring &amp; Goal Intensity Trend</span>
                    <span class="badge" style="background:#141418;border:1px solid var(--xp-border);font-size:11px;color:var(--xp-orange);font-weight:700;">
                        {{ $competition?->name }}
                    </span>
                </div>
                <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">
                    Total goals scored per round across Season {{ $selectedSeason }}/{{ substr((string)($selectedSeason+1), -2) }}.
                </div>
            </div>

            <!-- Season Selector Dropdown -->
            <form method="GET" action="{{ route('dashboard') }}" style="margin:0;">
                <input type="hidden" name="competition" value="{{ $selectedComp }}">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <select name="season" class="select-pill" onchange="this.form.submit()">
                    @foreach ($seasons as $s)
                        <option value="{{ $s }}" {{ $selectedSeason === (int)$s ? 'selected' : '' }}>
                            Season {{ $s }}/{{ substr((string)($s+1), -2) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @php
            $scoringItems = $compStats['matchday_scoring'];
            $scoringCount = $scoringItems->count();
            $maxGoals = max(36, (int)($compStats['highest_round']?->total_goals ?? 30) + 4);
            $chartWidth = 840;
            $chartHeight = 110;
            $paddingLeft = 35;
            $baseY = 140;

            $points = [];
            $pathD = '';
            $areaD = '';

            if ($scoringCount > 0) {
                foreach ($scoringItems as $idx => $item) {
                    $x = $scoringCount > 1 
                        ? round($paddingLeft + ($idx * ($chartWidth / ($scoringCount - 1))), 1) 
                        : round($paddingLeft + ($chartWidth / 2), 1);
                    $y = round($baseY - (($item->total_goals / $maxGoals) * $chartHeight), 1);
                    $points[] = [
                        'x' => $x, 
                        'y' => $y, 
                        'goals' => $item->total_goals, 
                        'matchday' => $item->matchday, 
                        'avg' => $item->avg_goals
                    ];
                }

                foreach ($points as $i => $pt) {
                    if ($i === 0) {
                        $pathD = "M {$pt['x']} {$pt['y']}";
                        $areaD = "M {$pt['x']} {$baseY} L {$pt['x']} {$pt['y']}";
                    } else {
                        $prev = $points[$i - 1];
                        $cx1 = round($prev['x'] + ($pt['x'] - $prev['x']) / 2, 1);
                        $cy1 = $prev['y'];
                        $cx2 = $cx1;
                        $cy2 = $pt['y'];
                        $pathD .= " C {$cx1} {$cy1}, {$cx2} {$cy2}, {$pt['x']} {$pt['y']}";
                        $areaD .= " C {$cx1} {$cy1}, {$cx2} {$cy2}, {$pt['x']} {$pt['y']}";
                    }
                }
                $lastPt = end($points);
                $areaD .= " L {$lastPt['x']} {$baseY} Z";
            }

            $avgY = $compStats['avg_goals_per_round'] > 0 
                ? round($baseY - (($compStats['avg_goals_per_round'] / $maxGoals) * $chartHeight), 1) 
                : $baseY;
        @endphp

        <!-- SVG Line Chart in vibrant xp-orange with dynamic points -->
        <div style="width:100%;height:190px;position:relative;">
            @if ($scoringCount > 0)
                <svg viewBox="0 0 910 175" preserveAspectRatio="none" style="width:100%;height:100%;overflow:visible;">
                    <defs>
                        <linearGradient id="trendGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#FF6B00" stop-opacity="0.22" />
                            <stop offset="100%" stop-color="#FF6B00" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>

                    <!-- Horizontal grid lines -->
                    <line x1="20" y1="30" x2="890" y2="30" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />
                    <line x1="20" y1="70" x2="890" y2="70" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />
                    <line x1="20" y1="110" x2="890" y2="110" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />

                    <!-- League Average Reference Line -->
                    @if ($compStats['avg_goals_per_round'] > 0)
                        <line x1="20" y1="{{ $avgY }}" x2="890" y2="{{ $avgY }}" stroke="#6B6B70" stroke-width="1.2" stroke-dasharray="4,4" />
                        <text x="888" y="{{ max(22, $avgY - 5) }}" fill="#A1A1A6" font-size="10" font-weight="600" text-anchor="end">
                            Avg: {{ $compStats['avg_goals_per_round'] }} goals/round
                        </text>
                    @endif

                    <!-- Area fill under the curve -->
                    <path d="{{ $areaD }}" fill="url(#trendGradient)" />

                    <!-- Smooth orange trendline -->
                    <path d="{{ $pathD }}" fill="none" stroke="#FF6B00" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />

                    <!-- Interactive Data Points and Labels -->
                    @foreach ($points as $pt)
                        <!-- Point circle -->
                        <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4.5" fill="#FF6B00" stroke="#0C0C0E" stroke-width="2" />
                        
                        <!-- Value badge above point -->
                        <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 8 }}" fill="#FFFFFF" font-size="11" font-weight="700" text-anchor="middle">
                            {{ $pt['goals'] }}
                        </text>

                        <!-- Matchday label on X-axis -->
                        <text x="{{ $pt['x'] }}" y="165" fill="#A1A1A6" font-size="11" font-weight="600" text-anchor="middle">
                            MD {{ $pt['matchday'] }}
                        </text>
                    @endforeach
                </svg>
            @else
                <div style="height:100%;display:flex;align-items:center;justify-content:center;color:var(--xp-muted);font-size:13px;">
                    No finished matchday scoring recorded for this season yet.
                </div>
            @endif
        </div>

        <!-- Footer Stats Bar -->
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid var(--xp-border);margin-top:10px;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:14px;font-size:12px;">
                @if ($compStats['highest_round'])
                    <div style="display:flex;align-items:center;gap:6px;color:var(--xp-ok);font-weight:600;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                        <span>Peak Round: Matchday {{ $compStats['highest_round']->matchday }}</span>
                        <span style="color:var(--xp-text);background:rgba(61,220,132,0.15);padding:1px 6px;border-radius:4px;">
                            {{ $compStats['highest_round']->total_goals }} goals
                        </span>
                    </div>
                @endif
                <div style="color:var(--xp-muted);">
                    League Scoring Pace: <strong style="color:var(--xp-text);">{{ number_format($compStats['goals_per_match'], 2) }}</strong> goals/match
                </div>
            </div>

            <a href="#fixtures-section" style="color:var(--xp-orange);font-size:12px;text-decoration:none;display:flex;align-items:center;gap:4px;font-weight:600;">
                <span>View Fixtures &amp; Predictions</span>
                <span>&darr;</span>
            </a>
        </div>
    </div>

    <!-- Secondary 3-Card Grid: Real League Dynamics -->
    <div class="grid-3-col">
        <!-- Card 1: Match Dynamics & Goal Line Distribution -->
        <div class="card" style="padding:18px 20px;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:2px;">
                    <span style="font-size:14px;font-weight:600;color:var(--xp-text);">Match Dynamics</span>
                    <span style="font-size:20px;font-weight:700;color:var(--xp-orange);">{{ $compStats['btts_pct'] }}%</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:baseline;font-size:11px;color:var(--xp-muted);margin-bottom:14px;">
                    <span>Goal line distributions</span>
                    <span>Both Teams Scored ({{ $compStats['btts_count'] }}/{{ $compStats['finished'] }})</span>
                </div>
            </div>

            <!-- Breakdown Bars -->
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:auto;">
                <!-- Over 2.5 Goals -->
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
                        <span style="color:var(--xp-muted);">Over 2.5 Goals</span>
                        <strong style="color:var(--xp-text);">{{ $compStats['over_25_pct'] }}% ({{ $compStats['over_25_count'] }})</strong>
                    </div>
                    <div style="width:100%;height:6px;background:#141418;border-radius:3px;overflow:hidden;border:1px solid var(--xp-border);">
                        <div style="width:{{ $compStats['over_25_pct'] }}%;height:100%;background:var(--xp-orange);border-radius:3px;"></div>
                    </div>
                </div>

                <!-- Over 1.5 Goals -->
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
                        <span style="color:var(--xp-muted);">Over 1.5 Goals</span>
                        <strong style="color:var(--xp-text);">{{ $compStats['over_15_pct'] }}% ({{ $compStats['over_15_count'] }})</strong>
                    </div>
                    <div style="width:100%;height:6px;background:#141418;border-radius:3px;overflow:hidden;border:1px solid var(--xp-border);">
                        <div style="width:{{ $compStats['over_15_pct'] }}%;height:100%;background:#FFFFFF;border-radius:3px;"></div>
                    </div>
                </div>

                <!-- Clean Sheets -->
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
                        <span style="color:var(--xp-muted);">Clean Sheets</span>
                        <strong style="color:var(--xp-text);">{{ $compStats['clean_sheets_pct'] }}% ({{ $compStats['clean_sheets_count'] }})</strong>
                    </div>
                    <div style="width:100%;height:6px;background:#141418;border-radius:3px;overflow:hidden;border:1px solid var(--xp-border);">
                        <div style="width:{{ $compStats['clean_sheets_pct'] }}%;height:100%;background:#6B6B70;border-radius:3px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Dynamic Outcome Distribution Donut -->
        <div class="card" style="padding:18px 20px;">
            <div style="margin-bottom:10px;">
                <div style="font-size:14px;font-weight:600;color:var(--xp-text);">Outcome Distribution</div>
                <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">{{ $competition?->name }} match outcomes.</div>
            </div>

            <!-- Dynamic Donut SVG with strict UI tokens -->
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:120px;">
                <svg width="130" height="130" viewBox="0 0 140 140">
                    <!-- Background ring -->
                    <circle cx="70" cy="70" r="46" fill="none" stroke="var(--xp-border)" stroke-width="24" />

                    @if ($compStats['finished'] > 0)
                        <!-- Slice 1: Home Win in xp-orange (#FF6B00) -->
                        <circle cx="70" cy="70" r="46" fill="none" stroke="#FF6B00" stroke-width="24"
                                stroke-dasharray="{{ $compStats['donut']['home_dash'] }} 289" 
                                stroke-dashoffset="{{ $compStats['donut']['home_offset'] }}" 
                                transform="rotate(-90 70 70)" />

                        <!-- Slice 2: Away Win in xp-away (#6B6B70) -->
                        <circle cx="70" cy="70" r="46" fill="none" stroke="#6B6B70" stroke-width="24"
                                stroke-dasharray="{{ $compStats['donut']['away_dash'] }} 289" 
                                stroke-dashoffset="{{ $compStats['donut']['away_offset'] }}" 
                                transform="rotate(-90 70 70)" />

                        <!-- Slice 3: Draw in xp-text (#FFFFFF) -->
                        <circle cx="70" cy="70" r="46" fill="none" stroke="#FFFFFF" stroke-width="24"
                                stroke-dasharray="{{ $compStats['donut']['draw_dash'] }} 289" 
                                stroke-dashoffset="{{ $compStats['donut']['draw_offset'] }}" 
                                transform="rotate(-90 70 70)" />
                    @endif

                    <!-- Inner hole -->
                    <circle cx="70" cy="70" r="34" fill="var(--xp-surface)" />

                    <!-- Center total text -->
                    <text x="70" y="67" fill="#FFFFFF" font-size="15" font-weight="700" text-anchor="middle">
                        {{ $compStats['finished'] }}
                    </text>
                    <text x="70" y="80" fill="#A1A1A6" font-size="9" font-weight="600" letter-spacing="0.05em" text-anchor="middle">
                        PLAYED
                    </text>
                </svg>
            </div>

            <!-- Legend with dynamic counts -->
            <div style="display:flex;justify-content:center;gap:12px;font-size:10px;color:var(--xp-muted);margin-top:6px;">
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:7px;height:7px;border-radius:2px;background:#FF6B00;display:inline-block;"></span>
                    <strong style="color:var(--xp-text);">Home {{ $compStats['home_win_pct'] }}% ({{ $compStats['home_wins'] }})</strong>
                </span>
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:7px;height:7px;border-radius:2px;background:#FFFFFF;display:inline-block;"></span>
                    <strong style="color:var(--xp-text);">Draw {{ $compStats['draw_pct'] }}% ({{ $compStats['draws'] }})</strong>
                </span>
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:7px;height:7px;border-radius:2px;background:#6B6B70;display:inline-block;"></span>
                    <strong style="color:var(--xp-text);">Away {{ $compStats['away_win_pct'] }}% ({{ $compStats['away_wins'] }})</strong>
                </span>
            </div>
        </div>

        <!-- Card 3: Quick operations -->
        <div class="card" style="padding:18px 20px;">
            <div style="margin-bottom:12px;">
                <div style="font-size:14px;font-weight:600;color:var(--xp-text);">Competition Operations</div>
                <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">{{ $competition?->short_name }} model &amp; data shortcuts.</div>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <!-- Action 1: Predict Next Matchday -->
                <a href="#fixtures-section" style="display:flex;align-items:center;justify-content:space-between;padding:8px 8px;border-radius:6px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='var(--xp-surface-hover)'" onmouseout="this.style.background='transparent'">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="color:var(--xp-orange);display:flex;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--xp-text);">Upcoming Fixtures</div>
                            <div style="font-size:11px;color:var(--xp-muted);">{{ $compStats['scheduled'] }} matches scheduled.</div>
                        </div>
                    </div>
                    <span style="color:var(--xp-away);font-size:12px;">&rsaquo;</span>
                </a>

                <!-- Action 2: Model Track Record -->
                <a href="{{ route('track-record') }}" style="display:flex;align-items:center;justify-content:space-between;padding:8px 8px;border-radius:6px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='var(--xp-surface-hover)'" onmouseout="this.style.background='transparent'">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="color:var(--xp-orange);display:flex;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 20v-6M6 20V10M18 20V4"></path>
                            </svg>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--xp-text);">Model Track Record</div>
                            <div style="font-size:11px;color:var(--xp-muted);">Cross-league accuracy &amp; calibration.</div>
                        </div>
                    </div>
                    <span style="color:var(--xp-away);font-size:12px;">&rsaquo;</span>
                </a>

                <!-- Action 3: Dev API Inspector -->
                <a href="{{ route('dev.api.index') }}" style="display:flex;align-items:center;justify-content:space-between;padding:8px 8px;border-radius:6px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='var(--xp-surface-hover)'" onmouseout="this.style.background='transparent'">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="color:var(--xp-orange);display:flex;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--xp-text);">API Inspector</div>
                            <div style="font-size:11px;color:var(--xp-muted);">Audit raw provider calls &amp; quota.</div>
                        </div>
                    </div>
                    <span style="color:var(--xp-away);font-size:12px;">&rsaquo;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Matches Grid: Main Content Stream + In-Page View Navigator Sidebar -->
    <div class="matches-grid-container">
        <!-- Main Content Stream (Spotlight & Fixtures List) -->
        <div style="min-width: 0; display:flex; flex-direction:column; gap:20px;">
            @if ($statusFilter === 'all' || $statusFilter === 'future')
                <!-- Next Matchday & Upcoming Predictions Spotlight -->
                <div id="upcoming-spotlight" class="card" style="padding:22px;border:1px solid rgba(217, 78, 0, 0.35);background:linear-gradient(180deg, #111114 0%, #0A0A0C 100%);box-shadow:0 8px 24px rgba(0,0,0,0.6);">
                    <!-- Spotlight Header -->
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;border-bottom:1px solid var(--xp-border);padding-bottom:14px;">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--xp-orange);box-shadow:0 0 10px var(--xp-orange);"></span>
                                <h2 style="font-size:17px;font-weight:700;color:var(--xp-text);letter-spacing:-0.02em;">
                                    Next Matchday &amp; Upcoming Predictions
                                </h2>
                                <span class="badge badge-warning" style="background:rgba(217, 78, 0, 0.15);color:var(--xp-orange);border:1px solid rgba(217, 78, 0, 0.35);font-size:10px;">
                                    {{ $nextMatchdayInfo['matchday'] ?? 'Next Round' }}
                                </span>
                            </div>
                            <p style="font-size:12px;color:var(--xp-muted);margin:0;">
                                {{ $competition?->name }} &bull; {{ $nextMatchdayInfo['date_range'] ?? 'Upcoming' }} &bull; {{ $upcomingMatches->count() }} Matches Scheduled &bull; Transparent Poisson &amp; Dixon-Coles model provenance
                            </p>
                        </div>

                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="badge badge-success" style="font-size:10px;">
                                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--xp-ok);margin-right:4px;"></span>
                                Model: v1.2.1-h2h active
                            </span>
                            <span class="badge" style="background:#16161A;border:1px solid var(--xp-border);color:var(--xp-muted);font-size:10px;">
                                Zero Data Leakage Guard
                            </span>
                        </div>
                    </div>

                    @if ($upcomingMatches->isEmpty())
                        <div style="text-align:center;padding:2.5rem 1rem;">
                            <h3 style="color:var(--xp-muted);font-size:14px;margin-bottom:6px;">No upcoming matches scheduled</h3>
                            <p style="color:var(--xp-muted);font-size:12px;margin-bottom:14px;">Sync current fixtures to populate the next matchday.</p>
                            <form method="POST" action="{{ route('settings.action') }}">
                                @csrf
                                <input type="hidden" name="action" value="sync_matches">
                                <button type="submit" class="btn btn-secondary btn-sm">Sync Upcoming Fixtures</button>
                            </form>
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            @foreach ($upcomingMatches as $match)
                                @php
                                    $prediction = $match->predictions->first();
                                @endphp
                                <div style="background:#0D0D10;border:1px solid var(--xp-border);border-radius:8px;padding:16px;transition:border-color 0.15s ease;">
                                    <!-- Top Metadata Row -->
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--xp-border-subtle);">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span class="badge badge-warning" style="font-size:10px;">
                                                {{ $match->status === 'finished' ? 'Full Time' : 'Upcoming' }}
                                            </span>
                                            <span style="font-size:12px;color:var(--xp-text);font-weight:600;">
                                                {{ $match->kickoff_at ? $match->kickoff_at->format('l, d M Y') : 'TBD' }}
                                            </span>
                                            <span style="font-size:11px;color:var(--xp-muted);">
                                                &bull; {{ $match->kickoff_at ? $match->kickoff_at->format('H:i') . ' UTC' : '' }} &bull; Matchday {{ $match->matchday ?? '—' }}
                                            </span>
                                        </div>

                                        @if ($prediction)
                                            @php
                                                $pH = (float) $prediction->p_home;
                                                $pD = (float) $prediction->p_draw;
                                                $pA = (float) $prediction->p_away;
                                                $topPick = $pH >= $pA && $pH >= $pD ? ($match->homeTeam?->short_name . ' Favored (' . round($pH * 100, 1) . '%)') : ($pA >= $pH && $pA >= $pD ? ($match->awayTeam?->short_name . ' Favored (' . round($pA * 100, 1) . '%)') : 'Draw Lean (' . round($pD * 100, 1) . '%)');
                                            @endphp
                                            <div style="display:flex;align-items:center;gap:6px;">
                                                <span style="font-size:11px;font-weight:700;color:var(--xp-orange);background:rgba(217, 78, 0, 0.10);border:1px solid rgba(217, 78, 0, 0.25);padding:2px 8px;border-radius:4px;">
                                                    {{ $topPick }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Matchup Teams Display -->
                                    <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:20px;align-items:center;margin-bottom:14px;background:#060608;padding:12px 16px;border-radius:6px;border:1px solid var(--xp-border-subtle);">
                                        <div style="text-align:right;">
                                            <div style="font-size:15px;font-weight:700;color:var(--xp-text);letter-spacing:-0.01em;">
                                                {{ $match->homeTeam?->name }}
                                            </div>
                                            <div style="font-size:11px;color:var(--xp-orange);margin-top:1px;font-weight:600;">
                                                Home &bull; {{ $match->homeTeam?->short_name }}
                                            </div>
                                        </div>

                                        <div style="text-align:center;padding:0 8px;">
                                            <div style="width:32px;height:32px;border-radius:50%;background:#141418;border:1px solid var(--xp-border);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                                <span style="font-size:10px;font-weight:800;color:var(--xp-away);letter-spacing:0.04em;">VS</span>
                                            </div>
                                        </div>

                                        <div style="text-align:left;">
                                            <div style="font-size:15px;font-weight:700;color:var(--xp-text);letter-spacing:-0.01em;">
                                                {{ $match->awayTeam?->name }}
                                            </div>
                                            <div style="font-size:11px;color:var(--xp-away);margin-top:1px;font-weight:600;">
                                                Away &bull; {{ $match->awayTeam?->short_name }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- The Prediction Section -->
                                    @if ($prediction)
                                        <div style="background:var(--xp-black);border:1px solid var(--xp-border);border-radius:6px;padding:14px;margin-bottom:10px;">
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                                                <div style="display:flex;align-items:center;gap:6px;">
                                                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--xp-away);letter-spacing:0.04em;">
                                                        Win / Draw / Loss Probabilities ({{ $prediction->model_version }})
                                                    </span>
                                                </div>
                                                <div style="font-size:12px;color:var(--xp-muted);">
                                                    Expected Goals: <strong style="color:var(--xp-orange);font-size:13px;">{{ $prediction->exp_home_goals }}</strong> &ndash; <strong style="color:var(--xp-away);font-size:13px;">{{ $prediction->exp_away_goals }}</strong>
                                                </div>
                                            </div>

                                            @php
                                                $pH = round((float) $prediction->p_home * 100, 1);
                                                $pD = round((float) $prediction->p_draw * 100, 1);
                                                $pA = round((float) $prediction->p_away * 100, 1);
                                            @endphp

                                            <!-- Probability Bars per Chart Rule: Home in dark orange (#D94E00), Draw in white (#FFFFFF), Away in gray (#6B6B70) -->
                                            <div style="height:26px;border-radius:5px;overflow:hidden;display:flex;margin-bottom:10px;font-size:12px;font-weight:700;line-height:26px;text-align:center;box-shadow:inset 0 1px 3px rgba(0,0,0,0.5);">
                                                <div style="width:{{ $pH }}%;background:var(--xp-orange);color:#000000;min-width:38px;">
                                                    {{ $pH }}%
                                                </div>
                                                <div style="width:{{ $pD }}%;background:var(--xp-text);color:#000000;min-width:38px;">
                                                    {{ $pD }}%
                                                </div>
                                                <div style="width:{{ $pA }}%;background:var(--xp-away);color:#FFFFFF;min-width:38px;">
                                                    {{ $pA }}%
                                                </div>
                                            </div>

                                            <!-- Probability Legend -->
                                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;font-size:12px;color:var(--xp-muted);padding-bottom:10px;border-bottom:1px solid var(--xp-border-subtle);">
                                                <span style="display:flex;align-items:center;gap:5px;">
                                                    <span style="display:inline-block;width:9px;height:9px;background:var(--xp-orange);border-radius:2px;"></span>
                                                    <strong style="color:var(--xp-text);">Home: {{ $match->homeTeam?->short_name }}</strong> ({{ $pH }}%)
                                                </span>
                                                <span style="display:flex;align-items:center;gap:5px;">
                                                    <span style="display:inline-block;width:9px;height:9px;background:var(--xp-text);border-radius:2px;"></span>
                                                    <strong style="color:var(--xp-text);">Draw</strong> ({{ $pD }}%)
                                                </span>
                                                <span style="display:flex;align-items:center;gap:5px;">
                                                    <span style="display:inline-block;width:9px;height:9px;background:var(--xp-away);border-radius:2px;"></span>
                                                    <strong style="color:var(--xp-text);">Away: {{ $match->awayTeam?->short_name }}</strong> ({{ $pA }}%)
                                                </span>
                                            </div>

                                            <!-- Top Scorelines -->
                                            @if (!empty($prediction->top_scorelines))
                                                <div style="margin-top:10px;display:flex;align-items:center;flex-wrap:wrap;gap:8px;font-size:11px;">
                                                    <span style="color:var(--xp-away);font-weight:600;">Most Probable Scorelines:</span>
                                                    @foreach (array_slice($prediction->top_scorelines, 0, 5) as $ts)
                                                        <span style="background:var(--xp-surface);border:1px solid var(--xp-border);padding:2px 8px;border-radius:4px;color:var(--xp-text);">
                                                            <strong style="color:var(--xp-orange);">{{ $ts['home'] }}-{{ $ts['away'] }}</strong> ({{ round($ts['prob'] * 100, 1) }}%)
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- "HOW THIS PREDICTION WAS CALCULATED" MODEL BREAKDOWN -->
                                        @include('dashboard.partials.feature-breakdown', ['prediction' => $prediction, 'match' => $match])
                                    @else
                                        <!-- Match without prediction yet -->
                                        <div style="background:var(--xp-black);border:1px dashed var(--xp-border);border-radius:6px;padding:16px;text-align:center;margin-bottom:10px;">
                                            <div style="font-size:12px;color:var(--xp-muted);margin-bottom:10px;">
                                                Prediction not yet computed for this upcoming fixture.
                                            </div>
                                            <form method="POST" action="{{ route('matches.predict', $match->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    + Compute Poisson Prediction
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Integrated Fixtures & Predictions Section -->
            <div id="fixtures-section" class="card" style="padding:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:18px;border-bottom:1px solid var(--xp-border);padding-bottom:14px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <h2 style="font-size:16px;font-weight:700;color:var(--xp-text);margin:0;">{{ $currentViewLabel }} &bull; Fixtures &amp; Predictions</h2>
                            <span class="badge" style="background:#16161A;border:1px solid var(--xp-border);color:var(--xp-orange);font-size:10px;font-weight:700;">
                                {{ $matches->total() }} matches
                            </span>
                        </div>
                        <p style="font-size:12px;color:var(--xp-muted);margin-top:2px;">
                            Showing {{ $matches->count() }} of {{ $matches->total() }} matches &bull; Filter matches by season and status with full prediction audit trails
                        </p>
                    </div>

                    <!-- Filter Controls with Specific Date Selector -->
                    <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="hidden" name="competition" value="{{ $selectedComp }}">
                        
                        <!-- Quick Matchday Dates Jump -->
                        @if (!empty($availableMatchDates) && $availableMatchDates->isNotEmpty())
                            <select class="select-pill" onchange="if(this.value) window.location.href=this.value;" title="Jump directly to dates with confirmed fixtures">
                                <option value="">📅 Matchdays ({{ $availableMatchDates->count() }} rounds)...</option>
                                @foreach ($availableMatchDates as $amd)
                                    <option value="{{ route('dashboard', array_merge(request()->except(['page', 'preset']), ['competition' => $selectedComp, 'date' => $amd->match_date])) }}" {{ $selectedDate === $amd->match_date ? 'selected' : '' }}>
                                        {{ \Carbon\CarbonImmutable::parse($amd->match_date)->format('D, d M Y') }} ({{ $amd->count }} {{ $amd->count === 1 ? 'match' : 'matches' }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <!-- Date Selector Input -->
                        <div style="display:flex;align-items:center;background:#0C0C0E;border:1px solid var(--xp-border);border-radius:6px;padding:2px 6px;gap:6px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--xp-orange);">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <input type="date" name="date" value="{{ $selectedDate }}" class="select-pill" style="border:none;background:transparent;padding:4px 2px;font-size:11px;color:var(--xp-text);outline:none;" title="Select specific match date" onchange="this.form.submit()">
                        </div>

                        @if ($selectedDate || !empty($selectedTimeSlot) || !empty($selectedTime))
                            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => $statusFilter]) }}" class="btn btn-secondary btn-sm" style="padding:4px 8px;font-size:11px;color:var(--xp-orange);border-color:rgba(217,78,0,0.3);" title="Clear date and view all matches">
                                ✕ Clear
                            </a>
                        @endif

                        <select name="season" class="select-pill" onchange="this.form.submit()">
                            @foreach ($seasons as $s)
                                <option value="{{ $s }}" {{ $selectedSeason === $s ? 'selected' : '' }}>Season {{ $s }}/{{ substr((string)($s+1), -2) }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="select-pill" onchange="this.form.submit()">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Matches ({{ $matchCounts['all'] }})</option>
                            <option value="future" {{ $statusFilter === 'future' ? 'selected' : '' }}>Future Matches ({{ $matchCounts['future'] }})</option>
                            <option value="today" {{ $statusFilter === 'today' ? 'selected' : '' }}>Today's Matches ({{ $matchCounts['today'] }})</option>
                            <option value="past" {{ $statusFilter === 'past' ? 'selected' : '' }}>Past Matches ({{ $matchCounts['past'] }})</option>
                        </select>
                    </form>
                </div>

                @if ($dateFilterInfo)
                    <div style="background:rgba(217,78,0,0.08);border:1px solid rgba(217,78,0,0.25);border-radius:6px;padding:10px 14px;margin-bottom:14px;display:flex;flex-direction:column;gap:8px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-size:12px;color:var(--xp-orange);font-weight:700;">📅 Date Filter:</span>
                                <span style="font-size:12px;color:#FFFFFF;font-weight:600;">{{ $dateFilterInfo['formatted'] }}</span>
                                @if (!empty($dateFilterInfo['time_slot_label']))
                                    <span class="badge" style="background:var(--xp-orange);color:#000000;font-size:10px;font-weight:700;">
                                        {{ $dateFilterInfo['time_slot_label'] }}
                                    </span>
                                @endif
                                <span class="badge" style="background:#0C0C0E;border:1px solid var(--xp-border);font-size:10px;color:var(--xp-text);">
                                    {{ $matches->total() }} matches found
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => $dateFilterInfo['prev_date']])) }}" class="btn btn-secondary btn-sm" style="padding:3px 8px;font-size:11px;">
                                    &larr; Prev
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => \Carbon\CarbonImmutable::now('UTC')->format('Y-m-d')])) }}" class="btn btn-secondary btn-sm" style="padding:3px 8px;font-size:11px;{{ $dateFilterInfo['is_today'] ? 'border-color:var(--xp-orange);color:var(--xp-orange);' : '' }}">
                                    Today
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => $dateFilterInfo['next_date']])) }}" class="btn btn-secondary btn-sm" style="padding:3px 8px;font-size:11px;">
                                    Next &rarr;
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['date', 'time', 'time_slot', 'preset', 'page']), ['competition' => $selectedComp])) }}" class="btn btn-secondary btn-sm" style="padding:3px 8px;font-size:11px;color:var(--xp-orange);">
                                    ✕ Reset
                                </a>
                            </div>
                        </div>

                        <!-- Kickoff Time Slot Pills -->
                        <div style="display:flex;align-items:center;gap:6px;border-top:1px solid rgba(217,78,0,0.15);padding-top:8px;font-size:11px;flex-wrap:wrap;">
                            <span style="color:var(--xp-away);font-weight:600;font-size:10.5px;">Kickoff Time Window:</span>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'all'])) }}"
                               style="padding:2px 8px;border-radius:4px;text-decoration:none;font-size:10.5px;font-weight:600;border:1px solid {{ !$selectedTimeSlot && !$selectedTime ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ !$selectedTimeSlot && !$selectedTime ? 'var(--xp-orange)' : '#101014' }};color:{{ !$selectedTimeSlot && !$selectedTime ? '#000000' : 'var(--xp-text)' }};">
                                All Kickoffs
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'early'])) }}"
                               style="padding:2px 8px;border-radius:4px;text-decoration:none;font-size:10.5px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'early' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'early' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'early' ? '#000000' : 'var(--xp-text)' }};">
                                🌤 Early (12:30)
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'afternoon'])) }}"
                               style="padding:2px 8px;border-radius:4px;text-decoration:none;font-size:10.5px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'afternoon' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'afternoon' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'afternoon' ? '#000000' : 'var(--xp-text)' }};">
                                ⚽ Prime (15:00)
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'evening'])) }}"
                               style="padding:2px 8px;border-radius:4px;text-decoration:none;font-size:10.5px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'evening' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'evening' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'evening' ? '#000000' : 'var(--xp-text)' }};">
                                🌙 Night (17:30+)
                            </a>
                        </div>
                    </div>
                @endif

                @if ($matches->isEmpty())
                    @if ($statusFilter === 'today')
                        <div style="text-align:center;padding:3rem 1rem;">
                            <div style="width:44px;height:44px;border-radius:50%;background:#141418;border:1px solid var(--xp-border);display:flex;align-items:center;justify-content:center;margin:0 auto 14px auto;color:var(--xp-away);">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 14 14"></polyline>
                                </svg>
                            </div>
                            <h3 style="color:var(--xp-text);font-size:15px;margin-bottom:6px;font-weight:700;">No matches kicking off today</h3>
                            <p style="color:var(--xp-muted);font-size:12px;margin-bottom:18px;max-width:440px;margin-left:auto;margin-right:auto;">
                                There are no {{ $competition?->name }} fixtures scheduled for today ({{ \Carbon\CarbonImmutable::now('UTC')->format('l, d M Y') }}). Switch to future matches to see the upcoming round of Poisson predictions.
                            </p>
                            <div style="display:flex;justify-content:center;gap:10px;">
                                <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'future']) }}" class="btn btn-primary btn-sm">
                                    View Future Matches ({{ $matchCounts['future'] }}) &rarr;
                                </a>
                            </div>
                        </div>
                    @else
                        <div style="text-align:center;padding:3rem 1rem;">
                            <h3 style="color:var(--xp-muted);font-size:15px;margin-bottom:6px;">No matches found</h3>
                            <p style="color:var(--xp-muted);font-size:12px;margin-bottom:18px;">There are no matches loaded for the selected filters.</p>
                            <form method="POST" action="{{ route('settings.action') }}">
                                @csrf
                                <input type="hidden" name="action" value="sync_matches">
                                <button type="submit" class="btn btn-secondary">Sync Fixtures</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        @foreach ($matches as $match)
                            @php
                                $prediction = $match->predictions->first();
                            @endphp
                            <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:8px;padding:14px 16px;">
                                <!-- Match Top Row -->
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--xp-border-subtle);">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="badge {{ $match->status === 'finished' ? 'badge-info' : 'badge-warning' }}">
                                            {{ $match->status === 'finished' ? 'Full Time' : 'Scheduled' }}
                                        </span>
                                        <span style="font-size:11px;color:var(--xp-muted);font-weight:500;">
                                            Matchday {{ $match->matchday ?? '—' }} &bull; {{ $match->kickoff_at ? $match->kickoff_at->format('D, d M Y H:i T') : 'TBD' }}
                                        </span>
                                    </div>
                                    @if ($match->status === 'finished' && $match->home_goals !== null)
                                        <div style="font-size:13px;font-weight:700;color:var(--xp-text);background:var(--xp-black);padding:2px 10px;border-radius:4px;border:1px solid var(--xp-border);">
                                            Score: {{ $match->home_goals }} &ndash; {{ $match->away_goals }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Teams Display -->
                                <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:16px;align-items:center;margin-bottom:12px;">
                                    <div style="text-align:right;">
                                        <div style="font-size:14px;font-weight:700;color:var(--xp-text);">{{ $match->homeTeam?->name }}</div>
                                        <div style="font-size:11px;color:var(--xp-away);">Home &bull; {{ $match->homeTeam?->short_name }}</div>
                                    </div>
                                    <div style="text-align:center;padding:0 8px;">
                                        <span style="font-size:11px;font-weight:700;color:var(--xp-away);text-transform:uppercase;">vs</span>
                                    </div>
                                    <div style="text-align:left;">
                                        <div style="font-size:14px;font-weight:700;color:var(--xp-text);">{{ $match->awayTeam?->name }}</div>
                                        <div style="font-size:11px;color:var(--xp-away);">Away &bull; {{ $match->awayTeam?->short_name }}</div>
                                    </div>
                                </div>

                                <!-- Prediction Box -->
                                @if ($prediction)
                                    <div style="background:var(--xp-black);border:1px solid var(--xp-border);border-radius:6px;padding:12px;margin-bottom:10px;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                            <div style="display:flex;align-items:center;gap:6px;">
                                                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--xp-away);">Poisson Probabilities ({{ $prediction->model_version }})</span>
                                                @if ($prediction->low_confidence)
                                                    <span class="badge badge-warning" style="font-size:9px;">Low Confidence</span>
                                                @endif
                                            </div>
                                            <div style="font-size:11px;color:var(--xp-muted);">
                                                Expected Goals: <strong style="color:var(--xp-orange);">{{ $prediction->exp_home_goals }}</strong> &ndash; <strong style="color:var(--xp-away);">{{ $prediction->exp_away_goals }}</strong>
                                            </div>
                                        </div>

                                        @php
                                            $pH = round((float) $prediction->p_home * 100, 1);
                                            $pD = round((float) $prediction->p_draw * 100, 1);
                                            $pA = round((float) $prediction->p_away * 100, 1);
                                        @endphp

                                        <!-- Probability Bars per Chart Rule: Home in dark orange (#D94E00), Draw in white (#FFFFFF), Away in gray (#6B6B70) -->
                                        <div style="height:22px;border-radius:5px;overflow:hidden;display:flex;margin-bottom:8px;font-size:11px;font-weight:700;line-height:22px;text-align:center;">
                                            <div style="width:{{ $pH }}%;background:var(--xp-orange);color:#000000;min-width:32px;">
                                                {{ $pH }}%
                                            </div>
                                            <div style="width:{{ $pD }}%;background:var(--xp-text);color:#000000;min-width:32px;">
                                                {{ $pD }}%
                                            </div>
                                            <div style="width:{{ $pA }}%;background:var(--xp-away);color:#FFFFFF;min-width:32px;">
                                                {{ $pA }}%
                                            </div>
                                        </div>

                                        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--xp-muted);">
                                            <span><span style="display:inline-block;width:7px;height:7px;background:var(--xp-orange);border-radius:2px;margin-right:4px;"></span>Home: {{ $match->homeTeam?->short_name }} ({{ $pH }}%)</span>
                                            <span><span style="display:inline-block;width:7px;height:7px;background:var(--xp-text);border-radius:2px;margin-right:4px;"></span>Draw ({{ $pD }}%)</span>
                                            <span><span style="display:inline-block;width:7px;height:7px;background:var(--xp-away);border-radius:2px;margin-right:4px;"></span>Away: {{ $match->awayTeam?->short_name }} ({{ $pA }}%)</span>
                                        </div>

                                        @if (!empty($prediction->top_scorelines))
                                            <div style="margin-top:8px;padding-top:6px;border-top:1px dashed var(--xp-border);font-size:11px;color:var(--xp-away);display:flex;gap:12px;flex-wrap:wrap;">
                                                <span>Top Scorelines:</span>
                                                @foreach (array_slice($prediction->top_scorelines, 0, 4) as $ts)
                                                    <span style="color:var(--xp-muted);font-weight:600;">
                                                        {{ $ts['home'] }}-{{ $ts['away'] }} ({{ round($ts['prob'] * 100, 1) }}%)
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- "HOW THIS PREDICTION WAS CALCULATED" MODEL BREAKDOWN -->
                                    @include('dashboard.partials.feature-breakdown', ['prediction' => $prediction, 'match' => $match])
                                @endif

                                <!-- Actions Row -->
                                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
                                    <form method="POST" action="{{ route('matches.predict', $match->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            {{ $prediction ? 'Re-Predict' : '+ Predict' }}
                                        </button>
                                    </form>
                                    @if ($prediction)
                                        <form method="POST" action="{{ route('predictions.preview', $prediction->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                {{ $prediction->previews?->first() ? 'Regenerate Preview' : 'Generate AI Preview' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination -->
                        <div style="margin-top:14px;">
                            {{ $matches->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- In-Page Match View Navigator Sidebar -->
        <div class="matches-sidebar-col">
            <aside class="view-sidebar-card">
                <!-- Status Pill & Header -->
                <div style="border-bottom: 1px solid var(--xp-border); padding-bottom: 12px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--xp-muted);display:flex;align-items:center;gap:6px;">
                            <span style="width:7px;height:7px;border-radius:50%;background:var(--xp-orange);display:inline-block;box-shadow:0 0 8px var(--xp-orange);animation:pulseDot 2s infinite;"></span>
                            Currently Viewing
                        </span>
                        <span class="badge" style="background:#141418;border:1px solid var(--xp-border);font-size:10px;color:var(--xp-orange);font-weight:700;">
                            {{ $competition?->short_name ?? 'League' }}
                        </span>
                    </div>
                    <div style="font-size:16px;font-weight:700;color:var(--xp-text);letter-spacing:-0.01em;">
                        {{ $currentViewLabel }}
                    </div>
                    <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">
                        {{ $competition?->name }} &bull; Season {{ $selectedSeason }}/{{ substr((string)($selectedSeason+1), -2) }}
                    </div>
                </div>

                <!-- Navigation List -->
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <!-- 1. Today's Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'today']) }}"
                       class="view-nav-btn {{ $statusFilter === 'today' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:26px;height:26px;border-radius:5px;background:{{ $statusFilter === 'today' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'today' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 14 14"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:12px;font-weight:600;color:var(--xp-text);">Today's Matches</div>
                                <div style="font-size:10.5px;color:var(--xp-muted);">{{ \Carbon\CarbonImmutable::now('UTC')->format('d M') }} kickoffs</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:11px;padding:2px 7px;border-radius:10px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['today'] }}
                        </span>
                    </a>

                    <!-- 2. Future Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'future']) }}"
                       class="view-nav-btn {{ $statusFilter === 'future' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:26px;height:26px;border-radius:5px;background:{{ $statusFilter === 'future' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'future' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:12px;font-weight:600;color:var(--xp-text);">Future Matches</div>
                                <div style="font-size:10.5px;color:var(--xp-muted);">Upcoming matchdays</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:11px;padding:2px 7px;border-radius:10px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['future'] }}
                        </span>
                    </a>

                    <!-- 3. Past Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'past']) }}"
                       class="view-nav-btn {{ $statusFilter === 'past' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:26px;height:26px;border-radius:5px;background:{{ $statusFilter === 'past' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'past' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:12px;font-weight:600;color:var(--xp-text);">Past Matches</div>
                                <div style="font-size:10.5px;color:var(--xp-muted);">Full time &amp; results</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:11px;padding:2px 7px;border-radius:10px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['past'] }}
                        </span>
                    </a>

                    <!-- 4. All Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'all']) }}"
                       class="view-nav-btn {{ $statusFilter === 'all' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:26px;height:26px;border-radius:5px;background:{{ $statusFilter === 'all' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'all' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:12px;font-weight:600;color:var(--xp-text);">All Matches</div>
                                <div style="font-size:10.5px;color:var(--xp-muted);">Full schedule</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:11px;padding:2px 7px;border-radius:10px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['all'] }}
                        </span>
                    </a>
                </div>

                <!-- Quick League Switcher -->
                <div style="border-top: 1px solid var(--xp-border); padding-top: 12px;">
                    <div style="font-size:10px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--xp-muted);margin-bottom:8px;">
                        Quick League Filter
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:4px;">
                        @php
                            $leaguePills = [
                                'premier-league' => 'EPL',
                                'la-liga' => 'ESP',
                                'bundesliga' => 'GER',
                                'serie-a' => 'ITA',
                                'ligue-1' => 'FRA',
                            ];
                        @endphp
                        @foreach ($leaguePills as $slug => $code)
                            <a href="{{ route('dashboard', ['competition' => $slug, 'season' => $selectedSeason, 'status' => $statusFilter]) }}"
                               style="text-align:center;padding:6px 0;font-size:10.5px;font-weight:700;border-radius:4px;text-decoration:none;border:1px solid {{ $selectedComp === $slug ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedComp === $slug ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedComp === $slug ? '#000000' : 'var(--xp-muted)' }};transition:all 0.15s ease;">
                                {{ $code }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Provenance & Quota Card -->
                <div style="border-top: 1px solid var(--xp-border); padding-top: 12px; font-size: 11px; color: var(--xp-muted); display:flex; flex-direction:column; gap:6px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Prediction Engine</span>
                        <a href="{{ route('changelog') }}" style="color:var(--xp-orange);font-size:11px;text-decoration:none;font-weight:700;" title="View Model Changelog &amp; Architecture">
                            v1.2.1-h2h &rarr;
                        </a>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Data Integrity</span>
                        <strong style="color:var(--xp-ok);display:flex;align-items:center;gap:4px;font-size:11px;">
                            <span style="display:inline-block;width:5px;height:5px;border-radius:50%;background:var(--xp-ok);"></span>
                            Zero Leakage
                        </strong>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Offline Guarantee</span>
                        <span style="color:var(--xp-away);font-size:10px;">Local DB only</span>
                    </div>
                    <a href="{{ route('changelog') }}" style="margin-top:4px;padding:6px 8px;background:rgba(217,78,0,0.1);border:1px solid rgba(217,78,0,0.25);border-radius:4px;color:var(--xp-orange);font-size:11px;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:space-between;" title="View Model Changelog &amp; Documentation">
                        <span>Model Changelog &amp; Docs</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
