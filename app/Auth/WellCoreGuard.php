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
        // Try Authorization header first
        $header = $this->request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // Try cookie (for web sessions)
        $cookie = $this->request->cookie('wc_token');
        if ($cookie) {
            return $cookie;
        }

        // Try query parameter (for testing)
        return $this->request->query('_token');
    }
}
