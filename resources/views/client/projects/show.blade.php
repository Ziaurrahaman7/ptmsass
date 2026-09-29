@php
    $slug = auth()->user()->company->slug;
    $latestStatus = $statusUpdates->first();
    $doneCount = $tasks->where('status', 'done')->count();
    $pct = $tasks->count() > 0 ? round(($doneCount / $tasks->count()) * 100) : 0;
    $inReviewCount = $tasksByStatus['in_review']->count();
    $overdueCount = $tasks->filter(fn ($t) => $t->status !== 'done' && $t->due_date && $t->due_date->isPast())->count();
    $statusOrder = ['in_review' => 'In review', 'in_progress' => 'In progress', 'todo' => 'To do', 'done' => 'Done'];
    $statusColors = ['in_review' => '#a78bfa', 'todo' => '#6b7385', 'in_progress' => '#22d3ee', 'done' => '#4ade80'];
    $mode = auth()->user()->clientMode($project);
    $modeMeta = match ($mode) {
        'collaborate' => ['label' => 'Collaborate', 'border' => 'rgba(34,211,238,0.4)', 'bg' => 'rgba(34,211,238,0.1)', 'color' => '#22d3ee', 'hint' => 'You can comment and upload files on tasks.'],
        'contribute' => ['label' => 'Contribute', 'border' => 'rgba(74,222,128,0.4)', 'bg' => 'rgba(74,222,128,0.1)', 'color' => '#4ade80', 'hint' => 'You can comment, upload files, and update task status.'],
        'approve' => ['label' => 'Approve', 'border' => 'rgba(167,139,250,0.4)', 'bg' => 'rgba(167,139,250,0.1)', 'color' => '#a78bfa', 'hint' => 'You can review work, comment, and record approval decisions.'],
        default => ['label' => 'View only', 'border' => 'var(--border2)', 'bg' => 'var(--surface2)', 'color' => 'var(--muted)', 'hint' => 'Read-only access to this project. Ask your project team to enable collaboration if needed.'],
    };
    $projectColor = $project->color ?: '#22d3ee';
@endphp

<x-client-layout :title="$project->name">

