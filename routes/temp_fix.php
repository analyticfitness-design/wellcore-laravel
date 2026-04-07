<?php
// Fix specific training plan issues for Juliana and Adriana
Route::get('/temp/fix-training-corrections', function () {
    try {
        $results = [];
        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

        // ===== JULIANA: Add 20 min cardio to all days except Saturday =====
        $juliana = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if ($juliana) {
            $rise = \DB::table('rise_programs')->where('client_id', $juliana->id)->first();
            if ($rise) {
                $data = json_decode($rise->personalized_program, true);
                $cardioAdded = 0;
                foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
                    foreach ($semana['dias'] as &$dia) {
                        $nombre = mb_strtolower($dia['nombre'] ?? $dia['dia'] ?? '');
                        // Add cardio if not Saturday and doesn't already have it
                        if (stripos($nombre, 'sabado') === false && stripos($nombre, 'full body') === false) {
                            $dia['cardio'] = '20 minutos de rumbaterapia o salto de lazo al finalizar la sesión de pesas';
                            $cardioAdded++;
                        } else {
                            // Saturday: no cardio
                            $dia['cardio'] = null;
                        }
                    }
                }
                unset($semana, $dia);
                \DB::table('rise_programs')->where('id', $rise->id)->update([
                    'personalized_program' => json_encode($data),
                ]);
                $results[] = ['client' => 'Juliana Portilla', 'action' => 'cardio added to non-Saturday days', 'days_fixed' => $cardioAdded];
            }
        }

        // ===== ADRIANA: Fix Wednesday name + add Bulgarian squat + restore Saturday full body =====
        $adriana = \App\Models\Client::where('email', 'asarmientoslm@gmail.com')->first();
        if ($adriana) {
            $rise = \DB::table('rise_programs')->where('client_id', $adriana->id)->first();
            if ($rise) {
                $data = json_decode($rise->personalized_program, true);
                $fixes = [];

                foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
                    $hasSaturday = false;
                    foreach ($semana['dias'] as &$dia) {
                        $nombre = mb_strtolower($dia['nombre'] ?? '');
                        $diaName = mb_strtolower($dia['dia'] ?? '');

                        // Fix Wednesday: rename Isquios to Glúteos + add Bulgarian squat
                        if (stripos($nombre, 'miercoles') !== false || stripos($diaName, 'miercoles') !== false || stripos($nombre, 'miércoles') !== false) {
                            // Fix name if it says isquios
                            if (stripos($dia['nombre'], 'isquio') !== false || stripos($dia['nombre'], 'Isquio') !== false) {
                                $dia['nombre'] = str_ireplace(['Isquios', 'isquios', 'Isquiotibiales', 'isquiotibiales'], 'Glúteos', $dia['nombre']);
                                $fixes[] = 'Wednesday renamed to Glúteos';
                            }

                            // Check if Bulgarian squat already exists
                            $hasBulgarian = false;
                            foreach ($dia['ejercicios'] as $ej) {
                                if (stripos($ej['nombre'], 'bulgar') !== false) {
                                    $hasBulgarian = true;
                                    break;
                                }
                            }

                            // Add Bulgarian squat if not present
                            if (!$hasBulgarian) {
                                $dia['ejercicios'][] = [
                                    'nombre' => 'Sentadilla Búlgara con Mancuernas',
                                    'series' => 4,
                                    'repeticiones' => '10-12 por pierna',
                                    'descanso' => '90s',
                                    'notas' => 'Pie trasero en banco. Baja hasta que la rodilla trasera casi toque el suelo. Empuja con el talón delantero. Mantén torso erguido.',
                                    'gif_url' => $base . '04101301-Dumbbell-Single-Leg-Split-Squat_Thighs_720.gif',
                                ];
                                $fixes[] = 'Bulgarian squat added to Wednesday';
                            }
                        }

                        // Check if Saturday exists
                        if (stripos($nombre, 'sabado') !== false || stripos($diaName, 'sabado') !== false || stripos($nombre, 'sábado') !== false) {
                            $hasSaturday = true;
                        }
                    }

                    // Add Saturday Full Body if missing
                    if (!$hasSaturday) {
                        $semana['dias'][] = [
                            'dia' => 'Sábado',
                            'nombre' => 'Sábado — Full Body',
                            'ejercicios' => [
                                ['nombre' => 'Sentadilla con Mancuernas', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Mancuernas a los lados. Baja hasta paralela.', 'gif_url' => $base . '17601301-Dumbbell-Goblet-Squat_Thighs-FIX_720.gif'],
                                ['nombre' => 'Press de Banca con Mancuernas', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Codos a 45 grados. Baja controlado.', 'gif_url' => $base . '00251301-Barbell-Bench-Press_Chest-FIX_720.gif'],
                                ['nombre' => 'Remo con Mancuerna', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Espalda neutra. Jala el codo hacia atrás.', 'gif_url' => $base . '02931301-Dumbbell-Bent-Over-Row_Back-FIX_720.gif'],
                                ['nombre' => 'Press de Hombro con Mancuerna', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Sentada en banco. Empuja arriba sin bloquear codos.', 'gif_url' => $base . '02871301-Dumbbell-Arnold-Press-II_Shoulders_720.gif'],
                                ['nombre' => 'Hip Thrust', 'series' => 3, 'repeticiones' => '15-20', 'descanso' => '60s', 'notas' => 'Espalda en banco. Empuja cadera arriba. Contrae glúteo 2 seg.', 'gif_url' => $base . '10601301-Barbell-Hip-Thrust_Hips_720.gif'],
                                ['nombre' => 'Curl de Bíceps con Mancuerna', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '45s', 'notas' => 'Codos fijos. Alterna brazos.', 'gif_url' => $base . '02851301-Dumbbell-Alternate-Biceps-Curl_Upper-Arms_720.gif'],
                                ['nombre' => 'Plancha Abdominal', 'series' => 3, 'repeticiones' => '45-60 seg', 'descanso' => '45s', 'notas' => 'Cierra la semana con core fuerte.', 'gif_url' => $base . '04631301-Front-Plank_waist-FIX_720.gif'],
                            ],
                            'cardio' => null,
                        ];
                        $fixes[] = 'Saturday Full Body added';
                    }
                }
                unset($semana, $dia);

                \DB::table('rise_programs')->where('id', $rise->id)->update([
                    'personalized_program' => json_encode($data),
                ]);
                $results[] = ['client' => 'Adriana Sarmiento', 'fixes' => $fixes];
            }
        }

        return response()->json(['ok' => true, 'results' => $results]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
    }
});
