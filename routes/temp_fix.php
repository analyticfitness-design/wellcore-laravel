<?php
Route::get('/temp/fix-juliana-gifs', function () {
    try {
        $client = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if (!$client) return response()->json(['error' => 'Client not found']);
        $rise = \DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);
        $current = json_decode($rise->personalized_program, true);

        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
        $gifMap = [
            'Sentadilla con banda en rodillas' => $base.'27991301-Band-squat_Thighs_720.gif',
            'Sentadilla sumo con banda' => $base.'27991301-Band-squat_Thighs_720.gif',
            'Zancadas alternas con banda en rodillas' => $base.'15551301-Dumbbell-Squat_Thighs_720.gif',
            'Step ups en silla o escalon' => $base.'08031301-Step-up_Thighs_720.gif',
            'Extension de rodilla sentada con banda' => $base.'05641301-Lever-Leg-Extension_Thighs_720.gif',
            'Press de hombros con superband' => $base.'00291301-Barbell-Standing-Military-Press_Shoulders_720.gif',
            'Elevaciones laterales con banda' => $base.'03341301-Dumbbell-Lateral-Raise_shoulder-AFIX_720.gif',
            'Extension de triceps con superband overhead' => $base.'01521301-Cable-Concentration-Extension-on-knee_Upper-Arms_720.gif',
            'Dips en silla' => $base.'02891301-Bench-dip-on-floor_Upper-arms_720.gif',
            'Crunch abdominal' => $base.'04141301-Crunch-Floor_Waist_720.gif',
            'Plancha lateral' => $base.'23591301-Front-Plank_Waist_720.gif',
            'Hip thrust en suelo con banda en rodillas' => $base.'29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'Puente de gluteo unilateral con banda' => $base.'29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'Kickback de gluteo con banda' => $base.'31101301-Band-Pull-Through_Hips_720.gif',
            'Clamshell con banda' => $base.'05981301-Lever-Seated-Hip-Adduction_Thighs_720.gif',
            'Fire hydrant con banda' => $base.'05981301-Lever-Seated-Hip-Adduction_Thighs_720.gif',
            'Sentadilla sumo pulso con banda' => $base.'27991301-Band-squat_Thighs_720.gif',
            'Remo con superband' => $base.'01151301-Cable-Bar-Lateral-Pulldown_Back_720.gif',
            'Pull apart con banda' => $base.'01771301-Cable-Lateral-Pulldown-with-rope-attachment_Back_720.gif',
            'Remo a una mano con superband' => $base.'03151301-Dumbbell-Bent-over-Row_Back_720.gif',
            'Curl de biceps con superband' => $base.'03211301-Dumbbell-Curl_Upper-Arms_720.gif',
            'Curl martillo con superband' => $base.'03211301-Dumbbell-Curl_Upper-Arms_720.gif',
            'Plancha abdominal isometrica' => $base.'23591301-Front-Plank_Waist_720.gif',
            'Peso muerto rumano con superband' => $base.'00851301-Barbell-Romanian-Deadlift_Hips_720.gif',
            'Curl femoral acostada con banda' => $base.'05821301-Lever-Kneeling-Leg-Curl-plate-loaded_Thighs_720.gif',
            'Hip thrust con banda en rodillas' => $base.'29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'Hip thrust con banda' => $base.'29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'Good morning con superband' => $base.'00441301-Barbell-Good-Morning_Thighs_720.gif',
            'Sentadilla bulgara con banda' => $base.'15551301-Dumbbell-Squat_Thighs_720.gif',
            'Puente de gluteo con pausa' => $base.'29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'Push ups (flexiones)' => $base.'06621301-Push-up-m_Chest-FIX_720.gif',
            'Plancha abdominal 60 seg' => $base.'23591301-Front-Plank_Waist_720.gif',
            'Sentadilla con banda' => $base.'27991301-Band-squat_Thighs_720.gif',
            'Press de hombros con superband' => $base.'00291301-Barbell-Standing-Military-Press_Shoulders_720.gif',
        ];

        $count = 0;
        if (isset($current['plan_entrenamiento']['semanas'])) {
            foreach ($current['plan_entrenamiento']['semanas'] as &$semana) {
                foreach ($semana['dias'] as &$dia) {
                    foreach ($dia['ejercicios'] as &$ej) {
                        $name = $ej['nombre'];
                        // Try exact match first, then normalized (no accents)
                        $normalized = str_replace(
                            ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'],
                            ['a','e','i','o','u','n','A','E','I','O','U','N'],
                            $name
                        );
                        if (isset($gifMap[$name])) {
                            $ej['gif_url'] = $gifMap[$name];
                            $count++;
                        } elseif (isset($gifMap[$normalized])) {
                            $ej['gif_url'] = $gifMap[$normalized];
                            $count++;
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
