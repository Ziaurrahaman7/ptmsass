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
$viewUrl = fn ($v) => $ctx['listUrl'] . (str_contains($ctx['listUrl'], '?') ? '&' : '?') . 'view=' . $v;
$statusMeta = [
    'todo'        => ['label' => 'To Do',       'color' => '#6b7385'],
    'in_progress' => ['label' => 'In Progress', 'color' => '#22d3ee'],
    'in_review'   => ['label' => 'In Review',   'color' => '#a78bfa'],
    'done'        => ['label' => 'Done',        'color' => '#4ade80'],
];
@endphp

<div style="margin-bottom:4px;">
    <div style="font-size:20px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">My Tasks</div>
</div>

<div class="mt-tabs">
    @foreach(['list'=>'List','board'=>'Board','calendar'=>'Calendar','dashboard'=>'Dashboard','files'=>'Files'] as $key => $label)
    <a href="{{ $viewUrl($key) }}" class="mt-tab {{ $view === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if(in_array($view, ['list','board','calendar'], true) && $ctx['canCreate'])
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
    <button onclick="document.getElementById('addTaskModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add task
    </button>
</div>
@endif

@if($view === 'list')
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
@elseif($view === 'calendar')
@php
    $calScale = $calScale ?? 'month';
    $calLabel = $calLabel ?? $cursor->format('F Y');
    $prevKey = $prevKey ?? $prevMonth;
    $nextKey = $nextKey ?? $nextMonth;
    $todayKey = $todayKey ?? now()->format('Y-m');
    $weekStartOfCursor = $cursor->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
    $monthOfCursor = $cursor->format('Y-m');
    $mtCalUrl = function (?string $key = null, ?string $scale = null) use ($ctx, $calScale, $weekStartOfCursor, $monthOfCursor) {
        $scale = $scale ?? $calScale;
        $q = ['view' => 'calendar', 'scale' => $scale];
        if ($scale === 'week') {
            $q['week'] = $key ?? $weekStartOfCursor;
        } else {
            $q['month'] = $key ?? $monthOfCursor;
        }
        return $ctx['listUrl'] . '?' . http_build_query($q);
    };
