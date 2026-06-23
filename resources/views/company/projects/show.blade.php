<x-company-layout :title="$project->name">

    @php
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
        // Ordered groups: each section, then a "(No section)" bucket.
        $groups = [];
        foreach ($sections as $section) {
            $groups[] = ['id' => $section->id, 'name' => $section->name, 'tasks' => $tasks->where('section_id', $section->id)];
        }
        $noSection = $tasks->whereNull('section_id');
        $groups[] = ['id' => null, 'name' => '(No section)', 'tasks' => $noSection];
        $colGrid = 'grid-template-columns:minmax(0,1fr) 150px 175px 150px 130px 40px;';
    @endphp

    <style>
        .al-cell { padding:9px 14px; border-right:1px solid var(--border); display:flex; align-items:center; min-width:0; }
        .al-row:hover { background:var(--surface2); }
        .al-name-input { background:transparent; border:1px solid transparent; border-radius:6px; color:var(--text); font-size:13px; font-weight:500; padding:5px 8px; width:100%; font-family:var(--font); }
        .al-name-input:hover { background:rgba(255,255,255,0.04); }
        .al-name-input:focus { outline:none; background:var(--surface2); border-color:var(--accent2); }
        .al-pill { font-size:12px; font-family:var(--font); border:1px solid transparent; border-radius:6px; padding:5px 10px; cursor:pointer; width:100%; -webkit-appearance:none; appearance:none; }
        .al-pill:focus { outline:none; border-color:var(--accent2); }
        .al-date { background:transparent; border:1px solid transparent; border-radius:6px; color:var(--muted); font-size:12px; font-family:var(--mono); padding:5px 6px; width:100%; cursor:pointer; }
        .al-date:hover { background:rgba(255,255,255,0.04); }
        .al-date:focus { outline:none; background:var(--surface2); border-color:var(--accent2); }
        .al-date::-webkit-calendar-picker-indicator { filter:invert(0.6); cursor:pointer; }
        .al-tab { font-size:13px; font-weight:500; color:var(--muted); padding:10px 4px; cursor:pointer; border-bottom:2px solid transparent; background:none; border-top:none; border-left:none; border-right:none; font-family:var(--font); }
        .al-tab:hover { color:var(--text); }
        .al-tab.active { color:var(--text); border-bottom-color:var(--accent); }
        .al-tool { display:flex; align-items:center; gap:6px; background:none; border:none; color:var(--muted); font-size:12px; font-family:var(--font); cursor:pointer; padding:6px 8px; border-radius:6px; }
        .al-tool:hover { color:var(--text); background:var(--surface2); }
        .al-avatar { width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .al-addrow input { background:transparent; border:none; color:var(--text); font-size:13px; padding:7px 8px; width:100%; font-family:var(--font); }
        .al-addrow input:focus { outline:none; }
    </style>

    <div x-data="{ tab: 'list' }">

        {{-- Header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px;">
            <div style="display:flex; align-items:center; gap:11px;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <div style="font-size:17px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $project->name }}</div>
                    @if($project->description)<div style="font-size:12px; color:var(--muted); margin-top:1px;">{{ $project->description }}</div>@endif
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:11px; font-family:var(--mono); padding:5px 10px; border-radius:6px; border:1px solid;
                    {{ $project->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                       ($project->status === 'completed' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                       ($project->status === 'on_hold' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                    {{ ucfirst(str_replace('_',' ',$project->status)) }}
                </span>
                <a href="{{ route('company.projects.edit', [$slug, $project]) }}" class="ptm-btn-ghost" style="text-decoration:none; font-size:12px; padding:6px 14px;">Edit Project</a>
            </div>
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

            {{-- Toolbar --}}
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <button onclick="focusFirstAdd()" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px; font-size:13px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add task
                    </button>
                    <form method="POST" action="{{ route('company.sections.store', [$slug, $project]) }}" style="display:flex; align-items:center; gap:6px;">
                        @csrf
                        <input type="text" name="name" placeholder="Add section..." required class="ptm-input" style="font-size:12px; padding:7px 11px; width:170px;">
                        <button type="submit" class="ptm-btn-ghost" style="display:flex; align-items:center; gap:6px; font-size:12px; white-space:nowrap;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add section
                        </button>
                    </form>
                </div>
                <div style="display:flex; align-items:center; gap:2px;">
                    <div style="position:relative; display:flex; align-items:center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" oninput="filterTasks(this.value)" placeholder="Search tasks..." class="ptm-input" style="font-size:12px; padding:6px 10px 6px 28px; width:180px;">
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="ptm-card" style="overflow:visible;">
                {{-- Column header --}}
                <div style="display:grid; {{ $colGrid }} background:var(--surface2); border-bottom:1px solid var(--border); border-radius:12px 12px 0 0;">
                    <div class="al-cell ptm-section-title">Name</div>
                    <div class="al-cell ptm-section-title">Due date</div>
                    <div class="al-cell ptm-section-title">Assignee</div>
                    <div class="al-cell ptm-section-title">Status</div>
                    <div class="al-cell ptm-section-title">Priority</div>
                    <div class="al-cell" style="border-right:none; justify-content:center;">
                        <span style="color:var(--muted); font-size:14px;">+</span>
                    </div>
                </div>

                @foreach($groups as $group)
                <div x-data="{ open: true }" data-section-block>
                    {{-- Section header --}}
                    <div style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid var(--border); background:var(--surface);">
                        <svg @click="open=!open" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :style="open ? '' : 'transform:rotate(-90deg)'" style="color:var(--muted); transition:transform 0.15s; cursor:pointer; flex-shrink:0;"><path d="M19 9l-7 7-7-7"/></svg>

                        @if($group['id'])
                            <span id="secname-{{ $group['id'] }}" style="font-size:13px; font-weight:600; color:var(--text); cursor:pointer;" ondblclick="toggleSecRename({{ $group['id'] }})">{{ $group['name'] }}</span>
                            <form method="POST" action="{{ route('company.sections.update', [$slug, $group['id']]) }}" id="secform-{{ $group['id'] }}" style="display:none; gap:6px; align-items:center;">
                                @csrf @method('PATCH')
                                <input type="text" name="name" value="{{ $group['name'] }}" class="ptm-input" style="font-size:12px; padding:4px 8px; width:200px;" required>
                                <button type="submit" class="ptm-btn-primary" style="padding:4px 10px; font-size:11px;">Save</button>
                                <button type="button" onclick="toggleSecRename({{ $group['id'] }})" class="ptm-btn-ghost" style="padding:4px 9px; font-size:11px;">✕</button>
                            </form>
                        @else
                            <span style="font-size:13px; font-weight:600; color:var(--muted);">{{ $group['name'] }}</span>
                        @endif

                        <span style="font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $group['tasks']->count() }}</span>

                        @if($group['id'])
                        <div style="margin-left:auto; display:flex; align-items:center; gap:2px;">
                            <button type="button" onclick="toggleSecRename({{ $group['id'] }})" title="Rename section" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('company.sections.destroy', [$slug, $group['id']]) }}" onsubmit="return confirm('Delete section “{{ addslashes($group['name']) }}”? Its tasks move to (No section).')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete section" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>

                    {{-- Task rows --}}
                    <div x-show="open">
                        @foreach($group['tasks'] as $task)
                            @php $sm = $statusMeta[$task->status] ?? $statusMeta['todo']; @endphp
                            <div class="al-row" id="row-{{ $task->id }}" data-title="{{ strtolower($task->title) }}" style="display:grid; {{ $colGrid }} border-bottom:1px solid var(--border); transition:background 0.1s;">
                                {{-- Name --}}
                                <div class="al-cell" style="gap:9px;">
                                    <div id="done-{{ $task->id }}" onclick="cycleDone({{ $task->id }}, '{{ $task->status }}')" title="Toggle done" style="width:15px; height:15px; border-radius:50%; border:1.5px solid {{ $task->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $task->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                                        @if($task->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
                                    </div>
                                    <input class="al-name-input" value="{{ $task->title }}" onchange="patchField({{ $task->id }}, 'title', this.value)" onkeydown="if(event.key==='Enter'){this.blur();}">
                                    @if(($task->subtasks_count ?? 0) > 0)
                                    <span title="{{ $task->subtasks_count }} subtask(s)" style="display:flex; align-items:center; gap:3px; flex-shrink:0; color:var(--muted); font-size:11px; font-family:var(--mono);">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v12"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 01-9 9"/></svg>
                                        {{ $task->subtasks_count }}
                                    </span>
                                    @endif
                                    <button onclick="openPanel({{ $task->id }})" title="Open details" style="flex-shrink:0; color:var(--muted); background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:3px; padding:3px 5px; border-radius:6px;" onmouseover="this.style.color='var(--accent2)'; this.style.background='var(--surface2)'" onmouseout="this.style.color='var(--muted)'; this.style.background='transparent'">
                                        @if(($task->comments_count ?? 0) > 0)<span style="font-size:11px; font-family:var(--mono);">{{ $task->comments_count }}</span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>@endif
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M14 10l7-7M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/></svg>
                                    </button>
                                </div>

                                {{-- Due date --}}
                                <div class="al-cell">
                                    <input type="date" id="due-{{ $task->id }}" class="al-date" value="{{ $task->due_date?->format('Y-m-d') }}"
                                        style="{{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : '' }}"
                                        onchange="patchField({{ $task->id }}, 'due_date', this.value).then(d => recolorDue({{ $task->id }}, d))">
                                </div>

                                {{-- Assignee --}}
                                <div class="al-cell" x-data="{ open:false }" style="position:relative; overflow:visible;">
                                    <div @click="open=!open" id="asg-{{ $task->id }}" style="display:flex; align-items:center; gap:4px; cursor:pointer; min-height:24px; flex-wrap:wrap;">
                                        @forelse($task->assignees->take(3) as $a)
                                            <div class="al-avatar" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>
                                        @empty
                                            <span style="font-size:12px; color:var(--muted);">—</span>
                                        @endforelse
                                        @if($task->assignees->count() > 3)<span style="font-size:11px; color:var(--muted); font-family:var(--mono);">+{{ $task->assignees->count()-3 }}</span>@endif
                                    </div>
                                    <div class="asg-dropdown" x-show="open" @click.outside="open=false" x-cloak style="position:absolute; top:calc(100% + 4px); left:0; width:220px; background:var(--surface); border:1px solid var(--border2); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.35); z-index:50; max-height:240px; overflow-y:auto; padding:6px;">
                                        @foreach($members as $member)
                                        <label style="display:flex; align-items:center; gap:8px; padding:7px 9px; border-radius:6px; cursor:pointer;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                            <input type="checkbox" value="{{ $member->id }}" {{ $task->assignees->contains('id',$member->id) ? 'checked' : '' }} onchange="onAssigneeChange(this, {{ $task->id }})" style="width:15px; height:15px; cursor:pointer;">
                                            <div class="al-avatar">{{ strtoupper(substr($member->name,0,1)) }}</div>
                                            <span style="font-size:13px; color:var(--text);">{{ $member->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="al-cell">
                                    <select class="al-pill al-status" onchange="applyStatus(this); patchField({{ $task->id }}, 'status', this.value).then(()=>updateDone({{ $task->id }}, this.value))">
                                        @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                        <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Priority --}}
                                <div class="al-cell">
                                    <select class="al-pill al-pri" onchange="applyPri(this); patchField({{ $task->id }}, 'priority', this.value)">
                                        @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $val=>$lbl)
                                        <option value="{{ $val }}" {{ $task->priority===$val?'selected':'' }}>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Delete --}}
                                <div class="al-cell" style="border-right:none; justify-content:center;">
                                    <button onclick="deleteTask({{ $task->id }})" title="Delete task" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        {{-- Add task row --}}
                        <form method="POST" action="{{ route('company.tasks.quick_store', [$slug, $project]) }}" class="al-addrow" style="display:flex; align-items:center; gap:6px; padding:4px 14px; border-bottom:1px solid var(--border);">
                            @csrf
                            <input type="hidden" name="section_id" value="{{ $group['id'] }}">
                            <span style="color:var(--muted); font-size:14px; flex-shrink:0;">+</span>
                            <input type="text" name="title" placeholder="Add task..." required>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ============================ BOARD TAB ============================ --}}
        <div x-show="tab==='board'" x-cloak>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                @foreach($statusMeta as $colStatus => $cfg)
                <div class="ptm-kanban-col" style="padding:12px;">
                    <div style="display:flex; align-items:center; gap:7px; margin-bottom:12px;">
                        <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
                        <span style="font-size:10px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-family:var(--mono);">{{ $cfg['label'] }}</span>
                        <span style="margin-left:auto; font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $tasks->where('status',$colStatus)->count() }}</span>
                    </div>
                    <div class="kanban-column" data-status="{{ $colStatus }}" style="display:flex; flex-direction:column; gap:8px; min-height:80px;">
                        @foreach($tasks->where('status',$colStatus) as $task)
                        <div class="kanban-task-wrapper" data-task-id="{{ $task->id }}" style="cursor:grab;">
                            <div class="ptm-kanban-card" style="padding:10px 12px;">
                                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:4px;">
                                    <a href="{{ route('company.tasks.show', [$slug, $task]) }}" class="task-title-link" style="font-size:13px; font-weight:500; color:var(--text); line-height:1.4; text-decoration:none; flex:1;">{{ $task->title }}</a>
                                    <button onclick="event.stopPropagation(); deleteTask({{ $task->id }})" class="task-actions" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:2px; flex-shrink:0;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                                @if($task->section)<div style="font-size:10px; font-family:var(--mono); color:var(--muted); margin-top:5px;">▸ {{ $task->section->name }}</div>@endif
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px;">
                                    <span class="ptm-badge" style="border:1px solid; {{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        @if($task->due_date)<span style="font-size:11px; font-family:var(--mono); {{ $task->due_date->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">{{ $task->due_date->format('d M') }}</span>@endif
                                        @foreach($task->assignees->take(2) as $a)<div class="al-avatar" style="width:20px; height:20px; font-size:9px;" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>@endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
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
                <div class="ptm-progress-track"><div class="ptm-progress-fill" style="width:{{ $project->progressPercentage() }}%;"></div></div>
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-top:18px;">
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">TOTAL TASKS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $tasks->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">SECTIONS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $sections->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">IN PROGRESS</div><div style="font-size:20px; font-weight:600; color:#22d3ee;">{{ $tasks->where('status','in_progress')->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">DONE</div><div style="font-size:20px; font-weight:600; color:#4ade80;">{{ $tasks->where('status','done')->count() }}</div></div>
                </div>
                @if($project->description)<div style="margin-top:18px; padding-top:16px; border-top:1px solid var(--border); font-size:13px; color:var(--muted); line-height:1.6;">{{ $project->description }}</div>@endif
                @if($project->due_date)<div style="margin-top:12px; font-size:12px; font-family:var(--mono); {{ $project->due_date->isPast() && $project->status !== 'completed' ? 'color:#f87171;' : 'color:var(--muted);' }}">Due {{ $project->due_date->format('d M Y') }}</div>@endif
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
    const slug = '{{ $slug }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /* ---- field styling ---- */
    function statusStyle(v){
        const m={todo:['#6b7385','rgba(107,115,133,0.14)'],in_progress:['#22d3ee','rgba(34,211,238,0.14)'],in_review:['#a78bfa','rgba(167,139,250,0.14)'],done:['#4ade80','rgba(74,222,128,0.14)']};
        return m[v]||m.todo;
    }
    function applyStatus(sel){ const [c,b]=statusStyle(sel.value); sel.style.color=c; sel.style.background=b; }
    function priStyle(v){
        const m={urgent:['#f87171','rgba(248,113,113,0.12)'],high:['#fb923c','rgba(251,146,60,0.12)'],medium:['#fbbf24','rgba(251,191,36,0.12)'],low:['#9aa3b2','transparent']};
        return m[v]||m.low;
    }
    function applyPri(sel){ const [c,b]=priStyle(sel.value); sel.style.color=c; sel.style.background=b; }

    document.querySelectorAll('.al-status').forEach(applyStatus);
    document.querySelectorAll('.al-pri').forEach(applyPri);

    /* ---- inline PATCH ---- */
    function patchField(id, field, value){
        const body={}; body[field] = value;
        return fetch(`/${slug}/admin/tasks/${id}/inline`, {
            method:'PATCH',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify(body)
        }).then(r=>r.json());
    }

    function recolorDue(id, d){
        const el=document.getElementById('due-'+id);
        if(!el) return;
        el.style.color = (d && d.task && d.task.overdue) ? '#f87171' : '';
    }

    function updateDone(id, status){
        const c=document.getElementById('done-'+id);
        if(!c) return;
        if(status==='done'){ c.style.background='#4ade80'; c.style.borderColor='#4ade80'; c.innerHTML='<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>'; }
        else { c.style.background='transparent'; c.style.borderColor='var(--border2)'; c.innerHTML=''; }
    }

    function cycleDone(id, current){
        const next = current==='done' ? 'todo' : 'done';
        const sel = document.querySelector(`#row-${id} .al-status`);
        if(sel){ sel.value=next; applyStatus(sel); }
        patchField(id,'status',next).then(()=>{ updateDone(id,next); document.getElementById('done-'+id).setAttribute('onclick', `cycleDone(${id}, '${next}')`); });
    }

    function onAssigneeChange(cb, id){
        const dd = cb.closest('.asg-dropdown');
        const ids = [...dd.querySelectorAll('input:checked')].map(c=>parseInt(c.value));
        patchField(id, 'assignees', ids).then(d=>renderAssignees(id, d.task.assignees));
    }
    function renderAssignees(id, arr){
        const cell=document.getElementById('asg-'+id);
        if(!cell) return;
        if(!arr.length){ cell.innerHTML='<span style="font-size:12px; color:var(--muted);">—</span>'; return; }
        let html = arr.slice(0,3).map(a=>`<div class="al-avatar" title="${a.name}">${a.initial}</div>`).join('');
        if(arr.length>3) html += `<span style="font-size:11px; color:var(--muted); font-family:var(--mono);">+${arr.length-3}</span>`;
        cell.innerHTML = html;
    }

    function deleteTask(id){
        if(!confirm('Delete this task?')) return;
        fetch(`/${slug}/admin/tasks/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(r=>r.json()).then(()=>{ document.getElementById('row-'+id)?.remove(); document.querySelector(`.kanban-task-wrapper[data-task-id="${id}"]`)?.remove(); });
    }

    /* ---- section rename toggle ---- */
    function toggleSecRename(id){
        const form=document.getElementById('secform-'+id);
        const name=document.getElementById('secname-'+id);
        const showing = form.style.display==='flex';
        form.style.display = showing ? 'none' : 'flex';
        name.style.display = showing ? 'inline' : 'none';
        if(!showing){ form.querySelector('input[name=name]').focus(); }
    }

    /* ---- toolbar ---- */
    function focusFirstAdd(){
        const input=document.querySelector('.al-addrow input[name=title]');
        if(input){ input.focus(); input.scrollIntoView({block:'center', behavior:'smooth'}); }
    }
    function filterTasks(q){
        q=q.trim().toLowerCase();
        document.querySelectorAll('.al-row').forEach(row=>{
            row.style.display = (!q || row.dataset.title.includes(q)) ? '' : 'none';
        });
    }

    /* ---- board drag ---- */
    if (window.Sortable) {
        document.querySelectorAll('.kanban-column').forEach(function(column){
            Sortable.create(column, {
                group:'kanban', animation:150, ghostClass:'sortable-ghost', draggable:'.kanban-task-wrapper',
                filter:'.task-actions, a.task-title-link', preventOnFilter:false,
                onEnd:function(evt){
                    const taskId=evt.item.getAttribute('data-task-id');
                    const newStatus=evt.to.getAttribute('data-status');
                    const oldStatus=evt.from.getAttribute('data-status');
                    if(newStatus===oldStatus) return;
                    fetch('/'+slug+'/admin/tasks/'+taskId+'/status',{
                        method:'PATCH',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                        body:JSON.stringify({status:newStatus})
                    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); }).catch(()=>location.reload());
                }
            });
        });
    }

    /* ================= TASK DETAIL PANEL (slide-in) ================= */
    let panelTaskId = null;
    let panelDirty = false;

    function openPanel(id){
        panelTaskId = id;
        document.getElementById('taskDrawer').classList.add('open');
        reloadPanel();
    }
    function closePanel(){
        document.getElementById('taskDrawer').classList.remove('open');
        if(panelDirty){ location.reload(); }
        panelTaskId = null;
    }
    function reloadPanel(){
        if(!panelTaskId) return;
        const body=document.getElementById('taskPanelBody');
        fetch(`/${slug}/admin/tasks/${panelTaskId}/panel`, { headers:{'Accept':'text/html'} })
            .then(r=>r.text()).then(html=>{
                body.innerHTML = html;
                body.querySelectorAll('.al-status').forEach(applyStatus);
                body.querySelectorAll('.al-pri').forEach(applyPri);
            });
    }

    function panelPatch(field, value){
        panelDirty = true;
        return patchField(panelTaskId, field, value);
    }
    function syncCompleteBtn(status){
        const btn=document.getElementById('panelComplete');
        if(!btn) return;
        if(status==='done'){ btn.style.background='rgba(74,222,128,0.15)'; btn.style.border='1px solid rgba(74,222,128,0.4)'; btn.style.color='#4ade80'; btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg> Completed'; btn.setAttribute('onclick',"panelMarkComplete('done')"); }
        else { btn.style.background='var(--surface2)'; btn.style.border='1px solid var(--border2)'; btn.style.color='var(--text)'; btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg> Mark complete'; btn.setAttribute('onclick',"panelMarkComplete('"+status+"')"); }
    }
    function panelMarkComplete(current){
        const next = current==='done' ? 'todo' : 'done';
        panelDirty = true;
        patchField(panelTaskId,'status',next).then(()=>reloadPanel());
    }
    function panelAssigneeChange(){
        const ids=[...document.querySelectorAll('.panel-asg-cb:checked')].map(c=>parseInt(c.value));
        panelDirty = true;
        patchField(panelTaskId,'assignees',ids).then(d=>{
            const sum=document.getElementById('panelAsgSummary');
            if(!sum) return;
            const a=d.task.assignees;
            if(!a.length){ sum.innerHTML='<div style="width:26px;height:26px;border-radius:50%;border:1.5px dashed var(--border2);display:flex;align-items:center;justify-content:center;color:var(--muted);"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 7a4 4 0 108 0 4 4 0 00-8 0"/></svg></div><span style="font-size:13px;color:var(--muted);">No assignee</span>'; }
            else { sum.innerHTML = a.map(x=>`<div style="display:flex;align-items:center;gap:6px;"><div class="al-avatar">${x.initial}</div><span style="font-size:13px;color:var(--text);">${x.name}</span></div>`).join(''); }
        });
    }
    function panelAddComment(form){
        panelDirty = true;
        fetch(form.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(form) })
            .then(r=>r.json()).then(()=>reloadPanel());
        return false;
    }
    function panelDeleteComment(id){
        panelDirty = true;
        fetch(`/${slug}/admin/tasks/comments/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>reloadPanel());
    }
    function panelUpload(input){
        if(!input.files.length) return;
        panelDirty = true;
        const fd=new FormData(); fd.append('file', input.files[0]);
        fetch(input.dataset.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:fd })
            .then(r=>r.json()).then(()=>reloadPanel());
    }
    function panelDeleteAttachment(id){
        panelDirty = true;
        fetch(`/${slug}/admin/tasks/attachments/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>reloadPanel());
    }
    function panelAddSubtask(form){
        panelDirty = true;
        fetch(form.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(form) })
            .then(r=>r.json()).then(()=>reloadPanel());
        return false;
    }
    function panelToggleSubtask(id, current){
        const next = current==='done' ? 'todo' : 'done';
        panelDirty = true;
        patchField(id,'status',next).then(()=>reloadPanel());
    }
    function panelDeleteTask(){
        if(!confirm('Delete this task?')) return;
        fetch(`/${slug}/admin/tasks/${panelTaskId}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>{ panelDirty=false; document.getElementById('taskDrawer').classList.remove('open'); location.reload(); });
    }

    /* close assignee dropdown on outside click */
    document.addEventListener('click', function(e){
        const drop=document.getElementById('panelAsgDrop');
        const sum=document.getElementById('panelAsgSummary');
        if(drop && drop.classList.contains('show') && !drop.contains(e.target) && sum && !sum.contains(e.target)){
            drop.classList.remove('show');
        }
    });
    /* ESC closes panel */
    document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ const d=document.getElementById('taskDrawer'); if(d.classList.contains('open')) closePanel(); } });
    </script>

    {{-- Slide-in task detail drawer --}}
    <div id="taskDrawer" class="task-drawer">
        <div class="task-drawer-overlay" onclick="closePanel()"></div>
        <div class="task-drawer-panel">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); flex-shrink:0;">
                <span style="font-size:12px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em;">Task details</span>
                <button onclick="closePanel()" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:5px; display:flex;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div id="taskPanelBody" style="flex:1; overflow-y:auto; padding:22px 24px;">
                <div style="text-align:center; color:var(--muted); padding:40px; font-size:13px;">Loading…</div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak]{display:none!important;}
        .task-drawer { position:fixed; inset:0; z-index:200; pointer-events:none; }
        .task-drawer .task-drawer-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.5); opacity:0; transition:opacity 0.2s; }
        .task-drawer .task-drawer-panel { position:absolute; top:0; right:0; height:100%; width:560px; max-width:92vw; background:var(--surface); border-left:1px solid var(--border2); display:flex; flex-direction:column; transform:translateX(100%); transition:transform 0.25s ease; box-shadow:-10px 0 40px rgba(0,0,0,0.35); }
        .task-drawer.open { pointer-events:auto; }
        .task-drawer.open .task-drawer-overlay { opacity:1; }
        .task-drawer.open .task-drawer-panel { transform:translateX(0); }
        .panel-drop.show { display:block !important; }
    </style>

</x-company-layout>
