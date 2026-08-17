@php
    $slug = auth()->user()->company->slug;
    $latestStatus = $statusUpdates->first();
    $pct = $tasks->count() > 0 ? round(($tasks->where('status','done')->count() / $tasks->count()) * 100) : 0;
    $statusOrder = ['in_review' => 'In review', 'todo' => 'To do', 'in_progress' => 'In progress', 'done' => 'Done'];
    $statusColors = ['in_review' => '#a78bfa', 'todo' => '#6b7385', 'in_progress' => '#22d3ee', 'done' => '#4ade80'];
@endphp

<x-client-layout :title="$project->name">

    <a href="{{ route('client.dashboard', $slug) }}" style="display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--muted); text-decoration:none; margin-bottom:14px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Dashboard
    </a>

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:36px; height:36px; border-radius:9px; background:{{ $project->color ?: 'var(--accent2)' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $project->color ? 'white' : '#0d0f12' }}" stroke-width="2.2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            </div>
            <div>
                <div style="font-size:19px; font-weight:600; color:var(--text);">{{ $project->name }}</div>
                @if($project->description)<div style="font-size:12px; color:var(--muted); margin-top:2px;">{{ $project->description }}</div>@endif
            </div>
        </div>
        @if($latestStatus)
        <span style="display:inline-flex; align-items:center; gap:7px; font-size:12px; color:var(--text); background:var(--surface2); border:1px solid var(--border); border-radius:20px; padding:6px 14px;">
            <span style="width:8px; height:8px; border-radius:50%; background:{{ $latestStatus->color }};"></span>
            {{ $latestStatus->label }}
        </span>
        @endif
    </div>

    <div style="display:grid; grid-template-columns:1.4fr 1fr; gap:16px; align-items:start;">
        <div style="display:flex; flex-direction:column; gap:16px;">

            {{-- Progress --}}
            <div class="ptm-card" style="padding:18px 20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <span class="ptm-section-title">Overall progress</span>
                    <span style="font-size:20px; font-weight:600; color:var(--accent);">{{ $pct }}%</span>
                </div>
                <div style="height:6px; background:var(--border); border-radius:3px;"><div style="height:100%; width:{{ $pct }}%; background:var(--accent); border-radius:3px;"></div></div>
                <div style="display:flex; gap:18px; margin-top:14px; font-size:12px; color:var(--muted);">
                    <span>{{ $tasks->count() }} total tasks</span>
                    <span>{{ $tasks->where('status','done')->count() }} done</span>
                    <span style="color:var(--accent2);">{{ $tasksByStatus['in_review']->count() }} in review</span>
                </div>
            </div>

            {{-- Tasks in review (highlighted) --}}
            @if($tasksByStatus['in_review']->isNotEmpty())
            <div class="ptm-card" style="border-color:rgba(34,211,238,0.35);">
                <div style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                    <span style="width:7px; height:7px; border-radius:50%; background:var(--accent2);"></span>
                    <span class="ptm-section-title" style="color:var(--accent2);">Awaiting your review</span>
                </div>
                <div>
                    @foreach($tasksByStatus['in_review'] as $task)
                    <div style="padding:12px 18px; border-bottom:1px solid var(--border);">
                        <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $task->title }}</div>
                        @if($task->description)<div style="font-size:12px; color:var(--muted); margin-top:4px; line-height:1.5;">{{ $task->description }}</div>@endif
                        <div style="display:flex; align-items:center; gap:10px; margin-top:6px;">
                            @if($task->due_date)<span style="font-size:11px; color:var(--muted); font-family:var(--mono);">Due {{ $task->due_date->format('d M Y') }}</span>@endif
                            @if($task->assignee)<span style="font-size:11px; color:var(--muted);">{{ $task->assignee->name }}</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- All tasks, grouped by status --}}
            <div class="ptm-card">
                <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                    <span class="ptm-section-title">All tasks</span>
                </div>
                <div>
                    @foreach(['in_review','in_progress','todo','done'] as $st)
                        @if($tasksByStatus[$st]->isNotEmpty())
                        <div style="padding:9px 18px; background:var(--surface2); display:flex; align-items:center; gap:8px;">
                            <span style="width:7px; height:7px; border-radius:50%; background:{{ $statusColors[$st] }};"></span>
                            <span style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:.05em;">{{ $statusOrder[$st] }}</span>
                            <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $tasksByStatus[$st]->count() }}</span>
                        </div>
                        @foreach($tasksByStatus[$st] as $task)
                        <div style="display:flex; align-items:center; gap:10px; padding:10px 18px; border-bottom:1px solid var(--border);">
                            <span style="font-size:13px; color:var(--text); flex:1; min-width:0; {{ $st === 'done' ? 'text-decoration:line-through; opacity:.6;' : '' }}">{{ $task->title }}</span>
                            @if($task->due_date)<span style="font-size:11px; color:var(--muted); font-family:var(--mono); flex-shrink:0;">{{ $task->due_date->format('d M') }}</span>@endif
                        </div>
                        @endforeach
                        @endif
                    @endforeach
                    @if($tasks->isEmpty())
                    <div style="padding:24px 18px; text-align:center; color:var(--muted); font-size:12px;">No tasks yet.</div>
                    @endif
                </div>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">

            {{-- Milestones --}}
            @if($milestones->isNotEmpty())
            <div class="ptm-card">
                <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                    <span class="ptm-section-title">Milestones</span>
                </div>
                <div style="padding:6px 18px 14px;">
                    @foreach($milestones as $m)
                    <div style="display:flex; align-items:center; gap:10px; padding:9px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                        <div style="width:16px; height:16px; border-radius:50%; border:2px solid {{ $m->status === 'done' ? 'var(--accent)' : 'var(--border2)' }}; background:{{ $m->status === 'done' ? 'var(--accent)' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                            @if($m->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>@endif
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:13px; color:var(--text); {{ $m->status === 'done' ? 'text-decoration:line-through; opacity:.6;' : '' }}">{{ $m->title }}</div>
                            @if($m->due_date)<div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:1px;">{{ $m->due_date->format('d M Y') }}</div>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Key resources --}}
            @if($resources->isNotEmpty())
            <div class="ptm-card">
                <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                    <span class="ptm-section-title">Key resources</span>
                </div>
                <div style="padding:6px 18px 14px;">
                    @foreach($resources as $r)
                    <div style="padding:9px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                        @if($r->type === 'brief')
                            <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $r->title }}</div>
                            @if($r->content)<div style="font-size:12px; color:var(--muted); margin-top:4px; line-height:1.5;">{{ $r->content }}</div>@endif
                        @else
                            <a href="{{ $r->url }}" target="_blank" style="font-size:13px; color:var(--accent2); text-decoration:none; display:flex; align-items:center; gap:6px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.5.5l2-2a5 5 0 00-7-7l-1 1"/><path d="M14 11a5 5 0 00-7.5-.5l-2 2a5 5 0 007 7l1-1"/></svg>
                                {{ $r->title }}
                            </a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Status update history --}}
            <div class="ptm-card">
                <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                    <span class="ptm-section-title">Status history</span>
                </div>
                @if($statusUpdates->isEmpty())
                    <div style="padding:24px 18px; text-align:center; color:var(--muted); font-size:12px;">No status updates posted yet.</div>
                @else
                <div style="padding:6px 18px 14px;">
                    @foreach($statusUpdates as $su)
                    <div style="display:flex; gap:10px; padding:11px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $su->color }}; margin-top:5px; flex-shrink:0;"></span>
                        <div style="min-width:0;">
                            <div style="font-size:12px; color:var(--text);"><strong>{{ $su->label }}</strong> · <span style="color:var(--muted);">{{ $su->created_at->diffForHumans() }}</span></div>
                            @if($su->message)<div style="font-size:12px; color:var(--muted); margin-top:3px; line-height:1.5;">{{ $su->message }}</div>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</x-client-layout>
