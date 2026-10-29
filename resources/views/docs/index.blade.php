<x-layouts.app title="Technical Documentation & System Manual">
    <div style="padding: 24px 32px; max-width: 1440px; margin: 0 auto;">

        {{-- Top Header --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--xp-orange); box-shadow: 0 0 10px var(--xp-orange);"></span>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--xp-orange); font-weight: 700;">
                        Technical Manual &bull; Architecture &bull; User Guide
                    </span>
                </div>
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 26px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em;">
                    System Documentation &amp; Technical Manual
                </h1>
                <p style="font-size: 13px; color: var(--xp-muted); margin-top: 4px;">
                    Complete reference for data flow, Poisson mathematical modeling, MOTM rating algorithms, and CLI commands.
                </p>
            </div>

            {{-- Quick Links / Status --}}
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('changelog') }}" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px; text-decoration: none;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Model Changelog</span>
                </a>
                <div style="background: rgba(61,220,132,0.1); border: 1px solid rgba(61,220,132,0.3); border-radius: 6px; padding: 6px 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="color:var(--xp-ok); font-size: 14px;">✓</span>
                    <span style="font-size: 12px; font-weight: 600; color: var(--xp-ok);">Zero Data Leakage Certified</span>
                </div>
            </div>
        </div>

        {{-- Quick Jump Navigation Bar --}}
        <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; overflow-x: auto;">
            <span style="font-size: 11px; font-weight: 700; color: var(--xp-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">
                Quick Jump:
            </span>
            <a href="#overview" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; text-decoration: none;">Overview</a>
            <a href="#engine" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; text-decoration: none;">Prediction Engine</a>
            <a href="#formulas" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; text-decoration: none;">Math Formulas</a>
            <a href="#motm" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; text-decoration: none;">MOTM Evaluation</a>
            <a href="#commands" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; text-decoration: none;">CLI Commands</a>
            <a href="#sources" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; text-decoration: none;">API Sources</a>
            <a href="#invariants" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 4px 10px; border-radius: 20px; text-decoration: none;">Core Rules</a>
        </div>

        {{-- Section 1: Overview --}}
        <div id="overview" style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="color: var(--xp-orange); font-size: 18px;">◈</span>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF; margin: 0;">
                    1. System Overview &amp; Architecture
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--xp-muted); line-height: 1.6; margin-bottom: 16px;">
                <strong>Xpect</strong> is a local-only, single-user football prediction and analytics tool built for the top 5 European leagues (Premier League, La Liga, Bundesliga, Serie A, and Ligue 1). It collects verified match data into a local SQLite database, computes objective team strengths, generates win/draw/loss probabilities using a Dixon-Coles adjusted Poisson model, and uses LLMs solely to explain predictions in plain English.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                    <div style="font-size: 11px; font-weight: 700; color: var(--xp-orange); text-transform: uppercase; margin-bottom: 6px;">Local-Only Execution</div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        Runs exclusively on local hardware with local SQLite database. No external tracking, no authentication barriers, no remote cloud dependencies.
                    </p>
                </div>
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                    <div style="font-size: 11px; font-weight: 700; color: var(--xp-ok); text-transform: uppercase; margin-bottom: 6px;">Zero Data Leakage</div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        Features for any match are computed strictly using data dated prior to kickoff (<code style="color:var(--xp-orange);">asOf: CarbonImmutable</code>). Verified by automated integrity audits.
                    </p>
                </div>
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                    <div style="font-size: 11px; font-weight: 700; color: #FFFFFF; text-transform: uppercase; margin-bottom: 6px;">Deterministic Mathematics</div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        The statistical model and PHP algorithms calculate all numbers. The AI explains and never decides. Previews pass an anti-hallucination guard.
                    </p>
                </div>
            </div>
        </div>

        {{-- Section 2: Prediction Engine --}}
        <div id="engine" style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="color: var(--xp-orange); font-size: 18px;">⚙</span>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF; margin: 0;">
                    2. The Prediction Engine: How Probabilities Are Calculated
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--xp-muted); line-height: 1.6; margin-bottom: 16px;">
                Predictions pass through a strict 5-stage deterministic mathematical pipeline:
            </p>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                {{-- Step 1 --}}
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px; border-left: 3px solid var(--xp-orange);">
                    <div style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 4px;">
                        Stage 1: Feature Extraction &amp; Sustainable xG Blending
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        For both teams, the system gathers the last <strong>20 matches</strong> dated strictly before the match kickoff. Match statistics blend <strong>70% expected goals (xG)</strong> with <strong>30% actual goals scored</strong> to remove finishing variance and luck. Past matches are exponentially discounted with a <strong>60-day half-life decay</strong>.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px; border-left: 3px solid var(--xp-orange);">
                    <div style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 4px;">
                        Stage 2: Attacking &amp; Defensive Strength Coefficients
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        Each club receives an offensive strength rating (<code style="color:var(--xp-orange);">Att</code>) and defensive vulnerability rating (<code style="color:var(--xp-orange);">Def</code>), normalized against league-wide goal averages. Home advantage is computed per-club rather than a uniform constant.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px; border-left: 3px solid var(--xp-orange);">
                    <div style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 4px;">
                        Stage 3: Head-to-Head (H2H) Matchup Factor (v1.2.1-h2h)
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        Historical head-to-head encounters between the two clubs are evaluated with an exponential half-life decay. A shrinkage factor (<code style="color:var(--xp-orange);">k = 3.0</code>) pulls sparse head-to-head records smoothly toward team strength baselines.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px; border-left: 3px solid var(--xp-orange);">
                    <div style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 4px;">
                        Stage 4: Dixon-Coles Low-Score Bivariate Correction
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        Standard bivariate Poisson assumes goal independence between teams. Real football exhibits systemic correlation on low scorelines. The model applies the Dixon-Coles correlation adjustment with parameter <code style="color:var(--xp-orange);">&rho; = -0.06</code> on scores 0-0, 1-0, 0-1, and 1-1.
                    </p>
                </div>

                {{-- Step 5 --}}
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px; border-left: 3px solid var(--xp-orange);">
                    <div style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-bottom: 4px;">
                        Stage 5: 6x6 Matrix Integration &amp; 1X2 Probabilities
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); margin: 0; line-height: 1.5;">
                        The 6x6 scoreline probability matrix ($0..5 \times 0..5$) is computed and normalized to exactly 1.000. Summing upper triangles yields <strong style="color:var(--xp-orange);">P(Home Win)</strong>, the diagonal yields <strong style="color:#FFFFFF;">P(Draw)</strong>, and lower triangles yield <strong style="color:var(--xp-away);">P(Away Win)</strong>.
                    </p>
                </div>
            </div>
        </div>

        {{-- Section 3: Mathematical Formulations --}}
        <div id="formulas" style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="color: var(--xp-orange); font-size: 18px;">∑</span>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF; margin: 0;">
                    3. Mathematical Formulations
                </h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 16px;">
                    <strong style="color: var(--xp-orange); font-size: 13px; display: block; margin-bottom: 6px;">Expected Goals Rate Parameters (&lambda;)</strong>
                    <div style="font-family: monospace; font-size: 12.5px; background: #000000; padding: 10px; border-radius: 4px; border: 1px solid var(--xp-border-subtle); color: #FFFFFF; margin-bottom: 8px;">
                        &lambda;<sub>H</sub> = Att<sub>H</sub> &times; Def<sub>A</sub> &times; &mu;<sub>H</sub><br>
                        &lambda;<sub>A</sub> = Att<sub>A</sub> &times; Def<sub>H</sub> &times; &mu;<sub>A</sub>
                    </div>
                    <span style="font-size: 11.5px; color: var(--xp-muted); line-height: 1.4;">
                        Where &mu;<sub>H</sub> is average home scoring rate and &mu;<sub>A</sub> is average away scoring rate.
                    </span>
                </div>

                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 16px;">
                    <strong style="color: var(--xp-orange); font-size: 13px; display: block; margin-bottom: 6px;">Dixon-Coles Low Score Adjustment (&tau;)</strong>
                    <div style="font-family: monospace; font-size: 12.5px; background: #000000; padding: 10px; border-radius: 4px; border: 1px solid var(--xp-border-subtle); color: #FFFFFF; margin-bottom: 8px;">
                        &tau;(0, 0) = 1 &minus; &lambda;<sub>H</sub>&lambda;<sub>A</sub>&rho;<br>
                        &tau;(1, 0) = 1 + &lambda;<sub>A</sub>&rho;<br>
                        &tau;(0, 1) = 1 + &lambda;<sub>H</sub>&rho;<br>
                        &tau;(1, 1) = 1 &minus; &rho;
                    </div>
                    <span style="font-size: 11.5px; color: var(--xp-muted); line-height: 1.4;">
                        Calibrated with &rho; = -0.06 to capture mutual tactical caution in scoreless and 1-1 draws.
                    </span>
                </div>

                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 16px;">
                    <strong style="color: var(--xp-orange); font-size: 13px; display: block; margin-bottom: 6px;">Exponential Time Decay Weight</strong>
                    <div style="font-family: monospace; font-size: 12.5px; background: #000000; padding: 10px; border-radius: 4px; border: 1px solid var(--xp-border-subtle); color: #FFFFFF; margin-bottom: 8px;">
                        w<sub>i</sub> = exp(&minus; &Delta;t &times; ln(2) / 60)
                    </div>
                    <span style="font-size: 11.5px; color: var(--xp-muted); line-height: 1.4;">
                        Weights diminish by 50% every 60 days to reflect ongoing team form and momentum shifts.
                    </span>
                </div>

                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 16px;">
                    <strong style="color: var(--xp-orange); font-size: 13px; display: block; margin-bottom: 6px;">Bivariate Poisson Distribution</strong>
                    <div style="font-family: monospace; font-size: 12.5px; background: #000000; padding: 10px; border-radius: 4px; border: 1px solid var(--xp-border-subtle); color: #FFFFFF; margin-bottom: 8px;">
                        P(x, y) = &tau;(x, y) &times; (&lambda;<sub>H</sub><sup>x</sup> e<sup>&minus;&lambda;<sub>H</sub></sup> / x!) &times; (&lambda;<sub>A</sub><sup>y</sup> e<sup>&minus;&lambda;<sub>A</sub></sup> / y!)
                    </div>
                    <span style="font-size: 11.5px; color: var(--xp-muted); line-height: 1.4;">
                        Generates the complete scoreline grid normalized across all possible outcomes.
                    </span>
                </div>
            </div>
        </div>

        {{-- Section 4: MOTM Engine Guide --}}
        <div id="motm" style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="color: var(--xp-orange); font-size: 18px;">★</span>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF; margin: 0;">
                    4. Man of the Match (MOTM) Evaluation Engine
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--xp-muted); line-height: 1.6; margin-bottom: 16px;">
                Man of the Match recognition is governed by the <strong>"Stats Question vs. Story Question"</strong> dual-engine framework:
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span style="color: var(--xp-orange); font-size: 16px;">📊</span>
                        <strong style="color: var(--xp-orange); font-size: 13px; text-transform: uppercase;">1. The Stats Question: Data Rating (0 to 10)</strong>
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); line-height: 1.5; margin: 0;">
                        Pure Opta / SofaScore / WhoScored action scores measuring total 90-minute on-pitch output: key passes, chances created, clearances, tackles, passing accuracy, xG, and clean sheets. <em>The truest measure of performance.</em>
                    </p>
                </div>

                <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span style="color: var(--xp-ok); font-size: 16px;">🏆</span>
                        <strong style="color: var(--xp-ok); font-size: 13px; text-transform: uppercase;">2. The Story Question: Award Decision Score (0 to 10)</strong>
                    </div>
                    <p style="font-size: 12px; color: var(--xp-muted); line-height: 1.5; margin: 0;">
                        Incorporates real-world broadcaster and panel award biases: winning team bonus (+0.9), match-winning goals (+1.1), braces (+1.4), hat-tricks (+2.2), and goalkeepers under heavy pressure (+1.2). <em>The defining moment of the fixture.</em>
                    </p>
                </div>
            </div>

            <div style="background: rgba(217,78,0,0.06); border: 1px solid rgba(217,78,0,0.2); border-radius: 8px; padding: 14px 16px; font-size: 12px; color: var(--xp-text); line-height: 1.5;">
                <strong style="color:var(--xp-orange);">Guaranteed Single-Winner Constraint:</strong> Exactly ONE player per fixture receives <code style="color:var(--xp-orange);">is_motm = true</code>. Enforced by a deterministic 6-tier tie-breaking algorithm and atomic database transaction across all 321 evaluated Season 2026 matches.
            </div>
        </div>

        {{-- Section 5: CLI Commands Reference --}}
        <div id="commands" style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="color: var(--xp-orange); font-size: 18px;">⌨</span>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF; margin: 0;">
                    5. Artisan CLI Command Reference
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--xp-muted); line-height: 1.6; margin-bottom: 16px;">
                All tool operations can be invoked directly from the terminal or through the web UI:
            </p>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 280px;">Artisan Command</th>
                            <th>Description</th>
                            <th>Example Invocation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commands as $cmd)
                            <tr>
                                <td>
                                    <code style="font-family: monospace; font-size: 12px; color: var(--xp-orange); font-weight: 700;">
                                        {{ $cmd['command'] }}
                                    </code>
                                </td>
                                <td style="font-size: 12px; color: var(--xp-text);">
                                    {{ $cmd['desc'] }}
                                </td>
                                <td>
                                    <code style="font-family: monospace; font-size: 11px; color: var(--xp-muted); background: #000000; padding: 3px 6px; border-radius: 4px; border: 1px solid var(--xp-border-subtle);">
                                        {{ $cmd['example'] }}
                                    </code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 6: API Catalog & Data Sources --}}
        <div id="sources" style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="color: var(--xp-orange); font-size: 18px;">☁</span>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF; margin: 0;">
                    6. Data Sources &amp; Outbound API Catalog
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--xp-muted); line-height: 1.6; margin-bottom: 16px;">
                Every outbound HTTP call is routed through <code style="color:var(--xp-orange);">LoggedHttpClient</code> and saved in full to <code style="color:var(--xp-orange);">api_calls</code>. Zero secrets or API keys are ever stored.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                @foreach ($apiSources as $src)
                    <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                        <strong style="color: #FFFFFF; font-size: 14px; font-family: 'Space Grotesk', sans-serif; display: block; margin-bottom: 4px;">
                            {{ $src['name'] }}
                        </strong>
                        <div style="font-size: 11px; color: var(--xp-orange); font-weight: 600; margin-bottom: 6px;">
                            {{ $src['role'] }}
                        </div>
                        <div style="font-size: 11px; color: var(--xp-muted); margin-bottom: 4px;">
                            <strong>Rate Limit:</strong> {{ $src['quota'] }}
                        </div>
                        <div style="font-size: 11px; color: var(--xp-away); line-height: 1.4;">
                            {{ $src['storage'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 7: Standing Rules & Invariants --}}
        <div id="invariants" style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="color: var(--xp-orange); font-size: 18px;">🛡</span>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF; margin: 0;">
                    7. Standing System Invariants (Hard Rules)
                </h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; font-size: 12px;">
                <div style="display: flex; align-items: flex-start; gap: 8px; background: #08080A; padding: 12px; border-radius: 6px; border: 1px solid var(--xp-border);">
                    <span style="color: var(--xp-ok); font-size: 14px;">1.</span>
                    <div>
                        <strong style="color: #FFFFFF;">No Data Leakage:</strong>
                        <div style="color: var(--xp-muted); margin-top: 2px;">Features for any fixture take (team, asOf: CarbonImmutable). No match on or after kickoff can ever enter the calculation.</div>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 8px; background: #08080A; padding: 12px; border-radius: 6px; border: 1px solid var(--xp-border);">
                    <span style="color: var(--xp-ok); font-size: 14px;">2.</span>
                    <div>
                        <strong style="color: #FFFFFF;">Immutable Predictions:</strong>
                        <div style="color: var(--xp-muted); margin-top: 2px;">Predictions and API call logs are append-only. Never UPDATE or DELETE historical rows. Model upgrades bump model_version.</div>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 8px; background: #08080A; padding: 12px; border-radius: 6px; border: 1px solid var(--xp-border);">
                    <span style="color: var(--xp-ok); font-size: 14px;">3.</span>
                    <div>
                        <strong style="color: #FFFFFF;">Grounded AI Previews:</strong>
                        <div style="color: var(--xp-muted); margin-top: 2px;">The LLM only explains computed numbers in plain English; it never decides predictions or introduces outside stats.</div>
                    </div>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 8px; background: #08080A; padding: 12px; border-radius: 6px; border: 1px solid var(--xp-border);">
                    <span style="color: var(--xp-ok); font-size: 14px;">4.</span>
                    <div>
                        <strong style="color: #FFFFFF;">Zero HTTP Replay Mode:</strong>
                        <div style="color: var(--xp-muted); margin-top: 2px;">All parsing bugs can be reproduced and re-tested directly from api_calls without spending live API quota.</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>

