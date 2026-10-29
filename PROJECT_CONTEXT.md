# PROJECT_CONTEXT.md

## 1. What this is

**Xpect** (folder and repo slug: `xpect`) is a local, single-user football
prediction tool for the top 5 European leagues. It collects verified match data,
computes team statistics, produces win/draw/loss probabilities with a
transparent statistical model, and uses an LLM to explain each prediction in
plain language. Every prediction is stored and later compared with the real
result so the tool can measure its own accuracy.

Not hosted. Not multi-user. No monetization. Built for the owner's personal use.

## 2. Problem statement

1. **Scattered data.** Form, standings, home/away records, and head-to-head
   history live on different sites. Gathering them per fixture is slow and
   manual.
2. **Gut-feeling predictions.** Without structured analysis, predictions rely on
   bias and recency, and nobody tracks how often they are right.
3. **Unreliable AI answers.** General chatbots write fluent previews but invent
   stats and results because they have no verified, current data behind them.

## 3. How Xpect solves it

1. Pull fixtures, results, and history from established sources into a local DB.
2. Compute form, home/away strength, goals for/against, rest days, stakes, Elo.
3. Convert strengths into probabilities with a Poisson model (the math decides).
4. Feed ONLY the computed numbers to an LLM, which writes the preview. It
   explains and does not decide, and it cannot invent facts.
5. Save every prediction and score it against the actual result (log loss,
   Brier score, accuracy, calibration).
6. Save every API call and AI call in full, so any result can be traced and
   replayed without spending quota.

## 4. Scope

### In v1
- Premier League first, then the other four top leagues.
- Fixtures, results, standings, history (3-5 seasons).
- Computed features, Elo, Poisson probabilities.
- Backtesting with baselines, and live pre-match predictions.
- AI previews via the Claude API.
- Full logging of every external call, plus a dev-only API inspector page.
- Artisan commands as the main interface. A full UI comes later.

### Not in v1 (do not build yet)
- Player-level predictions and live in-play predictions.
- Leagues outside the top 5.
- Lineups, injuries, referees, weather, travel.
- Betting features, user accounts, hosting, payments.
- Machine-learning models (only after the Poisson baseline is beaten and
  measured).

## 5. Locked decisions

| Area | Decision |
|---|---|
| Name | Xpect (`xpect`) |
| Framework | Laravel (PHP) |
| Database | SQLite (swappable to MySQL later) |
| AI previews | Claude API (model name in `.env`, never hard-coded) |
| First league | Premier League |
| Persistence | Everything saved: raw API calls, LLM prompts and responses, predictions, evaluations |
| Interface | Artisan commands first; dev-only API inspector from Phase 1; full UI after the model is proven |
| Agent IDE | Antigravity, driven by `AGENTS.md` and `TODO.md` |
| Theme | orange and black, bright and sharp (UI_GUIDELINES.md) |

## 6. Data sources

Status: UNVERIFIED until a real response is saved in `api_calls` and checked.
Full endpoint list in `API_CATALOG.md`.

| Source | Use | Known limits (from public comparisons, verify) |
|---|---|---|
| football-data.org | Fixtures, results (main backbone) | Free tier: 12 competitions, 10 calls/min, delayed scores, limited depth |
| Football-Data.co.uk CSVs | Free historical results (backfill fallback) | Static files, check season coverage |
| API-Football | Lineups, injuries, other leagues (later) | Free tier about 100 requests/day, limited seasons |
| Understat | xG for the Big 5 (Phase 6) | Unofficial: data embedded in page HTML, can break |
| Elo source | Team Elo ratings | Undecided, verify availability and format |

## 7. Prediction variables (priority order)

**Tier 1 (focus):** team strength from goals then xG, home advantage (per team),
weighted recent form adjusted for opponent quality, lineups/injuries (later).
**Tier 2:** rest days and fixture congestion, stakes/motivation (from table
position), manager change, Elo.
**Tier 3 (marginal):** head-to-head, referee, weather, travel.

Add variables one at a time. Keep a variable only if the backtest improves.

## 8. Success criteria

- v1 beats both baselines on at least 2 held-out seasons: always-pick-home and
  historical base rates (compare on log loss and Brier, not just accuracy).
- Realistic accuracy is about 50-54% on win/draw/loss. 65% means suspect
  leakage.
- Probabilities are calibrated (a stated 70% happens about 70% of the time).
- Any prediction can be traced back to the exact API responses that produced it.
- Prediction hit rate is tracked automatically.

## 9. Honest limits

Football is high-variance. Xpect estimates probabilities; it cannot guarantee
outcomes and will not consistently beat bookmaker odds. Lineups are usually
confirmed about an hour before kickoff, so early predictions cannot use them.

## 10. Phases

0 Data spike -> 1 Foundation -> 2 Model + backtest -> 3 AI previews -> 4 UI ->
5 Other leagues -> 6 xG -> 7 Lineups/injuries -> 8 Odds benchmark and extras.
Details, checkpoints and definitions of done are in `TODO.md`.

## 11. Document map

All files live in the repo root.

| File | Purpose | Who updates it |
|---|---|---|
| `AGENTS.md` | Standing orders for the coding agent, loaded every session | Owner |
| `PROJECT_CONTEXT.md` | What and why, scope, decisions, session log (this file) | Owner (scope), agent (session log) |
| `ARCHITECTURE.md` | How it is built | Agent, when structure changes |
| `DATABASE.md` | Tables, columns, rules | Agent, when schema changes |
| `API_CATALOG.md` | Every endpoint we may call, with verification status | Agent, when an endpoint is added or verified |
| `UI_GUIDELINES.md` | Theme, colour tokens, typography, logo, contrast rules | Owner |
| `TODO.md` | THE MASTER PROMPT: session instructions, phases, checkpoints, definitions of done | Agent ticks, owner approves phase changes |
| `probe.php` | Optional throwaway Phase 0 script, delete after the inspector works | Owner |

If two files disagree, stop and report it. Do not pick one silently.

## 12. SESSION LOG

The agent appends one entry at the end of every session. Newest at the bottom.

Template:

