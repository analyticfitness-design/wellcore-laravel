# 04 — Arquitectura de las 6 stages

> Documento de diseño. Implementación queda para Sprint 1+.

## TL;DR

Las 6 stages del motor v2 son **funciones puras tipadas con DTOs readonly** orquestadas por una clase `PlanEngineOrchestrator` que es la única dueña de los handles externos (PDO a `wellcore_kb` local, PDO a `wellcore_fitness` prod, Anthropic API client, Chrome MCP session). Cada stage recibe un DTO inmutable de entrada y devuelve un DTO inmutable de salida — si necesita transformar, devuelve un objeto nuevo, nunca muta. El orchestrator envuelve toda la pipeline en `try/finally` que invoca `safeCleanup()` por cada recurso, igual al patrón HF (`render/cleanup.ts:19-31`). Los errores se clasifican en 3 categorías: **fatal** (aborta y propaga), **gate** (aborta sin propagar — la stage decide que el plan no debe avanzar, ej. linter con errors), **warning** (loguea y continúa). La concurrencia se resuelve con `flock()` sobre `storage/locks/plan_engine.lock` — un solo run por laptop a la vez. Observabilidad: cada run escribe una fila en `wellcore_kb.plan_engine_runs` con timings, tokens, errores y screenshot paths. El payload de error sigue el shape de `buildRenderErrorDetails()` de HF (`cleanup.ts:76-99`).

---

## 1. Vista de pájaro

```
                ┌─────────────────────────────────────────────┐
                │   PlanEngineOrchestrator (laptop Daniel)    │
                │                                              │
                │   ┌──── try ──────────────────────────────┐ │
                │   │                                        │ │
   IntakeInput  │   │  ┌─ 1. INTAKE ──────► IntakeDTO       │ │
       ─────────┼───┼─►│                                    │ │
                │   │  ▼                                     │ │
                │   │  ┌─ 2. SELECT ──────► EligibleDTO     │ │
                │   │  │                                    │ │
                │   │  ▼                                     │ │
                │   │  ┌─ 3. COMPOSE ─────► PlanJsonDTO     │ │
                │   │  │   (LLM aquí)                       │ │
                │   │  ▼                                     │ │
                │   │  ┌─ 4. VALIDATE ────► LintResultDTO   │ │
                │   │  │   (linter — GATE)                  │ │
                │   │  ▼                                     │ │
                │   │  ┌─ 5. PERSIST ─────► PersistResultDTO│ │
                │   │  │   (INSERT a prod)                  │ │
                │   │  ▼                                     │ │
                │   │  ┌─ 6. VERIFY ──────► VerifyResultDTO │ │
                │   │     (Chrome MCP — GATE blando)        │ │
                │   │                                        │ │
                │   └── finally: cleanup PDO/LLM/Chrome ────┘ │
                │                                              │
                └──────────────────────────────────────────────┘
                                       │
                                       ▼
                          plan_engine_runs row (audit)
```

**Propiedades del diseño**:

- Cada stage es función pura → testeable sin levantar Chrome ni LLM.
- DTO entre stages es inmutable → cero estado compartido.
- Orchestrator dueño de handles → cleanup garantizado en `finally`.
- Linter es GATE → si falla, no se INSERTA. Si Verify falla, ya está INSERTADO pero se loguea.
- Concurrencia: file lock → un run por laptop a la vez.

---

## 2. Convenciones de tipos

PHP 8.4 readonly classes (no construct-promotion + readonly props, una sola unidad atómica).

```php
namespace App\PlanEngine\Dto;

readonly class IntakeDto {
    public function __construct(
        public int $clientId,
        public string $vertical,          // entrenamiento | nutricion | suplementacion | habitos | ciclo
        public string $clientName,
        public string $gender,            // M | F
        public int $age,
        public ?float $weightKg,
        public ?float $heightCm,
        public string $goal,              // hipertrofia | perdida_grasa | recomposicion | mantenimiento | performance
        public string $level,             // principiante | intermedio | avanzado
        public int $daysAvailable,        // 3..7
        public string $place,             // gym | casa | hibrido
        public array $equipment,          // ["barra","mancuernas",...] o ["bodyweight"]
        public ?string $injuries,
        public ?string $dietaryRestrictions,
        public ?int $coachId,             // default Anderson Ardila admin_id=7
        public string $validFrom,         // ISO date
        public ?int $durationWeeks,       // default según plan tier
    ) {}
}
```

