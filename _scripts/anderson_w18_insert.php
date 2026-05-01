<?php

declare(strict_types=1);

/**
 * INSERT del drop W18-2026 de Anderson Ardila #5.
 *
 * Para correr en producción (EasyPanel container path /code):
 *
 *   php _scripts/anderson_w18_insert.php --dry-run    # simula sin escribir
 *   php _scripts/anderson_w18_insert.php              # escribe el drop
 *
 * Lo que hace:
 *   1. Lee anderson_w18_drop.json (debe estar en la misma carpeta)
 *   2. Verifica que Anderson #5 existe en admins y es role=coach
 *   3. Verifica que tiene CoachMarketingProfile completo (completed_at NOT NULL)
 *   4. Valida el JSON contra DropSchemaValidator
 *   5. UPSERT en coach_content_drops con status=in_review
 *   6. Invalida cache coach_drop_v3:5:2026:18
 *   7. Reporta al admin via Database Notification (si la clase existe)
 *
 * NUNCA aprueba (status=ready) — eso lo hace Daniel desde /admin/marketing/drops/:id
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\Marketing\DropStatus;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Models\CoachMarketingProfile;
use App\Services\Marketing\DropSchemaValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

const COACH_ID  = 5;
const ISO_YEAR  = 2026;
const ISO_WEEK  = 18;

$dryRun = in_array('--dry-run', $argv ?? [], true);

echo "================================================================\n";
echo "  ANDERSON ARDILA #5 — DROP W18-2026 — INSERT\n";
echo "  Mode: " . ($dryRun ? 'DRY RUN (no writes)' : 'LIVE') . "\n";
echo "================================================================\n\n";

// ─── 1. Verify coach exists ──────────────────────────────────────
$coach = Admin::find(COACH_ID);
if (!$coach) {
    echo "FATAL: admins(id=5) no existe en esta DB.\n";
    exit(2);
}
if (($coach->role->value ?? null) !== 'coach') {
    echo "FATAL: admins(id=5) existe pero role no es 'coach' (es '{$coach->role->value}').\n";
    exit(2);
}
echo "OK · Coach encontrado: id=5 · " . ($coach->name ?? '(sin name)') . " · email=" . ($coach->email ?? '?') . "\n";

// ─── 2. Verify intake ────────────────────────────────────────────
$profile = CoachMarketingProfile::where('coach_id', COACH_ID)->first();
if (!$profile) {
    echo "FATAL: Anderson no tiene CoachMarketingProfile.\n";
    echo "       Crear primero el intake desde /admin/marketing/coaches/5/profile\n";
    exit(2);
}
if ($profile->completed_at === null) {
    echo "WARN · Profile existe pero completed_at IS NULL. ¿Continuar? Esto puede ser un drop con voz fallback.\n";
}
echo "OK · Profile: id={$profile->id} · brand=" . ($profile->brand_name ?? '?') . " · completed_at=" . ($profile->completed_at ?? 'NULL') . "\n\n";

// ─── 3. Load JSON content ────────────────────────────────────────
$jsonPath = __DIR__ . '/anderson_w18_drop.json';
if (!is_file($jsonPath)) {
    echo "FATAL: no existe {$jsonPath}\n";
    echo "       Correr antes _scripts/anderson_w18_build.php para generarlo.\n";
    exit(2);
}
$content = json_decode(file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
echo "OK · JSON cargado: " . number_format(filesize($jsonPath)) . " bytes · " . count($content['stories']) . " stories · " . count($content['reels']) . " reels\n";

// ─── 4. Validate against schema ──────────────────────────────────
echo "==> Validando schema coach_drop_v1...\n";
try {
    (new DropSchemaValidator())->validate($content);
    echo "    OK · Schema válido.\n\n";
} catch (\App\Exceptions\Marketing\InvalidDropSchema $e) {
    echo "FATAL · errores de schema:\n";
    foreach ($e->errors as $err) {
        echo "  - {$err['path']}: {$err['message']}\n";
    }
    exit(3);
}

// ─── 5. Compute week_starts_on (Monday ISO) ──────────────────────
$weekStartsOn = Carbon::now()->setISODate(ISO_YEAR, ISO_WEEK)->startOfWeek()->toDateString();
echo "OK · week_starts_on (ISO Monday) = {$weekStartsOn}\n";

// ─── 6. Check existing drop ──────────────────────────────────────
$existing = CoachContentDrop::where('coach_id', COACH_ID)
    ->where('iso_year', ISO_YEAR)
    ->where('iso_week', ISO_WEEK)
    ->first();

if ($existing) {
    echo "WARN · Drop ya existe: id={$existing->id} status={$existing->status->value} created_at={$existing->created_at}\n";
    echo "       UPSERT actualizará status=in_review y reemplazará content.\n";
} else {
    echo "OK · Sin drop previo en (5, 2026, 18). Insert fresco.\n";
}
echo "\n";

if ($dryRun) {
    echo "DRY RUN · No se escribió nada. Para correr live: php _scripts/anderson_w18_insert.php\n";
    exit(0);
}

// ─── 7. UPSERT ───────────────────────────────────────────────────
echo "==> Escribiendo drop...\n";
DB::transaction(function () use ($content, $profile, $weekStartsOn, &$drop) {
    $drop = CoachContentDrop::updateOrCreate(
        [
            'coach_id' => COACH_ID,
            'iso_year' => ISO_YEAR,
            'iso_week' => ISO_WEEK,
        ],
        [
            'week_starts_on'   => $weekStartsOn,
            'status'           => DropStatus::InReview,
            'content'          => $content,
            'original_content' => $content,
            'intake_snapshot'  => $profile->toArray(),
            'schema_version'   => 'coach_drop_v1',
            'generated_at'     => now(),
            'admin_edits_diff' => null,
        ]
    );
});

echo "    OK · Drop guardado: id={$drop->id} status=" . $drop->status->value . "\n\n";

// ─── 8. Cache invalidation ───────────────────────────────────────
$cacheKey = 'coach_drop_v3:' . COACH_ID . ':' . ISO_YEAR . ':' . ISO_WEEK;
Cache::forget($cacheKey);
echo "OK · Cache key '{$cacheKey}' invalidado.\n";

// ─── 9. Notify admin (skip si la clase no existe) ────────────────
$notifClass = 'App\\Notifications\\Marketing\\NewDropPendingReview';
if (class_exists($notifClass)) {
    try {
        $superAdmins = Admin::where('role', 'superadmin')->get();
        foreach ($superAdmins as $sa) {
            $sa->notify(new $notifClass($drop));
        }
        echo "OK · Notificación enviada a " . $superAdmins->count() . " superadmin(s).\n";
    } catch (\Throwable $t) {
        echo "WARN · Notificación falló (no bloquea): " . $t->getMessage() . "\n";
    }
} else {
    echo "WARN · {$notifClass} no existe (M11 pendiente). Skipped — INSERT no bloqueado.\n";
}

echo "\n================================================================\n";
echo "  COMPLETADO\n";
echo "  Drop ID: {$drop->id}\n";
echo "  URL admin: /admin/marketing/drops/{$drop->id}\n";
echo "  Status: in_review (esperando revisión de Daniel)\n";
echo "================================================================\n";
