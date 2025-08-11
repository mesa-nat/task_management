<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;

// List all tasks
Route::get('tasks', [TaskController::class, 'index']);

// Create a new task
Route::post('tasks', [TaskController::class, 'store']);

// Get a specific task by ID
Route::get('tasks/{task}', [TaskController::class, 'show']);

// Update a specific task by ID
Route::put('tasks/{task}', [TaskController::class, 'update']);

// Delete a specific task by ID
Route::delete('tasks/{task}', [TaskController::class, 'destroy']);