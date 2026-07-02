<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function store(Request $request, string $slug, Project $project)
    {
        abort_if($project->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'name'    => 'required|string|max:60',
            'type'    => 'required|in:text,number,date,select',
            'options' => 'nullable|string', // comma-separated for select
        ]);

        $options = null;
        if ($data['type'] === 'select' && !empty($data['options'])) {
            $options = collect(explode(',', $data['options']))
                ->map(fn($o) => trim($o))->filter()->values()->all();
        }

        CustomField::create([
            'company_id' => $this->companyId(),
            'project_id' => $project->id,
            'name'       => $data['name'],
            'type'       => $data['type'],
            'options'    => $options,
            'position'   => (int) $project->customFields()->max('position') + 1,
        ]);

        return back()->with('success', 'Field added.');
    }

    public function destroy(string $slug, CustomField $customField)
    {
        abort_if($customField->company_id !== $this->companyId(), 403);

        $customField->delete();

        return back()->with('success', 'Field removed.');
    }
}
