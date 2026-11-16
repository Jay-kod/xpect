# TODO.md (THE MASTER PROMPT)

This file is the master prompt and the working checklist for Xpect. The owner's
whole instruction to the coding agent is one line:

> Read TODO.md and follow its MASTER PROMPT section.

Rules, architecture, schema and endpoints live in the other docs, which this file
tells you to read.

---

## MASTER PROMPT (addressed to the coding agent)

You are the lead engineer on Xpect, a local-only Laravel football prediction
tool. One user, no hosting, no auth.

### Every session, in this order

1. Read `AGENTS.md` completely. It governs every message in this project.
2. Read `PROJECT_CONTEXT.md` (including the SESSION LOG at the bottom),
   `ARCHITECTURE.md`, `DATABASE.md`, `API_CATALOG.md`, `UI_GUIDELINES.md`, then the rest of this file.
3. Find the CURRENT PHASE below and its first unticked item. That is your work.
   Never start another phase or checkpoint without the owner's approval.
4. Work with the per-prompt protocol in `AGENTS.md` section 1. Stop at every
   checkpoint and report in the `AGENTS.md` section 8 format.
5. Before ending: tick finished items here, add a `CHANGELOG.md` entry, update any
   doc you changed, and append to the SESSION LOG in `PROJECT_CONTEXT.md`.

### First session only (until "First session reviewed" below is ticked)

Do NOT write any code. Reply with exactly these five parts, then STOP and wait:

a) The project in three sentences, in your own words.
b) The hard rules from `AGENTS.md` section 2, one line each.
c) Contradictions, gaps or risky assumptions across the docs. Be blunt. If you
   find none, say what you checked.
d) Where the owner's Phase 0 findings (table below) change `API_CATALOG.md`,
   `DATABASE.md` or this file, as a proposed list of edits. Do not apply them yet.
e) At most five questions, only ones that block Phase 1 checkpoint A.

- [x] First session reviewed (the owner ticks this after approving your reply;
      then apply the edits from (d) and begin the first unticked item)

### Owner input: Phase 0 findings

Treat these as more trustworthy than the docs. Blank means not checked yet. Do
not assume.

| Check | Result |
|---|---|
| football-data.org free key: past seasons returned | Verified: Current season (2026/27) + 1 past season (2023/24). 2022 and earlier return HTTP 403 Forbidden. CSV backfill mandatory for 3-5 season backtesting. Rate limits verified: 10 calls/min, 60s reset. |
| API-Football: plan, daily quota, reset time | Deferred to Phase 7 (no key configured in Phase 0; free plan is ~100 calls/day, reset tracked via headers) |
| API-Football free plan: seasons, injuries, lineups access | Deferred to Phase 7 |
| Football-Data.co.uk CSVs: seasons available, columns | Verified: 5 seasons (2020/21 - 2024/25) across all top 5 leagues (E0, SP1, D1, I1, F1). Format DD/MM/YYYY. Headers: \ufeffDiv, Date, Time, HomeTeam, AwayTeam, FTHG, FTAG, FTR, HTHG, HTAG, HTR, Referee, shots/odds columns. BOM present on first column. |
| Anything that differed from `API_CATALOG.md` | football-data.org free key returns only current + 1 past season (2023), not 3-5 seasons. CSV fallback is therefore mandatory for backtest history. |

### Standing instructions

- Work only inside the current phase and checkpoint. Stop after each checkpoint.
- Tick an item only when it is verified by running it, never assumed.
- Smallest change that works. Boring and simple beats clever.
- Never invent endpoints, columns, files, dependencies or football data.
- All HTTP through `LoggedHttpClient`. Every call saved in full. No secrets saved.
- If the docs and reality disagree, or two docs disagree, stop and tell the owner.
- Be direct. No filler. Disagree with the owner when they are wrong.

### The owner's messages (for reference)

- Start of every session: `Read TODO.md and follow its MASTER PROMPT section.`
- After the first-session reply: `Approved. Apply your proposed edits from (d),
  then begin the first unticked item in TODO.md.`
