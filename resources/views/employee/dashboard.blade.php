@php $slug = auth()->user()->company->slug; @endphp

<x-employee-layout title="My Dashboard">

    <div style="margin-bottom:18px;">
        <div style="font-size:13px; color:var(--muted);">Welcome back, <span style="color:var(--text); font-weight:500;">{{ auth()->user()->name }}</span> 👋</div>
    </div>

    {{-- Top Stats Cards --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px;">
        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Assigned Tasks</div>
            <div style="font-size:28px; font-weight:600; letter-spacing:-0.5px; color:#22d3ee; margin-bottom:8px;">{{ $totalAssigned }}</div>
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $done }} completed · {{ $inProgress }} in progress</div>
        </div>

        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Completion Rate</div>
            <div style="font-size:28px; font-weight:600; letter-spacing:-0.5px; color:#4ade80; margin-bottom:8px;">{{ $completionRate }}%</div>
            <div style="height:3px; background:var(--border); border-radius:2px; overflow:hidden;">
                <div style="height:100%; background:#4ade80; border-radius:2px; width:{{ $completionRate }}%; transition:width 0.3s;"></div>
            </div>
        </div>

        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">In Review</div>
            <div style="font-size:28px; font-weight:600; letter-spacing:-0.5px; color:#a78bfa; margin-bottom:8px;">{{ $inReview }}</div>
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">Pending review</div>
        </div>

        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Overdue</div>
            <div style="font-size:28px; font-weight:600; letter-spacing:-0.5px; color:#f87171; margin-bottom:8px;">{{ $overdue }}</div>
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $dueToday > 0 ? $dueToday . ' due today' : 'None today' }}</div>
        </div>
    </div>

    {{-- Status & Priority Breakdown --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
        {{-- Task Status --}}
        <div class="ptm-card">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">My Task Status</span>
            </div>
            <div style="padding:18px; display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#6b7385;"></div>
                        <span style="font-size:13px; color:var(--text);">To Do</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#6b7385; border-radius:3px; width:{{ $totalAssigned > 0 ? ($todoTasks / $totalAssigned * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $todoTasks }}</span>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#22d3ee;"></div>
                        <span style="font-size:13px; color:var(--text);">In Progress</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#22d3ee; border-radius:3px; width:{{ $totalAssigned > 0 ? ($inProgress / $totalAssigned * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $inProgress }}</span>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#a78bfa;"></div>
                        <span style="font-size:13px; color:var(--text);">In Review</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#a78bfa; border-radius:3px; width:{{ $totalAssigned > 0 ? ($inReview / $totalAssigned * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $inReview }}</span>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#4ade80;"></div>
                        <span style="font-size:13px; color:var(--text);">Done</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#4ade80; border-radius:3px; width:{{ $totalAssigned > 0 ? ($done / $totalAssigned * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $done }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Priority Breakdown --}}
        <div class="ptm-card">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Active Tasks by Priority</span>
            </div>
            <div style="padding:18px; display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#f87171;"></div>
                        <span style="font-size:13px; color:var(--text);">Urgent</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#f87171; border-radius:3px; width:{{ ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) > 0 ? ($urgentTasks / ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $urgentTasks }}</span>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#fb923c;"></div>
                        <span style="font-size:13px; color:var(--text);">High</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#fb923c; border-radius:3px; width:{{ ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) > 0 ? ($highPriorityTasks / ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $highPriorityTasks }}</span>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#fbbf24;"></div>
                        <span style="font-size:13px; color:var(--text);">Medium</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#fbbf24; border-radius:3px; width:{{ ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) > 0 ? ($mediumPriorityTasks / ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $mediumPriorityTasks }}</span>
                    </div>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:8px; height:8px; border-radius:50%; background:#6b7385;"></div>
                        <span style="font-size:13px; color:var(--text);">Low</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:120px;">
                            <div style="height:100%; background:#6b7385; border-radius:3px; width:{{ ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) > 0 ? ($lowPriorityTasks / ($urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks) * 100) : 0 }}%;"></div>
                        </div>
                        <span style="font-size:13px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:30px; text-align:right;">{{ $lowPriorityTasks }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deadlines & Activities --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
        {{-- Upcoming Deadlines --}}
        <div class="ptm-card">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span class="ptm-section-title">My Upcoming Deadlines</span>
                @if($overdue > 0)
                <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; background:rgba(248,113,113,0.08); border:1px solid rgba(248,113,113,0.3); color:#f87171;">{{ $overdue }} overdue</span>
                @endif
            </div>
            <div>
                @if($dueToday > 0)
                <div style="padding:10px 18px; background:rgba(248,113,113,0.05); border-bottom:1px solid var(--border);">
                    <div style="font-size:12px; font-weight:600; color:#f87171;">{{ $dueToday }} task(s) due today!</div>
                </div>
                @endif
                
                @forelse($upcomingTasks as $task)
                <div style="padding:10px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px;">
                    <div style="min-width:0; flex:1;">
                        <a href="{{ route('employee.tasks.show', [$slug, $task]) }}" style="font-size:13px; font-weight:500; color:var(--text); text-decoration:none; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $task->title }}</a>
                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $task->project->name }}</div>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <div style="font-size:11px; font-weight:600; font-family:var(--mono); color:{{ $task->due_date->diffInDays(today()) <= 1 ? '#f87171' : 'var(--muted)' }};">{{ $task->due_date->format('d M') }}</div>
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono);">{{ $task->due_date->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:24px; text-align:center; color:var(--muted); font-size:13px;">No upcoming deadlines in the next 7 days</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="ptm-card">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Recent Activities</span>
            </div>
            <div style="max-height:400px; overflow-y:auto;">
                @forelse($recentActivities as $activity)
                <div style="padding:10px 18px; border-bottom:1px solid var(--border); display:flex; gap:10px;">
                    <div style="width:28px; height:28px; border-radius:8px; background:rgba(34,211,238,0.15); color:#22d3ee; font-size:11px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                        {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:12px; color:var(--text); line-height:1.5;">{{ $activity->description }}</div>
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); margin-top:3px;">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:24px; text-align:center; color:var(--muted); font-size:13px;">No activities yet</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Tasks --}}
    <div class="ptm-card">
        <div style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <span class="ptm-section-title">My Recent Tasks</span>
            <a href="{{ route('employee.tasks.index', $slug) }}" style="font-size:12px; color:var(--accent2); text-decoration:none; font-weight:500;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--accent2)'">View all →</a>
        </div>
        <div>
            @forelse($myTasks as $task)
            <div style="padding:12px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <div style="min-width:0; flex:1;">
                    <a href="{{ route('employee.tasks.show', [$slug, $task]) }}" style="font-size:13px; font-weight:500; color:var(--text); text-decoration:none; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $task->title }}</a>
                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">
                        {{ $task->project->name }}
                        @if($task->due_date)· <span style="{{ $task->due_date->isPast() && $task->status !== 'done' ? 'color:#f87171;' : '' }}">Due {{ $task->due_date->format('d M Y') }}</span>@endif
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                        {{ $task->priority === 'urgent' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' :
                           ($task->priority === 'high' ? 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);' :
                           ($task->priority === 'medium' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                        {{ $task->status === 'done' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                           ($task->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                           ($task->status === 'in_review' ? 'color:#a78bfa; border-color:rgba(167,139,250,0.3); background:rgba(167,139,250,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                        {{ ucfirst(str_replace('_',' ',$task->status)) }}
                    </span>
                </div>
            </div>
            @empty
            <div style="padding:40px; text-align:center; color:var(--muted); font-size:13px;">No tasks assigned to you yet.</div>
            @endforelse
        </div>
    </div>

</x-employee-layout>
