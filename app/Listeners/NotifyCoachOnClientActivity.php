<?php

namespace App\Listeners;

use App\Events\CoachCommunityActivity;
use App\Models\CoachNotificationPreference;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCoachOnClientActivity implements ShouldQueue
{
    public function __construct(private PushNotificationService $push) {}

    public function handle(CoachCommunityActivity $event): void
    {
        $prefs = CoachNotificationPreference::forCoach($event->coachId);

        if (! $prefs->push_enabled && ! $prefs->in_app_enabled) {
            return;
        }

        $shouldNotify = match ($event->eventType) {
            'pr_broken'     => $prefs->notify_pr_broken,
            'streak'        => $prefs->notify_streak_milestone,
            'post_created'  => $prefs->notify_post_created,
            'comment_reply' => $prefs->notify_comment_on_my_reply,
            default         => false,
        };

        if (! $shouldNotify) {
            return;
        }

        $this->push->notifyCoachClientActivity(
            coachId: $event->coachId,
            clientName: $event->clientName,
            eventType: $event->eventType,
            payload: $event->payload,
        );
    }
}
