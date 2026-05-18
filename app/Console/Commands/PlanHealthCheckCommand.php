<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\DecisionRule;
use App\Models\Kb\ExerciseMetadata;
use App\Models\Kb\LintRule;
use App\Models\Kb\Methodology;
use App\Models\Kb\Principle;
use App\Services\LintEngine\ValidatorRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * plan:health-check — verifica el wiring del motor v2 end-to-end.
 *
 * Audita que cada componente esté correctamente registrado y consistente:
 *   1. KB DB accesible (wellcore_kb conexión)
 *   2. Todas las tablas con rows mínimos esperados
 *   3. Cada lint_rule.check_definition.rule apunta a un validator registrado
 *   4. Cada validator registrado tiene al menos una lint_rule que lo invoca
 *   5. Cada methodology tiene al menos una decision_rule que la apunta
 *   6. exercise_metadata sin gif_url broken bloqueando >X% del catálogo
 *   7. principles cuentan por vertical (alerta si alguna < 3)
 *
 * Exit code:
 *   0 = todo OK
 *   1 = warnings (motor funciona pero hay inconsistencias)
 *   2 = errores (motor no puede operar)
 */
final class PlanHealthCheckCommand extends Command
{
    protected $signature = 'plan:health-check {--json : output JSON}';

    protected $description = 'Verifica el wiring del motor v2 (validators registrados, methodologies con rules, etc.).';

    /** @var array<int, array{level: string, area: string, msg: string}> */
    private array $issues = [];

    public function handle(ValidatorRegistry $registry): int
    {
        $report = [
            'kb_connection' => $this->checkKbConnection(),
            'table_counts' => $this->checkTableCounts(),
            'validators_wiring' => $this->checkValidatorsWiring($registry),
            'methodologies_wiring' => $this->checkMethodologiesWiring(),
            'exercise_catalog' => $this->checkExerciseCatalog(),
            'principles_distribution' => $this->checkPrinciplesDistribution(),
            'issues' => $this->issues,
        ];

        $errors = array_filter($this->issues, fn ($i) => $i['level'] === 'error');
        $warnings = array_filter($this->issues, fn ($i) => $i['level'] === 'warning');

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->renderHuman($report);
        }

