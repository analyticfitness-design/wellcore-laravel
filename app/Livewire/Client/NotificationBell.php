<?php

namespace App\Livewire\Client;

use App\Models\WellcoreNotification;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAsRead(int $id): void
    {
        WellcoreNotification::where('id', $id)
            ->where('user_id', auth('wellcore')->id())
            ->where('user_type', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        WellcoreNotification::where('user_id', auth('wellcore')->id())
            ->where('user_type', 'client')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $notifications = WellcoreNotification::where('user_id', auth('wellcore')->id())
            ->where('user_type', 'client')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = $notifications->whereNull('read_at')->count();

        return view('livewire.client.notification-bell', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}
