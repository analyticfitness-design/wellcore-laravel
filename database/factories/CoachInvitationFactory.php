<?php

namespace Database\Factories;

use App\Enums\CoachInvitationStatus;
use App\Enums\PlanType;
use App\Models\Admin;
use App\Models\CoachInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CoachInvitation>
 */
class CoachInvitationFactory extends Factory
{
    protected $model = CoachInvitation::class;

    public function definition(): array
    {
        return [
            'uuid'       => Str::uuid()->toString(),
            'coach_id'   => Admin::factory()->coach(),
            'code'       => bin2hex(random_bytes(16)),
            'email'      => $this->faker->unique()->safeEmail(),
            'name'       => $this->faker->name(),
            'plan'       => PlanType::Metodo->value,
            'amount'     => 339150.00,
            'currency'   => 'COP',
            'status'     => CoachInvitationStatus::Sent->value,
            'subject'    => $this->faker->sentence(5),
            'cta_label'  => 'Ver mi plan',
            'expires_at' => now()->addDays(7),
            'sent_at'    => now(),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => CoachInvitationStatus::Sent->value,
            'sent_at' => now(),
        ]);
    }

    public function opened(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'    => CoachInvitationStatus::Opened->value,
            'opened_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => CoachInvitationStatus::Paid->value,
            'paid_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => CoachInvitationStatus::Expired->value,
            'expires_at' => now()->subDay(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => CoachInvitationStatus::Cancelled->value,
            'cancelled_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CoachInvitationStatus::Failed->value,
        ]);
    }

    public function sentToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now(),
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
