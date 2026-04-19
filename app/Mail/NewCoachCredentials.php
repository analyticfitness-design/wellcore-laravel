<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCoachCredentials extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $coachName,
        public string $username,
        public string $temporaryPassword,
        public bool $isReset = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isReset
            ? 'Nueva contrasena temporal — WellCore Coach Portal'
            : 'Bienvenido al equipo WellCore — Tus credenciales de coach';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.coach-credentials',
            with: [
                'coachName' => $this->coachName,
                'username' => $this->username,
                'temporaryPassword' => $this->temporaryPassword,
                'isReset' => $this->isReset,
                'loginUrl' => 'https://www.wellcorefitness.com/login',
            ],
        );
    }
}
