<?php
// Rename Adriana's Day 3 from "Isquios y Glúteos Posterior" to "Glúteos"
Route::get('/temp/fix-adriana-rename', function () {
    $adriana = \App\Models\Client::where('email', 'asarmientoslm@gmail.com')->first();
    if (!$adriana) return response()->json(['error' => 'not found']);
    $rise = \DB::table('rise_programs')->where('client_id', $adriana->id)->first();
    if (!$rise) return response()->json(['error' => 'no rise']);
    $data = json_decode($rise->personalized_program, true);

    $renamed = 0;
    foreach ($data['plan_entrenamiento']['semanas'] as &$semana) {
        if (isset($semana['dias'][2])) {
            $old = $semana['dias'][2]['nombre'];
            $semana['dias'][2]['nombre'] = str_replace(
                ['Isquios y Glúteos Posterior', 'Isquios y Gl\u00fateos Posterior'],
                'Glúteos',
                $old
            );
            // Also try regex
            if (stripos($semana['dias'][2]['nombre'], 'Isquio') !== false) {
                $semana['dias'][2]['nombre'] = preg_replace('/Isquios?\s*(y|&)?\s*/i', '', $semana['dias'][2]['nombre']);
            }
            // Final: force "Día 3 - Glúteos"
            $semana['dias'][2]['nombre'] = 'Día 3 - Glúteos';
            $renamed++;
        }
    }
    unset($semana);

    \DB::table('rise_programs')->where('id', $rise->id)->update([
        'personalized_program' => json_encode($data),
    ]);

    return response()->json(['ok' => true, 'renamed' => $renamed]);
});
