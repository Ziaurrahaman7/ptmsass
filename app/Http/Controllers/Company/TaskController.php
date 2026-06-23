<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Return the section id only if it belongs to the given project (and company); else null.
     */
    private function validSectionId($sectionId, int $projectId): ?int
    {
        if (empty($sectionId)) {
            return null;
        }

        $belongs = \App\Models\Section::where('id', $sectionId)
            ->where('project_id', $projectId)
            ->where('company_id', $this->companyId())
            ->exists();

        return $belongs ? (int) $sectionId : null;
    }

    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function index(Request $request, string $slug)
    {
        $query = Task::where('company_id', $this->companyId())
            ->whereNull('parent_task_id')
            ->with(['project', 'assignee', 'assignees']);

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('project'))  $query->where('project_id', $request->project);

        $tasks    = $query->latest()->paginate(20)->withQueryString();
        $projects = Project::where('company_id', $this->companyId())->orderBy('name')->get();
        $members  = auth()->user()->company->users()->where('is_active', true)->get();

        return view('company.tasks.index', compact('tasks', 'projects', 'members', 'slug'));
    }

    public function show(string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        
        $task->load(['project', 'assignee', 'assignees', 'comments.user', 'attachments.uploader', 'activities.user', 'subtasks.assignees']);
        $members = auth()->user()->company->users()->where('is_active', true)->get();
        
        return view('company.tasks.show', compact('task', 'members', 'slug'));
    }

    public function storeFromIndex(Request $request, string $slug)
    {
        $data = $request->validate([
            'parent_task_id' => 'nullable|exists:tasks,id',
            'project_id'  => ['required', 'exists:projects,id'],
            'section_id'  => 'nullable|exists:sections,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $project = Project::where('id', $data['project_id'])
            ->where('company_id', $this->companyId())
            ->firstOrFail();

        $task = Task::create([
            'parent_task_id' => $data['parent_task_id'] ?? null,
            'project_id' => $data['project_id'],
            'section_id' => $this->validSectionId($data['section_id'] ?? null, $project->id),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
        ]);

        if (!empty($data['assignees'])) {
            $task->assignees()->attach($data['assignees']);
        }

        ActivityLog::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'created',
            'description' => auth()->user()->name . ' created this task',
        ]);

        $assigneeIds = !empty($data['assignees']) ? $data['assignees'] : [];
        if ($task->assigned_to) {
            $assigneeIds[] = $task->assigned_to;
        }
        $assigneeIds = array_unique($assigneeIds);

        foreach ($assigneeIds as $userId) {
            if ($userId !== auth()->id()) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'task_assigned',
                    'title' => 'New Task Assigned',
                    'message' => auth()->user()->name . ' assigned you a task: ' . $task->title,
                    'link' => route('employee.tasks.show', [$slug, $task]),
                ]);
            }
        }

        return redirect()->route('company.tasks.index', $slug)->with('success', 'Task created.');
    }

    public function store(Request $request, string $slug, Project $project)
    {
        abort_if($project->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'parent_task_id' => 'nullable|exists:tasks,id',
            'section_id'  => 'nullable|exists:sections,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $task = Task::create([
            'parent_task_id' => $data['parent_task_id'] ?? null,
            'project_id' => $project->id,
            'section_id' => $this->validSectionId($data['section_id'] ?? null, $project->id),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
        ]);
        
        if (!empty($data['assignees'])) {
            $task->assignees()->attach($data['assignees']);
        }
        
        ActivityLog::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'created',
            'description' => auth()->user()->name . ' created this task',
        ]);
        
        $assigneeIds = !empty($data['assignees']) ? $data['assignees'] : [];
        if ($task->assigned_to) {
            $assigneeIds[] = $task->assigned_to;
        }
        $assigneeIds = array_unique($assigneeIds);
        
        foreach ($assigneeIds as $userId) {
            if ($userId !== auth()->id()) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'task_assigned',
                    'title' => 'New Task Assigned',
                    'message' => auth()->user()->name . ' assigned you a task: ' . $task->title,
                    'link' => route('employee.tasks.show', [$slug, $task]),
                ]);
            }
        }

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'section_id'  => 'nullable|exists:sections,id',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $changes = [];
        $oldAssignee = $task->assigned_to;
        $oldPriority = $task->priority;
        $oldDueDate = $task->due_date?->format('Y-m-d');
        $newDueDate = isset($data['due_date']) ? date('Y-m-d', strtotime($data['due_date'])) : null;
        $oldAssigneeIds = $task->assignees->pluck('id')->toArray();
        $newAssigneeIds = $data['assignees'] ?? [];
        
        if ($task->status !== $data['status']) {
            $changes[] = 'status from ' . $task->status . ' to ' . $data['status'];
        }
        if ($task->priority !== $data['priority']) {
            $changes[] = 'priority from ' . $task->priority . ' to ' . $data['priority'];
        }
        if ($task->assigned_to !== $data['assigned_to']) {
            $changes[] = 'assignee';
        }
        if ($oldDueDate !== $newDueDate) {
            $changes[] = 'due date';
        }
        if ($oldAssigneeIds != $newAssigneeIds) {
            $changes[] = 'assignees';
        }

        $task->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'section_id' => $this->validSectionId($data['section_id'] ?? null, $task->project_id),
            'status' => $data['status'],
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);
        
        if (!empty($data['assignees'])) {
            $task->assignees()->sync($data['assignees']);
        } else {
            $task->assignees()->detach();
        }
        
        if (!empty($changes)) {
            ActivityLog::create([
                'company_id' => $this->companyId(),
                'user_id' => auth()->id(),
                'subject_type' => Task::class,
                'subject_id' => $task->id,
                'action' => 'updated',
                'description' => auth()->user()->name . ' updated ' . implode(', ', $changes),
            ]);
        }
        
        $allNewAssignees = $newAssigneeIds;
        if ($task->assigned_to) {
            $allNewAssignees[] = $task->assigned_to;
        }
        $allNewAssignees = array_unique($allNewAssignees);
        
        if ($oldAssignee !== $task->assigned_to && $task->assigned_to && $task->assigned_to !== auth()->id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'task_assigned',
                'title' => 'Task Reassigned',
                'message' => auth()->user()->name . ' assigned you a task: ' . $task->title,
                'link' => route('employee.tasks.show', [$slug, $task]),
            ]);
        }
        
        $addedAssignees = array_diff($newAssigneeIds, $oldAssigneeIds);
        foreach ($addedAssignees as $userId) {
            if ($userId !== auth()->id()) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'task_assigned',
                    'title' => 'Task Assigned',
                    'message' => auth()->user()->name . ' assigned you a task: ' . $task->title,
                    'link' => route('employee.tasks.show', [$slug, $task]),
                ]);
            }
        }
        
        if ($oldPriority !== $task->priority) {
            foreach ($allNewAssignees as $userId) {
                if ($userId !== auth()->id()) {
                    Notification::create([
                        'user_id' => $userId,
                        'type' => 'task_updated',
                        'title' => 'Task Priority Changed',
                        'message' => auth()->user()->name . ' changed priority of "' . $task->title . '" from ' . $oldPriority . ' to ' . $task->priority,
                        'link' => route('employee.tasks.show', [$slug, $task]),
                    ]);
                }
            }
        }
        
        if ($oldDueDate !== $newDueDate) {
            $dueDateMsg = $newDueDate ? 'to ' . date('d M Y', strtotime($newDueDate)) : 'removed';
            foreach ($allNewAssignees as $userId) {
                if ($userId !== auth()->id()) {
                    Notification::create([
                        'user_id' => $userId,
                        'type' => 'task_updated',
                        'title' => 'Task Due Date Changed',
                        'message' => auth()->user()->name . ' changed due date of "' . $task->title . '" ' . $dueDateMsg,
                        'link' => route('employee.tasks.show', [$slug, $task]),
                    ]);
                }
            }
        }

        return back()->with('success', 'Task updated.');
    }

    public function destroy(string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        $task->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task deleted.');
    }

    /**
     * Quick inline task creation (List view "Add task" row).
     */
    public function quickStore(Request $request, string $slug, Project $project)
    {
        abort_if($project->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        Task::create([
            'project_id' => $project->id,
            'section_id' => $this->validSectionId($data['section_id'] ?? null, $project->id),
            'title'      => $data['title'],
            'status'     => 'todo',
            'priority'   => 'medium',
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Task added.');
    }

    /**
     * Inline single/partial field update from the List view (AJAX). Only updates the
     * fields actually present in the request.
     */
    public function inlineUpdate(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status'      => 'sometimes|required|in:todo,in_progress,in_review,done',
            'priority'    => 'sometimes|required|in:low,medium,high,urgent',
            'due_date'    => 'sometimes|nullable|date',
            'section_id'  => 'sometimes|nullable|exists:sections,id',
            'assignees'   => 'sometimes|array',
            'assignees.*' => 'exists:users,id',
        ]);

        $update = [];
        foreach (['title', 'description', 'status', 'priority', 'due_date'] as $field) {
            if ($request->has($field)) {
                $update[$field] = $data[$field] ?? null;
            }
        }
        if ($request->has('section_id')) {
            $update['section_id'] = $this->validSectionId($data['section_id'] ?? null, $task->project_id);
        }
        if (!empty($update)) {
            $task->update($update);
        }

        if ($request->has('assignees')) {
            $task->assignees()->sync($data['assignees'] ?? []);
        }

        $task->load('assignees');

        return response()->json([
            'success' => true,
            'task' => [
                'id'         => $task->id,
                'title'      => $task->title,
                'status'     => $task->status,
                'priority'   => $task->priority,
                'due_date'   => $task->due_date?->format('Y-m-d'),
                'due_label'  => $task->due_date?->format('d M Y'),
                'overdue'    => $task->due_date && $task->due_date->isPast() && $task->status !== 'done',
                'section_id' => $task->section_id,
                'assignees'  => $task->assignees->map(fn($u) => [
                    'id'      => $u->id,
                    'name'    => $u->name,
                    'initial' => strtoupper(substr($u->name, 0, 1)),
                ])->values(),
            ],
        ]);
    }

    public function updateStatus(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        
        $request->validate(['status' => 'required|in:todo,in_progress,in_review,done']);
        
        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);
        
        ActivityLog::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'status_changed',
            'description' => auth()->user()->name . ' moved task from ' . $oldStatus . ' to ' . $request->status,
        ]);
        
        return response()->json(['success' => true]);
    }

    public function storeComment(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        
        $request->validate(['comment' => 'required|string|max:1000']);
        
        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);
        
        // Log activity
        ActivityLog::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'commented',
            'description' => auth()->user()->name . ' added a comment',
        ]);
        
        // Notify task assignee
        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'type' => 'task_comment',
                'title' => 'New Comment',
                'message' => auth()->user()->name . ' commented on: ' . $task->title,
                'link' => route('employee.tasks.show', [$slug, $task]),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Comment added.');
    }

    public function destroyComment(string $slug, TaskComment $comment)
    {
        abort_if($comment->task->company_id !== $this->companyId(), 403);
        abort_if($comment->user_id !== auth()->id(), 403);

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Comment deleted.');
    }
    
    public function storeAttachment(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);
        
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('task-attachments', $fileName, 'public');
        
        TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
        
        // Log activity
        ActivityLog::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'uploaded',
            'description' => auth()->user()->name . ' uploaded a file: ' . $file->getClientOriginalName(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File uploaded.');
    }

    public function destroyAttachment(string $slug, TaskAttachment $attachment)
    {
        abort_if($attachment->task->company_id !== $this->companyId(), 403);

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File deleted.');
    }

    /**
     * Render the task detail panel (HTML fragment) used by the slide-in drawer.
     */
    public function panel(string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);

        $task->load([
            'project', 'section', 'assignees', 'comments.user',
            'attachments.uploader', 'subtasks.assignees',
        ]);
        $members  = auth()->user()->company->users()->where('is_active', true)->get();
        $sections = $task->project->sections()->get();

        return view('company.tasks._panel', compact('task', 'members', 'sections', 'slug'));
    }

    /**
     * Add a subtask to a task (from the detail panel).
     */
    public function storeSubtask(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        Task::create([
            'parent_task_id' => $task->id,
            'project_id'     => $task->project_id,
            'section_id'     => $task->section_id,
            'title'          => $data['title'],
            'status'         => 'todo',
            'priority'       => 'medium',
            'company_id'     => $this->companyId(),
            'created_by'     => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Subtask added.');
    }
}
