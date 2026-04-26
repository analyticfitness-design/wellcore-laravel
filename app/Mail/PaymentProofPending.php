<?php

namespace App\Mail;

use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofPending extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public PaymentProof $proof,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo comprobante de pago pendiente — #{$this->proof->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-proof-pending',
            with: [
                'proofId' => $this->proof->id,
                'coachName' => $this->proof->coach->name ?? 'Coach',
                'clientEmail' => $this->proof->client_email,
                'planLabel' => $this->proof->plan->label(),
                'submittedAt' => $this->proof->submitted_at?->format('d/m/Y H:i'),
            ],
        );
    }
}
