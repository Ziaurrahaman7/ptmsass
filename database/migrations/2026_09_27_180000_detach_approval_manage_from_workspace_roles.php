<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('permissions')->where('key', 'approval.manage')->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }
        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
    }

    public function down(): void
    {
        //
    }
};
