@props(['match', 'prediction' => null])

@php
    $historyService = app(\App\Services\Matches\MatchHistoryService::class);
    $historyData = $historyService->getDetailsForMatch($match);
    $h2h = $historyData['h2h'];
    $perf = $historyData['performance'];
    $motm = $perf['motm'] ?? null;
    $hasEvals = $perf['has_evaluations'] ?? false;
    $isFinished = $match->status === 'finished';

    $homeTeam = $match->homeTeam;
    $awayTeam = $match->awayTeam;

    $homeRating = $perf['home_team_rating'] ?? null;
    $awayRating = $perf['away_team_rating'] ?? null;

    $homePlayers = $perf['home_players'] ?? collect();
    $awayPlayers = $perf['away_players'] ?? collect();

    $uniqueTabId = 'mhr-' . $match->id;
@endphp

<details class="match-history-details" id="history-details-{{ $match->id }}" style="margin-top:10px;background:#09090C;border:1px solid var(--xp-border);border-radius:6px;overflow:hidden;">
    <summary style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-size:12px;font-weight:700;color:var(--xp-orange);cursor:pointer;user-select:none;list-style:none;background:rgba(217, 78, 0, 0.05);transition:background 0.15s ease;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:var(--xp-orange);">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span style="letter-spacing:0.2px;">Match History, Goals, Man of the Match &amp; Player Ratings</span>

            <div style="display:inline-flex;align-items:center;gap:6px;margin-left:6px;">
                @if ($h2h['total_played'] > 0)
                    <span class="badge" style="background:#14151C;border:1px solid var(--xp-border);color:var(--xp-text);font-size:9.5px;padding:2px 6px;">
                        {{ $h2h['total_played'] }} H2H Clashes
                    </span>
                @endif
                @if ($motm)
                    <span class="badge" style="background:rgba(217, 78, 0, 0.15);border:1px solid rgba(217, 78, 0, 0.4);color:var(--xp-orange);font-size:9.5px;padding:2px 6px;display:inline-flex;align-items:center;gap:3px;">
                        <span>★ MOTM:</span>
                        <strong>{{ $motm->player_name }}</strong>
                        <span>({{ number_format((float)$motm->rating, 1) }})</span>
                    </span>
                @endif
                @if ($homeRating && $awayRating)
                    <span class="badge" style="background:rgba(61, 220, 132, 0.12);border:1px solid rgba(61, 220, 132, 0.3);color:var(--xp-ok);font-size:9.5px;padding:2px 6px;">
                        Ratings: {{ $homeRating }} vs {{ $awayRating }}
                    </span>
                @endif
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--xp-muted);">
            <span>Expand Details</span>
            <svg class="chevron-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="transition:transform 0.2s ease;">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
    </summary>

    <div style="padding:16px;border-top:1px solid var(--xp-border);background:#060608;display:flex;flex-direction:column;gap:14px;">

        <!-- Interactive Sub-Tab Bar -->
        <div class="mhr-tab-bar" style="display:flex;gap:6px;overflow-x:auto;padding-bottom:4px;border-bottom:1px solid var(--xp-border);">
            <button type="button" class="btn btn-sm mhr-tab-btn active" data-tab="tab-h2h-{{ $uniqueTabId }}" onclick="switchMhrTab(this, 'tab-h2h-{{ $uniqueTabId }}')">
                <span>📜 Match History (H2H)</span>
                @if($h2h['total_played'] > 0)
                    <span class="badge" style="background:#14151C;font-size:9px;padding:1px 5px;margin-left:4px;">{{ $h2h['total_played'] }}</span>
                @endif
            </button>

            <button type="button" class="btn btn-sm mhr-tab-btn" data-tab="tab-goals-{{ $uniqueTabId }}" onclick="switchMhrTab(this, 'tab-goals-{{ $uniqueTabId }}')">
                <span>⚽ Goals Breakdown</span>
                @if($isFinished && $match->home_goals !== null)
                    <span class="badge" style="background:rgba(61,220,132,0.2);color:var(--xp-ok);font-size:9px;padding:1px 5px;margin-left:4px;">{{ $match->home_goals + $match->away_goals }} Goals</span>
                @endif
            </button>

            <button type="button" class="btn btn-sm mhr-tab-btn" data-tab="tab-motm-{{ $uniqueTabId }}" onclick="switchMhrTab(this, 'tab-motm-{{ $uniqueTabId }}')">
                <span>★ Man of the Match</span>
                @if($motm)
                    <span class="badge" style="background:rgba(217,78,0,0.25);color:var(--xp-orange);font-size:9px;padding:1px 5px;margin-left:4px;">{{ number_format((float)$motm->rating, 1) }}</span>
                @endif
            </button>

            <button type="button" class="btn btn-sm mhr-tab-btn" data-tab="tab-teams-{{ $uniqueTabId }}" onclick="switchMhrTab(this, 'tab-teams-{{ $uniqueTabId }}')">
                <span>📊 Team Ratings</span>
                @if($homeRating && $awayRating)
                    <span class="badge" style="background:#14151C;font-size:9px;padding:1px 5px;margin-left:4px;">{{ $homeRating }} - {{ $awayRating }}</span>
                @endif
            </button>

            <button type="button" class="btn btn-sm mhr-tab-btn" data-tab="tab-players-{{ $uniqueTabId }}" onclick="switchMhrTab(this, 'tab-players-{{ $uniqueTabId }}')">
                <span>👥 Individual Players</span>
                @if($homePlayers->isNotEmpty() || $awayPlayers->isNotEmpty())
                    <span class="badge" style="background:#14151C;font-size:9px;padding:1px 5px;margin-left:4px;">{{ $homePlayers->count() + $awayPlayers->count() }}</span>
                @endif
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: HEAD-TO-HEAD MATCH HISTORY (H2H)   -->
        <!-- ========================================== -->
        <div class="mhr-tab-content active" id="tab-h2h-{{ $uniqueTabId }}">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                <div style="font-size:12px;font-weight:700;color:#FFFFFF;display:flex;align-items:center;gap:6px;">
                    <span>📜 Head-to-Head Clash History</span>
                    <span style="font-size:11px;color:var(--xp-muted);font-weight:400;">
                        ({{ $homeTeam?->short_name }} vs {{ $awayTeam?->short_name }} in DB)
                    </span>
                </div>
                <div style="font-size:10.5px;color:var(--xp-ok);display:flex;align-items:center;gap:4px;">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--xp-ok);"></span>
                    <span>Zero Data Leakage: Strictly matches prior to kickoff</span>
                </div>
            </div>

            <!-- H2H Summary Telemetry Strip -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:8px;margin-bottom:14px;">
                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;text-align:center;">
                    <div style="font-size:10px;color:var(--xp-muted);text-transform:uppercase;">Total Matches</div>
                    <div style="font-size:18px;font-weight:800;color:#FFFFFF;margin-top:2px;">{{ $h2h['total_played'] }}</div>
                    <div style="font-size:10px;color:var(--xp-away);">In Database</div>
                </div>

                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;text-align:center;">
                    <div style="font-size:10px;color:var(--xp-orange);text-transform:uppercase;">{{ $homeTeam?->short_name }} Wins</div>
                    <div style="font-size:18px;font-weight:800;color:var(--xp-orange);margin-top:2px;">{{ $h2h['home_wins'] }}</div>
                    <div style="font-size:10px;color:var(--xp-muted);">{{ $h2h['home_win_pct'] }}% win rate</div>
                </div>

                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;text-align:center;">
                    <div style="font-size:10px;color:var(--xp-muted);text-transform:uppercase;">Draws</div>
                    <div style="font-size:18px;font-weight:800;color:#FFFFFF;margin-top:2px;">{{ $h2h['draws'] }}</div>
                    <div style="font-size:10px;color:var(--xp-muted);">{{ $h2h['draw_pct'] }}% of games</div>
                </div>

                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;text-align:center;">
                    <div style="font-size:10px;color:var(--xp-away);text-transform:uppercase;">{{ $awayTeam?->short_name }} Wins</div>
                    <div style="font-size:18px;font-weight:800;color:var(--xp-away);margin-top:2px;">{{ $h2h['away_wins'] }}</div>
                    <div style="font-size:10px;color:var(--xp-muted);">{{ $h2h['away_win_pct'] }}% win rate</div>
                </div>

                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;text-align:center;">
                    <div style="font-size:10px;color:var(--xp-muted);text-transform:uppercase;">Goals Rate</div>
                    <div style="font-size:18px;font-weight:800;color:var(--xp-ok);margin-top:2px;">{{ $h2h['avg_goals'] }}</div>
                    <div style="font-size:10px;color:var(--xp-muted);">Goals per game</div>
                </div>

                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;text-align:center;">
                    <div style="font-size:10px;color:var(--xp-muted);text-transform:uppercase;">Top Score</div>
                    <div style="font-size:18px;font-weight:800;color:#FFFFFF;margin-top:2px;">{{ $h2h['most_frequent_score'] ?? '—' }}</div>
                    <div style="font-size:10px;color:var(--xp-muted);">BTTS: {{ $h2h['btts_pct'] }}%</div>
                </div>
            </div>

            <!-- List of Past Historical Matches -->
            @if ($h2h['matches']->isNotEmpty())
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach ($h2h['matches'] as $pastMatch)
                        @php
                            $pastDate = $pastMatch->kickoff_at ? \Carbon\CarbonImmutable::parse($pastMatch->kickoff_at)->format('D, d M Y') : '—';
                            $pastMotm = $pastMatch->motmEvaluations->first();
                            $homeWon = $pastMatch->home_goals > $pastMatch->away_goals;
                            $awayWon = $pastMatch->away_goals > $pastMatch->home_goals;
                            $draw = $pastMatch->home_goals === $pastMatch->away_goals;
                        @endphp
                        <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <!-- Date & Competition -->
                            <div style="display:flex;align-items:center;gap:6px;font-size:11px;min-width:140px;">
                                <span style="color:var(--xp-muted);">{{ $pastDate }}</span>
                                <span class="badge" style="background:#14151C;border:1px solid var(--xp-border);color:var(--xp-away);font-size:9.5px;">
                                    {{ $pastMatch->competition?->name ?? 'League' }}
                                </span>
                            </div>

                            <!-- Matchup with Crests & Score -->
                            <div style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;">
                                <div style="display:flex;align-items:center;gap:6px;width:120px;justify-content:flex-end;{{ $homeWon ? 'color:var(--xp-orange);font-weight:700;' : 'color:var(--xp-text);' }}">
                                    <span>{{ $pastMatch->homeTeam?->short_name }}</span>
                                    <x-team-badge :team="$pastMatch->homeTeam" size="18" />
                                </div>

                                <div style="background:#000000;border:1px solid var(--xp-border);padding:2px 8px;border-radius:4px;font-size:13px;font-weight:800;color:#FFFFFF;letter-spacing:1px;">
                                    {{ $pastMatch->home_goals }} &ndash; {{ $pastMatch->away_goals }}
                                </div>

                                <div style="display:flex;align-items:center;gap:6px;width:120px;justify-content:flex-start;{{ $awayWon ? 'color:var(--xp-orange);font-weight:700;' : 'color:var(--xp-text);' }}">
                                    <x-team-badge :team="$pastMatch->awayTeam" size="18" />
                                    <span>{{ $pastMatch->awayTeam?->short_name }}</span>
                                </div>
                            </div>

                            <!-- HT & Winner Status -->
                            <div style="display:flex;align-items:center;gap:8px;font-size:11px;">
                                @if ($pastMatch->ht_home_goals !== null)
                                    <span style="color:var(--xp-muted);font-size:10.5px;">
                                        HT: {{ $pastMatch->ht_home_goals }}-{{ $pastMatch->ht_away_goals }}
                                    </span>
                                @endif

                                @if ($homeWon)
                                    <span class="badge" style="background:rgba(217, 78, 0, 0.15);border:1px solid rgba(217, 78, 0, 0.4);color:var(--xp-orange);font-size:9.5px;">
                                        {{ $pastMatch->homeTeam?->short_name }} Win
                                    </span>
                                @elseif ($awayWon)
                                    <span class="badge" style="background:rgba(88, 86, 214, 0.15);border:1px solid rgba(88, 86, 214, 0.4);color:var(--xp-away);font-size:9.5px;">
                                        {{ $pastMatch->awayTeam?->short_name }} Win
                                    </span>
                                @else
                                    <span class="badge" style="background:rgba(255, 255, 255, 0.08);border:1px solid var(--xp-border);color:var(--xp-text);font-size:9.5px;">
                                        Draw
                                    </span>
                                @endif

                                @if ($pastMotm)
                                    <span style="color:var(--xp-orange);font-size:10.5px;display:flex;align-items:center;gap:3px;" title="Official MOTM Winner">
                                        <span>★</span>
                                        <span>{{ $pastMotm->player_name }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="background:#09090D;border:1px dashed var(--xp-border);border-radius:6px;padding:14px;text-align:center;font-size:11.5px;color:var(--xp-muted);">
                    No prior historical meetings recorded in database before this fixture.
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: GOALS BREAKDOWN                    -->
        <!-- ========================================== -->
        <div class="mhr-tab-content" id="tab-goals-{{ $uniqueTabId }}" style="display:none;">
            <div style="font-size:12px;font-weight:700;color:#FFFFFF;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                <span>⚽ Goals &amp; Scoring Events Breakdown</span>
            </div>

            @if ($isFinished && $match->home_goals !== null)
                <!-- Finished Match Goals Timeline & Scorers -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <!-- Home Scorers -->
                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid var(--xp-border);">
                            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--xp-orange);">
                                <x-team-badge :team="$homeTeam" size="18" />
                                <span>{{ $homeTeam?->name }}</span>
                            </div>
                            <span style="font-size:16px;font-weight:800;color:var(--xp-orange);">{{ $match->home_goals }} Goals</span>
                        </div>

                        @if ($perf['goals']['home_scorers']->isNotEmpty())
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                @foreach ($perf['goals']['home_scorers'] as $sc)
                                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px;background:#000000;border:1px solid var(--xp-border);padding:6px 10px;border-radius:4px;">
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span style="color:var(--xp-orange);">⚽</span>
                                            <span style="font-weight:600;color:#FFFFFF;">{{ $sc->player_name }}</span>
                                            @if($sc->player_number) <span style="font-size:10px;color:var(--xp-muted);">#{{ $sc->player_number }}</span> @endif
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span class="badge" style="background:rgba(217, 78, 0, 0.2);color:var(--xp-orange);font-size:10px;">{{ $sc->goals }} Goal{{ $sc->goals > 1 ? 's' : '' }}</span>
                                            @if($sc->assists > 0) <span class="badge" style="background:rgba(61, 220, 132, 0.2);color:var(--xp-ok);font-size:10px;">{{ $sc->assists }} Ast</span> @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size:11px;color:var(--xp-muted);padding:8px 0;">
                                {{ $match->home_goals > 0 ? 'Team goals recorded from match result.' : 'No goals scored (Clean sheet conceded).' }}
                            </div>
                        @endif
                    </div>

                    <!-- Away Scorers -->
                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid var(--xp-border);">
                            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--xp-away);">
                                <x-team-badge :team="$awayTeam" size="18" />
                                <span>{{ $awayTeam?->name }}</span>
                            </div>
                            <span style="font-size:16px;font-weight:800;color:var(--xp-away);">{{ $match->away_goals }} Goals</span>
                        </div>

                        @if ($perf['goals']['away_scorers']->isNotEmpty())
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                @foreach ($perf['goals']['away_scorers'] as $sc)
                                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px;background:#000000;border:1px solid var(--xp-border);padding:6px 10px;border-radius:4px;">
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span style="color:var(--xp-away);">⚽</span>
                                            <span style="font-weight:600;color:#FFFFFF;">{{ $sc->player_name }}</span>
                                            @if($sc->player_number) <span style="font-size:10px;color:var(--xp-muted);">#{{ $sc->player_number }}</span> @endif
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span class="badge" style="background:rgba(88, 86, 214, 0.2);color:var(--xp-away);font-size:10px;">{{ $sc->goals }} Goal{{ $sc->goals > 1 ? 's' : '' }}</span>
                                            @if($sc->assists > 0) <span class="badge" style="background:rgba(61, 220, 132, 0.2);color:var(--xp-ok);font-size:10px;">{{ $sc->assists }} Ast</span> @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size:11px;color:var(--xp-muted);padding:8px 0;">
                                {{ $match->away_goals > 0 ? 'Team goals recorded from match result.' : 'No goals scored (Clean sheet conceded).' }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Halftime vs Fulltime Goals Breakdown -->
                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;display:flex;justify-content:space-around;text-align:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <div style="font-size:10.5px;color:var(--xp-muted);text-transform:uppercase;">1st Half Goals (HT)</div>
                        <div style="font-size:18px;font-weight:800;color:#FFFFFF;margin-top:2px;">
                            {{ $match->ht_home_goals ?? 0 }} &ndash; {{ $match->ht_away_goals ?? 0 }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;color:var(--xp-muted);text-transform:uppercase;">2nd Half Goals</div>
                        <div style="font-size:18px;font-weight:800;color:var(--xp-ok);margin-top:2px;">
                            {{ max(0, (int)$match->home_goals - (int)($match->ht_home_goals ?? 0)) }} &ndash; {{ max(0, (int)$match->away_goals - (int)($match->ht_away_goals ?? 0)) }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:10.5px;color:var(--xp-muted);text-transform:uppercase;">Total Match Goals</div>
                        <div style="font-size:18px;font-weight:800;color:var(--xp-orange);margin-top:2px;">
                            {{ $match->home_goals + $match->away_goals }}
                        </div>
                    </div>
                </div>
            @else
                <!-- Upcoming / Scheduled Expected Goals & Markets -->
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;">
                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;">
                        <div style="font-size:11px;font-weight:700;color:var(--xp-orange);text-transform:uppercase;margin-bottom:6px;">Expected Goals Rate (&lambda;)</div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;margin-bottom:4px;">
                            <span style="color:var(--xp-text);">{{ $homeTeam?->short_name }}:</span>
                            <strong style="color:var(--xp-orange);font-size:14px;">{{ $prediction->exp_home_goals ?? '—' }} xG</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                            <span style="color:var(--xp-text);">{{ $awayTeam?->short_name }}:</span>
                            <strong style="color:var(--xp-away);font-size:14px;">{{ $prediction->exp_away_goals ?? '—' }} xG</strong>
                        </div>
                    </div>

                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;">
                        <div style="font-size:11px;font-weight:700;color:var(--xp-ok);text-transform:uppercase;margin-bottom:6px;">Predicted Score Ranking</div>
                        @if ($prediction && !empty($prediction->top_scorelines))
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                @foreach (array_slice($prediction->top_scorelines, 0, 3) as $sc)
                                    <span class="badge" style="background:#000000;border:1px solid var(--xp-border);color:#FFFFFF;font-size:11px;padding:3px 7px;">
                                        <strong>{{ $sc['home'] }}-{{ $sc['away'] }}</strong> ({{ round($sc['prob'] * 100, 1) }}%)
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size:11px;color:var(--xp-muted);">Score distribution computes with prediction.</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: MAN OF THE MATCH (MOTM)             -->
        <!-- ========================================== -->
        <div class="mhr-tab-content" id="tab-motm-{{ $uniqueTabId }}" style="display:none;">
            @if ($motm)
                @php
                    $motmTeam = $motm->team_id === $match->home_team_id ? $homeTeam : $awayTeam;
                    $isHomeMotm = $motm->team_id === $match->home_team_id;
                @endphp
                <div style="background:linear-gradient(135deg, rgba(217, 78, 0, 0.12) 0%, rgba(20, 21, 28, 0.95) 100%);border:1px solid var(--xp-orange);border-radius:8px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="position:relative;">
                                <x-team-badge :team="$motmTeam" size="42" />
                                <span style="position:absolute;bottom:-4px;right:-4px;background:var(--xp-orange);color:#000000;font-size:10px;font-weight:900;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    ★
                                </span>
                            </div>
                            <div>
                                <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.08em;color:var(--xp-orange);font-weight:800;">
                                    Official Man of the Match
                                </div>
                                <h3 style="font-family:'Space Grotesk', sans-serif;font-size:18px;font-weight:800;color:#FFFFFF;margin:0;">
                                    {{ $motm->player_name }}
                                    @if ($motm->player_number)
                                        <span style="font-size:13px;color:var(--xp-muted);font-weight:500;">#{{ $motm->player_number }}</span>
                                    @endif
                                </h3>
                                <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">
                                    <strong style="color:{{ $isHomeMotm ? 'var(--xp-orange)' : 'var(--xp-away)' }};">{{ $motmTeam?->name }}</strong>
                                    &bull; Position: <span class="badge" style="background:#14151C;font-size:9.5px;">{{ $motm->position ?? 'M' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rating Badges -->
                        <div style="display:flex;gap:8px;align-items:center;">
                            @if ($motm->data_rating !== null)
                                <div style="text-align:right;background:rgba(0,0,0,0.5);border:1px solid var(--xp-border);border-radius:6px;padding:6px 10px;">
                                    <div style="font-size:9.5px;color:var(--xp-muted);text-transform:uppercase;">Opta Data</div>
                                    <div style="font-size:16px;font-weight:800;color:#FFFFFF;">{{ number_format((float)$motm->data_rating, 1) }}</div>
                                </div>
                            @endif
                            <div style="text-align:right;background:rgba(217, 78, 0, 0.2);border:1px solid var(--xp-orange);border-radius:6px;padding:6px 12px;">
                                <div style="font-size:9.5px;color:var(--xp-orange);text-transform:uppercase;font-weight:700;">Overall Match Rating</div>
                                <div style="font-size:22px;font-weight:900;color:var(--xp-orange);line-height:1;">
                                    {{ number_format((float)$motm->rating, 1) }} ★
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Decisive Actions & Key Contributions -->
                    @if (!empty($motm->decisive_actions))
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
                            @foreach ($motm->decisive_actions as $act)
                                <span class="badge" style="background:rgba(217, 78, 0, 0.2);color:var(--xp-orange);border:1px solid rgba(217, 78, 0, 0.4);font-size:10.5px;padding:3px 8px;">
                                    ⚡ {{ $act }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                        <span class="mini-market-pill" style="background:#000000;">
                            <span>Goals:</span>
                            <strong style="color:var(--xp-orange);">{{ $motm->goals }}</strong>
                        </span>
                        <span class="mini-market-pill" style="background:#000000;">
                            <span>Assists:</span>
                            <strong style="color:var(--xp-ok);">{{ $motm->assists }}</strong>
                        </span>
                        @if ($motm->xg !== null)
                            <span class="mini-market-pill" style="background:#000000;">
                                <span>xG:</span>
                                <strong style="color:#FFFFFF;">{{ number_format((float)$motm->xg, 2) }}</strong>
                            </span>
                        @endif
                        <span class="mini-market-pill" style="background:#000000;">
                            <span>Award Status:</span>
                            <strong style="color:var(--xp-ok);">✓ Strictly 1 of 1</strong>
                        </span>
                    </div>

                    <!-- Decision Summary Narrative -->
                    <div style="background:#08080C;border:1px solid rgba(217, 78, 0, 0.3);border-radius:6px;padding:12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.06em;color:var(--xp-orange);font-weight:700;margin-bottom:4px;">
                            Decision Rationale: Why He Was Player of the Match
                        </div>
                        <p style="font-size:12px;line-height:1.55;color:#FFFFFF;margin:0;">
                            {{ $motm->performance_summary }}
                        </p>
                    </div>
                </div>
            @else
                <div style="background:#09090D;border:1px dashed var(--xp-border);border-radius:6px;padding:16px;text-align:center;">
                    <div style="font-size:12px;color:var(--xp-muted);margin-bottom:10px;">
                        Man of the Match has not been computed for this match yet.
                    </div>
                    <form method="POST" action="{{ route('motm.evaluate', $match->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            ★ Evaluate Man of the Match &amp; Squad Ratings
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: TEAM PERFORMANCE RATINGS            -->
        <!-- ========================================== -->
        <div class="mhr-tab-content" id="tab-teams-{{ $uniqueTabId }}" style="display:none;">
            <div style="font-size:12px;font-weight:700;color:#FFFFFF;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                <span>📊 Both Teams Match Performance &amp; Unit Ratings</span>
            </div>

            @if ($homeRating && $awayRating)
                <!-- Team Match Rating Head-to-Head Comparison -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <!-- Home Team Rating Card -->
                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-top:3px solid var(--xp-orange);border-radius:6px;padding:14px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <x-team-badge :team="$homeTeam" size="24" />
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:#FFFFFF;">{{ $homeTeam?->name }}</div>
                                    <div style="font-size:10px;color:var(--xp-orange);">Home Club Rating</div>
                                </div>
                            </div>
                            <div style="font-size:24px;font-weight:900;color:var(--xp-orange);font-family:'Space Grotesk', sans-serif;">
                                {{ $homeRating }} <span style="font-size:12px;color:var(--xp-muted);font-weight:400;">/ 10</span>
                            </div>
                        </div>

                        <!-- Unit Ratings -->
                        <div style="display:flex;flex-direction:column;gap:6px;font-size:11.5px;">
                            <div style="display:flex;justify-content:space-between;background:#000000;padding:6px 10px;border-radius:4px;border:1px solid var(--xp-border);">
                                <span style="color:var(--xp-muted);">⚔ Attack Unit (Forwards):</span>
                                <strong style="color:#FFFFFF;">{{ $perf['home_units']['attack'] ?? '—' }}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;background:#000000;padding:6px 10px;border-radius:4px;border:1px solid var(--xp-border);">
                                <span style="color:var(--xp-muted);">⚙ Midfield Control:</span>
                                <strong style="color:#FFFFFF;">{{ $perf['home_units']['midfield'] ?? '—' }}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;background:#000000;padding:6px 10px;border-radius:4px;border:1px solid var(--xp-border);">
                                <span style="color:var(--xp-muted);">🛡 Defence &amp; Goalkeeper:</span>
                                <strong style="color:#FFFFFF;">{{ $perf['home_units']['defense'] ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Away Team Rating Card -->
                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-top:3px solid var(--xp-away);border-radius:6px;padding:14px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <x-team-badge :team="$awayTeam" size="24" />
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:#FFFFFF;">{{ $awayTeam?->name }}</div>
                                    <div style="font-size:10px;color:var(--xp-away);">Away Club Rating</div>
                                </div>
                            </div>
                            <div style="font-size:24px;font-weight:900;color:var(--xp-away);font-family:'Space Grotesk', sans-serif;">
                                {{ $awayRating }} <span style="font-size:12px;color:var(--xp-muted);font-weight:400;">/ 10</span>
                            </div>
                        </div>

                        <!-- Unit Ratings -->
                        <div style="display:flex;flex-direction:column;gap:6px;font-size:11.5px;">
                            <div style="display:flex;justify-content:space-between;background:#000000;padding:6px 10px;border-radius:4px;border:1px solid var(--xp-border);">
                                <span style="color:var(--xp-muted);">⚔ Attack Unit (Forwards):</span>
                                <strong style="color:#FFFFFF;">{{ $perf['away_units']['attack'] ?? '—' }}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;background:#000000;padding:6px 10px;border-radius:4px;border:1px solid var(--xp-border);">
                                <span style="color:var(--xp-muted);">⚙ Midfield Control:</span>
                                <strong style="color:#FFFFFF;">{{ $perf['away_units']['midfield'] ?? '—' }}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;background:#000000;padding:6px 10px;border-radius:4px;border:1px solid var(--xp-border);">
                                <span style="color:var(--xp-muted);">🛡 Defence &amp; Goalkeeper:</span>
                                <strong style="color:#FFFFFF;">{{ $perf['away_units']['defense'] ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rating Progress Dominance Bar -->
                @php
                    $totalRating = $homeRating + $awayRating;
                    $homePct = $totalRating > 0 ? round(($homeRating / $totalRating) * 100, 1) : 50;
                    $awayPct = 100 - $homePct;
                @endphp
                <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:10px 14px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:700;margin-bottom:6px;">
                        <span style="color:var(--xp-orange);">{{ $homeTeam?->short_name }} Match Rating Share ({{ $homePct }}%)</span>
                        <span style="color:var(--xp-away);">{{ $awayTeam?->short_name }} ({{ $awayPct }}%)</span>
                    </div>
                    <div style="height:8px;background:#000000;border-radius:4px;overflow:hidden;display:flex;">
                        <div style="width:{{ $homePct }}%;background:var(--xp-orange);transition:width 0.3s ease;"></div>
                        <div style="width:{{ $awayPct }}%;background:var(--xp-away);transition:width 0.3s ease;"></div>
                    </div>
                </div>
            @else
                <div style="background:#09090D;border:1px dashed var(--xp-border);border-radius:6px;padding:16px;text-align:center;">
                    <div style="font-size:12px;color:var(--xp-muted);margin-bottom:10px;">
                        Team ratings have not been calculated yet.
                    </div>
                    <form method="POST" action="{{ route('motm.evaluate', $match->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            + Calculate Both Team Ratings
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: INDIVIDUAL PLAYER RATINGS           -->
        <!-- ========================================== -->
        <div class="mhr-tab-content" id="tab-players-{{ $uniqueTabId }}" style="display:none;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                <div style="font-size:12px;font-weight:700;color:#FFFFFF;display:flex;align-items:center;gap:6px;">
                    <span>👥 Individual Player Ratings (Both Starting XIs)</span>
                </div>
                <div style="font-size:10.5px;color:var(--xp-muted);">
                    Ratings out of 10.0 &bull; Star (★) indicates Man of the Match
                </div>
            </div>

            @if ($homePlayers->isNotEmpty() || $awayPlayers->isNotEmpty())
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <!-- Home Squad Table -->
                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;overflow:hidden;">
                        <div style="padding:10px 12px;background:#000000;border-bottom:1px solid var(--xp-border);display:flex;align-items:center;justify-content:space-between;">
                            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--xp-orange);">
                                <x-team-badge :team="$homeTeam" size="18" />
                                <span>{{ $homeTeam?->name }}</span>
                            </div>
                            @if ($homeRating)
                                <span class="badge" style="background:rgba(217,78,0,0.15);color:var(--xp-orange);font-size:10px;">
                                    Avg: {{ $homeRating }}
                                </span>
                            @endif
                        </div>

                        <div style="max-height:360px;overflow-y:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:11.5px;">
                                <tbody>
                                    @foreach ($homePlayers as $hp)
                                        @php
                                            $rating = (float) $hp->rating;
                                            $ratingColor = $rating >= 8.0 ? 'var(--xp-ok)' : ($rating >= 7.0 ? '#FFFFFF' : ($rating >= 6.0 ? 'var(--xp-muted)' : '#FF453A'));
                                        @endphp
                                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);{{ $hp->is_motm ? 'background:rgba(217,78,0,0.12);' : '' }}">
                                            <td style="padding:6px 10px;">
                                                <div style="display:flex;align-items:center;gap:6px;">
                                                    @if ($hp->is_motm)
                                                        <span style="color:var(--xp-orange);font-weight:800;" title="Man of the Match">★</span>
                                                    @endif
                                                    @if ($hp->player_number)
                                                        <span style="font-size:10px;color:var(--xp-muted);font-family:monospace;min-width:16px;">#{{ $hp->player_number }}</span>
                                                    @endif
                                                    <span style="color:#FFFFFF;font-weight:{{ $hp->is_motm ? '700' : '500' }};">
                                                        {{ $hp->player_name }}
                                                    </span>
                                                    @if ($hp->goals > 0)
                                                        <span style="color:var(--xp-orange);font-size:10px;" title="{{ $hp->goals }} Goals">⚽{{ $hp->goals }}</span>
                                                    @endif
                                                    @if ($hp->assists > 0)
                                                        <span style="color:var(--xp-ok);font-size:10px;" title="{{ $hp->assists }} Assists">🎯{{ $hp->assists }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="padding:6px 8px;width:30px;">
                                                <span class="badge" style="background:#000000;color:var(--xp-muted);font-size:9.5px;padding:1px 4px;">
                                                    {{ $hp->position ?? 'M' }}
                                                </span>
                                            </td>
                                            <td style="padding:6px 10px;text-align:right;width:55px;">
                                                <span class="badge" style="background:#000000;border:1px solid var(--xp-border);color:{{ $ratingColor }};font-weight:700;font-family:monospace;font-size:11px;">
                                                    {{ number_format($rating, 1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Away Squad Table -->
                    <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;overflow:hidden;">
                        <div style="padding:10px 12px;background:#000000;border-bottom:1px solid var(--xp-border);display:flex;align-items:center;justify-content:space-between;">
                            <div style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--xp-away);">
                                <x-team-badge :team="$awayTeam" size="18" />
                                <span>{{ $awayTeam?->name }}</span>
                            </div>
                            @if ($awayRating)
                                <span class="badge" style="background:rgba(88,86,214,0.15);color:var(--xp-away);font-size:10px;">
                                    Avg: {{ $awayRating }}
                                </span>
                            @endif
                        </div>

                        <div style="max-height:360px;overflow-y:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:11.5px;">
                                <tbody>
                                    @foreach ($awayPlayers as $ap)
                                        @php
                                            $rating = (float) $ap->rating;
                                            $ratingColor = $rating >= 8.0 ? 'var(--xp-ok)' : ($rating >= 7.0 ? '#FFFFFF' : ($rating >= 6.0 ? 'var(--xp-muted)' : '#FF453A'));
                                        @endphp
                                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);{{ $ap->is_motm ? 'background:rgba(217,78,0,0.12);' : '' }}">
                                            <td style="padding:6px 10px;">
                                                <div style="display:flex;align-items:center;gap:6px;">
                                                    @if ($ap->is_motm)
                                                        <span style="color:var(--xp-orange);font-weight:800;" title="Man of the Match">★</span>
                                                    @endif
                                                    @if ($ap->player_number)
                                                        <span style="font-size:10px;color:var(--xp-muted);font-family:monospace;min-width:16px;">#{{ $ap->player_number }}</span>
                                                    @endif
                                                    <span style="color:#FFFFFF;font-weight:{{ $ap->is_motm ? '700' : '500' }};">
                                                        {{ $ap->player_name }}
                                                    </span>
                                                    @if ($ap->goals > 0)
                                                        <span style="color:var(--xp-orange);font-size:10px;" title="{{ $ap->goals }} Goals">⚽{{ $ap->goals }}</span>
                                                    @endif
                                                    @if ($ap->assists > 0)
                                                        <span style="color:var(--xp-ok);font-size:10px;" title="{{ $ap->assists }} Assists">🎯{{ $ap->assists }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="padding:6px 8px;width:30px;">
                                                <span class="badge" style="background:#000000;color:var(--xp-muted);font-size:9.5px;padding:1px 4px;">
                                                    {{ $ap->position ?? 'M' }}
                                                </span>
                                            </td>
                                            <td style="padding:6px 10px;text-align:right;width:55px;">
                                                <span class="badge" style="background:#000000;border:1px solid var(--xp-border);color:{{ $ratingColor }};font-weight:700;font-family:monospace;font-size:11px;">
                                                    {{ number_format($rating, 1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div style="background:#09090D;border:1px dashed var(--xp-border);border-radius:6px;padding:16px;text-align:center;">
                    <div style="font-size:12px;color:var(--xp-muted);margin-bottom:10px;">
                        Individual player ratings have not been evaluated for this fixture yet.
                    </div>
                    <form method="POST" action="{{ route('motm.evaluate', $match->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            + Evaluate Starting XIs &amp; Player Ratings
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>
</details>

@once
<script>
function switchMhrTab(btn, targetContentId) {
    var container = btn.closest('.match-history-details');
    if (!container) return;

    // Deactivate all buttons in this container
    var buttons = container.querySelectorAll('.mhr-tab-btn');
    buttons.forEach(function(b) {
        b.classList.remove('active');
        b.style.borderColor = 'var(--xp-border)';
        b.style.background = 'var(--xp-surface)';
        b.style.color = 'var(--xp-muted)';
    });

    // Activate clicked button
    btn.classList.add('active');
    btn.style.borderColor = 'var(--xp-orange)';
    btn.style.background = 'rgba(217, 78, 0, 0.15)';
    btn.style.color = 'var(--xp-orange)';

    // Hide all tab contents in this container
    var contents = container.querySelectorAll('.mhr-tab-content');
    contents.forEach(function(c) {
        c.style.display = 'none';
    });

    // Show selected content
    var target = container.querySelector('#' + targetContentId);
    if (target) {
        target.style.display = 'block';
    }
}
</script>
<style>
.mhr-tab-btn {
    background: var(--xp-surface);
    border: 1px solid var(--xp-border);
    color: var(--xp-muted);
    font-size: 11px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 4px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.mhr-tab-btn:hover {
    color: #FFFFFF;
    border-color: rgba(255, 255, 255, 0.3);
}
.mhr-tab-btn.active {
    background: rgba(217, 78, 0, 0.15) !important;
    border-color: var(--xp-orange) !important;
    color: var(--xp-orange) !important;
}
</style>
@endonce
