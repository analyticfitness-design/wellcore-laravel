<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatReactionToggled implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $coachId,
        public readonly int $clientId,
        public readonly int $messageId,
        public readonly string $emoji,
        public readonly array $counts,
        public readonly string $action,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->coachId . '-' . $this->clientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reaction.toggled';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'emoji'      => $this->emoji,
            'counts'     => $this->counts,
            'action'     => $this->action,
        ];
    }
}
