<x-employee-layout title="My teams">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Teams</div>
@forelse($teams as $team)
<a href="{{ route('employee.teams.show', [$slug, $team]) }}" class="ptm-card" style="display:block; padding:14px 16px; margin-bottom:8px; text-decoration:none; color:var(--text);">
    <strong>{{ $team->name }}</strong>
    <div style="font-size:12px; color:var(--muted);">{{ $team->pivot->role }}</div>
</a>
@empty
<div class="ptm-card" style="padding:24px; color:var(--muted);">You are not on a team yet.</div>
@endforelse
</x-employee-layout>
