<x-employee-layout title="Invite members">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
        <div>
            <div style="font-size:16px; font-weight:600; color:var(--text);">Invite members</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">You can invite people because your workspace role includes this permission.</div>
        </div>
        <button type="button" onclick="document.getElementById('addMemberModal').style.display='flex'" class="ptm-btn-primary">Invite member</button>
    </div>

    @if(session('success'))
    <div class="ptm-alert-success" style="padding:12px 16px; margin-bottom:16px; font-size:13px;">{{ session('success') }}</div>
    @endif

    @if(session('invite_url'))
    <div class="ptm-card" style="padding:14px 16px; margin-bottom:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <div style="font-size:12px; color:var(--muted);">Invite link</div>
        <input id="inviteUrlField" type="text" readonly value="{{ session('invite_url') }}" class="ptm-input" style="flex:1; min-width:220px; font-family:var(--mono); font-size:12px;">
        <button type="button" class="ptm-btn-ghost" onclick="navigator.clipboard.writeText(document.getElementById('inviteUrlField').value); this.textContent='Copied';">Copy</button>
    </div>
    @endif

    @if(($invitations ?? collect())->isNotEmpty())
    <div class="ptm-card" style="padding:14px 16px; margin-bottom:16px;">
        <div style="font-size:12px; font-weight:600; color:var(--muted); margin-bottom:10px; font-family:var(--mono);">PENDING INVITES</div>
        @foreach($invitations as $invite)
        <div style="display:flex; justify-content:space-between; gap:10px; padding:8px 0; border-bottom:1px solid var(--border); font-size:13px;">
            <span>{{ $invite->name }} · {{ $invite->email }}</span>
            <button type="button" class="ptm-btn-ghost" style="font-size:11px;" onclick="navigator.clipboard.writeText(@js($invite->acceptUrl())); this.textContent='Copied';">Copy link</button>
        </div>
        @endforeach
    </div>
    @endif

    <div id="addMemberModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:100; align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--surface); border:1px solid var(--border2); border-radius:16px; width:100%; max-width:420px;">
            <div style="padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between;">
                <span style="font-size:15px; font-weight:600;">Invite member</span>
                <button type="button" onclick="document.getElementById('addMemberModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer;">✕</button>
            </div>
            <form method="POST" action="{{ route('employee.members.store', $slug) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
                @csrf
                <input type="text" name="name" class="ptm-input" placeholder="Full name" required value="{{ old('name') }}">
                <input type="email" name="email" class="ptm-input" placeholder="Email" required value="{{ old('email') }}">
                @php $roles = $workspaceRoles ?? collect(); @endphp
                <select name="access_role" class="ptm-select" required>
                    @foreach($roles as $wsRole)
                    <option value="{{ $wsRole->id }}">{{ $wsRole->name }}</option>
                    @endforeach
                    <option value="client">Client</option>
                </select>
                @error('email')<div style="font-size:11px; color:#f87171;">{{ $message }}</div>@enderror
                <button type="submit" class="ptm-btn-primary">Send invite</button>
            </form>
        </div>
    </div>
    @if($errors->any())
    <script>document.getElementById('addMemberModal').style.display='flex';</script>
    @endif

</x-employee-layout>
