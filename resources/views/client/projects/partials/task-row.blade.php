@php
    $st = $task->status;
    $isDone = $st === 'done';
    $isOverdue = ! $isDone && $task->due_date && $task->due_date->isPast();
    $canComment = auth()->user()->can('comment', $task);
    $canUpdate = auth()->user()->can('update', $task);
    $hasActions = $canComment || $canUpdate;
    $assignee = $task->assignee ?? $task->assignees->first();
@endphp
<div style="padding:14px 18px; border-bottom:1px solid var(--border); {{ ($highlight ?? false) ? 'background:rgba(34,211,238,0.03);' : '' }}" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='{{ ($highlight ?? false) ? 'rgba(34,211,238,0.03)' : 'transparent' }}'">
    <div style="display:flex; align-items:flex-start; gap:12px;">
        <span style="width:8px; height:8px; border-radius:50%; background:{{ $statusColors[$st] ?? '#6b7385' }}; margin-top:6px; flex-shrink:0;"></span>
        <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-size:14px; font-weight:500; color:var(--text); {{ $isDone ? 'text-decoration:line-through; opacity:.65;' : '' }}">{{ $task->title }}</span>
                <span style="font-size:10px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:{{ $statusColors[$st] ?? '#6b7385' }}; border:1px solid {{ $statusColors[$st] ?? '#6b7385' }}44; background:{{ $statusColors[$st] ?? '#6b7385' }}15;">{{ $statusOrder[$st] ?? $st }}</span>
            </div>
            @if($task->description)
            <div style="font-size:12px; color:var(--muted); margin-top:6px; line-height:1.5;">{{ Str::limit($task->description, 200) }}</div>
            @endif
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:8px; font-size:11px; color:var(--muted);">
                @if($task->due_date)
                <span style="font-family:var(--mono); {{ $isOverdue ? 'color:var(--danger);' : '' }}">
                    @if($isOverdue)Overdue · @endif Due {{ $task->due_date->format('d M Y') }}
                </span>
                @endif
                @if($assignee)
                <span style="display:inline-flex; align-items:center; gap:6px;">
                    <span style="width:20px; height:20px; border-radius:6px; background:rgba(34,211,238,0.15); color:var(--accent2); font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($assignee->name, 0, 1)) }}</span>
                    {{ $assignee->name }}
                </span>
                @endif
            </div>

            @if($hasActions)
            <details style="margin-top:12px;">
                <summary style="font-size:12px; color:var(--accent2); cursor:pointer; list-style:none; display:inline-flex; align-items:center; gap:6px; user-select:none;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    Comment &amp; actions
                </summary>
                <div style="margin-top:12px; padding:12px 14px; border-radius:10px; background:var(--surface2); border:1px solid var(--border);">
                    @if($canComment)
                    <form method="POST" action="{{ route('client.tasks.comments.store', [$slug, $task]) }}" style="display:flex; gap:8px; margin-bottom:10px;">
                        @csrf
                        <input name="comment" class="ptm-input" style="flex:1;" placeholder="Write a comment…" required>
                        <button type="submit" class="ptm-btn-primary" style="white-space:nowrap;">Send</button>
                    </form>
                    <form method="POST" action="{{ route('client.tasks.attachments.store', [$slug, $task]) }}" enctype="multipart/form-data" style="margin-bottom:10px;">
                        @csrf
                        <label style="display:flex; align-items:center; gap:8px; font-size:12px; color:var(--muted); cursor:pointer;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                            <input type="file" name="file" onchange="this.form.submit()" style="font-size:12px; max-width:100%;">
                        </label>
                    </form>
                    <form method="POST" action="{{ route('client.tasks.follow', [$slug, $task]) }}" style="margin-bottom:10px;">
                        @csrf
                        <button type="submit" class="ptm-btn-ghost" style="font-size:12px; padding:6px 12px;">Follow this task</button>
                    </form>
                    @endif
                    @if($canUpdate)
                    <form method="POST" action="{{ route('client.tasks.status', [$slug, $task]) }}" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        @csrf
                        <span style="font-size:11px; font-family:var(--mono); color:var(--muted);">STATUS</span>
                        <select name="status" class="ptm-select" style="font-size:12px; min-width:140px;" onchange="this.form.submit()">
                            @foreach(['todo' => 'To do', 'in_progress' => 'In progress', 'in_review' => 'In review', 'done' => 'Done'] as $val => $label)
                            <option value="{{ $val }}" @selected($task->status === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>
            </details>
            @endif
        </div>
    </div>
</div>
