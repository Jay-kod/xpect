# XPECT FOOTBALL ANALYTICS — API & INTEGRATION ASSESSMENT

This report audits all outbound API integrations, authentication mechanisms, quota tracking, error handling, and security measures in Xpect.

---

## 1. INTEGRATION OVERVIEW

| Integration Provider | Transport / Client | Authentication Mechanism | Quota & Rate Limit | Status & Health |
|---|---|---|---|---|
| **football-data.org** | `LoggedHttpClient` (Guzzle/cURL) | `X-Auth-Token` header (read from `.env`) | 10 calls/min (tracked via `api_calls`) | Verified Active. Headers redacted before logging. |
| **Football-Data.co.uk** | Local / HTTP CSV stream | Public static historical repositories | Unlimited / Local files | Verified Active. Historical fallback for 2020–2025. |
| **API-Football** | `LoggedHttpClient` | `x-apisports-key` header | Free tier: 100 requests/day | Verified Active. Used for verified lineups and injuries. |
| **Understat xG** | `LoggedHttpClient` | Public page scraper / parser | Polite crawling / cached | Verified Active. Regex extraction of embedded JSON xG stats. |
| **Google Gemini API** | `LoggedHttpClient` | API key query parameter | Pay-as-you-go / Free tier | Verified Active (`gemini-3-flash-preview`). Keys masked. |
| **Anthropic Claude API** | `LoggedHttpClient` | `x-api-key` header | Pay-as-you-go | Verified Active. Model specified in `.env`. |
| **OpenAI ChatGPT API** | `LoggedHttpClient` | `Authorization: Bearer` header | Pay-as-you-go | Verified Active. Redacted in all call records. |

---

## 2. STRICT SECURITY & REDACTION COMPLIANCE

In strict accordance with `AGENTS.md` Hard Rules:
1. **Rule 2: Every outbound HTTP call goes through `LoggedHttpClient`.**
   - No raw `Http::get()` or Guzzle calls exist anywhere in the application logic.
2. **Rule 3: Every call is saved to `api_calls`.**
   - Request parameters, request body, status code, duration in milliseconds, and full raw response body are immutably persisted.
3. **Rule 4: Never store secrets.**
   - All API keys, tokens, and authorization headers are automatically stripped and replaced with `[REDACTED]` prior to writing records to the `api_calls` database table. Keys live strictly in `.env`.
4. **Rule 8: Verification before assumption.**
   - Real responses are checked against expected response envelopes. Provider envelope errors (`errors: []`) are detected and flagged as failures without silent workarounds.

---

## 3. CACHE REPLAY & ZERO-QUOTA TESTING

- The application supports `--from-cache` operation across all data ingestion and backfill routines.
- All automated tests (`php artisan test`) execute against saved fixtures and `Http::fake()` envelopes, using exactly zero external API quota during testing.
