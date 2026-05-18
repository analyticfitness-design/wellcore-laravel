<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * kb:stats — telemetría del motor v2 sobre el audit trail composed_plans.
 *
 * Muestra:
 *   - Total planes generados
 *   - Distribución por plan_type
 *   - Distribución por methodology
 *   - Distribución por status (validated/rejected/exported/composed)
 *   - Tiempo promedio compose+lint
 *   - Top client_handles (por # de planes)
 *   - Violations totales antes vs después de auto-fix
 *   - Tasa de auto-fix exitoso
 *
 * Filtros:
 *   --since=YYYY-MM-DD : solo planes creados después de X
 *   --vertical=X       : solo un vertical
 */
final class KbStatsCommand extends Command
{
    protected $signature = 'kb:stats
                            {--since= : YYYY-MM-DD, solo planes desde esta fecha}
                            {--vertical= : filtra a una sola vertical}
                            {--json : output JSON parseable}';

    protected $description = 'Telemetría del motor v2 sobre wellcore_kb.composed_plans (audit trail).';

    public function handle(): int
    {
        $query = ComposedPlan::query();
        if ($since = $this->option('since')) {
            $query->where('created_at', '>=', $since);
        }
        if ($vertical = $this->option('vertical')) {
            $query->where('plan_type', $vertical);
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn('No hay composed_plans con los filtros aplicados.');
            return 0;
        }

        $stats = [
            'total' => $total,
            'period' => [
                'since' => $since ?: (string) (clone $query)->min('created_at'),
                'until' => (string) (clone $query)->max('created_at'),
            ],
            'filtros' => [
                'vertical' => $vertical,
            ],
            'by_plan_type' => $this->countBy(clone $query, 'plan_type'),
            'by_methodology' => $this->countBy(clone $query, 'methodology_slug'),
            'by_status' => $this->countBy(clone $query, 'status'),
            'top_handles' => $this->topHandles(clone $query),
            'avg_compose_ms' => round((float) (clone $query)->avg('compose_duration_ms'), 2),
            'avg_lint_ms' => round((float) (clone $query)->avg('lint_duration_ms'), 2),
            'violations' => [
                'total_before_fix' => (int) (clone $query)->sum('violations_before'),
                'total_after_fix' => (int) (clone $query)->sum('violations_after'),
                'plans_with_zero_violations' => (clone $query)->where('violations_after', 0)->count(),
            ],
            'export_rate' => [
                'exported_to_prod' => (clone $query)->whereNotNull('export_path')->count(),
                'percentage' => round(((clone $query)->whereNotNull('export_path')->count() / $total) * 100, 1),
            ],
            // Sprint 48: telemetría principles inyectados
            'principles_telemetry' => $this->principlesTelemetry(clone $query),
        ];

        // Auto-fix effectiveness
        $beforeFix = $stats['violations']['total_before_fix'];
        $afterFix = $stats['violations']['total_after_fix'];
        $stats['auto_fix_effectiveness'] = [
            'violations_resolved' => max(0, $beforeFix - $afterFix),
            'effectiveness_pct' => $beforeFix > 0
                ? round((($beforeFix - $afterFix) / $beforeFix) * 100, 1)
                : 0.0,
        ];

        if ($this->option('json')) {
            $this->line(json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->renderHuman($stats);
        }

        return 0;
    }

    private function countBy($query, string $column): array
    {
        return $query->select($column, DB::raw('COUNT(*) as c'))
            ->groupBy($column)
            ->orderByDesc('c')
            ->get()
            ->pluck('c', $column)
            ->toArray();
    }

    private function topHandles($query, int $limit = 5): array
    {
        return $query->whereNotNull('client_handle')
            ->select('client_handle', DB::raw('COUNT(*) as c'))
            ->groupBy('client_handle')
            ->orderByDesc('c')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => ['handle' => $r->client_handle, 'planes' => $r->c])
            ->toArray();
    }

    /**
     * Sprint 48: telemetría de principles inyectados a través de plan_json.principios_aplicados.
     *
     * @return array{
     *   total_inyecciones: int,
     *   distinct_principles_usados: int,
     *   top: array<int, array{slug: string, count: int}>,
     *   unused: string[]  // principles activos que nunca fueron inyectados
     * }
     */
    private function principlesTelemetry($query): array
    {
        $counts = [];
        foreach ($query->select('plan_json')->get() as $row) {
            $plan = json_decode((string) $row->plan_json, true);
            $slugs = $plan['principios_aplicados'] ?? [];
            if (! is_array($slugs)) {
                continue;
            }
            foreach ($slugs as $slug) {
                $counts[$slug] = ($counts[$slug] ?? 0) + 1;
            }
        }
        arsort($counts);

        // Detectar principles NUNCA usados
        try {
            $allActiveSlugs = \App\Models\Kb\Principle::active()->pluck('slug')->toArray();
            $unused = array_values(array_diff($allActiveSlugs, array_keys($counts)));
        } catch (\Throwable) {
            $unused = [];
        }

        return [
            'total_inyecciones' => array_sum($counts),
            'distinct_principles_usados' => count($counts),
            'top' => array_map(
                fn ($slug, $c) => ['slug' => $slug, 'count' => $c],
                array_keys(array_slice($counts, 0, 10, true)),
                array_values(array_slice($counts, 0, 10, true)),
            ),
            'unused' => $unused,
        ];
    }

    private function renderHuman(array $stats): void
    {
        $this->info('═══ Motor v2 — Telemetría composed_plans ═══');
        $this->line("Período: {$stats['period']['since']} → {$stats['period']['until']}");
        if ($stats['filtros']['vertical']) {
            $this->line("Filtro vertical: {$stats['filtros']['vertical']}");
        }
        $this->line("Total planes: {$stats['total']}");
        $this->newLine();

        $this->info('Por plan_type:');
        $this->table(
            ['Vertical', 'Count', '%'],
            array_map(
                fn ($v, $c) => [$v, $c, round(($c / $stats['total']) * 100, 1) . '%'],
                array_keys($stats['by_plan_type']),
                array_values($stats['by_plan_type']),
            ),
        );

        $this->info('Por methodology:');
        $this->table(
            ['Methodology', 'Count'],
            array_map(fn ($k, $c) => [$k, $c], array_keys($stats['by_methodology']), array_values($stats['by_methodology'])),
        );

        $this->info('Por status:');
        $this->table(
            ['Status', 'Count', '%'],
            array_map(
                fn ($s, $c) => [$s, $c, round(($c / $stats['total']) * 100, 1) . '%'],
                array_keys($stats['by_status']),
                array_values($stats['by_status']),
            ),
        );

        if ($stats['top_handles'] !== []) {
            $this->info('Top client_handles:');
            $this->table(
                ['Handle', '# planes'],
                array_map(fn ($h) => [$h['handle'], $h['planes']], $stats['top_handles']),
            );
        }

        $this->info('Performance promedio:');
        $this->line("  · compose: {$stats['avg_compose_ms']} ms");
        $this->line("  · lint:    {$stats['avg_lint_ms']} ms");
        $this->newLine();

        $this->info('Violations:');
        $this->line("  · Total antes del fix:        {$stats['violations']['total_before_fix']}");
        $this->line("  · Total después del fix:      {$stats['violations']['total_after_fix']}");
        $this->line("  · Planes con 0 violations:    {$stats['violations']['plans_with_zero_violations']}");
        $this->line("  · Auto-fix resolved:          {$stats['auto_fix_effectiveness']['violations_resolved']} ({$stats['auto_fix_effectiveness']['effectiveness_pct']}%)");
        $this->newLine();

        $this->info('Export rate:');
        $this->line("  · Exportados a prod (script generado): {$stats['export_rate']['exported_to_prod']} / {$stats['total']} ({$stats['export_rate']['percentage']}%)");
        $this->newLine();

        // Sprint 48: principles telemetry
        $pt = $stats['principles_telemetry'];
        $this->info('Principles inyectados:');
        $this->line("  · Total inyecciones:     {$pt['total_inyecciones']}");
        $this->line("  · Principles únicos:     {$pt['distinct_principles_usados']}");
        if ($pt['top'] !== []) {
            $this->newLine();
            $this->info('Top 10 principles más inyectados:');
            $this->table(
                ['Slug', 'Inyectado N veces'],
                array_map(fn ($p) => [$p['slug'], $p['count']], $pt['top']),
            );
        }
        if ($pt['unused'] !== []) {
            $this->newLine();
            $this->warn('Principles NUNCA inyectados (' . count($pt['unused']) . '):');
            foreach ($pt['unused'] as $slug) {
                $this->line("  · $slug");
            }
        }
    }
}
