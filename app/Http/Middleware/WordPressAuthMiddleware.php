<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WordPressAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Session::get('wp_authenticated')) {
            return response()->json([
                'message' => 'WordPress authentication required'
            ], 401);
        }

        return $next($request);
    }
}