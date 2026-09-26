<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Support\PermissionCatalog;
use App\Services\PermissionService;

class ProjectController extends Controller
{
    public function show(string $slug, Project $project)
    {
        abort_if($project->company_id !== auth()->user()->company_id, 403);
        $this->authorize('view', $project);

        $userId = auth()->id();
        $perms = app(PermissionService::class);
        $user = auth()->user();

        $query = Task::where('project_id', $project->id)
            ->whereNull('parent_task_id')
            ->with(['assignees', 'assignee', 'section', 'subtasks' => fn ($q) => $q->with('assignees')->orderBy('position')->orderByDesc('created_at')])
            ->withCount(['comments', 'subtasks']);

        $canSeeAllInProject = $user->isCompanyAdmin()
            || $perms->projectAtLeast($user, $project, 'viewer');

        if (! $canSeeAllInProject) {
            $query->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                    ->orWhereHas('assignees', fn ($q) => $q->where('user_id', $userId));
            });
        }

        $tasks = $query->latest()->get();

        abort_if(! $canSeeAllInProject && $tasks->isEmpty(), 403);

        $sections = $project->sections()->get();
        $customFields = $project->customFields()->get();
        $members = auth()->user()->company->users()->where('is_active', true)->get(['id', 'name']);

        $projectAccessRole = $perms->projectLevel($user, $project);
        $assignedTasksOnly = ! $canSeeAllInProject;
        $projectAccessLabel = PermissionCatalog::projectRoleLabel($projectAccessRole);
        $projectAccessSummary = PermissionCatalog::projectAccessSummary($projectAccessRole, $assignedTasksOnly);

        return view('employee.projects.show', compact(
            'project', 'tasks', 'sections', 'customFields', 'members', 'canSeeAllInProject',
            'projectAccessRole', 'projectAccessLabel', 'projectAccessSummary', 'assignedTasksOnly'
        ));
    }
}
