# CHANGE_PLAN.md: Multi-market predictions + verification page

Temporary file. Part A updates the existing docs. Part B is folded into
`TODO.md` as tasks. When Part A is done and the owner approves, delete this file.

## 0. What changes

1. Each match gets FOUR predictions (one per market), each scored separately.
2. New data: half-time goals and corners (history and new matches).
3. New page `/verify`: the day's matches, each prediction marked green (correct),
   red (wrong) or gray (pending / no data).
4. Phase 2 becomes multi-market. Every market must beat its own baseline before
   it counts.
5. Verification is delayed and scheduled: a match is checked once it is 48 hours
   past kickoff, in one daily batch, with a capped number of retries. Nothing
   polls, and the page never triggers fetches.
6. Freshness: the system watches its sources cheaply, records what changed, and
   refreshes the database automatically once enough new data has arrived.

## 1. Decisions (do not redesign these)

| Market key | Predicts | Outcome keys | Line |
|---|---|---|---|
| `result_1x2` | Match result | home, draw, away | none |
| `goals_ou` | Total goals | over, under | 2.5 (configurable list) |
| `half_more_goals` | Half with more goals | first, second, equal | none |
| `corners_ou` | Total corners | over, under | half-line = floor(expected corners) + 0.5, stored per prediction |

- Only half-lines (x.5), so there are no pushes and every prediction resolves to
  correct or wrong.
- Markets live in `config/football.php` under `markets` (enabled list, lines).
- A market is `experimental` until it passes the gate (section 5). Experimental
  markets are still stored and shown, but labelled.
- Display timezone is configurable (`DISPLAY_TZ` in `.env`, for example
  `Africa/Lagos`). Storage stays UTC. "Matches of the day" uses the display
  timezone.
- Verification timing (all in `config/football.php` under `verification`):
  `first_check_after_hours` = 48, `retry_interval_hours` = 24, `max_attempts` = 5,
  `run_at` = 06:00 display time. When attempts run out the match is marked
  `no_data`; it is never retried forever. Optional and OFF by default:
  `fast_lane_hours` (null). Set to e.g. 6 and goals and half-time markets would
  verify earlier, while corners still wait the full 48 hours.
- Freshness and refresh (config `refresh` block in `config/football.php`):
  - Only two sources are probed for changes: football-data.org and the CSVs.
    API-Football is NEVER probed (quota); it is used only inside `verify:run`.
  - Probe cadence: a competition is "active" if a match kicked off in the last 8
    hours or kicks off within 2 hours. Active: probe every 120 minutes. Otherwise
    every 24 hours. CSVs: every 12 hours. `next_check_at` enforces this.
  - Significant change, refresh when ANY is true: (a) 5 or more unapplied
    `new_finished` or `result_correction` changes; (b) any `kickoff_change`,
    `postponed`, `new_fixture` or `new_season` touching a match in the next 7
    days; (c) at least 1 unapplied change and the last refresh is older than 24
    hours. Minimum gap between refreshes: 2 hours.
  - Predictions are generated once per match, `prediction_lead_hours` = 24 before
    kickoff, and only if the competition's data is no older than
    `max_data_age_hours` = 12. Existing predictions are never regenerated.

## 2. Part A: update the existing docs (docs only, no code)

If any migration has already been run, do NOT edit it. Add new migrations that
alter the tables (AGENTS.md rule 9) and say so in the report.

### D1. DATABASE.md
- `matches`: add `home_corners`, `away_corners`, `home_shots`, `away_shots`,
  `home_shots_on_target`, `away_shots_on_target` (smallint null). `ht_*` columns
  already exist and are now used. Reason for putting corners on `matches`: one
  row per match, available from the free CSVs, needed from Phase 2. `match_stats`
  stays for xG and advanced stats (Phase 6).
