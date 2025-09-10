<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\WordPressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class BlogPostController extends Controller
{
    private $wpService;

    public function __construct(WordPressService $wpService)
    {
        $this->wpService = $wpService;
        
        // Middleware to check WordPress authentication
        $this->middleware(function ($request, $next) {
            if (!Session::get('wp_authenticated')) {
                return response()->json(['message' => 'WordPress authentication required'], 401);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of blog posts
     */
    public function index(Request $request)
    {
        try {
            $wpPosts = $this->wpService->getPosts();
            $localPosts = BlogPost::all()->keyBy('wordpress_id');

            $posts = collect($wpPosts)->map(function ($post) use ($localPosts) {
                $localPost = $localPosts->get($post['id']);
                
                return [
                    'id' => $post['id'],
                    'title' => $post['title']['rendered'] ?? '',
                    'content' => $post['content']['rendered'] ?? '',
                    'excerpt' => $post['excerpt']['rendered'] ?? '',
                    'status' => $post['status'],
                    'date' => $post['date'],
                    'modified' => $post['modified'],
                    'priority' => $localPost ? $localPost->priority : 0,
                    'link' => $post['link'] ?? ''
                ];
            });

            // Sort by priority if requested
            if ($request->get('sort_by_priority')) {
                $posts = $posts->sortByDesc('priority');
            }

            Log::info('Fetched ' . $posts->count() . ' blog posts');
            return response()->json($posts->values());
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch blog posts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch blog posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        // Return form data if needed
        return response()->json([
            'statuses' => ['draft', 'publish', 'private'],
            'default_status' => 'draft'
        ]);
    }

    /**
     * Store a newly created blog post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'string|in:draft,publish,private',
            'priority' => 'integer|min:0|max:10'
        ]);

        try {
            // Create post in WordPress
            $wpPost = $this->wpService->createPost(
                $validated['title'],
                $validated['content'],
                $validated['status'] ?? 'draft'
            );

            // Store priority in local database if provided
            if (isset($validated['priority']) && $validated['priority'] > 0) {
                BlogPost::create([
                    'wordpress_id' => $wpPost['id'],
                    'priority' => $validated['priority']
                ]);
            }

            Log::info('Blog post created successfully', ['wp_id' => $wpPost['id']]);

            return response()->json([
                'success' => true,
                'post' => $wpPost,
                'message' => 'Post created successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create blog post: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified blog post
     */
    public function show($id)
    {
        try {
            $wpPost = $this->wpService->getPost($id);
            $priority = BlogPost::getPriority($id);

            $post = [
                'id' => $wpPost['id'],
                'title' => $wpPost['title']['rendered'] ?? '',
                'content' => $wpPost['content']['rendered'] ?? '',
                'excerpt' => $wpPost['excerpt']['rendered'] ?? '',
                'status' => $wpPost['status'],
                'date' => $wpPost['date'],
                'modified' => $wpPost['modified'],
                'priority' => $priority,
                'link' => $wpPost['link'] ?? ''
            ];

            return response()->json($post);

        } catch (\Exception $e) {
            Log::error('Failed to fetch blog post: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit($id)
    {
        return $this->show($id);
    }

    /**
     * Update the specified blog post
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'string|in:draft,publish,private',
            'priority' => 'integer|min:0|max:10'
        ]);

        try {
            // Update post in WordPress
            $wpPost = $this->wpService->updatePost(
                $id,
                $validated['title'],
                $validated['content'],
                $validated['status'] ?? null
            );

            // Update priority in local database
            if (isset($validated['priority'])) {
                BlogPost::updatePriority($id, $validated['priority']);
            }

            Log::info('Blog post updated successfully', ['wp_id' => $id]);

            return response()->json([
                'success' => true,
                'post' => $wpPost,
                'message' => 'Post updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update blog post: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified blog post
     */
    public function destroy($id)
    {
        try {
            // Delete from WordPress
            $this->wpService->deletePost($id);
            
            // Delete priority from local database
            BlogPost::where('wordpress_id', $id)->delete();

            Log::info('Blog post deleted successfully', ['wp_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete blog post: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update priority for a specific post
     */
    public function updatePriority(Request $request, $id)
    {
        $validated = $request->validate([
            'priority' => 'required|integer|min:0|max:10'
        ]);

        try {
            BlogPost::updatePriority($id, $validated['priority']);

            Log::info('Post priority updated', ['wp_id' => $id, 'priority' => $validated['priority']]);

            return response()->json([
                'success' => true,
                'message' => 'Priority updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update priority: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update priority',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync posts from WordPress
     */
    public function sync()
    {
        try {
            $wpPosts = $this->wpService->getPosts();
            
            return response()->json([
                'success' => true,
                'synced_count' => count($wpPosts),
                'message' => 'Posts synchronized successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync posts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to sync posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}