# Motor v2 — Creación de planes WellCore

> Diseño completo en `docs/wellcore-engine-v2/`. Empezar por `00-INDEX` mental:
> docs 01 (patrón HF) → 02 (estado actual) → 03 (schema kb) → 04 (stages) →
> 05 (decision engine) → 06 (lint rules) → 07 (rollout) → 08 (weekly loop) → 09 (open questions).

## Qué es esto

Reemplaza el flujo "Claude Code humano lee 27 MDs en cada sesión" por un toolchain
local determinístico + LLM tool-constrained.

**Output**: JSON canónico (schema 16a/b/c/d) que se INSERTA en
`wellcore_fitness.assigned_plans` igual que hoy. **El frontend Vue NO cambia**.

## Estructura de directorios

```
app/PlanEngine/
├── README.md              ← este archivo
├── Dto/                   ← DTOs readonly entre stages (PHP 8.4 readonly classes)
├── Stages/                ← Las 6 stages: Intake, Select, Compose, Validate, Persist, Verify
├── Prompts/V1/            ← System prompts versionados (tool definitions, etc.)
├── Tools/                 ← Tool Use definitions para Anthropic API
└── Lint/                  ← LintEngine + categorías de check_type
```

## DB local — `wellcore_kb`

Vive en MySQL local (Herd), NO en producción. 8 tablas:
- `methodologies`, `methodology_rules` (catálogo + filtros)
- `exercise_metadata` (265 ejercicios enriquecidos)
- `principles` (15 principios de coaching)
- `plan_templates_local` (starting points por perfil)
- `decision_rules` (input pattern → metodología)
- `lint_rules` (catálogo del linter, DB-driven hot-reload)
- `corpus_embeddings` (RAG, opcional MVP — Sprint 3+)

Migraciones en `database/migrations-kb/`. Conexión `kb` configurada en
`config/database.php`.

```bash
# Setup inicial (Sprint 0)
mysql -u root -e "CREATE DATABASE wellcore_kb DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate --database=kb --path=database/migrations-kb
```

## Killswitch operativo

En `.env`:
```
WC_ENGINE_V2_ENABLED=false  # default — motor desactivado, usa flujo manual
```

Cambiar a `true` cuando el rollout Sprint 4 arranque.

## Estado actual del motor (2026-05-17, Sprint 100)

### Counts
- **Comandos artisan**: 28 (`plan:*` + `kb:*`)
- **Validators registrados**: 34
- **Lint rules**: 45 (schema + heuristic + external_head + sql + llm_review opt-in)
- **Methodologies**: 8 (5 verticales)
- **Decision rules**: 19
- **Principles**: 48 (entrenamiento 11 · nutricion 11 · suplementacion 9 · habitos 10 · ciclo 7)
- **Exercise metadata**: 267 (45 broken pendientes de reconcile)

### Verificación rápida
```bash
php artisan plan:health-check         # estado completo del motor
php artisan kb:counts                 # cuenta rows por tabla (rápido)
php artisan kb:audit-orphans          # detecta rows huérfanos
php artisan plan:assert-deterministic --include-lint   # determinismo 5/5
```

### Comandos por categoría

**Pipeline E2E**
- `plan:decide` / `plan:compose` / `plan:lint` / `plan:persist` / `plan:bundle` / `plan:batch`

**Audit + inspección**
- `plan:show <id>` / `plan:diff <a> <b>` / `plan:explain <id>` / `plan:replay <id>`
- `plan:audit-summary` / `plan:violations-trend` / `plan:list-pending`
- `plan:assert-deterministic` (regression CI)

**Operación**
- `plan:export-prod-script` / `plan:export-bundle-prod-script`
- `plan:reset-audit-table` (cleanup con DRY-RUN)
- `plan:gif-recheck` (revalida catálogo)
- `plan:health-check` (meta-audit)

**KB management**
- `kb:install` / `kb:seed` / `kb:status` / `kb:counts` / `kb:stats`
- `kb:export-snapshot` / `kb:restore-snapshot` / `kb:diff-snapshots`
- `kb:export-principles-md` / `kb:export-methodologies-md`
- `kb:list-principles` / `kb:audit-orphans`
- `kb:verify-gifs` / `kb:reconcile-gifs` / `kb:clean-exercise-catalog` / `kb:import-exercise-catalog`

## Documentación de referencia

| Tema | Doc |
|------|-----|
| Patrón HF (qué portamos y qué no) | `docs/wellcore-engine-v2/01-hf-pattern-extraction.md` |
| Schema actual de `assigned_plans` (verificado contra prod 2026-05-17) | `docs/wellcore-engine-v2/02-wellcore-current-state.md` §5 |
| Schema de cada tabla del kb | `docs/wellcore-engine-v2/03-knowledge-base-schema.md` §3 |
| Las 6 stages con DTO contracts | `docs/wellcore-engine-v2/04-stages-architecture.md` §3-§8 |
| Tool Use + multi-model strategy | `docs/wellcore-engine-v2/05-decision-engine.md` §2-§3 |
| Catálogo de 30 lint rules iniciales | `docs/wellcore-engine-v2/06-lint-rules.md` §3-§7 |
| Plan de rollout y killswitch | `docs/wellcore-engine-v2/07-strangler-fig-rollout.md` |
| Manual operativo Daniel + slash commands | `docs/wellcore-engine-v2/08-weekly-loop-daniel.md` |
| Open questions y riesgos | `docs/wellcore-engine-v2/09-open-questions-and-risks.md` |

## Reglas de oro al implementar

1. **No tocar el frontend Vue ni el schema `assigned_plans`** (excepto migración aditiva Sprint 4).
2. **Cumplir voz tuteo neutro latino** (ver memoria `feedback_idioma_latino_neutro.md`).
3. **DTOs readonly** (PHP 8.4 `readonly class`) — cero mutación entre stages.
4. **Orchestrator dueño de handles** — stages reciben handles abiertos, no los abren.
5. **Linter es GATE DURO** antes de PERSIST — si falla, no INSERT.
6. **Cero llamadas LLM con prompt libre** — siempre Tool Use con schemas estrictos.
7. **Cada run se loguea en `plan_engine_runs`** para reproducibilidad.
