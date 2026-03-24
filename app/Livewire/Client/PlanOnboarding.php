<?php

namespace App\Livewire\Client;

use Livewire\Component;

class PlanOnboarding extends Component
{
    public string $planType = 'esencial';
    public string $clientName = '';
    public array $slides = [];
    public bool $showOnboarding = false;

    public function mount(): void
    {
        $user = auth('wellcore')->user();
        $this->clientName = explode(' ', $user->name ?? '')[0];
        $plan = $user->plan ?? 'esencial';
        $this->planType = strtolower($plan instanceof \App\Enums\PlanType ? $plan->value : (string) $plan);

        $this->slides = $this->getSlidesForPlan($this->planType);
        $this->showOnboarding = ! (bool) ($user->onboarding_completed ?? false);
    }

    public function completeOnboarding(): void
    {
        $user = auth('wellcore')->user();
        if ($user) {
            $user->update(['onboarding_completed' => true]);
        }
        $this->showOnboarding = false;
    }

    public function triggerOnboarding(): void
    {
        $this->showOnboarding = true;
    }

    protected function getSlidesForPlan(string $planType): array
    {
        $common = [
            [
                'icon' => 'sparkles',
                'title' => '¡Bienvenido a WellCore!',
                'description' => "Hola {$this->clientName}, tu coach ha creado un programa personalizado para ti. Aquí te explicamos cómo sacarle el máximo provecho.",
                'color' => 'wc-accent',
            ],
        ];

        $planSlides = match ($planType) {
            'esencial' => [
                [
                    'icon' => 'dumbbell',
                    'title' => 'Tu Entrenamiento',
                    'description' => 'Tu plan incluye un programa de entrenamiento personalizado. Cada ejercicio tiene series, repeticiones y descansos específicos para ti.',
                    'color' => 'blue-500',
                    'features' => ['Ejercicios con instrucciones detalladas', 'Registro de peso y repeticiones', 'Detección automática de records personales'],
                ],
                [
                    'icon' => 'habits',
                    'title' => 'Hábitos Diarios',
                    'description' => 'Agua, sueño, nutrición y entrenamiento — marca tus hábitos cada día para construir consistencia.',
                    'color' => 'emerald-500',
                    'features' => ['Seguimiento diario con rachas', 'Heatmap de 30 días', 'Tips de tu coach'],
                ],
            ],
            'metodo' => [
                [
                    'icon' => 'dumbbell',
                    'title' => 'Tu Entrenamiento',
                    'description' => 'Tu programa de entrenamiento está diseñado con periodización para maximizar tus resultados. Cada semana tiene progresiones específicas.',
                    'color' => 'blue-500',
                    'features' => ['Workout Player interactivo', 'Timer de descanso automático', 'Records personales con celebración'],
                ],
                [
                    'icon' => 'nutrition',
                    'title' => 'Plan de Nutrición',
                    'description' => 'Tu coach ha calculado tus macros ideales. Proteína, carbohidratos y grasas en las cantidades exactas para tu objetivo.',
                    'color' => 'emerald-500',
                    'features' => ['Calorías y macros personalizados', 'Plan de comidas detallado', 'Tracker de agua diario'],
                ],
                [
                    'icon' => 'habits',
                    'title' => 'Hábitos + Suplementación',
                    'description' => 'Los hábitos son la base de la transformación. Tu protocolo de suplementación complementa tu nutrición.',
                    'color' => 'violet-500',
                    'features' => ['5 hábitos diarios con rachas', 'Suplementos con horarios específicos', 'Adherencia semanal visible'],
                ],
            ],
            'elite' => [
                [
                    'icon' => 'dumbbell',
                    'title' => 'Entrenamiento Elite',
                    'description' => 'Tu plan es el más completo. Incluye variaciones semanales y progresiones de carga para resultados de élite.',
                    'color' => 'blue-500',
                    'features' => ['Progresiones semanales automáticas', 'Variaciones de ejercicios', 'Análisis de volumen de entrenamiento'],
                ],
                [
                    'icon' => 'nutrition',
                    'title' => 'Nutrición Avanzada',
                    'description' => 'Macros calculados científicamente con ajustes según tu progreso. Incluye plan de comidas y timing nutricional.',
                    'color' => 'emerald-500',
                    'features' => ['Macros personalizados', 'Timing nutricional', 'Ajustes por fase de entrenamiento'],
                ],
                [
                    'icon' => 'elite',
                    'title' => '6 Áreas de Coaching',
                    'description' => 'Entrenamiento, nutrición, hábitos, suplementación, ciclo hormonal y bloodwork — coaching integral.',
                    'color' => 'amber-500',
                    'features' => ['Seguimiento de ciclo hormonal', 'Análisis de bloodwork', 'Suplementación personalizada'],
                ],
            ],
            'rise' => [
                [
                    'icon' => 'fire',
                    'title' => '30 Días de Transformación',
                    'description' => 'RISE es un programa intensivo de 30 días. Cada día cuenta. Tu coach te guía paso a paso.',
                    'color' => 'orange-500',
                    'features' => ['Entrenamiento diario progresivo', 'Tips de nutrición', 'Tracking diario de progreso'],
                ],
                [
                    'icon' => 'chart',
                    'title' => 'Mide Tu Progreso',
                    'description' => 'Al inicio y al final tomarás medidas y fotos. La ciencia no miente — verás tu transformación en datos.',
                    'color' => 'cyan-500',
                    'features' => ['Medidas corporales día 1 y 30', 'Fotos de progreso', 'Reporte final de resultados'],
                ],
            ],
            'trial' => [
                [
                    'icon' => 'rocket',
                    'title' => '3 Días de Muestra',
                    'description' => 'Tienes 3 días para experimentar el método WellCore. Si te gusta, imagina lo que lograrás en 12 semanas.',
                    'color' => 'violet-500',
                    'features' => ['Entrenamiento de muestra', 'Tips de nutrición básicos', 'Acceso al dashboard completo'],
                ],
            ],
            default => [
                [
                    'icon' => 'dumbbell',
                    'title' => 'Tu Programa',
                    'description' => 'Tu coach está preparando un programa personalizado para ti.',
                    'color' => 'blue-500',
                    'features' => ['Entrenamiento personalizado', 'Seguimiento de progreso'],
                ],
            ],
        };

        $finalSlide = [
            [
                'icon' => 'rocket',
                'title' => '¡Comienza Ahora!',
                'description' => 'Todo está listo. Tu coach está aquí, tu comunidad está aquí. Solo falta tu decisión.',
                'color' => 'wc-accent',
                'cta' => true,
            ],
        ];

        return array_merge($common, $planSlides, $finalSlide);
    }

    public function render()
    {
        return view('livewire.client.plan-onboarding');
    }
}
