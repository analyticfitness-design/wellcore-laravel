<?php

declare(strict_types=1);

/**
 * Seed mínimo del CoachMarketingProfile para Anderson #5.
 *
 * Valores conservadores derivados del contexto del drop W18-2026
 * (ciclo menstrual + recomposición + fuerza). Daniel debe revisar
 * este intake en /admin/marketing/coaches/5/profile antes de aprobar
 * cualquier drop futuro — son defaults seguros, no la voz definitiva
 * del coach.
 *
 * Uso:
 *   php _scripts/anderson_intake_seed.php --dry-run
 *   php _scripts/anderson_intake_seed.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use App\Models\CoachMarketingProfile;

const COACH_ID = 5;

$dryRun = in_array('--dry-run', $argv ?? [], true);

echo "================================================================\n";
echo "  ANDERSON #5 — INTAKE SEED\n";
echo "  Mode: " . ($dryRun ? 'DRY RUN (no writes)' : 'LIVE') . "\n";
echo "================================================================\n\n";

$coach = Admin::find(COACH_ID);
if (!$coach || ($coach->role->value ?? null) !== 'coach') {
    echo "FATAL: Anderson #5 no existe o no es coach.\n";
    exit(2);
}
echo "OK · Coach " . ($coach->name ?? '?') . " · email=" . ($coach->email ?? '?') . "\n";

$existing = CoachMarketingProfile::where('coach_id', COACH_ID)->first();
if ($existing) {
    echo "WARN · Profile ya existe (id={$existing->id}, completed_at=" . ($existing->completed_at ?? 'NULL') . ").\n";
    echo "       Saliendo sin tocar — NO sobrescribir si ya hay datos.\n";
    exit(0);
}
echo "OK · Sin profile previo. Crearemos uno mínimo.\n\n";

$payload = [
    'coach_id'           => COACH_ID,
    'brand_name'         => 'Anderson Ardila',
    'specialty_primary'  => 'recomposicion',
    'specialty_secondary'=> 'fuerza',
    'differentiator'     => 'Entrenamiento de fuerza y recomposición corporal para mujeres, basado en evidencia científica reciente. Adaptación inteligente al ciclo menstrual sin parar nunca el plan.',
    'audience_age_range' => '25-35',
    'audience_gender'    => 'mujeres',
    'audience_pain_main' => 'Mujeres que entrenan con disciplina pero no entienden por qué hay semanas que progresan y otras que sienten que retroceden, sin saber que el ciclo importa.',
    'audience_offer_main'=> 'metodo',
    'preferred_methodologies' => [
        'Entrenamiento de fuerza basado en evidencia',
        'Periodización adaptada al ciclo menstrual',
        'Recomposición corporal con déficit/superávit pequeño',
        'Sobrecarga progresiva en patrones compuestos',
    ],
    'content_topics' => [
        'Ciclo menstrual y entrenamiento de fuerza',
        'Recomposición corporal en mujeres',
        'Fuerza femenina y mitos del gym',
        'Nutrición por fase del ciclo',
        'Hipertrofia y volumen progresivo',
    ],
    'voice_adjectives' => [
        'directo',
        'tecnico',
        'empatico',
    ],
    'active_offers' => [
        [
            'name'     => 'Plan Método',
            'price'    => 120,
            'currency' => 'USD',
            'promo'    => null,
        ],
    ],
    'voice_samples'    => [],
    'top_working_posts'=> [],
    'completed_at'     => now(),
];

echo "==> Payload:\n";
foreach ($payload as $k => $v) {
    if (is_array($v)) {
        echo "    {$k}: " . json_encode($v, JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "    {$k}: " . (string)$v . "\n";
    }
}
echo "\n";

if ($dryRun) {
    echo "DRY RUN · No se escribió nada.\n";
    exit(0);
}

$profile = CoachMarketingProfile::create($payload);
echo "OK · Profile creado: id={$profile->id} completed_at={$profile->completed_at}\n";
echo "\n⚠️  Daniel: revisa este intake en /admin/marketing/coaches/5/profile antes\n";
echo "    de aprobar cualquier drop futuro. Son defaults derivados del W18, no\n";
echo "    la voz definitiva de Anderson.\n";
