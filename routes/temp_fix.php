<?php
Route::get('/temp/fix-juliana-gifs-final', function () {
    try {
        $client = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if (!$client) return response()->json(['error' => 'Client not found']);
        $rise = \DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);
        $current = json_decode($rise->personalized_program, true);

        // Get real GIF filenames from exercise_aliases table
        $aliases = \DB::table('exercise_aliases')
            ->whereNotNull('gif_filename')
            ->get(['alias', 'gif_filename']);

        $gifLookup = [];
        foreach ($aliases as $a) {
            $gifLookup[mb_strtolower($a->alias)] = $a->gif_filename;
        }

        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

        // Keywords to search in exercise_aliases
        $keywordMap = [
            'Sentadilla con banda en rodillas' => ['squat', 'band squat', 'bodyweight squat'],
            'Sentadilla sumo con banda' => ['sumo squat', 'squat sumo', 'goblet squat'],
            'Zancadas alternas con banda en rodillas' => ['lunge', 'split squat'],
            'Step ups en silla o escalon' => ['step up', 'step-up'],
            'Extension de rodilla sentada con banda' => ['leg extension', 'knee extension'],
            'Press de hombros con superband' => ['shoulder press', 'military press', 'overhead press'],
            'Elevaciones laterales con banda' => ['lateral raise', 'dumbbell lateral'],
            'Extension de triceps con superband overhead' => ['tricep extension', 'overhead extension', 'cable extension'],
            'Dips en silla' => ['bench dip', 'dip', 'tricep dip'],
            'Crunch abdominal' => ['crunch', 'ab crunch'],
            'Plancha lateral' => ['side plank', 'plank'],
            'Hip thrust en suelo con banda en rodillas' => ['hip thrust', 'glute bridge', 'bridge'],
            'Puente de gluteo unilateral con banda' => ['glute bridge', 'hip thrust', 'single leg bridge'],
            'Kickback de gluteo con banda' => ['glute kickback', 'kickback', 'donkey kick'],
            'Clamshell con banda' => ['clamshell', 'clam', 'hip abduction'],
            'Fire hydrant con banda' => ['fire hydrant', 'hip abduction', 'clamshell'],
            'Sentadilla sumo pulso con banda' => ['sumo squat', 'pulse squat'],
            'Remo con superband' => ['row', 'cable row', 'seated row', 'bent over row'],
            'Pull apart con banda' => ['face pull', 'pull apart', 'rear delt'],
            'Remo a una mano con superband' => ['one arm row', 'dumbbell row', 'single arm row'],
            'Curl de biceps con superband' => ['bicep curl', 'dumbbell curl', 'curl'],
            'Curl martillo con superband' => ['hammer curl', 'dumbbell curl'],
            'Plancha abdominal isometrica' => ['plank', 'front plank'],
            'Peso muerto rumano con superband' => ['romanian deadlift', 'rdl', 'stiff leg'],
            'Curl femoral acostada con banda' => ['leg curl', 'hamstring curl', 'lying curl'],
            'Hip thrust con banda en rodillas' => ['hip thrust', 'glute bridge'],
            'Hip thrust con banda' => ['hip thrust', 'glute bridge'],
            'Good morning con superband' => ['good morning'],
            'Sentadilla bulgara con banda' => ['bulgarian split', 'split squat', 'lunge'],
            'Puente de gluteo con pausa' => ['glute bridge', 'hip thrust'],
            'Push ups (flexiones)' => ['push up', 'push-up', 'pushup'],
            'Plancha abdominal 60 seg' => ['plank', 'front plank'],
            'Sentadilla con banda' => ['squat', 'band squat'],
        ];

        $count = 0;
        $notFound = [];

        if (isset($current['plan_entrenamiento']['semanas'])) {
            foreach ($current['plan_entrenamiento']['semanas'] as &$semana) {
                foreach ($semana['dias'] as &$dia) {
                    foreach ($dia['ejercicios'] as &$ej) {
                        $name = $ej['nombre'];
                        // Normalize name (remove accents)
                        $normalized = str_replace(
                            ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'],
                            ['a','e','i','o','u','n','A','E','I','O','U','N'],
                            $name
                        );

                        $found = false;
                        $keywords = $keywordMap[$normalized] ?? $keywordMap[$name] ?? [];

                        foreach ($keywords as $kw) {
                            foreach ($gifLookup as $alias => $filename) {
                                if (stripos($alias, $kw) !== false) {
                                    $ej['gif_url'] = $base . $filename;
                                    $count++;
                                    $found = true;
                                    break 2;
                                }
                            }
                        }

                        if (!$found) {
                            $notFound[] = $name;
                            unset($ej['gif_url']);
                        }
                    }
                }
            }
            unset($semana, $dia, $ej);
        }

        \DB::table('rise_programs')->where('id', $rise->id)->update([
            'personalized_program' => json_encode($current),
        ]);

        return response()->json([
            'ok' => true,
            'gifs_mapped' => $count,
            'not_found' => array_unique($notFound),
            'total_aliases' => count($gifLookup),
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
    }
});
