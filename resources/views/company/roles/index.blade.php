<x-company-layout title="Roles">

<style>
    .roles-group-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; align-items:start; }
    @media (max-width: 960px) { .roles-group-grid { grid-template-columns:1fr; } }
    .roles-group { border-radius:14px; padding:14px; min-width:0; }
    .roles-group--admin { border:1px solid rgba(167,139,250,0.28); background:rgba(167,139,250,0.04); }
    .roles-group--employee { border:1px solid rgba(34,211,238,0.28); background:rgba(34,211,238,0.04); }
    .roles-group-head { padding:4px 4px 14px; margin-bottom:4px; border-bottom:1px solid var(--border); }
    .roles-group-list { display:flex; flex-direction:column; gap:10px; }
    .role-details { border:1px solid var(--border); border-radius:12px; background:var(--surface); overflow:hidden; }
    .role-details[open] { border-color:var(--border2); box-shadow:0 4px 20px rgba(0,0,0,0.1); }
    .role-summary { list-style:none; cursor:pointer; padding:14px 16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; user-select:none; }
    .role-summary::-webkit-details-marker { display:none; }
    .role-summary:hover { background:var(--surface2); }
    .role-chevron { width:18px; height:18px; color:var(--muted); transition:transform 0.2s; flex-shrink:0; }
    .role-details[open] .role-chevron { transform:rotate(90deg); }
    .role-meta { margin-left:auto; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .role-body { padding:0 16px 16px; border-top:1px solid var(--border); }
    .perm-grid { display:grid; grid-template-columns:1fr; gap:8px; }
    @media (min-width: 520px) { .perm-grid { grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); } }
    .perm-chip { display:flex; align-items:flex-start; gap:10px; padding:10px 12px; border-radius:10px; border:1px solid var(--border); background:var(--surface2); cursor:pointer; transition:border-color 0.15s, background 0.15s; }
    .perm-chip:hover { border-color:var(--border2); background:var(--surface); }
    .perm-chip input { margin-top:2px; flex-shrink:0; width:16px; height:16px; accent-color:var(--accent2); cursor:pointer; }
    .perm-chip-label { font-size:13px; font-weight:500; color:var(--text); line-height:1.35; }
    .perm-chip-hint { font-size:11px; color:var(--muted); margin-top:2px; line-height:1.35; display:none; }
    @media (min-width:720px) { .perm-chip-hint { display:block; } }
    .badge-emp { font-size:10px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:#22d3ee; border:1px solid rgba(34,211,238,0.35); background:rgba(34,211,238,0.08); }
    .badge-adm { font-size:10px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:#a78bfa; border:1px solid rgba(167,139,250,0.35); background:rgba(167,139,250,0.08); }
    .badge-sys { font-size:10px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:var(--muted); border:1px solid var(--border2); background:var(--surface2); }
    .badge-custom { font-size:10px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:#4ade80; border:1px solid rgba(74,222,128,0.35); background:rgba(74,222,128,0.08); }
    .member-pill { display:inline-flex; align-items:center; gap:6px; font-size:12px; padding:4px 10px; border-radius:999px; border:1px solid var(--border2); background:var(--surface2); color:var(--text); margin:4px 4px 0 0; }
    .member-pill button { background:none; border:none; padding:0; cursor:pointer; color:var(--muted); font-size:14px; line-height:1; }
    .member-pill button:hover { color:#f87171; }
    .assign-row { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-top:18px; padding-top:16px; border-top:1px solid var(--border); }
    .role-name-row { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:4px; }
    .role-name-row .ptm-input { flex:1; min-width:180px; max-width:320px; }
    .perm-section-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin:16px 0 10px; flex-wrap:wrap; }
</style>

@php
    $adminRoles = $roles->filter(fn ($r) => $r->isAdminPortalRole())->sortBy(fn ($r) => $r->slug === 'company-admin' ? '0' : '1'.strtolower($r->name))->values();
    $employeeRoles = $roles->filter(fn ($r) => $r->isEmployeePortalRole())->sortBy(fn ($r) => $r->slug === 'employee' ? '0' : '1'.strtolower($r->name))->values();
@endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:12px; flex-wrap:wrap;">
    <div>
        <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">Roles</div>
        <div style="font-size:12px; color:var(--muted); margin-top:2px;">Admin portal roles on the left · Employee portal on the right</div>
    </div>
    <button type="button" onclick="document.getElementById('createRoleModal').style.display='flex'" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New role
    </button>
</div>

@if(session('success'))
<div class="ptm-card" style="padding:12px 16px; margin-bottom:16px; border-color:rgba(74,222,128,0.35); background:rgba(74,222,128,0.06); font-size:13px; color:#4ade80;">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="ptm-card" style="padding:12px 16px; margin-bottom:16px; border-color:rgba(248,113,113,0.35); background:rgba(248,113,113,0.08); font-size:13px; color:#f87171;">
    {{ $errors->first() }}
</div>
@endif

<div class="roles-group-grid">
    <section class="roles-group roles-group--admin" aria-label="Admin portal roles">
        <div class="roles-group-head">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span class="badge-adm">Admin portal</span>
                <span style="font-size:13px; font-weight:600; color:var(--text);">Admin login roles</span>
            </div>
            <div style="font-size:11px; color:var(--muted); margin-top:6px; font-family:var(--mono);">/{{ $slug }}/admin/… · {{ $adminRoles->count() }} {{ $adminRoles->count() === 1 ? 'role' : 'roles' }}</div>
        </div>
        <div class="roles-group-list">
            @forelse($adminRoles as $role)
                @include('company.roles.partials.role-card', [
                    'role' => $role,
                    'open' => $loop->first,
                ])
            @empty
                <div style="font-size:12px; color:var(--muted); padding:12px 8px;">No admin roles yet. Create one with Admin login.</div>
            @endforelse
        </div>
    </section>

    <section class="roles-group roles-group--employee" aria-label="Employee portal roles">
        <div class="roles-group-head">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span class="badge-emp">Employee portal</span>
                <span style="font-size:13px; font-weight:600; color:var(--text);">Employee login roles</span>
            </div>
            <div style="font-size:11px; color:var(--muted); margin-top:6px; font-family:var(--mono);">/{{ $slug }}/… · {{ $employeeRoles->count() }} {{ $employeeRoles->count() === 1 ? 'role' : 'roles' }}</div>
        </div>
        <div class="roles-group-list">
            @forelse($employeeRoles as $role)
                @include('company.roles.partials.role-card', [
                    'role' => $role,
                    'open' => $loop->first,
                ])
            @empty
                <div style="font-size:12px; color:var(--muted); padding:12px 8px;">No employee roles yet.</div>
            @endforelse
        </div>
    </section>
</div>

<div id="createRoleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:200; align-items:center; justify-content:center; padding:20px;" onclick="if(event.target===this) this.style.display='none'">
    <div class="ptm-card" style="width:100%; max-width:420px; padding:0; overflow:hidden;">
        <div style="padding:18px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:15px; font-weight:600;">Create role</div>
            <button type="button" onclick="document.getElementById('createRoleModal').style.display='none'" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:20px; line-height:1;">×</button>
        </div>
        <form method="POST" action="{{ route('company.roles.store', $slug) }}" style="padding:20px; display:flex; flex-direction:column; gap:14px;">
            @csrf
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">LOGIN TYPE</label>
                <select name="portal_type" class="ptm-select" style="width:100%;" required>
                    <option value="employee" @selected(old('portal_type', 'employee') === 'employee')>Employee login (team members)</option>
                    <option value="admin" @selected(old('portal_type') === 'admin')>Admin login (admin portal)</option>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ROLE NAME</label>
                <input name="name" class="ptm-input" style="width:100%;" required placeholder="e.g. Accountant" value="{{ old('name') }}" autofocus>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="ptm-btn-ghost" onclick="document.getElementById('createRoleModal').style.display='none'">Cancel</button>
                <button type="submit" class="ptm-btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

@if($errors->has('name') || $errors->has('portal_type'))
<script>document.getElementById('createRoleModal').style.display='flex';</script>
@endif
</x-company-layout>
