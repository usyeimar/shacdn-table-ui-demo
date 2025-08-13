<?php

use App\Http\Controllers\Task\Web\TaskController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');

    // Task routes
    Route::prefix('/tasks')->name('web.tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::get('/{taskId}', [TaskController::class, 'show'])->name('show');
        Route::get('/{taskId}/edit', [TaskController::class, 'edit'])->name('edit');
    });
});

Route::get('/welcome', [WebController::class, 'welcome'])->name('welcome');

// Include other route files
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
