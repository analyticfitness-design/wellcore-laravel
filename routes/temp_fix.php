<?php
// Fix Adriana's Wednesday name + verify Bulgarian squat
Route::get('/temp/fix-adriana-wednesday', function () {
    try {
        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
        $adriana = \App\Models\Client::where('email', 'asarmientoslm@gmail.com')->first();
        if (!$adriana) return response()->json(['error' => 'Adriana not found']);

        $rise = \DB::table('rise_programs')->where('client_id', $adriana->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);

        $data = json_decode($rise->personalized_program, true);
        $fixes = [];
        $wednesdayNames = [];

        foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
            foreach ($semana['dias'] as &$dia) {
                $nombre = $dia['nombre'] ?? '';
                $diaField = $dia['dia'] ?? '';

                // Find Wednesday by day name or position (3rd day = index 2)
                $isWednesday = (stripos($nombre, 'miercoles') !== false || stripos($nombre, 'miércoles') !== false
                    || stripos($diaField, 'miercoles') !== false || stripos($diaField, 'miércoles') !== false);

                if ($isWednesday) {
                    $wednesdayNames[] = $nombre;

                    // Force rename to Glúteos if it doesn't say gluteos
                    if (stripos($nombre, 'gluteo') === false && stripos($nombre, 'glúteo') === false) {
                        // Replace whatever muscle name it has with Glúteos
                        $dia['nombre'] = preg_replace('/—\s*.*$/', '— Glúteos', $nombre);
                        if ($dia['nombre'] === $nombre) {
                            // If regex didn't match, just append
                            $dia['nombre'] = $nombre . ' (Glúteos)';
                        }
                        $fixes[] = 'Wednesday renamed: "' . $nombre . '" → "' . $dia['nombre'] . '"';
                    }

                    // Add Bulgarian squat if not present
                    $hasBulgarian = false;
                    foreach ($dia['ejercicios'] as $ej) {
                        if (stripos($ej['nombre'], 'bulgar') !== false || stripos($ej['nombre'], 'split squat') !== false) {
                            $hasBulgarian = true;
                            break;
                        }
                    }

                    if (!$hasBulgarian) {
                        $dia['ejercicios'][] = [
                            'nombre' => 'Sentadilla Búlgara con Mancuernas',
                            'series' => 4,
                            'repeticiones' => '10-12 por pierna',
                            'descanso' => '90s',
                            'notas' => 'Pie trasero en banco. Baja hasta que la rodilla trasera casi toque el suelo. Empuja con el talón delantero.',
                            'gif_url' => $base . '04101301-Dumbbell-Single-Leg-Split-Squat_Thighs_720.gif',
                        ];
                        $fixes[] = 'Bulgarian squat added to Wednesday';
                    }
                }
            }
        }
        unset($semana, $dia);

        \DB::table('rise_programs')->where('id', $rise->id)->update([
            'personalized_program' => json_encode($data),
        ]);

        return response()->json([
            'ok' => true,
            'wednesday_names_found' => $wednesdayNames,
            'fixes' => $fixes,
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
