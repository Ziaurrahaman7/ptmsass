<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('assigned_to', auth()->id())->with('project');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        return view('employee.tasks.index', compact('tasks'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== auth()->id(), 403);

        $request->validate(['status' => 'required|in:todo,in_progress,in_review,done']);

        $task->update(['status' => $request->status]);

        return back()->with('success', 'Task status updated.');
    }
}
