<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Http\Request;

class ApiBearerAuth
{
    public function handle(Request $request, Closure $next): mixed
    {
        $header = $request->header('Authorization', '');
        if (! str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = substr($header, 7);
        $authToken = AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $authToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
