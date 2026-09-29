@php use App\Support\TimeFormat; @endphp
<x-company-layout title="Reports">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Reports</div>
<form method="GET" class="ptm-card" style="padding:14px; margin-bottom:16px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
    <select name="project_id" class="ptm-select" onchange="this.form.submit()">
        <option value="">All projects</option>
        @foreach($projects as $project)
        <option value="{{ $project->id }}" @selected((int)$projectId === (int)$project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
    <span style="font-size:12px; color:var(--muted);">Filter tasks and time for one project, or view company-wide time below.</span>
</form>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
    <div class="ptm-card" style="padding:16px 18px;">
        <div class="ptm-section-title" style="margin-bottom:12px;">Task status{{ $projectId ? ' (project)' : '' }}</div>
        @forelse($tasks as $status => $total)
        <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border); font-size:13px;">
            <span style="color:var(--muted); font-family:var(--mono);">{{ $status }}</span><strong>{{ $total }}</strong>
        </div>
        @empty
        <div style="color:var(--muted); font-size:13px;">No tasks in this filter.</div>
        @endforelse
    </div>
    <div class="ptm-card" style="padding:16px 18px;">
        <div class="ptm-section-title" style="margin-bottom:12px;">Time summary{{ $projectId ? ' (project)' : ' (company)' }}</div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
                <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">TOTAL LOGGED</div>
                <div style="font-size:22px; font-weight:600; color:#22d3ee;">{{ TimeFormat::minutes($timeSummary['total_minutes']) }}</div>
                <div style="font-size:11px; color:var(--muted);">{{ $timeSummary['entry_count'] }} entries</div>
            </div>
            <div>
                <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">BILLABLE</div>
                <div style="font-size:22px; font-weight:600; color:#4ade80;">{{ TimeFormat::minutes($timeSummary['billable_minutes']) }}</div>
                <div style="font-size:11px; color:var(--muted);">{{ TimeFormat::decimalHours($timeSummary['billable_minutes']) }}h</div>
            </div>
        </div>
    </div>
</div>

@if($projectId && $timeByTask->isNotEmpty())
<div class="ptm-card" style="padding:16px 18px; margin-bottom:16px; overflow:hidden;">
    <div class="ptm-section-title" style="margin-bottom:12px;">Time by task (top 15)</div>
    <table class="ptm-table" style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead><tr><th style="padding:8px 12px; text-align:left;">Task</th><th style="padding:8px 12px; text-align:right;">Logged</th></tr></thead>
        <tbody>
        @foreach($timeByTask as $row)
        <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:10px 12px;">{{ $row->task?->title ?? '—' }}</td>
            <td style="padding:10px 12px; text-align:right; font-family:var(--mono);">{{ TimeFormat::minutes((int) $row->total_minutes) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
    <div class="ptm-card" style="padding:16px 18px; overflow:hidden;">
        <div class="ptm-section-title" style="margin-bottom:12px;">Time by person</div>
        @forelse($timeByPerson as $row)
        <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px;">
            <span>{{ $row->user?->name ?? 'Unknown' }}</span>
            <span style="font-family:var(--mono); color:var(--text);">{{ TimeFormat::minutes((int) $row->total_minutes) }}
                @if((int) $row->billable_minutes > 0)<span style="color:#4ade80; margin-left:6px;">({{ TimeFormat::minutes((int) $row->billable_minutes) }} billable)</span>@endif
            </span>
        </div>
        @empty
        <div style="color:var(--muted); font-size:13px;">No time logged yet.</div>
        @endforelse
    </div>
    <div class="ptm-card" style="padding:16px 18px; overflow:hidden;">
        <div class="ptm-section-title" style="margin-bottom:12px;">Time by project</div>
        @forelse($timeByProject as $row)
        <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px;">
            <span>{{ $row->project?->name ?? '—' }}</span>
            <span style="font-family:var(--mono);">{{ TimeFormat::minutes((int) $row->total_minutes) }}</span>
        </div>
        @empty
        <div style="color:var(--muted); font-size:13px;">No project time yet.</div>
        @endforelse
    </div>
</div>
</x-company-layout>
