<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\ClientXp;
use App\Models\CoachMessage;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Mis Clientes'])]
class ClientList extends Component
{
    public string $search = '';
    public ?int $expandedClient = null;

    public function toggleExpand(int $clientId): void
    {
        $this->expandedClient = $this->expandedClient === $clientId ? null : $clientId;
    }

    public function render()
    {
        $coachId = auth('wellcore')->id();

        // Get client IDs assigned to this coach
        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        // Build client query
        $query = Client::whereIn('id', $clientIds)->where('status', 'activo');

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $clients = $query->orderBy('name')->get();

        // Pre-load all related data with whereIn to avoid N+1
        $clientIds = $clients->pluck('id');

        $lastCheckins = Checkin::whereIn('client_id', $clientIds)
            ->orderByDesc('checkin_date')
            ->get()
            ->keyBy('client_id');

        $lastMessages = CoachMessage::whereIn('client_id', $clientIds)
            ->where('coach_id', $coachId)
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('client_id');

        $xpRecords = ClientXp::whereIn('client_id', $clientIds)
            ->get()
            ->keyBy('client_id');

        $activePlans = AssignedPlan::whereIn('client_id', $clientIds)
            ->where('assigned_by', $coachId)
            ->where('active', true)
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('client_id');

        $pendingCounts = Checkin::whereIn('client_id', $clientIds)
            ->whereNull('coach_reply')
            ->selectRaw('client_id, COUNT(*) as cnt')
            ->groupBy('client_id')
            ->pluck('cnt', 'client_id');

        // Enrich clients with extra data
        $clientData = $clients->map(function ($client) use ($lastCheckins, $lastMessages, $xpRecords, $activePlans, $pendingCounts) {
            $lastCheckin = $lastCheckins->get($client->id);
            $lastMessage = $lastMessages->get($client->id);
            $xp = $xpRecords->get($client->id);
            $activePlan = $activePlans->get($client->id);
            $pendingCheckins = $pendingCounts->get($client->id, 0);

            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'plan_label' => $client->plan?->label() ?? 'Sin plan',
                'status' => $client->status?->label() ?? 'Activo',
                'fecha_inicio' => $client->fecha_inicio ? Carbon::parse($client->fecha_inicio)->format('d M Y') : 'N/A',
                'last_checkin' => $lastCheckin ? Carbon::parse($lastCheckin->checkin_date)->diffForHumans() : 'Nunca',
                'last_checkin_date' => $lastCheckin ? Carbon::parse($lastCheckin->checkin_date)->format('d/m/Y') : null,
                'last_message' => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : 'Sin mensajes',
                'xp_level' => $xp->level ?? 1,
                'xp_total' => $xp->xp_total ?? 0,
                'streak_days' => $xp->streak_days ?? 0,
                'active_plan_type' => $activePlan->plan_type ?? null,
                'pending_checkins' => $pendingCheckins,
                'avatar_initial' => substr($client->name ?? 'C', 0, 1),
            ];
        });

        return view('livewire.coach.client-list', [
            'clients' => $clientData,
            'totalClients' => $clientData->count(),
        ]);
    }
}
