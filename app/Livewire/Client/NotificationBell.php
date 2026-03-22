<?php

namespace App\Livewire\Client;

use App\Models\WellcoreNotification;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $lastKnownId = 0;

    public Collection $notifications;

    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function pollNotifications(): void
    {
        $clientId = auth('wellcore')->id();

        $latestId = WellcoreNotification::where('user_id', $clientId)
            ->where('user_type', 'client')
            ->max('id') ?? 0;

        if ($latestId !== $this->lastKnownId) {
            $this->lastKnownId = $latestId;
            $this->loadNotifications();
        }
    }

    public function loadNotifications(): void
    {
        $clientId = auth('wellcore')->id();

        $this->notifications = WellcoreNotification::where('user_id', $clientId)
            ->where('user_type', 'client')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $this->unreadCount = $this->notifications->whereNull('read_at')->count();
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
