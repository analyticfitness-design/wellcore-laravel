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

## Estado del Sprint 0 (2026-05-17)

- ✅ Migraciones de las 8 tablas creadas
- ✅ Conexión `kb` configurada en `config/database.php`
- ✅ Killswitch `WC_ENGINE_V2_ENABLED` agregado a `.env.example`
- ✅ Estructura de directorios `app/PlanEngine/` creada
- ✅ `IntakeDto` boilerplate (otros DTOs en Sprint 2)
- ✅ Slash commands `/plan-create` y `/engine-health` boilerplate
- ⏳ Seed inicial (7 metodologías + 50 ejercicios + 15 principios + 5 templates) — pendiente
- ⏳ Linter aislado contra fixtures — Sprint 1

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
