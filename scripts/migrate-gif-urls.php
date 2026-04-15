<?php
/**
 * Migración one-time: reemplaza gif_filename (inglés viejo) por gif_url (CDN español)
 * en todos los planes de entrenamiento activos (assigned_plans + rise_programs).
 *
 * Usage:
 *   php scripts/migrate-gif-urls.php            # dry-run (solo muestra, no escribe)
 *   php scripts/migrate-gif-urls.php --execute   # ejecuta la migración
 */

$dryRun = !in_array('--execute', $argv);

// --- DB Connection (auto-detect: production uses env vars, local uses hardcoded) ---
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: 'QY@P6Ak2?';
$dbName = getenv('DB_DATABASE') ?: 'wellcore_fitness';
$dbPort = (int)(getenv('DB_PORT') ?: 3306);

$db = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($db->connect_error) {
    die("DB Error: {$db->connect_error}\n");
}
$db->set_charset('utf8mb4');

echo $dryRun ? "=== DRY RUN (no changes) ===\n\n" : "=== EXECUTING MIGRATION ===\n\n";

// --- Load 265 GIF catalog from gif-catalog.json ---
$catalogPath = __DIR__ . '/gif-catalog.json';
$gifFiles = json_decode(file_get_contents($catalogPath), true);
if (!$gifFiles || count($gifFiles) === 0) {
    die("ERROR: gif-catalog.json is empty or missing at {$catalogPath}\n");
}
echo "Catalog: " . count($gifFiles) . " GIFs loaded from gif-catalog.json\n\n";

$CDN_BASE = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

// Build lookup: normalized slug → filename
$slugMap = [];
foreach ($gifFiles as $file) {
    $slug = strtolower(str_replace('.gif', '', $file));
    $slugMap[$slug] = $file;
}

// Normalize exercise name to match GIF filename
function slugify($name) {
    // Remove parentheses content
    $name = preg_replace('/\([^)]*\)/', '', $name);
    // Lowercase
    $name = mb_strtolower($name, 'UTF-8');
    // Remove accents
    $name = strtr($name, [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'ñ'=>'n','ü'=>'u','Á'=>'a','É'=>'e','Í'=>'i',
        'Ó'=>'o','Ú'=>'u','Ñ'=>'n',
    ]);
    // Replace spaces, underscores with hyphens
    $name = preg_replace('/[\s_]+/', '-', $name);
    // Remove non-alphanumeric (except hyphens)
    $name = preg_replace('/[^a-z0-9\-]/', '', $name);
    // Collapse multiple hyphens
    $name = preg_replace('/-+/', '-', trim($name, '-'));
    return $name;
}

// Try multiple matching strategies
function findGif($nombre, $slugMap, $gifFiles) {
    // Strategy 1: exact slug match
    $slug = slugify($nombre);
    if (isset($slugMap[$slug])) {
        return [$slugMap[$slug], 'exact', 1.0];
    }

    // Strategy 2: slug without stopwords
    $stopwords = ['de','con','en','el','la','los','las','un','una','y','a','al','del','para','por'];
    $parts = explode('-', $slug);
    $filtered = array_filter($parts, fn($w) => !in_array($w, $stopwords));
    $slugNoStop = implode('-', $filtered);
    if (isset($slugMap[$slugNoStop])) {
        return [$slugMap[$slugNoStop], 'no-stopwords', 0.95];
    }

    // Strategy 3: word overlap against all filenames
    $nameWords = array_filter(explode('-', $slug), fn($w) => strlen($w) > 1);
    $bestFile = null;
    $bestScore = 0;

    foreach ($slugMap as $gifSlug => $file) {
        $gifWords = array_filter(explode('-', $gifSlug), fn($w) => strlen($w) > 1);

        // Count matching words
        $common = count(array_intersect($nameWords, $gifWords));
        if ($common === 0) continue;

        // Jaccard-like score
        $union = count(array_unique(array_merge($nameWords, $gifWords)));
        $score = $common / max($union, 1);

        // Bonus: if name words are a subset of gif words or vice versa
        if ($common === count($nameWords) || $common === count($gifWords)) {
            $score += 0.2;
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestFile = $file;
        }
    }

    if ($bestFile && $bestScore >= 0.45) {
        return [$bestFile, 'fuzzy', round($bestScore, 2)];
    }

    return [null, 'no-match', 0];
}

