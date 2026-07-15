<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class InsightController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    // Landing page — dashboard list (Asana Reporting style)
    public function index(string $slug)
    {
        $companyId = $this->companyId();

        $completionRate = 0;
        $totalTasks = Task::where('company_id', $companyId)->count();
        $doneTasks  = Task::where('company_id', $companyId)->where('status', 'done')->count();
        if ($totalTasks > 0) $completionRate = round(($doneTasks / $totalTasks) * 100);

        $overdueCount    = Task::where('company_id', $companyId)->where('status', '!=', 'done')->whereNotNull('due_date')->whereDate('due_date', '<', today())->count();
        $noDueDateCount  = Task::where('company_id', $companyId)->where('status', '!=', 'done')->whereNull('due_date')->count();
        $unassignedCount = Task::where('company_id', $companyId)->where('status', '!=', 'done')->whereNull('assigned_to')->count();

        $chartData = [
            'assignee' => User::where('company_id', $companyId)->where('role', 'employee')
                ->withCount(['assignedTasks as cnt' => fn($q) => $q->where('company_id', $companyId)])
                ->orderByDesc('cnt')->take(10)->get()
                ->map(fn($u) => ['label' => $u->name, 'value' => $u->cnt])->values(),
            'project' => Project::where('company_id', $companyId)
                ->withCount('tasks as cnt')->orderByDesc('cnt')->take(8)->get()
                ->map(fn($p) => ['label' => $p->name, 'value' => $p->cnt])->values(),
            'status' => [
                ['label' => 'Todo',        'value' => Task::where('company_id', $companyId)->where('status', 'todo')->count()],
                ['label' => 'In Progress', 'value' => Task::where('company_id', $companyId)->where('status', 'in_progress')->count()],
                ['label' => 'In Review',   'value' => Task::where('company_id', $companyId)->where('status', 'in_review')->count()],
                ['label' => 'Done',        'value' => Task::where('company_id', $companyId)->where('status', 'done')->count()],
            ],
            'priority' => [
                ['label' => 'Urgent', 'value' => Task::where('company_id', $companyId)->where('priority', 'urgent')->count()],
                ['label' => 'High',   'value' => Task::where('company_id', $companyId)->where('priority', 'high')->count()],
                ['label' => 'Medium', 'value' => Task::where('company_id', $companyId)->where('priority', 'medium')->count()],
                ['label' => 'Low',    'value' => Task::where('company_id', $companyId)->where('priority', 'low')->count()],
            ],
            'due_date' => [
                ['label' => 'Overdue',  'value' => $overdueCount],
                ['label' => 'On track', 'value' => $totalTasks - $overdueCount],
            ],
            'created_by' => Task::where('company_id', $companyId)
                ->selectRaw('created_by, count(*) as cnt')
                ->groupBy('created_by')
                ->orderByDesc('cnt')->take(8)->get()
                ->map(fn($t) => ['label' => User::find($t->created_by)?->name ?? 'Unknown', 'value' => $t->cnt])->values(),
        ];

        return view('company.insights.index', compact(
            'slug', 'completionRate', 'totalTasks', 'overdueCount', 'noDueDateCount', 'unassignedCount', 'chartData'
        ));
    }

    // Dashboard detail page
    public function show(string $slug, string $type)
    {
        $companyId = $this->companyId();

        $taskStats = [
            'total'       => Task::where('company_id', $companyId)->count(),
            'todo'        => Task::where('company_id', $companyId)->where('status', 'todo')->count(),
            'in_progress' => Task::where('company_id', $companyId)->where('status', 'in_progress')->count(),
            'in_review'   => Task::where('company_id', $companyId)->where('status', 'in_review')->count(),
            'done'        => Task::where('company_id', $companyId)->where('status', 'done')->count(),
        ];

        $priorityStats = [
            'urgent' => Task::where('company_id', $companyId)->where('priority', 'urgent')->where('status', '!=', 'done')->count(),
            'high'   => Task::where('company_id', $companyId)->where('priority', 'high')->where('status', '!=', 'done')->count(),
            'medium' => Task::where('company_id', $companyId)->where('priority', 'medium')->where('status', '!=', 'done')->count(),
            'low'    => Task::where('company_id', $companyId)->where('priority', 'low')->where('status', '!=', 'done')->count(),
        ];

        $projects = Project::where('company_id', $companyId)
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn($q) => $q->where('status', 'done')])
            ->orderBy('name')->get();

        $overdueTasks = Task::where('company_id', $companyId)
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->with(['project', 'assignee'])
            ->orderBy('due_date')->get();

        $members = User::where('company_id', $companyId)
            ->where('role', 'employee')->where('is_active', true)
            ->withCount([
                'assignedTasks as total_tasks'     => fn($q) => $q->where('company_id', $companyId),
                'assignedTasks as open_tasks'      => fn($q) => $q->where('company_id', $companyId)->where('status', '!=', 'done'),
                'assignedTasks as completed_tasks' => fn($q) => $q->where('company_id', $companyId)->where('status', 'done'),
            ])
            ->orderByDesc('total_tasks')->get();

        $completedByDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $completedByDay[] = [
                'label' => $date->format('D'),
                'count' => Task::where('company_id', $companyId)
                    ->where('status', 'done')
                    ->whereDate('updated_at', $date)->count(),
            ];
        }

        $completionRate = $taskStats['total'] > 0
            ? round(($taskStats['done'] / $taskStats['total']) * 100) : 0;

        $dashboardTitles = [
            'my-impact'       => ['title' => 'My Impact',       'desc' => 'See the impact of your work'],
            'my-organization' => ['title' => 'My Organization', 'desc' => 'Metrics across your organization'],
        ];
        $dashboard = $dashboardTitles[$type] ?? ['title' => 'Dashboard', 'desc' => ''];

        return view('company.insights.show', compact(
            'taskStats', 'priorityStats', 'projects', 'overdueTasks',
            'members', 'completedByDay', 'completionRate', 'slug', 'type', 'dashboard'
        ));
    }
}
