<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Concerns\ManagesMyTasks;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class MyTaskController extends Controller
{
    use ManagesMyTasks;

    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function index(Request $request, string $slug)
    {
        $data = $this->myTasksIndexData($request);
        $userId = auth()->id();
        $projectIds = Task::query()
            ->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->orWhereHas('assignees', fn ($q) => $q->where('users.id', $userId));
            })
            ->whereNotNull('project_id')
            ->pluck('project_id');
        $data['projects'] = $data['projects']->whereIn('id', $projectIds)->values();

        return view('my-tasks.index', array_merge(
            $data,
            [
                'slug' => $slug,
                'ctx'  => [
                    'store'     => route('employee.my-tasks.store', $slug),
                    'destroy'   => fn (Task $t) => route('employee.my-tasks.destroy', [$slug, $t]),
                    'status'    => fn (int $id) => "/{$slug}/my-tasks/{$id}/status",
                    'move'      => fn (int $id) => "/{$slug}/my-tasks/{$id}/move",
                    'inline'    => fn (int $id) => "/{$slug}/tasks/{$id}/inline",
                    'listUrl'   => route('employee.my-tasks.index', $slug),
                    'canCreate' => true,
                    'canDeletePersonal' => true,
                ],
            ]
        ));
    }

    public function store(Request $request, string $slug)
    {
        $this->storeMyTask($request);
        return back()->with('success', 'Task created.');
    }

    public function updateStatus(Request $request, string $slug, Task $task)
    {
        $this->authorizeMine($task);
        $request->validate(['status' => 'required|in:todo,in_progress,in_review,done']);
        $task->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function move(Request $request, string $slug, Task $task)
    {
        $this->authorizeMine($task);
        $data = $request->validate([
            'group'    => 'required|in:recently,recent,overdue,today,upcoming,later,calendar',
            'due_date' => 'nullable|date',
        ]);
        $this->applyMyTaskMove($task, $data['group'], $data['due_date'] ?? null);
        return response()->json(['success' => true]);
    }

    public function destroy(string $slug, Task $task)
    {
        $this->authorizeMine($task);
        abort_if($task->created_by !== auth()->id() || $task->project_id !== null, 403);
        $task->delete();
        return back()->with('success', 'Task deleted.');
    }
}
