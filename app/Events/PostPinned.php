<?php

namespace App\Events;

class PostPinned
{
    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $actorId,
        public string $actorType,
        public ?int $hours,
    ) {}
}
