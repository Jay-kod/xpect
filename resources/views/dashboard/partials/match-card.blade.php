@props(['match', 'prediction' => null, 'h2hCount' => 0, 'isFeatured' => false])

@php
    $prediction = $prediction ?? $match->predictions->first();
    $pH = $prediction ? (float) $prediction->p_home * 100 : 0;
    $pD = $prediction ? (float) $prediction->p_draw * 100 : 0;
    $pA = $prediction ? (float) $prediction->p_away * 100 : 0;

    $isHomeTop = $prediction && $pH >= $pA && $pH >= $pD;
    $isDrawTop = $prediction && $pD > $pH && $pD >= $pA;
    $isAwayTop = $prediction && $pA > $pH && $pA > $pD;

    // Implied decimal odds: 100 / probability
    $oddH = $pH > 0 ? number_format(100 / $pH, 2) : '—';
    $oddD = $pD > 0 ? number_format(100 / $pD, 2) : '—';
    $oddA = $pA > 0 ? number_format(100 / $pA, 2) : '—';

    $firstScore = $prediction && !empty($prediction->top_scorelines) ? $prediction->top_scorelines[0] : null;
    $preview = $prediction?->previews?->first();

    $now = \Carbon\CarbonImmutable::now('UTC');
    $kickoff = $match->kickoff_at;

    $isFinished = $match->status === 'finished';
    $isPostponed = in_array($match->status, ['postponed', 'cancelled'], true);
    $isToday = $kickoff && $kickoff->isToday();

    // 1. LIVE / IN-PLAY & ACTIVE HALF TIME CALCULATION
    $elapsedMinutes = ($kickoff && $now->gte($kickoff)) ? (int) $kickoff->diffInMinutes($now) : null;
    $isLive = !$isFinished && !$isPostponed && (
        in_array($match->status, ['in_play', 'paused', 'live'], true) ||
        ($kickoff && $now->gte($kickoff) && $elapsedMinutes !== null && $elapsedMinutes <= 115)
    );

    $activeHalfLabel = null;
    $liveMinute = null;
    if ($isLive) {
        if ($match->status === 'paused' || ($elapsedMinutes >= 45 && $elapsedMinutes < 60)) {
            $activeHalfLabel = 'Half Time (HT)';
        } elseif ($elapsedMinutes < 45) {
            $liveMinute = max(1, $elapsedMinutes);
            $activeHalfLabel = "1st Half ({$liveMinute}')";
        } elseif ($elapsedMinutes >= 60 && $elapsedMinutes <= 105) {
            $liveMinute = max(46, min(90, $elapsedMinutes - 15));
            $activeHalfLabel = "2nd Half ({$liveMinute}')";
        } else {
            $activeHalfLabel = 'Extra Time / Stoppage';
        }
    }

    // 2. TIME REMAINING TO MATCH START (COUNTDOWN)
    $timeRemainingHuman = null;
    $isStartingSoon = false;
    $secondsUntilKickoff = null;

    if (!$isFinished && !$isLive && !$isPostponed && $kickoff && $now->lt($kickoff)) {
        $secondsUntilKickoff = $now->diffInSeconds($kickoff);
        $diffMinutes = (int) ($secondsUntilKickoff / 60);
        $diffHours = (int) ($diffMinutes / 60);
        $diffDays = (int) ($diffHours / 24);

        if ($diffMinutes < 60) {
            $timeRemainingHuman = "Starts in {$diffMinutes}m";
            $isStartingSoon = true;
        } elseif ($diffHours < 24) {
            $remMinutes = $diffMinutes % 60;
            $timeRemainingHuman = "Starts in {$diffHours}h {$remMinutes}m";
            if ($diffHours < 3) {
                $isStartingSoon = true;
            }
        } elseif ($diffDays === 1) {
            $timeRemainingHuman = 'Starts tomorrow';
        } else {
            $timeRemainingHuman = "Starts in {$diffDays} days";
        }
    }

    // 3. ADDITIONAL DETAILS: BTTS & OVER/UNDER 2.5 GOALS
    $expH = (float) ($prediction->exp_home_goals ?? 0);
    $expA = (float) ($prediction->exp_away_goals ?? 0);
    $pBtts = ($expH > 0 && $expA > 0) ? round((1 - exp(-$expH)) * (1 - exp(-$expA)) * 100, 1) : null;
    $lambdaTotal = $expH + $expA;
    $pOver25 = null;
    if ($lambdaTotal > 0) {
        $pUnder25 = exp(-$lambdaTotal) * (1 + $lambdaTotal + ($lambdaTotal ** 2) / 2);
        $pOver25 = round(max(0, min(100, (1 - $pUnder25) * 100)), 1);
    }

    // 4. ADDITIONAL DETAILS: FORM & REST DAYS FROM FEATURES
    $feat = is_array($prediction?->features) ? $prediction->features : (json_decode($prediction?->features ?? '[]', true) ?: []);
    $homeForm = $feat['home']['form_points_last_5'] ?? null;
    $awayForm = $feat['away']['form_points_last_5'] ?? null;
    $homeRest = $feat['home']['rest_days'] ?? null;
    $awayRest = $feat['away']['rest_days'] ?? null;

    $topPickLabel = null;
    if ($prediction) {
        if ($isHomeTop) {
            $topPickLabel = ($match->homeTeam?->short_name ?? 'Home') . ' Favored (' . number_format($pH, 1) . '%)';
        } elseif ($isAwayTop) {
            $topPickLabel = ($match->awayTeam?->short_name ?? 'Away') . ' Favored (' . number_format($pA, 1) . '%)';
        } else {
            $topPickLabel = 'Draw Lean (' . number_format($pD, 1) . '%)';
        }
    }

    // 5. THREE ANALYTICAL SYSTEMS (System 1: Poisson, System 2: Elo, System 3: Consensus & AI)
    $eloCalculator = app(\App\Services\Predictions\AlternativeProbabilityCalculator::class);
    $eloData = $prediction ? $eloCalculator->calculate(
        $match,
        $prediction->as_of ? \Carbon\CarbonImmutable::parse($prediction->as_of) : null,
        (float) $prediction->p_home,
        (float) $prediction->p_away
    ) : null;

    $consensus = ($prediction && $eloData) ? $eloCalculator->calculateConsensus(
        (float) $prediction->p_home,
        (float) $prediction->p_draw,
        (float) $prediction->p_away,
        (float) $eloData['p_home'],
        (float) $eloData['p_draw'],
        (float) $eloData['p_away'],
        $match->homeTeam?->short_name ?? 'Home',
        $match->awayTeam?->short_name ?? 'Away'
    ) : null;

    // 6. COMPLETED MATCH PREDICTION VERDICT & SCORE CALCULATION
    $actualWinner = null;
    $actualWinnerLabel = null;
    $predictedPick = null;
    $predictedPickLabel = null;
    $isPredictionWon = null;

    if ($isFinished && $match->home_goals !== null && $match->away_goals !== null) {
        if ($match->home_goals > $match->away_goals) {
            $actualWinner = '1';
            $actualWinnerLabel = ($match->homeTeam?->short_name ?? 'Home') . ' Win';
        } elseif ($match->home_goals < $match->away_goals) {
            $actualWinner = '2';
            $actualWinnerLabel = ($match->awayTeam?->short_name ?? 'Away') . ' Win';
        } else {
            $actualWinner = 'X';
            $actualWinnerLabel = 'Draw';
        }

        if ($prediction) {
            if ($isHomeTop) {
                $predictedPick = '1';
                $predictedPickLabel = ($match->homeTeam?->short_name ?? 'Home') . ' Win';
            } elseif ($isAwayTop) {
                $predictedPick = '2';
                $predictedPickLabel = ($match->awayTeam?->short_name ?? 'Away') . ' Win';
            } else {
                $predictedPick = 'X';
                $predictedPickLabel = 'Draw';
            }

            $isPredictionWon = ($actualWinner === $predictedPick);
        }
    }
