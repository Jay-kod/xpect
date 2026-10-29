# AGENTS.md: Standing Orders for the Coding Agent

Read this file fully at the start of every session. Re-apply it on EVERY prompt.
(Claude Code: copy this file to `CLAUDE.md`. Cursor: use `.cursorrules`.)

## 0. The project in one line

Xpect is a local-only Laravel tool: verified football data -> computed stats ->
Poisson probabilities -> AI-written previews, with every prediction tracked
against the real result. One user (the owner). No hosting, no auth, no
multi-user.

Docs to read before acting: `PROJECT_CONTEXT.md`, `ARCHITECTURE.md`,
`DATABASE.md`, `API_CATALOG.md`, `UI_GUIDELINES.md`, `TODO.md`.

`TODO.md` is the master prompt. Its MASTER PROMPT section tells you what to do at
the start of every session, and its CURRENT PHASE decides what you work on.

If two docs disagree, stop and report it. Do not pick one silently.

---

## 1. Per-prompt protocol (run on EVERY prompt, in this order)

1. **Read** the docs above. Read the SESSION LOG at the bottom of
   `PROJECT_CONTEXT.md` to see where the last session stopped. If the prompt
   touches a file you have not seen this session, read it before editing.
   Never edit from memory.
2. **Classify** the prompt in one line: `PLAN | BUILD | DEBUG | REVIEW | LEARN`.
3. **Restate** the task in 1-3 lines, plus any assumptions you are making.
   - If an assumption affects schema, data correctness, or leakage: ASK first.
   - Otherwise state it and proceed.
4. **Phase gate:** check `TODO.md`. If the task belongs to a later phase or
   checkpoint, say so and ask before doing it. No scope creep.
5. **Smallest change** that solves the problem. Do not rewrite working code.
   Do not refactor unrelated files.
6. **Implement with tests** (section 5).
7. **Verify:** run the tests and/or the artisan command. Show real output.
   Never claim something works without running it.
8. **Sync docs:** tick `TODO.md`, add a `CHANGELOG.md` entry (create the file if
   missing), update `DATABASE.md` if the schema changed, `ARCHITECTURE.md` if the
   structure changed, and `API_CATALOG.md` when an endpoint is added or
   verified. Append an entry to the SESSION LOG in `PROJECT_CONTEXT.md`.
9. **Report** using the format in section 8.

## 2. Hard rules (never break)

1. **No data leakage.** Any feature for a match is computed ONLY from data dated
   strictly before that match's kickoff. Feature functions take
   `(team, asOf: CarbonImmutable)`. No exceptions, including "just for testing".
2. **Every outbound HTTP call goes through `LoggedHttpClient`.** No raw
   `Http::get()` or Guzzle calls anywhere else. This includes the Claude API.
   Sole exception: the throwaway Phase 0 `probe.php`, which lives outside `app/`
   and is deleted once the inspector works.
3. **Every call is saved to `api_calls`:** request (params/body), response
   (status + full raw body), duration, errors, who triggered it. Never skip
   logging, even on failure.
4. **Never store secrets.** Redact API keys and auth headers BEFORE saving to
   `api_calls`. Keys live only in `.env`. Never print keys in logs or output.
5. **Predictions are immutable.** Never UPDATE or DELETE rows in `predictions` or
   `api_calls`. To change a model, bump `model_version` and create new
   predictions.
6. **The LLM never decides the prediction.** It only explains numbers computed by
   the model. It receives structured data only and must not add outside football
   facts.
7. **Never fabricate football data.** If a value is missing, store NULL and say
   so. Never invent scores, stats, team names, IDs, endpoints, or columns.
8. **Verify before you assume.** Third-party API shapes (football-data.org,
   API-Football, Understat, Elo source) are UNVERIFIED until checked against a
   real response saved in `api_calls`. If a real response differs from the docs
   in this repo, STOP and report the difference. Do not code around it silently.
9. **Migrations are append-only.** Never edit a migration that has been run.
   Create a new one.
10. **Respect quotas.** Check the quota guard before any call to a rate-limited
    source. Never loop API calls without the cache check and throttle.
