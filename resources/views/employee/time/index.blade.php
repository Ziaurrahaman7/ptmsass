<x-employee-layout title="Time tracking">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Log time</div>
<form method="POST" action="{{ route('employee.time.store', $slug) }}" class="ptm-card" style="padding:16px; margin-bottom:16px; display:flex; flex-direction:column; gap:8px;">
    @csrf
    <select name="task_id" class="ptm-select">
        <option value="">No task</option>
        @foreach($tasks as $task)<option value="{{ $task->id }}">{{ $task->title }}</option>@endforeach
    </select>
    <input name="minutes" type="number" class="ptm-input" placeholder="Minutes" required>
    <input name="worked_on" type="date" class="ptm-input" value="{{ now()->toDateString() }}">
    <label style="font-size:12px;"><input type="checkbox" name="billable" value="1"> Billable</label>
    <input name="note" class="ptm-input" placeholder="Note">
    <button class="ptm-btn-primary">Save entry</button>
</form>
@foreach($entries as $entry)
<div class="ptm-card" style="padding:12px 16px; margin-bottom:6px; font-size:13px;">
    {{ $entry->worked_on?->format('Y-m-d') }} · {{ $entry->minutes }} min · {{ $entry->status }} · {{ $entry->task?->title }}
</div>
@endforeach
{{ $entries->links() }}
</x-employee-layout>
