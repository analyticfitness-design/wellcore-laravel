<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanTicketStatus;
use App\Enums\PlanType;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use App\Models\PlanTicket;
use App\Models\WellcoreNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoachPlanTicketController extends Controller
{
    use AuthenticatesVueRequests;

    /**
     * Resolve the authenticated Admin (coach/admin/superadmin/jefe) or abort.
     */
    protected function resolveCoachOrFail(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);

        if (! $auth) {
            abort(401, 'Token invalido o expirado.');
        }

        if ($auth['userType'] !== UserType::Admin) {
            abort(403, 'Acceso solo para coaches y administradores.');
        }

        $admin = $auth['user'];
        $role = $admin->role?->value ?? $admin->role ?? '';

        if (! in_array($role, ['coach', 'admin', 'superadmin', 'jefe'])) {
            abort(403, 'No tienes permisos para acceder al portal de coach.');
        }

        return $admin;
    }

    // ─── Index ──────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $status = $request->query('status');

        $query = PlanTicket::query()
            ->forCoach($coach->id)
            ->orderByDesc('updated_at');

        if ($status && PlanTicketStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        $paginator = $query->paginate(50);

        return response()->json([
            'tickets' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    // ─── Store ──────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'plan_type' => ['required', 'string', Rule::in(array_column(PlanType::cases(), 'value'))],
            'datos_generales' => ['nullable', 'array'],
            'plan_entrenamiento' => ['nullable', 'array'],
            'plan_nutricional' => ['nullable', 'array'],
            'plan_habitos' => ['nullable', 'array'],
            'plan_suplementacion' => ['nullable', 'array'],
            'plan_ciclo' => ['nullable', 'array'],
            'notas_coach' => ['nullable', 'string'],
        ]);

        $client = Client::find($validated['client_id']);

        if (! $client) {
            return response()->json(['error' => 'Cliente no encontrado.'], 404);
        }

        $ticket = PlanTicket::create([
            'coach_id' => $coach->id,
            'coach_name' => $coach->name ?? $coach->username ?? 'Coach',
            'client_id' => $client->id,
            'client_name' => $client->name ?? 'Cliente',
            'plan_type' => $validated['plan_type'],
            'status' => PlanTicketStatus::Borrador->value,
            'datos_generales' => $validated['datos_generales'] ?? [],
            'plan_entrenamiento' => $validated['plan_entrenamiento'] ?? [],
            'plan_nutricional' => $validated['plan_nutricional'] ?? null,
            'plan_habitos' => $validated['plan_habitos'] ?? null,
            'plan_suplementacion' => $validated['plan_suplementacion'] ?? null,
            'plan_ciclo' => $validated['plan_ciclo'] ?? null,
            'notas_coach' => $validated['notas_coach'] ?? null,
        ]);

        return response()->json(['ticket' => $ticket], 201);
    }

    // ─── Show ───────────────────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $ticket = PlanTicket::forCoach($coach->id)->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        return response()->json(['ticket' => $ticket]);
    }

    // ─── Update ─────────────────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $ticket = PlanTicket::forCoach($coach->id)->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        if (! $ticket->is_editable) {
            return response()->json(['error' => 'Este ticket ya no se puede editar.'], 403);
        }

        $validated = $request->validate([
            'plan_type' => ['sometimes', 'string', Rule::in(array_column(PlanType::cases(), 'value'))],
            'datos_generales' => ['sometimes', 'array'],
            'plan_entrenamiento' => ['sometimes', 'array'],
            'plan_nutricional' => ['sometimes', 'nullable', 'array'],
            'plan_habitos' => ['sometimes', 'nullable', 'array'],
            'plan_suplementacion' => ['sometimes', 'nullable', 'array'],
            'plan_ciclo' => ['sometimes', 'nullable', 'array'],
            'notas_coach' => ['sometimes', 'nullable', 'string'],
        ]);

        $ticket->fill($validated);
        $ticket->save();

        return response()->json(['ticket' => $ticket->fresh()]);
    }

    // ─── Submit ─────────────────────────────────────────────────────────

    public function submit(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $ticket = PlanTicket::forCoach($coach->id)->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        if ($ticket->status !== PlanTicketStatus::Borrador) {
            return response()->json(['error' => 'Solo se pueden enviar tickets en borrador.'], 422);
        }

        $missing = $this->findMissingFields($ticket);

        if (! empty($missing)) {
            return response()->json([
                'error' => 'Ticket incompleto',
                'missing' => $missing,
            ], 422);
        }

        DB::transaction(function () use ($ticket) {
            $ticket->status = PlanTicketStatus::Pendiente;
            $ticket->submitted_at = now();
            $ticket->save();

            $this->notifyAdminsOfNewTicket($ticket);
        });

        return response()->json(['ticket' => $ticket->fresh()]);
    }

    // ─── Destroy ────────────────────────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $ticket = PlanTicket::forCoach($coach->id)->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        if ($ticket->status !== PlanTicketStatus::Borrador) {
            return response()->json(['error' => 'Solo se pueden eliminar tickets en borrador.'], 403);
        }

        $ticket->delete();

        return response()->json(['deleted' => true]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────

    /**
     * Check required fields per plan_type. Returns list of dotted field paths.
     */
    protected function findMissingFields(PlanTicket $ticket): array
    {
        $missing = [];

        $datos = $ticket->datos_generales ?? [];
        $entreno = $ticket->plan_entrenamiento ?? [];
        $nutricional = $ticket->plan_nutricional ?? [];
        $habitos = $ticket->plan_habitos ?? [];
        $suplementacion = $ticket->plan_suplementacion ?? [];
        $ciclo = $ticket->plan_ciclo ?? [];

        if (empty($datos['nombre'] ?? null)) {
            $missing[] = 'datos_generales.nombre';
        }

        if (empty($entreno['dias_semana'] ?? null)) {
            $missing[] = 'plan_entrenamiento.dias_semana';
        }

        if (empty($entreno['split'] ?? null)) {
            $missing[] = 'plan_entrenamiento.split';
        }

        if (empty($nutricional['objetivo'] ?? null)) {
            $missing[] = 'plan_nutricional.objetivo';
        }

        if (! $this->hasHabitosContent($habitos)) {
            $missing[] = 'plan_habitos';
        }

        if (! $this->hasSuplementacionContent($suplementacion)) {
            $missing[] = 'plan_suplementacion';
        }

        $planType = $ticket->plan_type instanceof PlanType
            ? $ticket->plan_type
            : PlanType::tryFrom((string) $ticket->plan_type);

        if ($planType === PlanType::Elite && ! $this->hasCicloContent($ciclo)) {
            $missing[] = 'plan_ciclo';
        }

        return $missing;
    }

    protected function hasHabitosContent(?array $habitos): bool
    {
        if (empty($habitos)) {
            return false;
        }

        if (! empty($habitos['areas_foco'] ?? null)) {
            return true;
        }

        $items = $habitos['habitos'] ?? null;

        return is_array($items) && count($items) > 0;
    }

    protected function hasSuplementacionContent(?array $suplementacion): bool
    {
        if (empty($suplementacion)) {
            return false;
        }

        if (! empty($suplementacion['objetivo'] ?? null)) {
            return true;
        }

        $items = $suplementacion['suplementos'] ?? null;

        return is_array($items) && count($items) > 0;
    }

    protected function hasCicloContent(?array $ciclo): bool
    {
        return ! empty($ciclo);
    }

    protected function notifyAdminsOfNewTicket(PlanTicket $ticket): void
    {
        $admins = Admin::query()
            ->whereIn('role', ['superadmin', 'admin', 'jefe'])
            ->get(['id']);

        $link = "/admin/plan-tickets/{$ticket->id}";
        $body = "{$ticket->coach_name} envio un ticket para {$ticket->client_name} ({$ticket->plan_type?->value})";

        foreach ($admins as $admin) {
            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id' => $admin->id,
                'type' => 'plan_ticket_submitted',
                'title' => 'Nuevo ticket de plan',
                'body' => $body,
                'link' => $link,
            ]);
        }
    }
}
