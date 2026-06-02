<x-employee-layout title="My Tasks">

    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:22px;">
        <div>
            <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">My Tasks</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">All tasks assigned to you</div>
        </div>
        <form method="GET" style="display:flex; align-items:center; gap:8px;">
            <select name="status" onchange="this.form.submit()" class="ptm-select" style="font-size:12px; padding:7px 11px;">
                <option value="">All Status</option>
                @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                <option value="{{ $val }}" {{ request('status')===$val?'selected':'' }}>{{ $lbl }}</option>
                @endforeach
            </select>
            <select name="priority" onchange="this.form.submit()" class="ptm-select" style="font-size:12px; padding:7px 11px;">
                <option value="">All Priority</option>
                @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $val=>$lbl)
                <option value="{{ $val }}" {{ request('priority')===$val?'selected':'' }}>{{ $lbl }}</option>
                @endforeach
            </select>
            @if(request('status') || request('priority'))
            <a href="{{ route('employee.tasks.index') }}" style="font-size:12px; color:var(--muted); text-decoration:none;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">✕ Clear</a>
            @endif
        </form>
    </div>

    <div class="ptm-card" style="overflow:hidden;">
        <table class="ptm-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 18px; text-align:left;">Task</th>
                    <th style="padding:12px 18px; text-align:left;">Project</th>
                    <th style="padding:12px 18px; text-align:left;">Priority</th>
                    <th style="padding:12px 18px; text-align:left;">Due Date</th>
                    <th style="padding:12px 18px; text-align:left;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr style="border-bottom:1px solid var(--border); transition:background 0.1s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 18px;">
                        <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $task->title }}</div>
                        @if($task->description)<div style="font-size:11px; color:var(--muted); margin-top:2px; font-family:var(--mono); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px;">{{ $task->description }}</div>@endif
                    </td>
                    <td style="padding:12px 18px; font-size:13px; color:var(--muted); font-family:var(--mono);">{{ $task->project->name }}</td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $task->priority === 'urgent' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                               ($task->priority === 'high' ? 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                               ($task->priority === 'medium' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td style="padding:12px 18px; font-size:12px; font-family:var(--mono); {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171; font-weight:500;' : 'color:var(--muted);' }}">
                        {{ $task->due_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td style="padding:12px 18px;">
                        <form method="POST" action="{{ route('employee.tasks.status', $task) }}">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="ptm-select" style="font-size:12px; padding:5px 9px;">
                                @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:48px; text-align:center; color:var(--muted); font-size:13px;">No tasks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
    <div style="margin-top:16px;">{{ $tasks->links() }}</div>
    @endif

</x-employee-layout>
