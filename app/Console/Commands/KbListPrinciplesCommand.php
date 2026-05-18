<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\Principle;
use Illuminate\Console\Command;

/**
 * kb:list-principles — lista principles en consola con filtros.
 *
 * Útil para inspección rápida sin abrir el MD exportado por kb:export-principles-md.
 *
 * Filtros combinables:
 *   --vertical=X   solo principles del vertical X
 *   --tag=X        solo principles con tag X
 *   --slug=X       buscar por slug (substring match)
 *   --search=X     búsqueda full-text en name + description_short
 *   --detail       muestra description_long + when_to_apply
 */
final class KbListPrinciplesCommand extends Command
{
    protected $signature = 'kb:list-principles
                            {--vertical= : filtra por vertical (entrenamiento|nutricion|suplementacion|habitos|ciclo)}
                            {--tag= : filtra por tag específico}
                            {--slug= : busca slug (substring match)}
                            {--search= : búsqueda full-text en name + description_short}
                            {--detail : muestra description_long + when_to_apply}';

    protected $description = 'Lista principles seedeados con filtros (vertical, tag, slug, search).';

    public function handle(): int
    {
        $query = Principle::query()->active()->orderBy('vertical')->orderBy('id');

        if ($vertical = $this->option('vertical')) {
            $query->where('vertical', $vertical);
        }
        if ($slugLike = $this->option('slug')) {
            $query->where('slug', 'like', "%$slugLike%");
        }
        if ($search = $this->option('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description_short', 'like', "%$search%");
            });
        }

        $principles = $query->get();

        // Filtro por tag — post-query porque tags es JSON
        if ($tag = $this->option('tag')) {
            $principles = $principles->filter(function (Principle $p) use ($tag) {
                $tags = $p->tags ?? [];
                return in_array($tag, $tags, true);
            })->values();
        }

        if ($principles->isEmpty()) {
            $this->warn('No hay principles que matcheen los filtros.');
            return 1;
        }

        $detail = (bool) $this->option('detail');
        $this->info('═══ ' . $principles->count() . ' principles ═══');

        $current = null;
        foreach ($principles as $p) {
            if ($p->vertical !== $current) {
                $this->newLine();
                $this->info("── vertical: {$p->vertical} ──");
                $current = $p->vertical;
            }
            $tagsStr = is_array($p->tags) ? implode(',', $p->tags) : '';
            $this->line(sprintf('  · %s', $p->slug));
            $this->line("    name: {$p->name}");
            $this->line("    tags: $tagsStr");
            $this->line("    short: {$p->description_short}");

            if ($detail) {
                if (! empty($p->description_long)) {
                    $this->line('    long:  ' . mb_substr($p->description_long, 0, 200) . (mb_strlen($p->description_long) > 200 ? '...' : ''));
                }
                if (! empty($p->when_to_apply)) {
                    $this->line('    when:  ' . mb_substr($p->when_to_apply, 0, 200) . (mb_strlen($p->when_to_apply) > 200 ? '...' : ''));
                }
            }
            $this->newLine();
        }

        return 0;
    }
}
