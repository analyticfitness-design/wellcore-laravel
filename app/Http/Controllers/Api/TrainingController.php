<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\AssignedPlan;
use App\Models\BloodworkResult;
use App\Models\Checkin;
use App\Models\ClientXp;
use App\Models\HabitLog;
use App\Models\TrainingLog;
use App\Models\WellcoreNotification;
use App\Models\WorkoutLog;
use App\Models\WorkoutPr;
use App\Models\WorkoutSession;
use App\Services\ClientCacheService;
use App\Services\ExerciseMediaService;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TrainingController extends Controller
{
    use AuthenticatesVueRequests;

    public function __construct(private ExerciseMediaService $media) {}

    // ─── Plan Viewer ───────────────────────────────────────────────────

    /**
     * GET /api/v/client/plan
     *
     * Full plan viewer: training, nutrition, supplements, habits, bloodwork.
     * Ports PlanViewer.php mount() logic.
     */
    public function plan(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $plans = AssignedPlan::where('client_id', $clientId)
            ->where('active', true)
            ->get();

        $trainingPlan = null;
        $nutritionPlan = null;
        $supplementPlan = null;
        $cicloPlan = null;

        foreach ($plans as $plan) {
            $content = is_array($plan->content)
                ? $plan->content
                : json_decode($plan->content, true);

            $planType = strtolower(trim((string) $plan->plan_type));

            match ($planType) {
                'entrenamiento' => $trainingPlan = $this->normalizeTrainingPlan($content),
                'nutricion' => $nutritionPlan = $this->normalizeNutritionPlan($content),
                'suplementacion' => $supplementPlan = $content,
                'ciclo_hormonal', 'ciclo' => $cicloPlan = $this->normalizeCicloPlan($content),
                default => null,
            };
        }

        // Enrich training plan exercises with GIF URLs
        if ($trainingPlan && isset($trainingPlan['semanas'])) {
            foreach ($trainingPlan['semanas'] as $sIdx => $semana) {
                foreach ($semana['dias'] ?? [] as $dIdx => $dia) {
                    if (! empty($dia['ejercicios'])) {
                        $this->media->enrichWithMedia($trainingPlan['semanas'][$sIdx]['dias'][$dIdx]['ejercicios']);
                    }
                }
            }
        } elseif ($trainingPlan && isset($trainingPlan['dias'])) {
            foreach ($trainingPlan['dias'] as $dIdx => $dia) {
                if (! empty($dia['ejercicios'])) {
                    $this->media->enrichWithMedia($trainingPlan['dias'][$dIdx]['ejercicios']);
                }
            }
        }

        // Week progression
        $currentWeek = 1;
        $totalWeeks = 1;
        $progressPct = 0;
        $planStartDate = null;

        if ($trainingPlan) {
            $totalWeeks = (int) ($trainingPlan['duracion_semanas'] ?? count($trainingPlan['semanas'] ?? []) ?: 1);
            $startDate = $trainingPlan['fecha_inicio'] ?? $client->fecha_inicio ?? null;

            if ($startDate) {
                $start = Carbon::parse($startDate);
                $planStartDate = $start->format('d M Y');
                $daysElapsed = max(0, $start->diffInDays(now()));
                $currentWeek = min($totalWeeks, (int) ceil(max(1, $daysElapsed) / 7));
                $totalDays = $totalWeeks * 7;
                $progressPct = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100, 1)) : 0;
            }
        }

        // Plan Viewer V2: enrich aditivamente con campos derivados sin tocar el JSON original
        // del plan. NUNCA reescribe gif_url, sort_order, coach_note, series, reps, rest, RIR.
        // Se hace DESPUÉS del cálculo de currentWeek para marcar es_actual y es_hoy correctamente.
        if ($trainingPlan) {
            $trainingPlan = $this->enrichTrainingPlanV2($trainingPlan, $clientId, $currentWeek);
        }

        // Habits (last 30 days)
        $habitData = $this->buildHabitData($clientId);

        // Bloodwork
        $bloodwork = BloodworkResult::where('client_id', $clientId)
            ->orderByDesc('test_date')
            ->get()
            ->toArray();

        $planType = strtolower($client->plan instanceof PlanType ? $client->plan->value : (string) ($client->plan ?? 'esencial'));

        return response()->json([
            'training_plan' => $trainingPlan,
            'nutrition_plan' => $nutritionPlan,
            'supplement_plan' => $supplementPlan,
            'ciclo_plan' => $cicloPlan,
            'plan_type' => $planType,
            'current_week' => $currentWeek,
            'total_weeks' => $totalWeeks,
            'progress_pct' => $progressPct,
            'plan_start_date' => $planStartDate,
            'habit_data' => $habitData['habits'],
            'habit_compliance' => $habitData['compliance'],
            'bloodwork' => $bloodwork,
        ]);
    }

    // ─── Plan Viewer V2 — enrich + toggle variation ───────────────────

    /**
     * POST /api/v/client/plan/exercise/{id}/toggle-variation
     *
     * Persist el "estoy usando la variación" flag en una tabla auxiliar
     * sin modificar el JSON content del plan ni el gif_url original.
     * Idempotente. Rate-limited en routes/api.php.
     * IDOR-safe: valida que el ejercicio pertenezca a un plan activo del cliente.
     */
    public function toggleVariation(Request $request, int $id): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'use_variant' => 'required|boolean',
        ]);

        // IDOR check: el ejercicio debe estar dentro del JSON content del plan
        // de entrenamiento ACTIVO del cliente autenticado.
        $plan = AssignedPlan::where('client_id', $client->id)
            ->where('plan_type', 'entrenamiento')
            ->where('active', true)
            ->first();

        if (! $plan) {
            abort(404, 'No tienes un plan de entrenamiento activo.');
        }

        $content = is_array($plan->content)
            ? $plan->content
            : json_decode((string) $plan->content, true);

        $belongs = false;
        $hasVariant = false;
        // Defensive: usar is_array() en vez de ?? [] porque el JSON content
        // puede traer keys con valor null explícito (no missing).
        $semanas = is_array($content['semanas'] ?? null) ? $content['semanas'] : [];
        foreach ($semanas as $sem) {
            $dias = is_array($sem['dias'] ?? null) ? $sem['dias'] : [];
            foreach ($dias as $dia) {
                $ejs = is_array($dia['ejercicios'] ?? null) ? $dia['ejercicios'] : [];
                foreach ($ejs as $ej) {
                    if (is_array($ej) && (int) ($ej['id'] ?? 0) === $id) {
                        $belongs = true;
                        $hasVariant = ! empty($ej['variacion']);
                        break 3;
                    }
                }
            }
        }
        if (! $belongs) {
            // Plan plano (sin macrociclo): mismo check sobre dias[].ejercicios[]
            $diasFlat = is_array($content['dias'] ?? null) ? $content['dias'] : [];
            foreach ($diasFlat as $dia) {
                $ejs = is_array($dia['ejercicios'] ?? null) ? $dia['ejercicios'] : [];
                foreach ($ejs as $ej) {
                    if (is_array($ej) && (int) ($ej['id'] ?? 0) === $id) {
                        $belongs = true;
                        $hasVariant = ! empty($ej['variacion']);
                        break 2;
                    }
                }
            }
        }

        if (! $belongs) {
            abort(403);
        }

        // Defensive: si el ejercicio NO tiene variación definida en el plan, rechazar
        // el toggle on-state (no se puede "usar variación" si no hay variación).
        if ($validated['use_variant'] && ! $hasVariant) {
            return response()->json([
                'message' => 'Este ejercicio no tiene variación disponible.',
            ], 422);
        }

        DB::table('plan_exercise_variations')->updateOrInsert(
            ['client_id' => $client->id, 'exercise_id' => $id],
            [
                'using_variant' => (bool) $validated['use_variant'],
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'using_variant' => (bool) $validated['use_variant'],
            'exercise_id' => $id,
        ]);
    }

    /**
     * Aditivo y defensivo: agrega campos V2 al training_plan SIN modificar
     * los campos existentes (gif_url, series, reps, rest, RIR, coach_note,
     * sort_order/numero, etc. quedan IDÉNTICOS al input).
     */
    /**
     * Cast defensivo a string: si el valor es array (legacy JSONs estructurados como
     * { "es": "texto", "en": "..." } o objetos anidados), retorna ''. Evita el
     * fatal "Array to string conversion" sin tocar el JSON original.
     */
    private function safeStr(mixed $v, string $default = ''): string
    {
        if (is_string($v)) {
            return $v;
        }
        if (is_int($v) || is_float($v)) {
            return (string) $v;
        }
        if (is_bool($v)) {
            return $v ? '1' : '';
        }
        // arrays / objects / null -> default
        return $default;
    }

    private function enrichTrainingPlanV2(array $trainingPlan, int $clientId, int $currentWeekNumber = 1): array
    {
        // Variation states cargados de una sola vez (evita N+1)
        $variantStates = [];
        if (Schema::hasTable('plan_exercise_variations')) {
            $variantStates = DB::table('plan_exercise_variations')
                ->where('client_id', $clientId)
                ->pluck('using_variant', 'exercise_id')
                ->toArray();
        }

        // 1) objetivo_bloque: tomar tal cual del JSON si existe (safe cast)
        $trainingPlan['objetivo_bloque'] = $this->safeStr(
            $trainingPlan['objetivo_bloque']
            ?? $trainingPlan['objetivo']
            ?? $trainingPlan['plan_objetivo']
            ?? ''
        );

        // is_expired: defensivo (default false). El check real depende de tabla
        // que puede no estar populated en producción. Frontend usa esto + isLocked
        // (de plan-status) — si falta, no rompe.
        $trainingPlan['is_expired'] = (bool) ($trainingPlan['is_expired'] ?? false);

        // 2) weekly_schedule: derivado server-side desde semana actual
        $trainingPlan['weekly_schedule'] = $this->deriveWeeklySchedule($trainingPlan);

        // 3) Metadata top-level derivada (volumen total / RIR / freq) para hero V2
        $trainingPlan = $this->enrichPlanMetadataV2($trainingPlan);

        // 4) Walk semanas/dias/ejercicios para enriquecer cada nodo
        // dayOfWeekNow: 1=Lunes ... 7=Domingo (consistente con backend Carbon)
        $dayOfWeekNow = (int) Carbon::now()->isoWeekday();

        if (isset($trainingPlan['semanas']) && is_array($trainingPlan['semanas'])) {
            foreach ($trainingPlan['semanas'] as $sIdx => $semana) {
                $isCurrent = ($sIdx + 1) === $currentWeekNumber;

                // Enriquecer semana
                $trainingPlan['semanas'][$sIdx] = $this->enrichSemanaV2(
                    $semana,
                    $sIdx,
                    $isCurrent,
                    $sIdx + 1 < $currentWeekNumber
                );

                foreach (($semana['dias'] ?? []) as $dIdx => $dia) {
                    // Enriquecer día (incluye numero, titulo, grupos, es_hoy, subline)
                    $trainingPlan['semanas'][$sIdx]['dias'][$dIdx] = $this->enrichDiaV2(
                        $dia,
                        $dIdx,
                        $isCurrent,
                        $dayOfWeekNow
                    );

                    foreach (($dia['ejercicios'] ?? []) as $eIdx => $ej) {
                        $trainingPlan['semanas'][$sIdx]['dias'][$dIdx]['ejercicios'][$eIdx]
                            = $this->enrichExerciseV2($ej, $variantStates);
                    }
                }
            }
        } elseif (isset($trainingPlan['dias']) && is_array($trainingPlan['dias'])) {
            foreach ($trainingPlan['dias'] as $dIdx => $dia) {
                $trainingPlan['dias'][$dIdx] = $this->enrichDiaV2($dia, $dIdx, true, $dayOfWeekNow);

                foreach (($dia['ejercicios'] ?? []) as $eIdx => $ej) {
                    $trainingPlan['dias'][$dIdx]['ejercicios'][$eIdx]
                        = $this->enrichExerciseV2($ej, $variantStates);
                }
            }
        }

        return $trainingPlan;
    }

    /**
     * Enriquece la semana con campos V2 derivados (numero, titulo, fase, es_actual, completada,
     * total_minutos, total_series). NO modifica los días — eso lo hace enrichDiaV2.
     */
    private function enrichSemanaV2(array $semana, int $sIdx, bool $isCurrent, bool $isCompleted): array
    {
        // numero (1-based) — defensive: solo accept scalar
        $rawNumero = $semana['numero'] ?? $semana['n'] ?? $sIdx + 1;
        $semana['numero'] = is_numeric($rawNumero) ? (int) $rawNumero : ($sIdx + 1);

        // titulo (preferir label de fase sobre genérico "Semana N" — fidelidad V2.1)
        // Si no hay fase explícita en el JSON, asumir 'acumul' (default razonable
        // para el primer bloque de un plan — coincide con HTML V2.1).
        $faseRaw = strtolower($this->safeStr($semana['fase'] ?? $semana['phase'] ?? ''));
        if ($faseRaw === '') {
            $faseRaw = 'acumul';
        }
        $titleFromPhase = match ($faseRaw) {
            'acumulacion', 'acumulación', 'acumul' => 'Acumulación',
            'intensificacion', 'intensificación', 'intens' => 'Intensificación',
            'pico', 'peak' => 'Pico',
            'deload', 'descarga' => 'Descarga',
            default => '',
        };
        $tituloFromJson = $this->safeStr($semana['titulo'] ?? $semana['nombre'] ?? '');
        // Si el JSON solo trae "Semana N" (nombre genérico) Y hay fase válida,
        // preferir el label de la fase (HTML V2.1 muestra "Acumulación" no "Semana 1").
        $isGeneric = preg_match('/^semana\s*\d+$/iu', $tituloFromJson) === 1;
        if ($tituloFromJson !== '' && ! $isGeneric) {
            $semana['titulo'] = $tituloFromJson;
        } elseif ($titleFromPhase !== '') {
            $semana['titulo'] = $titleFromPhase;
        } else {
            $semana['titulo'] = 'Semana ' . $semana['numero'];
        }

        // fase normalizada
        $semana['fase'] = $faseRaw !== '' ? $faseRaw : 'acumul';

        // es_actual / completada
        $semana['es_actual'] = $isCurrent;
        $semana['completada'] = $isCompleted;

        // total_minutos / total_series derivados desde dias[]
        $totalMin = 0;
        $totalSeries = 0;
        $totalEj = 0;
        foreach (($semana['dias'] ?? []) as $dia) {
            $totalEj += count($dia['ejercicios'] ?? []);
            $totalMin += (int) ($dia['total_minutos'] ?? $dia['minutos_estimados'] ?? 0);
            foreach (($dia['ejercicios'] ?? []) as $ej) {
                $totalSeries += (int) ($ej['series'] ?? 0);
            }
        }
        $semana['total_minutos'] = $totalMin > 0 ? $totalMin : null;
        $semana['total_series'] = $totalSeries > 0 ? $totalSeries : null;

        return $semana;
    }

    /**
     * Enriquece un día con campos V2 derivados (numero, titulo, grupos, es_hoy, subline).
     * Defensive: respeta campos existentes sin sobreescribir.
     */
    private function enrichDiaV2(array $dia, int $dIdx, bool $semanaIsCurrent, int $dayOfWeekNow): array
    {
        // numero (1-based) — defensive
        $rawNumero = $dia['numero'] ?? $dia['dia'] ?? $dIdx + 1;
        $dia['numero'] = is_numeric($rawNumero) ? (int) $rawNumero : ($dIdx + 1);

        // titulo (preferir JSON, fallback a nombre) — safe cast
        $dia['titulo'] = $this->safeStr($dia['titulo'] ?? $dia['nombre'] ?? '');

        // grupos: si no vienen, parsear desde título (separador · o + o ,)
        if (empty($dia['grupos']) && $dia['titulo'] !== '') {
            $parts = preg_split('/[·\+,]/u', $dia['titulo']);
            $grupos = [];
            foreach ((array) $parts as $p) {
                $clean = trim($this->safeStr($p));
                if ($clean === '') {
                    continue;
                }
                $first = explode(' ', $clean)[0] ?? '';
                $first = trim($first);
                if ($first !== '' && ! in_array(mb_strtolower($first), ['—', '-', 'al', 'el', 'la', 'de', 'del'], true)) {
                    $grupos[] = $first;
                }
            }
            $dia['grupos'] = array_slice(array_values(array_unique($grupos)), 0, 3);
        }
        if (! is_array($dia['grupos'] ?? null)) {
            $dia['grupos'] = [];
        }

        // total_minutos (defensivo)
        if (! isset($dia['total_minutos']) || ! is_numeric($dia['total_minutos'])) {
            $estimate = count($dia['ejercicios'] ?? []) * 6;
            $dia['total_minutos'] = $estimate > 0 ? $estimate : null;
        }

        // rir_promedio (defensivo) — solo si los ej traen rir como string|number
        if (empty($dia['rir_promedio']) && ! empty($dia['ejercicios'])) {
            $rirs = [];
            foreach ($dia['ejercicios'] as $ej) {
                $r = trim($this->safeStr($ej['rir'] ?? $ej['rir_semana'] ?? ''));
                if ($r !== '') {
                    $rirs[] = $r;
                }
            }
            if (! empty($rirs)) {
                $counts = array_count_values($rirs);
                arsort($counts);
                $dia['rir_promedio'] = (string) array_key_first($counts);
            }
        }

        // es_hoy
        $dia['es_hoy'] = $semanaIsCurrent && ((int) $dia['numero'] === $dayOfWeekNow);

        // cooldown canonicalizado — safe cast
        $cooldownStr = $this->safeStr($dia['cooldown'] ?? $dia['vuelta_calma'] ?? $dia['vuelta_a_la_calma'] ?? '');
        $dia['cooldown'] = $cooldownStr !== '' ? $cooldownStr : null;

        // completado: respeta JSON
        $dia['completado'] = (bool) ($dia['completado'] ?? false);

        return $dia;
    }

    /**
     * Deriva metadata top-level del plan (volumen total semanal, RIR objetivo, freq, etc.)
     * desde las semanas — solo si no viene en el JSON. Sin modificar campos existentes.
     */
    private function enrichPlanMetadataV2(array $tp): array
    {
        $semanas = $tp['semanas'] ?? [];
        if (empty($semanas) || ! is_array($semanas)) {
            return $tp;
        }

        // Tomar la primera semana NO deload como referencia para los stats top-level
        $refSemana = null;
        foreach ($semanas as $s) {
            $f = strtolower($this->safeStr($s['fase'] ?? ''));
            if (! in_array($f, ['deload', 'descarga'], true)) {
                $refSemana = $s;
                break;
            }
        }
        if (! $refSemana) {
            $refSemana = $semanas[0];
        }

        // Total series semanales
        if (empty($tp['total_series_semana'])) {
            $total = 0;
            foreach (($refSemana['dias'] ?? []) as $dia) {
                foreach (($dia['ejercicios'] ?? []) as $ej) {
                    $total += is_numeric($ej['series'] ?? null) ? (int) $ej['series'] : 0;
                }
            }
            if ($total > 0) {
                $tp['total_series_semana'] = $total;
            }
        }

        // Días de entrenamiento por semana
        if (empty($tp['dias_semana'])) {
            $tp['dias_semana'] = count($refSemana['dias'] ?? []) ?: null;
        }

        // RIR objetivo (más común en la semana de referencia)
        if (empty($tp['rir_objetivo'])) {
            $rirs = [];
            foreach (($refSemana['dias'] ?? []) as $dia) {
                foreach (($dia['ejercicios'] ?? []) as $ej) {
                    $r = trim($this->safeStr($ej['rir'] ?? $ej['rir_semana'] ?? ''));
                    if ($r !== '') {
                        $rirs[] = $r;
                    }
                }
            }
            if (! empty($rirs)) {
                $counts = array_count_values($rirs);
                arsort($counts);
                $tp['rir_objetivo'] = (string) array_key_first($counts);
            }
        }

        // Volumen label (alto / medio / bajo derivado de total_series)
        if (empty($tp['volumen_label']) && ! empty($tp['total_series_semana'])) {
            $tp['volumen_label'] = match (true) {
                $tp['total_series_semana'] >= 60 => 'Vol. alto',
                $tp['total_series_semana'] >= 35 => 'Vol. medio',
                default => 'Vol. bajo',
            };
        }

        return $tp;
    }

    /**
     * Enrich un ejercicio individual con los campos V2.
     * No toca campos existentes — solo agrega los nuevos.
     */
    private function enrichExerciseV2(array $ej, array $variantStates): array
    {
        // tipo (fuerza | cardio) heurística defensiva
        $ej['tipo'] = $this->detectExerciseType($ej);

        // block_id / es_superset / es_circuito (defaults seguros)
        $blockId = $this->safeStr($ej['block_id'] ?? $ej['bloque_id'] ?? '');
        $ej['block_id'] = $blockId !== '' ? $blockId : null;
        $ej['es_superset'] = (bool) ($ej['es_superset'] ?? $ej['superset'] ?? false);
        $ej['es_circuito'] = (bool) ($ej['es_circuito'] ?? $ej['circuito'] ?? false);

        // variacion (objeto opcional con shape {nombre, gif_url, original_id})
        $ej['variacion'] = $this->normalizeVariacion($ej['variacion'] ?? null);

        // is_using_variant (server state)
        $exId = (int) ($ej['id'] ?? 0);
        $ej['is_using_variant'] = $exId > 0
            ? (bool) ($variantStates[$exId] ?? false)
            : false;

        // Aliases V2 — solo agregar; nunca sobreescribir los originales del JSON.
        // safeStr garantiza que arrays/objects no causen "Array to string conversion".
        if (! isset($ej['rest'])) {
            $ej['rest'] = $this->safeStr($ej['descanso'] ?? '');
        } else {
            $ej['rest'] = $this->safeStr($ej['rest']);
        }
        if (! isset($ej['rir'])) {
            $ej['rir'] = $this->safeStr($ej['rir_semana'] ?? '');
        } else {
            $ej['rir'] = $this->safeStr($ej['rir']);
        }
        if (! isset($ej['coach_note'])) {
            $ej['coach_note'] = $this->safeStr($ej['notas'] ?? $ej['nota_coach'] ?? '');
        } else {
            $ej['coach_note'] = $this->safeStr($ej['coach_note']);
        }
        // grupo derivado del primer músculo del campo `musculos_prim` o vacío
        if (! isset($ej['grupo'])) {
            $primer = '';
            $musc = $ej['musculos_prim'] ?? null;
            if (is_array($musc) && ! empty($musc)) {
                $primer = $this->safeStr($musc[0] ?? '');
            } elseif (is_string($musc)) {
                $parts = preg_split('/[,;·\+]/u', $musc);
                $primer = trim($this->safeStr($parts[0] ?? ''));
            }
            $ej['grupo'] = strtolower($primer);
        }

        // Cardio fields (solo populated si tipo === 'cardio')
        if ($ej['tipo'] === 'cardio') {
            $ej['cardio_min'] = $ej['cardio_min'] ?? $ej['minutos'] ?? null;
            $ej['cardio_velocidad'] = $ej['cardio_velocidad'] ?? $ej['velocidad'] ?? $ej['kmh'] ?? null;
            $ej['cardio_inclinacion'] = $ej['cardio_inclinacion'] ?? $ej['inclinacion'] ?? null;
        }

        return $ej;
    }

    /**
     * Heurística para clasificar ejercicio fuerza|cardio sin tocar el catálogo.
     */
    private function detectExerciseType(array $ej): string
    {
        $explicit = strtolower($this->safeStr($ej['tipo'] ?? ''));
        if ($explicit === 'cardio') {
            return 'cardio';
        }
        if ($explicit === 'fuerza' || $explicit === 'strength') {
            return 'fuerza';
        }

        $grupo = strtolower($this->safeStr($ej['grupo'] ?? ''));
        if ($grupo === 'cardio') {
            return 'cardio';
        }

        $name = strtolower($this->safeStr($ej['nombre'] ?? $ej['name'] ?? ''));
        $cardioKeywords = ['cinta', 'caminadora', 'bicicleta', 'eliptica', 'elíptica', 'cardio', 'rower', 'remo cardio', 'salto', 'jumping'];
        foreach ($cardioKeywords as $kw) {
            if (str_contains($name, $kw)) {
                return 'cardio';
            }
        }

        // Si tiene cardio_min populated, tratarlo como cardio
        if (! empty($ej['cardio_min']) || ! empty($ej['minutos']) || ! empty($ej['kmh'])) {
            return 'cardio';
        }

        return 'fuerza';
    }

    /**
     * Normaliza la estructura de variacion (defensive contra shapes legacy).
     */
    private function normalizeVariacion(mixed $raw): ?array
    {
        if (! is_array($raw) || empty($raw)) {
            return null;
        }
        $nombre = $this->safeStr($raw['nombre'] ?? $raw['name'] ?? '');
        $gif = $this->safeStr($raw['gif_url'] ?? $raw['gif'] ?? '');
        $orig = $raw['original_id'] ?? $raw['exercise_id'] ?? null;
        if ($nombre === '' && $gif === '') {
            return null;
        }
        return [
            'nombre' => $nombre,
            'gif_url' => $gif,
            'original_id' => is_numeric($orig) ? (int) $orig : null,
        ];
    }

    /**
     * Derivar el weekly_schedule (split L-S) desde la semana ACTUAL o la primera.
     */
    private function deriveWeeklySchedule(array $trainingPlan): array
    {
        $semanas = $trainingPlan['semanas'] ?? [];
        if (! is_array($semanas) || empty($semanas)) {
            return [];
        }

        $sem = null;
        foreach ($semanas as $s) {
            if (! empty($s['es_actual'])) {
                $sem = $s;
                break;
            }
        }
        if (! $sem) {
            $sem = $semanas[0];
        }

        $letters = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
        $labels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        $out = [];
        $dias = (array) ($sem['dias'] ?? []);
        $count = min(count($dias), 7);
        for ($i = 0; $i < $count; $i++) {
            $d = $dias[$i] ?? null;
            if (! is_array($d)) {
                continue;
            }
            $titulo = $this->safeStr($d['titulo'] ?? $d['nombre'] ?? '');
            if ($titulo === '' && ! empty($d['grupos']) && is_array($d['grupos'])) {
                $titulo = implode(' · ', array_map(fn ($g) => ucfirst($this->safeStr($g)), $d['grupos']));
            }
            $out[] = [
                'day_letter' => $letters[$i] ?? '·',
                'day_label' => $labels[$i] ?? '',
                'muscle_groups' => $titulo,
            ];
        }
        return $out;
    }

    // ─── Training View (Weekly Calendar) ───────────────────────────────

    /**
     * GET /api/v/client/training
     *
     * Weekly training calendar with ISO week navigation.
     * Ports TrainingView.php render() logic.
     */
    public function training(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'year' => 'nullable|integer|between:2020,2035',
            'week' => 'nullable|integer|between:1,53',
        ]);

        $year = (int) $request->query('year', now()->isoFormat('GGGG'));
        $week = (int) $request->query('week', now()->isoFormat('W'));

        $logs = TrainingLog::where('client_id', $clientId)
            ->where('year_num', $year)
            ->where('week_num', $week)
            ->get()
            ->keyBy(fn ($log) => $log->log_date->format('Y-m-d'));

        $startOfWeek = Carbon::now()->setISODate($year, $week, 1);
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dateKey = $day->format('Y-m-d');
            $days[] = [
                'date' => $dateKey,
                'dayNumber' => $day->format('d'),
                'dayName' => $day->locale('es')->isoFormat('ddd'),
                'isToday' => $day->isToday(),
                'completed' => isset($logs[$dateKey]) && $logs[$dateKey]->completed,
            ];
        }

        $completedCount = collect($days)->where('completed', true)->count();

        $isCurrentWeek = $year === (int) now()->isoFormat('GGGG')
            && $week === (int) now()->isoFormat('W');

        $monthCacheKey = "training:month_sessions:{$clientId}:".now()->format('Y-m');
        $monthSessions = Cache::remember($monthCacheKey, 300, function () use ($clientId) {
            return TrainingLog::where('client_id', $clientId)
                ->where('completed', true)
                ->whereBetween('log_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count();
        });

        return response()->json([
            'year' => $year,
            'week' => $week,
            'days' => $days,
            'completed_count' => $completedCount,
            'month_sessions' => $monthSessions,
            'is_current_week' => $isCurrentWeek,
        ]);
    }

    /**
     * POST /api/v/client/training/toggle
     *
     * Toggle a training day's completion status.
     * Ports TrainingView.php toggleDay() logic.
     */
    public function toggleTrainingDay(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'date' => 'required|date|before_or_equal:today',
        ]);

        $date = $request->input('date');

        $log = TrainingLog::where('client_id', $clientId)
            ->where('log_date', $date)
            ->first();

        if ($log) {
            $log->update(['completed' => ! $log->completed]);
            $completed = $log->completed;
        } else {
            $parsed = Carbon::parse($date);
            TrainingLog::create([
                'client_id' => $clientId,
                'log_date' => $date,
                'completed' => true,
                'year_num' => (int) $parsed->isoFormat('GGGG'),
                'week_num' => (int) $parsed->isoFormat('W'),
            ]);
            $completed = true;
        }

        // Keep client_xp.streak_days in sync with training_logs so the dashboard
        // streak (driven by client_xp) and the calendar streak share one source.
        if ($completed) {
            $this->recalculateStreak($clientId);
        }

        Cache::forget("training:month_sessions:{$clientId}:".now()->format('Y-m'));
        ClientCacheService::invalidateDashboard($clientId);

        return response()->json([
            'date' => $date,
            'completed' => $completed,
        ]);
    }

    // ─── Workout Player ────────────────────────────────────────────────

    /**
     * GET /api/v/client/workout/{day?}
     *
     * Workout player data for a specific day. Ports WorkoutPlayer.php mount() logic
     * including plan normalization, week progression, block groups, and session auto-resume.
     */
    public function workout(Request $request, ?int $day = null): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        // Check if first-time user
        $showTutorial = ! WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->exists();

        // Load plan
        $planData = Cache::remember("wp:plan:{$clientId}", 300, function () use ($clientId) {
            $row = AssignedPlan::select(['id', 'content', 'valid_from', 'client_id', 'created_at'])
                ->where('client_id', $clientId)
                ->where('plan_type', 'entrenamiento')
                ->where('active', true)
                ->latest('id')
                ->first();

            return $row ? $row->toArray() : null;
        });

        if (! $planData) {
            return response()->json([
                'hasPlan' => false,
                'showTutorial' => $showTutorial,
            ]);
        }

        $planId = $planData['id'];
        $content = is_array($planData['content'])
            ? $planData['content']
            : json_decode($planData['content'], true);

        // Normalize top-level key variants
        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            $fallback = $content['days'] ?? $content['weeks'] ?? null;
            if (is_array($fallback)) {
                $content['dias'] = $fallback;
            } elseif (! is_array($content['dias'] ?? null)) {
                unset($content['dias']);
            }
        }

        if (isset($content['dias']) && is_array($content['dias'])) {
            $content['dias'] = array_values(array_map(
                fn ($d) => is_array($d) ? $this->normalizeDay($d) : $d,
                $content['dias']
            ));
        }

        // Handle plan[] format
        if (! isset($content['semanas']) && isset($content['plan']) && is_array($content['plan'])) {
            $first = reset($content['plan']);
            if (is_array($first) && (isset($first['days']) || isset($first['week']))) {
                $content['semanas'] = array_values(array_map(fn ($w) => [
                    'semana' => $w['week'] ?? 1,
                    'dias' => $w['days'] ?? [],
                ], $content['plan']));
                unset($content['plan']);
            }
        }

        $hasProgressions = false;
        $currentWeek = 1;
        $totalWeeks = 1;
        $allWeeksDays = [];
        $days = [];

        // Elite plans with weekly progressions
        if (isset($content['semanas']) && is_array($content['semanas'])) {
            $hasProgressions = true;
            $totalWeeks = count($content['semanas']);

            foreach ($content['semanas'] as $weekIndex => $weekData) {
                $weekNumber = $weekIndex + 1;
                $dias = $weekData['dias'] ?? $weekData['days'] ?? [];
                $allWeeksDays[$weekNumber] = array_values(array_map(
                    fn ($d) => is_array($d) ? $this->normalizeDay($d) : $d,
                    $dias
                ));
            }

            $weeksActive = max(1, (int) ceil(Carbon::parse($planData['valid_from'] ?? $planData['created_at'])->diffInWeeks(now())) + 1);
            $currentWeek = min($weeksActive, $totalWeeks);

            // Allow client to request a specific week
            $requestedWeek = (int) $request->query('week', $currentWeek);
            if ($requestedWeek >= 1 && $requestedWeek <= $totalWeeks) {
                $currentWeek = $requestedWeek;
            }

            $days = $allWeeksDays[$currentWeek] ?? [];
        } else {
            $days = is_array($content['dias'] ?? null) ? $content['dias'] : [];
        }

        if (empty($days)) {
            return response()->json([
                'hasPlan' => false,
                'showTutorial' => $showTutorial,
            ]);
        }

        // Select day (1-based)
        $currentDayIndex = 0;
        if ($day !== null && $day >= 1 && $day <= count($days)) {
            $currentDayIndex = $day - 1;
        }

        $currentDay = $days[$currentDayIndex] ?? null;
        $dayName = $this->resolveDayName($currentDay ?? [], $currentDayIndex);
        $muscleGroup = $currentDay['grupo_muscular'] ?? $currentDay['muscle_group'] ?? $currentDay['musculo'] ?? '';
        $exercises = $currentDay['ejercicios'] ?? $currentDay['exercises'] ?? $currentDay['ejercicios_dia'] ?? [];

        // Build block groups
        $blockGroups = $this->buildBlockGroups($exercises);

        // Check for active session to auto-resume
        $activeSession = null;
        $setData = [];
        $today = now()->toDateString();

        $existingSession = WorkoutSession::where('client_id', $clientId)
            ->where('plan_id', $planId)
            ->where('day_name', $dayName)
            ->where('session_date', $today)
            ->where('completed', false)
            ->latest('id')
            ->first();

        if ($existingSession && $existingSession->created_at->diffInHours(now()) < 3) {
            $setData = $this->buildSetDataWithLogs($clientId, $exercises, $existingSession);
            $activeSession = [
                'id' => $existingSession->id,
                'startTime' => $existingSession->created_at->toIso8601String(),
                'setData' => $setData,
            ];
        }

        // Enrich exercises with last_weight / last_reps from previous sessions
        $exercises = $this->enrichExercisesWithHistory($clientId, $exercises);

        // Enrich exercises with GIF URLs
        $this->media->enrichWithMedia($exercises);

        // Build full days array including exercises so Vue can switch days client-side
        $fullDays = array_map(fn ($d, $i) => [
            'index' => $i,
            'nombre' => $this->resolveDayName($d, $i),
            'grupo_muscular' => $d['grupo_muscular'] ?? $d['muscle_group'] ?? '',
            'ejercicios' => ($i === $currentDayIndex) ? $exercises : ($d['ejercicios'] ?? $d['exercises'] ?? $d['ejercicios_dia'] ?? []),
        ], $days, array_keys($days));

        return response()->json([
            // camelCase keys — matches Vue WorkoutPlayer expectations
            'hasPlan' => true,
            'showTutorial' => $showTutorial,
            'planId' => $planId,
            'hasProgressions' => $hasProgressions,
            'currentWeek' => $currentWeek,
            'totalWeeks' => $totalWeeks,
            'days' => $fullDays,
            'currentDayIndex' => $currentDayIndex,
            'dayName' => $dayName,
            'muscleGroup' => $muscleGroup,
            'exercises' => $exercises,
            'blockGroups' => $blockGroups,
            'activeSession' => $activeSession,
            'setData' => $setData,
        ]);
    }

    /**
     * POST /api/v/client/workout/start
     *
     * Start a workout session. Ports WorkoutPlayer.php startWorkout() logic.
     */
    public function startWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'day_index' => 'required|integer|min:0',
            'week' => 'nullable|integer|min:1',
        ]);

        $dayIndex = (int) $request->input('day_index');
        $weekNum = $request->input('week');

        // Load the active plan to derive plan_id and day_name
        $plan = AssignedPlan::where('client_id', $clientId)
            ->where('plan_type', 'entrenamiento')
            ->where('active', true)
            ->latest('id')
            ->first();

        $planId = $plan?->id;
        $dayName = $this->resolveDayName([], $dayIndex);

        if ($plan) {
            $content = is_array($plan->content) ? $plan->content : json_decode($plan->content, true);

            if ($weekNum && isset($content['semanas'][$weekNum - 1])) {
                $dias = $content['semanas'][$weekNum - 1]['dias'] ?? [];
                $dayName = $this->resolveDayName($dias[$dayIndex] ?? [], $dayIndex);
            } elseif (isset($content['dias'][$dayIndex])) {
                $dayName = $this->resolveDayName($content['dias'][$dayIndex], $dayIndex);
            }
        }

        // El unique key (client_id, day_name, session_date) no incluye `completed`,
        // así que `firstOrCreate` no puede crear una nueva sesión si ya existe una
        // completada hoy. Manejamos los tres casos explícitamente:
        //   - Sesión incompleta hoy → resumir
        //   - Sesión completada hoy → reset a incompleta (permite reintentar)
        //   - Sin sesión hoy        → crear nueva
        $today = now()->toDateString();

        $session = WorkoutSession::where('client_id', $clientId)
            ->where('day_name', $dayName)
            ->where('session_date', $today)
            ->orderByDesc('id')
            ->first();

        if (! $session) {
            try {
                $session = WorkoutSession::create([
                    'client_id' => $clientId,
                    'plan_id' => $planId,
                    'day_name' => $dayName,
                    'session_date' => $today,
                    'completed' => false,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Race: otra petición simultánea creó la sesión entre el SELECT y el INSERT.
                $session = WorkoutSession::where('client_id', $clientId)
                    ->where('day_name', $dayName)
                    ->where('session_date', $today)
                    ->orderByDesc('id')
                    ->firstOrFail();
                if ($session->completed) {
                    $session->update(['completed' => false]);
                }
            }
        } elseif ($session->completed) {
            $session->update(['completed' => false]);
        }

        Cache::forget("wp:session:{$clientId}:".$today);

        return response()->json([
            'session_id' => $session->id,
            'start_time' => $session->created_at->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v/client/workout/complete-set
     *
     * Mark a set complete with weight/reps. Ports WorkoutPlayer.php completeSet() logic.
     */
    public function completeSet(Request $request): JsonResponse
    {
        if (blank($request->input('exercise_name'))) {
            \Log::warning('TrainingController.completeSet called without exercise_name', [
                'client_id' => auth('wellcore')->id(),
                'payload' => $request->except(['_token', 'media_file']),
                'referer' => $request->headers->get('referer'),
            ]);

            return response()->json(['ok' => false, 'reason' => 'missing_exercise'], 204);
        }

        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'session_id' => 'required|integer',
            'exercise_index' => 'required|integer|min:0|max:500',
            'set_number' => 'required|integer|min:1|max:50',
            'exercise_name' => 'required|string|max:255',
            // Anti-cheat: límites realistas humanos.
            'weight' => 'nullable|numeric|min:0|max:500',
            'reps' => 'required|integer|min:0|max:100',
            'target_reps' => 'nullable|string|max:50',
            'target_weight' => 'nullable|numeric|min:0|max:500',
            'is_cardio' => 'nullable|boolean',
            'duration_minutes' => 'nullable|integer|min:0|max:300',
            'duration_seconds' => 'nullable|integer|min:0|max:7200',
            'speed_kmh' => 'nullable|numeric|min:0|max:40',
            'incline_percent' => 'nullable|integer|min:0|max:40',
        ]);

        $sessionId = (int) $request->input('session_id');
        $exerciseIndex = (int) $request->input('exercise_index');
        $setNumber = (int) $request->input('set_number');
        $exerciseName = $request->input('exercise_name');
        $weight = (float) ($request->input('weight', 0));
        $reps = (int) $request->input('reps');
        $isCardio = (bool) $request->input('is_cardio', false);

        // Verifica que la sesión pertenezca al cliente.
        $session = WorkoutSession::where('id', $sessionId)
            ->where('client_id', $clientId)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json([
                'message' => 'No encontramos esa sesión de entrenamiento.',
            ], 404);
        }

        $logData = $isCardio ? [
            'weight_kg' => 0,
            'reps' => (int) $request->input('duration_minutes', 0),
            'is_cardio' => true,
            'duration_minutes' => (int) $request->input('duration_minutes', 0),
            'speed_kmh' => (float) $request->input('speed_kmh', 0),
            'incline_percent' => (int) $request->input('incline_percent', 0),
            'completed' => true,
        ] : [
            'weight_kg' => $weight,
            'reps' => $reps,
            'completed' => true,
        ];

        try {
            $isPr = DB::transaction(function () use (
                $sessionId, $clientId, $exerciseName, $exerciseIndex, $setNumber,
                $logData, $isCardio, $weight, $reps, $request
            ): bool {
                // Upsert atómico — evita race condition entre lecturas concurrentes.
                // Note: updated_at omitted — production workout_logs only has created_at.
                WorkoutLog::upsert(
                    [array_merge($logData, [
                        'client_id' => $clientId,
                        'session_id' => $sessionId,
                        'exercise_name' => $exerciseName,
                        'block_type' => 'normal',
                        'block_order' => $exerciseIndex,
                        'set_number' => $setNumber,
                        'target_reps' => $request->input('target_reps'),
                        'target_weight' => $request->input('target_weight'),
                        'is_pr' => false,
                        'created_at' => now(),
                    ])],
                    uniqueBy: ['session_id', 'exercise_name', 'set_number', 'block_order'],
                    update: array_merge(array_keys($logData), ['target_reps', 'target_weight'])
                );

                if ($isCardio || $weight <= 0) {
                    return false;
                }

                try {
                    $pr = WorkoutPr::checkAndAward($clientId, $exerciseName, $weight, $reps);
                } catch (\Throwable $e) {
                    Log::warning('WorkoutPr::checkAndAward failed', [
                        'user_id' => $clientId,
                        'exercise' => $exerciseName,
                        'error' => $e->getMessage(),
                    ]);

                    return false;
                }

                if (! $pr) {
                    return false;
                }

                WorkoutLog::where('session_id', $sessionId)
                    ->where('exercise_name', $exerciseName)
                    ->where('set_number', $setNumber)
                    ->where('block_order', $exerciseIndex)
                    ->update(['is_pr' => true]);

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('completeSet failed', [
                'user_id' => $clientId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'No pudimos guardar la serie. Intenta de nuevo.',
            ], 500);
        }

        return response()->json([
            'completed' => true,
            'is_pr' => $isPr,
        ]);
    }

    /**
     * POST /api/v/client/workout/uncomplete-set
     */
    public function uncompleteSet(Request $request): JsonResponse
    {
        if (blank($request->input('exercise_name'))) {
            \Log::warning('TrainingController.uncompleteSet called without exercise_name', [
                'client_id' => auth('wellcore')->id(),
                'payload' => $request->except(['_token', 'media_file']),
                'referer' => $request->headers->get('referer'),
            ]);

            return response()->json(['ok' => false, 'reason' => 'missing_exercise'], 204);
        }

        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'session_id' => 'required|integer',
            'exercise_name' => 'required|string|max:200',
            'set_number' => 'required|integer|min:1',
            'exercise_index' => 'nullable|integer',
        ]);

        $session = WorkoutSession::where('id', $validated['session_id'])
            ->where('client_id', $client->id)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Sesion no encontrada.'], 404);
        }

        WorkoutLog::where('session_id', $validated['session_id'])
            ->where('exercise_name', $validated['exercise_name'])
            ->where('set_number', $validated['set_number'])
            ->delete();

        return response()->json(['uncompleted' => true]);
    }

    /**
     * POST /api/v/client/workout/dismiss-tutorial
     */
    public function dismissWorkoutTutorial(Request $request): JsonResponse
    {
        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/v/client/workout/abandon
     */
    public function abandonWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);

        $validated = $request->validate([
            'session_id' => 'required|integer',
        ]);

        $session = WorkoutSession::where('id', $validated['session_id'])
            ->where('client_id', $client->id)
            ->where('completed', false)
            ->first();

        if ($session) {
            $session->delete();
        }

        return response()->json(['abandoned' => true]);
    }

    /**
     * POST /api/v/client/workout/finish
     *
     * Finish workout session. Ports WorkoutPlayer.php completeWorkout() logic.
     * Awards XP, detects PRs, updates streaks.
     */
    public function finishWorkout(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'session_id' => 'required|integer',
            'feeling' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        $session = WorkoutSession::where('id', $request->input('session_id'))
            ->where('client_id', $clientId)
            ->where('completed', false)
            ->first();

        if (! $session) {
            return response()->json(['error' => 'Sesion no encontrada o ya completada.'], 404);
        }

        // Use frontend elapsed time if provided (more accurate), fallback to server diff
        $elapsedFromClient = (int) $request->input('elapsed', 0);
        $durationSec = $elapsedFromClient > 0
            ? $elapsedFromClient
            : (int) $session->created_at->diffInSeconds(now());

        // Cap at 4 hours max (14400 sec) to prevent absurd values from stale sessions
        $durationSec = min($durationSec, 14400);

        $session->update([
            'completed' => true,
            'duration_sec' => $durationSec,
            'feeling' => $request->input('feeling'),
            'notes' => $request->input('notes'),
        ]);

        try {
            $session->calculateTotals();
        } catch (\Throwable $e) {
            \Log::warning('TrainingController: calculateTotals failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Write training_log FIRST so recalculateStreak() sees today's entry
        try {
            TrainingLog::updateOrCreate(
                ['client_id' => $clientId, 'log_date' => now()->toDateString()],
                [
                    'completed' => true,
                    'year_num' => (int) now()->isoFormat('GGGG'),
                    'week_num' => (int) now()->isoFormat('W'),
                ]
            );
        } catch (\Throwable $e) {
            \Log::warning('TrainingController: training_log write failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Idempotente: si la sesión ya tenía XP otorgado (caso de reinicio tras
        // completarla previamente), no volvemos a sumar al total del cliente.
        $xpEarned = (int) ($session->xp_earned ?? 0);
        if ($xpEarned <= 0) {
            try {
                $xpEarned = $session->awardXp();
                $this->updateClientXp($clientId, $xpEarned);
                $session->update(['xp_earned' => $xpEarned]);
            } catch (\Throwable $e) {
                \Log::warning('TrainingController: awardXp failed', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        ClientCacheService::invalidateDashboard($clientId);
        Cache::forget("training:month_sessions:{$clientId}:".now()->format('Y-m'));

        // Count PRs from this session
        $prCount = WorkoutLog::where('session_id', $session->id)
            ->where('completed', true)
            ->where('is_pr', true)
            ->count();

        $pulsoOffer = [
            'session_id' => $session->id,
            'pulso_type' => 'entrenamiento',
            'stats' => [
                'volume_kg' => round((float) ($session->total_volume_kg ?? 0), 1),
                'series' => (int) ($session->total_sets ?? 0),
                'ejercicios' => $session->logs()->where('completed', true)->distinct()->count('exercise_name'),
                'duracion_min' => (int) round($durationSec / 60),
                'day_name' => $session->day_name ?? '',
            ],
        ];

        return response()->json([
            'session_id' => $session->id,
            'xp_earned' => $xpEarned,
            'pr_count' => $prCount,
            'duration' => $session->formattedDuration(),
            'pulso_offer' => $pulsoOffer,
        ]);
    }

    // ─── Workout Summary ───────────────────────────────────────────────

    /**
     * GET /api/v/client/workout-summary/{sessionId}
     *
     * Post-workout summary. Ports WorkoutSummary.php mount() logic.
     */
    public function workoutSummary(Request $request, string $sessionId): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        if ($sessionId === 'latest') {
            $session = WorkoutSession::where('client_id', $clientId)
                ->where('completed', true)
                ->latest()
                ->firstOrFail();
        } else {
            $session = WorkoutSession::where('client_id', $clientId)
                ->findOrFail((int) $sessionId);
        }

        // SQL-level aggregates: source-of-truth, immune to collection filter
        // / boolean cast quirks. Resolves bug where sets_completed and reps
        // showed 0 even after sets were logged successfully.
        $logStats = WorkoutLog::where('session_id', $session->id)
            ->selectRaw('
                COUNT(*) as total_sets,
                SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_sets,
                COALESCE(SUM(CASE WHEN completed = 1 THEN reps ELSE 0 END), 0) as completed_reps,
                COUNT(DISTINCT CASE WHEN completed = 1 THEN exercise_name END) as completed_exercises,
                COALESCE(MAX(CASE WHEN completed = 1 THEN weight_kg END), 0) as max_weight,
                SUM(CASE WHEN completed = 1 AND is_pr = 1 THEN 1 ELSE 0 END) as pr_count,
                COALESCE(SUM(CASE WHEN completed = 1 THEN COALESCE(weight_kg, 0) * COALESCE(reps, 0) ELSE 0 END), 0) as volume_calc
            ')
            ->first();

        $maxWeight = (float) ($logStats->max_weight ?? 0);
        $maxWeightExercise = null;
        if ($maxWeight > 0) {
            $heaviestLog = WorkoutLog::where('session_id', $session->id)
                ->where('completed', true)
                ->where('weight_kg', $maxWeight)
                ->orderByDesc('id')
                ->first(['exercise_name']);
            $maxWeightExercise = $heaviestLog?->exercise_name;
        }

        // Fallback chain: prefer cached column (calculateTotals), fall back to SQL aggregate
        $totalVolume = (float) ($session->total_volume_kg ?? 0);
        if ($totalVolume <= 0) {
            $totalVolume = (float) $logStats->volume_calc;
        }

        $stats = [
            'duration' => $session->formattedDuration(),
            'duration_sec' => (int) ($session->duration_sec ?? 0),
            'max_weight' => $maxWeight,
            'max_weight_exercise' => $maxWeightExercise,
            'pr_count' => (int) $logStats->pr_count,
            'reps' => (int) $logStats->completed_reps,
            'sets_completed' => (int) $logStats->completed_sets,
            'sets_total' => (int) $logStats->total_sets,
            'exercises_count' => (int) $logStats->completed_exercises,
            'total_volume' => $totalVolume,
        ];

        // Self-healing: if session is completed but cached totals are stale,
        // recompute and persist so coach dashboards / analytics see truth.
        if ($session->completed && (
            (int) ($session->total_sets ?? 0) !== $stats['sets_completed'] ||
            (int) ($session->total_reps ?? 0) !== $stats['reps']
        )) {
            try {
                $session->calculateTotals();
            } catch (\Throwable $e) {
                Log::warning('workoutSummary: self-heal calculateTotals failed', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $cacheKey = "workout_summary_xp:{$session->id}";
        $xpEarned = Cache::remember($cacheKey, 86400 * 30, function () use ($session) {
            return $session->awardXp();
        });

        // Note: $completedLogs fue removido cuando workoutSummary migró a SQL aggregates,
        // así que consultamos los PR logs directamente para no romper el endpoint.
        $prLogs = WorkoutLog::where('session_id', $session->id)
            ->where('completed', true)
            ->where('is_pr', true)
            ->get();

        $prs = $prLogs->map(function ($log) use ($clientId, $session) {
            // Look up the best weight for this exercise in any prior completed session
            $prevBest = WorkoutLog::where('client_id', $clientId)
                ->where('exercise_name', $log->exercise_name)
                ->where('completed', true)
                ->where('session_id', '!=', $session->id)
                ->whereHas('session', fn ($q) => $q->where('completed', true))
                ->orderByDesc('weight_kg')
                ->first();

            $previousWeight = $prevBest ? (float) $prevBest->weight_kg : null;
            $previousReps = $prevBest ? (int) $prevBest->reps : null;

            return [
                'exercise' => $log->exercise_name,
                'weight' => (float) $log->weight_kg,
                'reps' => (int) $log->reps,
                'previous_weight' => $previousWeight,
                'previous_reps' => $previousReps,
            ];
        })->values()->toArray();

        $sessionHistory = WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->where('id', '!=', $session->id)
            ->orderByDesc('session_date')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'date' => $s->session_date?->format('d M') ?? '-',
                'day_name' => $s->day_name ?? '-',
                'duration' => $s->formattedDuration(),
                'total_volume' => (float) ($s->total_volume ?? 0),
            ])
            ->toArray();

        return response()->json([
            'session' => [
                'id' => $session->id,
                'day_name' => $session->day_name,
                'session_date' => $session->session_date?->format('Y-m-d'),
                'feeling' => $session->feeling,
                'notes' => $session->notes,
            ],
            'stats' => $stats,
            'xp_earned' => $xpEarned,
            'prs' => $prs,
            'session_history' => $sessionHistory,
        ]);
    }

    /**
     * POST /api/v/client/workout-summary/{sessionId}/feeling
     *
     * Save workout feeling and notes. Ports WorkoutSummary.php saveFeedback().
     */
    public function saveWorkoutFeeling(Request $request, string $sessionId): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'feeling' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($sessionId === 'latest') {
            $session = WorkoutSession::where('client_id', $clientId)->where('completed', true)->latest()->firstOrFail();
        } else {
            $session = WorkoutSession::where('client_id', $clientId)->findOrFail((int) $sessionId);
        }

        $session->update([
            'feeling' => $request->input('feeling'),
            'notes' => $request->input('notes') ?: null,
        ]);

        return response()->json(['saved' => true]);
    }

    // ─── Check-in ──────────────────────────────────────────────────────

    /**
     * GET /api/v/client/checkin
     *
     * Get check-in form data and recent check-ins. Ports CheckinForm.php.
     */
    public function checkin(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $timezone = $this->resolveClientTimezone($client);

        $showTutorial = ! Checkin::where('client_id', $clientId)->exists();

        $dayOfWeek = now($timezone)->dayOfWeek;
        $isCheckinAvailable = in_array($dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true);

        $cached = Cache::remember("checkin:recent:{$clientId}", 300, function () use ($clientId) {
            return Checkin::where('client_id', $clientId)
                ->orderByDesc('checkin_date')
                ->limit(10)
                ->get()
                ->toArray();
        });

        $weekLabel = $this->weekLabelForTimezone($timezone);
        $alreadySubmitted = Checkin::where('client_id', $clientId)
            ->where('week_label', $weekLabel)
            ->exists();

        return response()->json([
            'show_tutorial' => $showTutorial,
            'is_checkin_available' => $isCheckinAvailable,
            'already_submitted' => $alreadySubmitted,
            'week_label' => $weekLabel,
            'recent_checkins' => $cached,
            'timezone' => $timezone,
        ]);
    }

    /**
     * POST /api/v/client/checkin
     *
     * Submit weekly check-in. Ports CheckinForm.php submit().
     */
    public function submitCheckin(Request $request): JsonResponse
    {
        $client = $this->resolveClientOrFail($request);
        $clientId = $client->id;

        $request->validate([
            'bienestar' => 'required|integer|min:1|max:5',
            'dias_entrenados' => 'required|integer|min:0|max:7',
            'nutricion' => 'required|in:Si,No,Parcial',
            'rpe' => 'required|integer|min:1|max:10',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $timezone = $this->resolveClientTimezone($client);

        $dayOfWeek = now($timezone)->dayOfWeek;
        if (! in_array($dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true)) {
            return response()->json([
                'message' => 'El check-in semanal solo está disponible los viernes y sábados.',
                'timezone' => $timezone,
            ], 422);
        }

        $weekLabel = $this->weekLabelForTimezone($timezone);

        $alreadySubmitted = Checkin::where('client_id', $clientId)
            ->where('week_label', $weekLabel)
            ->exists();

        if ($alreadySubmitted) {
            return response()->json([
                'message' => 'Ya enviaste tu check-in esta semana.',
                'timezone' => $timezone,
            ], 422);
        }

        try {
            $checkin = Checkin::create([
                'client_id' => $clientId,
                'week_label' => $weekLabel,
                'checkin_date' => now($timezone)->toDateString(),
                'bienestar' => $request->input('bienestar'),
                'dias_entrenados' => $request->input('dias_entrenados'),
                'nutricion' => $request->input('nutricion'),
                'comentario' => $request->input('comentario'),
                'rpe' => $request->input('rpe'),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('submitCheckin failed', [
                'user_id' => $clientId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'No pudimos guardar tu check-in. Intenta de nuevo.',
            ], 500);
        }

        Cache::forget("checkin:recent:{$clientId}");
        ClientCacheService::invalidateDashboard($clientId);

        $coachId = AssignedPlan::where('client_id', $clientId)->where('active', true)->value('assigned_by');
        if ($coachId) {
            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id' => $coachId,
                'type' => 'new_checkin',
                'title' => 'Nuevo Check-in',
                'body' => "{$client->name} envió su check-in semanal",
                'link' => '/coach/checkins',
            ]);
            try {
                PushNotificationService::notifyCheckinReminder($coachId);
            } catch (\Throwable) {
            }
        }

        return response()->json([
            'saved' => true,
            'checkin_id' => $checkin->id,
            'timezone' => $timezone,
        ]);
    }

    /**
     * Resuelve la zona horaria del cliente (cae a America/Bogota si no hay).
     */
    private function resolveClientTimezone(mixed $client): string
    {
        $tz = is_object($client) && isset($client->timezone) ? $client->timezone : null;

        if (! is_string($tz) || $tz === '') {
            return 'America/Bogota';
        }

        if (! in_array($tz, timezone_identifiers_list(), true)) {
            return 'America/Bogota';
        }

        return $tz;
    }

    /**
     * ISO week label (YYYY-Www) en la timezone del cliente.
     */
    private function weekLabelForTimezone(string $timezone): string
    {
        $now = now($timezone);

        return $now->isoFormat('GGGG').'-W'.str_pad($now->isoFormat('W'), 2, '0', STR_PAD_LEFT);
    }

    // ─── Private helpers ───────────────────────────────────────────────

    /**
     * Build habit data for the last 30 days. Ports PlanViewer.php loadHabits().
     */
    private function buildHabitData(int $clientId): array
    {
        $startDate = Carbon::now()->subDays(30);
        $today = Carbon::today();

        $logs = HabitLog::where('client_id', $clientId)
            ->where('log_date', '>=', $startDate)
            ->orderByDesc('log_date')
            ->get();

        $habitTypes = ['agua', 'sueno', 'entrenamiento', 'nutricion', 'suplementos'];
        $habitLabels = [
            'agua' => 'Agua',
            'sueno' => 'Sueno',
            'entrenamiento' => 'Entrenamiento',
            'nutricion' => 'Nutricion',
            'suplementos' => 'Suplementos',
        ];
        $habitIcons = [
            'agua' => 'droplet',
            'sueno' => 'moon',
            'entrenamiento' => 'dumbbell',
            'nutricion' => 'utensils',
            'suplementos' => 'pill',
        ];

        $habits = [];

        foreach ($habitTypes as $type) {
            $typeLogs = $logs->where('habit_type', $type);

            $avg = $typeLogs->count() > 0 ? round($typeLogs->avg('value'), 1) : 0;

            $streak = 0;
            $checkDate = $today->copy();
            for ($i = 0; $i < 30; $i++) {
                $dayLog = $typeLogs->first(function ($log) use ($checkDate) {
                    return $log->log_date->format('Y-m-d') === $checkDate->format('Y-m-d');
                });
                if ($dayLog && $dayLog->value > 0) {
                    $streak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }

            $last7 = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dayLog = $typeLogs->first(function ($log) use ($date) {
                    return $log->log_date->format('Y-m-d') === $date->format('Y-m-d');
                });
                $last7[] = [
                    'date' => $date->format('D'),
                    'value' => $dayLog ? $dayLog->value : 0,
                ];
            }

            $habits[] = [
                'type' => $type,
                'label' => $habitLabels[$type],
                'icon' => $habitIcons[$type],
                'streak' => $streak,
                'average' => $avg,
                'last7' => $last7,
            ];
        }

        $daysInMonth = $today->day;
        $monthStart = $today->copy()->startOfMonth();
        $daysWithLogs = $logs
            ->where('log_date', '>=', $monthStart)
            ->pluck('log_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->count();

        $compliance = $daysInMonth > 0 ? round(($daysWithLogs / $daysInMonth) * 100, 0) : 0;

        return [
            'habits' => $habits,
            'compliance' => $compliance,
        ];
    }

    /**
     * Normalize a nutrition plan JSON so PlanViewer.vue can render it consistently.
     * Handles field name variations produced by different plan upload formats.
     */
    private function normalizeNutritionPlan(?array $plan): ?array
    {
        if (! $plan) {
            return null;
        }

        // Alias varios nombres de calorías → objetivo_cal (PlanViewer checks objetivo_cal first)
        if (! isset($plan['objetivo_cal'])) {
            $plan['objetivo_cal'] = $plan['objetivo_calorico']
                ?? $plan['calorias_objetivo']
                ?? $plan['calorias_diarias']
                ?? $plan['calorias']
                ?? null;
        }

        // Alias macros variants → macros normalized keys
        if (isset($plan['macros_objetivo']) && ! isset($plan['macros'])) {
            $plan['macros'] = $plan['macros_objetivo'];
        }
        if (isset($plan['macros']['proteina']) && ! isset($plan['macros']['proteina_g'])) {
            $plan['macros']['proteina_g'] = $plan['macros']['proteina'];
        }

        // Alias nota_coach / notas_generales → notas_coach
        if (! isset($plan['notas_coach'])) {
            $plan['notas_coach'] = $plan['nota_coach'] ?? $plan['notas_generales'] ?? null;
        }

        // Alias tips_nutricionales → tips
        if (! isset($plan['tips']) && isset($plan['tips_nutricionales'])) {
            $plan['tips'] = $plan['tips_nutricionales'];
        }

        // Map comidas_sugeridas → comidas so the full-featured tab section renders
        // (with macros chips, hora, CAMBIAR button, Opcion A/B/C tabs)
        if (! isset($plan['comidas']) && isset($plan['comidas_sugeridas'])) {
            $plan['comidas'] = $plan['comidas_sugeridas'];
        }

        // Convert opciones strings → opcion_a/b/c on any comidas array
        // (handles both comidas_sugeridas-sourced and directly-stored comidas)
        if (isset($plan['comidas']) && is_array($plan['comidas'])) {
            $plan['comidas'] = array_map(function (array $meal): array {
                // Convert string alimentos to array (split on ". " boundaries)
                if (isset($meal['alimentos']) && is_string($meal['alimentos'])) {
                    $parts = preg_split('/\.\s+/', rtrim($meal['alimentos'], '. '));
                    $meal['alimentos'] = array_values(array_filter(array_map('trim', $parts)));
                }
                if (
                    isset($meal['opciones']) &&
                    is_array($meal['opciones']) &&
                    ! isset($meal['opcion_a'])
                ) {
                    $opts = array_values($meal['opciones']);
                    $keys = ['opcion_a', 'opcion_b', 'opcion_c'];
                    foreach ($keys as $i => $key) {
                        if (! isset($opts[$i])) {
                            break;
                        }
                        $raw = preg_replace('/^[Oo]pci[oó]n\s*\d+\s*:\s*/u', '', trim((string) $opts[$i]));
                        $ingredients = array_map('trim', explode(' + ', $raw));
                        $meal[$key] = array_values(array_filter($ingredients));
                    }
                    unset($meal['opciones']);
                }

                return $meal;
            }, $plan['comidas']);
        }

        return $plan;
    }

    /**
     * Normalize a ciclo hormonal plan so it matches the PlanViewer Vue component schema.
     * Maps Spanish field names (compuestos, pct object) to the English/array keys the template expects.
     */
    private function normalizeCicloPlan(?array $plan): ?array
    {
        if (! $plan) {
            return null;
        }

        // compounds ← compuestos
        if (! isset($plan['compounds']) && isset($plan['compuestos'])) {
            $plan['compounds'] = $plan['compuestos'];
        }

        // nombre ← titulo
        if (! isset($plan['nombre']) && ! isset($plan['name']) && isset($plan['titulo'])) {
            $plan['nombre'] = $plan['titulo'];
        }

        // descripcion_protocolo ← objetivo
        if (! isset($plan['descripcion_protocolo']) && ! isset($plan['descripcion']) && isset($plan['objetivo'])) {
            $plan['descripcion_protocolo'] = $plan['objetivo'];
        }

        // advertencia ← alertas[] joined (first 2 items to avoid wall of text)
        if (! isset($plan['advertencia']) && ! isset($plan['warning']) && ! empty($plan['alertas'])) {
            $plan['advertencia'] = implode(' | ', array_slice((array) $plan['alertas'], 0, 2));
        }

        // fases ← semanas (week-level phase info)
        if (! isset($plan['fases']) && ! isset($plan['phases']) && ! empty($plan['semanas'])) {
            $plan['fases'] = array_map(fn ($s) => [
                'nombre' => 'Semana '.($s['numero'] ?? '').' — '.($s['fase'] ?? ''),
                'descripcion' => $s['notas_semana'] ?? '',
            ], $plan['semanas']);
        }

        // pct object → pct array (component iterates it)
        if (isset($plan['pct']) && is_array($plan['pct']) && ! isset($plan['pct'][0])) {
            $farmaco = $plan['pct']['farmaco'] ?? 'PCT';
            $inicio = $plan['pct']['inicio'] ?? null;
            $protocolo = $plan['pct']['protocolo'] ?? [];
            $notas = $plan['pct']['notas'] ?? null;
            $plan['pct'] = array_map(fn ($p) => [
                'nombre' => $farmaco.' — Semana '.($p['semana'] ?? ''),
                'dosis' => $p['dosis'] ?? '',
                'frecuencia' => 'Diario con comida AM',
                'inicio' => $inicio,
                'notas' => $notas,
            ], $protocolo);
        }

        // emergencia ← protocolo_suspension eventos (suspension timeline)
        if (! isset($plan['emergencia']) && ! empty($plan['protocolo_suspension']['eventos'])) {
            $plan['emergencia'] = array_map(fn ($e) => $e['fecha'].': '.$e['accion'].' — '.$e['razon'], $plan['protocolo_suspension']['eventos']);
        }

        // monitoreo_diario ← alertas remaining items
        if (! isset($plan['monitoreo_diario']) && ! empty($plan['alertas'])) {
            $plan['monitoreo_diario'] = array_map(fn ($a) => ['item' => $a], (array) $plan['alertas']);
        }

        return $plan;
    }

    /**
     * Normalize a training plan JSON structure. Ported from PlanViewer.php.
     */
    private function normalizeTrainingPlan(?array $content): ?array
    {
        if (! $content) {
            return null;
        }

        if (isset($content['semanas']) && is_array($content['semanas'])) {
            // Top-level dias (plan with semanas header + shared days array)
            $topDias = isset($content['dias']) && is_array($content['dias']) ? $content['dias'] : null;

            foreach ($content['semanas'] as &$semana) {
                $raw = $semana['dias'] ?? $semana['days'] ?? [];
                // If the semana has no days of its own, fall back to the top-level dias array
                if (empty($raw) && $topDias) {
                    $raw = $topDias;
                }
                $semana['dias'] = $this->normalizeDays($raw);
                unset($semana['days']);
                $semana['numero'] = $semana['numero'] ?? $semana['number'] ?? $semana['semana'] ?? null;
                $semana['fase'] = $semana['fase'] ?? $semana['phase'] ?? $semana['nombre'] ?? null;
            }
            unset($semana);

            return $content;
        }

        if (! isset($content['dias']) && ! isset($content['days'])
            && isset($content['plan']) && is_array($content['plan'])) {
            $content['semanas'] = [];
            foreach ($content['plan'] as $idx => $week) {
                if (is_array($week) && (isset($week['days']) || isset($week['dias']))) {
                    $content['semanas'][] = [
                        'numero' => $week['week'] ?? $week['semana'] ?? ($idx + 1),
                        'fase' => $week['phase'] ?? $week['fase'] ?? $week['name'] ?? null,
                        'dias' => $this->normalizeDays($week['days'] ?? $week['dias'] ?? []),
                    ];
                }
            }
            if (! empty($content['semanas'])) {
                unset($content['plan']);

                return $content;
            }
        }

        // Handle weeks as array of week-objects: {weeks: [{week:1, days:[...]}, ...]}
        if (! isset($content['semanas']) && ! isset($content['dias']) && ! isset($content['days'])
            && isset($content['weeks']) && is_array($content['weeks'])) {
            $firstWeek = $content['weeks'][0] ?? null;
            if (is_array($firstWeek) && (isset($firstWeek['days']) || isset($firstWeek['dias']))) {
                $content['semanas'] = [];
                foreach ($content['weeks'] as $idx => $week) {
                    if (is_array($week)) {
                        $content['semanas'][] = [
                            'numero' => $week['week'] ?? $week['semana'] ?? ($idx + 1),
                            'fase' => $week['phase'] ?? $week['fase'] ?? $week['name'] ?? null,
                            'dias' => $this->normalizeDays($week['days'] ?? $week['dias'] ?? []),
                        ];
                    }
                }
                unset($content['weeks']);

                return $content;
            }
        }

        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            $days = $content['days'] ?? null;
            $weeks = $content['weeks'] ?? null;
            if (is_array($days)) {
                $content['dias'] = $days;
            } elseif (is_array($weeks)) {
                $content['dias'] = $weeks;
            }
            unset($content['days']);
        }

        if (! isset($content['dias']) || ! is_array($content['dias'])) {
            return $content;
        }

        $content['dias'] = $this->normalizeDays($content['dias']);

        $duracion = (int) ($content['duracion_semanas'] ?? 1);
        if ($duracion > 1) {
            $content['semanas'] = [];
            for ($w = 1; $w <= $duracion; $w++) {
                $content['semanas'][] = [
                    'numero' => $w,
                    'fase' => $content['fases'][$w - 1] ?? null,
                    'dias' => $content['dias'],
                ];
            }
        } else {
            $content['semanas'] = [
                [
                    'numero' => 1,
                    'fase' => $content['fase'] ?? null,
                    'dias' => $content['dias'],
                ],
            ];
        }

        return $content;
    }

    /**
     * Normalize days array. Ported from PlanViewer.php.
     */
    private function normalizeDays(array $days): array
    {
        $normalized = [];
        foreach ($days as $dia) {
            if (! is_array($dia)) {
                continue;
            }

            if (! isset($dia['nombre']) && isset($dia['name'])) {
                $dia['nombre'] = $dia['name'];
            }
            if (! isset($dia['dia']) && isset($dia['day'])) {
                $dia['dia'] = $dia['day'];
            }

            if (! isset($dia['ejercicios'])) {
                $exercises = $dia['exercises'] ?? $dia['sessions'] ?? null;
                if ($exercises !== null) {
                    $dia['ejercicios'] = $exercises;
                    unset($dia['exercises'], $dia['sessions']);
                }
            }

            if (isset($dia['ejercicios']) && is_array($dia['ejercicios'])) {
                foreach ($dia['ejercicios'] as &$ej) {
                    if (! is_array($ej)) {
                        continue;
                    }
                    if (! isset($ej['nombre']) && isset($ej['name'])) {
                        $ej['nombre'] = $ej['name'];
                    }
                    if (! isset($ej['ejercicio']) && isset($ej['exercise'])) {
                        $ej['ejercicio'] = $ej['exercise'];
                    }
                    if (! isset($ej['series']) && isset($ej['sets'])) {
                        $ej['series'] = $ej['sets'];
                    }
                    if (! isset($ej['repeticiones']) && isset($ej['reps'])) {
                        $ej['repeticiones'] = $ej['reps'];
                    }
                }
                unset($ej);
            }

            $normalized[] = $dia;
        }

        return $normalized;
    }

    /**
     * Normalize a single training day (WorkoutPlayer format). Ported from WorkoutPlayer.php.
     */
    private function normalizeDay(array $dia): array
    {
        if (! isset($dia['nombre']) && isset($dia['name'])) {
            $dia['nombre'] = $dia['name'];
        }

        if (! isset($dia['ejercicios'])) {
            $exFallback = $dia['exercises'] ?? $dia['sessions'] ?? null;
            if ($exFallback !== null) {
                $dia['ejercicios'] = $exFallback;
                unset($dia['exercises'], $dia['sessions']);
            }
        }

        if (isset($dia['ejercicios']) && is_array($dia['ejercicios'])) {
            foreach ($dia['ejercicios'] as &$ej) {
                if (! is_array($ej)) {
                    continue;
                }
                if (! isset($ej['nombre'])) {
                    $ej['nombre'] = $ej['name'] ?? $ej['exercise'] ?? $ej['ejercicio'] ?? '';
                }
                if (! isset($ej['series']) && isset($ej['sets'])) {
                    $ej['series'] = $ej['sets'];
                }
                if (! isset($ej['repeticiones']) && isset($ej['reps'])) {
                    $ej['repeticiones'] = $ej['reps'];
                }
                if (! isset($ej['descanso'])) {
                    $ej['descanso'] = $ej['rest'] ?? $ej['rest_seconds'] ?? '90s';
                }

                $ej['is_cardio'] = $ej['is_cardio'] ?? $this->isCardioExercise($ej);
            }
            unset($ej);
        }

        return $dia;
    }

    /**
     * Detect cardio exercises by keywords. Ported from WorkoutPlayer.php.
     */
    private function isCardioExercise(array $exercise): bool
    {
        $name = mb_strtolower($exercise['nombre'] ?? $exercise['name'] ?? '');
        $type = mb_strtolower($exercise['tipo'] ?? $exercise['type'] ?? '');

        $cardioKeywords = [
            'caminadora', 'eliptica', 'eliptica', 'bicicleta', 'spinning', 'remo ergometro',
            'cardio', 'miss', 'hiit', 'trote', 'correr', 'treadmill', 'elliptical',
            'bike', 'rowing', 'caminata', 'estiramiento', 'descanso activo', 'recuperacion activa',
        ];

        foreach ($cardioKeywords as $keyword) {
            if (str_contains($name, $keyword) || str_contains($type, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build block groups for superset/circuit display. Ported from WorkoutPlayer.php.
     */
    private function buildBlockGroups(array $exercises): array
    {
        $blockGroups = [];
        $currentGroup = null;
        $groupIndex = 0;

        foreach ($exercises as $exIndex => $exercise) {
            $blockType = strtolower($exercise['bloque'] ?? $exercise['block_type'] ?? 'normal');

            if ($blockType === 'superset' || $blockType === 'circuito') {
                $groupId = $exercise['grupo_id'] ?? $exercise['group_id'] ?? $blockType.'_'.$groupIndex;

                if ($currentGroup && $currentGroup['id'] === $groupId) {
                    $currentGroup['exercises'][] = $exIndex;
                } else {
                    if ($currentGroup) {
                        $blockGroups[] = $currentGroup;
                    }
                    $currentGroup = [
                        'id' => $groupId,
                        'type' => $blockType,
                        'label' => $blockType === 'superset' ? 'SUPERSET' : 'CIRCUITO',
                        'rounds' => (int) ($exercise['rondas'] ?? $exercise['rounds'] ?? 1),
                        'exercises' => [$exIndex],
                    ];
                    $groupIndex++;
                }
            } else {
                if ($currentGroup) {
                    $blockGroups[] = $currentGroup;
                    $currentGroup = null;
                }
                $blockGroups[] = [
                    'id' => 'single_'.$exIndex,
                    'type' => 'normal',
                    'label' => null,
                    'exercises' => [$exIndex],
                ];
            }
        }

        if ($currentGroup) {
            $blockGroups[] = $currentGroup;
        }

        return $blockGroups;
    }

    /**
     * Build set data pre-filled with previous weights, then overlay existing logs.
     * Ported from WorkoutPlayer.php buildSetData() + rebuildSetDataFromLogs().
     */
    private function buildSetDataWithLogs(int $clientId, array $exercises, ?WorkoutSession $session = null): array
    {
        $exerciseNames = collect($exercises)
            ->pluck('nombre')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $lastWeights = [];
        if (! empty($exerciseNames)) {
            try {
                $lastWeights = WorkoutLog::select('workout_logs.exercise_name', 'workout_logs.weight_kg')
                    ->join(
                        DB::raw('(
                            SELECT wl2.exercise_name, MAX(wl2.id) as max_id
                            FROM workout_logs wl2
                            INNER JOIN workout_sessions ws2 ON ws2.id = wl2.session_id
                            WHERE ws2.client_id = ?
                              AND ws2.completed = 1
                              AND wl2.completed = 1
                              AND wl2.weight_kg IS NOT NULL
                            GROUP BY wl2.exercise_name
                        ) latest'),
                        function ($join) {
                            $join->on('workout_logs.exercise_name', '=', 'latest.exercise_name')
                                ->on('workout_logs.id', '=', 'latest.max_id');
                        }
                    )
                    ->whereIn('workout_logs.exercise_name', $exerciseNames)
                    ->addBinding($clientId, 'join')
                    ->pluck('workout_logs.weight_kg', 'workout_logs.exercise_name')
                    ->map(fn ($w) => $w !== null ? (float) $w : null)
                    ->toArray();
            } catch (\Throwable) {
                $lastWeights = [];
            }
        }

        $setData = [];

        foreach ($exercises as $exIndex => $exercise) {
            $seriesCount = (int) ($exercise['series'] ?? 4);
            $exerciseName = $exercise['nombre'] ?? '';
            $lastWeight = $lastWeights[$exerciseName] ?? null;
            $targetReps = $exercise['repeticiones'] ?? '8-10';

            $sets = [];
            for ($s = 1; $s <= $seriesCount; $s++) {
                $sets[$s] = [
                    'set_number' => $s,
                    'target_reps' => $targetReps,
                    'target_weight' => $lastWeight,
                    'weight' => $lastWeight,
                    'reps' => '',
                    'completed' => false,
                    'is_pr' => false,
                ];
            }

            $setData[$exIndex] = $sets;
        }

        // Overlay existing logs if resuming a session
        if ($session) {
            $logs = $session->logs()->get();
            foreach ($logs as $log) {
                $exIndex = $log->block_order;
                if (isset($setData[$exIndex][$log->set_number])) {
                    $setData[$exIndex][$log->set_number] = [
                        'set_number' => $log->set_number,
                        'target_reps' => $log->target_reps ?? $setData[$exIndex][$log->set_number]['target_reps'],
                        'target_weight' => $log->target_weight,
                        'weight' => $log->weight_kg,
                        'reps' => $log->reps,
                        'completed' => (bool) $log->completed,
                        'is_pr' => (bool) $log->is_pr,
                    ];
                }
            }
        }

        return $setData;
    }

    /**
     * Enrich exercises with last_weight / last_reps (legacy fields used by
     * existing UI) and last_session payload (used by WorkoutPlayer v2
     * LastSessionStrip), pulled from the client's previous completed sessions.
     *
     * Performance: 2 batched queries total, regardless of how many exercises
     * are in the day (no N+1):
     *   1) Latest log per exercise (legacy contract).
     *   2) Per-exercise top-set across the last 2 distinct completed sessions
     *      (used to compute delta_kg vs previous session).
     */
    private function enrichExercisesWithHistory(int $clientId, array $exercises): array
    {
        $names = collect($exercises)
            ->map(fn ($ex) => $ex['nombre'] ?? $ex['name'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($names)) {
            return $exercises;
        }

        try {
            $lastLogs = WorkoutLog::select('workout_logs.exercise_name', 'workout_logs.weight_kg', 'workout_logs.reps')
                ->join(
                    DB::raw('(
                        SELECT wl2.exercise_name, MAX(wl2.id) as max_id
                        FROM workout_logs wl2
                        INNER JOIN workout_sessions ws2 ON ws2.id = wl2.session_id
                        WHERE ws2.client_id = ?
                          AND ws2.completed = 1
                          AND wl2.completed = 1
                          AND wl2.weight_kg IS NOT NULL
                        GROUP BY wl2.exercise_name
                    ) latest'),
                    function ($join) {
                        $join->on('workout_logs.exercise_name', '=', 'latest.exercise_name')
                            ->on('workout_logs.id', '=', 'latest.max_id');
                    }
                )
                ->whereIn('workout_logs.exercise_name', $names)
                ->addBinding($clientId, 'join')
                ->get()
                ->keyBy('exercise_name');
        } catch (\Throwable) {
            return $exercises;
        }

        $lastSessionByExercise = $this->buildLastSessionMap($clientId, $names);

        foreach ($exercises as &$ex) {
            $exName = $ex['nombre'] ?? $ex['name'] ?? '';
            $lastLog = $lastLogs[$exName] ?? null;

            $ex['last_weight'] = $lastLog ? (float) $lastLog->weight_kg : null;
            $ex['last_reps'] = $lastLog ? (int) $lastLog->reps : null;
            $ex['last_session'] = $lastSessionByExercise[$exName] ?? null;
        }
        unset($ex);

        return $exercises;
    }

    /**
     * Build a map of exercise_name → last_session payload for the WorkoutPlayer
     * v2 LastSessionStrip. For each exercise, picks the heaviest top set of the
     * most recent completed session and computes delta_kg vs the previous
     * completed session (0 when there is only one session on record).
     *
     * Returns array<string, array{
     *     weight: float, reps: int, days_ago: int, delta_kg: float, session_id: int
     * }>
     */
    private function buildLastSessionMap(int $clientId, array $names): array
    {
        if (empty($names)) {
            return [];
        }

        try {
            $rows = DB::table('workout_logs as wl')
                ->join('workout_sessions as ws', 'ws.id', '=', 'wl.session_id')
                ->select([
                    'wl.exercise_name',
                    'wl.session_id',
                    'wl.weight_kg',
                    'wl.reps',
                    'ws.session_date',
                    'ws.created_at as session_created_at',
                ])
                ->where('ws.client_id', $clientId)
                ->where('ws.completed', 1)
                ->where('wl.completed', 1)
                ->whereNotNull('wl.weight_kg')
                ->whereIn('wl.exercise_name', $names)
                ->orderByDesc('ws.session_date')
                ->orderByDesc('ws.id')
                ->orderByDesc('wl.weight_kg')
                ->get();
        } catch (\Throwable) {
            return [];
        }

        // Per exercise, collapse rows into ordered sessions [latest, previous]
        // keeping the heaviest set of each session as the representative.
        $byExercise = [];

        foreach ($rows as $row) {
            $name = $row->exercise_name;
            $sessionId = (int) $row->session_id;
            $weight = (float) $row->weight_kg;
            $reps = (int) $row->reps;

            $byExercise[$name] ??= [];
            $sessions = $byExercise[$name];

            if (isset($sessions[$sessionId])) {
                if ($weight > $sessions[$sessionId]['weight']) {
                    $sessions[$sessionId]['weight'] = $weight;
                    $sessions[$sessionId]['reps'] = $reps;
                }
                $byExercise[$name] = $sessions;

                continue;
            }

            if (count($sessions) >= 2) {
                continue;
            }

            $sessions[$sessionId] = [
                'weight' => $weight,
                'reps' => $reps,
                'session_id' => $sessionId,
                'session_date' => $row->session_date,
            ];
            $byExercise[$name] = $sessions;
        }

        $map = [];

        foreach ($byExercise as $name => $sessions) {
            $sessions = array_values($sessions);
            $latest = $sessions[0] ?? null;
            if (! $latest) {
                continue;
            }

            $previous = $sessions[1] ?? null;
            $daysAgo = $latest['session_date']
                ? (int) Carbon::parse($latest['session_date'])
                    ->startOfDay()
                    ->diffInDays(now()->startOfDay())
                : 0;

            $map[$name] = [
                'weight' => round($latest['weight'], 2),
                'reps' => $latest['reps'],
                'days_ago' => max(0, $daysAgo),
                'delta_kg' => $previous
                    ? round($latest['weight'] - $previous['weight'], 2)
                    : 0.0,
                'session_id' => $latest['session_id'],
            ];
        }

        return $map;
    }

    /**
     * Update client XP and streak. Ported from WorkoutPlayer.php updateClientXp().
     *
     * XP is additive (per-session). Streak is always recomputed from training_logs
     * so every training action (toggleTrainingDay or finishWorkout) converges on
     * a single source of truth.
     */
    private function updateClientXp(int $clientId, int $xpEarned): void
    {
        $clientXp = ClientXp::firstOrCreate(
            ['client_id' => $clientId],
            [
                'xp_total' => 0,
                'level' => 1,
                'streak_days' => 0,
                'streak_last_date' => null,
                'streak_protected' => false,
            ]
        );

        $clientXp->xp_total += $xpEarned;
        $clientXp->level = max(1, (int) floor($clientXp->xp_total / 200) + 1);
        $clientXp->save();

        $this->recalculateStreak($clientId);
    }

    /**
     * Recalculate client_xp.streak_days from training_logs so the dashboard
     * streak and the calendar streak share a single source of truth.
     *
     * Streak = number of consecutive days (ending today, or yesterday if today
     * is not yet logged) with a completed training_log entry.
     */
    private function recalculateStreak(int $clientId): void
    {
        $clientXp = ClientXp::firstOrCreate(
            ['client_id' => $clientId],
            [
                'xp_total' => 0,
                'level' => 1,
                'streak_days' => 0,
                'streak_last_date' => null,
                'streak_protected' => false,
            ]
        );

        $completedDates = TrainingLog::where('client_id', $clientId)
            ->where('completed', true)
            ->orderByDesc('log_date')
            ->limit(400)
            ->pluck('log_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values();

        if ($completedDates->isEmpty()) {
            $clientXp->streak_days = 0;
            $clientXp->streak_last_date = null;
            $clientXp->save();

            return;
        }

        $todayStr = now()->format('Y-m-d');
        $yesterdayStr = now()->subDay()->format('Y-m-d');

        $cursor = match (true) {
            $completedDates->contains($todayStr) => $todayStr,
            $completedDates->contains($yesterdayStr) => $yesterdayStr,
            default => null,
        };

        if ($cursor === null) {
            if (! $clientXp->streak_protected) {
                $clientXp->streak_days = 0;
            } else {
                $clientXp->streak_protected = false;
            }
            $clientXp->save();

            return;
        }

        $streak = 0;
        $dateSet = $completedDates->flip();
        $pointer = Carbon::parse($cursor);

        while ($dateSet->has($pointer->format('Y-m-d'))) {
            $streak++;
            $pointer->subDay();
        }

        $clientXp->streak_days = $streak;
        $clientXp->streak_last_date = $cursor;
        $clientXp->save();
    }

    /**
     * Fuente única de verdad para el nombre del día de entrenamiento.
     * Extrae el nombre del JSON del plan o genera el fallback canónico "Día N".
     * Garantiza que fetchWorkout() y startWorkout() usen exactamente el mismo string.
     */
    private function resolveDayName(array $dayData, int $dayIndex): string
    {
        return $dayData['nombre'] ?? $dayData['name'] ?? $dayData['dia'] ?? 'Día '.($dayIndex + 1);
    }
}
