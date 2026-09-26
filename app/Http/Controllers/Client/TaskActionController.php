<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskApproval;
use App\Models\TaskComment;
use App\Services\TaskAttachmentIntake;
use App\Services\WorkspaceNotifier;
use Illuminate\Http\Request;

class TaskActionController extends Controller
{
    public function comment(Request $request, string $slug, Task $task)
    {
        $this->authorize('comment', $task);
        $request->validate(['comment' => 'required|string|max:4000']);
        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);
        $members = auth()->user()->company->users()->where('is_active', true)->get();
        app(WorkspaceNotifier::class)->commented($task, $request->comment, auth()->user(), $members);

        return back()->with('success', 'Comment added.');
    }

    public function attach(Request $request, string $slug, Task $task, TaskAttachmentIntake $intake)
    {
        $this->authorize('attach', $task);
        $request->validate(['file' => 'required|file|max:10240']);
        $intake->queue($task, $request->user(), $request->file('file'));

        return back()->with('success', 'Upload queued.');
    }

    public function follow(string $slug, Task $task)
    {
        $this->authorize('comment', $task);
        $task->followers()->syncWithoutDetaching([auth()->id()]);

        return back()->with('success', 'Following task.');
    }

    public function updateStatus(Request $request, string $slug, Task $task)
    {
        $this->authorize('update', $task);
        $request->validate(['status' => 'required|in:todo,in_progress,in_review,done']);
        $task->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }

    public function decide(Request $request, string $slug, Task $task, TaskApproval $approval)
    {
        $this->authorize('approve', $task);
        abort_if((int) $approval->task_id !== (int) $task->id, 404);
        $data = $request->validate([
            'status' => 'required|in:approved,rejected,changes_requested',
            'note' => 'nullable|string|max:2000',
        ]);
        $approval->update([
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
            'decided_at' => now(),
        ]);

        return back()->with('success', 'Decision saved.');
    }
}
