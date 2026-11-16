# XPECT FOOTBALL ANALYTICS — DESIGN SYSTEM & TOKEN SPECIFICATION

This design system establishes the visual guidelines, tokens, spacing rules, and component standards for the Xpect application. All components must strictly consume these tokens.

---

## 1. COLOR TOKENS & PALETTES

Xpect is built on dark luxury aesthetics with deep burnt orange accents and support for selectable palettes.

### 1.1 Core Tokens
```css
:root {
    /* Brand Accent */
    --xp-orange: #D94E00;         /* Primary Accent (Buttons, Highlights, Links) */
    --xp-orange-hover: #E85B07;   /* Interactive Hover State */
    --xp-orange-press: #BC4300;   /* Interactive Active/Pressed State */

    /* Pitch Black (Default Base Palette) */
    --xp-black: #000000;          /* Deepest Background */
    --xp-sidebar: #070709;        /* Navigation Sidebar */
    --xp-surface: #0C0C0E;        /* Cards, Modals, Panels */
    --xp-surface-hover: #141417;  /* Hovered Elements */
    --xp-surface-active: #1A1A1E; /* Active Element Surfaces */

    /* Borders & Dividers */
    --xp-border: #1E1E22;         /* Standard Component Borders */
    --xp-border-subtle: #131315;  /* Secondary Dividers */
    --xp-border-light: #2A2A30;   /* Elevated Component Borders */

    /* Typography */
    --xp-text: #FFFFFF;           /* Primary Heading & Body Text */
    --xp-muted: #A1A1A6;          /* Secondary & Label Text */
    --xp-away: #6B6B70;           /* Tertiary / Monospace Dim Text */

    /* Semantic Status */
    --xp-ok: #3DDC84;             /* Success, In-Play, Won Verdict */
    --xp-error: #FF4D4D;          /* Error, Missed Verdict, Cancelled */
}
```

### 1.2 Selectable Palette Matrix
| Token | Pitch Black (Default) | Obsidian | Midnight | Light |
|---|---|---|---|---|
| `page` (`--xp-black`) | `#000000` | `#121416` | `#07111F` | `#F3F5F7` |
| `sidebar` (`--xp-sidebar`) | `#070709` | `#101214` | `#091525` | `#E9EDF2` |
| `surface` (`--xp-surface`) | `#0C0C0E` | `#1B1E22` | `#0E1E31` | `#FFFFFF` |
| `surfaceHover` | `#141417` | `#252A30` | `#152944` | `#F0F2F5` |
| `border` (`--xp-border`) | `#1E1E22` | `#353B43` | `#203B5D` | `#D8DEE7` |
| `text` (`--xp-text`) | `#FFFFFF` | `#F3F4F6` | `#F2F7FC` | `#111827` |
| `muted` (`--xp-muted`) | `#A1A1A6` | `#B4BAC3` | `#B4C7D9` | `#4B5563` |
| `accent` (`--xp-orange`) | `#D94E00` | `#D94E00` | `#D94E00` | `#D94E00` |

---

## 2. TYPOGRAPHY & TEXT HIERARCHY

- **Primary Headings & Brand Mark:** `'Space Grotesk'`, system-ui, -apple-system, sans-serif. Bold (700/800). Letter-spacing: `-0.02em`.
- **Interface & Body Text:** System UI (`system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`). Weights: 400 (Regular), 500 (Medium), 600 (Semibold), 700 (Bold).
- **Odds, Probabilities & Metrics:** Tabular Monospace (`'SF Mono', Monaco, Menlo, 'Courier New', monospace`). Ensuring columns align numerically without jitter.

---

## 3. COMPONENT PATTERNS

### 3.1 Analytics Cards (KPIs)
- Border radius: `8px`.
- Border: `1px solid var(--xp-border)`.
- Background: `var(--xp-surface)`.
- Hover transition: `border-color 0.15s ease, transform 0.15s ease`.
- Header: Muted label (11px uppercase) + Icon badge.
- Main value: 22px Bold with secondary comparative delta or baseline badge.

### 3.2 SportyBet-Style Match Cards
- Container: Border with subtle gradient header.
- Teams block: Home vs Away with club badges, short names, and 5-game form pills (`11/15 pts`).
- 1-X-2 Odds Tiles: 3-column grid. The mathematical model favorite receives an active orange border and a glowing `MODEL PICK` badge.
- Analytical Systems Matrix: 3-card subgrid for Poisson, Elo, and AI Consensus.
- Progressive Disclosure: Accordion details for Match History (H2H), Goals Breakdown, MOTM Decision, and Player Ratings.

### 3.3 Tables & Data Presentations
- Row hover: `background: var(--xp-surface-hover)`.
- Column alignments: Text left-aligned; numerical values right-aligned.
- Pagination: Tactile dark luxury pagination with chevron controls and active page pill.
