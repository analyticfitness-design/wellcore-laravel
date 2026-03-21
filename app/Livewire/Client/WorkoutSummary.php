<?php

namespace App\Livewire\Client;

use App\Models\WorkoutSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Resumen de Sesión — WellCore'])]
class WorkoutSummary extends Component
{
    public WorkoutSession $session;

    public array $stats = [];

    public array $prs = [];

    public int $xpEarned = 0;

    public ?int $feeling = null;

    public string $notes = '';

    public array $sessionHistory = [];

    public function mount(int $session): void
    {
        $clientId = auth('wellcore')->id();

        $this->session = WorkoutSession::with('logs')
            ->where('client_id', $clientId)
            ->findOrFail($session);

        // Build stats
        $completedLogs = $this->session->logs->where('completed', true);
        $exerciseCount = $completedLogs->pluck('exercise_name')->unique()->count();
        $targetSets = $this->session->logs->count();

        $this->stats = [
            'duration' => $this->session->formattedDuration(),
            'duration_sec' => $this->session->duration_sec ?? 0,
            'volume' => $this->session->total_volume_kg ?? (int) $completedLogs->sum(fn ($l) => ($l->weight_kg ?? 0) * ($l->reps ?? 0)),
            'reps' => $this->session->total_reps ?? $completedLogs->sum('reps'),
            'sets_completed' => $completedLogs->count(),
            'sets_total' => $targetSets,
            'exercises_count' => $exerciseCount,
        ];

        // XP earned this session
        $this->xpEarned = $this->session->xp_earned ?? 0;

        // PR achievements from this session's logs
        $prLogs = $completedLogs->where('is_pr', true);
        $this->prs = $prLogs->map(fn ($log) => [
            'exercise' => $log->exercise_name,
            'weight' => (float) $log->weight_kg,
            'reps' => $log->reps,
        ])->values()->toArray();

        // Pre-fill feedback if already saved
        $this->feeling = $this->session->feeling;
        $this->notes = $this->session->notes ?? '';

        // Session history: last 10 completed sessions for this client
        $this->sessionHistory = WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->where('id', '!=', $this->session->id)
            ->orderByDesc('session_date')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'date' => $s->session_date?->format('d M') ?? '-',
                'day_name' => $s->day_name ?? '-',
                'duration' => $s->formattedDuration(),
                'volume' => $s->total_volume_kg ?? 0,
            ])
            ->toArray();
    }

    public function saveFeedback(): void
    {
        $this->validate([
            'feeling' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->session->update([
            'feeling' => $this->feeling,
            'notes' => $this->notes ?: null,
        ]);

        $this->dispatch('feedback-saved');
    }

    public function shareToCommunity(): void
    {
        $this->dispatch('share-workout', sessionId: $this->session->id);
    }

    public function render()
    {
        return view('livewire.client.workout-summary');
    }
}
