# 03 — Knowledge base schema (`wellcore_kb` local)

> Documento de diseño. La DB se crea en MySQL local de Daniel (Herd), NO en EasyPanel.

## TL;DR

`wellcore_kb` es la "memoria estructurada" del motor v2 — vive en MySQL local de Daniel (Herd), tiene **8 tablas** organizadas en 3 anillos (catálogo · reglas · validación), y se alimenta semana a semana sin redeploy de producción. Las migraciones van en `database/migrations-kb/` separadas de las de producción para que `php artisan migrate --database=kb --path=database/migrations-kb` corra solo contra la kb. El schema usa convenciones Laravel estándar (timestamps automáticos, FK con cascade, índices nombrados) — distinto a `wellcore_fitness` que tiene drift histórico por el origen vanilla. Todo el data del corpus se exporta vía `mysqldump` cifrado al repo (`storage/wellcore-kb-seed-YYYYMMDD.sql.gz.gpg`) para que la laptop nueva o un eventual VPS replique con un `gpg -d | mysql`. El MVP arranca con 7 tablas (la 8ª, `corpus_embeddings`, es opcional y entra en Sprint 3+).

---

## 1. Arquitectura: las 8 tablas en 3 anillos

```
┌─────────────────────────────────────────────────────────────┐
│  ANILLO 1: CATÁLOGO (qué existe y se puede recombinar)      │
│    • methodologies          • exercise_metadata             │
│    • principles             • plan_templates_local          │
├─────────────────────────────────────────────────────────────┤
│  ANILLO 2: REGLAS (cómo decidir, cómo combinar)             │
│    • methodology_rules      • decision_rules                │
├─────────────────────────────────────────────────────────────┤
│  ANILLO 3: VALIDACIÓN + RAG (cómo verificar y aprender)     │
│    • lint_rules             • corpus_embeddings (Sprint 3+) │
└─────────────────────────────────────────────────────────────┘
```

**Mapeo a las 6 stages del motor v2** (doc 04 lo detalla):

| Stage | Tablas que consulta |
|-------|---------------------|
| SELECT | `methodologies`, `methodology_rules`, `decision_rules` |
| COMPOSE | `methodologies`, `plan_templates_local`, `exercise_metadata`, `principles`, `corpus_embeddings` |
| VALIDATE | `lint_rules`, `exercise_metadata` (para validar GIFs) |
| PERSIST | — (escribe a `wellcore_fitness.assigned_plans`) |
| VERIFY | — |

---

## 2. Setup en Laravel — conexión separada

`config/database.php` agrega una conexión nueva:

```php
'connections' => [
    // existing 'mysql' connection (wellcore_fitness) stays as is

    'kb' => [
        'driver' => 'mysql',
        'host' => env('KB_DB_HOST', '127.0.0.1'),
        'port' => env('KB_DB_PORT', '3306'),
        'database' => env('KB_DB_DATABASE', 'wellcore_kb'),
        'username' => env('KB_DB_USERNAME', 'root'),
        'password' => env('KB_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'engine' => 'InnoDB',
        'strict' => true,  // queremos strict mode (a diferencia de prod)
    ],
],
```

`.env.example` agrega:

```
KB_DB_HOST=127.0.0.1
KB_DB_PORT=3306
KB_DB_DATABASE=wellcore_kb
KB_DB_USERNAME=root
KB_DB_PASSWORD=
```

`.env` local de Daniel tiene los valores reales (Herd usa puerto 3306 con root sin password por default).

**Comando de migración**:

```bash
php artisan migrate --database=kb --path=database/migrations-kb
```

**Comando para inspeccionar**:

```bash
php artisan db:show --database=kb
php artisan db:table --database=kb methodologies
```

---

## 3. Las 8 tablas — schemas detallados

### 3.1 `methodologies` — catálogo de metodologías

