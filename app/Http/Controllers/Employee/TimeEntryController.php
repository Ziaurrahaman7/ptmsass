<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('time.track'), 403);

        $userId = auth()->id();
        $statsBase = TimeEntry::query()->where('user_id', $userId);
        $weekStart = now()->startOfWeek()->toDateString();

        $stats = [
            'today_minutes' => (int) (clone $statsBase)->whereDate('worked_on', today())->sum('minutes'),
            'week_minutes' => (int) (clone $statsBase)->where('worked_on', '>=', $weekStart)->sum('minutes'),
            'week_billable' => (int) (clone $statsBase)->where('worked_on', '>=', $weekStart)->where('billable', true)->sum('minutes'),
            'pending_count' => (clone $statsBase)->where('status', 'pending')->count(),
        ];

        $entries = TimeEntry::query()
            ->where('user_id', $userId)
            ->with(['task.project'])
            ->latest('worked_on')
            ->latest('id')
            ->paginate(20);
        $tasks = Task::query()
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($q) {
                $q->where('assigned_to', auth()->id())
                    ->orWhereHas('assignees', fn ($a) => $a->where('users.id', auth()->id()))
                    ->orWhere('created_by', auth()->id());
            })
            ->orderBy('title')
            ->limit(80)
            ->get();

        return view('employee.time.index', compact('entries', 'tasks', 'stats'));
    }

    public function store(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('time.track'), 403);
        $data = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'minutes' => 'required|integer|min:1|max:1440',
            'worked_on' => 'required|date',
            'billable' => 'nullable|boolean',
            'note' => 'nullable|string|max:500',
        ]);
        $task = null;
        if (! empty($data['task_id'])) {
            $task = Task::query()->where('company_id', auth()->user()->company_id)->findOrFail($data['task_id']);
            $this->authorize('view', $task);
        }

        TimeEntry::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'task_id' => $task?->id,
            'project_id' => $task?->project_id,
            'minutes' => $data['minutes'],
            'worked_on' => $data['worked_on'],
            'billable' => (bool) ($data['billable'] ?? false),
            'status' => 'pending',
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Time logged.');
    }
}
