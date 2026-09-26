<x-company-layout title="Timesheets">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Timesheet review</div>
<div class="ptm-card">
<table class="ptm-table" style="width:100%;">
<thead><tr><th style="padding:10px;">Person</th><th>Task</th><th>Hours</th><th>Date</th><th>Status</th><th></th></tr></thead>
<tbody>
@foreach($entries as $entry)
<tr>
    <td style="padding:10px;">{{ $entry->user?->name }}</td>
    <td>{{ $entry->task?->title ?? '—' }}</td>
    <td>{{ number_format($entry->minutes/60, 1) }}</td>
    <td>{{ $entry->worked_on?->format('Y-m-d') }}</td>
    <td>{{ $entry->status }}</td>
    <td>
        @if($entry->status === 'pending')
        <form method="POST" action="{{ route('company.timesheets.review', [$slug, $entry]) }}" style="display:inline;">
            @csrf
            <button name="status" value="approved" class="ptm-btn-primary">Approve</button>
            <button name="status" value="rejected" class="ptm-btn-danger">Reject</button>
        </form>
        @endif
    </td>
</tr>
@endforeach
</tbody>
</table>
</div>
{{ $entries->links() }}
</x-company-layout>
