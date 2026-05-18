<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * plan:audit-summary — reporte high-level del audit trail (wellcore_kb.composed_plans).
 *
 * Da una vista a vuelo de pájaro del estado del motor SIN abrir cada row:
 *   - Total + distribución status (composed/validated/rejected/exported)
 *   - Distribución plan_type (5 verticales)
 *   - Top 10 methodologies por uso
 *   - Promedio compose/lint duration ms
 *   - Promedio violations (before/after autofix)
 *   - Autofix effectiveness (% rows con violations_before > violations_after)
 *   - Últimos N rows (default 5)
 *
 * Útil para:
 *   - Daniel: ver salud del motor de un vistazo
 *   - Detectar tendencias (ej. muchos rejected → revisar lint)
 *   - Saber si los autofixes están haciendo trabajo o no
 */
final class PlanAuditSummaryCommand extends Command
{
    protected $signature = 'plan:audit-summary
                            {--since= : solo rows desde YYYY-MM-DD}
                            {--limit=5 : últimos N rows a mostrar}
                            {--json : output JSON estructurado}';

    protected $description = 'Reporte high-level del audit trail composed_plans (status, verticales, performance, autofix).';

    public function handle(): int
    {
        $query = ComposedPlan::query();
        if ($since = $this->option('since')) {
            $query->where('created_at', '>=', $since);
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn('Audit table vacío para el filtro dado.');
            return 0;
        }

        $summary = [
            'filter_since' => $since ?: null,
            'total_rows' => $total,
            'by_status' => $this->groupBy($query, 'status'),
            'by_plan_type' => $this->groupBy($query, 'plan_type'),
            'top_methodologies' => $this->topMethodologies($query, 10),
            'performance' => $this->performance($query),
            'violations' => $this->violationsStats($query),
            'autofix_effectiveness' => $this->autofixEffectiveness($query),
            'recent' => $this->recent($query, (int) $this->option('limit')),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return 0;
        }

        $this->renderHuman($summary);
        return 0;
    }

    private function groupBy($query, string $col): array
    {
        return (clone $query)
            ->select($col, DB::raw('count(*) as c'))
            ->groupBy($col)
            ->orderByDesc('c')
            ->pluck('c', $col)
            ->toArray();
    }

    private function topMethodologies($query, int $limit): array
    {
        return (clone $query)
            ->select('methodology_slug', DB::raw('count(*) as c'))
            ->groupBy('methodology_slug')
            ->orderByDesc('c')
            ->limit($limit)
            ->pluck('c', 'methodology_slug')
            ->toArray();
    }

    private function performance($query): array
    {
        $row = (clone $query)
            ->selectRaw('AVG(compose_duration_ms) as avg_compose, AVG(lint_duration_ms) as avg_lint, MAX(compose_duration_ms) as max_compose, MAX(lint_duration_ms) as max_lint')
            ->first();
        return [
            'avg_compose_ms' => $row->avg_compose !== null ? round((float) $row->avg_compose, 2) : null,
            'avg_lint_ms' => $row->avg_lint !== null ? round((float) $row->avg_lint, 2) : null,
            'max_compose_ms' => $row->max_compose !== null ? round((float) $row->max_compose, 2) : null,
            'max_lint_ms' => $row->max_lint !== null ? round((float) $row->max_lint, 2) : null,
        ];
    }

    private function violationsStats($query): array
    {
        $row = (clone $query)
            ->selectRaw('AVG(violations_before) as avg_before, AVG(violations_after) as avg_after, MAX(violations_before) as max_before, SUM(violations_after) as sum_after')
            ->first();
        return [
            'avg_before_fix' => $row->avg_before !== null ? round((float) $row->avg_before, 2) : null,
            'avg_after_fix' => $row->avg_after !== null ? round((float) $row->avg_after, 2) : null,
            'max_before_fix' => (int) ($row->max_before ?? 0),
            'sum_remaining' => (int) ($row->sum_after ?? 0),
        ];
    }

    private function autofixEffectiveness($query): array
    {
        $total = (clone $query)->count();
        $fixed = (clone $query)->whereColumn('violations_after', '<', 'violations_before')->count();
        $clean = (clone $query)->where('violations_after', 0)->count();
        return [
            'rows_total' => $total,
            'rows_with_fixes' => $fixed,
            'rows_clean_after_fix' => $clean,
            'pct_fixed' => $total > 0 ? round(($fixed / $total) * 100, 1) : 0.0,
            'pct_clean' => $total > 0 ? round(($clean / $total) * 100, 1) : 0.0,
        ];
    }

    private function recent($query, int $limit): array
    {
        return (clone $query)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'client_handle', 'plan_type', 'methodology_slug', 'status', 'violations_before', 'violations_after', 'created_at'])
            ->map(fn ($r) => [
                'id' => $r->id,
                'handle' => $r->client_handle,
                'type' => $r->plan_type,
                'methodology' => $r->methodology_slug,
                'status' => $r->status,
                'viol' => "{$r->violations_before}→{$r->violations_after}",
                'at' => (string) $r->created_at,
            ])
            ->toArray();
    }

    private function renderHuman(array $s): void
    {
        $this->info('═══ plan:audit-summary ═══');
        if ($s['filter_since']) {
            $this->line('Filtro: desde ' . $s['filter_since']);
        }
        $this->line('Total rows: ' . $s['total_rows']);
        $this->newLine();

        $this->info('Por status:');
        foreach ($s['by_status'] as $k => $v) {
            $this->line(sprintf('  %-12s %d', $k, $v));
        }
        $this->newLine();

        $this->info('Por plan_type:');
        foreach ($s['by_plan_type'] as $k => $v) {
            $this->line(sprintf('  %-15s %d', $k, $v));
        }
        $this->newLine();

        $this->info('Top methodologies:');
        foreach ($s['top_methodologies'] as $k => $v) {
            $this->line(sprintf('  %-30s %d', $k, $v));
        }
        $this->newLine();

        $this->info('Performance:');
        $p = $s['performance'];
        $this->line("  avg compose: {$p['avg_compose_ms']} ms · max: {$p['max_compose_ms']} ms");
        $this->line("  avg lint:    {$p['avg_lint_ms']} ms · max: {$p['max_lint_ms']} ms");
        $this->newLine();

        $this->info('Violations (avg per row):');
        $v = $s['violations'];
        $this->line("  before fix: {$v['avg_before_fix']}");
        $this->line("  after fix:  {$v['avg_after_fix']}");
        $this->line("  max before: {$v['max_before_fix']}");
        $this->line("  remaining (sum): {$v['sum_remaining']}");
        $this->newLine();

        $this->info('Autofix effectiveness:');
        $a = $s['autofix_effectiveness'];
        $this->line("  con fixes:        {$a['rows_with_fixes']}/{$a['rows_total']} ({$a['pct_fixed']}%)");
        $this->line("  clean post-fix:   {$a['rows_clean_after_fix']}/{$a['rows_total']} ({$a['pct_clean']}%)");
        $this->newLine();

        $this->info('Recientes (' . count($s['recent']) . '):');
        foreach ($s['recent'] as $r) {
            $this->line(sprintf('  #%d %s · %s/%s · %s · viol %s · %s',
                $r['id'], $r['handle'] ?? '—', $r['type'], $r['methodology'], $r['status'], $r['viol'], $r['at']));
        }
    }
}
