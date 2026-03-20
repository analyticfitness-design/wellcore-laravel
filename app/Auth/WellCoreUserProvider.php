<?php

namespace App\Auth;

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class WellCoreUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        // Try admin first, then client
        return Admin::find($identifier) ?? Client::find($identifier);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null; // Not used - we use our custom guard
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Not used
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (isset($credentials['username'])) {
            return Admin::where('username', $credentials['username'])->first();
        }

        if (isset($credentials['email'])) {
            return Client::where('email', $credentials['email'])->first();
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $password = $credentials['password'] ?? '';

        if ($user instanceof Admin) {
            return password_verify($password, $user->password_hash);
        }

        if ($user instanceof Client) {
            return password_verify($password, $user->password_hash);
        }

        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Not implemented yet
    }
}