**Por qué `readonly class` y no `readonly $prop`**: PHP 8.2+ permite `readonly` por property, PHP 8.4 permite `readonly class` que hace TODAS las props readonly y bloquea inheritance no-readonly. Más seguro y más conciso.

**Por qué no Spatie Data**: lo evaluamos; Spatie agrega serialization helpers que no necesitamos en MVP (el JSON canónico ya tiene su propio shape). Más simple es mejor.

---

## 3. Stage 1 — INTAKE

**Propósito**: parsear input (CLI args, formulario, o slash command) → DTO validado. Verifica que el cliente existe en producción y carga datos base del perfil.

| Aspecto | Detalle |
|---------|---------|
| Input | `IntakeInput` raw (array asociativo desde CLI/form) |
| Output | `IntakeDto` |
| Determinístico | 100% |
| LLM | No |
| DB local | Ninguna |
| DB prod (read) | `clients` + `clients.profile` para precargar peso/altura/objetivo si no vino en el input |
| Errores fatal | `ClientNotFoundError` (client_id no existe) · `InvalidVerticalError` (vertical ∉ enum) · `InvalidEnumValueError` (gender/level/goal/place) |
| Errores gate | `IntakeIncompleteError` con lista de campos faltantes |
| Test | Unit puros con array literales — sin DB |

**Implementation outline**:

```php
namespace App\PlanEngine\Stages;

final class IntakeStage {
    public function __construct(
        private ClientRepository $clients,
    ) {}

    public function run(array $raw): IntakeDto {
        $clientId = $raw['client_id'] ?? throw new IntakeIncompleteError(['client_id']);
        $client = $this->clients->findActive($clientId)
            ?? throw new ClientNotFoundError($clientId);

        // Hidrata defaults desde el perfil
        $weight = $raw['weight_kg'] ?? $client->profile?->peso;
        $height = $raw['height_cm'] ?? $client->profile?->altura;

        $missing = $this->checkRequired($raw, $client);
        if (count($missing) > 0) {
            throw new IntakeIncompleteError($missing);
        }

        return new IntakeDto(...);
    }
}
```

---

## 4. Stage 2 — SELECT

**Propósito**: dado el `IntakeDto`, devolver la lista de metodologías elegibles rankeadas por score, SIN decidir cuál usar (eso lo hace COMPOSE).

| Aspecto | Detalle |
|---------|---------|
| Input | `IntakeDto` |
| Output | `EligibleMethodologiesDto { methodologies: array<MethodologyCandidate>, decisionRulesFired: array<DecisionRuleRef> }` |
| Determinístico | 100% (queries SQL) |
| LLM | No |
| DB local (read) | `methodologies` + `methodology_rules` + `decision_rules` |
| Errores fatal | `KbConnectionError` |
| Errores gate | `NoEligibleMethodologiesError` (todas filtradas) |
| Test | Unit con DB local de test poblada con seed mínimo |

**Algoritmo**:

1. `SELECT * FROM methodologies WHERE vertical = :v AND status = 'active'`
2. Para cada candidata, aplicar todas sus `methodology_rules`:
   - `hard_filter` → si matchea condición negativa, descartar
   - `soft_filter` → restar `weight` del score
   - `preference` → sumar `weight`
3. Cargar `decision_rules` cuyo `when_json` matchea con `IntakeDto` y boost confidence × peso a la `then_methodology_id`
4. Ordenar candidatas por score descendente
5. Si lista vacía → `NoEligibleMethodologiesError`

**Candidate shape**:

```php
readonly class MethodologyCandidate {
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public float $score,
        public array $rulesApplied,    // ["hard_filter:days_min_5_ok", "preference:matches_goal_hipertrofia"]
        public ?int $suggestedTemplateId, // si hay plan_templates_local que matchee
    ) {}
}
```

**Por qué SELECT NO decide la metodología final**: separación de responsabilidades. SELECT solo filtra y rankea. COMPOSE puede elegir top-1 (default determinístico) o usar el LLM con la lista como tool input (creativo). Si en el futuro queremos A/B testing entre top-1 vs LLM-pick, no hay que reescribir SELECT.

---

