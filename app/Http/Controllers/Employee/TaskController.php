<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
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
        
        // Notify company admin about status change
        $companyAdmins = $task->project->company->users()->where('role', 'company_admin')->get();
        foreach ($companyAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'task_status_changed',
                'title' => 'Task Status Updated',
                'message' => auth()->user()->name . ' moved "' . $task->title . '" from ' . $oldStatus . ' to ' . $request->status,
                'link' => route('company.tasks.show', [$slug, $task]),
            ]);
        }

        return back()->with('success', 'Task status updated.');
    }

    public function show(string $slug, Task $task)
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);
        
        $task->load(['project', 'assignee', 'assignees', 'comments.user', 'attachments.uploader', 'activities.user', 'subtasks.assignees']);
        
        return view('employee.tasks.show', compact('task'));
    }

    public function storeComment(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);
        
        $request->validate(['comment' => 'required|string|max:1000']);
        
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
        
        // Notify company admin
        $companyAdmins = $task->project->company->users()->where('role', 'company_admin')->get();
        foreach ($companyAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'task_comment',
                'title' => 'New Comment',
                'message' => auth()->user()->name . ' commented on: ' . $task->title,
                'link' => route('company.tasks.show', [$slug, $task]),
            ]);
        }
        
        return back()->with('success', 'Comment added.');
    }
    
    public function destroyComment(string $slug, TaskComment $comment)
    {
        abort_if($comment->task->company_id !== auth()->user()->company_id, 403);
        abort_if($comment->user_id !== auth()->id(), 403);
        
        $comment->delete();
        
        return back()->with('success', 'Comment deleted.');
    }
    
    public function storeAttachment(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== auth()->user()->company_id, 403);
        
        $request->validate(['file' => 'required|file|max:10240']);
        
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
        
        ActivityLog::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'uploaded',
            'description' => auth()->user()->name . ' uploaded a file: ' . $file->getClientOriginalName(),
        ]);
        
        return back()->with('success', 'File uploaded.');
    }
    
    public function destroyAttachment(string $slug, TaskAttachment $attachment)
    {
        abort_if($attachment->task->company_id !== auth()->user()->company_id, 403);
        
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        $attachment->delete();
        
        return back()->with('success', 'File deleted.');
    }
}
