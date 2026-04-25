<?php

namespace App\Services;

use App\Enums\ClientStatus;
use App\Enums\CoachInvitationStatus;
use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Exceptions\CoachInvitationBlockedException;
use App\Exceptions\CoachInvitationCancelException;
use App\Exceptions\CoachInvitationRateLimitException;
use App\Exceptions\CoachInvitationResendException;
use App\Mail\CoachClientInvitation;
use App\Mail\WelcomeMail;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientCoach;
use App\Models\CoachInvitation;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CoachInvitationService
{
    private const PLAN_AMOUNTS = [
        'rise' => 99900,
        'esencial' => 254150,
        'metodo' => 339150,
        'elite' => 466650,
        'presencial' => 450000,
    ];

    private const DAILY_LIMIT = 50;

    private const MONTHLY_LIMIT = 200;

    private const MAX_RESENDS = 3;

    private const EXPIRY_DAYS = 7;

    public function __construct(
        private readonly WompiService $wompi,
    ) {}

    public function create(Admin $coach, array $data): CoachInvitation
    {
        $this->enforceRateLimit($coach);
        $this->checkExistingClient($data['email']);

        $plan = PlanType::from($data['plan']);
        $amount = self::PLAN_AMOUNTS[$plan->value] ?? 0;
        $code = bin2hex(random_bytes(16));
        $wompiReference = 'WCI-'.$code;
        $expiresAt = now()->addDays($data['expires_in_days'] ?? self::EXPIRY_DAYS);

        $invitation = DB::transaction(function () use ($coach, $data, $plan, $amount, $code, $wompiReference, $expiresAt) {
            $invitation = CoachInvitation::create([
                'uuid' => Str::uuid()->toString(),
                'coach_id' => $coach->id,
                'code' => $code,
                'email' => $data['email'],
                'name' => $data['name'] ?? null,
                'plan' => $plan->value,
                'amount' => $amount,
                'currency' => 'COP',
                'subject' => $data['subject'],
                'intro_message' => isset($data['intro_message']) ? strip_tags($data['intro_message']) : null,
                'cta_label' => $data['cta_label'] ?? 'Comenzar mi plan ahora',
                'wompi_reference' => $wompiReference,
                'status' => CoachInvitationStatus::Sent->value,
                'expires_at' => $expiresAt,
                'sent_at' => now(),
            ]);

            Payment::create([
                'wompi_reference' => $wompiReference,
                'plan' => $plan->value,
                'amount' => $amount,
                'currency' => 'COP',
                'status' => PaymentStatus::Pending->value,
                'email' => $data['email'],
                'buyer_name' => $data['name'] ?? null,
            ]);

            $linkResult = $this->wompi->createPaymentLink([
                'reference' => $wompiReference,
                'amount_in_cents' => $amount * 100,
                'currency' => 'COP',
                'description' => 'Plan WellCore '.ucfirst($plan->value).' — Invitación de coach',
                'customer_email' => $data['email'],
                'customer_name' => $data['name'] ?? '',
                'expires_at' => $expiresAt->toISOString(),
                'redirect_url' => url('/pago-confirmado'),
            ]);

            if (! $linkResult['success']) {
                throw new \RuntimeException('No se pudo generar el link de pago: '.($linkResult['error'] ?? 'Error Wompi'));
            }

            $invitation->update([
                'wompi_payment_link_id' => $linkResult['link_id'],
                'wompi_payment_link_url' => $linkResult['url'],
            ]);

            return $invitation;
        });

        $this->sendEmail($invitation);

        return $invitation;
    }

    public function renderPreview(Admin $coach, array $data): string
    {
        $plan = PlanType::from($data['plan']);
        $amount = self::PLAN_AMOUNTS[$plan->value] ?? 0;
        $fakeCode = 'preview-'.Str::random(8);

        $fakeInvitation = new CoachInvitation([
            'email' => $data['email'] ?? 'ejemplo@correo.com',
            'name' => $data['name'] ?? 'Cliente',
            'plan' => $plan->value,
            'amount' => $amount,
            'subject' => $data['subject'],
            'intro_message' => isset($data['intro_message']) ? strip_tags($data['intro_message']) : null,
            'cta_label' => $data['cta_label'] ?? 'Comenzar mi plan ahora',
            'code' => $fakeCode,
            'expires_at' => now()->addDays(self::EXPIRY_DAYS),
        ]);

        $mailable = new CoachClientInvitation($fakeInvitation, $coach);

        return $mailable->render();
    }

    public function resend(CoachInvitation $invitation): void
    {
        if (! $invitation->canResend()) {
            $reason = $invitation->resend_count >= self::MAX_RESENDS
                ? 'Has alcanzado el máximo de 3 reenvíos para esta invitación.'
                : 'Solo se pueden reenviar invitaciones expiradas o fallidas.';

            throw new CoachInvitationResendException($reason);
        }

        $expiresAt = now()->addDays(self::EXPIRY_DAYS);
        $linkResult = $this->wompi->createPaymentLink([
            'reference' => $invitation->wompi_reference,
            'amount_in_cents' => (int) ($invitation->amount * 100),
            'currency' => $invitation->currency,
            'description' => 'Plan WellCore '.ucfirst($invitation->plan->value).' — Reenvío',
            'customer_email' => $invitation->email,
            'customer_name' => $invitation->name ?? '',
            'expires_at' => $expiresAt->toISOString(),
            'redirect_url' => url('/pago-confirmado'),
        ]);

        if (! $linkResult['success']) {
            throw new \RuntimeException('No se pudo regenerar el link de pago.');
        }

        $invitation->update([
            'wompi_payment_link_id' => $linkResult['link_id'],
            'wompi_payment_link_url' => $linkResult['url'],
            'expires_at' => $expiresAt,
            'resend_count' => $invitation->resend_count + 1,
            'status' => CoachInvitationStatus::Sent->value,
            'sent_at' => now(),
        ]);

        $this->sendEmail($invitation);
    }

    public function cancel(CoachInvitation $invitation): void
    {
        if ($invitation->status->isTerminal()) {
            throw new CoachInvitationCancelException(
                'No se puede cancelar una invitación que ya fue pagada o cancelada.'
            );
        }

        $invitation->update([
            'status' => CoachInvitationStatus::Cancelled->value,
            'cancelled_at' => now(),
        ]);
    }

    public function resolveByCode(string $code): ?CoachInvitation
    {
        return CoachInvitation::where('code', $code)->first();
    }

    public function trackOpen(CoachInvitation $invitation): void
    {
        if ($invitation->status !== CoachInvitationStatus::Sent) {
            return;
        }

        $invitation->update([
            'status' => CoachInvitationStatus::Opened->value,
            'opened_at' => now(),
        ]);
    }

    public function trackClickAndGetUrl(CoachInvitation $invitation): string
    {
        if (in_array($invitation->status, [CoachInvitationStatus::Sent, CoachInvitationStatus::Opened])) {
            $invitation->update([
                'status' => CoachInvitationStatus::LinkClicked->value,
                'clicked_at' => now(),
            ]);
        }

        return $invitation->wompi_payment_link_url ?? '#';
    }

    public function handlePaymentApproved(Payment $payment, CoachInvitation $invitation): void
    {
        if ($invitation->status === CoachInvitationStatus::Paid) {
            Log::info('CoachInvitation already paid — ignoring duplicate webhook', ['id' => $invitation->id]);

            return;
        }

        $client = null;

        DB::transaction(function () use ($payment, $invitation, &$client) {
            $client = $this->createOrActivateClient($invitation);

            if (! $payment->client_id) {
                $payment->update(['client_id' => $client->id]);
            }

            ClientCoach::where('client_id', $client->id)->update(['active' => false]);

            ClientCoach::create([
                'client_id' => $client->id,
                'admin_id' => $invitation->coach_id,
                'source' => 'coach_invitation',
                'coach_invitation_id' => $invitation->id,
                'assigned_at' => now(),
                'active' => true,
            ]);

            $invitation->update([
                'status' => CoachInvitationStatus::Paid->value,
                'paid_at' => now(),
                'client_id' => $client->id,
                'payment_id' => $payment->id,
            ]);
        });

        if ($client && $client->wasRecentlyCreated) {
            $coachName = $invitation->coach?->name ?? 'Tu Coach WellCore';

            Mail::to($invitation->email)->queue(new WelcomeMail(
                clientName: $invitation->name ?? 'Cliente',
                planName: $invitation->plan->label(),
                coachName: $coachName,
            ));
        }

        Log::info('CoachInvitation paid and coach assigned', [
            'invitation_id' => $invitation->id,
            'client_id' => $client?->id,
            'coach_id' => $invitation->coach_id,
        ]);
    }

    public function expireOverdue(): int
    {
        return CoachInvitation::whereNotIn('status', [
            CoachInvitationStatus::Paid->value,
            CoachInvitationStatus::Cancelled->value,
        ])
            ->where('expires_at', '<', now())
            ->update(['status' => CoachInvitationStatus::Expired->value]);
    }

    private function createOrActivateClient(CoachInvitation $invitation): Client
    {
        $client = Client::where('email', $invitation->email)->first();

        if ($client) {
            $client->update(['status' => ClientStatus::Activo->value]);

            return $client;
        }

        return Client::create([
            'email' => $invitation->email,
            'name' => $invitation->name ?? explode('@', $invitation->email)[0],
            'password_hash' => bcrypt(Str::password(12)),
            'status' => ClientStatus::Activo->value,
            'plan' => $invitation->plan->value,
        ]);
    }

    private function sendEmail(CoachInvitation $invitation): void
    {
        $coach = $invitation->relationLoaded('coach')
            ? $invitation->coach
            : Admin::find($invitation->coach_id);

        Mail::to($invitation->email)->queue(new CoachClientInvitation($invitation, $coach));
    }

    private function enforceRateLimit(Admin $coach): void
    {
        $todayCount = CoachInvitation::where('coach_id', $coach->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= self::DAILY_LIMIT) {
            throw new CoachInvitationRateLimitException('Has alcanzado el límite de 50 invitaciones por día.');
        }

        $monthCount = CoachInvitation::where('coach_id', $coach->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($monthCount >= self::MONTHLY_LIMIT) {
            throw new CoachInvitationRateLimitException('Has alcanzado el límite de 200 invitaciones por mes.');
        }
    }

    private function checkExistingClient(string $email): void
    {
        $client = Client::where('email', $email)->first();

        if ($client && $client->status === ClientStatus::Activo) {
            throw new CoachInvitationBlockedException(
                'Este email ya pertenece a un cliente activo en WellCore.',
                'CLIENT_ACTIVE'
            );
        }
    }
}
