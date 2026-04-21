<?php

namespace App\Http\Middleware;

use App\Enums\PlanType;
use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlan
{
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        $user = auth('wellcore')->user();

        if (! $user instanceof Client) {
            return $next($request);
        }

        $clientPlan = $user->plan instanceof PlanType
            ? $user->plan->value
            : (string) $user->plan;

        if (in_array($clientPlan, $plans, strict: true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(
                ['message' => 'Tu plan no incluye esta funcionalidad'],
                Response::HTTP_FORBIDDEN,
            );
        }

        return redirect()->back()->with('error', 'Tu plan no incluye esta funcionalidad');
    }
}
