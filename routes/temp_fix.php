<?php
Route::get('/temp/fix-juliana-gifs-v2', function () {
    try {
        $client = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if (!$client) return response()->json(['error' => 'Client not found']);
        $rise = \DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);
        $current = json_decode($rise->personalized_program, true);

        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

        // ONLY GIFs confirmed working from Silvia's plan
        $gifByMuscle = [
            'squat' => $base.'03541301-Dumbbell-Sumo-Squat_Thighs_720.gif',
            'lunge' => $base.'03541301-Dumbbell-Sumo-Squat_Thighs_720.gif',
            'step' => $base.'03541301-Dumbbell-Sumo-Squat_Thighs_720.gif',
            'extension_rodilla' => $base.'05821301-Lever-Kneeling-Leg-Curl-plate-loaded_Thighs_720.gif',
            'press_hombro' => $base.'11651301-Barbell-Standing-Military-Press-without-rack_Shoulders_720.gif',
            'lateral' => $base.'03341301-Dumbbell-Lateral-Raise_shoulder-AFIX_720.gif',
            'triceps' => $base.'01521301-Cable-Concentration-Extension-on-knee_Upper-Arms_720.gif',
            'dips' => $base.'06621301-Push-up-m_Chest-FIX_720.gif',
            'crunch' => $base.'02231301-Cable-Side-Crunch_Waist_720.gif',
            'plancha' => $base.'23591301-Front-Plank_Waist_720.gif',
            'hip_thrust' => $base.'29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'kickback' => $base.'29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'clamshell' => $base.'05981301-Lever-Seated-Hip-Adduction_Thighs_720.gif',
            'remo' => $base.'03151301-Dumbbell-Bent-over-Row_Back_720.gif',
            'pulldown' => $base.'01771301-Cable-Lateral-Pulldown-with-rope-attachment_Back_720.gif',
            'curl' => $base.'03211301-Dumbbell-Curl_Upper-Arms_720.gif',
            'rdl' => $base.'00851301-Barbell-Romanian-Deadlift_Hips_720.gif',
            'curl_femoral' => $base.'05821301-Lever-Kneeling-Leg-Curl-plate-loaded_Thighs_720.gif',
            'good_morning' => $base.'00441301-Barbell-Good-Morning_Thighs_720.gif',
            'push_up' => $base.'06621301-Push-up-m_Chest-FIX_720.gif',
        ];

        // Map exercise names to muscle categories
        $nameMap = [
            'Sentadilla' => 'squat',
            'Zancada' => 'lunge',
            'Step up' => 'step',
            'Extension de rodilla' => 'extension_rodilla',
            'Extensi' => 'extension_rodilla',
            'Press de hombro' => 'press_hombro',
            'Elevaciones laterales' => 'lateral',
            'Extension de triceps' => 'triceps',
            'Extensi.*triceps' => 'triceps',
            'Dips' => 'dips',
            'Crunch' => 'crunch',
            'Plancha' => 'plancha',
            'Hip thrust' => 'hip_thrust',
            'Puente de gl' => 'hip_thrust',
            'Kickback' => 'kickback',
            'Clamshell' => 'clamshell',
            'Fire hydrant' => 'clamshell',
            'Remo' => 'remo',
            'Pull apart' => 'pulldown',
            'Curl de b' => 'curl',
            'Curl martillo' => 'curl',
            'Curl femoral' => 'curl_femoral',
            'Peso muerto' => 'rdl',
            'Good morning' => 'good_morning',
            'Push up' => 'push_up',
            'Sentadilla b' => 'lunge',
        ];

        $count = 0;
        if (isset($current['plan_entrenamiento']['semanas'])) {
            foreach ($current['plan_entrenamiento']['semanas'] as &$semana) {
                foreach ($semana['dias'] as &$dia) {
                    foreach ($dia['ejercicios'] as &$ej) {
                        $name = $ej['nombre'];
                        $matched = false;
                        foreach ($nameMap as $keyword => $muscle) {
                            if (stripos($name, $keyword) !== false) {
                                $ej['gif_url'] = $gifByMuscle[$muscle];
                                $count++;
                                $matched = true;
                                break;
                            }
                        }
                        if (!$matched) {
                            unset($ej['gif_url']); // Remove broken GIF
                        }
                    }
                }
            }
            unset($semana, $dia, $ej);
        }

        \DB::table('rise_programs')->where('id', $rise->id)->update([
            'personalized_program' => json_encode($current),
        ]);

        return response()->json(['ok' => true, 'gifs_mapped' => $count]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
