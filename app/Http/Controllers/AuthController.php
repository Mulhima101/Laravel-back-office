<?php

namespace App\Http\Controllers;

use App\Services\WordPressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private $wpService;

    public function __construct(WordPressService $wpService)
    {
        $this->wpService = $wpService;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            if ($this->wpService->authenticateUser($credentials['username'], $credentials['password'])) {
                Session::put('wp_authenticated', true);
                Session::put('wp_username', $credentials['username']);
                Session::put('wp_password', $credentials['password']);
                
                Log::info('User authenticated successfully: ' . $credentials['username']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Authentication successful'
                ]);
            }

            Log::warning('Authentication failed for user: ' . $credentials['username']);
            return response()->json([
                'success' => false, 
                'message' => 'Invalid WordPress credentials'
            ], 401);
        } catch (\Exception $e) {
            Log::error('Authentication error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Authentication service unavailable'
            ], 500);
        }
    }

    public function logout()
    {
        Session::forget(['wp_authenticated', 'wp_username', 'wp_password']);
        Log::info('User logged out');
        
        return response()->json(['success' => true]);
    }

    public function check()
    {
        $isAuthenticated = Session::get('wp_authenticated', false);
        
        return response()->json([
            'authenticated' => $isAuthenticated,
            'username' => $isAuthenticated ? Session::get('wp_username') : null
        ]);
    }

    public function testConnection()
    {
        try {
            $connected = $this->wpService->testConnection();
            return response()->json([
                'connected' => $connected,
                'wordpress_url' => env('WORDPRESS_URL')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}