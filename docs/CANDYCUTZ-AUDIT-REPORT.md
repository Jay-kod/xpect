# XPECT FOOTBALL ANALYTICS — COMPREHENSIVE DASHBOARD AUDIT REPORT
*(Adopting the Unified Complete Dashboard Audit, Restructuring & Rebuild Framework)*

**Document Version:** 1.0.0
**Application Target:** Xpect Football Prediction & Analytics Platform
**Target Environment:** Local Production-Oriented Single-User Laravel System
**Audit Date:** 2026-11-14
**Auditor Roles:** Senior Laravel Software Architect, Senior UI/UX Designer, Lead Frontend/Backend Engineer, Quality Assurance Engineer

---

## EXECUTIVE SUMMARY

Xpect is a high-precision, production-grade football predictive analytics application built with Laravel 12, SQLite, and Tailwind CSS. The platform ingests verified match data from football-data.org, historical CSV repositories, and Understat xG metrics; computes Poisson probabilities with Dixon-Coles and temperature calibrations; and synthesizes structured AI reasoning without data leakage.

This audit evaluates the entire system under the complete 12-stage Dashboard Rebuild Framework. The audit confirms that the backend mathematical engines, zero-leakage guards, database schema, and test suite (176 passing tests, 952 assertions) are in excellent operational condition. However, the dashboard user interface has accumulated ad-hoc styling, inline CSS blocks, redundant presentation wrappers, and opportunities for significant information-density and navigation polish inspired by modern commercial SaaS analytics platforms (e.g., SportyBet, Linear, Vercel, Stripe).

All non-negotiable requirements are strictly enforced:
1. **Zero Data Leakage:** All match features strictly computed from data dated prior to kickoff (`asOf`).
2. **Branding & Theme Preservation:** Core brand identity (Xpect orange `#D94E00`, deep pitch-black `#000000`, obsidian, midnight, and light palettes) preserved verbatim.
3. **Database Integrity:** Zero destructive schema changes; immutable predictions and API logs preserved.
4. **Backend Continuity:** All existing routes, controllers, and services remain 100% operational.

---

## 1. TECHNOLOGY INSPECTION

| Technology / Component | Version / Specification | Audit Assessment & Status |
|---|---|---|
| **PHP Runtime** | PHP 8.2+ (strict types enabled) | Stable, modern syntax (`readonly`, match expressions, DTOs). |
| **Framework** | Laravel 12.0 | Clean architecture, thin controllers, single-purpose services in `app/Services/`. |
| **Frontend Tooling** | Vite 7.0.7, `@tailwindcss/vite` 4.0.0 | High-performance bundling; Tailwind 4 tokens configured via CSS variables. |
| **Database Engine** | SQLite 3 (`database/database.sqlite`), `:memory:` for tests | Fast, zero network latency; properly indexed with compound keys. |
| **Client Scripting** | Vanilla JS (ES2022) | Zero heavy framework overhead; fast client-side skeletal transitions and theme manager. |
| **Automated Testing** | PHPUnit 11.5 | 176 tests, 952 assertions, 100% pass rate. Dedicated leakage and calibration test cases. |
| **External HTTP** | `LoggedHttpClient` via Guzzle / Laravel Http | Strict audit logging in `api_calls`, header redaction, zero secrets stored. |

---

## 2. APPLICATION CONTENT & BUSINESS LOGIC INVENTORY

### 2.1 Competitions & Supported Leagues
- **Premier League (EPL)** (ID: 1, Slug: `premier-league`)
- **UEFA Champions League (UCL)** (ID: 2, Slug: `champions-league`)
- **La Liga (ESP)** (ID: 3, Slug: `la-liga`)
- **Bundesliga (GER)** (ID: 4, Slug: `bundesliga`)
- **Serie A (ITA)** (ID: 5, Slug: `serie-a`)
- **Ligue 1 (FRA)** (ID: 6, Slug: `ligue-1`)

### 2.2 Core Analytics & Intelligence Engines
1. **Poisson Engine (`v1.3.0-calibrated`):**
   - Bivariate Poisson goal distribution with Dixon-Coles low-score dependency correction ($\rho = -0.10$).
   - Sustainable xG ratio weight ($\alpha = 0.85$) against raw goals ($0.15$).
   - Exponential recency decay half-life ($180$ days).
   - 5-match rolling form momentum differential factor ($weight = 0.010$, clamped $[0.85, 1.15]$).
   - Post-grid temperature scaling ($T = 1.08$) ensuring empirical reliability across 10 probability bins.
