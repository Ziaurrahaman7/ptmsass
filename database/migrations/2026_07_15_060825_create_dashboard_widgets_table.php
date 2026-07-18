<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('chart_style')->default('bar'); // bar, column, donut, number, line
            $table->string('x_axis')->default('assignee');
            $table->unsignedBigInteger('project_filter')->nullable();
            $table->string('status_filter')->nullable();
            $table->string('priority_filter')->nullable();
            $table->string('date_range')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