- `predictions` becomes generic (still IMMUTABLE):
  `id, match_id, market, line (decimal null), model_version, is_backtest, as_of,
  probabilities (json, keys per market), predicted_outcome (string),
  p_predicted (decimal), expected_value (decimal null: expected total goals or
  corners), exp_home/exp_away (decimal null), top_scorelines (json null, result
  market only), features (json), low_confidence, experimental (bool, snapshot at
  creation), created_at`.
  Unique `(match_id, market, model_version, is_backtest)`. Remove `p_home`,
  `p_draw`, `p_away`.
- `prediction_evaluations`: `id, prediction_id (fk, unique), actual_outcome
  (string, an outcome key), actual_value (decimal null: actual total goals or
  corners), correct (bool), log_loss, brier (multi-class), evaluated_at`. No row
  means pending or no data.
- `previews`: link to `match_id` + `model_version` instead of `prediction_id`. One
  preview per match covers all enabled markets.
- `sync_runs.summary`: also records source conflicts (see the source merge rule in D2).
- New table `match_verifications` (mutable state table, one row per match):
  `id, match_id (fk, unique), status (waiting, partial, verified, no_data, void),
  first_eligible_at (kickoff + first_check_after_hours, recomputed if the kickoff
  changes), attempts (int), last_attempt_at, next_attempt_at, missing (json, for
  example ["corners"]), updated_at`. Index on `(status, next_attempt_at)`.
  `void` = postponed or cancelled.
- Update the Relationships section.

### D2. ARCHITECTURE.md
- Market framework: interface `MarketModel` with `predict(Features $home,
  Features $away, MatchContext $ctx): MarketPrediction`. Implementations:
  `ResultModel` (the existing Poisson 1X2), `GoalsOverUnderModel` (from the same
  scoreline grid), `HalfMoreGoalsModel`, `CornersModel`. `PredictionService` runs
  every enabled market and writes one row per market.
- Half model: take expected total goals from the Poisson model, split it using
  each team's time-decayed, shrunken first-half goal share (league share as the
  prior), then compute P(1H > 2H), P(equal), P(1H < 2H) from two independent
  Poisson goal counts (grid 0..8).
- Corners model: negative binomial for total corners. Mean = team corners-for
  strength x opponent corners-against strength x league average, home and away
  separately, with the same decay and shrinkage as goals. Dispersion estimated
  from league data as-of the match date. Shots may be added as a covariate later,
  only if the backtest improves.
- Generic evaluator: each market has `resolve(Match): ActualOutcome|null`. Null
  when the needed data is missing (stays pending). First half = HT goals, second
  half = FT goals minus HT goals.
- Results flow, in priority order: (1) football-data.org matches (goals, HT; free,
  10/min), (2) Football-Data.co.uk CSV refresh (corners, shots; free, may lag),
  (3) API-Football `/fixtures/statistics` only for finished matches still missing
  corners, capped by `config('football.api_football.corner_calls_per_day')`
  (default 30, leaves the rest of the 100/day). Premier League first.
- Verification timing (no stress on sources): results are fetched ONLY by
  `verify:run`, never on page load and never in a loop. `verify:run` runs once a
  day (scheduler, `verification.run_at`). It selects matches whose
  `first_eligible_at` has passed and whose `match_verifications.status` is waiting
  or partial with `next_attempt_at <= now`. One football-data.org call per
  competition-season covers all its matches, one CSV download per division, and
  API-Football statistics are used only for matches still missing corners, within
  the daily cap. After each attempt it creates evaluation rows for every market
  whose data now exists, sets the status to verified (all markets resolved),
  partial (some missing, attempts left) or no_data (attempts exhausted), and sets
  `next_attempt_at` = now + `retry_interval_hours`. Postponed or cancelled matches
  become `void`. If a kickoff moves, `first_eligible_at` is recomputed.
  `verify:run --match=ID --force` re-opens one match manually. Every call goes
  through `LoggedHttpClient`, so the cache and quota guard apply.
- Source merge: CSV rows are linked to existing matches by (competition, kickoff
  date within 1 day, home team, away team) through `team_aliases`. A non-null value
  from one source is never overwritten by another; disagreements are recorded in
  `sync_runs.summary` as conflicts.