// --- Process exercises in a JSON structure ---
function processExercises(&$exercises, $slugMap, $gifFiles, $cdnBase, &$stats) {
    if (!is_array($exercises)) return;
    foreach ($exercises as &$ej) {
        if (!is_array($ej) || empty($ej['nombre'])) continue;

        $nombre = $ej['nombre'];
        [$gifFile, $method, $score] = findGif($nombre, $slugMap, $gifFiles);

        if ($gifFile) {
            $ej['gif_url'] = $cdnBase . $gifFile;
            // Remove old gif_filename if present
            unset($ej['gif_filename']);
            $stats['matched'][] = "  ✅ {$nombre} → {$gifFile} ({$method}, {$score})";
        } else {
            // Remove stale gif_filename, don't add gif_url
            unset($ej['gif_filename']);
            $stats['unmatched'][] = "  ❌ {$nombre} — no match found";
        }
        $stats['total']++;
    }
    unset($ej);
}

function processTrainingPlan(&$plan, $slugMap, $gifFiles, $cdnBase, &$stats) {
    if (!is_array($plan)) return;

    // Structure: semanas[] → dias[] → ejercicios[]
    if (!empty($plan['semanas'])) {
        foreach ($plan['semanas'] as &$semana) {
            if (empty($semana['dias'])) continue;
            foreach ($semana['dias'] as &$dia) {
                if (empty($dia['ejercicios'])) continue;
                processExercises($dia['ejercicios'], $slugMap, $gifFiles, $cdnBase, $stats);
            }
            unset($dia);
        }
        unset($semana);
    }

    // Legacy: dias[] → ejercicios[]
    if (!empty($plan['dias'])) {
        foreach ($plan['dias'] as &$dia) {
            if (empty($dia['ejercicios'])) continue;
            processExercises($dia['ejercicios'], $slugMap, $gifFiles, $cdnBase, $stats);
        }
        unset($dia);
    }
}

// ============================================================
// 1. MIGRATE assigned_plans (client /client)
// ============================================================
echo "── ASSIGNED PLANS (client) ──\n";
$result = $db->query("SELECT id, client_id, content FROM assigned_plans WHERE plan_type = 'entrenamiento' AND content IS NOT NULL AND content != ''");
$clientPlanCount = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $content = json_decode($row['content'], true);
        if (!$content) continue;

        $stats = ['total' => 0, 'matched' => [], 'unmatched' => []];
        processTrainingPlan($content, $slugMap, $gifFiles, $CDN_BASE, $stats);

        if ($stats['total'] === 0) continue;
        $clientPlanCount++;

        echo "\nPlan #{$row['id']} (client {$row['client_id']}): {$stats['total']} ejercicios\n";
        foreach ($stats['matched'] as $m) echo $m . "\n";
        foreach ($stats['unmatched'] as $u) echo $u . "\n";
        echo "  Matched: " . count($stats['matched']) . "/{$stats['total']}\n";

        if (!$dryRun) {
            $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stmt = $db->prepare("UPDATE assigned_plans SET content = ? WHERE id = ?");
            $stmt->bind_param('si', $json, $row['id']);
            $stmt->execute();
            echo "  💾 SAVED\n";
        }
    }
} else {
    echo "No client training plans found.\n";
}

// ============================================================
// 2. MIGRATE rise_programs
// ============================================================
echo "\n── RISE PROGRAMS ──\n";
$result = $db->query("SELECT id, client_id, personalized_program FROM rise_programs WHERE personalized_program IS NOT NULL AND personalized_program != ''");
$risePlanCount = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $program = json_decode($row['personalized_program'], true);
        if (!$program) continue;

        $trainingPlan = $program['plan_entrenamiento'] ?? null;
        if (!$trainingPlan) continue;

        $stats = ['total' => 0, 'matched' => [], 'unmatched' => []];
        processTrainingPlan($trainingPlan, $slugMap, $gifFiles, $CDN_BASE, $stats);

        if ($stats['total'] === 0) continue;
        $risePlanCount++;

        echo "\nRISE #{$row['id']} (client {$row['client_id']}): {$stats['total']} ejercicios\n";
        foreach ($stats['matched'] as $m) echo $m . "\n";
        foreach ($stats['unmatched'] as $u) echo $u . "\n";
        echo "  Matched: " . count($stats['matched']) . "/{$stats['total']}\n";

        if (!$dryRun) {
            $program['plan_entrenamiento'] = $trainingPlan;
            $json = json_encode($program, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stmt = $db->prepare("UPDATE rise_programs SET personalized_program = ? WHERE id = ?");
            $stmt->bind_param('si', $json, $row['id']);
            $stmt->execute();
            echo "  💾 SAVED\n";
        }
    }
} else {
    echo "No active RISE programs found.\n";
}

// ============================================================
// Summary
// ============================================================
echo "\n── SUMMARY ──\n";
echo "Client plans processed: {$clientPlanCount}\n";
echo "RISE programs processed: {$risePlanCount}\n";
echo $dryRun ? "\n⚠️  DRY RUN — run with --execute to apply changes\n" : "\n✅ Migration complete!\n";

$db->close();
