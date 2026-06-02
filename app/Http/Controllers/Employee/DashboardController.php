<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index(string $slug)
    {
        $userId = auth()->id();

        return view('employee.dashboard', [
            'totalAssigned' => Task::where('assigned_to', $userId)->count(),
            'inProgress'    => Task::where('assigned_to', $userId)->where('status', 'in_progress')->count(),
            'inReview'      => Task::where('assigned_to', $userId)->where('status', 'in_review')->count(),
            'done'          => Task::where('assigned_to', $userId)->where('status', 'done')->count(),
            'overdue'       => Task::where('assigned_to', $userId)->whereNotIn('status', ['done'])->whereDate('due_date', '<', today())->count(),
            'myTasks'       => Task::where('assigned_to', $userId)->with('project')->latest()->take(10)->get(),
        ]);
    }
}
