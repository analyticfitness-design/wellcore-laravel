<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use App\Models\CoachVideoTip;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Videos — WellCore'])]
class VideoLibrary extends Component
{
    public string $search = '';
    public ?int $playingId = null;

    public function play(int $id): void
    {
        $this->playingId = $this->playingId === $id ? null : $id;
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        $coachId = AssignedPlan::where('client_id', $clientId)
            ->whereNotNull('assigned_by')
            ->latest('valid_from')
            ->value('assigned_by');

        $query = CoachVideoTip::where('is_active', true)
            ->orderBy('sort_order');

        if ($coachId) {
            $query->where(function ($q) use ($coachId) {
                $q->where('coach_id', $coachId)
                  ->orWhereNull('coach_id');
            });
        }

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%");
        }

        $videos = $query->limit(50)->get();
        $playingVideo = $this->playingId ? $videos->firstWhere('id', $this->playingId) : null;

        return view('livewire.client.video-library', compact('videos', 'playingVideo'));
    }
}
