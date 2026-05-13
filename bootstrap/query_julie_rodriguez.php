<?php
// Script temporal — buscar plan actual de Julie Rodriguez
// Ejecutar: php /code/bootstrap/query_julie_rodriguez.php
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

    // Buscar a Julie Rodriguez
    $stmt = $pdo->prepare("
        SELECT id, nombre, email, plan, coach_id, created_at
        FROM clients
        WHERE nombre LIKE '%julie%' OR nombre LIKE '%Julie%' OR email LIKE '%julie%'
        ORDER BY id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== CLIENTES ENCONTRADOS ===\n";
    foreach ($clients as $c) {
        echo "ID: {$c['id']} | Nombre: {$c['nombre']} | Email: {$c['email']} | Plan: {$c['plan']}\n";
    }

    if (empty($clients)) {
        // Intentar con apellido Rodriguez
        $stmt2 = $pdo->prepare("
            SELECT id, nombre, email, plan, coach_id, created_at
            FROM clients
            WHERE nombre LIKE '%rodriguez%' OR nombre LIKE '%Rodriguez%'
            ORDER BY id DESC LIMIT 10
        ");
        $stmt2->execute();
        $clients2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        echo "=== BÚSQUEDA POR APELLIDO RODRIGUEZ ===\n";
        foreach ($clients2 as $c) {
            echo "ID: {$c['id']} | Nombre: {$c['nombre']} | Email: {$c['email']} | Plan: {$c['plan']}\n";
        }
    }

    // Si encontramos exactamente un Julie Rodriguez, mostrar sus planes
    $julieId = null;
    foreach ($clients as $c) {
        if (stripos($c['nombre'], 'julie') !== false) {
            $julieId = $c['id'];
            break;
        }
    }

    if ($julieId) {
        echo "\n=== PLANES ACTIVOS DE CLIENT ID={$julieId} ===\n";
        $stmt3 = $pdo->prepare("
            SELECT id, plan_type, active, valid_from, expires_at, assigned_by, created_at
            FROM assigned_plans
            WHERE client_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt3->execute([$julieId]);
        $plans = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        foreach ($plans as $p) {
            echo "AP_ID: {$p['id']} | type: {$p['plan_type']} | active: {$p['active']} | from: {$p['valid_from']} | expires: {$p['expires_at']}\n";
        }

        // Extraer el JSON del plan de entrenamiento activo
        $stmt4 = $pdo->prepare("
            SELECT id, plan_type, content, valid_from, expires_at
            FROM assigned_plans
            WHERE client_id = ? AND plan_type = 'entrenamiento' AND active = 1
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt4->execute([$julieId]);
        $trainPlan = $stmt4->fetch(PDO::FETCH_ASSOC);

        if ($trainPlan) {
            echo "\n=== PLAN ENTRENAMIENTO ACTIVO (AP ID={$trainPlan['id']}) ===\n";
            echo "valid_from: {$trainPlan['valid_from']} | expires_at: {$trainPlan['expires_at']}\n";
            echo "CONTENT JSON:\n";
            // Pretty print
            $decoded = json_decode($trainPlan['content'], true);
            echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "\nNo hay plan de entrenamiento activo para este cliente.\n";
        }
    }

} catch (Exception $e) {
    die("ERROR: " . $e->getMessage() . "\n");
}
