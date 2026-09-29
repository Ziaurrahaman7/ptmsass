@php
    use App\Support\TimeFormat;
    $logged = (int) ($taskLoggedMinutes ?? 0);
    $estimated = (int) ($task->estimated_minutes ?? 0);
    $overEstimate = $estimated > 0 && $logged > $estimated;
@endphp
<div style="display:grid; grid-template-columns:120px 1fr; align-items:start; gap:10px; padding:8px 0;">
    <span style="font-size:12px; color:var(--muted); font-weight:500;">Time</span>
    <div>
        @if($canUpdate ?? true)
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px;">
            <label style="font-size:11px; color:var(--muted); font-family:var(--mono);">ESTIMATE (MIN)</label>
            <input type="number" min="0" max="59999" step="15" class="ptm-input" style="width:100px; font-size:12px; padding:6px 10px;"
                value="{{ $estimated ?: '' }}" placeholder="—"
                onchange="(typeof panelPatch==='function'?panelPatch:empPanelField)('estimated_minutes', this.value === '' ? null : parseInt(this.value, 10))">
        </div>
        @elseif($estimated > 0)
        <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Estimate: {{ TimeFormat::minutes($estimated) }}</div>
        @endif
        <div style="font-size:12px; color:{{ $overEstimate ? '#f87171' : 'var(--text)' }};">
            Logged: <strong>{{ TimeFormat::minutes($logged) }}</strong>
            @if($estimated > 0)
            <span style="color:var(--muted);"> / {{ TimeFormat::minutes($estimated) }} est.</span>
            @endif
        </div>
        @if($canTrackTime ?? false)
        @php
            $timeLogRoute = auth()->user()->isCompanyAdmin()
                ? route('company.time.index', [$slug, 'task_id' => $task->id])
                : route('employee.time.index', [$slug, 'task_id' => $task->id]);
        @endphp
        <a href="{{ $timeLogRoute }}" style="display:inline-block; margin-top:8px; font-size:12px; color:var(--accent2); text-decoration:none;">Log time or start timer →</a>
        @endif
    </div>
</div>
