<x-layouts.app title="API Inspector">
    <style>
        .inspector-tabs {
            display: inline-flex;
            gap: 4px;
            background: var(--xp-surface);
            border: 1px solid var(--border);
            padding: 4px;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .inspector-tab {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .inspector-tab:hover {
            color: var(--text-primary);
        }
        .inspector-tab.active {
            background: #242429;
            color: var(--text-primary);
        }

        .badge-get { background: #0284c7; color: #fff; }
        .badge-post { background: #16a34a; color: #fff; }
        .badge-2xx { background: rgba(34, 197, 94, 0.15); color: var(--green); border: 1px solid rgba(34, 197, 94, 0.3); }
        .badge-4xx { background: rgba(244, 63, 94, 0.15); color: var(--red); border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-5xx { background: rgba(244, 63, 94, 0.35); color: #fff; }
        .badge-cache { background: rgba(168, 85, 247, 0.15); color: var(--purple); border: 1px solid rgba(168, 85, 247, 0.3); }

        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }
        .modal-content {
            background: var(--xp-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            width: 900px;
            max-width: 100%;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .modal-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #111115;
        }
        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        pre.code-block {
            background: var(--xp-black);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 12px;
            overflow-x: auto;
            color: #d4d4d8;
            font-size: 11px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
    </style>

    <!-- Page Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:18px;border-bottom:1px solid var(--border);padding-bottom:14px;">
        <div>
            <h1 style="font-size:18px;font-weight:700;color:var(--text-primary);letter-spacing:-0.02em;">API Inspector</h1>
            <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">Real-time inspection of outbound HTTP requests, rate quotas, endpoint catalog, and raw payloads</p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="badge badge-info" style="text-transform:none;">APP_ENV: {{ app()->environment() }}</span>
            <span class="badge badge-success">READ-ONLY AUDIT</span>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;color:var(--text-muted);font-size:12px;background:var(--xp-surface);border:1px solid var(--border);padding:4px 8px;border-radius:6px;">
                <input type="checkbox" id="auto-refresh-toggle" checked style="cursor:pointer;">
                <span>Live Refresh (5s)</span>
            </label>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="inspector-tabs">
        <div class="inspector-tab active" onclick="switchTab('call-log')">Call Log</div>
        <div class="inspector-tab" onclick="switchTab('quota')">Quota & Limits</div>
        <div class="inspector-tab" onclick="switchTab('catalog')">Endpoint Catalog</div>
    </div>

    <!-- TAB 1: CALL LOG -->
    <div id="tab-call-log">
        <div class="card" style="padding:0;overflow:hidden;">
            <!-- Filter Bar -->
            <div style="padding:14px 18px;background:#16161c;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <select id="filter-source" class="select-pill" onchange="loadCalls()">
                        <option value="">All Sources</option>
                        <option value="football_data">football_data</option>
                        <option value="api_football">api_football</option>
                        <option value="understat">understat</option>
                        <option value="csv">csv</option>
                        <option value="anthropic">anthropic</option>
                    </select>

                    <select id="filter-status" class="select-pill" onchange="loadCalls()">
                        <option value="">All Statuses</option>
                        <option value="success">2xx Success</option>
                        <option value="error">Errors & Failures</option>
                        <option value="cache">Served From Cache</option>
                    </select>

                    <input type="text" id="filter-command" class="form-control" placeholder="Triggered by..." onkeyup="debounceLoadCalls()" style="padding:5px 9px;font-size:12px;width:160px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="loadCalls()">Refresh</button>
                </div>
                <div style="color:var(--text-muted);font-size:11px;" id="calls-count-label">Loading calls...</div>
            </div>

            <!-- Table -->
            <div class="table-container" style="border:none;border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Time (UTC)</th>
                            <th>Source</th>
                            <th>Method</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Cache</th>
                            <th>Triggered By</th>
                        </tr>
                    </thead>
                    <tbody id="calls-table-body">
                        <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">Loading calls...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: QUOTA & LIMITS -->
    <div id="tab-quota" style="display:none;">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <div>
                    <h3 style="font-size:15px;font-weight:600;color:var(--text-primary);">Provider Quota Status (QuotaGuard)</h3>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">Enforces daily call boundaries, per-minute throttles, and safety reserve limits</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="loadQuota()">Refresh Quota</button>
            </div>
            <div id="quota-container">
                Loading quota...
            </div>
        </div>
    </div>

    <!-- TAB 3: ENDPOINT CATALOG -->
    <div id="tab-catalog" style="display:none;">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <div>
                    <h3 style="font-size:15px;font-weight:600;color:var(--text-primary);">Allowed Outbound Catalog (config/api_catalog.php)</h3>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">Strictly whitelist-governed endpoints verified against saved fixtures</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="loadCatalog()">Refresh Catalog</button>
            </div>
            <div id="catalog-container">
                Loading catalog...
            </div>
        </div>
    </div>

    <!-- CALL DETAIL MODAL -->
    <div class="modal-overlay" id="detail-modal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 id="modal-title" style="font-size:14px;font-weight:700;color:var(--text-primary);">API Call Detail</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body" id="modal-body">
                Loading...
            </div>
        </div>
    </div>

    <script>
    let currentTab = 'call-log';
    let debounceTimeout = null;
    let refreshInterval = null;

    function switchTab(tabId) {
        currentTab = tabId;
        document.querySelectorAll('.inspector-tab').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');

        if (tabId === 'call-log') {
            document.querySelectorAll('.inspector-tab')[0].classList.add('active');
            document.getElementById('tab-call-log').style.display = 'block';
            loadCalls();
        } else if (tabId === 'quota') {
            document.querySelectorAll('.inspector-tab')[1].classList.add('active');
            document.getElementById('tab-quota').style.display = 'block';
            loadQuota();
        } else if (tabId === 'catalog') {
            document.querySelectorAll('.inspector-tab')[2].classList.add('active');
            document.getElementById('tab-catalog').style.display = 'block';
            loadCatalog();
        }
    }

    function debounceLoadCalls() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(loadCalls, 300);
    }

    async function loadCalls() {
        const source = document.getElementById('filter-source').value;
        const status = document.getElementById('filter-status').value;
        const command = document.getElementById('filter-command').value;

        const url = new URL('/dev/api/calls', window.location.origin);
        if (source) url.searchParams.set('source', source);
        if (status) url.searchParams.set('status', status);
        if (command) url.searchParams.set('command', command);

        try {
            const res = await fetch(url);
            const data = await res.json();
            renderCallsTable(data.data || []);
            document.getElementById('calls-count-label').innerText = `Showing ${data.data?.length || 0} of ${data.total || 0} total calls`;
        } catch (err) {
            console.error(err);
        }
    }

    function renderCallsTable(calls) {
        const tbody = document.getElementById('calls-table-body');
        if (!calls.length) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">No API calls logged matching these filters.</td></tr>';
            return;
        }

        tbody.innerHTML = calls.map(c => {
            const statusBadge = c.response_status 
                ? (c.response_status < 300 && !c.error ? `<span class="badge badge-2xx">${c.response_status}</span>` : `<span class="badge badge-4xx">${c.response_status}</span>`)
                : `<span class="badge badge-5xx">ERR</span>`;
            const methodBadge = `<span class="badge ${c.method === 'POST' ? 'badge-post' : 'badge-get'}">${c.method}</span>`;
            const cacheBadge = c.served_from_cache 
                ? `<span class="badge badge-cache">HIT (#${c.cached_from_call_id})</span>`
                : `<span style="color: var(--text-muted); font-size: 11px;">LIVE</span>`;
            const timeStr = c.called_at ? c.called_at.replace('T', ' ').substring(0, 19) : '-';

            return `
                <tr onclick="openCallDetail(${c.id})" style="cursor: pointer;">
                    <td style="color: var(--text-muted); white-space: nowrap; font-size: 12px;">${timeStr}</td>
                    <td><strong>${c.source}</strong></td>
                    <td>${methodBadge}</td>
                    <td style="max-width: 380px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px;" title="${c.url}">${c.url}</td>
                    <td>${statusBadge}</td>
                    <td style="font-size: 12px; color: var(--text-secondary);">${c.duration_ms}ms</td>
                    <td>${cacheBadge}</td>
                    <td style="color: var(--accent); font-size: 12px;">${c.triggered_by}</td>
                </tr>
            `;
        }).join('');
    }

    async function openCallDetail(id) {
        const modal = document.getElementById('detail-modal');
        const modalBody = document.getElementById('modal-body');
        modal.style.display = 'flex';
        modalBody.innerHTML = '<div style="color: var(--text-muted); text-align: center; padding: 30px;">Loading call detail...</div>';

        try {
            const res = await fetch(`/dev/api/calls/${id}`);
            const data = await res.json();
            const call = data.call;
            const prov = data.provenance;

            let prettyBody = call.response_body;
            try {
                prettyBody = JSON.stringify(JSON.parse(call.response_body), null, 2);
            } catch (_) {}

            modalBody.innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div class="card" style="background:var(--xp-black);padding:12px;">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Endpoint & Method</div>
                        <div style="font-size:13px;color:var(--text-primary);font-weight:600;word-break:break-all;">${call.method} ${call.url}</div>
                    </div>
                    <div class="card" style="background:var(--xp-black);padding:12px;">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Status & Duration</div>
                        <div style="font-size:13px;font-weight:700;color:${call.response_status < 300 && !call.error ? 'var(--green)' : 'var(--red)'};">
                            ${call.response_status || 'ERROR'} (${call.duration_ms}ms) ${call.served_from_cache ? '[FROM CACHE]' : '[LIVE OUTBOUND]'}
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:14px;background:var(--xp-black);border:1px solid var(--border);border-radius:6px;padding:12px;">
                    <div style="color:var(--text-muted);font-size:11px;text-transform:uppercase;font-weight:600;margin-bottom:6px;">Provenance Impact (Derived Entities)</div>
                    <div style="display:flex;gap:8px;font-size:11px;">
                        <span class="badge badge-info">Matches: ${prov.matches}</span>
                        <span class="badge badge-info">Standings: ${prov.standings_snapshots}</span>
                        <span class="badge badge-info">Ratings: ${prov.team_ratings}</span>
                        <span class="badge badge-info">Previews: ${prov.previews}</span>
                    </div>
                </div>

                ${call.error ? `<div style="background:rgba(244,63,94,0.15);border:1px solid var(--red);padding:10px 12px;border-radius:6px;margin-bottom:14px;color:var(--red);font-weight:600;font-size:12px;">Error: ${call.error}</div>` : ''}

                <div style="margin-bottom:14px;">
                    <div style="color:var(--text-muted);font-size:11px;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Sanitized Request Headers (Secrets Redacted)</div>
                    <pre class="code-block">${JSON.stringify(call.request_headers, null, 2)}</pre>
                </div>

                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <div style="color:var(--text-muted);font-size:11px;text-transform:uppercase;font-weight:600;">Raw Response Payload</div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="saveAsFixture(${call.id}, '${call.source}')">Save as Test Fixture</button>
                    </div>
                    <pre class="code-block" style="max-height:300px;">${prettyBody || '(Empty or Cache Hit Marker)'}</pre>
                </div>
            `;
        } catch (err) {
            modalBody.innerHTML = `<div style="color:var(--red);padding:20px;text-align:center;">Failed to load call detail.</div>`;
        }
    }

    async function saveAsFixture(apiCallId, source) {
        const filename = prompt('Enter fixture filename (saved in tests/Fixtures/):', `${source}_sample.json`);
        if (!filename) return;

        try {
            const res = await fetch('/dev/api/fixture', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ api_call_id: apiCallId, filename }),
            });
            const data = await res.json();
            alert(`Fixture successfully saved to: ${data.path}`);
        } catch (err) {
            alert('Failed to save fixture.');
        }
    }

    function closeModal(e) {
        document.getElementById('detail-modal').style.display = 'none';
    }

    async function loadQuota() {
        const container = document.getElementById('quota-container');
        try {
            const res = await fetch('/dev/api/quota');
            const data = await res.json();

            container.innerHTML = Object.entries(data).map(([src, q]) => `
                <div style="margin-bottom:20px;background:#16161c;border:1px solid var(--border);border-radius:8px;padding:16px;">
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                        <span>${src}</span>
                        <span class="badge ${q.daily_limit && q.used_today >= q.daily_limit ? 'badge-danger' : 'badge-success'}">${q.daily_limit ? Math.max(0, q.daily_limit - q.used_today) + ' remaining' : 'Unlimited'}</span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="card" style="background:var(--xp-black);padding:14px;">
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Daily Quota (Used Today / Limit)</div>
                            <div style="font-size:18px;font-weight:700;color:var(--text-primary);">${q.used_today} / ${q.daily_limit ? q.daily_limit : 'Unlimited'} <span style="font-size:12px;color:var(--text-muted);font-weight:normal;">(Reserve: ${q.reserve})</span></div>
                        </div>
                        <div class="card" style="background:var(--xp-black);padding:14px;">
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Per-Minute Throttle</div>
                            <div style="font-size:18px;font-weight:700;color:var(--text-primary);">${q.per_minute_used} / ${q.per_minute_limit ? q.per_minute_limit : 'N/A'}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (err) {
            container.innerHTML = `<div style="color:var(--red);padding:20px;">Failed to load quota status.</div>`;
        }
    }

    async function loadCatalog() {
        const container = document.getElementById('catalog-container');
        try {
            const res = await fetch('/dev/api/catalog');
            const data = await res.json();

            container.innerHTML = Object.entries(data).map(([srcKey, src]) => `
                <div style="margin-bottom:24px;background:#16161c;border:1px solid var(--border);border-radius:8px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                        <div>
                            <span style="font-size:14px;font-weight:700;color:var(--text-primary);">${src.name}</span>
                            <span style="color:var(--text-muted);font-size:11px;margin-left:6px;">(${srcKey})</span>
                        </div>
                        <span style="color:var(--text-dim);font-size:11px;font-family:monospace;">${src.base_url || 'N/A'}</span>
                    </div>
                    <div class="table-container" style="border:none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>Path</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Last Called</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${src.endpoints.map(ep => `
                                    <tr>
                                        <td><span class="badge ${ep.method === 'POST' ? 'badge-post' : 'badge-get'}">${ep.method}</span></td>
                                        <td style="font-family:monospace;font-size:12px;"><strong>${ep.path}</strong></td>
                                        <td style="color:var(--text-muted);font-size:12px;">${ep.purpose}</td>
                                        <td><span class="badge ${ep.status === 'VERIFIED' ? 'badge-success' : 'badge-warning'}">${ep.status}</span></td>
                                        <td style="font-size:11px;color:var(--text-secondary);">${ep.last_called ? ep.last_called.replace('T', ' ').substring(0, 19) + ` (${ep.last_status})` : 'Never'}</td>
                                        <td>
                                            ${ep.method === 'GET' && !ep.path.includes('{') ? `<button type="button" class="btn btn-secondary btn-sm" onclick="tryEndpoint('${srcKey}', '${src.base_url + ep.path}')">Try It</button>` : '<span style="color:var(--text-dim);">-</span>'}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `).join('');
        } catch (err) {
            container.innerHTML = `<div style="color:var(--red);padding:20px;">Failed to load catalog.</div>`;
        }
    }

    async function tryEndpoint(source, url) {
        if (!confirm(`Execute GET call to ${url} through LoggedHttpClient? (Will consume quota if not cached)`)) {
            return;
        }

        try {
            const res = await fetch('/dev/api/try', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ source, url }),
            });
            const data = await res.json();
            alert(`Call completed! Status: ${data.status}, Duration: ${data.duration_ms}ms`);
            loadCalls();
        } catch (err) {
            alert('Call failed.');
        }
    }

    // Auto-refresh setup
    document.getElementById('auto-refresh-toggle').addEventListener('change', function(e) {
        if (e.target.checked) {
            startAutoRefresh();
        } else {
            clearInterval(refreshInterval);
        }
    });

    function startAutoRefresh() {
        clearInterval(refreshInterval);
        refreshInterval = setInterval(() => {
            if (currentTab === 'call-log') {
                loadCalls();
            }
        }, 5000);
    }

    // Initial load
    loadCalls();
    startAutoRefresh();
    </script>
</x-layouts.app>
