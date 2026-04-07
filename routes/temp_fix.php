<?php
Route::get('/temp/fix-juliana-habits', function () {
    try {
        $client = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if (!$client) return response()->json(['error' => 'Client not found']);
        $rise = \DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);
        $current = json_decode($rise->personalized_program, true);
        
        // Fix habits: rename 'habito' to 'nombre'
        $current['plan_habitos'] = [
            ['nombre' => 'Completar el entrenamiento del día', 'frecuencia' => 'diario', 'descripcion' => 'Realiza tu rutina completa de bandas + cardio según el plan'],
            ['nombre' => 'Cumplir las 5 comidas del plan', 'frecuencia' => 'diario', 'descripcion' => 'Come las 5 comidas ajustadas a tus macros (1800 kcal)'],
            ['nombre' => 'Beber 3 litros de agua', 'frecuencia' => 'diario', 'descripcion' => 'Distribuye durante el día, especialmente antes y después de entrenar'],
            ['nombre' => 'Dormir mínimo 7 horas', 'frecuencia' => 'diario', 'descripcion' => 'El descanso es donde tu cuerpo se transforma'],
            ['nombre' => '20 min de cardio (lazo o rumba)', 'frecuencia' => 'diario', 'descripcion' => 'Al final de cada sesión de entrenamiento excepto sábado'],
        ];

        \DB::table('rise_programs')->where('id', $rise->id)->update([
            'personalized_program' => json_encode($current),
        ]);

        return response()->json(['ok' => true, 'habits' => count($current['plan_habitos'])]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
