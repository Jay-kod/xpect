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
            grid-template-columns: minmax(0, 1fr) 270px;
            gap: 18px;
            align-items: start;
            margin-top: 18px;
        }
        .matches-sidebar-col {
            position: -webkit-sticky;
            position: sticky;
            top: 68px;
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
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            max-height: calc(100vh - 84px);
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .view-nav-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
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
        .analytics-subgrid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 16px;
            border-top: 1px solid var(--xp-border);
            padding-top: 14px;
            margin-top: 12px;
        }
        @media (max-width: 900px) {
            .analytics-subgrid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Top KPI Strip: Real Competition Vital Signs -->
    <div class="grid-kpi-4">
        <!-- Card 1: Season Progress -->
        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">Season progress</span>
            <div class="kpi-value" style="font-size:22px;margin:3px 0;">
                {{ $compStats['finished'] }}
                <span style="font-size:14px;color:var(--xp-muted);font-weight:500;">/ {{ $compStats['total'] }}</span>
            </div>
            <div class="kpi-delta positive" style="gap:4px;font-size:11px;">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="18 15 12 9 6 15"></polyline>
                </svg>
                <span>{{ $compStats['progress_pct'] }}%</span>
                <span class="kpi-delta-context">played ({{ $compStats['scheduled'] }} left)</span>
            </div>
        </div>

        <!-- Card 2: Scoring Rate -->
        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">Scoring average</span>
            <div class="kpi-value" style="font-size:22px;margin:3px 0;">
                {{ number_format($compStats['goals_per_match'], 2) }}
                <span style="font-size:12px;color:var(--xp-muted);font-weight:500;">goals/game</span>
            </div>
            <div class="kpi-delta" style="gap:4px;color:var(--xp-orange);font-size:11px;">
                <span>{{ $compStats['total_goals'] }} goals</span>
                <span class="kpi-delta-context">Home {{ number_format($compStats['home_goals_avg'], 1) }} &bull; Away {{ number_format($compStats['away_goals_avg'], 1) }}</span>
            </div>
        </div>

        <!-- Card 3: Home Advantage -->
        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">Home advantage</span>
            <div class="kpi-value" style="font-size:22px;margin:3px 0;">{{ $compStats['home_win_pct'] }}%</div>
            <div class="kpi-delta" style="gap:4px;color:var(--xp-text);font-size:11px;">
                <span>{{ $compStats['home_wins'] }}W</span>
                <span class="kpi-delta-context">&bull; {{ $compStats['draws'] }}D &bull; {{ $compStats['away_wins'] }}L</span>
            </div>
        </div>

        <!-- Card 4: Model Predictions -->
        <div class="kpi-card" style="padding:14px 16px;">
            <span class="kpi-label">Model predictions</span>
            <div class="kpi-value" style="font-size:22px;margin:3px 0;">
                {{ $compStats['predictions_count'] }}
                <span style="font-size:12px;color:var(--xp-muted);font-weight:500;">active</span>
            </div>
            <a href="{{ route('changelog') }}" class="kpi-delta positive" style="gap:4px;text-decoration:none;font-size:11px;" title="View Model Changelog &amp; Architecture">
                <span>Dixon-Coles</span>
                <span class="kpi-delta-context">v1.2.1-h2h &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Unified League Pulse & Analytics Card (Merged Trend Chart + Match Dynamics + Outcomes + Shortcuts) -->
    <div class="card" style="margin-bottom:14px;padding:16px 18px;">
        <!-- Card Header: Title & Season Selector -->
        <div class="card-header" style="margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <div>
                <div class="card-title" style="display:flex;align-items:center;gap:8px;font-size:14px;">
                    <span>Matchday Scoring &amp; Goal Intensity Trend</span>
                    <span class="badge" style="background:#141418;border:1px solid var(--xp-border);font-size:10.5px;color:var(--xp-orange);font-weight:700;">
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
                <select name="season" class="select-pill" style="font-size:11px;padding:4px 8px;" onchange="this.form.submit()">
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
            $chartHeight = 75;
            $paddingLeft = 35;
            $baseY = 95;

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

        <!-- Sleek SVG Line Chart -->
        <div style="width:100%;height:115px;position:relative;">
            @if ($scoringCount > 0)
                <svg viewBox="0 0 910 120" preserveAspectRatio="none" style="width:100%;height:100%;overflow:visible;">
                    <defs>
                        <linearGradient id="trendGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#FF6B00" stop-opacity="0.18" />
                            <stop offset="100%" stop-color="#FF6B00" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>

                    <!-- Horizontal grid lines -->
                    <line x1="20" y1="20" x2="890" y2="20" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />
                    <line x1="20" y1="55" x2="890" y2="55" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />
                    <line x1="20" y1="90" x2="890" y2="90" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />

                    <!-- League Average Reference Line -->
                    @if ($compStats['avg_goals_per_round'] > 0)
                        <line x1="20" y1="{{ $avgY }}" x2="890" y2="{{ $avgY }}" stroke="#6B6B70" stroke-width="1" stroke-dasharray="4,4" />
                        <text x="888" y="{{ max(14, $avgY - 4) }}" fill="#A1A1A6" font-size="9" font-weight="600" text-anchor="end">
                            Avg: {{ $compStats['avg_goals_per_round'] }} goals/round
                        </text>
                    @endif

                    <!-- Area fill under the curve -->
                    <path d="{{ $areaD }}" fill="url(#trendGradient)" />

                    <!-- Smooth orange trendline -->
                    <path d="{{ $pathD }}" fill="none" stroke="#FF6B00" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />

                    <!-- Interactive Data Points and Labels -->
                    @foreach ($points as $pt)
                        <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="3.5" fill="#FF6B00" stroke="#0C0C0E" stroke-width="1.8" />
                        <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 6 }}" fill="#FFFFFF" font-size="10" font-weight="700" text-anchor="middle">
                            {{ $pt['goals'] }}
                        </text>
                        <text x="{{ $pt['x'] }}" y="112" fill="#A1A1A6" font-size="10" font-weight="600" text-anchor="middle">
                            MD {{ $pt['matchday'] }}
                        </text>
                    @endforeach
                </svg>
            @else
                <div style="height:100%;display:flex;align-items:center;justify-content:center;color:var(--xp-muted);font-size:12px;">
                    No finished matchday scoring recorded for this season yet.
                </div>
            @endif
        </div>

        <!-- Integrated 3-Part Analytics Subgrid (Match Dynamics + Outcomes + Operations) -->
        <div class="analytics-subgrid">
            <!-- Part 1: Match Dynamics -->
            <div style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:2px;">
                        <span style="font-size:13px;font-weight:600;color:var(--xp-text);">Match Dynamics</span>
                        <span style="font-size:16px;font-weight:700;color:var(--xp-orange);">{{ $compStats['btts_pct'] }}%</span>
                    </div>
                    <div style="font-size:10.5px;color:var(--xp-muted);margin-bottom:8px;">
                        BTTS: {{ $compStats['btts_count'] }}/{{ $compStats['finished'] }} games
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <!-- Over 2.5 -->
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;margin-bottom:2px;">
                            <span style="color:var(--xp-muted);">Over 2.5 Goals</span>
                            <strong style="color:var(--xp-text);">{{ $compStats['over_25_pct'] }}% ({{ $compStats['over_25_count'] }})</strong>
                        </div>
                        <div style="width:100%;height:4px;background:#141418;border-radius:2px;overflow:hidden;">
                            <div style="width:{{ $compStats['over_25_pct'] }}%;height:100%;background:var(--xp-orange);"></div>
                        </div>
                    </div>

                    <!-- Over 1.5 -->
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;margin-bottom:2px;">
                            <span style="color:var(--xp-muted);">Over 1.5 Goals</span>
                            <strong style="color:var(--xp-text);">{{ $compStats['over_15_pct'] }}% ({{ $compStats['over_15_count'] }})</strong>
                        </div>
                        <div style="width:100%;height:4px;background:#141418;border-radius:2px;overflow:hidden;">
                            <div style="width:{{ $compStats['over_15_pct'] }}%;height:100%;background:#FFFFFF;"></div>
                        </div>
                    </div>

                    <!-- Clean Sheets -->
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;margin-bottom:2px;">
                            <span style="color:var(--xp-muted);">Clean Sheets</span>
                            <strong style="color:var(--xp-text);">{{ $compStats['clean_sheets_pct'] }}% ({{ $compStats['clean_sheets_count'] }})</strong>
                        </div>
                        <div style="width:100%;height:4px;background:#141418;border-radius:2px;overflow:hidden;">
                            <div style="width:{{ $compStats['clean_sheets_pct'] }}%;height:100%;background:#6B6B70;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part 2: Outcome Distribution Donut -->
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;border-left:1px solid var(--xp-border-subtle);border-right:1px solid var(--xp-border-subtle);padding:0 10px;">
                <div style="font-size:13px;font-weight:600;color:var(--xp-text);margin-bottom:6px;align-self:flex-start;">
                    Outcome Distribution
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <svg width="86" height="86" viewBox="0 0 140 140">
                        <circle cx="70" cy="70" r="46" fill="none" stroke="var(--xp-border)" stroke-width="22" />
                        @if ($compStats['finished'] > 0)
                            <circle cx="70" cy="70" r="46" fill="none" stroke="#FF6B00" stroke-width="22"
                                    stroke-dasharray="{{ $compStats['donut']['home_dash'] }} 289" 
                                    stroke-dashoffset="{{ $compStats['donut']['home_offset'] }}" 
                                    transform="rotate(-90 70 70)" />
                            <circle cx="70" cy="70" r="46" fill="none" stroke="#6B6B70" stroke-width="22"
                                    stroke-dasharray="{{ $compStats['donut']['away_dash'] }} 289" 
                                    stroke-dashoffset="{{ $compStats['donut']['away_offset'] }}" 
                                    transform="rotate(-90 70 70)" />
                            <circle cx="70" cy="70" r="46" fill="none" stroke="#FFFFFF" stroke-width="22"
                                    stroke-dasharray="{{ $compStats['donut']['draw_dash'] }} 289" 
                                    stroke-dashoffset="{{ $compStats['donut']['draw_offset'] }}" 
                                    transform="rotate(-90 70 70)" />
                        @endif
                        <circle cx="70" cy="70" r="34" fill="var(--xp-surface)" />
                        <text x="70" y="68" fill="#FFFFFF" font-size="14" font-weight="700" text-anchor="middle">
                            {{ $compStats['finished'] }}
                        </text>
                        <text x="70" y="80" fill="#A1A1A6" font-size="8" font-weight="600" text-anchor="middle">
                            PLAYED
                        </text>
                    </svg>

                    <div style="display:flex;flex-direction:column;gap:4px;font-size:10px;color:var(--xp-muted);">
                        <span style="display:flex;align-items:center;gap:4px;">
                            <span style="width:6px;height:6px;border-radius:2px;background:#FF6B00;display:inline-block;"></span>
                            <span style="color:var(--xp-text);font-weight:600;">Home {{ $compStats['home_win_pct'] }}%</span> ({{ $compStats['home_wins'] }})
                        </span>
                        <span style="display:flex;align-items:center;gap:4px;">
                            <span style="width:6px;height:6px;border-radius:2px;background:#FFFFFF;display:inline-block;"></span>
                            <span style="color:var(--xp-text);font-weight:600;">Draw {{ $compStats['draw_pct'] }}%</span> ({{ $compStats['draws'] }})
                        </span>
                        <span style="display:flex;align-items:center;gap:4px;">
                            <span style="width:6px;height:6px;border-radius:2px;background:#6B6B70;display:inline-block;"></span>
                            <span style="color:var(--xp-text);font-weight:600;">Away {{ $compStats['away_win_pct'] }}%</span> ({{ $compStats['away_wins'] }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- Part 3: Competition Operations -->
            <div style="display:flex;flex-direction:column;justify-content:space-between;">
                <div style="font-size:13px;font-weight:600;color:var(--xp-text);margin-bottom:6px;">
                    Competition Operations
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <a href="#fixtures-section" style="display:flex;align-items:center;justify-content:space-between;padding:5px 6px;border-radius:4px;text-decoration:none;background:#0C0C0E;border:1px solid var(--xp-border-subtle);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--xp-border)'" onmouseout="this.style.borderColor='var(--xp-border-subtle)'">
                        <div style="font-size:11px;font-weight:600;color:var(--xp-text);">Upcoming Fixtures</div>
                        <span style="color:var(--xp-orange);font-size:11px;">{{ $compStats['scheduled'] }} matches &rsaquo;</span>
                    </a>
                    <a href="{{ route('track-record') }}" style="display:flex;align-items:center;justify-content:space-between;padding:5px 6px;border-radius:4px;text-decoration:none;background:#0C0C0E;border:1px solid var(--xp-border-subtle);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--xp-border)'" onmouseout="this.style.borderColor='var(--xp-border-subtle)'">
                        <div style="font-size:11px;font-weight:600;color:var(--xp-text);">Model Track Record</div>
                        <span style="color:var(--xp-away);font-size:11px;">Audit &rsaquo;</span>
                    </a>
                    <a href="{{ route('dev.api.index') }}" style="display:flex;align-items:center;justify-content:space-between;padding:5px 6px;border-radius:4px;text-decoration:none;background:#0C0C0E;border:1px solid var(--xp-border-subtle);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--xp-border)'" onmouseout="this.style.borderColor='var(--xp-border-subtle)'">
                        <div style="font-size:11px;font-weight:600;color:var(--xp-text);">API Inspector</div>
                        <span style="color:var(--xp-away);font-size:11px;">Quota &rsaquo;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Matches Grid: Main Stream + In-Page View Navigator Sidebar -->
    <div class="matches-grid-container">
        <!-- Main Content Stream (Spotlight & Fixtures Feed) -->
        <div style="min-width: 0; display:flex; flex-direction:column; gap:16px;">
            @if ($statusFilter === 'all' || $statusFilter === 'future')
                <!-- Curated Marquee Spotlight for Next Matchday -->
                <div id="upcoming-spotlight" class="card" style="padding:18px;border:1px solid rgba(217, 78, 0, 0.35);background:linear-gradient(180deg, #111114 0%, #0A0A0C 100%);">
                    <!-- Spotlight Header -->
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;border-bottom:1px solid var(--xp-border);padding-bottom:12px;">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--xp-orange);box-shadow:0 0 8px var(--xp-orange);"></span>
                                <h2 style="font-size:15px;font-weight:700;color:var(--xp-text);margin:0;letter-spacing:-0.01em;">
                                    Next Matchday &amp; Upcoming Predictions
                                </h2>
                                <span class="badge badge-warning" style="background:rgba(217, 78, 0, 0.15);color:var(--xp-orange);border:1px solid rgba(217, 78, 0, 0.35);font-size:10px;">
                                    {{ $nextMatchdayInfo['matchday'] ?? 'Next Round' }}
                                </span>
                            </div>
                            <p style="font-size:11px;color:var(--xp-muted);margin:2px 0 0 0;">
                                {{ $competition?->name }} &bull; {{ $nextMatchdayInfo['date_range'] ?? 'Upcoming' }} &bull; {{ $upcomingMatches->count() }} Matches Scheduled
                            </p>
                        </div>

                        <div style="display:flex;align-items:center;gap:6px;">
                            <span class="badge badge-success" style="font-size:9.5px;">
                                <span style="display:inline-block;width:5px;height:5px;border-radius:50%;background:var(--xp-ok);margin-right:3px;"></span>
                                Model: v1.2.1-h2h active
                            </span>
                            <span class="badge" style="background:#16161A;border:1px solid var(--xp-border);color:var(--xp-muted);font-size:9.5px;">
                                Zero Data Leakage Guard
                            </span>
                        </div>
                    </div>

                    @if ($upcomingMatches->isEmpty())
                        <div style="text-align:center;padding:2rem 1rem;">
                            <h3 style="color:var(--xp-muted);font-size:13px;margin-bottom:4px;">No upcoming matches scheduled</h3>
                            <p style="color:var(--xp-muted);font-size:11px;margin-bottom:12px;">Sync fixtures to populate the next matchday.</p>
                            <form method="POST" action="{{ route('settings.action') }}">
                                @csrf
                                <input type="hidden" name="action" value="sync_matches">
                                <button type="submit" class="btn btn-secondary btn-sm">Sync Upcoming Fixtures</button>
                            </form>
                        </div>
                    @else
                        @php
                            $featuredMatch = $upcomingMatches->first();
                            $featuredPrediction = $featuredMatch->predictions->first();
                            $otherUpcoming = $upcomingMatches->slice(1);
                        @endphp

                        <!-- Primary Featured Marquee Match Card -->
                        <div style="background:#0D0D10;border:1px solid var(--xp-border);border-radius:7px;padding:14px;">
                            <!-- Top Metadata Row -->
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid var(--xp-border-subtle);">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="badge badge-warning" style="font-size:9.5px;">
                                        {{ $featuredMatch->status === 'finished' ? 'Full Time' : 'Upcoming' }}
                                    </span>
                                    <span style="font-size:12px;color:var(--xp-text);font-weight:600;">
                                        {{ $featuredMatch->kickoff_at ? $featuredMatch->kickoff_at->format('l, d M Y') : 'TBD' }}
                                    </span>
                                    <span style="font-size:11px;color:var(--xp-muted);">
                                        &bull; {{ $featuredMatch->kickoff_at ? $featuredMatch->kickoff_at->format('H:i') . ' UTC' : '' }} &bull; Matchday {{ $featuredMatch->matchday ?? '—' }}
                                    </span>
                                </div>

                                @if ($featuredPrediction)
                                    @php
                                        $pH = (float) $featuredPrediction->p_home;
                                        $pD = (float) $featuredPrediction->p_draw;
                                        $pA = (float) $featuredPrediction->p_away;
                                        $topPick = $pH >= $pA && $pH >= $pD ? ($featuredMatch->homeTeam?->short_name . ' Favored (' . round($pH * 100, 1) . '%)') : ($pA >= $pH && $pA >= $pD ? ($featuredMatch->awayTeam?->short_name . ' Favored (' . round($pA * 100, 1) . '%)') : 'Draw Lean (' . round($pD * 100, 1) . '%)');
                                    @endphp
                                    <span style="font-size:10.5px;font-weight:700;color:var(--xp-orange);background:rgba(217, 78, 0, 0.10);border:1px solid rgba(217, 78, 0, 0.25);padding:2px 8px;border-radius:4px;">
                                        {{ $topPick }}
                                    </span>
                                @endif
                            </div>

                            <!-- Teams Matchup Block -->
                            <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:16px;align-items:center;margin-bottom:12px;background:#060608;padding:10px 14px;border-radius:6px;border:1px solid var(--xp-border-subtle);">
                                <div style="text-align:right;display:flex;align-items:center;justify-content:flex-end;gap:10px;">
                                    <div>
                                        <div style="font-size:14px;font-weight:700;color:var(--xp-text);">
                                            {{ $featuredMatch->homeTeam?->name }}
                                        </div>
                                        <div style="font-size:11px;color:var(--xp-orange);font-weight:600;">
                                            Home &bull; {{ $featuredMatch->homeTeam?->short_name }}
                                        </div>
                                    </div>
                                    <x-team-badge :team="$featuredMatch->homeTeam" size="26" />
                                </div>

                                <div style="text-align:center;padding:0 6px;">
                                    <div style="width:28px;height:28px;border-radius:50%;background:#141418;border:1px solid var(--xp-border);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                        <span style="font-size:9.5px;font-weight:800;color:var(--xp-away);">VS</span>
                                    </div>
                                </div>

                                <div style="text-align:left;display:flex;align-items:center;justify-content:flex-start;gap:10px;">
                                    <x-team-badge :team="$featuredMatch->awayTeam" size="26" />
                                    <div>
                                        <div style="font-size:14px;font-weight:700;color:var(--xp-text);">
                                            {{ $featuredMatch->awayTeam?->name }}
                                        </div>
                                        <div style="font-size:11px;color:var(--xp-away);font-weight:600;">
                                            Away &bull; {{ $featuredMatch->awayTeam?->short_name }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Prediction Summary Box -->
                            @if ($featuredPrediction)
                                @php
                                    $pH = round((float) $featuredPrediction->p_home * 100, 1);
                                    $pD = round((float) $featuredPrediction->p_draw * 100, 1);
                                    $pA = round((float) $featuredPrediction->p_away * 100, 1);
                                @endphp
                                <div style="background:var(--xp-black);border:1px solid var(--xp-border);border-radius:6px;padding:12px;margin-bottom:8px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:11px;">
                                        <span style="font-weight:700;text-transform:uppercase;color:var(--xp-away);">
                                            Poisson Probabilities ({{ $featuredPrediction->model_version }})
                                        </span>
                                        <span style="color:var(--xp-muted);">
                                            Expected Goals: <strong style="color:var(--xp-orange);">{{ $featuredPrediction->exp_home_goals }}</strong> &ndash; <strong style="color:var(--xp-away);">{{ $featuredPrediction->exp_away_goals }}</strong>
                                        </span>
                                    </div>

                                    <!-- Probability Bar -->
                                    <div style="height:22px;border-radius:4px;overflow:hidden;display:flex;margin-bottom:8px;font-size:11px;font-weight:700;line-height:22px;text-align:center;">
                                        <div style="width:{{ $pH }}%;background:var(--xp-orange);color:#000000;min-width:34px;">
                                            {{ $pH }}%
                                        </div>
                                        <div style="width:{{ $pD }}%;background:var(--xp-text);color:#000000;min-width:34px;">
                                            {{ $pD }}%
                                        </div>
                                        <div style="width:{{ $pA }}%;background:var(--xp-away);color:#FFFFFF;min-width:34px;">
                                            {{ $pA }}%
                                        </div>
                                    </div>

                                    <!-- Probability Legend -->
                                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--xp-muted);">
                                        <span><span style="display:inline-block;width:7px;height:7px;background:var(--xp-orange);border-radius:2px;margin-right:4px;"></span>Home: {{ $featuredMatch->homeTeam?->short_name }} ({{ $pH }}%)</span>
                                        <span><span style="display:inline-block;width:7px;height:7px;background:var(--xp-text);border-radius:2px;margin-right:4px;"></span>Draw ({{ $pD }}%)</span>
                                        <span><span style="display:inline-block;width:7px;height:7px;background:var(--xp-away);border-radius:2px;margin-right:4px;"></span>Away: {{ $featuredMatch->awayTeam?->short_name }} ({{ $pA }}%)</span>
                                    </div>

                                    @if (!empty($featuredPrediction->top_scorelines))
                                        <div style="margin-top:8px;padding-top:6px;border-top:1px dashed var(--xp-border);font-size:11px;color:var(--xp-away);display:flex;gap:10px;flex-wrap:wrap;">
                                            <span>Scorelines:</span>
                                            @foreach (array_slice($featuredPrediction->top_scorelines, 0, 4) as $ts)
                                                <span style="color:var(--xp-muted);font-weight:600;">
                                                    {{ $ts['home'] }}-{{ $ts['away'] }} ({{ round($ts['prob'] * 100, 1) }}%)
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <!-- Feature Calculation Breakdown Accordion -->
                                @include('dashboard.partials.feature-breakdown', ['prediction' => $featuredPrediction, 'match' => $featuredMatch])
                            @else
                                <div style="background:var(--xp-black);border:1px dashed var(--xp-border);border-radius:6px;padding:12px;text-align:center;margin-bottom:8px;">
                                    <span style="font-size:11px;color:var(--xp-muted);margin-right:10px;">Prediction not yet computed for this fixture.</span>
                                    <form method="POST" action="{{ route('matches.predict', $featuredMatch->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">+ Compute Poisson Prediction</button>
                                    </form>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                                <form method="POST" action="{{ route('matches.predict', $featuredMatch->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        {{ $featuredPrediction ? 'Re-Predict' : '+ Predict' }}
                                    </button>
                                </form>
                                @if ($featuredPrediction)
                                    <form method="POST" action="{{ route('predictions.preview', $featuredPrediction->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            {{ $featuredPrediction->previews?->first() ? 'Regenerate Preview' : 'Generate AI Preview' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Compact Secondary Grid for Remaining Round Fixtures (Prevents giant 10-card wall) -->
                        @if ($otherUpcoming->isNotEmpty())
                            <div style="margin-top:12px;border-top:1px solid var(--xp-border-subtle);padding-top:10px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <span style="font-size:11px;font-weight:700;color:var(--xp-muted);text-transform:uppercase;letter-spacing:0.04em;">
                                        Other Matchday Fixtures ({{ $otherUpcoming->count() }})
                                    </span>
                                    <a href="#fixtures-section" style="font-size:11px;color:var(--xp-orange);text-decoration:none;">
                                        View Full Schedule &darr;
                                    </a>
                                </div>

                                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:8px;">
                                    @foreach ($otherUpcoming->take(4) as $subMatch)
                                        @php
                                            $subPred = $subMatch->predictions->first();
                                        @endphp
                                        <div style="background:#09090C;border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;display:flex;align-items:center;justify-content:space-between;">
                                            <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                                                <div style="display:flex;align-items:center;gap:4px;">
                                                    <x-team-badge :team="$subMatch->homeTeam" size="18" />
                                                    <span style="font-size:12px;font-weight:600;color:var(--xp-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        {{ $subMatch->homeTeam?->short_name }}
                                                    </span>
                                                </div>
                                                <span style="font-size:10px;color:var(--xp-away);">v</span>
                                                <div style="display:flex;align-items:center;gap:4px;">
                                                    <x-team-badge :team="$subMatch->awayTeam" size="18" />
                                                    <span style="font-size:12px;font-weight:600;color:var(--xp-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        {{ $subMatch->awayTeam?->short_name }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div style="display:flex;align-items:center;gap:6px;">
                                                @if ($subPred)
                                                    <span class="badge" style="background:rgba(217, 78, 0, 0.12);color:var(--xp-orange);font-size:10px;font-weight:700;">
                                                        {{ round($subPred->p_home * 100) }}% | {{ round($subPred->p_draw * 100) }}% | {{ round($subPred->p_away * 100) }}%
                                                    </span>
                                                @else
                                                    <span style="font-size:10px;color:var(--xp-away);">
                                                        {{ $subMatch->kickoff_at ? $subMatch->kickoff_at->format('D H:i') : 'TBD' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <!-- Clean Integrated Fixtures & Predictions Section -->
            <div id="fixtures-section" class="card" style="padding:16px 18px;">
                <!-- Header & Filters Bar -->
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;border-bottom:1px solid var(--xp-border);padding-bottom:12px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <h2 style="font-size:15px;font-weight:700;color:var(--xp-text);margin:0;">
                                {{ $currentViewLabel }} &bull; Fixtures &amp; Predictions
                            </h2>
                            <span class="badge" style="background:#16161A;border:1px solid var(--xp-border);color:var(--xp-orange);font-size:10px;font-weight:700;">
                                {{ $matches->total() }} matches
                            </span>
                        </div>
                    </div>

                    <!-- Consolidated Filter Controls -->
                    <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin:0;">
                        <input type="hidden" name="competition" value="{{ $selectedComp }}">
                        
                        <!-- Quick Matchday Dates Jump -->
                        @if (!empty($availableMatchDates) && $availableMatchDates->isNotEmpty())
                            <select class="select-pill" style="font-size:11px;padding:3px 6px;" onchange="if(this.value) window.location.href=this.value;" title="Jump directly to confirmed matchday dates">
                                <option value="">📅 Matchdays ({{ $availableMatchDates->count() }} rounds)...</option>
                                @foreach ($availableMatchDates as $amd)
                                    <option value="{{ route('dashboard', array_merge(request()->except(['page', 'preset']), ['competition' => $selectedComp, 'date' => $amd->match_date])) }}" {{ $selectedDate === $amd->match_date ? 'selected' : '' }}>
                                        {{ \Carbon\CarbonImmutable::parse($amd->match_date)->format('D, d M') }} ({{ $amd->count }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        <!-- Date Selector Input -->
                        <div style="display:flex;align-items:center;background:#0C0C0E;border:1px solid var(--xp-border);border-radius:4px;padding:2px 6px;gap:4px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--xp-orange);">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <input type="date" name="date" value="{{ $selectedDate }}" class="select-pill" style="border:none;background:transparent;padding:2px;font-size:11px;color:var(--xp-text);outline:none;" title="Select specific match date" onchange="this.form.submit()">
                        </div>

                        @if ($selectedDate || !empty($selectedTimeSlot) || !empty($selectedTime))
                            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => $statusFilter]) }}" class="btn btn-secondary btn-sm" style="padding:3px 6px;font-size:10.5px;color:var(--xp-orange);" title="Clear date and view all matches">
                                ✕ Clear
                            </a>
                        @endif

                        <select name="season" class="select-pill" style="font-size:11px;padding:3px 6px;" onchange="this.form.submit()">
                            @foreach ($seasons as $s)
                                <option value="{{ $s }}" {{ $selectedSeason === $s ? 'selected' : '' }}>Season {{ $s }}/{{ substr((string)($s+1), -2) }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="select-pill" style="font-size:11px;padding:3px 6px;" onchange="this.form.submit()">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All ({{ $matchCounts['all'] }})</option>
                            <option value="future" {{ $statusFilter === 'future' ? 'selected' : '' }}>Future ({{ $matchCounts['future'] }})</option>
                            <option value="today" {{ $statusFilter === 'today' ? 'selected' : '' }}>Today ({{ $matchCounts['today'] }})</option>
                            <option value="past" {{ $statusFilter === 'past' ? 'selected' : '' }}>Past ({{ $matchCounts['past'] }})</option>
                        </select>
                    </form>
                </div>

                <!-- Date Filter Alert (if date active) -->
                @if ($dateFilterInfo)
                    <div style="background:rgba(217,78,0,0.08);border:1px solid rgba(217,78,0,0.25);border-radius:6px;padding:8px 12px;margin-bottom:12px;display:flex;flex-direction:column;gap:6px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:11.5px;color:var(--xp-orange);font-weight:700;">📅 Date Filter:</span>
                                <span style="font-size:11.5px;color:#FFFFFF;font-weight:600;">{{ $dateFilterInfo['formatted'] }}</span>
                                @if (!empty($dateFilterInfo['time_slot_label']))
                                    <span class="badge" style="background:var(--xp-orange);color:#000000;font-size:9.5px;font-weight:700;">
                                        {{ $dateFilterInfo['time_slot_label'] }}
                                    </span>
                                @endif
                                <span class="badge" style="background:#0C0C0E;border:1px solid var(--xp-border);font-size:9.5px;color:var(--xp-text);">
                                    {{ $matches->total() }} matches
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => $dateFilterInfo['prev_date']])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;">
                                    &larr; Prev
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => \Carbon\CarbonImmutable::now('UTC')->format('Y-m-d')])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;{{ $dateFilterInfo['is_today'] ? 'border-color:var(--xp-orange);color:var(--xp-orange);' : '' }}">
                                    Today
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => $dateFilterInfo['next_date']])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;">
                                    Next &rarr;
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['date', 'time', 'time_slot', 'preset', 'page']), ['competition' => $selectedComp])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;color:var(--xp-orange);">
                                    ✕ Reset
                                </a>
                            </div>
                        </div>

                        <!-- Kickoff Window Pills -->
                        <div style="display:flex;align-items:center;gap:5px;border-top:1px solid rgba(217,78,0,0.15);padding-top:6px;font-size:10.5px;flex-wrap:wrap;">
                            <span style="color:var(--xp-away);font-weight:600;font-size:10px;">Time Window:</span>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'all'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ !$selectedTimeSlot && !$selectedTime ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ !$selectedTimeSlot && !$selectedTime ? 'var(--xp-orange)' : '#101014' }};color:{{ !$selectedTimeSlot && !$selectedTime ? '#000000' : 'var(--xp-text)' }};">
                                All Kickoffs
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'early'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'early' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'early' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'early' ? '#000000' : 'var(--xp-text)' }};">
                                🌤 Early Kickoff
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'afternoon'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'afternoon' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'afternoon' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'afternoon' ? '#000000' : 'var(--xp-text)' }};">
                                ⚽ Prime Afternoon
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'evening'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'evening' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'evening' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'evening' ? '#000000' : 'var(--xp-text)' }};">
                                🌙 Evening (17:30+)
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Fixtures Listing -->
                @if ($matches->isEmpty())
                    @if ($statusFilter === 'today')
                        <div style="text-align:center;padding:2.5rem 1rem;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#141418;border:1px solid var(--xp-border);display:flex;align-items:center;justify-content:center;margin:0 auto 10px auto;color:var(--xp-away);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 14 14"></polyline>
                                </svg>
                            </div>
                            <h3 style="color:var(--xp-text);font-size:14px;margin-bottom:4px;font-weight:700;">No matches kicking off today</h3>
                            <p style="color:var(--xp-muted);font-size:11px;margin-bottom:14px;max-width:380px;margin-left:auto;margin-right:auto;">
                                There are no {{ $competition?->name }} fixtures scheduled for today. Switch to future matches to see upcoming predictions.
                            </p>
                            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'future']) }}" class="btn btn-primary btn-sm">
                                View Future Matches ({{ $matchCounts['future'] }}) &rarr;
                            </a>
                        </div>
                    @else
                        <div style="text-align:center;padding:2.5rem 1rem;">
                            <h3 style="color:var(--xp-muted);font-size:13px;margin-bottom:4px;">No matches found</h3>
                            <p style="color:var(--xp-muted);font-size:11px;margin-bottom:14px;">There are no matches loaded for the selected filters.</p>
                            <form method="POST" action="{{ route('settings.action') }}">
                                @csrf
                                <input type="hidden" name="action" value="sync_matches">
                                <button type="submit" class="btn btn-secondary btn-sm">Sync Fixtures</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach ($matches as $match)
                            @php
                                $prediction = $match->predictions->first();
                            @endphp
                            <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:7px;padding:12px 14px;">
                                <!-- Top Match Meta Row -->
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid var(--xp-border-subtle);">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <span class="badge {{ $match->status === 'finished' ? 'badge-info' : 'badge-warning' }}" style="font-size:9.5px;">
                                            {{ $match->status === 'finished' ? 'Full Time' : 'Scheduled' }}
                                        </span>
                                        <span style="font-size:10.5px;color:var(--xp-muted);font-weight:500;">
                                            Matchday {{ $match->matchday ?? '—' }} &bull; {{ $match->kickoff_at ? $match->kickoff_at->format('D, d M Y H:i T') : 'TBD' }}
                                        </span>
                                    </div>
                                    @if ($match->status === 'finished' && $match->home_goals !== null)
                                        <div style="font-size:12px;font-weight:700;color:var(--xp-text);background:var(--xp-black);padding:2px 8px;border-radius:3px;border:1px solid var(--xp-border);">
                                            Score: {{ $match->home_goals }} &ndash; {{ $match->away_goals }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Teams Display -->
                                <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:12px;align-items:center;margin-bottom:8px;">
                                    <div style="text-align:right;display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                                        <div>
                                            <div style="font-size:13.5px;font-weight:700;color:var(--xp-text);">{{ $match->homeTeam?->name }}</div>
                                            <div style="font-size:10.5px;color:var(--xp-away);">Home &bull; {{ $match->homeTeam?->short_name }}</div>
                                        </div>
                                        <x-team-badge :team="$match->homeTeam" size="22" />
                                    </div>
                                    <div style="text-align:center;padding:0 4px;">
                                        <span style="font-size:10px;font-weight:700;color:var(--xp-away);text-transform:uppercase;">vs</span>
                                    </div>
                                    <div style="text-align:left;display:flex;align-items:center;justify-content:flex-start;gap:8px;">
                                        <x-team-badge :team="$match->awayTeam" size="22" />
                                        <div>
                                            <div style="font-size:13.5px;font-weight:700;color:var(--xp-text);">{{ $match->awayTeam?->name }}</div>
                                            <div style="font-size:10.5px;color:var(--xp-away);">Away &bull; {{ $match->awayTeam?->short_name }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Prediction Summary (Clean & Compact) -->
                                @if ($prediction)
                                    @php
                                        $pH = round((float) $prediction->p_home * 100, 1);
                                        $pD = round((float) $prediction->p_draw * 100, 1);
                                        $pA = round((float) $prediction->p_away * 100, 1);
                                    @endphp
                                    <div style="background:var(--xp-black);border:1px solid var(--xp-border);border-radius:5px;padding:8px 10px;margin-bottom:6px;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:10.5px;">
                                            <span style="font-weight:700;color:var(--xp-away);text-transform:uppercase;">
                                                Poisson ({{ $prediction->model_version }})
                                                @if ($prediction->low_confidence)
                                                    <span class="badge badge-warning" style="font-size:8.5px;padding:1px 4px;">Low Conf</span>
                                                @endif
                                            </span>
                                            <span style="color:var(--xp-muted);">
                                                xG: <strong style="color:var(--xp-orange);">{{ $prediction->exp_home_goals }}</strong> &ndash; <strong style="color:var(--xp-away);">{{ $prediction->exp_away_goals }}</strong>
                                            </span>
                                        </div>

                                        <!-- Probability Bar -->
                                        <div style="height:18px;border-radius:3px;overflow:hidden;display:flex;font-size:10px;font-weight:700;line-height:18px;text-align:center;">
                                            <div style="width:{{ $pH }}%;background:var(--xp-orange);color:#000000;min-width:30px;" title="Home Win: {{ $pH }}%">
                                                {{ $pH }}%
                                            </div>
                                            <div style="width:{{ $pD }}%;background:var(--xp-text);color:#000000;min-width:30px;" title="Draw: {{ $pD }}%">
                                                {{ $pD }}%
                                            </div>
                                            <div style="width:{{ $pA }}%;background:var(--xp-away);color:#FFFFFF;min-width:30px;" title="Away Win: {{ $pA }}%">
                                                {{ $pA }}%
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Actions Row -->
                                <div style="display:flex;justify-content:flex-end;gap:6px;margin-top:4px;">
                                    <form method="POST" action="{{ route('matches.predict', $match->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding:2px 8px;font-size:10.5px;">
                                            {{ $prediction ? 'Re-Predict' : '+ Predict' }}
                                        </button>
                                    </form>
                                    @if ($prediction)
                                        <form method="POST" action="{{ route('predictions.preview', $prediction->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" style="padding:2px 8px;font-size:10.5px;">
                                                {{ $prediction->previews?->first() ? 'Regenerate Preview' : 'Generate AI Preview' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination -->
                        <div style="margin-top:10px;">
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
                <div style="border-bottom: 1px solid var(--xp-border); padding-bottom: 10px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:9.5px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--xp-muted);display:flex;align-items:center;gap:5px;">
                            <span style="width:6px;height:6px;border-radius:50%;background:var(--xp-orange);display:inline-block;"></span>
                            Currently Viewing
                        </span>
                        <span class="badge" style="background:#141418;border:1px solid var(--xp-border);font-size:9.5px;color:var(--xp-orange);font-weight:700;">
                            {{ $competition?->short_name ?? 'League' }}
                        </span>
                    </div>
                    <div style="font-size:14px;font-weight:700;color:var(--xp-text);letter-spacing:-0.01em;">
                        {{ $currentViewLabel }}
                    </div>
                    <div style="font-size:10.5px;color:var(--xp-muted);margin-top:2px;">
                        {{ $competition?->name }} &bull; Season {{ $selectedSeason }}/{{ substr((string)($selectedSeason+1), -2) }}
                    </div>
                </div>

                <!-- Navigation List -->
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <!-- 1. Today's Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'today']) }}"
                       class="view-nav-btn {{ $statusFilter === 'today' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'today' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'today' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 14 14"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">Today's Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">{{ \Carbon\CarbonImmutable::now('UTC')->format('d M') }} kickoffs</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['today'] }}
                        </span>
                    </a>

                    <!-- 2. Future Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'future']) }}"
                       class="view-nav-btn {{ $statusFilter === 'future' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'future' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'future' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">Future Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">Upcoming matchdays</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['future'] }}
                        </span>
                    </a>

                    <!-- 3. Past Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'past']) }}"
                       class="view-nav-btn {{ $statusFilter === 'past' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'past' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'past' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">Past Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">Full time &amp; results</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['past'] }}
                        </span>
                    </a>

                    <!-- 4. All Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'all']) }}"
                       class="view-nav-btn {{ $statusFilter === 'all' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'all' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'all' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">All Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">Full schedule</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['all'] }}
                        </span>
                    </a>
                </div>

                <!-- Quick League Switcher -->
                <div style="border-top: 1px solid var(--xp-border); padding-top: 10px;">
                    <div style="font-size:9.5px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--xp-muted);margin-bottom:6px;">
                        Quick League Filter
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:3px;">
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
                               style="text-align:center;padding:5px 0;font-size:10px;font-weight:700;border-radius:4px;text-decoration:none;border:1px solid {{ $selectedComp === $slug ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedComp === $slug ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedComp === $slug ? '#000000' : 'var(--xp-muted)' }};transition:all 0.15s ease;">
                                {{ $code }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Provenance & Quota Card -->
                <div style="border-top: 1px solid var(--xp-border); padding-top: 10px; font-size: 10.5px; color: var(--xp-muted); display:flex; flex-direction:column; gap:5px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Engine</span>
                        <a href="{{ route('changelog') }}" style="color:var(--xp-orange);font-size:10.5px;text-decoration:none;font-weight:700;" title="View Model Changelog &amp; Architecture">
                            v1.2.1-h2h &rarr;
                        </a>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Integrity</span>
                        <strong style="color:var(--xp-ok);display:flex;align-items:center;gap:4px;font-size:10.5px;">
                            <span style="display:inline-block;width:5px;height:5px;border-radius:50%;background:var(--xp-ok);"></span>
                            Zero Leakage
                        </strong>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Offline</span>
                        <span style="color:var(--xp-away);font-size:10px;">Local DB only</span>
                    </div>
                    <a href="{{ route('changelog') }}" style="margin-top:4px;padding:5px 8px;background:rgba(217,78,0,0.1);border:1px solid rgba(217,78,0,0.25);border-radius:4px;color:var(--xp-orange);font-size:10.5px;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:space-between;" title="View Model Changelog &amp; Documentation">
                        <span>Model Changelog &amp; Docs</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
