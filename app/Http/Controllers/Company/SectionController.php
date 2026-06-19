<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function store(Request $request, string $slug, Project $project)
    {
        abort_if($project->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Section::create([
            'company_id' => $this->companyId(),
            'project_id' => $project->id,
            'name'       => $data['name'],
            'position'   => (int) $project->sections()->max('position') + 1,
        ]);

        return back()->with('success', 'Section added.');
    }

    public function update(Request $request, string $slug, Section $section)
    {
        abort_if($section->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->update(['name' => $data['name']]);

        return back()->with('success', 'Section renamed.');
    }

    public function destroy(string $slug, Section $section)
    {
        abort_if($section->company_id !== $this->companyId(), 403);

        // Tasks keep existing; their section_id is set null by the FK (nullOnDelete).
        $section->delete();

        return back()->with('success', 'Section deleted.');
    }
}
