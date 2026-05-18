<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * kb:audit-orphans — encuentra rows huérfanos / sin referencias en wellcore_kb.
 *
 * No borra nada (solo reporta). Complementa plan:health-check pero baja
 * un nivel: en lugar de "¿está cableado?" pregunta "¿hay rows aislados que
 * deberían tener referencias?".
 *
 * Auditorías:
 *   1. exercise_metadata.gif_url_status='broken' sin override en aliases
 *   2. methodologies sin decision_rule que las apunte (NEVER seleccionable)
 *   3. decision_rules apuntando a methodology_id inexistente (broken FK soft)
 *   4. lint_rules con check_definition.rule apuntando a validator inexistente
 *   5. principles sin tags (no rankeable por PrincipleInjector)
 *   6. principles con vertical inválida (no en {entrenamiento,nutricion,suplementacion,habitos,ciclo})
 *
 * Output: tabla con counts + IDs sample. `--fix-hint` muestra comando sugerido.
 */
final class KbAuditOrphansCommand extends Command
{
    protected $signature = 'kb:audit-orphans {--json : output JSON} {--fix-hint : muestra comando sugerido para resolver}';

    protected $description = 'Encuentra rows huérfanos en wellcore_kb (gif broken, methodologies sin rules, principles sin tags).';

    private const VERTICALES_VALIDAS = ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'];

