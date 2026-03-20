<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\CoachMessage;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Coach Dashboard'])]
class Dashboard extends Component
{
    public string $greeting = '';
    public string $coachName = '';

    // Stats
    public int $activeClients = 0;
    public int $pendingCheckins = 0;
    public int $unreadMessages = 0;
    public int $plansThisMonth = 0;

    // Clients needing attention
    public array $attentionClients = [];

    // Recent messages
    public array $recentMessages = [];

    public function mount(): void
    {
        $coach = auth('wellcore')->user();
        $coachId = $coach->id;

        // Greeting based on time of day
        $hour = (int) now()->format('H');
        if ($hour < 12) {
            $this->greeting = 'Buenos dias';
        } elseif ($hour < 18) {
            $this->greeting = 'Buenas tardes';
        } else {
            $this->greeting = 'Buenas noches';
        }

        $this->coachName = explode(' ', $coach->name ?? 'Coach')[0];

        // Get client IDs assigned to this coach via assigned_plans
        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        // Active clients
        $clients = Client::whereIn('id', $clientIds)
            ->where('status', 'activo')
            ->get();
        $this->activeClients = $clients->count();

        // Pending check-ins (no coach reply yet)
        $this->pendingCheckins = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->count();

        // Unread messages from clients
        $this->unreadMessages = CoachMessage::where('coach_id', $coachId)
            ->where('direction', 'client_to_coach')
            ->whereNull('read_at')
            ->count();

        // Plans assigned this month
        $this->plansThisMonth = AssignedPlan::where('assigned_by', $coachId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $this->loadAttentionClients($clientIds);
        $this->loadRecentMessages($coachId);
    }

    protected function loadAttentionClients($clientIds): void
    {
        // Clients with unreplied check-ins, ordered by oldest unreplied
        $pendingByClient = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->selectRaw('client_id, COUNT(*) as pending_count, MIN(checkin_date) as oldest_checkin')
            ->groupBy('client_id')
            ->orderBy('oldest_checkin')
            ->limit(5)
            ->get();

        $this->attentionClients = [];
        foreach ($pendingByClient as $row) {
            $client = Client::find($row->client_id);
            if (!$client) continue;

            $lastMessage = CoachMessage::where('client_id', $row->client_id)
                ->orderByDesc('created_at')
                ->first();

            $this->attentionClients[] = [
                'id' => $client->id,
                'name' => $client->name,
                'plan' => $client->plan?->label() ?? 'Sin plan',
                'pending_checkins' => $row->pending_count,
                'oldest_checkin' => Carbon::parse($row->oldest_checkin)->diffForHumans(),
                'last_message' => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : 'Sin mensajes',
            ];
        }
    }

    protected function loadRecentMessages(int $coachId): void
    {
        $messages = CoachMessage::where('coach_id', $coachId)
            ->where('direction', 'client_to_coach')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $this->recentMessages = [];
        foreach ($messages as $msg) {
            $client = Client::find($msg->client_id);
            $this->recentMessages[] = [
                'client_name' => $client->name ?? 'Cliente',
                'message' => str()->limit($msg->message, 80),
                'time_ago' => Carbon::parse($msg->created_at)->diffForHumans(),
                'is_read' => $msg->read_at !== null,
            ];
        }
    }

    public function render()
    {
        return view('livewire.coach.dashboard');
    }
}
