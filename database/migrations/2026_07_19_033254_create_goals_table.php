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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_goal_id')->nullable()->constrained('goals')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('scope')->default('company'); // company, team
            $table->string('status')->default('on_track'); // on_track, at_risk, off_track, done
            $table->string('progress_mode')->default('manual'); // manual, projects
            $table->unsignedTinyInteger('manual_progress')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
