<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\AcademyContent;
use App\Models\AccountabilityPod;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\ClientXp;
use App\Models\CoachAudio;
use App\Models\CoachAvailability;
use App\Models\CoachCommunityPost;
use App\Models\CoachMessage;
use App\Models\CoachNote;
use App\Models\CoachProfile;
use App\Models\CoachPwaConfig;
use App\Models\CoachVideoTip;
use App\Models\Payment;
use App\Models\PersonalRecord;
use App\Models\PlanTemplate;
use App\Models\PodMember;
use App\Models\PodMessage;
use App\Models\Referral;
use App\Models\ReferralStat;
use App\Models\TrainingLog;
use App\Models\VideoCheckin;
use App\Services\AIService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CoachController extends Controller
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
        $role  = $admin->role?->value ?? $admin->role ?? '';

        if (! in_array($role, ['coach', 'admin', 'superadmin', 'jefe'])) {
            abort(403, 'No tienes permisos para acceder al portal de coach.');
        }

        return $admin;
    }

    /**
     * Get client IDs assigned to this coach via assigned_plans.
     */
    protected function getCoachClientIds(int $coachId): \Illuminate\Support\Collection
    {
        return AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();
    }

    // ─── Dashboard ──────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/dashboard
     *
     * Coach dashboard stats: greeting, active clients, pending checkins,
     * unread messages, clients needing attention, recent messages, charts.
     * Ports Coach\Dashboard.php mount() logic.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;

        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 12 => 'Buenos dias',
            $hour < 18 => 'Buenas tardes',
            default    => 'Buenas noches',
        };

        $coachName = explode(' ', $coach->name ?? 'Coach')[0];

        $clientIds = $this->getCoachClientIds($coachId);

        $clients       = Client::whereIn('id', $clientIds)->where('status', 'activo')->get();
        $activeClients = $clients->count();

        $pendingCheckins = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->count();

        $unreadMessages = CoachMessage::where('coach_id', $coachId)
            ->where('direction', 'client_to_coach')
            ->whereNull('read_at')
            ->count();

        $plansThisMonth = AssignedPlan::where('assigned_by', $coachId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Attention clients
        $attentionClients = $this->loadAttentionClients($coachId, $clientIds);

        // Recent messages
        $recentMessages = $this->loadRecentMessages($coachId);

        // Chart data
        $chartData = $this->loadCoachChartData($coachId, $clientIds);

        return response()->json([
            'greeting'            => $greeting,
            'coachName'           => $coachName,
            'activeClients'       => $activeClients,
            'pendingCheckins'     => $pendingCheckins,
            'unreadMessages'      => $unreadMessages,
            'plansThisMonth'      => $plansThisMonth,
            'attentionClients'    => $attentionClients,
            'recentMessages'      => $recentMessages,
            'clientProgressData'  => $chartData['clientProgressData'],
            'checkinFrequencyData' => $chartData['checkinFrequencyData'],
        ]);
    }

    protected function loadAttentionClients(int $coachId, $clientIds): array
    {
        $pendingByClient = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->selectRaw('client_id, COUNT(*) as pending_count, MIN(checkin_date) as oldest_checkin')
            ->groupBy('client_id')
            ->orderBy('oldest_checkin')
            ->limit(5)
            ->get();

        $pendingClientIds    = $pendingByClient->pluck('client_id');
        $clientsById         = Client::whereIn('id', $pendingClientIds)->get()->keyBy('id');
        $lastMessagesByClient = CoachMessage::whereIn('client_id', $pendingClientIds)
            ->orderByDesc('created_at')
            ->get()
            ->unique('client_id')
            ->keyBy('client_id');

        $result = [];
        foreach ($pendingByClient as $row) {
            $client = $clientsById->get($row->client_id);
            if (! $client) continue;

            $lastMessage = $lastMessagesByClient->get($row->client_id);

            $result[] = [
                'id'               => $client->id,
                'name'             => $client->name,
                'plan'             => $client->plan?->label() ?? 'Sin plan',
                'pending_checkins' => $row->pending_count,
                'oldest_checkin'   => Carbon::parse($row->oldest_checkin)->diffForHumans(),
                'last_message'     => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : 'Sin mensajes',
            ];
        }

        return $result;
    }

    protected function loadRecentMessages(int $coachId): array
    {
        $messages = CoachMessage::where('coach_id', $coachId)
            ->where('direction', 'client_to_coach')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $messageClientIds = $messages->pluck('client_id')->unique();
        $clientsById      = Client::whereIn('id', $messageClientIds)->get()->keyBy('id');

        $result = [];
        foreach ($messages as $msg) {
            $client   = $clientsById->get($msg->client_id);
            $result[] = [
                'client_name' => $client->name ?? 'Cliente',
                'message'     => str()->limit($msg->message, 80),
                'time_ago'    => Carbon::parse($msg->created_at)->diffForHumans(),
                'is_read'     => $msg->read_at !== null,
            ];
        }

        return $result;
    }

    protected function loadCoachChartData(int $coachId, $clientIds): array
    {
        $clientProgressData = TrainingLog::whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('log_date', '>=', now()->subWeeks(4)->toDateString())
            ->join('clients', 'training_logs.client_id', '=', 'clients.id')
            ->selectRaw('clients.name, COUNT(*) as sessions')
            ->groupBy('clients.name', 'training_logs.client_id')
            ->orderByDesc('sessions')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name'     => explode(' ', $row->name)[0],
                'sessions' => (int) $row->sessions,
            ])
            ->toArray();

        $checkinFrequencyData = Checkin::whereIn('client_id', $clientIds)
            ->where('checkin_date', '>=', now()->subWeeks(8)->toDateString())
            ->selectRaw("YEARWEEK(checkin_date, 1) as yw, COUNT(*) as count")
            ->groupBy('yw')
            ->orderBy('yw')
            ->get()
            ->map(fn ($row) => [
                'week'  => 'Sem ' . substr($row->yw, 4),
                'count' => (int) $row->count,
            ])
            ->toArray();

        return compact('clientProgressData', 'checkinFrequencyData');
    }

    // ─── Clients ────────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/clients
     *
     * Client list with search.
     * Ports Coach\ClientList.php render() logic.
     */
    public function clients(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;
        $search  = $request->query('search', '');

        $clientIds = $this->getCoachClientIds($coachId);

        $query = Client::whereIn('id', $clientIds)->where('status', 'activo');
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $clients   = $query->orderBy('name')->get();
        $cIds      = $clients->pluck('id');

        $lastCheckins   = Checkin::whereIn('client_id', $cIds)->orderByDesc('checkin_date')->get()->keyBy('client_id');
        $lastMessages   = CoachMessage::whereIn('client_id', $cIds)->where('coach_id', $coachId)->orderByDesc('created_at')->get()->keyBy('client_id');
        $xpRecords      = ClientXp::whereIn('client_id', $cIds)->get()->keyBy('client_id');
        $activePlans    = AssignedPlan::whereIn('client_id', $cIds)->where('assigned_by', $coachId)->where('active', true)->orderByDesc('created_at')->get()->keyBy('client_id');
        $pendingCounts  = Checkin::whereIn('client_id', $cIds)->whereNull('coach_reply')->selectRaw('client_id, COUNT(*) as cnt')->groupBy('client_id')->pluck('cnt', 'client_id');

        $clientData = $clients->map(function ($client) use ($lastCheckins, $lastMessages, $xpRecords, $activePlans, $pendingCounts) {
            $lastCheckin    = $lastCheckins->get($client->id);
            $lastMessage    = $lastMessages->get($client->id);
            $xp             = $xpRecords->get($client->id);
            $activePlan     = $activePlans->get($client->id);
            $pendingCheckins = $pendingCounts->get($client->id, 0);

            return [
                'id'               => $client->id,
                'name'             => $client->name,
                'email'            => $client->email,
                'plan_label'       => $client->plan?->label() ?? 'Sin plan',
                'status'           => $client->status?->label() ?? 'Activo',
                'fecha_inicio'     => $client->fecha_inicio ? Carbon::parse($client->fecha_inicio)->format('d M Y') : 'N/A',
                'last_checkin'     => $lastCheckin ? Carbon::parse($lastCheckin->checkin_date)->diffForHumans() : 'Nunca',
                'last_checkin_date' => $lastCheckin ? Carbon::parse($lastCheckin->checkin_date)->format('d/m/Y') : null,
                'last_message'     => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : 'Sin mensajes',
                'xp_level'         => $xp->level ?? 1,
                'xp_total'         => $xp->xp_total ?? 0,
                'streak_days'      => $xp->streak_days ?? 0,
                'active_plan_type' => $activePlan->plan_type ?? null,
                'pending_checkins' => $pendingCheckins,
                'avatar_initial'   => substr($client->name ?? 'C', 0, 1),
            ];
        });

        return response()->json([
            'clients'      => $clientData,
            'totalClients' => $clientData->count(),
        ]);
    }

    // ─── Kanban ─────────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/kanban
     *
     * Kanban board data.
     * Ports Coach\ClientKanban.php buildBoard() logic.
     */
    public function kanban(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;
        $search  = $request->query('search', '');

        $clientIds = $this->getCoachClientIds($coachId);

        $query = Client::whereIn('id', $clientIds);
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }
        $clients = $query->orderBy('name')->get();

        $lastCheckins    = Checkin::whereIn('client_id', $clientIds)->selectRaw('client_id, MAX(checkin_date) as last_checkin')->groupBy('client_id')->pluck('last_checkin', 'client_id');
        $lastTraining    = TrainingLog::whereIn('client_id', $clientIds)->where('completed', true)->selectRaw('client_id, MAX(log_date) as last_training')->groupBy('client_id')->pluck('last_training', 'client_id');
        $pendingCheckins = Checkin::whereIn('client_id', $clientIds)->whereNull('coach_reply')->selectRaw('client_id, COUNT(*) as cnt')->groupBy('client_id')->pluck('cnt', 'client_id');
        $unreadMessages  = CoachMessage::where('coach_id', $coachId)->where('direction', 'client_to_coach')->whereNull('read_at')->whereIn('client_id', $clientIds)->selectRaw('client_id, COUNT(*) as cnt')->groupBy('client_id')->pluck('cnt', 'client_id');

        $columns = [
            'nuevo'    => ['title' => 'Nuevos',     'icon' => 'sparkles',            'color' => 'blue',    'clients' => []],
            'activo'   => ['title' => 'Activos',    'icon' => 'bolt',                'color' => 'emerald', 'clients' => []],
            'riesgo'   => ['title' => 'En Riesgo',  'icon' => 'exclamation-triangle', 'color' => 'amber',   'clients' => []],
            'inactivo' => ['title' => 'Inactivos',  'icon' => 'pause-circle',        'color' => 'red',     'clients' => []],
        ];

        $now = Carbon::now();

        foreach ($clients as $client) {
            $lastCheckinDate  = isset($lastCheckins[$client->id]) ? Carbon::parse($lastCheckins[$client->id]) : null;
            $lastTrainingDate = isset($lastTraining[$client->id]) ? Carbon::parse($lastTraining[$client->id]) : null;

            $lastActivity = null;
            if ($lastCheckinDate && $lastTrainingDate) {
                $lastActivity = $lastCheckinDate->greaterThan($lastTrainingDate) ? $lastCheckinDate : $lastTrainingDate;
            } elseif ($lastCheckinDate) {
                $lastActivity = $lastCheckinDate;
            } elseif ($lastTrainingDate) {
                $lastActivity = $lastTrainingDate;
            }

            $daysSinceActivity = $lastActivity ? (int) $lastActivity->diffInDays($now) : null;
            $daysSinceStart    = $client->fecha_inicio ? (int) Carbon::parse($client->fecha_inicio)->diffInDays($now) : null;

            $column = $this->classifyClient($client, $daysSinceActivity, $daysSinceStart);

            $columns[$column]['clients'][] = [
                'id'                   => $client->id,
                'name'                 => $client->name,
                'avatar_initial'       => mb_strtoupper(mb_substr($client->name ?? 'C', 0, 1)),
                'plan_label'           => $client->plan?->label() ?? 'Sin plan',
                'plan_value'           => $client->plan?->value ?? '',
                'status_label'         => $client->status?->label() ?? 'Desconocido',
                'status_value'         => $client->status?->value ?? '',
                'fecha_inicio'         => $client->fecha_inicio ? Carbon::parse($client->fecha_inicio)->format('d M Y') : null,
                'days_since_activity'  => $daysSinceActivity,
                'last_activity_human'  => $lastActivity ? $lastActivity->diffForHumans() : 'Sin actividad',
                'last_checkin_date'    => $lastCheckinDate ? $lastCheckinDate->format('d/m') : null,
                'last_training_date'   => $lastTrainingDate ? $lastTrainingDate->format('d/m') : null,
                'pending_checkins'     => $pendingCheckins[$client->id] ?? 0,
                'unread_messages'      => $unreadMessages[$client->id] ?? 0,
            ];
        }

        return response()->json([
            'columns'      => $columns,
            'totalClients' => $clients->count(),
        ]);
    }

    /**
     * POST /api/v/coach/kanban/move
     *
     * Move client to a different kanban column.
     * Ports Coach\ClientKanban.php moveClient() logic.
     */
    public function kanbanMove(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $validated = $request->validate([
            'client_id'     => 'required|integer',
            'target_column' => 'required|in:nuevo,activo,riesgo,inactivo',
        ]);

        $dbClient = Client::find($validated['client_id']);
        if (! $dbClient) {
            return response()->json(['error' => 'Cliente no encontrado.'], 404);
        }

        $targetColumn = $validated['target_column'];

        if ($targetColumn === 'inactivo' && $dbClient->status?->value !== 'inactivo') {
            $dbClient->update(['status' => 'inactivo']);
        } elseif (in_array($targetColumn, ['activo', 'nuevo', 'riesgo']) &&
            in_array($dbClient->status?->value, ['inactivo', 'congelado'])) {
            $dbClient->update(['status' => 'activo']);
        }

        $columnLabels = [
            'nuevo' => 'Nuevos', 'activo' => 'Activos',
            'riesgo' => 'En Riesgo', 'inactivo' => 'Inactivos',
        ];

        CoachNote::create([
            'coach_id'   => $coach->id,
            'client_id'  => $validated['client_id'],
            'note'       => 'Movido a columna "' . ($columnLabels[$targetColumn] ?? $targetColumn) . '" en el Kanban.',
            'note_type'  => 'seguimiento',
        ]);

        return response()->json(['moved' => true]);
    }

    protected function classifyClient(Client $client, ?int $daysSinceActivity, ?int $daysSinceStart): string
    {
        if (in_array($client->status?->value, ['inactivo', 'suspendido', 'congelado'])) {
            return 'inactivo';
        }
        if ($daysSinceStart !== null && $daysSinceStart <= 14) {
            return 'nuevo';
        }
        if ($daysSinceActivity === null) {
            return 'inactivo';
        }
        if ($daysSinceActivity <= 7) {
            return 'activo';
        }
        if ($daysSinceActivity <= 21) {
            return 'riesgo';
        }
        return 'inactivo';
    }

    // ─── Checkins ───────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/checkins
     *
     * Pending checkins to review.
     * Ports Coach\CheckinReview.php render() logic.
     */
    public function checkins(Request $request): JsonResponse
    {
        $coach    = $this->resolveCoachOrFail($request);
        $coachId  = $coach->id;
        $showAll  = $request->boolean('show_replied', false);

        $clientIds = $this->getCoachClientIds($coachId);

        $query = Checkin::whereIn('client_id', $clientIds);
        if (! $showAll) {
            $query->whereNull('coach_reply');
        }
        $checkins = $query->orderByDesc('checkin_date')->get();

        $checkinClientIds  = $checkins->pluck('client_id')->unique();
        $checkinClientsById = Client::whereIn('id', $checkinClientIds)->get()->keyBy('id');

        $checkinData = $checkins->map(function ($checkin) use ($checkinClientsById) {
            $client = $checkinClientsById->get($checkin->client_id);
            return [
                'id'              => $checkin->id,
                'client_name'     => $client->name ?? 'Cliente',
                'client_initial'  => substr($client->name ?? 'C', 0, 1),
                'client_plan'     => $client->plan?->label() ?? 'Sin plan',
                'week_label'      => $checkin->week_label,
                'checkin_date'    => Carbon::parse($checkin->checkin_date)->format('d M Y'),
                'checkin_date_ago' => Carbon::parse($checkin->checkin_date)->diffForHumans(),
                'bienestar'       => $checkin->bienestar,
                'dias_entrenados' => $checkin->dias_entrenados,
                'nutricion'       => $checkin->nutricion,
                'rpe'             => $checkin->rpe,
                'comentario'      => $checkin->comentario,
                'coach_reply'     => $checkin->coach_reply,
                'replied_at'      => $checkin->replied_at ? Carbon::parse($checkin->replied_at)->diffForHumans() : null,
            ];
        });

        $pendingCount = Checkin::whereIn('client_id', $clientIds)->whereNull('coach_reply')->count();

        return response()->json([
            'checkins'     => $checkinData,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * POST /api/v/coach/checkins/{id}/reply
     *
     * Reply to a checkin.
     * Ports Coach\CheckinReview.php reply() logic.
     */
    public function checkinReply(Request $request, int $id): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;

        $validated = $request->validate([
            'reply' => 'required|string|min:1|max:5000',
        ]);

        $checkin = Checkin::find($id);
        if (! $checkin) {
            return response()->json(['error' => 'Check-in no encontrado.'], 404);
        }

        $clientIds = $this->getCoachClientIds($coachId);
        if (! $clientIds->contains($checkin->client_id)) {
            return response()->json(['error' => 'No tienes acceso a este check-in.'], 403);
        }

        $checkin->update([
            'coach_reply' => trim($validated['reply']),
            'replied_at'  => now(),
        ]);

        return response()->json(['replied' => true]);
    }

    // ─── Messages ───────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/messages
     *
     * Message threads with client list and conversation.
     * Ports Coach\MessageCenter.php render() logic.
     */
    public function messages(Request $request): JsonResponse
    {
        $coach     = $this->resolveCoachOrFail($request);
        $coachId   = $coach->id;
        $clientId  = $request->integer('client_id');

        $clientIds = $this->getCoachClientIds($coachId);

        // Client list with unread counts
        $clientList = Client::whereIn('id', $clientIds)
            ->where('status', 'activo')
            ->orderBy('name')
            ->get()
            ->map(function ($client) use ($coachId) {
                $unreadCount = CoachMessage::where('coach_id', $coachId)
                    ->where('client_id', $client->id)
                    ->where('direction', 'client_to_coach')
                    ->whereNull('read_at')
                    ->count();

                $lastMessage = CoachMessage::where('coach_id', $coachId)
                    ->where('client_id', $client->id)
                    ->orderByDesc('created_at')
                    ->first();

                return [
                    'id'                   => $client->id,
                    'name'                 => $client->name,
                    'initial'              => substr($client->name ?? 'C', 0, 1),
                    'plan'                 => $client->plan?->label() ?? 'Sin plan',
                    'unread_count'         => $unreadCount,
                    'last_message_preview' => $lastMessage ? str()->limit($lastMessage->message, 40) : 'Sin mensajes',
                    'last_message_time'    => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : null,
                ];
            });

        // Conversation for selected client
        $conversation = [];
        if ($clientId > 0) {
            // Mark messages as read
            CoachMessage::where('coach_id', $coachId)
                ->where('client_id', $clientId)
                ->where('direction', 'client_to_coach')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $conversation = CoachMessage::where('coach_id', $coachId)
                ->where('client_id', $clientId)
                ->orderBy('created_at')
                ->get()
                ->map(fn ($msg) => [
                    'id'        => $msg->id,
                    'message'   => $msg->message,
                    'direction' => $msg->direction,
                    'is_coach'  => $msg->direction === 'coach_to_client',
                    'time'      => Carbon::parse($msg->created_at)->format('d/m H:i'),
                    'time_ago'  => Carbon::parse($msg->created_at)->diffForHumans(),
                ]);
        }

        return response()->json([
            'clients'      => $clientList,
            'conversation' => $conversation,
        ]);
    }

    /**
     * POST /api/v/coach/messages
     *
     * Send message to client.
     * Ports Coach\MessageCenter.php sendMessage() logic.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;

        $validated = $request->validate([
            'client_id' => 'required|integer',
            'message'   => 'required|string|min:1|max:5000',
        ]);

        $clientIds = $this->getCoachClientIds($coachId);
        if (! $clientIds->contains($validated['client_id'])) {
            return response()->json(['error' => 'No tienes acceso a este cliente.'], 403);
        }

        $msg = CoachMessage::create([
            'coach_id'  => $coachId,
            'client_id' => $validated['client_id'],
            'message'   => trim($validated['message']),
            'direction' => 'coach_to_client',
        ]);

        return response()->json(['sent' => true, 'message_id' => $msg->id], 201);
    }

    // ─── Broadcast ──────────────────────────────────────────────────────

    /**
     * POST /api/v/coach/broadcast
     *
     * Broadcast message to multiple clients.
     * Ports Coach\BroadcastCenter.php sendBroadcast() logic.
     */
    public function broadcast(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;

        $validated = $request->validate([
            'message'        => 'required|string|min:1|max:5000',
            'recipient_mode' => 'required|in:all,plan,status,individual',
            'selected_plans' => 'nullable|array',
            'selected_status' => 'nullable|string',
            'selected_client_ids' => 'nullable|array',
        ]);

        $allClientIds = $this->getCoachClientIds($coachId);

        $recipientIds = match ($validated['recipient_mode']) {
            'all' => Client::whereIn('id', $allClientIds)
                ->where('status', 'activo')
                ->pluck('id')
                ->toArray(),

            'plan' => ! empty($validated['selected_plans'])
                ? Client::whereIn('id', $allClientIds)
                    ->where('status', 'activo')
                    ->whereIn('plan', $validated['selected_plans'])
                    ->pluck('id')
                    ->toArray()
                : [],

            'status' => Client::whereIn('id', $allClientIds)
                ->where('status', $validated['selected_status'] ?? 'activo')
                ->pluck('id')
                ->toArray(),

            'individual' => array_values(array_intersect(
                array_map('intval', $validated['selected_client_ids'] ?? []),
                $allClientIds->toArray()
            )),

            default => [],
        };

        if (empty($recipientIds)) {
            return response()->json(['error' => 'No hay destinatarios.'], 422);
        }

        $messageText = trim($validated['message']);
        $count       = 0;

        foreach ($recipientIds as $clientId) {
            CoachMessage::create([
                'coach_id'  => $coachId,
                'client_id' => $clientId,
                'message'   => $messageText,
                'direction' => 'coach_to_client',
            ]);
            $count++;
        }

        return response()->json(['sent' => true, 'sentCount' => $count]);
    }

    // ─── Plans ──────────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/plans
     *
     * Plan templates.
     * Ports Coach\PlansManager.php render() logic (My Templates tab).
     */
    public function plans(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;
        $search  = $request->query('search', '');
        $type    = $request->query('type', '');

        $query = PlanTemplate::where('coach_id', $coachId);

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($type !== '') {
            $query->where('plan_type', $type);
        }

        $templates = $query->orderByDesc('created_at')->get()->map(fn ($tpl) => [
            'id'           => $tpl->id,
            'name'         => $tpl->name,
            'plan_type'    => $tpl->plan_type,
            'methodology'  => $tpl->methodology,
            'description'  => $tpl->description,
            'is_public'    => (bool) $tpl->is_public,
            'ai_generated' => (bool) $tpl->ai_generated,
            'content_json' => $tpl->content_json,
            'created_at'   => $tpl->created_at?->format('d M Y'),
        ]);

        return response()->json(['templates' => $templates]);
    }

    /**
     * POST /api/v/coach/plans
     *
     * Create plan template.
     * Ports Coach\PlansManager.php saveTemplate() logic.
     */
    public function createPlan(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $validated = $request->validate([
            'name'         => 'required|string|max:160',
            'plan_type'    => 'required|in:entrenamiento,nutricion,habitos,suplementacion,ciclo',
            'methodology'  => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:5000',
            'content_json' => 'nullable',
            'is_public'    => 'nullable|boolean',
        ]);

        $contentArray = null;
        if (! empty($validated['content_json'])) {
            if (is_string($validated['content_json'])) {
                $contentArray = json_decode($validated['content_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'JSON invalido: ' . json_last_error_msg()], 422);
                }
            } else {
                $contentArray = $validated['content_json'];
            }
        }

        $template = PlanTemplate::create([
            'coach_id'     => $coach->id,
            'name'         => $validated['name'],
            'plan_type'    => $validated['plan_type'],
            'methodology'  => $validated['methodology'] ?? null,
            'description'  => $validated['description'] ?? null,
            'content_json' => $contentArray,
            'is_public'    => $validated['is_public'] ?? false,
            'ai_generated' => false,
        ]);

        return response()->json(['created' => true, 'id' => $template->id], 201);
    }

    /**
     * PUT /api/v/coach/plans/{id}
     *
     * Update plan template.
     * Ports Coach\PlansManager.php saveTemplate() (edit path) logic.
     */
    public function updatePlan(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $tpl = PlanTemplate::where('coach_id', $coach->id)->find($id);
        if (! $tpl) {
            return response()->json(['error' => 'Template no encontrado.'], 404);
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:160',
            'plan_type'    => 'required|in:entrenamiento,nutricion,habitos,suplementacion,ciclo',
            'methodology'  => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:5000',
            'content_json' => 'nullable',
            'is_public'    => 'nullable|boolean',
        ]);

        $contentArray = null;
        if (! empty($validated['content_json'])) {
            if (is_string($validated['content_json'])) {
                $contentArray = json_decode($validated['content_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'JSON invalido: ' . json_last_error_msg()], 422);
                }
            } else {
                $contentArray = $validated['content_json'];
            }
        }

        $tpl->update([
            'name'         => $validated['name'],
            'plan_type'    => $validated['plan_type'],
            'methodology'  => $validated['methodology'] ?? null,
            'description'  => $validated['description'] ?? null,
            'content_json' => $contentArray,
            'is_public'    => $validated['is_public'] ?? $tpl->is_public,
        ]);

        return response()->json(['updated' => true]);
    }

    /**
     * POST /api/v/coach/plans/generate
     *
     * AI plan generation.
     * Ports Coach\PlansManager.php generate logic (uses AIService).
     */
    public function generatePlan(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $validated = $request->validate([
            'plan_type'         => 'required|in:entrenamiento,nutricion,habitos',
            'methodology'       => 'nullable|string|max:100',
            'duration_weeks'    => 'required|integer|min:1|max:52',
            'frequency'         => 'nullable|integer|min:1|max:7',
            'experience_level'  => 'nullable|in:principiante,intermedio,avanzado',
            'training_goal'     => 'nullable|string|max:100',
            'injuries'          => 'nullable|string|max:500',
            'calorie_target'    => 'nullable|integer|min:800|max:10000',
            'protein_pct'       => 'nullable|integer|min:0|max:100',
            'carbs_pct'         => 'nullable|integer|min:0|max:100',
            'fat_pct'           => 'nullable|integer|min:0|max:100',
            'meals_per_day'     => 'nullable|integer|min:1|max:10',
            'dietary_restrictions' => 'nullable|string|max:500',
            'habit_focus_areas' => 'nullable|array',
            'target_client_id'  => 'nullable|integer',
        ]);

        try {
            $aiService = app(AIService::class);

            $prompt = $this->buildAIPrompt($validated);

            $result = $aiService->generatePlan($prompt, $validated['plan_type']);

            return response()->json([
                'generated' => true,
                'plan'      => $result,
                'planJson'  => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Coach AI plan generation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'generated' => false,
                'error'     => 'Error generando el plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildAIPrompt(array $params): string
    {
        $lines = [];
        $lines[] = "Genera un plan de {$params['plan_type']} profesional.";
        if (! empty($params['methodology'])) $lines[] = "Metodologia: {$params['methodology']}";
        $lines[] = "Duracion: {$params['duration_weeks']} semanas";
        if (! empty($params['frequency'])) $lines[] = "Frecuencia: {$params['frequency']} dias/semana";
        if (! empty($params['experience_level'])) $lines[] = "Nivel: {$params['experience_level']}";
        if (! empty($params['training_goal'])) $lines[] = "Objetivo: {$params['training_goal']}";
        if (! empty($params['injuries'])) $lines[] = "Lesiones/restricciones: {$params['injuries']}";
        if (! empty($params['calorie_target'])) $lines[] = "Calorias: {$params['calorie_target']}";
        if (! empty($params['dietary_restrictions'])) $lines[] = "Restricciones dieteticas: {$params['dietary_restrictions']}";

        return implode("\n", $lines);
    }

    // ─── Analytics ──────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/analytics
     *
     * Analytics data.
     * Ports Coach\Analytics.php loadMetrics() logic (summary version).
     */
    public function analytics(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;
        $range   = $request->query('range', 'month');

        $clientIds = $this->getCoachClientIds($coachId);

        if ($clientIds->isEmpty()) {
            return response()->json(['empty' => true]);
        }

        $dateFrom = match ($range) {
            'month'   => now()->subMonth(),
            'quarter' => now()->subMonths(3),
            'year'    => now()->subYear(),
            'all'     => null,
        };

        // Client overview
        $allClients    = Client::whereIn('id', $clientIds)->get();
        $totalClients  = $allClients->count();
        $activeClients = $allClients->where('status', 'activo')->count();
        $retentionRate = $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 1) : 0;

        // Checkin stats
        $checkinQuery    = Checkin::whereIn('client_id', $clientIds)->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom));
        $totalCheckins   = $checkinQuery->clone()->count();
        $repliedCheckins = $checkinQuery->clone()->whereNotNull('coach_reply')->count();
        $checkinReplyRate = $totalCheckins > 0 ? round(($repliedCheckins / $totalCheckins) * 100, 1) : 0;

        // Client progress averages
        $avgBienestar     = round((float) $checkinQuery->clone()->whereNotNull('bienestar')->avg('bienestar'), 1);
        $avgDiasEntrenados = round((float) $checkinQuery->clone()->whereNotNull('dias_entrenados')->avg('dias_entrenados'), 1);

        // Messages
        $messageQuery   = CoachMessage::where('coach_id', $coachId)->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom));
        $messagesSent     = $messageQuery->clone()->where('direction', 'coach_to_client')->count();
        $messagesReceived = $messageQuery->clone()->where('direction', 'client_to_coach')->count();

        // Revenue
        $payments     = Payment::whereIn('client_id', $clientIds)->where('status', 'approved')->when($dateFrom, fn ($q) => $q->where('created_at', '>=', $dateFrom));
        $totalRevenue = (float) $payments->sum('amount');

        // Plan distribution
        $planDistribution = Client::whereIn('id', $clientIds)
            ->where('status', 'activo')
            ->selectRaw("plan, COUNT(*) as count")
            ->groupBy('plan')
            ->get()
            ->map(fn ($row) => ['name' => ucfirst($row->plan ?? 'Sin plan'), 'count' => (int) $row->count])
            ->toArray();

        return response()->json([
            'totalClients'      => $totalClients,
            'activeClients'     => $activeClients,
            'retentionRate'     => $retentionRate,
            'totalCheckins'     => $totalCheckins,
            'repliedCheckins'   => $repliedCheckins,
            'checkinReplyRate'  => $checkinReplyRate,
            'avgBienestar'      => $avgBienestar,
            'avgDiasEntrenados' => $avgDiasEntrenados,
            'messagesSent'      => $messagesSent,
            'messagesReceived'  => $messagesReceived,
            'totalRevenue'      => $totalRevenue,
            'planDistribution'  => $planDistribution,
            'dateRange'         => $range,
        ]);
    }

    // ─── Profile ────────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/profile
     *
     * Coach profile with referral and revenue data.
     * Ports Coach\CoachProfilePage.php mount() logic.
     */
    public function profile(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $profile = CoachProfile::where('admin_id', $coach->id)->first();

        if (! $profile) {
            $profile = CoachProfile::create([
                'admin_id'       => $coach->id,
                'slug'           => Str::slug($coach->name) . '-' . Str::random(4),
                'referral_code'  => strtoupper(Str::random(8)),
                'color_primary'  => '#E31E24',
                'public_visible' => true,
            ]);
        }

        $specs = $profile->specializations;
        $specializations = is_array($specs) ? $specs : [];

        // Referral stats
        $referralCode   = $profile->referral_code ?? '';
        $referralLink   = url('/inscripcion?ref=' . $referralCode);
        $totalClicks    = ReferralStat::where('coach_id', $coach->id)->count();
        $convertedClicks = ReferralStat::where('coach_id', $coach->id)->where('converted', true)->count();

        $referrals       = Referral::where('referrer_id', $coach->id)->get();
        $totalReferrals  = $referrals->count();
        $pendingRefs     = $referrals->where('status', 'pending')->count();
        $registeredRefs  = $referrals->where('status', 'registered')->count();
        $convertedRefs   = $referrals->where('status', 'converted')->count();

        return response()->json([
            'coachName'           => $coach->name,
            'coachId'             => $coach->id,
            'profileId'           => $profile->id,
            'slug'                => $profile->slug,
            'bio'                 => $profile->bio ?? '',
            'city'                => $profile->city ?? '',
            'experience'          => $profile->experience ?? '',
            'whatsapp'            => $profile->whatsapp ?? '',
            'instagram'           => $profile->instagram ?? '',
            'specializations'     => $specializations,
            'color_primary'       => $profile->color_primary ?? '#E31E24',
            'public_visible'      => (bool) $profile->public_visible,
            'referral_code'       => $referralCode,
            'referral_link'       => $referralLink,
            'photo_url'           => $profile->photo_url ?? '',
            'logo_url'            => $profile->logo_url ?? '',
            'commissionRate'      => $profile->referral_commission ?? '5.00',
            'totalClicks'         => $totalClicks,
            'convertedClicks'     => $convertedClicks,
            'totalReferrals'      => $totalReferrals,
            'pendingReferrals'    => $pendingRefs,
            'registeredReferrals' => $registeredRefs,
            'convertedReferrals'  => $convertedRefs,
        ]);
    }

    /**
     * PUT /api/v/coach/profile
     *
     * Update coach profile.
     * Ports Coach\CoachProfilePage.php saveProfile() logic.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $profile = CoachProfile::where('admin_id', $coach->id)->first();

        if (! $profile) {
            return response()->json(['error' => 'Perfil no encontrado.'], 404);
        }

        $validated = $request->validate([
            'bio'             => 'nullable|string|max:2000',
            'city'            => 'nullable|string|max:100',
            'experience'      => 'nullable|string|max:100',
            'whatsapp'        => 'nullable|string|max:20',
            'instagram'       => 'nullable|string|max:100',
            'specializations' => 'nullable|array',
            'color_primary'   => 'nullable|string|max:20',
            'public_visible'  => 'nullable|boolean',
        ]);

        $profile->update([
            'bio'             => $validated['bio'] ?? $profile->bio,
            'city'            => $validated['city'] ?? $profile->city,
            'experience'      => $validated['experience'] ?? $profile->experience,
            'whatsapp'        => $validated['whatsapp'] ?? $profile->whatsapp,
            'instagram'       => $validated['instagram'] ?? $profile->instagram,
            'specializations' => isset($validated['specializations']) ? json_encode($validated['specializations']) : $profile->specializations,
            'color_primary'   => $validated['color_primary'] ?? $profile->color_primary,
            'public_visible'  => $validated['public_visible'] ?? $profile->public_visible,
        ]);

        return response()->json(['updated' => true]);
    }

    // ─── Notes ──────────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/notes
     *
     * Notes list with filters.
     * Ports Coach\CoachNotesPage.php render() logic.
     */
    public function notes(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;
        $search  = $request->query('search', '');
        $type    = $request->query('type', 'all');
        $clientFilter = $request->query('client_id', 'all');

        $notesQuery = CoachNote::where('coach_id', $coachId)->orderByDesc('created_at');

        if ($search !== '') {
            $searchTerm = '%' . $search . '%';
            $notesQuery->where(function ($q) use ($searchTerm) {
                $q->where('note', 'like', $searchTerm);
            });
        }

        if ($type !== 'all') {
            $notesQuery->where('note_type', $type);
        }

        if ($clientFilter !== 'all') {
            $notesQuery->where('client_id', (int) $clientFilter);
        }

        $notes = $notesQuery->get()->map(function ($note) {
            $client = Client::find($note->client_id);
            return [
                'id'              => $note->id,
                'client_id'       => $note->client_id,
                'client_name'     => $client->name ?? 'Cliente',
                'client_initial'  => substr($client->name ?? 'C', 0, 1),
                'note_type'       => $note->note_type ?? 'general',
                'note'            => $note->note,
                'created_at'      => Carbon::parse($note->created_at)->format('d M Y, H:i'),
                'created_at_ago'  => Carbon::parse($note->created_at)->diffForHumans(),
            ];
        });

        $noteStats = [
            'total'       => CoachNote::where('coach_id', $coachId)->count(),
            'general'     => CoachNote::where('coach_id', $coachId)->where('note_type', 'general')->count(),
            'seguimiento' => CoachNote::where('coach_id', $coachId)->where('note_type', 'seguimiento')->count(),
            'alerta'      => CoachNote::where('coach_id', $coachId)->where('note_type', 'alerta')->count(),
            'logro'       => CoachNote::where('coach_id', $coachId)->where('note_type', 'logro')->count(),
        ];

        // Clients for dropdown
        $clientIds    = $this->getCoachClientIds($coachId);
        $clientsList = Client::whereIn('id', $clientIds)->where('status', 'activo')->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'notes'      => $notes,
            'noteStats'  => $noteStats,
            'clients'    => $clientsList,
        ]);
    }

    /**
     * POST /api/v/coach/notes
     *
     * Create note.
     * Ports Coach\CoachNotesPage.php saveNote() logic.
     */
    public function createNote(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;

        $validated = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'note_type' => 'required|in:general,seguimiento,alerta,logro',
            'note'      => 'required|string|min:3|max:5000',
        ]);

        $clientIds = $this->getCoachClientIds($coachId);
        if (! $clientIds->contains($validated['client_id'])) {
            return response()->json(['error' => 'Cliente no asignado a ti.'], 403);
        }

        $note = CoachNote::create([
            'coach_id'  => $coachId,
            'client_id' => $validated['client_id'],
            'note_type' => $validated['note_type'],
            'note'      => trim($validated['note']),
        ]);

        return response()->json(['created' => true, 'id' => $note->id], 201);
    }

    /**
     * PUT /api/v/coach/notes/{id}
     *
     * Update note.
     */
    public function updateNote(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $note = CoachNote::where('id', $id)->where('coach_id', $coach->id)->first();
        if (! $note) {
            return response()->json(['error' => 'Nota no encontrada.'], 404);
        }

        $validated = $request->validate([
            'client_id' => 'nullable|integer|exists:clients,id',
            'note_type' => 'nullable|in:general,seguimiento,alerta,logro',
            'note'      => 'nullable|string|min:3|max:5000',
        ]);

        $note->update(array_filter([
            'client_id' => $validated['client_id'] ?? null,
            'note_type' => $validated['note_type'] ?? null,
            'note'      => isset($validated['note']) ? trim($validated['note']) : null,
        ], fn ($v) => $v !== null));

        return response()->json(['updated' => true]);
    }

    /**
     * DELETE /api/v/coach/notes/{id}
     *
     * Delete note.
     */
    public function deleteNote(Request $request, int $id): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $note = CoachNote::where('id', $id)->where('coach_id', $coach->id)->first();
        if (! $note) {
            return response()->json(['error' => 'Nota no encontrada.'], 404);
        }

        $note->delete();

        return response()->json(['deleted' => true]);
    }

    // ─── Brand ──────────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/brand
     *
     * Brand settings including PWA config.
     * Ports Coach\MyBrand.php mount() logic.
     */
    public function brand(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $profile = CoachProfile::where('admin_id', $coach->id)->first();
        if (! $profile) {
            $profile = CoachProfile::create([
                'admin_id'       => $coach->id,
                'slug'           => Str::slug($coach->name) . '-' . Str::random(4),
                'color_primary'  => '#E31E24',
                'public_visible' => true,
            ]);
        }

        $pwa = CoachPwaConfig::where('coach_id', $coach->id)->first();

        return response()->json([
            'coachName'       => $coach->name,
            'profile'         => [
                'id'              => $profile->id,
                'slug'            => $profile->slug ?? '',
                'bio'             => $profile->bio ?? '',
                'color_primary'   => $profile->color_primary ?? '#E31E24',
                'logo_url'        => $profile->logo_url ?? '',
                'photo_url'       => $profile->photo_url ?? '',
                'whatsapp'        => $profile->whatsapp ?? '',
                'instagram'       => $profile->instagram ?? '',
                'public_visible'  => (bool) $profile->public_visible,
            ],
            'pwa' => $pwa ? [
                'id'          => $pwa->id,
                'app_name'    => $pwa->app_name ?? 'Mi App Fitness',
                'icon_url'    => $pwa->icon_url ?? '',
                'color'       => $pwa->color ?? '#E31E24',
                'subdomain'   => $pwa->subdomain ?? '',
            ] : null,
        ]);
    }

    /**
     * PUT /api/v/coach/brand
     *
     * Update brand/PWA settings.
     * Ports Coach\MyBrand.php saveBrand() + savePwa() logic.
     */
    public function updateBrand(Request $request): JsonResponse
    {
        $coach = $this->resolveCoachOrFail($request);

        $validated = $request->validate([
            'slug'            => 'nullable|string|max:100',
            'bio'             => 'nullable|string|max:2000',
            'color_primary'   => 'nullable|string|max:20',
            'logo_url'        => 'nullable|string|max:500',
            'photo_url'       => 'nullable|string|max:500',
            'whatsapp'        => 'nullable|string|max:20',
            'instagram'       => 'nullable|string|max:100',
            'public_visible'  => 'nullable|boolean',
            'pwa_app_name'    => 'nullable|string|max:100',
            'pwa_icon_url'    => 'nullable|string|max:500',
            'pwa_color'       => 'nullable|string|max:20',
            'pwa_subdomain'   => 'nullable|string|max:100',
        ]);

        $profile = CoachProfile::where('admin_id', $coach->id)->first();
        if ($profile) {
            $profile->update(array_filter([
                'slug'           => $validated['slug'] ?? null,
                'bio'            => $validated['bio'] ?? null,
                'color_primary'  => $validated['color_primary'] ?? null,
                'logo_url'       => $validated['logo_url'] ?? null,
                'photo_url'      => $validated['photo_url'] ?? null,
                'whatsapp'       => $validated['whatsapp'] ?? null,
                'instagram'      => $validated['instagram'] ?? null,
                'public_visible' => $validated['public_visible'] ?? null,
            ], fn ($v) => $v !== null));
        }

        // PWA config
        if (isset($validated['pwa_app_name']) || isset($validated['pwa_icon_url']) || isset($validated['pwa_color']) || isset($validated['pwa_subdomain'])) {
            CoachPwaConfig::updateOrCreate(
                ['coach_id' => $coach->id],
                array_filter([
                    'app_name'  => $validated['pwa_app_name'] ?? null,
                    'icon_url'  => $validated['pwa_icon_url'] ?? null,
                    'color'     => $validated['pwa_color'] ?? null,
                    'subdomain' => $validated['pwa_subdomain'] ?? null,
                ], fn ($v) => $v !== null)
            );
        }

        return response()->json(['updated' => true]);
    }

    // ─── Features ───────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/features
     *
     * Features dashboard: pods, availability, audio, video checkins.
     * Ports Coach\CoachFeatures.php render() logic.
     */
    public function features(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;

        // Pods
        $pods = AccountabilityPod::where('coach_id', $coachId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pod) {
                $members     = PodMember::where('pod_id', $pod->id)->get();
                $memberData  = $members->map(function ($m) {
                    $client = Client::find($m->client_id);
                    return [
                        'id'        => $m->id,
                        'client_id' => $m->client_id,
                        'name'      => $client->name ?? 'Cliente',
                        'initial'   => substr($client->name ?? 'C', 0, 1),
                    ];
                });

                $lastMessage = PodMessage::where('pod_id', $pod->id)->orderByDesc('created_at')->first();

                return [
                    'id'            => $pod->id,
                    'name'          => $pod->name,
                    'description'   => $pod->description,
                    'max_members'   => $pod->max_members,
                    'is_active'     => $pod->is_active,
                    'member_count'  => $members->count(),
                    'members'       => $memberData,
                    'last_activity' => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : 'Sin actividad',
                    'created_at'    => Carbon::parse($pod->created_at)->format('d M Y'),
                ];
            });

        // Availability
        $slots = CoachAvailability::where('coach_id', $coachId)
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get()
            ->map(fn ($slot) => [
                'id'          => $slot->id,
                'day_of_week' => $slot->day_of_week,
                'time_start'  => substr($slot->time_start, 0, 5),
                'time_end'    => substr($slot->time_end, 0, 5),
                'is_active'   => $slot->is_active,
            ]);

        // Audio
        $audios = CoachAudio::where('coach_id', $coachId)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'audio_url'    => $a->audio_url,
                'duration_sec' => $a->duration_sec,
                'category'     => $a->category,
                'is_active'    => $a->is_active,
                'created_at'   => $a->created_at?->format('d M Y'),
            ]);

        // Video check-ins
        $checkinStats = [
            'total'       => VideoCheckin::where('coach_id', $coachId)->count(),
            'pending'     => VideoCheckin::where('coach_id', $coachId)->where('status', 'pending')->count(),
            'reviewed'    => VideoCheckin::where('coach_id', $coachId)->where('status', 'coach_reviewed')->count(),
            'ai_reviewed' => VideoCheckin::where('coach_id', $coachId)->where('status', 'ai_reviewed')->count(),
        ];

        return response()->json([
            'pods'          => $pods,
            'slots'         => $slots,
            'audios'        => $audios,
            'checkinStats'  => $checkinStats,
        ]);
    }

    // ─── Resources ──────────────────────────────────────────────────────

    /**
     * GET /api/v/coach/resources
     *
     * Resources/academy content.
     * Ports Coach\Resources.php render() logic.
     */
    public function resources(Request $request): JsonResponse
    {
        $coach   = $this->resolveCoachOrFail($request);
        $coachId = $coach->id;

        $academyItems = AcademyContent::orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $videoTips = CoachVideoTip::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $articles = CoachCommunityPost::orderByDesc('created_at')
            ->limit(20)
            ->get();

        $templates = PlanTemplate::where(function ($q) use ($coachId) {
            $q->where('coach_id', $coachId)->orWhere('is_public', true);
        })->orderByDesc('created_at')->get();

        return response()->json([
            'academyItems' => $academyItems,
            'videoTips'    => $videoTips,
            'articles'     => $articles,
            'templates'    => $templates,
        ]);
    }
}