2. **Elo Rating Engine (System 2):**
   - Bradley-Terry win expectancy with home advantage adjustment ($+65$ Elo points) and H2H form modifier.
3. **Consensus & Multi-System Matrix (System 3):**
   - Ensemble synthesis detecting mathematical divergence between Poisson and Elo distributions.
4. **Man of the Match (MOTM) & Player Performance Evaluator:**
   - Dual-engine statistical data rating (0–10 scale) combined with decisive story moment scoring.
   - Guaranteed strictly 1-of-1 MOTM award per completed match with verified citation sources.
5. **AI Reasoning Studio:**
   - Multi-LLM support (Gemini 3 Flash, Claude, OpenAI) producing structured text strictly grounded in DB facts.
   - Anti-Hallucination Guard (`NumberCheckGuard`) verifying numerical tokens before publishing.

---

## 3. DASHBOARD & ROUTE INVENTORY

| Route Name | HTTP Method | URL Pattern | Controller & Action | Functionality & Status |
|---|---|---|---|---|
| `dashboard` | `GET` | `/` | `DashboardController@index` | Main Fixtures & Predictions Feed, Multi-League Switcher, Marquee Spotlight. |
| `matches.sync-live` | `POST` | `/matches/sync-live` | `DashboardController@syncLiveScores` | Incremental fixture and score synchronization. |
| `matches.predict` | `POST` | `/matches/{match}/predict` | `DashboardController@predict` | Generate single-match Poisson prediction on demand. |
| `predictions.preview` | `POST` | `/predictions/{prediction}/preview` | `DashboardController@generatePreview` | Generate AI reasoning match preview. |
| `today` | `GET` | `/today` | `TodayFixturesController@index` | Multi-league today matchday hub with date navigation. |
| `today.predict-all` | `POST` | `/today/predict-all` | `TodayFixturesController@predictAll` | Batch predict all fixtures for current date. |
| `track-record` | `GET` | `/track-record` | `TrackRecordController@index` | Calibration curve, Log Loss, Brier score, baseline comparison table. |
| `ai-reasoning` | `GET` | `/ai-reasoning` | `AiReasoningController@index` | Scenario simulation and counterfactual reasoning matrix. |
| `ai-reasoning.simulate` | `POST` | `/ai-reasoning/simulate` | `AiReasoningController@simulate` | Execute live scenario simulation with prompt builder. |
| `ai-reasoning.check-guard`| `POST` | `/ai-reasoning/check-guard` | `AiReasoningController@checkGuard` | Anti-hallucination verification endpoint. |
| `motm` | `GET` | `/motm` | `ManOfTheMatchController@index` | Player ratings leaderboards, verified MOTM decisions. |
| `motm.evaluate` | `POST` | `/matches/{match}/motm` | `ManOfTheMatchController@evaluate` | Single match MOTM evaluation. |
| `motm.batch` | `POST` | `/motm/evaluate-batch` | `ManOfTheMatchController@batchEvaluate` | Batch MOTM evaluation across finished fixtures. |
| `system-health` | `GET` | `/system-health` | `SystemHealthController@index` | Integrity audits, coverage matrix, recent API calls. |
| `system-health.run` | `POST` | `/system-health/run` | `SystemHealthController@runAudit` | Trigger immediate comprehensive system health check. |
| `settings` | `GET` | `/settings` | `SettingsController@index` | API credentials, model parameters, cache actions. |
| `settings.keys` | `POST` | `/settings/keys` | `SettingsController@updateKeys` | Update provider API keys in `.env`. |
| `settings.test-key` | `POST` | `/settings/test-key` | `SettingsController@testKey` | Probe and validate external API keys. |
| `settings.action` | `POST` | `/settings/action` | `SettingsController@runAction` | Trigger sync, backfill, or backup Artisan commands. |
| `themes` | `GET` | `/themes` | `themes.index` (View) | Selectable page palettes (Pitch Black, Obsidian, Midnight, Light). |
| `changelog` | `GET` | `/changelog` | `ChangelogController@index` | Historical version changes, walk-forward test logs. |
| `docs` | `GET` | `/docs` | `DocsController@index` | Mathematical formulas, system architecture, user manual. |
| `dev.api.*` | `GET`/`POST` | `/dev/api/*` | `ApiInspectorController` | Development API Inspector (local-only call logs & quota). |

