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

        return [
            'rise' => [
                'name' => 'RISE',
                'badge' => 'Programa 30 dias',
                'badgeRight' => 'Pago unico',
                'price' => '$99.900',
                'priceSuffix' => 'COP',
                'priceUsd' => '~$25 USD',
                'billingNote' => 'Sin compromisos a largo plazo. Pago unico de $99.900 COP.',
                'ctaText' => 'Comenzar mi programa RISE',
                'ctaUrl' => "{$baseUrl}/rise-enroll",
                'intro' => 'Te invitamos a dar el primer paso hacia tu transformacion fisica con el programa RISE de WellCore Fitness — 30 dias disenados para cambiar tu cuerpo y tus habitos.',
                'features' => [
                    'Plan de entrenamiento progresivo de 4 semanas',
                    'Tips de nutricion y alimentacion saludable',
                    'Protocolo de habitos diarios para resultados reales',
                    'Guia de suplementacion basica',
                    'Acceso completo a la plataforma WellCore',
                    'Comunidad de apoyo y motivacion',
                ],
                'steps' => [
                    ['title' => 'Inscribete y completa tu perfil', 'desc' => 'Registra tus objetivos, experiencia y medidas actuales para personalizar tu programa.'],
                    ['title' => 'Recibe tu plan personalizado', 'desc' => 'Tu programa de entrenamiento y nutricion se adapta a ti — no es un plan generico.'],
                    ['title' => 'Entrena, registra y transforma', 'desc' => 'Registra tus entrenamientos en la plataforma y observa tu progreso en tiempo real.'],
                ],
                'followUp' => 'En WellCore no te dejamos solo. Nuestro sistema de seguimiento incluye <strong style="color:#FAFAFA;">tracking de entrenamientos en tiempo real</strong>, <strong style="color:#FAFAFA;">metricas de progreso</strong> (peso, medidas, rendimiento), y <strong style="color:#FAFAFA;">check-ins periodicos</strong> donde evaluas tu bienestar y tu coach ajusta el plan. Todo basado en datos, no en suposiciones.',
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
                'ctaText' => 'Comenzar plan Esencial',
                'ctaUrl' => "{$baseUrl}/pagar?plan=esencial",
                'intro' => 'Te invitamos a comenzar tu viaje de transformacion con el plan Esencial de WellCore Fitness — coaching online personalizado con entrenamiento disenado para tus objetivos.',
                'features' => [
                    'Entrenamiento 100% personalizado por tu coach',
                    'Protocolo de habitos y bienestar diario',
                    'Dashboard de progreso y metricas en tiempo real',
                    'Seguimiento semanal de entrenamientos',
                    'Acceso completo a la plataforma WellCore',
                    '11 funcionalidades de coaching incluidas',
                ],
                'steps' => [
                    ['title' => 'Inscribete y completa tu perfil', 'desc' => 'Registra tus objetivos, nivel de experiencia y medidas actuales.'],
                    ['title' => 'Tu coach crea tu plan personalizado', 'desc' => 'Un coach certificado disena tu rutina de entrenamiento especifica para tus metas.'],
                    ['title' => 'Entrena, registra y avanza', 'desc' => 'Registra cada sesion en la plataforma y mide tu progreso semana a semana.'],
                ],
                'followUp' => 'Cada semana realizas un <strong style="color:#FAFAFA;">check-in</strong> donde reportas tu bienestar, fotos de progreso y sensaciones. Tu coach revisa tus <strong style="color:#FAFAFA;">entrenamientos registrados</strong> en la plataforma y te da retroalimentacion. Todo se mide: peso, rendimiento, adherencia. <strong style="color:#FAFAFA;">Datos reales, resultados reales.</strong>',
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
                'ctaText' => 'Comenzar plan Metodo',
                'ctaUrl' => "{$baseUrl}/pagar?plan=metodo",
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
                'steps' => [
                    ['title' => 'Inscribete y completa tu perfil', 'desc' => 'Registra tus objetivos, historial de entrenamiento, preferencias alimenticias y medidas.'],
                    ['title' => 'Tu coach disena entrenamiento + nutricion', 'desc' => 'Recibes un plan integral: rutinas de entreno + plan de comidas adaptado a tu estilo de vida.'],
                    ['title' => 'Seguimiento semanal con ajustes', 'desc' => 'Cada semana tu coach revisa tu progreso y ajusta entreno y nutricion segun tus resultados.'],
                ],
                'followUp' => 'Con el plan Metodo, cada semana realizas un <strong style="color:#FAFAFA;">check-in completo</strong> con fotos de progreso y reporte de bienestar. Tu coach analiza tus <strong style="color:#FAFAFA;">datos de entrenamiento y nutricion</strong> registrados en la plataforma y realiza <strong style="color:#FAFAFA;">ajustes personalizados</strong> a tu plan. Entrenamiento y alimentacion trabajando juntos — ese es el metodo.',
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
                'ctaText' => 'Comenzar plan Elite',
                'ctaUrl' => "{$baseUrl}/pagar?plan=elite",
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
                'steps' => [
                    ['title' => 'Inscribete y completa tu perfil avanzado', 'desc' => 'Objetivos, historial medico, laboratorios, ciclo hormonal y preferencias completas.'],
                    ['title' => 'Plan integral personalizado al 100%', 'desc' => 'Entrenamiento + nutricion + suplementacion + habitos — todo adaptado a tu biologia.'],
                    ['title' => 'Seguimiento 1:1 y ajustes semanales', 'desc' => 'Check-ins personalizados con tu coach, analisis de datos y ajustes basados en ciencia.'],
                ],
                'followUp' => 'El plan Elite incluye el nivel mas alto de atencion. Realizas <strong style="color:#FAFAFA;">check-ins 1:1</strong> con tu coach, quien analiza tus <strong style="color:#FAFAFA;">resultados de laboratorio</strong> y <strong style="color:#FAFAFA;">ciclo hormonal</strong> para optimizar tu plan. Cada dato importa: metricas de entrenamiento, composicion corporal, nutricion, sueno y estres. <strong style="color:#FAFAFA;">Ciencia aplicada a tu transformacion.</strong>',
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
                'ctaText' => 'Inscribirme al plan Presencial',
                'ctaUrl' => "{$baseUrl}/presencial/inscripcion",
                'intro' => 'Te invitamos a entrenar cara a cara con el plan Presencial de WellCore Fitness — sesiones en persona con tu coach en Bucaramanga, combinadas con el poder de nuestra plataforma digital.',
                'features' => [
                    '<strong style="color:#FAFAFA;">Sesiones presenciales</strong> con tu coach en Bucaramanga',
                    'Plan nutricional personalizado completo',
                    'Protocolo de habitos y bienestar diario',
                    'Guia de suplementacion personalizada',
                    'Acceso completo a la plataforma WellCore',
                    'Seguimiento presencial + digital combinado',
                    'Correccion de tecnica en persona',
                ],
                'steps' => [
                    ['title' => 'Inscribete y agenda tu primera sesion', 'desc' => 'Completa tu perfil con objetivos y experiencia. Tu coach te contacta para agendar.'],
                    ['title' => 'Entrena con tu coach en persona', 'desc' => 'Sesiones presenciales donde tu coach corrige tecnica y supervisa cada movimiento.'],
                    ['title' => 'Seguimiento digital entre sesiones', 'desc' => 'Registra tu nutricion y entrenamientos en la plataforma los dias que no ves a tu coach.'],
                ],
                'followUp' => 'El plan Presencial combina lo mejor de ambos mundos: <strong style="color:#FAFAFA;">sesiones cara a cara</strong> donde tu coach corrige tu tecnica en tiempo real, mas <strong style="color:#FAFAFA;">seguimiento digital</strong> a traves de la plataforma WellCore los dias restantes. Tu nutricion, habitos y suplementacion se monitorean digitalmente. <strong style="color:#FAFAFA;">Entrenamiento presencial + tecnologia = resultados maximos.</strong>',
                'isPremium' => false,
                'locationNote' => '&#128205; <strong style="color:#FAFAFA;">Ubicacion:</strong> Bucaramanga, Colombia. Contactanos por WhatsApp para coordinar horarios y ubicacion exacta.',
            ],
        ];
    }
}
