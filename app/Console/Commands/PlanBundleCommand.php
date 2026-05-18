<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use App\PlanEngine\Normalization\VocabularyNormalizer;
use App\Services\ComposeEngine\ComposeEngine;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\DecisionEngine;
use App\Services\LintEngine\AutoFixEngine;
use App\Services\LintEngine\Data\ComposeResult as LintComposeResult;
use App\Services\LintEngine\LintEngine;
use App\Services\PersistEngine\Data\PersistInput;
use App\Services\PersistEngine\PersistService;
use Illuminate\Console\Command;
use Throwable;

/**
 * plan:bundle — orquesta el pipeline E2E para TODAS las verticales que aplican
 * a un cliente, en una sola corrida.
 *
 * Verticales aplicables por defecto:
 *   - entrenamiento (siempre)
 *   - nutricion (siempre)
 *   - suplementacion (siempre)
 *   - habitos (siempre)
 *   - ciclo (solo si gender ∈ F + tier ∈ {elite, rise})
 *
 * Para cada vertical: SELECT → COMPOSE → LINT → AUTOFIX → re-LINT → PERSIST.
 *
 * Output: tabla resumen con composed_plans.id, methodology, violations por vertical.
 *
 * Uso:
 *   php artisan plan:bundle --goal=perdida_grasa --level=intermedio --days=5 \
 *       --gender=F --tier=elite --client-handle="Cliente X"
 *
 *   --only=entrenamiento,nutricion    → solo esas verticales
 *   --skip=ciclo                       → excluir esta vertical
 *   --no-fix                           → omitir auto-fix
 *   --export-dir=path                  → exporta JSONs individuales a directorio
 */
final class PlanBundleCommand extends Command
{
    /** Verticales canónicas siempre aplicables. */
    private const ALWAYS_APPLICABLE = ['entrenamiento', 'nutricion', 'suplementacion', 'habitos'];

    /** Vertical condicional: solo F + tier elite/rise. */
    private const FEMALE_ELITE_ONLY = 'ciclo';

    protected $signature = 'plan:bundle
                            {--ticket-json= : path a JSON exportado de PlanTicket; carga TODOS los defaults sensatos del coach_brief y profile_snapshot}
                            {--goal= : profile.goal compartido entre verticales (override del ticket)}
                            {--level= : profile.level (override del ticket)}
                            {--days= : profile.days (int) (override del ticket)}
                            {--gender= : profile.gender (override del ticket)}
                            {--age= : profile.age (int) (override del ticket)}
                            {--weight= : profile.weight_kg (float) (override del ticket)}
                            {--height= : profile.height_cm (float) (override del ticket)}
                            {--tier= : profile.tier (trial|esencial|metodo|elite|rise) (override del ticket)}
                            {--equipment=gym_completo : equipo disponible (override del ticket)}
                            {--client-handle= : identificador audit del cliente}
                            {--coach-name= : nombre coach (override del ticket)}
                            {--fecha-inicio= : YYYY-MM-DD}
                            {--only= : lista CSV de verticales a procesar (excluyente)}
                            {--skip= : lista CSV de verticales a excluir}
                            {--no-fix : omitir auto-fix}
                            {--export-dir= : graba JSONs individuales (uno por vertical)}
                            {--exclude-foods= : CSV de slugs o nombres de alimentos a excluir (override del coach_brief.plan_nutricional.alimentos_no_incluir)}
                            {--meal-protein= : CSV pares slot:keyword (override del coach_brief.plan_nutricional)}
                            {--split= : CSV pares dia:grupo (override del coach_brief.plan_entrenamiento.split)}
                            {--meals= : num_comidas (int) (override del coach_brief.plan_nutricional.num_comidas)}
                            {--meal-times= : CSV de horarios HH:MM (override del coach_brief.plan_nutricional.horarios)}
                            {--supplements= : CSV de slugs/keywords de suplementos a INCLUIR literal (override del coach_brief.plan_suplementacion); usar "none" para vaciar}
                            {--json : output JSON estructurado en lugar de tabla}
                            {--show-conflicts : muestra warnings de conflicto entre coach_brief y profile_snapshot}';

