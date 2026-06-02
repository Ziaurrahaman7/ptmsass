<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ auth()->user()->company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg: #0d0f12; --surface: #13161b; --surface2: #1a1e25;
            --border: rgba(255,255,255,0.07); --border2: rgba(255,255,255,0.13);
            --text: #e8eaf0; --muted: #6b7385;
            --accent: #4ade80; --accent2: #22d3ee;
            --danger: #f87171;
            --font: 'DM Sans', sans-serif; --mono: 'DM Mono', monospace;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: var(--font); font-size: 14px; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }
        .ptm-sidebar { background: var(--surface); border-right: 1px solid var(--border); }
        .ptm-sidebar-header { border-bottom: 1px solid var(--border); }
        .ptm-logo-icon { background: var(--accent2); border-radius: 8px; }
        .ptm-nav-link {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px;
            font-size: 13px; font-weight: 500; color: var(--muted); text-decoration: none;
            transition: all 0.15s; position: relative;
        }
        .ptm-nav-link:hover { background: var(--surface2); color: var(--text); }
        .ptm-nav-link.active { background: var(--surface2); color: var(--text); }
        .ptm-nav-link.active::before {
            content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 55%; background: var(--accent2); border-radius: 0 2px 2px 0;
        }
        .ptm-topbar { background: var(--surface); border-bottom: 1px solid var(--border); }
        .ptm-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; }
        .ptm-card2 { background: var(--surface2); border-radius: 8px; }
        .ptm-table thead { background: var(--surface2); }
        .ptm-table thead th { color: var(--muted); font-family: var(--mono); font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; }
        .ptm-table tbody tr { border-bottom: 1px solid var(--border); }
        .ptm-table tbody tr:hover { background: var(--surface2); }
        .ptm-input, .ptm-select {
            background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px;
            color: var(--text); font-family: var(--font); font-size: 13px; padding: 8px 11px;
            transition: border 0.15s;
        }
        .ptm-input:focus, .ptm-select:focus { outline: none; border-color: var(--accent2); }
        .ptm-alert-success { background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.2); color: var(--accent); border-radius: 8px; }
        .ptm-section-title { font-family: var(--mono); font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; }
    </style>
</head>
<body style="min-height:100vh;">

<div style="display:flex; height:100vh; overflow:hidden;">

    <aside class="ptm-sidebar" style="width:240px; min-width:240px; display:flex; flex-direction:column;">
        <div class="ptm-sidebar-header" style="padding:18px 16px 14px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div class="ptm-logo-icon" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ auth()->user()->company->name }}</div>
                    <div style="font-size:10px; color:var(--muted); font-family:var(--mono);">Employee Portal</div>
                </div>
            </div>
        </div>

        @php $slug = auth()->user()->company->slug; @endphp
        <nav style="flex:1; padding:10px 8px; display:flex; flex-direction:column; gap:2px;">
            <a href="{{ route('employee.dashboard', $slug) }}" class="ptm-nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('employee.tasks.index', $slug) }}" class="ptm-nav-link {{ request()->routeIs('employee.tasks.*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                My Tasks
            </a>
        </nav>

        <div style="padding:12px 12px 14px; border-top:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                <div style="width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; background:rgba(34,211,238,0.15); color:var(--accent2); flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:13px; font-weight:500; color:var(--text);">{{ auth()->user()->name }}</div>
                    <div style="font-size:10px; color:var(--muted); font-family:var(--mono);">Employee</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button style="background:none; border:none; color:var(--muted); font-family:var(--font); font-size:12px; cursor:pointer; padding:0;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">→ Logout</button>
            </form>
        </div>
    </aside>

    <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">
        <header class="ptm-topbar" style="padding:14px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="font-size:15px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $title ?? 'Dashboard' }}</div>
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ now()->format('D, d M Y') }}</div>
        </header>

        <main style="flex:1; overflow-y:auto; padding:24px;">
            @if(session('success'))
                <div class="ptm-alert-success" style="padding:10px 14px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
