<?php

use App\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/tasks', [TasksController::class, 'index'])->name('tasks');
    Route::get('/tasks/table', [TasksController::class, 'table'])->name('tasks.table');
    Route::post('/tasks/store', [TasksController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/subtaskTable', [TasksController::class, 'subtaskTable'])->name('tasks.subtaskTable');
    Route::post('/tasks/subtask', [TasksController::class, 'subtask'])->name('tasks.subtask');
    Route::put('/tasks/status/{id}', [TasksController::class, 'status'])->name('tasks.status');
    Route::get('/tasks/view/{id}', [TasksController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/edit', [TasksController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/removeImage/{id}', [TasksController::class, 'removeImage'])->name('tasks.removeImage');
    Route::delete('/tasks/delete/{id}', [TasksController::class, 'destroy'])->name('tasks.delete');
    
});

require __DIR__.'/auth.php';
