<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function index()
    {
        $tasks = Task::where('company_id', $this->companyId())
            ->with(['project', 'assignee'])
            ->latest()
            ->paginate(20);

        return view('company.tasks.index', compact('tasks'));
    }

    public function store(Request $request, Project $project)
    {
        abort_if($project->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in_progress,in_review,done',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        Task::create([...$data,
            'project_id' => $project->id,
            'company_id' => $this->companyId(),
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, Task $task)
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

        $task->update($data);

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}