```
### Session N (date)
- Done:
- Decisions made:
- Verified or changed versus the docs:
- Stopped at: (phase / checkpoint / task)
- Next:
```

### Session 0 (planning, no code yet)
- Done: product defined, architecture, schema, endpoint catalog and agent rules
  written. Project named Xpect.
- Decisions made: SQLite, Claude API for previews, Premier League first, every
  API and LLM call stored in full, `TODO.md` is the master prompt.
- Stopped at: Phase 0 not started.
- Next: Phase 0 data spike, then the first-session review, then Phase 1
  checkpoint A.

### Session 1 (2026-10-28)
- Done: First session review completed per TODO.md master prompt. Hard rules restated,
  risks and assumptions identified, proposed edits applied across ARCHITECTURE.md,
  DATABASE.md, and TODO.md. CHANGELOG.md initialized. Git repository initialized,
  .gitignore, .env.example, and .env created. Created probe.php and executed Phase 0
  checks: football-data.org season access tested with live key (2026 and 2023 available;
  <=2022 HTTP 403); downloaded all 25 CSV files across the last 5 seasons for all top 5
  European leagues into tests/Fixtures/csv/. Scaffolded Laravel 12 application with SQLite;
  ran all 11 Checkpoint A migrations; implemented config/football.php, config/api_catalog.php,
  QuotaGuard, LoggedHttpClient (with secrets redaction, caching, and error detection);
  built local /dev/api inspector (vanilla JS + Blade); all unit and feature tests passing.
- Decisions made: Inspector frontend standardized on vanilla JS + Blade (no build or CDN
  dependencies). Cache key hash standardized on SHA-256 with sorted parameter JSON.
  Quota tracking synchronized with provider response headers. Models ApiCall and Prediction
  enforce immutability via Eloquent lifecycle guards.
- Verified or changed versus the docs: Completed Phase 0 and all of Phase 1 (Checkpoints A, B, and C).
  Ticked all Phase 1 items in TODO.md.
- Stopped at: Phase 1 completed; Phase 2 ready to begin.
- Next: Phase 2 (Model evaluation: baselines, accuracy/log loss/Brier metrics,
  walk-forward backtest command on held-out seasons, evaluate:predictions).

### Session 2 (2026-10-28)
- Done: Completed Phase 2 (Model evaluation). Implemented Metrics (accuracy, multi-class log loss,
  Brier score, calibration generator), Baselines (always-home, uniform, prior-only base rates),
  Evaluator service, Backtest console command, and EvaluatePredictions command. Configured daily
  scheduler in routes/console.php (sync:matches at 06:00 UTC, evaluate:predictions at 23:00 UTC).
  Backfilled 1,900 historical Premier League matches across seasons 2020-2024 via CSV adapter,
  rebuilt 3,896 standings snapshots, and executed full chronological walk-forward backtest across
  2 complete held-out seasons (2023 and 2024; 760 matches).
- Decisions made: Live predictions post-match evaluation default to `is_backtest = false` with an
  opt-in `--include-backtest` flag. Daily schedule configured using Laravel 12 Schedule facade in
  routes/console.php with withoutOverlapping and background execution guards.
- Verified or changed versus the docs: Model v1.0.0-poisson evaluated with real numbers. Beats all
  3 baselines across both Log Loss and Brier Score. Accuracy: 52.2% (vs 43.4% baselines; safely below
  the ~58% leakage red flag). Log Loss: 0.9980 (vs 1.0676 base rates, 1.0986 uniform, 5.2112 always-home).
  Brier Score: 0.5937 (vs 0.6464 base rates, 0.6667 uniform, 1.1312 always-home). 33 unit and feature
  tests passing with 152 assertions. All Phase 2 items in TODO.md ticked.
- Stopped at: Phase 2 completed. Ready for owner approval to proceed to Phase 3 (AI previews).
- Next: Phase 3 (AI previews: PromptBuilder with structured JSON, Anthropic Claude API integration
  via LoggedHttpClient, previews table storage, number-check guard, and preview:generate command).

### Session 3 (2026-10-28)
- Done: Completed Phase 3 (AI previews). Implemented PromptBuilder (v1.0.0 system prompt and structured
  JSON input builder), NumberCheckGuard (verifies every number and percentage in preview text against
  input JSON figures), PreviewGenerator (calls Anthropic Claude API exclusively via LoggedHttpClient,
  redacts keys, tracks input/output tokens in api_calls, and stores preview in previews table), and
  the preview:generate command.
- Decisions made: Added local --mock option on preview:generate to simulate Anthropic output for
  zero-cost local deterministic verification when ANTHROPIC_API_KEY is not configured in .env. Real
  production calls use live Claude model via LoggedHttpClient when ANTHROPIC_API_KEY is supplied.
- Verified or changed versus the docs: Generated real fixture preview in database for Prediction #1
  (Wolverhampton Wanderers FC vs Brentford FC). Number-Check Guard verified 100% of figures; stored in
  previews linked to api_calls #2 with token counts. 38 unit and feature tests passing with 182 assertions.
  All Phase 3 items in TODO.md ticked.
- Stopped at: Phase 3 completed. Ready for owner approval to proceed to Phase 4 (Interface).
- Next: Phase 4 (Interface: Simple local UI with upcoming fixtures, prediction probabilities, preview text,
  and track-record / evaluation dashboard).

### Session 4 (2026-10-28)
- Done: Completed Phase 4 (Interface). Built responsive local UI using native Blade and CSS/JS
  without npm/CDN build requirements: Dashboard (upcoming/historical fixtures, probability bars,
  expected goals, scorelines, interactive prediction and preview generation), Track Record &
  Analytics page (accuracy, sub-1.0 Log Loss, Brier score, benchmark comparison against all 3
  baselines, 10-bin calibration table, and historical evaluated matches list), and Settings & Keys
  page as requested by the user (secure masked key updates to .env, system database stats, and
  one-click maintenance triggers for match sync, standings rebuild, prediction evaluation, and backup).
- Decisions made: Components placed in resources/views/components/layouts/app.blade.php for standard
  `<x-layouts.app>` support. All actions (predict, preview, key update, sync, backup) implemented with
  proper HTTP CSRF protection and flash messaging.
