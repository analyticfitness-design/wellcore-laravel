<?php
// DEBUG TEMPORARY — REMOVE AFTER USE
// Usage: GET /_debug-log.php?key=wc-debug-2026

if (($_GET['key'] ?? '') !== 'wc-debug-2026') {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

// Action: limpiar compiled views (?action=clear-views)
if (($_GET['action'] ?? '') === 'clear-views') {
    $vd = __DIR__ . '/../storage/framework/views/';
    $files = glob($vd . '*.php');
    $deleted = 0;
    foreach ($files as $f) {
        if (@unlink($f)) $deleted++;
    }
    echo "Deleted $deleted compiled view files from $vd\n";
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "Called opcache_reset()\n";
    }
    exit;
}

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

echo "\n=== ROUTE CACHE STATE ===\n";
$bootstrapDir = __DIR__ . '/../bootstrap/cache/';
foreach (['routes-v7.php', 'config.php', 'services.php', 'packages.php'] as $f) {
    $p = $bootstrapDir . $f;
    if (file_exists($p)) {
        echo "  $f: " . filesize($p) . " bytes, mod=" . date('Y-m-d H:i:s', filemtime($p)) . "\n";
    }
}

// Try parsing route cache
$routeCacheFile = $bootstrapDir . 'routes-v7.php';
if (file_exists($routeCacheFile)) {
    // Read first 20KB and search for "PlanesController"
    $rc = file_get_contents($routeCacheFile, false, null, 0, 200000);
    $hasPlanes = (substr_count($rc, 'PlanesController') > 0) ? 'YES' : 'NO';
    $hasMethodIndex = (substr_count($rc, "'PlanesController','index'") > 0 || substr_count($rc, 'PlanesController@index') > 0) ? 'YES' : 'NO';
    echo "  Route cache mentions PlanesController: $hasPlanes\n";
}

// Check composer autoload
echo "\n=== COMPOSER AUTOLOAD ===\n";
$classmap = __DIR__ . '/../vendor/composer/autoload_classmap.php';
if (file_exists($classmap)) {
    $cm = include $classmap;
    $key = 'App\\Http\\Controllers\\Public\\PlanesController';
    if (isset($cm[$key])) {
        echo "  Classmap resolves $key → " . $cm[$key] . "\n";
        echo "  File exists: " . (file_exists($cm[$key]) ? 'YES' : 'NO') . "\n";
        if (file_exists($cm[$key])) {
            echo "  File modified: " . date('Y-m-d H:i:s', filemtime($cm[$key])) . "\n";
            $content = file_get_contents($cm[$key]);
            $hasMonthly = (str_contains($content, "'monthlyCop' =>")) ? 'YES' : 'NO';
            echo "  File has 'monthlyCop' key: $hasMonthly\n";
        }
    } else {
        echo "  $key NOT in classmap\n";
        // Search for partial
        $found = array_filter($cm, fn($k) => str_contains($k, 'PlanesController'), ARRAY_FILTER_USE_KEY);
        echo "  Partial matches: " . count($found) . "\n";
        foreach ($found as $k => $v) echo "    $k → $v\n";
    }
}

