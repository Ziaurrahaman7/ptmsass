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
    $sm = $statusMeta[$task->status] ?? $statusMeta['todo'];
@endphp

{{-- Actions --}}
<div style="display:flex; align-items:center; gap:8px; margin-bottom:18px;">
    @if($isMine)
    <button onclick="empMarkComplete('{{ $task->status }}')" id="panelComplete"
        style="display:flex; align-items:center; gap:7px; padding:7px 14px; border-radius:8px; cursor:pointer; font-size:13px; font-family:var(--font);
        {{ $task->status === 'done' ? 'background:rgba(74,222,128,0.15); border:1px solid rgba(74,222,128,0.4); color:#4ade80;' : 'background:var(--surface2); border:1px solid var(--border2); color:var(--text);' }}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
        {{ $task->status === 'done' ? 'Completed' : 'Mark complete' }}
    </button>
    @else
    <span style="font-size:11px; color:var(--muted); font-family:var(--mono); padding:6px 0;">Read-only — not assigned to you</span>
    @endif
</div>

{{-- Title --}}
<div style="font-size:21px; font-weight:600; color:var(--text); line-height:1.3; margin-bottom:18px; padding:0 2px;">{{ $task->title }}</div>

{{-- Meta grid --}}
<div style="display:flex; flex-direction:column; gap:2px; margin-bottom:22px;">
    {{-- Assignee --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Assignee</span>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            @forelse($task->assignees as $a)
                <div style="display:flex; align-items:center; gap:6px;">
                    <div class="al-avatar">{{ strtoupper(substr($a->name,0,1)) }}</div>
                    <span style="font-size:13px; color:var(--text);">{{ $a->name }}</span>
                </div>
            @empty
                <span style="font-size:13px; color:var(--muted);">No assignee</span>
            @endforelse
        </div>
    </div>

    {{-- Due date --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Due date</span>
        <span style="font-size:13px; font-family:var(--mono); {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--text);' }}">{{ $task->due_date?->format('d M Y') ?? '—' }}</span>
    </div>

    {{-- Status --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Status</span>
        @if($isMine)
            <select class="al-pill al-status" style="width:170px;" onchange="applyStatus(this); empPanelStatus(this.value)">
                @foreach($statusMeta as $val=>$cfg)
                <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $cfg['label'] }}</option>
                @endforeach
            </select>
        @else
            <span class="al-badge" style="border-color:var(--border2); color:{{ $sm['color'] }};">{{ $sm['label'] }}</span>
        @endif
    </div>

    {{-- Priority --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Priority</span>
        <span class="al-badge" style="{{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
    </div>

    {{-- Section --}}
    <div style="display:grid; grid-template-columns:120px 1fr; align-items:center; gap:10px; padding:8px 0;">
        <span style="font-size:12px; color:var(--muted); font-weight:500;">Section</span>
        <span style="font-size:13px; color:var(--text);">{{ $task->section?->name ?? '(No section)' }}</span>
    </div>
</div>

{{-- Description --}}
<div style="margin-bottom:22px;">
    <div style="font-size:12px; color:var(--muted); font-weight:500; margin-bottom:6px;">Description</div>
    <div style="font-size:13px; color:{{ $task->description ? 'var(--text)' : 'var(--muted)' }}; line-height:1.5; white-space:pre-wrap; padding:10px 12px; background:var(--surface2); border-radius:8px;">{{ $task->description ?: 'No description.' }}</div>
</div>

{{-- Subtasks --}}
@if($task->subtasks->count() > 0)
<div style="margin-bottom:22px;">
    <div style="font-size:12px; color:var(--text); font-weight:600; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
        Subtasks <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $task->subtasks->count() }}</span>
    </div>
    <div style="display:flex; flex-direction:column; gap:2px;">
        @foreach($task->subtasks as $sub)
        <div style="display:flex; align-items:center; gap:9px; padding:7px 4px; border-bottom:1px solid var(--border);">
            <div style="width:15px; height:15px; border-radius:50%; border:1.5px solid {{ $sub->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $sub->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                @if($sub->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
            </div>
            <span style="font-size:13px; color:var(--text); flex:1; {{ $sub->status === 'done' ? 'text-decoration:line-through; color:var(--muted);' : '' }}">{{ $sub->title }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Attachments --}}
<div style="margin-bottom:22px;">
    <div style="font-size:12px; color:var(--text); font-weight:600; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
        Attachments <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $task->attachments->count() }}</span>
    </div>
    <div style="display:flex; flex-direction:column; gap:6px;">
        @foreach($task->attachments as $att)
        <div style="display:flex; align-items:center; gap:9px; padding:8px 10px; background:var(--surface2); border-radius:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="flex-shrink:0;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
            <a href="{{ asset('storage/'.$att->file_path) }}" target="_blank" style="font-size:13px; color:var(--text); text-decoration:none; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $att->file_name }}</a>
            <span style="font-size:10px; color:var(--muted); font-family:var(--mono);">{{ number_format($att->file_size/1024, 0) }} KB</span>
            @if($att->uploaded_by === auth()->id())
            <button onclick="empDeleteAttachment({{ $att->id }})" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:2px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            @endif
        </div>
        @endforeach
    </div>
    <label style="display:inline-flex; align-items:center; gap:7px; margin-top:8px; padding:7px 12px; border:1px dashed var(--border2); border-radius:8px; cursor:pointer; font-size:12px; color:var(--muted);" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Upload file
        <input type="file" onchange="empUpload(this)" data-action="{{ route('employee.tasks.attachments.store', [$slug, $task]) }}" style="display:none;">
    </label>
</div>

{{-- Comments --}}
<div>
    <div style="font-size:12px; color:var(--text); font-weight:600; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
        Comments <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $task->comments->count() }}</span>
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
                    <button onclick="empDeleteComment({{ $comment->id }})" style="margin-left:auto; background:none; border:none; color:var(--muted); cursor:pointer; padding:2px; font-size:11px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">Delete</button>
                    @endif
                </div>
                <div style="font-size:13px; color:var(--text); line-height:1.5; margin-top:3px; white-space:pre-wrap;">{{ $comment->comment }}</div>
            </div>
        </div>
        @empty
        <div style="font-size:12px; color:var(--muted); font-family:var(--mono);">No comments yet.</div>
        @endforelse
    </div>
    <form onsubmit="return empAddComment(this)" action="{{ route('employee.tasks.comments.store', [$slug, $task]) }}" style="display:flex; gap:8px; align-items:flex-end;">
        <textarea name="comment" required rows="1" placeholder="Add a comment..." oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px';"
            style="flex:1; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-size:13px; font-family:var(--font); padding:9px 12px; resize:none; line-height:1.4; max-height:120px;"
            onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.requestSubmit();}"></textarea>
        <button type="submit" style="padding:9px 16px; font-size:13px; background:rgba(34,211,238,0.12); border:1px solid rgba(34,211,238,0.3); color:var(--accent2); border-radius:8px; cursor:pointer; font-family:var(--font);">Send</button>
    </form>
</div>
