<?php

namespace App\Livewire\Client;

use App\Models\CoachMessage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.client')]
class ChatWidget extends Component
{
    #[Validate('required|string|min:1|max:2000')]
    public string $message = '';

    public ?int $coachId = null;

    public string $coachName = '';

    /** Whether the authenticated client has a real coach assigned. */
    public bool $hasCoach = false;

    public function mount(): void
    {
        $clientId = auth('wellcore')->id();

        // Determine the client's coach from the most recent assigned plan.
        // Only accept a coach that was explicitly assigned — never fall back
        // to Admin::first() which could attach the wrong person.
        $assignedPlan = \App\Models\AssignedPlan::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->first();

        $coach = null;
        if ($assignedPlan && $assignedPlan->assigned_by) {
            $coach = \App\Models\Admin::find($assignedPlan->assigned_by);
        }

        if (! $coach) {
            // No real coach assigned — leave coachId null and surface a
            // friendly message in the view instead of silently chatting
            // with a random admin.
            $this->coachId   = null;
            $this->coachName = 'Coach no asignado';
            $this->hasCoach  = false;
            return;
        }

        $this->coachId   = $coach->id;
        $this->coachName = $coach->name ?? 'Coach';
        $this->hasCoach  = true;

        // Mark existing unread messages as read on initial load.
        $this->markMessagesRead();
    }

    protected function fetchMessages(): \Illuminate\Support\Collection
    {
        if (! $this->hasCoach) {
            return collect();
        }

        $clientId = auth('wellcore')->id();

        return CoachMessage::where('client_id', $clientId)
            ->where('coach_id', $this->coachId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();
    }

    protected function markMessagesRead(): void
    {
        if (! $this->hasCoach) {
            return;
        }

        $clientId = auth('wellcore')->id();

        CoachMessage::where('client_id', $clientId)
            ->where('coach_id', $this->coachId)
            ->where('direction', 'coach_to_client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Called by wire:poll.30s.visible — only fires when the component is in
     * the viewport. Skips the expensive UPDATE when there are no new messages.
     */
    public function pollMessages(): void
    {
        if (! $this->hasCoach) {
            return;
        }

        $clientId = auth('wellcore')->id();

        $unreadCount = CoachMessage::where('client_id', $clientId)
            ->where('coach_id', $this->coachId)
            ->where('direction', 'coach_to_client')
            ->whereNull('read_at')
            ->count();

        if ($unreadCount > 0) {
            // There are genuinely new messages — mark them read.
            // render() will re-fetch the full list automatically.
            $this->markMessagesRead();
        }
    }

    public function sendMessage(): void
    {
        if (! $this->hasCoach) {
            return;
        }

        $this->validate();

        $clientId = auth('wellcore')->id();

        CoachMessage::create([
            'coach_id'  => $this->coachId,
            'client_id' => $clientId,
            'message'   => $this->message,
            'direction' => 'client_to_coach',
        ]);

        $this->message = '';

        $this->dispatch('message-sent');
    }

    public function render()
    {
        return view('livewire.client.chat-widget', [
            'messages' => $this->fetchMessages(),
        ]);
    }
}
