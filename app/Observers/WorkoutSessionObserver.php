<?php

namespace App\Observers;

use App\Models\WorkoutSession;
use App\Services\MedalService;

/**
 * Fires medal evaluation when a workout session is marked completed.
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
    }

    private function evaluate(WorkoutSession $session): void
    {
        $this->medals->checkCategory($session->client, 'constancia');
        $this->medals->checkCategory($session->client, 'volumen');
        $this->medals->checkCategory($session->client, 'especial');
    }
}
