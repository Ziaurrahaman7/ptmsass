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
        @foreach(['todo'=>['label'=>'To Do','color'=>'#6b7385'],'in_progress'=>['label'=>'In Progress','color'=>'#22d3ee'],'in_review'=>['label'=>'In Review','color'=>'#a78bfa'],'done'=>['label'=>'Done','color'=>'#4ade80']] as $status => $cfg)
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:12px;">
            <div style="display:flex; align-items:center; gap:7px; margin-bottom:12px;">
                <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
                <span style="font-size:10px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-family:var(--mono);">{{ $cfg['label'] }}</span>
                <span style="margin-left:auto; font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $tasks->where('status',$status)->count() }}</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;" class="kanban-column" data-status="{{ $status }}">
                @foreach($tasks->where('status',$status) as $task)
                <div data-task-id="{{ $task->id }}" class="kanban-task-wrapper">
                <div class="ptm-kanban-card" style="padding:10px 12px; cursor:grab;" onmouseover="this.style.borderColor='var(--border2)'" onmouseout="this.style.borderColor='transparent'">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:4px;" onclick="window.location='{{ route('company.tasks.show', [auth()->user()->company->slug, $task]) }}'">
                        <div style="font-size:13px; font-weight:500; color:var(--text); line-height:1.4; cursor:pointer;">{{ $task->title }}</div>
                        <div style="display:flex; gap:4px; flex-shrink:0; opacity:0;" class="task-actions" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                            <button onclick="openEditTask({{ $task->id }},'{{ addslashes($task->title) }}','{{ addslashes($task->description) }}','{{ $task->status }}','{{ $task->priority }}','{{ $task->assigned_to }}','{{ $task->due_date?->format('Y-m-d') }}')" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:2px;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('company.tasks.destroy', [$slug, $task]) }}" style="display:inline;"
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
                            @if($task->assignee)<div style="width:20px; height:20px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center;" title="{{ $task->assignee->name }}">{{ strtoupper(substr($task->assignee->name,0,1)) }}</div>@endif
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
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ASSIGN TO</label>
                        <select name="assigned_to" class="ptm-select" style="width:100%;">
                            <option value="">Unassigned</option>
                            @foreach($members as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DUE DATE</label>
                        <input type="date" name="due_date" class="ptm-input" style="width:100%;">
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
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ASSIGN TO</label>
                        <select name="assigned_to" id="editAssignee" class="ptm-select" style="width:100%;">
                            <option value="">Unassigned</option>
                            @foreach($members as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DUE DATE</label>
                        <input type="date" name="due_date" id="editDueDate" class="ptm-input" style="width:100%;">
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
    
    // Drag & Drop
    document.querySelectorAll('.kanban-column').forEach(column => {
        new Sortable(column, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            draggable: '.kanban-task-wrapper',
            onEnd: function(evt) {
                const taskId = evt.item.getAttribute('data-task-id');
                const newStatus = evt.to.getAttribute('data-status');
                
                console.log('Task ID:', taskId, 'New Status:', newStatus);
                
                fetch(`/${slug}/admin/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update task status');
                    location.reload();
                });
            }
        });
    });
    
    function openEditTask(id,title,description,status,priority,assignedTo,dueDate){
        document.getElementById('editTaskForm').action='/'+slug+'/admin/tasks/'+id;
        document.getElementById('editTitle').value=title;
        document.getElementById('editDescription').value=description;
        document.getElementById('editStatus').value=status;
        document.getElementById('editPriority').value=priority;
        document.getElementById('editAssignee').value=assignedTo||'';
        document.getElementById('editDueDate').value=dueDate||'';
        document.getElementById('editTaskModal').style.display='flex';
    }
    document.querySelectorAll('.ptm-kanban-card').forEach(card=>{
        card.addEventListener('mouseenter',()=>{ const a=card.querySelector('.task-actions'); if(a) a.style.opacity=1; });
        card.addEventListener('mouseleave',()=>{ const a=card.querySelector('.task-actions'); if(a) a.style.opacity=0; });
    });
    </script>

</x-company-layout>