## 5. Stage 3 — COMPOSE

**Propósito**: armar el JSON canónico del plan. **Híbrido**: spine determinístico + topping LLM.

| Aspecto | Detalle |
|---------|---------|
| Input | `IntakeDto` + `EligibleMethodologiesDto` + `ComposeOptions` |
| Output | `PlanJsonDto { contentJson: array, methodologyChosenId: int, tokensUsed: TokenUsage, ragChunksUsed: array }` |
| Determinístico | Spine (50%) — periodización, split, frecuencia, defaults de ejercicios |
| LLM | Topping (50%) — notas_coach, tips, variaciones, casos especiales, ajustes por lesión |
| DB local (read) | `methodologies` (full row de la elegida) · `plan_templates_local` (starting point) · `exercise_metadata` (para sustituciones por equipo/lesión) · `principles` (para inyectar en notas) · `corpus_embeddings` (Sprint 3+) |
| Errores fatal | `LlmTimeoutError` · `LlmAuthError` · `TemplateNotFoundError` |
| Errores gate | `LlmOutputUnparseableError` (LLM devolvió texto que no parsea a JSON) — con retry automático 1 vez antes de propagar |
| Test | Golden tests sobre JSONs generados (snapshot tests); mock del LLM con respuestas fijas |

**Algoritmo (boceto)**:

```php
final class ComposeStage {
    public function run(IntakeDto $intake, EligibleMethodologiesDto $eligible, ComposeOptions $opts): PlanJsonDto {
        // 1. Elegir metodología (top-1 por default, configurable)
        $methodology = $this->pickMethodology($eligible, $opts);

        // 2. Cargar template starting point si existe
        $template = $methodology->suggestedTemplateId
            ? $this->templates->find($methodology->suggestedTemplateId)
            : $this->buildEmptySpine($methodology);

        // 3. Spine determinístico: periodización + split + frecuencia + ejercicios base
        $spine = $this->buildSpine($intake, $methodology, $template);

        // 4. RAG retrieval: principios + casos similares
        $ragContext = $this->rag->retrieve($intake, $methodology, limit: 5);

        // 5. LLM topping con tool use (Anthropic Tool Use API)
        $topping = $this->llm->generateTopping(
            spine: $spine,
            intake: $intake,
            methodology: $methodology,
            ragContext: $ragContext,
            tools: $this->loadToolDefinitions(),  // generate_coach_note, suggest_tip, etc.
            promptCacheKey: "plan_engine_v1_{$methodology->slug}",
        );

        // 6. Merge spine + topping en JSON canónico (16a/b/c/d)
        $contentJson = $this->merge($spine, $topping);

        return new PlanJsonDto(...);
    }
}
```

**Per qué Tool Use y no prompt-engineering puro**: con tool use, el LLM tiene functions tipadas (`generate_coach_note(week_number, fase, exercises)`) que devuelven structured output. Imposible que aluciné "weeks" en inglés o se invente un campo nuevo — el shape lo definimos nosotros. Reduce drasticamente bugs estilo caso Cristian.

**Prompt caching**: Anthropic permite cache de prompts >1024 tokens TTL 5min. El system prompt + el `methodology.description` + las `principles` aplicables son estables por metodología → cacheable. Esperamos hit rate ~80% en sesiones consecutivas, lo cual reduce tokens ~70% — el target del prompt original de Daniel.

**RAG (Sprint 3+)**: cosine similarity sobre `corpus_embeddings` en PHP — ver doc 03 §3.8. Para MVP, COMPOSE puede correr sin RAG (usa solo `methodology.description` + `principles` directos).

---

## 6. Stage 4 — VALIDATE (linter — GATE)

**Propósito**: correr todas las `lint_rules` activas contra el `PlanJsonDto` antes de tocar producción. Si hay `severity=error`, **el plan NO avanza**.

