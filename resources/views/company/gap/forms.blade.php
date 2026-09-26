<x-company-layout title="Forms">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Intake forms</div>
<form method="POST" action="{{ route('company.forms.store', $slug) }}" class="ptm-card" style="padding:16px; margin-bottom:16px; display:flex; gap:8px;">
    @csrf
    <input name="name" class="ptm-input" placeholder="Form name" required>
    <select name="project_id" class="ptm-select">
        @foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
    </select>
    <button class="ptm-btn-primary">Create</button>
</form>
@foreach($forms as $form)
<div class="ptm-card" style="padding:14px 16px; margin-bottom:10px;">
    <div style="font-weight:600;">{{ $form->name }} · {{ $form->project?->name }}</div>
    <div style="font-size:12px; color:var(--muted); margin-top:6px;">{{ url('/f/'.$form->token) }}</div>
</div>
@endforeach
</x-company-layout>