11. **No new dependencies** without asking, and say why.
12. **Never disable a safety measure** (validation, redaction, leakage guard,
    quota guard) to make something work.
13. **Only call endpoints listed in `API_CATALOG.md`.** To use a new one, add it to
    the catalog (and `config/api_catalog.php`) first. Mark it VERIFIED only after
    a real saved response confirms its shape.
14. **The API inspector is read-only and local-only.** It never shows unredacted
    headers and is never registered outside the local environment.
15. **All UI colours come from the tokens in `UI_GUIDELINES.md`.** No hard-coded colours.

## 3. Stop-and-ask conditions

Stop and ask the owner (do not guess) when:
- A real API response does not match what the docs assume.
- A task needs a schema change not described in `DATABASE.md`.
- A task belongs to a later phase or checkpoint.
- Tests fail twice in a row and the cause is unclear.
- A change would touch `predictions` or `api_calls` history.
- Two reasonable designs exist and the choice affects the model's accuracy.

## 4. Code standards (Laravel)

- Controllers stay thin (mostly a CLI tool; the inspector controller is thin too).
- Business logic lives in `app/Services/*`. Single-purpose classes.
- Use Form Requests for validation, Eloquent relationships, Jobs for background
  work, and Artisan commands as the main interface in v1.
- Config values (window size, decay, thresholds, TTLs) live in
  `config/football.php`, never hard-coded in services.
- Type everything: strict types, return types, DTOs/value objects for features
  and probabilities (no loose associative arrays across service boundaries).
- All timestamps are stored in UTC.
- Follow SOLID/DRY, but do not over-engineer. This is a single-user local tool.

## 5. Testing rules

- Every service gets unit tests. Pure functions (features, Poisson) get
  table-driven tests with hand-checked expected values.
- **Leakage test (mandatory):** a test proving that adding a match dated after
  `asOf` does NOT change the features for that `asOf`.
- Adapters are tested against saved fixtures (real responses copied from
  `api_calls`), never live APIs.
- HTTP in tests uses `Http::fake()`. Tests never spend real API quota.
- A task is not done if tests are missing or failing.

## 6. Data and model rules

- Raw first: store the raw response, then parse. Parsing bugs must be fixable by
  re-running from `api_calls` with zero new API calls (`--from-cache`).
- Team names are resolved ONLY through `team_aliases`. Never match on raw name
  strings elsewhere. Unknown names are logged and skipped, never auto-created.
- Bump `model_version` on ANY change to features, parameters, or formula.
- Judge changes by backtest (log loss + Brier + accuracy vs baselines), not by
  intuition. Keep a feature only if it improves held-out results.
- If backtest accuracy looks too good (above roughly 58%), assume leakage first
  and investigate before celebrating.

## 7. Behavior and tone

- Be direct, concise, and technically precise. No filler, no hype, no
  motivational talk.
- Do not agree just to agree. If the owner's idea is weak, overscoped, or will
  hurt accuracy or data integrity, say so, explain why, and propose a better
  option.
- Recommend one best approach; mention alternatives only when they matter.
- When teaching, explain the reasoning, not only the answer.
- Admit uncertainty. "I did not verify this" beats a confident guess.
- Prefer boring and simple over clever.

## 8. Report format after every task

```
MODE: BUILD | DEBUG | ...
DONE: what changed, in 1-4 lines
FILES: created/edited list
VERIFY: exact command(s) run + the key output
DOCS: which docs/TODO/CHANGELOG/session log were updated
RISKS / ASSUMPTIONS: anything unverified or decided on the owner's behalf
NEXT: the single next step per TODO.md
```

## 9. Definition of done (all must be true)

- [ ] Works when run (shown, not claimed)
- [ ] Tests written and passing, including the leakage test where relevant
- [ ] All HTTP goes through `LoggedHttpClient` and is logged
- [ ] No secrets in code, logs, or DB
- [ ] Docs, `TODO.md`, `CHANGELOG.md`, `API_CATALOG.md` (if touched) and the
      session log updated
- [ ] No unrelated changes in the diff
