<?php
// TEMP: Add gif_url to ALL RISE programs using exercise_aliases DB
// RULES: Only adds gif_url field. Does NOT modify any other field.
Route::get('/temp/fix-all-rise-gifs', function () {
    try {
        // Get all GIF filenames from exercise_aliases
        $aliases = \DB::table('exercise_aliases')
            ->whereNotNull('gif_filename')
            ->get(['alias', 'gif_filename']);

        $gifLookup = [];
        foreach ($aliases as $a) {
            $gifLookup[mb_strtolower(trim($a->alias))] = $a->gif_filename;
        }

        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

        // Get ALL active rise programs
        $programs = \DB::table('rise_programs')
            ->whereIn('status', ['active', 'activo'])
            ->get(['id', 'client_id', 'personalized_program']);

        $results = [];

        foreach ($programs as $prog) {
            $data = json_decode($prog->personalized_program, true);
            if (!$data || !isset($data['plan_entrenamiento']['semanas'])) {
                $results[] = ['id' => $prog->id, 'client_id' => $prog->client_id, 'skipped' => 'no semanas'];
                continue;
            }

            $count = 0;
            $already = 0;

            foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
                if (!isset($semana['dias'])) continue;
                foreach ($semana['dias'] as &$dia) {
                    if (!isset($dia['ejercicios'])) continue;
                    foreach ($dia['ejercicios'] as &$ej) {
                        // Skip if already has gif_url
                        if (!empty($ej['gif_url'])) {
                            $already++;
                            continue;
                        }

                        $name = mb_strtolower(trim($ej['nombre'] ?? ''));
                        // Remove accents for matching
                        $normalized = str_replace(
                            ['á','é','í','ó','ú','ñ'],
                            ['a','e','i','o','u','n'],
                            $name
                        );

                        $matched = false;

                        // Try exact match
                        if (isset($gifLookup[$name])) {
                            $ej['gif_url'] = $base . $gifLookup[$name];
                            $count++;
                            $matched = true;
                        } elseif (isset($gifLookup[$normalized])) {
                            $ej['gif_url'] = $base . $gifLookup[$normalized];
                            $count++;
                            $matched = true;
                        }

                        // Try partial match (exercise name contains alias or vice versa)
                        if (!$matched) {
                            foreach ($gifLookup as $alias => $filename) {
                                if (strlen($alias) > 4 && (
                                    stripos($normalized, $alias) !== false ||
                                    stripos($alias, $normalized) !== false
                                )) {
                                    $ej['gif_url'] = $base . $filename;
                                    $count++;
                                    $matched = true;
                                    break;
                                }
                            }
                        }

                        // Try keyword matching for common exercises
                        if (!$matched) {
                            $keywords = [
                                'sentadilla' => 'squat', 'squat' => 'squat',
                                'press militar' => 'military press', 'press hombro' => 'shoulder press',
                                'curl biceps' => 'bicep curl', 'curl femoral' => 'leg curl',
                                'peso muerto' => 'deadlift', 'hip thrust' => 'hip thrust',
                                'remo' => 'row', 'plancha' => 'plank',
                                'elevacion lateral' => 'lateral raise', 'elevaciones laterales' => 'lateral raise',
                                'extension triceps' => 'tricep', 'press banca' => 'bench press',
                                'zancada' => 'lunge', 'good morning' => 'good morning',
                                'crunch' => 'crunch', 'abdominal' => 'crunch',
                                'push up' => 'push up', 'flexion' => 'push up',
                                'jalon' => 'pulldown', 'pull' => 'pull',
                                'face pull' => 'face pull', 'kickback' => 'kickback',
                                'gluteo' => 'glute', 'bridge' => 'bridge',
                                'step up' => 'step', 'extension pierna' => 'leg extension',
                                'extension rodilla' => 'leg extension',
                                'dip' => 'dip', 'fondos' => 'dip',
                                'clamshell' => 'abduction', 'abduccion' => 'abduction',
                                'fire hydrant' => 'abduction',
                            ];

                            foreach ($keywords as $es => $en) {
                                if (stripos($normalized, $es) !== false) {
                                    foreach ($gifLookup as $alias => $filename) {
                                        if (stripos($alias, $en) !== false) {
                                            $ej['gif_url'] = $base . $filename;
                                            $count++;
                                            $matched = true;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            unset($semana, $dia, $ej);

            // Only update if we added new GIFs
            if ($count > 0) {
                \DB::table('rise_programs')->where('id', $prog->id)->update([
                    'personalized_program' => json_encode($data),
                ]);
            }

            $clientName = \App\Models\Client::where('id', $prog->client_id)->value('name') ?? 'Unknown';
            $results[] = [
                'id' => $prog->id,
                'client' => $clientName,
                'new_gifs' => $count,
                'already_had' => $already,
            ];
        }

        return response()->json([
            'ok' => true,
            'programs_processed' => count($results),
            'total_aliases' => count($gifLookup),
            'results' => $results,
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
    }
});
