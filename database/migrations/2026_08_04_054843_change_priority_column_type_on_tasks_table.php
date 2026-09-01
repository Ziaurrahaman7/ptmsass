<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Priority is now a company-customizable list (see `priorities` table) instead of a fixed enum,
        // so the column just needs to hold whatever slug the company's priority list currently uses.
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE tasks MODIFY priority VARCHAR(255) NOT NULL DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE tasks MODIFY priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium'");
    }
};
