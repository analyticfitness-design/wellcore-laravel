<?php

namespace App\Livewire\Client;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class ChallengesView extends Component
{
    public Collection $challenges;

    public Collection $participations;

    public function mount(): void
    {
        $this->loadChallenges();
    }

    public function loadChallenges(): void
    {
        $this->challenges = Challenge::where('is_active', true)
            ->orderByDesc('start_date')
            ->get();

        $clientId = auth('wellcore')->id();

        $this->participations = ChallengeParticipant::where('client_id', $clientId)
            ->get()
            ->keyBy('challenge_id');
    }

    public function join(int $challengeId): void
    {
        $clientId = auth('wellcore')->id();

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
            'client_id' => $clientId,
            'progress' => 0,
            'completed' => false,
            'joined_at' => now(),
        ]);

        $this->loadChallenges();

        $this->dispatch('challenge-joined');
    }

    public function getProgressPercentage(int $challengeId): float
    {
        $participation = $this->participations->get($challengeId);
        $challenge = $this->challenges->firstWhere('id', $challengeId);

        if (! $participation || ! $challenge || ! $challenge->goal_value) {
            return 0;
        }

        return min(100, round(($participation->progress / $challenge->goal_value) * 100, 1));
    }

    public function render()
    {
        return view('livewire.client.challenges-view');
    }
}
