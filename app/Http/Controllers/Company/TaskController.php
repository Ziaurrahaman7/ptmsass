<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function index(Request $request, string $slug)
    {
        $query = Task::where('company_id', $this->companyId())->with(['project', 'assignee']);

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
        
        $task->load(['project', 'assignee', 'comments.user', 'attachments.uploader', 'activities.user']);
        $members = auth()->user()->company->users()->where('is_active', true)->get();
        
        return view('company.tasks.show', compact('task', 'members'));
    }

    public function storeFromIndex(Request $request, string $slug)
    {
        $data = $request->validate([
            'project_id'  => ['required', 'exists:projects,id'],
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        // Check project belongs to user's company
        $project = Project::where('id', $data['project_id'])
            ->where('company_id', $this->companyId())
            ->firstOrFail();

        try {
            $task = Task::create([...$data,
                'company_id' => $this->companyId(),
                'created_by' => auth()->id(),
            ]);
            \Log::info('Task created', ['task_id' => $task->id]);
        } catch (\Exception $e) {
            \Log::error('Task creation failed', ['error' => $e->getMessage()]);
            return redirect()->route('company.tasks.index', $slug)->with('error', 'Failed to create task.');
        }

        return redirect()->route('company.tasks.index', $slug)->with('success', 'Task created.');
    }

    public function store(Request $request, string $slug, Project $project)
    {
        // Debug logging
        \Log::info('Task Store Debug', [
            'project_id' => $project->id,
            'project_company_id' => $project->company_id,
            'user_id' => auth()->id(),
            'user_company_id' => auth()->user()->company_id,
            'slug' => $slug,
        ]);
        
        abort_if($project->company_id !== $this->companyId(), 403, 'Project company mismatch');

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $task = Task::create([...$data,
            'project_id' => $project->id,
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
        ]);
        
        // Log activity
        ActivityLog::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'created',
            'description' => auth()->user()->name . ' created this task',
        ]);

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);
        
        $changes = [];
        if ($task->status !== $data['status']) {
            $changes[] = 'status from ' . $task->status . ' to ' . $data['status'];
        }
        if ($task->priority !== $data['priority']) {
            $changes[] = 'priority from ' . $task->priority . ' to ' . $data['priority'];
        }
        if ($task->assigned_to !== $data['assigned_to']) {
            $changes[] = 'assignee';
        }

        $task->update($data);
        
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

        return back()->with('success', 'Task updated.');
    }

    public function destroy(string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        $task->delete();

        return back()->with('success', 'Task deleted.');
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
        
        return back()->with('success', 'Comment added.');
    }
    
    public function destroyComment(string $slug, TaskComment $comment)
    {
        abort_if($comment->task->company_id !== $this->companyId(), 403);
        abort_if($comment->user_id !== auth()->id(), 403);
        
        $comment->delete();
        
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
        
        return back()->with('success', 'File deleted.');
    }
}
