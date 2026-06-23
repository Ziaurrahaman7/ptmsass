@php
    $statusMeta = [
        'todo'        => ['label' => 'To Do',       'color' => '#6b7385'],
        'in_progress' => ['label' => 'In Progress', 'color' => '#22d3ee'],
        'in_review'   => ['label' => 'In Review',   'color' => '#a78bfa'],
        'done'        => ['label' => 'Done',        'color' => '#4ade80'],
    ];
    $priorityStyles = [
        'urgent' => 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);',
        'high'   => 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);',
        'medium' => 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);',
        'low'    => 'color:var(--muted); border-color:var(--border2); background:transparent;',
    ];
@endphp

{{-- Actions --}}
<div style="display:flex; align-items:center; gap:8px; margin-bottom:18px;">
    <button onclick="panelMarkComplete('{{ $task->status }}')" id="panelComplete"
        style="display:flex; align-items:center; gap:7px; padding:7px 14px; border-radius:8px; cursor:pointer; font-size:13px; font-family:var(--font);
        {{ $task->status === 'done' ? 'background:rgba(74,222,128,0.15); border:1px solid rgba(74,222,128,0.4); color:#4ade80;' : 'background:var(--surface2); border:1px solid var(--border2); color:var(--text);' }}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
        {{ $task->status === 'done' ? 'Completed' : 'Mark complete' }}
    </button>
    <div style="margin-left:auto; display:flex; align-items:center; gap:4px;">
        <button onclick="panelDeleteTask()" title="Delete task" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:6px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </div>
</div>

{{-- Title --}}
<textarea onchange="panelPatch('title', this.value)" rows="1" oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px';"
    style="width:100%; background:transparent; border:1px solid transparent; border-radius:8px; color:var(--text); font-size:21px; font-weight:600; font-family:var(--font); padding:6px 8px; resize:none; line-height:1.3; margin-bottom:18px;"
    onfocus="this.style.background='var(--surface2)';this.style.borderColor='var(--border2)'" onblur="this.style.background='transparent';this.style.borderColor='transparent'">{{ $task->title }}</textarea>

