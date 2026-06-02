<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;

        return view('company.dashboard', [
            'totalProjects'    => Project::where('company_id', $company->id)->count(),
            'activeProjects'   => Project::where('company_id', $company->id)->where('status', 'in_progress')->count(),
            'totalTasks'       => Task::where('company_id', $company->id)->count(),
            'doneTasks'        => Task::where('company_id', $company->id)->where('status', 'done')->count(),
            'overdueTasks'     => Task::where('company_id', $company->id)->whereNot('status','done')->whereDate('due_date', '<', today())->count(),
            'recentProjects'   => Project::where('company_id', $company->id)->withCount('tasks')->latest()->take(5)->get(),
            'recentTasks'      => Task::where('company_id', $company->id)->with(['project','assignee'])->latest()->take(5)->get(),
        ]);
    }
}
