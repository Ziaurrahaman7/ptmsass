@php
    $fmtDuration = function (int $minutes): string {
        if ($minutes < 1) {
            return '0m';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h > 0 && $m > 0) {
            return $h.'h '.$m.'m';
        }
        if ($h > 0) {
            return $h.'h';
        }

        return $m.'m';
    };
    $statusStyle = [
        'pending' => 'color:#fbbf24; border-color:rgba(251,191,36,0.35); background:rgba(251,191,36,0.1);',
        'approved' => 'color:#4ade80; border-color:rgba(74,222,128,0.35); background:rgba(74,222,128,0.1);',
        'rejected' => 'color:#f87171; border-color:rgba(248,113,113,0.35); background:rgba(248,113,113,0.1);',
    ];
@endphp

<style>
    .time-grid { display:grid; grid-template-columns:minmax(300px,380px) 1fr; gap:18px; align-items:start; }
    @media (max-width: 960px) { .time-grid { grid-template-columns:1fr; } .time-stats { grid-template-columns:repeat(2, 1fr) !important; } }
    @media (max-width: 520px) { .time-stats { grid-template-columns:1fr !important; } }
    .time-preset { font-size:12px; font-family:var(--mono); padding:6px 11px; border-radius:8px; border:1px solid var(--border2); background:var(--surface2); color:var(--muted); cursor:pointer; transition:all 0.15s; }
    .time-preset:hover, .time-preset.active { border-color:rgba(34,211,238,0.45); color:var(--accent2); background:rgba(34,211,238,0.08); }
    .time-table { width:100%; border-collapse:collapse; font-size:13px; }
    .time-table thead th { text-align:left; padding:10px 14px; font-size:10px; font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); border-bottom:1px solid var(--border); background:var(--surface2); }
    .time-table tbody td { padding:12px 14px; border-bottom:1px solid var(--border); vertical-align:middle; }
    .time-table tbody tr:hover { background:var(--surface2); }
    .time-table tbody tr:last-child td { border-bottom:none; }
    .billable-toggle { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; border:1px solid var(--border); background:var(--surface2); cursor:pointer; user-select:none; }
    .billable-toggle input { width:16px; height:16px; accent-color:var(--accent2); cursor:pointer; }
</style>

<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="width:40px; height:40px; border-radius:11px; background:linear-gradient(135deg, rgba(34,211,238,0.25), rgba(74,222,128,0.15)); border:1px solid rgba(34,211,238,0.25); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22d3ee" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </div>
        <div>
            <div style="font-size:18px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">Time tracking</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Log hours against your tasks and keep timesheets ready for review.</div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="ptm-alert-success" style="padding:12px 16px; margin-bottom:16px; font-size:13px; display:flex; align-items:center; gap:8px;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="time-stats" style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
    <div class="ptm-card" style="padding:14px 16px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Today</div>
        <div style="font-size:26px; font-weight:600; letter-spacing:-0.5px; color:#22d3ee;">{{ $fmtDuration($stats['today_minutes']) }}</div>
        <div style="font-size:11px; color:var(--muted); margin-top:4px;">Logged today</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">This week</div>
        <div style="font-size:26px; font-weight:600; letter-spacing:-0.5px; color:var(--text);">{{ $fmtDuration($stats['week_minutes']) }}</div>
        <div style="font-size:11px; color:var(--muted); margin-top:4px;">Since {{ now()->startOfWeek()->format('D, M j') }}</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Billable</div>
        <div style="font-size:26px; font-weight:600; letter-spacing:-0.5px; color:#4ade80;">{{ $fmtDuration($stats['week_billable']) }}</div>
        <div style="font-size:11px; color:var(--muted); margin-top:4px;">This week</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Pending</div>
        <div style="font-size:26px; font-weight:600; letter-spacing:-0.5px; color:#fbbf24;">{{ $stats['pending_count'] }}</div>
        <div style="font-size:11px; color:var(--muted); margin-top:4px;">Awaiting review</div>
    </div>
</div>

