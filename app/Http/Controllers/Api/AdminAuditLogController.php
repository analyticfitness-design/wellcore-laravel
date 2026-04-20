<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * P2.3 — READ-ONLY audit log endpoint. Only accessible to superadmins.
 */
class AdminAuditLogController extends Controller
{
    use AuthenticatesVueRequests;

    protected function resolveSuperadminOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth || $auth['userType'] !== UserType::Admin) {
            abort(401, 'Token invalido o expirado.');
        }

        $admin = $auth['user'];
        $role = $admin->role instanceof UserRole ? $admin->role->value : $admin->role;

        if ($role !== 'superadmin') {
            abort(403, 'Solo superadmin puede ver el audit log.');
        }

        return $admin;
    }

    public function index(Request $request): JsonResponse
    {
        $this->resolveSuperadminOrFail($request);

        $action = trim((string) $request->query('action', ''));
        $actorType = trim((string) $request->query('actor_type', ''));
        $actorId = $request->query('actor_id');
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $query = AuditLog::query();

        if ($action !== '') {
            $query->where('action', 'LIKE', $action.'%');
        }
        if ($actorType !== '') {
            $query->where('actor_type', $actorType);
        }
        if ($actorId !== null && $actorId !== '') {
            $query->where('actor_id', (int) $actorId);
        }
        if ($from !== '') {
            $query->where('created_at', '>=', $from);
        }
        if ($to !== '') {
            $query->where('created_at', '<=', $to);
        }

        $paginated = $query->orderByDesc('id')->paginate(50);

        return response()->json([
            'logs' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }
}