    public function handle(): int
    {
        $report = [
            'broken_gifs' => $this->brokenGifs(),
            'orphan_methodologies' => $this->orphanMethodologies(),
            'broken_decision_rules' => $this->brokenDecisionRules(),
            'orphan_lint_rules' => $this->orphanLintRules(),
            'principles_no_tags' => $this->principlesNoTags(),
            'principles_invalid_vertical' => $this->principlesInvalidVertical(),
        ];

        $totalOrphans = array_sum(array_map(fn ($r) => $r['count'], $report));

        if ($this->option('json')) {
            $this->line(json_encode([
                'total_orphans' => $totalOrphans,
                'sections' => $report,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return $totalOrphans > 0 ? 1 : 0;
        }

        $this->renderHuman($report, $totalOrphans);
        return $totalOrphans > 0 ? 1 : 0;
    }

    private function brokenGifs(): array
    {
        $rows = DB::connection('kb')
            ->table('exercise_metadata')
            ->where('gif_url_status', 'broken')
            ->select('id', 'alias', 'gif_filename')
            ->limit(10)
            ->get();
        $total = DB::connection('kb')
            ->table('exercise_metadata')
            ->where('gif_url_status', 'broken')
            ->count();
        return [
            'count' => $total,
            'sample' => $rows->map(fn ($r) => ['id' => $r->id, 'alias' => $r->alias, 'gif_filename' => $r->gif_filename])->toArray(),
            'fix_hint' => 'php artisan kb:reconcile-gifs',
        ];
    }

    private function orphanMethodologies(): array
    {
        $methodologies = DB::connection('kb')->table('methodologies')->pluck('slug', 'id');
        $referenced = DB::connection('kb')
            ->table('decision_rules')
            ->select('then_methodology_id')
            ->distinct()
            ->pluck('then_methodology_id')
            ->filter()
            ->all();
        $orphan = [];
        foreach ($methodologies as $id => $slug) {
            if (! in_array($id, $referenced, true)) {
                $orphan[] = ['id' => $id, 'slug' => $slug];
            }
        }
        return [
            'count' => count($orphan),
            'sample' => array_slice($orphan, 0, 10),
            'fix_hint' => 'Agregar decision_rule en DecisionRulesSeeder con then_methodology_id apuntando a estos IDs.',
        ];
    }

    private function brokenDecisionRules(): array
    {
        $validIds = DB::connection('kb')->table('methodologies')->pluck('id')->all();
        $broken = DB::connection('kb')
            ->table('decision_rules')
            ->whereNotIn('then_methodology_id', $validIds)
            ->whereNotNull('then_methodology_id')
            ->select('id', 'name', 'then_methodology_id')
            ->limit(10)
            ->get();
        $total = DB::connection('kb')
            ->table('decision_rules')
            ->whereNotIn('then_methodology_id', $validIds)
            ->whereNotNull('then_methodology_id')
            ->count();
        return [
            'count' => $total,
            'sample' => $broken->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'pointing_to' => $r->then_methodology_id,
            ])->toArray(),
            'fix_hint' => 'Corregir then_methodology_id en DecisionRulesSeeder o re-seedear methodologies primero.',
        ];
    }

    private function orphanLintRules(): array
    {
        $rules = DB::connection('kb')
            ->table('lint_rules')
            ->whereIn('check_type', ['heuristic', 'external_head'])
            ->select('id', 'code', 'check_definition_json')
            ->get();
        $registered = $this->getRegisteredValidatorNames();
        $orphan = [];
        foreach ($rules as $rule) {
            $def = json_decode((string) $rule->check_definition_json, true) ?? [];
            $name = $def['rule'] ?? $def['validator'] ?? null;
            if ($name !== null && ! in_array($name, $registered, true)) {
                $orphan[] = ['id' => $rule->id, 'code' => $rule->code, 'expects' => $name];
            }
        }
        return [
            'count' => count($orphan),
            'sample' => array_slice($orphan, 0, 10),
            'fix_hint' => 'Crear y registrar validator faltante en LintEngineServiceProvider, o eliminar la lint_rule.',
        ];
    }

    private function principlesNoTags(): array
    {
        $rows = DB::connection('kb')
            ->table('principles')
            ->where(function ($q) {
                $q->whereNull('tags')
                    ->orWhere('tags', '[]')
                    ->orWhere('tags', '');
            })
            ->select('id', 'slug', 'vertical')
            ->limit(10)
            ->get();
        return [
            'count' => $rows->count(),
            'sample' => $rows->map(fn ($r) => ['id' => $r->id, 'slug' => $r->slug, 'vertical' => $r->vertical])->toArray(),
            'fix_hint' => 'Agregar tags[] al row en PrinciplesSeeder. Sin tags el PrincipleInjector no puede rankear.',
        ];
    }

    private function principlesInvalidVertical(): array
    {
        $rows = DB::connection('kb')
            ->table('principles')
            ->whereNotIn('vertical', self::VERTICALES_VALIDAS)
            ->select('id', 'slug', 'vertical')
            ->limit(10)
            ->get();
        return [
            'count' => $rows->count(),
            'sample' => $rows->map(fn ($r) => ['id' => $r->id, 'slug' => $r->slug, 'vertical' => $r->vertical])->toArray(),
            'fix_hint' => 'Cambiar vertical a uno válido: ' . implode(', ', self::VERTICALES_VALIDAS),
        ];
    }

    /**
     * @return string[]
     */
    private function getRegisteredValidatorNames(): array
    {
        $registry = app(\App\Services\LintEngine\ValidatorRegistry::class);
        if (method_exists($registry, 'names')) {
            return $registry->names();
        }
        // Fallback reflection
        try {
            $ref = new \ReflectionObject($registry);
            foreach ($ref->getProperties() as $prop) {
                $prop->setAccessible(true);
                $val = $prop->getValue($registry);
                if (is_array($val)) {
                    return array_keys($val);
                }
            }
        } catch (\Throwable) {
            // ignore
        }
        return [];
    }

    private function renderHuman(array $report, int $total): void
    {
        $this->info('═══ kb:audit-orphans ═══');
        $this->line('Total huérfanos detectados: ' . $total);
        $this->newLine();

        $sections = [
            'broken_gifs' => 'GIFs broken (exercise_metadata)',
            'orphan_methodologies' => 'Methodologies sin decision_rule',
            'broken_decision_rules' => 'Decision rules apuntando a methodology inexistente',
            'orphan_lint_rules' => 'Lint rules sin validator registrado',
            'principles_no_tags' => 'Principles sin tags',
            'principles_invalid_vertical' => 'Principles con vertical inválida',
        ];

        foreach ($sections as $key => $title) {
            $r = $report[$key];
            $this->line(sprintf('%s: %d', $title, $r['count']));
            if ($r['count'] > 0) {
                foreach (array_slice($r['sample'], 0, 5) as $item) {
                    $this->line('  · ' . json_encode($item, JSON_UNESCAPED_UNICODE));
                }
                if ($this->option('fix-hint')) {
                    $this->line('  → ' . $r['fix_hint']);
                }
            }
            $this->newLine();
        }

        if ($total === 0) {
            $this->info('✓ Sin huérfanos. KB consistente.');
        } else {
            $this->warn("⚠ $total rows huérfanas. Usa --fix-hint para sugerencias.");
        }
    }
}
