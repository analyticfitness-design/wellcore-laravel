<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;

/**
 * plan:reset-audit-table — cleanup selectivo de composed_plans.
 *
 * NUNCA toca wellcore_fitness (producción). Solo wellcore_kb.composed_plans
 * (audit trail local).
 *
 * Filtros combinables:
 *   --handle=X        : solo rows con client_handle X (substring)
 *   --status=X        : solo rows con status X (composed|validated|rejected|exported)
 *   --before=DATE     : rows creados antes de DATE (YYYY-MM-DD)
 *   --keep-exported   : preservar rows con export_path != null (los ya subidos a prod)
 *
 * Safety:
 *   --confirm requerido para borrar (default es DRY-RUN)
 *
 * Casos de uso:
 *   - Limpiar audit rows de tests (--handle=test- --confirm)
 *   - Borrar rejected viejos sin perder validados (--status=rejected --before=...)
 *   - Reset completo conservando solo los exportados a prod (--keep-exported --confirm)
 */
final class PlanResetAuditTableCommand extends Command
{
    protected $signature = 'plan:reset-audit-table
                            {--handle= : filtra por client_handle (substring match)}
                            {--status= : filtra por status (composed|validated|rejected|exported)}
                            {--before= : YYYY-MM-DD, solo rows creados antes}
                            {--keep-exported : preservar rows con export_path != null}
                            {--all : sin filtros (truncate completo) — requiere --confirm}
                            {--confirm : escribir real (default = dry-run)}';

    protected $description = 'Limpieza selectiva de wellcore_kb.composed_plans (audit trail local).';

    public function handle(): int
    {
        $hasFilter = $this->option('handle')
            || $this->option('status')
            || $this->option('before')
            || $this->option('keep-exported');

        $isAll = (bool) $this->option('all');

        if (! $hasFilter && ! $isAll) {
            $this->error('Sin filtros — pasá al menos uno (--handle/--status/--before/--keep-exported) o usá --all para truncate completo.');
            return 2;
        }

        $query = ComposedPlan::query();
        if ($handle = $this->option('handle')) {
            $query->where('client_handle', 'like', "%$handle%");
        }
        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }
        if ($before = $this->option('before')) {
            $query->where('created_at', '<', $before);
        }
        if ($this->option('keep-exported')) {
            $query->whereNull('export_path');
        }

        $count = (clone $query)->count();
        $dryRun = ! $this->option('confirm');

        $this->info('═══ plan:reset-audit-table ═══');
        $this->line('Filtros:');
        foreach (['handle', 'status', 'before', 'keep-exported', 'all'] as $f) {
            if ($v = $this->option($f)) {
                $this->line("  · --$f = " . ($v === true ? 'true' : $v));
            }
        }
        $this->line('Rows que match: ' . $count);
        $this->line('Modo: ' . ($dryRun ? 'DRY-RUN' : '⚠️ WRITE'));
        $this->newLine();

        if ($count === 0) {
            $this->info('Sin rows para borrar. Nada que hacer.');
            return 0;
        }

        // Preview top 5
        $preview = (clone $query)->limit(5)->get(['id', 'client_handle', 'plan_type', 'status', 'created_at']);
        $this->info('Preview (top 5):');
        foreach ($preview as $r) {
            $this->line(sprintf('  · #%d %s · %s · %s · %s',
                $r->id, $r->client_handle ?? '—', $r->plan_type, $r->status, $r->created_at));
        }
        $this->newLine();

        if ($dryRun) {
            $this->info('[DRY-RUN] No se borró nada. Re-ejecutar con --confirm para aplicar.');
            return 0;
        }

        $deleted = $query->delete();
        $this->info("✓ $deleted rows eliminados.");
        return 0;
    }
}
