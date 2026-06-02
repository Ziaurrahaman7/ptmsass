<x-employee-layout title="My Dashboard">

    <div style="margin-bottom:18px;">
        <div style="font-size:13px; color:var(--muted);">Welcome back, <span style="color:var(--text); font-weight:500;">{{ auth()->user()->name }}</span> 👋</div>
    </div>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:24px;">
        @foreach([
            ['label'=>'Assigned',    'value'=>$totalAssigned, 'color'=>'#4ade80'],
            ['label'=>'In Progress', 'value'=>$inProgress,    'color'=>'#22d3ee'],
            ['label'=>'In Review',   'value'=>$inReview,      'color'=>'#a78bfa'],
            ['label'=>'Done',        'value'=>$done,          'color'=>'#4ade80'],
            ['label'=>'Overdue',     'value'=>$overdue,       'color'=>'#f87171'],
        ] as $s)
        <div class="ptm-card" style="padding:16px;">
            <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">{{ $s['label'] }}</div>
            <div style="font-size:24px; font-weight:600; letter-spacing:-0.5px; color:{{ $s['color'] }};">{{ $s['value'] }}</div>
            <div style="height:2px; width:24px; background:{{ $s['color'] }}; border-radius:2px; margin-top:10px; opacity:0.5;"></div>
        </div>
        @endforeach
    </div>

    {{-- Recent Tasks --}}
    <div class="ptm-card">
        <div style="padding:14px 18px 12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Recent Tasks</span>
            <a href="{{ route('employee.tasks.index', auth()->user()->company->slug) }}" style="font-size:12px; color:var(--accent2); text-decoration:none;">View all →</a>
        </div>
        <div>
            @forelse($myTasks as $task)
            <div style="padding:12px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <div style="min-width:0; flex:1;">
                    <div style="font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $task->title }}</div>
                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">
                        {{ $task->project->name }}
                        @if($task->due_date)· <span style="{{ $task->due_date->isPast() && $task->status !== 'done' ? 'color:#f87171;' : '' }}">Due {{ $task->due_date->format('d M Y') }}</span>@endif
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                        {{ $task->priority === 'urgent' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                           ($task->priority === 'high' ? 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                           ($task->priority === 'medium' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                    <form method="POST" action="{{ route('employee.tasks.status', [auth()->user()->company->slug, $task]) }}">
                        @csrf @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="ptm-select" style="font-size:12px; padding:5px 9px;">
                            @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                            <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            @empty
            <div style="padding:40px; text-align:center; color:var(--muted); font-size:13px;">No tasks assigned to you yet.</div>
            @endforelse
        </div>
    </div>

</x-employee-layout>
