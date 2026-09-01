<?php

namespace App\Services;

use App\Models\Priority;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ProjectCsv
{
    public function import(Project $project, string $absolutePath, int $actorId): int
    {
        $rows = array_map('str_getcsv', file($absolutePath) ?: []);
        if ($rows === []) {
            return 0;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($rows) ?: []);

        $statusMap = [
            'not started' => 'todo',
            'in progress' => 'in_progress',
            'in review' => 'in_review',
            'completed' => 'done',
            'todo' => 'todo',
            'in_progress' => 'in_progress',
            'in_review' => 'in_review',
            'done' => 'done',
        ];

        $companyId = (int) $project->company_id;
        $companyPriorities = Priority::forCompany($companyId);
        $priorityByNameOrSlug = $companyPriorities->flatMap(fn ($p) => [
            strtolower($p->slug) => $p->slug,
            strtolower($p->name) => $p->slug,
        ]);
        $defaultPrioritySlug = Priority::defaultSlugFor($companyId);
        $sectionCache = [];
        $taskTitleCache = [];

        $members = User::where('company_id', $companyId)->where('is_active', true)->get();
        $memberByName = $members->keyBy(fn ($u) => strtolower($u->name));
        $memberByEmail = $members->keyBy(fn ($u) => strtolower($u->email));

        $position = (int) $project->tasks()->max('position');
        $imported = 0;

        $col = fn ($r, array $keys) => collect($keys)
            ->map(fn ($k) => trim($r[$k] ?? ''))
            ->first(fn ($v) => $v !== '') ?? '';

        foreach ($rows as $row) {
            if (count($row) < 1 || trim($row[0] ?? '') === '') {
                continue;
            }
            $r = array_combine($header, array_pad($row, count($header), null)) ?: [];

            $title = $col($r, ['name', 'title']);
            if ($title === '') {
                continue;
            }

            $sectionId = null;
            $sectionName = $col($r, ['section/column', 'section']);
            if ($sectionName !== '') {
                if (! isset($sectionCache[$sectionName])) {
                    $sectionCache[$sectionName] = $project->sections()->firstOrCreate(
                        ['name' => $sectionName],
                        ['company_id' => $companyId, 'position' => $project->sections()->count()]
                    )->id;
                }
                $sectionId = $sectionCache[$sectionName];
            }

            $statusRaw = strtolower($col($r, ['status']));
            $status = $statusMap[$statusRaw] ?? 'todo';
            $priority = $priorityByNameOrSlug->get(strtolower($col($r, ['priority'])), $defaultPrioritySlug);

            $assigneeName = strtolower($col($r, ['assignee']));
            $assigneeEmail = strtolower($col($r, ['assignee email', 'assignee_email']));
            $assignedTo = $memberByName->get($assigneeName)?->id
                ?? $memberByEmail->get($assigneeEmail)?->id;

            $dueDate = $col($r, ['due date', 'due_date']) ?: null;
            $startDate = $col($r, ['start date', 'start_date']) ?: null;
            $description = $col($r, ['notes', 'description']) ?: null;

            $parentTaskTitle = $col($r, ['parent task', 'parent_task']);
            $parentTaskId = $parentTaskTitle !== '' ? ($taskTitleCache[strtolower($parentTaskTitle)] ?? null) : null;

            $task = Task::create([
                'company_id' => $companyId,
                'project_id' => $project->id,
                'section_id' => $sectionId,
                'parent_task_id' => $parentTaskId,
                'created_by' => $actorId,
                'assigned_to' => $assignedTo,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'priority' => $priority,
                'start_date' => $startDate ?: null,
                'due_date' => $dueDate ?: null,
                'position' => ++$position,
            ]);

            $taskTitleCache[strtolower($title)] = $task->id;
            $imported++;
        }

        return $imported;
    }

    public function exportToPath(Project $project, string $absolutePath): void
    {
        $tasks = $project->tasks()
            ->with(['assignees', 'section', 'parentTask', 'blockedBy', 'blocking'])
            ->orderBy('position')
            ->get();
        $priorityNames = Priority::forCompany((int) $project->company_id)->pluck('name', 'slug');

        $statusLabels = [
            'todo' => 'Not Started',
            'in_progress' => 'In Progress',
            'in_review' => 'In Review',
            'done' => 'Completed',
        ];

        $out = fopen($absolutePath, 'w');
        fputcsv($out, [
            'Task ID',
            'Created At',
            'Completed At',
            'Last Modified',
            'Name',
            'Section/Column',
            'Assignee',
            'Assignee Email',
            'Start Date',
            'Due Date',
            'Tags',
            'Notes',
            'Projects',
            'Parent Task',
            'Blocked By',
            'Blocking (Deps)',
            'Priority',
            'Note',
            'Status',
        ]);
        foreach ($tasks as $t) {
            $firstAssignee = $t->assignees->first();
            fputcsv($out, [
                $t->id,
                $t->created_at?->format('n/j/Y'),
                $t->status === 'done' ? $t->updated_at?->format('n/j/Y') : '',
                $t->updated_at?->format('n/j/Y'),
                $t->title,
                $t->section?->name,
                $firstAssignee?->name,
                $firstAssignee?->email,
                $t->start_date?->format('n/j/Y') ?? $t->created_at?->format('n/j/Y'),
                $t->due_date?->format('n/j/Y'),
                '',
                $t->description,
                $project->name,
                $t->parentTask?->title,
                $t->blockedBy->pluck('title')->implode(', '),
                $t->blocking->pluck('title')->implode(', '),
                $priorityNames->get($t->priority, $t->priority),
                '',
                $statusLabels[$t->status] ?? $t->status,
            ]);
        }
        fclose($out);
    }
}
