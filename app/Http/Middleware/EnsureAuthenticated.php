<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->resolveToken($request);

        if (! $token) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Validate the token exists and is not expired
        $authToken = AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $authToken) {
            // Clear invalid session data
            session()->forget(['wc_token', 'wc_user_type', 'wc_user_id']);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Token expired or invalid.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        return $next($request);
    }

    protected function resolveToken(Request $request): ?string
    {
        // 1. Check Laravel session
        $sessionToken = session('wc_token');
        if ($sessionToken) {
            return $sessionToken;
        }

        // 2. Check Bearer token in Authorization header
        $header = $request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // 3. Check cookie
        $cookie = $request->cookie('wc_token');
        if ($cookie) {
            return $cookie;
        }

        return null;
    }
}