        if (! empty($errors)) {
            return 2;
        }
        if (! empty($warnings)) {
            return 1;
        }
        return 0;
    }

    private function checkKbConnection(): array
    {
        try {
            DB::connection('kb')->select('SELECT 1');
            return ['ok' => true];
        } catch (\Throwable $e) {
            $this->issues[] = ['level' => 'error', 'area' => 'kb', 'msg' => 'Conexión kb falló: ' . $e->getMessage()];
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function checkTableCounts(): array
    {
        $tables = [
            'methodologies' => 5,
            'decision_rules' => 5,
            'lint_rules' => 20,
            'exercise_metadata' => 100,
            'principles' => 10,
        ];
        $counts = [];
        foreach ($tables as $table => $min) {
            try {
                $c = DB::connection('kb')->table($table)->count();
                $counts[$table] = $c;
                if ($c < $min) {
                    $this->issues[] = [
                        'level' => 'warning',
                        'area' => 'tables',
                        'msg' => "Tabla $table tiene $c rows (mínimo esperado: $min). Correr `php artisan kb:seed`.",
                    ];
                }
            } catch (\Throwable $e) {
                $counts[$table] = null;
                $this->issues[] = ['level' => 'error', 'area' => 'tables', 'msg' => "Tabla $table inaccesible: " . $e->getMessage()];
            }
        }
        return $counts;
    }

    private function checkValidatorsWiring(ValidatorRegistry $registry): array
    {
        $registered = method_exists($registry, 'names')
            ? $registry->names()
            : $this->inferRegisteredNames($registry);

        $rules = LintRule::where('check_type', 'heuristic')->orWhere('check_type', 'external_head')->get();
        $referencedByRules = [];
        foreach ($rules as $rule) {
            $def = is_array($rule->check_definition_json)
                ? $rule->check_definition_json
                : (json_decode((string) $rule->check_definition_json, true) ?? []);
            $name = $def['rule'] ?? $def['validator'] ?? null;
            if ($name !== null) {
                $referencedByRules[] = $name;
            }
        }
        $referencedByRules = array_values(array_unique($referencedByRules));

        $orphanRules = array_diff($referencedByRules, $registered);
        $unusedValidators = array_diff($registered, $referencedByRules);

        foreach ($orphanRules as $name) {
            $this->issues[] = [
                'level' => 'error',
                'area' => 'validators',
                'msg' => "Lint rule referencia validator '$name' pero NO está registrado en LintEngineServiceProvider.",
            ];
        }
        // unused validators son informativos: pueden ser schema validators usados por validator_name
        if (count($unusedValidators) > 5) {
            $this->issues[] = [
                'level' => 'warning',
                'area' => 'validators',
                'msg' => count($unusedValidators) . ' validators registrados sin lint rule heurística asociada (puede ser normal para schema validators).',
            ];
        }

        return [
            'registered_count' => count($registered),
            'referenced_by_rules' => $referencedByRules,
            'orphan_rules' => array_values($orphanRules),
            'unused_validators' => array_values($unusedValidators),
        ];
    }

    private function checkMethodologiesWiring(): array
    {
        $methodologies = Methodology::all(['slug', 'vertical']);
        $ruleTargets = DecisionRule::select('then_methodology_id')->distinct()->get()
            ->pluck('then_methodology_id')->toArray();
        $methodologiesById = Methodology::whereIn('id', $ruleTargets)->pluck('slug')->toArray();
        $orphan = [];
        foreach ($methodologies as $m) {
            if (! in_array($m->slug, $methodologiesById, true)) {
                $orphan[] = $m->slug;
                $this->issues[] = [
                    'level' => 'warning',
                    'area' => 'methodologies',
                    'msg' => "Methodology '{$m->slug}' ({$m->vertical}) no tiene decision_rule que la apunte — no se puede SELECT automáticamente.",
                ];
            }
        }
        return [
            'total' => $methodologies->count(),
            'orphan' => $orphan,
        ];
    }

    private function checkExerciseCatalog(): array
    {
        $total = ExerciseMetadata::count();
        $broken = ExerciseMetadata::where('gif_url_status', 'broken')->count();
        $brokenPct = $total > 0 ? round(($broken / $total) * 100, 1) : 0.0;
        if ($brokenPct > 10) {
            $this->issues[] = [
                'level' => 'warning',
                'area' => 'exercises',
                'msg' => "$brokenPct% del catálogo tiene gif_url broken ($broken/$total). Correr `php artisan kb:reconcile-gifs`.",
            ];
        }
        return ['total' => $total, 'broken' => $broken, 'broken_pct' => $brokenPct];
    }

    private function checkPrinciplesDistribution(): array
    {
        $byVertical = Principle::selectRaw('vertical, count(*) as c')
            ->groupBy('vertical')->pluck('c', 'vertical')->toArray();
        foreach (['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'] as $v) {
            $c = $byVertical[$v] ?? 0;
            if ($c < 3) {
                $this->issues[] = [
                    'level' => 'warning',
                    'area' => 'principles',
                    'msg' => "Vertical '$v' tiene solo $c principles (mínimo recomendado: 3). PrincipleInjector tendrá poco material.",
                ];
            }
        }
        return $byVertical;
    }

    /**
     * Fallback si ValidatorRegistry no expone names() — reflection.
     *
     * @return string[]
     */
    private function inferRegisteredNames(ValidatorRegistry $registry): array
    {
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

    private function renderHuman(array $r): void
    {
        $this->info('═══ plan:health-check ═══');
        $this->newLine();

        $this->info('1. KB connection:');
        $this->line('   ' . ($r['kb_connection']['ok'] ? '✓ OK' : '✗ FALLA'));
        $this->newLine();

        $this->info('2. Table counts:');
        foreach ($r['table_counts'] as $t => $c) {
            $this->line(sprintf('   %-20s %s', $t, $c ?? 'ERROR'));
        }
        $this->newLine();

        $this->info('3. Validators wiring:');
        $vw = $r['validators_wiring'];
        $this->line("   registered: {$vw['registered_count']}");
        $this->line('   referenced by rules: ' . count($vw['referenced_by_rules']));
        if (! empty($vw['orphan_rules'])) {
            $this->line('   ⚠ orphan rules: ' . implode(', ', $vw['orphan_rules']));
        }
        $this->newLine();

        $this->info('4. Methodologies wiring:');
        $mw = $r['methodologies_wiring'];
        $this->line("   total: {$mw['total']} · orphan: " . count($mw['orphan']));
        if (! empty($mw['orphan'])) {
            $this->line('   ⚠ orphan: ' . implode(', ', $mw['orphan']));
        }
        $this->newLine();

        $this->info('5. Exercise catalog:');
        $ec = $r['exercise_catalog'];
        $this->line("   total: {$ec['total']} · broken: {$ec['broken']} ({$ec['broken_pct']}%)");
        $this->newLine();

        $this->info('6. Principles distribution:');
        foreach ($r['principles_distribution'] as $v => $c) {
            $this->line(sprintf('   %-15s %d', $v, $c));
        }
        $this->newLine();

        $errors = array_filter($r['issues'], fn ($i) => $i['level'] === 'error');
        $warnings = array_filter($r['issues'], fn ($i) => $i['level'] === 'warning');

        if (empty($errors) && empty($warnings)) {
            $this->info('✓ Motor v2 health: TODO OK');
            return;
        }

        if (! empty($errors)) {
            $this->error('Errores (' . count($errors) . '):');
            foreach ($errors as $i) {
                $this->line("  ✗ [{$i['area']}] {$i['msg']}");
            }
        }
        if (! empty($warnings)) {
            $this->warn('Warnings (' . count($warnings) . '):');
            foreach ($warnings as $i) {
                $this->line("  ⚠ [{$i['area']}] {$i['msg']}");
            }
        }
    }
}
