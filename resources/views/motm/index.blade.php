<x-layouts.app>
    <div style="padding: 24px 32px; max-width: 1440px; margin: 0 auto;">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div style="background: rgba(61, 220, 132, 0.12); border: 1px solid var(--xp-ok); color: var(--xp-ok); padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
                <span>✓ {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background:none;border:none;color:var(--xp-ok);font-size:16px;cursor:pointer;">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div style="background: rgba(255, 77, 77, 0.12); border: 1px solid var(--xp-error); color: var(--xp-error); padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
                <span>⚠ {{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background:none;border:none;color:var(--xp-error);font-size:16px;cursor:pointer;">&times;</button>
            </div>
        @endif

        {{-- Top Header & Integrity Guarantee --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--xp-orange); box-shadow: 0 0 10px var(--xp-orange);"></span>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--xp-orange); font-weight: 700;">Stats + Story Decision Engine &bull; Strictly 1 Winner Per Match</span>
                </div>
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 26px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em;">
                    Man of the Match &amp; Squad Performance Hub
                </h1>
                <p style="font-size: 13px; color: var(--xp-muted); margin-top: 4px;">
                    Partly a stats question (Opta/SofaScore data ratings), partly a story question (decisive actions &amp; winning-team moments).
                </p>
            </div>

            {{-- Integrity Pill & Quick Actions --}}
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div style="background: rgba(61,220,132,0.1); border: 1px solid rgba(61,220,132,0.3); border-radius: 6px; padding: 6px 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="color:var(--xp-ok); font-size: 14px;">✓</span>
                    <span style="font-size: 12px; font-weight: 600; color: var(--xp-ok);">Strictly 1 MOTM Per Fixture Guaranteed</span>
                </div>

                <form method="POST" action="{{ route('motm.batch') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Evaluate All Finished Matches</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- MOTM PHILOSOPHY EXPLAINER BANNER (Stats vs Story) --}}
        <div style="background: linear-gradient(135deg, rgba(255,107,0,0.08) 0%, rgba(22,22,24,0.95) 100%); border: 1px solid rgba(255,107,0,0.25); border-radius: 10px; padding: 16px 20px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span style="color:var(--xp-orange); font-size: 15px;">📊</span>
                        <span style="font-size: 12px; font-weight: 700; color: var(--xp-orange); text-transform: uppercase;">1. The Stats Question: Data Rating (0 to 10)</span>
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); line-height: 1.4; margin: 0;">
                        Opta/SofaScore action scores measuring total 90-minute output: key passes, chances created, tackles, clearances, passing accuracy, xG, and defensive clean sheets. <em>The truest measure of performance.</em>
                    </p>
                </div>

                <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span style="color:#3DDC84; font-size: 15px;">🏆</span>
                        <span style="font-size: 12px; font-weight: 700; color: #3DDC84; text-transform: uppercase;">2. The Story Question: MOTM Award Decision</span>
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); line-height: 1.4; margin: 0;">
                        How panels, broadcasters and sponsors decide the award: winning-team consensus, decisive match-winning goals, braces, hat-tricks, and goalkeepers under heavy siege. <em>The defining moment of the match.</em>
                    </p>
                </div>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            {{-- League Filter Pills --}}
            <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 2px;">
                <a href="{{ route('motm', ['q' => $searchQuery ?: null]) }}" class="btn {{ !$selectedComp ? 'btn-primary' : 'btn-secondary' }}" style="padding: 6px 14px; font-size: 12px; border-radius: 20px;">
                    All Competitions
                </a>
                @foreach ($competitions as $comp)
                    <a href="{{ route('motm', ['competition' => $comp->slug, 'q' => $searchQuery ?: null]) }}" class="btn {{ $selectedComp && $selectedComp->id === $comp->id ? 'btn-primary' : 'btn-secondary' }}" style="padding: 6px 14px; font-size: 12px; border-radius: 20px;">
                        {{ $comp->name }}
                    </a>
                @endforeach
            </div>

            {{-- Live Search Form --}}
            <form method="GET" action="{{ route('motm') }}" style="display: flex; gap: 8px; min-width: 280px;">
                @if ($selectedComp)
                    <input type="hidden" name="competition" value="{{ $selectedComp->slug }}">
                @endif
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Search player (e.g. Haaland, Saka, Salah) or club..." style="flex: 1; background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 6px 12px; font-size: 12px; color: #FFFFFF; outline: none;">
                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px;">Search</button>
                @if ($searchQuery)
                    <a href="{{ route('motm', ['competition' => $selectedComp?->slug]) }}" class="btn btn-secondary btn-sm" style="padding: 6px 10px; color: var(--xp-muted);">Clear</a>
                @endif
            </form>
        </div>

        {{-- Top 4 KPI Metric Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <div class="stat-card">
                <div class="stat-label">Conferred MOTM Awards</div>
                <div class="stat-value" style="color:var(--xp-orange);">{{ number_format($totalAwardsCount) }}</div>
                <div class="stat-meta">Historical matches awarded</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Single-Winner Integrity</div>
                <div class="stat-value" style="color:var(--xp-ok);">100%</div>
                <div class="stat-meta">{{ number_format($uniqueMatchesWithMotm) }} matches &bull; Exactly 1 winner each</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Player Roster Fidelity</div>
                <div class="stat-value" style="font-size: 1.3rem; color: #FFFFFF;">Confirmed Names</div>
                <div class="stat-meta">Verified starting XIs &amp; canonical club rosters</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Squad Ratings Tracked</div>
                <div class="stat-value">{{ number_format($totalEvaluationsCount) }}</div>
                <div class="stat-meta">Starting XI performance records</div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- HERO SPOTLIGHT: SELECTED MATCH & DETAILED WHY MOTM CARD   --}}
        {{-- ======================================================== --}}
        @if ($selectedMatch)
            @php
                $motmWinner = $matchEvaluations->firstWhere('is_motm', true);
                $homeScore = (int) ($selectedMatch->home_goals ?? 0);
                $awayScore = (int) ($selectedMatch->away_goals ?? 0);
            @endphp

            <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px; margin-bottom: 30px; position: relative; overflow: hidden;">
                {{-- Ambient Golden Glow behind Hero --}}
                <div style="position: absolute; top: -40px; right: -40px; width: 320px; height: 320px; background: radial-gradient(circle, rgba(255,107,0,0.14) 0%, transparent 70%); pointer-events: none;"></div>

                {{-- Header context --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--xp-border); padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge" style="background: rgba(255,107,0,0.15); color: var(--xp-orange); font-size: 12px; padding: 4px 8px;">
                            {{ $selectedMatch->competition->name ?? 'League' }} &bull; MD {{ $selectedMatch->matchday }}
                        </span>
                        <span style="font-size: 13px; color: var(--xp-muted);">
                            {{ $selectedMatch->kickoff_at ? $selectedMatch->kickoff_at->format('l, d F Y') : 'Finished' }}
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="font-size: 18px; font-weight: 700; color: #FFFFFF;">
                            {{ $selectedMatch->homeTeam->name }} <span style="color:var(--xp-orange); margin: 0 4px; font-family: monospace;">{{ $homeScore }} - {{ $awayScore }}</span> {{ $selectedMatch->awayTeam->name }}
                        </div>
                        <form method="POST" action="{{ route('motm.evaluate', $selectedMatch->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 5px 12px; font-size: 11px;">
                                ↻ Re-evaluate Match
                            </button>
                        </form>
                    </div>
                </div>

                @if ($motmWinner)
                    {{-- 2-Column Hero: MOTM Spotlight on Left, Squad XI on Right --}}
                    <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 24px; align-items: start;">
                        
                        {{-- LEFT: OFFICIAL MAN OF THE MATCH AWARD CARD --}}
                        <div style="background: linear-gradient(135deg, rgba(255,107,0,0.16) 0%, rgba(22,22,24,0.98) 100%); border: 1.5px solid var(--xp-orange); border-radius: 8px; padding: 22px; position: relative;">
                            
                            {{-- Top Banner: Trophy, Player Name & Dual Scores --}}
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--xp-orange); display: flex; align-items: center; justify-content: center; font-size: 28px; color: #000000; font-weight: 900; box-shadow: 0 0 20px rgba(255,107,0,0.5);">
                                        ★
                                    </div>
                                    <div>
                                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--xp-orange); font-weight: 800;">
                                            Official Man of the Match Decision
                                        </div>
                                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 24px; font-weight: 700; color: #FFFFFF; line-height: 1.2; margin-top: 2px;">
                                            {{ $motmWinner->player_name }}
                                            @if ($motmWinner->player_number)
                                                <span style="font-size: 15px; color: var(--xp-muted); font-weight: 400; margin-left: 4px;">#{{ $motmWinner->player_number }}</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 13px; color: var(--xp-muted); margin-top: 2px;">
                                            <strong style="color:#FFFFFF;">{{ $motmWinner->team->name }}</strong> &bull; Position: <span class="badge" style="background:rgba(255,255,255,0.08);color:#FFFFFF;">{{ $motmWinner->position ?? 'Starter' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dual Scores: Data Rating vs Decision Score --}}
                                <div style="display: flex; gap: 8px;">
                                    <div style="text-align: right; background: rgba(0,0,0,0.4); border: 1px solid var(--xp-border); border-radius: 8px; padding: 8px 12px;">
                                        <div style="font-size: 11px; color: var(--xp-muted); text-transform: uppercase;">Opta/Data</div>
                                        <div style="font-size: 22px; font-weight: 800; color: #FFFFFF; font-family: 'Space Grotesk', sans-serif;">
                                            {{ number_format((float)($motmWinner->data_rating ?? $motmWinner->rating), 1) }}
                                        </div>
                                    </div>

                                    <div style="text-align: right; background: rgba(255,107,0,0.15); border: 1px solid var(--xp-orange); border-radius: 8px; padding: 8px 14px;">
                                        <div style="font-size: 11px; color: var(--xp-orange); text-transform: uppercase; font-weight: 700;">Decision Score</div>
                                        <div style="font-size: 26px; font-weight: 900; color: var(--xp-orange); font-family: 'Space Grotesk', sans-serif; line-height: 1;">
                                            {{ number_format((float)($motmWinner->decision_score ?? $motmWinner->rating), 1) }} ★
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Decisive Actions Pill Tags --}}
                            @if (!empty($motmWinner->decisive_actions))
                                <div style="display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap;">
                                    @foreach ($motmWinner->decisive_actions as $action)
                                        <span class="badge" style="background: rgba(255,107,0,0.18); color: var(--xp-orange); border: 1px solid rgba(255,107,0,0.3); font-size: 11px; padding: 4px 8px;">
                                            ⚡ {{ $action }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Contribution Metrics Row --}}
                            <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
                                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--xp-border); border-radius: 6px; padding: 7px 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="color:var(--xp-orange); font-size: 13px;">⚽</span>
                                    <span style="font-size: 12px; color: #FFFFFF;"><strong>{{ $motmWinner->goals }}</strong> Goals</span>
                                </div>
                                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--xp-border); border-radius: 6px; padding: 7px 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="color:var(--xp-ok); font-size: 13px;">🎯</span>
                                    <span style="font-size: 12px; color: #FFFFFF;"><strong>{{ $motmWinner->assists }}</strong> Assists</span>
                                </div>
                                @if ($motmWinner->xg)
                                    <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--xp-border); border-radius: 6px; padding: 7px 12px; display: flex; align-items: center; gap: 8px;">
                                        <span style="color:var(--xp-muted); font-size: 13px;">📈</span>
                                        <span style="font-size: 12px; color: #FFFFFF;"><strong>{{ number_format((float)$motmWinner->xg, 2) }}</strong> xG</span>
                                    </div>
                                @endif
                                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--xp-border); border-radius: 6px; padding: 7px 12px; display: flex; align-items: center; gap: 8px;">
                                    <span style="color:var(--xp-ok); font-size: 13px;">✓</span>
                                    <span style="font-size: 12px; color: #FFFFFF;">Strictly 1 of 1</span>
                                </div>
                            </div>

                            {{-- THE CORE "WHY HE WAS MAN OF THE MATCH THAT DAY" BOX --}}
                            <div style="background: #101012; border: 1px solid rgba(255,107,0,0.35); border-radius: 6px; padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                    <span style="font-size: 14px; color: var(--xp-orange);">💡</span>
                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--xp-orange); font-weight: 700;">
                                        Why He Was Player of the Match That Day:
                                    </span>
                                </div>
                                <p style="font-size: 13px; color: #FFFFFF; line-height: 1.6; margin: 0; font-style: normal;">
                                    {{ $motmWinner->performance_summary }}
                                </p>
                            </div>

                        </div>

                        {{-- RIGHT: FULL SQUAD RATINGS BREAKDOWN (STATS VS DECISION) --}}
                        <div style="background: #101012; border: 1px solid var(--xp-border); border-radius: 8px; padding: 18px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 14px; font-weight: 700; color: #FFFFFF;">
                                    Squad Ratings Breakdown ({{ $matchEvaluations->count() }} Players)
                                </h3>
                                <span style="font-size: 11px; color: var(--xp-muted);">Opta vs Decision</span>
                            </div>

                            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Player</th>
                                            <th>Pos</th>
                                            <th>G/A</th>
                                            <th style="text-align: right;" title="Opta/SofaScore pure statistical action score out of 10">Data Rating</th>
                                            <th style="text-align: right;" title="Story & Decisive Actions Decision Score">Decision</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($matchEvaluations as $me)
                                            <tr style="{{ $me->is_motm ? 'background: rgba(255,107,0,0.12); font-weight:600;' : '' }}">
                                                <td style="color: {{ $me->is_motm ? 'var(--xp-orange)' : '#FFFFFF' }};">
                                                    @if ($me->is_motm)
                                                        <span style="color:var(--xp-orange); margin-right: 4px; font-weight:700;">★</span>
                                                    @endif
                                                    {{ $me->player_name }}
                                                    @if ($me->player_number)
                                                        <span style="font-size: 11px; color: var(--xp-dim);">#{{ $me->player_number }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--xp-muted); font-size: 10px;">
                                                        {{ $me->position ?? 'M' }}
                                                    </span>
                                                </td>
                                                <td style="font-size: 11px; color: var(--xp-muted); font-family: monospace;">
                                                    {{ $me->goals }}/{{ $me->assists }}
                                                </td>
                                                <td style="text-align: right; font-weight: 600; font-family: monospace; color: #FFFFFF;">
                                                    {{ number_format((float)($me->data_rating ?? $me->rating), 1) }}
                                                </td>
                                                <td style="text-align: right; font-weight: 700; font-family: monospace; color: {{ $me->is_motm ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                                    {{ number_format((float)($me->decision_score ?? $me->rating), 1) }}
                                                    @if ($me->is_motm)
                                                        <span style="color:var(--xp-orange);">★</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- MATCH-BY-MATCH CONFIRMED MOTM FEED & ARCHIVE             --}}
        {{-- ======================================================== --}}
        <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF;">
                        Confirmed Match-by-Match Man of the Match Feed
                    </h2>
                    <p style="font-size: 12px; color: var(--xp-muted); margin-top: 2px;">
                        Showing finished matches with verified single MOTM winners and tactical rationales.
                    </p>
                </div>
                <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--xp-muted);">
                    {{ $finishedMatches->count() }} Fixtures Listed
                </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                @forelse ($finishedMatches as $fm)
                    @php
                        $evalWinner = $fm->motmEvaluations->firstWhere('is_motm', true);
                        $isCurrent = $selectedMatch && $selectedMatch->id === $fm->id;
                    @endphp

                    <div style="background: #101012; border: 1px solid {{ $isCurrent ? 'var(--xp-orange)' : 'var(--xp-border)' }}; border-radius: 8px; padding: 16px 20px; transition: border-color 0.15s ease;">
                        
                        {{-- Top Header Row: Fixture Context & Score --}}
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--xp-muted); font-size: 11px;">
                                    {{ $fm->competition->name ?? 'League' }} &bull; MD {{ $fm->matchday }}
                                </span>
                                <span style="font-size: 12px; color: var(--xp-muted);">
                                    {{ $fm->kickoff_at ? $fm->kickoff_at->format('D, d M Y') : 'Finished' }}
                                </span>
                            </div>

                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="font-size: 15px; font-weight: 700; color: #FFFFFF;">
                                    {{ $fm->homeTeam->short_name ?? $fm->homeTeam->name }}
                                    <span style="color:var(--xp-orange); margin: 0 4px; font-family: monospace;">{{ $fm->home_goals ?? 0 }} - {{ $fm->away_goals ?? 0 }}</span>
                                    {{ $fm->awayTeam->short_name ?? $fm->awayTeam->name }}
                                </div>
                                <a href="{{ route('motm', ['match_id' => $fm->id, 'competition' => $selectedComp?->slug, 'q' => $searchQuery ?: null]) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 11px;">
                                    {{ $isCurrent ? 'Viewing in Spotlight ✓' : 'Inspect Full Match &rarr;' }}
                                </a>
                            </div>
                        </div>

                        {{-- MOTM Winner Showcase & Detailed Explanation --}}
                        @if ($evalWinner)
                            <div style="background: rgba(255,107,0,0.06); border: 1px solid rgba(255,107,0,0.2); border-radius: 6px; padding: 12px 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="width: 24px; height: 24px; border-radius: 50%; background: var(--xp-orange); color: #000000; display: inline-flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px;">★</span>
                                        <div>
                                            <span style="font-size: 15px; font-weight: 700; color: #FFFFFF;">
                                                {{ $evalWinner->player_name }}
                                            </span>
                                            @if ($evalWinner->player_number)
                                                <span style="font-size: 12px; color: var(--xp-muted);">#{{ $evalWinner->player_number }}</span>
                                            @endif
                                            <span style="font-size: 12px; color: var(--xp-muted); margin-left: 6px;">
                                                ({{ $evalWinner->team->short_name ?? $evalWinner->team->name }} &bull; {{ $evalWinner->position ?? 'Starter' }})
                                            </span>
                                        </div>
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        @if ($evalWinner->goals > 0)
                                            <span class="badge" style="background: rgba(255,107,0,0.15); color: var(--xp-orange);">
                                                {{ $evalWinner->goals }} Goals
                                            </span>
                                        @endif
                                        @if ($evalWinner->assists > 0)
                                            <span class="badge" style="background: rgba(61,220,132,0.15); color: var(--xp-ok);">
                                                {{ $evalWinner->assists }} Assists
                                            </span>
                                        @endif
                                        <span class="badge" style="background: rgba(255,255,255,0.06); color: #FFFFFF; font-size: 11px; font-family: monospace;">
                                            Opta: {{ number_format((float)($evalWinner->data_rating ?? $evalWinner->rating), 1) }}
                                        </span>
                                        <span class="badge" style="background: rgba(255,107,0,0.25); color: var(--xp-orange); font-size: 12.5px; font-weight: 800; font-family: monospace;">
                                            {{ number_format((float)($evalWinner->decision_score ?? $evalWinner->rating), 1) }} Decision ★
                                        </span>
                                    </div>
                                </div>

                                {{-- Why He Won Callout --}}
                                <div style="font-size: 12.5px; color: #FFFFFF; line-height: 1.5; padding-top: 6px; border-top: 1px solid rgba(255,107,0,0.15);">
                                    <strong style="color:var(--xp-orange);">Why He Was Player of the Match:</strong> {{ $evalWinner->performance_summary }}
                                </div>
                            </div>
                        @else
                            <div style="display: flex; justify-content: space-between; align-items: center; color: var(--xp-muted); font-size: 12px;">
                                <span>No Man of the Match evaluated yet.</span>
                                <form method="POST" action="{{ route('motm.evaluate', $fm->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Evaluate MOTM Now</button>
                                </form>
                            </div>
                        @endif

                    </div>
                @empty
                    <div style="text-align: center; color: var(--xp-muted); padding: 32px;">
                        No finished fixtures found matching your criteria.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- ALL-TIME / SEASON MOTM LEADERBOARD                       --}}
        {{-- ======================================================== --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            {{-- Top Players Card --}}
            <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color:var(--xp-orange); font-size: 16px;">★</span>
                        <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">
                            Top Players &bull; Most MOTM Decisions
                        </h3>
                    </div>
                    <span class="badge" style="background: rgba(255,107,0,0.15); color: var(--xp-orange);">
                        {{ $selectedComp ? $selectedComp->name : 'All Leagues' }}
                    </span>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 36px;">#</th>
                                <th>Player</th>
                                <th>Club</th>
                                <th style="text-align: center;">MOTMs</th>
                                <th style="text-align: right;">Avg Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($playerLeaderboard as $idx => $pl)
                                <tr>
                                    <td style="font-weight: 700; color: {{ $idx === 0 ? 'var(--xp-orange)' : ($idx < 3 ? '#FFFFFF' : 'var(--xp-muted)') }};">
                                        #{{ $idx + 1 }}
                                    </td>
                                    <td style="font-weight: 600; color: #FFFFFF;">
                                        {{ $pl->player_name }}
                                    </td>
                                    <td style="font-size: 12px; color: var(--xp-muted);">
                                        {{ $pl->team->short_name ?? $pl->team->name ?? 'Team' }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge" style="background: rgba(255,107,0,0.2); color: var(--xp-orange); font-weight: 800;">
                                            {{ $pl->motm_count }} ★
                                        </span>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #FFFFFF; font-family: monospace;">
                                        {{ number_format((float)$pl->avg_rating, 1) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--xp-muted); padding: 20px;">
                                        No player awards recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top Clubs Card --}}
            <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">
                        Top Clubs &bull; Total MOTM Tally
                    </h3>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    @forelse ($teamLeaderboard as $tl)
                        <div style="background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 13px; font-weight: 600; color: #FFFFFF;">
                                {{ $tl->team->short_name ?? $tl->team->name ?? 'Club' }}
                            </div>
                            <span class="badge" style="background: rgba(255,107,0,0.18); color: var(--xp-orange); font-weight: 800;">
                                {{ $tl->motm_count }} MOTMs
                            </span>
                        </div>
                    @empty
                        <div style="grid-column: span 2; text-align: center; color: var(--xp-muted); padding: 20px;">
                            No club tallies available yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
