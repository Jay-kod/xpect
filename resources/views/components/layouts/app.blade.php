<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | Xpect Football Analytics</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/mark-orange.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('brand/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('brand/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            /* Canonical UI_GUIDELINES.md Tokens (Darker burnt orange & pitch black) */
            --xp-orange: #D94E00;
            --xp-orange-hover: #E85B07;
            --xp-orange-press: #BC4300;
            --xp-black: #000000;
            --xp-surface: #0C0C0E;
            --xp-surface-hover: #141417;
            --xp-surface-active: #1A1A1E;
            --xp-border: #1E1E22;
            --xp-border-subtle: #131315;
            --xp-border-light: #2A2A30;
            --xp-text: #FFFFFF;
            --xp-muted: #A1A1A6;
            --xp-away: #6B6B70;
            --xp-ok: #3DDC84;
            --xp-error: #FF4D4D;

            /* Compatibility aliases mapped directly to canonical tokens */
            --text-primary: var(--xp-text);
            --text-secondary: var(--xp-text);
            --text-main: var(--xp-text);
            --text-muted: var(--xp-muted);
            --text-dim: var(--xp-away);
            --border: var(--xp-border);
            --border-light: var(--xp-border-light);
            --accent: var(--xp-orange);
            --accent-hover: var(--xp-orange-hover);
            --bg-body: var(--xp-black);
            --bg-card: var(--xp-surface);
            --bg-card-hover: var(--xp-surface-hover);
            --bg-card-header: var(--xp-surface);
            --green: var(--xp-ok);
            --red: var(--xp-error);
            --yellow: var(--xp-orange);
            --purple: var(--xp-away);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        *:focus-visible {
            outline: 2px solid var(--xp-orange);
            outline-offset: 2px;
        }

        /* Ultra-Thin Modern Scrollbars (Page & Containers) */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #222227;
            border-radius: 9999px;
            transition: background 0.15s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--xp-orange);
        }

        /* Firefox Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: #222227 transparent;
        }
        body {
            background-color: var(--xp-black);
            background-image: url("data:image/svg+xml,%3Csvg width='56' height='98' viewBox='0 0 56 98' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M28 0l28 16v32L28 64 0 48V16zM28 98l28-16V50L28 66 0 50v32z' fill='none' stroke='%23ffffff' stroke-width='1' stroke-opacity='0.035'/%3E%3C/svg%3E");
            color: var(--xp-text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            overflow-x: clip;
        }

        h1, h2, h3, h4, .card-title, .kpi-value, .sidebar-brand {
            font-family: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Sidebar */
        .sidebar {
            width: 244px;
            min-width: 244px;
            background: #070709;
            border-right: 1px solid var(--xp-border);
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            padding: 16px 12px;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2px 4px 14px 4px;
            text-decoration: none;
            border-bottom: 1px solid var(--xp-border-subtle);
            margin-bottom: 14px;
        }
        .sidebar-brand-left {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--xp-text);
            font-size: 19px;
            font-weight: 700;
            letter-spacing: -0.03em;
        }
        .sidebar-brand svg {
            width: 24px;
            height: 28px;
            flex-shrink: 0;
        }
        .brand-badge {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: var(--xp-orange);
            background: rgba(217, 78, 0, 0.12);
            border: 1px solid rgba(217, 78, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
        }


        .sidebar-section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #63636C;
            padding: 12px 8px 5px 8px;
        }

        .sidebar-nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-bottom: 6px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 7px;
            color: var(--xp-muted);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .sidebar-link-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-link-content svg {
            color: var(--xp-away);
            transition: color 0.15s ease;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.04);
            color: var(--xp-text);
        }
        .sidebar-link:hover .sidebar-link-content svg {
            color: var(--xp-text);
        }
        .sidebar-link.active {
            background: rgba(217, 78, 0, 0.10);
            color: var(--xp-text);
            border-left: 3px solid var(--xp-orange);
            padding-left: 7px;
            font-weight: 700;
        }
        .sidebar-link.active .sidebar-link-content svg {
            color: var(--xp-orange);
        }
        .nav-tag {
            font-size: 9px;
            font-weight: 700;
            color: #6E6E78;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid #1E1E24;
            padding: 1px 5px;
            border-radius: 4px;
            letter-spacing: 0.04em;
            transition: all 0.15s ease;
        }
        .sidebar-link.active .nav-tag {
            color: var(--xp-orange);
            background: rgba(217, 78, 0, 0.15);
            border-color: rgba(217, 78, 0, 0.35);
        }
        .sidebar-link:hover .nav-tag {
            color: var(--xp-text);
            border-color: #383842;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--xp-ok);
            box-shadow: 0 0 0 0 rgba(61, 220, 132, 0.6);
            animation: statusPulse 2s infinite;
        }
        @keyframes statusPulse {
            0% { box-shadow: 0 0 0 0 rgba(61, 220, 132, 0.6); }
            70% { box-shadow: 0 0 0 6px rgba(61, 220, 132, 0); }
            100% { box-shadow: 0 0 0 0 rgba(61, 220, 132, 0); }
        }

        /* Main layout wrapper */
        .main-wrapper {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .topbar {
            height: 52px;
            background: var(--xp-surface);
            border-bottom: 1px solid var(--xp-border);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .topbar-btn {
            background: transparent;
            border: none;
            color: var(--xp-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.15s;
        }
        .topbar-btn:hover {
            color: var(--xp-text);
        }
        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--xp-text);
        }
        .topbar-breadcrumb svg {
            color: var(--xp-muted);
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--xp-surface-hover);
            border: 1px solid var(--xp-border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--xp-muted);
        }

        /* Content container */
        .content-container {
            padding: 20px 24px 40px 24px;
            max-width: 1360px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }

        /* Alert notifications */
        .alert {
            padding: 10px 14px;
            border-radius: 7px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 500;
        }
        .alert-success { background: rgba(61, 220, 132, 0.12); color: var(--xp-ok); border: 1px solid rgba(61, 220, 132, 0.3); }
        .alert-error { background: rgba(255, 77, 77, 0.12); color: var(--xp-error); border: 1px solid rgba(255, 77, 77, 0.3); }
        .alert-info { background: rgba(255, 107, 0, 0.12); color: var(--xp-orange); border: 1px solid rgba(255, 107, 0, 0.3); }

        /* Card utilities */
        .card {
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 18px 20px;
            position: relative;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--xp-text);
            letter-spacing: -0.01em;
        }
        .card-subtitle {
            font-size: 12px;
            color: var(--xp-muted);
            margin-top: 2px;
        }

        /* KPI Metric cards */
        .grid-kpi-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 14px;
        }
        @media (max-width: 1024px) {
            .grid-kpi-4 { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .grid-kpi-4 { grid-template-columns: 1fr; }
        }

        .kpi-card {
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
        }
        .kpi-label {
            font-size: 13px;
            color: var(--xp-muted);
            font-weight: 500;
            margin-bottom: 8px;
        }
        .kpi-value {
            font-size: 26px;
            font-weight: 700;
            color: var(--xp-text);
            letter-spacing: -0.02em;
            line-height: 1.1;
            margin-bottom: 8px;
        }
        .kpi-delta {
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .kpi-delta.positive { color: var(--xp-ok); }
        .kpi-delta.negative { color: var(--xp-error); }
        .kpi-delta-context { color: var(--xp-muted); margin-left: 3px; }

        /* Secondary 3-Card Grid */
        .grid-3-col {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 18px;
        }
        @media (max-width: 1024px) {
            .grid-3-col { grid-template-columns: 1fr; }
        }

        /* General Grid Utilities for other pages */
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 14px; margin-bottom: 18px; }

        .stat-card {
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
        }
        .stat-label { font-size: 12px; text-transform: uppercase; color: var(--xp-muted); font-weight: 600; letter-spacing: 0.04em; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--xp-text); margin-top: 4px; }
        .stat-meta { font-size: 12px; color: var(--xp-muted); margin-top: 4px; }

        /* Form elements */
        .select-pill {
            background: #101012;
            border: 1px solid var(--xp-border);
            border-radius: 6px;
            padding: 5px 10px;
            color: var(--xp-text);
            font-size: 12px;
            font-weight: 500;
            outline: none;
            cursor: pointer;
        }
        .select-pill:focus {
            border-color: var(--xp-orange);
        }
        .form-control {
            background: #101012;
            border: 1px solid var(--xp-border);
            border-radius: 6px;
            padding: 7px 10px;
            color: var(--xp-text);
            font-size: 13px;
            outline: none;
        }
        .form-control:focus { border-color: var(--xp-orange); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-primary { 
            background: var(--xp-orange); 
            color: #000000 !important; 
            border: none;
            font-weight: 700;
        }
        .btn-primary:hover { 
            background: var(--xp-orange-hover); 
            color: #000000 !important;
        }
        .btn-primary:active { 
            background: var(--xp-orange-press); 
            color: #000000 !important;
        }
        .btn-secondary { 
            background: var(--xp-surface-hover); 
            color: var(--xp-text); 
            border: 1px solid var(--xp-border); 
        }
        .btn-secondary:hover { 
            background: var(--xp-surface-active); 
            border-color: var(--xp-orange); 
        }
        .btn-sm { padding: 4px 8px; font-size: 11px; border-radius: 5px; }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .badge-success { background: rgba(61, 220, 132, 0.12); color: var(--xp-ok); }
        .badge-danger { background: rgba(255, 77, 77, 0.12); color: var(--xp-error); }
        .badge-warning { background: rgba(255, 107, 0, 0.12); color: var(--xp-orange); }
        .badge-info { background: rgba(255, 255, 255, 0.1); color: var(--xp-text); }

        /* Table */
        .table-container {
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th {
            background: #101012;
            padding: 10px 14px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--xp-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--xp-border);
        }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--xp-border);
            color: var(--xp-text);
            font-size: 13px;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }

        /* Footer */
        .footer {
            border-top: 1px solid var(--xp-border);
            padding: 14px 24px;
            text-align: center;
            font-size: 12px;
            color: var(--xp-dim);
            background: var(--xp-surface);
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <!-- Brand -->
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-left">
                <svg width="24" height="28" viewBox="0 0 100 116" fill="none">
                    <path d="M50 3 L95.5 29.3 L95.5 86.7 L50 113 L4.5 86.7 L4.5 29.3 Z" fill="#D94E00" stroke="#D94E00" stroke-width="4" stroke-linejoin="round"/>
                    <rect x="21" y="48" width="9.5" height="40" rx="2.5" fill="#612403"/>
                    <rect x="36.5" y="27" width="9.5" height="61" rx="2.5" fill="#000000"/>
                    <rect x="52" y="42" width="9.5" height="46" rx="2.5" fill="#612403"/>
                    <rect x="67.5" y="66" width="9.5" height="22" rx="2.5" fill="#612403"/>
                    <line x1="17" y1="90" x2="81" y2="90" stroke="#000000" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                <span>xpect</span>
            </div>
            <span class="brand-badge">v1.2</span>
        </a>

        <!-- Section: Overview -->
        <div class="sidebar-section-title">Overview</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard*') && !request()->has('competition') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('today') }}" class="sidebar-link {{ request()->routeIs('today*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Today's Fixtures</span>
                    </div>
                    <span class="nav-tag" style="color:var(--xp-orange);border-color:rgba(255,107,0,0.3);">HUB</span>
                </a>
            </li>
            <li>
                <a href="{{ route('track-record') }}" class="sidebar-link {{ request()->routeIs('track-record*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        <span>Track Record</span>
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('ai-reasoning') }}" class="sidebar-link {{ request()->routeIs('ai-reasoning*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2a10 10 0 0 1 10 10c0 4.42-2.87 8.17-6.84 9.5-.5.08-.66-.23-.66-.5v-1.69c0-.62-.22-1.07-.47-1.28 1.55-.17 3.18-.78 3.18-3.47 0-.76-.27-1.39-.72-1.88.07-.18.31-.89-.07-1.86 0 0-.59-.19-1.93.72a6.7 6.7 0 0 0-3.52 0c-1.34-.91-1.93-.72-1.93-.72-.38.97-.14 1.68-.07 1.86-.45.49-.72 1.12-.72 1.88 0 2.68 1.62 3.3 3.16 3.48-.2.18-.38.5-.44 1-.4.18-1.4.48-2.02-.58 0 0-.36-.66-1.05-.71 0 0-.67-.01-.05.42 0 0 .45.21.76 1.02.18.52.82 1.54 2.32 1.09v1.07c0 .28-.18.58-.68.49A10 10 0 0 1 2 12 10 10 0 0 1 12 2z"></path>
                        </svg>
                        <span>AI Reasoning</span>
                    </div>
                    <span class="nav-tag" style="color:#3DDC84;border-color:rgba(61,220,132,0.3);">STUDIO</span>
                </a>
            </li>
            <li>
                <a href="{{ route('motm') }}" class="sidebar-link {{ request()->routeIs('motm*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Man of the Match</span>
                    </div>
                    <span class="nav-tag" style="color:var(--xp-orange);border-color:rgba(255,107,0,0.3);">MOTM</span>
                </a>
            </li>
        </ul>

        <!-- Section: Competitions -->
        <div class="sidebar-section-title">Competitions</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard', ['competition' => 'premier-league']) }}" class="sidebar-link {{ request()->get('competition') === 'premier-league' ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                            <path d="M2 12h20"></path>
                        </svg>
                        <span>Premier League (EPL)</span>
                    </div>
                    <span class="nav-tag" style="color:var(--xp-orange);border-color:rgba(217,78,0,0.3);font-weight:700;">EPL</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard', ['competition' => 'champions-league']) }}" class="sidebar-link {{ request()->get('competition') === 'champions-league' ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Champions League</span>
                    </div>
                    <span class="nav-tag" style="color:var(--xp-orange);border-color:rgba(217,78,0,0.3);font-weight:700;">UCL</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard', ['competition' => 'la-liga']) }}" class="sidebar-link {{ request()->get('competition') === 'la-liga' ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span>La Liga</span>
                    </div>
                    <span class="nav-tag">ESP</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard', ['competition' => 'bundesliga']) }}" class="sidebar-link {{ request()->get('competition') === 'bundesliga' ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Bundesliga</span>
                    </div>
                    <span class="nav-tag">GER</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard', ['competition' => 'serie-a']) }}" class="sidebar-link {{ request()->get('competition') === 'serie-a' ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Serie A</span>
                    </div>
                    <span class="nav-tag">ITA</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard', ['competition' => 'ligue-1']) }}" class="sidebar-link {{ request()->get('competition') === 'ligue-1' ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                        <span>Ligue 1</span>
                    </div>
                    <span class="nav-tag">FRA</span>
                </a>
            </li>
        </ul>

        <!-- Section: Administration -->
        <div class="sidebar-section-title">Administration</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('system-health') }}" class="sidebar-link {{ request()->routeIs('system-health*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                        <span>System Health</span>
                    </div>
                    <span class="nav-tag" style="color:var(--xp-ok);border-color:rgba(61,220,132,0.3);">AUDIT</span>
                </a>
            </li>
            <li>
                <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span>Settings</span>
                    </div>
                    <span class="nav-tag">CFG</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dev.api.index') }}" class="sidebar-link {{ request()->routeIs('dev.api*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="4 17 10 11 4 5"></polyline>
                            <line x1="12" y1="19" x2="20" y2="19"></line>
                        </svg>
                        <span>Dev Inspector</span>
                    </div>
                    <span class="nav-tag">API</span>
                </a>
            </li>
            <li>
                <a href="{{ route('docs') }}" class="sidebar-link {{ request()->routeIs('docs*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                        <span>Documentation</span>
                    </div>
                    <span class="nav-tag" style="background: rgba(217,78,0,0.15); color: var(--xp-orange); font-weight: 700;">DOCS</span>
                </a>
            </li>
            <li>
                <a href="{{ route('changelog') }}" class="sidebar-link {{ request()->routeIs('changelog*') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Changelog</span>
                    </div>
                    <span class="nav-tag">v1.2</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-btn" title="Toggle Sidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                        <line x1="9" y1="3" x2="9" y2="21"></line>
                    </svg>
                </button>
                <div class="topbar-breadcrumb">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>{{ $title ?? 'Dashboard' }}</span>
                </div>
            </div>

            <div class="topbar-right">
                <!-- Universal Temporal (Date & Time) Control Widget -->
                @php
                    $rawDate = request('date');
                    $rawTimeSlot = request('time_slot');
                    $rawTime = request('time');
                    $rawPreset = request('preset');
                    $isFiltered = request()->filled('date') || request()->filled('time_slot') || request()->filled('time') || request()->filled('preset');

                    $parsedNow = \Carbon\CarbonImmutable::now('UTC');
                    $activeDateStr = $rawDate ?? ($rawPreset === 'today' ? $parsedNow->format('Y-m-d') : null);

                    try {
                        $activeCarbon = $activeDateStr ? \Carbon\CarbonImmutable::parse($activeDateStr, 'UTC') : $parsedNow;
                    } catch (\Throwable) {
                        $activeCarbon = $parsedNow;
                    }

                    $prevDayUrl = url()->current() . '?' . http_build_query(array_merge(request()->except(['page', 'preset']), ['date' => $activeCarbon->subDay()->format('Y-m-d')]));
                    $nextDayUrl = url()->current() . '?' . http_build_query(array_merge(request()->except(['page', 'preset']), ['date' => $activeCarbon->addDay()->format('Y-m-d')]));

                    $slotBadge = match ($rawTimeSlot) {
                        'early' => '12:30 Early',
                        'afternoon' => '15:00 Prime',
                        'evening' => '17:30+ Night',
                        default => $rawTime ? $rawTime . ' UTC' : null,
                    };

                    $btnDisplayLabel = $activeDateStr 
                        ? ($activeDateStr === $parsedNow->format('Y-m-d') ? 'Today, ' . $activeCarbon->format('d M') : $activeCarbon->format('D, d M Y')) 
                        : 'Select Date & Time';
                @endphp

                <div class="temporal-widget-container" style="position:relative;display:flex;align-items:center;gap:4px;">
                    <!-- Prev Day Button -->
                    <a href="{{ $prevDayUrl }}" 
                       class="btn btn-secondary btn-sm" 
                       style="padding:3px 7px;font-size:10px;color:var(--xp-muted);border-color:var(--xp-border);background:#0C0C0E;display:flex;align-items:center;justify-content:center;height:28px;" 
                       title="Step to previous day ({{ $activeCarbon->subDay()->format('D, d M') }})">
                        &larr;
                    </a>

                    <!-- Popover Toggle Button -->
                    <button type="button" 
                            id="temporal-toggle-btn"
                            onclick="const p = document.getElementById('temporal-dropdown-panel'); p.style.display = p.style.display === 'block' ? 'none' : 'block';"
                            style="display:flex;align-items:center;gap:6px;background:{{ $isFiltered ? 'rgba(217,78,0,0.12)' : '#0C0C0E' }};border:1px solid {{ $isFiltered ? 'var(--xp-orange)' : 'var(--xp-border)' }};border-radius:6px;padding:3px 10px;cursor:pointer;color:var(--xp-text);font-size:11px;font-weight:600;height:28px;transition:all 0.15s ease;"
                            title="Open Date & Kickoff Time Filter">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--xp-orange);">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span style="color:{{ $isFiltered ? 'var(--xp-orange)' : 'var(--xp-text)' }};">
                            {{ $btnDisplayLabel }}
                            @if ($slotBadge)
                                &bull; <span style="background:var(--xp-orange);color:#000000;font-size:9.5px;padding:1px 5px;border-radius:3px;font-weight:700;">{{ $slotBadge }}</span>
                            @endif
                        </span>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--xp-away);margin-left:2px;">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <!-- Next Day Button -->
                    <a href="{{ $nextDayUrl }}" 
                       class="btn btn-secondary btn-sm" 
                       style="padding:3px 7px;font-size:10px;color:var(--xp-muted);border-color:var(--xp-border);background:#0C0C0E;display:flex;align-items:center;justify-content:center;height:28px;" 
                       title="Step to next day ({{ $activeCarbon->addDay()->format('D, d M') }})">
                        &rarr;
                    </a>

                    @if ($isFiltered)
                        <!-- One-click Reset Button -->
                        <a href="{{ url()->current() }}?{{ http_build_query(request()->except(['date', 'time_slot', 'time', 'preset', 'page'])) }}" 
                           class="btn btn-secondary btn-sm" 
                           style="padding:3px 7px;font-size:10px;color:var(--xp-orange);border-color:rgba(217,78,0,0.4);background:#0C0C0E;height:28px;display:flex;align-items:center;" 
                           title="Clear date and time filters (show all)">
                            ✕
                        </a>
                    @endif

                    <!-- Temporal Popover Panel -->
                    <div id="temporal-dropdown-panel" 
                         style="display:none;position:absolute;top:calc(100% + 8px);right:0;width:340px;background:#0C0C0E;border:1px solid var(--xp-border);box-shadow:0 12px 36px rgba(0,0,0,0.9);border-radius:8px;padding:16px;z-index:9999;">
                        
                        <!-- Panel Header -->
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1px solid var(--xp-border-subtle);padding-bottom:8px;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--xp-orange);"></span>
                                <span style="font-size:12px;font-weight:700;color:var(--xp-text);letter-spacing:-0.01em;">Date &amp; Kickoff Time Selector</span>
                            </div>
                            <button type="button" 
                                    onclick="document.getElementById('temporal-dropdown-panel').style.display='none'"
                                    style="background:transparent;border:none;color:var(--xp-away);font-size:14px;cursor:pointer;padding:0 4px;"
                                    title="Close panel">✕</button>
                        </div>

                        <!-- 1. Quick Presets -->
                        <div style="font-size:10px;font-weight:700;color:var(--xp-away);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">
                            Quick Presets
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px;margin-bottom:14px;">
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'date', 'time', 'time_slot']), ['preset' => 'today'])) }}" 
                               style="text-align:center;padding:5px 4px;font-size:11px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid {{ request('preset') === 'today' || $activeDateStr === $parsedNow->format('Y-m-d') ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ request('preset') === 'today' || $activeDateStr === $parsedNow->format('Y-m-d') ? 'rgba(217,78,0,0.15)' : '#141417' }};color:var(--xp-text);">
                                Today
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'date', 'time', 'time_slot']), ['preset' => 'yesterday'])) }}" 
                               style="text-align:center;padding:5px 4px;font-size:11px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid var(--xp-border);background:#141417;color:var(--xp-text);">
                                Yesterday
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'date', 'time', 'time_slot']), ['preset' => 'tomorrow'])) }}" 
                               style="text-align:center;padding:5px 4px;font-size:11px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid var(--xp-border);background:#141417;color:var(--xp-text);">
                                Tomorrow
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'date', 'time', 'time_slot']), ['preset' => 'weekend'])) }}" 
                               style="text-align:center;padding:5px 4px;font-size:11px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid var(--xp-border);background:#141417;color:var(--xp-text);">
                                Weekend
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'date', 'time', 'time_slot']), ['preset' => 'next_matchday'])) }}" 
                               style="text-align:center;padding:5px 4px;font-size:11px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid rgba(217,78,0,0.3);background:rgba(217,78,0,0.08);color:var(--xp-orange);" 
                               title="Jump to next upcoming fixture date">
                                Next Round
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'date', 'time', 'time_slot']), ['preset' => 'last_matchday'])) }}" 
                               style="text-align:center;padding:5px 4px;font-size:11px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid var(--xp-border);background:#141417;color:var(--xp-text);" 
                               title="Jump to latest finished fixture date">
                                Last Round
                            </a>
                        </div>

                        <!-- 2. Kickoff Time Window -->
                        <div style="font-size:10px;font-weight:700;color:var(--xp-away);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">
                            Kickoff Time Window (UTC)
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-bottom:14px;">
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'time', 'time_slot']), ['time_slot' => 'all'])) }}" 
                               style="text-align:center;padding:5px 6px;font-size:10.5px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid {{ !$rawTimeSlot && !$rawTime ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ !$rawTimeSlot && !$rawTime ? 'rgba(217,78,0,0.15)' : '#141417' }};color:var(--xp-text);">
                                ⚡ All Times
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'time', 'time_slot']), ['time_slot' => 'early'])) }}" 
                               style="text-align:center;padding:5px 6px;font-size:10.5px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid {{ $rawTimeSlot === 'early' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $rawTimeSlot === 'early' ? 'rgba(217,78,0,0.15)' : '#141417' }};color:var(--xp-text);">
                                🌤 Early (12:30)
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'time', 'time_slot']), ['time_slot' => 'afternoon'])) }}" 
                               style="text-align:center;padding:5px 6px;font-size:10.5px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid {{ $rawTimeSlot === 'afternoon' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $rawTimeSlot === 'afternoon' ? 'rgba(217,78,0,0.15)' : '#141417' }};color:var(--xp-text);">
                                ⚽ Prime (15:00)
                            </a>
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['page', 'time', 'time_slot']), ['time_slot' => 'evening'])) }}" 
                               style="text-align:center;padding:5px 6px;font-size:10.5px;font-weight:600;border-radius:4px;text-decoration:none;border:1px solid {{ $rawTimeSlot === 'evening' ? 'var(--xp-orange)' : 'var(--xp-border)' }};background:{{ $rawTimeSlot === 'evening' ? 'rgba(217,78,0,0.15)' : '#141417' }};color:var(--xp-text);">
                                🌙 Night (17:30+)
                            </a>
                        </div>

                        <!-- 3. Custom Date & Time Form -->
                        <div style="font-size:10px;font-weight:700;color:var(--xp-away);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">
                            Specific Date &amp; Kickoff Time
                        </div>
                        <form method="GET" action="{{ url()->current() }}" style="margin:0;">
                            @foreach (request()->except(['date', 'time', 'time_slot', 'preset', 'page']) as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach
                            <div style="display:flex;gap:6px;margin-bottom:8px;">
                                <div style="flex:1;">
                                    <label style="display:block;font-size:9.5px;color:var(--xp-muted);margin-bottom:2px;">Match Date</label>
                                    <input type="date" name="date" value="{{ $activeDateStr }}" 
                                           style="width:100%;padding:6px 8px;font-size:11px;background:#141417;border:1px solid var(--xp-border);border-radius:4px;color:var(--xp-text);outline:none;">
                                </div>
                                <div style="width:105px;">
                                    <label style="display:block;font-size:9.5px;color:var(--xp-muted);margin-bottom:2px;">Kickoff (UTC)</label>
                                    <input type="time" name="time" value="{{ request('time') }}" 
                                           style="width:100%;padding:6px 8px;font-size:11px;background:#141417;border:1px solid var(--xp-border);border-radius:4px;color:var(--xp-text);outline:none;">
                                </div>
                            </div>
                            <div style="display:flex;gap:6px;">
                                <button type="submit" class="btn btn-primary btn-sm" style="flex:1;padding:6px 0;font-size:11px;font-weight:700;display:flex;justify-content:center;align-items:center;">
                                    Apply Filter
                                </button>
                                @if ($isFiltered)
                                    <a href="{{ url()->current() }}?{{ http_build_query(request()->except(['date', 'time', 'time_slot', 'preset', 'page'])) }}" 
                                       class="btn btn-secondary btn-sm" style="padding:6px 10px;font-size:11px;text-decoration:none;">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    document.addEventListener('click', function(e) {
                        const container = e.target.closest('.temporal-widget-container');
                        const panel = document.getElementById('temporal-dropdown-panel');
                        if (!container && panel) {
                            panel.style.display = 'none';
                        }
                    });
                </script>

                <button class="topbar-btn" title="Notifications">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </button>
                <div class="user-avatar" title="Local Engineer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="content-container">
            @if (session('success'))
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;">✕</button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;">✕</button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info">
                    <span>{{ session('info') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;">✕</button>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="footer">
            Xpect &bull; Poisson Probability Model &bull; Leakage-Safe Strict Data Flow &bull; Single-User Local Tool
        </footer>
    </div>
</body>
</html>
