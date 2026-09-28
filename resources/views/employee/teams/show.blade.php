<x-employee-layout :title="$team->name">

@php
    $memberCount = $team->members->count();
    $adminCount = $team->members->filter(fn ($m) => ($m->pivot->role ?? 'member') === 'admin')->count();
@endphp

<div style="margin-bottom:20px;">
    <a href="{{ route('employee.teams.index', $slug) }}" style="font-size:12px; color:var(--muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:10px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        All teams
    </a>
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:48px; height:48px; border-radius:14px; background:rgba(167,139,250,0.12); color:#a78bfa; font-size:18px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                {{ strtoupper(substr($team->name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:18px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $team->name }}</div>
                <div style="font-size:12px; color:var(--muted); margin-top:4px; font-family:var(--mono);">
                    {{ $memberCount }} {{ $memberCount === 1 ? 'member' : 'members' }}
                    @if($adminCount > 0)
                    · {{ $adminCount }} {{ $adminCount === 1 ? 'admin' : 'admins' }}
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            @if($myTeamRole === 'admin')
            <span style="font-size:11px; font-family:var(--mono); padding:6px 12px; border-radius:8px; color:#a78bfa; border:1px solid rgba(167,139,250,0.4); background:rgba(167,139,250,0.1);">You · Team Admin</span>
            @else
            <span style="font-size:11px; font-family:var(--mono); padding:6px 12px; border-radius:8px; color:var(--muted); border:1px solid var(--border2);">You · Member</span>
            @endif
        </div>
    </div>
    @if($team->description)
    <p style="font-size:13px; color:var(--muted); margin:14px 0 0; line-height:1.5; max-width:640px;">{{ $team->description }}</p>
    @endif
</div>

@if($myTeamRole === 'admin')
<div class="ptm-card" style="padding:12px 16px; margin-bottom:16px; border-color:rgba(167,139,250,0.28); background:linear-gradient(135deg, rgba(167,139,250,0.08) 0%, rgba(167,139,250,0.02) 100%); display:flex; align-items:flex-start; gap:12px;">
    <div style="width:32px; height:32px; border-radius:10px; background:rgba(167,139,250,0.15); color:#a78bfa; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div style="font-size:12px; color:var(--muted); line-height:1.5;">
        <span style="color:#a78bfa; font-weight:600;">Team Admin</span> for this team only — you can change settings, add or remove members, and assign Team Admin. Your workspace login stays a normal employee; company-wide teams are still managed in the admin portal.
    </div>
</div>
@endif

@if(session('success'))
<div class="ptm-card" style="padding:12px 16px; margin-bottom:16px; border-color:rgba(74,222,128,0.35); background:rgba(74,222,128,0.06); font-size:13px; color:#4ade80;">
    {{ session('success') }}
</div>
@endif

@if($canManage)
<div class="ptm-card" style="padding:16px 18px; margin-bottom:16px;">
    <div style="font-size:12px; font-weight:600; color:var(--text); margin-bottom:12px;">Team settings</div>
    <form method="POST" action="{{ route('employee.teams.update', [$slug, $team]) }}" style="display:flex; flex-direction:column; gap:12px;">
        @csrf @method('PUT')
        <div>
            <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">NAME</label>
            <input name="name" class="ptm-input" style="width:100%; max-width:400px;" value="{{ $team->name }}" required>
        </div>
        <div>
            <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">DESCRIPTION</label>
            <textarea name="description" class="ptm-input" style="width:100%; max-width:520px; min-height:72px;" placeholder="What this team works on…">{{ $team->description }}</textarea>
        </div>
        <div>
            <button type="submit" class="ptm-btn-primary" style="font-size:13px;">Save changes</button>
        </div>
    </form>
</div>
@endif

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; gap:12px; flex-wrap:wrap;">
    <div style="font-size:14px; font-weight:600; color:var(--text);">Members</div>
    @if($canManage)
    <button type="button" onclick="document.getElementById('addTeamMemberModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px; font-size:13px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add member
    </button>
    @endif
</div>

