<?php

namespace App\Actions;

use App\Enums\ClientStatus;
use App\Enums\CoachInvitationStatus;
use App\Enums\PaymentProofStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserType;
use App\Mail\PaymentProofApproved;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientCoach;
use App\Models\CoachInvitation;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\WellcoreNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class ApprovePaymentProofAction
{
    public function handle(PaymentProof $proof, Admin $reviewer): void
    {
        if ($proof->status !== PaymentProofStatus::Pendiente) {
            throw new \BadMethodCallException('El comprobante ya fue procesado y no puede aprobarse nuevamente.');
        }

        $client = null;
        $invitation = null;
        $payment = null;
        $isNewClient = false;

        DB::transaction(function () use ($proof, $reviewer, &$client, &$invitation, &$payment, &$isNewClient) {
            $manualRef = 'MAN-'.Str::uuid();

            $invitation = CoachInvitation::create([
                'uuid' => Str::uuid()->toString(),
                'coach_id' => $proof->coach_id,
                'code' => bin2hex(random_bytes(16)),
                'email' => $proof->client_email,
                'name' => $proof->client_name,
                'plan' => $proof->plan->value,
                'amount' => $proof->amount ?? 0,
                'currency' => $proof->currency,
                'subject' => "Tu plan {$proof->plan->value} en WellCore Fitness está activo",
                'wompi_reference' => $manualRef,
                'status' => CoachInvitationStatus::Paid->value,
                'paid_at' => now(),
                'sent_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            $payment = Payment::create([
                'wompi_reference' => $manualRef,
                'plan' => $proof->plan->value,
                'amount' => $proof->amount ?? 0,
                'currency' => $proof->currency,
                'status' => PaymentStatus::Approved->value,
                'email' => $proof->client_email,
                'buyer_name' => $proof->client_name,
            ]);

            $client = Client::where('email', $proof->client_email)->first();

            if ($client) {
                $client->update([
                    'status' => ClientStatus::Activo->value,
                    'plan' => $proof->plan->value,
                ]);
            } else {
                do {
                    $clientCode = 'WC-'.strtoupper(Str::random(6));
                } while (Client::where('client_code', $clientCode)->exists());

                $client = Client::create([
                    'client_code' => $clientCode,
                    'email' => $proof->client_email,
                    'name' => $proof->client_name,
                    'password_hash' => bcrypt(Str::password(12)),
                    'status' => ClientStatus::Activo->value,
                    'plan' => $proof->plan->value,
                ]);

                $isNewClient = true;
            }

            $payment->update(['client_id' => $client->id]);
            $invitation->update([
                'client_id' => $client->id,
                'payment_id' => $payment->id,
            ]);

            ClientCoach::where('client_id', $client->id)->update(['active' => false]);

            ClientCoach::create([
                'client_id' => $client->id,
                'admin_id' => $proof->coach_id,
                'source' => 'payment_proof',
                'coach_invitation_id' => $invitation->id,
                'assigned_at' => now(),
                'active' => true,
            ]);

            $proof->update([
                'status' => PaymentProofStatus::Aprobado,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'coach_invitation_id' => $invitation->id,
                'payment_id' => $payment->id,
            ]);
        });

        $resetUrl = null;
        if ($isNewClient) {
            $resetToken = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $client->email],
                ['token' => Hash::make($resetToken), 'created_at' => now()]
            );
            $resetUrl = url('/reset-password/'.$resetToken.'?email='.urlencode($client->email));
        }

        Mail::to($proof->client_email)->queue(new PaymentProofApproved($proof, $client, $invitation, $resetUrl));

        WellcoreNotification::create([
            'user_type' => UserType::Admin,
            'user_id' => $proof->coach_id,
            'type' => 'payment_proof_approved',
            'title' => 'Comprobante aprobado',
            'body' => "El comprobante para {$proof->client_email} fue aprobado.",
        ]);

        AuditService::logAction(
            'payment_proof_approved',
            "Admin {$reviewer->id} aprobó proof {$proof->id} → client {$client->id}"
        );
    }
}
