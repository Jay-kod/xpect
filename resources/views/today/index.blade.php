<x-layouts.app title="Today's Fixtures Hub">
    <!-- Header & Date Navigator Bar -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <h1 style="font-size:24px;font-weight:700;color:var(--xp-text);margin:0;letter-spacing:-0.02em;">
                    Today's Fixtures Hub
                </h1>
                @if ($summary['is_today'])
                    <span class="badge" style="background:rgba(255,107,0,0.12);color:var(--xp-orange);border:1px solid rgba(255,107,0,0.3);font-size:11px;font-weight:700;display:flex;align-items:center;gap:6px;">
                        <span style="width:7px;height:7px;border-radius:50%;background:var(--xp-orange);display:inline-block;box-shadow:0 0 8px var(--xp-orange);animation:pulseDot 2s infinite;"></span>
                        TODAY LIVE
                    </span>
                @else
                    <span class="badge" style="background:#141418;color:var(--xp-muted);border:1px solid var(--xp-border);font-size:11px;font-weight:600;">
                        HISTORICAL &bull; {{ $summary['date_str'] }}
                    </span>
                @endif
            </div>
            <p style="font-size:13px;color:var(--xp-muted);margin:0;">
                Multi-league matchday command center across Premier League, La Liga, Bundesliga, Serie A, and Ligue 1.
            </p>
        </div>

        <!-- Date Controls & Batch Predict Actions -->
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <!-- Date Switcher Navigation -->
            <div style="display:flex;align-items:center;background:#0C0C0E;border:1px solid var(--xp-border);border-radius:6px;padding:3px;">
                <a href="{{ route('today', ['date' => $summary['prev_date']]) }}" class="btn" style="padding:4px 8px;font-size:12px;background:transparent;border:none;color:var(--xp-muted);text-decoration:none;" title="Previous Day">
                    &larr; Prev
                </a>

                <div style="display:flex;gap:3px;margin:0 4px;">
                    <a href="{{ route('today', ['date' => \Carbon\CarbonImmutable::now('UTC')->subDay()->format('Y-m-d')]) }}" 
                       class="btn" 
                       style="padding:4px 8px;font-size:11px;border-radius:4px;border:none;text-decoration:none;{{ $summary['is_yesterday'] ? 'background:rgba(255,107,0,0.15);color:var(--xp-orange);font-weight:700;' : 'background:transparent;color:var(--xp-muted);' }}">
                        Yesterday
                    </a>

                    <a href="{{ route('today', ['date' => $summary['today_date']]) }}" 
                       class="btn" 
                       style="padding:4px 8px;font-size:11px;border-radius:4px;border:none;text-decoration:none;{{ $summary['is_today'] ? 'background:var(--xp-orange);color:#000000;font-weight:700;' : 'background:transparent;color:var(--xp-muted);' }}">
                        Today
                    </a>

                    <a href="{{ route('today', ['date' => \Carbon\CarbonImmutable::now('UTC')->addDay()->format('Y-m-d')]) }}" 
                       class="btn" 
                       style="padding:4px 8px;font-size:11px;border-radius:4px;border:none;text-decoration:none;{{ $summary['is_tomorrow'] ? 'background:rgba(255,107,0,0.15);color:var(--xp-orange);font-weight:700;' : 'background:transparent;color:var(--xp-muted);' }}">
                        Tomorrow
                    </a>
                </div>

                <a href="{{ route('today', ['date' => $summary['next_date']]) }}" class="btn" style="padding:4px 8px;font-size:12px;background:transparent;border:none;color:var(--xp-muted);text-decoration:none;" title="Next Day">
                    Next &rarr;
                </a>
            </div>

            <!-- Calendar Picker Form -->
            <form method="GET" action="{{ route('today') }}" style="margin:0;">
                <input type="date" name="date" value="{{ $summary['date_str'] }}" class="input" style="padding:6px 10px;font-size:12px;background:#0C0C0E;border:1px solid var(--xp-border);color:var(--xp-text);border-radius:6px;" onchange="this.form.submit()">
            </form>

            @if ($summary['unpredicted'] > 0)
                <!-- Batch Predict Button -->
                <form method="POST" action="{{ route('today.predict-all') }}" style="margin:0;">
                    @csrf
                    <input type="hidden" name="date" value="{{ $summary['date_str'] }}">
                    <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:12px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        <span>Predict All Today ({{ $summary['unpredicted'] }})</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Matchday Summary KPIs -->
    <div class="grid-kpi-4" style="margin-bottom:20px;">
        <!-- Card 1: Matches on Date -->
        <div class="kpi-card">
            <span class="kpi-label">Fixtures for {{ \Carbon\CarbonImmutable::parse($summary['date_str'])->format('d M') }}</span>
            <div class="kpi-value">{{ $summary['total'] }}</div>
            <div class="kpi-delta" style="color:var(--xp-muted);font-size:11px;">
                <span>{{ $summary['display_date'] }}</span>
            </div>
        </div>

        <!-- Card 2: Predictions Coverage -->
        <div class="kpi-card">
            <span class="kpi-label">Poisson Predictions Active</span>
            <div class="kpi-value" style="color:{{ $summary['predicted'] > 0 ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                {{ $summary['predicted'] }} / {{ $summary['total'] }}
            </div>
            <div class="kpi-delta positive" style="font-size:11px;">
                <span>{{ $summary['total'] > 0 ? round(($summary['predicted'] / $summary['total']) * 100) : 0 }}% coverage</span>
                <span class="kpi-delta-context">Dixon-Coles v1.2.1-h2h</span>
            </div>
        </div>

        <!-- Card 3: Scheduled Matches -->
        <div class="kpi-card">
            <span class="kpi-label">Scheduled Kickoffs</span>
            <div class="kpi-value">{{ $summary['scheduled'] }}</div>
            <div class="kpi-delta" style="color:var(--xp-text);font-size:11px;">
                <span>Upcoming &bull; Pre-match mode</span>
            </div>
        </div>

        <!-- Card 4: Finished Matches -->
        <div class="kpi-card">
            <span class="kpi-label">Completed / Full Time</span>
            <div class="kpi-value">{{ $summary['finished'] }}</div>
            <div class="kpi-delta positive" style="font-size:11px;">
                <span>{{ $summary['finished'] }} evaluated results</span>
            </div>
        </div>
    </div>

    <!-- Fixtures Stream Grouped by Competition -->
    @if ($matches->isNotEmpty())
        <div style="display:flex;flex-direction:column;gap:22px;">
            @foreach ($groupedMatches as $competitionName => $competitionMatches)
                <div class="card" style="padding:0;overflow:hidden;border:1px solid var(--xp-border);">
                    <!-- League Header Strip -->
                    <div style="padding:14px 20px;background:#101014;border-bottom:1px solid var(--xp-border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:14px;font-weight:700;color:var(--xp-text);letter-spacing:-0.01em;">
                                {{ $competitionName }}
                            </span>
                            <span class="badge" style="background:#161618;border:1px solid var(--xp-border);font-size:10px;color:var(--xp-orange);">
                                {{ $competitionMatches->count() }} {{ \Illuminate\Support\Str::plural('Fixture', $competitionMatches->count()) }}
                            </span>
                        </div>

                        <a href="{{ route('dashboard', ['competition' => $competitionMatches->first()?->competition?->slug ?? 'premier-league']) }}" style="font-size:12px;color:var(--xp-muted);text-decoration:none;" onmouseover="this.style.color='var(--xp-orange)'" onmouseout="this.style.color='var(--xp-muted)'">
                            View Full League Standings &rarr;
                        </a>
                    </div>

                    <!-- Fixtures Grid for this Competition -->
                    <div style="display:flex;flex-direction:column;">
                        @foreach ($competitionMatches as $match)
                            @php
                                $pred = $match->predictions->first();
                                $preview = $pred?->previews?->first();
                            @endphp

                            <div style="padding:18px 20px;border-bottom:1px solid var(--xp-border-subtle);display:flex;flex-direction:column;gap:14px;">
                                <!-- Row 1: Status, Kickoff Time, Matchday -->
                                <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--xp-muted);">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        @if ($match->status === 'finished')
                                            <span class="badge" style="background:rgba(61,220,132,0.12);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:10px;font-weight:700;">
                                                FULL TIME
                                            </span>
                                        @else
                                            <span class="badge" style="background:#141418;color:var(--xp-orange);border:1px solid rgba(255,107,0,0.3);font-size:10px;font-weight:700;">
                                                SCHEDULED
                                            </span>
                                        @endif
                                        <span>Matchday {{ $match->matchday ?? 'N/A' }}</span>
                                    </div>

                                    <div style="font-family:monospace;font-size:12px;color:var(--xp-text);display:flex;align-items:center;gap:6px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span>{{ $match->kickoff_at ? $match->kickoff_at->format('H:i') . ' UTC' : 'TBD' }}</span>
                                    </div>
                                </div>

                                <!-- Row 2: Team Matchup & Score / Prediction Bar -->
                                <div style="display:grid;grid-template-columns:minmax(200px, 1.5fr) minmax(260px, 2fr) auto;gap:20px;align-items:center;">
                                    <!-- Teams -->
                                    <div>
                                        <div style="font-size:15px;font-weight:700;color:var(--xp-text);margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">
                                            <span>{{ $match->homeTeam?->name }}</span>
                                            @if ($match->status === 'finished')
                                                <strong style="color:var(--xp-orange);font-size:16px;">{{ $match->home_goals }}</strong>
                                            @endif
                                        </div>
                                        <div style="font-size:15px;font-weight:700;color:var(--xp-text);display:flex;justify-content:space-between;align-items:center;">
                                            <span>{{ $match->awayTeam?->name }}</span>
                                            @if ($match->status === 'finished')
                                                <strong style="color:var(--xp-orange);font-size:16px;">{{ $match->away_goals }}</strong>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Probabilities & Expected Goals -->
                                    <div>
                                        @if ($pred)
                                            <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:5px;">
                                                <span style="color:var(--xp-orange);font-weight:700;">Home {{ round($pred->p_home * 100, 1) }}%</span>
                                                <span style="color:var(--xp-text);font-weight:700;">Draw {{ round($pred->p_draw * 100, 1) }}%</span>
                                                <span style="color:var(--xp-away);font-weight:700;">Away {{ round($pred->p_away * 100, 1) }}%</span>
                                            </div>

                                            <!-- Multi-segment visual bar -->
                                            <div style="width:100%;height:8px;background:#0C0C0E;border-radius:4px;overflow:hidden;display:flex;border:1px solid var(--xp-border-subtle);margin-bottom:6px;">
                                                <div style="width:{{ $pred->p_home * 100 }}%;height:100%;background:var(--xp-orange);"></div>
                                                <div style="width:{{ $pred->p_draw * 100 }}%;height:100%;background:#FFFFFF;"></div>
                                                <div style="width:{{ $pred->p_away * 100 }}%;height:100%;background:#6B6B70;"></div>
                                            </div>

                                            <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--xp-muted);">
                                                <span>xG: <strong style="color:var(--xp-text);">{{ number_format((float)$pred->exp_home_goals, 2) }}</strong> &ndash; <strong style="color:var(--xp-text);">{{ number_format((float)$pred->exp_away_goals, 2) }}</strong></span>
                                                <span>Model: <span style="font-family:monospace;color:var(--xp-orange);">{{ $pred->model_version }}</span></span>
                                            </div>
                                        @else
                                            <div style="height:38px;background:#0C0C0E;border:1px dashed var(--xp-border);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--xp-muted);font-size:11px;">
                                                No Poisson prediction computed yet.
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Quick Action Buttons -->
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        @if (!$pred)
                                            <form method="POST" action="{{ route('matches.predict', $match->id) }}" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="btn btn-primary" style="font-size:11px;padding:6px 10px;">
                                                    Predict
                                                </button>
                                            </form>
                                        @else
                                            @if (!$preview)
                                                <form method="POST" action="{{ route('predictions.preview', $pred->id) }}" style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="btn" style="font-size:11px;padding:6px 10px;background:#141418;border:1px solid var(--xp-border);color:var(--xp-orange);font-weight:600;">
                                                        AI Preview
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge" style="background:rgba(61,220,132,0.12);color:var(--xp-ok);border:1px solid rgba(61,220,132,0.3);font-size:10px;font-weight:700;">
                                                    AI READY
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Row 3: Preview narrative (if generated) -->
                                @if ($preview)
                                    <div style="background:#0C0C0E;border:1px solid var(--xp-border-subtle);border-radius:6px;padding:10px 14px;font-size:12px;color:var(--xp-text);line-height:1.5;">
                                        <div style="font-size:10px;font-weight:700;color:var(--xp-muted);text-transform:uppercase;margin-bottom:4px;letter-spacing:0.05em;display:flex;justify-content:space-between;">
                                            <span>AI Match Preview &bull; {{ $preview->model }}</span>
                                            @if (!$preview->flagged)
                                                <span style="color:var(--xp-ok);">&bull; Numbers Verified</span>
                                            @else
                                                <span style="color:var(--xp-error);">&bull; Guard Flagged</span>
                                            @endif
                                        </div>
                                        {{ $preview->text }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Rich Empty State for Non-Matchday Dates -->
        <div class="card" style="padding:40px 24px;text-align:center;max-width:680px;margin:20px auto;border:1px solid var(--xp-border);background:linear-gradient(180deg, #111114 0%, #0A0A0C 100%);box-shadow:0 8px 32px rgba(0,0,0,0.5);">
            <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,107,0,0.12);border:1px solid rgba(255,107,0,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 16px auto;color:var(--xp-orange);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>

            <h3 style="font-size:18px;font-weight:700;color:var(--xp-text);margin:0 0 6px 0;">
                No matches scheduled for {{ $summary['display_date'] }}
            </h3>
            <p style="font-size:13px;color:var(--xp-muted);margin:0 0 24px 0;line-height:1.5;">
                There are no European top 5 league fixtures kicking off on this date. Use the date navigator above to inspect past matchdays or jump directly to the next upcoming scheduled round.
            </p>

            @if ($nextUpcomingFixture)
                <div style="background:#0C0C0E;border:1px solid var(--xp-border);border-radius:8px;padding:16px 20px;margin-bottom:20px;text-align:left;">
                    <div style="font-size:10px;font-weight:700;color:var(--xp-orange);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                        Next Upcoming Matchday
                    </div>
                    <div style="font-size:14px;font-weight:700;color:var(--xp-text);margin-bottom:4px;">
                        {{ $nextUpcomingFixture->homeTeam?->name }} vs {{ $nextUpcomingFixture->awayTeam?->name }}
                    </div>
                    <div style="font-size:12px;color:var(--xp-muted);display:flex;justify-content:space-between;align-items:center;">
                        <span>{{ $nextUpcomingFixture->competition?->name }} &bull; {{ $nextUpcomingFixture->kickoff_at?->format('l, d F Y') }} &bull; {{ $nextUpcomingFixture->kickoff_at?->format('H:i') }} UTC</span>
                        <a href="{{ route('today', ['date' => $nextUpcomingFixture->kickoff_at?->format('Y-m-d')]) }}" class="btn btn-primary" style="font-size:11px;padding:4px 10px;text-decoration:none;">
                            Jump to {{ $nextUpcomingFixture->kickoff_at?->format('d M') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif

            <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                <a href="{{ route('dashboard', ['competition' => 'premier-league', 'status' => 'future']) }}" class="btn" style="background:#141418;border:1px solid var(--xp-border);color:var(--xp-text);text-decoration:none;">
                    View Premier League Schedule &rarr;
                </a>
                <a href="{{ route('dashboard', ['competition' => 'la-liga', 'status' => 'future']) }}" class="btn" style="background:#141418;border:1px solid var(--xp-border);color:var(--xp-text);text-decoration:none;">
                    View La Liga Schedule &rarr;
                </a>
            </div>
        </div>
    @endif
</x-layouts.app>
