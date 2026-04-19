<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanTicketStatus;
use App\Enums\PlanType;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PlanTicket;
use App\Models\PlanTicketAttachment;
use App\Models\PlanTicketComment;
use App\Models\WellcoreNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminPlanTicketController extends Controller
{
    use AuthenticatesVueRequests;

    private const REJECTION_CODES = [
        'info_incompleta',
        'contexto_insuficiente',
        'conflicto_datos',
        'fuera_de_scope',
        'necesita_validacion_medica',
        'otro',
    ];

    private const SECTION_INSTRUCTIONS = [
        'entrenamiento' => 'Formato JSON entrenamiento con semanas[].dias[].ejercicios[]. Cada ejercicio incluir gif_url del catálogo CATALOGO_GIF_265.md. Seguir metodología en TESIS_ENTRENAMIENTO_WELLCORE.md.',
        'nutricion' => 'Formato con comidas_sugeridas[], opciones en formato "Opción N: ing1 (Xg) + ing2 (Yg) + ing3 (Zg)". Backend separa automáticamente por " + ". Seguir TESIS_NUTRICION_WELLCORE.md.',
        'habitos' => 'Formato con areas_foco[] y habitos[] estructurados. Incluir frecuencia, descripción y meta medible.',
        'suplementacion' => 'Formato con suplementos[] de nombre/dosis/momento/frecuencia.',
        'ciclo' => 'Solo Elite. Formato con compounds, phases, labs, pct. Seguir METODOLOGIAS_ELITE_COMPLETAS.md.',
    ];

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

    // ─── Export JSON (full) ─────────────────────────────────────────────

    public function exportJson(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        $planType = $ticket->plan_type?->value;

        $payload = [
            'ticket_id' => $ticket->id,
            'client' => [
                'id' => $ticket->client_id,
                'name' => $ticket->client_name,
            ],
            'coach' => $ticket->coach_name,
            'plan_type' => $planType,
            'instructions' => [
                'global' => "Plan tipo {$planType} para cliente del sistema WellCore. Los 4-5 JSONs que siguen se suben separadamente via /api/v/admin/clients/{id}/plans. Usar catálogo de ejercicios en CATALOGO_GIF_265.md y metodologías en TESIS_ENTRENAMIENTO_WELLCORE.md + TESIS_NUTRICION_WELLCORE.md + METODOLOGIAS_ELITE_COMPLETAS.md.",
                'entrenamiento' => self::SECTION_INSTRUCTIONS['entrenamiento'],
                'nutricion' => self::SECTION_INSTRUCTIONS['nutricion'],
                'habitos' => self::SECTION_INSTRUCTIONS['habitos'],
                'suplementacion' => self::SECTION_INSTRUCTIONS['suplementacion'],
                'ciclo' => self::SECTION_INSTRUCTIONS['ciclo'],
            ],
            'sections' => [
                'entrenamiento' => $ticket->plan_entrenamiento ?? (object) [],
                'nutricion' => $ticket->plan_nutricional ?? (object) [],
                'habitos' => $ticket->plan_habitos ?? (object) [],
                'suplementacion' => $ticket->plan_suplementacion ?? (object) [],
                'ciclo' => $ticket->plan_type === PlanType::Elite ? ($ticket->plan_ciclo ?? (object) []) : null,
            ],
            'datos_generales' => $ticket->datos_generales ?? (object) [],
            'notas_coach' => $ticket->notas_coach,
        ];

        return response()->json($payload);
    }

    // ─── Export Section ─────────────────────────────────────────────────

    public function exportSection(Request $request, int $id, string $section): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        if ($section === 'full') {
            return $this->exportJson($request, $id);
        }

        $map = [
            'entrenamiento' => $ticket->plan_entrenamiento,
            'nutricion' => $ticket->plan_nutricional,
            'habitos' => $ticket->plan_habitos,
            'suplementacion' => $ticket->plan_suplementacion,
            'ciclo' => $ticket->plan_ciclo,
        ];

        if (! array_key_exists($section, $map)) {
            return response()->json(['error' => 'Seccion invalida.'], 422);
        }

        if ($section === 'ciclo' && $ticket->plan_type !== PlanType::Elite) {
            return response()->json(['error' => 'Ciclo solo disponible para Elite.'], 422);
        }

        return response()->json([
            'client_id' => $ticket->client_id,
            'plan_type' => $ticket->plan_type?->value,
            'section' => $section,
            'instructions' => self::SECTION_INSTRUCTIONS[$section],
            'brief' => $map[$section] ?? (object) [],
        ]);
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
            'generated_plan_ids' => ['sometimes', 'array'],
            'generated_plan_ids.*' => ['integer'],
            'rejection_code' => ['sometimes', 'nullable', 'string', Rule::in(self::REJECTION_CODES)],
        ]);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        $newStatus = PlanTicketStatus::from($validated['status']);

        if ($newStatus === PlanTicketStatus::Rechazado && empty($validated['rejection_code'])) {
            return response()->json(['error' => 'rejection_code es requerido al rechazar.'], 422);
        }

        DB::transaction(function () use ($ticket, $newStatus, $validated, $admin) {
            $ticket->status = $newStatus;

            if (array_key_exists('admin_notas', $validated)) {
                $ticket->admin_notas = $validated['admin_notas'];
            }

            if ($newStatus === PlanTicketStatus::EnRevision) {
                $ticket->reviewed_at = now();
            } elseif ($newStatus === PlanTicketStatus::Completado) {
                $ticket->completed_at = now();

                if (! empty($validated['generated_plan_ids'])) {
                    $ticket->generated_plan_ids = array_values(array_map('intval', $validated['generated_plan_ids']));
                }
            } elseif ($newStatus === PlanTicketStatus::Rechazado) {
                $ticket->rejected_at = now();
                $ticket->rejection_code = $validated['rejection_code'];
            }

            $ticket->save();

            $this->notifyCoachOfStatusChange($ticket, $newStatus, $admin);
        });

        return response()->json(['ticket' => $ticket->fresh()]);
    }

    // ─── Comments ───────────────────────────────────────────────────────

    public function listComments(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        return response()->json(['comments' => $ticket->comments()->get()]);
    }

    public function addComment(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment = DB::transaction(function () use ($ticket, $admin, $validated) {
            $comment = PlanTicketComment::create([
                'plan_ticket_id' => $ticket->id,
                'author_type' => 'admin',
                'author_id' => $admin->id,
                'author_name' => $admin->name ?? $admin->username ?? 'Admin',
                'body' => $validated['body'],
            ]);

            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id' => $ticket->coach_id,
                'type' => 'plan_ticket_comment',
                'title' => 'El equipo comento tu ticket',
                'body' => "Comentario en ticket de {$ticket->client_name}",
                'link' => "/coach/plan-tickets/{$ticket->id}",
            ]);

            return $comment;
        });

        return response()->json(['comment' => $comment], 201);
    }

    // ─── Admin Notifications ────────────────────────────────────────────

    public function notifications(Request $request): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $paginator = WellcoreNotification::where('user_type', 'admin')
            ->where('user_id', $admin->id)
            ->orderByDesc('created_at')
            ->paginate(30, ['id', 'type', 'title', 'body', 'link', 'read_at', 'created_at']);

        $unreadCount = WellcoreNotification::where('user_type', 'admin')
            ->where('user_id', $admin->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $paginator->items(),
            'unread_count' => $unreadCount,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $updated = WellcoreNotification::where('id', $id)
            ->where('user_type', 'admin')
            ->where('user_id', $admin->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (! $updated) {
            return response()->json(['message' => 'Notificacion no encontrada o ya leida.'], 404);
        }

        return response()->json(['message' => 'Notificacion marcada como leida.']);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        WellcoreNotification::where('user_type', 'admin')
            ->where('user_id', $admin->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leidas.']);
    }

    // ─── Helpers ────────────────────────────────────────────────────────

    // ─── Attachments ────────────────────────────────────────────────────

    public function listAttachments(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        return response()->json(['attachments' => $ticket->attachments()->get()]);
    }

    public function deleteAttachment(Request $request, int $id, int $attId): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $attachment = PlanTicketAttachment::where('plan_ticket_id', $id)->find($attId);

        if (! $attachment) {
            return response()->json(['error' => 'Adjunto no encontrado.'], 404);
        }

        Storage::disk($attachment->disk ?: 'public')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['deleted' => true]);
    }

    // ─── Print view ─────────────────────────────────────────────────────

    public function printView(Request $request, int $id): View|JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = PlanTicket::with('attachments')->find($id);

        if (! $ticket) {
            return response()->json(['error' => 'Ticket no encontrado.'], 404);
        }

        return view('admin.plan-tickets.print', ['ticket' => $ticket]);
    }

    // ─── Stats ──────────────────────────────────────────────────────────

    public function stats(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $totalsRaw = PlanTicket::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totals = [
            'draft' => (int) ($totalsRaw[PlanTicketStatus::Borrador->value] ?? 0),
            'pendiente' => (int) ($totalsRaw[PlanTicketStatus::Pendiente->value] ?? 0),
            'en_revision' => (int) ($totalsRaw[PlanTicketStatus::EnRevision->value] ?? 0),
            'completado' => (int) ($totalsRaw[PlanTicketStatus::Completado->value] ?? 0),
            'rechazado' => (int) ($totalsRaw[PlanTicketStatus::Rechazado->value] ?? 0),
            'total' => (int) array_sum($totalsRaw),
        ];

        $avgCompleteHours = (float) PlanTicket::query()
            ->whereNotNull('submitted_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, submitted_at, completed_at)) / 60 as h')
            ->value('h');

        $avgReviewHours = (float) PlanTicket::query()
            ->whereNotNull('submitted_at')
            ->whereNotNull('reviewed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, submitted_at, reviewed_at)) / 60 as h')
            ->value('h');

        $overdueCount = PlanTicket::query()->overdue()->count();

        $perCoach = PlanTicket::query()
            ->select(
                'coach_id',
                DB::raw('MAX(coach_name) as coach_name'),
                DB::raw('SUM(CASE WHEN submitted_at IS NOT NULL THEN 1 ELSE 0 END) as submitted'),
                DB::raw("SUM(CASE WHEN status = '".PlanTicketStatus::Completado->value."' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = '".PlanTicketStatus::Rechazado->value."' THEN 1 ELSE 0 END) as rejected"),
                DB::raw('AVG(CASE WHEN submitted_at IS NOT NULL AND completed_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, submitted_at, completed_at) END) / 60 as avg_hours'),
            )
            ->groupBy('coach_id')
            ->get()
            ->map(function ($row) {
                $submitted = (int) $row->submitted;
                $rejected = (int) $row->rejected;

                return [
                    'coach_id' => (int) $row->coach_id,
                    'coach_name' => $row->coach_name,
                    'submitted' => $submitted,
                    'completed' => (int) $row->completed,
                    'rejected' => $rejected,
                    'rejection_rate_pct' => $submitted > 0 ? round(($rejected / $submitted) * 100, 2) : 0.0,
                    'avg_time_to_complete_hours' => $row->avg_hours !== null ? round((float) $row->avg_hours, 2) : null,
                ];
            })
            ->values()
            ->toArray();

        $perPlanRaw = PlanTicket::query()
            ->select('plan_type', DB::raw('COUNT(*) as total'))
            ->groupBy('plan_type')
            ->pluck('total', 'plan_type')
            ->toArray();

        $perPlanType = [
            'esencial' => (int) ($perPlanRaw[PlanType::Esencial->value] ?? 0),
            'metodo' => (int) ($perPlanRaw[PlanType::Metodo->value] ?? 0),
            'elite' => (int) ($perPlanRaw[PlanType::Elite->value] ?? 0),
        ];

        $since = now()->subDays(29)->startOfDay();

        $trendSubmitted = PlanTicket::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $since)
            ->selectRaw('DATE(submitted_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $trendCompleted = PlanTicket::query()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $since)
            ->selectRaw('DATE(completed_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $trendRejected = PlanTicket::query()
            ->whereNotNull('rejected_at')
            ->where('rejected_at', '>=', $since)
            ->selectRaw('DATE(rejected_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $trend = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays(29 - $i)->toDateString();
            $trend[] = [
                'date' => $date,
                'submitted' => (int) ($trendSubmitted[$date] ?? 0),
                'completed' => (int) ($trendCompleted[$date] ?? 0),
                'rejected' => (int) ($trendRejected[$date] ?? 0),
            ];
        }

        return response()->json([
            'totals' => $totals,
            'avg_time_submit_to_complete_hours' => round($avgCompleteHours, 2),
            'avg_time_to_review_hours' => round($avgReviewHours, 2),
            'overdue_count' => $overdueCount,
            'per_coach' => $perCoach,
            'per_plan_type' => $perPlanType,
            'trend_30d' => $trend,
        ]);
    }

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
