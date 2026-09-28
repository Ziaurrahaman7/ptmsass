<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\AutomationRule;
use App\Models\Project;
use App\Models\ProjectForm;
use App\Models\SecurityAuditLog;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Webhook;
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

    public function timesheets(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('time.review'), 403);
        $entries = TimeEntry::query()->where('company_id', $this->companyId())->with('user', 'task', 'project')->latest('worked_on')->paginate(40);

        return view('company.gap.timesheets', compact('entries'));
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
        $tasks = Task::query()
            ->where('company_id', $this->companyId())
            ->when($projectId, function ($q) use ($projectId) {
                $q->where(function ($inner) use ($projectId) {
                    $inner->where('project_id', $projectId)
                        ->orWhereHas('projects', fn ($p) => $p->where('projects.id', $projectId));
                });
            })
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $projects = Project::query()->where('company_id', $this->companyId())->orderBy('name')->get();

        return view('company.gap.reports', compact('tasks', 'projects', 'projectId'));
    }

    public function integrations(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('integration.manage'), 403);
        $tokens = ApiToken::query()->where('company_id', $this->companyId())->latest()->get();
        $webhooks = Webhook::query()->where('company_id', $this->companyId())->latest()->get();

        return view('company.gap.integrations', compact('tokens', 'webhooks'));
    }

    public function storeToken(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('integration.manage'), 403);
        $data = $request->validate(['name' => 'required|string|max:80']);
        $plain = Str::random(48);
        ApiToken::create([
            'user_id' => auth()->id(),
            'company_id' => $this->companyId(),
            'name' => $data['name'],
            'token' => hash('sha256', $plain),
        ]);

        return back()->with('success', 'Token created. Copy it now: '.$plain);
    }

    public function storeWebhook(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('integration.manage'), 403);
        $data = $request->validate([
            'url' => 'required|url',
            'event' => 'required|string|max:80',
        ]);
        Webhook::create([
            'company_id' => $this->companyId(),
            'url' => $data['url'],
            'event' => $data['event'],
            'secret' => Str::random(24),
            'is_active' => true,
        ]);

        return back()->with('success', 'Webhook saved.');
    }

    public function security(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);
        $company = auth()->user()->company;
        $logs = SecurityAuditLog::query()->where('company_id', $this->companyId())->latest()->limit(50)->get();

        return view('company.gap.security', compact('company', 'logs'));
    }

    public function updateSecurity(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);
        $data = $request->validate([
            'mfa_required' => 'nullable|boolean',
            'trusted_domains' => 'nullable|string',
        ]);
        $domains = array_values(array_filter(array_map('trim', explode(',', $data['trusted_domains'] ?? ''))));
        auth()->user()->company->update([
            'mfa_required' => (bool) ($data['mfa_required'] ?? false),
            'trusted_domains' => $domains,
        ]);
        SecurityAuditLog::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'action' => 'security.updated',
            'ip' => $request->ip(),
            'meta' => $data,
        ]);

        return back()->with('success', 'Security settings saved.');
    }
}
