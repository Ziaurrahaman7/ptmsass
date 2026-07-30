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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('color')->nullable()->after('name');
            $table->string('icon')->nullable()->after('color');
            $table->boolean('is_favorite')->default(false)->after('icon');
            $table->boolean('is_template')->default(false)->after('is_favorite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['color', 'icon', 'is_favorite', 'is_template']);
        });
    }
};
