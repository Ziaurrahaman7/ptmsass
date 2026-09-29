<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Models\Project;
use App\Models\ProjectForm;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\TaskTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GapWorkspaceController extends Controller
{
    protected function companyId(): int
    {
        return (int) auth()->user()->company_id;
    }

    public function templates(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('template.manage'), 403);
        $templates = TaskTemplate::query()->where('company_id', $this->companyId())->latest()->get();
        $projects = Project::query()->where('company_id', $this->companyId())->where('is_template', false)->orderBy('name')->get();

        return view('company.gap.templates', compact('templates', 'projects'));
    }

    public function storeTemplate(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('template.manage'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'due_in_days' => 'nullable|integer|min:0',
            'subtasks' => 'nullable|string',
        ]);
        TaskTemplate::create([
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? null,
            'due_in_days' => $data['due_in_days'] ?? null,
            'subtasks' => array_values(array_filter(array_map('trim', explode("\n", $data['subtasks'] ?? '')))),
        ]);

        return back()->with('success', 'Template saved.');
    }

    public function applyTemplate(Request $request, string $slug, TaskTemplate $template, TaskTemplateService $service)
    {
        abort_unless(auth()->user()->hasPermission('template.manage'), 403);
        abort_if((int) $template->company_id !== $this->companyId(), 403);
        $data = $request->validate(['project_id' => 'required|exists:projects,id']);
        $project = Project::query()->where('company_id', $this->companyId())->findOrFail($data['project_id']);
        $this->authorize('createTasks', $project);
        $service->apply($template, $project, auth()->user());

        return back()->with('success', 'Task created from template.');
    }

    public function forms(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('form.manage'), 403);
        $forms = ProjectForm::query()->where('company_id', $this->companyId())->with('project')->latest()->get();
        $projects = Project::query()->where('company_id', $this->companyId())->where('is_template', false)->orderBy('name')->get();

        return view('company.gap.forms', compact('forms', 'projects'));
    }

    public function storeForm(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('form.manage'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
        ]);
        $project = Project::query()->where('company_id', $this->companyId())->findOrFail($data['project_id']);
        ProjectForm::create([
            'company_id' => $this->companyId(),
            'project_id' => $project->id,
            'created_by' => auth()->id(),
            'name' => $data['name'],
            'token' => Str::random(40),
            'is_active' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'maps_to' => 'title'],
                ['key' => 'description', 'label' => 'Details', 'type' => 'textarea', 'maps_to' => 'description'],
                ['key' => 'due_date', 'label' => 'Due date', 'type' => 'date', 'maps_to' => 'due_date'],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'text', 'maps_to' => 'priority'],
            ],
        ]);

        return back()->with('success', 'Form created. Share the public link.');
    }

    public function rules(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('rule.manage'), 403);
        $rules = AutomationRule::query()->where('company_id', $this->companyId())->with('project')->latest()->get();
        $projects = Project::query()->where('company_id', $this->companyId())->where('is_template', false)->orderBy('name')->get();

        return view('company.gap.rules', compact('rules', 'projects'));
    }

    public function storeRule(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('rule.manage'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'trigger' => 'required|in:task.created,task.status_changed',
            'condition_status' => 'nullable|string',
            'action_status' => 'nullable|string',
            'action_comment' => 'nullable|string|max:500',
        ]);
        AutomationRule::create([
            'company_id' => $this->companyId(),
            'project_id' => $data['project_id'] ?? null,
            'name' => $data['name'],
            'trigger' => $data['trigger'],
            'conditions' => array_filter(['status' => $data['condition_status'] ?? null]),
            'actions' => array_filter([
                'status' => $data['action_status'] ?? null,
                'comment' => $data['action_comment'] ?? null,
            ]),
            'is_active' => true,
        ]);

        return back()->with('success', 'Rule saved.');
    }

    public function timesheets(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('time.review'), 403);
        if ($request->filled('week') && preg_match('/^(\d{4})-W(\d{2})$/', $request->input('week'), $m)) {
            $weekStart = \Carbon\Carbon::now()->setISODate((int) $m[1], (int) $m[2])->startOfWeek();
        } else {
            $weekStart = now()->startOfWeek();
        }
        $weekEnd = $weekStart->copy()->endOfWeek();

        $entries = TimeEntry::query()
            ->where('company_id', $this->companyId())
            ->where('status', '!=', 'running')
            ->whereBetween('worked_on', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->with('user', 'task', 'project')
            ->latest('worked_on')
            ->paginate(40)
            ->withQueryString();

        $weekTotals = TimeEntry::query()
            ->where('company_id', $this->companyId())
            ->finished()
            ->whereBetween('worked_on', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->selectRaw('sum(minutes) as total, sum(case when billable = 1 then minutes else 0 end) as billable')
            ->first();

        return view('company.gap.timesheets', compact('entries', 'weekStart', 'weekEnd', 'weekTotals'));
    }

    public function reviewTime(Request $request, string $slug, TimeEntry $time_entry)
    {
        abort_unless(auth()->user()->hasPermission('time.review'), 403);
        abort_if((int) $time_entry->company_id !== $this->companyId(), 403);
        $data = $request->validate(['status' => 'required|in:approved,rejected']);
        $time_entry->update(['status' => $data['status']]);

        return back()->with('success', 'Timesheet '.$data['status'].'.');
    }

    public function capacity(string $slug)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);
        $people = User::query()
            ->where('company_id', $this->companyId())
            ->whereIn('role', ['employee', 'company_admin'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $weekStart = now()->startOfWeek();
        $rows = $people->map(function (User $user) use ($weekStart) {
            $minutes = TimeEntry::query()
                ->where('user_id', $user->id)
                ->whereBetween('worked_on', [$weekStart->toDateString(), $weekStart->copy()->endOfWeek()->toDateString()])
                ->sum('minutes');
            $open = Task::query()
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'done')
                ->where(function ($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                        ->orWhereHas('assignees', fn ($a) => $a->where('users.id', $user->id));
                })
                ->count();
            $capacity = max(1, (int) $user->weekly_capacity_hours);
            $usedHours = round($minutes / 60, 1);

            return [
                'user' => $user,
                'capacity' => $capacity,
                'used' => $usedHours,
                'open' => $open,
                'overloaded' => $usedHours > $capacity || $open > $capacity,
            ];
        });

        return view('company.gap.capacity', compact('rows'));
    }

    public function updateCapacity(Request $request, string $slug, User $user)
    {
        abort_unless(auth()->user()->isCompanyAdmin(), 403);
        abort_if((int) $user->company_id !== $this->companyId(), 403);
        $data = $request->validate(['weekly_capacity_hours' => 'required|integer|min:1|max:80']);
        $user->update($data);

        return back()->with('success', 'Capacity updated.');
    }

    public function reports(string $slug, Request $request)
    {
        abort_unless(auth()->user()->hasPermission('report.view'), 403);
        $projectId = $request->integer('project_id') ?: null;
        $companyId = $this->companyId();
        $tasks = Task::query()
            ->where('company_id', $companyId)
            ->when($projectId, function ($q) use ($projectId) {
                $q->where(function ($inner) use ($projectId) {
                    $inner->where('project_id', $projectId)
                        ->orWhereHas('projects', fn ($p) => $p->where('projects.id', $projectId));
                });
            })
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $projects = Project::query()->where('company_id', $companyId)->orderBy('name')->get();

        $timeBase = TimeEntry::query()
            ->where('company_id', $companyId)
            ->finished()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId));

        $timeSummary = [
            'total_minutes' => (int) (clone $timeBase)->sum('minutes'),
            'billable_minutes' => (int) (clone $timeBase)->where('billable', true)->sum('minutes'),
            'entry_count' => (int) (clone $timeBase)->count(),
        ];

        $timeByPerson = (clone $timeBase)
            ->selectRaw('user_id, sum(minutes) as total_minutes, sum(case when billable = 1 then minutes else 0 end) as billable_minutes')
            ->groupBy('user_id')
            ->orderByDesc('total_minutes')
            ->get()
            ->map(function ($row) {
                $row->user = User::query()->find($row->user_id);

                return $row;
            });

        $timeByProject = TimeEntry::query()
            ->where('company_id', $companyId)
            ->finished()
            ->whereNotNull('project_id')
            ->selectRaw('project_id, sum(minutes) as total_minutes, sum(case when billable = 1 then minutes else 0 end) as billable_minutes')
            ->groupBy('project_id')
            ->orderByDesc('total_minutes')
            ->get()
            ->map(function ($row) {
                $row->project = Project::query()->find($row->project_id);

                return $row;
            });

        $timeByTask = $projectId
            ? (clone $timeBase)
                ->whereNotNull('task_id')
                ->selectRaw('task_id, sum(minutes) as total_minutes')
                ->groupBy('task_id')
                ->orderByDesc('total_minutes')
                ->limit(15)
                ->get()
                ->map(function ($row) {
                    $row->task = Task::query()->find($row->task_id);

                    return $row;
                })
            : collect();

        return view('company.gap.reports', compact(
            'tasks', 'projects', 'projectId', 'timeSummary', 'timeByPerson', 'timeByProject', 'timeByTask'
        ));
    }

    public function integrations(string $slug)
    {
        return redirect()->route('company.dashboard', $slug);
    }

    public function storeToken(Request $request, string $slug)
    {
        return redirect()->route('company.dashboard', $slug);
    }

    public function storeWebhook(Request $request, string $slug)
    {
        return redirect()->route('company.dashboard', $slug);
    }

    public function security(string $slug)
    {
        return redirect()->route('company.dashboard', $slug);
    }

    public function updateSecurity(Request $request, string $slug)
    {
        return redirect()->route('company.dashboard', $slug);
    }
}
