@php
    $feat = is_array($prediction->features) ? $prediction->features : (json_decode($prediction->features ?? '[]', true) ?: []);
    $featHome = $feat['home'] ?? [];
    $featAway = $feat['away'] ?? [];
    $preview = $prediction->previews?->first();

    $homeAtt = (float) ($featHome['home_attack_strength'] ?? 1.0);
    $awayDef = (float) ($featAway['away_defence_strength'] ?? 1.0);
    $awayAtt = (float) ($featAway['away_attack_strength'] ?? 1.0);
    $homeDef = (float) ($featHome['home_defence_strength'] ?? 1.0);

    $leagueHomeAvg = (float) ($featHome['league_avg_home_goals'] ?? 1.55);
    $leagueAwayAvg = (float) ($featHome['league_avg_away_goals'] ?? 1.35);

    $homeXgAtt = isset($featHome['home_xg_attack_strength']) ? (float) $featHome['home_xg_attack_strength'] : null;
    $awayXgDef = isset($featAway['away_xg_defence_strength']) ? (float) $featAway['away_xg_defence_strength'] : null;
    $awayXgAtt = isset($featAway['away_xg_attack_strength']) ? (float) $featAway['away_xg_attack_strength'] : null;
    $homeXgDef = isset($featHome['home_xg_defence_strength']) ? (float) $featHome['home_xg_defence_strength'] : null;

    $homeForm = $featHome['form_points_last_5'] ?? null;
    $awayForm = $featAway['form_points_last_5'] ?? null;
    $homeRest = $featHome['rest_days'] ?? null;
    $awayRest = $featAway['rest_days'] ?? null;

    $pH = round((float) $prediction->p_home * 100, 1);
    $pD = round((float) $prediction->p_draw * 100, 1);
    $pA = round((float) $prediction->p_away * 100, 1);
@endphp