| Aspecto | Detalle |
|---------|---------|
| Input | `PlanJsonDto` |
| Output | `LintResultDto { ok: bool, errorCount: int, warningCount: int, infoCount: int, findings: array<LintFinding> }` |
| Determinístico | 100% (excepto `check_type=llm_review` que es opcional para Sprint 6+) |
| LLM | Solo si una rule específica lo pide (e.g. "¿la voz del coach es WellCore?") |
| DB local (read) | `lint_rules` + `exercise_metadata` (para gif_url validation) |
| DB externa | HEAD checks a `raw.githubusercontent.com/.../GIFs/*.gif` (con timeout 8s, igual a HF `hyperframeLinter.ts:106`) |
| Errores fatal | `LintEngineError` (la rule misma crasheó — bug en la rule, no en el plan) |
| Errores gate | NO — los lint findings NO son exceptions; el gate es `result.ok === (errorCount === 0)` que el orchestrator checkea |
| Test | El linter corre contra los 5-10 JSONs viejos (Cristian incluido) — debe detectar todos los problemas históricos |

**Lint Finding shape** (idéntico al de HF `lint/types.ts:3-12`):

```php
readonly class LintFinding {
    public function __construct(
        public string $code,           // "missing_phase_field", "gif_alias_not_in_catalog"
        public string $severity,       // "error" | "warning" | "info"
        public string $message,
        public ?string $jsonPath,      // "$.semanas[2].dias[0].ejercicios[3].gif_url"
        public ?string $fixHint,       // ★ accionable
        public bool $autoFixApplied,   // si la rule aplicó autofix antes de generar el finding
    ) {}
}
```

**Categorías de rules** (doc 06 las cataloga en detalle):

