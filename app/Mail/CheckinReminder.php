<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckinReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $clientName;

    public function __construct(
        public Client $client,
    ) {
        $this->clientName = $client->name ?? $client->first_name ?? 'Cliente';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Hora de tu Check-in Semanal');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.checkin-reminder');
    }
}