<div class="ptm-card" style="padding:16px 18px; margin-bottom:16px;">
    <div style="font-size:12px; font-weight:600; color:var(--text); margin-bottom:12px;">This week</div>
    <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:8px;">
        @foreach($weekDays as $day)
        <div style="text-align:center; padding:10px 6px; border-radius:10px; border:1px solid {{ $day['date'] === now()->toDateString() ? 'rgba(34,211,238,0.4)' : 'var(--border)' }}; background:{{ $day['date'] === now()->toDateString() ? 'rgba(34,211,238,0.06)' : 'var(--surface2)' }};">
            <div style="font-size:10px; font-family:var(--mono); color:var(--muted);">{{ $day['label'] }}</div>
            <div style="font-size:14px; font-weight:600; color:var(--text); margin-top:4px;">{{ $fmtDuration($day['minutes']) }}</div>
        </div>
        @endforeach
    </div>
</div>

<div class="time-grid">
    <div style="display:flex; flex-direction:column; gap:16px;">
    <div class="ptm-card" style="padding:20px; border-color:rgba(34,211,238,0.35);">
        <div style="font-size:13px; font-weight:600; color:var(--accent2); margin-bottom:4px;">Live timer</div>
        <div style="font-size:11px; color:var(--muted); margin-bottom:14px;">Start/stop against a task — saved as a pending timesheet entry.</div>
        @if($runningTimer)
        <div id="timerRunningBlock">
            <div style="font-size:15px; font-weight:500; color:var(--text); margin-bottom:6px;">{{ $runningTimer->task?->title }}</div>
            <div style="font-size:28px; font-weight:600; font-family:var(--mono); color:#22d3ee; margin-bottom:12px;" id="timerDisplay">—</div>
            <button type="button" class="ptm-btn-primary" style="width:100%;" onclick="stopTimer()">Stop timer</button>
        </div>
        @else
        <div id="timerStartBlock">
            <select id="timerTaskSelect" class="ptm-select" style="width:100%; margin-bottom:10px;">
                @foreach($tasks as $task)
                <option value="{{ $task->id }}" @selected($preselectedTaskId === $task->id)>{{ $task->title }}</option>
                @endforeach
            </select>
            @if($tasks->isEmpty())
            <p style="font-size:12px; color:var(--muted); margin-bottom:10px;">No tasks available — create or assign a task first.</p>
            @endif
            <label class="billable-toggle" style="margin-bottom:12px;">
                <input type="checkbox" id="timerBillable">
                <span style="font-size:12px; color:var(--text);">Billable</span>
            </label>
            <button type="button" class="ptm-btn-primary" style="width:100%;" onclick="startTimer()" @disabled($tasks->isEmpty())>Start timer</button>
        </div>
        @endif
    </div>
    <div class="ptm-card" style="padding:20px;">
        <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:4px;">Manual entry</div>
        <div style="font-size:11px; color:var(--muted); margin-bottom:16px;">Link a task or log general work time.</div>

        <form method="POST" action="{{ $timeStoreRoute }}" style="display:flex; flex-direction:column; gap:14px;">
            @csrf

            <div>
                <label style="display:block; font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Task (optional)</label>
                <select name="task_id" class="ptm-select" style="width:100%;">
                    <option value="">— No linked task —</option>
                    @foreach($tasks as $task)
                    <option value="{{ $task->id }}" @selected(old('task_id', $preselectedTaskId) == $task->id)>{{ $task->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Duration *</label>
                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
                    @foreach([15, 30, 45, 60, 90, 120] as $preset)
                    <button type="button" class="time-preset" data-minutes="{{ $preset }}" onclick="setMinutes({{ $preset }}, this)">{{ $preset }}m</button>
                    @endforeach
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <input id="minutesInput" name="minutes" type="number" min="1" max="1440" class="ptm-input" placeholder="Minutes" required value="{{ old('minutes') }}" style="flex:1;">
                    <span style="font-size:12px; color:var(--muted); font-family:var(--mono); white-space:nowrap;">max 24h</span>
                </div>
                @error('minutes')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div>
                <label style="display:block; font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Date worked *</label>
                <input name="worked_on" type="date" class="ptm-input" style="width:100%;" value="{{ old('worked_on', now()->toDateString()) }}" required>
            </div>

            <label class="billable-toggle">
                <input type="checkbox" name="billable" value="1" @checked(old('billable'))>
                <span>
                    <span style="display:block; font-size:13px; font-weight:500; color:var(--text);">Billable time</span>
                    <span style="font-size:11px; color:var(--muted);">Counts toward client billing reports</span>
                </span>
            </label>

            <div>
                <label style="display:block; font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Note</label>
                <textarea name="note" rows="2" class="ptm-input" style="width:100%; resize:vertical;" placeholder="What did you work on?">{{ old('note') }}</textarea>
            </div>

            <button type="submit" class="ptm-btn-primary" style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px; padding:11px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Log time
            </button>
        </form>
    </div>
    </div>

    <div class="ptm-card" style="overflow:hidden;">
        <div style="padding:16px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div style="font-size:13px; font-weight:600; color:var(--text);">Recent entries</div>
                <div style="font-size:11px; color:var(--muted); margin-top:2px;">Your latest logged time</div>
            </div>
            <span style="font-size:11px; font-family:var(--mono); color:var(--muted); padding:4px 10px; border:1px solid var(--border2); border-radius:20px;">{{ $entries->total() }} total</span>
        </div>

        @if($entries->isEmpty())
        <div style="padding:48px 24px; text-align:center;">
            <div style="width:52px; height:52px; margin:0 auto 14px; border-radius:14px; background:var(--surface2); border:1px solid var(--border); display:flex; align-items:center; justify-content:center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </div>
            <div style="font-size:15px; font-weight:500; color:var(--text); margin-bottom:6px;">No time logged yet</div>
            <div style="font-size:12px; color:var(--muted); max-width:280px; margin:0 auto;">Use the form to record your first entry. It will show up here for review.</div>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="time-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Duration</th>
                        <th>Task / project</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                    @php
                        $st = $entry->status ?? 'pending';
                        $badge = $statusStyle[$st] ?? $statusStyle['pending'];
                    @endphp
                    <tr>
                        <td style="font-family:var(--mono); font-size:12px; color:var(--text); white-space:nowrap;">
                            {{ $entry->worked_on?->format('M j, Y') }}
                        </td>
                        <td>
                            <span style="font-weight:600; color:var(--text);">{{ $fmtDuration((int) $entry->minutes) }}</span>
                            @if($entry->billable)
                            <span style="margin-left:6px; font-size:10px; font-family:var(--mono); color:#4ade80; padding:2px 6px; border-radius:4px; background:rgba(74,222,128,0.1);">$</span>
                            @endif
                        </td>
                        <td style="min-width:160px;">
                            @if($entry->task)
                            <div style="font-weight:500; color:var(--text); line-height:1.35;">{{ $entry->task->title }}</div>
                            @if($entry->task->project)
                            <div style="font-size:11px; color:var(--muted); margin-top:2px;">{{ $entry->task->project->name }}</div>
                            @endif
                            @else
                            <span style="color:var(--muted); font-size:12px;">General time</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:11px; font-family:var(--mono); padding:4px 8px; border-radius:6px; border:1px solid; {{ $badge }}">{{ ucfirst($st) }}</span>
                        </td>
                        <td style="max-width:200px; color:var(--muted); font-size:12px; line-height:1.4;">
                            {{ $entry->note ? \Illuminate\Support\Str::limit($entry->note, 80) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
        <div style="padding:14px 18px; border-top:1px solid var(--border);">
            {{ $entries->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<script>
function setMinutes(n, btn) {
    const input = document.getElementById('minutesInput');
    if (input) input.value = n;
    document.querySelectorAll('.time-preset').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
}
function startTimer(){
    const taskId = document.getElementById('timerTaskSelect')?.value;
    if(!taskId) return;
    fetch(@json($timerStartUrl), {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({
            task_id: parseInt(taskId, 10),
            billable: document.getElementById('timerBillable')?.checked ? 1 : 0
        })
    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'Could not start timer'); });
}
function stopTimer(){
    fetch(@json($timerStopUrl), {
        method:'POST',
        headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}
    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'Could not stop'); });
}
@if($runningTimer && $runningTimer->started_at)
(function(){
    const started = new Date(@json($runningTimer->started_at->toIso8601String()));
    const el = document.getElementById('timerDisplay');
    function tick(){
        const sec = Math.max(0, Math.floor((Date.now() - started.getTime()) / 1000));
        const m = Math.floor(sec/60), s = sec%60;
        if(el) el.textContent = String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    }
    tick(); setInterval(tick, 1000);
})();
@endif
</script>
