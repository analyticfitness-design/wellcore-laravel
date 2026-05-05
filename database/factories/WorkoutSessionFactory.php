<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSession>
 */
class WorkoutSessionFactory extends Factory
{
    protected $model = WorkoutSession::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'plan_id' => null,
            'day_name' => 'Pecho + Hombros',
            'session_date' => Carbon::today(),
            'duration_sec' => 1800,
            'duration_minutes' => 30,
            'feeling' => null,
            'notes' => null,
            'completed' => false,
            'total_volume_kg' => 0,
            'total_reps' => 0,
            'total_sets' => 0,
            'xp_earned' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_date' => Carbon::today(),
        ]);
    }
}
