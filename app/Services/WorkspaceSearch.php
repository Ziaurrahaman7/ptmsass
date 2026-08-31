<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Portfolio;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WorkspaceSearch
{
    public function __construct(
        private readonly string $slug,
        private readonly int $companyId,
        private readonly int $userId,
        private readonly bool $isAdmin,
    ) {}

    public function search(string $q, ?string $type, ?string $preset): array
    {
        $q = trim($q);
        $type = $type ?: 'all';
        $limit = $type === 'all' ? 6 : 20;

        if ($preset) {
            return $this->preset($preset);
        }

        if (strlen($q) < 2 && $type === 'all') {
            return [
                'mode'    => 'idle',
                'results' => $this->emptyResults(),
                'recent'  => $this->serverRecents(),
                'total'   => 0,
            ];
        }

        $like = strlen($q) >= 2 ? '%' . $q . '%' : '%';
        $results = $this->emptyResults();

        $want = fn (string $key) => $type === 'all' || $type === $key || ($type === 'more' && in_array($key, ['comments', 'teams'], true));

        if ($want('tasks')) {
            $results['tasks'] = $this->searchTasks($like, $limit);
        }
        if ($want('projects')) {
            $results['projects'] = $this->searchProjects($like, $limit);
        }
        if ($want('people')) {
            $results['people'] = $this->searchPeople($like, $limit);
        }
        if ($this->isAdmin && $want('portfolios')) {
            $results['portfolios'] = $this->searchPortfolios($like, $limit);
        }
        if ($this->isAdmin && $want('goals')) {
            $results['goals'] = $this->searchGoals($like, $limit);
        }
        if ($want('comments') || $type === 'all' || $type === 'more') {
            $results['comments'] = $this->searchComments($like, $type === 'all' ? 4 : $limit);
        }
        if ($this->isAdmin && ($want('teams') || $type === 'all' || $type === 'more')) {
            $results['teams'] = $this->searchTeams($like, $type === 'all' ? 3 : $limit);
        }

        $total = collect($results)->sum(fn ($rows) => is_countable($rows) ? count($rows) : 0);

        return [
            'mode'    => 'results',
            'results' => $results,
            'recent'  => [],
            'total'   => $total,
        ];
    }

    private function emptyResults(): array
    {
        return [
            'tasks'      => [],
            'projects'   => [],
            'people'     => [],
            'portfolios' => [],
            'goals'      => [],
            'comments'   => [],
            'teams'      => [],
        ];
    }

    private function visibleTaskQuery(): Builder
    {
        $q = Task::query()->where('company_id', $this->companyId);

        if ($this->isAdmin) {
            return $q;
        }

        $userId = $this->userId;

        return $q->where(function (Builder $q) use ($userId) {
            $q->where('assigned_to', $userId)
              ->orWhereHas('assignees', fn (Builder $q) => $q->where('users.id', $userId))
              ->orWhere(fn (Builder $q) => $q->whereNull('project_id')->where('created_by', $userId));
        });
    }

    private function visibleProjectQuery(): Builder
    {
        $q = Project::query()
            ->where('company_id', $this->companyId)
            ->where('is_template', false);

        if ($this->isAdmin) {
            return $q;
        }

        $ids = $this->visibleTaskQuery()->whereNotNull('project_id')->pluck('project_id');
        $userId = $this->userId;

        return $q->where(function (Builder $q) use ($ids, $userId) {
            $q->whereIn('id', $ids)
              ->orWhereHas('members', fn (Builder $q) => $q->where('users.id', $userId));
        });
    }

    private function searchTasks(string $like, int $limit): Collection
    {
        return $this->visibleTaskQuery()
            ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->with(['project', 'assignees', 'assignee'])
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Task $t) => $this->mapTask($t));
    }

    private function searchProjects(string $like, int $limit): Collection
    {
        return $this->visibleProjectQuery()
            ->where(fn (Builder $q) => $q->where('name', 'like', $like)->orWhere('description', 'like', $like))
            ->with('members')
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Project $p) => $this->mapProject($p));
    }

    private function searchPeople(string $like, int $limit): Collection
    {
        return User::where('company_id', $this->companyId)
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->where('name', 'like', $like)->orWhere('email', 'like', $like))
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (User $u) => $this->mapPerson($u));
    }

    private function searchPortfolios(string $like, int $limit): Collection
    {
        return Portfolio::where('company_id', $this->companyId)
            ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->limit($limit)
            ->get()
            ->map(fn (Portfolio $p) => [
                'type'     => 'portfolio',
                'id'       => $p->id,
                'title'    => $p->title,
                'subtitle' => 'Portfolio',
                'url'      => route('company.portfolios.show', [$this->slug, $p]),
                'avatars'  => [],
            ]);
    }

    private function searchGoals(string $like, int $limit): Collection
    {
        return Goal::where('company_id', $this->companyId)
            ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->limit($limit)
            ->get()
            ->map(fn (Goal $g) => [
                'type'     => 'goal',
                'id'       => $g->id,
                'title'    => $g->title,
                'subtitle' => $g->status ?? 'Goal',
                'url'      => route('company.goals.index', $this->slug),
                'avatars'  => [],
            ]);
    }

    private function searchComments(string $like, int $limit): Collection
    {
        return TaskComment::query()
            ->where('comment', 'like', $like)
            ->whereHas('task', function (Builder $q) {
                $q->where('company_id', $this->companyId);
                if (! $this->isAdmin) {
                    $userId = $this->userId;
                    $q->where(function (Builder $q) use ($userId) {
                        $q->where('assigned_to', $userId)
                          ->orWhereHas('assignees', fn (Builder $q) => $q->where('users.id', $userId))
                          ->orWhere(fn (Builder $q) => $q->whereNull('project_id')->where('created_by', $userId));
                    });
                }
            })
            ->with(['task.project', 'user'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (TaskComment $c) => [
                'type'     => 'comment',
                'id'       => $c->id,
                'title'    => \Illuminate\Support\Str::limit($c->comment, 80),
                'subtitle' => 'Comment on ' . ($c->task?->title ?? 'task'),
                'url'      => $this->taskUrl($c->task),
                'initial'  => strtoupper(substr($c->user?->name ?? '?', 0, 1)),
                'avatars'  => [],
            ]);
    }

    private function searchTeams(string $like, int $limit): Collection
    {
        return Team::where('company_id', $this->companyId)
            ->where(fn (Builder $q) => $q->where('name', 'like', $like)->orWhere('description', 'like', $like))
            ->limit($limit)
            ->get()
            ->map(fn (Team $t) => [
                'type'     => 'team',
                'id'       => $t->id,
                'title'    => $t->name,
                'subtitle' => 'Team',
                'url'      => route('company.team.overview', [$this->slug, $t]),
                'avatars'  => [],
            ]);
    }

    private function preset(string $preset): array
    {
        $results = $this->emptyResults();

        $query = $this->visibleTaskQuery()->with(['project', 'assignees', 'assignee']);

        $results['tasks'] = match ($preset) {
            'created' => $query->where('created_by', $this->userId)->latest()->limit(20)->get()->map(fn (Task $t) => $this->mapTask($t)),
            'assigned' => $query->where('created_by', $this->userId)
                ->where(fn (Builder $q) => $q
                    ->where(fn (Builder $q) => $q->whereNotNull('assigned_to')->where('assigned_to', '!=', $this->userId))
                    ->orWhereHas('assignees', fn (Builder $q) => $q->where('users.id', '!=', $this->userId)))
                ->latest()->limit(20)->get()->map(fn (Task $t) => $this->mapTask($t)),
            'completed' => $query->where('status', 'done')->latest('updated_at')->limit(20)->get()->map(fn (Task $t) => $this->mapTask($t)),
            'deleted' => collect(),
            default => collect(),
        };

        return [
            'mode'    => 'preset',
            'preset'  => $preset,
            'results' => $results,
            'recent'  => [],
            'total'   => count($results['tasks']),
            'message' => $preset === 'deleted'
                ? 'Trash is not enabled. Deleted items are permanently removed.'
                : null,
        ];
    }

    private function serverRecents(): array
    {
        $tasks = $this->visibleTaskQuery()
            ->with(['project', 'assignees', 'assignee'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Task $t) => $this->mapTask($t));

        $projects = $this->visibleProjectQuery()
            ->with('members')
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->map(fn (Project $p) => $this->mapProject($p));

        $people = User::where('company_id', $this->companyId)
            ->where('is_active', true)
            ->where('id', '!=', $this->userId)
            ->orderBy('name')
            ->limit(2)
            ->get()
            ->map(fn (User $u) => $this->mapPerson($u));

        return $tasks->concat($projects)->concat($people)->values()->all();
    }

    private function mapTask(Task $t): array
    {
        $people = $t->assignees->isNotEmpty()
            ? $t->assignees
            : collect([$t->assignee])->filter();

        $subtitle = $t->project?->name ?? 'Personal';
        if ($t->parent_task_id) {
            $subtitle = 'Subtask · ' . $subtitle;
        }

        return [
            'type'       => 'task',
            'id'         => $t->id,
            'title'      => $t->title,
            'subtitle'   => $subtitle,
            'status'     => $t->status,
            'archived'   => ($t->project?->status ?? '') === 'archived',
            'is_subtask' => (bool) $t->parent_task_id,
            'color'      => $t->project?->color ?: '#f472b6',
            'url'        => $this->taskUrl($t),
            'avatars'    => $people->take(3)->map(fn (User $u) => [
                'initial' => strtoupper(substr($u->name, 0, 1)),
                'name'    => $u->name,
            ])->values()->all(),
        ];
    }

    private function mapProject(Project $p): array
    {
        return [
            'type'     => 'project',
            'id'       => $p->id,
            'title'    => $p->name,
            'subtitle' => ($p->status ?? '') === 'archived' ? 'Archived' : ($p->status ?? 'active'),
            'archived' => ($p->status ?? '') === 'archived',
            'color'    => $p->color ?: '#f472b6',
            'url'      => $this->isAdmin
                ? route('company.projects.show', [$this->slug, $p])
                : route('employee.projects.show', [$this->slug, $p]),
            'avatars'  => $p->relationLoaded('members')
                ? $p->members->take(3)->map(fn (User $u) => [
                    'initial' => strtoupper(substr($u->name, 0, 1)),
                    'name'    => $u->name,
                ])->values()->all()
                : [],
        ];
    }

    private function mapPerson(User $u): array
    {
        return [
            'type'     => 'person',
            'id'       => $u->id,
            'title'    => $u->name,
            'subtitle' => $u->email,
            'initial'  => strtoupper(substr($u->name, 0, 1)),
            'url'      => $this->isAdmin
                ? route('company.members.index', $this->slug)
                : route('employee.my-tasks.index', $this->slug),
            'avatars'  => [],
        ];
    }

    private function taskUrl(?Task $t): string
    {
        if (! $t) {
            return $this->isAdmin
                ? route('company.my-tasks.index', $this->slug)
                : route('employee.my-tasks.index', $this->slug);
        }

        $hash = '#task-' . $t->id;

        if ($t->project_id) {
            $base = $this->isAdmin
                ? route('company.projects.show', [$this->slug, $t->project_id])
                : route('employee.projects.show', [$this->slug, $t->project_id]);

            return $base . $hash;
        }

        $base = $this->isAdmin
            ? route('company.my-tasks.index', $this->slug)
            : route('employee.my-tasks.index', $this->slug);

        return $base . $hash;
    }
}
