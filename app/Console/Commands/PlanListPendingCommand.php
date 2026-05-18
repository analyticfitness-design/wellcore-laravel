<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;

/**
 * plan:list-pending — lista composed_plans status=composed/validated SIN export_path.
 *
 * Útil para responder: "¿qué planes están listos para producción pero no exporté
 * todavía?". Casos típicos:
 *   - corrió plan:bundle pero todavía no plan:export-bundle-prod-script
 *   - corrió plan:compose pero no plan:persist con --export
 *   - planes generados durante experimento que están esperando aprobación
 *
 * Flags:
 *   --status=composed|validated|rejected  filtra (default: composed,validated)
 *   --handle=X                            substring match en client_handle
 *   --vertical=X                          filtra plan_type
 *   --json                                output JSON
 */
final class PlanListPendingCommand extends Command
{
    protected $signature = 'plan:list-pending
                            {--status=composed,validated : status a incluir (csv)}
                            {--handle= : substring match client_handle}
                            {--vertical= : filtra plan_type}
                            {--json : output JSON}';

    protected $description = 'Lista composed_plans listos para exportar (status=composed/validated sin export_path).';

    public function handle(): int
    {
        $statuses = array_filter(array_map('trim', explode(',', (string) $this->option('status'))));
        $query = ComposedPlan::query()
            ->whereIn('status', $statuses)
            ->whereNull('export_path');

        if ($handle = $this->option('handle')) {
            $query->where('client_handle', 'like', "%$handle%");
        }
        if ($vertical = $this->option('vertical')) {
            $query->where('plan_type', $vertical);
        }

        $rows = $query->orderByDesc('id')->get([
            'id', 'client_handle', 'plan_type', 'methodology_slug', 'status',
            'violations_before', 'violations_after', 'created_at',
        ]);

        $data = $rows->map(fn ($r) => [
            'id' => $r->id,
            'handle' => $r->client_handle,
            'plan_type' => $r->plan_type,
            'methodology_slug' => $r->methodology_slug,
            'status' => $r->status,
            'violations' => "{$r->violations_before}→{$r->violations_after}",
            'created_at' => (string) $r->created_at,
        ])->toArray();

        if ($this->option('json')) {
            $this->line(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return 0;
        }

        $this->info('═══ plan:list-pending ═══');
        $this->line('Filtros: status=' . implode(',', $statuses)
            . ' · handle=' . ($this->option('handle') ?: '—')
            . ' · vertical=' . ($this->option('vertical') ?: '—'));
        $this->line('Rows: ' . $rows->count());
        $this->newLine();

        if ($rows->isEmpty()) {
            $this->info('Nada pendiente. ✓');
            return 0;
        }

        foreach ($data as $r) {
            $this->line(sprintf(
                '  #%d · %s · %s/%s · %s · viol %s · %s',
                $r['id'], $r['handle'] ?? '—', $r['plan_type'], $r['methodology_slug'],
                $r['status'], $r['violations'], $r['created_at'],
            ));
        }

        $this->newLine();
        $this->line('Siguiente acción típica: `plan:export-prod-script <id>` o `plan:export-bundle-prod-script <handle>`.');
        return 0;
    }
}
