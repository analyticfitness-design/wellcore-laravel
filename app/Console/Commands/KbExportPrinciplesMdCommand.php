<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\Principle;
use Illuminate\Console\Command;

/**
 * kb:export-principles-md — exporta los principles seedeados a un MD versionable.
 *
 * Útil para:
 *   - Revisión humana del catálogo (Daniel/coaches lo leen como doc)
 *   - Versionar en git como changelog del catálogo
 *   - Compartir con stakeholders no técnicos
 *
 * Output: docs/wellcore-engine-v2/principles-catalog.md
 *   - Header con timestamp + count
 *   - Agrupado por vertical
 *   - Por cada principle: slug, name, description_long, when_to_apply, example_usage
 *
 * NO toca producción.
 */
final class KbExportPrinciplesMdCommand extends Command
{
    protected $signature = 'kb:export-principles-md
                            {--out= : path output (default = docs/wellcore-engine-v2/principles-catalog.md)}
                            {--vertical= : exportar solo una vertical}';

    protected $description = 'Exporta wellcore_kb.principles a Markdown legible (para revisión humana).';

    public function handle(): int
    {
        $verticalFilter = $this->option('vertical');
        $defaultOut = base_path('docs/wellcore-engine-v2/principles-catalog.md');
        $outPath = $this->option('out') ?? $defaultOut;

        $outDir = dirname($outPath);
        if (! is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $query = Principle::query()->active()->orderBy('vertical')->orderBy('id');
        if ($verticalFilter !== null) {
            $query->where('vertical', $verticalFilter);
        }
        $principles = $query->get();

        if ($principles->isEmpty()) {
            $this->warn('No hay principles activos para exportar.');
            return 1;
        }

        $md = $this->buildMd($principles, $verticalFilter);
        file_put_contents($outPath, $md);

        $sizeKb = round(strlen($md) / 1024, 1);
        $relPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $outPath);
        $relPath = str_replace('\\', '/', $relPath);

        $this->info("✓ Principles exportados a: $relPath");
        $this->line('  · Total principles: ' . $principles->count());
        $this->line('  · Tamaño: ' . $sizeKb . ' KB');
        $byVertical = $principles->groupBy('vertical')->map->count();
        foreach ($byVertical as $v => $c) {
            $this->line("  · $v: $c");
        }

        return 0;
    }

    /**
     * @param \Illuminate\Support\Collection<int, Principle> $principles
     */
    private function buildMd($principles, ?string $verticalFilter): string
    {
        $now = now()->toIso8601String();
        $total = $principles->count();
        $title = $verticalFilter ? "Principles — vertical=$verticalFilter" : 'Catálogo de Principles motor v2';

        $md = "# $title\n\n";
        $md .= "> Generado automáticamente: `$now`  \n";
        $md .= "> Total principles: **$total**  \n";
        $md .= "> Fuente: `wellcore_kb.principles`  \n";
        $md .= "> Comando: `php artisan kb:export-principles-md`\n\n";
        $md .= "---\n\n";
        $md .= "Estos principles son inyectados automáticamente por el motor v2 en `notas_coach` y `tips[]` de los planes generados. Cada plan recibe los 3 principles más relevantes según vertical + tags del cliente (level, goal, condiciones especiales).\n\n";
        $md .= "Scoring: vertical match +20, tag overlap +5 cada, fundamental +3.\n\n";
        $md .= "---\n\n";

        $byVertical = $principles->groupBy('vertical');
        $verticalOrder = ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'];

        // TOC
        $md .= "## Índice por vertical\n\n";
        foreach ($verticalOrder as $v) {
            if (! $byVertical->has($v)) {
                continue;
            }
            $count = $byVertical->get($v)->count();
            $md .= "- [**$v** ($count)](#vertical-$v)\n";
        }
        $md .= "\n---\n\n";

        // Sections
        foreach ($verticalOrder as $vertical) {
            if (! $byVertical->has($vertical)) {
                continue;
            }
            $verticalsPrincipals = $byVertical->get($vertical);
            $md .= "## Vertical: `$vertical` ({$verticalsPrincipals->count()} principles)\n";
            $md .= "<a name=\"vertical-$vertical\"></a>\n\n";

            foreach ($verticalsPrincipals as $p) {
                $md .= $this->buildPrincipleSection($p);
            }

            $md .= "---\n\n";
        }

        $md .= "\n*Fin del catálogo.*\n";
        return $md;
    }

    private function buildPrincipleSection(Principle $p): string
    {
        $tags = is_array($p->tags) ? implode(', ', $p->tags) : '';
        $md = "### `{$p->slug}` — {$p->name}\n\n";
        $md .= "**Tags**: `$tags`\n\n";
        $md .= "**Resumen**: {$p->description_short}\n\n";

        if (! empty($p->description_long)) {
            $md .= "**Descripción completa**:\n\n";
            $md .= "{$p->description_long}\n\n";
        }
        if (! empty($p->when_to_apply)) {
            $md .= "**Cuándo aplicar**:\n\n";
            $md .= "{$p->when_to_apply}\n\n";
        }
        if (! empty($p->example_usage)) {
            $md .= "**Ejemplo de uso**:\n\n";
            $md .= "> {$p->example_usage}\n\n";
        }
        $md .= "---\n\n";
        return $md;
    }
}
