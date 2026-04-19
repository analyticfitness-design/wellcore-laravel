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
use App\Models\PlanTicketComment;
use App\Models\WellcoreNotification;
use App\Services\ClientAutofillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoachPlanTicketController extends Controller
{
    use AuthenticatesVueRequests;

    public function __construct(
        private readonly ClientAutofillService $autofill,
    ) {}

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

        $lockedStatuses = [
            PlanTicketStatus::EnRevision,
            PlanTicketStatus::Completado,
            PlanTicketStatus::Rechazado,
        ];

        if (in_array($ticket->status, $lockedStatuses, true)) {
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

        if ($ticket->status === PlanTicketStatus::Pendiente) {
            $ticket->resubmitted_at = now();
        }

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
            $ticket->deadline_at = now()->addHours(72);
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

    // ─── Duplicate ──────────────────────────────────────────────────────

    public function duplicate(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $ticket = PlanTicket::forCoach($coach->id)->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        $clone = PlanTicket::create([
            'coach_id' => $coach->id,
            'coach_name' => $ticket->coach_name,
            'client_id' => $ticket->client_id,
            'client_name' => $ticket->client_name,
            'plan_type' => $ticket->plan_type?->value,
            'status' => PlanTicketStatus::Borrador->value,
            'datos_generales' => $ticket->datos_generales,
            'plan_entrenamiento' => $ticket->plan_entrenamiento,
            'plan_nutricional' => $ticket->plan_nutricional,
            'plan_habitos' => $ticket->plan_habitos,
            'plan_suplementacion' => $ticket->plan_suplementacion,
            'plan_ciclo' => $ticket->plan_ciclo,
            'notas_coach' => $ticket->notas_coach,
            'parent_ticket_id' => $ticket->id,
        ]);

        return response()->json(['ticket' => $clone], 201);
    }

    // ─── Autofill ───────────────────────────────────────────────────────

    public function autofill(Request $request): JsonResponse
    {
        $this->resolveCoachOrFail($request);

        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
        ]);

        return response()->json($this->autofill->forClient((int) $validated['client_id']));
    }

    // ─── Comments ───────────────────────────────────────────────────────

    public function listComments(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $ticket = PlanTicket::forCoach($coach->id)->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        return response()->json(['comments' => $ticket->comments()->get()]);
    }

    public function addComment(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $ticket = PlanTicket::forCoach($coach->id)->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment = DB::transaction(function () use ($ticket, $coach, $validated) {
            $comment = PlanTicketComment::create([
                'plan_ticket_id' => $ticket->id,
                'author_type' => 'coach',
                'author_id' => $coach->id,
                'author_name' => $coach->name ?? $coach->username ?? 'Coach',
                'body' => $validated['body'],
            ]);

            $this->notifyAdminsOfCoachComment($ticket, $coach);

            return $comment;
        });

        return response()->json(['comment' => $comment], 201);
    }

    // ─── Coach Notifications ────────────────────────────────────────────

    public function notifications(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $rows = WellcoreNotification::where('user_type', 'admin')
            ->where('user_id', $coach->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get(['id', 'type', 'title', 'body', 'link', 'read_at', 'created_at']);

        $notifications = $rows->map(fn ($n) => [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'body' => $n->body,
            'link' => $n->link,
            'read_at' => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at?->diffForHumans(),
        ])->toArray();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $rows->whereNull('read_at')->count(),
        ]);
    }

    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $updated = WellcoreNotification::where('id', $id)
            ->where('user_type', 'admin')
            ->where('user_id', $coach->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (! $updated) {
            return response()->json(['message' => 'Notificacion no encontrada o ya leida.'], 404);
        }

        return response()->json(['message' => 'Notificacion marcada como leida.']);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        WellcoreNotification::where('user_type', 'admin')
            ->where('user_id', $coach->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leidas.']);
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

    protected function notifyAdminsOfCoachComment(PlanTicket $ticket, Admin $coach): void
    {
        $admins = Admin::query()
            ->whereIn('role', ['superadmin', 'admin', 'jefe'])
            ->get(['id']);

        $title = "Nuevo comentario en ticket #{$ticket->id}";
        $body = "{$coach->name} comento en ticket de {$ticket->client_name}";
        $link = "/admin/plan-tickets/{$ticket->id}";

        foreach ($admins as $admin) {
            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id' => $admin->id,
                'type' => 'plan_ticket_comment',
                'title' => $title,
                'body' => $body,
                'link' => $link,
            ]);
        }
    }
}
