<?php
// Script temporal — obtener plan activo de Julie Rodriguez (client_id=57, AP_ID=135)
// Ejecutar via EasyPanel script query-julie-plan
// ELIMINAR después de usar

$host = 'wellcorefitness_wellcorefitness-mysql';
$db   = 'wellcorefitness';
$user = 'wellcorefitness';
$pass = 'fYCVgn4XZ7twq34';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar schema de clients
    $cols = $pdo->query("SHOW COLUMNS FROM clients")->fetchAll(PDO::FETCH_COLUMN);
    $nameCol = in_array('nombre', $cols) ? 'nombre' : (in_array('name', $cols) ? 'name' : $cols[1]);
    echo "=== COLUMNA NOMBRE: $nameCol ===\n";

    // Buscar el plan activo de Julie (client_id=57, plan_type=entrenamiento)
    $stmt = $pdo->prepare("
        SELECT id, client_id, plan_type, active, valid_from, expires_at, created_at,
               SUBSTRING(content, 1, 200) as content_preview
        FROM assigned_plans
        WHERE client_id = 57 AND plan_type = 'entrenamiento'
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== PLANES ENTRENAMIENTO CLIENT 57 ===\n";
    foreach ($plans as $p) {
        echo "AP_ID: {$p['id']} | active: {$p['active']} | from: {$p['valid_from']} | expires: {$p['expires_at']}\n";
        echo "preview: {$p['content_preview']}\n\n";
    }

    // Extraer el plan activo AP_ID=135 — solo los días (ejercicios por día)
    $stmt2 = $pdo->prepare("SELECT content FROM assigned_plans WHERE id = 135");
    $stmt2->execute();
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $plan = json_decode($row['content'], true);
        echo "\n=== ESTRUCTURA PLAN AP_ID=135 ===\n";
        echo "Semanas: " . count($plan['semanas'] ?? []) . "\n";
        $sem0 = $plan['semanas'][0] ?? null;
        if ($sem0) {
            echo "Fase semana 0: " . ($sem0['fase'] ?? 'N/A') . "\n";
            foreach (($sem0['dias'] ?? []) as $dia) {
                echo "\n--- " . $dia['dia_semana'] . " (" . ($dia['grupo_muscular'] ?? '') . ") ---\n";
                foreach (($dia['ejercicios'] ?? []) as $ej) {
                    echo "  - " . $ej['nombre'] . " | series: " . ($ej['series'] ?? '') . " | reps: " . ($ej['reps'] ?? '') . "\n";
                }
            }
        }
    }

} catch (Exception $e) {
    die("ERROR: " . $e->getMessage() . "\n");
}
