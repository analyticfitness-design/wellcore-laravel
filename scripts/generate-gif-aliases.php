<?php
/**
 * Generate exercise aliases for each GIF based ONLY on its Spanish filename.
 *
 * Rules:
 * - Aliases are variations of HOW to write the SAME exercise
 * - Only reorder, simplify, or use synonyms of words already in the name
 * - NEVER add equipment/position/movement that isn't in the original name
 *
 * Usage: php scripts/generate-gif-aliases.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv);
$gifDir = 'E:/WELLCORE FITNESS PLATAFORMA/Recursos/GIF EJERCICIOS 758';

// Synonyms — ONLY used when the original word is present
$synonyms = [
    'barra' => ['barra libre', 'barra recta', 'barra olimpica'],
    'mancuerna' => ['mancuernas', 'dumbbell'],
    'polea' => ['cable', 'en polea', 'en cable'],
    'maquina' => ['en maquina', 'en aparato'],
    'banda' => ['banda elastica', 'liga', 'banda de resistencia'],
    'kettlebell' => ['pesa rusa'],
    'barra ez' => ['barra z', 'barra w', 'ez bar'],
    'cuerda' => ['soga', 'rope'],
    'disco' => ['plato'],
    'trineo' => ['sled'],
    'press de banca' => ['press en banco', 'bench press', 'press banca'],
    'press' => ['empuje'],
    'sentadilla' => ['squat', 'cuclilla'],
    'peso muerto' => ['deadlift'],
    'zancada' => ['estocada', 'lunge', 'desplante'],
    'remo' => ['row', 'jalon horizontal'],
    'dominadas' => ['pull up', 'pullup', 'pull ups', 'jalones en barra'],
    'flexiones' => ['push up', 'pushup', 'push ups', 'lagartijas'],
    'curl' => ['flexion de brazo'],
    'jalon al pecho' => ['lat pulldown', 'pulldown', 'jalon frontal', 'jalon polea alta'],
    'jalon' => ['pulldown', 'tiron'],
    'elevacion lateral' => ['vuelos laterales', 'lateral raise'],
    'elevacion frontal' => ['front raise'],
    'elevacion de talones' => ['pantorrillas', 'gemelos', 'calf raise'],
    'puente de gluteo' => ['hip thrust en suelo', 'glute bridge', 'puente gluteo'],
    'hip thrust' => ['empuje de cadera'],
    'crunch' => ['abdominal', 'contraccion abdominal'],
    'plancha' => ['plank'],
    'fondos' => ['dips'],
    'encogimiento' => ['shrug', 'encogimiento de hombros'],
    'apertura' => ['fly', 'aperturas', 'cristos'],
    'curl de biceps' => ['curl biceps', 'bicep curl'],
    'curl martillo' => ['hammer curl', 'curl neutro'],
    'curl predicador' => ['preacher curl', 'curl scott', 'curl en banco predicador'],
    'curl concentrado' => ['concentration curl'],
    'extension de triceps' => ['tricep extension', 'extension triceps'],
    'rompe craneos' => ['skull crusher', 'frances', 'press frances'],
    'remo inclinado' => ['bent over row', 'remo con barra inclinado'],
    'remo al menton' => ['upright row', 'remo vertical'],
    'patada de gluteo' => ['glute kickback', 'kickback de gluteo'],
    'buenos dias' => ['good morning'],
    'abdominales' => ['sit up', 'sit ups'],
    'abduccion de cadera' => ['abductora', 'abduccion'],
    'aduccion de cadera' => ['aductora', 'aduccion'],
    'sentadilla goblet' => ['goblet squat', 'copa'],
    'sentadilla bulgara' => ['bulgarian squat', 'bulgarian split squat'],
    'sentadilla sumo' => ['sumo squat'],
    'sentadilla hack' => ['hack squat'],
    'sentadilla frontal' => ['front squat'],
    'rack pull' => ['rack pull parcial', 'jalon de rack'],
    'de pie' => ['parado', 'de pie'],
    'sentado' => ['sentada'],
    'acostado' => ['tumbado', 'recostado'],
    'inclinado' => ['en inclinacion', 'en banco inclinado'],
    'declinado' => ['en declinacion', 'en banco declinado'],
    'de rodillas' => ['arrodillado'],
    'un brazo' => ['unilateral brazo', 'a un brazo', 'con un brazo'],
    'una pierna' => ['unilateral pierna', 'a una pierna', 'con una pierna'],
    'agarre cerrado' => ['agarre estrecho', 'close grip'],
    'agarre abierto' => ['agarre ancho', 'wide grip'],
    'agarre inverso' => ['agarre supino', 'reverse grip'],
    'alternado' => ['alterno', 'alternando'],
    'curl de pierna' => ['leg curl', 'femoral'],
    'extension de pierna' => ['leg extension', 'cuadriceps en maquina', 'extension de cuadriceps'],
    'prensa de pierna' => ['leg press', 'prensa'],
    'elevacion de piernas' => ['leg raise'],
    'curl de muneca' => ['wrist curl', 'curl muneca'],
    'pullover' => ['pull over'],
    'pierna rigida' => ['piernas rigidas', 'stiff leg'],
    'pierna recta' => ['piernas rectas', 'straight leg'],
    'deltoides posterior' => ['deltoides trasero', 'rear delt', 'pajaro'],
    'elevacion de rodillas' => ['knee raise'],
    'subida al banco' => ['step up'],
    'escaladores' => ['mountain climber', 'mountain climbers'],
    'bicicleta en el aire' => ['air bike', 'bicicleta'],
    'jalon a la cara' => ['face pull'],
    'separacion' => ['pull apart', 'separacion de banda'],
];

// ═══════════════════════════════════════════════════════════════
// ALIAS GENERATION LOGIC
// ═══════════════════════════════════════════════════════════════

function generateAliases(string $gifFilename, array $synonyms): array
{
    // Remove .gif extension and convert hyphens to spaces
    $name = str_replace('.gif', '', $gifFilename);
    $name = str_replace('-', ' ', $name);
    $name = mb_strtolower(trim($name));

    $aliases = [];

    // 1. The full name as-is is the primary alias
    $aliases[] = $name;

    // 2. Identify parts: equipment, position, movement
    $equipment = [];
    $equipmentWords = ['barra', 'barra ez', 'mancuerna', 'polea', 'maquina', 'banda', 'kettlebell', 'cuerda', 'disco', 'trineo', 'landmine'];

    $positions = [];
    $positionWords = ['de pie', 'sentado', 'acostado', 'inclinado', 'declinado', 'de rodillas', 'boca abajo', 'boca arriba', 'sobre cabeza'];

    // Check which equipment/positions are in the name
    foreach ($equipmentWords as $eq) {
        if (strpos($name, $eq) !== false) {
            $equipment[] = $eq;
        }
    }
    foreach ($positionWords as $pos) {
        if (strpos($name, $pos) !== false) {
            $positions[] = $pos;
        }
    }

    // 3. Generate reordered versions
    // "barra inclinado press de banca" → "press de banca inclinado con barra"
    $nameWithoutEquip = $name;
    foreach ($equipment as $eq) {
        $nameWithoutEquip = trim(str_replace($eq, '', $nameWithoutEquip));
    }
    $nameWithoutEquip = preg_replace('/\s+/', ' ', trim($nameWithoutEquip));

    if (!empty($equipment)) {
        // Movement + "con" + equipment
        foreach ($equipment as $eq) {
            $aliases[] = $nameWithoutEquip . ' con ' . $eq;
            $aliases[] = $nameWithoutEquip . ' ' . $eq;
        }
    }

    // 4. Without position (simplified) — use word boundaries to avoid partial matches
    $nameWithoutPos = $name;
    foreach ($positions as $pos) {
        $pattern = '/\b' . preg_quote($pos, '/') . '\b/u';
        $nameWithoutPos = preg_replace($pattern, '', $nameWithoutPos);
    }
    $nameWithoutPos = preg_replace('/\s+/', ' ', trim($nameWithoutPos));
    if ($nameWithoutPos !== $name && strlen($nameWithoutPos) > 5) {
        $aliases[] = $nameWithoutPos;
    }

    // 5. Without filler words
    $simplified = preg_replace('/\b(de|del|en|el|la|los|las|con|al|a)\b/', '', $name);
    $simplified = preg_replace('/\s+/', ' ', trim($simplified));
    if ($simplified !== $name && strlen($simplified) > 5) {
        $aliases[] = $simplified;
    }

    // 6. Synonym-based aliases — only for words/phrases ALREADY in the name
    // Sort by length descending to match longer phrases first
    $sortedSynonyms = $synonyms;
    uksort($sortedSynonyms, fn($a, $b) => strlen($b) - strlen($a));

    foreach ($sortedSynonyms as $original => $syns) {
        // Use word boundary matching to avoid partial replacements
        $pattern = '/\b' . preg_quote($original, '/') . '\b/u';
        if (preg_match($pattern, $name)) {
            foreach ($syns as $syn) {
                $variant = preg_replace($pattern, $syn, $name, 1);
                $variant = preg_replace('/\s+/', ' ', trim($variant));
                if ($variant !== $name && strlen($variant) > 3) {
                    $aliases[] = $variant;
                }
            }
        }
    }

    // 7. Clean up: normalize, deduplicate
    $aliases = array_map(function($a) {
        $a = mb_strtolower(trim($a));
        $a = preg_replace('/\s+/', ' ', $a);
        // Remove accents for matching
        $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n'];
        $a = strtr($a, $map);
        return $a;
    }, $aliases);

    $aliases = array_unique($aliases);
    $aliases = array_filter($aliases, fn($a) => strlen($a) > 3);

    return array_values($aliases);
}

// ═══════════════════════════════════════════════════════════════
// EXECUTE
// ═══════════════════════════════════════════════════════════════

$files = glob($gifDir . '/*.gif');
if (empty($files)) {
    echo "No GIF files found in: {$gifDir}\n";
    exit(1);
}

// Load the mapping JSON to know old filename → new filename
$mappingFile = $gifDir . '/gif-mapping.json';
$mapping = [];
if (file_exists($mappingFile)) {
    $mapping = json_decode(file_get_contents($mappingFile), true);
}
// Invert: new_filename => old_filename (for DB reference)
$reverseMapping = array_flip($mapping);

$totalAliases = 0;
$allData = []; // gif_filename => [aliases]

foreach ($files as $filePath) {
    $newName = basename($filePath);
    $oldName = $reverseMapping[$newName] ?? $newName;

    $aliases = generateAliases($newName, $synonyms);
    $allData[$newName] = [
        'old_filename' => $oldName,
        'aliases' => $aliases,
    ];
    $totalAliases += count($aliases);

    if ($dryRun) {
        echo "\n=== {$newName} ===\n";
        echo "  (was: {$oldName})\n";
        foreach ($aliases as $alias) {
            echo "  - {$alias}\n";
        }
    }
}

echo "\n";
echo "GIFs: " . count($allData) . "\n";
echo "Total aliases: {$totalAliases}\n";
echo "Average aliases per GIF: " . round($totalAliases / count($allData), 1) . "\n";

// Save aliases JSON
$outputFile = $gifDir . '/gif-aliases.json';
file_put_contents($outputFile, json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Aliases saved to: {$outputFile}\n";

if ($dryRun) {
    echo "\nDRY RUN — review the aliases above.\n";
}
