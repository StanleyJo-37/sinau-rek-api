<?php

use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function() {
    
    Route::group('/todos', function() {
        Route::get('/all', [TodoController::class, 'getAllTodos']);
        Route::get('/get/{todo_id}', [TodoController::class, 'getTodo']);
        Route::post('/create/{todo_id}', [TodoController::class, 'createTodo']);
        Route::patch('/edit/{todo_id}', [TodoController::class, 'editTodo']);
        Route::delete('/delete/{todo_id}', [TodoController::class, 'deleteTodo']);
    });
});