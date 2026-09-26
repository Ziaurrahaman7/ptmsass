<?php

namespace App\Http\Controllers;

use App\Models\ProjectForm;
use App\Models\Task;
use App\Services\WorkspaceNotifier;
use Illuminate\Http\Request;

class PublicFormController extends Controller
{
    public function show(string $token)
    {
        $form = ProjectForm::query()->where('token', $token)->where('is_active', true)->firstOrFail();

        return view('public.form', compact('form'));
    }

    public function submit(Request $request, string $token)
    {
        $form = ProjectForm::query()->where('token', $token)->where('is_active', true)->with('project')->firstOrFail();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|max:40',
        ]);

        $task = Task::create([
            'company_id' => $form->company_id,
            'project_id' => $form->project_id,
            'created_by' => $form->created_by,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'todo',
        ]);

        $owner = $form->project?->creator;
        if ($owner) {
            app(WorkspaceNotifier::class)->personal(
                $owner,
                'form_submitted',
                'Form submitted',
                $task->title.' via '.$form->name,
                '/'.$owner->company?->slug.'/admin/tasks/'.$task->id
            );
        }

        return back()->with('success', 'Request submitted.');
    }
}
