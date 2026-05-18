<?php
$path = __DIR__ . '/nutrition-foods-seed.json';
$j = json_decode(file_get_contents($path));
if ($j === null) {
    echo 'JSON INVALIDO: ' . json_last_error_msg() . PHP_EOL;
    exit(1);
}
echo 'JSON OK · foods count: ' . count($j->foods) . PHP_EOL;
echo str_repeat('-', 110) . PHP_EOL;
$allPass = true;
foreach ($j->foods as $f) {
    $passes = $f->macros_sanity_check->passes ? 'PASS' : 'FAIL';
    if (!$f->macros_sanity_check->passes) $allPass = false;
    echo sprintf(
        '  %-28s | kcal calc %6.1f vs %6.1f (diff %4.1f) %s | %s | %-12s | conf:%s%s' . PHP_EOL,
        $f->slug,
        $f->macros_sanity_check->calculated_kcal,
        $f->macros_sanity_check->actual_kcal,
        $f->macros_sanity_check->diff_kcal,
        $passes,
        $f->icon_emoji,
        $f->shopping_category_ui_v1,
        $f->confidence,
        $f->needs_daniel_validation ? ' [NEEDS_VAL]' : ''
    );
}
echo str_repeat('-', 110) . PHP_EOL;
echo $allPass ? 'TODOS LOS macros_sanity_check PASAN ✓' : 'HAY FAILS — revisar' . PHP_EOL;
echo 'needs_daniel_validation:true count = ' . count(array_filter($j->foods, fn($f) => $f->needs_daniel_validation)) . PHP_EOL;
echo 'confidence breakdown: ';
$conf = ['high'=>0,'moderate'=>0,'low'=>0];
foreach ($j->foods as $f) $conf[$f->confidence]++;
foreach ($conf as $k=>$v) echo "$k=$v ";
echo PHP_EOL;