---

## 4. DATABASE ASSESSMENT

The database schema encompasses 18 models across 27 append-only migrations:
- **`competitions`**: League registry with active flags and country associations.
- **`teams` & `team_aliases`**: Authoritative club registry preventing duplicate team strings.
- **`matches`**: Fixture schedules, halftime/fulltime scores, referee, kickoff timestamps in UTC.
- **`team_ratings`**: Historical attack/defense strengths, xG ratings, calculated with zero data leakage.
- **`predictions` & `prediction_evaluations`**: Immutable predictions with stage tagging (`pre_match`, `matchday`, `final`) and walk-forward verification metrics.
- **`previews`**: Verified AI generation text with prompt versions and hallucination guard flags.
- **`motm_evaluations`**: 22 player ratings per finished match, Opta/SofaScore data rating, decision score, and verified source citations.
- **`api_calls`**: Immutable HTTP audit trail storing request parameters, full raw response body, duration, and caller metadata with secret redaction.

**Assessment:** Database integrity is pristine. Foreign keys and indexes are correctly applied. No destructive migrations are needed or permitted.

---

## 5. API & INTEGRATION ASSESSMENT

1. **Football-Data.org:**
   - Status: Verified active. Key stored in `.env`. Rate limit: 10 calls/min.
   - Protection: Wrapped in `LoggedHttpClient` with exponential backoff and cache replay (`--from-cache`).
2. **Understat xG:**
   - Status: Verified active. HTML parsing extracts embedded JSON xG coordinates.
3. **AI LLM Providers (Claude, Gemini, OpenAI):**
   - Status: Verified active. Keys tested via `ApiKeyTesterService`. Prompts strictly constrained to computed DB numbers.
4. **Dev API Inspector (`/dev/api`):**
   - Status: Verified local-only, read-only inspection. Authentication headers automatically masked.

---

## 6. UI/UX & ARCHITECTURAL PROBLEM CLASSIFICATION

### Issues Identified:
- **P2 [UI / Architecture]: Monolithic Inline Styling in Views.**
  - `resources/views/dashboard/index.blade.php` contained over 600 lines of inline `<style>` and repeated utility CSS classes, leading to unnecessary HTML bloat and rendering overhead.
- **P2 [UI / Layout]: Inconsistent Sidebar & Topbar Composition.**
  - While functional, the sidebar lacked clear active indicator grouping and modern subtle hover transitions.
  - The topbar lacked a unified quick-action command palette or responsive search trigger.
- **P2 [UX / Information Hierarchy]: Analytics Card Proportions.**
  - Overview KPI metrics on the dashboard were spread across varying card shapes. They require compact, high-density presentation with explicit baseline badges and clean sparkline/progress metrics.
- **P3 [Visual Polish]: Responsive Mobile Drawer Navigation.**
  - On viewports $< 1024$px, the sidebar collapsed into a vertical block rather than an executive off-canvas sliding drawer with overlay dismissal.
- **P3 [Component Reusability]: Missing Dedicated Blade Component Architecture.**
  - Cards, stats badges, and status pills were repeatedly hard-coded inline rather than consuming reusable design-system components.

---

## 7. ACTIONABLE REBUILD PLAN & SEQUENCE

1. **Phase Two:** Formalize design tokens in `resources/css/app.css` and `docs/DESIGN-SYSTEM.md`.
2. **Phase Three:** Create reusable Blade layout and presentation components:
   - `<x-stat-card>`: High-density KPI cards with trend indicators and subtitle badges.
   - `<x-sidebar>` & `<x-topbar>`: Polished application shell with off-canvas mobile drawer and contextual controls.
   - `<x-data-table>`: Unified responsive table component with empty states and pagination.
3. **Phase Four:** Rebuild `resources/views/dashboard/index.blade.php`:
   - Curated executive header with live operational status and multi-league quick selector.
   - High-density analytics summary (Poisson Calibration, Active Model, Prediction Accuracy, Sample Size).
   - Reorganized SportyBet-style match cards with progressive disclosure, 1-X-2 odds, 3-engine consensus, and MOTM stats.
   - Sticky view navigator sidebar with round completion metrics and league standings widget.
4. **Phase Five:** Verification & Test Execution:
   - Run full automated test suite to ensure 100% pass rate.
   - Verify all screen breakpoints (mobile, tablet, desktop).