<div style="margin-bottom:20px;">
    <a href="{{ route('client.dashboard', $slug) }}" style="font-size:12px; color:var(--muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:12px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        All projects
    </a>

    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:14px; min-width:0;">
            <div style="width:52px; height:52px; border-radius:14px; background:{{ $projectColor }}; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 8px 24px {{ $projectColor }}33;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="{{ $project->color ? 'white' : '#0d0f12' }}" stroke-width="2.2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            </div>
            <div style="min-width:0;">
                <div style="font-size:20px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $project->name }}</div>
                @if($project->description)
                <div style="font-size:13px; color:var(--muted); margin-top:4px; line-height:1.45; max-width:520px;">{{ $project->description }}</div>
                @endif
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:10px;">
                    <span style="font-size:10px; font-family:var(--mono); padding:5px 10px; border-radius:8px; color:{{ $modeMeta['color'] }}; border:1px solid {{ $modeMeta['border'] }}; background:{{ $modeMeta['bg'] }};">Your access · {{ $modeMeta['label'] }}</span>
                    @if($latestStatus)
                    <span style="display:inline-flex; align-items:center; gap:6px; font-size:11px; color:var(--text); background:var(--surface2); border:1px solid var(--border); border-radius:8px; padding:5px 10px;">
                        <span style="width:7px; height:7px; border-radius:50%; background:{{ $latestStatus->color }};"></span>
                        {{ $latestStatus->label }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="ptm-card" style="padding:14px 18px; min-width:160px; text-align:center;">
            <div style="font-size:28px; font-weight:600; color:var(--accent); line-height:1;">{{ $pct }}%</div>
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:4px;">complete</div>
            <div style="height:5px; background:var(--border); border-radius:3px; margin-top:10px;"><div style="height:100%; width:{{ $pct }}%; background:var(--accent); border-radius:3px;"></div></div>
        </div>
    </div>
</div>

<div class="ptm-card" style="padding:12px 16px; margin-bottom:16px; border-color:{{ $modeMeta['border'] }}; background:linear-gradient(135deg, {{ $modeMeta['bg'] }} 0%, transparent 100%); font-size:12px; color:var(--muted); line-height:1.5;">
    {{ $modeMeta['hint'] }}
</div>

<div class="client-stat-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:18px;">
    <div class="ptm-card" style="padding:14px 16px;">
        <div class="ptm-section-title" style="margin-bottom:6px;">Tasks</div>
        <div style="font-size:22px; font-weight:600; color:var(--text);">{{ $tasks->count() }}</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div class="ptm-section-title" style="margin-bottom:6px;">Done</div>
        <div style="font-size:22px; font-weight:600; color:var(--accent);">{{ $doneCount }}</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div class="ptm-section-title" style="margin-bottom:6px;">In review</div>
        <div style="font-size:22px; font-weight:600; color:var(--accent2);">{{ $inReviewCount }}</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div class="ptm-section-title" style="margin-bottom:6px;">Overdue</div>
        <div style="font-size:22px; font-weight:600; color:{{ $overdueCount > 0 ? 'var(--danger)' : 'var(--text)' }};">{{ $overdueCount }}</div>
    </div>
</div>

<div class="client-project-grid" style="display:grid; grid-template-columns:1.45fr 1fr; gap:16px; align-items:start;">
    <div style="display:flex; flex-direction:column; gap:16px;">

        @if($tasksByStatus['in_review']->isNotEmpty())
        <div class="ptm-card" style="overflow:hidden; border-color:rgba(34,211,238,0.35);">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:rgba(34,211,238,0.04);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="width:8px; height:8px; border-radius:50%; background:var(--accent2);"></span>
                    <span style="font-size:13px; font-weight:600; color:var(--accent2);">Awaiting your review</span>
                </div>
                <span style="font-size:11px; font-family:var(--mono); color:var(--muted);">{{ $inReviewCount }}</span>
            </div>
            @foreach($tasksByStatus['in_review'] as $task)
            @include('client.projects.partials.task-row', ['task' => $task, 'slug' => $slug, 'statusColors' => $statusColors, 'statusOrder' => $statusOrder, 'highlight' => true])
            @endforeach
        </div>
        @endif

        <div class="ptm-card" style="overflow:hidden;">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span class="ptm-section-title">All tasks</span>
                <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $tasks->count() }} total</span>
            </div>
            @php
                $listStatuses = $inReviewCount > 0
                    ? ['in_progress', 'todo', 'done']
                    : ['in_review', 'in_progress', 'todo', 'done'];
            @endphp
            @foreach($listStatuses as $st)
                @if($tasksByStatus[$st]->isNotEmpty())
                <div style="padding:8px 18px; background:var(--surface2); display:flex; align-items:center; gap:8px; border-bottom:1px solid var(--border);">
                    <span style="width:7px; height:7px; border-radius:50%; background:{{ $statusColors[$st] }};"></span>
                    <span style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:.05em;">{{ $statusOrder[$st] }}</span>
                    <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $tasksByStatus[$st]->count() }}</span>
                </div>
                @foreach($tasksByStatus[$st] as $task)
                    @include('client.projects.partials.task-row', ['task' => $task, 'slug' => $slug, 'statusColors' => $statusColors, 'statusOrder' => $statusOrder, 'highlight' => false])
                @endforeach
                @endif
            @endforeach
            @if($tasks->isEmpty())
            <div style="padding:32px 18px; text-align:center; color:var(--muted); font-size:13px;">No tasks on this project yet.</div>
            @endif
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">

        @if($milestones->isNotEmpty())
        <div class="ptm-card" style="overflow:hidden;">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Milestones</span>
            </div>
            <div style="padding:8px 18px 14px;">
                @foreach($milestones as $m)
                <div style="display:flex; align-items:center; gap:10px; padding:10px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                    <div style="width:18px; height:18px; border-radius:50%; border:2px solid {{ $m->status === 'done' ? 'var(--accent)' : 'var(--border2)' }}; background:{{ $m->status === 'done' ? 'var(--accent)' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                        @if($m->status === 'done')<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>@endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; font-weight:500; color:var(--text); {{ $m->status === 'done' ? 'text-decoration:line-through; opacity:.65;' : '' }}">{{ $m->title }}</div>
                        @if($m->due_date)<div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $m->due_date->format('d M Y') }}</div>@endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($resources->isNotEmpty())
        <div class="ptm-card" style="overflow:hidden;">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Key resources</span>
            </div>
            <div style="padding:8px 18px 14px;">
                @foreach($resources as $r)
                <div style="padding:10px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                    @if($r->type === 'brief')
                        <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $r->title }}</div>
                        @if($r->content)<div style="font-size:12px; color:var(--muted); margin-top:5px; line-height:1.55;">{{ $r->content }}</div>@endif
                    @else
                        <a href="{{ $r->url }}" target="_blank" rel="noopener" style="font-size:13px; color:var(--accent2); text-decoration:none; display:flex; align-items:center; gap:8px;">
                            <span style="width:28px; height:28px; border-radius:8px; background:rgba(34,211,238,0.12); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.5.5l2-2a5 5 0 00-7-7l-1 1"/><path d="M14 11a5 5 0 00-7.5-.5l-2 2a5 5 0 007 7l1-1"/></svg>
                            </span>
                            {{ $r->title }}
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="ptm-card" style="overflow:hidden;">
            <div style="padding:14px 18px; border-bottom:1px solid var(--border);">
                <span class="ptm-section-title">Status updates</span>
            </div>
            @if($statusUpdates->isEmpty())
            <div style="padding:28px 18px; text-align:center; color:var(--muted); font-size:12px;">No status updates yet.</div>
            @else
            <div style="padding:8px 18px 14px;">
                @foreach($statusUpdates as $su)
                <div style="display:flex; gap:10px; padding:11px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                    <span style="width:8px; height:8px; border-radius:50%; background:{{ $su->color }}; margin-top:5px; flex-shrink:0;"></span>
                    <div style="min-width:0;">
                        <div style="font-size:12px; color:var(--text);"><strong>{{ $su->label }}</strong> <span style="color:var(--muted);">· {{ $su->created_at->diffForHumans() }}</span></div>
                        @if($su->message)<div style="font-size:12px; color:var(--muted); margin-top:4px; line-height:1.5;">{{ $su->message }}</div>@endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

</x-client-layout>
