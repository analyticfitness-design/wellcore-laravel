<?php

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'client_code'  => strtoupper(Str::random(8)),
            'name'         => $this->faker->name(),
            'email'        => $this->faker->unique()->safeEmail(),
            'password_hash' => password_hash('password', PASSWORD_BCRYPT),
            'plan'         => PlanType::Metodo->value,
            'status'       => ClientStatus::Activo->value,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClientStatus::Activo->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ClientStatus::Inactivo->value,
        ]);
    }
}
