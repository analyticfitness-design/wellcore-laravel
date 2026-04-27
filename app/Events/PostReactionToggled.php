<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostReactionToggled implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $postId,
        public readonly string $reactionType,
        public readonly int    $count,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('community-post.' . $this->postId)];
    }

    public function broadcastAs(): string
    {
        return 'reaction.toggled';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'       => $this->postId,
            'reaction_type' => $this->reactionType,
            'count'         => $this->count,
        ];
    }
}