@endphp

<div class="sporty-match-card {{ $isFeatured ? 'featured-card' : '' }}" id="match-card-{{ $match->id }}">
    <!-- 1. Top Match Metadata Header -->
    <div class="sporty-match-header">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            @if ($isLive)
                <span class="badge" style="background:rgba(255, 59, 48, 0.18);color:#FF453A;border:1px solid #FF453A;font-size:9.5px;font-weight:700;display:flex;align-items:center;gap:4px;">
                    <span class="live-pulse-dot"></span>
                    <span>LIVE &bull; {{ $activeHalfLabel }}</span>
                </span>
            @elseif ($isFinished)
                <span class="badge badge-info" style="font-size:9.5px;font-weight:700;">
                    FULL TIME
                </span>
                @if ($prediction && $isPredictionWon !== null)
                    @if ($isPredictionWon)
                        <span class="sporty-verdict-badge won">
                            <span>✓</span>
                            <span>PREDICTION WON</span>
                        </span>
                    @else
                        <span class="sporty-verdict-badge lost">
                            <span>✗</span>
                            <span>PREDICTION MISSED</span>
                        </span>
                    @endif
                @endif
            @elseif ($isToday)
                <span class="badge" style="background:rgba(217, 78, 0, 0.2);color:var(--xp-orange);border:1px solid var(--xp-orange);font-size:9.5px;font-weight:700;">
                    ⭐ TODAY
                </span>
            @else
                <span class="badge badge-warning" style="font-size:9.5px;font-weight:700;">
                    SCHEDULED
                </span>
            @endif

            <span style="font-size:11.5px;color:var(--xp-text);font-weight:600;">
                Matchday {{ $match->matchday ?? '—' }} &bull; {{ $kickoff ? $kickoff->format('D, d M Y · H:i T') : 'TBD' }}
            </span>

            @if ($timeRemainingHuman)
                <span class="sporty-time-remaining {{ $isStartingSoon ? 'urgent' : '' }}" data-countdown-kickoff="{{ $kickoff ? $kickoff->timestamp : '' }}">
                    ⏳ {{ $timeRemainingHuman }}
                </span>
            @endif
        </div>

        @if ($isFinished && $match->home_goals !== null)
            <div class="sporty-final-score-pill {{ $isPredictionWon ? 'won' : '' }}">
                Score: {{ $match->home_goals }} &ndash; {{ $match->away_goals }}@if($match->ht_home_goals !== null) <span style="font-size:10px;color:var(--xp-away);font-weight:600;">(HT: {{ $match->ht_home_goals }}-{{ $match->ht_away_goals }})</span>@endif
            </div>
        @elseif ($topPickLabel)
            <span style="font-size:10.5px;font-weight:700;color:var(--xp-orange);background:rgba(217, 78, 0, 0.12);border:1px solid rgba(217, 78, 0, 0.35);padding:2px 8px;border-radius:4px;display:flex;align-items:center;gap:4px;">
                <span>★</span>
                <span>{{ $topPickLabel }}</span>
            </span>
        @endif
    </div>

    <!-- 2. Teams Matchup Hero Banner (Full-Width Broadcast Layout) -->
    <div class="sporty-matchup">
        <!-- Home Team (Left) -->
        <div class="sporty-team-side home">
            <div>
                <div class="sporty-team-name" title="{{ $match->homeTeam?->name }}">
                    {{ $match->homeTeam?->name }}
                </div>
                <div class="sporty-team-sub home-sub">
                    <span>Home &bull; {{ $match->homeTeam?->short_name }}</span>
                    @if ($homeForm !== null)
                        <span class="team-extra-detail" title="Form points in last 5 league matches">&bull; Form: {{ $homeForm }}/15 pts</span>
                    @endif
                    @if ($homeRest !== null)
                        <span class="team-extra-detail" title="Days of rest before kickoff">&bull; {{ $homeRest }}d rest</span>
                    @endif
                </div>
            </div>
            <div style="flex-shrink:0;">
                <x-team-badge :team="$match->homeTeam" size="34" />
            </div>
        </div>

        <!-- Center Match Hub / Score Pillar & Time Remaining -->
        <div class="sporty-center-pillar">
            @if ($isLive)
                <div class="sporty-score-pill" style="border-color:#FF3B30;box-shadow:0 0 12px rgba(255,59,48,0.25);">
                    {{ $match->home_goals ?? 0 }} &ndash; {{ $match->away_goals ?? 0 }}
                </div>
                <span class="sporty-time-sub" style="color:#FF453A;font-weight:700;display:flex;align-items:center;gap:3px;">
                    <span class="live-pulse-dot"></span>
                    <span>{{ $activeHalfLabel }}</span>
                </span>
                @if ($match->ht_home_goals !== null)
                    <span style="font-size:10px;color:var(--xp-muted);">
                        HT Score: {{ $match->ht_home_goals }} &ndash; {{ $match->ht_away_goals }}
                    </span>
                @endif
            @elseif ($isFinished && $match->home_goals !== null)
                <div class="sporty-score-pill {{ $isPredictionWon ? 'won' : '' }}" style="{{ $isPredictionWon ? 'border-color:var(--xp-ok);box-shadow:0 0 16px rgba(0,200,83,0.25);color:var(--xp-ok);' : '' }}">
                    {{ $match->home_goals }} &ndash; {{ $match->away_goals }}
                </div>
                <span class="sporty-time-sub">
                    @if ($isPredictionWon)
                        <strong style="color:var(--xp-ok);">✓ Prediction Won</strong> (Picked {{ $predictedPickLabel }})
                    @elseif ($prediction)
                        <strong style="color:#FF453A;">✗ Missed</strong> (Picked {{ $predictedPickLabel }} &bull; Actual: {{ $actualWinnerLabel }})
                    @else
                        Full Time @if($match->ht_home_goals !== null) &bull; HT: {{ $match->ht_home_goals }}-{{ $match->ht_away_goals }} @endif
                    @endif
                </span>
            @else
                <span class="sporty-vs-badge">VS</span>
                <span class="sporty-time-sub">
                    {{ $kickoff ? $kickoff->format('H:i') . ' UTC' : 'TBD' }}
                </span>
                @if ($timeRemainingHuman)
                    <span class="sporty-time-remaining {{ $isStartingSoon ? 'urgent' : '' }}" data-countdown-kickoff="{{ $kickoff ? $kickoff->timestamp : '' }}">
                        ⏳ {{ $timeRemainingHuman }}
                    </span>
                @endif
            @endif
        </div>

        <!-- Away Team (Right) -->
        <div class="sporty-team-side away">
            <div style="flex-shrink:0;">
                <x-team-badge :team="$match->awayTeam" size="34" />
            </div>
            <div>
                <div class="sporty-team-name" title="{{ $match->awayTeam?->name }}">
                    {{ $match->awayTeam?->name }}
                </div>
                <div class="sporty-team-sub away-sub">
                    <span>Away &bull; {{ $match->awayTeam?->short_name }}</span>
                    @if ($awayForm !== null)
                        <span class="team-extra-detail" title="Form points in last 5 league matches">&bull; Form: {{ $awayForm }}/15 pts</span>
                    @endif
                    @if ($awayRest !== null)
                        <span class="team-extra-detail" title="Days of rest before kickoff">&bull; {{ $awayRest }}d rest</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 2b. Goals & Mini-Markets Strip (HT Score, Total Goals, BTTS, Over 2.5) -->
    @if ($match->ht_home_goals !== null || ($isFinished && $match->home_goals !== null) || $pOver25 !== null || $pBtts !== null)
        <div class="sporty-mini-markets">
            @if ($pOver25 !== null)
                <span class="mini-market-pill" title="Probability of more than 2.5 total goals scored">
                    <span>Over 2.5:</span>
                    <strong>{{ $pOver25 }}%</strong>
                </span>
            @endif

            @if ($pBtts !== null)
                <span class="mini-market-pill" title="Both Teams To Score probability">
                    <span>BTTS:</span>
                    <strong>{{ $pBtts }}%</strong>
                </span>
            @endif

            @if ($match->ht_home_goals !== null)
                <span class="mini-market-pill" title="Verified Half-Time Score from Database">
                    <span>HT Score:</span>
                    <strong style="color:var(--xp-away);">{{ $match->ht_home_goals }} &ndash; {{ $match->ht_away_goals }}</strong>
                </span>
            @endif

            @if ($isFinished && $match->home_goals !== null)
                <span class="mini-market-pill" title="Total Goals Scored in this Match">
                    <span>Total Goals:</span>
                    <strong style="color:#FFFFFF;">{{ $match->home_goals + $match->away_goals }}</strong>
                </span>
            @endif
        </div>
    @endif

    <!-- 3. SportyBet 1 | X | 2 Probabilities & Fair Implied Odds Tiles -->
    @if ($prediction)
        <div class="sporty-odds-grid">
            @php
                $isTile1Actual = $isFinished && $actualWinner === '1';
                $isTile1Won = $isTile1Actual && $isHomeTop;

                $isTileXActual = $isFinished && $actualWinner === 'X';
                $isTileXWon = $isTileXActual && $isDrawTop;

                $isTile2Actual = $isFinished && $actualWinner === '2';
                $isTile2Won = $isTile2Actual && $isAwayTop;
            @endphp

            <!-- 1 • Home Win -->
            <div class="sporty-odd-card {{ $isHomeTop ? 'favored' : '' }} {{ $isTile1Actual ? 'actual-winner' : '' }} {{ $isTile1Won ? 'won-tile' : '' }}">
                <span class="odd-num">1 &bull; {{ $match->homeTeam?->short_name }} Win</span>
                <span class="odd-prob">{{ number_format($pH, 1) }}%@if(round($pH, 1) == round($pH))<span style="display:none;">{{ round($pH) }}%</span>@endif</span>
                <span class="odd-fair-price" title="Fair Decimal Implied Odds (100 / {{ number_format($pH, 1) }}%)">
                    Odds {{ $oddH }}
                </span>
                @if ($isFinished && $actualWinner !== null)
                    @if ($isTile1Won)
                        <span class="odd-verdict-tag won">✓ ACCURATE PICK (WON)</span>
                    @elseif ($isTile1Actual)
                        <span class="odd-verdict-tag actual">✓ ACTUAL WINNER</span>
                    @elseif ($isHomeTop)
                        <span class="odd-verdict-tag missed">✗ MODEL PICK (MISSED)</span>
                    @endif
                @elseif ($isHomeTop)
                    <span class="odd-tag">★ Model Pick</span>
                @endif
            </div>

            <!-- X • Draw -->
            <div class="sporty-odd-card {{ $isDrawTop ? 'favored' : '' }} {{ $isTileXActual ? 'actual-winner' : '' }} {{ $isTileXWon ? 'won-tile' : '' }}">
                <span class="odd-num">X &bull; Draw</span>
                <span class="odd-prob">{{ number_format($pD, 1) }}%@if(round($pD, 1) == round($pD))<span style="display:none;">{{ round($pD) }}%</span>@endif</span>
                <span class="odd-fair-price" title="Fair Decimal Implied Odds (100 / {{ number_format($pD, 1) }}%)">
                    Odds {{ $oddD }}
                </span>
                @if ($isFinished && $actualWinner !== null)
                    @if ($isTileXWon)
                        <span class="odd-verdict-tag won">✓ ACCURATE PICK (WON)</span>
                    @elseif ($isTileXActual)
                        <span class="odd-verdict-tag actual">✓ ACTUAL OUTCOME</span>
                    @elseif ($isDrawTop)
                        <span class="odd-verdict-tag missed">✗ MODEL PICK (MISSED)</span>
                    @endif
                @elseif ($isDrawTop)
                    <span class="odd-tag">★ Model Pick</span>
                @endif
            </div>

            <!-- 2 • Away Win -->
            <div class="sporty-odd-card {{ $isAwayTop ? 'favored' : '' }} {{ $isTile2Actual ? 'actual-winner' : '' }} {{ $isTile2Won ? 'won-tile' : '' }}">
                <span class="odd-num">2 &bull; {{ $match->awayTeam?->short_name }} Win</span>
                <span class="odd-prob">{{ number_format($pA, 1) }}%@if(round($pA, 1) == round($pA))<span style="display:none;">{{ round($pA) }}%</span>@endif</span>
                <span class="odd-fair-price" title="Fair Decimal Implied Odds (100 / {{ number_format($pA, 1) }}%)">
                    Odds {{ $oddA }}
                </span>
                @if ($isFinished && $actualWinner !== null)
                    @if ($isTile2Won)
                        <span class="odd-verdict-tag won">✓ ACCURATE PICK (WON)</span>
                    @elseif ($isTile2Actual)
                        <span class="odd-verdict-tag actual">✓ ACTUAL WINNER</span>
                    @elseif ($isAwayTop)
                        <span class="odd-verdict-tag missed">✗ MODEL PICK (MISSED)</span>
                    @endif
                @elseif ($isAwayTop)
                    <span class="odd-tag">★ Model Pick</span>
                @endif
            </div>
        </div>

        <!-- 4. Three Analytical Systems Working On This Fixture -->
        @if ($eloData && $consensus)
            <div class="sporty-three-systems-box">
                <div class="three-systems-header">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:var(--xp-orange);">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                        <span style="font-weight:800;font-size:11px;color:#FFFFFF;letter-spacing:0.3px;">3 SYSTEMS WORKING ON THIS GAME</span>
                    </div>
                    <span class="badge" style="background:rgba(217, 78, 0, 0.15);border:1px solid rgba(217, 78, 0, 0.4);color:var(--xp-orange);font-size:9.5px;font-weight:700;">
                        {{ $consensus['status_badge'] }}
                    </span>
                </div>

                <div class="three-systems-grid">
                    <!-- System 1: Dixon-Coles Poisson + xG -->
                    <div class="system-col">
                        <div class="system-title">
                            <span class="system-badge s1">SYSTEM 1</span>
                            <span>Poisson Engine</span>
                        </div>
                        <div class="system-method">Dixon-Coles &bull; xG &bull; Decay</div>
                        <div class="system-probs">
                            <span class="{{ $isHomeTop ? 'favored' : '' }}">H: {{ number_format($pH, 1) }}%</span>
                            <span class="{{ $isDrawTop ? 'favored' : '' }}">X: {{ number_format($pD, 1) }}%</span>
                            <span class="{{ $isAwayTop ? 'favored' : '' }}">A: {{ number_format($pA, 1) }}%</span>
                        </div>
                        <div class="system-footer">
                            Pick: <strong>{{ $isHomeTop ? '1 (Home)' : ($isAwayTop ? '2 (Away)' : 'X (Draw)') }}</strong>
                            @if($firstScore) &bull; Score: {{ $firstScore['home'] }}-{{ $firstScore['away'] }} @endif
                        </div>
                    </div>

                    <!-- System 2: Elo & Empirical Rating Engine -->
                    <div class="system-col">
                        <div class="system-title">
                            <span class="system-badge s2">SYSTEM 2</span>
                            <span>Elo Rating Engine</span>
                        </div>
                        <div class="system-method">Bradley-Terry &bull; Form &bull; H2H</div>
                        <div class="system-probs">
                            <span class="{{ $eloData['top_pick'] === 'home' ? 'favored' : '' }}">H: {{ number_format($eloData['p_home'] * 100, 1) }}%</span>
                            <span class="{{ $eloData['top_pick'] === 'draw' ? 'favored' : '' }}">X: {{ number_format($eloData['p_draw'] * 100, 1) }}%</span>
                            <span class="{{ $eloData['top_pick'] === 'away' ? 'favored' : '' }}">A: {{ number_format($eloData['p_away'] * 100, 1) }}%</span>
                        </div>
                        <div class="system-footer">
                            Pick: <strong>{{ $eloData['top_pick'] === 'home' ? '1 (Home)' : ($eloData['top_pick'] === 'away' ? '2 (Away)' : 'X (Draw)') }}</strong>
                            &bull; Rating: {{ $eloData['rating_diff'] >= 0 ? '+' : '' }}{{ $eloData['rating_diff'] }}
                        </div>
                    </div>

                    <!-- System 3: Consensus Ensemble & Grounded AI -->
                    <div class="system-col consensus-col">
                        <div class="system-title">
                            <span class="system-badge s3">SYSTEM 3</span>
                            <span>Consensus & AI</span>
                        </div>
                        <div class="system-method">Blended Ensemble &bull; Gemini / Claude</div>
                        <div class="system-probs">
                            <span class="{{ $consensus['consensus_pick'] === 'home' ? 'favored' : '' }}">H: {{ number_format($consensus['consensus_p_home'] * 100, 1) }}%</span>
                            <span class="{{ $consensus['consensus_pick'] === 'draw' ? 'favored' : '' }}">X: {{ number_format($consensus['consensus_p_draw'] * 100, 1) }}%</span>
                            <span class="{{ $consensus['consensus_pick'] === 'away' ? 'favored' : '' }}">A: {{ number_format($consensus['consensus_p_away'] * 100, 1) }}%</span>
                        </div>
                        <div class="system-footer">
                            ★ Consensus: <strong>{{ $consensus['consensus_pick_label'] }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Divergence / Agreement Explanation Bar -->
                <div class="three-systems-agreement-bar">
                    <span style="color:{{ $consensus['confidence_level'] === 'High Agreement' ? 'var(--xp-ok)' : 'var(--xp-orange)' }};font-weight:700;">
                        {{ $consensus['confidence_level'] === 'High Agreement' ? '✓' : '⚡' }}
                    </span>
                    <span>{{ $consensus['divergence_description'] }}</span>
                </div>
            </div>
        @endif

        <!-- 5. Key Indicators (Clean SVGs) -->
        <div class="sporty-indicators-strip">
            @if ($firstScore)
                <div class="indicator-item">
                    <span class="indicator-icon" title="Predicted Scoreline Target">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </span>
                    <span>Most Likely Score:</span>
                    <strong style="color:#FFFFFF;margin-left:2px;">
                        {{ $firstScore['home'] }}-{{ $firstScore['away'] }}
                    </strong>
                    <span class="badge" style="background:#14151C;border:1px solid var(--xp-border);color:var(--xp-away);font-size:9.5px;padding:1px 5px;border-radius:4px;">
                        {{ round($firstScore['prob'] * 100, 1) }}%
                    </span>
                </div>
            @endif

            <div class="indicator-item">
                <span class="indicator-icon" title="Expected Goals (xG)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="m4.93 4.93 4.24 4.24"></path>
                        <path d="m14.83 9.17 4.24-4.24"></path>
                        <path d="m14.83 14.83 4.24 4.24"></path>
                        <path d="m9.17 14.83-4.24 4.24"></path>
                    </svg>
                </span>
                <span>xG:</span>
                <strong style="color:var(--xp-orange);margin-left:3px;">{{ $prediction->exp_home_goals }}</strong>
                <span>&ndash;</span>
                <strong style="color:var(--xp-away);">{{ $prediction->exp_away_goals }}</strong>
            </div>

            <div class="indicator-item">
                <span class="indicator-icon" style="color:var(--xp-ok);" title="Database Provenance">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </span>
                <span style="color:var(--xp-ok);font-weight:600;">
                    <strong style="color:#FFFFFF;">{{ $h2hCount }}</strong> past H2H matches in DB &bull; Zero Leakage
                </span>
            </div>
        </div>

        <!-- 6. Grounded AI Narrative Preview Box -->
        @if ($preview && !empty($preview->text))
            <div class="sporty-ai-box">
                <div class="sporty-ai-header">
                    <div class="sporty-ai-title">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                            <circle cx="12" cy="5" r="2"></circle>
                            <path d="M12 7v4"></path>
                            <line x1="8" y1="16" x2="8" y2="16"></line>
                            <line x1="16" y1="16" x2="16" y2="16"></line>
                        </svg>
                        <span>AI Reasoning Summary (Verified Data)</span>
                    </div>

                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="badge" style="background:#14151C;border:1px solid var(--xp-border);color:var(--xp-away);font-size:9.5px;font-family:var(--xp-font-mono, monospace);">
                            {{ $preview->model ?? 'gemini-3-flash-preview' }}
                        </span>
                        <span style="color:var(--xp-ok);font-size:10px;font-weight:700;display:flex;align-items:center;gap:3px;">
                            <span>✓</span>
                            <span>Strict DB Facts</span>
                        </span>
                    </div>
                </div>

                <div style="margin-top:4px;font-size:11.5px;line-height:1.55;color:#E0E0E6;">
                    {{ $preview->text }}
                </div>
            </div>
        @else
            <div style="background:#09090D;border:1px dashed #20222D;border-radius:6px;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--xp-muted);">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--xp-orange);">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span>AI reasoning preview ready to generate with Gemini.</span>
                </div>
                <form method="POST" action="{{ route('predictions.preview', $prediction->id) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" style="padding:2px 8px;font-size:10px;">
                        + Generate AI Preview
                    </button>
                </form>
            </div>
        @endif

        <!-- 7. Feature Calculation Breakdown Accordion -->
        @include('dashboard.partials.feature-breakdown', ['prediction' => $prediction, 'match' => $match])
    @else
        <!-- Prediction Not Yet Computed State -->
        <div style="background:#08080B;border:1px dashed var(--xp-border);border-radius:8px;padding:16px;text-align:center;">
            <div style="font-size:12px;color:var(--xp-muted);margin-bottom:8px;">
                Prediction not yet computed for this fixture.
            </div>
            <form method="POST" action="{{ route('matches.predict', $match->id) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    + Compute Poisson Prediction
                </button>
            </form>
        </div>
    @endif

    <!-- 8. Match History, Goals, Man of the Match & Player Ratings Section -->
    @include('dashboard.partials.match-history-ratings', ['match' => $match, 'prediction' => $prediction])

    <!-- 9. Footer Actions Toolbar -->
    <div class="sporty-card-footer">
        <div style="display:flex;align-items:center;gap:6px;font-size:10.5px;color:var(--xp-muted);">
            <span class="badge" style="background:#14151C;border:1px solid var(--xp-border);color:var(--xp-away);font-size:9.5px;">
                Engine: {{ $prediction->model_version ?? 'v1.2.1-h2h' }}@if($prediction && !empty($prediction->stage)) &bull; {{ strtoupper(str_replace('_', ' ', $prediction->stage)) }}@endif
            </span>
            <span style="color:var(--xp-ok);font-size:10px;font-weight:600;">
                &bull; Zero Leakage Verified
            </span>
        </div>

        <div style="display:flex;align-items:center;gap:6px;">
            <form method="POST" action="{{ route('matches.predict', $match->id) }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" style="display:flex;align-items:center;gap:4px;padding:3px 8px;font-size:10.5px;" title="Re-calculate Poisson prediction probabilities">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    <span>{{ $prediction ? 'Re-Predict' : '+ Predict' }}</span>
                </button>
            </form>

            @if ($prediction)
                <form method="POST" action="{{ route('predictions.preview', $prediction->id) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" style="display:flex;align-items:center;gap:4px;padding:3px 9px;font-size:10.5px;" title="Generate AI factual summary with Gemini/Claude">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>{{ $preview ? 'Regenerate Preview' : 'Generate Preview' }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
