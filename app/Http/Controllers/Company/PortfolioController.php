<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    private function authorizePortfolio(Portfolio $portfolio): void
    {
        abort_if($portfolio->company_id !== $this->companyId(), 403);
    }

    // Portfolio list (grid of cards)
    public function index(string $slug)
    {
        $companyId = $this->companyId();

        $portfolios = Portfolio::where('company_id', $companyId)
            ->withCount('projects')
            ->with('owner')
            ->latest()->get();

        return view('company.portfolios.index', compact('slug', 'portfolios'));
    }

    // Create a portfolio
    public function store(Request $request, string $slug)
    {
        $request->validate(['title' => 'nullable|string|max:255']);

        $portfolio = Portfolio::create([
            'company_id'  => $this->companyId(),
            'user_id'     => auth()->id(),
            'title'       => trim($request->title ?? '') ?: 'New Portfolio',
            'description' => $request->description,
        ]);

        return response()->json([
            'url' => route('company.portfolios.show', [$slug, $portfolio->id]),
        ]);
    }

    public function update(Request $request, string $slug, Portfolio $portfolio)
    {
        $this->authorizePortfolio($portfolio);

        $portfolio->update([
            'title'       => trim($request->title ?? '') ?: $portfolio->title,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $slug, Portfolio $portfolio)
    {
        $this->authorizePortfolio($portfolio);
        $portfolio->delete();
        return redirect()->route('company.portfolios.index', $slug);
    }

    // Attach a project to the portfolio
    public function addProject(Request $request, string $slug, Portfolio $portfolio)
    {
        $this->authorizePortfolio($portfolio);

        $project = Project::where('company_id', $this->companyId())->findOrFail($request->project_id);
        $portfolio->projects()->syncWithoutDetaching([$project->id]);

        return response()->json(['success' => true]);
    }

    // Detach a project from the portfolio
    public function removeProject(string $slug, Portfolio $portfolio, Project $project)
    {
        $this->authorizePortfolio($portfolio);
        $portfolio->projects()->detach($project->id);
        return response()->json(['success' => true]);
    }

    // Main tabbed portfolio page: List / Timeline / Dashboard / Progress / Workload
    public function show(string $slug, Portfolio $portfolio)
    {
        $this->authorizePortfolio($portfolio);

        $companyId = $this->companyId();

        $projects = $portfolio->projects()
            ->with('creator')
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn($q) => $q->where('status', 'done')])
            ->orderBy('name')->get()
            ->map(function ($p) {
                $p->progress = $p->tasks_count > 0 ? (int) round(($p->done_tasks_count / $p->tasks_count) * 100) : 0;
                return $p;
            });

        $projectIds = $projects->pluck('id');

        // ---- Dashboard tab aggregates ----
        $statusCounts = [
            'planning'    => $projects->where('status', 'planning')->count(),
            'in_progress' => $projects->where('status', 'in_progress')->count(),
            'on_hold'     => $projects->where('status', 'on_hold')->count(),
            'completed'   => $projects->where('status', 'completed')->count(),
        ];

        $totalTasks = Task::whereIn('project_id', $projectIds)->count();
        $doneTasks  = Task::whereIn('project_id', $projectIds)->where('status', 'done')->count();
        $completionRate = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;
        $overdueCount = Task::whereIn('project_id', $projectIds)
            ->where('status', '!=', 'done')->whereNotNull('due_date')->whereDate('due_date', '<', today())->count();

        // ---- Timeline tab ----
        $timelineProjects = $projects->filter(fn($p) => $p->start_date && $p->due_date)->values();
        $rangeStart = $timelineProjects->min('start_date');
        $rangeEnd   = $timelineProjects->max('due_date');

        // ---- Workload tab: open task count per employee, scoped to this portfolio's projects ----
        $workload = User::where('company_id', $companyId)->where('role', 'employee')->where('is_active', true)
            ->withCount([
                'assignedTasks as open_tasks'  => fn($q) => $q->whereIn('project_id', $projectIds)->where('status', '!=', 'done'),
                'assignedTasks as total_tasks' => fn($q) => $q->whereIn('project_id', $projectIds),
            ])
            ->orderByDesc('open_tasks')->get()
            ->filter(fn($u) => $u->total_tasks > 0)->values();

        $availableProjects = Project::where('company_id', $companyId)
            ->whereNotIn('id', $projectIds->isNotEmpty() ? $projectIds : [0])
            ->orderBy('name')->get();

        return view('company.portfolios.show', compact(
            'slug', 'portfolio', 'projects', 'statusCounts', 'totalTasks', 'doneTasks',
            'completionRate', 'overdueCount', 'timelineProjects', 'rangeStart', 'rangeEnd',
            'workload', 'availableProjects'
        ));
    }
}
