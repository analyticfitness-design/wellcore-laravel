<?php

namespace App\Listeners;

use App\Events\MentionCreated;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyMentionedUsers implements ShouldQueue
{
    public function __construct(private PushNotificationService $push) {}

    public function handle(MentionCreated $event): void
    {
        $this->push->notifyMention(
            mentionedType: $event->mentionedType,
            mentionedId: $event->mentionedId,
            mentionerType: $event->mentionerType,
            mentionerId: $event->mentionerId,
            postId: $event->postId,
            commentId: $event->commentId,
        );
    }
}
