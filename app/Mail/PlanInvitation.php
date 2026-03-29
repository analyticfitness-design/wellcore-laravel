<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $planKey,
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
            subject: "Tu invitacion al plan {$planName} — WellCore Fitness",
        );
    }

    public function content(): Content
    {
        $plans = $this->getPlanData();

        return new Content(
            view: 'emails.plan-invitation',
            with: [
                'plan' => $plans[$this->planKey] ?? $plans['esencial'],
                'recipientName' => $this->recipientName,
            ],
        );
    }

    protected function getPlanData(): array
    {
        $baseUrl = 'https://wellcorefitness.com';

        $loginUrl = "{$baseUrl}/login";

        // Flujo comun post-pago para todos los planes online
        $onlineSteps = [
            ['title' => 'Haz clic en el boton de abajo', 'desc' => 'Te lleva directo a la pagina de pago seguro. Completa tus datos personales.'],
            ['title' => 'Paga con Wompi', 'desc' => 'Tarjeta de credito/debito, Nequi, PSE o Bancolombia. Pago 100% seguro. Recibiras confirmacion inmediata.'],
            ['title' => 'Crea tu contrasena', 'desc' => 'Despues del pago, se crea tu cuenta. Establece tu contrasena para ingresar a la plataforma.'],
            ['title' => 'Inicia sesion en wellcorefitness.com', 'desc' => 'Ingresa con tu email y contrasena en wellcorefitness.com/login'],
            ['title' => 'Sube tus fotos y completa tus medidas', 'desc' => 'En tu dashboard, sube fotos de progreso (frente, lado, espalda) y registra tus medidas actuales.'],
            ['title' => 'Tu coach revisa y te contacta', 'desc' => 'Tu coach asignado revisa tu perfil completo y te contacta por WhatsApp para comenzar.'],
        ];

        $presencialSteps = [
            ['title' => 'Haz clic en el boton de abajo', 'desc' => 'Te lleva a la pagina de inscripcion. Completa tus datos y realiza el pago.'],
            ['title' => 'Paga con Wompi', 'desc' => 'Tarjeta de credito/debito, Nequi, PSE o Bancolombia. Pago 100% seguro. Confirmacion inmediata.'],
            ['title' => 'Crea tu contrasena e inicia sesion', 'desc' => 'Se crea tu cuenta. Ingresa en wellcorefitness.com/login con tu email y contrasena.'],
            ['title' => 'Sube fotos y completa tus medidas', 'desc' => 'En tu dashboard, sube fotos de progreso y registra tus medidas para que tu coach te conozca.'],
            ['title' => 'Tu coach te contacta para agendar', 'desc' => 'Tu coach te escribe por WhatsApp para coordinar horarios y ubicacion de tus sesiones presenciales.'],
        ];

        return [
            'rise' => [
                'name' => 'RISE',
                'badge' => 'Programa 30 dias',
                'badgeRight' => 'Pago unico',
                'price' => '$99.900',
                'priceSuffix' => 'COP',
                'priceUsd' => '~$25 USD',
                'billingNote' => 'Pago unico de $99.900 COP. Sin suscripcion.',
                'ctaText' => 'PAGAR Y COMENZAR RISE',
                'ctaUrl' => "{$baseUrl}/pagar?plan=rise",
                'loginUrl' => $loginUrl,
                'intro' => 'Te invitamos a dar el primer paso hacia tu transformacion fisica con el programa RISE de WellCore Fitness — 30 dias disenados para cambiar tu cuerpo y tus habitos.',
                'features' => [
                    'Plan de entrenamiento progresivo de 4 semanas',
                    'Tips de nutricion y alimentacion saludable',
                    'Protocolo de habitos diarios para resultados reales',
                    'Guia de suplementacion basica',
                    'Acceso completo a la plataforma WellCore',
                    'Comunidad de apoyo y motivacion',
                ],
                'steps' => $onlineSteps,
                'followUp' => 'En WellCore no te dejamos solo. Tu coach revisa tus <strong style="color:#FAFAFA;">entrenamientos registrados</strong> en la plataforma, analiza tus <strong style="color:#FAFAFA;">fotos de progreso</strong> y <strong style="color:#FAFAFA;">medidas</strong>, y te da retroalimentacion personalizada. Todo basado en datos, no en suposiciones.',
                'isPremium' => false,
                'locationNote' => null,
            ],
            'esencial' => [
                'name' => 'ESENCIAL',
                'badge' => 'Tu primer paso',
                'badgeRight' => 'Mensual',
                'price' => '$299.000',
                'priceSuffix' => 'COP/mes',
                'priceUsd' => '~$65 USD',
                'billingNote' => '$299.000 COP/mes. Cancela cuando quieras.',
                'ctaText' => 'PAGAR Y COMENZAR ESENCIAL',
                'ctaUrl' => "{$baseUrl}/pagar?plan=esencial",
                'loginUrl' => $loginUrl,
                'intro' => 'Te invitamos a comenzar tu viaje de transformacion con el plan Esencial de WellCore Fitness — coaching online personalizado con entrenamiento disenado para tus objetivos.',
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Protocolo de habitos y bienestar diario',
                    'Dashboard de progreso y metricas en tiempo real',
                    'Seguimiento semanal de entrenamientos',
                    'Acceso completo a la plataforma WellCore',
                    '11 funcionalidades de coaching incluidas',
                ],
                'steps' => $onlineSteps,
                'followUp' => 'Cada semana realizas un <strong style="color:#FAFAFA;">check-in</strong> con fotos de progreso y reporte de bienestar. Tu coach revisa tus <strong style="color:#FAFAFA;">entrenamientos y medidas</strong> en la plataforma y ajusta tu plan. <strong style="color:#FAFAFA;">Datos reales, resultados reales.</strong>',
                'isPremium' => false,
                'locationNote' => null,
            ],
            'metodo' => [
                'name' => 'METODO',
                'badge' => '&#11088; Mas popular',
                'badgeRight' => 'Mensual',
                'price' => '$399.000',
                'priceSuffix' => 'COP/mes',
                'priceUsd' => '~$95 USD',
                'billingNote' => '$399.000 COP/mes. Sin permanencia minima.',
                'ctaText' => 'PAGAR Y COMENZAR METODO',
                'ctaUrl' => "{$baseUrl}/pagar?plan=metodo",
                'loginUrl' => $loginUrl,
                'intro' => 'Te invitamos a experimentar el coaching integral con el plan Metodo de WellCore Fitness — nuestro plan mas popular que combina entrenamiento, nutricion y ajustes semanales con tu coach.',
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Plan nutricional completo y personalizado',
                    'Protocolo de habitos y bienestar diario',
                    'Guia de suplementacion avanzada',
                    'Ajustes semanales con tu coach personal',
                    'Check-in semanal de progreso',
                    'Dashboard con metricas y analisis de rendimiento',
                    '21 funcionalidades de coaching incluidas',
                ],
                'steps' => $onlineSteps,
                'followUp' => 'Con el plan Metodo, cada semana haces un <strong style="color:#FAFAFA;">check-in completo</strong> con fotos y bienestar. Tu coach analiza <strong style="color:#FAFAFA;">entrenamiento y nutricion</strong> y realiza <strong style="color:#FAFAFA;">ajustes personalizados</strong>. Entrenamiento y alimentacion trabajando juntos — ese es el metodo.',
                'isPremium' => false,
                'locationNote' => null,
            ],
            'elite' => [
                'name' => 'ELITE',
                'badge' => '&#9733; Premium',
                'badgeRight' => 'Mensual',
                'price' => '$549.000',
                'priceSuffix' => 'COP/mes',
                'priceUsd' => '~$150 USD',
                'billingNote' => '$549.000 COP/mes. La inversion mas completa en tu transformacion.',
                'ctaText' => 'PAGAR Y COMENZAR ELITE',
                'ctaUrl' => "{$baseUrl}/pagar?plan=elite",
                'loginUrl' => $loginUrl,
                'intro' => 'Te invitamos a vivir la experiencia definitiva de transformacion con el plan Elite de WellCore Fitness — nuestro plan premium con atencion 1:1, nutricion completa y analisis avanzados.',
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Plan nutricional completo y personalizado',
                    'Protocolo avanzado de habitos y bienestar',
                    'Guia de suplementacion avanzada',
                    '<strong style="color:#FAFAFA;">Check-ins 1:1 con tu coach</strong>',
                    '<strong style="color:#FAFAFA;">Analisis de ciclo hormonal</strong> (mujeres)',
                    '<strong style="color:#FAFAFA;">Interpretacion de laboratorios</strong> (bloodwork)',
                    'Ajustes semanales de entrenamiento y nutricion',
                    'Prioridad maxima de atencion con tu coach',
                    '29 funcionalidades de coaching incluidas',
                ],
                'steps' => $onlineSteps,
                'followUp' => 'El plan Elite incluye <strong style="color:#FAFAFA;">check-ins 1:1</strong>, analisis de <strong style="color:#FAFAFA;">laboratorios</strong> y <strong style="color:#FAFAFA;">ciclo hormonal</strong>. Cada dato importa: entrenamiento, composicion corporal, nutricion, sueno y estres. <strong style="color:#FAFAFA;">Ciencia aplicada a tu transformacion.</strong>',
                'isPremium' => true,
                'locationNote' => null,
            ],
            'presencial' => [
                'name' => 'PRESENCIAL',
                'badge' => '&#127939; Cara a cara',
                'badgeRight' => 'Mensual',
                'price' => '$450.000 — $650.000',
                'priceSuffix' => 'COP/mes',
                'priceUsd' => 'segun frecuencia de sesiones',
                'billingNote' => 'Desde $450.000 COP/mes segun frecuencia.',
                'ctaText' => 'INSCRIBIRME Y PAGAR PRESENCIAL',
                'ctaUrl' => "{$baseUrl}/presencial/inscripcion",
                'loginUrl' => $loginUrl,
                'intro' => 'Te invitamos a entrenar cara a cara con el plan Presencial de WellCore Fitness — sesiones en persona con tu coach en Bucaramanga, combinadas con nuestra plataforma digital.',
                'features' => [
                    '<strong style="color:#FAFAFA;">Sesiones presenciales</strong> con tu coach en Bucaramanga',
                    'Plan nutricional personalizado completo',
                    'Protocolo de habitos y bienestar diario',
                    'Guia de suplementacion personalizada',
                    'Acceso completo a la plataforma WellCore',
                    'Seguimiento presencial + digital combinado',
                    'Correccion de tecnica en persona',
                ],
                'steps' => $presencialSteps,
                'followUp' => 'Combinas <strong style="color:#FAFAFA;">sesiones cara a cara</strong> con <strong style="color:#FAFAFA;">seguimiento digital</strong>. Tu coach corrige tecnica en persona y monitorea nutricion, habitos y suplementacion digitalmente. <strong style="color:#FAFAFA;">Presencial + tecnologia = resultados maximos.</strong>',
                'isPremium' => false,
                'locationNote' => '&#128205; <strong style="color:#FAFAFA;">Ubicacion:</strong> Bucaramanga, Colombia. Tu coach te contactara por WhatsApp para coordinar.',
            ],
        ];
    }
}
