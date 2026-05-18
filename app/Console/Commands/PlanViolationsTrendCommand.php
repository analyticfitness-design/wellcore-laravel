<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use Illuminate\Console\Command;

/**
 * plan:violations-trend — agrupa violations por rule_code y reporta frecuencia.
 *
 * Útil para detectar qué reglas están "siempre encendidas" en el audit table
 * (señal de bug sistemático en el composer) vs reglas que casi nunca disparan
 * (candidatas a deprecar o relax tolerance).
 *
 * Procesa lint_result_post_json (post-autofix). Si querés pre-fix, --pre.
 *
 * Flags:
 *   --since=YYYY-MM-DD    rows desde fecha
 *   --pre                 usa lint_result_pre_json (antes de autofix)
 *   --top=N               muestra solo top N (default 20)
 *   --json
 */
final class PlanViolationsTrendCommand extends Command
{
    protected $signature = 'plan:violations-trend
                            {--since= : YYYY-MM-DD}
                            {--pre : usa lint_result_pre_json (pre-autofix)}
                            {--top=20 : top N rules}
                            {--json : output JSON}';

    protected $description = 'Agrupa violations por rule_code en audit trail; detecta reglas crónicas.';

    public function handle(): int
    {
        $query = ComposedPlan::query();
        if ($since = $this->option('since')) {
            $query->where('created_at', '>=', $since);
        }

        $col = $this->option('pre') ? 'lint_result_pre_json' : 'lint_result_post_json';
        $rows = $query->whereNotNull($col)->get([$col, 'id', 'plan_type']);

        $counts = [];
        $severityByRule = [];
        $totalRows = 0;
        foreach ($rows as $row) {
            $totalRows++;
            $data = $row->{$col};
            if (! is_array($data)) {
                $data = json_decode((string) $data, true) ?? [];
            }
            foreach (($data['violations'] ?? []) as $v) {
                $code = (string) ($v['rule_code'] ?? 'unknown');
                $counts[$code] = ($counts[$code] ?? 0) + 1;
                $severityByRule[$code] = $v['severity'] ?? 'warning';
            }
        }

        arsort($counts);
        $top = (int) $this->option('top');
        $sliced = array_slice($counts, 0, $top, true);

        $data = [];
        foreach ($sliced as $code => $c) {
            $pct = $totalRows > 0 ? round(($c / $totalRows) * 100, 1) : 0.0;
            $data[] = [
                'rule_code' => $code,
                'severity' => $severityByRule[$code] ?? 'warning',
                'count' => $c,
                'pct_of_audit_rows' => $pct,
            ];
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'source' => $col,
                'total_audit_rows' => $totalRows,
                'unique_rules_triggered' => count($counts),
                'top' => $data,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return 0;
        }

        $this->info('═══ plan:violations-trend ═══');
        $this->line('Fuente: ' . $col . ($this->option('pre') ? ' (PRE-autofix)' : ' (POST-autofix)'));
        if ($since) {
            $this->line('Desde: ' . $since);
        }
        $this->line('Audit rows totales: ' . $totalRows);
        $this->line('Rules únicas disparadas: ' . count($counts));
        $this->newLine();

        $this->line(sprintf('%-50s %-10s %6s %6s', 'rule_code', 'severity', 'count', '%'));
        $this->line(str_repeat('-', 75));
        foreach ($data as $row) {
            $this->line(sprintf(
                '%-50s %-10s %6d %5.1f%%',
                $row['rule_code'], $row['severity'], $row['count'], $row['pct_of_audit_rows'],
            ));
        }
        return 0;
    }
}
