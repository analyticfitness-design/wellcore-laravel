<?php

/**
 * Security headers verifier — Wave 3 Fase K.
 * Usage: php scripts/security-headers-check.php https://wellcorefitness.com
 * Exits 0 if all required headers present, 1 otherwise.
 */

$requiredHeaders = [
    'content-security-policy'   => null,
    'strict-transport-security' => 'max-age=31536000',
    'x-content-type-options'    => 'nosniff',
    'x-frame-options'           => 'sameorigin',
    'referrer-policy'           => 'strict-origin-when-cross-origin',
    'permissions-policy'        => 'camera=()',
];

$domain = $argv[1] ?? 'https://wellcorefitness.com';

$context = stream_context_create([
    'http'  => ['method' => 'HEAD', 'timeout' => 10, 'ignore_errors' => true],
    'https' => ['method' => 'HEAD', 'timeout' => 10, 'ignore_errors' => true],
]);

$fp = @fopen($domain, 'r', false, $context);
if ($fp === false) {
    echo "Error fetching {$domain}" . PHP_EOL;
    exit(2);
}
$meta = stream_get_meta_data($fp);
fclose($fp);

$headerLines = strtolower(implode("\n", $meta['wrapper_data'] ?? []));

$pass = true;
foreach ($requiredHeaders as $header => $expectedSubstring) {
    if (!str_contains($headerLines, $header . ':')) {
        echo "MISSING: {$header}" . PHP_EOL;
        $pass = false;
        continue;
    }
    if ($expectedSubstring !== null && !str_contains($headerLines, strtolower($expectedSubstring))) {
        echo "WEAK: {$header} no contiene '{$expectedSubstring}'" . PHP_EOL;
        $pass = false;
        continue;
    }
    echo "OK: {$header}" . PHP_EOL;
}

exit($pass ? 0 : 1);