@endphp
<div style="margin:-4px -4px 0;">
    <div class="mt-cal-toolbar" style="display:flex; align-items:center; justify-content:space-between; padding:6px 4px 14px; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:2px;">
                <a href="{{ $mtCalUrl($prevKey) }}" class="cal-navbtn" title="{{ $calScale === 'week' ? 'Previous week' : 'Previous month' }}">‹</a>
                <a href="{{ $mtCalUrl($todayKey) }}" class="cal-navbtn" style="width:auto; padding:0 12px; font-size:13px;">Today</a>
                <a href="{{ $mtCalUrl($nextKey) }}" class="cal-navbtn" title="{{ $calScale === 'week' ? 'Next week' : 'Next month' }}">›</a>
            </div>
            <span style="font-size:15px; font-weight:600; color:var(--text);">{{ $calLabel }}</span>
            <div class="cal-scale">
                <a href="{{ $mtCalUrl($monthOfCursor, 'month') }}" class="{{ $calScale === 'month' ? 'active' : '' }}">Month</a>
                <a href="{{ $mtCalUrl($weekStartOfCursor, 'week') }}" class="{{ $calScale === 'week' ? 'active' : '' }}">Week</a>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:2px;">
            <div style="position:relative;">
                <button type="button" class="tb-btn" id="mtCalFilterBtn" onclick="mtCalToggle(event,'mtCalFilter')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter <span id="mtCalFilterCount" style="display:none; color:var(--accent2); font-family:var(--mono);"></span>
                </button>
                <div id="mtCalFilter" class="tb-menu" style="right:0; left:auto; width:380px; max-width:92vw; padding:18px 20px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                        <span style="font-size:16px; font-weight:600; color:var(--text);">Filters</span>
                        <button type="button" onclick="mtCalClear()" style="background:none; border:none; color:var(--muted); font-size:13px; cursor:pointer; font-family:var(--font);">Clear</button>
                    </div>
                    <div class="tb-mlabel">Quick filters</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                        <button type="button" class="qf-pill" data-qf="incomplete" onclick="mtCalQuick('incomplete')">Incomplete tasks</button>
                        <button type="button" class="qf-pill" data-qf="completed" onclick="mtCalQuick('completed')">Completed tasks</button>
                        <button type="button" class="qf-pill" data-qf="personal" onclick="mtCalQuick('personal')">Personal</button>
                        <button type="button" class="qf-pill" data-qf="overdue" onclick="mtCalQuick('overdue')">Overdue</button>
                    </div>
                    <div style="margin-top:14px;">
                        <div class="tb-mlabel">Project</div>
                        <div style="display:flex; flex-direction:column; gap:6px; margin-top:8px; max-height:140px; overflow-y:auto;">
                            <label class="fg-opt"><input type="checkbox" onchange="mtCalSet('project','0',this.checked)"> Personal</label>
                            @foreach($projects as $p)
                            <label class="fg-opt"><input type="checkbox" onchange="mtCalSet('project','{{ $p->id }}',this.checked)"> {{ $p->name }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div style="margin-top:14px;">
                        <div class="tb-mlabel">Priority</div>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                            @foreach($priorities as $p)
                            <label class="fg-opt"><input type="checkbox" onchange="mtCalSet('priority','{{ $p->slug }}',this.checked)"> {{ $p->name }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div style="position:relative;">
                <button type="button" class="tb-btn" onclick="mtCalToggle(event,'mtCalOptions')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="8" x2="20" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="10" y1="16" x2="14" y2="16"/></svg>
                    Options
                </button>
                <div id="mtCalOptions" class="tb-menu" style="right:0; left:auto; width:220px; padding:10px;">
                    <label class="tb-opt"><input type="checkbox" checked onchange="mtCalWeekends(this.checked)"> Show weekends</label>
                    <label class="tb-opt"><input type="checkbox" onchange="MTF.hideDone=this.checked; mtCalApply()"> Hide completed</label>
                </div>
            </div>
            <div style="width:1px; height:20px; background:var(--border2); margin:0 6px;"></div>
            <div style="position:relative; display:flex; align-items:center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" placeholder="Search" oninput="MTF.q=this.value.trim().toLowerCase(); mtCalApply()" autocomplete="off"
                    style="background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:12px; padding:6px 10px 6px 28px; width:150px;">
            </div>
        </div>
    </div>
    <div style="overflow-x:auto; border-top:1px solid var(--border); border-left:1px solid var(--border);">
        <div class="mt-cal-grid {{ $calScale === 'week' ? 'is-week' : '' }}" id="mtCalHead">
            @foreach(['MON','TUE','WED','THU','FRI','SAT','SUN'] as $i => $dow)
            <div class="{{ $i >= 5 ? 'mt-cal-weekend' : '' }}" style="padding:7px 10px; font-size:10px; color:var(--muted); font-family:var(--mono); letter-spacing:0.06em; border-right:1px solid var(--border); border-bottom:1px solid var(--border);">{{ $dow }}</div>
            @endforeach
        </div>
        <div class="mt-cal-grid {{ $calScale === 'week' ? 'is-week' : '' }}" id="mtCalBody">
            @foreach($days as $date)
            @php
                $dateStr = $date->toDateString();
                $dayTasks = $tasksByDate[$dateStr] ?? collect();
                $isToday = $dateStr === $todayStr;
                $inMonth = $calScale === 'week' || $date->month === $monthNum;
            @endphp
            <div class="mt-cal-day {{ $date->isWeekend() ? 'mt-cal-weekend' : '' }}" data-date="{{ $dateStr }}" style="min-height:{{ $calScale === 'week' ? '420px' : '140px' }}; padding:5px 6px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); display:flex; flex-direction:column; {{ $isToday ? 'background:rgba(74,127,192,0.06);' : '' }} {{ $inMonth ? '' : 'opacity:0.45;' }}">
                <div style="margin-bottom:5px; display:flex; align-items:center; justify-content:space-between; gap:6px;">
                    @if($isToday)
                    <span style="display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:20px; padding:0 5px; border-radius:10px; background:#3b82f6; color:#fff; font-size:12px; font-weight:700;">{{ $calScale === 'week' ? $date->format('M j') : $date->day }}</span>
                    @else
                    <span style="font-size:12px; font-family:var(--mono); color:var(--muted);">{{ $calScale === 'week' ? $date->format('M j') : $date->day }}</span>
                    @endif
                </div>
                <div class="mt-cal-list" data-date="{{ $dateStr }}" style="min-height:40px; flex:1;">
                    @foreach($dayTasks as $t)
                    @php $bg = $pillPalette[(($t->project_id ?? $t->id)) % count($pillPalette)]; @endphp
                    <div class="mt-row mt-cal-pill" data-id="{{ $t->id }}"
                        data-title="{{ strtolower($t->title) }}"
                        data-status="{{ $t->status }}"
                        data-priority="{{ $t->priority }}"
                        data-due="{{ $t->due_date?->format('Y-m-d') }}"
                        data-project="{{ $t->project_id ?: '0' }}"
                        onclick="if(!window.__mtDrag) openPanel({{ $t->id }})" title="{{ $t->title }}"
                        style="cursor:grab; display:flex; align-items:center; gap:5px; margin-bottom:3px; padding:3px 5px; border-radius:5px; background:{{ $bg }}; overflow:hidden;">
                        <span style="display:block; font-size:11px; color:#fff; font-weight:500; {{ $calScale === 'week' ? 'white-space:normal; line-height:1.35;' : 'white-space:nowrap; overflow:hidden; text-overflow:ellipsis;' }}">{{ $t->title }}</span>
                    </div>
                    @endforeach
                </div>
                @if($calScale === 'week' && $ctx['canCreate'])
                <form method="POST" action="{{ $ctx['store'] }}" onclick="event.stopPropagation()" style="margin-top:6px;">
                    @csrf
                    <input type="text" name="title" required maxlength="255" placeholder="+ Add task"
                        style="width:100%; background:transparent; border:none; color:var(--text); font-size:12px; font-family:var(--font); outline:none; padding:4px 2px;">
                    <input type="hidden" name="due_date" value="{{ $dateStr }}">
                    <input type="hidden" name="priority" value="{{ $defaultPriority }}">
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
<style>
    .cal-navbtn { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:7px; color:var(--muted); text-decoration:none; font-size:16px; }
    .cal-navbtn:hover { background:var(--surface2); color:var(--text); }
    .cal-scale { display:inline-flex; border:1px solid var(--border2); border-radius:8px; overflow:hidden; }
    .cal-scale a { padding:5px 12px; font-size:12px; color:var(--muted); text-decoration:none; font-family:var(--font); }
    .cal-scale a:hover { color:var(--text); background:var(--surface2); }
    .cal-scale a.active { background:var(--surface2); color:var(--text); font-weight:600; }
    .mt-cal-grid { display:grid; grid-template-columns:repeat(7, minmax(120px, 1fr)); }
    .mt-cal-grid.hide-weekends { grid-template-columns:repeat(5, minmax(120px, 1fr)); }
    .mt-cal-grid.hide-weekends .mt-cal-weekend { display:none; }
    .tb-btn { display:flex; align-items:center; gap:6px; background:none; border:none; color:var(--muted); font-size:13px; font-family:var(--font); cursor:pointer; padding:6px 10px; border-radius:7px; }
    .tb-btn:hover, .tb-btn.active { color:var(--text); background:var(--surface2); }
    .tb-menu { display:none; position:absolute; top:calc(100% + 6px); right:0; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.4); z-index:60; padding:8px; text-align:left; }
    .tb-menu.show { display:block; }
    .tb-mlabel { font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; padding:2px 0 5px; }
    .tb-opt { display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:6px; cursor:pointer; font-size:13px; color:var(--text); }
    .qf-pill { display:inline-flex; align-items:center; gap:7px; padding:7px 12px; border-radius:20px; border:1px solid var(--border2); background:transparent; color:var(--text); font-size:13px; font-family:var(--font); cursor:pointer; }
    .qf-pill.active { background:rgba(34,211,238,0.12); border-color:rgba(34,211,238,0.5); color:var(--accent2); }
    .fg-opt { display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; }
</style>

@elseif($view === 'board')
@php $boardTasks = $boardTasks ?? collect(); @endphp
<div style="display:grid; grid-template-columns:repeat(4,minmax(200px,1fr)); gap:12px;">
    @foreach($statusMeta as $colStatus => $cfg)
    <div class="ptm-card" style="padding:12px;">
        <div style="display:flex; align-items:center; gap:7px; margin-bottom:12px;">
            <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
            <span style="font-size:10px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-family:var(--mono);">{{ $cfg['label'] }}</span>
            <span style="margin-left:auto; font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $boardTasks->where('status', $colStatus)->count() }}</span>
        </div>
        <div class="mt-board-col" data-status="{{ $colStatus }}" style="display:flex; flex-direction:column; gap:8px; min-height:80px;">
            @foreach($boardTasks->where('status', $colStatus) as $task)
            <div class="mt-board-card" data-id="{{ $task->id }}" onclick="if(!window.__mtDrag) openPanel({{ $task->id }})"
                style="padding:10px 12px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; cursor:grab;">
                <div style="font-size:13px; font-weight:500; color:var(--text); line-height:1.4;">{{ $task->title }}</div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px; gap:8px;">
                    @if($task->project)
                    <span style="font-size:11px; color:var(--muted); font-family:var(--mono); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $task->project->name }}</span>
                    @else
                    <span style="font-size:11px; color:#a78bfa; font-family:var(--mono);">Personal</span>
                    @endif
                    @if($task->due_date)
                    <span style="font-size:11px; font-family:var(--mono); {{ $task->due_date->lt(now()->startOfDay()) && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">{{ $task->due_date->format('d M') }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@elseif($view === 'dashboard')
@php
    $dash = $dashboard ?? ['open'=>0,'overdue'=>0,'today'=>0,'done'=>0,'statuses'=>collect(),'projects'=>collect()];
    $maxStatus = max(1, (int) collect($dash['statuses'])->max());
@endphp
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px;">
    @foreach([['Open',$dash['open'],'#22d3ee'],['Overdue',$dash['overdue'],'#f87171'],['Due today',$dash['today'],'#fbbf24'],['Completed',$dash['done'],'#4ade80']] as $card)
    <div class="ptm-card" style="padding:16px 18px;">
        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">{{ $card[0] }}</div>
        <div style="font-size:28px; font-weight:600; color:{{ $card[2] }};">{{ $card[1] }}</div>
    </div>
    @endforeach
</div>
<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
    <div class="ptm-card" style="padding:18px 20px;">
        <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:14px;">By status</div>
        @foreach($statusMeta as $st => $cfg)
        @php $n = (int) ($dash['statuses'][$st] ?? 0); @endphp
        <div style="margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--muted); margin-bottom:4px;">
                <span>{{ $cfg['label'] }}</span><span style="font-family:var(--mono);">{{ $n }}</span>
            </div>
            <div style="height:6px; background:var(--surface2); border-radius:3px; overflow:hidden;">
                <div style="height:100%; width:{{ (int) round($n / $maxStatus * 100) }}%; background:{{ $cfg['color'] }};"></div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="ptm-card" style="padding:18px 20px;">
        <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:14px;">Open by project</div>
        @forelse($projects->filter(fn ($p) => ($dash['projects'][$p->id] ?? 0) > 0) as $p)
        <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border); font-size:13px;">
            <span style="color:var(--text);">{{ $p->name }}</span>
            <span style="font-family:var(--mono); color:var(--muted);">{{ $dash['projects'][$p->id] }}</span>
        </div>
        @empty
        <div style="color:var(--muted); font-size:13px;">No project tasks yet.</div>
        @endforelse
        @php $personalOpen = ($dash['open'] ?? 0) - collect($dash['projects'])->sum(); @endphp
        @if($personalOpen > 0)
        <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13px;">
            <span style="color:#a78bfa;">Personal</span>
            <span style="font-family:var(--mono); color:var(--muted);">{{ $personalOpen }}</span>
        </div>
        @endif
    </div>
</div>

@elseif($view === 'files')
@if(($files ?? collect())->isEmpty())
<div class="ptm-card" style="padding:48px 20px; text-align:center; color:var(--muted); font-size:13px;">No files on your tasks yet. Open a task and upload an attachment.</div>
@else
<div class="ptm-card" style="overflow:hidden;">
    <div style="display:grid; grid-template-columns:1fr 180px 140px 120px; padding:10px 18px; background:var(--surface2); font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">
        <div>File</div><div>Task</div><div>Uploaded</div><div>Size</div>
    </div>
    @foreach($files as $file)
    <div style="display:grid; grid-template-columns:1fr 180px 140px 120px; padding:12px 18px; border-top:1px solid var(--border); align-items:center; gap:10px;">
        <a href="{{ \Illuminate\Support\Facades\Storage::url($file->file_path) }}" target="_blank" style="font-size:13px; color:var(--text); text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $file->file_name }}</a>
        <button type="button" onclick="openPanel({{ $file->task_id }})" style="background:none; border:none; text-align:left; font-size:12px; color:var(--muted); cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-family:var(--font);">{{ $file->task?->title ?? 'Task' }}</button>
        <span style="font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $file->created_at?->format('d M Y') }}</span>
        <span style="font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $file->file_size ? number_format($file->file_size / 1024, 1).' KB' : '—' }}</span>
    </div>
    @endforeach
</div>
@endif
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
    document.querySelectorAll('.mt-board-col').forEach(col => {
        new Sortable(col, {
            group: 'my-tasks-board',
            animation: 150,
            onStart() { window.__mtDrag = true; },
            onEnd(evt) {
                setTimeout(() => { window.__mtDrag = false; }, 50);
                const id = evt.item.dataset.id;
                const status = evt.to.dataset.status;
                if (!id || !status) return;
                fetch(mtUrl(mtStatusUrl, id), {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': mtCsrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ status })
                }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
            }
        });
    });
}
@if($errors->any())
document.getElementById('addTaskModal').style.display = 'flex';
@endif

const MTF = { q:'', qf:new Set(), project:new Set(), priority:new Set(), hideDone:false };
function mtCalToggle(e, id){
    e.stopPropagation();
    const m=document.getElementById(id); const open=m.classList.contains('show');
    document.querySelectorAll('.mt-cal-toolbar .tb-menu').forEach(x=>x.classList.remove('show'));
    if(!open) m.classList.add('show');
}
document.addEventListener('click', function(e){
    if(!e.target.closest('.mt-cal-toolbar')) document.querySelectorAll('.mt-cal-toolbar .tb-menu').forEach(x=>x.classList.remove('show'));
});
function mtCalQuick(key){
    if(MTF.qf.has(key)) MTF.qf.delete(key);
    else {
        if(key==='incomplete') MTF.qf.delete('completed');
        if(key==='completed') MTF.qf.delete('incomplete');
        MTF.qf.add(key);
    }
    document.querySelectorAll('#mtCalFilter .qf-pill').forEach(p=>p.classList.toggle('active', MTF.qf.has(p.dataset.qf)));
    mtCalApply();
}
function mtCalSet(type, val, on){
    const set = MTF[type];
    if(on) set.add(val); else set.delete(val);
    mtCalApply();
}
function mtCalClear(){
    MTF.qf.clear(); MTF.project.clear(); MTF.priority.clear(); MTF.q='';
    document.querySelectorAll('#mtCalFilter .qf-pill').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('#mtCalFilter input[type=checkbox]').forEach(c=>c.checked=false);
    mtCalApply();
}
function mtCalWeekends(on){
    document.getElementById('mtCalHead')?.classList.toggle('hide-weekends', !on);
    document.getElementById('mtCalBody')?.classList.toggle('hide-weekends', !on);
}
function mtCalApply(){
    const today = new Date(); const td = today.getFullYear()+'-'+String(today.getMonth()+1).padStart(2,'0')+'-'+String(today.getDate()).padStart(2,'0');
    document.querySelectorAll('.mt-cal-pill').forEach(el=>{
        let vis = true;
        if(MTF.q && !(el.dataset.title||'').includes(MTF.q)) vis=false;
        if(MTF.hideDone && el.dataset.status==='done') vis=false;
        if(MTF.qf.has('incomplete') && el.dataset.status==='done') vis=false;
        if(MTF.qf.has('completed') && el.dataset.status!=='done') vis=false;
        if(MTF.qf.has('personal') && el.dataset.project!=='0') vis=false;
        if(MTF.qf.has('overdue') && !(el.dataset.due && el.dataset.due < td && el.dataset.status!=='done')) vis=false;
        if(MTF.project.size && !MTF.project.has(el.dataset.project)) vis=false;
        if(MTF.priority.size && !MTF.priority.has(el.dataset.priority)) vis=false;
        el.style.display = vis ? '' : 'none';
    });
    const n = MTF.qf.size + MTF.project.size + MTF.priority.size + (MTF.hideDone?1:0);
    const c=document.getElementById('mtCalFilterCount'), b=document.getElementById('mtCalFilterBtn');
    if(c && b){ if(n){ c.textContent=n; c.style.display='inline'; b.classList.add('active'); } else { c.style.display='none'; b.classList.remove('active'); } }
}
</script>
<style>
    .mt-ghost { opacity: 0.45; }
    .mt-list { padding-bottom: 4px; }
    .mt-tabs { display:flex; align-items:center; gap:22px; border-bottom:1px solid var(--border); margin:10px 0 16px; }
    .mt-tab { font-size:14px; font-weight:500; color:var(--muted); text-decoration:none; padding:10px 2px 11px; border-bottom:2px solid transparent; }
    .mt-tab:hover { color:var(--text); }
    .mt-tab.active { color:#60a5fa; border-bottom-color:#3b82f6; }
</style>

</x-dynamic-component>
