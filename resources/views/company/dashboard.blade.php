<x-company-layout title="Dashboard">

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px;">
        @foreach([
            ['label'=>'Total Projects', 'value'=>$totalProjects,  'color'=>'#4ade80'],
            ['label'=>'Active Projects','value'=>$activeProjects,  'color'=>'#22d3ee'],
            ['label'=>'Total Tasks',    'value'=>$totalTasks,      'color'=>'#a78bfa'],
            ['label'=>'Overdue Tasks',  'value'=>$overdueTasks,    'color'=>'#f87171'],
        ] as $s)
        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">{{ $s['label'] }}</div>
            <div style="font-size:26px; font-weight:600; letter-spacing:-0.5px; color:{{ $s['color'] }};">{{ $s['value'] }}</div>
        </div>
        @endforeach
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

        {{-- Recent Projects --}}
        <div class="ptm-card">
            <div style="padding:14px 18px 12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Recent Projects</span>
                <a href="{{ route('company.projects.index', $slug) }}" style="font-size:12px; color:var(--accent); text-decoration:none;">View all →</a>
            </div>
            <div>
                @forelse($recentProjects as $project)
                <div style="padding:12px 18px; border-bottom:1px solid var(--border);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <a href="{{ route('company.projects.show', [$slug, $project]) }}" style="font-size:13px; font-weight:500; color:var(--text); text-decoration:none;">{{ $project->name }}</a>
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $project->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                               ($project->status === 'completed' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                               ($project->status === 'on_hold' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                            {{ ucfirst(str_replace('_',' ',$project->status)) }}
                        </span>
                    </div>
                    <div style="height:3px; background:var(--border); border-radius:2px;">
                        <div style="height:100%; border-radius:2px; background:#4ade80; width:{{ $project->progressPercentage() }}%;"></div>
                    </div>
                    <div style="font-size:11px; color:var(--muted); margin-top:5px; font-family:var(--mono);">{{ $project->progressPercentage() }}% · {{ $project->tasks_count }} tasks</div>
                </div>
                @empty
                <div style="padding:24px; text-align:center; color:var(--muted); font-size:13px;">
                    No projects yet. <a href="{{ route('company.projects.create', $slug) }}" style="color:var(--accent); text-decoration:none;">Create one →</a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Tasks --}}
        <div class="ptm-card">
            <div style="padding:14px 18px 12px; border-bottom:1px solid var(--border);">
                <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Recent Tasks</span>
            </div>
            <div>
                @forelse($recentTasks as $task)
                <div style="padding:10px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <div style="min-width:0;">
                        <div style="font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $task->title }}</div>
                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $task->project->name }} · {{ $task->assignee?->name ?? 'Unassigned' }}</div>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                        <span class="ptm-badge" style="
                            {{ $task->priority === 'urgent' ? 'color:#f87171; border:1px solid rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                               ($task->priority === 'high' ? 'color:#fb923c; border:1px solid rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                               ($task->priority === 'medium' ? 'color:#fbbf24; border:1px solid rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border:1px solid var(--border2); background:transparent;')) }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                        <span class="ptm-badge" style="
                            {{ $task->status === 'done' ? 'color:#4ade80; border:1px solid rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                               ($task->status === 'in_progress' ? 'color:#22d3ee; border:1px solid rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                               ($task->status === 'in_review' ? 'color:#a78bfa; border:1px solid rgba(167,139,250,0.3); background:rgba(167,139,250,0.08);' : 'color:var(--muted); border:1px solid var(--border2); background:transparent;')) }}">
                            {{ ucfirst(str_replace('_',' ',$task->status)) }}
                        </span>
                    </div>
                </div>
                @empty
                <div style="padding:24px; text-align:center; color:var(--muted); font-size:13px;">No tasks yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</x-company-layout>
