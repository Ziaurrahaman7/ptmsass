<x-company-layout :title="$dashboard['title']">

<style>
.ins-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
}
.ins-card-title {
    font-size: 11px; font-family: var(--mono); color: var(--muted);
    text-transform: uppercase; letter-spacing: .07em; margin-bottom: 14px;
}
.ins-stat-big { font-size: 32px; font-weight: 700; letter-spacing: -1px; color: var(--text); line-height: 1; }
.ins-stat-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }
.bar-wrap { display: flex; align-items: flex-end; gap: 6px; height: 80px; }
.bar-col { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; }
.bar-fill { width: 100%; border-radius: 4px 4px 0 0; min-height: 2px; }
.bar-label { font-size: 10px; color: var(--muted); font-family: var(--mono); }
.donut-wrap { position: relative; width: 100px; height: 100px; flex-shrink: 0; }
.donut-wrap svg { transform: rotate(-90deg); }
.donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.prog-bar-track { flex: 1; height: 5px; background: var(--border); border-radius: 3px; overflow: hidden; }
.prog-bar-fill { height: 100%; border-radius: 3px; transition: width .4s; }
.member-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }
.member-row:last-child { border-bottom: none; }
.overdue-badge { background: rgba(248,113,113,.12); color: var(--danger); border: 1px solid rgba(248,113,113,.2); border-radius: 6px; font-size: 11px; padding: 2px 8px; font-family: var(--mono); }
.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    color: var(--muted); font-size: 13px; text-decoration: none;
    margin-bottom: 18px; transition: color .15s;
}
.back-btn:hover { color: var(--text); }
</style>

{{-- Back link --}}
<a href="{{ route('company.insights.index', $slug) }}" class="back-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Reporting
</a>

{{-- Header --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg,#c026d3,#9333ea); display:flex; align-items:center; justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div>
            <h1 style="font-size:20px; font-weight:700; color:var(--text); margin:0 0 2px;">{{ $dashboard['title'] }}</h1>
            <p style="font-size:12px; color:var(--muted); margin:0;">{{ $dashboard['desc'] }}</p>
        </div>
    </div>
    <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ now()->format('d M Y, H:i') }}</div>
</div>

