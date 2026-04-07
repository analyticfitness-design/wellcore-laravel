<?php
// FINAL curated GIF mapping for ALL RISE programs
// Each exercise mapped by MOVEMENT PATTERN to a VERIFIED GIF filename
Route::get('/temp/fix-rise-gif-final', function () {
    try {
        $base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

        // CURATED MAP: spanish keyword → verified GIF (by movement pattern)
        // Sorted by specificity: longer/more specific keywords first
        $map = [
            // === PIERNAS / CUADRICEPS ===
            'sentadilla bulgara' => '04101301-Dumbbell-Single-Leg-Split-Squat_Thighs_720.gif',
            'sentadilla sumo pulso' => '17601301-Dumbbell-Goblet-Squat_Thighs-FIX_720.gif',
            'sentadilla sumo' => '17601301-Dumbbell-Goblet-Squat_Thighs-FIX_720.gif',
            'sentadilla con barra' => '10041301-Band-squat_Hips_720.gif',
            'sentadilla con banda' => '10041301-Band-squat_Hips_720.gif',
            'sentadilla goblet' => '17601301-Dumbbell-Goblet-Squat_Thighs-FIX_720.gif',
            'sentadilla' => '10041301-Band-squat_Hips_720.gif',
            'zancada' => '00771301-Barbell-Rear-Lunge-II_Thighs_720.gif',
            'lunges' => '00771301-Barbell-Rear-Lunge-II_Thighs_720.gif',
            'step up' => '04311301-Dumbbell-Step-up_Hips_720.gif',
            'extension de rodilla' => '05851301-Lever-Leg-Extension_Thighs_720.gif',
            'extension rodilla' => '05851301-Lever-Leg-Extension_Thighs_720.gif',
            'extension cuadriceps' => '05851301-Lever-Leg-Extension_Thighs_720.gif',
            'prensa' => '05721301-Lever-45-Leg-Press_Thighs_720.gif',

            // === FEMORAL / ISQUIOTIBIALES ===
            'curl femoral' => '05821301-Lever-Kneeling-Leg-Curl-plate-loaded_Thighs_720.gif',
            'peso muerto rumano' => '00851301-Barbell-Romanian-Deadlift_Hips_720.gif',
            'peso muerto' => '00321301-Barbell-Deadlift_Hips-FIX_720.gif',
            'good morning' => '00441301-Barbell-Good-Morning_Thighs_720.gif',

            // === GLUTEOS / CADERA ===
            'hip thrust en suelo' => '29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'hip thrust' => '10601301-Barbell-Hip-Thrust_Hips_720.gif',
            'puente de gluteo unilateral' => '10601301-Barbell-Hip-Thrust_Hips_720.gif',
            'puente de gluteo con pausa' => '29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'puente de gluteo' => '29641301-Barbell-Glute-Bridge-hands-on-bar_Hips_720.gif',
            'kickback de gluteo' => '10371301-Lever-Standing-Rear-Kick_Hips_720.gif',
            'kickback gluteo' => '10371301-Lever-Standing-Rear-Kick_Hips_720.gif',
            'clamshell' => '05971301-Lever-Seated-Hip-Abduction_Hips-FIX_720.gif',
            'fire hydrant' => '05971301-Lever-Seated-Hip-Abduction_Hips-FIX_720.gif',
            'abduccion' => '05971301-Lever-Seated-Hip-Abduction_Hips-FIX_720.gif',
            'abductor' => '05971301-Lever-Seated-Hip-Abduction_Hips-FIX_720.gif',

            // === ESPALDA ===
            'remo a una mano' => '00271301-Barbell-Bent-Over-Row_Back-FIX_720.gif',
            'remo con superband' => '01801301-Cable-Low-Seated-Row_Back_720.gif',
            'remo con banda' => '01801301-Cable-Low-Seated-Row_Back_720.gif',
            'remo' => '01801301-Cable-Low-Seated-Row_Back_720.gif',
            'pull apart' => '15031301-Band-Pull-Apart_Shoulders_720.gif',
            'face pull' => '02021301-Cable-Rear-Delt-Row-stirrups_Shoulders_720.gif',
            'jalon al pecho' => '01151301-Cable-Bar-Lateral-Pulldown_Back_720.gif',
            'pulldown' => '01151301-Cable-Bar-Lateral-Pulldown_Back_720.gif',
            'dominada' => '06871301-Pull-up_Back_720.gif',

            // === HOMBROS ===
            'press de hombros' => '11651301-Barbell-Standing-Military-Press-without-rack_Shoulders_720.gif',
            'press militar' => '11651301-Barbell-Standing-Military-Press-without-rack_Shoulders_720.gif',
            'press arnold' => '21371301-Dumbbell-Arnold-Press_Shoulders_720.gif',
            'elevaciones laterales' => '03341301-Dumbbell-Lateral-Raise_shoulder-AFIX_720.gif',
            'elevacion lateral' => '03341301-Dumbbell-Lateral-Raise_shoulder-AFIX_720.gif',
            'pajaros' => '03771301-Dumbbell-Rear-Delt-Row_shoulder_720.gif',
            'vuelos posteriores' => '03771301-Dumbbell-Rear-Delt-Row_shoulder_720.gif',

            // === BICEPS ===
            'curl de biceps' => '02851301-Dumbbell-Alternate-Biceps-Curl_Upper-Arms_720.gif',
            'curl biceps' => '02851301-Dumbbell-Alternate-Biceps-Curl_Upper-Arms_720.gif',
            'curl martillo' => '01651301-Cable-Hammer-Curl-with-rope-m_Forearms_720.gif',
            'curl con barra' => '00311301-Barbell-Curl_Upper-Arms_720.gif',
            'curl con mancuerna' => '02851301-Dumbbell-Alternate-Biceps-Curl_Upper-Arms_720.gif',

            // === TRICEPS ===
            'extension de triceps' => '01941301-Cable-Overhead-Triceps-Extension-rope-attachment_Upper-Arms_720.gif',
            'triceps en polea' => '02411301-Cable-Triceps-Pushdown-V-bar-attachment_Upper-Arms_720.gif',
            'dips en silla' => '13991301-Bench-dip-on-floor_Upper-Arms_720.gif',
            'dips' => '13991301-Bench-dip-on-floor_Upper-Arms_720.gif',
            'fondos' => '13991301-Bench-dip-on-floor_Upper-Arms_720.gif',

            // === PECHO ===
            'push up' => '06621301-Push-up-m_Chest-FIX_720.gif',
            'flexion' => '06621301-Push-up-m_Chest-FIX_720.gif',
            'press de banca' => '00251301-Barbell-Bench-Press_Chest-FIX_720.gif',
            'press inclinado' => '00691301-Barbell-Incline-Bench-Press_Chest_720.gif',

            // === ABDOMINALES / CORE ===
            'crunch abdominal' => '06351301-Oblique-Crunches-Floor_waist_720.gif',
            'cable crunch' => '01751301-Cable-Kneeling-Crunch_Waist-FIX_720.gif',
            'crunch' => '06351301-Oblique-Crunches-Floor_waist_720.gif',
            'plancha lateral' => '07151301-Side-Plank-m_Waist_720.gif',
            'plancha abdominal' => '04631301-Front-Plank_waist-FIX_720.gif',
            'plancha isometrica' => '04631301-Front-Plank_waist-FIX_720.gif',
            'plancha' => '04631301-Front-Plank_waist-FIX_720.gif',
        ];

        $programs = \DB::table('rise_programs')
            ->whereIn('status', ['active', 'activo'])
            ->get(['id', 'client_id', 'personalized_program']);

        $results = [];

        foreach ($programs as $prog) {
            $data = json_decode($prog->personalized_program, true);
            if (!$data || !isset($data['plan_entrenamiento']['semanas'])) continue;

            $fixed = 0;
            foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
                if (!isset($semana['dias'])) continue;
                foreach ($semana['dias'] as &$dia) {
                    if (!isset($dia['ejercicios'])) continue;
                    foreach ($dia['ejercicios'] as &$ej) {
                        $name = mb_strtolower(trim($ej['nombre'] ?? ''));
                        $norm = str_replace(
                            ['á','é','í','ó','ú','ñ'],
                            ['a','e','i','o','u','n'],
                            $name
                        );

                        // Find BEST match (longest keyword wins = most specific)
                        $bestGif = null;
                        $bestLen = 0;
                        foreach ($map as $keyword => $filename) {
                            if (stripos($norm, $keyword) !== false && strlen($keyword) > $bestLen) {
                                $bestGif = $base . $filename;
                                $bestLen = strlen($keyword);
                            }
                        }

                        if ($bestGif) {
                            $ej['gif_url'] = $bestGif;
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

            $name = \App\Models\Client::where('id', $prog->client_id)->value('name') ?? 'ID:'.$prog->client_id;
            $results[] = ['client' => $name, 'fixed' => $fixed];
        }

        return response()->json(['ok' => true, 'results' => $results]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
