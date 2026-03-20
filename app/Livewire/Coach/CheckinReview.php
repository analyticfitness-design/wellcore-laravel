<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Check-ins'])]
class CheckinReview extends Component
{
    public string $replyText = '';
    public ?int $replyingTo = null;
    public bool $showReplied = false;

    public function startReply(int $checkinId): void
    {
        $this->replyingTo = $checkinId;
        $this->replyText = '';
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->replyText = '';
    }

    public function reply(): void
    {
        if (!$this->replyingTo || trim($this->replyText) === '') {
            return;
        }

        $coachId = auth('wellcore')->id();

        // Verify this check-in belongs to a client assigned to this coach
        $checkin = Checkin::find($this->replyingTo);
        if (!$checkin) return;

        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        if (!$clientIds->contains($checkin->client_id)) {
            return;
        }

        $checkin->update([
            'coach_reply' => trim($this->replyText),
            'replied_at' => now(),
        ]);

        $this->replyingTo = null;
        $this->replyText = '';
    }

    public function render()
    {
        $coachId = auth('wellcore')->id();

        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        // Build query based on filter
        $query = Checkin::whereIn('client_id', $clientIds);

        if (!$this->showReplied) {
            $query->whereNull('coach_reply');
        }

        $checkins = $query->orderByDesc('checkin_date')->get();

        // Enrich with client info
        $checkinData = $checkins->map(function ($checkin) {
            $client = Client::find($checkin->client_id);
            return [
                'id' => $checkin->id,
                'client_name' => $client->name ?? 'Cliente',
                'client_initial' => substr($client->name ?? 'C', 0, 1),
                'client_plan' => $client->plan?->label() ?? 'Sin plan',
                'week_label' => $checkin->week_label,
                'checkin_date' => Carbon::parse($checkin->checkin_date)->format('d M Y'),
                'checkin_date_ago' => Carbon::parse($checkin->checkin_date)->diffForHumans(),
                'bienestar' => $checkin->bienestar,
                'dias_entrenados' => $checkin->dias_entrenados,
                'nutricion' => $checkin->nutricion,
                'rpe' => $checkin->rpe,
                'comentario' => $checkin->comentario,
                'coach_reply' => $checkin->coach_reply,
                'replied_at' => $checkin->replied_at ? Carbon::parse($checkin->replied_at)->diffForHumans() : null,
            ];
        });

        $pendingCount = Checkin::whereIn('client_id', $clientIds)->whereNull('coach_reply')->count();

        return view('livewire.coach.checkin-review', [
            'checkins' => $checkinData,
            'pendingCount' => $pendingCount,
        ]);
    }
}
