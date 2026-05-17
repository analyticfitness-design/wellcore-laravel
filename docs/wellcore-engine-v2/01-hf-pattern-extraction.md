# 01 — Extracción del patrón HyperFrames

> Documento de diseño. NO se porta código TypeScript. Se porta el PATRÓN.

## TL;DR

HyperFrames (HF) compone su pipeline en **6 stages aisladas** bajo `packages/producer/src/services/render/stages/`, donde el orchestrator usa un único `try/finally` para que ningún recurso (Chrome, ffmpeg, work dir) quede colgado si una stage falla. El linter `lintHyperframeHtml()` corre **antes** del orchestrator, agrupa 7 paquetes de rules por dominio, y bloquea el render si `errorCount > 0` — es un gate, no un add-on de calidad. Cada finding lleva `code` estable + `fixHint` accionable, lo cual lo hace útil al humano y no solo al CI. Vamos a portar dos cosas: (a) stages puras + cleanup centralizado por el orchestrator, (b) linter como gate previo. Todo lo demás de HF (frame capture, ffmpeg, HDR, workers, file server) NO aplica a WellCore — su dominio es video, el nuestro es JSON validado en `assigned_plans`.

---

## 1. Las 6 stages del renderOrchestrator

`packages/producer/src/services/renderOrchestrator.ts:1-31` declara el contrato:

> Each stage lives in its own module under `./render/stages/` so the pure-function primitives can be reused by the distributed render path without dragging the orchestrator's cleanup and observability scaffolding with them.

| # | Stage | Archivo |
|---|-------|---------|
| 1 | compile | `render/stages/compileStage.ts:102` (`runCompileStage`) |
| 1b | probe (browser-driven duration discovery) | `render/stages/probeStage.ts` |
| 2 | extract videos | `render/stages/extractVideosStage.ts` |
| 3 | audio | `render/stages/audioStage.ts` |
| 4 | capture | `render/stages/captureStage.ts` (+ `captureStreamingStage.ts`, `captureHdrStage.ts`) |
| 5 | encode | `render/stages/encodeStage.ts` |
| 6 | assemble | `render/stages/assembleStage.ts` |

Cada stage exporta `run{StageName}Stage(input)` como **función pura** que recibe un input DTO tipado (`CompileStageInput`, `compileStage.ts:51-81`) y devuelve un result DTO (`CompileStageResult`, `compileStage.ts:83-100`). El orchestrator las compone secuencialmente.

### 1.1 Cleanup centralizado — la garantía clave

`render/cleanup.ts:19-31` define `safeCleanup()`:

> Wrap a cleanup operation so it never throws, but logs any failure. The sequencer needs to keep tearing down resources even when one of them is stuck (e.g. a `fileServer.close()` hitting a TCP race); a thrown cleanup error would mask the original render failure.

`cleanupRenderResources()` (cleanup.ts:38-70) cierra `fileServer`, `probeSession`, y `workDir` cada uno envuelto en `safeCleanup`. **Propiedad portable**: si una stage tira `ValidationError`, ese error sobrevive aunque el cleanup posterior falle por separado. En PHP esto es un `try/catch` con `finally` que captura excepciones del cleanup y las loguea sin re-lanzar.

### 1.2 Observability por stage

`buildRenderErrorDetails()` (cleanup.ts:76-99) ensambla el payload de error que la API expone:

```ts
{ message, stack, elapsedMs, freeMemoryMB,
  browserConsoleTail: last30Lines,
  perfStages: { compileMs, probeMs, captureMs, ... },
  hdrDiagnostics? }
```

`perfStages` es un `Record<string, number>` que cada stage mutó al terminar. En un fallo, se sabe en cuál stage murió y cuánto tardó cada una. Para WellCore, esto se traduce en una tabla `plan_engine_runs` con columnas `stage_timings_json` y `error_origin_stage`.

---

## 2. El linter: 7 paquetes de rules + un gate `ok`

Archivo principal: `packages/core/src/lint/hyperframeLinter.ts`.

### 2.1 Tipos (lint/types.ts:1-30)

```ts
type HyperframeLintFinding = {
  code: string;          // ID estable: dedup, autofixes, silenciamientos
  severity: "error" | "warning" | "info";
  message: string;       // human-readable
  selector?, elementId?, snippet?: string;
  fixHint?: string;      // ★ texto accionable: "agrega X"
};

type HyperframeLintResult = {
  ok: boolean;           // ★ === errorCount === 0
  errorCount, warningCount, infoCount, findings;
};

type LintRule<TContext> = (ctx: TContext) => HyperframeLintFinding[];
```

### 2.2 Registro (hyperframeLinter.ts:12-20)

```ts
const ALL_RULES = [
  ...coreRules, ...mediaRules, ...gsapRules, ...captionRules,
  ...compositionRules, ...adapterRules, ...textureRules,
];
```

7 paquetes organizados por **dominio**. Cada paquete exporta un array de funciones. El registro es concatenación, no DI — simple y testeable. Por dominio hay un `.test.ts` hermano (`rules/core.test.ts`, etc.).

### 2.3 Ejecución y gate (hyperframeLinter.ts:22-56)

```ts
for (const rule of ALL_RULES) {
  for (const finding of rule(ctx)) {
    const dedupeKey = [code, severity, selector, elementId, message].join("|");
    if (seen.has(dedupeKey)) continue;
    findings.push(finding);
  }
}
return { ok: errorCount === 0, errorCount, ..., findings };
```

**Dedup por composite key** porque varias rules pueden detectar el mismo síntoma de ángulos distintos. El gate es `ok = errorCount === 0`: warnings e info no bloquean.

### 2.4 Anatomía de una rule (rules/core.ts:58-80)

