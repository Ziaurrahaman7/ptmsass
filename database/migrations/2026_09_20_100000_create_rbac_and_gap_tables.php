<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->unique(['user_id', 'role_id']);
        });

        Schema::table('team_user', function (Blueprint $table) {
            $table->string('role')->default('member')->after('user_id');
        });

        Schema::table('project_clients', function (Blueprint $table) {
            $table->string('access_mode')->default('view')->after('user_id');
        });

        Schema::create('task_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'project_id']);
        });

        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('priority')->nullable();
            $table->unsignedInteger('due_in_days')->nullable();
            $table->json('subtasks')->nullable();
            $table->json('custom_values')->nullable();
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('recurrence')->nullable();
            $table->date('recurrence_until')->nullable();
            $table->unsignedInteger('recurrence_remaining')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->foreignId('task_template_id')->nullable()->constrained('task_templates')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('weekly_capacity_hours')->default(40);
            $table->boolean('mfa_enabled')->default(false);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('mfa_required')->default(false);
            $table->json('trusted_domains')->nullable();
        });

        Schema::create('project_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('token')->unique();
            $table->boolean('is_active')->default(true);
            $table->json('fields');
            $table->timestamps();
        });

        Schema::create('task_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('waiting');
            $table->text('note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('trigger');
            $table->json('conditions')->nullable();
            $table->json('actions');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('minutes');
            $table->date('worked_on');
            $table->boolean('billable')->default(false);
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
        });

        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('token', 80)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('event');
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('ip')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('project_user')) {
            DB::table('project_user')->where('role', 'member')->update(['role' => 'editor']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('task_approvals');
        Schema::dropIfExists('project_forms');
        Schema::dropIfExists('task_templates');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['mfa_required', 'trusted_domains']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['weekly_capacity_hours', 'mfa_enabled']);
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_template_id');
            $table->dropColumn(['recurrence', 'recurrence_until', 'recurrence_remaining', 'estimated_minutes']);
        });

        Schema::dropIfExists('task_project');
        Schema::table('project_clients', function (Blueprint $table) {
            $table->dropColumn('access_mode');
        });
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
