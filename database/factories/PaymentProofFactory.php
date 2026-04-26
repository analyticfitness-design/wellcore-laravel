<?php

namespace Database\Factories;

use App\Enums\PaymentProofMethod;
use App\Enums\PaymentProofStatus;
use App\Enums\PlanType;
use App\Models\Admin;
use App\Models\PaymentProof;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentProof>
 */
class PaymentProofFactory extends Factory
{
    protected $model = PaymentProof::class;

    public function definition(): array
    {
        return [
            'coach_id'       => Admin::factory()->coach(),
            'client_email'   => $this->faker->unique()->safeEmail(),
            'client_name'    => $this->faker->name(),
            'plan'           => PlanType::Metodo->value,
            'amount'         => 339150.00,
            'currency'       => 'COP',
            'payment_method' => PaymentProofMethod::Transferencia->value,
            'file_path'      => 'proofs/test-proof-' . $this->faker->uuid() . '.pdf',
            'file_disk'      => 'payment_proofs',
            'file_mime'      => 'application/pdf',
            'file_size'      => 512000,
            'file_hash'      => hash('sha256', $this->faker->uuid()),
            'coach_note'     => null,
            'status'         => PaymentProofStatus::Pendiente->value,
            'submitted_at'   => now(),
            'expires_at'     => now()->addDays(7),
        ];
    }

    // -------------------------------------------------------------------------
    // Status states
    // -------------------------------------------------------------------------

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => PaymentProofStatus::Pendiente->value,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ]);
    }

    public function aprobado(int $reviewerId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => PaymentProofStatus::Aprobado->value,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_note' => null,
        ]);
    }

    public function rechazado(int $reviewerId = null, string $reason = 'Comprobante no válido'): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => PaymentProofStatus::Rechazado->value,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_note' => $reason,
        ]);
    }

    public function expirado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => PaymentProofStatus::Expirado->value,
            'expires_at'  => now()->subDay(),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Expiry helpers
    // -------------------------------------------------------------------------

    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => PaymentProofStatus::Pendiente->value,
            'expires_at' => now()->subHour(),
        ]);
    }

    public function notExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays(3),
        ]);
    }

    // -------------------------------------------------------------------------
    // File type helpers
    // -------------------------------------------------------------------------

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_mime' => 'application/pdf',
            'file_path' => 'proofs/test-' . $this->faker->uuid() . '.pdf',
        ]);
    }

    public function webp(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_mime' => 'image/webp',
            'file_path' => 'proofs/test-' . $this->faker->uuid() . '.webp',
        ]);
    }

    // -------------------------------------------------------------------------
    // Ownership helpers
    // -------------------------------------------------------------------------

    public function forCoach(Admin $coach): static
    {
        return $this->state(fn (array $attributes) => [
            'coach_id' => $coach->id,
        ]);
    }
}
