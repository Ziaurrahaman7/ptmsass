<x-company-layout :title="$portfolio->title">

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.back-btn { display:inline-flex; align-items:center; gap:6px; color:var(--muted); font-size:13px; text-decoration:none; margin-bottom:14px; transition:color .15s; }
.back-btn:hover { color:var(--text); }

.pfh-topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
.pfh-title-row { display:flex; align-items:center; gap:12px; }
.pfh-icon { width:38px; height:38px; border-radius:10px; background:linear-gradient(135deg,#f59e0b,#ef4444); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pfh-title { font-size:20px; font-weight:700; color:var(--text); }
.pfh-actions { display:flex; align-items:center; gap:10px; }

.btn-primary { display:inline-flex; align-items:center; gap:6px; background:#4573d2; color:#fff; border:none; border-radius:8px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); transition:background .15s; }
.btn-primary:hover { background:#3a62bb; }
.btn-outline-danger { background:none; border:1px solid var(--border2); color:var(--muted); border-radius:8px; padding:8px 12px; font-size:12px; cursor:pointer; font-family:var(--font); transition:color .15s, border-color .15s; }
.btn-outline-danger:hover { color:var(--danger); border-color:var(--danger); }

/* Tabs */
.pf-tabs { display:flex; gap:22px; border-bottom:1px solid var(--border); margin-bottom:22px; }
.pf-tab { font-size:14px; font-weight:600; color:var(--muted); padding-bottom:10px; border-bottom:2px solid transparent; cursor:pointer; transition:color .15s; }
.pf-tab:hover { color:var(--text); }
.pf-tab.active { color:var(--text); border-bottom-color:var(--text); }
.pf-panel { display:none; }
.pf-panel.active { display:block; }

/* List tab */
.pf-list-table { width:100%; border-collapse:collapse; }
.pf-list-table th { text-align:left; font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:.05em; padding:8px 10px; border-bottom:1px solid var(--border); }
.pf-list-table td { padding:12px 10px; border-bottom:1px solid var(--border); font-size:13px; color:var(--text); vertical-align:middle; }
.pf-list-table tr:last-child td { border-bottom:none; }
.pf-list-table tr:hover td { background:var(--surface2); }
.pf-status-badge { display:inline-block; font-size:11px; font-weight:600; padding:3px 9px; border-radius:20px; }
.pf-progress-track { width:120px; height:6px; background:var(--border); border-radius:3px; overflow:hidden; display:inline-block; vertical-align:middle; margin-right:8px; }
.pf-progress-fill { height:100%; border-radius:3px; background:#4ade80; }
.pf-row-remove { background:none; border:none; color:var(--muted); cursor:pointer; padding:4px; border-radius:6px; transition:color .15s, background .15s; }
.pf-row-remove:hover { color:var(--danger); background:rgba(248,113,113,.1); }

/* Cards / KPI */
.pf-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px 22px; }
.pf-card-title { font-size:11px; font-family:var(--mono); color:var(--muted); text-transform:uppercase; letter-spacing:.07em; margin-bottom:14px; }
.pf-stat-big { font-size:32px; font-weight:700; letter-spacing:-1px; color:var(--text); line-height:1; }
.pf-stat-sub { font-size:12px; color:var(--muted); margin-top:4px; }

/* Timeline */
.pf-tl-row { display:grid; grid-template-columns:200px 1fr; gap:14px; align-items:center; padding:9px 0; border-bottom:1px solid var(--border); }
.pf-tl-row:last-child { border-bottom:none; }
.pf-tl-name { font-size:13px; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.pf-tl-track { position:relative; height:22px; background:var(--surface2); border-radius:6px; }
.pf-tl-bar { position:absolute; top:2px; bottom:2px; border-radius:5px; min-width:10px; display:flex; align-items:center; padding:0 8px; }
.pf-tl-bar span { font-size:10px; font-weight:600; color:#0a0f1a; white-space:nowrap; overflow:hidden; }

/* Workload */
.pf-wl-row { display:grid; grid-template-columns:160px 1fr 60px; gap:14px; align-items:center; padding:10px 0; border-bottom:1px solid var(--border); }
.pf-wl-row:last-child { border-bottom:none; }
.pf-wl-name { font-size:13px; color:var(--text); display:flex; align-items:center; gap:8px; }
.pf-wl-avatar { width:24px; height:24px; border-radius:50%; background:#7c3aed; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pf-wl-track { height:10px; background:var(--surface2); border-radius:5px; overflow:hidden; }
.pf-wl-fill { height:100%; border-radius:5px; background:#22d3ee; }
.pf-wl-count { font-size:12px; color:var(--muted); text-align:right; font-family:var(--mono); }

.pf-empty { text-align:center; padding:50px 20px; color:var(--muted); font-size:13px; }

/* Modals (shared with index) */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px; width:440px; max-width:95vw; }
.modal-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:20px; }
.form-row { margin-bottom:14px; }
.form-label { font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; display:block; }
.form-control { width:100%; background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:8px 12px; color:var(--text); font-size:13px; font-family:var(--font); outline:none; transition:border-color .15s; box-sizing:border-box; }
.form-control:focus { border-color:var(--accent); }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:22px; }
.btn-cancel { background:none; border:1px solid var(--border2); color:var(--muted); border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-family:var(--font); }
.btn-save { background:var(--accent); border:none; color:#0a0f1a; border-radius:8px; padding:8px 20px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); }
</style>

<a href="{{ route('company.portfolios.index', $slug) }}" class="back-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Portfolios
</a>

<div class="pfh-topbar">
    <div class="pfh-title-row">
        <div class="pfh-icon">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
        </div>
        <span class="pfh-title">{{ $portfolio->title }}</span>
    </div>
    <div class="pfh-actions">
        <button class="btn-primary" onclick="document.getElementById('addProjectModal').classList.add('open')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add project
        </button>
        <form method="POST" action="{{ route('company.portfolios.destroy', [$slug, $portfolio->id]) }}" onsubmit="return confirm('Delete this portfolio? Projects themselves will not be deleted.')" style="margin:0;">
            @csrf @method('DELETE')
            <button type="submit" class="btn-outline-danger">Delete</button>
        </form>
    </div>
</div>

{{-- Tabs --}}
<div class="pf-tabs">
    <div class="pf-tab active" data-tab="list" onclick="switchPfTab('list')">List</div>
    <div class="pf-tab" data-tab="timeline" onclick="switchPfTab('timeline')">Timeline</div>
    <div class="pf-tab" data-tab="dashboard" onclick="switchPfTab('dashboard')">Dashboard</div>
    <div class="pf-tab" data-tab="progress" onclick="switchPfTab('progress')">Progress</div>
    <div class="pf-tab" data-tab="workload" onclick="switchPfTab('workload')">Workload</div>
</div>

{{-- LIST --}}
<div class="pf-panel active" id="panel-list">
    @if($projects->isEmpty())
        <div class="pf-empty">No projects in this portfolio yet. Click "Add project" to get started.</div>
    @else
    <table class="pf-list-table">
        <thead>
            <tr><th>Name</th><th>Status</th><th>Task progress</th><th>Owner</th><th>Due date</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($projects as $p)
            @php
                $statusColors = ['planning'=>'#60a5fa','in_progress'=>'#fbbf24','on_hold'=>'#f87171','completed'=>'#4ade80'];
                $sc = $statusColors[$p->status] ?? '#6b7385';
            @endphp
            <tr>
                <td><a href="{{ route('company.projects.show', [$slug, $p->id]) }}" style="color:var(--text); text-decoration:none; font-weight:500;">{{ $p->name }}</a></td>
                <td><span class="pf-status-badge" style="background:{{ $sc }}22; color:{{ $sc }};">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span></td>
                <td>
                    <div class="pf-progress-track"><div class="pf-progress-fill" style="width:{{ $p->progress }}%; background:{{ $sc }};"></div></div>
                    <span style="font-size:12px; color:var(--muted);">{{ $p->progress }}%</span>
                </td>
                <td>{{ $p->creator->name ?? '—' }}</td>
                <td style="color:var(--muted);">{{ $p->due_date?->format('d M Y') ?? '—' }}</td>
                <td style="text-align:right;">
                    <button class="pf-row-remove" title="Remove from portfolio" onclick="removeProjectFromPortfolio({{ $p->id }})">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- TIMELINE --}}
<div class="pf-panel" id="panel-timeline">
    @if($timelineProjects->isEmpty())
        <div class="pf-empty">No projects with both a start date and a due date yet.</div>
    @else
        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:10px;">
            {{ $rangeStart->format('d M Y') }} — {{ $rangeEnd->format('d M Y') }}
        </div>
        <div class="pf-card">
            @php
                $totalDays = max($rangeStart->diffInDays($rangeEnd), 1);
                $statusColors = ['planning'=>'#60a5fa','in_progress'=>'#fbbf24','on_hold'=>'#f87171','completed'=>'#4ade80'];
            @endphp
            @foreach($timelineProjects as $p)
            @php
                $offset = $rangeStart->diffInDays($p->start_date);
                $span = max($p->start_date->diffInDays($p->due_date), 1) + 1;
                $left = round(($offset / $totalDays) * 100, 2);
                $width = max(round(($span / $totalDays) * 100, 2), 2);
                $sc = $statusColors[$p->status] ?? '#6b7385';
            @endphp
            <div class="pf-tl-row">
                <div class="pf-tl-name" title="{{ $p->name }}">{{ $p->name }}</div>
                <div class="pf-tl-track">
                    <div class="pf-tl-bar" style="left:{{ $left }}%; width:{{ $width }}%; background:{{ $sc }};" title="{{ $p->start_date->format('d M') }} – {{ $p->due_date->format('d M Y') }}">
                        <span>{{ $p->progress }}%</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- DASHBOARD --}}
<div class="pf-panel" id="panel-dashboard">
    @if($projects->isEmpty())
        <div class="pf-empty">Add projects to this portfolio to see aggregate stats.</div>
    @else
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:16px;">
        <div class="pf-card">
            <div class="pf-card-title">Completion rate</div>
            <div class="pf-stat-big" style="color:#4ade80;">{{ $completionRate }}%</div>
            <div style="height:5px; background:var(--border); border-radius:3px; margin-top:10px;"><div style="height:100%; width:{{ $completionRate }}%; background:#4ade80; border-radius:3px;"></div></div>
        </div>
        <div class="pf-card">
            <div class="pf-card-title">Total tasks</div>
            <div class="pf-stat-big">{{ $totalTasks }}</div>
            <div class="pf-stat-sub">{{ $doneTasks }} done</div>
        </div>
        <div class="pf-card">
            <div class="pf-card-title">Overdue tasks</div>
            <div class="pf-stat-big" style="color:#f87171;">{{ $overdueCount }}</div>
            <div class="pf-stat-sub">past due date</div>
        </div>
        <div class="pf-card">
            <div class="pf-card-title">Projects</div>
            <div class="pf-stat-big">{{ $projects->count() }}</div>
            <div class="pf-stat-sub">in this portfolio</div>
        </div>
    </div>
    <div class="pf-card" style="max-width:420px;">
        <div class="pf-card-title">Projects by status</div>
        <div style="position:relative; height:220px;">
            <canvas id="pfStatusChart"></canvas>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('pfStatusChart');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Planning', 'In Progress', 'On Hold', 'Completed'],
                datasets: [{
                    data: [{{ $statusCounts['planning'] }}, {{ $statusCounts['in_progress'] }}, {{ $statusCounts['on_hold'] }}, {{ $statusCounts['completed'] }}],
                    backgroundColor: ['#60a5fa', '#fbbf24', '#f87171', '#4ade80'],
                    borderWidth: 0, hoverOffset: 8,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#6b7385', font: { size: 11 }, boxWidth: 10, padding: 14 } },
                    tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed}` } },
                }
            }
        });
    });
    </script>
    @endif
</div>

{{-- PROGRESS --}}
<div class="pf-panel" id="panel-progress">
    @if($projects->isEmpty())
        <div class="pf-empty">Add projects to this portfolio to track progress.</div>
    @else
    <div class="pf-card">
        @foreach($projects->sortBy('progress') as $p)
        @php
            $statusColors = ['planning'=>'#60a5fa','in_progress'=>'#fbbf24','on_hold'=>'#f87171','completed'=>'#4ade80'];
            $sc = $statusColors[$p->status] ?? '#6b7385';
        @endphp
        <div style="display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--border);">
            <div style="width:200px; flex-shrink:0; font-size:13px; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $p->name }}</div>
            <div style="flex:1; height:8px; background:var(--surface2); border-radius:4px; overflow:hidden;">
                <div style="height:100%; width:{{ $p->progress }}%; background:{{ $sc }}; border-radius:4px;"></div>
            </div>
            <div style="width:48px; text-align:right; font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $p->progress }}%</div>
            <div style="width:80px; text-align:right; font-size:11px; color:var(--muted);">{{ $p->done_tasks_count }}/{{ $p->tasks_count }}</div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- WORKLOAD --}}
<div class="pf-panel" id="panel-workload">
    @if($workload->isEmpty())
        <div class="pf-empty">No tasks assigned to members across this portfolio's projects yet.</div>
    @else
    @php $maxOpen = max($workload->max('open_tasks'), 1); @endphp
    <div class="pf-card">
        @foreach($workload as $u)
        <div class="pf-wl-row">
            <div class="pf-wl-name">
                <div class="pf-wl-avatar">{{ strtoupper(substr($u->name,0,2)) }}</div>
                <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $u->name }}</span>
            </div>
            <div class="pf-wl-track"><div class="pf-wl-fill" style="width:{{ round(($u->open_tasks / $maxOpen) * 100) }}%;"></div></div>
            <div class="pf-wl-count">{{ $u->open_tasks }}/{{ $u->total_tasks }}</div>
        </div>
        @endforeach
    </div>
    <div style="font-size:11px; color:var(--muted); margin-top:10px;">Open tasks / total tasks assigned, across this portfolio's projects.</div>
    @endif
</div>

{{-- Add Project Modal --}}
<div class="modal-overlay" id="addProjectModal">
    <div class="modal-box">
        <div class="modal-title">Add project to portfolio</div>
        @if($availableProjects->isEmpty())
            <div style="font-size:13px; color:var(--muted);">All of your projects are already in this portfolio.</div>
        @else
        <div class="form-row">
            <label class="form-label">Project</label>
            <select id="apProjectSelect" class="form-control">
                @foreach($availableProjects as $ap)
                <option value="{{ $ap->id }}">{{ $ap->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeAddProjectModal()">Cancel</button>
            @if($availableProjects->isNotEmpty())
            <button type="button" class="btn-save" id="apSaveBtn" onclick="addProjectToPortfolio()">Add</button>
            @endif
        </div>
    </div>
</div>

<script>
function switchPfTab(tab) {
    document.querySelectorAll('.pf-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
    document.querySelectorAll('.pf-panel').forEach(p => p.classList.toggle('active', p.id === 'panel-' + tab));
}

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function closeAddProjectModal() {
    document.getElementById('addProjectModal').classList.remove('open');
}
document.getElementById('addProjectModal').addEventListener('click', function (e) {
    if (e.target === this) closeAddProjectModal();
});

function addProjectToPortfolio() {
    const select = document.getElementById('apProjectSelect');
    if (!select) return;
    const btn = document.getElementById('apSaveBtn');
    btn.disabled = true; btn.textContent = 'Adding…';

    fetch('{{ route("company.portfolios.projects.add", [$slug, $portfolio->id]) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ project_id: select.value })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.reload(); return; }
        btn.disabled = false; btn.textContent = 'Add';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Add'; });
}

function removeProjectFromPortfolio(projectId) {
    if (!confirm('Remove this project from the portfolio?')) return;
    fetch('{{ url("/{$slug}/admin/portfolios/{$portfolio->id}/projects") }}/' + projectId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); });
}
</script>

</x-company-layout>
