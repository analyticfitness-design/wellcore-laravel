<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\ClientXp;
use App\Models\CoachMessage;
use App\Models\CoachNote;
use App\Models\TrainingLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Kanban Clientes'])]
class ClientKanban extends Component
{
    public array $columns = [];
    public int $totalClients = 0;
    public string $search = '';

    /** Client detail modal */
    public bool $showDetail = false;
    public array $detailClient = [];

    public function mount(): void
    {
        $this->loadBoard();
    }

    public function updatedSearch(): void
    {
        $this->loadBoard();
    }

    // -------------------------------------------------------------------------
    // Cache helpers
    // -------------------------------------------------------------------------

    private function boardCacheKey(int $coachId): string
    {
        $searchSlug = $this->search !== '' ? md5($this->search) : 'all';
        $today      = now()->toDateString();          // date boundary prevents stale column labels

        return "kanban:coach:{$coachId}:search:{$searchSlug}:date:{$today}";
    }

    private function invalidateBoardCache(int $coachId): void
    {
        // Bust every cached search variant for this coach by iterating the
        // known date key. Cache::tags() would be cleaner but requires a
        // taggable driver; the date-keyed pattern is driver-agnostic and
        // naturally expires at midnight anyway.
        $today = now()->toDateString();
        Cache::forget("kanban:coach:{$coachId}:search:all:date:{$today}");
        if ($this->search !== '') {
            Cache::forget($this->boardCacheKey($coachId));
        }
    }

    // -------------------------------------------------------------------------
    // Board load (cached 60 s)
    // -------------------------------------------------------------------------

    public function loadBoard(): void
    {
        $coachId = auth('wellcore')->id();
        $cacheKey = $this->boardCacheKey($coachId);

        ['columns' => $this->columns, 'totalClients' => $this->totalClients]
            = Cache::remember($cacheKey, 60, fn () => $this->buildBoard($coachId));
    }

    private function buildBoard(int $coachId): array
    {
        // Get all client IDs assigned to this coach via assigned_plans
        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        // Get all clients (not just active — we want to show all statuses on the board)
        $query = Client::whereIn('id', $clientIds);

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $clients = $query->orderBy('name')->get();

        // Get last activity dates for all clients in one batch
        $lastCheckins = Checkin::whereIn('client_id', $clientIds)
            ->selectRaw('client_id, MAX(checkin_date) as last_checkin')
            ->groupBy('client_id')
            ->pluck('last_checkin', 'client_id');

        $lastTraining = TrainingLog::whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->selectRaw('client_id, MAX(log_date) as last_training')
            ->groupBy('client_id')
            ->pluck('last_training', 'client_id');

        $pendingCheckins = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->selectRaw('client_id, COUNT(*) as cnt')
            ->groupBy('client_id')
            ->pluck('cnt', 'client_id');

        $unreadMessages = CoachMessage::where('coach_id', $coachId)
            ->where('direction', 'client_to_coach')
            ->whereNull('read_at')
            ->whereIn('client_id', $clientIds)
            ->selectRaw('client_id, COUNT(*) as cnt')
            ->groupBy('client_id')
            ->pluck('cnt', 'client_id');

        $columns = [
            'nuevo' => [
                'title' => 'Nuevos',
                'icon' => 'sparkles',
                'color' => 'blue',
                'clients' => [],
            ],
            'activo' => [
                'title' => 'Activos',
                'icon' => 'bolt',
                'color' => 'emerald',
                'clients' => [],
            ],
            'riesgo' => [
                'title' => 'En Riesgo',
                'icon' => 'exclamation-triangle',
                'color' => 'amber',
                'clients' => [],
            ],
            'inactivo' => [
                'title' => 'Inactivos',
                'icon' => 'pause-circle',
                'color' => 'red',
                'clients' => [],
            ],
        ];

        $now = Carbon::now();

        foreach ($clients as $client) {
            $lastCheckinDate = isset($lastCheckins[$client->id])
                ? Carbon::parse($lastCheckins[$client->id])
                : null;

            $lastTrainingDate = isset($lastTraining[$client->id])
                ? Carbon::parse($lastTraining[$client->id])
                : null;

            // Most recent activity = max of checkin and training dates
            $lastActivity = null;
            if ($lastCheckinDate && $lastTrainingDate) {
                $lastActivity = $lastCheckinDate->greaterThan($lastTrainingDate) ? $lastCheckinDate : $lastTrainingDate;
            } elseif ($lastCheckinDate) {
                $lastActivity = $lastCheckinDate;
            } elseif ($lastTrainingDate) {
                $lastActivity = $lastTrainingDate;
            }

            $daysSinceActivity = $lastActivity ? (int) $lastActivity->diffInDays($now) : null;
            $daysSinceStart = $client->fecha_inicio
                ? (int) Carbon::parse($client->fecha_inicio)->diffInDays($now)
                : null;

            $column = $this->classifyClient($client, $daysSinceActivity, $daysSinceStart);

            $clientData = [
                'id' => $client->id,
                'name' => $client->name,
                'avatar_initial' => mb_strtoupper(mb_substr($client->name ?? 'C', 0, 1)),
                'plan_label' => $client->plan?->label() ?? 'Sin plan',
                'plan_value' => $client->plan?->value ?? '',
                'status_label' => $client->status?->label() ?? 'Desconocido',
                'status_value' => $client->status?->value ?? '',
                'fecha_inicio' => $client->fecha_inicio ? Carbon::parse($client->fecha_inicio)->format('d M Y') : null,
                'days_since_activity' => $daysSinceActivity,
                'last_activity_human' => $lastActivity ? $lastActivity->diffForHumans() : 'Sin actividad',
                'last_checkin_date' => $lastCheckinDate ? $lastCheckinDate->format('d/m') : null,
                'last_training_date' => $lastTrainingDate ? $lastTrainingDate->format('d/m') : null,
                'pending_checkins' => $pendingCheckins[$client->id] ?? 0,
                'unread_messages' => $unreadMessages[$client->id] ?? 0,
            ];

            $columns[$column]['clients'][] = $clientData;
        }

        return ['columns' => $columns, 'totalClients' => $clients->count()];
    }