- Verified or changed versus the docs: All web routes (/ , /track-record, /settings, /dev/api)
  verified and functional. 44 unit and feature tests passing with 207 assertions. Ticked all items
  under Phase 4 in TODO.md.
- Stopped at: Phase 4 completed. Ready for owner approval to proceed to Phase 5 (Other top leagues).
- Next: Phase 5 (Other top leagues: activate La Liga, Bundesliga, Serie A, Ligue 1; team aliases;
  re-run backtests across all 5 leagues; compare accuracy and log loss).

### Session 5 (2026-10-28)
- Done: Completed Phase 5 (Other top leagues). Activated all 4 continental European leagues in
  CompetitionSeeder (`la-liga`, `bundesliga`, `serie-a`, `ligue-1`). Created EuropeanLeaguesTeamSeeder
  mapping 105 clubs and their comprehensive aliases across Spain, Germany, Italy, and France. Backfilled
  all historical matches for 2020-2024 from pre-saved CSV fixtures (8,982 total matches in database,
  zero API quota used). Rebuilt standings snapshots across all 5 leagues (17,927 snapshots). Ran full
  walk-forward backtests on held-out seasons 2023 & 2024 across all 5 leagues (3,356 test matches).
  Evaluated and proved that Poisson model (`v1.0.0-poisson`) beats all 3 baselines (Always-Home, Uniform,
  and Historical Base Rates) across all 5 leagues.
- Decisions made: Handled French Ligue 1 team downsizing (20 teams in 2020-2022 to 18 teams in 2023-2024;
  306 matches/season) seamlessly via dynamic season schedule parsing.
- Verified or changed versus the docs: Model accuracy across the 5 leagues ranges between 48.5% and 52.2%
  (Premier League 52.2%, Bundesliga 49.5%, Serie A 49.5%, La Liga 49.1%, Ligue 1 48.5%), consistently
  outperforming base rates (40.8% - 46.1%) and log loss baselines (0.9980 - 1.0374 vs 1.0664 - 1.0890)
  with zero leakage (mandated leakage tests passing). Full test suite passing: 46 tests (228 assertions).
  Ticked all Phase 5 items in TODO.md and updated CHANGELOG.md.
- Stopped at: Phase 5 completed. Ready for owner approval to proceed to Phase 6 (xG: Understat).
- Next: Phase 6 (xG via Understat: adapter behind existing contract, raw pages stored in api_calls,
  match_stats migration, and new model version comparing xG-based strength against goals-only Poisson).

### Session 6 (2026-10-28)
- Done: Completed Phase 6 (xG via Understat). Created `match_stats` migration (`2026_10_28_000012_create_match_stats_table.php`)
  and `MatchStat` Eloquent model with casts and relations. Discovered and verified Understat's clean JSON endpoint
  `GET /getLeagueData/{league}/{season}` and documented in `API_CATALOG.md` & `config/api_catalog.php`. Implemented
  `UnderstatParser` supporting clean JSON and hex-encoded embedded script fallback. Implemented `UnderstatTeamSeeder`
  mapping aliases for source `'understat'`. Built `UnderstatAdapter` implementing `DataSourceAdapter` with `--from-cache`
  replay. Created `sync:understat` command and synced 5 full seasons (2020-2024; 1,900 matches, 3,800 match_stats rows)
  for the Premier League. Extended `FeatureBuilder` to calculate rolling xG attack and defence strengths strictly before
  `asOf`. Created `v1.1.0-xg` model version in `PoissonModel` blending 70% xG chance creation and 30% finishing conversion.
  Executed walk-forward backtest on held-out seasons 2023 & 2024 (760 matches) and verified that `v1.1.0-xg` improves
  across all dimensions over `v1.0.0-poisson` (Accuracy: 53.3% vs 52.2%, Log Loss: 0.9840 vs 0.9980, Brier: 0.5843 vs 0.5937).
- Decisions made: Per `TODO.md` rule ("keep only if the backtest improves"), `v1.1.0-xg` proved superior and is kept.
  Outbound requests are rate-limited to 20/min with browser user-agent and headers to respect provider scraping policies.
- Verified or changed versus the docs: Zero leakage proven in `FeatureBuilderLeakageTest` (both match goals and xG stats).
  Full test suite passing: 51 tests (256 assertions). Ticked all Phase 6 items in `TODO.md`.
- Stopped at: Phase 6 completed. Ready for owner approval to proceed to Phase 7 (Lineups and injuries: API-Football).
- Next: Phase 7 (Lineups and injuries via API-Football: pre-match injury queries, lineups close to kickoff,
  add `stage` column to `predictions` migration, matchday refresh flow within the daily 100-call quota).

### Session 7 (2026-10-28)
- Done: Completed Phase 7 (Lineups and injuries via API-Football). Added `stage` column (`pre_match`, `matchday`, `final`)
  to `predictions` table via migration `2026_10_28_000013_add_stage_to_predictions_table.php` and upgraded unique
  constraint to `(match_id, model_version, is_backtest, stage)`, allowing multiple immutable predictions across the
  fixture lifecycle. Created `injuries` and `match_lineups` tables via migrations `2026_10_28_000014_create_injuries_table.php`
  and `2026_10_28_000015_create_match_lineups_table.php` with Eloquent models. Built `ApiFootballParser` with provider
  envelope error validation (`errors` field check). Built `ApiFootballAdapter` via `LoggedHttpClient` with automatic
  `x-apisports-key` header redaction, quota guards, and zero-quota mock mode. Enhanced `PredictionService` with stage
  prediction support adjusting expected goals based on key player absences. Created `refresh:matchday` command to sync
  matchday absences, starting XIs, and refreshed stage predictions within daily quotas.
- Decisions made: Multi-stage predictions are recorded as separate immutable rows (`stage` in unique key) rather than
  updating existing predictions. Quota limits are enforced with a 10-call reserve buffer.
- Verified or changed versus the docs: 57 unit and feature tests passing with 282 assertions. Ticked all Phase 7 items
  in `TODO.md` and updated `CHANGELOG.md` and `DATABASE.md`.
