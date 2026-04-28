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
use App\Services\PlanTicketExportService;
use App\Services\PushNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function __construct(
        private readonly PlanTicketExportService $exportService,
    ) {}

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

        return response()->json($this->exportService->buildFullExport($ticket));
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

        if (! array_key_exists($section, PlanTicketExportService::SECTION_INSTRUCTIONS)) {
            return response()->json(['error' => 'Seccion invalida.'], 422);
        }

        if ($section === 'ciclo' && $ticket->plan_type !== PlanType::Elite) {
            return response()->json(['error' => 'Ciclo solo disponible para Elite.'], 422);
        }

        return response()->json($this->exportService->buildSectionExport($ticket, $section));
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

        // Idempotencia: si el ticket ya está en el estado solicitado, devolver 200 sin
        // re-disparar notificaciones ni timestamps. Protege contra dobles clicks o
        // requests retransmitidos por la red.
        if ($ticket->status === $newStatus) {
            return response()->json(['ticket' => $ticket->fresh(), 'idempotent' => true]);
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

            if ($newStatus === PlanTicketStatus::Completado) {
                $this->notifyClientOfCompletedPlan($ticket);
            }
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

    // ─── Stats v2 (period-aware) ──────────────────────────────────────────

    public function stats(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $periodParam = $request->query('period', 'month');
        $period = in_array($periodParam, ['week', 'month', 'quarter', 'year'], true)
            ? $periodParam
            : 'month';

        $since = match ($period) {
            'week'    => now()->subDays(6)->startOfDay(),
            'quarter' => now()->subDays(89)->startOfDay(),
            'year'    => now()->subDays(364)->startOfDay(),
            default   => now()->subDays(29)->startOfDay(),
        };

        $created = PlanTicket::whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $since)->count();

        $approved = PlanTicket::where('status', PlanTicketStatus::Completado->value)
            ->whereNotNull('completed_at')->where('completed_at', '>=', $since)->count();

        $rejected = PlanTicket::where('status', PlanTicketStatus::Rechazado->value)
            ->whereNotNull('rejected_at')->where('rejected_at', '>=', $since)->count();

        $avgTimeHours = (float) PlanTicket::where('status', PlanTicketStatus::Completado->value)
            ->whereNotNull('submitted_at')->whereNotNull('completed_at')
            ->where('completed_at', '>=', $since)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, submitted_at, completed_at)) / 60 as h')
            ->value('h');

        $rejCodes = PlanTicket::where('status', PlanTicketStatus::Rechazado->value)
            ->whereNotNull('rejected_at')->where('rejected_at', '>=', $since)
            ->whereNotNull('rejection_code')
            ->select('rejection_code', DB::raw('COUNT(*) as cnt'))
            ->groupBy('rejection_code')->orderByDesc('cnt')->limit(5)->get();

        $totalRejCodes = $rejCodes->sum('cnt');
        $rejectionReasons = $rejCodes->map(fn ($r) => [
            'code'  => $r->rejection_code,
            'label' => self::rejectionCodeLabel($r->rejection_code),
            'count' => (int) $r->cnt,
            'pct'   => $totalRejCodes > 0 ? round($r->cnt / $totalRejCodes * 100, 1) : 0.0,
        ])->values()->toArray();

        return response()->json([
            'period'             => $period,
            'kpis'               => [
                'created'        => $created,
                'approved'       => $approved,
                'rejected'       => $rejected,
                'avg_time_hours' => round($avgTimeHours, 1),
            ],
            'throughput'         => $this->buildThroughput($since, $period),
            'coach_ranking'      => $this->buildCoachRanking($since),
            'rejection_reasons'  => $rejectionReasons,
            'resolution_buckets' => $this->buildResolutionBuckets($since),
        ]);
    }

    private function buildThroughput(\Carbon\Carbon $since, string $period): array
    {
        if ($period === 'year') {
            $tSub = PlanTicket::whereNotNull('submitted_at')->where('submitted_at', '>=', $since)
                ->selectRaw("DATE_FORMAT(submitted_at, '%Y-%m') as m, COUNT(*) as c")
                ->groupBy('m')->pluck('c', 'm')->toArray();
            $tApp = PlanTicket::whereNotNull('completed_at')->where('completed_at', '>=', $since)
                ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as m, COUNT(*) as c")
                ->groupBy('m')->pluck('c', 'm')->toArray();
            $tRej = PlanTicket::whereNotNull('rejected_at')->where('rejected_at', '>=', $since)
                ->selectRaw("DATE_FORMAT(rejected_at, '%Y-%m') as m, COUNT(*) as c")
                ->groupBy('m')->pluck('c', 'm')->toArray();

            $result = [];
            for ($i = 11; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $result[] = ['date' => $key, 'created' => (int) ($tSub[$key] ?? 0), 'approved' => (int) ($tApp[$key] ?? 0), 'rejected' => (int) ($tRej[$key] ?? 0)];
            }
            return $result;
        }

        $days = match ($period) {
            'week'    => 7,
            'quarter' => 90,
            default   => 30,
        };

        $tSub = PlanTicket::whereNotNull('submitted_at')->where('submitted_at', '>=', $since)
            ->selectRaw('DATE(submitted_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd')->toArray();
        $tApp = PlanTicket::whereNotNull('completed_at')->where('completed_at', '>=', $since)
            ->selectRaw('DATE(completed_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd')->toArray();
        $tRej = PlanTicket::whereNotNull('rejected_at')->where('rejected_at', '>=', $since)
            ->selectRaw('DATE(rejected_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd')->toArray();

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($days - 1 - $i)->toDateString();
            $result[] = ['date' => $date, 'created' => (int) ($tSub[$date] ?? 0), 'approved' => (int) ($tApp[$date] ?? 0), 'rejected' => (int) ($tRej[$date] ?? 0)];
        }
        return $result;
    }

    private function buildCoachRanking(\Carbon\Carbon $since): array
    {
        $sinceStr = $since->toDateTimeString();

        $rows = DB::table('plan_tickets')
            ->select('coach_id', DB::raw('MAX(coach_name) as name'))
            ->selectRaw("SUM(CASE WHEN status = 'completado' AND completed_at >= ? THEN 1 ELSE 0 END) as approved_count", [$sinceStr])
            ->selectRaw("SUM(CASE WHEN status = 'rechazado' AND rejected_at >= ? THEN 1 ELSE 0 END) as rejected_count", [$sinceStr])
            ->whereNotNull('coach_id')
            ->groupBy('coach_id')
            ->orderByDesc('approved_count')
            ->get();

        return $rows->filter(fn ($r) => (int) $r->approved_count > 0 || (int) $r->rejected_count > 0)
            ->map(function ($r) {
                $approved = (int) $r->approved_count;
                $rejected = (int) $r->rejected_count;
                $total    = $approved + $rejected;
                return [
                    'coach_id'       => (int) $r->coach_id,
                    'name'           => $r->name,
                    'approved_count' => $approved,
                    'rejected_count' => $rejected,
                    'rejection_pct'  => $total > 0 ? round($rejected / $total * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->toArray();
    }

    private function buildResolutionBuckets(\Carbon\Carbon $since): array
    {
        $minutes = DB::table('plan_tickets')
            ->whereIn('status', [PlanTicketStatus::Completado->value, PlanTicketStatus::Rechazado->value])
            ->whereNotNull('submitted_at')
            ->where(function ($q) use ($since) {
                $q->where(fn ($q2) => $q2->whereNotNull('completed_at')->where('completed_at', '>=', $since))
                  ->orWhere(fn ($q2) => $q2->whereNotNull('rejected_at')->where('rejected_at', '>=', $since));
            })
            ->selectRaw('TIMESTAMPDIFF(MINUTE, submitted_at, COALESCE(completed_at, rejected_at)) as mins')
            ->pluck('mins')
            ->map(fn ($v) => max(0, (int) $v))
            ->values()
            ->toArray();

        $defs = [
            ['bucket' => '<2h',    'min' => 0,     'max' => 119],
            ['bucket' => '2-6h',   'min' => 120,   'max' => 359],
            ['bucket' => '6-12h',  'min' => 360,   'max' => 719],
            ['bucket' => '12-24h', 'min' => 720,   'max' => 1439],
            ['bucket' => '1-3d',   'min' => 1440,  'max' => 4319],
            ['bucket' => '3-7d',   'min' => 4320,  'max' => 10079],
            ['bucket' => '+7d',    'min' => 10080, 'max' => PHP_INT_MAX],
        ];

        $counts = array_fill(0, count($defs), 0);
        foreach ($minutes as $m) {
            foreach ($defs as $idx => $d) {
                if ($m >= $d['min'] && $m <= $d['max']) { $counts[$idx]++; break; }
            }
        }

        $total  = count($minutes);
        $result = [];
        foreach ($defs as $idx => $d) {
            $result[] = ['bucket' => $d['bucket'], 'count' => $counts[$idx], 'pct' => $total > 0 ? round($counts[$idx] / $total * 100, 1) : 0.0];
        }

        return ['buckets' => $result, 'stats' => $this->computeTimeStats($minutes), 'total' => $total];
    }

    private function computeTimeStats(array $minutes): array
    {
        if (empty($minutes)) {
            return ['mean_hours' => null, 'median_hours' => null, 'p90_hours' => null];
        }
        sort($minutes);
        $n      = count($minutes);
        $mean   = array_sum($minutes) / $n;
        $mid    = intdiv($n, 2);
        $median = $n % 2 === 0 ? ($minutes[$mid - 1] + $minutes[$mid]) / 2 : $minutes[$mid];
        $p90    = $minutes[min((int) ceil(0.9 * $n) - 1, $n - 1)];
        return [
            'mean_hours'   => round($mean / 60, 1),
            'median_hours' => round($median / 60, 1),
            'p90_hours'    => round($p90 / 60, 1),
        ];
    }

    private static function rejectionCodeLabel(string $code): string
    {
        return match ($code) {
            'info_incompleta'            => 'Info incompleta',
            'contexto_insuficiente'      => 'Contexto insuficiente',
            'conflicto_datos'            => 'Conflicto de datos',
            'fuera_de_scope'             => 'Fuera de scope',
            'necesita_validacion_medica' => 'Validacion medica',
            'otro'                       => 'Otro',
            default                      => $code,
        };
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

    /**
     * Notify the client that their personalized plan is ready.
     *
     * Creates a bell notification + sends a web-push if the client has an active
     * subscription. Push failures are logged but never break the transaction.
     */
    protected function notifyClientOfCompletedPlan(PlanTicket $ticket): void
    {
        if (! $ticket->client_id) {
            return;
        }

        WellcoreNotification::create([
            'user_type' => 'client',
            'user_id' => $ticket->client_id,
            'type' => 'plan_ready',
            'title' => 'Tu nuevo plan esta listo',
            'body' => 'Abre la app y revisa tu plan personalizado.',
            'link' => '/client/plan',
        ]);

        try {
            (new PushNotificationService)->send($ticket->client_id, [
                'title' => 'Tu nuevo plan esta listo',
                'body' => 'Abre la app y revisa tu plan personalizado.',
                'icon' => '/images/logo-dark.png',
                'badge' => '/icons/icon-192x192.png',
                'tag' => 'plan-ready',
                'data' => ['url' => '/client/plan', 'type' => 'plan_ready'],
                'actions' => [
                    ['action' => 'open', 'title' => 'Ver plan'],
                    ['action' => 'dismiss', 'title' => 'Despues'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Push notification to client failed', [
                'client_id' => $ticket->client_id,
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
