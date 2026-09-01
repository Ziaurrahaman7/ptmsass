<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\TaskAttachmentIntake;
use App\Services\WorkspaceNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $userId = auth()->id();
        
        // Get tasks where user is assigned (either in assigned_to OR in assignees pivot table)
        $query = Task::where(function($q) use ($userId) {
            $q->where('assigned_to', $userId)
              ->orWhereHas('assignees', function($q) use ($userId) {
                  $q->where('user_id', $userId);
              });
        })
        ->whereNull('parent_task_id') // Only show parent tasks, not subtasks
        ->with(['project', 'assignees']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        return view('employee.tasks.index', compact('tasks'));
    }

    public function updateStatus(Request $request, string $slug, Task $task)
    {
        // Check if user is assigned (either assigned_to or in assignees)
        $isAssigned = $task->assigned_to === auth()->id() || 
                      $task->assignees->contains('id', auth()->id());
        
        abort_if(!$isAssigned, 403);

        $request->validate(['status' => 'required|in:todo,in_progress,in_review,done']);
        
        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);
        
        if ($oldStatus !== $request->status) {
            app(WorkspaceNotifier::class)->statusChanged($task, auth()->user(), $oldStatus, $request->status);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task status updated.');
    }

    public function show(string $slug, Task $task)
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);

        $task->load(['project', 'assignee', 'assignees', 'comments.user', 'attachments.uploader', 'activities.user', 'subtasks.assignees']);

        $userId = auth()->id();
        $isMine = $task->assigned_to === $userId || $task->assignees->contains('id', $userId);

        $members = User::where('company_id', $task->company_id)->where('is_active', true)->whereIn('role', ['employee', 'company_admin'])->get();

        return view('employee.tasks.show', compact('task', 'isMine', 'slug', 'members'));
    }

    /**
     * Update a field an employee is allowed to edit on their own assigned task (currently: description only).
     */
    public function inlineUpdate(Request $request, string $slug, Task $task)
    {
        $this->authorizeMine($task);

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'due_date'    => 'sometimes|nullable|date',
            'list_group'  => 'sometimes|nullable|in:recent,later',
            'status'      => 'sometimes|in:todo,in_progress,in_review,done',
        ]);
        $task->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Description updated.');
    }

    public function storeSubtask(Request $request, string $slug, Task $task)
    {
        $this->authorizeMine($task);

        $data = $request->validate(['title' => 'required|string|max:255']);

        Task::create([
            'parent_task_id' => $task->id,
            'project_id'     => $task->project_id,
            'section_id'     => $task->section_id,
            'title'          => $data['title'],
            'status'         => 'todo',
            'priority'       => \App\Models\Priority::defaultSlugFor($task->company_id),
            'company_id'     => $task->company_id,
            'created_by'     => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Subtask added.');
    }

    private function authorizeMine(Task $task): void
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);
        $userId = auth()->id();
        $isMine = $task->assigned_to === $userId
            || $task->assignees->contains('id', $userId)
            || ($task->project_id === null && $task->created_by === $userId);
        abort_if(!$isMine, 403);
    }

    /**
     * Render the task detail panel (HTML fragment) for the employee slide-in drawer.
     */
    public function panel(string $slug, Task $task)
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);

        $task->load(['project', 'section', 'assignees', 'followers', 'comments.user', 'attachments.uploader', 'subtasks.assignees']);
        $members = User::where('company_id', $task->company_id)->where('is_active', true)->whereIn('role', ['employee', 'company_admin'])->get();

        $userId = auth()->id();
        $isMine = $task->assigned_to === $userId
            || $task->assignees->contains('id', $userId)
            || ($task->project_id === null && $task->created_by === $userId);

        return view('employee.tasks._panel', compact('task', 'isMine', 'slug', 'members'));
    }

    public function storeComment(Request $request, string $slug, Task $task)
    {
        $this->authorizeMine($task);
        
        $request->validate([
            'comment' => 'required|string|max:4000',
            'mentioned_ids' => 'nullable|array',
            'mentioned_ids.*' => 'integer',
        ]);
        
        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);
        
        ActivityLog::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'commented',
            'description' => auth()->user()->name . ' added a comment',
        ]);

        $members = User::where('company_id', $task->company_id)->where('is_active', true)->get();
        app(WorkspaceNotifier::class)->commented(
            $task,
            $request->comment,
            auth()->user(),
            $members,
            $request->input('mentioned_ids', [])
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Comment added.');
    }

    public function destroyComment(string $slug, TaskComment $comment)
    {
        abort_if($comment->task->company_id !== auth()->user()->company_id, 403);
        abort_if($comment->user_id !== auth()->id(), 403);

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Comment deleted.');
    }
    
    public function storeAttachment(Request $request, string $slug, Task $task, TaskAttachmentIntake $intake)
    {
        $this->authorizeMine($task);

        $request->validate(['file' => 'required|file|max:10240']);

        $intake->queue($task, $request->user(), $request->file('file'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'queued' => true]);
        }

        return back()->with('success', 'Upload queued. The file will appear shortly — check Inbox if it takes a moment.');
    }

    public function destroyAttachment(string $slug, TaskAttachment $attachment)
    {
        $this->authorizeMine($attachment->task);

        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File deleted.');
    }

    public function storeFollower(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);
        $task->followers()->syncWithoutDetaching([auth()->id()]);

        return response()->json(['success' => true]);
    }

    public function destroyFollower(string $slug, Task $task, User $user)
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);
        abort_if($user->id !== auth()->id(), 403);
        $task->followers()->detach($user->id);

        return response()->json(['success' => true]);
    }
}
