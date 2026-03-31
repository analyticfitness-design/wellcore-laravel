<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clientId = 58; // VANESSA DIAZ

// Read the full program JSON
$json = file_get_contents(__DIR__ . '/vanessa-rise-program.json');
$program = json_decode($json, true);

if (!$program) {
    echo "ERROR: Invalid JSON!\n";
    exit(1);
}

// Check if RISE program exists
$riseProgram = App\Models\RiseProgram::where('client_id', $clientId)->first();

if ($riseProgram) {
    // Update existing
    $riseProgram->update([
        'personalized_program' => $program,
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(4)->toDateString(),
    ]);
    echo "Updated existing RISE program (ID: {$riseProgram->id})\n";
} else {
    // Create new
    $riseProgram = App\Models\RiseProgram::create([
        'client_id' => $clientId,
        'enrollment_date' => now(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(4)->toDateString(),
        'experience_level' => 'intermedio',
        'training_location' => 'gym',
        'gender' => 'female',
        'status' => 'active',
        'personalized_program' => $program,
    ]);
    echo "Created new RISE program (ID: {$riseProgram->id})\n";
}

echo "Client: VANESSA DIAZ (ID: {$clientId})\n";
echo "Weeks: " . count($program['plan_entrenamiento']['semanas']) . "\n";
echo "Days/week: " . count($program['plan_entrenamiento']['semanas'][0]['dias']) . "\n";
echo "Nutrition: " . ($program['plan_nutricion']['calorias_diarias'] ?? '?') . " kcal\n";
echo "Habits: " . count($program['plan_habitos']['habitos'] ?? []) . " habits\n";
echo "Done!\n";
