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

    // Aliases that don't exist in the GIF repo → correct existing alias
    private array $aliasFixes = [
        'extension-de-cuadriceps-en-maquina'  => 'extension-de-piernas-en-maquina',
        'crunch-en-maquina'                    => 'crunch-sentado-en-maquina',
        'crunch-oblicuo-en-maquina'            => 'crunches-oblicuos-acostado',
        'patada-de-gluteo-en-cable'            => 'patada-trasera-en-polea',
        'kickback-de-gluteo'                   => 'patada-trasera-en-polea',
        'kickback'                             => 'patada-trasera-en-polea',
        'abduccion-de-cadera-en-maquina'       => 'abduccion-de-cadera-sentado-en-maquina',
        'plancha-con-soporte'                  => 'plancha-de-rodillas',
        'elevacion-de-piernas-colgado'         => 'elevacion-de-piernas-captain-chair',
    ];

    // Fix by exercise name when gif_url is null or points to a clearly wrong alias
    private array $nameFixes = [
        'patada'    => 'patada-trasera-en-polea',
        'kickback'  => 'patada-trasera-en-polea',
        'oblicuo'   => 'crunches-oblicuos-acostado',
        'plancha'   => 'plancha-de-rodillas',
    ];

    public function handle(): int
    {
        $plan = DB::table('assigned_plans')->where('id', 183)->first();
        if (! $plan) {
            $this->error('Plan 183 not found.');
            return 1;
        }

        $content = json_decode($plan->content, true);
        $listOnly = $this->option('list');
        $changed = 0;
        $seen = [];

        foreach ($content['semanas'] ?? [] as $si => &$semana) {
            foreach ($semana['dias'] ?? [] as $di => &$dia) {
                foreach ($dia['ejercicios'] ?? [] as $ei => &$ej) {
                    $nombre = $ej['nombre'] ?? '';
                    $url    = $ej['gif_url'] ?? '';

                    // Extract alias from URL
                    $alias = $url ? str_replace([self::BASE, '.gif'], '', $url) : '(sin gif_url)';

                    if ($listOnly) {
                        $key = $alias . '|' . $nombre;
                        if (! isset($seen[$key])) {
                            $this->line("  alias={$alias}  |  nombre={$nombre}");
                            $seen[$key] = true;
                        }
                        continue;
                    }

                    // Fix by URL alias
                    if ($url && isset($this->aliasFixes[$alias])) {
                        $newAlias       = $this->aliasFixes[$alias];
                        $ej['gif_url']  = self::BASE . $newAlias . '.gif';
                        $this->line("  URL fix: {$alias} → {$newAlias}  ({$nombre})");
                        $changed++;
                        continue;
                    }

                    // Fix by exercise name when gif_url is missing or unrecognized
                    if (! $url || $alias === '(sin gif_url)') {
                        $nombreNorm = mb_strtolower($nombre);
                        foreach ($this->nameFixes as $keyword => $newAlias) {
                            if (str_contains($nombreNorm, $keyword)) {
                                $ej['gif_url'] = self::BASE . $newAlias . '.gif';
                                $this->line("  Name fix: '{$nombre}' → {$newAlias}");
                                $changed++;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($listOnly) {
            $this->info('Listing done.');
            return 0;
        }

        if ($changed === 0) {
            $this->info('No broken GIF aliases found in plan 183.');
            return 0;
        }

        DB::table('assigned_plans')
            ->where('id', 183)
            ->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

        foreach (['client_plan_v3_96', 'wp:plan:96', 'wp:weekdays:96'] as $key) {
            Cache::forget($key);
        }

        $this->info("Fixed {$changed} GIF URL(s) in plan 183. Cache cleared.");
        return 0;
    }
}
