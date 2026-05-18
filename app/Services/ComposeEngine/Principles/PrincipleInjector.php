<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Principles;

use App\Models\Kb\Principle;
use App\Services\DecisionEngine\Data\ClientProfile;
use Illuminate\Support\Collection;

/**
 * Selecciona principles relevantes para el contexto y los formatea para
 * inyectar en `notas_coach` y `tips[]` de un plan.
 *
 * Algoritmo:
 *   1. Filtra principles por vertical (entrenamiento/nutricion/suplementacion/habitos)
 *      + el principio universal 'fundamental' tag matchea todo.
 *   2. Scoring por relevancia:
 *      - Vertical match: +10
 *      - Tags overlap con context: +5 cada tag (level, goal, principiante, lesion, etc.)
 *      - 'fundamental' tag: +3 (siempre aplica)
 *   3. Devuelve top-3 con score más alto.
 *
 * Output:
 *   - asTipsArray() → array de strings para `tips[]`
 *   - asInlineNotes() → string apendible a `notas_coach`
 *
 * Determinismo: ordena por score DESC, tiebreak por id ASC.
 */
final class PrincipleInjector
{
    public function selectTop(ClientProfile $profile, string $vertical, int $limit = 3): Collection
    {
        $candidates = Principle::query()->active()->get();

        $scored = $candidates->map(function (Principle $p) use ($profile, $vertical) {
            $score = 0;

            // Vertical match — bonus alto para asegurar que los principles del
            // vertical relevante siempre aparezcan en top.
            if ($p->vertical === $vertical) {
                $score += 20;
            }

            // Tags overlap con context (level/goal/etc.)
            $tags = $p->tags ?? [];
            $contextTags = $this->buildContextTags($profile);
            $overlap = count(array_intersect($tags, $contextTags));
            $score += $overlap * 5;

            // Sprint 49: 'fundamental' tag tiene boost mucho más fuerte (+10 vs +3).
            // Razón: principles fundamentales son por definición universales y deben
            // aparecer en todos los planes (telemetría Sprint 48 mostró 11 unused fundamentales).
            if (in_array('fundamental', $tags, true)) {
                $score += 10;
            }

            // Sprint 49: tags universales que cruzan verticales (adherencia, prevencion)
            // reciben mini-boost para diversificar las inyecciones.
            $universalTags = ['adherencia', 'prevencion', 'prevencion_lesiones', 'realismo', 'seguridad'];
            $universalOverlap = count(array_intersect($tags, $universalTags));
            $score += $universalOverlap * 2;

            return ['principle' => $p, 'score' => $score];
        })
            ->sortBy(function ($item) {
                // Sprint 58: sort multi-key. Score DESC (primary).
                // Tiebreak: evidence_level (muy_alta > alta > moderada > limitada > anecdotica).
                $evidenceRank = match ($item['principle']->evidence_level ?? 'alta') {
                    'muy_alta' => 5,
                    'alta' => 4,
                    'moderada' => 3,
                    'limitada' => 2,
                    'anecdotica' => 1,
                    default => 4,
                };
                // sortBy ASC: negamos para invertir y obtener DESC efectivo.
                return [-$item['score'], -$evidenceRank];
            })
            ->take($limit)
            ->pluck('principle');

        return $scored->values();
    }

    /**
     * @return string[] Tips formateados como bullets para tips[] del plan.
     */
    public function asTipsArray(Collection $principles): array
    {
        return $principles
            ->map(fn (Principle $p) => $this->formatAsTip($p))
            ->toArray();
    }

    /**
     * Texto inline para apendir al notas_coach. Una frase por principle.
     */
    public function asInlineNotes(Collection $principles): string
    {
        return $principles
            ->map(fn (Principle $p) => "{$p->name}: {$p->description_short}")
            ->implode(' · ');
    }

    private function formatAsTip(Principle $p): string
    {
        return $p->description_short;
    }

    /**
     * Tags del contexto del cliente (level, goal, gender, condiciones especiales).
     * @return string[]
     */
    private function buildContextTags(ClientProfile $profile): array
    {
        $tags = [];

        if ($profile->level !== null) {
            $tags[] = $profile->level; // principiante / intermedio / avanzado
        }
        if ($profile->goal !== null) {
            $tags[] = $profile->goal; // hipertrofia / perdida_grasa / etc.
        }
        if ($profile->injuries !== []) {
            $tags[] = 'lesion';
            $tags[] = 'rehabilitacion';
            $tags[] = 'prevencion_lesiones';
        }
        // tags universales que aplican a casi todo
        $tags[] = 'macros';
        $tags[] = 'timing';
        $tags[] = 'recuperacion';
        $tags[] = 'adherencia';
        $tags[] = 'progresion';

        return array_values(array_unique($tags));
    }
}
