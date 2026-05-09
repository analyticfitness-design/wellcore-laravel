<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDanielaGifs extends Command
{
    protected $signature   = 'wellcore:fix-daniela-gifs';
    protected $description = 'Fix broken GIF URLs in Daniela (client 96) training plan';

    // Aliases that DON'T exist in the GIF repo → map to the correct existing alias
    private array $fixes = [
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

    public function handle(): int
    {
        $plan = DB::table('assigned_plans')->where('id', 183)->first();
        if (! $plan) {
            $this->error('Plan 183 not found.');
            return 1;
        }

        $content  = json_decode($plan->content, true);
        $base     = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
        $changed  = 0;

        foreach ($content['semanas'] ?? [] as &$semana) {
            foreach ($semana['dias'] ?? [] as &$dia) {
                foreach ($dia['ejercicios'] ?? [] as &$ej) {
                    $url = $ej['gif_url'] ?? '';
                    if (! $url) continue;

                    // Extract current alias from URL
                    $alias = str_replace([$base, '.gif'], '', $url);

                    if (isset($this->fixes[$alias])) {
                        $newAlias      = $this->fixes[$alias];
                        $ej['gif_url'] = $base . $newAlias . '.gif';
                        $this->line("  Fixed: {$alias} → {$newAlias}");
                        $changed++;
                    }
                }
            }
        }

        if ($changed === 0) {
            $this->info('No broken GIF aliases found in plan 183.');
            return 0;
        }

        DB::table('assigned_plans')
            ->where('id', 183)
            ->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

        // Bust caches
        foreach (['client_plan_v3_96', 'wp:plan:96', 'wp:weekdays:96'] as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }

        $this->info("Fixed {$changed} GIF URL(s) in plan 183. Cache cleared.");
        return 0;
    }
}
