<?php

namespace App\Livewire\Client;

use App\Models\CommunityPost;
use App\Models\WorkoutSession;
use Illuminate\Support\Facades\Cache;
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

    public function mount(int $sessionId): void
    {
        $clientId = auth('wellcore')->id();

        $this->session = WorkoutSession::with('logs')
            ->where('client_id', $clientId)
            ->findOrFail($sessionId);

        // Build stats
        $completedLogs = $this->session->logs->where('completed', true);
        $exerciseCount = $completedLogs->pluck('exercise_name')->unique()->count();
        $targetSets = $this->session->logs->count();

        $rawVolume = $this->session->total_volume ?? (int) $completedLogs->sum(fn ($l) => ($l->weight_kg ?? 0) * ($l->reps ?? 0));

        // Format volume rationally: show as tons if >= 1000, otherwise as kg
        $volumeDisplay = $rawVolume >= 1000
            ? number_format($rawVolume / 1000, 1)
            : number_format($rawVolume);
        $volumeUnit = $rawVolume >= 1000 ? 'toneladas' : 'kg';

        // Average weight per set (useful metric)
        $avgWeightPerSet = $completedLogs->count() > 0
            ? round($completedLogs->avg('weight_kg'), 1)
            : 0;

        $this->stats = [
            'duration' => $this->session->formattedDuration(),
            'duration_sec' => ($this->session->duration_minutes ?? 0) * 60,
            'volume_raw' => $rawVolume,
            'volume_display' => $volumeDisplay,
            'volume_unit' => $volumeUnit,
            'avg_weight' => $avgWeightPerSet,
            'reps' => (int) $completedLogs->sum('reps'),
            'sets_completed' => $completedLogs->count(),
            'sets_total' => $targetSets,
            'exercises_count' => $exerciseCount,
        ];

        // XP earned this session — cached so the number never changes across page reloads
        $cacheKey = "workout_summary_xp:{$this->session->id}";
        $this->xpEarned = Cache::remember($cacheKey, 86400 * 30, function () {
            return $this->session->awardXp();
        });

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
            ->map(function ($s) {
                $vol = $s->total_volume ?? 0;
                return [
                    'id' => $s->id,
                    'date' => $s->session_date?->format('d M') ?? '-',
                    'day_name' => $s->day_name ?? '-',
                    'duration' => $s->formattedDuration(),
                    'volume' => $vol >= 1000 ? number_format($vol / 1000, 1) . ' ton' : number_format($vol) . ' kg',
                ];
            })
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
        $clientId = auth('wellcore')->id();

        // Guard against duplicate shares for the same session
        $alreadyShared = CommunityPost::where('client_id', $clientId)
            ->where('content', 'like', '%[session:' . $this->session->id . ']%')
            ->exists();

        if ($alreadyShared) {
            $this->dispatch('toast', message: 'Este entreno ya fue compartido', type: 'info');
            return;
        }

        $setsCompleted = $this->stats['sets_completed'] ?? 0;
        $volume        = $this->stats['volume'] ?? 0;
        $dayName       = $this->session->day_name ?? 'Entrenamiento';

        CommunityPost::create([
            'client_id' => $clientId,
            'content'   => "¡Completé mi entrenamiento: {$dayName}! 💪 {$setsCompleted} series | {$volume} kg de volumen. [session:{$this->session->id}]",
            'post_type' => 'achievement',
            'visible'   => true,
        ]);

        $this->dispatch('toast', message: '¡Compartido en la comunidad!', type: 'success');
    }

    public function render()
    {
        return view('livewire.client.workout-summary');
    }
}
