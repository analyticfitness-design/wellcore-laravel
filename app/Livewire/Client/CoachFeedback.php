<?php

namespace App\Livewire\Client;

use App\Models\CoachRating;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Coach Feedback — WellCore'])]
class CoachFeedback extends Component
{
    public int $rating = 0;
    public string $comment = '';
    public bool $showSuccess = false;

    /**
     * Resolved once in mount() so render() and submitRating() do not each
     * issue a separate DB::table('coach_messages') query.
     */
    public ?int $resolvedCoachId = null;

    protected function rules(): array
    {
        return [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'rating.required' => 'Selecciona una calificación de 1 a 5 estrellas.',
            'rating.min'      => 'La calificación mínima es 1 estrella.',
            'rating.max'      => 'La calificación máxima es 5 estrellas.',
        ];
    }

    public function mount(): void
    {
        // Resolve coach once at mount time. Stored in a public int so Livewire
        // survives hydration without issuing the query again on every render.
        $this->resolvedCoachId = $this->resolveCoachId(auth('wellcore')->id());
    }

    public function submitRating(): void
    {
        $this->validate();

        $client  = auth('wellcore')->user();
        $coachId = $this->resolvedCoachId;

        if (! $coachId) {
            return;
        }

        $recentRating = CoachRating::where('client_id', $client->id)
            ->where('coach_id', $coachId)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        if ($recentRating) {
            $nextAllowed = $recentRating->created_at->addDays(7)->format('d/m/Y');
            $this->addError('rating', "Ya calificaste a tu coach esta semana. Podrás calificar nuevamente el {$nextAllowed}.");
            return;
        }

        CoachRating::create([
            'client_id' => $client->id,
            'coach_id'  => $coachId,
            'rating'    => $this->rating,
            'comment'   => $this->comment ?: null,
        ]);

        $this->rating      = 0;
        $this->comment     = '';
        $this->showSuccess = true;
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    /**
     * Resolve the coach assigned to this client.
     * Called only from mount() — not on every render().
     */
    private function resolveCoachId(int $clientId): ?int
    {
        $row = DB::table('coach_messages')
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->value('coach_id');

        return $row ? (int) $row : null;
    }

    public function render()
    {
        $client  = auth('wellcore')->user();
        $coachId = $this->resolvedCoachId;

        $coach   = null;
        $ratings = collect();

        if ($coachId) {
            // Fetch coach profile — acceptable single query per page render.
            $coach = DB::table('coach_profiles')
                ->join('admins', 'admins.id', '=', 'coach_profiles.admin_id')
                ->where('coach_profiles.admin_id', $coachId)
                ->select('admins.name', 'coach_profiles.bio', 'coach_profiles.photo_url', 'coach_profiles.city')
                ->first();

            $ratings = CoachRating::where('client_id', $client->id)
                ->where('coach_id', $coachId)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('livewire.client.coach-feedback', [
            'coachId' => $coachId,
            'coach'   => $coach,
            'ratings' => $ratings,
        ]);
    }
}
