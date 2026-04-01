<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftPlanInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $planKey,
        public string $gifterName,
        public string $gifterEmail,
        public ?string $giftMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        $planNames = [
            'rise' => 'RISE',
            'esencial' => 'Esencial',
            'metodo' => 'Metodo',
            'elite' => 'Elite',
            'presencial' => 'Presencial',
        ];

        $planName = $planNames[$this->planKey] ?? $this->planKey;

        return new Envelope(
            subject: "{$this->gifterName} te ha regalado el plan {$planName} — WellCore Fitness",
        );
    }

    public function content(): Content
    {
        $plans = $this->getPlanData();

        return new Content(
            view: 'emails.gift-plan-invitation',
            with: [
                'plan' => $plans[$this->planKey] ?? $plans['esencial'],
                'recipientName' => $this->recipientName,
                'gifterName' => $this->gifterName,
                'gifterEmail' => $this->gifterEmail,
                'giftMessage' => $this->giftMessage,
            ],
        );
    }

    protected function getPlanData(): array
    {
        $baseUrl = 'https://wellcorefitness.com';
        $loginUrl = "{$baseUrl}/login";

        $giftSteps = [
            ['title' => 'Haz clic en el boton de abajo', 'desc' => 'Te lleva directo a la pagina de registro. Tu plan ya esta cubierto — solo crea tu cuenta.'],
            ['title' => 'Crea tu contrasena', 'desc' => 'Establece tu contrasena para ingresar a la plataforma WellCore.'],
            ['title' => 'Inicia sesion en wellcorefitness.com', 'desc' => 'Ingresa con tu email y contrasena en wellcorefitness.com/login'],
            ['title' => 'Sube tus fotos y completa tus medidas', 'desc' => 'En tu dashboard, sube fotos de progreso (frente, lado, espalda) y registra tus medidas actuales.'],
            ['title' => 'Tu coach revisa y te contacta', 'desc' => 'Tu coach asignado revisa tu perfil completo y te contacta por WhatsApp para comenzar.'],
        ];

        return [
            'rise' => [
                'name' => 'RISE',
                'badge' => 'Programa 30 dias',
                'price' => '$99.900',
                'priceSuffix' => 'COP',
                'billingNote' => 'Este plan fue regalado. No necesitas pagar nada.',
                'ctaText' => 'ACTIVAR MI REGALO',
                'ctaUrl' => "{$baseUrl}/pagar?plan=rise",
                'loginUrl' => $loginUrl,
                'features' => [
                    'Plan de entrenamiento progresivo de 4 semanas',
                    'Tips de nutricion y alimentacion saludable',
                    'Protocolo de habitos diarios para resultados reales',
                    'Guia de suplementacion basica',
                    'Acceso completo a la plataforma WellCore',
                    'Comunidad de apoyo y motivacion',
                ],
                'steps' => $giftSteps,
            ],
            'esencial' => [
                'name' => 'ESENCIAL',
                'badge' => 'Tu primer paso',
                'price' => '$299.000',
                'priceSuffix' => 'COP/mes',
                'billingNote' => 'Primer mes regalado. Sin compromiso.',
                'ctaText' => 'ACTIVAR MI REGALO',
                'ctaUrl' => "{$baseUrl}/pagar?plan=esencial",
                'loginUrl' => $loginUrl,
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Protocolo de habitos y bienestar diario',
                    'Dashboard de progreso y metricas en tiempo real',
                    'Seguimiento semanal de entrenamientos',
                    'Acceso completo a la plataforma WellCore',
                ],
                'steps' => $giftSteps,
            ],
            'metodo' => [
                'name' => 'METODO',
                'badge' => 'Mas popular',
                'price' => '$399.000',
                'priceSuffix' => 'COP/mes',
                'billingNote' => 'Primer mes regalado. Sin compromiso.',
                'ctaText' => 'ACTIVAR MI REGALO',
                'ctaUrl' => "{$baseUrl}/pagar?plan=metodo",
                'loginUrl' => $loginUrl,
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Plan nutricional completo y personalizado',
                    'Protocolo de habitos y bienestar diario',
                    'Guia de suplementacion avanzada',
                    'Ajustes semanales con tu coach personal',
                    'Check-in semanal de progreso',
                ],
                'steps' => $giftSteps,
            ],
            'elite' => [
                'name' => 'ELITE',
                'badge' => 'Premium',
                'price' => '$549.000',
                'priceSuffix' => 'COP/mes',
                'billingNote' => 'Primer mes regalado. Sin compromiso.',
                'ctaText' => 'ACTIVAR MI REGALO',
                'ctaUrl' => "{$baseUrl}/pagar?plan=elite",
                'loginUrl' => $loginUrl,
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Plan nutricional completo y personalizado',
                    'Protocolo avanzado de habitos y bienestar',
                    'Guia de suplementacion avanzada',
                    'Check-ins 1:1 con tu coach',
                    'Analisis de ciclo hormonal (mujeres)',
                    'Interpretacion de laboratorios (bloodwork)',
                ],
                'steps' => $giftSteps,
            ],
            'presencial' => [
                'name' => 'PRESENCIAL',
                'badge' => 'Cara a cara',
                'price' => '$450.000 — $650.000',
                'priceSuffix' => 'COP/mes',
                'billingNote' => 'Primer mes regalado. Tu coach te contactara para coordinar.',
                'ctaText' => 'ACTIVAR MI REGALO',
                'ctaUrl' => "{$baseUrl}/presencial/inscripcion",
                'loginUrl' => $loginUrl,
                'features' => [
                    'Sesiones presenciales con tu coach en Bucaramanga',
                    'Plan nutricional personalizado completo',
                    'Protocolo de habitos y bienestar diario',
                    'Guia de suplementacion personalizada',
                    'Acceso completo a la plataforma WellCore',
                ],
                'steps' => $giftSteps,
            ],
        ];
    }
}
