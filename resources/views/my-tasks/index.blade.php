<x-dynamic-component :component="auth()->user()->isCompanyAdmin() ? 'company-layout' : 'employee-layout'" title="My Tasks">

@php
$emptyCopy = [
    'recently' => 'Nothing newly assigned.',
    'overdue'  => 'Nothing overdue.',
    'today'    => "No tasks due today — you're all caught up!",
    'upcoming' => 'Nothing upcoming.',
    'later'    => 'No unscheduled tasks.',
    'done'     => 'No completed tasks yet.',
];
$groups = [
    'recently' => ['label' => 'Recently assigned', 'color' => '#a78bfa', 'tasks' => $recently, 'icon' => '✦', 'due' => null, 'add' => true, 'list_group' => 'recent'],
    'overdue'  => ['label' => 'Overdue',           'color' => '#f87171', 'tasks' => $overdue,  'icon' => '⚠', 'due' => now()->subDay()->format('Y-m-d'), 'add' => true, 'list_group' => null],
    'today'    => ['label' => 'Today',             'color' => '#fbbf24', 'tasks' => $today,    'icon' => '◉', 'due' => now()->format('Y-m-d'), 'add' => true, 'list_group' => null],
    'upcoming' => ['label' => 'Upcoming',          'color' => '#22d3ee', 'tasks' => $upcoming, 'icon' => '→', 'due' => now()->addDay()->format('Y-m-d'), 'add' => true, 'list_group' => null],
    'later'    => ['label' => 'Later',             'color' => '#6b7385', 'tasks' => $later,    'icon' => '○', 'due' => null, 'add' => true, 'list_group' => 'later'],
    'done'     => ['label' => 'Completed',         'color' => '#4ade80', 'tasks' => $done,     'icon' => '✓', 'due' => null, 'add' => false, 'list_group' => null],
];
$defaultPriority = $priorities->firstWhere('is_default')?->slug ?? $priorities->first()?->slug;
$pillPalette = ['#4a8f6a','#9c6b4a','#c96b98','#4a7fc0','#8b6fc0','#c08348','#3f9a9a','#b39240'];
$isCalendar = $view === 'calendar';
$calListUrl = $ctx['listUrl'] . (str_contains($ctx['listUrl'], '?') ? '&' : '?');
@endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; gap:12px; flex-wrap:wrap;">
    <div>
        <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">My Tasks</div>
        <div style="font-size:12px; color:var(--muted); margin-top:2px; font-family:var(--mono);">
            {{ $recently->count() }} recent · {{ $overdue->count() }} overdue · {{ $today->count() }} today · {{ $upcoming->count() }} upcoming · {{ $later->count() }} later
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <div style="display:flex; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:3px;">
            <a href="{{ $ctx['listUrl'] }}" style="text-decoration:none; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:500; {{ !$isCalendar ? 'background:var(--surface); color:var(--text);' : 'color:var(--muted);' }}">List</a>
            <a href="{{ $ctx['listUrl'] }}?view=calendar" style="text-decoration:none; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:500; {{ $isCalendar ? 'background:var(--surface); color:var(--text);' : 'color:var(--muted);' }}">Calendar</a>
        </div>
        @if($ctx['canCreate'])
        <button onclick="document.getElementById('addTaskModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Task
        </button>
        @endif
    </div>
</div>

