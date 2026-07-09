<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ auth()->user()->company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg: #0d0f12;
            --surface: #13161b;
            --surface2: #1a1e25;
            --border: rgba(255,255,255,0.07);
            --border2: rgba(255,255,255,0.13);
            --text: #e8eaf0;
            --muted: #6b7385;
            --accent: #4ade80;
            --accent2: #22d3ee;
            --warn: #fbbf24;
            --danger: #f87171;
            --purple: #a78bfa;
            --font: 'DM Sans', sans-serif;
            --mono: 'DM Mono', monospace;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: var(--font); font-size: 14px; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

        /* Sidebar */
        .ptm-sidebar { background: var(--surface); border-right: 1px solid var(--border); }
        .ptm-sidebar-header { border-bottom: 1px solid var(--border); }
        .ptm-logo-icon { background: var(--accent); border-radius: 8px; }
        .ptm-nav-link {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px;
            font-size: 13px; font-weight: 500; color: var(--muted); text-decoration: none;
            transition: all 0.15s; position: relative;
        }
        .ptm-nav-link:hover { background: var(--surface2); color: var(--text); }
        .ptm-nav-link.active { background: var(--surface2); color: var(--text); }
        .ptm-nav-link.active::before {
            content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 55%; background: var(--accent); border-radius: 0 2px 2px 0;
        }

        /* Topbar */
        .ptm-topbar { background: var(--surface); border-bottom: 1px solid var(--border); }

        /* Cards */
        .ptm-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; }
        .ptm-card2 { background: var(--surface2); border-radius: 8px; }
        .ptm-divider { border-color: var(--border); }

        /* Table */
        .ptm-table thead { background: var(--surface2); }
        .ptm-table thead th { color: var(--muted); font-family: var(--mono); font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; }
        .ptm-table tbody tr { border-bottom: 1px solid var(--border); }
        .ptm-table tbody tr:hover { background: var(--surface2); }

        /* Inputs */
        .ptm-input, .ptm-select {
            background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px;
            color: var(--text); font-family: var(--font); font-size: 13px; padding: 9px 12px;
            width: 100%; transition: border 0.15s;
        }
        .ptm-input:focus, .ptm-select:focus { outline: none; border-color: var(--accent2); }

        /* Buttons */
        .ptm-btn-primary {
            background: rgba(74,222,128,0.12); border: 1px solid rgba(74,222,128,0.3);
            color: var(--accent); border-radius: 8px; padding: 8px 16px;
            font-family: var(--font); font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s;
        }
        .ptm-btn-primary:hover { background: rgba(74,222,128,0.2); }
        .ptm-btn-danger {
            background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.2);
            color: var(--danger); border-radius: 6px; padding: 4px 10px;
            font-family: var(--font); font-size: 12px; cursor: pointer; transition: all 0.15s;
        }
        .ptm-btn-danger:hover { background: rgba(248,113,113,0.15); }
        .ptm-btn-ghost {
            background: transparent; border: 1px solid var(--border2);
            color: var(--muted); border-radius: 8px; padding: 8px 16px;
            font-family: var(--font); font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s;
        }
        .ptm-btn-ghost:hover { background: var(--surface2); color: var(--text); }

        /* Modal */
        .ptm-modal-bg { background: rgba(0,0,0,0.7); }
        .ptm-modal { background: var(--surface); border: 1px solid var(--border2); border-radius: 16px; }

        /* Badge */
        .ptm-badge { font-family: var(--mono); font-size: 11px; border-radius: 6px; padding: 3px 8px; }

        /* Section title */
        .ptm-section-title { font-family: var(--mono); font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; }

        /* Avatar */
        .ptm-avatar { border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 600; }

        /* Progress */
        .ptm-progress-track { height: 3px; background: var(--border); border-radius: 2px; }
        .ptm-progress-fill { height: 100%; border-radius: 2px; background: var(--accent); }

        /* Alert */
        .ptm-alert-success { background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.2); color: var(--accent); border-radius: 8px; }
        .ptm-alert-error { background: rgba(248,113,113,0.06); border: 1px solid rgba(248,113,113,0.2); color: var(--danger); border-radius: 8px; }

        /* Kanban column */
        .ptm-kanban-col { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; }
        .ptm-kanban-card { background: var(--surface2); border: 1px solid transparent; border-radius: 8px; transition: all 0.15s; }
        .ptm-kanban-card:hover { border-color: var(--border2); }
        .sortable-ghost { opacity: 0.4; }
        .sortable-drag { transform: rotate(3deg); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    </style>
</head>
<body style="min-height:100vh;">

<div style="display:flex; height:100vh; overflow:hidden;">

    {{-- Sidebar --}}
    <aside class="ptm-sidebar" style="width:240px; min-width:240px; display:flex; flex-direction:column; overflow:hidden;">
        <div class="ptm-sidebar-header" style="padding:18px 16px 14px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <div class="ptm-logo-icon" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ auth()->user()->company->name }}</div>
                    <div style="font-size:10px; color:var(--muted); font-family:var(--mono);">Project Management</div>
                </div>
            </div>
        </div>

        @php
            $slug = auth()->user()->company->slug;
            $sidebarProjects = \App\Models\Project::where('company_id', auth()->user()->company_id)
                ->orderBy('name')
                ->get(['id', 'name']);
            $activeProject = request()->route('project');
            $activeProjectId = is_object($activeProject) ? $activeProject->id : (int) $activeProject;
            $sidebarTeams = \App\Models\Team::where('company_id', auth()->user()->company_id)
                ->orderBy('name')
                ->get(['id', 'name']);
            $activeTeam = request()->route('team');
            $activeTeamId = is_object($activeTeam) ? $activeTeam->id : (int) $activeTeam;
        @endphp
        <nav style="flex:1; padding:10px 8px; overflow-y:auto; display:flex; flex-direction:column; gap:2px;">
            <a href="{{ route('company.dashboard', $slug) }}" class="ptm-nav-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('company.tasks.index', $slug) }}" class="ptm-nav-link {{ request()->routeIs('company.tasks.*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                My Tasks
            </a>
            <a href="{{ route('company.members.index', $slug) }}" class="ptm-nav-link {{ request()->routeIs('company.members.*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                Members
            </a>

            {{-- Teams Dropdown --}}
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 12px 6px;">
                <span class="ptm-section-title">Teams</span>
                <button onclick="document.getElementById('createTeamModal').style.display='flex'" style="background:none; border:none; color:var(--muted); cursor:pointer; display:flex; padding:0;" title="New team" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
            </div>
            @forelse($sidebarTeams as $sidebarTeam)
                <a href="{{ route('company.team.overview', [$slug, $sidebarTeam->id]) }}"
                   class="ptm-nav-link {{ $activeTeamId === $sidebarTeam->id ? 'active' : '' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $sidebarTeam->name }}</span>
                </a>
            @empty
                <div style="padding:6px 12px; font-size:11px; color:var(--muted); font-family:var(--mono);">No teams yet</div>
            @endforelse

            {{-- Projects list --}}
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 12px 6px;">
                <span class="ptm-section-title">Projects</span>
                <a href="{{ route('company.projects.create', $slug) }}" style="display:flex; color:var(--muted); text-decoration:none;" title="New project" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </a>
            </div>
            @forelse($sidebarProjects as $sidebarProject)
                <a href="{{ route('company.projects.show', [$slug, $sidebarProject->id]) }}"
                   class="ptm-nav-link {{ $activeProjectId === $sidebarProject->id ? 'active' : '' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $sidebarProject->name }}</span>
                </a>
            @empty
                <div style="padding:6px 12px; font-size:11px; color:var(--muted); font-family:var(--mono);">No projects yet</div>
            @endforelse
        </nav>

        <div style="padding:12px 12px 14px; border-top:1px solid var(--border);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                <div class="ptm-avatar" style="width:30px; height:30px; font-size:12px; background:rgba(74,222,128,0.15); color:var(--accent); flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:13px; font-weight:500; color:var(--text);">{{ auth()->user()->name }}</div>
                    <div style="font-size:10px; color:var(--muted); font-family:var(--mono);">Company Admin</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button style="background:none; border:none; color:var(--muted); font-family:var(--font); font-size:12px; cursor:pointer; padding:0;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">→ Logout</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">
        <header class="ptm-topbar" style="padding:14px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div>
                <div style="font-size:15px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $title ?? 'Dashboard' }}</div>
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
                {{-- Notification Bell --}}
                <div style="position:relative;">
                    <button id="notificationBell" onclick="toggleNotifications()" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:6px; position:relative; transition:color 0.15s;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span id="notificationCount" style="position:absolute; top:2px; right:2px; background:#f87171; color:#fff; border-radius:8px; font-size:9px; font-family:var(--mono); font-weight:600; padding:1px 4px; min-width:14px; text-align:center; display:none;">0</span>
                    </button>
                    
                    {{-- Notification Dropdown --}}
                    <div id="notificationDropdown" style="display:none; position:absolute; top:calc(100% + 8px); right:0; width:360px; max-height:480px; background:var(--surface); border:1px solid var(--border2); border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.3); z-index:1000; overflow:hidden;">
                        <div style="padding:14px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:600; color:var(--text);">Notifications</span>
                            <button onclick="markAllAsRead()" style="background:none; border:none; color:var(--muted); font-size:11px; cursor:pointer; font-family:var(--mono);" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">Mark all read</button>
                        </div>
                        <div id="notificationList" style="max-height:400px; overflow-y:auto;">
                            <div style="padding:40px 20px; text-align:center; color:var(--muted); font-size:13px;">Loading...</div>
                        </div>
                        <div style="padding:10px 16px; border-top:1px solid var(--border); text-align:center;">
                            <a href="{{ route('company.notifications.index', $slug) }}" style="font-size:12px; color:var(--accent2); text-decoration:none; font-weight:500;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--accent2)'">View all notifications</a>
                        </div>
                    </div>
                </div>
                <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ now()->format('D, d M Y') }}</div>
            </div>
        </header>

        <main style="flex:1; overflow-y:auto; padding:24px;">
            @if(session('success'))
                <div class="ptm-alert-success" style="padding:10px 14px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="ptm-alert-error" style="padding:10px 14px; font-size:13px; margin-bottom:18px;">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

