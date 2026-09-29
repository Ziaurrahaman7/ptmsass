<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Support\TimeFormat;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('time.track'), 403);

        $userId = auth()->id();
        $statsBase = TimeEntry::query()->where('user_id', $userId)->finished();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $stats = [
            'today_minutes' => (int) TimeEntry::query()->where('user_id', $userId)->finished()->whereDate('worked_on', today())->sum('minutes'),
            'week_minutes' => (int) (clone $statsBase)->whereBetween('worked_on', [$weekStart->toDateString(), $weekEnd->toDateString()])->sum('minutes'),
            'week_billable' => (int) (clone $statsBase)->whereBetween('worked_on', [$weekStart->toDateString(), $weekEnd->toDateString()])->where('billable', true)->sum('minutes'),
            'pending_count' => TimeEntry::query()->where('user_id', $userId)->where('status', 'pending')->count(),
        ];

        $weekDays = collect();
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $date = $d->toDateString();
            $weekDays->push([
                'date' => $date,
                'label' => $d->format('D'),
                'minutes' => (int) TimeEntry::query()->where('user_id', $userId)->finished()->whereDate('worked_on', $date)->sum('minutes'),
            ]);
        }

        $runningTimer = TimeEntry::runningForUser($userId)?->load('task.project');

        $entries = TimeEntry::query()
            ->where('user_id', $userId)
            ->where('status', '!=', 'running')
            ->with(['task.project'])
            ->latest('worked_on')
            ->latest('id')
            ->paginate(20);
        $user = auth()->user();
        $tasksQuery = Task::query()
            ->where('company_id', $user->company_id)
            ->whereNull('parent_task_id');
        if (! $user->isCompanyAdmin()) {
            $tasksQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereHas('assignees', fn ($a) => $a->where('users.id', $user->id))
                    ->orWhere('created_by', $user->id);
            });
        }
        $tasks = $tasksQuery->orderBy('title')->limit($user->isCompanyAdmin() ? 200 : 80)->get();

        $preselectedTaskId = $request->integer('task_id') ?: null;

        $routeGroup = $user->isCompanyAdmin() ? 'company' : 'employee';
        $timeStoreRoute = route("{$routeGroup}.time.store", $slug);
        $timerStartUrl = route("{$routeGroup}.time.timer.start", $slug);
        $timerStopUrl = route("{$routeGroup}.time.timer.stop", $slug);
        $view = $user->isCompanyAdmin() ? 'company.time.index' : 'employee.time.index';

        return view($view, compact(
            'entries', 'tasks', 'stats', 'weekDays', 'runningTimer', 'preselectedTaskId', 'slug',
            'timeStoreRoute', 'timerStartUrl', 'timerStopUrl'
        ));
    }

    public function startTimer(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('time.track'), 403);

        $data = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'billable' => 'nullable|boolean',
            'note' => 'nullable|string|max:500',
        ]);

        $userId = auth()->id();
        $existing = TimeEntry::runningForUser($userId);
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Stop your running timer first.'], 422);
        }

        $task = Task::query()->where('company_id', auth()->user()->company_id)->findOrFail($data['task_id']);
        $this->authorize('view', $task);

        $entry = TimeEntry::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => $userId,
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'minutes' => 0,
            'worked_on' => today(),
            'billable' => (bool) ($data['billable'] ?? false),
            'status' => 'running',
            'note' => $data['note'] ?? null,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'entry' => [
                'id' => $entry->id,
                'task_title' => $task->title,
                'started_at' => $entry->started_at?->toIso8601String(),
            ],
        ]);
    }

    public function stopTimer(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('time.track'), 403);

        $entry = TimeEntry::runningForUser(auth()->id());
        if (! $entry) {
            return response()->json(['success' => false, 'message' => 'No timer running.'], 422);
        }

        $minutes = max(1, (int) ceil($entry->started_at->diffInSeconds(now()) / 60));
        $entry->update([
            'minutes' => $minutes,
            'stopped_at' => now(),
            'status' => 'pending',
            'worked_on' => today(),
        ]);

        return response()->json([
            'success' => true,
            'minutes' => $minutes,
            'formatted' => TimeFormat::minutes($minutes),
        ]);
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
