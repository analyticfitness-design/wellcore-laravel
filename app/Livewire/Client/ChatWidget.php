<?php

namespace App\Livewire\Client;

use App\Models\Admin;
use App\Models\CoachMessage;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.client')]
class ChatWidget extends Component
{
    #[Validate('required|string|min:1|max:2000')]
    public string $message = '';

    public Collection $messages;

    public ?int $coachId = null;

    public string $coachName = '';

    public function mount(): void
    {
        $clientId = auth('wellcore')->id();

        // Determine the client's coach from the most recent assigned plan
        $assignedPlan = \App\Models\AssignedPlan::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->first();

        if ($assignedPlan) {
            $coach = Admin::find($assignedPlan->assigned_by);
        }

        if (! isset($coach) || ! $coach) {
            $coach = Admin::first();
        }

        if ($coach) {
            $this->coachId = $coach->id;
            $this->coachName = $coach->name ?? 'Coach';
        }

        $this->loadMessages();
    }

    public function loadMessages(): void
    {
        $clientId = auth('wellcore')->id();

        $query = CoachMessage::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit(50);

        if ($this->coachId) {
            $query->where('coach_id', $this->coachId);
        }

        $this->messages = $query->get()->reverse()->values();

        // Mark unread coach messages as read
        CoachMessage::where('client_id', $clientId)
            ->where('direction', 'coach_to_client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sendMessage(): void
    {
        $this->validate();

        if (! $this->coachId) {
            return;
        }

        $clientId = auth('wellcore')->id();

        CoachMessage::create([
            'coach_id' => $this->coachId,
            'client_id' => $clientId,
            'message' => $this->message,
            'direction' => 'client_to_coach',
        ]);

        $this->message = '';
        $this->loadMessages();

        $this->dispatch('message-sent');
    }

    public function pollMessages(): void
    {
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.client.chat-widget');
    }
}