```ts
export const coreRules = [
  ({ rootTag }) => {
    const findings = [];
    if (!rootTag || !readAttr(rootTag.raw, "data-composition-id")) {
      findings.push({
        code: "root_missing_composition_id",
        severity: "error",
        message: "Root composition is missing `data-composition-id`.",
        fixHint: "Add a stable `data-composition-id` to the entry composition wrapper.",
        snippet: truncateSnippet(rootTag?.raw || ""),
      });
    }
    return findings;
  },
];
```

Tres propiedades clave a copiar:
1. **`code`** único y estable — usable como ID para autofixes y para silenciar findings en casos justificados.
2. **`fixHint`** dice CÓMO arreglar — no solo qué está mal.
3. **Rules sin estado** — solo leen del `ctx` precomputado (`buildLintContext`).

### 2.5 Async lint pass — HEAD checks (hyperframeLinter.ts:99-151)

`lintMediaUrls()` hace HEAD a cada URL externa con `AbortController` y timeout, devuelve findings `code: "inaccessible_media_url"`. **Patrón directamente reutilizable para nosotros**: validar los GIFs del catálogo `analyticfitness-design/wellcore-exercise-gifs` con HEAD antes del INSERT (el caso Cristian se rompió exactamente por GIFs con URL incorrecta).

---

## 3. Mapping 1:1 — HF → WellCore

| # | HF stage | WellCore stage | Determinístico vs LLM | Recurso a limpiar |
|---|----------|----------------|------------------------|-------------------|
| 1 | compile (HTML → metadata) | **INTAKE** (form/CLI → IntakeDTO validada) | 100% código | — |
| 1b | probe (browser-driven duration) | **SELECT** (queries a `wellcore_kb`) | 100% código | conexión MySQL local |
| 2-3 | extract videos + audio (compose media) | **COMPOSE** (rules arman spine + LLM arma topping vía Tool Use) | híbrido | sesión Anthropic API + prompt cache |
| 4 | capture (frame-by-frame) | **VALIDATE** (linter sobre JSON) | 100% código | — |
| 5 | encode (frames → mp4) | **PERSIST** (PDO INSERT a producción) | 100% código | PDO connection + transaction |
| 6 | assemble (mux audio+video + faststart) | **VERIFY** (Chrome MCP impersona cliente + checklist visual) | 100% código + tool calls | Chrome session MCP |

**Por qué este mapping funciona**:
- HF `compile` y nuestro INTAKE parsean input externo a un DTO interno tipado.
- HF `capture` y nuestro VALIDATE son la stage "se concreta o se rompe": HF captura frames y falla si el DOM está mal armado; nosotros validamos JSON y fallamos si el schema 16a/b/c/d no cumple.
- HF `encode` y nuestro PERSIST son el punto de no retorno: si pasaste el linter, casi imposible producir output inválido aguas abajo.
- HF `assemble` y nuestro VERIFY hacen ensamblaje final + sanity: HF mux + faststart, nosotros impersonar cliente real en producción.

---

## 4. Qué del patrón HF NO aplica a WellCore

| Elemento HF | Por qué no aplica |
|-------------|-------------------|
| Frame capture (BeginFrame, screenshot fallback) | Dominio video. Nosotros "renderizamos" JSON, no píxeles. |
| ffmpeg subprocess management | Sin video encoding. |
| HDR diagnostics, alpha channel handling | Específicos de formato de video. |
| Worker parallelism (`executeParallelCapture`) | Generar 1 plan no se beneficia de paralelismo intra-plan. |
| `MemorySampler` | Procesos PHP CLI cortos, no relevante. |
| File server (servir assets a Chrome) | No servimos HTML a un browser durante la generación. |
| `LockedRenderConfig` para distributed renders | Sin distributed path en MVP. |

**Lo que sí portamos del scaffolding observability** (estructura de `perfStages`, `error origin stage`, browser console tail) se aplicará al payload de la tabla `plan_engine_runs` que se detalla en doc 04.

---

## 5. Lo que NO está resuelto en este doc

- **Concurrencia**: HF orquesta 1 render por proceso. ¿Qué pasa si Daniel dispara 2 generaciones en paralelo en la misma laptop? ¿Lock optimista en `wellcore_kb`? Lo trabajamos en doc 04 (stages architecture).
- **Cancelación cooperativa**: HF pasa `assertNotAborted()` a cada stage (`compileStage.ts:72`). ¿WellCore necesita cancelar mid-COMPOSE si el LLM alucina? Doc 05 (decision engine).
- **Streaming del LLM**: HF tiene `captureStreamingStage.ts`. ¿COMPOSE debería streamear para iterar más rápido y cortar early? Doc 05.
- **Drift schema real**: `app/Models/AssignedPlan.php:13-22` declara `version` en `#[Fillable]` y `app/Livewire/Admin/AIPlanGenerator.php:413-422` lo usa (`$latestVersion + 1`), pero `CASOS-REALES/LECCIONES_APRENDIDAS_CRISTIAN.md:198` afirma que la tabla NO tiene esa columna. Verificación dura con `DESCRIBE assigned_plans` en producción es prerequisito antes de cualquier diseño que dependa de versionado — lo resuelvo en doc 02.

## Próximo doc

**`02-wellcore-current-state.md`** — Inventario corto del sistema actual:
- Flujo real de creación hoy (Claude Code → 27 MDs → INSERT PDO).
- Riesgos rankeados: token cost, monotonía 3×12, caso Cristian, ausencia de validation pre-INSERT, drift docs ↔ código.
- Lista exhaustiva de columnas de `assigned_plans` verificadas en producción (resuelve el drift `version`).
- Tablas intocables enumeradas con motivo.
- Volumen actual: `COUNT(*)` de planes activos para dimensionar blast radius del rollout.

Espero OK de Daniel para avanzar al doc 02.
