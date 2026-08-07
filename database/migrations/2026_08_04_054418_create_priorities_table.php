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
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color');
            $table->integer('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'slug']);
        });

        // Seed every existing company with the same 4 priorities that used to be hardcoded,
        // so nothing changes visually until an admin edits the list.
        $defaults = [
            ['name' => 'Low',    'slug' => 'low',    'color' => '#6b7385', 'position' => 0, 'is_default' => false],
            ['name' => 'Medium', 'slug' => 'medium', 'color' => '#fbbf24', 'position' => 1, 'is_default' => true],
            ['name' => 'High',   'slug' => 'high',   'color' => '#fb923c', 'position' => 2, 'is_default' => false],
            ['name' => 'Urgent', 'slug' => 'urgent', 'color' => '#f87171', 'position' => 3, 'is_default' => false],
        ];

        $companyIds = \Illuminate\Support\Facades\DB::table('companies')->pluck('id');
        $now = now();
        $rows = [];
        foreach ($companyIds as $companyId) {
            foreach ($defaults as $d) {
                $rows[] = $d + ['company_id' => $companyId, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        if (!empty($rows)) {
            \Illuminate\Support\Facades\DB::table('priorities')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priorities');
    }
};
