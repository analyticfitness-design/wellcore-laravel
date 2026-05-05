<?php

namespace App\Services;

use App\Events\PostMadeOfficial;
use App\Events\PostPinned;
use App\Models\Admin;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Models\PinnedPost;
use App\Models\PostReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ModerationService
{
    public function pinPost(CommunityPost $post, Admin $actor, string $actorType, ?int $hours, ?string $note = null): PinnedPost
    {
        return DB::transaction(function () use ($post, $actor, $actorType, $hours, $note) {
            $pin = PinnedPost::create([
                'post_id' => $post->id,
                'pinned_by_type' => $actorType,
                'pinned_by_id' => $actor->id,
                'pinned_at' => Carbon::now(),
                'pinned_until' => $hours ? Carbon::now()->addHours($hours) : null,
                'note' => $note,
            ]);

            ModerationAction::create([
                'actor_type' => $actorType,
                'actor_id' => $actor->id,
                'action_type' => 'pin',
                'target_type' => 'post',
                'target_id' => $post->id,
                'reason' => $note,
                'metadata' => ['hours' => $hours],
                'created_at' => Carbon::now(),
            ]);

            event(new PostPinned($post->id, $post->coach_admin_id, $actor->id, $actorType, $hours));

            return $pin;
        });
    }

    public function unpinPost(CommunityPost $post, Admin $actor, string $actorType): void
    {
        DB::transaction(function () use ($post, $actor, $actorType) {
            PinnedPost::where('post_id', $post->id)
                ->where(fn ($q) => $q->whereNull('pinned_until')->orWhere('pinned_until', '>', now()))
                ->update(['pinned_until' => Carbon::now()]);

            ModerationAction::create([
                'actor_type' => $actorType,
                'actor_id' => $actor->id,
                'action_type' => 'unpin',
                'target_type' => 'post',
                'target_id' => $post->id,
                'created_at' => Carbon::now(),
            ]);
        });
    }

    public function deletePost(CommunityPost $post, Admin $actor, string $actorType, ?string $reason = null): void
    {
        DB::transaction(function () use ($post, $actor, $actorType, $reason) {
            $post->update(['visible' => false]);

            ModerationAction::create([
                'actor_type' => $actorType,
                'actor_id' => $actor->id,
                'action_type' => 'delete',
                'target_type' => 'post',
                'target_id' => $post->id,
                'reason' => $reason,
                'created_at' => Carbon::now(),
            ]);
        });
    }

    public function makeOfficial(CommunityPost $post, Admin $actor, string $actorType): void
    {
        DB::transaction(function () use ($post, $actor, $actorType) {
            $post->update([
                'is_official' => true,
                'author_type' => $actorType,
                'author_admin_id' => $actor->id,
            ]);

            ModerationAction::create([
                'actor_type' => $actorType,
                'actor_id' => $actor->id,
                'action_type' => 'make_official',
                'target_type' => 'post',
                'target_id' => $post->id,
                'created_at' => Carbon::now(),
            ]);

            event(new PostMadeOfficial($post->id, $post->coach_admin_id, $actor->id, $actorType));
        });
    }

    public function dismissReport(PostReport $report, Admin $admin): void
    {
        $report->update([
            'status' => 'dismissed',
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => Carbon::now(),
        ]);

        ModerationAction::create([
            'actor_type' => 'admin',
            'actor_id' => $admin->id,
            'action_type' => 'dismiss_report',
            'target_type' => 'post',
            'target_id' => $report->post_id,
            'metadata' => ['report_id' => $report->id],
            'created_at' => Carbon::now(),
        ]);
    }
}
