<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;

/**
 * plan:diff — compara 2 composed_plans y muestra qué cambió.
 *
 * Casos de uso:
 *   - Cliente tiene plan v1 y v2: ver qué se modificó entre versiones
 *   - Comparar planes de 2 clientes distintos (mismo vertical)
 *   - Detectar regresiones en el motor: mismo profile + 2 corridas → debería ser idéntico (determinismo)
 *
 * Algoritmo:
 *   1. Compara metadata (methodology, status, violations, performance)
 *   2. Compara fields top-level del planJson
 *   3. Para verticales con arrays (ejercicios/comidas/hábitos/suplementos), muestra:
 *      - items añadidos (en B, no en A)
 *      - items quitados (en A, no en B)
 *      - items modificados (mismo nombre, distintos atributos)
 *
 * Output: tabla "campo · antes · después" + listas added/removed por sección.
 */
final class PlanDiffCommand extends Command
{
    protected $signature = 'plan:diff
                            {a : composed_plans.id A (antes)}
                            {b : composed_plans.id B (después)}
                            {--json : output JSON estructurado}';

    protected $description = 'Compara 2 composed_plans y muestra qué cambió (metadata + contenido).';

    public function handle(): int
    {
        $idA = (int) $this->argument('a');
        $idB = (int) $this->argument('b');

        $a = ComposedPlan::find($idA);
        $b = ComposedPlan::find($idB);

        if (! $a) {
            $this->error("composed_plans #$idA no encontrado.");
            return 2;
        }
        if (! $b) {
            $this->error("composed_plans #$idB no encontrado.");
            return 2;
        }

        $planA = $a->planJson();
        $planB = $b->planJson();

        $diff = $this->buildDiff($a, $b, $planA, $planB);

        if ($this->option('json')) {
            $this->line(json_encode($diff, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->renderHuman($diff);
        }

        return $diff['identical'] ? 0 : 1;
    }

    private function buildDiff(ComposedPlan $a, ComposedPlan $b, array $planA, array $planB): array
    {
        $metadata = $this->compareMetadata($a, $b);
        $contentDiff = $this->compareContent($planA, $planB);

        return [
            'a_id' => $a->id,
            'b_id' => $b->id,
            'identical' => $metadata['identical'] && $contentDiff['identical'],
            'metadata_diff' => $metadata['diffs'],
            'content_diff' => $contentDiff,
        ];
    }

    private function compareMetadata(ComposedPlan $a, ComposedPlan $b): array
    {
        $fields = ['client_handle', 'plan_type', 'methodology_slug', 'status', 'violations_before', 'violations_after'];
        $diffs = [];
        foreach ($fields as $f) {
            if ($a->$f != $b->$f) {
                $diffs[$f] = ['a' => $a->$f, 'b' => $b->$f];
            }
        }
        return ['identical' => $diffs === [], 'diffs' => $diffs];
    }

    private function compareContent(array $a, array $b): array
    {
        $planType = $a['plan_type'] ?? $b['plan_type'] ?? null;

        $topLevelDiff = $this->topLevelDiff($a, $b);

        $sectionDiff = match ($planType) {
            'entrenamiento' => $this->diffEntrenamiento($a, $b),
            'nutricion' => $this->diffNutricion($a, $b),
            'suplementacion' => $this->diffSuplementacion($a, $b),
            'habitos' => $this->diffHabitos($a, $b),
            'ciclo' => $this->diffCiclo($a, $b),
            default => [],
        };

        $identical = $topLevelDiff === [] && $this->sectionsIdentical($sectionDiff);

        return [
            'identical' => $identical,
            'plan_type' => $planType,
            'top_level' => $topLevelDiff,
            'sections' => $sectionDiff,
        ];
    }

    /**
     * @return array<string, array{a: mixed, b: mixed}>
     */
    private function topLevelDiff(array $a, array $b): array
    {
        $skipKeys = ['semanas', 'comidas', 'suplementos', 'habitos', 'fases']; // array sections analizadas aparte
        $diffs = [];
        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
        foreach ($keys as $k) {
            if (in_array($k, $skipKeys, true)) {
                continue;
            }
            $vA = $a[$k] ?? null;
            $vB = $b[$k] ?? null;
            if (is_array($vA) || is_array($vB)) {
                if (json_encode($vA) !== json_encode($vB)) {
                    $diffs[$k] = ['a' => $vA, 'b' => $vB];
                }
                continue;
            }
            if ($vA != $vB) {
                $diffs[$k] = ['a' => $vA, 'b' => $vB];
            }
        }
        return $diffs;
    }

    private function diffEntrenamiento(array $a, array $b): array
    {
        $semanasA = $a['semanas'] ?? [];
        $semanasB = $b['semanas'] ?? [];

        $exercisesA = $this->collectAllExercises($semanasA);
        $exercisesB = $this->collectAllExercises($semanasB);

        $namesA = array_unique(array_column($exercisesA, 'nombre'));
        $namesB = array_unique(array_column($exercisesB, 'nombre'));

        return [
            'semanas_count' => ['a' => count($semanasA), 'b' => count($semanasB)],
            'ejercicios_unicos' => ['a' => count($namesA), 'b' => count($namesB)],
            'ejercicios_added' => array_values(array_diff($namesB, $namesA)),
            'ejercicios_removed' => array_values(array_diff($namesA, $namesB)),
        ];
    }

    private function collectAllExercises(array $semanas): array
    {
        $out = [];
        foreach ($semanas as $s) {
            foreach (($s['dias'] ?? []) as $d) {
                foreach (($d['ejercicios'] ?? []) as $ej) {
                    $out[] = $ej;
                }
            }
        }
        return $out;
    }

    private function diffNutricion(array $a, array $b): array
    {
        $comidasA = $a['comidas'] ?? [];
        $comidasB = $b['comidas'] ?? [];

        $foodsA = $this->collectAllFoods($comidasA);
        $foodsB = $this->collectAllFoods($comidasB);

        $macrosA = $a['macros'] ?? [];
        $macrosB = $b['macros'] ?? [];

        return [
            'objetivo_cal' => ['a' => $a['objetivo_cal'] ?? null, 'b' => $b['objetivo_cal'] ?? null],
            'macros' => ['a' => $macrosA, 'b' => $macrosB],
            'comidas_count' => ['a' => count($comidasA), 'b' => count($comidasB)],
            'foods_unicos' => ['a' => count(array_unique($foodsA)), 'b' => count(array_unique($foodsB))],
            'foods_added' => array_values(array_diff(array_unique($foodsB), array_unique($foodsA))),
            'foods_removed' => array_values(array_diff(array_unique($foodsA), array_unique($foodsB))),
        ];
    }

    private function collectAllFoods(array $comidas): array
    {
        $out = [];
        foreach ($comidas as $c) {
            foreach (['a', 'b', 'c'] as $k) {
                foreach (($c["opcion_$k"] ?? []) as $item) {
                    // Quita el portion (entre paréntesis) para comparar nombres
                    $name = preg_replace('/\s*\([^)]+\)\s*$/', '', $item);
                    $out[] = $name;
                }
            }
        }
        return $out;
    }

    private function diffSuplementacion(array $a, array $b): array
    {
        $supsA = array_column($a['suplementos'] ?? [], 'slug');
        $supsB = array_column($b['suplementos'] ?? [], 'slug');

        return [
            'stack_slug' => [
                'a' => $a['stack_info']['stack_slug'] ?? null,
                'b' => $b['stack_info']['stack_slug'] ?? null,
            ],
            'suplementos_count' => ['a' => count($supsA), 'b' => count($supsB)],
            'suplementos_added' => array_values(array_diff($supsB, $supsA)),
            'suplementos_removed' => array_values(array_diff($supsA, $supsB)),
        ];
    }

    private function diffHabitos(array $a, array $b): array
    {
        $namesA = array_column($a['habitos'] ?? [], 'nombre');
        $namesB = array_column($b['habitos'] ?? [], 'nombre');

        return [
            'habitos_count' => ['a' => count($namesA), 'b' => count($namesB)],
            'habitos_added' => array_values(array_diff($namesB, $namesA)),
            'habitos_removed' => array_values(array_diff($namesA, $namesB)),
        ];
    }

    private function diffCiclo(array $a, array $b): array
    {
        return [
            'usa_anticonceptivos' => [
                'a' => $a['usa_anticonceptivos_hormonales'] ?? null,
                'b' => $b['usa_anticonceptivos_hormonales'] ?? null,
            ],
            'fase_actual' => [
                'a' => $a['fase_actual'] ?? null,
                'b' => $b['fase_actual'] ?? null,
            ],
            'fases_count' => [
                'a' => count($a['fases'] ?? []),
                'b' => count($b['fases'] ?? []),
            ],
        ];
    }

    private function sectionsIdentical(array $diff): bool
    {
        foreach ($diff as $k => $v) {
            if (is_array($v) && isset($v['a'], $v['b'])) {
                if (is_array($v['a']) || is_array($v['b'])) {
                    if (json_encode($v['a']) !== json_encode($v['b'])) {
                        return false;
                    }
                } elseif ($v['a'] != $v['b']) {
                    return false;
                }
            } elseif (is_array($v) && $v !== []) {
                return false;
            }
        }
        return true;
    }

    private function renderHuman(array $diff): void
    {
        $this->info("═══ Diff: #{$diff['a_id']} → #{$diff['b_id']} ═══");
        if ($diff['identical']) {
            $this->info('✓ IDÉNTICOS — sin diferencias.');
            return;
        }

        if ($diff['metadata_diff'] !== []) {
            $this->newLine();
            $this->info('Metadata diffs:');
            foreach ($diff['metadata_diff'] as $field => $vals) {
                $this->line(sprintf('  · %s: %s → %s', $field, $this->stringify($vals['a']), $this->stringify($vals['b'])));
            }
        }

        $content = $diff['content_diff'];

        if ($content['top_level'] !== []) {
            $this->newLine();
            $this->info('Top-level diffs:');
            foreach ($content['top_level'] as $field => $vals) {
                $this->line(sprintf('  · %s: %s → %s',
                    $field,
                    mb_substr($this->stringify($vals['a']), 0, 60),
                    mb_substr($this->stringify($vals['b']), 0, 60),
                ));
            }
        }

        $sections = $content['sections'];
        // Filtrar solo los sub-diffs con cambios reales
        $sectionsWithChanges = [];
        foreach ($sections as $k => $v) {
            if ($this->isAbPairIdentical($v)) {
                continue; // skip pares iguales
            }
            if (is_array($v) && $v === []) {
                continue;
            }
            $sectionsWithChanges[$k] = $v;
        }

        if ($sectionsWithChanges !== []) {
            $this->newLine();
            $this->info('Sección "' . $content['plan_type'] . '" diffs:');
            foreach ($sectionsWithChanges as $k => $v) {
                if (isset($v['a'], $v['b']) && ! is_array($v['a']) && ! is_array($v['b'])) {
                    $this->line(sprintf('  · %s: %s → %s', $k, $this->stringify($v['a']), $this->stringify($v['b'])));
                } elseif (isset($v['a'], $v['b'])) {
                    $this->line('  · ' . $k . ':');
                    $this->line('      a: ' . $this->stringify($v['a']));
                    $this->line('      b: ' . $this->stringify($v['b']));
                } else {
                    $this->line('  · ' . $k . ': ' . $this->stringify($v));
                }
            }
        }
    }

    /**
     * Determina si una sub-section ['a'=>X, 'b'=>Y] tiene valores idénticos.
     */
    private function isAbPairIdentical(mixed $v): bool
    {
        if (! is_array($v) || ! isset($v['a'], $v['b'])) {
            return false;
        }
        if (is_array($v['a']) || is_array($v['b'])) {
            return json_encode($v['a']) === json_encode($v['b']);
        }
        return $v['a'] == $v['b'];
    }

    private function stringify(mixed $val): string
    {
        if ($val === null) {
            return 'null';
        }
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }
        if (is_array($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        return (string) $val;
    }
}
