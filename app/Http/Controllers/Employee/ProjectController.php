<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Task;
use App\Services\AutomationEngine;
use App\Support\PermissionCatalog;
use App\Services\PermissionService;
use Illuminate\Http\Request;

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

        $canCreateTasks = auth()->user()->can('createTasks', $project);

        return view('employee.projects.show', compact(
            'project', 'tasks', 'sections', 'customFields', 'members', 'canSeeAllInProject',
            'projectAccessRole', 'projectAccessLabel', 'projectAccessSummary', 'assignedTasksOnly',
            'canCreateTasks'
        ));
    }

    public function storeTask(Request $request, string $slug, Project $project)
    {
        abort_if($project->company_id !== auth()->user()->company_id, 403);
        $this->authorize('createTasks', $project);

        $companyId = (int) auth()->user()->company_id;
        $priorityRule = 'in:'.Priority::forCompany($companyId)->pluck('slug')->implode(',');

        $data = $request->validate([
            'section_id'  => 'nullable|exists:sections,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:todo,in_progress,in_review,done',
            'priority'    => 'nullable|'.$priorityRule,
            'due_date'    => 'nullable|date',
        ]);

        $sectionId = $data['section_id'] ?? null;
        if ($sectionId && ! $project->sections()->where('id', $sectionId)->exists()) {
            $sectionId = null;
        }

        $task = Task::create([
            'project_id'  => $project->id,
            'section_id'  => $sectionId,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'todo',
            'priority'    => $data['priority'] ?? Priority::defaultSlugFor($companyId),
            'company_id'  => $companyId,
            'created_by'  => auth()->id(),
        ]);

        ActivityLog::create([
            'company_id'   => $companyId,
            'user_id'      => auth()->id(),
            'subject_type' => Task::class,
            'subject_id'   => $task->id,
            'action'       => 'created',
            'description'  => auth()->user()->name.' created this task',
        ]);

        app(AutomationEngine::class)->fire('task.created', $task);

        return redirect()
            ->route('employee.projects.show', [$slug, $project])
            ->with('success', 'Task created.');
    }
}
