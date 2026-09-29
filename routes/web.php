<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\Superadmin\CompanyController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Superadmin\MailSettingController;
use App\Http\Controllers\Superadmin\PusherSettingController;
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
use App\Http\Controllers\Company\PortfolioController as CompanyPortfolioController;
use App\Http\Controllers\Company\GoalController as CompanyGoalController;
use App\Http\Controllers\Company\PriorityController as CompanyPriorityController;
use App\Http\Controllers\Company\MyTaskController as CompanyMyTaskController;
use App\Http\Controllers\Company\SearchController as CompanySearchController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\ProjectController as EmployeeProjectController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
use App\Http\Controllers\Employee\MyTaskController as EmployeeMyTaskController;
use App\Http\Controllers\Employee\SearchController as EmployeeSearchController;
use App\Http\Controllers\Employee\NotificationController as EmployeeNotificationController;
use App\Http\Controllers\Company\RoleController as CompanyRoleController;
use App\Http\Controllers\Company\GapWorkspaceController as CompanyGapWorkspaceController;
use App\Http\Controllers\Employee\TeamAdminController as EmployeeTeamAdminController;
use App\Http\Controllers\Employee\TimeEntryController as EmployeeTimeEntryController;
use App\Http\Controllers\Employee\MemberController as EmployeeMemberController;
use App\Http\Controllers\Employee\FormController as EmployeeFormController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\ProjectController as ClientProjectController;
use App\Http\Controllers\Client\TaskActionController as ClientTaskActionController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\Superadmin\SecurityController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/cron/queue', function () {
    $token = (string) config('app.queue_cron_token');
    abort_if($token === '' || ! hash_equals($token, (string) request('token')), 403);

    \Illuminate\Support\Facades\Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--tries' => 3,
        '--max-time' => 120,
    ]);

    return response(trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'ok');
})->middleware('throttle:20,1');

Route::middleware('guest')->group(function () {
    Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.show');
    Route::post('/invite/{token}', [InviteController::class, 'store'])->name('invite.store');
});

// Superadmin routes
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('companies', CompanyController::class)->except(['show']);
    Route::patch('companies/{company}/toggle', [CompanyController::class, 'toggleStatus'])->name('companies.toggle');
    Route::get('smtp', [MailSettingController::class, 'edit'])->name('smtp.edit');
    Route::put('smtp', [MailSettingController::class, 'update'])->name('smtp.update');
    Route::post('smtp/test', [MailSettingController::class, 'test'])->name('smtp.test');
    Route::get('pusher', [PusherSettingController::class, 'edit'])->name('pusher.edit');
    Route::put('pusher', [PusherSettingController::class, 'update'])->name('pusher.update');
    Route::post('pusher/test', [PusherSettingController::class, 'test'])->name('pusher.test');
    Route::get('security', [SecurityController::class, 'edit'])->name('security.edit');
    Route::put('security', [SecurityController::class, 'update'])->name('security.update');
});

Route::get('/f/{token}', [PublicFormController::class, 'show'])->name('forms.public.show');
Route::post('/f/{token}', [PublicFormController::class, 'submit'])->name('forms.public.submit');

