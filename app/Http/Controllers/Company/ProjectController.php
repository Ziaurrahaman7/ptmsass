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

        $tasks = $project->tasks()->with('assignee')->latest()->get();
        $members = auth()->user()->company->users()->where('is_active', true)->get();

        return view('company.projects.show', compact('project', 'tasks', 'members'));
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

    public function destroy(string $slug, Project $project)
    {
        $this->authorizeProject($project);
        $project->delete();

        return redirect()->route('company.projects.index', $slug)
            ->with('success', 'Project deleted.');
    }

    private function authorizeProject(Project $project): void
    {
        if ($project->company_id !== $this->companyId()) {
            \Log::error('Project Authorization Failed', [
                'project_id' => $project->id,
                'project_company_id' => $project->company_id,
                'user_company_id' => $this->companyId(),
                'user_id' => auth()->id(),
                'project_name' => $project->name,
                'project_company_name' => $project->company?->name,
                'user_company_name' => auth()->user()->company?->name,
            ]);
            abort(403, 'This project belongs to ' . ($project->company?->name ?? 'another company') . ' (ID: ' . $project->company_id . '). Your company ID is: ' . $this->companyId());
        }
    }
}
