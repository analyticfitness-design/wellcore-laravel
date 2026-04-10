<?php

/**
 * Rename exercise GIFs from English to clean Spanish names.
 *
 * Input:  00881301-Barbell-Seated-Calf-Raise_Calves_720.gif
 * Output: elevacion-de-talones-sentado-con-barra.gif
 *
 * Usage: php scripts/rename-gifs-to-spanish.php [--dry-run] [--source DIR] [--output DIR]
 */
$dryRun = in_array('--dry-run', $argv);
$sourceDir = null;
$outputDir = null;

foreach ($argv as $i => $arg) {
    if ($arg === '--source' && isset($argv[$i + 1])) {
        $sourceDir = $argv[$i + 1];
    }
    if ($arg === '--output' && isset($argv[$i + 1])) {
        $outputDir = $argv[$i + 1];
    }
}

$sourceDir = $sourceDir ?: 'C:/Users/GODSF/Music/ROLES Y DATOS DE LA PLATAFORMA WELLCORE/BASE DE DATOS GIF/MODIFICAICONES REFERENCIAS SIN FONDO';
$outputDir = $outputDir ?: 'C:/Users/GODSF/Music/ROLES Y DATOS DE LA PLATAFORMA WELLCORE/BASE DE DATOS GIF/GIFs-Español';

