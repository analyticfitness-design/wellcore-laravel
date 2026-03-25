<?php

namespace App\Livewire\Rise;

use App\Livewire\Client\WorkoutPlayer as BaseWorkoutPlayer;
use App\Models\RiseProgram;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;

/**
 * RISE Workout Player
 *
 * Extends the Client WorkoutPlayer to reuse all set-tracking, timer,
 * PR detection, and XP logic — only overrides the data source (RiseProgram
 * instead of AssignedPlan) and the post-workout redirect.
 */
#[Layout('layouts.rise', ['title' => 'Entrenamiento — WellCore RISE'])]
class WorkoutPlayer extends BaseWorkoutPlayer
{
    public function mount(?int $day = null): void
    {
        $clientId = auth('wellcore')->id();

        $this->showTutorial = ! WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->exists();

        $riseProgram = RiseProgram::where('client_id', $clientId)
            ->whereIn('status', ['active', 'activo'])
            ->first();

        if (! $riseProgram) {
            return;
        }

        $programJson    = $riseProgram->personalized_program ?? [];
        $trainingPlan   = $programJson['plan_entrenamiento'] ?? null;

        if (! $trainingPlan || empty($trainingPlan['semanas'])) {
            return;
        }

        $this->hasPlan         = true;
        $this->hasProgressions = true;
        $this->totalWeeks      = (int) ($trainingPlan['duracion_semanas'] ?? count($trainingPlan['semanas']));

        // Pre-normalise all weeks so switchWeek() never re-fetches DB.
        foreach ($trainingPlan['semanas'] as $weekIndex => $weekData) {
            $weekNumber = $weekIndex + 1;
            $dias       = $weekData['dias'] ?? [];
            $this->allWeeksDays[$weekNumber] = array_values(
                array_map(fn ($d) => is_array($d) ? $this->normalizeDay($d) : $d, $dias)
            );
        }

        // Current week based on program start date.
        $startDate         = Carbon::parse($riseProgram->start_date ?? now());
        $weeksActive       = max(1, (int) ceil($startDate->diffInWeeks(now())) + 1);
        $this->currentWeek = min($weeksActive, $this->totalWeeks);
        $this->days        = $this->allWeeksDays[$this->currentWeek] ?? [];

        Cache::put("wp:weekdays:{$clientId}", $this->allWeeksDays, 300);

        if (empty($this->days)) {
            $this->hasPlan = false;
            return;
        }

        if ($day !== null && $day >= 1 && $day <= count($this->days)) {
            $this->currentDayIndex = $day - 1;
        }

        $this->loadDay();

        // Resume an incomplete session from today if it exists.
        $today   = now()->toDateString();
        $dayName = $this->dayName;

        $existingSessionId = Cache::remember(
            "wp:session:{$clientId}:{$today}",
            60,
            fn () => WorkoutSession::where('client_id', $clientId)
                ->where('day_name', $dayName)
                ->where('session_date', $today)
                ->where('completed', false)
                ->latest('id')
                ->value('id')
        );

        if ($existingSessionId) {
            $existingSession = WorkoutSession::find($existingSessionId);
            if ($existingSession) {
                if ($existingSession->created_at->diffInHours(now()) >= 3) {
                    Cache::forget("wp:session:{$clientId}:{$today}");
                } else {
                    $this->sessionId  = $existingSession->id;
                    $this->isActive   = true;
                    $this->startTime  = $existingSession->created_at->toIso8601String();
                    $this->rebuildSetDataFromLogs($existingSession);
                }
            }
        }
    }

    public function completeWorkout(?string $feeling = null, ?string $notes = null): void
    {
        if (! $this->isActive || ! $this->sessionId) {
            return;
        }

        $session = WorkoutSession::find($this->sessionId);
        if (! $session) {
            return;
        }

        $durationSec = (int) Carbon::parse($this->startTime)->diffInSeconds(now());

        $session->update([
            'completed'        => true,
            'duration_minutes' => (int) ($durationSec / 60),
            'feeling'          => $feeling,
            'notes'            => $notes,
        ]);

        try {
            $session->calculateTotals();
        } catch (\Throwable $e) {
            \Log::warning('Rise\WorkoutPlayer: calculateTotals failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $this->updateClientXp($session->awardXp());
        } catch (\Throwable $e) {
            \Log::warning('Rise\WorkoutPlayer: awardXp/updateClientXp failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->isActive = false;

        $this->redirect(route('rise.dashboard'), navigate: true);
    }

    public function abandonWorkout(): void
    {
        if (! $this->isActive || ! $this->sessionId) {
            $this->redirect(route('rise.dashboard'));
            return;
        }

        $session = WorkoutSession::find($this->sessionId);
        if ($session) {
            $session->update(['completed' => false]);
        }

        $this->sessionId = null;
        $this->isActive  = false;
        $this->startTime = null;
        $this->setData   = [];

        $this->redirect(route('rise.dashboard'));
    }

    public function render()
    {
        return view('livewire.client.workout-player', [
            'completedSets' => $this->getCompletedSetsCount(),
            'totalSets'     => $this->getTotalSetsCount(),
            'currentVolume' => $this->getCurrentVolume(),
        ]);
    }

    /**
     * Override explicitly so OPcache stale-bytecode on the parent class
     * never causes "method does not exist" via Livewire's __call interceptor.
     */
    protected function normalizeDay(array $dia): array
    {
        if (! isset($dia['nombre']) && isset($dia['name'])) {
            $dia['nombre'] = $dia['name'];
        }

        if (! isset($dia['ejercicios'])) {
            $exFallback = $dia['exercises'] ?? $dia['sessions'] ?? null;
            if ($exFallback !== null) {
                $dia['ejercicios'] = $exFallback;
                unset($dia['exercises'], $dia['sessions']);
            }
        }

        if (isset($dia['ejercicios']) && is_array($dia['ejercicios'])) {
            foreach ($dia['ejercicios'] as &$ej) {
                if (! is_array($ej)) {
                    continue;
                }
                if (! isset($ej['nombre'])) {
                    $ej['nombre'] = $ej['name'] ?? $ej['exercise'] ?? $ej['ejercicio'] ?? '';
                }
                if (! isset($ej['series']) && isset($ej['sets'])) {
                    $ej['series'] = $ej['sets'];
                }
                if (! isset($ej['repeticiones']) && isset($ej['reps'])) {
                    $ej['repeticiones'] = $ej['reps'];
                }
                if (! isset($ej['descanso'])) {
                    $ej['descanso'] = $ej['rest'] ?? $ej['rest_seconds'] ?? '90s';
                }
            }
            unset($ej);
        }

        return $dia;
    }
}
