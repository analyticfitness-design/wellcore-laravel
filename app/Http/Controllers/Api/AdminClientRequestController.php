<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientActionRequest;
use App\Models\WellcoreNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminClientRequestController extends Controller
{
    use AuthenticatesVueRequests;

    protected function resolveAdminOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth || $auth['userType'] !== UserType::Admin) {
            abort(401, 'Token invalido o expirado.');
        }

        $admin = $auth['user'];
        $role = $admin->role instanceof UserRole ? $admin->role->value : $admin->role;

        if (! in_array($role, ['admin', 'superadmin', 'jefe'])) {
            abort(403, 'Solo administradores.');
        }

        return $admin;
    }

    public function index(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $status = (string) $request->query('status', '');
        $action = (string) $request->query('action', '');
        $coachId = $request->query('coach_id');
        $search = trim((string) $request->query('search', ''));

        $query = ClientActionRequest::query();

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($action !== '') {
            $query->where('action', $action);
        }
        if ($coachId !== null && $coachId !== '') {
            $query->where('coach_id', (int) $coachId);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'LIKE', "%{$search}%")
                    ->orWhere('coach_name', 'LIKE', "%{$search}%");
            });
        }

        $counts = ClientActionRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $paginated = $query->orderByDesc('created_at')->paginate(30);

        return response()->json([
            'requests' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
            'counts' => [
                'pendiente' => (int) ($counts['pendiente'] ?? 0),
                'aprobado' => (int) ($counts['aprobado'] ?? 0),
                'rechazado' => (int) ($counts['rechazado'] ?? 0),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $req = ClientActionRequest::findOrFail($id);

        return response()->json(['request' => $req]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $req = ClientActionRequest::where('status', 'pendiente')->findOrFail($id);

        DB::transaction(function () use ($req, $admin) {
            match ($req->action) {
                'delete' => optional(Client::find($req->client_id))->delete(),
                'deactivate' => Client::where('id', $req->client_id)->update(['status' => 'inactivo']),
                'edit' => null,
                default => null,
            };

            $req->update([
                'status' => 'aprobado',
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);

            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id' => $req->coach_id,
                'type' => 'client_action_request_resolved',
                'title' => 'Solicitud aprobada',
                'body' => "Tu solicitud de {$req->action} sobre {$req->client_name} fue aprobada.",
                'link' => '/coach/clients/'.$req->client_id,
            ]);
        });

        return response()->json(['approved' => true]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $validated = $request->validate([
            'admin_notas' => 'required|string|min:5|max:2000',
        ]);

        $req = ClientActionRequest::where('status', 'pendiente')->findOrFail($id);

        DB::transaction(function () use ($req, $admin, $validated) {
            $req->update([
                'status' => 'rechazado',
                'admin_notas' => $validated['admin_notas'],
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);

            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id' => $req->coach_id,
                'type' => 'client_action_request_resolved',
                'title' => 'Solicitud rechazada',
                'body' => "Tu solicitud de {$req->action} sobre {$req->client_name} fue rechazada.",
                'link' => '/coach/clients/'.$req->client_id,
            ]);
        });

        return response()->json(['rejected' => true]);
    }
}