// ═══════════════════════════════════════════════════════════════
// COMPOUND PHRASES — matched FIRST (order matters: longest first)
// ═══════════════════════════════════════════════════════════════
$compounds = [
    // === EXERCISES (core movements) ===
    'calf raise' => 'elevacion de talones',
    'calf press' => 'prensa de pantorrillas',
    'bench press' => 'press de banca',
    'chest press' => 'press de pecho',
    'shoulder press' => 'press de hombro',
    'overhead press' => 'press sobre cabeza',
    'floor press' => 'press en suelo',
    'close grip press' => 'press agarre cerrado',
    'wide grip press' => 'press agarre abierto',
    'skull crusher' => 'rompe craneos',
    'skull press' => 'press a craneo',
    'rack pull' => 'rack~pull',
    'hip thrust' => 'hip~thrust',
    'on knees' => 'de rodillas',
    'biceps curl' => 'curl de biceps',
    'hammer curl' => 'curl martillo',
    'concentration curl' => 'curl concentrado',
    'preacher curl' => 'curl predicador',
    'wrist curl' => 'curl de muneca',
    'drag curl' => 'curl arrastre',
    'spider curl' => 'curl arana',
    'reverse curl' => 'curl inverso',
    'leg curl' => 'curl de pierna',
    'leg extension' => 'extension de pierna',
    'leg press' => 'prensa de pierna',
    'leg raise' => 'elevacion de piernas',
    'hip thrust' => 'hip~thrust',
    'hip lift' => 'puente de gluteo',
    'hip flexion' => 'flexion de cadera',
    'hip extension' => 'extension de cadera',
    'hip abduction' => 'abduccion de cadera',
    'hip adduction' => 'aduccion de cadera',
    'hip external rotation' => 'rotacion externa de cadera',
    'hip internal rotation' => 'rotacion interna de cadera',
    'glute bridge' => 'puente de gluteo',
    'glute kickback' => 'patada de gluteo',
    'good morning' => 'buenos dias',
    'bent over row' => 'remo inclinado',
    'upright row' => 'remo al menton',
    'seated row' => 'remo sentado',
    'low row' => 'remo bajo',
    'high row' => 'remo alto',
    'face pull' => 'jalon a la cara',
    'pull up' => 'dominadas',
    'pull apart' => 'separacion',
    'chin up' => 'dominadas supinas',
    'push up' => 'flexiones',
    'push down' => 'empuje hacia abajo',
    'push press' => 'press empuje',
    'lat pulldown' => 'jalon al pecho',
    'lateral pulldown' => 'jalon lateral',
    'lateral raise' => 'elevacion lateral',
    'front raise' => 'elevacion frontal',
    'rear delt' => 'deltoides posterior',
    'rear lateral raise' => 'elevacion lateral posterior',
    'side bend' => 'inclinacion lateral',
    'side plank' => 'plancha lateral',
    'side lunge' => 'zancada lateral',
    'step up' => 'subida al banco',
    'jump squat' => 'sentadilla con salto',
    'full squat' => 'sentadilla completa',
    'split squat' => 'sentadilla dividida',
    'pistol squat' => 'sentadilla pistola',
    'goblet squat' => 'sentadilla goblet',
    'hack squat' => 'sentadilla hack',
    'front squat' => 'sentadilla frontal',
    'sumo squat' => 'sentadilla sumo',
    'narrow stance squat' => 'sentadilla estrecha',
    'dead lift' => 'peso muerto',
    'deadlift' => 'peso muerto',
    'stiff leg' => 'pierna rigida',
    'straight leg' => 'pierna recta',
    'single leg' => 'una pierna',
    'one leg' => 'una pierna',
    'one arm' => 'un brazo',
    'single arm' => 'un brazo',
    'heel touchers' => 'toque de talones',
    'heel touch' => 'toque de talones',
    'mountain climber' => 'escaladores',
    'jack knife' => 'navaja',
    'sit up' => 'abdominales',
    'hanging knee raise' => 'elevacion de rodillas colgado',
    'knee raise' => 'elevacion de rodillas',
    'cross body' => 'cruzado',
    'air bike' => 'bicicleta en el aire',
    'assault bike' => 'bicicleta assault',
    'exercise ball' => 'pelota de ejercicio',
    'stability ball' => 'pelota de estabilidad',
    'medicine ball' => 'balon medicinal',
    'bosu ball' => 'bosu',
    'foam roller' => 'rodillo de espuma',
    'bed sheet' => 'sabana',
    'body weight' => 'peso corporal',
    'smith machine' => 'maquina smith',
    'pec deck' => 'pec deck',
    'chest fly' => 'apertura de pecho',
    'high fly' => 'apertura alta',
    'low fly' => 'apertura baja',
    'triceps extension' => 'extension de triceps',
    'triceps dip' => 'fondos de triceps',
    'chest dip' => 'fondos de pecho',
    'back extension' => 'extension de espalda',
    'neck curl' => 'curl de cuello',
    'neck extension' => 'extension de cuello',
    'neck lateral flexion' => 'flexion lateral de cuello',
    'close grip' => 'agarre cerrado',
    'wide grip' => 'agarre abierto',
    'reverse grip' => 'agarre inverso',
    'underhand' => 'agarre supino',
    'overhand' => 'agarre prono',
    'drop set' => 'serie descendente',
    'run in place' => 'correr en sitio',
    'high knee' => 'rodillas altas',
    'backwards run' => 'correr hacia atras',
    'tuck crunch' => 'crunch encogido',
    'twisting crunch' => 'crunch con giro',
    'bicycle crunch' => 'crunch bicicleta',
    'v up' => 'v up',
    'y raise' => 'elevacion en y',
    'pull through' => 'jalon entre piernas',
    'elbow to knee' => 'codo a rodilla',
    'elbow press' => 'press de codo',
];

