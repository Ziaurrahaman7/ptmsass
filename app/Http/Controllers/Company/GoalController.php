<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    private function authorizeGoal(Goal $goal): void
    {
        abort_if($goal->company_id !== $this->companyId(), 403);
    }

    public function index(string $slug)
    {
        $companyId = $this->companyId();

        $goals = Goal::where('company_id', $companyId)
            ->with(['owner', 'team', 'projects', 'parent'])
            ->orderBy('due_date')
            ->get();

        $companyGoals = $goals->where('scope', 'company')->values();
        $teamGoals    = $goals->where('scope', 'team')->groupBy(fn ($g) => $g->team->name ?? 'No team');
        $myGoals      = $goals->where('owner_id', auth()->id())->values();

        // Strategy map: root goals (no parent) with children eager-attached (2 levels deep is plenty for a company->team hierarchy)
        $byParent = $goals->groupBy('parent_goal_id');
        $attachChildren = function ($goal) use (&$attachChildren, $byParent) {
            $goal->childGoals = ($byParent[$goal->id] ?? collect())->map($attachChildren);
            return $goal;
        };
        $rootGoals = $goals->whereNull('parent_goal_id')->map($attachChildren)->values();

        $teams = Team::where('company_id', $companyId)->orderBy('name')->get();
        $members = User::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)->orderBy('name')->get();
        $parentOptions = $companyGoals;

        $goalsForJs = $goals->map(fn ($g) => [
            'id'              => $g->id,
            'title'           => $g->title,
            'description'     => $g->description,
            'scope'           => $g->scope,
            'team_id'         => $g->team_id,
            'owner_id'        => $g->owner_id,
            'parent_goal_id'  => $g->parent_goal_id,
            'status'          => $g->status,
            'progress_mode'   => $g->progress_mode,
            'manual_progress' => $g->manual_progress,
            'start_date'      => $g->start_date?->format('Y-m-d'),
            'due_date'        => $g->due_date?->format('Y-m-d'),
            'project_ids'     => $g->projects->pluck('id'),
        ])->values();

        return view('company.goals.index', compact(
            'slug', 'companyGoals', 'teamGoals', 'myGoals', 'rootGoals',
            'teams', 'members', 'projects', 'parentOptions', 'goalsForJs'
        ));
    }

    public function store(Request $request, string $slug)
    {
        $goal = Goal::create($this->extractData($request) + ['company_id' => $this->companyId()]);
        $this->syncProjects($goal, $request);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $slug, Goal $goal)
    {
        $this->authorizeGoal($goal);

        // A goal can't become its own ancestor
        $parentId = $request->parent_goal_id ?: null;
        if ($parentId == $goal->id) $parentId = null;

        $goal->update($this->extractData($request, $parentId));
        $this->syncProjects($goal, $request);

        return response()->json(['success' => true]);
    }

    public function destroy(string $slug, Goal $goal)
    {
        $this->authorizeGoal($goal);
        $goal->delete();
        return response()->json(['success' => true]);
    }

    private function extractData(Request $request, $parentIdOverride = false): array
    {
        $scope = $request->scope === 'team' ? 'team' : 'company';

        return [
            'title'           => trim($request->title ?? '') ?: 'New goal',
            'description'     => $request->description,
            'scope'           => $scope,
            'team_id'         => $scope === 'team' ? ($request->team_id ?: null) : null,
            'owner_id'        => $request->owner_id ?: auth()->id(),
            'parent_goal_id'  => $parentIdOverride !== false ? $parentIdOverride : ($request->parent_goal_id ?: null),
            'status'          => in_array($request->status, ['on_track','at_risk','off_track','done']) ? $request->status : 'on_track',
            'progress_mode'   => $request->progress_mode === 'projects' ? 'projects' : 'manual',
            'manual_progress' => $request->manual_progress !== null ? max(0, min(100, (int) $request->manual_progress)) : 0,
            'start_date'      => $request->start_date ?: null,
            'due_date'        => $request->due_date ?: null,
        ];
    }

    private function syncProjects(Goal $goal, Request $request): void
    {
        $ids = collect($request->project_ids ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        // Keep them scoped to this company
        $validIds = Project::where('company_id', $this->companyId())->whereIn('id', $ids)->pluck('id');
        $goal->projects()->sync($validIds);
    }
}
