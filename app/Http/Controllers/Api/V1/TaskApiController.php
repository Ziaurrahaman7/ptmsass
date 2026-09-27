<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = Task::query()
            ->where('company_id', $user->company_id)
            ->latest()
            ->limit(100)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'project_id']);

        return response()->json(['data' => $tasks]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|in:todo,in_progress,in_review,done',
        ]);

        if (! empty($data['project_id'])) {
            $project = Project::query()
                ->where('company_id', $user->company_id)
                ->findOrFail($data['project_id']);
            $this->authorize('createTasks', $project);
        } else {
            $this->authorize('create', Task::class);
        }

        $task = Task::create([
            'company_id' => $user->company_id,
            'project_id' => $data['project_id'] ?? null,
            'created_by' => $user->id,
            'title' => $data['title'],
            'status' => $data['status'] ?? 'todo',
        ]);

        return response()->json(['data' => $task], 201);
    }
}
