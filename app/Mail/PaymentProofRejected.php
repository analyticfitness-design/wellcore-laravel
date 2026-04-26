<?php

namespace App\Mail;

use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public PaymentProof $proof,
        public string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Comprobante de pago rechazado — {$this->proof->client_email}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-proof-rejected',
            with: [
                'coachName' => $this->proof->coach->name ?? 'Coach',
                'clientEmail' => $this->proof->client_email,
                'planLabel' => $this->proof->plan->label(),
                'reason' => $this->reason,
            ],
        );
    }
}
