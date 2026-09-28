<?php

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('portal_type', 16)->default('employee')->after('is_system');
        });

        Role::query()->where('slug', 'company-admin')->update(['portal_type' => 'admin']);
        Role::query()->where('slug', '!=', 'company-admin')->update(['portal_type' => 'employee']);

        $adminPermIds = Permission::query()->whereIn('key', PermissionCatalog::adminPortalKeys())->pluck('id');
        $empPermIds = Permission::query()->whereIn('key', PermissionCatalog::employeePortalKeys())->pluck('id');

        Role::query()->where('portal_type', 'employee')->each(function (Role $role) use ($adminPermIds) {
            $role->permissions()->detach($adminPermIds);
        });

        Role::query()->where('portal_type', 'admin')->each(function (Role $role) use ($empPermIds) {
            $role->permissions()->detach($empPermIds);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('portal_type');
        });
    }
};
