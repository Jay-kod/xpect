<x-layouts.app title="Dashboard">
    <style>
        .breakdown-details[open] .chevron-icon {
            transform: rotate(180deg);
        }
        .breakdown-details summary::-webkit-details-marker {
            display: none;
        }
        .breakdown-details summary:hover {
            background: rgba(217, 78, 0, 0.10) !important;
        }
        .matches-grid-container {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 270px;
            gap: 18px;
            align-items: start;
            margin-top: 14px;
        }
        .matches-sidebar-col {
            position: -webkit-sticky;
            position: sticky;
            top: 68px;
            align-self: start;
            z-index: 40;
        }
        @media (max-width: 1080px) {
            .matches-grid-container {
                grid-template-columns: 1fr;
            }
            .matches-sidebar-col {
                position: static;
                top: auto;
            }
        }
        .view-sidebar-card {
            background: #0C0C0E;
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            max-height: calc(100vh - 84px);
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .view-nav-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid var(--xp-border-subtle);
            background: #060608;
        }
        .view-nav-btn:hover {
            background: #141418;
            border-color: var(--xp-border);
        }
        .view-nav-btn.active {
            background: rgba(217, 78, 0, 0.12) !important;
            border: 1px solid rgba(217, 78, 0, 0.45) !important;
            border-left: 3px solid var(--xp-orange) !important;
        }
        .view-nav-btn.active .nav-btn-title {
            color: var(--xp-orange) !important;
            font-weight: 700;
        }
        .view-nav-btn.active .nav-btn-badge {
            background: var(--xp-orange) !important;
            color: #000000 !important;
            font-weight: 700;
        }
        .analytics-subgrid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 16px;
            border-top: 1px solid var(--xp-border);
            padding-top: 14px;
            margin-top: 12px;
        }
        @media (max-width: 900px) {
            .analytics-subgrid {
                grid-template-columns: 1fr;
            }
        }

        /* ------------------------------------------------------------- */
        /* SPORTYBET-STYLE MODERN UI EXTENSIONS                          */
        /* ------------------------------------------------------------- */
        .db-provenance-strip {
            background: linear-gradient(90deg, #0A0A0D 0%, #111115 100%);
            border: 1px solid var(--xp-border);
            border-left: 3px solid var(--xp-orange);
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .db-provenance-main {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .db-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--xp-ok);
            box-shadow: 0 0 8px var(--xp-ok);
            animation: pulse-live 2s infinite;
        }
        @keyframes pulse-live {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .db-title-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .db-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--xp-text);
            letter-spacing: -0.01em;
        }
        .db-tag-model {
            background: rgba(217, 78, 0, 0.15);
            color: var(--xp-orange);
            border: 1px solid rgba(217, 78, 0, 0.35);
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .db-tag-clean {
            background: #141418;
            border: 1px solid var(--xp-border);
            color: var(--xp-ok);
            font-size: 10px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .db-provenance-stats {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: var(--xp-muted);
            flex-wrap: wrap;
        }
        .db-bullet {
            color: var(--xp-away);
        }
        .db-today-badge {
            background: rgba(217, 78, 0, 0.2);
            color: var(--xp-orange);
            border: 1px solid var(--xp-orange);
            padding: 1px 7px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 10px;
        }

        /* SportyBet Horizontal League Filter Bar */
        .sporty-leagues-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            scrollbar-width: thin;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .sporty-league-chip {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 20px;
            background: #0C0C0E;
            border: 1px solid var(--xp-border);
            color: var(--xp-muted);
            text-decoration: none;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .sporty-league-chip:hover {
            background: #141418;
            color: var(--xp-text);
            border-color: var(--xp-border-light);
        }
        .sporty-league-chip.active {
            background: rgba(217, 78, 0, 0.14) !important;
            border-color: var(--xp-orange) !important;
            color: #FFFFFF !important;
            box-shadow: 0 0 12px rgba(217, 78, 0, 0.25);
        }
        .sporty-league-chip.active .chip-code {
            background: var(--xp-orange);
            color: #000000;
            font-weight: 700;
        }
        .chip-code {
            font-size: 9.5px;
            padding: 1px 5px;
            border-radius: 4px;
            background: #18181C;
            color: var(--xp-away);
            font-weight: 700;
        }

        /* SportyBet Day Switcher Bar */
        .sporty-day-switcher {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            background: #0C0C0E;
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 14px;
        }
        .day-switcher-left {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .day-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 6px;
            background: #060608;
            border: 1px solid var(--xp-border-subtle);
            color: var(--xp-muted);
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .day-pill:hover {
            background: #141418;
            color: var(--xp-text);
            border-color: var(--xp-border);
        }
        .day-pill.active {
            background: rgba(217, 78, 0, 0.14) !important;
            border-color: var(--xp-orange) !important;
            color: var(--xp-orange) !important;
            font-weight: 700;
        }
        .day-badge {
            background: #18181C;
            color: var(--xp-away);
            font-size: 9.5px;
            padding: 1px 5px;
            border-radius: 8px;
            font-weight: 700;
        }
        .day-pill.active .day-badge {
            background: var(--xp-orange);
            color: #000000;
        }

        /* SportyBet 1X2 Match Card - Professional Broadcast & Betting Look */
        .sporty-match-card {
            background: #0D0E12;
            border: 1px solid #1E2028;
            border-radius: 10px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        .sporty-match-card:hover {
            border-color: #2E3342;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
        }
        .sporty-match-card.featured-card {
            border: 1.5px solid rgba(217, 78, 0, 0.45);
            background: linear-gradient(180deg, #101117 0%, #0B0B0E 100%);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.6), 0 0 20px rgba(217, 78, 0, 0.08);
        }
        .sporty-match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #181922;
            flex-wrap: wrap;
            gap: 8px;
        }
        .sporty-matchup {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 16px;
            align-items: center;
            background: #08080B;
            padding: 14px 18px;
            border-radius: 8px;
            border: 1px solid #181922;
        }
        .sporty-team-side {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .sporty-team-side.home {
            justify-content: flex-end;
            text-align: right;
        }
        .sporty-team-side.away {
            justify-content: flex-start;
            text-align: left;
        }
        .sporty-team-name {
            font-size: 15px;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.01em;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sporty-team-sub {
            font-size: 11px;
            font-weight: 600;
            margin-top: 2px;
        }
        .sporty-team-sub.home-sub {
            color: var(--xp-orange);
        }
        .sporty-team-sub.away-sub {
            color: var(--xp-away);
        }
        .sporty-center-pillar {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 0 8px;
        }
        .sporty-vs-badge {
            background: #14151C;
            border: 1px solid #252835;
            color: var(--xp-away);
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.08em;
            padding: 3px 10px;
            border-radius: 14px;
        }
        .sporty-score-pill {
            font-size: 20px;
            font-weight: 900;
            color: #FFFFFF;
            background: #14151C;
            border: 1px solid #282A36;
            padding: 4px 14px;
            border-radius: 6px;
            letter-spacing: 0.05em;
        }
        .sporty-time-sub {
            font-size: 10.5px;
            color: var(--xp-muted);
            font-weight: 600;
        }
        .sporty-odds-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }
        .sporty-odd-card {
            background: #07070A;
            border: 1px solid #1C1E26;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all 0.15s ease;
            position: relative;
        }
        .sporty-odd-card:hover {
            border-color: #2D303D;
            background: #0E0F14;
        }
        .sporty-odd-card.favored {
            border: 1.5px solid var(--xp-orange) !important;
            background: linear-gradient(180deg, rgba(217, 78, 0, 0.16) 0%, rgba(217, 78, 0, 0.04) 100%) !important;
            box-shadow: 0 0 16px rgba(217, 78, 0, 0.22);
        }
        .odd-num {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--xp-away);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .sporty-odd-card.favored .odd-num {
            color: var(--xp-orange);
        }
        .odd-prob {
            font-size: 18px;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        .sporty-odd-card.favored .odd-prob {
            color: var(--xp-orange);
        }
        .odd-fair-price {
            font-size: 11px;
            color: var(--xp-muted);
            font-weight: 600;
            font-family: var(--xp-font-mono, monospace);
        }
        .sporty-odd-card.favored .odd-fair-price {
            color: #F0A57A;
        }
        .odd-tag {
            font-size: 9px;
            font-weight: 800;
            color: #000000;
            background: var(--xp-orange);
            padding: 2px 6px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }
        .sporty-indicators-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            background: #08080B;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #181922;
            font-size: 11px;
            color: var(--xp-muted);
        }
        .indicator-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .indicator-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--xp-orange);
        }
        .sporty-ai-box {
            background: linear-gradient(90deg, rgba(217, 78, 0, 0.09) 0%, rgba(13, 14, 18, 0.7) 100%);
            border: 1px solid rgba(217, 78, 0, 0.3);
            border-left: 3.5px solid var(--xp-orange);
            border-radius: 7px;
            padding: 12px 14px;
            font-size: 12px;
            color: #E2E2E8;
            line-height: 1.55;
        }
        .sporty-ai-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }
        .sporty-ai-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: var(--xp-orange);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .sporty-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            padding-top: 4px;
        }

        /* Prediction Verdict Badges for Completed Games */
        .sporty-verdict-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 800;
            padding: 2.5px 8px;
            border-radius: 4px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .sporty-verdict-badge.won {
            background: rgba(0, 200, 83, 0.16);
            color: var(--xp-ok);
            border: 1px solid var(--xp-ok);
            box-shadow: 0 0 10px rgba(0, 200, 83, 0.2);
        }
        .sporty-verdict-badge.lost {
            background: rgba(255, 69, 58, 0.14);
            color: #FF453A;
            border: 1px solid #FF453A;
        }
        .sporty-final-score-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #050507;
            border: 1px solid #282A36;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 800;
            color: #FFFFFF;
        }
        .sporty-final-score-pill.won {
            border-color: var(--xp-ok);
            box-shadow: 0 0 10px rgba(0, 200, 83, 0.2);
        }
        .sporty-odd-card.actual-winner {
            border: 1.5px solid var(--xp-ok) !important;
            background: rgba(0, 200, 83, 0.06) !important;
        }
        .sporty-odd-card.won-tile {
            border: 2px solid var(--xp-ok) !important;
            background: linear-gradient(180deg, rgba(0, 200, 83, 0.18) 0%, rgba(0, 200, 83, 0.05) 100%) !important;
            box-shadow: 0 0 18px rgba(0, 200, 83, 0.28);
        }
        .sporty-odd-card.won-tile .odd-num {
            color: var(--xp-ok);
        }
        .sporty-odd-card.won-tile .odd-prob {
            color: var(--xp-ok);
        }
        .odd-verdict-tag {
            font-size: 8.5px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }
        .odd-verdict-tag.won {
            background: var(--xp-ok);
            color: #000000;
        }
        .odd-verdict-tag.actual {
            background: rgba(0, 200, 83, 0.2);
            color: var(--xp-ok);
            border: 1px solid var(--xp-ok);
        }
        .odd-verdict-tag.missed {
            background: rgba(255, 69, 58, 0.2);
            color: #FF453A;
            border: 1px solid #FF453A;
        }

        /* 3 Analytical Systems Box */
        .sporty-three-systems-box {
            background: #08080B;
            border: 1px solid #1C1E27;
            border-radius: 7px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .three-systems-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }
        .three-systems-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }
        .system-col {
            background: #0D0E13;
            border: 1px solid #1F212D;
            border-radius: 6px;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .system-col.consensus-col {
            border-color: rgba(217, 78, 0, 0.45);
            background: linear-gradient(180deg, rgba(217, 78, 0, 0.09) 0%, #0D0E13 100%);
        }
        .system-title {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            color: #FFFFFF;
        }
        .system-badge {
            font-size: 8px;
            font-weight: 800;
            padding: 1px 4px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .system-badge.s1 { background: rgba(59, 130, 246, 0.2); color: #60A5FA; border: 1px solid #3B82F6; }
        .system-badge.s2 { background: rgba(168, 85, 247, 0.2); color: #C084FC; border: 1px solid #A855F7; }
        .system-badge.s3 { background: rgba(217, 78, 0, 0.25); color: var(--xp-orange); border: 1px solid var(--xp-orange); }
        .system-method {
            font-size: 9.5px;
            color: var(--xp-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .system-probs {
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            font-weight: 600;
            color: #D4D4DC;
            margin-top: 2px;
            padding-top: 2px;
            border-top: 1px solid #171822;
        }
        .system-probs .favored {
            color: var(--xp-orange);
            font-weight: 800;
        }
        .system-footer {
            font-size: 10px;
            color: var(--xp-away);
            margin-top: 1px;
        }
        .system-footer strong {
            color: #FFFFFF;
        }
        .three-systems-agreement-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #050508;
            border: 1px solid #161720;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            color: #B5B7C2;
        }
        @media (max-width: 768px) {
            .three-systems-grid {
                grid-template-columns: 1fr;
            }
        }
        .sporty-time-remaining {
            font-size: 10px;
            font-weight: 700;
            color: var(--xp-orange);
            background: rgba(217, 78, 0, 0.12);
            border: 1px solid rgba(217, 78, 0, 0.3);
            padding: 2px 7px;
            border-radius: 4px;
            white-space: nowrap;
            letter-spacing: 0.02em;
        }
        .sporty-time-remaining.urgent {
            color: #FF453A;
            background: rgba(255, 69, 58, 0.14);
            border-color: rgba(255, 69, 58, 0.45);
            animation: pulse-countdown 1.8s infinite ease-in-out;
        }
        @keyframes pulse-countdown {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.65; }
        }
        .live-pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #FF3B30;
            box-shadow: 0 0 8px #FF3B30;
            display: inline-block;
            margin-right: 4px;
            animation: pulse-countdown 1.2s infinite;
        }
        .team-extra-detail {
            font-size: 10px;
            color: var(--xp-away);
            font-weight: 600;
            margin-left: 4px;
        }
        .sporty-mini-markets {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 10.5px;
            color: var(--xp-muted);
            border-top: 1px solid #161720;
            padding-top: 6px;
            margin-top: 2px;
        }
        .mini-market-pill {
            background: #090A0D;
            border: 1px solid #1C1E28;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            color: var(--xp-text);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .mini-market-pill strong {
            color: var(--xp-orange);
        }
    </style>

    <!-- Dashboard Skeletal Preloader (Active during navigation / filter state changes or ?skeleton=1) -->
    <x-skeletal-preloader page="dashboard" />

    <!-- Dashboard Real Content Stream -->
    <div id="dashboard-real-content" class="xp-page-content" style="{{ request()->has('skeleton') ? 'display: none;' : '' }}">
    <!-- 1. TOP DATABASE TRUST & PROVENANCE STRIP (The Confidence Ribbon) -->
    <div class="db-provenance-strip">
        <div class="db-provenance-main">
            <span class="db-pulse-dot" title="Live Model Engine Connected"></span>
            <div class="db-title-group">
                <span class="db-title">Xpect Prediction Engine Active</span>
                <span class="db-tag-model">v1.2.1-h2h (Dixon-Coles + xG)</span>
                <span class="db-tag-clean">Zero Leakage Guard</span>
            </div>
        </div>
        <div class="db-provenance-stats">
            <span>⚡ Powered by <strong>{{ number_format($dbStats['total_matches'] ?? 10734) }}</strong> matches in DB</span>
            <span class="db-bullet">&bull;</span>
            <span><strong>{{ $dbStats['total_seasons'] ?? 5 }}</strong> seasons verified</span>
            <span class="db-bullet">&bull;</span>
            <span><strong>{{ number_format($dbStats['total_predictions'] ?? 6597) }}</strong> predictions evaluated</span>
            @if (!empty($dbStats['today_all_leagues_count']) && $dbStats['today_all_leagues_count'] > 0)
                <span class="db-bullet">&bull;</span>
                <span class="db-today-badge">⭐ {{ $dbStats['today_all_leagues_count'] }} games today</span>
            @endif
        </div>
    </div>

    <!-- 2. SPORTYBET HORIZONTAL LEAGUE FILTER BAR -->
    <div class="sporty-leagues-bar">
        @foreach ($competitions as $c)
            @php
                $cCode = match($c->slug) {
                    'premier-league' => 'EPL',
                    'la-liga' => 'ESP',
                    'bundesliga' => 'GER',
                    'serie-a' => 'ITA',
                    'ligue-1' => 'FRA',
                    default => 'UCL',
                };
                $cFlag = match($c->slug) {
                    'premier-league' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
                    'la-liga' => '🇪🇸',
                    'bundesliga' => '🇩🇪',
                    'serie-a' => '🇮🇹',
                    'ligue-1' => '🇫🇷',
                    default => '🏆',
                };
                $cTodayCount = $todayLeagueCounts[$c->id] ?? 0;
                $cUpcomingCount = $upcomingLeagueCounts[$c->id] ?? 0;
            @endphp
            <a href="{{ route('dashboard', ['competition' => $c->slug, 'season' => $selectedSeason, 'status' => $statusFilter]) }}"
               class="sporty-league-chip {{ $selectedComp === $c->slug ? 'active' : '' }}"
               title="Filter to {{ $c->name }}">
                <span>{{ $cFlag }}</span>
                <span>{{ $c->name }}</span>
                <span class="chip-code">{{ $cCode }}</span>
                @if ($cTodayCount > 0)
                    <span class="badge" style="background:var(--xp-orange);color:#000000;font-size:9.5px;font-weight:700;padding:1px 5px;border-radius:8px;">
                        {{ $cTodayCount }} today
                    </span>
                @elseif ($cUpcomingCount > 0)
                    <span class="badge" style="background:#18181C;color:var(--xp-away);font-size:9.5px;padding:1px 5px;border-radius:8px;">
                        {{ $cUpcomingCount }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- 3. SPORTYBET QUICK DAY SWITCHER & MATCHDAY JUMP BAR -->
    <div class="sporty-day-switcher">
        <div class="day-switcher-left">
            <!-- 1. Today's Matches -->
            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'today']) }}"
               class="day-pill {{ $statusFilter === 'today' ? 'active' : '' }}">
                <span>⭐</span>
                <span>Today's Matches</span>
                <span class="day-badge">{{ $matchCounts['today'] }}</span>
            </a>

            <!-- 2. Upcoming Round / Future -->
            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'future']) }}"
               class="day-pill {{ $statusFilter === 'future' ? 'active' : '' }}">
                <span>Upcoming Round</span>
                <span class="day-badge">{{ $matchCounts['future'] }}</span>
            </a>

            <!-- 3. Past Results -->
            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'past']) }}"
               class="day-pill {{ $statusFilter === 'past' ? 'active' : '' }}">
                <span>Past Results</span>
                <span class="day-badge">{{ $matchCounts['past'] }}</span>
            </a>

            <!-- 4. All Matches -->
            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'all']) }}"
               class="day-pill {{ $statusFilter === 'all' && !$dateFilterInfo ? 'active' : '' }}">
                <span>All Fixtures</span>
                <span class="day-badge">{{ $matchCounts['all'] }}</span>
            </a>
        </div>

        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <!-- Matchdays Jump Dropdown -->
            @if (!empty($availableMatchDates) && $availableMatchDates->isNotEmpty())
                <select class="select-pill" style="font-size:11px;padding:3px 6px;" onchange="if(this.value) window.location.href=this.value;" title="Jump directly to confirmed matchday dates">
                    <option value="">📅 Matchdays ({{ $availableMatchDates->count() }} rounds)...</option>
                    @foreach ($availableMatchDates as $amd)
                        <option value="{{ route('dashboard', array_merge(request()->except(['page', 'preset']), ['competition' => $selectedComp, 'date' => $amd->match_date])) }}" {{ $selectedDate === $amd->match_date ? 'selected' : '' }}>
                            {{ \Carbon\CarbonImmutable::parse($amd->match_date)->format('D, d M') }} ({{ $amd->count }})
                        </option>
                    @endforeach
                </select>
            @endif

            <!-- Date Selector Input -->
            <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:4px;align-items:center;margin:0;">
                <input type="hidden" name="competition" value="{{ $selectedComp }}">
                <input type="hidden" name="season" value="{{ $selectedSeason }}">
                <div style="display:flex;align-items:center;background:#060608;border:1px solid var(--xp-border);border-radius:4px;padding:2px 6px;gap:4px;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--xp-orange);">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <input type="date" name="date" value="{{ $selectedDate }}" class="select-pill" style="border:none;background:transparent;padding:2px;font-size:11px;color:var(--xp-text);outline:none;" title="Select specific match date" onchange="this.form.submit()">
                </div>
                @if ($selectedDate || !empty($selectedTimeSlot) || !empty($selectedTime))
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => $statusFilter]) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;color:var(--xp-orange);" title="Clear date filter">
                        ✕
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- 4. MATCHES GRID CONTAINER: Matches Feed + In-Page View Navigator Sidebar -->
    <div class="matches-grid-container">
        <!-- Main Content Stream (Spotlight & Fixtures Feed) -->
        <div style="min-width: 0; display:flex; flex-direction:column; gap:16px;">
            @if (($statusFilter === 'all' || $statusFilter === 'future') && !$dateFilterInfo && empty($selectedTimeSlot) && empty($selectedTime))
                <!-- Curated Marquee Spotlight for Next Matchday -->
                <div id="upcoming-spotlight" class="card" style="padding:16px;border:1px solid rgba(217, 78, 0, 0.35);background:linear-gradient(180deg, #111114 0%, #0A0A0C 100%);">
                    <!-- Spotlight Header -->
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px;border-bottom:1px solid var(--xp-border);padding-bottom:10px;">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--xp-orange);box-shadow:0 0 8px var(--xp-orange);"></span>
                                <h2 style="font-size:14px;font-weight:700;color:var(--xp-text);margin:0;letter-spacing:-0.01em;">
                                    Next Matchday &amp; Upcoming Predictions
                                </h2>
                                <span class="badge badge-warning" style="background:rgba(217, 78, 0, 0.15);color:var(--xp-orange);border:1px solid rgba(217, 78, 0, 0.35);font-size:9.5px;">
                                    {{ $nextMatchdayInfo['matchday'] ?? 'Next Round' }}
                                </span>
                            </div>
                            <p style="font-size:11px;color:var(--xp-muted);margin:2px 0 0 0;">
                                {{ $competition?->name }} &bull; {{ $nextMatchdayInfo['date_range'] ?? 'Upcoming' }} &bull; {{ $upcomingMatches->count() }} Matches Scheduled
                            </p>
                        </div>

                        <div style="display:flex;align-items:center;gap:6px;">
                            <span class="badge badge-success" style="font-size:9.5px;">
                                <span style="display:inline-block;width:5px;height:5px;border-radius:50%;background:var(--xp-ok);margin-right:3px;"></span>
                                Model: v1.2.1-h2h active
                            </span>
                            <span class="badge" style="background:#16161A;border:1px solid var(--xp-border);color:var(--xp-muted);font-size:9.5px;">
                                Zero Data Leakage Guard
                            </span>
                        </div>
                    </div>

                    @if ($upcomingMatches->isEmpty())
                        <div style="text-align:center;padding:2rem 1rem;">
                            <h3 style="color:var(--xp-muted);font-size:13px;margin-bottom:4px;">No upcoming matches scheduled</h3>
                            <p style="color:var(--xp-muted);font-size:11px;margin-bottom:12px;">Sync fixtures to populate the next matchday.</p>
                            <form method="POST" action="{{ route('settings.action') }}">
                                @csrf
                                <input type="hidden" name="action" value="sync_matches">
                                <button type="submit" class="btn btn-secondary btn-sm">Sync Upcoming Fixtures</button>
                            </form>
                        </div>
                    @else
                        @php
                            $featuredMatch = $upcomingMatches->first();
                            $featuredPrediction = $featuredMatch->predictions->first();
                            $otherUpcoming = $upcomingMatches->slice(1);
                        @endphp

                        <!-- Primary Featured Marquee Match Card (SportyBet Pattern) -->
                        @include('dashboard.partials.match-card', [
                            'match' => $featuredMatch,
                            'prediction' => $featuredPrediction,
                            'h2hCount' => $h2hCounts[$featuredMatch->id] ?? 0,
                            'isFeatured' => true
                        ])

                        <!-- Compact Secondary Grid for Remaining Round Fixtures -->
                        @if ($otherUpcoming->isNotEmpty())
                            <div style="margin-top:12px;border-top:1px solid var(--xp-border-subtle);padding-top:10px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <span style="font-size:11px;font-weight:700;color:var(--xp-muted);text-transform:uppercase;letter-spacing:0.04em;">
                                        Other Round Fixtures ({{ $otherUpcoming->count() }})
                                    </span>
                                    <a href="#fixtures-section" style="font-size:11px;color:var(--xp-orange);text-decoration:none;">
                                        View Full Schedule &darr;
                                    </a>
                                </div>

                                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:8px;">
                                    @foreach ($otherUpcoming->take(4) as $subMatch)
                                        @php
                                            $subPred = $subMatch->predictions->first();
                                        @endphp
                                        <div style="background:#09090C;border:1px solid var(--xp-border);border-radius:6px;padding:8px 10px;display:flex;align-items:center;justify-content:space-between;">
                                            <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                                                <div style="display:flex;align-items:center;gap:4px;">
                                                    <x-team-badge :team="$subMatch->homeTeam" size="18" />
                                                    <span style="font-size:12px;font-weight:600;color:var(--xp-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        {{ $subMatch->homeTeam?->short_name }}
                                                    </span>
                                                </div>
                                                <span style="font-size:10px;color:var(--xp-away);">v</span>
                                                <div style="display:flex;align-items:center;gap:4px;">
                                                    <x-team-badge :team="$subMatch->awayTeam" size="18" />
                                                    <span style="font-size:12px;font-weight:600;color:var(--xp-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        {{ $subMatch->awayTeam?->short_name }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div style="display:flex;align-items:center;gap:6px;">
                                                @if ($subPred)
                                                    <span class="badge" style="background:rgba(217, 78, 0, 0.12);color:var(--xp-orange);font-size:10px;font-weight:700;">
                                                        {{ round($subPred->p_home * 100) }}% | {{ round($subPred->p_draw * 100) }}% | {{ round($subPred->p_away * 100) }}%
                                                    </span>
                                                @else
                                                    <span style="font-size:10px;color:var(--xp-away);">
                                                        {{ $subMatch->kickoff_at ? $subMatch->kickoff_at->format('D H:i') : 'TBD' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <!-- Clean Integrated Fixtures & Predictions Section (SportyBet Card Stream) -->
            <div id="fixtures-section" class="card" style="padding:16px 18px;">
                <!-- Header & Filters Bar -->
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;border-bottom:1px solid var(--xp-border);padding-bottom:12px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <h2 style="font-size:15px;font-weight:700;color:var(--xp-text);margin:0;">
                                {{ $currentViewLabel }} &bull; Fixtures &amp; Predictions
                            </h2>
                            <span class="badge" style="background:#16161A;border:1px solid var(--xp-border);color:var(--xp-orange);font-size:10px;font-weight:700;">
                                {{ $matches->total() }} matches
                            </span>
                        </div>
                    </div>

                    <!-- Consolidated Filter Controls -->
                    <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin:0;">
                        <input type="hidden" name="competition" value="{{ $selectedComp }}">

                        <select name="season" class="select-pill" style="font-size:11px;padding:3px 6px;" onchange="this.form.submit()">
                            @foreach ($seasons as $s)
                                <option value="{{ $s }}" {{ $selectedSeason === $s ? 'selected' : '' }}>Season {{ $s }}/{{ substr((string)($s+1), -2) }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="select-pill" style="font-size:11px;padding:3px 6px;" onchange="this.form.submit()">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All ({{ $matchCounts['all'] }})</option>
                            <option value="future" {{ $statusFilter === 'future' ? 'selected' : '' }}>Future ({{ $matchCounts['future'] }})</option>
                            <option value="today" {{ $statusFilter === 'today' ? 'selected' : '' }}>Today ({{ $matchCounts['today'] }})</option>
                            <option value="past" {{ $statusFilter === 'past' ? 'selected' : '' }}>Past ({{ $matchCounts['past'] }})</option>
                        </select>
                    </form>
                </div>

                <!-- Date Filter Alert (if date active) -->
                @if ($dateFilterInfo)
                    <div style="background:rgba(217,78,0,0.08);border:1px solid rgba(217,78,0,0.25);border-radius:6px;padding:8px 12px;margin-bottom:12px;display:flex;flex-direction:column;gap:6px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:11.5px;color:var(--xp-orange);font-weight:700;">📅 Date Filter:</span>
                                <span style="font-size:11.5px;color:#FFFFFF;font-weight:600;">{{ $dateFilterInfo['formatted'] }}</span>
                                @if (!empty($dateFilterInfo['time_slot_label']))
                                    <span class="badge" style="background:var(--xp-orange);color:#000000;font-size:9.5px;font-weight:700;">
                                        {{ $dateFilterInfo['time_slot_label'] }}
                                    </span>
                                @endif
                                <span class="badge" style="background:#0C0C0E;border:1px solid var(--xp-border);font-size:9.5px;color:var(--xp-text);">
                                    {{ $matches->total() }} matches
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => $dateFilterInfo['prev_date']])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;">
                                    &larr; Prev
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => \Carbon\CarbonImmutable::now('UTC')->format('Y-m-d')])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;{{ $dateFilterInfo['is_today'] ? 'border-color:var(--xp-orange);color:var(--xp-orange);' : '' }}">
                                    Today
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['page']), ['competition' => $selectedComp, 'date' => $dateFilterInfo['next_date']])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;">
                                    Next &rarr;
                                </a>
                                <a href="{{ route('dashboard', array_merge(request()->except(['date', 'time', 'time_slot', 'preset', 'page']), ['competition' => $selectedComp])) }}" class="btn btn-secondary btn-sm" style="padding:2px 6px;font-size:10px;color:var(--xp-orange);">
                                    ✕ Reset
                                </a>
                            </div>
                        </div>

                        <!-- Kickoff Window Pills -->
                        <div style="display:flex;align-items:center;gap:5px;border-top:1px solid rgba(217,78,0,0.15);padding-top:6px;font-size:10.5px;flex-wrap:wrap;">
                            <span style="color:var(--xp-away);font-weight:600;font-size:10px;">Time Window:</span>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'all'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ !$selectedTimeSlot && !$selectedTime ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ !$selectedTimeSlot && !$selectedTime ? 'var(--xp-orange)' : '#101014' }};color:{{ !$selectedTimeSlot && !$selectedTime ? '#000000' : 'var(--xp-text)' }};">
                                All Kickoffs
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'early'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'early' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'early' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'early' ? '#000000' : 'var(--xp-text)' }};">
                                🌤 Early Kickoff
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'afternoon'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'afternoon' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'afternoon' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'afternoon' ? '#000000' : 'var(--xp-text)' }};">
                                ⚽ Prime Afternoon
                            </a>
                            <a href="{{ route('dashboard', array_merge(request()->except(['page', 'time', 'time_slot']), ['competition' => $selectedComp, 'date' => $selectedDate, 'time_slot' => 'evening'])) }}"
                               style="padding:1px 6px;border-radius:3px;text-decoration:none;font-size:10px;font-weight:600;border:1px solid {{ $selectedTimeSlot === 'evening' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedTimeSlot === 'evening' ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedTimeSlot === 'evening' ? '#000000' : 'var(--xp-text)' }};">
                                🌙 Evening (17:30+)
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Fixtures Listing with SportyBet Cards -->
                @if ($matches->isEmpty())
                    @if ($statusFilter === 'today')
                        <div style="text-align:center;padding:2.5rem 1rem;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#141418;border:1px solid var(--xp-border);display:flex;align-items:center;justify-content:center;margin:0 auto 10px auto;color:var(--xp-away);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 14 14"></polyline>
                                </svg>
                            </div>
                            <h3 style="color:var(--xp-text);font-size:14px;margin-bottom:4px;font-weight:700;">No matches kicking off today</h3>
                            <p style="color:var(--xp-muted);font-size:11px;margin-bottom:14px;max-width:380px;margin-left:auto;margin-right:auto;">
                                There are no {{ $competition?->name }} fixtures scheduled for today. Switch to future matches to see upcoming predictions.
                            </p>
                            <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'future']) }}" class="btn btn-primary btn-sm">
                                View Future Matches ({{ $matchCounts['future'] }}) &rarr;
                            </a>
                        </div>
                    @else
                        <div style="text-align:center;padding:2.5rem 1rem;">
                            <h3 style="color:var(--xp-muted);font-size:13px;margin-bottom:4px;">No matches found</h3>
                            <p style="color:var(--xp-muted);font-size:11px;margin-bottom:14px;">There are no matches loaded for the selected filters.</p>
                            <form method="POST" action="{{ route('settings.action') }}">
                                @csrf
                                <input type="hidden" name="action" value="sync_matches">
                                <button type="submit" class="btn btn-secondary btn-sm">Sync Fixtures</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        @foreach ($matches as $match)
                            @include('dashboard.partials.match-card', [
                                'match' => $match,
                                'prediction' => $match->predictions->first(),
                                'h2hCount' => $h2hCounts[$match->id] ?? 0,
                                'isFeatured' => false
                            ])
                        @endforeach

                        <!-- Pagination -->
                        <div style="margin-top:10px;">
                            {{ $matches->links('partials.pagination') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- 5. COMPETITION ANALYTICS & SEASON PULSE (Positioned below the active matches feed) -->
            <div class="card" style="margin-top:4px;padding:16px 18px;">
                <!-- Header: Title & Season Selector -->
                <div class="card-header" style="margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <div class="card-title" style="display:flex;align-items:center;gap:8px;font-size:14px;">
                            <span>Matchday Scoring &amp; Goal Intensity Trend</span>
                            <span class="badge" style="background:#141418;border:1px solid var(--xp-border);font-size:10.5px;color:var(--xp-orange);font-weight:700;">
                                {{ $competition?->name }}
                            </span>
                        </div>
                        <div style="font-size:11px;color:var(--xp-muted);margin-top:2px;">
                            Total goals scored per round across Season {{ $selectedSeason }}/{{ substr((string)($selectedSeason+1), -2) }}.
                        </div>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:6px;align-items:center;margin:0;">
                        <input type="hidden" name="competition" value="{{ $selectedComp }}">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                        <select name="season" class="select-pill" style="font-size:11px;padding:3px 6px;" onchange="this.form.submit()">
                            @foreach ($seasons as $s)
                                <option value="{{ $s }}" {{ $selectedSeason === $s ? 'selected' : '' }}>Season {{ $s }}/{{ substr((string)($s+1), -2) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- SVG Area/Line Chart -->
                @php
                    $scoringData = $compStats['matchday_scoring'] ?? collect();
                    $scoringCount = $scoringData->count();
                    $maxGoals = max(40, (int) ($compStats['highest_round']->total_goals ?? 35) + 5);
                    $chartHeight = 90;
                    $chartWidth = 860;
                    $baseY = 96;

                    $points = [];
                    $pathD = '';
                    $areaD = '';

                    if ($scoringCount > 0) {
                        $stepX = $scoringCount > 1 ? ($chartWidth / ($scoringCount - 1)) : ($chartWidth / 2);
                        foreach ($scoringData as $idx => $item) {
                            $x = round(30 + ($idx * $stepX), 1);
                            $y = round($baseY - (($item->total_goals / $maxGoals) * $chartHeight), 1);
                            $points[] = [
                                'x' => $x,
                                'y' => $y,
                                'goals' => $item->total_goals,
                                'matchday' => $item->matchday,
                                'avg' => $item->avg_goals
                            ];
                        }

                        foreach ($points as $i => $pt) {
                            if ($i === 0) {
                                $pathD = "M {$pt['x']} {$pt['y']}";
                                $areaD = "M {$pt['x']} {$baseY} L {$pt['x']} {$pt['y']}";
                            } else {
                                $prev = $points[$i - 1];
                                $cx1 = round($prev['x'] + ($pt['x'] - $prev['x']) / 2, 1);
                                $cy1 = $prev['y'];
                                $cx2 = $cx1;
                                $cy2 = $pt['y'];
                                $pathD .= " C {$cx1} {$cy1}, {$cx2} {$cy2}, {$pt['x']} {$pt['y']}";
                                $areaD .= " C {$cx1} {$cy1}, {$cx2} {$cy2}, {$pt['x']} {$pt['y']}";
                            }
                        }
                        $lastPt = end($points);
                        $areaD .= " L {$lastPt['x']} {$baseY} Z";
                    }

                    $avgY = $compStats['avg_goals_per_round'] > 0
                        ? round($baseY - (($compStats['avg_goals_per_round'] / $maxGoals) * $chartHeight), 1)
                        : $baseY;
                @endphp

                <!-- Sleek SVG Line Chart -->
                <div style="width:100%;height:115px;position:relative;">
                    @if ($scoringCount > 0)
                        <svg viewBox="0 0 910 120" preserveAspectRatio="none" style="width:100%;height:100%;overflow:visible;">
                            <defs>
                                <linearGradient id="trendGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#D94E00" stop-opacity="0.18" />
                                    <stop offset="100%" stop-color="#D94E00" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>

                            <!-- Horizontal grid lines -->
                            <line x1="20" y1="20" x2="890" y2="20" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />
                            <line x1="20" y1="55" x2="890" y2="55" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />
                            <line x1="20" y1="90" x2="890" y2="90" stroke="var(--xp-border)" stroke-width="1" stroke-dasharray="3,3" />

                            <!-- League Average Reference Line -->
                            @if ($compStats['avg_goals_per_round'] > 0)
                                <line x1="20" y1="{{ $avgY }}" x2="890" y2="{{ $avgY }}" stroke="#6B6B70" stroke-width="1" stroke-dasharray="4,4" />
                                <text x="888" y="{{ max(14, $avgY - 4) }}" fill="#A1A1A6" font-size="9" font-weight="600" text-anchor="end">
                                    Avg: {{ $compStats['avg_goals_per_round'] }} goals/round
                                </text>
                            @endif

                            <!-- Area fill under the curve -->
                            <path d="{{ $areaD }}" fill="url(#trendGradient)" />

                            <!-- Smooth orange trendline -->
                            <path d="{{ $pathD }}" fill="none" stroke="#D94E00" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />

                            <!-- Interactive Data Points and Labels -->
                            @foreach ($points as $pt)
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="3.5" fill="#D94E00" stroke="#0C0C0E" stroke-width="1.8" />
                                <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 6 }}" fill="var(--xp-text)" font-size="10" font-weight="700" text-anchor="middle">
                                    {{ $pt['goals'] }}
                                </text>
                                <text x="{{ $pt['x'] }}" y="112" fill="#A1A1A6" font-size="10" font-weight="600" text-anchor="middle">
                                    MD {{ $pt['matchday'] }}
                                </text>
                            @endforeach
                        </svg>
                    @else
                        <div style="height:100%;display:flex;align-items:center;justify-content:center;color:var(--xp-muted);font-size:12px;">
                            No finished matchday scoring recorded for this season yet.
                        </div>
                    @endif
                </div>

                <!-- Integrated 3-Part Analytics Subgrid (Match Dynamics + Outcomes + Operations) -->
                <div class="analytics-subgrid">
                    <!-- Part 1: Match Dynamics -->
                    <div style="display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:2px;">
                                <span style="font-size:13px;font-weight:600;color:var(--xp-text);">Match Dynamics</span>
                                <span style="font-size:16px;font-weight:700;color:var(--xp-orange);">{{ $compStats['btts_pct'] }}%</span>
                            </div>
                            <div style="font-size:10.5px;color:var(--xp-muted);margin-bottom:8px;">
                                BTTS: {{ $compStats['btts_count'] }}/{{ $compStats['finished'] }} games
                            </div>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <!-- Over 2.5 -->
                            <div>
                                <div style="display:flex;justify-content:space-between;font-size:10.5px;margin-bottom:2px;">
                                    <span style="color:var(--xp-muted);">Over 2.5 Goals</span>
                                    <strong style="color:var(--xp-text);">{{ $compStats['over_25_pct'] }}% ({{ $compStats['over_25_count'] }})</strong>
                                </div>
                                <div style="width:100%;height:4px;background:#141418;border-radius:2px;overflow:hidden;">
                                    <div style="width:{{ $compStats['over_25_pct'] }}%;height:100%;background:var(--xp-orange);"></div>
                                </div>
                            </div>

                            <!-- Over 1.5 -->
                            <div>
                                <div style="display:flex;justify-content:space-between;font-size:10.5px;margin-bottom:2px;">
                                    <span style="color:var(--xp-muted);">Over 1.5 Goals</span>
                                    <strong style="color:var(--xp-text);">{{ $compStats['over_15_pct'] }}% ({{ $compStats['over_15_count'] }})</strong>
                                </div>
                                <div style="width:100%;height:4px;background:#141418;border-radius:2px;overflow:hidden;">
                                    <div style="width:{{ $compStats['over_15_pct'] }}%;height:100%;background:var(--xp-text);"></div>
                                </div>
                            </div>

                            <!-- Clean Sheets -->
                            <div>
                                <div style="display:flex;justify-content:space-between;font-size:10.5px;margin-bottom:2px;">
                                    <span style="color:var(--xp-muted);">Clean Sheets</span>
                                    <strong style="color:var(--xp-text);">{{ $compStats['clean_sheets_pct'] }}% ({{ $compStats['clean_sheets_count'] }})</strong>
                                </div>
                                <div style="width:100%;height:4px;background:#141418;border-radius:2px;overflow:hidden;">
                                    <div style="width:{{ $compStats['clean_sheets_pct'] }}%;height:100%;background:#6B6B70;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Part 2: Outcome Distribution Donut -->
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;border-left:1px solid var(--xp-border-subtle);border-right:1px solid var(--xp-border-subtle);padding:0 10px;">
                        <div style="font-size:13px;font-weight:600;color:var(--xp-text);margin-bottom:6px;align-self:flex-start;">
                            Outcome Distribution
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <svg width="86" height="86" viewBox="0 0 140 140">
                                <circle cx="70" cy="70" r="46" fill="none" stroke="var(--xp-border)" stroke-width="22" />
                                @if ($compStats['finished'] > 0)
                                    <circle cx="70" cy="70" r="46" fill="none" stroke="var(--xp-orange)" stroke-width="22"
                                            stroke-dasharray="{{ $compStats['donut']['home_dash'] }} 289"
                                            stroke-dashoffset="{{ $compStats['donut']['home_offset'] }}"
                                            transform="rotate(-90 70 70)" />
                                    <circle cx="70" cy="70" r="46" fill="none" stroke="#6B6B70" stroke-width="22"
                                            stroke-dasharray="{{ $compStats['donut']['away_dash'] }} 289"
                                            stroke-dashoffset="{{ $compStats['donut']['away_offset'] }}"
                                            transform="rotate(-90 70 70)" />
                                    <circle cx="70" cy="70" r="46" fill="none" stroke="var(--xp-text)" stroke-width="22"
                                            stroke-dasharray="{{ $compStats['donut']['draw_dash'] }} 289"
                                            stroke-dashoffset="{{ $compStats['donut']['draw_offset'] }}"
                                            transform="rotate(-90 70 70)" />
                                @endif
                                <circle cx="70" cy="70" r="34" fill="var(--xp-surface)" />
                                <text x="70" y="68" fill="var(--xp-text)" font-size="14" font-weight="700" text-anchor="middle">
                                    {{ $compStats['finished'] }}
                                </text>
                                <text x="70" y="82" fill="var(--xp-muted)" font-size="9" text-anchor="middle">
                                    MATCHES
                                </text>
                            </svg>

                            <div style="display:flex;flex-direction:column;gap:5px;font-size:11px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:var(--xp-orange);"></span>
                                    <span style="color:var(--xp-muted);">Home:</span>
                                    <strong style="color:var(--xp-text);">{{ $compStats['home_win_pct'] }}% ({{ $compStats['home_wins'] }})</strong>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:var(--xp-text);"></span>
                                    <span style="color:var(--xp-muted);">Draw:</span>
                                    <strong style="color:var(--xp-text);">{{ $compStats['draw_pct'] }}% ({{ $compStats['draws'] }})</strong>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#6B6B70;"></span>
                                    <span style="color:var(--xp-muted);">Away:</span>
                                    <strong style="color:var(--xp-text);">{{ $compStats['away_win_pct'] }}% ({{ $compStats['away_wins'] }})</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Part 3: Competition Operations & Quick Shortcuts -->
                    <div style="display:flex;flex-direction:column;justify-content:space-between;">
                        <div style="font-size:13px;font-weight:600;color:var(--xp-text);margin-bottom:6px;">
                            Competition Operations
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <a href="#fixtures-section" style="display:flex;align-items:center;justify-content:space-between;padding:5px 6px;border-radius:4px;text-decoration:none;background:#0C0C0E;border:1px solid var(--xp-border-subtle);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--xp-border)'" onmouseout="this.style.borderColor='var(--xp-border-subtle)'">
                                <div style="font-size:11px;font-weight:600;color:var(--xp-text);">Upcoming Fixtures</div>
                                <span style="color:var(--xp-orange);font-size:11px;">{{ $compStats['scheduled'] }} matches &rsaquo;</span>
                            </a>
                            <a href="{{ route('track-record') }}" style="display:flex;align-items:center;justify-content:space-between;padding:5px 6px;border-radius:4px;text-decoration:none;background:#0C0C0E;border:1px solid var(--xp-border-subtle);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--xp-border)'" onmouseout="this.style.borderColor='var(--xp-border-subtle)'">
                                <div style="font-size:11px;font-weight:600;color:var(--xp-text);">Model Track Record</div>
                                <span style="color:var(--xp-away);font-size:11px;">Audit &rsaquo;</span>
                            </a>
                            <a href="{{ route('dev.api.index') }}" style="display:flex;align-items:center;justify-content:space-between;padding:5px 6px;border-radius:4px;text-decoration:none;background:#0C0C0E;border:1px solid var(--xp-border-subtle);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--xp-border)'" onmouseout="this.style.borderColor='var(--xp-border-subtle)'">
                                <div style="font-size:11px;font-weight:600;color:var(--xp-text);">API Inspector</div>
                                <span style="color:var(--xp-away);font-size:11px;">Quota &rsaquo;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. TOP KPI STRIP: Real Competition Vital Signs (Season level) -->
            <div class="grid-kpi-4" style="margin-top:2px;">
                <!-- Card 1: Season Progress -->
                <div class="kpi-card" style="padding:14px 16px;">
                    <span class="kpi-label">Season progress</span>
                    <div class="kpi-value" style="font-size:22px;margin:3px 0;">
                        {{ $compStats['finished'] }}
                        <span style="font-size:14px;color:var(--xp-muted);font-weight:500;">/ {{ $compStats['total'] }}</span>
                    </div>
                    <div class="kpi-delta positive" style="gap:4px;font-size:11px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                        <span>{{ $compStats['progress_pct'] }}%</span>
                        <span class="kpi-delta-context">played ({{ $compStats['scheduled'] }} left)</span>
                    </div>
                </div>

                <!-- Card 2: Scoring Rate -->
                <div class="kpi-card" style="padding:14px 16px;">
                    <span class="kpi-label">Scoring average</span>
                    <div class="kpi-value" style="font-size:22px;margin:3px 0;">
                        {{ number_format($compStats['goals_per_match'], 2) }}
                        <span style="font-size:12px;color:var(--xp-muted);font-weight:500;">goals/game</span>
                    </div>
                    <div class="kpi-delta" style="gap:4px;color:var(--xp-orange);font-size:11px;">
                        <span>{{ $compStats['total_goals'] }} goals</span>
                        <span class="kpi-delta-context">Home {{ number_format($compStats['home_goals_avg'], 1) }} &bull; Away {{ number_format($compStats['away_goals_avg'], 1) }}</span>
                    </div>
                </div>

                <!-- Card 3: Home Advantage -->
                <div class="kpi-card" style="padding:14px 16px;">
                    <span class="kpi-label">Home advantage</span>
                    <div class="kpi-value" style="font-size:22px;margin:3px 0;">{{ $compStats['home_win_pct'] }}%</div>
                    <div class="kpi-delta" style="gap:4px;color:var(--xp-text);font-size:11px;">
                        <span>{{ $compStats['home_wins'] }}W</span>
                        <span class="kpi-delta-context">&bull; {{ $compStats['draws'] }}D &bull; {{ $compStats['away_wins'] }}L</span>
                    </div>
                </div>

                <!-- Card 4: Model Predictions -->
                <div class="kpi-card" style="padding:14px 16px;">
                    <span class="kpi-label">Model predictions</span>
                    <div class="kpi-value" style="font-size:22px;margin:3px 0;">
                        {{ $compStats['predictions_count'] }}
                        <span style="font-size:12px;color:var(--xp-muted);font-weight:500;">active</span>
                    </div>
                    <a href="{{ route('changelog') }}" class="kpi-delta positive" style="gap:4px;text-decoration:none;font-size:11px;" title="View Model Changelog &amp; Architecture">
                        <span>Dixon-Coles</span>
                        <span class="kpi-delta-context">v1.2.1-h2h &rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- In-Page Match View Navigator Sidebar -->
        <div class="matches-sidebar-col">
            <aside class="view-sidebar-card">
                <!-- Status Pill & Header -->
                <div style="border-bottom: 1px solid var(--xp-border); padding-bottom: 10px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:9.5px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--xp-muted);display:flex;align-items:center;gap:5px;">
                            <span style="width:6px;height:6px;border-radius:50%;background:var(--xp-orange);display:inline-block;"></span>
                            Currently Viewing
                        </span>
                        <span class="badge" style="background:#141418;border:1px solid var(--xp-border);font-size:9.5px;color:var(--xp-orange);font-weight:700;">
                            {{ $competition?->short_name ?? 'League' }}
                        </span>
                    </div>
                    <div style="font-size:14px;font-weight:700;color:var(--xp-text);letter-spacing:-0.01em;">
                        {{ $currentViewLabel }}
                    </div>
                    <div style="font-size:10.5px;color:var(--xp-muted);margin-top:2px;">
                        {{ $competition?->name }} &bull; Season {{ $selectedSeason }}/{{ substr((string)($selectedSeason+1), -2) }}
                    </div>
                </div>

                <!-- Navigation List -->
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <!-- 1. Today's Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'today']) }}"
                       class="view-nav-btn {{ $statusFilter === 'today' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'today' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'today' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 14 14"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">Today's Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">{{ \Carbon\CarbonImmutable::now('UTC')->format('d M') }} kickoffs</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['today'] }}
                        </span>
                    </a>

                    <!-- 2. Future Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'future']) }}"
                       class="view-nav-btn {{ $statusFilter === 'future' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'future' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'future' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">Future Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">Upcoming matchdays</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['future'] }}
                        </span>
                    </a>

                    <!-- 3. Past Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'past']) }}"
                       class="view-nav-btn {{ $statusFilter === 'past' ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'past' ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'past' ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">Past Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">Full time &amp; results</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['past'] }}
                        </span>
                    </a>

                    <!-- 4. All Matches -->
                    <a href="{{ route('dashboard', ['competition' => $selectedComp, 'season' => $selectedSeason, 'status' => 'all']) }}"
                       class="view-nav-btn {{ $statusFilter === 'all' && !$dateFilterInfo ? 'active' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:22px;border-radius:4px;background:{{ $statusFilter === 'all' && !$dateFilterInfo ? 'rgba(217, 78, 0, 0.25)' : '#121215' }};display:flex;align-items:center;justify-content:center;color:{{ $statusFilter === 'all' && !$dateFilterInfo ? 'var(--xp-orange)' : 'var(--xp-muted)' }};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </div>
                            <div>
                                <div class="nav-btn-title" style="font-size:11.5px;font-weight:600;color:var(--xp-text);">All Matches</div>
                                <div style="font-size:10px;color:var(--xp-muted);">Full schedule</div>
                            </div>
                        </div>
                        <span class="nav-btn-badge" style="font-size:10px;padding:1px 6px;border-radius:8px;background:#18181C;color:var(--xp-muted);border:1px solid var(--xp-border);">
                            {{ $matchCounts['all'] }}
                        </span>
                    </a>
                </div>

                <!-- Quick League Switcher -->
                <div style="border-top: 1px solid var(--xp-border); padding-top: 10px;">
                    <div style="font-size:9.5px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--xp-muted);margin-bottom:6px;">
                        Quick League Filter
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:3px;">
                        @php
                            $leaguePills = [
                                'premier-league' => 'EPL',
                                'la-liga' => 'ESP',
                                'bundesliga' => 'GER',
                                'serie-a' => 'ITA',
                                'ligue-1' => 'FRA',
                            ];
                        @endphp
                        @foreach ($leaguePills as $slug => $code)
                            <a href="{{ route('dashboard', ['competition' => $slug, 'season' => $selectedSeason, 'status' => $statusFilter]) }}"
                               style="text-align:center;padding:5px 0;font-size:10px;font-weight:700;border-radius:4px;text-decoration:none;border:1px solid {{ $selectedComp === $slug ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $selectedComp === $slug ? 'var(--xp-orange)' : '#101014' }};color:{{ $selectedComp === $slug ? '#000000' : 'var(--xp-muted)' }};transition:all 0.15s ease;">
                                {{ $code }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Provenance & Quota Card -->
                <div style="border-top: 1px solid var(--xp-border); padding-top: 10px; font-size: 10.5px; color: var(--xp-muted); display:flex; flex-direction:column; gap:5px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Engine</span>
                        <a href="{{ route('changelog') }}" style="color:var(--xp-orange);font-size:10.5px;text-decoration:none;font-weight:700;" title="View Model Changelog &amp; Architecture">
                            v1.2.1-h2h &rarr;
                        </a>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Integrity</span>
                        <strong style="color:var(--xp-ok);display:flex;align-items:center;gap:4px;font-size:10.5px;">
                            <span style="display:inline-block;width:5px;height:5px;border-radius:50%;background:var(--xp-ok);"></span>
                            Zero Leakage
                        </strong>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span>Offline</span>
                        <span style="color:var(--xp-away);font-size:10px;">Local DB only</span>
                    </div>
                    <a href="{{ route('changelog') }}" style="margin-top:4px;padding:5px 8px;background:rgba(217,78,0,0.1);border:1px solid rgba(217,78,0,0.25);border-radius:4px;color:var(--xp-orange);font-size:10.5px;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:space-between;" title="View Model Changelog &amp; Documentation">
                        <span>Model Changelog &amp; Docs</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </aside>
        </div>
    </div>
    </div> <!-- /#dashboard-real-content -->

    <!-- Real-Time Countdown Ticker for Upcoming Fixtures -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updateMatchCountdowns() {
                const nowSec = Math.floor(Date.now() / 1000);
                document.querySelectorAll('[data-countdown-kickoff]').forEach(function (el) {
                    const kickoffSec = parseInt(el.getAttribute('data-countdown-kickoff'), 10);
                    if (!kickoffSec || isNaN(kickoffSec)) return;

                    const diff = kickoffSec - nowSec;
                    if (diff <= 0) {
                        el.textContent = '⚡ Starting now / In-Play';
                        el.classList.add('urgent');
                        return;
                    }

                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;

                    if (diff < 3600) {
                        el.textContent = '⚡ Starts in ' + minutes + 'm ' + String(seconds).padStart(2, '0') + 's';
                        el.classList.add('urgent');
                    } else if (diff < 86400) {
                        el.textContent = '⏳ Starts in ' + hours + 'h ' + minutes + 'm ' + String(seconds).padStart(2, '0') + 's';
                        if (hours < 3) el.classList.add('urgent');
                    }
                });
            }

            updateMatchCountdowns();
            setInterval(updateMatchCountdowns, 1000);
        });
    </script>
</x-layouts.app>
