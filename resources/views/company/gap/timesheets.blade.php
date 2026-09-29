@php use App\Support\TimeFormat; @endphp
<x-company-layout title="Timesheets">
<div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
    <div style="font-size:20px; font-weight:600;">Timesheet review</div>
    <form method="GET" style="display:flex; gap:8px; align-items:center;">
        <input type="week" name="week" class="ptm-input" style="font-size:12px;" value="{{ $weekStart->format('o-\WW') }}" onchange="this.form.submit()">
    </form>
</div>

<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px;">
    <div class="ptm-card" style="padding:14px 16px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">Week total</div>
        <div style="font-size:22px; font-weight:600; color:#22d3ee;">{{ TimeFormat::minutes((int) ($weekTotals->total ?? 0)) }}</div>
        <div style="font-size:11px; color:var(--muted);">{{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">Billable</div>
        <div style="font-size:22px; font-weight:600; color:#4ade80;">{{ TimeFormat::minutes((int) ($weekTotals->billable ?? 0)) }}</div>
    </div>
    <div class="ptm-card" style="padding:14px 16px;">
        <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">Entries</div>
        <div style="font-size:22px; font-weight:600;">{{ $entries->total() }}</div>
    </div>
</div>

<div class="ptm-card" style="overflow:hidden;">
<table class="ptm-table" style="width:100%; border-collapse:collapse; font-size:13px;">
<thead>
<tr>
    <th style="padding:12px 14px; text-align:left;">Person</th>
    <th style="padding:12px 14px; text-align:left;">Task / project</th>
    <th style="padding:12px 14px; text-align:left;">Duration</th>
    <th style="padding:12px 14px; text-align:left;">Date</th>
    <th style="padding:12px 14px; text-align:left;">Billable</th>
    <th style="padding:12px 14px; text-align:left;">Status</th>
    <th style="padding:12px 14px;"></th>
</tr>
</thead>
<tbody>
@forelse($entries as $entry)
<tr style="border-bottom:1px solid var(--border);">
    <td style="padding:12px 14px;">{{ $entry->user?->name }}</td>
    <td style="padding:12px 14px;">
        <div>{{ $entry->task?->title ?? 'General' }}</div>
        @if($entry->project)<div style="font-size:11px; color:var(--muted);">{{ $entry->project->name }}</div>@endif
    </td>
    <td style="padding:12px 14px; font-family:var(--mono);">{{ TimeFormat::minutes((int) $entry->minutes) }}</td>
    <td style="padding:12px 14px; font-family:var(--mono); font-size:12px;">{{ $entry->worked_on?->format('Y-m-d') }}</td>
    <td style="padding:12px 14px;">{{ $entry->billable ? 'Yes' : '—' }}</td>
    <td style="padding:12px 14px; font-family:var(--mono); font-size:11px;">{{ $entry->status }}</td>
    <td style="padding:12px 14px;">
        @if($entry->status === 'pending')
        <form method="POST" action="{{ route('company.timesheets.review', [$slug, $entry]) }}" style="display:flex; gap:6px;">
            @csrf
            <button name="status" value="approved" class="ptm-btn-primary" style="font-size:11px; padding:6px 10px;">Approve</button>
            <button name="status" value="rejected" class="ptm-btn-ghost" style="font-size:11px; padding:6px 10px; color:#f87171;">Reject</button>
        </form>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="7" style="padding:24px; text-align:center; color:var(--muted);">No entries this week.</td></tr>
@endforelse
</tbody>
</table>
</div>
<div style="margin-top:14px;">{{ $entries->links() }}</div>
</x-company-layout>
