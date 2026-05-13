<?php
// Script temporal — verificar plan mes 2 Julie Rodriguez (client_id=57, AP_ID=186)
// ELIMINAR después de usar

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness', 'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$stmt = $pdo->query("SELECT id, active, valid_from, expires_at FROM assigned_plans WHERE client_id=57 AND plan_type='entrenamiento' ORDER BY id DESC LIMIT 3");
echo "=== PLANES ENTRENAMIENTO client_id=57 ===\n";
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
    echo "AP_ID: {$p['id']} | active: {$p['active']} | from: {$p['valid_from']} | expires: {$p['expires_at']}\n";
}

$stmt2 = $pdo->query("SELECT content FROM assigned_plans WHERE id=186");
$row = $stmt2->fetch(PDO::FETCH_ASSOC);
if (!$row) { die("AP_ID=186 no encontrado\n"); }

$plan = json_decode($row['content'], true);
echo "\n=== ESTRUCTURA PLAN MES 2 (AP_ID=186) ===\n";
echo "Semanas: " . count($plan['semanas'] ?? []) . "\n";

foreach (($plan['semanas'] ?? []) as $i => $sem) {
    echo "\n--- Semana " . ($i+1) . " | Fase: " . ($sem['fase'] ?? '') . " | Series: " . ($sem['series_base'] ?? '') . " | Reps: " . ($sem['repeticiones_base'] ?? '') . " | RIR: " . ($sem['rir_base'] ?? '') . " ---\n";
    foreach (($sem['dias'] ?? []) as $dia) {
        echo "  [" . $dia['dia_semana'] . "] " . count($dia['ejercicios'] ?? []) . " ejercicios";
        $first = $dia['ejercicios'][0] ?? null;
        if ($first) echo " | 1ro: " . $first['nombre'];
        echo "\n";
    }
}
echo "\nOK\n";