Anillo 1. Todas las metodologías que el motor puede recomendar.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `slug` | varchar(64) UNIQUE | NO | — | ej `body_part_split_5d`, `upper_lower_4d`, `ppl_6d`, `iifym`, `creatina_basica` |
| `name` | varchar(120) | NO | — | display, ej "Body Part Split 5 días" |
| `vertical` | enum | NO | — | `entrenamiento\|nutricion\|suplementacion\|habitos\|ciclo` |
| `description` | text | NO | — | principios + cuándo usar |
| `target_days_min` | tinyint unsigned | YES | NULL | para entrenamiento (mínimo días/semana) |
| `target_days_max` | tinyint unsigned | YES | NULL | máximo días/semana |
| `target_level` | enum | NO | `any` | `principiante\|intermedio\|avanzado\|any` |
| `target_goal` | enum | NO | `any` | `hipertrofia\|fuerza\|perdida_grasa\|recomposicion\|mantenimiento\|performance\|any` |
| `periodization_pattern` | json | YES | NULL | default phase progression — `[{weeks:3, fase:"Adaptación"}, {weeks:3, fase:"Hipertrofia"}, ...]` |
| `status` | enum | NO | `active` | `active\|experimental\|deprecated` |
| `created_by` | varchar(80) | NO | — | "daniel" / "anderson" / "claude-curated" |
| `version` | varchar(16) | NO | `1.0` | semver-like |
| `created_at`, `updated_at` | timestamp | NO | auto | Laravel timestamps |

**Índices**: PK · UNIQUE `slug` · INDEX `(vertical, status)` · INDEX `(target_goal, target_level)`.

**Migration ejemplo (la única que muestro completa — las otras 7 siguen el mismo patrón)**:

```php
<?php
// database/migrations-kb/2026_05_17_000001_create_methodologies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'kb';  // ★ apunta a la conexión kb

    public function up(): void {
        Schema::connection('kb')->create('methodologies', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 64)->unique();
            $t->string('name', 120);
            $t->enum('vertical', ['entrenamiento','nutricion','suplementacion','habitos','ciclo']);
            $t->text('description');
            $t->unsignedTinyInteger('target_days_min')->nullable();
            $t->unsignedTinyInteger('target_days_max')->nullable();
            $t->enum('target_level', ['principiante','intermedio','avanzado','any'])->default('any');
            $t->enum('target_goal', ['hipertrofia','fuerza','perdida_grasa','recomposicion','mantenimiento','performance','any'])->default('any');
            $t->json('periodization_pattern')->nullable();
            $t->enum('status', ['active','experimental','deprecated'])->default('active');
            $t->string('created_by', 80);
            $t->string('version', 16)->default('1.0');
            $t->timestamps();

            $t->index(['vertical', 'status']);
            $t->index(['target_goal', 'target_level']);
        });
    }

    public function down(): void {
        Schema::connection('kb')->dropIfExists('methodologies');
    }
};
```

---

### 3.2 `methodology_rules` — filtros de elegibilidad

Anillo 2. Vinculan input del cliente → metodologías candidatas. SELECT stage las consulta.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `methodology_id` | bigint unsigned FK → methodologies(id) CASCADE | NO | — | |
| `rule_type` | enum | NO | — | `hard_filter\|soft_filter\|preference` — hard filter elimina, soft solo penaliza, preference solo suma |
| `applies_when_json` | json | NO | — | condición evaluable, ej `{"age_max":17,"reason":"menor de edad"}` |
| `weight` | decimal(4,2) | NO | `1.00` | peso para sort de candidatas (0.00–10.00) |
| `explanation` | text | NO | — | para el LLM y para Daniel |
| `created_at`, `updated_at` | timestamp | NO | auto | |

**Índices**: PK · INDEX `methodology_id` · INDEX `rule_type`.

**Ejemplo de fila**:
```json
{
  "methodology_id": 1,  // body_part_split_5d
  "rule_type": "hard_filter",
  "applies_when_json": {"target_days_min": 5, "client_days_available_lt": 5},
  "weight": 0.00,
  "explanation": "Body Part Split 5d requiere 5 días/semana — descartar si el cliente tiene menos"
}
```

---

### 3.3 `exercise_metadata` — los 265 ejercicios enriquecidos

