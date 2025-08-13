<?php

use App\Http\Controllers\Task\Api\TasksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/tasks')
    ->name('tasks.')
    ->middleware('auth:sanctum')
    ->controller(TasksController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::get('{taskId}', 'show')->name('show');
        Route::patch('{taskId}', 'update')->name('update');
        Route::delete('{taskId}', 'destroy')->name('destroy');

        // Soft delete operations
        Route::post('{taskId}/restore', 'restore')->name('restore');
        Route::delete('{taskId}/force-delete', 'forceDelete')->name('forceDelete');

        // Task-specific operations
        Route::post('{taskId}/archive', 'archive')->name('archive');
        Route::post('{taskId}/mark-completed', 'markAsCompleted')->name('markAsCompleted');
        Route::post('{taskId}/assign', 'assignTo')->name('assignTo');
        Route::post('{taskId}/priority', 'updatePriority')->name('updatePriority');

        // Bulk operations
        Route::post('bulk-delete', 'bulkDelete')->name('bulkDelete');
        Route::post('bulk-restore', 'bulkRestore')->name('bulkRestore');
        Route::post('bulk-force-delete', 'bulkForceDelete')->name('bulkForceDelete');
        Route::post('bulk-archive', 'bulkArchive')->name('bulkArchive');

        // Export and utilities
        Route::post('export', 'export')->name('export');
        Route::get('stats', 'stats')->name('stats');
        Route::get('statuses', 'getStatuses')->name('statuses');
        Route::get('priorities', 'getPriorities')->name('priorities');

        // Deleted tasks
        Route::get('deleted', 'getDeletedTasks')->name('deleted');
    });
