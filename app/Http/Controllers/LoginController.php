use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

public function login(Request $request)
{
    $response = Http::withBasicAuth(
        env('WORDPRESS_USERNAME'),
        env('WORDPRESS_PASSWORD')
    )->get(env('WORDPRESS_URL') . '/users/me');

    if ($response->successful()) {
        return response()->json([
            'success' => true,
            'user' => $response->json(),
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Invalid WordPress credentials',
    ], 401);
}
