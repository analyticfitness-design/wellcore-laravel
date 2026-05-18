<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mapeo manual alias canónico → filename real en el repo GitHub.
 *
 * El repo `analyticfitness-design/wellcore-exercise-gifs` tiene 265 .gifs.
 * Nuestros aliases canónicos son nombres "semánticos" que no siempre matchean
 * el filename exacto del repo (ej. "hip-thrust" vs "hipthrust-con-barra.gif").
 *
 * Este seeder llena exercise_metadata.gif_filename con el nombre real verificado.
 * Solo se aplica a aliases que NO matchean por convención {alias}.gif.
 *
 * Idempotente: upsert por alias.
 * Solo wellcore_kb local. NO toca producción.
 *
 * Si un alias no aparece acá, ExerciseMetadata->gifUrl() usa "{alias}.gif" por default.
 */
final class ExerciseGifFilenameOverrideSeeder extends Seeder
{
    public function run(): void
    {
        // alias canónico → filename real (verificado contra el repo el 2026-05-17)
        $overrides = [
            'press-banca-mancuernas' => 'press-de-banca-con-mancuernas.gif',
            'press-banca-maquina' => 'press-de-pecho-en-maquina.gif',
            'press-inclinado-mancuernas' => 'press-banca-inclinado-con-barra.gif', // sustituto: inclinado con barra
            'fondos-paralelas' => 'fondos-de-pecho-en-maquina.gif', // sustituto: máquina (no hay paralelas en repo)
            'dominadas-asistidas' => 'dominadas.gif', // sustituto: variante sin asistencia
            'jalon-polea-alta' => 'jalon-en-polea.gif',
            'remo-barra' => 'remo-con-barra.gif',
            'remo-mancuerna-una-mano' => 'remo-con-mancuerna-a-una mano.gif',
            'remo-sentado-maquina' => 'remo-sentado-en-maquina.gif',
            'sentadilla-barra' => 'sentadilla-con-barra.gif',
            'sentadilla-frontal' => 'sentadilla-frontal-en-landmine.gif', // única variante frontal en repo
            'sentadilla-bulgara' => 'sentadilla-bulgara-mancuerna.gif',
            'peso-muerto-convencional' => 'peso-muerto-con-barra.gif',
            'peso-muerto-rumano' => 'peso-muerto-rumano-con-barra.gif',
            'prensa-piernas' => 'prensa-de-piernas-cerrado.gif',
            'hip-thrust' => 'hipthrust-con-barra.gif',
            'zancadas-mancuernas' => 'zancada-frontal-con-mancuerna.gif',
            'press-militar-barra' => 'press-militar-con-barra-de-pie.gif',
            'press-militar-mancuernas' => 'press-militar-con-barra-de-pie.gif', // sustituto: único press militar en repo
            'elevaciones-laterales-mancuerna' => 'elevacion-lateral-con-mancuerna.gif',
            'curl-biceps-mancuerna' => 'curl-biceps-con-mancuerna.gif',
            'curl-biceps-barra-z' => 'curl-biceps-barra-ez.gif',
            'extension-triceps-polea' => 'extension-de-triceps-en-polea-con-cuerda.gif',
            'extension-femoral-acostado' => 'curl-femoral-acostado-en-maquina.gif',
            'extension-cuadriceps' => 'extension-de-piernas-en-maquina.gif',
            'plancha-frontal' => 'plancha-abdominal.gif',
            'abdominales-rueda' => 'abdominales-con-rueda-de-pie.gif',
        ];

        $now = now()->toDateTimeString();
        $updated = 0;
        $skipped = 0;

        foreach ($overrides as $alias => $filename) {
            $affected = DB::connection('kb')
                ->table('exercise_metadata')
                ->where('alias', $alias)
                ->update([
                    'gif_filename' => $filename,
                    'updated_at' => $now,
                ]);

            if ($affected > 0) {
                $updated++;
            } else {
                $skipped++;
                $this->command?->warn("Override skipped (alias not found): $alias");
            }
        }

        $this->command?->info("Updated $updated gif_filename overrides ($skipped skipped).");
    }
}
