<x-layouts.app title="Model Changelog & Technical Documentation">
    <div style="padding: 24px 32px; max-width: 1440px; margin: 0 auto;">

        {{-- Top Header --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--xp-orange); box-shadow: 0 0 10px var(--xp-orange);"></span>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--xp-orange); font-weight: 700;">
                        System Provenance &bull; Model Versioning
                    </span>
                </div>
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 26px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.02em;">
                    Model Changelog &amp; Technical Documentation
                </h1>
                <p style="font-size: 13px; color: var(--xp-muted); margin-top: 4px;">
                    Walk-forward benchmarks, mathematical derivations, Dixon-Coles parameters, and version history.
                </p>
            </div>

            {{-- Integrity Badges --}}
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div style="background: rgba(61,220,132,0.1); border: 1px solid rgba(61,220,132,0.3); border-radius: 6px; padding: 6px 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="color:var(--xp-ok); font-size: 14px;">✓</span>
                    <span style="font-size: 12px; font-weight: 600; color: var(--xp-ok);">Zero Data Leakage Proven</span>
                </div>
                <div style="background: rgba(217, 78, 0, 0.12); border: 1px solid rgba(217, 78, 0, 0.35); border-radius: 6px; padding: 6px 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="color:var(--xp-orange); font-size: 12px; font-weight: 700;">ACTIVE</span>
                    <span style="font-size: 12px; font-weight: 600; color: #FFFFFF;">{{ $activeModel['version'] }}</span>
                </div>
            </div>
        </div>

        {{-- Active Model Spotlight Hero Card --}}
        <div style="background: linear-gradient(135deg, rgba(217,78,0,0.12) 0%, rgba(12,12,14,0.98) 100%); border: 1px solid rgba(217,78,0,0.35); border-radius: 12px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span class="badge" style="background: var(--xp-orange); color: #000000; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                            CURRENT PRODUCTION ENGINE
                        </span>
                        <span style="color: var(--xp-muted); font-size: 12px;">Released {{ $activeModel['released_at'] }}</span>
                    </div>
                    <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 22px; font-weight: 700; color: #FFFFFF; margin: 0;">
                        {{ $activeModel['version'] }}: {{ $activeModel['name'] }}
                    </h2>
                    <p style="color: var(--xp-muted); font-size: 13px; margin-top: 6px; max-width: 820px; line-height: 1.5;">
                        Combines Dixon-Coles bivariate low-scoring correlation adjustments with recency-weighted head-to-head historical matchup factors, evaluated against 20-match rolling sustainable xG profiles.
                    </p>
                </div>

                {{-- Live Quick Stats --}}
                <div style="display: flex; gap: 16px;">
                    <div style="background: rgba(0,0,0,0.5); border: 1px solid var(--xp-border); border-radius: 8px; padding: 10px 16px; text-align: center;">
                        <div style="font-size: 10px; color: var(--xp-muted); text-transform: uppercase; font-weight: 600;">Accuracy</div>
                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 20px; font-weight: 700; color: var(--xp-ok);">54.5%</div>
                    </div>
                    <div style="background: rgba(0,0,0,0.5); border: 1px solid var(--xp-border); border-radius: 8px; padding: 10px 16px; text-align: center;">
                        <div style="font-size: 10px; color: var(--xp-muted); text-transform: uppercase; font-weight: 600;">Log Loss (&darr;)</div>
                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 20px; font-weight: 700; color: var(--xp-orange);">0.9704</div>
                    </div>
                    <div style="background: rgba(0,0,0,0.5); border: 1px solid var(--xp-border); border-radius: 8px; padding: 10px 16px; text-align: center;">
                        <div style="font-size: 10px; color: var(--xp-muted); text-transform: uppercase; font-weight: 600;">Brier Score (&darr;)</div>
                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 20px; font-weight: 700; color: #FFFFFF;">0.5759</div>
                    </div>
                </div>
            </div>

            {{-- 4 Core Parameter Cards --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                <div style="background: rgba(0,0,0,0.45); border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 14px;">
                    <span style="font-size: 11px; color: var(--xp-muted); display: block; margin-bottom: 4px;">Dixon-Coles &rho; Factor</span>
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: var(--xp-orange);">
                        {{ $activeModel['dixon_coles_rho'] }}
                    </div>
                    <span style="font-size: 11px; color: var(--xp-away);">Adjusts 0-0, 1-0, 0-1, 1-1 scores</span>
                </div>

                <div style="background: rgba(0,0,0,0.45); border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 14px;">
                    <span style="font-size: 11px; color: var(--xp-muted); display: block; margin-bottom: 4px;">Form Rolling Window</span>
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF;">
                        {{ $activeModel['window_matches'] }} Matches
                    </div>
                    <span style="font-size: 11px; color: var(--xp-away);">Strictly before kickoff (Zero Leakage)</span>
                </div>

                <div style="background: rgba(0,0,0,0.45); border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 14px;">
                    <span style="font-size: 11px; color: var(--xp-muted); display: block; margin-bottom: 4px;">Exponential Half-Life</span>
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: #FFFFFF;">
                        {{ $activeModel['half_life_days'] }} Days
                    </div>
                    <span style="font-size: 11px; color: var(--xp-away);">&lambda; = ln(2) / 60 decay factor</span>
                </div>

                <div style="background: rgba(0,0,0,0.45); border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 14px;">
                    <span style="font-size: 11px; color: var(--xp-muted); display: block; margin-bottom: 4px;">Sustainable xG Blend</span>
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; font-weight: 700; color: var(--xp-ok);">
                        70% xG + 30% Goals
                    </div>
                    <span style="font-size: 11px; color: var(--xp-away);">Filters out finishing luck</span>
                </div>
            </div>
        </div>

        {{-- 2-Column Section: Model Version Evolution vs. Mathematical Architecture --}}
        <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px; margin-bottom: 24px;">

            {{-- Column 1: Model Version Evolution & Benchmarks --}}
            <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 17px; font-weight: 700; color: #FFFFFF; margin: 0;">
                            Model Version History &amp; Benchmarks
                        </h3>
                        <p style="font-size: 12px; color: var(--xp-muted); margin-top: 2px;">
                            Walk-forward validation on held-out seasons. Every feature change triggers a version bump.
                        </p>
                    </div>
                    <a href="{{ route('track-record') }}" class="btn btn-secondary btn-sm" style="font-size: 11px; text-decoration: none;">
                        <span>Live Track Record</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach ($modelVersions as $m)
                        <div style="background: #08080A; border: 1px solid {{ $m['status'] === 'active' ? 'rgba(217,78,0,0.4)' : 'var(--xp-border)' }}; border-radius: 8px; padding: 14px 16px; border-left: 3px solid {{ $m['status'] === 'active' ? 'var(--xp-orange)' : 'var(--xp-away)' }};">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <strong style="color: {{ $m['status'] === 'active' ? 'var(--xp-orange)' : '#FFFFFF' }}; font-size: 14px; font-family: 'Space Grotesk', sans-serif;">
                                            {{ $m['version'] }}
                                        </strong>
                                        <span style="font-size: 13px; font-weight: 600; color: #FFFFFF;">&bull; {{ $m['title'] }}</span>
                                        @if ($m['status'] === 'active')
                                            <span style="background: var(--xp-orange); color: #000000; font-size: 9px; font-weight: 800; padding: 1px 6px; border-radius: 3px;">
                                                ACTIVE
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; color: var(--xp-muted); margin-top: 2px;">
                                        Released {{ $m['date'] }}
                                    </div>
                                </div>

                                {{-- Metrics Badge --}}
                                <div style="display: flex; gap: 10px; font-size: 11px;">
                                    <span style="color: var(--xp-ok); font-weight: 700;">{{ $m['accuracy'] }} Acc</span>
                                    <span style="color: var(--xp-muted);">LogLoss: <strong style="color: #FFFFFF;">{{ $m['log_loss'] }}</strong></span>
                                    <span style="color: var(--xp-muted);">Brier: <strong style="color: #FFFFFF;">{{ $m['brier'] }}</strong></span>
                                </div>
                            </div>

                            <p style="font-size: 12px; color: var(--xp-muted); line-height: 1.45; margin: 6px 0 10px 0;">
                                {{ $m['summary'] }}
                            </p>

                            <ul style="margin: 0; padding-left: 18px; font-size: 11.5px; color: #CCCCCC; line-height: 1.4;">
                                @foreach ($m['highlights'] as $highlight)
                                    <li>{{ $highlight }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                {{-- Baseline Comparison Table --}}
                <div style="margin-top: 20px;">
                    <div style="font-size: 12px; font-weight: 700; color: #FFFFFF; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
                        Comparison vs. Standard Statistical Baselines
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Model / Baseline</th>
                                    <th style="text-align: center;">Accuracy</th>
                                    <th style="text-align: center;">Multi-Class Log Loss (&darr;)</th>
                                    <th style="text-align: center;">Brier Score (&darr;)</th>
                                    <th>Status / Rule</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background: rgba(217, 78, 0, 0.08); font-weight: 700;">
                                    <td style="color: var(--xp-orange);">
                                        <strong>v1.2.1-h2h (Current Engine)</strong>
                                    </td>
                                    <td style="text-align: center; color: var(--xp-ok);">54.5%</td>
                                    <td style="text-align: center; color: var(--xp-orange);">0.9704</td>
                                    <td style="text-align: center;">0.5759</td>
                                    <td><span class="badge" style="background: var(--xp-orange); color: #000000; font-weight: 700;">ACTIVE</span></td>
                                </tr>
                                @foreach ($baselines as $b)
                                    <tr style="color: var(--xp-muted);">
                                        <td>{{ $b['name'] }}</td>
                                        <td style="text-align: center;">{{ $b['accuracy'] }}</td>
                                        <td style="text-align: center;">{{ $b['log_loss'] }}</td>
                                        <td style="text-align: center;">{{ $b['brier'] }}</td>
                                        <td style="font-size: 11px; color: var(--xp-away);">{{ $b['notes'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Column 2: Mathematical Derivations & Architecture --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">

                {{-- Mathematical Formula Breakdown --}}
                <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 22px;">
                    <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 17px; font-weight: 700; color: #FFFFFF; margin: 0 0 4px 0;">
                        Mathematical Architecture
                    </h3>
                    <p style="font-size: 12px; color: var(--xp-muted); margin-bottom: 16px;">
                        Exact statistical formulations implemented in PHP with zero outside dependencies.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        {{-- Formula 1: Expected Goals Rate Parameters --}}
                        <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 14px;">
                            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--xp-orange); margin-bottom: 4px;">
                                1. Expected Goals Rate Parameters (&lambda;)
                            </div>
                            <div style="font-family: monospace; font-size: 13px; color: #FFFFFF; background: #000000; padding: 8px 10px; border-radius: 4px; margin: 6px 0; border: 1px solid var(--xp-border-subtle);">
                                &lambda;<sub>Home</sub> = Att<sub>Home</sub> &times; Def<sub>Away</sub> &times; &mu;<sub>Home</sub><br>
                                &lambda;<sub>Away</sub> = Att<sub>Away</sub> &times; Def<sub>Home</sub> &times; &mu;<sub>Away</sub>
                            </div>
                            <p style="font-size: 11.5px; color: var(--xp-muted); margin: 0; line-height: 1.4;">
                                Attacking and defensive coefficients calculated over a 20-match rolling window normalized against the league-wide average scoring rate (&mu;).
                            </p>
                        </div>

                        {{-- Formula 2: Dixon-Coles Low-Score Matrix --}}
                        <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 14px;">
                            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--xp-orange); margin-bottom: 4px;">
                                2. Dixon-Coles Correlation Matrix (&tau;)
                            </div>
                            <div style="font-family: monospace; font-size: 12.5px; color: #FFFFFF; background: #000000; padding: 8px 10px; border-radius: 4px; margin: 6px 0; border: 1px solid var(--xp-border-subtle);">
                                P(x, y) = &tau;<sub>&rho;</sub>(x, y) &times; Poisson(x, &lambda;<sub>H</sub>) &times; Poisson(y, &lambda;<sub>A</sub>)<br>
                                &tau;<sub>-0.06</sub>(0, 0) = 1 - &lambda;<sub>H</sub>&lambda;<sub>A</sub>&rho; = 1 - 0.06&lambda;<sub>H</sub>&lambda;<sub>A</sub>
                            </div>
                            <p style="font-size: 11.5px; color: var(--xp-muted); margin: 0; line-height: 1.4;">
                                Applies low-score correlation parameter (&rho; = -0.06) to accurately adjust for 0-0, 1-0, 0-1, and 1-1 scoreline dependencies.
                            </p>
                        </div>

                        {{-- Formula 3: Exponential Decay Weighting --}}
                        <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 12px 14px;">
                            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--xp-orange); margin-bottom: 4px;">
                                3. Exponential Time Decay Function
                            </div>
                            <div style="font-family: monospace; font-size: 13px; color: #FFFFFF; background: #000000; padding: 8px 10px; border-radius: 4px; margin: 6px 0; border: 1px solid var(--xp-border-subtle);">
                                w<sub>i</sub> = exp(&minus; &Delta;t &times; ln(2) / 60)
                            </div>
                            <p style="font-size: 11.5px; color: var(--xp-muted); margin: 0; line-height: 1.4;">
                                Halves the statistical weight of past matches every 60 days to prioritize current form while preserving sample stability.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Architectural Principles & Standing Rules --}}
                <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 22px;">
                    <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 16px; font-weight: 700; color: #FFFFFF; margin: 0 0 12px 0;">
                        Standing Architectural Guarantees
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12px;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <span style="color: var(--xp-ok); font-size: 14px; line-height: 1;">✓</span>
                            <div>
                                <strong style="color: #FFFFFF;">Zero Data Leakage:</strong>
                                <span style="color: var(--xp-muted);">All team features take (team, asOf: CarbonImmutable). No matches dated on or after kickoff can ever enter the feature vector.</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <span style="color: var(--xp-ok); font-size: 14px; line-height: 1;">✓</span>
                            <div>
                                <strong style="color: #FFFFFF;">Immutable Predictions:</strong>
                                <span style="color: var(--xp-muted);">Predictions are write-once, never updated or deleted. Model improvements trigger a version bump.</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <span style="color: var(--xp-ok); font-size: 14px; line-height: 1;">✓</span>
                            <div>
                                <strong style="color: #FFFFFF;">Deterministic Mathematics:</strong>
                                <span style="color: var(--xp-muted);">The LLM never calculates numbers or decides probabilities. It only writes human-readable previews from structured data.</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <span style="color: var(--xp-ok); font-size: 14px; line-height: 1;">✓</span>
                            <div>
                                <strong style="color: #FFFFFF;">Local-Only &amp; Zero Quota Replay:</strong>
                                <span style="color: var(--xp-muted);">Every HTTP call is logged in full to api_calls. Replays and backtests execute with zero network calls.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Application Release Log (Recent Releases) --}}
        <div style="background: var(--xp-surface); border: 1px solid var(--xp-border); border-radius: 10px; padding: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 17px; font-weight: 700; color: #FFFFFF; margin: 0;">
                        Application Release History
                    </h3>
                    <p style="font-size: 12px; color: var(--xp-muted); margin-top: 2px;">
                        Notable product updates and feature milestones from CHANGELOG.md.
                    </p>
                </div>
                <div style="font-size: 11px; color: var(--xp-away);">
                    Latest: {{ $recentReleases[0]['tag'] }} &bull; {{ $recentReleases[0]['date'] }}
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                @foreach ($recentReleases as $release)
                    <div style="background: #08080A; border: 1px solid var(--xp-border); border-radius: 8px; padding: 14px 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span class="badge" style="background: rgba(217,78,0,0.15); color: var(--xp-orange); font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                                {{ $release['badge'] }}
                            </span>
                            <span style="font-size: 11px; color: var(--xp-away);">{{ $release['date'] }}</span>
                        </div>
                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 14px; font-weight: 700; color: #FFFFFF; margin-bottom: 4px;">
                            {{ $release['tag'] }} &bull; {{ $release['title'] }}
                        </div>
                        <p style="font-size: 12px; color: var(--xp-muted); line-height: 1.45; margin: 0;">
                            {{ $release['description'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-layouts.app>

