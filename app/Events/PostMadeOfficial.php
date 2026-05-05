<?php

namespace App\Events;

class PostMadeOfficial
{
    public function __construct(
        public int $postId,
        public ?int $coachAdminId,
        public int $actorId,
        public string $actorType,
    ) {}
}
