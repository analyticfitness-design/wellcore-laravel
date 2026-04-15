<?php
/**
 * Fix specific exercise→GIF mappings in existing plans.
 * Usage:
 *   php scripts/fix-gif-aliases.php            # dry-run
 *   php scripts/fix-gif-aliases.php --execute   # apply
 */

$dryRun = !in_array('--execute', $argv);
$CDN = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

// Exercise name (normalized lowercase) → correct GIF filename
$fixes = [
    'patada de gluteo en polea'                  => 'patada-trasera-en-polea.gif',
    'patada de glúteo en polea'                  => 'patada-trasera-en-polea.gif',
    'patada gluteo en polea'                     => 'patada-trasera-en-polea.gif',
    'abductor en polea'                          => 'patada-lateral-en-polea.gif',
    'abduccion en polea'                         => 'patada-lateral-en-polea.gif',
    'remo con mancuerna un brazo en banco'       => 'remo-con-mancuerna-a-una mano.gif',
    'remo con mancuerna a un brazo'              => 'remo-con-mancuerna-a-una mano.gif',
    'remo con mancuerna unilateral'              => 'remo-con-mancuerna-a-una mano.gif',
    'face pull en polea alta con cuerda'         => 'facepull-en-polea.gif',
    'face pull con cuerda'                       => 'facepull-en-polea.gif',
    'face pull'                                  => 'facepull-en-polea.gif',
    'facepull'                                   => 'facepull-en-polea.gif',
    'romanian deadlift con mancuernas'           => 'peso-muerto-rumano-con-mancuerna.gif',
    'romanian deadlift con mancuerna'            => 'peso-muerto-rumano-con-mancuerna.gif',
    'rdl con mancuernas'                         => 'peso-muerto-rumano-con-mancuerna.gif',
    'zancada con mancuerna'                      => 'zancada-frontal-con-mancuerna.gif',
    'zancada con mancuernas'                     => 'zancada-frontal-con-mancuerna.gif',
    'press en banco inclinado con mancuerna'     => 'press-de-banca-con-mancuernas.gif',
    'press en banco inclinado con mancuernas'    => 'press-de-banca-con-mancuernas.gif',
    'press inclinado con mancuerna'              => 'press-de-banca-con-mancuernas.gif',
    'press inclinado con mancuernas'             => 'press-de-banca-con-mancuernas.gif',
    'extension de triceps en polea alta'         => 'extension-de-triceps-en-polea-con-cuerda.gif',
    'extensión de tríceps en polea alta'         => 'extension-de-triceps-en-polea-con-cuerda.gif',
    'sentadilla búlgara con mancuerna'           => 'sentadilla-bulgara-mancuerna.gif',
    'sentadilla bulgara con mancuerna'           => 'sentadilla-bulgara-mancuerna.gif',
    'sentadilla búlgara con mancuernas'          => 'sentadilla-bulgara-mancuerna.gif',
    'sentadilla bulgara con mancuernas'          => 'sentadilla-bulgara-mancuerna.gif',
    'extension de cuádriceps en maquina'         => 'extension-de-piernas-en-maquina.gif',
    'extension de cuadriceps en maquina'         => 'extension-de-piernas-en-maquina.gif',
    'extensión de cuádriceps en máquina'         => 'extension-de-piernas-en-maquina.gif',
    'zancada reversa con mancuerna'              => 'zancada-inversa-con-mancuernas.gif',
    'zancada reversa con mancuernas'             => 'zancada-inversa-con-mancuernas.gif',
];

// Normalize for comparison
function norm($s) {
    $s = mb_strtolower(trim($s), 'UTF-8');
    $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    return $s;
}

// Build normalized lookup
$normFixes = [];
foreach ($fixes as $name => $gif) {
    $normFixes[norm($name)] = $gif;
}

// DB
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: 'QY@P6Ak2?';
$dbName = getenv('DB_DATABASE') ?: 'wellcore_fitness';
$dbPort = (int)(getenv('DB_PORT') ?: 3306);

$db = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($db->connect_error) die("DB Error: {$db->connect_error}\n");
$db->set_charset('utf8mb4');

echo $dryRun ? "=== DRY RUN ===\n\n" : "=== EXECUTING ===\n\n";

$totalFixed = 0;

function fixExercises(&$exercises, $normFixes, $CDN, &$totalFixed) {
    if (!is_array($exercises)) return;
    foreach ($exercises as &$ej) {
        if (!is_array($ej) || empty($ej['nombre'])) continue;
        $key = norm($ej['nombre']);
        if (isset($normFixes[$key])) {
            $oldUrl = $ej['gif_url'] ?? '(none)';
            $ej['gif_url'] = $CDN . $normFixes[$key];
            unset($ej['gif_filename']);
            echo "  ✅ \"{$ej['nombre']}\" → {$normFixes[$key]}\n";
            $totalFixed++;
        }
    }
    unset($ej);
}

function fixPlan(&$plan, $normFixes, $CDN, &$totalFixed) {
    if (!is_array($plan)) return;
    if (!empty($plan['semanas'])) {
        foreach ($plan['semanas'] as &$sem) {
            foreach ($sem['dias'] ?? [] as &$dia) {
                if (!empty($dia['ejercicios'])) fixExercises($dia['ejercicios'], $normFixes, $CDN, $totalFixed);
            }
            unset($dia);
        }
        unset($sem);
    }
    if (!empty($plan['dias'])) {
        foreach ($plan['dias'] as &$dia) {
            if (!empty($dia['ejercicios'])) fixExercises($dia['ejercicios'], $normFixes, $CDN, $totalFixed);
        }
        unset($dia);
    }
}

// 1. assigned_plans
echo "── ASSIGNED PLANS ──\n";
$r = $db->query("SELECT id, client_id, content FROM assigned_plans WHERE plan_type='entrenamiento' AND content IS NOT NULL AND content != ''");
while ($r && $row = $r->fetch_assoc()) {
    $content = json_decode($row['content'], true);
    if (!$content) continue;
    $before = $totalFixed;
    fixPlan($content, $normFixes, $CDN, $totalFixed);
    if ($totalFixed > $before) {
        echo "Plan #{$row['id']} (client {$row['client_id']}): " . ($totalFixed - $before) . " fixed\n";
        if (!$dryRun) {
            $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stmt = $db->prepare("UPDATE assigned_plans SET content = ? WHERE id = ?");
            $stmt->bind_param('si', $json, $row['id']);
            $stmt->execute();
        }
    }
}

// 2. rise_programs
echo "\n── RISE PROGRAMS ──\n";
$r = $db->query("SELECT id, client_id, personalized_program FROM rise_programs WHERE personalized_program IS NOT NULL AND personalized_program != ''");
while ($r && $row = $r->fetch_assoc()) {
    $prog = json_decode($row['personalized_program'], true);
    if (!$prog || empty($prog['plan_entrenamiento'])) continue;
    $before = $totalFixed;
    fixPlan($prog['plan_entrenamiento'], $normFixes, $CDN, $totalFixed);
    if ($totalFixed > $before) {
        echo "RISE #{$row['id']} (client {$row['client_id']}): " . ($totalFixed - $before) . " fixed\n";
        if (!$dryRun) {
            $json = json_encode($prog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stmt = $db->prepare("UPDATE rise_programs SET personalized_program = ? WHERE id = ?");
            $stmt->bind_param('si', $json, $row['id']);
            $stmt->execute();
        }
    }
}

echo "\n── TOTAL: {$totalFixed} exercises fixed ──\n";
echo $dryRun ? "⚠️  DRY RUN — use --execute to apply\n" : "✅ Done!\n";
$db->close();
