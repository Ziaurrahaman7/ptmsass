<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function index(string $slug)
    {
        $projects = Project::where('company_id', $this->companyId())
            ->where('is_template', false)
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn($q) => $q->where('status', 'done')])
            ->latest()->paginate(10);

        return view('company.projects.index', compact('projects'));
    }

    public function create(string $slug)
    {
        $templates = Project::where('company_id', $this->companyId())->where('is_template', true)->orderBy('name')->get();
        return view('company.projects.create', compact('templates'));
    }

    public function store(Request $request, string $slug)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,in_progress,on_hold,completed',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date|after_or_equal:start_date',
            'template_id' => 'nullable|exists:projects,id',
        ]);

        $template = null;
        if (!empty($data['template_id'])) {
            $template = Project::where('company_id', $this->companyId())->where('is_template', true)->find($data['template_id']);
        }
        unset($data['template_id']);

        $project = Project::create([...$data,
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
        ]);
        $project->members()->attach(auth()->id(), ['role' => 'owner']);

        if ($template) $this->cloneProjectContents($template, $project);

        return redirect()->route('company.projects.index', $slug)
            ->with('success', 'Project created successfully.');
    }

    public function show(string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $tasks = $project->tasks()
            ->whereNull('parent_task_id')
            ->with(['assignee', 'assignees', 'section', 'subtasks' => fn($q) => $q->with('assignees')->orderBy('position')->orderByDesc('created_at')])
            ->withCount(['comments', 'subtasks', 'attachments'])
            ->orderBy('position')->orderByDesc('created_at')->get();
        $sections = $project->sections()->get();
        $customFields = $project->customFields()->get();
        $members = auth()->user()->company->users()->where('is_active', true)->get();
        $statusUpdates = $project->statusUpdates()->with('user')->take(10)->get();
        $portfolios = \App\Models\Portfolio::where('company_id', $this->companyId())->orderBy('title')->get();

        // Backfill: projects created before the project-members feature existed have no owner row yet
        if ($project->members()->count() === 0) {
            $project->members()->attach($project->created_by, ['role' => 'owner']);
        }

        $projectMembers = $project->members()->orderByDesc('project_user.created_at')->get();
        $availableMembers = $members->whereNotIn('id', $projectMembers->pluck('id'))->values();
        $resources = $project->resources;
        $milestones = $project->milestones()->with('assignee')->get();

        $activity = collect()
            ->concat($projectMembers->map(fn ($u) => [
                'type' => 'joined', 'at' => $u->pivot->created_at, 'user' => $u,
            ]))
            ->concat($project->messages()->with('user')->latest()->take(20)->get()->map(fn ($m) => [
                'type' => 'message', 'at' => $m->created_at, 'user' => $m->user, 'body' => $m->body,
            ]))
            ->sortByDesc('at')->values();

        return view('company.projects.show', compact(
            'project', 'tasks', 'sections', 'customFields', 'members', 'statusUpdates', 'portfolios',
            'projectMembers', 'availableMembers', 'resources', 'milestones', 'activity'
        ));
    }

    public function edit(string $slug, Project $project)
    {
        $this->authorizeProject($project);
        return view('company.projects.edit', compact('project'));
    }

    public function update(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,in_progress,on_hold,completed',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($data);

        return redirect()->route('company.projects.index', $slug)
            ->with('success', 'Project updated successfully.');
    }

    public function updateGoal(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate([
            'month' => 'required|integer|min:1|max:6',
            'goal'  => 'nullable|string|max:1000',
        ]);

        $goals = $project->month_goals ?? [];
        $goals[$data['month']] = $data['goal'];
        $project->update(['month_goals' => $goals]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $slug, Project $project)
    {
        $this->authorizeProject($project);
        $project->delete();

        return redirect()->route('company.projects.index', $slug)
            ->with('success', 'Project deleted.');
    }

    // Set color & icon (title dropdown)
    public function updateColorIcon(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = [];
        if ($request->has('color')) $data['color'] = $request->color ?: null;
        if ($request->has('icon'))  $data['icon']  = $request->icon ?: null;
        $project->update($data);

        return response()->json(['success' => true]);
    }

    // Star / favorite toggle
    public function toggleFavorite(string $slug, Project $project)
    {
        $this->authorizeProject($project);
        $project->update(['is_favorite' => !$project->is_favorite]);
        return response()->json(['success' => true, 'is_favorite' => $project->is_favorite]);
    }

    // Duplicate: clone project + sections + tasks (+ subtasks, assignees) + custom fields
    public function duplicateProject(string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $copy = $project->replicate();
        $copy->name = $project->name . ' (copy)';
        $copy->is_favorite = false;
        $copy->is_template = false;
        $copy->created_by = auth()->id();
        $copy->save();
        $copy->members()->attach(auth()->id(), ['role' => 'owner']);

        $this->cloneProjectContents($project, $copy);

        return response()->json(['success' => true, 'url' => route('company.projects.show', [$slug, $copy->id])]);
    }

    // Save as template: same cloning, but the copy is flagged is_template so it shows up in the "start from template" picker
    public function saveAsTemplate(string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $copy = $project->replicate();
        $copy->name = $project->name . ' (template)';
        $copy->is_favorite = false;
        $copy->is_template = true;
        $copy->created_by = auth()->id();
        $copy->save();
        $copy->members()->attach(auth()->id(), ['role' => 'owner']);

        $this->cloneProjectContents($project, $copy);

        return response()->json(['success' => true]);
    }

    private function cloneProjectContents(Project $source, Project $target): void
    {
        $sectionMap = [];
        foreach ($source->sections()->get() as $section) {
            $sCopy = $section->replicate();
            $sCopy->project_id = $target->id;
            $sCopy->save();
            $sectionMap[$section->id] = $sCopy->id;
        }

        $taskMap = [];
        $topTasks = $source->tasks()->whereNull('parent_task_id')->orderBy('position')->get();
        foreach ($topTasks as $task) {
            $tCopy = $task->replicate();
            $tCopy->project_id = $target->id;
            $tCopy->section_id = $task->section_id ? ($sectionMap[$task->section_id] ?? null) : null;
            $tCopy->created_by = auth()->id();
            $tCopy->save();
            $taskMap[$task->id] = $tCopy->id;
            $tCopy->assignees()->sync($task->assignees->pluck('id'));
        }

        $subtasks = $source->tasks()->whereNotNull('parent_task_id')->orderBy('position')->get();
        foreach ($subtasks as $sub) {
            $subCopy = $sub->replicate();
            $subCopy->project_id = $target->id;
            $subCopy->section_id = $sub->section_id ? ($sectionMap[$sub->section_id] ?? null) : null;
            $subCopy->parent_task_id = $taskMap[$sub->parent_task_id] ?? null;
            $subCopy->created_by = auth()->id();
            $subCopy->save();
            $subCopy->assignees()->sync($sub->assignees->pluck('id'));
        }

        foreach ($source->customFields as $cf) {
            $cfCopy = $cf->replicate();
            $cfCopy->project_id = $target->id;
            $cfCopy->save();
        }
    }

    // Export this project's tasks as CSV
    public function exportTasksCsv(string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $tasks = $project->tasks()->with(['assignee', 'section'])->orderBy('position')->get();

        $filename = \Illuminate\Support\Str::slug($project->name) . '-tasks.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($tasks) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Title', 'Section', 'Status', 'Priority', 'Due date', 'Assignee', 'Description']);
            foreach ($tasks as $t) {
                fputcsv($out, [
                    $t->title,
                    $t->section?->name,
                    $t->status,
                    $t->priority,
                    $t->due_date?->format('Y-m-d'),
                    $t->assignee?->name,
                    $t->description,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Import tasks from a CSV (Title, Section, Status, Priority, Due date, Assignee, Description)
    public function importTasksCsv(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $header = array_map(fn ($h) => strtolower(trim($h)), array_shift($rows));

        $validStatuses = ['todo', 'in_progress', 'in_review', 'done'];
        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        $sectionCache = [];
        $memberByName = auth()->user()->company->users()->where('is_active', true)->get()->keyBy(fn ($u) => strtolower($u->name));
        $position = (int) $project->tasks()->max('position');
        $imported = 0;

        foreach ($rows as $row) {
            if (count($row) < 1 || trim($row[0] ?? '') === '') continue;
            $r = array_combine($header, array_pad($row, count($header), null)) ?: [];

            $sectionId = null;
            $sectionName = trim($r['section'] ?? '');
            if ($sectionName !== '') {
                if (!isset($sectionCache[$sectionName])) {
                    $sectionCache[$sectionName] = $project->sections()->firstOrCreate(
                        ['name' => $sectionName],
                        ['company_id' => $this->companyId(), 'position' => $project->sections()->count()]
                    )->id;
                }
                $sectionId = $sectionCache[$sectionName];
            }

            $status = strtolower(trim($r['status'] ?? ''));
            $status = in_array($status, $validStatuses) ? $status : 'todo';
            $priority = strtolower(trim($r['priority'] ?? ''));
            $priority = in_array($priority, $validPriorities) ? $priority : 'medium';
            $assigneeName = strtolower(trim($r['assignee'] ?? ''));
            $assignedTo = $memberByName->get($assigneeName)?->id;

            Task::create([
                'company_id'  => $this->companyId(),
                'project_id'  => $project->id,
                'section_id'  => $sectionId,
                'created_by'  => auth()->id(),
                'assigned_to' => $assignedTo,
                'title'       => trim($r['title'] ?? 'Untitled task'),
                'description' => $r['description'] ?? null,
                'status'      => $status,
                'priority'    => $priority,
                'due_date'    => !empty($r['due date']) ? $r['due date'] : (!empty($r['due_date']) ? $r['due_date'] : null),
                'position'    => ++$position,
            ]);
            $imported++;
        }

        return response()->json(['success' => true, 'imported' => $imported]);
    }

    // Post a status update ("On track" / "At risk" / ...) for the Set-status feed
    public function storeStatusUpdate(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate([
            'status'  => 'required|in:on_track,at_risk,off_track,on_hold',
            'message' => 'nullable|string|max:2000',
        ]);

        $project->statusUpdates()->create([
            'company_id' => $this->companyId(),
            'user_id'    => auth()->id(),
            'status'     => $data['status'],
            'message'    => $data['message'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    // Project roles/members
    public function addMember(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate(['user_id' => 'required|exists:users,id']);
        $user = auth()->user()->company->users()->where('is_active', true)->findOrFail($data['user_id']);

        $project->members()->syncWithoutDetaching([$user->id => ['role' => 'member']]);

        return response()->json(['success' => true]);
    }

    public function removeMember(string $slug, Project $project, User $user)
    {
        $this->authorizeProject($project);
        $project->members()->detach($user->id);
        return response()->json(['success' => true]);
    }

    // Key resources (project brief / links)
    public function storeResource(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate([
            'type'    => 'required|in:brief,link',
            'title'   => 'required|string|max:255',
            'url'     => 'nullable|url|max:2000',
            'content' => 'nullable|string|max:5000',
        ]);

        $project->resources()->create([...$data,
            'company_id' => $this->companyId(),
            'user_id'    => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroyResource(string $slug, Project $project, ProjectResource $resource)
    {
        $this->authorizeProject($project);
        abort_if($resource->project_id !== $project->id, 403);
        $resource->delete();
        return response()->json(['success' => true]);
    }

    // Send a message to project members (feeds the activity log)
    public function storeMessage(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate(['body' => 'required|string|max:4000']);

        $project->messages()->create([
            'company_id' => $this->companyId(),
            'user_id'    => auth()->id(),
            'body'       => $data['body'],
        ]);

        return response()->json(['success' => true]);
    }

    // Milestones (tasks flagged is_milestone)
    public function storeMilestone(Request $request, string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $position = (int) $project->tasks()->max('position');

        $task = Task::create([
            'company_id'   => $this->companyId(),
            'project_id'   => $project->id,
            'created_by'   => auth()->id(),
            'title'        => $data['title'],
            'due_date'     => $data['due_date'] ?? null,
            'is_milestone' => true,
            'position'     => $position + 1,
        ]);

        return response()->json(['success' => true, 'id' => $task->id]);
    }

    public function toggleMilestone(string $slug, Project $project, Task $task)
    {
        $this->authorizeProject($project);
        abort_if($task->project_id !== $project->id || !$task->is_milestone, 403);

        $task->update(['status' => $task->status === 'done' ? 'todo' : 'done']);

        return response()->json(['success' => true, 'status' => $task->status]);
    }

    private function authorizeProject(Project $project): void
    {
        abort_if($project->company_id !== $this->companyId(), 403);
    }
}
