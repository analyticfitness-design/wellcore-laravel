<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Minimum seconds between last_used_at writes to reduce DB pressure.
     */
    private const THROTTLE_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only act on /api/v/client/* routes
        if (! $request->is('api/v/client/*')) {
            return $response;
        }

        // Skip if this is an impersonation session — wc_admin_token presence
        // means an admin is acting as the client, not the real client.
        try {
            if (session()->has('wc_admin_token')) {
                return $response;
            }
        } catch (\RuntimeException) {
            // Session not started (stateless API route) — not impersonation, proceed.
        }

        $token = $this->resolveToken($request);

        if (! $token) {
            return $response;
        }

        $this->touchLastSeen($token);

        return $response;
    }

    /**
     * Update last_used_at for the given token, throttled to once per minute.
     */
    private function touchLastSeen(string $token): void
    {
        try {
            $affected = DB::table('auth_tokens')
                ->where('token', $token)
                ->where('user_type', 'client')
                ->where(function ($q) {
                    $q->whereNull('last_used_at')
                        ->orWhereRaw('last_used_at < NOW() - INTERVAL ? SECOND', [self::THROTTLE_SECONDS]);
                })
                ->update(['last_used_at' => now()]);
        } catch (\Throwable $e) {
            // Never break the request flow for a tracking write.
            report($e);
        }
    }

    /**
     * Extract the token from session, Bearer header, or cookie — mirrors EnsureAuthenticated.
     */
    private function resolveToken(Request $request): ?string
    {
        try {
            $sessionToken = session('wc_token');
            if ($sessionToken) {
                return $sessionToken;
            }
        } catch (\RuntimeException) {
            // Session not available on stateless routes.
        }

        $header = $request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        try {
            $cookie = $request->cookie('wc_token');
            if ($cookie) {
                return $cookie;
            }
        } catch (\Exception) {
            // Decryption failed (vanilla PHP cookie) — skip.
        }

        return null;
    }
}
