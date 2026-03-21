<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public string $amount,
        public string $currency,
        public string $plan,
        public string $reference,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pago Confirmado - WellCore Fitness');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-confirmation');
    }
}
