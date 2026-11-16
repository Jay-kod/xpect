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
    <script>
        const XPECT_THEME_STORAGE_KEY = 'xpect-theme';
        const XPECT_CUSTOM_THEMES_STORAGE_KEY = 'xpect-custom-themes';
        const XPECT_THEME_COLORS = Object.freeze({
            page: '--xp-black',
            sidebar: '--xp-sidebar',
            surface: '--xp-surface',
            surfaceHover: '--xp-surface-hover',
            surfaceActive: '--xp-surface-active',
            border: '--xp-border',
            borderSubtle: '--xp-border-subtle',
            borderLight: '--xp-border-light',
            text: '--xp-text',
            muted: '--xp-muted',
            away: '--xp-away',
            accent: '--xp-orange',
            accentHover: '--xp-orange-hover',
            accentPress: '--xp-orange-press',
        });
        const XPECT_THEME_PRESETS = [
            {
                id: 'pitch-black',
                name: 'Pitch Black',
                description: 'The original deep-black Xpect look.',
                mode: 'dark',
                colors: {
                    page: '#000000', sidebar: '#070709', surface: '#0C0C0E',
                    surfaceHover: '#141417', surfaceActive: '#1A1A1E',
                    border: '#1E1E22', borderSubtle: '#131315', borderLight: '#2A2A30',
                    text: '#FFFFFF', muted: '#A1A1A6', away: '#6B6B70',
                    accent: '#D94E00', accentHover: '#E85B07', accentPress: '#BC4300',
                },
            },
            {
                id: 'obsidian',
                name: 'Obsidian',
                description: 'A softer charcoal dark palette.',
                mode: 'dark',
                colors: {
                    page: '#121416', sidebar: '#101214', surface: '#1B1E22',
                    surfaceHover: '#252A30', surfaceActive: '#30363D',
                    border: '#353B43', borderSubtle: '#252A30', borderLight: '#444B55',
                    text: '#F3F4F6', muted: '#B4BAC3', away: '#8A929E',
                    accent: '#D94E00', accentHover: '#E85B07', accentPress: '#BC4300',
                },
            },
            {
                id: 'midnight',
                name: 'Midnight',
                description: 'A cool, blue-tinted dark palette.',
                mode: 'dark',
                colors: {
                    page: '#07111F', sidebar: '#091525', surface: '#0E1E31',
                    surfaceHover: '#152944', surfaceActive: '#1C3555',
                    border: '#203B5D', borderSubtle: '#152A42', borderLight: '#345271',
                    text: '#F2F7FC', muted: '#B4C7D9', away: '#829AB2',
                    accent: '#D94E00', accentHover: '#E85B07', accentPress: '#BC4300',
                },
            },
            {
                id: 'light',
                name: 'Light',
                description: 'A bright neutral palette with Xpect orange accents.',
                mode: 'light',
                colors: {
                    page: '#F3F5F7', sidebar: '#E9EDF2', surface: '#FFFFFF',
                    surfaceHover: '#F0F2F5', surfaceActive: '#E5E9EE',
                    border: '#D8DEE7', borderSubtle: '#E9EDF2', borderLight: '#C2CBD6',
                    text: '#1A2028', muted: '#4C5662', away: '#626D79',
                    accent: '#D94E00', accentHover: '#E85B07', accentPress: '#BC4300',
                },
            },
        ];
        const isXpectHexColor = (value) => typeof value === 'string' && /^#[0-9A-Fa-f]{6}$/.test(value);
        const isXpectTheme = (theme) => theme
            && typeof theme.id === 'string'
            && typeof theme.name === 'string'
            && ['dark', 'light'].includes(theme.mode)
            && theme.colors
            && Object.keys(XPECT_THEME_COLORS).every((key) => isXpectHexColor(theme.colors[key]));
        let xpectCustomThemes = [];
        try {
            const storedThemes = JSON.parse(window.localStorage.getItem(XPECT_CUSTOM_THEMES_STORAGE_KEY) || '[]');
            if (Array.isArray(storedThemes)) {
                xpectCustomThemes = storedThemes.filter((theme) => isXpectTheme(theme) && theme.id.startsWith('custom-'));
            }
        } catch (error) {
            console.warn('Unable to load saved Xpect themes from browser storage.', error);
        }
        const getXpectThemes = () => [...XPECT_THEME_PRESETS, ...xpectCustomThemes];
        const savedThemeId = window.localStorage.getItem(XPECT_THEME_STORAGE_KEY);
        const initialTheme = getXpectThemes().find((theme) => theme.id === savedThemeId) || XPECT_THEME_PRESETS[0];
        const applyXpectTheme = (theme) => {
            document.documentElement.dataset.theme = theme.id;
            document.documentElement.dataset.themeMode = theme.mode;
            document.documentElement.style.colorScheme = theme.mode;
            Object.entries(XPECT_THEME_COLORS).forEach(([key, variable]) => {
                document.documentElement.style.setProperty(variable, theme.colors[key]);
            });
        };
        applyXpectTheme(initialTheme);
        window.XpectThemeManager = Object.freeze({
            presets: XPECT_THEME_PRESETS,
            colors: XPECT_THEME_COLORS,
            getThemes: getXpectThemes,
            getActiveTheme: () => getXpectThemes().find((theme) => theme.id === document.documentElement.dataset.theme) || XPECT_THEME_PRESETS[0],
            activate: (themeId) => {
                const theme = getXpectThemes().find((candidate) => candidate.id === themeId);
                if (!theme) {
                    throw new Error('The selected Xpect theme is unavailable.');
                }
                window.localStorage.setItem(XPECT_THEME_STORAGE_KEY, theme.id);
                applyXpectTheme(theme);
            },
            saveCustomThemes: (themes) => {
                if (!Array.isArray(themes) || !themes.every((theme) => isXpectTheme(theme) && theme.id.startsWith('custom-'))) {
                    throw new Error('Custom theme data is invalid.');
                }
                window.localStorage.setItem(XPECT_CUSTOM_THEMES_STORAGE_KEY, JSON.stringify(themes));
                xpectCustomThemes = themes;
            },
        });
    </script>
    <style>
        :root {
            color-scheme: dark;

            /* Canonical UI_GUIDELINES.md Tokens (Darker burnt orange & pitch black) */
            --xp-orange: #D94E00;
            --xp-orange-hover: #E85B07;
            --xp-orange-press: #BC4300;
            --xp-black: #000000;
            --xp-sidebar: #070709;
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
            --theme-preview-pitch-page: #000000;
            --theme-preview-pitch-surface: #0C0C0E;
            --theme-preview-obsidian-page: #121416;
            --theme-preview-obsidian-surface: #1B1E22;
            --theme-preview-midnight-page: #07111F;
            --theme-preview-midnight-surface: #0E1E31;
            --theme-preview-light-page: #F3F5F7;
            --theme-preview-light-surface: #FFFFFF;

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
            background: var(--xp-border-light);
            border-radius: 9999px;
            transition: background 0.15s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--xp-orange);
        }

        /* Firefox Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--xp-border-light) transparent;
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
        html[data-theme-mode="light"] body { background-image: none; }

        h1, h2, h3, h4, .card-title, .kpi-value, .sidebar-brand {
            font-family: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Sidebar */
        .sidebar {
            width: 244px;
            min-width: 244px;
            background: var(--xp-sidebar);
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

        /* Responsive Off-Canvas Mobile Drawer */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 105;
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }
        .sidebar-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }
        .sidebar-close-btn {
            display: none;
            background: transparent;
            border: none;
            color: var(--xp-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .sidebar-close-btn:hover {
            color: var(--xp-text);
            background: var(--xp-surface-hover);
        }

        @media (max-width: 1024px) {
            .sidebar-backdrop {
                display: block;
            }
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                height: 100vh;
                z-index: 110;
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: none;
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 0 0 35px rgba(0, 0, 0, 0.9);
            }
            .sidebar-close-btn {
                display: flex !important;
            }
            .main-wrapper {
                width: 100%;
                min-width: 0;
            }
            .topbar {
                padding: 0 16px;
            }
            .content-container {
                padding: 16px 16px 36px 16px;
            }
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
            color: var(--xp-muted);
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
            color: var(--xp-muted);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--xp-border);
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
            border-color: var(--xp-border-light);
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
            background: var(--xp-surface-hover);
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
            background: var(--xp-surface-hover);
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
            background: var(--xp-surface-hover);
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

        /* ==========================================================================
           SKELETAL LOADING SYSTEM (Shimmer Animations & Skeleton Primitives)
           ========================================================================== */
        @keyframes xp-shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .xp-skeleton {
            background: linear-gradient(90deg, var(--xp-surface-hover) 25%, var(--xp-surface-active) 37%, var(--xp-surface-hover) 63%);
            background-size: 400% 100%;
            animation: xp-shimmer 1.4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            border-radius: 4px;
            display: block;
        }

        .xp-skeleton-text {
            height: 12px;
            margin-bottom: 6px;
            border-radius: 3px;
        }

        .xp-skeleton-title {
            height: 22px;
            width: 60%;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .xp-skeleton-badge {
            border-radius: 50%;
        }

        .xp-skeleton-card {
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 8px;
            padding: 16px;
        }

        /* Fast page-transition skeleton progress line */
        #xp-nav-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 2.5px;
            background: var(--xp-orange);
            box-shadow: 0 0 10px rgba(217, 78, 0, 0.8), 0 0 4px var(--xp-orange);
            z-index: 99999;
            transition: width 0.2s ease, opacity 0.2s ease;
            pointer-events: none;
            opacity: 0;
        }

        #xp-nav-progress.active {
            opacity: 1;
            width: 80%;
            transition: width 0.35s cubic-bezier(0.1, 0.9, 0.2, 1);
        }

        #xp-nav-progress.done {
            width: 100%;
            opacity: 0;
            transition: width 0.15s ease, opacity 0.2s ease 0.1s;
        }

        .content-loading-veil {
            opacity: 0.65;
            filter: blur(0.3px);
            pointer-events: none;
            transition: opacity 0.15s ease;
        }

        .xp-skeletal-preloader {
            width: 100%;
            animation: xp-fadeIn 0.2s ease-in-out;
        }
        @keyframes xp-fadeIn {
            from { opacity: 0.65; }
            to { opacity: 1; }
        }

        /* Xpect Dedicated Action & Preactivity Preloader */
        .xp-activity-preloader {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            animation: xp-fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .xp-preloader-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.78);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .xp-preloader-dialog {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 38px 42px;
            background: var(--xp-surface, #0C0C0E);
            border: 1px solid var(--xp-border, #1E1E22);
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.85), 0 0 45px rgba(217, 78, 0, 0.16);
            max-width: 440px;
            width: 90%;
            text-align: center;
            overflow: hidden;
        }
        .xp-preloader-ambient-glow {
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 240px;
            height: 160px;
            background: radial-gradient(circle, rgba(217, 78, 0, 0.4) 0%, rgba(217, 78, 0, 0.08) 55%, transparent 75%);
            pointer-events: none;
            animation: xp-glow-pulse 3s ease-in-out infinite alternate;
        }
        .xp-preloader-stage {
            position: relative;
            width: 96px;
            height: 96px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
        }
        .xp-preloader-ring-outer {
            position: absolute;
            inset: 0;
            border: 2px dashed rgba(217, 78, 0, 0.45);
            border-top-color: var(--xp-orange);
            border-radius: 50%;
            animation: xp-orbit-spin 4s linear infinite;
        }
        .xp-preloader-ring-inner {
            position: absolute;
            inset: 10px;
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            border-bottom-color: var(--xp-orange);
            border-radius: 50%;
            animation: xp-orbit-spin-reverse 2.4s linear infinite;
        }
        .xp-preloader-brand-hub {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 4px 14px rgba(217, 78, 0, 0.45));
            animation: xp-hub-breathe 2.4s ease-in-out infinite alternate;
        }
        .xp-preloader-runner-track {
            width: 100%;
            height: 3px;
            background: var(--xp-border-subtle, #131315);
            border-radius: 9999px;
            overflow: hidden;
            position: relative;
            margin-bottom: 18px;
        }
        .xp-runner-sweep {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 38%;
            background: linear-gradient(90deg, transparent, var(--xp-orange), #FF8533, transparent);
            border-radius: 9999px;
            animation: xp-runner-slide 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        .xp-preloader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .xp-preloader-text {
            font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--xp-text, #FFFFFF);
            margin: 0;
        }
        .xp-preloader-subtext {
            font-size: 11.5px;
            color: var(--xp-muted, #A1A1A6);
            line-height: 1.45;
            margin: 0;
            max-width: 320px;
        }
        .xp-preloader-dismiss-btn {
            margin-top: 18px;
            font-size: 11px;
            font-weight: 600;
            color: var(--xp-away, #6B6B70);
            background: transparent;
            border: 1px solid var(--xp-border, #1E1E22);
            padding: 5px 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .xp-preloader-dismiss-btn:hover {
            color: var(--xp-text, #FFFFFF);
            border-color: var(--xp-border-light, #2A2A30);
            background: var(--xp-surface-hover, #141417);
        }

        @keyframes xp-orbit-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes xp-orbit-spin-reverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }
        @keyframes xp-glow-pulse {
            0% { opacity: 0.6; transform: translateX(-50%) scale(0.95); }
            100% { opacity: 1; transform: translateX(-50%) scale(1.15); }
        }
        @keyframes xp-hub-breathe {
            0% { transform: scale(0.96); }
            100% { transform: scale(1.04); }
        }
        @keyframes xp-runner-slide {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }

        html[data-theme-mode="light"] .xp-activity-preloader .xp-preloader-backdrop {
            background: rgba(243, 245, 247, 0.85);
        }
        html[data-theme-mode="light"] .xp-activity-preloader .xp-preloader-dialog {
            background: #FFFFFF;
            border-color: #D8DEE7;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.12), 0 0 35px rgba(217, 78, 0, 0.12);
        }

        /* Dark Luxury Unified Pagination */
        .xp-pagination-container {
            background: var(--xp-surface, #0C0C0E);
            border: 1px solid var(--xp-border, #1E1E22);
            border-radius: 10px;
            padding: 12px 18px;
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45);
        }
        .xp-pagination-summary {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .xp-pagination-summary-text {
            font-size: 12.5px;
            color: var(--xp-muted, #A1A1A6);
            line-height: 1.4;
        }
        .xp-pagination-number {
            color: var(--xp-text, #FFFFFF);
            font-weight: 700;
        }
        .xp-pagination-total {
            color: var(--xp-orange, #D94E00);
            font-weight: 800;
        }
        .xp-pagination-page-pill {
            font-size: 11px;
            font-weight: 700;
            color: var(--xp-text, #FFFFFF);
            background: var(--xp-surface-hover, #141417);
            border: 1px solid var(--xp-border, #1E1E22);
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.2px;
        }
        .xp-pagination-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .xp-page-numbers-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .xp-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            user-select: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
        }
        .xp-page-prev,
        .xp-page-next {
            padding: 0 16px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .xp-page-btn.active-btn {
            background: var(--xp-surface-hover, #141417);
            border: 1px solid var(--xp-border, #1E1E22);
            color: var(--xp-text, #FFFFFF);
            cursor: pointer;
        }
        .xp-page-btn.active-btn:hover {
            background: rgba(217, 78, 0, 0.14);
            border-color: var(--xp-orange, #D94E00);
            color: var(--xp-orange, #D94E00);
            box-shadow: 0 0 12px rgba(217, 78, 0, 0.25);
        }
        .xp-page-prev.active-btn:hover .xp-prev-icon {
            transform: translateX(-3px);
        }
        .xp-page-next.active-btn:hover .xp-next-icon {
            transform: translateX(3px);
        }
        .xp-prev-icon,
        .xp-next-icon {
            transition: transform 0.2s ease;
        }
        .xp-page-btn.active-btn:active {
            background: var(--xp-orange-press, #BC4300);
            border-color: var(--xp-orange-press, #BC4300);
            color: #000000;
            transform: scale(0.98);
        }
        .xp-page-btn.disabled {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--xp-border-subtle, #131315);
            color: var(--xp-away, #6B6B70);
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }
        .xp-page-num {
            min-width: 38px;
            padding: 0 10px;
            background: var(--xp-surface, #0C0C0E);
            border: 1px solid var(--xp-border, #1E1E22);
            color: var(--xp-text, #FFFFFF);
            font-family: 'Space Grotesk', system-ui, sans-serif;
            font-size: 13px;
            cursor: pointer;
        }
        .xp-page-num:hover {
            background: var(--xp-surface-hover, #141417);
            border-color: var(--xp-border-light, #2A2A30);
            color: var(--xp-orange, #D94E00);
        }
        .xp-page-num.current {
            background: var(--xp-orange, #D94E00) !important;
            border-color: var(--xp-orange, #D94E00) !important;
            color: #000000 !important;
            font-weight: 800;
            cursor: default;
            box-shadow: 0 0 14px rgba(217, 78, 0, 0.45);
        }
        .xp-page-dots {
            min-width: 32px;
            padding: 0 6px;
            background: transparent;
            border: 1px solid transparent;
            color: var(--xp-away, #6B6B70);
            cursor: default;
        }
        @media (max-width: 768px) {
            .xp-pagination-container {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .xp-pagination-summary {
                justify-content: space-between;
                width: 100%;
            }
            .xp-pagination-controls {
                justify-content: space-between;
                width: 100%;
            }
            .xp-page-numbers-group {
                display: none;
            }
            .xp-page-prev,
            .xp-page-next {
                flex: 1;
            }
        }

        .theme-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px;
        }
        .theme-card {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }
        .theme-manager-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        .theme-header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .theme-manager-message {
            min-height: 20px;
            margin-bottom: 12px;
            color: var(--xp-muted);
        }
        .theme-option-actions,
        .theme-editor-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            width: 100%;
        }
        .theme-editor {
            margin-top: 16px;
        }
        .theme-editor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 12px;
            margin: 14px 0;
        }
        .theme-color-field {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 48px;
            padding: 7px 10px;
            color: var(--xp-text);
            background: var(--xp-surface-hover);
            border: 1px solid var(--xp-border);
            border-radius: 6px;
        }
        .theme-color-field input[type="color"] {
            width: 40px;
            height: 34px;
            padding: 2px;
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 5px;
            cursor: pointer;
        }
        .theme-contrast-warning {
            margin: 8px 0 12px;
            color: var(--xp-orange);
        }
        .theme-description {
            color: var(--xp-muted);
            font-size: 12px;
        }
        @media (max-width: 640px) {
            .theme-manager-header { flex-direction: column; }
            .theme-header-actions { width: 100%; }
        }
        .theme-option {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            padding: 14px;
            text-align: left;
            color: var(--xp-text);
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 9px;
            cursor: pointer;
            transition: border-color 0.15s ease, transform 0.15s ease, background 0.15s ease;
        }
        .theme-option:hover {
            border-color: var(--xp-border-light);
            transform: translateY(-2px);
        }
        .theme-option[aria-pressed="true"] {
            border-color: var(--xp-orange);
            box-shadow: 0 0 0 1px var(--xp-orange);
        }
        .theme-swatch {
            --preview-page: var(--theme-preview-pitch-page);
            --preview-surface: var(--theme-preview-pitch-surface);
            position: relative;
            display: flex;
            align-items: flex-end;
            gap: 7px;
            height: 100px;
            padding: 12px;
            overflow: hidden;
            background: var(--preview-page);
            border: 1px solid var(--xp-border);
            border-radius: 6px;
        }
        .theme-swatch[data-preview="obsidian"] {
            --preview-page: var(--theme-preview-obsidian-page);
            --preview-surface: var(--theme-preview-obsidian-surface);
        }
        .theme-swatch[data-preview="midnight"] {
            --preview-page: var(--theme-preview-midnight-page);
            --preview-surface: var(--theme-preview-midnight-surface);
        }
        .theme-swatch[data-preview="light"] {
            --preview-page: var(--theme-preview-light-page);
            --preview-surface: var(--theme-preview-light-surface);
        }
        .theme-swatch-panel {
            width: 58%;
            height: 70%;
            background: var(--preview-surface);
            border: 1px solid var(--xp-border);
            border-radius: 4px;
        }
        .theme-swatch-accent {
            width: 22%;
            height: 35%;
            background: var(--preview-accent, var(--xp-orange));
            border-radius: 4px;
        }
        .theme-option-copy {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .theme-option-title { font-size: 14px; font-weight: 700; }
        .theme-option-description { font-size: 12px; color: var(--xp-muted); }
        .theme-option-status {
            min-height: 16px;
            color: var(--xp-orange);
            font-size: 11px;
            font-weight: 700;
        }
        .theme-storage-note {
            margin-top: 16px;
            padding: 12px 14px;
            color: var(--xp-muted);
            background: var(--xp-surface-hover);
            border: 1px solid var(--xp-border);
            border-radius: 7px;
        }

        html[data-theme-mode="light"] [style*="background:#0c0c0e" i],
        html[data-theme-mode="light"] [style*="background: #0c0c0e" i],
        html[data-theme-mode="light"] [style*="background-color:#0c0c0e" i],
        html[data-theme-mode="light"] [style*="background-color: #0c0c0e" i],
        html[data-theme-mode="light"] [style*="background:#09090c" i],
        html[data-theme-mode="light"] [style*="background: #09090c" i],
        html[data-theme-mode="light"] [style*="background:#09090b" i],
        html[data-theme-mode="light"] [style*="background: #09090b" i],
        html[data-theme-mode="light"] [style*="background:#070709" i],
        html[data-theme-mode="light"] [style*="background: #070709" i],
        html[data-theme-mode="light"] [style*="background:#08080a" i],
        html[data-theme-mode="light"] [style*="background: #08080a" i],
        html[data-theme-mode="light"] [style*="background:#060608" i],
        html[data-theme-mode="light"] [style*="background: #060608" i],
        html[data-theme-mode="light"] [style*="background:#101012" i],
        html[data-theme-mode="light"] [style*="background: #101012" i],
        html[data-theme-mode="light"] [style*="background:#141418" i],
        html[data-theme-mode="light"] [style*="background: #141418" i],
        html[data-theme-mode="light"] [style*="background:#18181c" i],
        html[data-theme-mode="light"] [style*="background: #18181c" i],
        html[data-theme-mode="light"] [style*="background:#16161a" i],
        html[data-theme-mode="light"] [style*="background: #16161a" i],
        html[data-theme-mode="light"] [style*="background:#16161c" i],
        html[data-theme-mode="light"] [style*="background: #16161c" i],
        html[data-theme-mode="light"] [style*="background:#000000" i],
        html[data-theme-mode="light"] [style*="background: #000000" i] {
            background: var(--xp-surface) !important;
        }
        html[data-theme-mode="light"] [style*="color:#ffffff" i],
        html[data-theme-mode="light"] [style*="color: #ffffff" i],
        html[data-theme-mode="light"] [style*="color:#fff" i],
        html[data-theme-mode="light"] [style*="color: #fff" i] {
            color: var(--xp-text) !important;
        }
    </style>
</head>
<body>
    <!-- Top Progress Bar for Skeletal Page Transitions -->
    <div id="xp-nav-progress"></div>

    <!-- Dedicated Xpect Preloader for Preactivities and Action-Based Operations -->
    <x-activity-preloader />

    <!-- Off-Canvas Sidebar Backdrop (Mobile / Small Screens) -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

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
            <div style="display:flex;align-items:center;gap:6px;">
                <span class="brand-badge">v1.2</span>
                <button type="button" class="sidebar-close-btn" id="sidebar-close-btn" aria-label="Close sidebar" title="Close sidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
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
                <a href="{{ route('themes') }}" class="sidebar-link {{ request()->routeIs('themes') ? 'active' : '' }}">
                    <div class="sidebar-link-content">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 2a4 4 0 0 0 0 8h1a2 2 0 0 1 0 4h-1a4 4 0 0 0 0 8"></path>
                        </svg>
                        <span>Themes</span>
                    </div>
                    <span class="nav-tag">UI</span>
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
                <button class="topbar-btn" id="sidebar-toggle-btn" title="Toggle Sidebar" aria-label="Toggle Sidebar">
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
                    $activeDateStr = $rawDate ?: match ($rawPreset) {
                        'yesterday' => $parsedNow->subDay()->format('Y-m-d'),
                        'tomorrow' => $parsedNow->addDay()->format('Y-m-d'),
                        'weekend' => in_array((int) $parsedNow->format('w'), [0, 6], true)
                            ? $parsedNow->format('Y-m-d')
                            : $parsedNow->next(\Carbon\CarbonImmutable::SATURDAY)->format('Y-m-d'),
                        default => $parsedNow->format('Y-m-d'),
                    };

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

                    $btnDisplayLabel = match ($rawPreset) {
                        'next_matchday' => 'Next Round',
                        'last_matchday' => 'Last Round',
                        default => $activeDateStr === $parsedNow->format('Y-m-d')
                            ? 'Today, ' . $activeCarbon->format('d M')
                            : $activeCarbon->format('D, d M Y'),
                    };
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

    <!-- Progressive Skeletal Loading Transitions -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Responsive Mobile Sidebar Drawer
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
            const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('open');
                if (sidebarBackdrop) sidebarBackdrop.classList.add('active');
                if (window.innerWidth <= 1024) document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('open');
                if (sidebarBackdrop) sidebarBackdrop.classList.remove('active');
                document.body.style.overflow = '';
            }

            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (sidebar && sidebar.classList.contains('open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            if (sidebarCloseBtn) {
                sidebarCloseBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeSidebar();
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function () {
                    closeSidebar();
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
                    closeSidebar();
                }
            });

            const progressBar = document.getElementById('xp-nav-progress');
            const mainContent = document.querySelector('.content-container');

            if (progressBar) {
                progressBar.classList.add('done');
                setTimeout(() => {
                    progressBar.classList.remove('active', 'done');
                    progressBar.style.width = '0%';
                }, 300);
            }

            // Global Activity Preloader Controller for Background Tasks, AI Synthesis & Action Triggers
            window.XpectPreloader = {
                show: function (title, subtext) {
                    const el = document.getElementById('xp-activity-preloader');
                    if (!el) return;
                    if (title) {
                        const titleEl = document.getElementById('xp-preloader-title');
                        if (titleEl) titleEl.textContent = title;
                    }
                    if (subtext) {
                        const subtextEl = document.getElementById('xp-preloader-subtext');
                        if (subtextEl) subtextEl.textContent = subtext;
                    }
                    el.style.display = 'flex';
                },
                hide: function () {
                    const el = document.getElementById('xp-activity-preloader');
                    if (el) el.style.display = 'none';
                },
                setText: function (title, subtext) {
                    if (title) {
                        const titleEl = document.getElementById('xp-preloader-title');
                        if (titleEl) titleEl.textContent = title;
                    }
                    if (subtext) {
                        const subtextEl = document.getElementById('xp-preloader-subtext');
                        if (subtextEl) subtextEl.textContent = subtext;
                    }
                },
                isActive: function () {
                    const el = document.getElementById('xp-activity-preloader');
                    return el ? el.style.display !== 'none' : false;
                }
            };

            const preloaderDismissBtn = document.getElementById('xp-preloader-dismiss-btn');
            if (preloaderDismissBtn) {
                preloaderDismissBtn.addEventListener('click', function () {
                    window.XpectPreloader.hide();
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && window.XpectPreloader && window.XpectPreloader.isActive()) {
                    window.XpectPreloader.hide();
                }
            });

            // Intercept internal navigation clicks for instant skeletal feedback or preactivity actions
            document.addEventListener('click', function (e) {
                const preloaderTrigger = e.target.closest('[data-preloader="true"]');
                if (preloaderTrigger) {
                    const pTitle = preloaderTrigger.getAttribute('data-preloader-title') || 'PROCESSING SYSTEM REQUEST';
                    const pSubtext = preloaderTrigger.getAttribute('data-preloader-subtext') || 'Executing background operation...';
                    if (window.XpectPreloader) {
                        window.XpectPreloader.show(pTitle, pSubtext);
                    }
                    return;
                }

                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;
                if (href.startsWith(window.location.origin) || href.startsWith('/')) {
                    if (progressBar) progressBar.classList.add('active');
                    const skeletal = document.querySelector('.xp-skeletal-preloader');
                    const realContent = document.querySelector('.xp-page-content');
                    if (skeletal && realContent) {
                        skeletal.style.display = 'block';
                        realContent.style.display = 'none';
                        window.scrollTo({ top: 0, behavior: 'instant' });
                    } else if (mainContent) {
                        mainContent.classList.add('content-loading-veil');
                    }
                }
            });

            // Intercept filter form submissions (dates, seasons, search) and action preactivities
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form && !form.target && !e.defaultPrevented) {
                    const method = (form.getAttribute('method') || 'GET').toUpperCase();
                    const isActionForm = method !== 'GET' || form.hasAttribute('data-preloader');

                    if (isActionForm) {
                        const action = (form.getAttribute('action') || '').toLowerCase();
                        let title = 'PROCESSING SYSTEM REQUEST';
                        let subtext = 'Executing server operation with strict data integrity guards...';

                        if (form.getAttribute('data-preloader-title')) {
                            title = form.getAttribute('data-preloader-title');
                            subtext = form.getAttribute('data-preloader-subtext') || subtext;
                        } else if (action.includes('predict') || action.includes('calculate')) {
                            title = 'CALIBRATING POISSON PROBABILITIES';
                            subtext = 'Computing expected goals, Dixon-Coles parameters & zero-leakage features...';
                        } else if (action.includes('ai') || action.includes('preview')) {
                            title = 'SYNTHESIZING AI REASONING PREVIEW';
                            subtext = 'Generating match narrative from structured statistical models...';
                        } else if (action.includes('audit') || action.includes('health') || action.includes('integrity')) {
                            title = 'RUNNING SYSTEM INTEGRITY AUDIT';
                            subtext = 'Validating database foreign keys, API logs & leakage invariants...';
                        } else if (action.includes('sync') || action.includes('fetch') || action.includes('fixture')) {
                            title = 'SYNCHRONIZING FOOTBALL DATA';
                            subtext = 'Pulling latest verified results and updating standings...';
                        } else if (action.includes('simulate') || action.includes('scenario')) {
                            title = 'RUNNING AI PREDICTION SIMULATION';
                            subtext = 'Simulating match outcomes across varied parameter weights...';
                        } else if (action.includes('motm') || action.includes('history')) {
                            title = 'EVALUATING MAN OF THE MATCH';
                            subtext = 'Aggregating player performance ratings and event timeline...';
                        } else if (action.includes('key') || action.includes('credential') || action.includes('settings')) {
                            title = 'UPDATING SYSTEM CREDENTIALS';
                            subtext = 'Encrypting and securely testing provider API connections...';
                        }

                        if (window.XpectPreloader) {
                            window.XpectPreloader.show(title, subtext);
                        }
                    } else {
                        if (progressBar) progressBar.classList.add('active');
                        const skeletal = document.querySelector('.xp-skeletal-preloader');
                        const realContent = document.querySelector('.xp-page-content');
                        if (skeletal && realContent) {
                            skeletal.style.display = 'block';
                            realContent.style.display = 'none';
                            window.scrollTo({ top: 0, behavior: 'instant' });
                        } else if (mainContent) {
                            mainContent.classList.add('content-loading-veil');
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
