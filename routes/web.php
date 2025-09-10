<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogPostController;
use Illuminate\Support\Facades\Route;

// Default route - redirect to backoffice
Route::get('/', function () {
    return redirect('/backoffice');
});

// WordPress Authentication API routes
Route::post('/api/auth/login', [AuthController::class, 'login']);
Route::post('/api/auth/logout', [AuthController::class, 'logout']);
Route::get('/api/auth/check', [AuthController::class, 'check']);
Route::get('/api/auth/test', [AuthController::class, 'testConnection']);

// Blog post API routes
Route::get('/api/posts', [BlogPostController::class, 'index']);
Route::post('/api/posts', [BlogPostController::class, 'store']);
Route::get('/api/posts/{id}', [BlogPostController::class, 'show']);
Route::put('/api/posts/{id}', [BlogPostController::class, 'update']);
Route::delete('/api/posts/{id}', [BlogPostController::class, 'destroy']);

// Main Vue.js application route (catch-all)
Route::get('/backoffice/{any?}', function () {
    return view('backoffice');
})->where('any', '.*');