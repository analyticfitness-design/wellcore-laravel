<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\PersonalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonalRecord>
 */
class PersonalRecordFactory extends Factory
{
    protected $model = PersonalRecord::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'exercise' => 'Sentadilla',
            'category' => 'fuerza',
            'weight' => 100.00,
            'reps' => 5,
            'duration_sec' => null,
            'distance_km' => null,
            'is_current' => 1,
            'achieved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
