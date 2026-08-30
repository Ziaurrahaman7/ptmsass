<x-company-layout title="My Tasks">

@php
$groups = [
    'overdue'  => ['label' => 'Overdue',   'color' => '#f87171', 'tasks' => $overdue,  'icon' => '⚠'],
    'today'    => ['label' => 'Today',     'color' => '#fbbf24', 'tasks' => $today,    'icon' => '◉'],
    'upcoming' => ['label' => 'Upcoming',  'color' => '#22d3ee', 'tasks' => $upcoming, 'icon' => '→'],
    'done'     => ['label' => 'Completed', 'color' => '#4ade80', 'tasks' => $done,     'icon' => '✓'],
];
@endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
    <div>
        <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">My Tasks</div>
        <div style="font-size:12px; color:var(--muted); margin-top:2px; font-family:var(--mono);">
            {{ $overdue->count() > 0 ? $overdue->count().' overdue · ' : '' }}{{ $today->count() }} today · {{ $upcoming->count() }} upcoming
        </div>
    </div>
    <button onclick="document.getElementById('addTaskModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Task
    </button>
</div>

@foreach($groups as $key => $group)
@if($group['tasks']->count() > 0 || $key === 'today')
<div style="margin-bottom:28px;">
    {{-- Group header --}}
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid var(--border);">
        <span style="font-size:13px; color:{{ $group['color'] }};">{{ $group['icon'] }}</span>
        <span style="font-size:12px; font-weight:600; color:{{ $group['color'] }}; font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em;">{{ $group['label'] }}</span>
        <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">({{ $group['tasks']->count() }})</span>
    </div>

    @if($group['tasks']->isEmpty())
    <div style="padding:20px 0; text-align:center; color:var(--muted); font-size:13px;">
        @if($key === 'today') No tasks due today — you're all caught up! @endif
    </div>
    @else
    <div style="display:flex; flex-direction:column; gap:4px;">
        @foreach($group['tasks'] as $task)
        <div style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:var(--surface); border:1px solid var(--border); border-radius:10px; transition:border-color 0.15s;" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
            {{-- Checkbox --}}
            <button onclick="toggleTaskDone({{ $task->id }}, this)"
                data-done="{{ $task->status === 'done' ? '1' : '0' }}"
                style="width:18px; height:18px; border-radius:5px; border:2px solid {{ $task->status === 'done' ? 'var(--accent)' : 'var(--border2)' }}; background:{{ $task->status === 'done' ? 'rgba(74,222,128,0.2)' : 'transparent' }}; cursor:pointer; flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:all 0.15s;">
                @if($task->status === 'done')
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                @endif
            </button>

            {{-- Title --}}
            <div style="flex:1; min-width:0;">
                <span style="font-size:13px; color:{{ $task->status === 'done' ? 'var(--muted)' : 'var(--text)' }}; {{ $task->status === 'done' ? 'text-decoration:line-through;' : '' }}">{{ $task->title }}</span>
                @if($task->project)
                <span style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-left:8px;">· {{ $task->project->name }}</span>
                @else
                <span style="font-size:11px; color:var(--purple); font-family:var(--mono); margin-left:8px;">· Personal</span>
                @endif
            </div>

            {{-- Priority --}}
            @php $pri = $priorities->firstWhere('slug', $task->priority); @endphp
            @if($pri)
            <span style="font-size:10px; font-family:var(--mono); padding:2px 7px; border-radius:5px; border:1px solid {{ $pri->color }}4d; color:{{ $pri->color }}; background:{{ $pri->color }}14; white-space:nowrap;">{{ $pri->name }}</span>
            @endif

            {{-- Due date --}}
            @if($task->due_date)
            <span style="font-size:11px; font-family:var(--mono); color:{{ $key === 'overdue' ? '#f87171' : ($key === 'today' ? '#fbbf24' : 'var(--muted)') }}; white-space:nowrap;">
                {{ $task->due_date->format('d M') }}
            </span>
            @endif

            {{-- View link --}}
            @if($task->project)
            <a href="{{ route('company.tasks.show', [$slug, $task]) }}" style="color:var(--muted); text-decoration:none; font-size:11px; font-family:var(--mono); white-space:nowrap;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">View →</a>
            @endif

            {{-- Delete (personal tasks only) --}}
            @if(!$task->project)
            <form method="POST" action="{{ route('company.my-tasks.destroy', [$slug, $task]) }}" onsubmit="return confirm('Delete?')" style="display:inline;">
                @csrf @method('DELETE')
                <button style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:11px; font-family:var(--mono);" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">✕</button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif
@endforeach

{{-- Add Task Modal --}}
<div id="addTaskModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:460px;">
        <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:15px; font-weight:600; color:var(--text);">Add Task</span>
            <button onclick="document.getElementById('addTaskModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px;">✕</button>
        </div>
        <form method="POST" action="{{ route('company.my-tasks.store', $slug) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            @csrf
            @if($errors->any())
            <div style="background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.3); border-radius:8px; padding:10px; color:#f87171; font-size:12px;">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
            @endif
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TASK TITLE *</label>
                <input type="text" name="title" required value="{{ old('title') }}" placeholder="What needs to be done?" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DUE DATE</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PRIORITY</label>
                    <select name="priority" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @foreach($priorities as $p)
                        <option value="{{ $p->slug }}" {{ $p->is_default ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">LINK TO PROJECT <span style="color:var(--muted); font-weight:400;">(optional — leave blank for personal task)</span></label>
                <select name="project_id" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    <option value="">Personal (no project)</option>
                    @foreach(\App\Models\Project::where('company_id', auth()->user()->company_id)->orderBy('name')->get() as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">NOTES</label>
                <textarea name="notes" rows="2" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px; resize:vertical;">{{ old('notes') }}</textarea>
            </div>
            <div style="display:flex; gap:10px; padding-top:4px;">
                <button type="submit" class="ptm-btn-primary">Add Task</button>
                <button type="button" onclick="document.getElementById('addTaskModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const myTaskSlug = '{{ $slug }}';
const myTaskCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function toggleTaskDone(taskId, btn) {
    const isDone = btn.dataset.done === '1';
    const newStatus = isDone ? 'todo' : 'done';

    fetch(`/${myTaskSlug}/admin/my-tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': myTaskCsrf },
        body: JSON.stringify({ status: newStatus })
    }).then(r => r.json()).then(d => {
        if (d.success) window.location.reload();
    });
}

@if($errors->any())
document.getElementById('addTaskModal').style.display = 'flex';
@endif
</script>

</x-company-layout>
