<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clientId = 57; // Julie Rodriguez

// 1. Deactivate old training plan
App\Models\AssignedPlan::where('client_id', $clientId)
    ->where('plan_type', 'entrenamiento')
    ->where('active', true)
    ->update(['active' => false]);

echo "Old training plan deactivated.\n";

// 2. Insert new training plan
$json = file_get_contents(__DIR__ . '/julie-entrenamiento.json');
$content = json_decode($json, true);

if (!$content) {
    echo "ERROR: Invalid JSON!\n";
    exit(1);
}

App\Models\AssignedPlan::create([
    'client_id' => $clientId,
    'plan_type' => 'entrenamiento',
    'content' => $content,
    'version' => 2,
    'assigned_by' => 1, // Daniel Esparza admin ID
    'valid_from' => now()->toDateString(),
    'active' => true,
]);

echo "New training plan inserted for Julie (client_id: {$clientId}).\n";
echo "Weeks: " . count($content['semanas']) . "\n";
echo "Days per week (sem1): " . count($content['semanas'][0]['dias']) . "\n";
echo "Done!\n";
