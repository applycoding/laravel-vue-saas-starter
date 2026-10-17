<?php

use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'api.team'])->prefix('v1')->group(function () {
    Route::get('/team', [TeamController::class, 'show'])->name('api.team');
    Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('api.projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('api.projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');
});
