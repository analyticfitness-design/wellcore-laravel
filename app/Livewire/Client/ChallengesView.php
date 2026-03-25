<?php

namespace App\Livewire\Client;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class ChallengesView extends Component
{
    public bool $showSuccess = false;
    public string $lastChallengeName = '';

    public function markComplete(int $participantId): void
    {
        $clientId = auth('wellcore')->id();

        $participant = ChallengeParticipant::where('id', $participantId)
            ->where('client_id', $clientId)
            ->first();

        if (! $participant) {
            return;
        }

        $this->lastChallengeName = $participant->challenge->title ?? '';

        $participant->update([
            'completed'    => true,
            'completed_at' => now(),
        ]);

        $this->showSuccess = true;
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    public function join(int $challengeId): void
    {
        $clientId = auth('wellcore')->id();

        // Re-use the already-loaded challenge from the render data to avoid
        // an extra query; the exists() check is a cheap COUNT before creating.
        $exists = ChallengeParticipant::where('challenge_id', $challengeId)
            ->where('client_id', $clientId)
            ->exists();

        if ($exists) {
            return;
        }

        $challenge = Challenge::where('id', $challengeId)
            ->where('is_active', true)
            ->first();

        if (! $challenge) {
            return;
        }

        ChallengeParticipant::create([
            'challenge_id' => $challengeId,
            'client_id'   => $clientId,
            'progress'    => 0,
            'completed'   => false,
            'joined_at'   => now(),
        ]);

        $this->dispatch('challenge-joined');
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        // ONE query: load active challenges + the current client's participation
        // via eager loading. Eliminates the N+1 caused by getProgressPercentage()
        // calling ChallengeParticipant::where() and Challenge::find() per row.
        $challenges = Challenge::where('is_active', true)
            ->with([
                'participants' => fn ($q) => $q->where('client_id', $clientId)
                                               ->select(['id', 'challenge_id', 'client_id', 'progress', 'completed', 'joined_at']),
            ])
            ->orderByDesc('start_date')
            ->limit(50)
            ->get();

        // Build per-challenge participation + progress percentage in PHP — no extra queries.
        $challenges->each(function (Challenge $challenge) {
            $participation = $challenge->participants->first();
            $challenge->my_participation  = $participation;
            $challenge->my_progress_pct   = ($participation && $challenge->goal_value)
                ? min(100, round(($participation->progress / $challenge->goal_value) * 100, 1))
                : 0;
        });

        // Keep a keyed collection for backward-compatible Blade access.
        $participations = $challenges
            ->filter(fn ($c) => $c->my_participation !== null)
            ->keyBy('id')
            ->map(fn ($c) => $c->my_participation);

        return view('livewire.client.challenges-view', [
            'challenges'     => $challenges,
            'participations' => $participations,
        ]);
    }
}
