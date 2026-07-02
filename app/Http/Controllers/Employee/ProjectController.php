<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;

class ProjectController extends Controller
{
    /**
     * Show a single project as an Asana-style list of tasks grouped by status.
     * Employee can only open a project where they have at least one assigned task.
     */
    public function show(string $slug, Project $project)
    {
        $userId = auth()->id();

        // Must belong to the same company
        abort_if($project->company_id !== auth()->user()->company_id, 403);

        // Must have at least one task assigned to the employee in this project
        $hasAccess = Task::where('project_id', $project->id)
            ->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->orWhereHas('assignees', fn($q) => $q->where('user_id', $userId));
            })
            ->exists();

        abort_if(! $hasAccess, 403);

        // All parent tasks of the project, grouped by section for the list view
        $tasks = Task::where('project_id', $project->id)
            ->whereNull('parent_task_id')
            ->with(['assignees', 'assignee', 'section', 'subtasks' => fn($q) => $q->with('assignees')->orderBy('position')->orderByDesc('created_at')])
            ->withCount(['comments', 'subtasks'])
            ->latest()
            ->get();

        $sections = $project->sections()->get();
        $customFields = $project->customFields()->get();
        $members = auth()->user()->company->users()->where('is_active', true)->get(['id', 'name']);

        return view('employee.projects.show', compact('project', 'tasks', 'sections', 'customFields', 'members'));
    }
}
