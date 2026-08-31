<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Priority;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesMyTasks
{
    abstract protected function companyId(): int;

    protected function myTasksBaseQuery(): Builder
    {
        $userId = auth()->id();

        return Task::query()
            ->where('company_id', $this->companyId())
            ->whereNull('parent_task_id')
            ->where(function (Builder $q) use ($userId) {
                $q->whereHas('assignees', fn (Builder $q) => $q->where('users.id', $userId))
                  ->orWhere('assigned_to', $userId)
                  ->orWhere(fn (Builder $q) => $q->whereNull('project_id')->where('created_by', $userId));
            });
    }

    protected function recentlyAssignedConstraint(Builder $q): Builder
    {
        $userId = auth()->id();
        $since = now()->subDays(7);

        return $q->where(function (Builder $q) use ($userId, $since) {
            $q->where('list_group', 'recent')
              ->orWhere(function (Builder $q) use ($userId, $since) {
                  $q->where(function (Builder $q) {
                      $q->whereNull('list_group')->orWhere('list_group', '!=', 'later');
                  })->where(function (Builder $q) use ($userId, $since) {
                      $q->whereHas('assignees', fn (Builder $q) => $q
                            ->where('users.id', $userId)
                            ->where('task_assignees.created_at', '>=', $since))
                        ->orWhere(fn (Builder $q) => $q->where('created_at', '>=', $since)->where('created_by', $userId));
                  });
              });
        });
    }

    protected function groupedMyTasks(): array
    {
        $with = ['project', 'assignees'];

        $recently = $this->myTasksBaseQuery()
            ->where('status', '!=', 'done')
            ->whereNull('due_date')
            ->where(fn (Builder $q) => $this->recentlyAssignedConstraint($q))
            ->latest()
            ->with($with)
            ->get();

        $recentIds = $recently->pluck('id');

        $later = $this->myTasksBaseQuery()
            ->where('status', '!=', 'done')
            ->whereNull('due_date')
            ->whereNotIn('id', $recentIds)
            ->latest()
            ->with($with)
            ->get();

        $overdue = $this->myTasksBaseQuery()
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '<', today())
            ->orderByDesc('due_date')
            ->with($with)
            ->get();

        $today = $this->myTasksBaseQuery()
            ->where('status', '!=', 'done')
            ->whereDate('due_date', today())
            ->orderBy('priority')
            ->with($with)
            ->get();

        $upcoming = $this->myTasksBaseQuery()
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '>', today())
            ->orderBy('due_date')
            ->with($with)
            ->get();

        $done = $this->myTasksBaseQuery()
            ->where('status', 'done')
            ->latest('updated_at')
            ->limit(20)
            ->with($with)
            ->get();

        return compact('recently', 'overdue', 'today', 'upcoming', 'later', 'done');
    }

    protected function calendarMyTasks(?string $month, string $scale = 'month', ?string $week = null): array
    {
        $scale = $scale === 'week' ? 'week' : 'month';

        if ($scale === 'week') {
            try {
                $weekStart = $week
                    ? Carbon::parse($week)->startOfWeek(Carbon::MONDAY)
                    : now()->startOfWeek(Carbon::MONDAY);
            } catch (\Exception) {
                $weekStart = now()->startOfWeek(Carbon::MONDAY);
            }
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $gridStart = $weekStart->copy();
            $gridEnd = $weekEnd->copy();
            $cursor = $weekStart->copy();
            $prevKey = $weekStart->copy()->subWeek()->toDateString();
            $nextKey = $weekStart->copy()->addWeek()->toDateString();
            $todayKey = now()->startOfWeek(Carbon::MONDAY)->toDateString();
            $calLabel = $weekStart->month === $weekEnd->month
                ? $weekStart->format('M j') . ' – ' . $weekEnd->format('j, Y')
                : $weekStart->format('M j') . ' – ' . $weekEnd->format('M j, Y');
        } else {
            try {
                $cursor = $month
                    ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
                    : now()->startOfMonth();
            } catch (\Exception) {
                $cursor = now()->startOfMonth();
            }
            $gridStart = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $gridEnd = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
            $prevKey = $cursor->copy()->subMonth()->format('Y-m');
            $nextKey = $cursor->copy()->addMonth()->format('Y-m');
            $todayKey = now()->format('Y-m');
            $calLabel = $cursor->format('F Y');
        }

        $tasks = $this->myTasksBaseQuery()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->with(['project', 'assignees'])
            ->orderBy('due_date')
            ->get();

        return [
            'cursor' => $cursor,
            'calScale' => $scale,
            'calLabel' => $calLabel,
            'prevKey' => $prevKey,
            'nextKey' => $nextKey,
            'todayKey' => $todayKey,
            'prevMonth' => $scale === 'month' ? $prevKey : $cursor->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $scale === 'month' ? $nextKey : $cursor->copy()->addMonth()->format('Y-m'),
            'days' => CarbonPeriod::create($gridStart, $gridEnd)->toArray(),
            'tasksByDate' => $tasks->groupBy(fn (Task $t) => $t->due_date->toDateString()),
            'todayStr' => now()->toDateString(),
            'monthNum' => $cursor->month,
        ];
    }

    protected function myTasksIndexData(Request $request): array
    {
        $groups = $this->groupedMyTasks();
        $calendar = $this->calendarMyTasks(
            $request->query('month'),
            $request->query('scale') === 'week' ? 'week' : 'month',
            $request->query('week')
        );
        $allowed = ['list', 'board', 'calendar', 'dashboard', 'files'];
        $view = $request->query('view', 'list');
        if (! in_array($view, $allowed, true)) {
            $view = 'list';
        }
        $priorities = Priority::forCompany($this->companyId());
        $projects = Project::where('company_id', $this->companyId())
            ->where('is_template', false)
            ->orderBy('name')
            ->get(['id', 'name']);

        $boardTasks = $this->myTasksBaseQuery()
            ->with(['project', 'assignees'])
            ->orderBy('position')
            ->orderByDesc('updated_at')
            ->get();

        $taskIds = $this->myTasksBaseQuery()->pluck('id');
        $files = TaskAttachment::whereIn('task_id', $taskIds)
            ->with(['task.project', 'uploader'])
            ->latest()
            ->limit(80)
            ->get();

        $dashboard = [
            'open'     => $this->myTasksBaseQuery()->where('status', '!=', 'done')->count(),
            'overdue'  => $this->myTasksBaseQuery()->where('status', '!=', 'done')->whereDate('due_date', '<', today())->count(),
            'today'    => $this->myTasksBaseQuery()->where('status', '!=', 'done')->whereDate('due_date', today())->count(),
            'done'     => $this->myTasksBaseQuery()->where('status', 'done')->count(),
            'statuses' => $this->myTasksBaseQuery()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'projects' => $this->myTasksBaseQuery()->where('status', '!=', 'done')->whereNotNull('project_id')
                ->selectRaw('project_id, count(*) as c')->groupBy('project_id')->pluck('c', 'project_id'),
        ];

        return array_merge($groups, $calendar, compact(
            'view', 'priorities', 'projects', 'boardTasks', 'files', 'dashboard'
        ));
    }

    protected function storeMyTask(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'due_date'   => 'nullable|date',
            'priority'   => 'required|in:' . Priority::forCompany($this->companyId())->pluck('slug')->implode(','),
            'project_id' => 'nullable|exists:projects,id',
            'notes'      => 'nullable|string',
            'list_group' => 'nullable|in:recent,later',
        ]);

        if (! empty($data['project_id'])) {
            abort_if(
                ! Project::where('id', $data['project_id'])->where('company_id', $this->companyId())->exists(),
                403
            );
        }

        $task = Task::create([
            'title'       => $data['title'],
            'description' => $data['notes'] ?? null,
            'due_date'    => $data['due_date'] ?? null,
            'list_group'  => ($data['due_date'] ?? null) ? null : ($data['list_group'] ?? 'recent'),
            'priority'    => $data['priority'],
            'project_id'  => $data['project_id'] ?? null,
            'status'      => 'todo',
            'company_id'  => $this->companyId(),
            'created_by'  => auth()->id(),
            'assigned_to' => auth()->id(),
        ]);

        $task->assignees()->syncWithoutDetaching([auth()->id()]);

        return $task;
    }

    protected function applyMyTaskMove(Task $task, string $group, ?string $dueDate = null): void
    {
        $update = match ($group) {
            'overdue'  => ['due_date' => now()->subDay()->toDateString(), 'list_group' => null],
            'today'    => ['due_date' => now()->toDateString(), 'list_group' => null],
            'upcoming' => ['due_date' => now()->addDay()->toDateString(), 'list_group' => null],
            'later'    => ['due_date' => null, 'list_group' => 'later'],
            'recently', 'recent' => ['due_date' => null, 'list_group' => 'recent'],
            'calendar' => ['due_date' => $dueDate, 'list_group' => null],
            default    => null,
        };

        abort_if($update === null, 422);
        if ($group === 'calendar') {
            abort_if(! $dueDate, 422);
        }

        $task->update($update);
    }

    protected function authorizeMine(Task $task): void
    {
        abort_if($task->company_id !== $this->companyId(), 403);

        $userId = auth()->id();
        $task->loadMissing('assignees');
        $isMine = $task->assigned_to === $userId
            || $task->assignees->contains('id', $userId)
            || ($task->project_id === null && $task->created_by === $userId);

        abort_if(! $isMine, 403);
    }

    protected function groupDuePreset(string $key): ?string
    {
        return match ($key) {
            'overdue'  => now()->subDay()->format('Y-m-d'),
            'today'    => now()->format('Y-m-d'),
            'upcoming' => now()->addDay()->format('Y-m-d'),
            default    => null,
        };
    }
}