{{-- Create Team Modal --}}
<div id="createTeamModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:200; align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:460px;">
        <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:15px; font-weight:600; color:var(--text);">Create Team</span>
            <button onclick="document.getElementById('createTeamModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px;">✕</button>
        </div>
        <form method="POST" action="{{ route('company.teams.store', $slug) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            @csrf
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TEAM NAME *</label>
                <input type="text" name="name" class="ptm-input" style="width:100%;" placeholder="e.g. Design Team" required>
            </div>
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DESCRIPTION</label>
                <textarea name="description" rows="2" class="ptm-input" style="width:100%; resize:vertical;" placeholder="What does this team work on?"></textarea>
            </div>
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ADD MEMBERS</label>
                <div class="custom-multiselect" style="position:relative;">
                    <div class="multiselect-trigger" onclick="toggleMultiselectTeam(this)" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:9px 12px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; min-height:42px;">
                        <div class="selected-users" style="display:flex; flex-wrap:wrap; gap:4px; flex:1;">
                            <span style="font-size:13px; color:var(--muted);">Select members...</span>
                        </div>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; transition:transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="multiselect-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; margin-top:4px; background:var(--surface); border:1px solid var(--border2); border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.3); z-index:1000; max-height:200px; overflow-y:auto;">
                        <div style="padding:8px;">
                            @foreach(auth()->user()->company->users()->where('is_active', true)->get() as $m)
                            <label class="multiselect-option" data-user-id="{{ $m->id }}" data-user-name="{{ $m->name }}" data-user-initial="{{ strtoupper(substr($m->name,0,1)) }}" style="display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:6px; cursor:pointer;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="members[]" value="{{ $m->id }}" style="width:16px; height:16px; cursor:pointer;" onchange="updateSelectedUsersTeam(this)">
                                <div style="width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:11px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($m->name,0,1)) }}</div>
                                <span style="font-size:13px; color:var(--text);">{{ $m->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:10px; padding-top:4px;">
                <button type="submit" class="ptm-btn-primary">Create Team</button>
                <button type="button" onclick="document.getElementById('createTeamModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const slug = '{{ $slug }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Fetch unread notifications
function fetchNotifications() {
    fetch('/' + slug + '/admin/notifications/unread', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const countBadge = document.getElementById('notificationCount');
        const notificationList = document.getElementById('notificationList');
        
        if (data.count > 0) {
            countBadge.textContent = data.count;
            countBadge.style.display = 'block';
        } else {
            countBadge.style.display = 'none';
        }
        
        if (data.notifications.length === 0) {
            notificationList.innerHTML = '<div style="padding:40px 20px; text-align:center; color:var(--muted); font-size:13px;">No new notifications</div>';
        } else {
            notificationList.innerHTML = data.notifications.map(n => `
                <div onclick="markAsReadAndNavigate(${n.id}, '${n.link}')" style="padding:12px 16px; border-bottom:1px solid var(--border); cursor:pointer; transition:background 0.15s; ${n.is_read ? '' : 'background:rgba(74,222,128,0.03);'}" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='${n.is_read ? 'transparent' : 'rgba(74,222,128,0.03)'}'}">
                    <div style="display:flex; align-items:flex-start; gap:10px;">
                        <div style="width:6px; height:6px; border-radius:50%; background:${n.is_read ? 'transparent' : 'var(--accent)'}; margin-top:6px; flex-shrink:0;"></div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:12px; font-weight:500; color:var(--text); margin-bottom:2px;">${n.title}</div>
                            <div style="font-size:12px; color:var(--muted); line-height:1.4;">${n.message}</div>
                            <div style="font-size:10px; color:var(--muted); font-family:var(--mono); margin-top:4px;">${timeAgo(n.created_at)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    });
}

// Toggle notification dropdown
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown.style.display === 'none') {
        dropdown.style.display = 'block';
        fetchNotifications();
    } else {
        dropdown.style.display = 'none';
    }
}

// Mark as read and navigate
function markAsReadAndNavigate(notificationId, link) {
    fetch('/' + slug + '/admin/notifications/' + notificationId + '/read', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    }).then(() => {
        if (link) window.location.href = link;
    });
}

// Mark all as read
function markAllAsRead() {
    fetch('/' + slug + '/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    }).then(() => {
        fetchNotifications();
    });
}

// Time ago helper
function timeAgo(dateString) {
    const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + 'm ago';
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + 'h ago';
    const days = Math.floor(hours / 24);
    return days + 'd ago';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('notificationDropdown');
    const bell = document.getElementById('notificationBell');
    if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// Fetch notifications every 30 seconds
fetchNotifications();
setInterval(fetchNotifications, 30000);

// Team multiselect
function toggleMultiselectTeam(trigger) {
    const dropdown = trigger.nextElementSibling;
    const isVisible = dropdown.style.display === 'block';
    document.querySelectorAll('.multiselect-dropdown').forEach(d => d.style.display = 'none');
    dropdown.style.display = isVisible ? 'none' : 'block';
    trigger.querySelector('svg').style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
}

function updateSelectedUsersTeam(checkbox) {
    const container = checkbox.closest('.custom-multiselect');
    const selectedDiv = container.querySelector('.selected-users');
    const checkedBoxes = container.querySelectorAll('input[type="checkbox"]:checked');
    selectedDiv.innerHTML = '';
    if (checkedBoxes.length === 0) {
        selectedDiv.innerHTML = '<span style="font-size:13px; color:var(--muted);">Select members...</span>';
    } else {
        checkedBoxes.forEach(cb => {
            const option = cb.closest('.multiselect-option');
            const badge = document.createElement('div');
            badge.style.cssText = 'display:inline-flex; align-items:center; gap:4px; padding:4px 8px; background:rgba(74,222,128,0.15); border:1px solid rgba(74,222,128,0.3); border-radius:6px; font-size:12px; color:var(--text);';
            badge.innerHTML = `<div style="width:18px;height:18px;border-radius:4px;background:rgba(74,222,128,0.3);color:#4ade80;font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center;">${option.dataset.userInitial}</div><span>${option.dataset.userName}</span>`;
            selectedDiv.appendChild(badge);
        });
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-multiselect')) {
        document.querySelectorAll('.multiselect-dropdown').forEach(d => d.style.display = 'none');
        document.querySelectorAll('.multiselect-trigger svg').forEach(svg => svg.style.transform = 'rotate(0deg)');
    }
});
</script>

</body>
</html>