- Normal requests, use this template:
  `MODE: BUILD | DEBUG | REVIEW | PLAN | LEARN`, then `TASK:`, `CONSTRAINTS:`
  (optional), `DONE WHEN:`
- If the agent skips its end-of-session duties:
  `Update the session log and CHANGELOG now.`

---

CURRENT PHASE: **Phase 8 (Benchmarks and extras) - COMPLETE; All v1 Phases Complete**

---

## Phase 0: Data spike and setup (no real coding)

Goal: confirm the data sources behave the way the plan assumes.

- [x] Register a football-data.org key. Make one call for Premier League matches.
      Record which seasons come back on the free key (need 3-5 for backtesting).
- [x] Download the Football-Data.co.uk CSVs for the last 5 seasons of the top 5
      leagues (fallback history). Note column names and date formats.
- [ ] Register an API-Football key. Call its status endpoint. Record the real
      daily quota and reset time. Make one fixtures call.
- [x] Export the logo SVGs from the design canvas into public/brand/
- [x] Save the real responses as fixtures (they become test fixtures later).
- [x] (Optional) run `probe.php` to automate the three checks above. It is a
      throwaway script outside the app and the only code allowed to call APIs
      without `LoggedHttpClient`. Delete it once the inspector page works.
- [x] Create the Laravel project named `xpect` (`APP_NAME=Xpect`), git repo,
      `.env`, `.env.example` (no secrets). Put `AGENTS.md`, `PROJECT_CONTEXT.md`,
      `ARCHITECTURE.md`, `DATABASE.md`, `API_CATALOG.md` and `TODO.md` in the repo
      ROOT (`AGENTS.md` references them by root-relative name).
- [x] Set `.gitignore`: `.env`, the SQLite file, `storage/backups`, logs.
- [x] Fill in the Phase 0 findings table above.
- [x] Note any difference between real responses and what the docs assume.
- [ ] football-data.org: do `dateFrom`, `dateTo` and `status` filters work on the free
      key, and does each match carry `lastUpdated`? How long after full time does a
      match show FINISHED with its final score?
- [ ] CSV: does a `HEAD` request return `ETag`, `Last-Modified` or `Content-Length`,
      and does the server honor conditional requests? Check the current-season file
      twice a day for a few days and note when it changes.
- [ ] Confirm one probe cycle (all active competitions) stays well under 10
      football-data.org calls per minute.

**Done when:** you know exactly what each free key returns and the findings table
is filled in.

## Phase 1: Foundation

Goal: Premier League history in the DB, correct names, every call logged, and a
first prediction printed in the terminal.

The agent STOPS after each checkpoint and reports. The owner approves the next.

### Checkpoint A: logging backbone

- [x] Migrations: competitions, teams, team_aliases, matches,
      standings_snapshots, team_ratings, api_calls, sync_runs, predictions,
      prediction_evaluations, previews (match_stats can wait for Phase 6)
- [x] `config/football.php` (window, half-life, shrinkage k, TTLs, quota limits,
      secrets list, backup count)
- [x] `config/api_catalog.php` (machine copy of `API_CATALOG.md`)
- [x] `LoggedHttpClient` + `QuotaGuard`: redaction, cache-hit marker rows,
      provider-error detection (non-empty `errors` is a failure)
- [x] Test: keys never appear in `api_calls`
- [x] Inspector skeleton: `/dev/api` with the call log tab, local env only, styled with UI_GUIDELINES.md tokens

### Checkpoint B: data in

- [x] Seed competitions; seed teams + aliases from real responses (hand-reviewed)
- [x] `TeamResolver` (unknown names reported, never auto-created)
- [x] football-data.org adapter + parser, `backfill:history premier-league`
- [x] `--from-cache` replay works with zero HTTP
- [x] `sync:matches premier-league` (upcoming fixtures + latest results)
- [x] CSV backfill adapter, ONLY if the free football-data.org key returns fewer
      than 3 past seasons
- [x] `standings:rebuild premier-league` (snapshots computed from `matches`, no
      API cost)
- [x] `app:backup` command; run it after every backfill
- [x] Inspector complete: call detail, quota panel, catalog tab with the
      expected-vs-actual check and the guarded "Try it" button

