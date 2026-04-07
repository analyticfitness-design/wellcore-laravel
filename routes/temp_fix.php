<?php
// Debug: show Adriana's day names to find Wednesday
Route::get('/temp/debug-adriana-days', function () {
    $adriana = \App\Models\Client::where('email', 'asarmientoslm@gmail.com')->first();
    if (!$adriana) return response()->json(['error' => 'not found']);
    $rise = \DB::table('rise_programs')->where('client_id', $adriana->id)->first();
    if (!$rise) return response()->json(['error' => 'no rise']);
    $data = json_decode($rise->personalized_program, true);

    $days = [];
    $week1 = $data['plan_entrenamiento']['semanas'][0] ?? null;
    if ($week1) {
        foreach ($week1['dias'] as $i => $dia) {
            $days[] = [
                'index' => $i,
                'dia' => $dia['dia'] ?? 'N/A',
                'nombre' => $dia['nombre'] ?? 'N/A',
                'exercises' => count($dia['ejercicios'] ?? []),
                'cardio' => $dia['cardio'] ?? null,
            ];
        }
    }
    return response()->json([
        'total_weeks' => count($data['plan_entrenamiento']['semanas'] ?? []),
        'week1_days' => $days,
    ]);
});

// Fix Adriana by index (3rd day = index 2 is Wednesday)
Route::get('/temp/fix-adriana-by-index', function () {
    $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
    $adriana = \App\Models\Client::where('email', 'asarmientoslm@gmail.com')->first();
    if (!$adriana) return response()->json(['error' => 'not found']);
    $rise = \DB::table('rise_programs')->where('client_id', $adriana->id)->first();
    if (!$rise) return response()->json(['error' => 'no rise']);
    $data = json_decode($rise->personalized_program, true);

    $fixes = [];
    foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
        // Fix 3rd day (index 2) = Wednesday → rename to Glúteos + add Bulgarian
        if (isset($semana['dias'][2])) {
            $dia = &$semana['dias'][2];
            $oldName = $dia['nombre'] ?? 'N/A';

            // Rename to Glúteos
            if (stripos($oldName, 'gluteo') === false && stripos($oldName, 'glúteo') === false) {
                $dia['nombre'] = preg_replace('/(—|:)\s*.*$/', '$1 Glúteos', $oldName);
                if ($dia['nombre'] === $oldName) {
                    $dia['nombre'] = str_replace(['Isquios', 'isquios', 'Isquiotibiales', 'Femoral'], 'Glúteos', $oldName);
                }
                $fixes[] = 'Wed renamed: ' . $oldName . ' → ' . $dia['nombre'];
            }

            // Add Bulgarian squat
            $has = false;
            foreach ($dia['ejercicios'] as $ej) {
                if (stripos($ej['nombre'] ?? '', 'bulgar') !== false) { $has = true; break; }
            }
            if (!$has) {
                $dia['ejercicios'][] = [
                    'nombre' => 'Sentadilla Búlgara con Mancuernas',
                    'series' => 4, 'repeticiones' => '10-12 por pierna', 'descanso' => '90s',
                    'notas' => 'Pie trasero en banco. Baja controlado. Empuja con talón delantero.',
                    'gif_url' => $base . '04101301-Dumbbell-Single-Leg-Split-Squat_Thighs_720.gif',
                ];
                $fixes[] = 'Bulgarian squat added to Wed';
            }
            unset($dia);
        }
    }
    unset($semana);

    \DB::table('rise_programs')->where('id', $rise->id)->update([
        'personalized_program' => json_encode($data),
    ]);

    return response()->json(['ok' => true, 'fixes' => $fixes]);
});
