<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clientId = 57; // Julie Rodriguez

$plans = [
    'nutricion' => 'julie-nutricion.json',
    'suplementacion' => 'julie-suplementacion.json',
    'habitos' => 'julie-habitos.json',
];

foreach ($plans as $type => $file) {
    // Deactivate old
    App\Models\AssignedPlan::where('client_id', $clientId)
        ->where('plan_type', $type)
        ->where('active', true)
        ->update(['active' => false]);

    // Read JSON
    $json = file_get_contents(__DIR__ . '/' . $file);
    $content = json_decode($json, true);

    if (!$content) {
        echo "ERROR: Invalid JSON in {$file}!\n";
        continue;
    }

    // Insert new
    App\Models\AssignedPlan::create([
        'client_id' => $clientId,
        'plan_type' => $type,
        'content' => $content,
        'version' => 2,
        'assigned_by' => 1,
        'valid_from' => now()->toDateString(),
        'active' => true,
    ]);

    echo "OK: {$type} plan inserted from {$file}\n";
}

echo "\nAll plans inserted for Julie (client_id: {$clientId}).\n";
echo "Done!\n";
