<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->json('field_values')->nullable()->after('job_title');
        });
    }

    public function down(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropColumn('field_values');
        });
    }
};