echo "\n=== CONTROLLER STATE ===\n";
$ctrlPath = __DIR__ . '/../app/Http/Controllers/Public/PlanesController.php';
if (file_exists($ctrlPath)) {
    $ctrlContent = file_get_contents($ctrlPath);
    $hasMonthlyCop = (str_contains($ctrlContent, "'monthlyCop' => \$monthlyCop") || str_contains($ctrlContent, '"monthlyCop"')) ? 'YES' : 'NO';
    echo "  Controller exists: YES\n";
    echo "  Size: " . filesize($ctrlPath) . " bytes\n";
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($ctrlPath)) . "\n";
    echo "  Has 'monthlyCop' key: $hasMonthlyCop\n";
    echo "  First 30 lines preview:\n";
    foreach (array_slice(explode("\n", $ctrlContent), 0, 80) as $i => $l) echo "    " . str_pad($i+1, 3, ' ', STR_PAD_LEFT) . ": $l\n";

    // Try to actually instantiate and run
    echo "\n  === RUNTIME TEST ===\n";
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $svc = $app->make(\App\Services\PricingService::class);
        $ctrl = $app->make(\App\Http\Controllers\Public\PlanesController::class);
        $r = (new \ReflectionMethod($ctrl, 'index'))->getParameters();
        echo "  index() params: " . count($r) . " (should be 1: PricingService)\n";
        $resp = $ctrl->index($svc);
        $data = $resp->getData();
        echo "  view data keys: " . implode(', ', array_keys($data)) . "\n";
        echo "  monthlyCop key present: " . (isset($data['monthlyCop']) ? 'YES' : 'NO') . "\n";
        if (isset($data['monthlyCop'])) {
            echo "  monthlyCop value: " . json_encode($data['monthlyCop']) . "\n";
        }
    } catch (\Throwable $e) {
        echo "  EXCEPTION: " . $e->getMessage() . "\n";
        echo "  AT: " . $e->getFile() . ':' . $e->getLine() . "\n";
    }

    // Inspeccionar la ruta real que responde /planes
    echo "\n  === ROUTE /planes RESOLUTION ===\n";
    try {
        $router = $app->make('router');
        $routes = $router->getRoutes();
        $found = null;
        foreach ($routes as $r) {
            if ($r->uri() === 'planes') { $found = $r; break; }
        }
        if ($found) {
            echo "  URI: " . $found->uri() . "\n";
            echo "  Methods: " . implode(',', $found->methods()) . "\n";
            echo "  Action: " . print_r($found->getAction(), true) . "\n";
            echo "  Controller: " . ($found->getController()::class ?? 'N/A') . "\n";
            echo "  Method: " . $found->getActionMethod() . "\n";
        } else {
            echo "  No route found for 'planes'\n";
        }

        // Hacer un INTERNAL request a /planes y ver qué pasa
        echo "\n  === INTERNAL REQUEST TO /planes ===\n";
        $req = \Illuminate\Http\Request::create('/planes', 'GET');
        try {
            $resp = $app->handle($req);
            echo "  status: " . $resp->getStatusCode() . "\n";
            echo "  content length: " . strlen($resp->getContent()) . "\n";
            echo "  content head: " . substr(strip_tags($resp->getContent()), 0, 300) . "\n";
        } catch (\Throwable $e) {
            echo "  EXCEPTION: " . $e->getMessage() . "\n";
            echo "  AT: " . $e->getFile() . ':' . $e->getLine() . "\n";
        }
    } catch (\Throwable $e) {
        echo "  ROUTE EXCEPTION: " . $e->getMessage() . "\n";
    }
} else {
    echo "  Controller MISSING at: $ctrlPath\n";
}

echo "\n=== OPCACHE STATUS ===\n";
if (function_exists('opcache_get_status')) {
    $oc = @opcache_get_status(false);
    if ($oc) {
        echo "  enabled: " . ($oc['opcache_enabled'] ? 'true' : 'false') . "\n";
        echo "  hits: " . $oc['opcache_statistics']['hits'] . "\n";
        echo "  misses: " . $oc['opcache_statistics']['misses'] . "\n";
        echo "  cached_files: " . $oc['opcache_statistics']['num_cached_scripts'] . "\n";
    } else {
        echo "  cannot read opcache status\n";
    }
} else {
    echo "  opcache extension not loaded\n";
}

echo "\n=== COMPILED VIEW STATE ===\n";
$compiledDir = __DIR__ . '/../storage/framework/views/';
$compiledFile = $compiledDir . '2a08d483f0078eae5e88205b7273b277.php';
if (file_exists($compiledFile)) {
    echo "  Compiled view EXISTS: $compiledFile\n";
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($compiledFile)) . "\n";
    echo "  Line 24 (the failing one):\n";
    $compiled = file($compiledFile);
    foreach ([22, 23, 24, 25, 26] as $n) {
        echo "    " . str_pad($n, 3, ' ', STR_PAD_LEFT) . ": " . ($compiled[$n - 1] ?? '(out of range)') . "";
    }
} else {
    echo "  Compiled view does NOT exist (will be regenerated on next request)\n";
}

echo "\n=== LOG FILES (newest first) ===\n";
foreach ($files as $f) {
    $size = filesize($f);
    $mtime = date('Y-m-d H:i:s', filemtime($f));
    echo "  $mtime  " . str_pad((string)$size, 10, ' ', STR_PAD_LEFT) . "  $f\n";
}

$latest = $files[0];
echo "\n=== LAST production.ERROR MESSAGES (no stack) IN " . basename($latest) . " ===\n";

// Read last 5MB max for performance
$size = filesize($latest);
$readFrom = max(0, $size - 5_000_000);
$fh = fopen($latest, 'r');
if (! $fh) { exit("cannot open $latest"); }
fseek($fh, $readFrom);
$content = stream_get_contents($fh);
fclose($fh);

// Get error message lines (no stack frames)
$lines = explode("\n", $content);
$errLines = array_filter($lines, function($l) {
    return preg_match('/production\.(ERROR|CRITICAL|EMERGENCY|ALERT|WARNING)/', $l);
});

// Take last 10 error messages
$errLines = array_slice($errLines, -10);
foreach ($errLines as $line) {
    // Truncate at 800 chars per line for readability
    echo substr($line, 0, 800) . "\n\n";
}
