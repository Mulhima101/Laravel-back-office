protected $middlewareAliases = [
    // ... other middleware
    'wp.auth' => \App\Http\Middleware\WordPressAuthMiddleware::class,
];