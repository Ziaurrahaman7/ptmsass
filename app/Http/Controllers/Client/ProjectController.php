<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function show(string $slug, Project $project)
    {
        $user = auth()->user();
        abort_unless($project->clients()->where('user_id', $user->id)->exists(), 403);

        $tasks = $project->tasks()
            ->whereNull('parent_task_id')
            ->with(['assignee', 'assignees'])
            ->orderBy('position')->orderByDesc('created_at')
            ->get();

        $tasksByStatus = [
            'in_review'   => $tasks->where('status', 'in_review')->values(),
            'todo'        => $tasks->where('status', 'todo')->values(),
            'in_progress' => $tasks->where('status', 'in_progress')->values(),
            'done'        => $tasks->where('status', 'done')->values(),
        ];

        $statusUpdates = $project->statusUpdates()->with('user')->take(15)->get();
        $milestones = $project->milestones()->with('assignee')->get();
        $resources = $project->resources()->get();

        return view('client.projects.show', compact(
            'project', 'tasks', 'tasksByStatus', 'statusUpdates', 'milestones', 'resources'
        ));
    }
}
