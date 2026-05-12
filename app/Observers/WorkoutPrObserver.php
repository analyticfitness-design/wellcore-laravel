<?php

namespace App\Observers;

use App\Events\NewMessageSent;
use App\Models\Admin;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Models\CommunityPost;
use App\Models\WorkoutPr;
use Illuminate\Support\Facades\DB;

class WorkoutPrObserver
{
    public function created(WorkoutPr $pr): void
    {
        $client = $pr->client ?? Client::find($pr->client_id);

        if (! $client) {
            return;
        }

        $this->notifyCoach($pr, $client);
        $this->maybeAutoShare($pr, $client);
    }

    private function notifyCoach(WorkoutPr $pr, Client $client): void
    {
        $coach = $this->resolveCoach($client);

        if (! $coach) {
            return;
        }

        $text = $this->buildMessageText($pr);

        $msg = CoachMessage::create([
            'coach_id' => $coach->id,
            'client_id' => $client->id,
            'message' => $text,
            'direction' => 'client_to_coach',
            'auto' => true,
        ]);

        try {
            event(new NewMessageSent(
                coachId: $coach->id,
                clientId: $client->id,
                senderId: $client->id,
                senderName: $client->name ?? 'Cliente',
                messagePreview: mb_substr($text, 0, 100),
                sentAt: $msg->created_at?->toIso8601String() ?? now()->toIso8601String(),
            ));
        } catch (\Throwable $e) {
            \Log::warning('NewMessageSent broadcast failed (PR observer)', ['error' => $e->getMessage()]);
        }
    }

    private function maybeAutoShare(WorkoutPr $pr, Client $client): void
    {
        if (! $client->autoshare_pr) {
            return;
        }

        $coachId = optional($client->activeCoach())->id;

        CommunityPost::create([
            'client_id' => $client->id,
            'coach_admin_id' => $coachId,
            'content' => sprintf(
                '🏆 Nuevo PR en %s: %s kg × %d reps.',
                $pr->exercise_name,
                number_format((float) $pr->weight_kg, 1),
                $pr->reps
            ),
            'post_type' => 'pr',
            'visible' => true,
        ]);
    }

    /**
     * Resolve the coach assigned to a client.
     *
     * Priority:
     *   1. Most recent active assigned plan (assigned_by is the explicit coach FK).
     *   2. Most recent coach_messages thread (coach who has been chatting with client).
     */
    private function resolveCoach(Client $client): ?Admin
    {
        $coachId = DB::table('assigned_plans')
            ->where('client_id', $client->id)
            ->where('active', true)
            ->orderByDesc('created_at')
            ->value('assigned_by');

        if (! $coachId) {
            $coachId = DB::table('coach_messages')
                ->where('client_id', $client->id)
                ->orderByDesc('id')
                ->value('coach_id');
        }

        if (! $coachId) {
            return null;
        }

        return Admin::find($coachId);
    }

    private function buildMessageText(WorkoutPr $pr): string
    {
        $parts = ['🏆 Nuevo PR — '.$pr->exercise_name];

        if ($pr->weight_kg) {
            $parts[] = number_format((float) $pr->weight_kg, 1).' kg';
        }

        if ($pr->reps) {
            $parts[] = '× '.$pr->reps.' reps';
        }

        if ($pr->volume) {
            $parts[] = '(vol. '.number_format((float) $pr->volume, 1).')';
        }

        return implode(' ', $parts);
    }
}
