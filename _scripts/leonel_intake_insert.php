<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/**
 * Jose Leonel Vanegas — coach_id=10
 * INSERT de CoachMarketingProfile (intake manual por admin)
 *
 * Ejecutar en EasyPanel consola:
 *   cd /code && php artisan tinker < /tmp/leonel_intake_insert.php
 *
 * CORRER ESTE SCRIPT PRIMERO, luego leonel_drop_w18_insert.php
 */

use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\LastUpdatedBy;
use App\Enums\Marketing\SpecialtyPrimary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$coachId = 10;

// 1. Verificar que el admin existe y es coach
$coach = Admin::find($coachId);
if (! $coach) {
    throw new \RuntimeException("Admin id={$coachId} no existe en producción.");
}
echo "Coach encontrado: {$coach->name} | role={$coach->role->value}\n";

// 2. Intake data — llenado manualmente por equipo WellCore
$intakeData = [
    'coach_id'             => $coachId,
    'brand_name'           => 'Leonel Vanegas',
    'specialty_primary'    => 'hipertrofia',
    'specialty_secondary'  => 'recomposicion',
    'differentiator'       => 'Gestiono mis asesorías con WellCore como sistema central: rutina personalizada, registro de pesos, seguimiento de PRs, plan nutricional y comunicación directa. Cada ajuste respaldado por datos reales.',
    'audience_age_range'   => '25-35',
    'audience_gender'      => 'mixto',
    'audience_pain_main'   => 'Quieren mejorar su composición corporal y llevan meses entrenando sin ver progreso real: sin registro de pesos, sin plan nutricional, sin seguimiento semanal.',
    'audience_offer_main'  => 'metodo',
    'preferred_methodologies' => [
        'sobrecarga progresiva',
        'periodizacion lineal',
        'control de volumen semanal',
        'registro de pesos por sesion',
        'ajuste quincenal de plan',
    ],
    'content_topics' => [
        'progresion de fuerza y pesos',
        'registro de PRs y seguimiento',
        'plataforma WellCore en accion',
        'metodologia de entrenamiento online',
        'nutricion basica para cambio de composicion',
        'rutinas estructuradas y progresivas',
    ],
    'voice_adjectives' => ['meticuloso', 'consistente', 'cercano'],
    'active_offers'    => [
        [
            'name'     => 'Metodo Leonel',
            'price'    => 150000,
            'currency' => 'COP',
            'promo'    => 'Primer mes con revision quincenal directa y acceso completo a la plataforma WellCore Fitness',
        ],
    ],
    'voice_samples'     => null,
    'top_working_posts' => null,
    'last_updated_by'   => 'admin',
    'completed_at'      => Carbon::now(),
];

// 3. Upsert del perfil
DB::transaction(function () use ($coachId, $intakeData, $coach) {
    $profile = CoachMarketingProfile::updateOrCreate(
        ['coach_id' => $coachId],
        $intakeData
    );

    echo "✅ CoachMarketingProfile UPSERTed id={$profile->id}\n";
    echo "   brand_name:   {$profile->brand_name}\n";
    echo "   completed_at: {$profile->completed_at}\n";
    echo "   Coach: {$coach->name} (id={$coachId})\n";
});

echo "\n=== INTAKE LISTO ===\n";
echo "Ahora correr: leonel_drop_w18_insert.php\n";