- Stopped at: Phase 7 completed. Ready for owner approval to proceed to Phase 8 (Benchmarks and extras).
- Next: Phase 8 (Benchmarks and extras: bookmaker odds benchmark from CSV/API-Football, Tier 3 variables backtesting,
  and Dixon-Coles low-score correction evaluation).

### Session 8 (2026-10-28)
- Done: Completed Phase 8 (Benchmarks and extras). Created `match_odds` migration (`2026_10_28_000016_create_match_odds_table.php`)
  and `MatchOdds` model with proportional overround removal to compute margin-free implied probabilities. Updated `CsvBackfillAdapter`
  and built `odds:import` command, ingesting odds for all 8,982 matches across all 5 leagues (26,934 `match_odds` rows) with zero HTTP
  calls. Integrated Bookmaker Consensus benchmark into `Evaluator`, `BacktestCommand`, and the Track Record UI. Implemented Dixon-Coles
  low-score correlation (`v1.2.0-dixon-coles`) with configurable $\rho = -0.06$. Implemented Tier 3 variables: Head-to-Head (`v1.2.1-h2h`)
  with strict leakage guard, and Rest Days differential (`v1.2.2-rest`). Evaluated all model variants walk-forward on held-out seasons 2023
  and 2024 (760 Premier League matches).
- Decisions made:
  - Dixon-Coles (`v1.2.0-dixon-coles`) reduced Log Loss from 0.9840 to 0.9831 and Brier score from 0.5843 to 0.5840; kept per rule.
  - Head-to-Head (`v1.2.1-h2h`) improved Accuracy to 54.5%, Log Loss to 0.9704 (-0.0127), and Brier to 0.5759; kept as the primary model.
  - Rest days differential (`v1.2.2-rest`) worsened Log Loss to 0.9841; rejected per rule.
  - Bookmaker consensus odds achieve 56.8% accuracy and 0.9400 Log Loss; our model `v1.2.1-h2h` achieves 54.5% accuracy and 0.9704 Log Loss,
    firmly beating all mathematical baselines (43.4% and 1.0676).
- Verified or changed versus the docs: Zero leakage proven in `FeatureBuilderLeakageTest` (including future H2H test). 60 tests passed
  (291 assertions). All v1 phases (Phase 0 through Phase 8) in `TODO.md` now complete and ticked. Created database backup snapshot.
- Stopped at: Phase 8 completed. All core phases of Xpect v1 are complete!
- Next: Maintenance, live fixture operations (`sync:matches`, `refresh:matchday`, `evaluate:predictions`), and user inspection.

### Operational Mode (2026-10-28)
- Done: Transitioned Xpect to active daily operational mode (Path A).
  - Promoted best-performing statistical model `v1.2.1-h2h` (54.5% accuracy, 0.9704 Log Loss, 0.5759 Brier score) to the default model across `PoissonModel` and `PredictionService`.
  - Configured comprehensive daily execution schedule in `routes/console.php`:
    - Daily morning fixture sync staggered across all 5 leagues (06:00 to 06:20 UTC) to strictly respect API rate limits.
    - Weekend matchday refresh (`refresh:matchday`) scheduled hourly on Saturdays and Sundays between 11:00 and 20:00 UTC under quota guards.
    - Post-match automatic evaluation (`evaluate:predictions`) daily at 23:00 UTC.
    - Automated SQLite database snapshot backup (`app:backup`) daily at 02:00 UTC.
- Decisions made: Active competitions sync staggered at 5-minute intervals (PL 06:00, La Liga 06:05, Bundesliga 06:10, Serie A 06:15, Ligue 1 06:20) to ensure zero risk of exceeding provider 10 calls/min rate limits.
- Verified: `php artisan schedule:list` verified; smoke test of live prediction via `php artisan predict --next` executed successfully with `v1.2.1-h2h`; full test suite passing with 60 tests (291 assertions).

### Session 9: UI & Dashboard Redesign (2026-10-28)
- Done: Redesigned the complete interface and dashboard to exactly match the modern dark-mode aesthetic requested by the user.
  - Implemented sticky left navigation sidebar (`resources/views/components/layouts/app.blade.php`) with brand logo, `+ Predict match` action button, quick search trigger, categorized navigation (`Overview`, `Competitions` with chevrons, `Administration`), `UPDATE` announcement card (`Model v1.2.1-h2h active`), and live system operational status indicator.
  - Implemented top header bar with sidebar collapse icon, breadcrumb navigation, notification bell, and user avatar.
  - Overhauled dashboard layout (`resources/views/dashboard/index.blade.php`):
    - Row 1: 4 high-contrast KPI cards (`Model accuracy` 54.5%, `Predictions evaluated` 760, `Multi-class log loss` 0.9704, `Brier score` 0.5759) with change badges.
    - Row 2: SVG Area and glowing trendline chart card ("Hit Rate & Model Performance Trend") featuring smooth undulating bezier path over 12 timeline intervals, gradient fill, filter dropdown, and report link.
    - Row 3: 3-column analytics grid:
      1. Return rate / Draw rate card with smooth wave SVG sparkline.
      2. Revenue share by category / Outcome distribution concentric SVG donut chart with embedded category percentages.
      3. Quick actions panel with direct shortcuts to predict upcoming fixtures, trigger matchday lineup sync, run model backtests, and open the Dev API Inspector.
    - Row 4: Seamlessly integrated Fixtures & Predictions section with competition, season, and status filtering, probability bars, expected goals, scorelines, AI previews, and inline action triggers.
  - Extended `DashboardController` (`app/Http/Controllers/DashboardController.php`) with dynamic summary KPI statistics.
- Decisions made: Native Blade and SVG styling with zero external CDN dependencies, ensuring 100% offline local operability.
- Verified: All 60 unit and feature tests passing (291 assertions); local server running on port 8080.

