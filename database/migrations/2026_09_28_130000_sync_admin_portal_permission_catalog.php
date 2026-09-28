<?php

use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleProvisioner;
use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(RoleProvisioner::class)->seedCatalog();

        foreach (PermissionCatalog::adminPortalPermissions() as $key => $meta) {
            Permission::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $meta['label']]
            );
        }

        $adminPermIds = Permission::query()
            ->whereIn('key', PermissionCatalog::adminPortalKeys())
            ->pluck('id');

        Role::query()->where('slug', 'company-admin')->each(function (Role $role) use ($adminPermIds) {
            $role->permissions()->sync($adminPermIds);
        });
    }

    public function down(): void
    {
        // Permissions remain; no destructive rollback.
    }
};