// ═══════════════════════════════════════════════════════════════
// SINGLE WORD translations
// ═══════════════════════════════════════════════════════════════
$words = [
    // Equipment
    'barbell' => 'barra',
    'dumbbell' => 'mancuerna',
    'dumbell' => 'mancuerna',
    'cable' => 'polea',
    'band' => 'banda',
    'lever' => 'maquina',
    'machine' => 'maquina',
    'kettlebell' => 'kettlebell',
    'ez' => 'barra ez',
    'rope' => 'cuerda',
    'plate' => 'disco',
    'loaded' => 'cargado',
    'weighted' => 'con peso',
    'assisted' => 'asistido',
    'sled' => 'trineo',
    'trap' => 'barra hexagonal',
    'smith' => 'smith',
    'olympic' => 'olimpica',

    // Position
    'standing' => 'de pie',
    'seated' => 'sentado',
    'lying' => 'acostado',
    'kneeling' => 'de rodillas',
    'incline' => 'inclinado',
    'decline' => 'declinado',
    'flat' => 'plano',
    'prone' => 'boca abajo',
    'supine' => 'boca arriba',
    'overhead' => 'sobre cabeza',
    'behind' => 'detras',
    'across' => 'cruzado',
    'alternate' => 'alternado',
    'alternating' => 'alternado',

    // Movement type
    'curl' => 'curl',
    'press' => 'press',
    'fly' => 'apertura',
    'raise' => 'elevacion',
    'extension' => 'extension',
    'flexion' => 'flexion',
    'rotation' => 'rotacion',
    'pullover' => 'pullover',
    'pulldown' => 'jalon',
    'pushdown' => 'empuje abajo',
    'row' => 'remo',
    'squat' => 'sentadilla',
    'lunge' => 'zancada',
    'crunch' => 'crunch',
    'plank' => 'plancha',
    'shrug' => 'encogimiento',
    'twist' => 'giro',
    'twisting' => 'con giro',
    'kickback' => 'patada',
    'swing' => 'balanceo',
    'snatch' => 'arrancada',
    'clean' => 'cargada',
    'jerk' => 'envion',
    'dip' => 'fondos',
    'step' => 'paso',
    'walk' => 'caminata',
    'run' => 'correr',
    'jump' => 'salto',
    'skip' => 'saltar',
    'sprint' => 'sprint',
    'stretch' => 'estiramiento',
    'hold' => 'mantenimiento',
    'drag' => 'arrastre',
    'punch' => 'golpe',
    'punching' => 'golpes',
    'push' => 'empuje',
    'pull' => 'jalon',

    // Body parts
    'chest' => 'pecho',
    'back' => 'espalda',
    'shoulder' => 'hombro',
    'shoulders' => 'hombros',
    'biceps' => 'biceps',
    'triceps' => 'triceps',
    'forearm' => 'antebrazo',
    'forearms' => 'antebrazos',
    'arm' => 'brazo',
    'arms' => 'brazos',
    'leg' => 'pierna',
    'legs' => 'piernas',
    'hip' => 'cadera',
    'hips' => 'caderas',
    'glute' => 'gluteo',
    'glutes' => 'gluteos',
    'calf' => 'pantorrilla',
    'calves' => 'pantorrillas',
    'thigh' => 'muslo',
    'thighs' => 'muslos',
    'quad' => 'cuadriceps',
    'quads' => 'cuadriceps',
    'hamstring' => 'isquiotibiales',
    'hamstrings' => 'isquiotibiales',
    'ab' => 'abdomen',
    'abs' => 'abdominales',
    'waist' => 'cintura',
    'neck' => 'cuello',
    'wrist' => 'muneca',
    'knee' => 'rodilla',
    'heel' => 'talon',
    'toe' => 'punta',
    'delt' => 'deltoides',
    'lat' => 'dorsal',
    'lats' => 'dorsales',
    'pec' => 'pectoral',
    'trap' => 'trapecio',
    'traps' => 'trapecios',
    'oblique' => 'oblicuo',
    'obliques' => 'oblicuos',
    'core' => 'core',
    'upper' => 'superior',
    'lower' => 'inferior',

    // Modifiers
    'reverse' => 'inverso',
    'close' => 'cerrado',
    'wide' => 'abierto',
    'narrow' => 'estrecho',
    'front' => 'frontal',
    'rear' => 'posterior',
    'side' => 'lateral',
    'high' => 'alto',
    'low' => 'bajo',
    'inner' => 'interno',
    'outer' => 'externo',
    'external' => 'externo',
    'internal' => 'interno',
    'bent' => 'flexionado',
    'straight' => 'recto',
    'stiff' => 'rigido',
    'full' => 'completo',
    'half' => 'medio',
    'single' => 'simple',
    'double' => 'doble',
    'on' => 'en',
    'with' => 'con',
    'to' => 'a',
    'and' => 'y',
    'the' => '',
    'in' => 'en',
    'of' => 'de',
    'for' => 'para',
    'from' => 'desde',
    'over' => 'sobre',
    'under' => 'bajo',
    'behind' => 'detras',
    'between' => 'entre',

    // Misc
    'male' => '',
    'female' => '',
    'version' => 'version',
    'fix' => '',
    'sfix' => '',
    'ii' => '2',
    '2' => '2',
    '3' => '3',
    'archer' => 'arquero',
    'fighter' => 'luchador',
    'military' => 'militar',
    'sumo' => 'sumo',
    'bulgarian' => 'bulgara',
    'romanian' => 'rumano',
    'russian' => 'ruso',
    'cardio' => 'cardio',
    'bar' => 'barra',
    'bench' => 'banco',
    'floor' => 'suelo',
    'wall' => 'pared',
    'hands' => 'manos',
    'feet' => 'pies',
    'two' => 'dos',
    'three' => 'tres',
    'four' => 'cuatro',
    'forth' => 'adelante',
    'head' => 'cabeza',
    'hand' => 'mano',
    'finger' => 'dedo',
    'stance' => 'posicion',
    'grip' => 'agarre',
    'deck' => 'deck',
    'circle' => 'circulo',
    'apart' => 'separado',
    'together' => 'juntos',
    'cross' => 'cruzado',
    'touching' => 'tocando',
    'pendlay' => 'pendlay',
    'zercher' => 'zercher',
    'landmine' => 'landmine',
    'around' => 'alrededor',
    'scissor' => 'tijera',
    'scissors' => 'tijeras',
    'door' => 'puerta',
    'way' => '',
    'up' => 'arriba',
    'down' => 'abajo',
    'lift' => 'elevacion',
    'kick' => 'patada',
    'thrust' => 'thrust',
    'skull' => 'craneo',
    'rack' => 'rack',
    'harness' => 'arnes',
    'without' => 'sin',
    'knees' => 'rodillas',
    'skier' => 'esquiador',
    'bradford' => 'bradford',
    'revers' => 'inverso',
    'sliding' => 'deslizamiento',
    'roller' => 'rodillo',
    'jack' => 'jack',
    'star' => 'estrella',
    'tap' => 'toque',
    'tuck' => 'encogido',
    'pike' => 'pike',
    'burpee' => 'burpee',
    'sprinter' => 'sprinter',
    'climber' => 'escalador',
    'swimmer' => 'nadador',
    'superman' => 'superman',
    'hyperextension' => 'hiperextension',
    'roller' => 'rodillo',
    'bridge' => 'puente',
    'abduction' => 'abduccion',
    'adduction' => 'aduccion',
    'elbow' => 'codo',
    'fixed' => 'fijo',
    'inverted' => 'invertido',
    'inverse' => 'inverso',
    'through' => 'entre-piernas',
    'marches' => 'marcha',
    'march' => 'marcha',
    'lifting' => 'elevacion',
    'twists' => 'giros',
    'angled' => 'angulado',
    'straps' => 'correas',
    'cage' => 'jaula',
    'dips' => 'fondos',
    'extended' => 'extendido',
    'leaning' => 'inclinado',
    'spread' => 'abierto',
    'neutral' => 'neutro',
    'crossed' => 'cruzado',
    'sumo' => 'sumo',
    'donkey' => 'donkey',
    'body' => 'cuerpo',
    'diagonal' => 'diagonal',
    'self' => 'auto',
    'backward' => 'atras',
    'backwards' => 'atras',
    'forward' => 'adelante',
    'm' => '',
    'wtih' => 'con', // typo in source files
    'rotatio' => 'rotacion', // typo in source files
];

