<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\WordPressService;

class BlogPostController extends Controller
{
    private $wpService;

    public function __construct(WordPressService $wpService)
    {
        $this->wpService = $wpService;
        
        // Fix: Use the correct middleware syntax
        $this->middleware(function ($request, $next) {
            if (!session()->get('wp_authenticated')) {
                return response()->json(['message' => 'WordPress authentication required'], 401);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of blog posts
     */
    public function index()
    {
        try {
            $posts = $this->wpService->getPosts();
            return response()->json($posts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created blog post
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'in:publish,draft,private'
        ]);

        try {
            $post = $this->wpService->createPost([
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->status ?? 'draft'
            ]);
            
            return response()->json($post, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified blog post
     */
    public function show($id)
    {
        try {
            $post = $this->wpService->getPost($id);
            return response()->json($post);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified blog post
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'status' => 'in:publish,draft,private'
        ]);

        try {
            $post = $this->wpService->updatePost($id, $request->only(['title', 'content', 'status']));
            return response()->json($post);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified blog post
     */
    public function destroy($id)
    {
        try {
            $this->wpService->deletePost($id);
            return response()->json(['message' => 'Post deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}