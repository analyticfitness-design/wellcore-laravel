<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Habits;

use App\Services\ComposeEngine\Data\ComposeContext;
use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\ComposeEngine\Principles\PrincipleInjector;
use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Compose stage para vertical=habitos (Sprint 16).
 *
 * Pipeline simple — sin DB lookups (los hábitos canónicos están hardcodeados
 * porque son pilares universales, no per-cliente):
 *   1. Genera un catálogo de hábitos base (sueño, hidratación, registro, etc.)
 *   2. Personaliza según ClientProfile (peso → hidratación, gender → ciclo, etc.)
 *   3. Produce JSON con shape `habitos[]` compatible con lint rules schema básicas.
 *
 * Shape canónico:
 *   {
 *     plan_type: "habitos",
 *     titulo, objetivo, duracion_semanas, fecha_inicio,
 *     habitos: [
 *       {
 *         nombre: "Sueño 7-9h consistente",
 *         categoria: "sueño",
 *         objetivo: "7.5 horas promedio semanal",
 *         tracking_method: "app WellCore — campo horas_sueño",
 *         por_que_importa: "...",
 *         tips: [...]
 *       }, ...
 *     ],
 *     notas_coach, tips
 *   }
 */
final class HabitsPlanComposer
{
    public function __construct(
        private readonly PrincipleInjector $principleInjector,
    ) {
    }