<details class="breakdown-details" style="margin-top:10px;background:#09090C;border:1px solid var(--xp-border);border-radius:6px;overflow:hidden;">
    <summary style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-size:12px;font-weight:700;color:var(--xp-orange);cursor:pointer;user-select:none;list-style:none;background:rgba(217, 78, 0, 0.05);transition:background 0.15s ease;">
        <div style="display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>How this prediction was calculated (Model Breakdown)</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--xp-muted);">
            <span>Model {{ $prediction->model_version }}</span>
            <svg class="chevron-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="transition:transform 0.2s ease;">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
    </summary>

    <div style="padding:16px;border-top:1px solid var(--xp-border);display:flex;flex-direction:column;gap:14px;background:#060608;">
        <!-- Header Provenance Tagline -->
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;font-size:11px;color:var(--xp-muted);background:var(--xp-surface);padding:8px 12px;border-radius:4px;border:1px solid var(--xp-border);">
            <div>
                <strong>Statistical Pipeline:</strong> Independent Bivariate Poisson + Dixon-Coles Low-Score Correction + H2H Calibration
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--xp-ok);"></span>
                <span style="color:var(--xp-text);">Zero Data Leakage:</span>
                <span>Cutoff {{ $prediction->as_of ? $prediction->as_of->format('Y-m-d H:i') : 'Kickoff' }} UTC</span>
            </div>
        </div>

        <!-- 3-Column Calculations Grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:12px;">
            <!-- Column 1: Expected Goals Calculation -->
            <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;">
                <div style="font-size:11px;font-weight:700;color:var(--xp-orange);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    <span>1. Expected Goals (&lambda;)</span>
                </div>
                <div style="font-size:11px;color:var(--xp-muted);line-height:1.45;margin-bottom:8px;">
                    Derived from attack strength, opponent defence weakness, and league home advantage:
                </div>
                
                <!-- Home Lambda -->
                <div style="background:var(--xp-black);border:1px solid var(--xp-border);padding:8px;border-radius:4px;margin-bottom:6px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
                        <span style="color:var(--xp-text);font-weight:600;">Home Exp Goals (&lambda;<sub>H</sub>):</span>
                        <strong style="color:var(--xp-orange);font-size:13px;">{{ $prediction->exp_home_goals }}</strong>
                    </div>
                    <div style="font-size:10px;color:var(--xp-away);font-family:monospace;">
                        {{ number_format($homeAtt, 2) }} (Att) &times; {{ number_format($awayDef, 2) }} (Def) &times; {{ number_format($leagueHomeAvg, 2) }}
                    </div>
                    @if ($homeXgAtt !== null)
                        <div style="font-size:10px;color:var(--xp-muted);margin-top:2px;">
                            xG blended: {{ number_format($homeXgAtt, 2) }} (xG) / {{ number_format($awayXgDef, 2) }} (xGA)
                        </div>
                    @endif
                </div>

                <!-- Away Lambda -->
                <div style="background:var(--xp-black);border:1px solid var(--xp-border);padding:8px;border-radius:4px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
                        <span style="color:var(--xp-text);font-weight:600;">Away Exp Goals (&lambda;<sub>A</sub>):</span>
                        <strong style="color:var(--xp-away);font-size:13px;">{{ $prediction->exp_away_goals }}</strong>
                    </div>
                    <div style="font-size:10px;color:var(--xp-away);font-family:monospace;">
                        {{ number_format($awayAtt, 2) }} (Att) &times; {{ number_format($homeDef, 2) }} (Def) &times; {{ number_format($leagueAwayAvg, 2) }}
                    </div>
                    @if ($awayXgAtt !== null)
                        <div style="font-size:10px;color:var(--xp-muted);margin-top:2px;">
                            xG blended: {{ number_format($awayXgAtt, 2) }} (xG) / {{ number_format($homeXgDef, 2) }} (xGA)
                        </div>
                    @endif
                </div>
            </div>

            <!-- Column 2: Form, Rest & Contextual Adjustments -->
            <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;">
                <div style="font-size:11px;font-weight:700;color:var(--xp-orange);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    <span>2. Form &amp; Fatigue</span>
                </div>
                <div style="font-size:11px;color:var(--xp-muted);line-height:1.45;margin-bottom:8px;">
                    Opponent-adjusted form and physical rest days prior to this fixture:
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <div style="background:var(--xp-black);border:1px solid var(--xp-border);padding:8px;border-radius:4px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;">
                            <span style="color:var(--xp-muted);">Recent Form (Last 5):</span>
                            <span style="font-weight:600;color:var(--xp-text);">
                                <span style="color:var(--xp-orange);">{{ $homeForm !== null ? $homeForm . ' pts' : '—' }}</span>
                                <span style="color:var(--xp-away);">&bull;</span>
                                <span style="color:var(--xp-away);">{{ $awayForm !== null ? $awayForm . ' pts' : '—' }}</span>
                            </span>
                        </div>
                    </div>

                    <div style="background:var(--xp-black);border:1px solid var(--xp-border);padding:8px;border-radius:4px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;">
                            <span style="color:var(--xp-muted);">Rest Days / Schedule:</span>
                            <span style="font-weight:600;color:var(--xp-text);">
                                <span style="color:var(--xp-orange);">{{ $homeRest !== null ? $homeRest . 'd rest' : '—' }}</span>
                                <span style="color:var(--xp-away);">&bull;</span>
                                <span style="color:var(--xp-away);">{{ $awayRest !== null ? $awayRest . 'd rest' : '—' }}</span>
                            </span>
                        </div>
                    </div>

                    <div style="background:var(--xp-black);border:1px solid var(--xp-border);padding:8px;border-radius:4px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;">
                            <span style="color:var(--xp-muted);">Sample Window:</span>
                            <span style="font-weight:600;color:var(--xp-text);">20 matches (180d half-life)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 3: Poisson Grid & Dixon-Coles Correction -->
            <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-radius:6px;padding:12px;">
                <div style="font-size:11px;font-weight:700;color:var(--xp-orange);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    <span>3. Dixon-Coles Correction</span>
                </div>
                <div style="font-size:11px;color:var(--xp-muted);line-height:1.45;margin-bottom:8px;">
                    Low-score correlation factor (&rho; = -0.06) applied across score matrix (0-0 to 7-7):
                </div>

                <div style="background:var(--xp-black);border:1px solid var(--xp-border);padding:8px;border-radius:4px;margin-bottom:6px;">
                    <div style="font-size:11px;color:var(--xp-text);margin-bottom:4px;">
                        <strong>Scoreline Probability Ranking:</strong>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @if (!empty($prediction->top_scorelines))
                            @foreach (array_slice($prediction->top_scorelines, 0, 5) as $ts)
                                <span style="font-size:10px;background:var(--xp-surface);border:1px solid var(--xp-border);padding:2px 6px;border-radius:3px;color:var(--xp-text);">
                                    <strong>{{ $ts['home'] }}-{{ $ts['away'] }}</strong> ({{ round($ts['prob'] * 100, 1) }}%)
                                </span>
                            @endforeach
                        @else
                            <span style="font-size:11px;color:var(--xp-muted);">Standard Poisson distribution</span>
                        @endif
                    </div>
                </div>

                <div style="background:var(--xp-black);border:1px solid var(--xp-border);padding:8px;border-radius:4px;font-size:11px;color:var(--xp-muted);">
                    Draw probability boosted by Dixon-Coles adjustment to reflect real low-score clustering.
                </div>
            </div>
        </div>

        <!-- AI Preview Section -->
        <div style="background:var(--xp-surface);border:1px solid var(--xp-border);border-left:3px solid var(--xp-orange);border-radius:0 6px 6px 0;padding:12px 14px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div style="font-size:11px;font-weight:700;color:var(--xp-orange);text-transform:uppercase;letter-spacing:0.04em;display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <span>Grounded AI Match Preview</span>
                </div>
                @if ($preview)
                    <span class="badge {{ $preview->flagged ? 'badge-danger' : 'badge-success' }}" style="font-size:9px;">
                        {{ $preview->flagged ? 'Guard Flagged' : 'Numbers Verified' }} ({{ $preview->model }})
                    </span>
                @endif
            </div>

            @if ($preview)
                <p style="font-size:12px;line-height:1.6;color:var(--xp-text);margin:0;">
                    {{ $preview->text }}
                </p>
            @else
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                    <p style="font-size:11px;color:var(--xp-muted);margin:0;line-height:1.4;">
                        No AI preview generated yet. Previews strictly synthesize the computed Poisson probabilities into natural language with zero outside facts.
                    </p>
                    <form method="POST" action="{{ route('predictions.preview', $prediction->id) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            Generate AI Preview
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Link to Full Model Changelog & Documentation -->
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:rgba(217,78,0,0.06);border:1px solid rgba(217,78,0,0.2);border-radius:6px;font-size:11.5px;flex-wrap:wrap;gap:8px;">
            <span style="color:var(--xp-muted);">Want to see full model parameters, Dixon-Coles &rho; matrix, and version history?</span>
            <a href="{{ route('changelog') }}" style="color:var(--xp-orange);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <span>View Model Changelog &amp; Technical Docs</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</details>

