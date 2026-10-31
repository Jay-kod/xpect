# API_CATALOG.md

Every external endpoint Xpect may call. The agent may ONLY call endpoints listed
here. To use a new one: add it here and to `config/api_catalog.php` first, then
build.

Status legend:
- DOC = seen in provider or third-party documentation
- MEM = written from memory, must be checked
- VERIFIED = confirmed by a real saved response in `api_calls` (the agent
  upgrades the status only after reading the saved response, and notes the date
  and what it checked)

Machine copy: `config/api_catalog.php` (used by the inspector page). Keep both in
sync.

---

## 1. football-data.org (main backbone)

- Base URL: `https://api.football-data.org/v4`
- Auth: header `X-Auth-Token: <token>` (never in the URL, never saved to the DB)
- Free plan: 12 competitions, 10 requests/minute, delayed scores
- Anonymous: 100 requests/24h, area and competition lists only
- Response headers to read: `X-Requests-Available-Minute`, `X-RequestCounter-Reset`
- Errors: 403 = missing/invalid token or restricted resource, 429 = rate limited

| Endpoint | Purpose | Status |
|---|---|---|
| `GET /competitions` | Competitions available on your plan | DOC |
| `GET /competitions/{code or id}` | Competition detail + list of seasons | DOC |
| `GET /competitions/{code}/matches?season=YYYY` | Fixtures and results | VERIFIED (2026-10-28: code 'PL', season 2026 and 2023 OK, <=2022 403 Forbidden) |
| `GET /competitions/{code}/standings` | League table (cross-check only; we compute our own) | DOC |
| `GET /competitions/{code}/teams?season=YYYY` | Teams in a season | DOC |
| `GET /teams/{id}` | Team detail | DOC |
| `GET /persons/{id}` and `/persons/{id}/matches` | Player data (not planned) | DOC |

Optional request headers `X-Unfold-Lineups`, `X-Unfold-Bookings`, `X-Unfold-Subs`,
`X-Unfold-Goals` control deeper match fields (DOC). Likely limited on the free
plan. Do not rely on them until verified.

Competition codes: `WC` (DOC). `PL` (VERIFIED: confirmed 2026-10-28). `PD`, `BL1`, `SA`, `FL1`, `CL` (MEM).

## 2. API-Football (lineups, injuries, other leagues; later phases)

- Base URL: `https://v3.football.api-sports.io`
- Auth: header `x-apisports-key: <key>` (never saved to the DB)
- Free plan: 100 requests/day, all endpoints but limited seasons
- Response envelope: `get, parameters, errors, results, paging, response`
- **Check `errors` in the body on every call.** Do not trust the HTTP status
  alone. Treat a non-empty `errors` as a failed call.
- Rate headers: `x-ratelimit-requests-limit` and `x-ratelimit-requests-remaining`
  (daily), `X-RateLimit-Limit` and `X-RateLimit-Remaining` (per minute)

| Endpoint | Purpose | Status |
|---|---|---|
| `GET /status` | Plan and quota usage | DOC |
| `GET /leagues?country=&season=` | League list and coverage | DOC |
| `GET /countries` | Country list | DOC |
| `GET /teams?id=` or `?name=` | Team lookup | DOC |
| `GET /fixtures?league=&season=` | Fixtures by league and season | DOC |
| `GET /fixtures?from=&to=&round=&status=&live=` | Filtered fixtures | DOC |
| `GET /injuries?league=&season=` | Injuries | MEM (path) |
| `GET /fixtures/lineups?fixture=` | Lineups | MEM |
| `GET /standings?league=&season=` | League table | MEM |
| `GET /fixtures/statistics?fixture=` | Match stats | MEM |
| `GET /predictions?fixture=` | Provider predictions (not used; we build our own) | DOC |
| `GET /odds` | Odds | MEM |

Premier League id `39` (MEM).

## 3. Football-Data.co.uk CSV (free history and backfill fallback)

- URL pattern: `https://www.football-data.co.uk/mmz4281/{SSSS}/{DIV}.csv` where
  `SSSS` is the season code, for example `2425` for 2024/25 (VERIFIED: confirmed 2026-10-28 across 2020/21 - 2024/25)
- Division codes: `E0` Premier League, `SP1` La Liga, `D1` Bundesliga, `I1` Serie
  A, `F1` Ligue 1 (VERIFIED: confirmed 2026-10-28)
- Static files, no key. Results, and usually bookmaker odds columns (VERIFIED: confirmed date DD/MM/YYYY, goals FTHG/FTAG, UTF-8 BOM on header)
- Be polite: download each file once and store the raw file via `LoggedHttpClient`

## 4. AI Reasoning & Preview APIs (Claude, ChatGPT, Gemini)

- Called only by `PreviewGenerator`, through `LoggedHttpClient`.
- Provider and model name configured in `.env` (`AI_PROVIDER`, `AI_MODEL`, `ANTHROPIC_MODEL`, `OPENAI_MODEL`, `GEMINI_MODEL`), never hard-coded.
- Keys stored in `.env` only. Full prompts and raw responses saved in `api_calls` with keys redacted.

### Anthropic Claude
- Base URL: `https://api.anthropic.com/v1`
- Auth: `x-api-key: <key>`
- Endpoint: `POST /messages` (Status: DOC)

### OpenAI ChatGPT
- Base URL: `https://api.openai.com/v1`
- Auth: `Authorization: Bearer <key>`
- Endpoint: `POST /chat/completions` (Status: DOC)

### Google Gemini
- Base URL: `https://generativelanguage.googleapis.com/v1beta`
- Auth: `x-goog-api-key: <key>`
- Endpoint: `POST /models/{model}:generateContent` (Status: DOC)

## 5. Understat (xG for Top 5 European Leagues)

- Base URL: `https://understat.com`
- Pattern: `GET /getLeagueData/{league}/{season}` (VERIFIED: confirmed 2026-10-28) where:
  - `{league}`: `EPL` (Premier League), `La_liga` (La Liga), `Bundesliga` (Bundesliga), `Serie_A` (Serie A), `Ligue_1` (Ligue 1)
  - `{season}`: starting year (e.g., `2024` for 2024/25, `2023` for 2023/24)
- Response: JSON object with keys `dates`, `teams`, `players`.
  - `dates`: array of match objects with `id`, `isResult` (bool/int), `datetime` (`YYYY-MM-DD HH:MM:SS` UTC), `h` (`title`, `short_title`), `a` (`title`, `short_title`), `goals` (`h`, `a`), `xG` (`h`, `a` floats), `forecast` (`w`, `d`, `l` floats).
- Headers: `User-Agent`, `Accept: application/json, text/javascript, */*; q=0.01`, `X-Requested-With: XMLHttpRequest`
- Status: `VERIFIED` (confirmed with real response saved in `api_calls` for EPL 2024, 380 matches).
- Fallback pattern: `GET /league/{league}/{season}` for HTML page.

## 6. Later phases (not for v1)

- Elo ratings source: undecided, check availability first.

---

## Rules for the agent

1. Only call endpoints in this file.
2. Any new endpoint goes into this file AND `config/api_catalog.php` first.
3. Mark an endpoint VERIFIED only after reading a real saved response in
   `api_calls`. Note the date and what you checked.
4. If a real response differs from what is written here, stop and report it.
