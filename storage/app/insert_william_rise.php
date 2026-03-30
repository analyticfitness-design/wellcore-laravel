<?php
/**
 * Script to insert William's RISE personalized_program into rise_programs.
 * Run from server: php /code/storage/app/insert_william_rise.php
 */
require '/code/vendor/autoload.php';
$app = require '/code/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Read the assembled JSON
$jsonPath = __DIR__ . '/william_rise_complete.json';
if (!file_exists($jsonPath)) {
    echo "ERROR: File not found: $jsonPath\n";
    exit(1);
}

$json = file_get_contents($jsonPath);
$decoded = json_decode($json, true);
if (!$decoded) {
    echo "ERROR: Invalid JSON\n";
    exit(1);
}

echo "JSON loaded: " . strlen($json) . " bytes\n";
echo "Sections: plan_entrenamiento, plan_nutricion, plan_habitos\n";
echo "Semanas: " . count($decoded['plan_entrenamiento']['semanas']) . "\n";
echo "Comidas: " . count($decoded['plan_nutricion']['comidas_sugeridas']) . "\n";
echo "Habitos: " . count($decoded['plan_habitos']) . "\n";

// Find the rise_program for client 47
$rp = DB::table('rise_programs')
    ->where('client_id', 47)
    ->where('status', 'active')
    ->orderByDesc('id')
    ->first();

if (!$rp) {
    echo "ERROR: No active rise_program found for client_id=47\n";
    exit(1);
}

echo "Found rise_program ID: {$rp->id}\n";

// Update with personalized_program
DB::table('rise_programs')
    ->where('id', $rp->id)
    ->update(['personalized_program' => $json]);

// Verify
$updated = DB::table('rise_programs')->where('id', $rp->id)->first();
$programSize = strlen($updated->personalized_program ?? '');
echo "Updated! personalized_program size: {$programSize} bytes\n";
echo "DONE - William's RISE plan is now active.\n";
