# ARCHITECTURE.md

Principle: the simplest architecture that works. Single user, local machine.
No microservices, no queue server, no Redis in v1.

## 1. Stack

- Laravel (PHP), SQLite, Artisan commands, Laravel scheduler.
- Tailwind CSS for styling and UI token definitions.
- Blade (+ one small Vue 3 component) for the dev-only API inspector from
  Phase 1. The full UI waits until the model is proven (Phase 4).
- The shared Blade layout defines appearance tokens and applies the browser's
  selected theme; `/themes` stores built-in selection and custom palettes in
  localStorage. Built-in presets are fixed; the active preset can be edited in
  a live preview and saved as a custom copy. Custom palettes support create,
  edit, and delete without server-side or database persistence.
- Claude API for previews (model name from `.env`).

## Local Docker development

- `compose.yaml` runs the Laravel app, an isolated PHPUnit test service, and an
  optional Vite asset builder.
- The app uses SQLite in a named Docker volume at `/data/xpect.sqlite`; tests
  use an in-memory SQLite database. Composer dependencies are installed into
  the ignored host `vendor` directory and shared with the containers.
- The app is published only on `127.0.0.1:8081`. The container listens on port
  8080 internally, so it does not interfere with a host development server.
- Only the app starts with default `docker compose up`; the test and asset
  services use the `tools` profile. The app has an HTTP health check and
  `always` restart policy so it starts whenever the Docker engine starts.
- `start-xpect.ps1` initializes local environment settings, applies migrations,
  seeds only a newly initialized SQLite volume, builds missing frontend assets,
  and waits for HTTP readiness. `stop-xpect.ps1` stops only the app container.
  The app starts again on the next Docker engine start, even if it had been
  manually stopped before Docker shut down.
- `.env` remains local and is excluded from the Docker build context. It is
  mounted only into the local app/test containers.

## 2. Data flow

```
 External sources                     Local app (Laravel)
 ----------------                     -------------------
 football-data.org  --\
 Football-Data.co.uk --\            +-----------------+
 API-Football        ----> Adapter -> LoggedHttpClient -> api_calls (raw, full)
 Understat           --/            +-----------------+        |
 Elo source         --/                     |                  | (parse from here;
                                            v                  |  replayable)
                                  Parsers -> matches, standings_snapshots,
                                             team_ratings, match_stats
                                            |
                                            v
                              FeatureBuilder(team, asOf)   <- leakage-safe
                                            |
                                            v
                                  PoissonModel -> predictions (immutable)
                                            |
                              +-------------+-------------+
                              v                           v
                     PreviewGenerator              Evaluator (after FT)
                   (LLM via LoggedHttpClient)      -> prediction_evaluations
                              |
                              v
                           previews
```

## 3. Directory layout

```
app/
  Console/Commands/
    SyncMatches.php            sync:matches {competition} {--season=}
    RebuildStandings.php       standings:rebuild {competition}  (from matches, no API cost)
    BackfillHistory.php        backfill:history {competition} {--seasons=} {--from-cache}
    Predict.php                predict {match_id|--next=|--due}
    Backtest.php               backtest {competition} {--seasons=} {--model=}
    EvaluatePredictions.php    evaluate:predictions
    ImportOdds.php             odds:import {competition?} {--seasons=}
    GeneratePreview.php        preview:generate {prediction_id}
    SourcesProbe.php           sources:probe  (due-only cheap probes, no calls before next_check_at)
    RefreshRun.php             refresh:run {--force}  (applies stored responses, zero new HTTP)
    Backup.php                 app:backup
  Http/
    Controllers/Dev/ApiInspectorController.php   read-only inspector (section 10)
    Clients/
      LoggedHttpClient.php     THE ONLY place outbound HTTP happens
      RateLimiter/QuotaGuard.php   per-source throttle + daily quota
  Services/
    DataSources/
      Contracts/DataSourceAdapter.php
      FootballData/FootballDataAdapter.php (+ Parser)
      FootballDataCsv/CsvBackfillAdapter.php
      ApiFootball/ApiFootballAdapter.php        (later phase)
      Understat/UnderstatAdapter.php            (later phase)
    Teams/TeamResolver.php     alias -> team_id (only way to match names)
    Features/FeatureBuilder.php   build(Team, CarbonImmutable $asOf): TeamFeatures
    Models/PoissonModel.php       predict(Features $home, Features $away): Probabilities
    Predictions/PredictionService.php
    Evaluation/Evaluator.php, Baselines.php, Metrics.php
    Previews/PreviewGenerator.php, PromptBuilder.php
  Data/                        DTOs/value objects (TeamFeatures, Probabilities, ...)
config/football.php            all tunable parameters
config/api_catalog.php         machine copy of API_CATALOG.md
public/brand/                  logo and mark SVGs
routes/dev.php                 /dev/api routes, loaded only when APP_ENV=local
tailwind.config.js             Tailwind config with xp colour tokens (UI_GUIDELINES.md)
tests/
  Unit/ Feature/ Fixtures/     fixtures = real responses copied from api_calls
```

