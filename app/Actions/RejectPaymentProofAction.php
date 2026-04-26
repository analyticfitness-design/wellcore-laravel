<?php

namespace App\Actions;

use App\Enums\PaymentProofStatus;
use App\Enums\UserType;
use App\Mail\PaymentProofRejected;
use App\Models\Admin;
use App\Models\PaymentProof;
use App\Models\WellcoreNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\Mail;

final class RejectPaymentProofAction
{
    public function handle(PaymentProof $proof, Admin $reviewer, string $reason): void
    {
        if ($proof->status !== PaymentProofStatus::Pendiente) {
            throw new \BadMethodCallException('El comprobante ya fue procesado y no puede rechazarse.');
        }

        $proof->update([
            'status' => PaymentProofStatus::Rechazado,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $reason,
        ]);

        WellcoreNotification::create([
            'user_type' => UserType::Admin,
            'user_id' => $proof->coach_id,
            'type' => 'payment_proof_rejected',
            'title' => 'Comprobante rechazado',
            'body' => "El comprobante para {$proof->client_email} fue rechazado. Razón: {$reason}",
        ]);

        $coachEmail = $proof->coach?->email ?? '';

        if ($coachEmail !== '') {
            Mail::to($coachEmail)->queue(new PaymentProofRejected($proof, $reason));
        }

        AuditService::logAction(
            'payment_proof_rejected',
            "Admin {$reviewer->id} rechazó proof {$proof->id}. Razón: {$reason}"
        );
    }
}
