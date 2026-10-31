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
  - Keep built-in presets fixed and fall back to Pitch Black when the active custom theme is deleted

---

## Open items

- Confirm football-data.org free-tier history depth (Phase 0).
- Confirm API-Football daily quota, reset time and season access (Phase 0).
- Decide the Elo source after checking availability.
- Set `ANTHROPIC_MODEL` in `.env` before Phase 3.
