<?php
$training = json_decode(file_get_contents(__DIR__ . '/william_v2_training.json'), true);
$nutrition = json_decode(file_get_contents(__DIR__ . '/william_v2_nutrition_2400.json'), true);
$habits = json_decode(file_get_contents(__DIR__ . '/william_v2_habits.json'), true);

if (!$training) die("ERROR: training JSON invalid\n");
if (!$nutrition) die("ERROR: nutrition JSON invalid\n");
if (!$habits) die("ERROR: habits JSON invalid\n");

$program = [
    'plan_entrenamiento' => $training,
    'plan_nutricion' => $nutrition,
    'plan_habitos' => $habits,
];

$json = json_encode($program, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/william_v2_complete.json', $json);

// Backup
$backupDir = 'C:/Users/GODSF/Music/ROLES Y DATOS DE LA PLATAFORMA WELLCORE/PLANES CON IA POR MEDIO DE CLAUDE Y PROTOCOLO PARA PODER SINCRONIZARLOS EN LA APP DEPENDIENDO DEL CLIENTE/Carpeta de backups de planes/william-torres';
if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
file_put_contents($backupDir . '/2026-03-29-rise-v2.json', $json);

echo "ASSEMBLED\n";
echo "Size: " . strlen($json) . " bytes\n";
echo "Semanas: " . count($training['semanas']) . "\n";
echo "Comidas: " . count($nutrition['comidas_sugeridas']) . "\n";
echo "Habitos: " . count($habits) . "\n";
echo "Calorias: " . $nutrition['calorias_diarias'] . " kcal\n";
