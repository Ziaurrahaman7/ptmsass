@php
    $maxOpen = max(1, (int) $workloadRows->max('open'));
    $capacity = 8;
    $overloadedCount = $workloadRows->where('overloaded', true)->whereNotNull('user')->count();
    $weekLabels = $workloadRows->isNotEmpty()
        ? collect($workloadRows->first()['week_days'] ?? [])->keys()->map(fn ($d) => \Carbon\Carbon::parse($d)->format('D'))
        : collect(['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);
@endphp

<style>
    .wl-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }
    .wl-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:16px 18px; }
    .wl-row { display:grid; grid-template-columns:200px 1fr 220px 70px; gap:14px; align-items:center; padding:12px 0; border-bottom:1px solid var(--border); }
    .wl-row:last-child { border-bottom:none; }
    .wl-name { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text); }
    .wl-av { width:26px; height:26px; border-radius:50%; background:rgba(74,222,128,0.2); color:#4ade80; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; }
    .wl-track { height:10px; background:var(--surface2); border-radius:5px; overflow:hidden; }
    .wl-fill { height:100%; border-radius:5px; }
    .wl-heat { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
    .wl-cell { height:22px; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:10px; font-family:var(--mono); color:#0d0f12; }
    .wl-badge { font-size:10px; font-family:var(--mono); padding:2px 7px; border-radius:999px; }
    .wl-task { font-size:12px; color:var(--muted); padding:2px 0; cursor:pointer; }
    .wl-task:hover { color:var(--text); }
</style>

<div class="wl-grid">
    <div class="wl-card">
        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">People on this project</div>
        <div style="font-size:28px; font-weight:600; color:var(--text);">{{ $workloadRows->whereNotNull('user')->count() }}</div>
    </div>
    <div class="wl-card">
        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">Overloaded</div>
        <div style="font-size:28px; font-weight:600; color:{{ $overloadedCount ? '#f87171' : 'var(--text)' }};">{{ $overloadedCount }}</div>
        <div style="font-size:11px; color:var(--muted); margin-top:4px;">8+ open tasks or 3+ overdue</div>
    </div>
    <div class="wl-card">
        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:8px;">Open tasks</div>
        <div style="font-size:28px; font-weight:600; color:var(--text);">{{ $scheduleTasks->where('status','!=','done')->count() }}</div>
    </div>
</div>

@if($workloadRows->isEmpty())
    <div class="ptm-card" style="padding:48px 20px; text-align:center; color:var(--muted); font-size:13px;">Assign tasks to see who is loaded this week.</div>
@else
<div class="ptm-card" style="padding:8px 20px 16px;">
    <div class="wl-row" style="padding-top:14px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">Person</div>
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">Open load (capacity {{ $capacity }})</div>
        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:3px; font-size:9px; color:var(--muted); font-family:var(--mono); text-align:center;">
            @foreach($weekLabels as $lab)<span>{{ $lab }}</span>@endforeach
        </div>
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-align:right; text-transform:uppercase;">Open</div>
    </div>

    @foreach($workloadRows as $row)
        @php
            $u = $row['user'];
            $pct = min(100, (int) round(($row['open'] / max($capacity, 1)) * 100));
            $fill = $row['overloaded'] ? '#f87171' : ($pct >= 75 ? '#fbbf24' : '#22d3ee');
        @endphp
        <div class="wl-row">
            <div>
                <div class="wl-name">
                    <div class="wl-av">{{ $u ? strtoupper(substr($u->name,0,1)) : '?' }}</div>
                    <div>
                        <div>{{ $u->name ?? 'Unassigned' }}</div>
                        @if($row['overloaded'])
                            <span class="wl-badge" style="color:#f87171; background:rgba(248,113,113,0.12);">Overloaded</span>
                        @elseif($row['overdue'])
                            <span class="wl-badge" style="color:#fbbf24; background:rgba(251,191,36,0.12);">{{ $row['overdue'] }} overdue</span>
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <div class="wl-track"><div class="wl-fill" style="width:{{ $pct }}%; background:{{ $fill }};"></div></div>
                <div style="font-size:11px; color:var(--muted); margin-top:4px;">{{ $row['this_week'] }} due this week · {{ $row['total'] }} total</div>
                @foreach($row['tasks']->take(4) as $t)
                    <div class="wl-task" onclick="openPanel({{ $t->id }})">{{ $t->title }}@if($t->due_date) · {{ $t->due_date->format('d M') }}@endif</div>
                @endforeach
            </div>
            <div class="wl-heat">
                @foreach($row['week_days'] as $count)
                    @php
                        $bg = $count === 0 ? 'var(--surface2)' : ($count >= 3 ? '#f87171' : ($count === 2 ? '#fbbf24' : '#22d3ee'));
                        $fg = $count === 0 ? 'var(--muted)' : '#0d0f12';
                    @endphp
                    <div class="wl-cell" style="background:{{ $bg }}; color:{{ $fg }};">{{ $count ?: '' }}</div>
                @endforeach
            </div>
            <div style="text-align:right; font-family:var(--mono); font-size:13px; color:{{ $row['overloaded'] ? '#f87171' : 'var(--text)' }};">{{ $row['open'] }}</div>
        </div>
    @endforeach
</div>
@endif