Anillo 1. Una fila por alias del catálogo de GIFs. NO duplica `exercise_aliases` de prod — la enriquece.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `alias` | varchar(80) UNIQUE | NO | — | matches `exercise_aliases.gif_filename` sin `.gif` |
| `name_canonical` | varchar(160) | NO | — | "Press de banca con barra" |
| `muscle_primary` | varchar(40) | NO | — | "Pecho" |
| `muscle_secondary` | varchar(160) | YES | NULL | CSV: "Tríceps, Hombro anterior" |
| `equipment_required` | json | NO | `[]` | `["barra", "banco_plano"]` |
| `equipment_substitutes` | json | NO | `[]` | aliases alternativos: `["press-banca-mancuernas", "press-banca-maquina"]` |
| `level_min` | enum | NO | `principiante` | `principiante\|intermedio\|avanzado` |
| `compound_isolation` | enum | NO | — | `compound\|isolation` |
| `movement_pattern` | enum | YES | NULL | `push_horizontal\|push_vertical\|pull_horizontal\|pull_vertical\|squat\|hinge\|lunge\|core\|carry\|cardio_steady\|cardio_intervals\|other` |
| `contraindications` | json | NO | `[]` | `["lesion_hombro_anterior", "hernia_inguinal"]` |
| `common_mistakes` | text | YES | NULL | bullet list textual |
| `coaching_cues` | json | NO | `[]` | `["Codos a 45°", "Bajada en 2s", "No bloquees codos"]` |
| `variations` | json | NO | `[]` | `[{"alias":"...","reason":"alternativa con mancuernas"}]` |
| `gif_url_verified_at` | timestamp | YES | NULL | última vez que se HEAD-checkeó la URL |
| `gif_url_status` | enum | NO | `unknown` | `ok\|broken\|missing\|unknown` |
| `created_at`, `updated_at` | timestamp | NO | auto | |

**Índices**: PK · UNIQUE `alias` · INDEX `muscle_primary` · INDEX `(compound_isolation, level_min)` · INDEX `movement_pattern` · INDEX `gif_url_status`.

