<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Superadmin\CompanyController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\ProjectController as CompanyProjectController;
use App\Http\Controllers\Company\CustomFieldController as CompanyCustomFieldController;
use App\Http\Controllers\Company\SectionController as CompanySectionController;
use App\Http\Controllers\Company\TaskController as CompanyTaskController;
use App\Http\Controllers\Company\MemberController as CompanyMemberController;
use App\Http\Controllers\Company\TeamController as CompanyTeamController;
use App\Http\Controllers\Company\TeamFieldController as CompanyTeamFieldController;
use App\Http\Controllers\Company\NotificationController as CompanyNotificationController;
use App\Http\Controllers\Company\InsightController as CompanyInsightController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\ProjectController as EmployeeProjectController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
use App\Http\Controllers\Employee\NotificationController as EmployeeNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));


// Superadmin routes
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('companies', CompanyController::class)->except(['show']);
    Route::patch('companies/{company}/toggle', [CompanyController::class, 'toggleStatus'])->name('companies.toggle');
});

// Company Admin routes — /{slug}/admin/...
Route::prefix('{slug}/admin')->name('company.')->middleware(['auth', 'company_admin', 'company_slug'])->group(function () {
    Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');
    
    // Tasks POST routes FIRST
    Route::post('tasks', [CompanyTaskController::class, 'storeFromIndex'])->name('tasks.store_index');
    Route::post('projects/{project}/tasks', [CompanyTaskController::class, 'store'])->name('tasks.store');
    Route::post('projects/{project}/tasks/quick', [CompanyTaskController::class, 'quickStore'])->name('tasks.quick_store');
    Route::post('projects/{project}/tasks/reorder', [CompanyTaskController::class, 'reorder'])->name('tasks.reorder');

    // Custom fields
    Route::post('projects/{project}/custom-fields', [CompanyCustomFieldController::class, 'store'])->name('custom_fields.store');
    Route::delete('custom-fields/{customField}', [CompanyCustomFieldController::class, 'destroy'])->name('custom_fields.destroy');
    Route::patch('tasks/{task}/custom', [CompanyTaskController::class, 'setCustomValue'])->name('tasks.custom');

    // Sections
    Route::post('projects/{project}/sections', [CompanySectionController::class, 'store'])->name('sections.store');
    Route::patch('sections/{section}', [CompanySectionController::class, 'update'])->name('sections.update');
    Route::post('sections/{section}/duplicate', [CompanySectionController::class, 'duplicate'])->name('sections.duplicate');
    Route::delete('sections/{section}', [CompanySectionController::class, 'destroy'])->name('sections.destroy');
    Route::post('tasks/{task}/comments', [CompanyTaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('tasks/{task}/attachments', [CompanyTaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::post('tasks/{task}/subtasks', [CompanyTaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');
    
    // Projects resource
    Route::patch('projects/{project}/goal', [CompanyProjectController::class, 'updateGoal'])->name('projects.goal');
    Route::resource('projects', CompanyProjectController::class);
    
    // Tasks GET/PUT/PATCH/DELETE routes
    Route::get('tasks', [CompanyTaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/{task}', [CompanyTaskController::class, 'show'])->name('tasks.show');
    Route::get('tasks/{task}/panel', [CompanyTaskController::class, 'panel'])->name('tasks.panel');
    Route::patch('tasks/{task}/status', [CompanyTaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::patch('tasks/{task}/inline', [CompanyTaskController::class, 'inlineUpdate'])->name('tasks.inline');
    Route::put('tasks/{task}', [CompanyTaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [CompanyTaskController::class, 'destroy'])->name('tasks.destroy');
    Route::delete('tasks/comments/{comment}', [CompanyTaskController::class, 'destroyComment'])->name('tasks.comments.destroy');
    Route::delete('tasks/attachments/{attachment}', [CompanyTaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    
    Route::get('members', [CompanyMemberController::class, 'index'])->name('members.index');
    Route::post('members', [CompanyMemberController::class, 'store'])->name('members.store');
    Route::patch('members/{user}/toggle', [CompanyMemberController::class, 'toggle'])->name('members.toggle');

    // Team
    Route::post('teams', [CompanyTeamController::class, 'store'])->name('teams.store');
    Route::get('teams/{team}', [CompanyTeamController::class, 'overview'])->name('team.overview');
    Route::put('teams/{team}', [CompanyTeamController::class, 'update'])->name('teams.update');
    Route::post('teams/{team}/members', [CompanyTeamController::class, 'addMembers'])->name('teams.members.add');
    Route::patch('teams/{team}/members/{user}/title', [CompanyTeamController::class, 'updateMemberTitle'])->name('teams.members.title');
    Route::delete('teams/{team}/members/{user}', [CompanyTeamController::class, 'removeMember'])->name('teams.members.remove');
    Route::post('teams/{team}/fields', [CompanyTeamFieldController::class, 'store'])->name('teams.fields.store');
    Route::delete('teams/{team}/fields/{field}', [CompanyTeamFieldController::class, 'destroy'])->name('teams.fields.destroy');
    Route::patch('teams/{team}/members/{user}/field', [CompanyTeamFieldController::class, 'setValue'])->name('teams.members.field');
    Route::post('teams/{team}/messages', [CompanyTeamController::class, 'storeMessage'])->name('teams.messages.store');
    Route::post('teams/{team}/docs', [CompanyTeamController::class, 'storeDoc'])->name('teams.docs.store');
    Route::delete('teams/{team}/docs/{doc}', [CompanyTeamController::class, 'destroyDoc'])->name('teams.docs.destroy');
    Route::post('teams/{team}/notes', [CompanyTeamController::class, 'storeNote'])->name('teams.notes.store');
    Route::patch('teams/{team}/notes/{note}', [CompanyTeamController::class, 'updateNote'])->name('teams.notes.update');
    Route::delete('teams/{team}/notes/{note}', [CompanyTeamController::class, 'destroyNote'])->name('teams.notes.destroy');
    Route::delete('teams/{team}', [CompanyTeamController::class, 'destroy'])->name('teams.destroy');

    // Notifications
    Route::get('notifications', [CompanyNotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread', [CompanyNotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('notifications/{notification}/read', [CompanyNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('notifications/mark-all-read', [CompanyNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Insights
    Route::get('insights', [CompanyInsightController::class, 'index'])->name('insights.index');
    Route::post('insights/dashboards', [CompanyInsightController::class, 'storeDashboard'])->name('insights.dashboards.store');
    Route::get('insights/dashboards/{dashboard}', [CompanyInsightController::class, 'showDashboard'])->name('insights.dashboards.show');
    Route::delete('insights/dashboards/{dashboard}', [CompanyInsightController::class, 'destroyDashboard'])->name('insights.dashboards.destroy');
    Route::get('insights/{type}', [CompanyInsightController::class, 'show'])->name('insights.show');
});

// Employee routes — /{slug}/...
Route::prefix('{slug}')->name('employee.')->middleware(['auth', 'employee', 'company_slug'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/projects/{project}', [EmployeeProjectController::class, 'show'])->name('projects.show');
    Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [EmployeeTaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/panel', [EmployeeTaskController::class, 'panel'])->name('tasks.panel');
    Route::patch('/tasks/{task}/status', [EmployeeTaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/comments', [EmployeeTaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::delete('/tasks/comments/{comment}', [EmployeeTaskController::class, 'destroyComment'])->name('tasks.comments.destroy');
    Route::post('/tasks/{task}/attachments', [EmployeeTaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/attachments/{attachment}', [EmployeeTaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    
    // Notifications
    Route::get('/notifications', [EmployeeNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [EmployeeNotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/notifications/{notification}/read', [EmployeeNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [EmployeeNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

// Dashboard redirect
Route::get('/dashboard', function () {
    $user = auth()->user();
    $slug = $user->company?->slug;
    if ($user->isSuperAdmin()) return redirect()->route('superadmin.dashboard');
    if ($user->isCompanyAdmin()) return redirect()->route('company.dashboard', $slug);
    if ($user->isEmployee()) return redirect()->route('employee.dashboard', $slug);
    abort(403);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
