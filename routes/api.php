<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Middleware\AcceptJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/auth')->group(function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function() {

    
    Route::prefix('/todos')->group(function() {
        Route::get('/all', [TodoController::class, 'getAllTodos']);
        Route::get('/get/{todo_id}', [TodoController::class, 'getTodo']);
        Route::post('/create', [TodoController::class, 'createTodo']);
        Route::patch('/edit/{todo_id}', [TodoController::class, 'editTodo']);
        Route::delete('/delete/{todo_id}', [TodoController::class, 'deleteTodo']);
    });
    
});