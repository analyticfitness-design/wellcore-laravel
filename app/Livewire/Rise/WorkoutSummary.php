<?php

namespace App\Livewire\Rise;

use App\Livewire\Client\WorkoutSummary as BaseWorkoutSummary;
use Livewire\Attributes\Layout;

/**
 * RISE Workout Summary
 *
 * Extends Client\WorkoutSummary — reuses all stats, XP, PR, and feedback
 * logic. Only overrides the layout and the blade view (which uses rise routes).
 */
#[Layout('layouts.rise', ['title' => 'Resumen de Sesión — WellCore RISE'])]
class WorkoutSummary extends BaseWorkoutSummary
{
    public function render()
    {
        return view('livewire.rise.workout-summary');
    }
}
