<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklySummary extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public int $weekNumber,
        public int $workoutsCompleted,
        public int $adherencePercent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu Resumen Semanal - WellCore');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.weekly-summary');
    }
}
