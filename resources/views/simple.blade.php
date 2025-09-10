<!DOCTYPE html>
<html>
<head>
    <title>WordPress Back Office - API Ready</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px;
            background: #f5f5f5;
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .endpoint { 
            background: #f8f9fa; 
            padding: 10px; 
            border-left: 4px solid #007bff; 
            margin: 10px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 WordPress Back Office - API Ready!</h1>
        
        <h2 class="success">✅ Laravel Backend is Running</h2>
        
        <h3>Available API Endpoints:</h3>
        
        <div class="endpoint">POST /api/auth/login</div>
        <div class="endpoint">GET /api/auth/check</div>
        <div class="endpoint">GET /api/blog-posts</div>
        <div class="endpoint">POST /api/blog-posts</div>
        <div class="endpoint">PUT /api/blog-posts/{id}</div>
        <div class="endpoint">DELETE /api/blog-posts/{id}</div>
        
        <h3>Test WordPress Connection:</h3>
        <p><a href="/test-wp" target="_blank">Test WordPress API Connection</a></p>
        
        <h3>Your WordPress Details:</h3>
        <p><strong>URL:</strong> {{ env('WORDPRESS_URL') }}</p>
        <p><strong>Username:</strong> {{ env('WORDPRESS_USERNAME') }}</p>
        
        <h3>Next Steps:</h3>
        <ol>
            <li>Test the <code>/test-wp</code> endpoint to verify WordPress connection</li>
            <li>Test authentication with your credentials</li>
            <li>Build the Vue frontend with <code>npm run build</code></li>
        </ol>
    </div>
</body>
</html>