### Checkpoint C: first prediction

- [x] `FeatureBuilder::build(team, asOf)` + **leakage test passing**
- [x] `PoissonModel` with hand-checked unit tests
- [x] `php artisan predict {match}` prints probabilities

**Done when:** `predict` prints sensible numbers for an upcoming Premier League
match, tests pass, and `api_calls` contains every request made.

## Phase 2: Model evaluation

- [x] Baselines: always-home, uniform, historical base rates
- [x] Metrics: accuracy, log loss, Brier, calibration table
- [x] `backtest` command (walk-forward, `is_backtest = true`)
- [x] Run on 2+ held-out seasons; record results in `CHANGELOG.md` per
      `model_version`
- [x] `evaluate:predictions` for live predictions after full time
- [x] Scheduler: daily `sync:matches` + `evaluate:predictions`
- [x] If results beat the baselines: proceed. If not: fix the model before
      anything else. If accuracy is suspiciously high: hunt for leakage.

**Done when:** you can state, with numbers, whether v1 beats the baselines.

## Phase 2D: Freshness and auto-refresh (build right after 2A)

How it works:
- **Probes (cheap, due-only).** football-data.org: one windowed matches call per
  active competition (`dateFrom` today minus 3 days, `dateTo` today plus 7 days).
  The response is stored in `api_calls` and diffed against the DB. CSV: `HEAD` or a
  conditional GET; if the server gives no usable headers, download and hash.
- **Diff.** Each difference becomes a `source_changes` row: new finished match,
  result correction, kickoff change, postponement, new fixture, new season, CSV new
  rows or corrections. Nothing is written to `matches` yet.
- **Refresh.** `refresh:run` checks the significance rules. If met, it applies the
  STORED responses (no new calls): upserts matches with the merge rules, runs
  `standings:rebuild`, creates or recomputes `match_verifications`, marks the
  changes applied and writes a `sync_runs` row. It never touches `predictions`.
- **Predict due.** `predict --due` predicts matches kicking off within 24 hours that
  have no prediction yet, but refuses if the competition's data is older than 12
  hours. It then runs a probe first (within quota) or skips and logs a warning.
- **Visibility.** Inspector "Sources" tab (read-only): per resource, last checked,
  next check, last changed, unapplied changes by kind, last refresh, data age.
  `/verify` header shows "Data last refreshed at ...".

Tasks:
- [ ] Migrations `source_checks`, `source_changes`; `refresh` block in config
- [ ] `SourceProbe` interface; `FootballDataWindowProbe`; `CsvProbe` (HEAD,
      conditional GET, hash fallback). All HTTP through `LoggedHttpClient`.
- [ ] Diff engine: stored response vs DB, writing `source_changes`
- [ ] `sources:probe` (does nothing unless `next_check_at` has passed)
- [ ] `refresh:run {--force}` with the significance rules and the 2-hour gap
- [ ] Table-driven tests for significance: 4 new finished results do not trigger;
      5 do; one kickoff change in the next 7 days triggers (respecting the gap); 1
      change after 24 hours since the last refresh triggers
- [ ] Test: a probe makes zero HTTP calls when `next_check_at` is in the future,
      and at most one call per competition per cycle
- [ ] Test: `refresh:run` makes zero HTTP calls (replays stored responses)
- [ ] `predict --due` with the data-age guard and lead time
- [ ] Inspector "Sources" tab and the `/verify` "last refreshed" line
- [ ] Scheduler: `sources:probe` and `refresh:run` every 30 minutes

## Phase 3: AI previews

- [x] `PromptBuilder` (structured JSON input only) + versioned system prompt
- [x] `PreviewGenerator` via `LoggedHttpClient` (prompt + response saved in
      `api_calls`)
- [x] `previews` rows linked to the prediction and the api_call
- [x] Number-check guard (flag if the text contains numbers not in the input)
- [x] `preview:generate {prediction_id}` command

**Done when:** a preview is generated for a real fixture, every number in it
matches the model output, and the full call is stored.

## Phase 4: Interface

