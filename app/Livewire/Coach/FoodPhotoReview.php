<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\FoodPhoto;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Fotos de Comida'])]
class FoodPhotoReview extends Component
{
    public $showReviewed = false;

    public $selectedClientId = null;

    public $noteMap = [];

    protected function getCoachClientIds(): \Illuminate\Support\Collection
    {
        return AssignedPlan::where('assigned_by', auth('wellcore')->id())
            ->pluck('client_id')
            ->unique();
    }

    public function react(int $photoId, string $reaction): void
    {
        if (! in_array($reaction, ['bien', 'mejorar'], true)) {
            return;
        }

        $photo = FoodPhoto::find($photoId);
        if (! $photo) {
            return;
        }
        if (! $this->getCoachClientIds()->contains($photo->client_id)) {
            return;
        }

        $photo->update([
            'coach_seen'     => true,
            'coach_seen_at'  => Carbon::now(),
            'coach_reaction' => $reaction,
        ]);

        Cache::forget('coach_food_pending:'.auth('wellcore')->id());

        try {
            $coach = \App\Models\Admin::find(auth('wellcore')->id());
            PushNotificationService::notifyClientFoodPhotoReacted(
                $photo->client_id,
                $coach?->name ?? 'Tu coach',
                $reaction,
                $photo->meal_name
            );
        } catch (\Throwable $e) {
            Log::warning('FoodPhotoReview::react notify failed', ['error' => $e->getMessage()]);
        }
    }

    public function saveNote(int $photoId): void
    {
        $photo = FoodPhoto::find($photoId);
        if (! $photo || ! $this->getCoachClientIds()->contains($photo->client_id)) {
            return;
        }

        $note = trim((string) ($this->noteMap[$photoId] ?? ''));
        $photo->update(['coach_note' => $note === '' ? null : $note]);
    }

    public function markSeen(int $photoId): void
    {
        $photo = FoodPhoto::find($photoId);
        if (! $photo || ! $this->getCoachClientIds()->contains($photo->client_id)) {
            return;
        }

        $photo->update(['coach_seen' => true, 'coach_seen_at' => Carbon::now()]);
        Cache::forget('coach_food_pending:'.auth('wellcore')->id());
    }

    public function toggleFilter(): void
    {
        $this->showReviewed = ! $this->showReviewed;
    }

    public function selectClient($clientId): void
    {
        $this->selectedClientId = $clientId;
    }

    public function render()
    {
        $clientIds = $this->getCoachClientIds();

        $photos = FoodPhoto::whereIn('client_id', $clientIds)
            ->where('coach_seen', $this->showReviewed)
            ->when($this->selectedClientId, fn ($q) => $q->where('client_id', $this->selectedClientId))
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        $clientsById = Client::whereIn('id', $photos->pluck('client_id')->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $allClients = Client::whereIn('id', $clientIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $pendingCount = Cache::remember(
            'coach_food_pending:'.auth('wellcore')->id(),
            60,
            fn () => FoodPhoto::whereIn('client_id', $clientIds)->where('coach_seen', false)->count()
        );

        return view('livewire.coach.food-photo-review', [
            'photos'       => $photos,
            'clientsById'  => $clientsById,
            'allClients'   => $allClients,
            'pendingCount' => $pendingCount,
        ]);
    }
}
