<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\CoachInvitation;
use App\Models\CoachProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachClientInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachInvitation $invitation,
        public Admin $coach,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->invitation->subject,
        );
    }

    public function content(): Content
    {
        $coachProfile = $this->coach->coachProfile;
        $planDetails  = $this->getPlanDetails($this->invitation->plan->value);

        return new Content(
            view: 'emails.coach-client-invitation',
            with: [
                'invitation'    => $this->invitation,
                'coach'         => $this->coach,
                'coachProfile'  => $coachProfile,
                'planDetails'   => $planDetails,
                'invitationUrl' => $this->invitation->invitationUrl(),
                'pixelUrl'      => $this->invitation->pixelUrl(),
            ],
        );
    }

    private function getPlanDetails(string $plan): array
    {
        $plans = [
            'rise' => [
                'name'     => 'RISE',
                'features' => [
                    'Plan de entrenamiento progresivo de 4 semanas',
                    'Tips de nutricion y alimentacion saludable',
                    'Protocolo de habitos diarios para resultados reales',
                    'Guia de suplementacion basica',
                    'Acceso completo a la plataforma WellCore',
                ],
            ],
            'esencial' => [
                'name'     => 'ESENCIAL',
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Protocolo de habitos y bienestar diario',
                    'Dashboard de progreso y metricas en tiempo real',
                    'Seguimiento semanal de entrenamientos',
                    'Acceso completo a la plataforma WellCore',
                ],
            ],
            'metodo' => [
                'name'     => 'METODO',
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Plan nutricional completo y personalizado',
                    'Protocolo de habitos y bienestar diario',
                    'Guia de suplementacion avanzada',
                    'Ajustes semanales con tu coach personal',
                ],
            ],
            'elite' => [
                'name'     => 'ELITE',
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Plan nutricional completo y personalizado',
                    'Check-ins 1:1 con tu coach',
                    'Analisis de ciclo hormonal (mujeres)',
                    'Interpretacion de laboratorios',
                ],
            ],
            'presencial' => [
                'name'     => 'PRESENCIAL',
                'features' => [
                    'Sesiones presenciales con tu coach en Bucaramanga',
                    'Plan nutricional personalizado completo',
                    'Protocolo de habitos y bienestar diario',
                    'Acceso completo a la plataforma WellCore',
                    'Correccion de tecnica en persona',
                ],
            ],
        ];

        return $plans[$plan] ?? $plans['esencial'];
    }
}
