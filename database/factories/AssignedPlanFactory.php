<?php

namespace Database\Factories;

use App\Models\AssignedPlan;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignedPlanFactory extends Factory
{
    protected $model = AssignedPlan::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'plan_type' => 'entrenamiento',
            'content' => ['weeks' => []],
            'version' => 1,
            'valid_from' => now()->toDateString(),
            'expires_at' => null, // booted() hook calculará si es null
            'active' => true,
            'assigned_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay()->toDateString(),
        ]);
    }

    public function expiresAt(string $date): static
    {
        return $this->state(fn () => ['expires_at' => $date]);
    }

    public function trial(): static
    {
        return $this->state(fn () => ['plan_type' => 'trial']);
    }

    public function nutrition(): static
    {
        return $this->state(fn () => ['plan_type' => 'nutricion']);
    }
}
