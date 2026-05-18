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

        // Override del coach: si preferences.supplements_override está presente, respetar EXACTO.
        // - lista vacía → cero suplementos (motor no prescribe)
        // - lista con nombres → filtrar/agregar para que el output matchee la prescripción del coach
        $prefs = $ctx->profile->preferences ?? [];
        if (array_key_exists('supplements_override', $prefs)) {
            $coachList = $prefs['supplements_override'];
            $result = $this->applyCoachOverride($coachList, $suplementos, $warnings);
            $suplementos = $result['suplementos'];
            $warnings = $result['warnings'];
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
            'advertencia_legal' => $stack->legal_advertencia ?? 'Estos suplementos son ayudas, no medicamentos. Si estás en tratamiento médico, consultá con tu doctor antes de empezar. No reemplazan una alimentación bien hecha.',
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

    /**
     * Aplica el override del coach: respeta literalmente la lista que el coach prescribió.
     *
     * Política:
     *   - lista vacía → motor no prescribe nada (warning informativo)
     *   - cada nombre del coach se busca tolerantemente en el stack del motor
     *     (LIKE sin acentos/case). Si matchea → keep esa entry rica.
     *   - si no matchea ningún component del stack → entry custom con placeholders
     *     "consultar dosis con coach" + warning indicando que es prescripción no canónica.
     *
     * @param array<int,string> $coachList nombres que el coach prescribió
     * @param array<int,array<string,mixed>> $motorList el stack que el motor produjo
     * @param array<int,string> $warnings array existente, se appendean nuevos
     * @return array{suplementos: array<int,array<string,mixed>>, warnings: array<int,string>}
     */
    private function applyCoachOverride(array $coachList, array $motorList, array $warnings): array
    {
        if ($coachList === []) {
            $warnings[] = 'Coach pidió suplementación vacía (supplements_override=[]). Motor NO prescribe nada.';
            return ['suplementos' => [], 'warnings' => $warnings];
        }

        $resolved = [];
        $unmatched = [];

        foreach ($coachList as $coachName) {
            $needle = $this->normalizeName((string) $coachName);
            if ($needle === '') {
                continue;
            }

            $found = null;
            foreach ($motorList as $entry) {
                $candidate = $this->normalizeName((string) ($entry['nombre'] ?? ''));
                $slug = $this->normalizeName((string) ($entry['slug'] ?? ''));
                if (str_contains($candidate, $needle) || str_contains($slug, $needle)
                    || str_contains($needle, $candidate) || str_contains($needle, $slug)) {
                    $found = $entry;
                    break;
                }
            }

            if ($found !== null) {
                $resolved[] = $found;
            } else {
                $unmatched[] = $coachName;
                $resolved[] = [
                    'nombre' => $coachName,
                    'slug' => $this->slugify($coachName),
                    'dosis' => 'Consultá dosis con tu coach',
                    'momento' => 'Consultá momento con tu coach',
                    'frecuencia' => 'Según prescripción del coach',
                    'notas' => 'Te lo recomendé personalmente. Confirmame dosis y marca antes de comprarlo, así nos aseguramos.',
                ];
            }
        }

        if ($unmatched !== []) {
            $warnings[] = 'Coach prescribió suplementos que no matchean catálogo canónico: '
                . implode(', ', $unmatched)
                . '. Incluidos como entries custom — confirmá evidencia y dosis con el coach.';
        }

        return ['suplementos' => $resolved, 'warnings' => $warnings];
    }

    /**
     * Normaliza para matching: lower + sin tildes + sin separadores.
     */
    private function normalizeName(string $raw): string
    {
        $s = mb_strtolower(trim($raw), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);
        return (string) preg_replace('/[^a-z0-9]/u', '', $s);
    }

    private function slugify(string $raw): string
    {
        $s = mb_strtolower(trim($raw), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
        $s = (string) preg_replace('/[^a-z0-9]+/u', '-', $s);
        return trim($s, '-');
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
            'perdida_grasa' => 'Suplementos que te ayudan a bajar grasa sin perder músculo — proteína, creatina, multivitamínico.',
            'hipertrofia' => 'Suplementos para ganar masa muscular — proteína, creatina, y los que tienen ciencia detrás para apoyar el entreno.',
            'recomposicion' => 'Suplementos que apoyan bajar grasa y mantener músculo al mismo tiempo.',
            default => 'Suplementos básicos con respaldo científico para apoyar tu plan.',
        };
    }

    private function buildNotasCoach(ComposeContext $ctx, ?\App\Models\Kb\SupplementStack $stack): string
    {
        $coach = $this->resolveFirstName($ctx->coachName) ?: 'tu coach';
        $p1 = 'Tomá cada suplemento en el momento que te marco — el cuándo importa tanto como el qué. Y constancia: mejor que los tomes el 80% del mes y no que los tomes a tope la primera semana y los abandones.';
        $p2 = 'Si tenés algo de riñones, hígado, presión, o estás embarazada, parame ahí y hablamos antes de que compres nada.';

        $costoNota = '';
        if ($stack !== null && $stack->approximate_monthly_cost_cop !== null) {
            $costo = number_format($stack->approximate_monthly_cost_cop, 0, ',', '.');
            $costoNota = " El costo mensual aproximado es de COP \${$costo} (referencial, varía 2-3× por marca y país).";
        }

        $p3 = "Si no te alcanza para todos este mes, arrancá por los que te marqué como esenciales — los demás los sumás cuando puedas.{$costoNota} — {$coach}";

        return implode("\n\n", [$p1, $p2, $p3]);
    }

    /**
     * @return string[]
     */
    private function buildTips(): array
    {
        return [
            'La creatina no necesita "fase de carga" como dicen por ahí — 5g diarios desde el día 1 alcanzan',
            'La proteína whey es opcional si ya llegás a la proteína del día comiendo alimentos enteros',
            'Si te saltás un día de creatina, no pasa nada — el efecto se construye con el tiempo, no con una sola toma',
            'No mezclés más de un pre-entreno con cafeína al día — te puede tirar el corazón',
            'Si algún suplemento te cae mal o te sentís raro, parate y escribime',
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
