<?php
declare(strict_types=1);
namespace Database\Factories;

use App\Models\CoachContentDrop;
use App\Models\CoachContentPieceState;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CoachContentPieceStateFactory extends Factory
{
    protected $model = CoachContentPieceState::class;

    public function definition(): array
    {
        $drop = CoachContentDrop::factory()->create();
        return [
            'drop_id'    => $drop->id,
            'coach_id'   => $drop->coach_id,
            'piece_type' => 'reel',
            'piece_key'  => 'reel_1',
            'state'      => 'pending',
        ];
    }

    public function published(string $url = 'https://instagram.com/p/abc'): static
    {
        return $this->state([
            'state'            => 'published',
            'published_url'    => $url,
            'state_changed_at' => now(),
        ]);
    }
}
