<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWorkspaces;
use Tests\TestCase;

class PermissionGapTest extends TestCase
{
    use CreatesWorkspaces;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_company_gets_default_roles(): void
    {
        $ws = $this->workspace('acme');

        $this->assertTrue(Role::query()->where('company_id', $ws['company']->id)->where('slug', 'company-admin')->exists());
        $this->assertTrue($ws['admin']->hasPermission('project.create'));
        $this->assertFalse($ws['employee']->hasPermission('project.delete'));
        $this->assertTrue($ws['employee']->hasPermission('task.comment'));
    }

    public function test_two_employees_can_have_different_project_rights(): void
    {
        $ws = $this->workspace('acme');
        $editor = $ws['employee'];
        $viewer = User::factory()->employee()->create([
            'company_id' => $ws['company']->id,
            'email' => 'viewer@acme.test',
        ]);
        $ws['project']->members()->attach($editor->id, ['role' => 'editor']);
        $ws['project']->members()->attach($viewer->id, ['role' => 'viewer']);

        $perms = app(PermissionService::class);
        $this->assertTrue($perms->projectAtLeast($editor, $ws['project'], 'editor'));
        $this->assertFalse($perms->projectAtLeast($viewer, $ws['project'], 'editor'));
        $this->assertTrue($viewer->can('view', $ws['project']));
        $this->assertFalse($viewer->can('createTasks', $ws['project']));
        $this->assertTrue($editor->can('createTasks', $ws['project']));
    }

    public function test_team_admin_cannot_manage_other_team(): void
    {
        $ws = $this->workspace('acme');
        $teamA = Team::create(['company_id' => $ws['company']->id, 'name' => 'Team A']);
        $teamB = Team::create(['company_id' => $ws['company']->id, 'name' => 'Team B']);
        $teamA->members()->attach($ws['employee']->id, ['role' => 'admin']);
        $teamB->members()->attach($ws['employee']->id, ['role' => 'member']);

        $this->assertTrue($ws['employee']->can('update', $teamA));
        $this->assertFalse($ws['employee']->can('update', $teamB));
    }

    public function test_client_collaborate_can_comment_view_cannot(): void
    {
        $ws = $this->workspace('acme');
        $client = User::factory()->create([
            'company_id' => $ws['company']->id,
            'role' => 'client',
            'email' => 'client@acme.test',
        ]);
        $ws['project']->clients()->attach($client->id, ['access_mode' => 'view']);
        $this->assertFalse($client->can('comment', $ws['task']));

        $ws['project']->clients()->updateExistingPivot($client->id, ['access_mode' => 'collaborate']);
        $client->unsetRelation('clientProjects');
        $this->assertTrue($client->fresh()->can('comment', $ws['task']->fresh()));
    }

    public function test_task_appears_in_two_projects(): void
    {
        $ws = $this->workspace('acme');
        $other = Project::factory()->create([
            'company_id' => $ws['company']->id,
            'created_by' => $ws['admin']->id,
            'name' => 'Second',
        ]);

        $this->actingAs($ws['admin'])
            ->post(route('company.tasks.projects.attach', [$ws['company']->slug, $ws['task']]), [
                'project_id' => $other->id,
            ])
            ->assertRedirect();

        $this->assertTrue($ws['task']->fresh()->projects()->where('projects.id', $other->id)->exists());
        $this->assertTrue($ws['task']->fresh()->projects()->where('projects.id', $ws['project']->id)->exists());
    }

    public function test_employee_can_create_personal_my_task(): void
    {
        $ws = $this->workspace('acme');

        $this->assertTrue($ws['employee']->can('create', Task::class));

        $task = Task::create([
            'company_id' => $ws['company']->id,
            'created_by' => $ws['employee']->id,
            'title' => 'Personal note',
            'status' => 'todo',
            'priority' => 'medium',
            'project_id' => null,
        ]);

        $this->assertTrue($ws['employee']->can('update', $task));
        $this->assertTrue($ws['employee']->can('delete', $task));
    }

