<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FixDanielaGifs extends Command
{
    protected $signature   = 'wellcore:fix-daniela-gifs {--list : Only list current aliases without fixing}';
    protected $description = 'Fix broken GIF URLs in Daniela (client 96) training plan';

    private const BASE = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

    // Name-based fixes take priority (checked first by substring in exercise nombre)
    private array $nameFixes = [
        'oblicuo'   => 'crunches-oblicuos-acostado',
        'patada'    => 'patada-trasera-en-polea',
        'kickback'  => 'patada-trasera-en-polea',
    ];

    // Alias-based fixes (applied when name fix didn't match)
    private array $aliasFixes = [
        'extension-de-cuadriceps-en-maquina'  => 'extension-de-piernas-en-maquina',
        'crunch-en-maquina'                    => 'crunch-sentado-en-maquina',
        'abduccion-de-cadera-en-maquina'       => 'abduccion-de-cadera-sentado-en-maquina',
        'plancha-con-soporte'                  => 'plancha-abdominal',
        'plancha'                              => 'plancha-abdominal',
        'elevacion-de-piernas-colgado'         => 'elevacion-de-piernas-captain-chair',
        'patada-gluteo-cable'                  => 'patada-trasera-en-polea',
        'patada-de-gluteo-en-cable'            => 'patada-trasera-en-polea',
        'kickback'                             => 'patada-trasera-en-polea',
    ];

    // GIF aliases confirmed to exist in the GitHub repo
    private array $knownGood = [
        'extension-de-piernas-en-maquina', 'crunch-sentado-en-maquina',
        'crunches-oblicuos-acostado', 'patada-trasera-en-polea',
        'abduccion-de-cadera-sentado-en-maquina', 'plancha-abdominal',
        'elevacion-de-piernas-captain-chair', 'plancha-de-rodillas',
    ];

    public function handle(): int
    {
        $plan = DB::table('assigned_plans')->where('id', 183)->first();
        if (! $plan) {
            $this->error('Plan 183 not found.');
            return 1;
        }

        $content  = json_decode($plan->content, true);
        $listOnly = $this->option('list');
        $changed  = 0;
        $seen     = [];

        foreach ($content['semanas'] as $si => &$semana) {
            foreach ($semana['dias'] as $di => &$dia) {
                foreach ($dia['ejercicios'] as $ei => &$ej) {
                    $nombre = $ej['nombre'] ?? '';
                    $url    = $ej['gif_url'] ?? '';
                    $alias  = $url ? basename(str_replace('.gif', '', $url)) : '(none)';

                    if ($listOnly) {
                        $key = $alias . '|' . $nombre;
                        if (! isset($seen[$key])) {
                            $broken = ! in_array($alias, $this->knownGood, true);
                            $mark   = $broken ? ' *** BROKEN' : '';
                            $this->line("  {$alias}  |  {$nombre}{$mark}");
                            $seen[$key] = true;
                        }
                        continue;
                    }

                    // 1. Name-based fix (higher priority — distinguishes exercises sharing the same alias)
                    $nombreNorm = mb_strtolower($nombre);
                    foreach ($this->nameFixes as $keyword => $newAlias) {
                        if (str_contains($nombreNorm, $keyword)) {
                            $old          = $alias;
                            $ej['gif_url'] = self::BASE . $newAlias . '.gif';
                            $content['semanas'][$si]['dias'][$di]['ejercicios'][$ei]['gif_url'] = self::BASE . $newAlias . '.gif';
                            $this->line("  [name] {$old} → {$newAlias}  ({$nombre})");
                            $changed++;
                            continue 2; // next ejercicio
                        }
                    }

                    // 2. Alias-based fix
                    if (isset($this->aliasFixes[$alias])) {
                        $newAlias      = $this->aliasFixes[$alias];
                        $old           = $alias;
                        $ej['gif_url'] = self::BASE . $newAlias . '.gif';
                        $content['semanas'][$si]['dias'][$di]['ejercicios'][$ei]['gif_url'] = self::BASE . $newAlias . '.gif';
                        $this->line("  [alias] {$old} → {$newAlias}  ({$nombre})");
                        $changed++;
                    }
                }
            }
        }

        if ($listOnly) {
            $this->info('Listing done.');
            return 0;
        }

        if ($changed === 0) {
            $this->info('No broken GIF aliases found.');
            return 0;
        }

        $newJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $rows    = DB::table('assigned_plans')->where('id', 183)->update(['content' => $newJson]);
        $this->info("DB rows updated: {$rows}");

        foreach (['client_plan_v3_96', 'wp:plan:96', 'wp:weekdays:96'] as $key) {
            Cache::forget($key);
        }

        $this->info("Fixed {$changed} GIF URL(s). Cache cleared.");
        return 0;
    }
}
