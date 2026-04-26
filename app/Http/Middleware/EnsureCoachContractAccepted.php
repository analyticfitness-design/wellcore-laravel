<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\CoachContractService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureCoachContractAccepted
{
    public function __construct(private readonly CoachContractService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (! $this->service->isGateEnabled()) {
                return $next($request);
            }
        } catch (\RuntimeException $e) {
            // Config missing — fail closed: block access and return 503
            Log::critical('Coach contract gate config unavailable', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'gate_unavailable'], 503);
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
