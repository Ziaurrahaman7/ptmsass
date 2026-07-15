<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('chart_style')->default('bar'); // bar, column, donut, number, line
            $table->string('x_axis')->default('assignee');  // assignee, project, status, priority, due_date, created_by
            $table->string('report_on')->default('tasks');
            $table->unsignedBigInteger('project_filter')->nullable(); // null = all projects
            $table->string('status_filter')->nullable();
            $table->string('priority_filter')->nullable();
            $table->string('date_range')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};