    protected $description = 'Pipeline E2E multi-vertical: genera 3-5 planes (entreno+nutri+supl+habitos+ciclo) para un cliente en una sola corrida.';

    /** Defaults extraídos del ticket-json. null si no se pasó --ticket-json. */
    private ?array $ticketDefaults = null;

    /** Lista de conflictos detectados profile_snapshot vs coach_brief vs flags CLI. */
    private array $conflicts = [];

    public function handle(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
    ): int {
        // Cargar ticket JSON si se pasó, antes de resolver verticals/profile.
        $ticketPath = $this->option('ticket-json');
        if ($ticketPath !== null) {
            if (! is_file($ticketPath)) {
                $this->error("--ticket-json no encontrado: {$ticketPath}");
                return 2;
            }
            $raw = file_get_contents($ticketPath);
            $ticket = json_decode($raw, true);
            if (! is_array($ticket)) {
                $this->error("--ticket-json no es JSON válido: {$ticketPath}");
                return 2;
            }
            $this->ticketDefaults = $this->resolveTicketDefaults($ticket);
        }

        $verticals = $this->resolveVerticals();
        if ($verticals === null) {
            return 2;
        }

        // Mostrar conflictos antes de procesar (si los hay).
        if ($this->conflicts !== [] && ($this->option('show-conflicts') || $this->ticketDefaults !== null)) {
            $this->renderConflicts();
        }

        $fechaInicio = (string) ($this->option('fecha-inicio') ?: now()->addDay()->toDateString());
        $applyFix = ! $this->option('no-fix');
        $exportDir = $this->option('export-dir');

        if ($exportDir !== null && ! is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $results = [];
        $start = microtime(true);

        foreach ($verticals as $vertical) {
            $profile = $this->buildProfile($vertical);
            $result = $this->runVerticalPipeline(
                $decision, $compose, $lint, $autoFix, $persist,
                $profile, $fechaInicio, $applyFix, $exportDir,
            );
            $results[] = $result;
        }

        $totalDuration = (microtime(true) - $start) * 1000;

        if ($this->option('json')) {
            $this->line(json_encode([
                'total_duration_ms' => round($totalDuration, 2),
                'verticals_processed' => count($results),
                'results' => $results,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->renderTable($results, $totalDuration);
        }

        // Exit 0 si todos validated, 1 si algún rejected.
        $anyRejected = array_filter($results, fn ($r) => $r['status'] === 'rejected');
        return $anyRejected === [] ? 0 : 1;
    }

    /**
     * @return string[]|null
     */
    private function resolveVerticals(): ?array
    {
        $gender = $this->option('gender');
        $tier = $this->option('tier');

        $verticals = self::ALWAYS_APPLICABLE;

        // Ciclo solo si F + Elite/Rise
        $isFemenino = $gender !== null && in_array(strtolower($gender), ['f', 'femenino', 'female', 'mujer'], true);
        $isElitePlus = in_array($tier, ['elite', 'rise'], true);
        if ($isFemenino && $isElitePlus) {
            $verticals[] = self::FEMALE_ELITE_ONLY;
        }

        // --only filtra a un subset explícito
        if ($onlyRaw = $this->option('only')) {
            $only = array_map('trim', explode(',', $onlyRaw));
            $invalid = array_diff($only, self::ALWAYS_APPLICABLE + [4 => self::FEMALE_ELITE_ONLY]);
            if ($invalid !== []) {
                $this->error('--only contiene verticales inválidas: ' . implode(', ', $invalid));
                return null;
            }
            $verticals = array_values(array_intersect($verticals, $only));
            if (in_array('ciclo', $only, true) && ! in_array('ciclo', $verticals, true)) {
                // Permitir ciclo explícito aunque no matchee F/elite
                $verticals[] = 'ciclo';
            }
        }

        // --skip excluye verticales
        if ($skipRaw = $this->option('skip')) {
            $skip = array_map('trim', explode(',', $skipRaw));
            $verticals = array_values(array_diff($verticals, $skip));
        }

        if ($verticals === []) {
            $this->error('No quedaron verticales para procesar después de --only/--skip.');
            return null;
        }

        return $verticals;
    }

    /**
     * Resuelve un valor: flag CLI > ticket default > null.
     * El flag CLI siempre gana. Si no hay flag, usa el default extraído del ticket.
     */
    private function resolveValue(string $optionName, string $ticketKey)
    {
        $fromFlag = $this->option($optionName);
        if ($fromFlag !== null && $fromFlag !== '') {
            return $fromFlag;
        }
        return $this->ticketDefaults[$ticketKey] ?? null;
    }

    private function buildProfile(string $vertical): ClientProfile
    {
        // Normalizar vocabulario externo → keys canónicas del motor.
        // El JSON del ticket puede traer "perder_grasa", "femenino", "avanzado"
        // pero decision_rules y methodologies usan "perdida_grasa", "F", "avanzado".
        $goal   = VocabularyNormalizer::goal($this->resolveValue('goal', 'goal'));
        $gender = VocabularyNormalizer::gender($this->resolveValue('gender', 'gender'));
        $level  = VocabularyNormalizer::level($this->resolveValue('level', 'level'));

        $daysRaw   = $this->resolveValue('days', 'days');
        $ageRaw    = $this->resolveValue('age', 'age');
        $weightRaw = $this->resolveValue('weight', 'weight');
        $heightRaw = $this->resolveValue('height', 'height');
        $tier      = $this->resolveValue('tier', 'tier');
        $equipment = $this->resolveValue('equipment', 'equipment') ?: 'gym_completo';

        return new ClientProfile(
            vertical: $vertical,
            goal: $goal,
            level: $level,
            days: $daysRaw !== null ? (int) $daysRaw : null,
            gender: $gender,
            equipment: $equipment,
            age: $ageRaw !== null ? (int) $ageRaw : null,
            weightKg: $weightRaw !== null ? (float) $weightRaw : null,
            heightCm: $heightRaw !== null ? (float) $heightRaw : null,
            tier: $tier,
            preferences: $this->collectPreferences(),
        );
    }

    /**
     * Construye el array preferences combinando flags CLI + defaults del ticket.
     * Flags CLI ganan sobre ticket. Si no hay flag y no hay ticket → key ausente.
     */
    private function collectPreferences(): array
    {
        $prefs = [];

        // Excluded foods: flag CLI > ticket
        $excludeRaw = $this->option('exclude-foods') ?? ($this->ticketDefaults['excluded_foods_csv'] ?? null);
        if ($excludeRaw) {
            $prefs['excluded_foods'] = array_values(array_filter(
                array_map('trim', explode(',', (string) $excludeRaw)),
            ));
        }

        // Meal protein: flag CLI > ticket
        $proteinRaw = $this->option('meal-protein') ?? ($this->ticketDefaults['meal_protein_csv'] ?? null);
        if ($proteinRaw) {
            $prefs['meal_protein'] = $this->parseCsvPairs((string) $proteinRaw);
        }

        // Split: flag CLI > ticket coach_brief.split
        $splitRaw = $this->option('split') ?? ($this->ticketDefaults['split_csv'] ?? null);
        if ($splitRaw) {
            $prefs['split_override'] = $this->parseCsvPairs((string) $splitRaw);
        }

        // Meals (num_comidas): flag CLI > ticket coach_brief.num_comidas
        $mealsRaw = $this->option('meals') ?? ($this->ticketDefaults['num_meals'] ?? null);
        if ($mealsRaw !== null && $mealsRaw !== '') {
            $prefs['num_meals'] = (int) $mealsRaw;
        }

        // Meal times: flag CLI > ticket coach_brief.horarios
        $mealTimesRaw = $this->option('meal-times') ?? ($this->ticketDefaults['meal_times_csv'] ?? null);
        if ($mealTimesRaw) {
            $prefs['meal_times'] = array_values(array_filter(
                array_map('trim', explode(',', (string) $mealTimesRaw)),
            ));
        }

        // Supplements: flag CLI > ticket coach_brief.plan_suplementacion.suplementos[]
        // "none" → vacío explícito (motor genera stack desde cero)
        $suppsRaw = $this->option('supplements');
        if ($suppsRaw === 'none') {
            $prefs['supplements_override'] = [];
        } elseif ($suppsRaw !== null && $suppsRaw !== '') {
            $prefs['supplements_override'] = array_values(array_filter(
                array_map('trim', explode(',', (string) $suppsRaw)),
            ));
        } elseif (isset($this->ticketDefaults['supplements'])) {
            $prefs['supplements_override'] = $this->ticketDefaults['supplements'];
        }

        return $prefs;
    }

    /**
     * Parsea CSV "k:v,k2:v2" → ['k' => 'v', 'k2' => 'v2'].
     */
    private function parseCsvPairs(string $raw): array
    {
        $out = [];
        foreach (explode(',', $raw) as $pair) {
            $pair = trim($pair);
            if ($pair === '' || ! str_contains($pair, ':')) {
                continue;
            }
            [$k, $v] = array_map('trim', explode(':', $pair, 2));
            if ($k === '' || $v === '') {
                continue;
            }
            $out[strtolower($k)] = $v;
        }
        return $out;
    }

    private function runVerticalPipeline(
        DecisionEngine $decision,
        ComposeEngine $compose,
        LintEngine $lint,
        AutoFixEngine $autoFix,
        PersistService $persist,
        ClientProfile $profile,
        string $fechaInicio,
        bool $applyFix,
        ?string $exportDir,
    ): array {
        $vertical = $profile->vertical;
        $start = microtime(true);

        try {
            // SELECT
            $decisionResult = $decision->decide($profile);
            $recs = $decisionResult->byVertical[$vertical] ?? [];
            if ($recs === []) {
                return $this->failureResult($vertical, 'no decision rule matched');
            }
            $methodologySlug = $recs[0]->methodologySlug;

            // COMPOSE
            $clientHandle = $this->option('client-handle');
            // Nombre real del cliente: --client-handle se sigue usando como identificador audit,
            // pero para personalizar notas usamos el nombre real del ticket (si disponible).
            $clientNameReal = $this->ticketDefaults['client_name'] ?? $clientHandle;
            $coachName = $this->option('coach-name') ?? $this->ticketDefaults['coach_name'] ?? null;
            $equipment = array_map('trim', explode(',', (string) ($this->option('equipment') ?: 'gym_completo')));

            $composeResult = $compose->composeForMethodology(
                $profile, $methodologySlug, $fechaInicio,
                $clientNameReal, $coachName, $equipment,
            );

            // LINT pre
            $lintBefore = $lint->lint($composeResult->planJson, $vertical);

            // AUTOFIX (si aplica + hay violations con auto-fix disponible)
            $fixesApplied = [];
            $planFinal = $composeResult->planJson;
            $lintAfter = $lintBefore;
            if ($applyFix && count($lintBefore->violations) > 0) {
                $fixResult = $autoFix->applyAll($composeResult->planJson, $lintBefore->violations);
                $fixesApplied = $fixResult->appliedFixes;
                $planFinal = $fixResult->fixedPlan;
                $lintAfter = $lint->lint($planFinal, $vertical);
            }

            // Export JSON si exportDir
            $exportPath = null;
            if ($exportDir !== null) {
                $exportPath = rtrim($exportDir, '/\\') . DIRECTORY_SEPARATOR . "plan_{$vertical}.json";
                file_put_contents($exportPath, json_encode($planFinal, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }

            // PERSIST
            $composeResultPost = new \App\Services\ComposeEngine\Data\ComposeResult(
                planJson: $planFinal,
                warnings: $composeResult->warnings,
                durationMs: $composeResult->durationMs,
            );
            $audit = $persist->persist(new PersistInput(
                profile: $profile,
                methodologySlug: $methodologySlug,
                composeResult: $composeResultPost,
                lintBefore: $lintBefore,
                lintAfter: $lintAfter,
                fixesApplied: $fixesApplied,
                clientHandle: $clientHandle,
                notes: 'bundle:' . ($this->option('client-handle') ?: 'no-handle'),
                exportPath: $exportPath,
            ));

            return [
                'vertical' => $vertical,
                'methodology_slug' => $methodologySlug,
                'composed_id' => $audit->id,
                'status' => $audit->status,
                'errors' => count($lintAfter->errors()),
                'warnings' => count($lintAfter->warnings()),
                'fixes_applied' => count($fixesApplied),
                'export_path' => $exportPath,
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (Throwable $e) {
            return $this->failureResult($vertical, $e->getMessage());
        }
    }

    private function failureResult(string $vertical, string $reason): array
    {
        return [
            'vertical' => $vertical,
            'methodology_slug' => null,
            'composed_id' => null,
            'status' => 'rejected',
            'errors' => 1,
            'warnings' => 0,
            'fixes_applied' => 0,
            'export_path' => null,
            'duration_ms' => 0,
            'failure_reason' => $reason,
        ];
    }

    private function renderTable(array $results, float $totalDuration): void
    {
        $this->info('═══ Bundle Pipeline E2E ═══');
        $this->line(sprintf('Cliente: %s · Duración total: %.2f ms', $this->option('client-handle') ?? '?', $totalDuration));
        $this->newLine();

        $rows = [];
        foreach ($results as $r) {
            $statusIcon = match ($r['status']) {
                'validated', 'exported' => '✓',
                'rejected' => '✗',
                default => '~',
            };
            $rows[] = [
                $statusIcon . ' ' . $r['vertical'],
                $r['methodology_slug'] ?? '—',
                $r['composed_id'] ?? '—',
                $r['errors'] . '/' . $r['warnings'],
                $r['fixes_applied'],
                $r['duration_ms'] . ' ms',
                $r['status'],
            ];
        }

        $this->table(
            ['Vertical', 'Methodology', 'audit_id', 'err/warn', 'fixes', 'duración', 'status'],
            $rows,
        );

        $validated = count(array_filter($results, fn ($r) => $r['status'] === 'validated' || $r['status'] === 'exported'));
        $rejected = count(array_filter($results, fn ($r) => $r['status'] === 'rejected'));

        $this->newLine();
        $this->info("Resumen: $validated validated · $rejected rejected · " . count($results) . " totales");

        $composedIds = array_filter(array_column($results, 'composed_id'));
        if ($composedIds !== []) {
            $this->newLine();
            $this->line('Para exportar a producción (UN script con todos los inserts):');
            $this->line('   php artisan plan:export-bundle-prod-script --composed-ids=' . implode(',', $composedIds) . ' --client-id=<X> --coach-id=<Y>');
        }
    }

    /**
     * Extrae defaults sensatos del JSON del ticket (output de PlanTicketExportService).
     * Detecta conflictos entre coach_brief y profile_snapshot y los registra en $this->conflicts.
     *
     * Política: por defecto preferimos coach_brief (es el dato más fresco editado por el coach
     * en el wizard). Flags CLI sobreescriben todo. Los conflictos se reportan al usuario para
     * decisión consciente (no son fatales).
     */
    private function resolveTicketDefaults(array $ticket): array
    {
        $profile = $ticket['profile_snapshot'] ?? [];
        $brief = $ticket['coach_brief'] ?? [];
        $datos = $brief['datos_generales'] ?? [];
        $entreno = $brief['plan_entrenamiento'] ?? [];
        $nutri = $brief['plan_nutricional'] ?? [];
        $supl = $brief['plan_suplementacion'] ?? [];
        $tier = $ticket['plan_tier_expectations'] ?? [];

        $defaults = [];

        // ─── Edad: coach > profile ───
        $coachAge = isset($datos['edad']) ? (int) $datos['edad'] : null;
        $profileAge = isset($profile['edad']) ? (int) $profile['edad'] : null;
        if ($coachAge !== null && $profileAge !== null && $coachAge !== $profileAge) {
            $this->conflicts[] = "edad: coach={$coachAge} vs profile={$profileAge} → usando {$coachAge} (coach)";
        }
        $defaults['age'] = $coachAge ?? $profileAge;

        // ─── Peso: coach > profile ───
        $coachWeight = isset($datos['peso']) ? (float) $datos['peso'] : null;
        $profileWeight = isset($profile['peso_actual_kg']) ? (float) $profile['peso_actual_kg'] : null;
        if ($coachWeight !== null && $profileWeight !== null && abs($coachWeight - $profileWeight) > 0.1) {
            $this->conflicts[] = "peso: coach={$coachWeight}kg vs profile={$profileWeight}kg → usando {$coachWeight}kg (coach)";
        }
        $defaults['weight'] = $coachWeight ?? $profileWeight;

        // ─── Estatura: coach > profile ───
        $coachHeight = isset($datos['estatura']) ? (float) $datos['estatura'] : null;
        $profileHeight = isset($profile['estatura_cm']) ? (float) $profile['estatura_cm'] : null;
        if ($coachHeight !== null && $profileHeight !== null && abs($coachHeight - $profileHeight) > 0.1) {
            $this->conflicts[] = "estatura: coach={$coachHeight}cm vs profile={$profileHeight}cm → usando {$coachHeight}cm (coach)";
        }
        $defaults['height'] = $coachHeight ?? $profileHeight;

        // ─── Género: coach > profile ───
        $coachGender = $datos['genero'] ?? null;
        $profileGender = $profile['genero'] ?? null;
        if ($coachGender !== null && $profileGender !== null
            && VocabularyNormalizer::gender($coachGender) !== VocabularyNormalizer::gender($profileGender)) {
            $this->conflicts[] = "genero: coach='{$coachGender}' vs profile='{$profileGender}' → usando '{$coachGender}' (coach)";
        }
        $defaults['gender'] = $coachGender ?? $profileGender;

        // ─── Goal/objetivo ───
        // coach_brief.datos_generales.objetivo es texto libre ("Disminuir porcentaje de grasa")
        // → normalizer lo mapea. profile_snapshot.objetivo_general suele ser slug.
        $coachGoal = $datos['objetivo'] ?? null;
        $profileGoal = $profile['objetivo_general'] ?? null;
        $normalizedCoach = $coachGoal ? VocabularyNormalizer::goal($coachGoal) : null;
        $normalizedProfile = $profileGoal ? VocabularyNormalizer::goal($profileGoal) : null;
        if ($normalizedCoach && $normalizedProfile && $normalizedCoach !== $normalizedProfile) {
            $this->conflicts[] = "objetivo: coach='{$coachGoal}' (→{$normalizedCoach}) vs profile='{$profileGoal}' (→{$normalizedProfile}) → usando '{$normalizedCoach}'";
        }
        $defaults['goal'] = $coachGoal ?? $profileGoal;

        // ─── Nivel ───
        $defaults['level'] = $entreno['nivel'] ?? $profile['nivel_actividad'] ?? null;

        // ─── Días entrenamiento ───
        $defaults['days'] = $entreno['dias_semana'] ?? null;

        // ─── Tier (plan contratado) ───
        $defaults['tier'] = $ticket['client']['plan_contratado'] ?? $ticket['ticket']['plan_type'] ?? null;

        // ─── Equipment / lugar ───
        $defaults['equipment'] = $entreno['lugar'] === 'gym' ? 'gym_completo' : ($entreno['lugar'] ?? null);

        // ─── Nombres reales del cliente y coach (no client-handle) ───
        $defaults['client_name'] = $ticket['client']['name'] ?? null;
        $defaults['coach_name'] = $ticket['coach']['name'] ?? null;

        // ─── Split del coach (coach_brief.plan_entrenamiento.split) ───
        // Convierte {"lunes": {"grupos": ["gluteos"]}, "martes": {"grupos": ["espalda", "triceps"]}, ...}
        // a CSV "lunes:gluteos,martes:espalda+triceps,..."
        $coachSplit = $entreno['split'] ?? null;
        if (is_array($coachSplit)) {
            $pairs = [];
            foreach ($coachSplit as $day => $config) {
                $grupos = $config['grupos'] ?? [];
                if (! is_array($grupos) || $grupos === []) {
                    continue;
                }
                // Filtrar "descanso" — el motor no necesita días de descanso explícitos
                $grupos = array_filter($grupos, fn ($g) => mb_strtolower((string) $g) !== 'descanso');
                if ($grupos === []) {
                    continue;
                }
                $pairs[] = mb_strtolower($day) . ':' . implode('+', $grupos);
            }
            if ($pairs !== []) {
                $defaults['split_csv'] = implode(',', $pairs);
            }
        }

        // ─── Num comidas + horarios (coach_brief.plan_nutricional) ───
        if (isset($nutri['num_comidas'])) {
            $defaults['num_meals'] = (int) $nutri['num_comidas'];
        }
        $horarios = $nutri['horarios'] ?? null;
        if (is_array($horarios) && $horarios !== []) {
            // Normalizar "5 am" → "05:00", "10 am" → "10:00", "1 pm" → "13:00", "4 pm" → "16:00"
            $normalized = array_map([$this, 'normalizeMealTime'], $horarios);
            $normalized = array_values(array_filter($normalized));
            if ($normalized !== []) {
                $defaults['meal_times_csv'] = implode(',', $normalized);
            }
        }

        // ─── Excluded foods (coach_brief.plan_nutricional.alimentos_no_incluir) ───
        $excluded = $nutri['alimentos_no_incluir'] ?? null;
        if (is_string($excluded) && trim($excluded) !== '') {
            $defaults['excluded_foods_csv'] = $excluded;
        }

        // ─── Suplementos prescritos por el coach ───
        $coachSupps = $supl['suplementos'] ?? null;
        if (is_array($coachSupps) && $coachSupps !== []) {
            $defaults['supplements'] = array_values(array_filter(array_map(
                fn ($s) => is_array($s) ? ($s['nombre'] ?? null) : null,
                $coachSupps,
            )));
        }

        return $defaults;
    }

    /**
     * Normaliza horarios del coach ("5 am", "1 pm") a "HH:MM" 24h.
     * Si no parsea, devuelve null para que se omita.
     */
    private function normalizeMealTime(string $raw): ?string
    {
        $raw = mb_strtolower(trim($raw));
        // Ya formato HH:MM
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $raw, $m)) {
            $h = (int) $m[1];
            return sprintf('%02d:%02d', $h, (int) $m[2]);
        }
        // Formato "5 am", "10 am", "1 pm"
        if (preg_match('/^(\d{1,2})\s*(am|pm|a\.m\.|p\.m\.)/u', $raw, $m)) {
            $h = (int) $m[1];
            $ampm = str_starts_with($m[2], 'p') ? 'pm' : 'am';
            if ($ampm === 'pm' && $h < 12) $h += 12;
            if ($ampm === 'am' && $h === 12) $h = 0;
            return sprintf('%02d:00', $h);
        }
        return null;
    }

    private function renderConflicts(): void
    {
        if ($this->conflicts === []) {
            return;
        }
        $this->warn('═══ Conflictos detectados entre coach_brief y profile_snapshot ═══');
        foreach ($this->conflicts as $c) {
            $this->line('  • ' . $c);
        }
        $this->line('Si querés override, usá flags CLI explícitos (--age, --weight, --gender, etc.)');
        $this->newLine();
    }
}
