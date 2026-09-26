<x-company-layout title="Roles">
<div style="margin-bottom:18px;">
    <div style="font-size:20px; font-weight:600;">Roles & permissions</div>
    <div style="font-size:13px; color:var(--muted); margin-top:4px;">Workspace-wide features (Time, Reports, creating projects in admin, etc.). Access updates immediately when you save.</div>
</div>

<div class="ptm-card" style="padding:14px 16px; margin-bottom:18px; font-size:12px; color:var(--muted); line-height:1.55;">
    <div style="font-weight:600; color:var(--text); margin-bottom:6px;">How access works (simple)</div>
    <ol style="margin:0; padding-left:18px;">
        <li><strong style="color:var(--text);">Members → Role</strong> — team vs client portal + workspace role (this page).</li>
        <li><strong style="color:var(--text);">Project → Share</strong> — what someone can do on one project (Viewer / Editor / …).</li>
        <li><strong style="color:var(--text);">Teams</strong> — team admins manage only their team.</li>
    </ol>
</div>

<form method="POST" action="{{ route('company.roles.store', $slug) }}" class="ptm-card" style="padding:16px 18px; margin-bottom:18px;">
    @csrf
    <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <label class="ptm-section-title">New role name</label>
            <input name="name" class="ptm-input" required>
        </div>
        <button class="ptm-btn-primary">Create role</button>
    </div>
</form>

@foreach($roles as $role)
<div class="ptm-card" style="padding:16px 18px; margin-bottom:14px;">
    <form method="POST" action="{{ route('company.roles.update', [$slug, $role]) }}">
        @csrf @method('PUT')
        <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:12px;">
            <input name="name" class="ptm-input" value="{{ $role->name }}" style="max-width:280px;">
            <span class="ptm-badge" style="background:var(--surface2); color:var(--muted);">{{ $role->is_system ? 'System' : 'Custom' }}</span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:8px;">
            @foreach($permissions as $permission)
            <label style="display:flex; gap:8px; align-items:center; font-size:12px; color:var(--text);">
                <input type="checkbox" name="permissions[]" value="{{ $permission->key }}" @checked($role->permissions->contains('key', $permission->key))>
                {{ $permission->label }}
            </label>
            @endforeach
        </div>
        <button class="ptm-btn-primary" style="margin-top:12px;">Save permissions</button>
    </form>
    <form method="POST" action="{{ route('company.roles.assign', [$slug, $role]) }}" style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
        @csrf
        <select name="user_id" class="ptm-select" style="max-width:260px;">
            @foreach($members as $member)
            <option value="{{ $member->id }}">{{ $member->name }}</option>
            @endforeach
        </select>
        <button class="ptm-btn-ghost">Assign user</button>
    </form>
    <div style="font-size:11px; color:var(--muted); margin-top:6px;">Assigning replaces any other workspace role on that user. Project access is still set per project.</div>
    @if($role->users->isNotEmpty())
    <div style="margin-top:10px; font-size:12px; color:var(--muted);">
        @foreach($role->users as $assigned)
        <form method="POST" action="{{ route('company.roles.unassign', [$slug, $role, $assigned]) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button class="ptm-btn-ghost" style="padding:3px 8px; margin:2px;">{{ $assigned->name }} ×</button>
        </form>
        @endforeach
    </div>
    @endif
</div>
@endforeach
</x-company-layout>
