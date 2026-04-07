<?php
Route::get('/temp/fix-juliana-nutrition', function () {
    try {
        $client = \App\Models\Client::where('email', 'juliana27p@gmail.com')->first();
        if (!$client) return response()->json(['error' => 'Client not found']);
        $rise = \DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (!$rise) return response()->json(['error' => 'No rise program']);
        $current = json_decode($rise->personalized_program, true);
        
        $current['plan_nutricion'] = [
            'objetivo_calorico' => 1800,
            'macros' => ['proteina_g' => 130, 'carbohidratos_g' => 200, 'grasas_g' => 55],
            'comidas_sugeridas' => [
                [
                    'nombre' => 'Desayuno',
                    'hora' => '7:00am',
                    'calorias' => 400,
                    'macros' => ['proteina' => 30, 'carbohidratos' => 44, 'grasas' => 12],
                    'opciones' => [
                        'Opción 1: Avena (60g) + 1 scoop whey vainilla + 1/2 banano + 5g semillas de chía',
                        'Opción 2: 3 claras + 1 huevo entero revueltos + 2 tostadas integrales + 1/4 aguacate',
                        'Opción 3: Yogurt griego (200g) + granola sin azúcar (30g) + fresas (80g) + miel (1 cdta)',
                    ],
                ],
                [
                    'nombre' => 'Snack Media Mañana',
                    'hora' => '10:00am',
                    'calorias' => 250,
                    'macros' => ['proteina' => 18, 'carbohidratos' => 28, 'grasas' => 8],
                    'opciones' => [
                        'Opción 1: 1 manzana + 1 cda mantequilla de maní natural',
                        'Opción 2: Yogurt griego (150g) + 10 almendras',
                        'Opción 3: 2 tortas de arroz + atún en agua (80g) + limón',
                    ],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'hora' => '1:00pm',
                    'calorias' => 500,
                    'macros' => ['proteina' => 38, 'carbohidratos' => 55, 'grasas' => 14],
                    'opciones' => [
                        'Opción 1: Pechuga de pollo a la plancha (180g) + arroz integral (120g cocido) + ensalada verde con limón',
                        'Opción 2: Atún en agua (2 latas) + quinoa (80g cocida) + tomate + pepino + aceite de oliva (1 cdta)',
                        'Opción 3: Carne molida magra (150g) + papa cocida (120g) + brócoli al vapor (1 taza)',
                    ],
                ],
                [
                    'nombre' => 'Merienda Pre/Post Entreno',
                    'hora' => '4:00pm',
                    'calorias' => 300,
                    'macros' => ['proteina' => 22, 'carbohidratos' => 35, 'grasas' => 8],
                    'opciones' => [
                        'Opción 1: Batido: 1 scoop whey + 1 banano + 200ml leche descremada',
                        'Opción 2: 2 huevos cocidos + 1 banano',
                        'Opción 3: Avena (40g) + whey (1/2 scoop) + fresas (50g)',
                    ],
                ],
                [
                    'nombre' => 'Cena',
                    'hora' => '7:30pm',
                    'calorias' => 350,
                    'macros' => ['proteina' => 30, 'carbohidratos' => 22, 'grasas' => 15],
                    'opciones' => [
                        'Opción 1: Salmón al horno (150g) + espárragos salteados + ensalada verde',
                        'Opción 2: Pechuga de pollo (180g) + vegetales al vapor (brócoli, zanahoria, calabacín)',
                        'Opción 3: 3 huevos revueltos + espinaca salteada + 1/4 aguacate',
                    ],
                ],
            ],
            'hidratacion' => ['agua_minima_litros' => 3, 'nota' => 'Agregar limón al agua para mejor sabor y digestión'],
            'tips_nutricionales' => [
                'Comer cada 3-4 horas para mantener metabolismo activo.',
                'Prioriza proteína en cada comida — es clave para perder grasa sin perder músculo.',
                'Evita azúcares refinados y bebidas con calorías.',
            ],
            'notas_coach' => 'Juliana, este plan está diseñado para que pierdas grasa mientras entrenas en casa. Las 1800 kcal con 130g de proteína son suficientes para tu objetivo. Come limpio, toma agua y sé consistente con las 5 comidas.',
        ];

        \DB::table('rise_programs')->where('id', $rise->id)->update([
            'personalized_program' => json_encode($current),
        ]);

        return response()->json(['ok' => true, 'comidas' => count($current['plan_nutricion']['comidas_sugeridas'])]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