// Company Admin routes — /{slug}/admin/...
Route::prefix('{slug}/admin')->name('company.')->middleware(['auth', 'company_admin', 'company_slug', 'admin.permission'])->group(function () {
    Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');
    
    // Search
    Route::get('search', CompanySearchController::class)->name('search');

    // My Tasks
    Route::get('my-tasks', [CompanyMyTaskController::class, 'index'])->name('my-tasks.index');
    Route::post('my-tasks', [CompanyMyTaskController::class, 'store'])->name('my-tasks.store');
    Route::patch('my-tasks/{task}/status', [CompanyMyTaskController::class, 'updateStatus'])->name('my-tasks.status');
    Route::patch('my-tasks/{task}/move', [CompanyMyTaskController::class, 'move'])->name('my-tasks.move');
    Route::delete('my-tasks/{task}', [CompanyMyTaskController::class, 'destroy'])->name('my-tasks.destroy');

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
    Route::post('tasks/{task}/followers', [CompanyTaskController::class, 'storeFollower'])->name('tasks.followers.store');
    Route::delete('tasks/{task}/followers/{user}', [CompanyTaskController::class, 'destroyFollower'])->name('tasks.followers.destroy');
    Route::post('tasks/{task}/attachments', [CompanyTaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::post('tasks/{task}/subtasks', [CompanyTaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');
    
    // Projects resource
    Route::patch('projects/{project}/goal', [CompanyProjectController::class, 'updateGoal'])->name('projects.goal');
    Route::patch('projects/{project}/color-icon', [CompanyProjectController::class, 'updateColorIcon'])->name('projects.color-icon');
    Route::post('projects/{project}/favorite', [CompanyProjectController::class, 'toggleFavorite'])->name('projects.favorite');
    Route::post('projects/{project}/duplicate', [CompanyProjectController::class, 'duplicateProject'])->name('projects.duplicate');
    Route::post('projects/{project}/save-as-template', [CompanyProjectController::class, 'saveAsTemplate'])->name('projects.save-as-template');
    Route::get('projects/{project}/export', [CompanyProjectController::class, 'exportTasksCsv'])->name('projects.export');
    Route::get('projects/{project}/exports/{token}', [CompanyProjectController::class, 'downloadExport'])->name('projects.export.download');
    Route::post('projects/{project}/import', [CompanyProjectController::class, 'importTasksCsv'])->name('projects.import');
    Route::post('projects/{project}/status-updates', [CompanyProjectController::class, 'storeStatusUpdate'])->name('projects.status-updates.store');
    Route::post('projects/{project}/members', [CompanyProjectController::class, 'addMember'])->name('projects.members.add');
    Route::delete('projects/{project}/members/{user}', [CompanyProjectController::class, 'removeMember'])->name('projects.members.remove');
    Route::post('projects/{project}/clients', [CompanyProjectController::class, 'addClient'])->name('projects.clients.add');
    Route::patch('projects/{project}/clients/{user}', [CompanyProjectController::class, 'updateClientAccess'])->name('projects.clients.update');
    Route::delete('projects/{project}/clients/{user}', [CompanyProjectController::class, 'removeClient'])->name('projects.clients.remove');
    Route::post('projects/{project}/resources', [CompanyProjectController::class, 'storeResource'])->name('projects.resources.store');
    Route::delete('projects/{project}/resources/{resource}', [CompanyProjectController::class, 'destroyResource'])->name('projects.resources.destroy');
    Route::post('projects/{project}/messages', [CompanyProjectController::class, 'storeMessage'])->name('projects.messages.store');
    Route::post('projects/{project}/milestones', [CompanyProjectController::class, 'storeMilestone'])->name('projects.milestones.store');
    Route::patch('projects/{project}/milestones/{task}/toggle', [CompanyProjectController::class, 'toggleMilestone'])->name('projects.milestones.toggle');
    Route::resource('projects', CompanyProjectController::class);
    
    // Tasks GET/PUT/PATCH/DELETE routes
    Route::get('tasks', [CompanyTaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/{task}', [CompanyTaskController::class, 'show'])->name('tasks.show');
    Route::get('tasks/{task}/panel', [CompanyTaskController::class, 'panel'])->name('tasks.panel');
    Route::patch('tasks/{task}/status', [CompanyTaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::patch('tasks/{task}/inline', [CompanyTaskController::class, 'inlineUpdate'])->name('tasks.inline');
    Route::post('tasks/{task}/dependencies', [CompanyTaskController::class, 'storeDependency'])->name('tasks.dependencies.store');
    Route::delete('tasks/{task}/dependencies/{dependency}', [CompanyTaskController::class, 'destroyDependency'])->name('tasks.dependencies.destroy');
    Route::put('tasks/{task}', [CompanyTaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [CompanyTaskController::class, 'destroy'])->name('tasks.destroy');
    Route::delete('tasks/comments/{comment}', [CompanyTaskController::class, 'destroyComment'])->name('tasks.comments.destroy');
    Route::delete('tasks/attachments/{attachment}', [CompanyTaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    
    Route::post('tasks/{task}/projects', [CompanyTaskController::class, 'attachProject'])->name('tasks.projects.attach');
    Route::delete('tasks/{task}/projects/{project}', [CompanyTaskController::class, 'detachProject'])->name('tasks.projects.detach');
    Route::post('tasks/{task}/approvals', [CompanyTaskController::class, 'requestApproval'])->name('tasks.approvals.store');
    Route::patch('tasks/{task}/approvals/{approval}', [CompanyTaskController::class, 'decideApproval'])->name('tasks.approvals.decide');
    Route::patch('tasks/{task}/recurrence', [CompanyTaskController::class, 'setRecurrence'])->name('tasks.recurrence');

    Route::get('roles', [CompanyRoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [CompanyRoleController::class, 'store'])->name('roles.store');
    Route::put('roles/{role}', [CompanyRoleController::class, 'update'])->name('roles.update');
    Route::post('roles/{role}/assign', [CompanyRoleController::class, 'assign'])->name('roles.assign');
    Route::delete('roles/{role}/users/{user}', [CompanyRoleController::class, 'unassign'])->name('roles.unassign');

    Route::get('templates', [CompanyGapWorkspaceController::class, 'templates'])->name('templates.index');
    Route::post('templates', [CompanyGapWorkspaceController::class, 'storeTemplate'])->name('templates.store');
    Route::post('templates/{template}/apply', [CompanyGapWorkspaceController::class, 'applyTemplate'])->name('templates.apply');
    Route::get('forms', [CompanyGapWorkspaceController::class, 'forms'])->name('forms.index');
    Route::post('forms', [CompanyGapWorkspaceController::class, 'storeForm'])->name('forms.store');
    Route::get('rules', [CompanyGapWorkspaceController::class, 'rules'])->name('rules.index');
    Route::post('rules', [CompanyGapWorkspaceController::class, 'storeRule'])->name('rules.store');
    Route::get('time', [EmployeeTimeEntryController::class, 'index'])->name('time.index');
    Route::post('time', [EmployeeTimeEntryController::class, 'store'])->name('time.store');
    Route::post('time/timer/start', [EmployeeTimeEntryController::class, 'startTimer'])->name('time.timer.start');
    Route::post('time/timer/stop', [EmployeeTimeEntryController::class, 'stopTimer'])->name('time.timer.stop');
    Route::get('timesheets', [CompanyGapWorkspaceController::class, 'timesheets'])->name('timesheets.index');
    Route::post('timesheets/{time_entry}/review', [CompanyGapWorkspaceController::class, 'reviewTime'])->name('timesheets.review');
    Route::get('capacity', [CompanyGapWorkspaceController::class, 'capacity'])->name('capacity.index');
    Route::post('capacity/{user}', [CompanyGapWorkspaceController::class, 'updateCapacity'])->name('capacity.update');
    Route::get('reports', [CompanyGapWorkspaceController::class, 'reports'])->name('reports.index');
    Route::get('integrations', [CompanyGapWorkspaceController::class, 'integrations'])->name('integrations.index');
    Route::post('integrations/tokens', [CompanyGapWorkspaceController::class, 'storeToken'])->name('integrations.tokens.store');
    Route::post('integrations/webhooks', [CompanyGapWorkspaceController::class, 'storeWebhook'])->name('integrations.webhooks.store');
    Route::get('security', [CompanyGapWorkspaceController::class, 'security'])->name('security.edit');
    Route::post('security', [CompanyGapWorkspaceController::class, 'updateSecurity'])->name('security.update');

    Route::get('members', [CompanyMemberController::class, 'index'])->name('members.index');
    Route::post('members', [CompanyMemberController::class, 'store'])->name('members.store');
    Route::post('members/invitations/{invitation}/resend', [CompanyMemberController::class, 'resend'])->name('members.invitations.resend');
    Route::delete('members/invitations/{invitation}', [CompanyMemberController::class, 'revoke'])->name('members.invitations.revoke');
    Route::patch('members/{user}/toggle', [CompanyMemberController::class, 'toggle'])->name('members.toggle');

    // Team
    Route::post('teams', [CompanyTeamController::class, 'store'])->name('teams.store');
    Route::get('teams/{team}', [CompanyTeamController::class, 'overview'])->name('team.overview');
    Route::put('teams/{team}', [CompanyTeamController::class, 'update'])->name('teams.update');
    Route::post('teams/{team}/members', [CompanyTeamController::class, 'addMembers'])->name('teams.members.add');
    Route::patch('teams/{team}/members/{user}/title', [CompanyTeamController::class, 'updateMemberTitle'])->name('teams.members.title');
    Route::patch('teams/{team}/members/{user}/role', [CompanyTeamController::class, 'updateMemberRole'])->name('teams.members.role');
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
    Route::post('insights/chart-preview', [CompanyInsightController::class, 'chartPreview'])->name('insights.chart-preview');
    Route::get('insights/dashboards/{dashboard}', [CompanyInsightController::class, 'showDashboard'])->name('insights.dashboards.show');
    Route::delete('insights/dashboards/{dashboard}', [CompanyInsightController::class, 'destroyDashboard'])->name('insights.dashboards.destroy');
    Route::patch('insights/dashboards/{dashboard}', [CompanyInsightController::class, 'updateDashboard'])->name('insights.dashboards.update');
    Route::post('insights/dashboards/{dashboard}/favorite', [CompanyInsightController::class, 'toggleFavoriteDashboard'])->name('insights.dashboards.favorite');
    Route::post('insights/dashboards/{dashboard}/duplicate', [CompanyInsightController::class, 'duplicateDashboard'])->name('insights.dashboards.duplicate');
    Route::post('insights/dashboard-prefs', [CompanyInsightController::class, 'updateDashboardPref'])->name('insights.prefs.update');
    Route::post('insights/dashboard-prefs/favorite', [CompanyInsightController::class, 'toggleFavoritePref'])->name('insights.prefs.favorite');
    Route::post('insights/dashboard-prefs/hide', [CompanyInsightController::class, 'hideDashboardPref'])->name('insights.prefs.hide');
    Route::post('insights/dashboard-prefs/duplicate', [CompanyInsightController::class, 'duplicateBuiltin'])->name('insights.prefs.duplicate');
    Route::post('insights/dashboards/{dashboard}/widgets', [CompanyInsightController::class, 'storeWidget'])->name('insights.widgets.store');
    Route::delete('insights/dashboards/{dashboard}/widgets/{widget}', [CompanyInsightController::class, 'destroyWidget'])->name('insights.widgets.destroy');
    Route::get('insights/{type}', [CompanyInsightController::class, 'show'])->name('insights.show');

    // Portfolios
    Route::get('portfolios', [CompanyPortfolioController::class, 'index'])->name('portfolios.index');
    Route::post('portfolios', [CompanyPortfolioController::class, 'store'])->name('portfolios.store');
    Route::get('portfolios/{portfolio}', [CompanyPortfolioController::class, 'show'])->name('portfolios.show');
    Route::patch('portfolios/{portfolio}', [CompanyPortfolioController::class, 'update'])->name('portfolios.update');
    Route::delete('portfolios/{portfolio}', [CompanyPortfolioController::class, 'destroy'])->name('portfolios.destroy');
    Route::post('portfolios/{portfolio}/projects', [CompanyPortfolioController::class, 'addProject'])->name('portfolios.projects.add');
    Route::delete('portfolios/{portfolio}/projects/{project}', [CompanyPortfolioController::class, 'removeProject'])->name('portfolios.projects.remove');

    // Goals
    Route::get('goals', [CompanyGoalController::class, 'index'])->name('goals.index');
    Route::post('goals', [CompanyGoalController::class, 'store'])->name('goals.store');
    Route::patch('goals/{goal}', [CompanyGoalController::class, 'update'])->name('goals.update');
    Route::delete('goals/{goal}', [CompanyGoalController::class, 'destroy'])->name('goals.destroy');

    // Priorities
    Route::get('priorities', [CompanyPriorityController::class, 'index'])->name('priorities.index');
    Route::post('priorities', [CompanyPriorityController::class, 'store'])->name('priorities.store');
    Route::patch('priorities/reorder', [CompanyPriorityController::class, 'reorder'])->name('priorities.reorder');
    Route::patch('priorities/{priority}', [CompanyPriorityController::class, 'update'])->name('priorities.update');
    Route::delete('priorities/{priority}', [CompanyPriorityController::class, 'destroy'])->name('priorities.destroy');
});

// Employee routes — /{slug}/...
Route::prefix('{slug}')->name('employee.')->middleware(['auth', 'employee', 'company_slug'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', EmployeeSearchController::class)->name('search');
    Route::get('/projects/create', [EmployeeProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [EmployeeProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [EmployeeProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/tasks', [EmployeeProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::get('/members', [EmployeeMemberController::class, 'index'])->name('members.index');
    Route::post('/members', [EmployeeMemberController::class, 'store'])->name('members.store');
    Route::get('/my-tasks', [EmployeeMyTaskController::class, 'index'])->name('my-tasks.index');
    Route::post('/my-tasks', [EmployeeMyTaskController::class, 'store'])->name('my-tasks.store');
    Route::patch('/my-tasks/{task}/status', [EmployeeMyTaskController::class, 'updateStatus'])->name('my-tasks.status');
    Route::patch('/my-tasks/{task}/move', [EmployeeMyTaskController::class, 'move'])->name('my-tasks.move');
    Route::delete('/my-tasks/{task}', [EmployeeMyTaskController::class, 'destroy'])->name('my-tasks.destroy');
    Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [EmployeeTaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/panel', [EmployeeTaskController::class, 'panel'])->name('tasks.panel');
    Route::patch('/tasks/{task}/status', [EmployeeTaskController::class, 'updateStatus'])->name('tasks.status');
    Route::patch('/tasks/{task}/inline', [EmployeeTaskController::class, 'inlineUpdate'])->name('tasks.inline');
    Route::post('/tasks/{task}/subtasks', [EmployeeTaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');
    Route::post('/tasks/{task}/comments', [EmployeeTaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/followers', [EmployeeTaskController::class, 'storeFollower'])->name('tasks.followers.store');
    Route::delete('/tasks/{task}/followers/{user}', [EmployeeTaskController::class, 'destroyFollower'])->name('tasks.followers.destroy');
    Route::delete('/tasks/comments/{comment}', [EmployeeTaskController::class, 'destroyComment'])->name('tasks.comments.destroy');
    Route::post('/tasks/{task}/attachments', [EmployeeTaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/attachments/{attachment}', [EmployeeTaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::post('/tasks/{task}/projects', [EmployeeTaskController::class, 'attachProject'])->name('tasks.projects.attach');
    Route::delete('/tasks/{task}/projects/{project}', [EmployeeTaskController::class, 'detachProject'])->name('tasks.projects.detach');

    // Notifications
    Route::get('/notifications', [EmployeeNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/teams', [EmployeeTeamAdminController::class, 'index'])->name('teams.index');
    Route::get('/teams/{team}', [EmployeeTeamAdminController::class, 'show'])->name('teams.show');
    Route::put('/teams/{team}', [EmployeeTeamAdminController::class, 'update'])->name('teams.update');
    Route::post('/teams/{team}/members', [EmployeeTeamAdminController::class, 'addMember'])->name('teams.members.add');
    Route::patch('/teams/{team}/members/{user}/role', [EmployeeTeamAdminController::class, 'updateMemberRole'])->name('teams.members.role');
    Route::delete('/teams/{team}/members/{user}', [EmployeeTeamAdminController::class, 'removeMember'])->name('teams.members.remove');
    Route::get('/time', [EmployeeTimeEntryController::class, 'index'])->name('time.index');
    Route::post('/time', [EmployeeTimeEntryController::class, 'store'])->name('time.store');
    Route::post('/time/timer/start', [EmployeeTimeEntryController::class, 'startTimer'])->name('time.timer.start');
    Route::post('/time/timer/stop', [EmployeeTimeEntryController::class, 'stopTimer'])->name('time.timer.stop');
    Route::get('/forms', [EmployeeFormController::class, 'index'])->name('forms.index');
    Route::post('/forms', [EmployeeFormController::class, 'store'])->name('forms.store');

    Route::get('/notifications/unread', [EmployeeNotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/notifications/{notification}/read', [EmployeeNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [EmployeeNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

Route::prefix('{slug}/client')->name('client.')->middleware(['auth', 'client', 'company_slug'])->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/projects/{project}', [ClientProjectController::class, 'show'])->name('projects.show');
    Route::post('/tasks/{task}/comments', [ClientTaskActionController::class, 'comment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/attachments', [ClientTaskActionController::class, 'attach'])->name('tasks.attachments.store');
    Route::post('/tasks/{task}/follow', [ClientTaskActionController::class, 'follow'])->name('tasks.follow');
    Route::post('/tasks/{task}/status', [ClientTaskActionController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/approvals/{approval}', [ClientTaskActionController::class, 'decide'])->name('tasks.approvals.decide');
    Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [ClientNotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/notifications/{notification}/read', [ClientNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [ClientNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

// Dashboard redirect
Route::get('/dashboard', function () {
    $user = auth()->user();
    $slug = $user->company?->slug;
    if ($user->isSuperAdmin()) return redirect()->route('superadmin.dashboard');
    if ($user->isCompanyAdmin()) return redirect()->route('company.dashboard', $slug);
    if ($user->isEmployee()) return redirect()->route('employee.dashboard', $slug);
    if ($user->isClient()) return redirect()->route('client.dashboard', $slug);
    abort(403);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
