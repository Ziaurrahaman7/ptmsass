<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(string $slug)
    {
        $company = auth()->user()->company;
        $companyId = $company->id;

        // Basic stats
        $totalProjects = Project::where('company_id', $companyId)->count();
        $activeProjects = Project::where('company_id', $companyId)->where('status', 'in_progress')->count();
        $completedProjects = Project::where('company_id', $companyId)->where('status', 'completed')->count();
        $onHoldProjects = Project::where('company_id', $companyId)->where('status', 'on_hold')->count();
        
        $totalTasks = Task::where('company_id', $companyId)->count();
        $doneTasks = Task::where('company_id', $companyId)->where('status', 'done')->count();
        $inProgressTasks = Task::where('company_id', $companyId)->where('status', 'in_progress')->count();
        $todoTasks = Task::where('company_id', $companyId)->where('status', 'todo')->count();
        $inReviewTasks = Task::where('company_id', $companyId)->where('status', 'in_review')->count();
        
        // Task completion rate
        $completionRate = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100, 1) : 0;
        
        // Priority breakdown (ordered highest-urgency first, for the dashboard widget)
        $priorityBreakdown = Priority::forCompany($companyId)->reverse()->values()->map(fn ($p) => [
            'label' => $p->name,
            'value' => Task::where('company_id', $companyId)->where('priority', $p->slug)->whereNot('status', 'done')->count(),
            'color' => $p->color,
        ]);
        
        // Deadline stats
        $overdueTasks = Task::where('company_id', $companyId)
            ->whereNot('status', 'done')
            ->whereDate('due_date', '<', today())
            ->count();
        
        $dueTodayTasks = Task::where('company_id', $companyId)
            ->whereNot('status', 'done')
            ->whereDate('due_date', '=', today())
            ->count();
        
        $upcomingTasks = Task::where('company_id', $companyId)
            ->whereNot('status', 'done')
            ->whereBetween('due_date', [today()->addDay(), today()->addDays(7)])
            ->with(['project', 'assignee'])
            ->orderBy('due_date')
            ->take(5)
            ->get();
        
        // Team stats
        $totalMembers = User::where('company_id', $companyId)->where('is_active', true)->count();
        $activeMembers = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('assignedTasks', function($q) {
                $q->whereNot('status', 'done');
            })
            ->count();
        
        // Top performers (employees with most completed tasks)
        $topPerformers = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('role', 'employee')
            ->withCount(['assignedTasks as completed_tasks_count' => function($q) {
                $q->where('status', 'done');
            }])
            ->having('completed_tasks_count', '>', 0)
            ->orderBy('completed_tasks_count', 'desc')
            ->take(5)
            ->get();
        
        // Recent activities
        $recentActivities = ActivityLog::where('company_id', $companyId)
            ->with('user')
            ->latest()
            ->take(10)
            ->get();
        
        // Recent projects & tasks
        $recentProjects = Project::where('company_id', $companyId)
            ->withCount('tasks')
            ->latest()
            ->take(5)
            ->get();
        
        $recentTasks = Task::where('company_id', $companyId)
            ->with(['project', 'assignee'])
            ->latest()
            ->take(8)
            ->get();

        return view('company.dashboard', compact(
            'totalProjects', 'activeProjects', 'completedProjects', 'onHoldProjects',
            'totalTasks', 'doneTasks', 'inProgressTasks', 'todoTasks', 'inReviewTasks',
            'completionRate',
            'priorityBreakdown',
            'overdueTasks', 'dueTodayTasks', 'upcomingTasks',
            'totalMembers', 'activeMembers', 'topPerformers',
            'recentActivities', 'recentProjects', 'recentTasks'
        ));
    }
}
