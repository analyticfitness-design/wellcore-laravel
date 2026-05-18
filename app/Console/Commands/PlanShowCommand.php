<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;

/**
 * plan:show — renderiza un composed_plan en formato humano-leible.
 *
 * Soporta los 5 verticales del motor v2. Cada uno tiene render dedicado.
 *
 * Modos:
 *   --summary (default) → titulo, methodology, métricas clave, count por bloque
 *   --detail            → todo el contenido renderizado (semanas/comidas/hábitos)
 *   --json              → JSON crudo del composed_plan
 */
final class PlanShowCommand extends Command
{
    protected $signature = 'plan:show
                            {composed_id : ID en wellcore_kb.composed_plans}
                            {--detail : muestra contenido completo (semanas/comidas/etc.)}
                            {--with-violations : muestra las violations del LintEngine (pre + post fix)}
                            {--json : output JSON crudo}';

    protected $description = 'Renderiza un composed_plan en formato humano-leible (resumen o detalle).';

    public function handle(): int
    {
        $id = (int) $this->argument('composed_id');
        $cp = ComposedPlan::find($id);
        if (! $cp) {
            $this->error("composed_plans #$id no encontrado.");
            return 2;
        }

        if ($this->option('json')) {
            $this->line(json_encode($cp->planJson(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return 0;
        }

        $plan = $cp->planJson();
        $detail = (bool) $this->option('detail');

        $this->renderHeader($cp, $plan);

        $renderer = match ($plan['plan_type'] ?? null) {
            'entrenamiento' => 'renderEntrenamiento',
            'nutricion' => 'renderNutricion',
            'suplementacion' => 'renderSuplementacion',
            'habitos' => 'renderHabitos',
            'ciclo' => 'renderCiclo',
            default => 'renderGenerico',
        };

        $this->$renderer($plan, $detail);

        // Sprint 39: render principios aplicados al final (todas las verticales)
        $this->renderPrinciples($plan);

        if ($this->option('with-violations')) {
            $this->renderViolations($cp);
        }

        return 0;
    }

    /**
     * Render violations del LintEngine guardadas en composed_plans.
     * Esquema real: lint_result_pre_json + lint_result_post_json + fixes_applied_json.
     */
    private function renderViolations(ComposedPlan $cp): void
    {
        // Los campos pueden venir como array (cast) o string (legacy). Normalizamos.
        $pre = $this->coerceJson($cp->lint_result_pre_json);
        $post = $this->coerceJson($cp->lint_result_post_json);
        $fixes = $this->coerceJson($cp->fixes_applied_json);

        $preViolations = $pre['violations'] ?? [];
        $postViolations = $post['violations'] ?? [];

        $this->newLine();
        $this->info('═══ LintEngine ═══');
        $this->line("Pre-fix:  {$cp->violations_before} violations · " . count($fixes) . " fixes aplicados · Post-fix: {$cp->violations_after} violations");

        if ($postViolations !== []) {
            $this->newLine();
            $this->warn('Violations restantes (post-fix):');
            foreach ($postViolations as $v) {
                $rule = $v['rule_code'] ?? $v['rule'] ?? $v['rule_id'] ?? '?';
                $severity = $v['severity'] ?? 'warning';
                $message = $v['message'] ?? '?';
                $icon = $severity === 'error' ? '✗' : ($severity === 'warning' ? '⚠' : '·');
                $this->line(sprintf('  %s [%s] %s', $icon, $rule, $message));
                if (! empty($v['evidence'])) {
                    $ev = is_array($v['evidence']) ? json_encode($v['evidence'], JSON_UNESCAPED_UNICODE) : (string) $v['evidence'];
                    $this->line('     evidencia: ' . mb_substr($ev, 0, 200));
                }
            }
        }

        if ($fixes !== []) {
            $this->newLine();
            $this->info('Auto-fixes aplicados:');
            foreach ($fixes as $f) {
                $rule = $f['rule_code'] ?? $f['rule'] ?? $f['rule_id'] ?? '?';
                $action = $f['action'] ?? $f['description'] ?? '?';
                $this->line("  ✓ [{$rule}] {$action}");
            }
        }

        if ($preViolations !== $postViolations && $preViolations !== []) {
            $this->newLine();
            $this->line('Violations pre-fix (' . count($preViolations) . '):');
            foreach ($preViolations as $v) {
                $rule = $v['rule_code'] ?? $v['rule'] ?? $v['rule_id'] ?? '?';
                $severity = $v['severity'] ?? 'warning';
                $message = $v['message'] ?? '?';
                $icon = $severity === 'error' ? '✗' : ($severity === 'warning' ? '⚠' : '·');
                $this->line(sprintf('  %s [%s] %s', $icon, $rule, $message));
            }
        }
    }

    private function renderPrinciples(array $plan): void
    {
        $slugs = $plan['principios_aplicados'] ?? [];
        if ($slugs === []) {
            return;
        }
        $this->newLine();
        $this->info('Principios aplicados (' . count($slugs) . '):');
        $principles = \App\Models\Kb\Principle::whereIn('slug', $slugs)->get()->keyBy('slug');
        foreach ($slugs as $slug) {
            $p = $principles->get($slug);
            if ($p === null) {
                $this->line("  · $slug (no encontrado en DB)");
                continue;
            }
            $this->line("  · $slug — {$p->name}");
            $this->line('    ' . mb_substr($p->description_short, 0, 120) . (mb_strlen($p->description_short) > 120 ? '...' : ''));
        }
    }

    private function renderHeader(ComposedPlan $cp, array $plan): void
    {
        $this->info('═══ Plan #' . $cp->id . ' · ' . ($plan['plan_type'] ?? '?') . ' ═══');
        $this->line('Título:        ' . ($plan['titulo'] ?? '?'));
        $this->line('Methodology:   ' . ($cp->methodology_slug ?? '?'));
        $this->line('Cliente handle: ' . ($cp->client_handle ?? '—'));
        $this->line('Fecha inicio:  ' . ($plan['fecha_inicio'] ?? '—'));
        $this->line('Duración:      ' . ($plan['duracion_semanas'] ?? '?') . ' semanas');
        $this->line('Status:        ' . $cp->status);
        $this->line('Violations:    pre=' . $cp->violations_before . ' / post=' . $cp->violations_after);
        $this->line('Creado:        ' . $cp->created_at);
        $this->newLine();
    }

    private function renderEntrenamiento(array $plan, bool $detail): void
    {
        $this->info('Split semanal:');
        foreach (($plan['split'] ?? []) as $dia => $grupo) {
            $this->line("  $dia → $grupo");
        }
        $this->newLine();

        $semanas = $plan['semanas'] ?? [];
        if (! $detail) {
            $this->info('Periodización (resumen):');
            foreach ($semanas as $s) {
                $totalEj = array_sum(array_map(fn ($d) => count($d['ejercicios'] ?? []), $s['dias'] ?? []));
                $this->line("  Sem {$s['numero']} · {$s['fase']} · {$totalEj} ejercicios");
            }
            return;
        }

        $this->info('Detalle completo:');
        foreach ($semanas as $s) {
            $this->newLine();
            $this->line("─── Semana {$s['numero']} · {$s['fase']} ───");
            foreach (($s['dias'] ?? []) as $dia) {
                $this->line("  {$dia['dia_semana']} — {$dia['grupo_muscular']}");
                foreach (($dia['ejercicios'] ?? []) as $ej) {
                    $this->line(sprintf(
                        '    · %s · %s×%s · descanso %s · RIR %s',
                        $ej['nombre'],
                        $ej['series'] ?? '?',
                        $ej['repeticiones'] ?? '?',
                        $ej['descanso'] ?? '?',
                        $ej['rir'] ?? '?',
                    ));
                }
            }
        }
    }

    private function renderNutricion(array $plan, bool $detail): void
    {
        $this->info('Objetivo calórico: ' . ($plan['objetivo_cal'] ?? '?') . ' kcal/día');
        if (isset($plan['macros'])) {
            $m = $plan['macros'];
            $this->line("Macros target:  P {$m['proteina_g']}g · C {$m['carbohidratos_g']}g · G {$m['grasas_g']}g");
        }
        $this->line('BMR: ' . ($plan['bmr_calculado'] ?? '?') . ' · TDEE: ' . ($plan['tdee_calculado'] ?? '?'));
        $this->newLine();

        $comidas = $plan['comidas'] ?? [];
        if (! $detail) {
            $this->info('Comidas (resumen):');
            foreach ($comidas as $c) {
                $opciones = count(array_filter(['a', 'b', 'c'], fn ($k) => isset($c["opcion_$k"])));
                $kcal = $c['kcal_objetivo'] ?? '?';
                $this->line("  · {$c['nombre']} ({$c['hora']}) — {$kcal} kcal — {$opciones} opciones");
            }
            return;
        }

        foreach ($comidas as $c) {
            $this->newLine();
            $this->line("─── {$c['nombre']} ({$c['hora']}) ───");
            $m = $c['macros'] ?? [];
            $this->line("  Target: P {$m['proteina']}g · C {$m['carbohidratos']}g · G {$m['grasas']}g · {$c['kcal_objetivo']} kcal");
            foreach (['a', 'b', 'c'] as $k) {
                if (isset($c["opcion_$k"])) {
                    $this->line("  Opción " . strtoupper($k) . ':');
                    foreach ($c["opcion_$k"] as $item) {
                        $this->line("    - $item");
                    }
                }
            }
        }
    }

    private function renderSuplementacion(array $plan, bool $detail): void
    {
        if (isset($plan['stack_info'])) {
            $s = $plan['stack_info'];
            $this->line('Stack:         ' . ($s['stack_nombre'] ?? '?'));
            $this->line('Slug:          ' . ($s['stack_slug'] ?? '?'));
            $this->line('Costo mensual: COP ' . number_format((int) ($s['costo_mensual_estimado_cop'] ?? 0), 0, ',', '.'));
        }
        $this->newLine();

        $sups = $plan['suplementos'] ?? [];
        $this->info(count($sups) . ' suplementos:');
        foreach ($sups as $s) {
            $this->line("  · {$s['nombre']}");
            if ($detail) {
                $this->line("    dosis:     {$s['dosis']}");
                $this->line("    momento:   {$s['momento']}");
                $this->line("    frecuencia: {$s['frecuencia']}");
                if ($s['notas']) {
                    $this->line("    notas:     " . mb_substr($s['notas'], 0, 150) . (mb_strlen($s['notas']) > 150 ? '...' : ''));
                }
            } else {
                $this->line("    {$s['dosis']} · {$s['momento']}");
            }
        }
    }

    private function renderHabitos(array $plan, bool $detail): void
    {
        $habitos = $plan['habitos'] ?? [];
        $this->info(count($habitos) . ' hábitos:');
        foreach ($habitos as $h) {
            $this->line("  · {$h['nombre']} [{$h['categoria']}]");
            $this->line("    objetivo:  {$h['objetivo']}");
            if ($detail) {
                $this->line("    tracking:  {$h['tracking_method']}");
                if (isset($h['por_que_importa'])) {
                    $this->line("    por qué:   " . mb_substr($h['por_que_importa'], 0, 150) . (mb_strlen($h['por_que_importa']) > 150 ? '...' : ''));
                }
            }
        }
    }

    private function renderCiclo(array $plan, bool $detail): void
    {
        $this->line('Días totales: ' . ($plan['ciclo_dias_totales'] ?? 28));
        $this->line('Anticonceptivos hormonales: ' . ($plan['usa_anticonceptivos_hormonales'] ? 'sí' : 'no'));
        $this->line('Día 1 último ciclo: ' . ($plan['dia_uno_ultimo_ciclo'] ?? '—'));
        $this->line('Fase actual: ' . ($plan['fase_actual'] ?? '—'));
        $this->newLine();

        $fases = $plan['fases'] ?? [];
        $this->info(count($fases) . ' fases del ciclo:');
        foreach ($fases as $f) {
            $this->line("  · {$f['nombre']} (días {$f['dias_tipico']})");
            if ($detail && isset($f['sintomas_tipicos'])) {
                $sintomas = is_array($f['sintomas_tipicos']) ? implode(', ', array_slice($f['sintomas_tipicos'], 0, 3)) : '';
                $this->line("    síntomas: $sintomas");
            }
        }
    }

    /** Convierte un atributo del modelo (puede ser array casteado o string JSON) en array. */
    private function coerceJson(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private function renderGenerico(array $plan, bool $detail): void
    {
        $this->warn('Renderizado genérico (plan_type no reconocido).');
        $this->line('Keys top-level: ' . implode(', ', array_keys($plan)));
        if ($detail) {
            $this->line(json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }
}
