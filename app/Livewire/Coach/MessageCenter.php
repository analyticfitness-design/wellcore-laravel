<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\CoachMessage;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Mensajes'])]
class MessageCenter extends Component
{
    public ?int $selectedClientId = null;
    public string $newMessage = '';

    public function selectClient(int $clientId): void
    {
        $this->selectedClientId = $clientId;
        $this->newMessage = '';

        // Mark messages from this client as read
        $coachId = auth('wellcore')->id();
        CoachMessage::where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->where('direction', 'client_to_coach')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sendMessage(): void
    {
        if (!$this->selectedClientId || trim($this->newMessage) === '') {
            return;
        }

        $coachId = auth('wellcore')->id();

        // Verify this client is assigned to this coach
        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        if (!$clientIds->contains($this->selectedClientId)) {
            return;
        }

        CoachMessage::create([
            'coach_id' => $coachId,
            'client_id' => $this->selectedClientId,
            'message' => trim($this->newMessage),
            'direction' => 'coach_to_client',
        ]);

        $this->newMessage = '';
    }

    public function render()
    {
        $coachId = auth('wellcore')->id();

        // Get client IDs assigned to this coach
        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        // Build client list with unread counts
        $clients = Client::whereIn('id', $clientIds)
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
                    'id' => $client->id,
                    'name' => $client->name,
                    'initial' => substr($client->name ?? 'C', 0, 1),
                    'plan' => $client->plan?->label() ?? 'Sin plan',
                    'unread_count' => $unreadCount,
                    'last_message_preview' => $lastMessage ? str()->limit($lastMessage->message, 40) : 'Sin mensajes',
                    'last_message_time' => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : null,
                ];
            });

        // Get conversation messages for selected client
        $messages = collect();
        $selectedClient = null;
        if ($this->selectedClientId) {
            $selectedClient = Client::find($this->selectedClientId);
            $messages = CoachMessage::where('coach_id', $coachId)
                ->where('client_id', $this->selectedClientId)
                ->orderBy('created_at')
                ->get()
                ->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'message' => $msg->message,
                        'direction' => $msg->direction,
                        'is_coach' => $msg->direction === 'coach_to_client',
                        'time' => Carbon::parse($msg->created_at)->format('d/m H:i'),
                        'time_ago' => Carbon::parse($msg->created_at)->diffForHumans(),
                    ];
                });
        }

        return view('livewire.coach.message-center', [
            'clients' => $clients,
            'messages' => $messages,
            'selectedClient' => $selectedClient,
        ]);
    }
}
