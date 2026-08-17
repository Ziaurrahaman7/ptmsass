<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ProjectStatusUpdate;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index(string $slug)
    {
        $user = auth()->user();

        $projects = $user->clientProjects()
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done')])
            ->with('latestStatusUpdate')
            ->orderBy('name')
            ->get();

        $projectIds = $projects->pluck('id');

        $totalTasks = Task::whereIn('project_id', $projectIds)->count();
        $doneTasks = Task::whereIn('project_id', $projectIds)->where('status', 'done')->count();
        $completionRate = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

        $inReviewTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', 'in_review')
            ->with('project')
            ->orderByDesc('updated_at')
            ->get();

        $overdueTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        $recentStatusUpdates = ProjectStatusUpdate::whereIn('project_id', $projectIds)
            ->with(['project', 'user'])
            ->latest()
            ->take(6)
            ->get();

        return view('client.dashboard', compact(
            'projects', 'totalTasks', 'doneTasks', 'completionRate',
            'inReviewTasks', 'overdueTasks', 'recentStatusUpdates'
        ));
    }
}
