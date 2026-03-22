<?php

namespace App\Livewire\Client;

use App\Models\CoachAudio;
use App\Models\AssignedPlan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Audio — WellCore'])]
class AudioPlayer extends Component
{
    public string $categoryFilter = '';
    public ?int $playingId = null;

    public function play(int $id): void
    {
        $this->playingId = $this->playingId === $id ? null : $id;
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        // Obtain the most recent plan type for this client
        $planType = AssignedPlan::where('client_id', $clientId)
            ->latest('valid_from')
            ->value('plan_type') ?? 'base';

        $query = CoachAudio::where('is_active', true)
            ->orderBy('sort_order');

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        // Filter by plan access — empty plan_access array means open to everyone
        $audios = $query->get()->filter(function (CoachAudio $audio) use ($planType) {
            $planAccess = $audio->plan_access ?? [];
            return empty($planAccess)
                || in_array($planType, $planAccess)
                || in_array('all', $planAccess);
        })->values();

        $categories = CoachAudio::where('is_active', true)
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();

        $playingAudio = $this->playingId
            ? $audios->firstWhere('id', $this->playingId)
            : null;

        return view('livewire.client.audio-player', compact('audios', 'categories', 'playingAudio'));
    }
}
