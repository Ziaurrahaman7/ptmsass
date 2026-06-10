<x-company-layout :title="$project->name">

    {{-- Header --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px;">
        <div>
            <a href="{{ route('company.projects.index', $slug) }}" style="font-size:12px; color:var(--muted); text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">← Projects</a>
            <div style="font-size:18px; font-weight:600; letter-spacing:-0.3px; color:var(--text); margin-top:4px;">{{ $project->name }}</div>
            @if($project->description)<div style="font-size:13px; color:var(--muted); margin-top:2px;">{{ $project->description }}</div>@endif
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

    {{-- Progress --}}
    <div class="ptm-card" style="padding:16px 20px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
            <span style="font-size:12px; color:var(--muted);">Overall Progress</span>
            <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:#4ade80;">{{ $project->progressPercentage() }}%</span>
        </div>
        <div style="height:4px; background:var(--border); border-radius:2px;">
            <div style="height:100%; border-radius:2px; background:#4ade80; width:{{ $project->progressPercentage() }}%; transition:width 0.3s;"></div>
        </div>
        <div style="display:flex; gap:20px; margin-top:10px; font-size:11px; color:var(--muted); font-family:var(--mono);">
            <span>{{ $tasks->count() }} total</span>
            <span>{{ $tasks->where('status','in_progress')->count() }} in progress</span>
            <span>{{ $tasks->where('status','done')->count() }} done</span>
            @if($project->due_date)<span style="{{ $project->due_date->isPast() && $project->status !== 'completed' ? 'color:#f87171;' : '' }}">Due {{ $project->due_date->format('d M Y') }}</span>@endif
        </div>
    </div>

    {{-- Kanban --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
        @foreach(['todo'=>['label'=>'To Do','color'=>'#6b7385'],'in_progress'=>['label'=>'In Progress','color'=>'#22d3ee'],'in_review'=>['label'=>'In Review','color'=>'#a78bfa'],'done'=>['label'=>'Done','color'=>'#4ade80']] as $colStatus => $cfg)
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:12px;">
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
                            <a href="{{ route('company.tasks.show', [auth()->user()->company->slug, $task]) }}" class="task-title-link" style="font-size:13px; font-weight:500; color:var(--text); line-height:1.4; text-decoration:none; flex:1;">{{ $task->title }}</a>
                            <div style="display:flex; gap:4px; flex-shrink:0;" class="task-actions">
                                <button onclick="event.stopPropagation(); openEditTask({{ $task->id }},'{{ addslashes($task->title) }}','{{ addslashes($task->description ?? '') }}','{{ $task->status }}','{{ $task->priority }}','{{ $task->assignees->pluck("id")->implode(",") }}','{{ $task->due_date?->format('Y-m-d') }}')" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:2px;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('company.tasks.destroy', [$slug, $task]) }}" style="display:inline;" onsubmit="event.stopPropagation();">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete task?')" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:2px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($task->description)<div style="font-size:11px; color:var(--muted); margin-top:4px; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $task->description }}</div>@endif
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px;">
                            <span style="font-size:10px; font-family:var(--mono); padding:2px 7px; border-radius:4px; border:1px solid;
                                {{ $task->priority === 'urgent' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                                   ($task->priority === 'high' ? 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                                   ($task->priority === 'medium' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                            <div style="display:flex; align-items:center; gap:6px;">
                                @if($task->due_date)<span style="font-size:11px; font-family:var(--mono); {{ $task->due_date->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">{{ $task->due_date->format('d M') }}</span>@endif
                                @if($task->assignees->count() > 0)
                                <div style="display:flex; align-items:center; gap:2px;">
                                    @foreach($task->assignees->take(2) as $assignee)
                                    <div style="width:20px; height:20px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center;" title="{{ $assignee->name }}">{{ strtoupper(substr($assignee->name,0,1)) }}</div>
                                    @endforeach
                                    @if($task->assignees->count() > 2)
                                    <div style="width:20px; height:20px; border-radius:6px; background:var(--surface); border:1px solid var(--border2); font-size:9px; color:var(--muted); display:flex; align-items:center; justify-content:center;" title="+{{ $task->assignees->count() - 2 }} more">+{{ $task->assignees->count() - 2 }}</div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <button onclick="document.getElementById('addTaskModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Task
    </button>

    {{-- Add Task Modal --}}
    <div id="addTaskModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; padding:0; width:100%; max-width:480px;">
            <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:15px; font-weight:600; color:var(--text);">Add Task</span>
                <button onclick="document.getElementById('addTaskModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">✕</button>
            </div>
            <form method="POST" action="{{ route('company.tasks.store', [$slug, $project]) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @csrf
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TITLE *</label>
                    <input type="text" name="title" class="ptm-input" style="width:100%;" required>
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DESCRIPTION</label>
                    <textarea name="description" rows="2" class="ptm-input" style="width:100%; resize:vertical;"></textarea>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">STATUS</label>
                        <select name="status" class="ptm-select" style="width:100%;">
                            <option value="todo">To Do</option><option value="in_progress">In Progress</option><option value="in_review">In Review</option><option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PRIORITY</label>
                        <select name="priority" class="ptm-select" style="width:100%;">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DUE DATE</label>
                        <input type="date" name="due_date" class="ptm-input" style="width:100%;">
                    </div>
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ASSIGN TO (Multiple)</label>
                    <div class="custom-multiselect" style="position:relative;">
                        <div class="multiselect-trigger" onclick="toggleMultiselectAdd(this)" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:9px 12px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; min-height:42px;">
                            <div class="selected-users" style="display:flex; flex-wrap:wrap; gap:4px; flex:1;">
                                <span style="font-size:13px; color:var(--muted);">Select assignees...</span>
                            </div>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; transition:transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="multiselect-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; margin-top:4px; background:var(--surface); border:1px solid var(--border2); border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:1000; max-height:240px; overflow-y:auto;">
                            <div style="padding:8px;">
                                @foreach($members as $member)
                                <label class="multiselect-option" data-user-id="{{ $member->id }}" data-user-name="{{ $member->name }}" data-user-initial="{{ strtoupper(substr($member->name,0,1)) }}" style="display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:6px; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="assignees[]" value="{{ $member->id }}" style="width:16px; height:16px; cursor:pointer;" onchange="updateSelectedUsersAdd(this)">
                                    <div style="width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:11px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($member->name,0,1)) }}</div>
                                    <span style="font-size:13px; color:var(--text); flex:1;">{{ $member->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:10px; padding-top:4px;">
                    <button type="submit" class="ptm-btn-primary">Add Task</button>
                    <button type="button" onclick="document.getElementById('addTaskModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Task Modal --}}
    <div id="editTaskModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:480px;">
            <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:15px; font-weight:600; color:var(--text);">Edit Task</span>
                <button onclick="document.getElementById('editTaskModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">✕</button>
            </div>
            <form id="editTaskForm" method="POST" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TITLE *</label>
                    <input type="text" name="title" id="editTitle" class="ptm-input" style="width:100%;" required>
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DESCRIPTION</label>
                    <textarea name="description" id="editDescription" rows="2" class="ptm-input" style="width:100%; resize:vertical;"></textarea>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">STATUS</label>
                        <select name="status" id="editStatus" class="ptm-select" style="width:100%;">
                            <option value="todo">To Do</option><option value="in_progress">In Progress</option><option value="in_review">In Review</option><option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PRIORITY</label>
                        <select name="priority" id="editPriority" class="ptm-select" style="width:100%;">
                            <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DUE DATE</label>
                        <input type="date" name="due_date" id="editDueDate" class="ptm-input" style="width:100%;">
                    </div>
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ASSIGN TO (Multiple)</label>
                    <div class="custom-multiselect" id="editMultiselect" style="position:relative;">
                        <div class="multiselect-trigger" onclick="toggleMultiselectEdit(this)" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:9px 12px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; min-height:42px;">
                            <div class="selected-users" style="display:flex; flex-wrap:wrap; gap:4px; flex:1;">
                                <span style="font-size:13px; color:var(--muted);">Select assignees...</span>
                            </div>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; transition:transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="multiselect-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; margin-top:4px; background:var(--surface); border:1px solid var(--border2); border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:1000; max-height:240px; overflow-y:auto;">
                            <div style="padding:8px;">
                                @foreach($members as $member)
                                <label class="multiselect-option" data-user-id="{{ $member->id }}" data-user-name="{{ $member->name }}" data-user-initial="{{ strtoupper(substr($member->name,0,1)) }}" style="display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:6px; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" name="assignees[]" value="{{ $member->id }}" class="edit-assignee-checkbox" data-user-id="{{ $member->id }}" style="width:16px; height:16px; cursor:pointer;" onchange="updateSelectedUsersEdit(this)">
                                    <div style="width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:11px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($member->name,0,1)) }}</div>
                                    <span style="font-size:13px; color:var(--text); flex:1;">{{ $member->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:10px; padding-top:4px;">
                    <button type="submit" class="ptm-btn-primary">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editTaskModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const slug = '{{ $slug }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleMultiselectAdd(trigger) {
        const dropdown = trigger.nextElementSibling;
        const isVisible = dropdown.style.display === 'block';
        document.querySelectorAll('.multiselect-dropdown').forEach(d => d.style.display = 'none');
        dropdown.style.display = isVisible ? 'none' : 'block';
        trigger.querySelector('svg').style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function updateSelectedUsersAdd(checkbox) {
        const container = checkbox.closest('.custom-multiselect');
        const selectedDiv = container.querySelector('.selected-users');
        const checkedBoxes = container.querySelectorAll('input[type="checkbox"]:checked');
        
        selectedDiv.innerHTML = '';
        
        if (checkedBoxes.length === 0) {
            selectedDiv.innerHTML = '<span style="font-size:13px; color:var(--muted);">Select assignees...</span>';
        } else {
            checkedBoxes.forEach(cb => {
                const option = cb.closest('.multiselect-option');
                const initial = option.dataset.userInitial;
                const name = option.dataset.userName;
                const badge = document.createElement('div');
                badge.style.cssText = 'display:inline-flex; align-items:center; gap:4px; padding:4px 8px; background:rgba(74,222,128,0.15); border:1px solid rgba(74,222,128,0.3); border-radius:6px; font-size:12px; color:var(--text);';
                badge.innerHTML = `<div style="width:18px; height:18px; border-radius:4px; background:rgba(74,222,128,0.3); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center;">${initial}</div><span>${name}</span>`;
                selectedDiv.appendChild(badge);
            });
        }
    }

    function toggleMultiselectEdit(trigger) {
        const dropdown = trigger.nextElementSibling;
        const isVisible = dropdown.style.display === 'block';
        document.querySelectorAll('.multiselect-dropdown').forEach(d => d.style.display = 'none');
        dropdown.style.display = isVisible ? 'none' : 'block';
        trigger.querySelector('svg').style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function updateSelectedUsersEdit(checkbox) {
        const container = checkbox.closest('.custom-multiselect');
        const selectedDiv = container.querySelector('.selected-users');
        const checkedBoxes = container.querySelectorAll('input[type="checkbox"]:checked');
        
        selectedDiv.innerHTML = '';
        
        if (checkedBoxes.length === 0) {
            selectedDiv.innerHTML = '<span style="font-size:13px; color:var(--muted);">Select assignees...</span>';
        } else {
            checkedBoxes.forEach(cb => {
                const option = cb.closest('.multiselect-option');
                const initial = option.dataset.userInitial;
                const name = option.dataset.userName;
                const badge = document.createElement('div');
                badge.style.cssText = 'display:inline-flex; align-items:center; gap:4px; padding:4px 8px; background:rgba(74,222,128,0.15); border:1px solid rgba(74,222,128,0.3); border-radius:6px; font-size:12px; color:var(--text);';
                badge.innerHTML = `<div style="width:18px; height:18px; border-radius:4px; background:rgba(74,222,128,0.3); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center;">${initial}</div><span>${name}</span>`;
                selectedDiv.appendChild(badge);
            });
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-multiselect')) {
            document.querySelectorAll('.multiselect-dropdown').forEach(d => d.style.display = 'none');
            document.querySelectorAll('.multiselect-trigger svg').forEach(svg => svg.style.transform = 'rotate(0deg)');
        }
    });

    document.querySelectorAll('.kanban-column').forEach(function(column) {
        Sortable.create(column, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            draggable: '.kanban-task-wrapper',
            filter: '.task-actions, a.task-title-link',
            preventOnFilter: false,
            onEnd: function(evt) {
                const taskId = evt.item.getAttribute('data-task-id');
                const newStatus = evt.to.getAttribute('data-status');
                const oldStatus = evt.from.getAttribute('data-status');
                if (newStatus === oldStatus) return;
                fetch('/' + slug + '/admin/tasks/' + taskId + '/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) { if (data.success) location.reload(); })
                .catch(function() { location.reload(); });
            }
        });
    });

    function openEditTask(id,title,description,status,priority,assignedTo,dueDate){
        document.getElementById('editTaskForm').action='/'+slug+'/admin/tasks/'+id;
        document.getElementById('editTitle').value=title;
        document.getElementById('editDescription').value=description;
        document.getElementById('editStatus').value=status;
        document.getElementById('editPriority').value=priority;
        document.getElementById('editDueDate').value=dueDate||'';
        
        // Clear all checkboxes first
        document.querySelectorAll('.edit-assignee-checkbox').forEach(cb => cb.checked = false);
        
        // Check the assignees (comma-separated string)
        if(assignedTo) {
            const ids = assignedTo.split(',');
            ids.forEach(id => {
                const checkbox = document.querySelector(`.edit-assignee-checkbox[data-user-id="${id.trim()}"]`);
                if(checkbox) {
                    checkbox.checked = true;
                    updateSelectedUsersEdit(checkbox);
                }
            });
        } else {
            // Reset selected users display
            const container = document.getElementById('editMultiselect');
            const selectedDiv = container.querySelector('.selected-users');
            selectedDiv.innerHTML = '<span style="font-size:13px; color:var(--muted);">Select assignees...</span>';
        }
        
        document.getElementById('editTaskModal').style.display='flex';
    }
    </script>

</x-company-layout>