`{competition}` in every command is the competition slug, for example
`premier-league`.

## 4. LoggedHttpClient (core requirement)

Every external request in the app goes through this class. No exceptions.

Behavior:
1. Accept: source, method, url, params, body, headers, `triggered_by` (command
   name), `counts_toward_quota`.
2. **Cache check:** look in `api_calls` for the same source + method + url +
   params with status 2xx inside the TTL. If found and `--fresh` is not set,
   return the stored response and insert a marker row in `api_calls`
   (`served_from_cache = true`, `cached_from_call_id` = the original call, no
   body, no quota used). Replay mode (below) is not a call and creates no rows.
3. **Quota/throttle check** via `QuotaGuard` BEFORE sending. If the daily limit
   (minus a reserve) is reached, refuse with a clear error. Sleep to respect
   per-minute limits.
4. Send the request.
5. **Always** insert an `api_calls` row: request params/body, redacted headers,
   response status, full raw response body, duration_ms, error (if any),
   timestamps, token counts for LLM calls.
6. **Failure detection:** a call is a failure if the HTTP status is not 2xx OR
   the provider reports errors in the body (API-Football returns an `errors`
   field in its response envelope; a non-empty `errors` is a failed call even on
   HTTP 200). Failures are logged like any other call and never cached.
7. **Redaction:** remove `Authorization`, `X-Auth-Token`, `x-apisports-key`, and
   any header/param matching the secrets list in `config/football.php` BEFORE
   persisting. Keys must never reach the DB, logs, or console output.
8. Return a typed response object to the adapter.

Cache TTL guidance (in config): finished-season data = forever; current
fixtures = 6-12 hours; matchday live results = 5-15 minutes; LLM calls = never
cached automatically (each preview run is saved, but regenerating is allowed).

Replay mode: adapters accept `--from-cache`. They read the latest stored
`api_calls` rows and re-parse with zero HTTP. This makes parser fixes free.

## 5. Provenance

Every parsed row (matches, standings_snapshots, team_ratings, match_stats)
stores `source_api_call_id`. Any number in the DB can be traced to the exact raw
response that produced it.

## 6. Feature building (leakage-safe)

`FeatureBuilder::build(Team $team, CarbonImmutable $asOf)` only queries matches
with `kickoff_at < $asOf` and status finished.

v1 features (all parameters in `config/football.php`):
- Window: last N league matches (default 20), crossing season boundaries.
- Time decay: weight = 0.5 ^ (days_ago / half_life_days) (default 60).
- Separate home and away attack/defence strengths.
- Shrinkage toward 1.0 for small samples: `(sum_w * ratio + k) / (sum_w + k)`,
  k default 3. Promoted teams without history therefore start near league
  average and are flagged `low_confidence`.
- League averages (home goals, away goals per match) computed from the same
  `asOf`-bounded data.
- Also computed and stored (used by the LLM, optional in the model): form points
  last 5, rest days, points gap to title/Europe/relegation.

## 7. Poisson model (v1)

```
lambda_home = home_attack(H) * away_defence(A) * league_avg_home_goals
lambda_away = away_attack(A) * home_defence(H) * league_avg_away_goals
```

Score grid 0..8 goals each side with independent Poisson. Output: p_home,
p_draw, p_away, expected goals, most likely scoreline, top 5 scorelines.

Model versions:
- `v1.0.0-poisson`: Base independent Poisson with goals-only attack and defence.
- `v1.1.0-xg`: Blended 70% xG chance creation and 30% finishing conversion.
- `v1.2.0-dixon-coles`: Implements Dixon-Coles (1997) parameter $\tau(x, y)$ with $\rho = -0.06$ adjusting low scores (0-0, 1-1, 1-0, 0-1) and renormalizing grid (improves Log Loss to 0.9831).
- `v1.2.1-h2h`: Incorporates shrinkage-adjusted Head-to-Head goal ratio strictly prior to $asOf$ (best model: 54.5% accuracy, 0.9704 Log Loss).

## 8. Evaluation

- **Walk-forward backtest:** iterate matches chronologically, build features with
  `asOf = kickoff`, predict, store with `is_backtest = true`.
