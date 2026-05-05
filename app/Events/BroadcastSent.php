<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $broadcastId,
        public string $audienceType,
        public int $recipientsCount,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.community');
    }

    public function broadcastAs(): string
    {
        return 'broadcast-sent';
    }

    public function broadcastWith(): array
    {
        return [
            'broadcast_id'     => $this->broadcastId,
            'audience_type'    => $this->audienceType,
            'recipients_count' => $this->recipientsCount,
            'at'               => now()->toIso8601String(),
        ];
    }
}
