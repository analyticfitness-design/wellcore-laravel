<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanTicketStatus;
use App\Enums\PlanType;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PlanTicket;
use App\Models\WellcoreNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminPlanTicketController extends Controller
{
    use AuthenticatesVueRequests;

    /**
     * Resolve the authenticated Admin (admin/superadmin/jefe) or abort.
     */
    protected function resolveAdminOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth) {
            abort(401, 'Token invalido o expirado.');
        }

        if ($auth['userType'] !== UserType::Admin) {
            abort(403, 'Acceso solo para administradores.');
        }

        $admin = $auth['user'];
        $role = $admin->role?->value ?? $admin->role ?? '';

        if (! in_array($role, ['admin', 'superadmin', 'jefe'])) {
            abort(403, 'No tienes permisos de administrador.');
        }

        return $admin;
    }

    // ─── Index (inbox) ──────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $status = $request->query('status');
        $coachId = $request->query('coach_id');
        $planType = $request->query('plan_type');
        $search = trim((string) $request->query('search', ''));

        $query = PlanTicket::query()->orderByDesc('submitted_at');

        if ($status && PlanTicketStatus::tryFrom($status)) {
            $query->where('status', $status);
        } else {
            $query->forAdminInbox();
        }

        if ($coachId !== null && $coachId !== '') {
            $query->where('coach_id', (int) $coachId);
        }

        if ($planType && PlanType::tryFrom($planType)) {
            $query->where('plan_type', $planType);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'LIKE', "%{$search}%")
                    ->orWhere('coach_name', 'LIKE', "%{$search}%");
            });
        }

        $paginator = $query->paginate(30);

        $counts = PlanTicket::query()
            ->forAdminInbox()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return response()->json([
            'tickets' => $paginator->items(),
            'counts' => $counts,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    // ─── Show ───────────────────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        return response()->json(['ticket' => $ticket]);
    }

    // ─── Export JSON ────────────────────────────────────────────────────

    public function exportJson(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        $payload = [
            'client_id' => $ticket->client_id,
            'client_name' => $ticket->client_name,
            'plan_type' => $ticket->plan_type?->value,
            'coach' => $ticket->coach_name,
            'created_at' => $ticket->created_at?->toDateString(),
            'brief' => [
                'datos_generales' => $ticket->datos_generales ?? (object) [],
                'plan_entrenamiento' => $ticket->plan_entrenamiento ?? (object) [],
                'plan_nutricional' => $ticket->plan_nutricional ?? (object) [],
                'plan_habitos' => $ticket->plan_habitos ?? (object) [],
                'plan_suplementacion' => $ticket->plan_suplementacion ?? (object) [],
                'plan_ciclo' => $ticket->plan_ciclo ?? (object) [],
            ],
            'notas_coach' => $ticket->notas_coach,
        ];

        return response()->json($payload);
    }

    // ─── Update Status ──────────────────────────────────────────────────

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    PlanTicketStatus::EnRevision->value,
                    PlanTicketStatus::Completado->value,
                    PlanTicketStatus::Rechazado->value,
                ]),
            ],
            'admin_notas' => ['sometimes', 'nullable', 'string'],
        ]);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        $newStatus = PlanTicketStatus::from($validated['status']);

        DB::transaction(function () use ($ticket, $newStatus, $validated, $admin) {
            $ticket->status = $newStatus;

            if (array_key_exists('admin_notas', $validated)) {
                $ticket->admin_notas = $validated['admin_notas'];
            }

            if ($newStatus === PlanTicketStatus::EnRevision) {
                $ticket->reviewed_at = now();
            } elseif ($newStatus === PlanTicketStatus::Completado) {
                $ticket->completed_at = now();
            } elseif ($newStatus === PlanTicketStatus::Rechazado) {
                $ticket->rejected_at = now();
            }

            $ticket->save();

            $this->notifyCoachOfStatusChange($ticket, $newStatus, $admin);
        });

        return response()->json(['ticket' => $ticket->fresh()]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────

    protected function notifyCoachOfStatusChange(PlanTicket $ticket, PlanTicketStatus $status, Admin $admin): void
    {
        $titles = [
            PlanTicketStatus::EnRevision->value => 'Tu ticket esta en revision',
            PlanTicketStatus::Completado->value => 'Tu ticket fue completado',
            PlanTicketStatus::Rechazado->value => 'Tu ticket fue rechazado',
        ];

        $title = $titles[$status->value] ?? 'Actualizacion de ticket';
        $body = "Ticket de {$ticket->client_name}: {$status->label()}";

        WellcoreNotification::create([
            'user_type' => 'admin',
            'user_id' => $ticket->coach_id,
            'type' => 'plan_ticket_status',
            'title' => $title,
            'body' => $body,
            'link' => "/coach/plan-tickets/{$ticket->id}",
        ]);
    }
}
