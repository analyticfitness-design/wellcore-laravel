<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'username'     => $this->faker->unique()->userName(),
            'password_hash' => password_hash('password', PASSWORD_BCRYPT),
            'name'         => $this->faker->name(),
            'role'         => UserRole::Coach->value,
            'email'        => $this->faker->unique()->safeEmail(),
            'active'       => true,
        ];
    }

    public function coach(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Coach->value,
        ]);
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Superadmin->value,
        ]);
    }
}
