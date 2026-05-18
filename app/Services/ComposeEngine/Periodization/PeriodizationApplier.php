<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Periodization;

/**
 * Expande el `periodization_pattern` de una metodología en una lista de fases por semana.
 *
 * Input: [
 *   ['weeks' => 1, 'fase' => 'Adaptación', 'rir_objetivo' => 3, 'volumen_pct' => 70],
 *   ['weeks' => 1, 'fase' => 'Hipertrofia', 'rir_objetivo' => 2, 'volumen_pct' => 100],
 *   ...
 * ]
 *
 * Output (cada semana): [
 *   ['fase' => 'Adaptación', 'rir' => 3, 'volumen_pct' => 70],
 *   ['fase' => 'Hipertrofia', 'rir' => 2, 'volumen_pct' => 100],
 *   ...
 * ]
 *
 * Si pattern tiene menos semanas que duracion_semanas, repite el último bloque.
 * Si tiene más, trunca al fin.
 */
final class PeriodizationApplier
{
    /**
     * @param array<int, array<string,mixed>> $pattern
     * @return array<int, array{fase: string, rir: int, volumen_pct: int}>
     */
    public function expand(array $pattern, int $duracionSemanas): array
    {
        $weeks = [];

        foreach ($pattern as $block) {
            $blockWeeks = (int) ($block['weeks'] ?? 1);
            $fase = (string) ($block['fase'] ?? 'Hipertrofia');
            $rir = (int) ($block['rir_objetivo'] ?? 2);
            $volumenPct = (int) ($block['volumen_pct'] ?? 100);

            for ($i = 0; $i < $blockWeeks; $i++) {
                $weeks[] = [
                    'fase' => $fase,
                    'rir' => $rir,
                    'volumen_pct' => $volumenPct,
                ];
            }
        }

        // Padding con el último bloque si faltan semanas.
        if (count($weeks) < $duracionSemanas && $weeks !== []) {
            $last = end($weeks);
            while (count($weeks) < $duracionSemanas) {
                $weeks[] = $last;
            }
        }

        // Truncar si sobran.
        return array_slice($weeks, 0, $duracionSemanas);
    }

    /**
     * Series/reps recomendadas para una fase + nivel de cliente.
     *
     * @return array{series: int, reps: string, descanso: string}
     */
    public function setRepsForPhase(string $fase, string $level = 'intermedio'): array
    {
        $base = match ($fase) {
            'Adaptación' => ['series' => 3, 'reps' => '12', 'descanso' => '90s'],
            'Hipertrofia' => ['series' => 4, 'reps' => '10', 'descanso' => '90s'],
            'Fuerza' => ['series' => 4, 'reps' => '6-8', 'descanso' => '150s'],
            'Fuerza Máxima' => ['series' => 5, 'reps' => '5', 'descanso' => '180s'],
            'Peak' => ['series' => 5, 'reps' => '3-5', 'descanso' => '180s'],
            'Deload' => ['series' => 2, 'reps' => '8', 'descanso' => '90s'],
            'Recuperación' => ['series' => 2, 'reps' => '10', 'descanso' => '60s'],
            'Preparación' => ['series' => 3, 'reps' => '10', 'descanso' => '90s'],
            'Mantenimiento' => ['series' => 3, 'reps' => '10', 'descanso' => '90s'],
            default => ['series' => 3, 'reps' => '10', 'descanso' => '90s'],
        };

        // Ajuste para principiante: menos series.
        if ($level === 'principiante' && $base['series'] > 3) {
            $base['series'] = 3;
        }

        return $base;
    }

    /**
     * Variación para ejercicios isolation: aporta diversidad de carga vs los compounds
     * dentro de la misma fase (evita warning heur_monotonia_3x12).
     *
     * Reglas fisiológicas:
     *   - Isolations toleran reps más altas con descanso más corto.
     *   - Series generalmente igual o -1 vs compound.
     *
     * @return array{series: int, reps: string, descanso: string}
     */
    public function setRepsForPhaseIsolation(string $fase, string $level = 'intermedio'): array
    {
        // Usamos rangos en isolations para diferenciarlos de compounds (que usan
        // valor fijo). Garantiza combinación única por fase × tipo y evita
        // colisiones cross-fase (heur_monotonia_3x12).
        $variants = match ($fase) {
            'Adaptación' => ['series' => 3, 'reps' => '12-15', 'descanso' => '60s'],
            'Hipertrofia' => ['series' => 3, 'reps' => '10-12', 'descanso' => '75s'],
            'Fuerza' => ['series' => 3, 'reps' => '8-10', 'descanso' => '90s'],
            'Fuerza Máxima' => ['series' => 4, 'reps' => '6-8', 'descanso' => '90s'],
            'Peak' => ['series' => 4, 'reps' => '8-10', 'descanso' => '75s'],
            'Deload' => ['series' => 2, 'reps' => '10-12', 'descanso' => '60s'],
            'Recuperación' => ['series' => 2, 'reps' => '12-15', 'descanso' => '45s'],
            'Preparación' => ['series' => 3, 'reps' => '10-12', 'descanso' => '60s'],
            'Mantenimiento' => ['series' => 3, 'reps' => '10-12', 'descanso' => '75s'],
            default => ['series' => 3, 'reps' => '10-12', 'descanso' => '75s'],
        };

        if ($level === 'principiante' && $variants['series'] > 3) {
            $variants['series'] = 3;
        }

        return $variants;
    }

    /**
     * Técnica de intensificación a aplicar según fase + posición del ejercicio.
     *
     * Reglas (MD 08-METODOLOGIAS §229-241):
     *   - Solo al ÚLTIMO ejercicio del grupo (cierre).
     *   - Drop set: hipertrofia, peak
     *   - Rest-pause: fuerza, hipertrofia (avanzado)
     *   - Cluster sets: fuerza máxima, peak
     *   - Isometría: core only (descartado acá)
     *   - Series rectas (default): adaptación, deload, recuperación
     *
     * @return array{nombre: string, descripcion: string}|null
     */
    public function intensificationFor(
        string $fase,
        int $position,
        int $totalInDay,
        bool $isCompound,
    ): ?array {
        // Solo al cierre del día — no aplicar a compounds principales.
        $isLastOfDay = $position === $totalInDay - 1;
        if (! $isLastOfDay || $totalInDay < 3) {
            return null;
        }
        // Compounds no llevan dropset (riesgo lesión); el cierre es típicamente isolation.
        if ($isCompound) {
            return null;
        }

        return match ($fase) {
            'Hipertrofia' => [
                'nombre' => 'Drop Set',
                'descripcion' => 'En la última serie: bajá ~20-30% el peso y seguí hasta el fallo técnico. Sin descanso entre la serie principal y el drop.',
            ],
            'Peak' => [
                'nombre' => 'Rest-Pause',
                'descripcion' => 'Última serie al RIR 0. Descansá 15s y hacé 3-5 reps más. Repetí 2× total. Acumulás reps sin agregar más series.',
            ],
            'Fuerza Máxima', 'Fuerza' => [
                'nombre' => 'Cluster Set',
                'descripcion' => 'Última serie: hacé 2 reps, descansá 20s, 2 reps más, 20s, 2 reps más. Carga alta con técnica controlada.',
            ],
            default => null,
        };
    }
}
