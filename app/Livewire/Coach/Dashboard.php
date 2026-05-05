<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\Ticket;
use App\Models\TrainingLog;
use App\Support\CoachScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Coach Dashboard'])]
class Dashboard extends Component
{
    public string $greeting = '';

    public string $coachName = '';

    public string $todayDateLabel = '';

    // Stats
    public int $activeClients = 0;

    public int $pendingCheckins = 0;

    public int $unreadMessages = 0;

    public int $plansThisMonth = 0;

    public int $urgentClientsCount = 0;

    public int $openTickets = 0;

    // Lists
    public array $attentionClients = [];

    public array $recentMessages = [];

    public array $todayActivity = [];

    public array $pendingCheckinsList = [];

    public array $openTicketsList = [];

    // Chart / sparkline data
    public array $clientProgressData = [];

    public array $checkinFrequencyData = [];

    public array $sparklines = [];

    public function mount(): void
    {
        $coach = auth('wellcore')->user();
        $coachId = $coach->id;

        $hour = (int) now()->format('H');
        $this->greeting = match (true) {
            $hour < 12 => 'Buenos dias',
            $hour < 18 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        $this->coachName = explode(' ', $coach->name ?? 'Coach')[0];
        $this->todayDateLabel = mb_strtoupper(now()->locale('es')->isoFormat('dddd D MMM'));

        $clientIds = CoachScope::clientIdsFor($coachId);

        $cached = Cache::remember("coach_dashboard:{$coachId}", 300, function () use ($coachId, $clientIds) {
            return [
                'activeClients' => Client::whereIn('id', $clientIds)->where('status', 'activo')->count(),
                'pendingCheckins' => Checkin::whereIn('client_id', $clientIds)->whereNull('coach_reply')->count(),
                'unreadMessages' => CoachMessage::where('coach_id', $coachId)->where('direction', 'client_to_coach')->whereNull('read_at')->count(),
                'plansThisMonth' => AssignedPlan::where('assigned_by', $coachId)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
                'urgentClientsCount' => $this->computeUrgentCount($clientIds),
                'openTickets' => $this->computeOpenTickets($coachId),
                'attentionClients' => $this->loadAttentionClients($clientIds),
                'recentMessages' => $this->loadRecentMessages($coachId),
                'todayActivity' => $this->loadTodayActivity($clientIds, $coachId),
                'pendingCheckinsList' => $this->loadPendingCheckinsList($clientIds),
                'openTicketsList' => $this->loadOpenTickets($coachId),
                'sparklines' => $this->loadSparklines($clientIds, $coachId),
                'clientProgressData' => $this->loadClientProgressData($clientIds),
                'checkinFrequencyData' => $this->loadCheckinFrequencyData($clientIds),
            ];
        });

        $this->activeClients = $cached['activeClients'];
        $this->pendingCheckins = $cached['pendingCheckins'];
        $this->unreadMessages = $cached['unreadMessages'];
        $this->plansThisMonth = $cached['plansThisMonth'];
        $this->urgentClientsCount = $cached['urgentClientsCount'];
        $this->openTickets = $cached['openTickets'];
        $this->attentionClients = $cached['attentionClients'];
        $this->recentMessages = $cached['recentMessages'];
        $this->todayActivity = $cached['todayActivity'];
        $this->pendingCheckinsList = $cached['pendingCheckinsList'];
        $this->openTicketsList = $cached['openTicketsList'];
        $this->sparklines = $cached['sparklines'];
        $this->clientProgressData = $cached['clientProgressData'];
        $this->checkinFrequencyData = $cached['checkinFrequencyData'];
    }

    // -------------------------------------------------------------------------
    // Private scalar helpers (used inside the Cache closure)
    // -------------------------------------------------------------------------

    private function computeUrgentCount(Collection $clientIds): int
    {
        return Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->where('checkin_date', '<=', now()->subHours(48)->toDateTimeString())
            ->distinct('client_id')
            ->count('client_id');
    }

    private function computeOpenTickets(int $coachId): int
    {
        return Ticket::where('coach_id', $coachId)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
    }

    // -------------------------------------------------------------------------
    // Load methods (return arrays — no Eloquent collections)
    // -------------------------------------------------------------------------

    protected function loadAttentionClients(Collection $clientIds): array
    {
        $pendingByClient = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->selectRaw('client_id, COUNT(*) as pending_count, MIN(checkin_date) as oldest_checkin')
            ->groupBy('client_id')
            ->orderBy('oldest_checkin')
            ->limit(5)
            ->get();

        if ($pendingByClient->isEmpty()) {
            return [];
        }

        $pendingClientIds = $pendingByClient->pluck('client_id');

        $clientsById = Client::whereIn('id', $pendingClientIds)->get()->keyBy('id');
        $lastMsgByClient = CoachMessage::whereIn('client_id', $pendingClientIds)
            ->orderByDesc('created_at')
            ->get()
            ->unique('client_id')
            ->keyBy('client_id');

        $result = [];
        foreach ($pendingByClient as $row) {
            $client = $clientsById->get($row->client_id);
            if (! $client) {
                continue;
            }

            $lastMessage = $lastMsgByClient->get($row->client_id);

            $result[] = [
                'id' => $client->id,
                'name' => $client->name,
                'plan' => $client->plan?->label() ?? 'Sin plan',
                'pending_checkins' => $row->pending_count,
                'oldest_checkin' => Carbon::parse($row->oldest_checkin)->diffForHumans(),
                'last_message' => $lastMessage
                    ? Carbon::parse($lastMessage->created_at)->diffForHumans()
                    : 'Sin mensajes',
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

        if ($messages->isEmpty()) {
            return [];
        }

        $clientsById = Client::whereIn('id', $messages->pluck('client_id')->unique())
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($messages as $msg) {
            $client = $clientsById->get($msg->client_id);
            $result[] = [
                'client_name' => $client->name ?? 'Cliente',
                'message' => str()->limit($msg->message, 80),
                'time_ago' => Carbon::parse($msg->created_at)->diffForHumans(),
                'is_read' => $msg->read_at !== null,
            ];
        }

        return $result;
    }

    protected function loadUrgentClients(Collection $clientIds): array
    {
        $cutoff = now()->subHours(48)->toDateTimeString();

        $unrepliedCheckins = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->where('checkin_date', '<=', $cutoff)
            ->selectRaw('client_id, MIN(checkin_date) as oldest_checkin')
            ->groupBy('client_id')
            ->orderBy('oldest_checkin')
            ->limit(5)
            ->get();

        if ($unrepliedCheckins->isEmpty()) {
            return [];
        }

        $urgentIds = $unrepliedCheckins->pluck('client_id');
        $clientsById = Client::whereIn('id', $urgentIds)->get()->keyBy('id');
        $lastMsgByClient = CoachMessage::whereIn('client_id', $urgentIds)
            ->orderByDesc('created_at')
            ->get()
            ->unique('client_id')
            ->keyBy('client_id');

        $result = [];
        foreach ($unrepliedCheckins as $row) {
            $client = $clientsById->get($row->client_id);
            if (! $client) {
                continue;
            }

            $lastMessage = $lastMsgByClient->get($row->client_id);
            $daysWithout = Carbon::parse($row->oldest_checkin)->diffInDays(now());

            $tags = ['SIN RESPONDER'];
            if ($daysWithout >= 7) {
                $tags[] = 'SIN CHECK-IN';
            }

            $result[] = [
                'id' => $client->id,
                'name' => $client->name,
                'plan' => $client->plan?->label() ?? 'Sin plan',
                'days_without_reply' => $daysWithout,
                'last_message_ago' => $lastMessage
                    ? Carbon::parse($lastMessage->created_at)->diffForHumans()
                    : 'Sin mensajes',
                'tags' => $tags,
            ];
        }

        return $result;
    }

    protected function loadTodayActivity(Collection $clientIds, int $coachId): array
    {
        $checkins = Checkin::whereIn('client_id', $clientIds)
            ->whereDate('checkin_date', today())
            ->get(['client_id', 'checkin_date as created_at_raw'])
            ->map(fn ($r) => ['type' => 'checkin', 'client_id' => $r->client_id, 'created_at' => $r->created_at_raw]);

        $trainings = TrainingLog::whereIn('client_id', $clientIds)
            ->whereDate('log_date', today())
            ->where('completed', true)
            ->get(['client_id', 'log_date as created_at_raw'])
            ->map(fn ($r) => ['type' => 'training', 'client_id' => $r->client_id, 'created_at' => $r->created_at_raw]);

        $messages = CoachMessage::where('coach_id', $coachId)
            ->whereDate('created_at', today())
            ->where('direction', 'client_to_coach')
            ->get(['client_id', 'created_at'])
            ->map(fn ($r) => ['type' => 'message', 'client_id' => $r->client_id, 'created_at' => (string) $r->created_at]);

        $events = collect($checkins)->merge($trainings)->merge($messages);

        if ($events->isEmpty()) {
            return [];
        }

        $allClientIds = $events->pluck('client_id')->unique();
        $clientsById = Client::whereIn('id', $allClientIds)->get()->keyBy('id');

        $colorMap = [
            'checkin' => 'success',
            'training' => 'info',
            'message' => 'accent',
        ];

        return $events
            ->sortByDesc('created_at')
            ->take(10)
            ->map(function (array $event) use ($clientsById, $colorMap) {
                $client = $clientsById->get($event['client_id']);

                return [
                    'type' => $event['type'],
                    'client_name' => $client->name ?? 'Cliente',
                    'time_ago' => Carbon::parse($event['created_at'])->diffForHumans(),
                    'color' => $colorMap[$event['type']],
                ];
            })
            ->values()
            ->toArray();
    }

    protected function loadPendingCheckinsList(Collection $clientIds): array
    {
        $checkins = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->orderBy('checkin_date')
            ->limit(8)
            ->get(['client_id', 'checkin_date']);

        if ($checkins->isEmpty()) {
            return [];
        }

        $clientsById = Client::whereIn('id', $checkins->pluck('client_id')->unique())
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($checkins as $checkin) {
            $client = $clientsById->get($checkin->client_id);
            $date = Carbon::parse($checkin->checkin_date);
            $result[] = [
                'client_name' => $client->name ?? 'Cliente',
                'checkin_date_label' => $date->diffForHumans(),
                'week_number' => $date->weekOfYear,
            ];
        }

        return $result;
    }

    protected function loadOpenTickets(int $coachId): array
    {
        $tickets = Ticket::where('coach_id', $coachId)
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->limit(5)
            ->get(['id', 'client_id', 'client_name', 'description', 'status', 'priority', 'created_at']);

        if ($tickets->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($tickets as $ticket) {
            $result[] = [
                'id' => $ticket->id,
                'title' => str()->limit($ticket->description, 60),
                'client_name' => $ticket->client_name ?? 'Cliente',
                'status' => $ticket->status instanceof \BackedEnum ? $ticket->status->value : $ticket->status,
                'priority' => $ticket->priority instanceof \BackedEnum ? $ticket->priority->value : $ticket->priority,
                'created_ago' => Carbon::parse($ticket->created_at)->diffForHumans(),
            ];
        }

        return $result;
    }

    protected function loadSparklines(Collection $clientIds, int $coachId): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $since = $days->first();

        // Checkins per day
        $checkinRows = Checkin::whereIn('client_id', $clientIds)
            ->where('checkin_date', '>=', $since)
            ->selectRaw('DATE(checkin_date) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        // Messages per day
        $messageRows = CoachMessage::where('coach_id', $coachId)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        // Tickets per day
        $ticketRows = Ticket::where('coach_id', $coachId)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        // Active clients count is static — repeat for all 7 days
        $activeCount = Client::whereIn('id', $clientIds)->where('status', 'activo')->count();

        $fillSeries = function (Collection $rows) use ($days): array {
            return $days->map(fn ($day) => (int) ($rows[$day] ?? 0))->values()->toArray();
        };

        return [
            'clients' => $days->map(fn () => $activeCount)->values()->toArray(),
            'checkins' => $fillSeries($checkinRows),
            'messages' => $fillSeries($messageRows),
            'tickets' => $fillSeries($ticketRows),
        ];
    }

    // -------------------------------------------------------------------------
    // Chart helpers (kept for backward compat — now called inside cache closure)
    // -------------------------------------------------------------------------

    protected function loadClientProgressData(Collection $clientIds): array
    {
        return TrainingLog::whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('log_date', '>=', now()->subWeeks(4)->toDateString())
            ->join('clients', 'training_logs.client_id', '=', 'clients.id')
            ->selectRaw('clients.name, COUNT(*) as sessions')
            ->groupBy('clients.name', 'training_logs.client_id')
            ->orderByDesc('sessions')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name' => explode(' ', $row->name)[0],
                'sessions' => (int) $row->sessions,
            ])
            ->toArray();
    }

    protected function loadCheckinFrequencyData(Collection $clientIds): array
    {
        return Checkin::whereIn('client_id', $clientIds)
            ->where('checkin_date', '>=', now()->subWeeks(8)->toDateString())
            ->selectRaw('YEARWEEK(checkin_date, 1) as yw, COUNT(*) as count')
            ->groupBy('yw')
            ->orderBy('yw')
            ->get()
            ->map(function ($row) {
                $week = substr($row->yw, 4);

                return [
                    'week' => 'Sem '.$week,
                    'count' => (int) $row->count,
                ];
            })
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // Legacy wrapper kept for backward compatibility with any existing blade
    // references to loadChartData(). Delegates to the split methods.
    // -------------------------------------------------------------------------
    protected function loadChartData(Collection $clientIds): void
    {
        $this->clientProgressData = $this->loadClientProgressData($clientIds);
        $this->checkinFrequencyData = $this->loadCheckinFrequencyData($clientIds);
    }

    public function render()
    {
        return view('livewire.coach.dashboard');
    }
}
