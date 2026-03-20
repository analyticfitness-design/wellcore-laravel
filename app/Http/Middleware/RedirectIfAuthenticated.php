<?php

namespace App\Http\Middleware;

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->resolveToken($request);

        if (! $token) {
            return $next($request);
        }

        $authToken = AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $authToken) {
            // Token is invalid or expired, clear session and let them through to guest page
            session()->forget(['wc_token', 'wc_user_type', 'wc_user_id']);
            return $next($request);
        }

        // User is authenticated, redirect to their dashboard
        $redirectUrl = $this->resolveRedirectUrl($authToken);

        return redirect($redirectUrl);
    }

    protected function resolveToken(Request $request): ?string
    {
        // Check session first (primary for web)
        $sessionToken = session('wc_token');
        if ($sessionToken) {
            return $sessionToken;
        }

        // Check Bearer header (for API clients)
        $header = $request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // Check cookie
        $cookie = $request->cookie('wc_token');
        if ($cookie) {
            return $cookie;
        }

        return null;
    }

    protected function resolveRedirectUrl(AuthToken $authToken): string
    {
        if ($authToken->user_type->value === 'admin') {
            $admin = Admin::find($authToken->user_id);
            if ($admin && $admin->role === UserRole::Coach) {
                return route('coach.dashboard');
            }
            return route('admin.dashboard');
        }

        // Client
        $client = Client::find($authToken->user_id);
        if ($client && $client->plan === PlanType::Rise) {
            return route('rise.dashboard');
        }

        return route('client.dashboard');
    }
}
