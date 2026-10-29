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

        {{-- Top Header & Match Selector --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--xp-orange); box-shadow: 0 0 10px var(--xp-orange);"></span>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--xp-orange); font-weight: 700;">Model Provenance &amp; Verification</span>
                </div>
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 26px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em;">
                    AI Reasoning Studio &amp; Permutation Explainer
                </h1>
                <p style="font-size: 13px; color: var(--xp-muted); margin-top: 4px;">
                    Demystifying calculations: pure mathematical Poisson permutations vs. qualitative LLM reasoning.
                </p>
            </div>

            {{-- Match Selector Dropdown --}}
            <div style="display: flex; align-items: center; gap: 10px;">
                <label for="matchSelect" style="font-size: 12px; color: var(--xp-muted); font-weight: 600;">Inspect Match:</label>
                <form method="GET" action="{{ route('ai-reasoning') }}" style="display: flex; gap: 8px;">
                    <select id="matchSelect" name="match_id" onchange="this.form.submit()" style="background: #101012; border: 1px solid var(--xp-border); color: #FFFFFF; padding: 8px 14px; border-radius: 6px; font-size: 13px; outline: none; cursor: pointer; max-width: 320px;">
                        @foreach ($availableMatches as $m)
                            <option value="{{ $m->id }}" {{ $selectedMatch && $selectedMatch->id === $m->id ? 'selected' : '' }}>
                                {{ $m->homeTeam->short_name ?? $m->homeTeam->name }} vs {{ $m->awayTeam->short_name ?? $m->awayTeam->name }} ({{ $m->competition->name ?? 'League' }} &bull; MD {{ $m->matchday }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- Architectural Separation Banner --}}
        <div style="background: linear-gradient(135deg, rgba(255,107,0,0.08) 0%, rgba(22,22,24,0.95) 100%); border: 1px solid rgba(255,107,0,0.25); border-radius: 10px; padding: 18px 22px; margin-bottom: 24px;">
            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 20px; align-items: center;">
                {{-- Side A: Pure Math --}}
                <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span style="font-size: 15px;">📐</span>
                        <span style="font-size: 12px; font-weight: 700; color: var(--xp-orange); text-transform: uppercase; letter-spacing: 0.05em;">Engine 1: Pure Mathematical Model</span>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: #FFFFFF;">Dixon-Coles Bivariate Poisson (PHP)</div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin-top: 4px; line-height: 1.4;">
                        Computes all probabilities (P<sub>H</sub>, P<sub>D</sub>, P<sub>A</sub>), expected goals, and scoreline grids deterministically. <strong style="color:var(--xp-ok);">The AI never calculates numbers.</strong>
                    </p>
                </div>

                {{-- Separation Arrow --}}
                <div style="display: flex; flex-direction: column; align-items: center; color: var(--xp-orange); font-size: 18px; font-weight: 700;">
                    <span>⇄</span>
                    <span style="font-size: 10px; text-transform: uppercase; color: var(--xp-muted); letter-spacing: 0.05em; margin-top: 2px;">Separation of Concerns</span>
                </div>

                {{-- Side B: AI Reasoning --}}
                <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span style="font-size: 15px;">🤖</span>
                        <span style="font-size: 12px; font-weight: 700; color: #3DDC84; text-transform: uppercase; letter-spacing: 0.05em;">Engine 2: Grounded AI Reasoning</span>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: #FFFFFF;">Claude 3.5 Sonnet / Prompt Engine</div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin-top: 4px; line-height: 1.4;">
                        Receives only structured numbers from the model output. Explains expected goal balance and tactical implications without inventing facts.
                    </p>
                </div>
            </div>
        </div>

        @if ($selectedMatch && $prediction)
            {{-- Selected Match Spotlight Bar --}}
            <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <span class="badge" style="background: rgba(255,107,0,0.15); color: var(--xp-orange); font-size: 12px; padding: 4px 8px;">
                        {{ $selectedMatch->competition->name ?? 'League' }}
                    </span>
                    <div style="font-size: 15px; font-weight: 700; color: #FFFFFF;">
                        {{ $selectedMatch->homeTeam->name }} <span style="color:var(--xp-muted);font-weight:400;margin:0 4px;">vs</span> {{ $selectedMatch->awayTeam->name }}
                    </div>
                    <span style="font-size: 12px; color: var(--xp-muted);">
                        Kickoff: {{ $selectedMatch->kickoff_at ? $selectedMatch->kickoff_at->format('D, d M Y &bull; H:i') : 'TBD' }} UTC
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="text-align: right;">
                        <div style="font-size: 11px; color: var(--xp-muted); text-transform: uppercase;">Poisson Win Tilt</div>
                        <div style="font-size: 14px; font-weight: 700; color: #FFFFFF; margin-top: 2px;">
                            <span style="color:var(--xp-orange);">{{ round((float)$prediction->p_home * 100, 1) }}% H</span> &bull; 
                            <span style="color:#FFFFFF;">{{ round((float)$prediction->p_draw * 100, 1) }}% D</span> &bull; 
                            <span style="color:var(--xp-away);">{{ round((float)$prediction->p_away * 100, 1) }}% A</span>
                        </div>
                    </div>
                    <div style="text-align: right; border-left: 1px solid var(--xp-border); padding-left: 16px;">
                        <div style="font-size: 11px; color: var(--xp-muted); text-transform: uppercase;">Expected Goals (&lambda;)</div>
                        <div style="font-size: 14px; font-weight: 700; color: #FFFFFF; margin-top: 2px;">
                            {{ $prediction->exp_home_goals }} &ndash; {{ $prediction->exp_away_goals }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Two-Column Main Layout: Math on Left, AI Reasoning on Right --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px;">

                {{-- ========================================== --}}
                {{-- LEFT COLUMN: PURE MATHEMATICAL DERIVATION --}}
                {{-- ========================================== --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="color:var(--xp-orange); font-size: 16px;">📐</span>
                                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">
                                    Mathematical Calculation Derivation
                                </h3>
                            </div>
                            <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--xp-muted); font-family: monospace;">
                                {{ $prediction->model_version }}
                            </span>
                        </div>

                        {{-- Step-by-step Formulas --}}
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            {{-- Step 1: Sustainable Attack/Defence --}}
                            <div style="background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 12px 14px;">
                                <div style="font-size: 11px; text-transform: uppercase; color: var(--xp-orange); font-weight: 700; margin-bottom: 4px;">
                                    1. Sustainable xG Chance Creation Blend (&alpha; = 0.70)
                                </div>
                                <div style="font-family: monospace; font-size: 12px; color: #FFFFFF; line-height: 1.5;">
                                    Att<sub>Home</sub> = 0.70 &times; xG<sub>Att</sub> + 0.30 &times; Act<sub>Att</sub> = <strong>{{ $mathBreakdown['blended_home_attack'] }}</strong><br>
                                    Def<sub>Away</sub> = 0.70 &times; xG<sub>Def</sub> + 0.30 &times; Act<sub>Def</sub> = <strong>{{ $mathBreakdown['blended_away_defence'] }}</strong>
                                </div>
                                <div style="font-size: 11px; color: var(--xp-muted); margin-top: 4px;">
                                    Weighted over 20-match rolling window with 180-day exponential decay half-life.
                                </div>
                            </div>

                            {{-- Step 2: Rate Parameters (Lambdas) --}}
                            <div style="background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 12px 14px;">
                                <div style="font-size: 11px; text-transform: uppercase; color: var(--xp-orange); font-weight: 700; margin-bottom: 4px;">
                                    2. Expected Goals Poisson Rate Parameters (&lambda;)
                                </div>
                                <div style="font-family: monospace; font-size: 12px; color: #FFFFFF; line-height: 1.5;">
                                    &lambda;<sub>Home</sub> = Att<sub>H</sub> &times; Def<sub>A</sub> &times; Avg<sub>H</sub> &times; H2H = <strong>{{ $mathBreakdown['lambda_home'] }}</strong><br>
                                    &lambda;<sub>Away</sub> = Att<sub>A</sub> &times; Def<sub>H</sub> &times; Avg<sub>A</sub> / H2H = <strong>{{ $mathBreakdown['lambda_away'] }}</strong>
                                </div>
                                <div style="font-size: 11px; color: var(--xp-muted); margin-top: 4px;">
                                    League baseline averages: {{ $mathBreakdown['league_avg_home'] }} (home) / {{ $mathBreakdown['league_avg_away'] }} (away) goals per match.
                                </div>
                            </div>

                            {{-- Step 3: Dixon-Coles Adjustment --}}
                            <div style="background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 12px 14px;">
                                <div style="font-size: 11px; text-transform: uppercase; color: var(--xp-orange); font-weight: 700; margin-bottom: 4px;">
                                    3. Dixon-Coles Low-Score Correlation (&rho; = -0.06)
                                </div>
                                <div style="font-family: monospace; font-size: 12px; color: #FFFFFF; line-height: 1.5;">
                                    P(x, y) = &tau;(x, y) &times; [ (&lambda;<sub>H</sub><sup>x</sup> e<sup>-&lambda;<sub>H</sub></sup> / x!) &times; (&lambda;<sub>A</sub><sup>y</sup> e<sup>-&lambda;<sub>A</sub></sup> / y!) ]<br>
                                    &tau;(0,0) = 1 - &lambda;<sub>H</sub>&lambda;<sub>A</sub>&rho; &bull; &tau;(1,1) = 1 - &rho;
                                </div>
                                <div style="font-size: 11px; color: var(--xp-muted); margin-top: 4px;">
                                    Accounts for empirical football correlation in low-scoring draws and 1-0 results.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 6x6 Scoreline Probability Permutation Matrix --}}
                    <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div>
                                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 15px; font-weight: 700; color: #FFFFFF;">
                                    Scoreline Probability Heatmap (0..5 &times; 0..5)
                                </h3>
                                <p style="font-size: 11px; color: var(--xp-muted); margin-top: 2px;">
                                    Bivariate Poisson matrix after Dixon-Coles low-score correction
                                </p>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 11px;">
                                <span style="display:inline-block;width:10px;height:10px;background:rgba(255,107,0,0.7);border-radius:2px;"></span> Peak Prob
                                <span style="display:inline-block;width:10px;height:10px;background:#101012;border:1px solid var(--xp-border);border-radius:2px;margin-left:4px;"></span> Low
                            </div>
                        </div>

                        {{-- The Matrix Grid Table --}}
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: center; font-family: monospace; font-size: 11px;">
                                <thead>
                                    <tr>
                                        <th style="background: transparent; color: var(--xp-muted); font-size: 10px; border-bottom: 1px solid var(--xp-border); padding: 6px;">H \ A</th>
                                        @for ($a = 0; $a <= 5; $a++)
                                            <th style="background: #101012; color: var(--xp-muted); padding: 6px; border-bottom: 1px solid var(--xp-border); font-size: 10px;">{{ $a }} Aw</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($h = 0; $h <= 5; $h++)
                                        <tr>
                                            <td style="background: #101012; color: var(--xp-muted); font-weight: 700; padding: 6px; border-right: 1px solid var(--xp-border);">{{ $h }} Hm</td>
                                            @for ($a = 0; $a <= 5; $a++)
                                                @php
                                                    $cell = $scoreGrid[$h][$a] ?? ['pct' => 0.0, 'prob' => 0.0, 'outcome' => 'draw'];
                                                    $pct = $cell['pct'];
                                                    $ratio = $maxGridProb > 0 ? ($cell['prob'] / $maxGridProb) : 0;
                                                    $bg = $ratio > 0.6 
                                                        ? 'rgba(255, 107, 0, ' . (0.2 + $ratio * 0.6) . ')' 
                                                        : ($ratio > 0.3 ? 'rgba(255, 107, 0, 0.15)' : '#101012');
                                                    $isPeak = $cell['prob'] === $maxGridProb;
                                                    $borderColor = $isPeak ? 'var(--xp-orange)' : 'var(--xp-border)';
                                                @endphp
                                                <td style="background: {{ $bg }}; border: 1px solid {{ $borderColor }}; padding: 7px 4px; color: {{ $isPeak ? '#FFFFFF' : ($ratio > 0.3 ? '#FFFFFF' : 'var(--xp-muted)') }}; font-weight: {{ $isPeak ? '700' : '400' }};" title="{{ $h }}-{{ $a }}: {{ $pct }}%">
                                                    <div style="font-size: 9px; color: {{ $isPeak ? 'var(--xp-orange)' : 'var(--xp-dim)' }};">{{ $h }}-{{ $a }}</div>
                                                    <div>{{ $pct }}%</div>
                                                </td>
                                            @endfor
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        {{-- Integration Summary --}}
                        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--xp-border); display: flex; justify-content: space-around; font-size: 12px;">
                            <div>
                                <span style="color:var(--xp-muted);">P<sub>Home</sub> (&sum; h &gt; a):</span>
                                <strong style="color:var(--xp-orange); margin-left: 4px;">{{ $mathBreakdown['p_home'] }}%</strong>
                            </div>
                            <div>
                                <span style="color:var(--xp-muted);">P<sub>Draw</sub> (&sum; h = a):</span>
                                <strong style="color:#FFFFFF; margin-left: 4px;">{{ $mathBreakdown['p_draw'] }}%</strong>
                            </div>
                            <div>
                                <span style="color:var(--xp-muted);">P<sub>Away</sub> (&sum; h &lt; a):</span>
                                <strong style="color:var(--xp-away); margin-left: 4px;">{{ $mathBreakdown['p_away'] }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- RIGHT COLUMN: AI QUALITATIVE REASONING     --}}
                {{-- ========================================== --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    
                    {{-- Active Reasoning Preview Card --}}
                    <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">🤖</span>
                                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF;">
                                    Grounded AI Preview &amp; Explainer
                                </h3>
                            </div>
                            
                            @if ($existingPreview)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @if ($existingPreview->flagged)
                                        <span class="badge" style="background: rgba(255,77,77,0.15); color: var(--xp-error); border: 1px solid var(--xp-error);">
                                            ⚠ FLAGGED (Disallowed Numbers)
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(61,220,132,0.15); color: var(--xp-ok); border: 1px solid var(--xp-ok);">
                                            ✓ GUARD VERIFIED (0 Hallucinations)
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Preview Content or Empty State --}}
                        @if ($existingPreview)
                            <div style="background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 14px 16px; margin-bottom: 14px;">
                                <div style="font-size: 13px; color: #FFFFFF; line-height: 1.6; white-space: pre-line;">
                                    {{ $existingPreview->text }}
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--xp-border); font-size: 11px; color: var(--xp-muted);">
                                    <span>Model: <strong style="color:#FFFFFF;">{{ $existingPreview->model }}</strong></span>
                                    <span>Prompt: <code style="color:var(--xp-orange);">{{ $existingPreview->prompt_version }}</code></span>
                                    <span>Recorded: {{ $existingPreview->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @else
                            <div style="background: #101012; border: 1px dashed var(--xp-border); border-radius: 6px; padding: 24px 20px; text-align: center; margin-bottom: 16px;">
                                <div style="font-size: 24px; margin-bottom: 8px;">📝</div>
                                <div style="font-size: 13px; font-weight: 600; color: #FFFFFF;">No AI Preview Generated Yet for this Prediction</div>
                                <p style="font-size: 12px; color: var(--xp-muted); margin-top: 4px;">
                                    Click below to run the AI reasoning engine. The engine uses strict anti-hallucination instructions to ground every statement in the mathematical numbers.
                                </p>
                            </div>
                        @endif

                        {{-- Action Form: Generate / Simulate AI Reasoning --}}
                        <form method="POST" action="{{ route('ai-reasoning.simulate') }}" style="display: flex; flex-direction: column; gap: 10px;">
                            @csrf
                            <input type="hidden" name="prediction_id" value="{{ $prediction->id }}">

                            <div style="display: flex; gap: 10px; align-items: center;">
                                <select name="mode" style="background: #101012; border: 1px solid var(--xp-border); color: #FFFFFF; padding: 8px 12px; border-radius: 6px; font-size: 12px; outline: none;">
                                    @if ($hasAnthropicKey)
                                        <option value="auto">Live Claude 3.5 Sonnet (API Key Active)</option>
                                        <option value="simulation">Deterministic Simulation (Offline)</option>
                                    @else
                                        <option value="simulation">Deterministic Simulation (Safe Offline)</option>
                                    @endif
                                </select>

                                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 8px 16px; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                    <span>{{ $existingPreview ? 'Re-Run Reasoning Preview' : 'Generate AI Reasoning Preview' }}</span>
                                </button>
                            </div>
                            <div style="font-size: 11px; color: var(--xp-muted); text-align: center;">
                                Saves automatically to persistent SQLite history with API call traceability.
                            </div>
                        </form>
                    </div>

                    {{-- Structured Input JSON Payload Inspector --}}
                    <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div>
                                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 15px; font-weight: 700; color: #FFFFFF;">
                                    Structured Payload Passed to LLM
                                </h3>
                                <p style="font-size: 11px; color: var(--xp-muted); margin-top: 2px;">
                                    Verified AGENTS.md Rule 6: Only structured numbers are provided. Zero external opinions.
                                </p>
                            </div>
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('rawPayloadCode').innerText); this.innerText='Copied!';" style="background: #101012; border: 1px solid var(--xp-border); color: var(--xp-muted); padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                                Copy JSON
                            </button>
                        </div>

                        <div style="background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 12px; max-height: 220px; overflow-y: auto;">
                            <pre id="rawPayloadCode" style="font-family: monospace; font-size: 11px; color: #A1A1A6; line-height: 1.4; margin: 0;">{{ json_encode($aiPayload['input_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>

                    {{-- Interactive Anti-Hallucination Guard Sandbox --}}
                    <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 15px; font-weight: 700; color: #FFFFFF;">
                                Anti-Hallucination Guard Tester
                            </h3>
                            <span class="badge" style="background: rgba(61,220,132,0.12); color: var(--xp-ok);">
                                Live Inspector
                            </span>
                        </div>
                        <p style="font-size: 12px; color: var(--xp-muted); margin-bottom: 12px;">
                            Paste any candidate text to test against this match's model output. The guard instantly detects disallowed numbers or outside data.
                        </p>

                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <textarea id="guardTestText" rows="3" style="width: 100%; background: #101012; border: 1px solid var(--xp-border); border-radius: 6px; padding: 10px; color: #FFFFFF; font-size: 12px; resize: vertical;" placeholder="e.g. Manchester City host Liverpool with 55% home win probability and expected 1-0 scoreline..."></textarea>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div id="guardResultStatus" style="font-size: 12px; font-weight: 600; color: var(--xp-muted);">
                                    Ready to inspect
                                </div>
                                <button type="button" onclick="testCandidateWithGuard()" class="btn btn-secondary btn-sm" style="padding: 6px 14px;">
                                    Check for Hallucinations
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else
            {{-- Empty State: No Matches Available --}}
            <div style="background: var(--xp-surface); border: 1px dashed var(--xp-border); border-radius: 12px; padding: 48px 24px; text-align: center; margin-bottom: 30px;">
                <div style="font-size: 32px; margin-bottom: 12px;">📊</div>
                <h3 style="font-size: 16px; font-weight: 700; color: #FFFFFF;">No Predicted Matches Found</h3>
                <p style="font-size: 13px; color: var(--xp-muted); max-width: 480px; margin: 8px auto 20px;">
                    Predictions are required to run the permutation explainer and AI reasoning studio.
                </p>
                <a href="{{ route('today') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px;">
                    Go to Today's Fixtures Hub &rarr;
                </a>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- PERSISTENT REASONING HISTORY (Full Audit Trail)         --}}
        {{-- ======================================================== --}}
        <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 17px; font-weight: 700; color: #FFFFFF;">
                        Persistent Reasoning History
                    </h3>
                    <p style="font-size: 12px; color: var(--xp-muted); margin-top: 2px;">
                        Immutable audit trail: every generated AI reasoning preview and simulation is permanently logged.
                    </p>
                </div>
                <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--xp-muted);">
                    {{ $recentPreviews->count() }} Previews in History
                </span>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Fixture &amp; Competition</th>
                            <th>Model / Source</th>
                            <th>Prompt</th>
                            <th>Guard Status</th>
                            <th>Reasoning Excerpt</th>
                            <th>Timestamp (UTC)</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentPreviews as $p)
                            @php
                                $m = $p->prediction?->match;
                            @endphp
                            <tr>
                                <td>
                                    @if ($m)
                                        <div style="font-weight: 600; color: #FFFFFF;">
                                            {{ $m->homeTeam->short_name ?? $m->homeTeam->name }} vs {{ $m->awayTeam->short_name ?? $m->awayTeam->name }}
                                        </div>
                                        <div style="font-size: 11px; color: var(--xp-muted);">
                                            {{ $m->competition->name ?? 'League' }} &bull; MD {{ $m->matchday }}
                                        </div>
                                    @else
                                        <span style="color:var(--xp-muted);">Prediction #{{ $p->prediction_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: #FFFFFF; font-weight: 500;">{{ $p->model }}</span>
                                    @if ($p->api_call_id)
                                        <div style="font-size: 10px; color: var(--xp-muted);">API Call #{{ $p->api_call_id }}</div>
                                    @endif
                                </td>
                                <td>
                                    <code style="font-size: 11px; color: var(--xp-orange);">{{ $p->prompt_version }}</code>
                                </td>
                                <td>
                                    @if ($p->flagged)
                                        <span class="badge badge-danger">FLAGGED</span>
                                    @else
                                        <span class="badge badge-success">PASSED</span>
                                    @endif
                                </td>
                                <td style="max-width: 380px;">
                                    <div style="font-size: 12px; color: var(--xp-muted); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $p->text }}
                                    </div>
                                </td>
                                <td style="font-size: 12px; color: var(--xp-muted); white-space: nowrap;">
                                    {{ $p->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td style="text-align: right;">
                                    @if ($m)
                                        <a href="{{ route('ai-reasoning', ['match_id' => $m->id]) }}" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 8px;">
                                            Inspect &rarr;
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--xp-muted); padding: 24px;">
                                    No reasoning history recorded yet. Run a preview above to create the first record.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Interactive Guard Checker Script --}}
    <script>
        function testCandidateWithGuard() {
            const text = document.getElementById('guardTestText').value.trim();
            const statusEl = document.getElementById('guardResultStatus');

            if (!text) {
                statusEl.innerHTML = '<span style="color:var(--xp-error);">Please enter candidate text first.</span>';
                return;
            }

            statusEl.innerHTML = '<span style="color:var(--xp-orange);">Verifying against mathematical input...</span>';

            fetch('{{ route('ai-reasoning.check-guard') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prediction_id: {{ $prediction?->id ?? 0 }},
                    text: text
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.flagged) {
                    statusEl.innerHTML = `<span style="color:var(--xp-error); font-weight:700;">⚠ FLAGGED:</span> Disallowed numbers detected: <code style="color:#FF4D4D;">${data.disallowed_numbers.join(', ')}</code> (${data.allowed_numbers_count} allowed numbers verified)`;
                } else {
                    statusEl.innerHTML = `<span style="color:var(--xp-ok); font-weight:700;">✓ PASSED:</span> 100% verified against model output (${data.allowed_numbers_count} allowed numbers checked)`;
                }
            })
            .catch(err => {
                statusEl.innerHTML = `<span style="color:var(--xp-error);">Verification failed: ${err.message}</span>`;
            });
        }
    </script>
</x-layouts.app>