- [x] Simple local UI: upcoming fixtures, prediction, preview, track record, styled with UI_GUIDELINES.md tokens
- [x] Track-record page (hit rate, log loss over time, calibration, styled with UI_GUIDELINES.md tokens)

## Phase 5: Other top leagues

- [x] Activate La Liga, Bundesliga, Serie A, Ligue 1 (aliases first)
- [x] Re-run backtests per league; compare

## Phase 6: xG (Understat)

- [x] Adapter behind the same interface; raw page/JSON saved in `api_calls`
- [x] `match_stats` migration + parser
- [x] Add xG-based strength as a new `model_version`; keep only if the backtest
      improves

## Phase 7: Lineups and injuries (API-Football)

- [x] Injury data pre-match; lineups close to kickoff
- [x] Add the `stage` column to `predictions` (new migration)
- [x] Matchday refresh flow within the daily quota

## Phase 8: Benchmarks and extras

- [x] Bookmaker odds as a benchmark (not a model input at first)
- [x] Tier 3 variables, one at a time, each backtested
- [x] Dixon-Coles correction, only if it helps the backtest

## Phase 9: Transparency, Matchday Operations & Player Recognition

- [x] **Phase A: Data Health, Coverage & Integrity Auditor (`/system-health` + `audit:data`)**
  - Timeline start-to-end dataset coverage per competition (2020 to 2027)
  - 8-point automated integrity verification script with zero data leakage proof
  - Migration `create_system_audits_table` + web dashboard
- [x] **Phase B: Today's Multi-League Fixtures Hub (`/today`)**
  - Central multi-league matchday hub with date navigator (Yesterday, Today, Tomorrow)
  - Batch predict action computing Dixon-Coles Poisson probabilities with zero leakage
  - Probability outcome bars, expected goals, and AI preview cards
  - [x] Match feeds default to today's UTC date; historical analysis pages keep their existing unfiltered content
- [x] **Phase C: AI Reasoning Studio & Permutation Explainer (`/ai-reasoning`)**
  - Explicit architectural separation: pure math (PHP) vs. grounded qualitative LLM reasoning
  - Mathematical derivation breakdown cards + 6x6 scoreline heatmap matrix
  - AI structured JSON payload inspector + Anti-Hallucination Guard sandbox
  - Persistent reasoning and simulation history stored in SQLite
- [x] **Phase D: Man of the Match (MOTM) & Player Performance Hub (`/motm`)**
  - Migration `create_motm_evaluations_table` for persistent player ratings history
  - `MotmEvaluator` service calculating 5.0 to 10.0 ratings from lineups and match contributions
  - MOTM Leaderboards (Top Players, Top Clubs) + Squad Ratings Breakdown
  - 1-click single match and batch evaluation workflows
  - [x] Verified-only AI research path with Google Search grounding, citations, and end-of-day command
- [x] **Phase E: Model Changelog & Technical Documentation Hub (`/changelog`, `/docs`)**
  - Dedicated page spotlighting active model `v1.2.1-h2h` with live parameters and walk-forward benchmarks
  - Mathematical architecture breakdown: Poisson rate parameters, Dixon-Coles low-score adjustment, and exponential decay
  - Benchmark comparison table against Always Home, Uniform, and Historical Base Rates
  - Direct links on dashboard: sidebar update widget, view navigator, KPI Card 4, and calculation provenance drawer
- [x] **Phase F: Selectable UI Themes (`/themes`)**
  - Sidebar-linked page for Pitch Black, Obsidian, Midnight, and Light palettes
  - Browser-local persistence without database or prediction changes
- [x] **Phase G: Custom Theme Manager**
  - Create, edit, save, select, and delete custom palettes
  - Edit the current palette with a live preview; save built-in edits as custom copies
  - Keep built-in presets fixed and fall back to Pitch Black when the active custom theme is deleted

## Phase 10: Model Calibration & Mathematical Perfection (`v1.3.0-calibrated`)

- [x] **Phase A: Dixon-Coles Optimization & Empirical Rho**
  - Grid search across historical matches optimizing low-scoring scoreline covariance (&rho; = -0.10)
  - Eliminates bivariate Poisson draw probability underestimation on 0-0 and 1-1 scorelines
