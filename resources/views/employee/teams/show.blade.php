<x-employee-layout title="Team">
<div style="font-size:20px; font-weight:600; margin-bottom:12px;">{{ $team->name }}</div>
@can('update', $team)
<form method="POST" action="{{ route('employee.teams.update', [$slug, $team]) }}" class="ptm-card" style="padding:16px; margin-bottom:14px; display:flex; flex-direction:column; gap:8px;">
    @csrf @method('PUT')
    <input name="name" class="ptm-input" value="{{ $team->name }}">
    <textarea name="description" class="ptm-input">{{ $team->description }}</textarea>
    <button class="ptm-btn-primary">Save team</button>
</form>
<form method="POST" action="{{ route('employee.teams.members.add', [$slug, $team]) }}" class="ptm-card" style="padding:16px; margin-bottom:14px; display:flex; gap:8px;">
    @csrf
    <select name="user_id" class="ptm-select">
        @foreach($people as $person)<option value="{{ $person->id }}">{{ $person->name }}</option>@endforeach
    </select>
    <select name="role" class="ptm-select">
        <option value="member">Member</option>
        <option value="admin">Team Admin</option>
    </select>
    <button class="ptm-btn-ghost">Add</button>
</form>
@endcan
@foreach($team->members as $member)
<div class="ptm-card" style="padding:12px 16px; margin-bottom:6px; display:flex; justify-content:space-between;">
    <span>{{ $member->name }} · {{ $member->pivot->role }}</span>
    @can('update', $team)
    <form method="POST" action="{{ route('employee.teams.members.remove', [$slug, $team, $member]) }}">
        @csrf @method('DELETE')
        <button class="ptm-btn-danger">Remove</button>
    </form>
    @endcan
</div>
@endforeach
</x-employee-layout>
