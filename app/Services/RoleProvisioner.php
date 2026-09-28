<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionCatalog;

class RoleProvisioner
{
    public function seedCatalog(): void
    {
        foreach (PermissionCatalog::all() as $key => $label) {
            Permission::query()->updateOrCreate(['key' => $key], ['label' => $label]);
        }
    }

    public function forCompany(int $companyId): void
    {
        $this->seedCatalog();

        $admin = $this->role($companyId, 'Company Admin', 'company-admin', true);
        $member = $this->role($companyId, 'Employee', 'employee', true);

        $this->syncKeys($admin, PermissionCatalog::companyAdminKeys());
        $this->syncKeys($member, PermissionCatalog::employeeKeys());

        User::query()
            ->where('company_id', $companyId)
            ->where('role', 'company_admin')
            ->each(fn (User $user) => $user->workspaceRoles()->syncWithoutDetaching([$admin->id]));

        User::query()
            ->where('company_id', $companyId)
            ->where('role', 'employee')
            ->each(fn (User $user) => $user->workspaceRoles()->syncWithoutDetaching([$member->id]));
    }

    public function assignDefault(User $user): void
    {
        if (! $user->company_id || in_array($user->role, ['superadmin', 'client'], true)) {
            return;
        }

        $this->forCompany((int) $user->company_id);
        $slug = $user->isCompanyAdmin() ? 'company-admin' : 'employee';
        $role = Role::query()->where('company_id', $user->company_id)->where('slug', $slug)->first();
        if ($role) {
            $user->workspaceRoles()->syncWithoutDetaching([$role->id]);
        }
    }

    protected function role(int $companyId, string $name, string $slug, bool $system): Role
    {
        return Role::query()->firstOrCreate(
            ['company_id' => $companyId, 'slug' => $slug],
            ['name' => $name, 'is_system' => $system]
        );
    }

    protected function syncKeys(Role $role, array $keys): void
    {
        $ids = Permission::query()->whereIn('key', $keys)->pluck('id');
        $role->permissions()->sync($ids);
    }
}