    /**
     * Classify a client into a kanban column based on activity.
     * - Nuevo: registered in last 14 days
     * - Activo: has activity in last 7 days
     * - En Riesgo: no activity in 7-21 days
     * - Inactivo: no activity in 21+ days OR DB status is inactivo/pendiente
     */
    private function classifyClient(Client $client, ?int $daysSinceActivity, ?int $daysSinceStart): string
    {
        // If DB status is explicitly inactive, always show in inactivo
        if ($client->status?->value === 'inactivo' || $client->status?->value === 'suspendido' || $client->status?->value === 'congelado') {
            return 'inactivo';
        }

        // New client: registered in last 14 days
        if ($daysSinceStart !== null && $daysSinceStart <= 14) {
            return 'nuevo';
        }

        // No activity at all → inactive
        if ($daysSinceActivity === null) {
            return 'inactivo';
        }

        // Active: activity within 7 days
        if ($daysSinceActivity <= 7) {
            return 'activo';
        }

        // At risk: 8-21 days without activity
        if ($daysSinceActivity <= 21) {
            return 'riesgo';
        }

        // Inactive: 21+ days
        return 'inactivo';
    }

    /**
     * Handle drag-and-drop: move client to a different column.
     * This updates the client's status in the DB when moving to inactivo/activo.
     */
    public function moveClient(int $clientId, string $targetColumn): void
    {
        // Remove client from current column
        $movedClient = null;
        foreach ($this->columns as $colKey => &$column) {
            foreach ($column['clients'] as $idx => $client) {
                if ($client['id'] === $clientId) {
                    $movedClient = $client;
                    array_splice($column['clients'], $idx, 1);
                    break 2;
                }
            }
        }
        unset($column);

        if (!$movedClient) {
            return;
        }

        // Add to target column
        if (isset($this->columns[$targetColumn])) {
            $this->columns[$targetColumn]['clients'][] = $movedClient;
        }

        // Optionally update DB status when moving to inactivo or back to activo
        $dbClient = Client::find($clientId);
        if ($dbClient) {
            if ($targetColumn === 'inactivo' && $dbClient->status?->value !== 'inactivo') {
                $dbClient->update(['status' => 'inactivo']);
            } elseif (in_array($targetColumn, ['activo', 'nuevo', 'riesgo']) &&
                in_array($dbClient->status?->value, ['inactivo', 'congelado'])) {
                $dbClient->update(['status' => 'activo']);
            }
        }

        // Create a coach note about the move
        $coachId = auth('wellcore')->id();
        $columnLabels = [
            'nuevo' => 'Nuevos',
            'activo' => 'Activos',
            'riesgo' => 'En Riesgo',
            'inactivo' => 'Inactivos',
        ];
        CoachNote::create([
            'coach_id' => $coachId,
            'client_id' => $clientId,
            'note' => 'Movido a columna "' . ($columnLabels[$targetColumn] ?? $targetColumn) . '" en el Kanban.',
            'note_type' => 'seguimiento',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->invalidateBoardCache($coachId);

        $this->dispatch('notify', type: 'success', message: 'Cliente movido a ' . ($columnLabels[$targetColumn] ?? $targetColumn));
    }

    public function openDetail(int $clientId): void
    {
        $client = Client::find($clientId);
        if (!$client) {
            return;
        }

        $coachId = auth('wellcore')->id();
        $xp = ClientXp::where('client_id', $clientId)->first();

        $lastCheckin = Checkin::where('client_id', $clientId)
            ->orderByDesc('checkin_date')
            ->first();

        $recentNotes = CoachNote::where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(fn($n) => [
                'note' => str()->limit($n->note, 100),
                'type' => $n->note_type,
                'date' => Carbon::parse($n->created_at)->diffForHumans(),
            ])
            ->toArray();

        $activePlan = AssignedPlan::where('client_id', $clientId)
            ->where('assigned_by', $coachId)
            ->where('active', true)
            ->orderByDesc('created_at')
            ->first();

        $this->detailClient = [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'plan_label' => $client->plan?->label() ?? 'Sin plan',
            'status_label' => $client->status?->label() ?? 'Desconocido',
            'fecha_inicio' => $client->fecha_inicio ? Carbon::parse($client->fecha_inicio)->format('d M Y') : 'N/A',
            'avatar_initial' => mb_strtoupper(mb_substr($client->name ?? 'C', 0, 1)),
            'xp_level' => $xp->level ?? 1,
            'xp_total' => $xp->xp_total ?? 0,
            'streak_days' => $xp->streak_days ?? 0,
            'last_checkin' => $lastCheckin ? Carbon::parse($lastCheckin->checkin_date)->diffForHumans() : 'Sin check-ins',
            'last_checkin_bienestar' => $lastCheckin?->bienestar,
            'active_plan_type' => $activePlan?->plan_type ?? 'N/A',
            'recent_notes' => $recentNotes,
        ];

        $this->showDetail = true;
    }

    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->detailClient = [];
    }

    public function render()
    {
        return view('livewire.coach.client-kanban');
    }
}
