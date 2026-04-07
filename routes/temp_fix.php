<?php
// TEMP: Fix Juliana RISE JSON structure
Route::get('/temp/fix-juliana-structure', function () {
    try {
        $client = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if (!$client) return response()->json(['error' => 'Client not found']);
        $rise = \DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);
        $current = json_decode($rise->personalized_program, true);
        // If semanas exists at root, wrap in plan_entrenamiento
        if (isset($current['semanas']) && !isset($current['plan_entrenamiento'])) {
            $wrapped = ['plan_entrenamiento' => $current];
            \DB::table('rise_programs')->where('id', $rise->id)->update([
                'personalized_program' => json_encode($wrapped),
            ]);
            return response()->json(['ok' => true, 'action' => 'wrapped in plan_entrenamiento', 'semanas' => count($current['semanas'])]);
        }
        return response()->json(['ok' => true, 'already_correct' => true]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
