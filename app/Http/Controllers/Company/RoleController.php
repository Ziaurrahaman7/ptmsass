<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleProvisioner;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(string $slug, RoleProvisioner $provisioner)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);
        $provisioner->forCompany((int) auth()->user()->company_id);

        $roles = Role::query()->where('company_id', auth()->user()->company_id)->with('permissions', 'users')->get();
        $members = User::query()->where('company_id', auth()->user()->company_id)->whereIn('role', ['employee', 'company_admin'])->orderBy('name')->get();

        $employeePermissions = PermissionCatalog::employeePortalPermissions();
        $adminPermissions = PermissionCatalog::adminPortalPermissions();

        return view('company.roles.index', compact('roles', 'members', 'employeePermissions', 'adminPermissions'));
    }

    public function store(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'portal_type' => 'required|in:employee,admin',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role = Role::create([
            'company_id' => auth()->user()->company_id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'is_system' => false,
            'portal_type' => $data['portal_type'],
        ]);
        $allowed = PermissionCatalog::keysForPortalType($data['portal_type']);
        $keys = array_values(array_intersect($data['permissions'] ?? [], $allowed));
        $ids = Permission::query()->whereIn('key', $keys)->pluck('id');
        $role->permissions()->sync($ids);

        return back()->with('success', 'Role created.');
    }

    public function update(Request $request, string $slug, Role $role)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);
        abort_if((int) $role->company_id !== (int) auth()->user()->company_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:'.implode(',', PermissionCatalog::keysForPortalType($role->portal_type ?? 'employee')),
        ]);
        if (! ($role->is_system && $role->slug === 'company-admin')) {
            $role->update(['name' => $data['name']]);
        }
        $allowed = PermissionCatalog::keysForPortalType($role->portal_type ?? 'employee');
        $keys = array_values(array_intersect($data['permissions'] ?? [], $allowed));
        $ids = Permission::query()->whereIn('key', $keys)->pluck('id');
        $role->permissions()->sync($ids);

        return back()->with('success', 'Role updated. Assigned users receive these permissions immediately.');
    }

    public function assign(Request $request, string $slug, Role $role)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);
        abort_if((int) $role->company_id !== (int) auth()->user()->company_id, 403);
        $data = $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::query()->where('company_id', auth()->user()->company_id)->findOrFail($data['user_id']);

        if ($role->isAdminPortalRole() && ! $user->isCompanyAdmin()) {
            return back()->withErrors([
                'user_id' => 'This role is for users with admin login. Invite them as Company admin under Members.',
            ]);
        }
        if ($role->isEmployeePortalRole() && $user->isCompanyAdmin()) {
            return back()->withErrors([
                'user_id' => 'This role is for employee login users. Company admins use the Company Admin role.',
            ]);
        }

        // One workspace role per user — replaces prior roles (e.g. default Employee pack).
        $user->workspaceRoles()->sync([$role->id]);

        return back()->with('success', $user->name.' now has only the '.$role->name.' role (previous workspace roles removed).');
    }

    public function unassign(string $slug, Role $role, User $user)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);
        abort_if((int) $role->company_id !== (int) auth()->user()->company_id, 403);
        $role->users()->detach($user->id);

        if ($user->workspaceRoles()->count() === 0) {
            app(RoleProvisioner::class)->assignDefault($user);
        }

        return back()->with('success', 'Role removed from '.$user->name.'.');
    }
}
