<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
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
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn($q) => $q->where('status', 'done')])
            ->latest()->paginate(10);

        return view('company.projects.index', compact('projects'));
    }

    public function create(string $slug)
    {
        return view('company.projects.create');
    }

    public function store(Request $request, string $slug)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,in_progress,on_hold,completed',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        Project::create([...$data,
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('company.projects.index', $slug)
            ->with('success', 'Project created successfully.');
    }

    public function show(string $slug, Project $project)
    {
        $this->authorizeProject($project);

        $tasks = $project->tasks()->with(['assignee', 'assignees', 'section'])->withCount(['comments', 'subtasks'])->latest()->get();
        $sections = $project->sections()->get();
        $members = auth()->user()->company->users()->where('is_active', true)->get();

        return view('company.projects.show', compact('project', 'tasks', 'sections', 'members'));
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

    private function authorizeProject(Project $project): void
    {
        abort_if($project->company_id !== $this->companyId(), 403);
    }
}
