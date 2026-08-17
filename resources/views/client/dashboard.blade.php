@php $slug = auth()->user()->company->slug; @endphp

<x-client-layout title="Dashboard">

    <div style="margin-bottom:18px;">
        <div style="font-size:20px; font-weight:600; color:var(--text);">Welcome, {{ explode(' ', auth()->user()->name)[0] }}</div>
        <div style="font-size:13px; color:var(--muted); margin-top:2px;">Here's how your project{{ $projects->count() === 1 ? '' : 's' }} {{ $projects->count() === 1 ? 'is' : 'are' }} coming along.</div>
    </div>

    @if($projects->isEmpty())
        <div class="ptm-card" style="padding:50px 20px; text-align:center; color:var(--muted); font-size:13px;">
            No projects have been shared with you yet.
        </div>
    @else

    {{-- Stat cards --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px;">
        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">Projects</div>
            <div style="font-size:26px; font-weight:600; color:var(--text);">{{ $projects->count() }}</div>
        </div>
        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">Completion rate</div>
            <div style="font-size:26px; font-weight:600; color:var(--accent);">{{ $completionRate }}%</div>
        </div>
        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">In review</div>
            <div style="font-size:26px; font-weight:600; color:var(--accent2);">{{ $inReviewTasks->count() }}</div>
        </div>
        <div class="ptm-card" style="padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">Overdue</div>
            <div style="font-size:26px; font-weight:600; color:{{ $overdueTasks > 0 ? 'var(--danger)' : 'var(--text)' }};">{{ $overdueTasks }}</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1.4fr 1fr; gap:16px; align-items:start;">
        <div style="display:flex; flex-direction:column; gap:16px;">

            {{-- Projects --}}
            <div class="ptm-card">
                <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                    <span class="ptm-section-title">Your projects</span>
                </div>
                <div>
                    @foreach($projects as $project)
                    @php
                        $pct = $project->tasks_count > 0 ? round(($project->done_tasks_count / $project->tasks_count) * 100) : 0;
                        $lu = $project->latestStatusUpdate;
                    @endphp
                    <a href="{{ route('client.projects.show', [$slug, $project->id]) }}" style="display:flex; align-items:center; gap:14px; padding:14px 18px; border-bottom:1px solid var(--border); text-decoration:none; transition:background .12s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                        <div style="width:34px; height:34px; border-radius:9px; background:{{ $project->color ?: 'var(--accent2)' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $project->color ? 'white' : '#0d0f12' }}" stroke-width="2.2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $project->name }}</div>
                            <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                @if($lu)
                                <span style="display:inline-flex; align-items:center; gap:5px; font-size:11px; color:var(--muted);">
                                    <span style="width:7px; height:7px; border-radius:50%; background:{{ $lu->color }};"></span>{{ $lu->label }}
                                </span>
                                @endif
                                <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $project->done_tasks_count }}/{{ $project->tasks_count }} tasks done</span>
                            </div>
                        </div>
                        <div style="width:80px; flex-shrink:0;">
                            <div style="height:5px; background:var(--border); border-radius:3px;"><div style="height:100%; width:{{ $pct }}%; background:var(--accent); border-radius:3px;"></div></div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Tasks in review --}}
            <div class="ptm-card">
                <div style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <span class="ptm-section-title">Tasks awaiting your review</span>
                    <span style="font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $inReviewTasks->count() }}</span>
                </div>
                @if($inReviewTasks->isEmpty())
                    <div style="padding:24px 18px; text-align:center; color:var(--muted); font-size:12px;">Nothing waiting on you right now.</div>
                @else
                <div>
                    @foreach($inReviewTasks as $task)
                    <a href="{{ route('client.projects.show', [$slug, $task->project_id]) }}" style="display:flex; align-items:center; gap:10px; padding:11px 18px; border-bottom:1px solid var(--border); text-decoration:none;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                        <span style="width:7px; height:7px; border-radius:50%; background:var(--accent2); flex-shrink:0;"></span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $task->title }}</div>
                            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $task->project->name }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Recent status updates --}}
        <div class="ptm-card">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Recent updates</span>
            </div>
            @if($recentStatusUpdates->isEmpty())
                <div style="padding:24px 18px; text-align:center; color:var(--muted); font-size:12px;">No status updates posted yet.</div>
            @else
            <div style="padding:6px 18px 14px;">
                @foreach($recentStatusUpdates as $su)
                <div style="display:flex; gap:10px; padding:12px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                    <span style="width:8px; height:8px; border-radius:50%; background:{{ $su->color }}; margin-top:5px; flex-shrink:0;"></span>
                    <div style="min-width:0;">
                        <div style="font-size:12px; color:var(--text);"><strong>{{ $su->label }}</strong> · {{ $su->project->name }}</div>
                        <div style="font-size:11px; color:var(--muted); margin-top:2px;">{{ $su->user->name }} · {{ $su->created_at->diffForHumans() }}</div>
                        @if($su->message)<div style="font-size:12px; color:var(--muted); margin-top:5px; line-height:1.5;">{{ $su->message }}</div>@endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

</x-client-layout>
