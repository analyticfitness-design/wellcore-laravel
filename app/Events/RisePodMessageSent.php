<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RisePodMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $podId,
        public readonly int $senderId,
        public readonly string $senderName,
        public readonly string $senderInitial,
        public readonly int $messageId,
        public readonly string $messageText,
        public readonly string $sentAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('rise-pod.' . $this->podId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pod.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'       => $this->messageId,
            'message'  => $this->messageText,
            'name'     => $this->senderName,
            'initial'  => $this->senderInitial,
            'isOwn'    => false,
            'sent_at'  => $this->sentAt,
        ];
    }
}
