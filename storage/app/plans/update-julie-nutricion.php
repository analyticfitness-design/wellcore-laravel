<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clientId = 57;

// Deactivate old nutrition
App\Models\AssignedPlan::where('client_id', $clientId)
    ->where('plan_type', 'nutricion')
    ->where('active', true)
    ->update(['active' => false]);

// Insert updated
$json = file_get_contents(__DIR__ . '/julie-nutricion.json');
$content = json_decode($json, true);

App\Models\AssignedPlan::create([
    'client_id' => $clientId,
    'plan_type' => 'nutricion',
    'content' => $content,
    'version' => 3,
    'assigned_by' => 1,
    'valid_from' => now()->toDateString(),
    'active' => true,
]);

echo "OK: Julie nutrition updated to 4 meals (no pre-entreno, no snack nocturno)\n";
echo "Meals: " . count($content['comidas']) . "\n";
echo "Done!\n";
