<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\VideoCheckin;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Check-ins'])]
class CheckinReview extends Component
{
    public string $replyText = '';
    public ?int $replyingTo = null;
    public bool $showReplied = false;

    // Video check-in properties
    public string $videoReplyText = '';
    public ?int $videoReplyingTo = null;
    public ?int $videoExpandedId = null;
    public bool $showVideoReviewed = false;

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

    // Video check-in methods
    public function startVideoReply(int $checkinId): void
    {
        $this->videoReplyingTo = $checkinId;
        $this->videoReplyText = '';
        $this->videoExpandedId = $checkinId;
    }

    public function cancelVideoReply(): void
    {
        $this->videoReplyingTo = null;
        $this->videoReplyText = '';
    }

    public function toggleVideoExpand(int $id): void
    {
        $this->videoExpandedId = $this->videoExpandedId === $id ? null : $id;
    }

    public function submitVideoReply(): void
    {
        if (!$this->videoReplyingTo || trim($this->videoReplyText) === '') {
            return;
        }

        $coachId = auth('wellcore')->id();

        $videoCheckin = VideoCheckin::find($this->videoReplyingTo);
        if (!$videoCheckin) return;

        // Verify this video check-in belongs to a client assigned to this coach
        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        if (!$clientIds->contains($videoCheckin->client_id)) {
            return;
        }

        $videoCheckin->update([
            'coach_response' => trim($this->videoReplyText),
            'status' => 'coach_reviewed',
            'responded_at' => now(),
        ]);

        $this->videoReplyingTo = null;
        $this->videoReplyText = '';
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

        // Video check-ins
        $videoQuery = VideoCheckin::whereIn('client_id', $clientIds);
        if (!$this->showVideoReviewed) {
            $videoQuery->where('status', 'pending');
        }
        $videoCheckins = $videoQuery->orderByDesc('created_at')->get();

        $videoCheckinData = $videoCheckins->map(function ($vc) {
            $client = Client::find($vc->client_id);
            return [
                'id' => $vc->id,
                'client_name' => $client->name ?? 'Cliente',
                'client_initial' => substr($client->name ?? 'C', 0, 1),
                'exercise_name' => $vc->exercise_name,
                'media_type' => $vc->media_type,
                'media_url' => $vc->media_url,
                'notes' => $vc->notes,
                'coach_response' => $vc->coach_response,
                'status' => $vc->status,
                'created_at' => $vc->created_at->format('d M Y, H:i'),
                'created_at_ago' => $vc->created_at->diffForHumans(),
                'responded_at' => $vc->responded_at?->diffForHumans(),
            ];
        });

        $pendingVideoCount = VideoCheckin::whereIn('client_id', $clientIds)->where('status', 'pending')->count();

        return view('livewire.coach.checkin-review', [
            'checkins' => $checkinData,
            'pendingCount' => $pendingCount,
            'videoCheckins' => $videoCheckinData,
            'pendingVideoCount' => $pendingVideoCount,
        ]);
    }
}
