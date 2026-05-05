<?php

namespace App\Listeners;

use App\Events\BroadcastSent;
use App\Events\CoachCommunityActivity;
use App\Events\PostMadeOfficial;
use App\Events\PostPinned;
use Illuminate\Support\Facades\Cache;

/**
 * Synchronous cache invalidation for Community Cross-Role namespaces.
 *
 * NOT ShouldQueue — invalidation must run inline so that the next read
 * after a pin/official/broadcast/coach-activity event sees fresh data.
 */
class InvalidateCommunityCaches
{
    public function handlePostPinned(PostPinned $event): void
    {
        if ($event->coachAdminId) {
            Cache::forget("wc:coach-pulse:v1:{$event->coachAdminId}");
        }
        Cache::forget('wc:admin-community-analytics:v1:week');
        Cache::forget('wc:admin-community-analytics:v1:day');
    }

    public function handlePostMadeOfficial(PostMadeOfficial $event): void
    {
        if ($event->coachAdminId) {
            Cache::forget("wc:coach-pulse:v1:{$event->coachAdminId}");
        }
        Cache::forget('wc:admin-community-analytics:v1:week');
    }

    public function handleBroadcastSent(BroadcastSent $event): void
    {
        Cache::forget('wc:admin-community-analytics:v1:week');
    }

    public function handleCoachActivity(CoachCommunityActivity $event): void
    {
        Cache::forget("wc:coach-pulse:v1:{$event->coachId}");
    }

    /**
     * Subscribe to multiple events.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array<class-string, string>
     */
    public function subscribe($events): array
    {
        return [
            PostPinned::class             => 'handlePostPinned',
            PostMadeOfficial::class       => 'handlePostMadeOfficial',
            BroadcastSent::class          => 'handleBroadcastSent',
            CoachCommunityActivity::class => 'handleCoachActivity',
        ];
    }
}
