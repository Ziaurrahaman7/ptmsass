<x-employee-layout title="Intake forms">
    @include('partials.intake-forms-manager', [
        'forms' => $forms,
        'projects' => $projects,
        'slug' => $slug,
        'storeRoute' => route('employee.forms.store', $slug),
    ])
</x-employee-layout>
