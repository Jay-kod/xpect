# XPECT FOOTBALL ANALYTICS — ROLE & ACCESS PERMISSIONS ARCHITECTURE

## 1. ACCESS MODEL ARCHITECTURE

Xpect is designed as a single-user local football prediction and research platform operated directly by the owner/lead analyst. As established in `PROJECT_CONTEXT.md` and `AGENTS.md` Rule 0:
> "One user (the owner). No hosting, no auth, no multi-user."

However, to deliver an executive-grade commercial SaaS experience, the application segregates operational authority into clear, structured functional scopes.

---

## 2. FUNCTIONAL SCOPES & DOMAINS

### 2.1 Lead Analyst / Matchday Operations (Equivalent to Admin Dashboard)
- **Primary Views:** `/` (Dashboard), `/today` (Today's Fixtures Hub)
- **Authorized Capabilities:**
  - View live fixtures, matchday countdowns, and real-time scores across all 6 leagues.
  - Filter match feeds by competition, season, date, kickoff time slots (Early 12:30, Afternoon 15:00, Evening 17:30+), and match status.
  - Inspect Poisson probabilities, fair implied odds, and multi-system analytical divergence.
  - Trigger live score synchronization (`/matches/sync-live`).
  - Generate single-match Poisson predictions and batch today predictions.
  - Generate and review verified AI match reasoning summaries.

### 2.2 Model Architect & Research Workspace (Equivalent to Super-Admin Authority)
- **Primary Views:** `/track-record`, `/ai-reasoning`, `/motm`
- **Authorized Capabilities:**
  - View 10-bin calibration curves, Brier scores, and Log Loss metrics compared to Always-Home, Uniform, and Historical baselines.
  - Run counterfactual match simulations with custom parameters in the AI Reasoning Studio.
  - Inspect and enforce the Anti-Hallucination Guard (`NumberCheckGuard`).
  - Review Man of the Match awards, player performance ratings (0–10 scale), and verified research citations.
  - Trigger batch evaluations across completed historical matches.

### 2.3 System Administrator & Infrastructure Auditor
- **Primary Views:** `/system-health`, `/settings`, `/dev/api`
- **Authorized Capabilities:**
  - Monitor data integrity, coverage timelines, and sync frequency across all active leagues.
  - Inspect live and historical API request/response logs in full raw detail with secrets redacted.
  - Configure external API credentials (football-data.org, API-Football, Claude, Gemini, OpenAI) with connectivity probes.
  - Execute database maintenance, historical CSV backfills, and SQLite backups.
  - Manage application theme presets and customize browser-local palettes (`/themes`).
