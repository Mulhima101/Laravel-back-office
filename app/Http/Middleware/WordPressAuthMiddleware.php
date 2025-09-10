<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WordPressAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->get('wp_authenticated')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'WordPress authentication required'], 401);
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}