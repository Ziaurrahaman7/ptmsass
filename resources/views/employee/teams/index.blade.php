<x-employee-layout title="Teams">

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; gap:12px; flex-wrap:wrap;">
    <div>
        <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">My teams</div>
        <div style="font-size:12px; color:var(--muted); margin-top:2px;">Teams you belong to in this workspace</div>
    </div>
</div>

@forelse($teams as $team)
@php
    $isAdmin = ($team->pivot->role ?? 'member') === 'admin';
@endphp
<a href="{{ route('employee.teams.show', [$slug, $team]) }}" class="ptm-card" style="display:block; padding:16px 18px; margin-bottom:10px; text-decoration:none; color:var(--text); border-color:{{ $isAdmin ? 'rgba(167,139,250,0.35)' : 'var(--border)' }}; transition:border-color 0.15s, background 0.15s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:42px; height:42px; border-radius:12px; background:{{ $isAdmin ? 'rgba(167,139,250,0.12)' : 'rgba(74,222,128,0.12)' }}; color:{{ $isAdmin ? '#a78bfa' : '#4ade80' }}; font-size:15px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            {{ strtoupper(substr($team->name, 0, 1)) }}
        </div>
        <div style="flex:1; min-width:0;">
            <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $team->name }}</div>
            @if($team->description)
            <div style="font-size:12px; color:var(--muted); margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $team->description }}</div>
            @endif
        </div>
        @if($isAdmin)
        <span style="font-size:10px; font-family:var(--mono); padding:4px 10px; border-radius:6px; color:#a78bfa; border:1px solid rgba(167,139,250,0.35); background:rgba(167,139,250,0.08); flex-shrink:0;">Team Admin</span>
        @else
        <span style="font-size:10px; font-family:var(--mono); padding:4px 10px; border-radius:6px; color:var(--muted); border:1px solid var(--border2); flex-shrink:0;">Member</span>
        @endif
    </div>
</a>
@empty
<div class="ptm-card" style="padding:32px 20px; text-align:center; color:var(--muted); font-size:13px;">
    You are not on a team yet. Ask your company admin to add you from the admin portal.
</div>
@endforelse
</x-employee-layout>
