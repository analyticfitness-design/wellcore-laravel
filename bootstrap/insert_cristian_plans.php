<?php
// ============================================================
// POST-INSERT ACTIONS para CRISTIAN OQUENDO (client_id=78)
//   1. Notificacion in-app "Tu plan esta listo"
//   2. Dump del whatsapp del cliente para link wa.me
// PDO puro, sin Laravel bootstrap.
// Ejecutar con: php /code/bootstrap/insert_cristian_plans.php
// ============================================================

$host = 'wellcorefitness_wellcorefitness-mysql';
$db   = 'wellcorefitness';
$user = 'wellcorefitness';
$pass = 'fYCVgn4XZ7twq34';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Exception $e) {
    die("DB ERROR: " . $e->getMessage() . "\n");
}

$clientId = 78;
$now      = date('Y-m-d H:i:s');

// 1) Datos del cliente (whatsapp/telefono y nombre)
$stmt = $pdo->prepare("SELECT id, name, email, whatsapp, telefono FROM clients WHERE id = ? LIMIT 1");
$stmt->execute([$clientId]);
$cli = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cli) {
    die("ERROR: cliente $clientId no existe\n");
}
echo "Cliente: " . $cli['name'] . " <" . $cli['email'] . ">\n";
echo "WhatsApp DB: " . ($cli['whatsapp'] ?: '(vacio)') . "\n";
echo "Telefono DB: " . ($cli['telefono'] ?: '(vacio)') . "\n";

// 2) Insertar notificacion in-app (si NO existe ya una identica reciente)
$stmt = $pdo->prepare("SELECT id FROM notifications WHERE user_type='client' AND user_id=? AND type='plan_assigned' AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) LIMIT 1");
$stmt->execute([$clientId]);
$exists = $stmt->fetchColumn();

if ($exists) {
    echo "Notificacion 'plan_assigned' ya existe (id=$exists), saltando insert\n";
} else {
    $title = 'Tu plan esta listo';
    $body  = 'Tu coach Anderson asigno tu plan. Entra a Mi Plan y arranca.';
    $link  = '/client/plan';

    $stmt = $pdo->prepare("INSERT INTO notifications (user_type, user_id, type, title, body, link, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['client', $clientId, 'plan_assigned', $title, $body, $link, $now]);
    $nid = $pdo->lastInsertId();
    echo "Notificacion creada: id=$nid\n";
}

// 3) Generar link wa.me con mensaje pre-llenado
$wa = $cli['whatsapp'] ?: ($cli['telefono'] ?: '');
$wa = preg_replace('/\D+/', '', $wa); // solo digitos
if ($wa) {
    $msg = "Hola Cristian! 👋\n\n"
         . "Tu plan WellCore ya esta activo. Entra a wellcorefitness.com/client/plan "
         . "y vas a ver todo: entrenamiento, nutricion y suplementacion.\n\n"
         . "El primer entreno es el lunes. No esperes el lunes perfecto, empieza tranquilo "
         . "y consistente. Cualquier duda me escribes.\n\n"
         . "— Anderson | WellCore";
    $link = 'https://wa.me/' . $wa . '?text=' . rawurlencode($msg);
    echo "\n=== LINK WHATSAPP ===\n$link\n=== FIN LINK ===\n";
} else {
    echo "\n(El cliente no tiene whatsapp ni telefono en DB - no se genera link wa.me)\n";
}

echo "\nLISTO\n";