// ═══════════════════════════════════════════════════════════════
// MAIN LOGIC
// ═══════════════════════════════════════════════════════════════

function parseFilename(string $filename): string
{
    // Remove extension
    $name = pathinfo($filename, PATHINFO_FILENAME);

    // Remove numeric prefix (e.g., "00881301-")
    $name = preg_replace('/^\d+-/', '', $name);

    // Remove muscle group suffix (e.g., "_Calves_720" or "_Upper-Arms_720" or "_waist-FIX_720")
    // Pattern: _MusclePart_720 or _muscle-part-FIX_720
    $name = preg_replace('/_[A-Za-z\-]+_720$/', '', $name);
    $name = preg_replace('/_720$/', '', $name);

    // Replace hyphens and underscores with spaces
    $name = str_replace(['-', '_'], ' ', $name);

    // Clean up extra spaces
    return trim(preg_replace('/\s+/', ' ', $name));
}

function translateToSpanish(string $englishName, array $compounds, array $words): string
{
    $text = mb_strtolower($englishName);

    // Sort compounds by length descending (match longest first)
    uksort($compounds, fn ($a, $b) => strlen($b) - strlen($a));

    // Replace compound phrases first
    foreach ($compounds as $en => $es) {
        $pattern = '/\b'.preg_quote($en, '/').'\b/i';
        $text = preg_replace($pattern, $es, $text);
    }

    // Replace remaining individual words
    $parts = explode(' ', $text);
    $translated = [];
    foreach ($parts as $part) {
        $lower = strtolower(trim($part));
        if ($lower === '') {
            continue;
        }
        if (isset($words[$lower])) {
            $w = $words[$lower];
            if ($w !== '') {
                $translated[] = $w;
            }
        } else {
            // Keep as-is if not in dictionary (proper nouns, numbers, etc.)
            $translated[] = $lower;
        }
    }

    return implode(' ', $translated);
}

