<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * kb:counts — versión rápida de kb:status, solo cuenta de tablas.
 *
 * Útil para chequeos rápidos durante seeding o cuando solo necesitás el número.
 * No verifica integridad ni warnings — usar plan:health-check para eso.
 *
 * Por defecto output tabular. `--json` para parsear.
 */
final class KbCountsCommand extends Command
{
    protected $signature = 'kb:counts {--json : output JSON}';

    protected $description = 'Cuenta rápida de rows por tabla en wellcore_kb (más rápido que kb:status).';

    private const TABLES = [
        'methodologies',
        'decision_rules',
        'lint_rules',
        'exercise_metadata',
        'exercise_aliases',
        'principles',
        'nutrition_foods',
        'supplement_stacks',
        'ciclo_fases',
        'composed_plans',
        'gif_url_status',
    ];

    public function handle(): int
    {
        $counts = [];
        foreach (self::TABLES as $t) {
            try {
                $counts[$t] = DB::connection('kb')->table($t)->count();
            } catch (\Throwable) {
                $counts[$t] = null;
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode($counts, JSON_PRETTY_PRINT));
            return 0;
        }

        foreach ($counts as $t => $c) {
            $this->line(sprintf('%-25s %s', $t, $c === null ? '—' : $c));
        }
        return 0;
    }
}
