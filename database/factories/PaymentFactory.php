<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'email'            => $this->faker->unique()->safeEmail(),
            'plan'             => PlanType::Metodo->value,
            'amount'           => 339150.00,
            'currency'         => 'COP',
            'status'           => PaymentStatus::Pending->value,
            'wompi_reference'  => 'WCI-' . bin2hex(random_bytes(16)),
            'payment_method'   => 'CARD',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Pending->value,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Approved->value,
        ]);
    }
}
