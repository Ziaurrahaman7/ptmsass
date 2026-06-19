<x-employee-layout :title="$project->name">

    @php
        $myId = auth()->id();
        $priorityStyles = [
            'urgent' => 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);',
            'high'   => 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);',
            'medium' => 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);',
            'low'    => 'color:var(--muted); border-color:var(--border2); background:transparent;',
        ];
        $statusMeta = [
            'todo'        => ['label' => 'To Do',       'color' => '#6b7385'],
            'in_progress' => ['label' => 'In Progress', 'color' => '#22d3ee'],
            'in_review'   => ['label' => 'In Review',   'color' => '#a78bfa'],
            'done'        => ['label' => 'Done',        'color' => '#4ade80'],
        ];
        $groups = [];
        foreach ($sections as $section) {
            $groups[] = ['id' => $section->id, 'name' => $section->name, 'tasks' => $tasks->where('section_id', $section->id)];
        }
        $noSection = $tasks->whereNull('section_id');
        $groups[] = ['id' => null, 'name' => '(No section)', 'tasks' => $noSection];
        $colGrid = 'grid-template-columns:minmax(0,1fr) 150px 175px 150px 130px;';
    @endphp

    <style>
        .al-cell { padding:9px 14px; border-right:1px solid var(--border); display:flex; align-items:center; min-width:0; }
        .al-row:hover { background:var(--surface2); }
        .al-pill { font-size:12px; font-family:var(--font); border:1px solid transparent; border-radius:6px; padding:5px 10px; cursor:pointer; width:100%; -webkit-appearance:none; appearance:none; }
        .al-pill:focus { outline:none; border-color:var(--accent2); }
        .al-tab { font-size:13px; font-weight:500; color:var(--muted); padding:10px 4px; cursor:pointer; border-bottom:2px solid transparent; background:none; border-top:none; border-left:none; border-right:none; font-family:var(--font); }
        .al-tab:hover { color:var(--text); }
        .al-tab.active { color:var(--text); border-bottom-color:var(--accent); }
        .al-avatar { width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .al-badge { font-family:var(--mono); font-size:11px; border-radius:6px; padding:4px 9px; border:1px solid; }
        .al-kcol { background:var(--surface); border:1px solid var(--border); border-radius:12px; }
        .al-kcard { background:var(--surface2); border:1px solid transparent; border-radius:8px; transition:border 0.15s; }
        .al-kcard:hover { border-color:var(--border2); }
        .al-ptrack { height:4px; background:var(--border); border-radius:2px; }
        .al-pfill { height:100%; border-radius:2px; background:var(--accent); }
        [x-cloak]{display:none!important;}
    </style>

    <div x-data="{ tab: 'list' }">

        {{-- Header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px;">
            <div style="display:flex; align-items:center; gap:11px;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--accent2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <div style="font-size:17px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $project->name }}</div>
                    @if($project->description)<div style="font-size:12px; color:var(--muted); margin-top:1px;">{{ $project->description }}</div>@endif
                </div>
            </div>
            <span style="font-size:11px; font-family:var(--mono); padding:5px 10px; border-radius:6px; border:1px solid;
                {{ $project->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                   ($project->status === 'completed' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                   ($project->status === 'on_hold' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                {{ ucfirst(str_replace('_',' ',$project->status)) }}
            </span>
        </div>

        {{-- Tabs --}}
        <div style="display:flex; align-items:center; gap:20px; border-bottom:1px solid var(--border); margin-bottom:16px;">
            <button class="al-tab" :class="{ 'active': tab==='overview' }" @click="tab='overview'">Overview</button>
            <button class="al-tab" :class="{ 'active': tab==='list' }" @click="tab='list'">List</button>
            <button class="al-tab" :class="{ 'active': tab==='board' }" @click="tab='board'">Board</button>
            <button class="al-tab" :class="{ 'active': tab==='dashboard' }" @click="tab='dashboard'">Dashboard</button>
        </div>

        {{-- ============================ LIST TAB ============================ --}}
        <div x-show="tab==='list'">

            {{-- Toolbar (read-only: search only) --}}
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-bottom:12px;">
                <div style="position:relative; display:flex; align-items:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" oninput="filterTasks(this.value)" placeholder="Search tasks..." class="ptm-input" style="font-size:12px; padding:6px 10px 6px 28px; width:180px;">
                </div>
            </div>

            <div class="ptm-card" style="overflow:hidden;">
                {{-- Column header --}}
                <div style="display:grid; {{ $colGrid }} background:var(--surface2); border-bottom:1px solid var(--border);">
                    <div class="al-cell ptm-section-title">Name</div>
                    <div class="al-cell ptm-section-title">Due date</div>
                    <div class="al-cell ptm-section-title">Assignee</div>
                    <div class="al-cell ptm-section-title">Status</div>
                    <div class="al-cell ptm-section-title" style="border-right:none;">Priority</div>
                </div>

                @foreach($groups as $group)
                <div x-data="{ open: true }">
                    {{-- Section header --}}
                    <div style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid var(--border); background:var(--surface);">
                        <svg @click="open=!open" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :style="open ? '' : 'transform:rotate(-90deg)'" style="color:var(--muted); transition:transform 0.15s; cursor:pointer; flex-shrink:0;"><path d="M19 9l-7 7-7-7"/></svg>
                        <span style="font-size:13px; font-weight:600; color:{{ $group['id'] ? 'var(--text)' : 'var(--muted)' }};">{{ $group['name'] }}</span>
                        <span style="font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $group['tasks']->count() }}</span>
                    </div>

                    {{-- Rows --}}
                    <div x-show="open">
                        @forelse($group['tasks'] as $task)
                            @php
                                $isMine = $task->assigned_to === $myId || $task->assignees->contains('id', $myId);
                                $sm = $statusMeta[$task->status] ?? $statusMeta['todo'];
                            @endphp
                            <div class="al-row" data-title="{{ strtolower($task->title) }}" style="display:grid; {{ $colGrid }} border-bottom:1px solid var(--border); transition:background 0.1s;">
                                {{-- Name --}}
                                <div class="al-cell" style="gap:9px;">
                                    <div style="width:15px; height:15px; border-radius:50%; border:1.5px solid {{ $task->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $task->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                                        @if($task->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
                                    </div>
                                    <a href="{{ route('employee.tasks.show', [$slug, $task]) }}" style="font-size:13px; font-weight:500; color:var(--text); text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $task->title }}</a>
                                    @unless($isMine)<span title="Not assigned to you" style="font-size:10px; color:var(--muted); font-family:var(--mono); flex-shrink:0;">read-only</span>@endunless
                                </div>

                                {{-- Due date --}}
                                <div class="al-cell" style="font-size:12px; font-family:var(--mono); {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">
                                    {{ $task->due_date?->format('d M Y') ?? '—' }}
                                </div>

                                {{-- Assignee --}}
                                <div class="al-cell">
                                    @if($task->assignees->count() > 0)
                                        <div style="display:flex; align-items:center; gap:4px;">
                                            @foreach($task->assignees->take(3) as $a)
                                                <div class="al-avatar" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>
                                            @endforeach
                                            @if($task->assignees->count() > 3)<span style="font-size:11px; color:var(--muted); font-family:var(--mono);">+{{ $task->assignees->count()-3 }}</span>@endif
                                        </div>
                                    @else
                                        <span style="font-size:12px; color:var(--muted);">—</span>
                                    @endif
                                </div>

                                {{-- Status (editable only for own tasks) --}}
                                <div class="al-cell">
                                    @if($isMine)
                                        <form method="POST" action="{{ route('employee.tasks.status', [$slug, $task]) }}" style="width:100%;">
                                            @csrf @method('PATCH')
                                            <select name="status" class="al-pill al-status" onchange="applyStatus(this); this.form.submit()">
                                                @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                                    <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span class="al-badge" style="border-color:var(--border2); color:{{ $sm['color'] }};">{{ $sm['label'] }}</span>
                                    @endif
                                </div>

                                {{-- Priority (read-only) --}}
                                <div class="al-cell" style="border-right:none;">
                                    <span class="al-badge" style="{{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
                                </div>
                            </div>
                        @empty
                            <div style="padding:11px 14px 11px 35px; font-size:12px; color:var(--muted); font-family:var(--mono); border-bottom:1px solid var(--border);">No tasks in this section</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ============================ BOARD TAB (read-only) ============================ --}}
        <div x-show="tab==='board'" x-cloak>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                @foreach($statusMeta as $colStatus => $cfg)
                <div class="al-kcol" style="padding:12px;">
                    <div style="display:flex; align-items:center; gap:7px; margin-bottom:12px;">
                        <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
                        <span style="font-size:10px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-family:var(--mono);">{{ $cfg['label'] }}</span>
                        <span style="margin-left:auto; font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $tasks->where('status',$colStatus)->count() }}</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px; min-height:60px;">
                        @foreach($tasks->where('status',$colStatus) as $task)
                        <a href="{{ route('employee.tasks.show', [$slug, $task]) }}" class="al-kcard" style="padding:10px 12px; text-decoration:none; display:block;">
                            <div style="font-size:13px; font-weight:500; color:var(--text); line-height:1.4;">{{ $task->title }}</div>
                            @if($task->section)<div style="font-size:10px; font-family:var(--mono); color:var(--muted); margin-top:5px;">▸ {{ $task->section->name }}</div>@endif
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px;">
                                <span class="al-badge" style="font-size:10px; padding:2px 7px; {{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    @if($task->due_date)<span style="font-size:11px; font-family:var(--mono); {{ $task->due_date->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">{{ $task->due_date->format('d M') }}</span>@endif
                                    @foreach($task->assignees->take(2) as $a)<div class="al-avatar" style="width:20px; height:20px; font-size:9px;" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>@endforeach
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ============================ OVERVIEW TAB ============================ --}}
        <div x-show="tab==='overview'" x-cloak>
            <div class="ptm-card" style="padding:18px 22px; max-width:640px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                    <span style="font-size:13px; font-weight:600; color:var(--text);">Overall Progress</span>
                    <span style="font-size:14px; font-weight:600; font-family:var(--mono); color:#4ade80;">{{ $project->progressPercentage() }}%</span>
                </div>
                <div class="al-ptrack"><div class="al-pfill" style="width:{{ $project->progressPercentage() }}%;"></div></div>
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-top:18px;">
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">TOTAL TASKS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $tasks->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">SECTIONS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $sections->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">IN PROGRESS</div><div style="font-size:20px; font-weight:600; color:#22d3ee;">{{ $tasks->where('status','in_progress')->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">DONE</div><div style="font-size:20px; font-weight:600; color:#4ade80;">{{ $tasks->where('status','done')->count() }}</div></div>
                </div>
                @if($project->due_date)<div style="margin-top:14px; font-size:12px; font-family:var(--mono); {{ $project->due_date->isPast() && $project->status !== 'completed' ? 'color:#f87171;' : 'color:var(--muted);' }}">Due {{ $project->due_date->format('d M Y') }}</div>@endif
            </div>
        </div>

        {{-- ============================ DASHBOARD TAB ============================ --}}
        <div x-show="tab==='dashboard'" x-cloak>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                @foreach($statusMeta as $st => $cfg)
                <div class="ptm-card" style="padding:16px 18px;">
                    <div style="display:flex; align-items:center; gap:7px; margin-bottom:10px;">
                        <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
                        <span style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">{{ $cfg['label'] }}</span>
                    </div>
                    <div style="font-size:28px; font-weight:600; color:var(--text);">{{ $tasks->where('status',$st)->count() }}</div>
                </div>
                @endforeach
            </div>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:12px;">
                @foreach(['urgent'=>'#f87171','high'=>'#fb923c','medium'=>'#fbbf24','low'=>'#6b7385'] as $pr=>$clr)
                <div class="ptm-card" style="padding:16px 18px;">
                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:10px;">{{ ucfirst($pr) }} priority</div>
                    <div style="font-size:28px; font-weight:600; color:{{ $clr }};">{{ $tasks->where('priority',$pr)->count() }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <script>
    function statusStyle(v){
        const m={todo:['#6b7385','rgba(107,115,133,0.14)'],in_progress:['#22d3ee','rgba(34,211,238,0.14)'],in_review:['#a78bfa','rgba(167,139,250,0.14)'],done:['#4ade80','rgba(74,222,128,0.14)']};
        return m[v]||m.todo;
    }
    function applyStatus(sel){ const [c,b]=statusStyle(sel.value); sel.style.color=c; sel.style.background=b; }
    document.querySelectorAll('.al-status').forEach(applyStatus);

    function filterTasks(q){
        q=q.trim().toLowerCase();
        document.querySelectorAll('.al-row').forEach(row=>{
            row.style.display = (!q || row.dataset.title.includes(q)) ? '' : 'none';
        });
    }
    </script>

</x-employee-layout>
