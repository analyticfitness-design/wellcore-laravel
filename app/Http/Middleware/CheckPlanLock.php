<?php

namespace App\Http\Middleware;

use App\Auth\WellCoreGuard;
use App\Enums\UserType;
use App\Models\Client;
use App\Services\PlanLockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inyecta el estado de lock del plan del cliente en la request (soft mode),
 * o bloquea con 403 si el plan está expirado (strict mode).
 *
 * Uso en rutas:
 *   ->middleware('plan.lock')         → soft: inyecta attributes, nunca bloquea
 *   ->middleware('plan.lock:strict')  → strict: 403 si expiró
 */
class CheckPlanLock
{
    public function __construct(private PlanLockService $lockService) {}

    public function handle(Request $request, Closure $next, string $mode = 'soft'): Response
    {
        $client = $this->resolveClient($request);

        if (! $client) {
            // Sin cliente autenticado no hay nada que revisar
            return $next($request);
        }

        $status = $this->lockService->status($client);

        // Compartir siempre con la request (controllers + views pueden leer)
        $request->attributes->set('plan_lock_status', $status);

        if ($mode === 'strict' && $status['is_locked']) {
            return $request->expectsJson() || $request->is('api/*')
                ? response()->json([
                    'message' => 'Tu plan expiró. Renueva para continuar.',
                    'plan_lock_status' => $status,
                    'renew_url' => '/renovar',
                ], 403)
                : redirect('/renovar');
        }

        return $next($request);
    }

    private function resolveClient(Request $request): ?Client
    {
        try {
            $guard = app(WellCoreGuard::class);
            $user = $guard->user();

            if (! $user || $guard->userType() !== UserType::Client) {
                return null;
            }

            return $user instanceof Client ? $user : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
