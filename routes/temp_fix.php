<?php
// Fix specific GIF mismatches for ALL RISE programs
Route::get('/temp/fix-rise-gif-corrections', function () {
    try {
        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

        // MANUAL CORRECTIONS based on user feedback
        // Each maps exercise name keyword → correct gif filename
        $corrections = [
            // Crunch abdominal → acostado, no de pie
            'crunch abdominal' => $base . '04141301-Crunch-Floor_Waist_720.gif',
            'crunch' => $base . '04141301-Crunch-Floor_Waist_720.gif',
            'cable crunch' => $base . '04141301-Crunch-Floor_Waist_720.gif',

            // Puente de gluteo unilateral → single leg bridge
            'puente de gluteo unilateral' => $base . '14801301-Single-Leg-Hip-Thrust_Hips_720.gif',

            // Kickback de gluteo → donkey kick / glute kickback
            'kickback de gluteo' => $base . '13881301-Kneeling-Kickback_Hips_720.gif',
            'kickback' => $base . '13881301-Kneeling-Kickback_Hips_720.gif',

            // Clamshell → lying hip abduction
            'clamshell' => $base . '14351301-Lying-Hip-Abduction-(male)_Hips_720.gif',

            // Fire hydrant → quadruped hip abduction
            'fire hydrant' => $base . '14351301-Lying-Hip-Abduction-(male)_Hips_720.gif',

            // Remo con superband → seated cable row / bent over row
            'remo con superband' => $base . '01361301-Cable-Seated-Row_Back_720.gif',
            'remo con banda' => $base . '01361301-Cable-Seated-Row_Back_720.gif',

            // Pull apart → reverse fly / rear delt
            'pull apart' => $base . '00151301-Band-Pull-Apart_Shoulders_720.gif',

            // Remo a una mano → one arm dumbbell row
            'remo a una mano' => $base . '03151301-Dumbbell-Bent-over-Row_Back_720.gif',

            // Curl de biceps → standing dumbbell curl
            'curl de biceps' => $base . '03211301-Dumbbell-Curl_Upper-Arms_720.gif',
            'curl biceps' => $base . '03211301-Dumbbell-Curl_Upper-Arms_720.gif',
            'curl martillo' => $base . '03231301-Dumbbell-Hammer-Curl_Upper-Arms_720.gif',

            // Plancha abdominal → front plank (not lateral)
            'plancha abdominal' => $base . '23591301-Front-Plank_Waist_720.gif',
            'plancha isometrica' => $base . '23591301-Front-Plank_Waist_720.gif',
            'plancha abdominal 60' => $base . '23591301-Front-Plank_Waist_720.gif',

            // Plancha lateral → side plank
            'plancha lateral' => $base . '23601301-Side-Plank_Waist_720.gif',

            // Hip thrust → barbell hip thrust (not just bridge)
            'hip thrust' => $base . '00791301-Barbell-Hip-Thrust_Thighs_720.gif',

            // Puente de gluteo con pausa → glute bridge
            'puente de gluteo con pausa' => $base . '29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'puente de gluteo' => $base . '29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',

            // Sentadilla sumo pulso → sumo squat
            'sentadilla sumo pulso' => $base . '03541301-Dumbbell-Sumo-Squat_Thighs_720.gif',
            'sentadilla sumo' => $base . '03541301-Dumbbell-Sumo-Squat_Thighs_720.gif',

            // Sentadilla con banda → bodyweight squat
            'sentadilla con banda' => $base . '09471301-Bodyweight-Squat-Side_Thighs_720.gif',
            'sentadilla bulgara' => $base . '03601301-Dumbbell-Single-Leg-Split-Squat_Thighs_720.gif',

            // Zancadas → lunge
            'zancada' => $base . '03631301-Dumbbell-Lunge_Thighs_720.gif',

            // Step ups
            'step up' => $base . '03631301-Dumbbell-Lunge_Thighs_720.gif',

            // Extension de rodilla
            'extension de rodilla' => $base . '05641301-Lever-Leg-Extension_Thighs_720.gif',

            // Extension de triceps
            'extension de triceps' => $base . '01681301-Cable-Pushdown_Upper-Arms_720.gif',

            // Dips
            'dips en silla' => $base . '02891301-Bench-dip-on-floor_Upper-arms_720.gif',
            'dips' => $base . '02891301-Bench-dip-on-floor_Upper-arms_720.gif',

            // Push ups
            'push up' => $base . '06621301-Push-up-m_Chest-FIX_720.gif',
            'flexion' => $base . '06621301-Push-up-m_Chest-FIX_720.gif',

            // Curl femoral
            'curl femoral' => $base . '05821301-Lever-Kneeling-Leg-Curl-plate-loaded_Thighs_720.gif',

            // Peso muerto rumano
            'peso muerto rumano' => $base . '00851301-Barbell-Romanian-Deadlift_Hips_720.gif',
            'peso muerto' => $base . '00851301-Barbell-Romanian-Deadlift_Hips_720.gif',

            // Good morning
            'good morning' => $base . '00441301-Barbell-Good-Morning_Thighs_720.gif',

            // Press de hombros
            'press de hombros' => $base . '11651301-Barbell-Standing-Military-Press-without-rack_Shoulders_720.gif',
            'press militar' => $base . '11651301-Barbell-Standing-Military-Press-without-rack_Shoulders_720.gif',

            // Elevaciones laterales
            'elevaciones laterales' => $base . '03341301-Dumbbell-Lateral-Raise_shoulder-AFIX_720.gif',
            'elevacion lateral' => $base . '03341301-Dumbbell-Lateral-Raise_shoulder-AFIX_720.gif',
        ];

        // Get ALL active rise programs
        $programs = \DB::table('rise_programs')
            ->whereIn('status', ['active', 'activo'])
            ->get(['id', 'client_id', 'personalized_program']);

        $results = [];

        foreach ($programs as $prog) {
            $data = json_decode($prog->personalized_program, true);
            if (!$data || !isset($data['plan_entrenamiento']['semanas'])) {
                continue;
            }

            $fixed = 0;

            foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
                if (!isset($semana['dias'])) continue;
                foreach ($semana['dias'] as &$dia) {
                    if (!isset($dia['ejercicios'])) continue;
                    foreach ($dia['ejercicios'] as &$ej) {
                        $name = mb_strtolower(trim($ej['nombre'] ?? ''));
                        $normalized = str_replace(
                            ['á','é','í','ó','ú','ñ'],
                            ['a','e','i','o','u','n'],
                            $name
                        );

                        // Try longest match first (more specific)
                        $bestMatch = null;
                        $bestLen = 0;
                        foreach ($corrections as $keyword => $url) {
                            if (stripos($normalized, $keyword) !== false && strlen($keyword) > $bestLen) {
                                $bestMatch = $url;
                                $bestLen = strlen($keyword);
                            }
                        }

                        if ($bestMatch) {
                            $ej['gif_url'] = $bestMatch;
                            $fixed++;
                        }
                    }
                }
            }
            unset($semana, $dia, $ej);

            if ($fixed > 0) {
                \DB::table('rise_programs')->where('id', $prog->id)->update([
                    'personalized_program' => json_encode($data),
                ]);
            }

            $clientName = \App\Models\Client::where('id', $prog->client_id)->value('name') ?? 'ID:'.$prog->client_id;
            $results[] = ['client' => $clientName, 'fixed' => $fixed];
        }

        return response()->json(['ok' => true, 'results' => $results]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
    }
});
