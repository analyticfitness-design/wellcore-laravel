<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MentionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ?int $postId,
        public ?int $commentId,
        public string $mentionerType,
        public int $mentionerId,
        public string $mentionedType,
        public int $mentionedId,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->mentionedType}.{$this->mentionedId}");
    }

    public function broadcastAs(): string
    {
        return 'mention-created';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'        => $this->postId,
            'comment_id'     => $this->commentId,
            'mentioner_type' => $this->mentionerType,
            'mentioner_id'   => $this->mentionerId,
            'at'             => now()->toIso8601String(),
        ];
    }
}
