<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRecurringTasks implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $tasks = Task::query()
            ->whereNotNull('recurrence')
            ->where('status', 'done')
            ->whereNotNull('due_date')
            ->get();

        foreach ($tasks as $task) {
            if ($task->recurrence_until && $task->recurrence_until->lt(now()->startOfDay())) {
                continue;
            }
            if ($task->recurrence_remaining !== null && $task->recurrence_remaining < 1) {
                continue;
            }

            $nextDue = match ($task->recurrence) {
                'daily' => $task->due_date->copy()->addDay(),
                'weekly' => $task->due_date->copy()->addWeek(),
                'monthly' => $task->due_date->copy()->addMonth(),
                default => null,
            };
            if (! $nextDue) {
                continue;
            }

            $copy = $task->replicate(['status', 'due_date', 'recurrence_remaining']);
            $copy->status = 'todo';
            $copy->due_date = $nextDue;
            if ($task->recurrence_remaining !== null) {
                $copy->recurrence_remaining = max(0, $task->recurrence_remaining - 1);
            }
            $copy->save();
            $copy->assignees()->sync($task->assignees()->pluck('users.id'));
            if ($task->project_id) {
                $copy->projects()->syncWithoutDetaching([$task->project_id]);
            }

            $task->update(['recurrence' => null]);
        }
    }
}
