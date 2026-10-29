<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') — Xpect Football Engine</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/mark-orange.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --xp-orange: #D94E00;
            --xp-orange-hover: #E85B07;
            --xp-orange-press: #BC4300;
            --xp-black: #000000;
            --xp-surface: #0C0C0E;
            --xp-surface-elevated: #141416;
            --xp-border: #1E1E22;
            --xp-border-subtle: #18181B;
            --xp-text: #FFFFFF;
            --xp-muted: #A1A1A6;
            --xp-away: #6B6B70;
            --xp-ok: #3DDC84;
            --xp-error: #FF4D4D;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--xp-black);
            color: var(--xp-text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
            background-image: radial-gradient(circle at 50% 20%, rgba(217, 78, 0, 0.08) 0%, rgba(0, 0, 0, 0.95) 70%);
        }

        .error-container {
            width: 100%;
            max-width: 580px;
            background: var(--xp-surface);
            border: 1px solid var(--xp-border);
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.03);
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .brand-header {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 28px;
        }

        .brand-header svg {
            width: 26px;
            height: 30px;
        }

        .brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: #FFFFFF;
        }

        .brand-pill {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: var(--xp-orange);
            background: rgba(217, 78, 0, 0.12);
            border: 1px solid rgba(217, 78, 0, 0.3);
            padding: 2px 7px;
            border-radius: 4px;
        }

        .error-code-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Space Grotesk', monospace;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--xp-orange);
            background: rgba(217, 78, 0, 0.1);
            border: 1px solid rgba(217, 78, 0, 0.25);
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 16px;
        }

        .error-status-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 72px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.04em;
            color: #FFFFFF;
            margin-bottom: 12px;
        }

        .error-status-number span {
            color: var(--xp-orange);
        }

        .error-headline {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #FFFFFF;
            margin-bottom: 12px;
        }

        .error-description {
            font-size: 14px;
            line-height: 1.6;
            color: var(--xp-muted);
            margin-bottom: 28px;
            max-width: 460px;
            margin-left: auto;
            margin-right: auto;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-bottom: 24px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--xp-orange);
            color: #000000 !important;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 8px;
            min-height: 44px;
            transition: all 0.15s ease;
            box-shadow: 0 4px 14px rgba(217, 78, 0, 0.25);
            cursor: pointer;
            border: none;
        }

        .btn-primary:hover {
            background: var(--xp-orange-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(217, 78, 0, 0.35);
        }

        .btn-primary:active {
            background: var(--xp-orange-press);
            transform: translateY(0);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--xp-border);
            color: var(--xp-text);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            min-height: 44px;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .error-meta-box {
            background: var(--xp-surface-elevated);
            border: 1px solid var(--xp-border-subtle);
            border-radius: 8px;
            padding: 12px 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: #8E8E93;
            text-align: left;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .error-meta-box span.key {
            color: var(--xp-muted);
        }

        .error-meta-box span.val {
            color: #FFFFFF;
        }

        .error-footer {
            font-size: 11px;
            color: #63636C;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-top: 1px solid var(--xp-border-subtle);
            padding-top: 18px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--xp-ok);
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Brand Header -->
        <a href="{{ url('/') }}" class="brand-header">
            <svg viewBox="0 0 100 116" fill="none">
                <path d="M50 3 L95.5 29.3 L95.5 86.7 L50 113 L4.5 86.7 L4.5 29.3 Z" fill="#D94E00" stroke="#D94E00" stroke-width="4" stroke-linejoin="round"/>
                <rect x="21" y="48" width="9.5" height="40" rx="2.5" fill="#612403"/>
                <rect x="36.5" y="27" width="9.5" height="61" rx="2.5" fill="#000000"/>
                <rect x="52" y="42" width="9.5" height="46" rx="2.5" fill="#612403"/>
                <rect x="67.5" y="66" width="9.5" height="22" rx="2.5" fill="#612403"/>
                <line x1="17" y1="90" x2="81" y2="90" stroke="#000000" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
            <span class="brand-title">xpect</span>
            <span class="brand-pill">v1.2</span>
        </a>

        <!-- Error Badge -->
        <div>
            <span class="error-code-badge">
                <span class="status-dot" style="background: @yield('badge_color', 'var(--xp-orange)')"></span>
                @yield('badge_text', 'HTTP EXCEPTION')
            </span>
        </div>

        <!-- Big Status Code -->
        <div class="error-status-number">
            @yield('code', '404')
        </div>

        <!-- Headline & Description -->
        <h1 class="error-headline">@yield('headline', 'Page Not Found')</h1>
        <p class="error-description">
            @yield('message', 'The requested resource or fixture could not be located in the local prediction database.')
        </p>

        <!-- Context Meta (Optional) -->
        @hasSection('meta')
            <div class="error-meta-box">
                @yield('meta')
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Return to Dashboard</span>
            </a>
            <a href="{{ route('today') }}" class="btn-secondary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                </svg>
                <span>Today's Fixtures</span>
            </a>
            <a href="{{ route('docs') }}" class="btn-secondary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span>System Docs</span>
            </a>
        </div>

        <!-- Footer -->
        <div class="error-footer">
            <span>Model v1.2.1-h2h</span>
            <span>•</span>
            <span>Local SQLite Engine</span>
            <span>•</span>
            <span>Zero Data Leakage Invariant</span>
        </div>
    </div>
</body>
</html>
