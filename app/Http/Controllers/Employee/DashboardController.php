<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index(string $slug)
    {
        $userId = auth()->id();

        // Basic stats
        $totalAssigned = Task::where('assigned_to', $userId)->count();
        $todoTasks = Task::where('assigned_to', $userId)->where('status', 'todo')->count();
        $inProgress = Task::where('assigned_to', $userId)->where('status', 'in_progress')->count();
        $inReview = Task::where('assigned_to', $userId)->where('status', 'in_review')->count();
        $done = Task::where('assigned_to', $userId)->where('status', 'done')->count();
        
        // Completion rate
        $completionRate = $totalAssigned > 0 ? round(($done / $totalAssigned) * 100, 1) : 0;
        
        // Priority breakdown
        $urgentTasks = Task::where('assigned_to', $userId)->where('priority', 'urgent')->whereNot('status', 'done')->count();
        $highPriorityTasks = Task::where('assigned_to', $userId)->where('priority', 'high')->whereNot('status', 'done')->count();
        $mediumPriorityTasks = Task::where('assigned_to', $userId)->where('priority', 'medium')->whereNot('status', 'done')->count();
        $lowPriorityTasks = Task::where('assigned_to', $userId)->where('priority', 'low')->whereNot('status', 'done')->count();
        
        // Deadline stats
        $overdue = Task::where('assigned_to', $userId)
            ->whereNot('status', 'done')
            ->whereDate('due_date', '<', today())
            ->count();
        
        $dueToday = Task::where('assigned_to', $userId)
            ->whereNot('status', 'done')
            ->whereDate('due_date', '=', today())
            ->count();
        
        $upcomingTasks = Task::where('assigned_to', $userId)
            ->whereNot('status', 'done')
            ->whereBetween('due_date', [today()->addDay(), today()->addDays(7)])
            ->with('project')
            ->orderBy('due_date')
            ->take(5)
            ->get();
        
        // Recent activities
        $recentActivities = ActivityLog::where('company_id', auth()->user()->company_id)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('subject', function($sq) use ($userId) {
                      $sq->where('assigned_to', $userId);
                  });
            })
            ->with('user')
            ->latest()
            ->take(10)
            ->get();
        
        // My tasks
        $myTasks = Task::where('assigned_to', $userId)
            ->with('project')
            ->latest()
            ->take(8)
            ->get();

        return view('employee.dashboard', compact(
            'totalAssigned', 'todoTasks', 'inProgress', 'inReview', 'done',
            'completionRate',
            'urgentTasks', 'highPriorityTasks', 'mediumPriorityTasks', 'lowPriorityTasks',
            'overdue', 'dueToday', 'upcomingTasks',
            'recentActivities', 'myTasks'
        ));
    }
}
