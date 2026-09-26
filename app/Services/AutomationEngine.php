<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\Task;
use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    public function fire(string $trigger, Task $task): void
    {
        $rules = AutomationRule::query()
            ->where('company_id', $task->company_id)
            ->where('is_active', true)
            ->where('trigger', $trigger)
            ->where(function ($q) use ($task) {
                $q->whereNull('project_id')->orWhere('project_id', $task->project_id);
            })
            ->get();

        foreach ($rules as $rule) {
            try {
                if (! $this->matches($rule, $task)) {
                    continue;
                }
                $this->apply($rule, $task);
                $rule->runs()->create([
                    'task_id' => $task->id,
                    'status' => 'ok',
                    'message' => 'Ran '.$rule->name,
                ]);
            } catch (\Throwable $e) {
                $rule->runs()->create([
                    'task_id' => $task->id,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
                report($e);
            }
        }

        $this->webhooks($trigger, $task);
    }

    protected function matches(AutomationRule $rule, Task $task): bool
    {
        $conditions = $rule->conditions ?? [];
        if (isset($conditions['status']) && $conditions['status'] !== $task->status) {
            return false;
        }
        if (isset($conditions['priority']) && $conditions['priority'] !== $task->priority) {
            return false;
        }

        return true;
    }

    protected function apply(AutomationRule $rule, Task $task): void
    {
        $actions = $rule->actions ?? [];
        if (! empty($actions['status'])) {
            $task->update(['status' => $actions['status']]);
        }
        if (! empty($actions['assign_user_id'])) {
            $task->update(['assigned_to' => (int) $actions['assign_user_id']]);
            $task->assignees()->syncWithoutDetaching([(int) $actions['assign_user_id']]);
        }
        if (! empty($actions['comment'])) {
            $task->comments()->create([
                'user_id' => $task->created_by,
                'comment' => $actions['comment'],
            ]);
        }
    }

    protected function webhooks(string $event, Task $task): void
    {
        $hooks = Webhook::query()
            ->where('company_id', $task->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($event) {
                $q->where('event', $event)->orWhere('event', '*');
            })
            ->get();

        foreach ($hooks as $hook) {
            try {
                Http::timeout(5)->post($hook->url, [
                    'event' => $event,
                    'task_id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'secret' => $hook->secret,
                ]);
            } catch (\Throwable $e) {
                Log::warning('webhook.failed', ['id' => $hook->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
