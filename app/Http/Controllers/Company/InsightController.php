<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

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

        $userDashboards = Dashboard::where('company_id', $companyId)
            ->where('user_id', auth()->id())
            ->latest()->get();

        return view('company.insights.index', compact(
            'slug', 'completionRate', 'totalTasks', 'overdueCount', 'noDueDateCount', 'unassignedCount', 'chartData', 'userDashboards'
        ));
    }

    // Store a new custom dashboard
    public function storeDashboard(Request $request, string $slug)
    {
        $title = trim($request->title ?? '') ?: 'New Dashboard';

        $dashboard = Dashboard::create([
            'company_id'     => $this->companyId(),
            'user_id'        => auth()->id(),
            'title'          => $title,
            'chart_style'    => $request->chart_style ?? 'bar',
            'x_axis'         => $request->x_axis ?? 'assignee',
            'report_on'      => $request->report_on ?? 'tasks',
            'project_filter' => ($request->project_filter && $request->project_filter !== 'all') ? $request->project_filter : null,
            'status_filter'  => ($request->status_filter  && $request->status_filter  !== 'all') ? $request->status_filter  : null,
            'priority_filter'=> ($request->priority_filter && $request->priority_filter !== 'all') ? $request->priority_filter : null,
            'date_range'     => ($request->date_range && $request->date_range !== 'all') ? $request->date_range : null,
        ]);

        return response()->json([
            'url'   => route('company.insights.dashboards.show', [$slug, $dashboard->id]),
            'id'    => $dashboard->id,
            'title' => $dashboard->title,
        ]);
    }

    // Show a saved custom dashboard
    public function showDashboard(string $slug, Dashboard $dashboard)
    {
        abort_if($dashboard->company_id !== $this->companyId(), 403);

        $companyId = $this->companyId();
        $widgets = $dashboard->widgets;

        // Build chart data for each widget
        $widgetsData = $widgets->map(function ($widget) use ($companyId) {
            $q = Task::where('company_id', $companyId);
            if ($widget->project_filter) $q->where('project_id', $widget->project_filter);
            if ($widget->status_filter)  $q->where('status', $widget->status_filter);
            if ($widget->priority_filter) $q->where('priority', $widget->priority_filter);
            if ($widget->date_range && $widget->date_range !== 'all') {
                $q->whereDate('created_at', '>=', now()->subDays((int)$widget->date_range));
            }
            return [
                'widget'    => $widget,
                'chartData' => $this->buildChartData($companyId, $widget->x_axis, clone $q),
            ];
        });

        $projects = Project::where('company_id', $companyId)->orderBy('name')->get();

        // Legacy single-chart data (for backward compat)
        $q = Task::where('company_id', $companyId);
        if ($dashboard->project_filter) $q->where('project_id', $dashboard->project_filter);
        if ($dashboard->status_filter)  $q->where('status', $dashboard->status_filter);
        if ($dashboard->priority_filter) $q->where('priority', $dashboard->priority_filter);
        if ($dashboard->date_range && $dashboard->date_range !== 'all') {
            $q->whereDate('created_at', '>=', now()->subDays((int)$dashboard->date_range));
        }
        $chartData = $this->buildChartData($companyId, $dashboard->x_axis, clone $q);

        return view('company.insights.dashboard', compact('slug', 'dashboard', 'chartData', 'widgetsData', 'projects'));
    }

    // Store a widget on a dashboard
    public function storeWidget(Request $request, string $slug, Dashboard $dashboard)
    {
        abort_if($dashboard->company_id !== $this->companyId(), 403);

        $position = $dashboard->widgets()->count();

        $widget = DashboardWidget::create([
            'dashboard_id'   => $dashboard->id,
            'title'          => trim($request->title ?? '') ?: 'New Chart',
            'chart_style'    => $request->chart_style ?? 'bar',
            'x_axis'         => $request->x_axis ?? 'assignee',
            'project_filter' => ($request->project_filter && $request->project_filter !== 'all') ? $request->project_filter : null,
            'status_filter'  => ($request->status_filter  && $request->status_filter  !== 'all') ? $request->status_filter  : null,
            'priority_filter'=> ($request->priority_filter && $request->priority_filter !== 'all') ? $request->priority_filter : null,
            'date_range'     => ($request->date_range && $request->date_range !== 'all') ? $request->date_range : null,
            'position'       => $position,
        ]);

        return response()->json(['success' => true, 'widget_id' => $widget->id]);
    }

    // Delete a widget
    public function destroyWidget(string $slug, Dashboard $dashboard, DashboardWidget $widget)
    {
        abort_if($dashboard->company_id !== $this->companyId(), 403);
        abort_if($widget->dashboard_id !== $dashboard->id, 403);
        $widget->delete();
        return response()->json(['success' => true]);
    }

    public function destroyDashboard(string $slug, Dashboard $dashboard)
    {
        abort_if($dashboard->company_id !== $this->companyId(), 403);
        $dashboard->delete();
        return redirect()->route('company.insights.index', $slug);
    }

    private function buildChartData(int $companyId, string $xAxis, $baseQuery): array
    {
        return match($xAxis) {
            'assignee' => User::where('company_id', $companyId)->where('role', 'employee')
                ->withCount(['assignedTasks as cnt' => fn($q) => $q->where('company_id', $companyId)])
                ->orderByDesc('cnt')->take(10)->get()
                ->map(fn($u) => ['label' => $u->name, 'value' => $u->cnt])->values()->toArray(),
            'project'  => Project::where('company_id', $companyId)
                ->withCount('tasks as cnt')->orderByDesc('cnt')->take(8)->get()
                ->map(fn($p) => ['label' => $p->name, 'value' => $p->cnt])->values()->toArray(),
            'status'   => [
                ['label' => 'Todo',        'value' => (clone $baseQuery)->where('status', 'todo')->count()],
                ['label' => 'In Progress', 'value' => (clone $baseQuery)->where('status', 'in_progress')->count()],
                ['label' => 'In Review',   'value' => (clone $baseQuery)->where('status', 'in_review')->count()],
                ['label' => 'Done',        'value' => (clone $baseQuery)->where('status', 'done')->count()],
            ],
            'priority' => [
                ['label' => 'Urgent', 'value' => (clone $baseQuery)->where('priority', 'urgent')->count()],
                ['label' => 'High',   'value' => (clone $baseQuery)->where('priority', 'high')->count()],
                ['label' => 'Medium', 'value' => (clone $baseQuery)->where('priority', 'medium')->count()],
                ['label' => 'Low',    'value' => (clone $baseQuery)->where('priority', 'low')->count()],
            ],
            'due_date' => [
                ['label' => 'Overdue',  'value' => (clone $baseQuery)->where('status', '!=', 'done')->whereNotNull('due_date')->whereDate('due_date', '<', today())->count()],
                ['label' => 'On track', 'value' => (clone $baseQuery)->where('status', '!=', 'done')->whereDate('due_date', '>=', today())->count()],
            ],
            default => [],
        };
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
