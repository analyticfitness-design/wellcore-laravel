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

    public function submitRating(): void
    {
        $this->validate();

        $client  = auth('wellcore')->user();
        $coachId = $this->getCoachId($client->id);

        if (! $coachId) {
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
     * We look in coach_messages (most recent exchange) since clients
     * table has no coach_id column.
     */
    private function getCoachId(int $clientId): ?int
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
        $coachId = $this->getCoachId($client->id);

        $coach   = null;
        $ratings = collect();

        if ($coachId) {
            // Fetch coach name from coach_profiles + admins join
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
