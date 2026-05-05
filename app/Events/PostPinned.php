<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostPinned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $actorId,
        public string $actorType,
        public ?int $hours,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("coach.{$this->coachAdminId}.community");
    }

    public function broadcastAs(): string
    {
        return 'post-pinned';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'    => $this->postId,
            'actor_id'   => $this->actorId,
            'actor_type' => $this->actorType,
            'hours'      => $this->hours,
            'at'         => now()->toIso8601String(),
        ];
    }
}
