<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\ImpersonationLog;
use App\Traits\Auditable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminImpersonateController extends Controller
{
    use Auditable;

    /**
     * POST /api/v/admin/coaches/{adminId}/impersonate
     */
    public function start(Request $request, int $adminId): JsonResponse
    {
        $superadmin = $this->resolveSuperadminOrFail($request);

        if ($adminId === $superadmin->id) {
            return response()->json(['error' => 'No puedes impersonificarte a ti mismo.'], 422);
        }

        $target = Admin::find($adminId);
        if (! $target) {
            return response()->json(['error' => 'Admin no encontrado.'], 404);
        }

        $targetRole = $target->role instanceof UserRole ? $target->role->value : (string) $target->role;
        if ($targetRole === UserRole::Superadmin->value) {
            return response()->json(['error' => 'No se puede impersonificar a otro superadmin.'], 422);
        }
        if (! in_array($targetRole, ['coach', 'admin', 'jefe'], true)) {
            return response()->json(['error' => 'Rol del target no es impersonificable.'], 422);
        }

        $rootToken = session('wc_root_token');
        if (! $rootToken) {
            $rootToken = $request->bearerToken() ?? session('wc_token');
            if (! $rootToken) {
                return response()->json(['error' => 'Sesion raiz no encontrada.'], 403);
            }
            session([
                'wc_root_token'     => $rootToken,
                'wc_root_user_id'   => $superadmin->id,
                'wc_root_user_name' => $superadmin->name ?? $superadmin->username ?? 'Superadmin',
            ]);
        }

        $newToken = bin2hex(random_bytes(32));
        $expiresAt = now()->addMinutes(60);

        $authToken = AuthToken::create([
            'user_type'  => UserType::Admin->value,
            'user_id'    => $target->id,
            'token'      => $newToken,
            'expires_at' => $expiresAt,
            'ip_address' => $request->ip(),
        ]);

        $log = ImpersonationLog::create([
            'actor_type'  => 'admin',
            'actor_id'    => $superadmin->id,
            'actor_name'  => $superadmin->name ?? $superadmin->username ?? 'Superadmin',
            'target_type' => 'admin',
            'target_id'   => $target->id,
            'target_name' => $target->name ?? $target->username ?? 'Admin',
            'token'       => $newToken,
            'started_at'  => now(),
            'ip'          => $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 500),
        ]);

        $authToken->update(['impersonation_log_id' => $log->id]);

        $chain = session('wc_impersonation_chain', []);
        $chain[] = [
            'level'       => count($chain) + 1,
            'log_id'      => $log->id,
            'token'       => $newToken,
            'target_type' => 'admin',
            'target_id'   => $target->id,
            'target_name' => $log->target_name,
        ];

        session([
            'wc_impersonation_chain' => $chain,
            'wc_token'               => $newToken,
            'wc_user_type'           => 'admin',
            'wc_user_id'             => $target->id,
            'wc_user_name'           => $log->target_name,
            'wc_user_portal'         => '/coach',
        ]);

        Log::channel('security')->info('IMPERSONATE_START', [
            'superadmin_id' => $superadmin->id,
            'target_id'     => $target->id,
            'target_type'   => 'admin',
            'log_id'        => $log->id,
            'ip'            => $request->ip(),
            'user_agent'    => $request->userAgent(),
        ]);

        return response()->json([
            'token'        => $newToken,
            'redirect_url' => '/coach',
            'log_id'       => $log->id,
            'expires_at'   => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v/admin/impersonate/end
     */
    public function end(Request $request): JsonResponse
    {
        $chain = session('wc_impersonation_chain', []);
        if (empty($chain)) {
            return response()->json(['ok' => true, 'noop' => true]);
        }

        $rootToken = session('wc_root_token');
        if (! $rootToken) {
            return response()->json(['error' => 'No se encontro sesion raiz.'], 422);
        }

        foreach ($chain as $entry) {
            ImpersonationLog::where('id', $entry['log_id'])
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);
            AuthToken::where('token', $entry['token'])->delete();
        }

        $rootAuth = AuthToken::where('token', $rootToken)
            ->where('expires_at', '>', now())
            ->first();
        $rootUser = $rootAuth ? Admin::find($rootAuth->user_id) : null;

        session([
            'wc_token'       => $rootToken,
            'wc_user_type'   => 'admin',
            'wc_user_id'     => $rootUser?->id,
            'wc_user_name'   => $rootUser?->name ?? $rootUser?->username ?? 'Superadmin',
            'wc_user_portal' => '/admin',
        ]);
        session()->forget([
            'wc_root_token', 'wc_root_user_id', 'wc_root_user_name',
            'wc_impersonation_chain', 'wc_admin_token',
        ]);

        Log::channel('security')->info('IMPERSONATE_END', [
            'superadmin_id' => $rootUser?->id,
            'closed_logs'   => array_column($chain, 'log_id'),
        ]);

        return response()->json([
            'ok'           => true,
            'redirect_url' => '/admin/coaches',
            'root_token'   => $rootToken,
        ]);
    }

    protected function resolveSuperadminOrFail(Request $request): Admin
    {
        $user = auth('wellcore')->user();
        if (! $user instanceof Admin) {
            abort(403, 'No autenticado.');
        }
        $role = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        if ($role !== UserRole::Superadmin->value) {
            abort(403, 'Solo superadmin puede impersonificar coaches.');
        }
        return $user;
    }
}
