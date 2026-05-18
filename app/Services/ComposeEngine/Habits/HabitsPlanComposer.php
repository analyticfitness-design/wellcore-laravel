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
            'objetivo' => 'Vamos a fijar los hábitos que sostienen todo lo demás: dormir bien, tomar agua, anotar lo que hacés en el gym. Sin esto, el plan de entreno y nutrición no rinde.',
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
                'id' => 'sueno',
                'nombre' => 'Dormir entre 7 y 9 horas',
                'categoria' => 'sueño',
                'objetivo_diario' => '7.5 horas promedio · mismo horario entre semana y fin de semana (±30 min)',
                'objetivo' => '7.5 horas promedio · mismo horario entre semana y fin de semana (±30 min)',
                'tracking_method' => 'Registrá tus horas de sueño cada mañana en la app',
                'por_que_importa' => 'Mientras dormís profundo, el cuerpo libera la hormona que reconstruye el músculo. Dormís menos de 6 horas, perdés hasta la mitad de lo que ganaste en el gym.',
                'tips' => [
                    'Apagá las pantallas una hora antes de dormir',
                    'Habitación fresca (entre 18 y 20°C) y oscura',
                    'Si trabajás de noche, escribime y vemos cómo ajustamos esto',
                ],
            ],
            [
                'id' => 'agua',
                'nombre' => "Tomar {$hidratacionLitros} L de agua al día",
                'categoria' => 'hidratacion',
                'objetivo_diario' => "{$hidratacionLitros} L diarios + 500 ml extra por hora de entreno",
                'objetivo' => "{$hidratacionLitros} L diarios + 500 ml extra por hora de entreno",
                'tracking_method' => 'Una botella de 1L a la vista — apuntá a vaciarla N veces al día',
                'por_que_importa' => 'Si perdés apenas 2% de tu peso en agua, ya te baja la fuerza, el aguante y la concentración. Cuando sentís sed, ya estás corto.',
                'tips' => [
                    "Tu mínimo te lo calculo así: tu peso (" . round($weightKg) . " kg) × 0.035 = {$hidratacionLitros} L",
                    'Los días que entrenás, sumá 500 ml extra (durante y después)',
                    'Café y té cuentan parcialmente (60%); bebidas con azúcar no cuentan',
                ],
            ],
            [
                'id' => 'entrenamiento',
                'nombre' => 'Anotar cada serie del entreno',
                'categoria' => 'registro',
                'objetivo_diario' => 'Peso, reps y RIR de cada serie, antes de salir del gym',
                'objetivo' => 'Peso, reps y RIR de cada serie, antes de salir del gym',
                'tracking_method' => 'Registralo en la app apenas terminás cada ejercicio',
                'por_que_importa' => 'Si no anotás, no podés saber cuándo subir peso, cuándo te estancaste o cuándo te toca una semana más liviana. La memoria juega malas pasadas — lo escrito gana.',
                'tips' => [
                    'Anotá en el momento, no al final del día',
                    'Es mejor que lo hagas el 80% siempre y no el 100% solo los primeros días',
                    'Si la app falla, libreta de papel funciona igual — lo importante es que anotes, no en qué',
                ],
            ],
            [
                'id' => 'nutricion',
                'nombre' => 'Check-in semanal',
                'categoria' => 'tracking',
                'objetivo_diario' => 'Pesate una vez por semana en ayunas + medidas cintura/cadera + 2 fotos (frente y costado)',
                'objetivo' => 'Pesate una vez por semana en ayunas + medidas cintura/cadera + 2 fotos (frente y costado)',
                'tracking_method' => 'En la app, sección Check-in. Domingos en la mañana funciona bien',
                'por_que_importa' => 'El peso diario varía mucho (agua, comida, hora del día). Lo que importa es el promedio de la semana. Las fotos te muestran cambios que la balanza esconde.',
                'tips' => [
                    'Mismo día y hora cada semana (domingos en ayunas funciona bien)',
                    'Mismas condiciones para las fotos (luz, ángulo, ropa)',
                    'No te peses todos los días — solo te genera ansiedad sin darte información que sirva',
                ],
            ],
        ];

        // Hábito condicional: tracking del ciclo (solo para mujeres).
        $isFemenino = $this->isFemenino($profile->gender);
        if ($isFemenino) {
            $habitos[] = [
                'id' => 'suplementos',
                'nombre' => 'Tracking del ciclo menstrual',
                'categoria' => 'ciclo',
                'objetivo_diario' => 'Día 1 del ciclo + duración promedio + síntomas relevantes',
                'objetivo' => 'Día 1 del ciclo + duración promedio + síntomas relevantes',
                'tracking_method' => 'En la app sección Ciclo, o en una app dedicada como Flo o Clue',
                'por_que_importa' => 'Tu ciclo afecta cómo te recuperás, qué tan fuerte estás y cómo respondés a comer menos. Saber en qué fase estás te ayuda a ajustar: en la primera mitad del ciclo podés meterle más; en la segunda, priorizá recuperación.',
                'tips' => [
                    'No es para justificar días malos — es información para acomodar tu entreno',
                    'Si el ciclo se interrumpe o cambia mucho, escribime',
                    'Los últimos 5-7 días antes del periodo, tu cuerpo te pide más calorías — eso es normal',
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
        $coach = $this->resolveFirstName($ctx->coachName) ?: 'tu coach';
        $p1 = 'Los hábitos son la base de todo. Con esto firme, el resto del plan rinde el triple. Sin esto, el mejor entreno y la mejor nutrición no sirven.';
        $p2 = 'No te pongas la meta del 100% — apuntá al 80% todas las semanas y vas a ver el cambio. Si fallás un día, retomá al siguiente. No compensés con esfuerzo extra (eso desgasta).';
        $p3 = "Si algo no te encaja con tus tiempos o tu situación, escribime y lo ajustamos. — {$coach}";
        return implode("\n\n", [$p1, $p2, $p3]);
    }

    /**
     * @return string[]
     */
    private function buildTips(): array
    {
        return [
            'Arrancá por el hábito que más te cuesta — ese es el que más te va a mover la aguja',
            'Si fallás un día, retomá al siguiente. No compensés con esfuerzo extra (eso desgasta)',
            'En 4 semanas el hábito se te vuelve automático y ya no necesitás estar motivada todos los días',
            'Si un hábito no te encaja, escribime antes de abandonarlo',
        ];
    }

    private function resolveFirstName(?string $fullName): string
    {
        if ($fullName === null || trim($fullName) === '') {
            return '';
        }
        $parts = explode(' ', trim($fullName));
        return $parts[0];
    }

    private function isFemenino(?string $g): bool
    {
        if ($g === null) {
            return false;
        }
        return in_array(strtolower($g), ['f', 'femenino', 'female', 'mujer'], true);
    }
}
