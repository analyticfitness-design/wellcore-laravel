<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Enums\UserType;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Http\Request;

trait AuthenticatesVueRequests
{
    /**
     * Resolve the authenticated user from the Bearer token.
     *
     * Looks up the token in the auth_tokens table, validates expiry,
     * and returns the associated Client or Admin model.
     *
     * @return array{user: Client|Admin, userType: UserType, token: AuthToken}|null
     */
    protected function resolveAuthUser(Request $request): ?array
    {
        $token = $request->bearerToken();

        if (! $token) {
            return null;
        }

        $authToken = AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $authToken) {
            return null;
        }

        $userType = $authToken->user_type instanceof UserType
            ? $authToken->user_type
            : UserType::from($authToken->user_type);

        $user = match ($userType) {
            UserType::Admin  => Admin::find($authToken->user_id),
            UserType::Client => Client::find($authToken->user_id),
        };

        if (! $user) {
            return null;
        }

        return [
            'user'     => $user,
            'userType' => $userType,
            'token'    => $authToken,
        ];
    }

    /**
     * Resolve the authenticated Client or abort with 401.
     *
     * Convenience wrapper that enforces the user is a Client (not Admin).
     */
    protected function resolveClientOrFail(Request $request): Client
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth) {
            abort(401, 'Token invalido o expirado.');
        }

        if ($auth['userType'] !== UserType::Client) {
            abort(403, 'Acceso solo para clientes.');
        }

        $client = $auth['user'];

        // Block accounts that are not active (inactivo, suspendido, pendiente, congelado)
        if ($client->status !== \App\Enums\ClientStatus::Activo) {
            abort(response()->json([
                'inactive' => true,
                'status'   => $client->status instanceof \App\Enums\ClientStatus
                    ? $client->status->value
                    : 'inactivo',
                'message'  => 'Tu cuenta esta inactiva. Contacta a tu coach para renovar tu plan.',
            ], 403));
        }

        return $client;
    }
}
