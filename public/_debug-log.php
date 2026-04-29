<?php
// DEBUG TEMPORARY — REMOVE AFTER USE
// Usage: GET /_debug-log.php?key=wc-debug-2026

if (($_GET['key'] ?? '') !== 'wc-debug-2026') {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

$logsDir = __DIR__ . '/../storage/logs/';
$files = glob($logsDir . 'laravel*.log');
if (! $files) {
    echo "No log files found in $logsDir\n";
    echo "Listing all files:\n";
    foreach (scandir($logsDir) ?: [] as $f) echo "  $f\n";
    exit;
}

// Sort by mtime desc
usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

echo "=== ENV ===\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $env = file_get_contents($envFile);
    foreach (explode("\n", $env) as $line) {
        if (preg_match('/^(APP_ENV|APP_DEBUG|LOG_)/', $line)) echo "  $line\n";
    }
}

echo "\n=== LOG FILES (newest first) ===\n";
foreach ($files as $f) {
    $size = filesize($f);
    $mtime = date('Y-m-d H:i:s', filemtime($f));
    echo "  $mtime  " . str_pad((string)$size, 10, ' ', STR_PAD_LEFT) . "  $f\n";
}

$latest = $files[0];
echo "\n=== TAIL 200 LINES OF " . basename($latest) . " ===\n";

$lines = [];
$fh = fopen($latest, 'r');
if ($fh) {
    while (($line = fgets($fh)) !== false) {
        $lines[] = $line;
        if (count($lines) > 200) array_shift($lines);
    }
    fclose($fh);
}
echo implode('', $lines);
