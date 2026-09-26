<?php

use App\Http\Controllers\Api\V1\TaskApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api_token')->prefix('v1')->group(function () {
    Route::get('tasks', [TaskApiController::class, 'index']);
    Route::post('tasks', [TaskApiController::class, 'store']);
});
