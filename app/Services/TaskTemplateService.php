<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\User;

class TaskTemplateService
{
    public function apply(TaskTemplate $template, Project $project, User $actor): Task
    {
        $task = Task::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'created_by' => $actor->id,
            'title' => $template->name,
            'description' => $template->description,
            'priority' => $template->priority,
            'status' => 'todo',
            'due_date' => $template->due_in_days ? now()->addDays($template->due_in_days) : null,
            'custom_values' => $template->custom_values,
            'task_template_id' => $template->id,
        ]);

        foreach ($template->subtasks ?? [] as $title) {
            if (! is_string($title) || trim($title) === '') {
                continue;
            }
            Task::create([
                'company_id' => $project->company_id,
                'project_id' => $project->id,
                'parent_task_id' => $task->id,
                'created_by' => $actor->id,
                'title' => $title,
                'status' => 'todo',
            ]);
        }

        return $task;
    }
}
