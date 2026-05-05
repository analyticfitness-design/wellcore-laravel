<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoachCommunityActivity implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $coachId,
        public string $eventType,
        public int $clientId,
        public string $clientName,
        public array $payload = [],
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("coach.{$this->coachId}.community");
    }

    public function broadcastAs(): string
    {
        return 'coach-community-activity';
    }

    public function broadcastWith(): array
    {
        return [
            'coach_id' => $this->coachId,
            'event_type' => $this->eventType,
            'client_id' => $this->clientId,
            'client_name' => $this->clientName,
            'payload' => $this->payload,
            'at' => now()->toIso8601String(),
        ];
    }
}
