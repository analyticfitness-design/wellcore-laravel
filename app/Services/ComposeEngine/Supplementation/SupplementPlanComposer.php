<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Supplementation;

use App\Services\ComposeEngine\Data\ComposeContext;
use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\ComposeEngine\Principles\PrincipleInjector;

/**
 * Compose stage para vertical=suplementacion (Sprint 13).
 *
 * Pipeline:
 *   1. StackSelector → escoge un supplement_stack basado en profile.
 *   2. Itera components_essential + components_recommended (skip optional para mantener
 *      el stack más enfocado; el coach puede agregar manualmente).
 *   3. Mapea timing/frequency keywords a strings humanos (canónicos LATAM).
 *   4. Produce JSON con shape `suplementos[]` compatible con lint rule
 *      `schema_supl_missing_array` (sin `_g` en keys; `momento` en vez de `timing`).
 */
final class SupplementPlanComposer
{
    private const TIMING_LABELS = [
        'post_entreno_inmediato' => 'Inmediatamente post-entreno (dentro de 30 min)',
        'pre_entreno_30_45_min' => '30-45 minutos pre-entreno',
        'pre_entreno_inmediato' => 'Inmediatamente pre-entreno',
        'cualquier_momento_consistente' => 'Cualquier momento del día (siempre a la misma hora)',
        'con_la_cena' => 'Con la cena',
        'con_el_desayuno' => 'Con el desayuno',
        'con_la_primera_comida' => 'Con la primera comida del día',
        'con_comida_principal' => 'Con la comida principal',
        'antes_de_dormir' => 'Antes de dormir (30 min antes)',
        'al_despertar' => 'Al despertar, en ayunas',
        'intra_entreno' => 'Durante el entreno (intra-workout)',
        'mañana_y_tarde' => 'Una toma en la mañana y otra en la tarde',
    ];

    private const FREQUENCY_LABELS = [
        'diaria_solo_dias_entreno' => 'Solo días de entrenamiento',
        'diaria_incluidos_dias_descanso' => 'Diaria, incluyendo días de descanso',
        'diaria_todos_los_dias' => 'Diaria, todos los días',
        'tres_a_cinco_veces_semana' => '3-5 veces por semana',
        'segun_demanda' => 'Según necesidad',
        'ciclo_8_semanas_pausa_4' => 'Ciclo de 8 semanas + 4 semanas de pausa',
    ];

    public function __construct(
        private readonly StackSelector $selector,
        private readonly PrincipleInjector $principleInjector,
    ) {
    }

    public function compose(ComposeContext $ctx): ComposeResult
    {
        $start = microtime(true);
        $warnings = [];

        $stack = $this->selector->selectFor($ctx->profile);
        if ($stack === null) {
            // Sin stack disponible — produce JSON mínimo válido con 1 suplemento basal.
            $warnings[] = 'No se encontró stack apropiado — produciendo stack mínimo básico.';
            $suplementos = [$this->fallbackBasico()];
        } else {
            $essential = $stack->components_essential ?? [];
            $recommended = $stack->components_recommended ?? [];
            $components = array_merge($essential, $recommended);

            if ($components === []) {
                $warnings[] = "Stack '{$stack->slug}' no tiene components_essential ni _recommended.";
                $suplementos = [$this->fallbackBasico()];
            } else {
                $suplementos = array_map(fn ($c) => $this->buildSupplementEntry($c), $components);
            }
        }

        $stackInfo = $stack !== null ? [
            'stack_slug' => $stack->slug,
            'stack_nombre' => $stack->name,
            'costo_mensual_estimado_cop' => $stack->approximate_monthly_cost_cop,
            'costo_mensual_rango_cop' => $stack->approximate_monthly_cost_range_cop,
        ] : null;

        // Sprint 34: inyectar principles relevantes
        $injectedPrinciples = $this->principleInjector->selectTop($ctx->profile, 'suplementacion', limit: 3);
        $extraTips = $this->principleInjector->asTipsArray($injectedPrinciples);

        $planJson = [
            'plan_type' => 'suplementacion',
            'titulo' => $this->buildTitle($ctx, $stack),
            'objetivo' => $this->buildObjetivo($ctx, $stack),
            'metodologia' => (string) $ctx->methodology->name,
            'duracion_semanas' => 4,
            'fecha_inicio' => $ctx->fechaInicio,
            'stack_info' => $stackInfo,
            'notas_coach' => $this->buildNotasCoach($ctx, $stack),
            'tips' => array_merge($this->buildTips(), $extraTips),
            'principios_aplicados' => $injectedPrinciples->pluck('slug')->toArray(),
            'suplementos' => $suplementos,
            'advertencia_legal' => $stack->legal_advertencia ?? 'Estos suplementos son ayudas ergogénicas, no medicamentos. Consultá con tu médico si tomás otros tratamientos. NO sustituyen una alimentación adecuada.',
        ];

        return new ComposeResult(
            planJson: $planJson,
            warnings: $warnings,
            durationMs: (microtime(true) - $start) * 1000,
        );
    }

