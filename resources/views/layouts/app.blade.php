<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} | Xpect Football Analytics</title>
    <style>
        :root {
            --bg-body: #0b0f19;
            --bg-card: #131b2e;
            --bg-card-hover: #1a253f;
            --bg-card-header: #101726;
            --border: #23314d;
            --border-light: #324468;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --accent: #38bdf8;
            --accent-hover: #0ea5e9;
            --green: #10b981;
            --green-dim: rgba(16, 185, 129, 0.15);
            --red: #f43f5e;
            --red-dim: rgba(244, 63, 94, 0.15);
            --yellow: #f59e0b;
            --yellow-dim: rgba(245, 158, 11, 0.15);
            --purple: #a855f7;
            --purple-dim: rgba(168, 85, 247, 0.15);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation */
        .navbar {
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        .brand-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.45rem;
            border-radius: 9999px;
            background: rgba(56, 189, 248, 0.15);
            color: var(--accent);
            border: 1px solid rgba(56, 189, 248, 0.3);
            text-transform: uppercase;
            font-weight: 600;
        }
        .nav-links {
            display: flex;
            gap: 0.5rem;
            list-style: none;
        }
        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .nav-link:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
        }
        .nav-link.active {
            color: var(--accent);
            background: rgba(56, 189, 248, 0.1);
        }

        /* Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1.5rem;
            width: 100%;
            flex: 1;
        }

        /* Alerts & Flash Messages */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
        }
        .alert-success { background: var(--green-dim); color: var(--green); border: 1px solid var(--green); }
        .alert-error { background: var(--red-dim); color: var(--red); border: 1px solid var(--red); }
        .alert-info { background: rgba(56, 189, 248, 0.15); color: var(--accent); border: 1px solid var(--accent); }

        /* Typography & Layout Utilities */
        h1, h2, h3, h4 { color: var(--text-main); font-weight: 600; letter-spacing: -0.01em; }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        .page-title { font-size: 1.5rem; }
        .page-subtitle { color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem; }

        /* Grid */
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }

        /* Stat Card */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
        }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-dim); font-weight: 600; letter-spacing: 0.05em; }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--text-main); margin-top: 0.25rem; }
        .stat-meta { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.35rem; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-primary { background: var(--accent); color: #000; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-secondary { background: var(--bg-card-hover); color: var(--text-main); border-color: var(--border); }
        .btn-secondary:hover { background: #263554; border-color: var(--border-light); }
        .btn-sm { padding: 0.25rem 0.6rem; font-size: 0.75rem; border-radius: 4px; }

        /* Tables */
        .table-container {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th {
            background: var(--bg-card-header);
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: var(--green-dim); color: var(--green); }
        .badge-danger { background: var(--red-dim); color: var(--red); }
        .badge-warning { background: var(--yellow-dim); color: var(--yellow); }
        .badge-info { background: rgba(56, 189, 248, 0.15); color: var(--accent); }

        /* Forms */
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem; }
        .form-control {
            width: 100%;
            background: #0f1626;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.6rem 0.85rem;
            color: var(--text-main);
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.15s;
        }
        .form-control:focus { border-color: var(--accent); }
        .form-text { font-size: 0.75rem; color: var(--text-dim); margin-top: 0.35rem; }

        /* Footer */
        .footer {
            border-top: 1px solid var(--border);
            padding: 1rem 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-dim);
            background: var(--bg-card);
        }
    </style>
</head>
<body>
    <header class="navbar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span>⚽ XPECT</span>
            <span class="brand-badge">Local v1.0</span>
        </a>
        <nav>
            <ul class="nav-links">
                <li><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">Fixtures & Predictions</a></li>
                <li><a href="{{ route('track-record') }}" class="nav-link {{ request()->routeIs('track-record*') ? 'active' : '' }}">Track Record</a></li>
                <li><a href="{{ route('dev.api.index') }}" class="nav-link {{ request()->routeIs('dev.api*') ? 'active' : '' }}">API Inspector</a></li>
                <li><a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }}">Settings & Keys</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
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

        {{ $slot }}
    </main>

    <footer class="footer">
        Xpect Local Football Analytics &copy; {{ date('Y') }} &mdash; Poisson Probability Model &bull; Leakage-Safe Strict Data Flow &bull; Anthropic AI Grounded Previews
    </footer>
</body>
</html>

