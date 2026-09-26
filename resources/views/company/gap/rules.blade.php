<x-company-layout title="Rules">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Automation rules</div>
<form method="POST" action="{{ route('company.rules.store', $slug) }}" class="ptm-card" style="padding:16px; margin-bottom:16px; display:flex; flex-direction:column; gap:8px;">
    @csrf
    <input name="name" class="ptm-input" placeholder="Rule name" required>
    <select name="trigger" class="ptm-select">
        <option value="task.created">When a task is created</option>
        <option value="task.status_changed">When status changes</option>
    </select>
    <select name="project_id" class="ptm-select">
        <option value="">All projects</option>
        @foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
    </select>
    <input name="condition_status" class="ptm-input" placeholder="If status equals (optional)">
    <input name="action_status" class="ptm-input" placeholder="Then set status (optional)">
    <input name="action_comment" class="ptm-input" placeholder="Then add comment (optional)">
    <button class="ptm-btn-primary">Save rule</button>
</form>
@foreach($rules as $rule)
<div class="ptm-card" style="padding:14px 16px; margin-bottom:8px;">
    <strong>{{ $rule->name }}</strong>
    <div style="font-size:12px; color:var(--muted);">{{ $rule->trigger }} · {{ $rule->project?->name ?? 'All' }}</div>
</div>
@endforeach
</x-company-layout>
