<?php

namespace App\Events;

class BroadcastSent
{
    public function __construct(
        public int $broadcastId,
        public string $audienceType,
        public int $recipientsCount,
    ) {}
}
