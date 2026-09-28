<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectForm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('form.manage'), 403);

        $companyId = (int) auth()->user()->company_id;
        $forms = ProjectForm::query()
            ->where('company_id', $companyId)
            ->with('project')
            ->latest()
            ->get();
        $projects = Project::query()
            ->where('company_id', $companyId)
            ->where('is_template', false)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employee.forms.index', compact('forms', 'projects', 'slug'));
    }

    public function store(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('form.manage'), 403);

        $companyId = (int) auth()->user()->company_id;
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
        ]);
        $project = Project::query()
            ->where('company_id', $companyId)
            ->findOrFail($data['project_id']);

        ProjectForm::create([
            'company_id' => $companyId,
            'project_id' => $project->id,
            'created_by' => auth()->id(),
            'name' => $data['name'],
            'token' => Str::random(40),
            'is_active' => true,
            'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'maps_to' => 'title'],
                ['key' => 'description', 'label' => 'Details', 'type' => 'textarea', 'maps_to' => 'description'],
                ['key' => 'due_date', 'label' => 'Due date', 'type' => 'date', 'maps_to' => 'due_date'],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'text', 'maps_to' => 'priority'],
            ],
        ]);

        return back()->with('success', 'Intake form created. Share the public link below.');
    }
}
