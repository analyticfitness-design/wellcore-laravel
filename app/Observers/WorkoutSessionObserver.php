<?php

namespace App\Observers;

use App\Models\CommunityPost;
use App\Models\WorkoutSession;
use App\Services\MedalService;

/**
 * Fires medal evaluation and optional community auto-share when a workout
 * session is marked completed.
 *
 * Only reacts when `completed` transitions to true — avoids re-evaluating
 * on every minor update (duration, notes, feeling).
 *
 * We evaluate constancia + volumen together; both track workouts count/streak.
 */
class WorkoutSessionObserver
{
    public function __construct(private readonly MedalService $medals) {}

    public function created(WorkoutSession $session): void
    {
        if (! $session->completed || ! $session->client) {
            return;
        }

        $this->evaluate($session);
    }

    public function updated(WorkoutSession $session): void
    {
        if (! $session->wasChanged('completed') || ! $session->completed) {
            return;
        }

        if (! $session->client) {
            return;
        }

        $this->evaluate($session);
        $this->maybeAutoShare($session);
    }

    private function evaluate(WorkoutSession $session): void
    {
        $this->medals->checkCategory($session->client, 'constancia');
        $this->medals->checkCategory($session->client, 'volumen');
        $this->medals->checkCategory($session->client, 'especial');
    }

    private function maybeAutoShare(WorkoutSession $session): void
    {
        $client = $session->client;

        if (! $client?->autoshare_workout) {
            return;
        }

        $coachId = optional($client->activeCoach())->id;

        CommunityPost::create([
            'client_id' => $client->id,
            'coach_admin_id' => $coachId,
            'content' => sprintf(
                '¡Completé mi entrenamiento: %s! 💪 %d series | %s kg de volumen.',
                $session->day_name ?? 'Sesión',
                $session->total_sets ?? 0,
                number_format((float) ($session->total_volume_kg ?? 0), 1),
            ),
            'post_type' => 'achievement',
            'visible' => true,
        ]);
    }
}
