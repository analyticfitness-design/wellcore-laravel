<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $postId,
        public readonly int    $clientId,
        public readonly string $clientName,
        public readonly string $content,
        public readonly string $createdAt,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('community-post.' . $this->postId)];
    }

    public function broadcastAs(): string
    {
        return 'comment.added';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'     => $this->postId,
            'client_id'   => $this->clientId,
            'client_name' => $this->clientName,
            'content'     => $this->content,
            'created_at'  => $this->createdAt,
        ];
    }
}
