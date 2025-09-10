<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogPostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes - NO middleware needed
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check', [AuthController::class, 'check']);
    Route::get('/test-connection', [AuthController::class, 'testConnection']);
});

// Blog management routes (these will be protected by session check in controller)
Route::prefix('blog-posts')->group(function () {
    Route::get('/', [BlogPostController::class, 'index']);
    Route::post('/', [BlogPostController::class, 'store']);
    Route::get('/create', [BlogPostController::class, 'create']);
    Route::get('/{id}', [BlogPostController::class, 'show']);
    Route::get('/{id}/edit', [BlogPostController::class, 'edit']);
    Route::put('/{id}', [BlogPostController::class, 'update']);
    Route::patch('/{id}', [BlogPostController::class, 'update']);
    Route::delete('/{id}', [BlogPostController::class, 'destroy']);
    Route::put('/{id}/priority', [BlogPostController::class, 'updatePriority']);
    Route::post('/sync', [BlogPostController::class, 'sync']);
});

// Alternative resource route (you can use either the above or this)
// Route::resource('blog-posts', BlogPostController::class);
// Route::put('blog-posts/{id}/priority', [BlogPostController::class, 'updatePriority']);
// Route::post('blog-posts/sync', [BlogPostController::class, 'sync']);

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'wordpress_url' => env('WORDPRESS_URL'),
        'laravel_version' => app()->version()
    ]);
});

// Test route for WordPress connection (remove after testing)
Route::get('/test-wp', function () {
    try {
        $wpService = app(\App\Services\WordPressService::class);
        $posts = $wpService->getPosts();
        
        return response()->json([
            'status' => 'success',
            'message' => 'WordPress connection successful',
            'posts_count' => count($posts),
            'wordpress_url' => env('WORDPRESS_URL'),
            'first_post_title' => isset($posts[0]) ? $posts[0]['title']['rendered'] : 'No posts found'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'wordpress_url' => env('WORDPRESS_URL')
        ]);
    }
});