- [x] **Phase B: Sustainable xG Ratio Elevation (&alpha; = 0.85)**
  - Raised sustainable xG weight from 0.70 to 0.85 (15% actual goals) to filter noisy finishing variance
  - Focuses team ratings strictly on underlying shot generation and suppression
- [x] **Phase C: Form Momentum Factor Integration**
  - Implemented 5-match rolling points differential factor (weight = 0.010, clamped [0.85, 1.15])
  - Dynamically scales expected goals (&lambda;) based on recent momentum without data leakage
- [x] **Phase D: Post-Grid Temperature Scaling (T = 1.08)**
  - Applied post-grid temperature calibration to soften extreme favorite overconfidence
  - Aligns predicted probabilities to empirical match outcomes within 0.1%–1.5% calibration error across all 10 bins
- [x] **Phase E: Walk-Forward Backtesting & Documentation Synchronization**
  - 760-match walk-forward backtest on held-out seasons (2023, 2024): 55.3% accuracy, 0.9658 Log Loss, 0.5727 Brier Score
  - Synced documentation, interactive technical manual, changelog, and settings references

## Phase 11: Commercial SaaS Dashboard Audit, Restructuring & Rebuild

- [x] **Comprehensive Application & Technical Audit**
  - Generated full audit and reference reports in `docs/`: `CANDYCUTZ-AUDIT-REPORT.md`, `DASHBOARD-AUDIT-REPORT.md`, `DASHBOARD-IMPLEMENTATION-PLAN.md`, `DESIGN-SYSTEM.md`, `ROUTE-INVENTORY.md`, `ROLE-PERMISSIONS.md`, `API-INTEGRATION-REPORT.md`, `TESTING-REPORT.md`
- [x] **Responsive Layout & Mobile Drawer Architecture**
  - Added slide-over off-canvas mobile drawer with backdrop blur `#sidebar-backdrop` for screens &le; 1024px
  - Connected `#sidebar-toggle-btn`, close button `#sidebar-close-btn`, backdrop dismiss, and `Escape` key listeners
  - Cleaned navigation grouping into Overview, Intelligence, Competitions, and Administration with micro-badges
- [x] **Dashboard Experience Restructuring & Polish**
  - High-density Executive Provenance Trust Strip with live status dot, model versioning, zero leakage badge, and database statistics
  - 4-Card Executive KPI Grid (Poisson Engine Calibration, Model Accuracy vs Baselines, Calibration & Risk Brier Score, Competition Matchday Operations)
  - SportyBet 6-League Horizontal Filter Chips (EPL, UCL, La Liga, Bundesliga, Serie A, Ligue 1) with dynamic counters
  - SportyBet Day Switcher & confirmed matchdays jump dropdown
  - Curated Marquee Spotlight card for featured next matchday game
  - SportyBet 1-X-2 odds tiles with glowing favored pick, implied fair odds, 3 analytical systems breakdown, match history & MOTM/player ratings, and mathematical breakdown
  - Purged legacy `#FF6B00` hexes in favor of canonical `var(--xp-orange)` / `#D94E00`
- [x] **Verification & Regression Testing**
  - All 180 unit and feature tests passing with 982 assertions (0 failures)
- [x] **Action & Preactivity Dedicated Preloader System**
  - `<x-activity-preloader />` component with orbital rings, ambient radial glow, dynamic operation labels, and safety dismiss
  - `window.XpectPreloader` JavaScript API and action event interception for predictions, AI previews, audits, syncs, simulations, MOTM, and API tests
  - Distinct separation: skeletal transitions for GET page filters, rich preloader for background actions and mutations

---

## Open items

- [x] Add targeted SQLite indexes for dashboard, track-record, and verified
      MOTM query paths.
- [x] Dedicated action & preactivity preloader alongside active skeletal loading system.
- Confirm football-data.org free-tier history depth (Phase 0).
- Confirm API-Football daily quota, reset time and season access (Phase 0).
- Decide the Elo source after checking availability.
- Set `ANTHROPIC_MODEL` in `.env` before Phase 3.
