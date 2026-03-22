<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $referrerName,
        public string $referralLink,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->referrerName} te invita a WellCore Fitness",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.referral-invitation',
        );
    }
}