    public function test_custom_role_without_permissions_overrides_default_employee_pack(): void
    {
        $ws = $this->workspace('acme');
        $employee = $ws['employee'];

        $this->assertTrue($employee->hasPermission('time.track'));

        $accountant = Role::query()->create([
            'company_id' => $ws['company']->id,
            'name' => 'Accountant',
            'slug' => 'accountant-test',
            'is_system' => false,
        ]);

        $employeeRole = Role::query()
            ->where('company_id', $ws['company']->id)
            ->where('slug', 'employee')
            ->first();

        $employee->workspaceRoles()->sync([$employeeRole->id, $accountant->id]);

        $employee->unsetRelation('workspaceRoles');

        $this->assertFalse($employee->hasPermission('time.track'));
        $this->assertFalse($employee->hasPermission('task.comment'));
    }

    public function test_project_member_can_view_project_with_minimal_columns(): void
    {
        $ws = $this->workspace('acme');
        $ws['project']->members()->attach($ws['employee']->id, ['role' => 'viewer']);

        $partial = Project::query()->whereKey($ws['project']->id)->first(['id', 'name', 'company_id']);

        $this->assertTrue($ws['employee']->can('view', $partial));
    }

    public function test_removing_project_member_asana_style(): void
    {
        $ws = $this->workspace('acme');
        $employee = $ws['employee'];
        $project = $ws['project'];
        $project->members()->attach($employee->id, ['role' => 'editor']);

        $open = $ws['task'];
        $open->update(['assigned_to' => $employee->id, 'status' => 'todo']);

        $done = Task::factory()->create([
            'company_id' => $ws['company']->id,
            'project_id' => $project->id,
            'created_by' => $ws['admin']->id,
            'assigned_to' => $employee->id,
            'status' => 'done',
            'title' => 'Shipped feature',
        ]);

        $this->assertTrue($employee->can('view', $project));

        $result = $project->revokeMemberAccess($employee);

        $this->assertSame(1, $result['open_unassigned']);
        $this->assertSame(1, $result['completed_kept']);
        $this->assertNull($open->fresh()->assigned_to);
        $this->assertSame($employee->id, $done->fresh()->assigned_to);
        $this->assertFalse($employee->fresh()->can('view', $project->fresh()));
        $this->assertTrue($employee->fresh()->can('view', $done->fresh()));
    }

    public function test_project_viewer_assignee_cannot_edit_or_comment(): void
    {
        $ws = $this->workspace('acme');
        $employee = $ws['employee'];
        $ws['project']->members()->attach($employee->id, ['role' => 'viewer']);
        $ws['task']->update(['assigned_to' => $employee->id]);

        $this->assertTrue($employee->can('view', $ws['task']));
        $this->assertFalse($employee->can('update', $ws['task']));
        $this->assertFalse($employee->can('comment', $ws['task']));
    }

    public function test_workspace_task_edit_allows_create_tasks_on_project(): void
    {
        $ws = $this->workspace('acme');
        $accountant = Role::query()->create([
            'company_id' => $ws['company']->id,
            'name' => 'Accountant',
            'slug' => 'accountant-perms',
            'is_system' => false,
        ]);
        $accountant->permissions()->sync(
            Permission::query()->whereIn('key', ['task.edit', 'member.invite'])->pluck('id')
        );
        $user = User::factory()->employee()->create([
            'company_id' => $ws['company']->id,
            'email' => 'acct@acme.test',
        ]);
        $user->workspaceRoles()->sync([$accountant->id]);
        $ws['project']->members()->attach($user->id, ['role' => 'editor']);

        $this->assertTrue($user->hasPermission('task.edit'));
        $this->assertTrue($user->hasPermission('member.invite'));
        $this->assertTrue($user->can('createTasks', $ws['project']));
    }

    public function test_employee_with_project_create_can_store_via_employee_portal(): void
    {
        $ws = $this->workspace('buildco');
        $builder = Role::query()->create([
            'company_id' => $ws['company']->id,
            'name' => 'Builder',
            'slug' => 'builder-role',
            'is_system' => false,
        ]);
        $builder->permissions()->sync(
            Permission::query()->where('key', 'project.create')->pluck('id')
        );
        $user = User::factory()->employee()->create([
            'company_id' => $ws['company']->id,
            'email' => 'builder@buildco.test',
        ]);
        $user->workspaceRoles()->sync([$builder->id]);

        $this->assertTrue($user->hasPermission('project.create'));

        $this->actingAs($user)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('employee.projects.store', $ws['company']->slug), [
                'name' => 'Delegated project',
                'status' => 'planning',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'company_id' => $ws['company']->id,
            'name' => 'Delegated project',
            'created_by' => $user->id,
        ]);
    }
}
