<?php
/**
 * WELLCORE KB — Seed methodologies desde methodologies-seed.json
 *
 * SAFETY GUARANTEES:
 *   1. Conexión exclusivamente a 127.0.0.1:3306 (HERD MySQL local).
 *   2. Base de datos: wellcore_kb (NO wellcore_fitness).
 *   3. Upsert por slug (INSERT ... ON DUPLICATE KEY UPDATE) — idempotente.
 *   4. Sin DROP, sin TRUNCATE, sin DELETE.
 *   5. Audit trail en methodologies_seed_runs.
 *
 * Re-ejecución:
 *   php docs/audit-motor-v2/02-seed-methodologies.cli.php
 */

declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_USER = 'root';
const DB_PASS = 'QY@P6Ak2?';
const DB_NAME = 'wellcore_kb';
const JSON_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'methodologies-seed.json';

// --- SAFETY GUARD ---
if (DB_HOST !== '127.0.0.1' && DB_HOST !== 'localhost') {
    fwrite(STDERR, 'SAFETY ABORT: DB_HOST debe ser local. Valor: ' . DB_HOST . PHP_EOL);
    exit(1);
}
if (DB_NAME !== 'wellcore_kb') {
    fwrite(STDERR, 'SAFETY ABORT: DB_NAME debe ser wellcore_kb. Valor: ' . DB_NAME . PHP_EOL);
    exit(1);
}

// --- LEER JSON ---
$payload = json_decode((string)file_get_contents(JSON_PATH), true);
if (!is_array($payload) || empty($payload['methodologies'])) {
    fwrite(STDERR, 'ERROR JSON invalido o sin methodologies' . PHP_EOL);
    exit(1);
}
$entries = $payload['methodologies'];
$total = count($entries);
echo '[seed] JSON cargado: ' . $total . ' entries' . PHP_EOL;

// --- CONECTAR ---
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
echo '[seed] Conectado a ' . DB_HOST . '/' . DB_NAME . PHP_EOL;

// --- AUDIT TRAIL: iniciar registro ---
$runIns = $pdo->prepare(
    'INSERT INTO methodologies_seed_runs (json_version, json_generated_at, json_source_path, rows_total, status)
     VALUES (?, ?, ?, ?, "started")'
);
$runIns->execute([
    $payload['version'] ?? null,
    $payload['generated_at'] ?? null,
    'docs/audit-motor-v2/methodologies-seed.json',
    $total,
]);
$runId = (int)$pdo->lastInsertId();
echo '[seed] Audit run id=' . $runId . PHP_EOL;

// --- UPSERT ---
$upsert = $pdo->prepare(
    'INSERT INTO methodologies (slug, name, type, source, evidence_level, is_split_agnostic,
        short_description, applicable_tiers, applicable_levels, applicable_objectives,
        applicable_gender, applicable_days_range, applicable_locations, raw_data, version, active)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        name=VALUES(name), type=VALUES(type), source=VALUES(source),
        evidence_level=VALUES(evidence_level), is_split_agnostic=VALUES(is_split_agnostic),
        short_description=VALUES(short_description),
        applicable_tiers=VALUES(applicable_tiers), applicable_levels=VALUES(applicable_levels),
        applicable_objectives=VALUES(applicable_objectives), applicable_gender=VALUES(applicable_gender),
        applicable_days_range=VALUES(applicable_days_range), applicable_locations=VALUES(applicable_locations),
        raw_data=VALUES(raw_data), version=VALUES(version), active=VALUES(active)'
);

$existsQ = $pdo->prepare('SELECT id FROM methodologies WHERE slug = ?');

$ins = 0;
$upd = 0;
$err = [];

foreach ($entries as $m) {
    if (empty($m['slug'])) { $err[] = 'sin slug, skipped'; continue; }
    $existsQ->execute([$m['slug']]);
    $wasThere = (bool)$existsQ->fetch();
    try {
        $upsert->execute([
            $m['slug'],
            $m['name'] ?? $m['slug'],
            $m['type'] ?? 'entrenamiento',
            $m['source'] ?? null,
            $m['evidence_level'] ?? null,
            !empty($m['is_split_agnostic']) ? 1 : 0,
            $m['short_description'] ?? null,
            json_encode($m['applicable_tiers'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($m['applicable_levels'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($m['applicable_objectives'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($m['applicable_gender'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($m['applicable_days_range'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($m['applicable_locations'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($m, JSON_UNESCAPED_UNICODE),
            $m['version'] ?? 1,
            !empty($m['active']) ? 1 : 0,
        ]);
        if ($wasThere) { $upd++; echo '[upd] ' . $m['slug'] . PHP_EOL; }
        else           { $ins++; echo '[new] ' . $m['slug'] . PHP_EOL; }
    } catch (PDOException $e) {
        $err[] = $m['slug'] . ': ' . $e->getMessage();
        echo '[ERR] ' . $m['slug'] . ': ' . $e->getMessage() . PHP_EOL;
    }
}

// --- CERRAR AUDIT TRAIL ---
$runUpd = $pdo->prepare(
    'UPDATE methodologies_seed_runs
     SET seed_finished_at=NOW(), rows_inserted=?, rows_updated=?, rows_skipped=?,
         status=?, notes=?
     WHERE id=?'
);
$runUpd->execute([
    $ins,
    $upd,
    count($err),
    $err ? 'completed_with_errors' : 'completed',
    $err ? implode("\n", $err) : null,
    $runId,
]);

echo PHP_EOL . '==========================================' . PHP_EOL;
echo 'RESUMEN: ins=' . $ins . ' upd=' . $upd . ' err=' . count($err) . PHP_EOL;
echo '==========================================' . PHP_EOL;

$count = $pdo->query('SELECT COUNT(*) as c FROM methodologies WHERE active=1')->fetch();
echo 'Total activas en BD: ' . $count['c'] . PHP_EOL;

exit($err ? 1 : 0);
