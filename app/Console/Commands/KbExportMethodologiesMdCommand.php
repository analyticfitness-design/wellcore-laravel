<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\Methodology;
use Illuminate\Console\Command;

/**
 * kb:export-methodologies-md — exporta wellcore_kb.methodologies a Markdown.
 *
 * Equivalente de kb:export-principles-md pero para methodologies. Genera un
 * MD versionable que sirve para:
 *   - revisión humana de Daniel
 *   - documentación viva del motor
 *   - diff entre snapshots para detectar cambios silenciosos en metodología
 *
 * Default output: docs/wellcore-engine-v2/methodologies.md
 */
final class KbExportMethodologiesMdCommand extends Command
{
    protected $signature = 'kb:export-methodologies-md
                            {--path= : ruta de output (default docs/wellcore-engine-v2/methodologies.md)}';

    protected $description = 'Exporta methodologies a Markdown versionable (1 sección por vertical).';

    public function handle(): int
    {
        $methodologies = Methodology::orderBy('vertical')->orderBy('slug')->get();
        if ($methodologies->isEmpty()) {
            $this->warn('Sin methodologies para exportar.');
            return 0;
        }

        $path = (string) ($this->option('path')
            ?: base_path('docs/wellcore-engine-v2/methodologies.md'));

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        $md = $this->buildMd($methodologies);
        file_put_contents($path, $md);

        $this->info("✓ Exportado: $path");
        $this->line('Methodologies: ' . $methodologies->count());
        return 0;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection<int, Methodology> $methodologies
     */
    private function buildMd($methodologies): string
    {
        $byVertical = $methodologies->groupBy('vertical');
        $out = "# Methodologies — Motor v2\n\n";
        $out .= "> Auto-generado por `php artisan kb:export-methodologies-md`. NO editar a mano — editar el seeder.\n\n";
        $out .= 'Total: ' . $methodologies->count() . ' methodologies en ' . $byVertical->count() . " verticales.\n\n";
        $out .= "## Resumen por vertical\n\n";
        $out .= "| Vertical | Count | Slugs |\n";
        $out .= "|----------|-------|-------|\n";
        foreach ($byVertical as $vertical => $items) {
            $slugs = $items->pluck('slug')->implode(', ');
            $out .= "| $vertical | " . $items->count() . " | $slugs |\n";
        }
        $out .= "\n---\n\n";

        foreach ($byVertical as $vertical => $items) {
            $out .= "## $vertical\n\n";
            foreach ($items as $m) {
                $out .= "### `{$m->slug}` — {$m->name}\n\n";
                $out .= "- **vertical**: {$m->vertical}\n";
                $out .= "- **target_days**: {$m->target_days_min}-{$m->target_days_max}\n";
                $out .= '- **target_level**: ' . $this->stringify($m->target_level) . "\n";
                $out .= '- **target_goal**: ' . $this->stringify($m->target_goal) . "\n";
                $out .= '- **periodization_pattern**: ' . $this->stringify($m->periodization_pattern) . "\n";
                $out .= "- **status**: {$m->status}\n\n";
                if ($m->description) {
                    $out .= "**Descripción:**\n\n{$m->description}\n\n";
                }
                $out .= "---\n\n";
            }
        }
        return $out;
    }

    private function stringify(mixed $value): string
    {
        if (is_array($value)) {
            // Si es asociativo, JSON inline. Si es lista plana, join.
            $isList = array_keys($value) === range(0, count($value) - 1);
            if ($isList) {
                return implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), $value));
            }
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return (string) ($value ?? '');
    }
}
