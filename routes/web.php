<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Superadmin\CompanyController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\ProjectController as CompanyProjectController;
use App\Http\Controllers\Company\TaskController as CompanyTaskController;
use App\Http\Controllers\Company\MemberController as CompanyMemberController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
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
    Route::resource('projects', CompanyProjectController::class);
    
    // Tasks routes - MUST be before generic routes
    Route::get('tasks', [CompanyTaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [CompanyTaskController::class, 'storeFromIndex'])->name('tasks.store_index');
    Route::get('tasks/{task}', [CompanyTaskController::class, 'show'])->name('tasks.show');
    Route::patch('tasks/{task}/status', [CompanyTaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::put('tasks/{task}', [CompanyTaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [CompanyTaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('tasks/{task}/comments', [CompanyTaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::delete('tasks/comments/{comment}', [CompanyTaskController::class, 'destroyComment'])->name('tasks.comments.destroy');
    Route::post('tasks/{task}/attachments', [CompanyTaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('tasks/attachments/{attachment}', [CompanyTaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::post('projects/{project}/tasks', [CompanyTaskController::class, 'store'])->name('tasks.store');
    
    Route::get('members', [CompanyMemberController::class, 'index'])->name('members.index');
    Route::post('members', [CompanyMemberController::class, 'store'])->name('members.store');
    Route::patch('members/{user}/toggle', [CompanyMemberController::class, 'toggle'])->name('members.toggle');
});

// Employee routes — /{slug}/...
Route::prefix('{slug}')->name('employee.')->middleware(['auth', 'employee', 'company_slug'])->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [EmployeeTaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{task}/status', [EmployeeTaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/comments', [EmployeeTaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::delete('/tasks/comments/{comment}', [EmployeeTaskController::class, 'destroyComment'])->name('tasks.comments.destroy');
    Route::post('/tasks/{task}/attachments', [EmployeeTaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/attachments/{attachment}', [EmployeeTaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
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
