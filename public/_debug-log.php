<?php
// DEBUG TEMPORARY — REMOVE AFTER USE
// Usage: GET /_debug-log.php?key=wc-debug-planes-v2b[&action=clear-views]

if (($_GET['key'] ?? '') !== 'wc-debug-planes-v2b') {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

// Action: limpiar compiled views
if (($_GET['action'] ?? '') === 'clear-views') {
    $vd = __DIR__ . '/../storage/framework/views/';
    $files = glob($vd . '*.php');
    $deleted = 0;
    foreach ($files as $f) { if (@unlink($f)) $deleted++; }
    echo "Deleted $deleted compiled views\n";
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "Called opcache_reset()\n";
    }
    exit;
}

// ENV
echo "=== ENV ===\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (explode("\n", file_get_contents($envFile)) as $line) {
        if (preg_match('/^(APP_ENV|APP_DEBUG|LOG_)/', $line)) echo "  $line\n";
    }
}

// LOG FILES
echo "\n=== LOG FILES (newest first) ===\n";
$logsDir = __DIR__ . '/../storage/logs/';
$files = glob($logsDir . 'laravel*.log');
usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
foreach ($files as $f) {
    echo "  " . date('Y-m-d H:i:s', filemtime($f)) . "  "
       . str_pad((string)filesize($f), 10, ' ', STR_PAD_LEFT) . "  $f\n";
}

// LAST ERRORS
$latest = $files[0];
echo "\n=== LAST production.ERROR MESSAGES IN " . basename($latest) . " ===\n";
$content = file_get_contents($latest, false, null, max(0, filesize($latest) - 5_000_000));
$lines = explode("\n", $content);
$errLines = array_filter($lines, fn($l) => preg_match('/production\.(ERROR|CRITICAL|EMERGENCY|ALERT|WARNING)/', $l));
foreach (array_slice($errLines, -15) as $line) echo substr($line, 0, 800) . "\n\n";

// CONTROLLER STATE
echo "\n=== CONTROLLER STATE ===\n";
$ctrlPath = __DIR__ . '/../app/Http/Controllers/Public/PlanesController.php';
if (file_exists($ctrlPath)) {
    $c = file_get_contents($ctrlPath);
    echo "  Size: " . filesize($ctrlPath) . " bytes\n";
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($ctrlPath)) . "\n";
    echo "  Has 'monthlyCop': " . (str_contains($c, "'monthlyCop' =>") ? 'YES' : 'NO') . "\n";
    echo "  Has 'pricesCop': " . (str_contains($c, "'pricesCop'") ? 'YES' : 'NO') . "\n";
} else {
    echo "  CONTROLLER NOT FOUND\n";
}

// OPCACHE
echo "\n=== OPCACHE STATUS ===\n";
if (function_exists('opcache_get_status')) {
    $oc = @opcache_get_status(false);
    if ($oc) {
        echo "  enabled: " . ($oc['opcache_enabled'] ? 'true' : 'false') . "\n";
        echo "  hits: " . $oc['opcache_statistics']['hits'] . "\n";
        echo "  cached_files: " . $oc['opcache_statistics']['num_cached_scripts'] . "\n";
    }
}

// COMPOSER CLASSMAP
echo "\n=== COMPOSER AUTOLOAD ===\n";
$classmap = __DIR__ . '/../vendor/composer/autoload_classmap.php';
if (file_exists($classmap)) {
    $cm = include $classmap;
    $key = 'App\\Http\\Controllers\\Public\\PlanesController';
    if (isset($cm[$key])) {
        echo "  $key → " . $cm[$key] . "\n";
        echo "  File modified: " . date('Y-m-d H:i:s', filemtime($cm[$key])) . "\n";
    } else {
        echo "  $key NOT in classmap\n";
    }
}

// COMPILED VIEW STATE
echo "\n=== COMPILED VIEWS (planes related) ===\n";
$compiledDir = __DIR__ . '/../storage/framework/views/';
$compiledFiles = glob($compiledDir . '*.php');
if ($compiledFiles) {
    foreach ($compiledFiles as $cf) {
        echo "  " . basename($cf) . " — modified: " . date('Y-m-d H:i:s', filemtime($cf)) . "\n";
    }
} else {
    echo "  No compiled views found\n";
}
