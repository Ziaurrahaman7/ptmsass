<x-company-layout title="All Tasks">

    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:22px;">
        <div>
            <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">All Tasks</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Tasks across all projects</div>
        </div>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            {{-- Filters --}}
            <form method="GET" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <select name="project" onchange="this.form.submit()" style="background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:12px; padding:7px 11px;">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" style="background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:12px; padding:7px 11px;">
                    <option value="">All Status</option>
                    @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $v=>$l)
                    <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <select name="priority" onchange="this.form.submit()" style="background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:12px; padding:7px 11px;">
                    <option value="">All Priority</option>
                    @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $v=>$l)
                    <option value="{{ $v }}" {{ request('priority')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @if(request('status') || request('priority') || request('project'))
                <a href="{{ route('company.tasks.index', $slug) }}" style="font-size:12px; color:var(--muted); text-decoration:none;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">✕ Clear</a>
                @endif
            </form>

            <button onclick="document.getElementById('createTaskModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px; white-space:nowrap;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Task
            </button>
        </div>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:var(--surface2);">
                <tr>
                    @foreach(['Task','Project','Assignee','Priority','Status','Due Date',''] as $h)
                    <th style="padding:12px 18px; text-align:left; font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; font-weight:500;">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr style="border-bottom:1px solid var(--border); transition:background 0.1s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 18px;">
                        <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $task->title }}</div>
                        @if($task->description)<div style="font-size:11px; color:var(--muted); margin-top:2px; font-family:var(--mono);">{{ Str::limit($task->description, 50) }}</div>@endif
                    </td>
                    <td style="padding:12px 18px;">
                        <a href="{{ route('company.projects.show', [$slug, $task->project]) }}" style="font-size:13px; color:var(--accent); text-decoration:none;">{{ $task->project->name }}</a>
                    </td>
                    <td style="padding:12px 18px; font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $task->assignee?->name ?? '—' }}</td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $task->priority==='urgent'?'color:#f87171;border-color:rgba(248,113,113,0.3);background:rgba(248,113,113,0.08);':($task->priority==='high'?'color:#fb923c;border-color:rgba(251,146,60,0.3);background:rgba(251,146,60,0.08);':($task->priority==='medium'?'color:#fbbf24;border-color:rgba(251,191,36,0.3);background:rgba(251,191,36,0.08);':'color:var(--muted);border-color:var(--border2);background:transparent;')) }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $task->status==='done'?'color:#4ade80;border-color:rgba(74,222,128,0.3);background:rgba(74,222,128,0.08);':($task->status==='in_progress'?'color:#22d3ee;border-color:rgba(34,211,238,0.3);background:rgba(34,211,238,0.08);':($task->status==='in_review'?'color:#a78bfa;border-color:rgba(167,139,250,0.3);background:rgba(167,139,250,0.08);':'color:var(--muted);border-color:var(--border2);background:transparent;')) }}">
                            {{ ucfirst(str_replace('_',' ',$task->status)) }}
                        </span>
                    </td>
                    <td style="padding:12px 18px; font-size:12px; font-family:var(--mono); {{ $task->due_date?->isPast() && $task->status!=='done' ? 'color:#f87171;' : 'color:var(--muted);' }}">
                        {{ $task->due_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td style="padding:12px 18px;">
                        <form method="POST" action="{{ route('company.tasks.destroy', [$slug, $task]) }}" onsubmit="return confirm('Delete task?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:12px; font-family:var(--mono);" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:48px; text-align:center; color:var(--muted); font-size:13px;">
                    No tasks found.
                    @if(!request()->hasAny(['status','priority','project']))
                    <a href="#" onclick="document.getElementById('createTaskModal').style.display='flex'; return false;" style="color:var(--accent); text-decoration:none;"> Create one →</a>
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
    <div style="margin-top:16px;">{{ $tasks->links() }}</div>
    @endif

    {{-- Create Task Modal --}}
    <div id="createTaskModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:500px;">
            <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:15px; font-weight:600; color:var(--text);">New Task</span>
                <button onclick="document.getElementById('createTaskModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px;">✕</button>
            </div>
            <form method="POST" action="{{ route('company.tasks.store_index', $slug) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @csrf
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PROJECT *</label>
                    <select name="project_id" required style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        <option value="">Select project...</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TITLE *</label>
                    <input type="text" name="title" required style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DESCRIPTION</label>
                    <textarea name="description" rows="2" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px; resize:vertical;"></textarea>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">STATUS</label>
                        <select name="status" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                            <option value="todo">To Do</option><option value="in_progress">In Progress</option><option value="in_review">In Review</option><option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PRIORITY</label>
                        <select name="priority" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ASSIGN TO</label>
                        <select name="assigned_to" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                            <option value="">Unassigned</option>
                            @foreach($members as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DUE DATE</label>
                        <input type="date" name="due_date" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    </div>
                </div>
                <div style="display:flex; gap:10px; padding-top:4px;">
                    <button type="submit" class="ptm-btn-primary">Create Task</button>
                    <button type="button" onclick="document.getElementById('createTaskModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</x-company-layout>