- New section "Verification page" (spec in Phase 2C).
- Commands: `predict`, `backtest` and `evaluate:predictions` gain `--market=`
  (default all enabled). Add `verify:run {--date=} {--match=} {--force}` (fetch
  missing results inside the window, then evaluate). `evaluate:predictions` stays
  pure: it reads the DB only and makes no HTTP calls.

### D3. API_CATALOG.md (all new items MEM until verified)
- football-data.org `matches`: note `score.halfTime` (MEM).
- API-Football `/fixtures/statistics?fixture=`: note the corner stat name
  "Corner Kicks" (MEM); `/fixtures` response `score.halftime` (MEM).
- CSV columns (MEM): `FTHG, FTAG, FTR, HTHG, HTAG, HC, AC, HS, AS, HST, AST`.
- Note the quota cost: one call per fixture for statistics.

### D4. TODO.md
- Phase 0: add the checks in section 4 below.
- Phase 1: apply the schema changes (D1) in checkpoint A migrations. The CSV
  adapter is now REQUIRED (not conditional) in checkpoint B, plus the source merge.
- Replace Phase 2 with 2A, 2B, 2C and 2D from section 3 below (2D is built right after 2A). Keep Phases 3-8 as they
  are, with Phase 3 previews covering all enabled markets.
- Update the Phase summary line everywhere ("2 Model + backtest" becomes
  "2A-2D Markets, backtests, verification page, freshness").

### D5. PROJECT_CONTEXT.md
- Scope: add the four markets and the verification page to "In v1". Remove "half
  and corners" from any not-in-v1 wording.
- Success criteria: per-market baselines and the gate; `/verify` shows green/red.
- Honest limits: add the expectations in section 6 below.
- Locked decisions: add "Markets: 4 per match, gated". Append a session log entry.

### D6. AGENTS.md and UI_GUIDELINES.md
- AGENTS.md: add the next free hard-rule number: "A market is enabled for live use
  only after passing its backtest gate. Until then it is stored with
  `experimental = true`." In section 6 add: "Per-market baselines are mandatory.
  The verification page reads stored predictions and evaluations and never
  recomputes a past prediction." Keep the file under 12,000 characters.
- UI_GUIDELINES.md (create it first from the earlier theme prompt if it does not
  exist): `xp-ok` and `xp-error` are now allowed on the inspector AND `/verify`.
  Correct/wrong must always use an icon and a text label as well as color. Pending
  uses `xp-muted`.

### D7. Freshness and auto-refresh (docs changes)
- DATABASE.md, two new tables:
  - `source_checks` (mutable state): `id, source, resource` (for example `PL:2026`
    or `E0:2526`)`, probe_type` (windowed_query, conditional_get, content_hash)`,
    fingerprint (string null), last_checked_at, next_check_at, last_changed_at,
    consecutive_unchanged (int), last_api_call_id (fk null), updated_at`. Unique
    `(source, resource)`.
  - `source_changes` (append-only log): `id, source_check_id (fk), detected_at,
    kind` (new_finished, result_correction, kickoff_change, postponed, new_fixture,
    new_season, csv_new_rows, csv_correction)`, match_id (fk null), count (int),
    api_call_id (fk), applied_at (null), refresh_run_id (fk to sync_runs, null)`.
    Index `(applied_at, kind)`.
- ARCHITECTURE.md: new section "Freshness and refresh" (spec in Phase 2D). New
  commands `sources:probe`, `refresh:run {--force}` and the `--due` option on
  `predict`. Scheduler: `sources:probe` and `refresh:run` every 30 minutes (both do
  nothing unless something is due), `verify:run` daily. Replace the old "daily
  `sync:matches`" scheduler line. `sync:matches` stays as the manual command that
  `refresh:run` reuses. `verify:run` only fetches fields still missing after a
  refresh (corners, missing HT), so it makes fewer calls.
