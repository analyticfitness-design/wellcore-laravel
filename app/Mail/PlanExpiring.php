<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanExpiring extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public string $planName,
        public string $expiryDate,
        public string $renewalAmount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu Plan WellCore Expira Pronto');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.plan-expiring');
    }
}