**Por qué `gif_url_verified_at` y `gif_url_status`**: el linter VALIDATE va a hacer HEAD checks periódicos al repo de GIFs (`raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs`). Si un alias deja de existir, el motor lo detecta antes del INSERT (caso Cristian error #2 — esto lo previene).

---

### 3.4 `principles` — principios de coaching reutilizables

Anillo 1. COMPOSE stage los inyecta en `notas_coach`, `tips[]`, y notas de ejercicio cuando aplican.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `slug` | varchar(64) UNIQUE | NO | — | `sobrecarga_progresiva`, `tecnica_primero` |
| `name` | varchar(120) | NO | — | display |
| `vertical` | enum | NO | — | igual al de methodologies |
| `description_short` | varchar(280) | NO | — | 1-2 frases para chip/badge |
| `description_long` | text | NO | — | markdown, varios párrafos |
| `when_to_apply` | text | NO | — | "cuándo este principio es relevante" |
| `example_usage` | text | YES | NULL | ejemplo de nota_coach que lo usa |
| `tags` | json | NO | `[]` | `["adaptacion","intermedio_avanzado"]` |
| `status` | enum | NO | `active` | `active\|experimental\|deprecated` |
| `created_by` | varchar(80) | NO | — | |
| `created_at`, `updated_at` | timestamp | NO | auto | |

**Índices**: PK · UNIQUE `slug` · INDEX `(vertical, status)`.

---

### 3.5 `plan_templates_local` — templates listos por perfil

Anillo 1. **NO confundir con `plan_templates` de producción** (esa pertenece al `PlanTemplate` model que usa el panel coach). Esta es local del motor v2.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `name` | varchar(160) | NO | — | "Esencial — hombre intermedio 5d hipertrofia" |
| `vertical` | enum | NO | — | igual |
| `target_profile_json` | json | NO | — | `{gender, age_range, level, goal, days, equipment}` |
| `structure_json` | longtext | NO | — | el plan canónico (16a/b/c/d) — sirve como starting point para COMPOSE |
| `source` | enum | NO | — | **`curated_literature\|from_real_client\|manual_daniel\|manual_coach`** (excluye `ai_generated` — ver `feedback_no_ai_plan_generator_corpus.md`) |
| `quality_score` | tinyint unsigned | NO | `50` | 0-100, Daniel evalúa |
| `times_used` | int unsigned | NO | `0` | incrementa cuando COMPOSE lo usa como starting point |
| `last_used_at` | timestamp | YES | NULL | |
| `created_by` | varchar(80) | NO | — | |
| `version` | varchar(16) | NO | `1.0` | |
| `status` | enum | NO | `active` | `active\|experimental\|deprecated` |
| `created_at`, `updated_at` | timestamp | NO | auto | |

**Índices**: PK · INDEX `(vertical, status, quality_score)` · INDEX `source` · INDEX `times_used`.

---

### 3.6 `decision_rules` — input pattern → metodología recomendada

Anillo 2. SELECT stage las usa para rankear candidatas más allá del filtro.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `name` | varchar(160) | NO | — | "Hipertrofia intermedio 5d → Body Part Split" |
| `when_json` | json | NO | — | condición evaluable: `{"goal":"hipertrofia","level":"intermedio","days":5}` |
| `then_methodology_id` | bigint unsigned FK → methodologies(id) CASCADE | NO | — | |
| `confidence` | decimal(3,2) | NO | — | 0.00-1.00 |
| `rationale` | text | NO | — | para el LLM y para Daniel |
| `author` | varchar(80) | NO | — | quien la escribió |
| `status` | enum | NO | `active` | `active\|experimental\|deprecated` |
| `times_fired` | int unsigned | NO | `0` | métrica de uso |
| `created_at`, `updated_at` | timestamp | NO | auto | |

**Índices**: PK · INDEX `then_methodology_id` · INDEX `(status, confidence)`.

---

### 3.7 `lint_rules` — el catálogo del linter pre-INSERT

Anillo 3. VALIDATE stage carga estas rules dinámicamente — agregar una rule nueva NO requiere redeploy. Doc 06 detalla cada categoría.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `code` | varchar(80) UNIQUE | NO | — | `missing_phase_field`, `gif_alias_not_in_catalog`, `monotonia_3x12` |
| `vertical` | enum | YES | NULL | NULL = aplica a todos los verticals |
| `severity` | enum | NO | — | `error\|warning\|info` (error bloquea el INSERT) |
| `description` | text | NO | — | qué detecta la rule |
| `check_type` | enum | NO | — | `schema\|heuristic\|external_head\|sql\|llm_review` |
| `check_definition_json` | json | NO | — | depende del check_type — schema usa JSONPath, heuristic usa predicado, external_head usa URL pattern, sql usa query |
| `fix_hint_template` | text | NO | — | con placeholders, ej `"Agregá semanas[].fase con uno de: {{fase_names}}"` |
| `enabled` | boolean | NO | `true` | toggle sin borrar |
| `auto_fix_available` | boolean | NO | `false` | si la rule puede autocorregir el JSON antes de fallar |
| `created_by` | varchar(80) | NO | — | |
| `created_at`, `updated_at` | timestamp | NO | auto | |

**Índices**: PK · UNIQUE `code` · INDEX `(enabled, severity)` · INDEX `vertical`.

**Por qué DB-driven y no filesystem-driven**: las rules cambian rápido durante el período de calibración (las primeras 4 semanas tras el rollout). DB-driven permite a Daniel agregar/desactivar/editar rules con un UPDATE sin redeploy. Cuando el catálogo se estabilice (Sprint 6+), se puede snapshot a YAML versionado en git.

---

### 3.8 `corpus_embeddings` — RAG vectorial (OPCIONAL MVP, Sprint 3+)

Anillo 3. Embeddings para retrieval semántico en COMPOSE stage.

| Columna | Tipo | Null | Default | Notas |
|---------|------|------|---------|-------|
| `id` | bigint unsigned PK | NO | auto | |
| `source_type` | enum | NO | — | `methodology\|principle\|exercise_meta\|plan_template\|doc_chunk\|success_case` |
| `source_id` | bigint unsigned | NO | — | FK polimórfico al ID del recurso de origen |
| `chunk_text` | text | NO | — | texto que generó el embedding |
| `embedding` | json | NO | — | array de 1536 floats (Voyage AI default) o 1024 (Anthropic Voyage Lite) |
| `model_used` | varchar(40) | NO | — | `voyage-3.5`, `voyage-3-lite` |
| `token_count` | int unsigned | NO | — | para tracking de costo |
| `created_at` | timestamp | NO | auto | |

**Índices**: PK · INDEX `(source_type, source_id)` · INDEX `model_used`.

**Por qué `json` y no `vector`**: MySQL 8 no tiene tipo `vector` nativo (PostgreSQL pgvector sí). Para MVP usamos JSON y hacemos cosine similarity en aplicación PHP. Si el corpus crece >50K embeddings y la latency molesta, alternativas:
- Migrar a `sqlite-vec` (extension de SQLite)
- Migrar a Qdrant/Weaviate local
- Esperar MySQL 9 vector support

**Por qué Voyage y no OpenAI**: Anthropic recomienda Voyage AI para apps de Claude (precio + latency mejor en LATAM). 1024 dims es enough para retrieval de planes.

---

## 4. Estrategia de seeding inicial (Sprint 0)

Para arrancar el motor v2 hace falta data en al menos 4 tablas. El seeding se hace vía artisan commands custom + SQL puro.

### 4.1 Seed mínimo viable

| Tabla | Cantidad mínima MVP | Origen |
|-------|---------------------|--------|
| `methodologies` | 3 entrenamiento + 2 nutrición + 1 suplementación + 1 hábitos = **7 metodologías** | Curadas por Daniel + Claude Code desde MD 08 + MD 22 |
| `exercise_metadata` | **50 ejercicios** (los más usados en planes reales) | Extraídos de `assigned_plans.content` históricos + enriquecidos con MD 20 |
| `principles` | **15 principios** | Curados desde MD 05 + MD 08 |
| `plan_templates_local` | **5 templates** (1 por arquetipo de cliente común) | Extraídos de planes reales exitosos (anonimizados) — fuente `from_real_client` |
| `decision_rules` | **10 rules** iniciales | Daniel + Claude Code desde experiencia |
| `lint_rules` | **20 rules** iniciales | Doc 06 las cataloga |
| `methodology_rules` | **15 rules** | Por cada metodología, 2-3 filtros |
| `corpus_embeddings` | 0 | Sprint 3+ |

### 4.2 Artisan commands para seeding

```bash
# Crear DB local (una sola vez)
php artisan kb:install

# Correr migraciones
php artisan migrate --database=kb --path=database/migrations-kb

# Seed inicial
php artisan kb:seed:methodologies
php artisan kb:seed:exercises --source=production --limit=50
php artisan kb:seed:principles
php artisan kb:seed:lint-rules

# Inspección
php artisan kb:status      # cuenta filas por tabla, alerta si están vacías
php artisan kb:verify-gifs # HEAD checks a todas las gif URLs del exercise_metadata
```

### 4.3 Iteración semanal

Doc 08 (weekly loop Daniel) detalla. Resumen:

```bash
# Daniel agrega una metodología nueva tras el lunes
php artisan kb:add:methodology

# Daniel marca un plan real como "exitoso, usar como template"
php artisan kb:capture:template --client=78 --plan-type=entrenamiento --quality=85

# Re-genera embeddings si cambia el corpus
php artisan kb:reindex
```

---

## 5. Sincronización local ↔ portabilidad

### 5.1 Backup local

Daniel corre semanalmente (o lo automatiza con un cron task):

```bash
# Export del DATA (no estructura — eso vive en git)
mysqldump --no-create-info --skip-triggers wellcore_kb \
  | gzip \
  | gpg --symmetric --cipher-algo AES256 \
  > storage/wellcore-kb-seed-$(date +%Y%m%d).sql.gz.gpg
git add storage/wellcore-kb-seed-*.sql.gz.gpg
git commit -m "kb: snapshot $(date +%Y-%m-%d)"
```

El `.gpg` mantiene seguro el contenido aún en repo público. Daniel guarda la passphrase fuera del repo (1Password, etc.).

### 5.2 Restore en laptop nueva o VPS

```bash
git clone .../wellcore-laravel
cd wellcore-laravel
php artisan migrate --database=kb --path=database/migrations-kb
gpg -d storage/wellcore-kb-seed-latest.sql.gz.gpg | gunzip | mysql wellcore_kb
```

### 5.3 Si en el futuro se quiere portar a un VPS

- Cambiar `KB_DB_HOST` en `.env` del nuevo entorno.
- El código del motor v2 NO requiere refactor — usa la conexión `kb` definida en `config/database.php`.
- Los Sprint 6+ pueden agregar replicación si Daniel + un coach trabajan en paralelo desde laptops distintas (probablemente no necesario el primer año).

---

## 6. Convenciones de schema (diferencias vs `wellcore_fitness`)

| Aspecto | `wellcore_fitness` (prod) | `wellcore_kb` (local) |
|---------|---------------------------|------------------------|
| Origin | Creada por vanilla PHP, Laravel solo agrega aditivo | 100% Laravel migrations desde el día 1 |
| Naming de índices | Mixto (vanilla + Laravel) — algunos sin convención | Laravel-style: `{table}_{column}_index` |
| Timestamps | `created_at` solo, no `updated_at` | Laravel timestamps completos (`created_at`, `updated_at`) |
| FK | Algunas con CASCADE, otras SET NULL, inconsistente | Estándar: CASCADE para owners, SET NULL para referencias |
| Strict mode | OFF en algunas migraciones (`SET sql_mode=''`) — permite valores ENUM vacíos | ON — rechaza valores inválidos en INSERT |
| Charset | `utf8mb4_0900_ai_ci` (MySQL 8 default) | `utf8mb4_unicode_ci` (compatible MySQL 5.7+) |

**Por qué `utf8mb4_unicode_ci` en kb**: si en algún momento Daniel quiere correr el motor en una versión vieja de MySQL (5.7 en algún VPS legacy), unicode_ci es compatible. `0900_ai_ci` es MySQL 8 only.

---

## 7. Lo que NO está resuelto en este doc

1. **Modelos Eloquent**: este doc define schema. Los modelos PHP (`app/Models/Kb/Methodology.php`, etc.) los crea el Sprint 0 — la-02-backend agent los implementa siguiendo este spec.
2. **Validación cruzada con `wellcore_fitness`**: ¿`exercise_metadata.alias` debe estar en `exercise_aliases.gif_filename` de prod? La respuesta es **sí pero opcional** (no es FK porque cruza DBs). El comando `php artisan kb:verify-gifs` checkea inconsistencias post-seed.
3. ~~**`ai_generation_id` de `assigned_plans`**~~ ✅ **DECIDIDO 2026-05-16**: el motor v2 deja `ai_generation_id` NULL siempre. Si se necesita trazabilidad futura, se agrega columna `plan_engine_run_id` separada — el doc 04 detalla el schema de `plan_engine_runs`.
4. **Performance de queries**: ¿Cuál es el target de latencia para SELECT stage? Si el linter del Sprint 6 corre contra 200 lint_rules y cada plan tarda 2s en validar, el flujo se nota lento. Sprint 1 incluye benchmark básico.
5. **Multi-tenant**: el motor v2 es uso interno (Daniel + 1-2 coaches). Si en el futuro WellCore vende el motor a otros estudios, hace falta `tenant_id` en casi todas las tablas. Por ahora NO se agrega — premature.
6. **Backup automático**: el §5.1 muestra el script manual. Convertirlo en hook de Claude Code (`/kb-snapshot` slash command) lo cubre el doc 08.

## Próximo doc

**`04-stages-architecture.md`** — Las 6 stages WellCore en detalle:
- DTOs in/out tipados de cada stage (PHP 8.4 readonly classes).
- Errores que cada stage puede arrojar.
- Cómo se aíslan los fallos (orchestrator dueño de handles, stages puras).
- Estructura de la tabla `plan_engine_runs` para observability.
- Decisión sobre `ai_generation_id` (pendiente del doc 02 §7).
- Tests por stage.

Espero OK de Daniel para avanzar al doc 04.
