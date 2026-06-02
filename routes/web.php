<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Superadmin\CompanyController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\ProjectController as CompanyProjectController;
use App\Http\Controllers\Company\TaskController as CompanyTaskController;
use App\Http\Controllers\Company\MemberController as CompanyMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

// Superadmin routes
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('companies', CompanyController::class)->except(['show']);
    Route::patch('companies/{company}/toggle', [CompanyController::class, 'toggleStatus'])->name('companies.toggle');
});

// Company Admin routes
Route::prefix('company')->name('company.')->middleware(['auth', 'company_admin'])->group(function () {
    Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');
    Route::resource('projects', CompanyProjectController::class);
    Route::post('projects/{project}/tasks', [CompanyTaskController::class, 'store'])->name('tasks.store');
    Route::put('tasks/{task}', [CompanyTaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [CompanyTaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('tasks', [CompanyTaskController::class, 'index'])->name('tasks.index');
    Route::get('members', [CompanyMemberController::class, 'index'])->name('members.index');
    Route::post('members', [CompanyMemberController::class, 'store'])->name('members.store');
    Route::patch('members/{user}/toggle', [CompanyMemberController::class, 'toggle'])->name('members.toggle');
});

// Authenticated user dashboard redirect
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->isSuperAdmin()) return redirect()->route('superadmin.dashboard');
    if ($user->isCompanyAdmin()) return redirect()->route('company.dashboard');
    return redirect()->route('company.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