@if(!$isCalendar)
@foreach($groups as $key => $group)
@if($key !== 'done' || $group['tasks']->count() > 0)
<div class="mt-group" style="margin-bottom:26px;" data-group="{{ $key }}">
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid var(--border);">
        <span style="font-size:13px; color:{{ $group['color'] }};">{{ $group['icon'] }}</span>
        <span style="font-size:12px; font-weight:600; color:{{ $group['color'] }}; font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em;">{{ $group['label'] }}</span>
        <span class="mt-count" style="font-size:11px; color:var(--muted); font-family:var(--mono);">({{ $group['tasks']->count() }})</span>
    </div>

    <div class="mt-list" data-group="{{ $key }}" style="display:flex; flex-direction:column; gap:4px; min-height:8px;">
        @forelse($group['tasks'] as $task)
        <div class="mt-row" data-id="{{ $task->id }}" onclick="if(!window.__mtDrag) openPanel({{ $task->id }})"
            style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:var(--surface); border:1px solid var(--border); border-radius:10px; cursor:grab; transition:border-color 0.15s;"
            onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='var(--border)'">
            <span class="mt-handle" onclick="event.stopPropagation()" title="Drag" style="color:var(--muted); cursor:grab; font-size:12px; letter-spacing:-1px;">⋮⋮</span>
            <button type="button" onclick="event.stopPropagation(); toggleTaskDone({{ $task->id }}, this)"
                data-done="{{ $task->status === 'done' ? '1' : '0' }}"
                style="width:18px; height:18px; border-radius:5px; border:2px solid {{ $task->status === 'done' ? 'var(--accent)' : 'var(--border2)' }}; background:{{ $task->status === 'done' ? 'rgba(74,222,128,0.2)' : 'transparent' }}; cursor:pointer; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                @if($task->status === 'done')
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                @endif
            </button>
            <div style="flex:1; min-width:0;">
                <span style="font-size:13px; color:{{ $task->status === 'done' ? 'var(--muted)' : 'var(--text)' }}; {{ $task->status === 'done' ? 'text-decoration:line-through;' : '' }}">{{ $task->title }}</span>
                @if($task->project)
                <span style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-left:8px;">· {{ $task->project->name }}</span>
                @else
                <span style="font-size:11px; color:var(--purple, #a78bfa); font-family:var(--mono); margin-left:8px;">· Personal</span>
                @endif
            </div>
            @php $pri = $priorities->firstWhere('slug', $task->priority); @endphp
            @if($pri)
            <span style="font-size:10px; font-family:var(--mono); padding:2px 7px; border-radius:5px; border:1px solid {{ $pri->color }}4d; color:{{ $pri->color }}; background:{{ $pri->color }}14; white-space:nowrap;">{{ $pri->name }}</span>
            @endif
            <input type="date" value="{{ $task->due_date?->format('Y-m-d') }}"
                onclick="event.stopPropagation()"
                onchange="event.stopPropagation(); patchDueDate({{ $task->id }}, this.value)"
                style="background:transparent; border:1px solid transparent; border-radius:6px; color:{{ $key === 'overdue' ? '#f87171' : ($key === 'today' ? '#fbbf24' : 'var(--muted)') }}; font-size:11px; font-family:var(--mono); padding:3px 4px; width:122px; cursor:pointer;">
            @if($ctx['canDeletePersonal'] && !$task->project)
            <form method="POST" action="{{ $ctx['destroy']($task) }}" onsubmit="event.stopPropagation(); return confirm('Delete this personal task?')" style="display:inline;" onclick="event.stopPropagation()">
                @csrf @method('DELETE')
                <button type="submit" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:11px; font-family:var(--mono);">✕</button>
            </form>
            @endif
        </div>
        @empty
        <div class="mt-empty" style="padding:12px 4px; color:var(--muted); font-size:13px;">{{ $emptyCopy[$key] }}</div>
        @endforelse
    </div>

    @if($group['add'] && $ctx['canCreate'])
    <form method="POST" action="{{ $ctx['store'] }}" style="display:flex; align-items:center; gap:10px; margin-top:8px; padding:8px 14px;">
        @csrf
        <span style="color:var(--muted); font-size:16px; line-height:1;">+</span>
        <input type="text" name="title" required maxlength="255" placeholder="Add task in {{ $group['label'] }}…"
            style="flex:1; background:transparent; border:none; color:var(--text); font-size:13px; font-family:var(--font); outline:none; padding:4px 0;">
        @if($group['due'])
        <input type="hidden" name="due_date" value="{{ $group['due'] }}">
        @endif
        @if($group['list_group'])
        <input type="hidden" name="list_group" value="{{ $group['list_group'] }}">
        @endif
        <input type="hidden" name="priority" value="{{ $defaultPriority }}">
    </form>
    @endif
</div>
@endif
@endforeach
@else
<div style="margin:-4px -4px 0;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 4px 14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <div style="display:flex; align-items:center; gap:2px;">
                <a href="{{ $ctx['listUrl'] }}?view=calendar&month={{ $prevMonth }}" class="cal-navbtn" title="Previous month">‹</a>
                <a href="{{ $ctx['listUrl'] }}?view=calendar" class="cal-navbtn" style="width:auto; padding:0 12px; font-size:13px;">Today</a>
                <a href="{{ $ctx['listUrl'] }}?view=calendar&month={{ $nextMonth }}" class="cal-navbtn" title="Next month">›</a>
            </div>
            <span style="font-size:15px; font-weight:600; color:var(--text);">{{ $cursor->format('F Y') }}</span>
        </div>
        <span style="font-size:12px; color:var(--muted);">Drag a task onto a day to reschedule</span>
    </div>
    <div style="overflow-x:auto; border-top:1px solid var(--border); border-left:1px solid var(--border);">
        <div style="display:grid; grid-template-columns:repeat(7, minmax(120px, 1fr));">
            @foreach(['MON','TUE','WED','THU','FRI','SAT','SUN'] as $dow)
            <div style="padding:7px 10px; font-size:10px; color:var(--muted); font-family:var(--mono); letter-spacing:0.06em; border-right:1px solid var(--border); border-bottom:1px solid var(--border);">{{ $dow }}</div>
            @endforeach
        </div>
        <div style="display:grid; grid-template-columns:repeat(7, minmax(120px, 1fr));">
            @foreach($days as $date)
            @php
                $dateStr = $date->toDateString();
                $dayTasks = $tasksByDate[$dateStr] ?? collect();
                $isToday = $dateStr === $todayStr;
                $inMonth = $date->month === $monthNum;
            @endphp
            <div class="mt-cal-day" data-date="{{ $dateStr }}" style="min-height:140px; padding:5px 6px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); {{ $isToday ? 'background:rgba(74,127,192,0.06);' : '' }} {{ $inMonth ? '' : 'opacity:0.45;' }}">
                <div style="margin-bottom:5px;">
                    @if($isToday)
                    <span style="display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:20px; padding:0 5px; border-radius:10px; background:#3b82f6; color:#fff; font-size:12px; font-weight:700;">{{ $date->day }}</span>
                    @else
                    <span style="font-size:12px; font-family:var(--mono); color:var(--muted);">{{ $date->day }}</span>
                    @endif
                </div>
                <div class="mt-cal-list" data-date="{{ $dateStr }}" style="min-height:40px;">
                    @foreach($dayTasks as $t)
                    @php $bg = $pillPalette[(($t->project_id ?? $t->id)) % count($pillPalette)]; @endphp
                    <div class="mt-row" data-id="{{ $t->id }}" onclick="if(!window.__mtDrag) openPanel({{ $t->id }})" title="{{ $t->title }}"
                        style="cursor:grab; display:flex; align-items:center; gap:5px; margin-bottom:3px; padding:3px 5px; border-radius:5px; background:{{ $bg }}; overflow:hidden;">
                        <span style="display:block; font-size:11px; color:#fff; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $t->title }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
