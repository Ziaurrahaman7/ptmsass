<x-company-layout title="Members">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
        <div>
            <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">Team Members</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Manage your company's members</div>
        </div>
        <button onclick="document.getElementById('addMemberModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Invite Member
        </button>
    </div>

    @if(session('invite_url'))
    <div class="ptm-card" style="padding:14px 16px; margin-bottom:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <div style="font-size:12px; color:var(--muted); flex-shrink:0;">Invite link</div>
        <input id="inviteUrlField" type="text" readonly value="{{ session('invite_url') }}"
            style="flex:1; min-width:220px; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--mono); font-size:12px; padding:8px 10px;">
        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('inviteUrlField').value); this.textContent='Copied';"
            class="ptm-btn-ghost" style="font-size:12px;">Copy</button>
    </div>
    @endif

    @php $invitations = $invitations ?? collect(); @endphp
    @if($invitations->isNotEmpty())
    <div class="ptm-card" style="overflow:hidden; margin-bottom:16px;">
        <div style="padding:12px 18px; border-bottom:1px solid var(--border); font-size:12px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em;">Pending invites</div>
        <table class="ptm-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 18px; text-align:left;">Name</th>
                    <th style="padding:12px 18px; text-align:left;">Email</th>
                    <th style="padding:12px 18px; text-align:left;">Role</th>
                    <th style="padding:12px 18px; text-align:left;">Expires</th>
                    <th style="padding:12px 18px; text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invitations as $invite)
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:12px 18px; font-size:13px; color:var(--text);">{{ $invite->name }}</td>
                    <td style="padding:12px 18px; font-size:13px; color:var(--muted); font-family:var(--mono);">{{ $invite->email }}</td>
                    <td style="padding:12px 18px; font-size:12px; color:var(--muted);">{{ $invite->roleLabel() }}</td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); {{ $invite->isExpired() ? 'color:#f87171;' : 'color:var(--muted);' }}">
                            {{ $invite->isExpired() ? 'Expired' : $invite->expires_at->diffForHumans() }}
                        </span>
                    </td>
                    <td style="padding:12px 18px;">
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <button type="button" onclick="navigator.clipboard.writeText(@js($invite->acceptUrl())); this.textContent='Copied';"
                                style="background:none; border:none; font-size:12px; font-family:var(--mono); cursor:pointer; color:var(--muted); text-decoration:underline;">Copy link</button>
                            <form method="POST" action="{{ route('company.members.invitations.resend', [$slug, $invite]) }}">
                                @csrf
                                <button style="background:none; border:none; font-size:12px; font-family:var(--mono); cursor:pointer; color:var(--muted); text-decoration:underline;">Resend</button>
                            </form>
                            <form method="POST" action="{{ route('company.members.invitations.revoke', [$slug, $invite]) }}" onsubmit="return confirm('Revoke this invite?')">
                                @csrf @method('DELETE')
                                <button style="background:none; border:none; font-size:12px; font-family:var(--mono); cursor:pointer; color:#f87171; text-decoration:underline;">Revoke</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="ptm-card" style="overflow:hidden;">
        <table class="ptm-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 18px; text-align:left;">Name</th>
                    <th style="padding:12px 18px; text-align:left;">Email</th>
                    <th style="padding:12px 18px; text-align:left;">Role</th>
                    <th style="padding:12px 18px; text-align:left;">Status</th>
                    <th style="padding:12px 18px; text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr style="border-bottom:1px solid var(--border); transition:background 0.1s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 18px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:30px; height:30px; border-radius:8px; background:rgba(74,222,128,0.12); color:#4ade80; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                {{ strtoupper(substr($member->name,0,1)) }}
                            </div>
                            <span style="font-size:13px; font-weight:500; color:var(--text);">{{ $member->name }}</span>
                        </div>
                    </td>
                    <td style="padding:12px 18px; font-size:13px; color:var(--muted); font-family:var(--mono);">{{ $member->email }}</td>
                    <td style="padding:12px 18px;">
                        @php
                            $roleStyle = match($member->role) {
                                'company_admin' => 'color:#a78bfa; border-color:rgba(167,139,250,0.3); background:rgba(167,139,250,0.08);',
                                'client'        => 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);',
                                default         => 'color:var(--muted); border-color:var(--border2); background:transparent;',
                            };
                            $roleLabel = match($member->role) {
                                'company_admin' => 'Admin',
                                'client'        => 'Client',
                                default         => 'Employee',
                            };
                        @endphp
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid; {{ $roleStyle }}">
                            {{ $roleLabel }}
                        </span>
                    </td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $member->is_active ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' : 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' }}">
                            {{ $member->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="padding:12px 18px;">
                        @if($member->id !== auth()->id())
                        <form method="POST" action="{{ route('company.members.toggle', [$slug, $member]) }}">
                            @csrf @method('PATCH')
                            <button style="background:none; border:none; font-size:12px; font-family:var(--mono); cursor:pointer; color:var(--muted); text-decoration:underline;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">
                                {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @else
                        <span style="font-size:11px; color:var(--border2); font-family:var(--mono);">You</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:48px; text-align:center; color:var(--muted); font-size:13px;">No members yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add Member Modal --}}
    <div id="addMemberModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:420px;">
            <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:15px; font-weight:600; color:var(--text);">Invite Member</span>
                <button onclick="document.getElementById('addMemberModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px;">✕</button>
            </div>
            <form method="POST" action="{{ route('company.members.store', $slug) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @csrf
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">FULL NAME *</label>
                    <input type="text" name="name" class="ptm-input" style="width:100%;" required value="{{ old('name') }}">
                    @error('name')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">EMAIL *</label>
                    <input type="email" name="email" class="ptm-input" style="width:100%;" required value="{{ old('email') }}">
                    @error('email')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ROLE</label>
                    <select name="role" class="ptm-select" style="width:100%;">
                        <option value="employee">Employee</option>
                        <option value="client">Client</option>
                    </select>
                </div>
                <div style="display:flex; gap:10px; padding-top:4px;">
                    <button type="submit" class="ptm-btn-primary">Send invite</button>
                    <button type="button" onclick="document.getElementById('addMemberModal').style.display='none'" class="ptm-btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @if($errors->any())
    <script>document.getElementById('addMemberModal').style.display='flex';</script>
    @endif

</x-company-layout>