{{-- KPI Row --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:16px;">

    {{-- Completion Rate --}}
    <div class="ins-card" style="display:flex; align-items:center; gap:14px;">
        <div class="donut-wrap">
            @php $r=40; $circ=2*M_PI*$r; $dash=($completionRate/100)*$circ; @endphp
            <svg width="100" height="100" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="{{ $r }}" fill="none" stroke="var(--border)" stroke-width="8"/>
                <circle cx="50" cy="50" r="{{ $r }}" fill="none" stroke="#4ade80" stroke-width="8"
                    stroke-dasharray="{{ round($dash,2) }} {{ round($circ,2) }}" stroke-linecap="round"/>
            </svg>
            <div class="donut-center">
                <span style="font-size:14px; font-weight:700; color:var(--text);">{{ $completionRate }}%</span>
            </div>
        </div>
        <div>
            <div class="ins-card-title" style="margin-bottom:4px;">Completion</div>
            <div class="ins-stat-big">{{ $taskStats['done'] }}</div>
            <div class="ins-stat-sub">of {{ $taskStats['total'] }} tasks</div>
        </div>
    </div>

    {{-- Total Tasks --}}
    <div class="ins-card">
        <div class="ins-card-title">Total Tasks</div>
        <div class="ins-stat-big">{{ $taskStats['total'] }}</div>
        <div style="display:flex; gap:6px; margin-top:10px; flex-wrap:wrap;">
            <span style="font-size:11px; background:rgba(107,115,133,.12); color:var(--muted); border-radius:5px; padding:2px 7px; font-family:var(--mono);">{{ $taskStats['todo'] }} todo</span>
            <span style="font-size:11px; background:rgba(34,211,238,.1); color:var(--accent2); border-radius:5px; padding:2px 7px; font-family:var(--mono);">{{ $taskStats['in_progress'] }} active</span>
            <span style="font-size:11px; background:rgba(167,139,250,.1); color:var(--purple); border-radius:5px; padding:2px 7px; font-family:var(--mono);">{{ $taskStats['in_review'] }} review</span>
        </div>
    </div>

    {{-- Overdue --}}
    <div class="ins-card">
        <div class="ins-card-title">Overdue</div>
        <div class="ins-stat-big" style="color:{{ $overdueTasks->count() > 0 ? 'var(--danger)' : 'var(--text)' }};">{{ $overdueTasks->count() }}</div>
        <div class="ins-stat-sub">{{ $overdueTasks->count() > 0 ? 'Need attention' : 'All on track 🎉' }}</div>
    </div>

    {{-- Urgent/High --}}
    <div class="ins-card">
        <div class="ins-card-title">Urgent / High</div>
        <div class="ins-stat-big" style="color:var(--warn);">{{ $priorityStats['urgent'] + $priorityStats['high'] }}</div>
        <div style="display:flex; gap:6px; margin-top:10px;">
            <span style="font-size:11px; background:rgba(248,113,113,.1); color:var(--danger); border-radius:5px; padding:2px 7px; font-family:var(--mono);">{{ $priorityStats['urgent'] }} urgent</span>
            <span style="font-size:11px; background:rgba(251,191,36,.1); color:var(--warn); border-radius:5px; padding:2px 7px; font-family:var(--mono);">{{ $priorityStats['high'] }} high</span>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:16px;">

    {{-- Bar chart --}}
    <div class="ins-card">
        <div class="ins-card-title">Completed — Last 7 Days</div>
        @php $maxBar = max(max(array_column($completedByDay, 'count')), 1); @endphp
        <div class="bar-wrap">
            @foreach($completedByDay as $day)
            @php $pct = ($day['count'] / $maxBar) * 100; @endphp
            <div class="bar-col">
                <span style="font-size:10px; color:var(--muted); font-family:var(--mono);">{{ $day['count'] ?: '' }}</span>
                <div style="width:100%; flex:1; display:flex; align-items:flex-end;">
                    <div class="bar-fill" style="height:{{ max($pct,3) }}%; background:{{ $day['count'] > 0 ? 'var(--accent)' : 'var(--border)' }}; opacity:{{ $day['count'] > 0 ? '1' : '.4' }};"></div>
                </div>
                <span class="bar-label">{{ $day['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Status breakdown --}}
    <div class="ins-card">
        <div class="ins-card-title">Status Breakdown</div>
        @php
            $statuses = [
                ['label'=>'Done',        'count'=>$taskStats['done'],        'color'=>'#4ade80'],
                ['label'=>'In Progress', 'count'=>$taskStats['in_progress'], 'color'=>'#22d3ee'],
                ['label'=>'In Review',   'count'=>$taskStats['in_review'],   'color'=>'#a78bfa'],
                ['label'=>'Todo',        'count'=>$taskStats['todo'],        'color'=>'#6b7385'],
            ];
            $total = max($taskStats['total'], 1);
        @endphp
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($statuses as $s)
            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-size:12px; color:var(--text);">{{ $s['label'] }}</span>
                    <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $s['count'] }}</span>
                </div>
                <div class="prog-bar-track">
                    <div class="prog-bar-fill" style="width:{{ round(($s['count']/$total)*100) }}%; background:{{ $s['color'] }};"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Priority breakdown --}}
    <div class="ins-card">
        <div class="ins-card-title">Priority (Open Tasks)</div>
        @php
            $priorities = [
                ['label'=>'Urgent', 'count'=>$priorityStats['urgent'], 'color'=>'#f87171'],
                ['label'=>'High',   'count'=>$priorityStats['high'],   'color'=>'#fbbf24'],
                ['label'=>'Medium', 'count'=>$priorityStats['medium'], 'color'=>'#22d3ee'],
                ['label'=>'Low',    'count'=>$priorityStats['low'],    'color'=>'#6b7385'],
            ];
            $openTotal = max(array_sum(array_column($priorities, 'count')), 1);
        @endphp
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($priorities as $p)
            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-size:12px; color:var(--text);">{{ $p['label'] }}</span>
                    <span style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $p['count'] }}</span>
                </div>
                <div class="prog-bar-track">
                    <div class="prog-bar-fill" style="width:{{ round(($p['count']/$openTotal)*100) }}%; background:{{ $p['color'] }};"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Bottom Row --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">

    {{-- Project Progress --}}
    <div class="ins-card">
        <div class="ins-card-title">Project Progress</div>
        @forelse($projects as $proj)
        @php
            $pPct = $proj->tasks_count > 0 ? round(($proj->done_tasks_count / $proj->tasks_count) * 100) : 0;
        @endphp
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <a href="{{ route('company.projects.show', [$slug, $proj->id]) }}"
               style="width:130px; font-size:12px; color:var(--text); text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex-shrink:0;"
               onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text)'">
                {{ $proj->name }}
            </a>
            <div class="prog-bar-track">
                <div class="prog-bar-fill" style="width:{{ $pPct }}%; background:{{ $pPct>=100 ? '#4ade80' : ($pPct>=50 ? '#22d3ee' : '#a78bfa') }};"></div>
            </div>
            <span style="font-size:11px; color:var(--muted); font-family:var(--mono); width:34px; text-align:right; flex-shrink:0;">{{ $pPct }}%</span>
        </div>
        @empty
        <div style="padding:30px 0; text-align:center; color:var(--muted); font-size:13px;">No projects yet</div>
        @endforelse
    </div>

    {{-- Member Workload --}}
    <div class="ins-card">
        <div class="ins-card-title">Member Workload</div>
        @forelse($members as $member)
        @php $mPct = round(($member->completed_tasks / max($member->total_tasks,1)) * 100); @endphp
        <div class="member-row">
            <div style="width:30px; height:30px; border-radius:8px; background:rgba(74,222,128,.15); color:var(--accent); font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                {{ strtoupper(substr($member->name,0,1)) }}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:12px; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $member->name }}</div>
                <div class="prog-bar-track" style="margin-top:5px;">
                    <div class="prog-bar-fill" style="width:{{ $mPct }}%; background:var(--accent);"></div>
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:12px; color:var(--text); font-family:var(--mono);">{{ $member->completed_tasks }}/{{ $member->total_tasks }}</div>
                <div style="font-size:10px; color:var(--muted);">{{ $member->open_tasks }} open</div>
            </div>
        </div>
        @empty
        <div style="padding:30px 0; text-align:center; color:var(--muted); font-size:13px;">No members yet</div>
        @endforelse
    </div>
