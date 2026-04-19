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
use Illuminate\Validation\Rule;

class CoachClientRequestController extends Controller
{
    use AuthenticatesVueRequests;

    protected function resolveCoachOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth || $auth['userType'] !== UserType::Admin) {
            abort(401, 'Token invalido o expirado.');
        }

        $admin = $auth['user'];
        $role = $admin->role instanceof UserRole ? $admin->role->value : $admin->role;

        if (! in_array($role, ['coach', 'admin', 'superadmin', 'jefe'])) {
            abort(403, 'Acceso restringido a coaches.');
        }

        return $admin;
    }

    protected function assertOwnsClient(Admin $coach, int $clientId): void
    {
        $coachController = app(CoachController::class);
        $reflection = new \ReflectionClass($coachController);
        $method = $reflection->getMethod('getCoachClientIds');
        $method->setAccessible(true);
        $ids = $method->invoke($coachController, $coach->id);

        if (! $ids->contains($clientId)) {
            abort(403, 'Este cliente no esta asignado a ti.');
        }
    }

    public function store(Request $request, int $clientId): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        $this->assertOwnsClient($coach, $clientId);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['delete', 'deactivate', 'edit'])],
            'reason' => 'required|string|min:10|max:2000',
        ]);

        $duplicate = ClientActionRequest::where('client_id', $clientId)
            ->where('coach_id', $coach->id)
            ->where('action', $validated['action'])
            ->where('status', 'pendiente')
            ->exists();

        if ($duplicate) {
            return response()->json([
                'error' => 'Ya tienes una solicitud pendiente de este tipo para este cliente.',
            ], 409);
        }

        $client = Client::findOrFail($clientId);

        $req = DB::transaction(function () use ($coach, $client, $validated) {
            $req = ClientActionRequest::create([
                'coach_id' => $coach->id,
                'coach_name' => $coach->name ?? $coach->username ?? 'Coach',
                'client_id' => $client->id,
                'client_name' => $client->name ?? '',
                'action' => $validated['action'],
                'reason' => $validated['reason'],
                'status' => 'pendiente',
            ]);

            $this->notifySuperadmins(
                title: 'Nueva solicitud de accion sobre cliente',
                body: "{$req->coach_name} solicito {$req->action} sobre {$req->client_name}.",
                link: '/admin/client-requests/'.$req->id,
            );

            return $req;
        });

        return response()->json(['created' => true, 'request' => $req], 201);
    }

    public function index(Request $request, int $clientId): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);
        $this->assertOwnsClient($coach, $clientId);

        $requests = ClientActionRequest::where('client_id', $clientId)
            ->where('coach_id', $coach->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['requests' => $requests]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $req = ClientActionRequest::where('id', $id)
            ->where('coach_id', $coach->id)
            ->where('status', 'pendiente')
            ->first();

        if (! $req) {
            return response()->json(['error' => 'Solicitud no encontrada o no cancelable.'], 404);
        }

        $req->delete();

        return response()->json(['cancelled' => true]);
    }

    protected function notifySuperadmins(string $title, string $body, ?string $link = null): void
    {
        $superadminIds = Admin::whereIn('role', ['admin', 'superadmin', 'jefe'])
            ->pluck('id');

        foreach ($superadminIds as $adminId) {
            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id' => $adminId,
                'type' => 'client_action_request',
                'title' => $title,
                'body' => $body,
                'link' => $link,
            ]);
        }
    }
}