{{-- Meta grid --}}
<div style="display:flex; flex-direction:column; gap:2px; margin-bottom:22px;">
    {{-- Assignee --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Assignee</span>
        <div style="position:relative;">
            <div id="panelAsgSummary" onclick="document.getElementById('panelAsgDrop').classList.toggle('show')" style="display:flex; align-items:center; gap:6px; cursor:pointer; padding:5px 8px; border-radius:7px;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                @forelse($task->assignees as $a)
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div class="al-avatar">{{ strtoupper(substr($a->name,0,1)) }}</div>
                        <span style="font-size:13px; color:var(--text);">{{ $a->name }}</span>
                    </div>
                @empty
                    <div style="width:26px; height:26px; border-radius:50%; border:1.5px dashed var(--border2); display:flex; align-items:center; justify-content:center; color:var(--muted);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 7a4 4 0 108 0 4 4 0 00-8 0"/></svg>
                    </div>
                    <span style="font-size:13px; color:var(--muted);">No assignee</span>
                @endforelse
            </div>
            <div id="panelAsgDrop" class="panel-drop" style="display:none; position:absolute; top:calc(100% + 4px); left:0; width:240px; background:var(--surface); border:1px solid var(--border2); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.4); z-index:20; max-height:240px; overflow-y:auto; padding:6px;">
                @foreach($members as $member)
                <label style="display:flex; align-items:center; gap:8px; padding:7px 9px; border-radius:6px; cursor:pointer;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" value="{{ $member->id }}" {{ $task->assignees->contains('id',$member->id) ? 'checked' : '' }} onchange="panelAssigneeChange()" class="panel-asg-cb" style="width:15px; height:15px; cursor:pointer;">
                    <div class="al-avatar">{{ strtoupper(substr($member->name,0,1)) }}</div>
                    <span style="font-size:13px; color:var(--text);">{{ $member->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Due date --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Due date</span>
        <input type="date" value="{{ $task->due_date?->format('Y-m-d') }}" onchange="panelPatch('due_date', this.value)"
            style="background:transparent; border:1px solid transparent; border-radius:7px; color:{{ $task->due_date?->isPast() && $task->status !== 'done' ? '#f87171' : 'var(--text)' }}; font-size:13px; font-family:var(--mono); padding:5px 8px; width:170px; cursor:pointer;"
            onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
    </div>

    {{-- Status --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Status</span>
        <select class="al-pill al-status" style="width:170px;" onchange="applyStatus(this); panelPatch('status', this.value); syncCompleteBtn(this.value)">
            @foreach($statusMeta as $val=>$cfg)
            <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $cfg['label'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Priority --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Priority</span>
        <select class="al-pill al-pri" style="width:170px;" onchange="applyPri(this); panelPatch('priority', this.value)">
            @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $val=>$lbl)
            <option value="{{ $val }}" {{ $task->priority===$val?'selected':'' }}>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>

    {{-- Section / Project --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Section</span>
        <select class="al-pill" style="width:200px; color:var(--text); background:var(--surface2);" onchange="panelPatch('section_id', this.value)">
            <option value="">No section</option>
            @foreach($sections as $section)
            <option value="{{ $section->id }}" {{ $task->section_id===$section->id?'selected':'' }}>{{ $section->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Description --}}
<div style="margin-bottom:22px;">
    <div style="font-size:12px; color:var(--muted); font-weight:500; margin-bottom:6px;">Description</div>
    <textarea onchange="panelPatch('description', this.value)" rows="3" placeholder="Add a description..."
        style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-size:13px; font-family:var(--font); padding:10px 12px; resize:vertical; line-height:1.5;">{{ $task->description }}</textarea>
</div>

{{-- Subtasks --}}
<div style="margin-bottom:22px;">
    <div style="font-size:12px; color:var(--text); font-weight:600; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
        Subtasks
        <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $task->subtasks->count() }}</span>
    </div>
    <div style="display:flex; flex-direction:column; gap:2px;">
        @foreach($task->subtasks as $sub)
        <div style="display:flex; align-items:center; gap:9px; padding:7px 4px; border-bottom:1px solid var(--border);">
            <div onclick="panelToggleSubtask({{ $sub->id }}, '{{ $sub->status }}')" title="Toggle done" style="width:15px; height:15px; border-radius:50%; border:1.5px solid {{ $sub->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $sub->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                @if($sub->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
            </div>
            <span style="font-size:13px; color:var(--text); flex:1; {{ $sub->status === 'done' ? 'text-decoration:line-through; color:var(--muted);' : '' }}">{{ $sub->title }}</span>
            @foreach($sub->assignees->take(1) as $a)<div class="al-avatar" style="width:20px; height:20px; font-size:9px;" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>@endforeach
        </div>
        @endforeach
    </div>
    <form onsubmit="return panelAddSubtask(this)" action="{{ route('company.tasks.subtasks.store', [$slug, $task]) }}" style="display:flex; align-items:center; gap:8px; padding:8px 4px 0;">
        <span style="color:var(--muted); font-size:14px;">+</span>
        <input type="text" name="title" placeholder="Add subtask..." required style="flex:1; background:transparent; border:none; color:var(--text); font-size:13px; padding:4px 0; font-family:var(--font); outline:none;">
    </form>
</div>

{{-- Attachments --}}
<div style="margin-bottom:22px;">
    <div style="font-size:12px; color:var(--text); font-weight:600; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
        Attachments
        <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $task->attachments->count() }}</span>
    </div>
    <div style="display:flex; flex-direction:column; gap:6px;">
        @foreach($task->attachments as $att)
        <div style="display:flex; align-items:center; gap:9px; padding:8px 10px; background:var(--surface2); border-radius:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="flex-shrink:0;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
            <a href="{{ asset('storage/'.$att->file_path) }}" target="_blank" style="font-size:13px; color:var(--text); text-decoration:none; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $att->file_name }}</a>
            <span style="font-size:10px; color:var(--muted); font-family:var(--mono);">{{ number_format($att->file_size/1024, 0) }} KB</span>
            <button onclick="panelDeleteAttachment({{ $att->id }})" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:2px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        @endforeach
    </div>
    <label style="display:inline-flex; align-items:center; gap:7px; margin-top:8px; padding:7px 12px; border:1px dashed var(--border2); border-radius:8px; cursor:pointer; font-size:12px; color:var(--muted);" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Upload file
        <input type="file" onchange="panelUpload(this)" data-action="{{ route('company.tasks.attachments.store', [$slug, $task]) }}" style="display:none;">
    </label>
</div>

{{-- Comments --}}
<div>
    <div style="font-size:12px; color:var(--text); font-weight:600; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
        Comments
        <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $task->comments->count() }}</span>
    </div>
    <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:14px;">
        @forelse($task->comments->sortByDesc('created_at') as $comment)
        <div style="display:flex; gap:10px;">
            <div class="al-avatar" style="width:28px; height:28px; font-size:11px; flex-shrink:0;">{{ strtoupper(substr($comment->user->name ?? '?',0,1)) }}</div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:13px; font-weight:600; color:var(--text);">{{ $comment->user->name ?? 'Unknown' }}</span>
                    <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $comment->created_at->diffForHumans() }}</span>
                    @if($comment->user_id === auth()->id())
                    <button onclick="panelDeleteComment({{ $comment->id }})" style="margin-left:auto; background:none; border:none; color:var(--muted); cursor:pointer; padding:2px; font-size:11px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">Delete</button>
                    @endif
                </div>
                <div style="font-size:13px; color:var(--text); line-height:1.5; margin-top:3px; white-space:pre-wrap;">{{ $comment->comment }}</div>
            </div>
        </div>
        @empty
        <div style="font-size:12px; color:var(--muted); font-family:var(--mono);">No comments yet.</div>
        @endforelse
    </div>
    <form onsubmit="return panelAddComment(this)" action="{{ route('company.tasks.comments.store', [$slug, $task]) }}" style="display:flex; gap:8px; align-items:flex-end;">
        <textarea name="comment" required rows="1" placeholder="Add a comment..." oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px';"
            style="flex:1; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-size:13px; font-family:var(--font); padding:9px 12px; resize:none; line-height:1.4; max-height:120px;"
            onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.requestSubmit();}"></textarea>
        <button type="submit" class="ptm-btn-primary" style="padding:9px 16px; font-size:13px;">Send</button>
    </form>
</div>