- API_CATALOG.md (MEM until verified): football-data.org matches filters
  `dateFrom`, `dateTo`, `status`, `season` and the match field `lastUpdated`; CSV
  `HEAD` requests and conditional requests (`If-None-Match`, `If-Modified-Since`).
  State that API-Football is never probed for freshness.
- TODO.md: Phase 0 checks (section 4) and Phase 2D tasks (section 3).
- PROJECT_CONTEXT.md: add "freshness monitor and auto-refresh" to "In v1", and
  the honest limit in section 6.
- AGENTS.md (next free hard-rule numbers): "Freshness checks go only through
  `sources:probe`. No other code polls a source to see whether it changed, and a
  probe never runs before its `next_check_at`." and "Predictions are generated
  only by `predict --due` after the data-age guard. Existing predictions are never
  regenerated." Keep the file under 12,000 characters.

## 3. Part B: implementation tasks (copy into TODO.md)

### Phase 1 additions
- [ ] Migrations include D1 changes (checkpoint A)
- [ ] CSV backfill adapter, REQUIRED: parse results, HT goals, corners, shots
- [ ] Source merge by (competition, date, home, away) with conflict logging
- [ ] Test: merge never overwrites a non-null value; conflicts are logged
- [ ] `app:backup` after the first CSV backfill

### Phase 2A: framework + result market
- [ ] `MarketModel` interface, `markets` config, multi-row `PredictionService`
- [ ] `ResultModel` (Poisson 1X2) + leakage test
- [ ] Generic `Evaluator` + `Baselines` per market + `Metrics` (accuracy, log loss,
      multi-class Brier, calibration table)
- [ ] `backtest --market=result_1x2` on 2+ held-out seasons
- [ ] Record results and the gate decision in CHANGELOG

### Phase 2B: the other three markets (one at a time, each gated)
- [ ] `GoalsOverUnderModel`: derived from the scoreline grid; backtest vs
      always-over/under and base rate
- [ ] `HalfMoreGoalsModel`: backtest vs always-2nd-half and base rates
- [ ] `CornersModel` (negative binomial, dispersion estimate): backtest vs the base
      rate of over at the same line
- [ ] Per-market table in CHANGELOG: baseline vs model on log loss and Brier
- [ ] Optional: calibration step, only for a market whose calibration table shows
      systematic bias; fit on earlier seasons, test on later
- [ ] Set `experimental` flags from the gate results
- [ ] `match_verifications` migration + model
- [ ] `verify:run`: 48h window, one daily batch, retry cap, `void` / `no_data`
      handling, `--match` / `--force`. Unit tests: no HTTP before eligibility,
      stops at `max_attempts`.
- [ ] Scheduler entry for `verify:run` at `verification.run_at` (the freshness
      entries are added in Phase 2D)

### Phase 2C: verification page
- [ ] Route `/verify` (local, not dev-only). Thin controller. Read-only: it never
      calls an API and never triggers `verify:run`.
- [ ] Default view = the most recent day that has verified matches. An "Awaiting
      verification" tab lists recent and upcoming matches with their
      `first_eligible_at` and attempts left.
- [ ] Day view (display timezone): kickoff, teams, FT score (HT score), then one
      chip per market: predicted outcome + probability, actual, status
      (green check = correct, red cross = wrong, gray = waiting for the 48h window
      or partial, "no data" after attempts run out, "no prediction" when none was
      stored)
- [ ] Day summary: correct / total per market
- [ ] Rolling 30-day hit rate per market next to its baseline
- [ ] Date picker with previous/next day; filter by league and market
- [ ] Only `is_backtest = false`, current live `model_version`; experimental
      markets labelled
- [ ] Mobile-first cards, icons plus text labels, tokens from UI_GUIDELINES.md
- [ ] Shows "last evaluated at" and why a chip is gray (waiting for the 48h window /
      corners not available yet / attempts exhausted)
- [ ] Feature test: a seeded finished match renders green and red correctly

