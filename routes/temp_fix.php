<?php
Route::get('/temp/fix-juliana-final', function () {
    try {
        $client = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if (!$client) return response()->json(['error' => 'Client not found']);
        $rise = \DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);
        $current = json_decode($rise->personalized_program, true);
        
        // Fix nutrition: flatten macros to root level
        if (isset($current['plan_nutricion']['macros'])) {
            $m = $current['plan_nutricion']['macros'];
            $current['plan_nutricion']['proteina_g'] = $m['proteina_g'] ?? 0;
            $current['plan_nutricion']['carbohidratos_g'] = $m['carbohidratos_g'] ?? 0;
            $current['plan_nutricion']['grasas_g'] = $m['grasas_g'] ?? 0;
            $current['plan_nutricion']['calorias_diarias'] = $current['plan_nutricion']['objetivo_calorico'] ?? 0;
        }

        // Add habits
        $current['plan_habitos'] = [
            'habitos' => [
                ['habito' => 'Completar el entrenamiento del día', 'frecuencia' => 'diario', 'objetivo' => '6 de 7 días', 'categoria' => 'Movimiento'],
                ['habito' => 'Cumplir el plan de nutrición (5 comidas)', 'frecuencia' => 'diario', 'objetivo' => '5/5 comidas', 'categoria' => 'Nutrición'],
                ['habito' => 'Beber 3 litros de agua', 'frecuencia' => 'diario', 'objetivo' => '3L', 'categoria' => 'Hidratación'],
                ['habito' => 'Dormir mínimo 7 horas', 'frecuencia' => 'diario', 'objetivo' => '7h mínimo', 'categoria' => 'Sueño'],
                ['habito' => '20 min de cardio (lazo o rumba)', 'frecuencia' => 'diario', 'objetivo' => '5 de 6 días entreno', 'categoria' => 'Movimiento'],
            ],
            'notas_coach' => 'Juliana, estos hábitos son tu base para los 30 días. Márcalos cada día.',
        ];

        \DB::table('rise_programs')->where('id', $rise->id)->update([
            'personalized_program' => json_encode($current),
        ]);

        return response()->json([
            'ok' => true, 
            'cal' => $current['plan_nutricion']['calorias_diarias'] ?? 'N/A',
            'prot' => $current['plan_nutricion']['proteina_g'] ?? 'N/A',
            'habits' => count($current['plan_habitos']['habitos']),
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