- **Baselines:** (a) always home, (b) uniform 1/3 each, (c) historical base rates
  of H/D/A from data before the test period.
- **Benchmarks (Phase 8):** Bookmaker consensus odds (Bet365, Market Average) with
  proportional margin/overround removal, evaluated on the exact same multi-class Log Loss
  and Brier score metrics.
- **Metrics:** accuracy, log loss, Brier score, calibration table (10 bins).
- Live predictions are evaluated automatically after full time by
  `evaluate:predictions`, writing to `prediction_evaluations`.

## 9. AI previews

- Input to the LLM: a JSON object containing probabilities, expected goals, key
  features, and team names. Nothing else.
- System prompt rules: use only provided numbers; no outside facts about players,
  injuries, or history; mention uncertainty; do not contradict the model's
  probabilities; keep to a word limit.
- Stored: full prompt, full response, model string, token counts (in
  `api_calls`), plus the final text and `prompt_version` in `previews`.
- Guard (Phase 3): after generation, verify every number in the text exists in
  the input JSON; flag the preview if not.

## 10. API inspector (dev page)

Purpose: while developing, see exactly what the app calls and what comes back,
without opening the database. A read-only view over `api_calls`, plus a catalog
of what could be called.

Access: route `/dev/api`, registered ONLY when `APP_ENV=local` (`routes/dev.php`).
Local machine only. Read-only. Never shows unredacted headers (redaction happens
before saving, see section 4).

Tabs:
1. **Call log.** One row per call: time, source, method, endpoint + params,
   status (colored), duration, cache hit or live, triggered_by, rate-limit
   headers (remaining quota), response size. Filters: source, status, command,
   date. Auto-refreshes every few seconds so you can watch a sync run live.
2. **Call detail.** Request params/body, redacted headers, response headers,
   pretty-printed response body (collapsible), the error if any, and which tables
   the call produced rows in (via `source_api_call_id`). For LLM calls: the full
   prompt, the response, and token counts. Button: "Save as test fixture"
   (copies the response into `tests/Fixtures/`).
3. **Quota panel.** Per source: used today, daily limit, per-minute remaining
   from the latest response headers, time to reset.
4. **Catalog.** Reads `config/api_catalog.php`. For each endpoint: status
   (DOC / MEM / VERIFIED), last called, last HTTP status, and the EXPECTED shape
   = key paths of the latest successful saved response. A "Try it" button (GET
   only) goes through `LoggedHttpClient`, so it is logged and quota-guarded. For
   rate-limited sources it shows remaining quota and asks for confirmation first.

