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

    // Shared numbers + unfiltered chart-data snapshot used by the "Add chart" gallery/config modal
    private function chartGalleryContext(int $companyId): array
    {
        $completionRate = 0;
        $totalTasks = Task::where('company_id', $companyId)->count();
        $doneTasks  = Task::where('company_id', $companyId)->where('status', 'done')->count();
        if ($totalTasks > 0) $completionRate = round(($doneTasks / $totalTasks) * 100);

        $overdueCount    = Task::where('company_id', $companyId)->where('status', '!=', 'done')->whereNotNull('due_date')->whereDate('due_date', '<', today())->count();
        $noDueDateCount  = Task::where('company_id', $companyId)->where('status', '!=', 'done')->whereNull('due_date')->count();
        $unassignedCount = Task::where('company_id', $companyId)->where('status', '!=', 'done')->whereNull('assigned_to')->count();

        $xAxisKeys = ['assignee', 'project', 'status', 'priority', 'due_date', 'created_by',
            'no_due_date', 'unassigned', 'total_tasks', 'overdue_count', 'completion_rate',
            'project_status', 'upcoming_assignee', 'completed_over_time', 'done_this_week'];
        $chartData = collect($xAxisKeys)->mapWithKeys(fn($key) => [$key => $this->buildChartData($companyId, $key)]);

        return compact('completionRate', 'totalTasks', 'overdueCount', 'noDueDateCount', 'unassignedCount', 'chartData');
    }

    // Landing page — dashboard list (Asana Reporting style)
    public function index(string $slug)
    {
        $companyId = $this->companyId();

        $userDashboards = Dashboard::where('company_id', $companyId)
            ->where('user_id', auth()->id())
            ->latest()->get();

        $projects = Project::where('company_id', $companyId)->orderBy('name')->get();

        return view('company.insights.index', array_merge(
            $this->chartGalleryContext($companyId),
            compact('slug', 'userDashboards', 'projects')
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

    // Live preview data for the chart config modal (reflects the currently selected filters)
    public function chartPreview(Request $request, string $slug)
    {
        $data = $this->buildChartData($this->companyId(), $request->x_axis ?? 'assignee', [
            'project'    => ($request->project_filter  && $request->project_filter  !== 'all') ? $request->project_filter  : null,
            'status'     => ($request->status_filter    && $request->status_filter    !== 'all') ? $request->status_filter    : null,
            'priority'   => ($request->priority_filter  && $request->priority_filter  !== 'all') ? $request->priority_filter  : null,
            'date_range' => ($request->date_range       && $request->date_range       !== 'all') ? $request->date_range       : null,
        ]);

        return response()->json(['data' => $data]);
    }

    // Show a saved custom dashboard
    public function showDashboard(string $slug, Dashboard $dashboard)
    {
        abort_if($dashboard->company_id !== $this->companyId(), 403);

        $companyId = $this->companyId();
        $widgets = $dashboard->widgets;

        // Build chart data for each widget
        $widgetsData = $widgets->map(function ($widget) use ($companyId) {
            return [
                'widget'    => $widget,
                'chartData' => $widget->chart_style === 'text' ? [] : $this->buildChartData($companyId, $widget->x_axis, [
                    'project' => $widget->project_filter,
                    'status'  => $widget->status_filter,
                    'priority'=> $widget->priority_filter,
                    'date_range' => $widget->date_range,
                ]),
            ];
        });

        $projects = Project::where('company_id', $companyId)->orderBy('name')->get();

        // Legacy single-chart data (for backward compat)
        $chartData = $this->buildChartData($companyId, $dashboard->x_axis, [
            'project' => $dashboard->project_filter,
            'status'  => $dashboard->status_filter,
            'priority'=> $dashboard->priority_filter,
            'date_range' => $dashboard->date_range,
        ]);

        return view('company.insights.dashboard', array_merge(
            $this->chartGalleryContext($companyId),
            compact('slug', 'dashboard', 'chartData', 'widgetsData', 'projects')
        ));
    }

    // Store a widget on a dashboard
    public function storeWidget(Request $request, string $slug, Dashboard $dashboard)
    {
        abort_if($dashboard->company_id !== $this->companyId(), 403);

        $position = $dashboard->widgets()->count();
        $isText = $request->chart_style === 'text';

        $widget = DashboardWidget::create([
            'dashboard_id'   => $dashboard->id,
            'title'          => trim($request->title ?? '') ?: ($isText ? 'Note' : 'New Chart'),
            'chart_style'    => $request->chart_style ?? 'bar',
            'x_axis'         => $request->x_axis ?? 'assignee',
            'content'        => $isText ? $request->content : null,
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

    // $filters: ['project' => ?, 'status' => ?, 'priority' => ?, 'date_range' => ?]
    // status supports two virtual values on top of real statuses: 'incomplete' (!= done) and 'overdue' (!= done && past due)
    private function buildChartData(int $companyId, string $xAxis, array $filters = []): array
    {
        $today = today();

        $apply = function ($q, array $skip = []) use ($filters, $today) {
            if (!in_array('project', $skip) && !empty($filters['project'])) $q->where('project_id', $filters['project']);
            if (!in_array('priority', $skip) && !empty($filters['priority'])) $q->where('priority', $filters['priority']);
            if (!in_array('date_range', $skip) && !empty($filters['date_range'])) $q->whereDate('created_at', '>=', now()->subDays((int) $filters['date_range']));
            if (!in_array('status', $skip) && !empty($filters['status'])) {
                $status = $filters['status'];
                if ($status === 'incomplete') $q->where('status', '!=', 'done');
                elseif ($status === 'overdue') $q->where('status', '!=', 'done')->whereNotNull('due_date')->whereDate('due_date', '<', $today);
                else $q->where('status', $status);
            }
            return $q;
        };

        switch ($xAxis) {
            case 'assignee':
                return User::where('company_id', $companyId)->where('role', 'employee')
                    ->withCount(['assignedTasks as cnt' => fn($q) => $apply($q->where('company_id', $companyId))])
                    ->orderByDesc('cnt')->take(10)->get()
                    ->map(fn($u) => ['label' => $u->name, 'value' => $u->cnt])->values()->toArray();

            case 'project':
                return Project::where('company_id', $companyId)
                    ->withCount(['tasks as cnt' => fn($q) => $apply($q)])
                    ->orderByDesc('cnt')->take(8)->get()
                    ->map(fn($p) => ['label' => $p->name, 'value' => $p->cnt])->values()->toArray();

            case 'created_by':
                return $apply(Task::where('company_id', $companyId))
                    ->selectRaw('created_by, count(*) as cnt')->groupBy('created_by')
                    ->orderByDesc('cnt')->take(8)->get()
                    ->map(fn($t) => ['label' => User::find($t->created_by)?->name ?? 'Unknown', 'value' => $t->cnt])->values()->toArray();

            case 'status':
                return [
                    ['label' => 'Todo',        'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', 'todo')->count()],
                    ['label' => 'In Progress', 'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', 'in_progress')->count()],
                    ['label' => 'In Review',   'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', 'in_review')->count()],
                    ['label' => 'Done',        'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', 'done')->count()],
                ];

            case 'priority':
                return [
                    ['label' => 'Urgent', 'value' => $apply(Task::where('company_id', $companyId), ['priority'])->where('priority', 'urgent')->count()],
                    ['label' => 'High',   'value' => $apply(Task::where('company_id', $companyId), ['priority'])->where('priority', 'high')->count()],
                    ['label' => 'Medium', 'value' => $apply(Task::where('company_id', $companyId), ['priority'])->where('priority', 'medium')->count()],
                    ['label' => 'Low',    'value' => $apply(Task::where('company_id', $companyId), ['priority'])->where('priority', 'low')->count()],
                ];

            case 'due_date':
                return [
                    ['label' => 'Overdue',  'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', '!=', 'done')->whereNotNull('due_date')->whereDate('due_date', '<', $today)->count()],
                    ['label' => 'On track', 'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', '!=', 'done')->whereDate('due_date', '>=', $today)->count()],
                ];

            case 'no_due_date':
                return [['label' => 'No due date', 'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', '!=', 'done')->whereNull('due_date')->count()]];

            case 'unassigned':
                return [['label' => 'Unassigned', 'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', '!=', 'done')->whereNull('assigned_to')->count()]];

            case 'total_tasks':
                return [['label' => 'Total tasks', 'value' => $apply(Task::where('company_id', $companyId))->count()]];

            case 'overdue_count':
                return [['label' => 'Overdue', 'value' => $apply(Task::where('company_id', $companyId), ['status'])->where('status', '!=', 'done')->whereNotNull('due_date')->whereDate('due_date', '<', $today)->count()]];

            case 'completion_rate':
                $base  = $apply(Task::where('company_id', $companyId), ['status']);
                $total = (clone $base)->count();
                $done  = (clone $base)->where('status', 'done')->count();
                return [['label' => 'Completion rate', 'value' => $total > 0 ? round(($done / $total) * 100) : 0]];

            case 'project_status':
                $labels = ['planning' => 'Planning', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed'];
                return collect($labels)->map(function ($label, $key) use ($companyId, $filters) {
                    $q = Project::where('company_id', $companyId)->where('status', $key);
                    if (!empty($filters['project'])) $q->where('id', $filters['project']);
                    return ['label' => $label, 'value' => $q->count()];
                })->values()->toArray();

            case 'upcoming_assignee':
                return User::where('company_id', $companyId)->where('role', 'employee')
                    ->withCount(['assignedTasks as cnt' => fn($q) => $apply($q->where('company_id', $companyId), ['status'])
                        ->where('status', '!=', 'done')->whereNotNull('due_date')
                        ->whereBetween('due_date', [$today, (clone $today)->addDays(7)])])
                    ->orderByDesc('cnt')->take(10)->get()
                    ->map(fn($u) => ['label' => $u->name, 'value' => $u->cnt])->values()->toArray();

            case 'completed_over_time':
                $days = [];
                for ($i = 9; $i >= 0; $i--) {
                    $date = (clone $today)->subDays($i);
                    $days[] = ['label' => $date->format('d M'), 'value' => $apply(Task::where('company_id', $companyId), ['status', 'date_range'])->where('status', 'done')->whereDate('updated_at', $date)->count()];
                }
                return $days;

            case 'done_this_week':
                $days = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = (clone $today)->subDays($i);
                    $days[] = ['label' => $date->format('D'), 'value' => $apply(Task::where('company_id', $companyId), ['status', 'date_range'])->where('status', 'done')->whereDate('updated_at', $date)->count()];
                }
                return $days;

            default:
                return [];
        }
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
