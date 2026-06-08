<?php

// Temporary debug route - DELETE AFTER USE
Route::get('/debug-auth', function() {
    if (!auth()->check()) {
        return 'Not logged in';
    }
    
    $user = auth()->user();
    $company = $user->company;
    
    $projects = \App\Models\Project::where('id', 2)->get();
    
    return [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'user_role' => $user->role,
        'user_company_id' => $user->company_id,
        'company_slug' => $company?->slug,
        'company_name' => $company?->name,
        'project_2' => $projects->first() ? [
            'id' => $projects->first()->id,
            'name' => $projects->first()->name,
            'company_id' => $projects->first()->company_id,
        ] : 'Project not found',
        'all_user_projects' => \App\Models\Project::where('company_id', $user->company_id)->pluck('id', 'name'),
    ];
})->middleware('auth');
