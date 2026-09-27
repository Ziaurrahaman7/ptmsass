<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyKeys = ['task.edit', 'task.comment', 'task.assign'];
        $ids = DB::table('permissions')->whereIn('key', $legacyKeys)->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }
        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
    }

    public function down(): void
    {
        // Legacy keys are no longer assigned via workspace roles.
    }
};
