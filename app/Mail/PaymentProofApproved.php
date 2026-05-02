<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\CoachInvitation;
use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public PaymentProof $proof,
        public Client $client,
        public CoachInvitation $invitation,
        public ?string $resetUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡Tu acceso a WellCore está listo, {$this->client->name}!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-proof-approved',
            with: [
                'clientName' => $this->client->name,
                'clientEmail' => $this->client->email,
                'planLabel' => $this->proof->plan->label(),
                'coachName' => $this->proof->coach->name ?? 'Tu Coach WellCore',
                'loginUrl' => url('/login'),
                'resetUrl' => $this->resetUrl,
            ],
        );
    }
}