    /**
     * @param array<string,mixed> $component
     */
    private function buildSupplementEntry(array $component): array
    {
        $slug = (string) ($component['supplement_slug'] ?? '');
        $dosis = (string) ($component['dosis_recommended'] ?? '');
        $timingKey = (string) ($component['timing'] ?? '');
        $frequencyKey = (string) ($component['frequency'] ?? '');
        $rationale = (string) ($component['rationale'] ?? '');

        return [
            'nombre' => $this->humanizeSlug($slug),
            'slug' => $slug,
            'dosis' => $dosis,
            'momento' => self::TIMING_LABELS[$timingKey] ?? $this->humanizeKey($timingKey),
            'frecuencia' => self::FREQUENCY_LABELS[$frequencyKey] ?? $this->humanizeKey($frequencyKey),
            'notas' => $rationale,
        ];
    }

    private function fallbackBasico(): array
    {
        return [
            'nombre' => 'Proteína Whey concentrada',
            'slug' => 'proteina-whey-concentrada',
            'dosis' => '1 scoop (25-30g, ~22g proteína neta) post-entreno',
            'momento' => self::TIMING_LABELS['post_entreno_inmediato'],
            'frecuencia' => self::FREQUENCY_LABELS['diaria_solo_dias_entreno'],
            'notas' => 'Asegura el target proteico diario cuando los alimentos no llegan.',
        ];
    }

    private function buildTitle(ComposeContext $ctx, ?\App\Models\Kb\SupplementStack $stack): string
    {
        $stackName = $stack?->name ?? 'Stack básico';
        $base = "Stack de suplementación — {$stackName}";
        return $ctx->clientName !== null ? "{$base} — {$ctx->clientName}" : $base;
    }

    private function buildObjetivo(ComposeContext $ctx, ?\App\Models\Kb\SupplementStack $stack): string
    {
        $obj = $stack?->objective;
        if ($obj !== null && $obj !== '') {
            return $obj;
        }

        return match ($ctx->profile->goal) {
            'perdida_grasa' => 'Apoyar la pérdida de grasa con suplementos basales (proteína, creatina, multivitamínico).',
            'hipertrofia' => 'Apoyar la ganancia de masa muscular con suplementos basales + ergogénicos validados.',
            'recomposicion' => 'Apoyar la recomposición corporal (perder grasa preservando músculo).',
            default => 'Soporte basal con suplementos de alta evidencia científica.',
        };
    }

    private function buildNotasCoach(ComposeContext $ctx, ?\App\Models\Kb\SupplementStack $stack): string
    {
        $coach = $ctx->coachName ?? 'tu coach';
        $base = "El stack está pensado para tu objetivo y nivel actual. Tomá los suplementos en el momento y frecuencia indicados — la consistencia importa más que la dosis exacta.";

        if ($stack !== null && $stack->approximate_monthly_cost_cop !== null) {
            $costo = number_format($stack->approximate_monthly_cost_cop, 0, ',', '.');
            $base .= " El costo mensual aproximado es de COP \${$costo}.";
        }

        return $base . " — $coach";
    }

    /**
     * @return string[]
     */
    private function buildTips(): array
    {
        return [
            'La creatina NO necesita fase de carga — 5g diarios desde el día 1 son suficientes',
            'La proteína whey es opcional si llegás a tu target diario con alimentos enteros',
            'Si te saltás un día de creatina, no pasa nada — el efecto es por saturación acumulada',
            'NO mezcles más de un pre-entreno con cafeína al día (riesgo cardiovascular)',
            'Suspendé cualquier suplemento si aparecen síntomas adversos y avisá al coach',
        ];
    }

    private function humanizeSlug(string $slug): string
    {
        $clean = str_replace('-', ' ', $slug);
        return ucfirst($clean);
    }

    private function humanizeKey(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
