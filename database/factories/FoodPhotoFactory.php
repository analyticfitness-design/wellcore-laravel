<?php

namespace Database\Factories;

use App\Models\FoodPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FoodPhotoFactory extends Factory
{
    protected $model = FoodPhoto::class;

    public function definition(): array
    {
        return [
            'client_id'      => 1,
            'meal_name'      => $this->faker->randomElement(['Desayuno', 'Almuerzo', 'Cena', 'Snack']),
            'meal_index'     => 0,
            'photo_date'     => now()->toDateString(),
            'filename'       => 'food-photos/1/'.Str::uuid().'.jpg',
            'file_size'      => 250000,
            'coach_seen'     => false,
            'coach_seen_at'  => null,
            'coach_reaction' => null,
            'coach_note'     => null,
            'xp_awarded'     => false,
            'ai_analysis'    => null,
        ];
    }

    public function reviewed(string $reaction = 'bien'): static
    {
        return $this->state([
            'coach_seen'     => true,
            'coach_seen_at'  => now(),
            'coach_reaction' => $reaction,
        ]);
    }
}