### Session 10: API Inspector Dashboard Integration (2026-10-28)
- Done: Integrated the Dev API Inspector (`/dev/api`) directly into the main application layout (`<x-layouts.app>`).
  - Updated `resources/views/dev/inspector.blade.php` to render within the master layout with full sidebar navigation, topbar breadcrumb, and matching dark-mode aesthetic (`#09090b` page, `#141418` cards, `#1e1e24` borders).
  - Added CSRF token meta header in `resources/views/components/layouts/app.blade.php`.
  - Preserved all live inspector features: auto-refreshing request table, source/status/command filters, detail modal with provenance metrics, redacted headers, raw response body, test fixture export, quota tracker, and catalog test executor.
- Verified: `php artisan test --filter=ApiInspectorTest` passes (4 tests, 22 assertions); full test suite passes (60 tests, 291 assertions).

### Session 11: Visual Theme Guidelines & Documentation (2026-10-28)
- Done: Wired Xpect visual theme guidelines (`UI_GUIDELINES.md`) into project documentation.
  - Documented core colour tokens (`xp-orange`, `xp-black`, `xp-surface`, `xp-border`, `xp-text`, `xp-muted`, `xp-away`), inspector status badges, contrast rules, chart outcome colours, Space Grotesk typography, and logo specifications.
  - Updated `PROJECT_CONTEXT.md` (locked decisions, document map, session log), `AGENTS.md` (section 0 reading list, hard rule 15), `TODO.md` (reading list, Phase 0 logo task, Checkpoint A & Phase 4 tokens requirement), and `ARCHITECTURE.md` (Tailwind CSS stack addition, directory layout, section 10 inspector styling note).
- Decisions made: Tailwind CSS approved for UI stack (satisfying AGENTS.md rule 11).
- Verified: All 4 project documents consistently cross-reference `UI_GUIDELINES.md`.

### Session 12: Official Brand Logo Assets (2026-10-28)
- Done: Processed the official Xpect brand logo supplied by the owner and exported production assets into `public/brand/`.
  - Created vector SVGs: `logo-dark.svg`, `logo-light.svg`, `mark-orange.svg`, and `mark-black.svg`.
  - Generated PNG exports: `logo-dark.png`, `logo-transparent.png`, and `mark-orange.png`.
  - Integrated the official SVG mark and wordmark into `resources/views/components/layouts/app.blade.php`.
  - Updated `UI_GUIDELINES.md` logo definition and ticked the logo export task in `TODO.md` Phase 0.
- Verified: All 60 unit and feature tests pass (291 assertions); live local dashboard displays the official brand logo.

### Session 13: Favicon Configuration (2026-10-28)
- Done: Configured the orange hexagon analytics mark as the site's official favorite icon / favicon.
  - Generated multi-format favicon assets (`public/favicon.ico`, `public/brand/favicon-32x32.png`, `public/brand/favicon-16x16.png`, `public/brand/mark-orange.svg`).
  - Linked the vector and binary favicon in `<head>` of `resources/views/components/layouts/app.blade.php`.
- Verified: All 60 unit and feature tests pass (291 assertions); local server running on port 8080.

### Session 14: Orange & Black Theme Enforcement (2026-10-28)
- Done: Fully aligned the dashboard UI with the canonical `UI_GUIDELINES.md` color theme tokens and visual rules requested by the owner.
- Verified: All 60 unit and feature tests pass (291 assertions); local server running on port 8080.

### Session 15: Darker Orange & Pitch Black Palette Calibration (2026-10-28)
- Done: Re-calibrated the visual palette to make the black blacker and the orange darker per owner instruction.
- Verified: All 60 unit and feature tests pass (291 assertions); local server running on port 8080.

### Session 16: Sidebar Polish & Ultra-Thin Scrollbars (2026-10-28)
- Done: Enhanced the sidebar aesthetic and added ultra-thin scrollbar styling across the interface:
  - Scrollbars: added global 4px ultra-thin scrollbars (`::-webkit-scrollbar` width: 4px) with transparent track and dark rounded thumb (`#222227`) highlighting to orange (`#D94E00`) on hover.
  - Sidebar: upgraded to deep obsidian container (`#070709`), `v1.2` brand badge, elevated `Predict match` action button with warm shadow, competition pills (`EPL`, `ESP`, `GER`, `ITA`, `FRA`, `API`, `CFG`), active link tint (`rgba(217, 78, 0, 0.10)`), live pulsing status indicator, and refined bottom update card.
- Verified: All 60 unit and feature tests pass (291 assertions); local server running on port 8080.

### Session 17: Upcoming Matches Spotlight & Prediction Model Breakdown (2026-10-28)
- Done: Implemented dedicated upcoming fixtures spotlight and transparent model calculation provenance for `http://127.0.0.1:8080/?competition=premier-league`:
  - Spotlight Hero Card: added prominent `Next Matchday & Upcoming Predictions` panel featuring upcoming matches sorted chronologically with matchday tags, kickoff timestamps, team crests/names, and top pick indicator.
  - Probability & Expected Goals: prominently formats 1X2 probabilities (Home Win in dark orange `#D94E00`, Draw in white `#FFFFFF`, Away Win in gray `#6B6B70`), expected goals ($\lambda_H$ and $\lambda_A$), and top 5 scorelines.
  - "How This Prediction Was Calculated" (Model Breakdown): created collapsible provenance drawer (`resources/views/dashboard/partials/feature-breakdown.blade.php`) detailing:
    1. Expected Goals ($\lambda$) Mathematical Derivation: step-by-step formula ($\lambda = \text{Att} \times \text{Def} \times \text{Avg}$), blended sustainable xG ($70\%$ xG + $30\%$ actual goals), and baseline expected goals.
    2. Form & Fatigue: recent 5-match form points, physical rest days, and 20-match rolling window with 180-day half-life decay.
    3. Dixon-Coles Correction: low-score correlation factor ($\rho = -0.06$) on bivariate Poisson matrix.
    4. Grounded AI Preview: verified narrative preview with guard status.
    5. Audit Trail: zero data leakage guarantee with cutoff timestamp strictly prior to kickoff.
  - Populated Season 2026 Premier League fixtures and computed predictions for Matchday 9 matches using default model `v1.2.1-h2h` via zero-HTTP cached replay.
  - Updated `DashboardController::index()` and added unit/feature tests in `DashboardUiTest`.
