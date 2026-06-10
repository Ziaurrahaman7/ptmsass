@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-employee-layout :title="$task->title">

    {{-- Header --}}
    <div style="margin-bottom:20px;">
        <a href="{{ route('employee.tasks.index', auth()->user()->company->slug) }}" style="font-size:12px; color:var(--muted); text-decoration:none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">← My Tasks</a>
        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-top:8px;">
            <div style="flex:1;">
                <div style="font-size:18px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $task->title }}</div>
                <div style="font-size:12px; color:var(--muted); margin-top:4px; font-family:var(--mono);">{{ $task->project->name }}</div>
                @if($task->description)
                <div style="font-size:13px; color:var(--muted); margin-top:8px; line-height:1.6;">{{ $task->description }}</div>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:20px;">
        {{-- Main Content --}}
        <div>
            {{-- Task Details Card --}}
            <div class="ptm-card" style="margin-bottom:16px;">
                <div style="padding:16px 18px; border-bottom:1px solid var(--border);">
                    <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Task Details</span>
                </div>
                <div style="padding:18px; display:grid; grid-template-columns:repeat(2,1fr); gap:16px;">
                    <div>
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Status</div>
                        <form method="POST" action="{{ route('employee.tasks.status', [auth()->user()->company->slug, $task]) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="ptm-select" style="font-size:12px; padding:5px 10px;">
                                @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Priority</div>
                        <span style="font-size:12px; font-family:var(--mono); padding:4px 10px; border-radius:6px; border:1px solid; display:inline-block;
                            {{ $task->priority === 'urgent' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                               ($task->priority === 'high' ? 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                               ($task->priority === 'medium' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                    <div style="grid-column:span 2;">
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Assigned To</div>
                        @if($task->assignees->count() > 0)
                        <div style="display:flex; flex-wrap:wrap; gap:6px;">
                            @foreach($task->assignees as $assignee)
                            <div style="display:flex; align-items:center; gap:6px; padding:4px 10px; background:var(--surface2); border:1px solid var(--border); border-radius:6px;">
                                <div style="width:20px; height:20px; border-radius:5px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($assignee->name,0,1)) }}</div>
                                <span style="font-size:12px; color:var(--text);">{{ $assignee->name }}</span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div style="font-size:13px; color:var(--muted);">Unassigned</div>
                        @endif
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Due Date</div>
                        <div style="font-size:13px; {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--text);' }}">{{ $task->due_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>

            @if($task->subtasks->count() > 0)
            {{-- Subtasks Section (View Only) --}}
            <div class="ptm-card" style="margin-bottom:16px;">
                <div style="padding:16px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Subtasks</span>
                        <span style="font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $task->subtasks->count() }}</span>
                    </div>
                </div>
                <div style="padding:14px;">
                    @php
                        $completedSubtasks = $task->subtasks->where('status', 'done')->count();
                        $totalSubtasks = $task->subtasks->count();
                        $progressPercent = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                    @endphp
                    <div style="margin-bottom:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:11px; color:var(--muted);">Progress</span>
                            <span style="font-size:12px; font-weight:600; font-family:var(--mono); color:#4ade80;">{{ $completedSubtasks }}/{{ $totalSubtasks }}</span>
                        </div>
                        <div style="height:4px; background:var(--border); border-radius:2px;">
                            <div style="height:100%; border-radius:2px; background:#4ade80; width:{{ $progressPercent }}%; transition:width 0.3s;"></div>
                        </div>
                    </div>
                    
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @foreach($task->subtasks as $subtask)
                        <a href="{{ route('employee.tasks.show', [auth()->user()->company->slug, $subtask]) }}" style="text-decoration:none;">
                            <div style="padding:12px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; gap:10px; transition:border-color 0.15s;" onmouseover="this.style.borderColor='var(--accent2)'" onmouseout="this.style.borderColor='var(--border)'">
                                <div style="width:18px; height:18px; border-radius:4px; border:2px solid {{ $subtask->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $subtask->status === 'done' ? '#4ade80' : 'transparent' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    @if($subtask->status === 'done')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:13px; font-weight:500; color:var(--text); {{ $subtask->status === 'done' ? 'text-decoration:line-through; opacity:0.6;' : '' }}">{{ $subtask->title }}</div>
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                        <span style="font-size:10px; font-family:var(--mono); padding:2px 6px; border-radius:4px; border:1px solid;
                                            {{ $subtask->priority === 'urgent' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                                               ($subtask->priority === 'high' ? 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                                               ($subtask->priority === 'medium' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                                            {{ ucfirst($subtask->priority) }}
                                        </span>
                                        @if($subtask->assignees->count() > 0)
                                        <div style="display:flex; align-items:center; gap:3px;">
                                            @foreach($subtask->assignees->take(3) as $assignee)
                                            <div style="width:18px; height:18px; border-radius:4px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:9px; font-weight:600; display:flex; align-items:center; justify-content:center;" title="{{ $assignee->name }}">{{ strtoupper(substr($assignee->name,0,1)) }}</div>
                                            @endforeach
                                            @if($subtask->assignees->count() > 3)
                                            <span style="font-size:10px; color:var(--muted);">+{{ $subtask->assignees->count() - 3 }}</span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Comments Section --}}
            <div class="ptm-card">
                <div style="padding:16px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Comments</span>
                    <span style="font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $task->comments->count() }}</span>
                </div>
                <div style="padding:18px;">
                    {{-- Add Comment Form --}}
                    <form method="POST" action="{{ route('employee.tasks.comments.store', [auth()->user()->company->slug, $task]) }}" style="margin-bottom:20px;">
                        @csrf
                        <textarea name="comment" rows="3" class="ptm-input" style="width:100%; resize:vertical; margin-bottom:10px;" placeholder="Add a comment..." required></textarea>
                        <button type="submit" class="ptm-btn-primary" style="font-size:12px; padding:6px 16px;">Add Comment</button>
                    </form>

                    {{-- Comments List --}}
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        @forelse($task->comments()->latest()->get() as $comment)
                        <div style="padding:12px; background:var(--surface2); border:1px solid var(--border); border-radius:10px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:28px; height:28px; border-radius:8px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($comment->user->name,0,1)) }}</div>
                                    <div>
                                        <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $comment->user->name }}</div>
                                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $comment->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                @if($comment->user_id === auth()->id())
                                <form method="POST" action="{{ route('employee.tasks.comments.destroy', [auth()->user()->company->slug, $comment]) }}" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete comment?')" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                            <div style="font-size:13px; color:var(--text); line-height:1.6;">{{ $comment->comment }}</div>
                        </div>
                        @empty
                        <div style="padding:30px; text-align:center; color:var(--muted); font-size:13px;">No comments yet. Be the first to comment!</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            {{-- Activity Log --}}
            <div class="ptm-card" style="margin-bottom:16px;">
                <div style="padding:16px 18px; border-bottom:1px solid var(--border);">
                    <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Activity</span>
                </div>
                <div style="padding:14px; max-height:400px; overflow-y:auto;">
                    @forelse($task->activities()->latest()->take(20)->get() as $activity)
                    <div style="padding:10px 0; border-bottom:1px solid var(--border2); display:flex; gap:10px;">
                        <div style="width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ strtoupper(substr($activity->user->name,0,1)) }}</div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:12px; color:var(--text); line-height:1.5;">{{ $activity->description }}</div>
                            <div style="font-size:10px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $activity->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="padding:20px; text-align:center; color:var(--muted); font-size:12px;">No activity yet</div>
                    @endforelse
                </div>
            </div>

            {{-- Attachments --}}
            <div class="ptm-card">
                <div style="padding:16px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Attachments</span>
                    <span style="font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $task->attachments->count() }}</span>
                </div>
                <div style="padding:14px;">
                    {{-- Upload Form --}}
                    <form method="POST" action="{{ route('employee.tasks.attachments.store', [auth()->user()->company->slug, $task]) }}" enctype="multipart/form-data" style="margin-bottom:16px;">
                        @csrf
                        <label style="display:flex; align-items:center; gap:8px; padding:10px 14px; background:var(--surface2); border:1px dashed var(--border2); border-radius:8px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='var(--accent2)'" onmouseout="this.style.borderColor='var(--border2)'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                            <input type="file" name="file" onchange="this.form.submit()" style="display:none;" required>
                            <span style="font-size:12px; color:var(--muted);">Click to upload file (max 10MB)</span>
                        </label>
                    </form>

                    {{-- Attachments List --}}
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @forelse($task->attachments()->latest()->get() as $attachment)
                        <div style="padding:10px 12px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
                            <div style="display:flex; align-items:center; gap:8px; min-width:0; flex:1;">
                                <div style="width:32px; height:32px; border-radius:6px; background:rgba(74,222,128,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                </div>
                                <div style="min-width:0; flex:1;">
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" style="font-size:12px; font-weight:500; color:var(--text); text-decoration:none; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $attachment->file_name }}</a>
                                    <div style="font-size:10px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ number_format($attachment->file_size / 1024, 1) }} KB · {{ $attachment->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('employee.tasks.attachments.destroy', [auth()->user()->company->slug, $attachment]) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('Delete file?')" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        @empty
                        <div style="padding:20px; text-align:center; color:var(--muted); font-size:12px;">No attachments yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-employee-layout>
