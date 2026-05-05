<?php

namespace App\Events;

class MentionCreated
{
    public function __construct(
        public ?int $postId,
        public ?int $commentId,
        public string $mentionerType,
        public int $mentionerId,
        public string $mentionedType,
        public int $mentionedId,
    ) {}
}
