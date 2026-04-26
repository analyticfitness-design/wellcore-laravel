<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\CoachContractService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCoachContractAccepted
{
    public function __construct(private readonly CoachContractService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->service->isGateEnabled()) {
            return $next($request);
        }

        $user = $request->user('wellcore');

        if (! $user) {
            return $next($request); // let auth middleware handle unauthenticated
        }

        if ($user->role !== UserRole::Coach) {
            return $next($request);
        }

        if ($this->service->hasAcceptedCurrentVersion($user->id)) {
            return $next($request);
        }

        return response()->json([
            'contract_required' => true,
            'version' => $this->service->getCurrentVersion(),
        ], 403);
    }
}
