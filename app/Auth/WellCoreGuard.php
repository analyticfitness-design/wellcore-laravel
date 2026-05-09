<?php

namespace App\Auth;

use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class WellCoreGuard implements Guard
{
    use GuardHelpers;

    protected Request $request;
    protected ?AuthToken $currentToken = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();
        if (!$token) {
            return null;
        }

        $authToken = AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$authToken) {
            return null;
        }

        $this->currentToken = $authToken;

        // Update last_used_at throttled to every 5 min to avoid N writes per session
        if ($authToken->last_used_at === null || $authToken->last_used_at->diffInMinutes(now()) > 5) {
            $updateData = ['last_used_at' => now()];
            // Rolling expiry: if token expires within 7 days, extend by 30 more days
            if ($authToken->expires_at->diffInDays(now()) < 7) {
                $updateData['expires_at'] = now()->addDays(30);
            }
            $authToken->updateQuietly($updateData);
        }

        $this->user = match ($authToken->user_type->value) {
            'admin' => Admin::find($authToken->user_id),
            'client' => Client::find($authToken->user_id),
            default => null,
        };

        return $this->user;
    }

    public function validate(array $credentials = []): bool
    {
        $token = $credentials['token'] ?? null;
        if (!$token) {
            return false;
        }

        return AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function currentToken(): ?AuthToken
    {
        return $this->currentToken;
    }

    public function isAdmin(): bool
    {
        return $this->user() instanceof Admin;
    }

    public function isClient(): bool
    {
        return $this->user() instanceof Client;
    }

    protected function getTokenFromRequest(): ?string
    {
        // 1. Try Laravel session (web login stores token here)
        $sessionToken = session('wc_token');
        if ($sessionToken) {
            return $sessionToken;
        }

        // 2. Try Authorization header (API clients)
        $header = $this->request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // 3. Try cookie (vanilla PHP app compatibility)
        $cookie = $this->request->cookie('wc_token');
        if ($cookie) {
            return $cookie;
        }

        return null;
    }
}
