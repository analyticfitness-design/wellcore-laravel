<?php

namespace App\Livewire\Client;

use App\Models\WellcoreNotification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $lastKnownId = 0;

    /**
     * Stored as a plain array instead of a Collection so Livewire's
     * serialization payload stays small on every poll round-trip.
     * Each element is a plain array of scalar values, not a full Eloquent model.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $notifications = [];

    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    /**
     * Called by wire:poll — fires an inexpensive MAX(id) check first.
     * Only reloads the full list when a genuinely new notification exists.
     */
    public function pollNotifications(): void
    {
        $clientId = auth('wellcore')->id();

        $latestId = (int) (WellcoreNotification::where('user_id', $clientId)
            ->where('user_type', 'client')
            ->max('id') ?? 0);

        if ($latestId !== $this->lastKnownId) {
            $this->lastKnownId = $latestId;
            $this->loadNotifications();
        }
    }

    public function loadNotifications(): void
    {
        $clientId = auth('wellcore')->id();

        $rows = WellcoreNotification::where('user_id', $clientId)
            ->where('user_type', 'client')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'link', 'read_at', 'created_at']);

        // Convert to plain arrays — avoids Livewire serializing full Eloquent models.
        $this->notifications = $rows->map(fn ($n) => [
            'id'         => $n->id,
            'title'      => $n->title,
            'body'       => $n->body,
            'link'       => $n->link,
            'read_at'    => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at?->diffForHumans(),
        ])->toArray();

        $this->unreadCount = $rows->whereNull('read_at')->count();

        // Keep lastKnownId in sync so poll skips unnecessary reloads.
        if (! empty($this->notifications)) {
            $this->lastKnownId = $rows->max('id');
        }
    }

    public function markAsRead(int $id): void
    {
        WellcoreNotification::where('id', $id)
            ->where('user_id', auth('wellcore')->id())
            ->where('user_type', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->loadNotifications();
    }

    public function markAllAsRead(): void
    {
        WellcoreNotification::where('user_id', auth('wellcore')->id())
            ->where('user_type', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.client.notification-bell', [
            'notifications' => $this->notifications,
            'unreadCount'   => $this->unreadCount,
        ]);
    }
}
