<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $companyId = auth()->user()->company_id;
        $like = '%' . $q . '%';

        $tasks = Task::where('company_id', $companyId)
            ->whereNull('parent_task_id')
            ->where(fn($query) => $query->where('title', 'like', $like)
                ->orWhere('description', 'like', $like))
            ->with('project')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'type'     => 'task',
                'id'       => $t->id,
                'title'    => $t->title,
                'subtitle' => $t->project?->name ?? 'Personal',
                'status'   => $t->status,
                'url'      => $t->project_id
                    ? route('company.tasks.show', [$slug, $t])
                    : route('company.my-tasks.index', $slug),
            ]);

        $projects = Project::where('company_id', $companyId)
            ->where('is_template', false)
            ->where(fn($query) => $query->where('name', 'like', $like)
                ->orWhere('description', 'like', $like))
            ->limit(4)
            ->get()
            ->map(fn($p) => [
                'type'     => 'project',
                'id'       => $p->id,
                'title'    => $p->name,
                'subtitle' => $p->status ?? 'active',
                'url'      => route('company.projects.show', [$slug, $p]),
            ]);

        $members = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(fn($query) => $query->where('name', 'like', $like)
                ->orWhere('email', 'like', $like))
            ->limit(3)
            ->get()
            ->map(fn($u) => [
                'type'     => 'member',
                'id'       => $u->id,
                'title'    => $u->name,
                'subtitle' => $u->email,
                'initial'  => strtoupper(substr($u->name, 0, 1)),
                'url'      => route('company.members.index', $slug),
            ]);

        return response()->json([
            'results' => [
                'tasks'    => $tasks,
                'projects' => $projects,
                'members'  => $members,
            ],
            'total' => $tasks->count() + $projects->count() + $members->count(),
        ]);
    }
}
