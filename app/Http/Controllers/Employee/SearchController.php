<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\WorkspaceSearch;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $user = $request->user();

        $type = $request->query('type', 'all');
        $preset = $request->query('preset');

        $allowedTypes = ['all', 'tasks', 'projects', 'people', 'comments', 'more'];
        if (! is_string($type) || ! in_array($type, $allowedTypes, true)) {
            $type = 'all';
        }

        $allowedPresets = ['created', 'assigned', 'completed', 'deleted'];
        if (! is_string($preset) || ! in_array($preset, $allowedPresets, true)) {
            $preset = null;
        }

        $search = new WorkspaceSearch(
            slug: $slug,
            companyId: (int) $user->company_id,
            userId: (int) $user->id,
            isAdmin: false,
        );

        return response()->json($search->search(
            (string) $request->query('q', ''),
            $type,
            $preset,
        ));
    }
}
