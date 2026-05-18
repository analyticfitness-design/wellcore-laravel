<?php
$path = __DIR__ . '/lint-rules-seed.json';
$j = json_decode(file_get_contents($path));
if ($j === null) {
    echo 'JSON INVALIDO: ' . json_last_error_msg() . PHP_EOL;
    exit(1);
}
echo 'JSON OK · rules count: ' . count($j->rules) . PHP_EOL;
echo str_repeat('-', 110) . PHP_EOL;
$cats = [];
$sevs = ['error' => 0, 'warning' => 0, 'info' => 0];
$autofix = 0;
foreach ($j->rules as $r) {
    $cats[$r->category] = ($cats[$r->category] ?? 0) + 1;
    $sevs[$r->severity]++;
    if (!empty($r->auto_fix) && $r->auto_fix->supported) $autofix++;
    printf("  %-55s | %-10s | %-12s | auto_fix:%s\n", $r->slug, $r->severity, $r->category, ($r->auto_fix->supported ? 'YES' : 'no'));
}
echo str_repeat('-', 110) . PHP_EOL;
echo 'BY CATEGORY: ';
foreach ($cats as $k => $v) echo "$k=$v ";
echo PHP_EOL;
echo 'BY SEVERITY: ';
foreach ($sevs as $k => $v) echo "$k=$v ";
echo PHP_EOL;
echo "AUTO_FIX SUPPORTED: $autofix / " . count($j->rules) . PHP_EOL;

// Verify required fields per rule
$missing = [];
foreach ($j->rules as $r) {
    $required = ['slug', 'name', 'category', 'severity', 'check_function', 'check_function_signature', 'message_template', 'examples', 'version', 'active'];
    foreach ($required as $f) {
        if (!isset($r->$f)) $missing[] = "{$r->slug}: missing {$f}";
    }
    if (!isset($r->examples->input_passing) || !isset($r->examples->input_failing)) {
        $missing[] = "{$r->slug}: missing examples.input_passing or input_failing";
    }
}
if (empty($missing)) echo "REQUIRED FIELDS: all rules OK\n";
else { echo "REQUIRED FIELDS MISSING:\n"; foreach ($missing as $m) echo "  - $m\n"; }
