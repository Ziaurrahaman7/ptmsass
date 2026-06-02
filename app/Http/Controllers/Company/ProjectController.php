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

    public function index()
    {
        $projects = Project::where('company_id', $this->companyId())
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn($q) => $q->where('status', 'done')])
            ->latest()->paginate(10);

        return view('company.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('company.projects.create');
    }

    public function store(Request $request)
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

        return redirect()->route('company.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->authorizeProject($project);

        $tasks = $project->tasks()->with('assignee')->latest()->get();
        $members = auth()->user()->company->users()->where('is_active', true)->get();

        return view('company.projects.show', compact('project', 'tasks', 'members'));
    }

    public function edit(Project $project)
    {
        $this->authorizeProject($project);
        return view('company.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
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

        return redirect()->route('company.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorizeProject($project);
        $project->delete();

        return redirect()->route('company.projects.index')
            ->with('success', 'Project deleted.');
    }

    private function authorizeProject(Project $project): void
    {
        abort_if($project->company_id !== $this->companyId(), 403);
    }
}
