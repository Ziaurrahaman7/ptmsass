@php $slug = auth()->user()->company->slug; @endphp

<x-company-layout title="Dashboard">

    {{-- Top Metrics --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px;">
        <div class="ptm-card" style="padding:18px 20px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:10px;">Overall Progress</div>
            <div style="font-size:32px; font-weight:600; letter-spacing:-0.5px; color:#4ade80; margin-bottom:8px;">{{ $completionRate }}%</div>
            <div style="height:4px; background:var(--border); border-radius:2px; overflow:hidden;">
                <div style="height:100%; background:#4ade80; border-radius:2px; width:{{ $completionRate }}%; transition:width 0.3s;"></div>
            </div>
        </div>

        <div class="ptm-card" style="padding:18px 20px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:10px;">Projects</div>
            <div style="font-size:32px; font-weight:600; letter-spacing:-0.5px; color:#22d3ee;">{{ $totalProjects }}</div>
            <div style="font-size:12px; color:var(--muted); margin-top:4px;">{{ $activeProjects }} active · {{ $completedProjects }} done</div>
        </div>

        <div class="ptm-card" style="padding:18px 20px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:10px;">Tasks</div>
            <div style="font-size:32px; font-weight:600; letter-spacing:-0.5px; color:#a78bfa;">{{ $totalTasks }}</div>
            <div style="font-size:12px; color:var(--muted); margin-top:4px;">{{ $doneTasks }} done · {{ $inProgressTasks}} in progress</div>
        </div>

        <div class="ptm-card" style="padding:18px 20px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:10px;">Team</div>
            <div style="font-size:32px; font-weight:600; letter-spacing:-0.5px; color:#fbbf24;">{{ $totalMembers }}</div>
            <div style="font-size:12px; color:var(--muted); margin-top:4px;">{{ $activeMembers }} working</div>
        </div>
    </div>

    {{-- Project Timeline --}}
    <div class="ptm-card" style="margin-bottom:20px;">
        <div style="padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <span class="ptm-section-title">Project Timeline</span>
            <a href="{{ route('company.projects.index', $slug) }}" style="font-size:12px; color:var(--accent2); text-decoration:none; font-weight:500;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--accent2)'">View all projects →</a>
        </div>
        <div style="padding:20px 22px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:10px;">
                @foreach($recentProjects->take(6) as $index => $project)
                @php
                    $colors = ['#4ade80', '#22d3ee', '#a78bfa', '#fbbf24', '#f87171', '#fb923c'];
                    $color = $colors[$index % 6];
                    $progress = $project->progressPercentage();
                @endphp
                <div style="border:1px solid var(--border); border-radius:10px; overflow:hidden; cursor:pointer; transition:all 0.15s;" onmouseover="this.style.borderColor='{{ $color }}44'" onmouseout="this.style.borderColor='var(--border)'" onclick="window.location='{{ route('company.projects.show', [$slug, $project]) }}'">
                    <div style="padding:12px 14px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="font-size:13px; font-weight:600; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1;">{{ $project->name }}</div>
                        <div style="width:10px; height:10px; border-radius:50%; background:{{ $project->status === 'completed' ? '#4ade80' : ($project->status === 'in_progress' ? $color : '#6b7385') }}; flex-shrink:0;"></div>
                    </div>
                    <div style="padding:0 14px 4px; font-size:11px; color:var(--muted); line-height:1.4; min-height:32px;">{{ Str::limit($project->description ?? 'No description', 50) }}</div>
                    <div style="padding:0 14px 12px; display:flex; gap:4px;">
                        @for($i = 0; $i < 4; $i++)
                            <div style="flex:1; height:4px; border-radius:2px; background:{{ $progress >= ($i + 1) * 25 ? $color : 'var(--border)' }}; transition:all 0.2s;"></div>
                        @endfor
                    </div>
                    <div style="padding:8px 14px; background:var(--surface2); border-top:1px solid var(--border); font-size:11px; color:var(--muted); font-family:var(--mono); display:flex; justify-content:space-between;">
                        <span style="color:{{ $color }}">{{ $progress }}%</span>
                        <span>{{ $project->tasks_count }} tasks</span>
                    </div>
                </div>
                @endforeach
                
                @if($recentProjects->count() === 0)
                <div style="grid-column:1/-1; padding:40px; text-align:center; color:var(--muted); font-size:13px;">
                    No projects yet. <a href="{{ route('company.projects.create', $slug) }}" style="color:var(--accent); text-decoration:none;">Create one →</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Monthly Progress Timeline --}}
    <div class="ptm-card" style="margin-bottom:20px;">
        <div style="padding:18px 22px; border-bottom:1px solid var(--border);">
            <span class="ptm-section-title">Monthly Progress - {{ now()->format('Y') }}</span>
        </div>
        <div style="padding:20px 22px;">
            <div style="display:grid; grid-template-columns:repeat(6, 1fr); gap:10px;">
                @php
                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    $currentMonth = now()->month - 1;
                    $monthColors = ['#4ade80', '#22d3ee', '#a78bfa', '#fbbf24', '#f87171', '#fb923c'];
                @endphp
                @for($m = 0; $m < 6; $m++)
                    @php
                        $monthIndex = ($currentMonth - 5 + $m + 12) % 12;
                        $monthName = $months[$monthIndex];
                        $color = $monthColors[$m];
                        
                        // Calculate tasks for this month (mock data - you can enhance this)
                        $monthStart = now()->subMonths(5 - $m)->startOfMonth();
                        $monthEnd = now()->subMonths(5 - $m)->endOfMonth();
                        
                        $monthTasks = \App\Models\Task::where('company_id', auth()->user()->company_id)
                            ->whereBetween('created_at', [$monthStart, $monthEnd])
                            ->count();
                        
                        $monthCompleted = \App\Models\Task::where('company_id', auth()->user()->company_id)
                            ->whereBetween('created_at', [$monthStart, $monthEnd])
                            ->where('status', 'done')
                            ->count();
                        
                        $monthProgress = $monthTasks > 0 ? round(($monthCompleted / $monthTasks) * 100) : 0;
                        $isCurrent = $m === 5;
                    @endphp
                    <div style="border:1px solid {{ $isCurrent ? $color.'66' : 'var(--border)' }}; border-radius:10px; overflow:hidden; transition:all 0.15s; {{ $isCurrent ? 'box-shadow: 0 0 0 1px '.$color.'33;' : '' }}" onmouseover="this.style.borderColor='{{ $color }}66'" onmouseout="this.style.borderColor='{{ $isCurrent ? $color.'66' : 'var(--border)' }}'">
                        <div style="padding:10px 12px; display:flex; align-items:center; justify-content:space-between;">
                            <div style="font-size:13px; font-weight:600; color:{{ $isCurrent ? $color : 'var(--text)' }}; font-family:var(--mono);">{{ $monthName }}</div>
                            <div style="width:8px; height:8px; border-radius:50%; background:{{ $monthProgress > 0 ? $color : 'var(--border2)' }};"></div>
                        </div>
                        <div style="padding:0 12px 4px; font-size:11px; color:var(--muted);">{{ $monthTasks }} tasks</div>
                        <div style="padding:0 12px 10px; display:flex; gap:4px;">
                            @for($w = 0; $w < 4; $w++)
                                <div style="flex:1; height:4px; border-radius:2px; background:{{ $monthProgress >= ($w + 1) * 25 ? $color : 'var(--border)' }}; transition:all 0.2s;"></div>
                            @endfor
                        </div>
                        <div style="padding:6px 12px; background:{{ $isCurrent ? $color.'11' : 'var(--surface2)' }}; border-top:1px solid var(--border); font-size:10px; color:{{ $isCurrent ? $color : 'var(--muted)' }}; font-family:var(--mono); text-align:center;">
                            {{ $monthProgress }}% done
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Task Breakdown & Priority --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
        {{-- Task Status Breakdown --}}
        <div class="ptm-card">
            <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Task Status</span>
            </div>
            <div style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @php
                    $statuses = [
                        ['label' => 'To Do', 'value' => $todoTasks, 'color' => '#6b7385'],
                        ['label' => 'In Progress', 'value' => $inProgressTasks, 'color' => '#22d3ee'],
                        ['label' => 'In Review', 'value' => $inReviewTasks, 'color' => '#a78bfa'],
                        ['label' => 'Done', 'value' => $doneTasks, 'color' => '#4ade80'],
                    ];
                @endphp
                @foreach($statuses as $status)
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px; flex:1;">
                        <div style="width:10px; height:10px; border-radius:50%; background:{{ $status['color'] }}; flex-shrink:0;"></div>
                        <span style="font-size:13px; color:var(--text);">{{ $status['label'] }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:140px;">
                            <div style="height:100%; background:{{ $status['color'] }}; border-radius:3px; width:{{ $totalTasks > 0 ? ($status['value'] / $totalTasks * 100) : 0 }}%; transition:width 0.3s;"></div>
                        </div>
                        <span style="font-size:14px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:35px; text-align:right;">{{ $status['value'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Priority Breakdown --}}
        <div class="ptm-card">
            <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Active by Priority</span>
            </div>
            <div style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @php
                    $priorities = [
                        ['label' => 'Urgent', 'value' => $urgentTasks, 'color' => '#f87171'],
                        ['label' => 'High', 'value' => $highPriorityTasks, 'color' => '#fb923c'],
                        ['label' => 'Medium', 'value' => $mediumPriorityTasks, 'color' => '#fbbf24'],
                        ['label' => 'Low', 'value' => $lowPriorityTasks, 'color' => '#6b7385'],
                    ];
                    $totalActiveTasks = $urgentTasks + $highPriorityTasks + $mediumPriorityTasks + $lowPriorityTasks;
                @endphp
                @foreach($priorities as $priority)
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px; flex:1;">
                        <div style="width:10px; height:10px; border-radius:50%; background:{{ $priority['color'] }}; flex-shrink:0;"></div>
                        <span style="font-size:13px; color:var(--text);">{{ $priority['label'] }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="height:6px; background:var(--border); border-radius:3px; width:140px;">
                            <div style="height:100%; background:{{ $priority['color'] }}; border-radius:3px; width:{{ $totalActiveTasks > 0 ? ($priority['value'] / $totalActiveTasks * 100) : 0 }}%; transition:width 0.3s;"></div>
                        </div>
                        <span style="font-size:14px; font-weight:600; font-family:var(--mono); color:var(--text); min-width:35px; text-align:right;">{{ $priority['value'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Deadlines & Top Performers --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
        {{-- Upcoming Deadlines --}}
        <div class="ptm-card">
            <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span class="ptm-section-title">Upcoming Deadlines</span>
                @if($overdueTasks > 0)
                <span style="font-size:11px; font-family:var(--mono); padding:4px 10px; border-radius:6px; background:rgba(248,113,113,0.08); border:1px solid rgba(248,113,113,0.3); color:#f87171;">{{ $overdueTasks }} overdue</span>
                @endif
            </div>
            <div>
                @if($dueTodayTasks > 0)
                <div style="padding:10px 20px; background:rgba(248,113,113,0.05); border-bottom:1px solid var(--border);">
                    <div style="font-size:13px; font-weight:600; color:#f87171;">⚠ {{ $dueTodayTasks }} task(s) due today!</div>
                </div>
                @endif
                
                @forelse($upcomingTasks as $task)
                <div style="padding:12px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <div style="min-width:0; flex:1;">
                        <a href="{{ route('company.tasks.show', [$slug, $task]) }}" style="font-size:13px; font-weight:500; color:var(--text); text-decoration:none; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $task->title }}</a>
                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:3px;">{{ $task->project->name }} · {{ $task->assignee?->name ?? 'Unassigned' }}</div>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <div style="font-size:12px; font-weight:600; font-family:var(--mono); color:{{ $task->due_date->diffInDays(today()) <= 1 ? '#f87171' : 'var(--muted)' }};">{{ $task->due_date->format('d M') }}</div>
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono);">{{ $task->due_date->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:30px 20px; text-align:center; color:var(--muted); font-size:13px;">No upcoming deadlines in next 7 days</div>
                @endforelse
            </div>
        </div>

        {{-- Top Performers --}}
        <div class="ptm-card">
            <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Top Performers</span>
            </div>
            <div>
                @forelse($topPerformers as $index => $member)
                @php
                    $avatarColors = [
                        'bg' => ['rgba(74,222,128,0.15)', 'rgba(34,211,238,0.15)', 'rgba(167,139,250,0.15)', 'rgba(251,191,36,0.15)', 'rgba(248,113,113,0.15)'],
                        'text' => ['#4ade80', '#22d3ee', '#a78bfa', '#fbbf24', '#f87171']
                    ];
                    $colorIndex = $index % 5;
                @endphp
                <div style="padding:14px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:14px;">
                    <div style="font-size:14px; font-weight:600; font-family:var(--mono); color:var(--muted); min-width:24px;">{{ $index + 1 }}.</div>
                    <div style="width:36px; height:36px; border-radius:8px; background:{{ $avatarColors['bg'][$colorIndex] }}; color:{{ $avatarColors['text'][$colorIndex] }}; font-size:14px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $member->name }}</div>
                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $member->email }}</div>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <div style="font-size:18px; font-weight:600; color:#4ade80;">{{ $member->completed_tasks_count }}</div>
                        <div style="font-size:10px; color:var(--muted); font-family:var(--mono);">completed</div>
                    </div>
                </div>
                @empty
                <div style="padding:30px 20px; text-align:center; color:var(--muted); font-size:13px;">No completed tasks yet</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="ptm-card">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
            <span class="ptm-section-title">Recent Activities</span>
        </div>
        <div style="max-height:450px; overflow-y:auto;">
            @forelse($recentActivities as $activity)
            <div style="padding:12px 20px; border-bottom:1px solid var(--border); display:flex; gap:12px;">
                <div style="width:32px; height:32px; border-radius:8px; background:rgba(74,222,128,0.15); color:#4ade80; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                    {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; color:var(--text); line-height:1.6;">{{ $activity->description }}</div>
                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:4px;">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="padding:30px 20px; text-align:center; color:var(--muted); font-size:13px;">No activities yet</div>
            @endforelse
        </div>
    </div>

</x-company-layout>