<div class="ptm-card" style="overflow:hidden;">
    <table class="ptm-table" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="padding:12px 18px; text-align:left;">Name</th>
                <th style="padding:12px 18px; text-align:left;">Email</th>
                <th style="padding:12px 18px; text-align:left;">Team role</th>
                @if($canManage)
                <th style="padding:12px 18px; text-align:left; width:100px;">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($team->members->sortBy(fn ($m) => [($m->pivot->role ?? 'member') === 'admin' ? 0 : 1, strtolower($m->name)]) as $member)
            @php
                $teamMemberRole = $member->pivot->role ?? 'member';
                $isSelf = (int) $member->id === (int) auth()->id();
            @endphp
            <tr style="border-bottom:1px solid var(--border);" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 18px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:32px; height:32px; border-radius:10px; background:{{ $teamMemberRole === 'admin' ? 'rgba(167,139,250,0.15)' : 'rgba(74,222,128,0.12)' }}; color:{{ $teamMemberRole === 'admin' ? '#a78bfa' : '#4ade80' }}; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        <div>
                            <span style="font-size:13px; font-weight:500; color:var(--text);">{{ $member->name }}</span>
                            @if($isSelf)
                            <span style="font-size:11px; color:var(--muted); margin-left:6px;">(you)</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="padding:12px 18px; font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $member->email }}</td>
                <td style="padding:12px 18px;">
                    @if($canManage && ! $isSelf)
                    <form method="POST" action="{{ route('employee.teams.members.role', [$slug, $team, $member]) }}" style="margin:0;">
                        @csrf @method('PATCH')
                        <select name="role" onchange="this.form.submit()" class="ptm-select" style="font-size:12px; padding:6px 10px; max-width:160px;">
                            <option value="member" @selected($teamMemberRole !== 'admin')>Member</option>
                            <option value="admin" @selected($teamMemberRole === 'admin')>Team Admin</option>
                        </select>
                    </form>
                    @elseif($teamMemberRole === 'admin')
                    <span style="font-size:10px; font-family:var(--mono); padding:4px 10px; border-radius:6px; color:#a78bfa; border:1px solid rgba(167,139,250,0.35); background:rgba(167,139,250,0.1);">Team Admin</span>
                    @else
                    <span style="font-size:10px; font-family:var(--mono); padding:4px 10px; border-radius:6px; color:var(--muted); border:1px solid var(--border2);">Member</span>
                    @endif
                </td>
                @if($canManage)
                <td style="padding:12px 18px;">
                    @if(! $isSelf)
                    <form method="POST" action="{{ route('employee.teams.members.remove', [$slug, $team, $member]) }}" onsubmit="return confirm('Remove {{ addslashes($member->name) }} from this team?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none; border:none; font-size:12px; font-family:var(--mono); cursor:pointer; color:var(--muted); text-decoration:underline;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'">Remove</button>
                    </form>
                    @else
                    <span style="font-size:11px; color:var(--muted);">—</span>
                    @endif
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($canManage)
<div id="addTeamMemberModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:200; align-items:center; justify-content:center; padding:20px;" onclick="if(event.target===this) this.style.display='none'">
    <div class="ptm-card" style="width:100%; max-width:440px; padding:0; overflow:hidden;">
        <div style="padding:18px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:15px; font-weight:600;">Add team member</div>
            <button type="button" onclick="document.getElementById('addTeamMemberModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:20px;">×</button>
        </div>
        @if($people->isEmpty())
        <div style="padding:28px 20px; text-align:center; color:var(--muted); font-size:13px;">Everyone in the company is already on this team.</div>
        @else
        <form method="POST" action="{{ route('employee.teams.members.add', [$slug, $team]) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            @csrf
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PERSON</label>
                <select name="user_id" class="ptm-select" style="width:100%;" required>
                    @foreach($people as $person)
                    <option value="{{ $person->id }}">{{ $person->name }} · {{ $person->email }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TEAM ROLE</label>
                <select name="role" class="ptm-select" style="width:100%;">
                    <option value="member">Member</option>
                    <option value="admin">Team Admin (manage this team only)</option>
                </select>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="ptm-btn-ghost" onclick="document.getElementById('addTeamMemberModal').style.display='none'">Cancel</button>
                <button type="submit" class="ptm-btn-primary">Add</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endif

@if(! $canManage)
<div class="ptm-card" style="padding:14px 16px; margin-top:16px; font-size:12px; color:var(--muted); line-height:1.45;">
    You are a <strong style="color:var(--text);">member</strong> of this team. Team admins can change settings and membership.
</div>
@endif

</x-employee-layout>
