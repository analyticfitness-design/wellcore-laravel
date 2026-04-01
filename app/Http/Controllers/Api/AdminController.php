<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\AuthToken;
use App\Models\ChatMessage;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\CoachMessage;
use App\Models\CoachProfile;
use App\Models\Inscription;
use App\Models\Invitation;
use App\Models\Payment;
use App\Models\PlanTemplate;
use App\Models\Referral;
use App\Models\RiseMeasurement;
use App\Models\RiseProgram;
use App\Models\RiseTracking;
use App\Models\Ticket;
use App\Models\TrainingLog;
use App\Services\AIService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
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

    // ─── Dashboard ──────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/dashboard
     *
     * Admin dashboard: MRR, active clients, churn, growth, charts, timeline.
     * Ports Admin\Dashboard.php mount() + loadAllData() logic.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        // Summary stats (cached 5 min)
        $stats = Cache::remember('admin_dashboard_stats_api', 300, function () {
            return [
                'activeClients' => Client::where('status', 'activo')->count(),
                'monthlyRevenue' => number_format(
                    (float) Payment::where('status', 'approved')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('amount'),
                    0, ',', '.'
                ),
                'pendingCheckins' => Checkin::whereNull('coach_reply')->count(),
                'newInscriptions' => Inscription::where('created_at', '>=', now()->startOfMonth())->count(),
            ];
        });

        // Client breakdown
        $breakdown = Client::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $clientBreakdown = [
            'activo' => $breakdown->get('activo', 0),
            'inactivo' => $breakdown->get('inactivo', 0),
            'pendiente' => $breakdown->get('pendiente', 0),
            'suspendido' => $breakdown->get('suspendido', 0),
            'total' => $breakdown->sum(),
        ];

        // Recent inscriptions
        $recentInscriptions = Inscription::latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($i) => [
                'nombre' => trim(($i->nombre ?? '').' '.($i->apellido ?? '')),
                'email' => $i->email ?? '',
                'plan' => $i->plan?->label() ?? '-',
                'status' => $i->status ?? '-',
                'timeAgo' => $i->created_at?->diffForHumans() ?? '-',
            ])
            ->toArray();

        // Recent payments
        $recentPayments = Payment::with('client')
            ->where('status', 'approved')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($p) {
                $buyerName = $p->buyer_name ?? $p->email ?? $p->client?->name ?? 'Sin nombre';
                $planLabel = $p->plan instanceof PlanType
                    ? $p->plan->label()
                    : (filled($p->getRawOriginal('plan')) ? ucfirst($p->getRawOriginal('plan')) : '-');

                return [
                    'buyerName' => $buyerName,
                    'plan' => $planLabel,
                    'amount' => number_format((float) $p->amount, 0, ',', '.'),
                    'method' => $p->payment_method ?? '-',
                    'timeAgo' => $p->created_at?->diffForHumans() ?? '-',
                ];
            })
            ->toArray();

        // Chart data
        $revenueChartData = Cache::remember('admin_chart_revenue_api', 600, function () {
            return DB::table('payments')
                ->where('status', 'approved')
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(fn ($row) => ['month' => $row->month, 'total' => (float) $row->total])
                ->toArray();
        });

        $clientGrowthData = Cache::remember('admin_chart_growth_api', 600, function () {
            return DB::table('clients')
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(fn ($row) => ['month' => $row->month, 'count' => (int) $row->count])
                ->toArray();
        });

        $planDistributionData = Cache::remember('admin_chart_plan_dist_api', 600, function () {
            return DB::table('assigned_plans')
                ->where('active', 1)
                ->selectRaw('plan_type as name, COUNT(*) as count')
                ->groupBy('plan_type')
                ->get()
                ->map(fn ($row) => ['name' => ucfirst($row->name ?? 'Sin tipo'), 'count' => (int) $row->count])
                ->toArray();
        });

        // Pending rewards
        $pendingRewards = Referral::where('reward_granted', false)
            ->whereNotIn('status', ['denied'])
            ->with('referrer:id,name,email')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'referrer_name' => $r->referrer?->name ?? '-',
                'referred_email' => $r->referred_email,
                'status' => $r->status,
                'created_at' => $r->created_at?->diffForHumans(),
            ])
            ->toArray();

        return response()->json([
            'stats' => $stats,
            'clientBreakdown' => $clientBreakdown,
            'recentInscriptions' => $recentInscriptions,
            'recentPayments' => $recentPayments,
            'revenueChartData' => $revenueChartData,
            'clientGrowthData' => $clientGrowthData,
            'planDistributionData' => $planDistributionData,
            'pendingRewards' => $pendingRewards,
        ]);
    }

    // ─── Live Feed ──────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/feed
     *
     * Live activity feed with type/date filters.
     * Ports Admin\LiveFeed.php loadFeed() + loadStats() logic.
     */
    public function feed(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $typeFilter = $request->query('type', 'all');
        $dateFilter = $request->query('date', 'week');

        $typeFilter = match ($typeFilter) {
            'checkin' => 'checkins',
            'payment' => 'payments',
            'signup' => 'inscriptions',
            default => $typeFilter,
        };

        $tz = 'America/Bogota';
        $dateFrom = match ($dateFilter) {
            'today' => Carbon::today($tz)->utc(),
            'week' => Carbon::now($tz)->subWeek()->startOfDay()->utc(),
            'month' => Carbon::now($tz)->subMonth()->startOfDay()->utc(),
            default => null,
        };

        $items = collect();

        // Inscriptions
        if ($typeFilter === 'all' || $typeFilter === 'inscriptions') {
            $inscriptions = Inscription::when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')->limit(50)->get()
                ->map(fn ($i) => [
                    'type' => 'signup',
                    'icon' => 'clipboard-document-check',
                    'color' => 'sky',
                    'title' => 'Nueva inscripcion',
                    'clientName' => trim(($i->nombre ?? '').' '.($i->apellido ?? '')),
                    'description' => trim(($i->nombre ?? '').' '.($i->apellido ?? '')).' — '.($i->plan?->label() ?? 'Sin plan'),
                    'timestamp' => $i->created_at?->toIso8601String(),
                    'time_ago' => $i->created_at?->diffForHumans() ?? '-',
                    'time' => $i->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($inscriptions);
        }

        // Payments
        if ($typeFilter === 'all' || $typeFilter === 'payments') {
            $payments = Payment::when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')->limit(50)->get()
                ->map(fn ($p) => [
                    'type' => 'payment',
                    'icon' => 'banknotes',
                    'color' => 'emerald',
                    'title' => 'Pago recibido',
                    'clientName' => $p->buyer_name ?? $p->email ?? 'Desconocido',
                    'description' => ($p->buyer_name ?? $p->email ?? 'Desconocido').' — $'.number_format((float) $p->amount, 0, ',', '.').' COP',
                    'timestamp' => $p->created_at?->toIso8601String(),
                    'time_ago' => $p->created_at?->diffForHumans() ?? '-',
                    'time' => $p->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($payments);
        }

        // Checkins
        if ($typeFilter === 'all' || $typeFilter === 'checkins') {
            $checkins = Checkin::with('client')
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')->limit(50)->get()
                ->map(fn ($c) => [
                    'type' => 'checkin',
                    'icon' => 'clipboard-document-list',
                    'color' => 'orange',
                    'title' => 'Check-in enviado',
                    'clientName' => $c->client?->name ?? 'Cliente',
                    'description' => ($c->client?->name ?? 'Cliente').' — Bienestar: '.($c->bienestar ?? '-').'/10',
                    'timestamp' => $c->created_at?->toIso8601String(),
                    'time_ago' => $c->created_at?->diffForHumans() ?? '-',
                    'time' => $c->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($checkins);
        }

        // Messages
        if ($typeFilter === 'all' || $typeFilter === 'messages') {
            $messages = CoachMessage::with(['client', 'coach'])
                ->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')->limit(50)->get()
                ->map(fn ($m) => [
                    'type' => 'message',
                    'icon' => 'chat-bubble-left-right',
                    'color' => 'violet',
                    'title' => 'Nuevo mensaje',
                    'clientName' => $m->direction === 'client_to_coach'
                        ? ($m->client?->name ?? 'Cliente')
                        : 'Coach '.($m->coach?->name ?? ''),
                    'description' => ($m->direction === 'coach_to_client'
                        ? ('Coach '.($m->coach?->name ?? '').' → '.($m->client?->name ?? 'Cliente'))
                        : (($m->client?->name ?? 'Cliente').' → Coach')).' — '.Str::limit($m->message ?? '', 60),
                    'timestamp' => $m->created_at?->toIso8601String(),
                    'time_ago' => $m->created_at?->diffForHumans() ?? '-',
                    'time' => $m->created_at?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($messages);
        }

        // Training
        if ($typeFilter === 'all' || $typeFilter === 'training') {
            $training = TrainingLog::with('client')
                ->when($dateFrom, fn ($q) => $q->where('log_date', '>=', $dateFrom->toDateString()))
                ->latest('log_date')->limit(50)->get()
                ->map(fn ($t) => [
                    'type' => 'training',
                    'icon' => 'fire',
                    'color' => 'yellow',
                    'title' => 'Entrenamiento',
                    'clientName' => $t->client?->name ?? 'Cliente',
                    'description' => ($t->client?->name ?? 'Cliente').' — '.($t->completed ? 'Completado' : 'Registrado'),
                    'timestamp' => $t->log_date?->startOfDay()->toIso8601String(),
                    'time_ago' => $t->log_date?->diffForHumans() ?? '-',
                    'time' => $t->log_date?->diffForHumans() ?? '-',
                ]);
            $items = $items->merge($training);
        }

        $feed = $items->sortByDesc('timestamp')->take(50)->values()->toArray();

        // Stats
        $today = Carbon::today('America/Bogota');
        $feedStats = [
            'eventsToday' => Inscription::where('created_at', '>=', $today)->count()
                + Payment::where('created_at', '>=', $today)->count()
                + Checkin::where('created_at', '>=', $today)->count()
                + CoachMessage::where('created_at', '>=', $today)->count(),
            'actionsToday' => Inscription::where('created_at', '>=', $today)->count(),
            'paymentsToday' => Payment::where('created_at', '>=', $today)->count(),
            'activeNow' => CoachMessage::where('created_at', '>=', $today)->distinct('client_id')->count('client_id'),
        ];

        return response()->json([
            'feed' => $feed,
            'stats' => $feedStats,
        ]);
    }

    // ─── Clients ────────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/clients
     *
     * Client table with search/filter/sort/pagination.
     * Ports Admin\ClientTable.php render() logic.
     */
    public function clients(Request $request): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $search = $request->query('search', '');
        $planFilter = $request->query('plan', '');
        $statusFilter = $request->query('status', '');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $perPage = $request->integer('per_page', 25);

        $query = Client::query()
            ->addSelect([
                'last_login_at' => AuthToken::select('created_at')
                    ->whereColumn('user_id', 'clients.id')
                    ->where('user_type', 'client')
                    ->latest('created_at')
                    ->limit(1),
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('client_code', 'like', "%{$search}%");
            });
        }

        if ($planFilter !== '') {
            $query->where('plan', $planFilter);
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $allowedSorts = ['name', 'email', 'client_code', 'plan', 'status', 'created_at', 'fecha_inicio'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');

        $paginated = $query->paginate($perPage);

        $isSuperadmin = $admin->role === UserRole::Superadmin
            || $admin->role?->value === 'superadmin';

        return response()->json([
            'clients' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
            'isSuperadmin' => $isSuperadmin,
        ]);
    }

    /**
     * GET /api/v/admin/clients/{id}
     *
     * Client detail.
     * Ports Admin\ClientDetail.php mount() logic.
     */
    public function clientDetail(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $client = Client::findOrFail($id);

        // Assigned plans (for edit form)
        $plans = AssignedPlan::where('client_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'plan_type' => $p->plan_type,
                'active' => (bool) $p->active,
                'version' => $p->version,
                'assigned_by' => $p->assigned_by,
                'created_at' => $p->created_at?->format('d M Y'),
            ]);

        // Available coaches (for edit form)
        $coaches = Admin::where('role', 'coach')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Coach name: derive from the most recent assigned plan's assigned_by
        $latestPlan = AssignedPlan::where('client_id', $id)
            ->whereNotNull('assigned_by')
            ->orderByDesc('created_at')
            ->first();
        $coachName = $latestPlan?->assigned_by
            ? Admin::find($latestPlan->assigned_by)?->name
            : null;

        // Metrics
        $totalWorkouts = $client->trainingLogs()->count();
        $completedWorkouts = $client->trainingLogs()->where('completed', true)->count();
        $adherence = $totalWorkouts > 0
            ? round(($completedWorkouts / $totalWorkouts) * 100)
            : 0;

        // registeredAt: prefer fecha_inicio, fall back to created_at
        $registeredAt = $client->fecha_inicio
            ? Carbon::parse($client->fecha_inicio)->format('d M Y')
            : $client->created_at?->format('d M Y');

        // planDetails: current week from fecha_inicio
        $startDate = $client->fecha_inicio ? Carbon::parse($client->fecha_inicio) : null;
        $currentWeek = $startDate
            ? (int) max(1, $startDate->diffInWeeks(now()) + 1)
            : null;

        // Latest 10 check-ins
        $checkins = $client->checkins()
            ->orderByDesc('checkin_date')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'date' => $c->checkin_date?->format('d M Y'),
                'reviewed' => $c->coach_reply !== null,
                'note' => $c->comentario,
            ]);

        // Latest 10 payments
        $payments = $client->payments()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'description' => $p->plan?->label() ?? $p->plan,
                'date' => $p->created_at?->format('d M Y'),
                'amount' => $p->amount,
                'currency' => $p->currency ?? 'COP',
                'status' => $p->status?->value ?? $p->status,
            ]);

        // Last login from auth_tokens
        $lastLogin = AuthToken::where('user_id', $id)
            ->where('user_type', UserType::Client)
            ->orderByDesc('created_at')
            ->value('created_at');

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone ?? null,
                'plan' => $client->plan?->value ?? '',
                'plan_label' => $client->plan?->label() ?? 'Sin plan',
                'status' => $client->status?->value ?? '',
                'status_label' => $client->status?->label() ?? 'Desconocido',
                'fecha_inicio' => $client->fecha_inicio
                    ? Carbon::parse($client->fecha_inicio)->format('d M Y')
                    : null,
                'created_at' => $client->created_at?->format('d M Y'),
                'client_code' => $client->client_code ?? null,
                'city' => $client->city ?? null,
                'birth_date' => $client->birth_date
                    ? Carbon::parse($client->birth_date)->format('d/m/Y')
                    : null,
                'referral_code' => $client->referral_code ?? null,
                'referred_by' => $client->referred_by ?? null,
                'bio' => $client->bio ?? null,
                'avatar_url' => $client->avatar_url ?? null,
                // Vue ClientDetail.vue fields
                'coachName' => $coachName,
                'country' => $client->city ?? null,
                'registeredAt' => $registeredAt,
                // Summary stats
                'stats' => [
                    'checkins_count' => $client->checkins()->count(),
                    'approved_payments' => $client->payments()->where('status', 'approved')->count(),
                    'active_plans' => AssignedPlan::where('client_id', $id)->where('active', true)->count(),
                    'progress_photos' => $client->progressPhotos()->count(),
                ],
                'planDetails' => [
                    'name' => $client->plan?->label() ?? null,
                    'startDate' => $startDate?->format('d M Y'),
                    'currentWeek' => $currentWeek,
                    'totalWeeks' => null,
                ],
                'metrics' => [
                    'totalWorkouts' => $totalWorkouts,
                    'adherence' => $adherence,
                    'streak' => 0,
                ],
                'checkins' => $checkins,
                'payments' => $payments,
                'lastLogin' => $lastLogin
                    ? Carbon::parse($lastLogin)->format('d M Y H:i')
                    : null,
            ],
            'plans' => $plans,
            'coaches' => $coaches,
            'statusOptions' => array_map(
                fn ($s) => ['value' => $s->value, 'label' => $s->label()],
                ClientStatus::cases()
            ),
            'planOptions' => array_map(
                fn ($p) => ['value' => $p->value, 'label' => $p->label()],
                PlanType::cases()
            ),
        ]);
    }

    /**
     * PUT /api/v/admin/clients/{id}
     *
     * Update client (status, plan, coach assignment).
     * Ports Admin\ClientDetail.php updateStatus() / updatePlan() / assignCoach() logic.
     */
    public function updateClient(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string',
            'plan' => 'nullable|string',
            'coach_id' => 'nullable|integer',
            'assign_plan_type' => 'nullable|string',
        ]);

        $messages = [];

        if (isset($validated['status']) && $validated['status'] !== '') {
            $client->update(['status' => $validated['status']]);
            $messages[] = 'Estado actualizado a '.ClientStatus::from($validated['status'])->label();
        }

        if (isset($validated['plan']) && $validated['plan'] !== '') {
            $client->update(['plan' => $validated['plan']]);
            $messages[] = 'Plan actualizado a '.PlanType::from($validated['plan'])->label();
        }

        if (isset($validated['coach_id']) && $validated['coach_id'] > 0) {
            $coach = Admin::where('id', $validated['coach_id'])->where('role', 'coach')->first();
            if (! $coach) {
                return response()->json(['error' => 'Coach no encontrado.'], 404);
            }

            $planType = $validated['assign_plan_type'] ?? 'entrenamiento';

            AssignedPlan::where('client_id', $id)
                ->where('plan_type', $planType)
                ->where('active', true)
                ->update(['active' => false]);

            AssignedPlan::create([
                'client_id' => $id,
                'plan_type' => $planType,
                'content' => json_encode(['coach_assigned' => true, 'notes' => 'Asignado desde admin panel']),
                'version' => 1,
                'active' => true,
                'assigned_by' => $coach->id,
            ]);

            $messages[] = 'Coach '.$coach->name.' asignado.';
        }

        return response()->json([
            'updated' => true,
            'messages' => $messages,
        ]);
    }

    // ─── Payments ───────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/payments
     *
     * Payments dashboard with stats and paginated list.
     * Ports Admin\PaymentsDashboard.php mount() + render() logic.
     */
    public function payments(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $statusFilter = $request->query('status', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo = $request->query('date_to', '');
        $perPage = $request->integer('per_page', 25);

        // Stats
        $totalRevenue = number_format((float) Payment::where('status', 'approved')->sum('amount'), 0, ',', '.');
        $monthRevenue = number_format(
            (float) Payment::where('status', 'approved')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            0, ',', '.'
        );
        $pendingPayments = Payment::where('status', 'pending')->count();

        $activeClients = Client::where('status', 'activo')->count();
        $totalApproved = (float) Payment::where('status', 'approved')->sum('amount');
        $avgPerClient = $activeClients > 0 ? number_format($totalApproved / $activeClients, 0, ',', '.') : '0';

        // Payments list
        $query = Payment::query()->with('client');

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $paginated = $query->latest('created_at')->paginate($perPage);

        $payments = collect($paginated->items())->map(fn ($p) => [
            'id' => $p->id,
            'buyer_name' => $p->buyer_name ?? $p->email ?? $p->client?->name ?? 'Sin nombre',
            'client_name' => $p->client?->name ?? null,
            'amount' => (float) $p->amount,
            'amount_fmt' => number_format((float) $p->amount, 0, ',', '.'),
            'status' => $p->status?->value ?? $p->status ?? '-',
            'payment_method' => $p->payment_method ?? '-',
            'plan' => $p->plan?->label() ?? $p->getRawOriginal('plan') ?? '-',
            'created_at' => $p->created_at?->format('d M Y H:i'),
            'time_ago' => $p->created_at?->diffForHumans(),
        ]);

        return response()->json([
            'stats' => [
                'totalRevenue' => $totalRevenue,
                'monthRevenue' => $monthRevenue,
                'pendingPayments' => $pendingPayments,
                'avgPerClient' => $avgPerClient,
            ],
            'payments' => $payments,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    // ─── Coaches ────────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/coaches
     *
     * Coach management list.
     * Ports Admin\CoachManagement.php render() logic.
     */
    public function coaches(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $search = $request->query('search', '');
        $roleFilter = $request->query('role', 'all');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');

        $query = Admin::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $allowedSorts = ['name', 'username', 'role', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $admins = $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc')
            ->paginate(25);

        $adminsData = collect($admins->items())->map(function ($admin) {
            $profile = CoachProfile::where('admin_id', $admin->id)->first();
            $clientCount = AssignedPlan::where('assigned_by', $admin->id)->distinct('client_id')->count('client_id');

            return [
                'id' => $admin->id,
                'name' => $admin->name,
                'username' => $admin->username,
                'role' => $admin->role?->value ?? $admin->role ?? '',
                'role_label' => $admin->role?->label() ?? ucfirst($admin->role ?? ''),
                'client_count' => $clientCount,
                'has_profile' => $profile !== null,
                'city' => $profile?->city,
                'referral_code' => $profile?->referral_code,
                'created_at' => $admin->created_at?->format('d M Y'),
            ];
        });

        return response()->json([
            'coaches' => $adminsData,
            'pagination' => [
                'current_page' => $admins->currentPage(),
                'last_page' => $admins->lastPage(),
                'total' => $admins->total(),
            ],
        ]);
    }

    /**
     * POST /api/v/admin/coaches
     *
     * Add coach.
     * Ports Admin\CoachManagement.php createCoach() logic.
     */
    public function addCoach(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:admins,username',
            'password' => 'required|string|min:8|max:255',
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Create coach profile
        if ($validated['role'] === 'coach') {
            CoachProfile::create([
                'admin_id' => $admin->id,
                'slug' => Str::slug($admin->name).'-'.Str::random(4),
                'referral_code' => strtoupper(Str::random(8)),
                'color_primary' => '#E31E24',
                'public_visible' => true,
            ]);
        }

        return response()->json(['created' => true, 'id' => $admin->id], 201);
    }

    /**
     * PUT /api/v/admin/coaches/{id}
     *
     * Update coach.
     * Ports Admin\CoachManagement.php edit flow.
     */
    public function updateCoach(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $admin = Admin::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'role' => ['nullable', Rule::in(array_column(UserRole::cases(), 'value'))],
            'password' => 'nullable|string|min:8|max:255',
            'bio' => 'nullable|string|max:2000',
            'city' => 'nullable|string|max:100',
            'experience' => 'nullable|string|max:100',
            'specializations' => 'nullable|string|max:500',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'referral_code' => 'nullable|string|max:20',
            'referral_commission' => 'nullable|string|max:10',
            'public_visible' => 'nullable|boolean',
        ]);

        // Update admin record
        $adminUpdates = array_filter([
            'name' => $validated['name'] ?? null,
            'role' => $validated['role'] ?? null,
        ], fn ($v) => $v !== null);

        if (isset($validated['password'])) {
            $adminUpdates['password'] = Hash::make($validated['password']);
        }

        if (! empty($adminUpdates)) {
            $admin->update($adminUpdates);
        }

        // Update coach profile if relevant fields provided
        $profileFields = ['bio', 'city', 'experience', 'whatsapp', 'instagram', 'referral_code', 'referral_commission', 'public_visible'];
        $profileUpdates = [];

        foreach ($profileFields as $field) {
            if (isset($validated[$field])) {
                $profileUpdates[$field] = $validated[$field];
            }
        }

        if (isset($validated['specializations'])) {
            $specs = array_map('trim', explode(',', $validated['specializations']));
            $profileUpdates['specializations'] = json_encode(array_values(array_filter($specs)));
        }

        if (! empty($profileUpdates)) {
            $profile = CoachProfile::where('admin_id', $id)->first();
            if ($profile) {
                $profile->update($profileUpdates);
            }
        }

        return response()->json(['updated' => true]);
    }

    // ─── Plans ──────────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/plans
     *
     * Plan management.
     * Ports Admin\PlanManagement.php render() logic.
     */
    public function plans(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $search = $request->query('search', '');
        $typeFilter = $request->query('type', 'all');
        $coachFilter = $request->query('coach', 'all');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');

        $query = PlanTemplate::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($typeFilter !== 'all') {
            $query->where('plan_type', $typeFilter);
        }
        if ($coachFilter !== 'all') {
            $query->where('coach_id', (int) $coachFilter);
        }

        $allowedSorts = ['name', 'plan_type', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $paginated = $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc')->paginate(25);

        $plans = collect($paginated->items())->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'plan_type' => $p->plan_type,
            'methodology' => $p->methodology,
            'description' => $p->description,
            'is_public' => (bool) $p->is_public,
            'ai_generated' => (bool) $p->ai_generated,
            'coach_id' => $p->coach_id,
            'coach_name' => $p->coach_id ? Admin::find($p->coach_id)?->name : null,
            'created_at' => $p->created_at?->format('d M Y'),
        ]);

        // Coaches for filter
        $coaches = Admin::whereIn('role', ['coach', 'admin', 'superadmin'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return response()->json([
            'plans' => $plans,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
            'coaches' => $coaches,
        ]);
    }

    // ─── Inscriptions ───────────────────────────────────────────────────

    /**
     * GET /api/v/admin/inscriptions
     *
     * Inscriptions list with filters.
     * Ports Admin\InscriptionsList.php render() logic.
     */
    public function inscriptions(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $search = $request->query('search', '');
        $statusFilter = $request->query('status', '');
        $planFilter = $request->query('plan', '');
        $perPage = $request->integer('per_page', 25);

        $query = Inscription::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($planFilter !== '') {
            $query->where('plan', $planFilter);
        }

        $paginated = $query->latest('created_at')->paginate($perPage);

        $inscriptions = collect($paginated->items())->map(fn ($i) => [
            'id' => $i->id,
            'nombre' => trim(($i->nombre ?? '').' '.($i->apellido ?? '')),
            'initial' => mb_substr($i->nombre ?? 'I', 0, 1),
            'email' => $i->email,
            'phone' => $i->phone ?? $i->telefono ?? null,
            'whatsapp' => $i->whatsapp ?? null,
            'plan' => $i->plan?->label() ?? $i->getRawOriginal('plan') ?? '-',
            'plan_raw' => $i->getRawOriginal('plan') ?? '',
            'status' => $i->status ?? '-',
            'ciudad' => $i->ciudad ?? null,
            'objetivo' => $i->objetivo ?? null,
            'experiencia' => $i->experiencia ?? null,
            'created_at' => $i->created_at?->format('d/m/Y H:i'),
            'time_ago' => $i->created_at?->diffForHumans(),
        ]);

        return response()->json([
            'inscriptions' => $inscriptions,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * PUT /api/v/admin/inscriptions/{id}
     *
     * Update inscription status.
     */
    public function updateInscription(Request $request, int $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $inscription = Inscription::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:pendiente,nuevo,contactado,convertido,pagado,activo,rechazado',
        ]);

        $inscription->update(['status' => $validated['status']]);

        return response()->json(['updated' => true]);
    }

    // ─── Invitations ────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/invitations
     *
     * Invitation manager.
     * Ports Admin\InvitationManager.php render() logic.
     */
    public function invitations(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $search = $request->query('search', '');
        $statusFilter = $request->query('status', 'all');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');

        $query = Invitation::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('email_hint', 'like', "%{$search}%");
            });
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $paginated = $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc')->paginate(25);

        return response()->json([
            'invitations' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * POST /api/v/admin/invitations
     *
     * Create invitation.
     * Ports Admin\InvitationManager.php createInvitation() logic.
     */
    public function createInvitation(Request $request): JsonResponse
    {
        $admin = $this->resolveAdminOrFail($request);

        $validated = $request->validate([
            'plan' => 'required|in:rise,esencial,metodo,elite,presencial',
            'email_hint' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $code = strtoupper(Str::random(10));

        $invitation = Invitation::create([
            'code' => $code,
            'plan' => $validated['plan'],
            'email_hint' => $validated['email_hint'] ?? null,
            'note' => $validated['note'] ?? null,
            'expires_at' => $validated['expires_at'] ?? now()->addDays(30),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $intakeUrl = url('/intake?code='.$code);

        return response()->json([
            'created' => true,
            'code' => $code,
            'intake_url' => $intakeUrl,
            'id' => $invitation->id,
        ], 201);
    }

    // ─── Rise Management ────────────────────────────────────────────────

    /**
     * GET /api/v/admin/rise
     *
     * Rise management overview.
     * Ports Admin\RiseManagement.php render() logic (overview tab).
     */
    public function rise(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $search = $request->query('search', '');
        $statusFilter = $request->query('status', 'all');

        // Overview stats
        $totalPrograms = RiseProgram::count();
        $activePrograms = RiseProgram::whereIn('status', ['active', 'activo'])->count();
        $totalTracking = RiseTracking::count();
        $totalMeasurements = RiseMeasurement::count();

        // Participants
        $query = RiseProgram::query()->with('client');

        if ($search !== '') {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $programs = $query->orderByDesc('created_at')->paginate(25);

        $participants = collect($programs->items())->map(fn ($p) => [
            'id' => $p->id,
            'client_id' => $p->client_id,
            'client_name' => $p->client?->name ?? 'Cliente',
            'status' => $p->status,
            'start_date' => $p->start_date?->format('d M Y'),
            'end_date' => $p->end_date?->format('d M Y'),
            'experience_level' => $p->experience_level,
            'training_location' => $p->training_location,
            'gender' => $p->gender,
            'created_at' => $p->created_at?->format('d M Y'),
        ]);

        // RISE payments
        $risePayments = Payment::where('plan', 'rise')
            ->where('status', 'approved')
            ->sum('amount');

        return response()->json([
            'overview' => [
                'totalPrograms' => $totalPrograms,
                'activePrograms' => $activePrograms,
                'totalTracking' => $totalTracking,
                'totalMeasurements' => $totalMeasurements,
                'totalRevenue' => (float) $risePayments,
            ],
            'participants' => $participants,
            'pagination' => [
                'current_page' => $programs->currentPage(),
                'last_page' => $programs->lastPage(),
                'total' => $programs->total(),
            ],
        ]);
    }

    // ─── Settings ───────────────────────────────────────────────────────

    /**
     * GET /api/v/admin/settings
     *
     * Admin settings (read-only config display).
     * Ports Admin\AdminSettings.php mount() logic.
     */
    public function settings(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $config = [
            'app_name' => config('app.name', 'WellCore'),
            'app_url' => config('app.url', ''),
            'app_env' => config('app.env', 'production'),
            'mail_mailer' => config('mail.default', 'smtp'),
            'mail_from_address' => config('mail.from.address', ''),
            'mail_from_name' => config('mail.from.name', ''),
            'db_connection' => config('database.default', 'mysql'),
            'db_database' => config('database.connections.mysql.database', ''),
            'cache_store' => config('cache.default', 'file'),
            'session_driver' => config('session.driver', 'file'),
            'queue_connection' => config('queue.default', 'sync'),
        ];

        $features = [
            ['name' => 'AI Nutrition',      'key' => 'ai-nutrition',    'enabled' => true],
            ['name' => 'AI Plan Generator', 'key' => 'ai-generator',   'enabled' => true],
            ['name' => 'Community Feed',    'key' => 'community',      'enabled' => true],
            ['name' => 'Video Check-ins',   'key' => 'video-checkin',  'enabled' => true],
            ['name' => 'Chat Widget',       'key' => 'chat',           'enabled' => true],
            ['name' => 'RISE Program',      'key' => 'rise',           'enabled' => true],
            ['name' => 'Shop / Tienda',     'key' => 'shop',           'enabled' => true],
            ['name' => 'Referral Program',  'key' => 'referrals',      'enabled' => true],
            ['name' => 'Coach Portal',      'key' => 'coach',          'enabled' => true],
            ['name' => 'Presencial',        'key' => 'presencial',     'enabled' => true],
        ];

        return response()->json([
            'config' => $config,
            'features' => $features,
        ]);
    }

    /**
     * PUT /api/v/admin/settings
     *
     * Update settings (feature toggles — currently a placeholder).
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        // Feature toggles would be stored in a settings table in a future iteration.
        // For now, acknowledge the request.
        return response()->json(['updated' => true, 'message' => 'Los ajustes de configuracion se actualizaran en una version futura.']);
    }

    // ─── Chat Analytics ─────────────────────────────────────────────────

    /**
     * GET /api/v/admin/chat-analytics
     *
     * Chat analytics.
     * Ports Admin\ChatAnalytics.php render() logic.
     */
    public function chatAnalytics(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $search = $request->query('search', '');
        $sessionId = $request->query('session_id');

        try {
            $totalConversations = ChatMessage::distinct('session_id')->count('session_id');
            $totalMessages = ChatMessage::count();
            $messagesToday = ChatMessage::whereDate('created_at', today())->count();

            $topQuestions = ChatMessage::where('role', 'user')
                ->select('content', DB::raw('COUNT(*) as count'))
                ->groupBy('content')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            $hasPageUrl = Schema::hasColumn('chat_messages', 'page_url');

            $conversationsQuery = ChatMessage::select(
                'session_id',
                DB::raw('MIN(CASE WHEN role = \'user\' THEN content END) as first_message'),
                DB::raw('COUNT(*) as message_count'),
                $hasPageUrl ? DB::raw('MAX(page_url) as page_url') : DB::raw('NULL as page_url'),
                DB::raw('MIN(created_at) as started_at'),
                DB::raw('MAX(created_at) as last_message_at')
            )
                ->groupBy('session_id')
                ->orderByDesc(DB::raw('MAX(created_at)'));

            if ($search !== '') {
                $conversationsQuery->whereIn('session_id', function ($query) use ($search) {
                    $query->select('session_id')
                        ->from('chat_messages')
                        ->where('content', 'like', '%'.$search.'%');
                });
            }

            $conversations = $conversationsQuery->paginate(20);

            // Expanded session messages
            $expandedMessages = [];
            if ($sessionId) {
                $expandedMessages = ChatMessage::where('session_id', $sessionId)
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn ($m) => [
                        'id' => $m->id,
                        'role' => $m->role,
                        'content' => $m->content,
                        'created_at' => $m->created_at?->format('d M Y H:i'),
                    ])
                    ->toArray();
            }

            return response()->json([
                'stats' => [
                    'totalConversations' => $totalConversations,
                    'totalMessages' => $totalMessages,
                    'messagesToday' => $messagesToday,
                ],
                'topQuestions' => $topQuestions,
                'conversations' => $conversations->items(),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'total' => $conversations->total(),
                ],
                'expandedMessages' => $expandedMessages,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'stats' => ['totalConversations' => 0, 'totalMessages' => 0, 'messagesToday' => 0],
                'topQuestions' => [],
                'conversations' => [],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
                'expandedMessages' => [],
                'error' => 'Error cargando chat analytics.',
            ]);
        }
    }

    // ─── AI Generator ───────────────────────────────────────────────────

    /**
     * POST /api/v/admin/ai-generator
     *
     * AI plan generation.
     * Ports Admin\AIPlanGenerator.php generate logic.
     */
    public function aiGenerator(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $validated = $request->validate([
            'plan_type' => 'required|in:entrenamiento,nutricion,habitos',
            'methodology' => 'nullable|string|max:100',
            'duration_weeks' => 'required|integer|min:1|max:52',
            'frequency' => 'nullable|integer|min:1|max:7',
            'experience_level' => 'nullable|in:principiante,intermedio,avanzado',
            'training_goal' => 'nullable|string|max:100',
            'injuries' => 'nullable|string|max:500',
            'calorie_target' => 'nullable|integer|min:800|max:10000',
            'protein_pct' => 'nullable|integer|min:0|max:100',
            'carbs_pct' => 'nullable|integer|min:0|max:100',
            'fat_pct' => 'nullable|integer|min:0|max:100',
            'meals_per_day' => 'nullable|integer|min:1|max:10',
            'dietary_restrictions' => 'nullable|string|max:500',
            'habit_focus_areas' => 'nullable|array',
            'target_client_id' => 'nullable|integer',
            'template_name' => 'nullable|string|max:160',
            'is_public' => 'nullable|boolean',
            'save_mode' => 'nullable|in:template_only,template_and_assign',
        ]);

        try {
            $aiService = app(AIService::class);

            $lines = [];
            $lines[] = "Genera un plan de {$validated['plan_type']} profesional.";
            if (! empty($validated['methodology'])) {
                $lines[] = "Metodologia: {$validated['methodology']}";
            }
            $lines[] = "Duracion: {$validated['duration_weeks']} semanas";
            if (! empty($validated['frequency'])) {
                $lines[] = "Frecuencia: {$validated['frequency']} dias/semana";
            }
            if (! empty($validated['experience_level'])) {
                $lines[] = "Nivel: {$validated['experience_level']}";
            }
            if (! empty($validated['training_goal'])) {
                $lines[] = "Objetivo: {$validated['training_goal']}";
            }
            if (! empty($validated['injuries'])) {
                $lines[] = "Lesiones/restricciones: {$validated['injuries']}";
            }
            if (! empty($validated['calorie_target'])) {
                $lines[] = "Calorias: {$validated['calorie_target']}";
            }
            if (! empty($validated['dietary_restrictions'])) {
                $lines[] = "Restricciones dieteticas: {$validated['dietary_restrictions']}";
            }

            // Client context if selected
            if (! empty($validated['target_client_id'])) {
                $targetClient = Client::find($validated['target_client_id']);
                if ($targetClient) {
                    $profile = ClientProfile::where('client_id', $targetClient->id)->first();
                    $lines[] = "Cliente: {$targetClient->name}";
                    if ($profile) {
                        if ($profile->age) {
                            $lines[] = "Edad: {$profile->age}";
                        }
                        if ($profile->weight) {
                            $lines[] = "Peso: {$profile->weight} kg";
                        }
                        if ($profile->height) {
                            $lines[] = "Altura: {$profile->height} cm";
                        }
                        if ($profile->gender) {
                            $lines[] = "Genero: {$profile->gender}";
                        }
                    }
                }
            }

            $prompt = implode("\n", $lines);
            $result = $aiService->generatePlan($prompt, $validated['plan_type']);

            // Auto-save if template_name provided
            $savedTemplateId = null;
            $savedAssignedId = null;

            if (! empty($validated['template_name'])) {
                $template = PlanTemplate::create([
                    'coach_id' => auth('wellcore')->id() ?? null,
                    'name' => $validated['template_name'],
                    'plan_type' => $validated['plan_type'],
                    'methodology' => $validated['methodology'] ?? null,
                    'content_json' => $result,
                    'ai_generated' => true,
                    'is_public' => $validated['is_public'] ?? false,
                ]);
                $savedTemplateId = $template->id;

                if (($validated['save_mode'] ?? '') === 'template_and_assign' && ! empty($validated['target_client_id'])) {
                    $assigned = AssignedPlan::create([
                        'client_id' => $validated['target_client_id'],
                        'plan_type' => $validated['plan_type'],
                        'content' => $result,
                        'version' => 1,
                        'active' => true,
                        'assigned_by' => auth('wellcore')->id() ?? null,
                    ]);
                    $savedAssignedId = $assigned->id;
                }
            }

            return response()->json([
                'generated' => true,
                'plan' => $result,
                'planJson' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'savedTemplateId' => $savedTemplateId,
                'savedAssignedId' => $savedAssignedId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Admin AI plan generation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'generated' => false,
                'error' => 'Error generando el plan: '.$e->getMessage(),
            ], 500);
        }
    }

    // ─── Support Tickets ────────────────────────────────────────────────

    /**
     * GET /api/v/admin/tickets
     *
     * Returns all support tickets (paginated). Admins see every ticket,
     * not scoped to a single client. Optional ?status= filter.
     */
    public function tickets(Request $request): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $status = $request->query('status', 'all');
        $perPage = $request->integer('per_page', 25);

        $query = Ticket::query()->orderByDesc('created_at');

        if ($status !== 'all' && $status !== '') {
            $query->where('status', $status);
        }

        $paginated = $query->paginate($perPage);

        $tickets = collect($paginated->items())->map(fn (Ticket $t) => [
            'id' => $t->id,
            'client_name' => $t->client_name,
            'client_plan' => $t->client_plan,
            'coach_id' => $t->coach_id,
            'coach_name' => $t->coach_name,
            'ticket_type' => (string) $t->ticket_type,
            'description' => $t->description,
            'priority' => $t->priority instanceof TicketPriority ? $t->priority->value : (string) $t->priority,
            'status' => $t->status instanceof TicketStatus ? $t->status->value : (string) $t->status,
            'response' => $t->response,
            'assigned_to' => $t->assigned_to,
            'deadline' => $t->deadline?->format('d M Y, H:i'),
            'resolved_at' => $t->resolved_at?->diffForHumans(),
            'created_at' => $t->created_at?->format('d M Y, H:i'),
        ]);

        return response()->json([
            'tickets' => $tickets,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * POST /api/v/admin/tickets/{id}/reply
     *
     * Admin posts a response to a ticket and marks it resolved.
     */
    public function replyTicket(Request $request, string $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'response' => 'required|string|min:5|max:5000',
        ]);

        $ticket->update([
            'response' => $validated['response'],
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Respuesta enviada. Ticket marcado como resuelto.',
            'ticket' => [
                'id' => $ticket->id,
                'status' => 'resolved',
                'response' => $ticket->response,
                'resolved_at' => $ticket->resolved_at?->diffForHumans(),
            ],
        ]);
    }

    /**
     * PATCH /api/v/admin/tickets/{id}/status
     *
     * Updates the status of a support ticket.
     */
    public function updateTicketStatus(Request $request, string $id): JsonResponse
    {
        $this->resolveAdminOrFail($request);

        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $update = ['status' => $validated['status']];

        if ($validated['status'] === 'resolved' && $ticket->resolved_at === null) {
            $update['resolved_at'] = now();
        }

        $ticket->update($update);

        return response()->json([
            'message' => 'Estado actualizado.',
            'ticket' => [
                'id' => $ticket->id,
                'status' => $validated['status'],
                'resolved_at' => $ticket->resolved_at?->diffForHumans(),
            ],
        ]);
    }
}