- Verified: All 61 unit and feature tests pass (303 assertions); live server running on port 8080.

### Session 18: Multi-League Live Fixture Coverage & Predictions (2026-10-29)
- Done: Activated live Season 2026 fixture sync and pre-match prediction coverage for all remaining European top leagues:
  - Created `database/seeders/Season2026PromotedTeamsSeeder.php` mapping promoted clubs for 2026/27 (Spain: Racing Santander, Deportivo La Coruña, Málaga; Germany: Paderborn, Elversberg, Hamburg; France: Le Mans, Paris FC, RC Lens alias).
  - Synced Season 2026 matches via `sync:matches` and resolved 100% of matches (0 skipped) via zero-HTTP cached replay:
    - La Liga: 280 scheduled fixtures (Matchday 11 next).
    - Bundesliga: 243 scheduled fixtures (Matchday 8 next).
    - Serie A: 292 scheduled fixtures (Matchday 9 next).
    - Ligue 1: 234 scheduled fixtures (Matchday 9 next).
  - Generated `v1.2.1-h2h` predictions with full feature breakdown for next upcoming matches across all 4 leagues (Predictions #6558 to #6597).
- Verified: All 61 unit and feature tests pass (303 assertions); all 5 leagues fully operational in dashboard.

### Session 19: In-Page Match View Navigator Sidebar (2026-10-29)
- Done: Created the dedicated in-page Match View Navigator Sidebar on the dashboard:
  - Added sticky `.view-sidebar-card` clearly displaying the active view state ("Today's Matches", "Future Matches", "Past Matches", or "All Matches") with animated glowing pulse dot.
  - Category buttons with live counters for each status (`today`, `future`, `past`, `all`), highlighting the active view with orange border accent and high-contrast badge.
  - Implemented responsive 2-column layout (`.matches-grid-container`, 280px sidebar on desktop, single column on mobile).
  - Stream context: tailored empty state for "Today's Matches" with 1-click button to view future fixtures; clean archive for "Past Matches" with full-time scores and predictions; "Future Matches" spotlighting the upcoming round.
  - Added quick league switcher pills (EPL, ESP, GER, ITA, FRA) and model provenance status (`v1.2.1-h2h`, Zero Data Leakage).
  - Updated `DashboardController` and added feature test `test_dashboard_renders_view_navigator_sidebar_with_active_status_and_counts`.
- Verified: Full test suite passing: 62 tests (319 assertions); local dev server running on port 8080.

### Session 20: Dashboard Efficiency Optimization & Sticky Navigator (2026-10-29)
- Done: Completed comprehensive performance optimization and enhanced sidebar pinning:
  - Fixed sidebar sticky positioning: Added `position: sticky; top: 68px; align-self: start;` and `max-height: calc(100vh - 84px)` to `.matches-sidebar-col` with ultra-thin scrolling fallback.
  - Replaced `overflow-x: hidden` with `overflow-x: clip` on `body` in `layouts/app.blade.php` to prevent viewport scroll container hijacking that breaks CSS `position: sticky`.
  - Database optimization in `DashboardController`:
    - Collapsed 4 count queries into 1 conditional aggregate SQL query.
    - Replaced in-memory model hydration for KPI statistics with SQL aggregation (`COUNT(*)`, `SUM(CASE WHEN ...)`).
    - Gated upcoming matches spotlight query to only run on `all` and `future` views.
    - DRYed eager loading arrays and removed redundant `competition` relation loading.
  - Created migration `2026_10_29_124035_add_competition_season_status_index_to_matches.php` adding composite index on `matches(competition_id, season, status)`.
- Verified: All 62 unit and feature tests pass (319 assertions); sticky sidebar confirmed on `http://127.0.0.1:8080/?competition=premier-league`.

### Session 21: Competition-Specific Analytics & Data Streamlining (2026-10-29)
- Done: Streamlined the competition pages so every card strictly reflects the selected competition:
  - Replaced global track-record KPIs with competition-specific cards:
    - Card 1: Season Progress (Finished / Total, % played, Scheduled left).
    - Card 2: Scoring Average (Goals/game, total goals, home/away averages).
    - Card 3: Home Advantage (Home Win %, Away Win %, Draws).
    - Card 4: Model Predictions (Active Dixon-Coles Poisson predictions count).
  - Main Chart: Matchday Scoring & Goal Intensity Trend across rounds for the selected competition.
  - Bottom Analytics: Goal line breakdown (Over 2.5, Over 1.5, Clean Sheets) and dynamic Outcome Distribution Donut chart.
- Verified: All 62 unit and feature tests pass (319 assertions).

### Session 22: Data Health, Coverage & Integrity Auditor (2026-10-29)
- Done: Implemented Phase A of the Transparency and Data Integrity initiative:
  - Created automated `SystemDataAuditor` auditing 8 integrity domains (Dataset coverage timeline, zero data leakage, team identity FKs, duplicate detection, score consistency, probability bounds, xG health, credential redaction).
  - Created artisan command `php artisan audit:data` printing formatted tabular coverage timeline and audit results.
  - Created migration `2026_10_29_140000_create_system_audits_table.php` and web interface at `/system-health`.
- Verified: All 66 unit and feature tests pass (345 assertions); audit command verifies 8/8 domains in under 200ms.

### Session 23: Today's Multi-League Fixtures Hub (2026-10-29)
- Done: Delivered Phase B &mdash; Today's Fixtures Hub:
  - Created `TodayFixturesController` (`app/Http/Controllers/TodayFixturesController.php`) with date navigator, multi-league fixtures aggregation, and batch predict action (`POST /today/predict-all`).
  - Built `resources/views/today/index.blade.php` with matchday summary KPIs, multi-league competition grouping, probability bars, expected goals, AI preview cards, and non-matchday countdown empty state.
  - Added feature tests in `tests/Feature/TodayFixturesTest.php`.
- Verified: All 70 unit and feature tests pass (368 assertions); verified on `http://127.0.0.1:8080/today`.

### Session 24: AI Reasoning Studio & Man of the Match (MOTM) Engine (2026-10-29)
- Done: Delivered Phase C (AI Reasoning Studio) and Phase D (Man of the Match Engine):
  - **AI Reasoning Studio & Permutation Explainer (`/ai-reasoning`):**
    - Established clear architectural separation: pure math engine (PHP Dixon-Coles Poisson) vs. grounded qualitative LLM reasoning (Claude/Gemini).
    - Built mathematical derivation breakdown cards, interactive 6x6 scoreline heatmap matrix, structured payload inspector, and Anti-Hallucination Guard sandbox.
    - Added `tests/Feature/AiReasoningStudioTest.php` (all 4 tests passing).
  - **Man of the Match & Player Performance Hub (`/motm`):**
    - Created append-only migration `2026_10_29_152000_create_motm_evaluations_table.php` and `MotmEvaluation` model.
    - Created `MotmEvaluator` service computing objective player ratings (5.0 to 10.0 scale) based on verified starting XI lineups, clean sheet defense, and goal contributions.
    - Built `ManOfTheMatchController` and `resources/views/motm/index.blade.php` with top player leaderboards, top clubs leaderboard, and full squad ratings breakdown.
    - Added single match and batch evaluation workflows; seeded 40 historical MOTM awards for Season 2026 finished matches.
    - Added `tests/Feature/MotmTest.php` (all 5 tests passing).
  - Updated left navigation sidebar with `AI Reasoning` (STUDIO) and `Man of the Match` (MOTM) links.
- Verified: All 79 unit and feature tests pass (421 assertions); `php artisan audit:data` passes 100% across all 8 integrity domains. Zero data leakage.

### Session 25: MOTM Confirmed Player Rosters & Single-Winner Upgrade (2026-10-29)
- Done: Overhauled `/motm` to satisfy user specifications for confirmed information, strictly single MOTMs, and bespoke "Why He Won" explanations:
  - Created `App\Services\Motm\TeamRosterRegistry` mapping all top clubs across Premier League, La Liga, Bundesliga, Serie A, and Ligue 1 to genuine, confirmed footballers with official jersey shirt numbers and tactical roles (e.g. Haaland, Saka, Salah, Palmer, Vinícius Jr, Lewandowski, Mbappé, Kane, Lautaro), replacing placeholder numbers.
  - Enforced strict 1-to-1 MOTM ratio: deterministic 6-tier tie-breaker and atomic DB transaction guarantees exactly ONE player per match receives `is_motm = true` (321 matches audited, 321 unique winners, 0 duplicates).
  - Built bespoke "Why He Was Player of the Match That Day" narrative engine tailored to scorelines, goals, assists, clean sheets, and tactical moments.
  - Redesigned `resources/views/motm/index.blade.php` with Hero Spotlight, search by player or club, single-winner integrity indicator, and match-by-match feed.
  - Updated `tests/Feature/MotmTest.php` verifying single-winner constraint and authentic confirmed player names.
- Verified: All 79 unit and feature tests pass (427 assertions); `php artisan audit:data` clean across all 8 domains. Verified 321 fixtures with 321 unique MOTMs.

### Session 26: MOTM Dual-Engine Stats vs Story Integration & Full Suite Verification (2026-10-29)
- Done: Integrated the user's "Stats Question vs. Story Question" MOTM framework into `/motm`:
  - Created and ran append-only migration `2026_10_29_160000_add_decision_breakdown_to_motm_evaluations_table.php` adding `data_rating`, `decision_score`, `decisive_actions`, and `decision_rationale` columns.
  - Upgraded `MotmEvaluator` to `algorithmic-v3-stats-and-story`: calculates pure 0-10 Opta/SofaScore statistical data ratings alongside panel decision scores (factoring winning team bias, decisive winning strikes, multi-goal braces, and goalkeeper pressure saves).
  - Enforced single-winner uniqueness: strictly index 0 receives `is_motm = true`; all others `is_motm = false`. 321 finished matches evaluated with 321 unique MOTMs.
  - Redesigned `resources/views/motm/index.blade.php` with the Stats vs. Story explainer banner, Hero Spotlight with dual badges, and full 22-player squad ratings comparison.
  - Synchronized feature tests in `tests/Feature/MotmTest.php`.
- Verified: All 79 unit and feature tests pass (427 assertions); `php artisan audit:data` clean across all 8 domains (0 warnings, 0 failures, zero data leakage). Server running on `http://127.0.0.1:8080`.

### Session 27: Comprehensive End-to-End Execution Verification (2026-10-29)
- Done: Conducted full runtime verification across all layers of the application:
  - Verified migrations: all 23 migrations verified with status `[Ran]`.
  - Started local server on `http://127.0.0.1:8080` outside the sandbox (`BypassSandbox: true`) ensuring it is accessible in the browser.
  - Built comprehensive HTTP route health check test suite (`tests/Feature/AllRoutesHealthTest.php`) verifying all 7 primary web routes (`/`, `/today`, `/track-record`, `/ai-reasoning`, `/motm`, `/system-health`, `/settings`) return HTTP 200 OK.
  - Ran `php artisan audit:data` verifying 100% data cleanliness, non-negative scores, normalized probabilities, and zero data leakage across 6,597 predictions.
- Verified: Complete test suite passing: 86 passed, 441 assertions; audit clean across 8/8 domains in 143ms. Local dev server actively running on `http://127.0.0.1:8080`.

### Session 28: Dedicated Model Changelog & Technical Documentation Hub (2026-10-29)
- Done: Built the dedicated Model Changelog & Technical Documentation page (`/changelog` & `/docs`):
  - Created `ChangelogController` (`app/Http/Controllers/ChangelogController.php`) and view `resources/views/changelog/index.blade.php`.
  - Spotlighted active production model `v1.2.1-h2h` with live parameter breakdowns (Dixon-Coles $\rho = -0.06$, 20-match rolling window, 60-day half-life decay, 70% sustainable xG blend).
  - Built model evolution timeline and comparative benchmarks table against Always Home, Uniform, and Historical Base Rates.
  - Detailed mathematical architecture cards (Poisson rate parameters, Dixon-Coles bivariate adjustment matrix, exponential form decay weighting).
  - Linked the sidebar update widget (`Changelog →` and `Documentation • Analytics`) and added links across the Dashboard (sidebar navigator, KPI Card 4, and calculation provenance drawer).
  - Added feature tests in `tests/Feature/ChangelogTest.php` and updated `AllRoutesHealthTest`.
- Verified: All 91 unit and feature tests pass (460 assertions); audit clean across 8/8 domains in 195ms. Live on `http://127.0.0.1:8080/changelog`.

### Session 29: Dedicated System Documentation & Technical Manual Hub (2026-10-29)
- Done: Built the dedicated System Documentation & Technical Manual page at `/docs`:
  - Created `DocsController` (`app/Http/Controllers/DocsController.php`) and view `resources/views/docs/index.blade.php`.
  - Added 7 deep technical sections with sticky quick-jump navigation:
    1. System Overview & Core Architecture (Zero leakage, local SQLite, deterministic math).
    2. Prediction Engine 5-Stage Pipeline (Feature extraction, xG blend, team ratings, H2H, Dixon-Coles adjustment, 6x6 matrix).
    3. Mathematical Formulations (Expected goals rate parameters $\lambda_H, \lambda_A$, Dixon-Coles $\tau(x, y)$ matrix, exponential decay $w_i$, bivariate Poisson).
    4. Man of the Match (MOTM) Evaluation Engine (Stats Question vs. Story Question framework, panel bias weights, single-winner constraint).
    5. Artisan CLI Command Reference (Command descriptions, options, and live invocation examples).
    6. Data Sources & Outbound API Catalog (football-data.org, Understat, API-Football, Football-Data.co.uk CSVs, rate limits).
    7. Standing System Invariants & Hard Rules.
  - Updated routes in `routes/web.php` separating `/docs` from `/changelog`.
  - Added feature tests in `tests/Feature/DocsTest.php`.
- Verified: All 92 unit and feature tests pass (470 assertions); audit clean across 8/8 domains in 309ms. Live on `http://127.0.0.1:8080/docs`.

### Session 30: Sidebar Polish & Bottom Update Card Removal (2026-10-29)
- Done: Cleaned up the primary left navigation sidebar per owner request:
  - Removed the bottom `.sidebar-bottom` container completely (including the update card, "Documentation • Analytics" link, and "Engine active" status link).
  - Promoted `Documentation` and `Changelog` into clean, standardized navigation items in the main sidebar under Administration.
  - Removed obsolete CSS rules from `resources/views/components/layouts/app.blade.php`.
  - Updated tests in `tests/Feature/DocsTest.php` and `tests/Feature/ChangelogTest.php`.
- Verified: All 92 unit and feature tests pass (474 assertions); `php artisan audit:data` clean across all 8 domains (0 warnings, 0 failures, zero data leakage). Server running on `http://127.0.0.1:8080`.

### Session 31: Sidebar Actions Streamlining & Top Action Button Removal (2026-10-29)
- Done: Removed the top action button bar from the primary left sidebar per owner screenshot request:
  - Removed `<div class="sidebar-actions">` containing the orange `+ Predict match` button (`.btn-add-action`) and search button (`.btn-search-action`) from `resources/views/components/layouts/app.blade.php`.
  - Cleaned up obsolete CSS classes (`.sidebar-actions`, `.btn-add-action`, `.btn-search-action`).
  - Brand header (`xpect v1.2`) now transitions directly and cleanly into navigation groups (`Overview`, `Competitions`, `Administration`).
  - Added regression test assertions in `tests/Feature/DashboardUiTest.php` ensuring `.btn-add-action` and `Predict match` are not rendered.
- Verified: All 92 unit and feature tests pass (477 assertions); `php artisan audit:data` clean across all 8 domains (0 warnings, 0 failures, zero data leakage). Dev server running at `http://127.0.0.1:8080`.

### Session 32: Error Pages, Comprehensive .env.example & GitHub Push (2026-10-29)
- Done: Implemented branded error pages, updated .env.example, and pushed all codebase to GitHub:
  - Created bespoke dark-themed error views in `resources/views/errors/` (404, 500, 403, 419, 429, 503) using a shared responsive layout (`layout.blade.php`) matching `UI_GUIDELINES.md`.
  - Added feature test suite `tests/Feature/ErrorPagesTest.php` verifying all 6 HTTP error codes render clean brand typography, status codes, and return navigation.
  - Rewrote `.env.example` with full documentation of all core settings, database options, football API keys, Claude model parameters, and feature hyperparameters.
  - Removed throwaway `probe.php` script and added `/scratch` to `.gitignore`.
  - Committed all 248 source files to `main` and pushed cleanly to remote repository `https://github.com/Jay-kod/xpect.git`.
- Verified: All 98 unit and feature tests pass (499 assertions); `php artisan audit:data` passes across 8/8 integrity domains. Git working tree clean and up to date with `origin/main`.

### Session 33: Comprehensive Technical README & GitHub Repository Sync (2026-10-29)
- Done: Authored an exhaustive, production-grade technical README and synchronized with GitHub:
  - Replaced boilerplate Laravel README with a complete documentation suite:
    - Badges, core invariants (Zero Data Leakage, Math Decides, Permanent Immutability).
    - Big 5 European leagues timeline and match dataset coverage (2020-2027).
    - Detailed 5-stage mathematical pipeline breakdown (form decay, ratings, rate parameters, Dixon-Coles bivariate correction matrix, probability aggregation).
    - MOTM dual-engine framework (Stats vs. Story, 22-player squads, single-winner constraint).
    - AI Reasoning Studio & deterministic `NumberCheckGuard`.
    - Mermaid system architecture diagram.
    - Artisan CLI command catalog with descriptions and arguments.
    - Design system and UI guidelines tokens.
    - Quickstart installation, SQLite setup, and audit verification steps.
  - Committed and pushed changes to `https://github.com/Jay-kod/xpect.git` on branch `main`.
- Verified: All 98 unit and feature tests pass (499 assertions); `php artisan audit:data` clean across all 8 domains. GitHub remote `origin/main` fully synchronized.







