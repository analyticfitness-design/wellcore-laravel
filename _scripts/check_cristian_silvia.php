<?php
/**
 * Verifica estado de los planes de Cristian Oquendo y Silvia Mesa.
 * Ejecutar: php _scripts/check_cristian_silvia.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\AssignedPlan;

$out = [];

// --- CRISTIAN ---
$cristian = Client::where('email', 'cristian_fov@hotmail.com')->first();
if (!$cristian) {
    $out['cristian'] = 'NOT FOUND';
} else {
    $plan = AssignedPlan::where('client_id', $cristian->id)
        ->where('plan_type', 'entrenamiento')
        ->where('active', 1)
        ->latest('id')
        ->first();
    if (!$plan) {
        $out['cristian'] = ['client_id' => $cristian->id, 'plan' => 'NO ACTIVE TRAINING PLAN'];
    } else {
        $stats = ['20' => 0, '30' => 0, 'otro' => 0];
        $totalDias = 0;
        foreach (($plan->content['semanas'] ?? []) as $w) {
            foreach (($w['dias'] ?? []) as $d) {
                $totalDias++;
                $v = $d['cardio']['duracion_min'] ?? null;
                if ($v == 20) $stats['20']++;
                elseif ($v == 30) $stats['30']++;
                else $stats['otro']++;
            }
        }
        $out['cristian'] = [
            'client_id' => $cristian->id,
            'plan_id' => $plan->id,
            'updated_at' => (string) $plan->updated_at,
            'total_dias' => $totalDias,
            'cardio_duracion' => $stats,
            'notas_coach_has_20min' => str_contains($plan->content['notas_coach'] ?? '', '20 minutos en caminadora'),
            'notas_coach_has_30min' => str_contains($plan->content['notas_coach'] ?? '', '30 minutos en caminadora'),
        ];
    }
}

// --- SILVIA ---
$silvia = Client::where('email', 'silviaj2598@gmail.com')->first();
if (!$silvia) {
    $out['silvia'] = 'NOT FOUND';
} else {
    $plan = AssignedPlan::where('client_id', $silvia->id)
        ->where('plan_type', 'entrenamiento')
        ->where('active', 1)
        ->latest('id')
        ->first();
    if (!$plan) {
        $out['silvia'] = ['client_id' => $silvia->id, 'plan' => 'NO ACTIVE TRAINING PLAN'];
    } else {
        $weeks = [];
        foreach (($plan->content['semanas'] ?? []) as $i => $w) {
            $dias = [];
            foreach (($w['dias'] ?? []) as $d) {
                $dias[] = ($d['dia_semana'] ?? '?') . ' (' . ($d['nombre'] ?? '?') . ')';
            }
            $weeks[] = ['week' => $i + 1, 'count' => count($w['dias'] ?? []), 'dias' => $dias];
        }
        $out['silvia'] = [
            'client_id' => $silvia->id,
            'plan_id' => $plan->id,
            'updated_at' => (string) $plan->updated_at,
            'frecuencia_dias' => $plan->content['frecuencia_dias'] ?? null,
            'top_keys' => array_keys($plan->content),
            'sample_dia_keys' => isset($plan->content['semanas'][0]['dias'][0])
                ? array_keys($plan->content['semanas'][0]['dias'][0]) : null,
            'weeks' => $weeks,
            'has_sabado' => collect($plan->content['semanas'][0]['dias'] ?? [])
                ->pluck('dia_semana')
                ->map(fn ($x) => mb_strtolower($x))
                ->contains('sabado') || collect($plan->content['semanas'][0]['dias'] ?? [])
                ->pluck('dia_semana')
                ->map(fn ($x) => mb_strtolower($x))
                ->contains('sábado'),
        ];
    }
}

echo "=== DIAGNOSTICO ===\n";
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
