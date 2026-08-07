<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Models\Task;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    private function authorizePriority(Priority $priority): void
    {
        abort_if($priority->company_id !== $this->companyId(), 403);
    }

    public function index(string $slug)
    {
        $companyId = $this->companyId();
        $priorities = Priority::forCompany($companyId)
            ->map(function ($p) use ($companyId) {
                $p->tasks_count = Task::where('company_id', $companyId)->where('priority', $p->slug)->count();
                return $p;
            });

        return view('company.priorities.index', compact('slug', 'priorities'));
    }

    public function store(Request $request, string $slug)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:60',
            'color' => 'required|string|max:20',
        ]);

        $companyId = $this->companyId();

        $priority = Priority::create([
            'company_id' => $companyId,
            'name'       => $data['name'],
            'slug'       => Priority::uniqueSlug($companyId, $data['name']),
            'color'      => $data['color'],
            'position'   => Priority::where('company_id', $companyId)->count(),
            'is_default' => false,
        ]);

        return response()->json(['success' => true, 'priority' => $priority]);
    }

    public function update(Request $request, string $slug, Priority $priority)
    {
        $this->authorizePriority($priority);

        $data = $request->validate([
            'name'       => 'sometimes|required|string|max:60',
            'color'      => 'sometimes|required|string|max:20',
            'is_default' => 'sometimes|boolean',
        ]);

        if (!empty($data['is_default'])) {
            // Only one priority can be the default per company.
            Priority::where('company_id', $priority->company_id)->where('id', '!=', $priority->id)->update(['is_default' => false]);
        }

        $priority->update($data);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, string $slug)
    {
        $data = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:priorities,id',
        ]);

        $companyId = $this->companyId();
        foreach ($data['order'] as $position => $id) {
            Priority::where('company_id', $companyId)->where('id', $id)->update(['position' => $position]);
        }

        return response()->json(['success' => true]);
    }

    // Deleting a priority reassigns any tasks using it to the company's default priority — nothing is left orphaned.
    public function destroy(string $slug, Priority $priority)
    {
        $this->authorizePriority($priority);

        $companyId = $priority->company_id;
        $remaining = Priority::where('company_id', $companyId)->count();
        abort_if($remaining <= 1, 422, 'A company must keep at least one priority.');

        $fallback = Priority::where('company_id', $companyId)->where('id', '!=', $priority->id)
            ->orderByDesc('is_default')->orderBy('position')->first();

        Task::where('company_id', $companyId)->where('priority', $priority->slug)
            ->update(['priority' => $fallback->slug]);

        if ($priority->is_default && $fallback) {
            $fallback->update(['is_default' => true]);
        }

        $priority->delete();

        return response()->json(['success' => true]);
    }
}