</div>

{{-- Overdue Table --}}
@if($overdueTasks->count() > 0)
<div class="ins-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <div class="ins-card-title" style="margin-bottom:0;">Overdue Tasks</div>
        <span class="overdue-badge">{{ $overdueTasks->count() }} overdue</span>
    </div>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                @foreach(['Task','Project','Assignee','Due Date','Priority'] as $h)
                <th style="text-align:left; font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:.06em; padding:0 12px 10px 0; font-weight:500;">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($overdueTasks as $task)
            <tr style="border-top:1px solid var(--border);">
                <td style="padding:10px 12px 10px 0;">
                    <a href="{{ route('company.tasks.show', [$slug, $task]) }}"
                       style="font-size:13px; color:var(--text); text-decoration:none;"
                       onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text)'">
                        {{ Str::limit($task->title, 38) }}
                    </a>
                </td>
                <td style="padding:10px 12px 10px 0; font-size:12px; color:var(--muted);">{{ $task->project->name ?? '—' }}</td>
                <td style="padding:10px 12px 10px 0;">
                    @if($task->assignee)
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:22px; height:22px; border-radius:6px; background:rgba(74,222,128,.15); color:var(--accent); font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center;">{{ strtoupper(substr($task->assignee->name,0,1)) }}</div>
                        <span style="font-size:12px; color:var(--muted);">{{ $task->assignee->name }}</span>
                    </div>
                    @else
                    <span style="font-size:12px; color:var(--muted);">Unassigned</span>
                    @endif
                </td>
                <td style="padding:10px 12px 10px 0;">
                    <span style="font-size:12px; color:var(--danger); font-family:var(--mono);">
                        {{ $task->due_date->format('d M Y') }}
                        <span style="font-size:10px; opacity:.7;">({{ $task->due_date->diffForHumans() }})</span>
                    </span>
                </td>
                <td style="padding:10px 0;">
                    @php
                        $pc=['urgent'=>'var(--danger)','high'=>'var(--warn)','medium'=>'var(--accent2)','low'=>'var(--muted)'];
                        $pb=['urgent'=>'rgba(248,113,113,.1)','high'=>'rgba(251,191,36,.1)','medium'=>'rgba(34,211,238,.1)','low'=>'rgba(107,115,133,.1)'];
                    @endphp
                    <span style="font-size:11px; color:{{ $pc[$task->priority] }}; background:{{ $pb[$task->priority] }}; border-radius:5px; padding:2px 8px; font-family:var(--mono);">{{ $task->priority }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</x-company-layout>
