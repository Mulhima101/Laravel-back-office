<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WordPressService
{
    private $client;
    private $baseUrl;
    private $username;
    private $password;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false // For development only
        ]);
        $this->baseUrl = rtrim(env('WORDPRESS_URL'), '/');
        $this->username = env('WORDPRESS_USERNAME');
        $this->password = env('WORDPRESS_PASSWORD');
    }

    private function getAuthHeaders()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function testConnection()
    {
        try {
            $response = $this->client->get($this->baseUrl . '/wp-json/wp/v2/', [
                'headers' => $this->getAuthHeaders(),
            ]);
            
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('WordPress connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getPosts()
    {
        try {
            $response = $this->client->get($this->baseUrl . '/wp-json/wp/v2/posts', [
                'headers' => $this->getAuthHeaders(),
                'query' => [
                    'per_page' => 100,
                    '_embed' => true
                ]
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Failed to fetch posts: ' . $e->getMessage());
            throw new \Exception('Failed to fetch posts: ' . $e->getMessage());
        }
    }

    public function getPost($id)
    {
        try {
            $response = $this->client->get($this->baseUrl . "/wp-json/wp/v2/posts/{$id}", [
                'headers' => $this->getAuthHeaders()
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Failed to fetch post: ' . $e->getMessage());
            throw new \Exception('Failed to fetch post: ' . $e->getMessage());
        }
    }

    public function createPost($title, $content, $status = 'draft')
    {
        try {
            $response = $this->client->post($this->baseUrl . '/wp-json/wp/v2/posts', [
                'headers' => $this->getAuthHeaders(),
                'json' => [
                    'title' => $title,
                    'content' => $content,
                    'status' => $status
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            Log::info('Post created successfully: ' . $result['id']);
            return $result;
        } catch (RequestException $e) {
            Log::error('Failed to create post: ' . $e->getMessage());
            throw new \Exception('Failed to create post: ' . $e->getMessage());
        }
    }

    public function updatePost($id, $title, $content, $status = null)
    {
        try {
            $data = [
                'title' => $title,
                'content' => $content
            ];
            
            if ($status) {
                $data['status'] = $status;
            }

            $response = $this->client->post($this->baseUrl . "/wp-json/wp/v2/posts/{$id}", [
                'headers' => $this->getAuthHeaders(),
                'json' => $data
            ]);
            
            $result = json_decode($response->getBody(), true);
            Log::info('Post updated successfully: ' . $id);
            return $result;
        } catch (RequestException $e) {
            Log::error('Failed to update post: ' . $e->getMessage());
            throw new \Exception('Failed to update post: ' . $e->getMessage());
        }
    }

    public function deletePost($id)
    {
        try {
            $response = $this->client->delete($this->baseUrl . "/wp-json/wp/v2/posts/{$id}", [
                'headers' => $this->getAuthHeaders(),
                'query' => ['force' => true] // Permanently delete
            ]);
            
            $result = json_decode($response->getBody(), true);
            Log::info('Post deleted successfully: ' . $id);
            return $result;
        } catch (RequestException $e) {
            Log::error('Failed to delete post: ' . $e->getMessage());
            throw new \Exception('Failed to delete post: ' . $e->getMessage());
        }
    }

    public function authenticateUser($username, $password)
    {
        try {
            $client = new Client(['timeout' => 10]);
            $response = $client->get($this->baseUrl . '/wp-json/wp/v2/posts', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
                ],
                'query' => ['per_page' => 1]
            ]);
            
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('Authentication failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getCurrentUser()
    {
        try {
            $response = $this->client->get($this->baseUrl . '/wp-json/wp/v2/users/me', [
                'headers' => $this->getAuthHeaders()
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Failed to get current user: ' . $e->getMessage());
            return null;
        }
    }
}