    public function compose(ComposeContext $ctx): ComposeResult
    {
        $start = microtime(true);
        $warnings = [];

        $habitos = $this->buildHabitos($ctx->profile);

        // Sprint 34: inyectar principles relevantes
        $injectedPrinciples = $this->principleInjector->selectTop($ctx->profile, 'habitos', limit: 3);
        $extraTips = $this->principleInjector->asTipsArray($injectedPrinciples);

        $planJson = [
            'plan_type' => 'habitos',
            'titulo' => $this->buildTitle($ctx),
            'objetivo' => 'Consolidar pilares de recuperación y consistencia (sueño, hidratación, registro). Los hábitos básicos sostenidos valen más que cualquier suplemento o táctica avanzada.',
            'metodologia' => (string) $ctx->methodology->name,
            'duracion_semanas' => 4,
            'fecha_inicio' => $ctx->fechaInicio,
            'habitos' => $habitos,
            'notas_coach' => $this->buildNotasCoach($ctx),
            'tips' => array_merge($this->buildTips(), $extraTips),
            'principios_aplicados' => $injectedPrinciples->pluck('slug')->toArray(),
        ];

        return new ComposeResult(
            planJson: $planJson,
            warnings: $warnings,
            durationMs: (microtime(true) - $start) * 1000,
        );
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    private function buildHabitos(ClientProfile $profile): array
    {
        $weightKg = $profile->weightKg ?? 70.0;
        $hidratacionLitros = round($weightKg * 0.035, 1);

        $habitos = [
            [
                'nombre' => 'Sueño 7-9h consistente',
                'categoria' => 'sueño',
                'objetivo' => '7.5 horas promedio semanal · mismo horario ±30 min entre semana y fin de semana',
                'tracking_method' => 'app WellCore — campo horas_sueño (registrar cada mañana)',
                'por_que_importa' => 'El pico de hormona de crecimiento (GH) y la recuperación muscular ocurren en sueño profundo. Dormir <6h reduce ~50% de las ganancias de entrenamiento.',
                'tips' => [
                    'Sin pantallas 1 hora antes de dormir',
                    'Habitación fresca (18-20°C) y oscura',
                    'Si trabajás de noche, hablalo con el coach para ajustar el plan',
                ],
            ],
            [
                'nombre' => "Hidratación mínima {$hidratacionLitros} L/día",
                'categoria' => 'hidratacion',
                'objetivo' => "{$hidratacionLitros} L diarios base + 500 ml por hora de entrenamiento",
                'tracking_method' => 'Botella de 1L visible — meta de N botellas/día',
                'por_que_importa' => 'Deshidratación leve (-2% peso corporal en líquidos) reduce fuerza, resistencia y enfoque cognitivo. Cuando sentís sed ya estás deshidratado.',
                'tips' => [
                    "Tu mínimo: peso × 0.035 = {$hidratacionLitros} L (peso aproximado " . round($weightKg) . " kg)",
                    'Sumá 500 ml extra los días de entreno (durante + post)',
                    'Café y té cuentan parcialmente (60%), bebidas con azúcar no',
                ],
            ],
            [
                'nombre' => 'Registro de entrenamiento',
                'categoria' => 'registro',
                'objetivo' => 'Anotar peso, reps y RIR de cada serie ANTES de salir del gym',
                'tracking_method' => 'app WellCore — registro post-ejercicio en tiempo real',
                'por_que_importa' => 'Sin registro, no hay sobrecarga progresiva real, solo recuerdo selectivo. El que anota sabe exactamente cuándo subir, cuándo está estancado, cuándo deload.',
                'tips' => [
                    'Anotá en el momento, no al final del día',
                    '80% de adherencia sostenida vale más que 100% del primer mes',
                    'Si la app falla, libreta funciona igual — lo importante es la consistencia',
                ],
            ],
            [
                'nombre' => 'Check-in semanal',
                'categoria' => 'tracking',
                'objetivo' => 'Peso (ayunas, 1× semana) + medidas (cintura/cadera, 1× semana) + 2 fotos (frente/lateral)',
                'tracking_method' => 'app WellCore — sección Check-in los domingos en la mañana',
                'por_que_importa' => 'El peso diario tiene mucha varianza (agua, comida). El promedio semanal es la métrica real. Las fotos detectan cambios que el peso no.',
                'tips' => [
                    'Mismo día y hora cada semana (domingos en ayunas funciona)',
                    'Mismas condiciones para las fotos (luz, ángulo, ropa)',
                    'No mires el peso de la balanza diariamente — eso aumenta ansiedad sin info útil',
                ],
            ],
        ];

        // Hábito 5 condicional: solo para mujeres (tracking de ciclo).
        $isFemenino = $this->isFemenino($profile->gender);
        if ($isFemenino) {
            $habitos[] = [
                'nombre' => 'Tracking del ciclo menstrual',
                'categoria' => 'ciclo',
                'objetivo' => 'Registrar día 1 del ciclo + duración promedio + síntomas relevantes',
                'tracking_method' => 'app WellCore — sección Ciclo o app dedicada (Flo, Clue)',
                'por_que_importa' => 'El ciclo modula recuperación, fuerza y respuesta a déficit calórico. Conocer en qué fase estás permite ajustar el entreno (más volumen folicular, más recuperación lútea).',
                'tips' => [
                    'No es para "explicar" malos días — es info para ajustar carga',
                    'Si el ciclo se interrumpe o cambia drásticamente, avisá al coach',
                    'La fase lútea tardía puede pedir más calorías; está bien',
                ],
            ];
        }

        return $habitos;
    }

    private function buildTitle(ComposeContext $ctx): string
    {
        $base = "Plan de hábitos — {$ctx->methodology->name}";
        return $ctx->clientName !== null ? "{$base} — {$ctx->clientName}" : $base;
    }

    private function buildNotasCoach(ComposeContext $ctx): string
    {
        $coach = $ctx->coachName ?? 'tu coach';
        return "Los hábitos son la base de todo. El plan de entreno y nutrición rinde 3× cuando estos pilares están sostenidos. No tenés que cumplir el 100% — apuntá a 80% sostenido durante las 4 semanas y el resultado se nota. — $coach";
    }

    /**
     * @return string[]
     */
    private function buildTips(): array
    {
        return [
            'Empezá por el hábito que más te cueste sostener — ese es el que más valor agrega',
            'Si fallás un día, retomá al siguiente — no compenses con extra esfuerzo (eso desgasta)',
            '4 semanas es suficiente para que el hábito automatice — no necesitás motivación constante',
            'Si un hábito no te encaja, hablalo con el coach antes de abandonarlo',
        ];
    }

    private function isFemenino(?string $g): bool
    {
        if ($g === null) {
            return false;
        }
        return in_array(strtolower($g), ['f', 'femenino', 'female', 'mujer'], true);
    }
}
