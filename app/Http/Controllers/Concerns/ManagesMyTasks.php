<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Priority;
use App\Models\Project;
use App\Models\Task;
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

    protected function calendarMyTasks(?string $month): array
    {
        try {
            $cursor = $month
                ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Exception) {
            $cursor = now()->startOfMonth();
        }

        $gridStart = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $tasks = $this->myTasksBaseQuery()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->with(['project', 'assignees'])
            ->orderBy('due_date')
            ->get();

        return [
            'cursor' => $cursor,
            'prevMonth' => $cursor->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $cursor->copy()->addMonth()->format('Y-m'),
            'days' => CarbonPeriod::create($gridStart, $gridEnd)->toArray(),
            'tasksByDate' => $tasks->groupBy(fn (Task $t) => $t->due_date->toDateString()),
            'todayStr' => now()->toDateString(),
            'monthNum' => $cursor->month,
        ];
    }

    protected function myTasksIndexData(Request $request): array
    {
        $groups = $this->groupedMyTasks();
        $calendar = $this->calendarMyTasks($request->query('month'));
        $view = $request->query('view') === 'calendar' ? 'calendar' : 'list';
        $priorities = Priority::forCompany($this->companyId());
        $projects = Project::where('company_id', $this->companyId())
            ->where('is_template', false)
            ->orderBy('name')
            ->get(['id', 'name']);

        return array_merge($groups, $calendar, compact('view', 'priorities', 'projects'));
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
