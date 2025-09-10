<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\WordPressService; // Add this import

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ADD THIS TEST ROUTE - WordPress Connection Test (TEMPORARY)
Route::get('/test-wp', function () {
    try {
        $wpService = new WordPressService();
        
        // Test connection first
        $connected = $wpService->testConnection();
        
        if (!$connected) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot connect to WordPress',
                'wordpress_url' => env('WORDPRESS_URL')
            ]);
        }
        
        // Try to get posts
        $posts = $wpService->getPosts();
        
        return response()->json([
            'status' => 'success',
            'connection' => 'OK',
            'wordpress_url' => env('WORDPRESS_URL'),
            'posts_count' => count($posts),
            'first_post_title' => isset($posts[0]) ? $posts[0]['title']['rendered'] : 'No posts found',
            'message' => 'WordPress connection successful!'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'wordpress_url' => env('WORDPRESS_URL'),
            'suggestion' => 'Check your WordPress credentials and URL'
        ], 500);
    }
});

// ADD THIS ROUTE FOR YOUR MAIN APPLICATION (Vue Frontend)
Route::get('/backoffice/{any?}', function () {
    return view('backoffice'); // This will be your Vue app
})->where('any', '.*');

require __DIR__.'/auth.php';