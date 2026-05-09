<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FixDanielaGifs extends Command
{
    protected $signature   = 'wellcore:fix-daniela-gifs';
    protected $description = 'Swap sentadilla con barra → sentadilla hack in plan 183';

    public function handle(): int
    {
        $plan = DB::table('assigned_plans')->where('id', 183)->first();
        if (! $plan) { $this->error('Plan 183 not found.'); return 1; }

        $content = json_decode($plan->content, true);
        $base    = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
        $changed = 0;

        foreach ($content['semanas'] as $si => &$semana) {
            foreach ($semana['dias'] as $di => &$dia) {
                foreach ($dia['ejercicios'] as $ei => &$ej) {
                    if (str_contains($ej['nombre'] ?? '', 'Sentadilla con barra')) {
                        $ej['nombre']   = 'Sentadilla Hack';
                        $ej['gif_url']  = $base . 'sentadilla-hacka.gif';
                        $content['semanas'][$si]['dias'][$di]['ejercicios'][$ei]['nombre']  = 'Sentadilla Hack';
                        $content['semanas'][$si]['dias'][$di]['ejercicios'][$ei]['gif_url'] = $base . 'sentadilla-hacka.gif';
                        $changed++;
                        $this->line("  Semana {$si} → Día {$di} → ejercicio {$ei} actualizado.");
                    }
                }
            }
        }

        if ($changed === 0) { $this->info('No matches found.'); return 0; }

        $rows = DB::table('assigned_plans')
            ->where('id', 183)
            ->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

        foreach (['client_plan_v3_96', 'wp:plan:96', 'wp:weekdays:96'] as $key) {
            Cache::forget($key);
        }

        $this->info("Changed: {$changed}, DB rows: {$rows}. Cache cleared.");
        return 0;
    }
}