### Phase 2D: freshness and auto-refresh (build right after 2A)
How it works:
- **Probes (cheap, due-only).** football-data.org: one windowed matches call per
  active competition (`dateFrom` today minus 3 days, `dateTo` today plus 7 days).
  The response is stored in `api_calls` and diffed against the DB. CSV: `HEAD` or a
  conditional GET; if the server gives no usable headers, download and hash.
- **Diff.** Each difference becomes a `source_changes` row: new finished match,
  result correction, kickoff change, postponement, new fixture, new season, CSV new
  rows or corrections. Nothing is written to `matches` yet.
- **Refresh.** `refresh:run` checks the significance rules (section 1). If met, it
  applies the STORED responses (no new calls): upserts matches with the merge rules,
  runs `standings:rebuild`, creates or recomputes `match_verifications`, marks the
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

### Phase 3 change
- [ ] One preview per match covering all enabled markets; the number-check guard
      covers every market's numbers

## 4. Phase 0 additions (checks before building)

- [ ] CSV: confirm columns for HT goals, corners, shots exist for the last 5
      seasons of the top 5 leagues; note any missing seasons or leagues
- [ ] CSV freshness: how many days after a matchday does the current-season file
      include that match and its corners? Record it.
- [ ] football-data.org: does the matches response include halfTime on the free key?
- [ ] API-Football: does `/fixtures/statistics` return corners on the free key for the
      current season, and what does it cost in quota?
- [ ] Record the real delay until each source has a finished match's goals, HT and
      corners (football-data.org, API-Football, CSV). The 48h default and the retry
      settings are tuned from this.
- [ ] football-data.org: do `dateFrom`, `dateTo` and `status` filters work on the free
      key, and does each match carry `lastUpdated`? How long after full time does a
      match show FINISHED with its final score?
- [ ] CSV: does a `HEAD` request return `ETag`, `Last-Modified` or `Content-Length`,
      and does the server honor conditional requests? Check the current-season file
      twice a day for a few days and note when it changes.
- [ ] Confirm one probe cycle (all active competitions) stays well under 10
      football-data.org calls per minute.

## 5. Gates and acceptance

- Gate for a market: beats its baseline on BOTH log loss and Brier on 2+ held-out
  seasons. Accuracy is reported but is not the gate.
- A market that fails stays `experimental`. It is not hidden and not deleted.
- Done for Part B: `/verify` shows correct colors for a real finished day, every
  enabled market has a recorded gate decision, and all tests pass.
- Verification timing tests: `verify:run` makes no HTTP call for a match before
  `first_eligible_at`, stops after `max_attempts`, makes at most one fetch per
  source per run for all eligible matches, and never runs from the page.
- Freshness tests (Phase 2D): probes respect `next_check_at`, `refresh:run` replays
  stored responses with no HTTP, and API-Football is never called by a probe.

## 6. Honest expectations (put in PROJECT_CONTEXT.md)

- 1X2: about 50-54% accuracy at best.
- Half-with-more-goals: weak. The second half usually has more goals, so the model
  may only echo that base rate. If it cannot beat "always 2nd half", it stays
  experimental.
- Corners: possibly a modest edge. Corner data for new matches can lag, so some
  chips stay gray for a while.
- Expect roughly 2 of 4 green on a typical day. 4 of 4 will be rare. The
  backtest numbers replace this guess.
- Verification lags by design: a match stays gray for about 48 hours after kickoff,
  so the page is a look-back, not a live scoreboard. A faster lane for goals-only
  markets is possible later through config, off by default.
- Freshness is only as good as the sources. football-data.org's free scores are
  delayed and the CSV may lag, so "current" means current as of the last probe, not
  live.

## 7. Order of work

1. Part A (docs), owner approves.
2. Phase 0 checks, filling the findings table.
3. Phase 1 with the new schema.
4. Phase 2A, then 2D (predictions need current data), then 2B market by market,
   then 2C. The letters are labels, not the build order.
5. Delete this file.

Do not build corners or the half model before the 1X2 pipeline and backtest work.