function toCleanFilename(string $spanishName): string
{
    // Remove accents for filename (keep readable)
    $name = $spanishName;

    // Replace tilde (compound protector) and spaces with hyphens
    $name = str_replace(['~', ' '], '-', $name);

    // Remove any double hyphens
    $name = preg_replace('/-+/', '-', $name);

    // Remove trailing/leading hyphens
    $name = trim($name, '-');

    return $name.'.gif';
}

// ═══════════════════════════════════════════════════════════════
// EXECUTE
// ═══════════════════════════════════════════════════════════════

if (! is_dir($sourceDir)) {
    echo "Source directory not found: {$sourceDir}\n";
    exit(1);
}

if (! $dryRun && ! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "Created output directory: {$outputDir}\n";
}

$files = glob($sourceDir.'/*.gif');
$mapping = []; // old_name => new_name
$duplicates = [];
$total = 0;

foreach ($files as $filePath) {
    $originalName = basename($filePath);
    $englishParsed = parseFilename($originalName);
    $spanishName = translateToSpanish($englishParsed, $compounds, $words);
    $newFilename = toCleanFilename($spanishName);

    // Handle duplicates by appending a number
    if (isset($duplicates[$newFilename])) {
        $duplicates[$newFilename]++;
        $base = pathinfo($newFilename, PATHINFO_FILENAME);
        $newFilename = $base.'-'.$duplicates[$newFilename].'.gif';
    } else {
        $duplicates[$newFilename] = 1;
    }

    $mapping[$originalName] = $newFilename;
    $total++;

    if ($dryRun) {
        echo sprintf("%-70s => %s\n", $originalName, $newFilename);
    } else {
        copy($filePath, $outputDir.'/'.$newFilename);
    }
}

echo "\n";
echo "Total: {$total} files\n";

// Save mapping JSON for DB update later
$mappingFile = ($dryRun ? sys_get_temp_dir() : $outputDir).'/gif-mapping.json';
file_put_contents($mappingFile, json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Mapping saved to: {$mappingFile}\n";

if ($dryRun) {
    echo "\nDRY RUN — no files were copied. Run without --dry-run to execute.\n";
} else {
    echo "\nFiles copied to: {$outputDir}\n";
}
