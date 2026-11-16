# DASHBOARD IMPLEMENTATION PLAN
**Project:** Xpect Football Analytics & Prediction Engine
**Version:** 1.3.0
**Status:** In Progress / Controlled Rebuild

---

## 1. OBJECTIVES & ARCHITECTURAL SCOPE

The purpose of this rebuild is to elevate the Xpect dashboard interface to a modern, commercial-grade SaaS management and analytics platform (inspired by high-performance data dashboards such as SportyBet, Linear, and Vercel) while strictly maintaining:
1. Zero Data Leakage (all match predictions and stats dated strictly before kickoff).
2. Existing system colour theme (Deep orange `#D94E00`, pitch black `#000000`, obsidian `#121416`, midnight `#07111F`, and light `#F3F5F7`).
3. Complete backend and database continuity (no changes to database models, relationships, or tables).
4. All existing URLs, routes, and functional features.

---

## 2. STAGE-BY-STAGE EXECUTION ROADMAP

### Stage 1: Technology & Application Audit (Complete)
- Inspected technology stack, existing Blade views, controller actions, database tables, and API client pipelines.
- Documented findings in `docs/CANDYCUTZ-AUDIT-REPORT.md` and `docs/DASHBOARD-AUDIT-REPORT.md`.

### Stage 2: Design System & Token Standardization (Stage 2)
- Centralize all design tokens in `docs/DESIGN-SYSTEM.md` and `resources/views/components/layouts/app.blade.php`.
- Formalize tokens for:
  - `--xp-orange`: Primary action & focus indicator (`#D94E00`).
  - `--xp-surface`, `--xp-surface-hover`, `--xp-surface-active`: Card layers.
  - `--xp-border`, `--xp-border-subtle`, `--xp-border-light`: Clean divider treatments.
  - `--xp-ok` (`#3DDC84`) & `--xp-error` (`#FF4D4D`): Semantic status pills.
  - Typography: Space Grotesk Bold for headings; system UI for content; tabular monospace for odds and timestamps.

### Stage 3: Modern Application Shell & Navigation
- **Sidebar:**
  - Modern vertical composition with brand mark, grouped sections (Overview, Matchdays, Intelligence, Competitions, Administration).
  - Clear active pill indicators with subtle orange glows.
  - Mobile drawer toggle with smooth slide-in transition and backdrop blur overlay.
- **Topbar:**
  - Breadcrumb navigation.
  - Live system engine indicator badge (Active model `v1.3.0-calibrated`, Zero Leakage Guard).
  - Universal temporal date/time filter widget with fast step controls.
  - Quick theme selector dropdown & shortcut links.

### Stage 4: High-Density Analytics Cards
- Standardize top overview KPI cards into a 4-column responsive grid:
  1. **Active Prediction Engine:** Model version, Dixon-Coles $\rho = -0.10$, Form Momentum.
  2. **Model Accuracy & Win Rate:** Walk-forward accuracy vs Always-Home & Uniform baselines.
  3. **Calibration & Reliability:** Brier score & Log Loss metrics.
  4. **Active Fixtures & Coverage:** Scheduled matches today, current round, total historical matches evaluated.

### Stage 5: Restructured Match Fixture Feed & SportyBet Odds Cards
- Re-architect match fixture presentation:
  - Header: Competition chip, kickoff time countdown badge (`data-countdown-kickoff`), matchday number.
  - Team matchup block: High-resolution team badges, club short names, recent 5-match form points, rest days.
  - SportyBet 1-X-2 odds tiles with favored pick visual highlight and fair implied odds.
  - Multi-System Analytical Convergence: System 1 (Poisson), System 2 (Elo), System 3 (Consensus & AI).
  - Integrated Match History, MOTM & Squad Unit Ratings tab accordion with zero leakage guarantee.
  - Collapsible Advanced Feature Breakdown drawer (xG attack/defense strengths, league averages).

### Stage 6: Sticky In-Page Navigator & Standings Sidebar
- Sticky right-hand column:
  - Quick view navigator: All Matches, Today's Fixtures, Scheduled Upcoming, Finished Past.
  - Active Competition Standings & Form widget.
  - Model Performance distribution summary card.

### Stage 7: Automated Verification & Regression Testing
- Execute complete PHPUnit test suite.
- Verify 100% test pass rate across all 176+ tests.
- Confirm zero console errors, zero layout shifts, and full responsiveness across mobile, tablet, and desktop breakpoints.
