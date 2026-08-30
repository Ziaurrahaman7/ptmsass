<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MyTaskController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    private function priorityRule(): string
    {
        return 'in:' . Priority::forCompany($this->companyId())->pluck('slug')->implode(',');
    }

    private function baseQuery(): Builder
    {
        return Task::query()
            ->where('company_id', $this->companyId())
            ->whereNull('parent_task_id')
            ->where(function (Builder $q) {
                $q->whereHas('assignees', fn(Builder $q) => $q->where('users.id', auth()->id()))
                  ->orWhere('assigned_to', auth()->id())
                  ->orWhere(fn(Builder $q) => $q->whereNull('project_id')->where('created_by', auth()->id()));
            });
    }

    public function index(string $slug)
    {
        $today = $this->baseQuery()
            ->whereDate('due_date', today())
            ->where('status', '!=', 'done')
            ->orderBy('priority')
            ->with(['project', 'assignees'])
            ->get();

        $overdue = $this->baseQuery()
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', 'done')
            ->orderByDesc('due_date')
            ->with(['project', 'assignees'])
            ->get();

        $upcoming = $this->baseQuery()
            ->where(function (Builder $q) {
                $q->whereDate('due_date', '>', today())->orWhereNull('due_date');
            })
            ->where('status', '!=', 'done')
            ->orderBy('due_date')
            ->with(['project', 'assignees'])
            ->get();

        $done = $this->baseQuery()
            ->where('status', 'done')
            ->latest('updated_at')
            ->limit(20)
            ->with(['project', 'assignees'])
            ->get();

        $priorities = Priority::forCompany($this->companyId());

        return view('company.my-tasks.index', compact('today', 'overdue', 'upcoming', 'done', 'priorities', 'slug'));
    }

    public function store(Request $request, string $slug)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'due_date'   => 'nullable|date',
            'priority'   => 'required|' . $this->priorityRule(),
            'project_id' => 'nullable|exists:projects,id',
            'notes'      => 'nullable|string',
        ]);

        if (!empty($data['project_id'])) {
            abort_if(
                !Project::where('id', $data['project_id'])->where('company_id', $this->companyId())->exists(),
                403
            );
        }

        $task = Task::create([
            'title'       => $data['title'],
            'description' => $data['notes'] ?? null,
            'due_date'    => $data['due_date'] ?? null,
            'priority'    => $data['priority'],
            'project_id'  => $data['project_id'] ?? null,
            'status'      => 'todo',
            'company_id'  => $this->companyId(),
            'created_by'  => auth()->id(),
            'assigned_to' => auth()->id(),
        ]);

        $task->assignees()->attach(auth()->id());

        return back()->with('success', 'Task created.');
    }

    public function updateStatus(Request $request, string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        $request->validate(['status' => 'required|in:todo,in_progress,in_review,done']);
        $task->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function destroy(string $slug, Task $task)
    {
        abort_if($task->company_id !== $this->companyId(), 403);
        abort_if($task->created_by !== auth()->id(), 403);
        $task->delete();
        return back()->with('success', 'Task deleted.');
    }
}
