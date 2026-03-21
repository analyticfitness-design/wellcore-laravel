<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('wellcore')->user();
        if (!$user) {
            return redirect('/login');
        }

        $userRole = null;
        if ($user instanceof \App\Models\Admin) {
            $userRole = $user->role?->value ?? 'admin';
        } elseif ($user instanceof \App\Models\Client) {
            $userRole = 'client';
        }

        if (!in_array($userRole, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