Expected vs actual (schema drift check): after any call, compare the key paths of
the new response with the expected shape. Show missing keys, new keys, and type
changes. If an API changes under you, this is where you see it first. It is also
the evidence for rule 8 in `AGENTS.md` ("stop if a real response differs from the
docs").

Implementation: Blade + plain vanilla JavaScript (native fetch polling and DOM rendering) over a
JSON endpoint `/dev/api/calls`. No build step, no CDN, and no new dependencies needed. The inspector UI is styled using the `UI_GUIDELINES.md` tokens.

## 11. Freshness and auto-refresh (Phase 2D)

Xpect watches external data sources cheaply, records what changed, and refreshes the database automatically once significant new data has arrived.

### How it works:
- **Probes (cheap, due-only).** Only two sources are probed for changes:
  football-data.org and the CSVs. API-Football is NEVER probed (preserves quota);
  it is used only inside `verify:run`.
  - Cadence: a competition is "active" if a match kicked off in the last 8 hours
    or kicks off within 2 hours. Active: probe every 120 minutes. Otherwise
    every 24 hours. CSVs: every 12 hours. `source_checks.next_check_at` strictly
    enforces this.
  - football-data.org probe: one windowed matches call per active competition
    (`dateFrom` today minus 3 days, `dateTo` today plus 7 days). The response is
    stored in `api_calls` and diffed against the DB.
  - CSV probe: `HEAD` request or conditional GET (`If-None-Match`, `If-Modified-Since`);
    if the server gives no usable headers, download and content-hash.
- **Diff engine.** Compares the stored response against existing DB state. Each
  difference is logged as an append-only row in `source_changes`: `new_finished`,
  `result_correction`, `kickoff_change`, `postponed`, `new_fixture`, `new_season`,
  `csv_new_rows`, or `csv_correction`. Nothing is written to `matches` yet.
- **Refresh (`refresh:run`).** Replays stored responses from `api_calls` with ZERO
  new HTTP calls. Triggers when any significance rule is met:
  1. 5 or more unapplied `new_finished` or `result_correction` changes;
  2. Any `kickoff_change`, `postponed`, `new_fixture`, or `new_season` touching a
     match in the next 7 days;
  3. At least 1 unapplied change and the last refresh is older than 24 hours.
  - Minimum gap between refreshes: 2 hours (unless `--force`).
  - Action: applies updates to `matches` with merge rules, runs `standings:rebuild`,
    creates or recomputes `match_verifications`, marks changes applied with
    `applied_at` and `refresh_run_id`, and writes a `sync_runs` row. It never
    touches existing predictions.
- **Predict due (`predict --due`).** Predicts matches kicking off within
  `prediction_lead_hours` = 24 hours that have no prediction yet, but refuses if
  the competition's data is older than `max_data_age_hours` = 12 hours. If stale,
  it runs a probe first (within quota) or skips and logs a warning. Existing
  predictions are never regenerated.
- **Visibility.** The API inspector includes a read-only "Sources" tab showing per
  resource: last checked, next check, last changed, unapplied changes by kind,
  last refresh, and data age. The verification page (`/verify`) displays a "Data
  last refreshed at ..." timestamp.

## 12. Scheduling (Laravel scheduler, local machine)

- Every 30 minutes: `sources:probe` (checks whether any `next_check_at` has passed; does nothing otherwise).
- Every 30 minutes: `refresh:run` (evaluates significance rules against unapplied `source_changes`; replays stored responses with zero new HTTP).
- Daily: `verify:run` (at `verification.run_at`, verifies completed matches past their eligibility window; only fetches fields still missing after refresh).
- Daily: `evaluate:predictions` (pure DB evaluation of finished matches).
- Manual: `sync:matches` remains as the manual command that `refresh:run` reuses.
- All commands are idempotent and quota-aware.

## 13. Backups

`php artisan app:backup` copies the SQLite file to
`storage/backups/xpect-YYYYmmdd-HHMM.sqlite` and keeps the last N copies (N in
`config/football.php`). Run it after every backfill and before any migration that
changes existing tables. Reason: `api_calls` holds raw data that costs API quota
to fetch again (API-Football allows about 100 calls/day), so losing the file is
expensive. Backups stay out of git.

## 14. Error handling

- Adapter failures never crash a run. Log, record in `sync_runs`, continue.
- Unknown team names: skip the row, record in `sync_runs.summary`, never
  auto-create teams.
- Parser errors are fixable via replay without new API calls.

## 15. Security

- Secrets only in `.env`; `.env.example` committed without values.
- Redaction before persistence (see section 4).
- Serve only on 127.0.0.1 (the `php artisan serve` default). The `/dev/api`
  inspector is registered only when `APP_ENV=local`.
- SQLite file and backups excluded from git.
- Validate all command inputs; use Eloquent/query builder only (no raw SQL with
  user input).

## 16. Testing

- Unit tests: features, Poisson, metrics (hand-checked values).
- Mandatory leakage test.
- Adapter/parser tests against real saved fixtures.
- `Http::fake()` everywhere; tests never use real quota.
- Test that keys never appear in `api_calls`.
- The backtest command is itself a regression check: record the metrics per
  `model_version` in `CHANGELOG.md`.

## 17. Appearance preferences

- `/themes` manages the four built-in palettes and browser-local custom palettes.
- Built-in palettes are fixed. Custom palettes support creation, color editing,
  saving, selection, and deletion; deleting the active custom palette falls back
  to Pitch Black.
- Editing the current built-in palette previews changes live and saves them as
  a new custom palette; it never overwrites the preset.
- The shared layout validates stored colors as six-digit hex values and applies
  the selected palette before rendering page styles. The default is Pitch Black.
- Palette changes affect presentation only; they do not change prediction
  calculations, match data, or model configuration.

## 18. Date navigation defaults

- The shared date navigator displays the current UTC date on every application
  page without applying a date filter by itself.
- The dashboard match feed defaults to today's UTC fixtures, including an empty
  today's state when no fixtures are scheduled. Explicit date, status, season,
  and time filters continue to take precedence.
- The `/today` fixtures hub already defaults its data to today's UTC date.
- Historical analysis pages such as `/motm` and `/ai-reasoning` retain their
  existing content until the user applies a date filter.

## 19. Verified MOTM research

`motm:research` runs at the end of the UTC day for finished matches, prioritizing
configured high-interest teams and respecting `football.motm.daily_limit`.
Gemini is called through `LoggedHttpClient` with Google Search grounding. The
application stores a MOTM only when the structured response explicitly names the
winner, gives a reason, identifies one of the fixture teams, and includes at
least one valid source URL. The `/motm` page never falls back to the algorithmic
evaluator and shows only `verification_status=verified` rows.
