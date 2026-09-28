@php
    $isAdminRole = $role->isAdminPortalRole();
    $permCatalog = $isAdminRole ? $adminPermissions : $employeePermissions;
    $enabledCount = $role->permissions->whereIn('key', array_keys($permCatalog))->count();
    $totalCount = count($permCatalog);
    $memberCount = $role->users->count();
    $isCompanyAdminRole = $role->slug === 'company-admin';
    $isEmployeeSystemRole = $role->slug === 'employee';
    $permGroup = ($isAdminRole ? 'adm' : 'emp').'-'.$role->id;
@endphp

<details class="role-details" @if($open ?? false) open @endif>
    <summary class="role-summary">
        <svg class="role-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <div style="min-width:0;">
            <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $role->name }}</div>
            <div style="font-size:11px; color:var(--muted); margin-top:2px; font-family:var(--mono);">
                {{ $enabledCount }}/{{ $totalCount }} permissions
                @if($memberCount > 0)
                · {{ $memberCount }} {{ $memberCount === 1 ? 'member' : 'members' }}
                @endif
            </div>
        </div>
        <div class="role-meta">
            <span class="{{ $role->is_system ? 'badge-sys' : 'badge-custom' }}">{{ $role->is_system ? 'System' : 'Custom' }}</span>
        </div>
    </summary>

    <div class="role-body">
        <form method="POST" action="{{ route('company.roles.update', [$slug, $role]) }}">
            @csrf @method('PUT')

            <div class="role-name-row" style="margin-top:16px;">
                <label class="ptm-section-title" style="margin:0;">Role name</label>
                <input name="name" class="ptm-input" value="{{ $role->name }}" @disabled($isCompanyAdminRole || $isEmployeeSystemRole)>
                @if($isCompanyAdminRole || $isEmployeeSystemRole)
                <input type="hidden" name="name" value="{{ $role->name }}">
                @endif
            </div>

            <div class="perm-section-head">
                <span class="{{ $isAdminRole ? 'badge-adm' : 'badge-emp' }}">
                    {{ $isAdminRole ? '/'.$slug.'/admin/…' : '/'.$slug.'/…' }}
                </span>
                <button type="button" class="ptm-btn-ghost" style="font-size:11px; padding:4px 10px;"
                    onclick="document.querySelectorAll('[data-perm-group={{ $permGroup }}]').forEach(cb => cb.checked = true)">Select all</button>
            </div>

            <div class="perm-grid">
                @foreach($permCatalog as $key => $meta)
                <label class="perm-chip" title="{{ $meta['hint'] }}">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}" data-perm-group="{{ $permGroup }}"
                        @checked($role->permissions->contains('key', $key))>
                    <span>
                        <div class="perm-chip-label">{{ $meta['label'] }}</div>
                        <div class="perm-chip-hint">{{ $meta['hint'] }}</div>
                    </span>
                </label>
                @endforeach
            </div>

            <button type="submit" class="ptm-btn-primary" style="margin-top:16px;">Save role</button>
        </form>

        <div class="assign-row">
            <span style="font-size:12px; color:var(--muted); flex-shrink:0;">Assign to</span>
            <form method="POST" action="{{ route('company.roles.assign', [$slug, $role]) }}" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; flex:1; min-width:0;">
                @csrf
                <select name="user_id" class="ptm-select" style="flex:1; min-width:200px; max-width:360px;" required>
                    <option value="" disabled selected>Choose member…</option>
                    @foreach($members as $member)
                        @if($isAdminRole && ! $member->isCompanyAdmin())
                            @continue
                        @endif
                        @if(! $isAdminRole && $member->isCompanyAdmin())
                            @continue
                        @endif
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ptm-btn-ghost">Apply role</button>
            </form>
        </div>

        @if($role->users->isNotEmpty())
        <div style="margin-top:12px;">
            <div style="font-size:11px; font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); margin-bottom:6px;">Has this role</div>
            @foreach($role->users as $assigned)
            <span class="member-pill">
                {{ $assigned->name }}
                <form method="POST" action="{{ route('company.roles.unassign', [$slug, $role, $assigned]) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" title="Remove role" aria-label="Remove {{ $assigned->name }}">×</button>
                </form>
            </span>
            @endforeach
        </div>
        @endif
    </div>
</details>
