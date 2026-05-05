<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostReported implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $reporterId,
        public string $reason,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('admin.community')];
        if ($this->coachAdminId) {
            $channels[] = new PrivateChannel("coach.{$this->coachAdminId}.community");
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'post-reported';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'     => $this->postId,
            'reporter_id' => $this->reporterId,
            'reason'      => $this->reason,
            'at'          => now()->toIso8601String(),
        ];
    }
}
