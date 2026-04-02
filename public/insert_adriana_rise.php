<?php
// Script temporal de insercion — Plan RISE Adriana Sarmiento
// rise_program_id = 29, client_id = 59
// ELIMINAR DESPUES DE USAR

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$jsonFile = __DIR__ . '/adriana_plan_insert.json';

if (!file_exists($jsonFile)) {
    die("ERROR: No se encuentra el archivo JSON en: $jsonFile\n");
}

$json = file_get_contents($jsonFile);

// Validar JSON
$decoded = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("ERROR: JSON invalido — " . json_last_error_msg() . "\n");
}

// Verificar estructura
$semanas = count($decoded['plan_entrenamiento']['semanas'] ?? []);
$habitos = count($decoded['plan_habitos'] ?? []);
$calorias = $decoded['plan_nutricion']['calorias_diarias'] ?? 0;

echo "JSON valido.\n";
echo "Semanas de entrenamiento: $semanas\n";
echo "Habitos: $habitos\n";
echo "Calorias diarias: $calorias\n";
echo "Tamano JSON: " . strlen($json) . " bytes\n\n";

// Verificar que el registro existe
$program = DB::table('rise_programs')->where('id', 29)->first();
if (!$program) {
    die("ERROR: No existe rise_program con id=29\n");
}

echo "Programa encontrado: client_id={$program->client_id}, status={$program->status}\n";
echo "Insertando personalized_program...\n";

// Insertar
$updated = DB::table('rise_programs')
    ->where('id', 29)
    ->update(['personalized_program' => $json]);

if ($updated) {
    echo "EXITO: personalized_program actualizado correctamente.\n";

    // Verificar
    $check = DB::table('rise_programs')->where('id', 29)->value('personalized_program');
    $checkDecoded = json_decode($check, true);
    $checkSemanas = count($checkDecoded['plan_entrenamiento']['semanas'] ?? []);
    echo "Verificacion: semanas en DB = $checkSemanas\n";
} else {
    echo "AVISO: No se realizaron cambios (posiblemente el valor ya era igual).\n";
}

echo "\nScript completado. Eliminar este archivo del servidor.\n";