<style>
    .cal-navbtn { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:7px; color:var(--muted); text-decoration:none; font-size:16px; }
    .cal-navbtn:hover { background:var(--surface2); color:var(--text); }
</style>
@endif

<div id="addTaskModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:460px;">
        <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:15px; font-weight:600; color:var(--text);">Add Task</span>
            <button onclick="document.getElementById('addTaskModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px;">✕</button>
        </div>
        <form method="POST" action="{{ $ctx['store'] }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
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
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">LINK TO PROJECT <span style="font-weight:400;">(optional)</span></label>
                <select name="project_id" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    <option value="">Personal (no project)</option>
                    @foreach($projects as $p)
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const mtCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const mtStatusUrl = @json($ctx['status'](0));
const mtMoveUrl = @json($ctx['move'](0));
const mtInlineUrl = @json($ctx['inline'](0));
function mtUrl(tpl, id){ return tpl.replace(/\/0(\/|$)/, '/' + id + '$1').replace(/\/0$/, '/' + id); }

function toggleTaskDone(taskId, btn) {
    const next = btn.dataset.done === '1' ? 'todo' : 'done';
    fetch(mtUrl(mtStatusUrl, taskId), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': mtCsrf, 'Accept': 'application/json' },
        body: JSON.stringify({ status: next })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function patchDueDate(taskId, value) {
    fetch(mtUrl(mtInlineUrl, taskId), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': mtCsrf, 'Accept': 'application/json' },
        body: JSON.stringify({ due_date: value || null, list_group: value ? null : 'later' })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function moveTask(taskId, group, dueDate) {
    const body = { group };
    if (dueDate) body.due_date = dueDate;
    return fetch(mtUrl(mtMoveUrl, taskId), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': mtCsrf, 'Accept': 'application/json' },
        body: JSON.stringify(body)
    });
}

if (typeof Sortable !== 'undefined') {
    document.querySelectorAll('.mt-list').forEach(list => {
        new Sortable(list, {
            group: 'my-tasks',
            handle: '.mt-handle',
            animation: 150,
            ghostClass: 'mt-ghost',
            onStart() { window.__mtDrag = true; },
            onEnd(evt) {
                setTimeout(() => { window.__mtDrag = false; }, 50);
                const id = evt.item.dataset.id;
                const group = evt.to.dataset.group;
                if (!id || !group || group === 'done') return;
                moveTask(id, group).then(r => r.json()).then(d => { if (d.success) location.reload(); });
            }
        });
    });
    document.querySelectorAll('.mt-cal-list').forEach(list => {
        new Sortable(list, {
            group: 'my-tasks-cal',
            animation: 150,
            onStart() { window.__mtDrag = true; },
            onEnd(evt) {
                setTimeout(() => { window.__mtDrag = false; }, 50);
                const id = evt.item.dataset.id;
                const date = evt.to.dataset.date;
                if (!id || !date) return;
                moveTask(id, 'calendar', date).then(r => r.json()).then(d => { if (d.success) location.reload(); });
            }
        });
    });
}
@if($errors->any())
document.getElementById('addTaskModal').style.display = 'flex';
@endif
</script>
<style>
    .mt-ghost { opacity: 0.45; }
    .mt-list { padding-bottom: 4px; }
</style>

</x-dynamic-component>