| Categoría | check_type | Ejemplo |
|-----------|------------|---------|
| Schema | `schema` | `semanas[].fase` REQUIRED |
| Heurística semántica | `heuristic` | No más de 60% ejercicios con `series=3, reps="12"` (anti-monotonía) |
| External | `external_head` | HEAD check de cada `gif_url` (resuelve caso Cristian error #2) |
| SQL cross-check | `sql` | `exercise_metadata.alias` debe existir en `wellcore_kb` |
| LLM review (Sprint 6+) | `llm_review` | Voz del coach pasa filtro WellCore-tone |

**Dedup**: igual al de HF (`hyperframeLinter.ts:32-43`): composite key `code|severity|jsonPath|message`.

**Por qué el linter NO autofix por default**: autofix puede esconder bugs. La policy es: rule puede aplicar autofix solo si `lint_rules.auto_fix_available = true` Y `enabled = true`. El finding queda en el output con `autoFixApplied = true` para auditoría.

---

## 7. Stage 5 — PERSIST

**Propósito**: INSERT a `assigned_plans` de producción, marca planes previos como `active=false`, invalida caches del cliente.

| Aspecto | Detalle |
|---------|---------|
| Input | `PlanJsonDto` + `LintResultDto` (validar `result.ok === true` antes de entrar) |
| Output | `PersistResultDto { assignedPlanId: int, version: int, deactivatedPreviousIds: array<int>, cacheKeysInvalidated: array<string> }` |
| Determinístico | 100% (PDO INSERT) |
| LLM | No |
| DB local (write) | `plan_engine_runs` (UPDATE con resultado) |
| DB prod (write) | `assigned_plans` (UPDATE para deactivate + INSERT del nuevo) en transacción |
| DB prod (write opcional) | Redis `DEL` de caches del cliente (`client_plan_v3_{id}`, `wp:plan:{id}`, `dashboard:{id}`) — best effort |
| Errores fatal | `PdoError` (transacción rollback) · `PreInsertGateError` (si lint result.ok=false — defense in depth) |
| Errores warning | `CacheInvalidationFailure` (no crítico, loguea) |
| Test | Integration tests contra DB de test que replica el schema de prod (la-06-database lo prepara en Sprint 1) |

**Transacción atómica**:

```php
final class PersistStage {
    public function run(PlanJsonDto $plan, LintResultDto $lint, IntakeDto $intake): PersistResultDto {
        if (!$lint->ok) {
            throw new PreInsertGateError("Linter no aprobó — abortar PERSIST");
        }

        $pdo = $this->prodDb;
        $pdo->beginTransaction();
        try {
            // 1. Lookup max version anterior del cliente para este plan_type
            $stmt = $pdo->prepare("SELECT MAX(version) FROM assigned_plans WHERE client_id=? AND plan_type=?");
            $stmt->execute([$intake->clientId, $intake->vertical]);
            $latestVersion = (int) ($stmt->fetchColumn() ?: 0);

            // 2. Deactivate previos
            $stmt = $pdo->prepare("UPDATE assigned_plans SET active=0 WHERE client_id=? AND plan_type=? AND active=1");
            $stmt->execute([$intake->clientId, $intake->vertical]);
            $deactivated = $this->fetchDeactivatedIds($pdo, $intake);

            // 3. INSERT del nuevo (version+1, ai_generation_id=NULL)
            $stmt = $pdo->prepare("
                INSERT INTO assigned_plans
                    (client_id, plan_type, content, version, assigned_by, valid_from, expires_at, active, created_at, ai_generation_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NULL)
            ");
            $expiresAt = date('Y-m-d', strtotime($intake->validFrom . ' +' . ($intake->durationWeeks ?? 4) * 7 . ' days'));
            $stmt->execute([
                $intake->clientId, $intake->vertical, json_encode($plan->contentJson),
                $latestVersion + 1, $intake->coachId ?? 7, $intake->validFrom, $expiresAt,
            ]);
            $assignedPlanId = (int) $pdo->lastInsertId();

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw new PdoError(previous: $e);
        }

        // 4. Cache invalidation (best effort, fuera de transacción)
        $invalidated = $this->invalidateCaches($intake->clientId);

        return new PersistResultDto($assignedPlanId, $latestVersion + 1, $deactivated, $invalidated);
    }
}
```

**Por qué `ai_generation_id=NULL`**: decisión 2026-05-16 (doc 02 §7, doc 03 §7). El motor v2 NO es un AI generator del scope deprecated.

### 7.1 Idempotencia (decisión 2026-05-16)

PERSIST genera una `idempotency_key` antes del INSERT:

```php
$idempotencyKey = hash('sha256', implode('|', [
    $intake->clientId,
    $intake->vertical,
    $intake->validFrom,
    $run->id,           // ID de plan_engine_runs
]));

// Verifica si ya existe
$existing = $pdo->prepare("SELECT id, version FROM assigned_plans WHERE idempotency_key = ?");
$existing->execute([$idempotencyKey]);
$found = $existing->fetch(PDO::FETCH_ASSOC);

if ($found) {
    // Idempotent return — el job ya corrió, devuelve el plan existente
    return new PersistResultDto(
        assignedPlanId: (int) $found['id'],
        version: (int) $found['version'],
        deactivatedPreviousIds: [],
        cacheKeysInvalidated: [],
        idempotentHit: true,  // flag para que el orchestrator no re-loguee
    );
}

// ... INSERT normal con idempotency_key en columna
```

Sprint 0 corre la migración aditiva (ver §15.4). Hasta entonces, el motor v2 puede operar sin idempotency (columna NULL, sin enforcement) — la propiedad la activa la migración cuando entra a prod.

---

## 8. Stage 6 — VERIFY (Chrome MCP — GATE blando)

**Propósito**: impersonar al cliente en `wellcorefitness.com`, navegar a `/client/plan` y `/client/nutrition`, ejecutar checklist visual.

| Aspecto | Detalle |
|---------|---------|
| Input | `PersistResultDto` + `IntakeDto` |
| Output | `VerifyResultDto { visualOk: bool, checklistResults: array<ChecklistItem>, screenshots: array<string>, consoleErrors: array<string>, networkErrors: array<string> }` |
| Determinístico | 100% código + tool calls Chrome MCP |
| LLM | No |
| DB local | Ninguna |
| DB prod (read) | `assigned_plans` (confirmar que la fila se ve correcta) |
| Errores fatal | `BrowserSessionError` · `LoginFailedError` |
| Errores GATE blando | `VisualChecklistFailedError` — el plan YA está INSERTED, no se puede rollback automático. Loguea y notifica a Daniel (slack/whatsapp). Daniel decide si manualmente desactiva con `UPDATE assigned_plans SET active=0`. |
| Test | E2E real — usar test client específico (no real cliente), por ejemplo seed `client_id=test_engine_v2_dummy` |

**Por qué GATE BLANDO y no GATE DURO**: una vez que PERSIST commiteó, el plan ya está en producción. Hacer rollback automático tiene riesgos (race condition con clientes mirando la UI, cache invalidation complicada). Más simple: GATE BLANDO + alerta. Daniel decide caso por caso. El linter (VALIDATE) ya es GATE DURO antes de PERSIST — esa es la línea de defensa principal.

**Checklist** (heredado de `validation.md` + `LECCIONES_APRENDIDAS_CRISTIAN.md`):

```php
readonly class ChecklistItem {
    public function __construct(
        public string $name,            // "HORARIO SEMANAL renderiza"
        public string $tabUrl,          // "/client/plan"
        public bool $passed,
        public ?string $screenshotPath,
        public ?string $failureReason,
    ) {}
}
```

Items por vertical (~6 por tab, ver `18-CHECKLIST-VERIFICACION-INTERFAZ.md`):

- Tab Entrenamiento: topbar "Semana X · Fase: Y" · HORARIO SEMANAL aparece · accordion semanas · ejercicios con GIF · sin errores consola
- Tab Nutrición: hero Calorías Diarias · 3 cards macros · CONSEJOS DE TU COACH · comidas con macros · 3 tabs A/B/C
- Tab Suplementos: NO dice "Tu coach está preparando..." · suplementos numerados

---

## 9. PlanEngineOrchestrator — el cleanup ownership

```php
final class PlanEngineOrchestrator {
    public function __construct(
        private IntakeStage $intake,
        private SelectStage $select,
        private ComposeStage $compose,
        private ValidateStage $validate,
        private PersistStage $persist,
        private VerifyStage $verify,
        private PlanEngineRunRepository $runs,
    ) {}

    public function executeJob(array $rawInput, string $createdBy): PlanEngineRun {
        // Lock — 1 run por laptop (file lock vía flock)
        $lock = $this->acquireLock();

        // Open handles
        $kbPdo = $this->openKbPdo();
        $prodPdo = $this->openProdPdo();
        $llmClient = $this->openLlmClient();
        $chromeMcp = $this->openChromeMcpSession();

        // Crear run row (status=running)
        $run = $this->runs->start($createdBy, $rawInput);
        $perfStages = [];

        try {
            $t0 = microtime(true);
            $intake = $this->intake->run($rawInput);
            $perfStages['intake_ms'] = ms_since($t0);

            $t0 = microtime(true);
            $eligible = $this->select->run($intake, $kbPdo);
            $perfStages['select_ms'] = ms_since($t0);

            $t0 = microtime(true);
            $plan = $this->compose->run($intake, $eligible, $kbPdo, $llmClient);
            $perfStages['compose_ms'] = ms_since($t0);

            $t0 = microtime(true);
            $lint = $this->validate->run($plan, $kbPdo);
            $perfStages['validate_ms'] = ms_since($t0);

            if (!$lint->ok) {
                $this->runs->markFailed($run, 'validate', "Linter failed with {$lint->errorCount} errors", $perfStages, $lint);
                return $run;  // GATE DURO — no avanza a PERSIST
            }

            $t0 = microtime(true);
            $persisted = $this->persist->run($plan, $lint, $intake, $prodPdo);
            $perfStages['persist_ms'] = ms_since($t0);

            $t0 = microtime(true);
            $verified = $this->verify->run($persisted, $intake, $chromeMcp);
            $perfStages['verify_ms'] = ms_since($t0);

            $this->runs->markCompleted($run, $persisted, $verified, $lint, $perfStages, $plan->tokensUsed);
        } catch (PlanEngineFatalError $e) {
            $this->runs->markFailed($run, $e->stage, $e->getMessage(), $perfStages, null);
            throw $e;
        } finally {
            safe_cleanup('chrome_mcp', fn() => $chromeMcp?->close());
            safe_cleanup('llm_client', fn() => $llmClient?->close());
            safe_cleanup('prod_pdo', fn() => $prodPdo = null);
            safe_cleanup('kb_pdo', fn() => $kbPdo = null);
            safe_cleanup('lock', fn() => $this->releaseLock($lock));
        }

        return $run;
    }
}
```

**`safe_cleanup`** (helper basado en HF `cleanup.ts:19-31`):

```php
function safe_cleanup(string $label, callable $fn): void {
    try { $fn(); }
    catch (\Throwable $e) {
        Log::debug("Cleanup failed ($label)", ['error' => $e->getMessage()]);
    }
}
```

**Propiedad clave**: si `chromeMcp->close()` tira (TCP race, sesión zombie), la excepción NO oculta el `PlanEngineFatalError` original. Igual semántica que HF.

---

## 10. Schema de `plan_engine_runs` (tabla en `wellcore_kb`)

Para observability post-run. Daniel + Claude Code la consultan para auditar.

| Columna | Tipo | Notas |
|---------|------|-------|
| `id` | bigint unsigned PK | |
| `status` | enum `running\|completed\|failed\|cancelled` | |
| `vertical` | enum | igual a wellcore_kb |
| `target_client_id` | int unsigned | FK lógico (sin constraint cross-DB) a `wellcore_fitness.clients.id` |
| `intake_dto_json` | json | snapshot completo del IntakeDto para reproducibilidad |
| `methodology_chosen_id` | bigint unsigned FK → methodologies | |
| `assigned_plan_id` | int unsigned | NULL si falló antes de PERSIST · FK lógico a `assigned_plans.id` prod |
| `error_origin_stage` | enum nullable | `intake\|select\|compose\|validate\|persist\|verify` |
| `error_message` | text nullable | |
| `error_stack` | longtext nullable | |
| `stage_timings_json` | json | `{"intake_ms":12, "select_ms":34, ...}` |
| `prompt_tokens_used` | int unsigned default 0 | |
| `completion_tokens_used` | int unsigned default 0 | |
| `cached_tokens_used` | int unsigned default 0 | tokens que vinieron del prompt cache (los "gratis") |
| `estimated_cost_usd` | decimal(8,4) default 0 | |
| `lint_findings_json` | json nullable | snapshot del LintResultDto |
| `verify_result_json` | json nullable | snapshot del VerifyResultDto |
| `screenshots_paths_json` | json nullable | paths a screenshots locales del VERIFY |
| `created_by` | varchar(80) | "daniel" / "coach:anderson" |
| `started_at`, `completed_at` | timestamp | |

**Índices**: PK · `(status, started_at)` · `target_client_id` · `methodology_chosen_id` · `error_origin_stage`.

**Por qué guardar el `intake_dto_json` completo**: reproducibilidad. Si dentro de 2 meses Daniel quiere regenerar el mismo plan para comparar con el motor de la versión 1.5, tiene el input exacto.

---

## 11. Concurrencia local (1 run por laptop)

```php
final class PlanEngineOrchestrator {
    private const LOCK_FILE = 'storage/locks/plan_engine.lock';

    private function acquireLock(): mixed {
        $fp = fopen(storage_path('locks/plan_engine.lock'), 'c');
        if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
            throw new LockBusyError("Otro run de plan_engine está activo — espera a que termine");
        }
        return $fp;
    }

    private function releaseLock(mixed $fp): void {
        if ($fp) { flock($fp, LOCK_UN); fclose($fp); }
    }
}
```

**Por qué `LOCK_NB` (non-blocking)**: si Daniel ya tiene un run corriendo y dispara otro, queremos fallar rápido con mensaje claro, no bloquear su segunda terminal indefinidamente.

**Si en el futuro hay 2 coaches usando el motor desde la misma máquina**: el lock sigue funcionando — 1 run por máquina. Si en algún momento se quiere paralelismo, hace falta DB lock (`GET_LOCK()` MySQL) o algo más serio. Premature en MVP.

---

## 12. Errores: las 3 categorías

```php
abstract class PlanEngineError extends \RuntimeException {
    public function __construct(
        public readonly string $stage,
        public readonly string $kind,         // "fatal" | "gate" | "warning"
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}

final class PlanEngineFatalError extends PlanEngineError { /* aborta y propaga */ }
final class PlanEngineGateError extends PlanEngineError { /* aborta sin propagar — la stage decidió que no avanza */ }
final class PlanEngineWarning extends PlanEngineError { /* loguea y continúa */ }
```

| Categoría | Comportamiento | Ejemplos |
|-----------|---------------|----------|
| **Fatal** | El orchestrator captura, marca `runs.status=failed`, llama `cleanup()`, propaga al caller | `LlmTimeoutError`, `PdoError`, `BrowserSessionError`, `LockBusyError` |
| **Gate** | El orchestrator captura, marca `runs.status=failed`, llama `cleanup()`, NO propaga (return normal del job) | `IntakeIncompleteError`, `NoEligibleMethodologiesError`, `PreInsertGateError` (linter falló) |
| **Warning** | Solo loguea, continúa al siguiente stage | `CacheInvalidationFailure`, lint findings con `severity=warning` |

**Por qué Gate ≠ Fatal**: un `IntakeIncompleteError` no es "el sistema se rompió" — es "el input no era suficiente". El caller (CLI / slash command) decide qué hacer (pedir más data). Diferenciar las dos categorías evita que el CLI muestre "ERROR FATAL" para algo que es una pregunta válida.

---

## 13. Cancelación cooperativa (mencionado en doc 01)

Cada stage acepta un closure `assertNotCancelled` (heredado de HF `compileStage.ts:72`):

```php
$intake = $this->intake->run($rawInput, $assertNotCancelled);
```

Si Daniel hace Ctrl+C en el CLI, el handler setea un flag que `assertNotCancelled` checkea entre operaciones largas (LLM call principalmente). Si está cancelado → throw `RunCancelledError` (fatal, propaga limpio).

Para MVP, la cancelación solo se honora en COMPOSE (la stage cara). Las otras son rápidas.

---

## 14. Testing — qué se prueba dónde

| Stage | Tipo de test | Herramienta |
|-------|--------------|-------------|
| INTAKE | Unit | Pest, sin DB |
| SELECT | Unit + DB local de test | Pest + sqlite in-memory con seed mínimo |
| COMPOSE | Snapshot (golden file) | Pest + mock LLM con respuestas grabadas |
| VALIDATE | Corre contra fixtures reales viejas | Pest + 5-10 JSONs de `CASOS-REALES/` incluido Cristian |
| PERSIST | Integration | Pest + MySQL de test (replica schema prod via `mysqldump --no-data`) |
| VERIFY | E2E manual + smoke automatizado | Daniel corre 1 vez por sprint; Playwright para smoke nightly |
| Orchestrator (end-to-end) | Integration completa | Pest + mock externos (Anthropic mock, Chrome stub) |

**Por qué snapshot tests en COMPOSE**: el output del LLM no es bit-exact reproducible (incluso con `temperature=0`). Snapshot tests permiten cambios controlados: Daniel revisa el diff cuando es esperado, falla cuando es regresión.

---

## 15. Lo que NO está resuelto en este doc

1. **Streaming del LLM en COMPOSE** — para iterar visible y permitir cancelación más fina. Pendiente para doc 05 (decision engine) donde discutimos prompt engineering en detalle.
2. **Multi-model routing** (Sonnet vs Haiku vs Opus) — qué stage usa qué modelo. Doc 05.
3. ~~**Retries en COMPOSE** cuando el LLM devuelve JSON unparseable~~ ✅ **DECIDIDO 2026-05-16**: **1 retry con prompt corregido** ("tu output anterior no parseaba, devolvé solo JSON válido"). Si falla el retry → `LlmOutputUnparseableError` fatal. Trade-off elegido: balance entre costo (no quemar tokens en loop) y resilience (cubre alucinaciones eventuales).
4. ~~**Idempotencia de PERSIST**~~ ✅ **DECIDIDO 2026-05-16**: **detectar duplicado y devolver el plan existente**. Requiere migración aditiva contra `wellcore_fitness`:
   ```sql
   ALTER TABLE assigned_plans
   ADD COLUMN idempotency_key VARCHAR(64) NULL,
   ADD UNIQUE KEY uniq_assigned_plans_idempotency (idempotency_key);
   ```
   Key derivada: `sha256("{client_id}|{vertical}|{valid_from}|{run_id}")`. Sprint 0 ejecuta la migración (es aditiva, no destructiva — cumple ADR-0003). Ver §7 PERSIST para flujo modificado.
5. **Webhooks post-run** — para que el motor v2 dispare WhatsApp/notification automáticamente. Por ahora queda fuera del scope (Daniel lo dispara manualmente con templates).

## Próximo doc

**`05-decision-engine.md`** — La capa híbrida en detalle:
- Cuándo decide REGLA y cuándo decide LLM (tabla decisión por decisión).
- Cómo se entrena el corpus RAG (Daniel agrega caso → embeds se recalculan).
- Prompt engineering: prompt caching, structured outputs vía Tool Use, reducción de token cost.
- Multi-model strategy: Sonnet para spine, Haiku para humanización.
- Stop conditions: cuándo el LLM "no sabe" y delega a humano.

Espero OK de Daniel para avanzar al doc 05.
