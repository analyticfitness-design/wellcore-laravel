<?php

namespace App\Services;

use App\Events\BroadcastSent;
use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BroadcastService
{
    public const CHUNK_SIZE = 100;

    public function previewRecipients(string $audience, array $segment): int
    {
        return $this->buildAudienceQuery($audience, $segment)->count();
    }

    public function dispatch(
        Admin $sender,
        string $senderType,
        string $audience,
        array $segment,
        ?string $subject,
        string $body,
        bool $pushEnabled,
    ): BroadcastMessage {
        return DB::transaction(function () use ($sender, $senderType, $audience, $segment, $subject, $body, $pushEnabled) {
            $count = $this->buildAudienceQuery($audience, $segment)->count();

            $broadcast = BroadcastMessage::create([
                'sender_type' => $senderType,
                'sender_id' => $sender->id,
                'audience_type' => $audience,
                'segment_filter' => $segment ?: null,
                'subject' => $subject,
                'body' => $body,
                'push_enabled' => $pushEnabled,
                'recipients_count' => $count,
                'delivered_count' => 0,
                'sent_at' => Carbon::now(),
            ]);

            $this->buildAudienceQuery($audience, $segment)
                ->select('id')
                ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($broadcast, $pushEnabled) {
                    $this->deliverChunk($broadcast, $chunk, $pushEnabled);
                });

            event(new BroadcastSent($broadcast->id, $broadcast->audience_type, $count));

            return $broadcast->fresh();
        });
    }

    private function buildAudienceQuery(string $audience, array $segment): Builder
    {
        if ($audience === 'coaches') {
            $q = Admin::query()->where('role', 'coach');

            if (! empty($segment['coach_ids'])) {
                $q->whereIn('id', $segment['coach_ids']);
            }

            return $q;
        }

        $q = Client::query();

        if (! empty($segment['plan'])) {
            $q->whereIn('plan', $segment['plan']);
        }

        if (! empty($segment['status'])) {
            $q->whereIn('status', $segment['status']);
        }

        if (! empty($segment['coach_id'])) {
            $q->where('coach_id', $segment['coach_id']);
        }

        if (! empty($segment['inactive_days'])) {
            $q->where('last_login_at', '<', now()->subDays($segment['inactive_days']));
        }

        return $q;
    }

    private function deliverChunk(BroadcastMessage $broadcast, $chunk, bool $pushEnabled): void
    {
        // Increment delivered count optimistically.
        // Actual push delivery happens in a queued job dispatched by listener
        // NotifyMentionedUsers/PushNotificationService — we just track count here.
        $broadcast->increment('delivered_count', count($chunk));
    }
}
