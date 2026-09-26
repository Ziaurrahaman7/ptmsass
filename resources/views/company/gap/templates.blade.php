<x-company-layout title="Task templates">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Task templates</div>
<form method="POST" action="{{ route('company.templates.store', $slug) }}" class="ptm-card" style="padding:16px; margin-bottom:16px; display:flex; flex-direction:column; gap:10px;">
    @csrf
    <input name="name" class="ptm-input" placeholder="Template name" required>
    <textarea name="description" class="ptm-input" placeholder="Description"></textarea>
    <input name="due_in_days" class="ptm-input" type="number" placeholder="Due in days">
    <textarea name="subtasks" class="ptm-input" placeholder="One subtask per line"></textarea>
    <button class="ptm-btn-primary">Save template</button>
</form>
@foreach($templates as $template)
<div class="ptm-card" style="padding:14px 16px; margin-bottom:10px;">
    <div style="font-weight:600;">{{ $template->name }}</div>
    <form method="POST" action="{{ route('company.templates.apply', [$slug, $template]) }}" style="display:flex; gap:8px; margin-top:8px;">
        @csrf
        <select name="project_id" class="ptm-select">
            @foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
        </select>
        <button class="ptm-btn-ghost">Create task</button>
    </form>
</div>
@endforeach
</x-company-layout>
