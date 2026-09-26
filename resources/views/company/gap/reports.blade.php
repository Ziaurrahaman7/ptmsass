<x-company-layout title="Reports">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Flexible report</div>
<form method="GET" class="ptm-card" style="padding:14px; margin-bottom:16px; display:flex; gap:8px;">
    <select name="project_id" class="ptm-select" onchange="this.form.submit()">
        <option value="">All projects</option>
        @foreach($projects as $project)
        <option value="{{ $project->id }}" @selected((int)$projectId === (int)$project->id)>{{ $project->name }}</option>
        @endforeach
    </select>
</form>
<div class="ptm-card" style="padding:16px;">
    @forelse($tasks as $status => $total)
    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border);">
        <span>{{ $status }}</span><strong>{{ $total }}</strong>
    </div>
    @empty
    <div style="color:var(--muted);">No tasks in this filter.</div>
    @endforelse
</div>
</x-company-layout>
