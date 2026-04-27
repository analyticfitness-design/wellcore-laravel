# Coach Strategy Hub Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir la pestaña `/coach/strategy` con drops semanales personalizados por coach, onboarding de Brand Profile, queue admin de aprobación, y el sistema offline de MDs en `C:\Users\GODSF\Downloads\SISTEMA-CREACION-MARKETING-COACHES\` que alimenta la generación.

**Architecture:** 12 módulos ordenados por dependencia. Foundations → DB → Domain → Auth → MDs offline → API → Frontend Foundation → Onboarding/Strategy/Admin UIs → Operations. TDD estricto, commits frecuentes. Tipos PHP estrictos, DTOs final readonly, JSON Schema validator server-side, máquina de estados explícita, IDOR-proof por Policy. Vue 3 + TS estricto, Pinia store, components <250 líneas, atmosphere layer.

**Tech Stack:** PHP 8.4 + Laravel 13.1, MySQL 8, Eloquent, opis/json-schema, Vue 3.5 + TypeScript estricto, Pinia 2, Vue Router 4, Tailwind CSS 4, Vite 8, Pest 3, Fraunces (font), html-to-image (npm).

**Spec source:** `docs/superpowers/specs/2026-04-26-coach-strategy-hub-design.md` (commit `c12c0311`).

---

## Mapa de módulos y dependencias

```
                    ┌──────────────────────┐
                    │ M0: Foundations      │ config, schema artifact, deps
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │ M1: Database         │ 3 migraciones + models + factories
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │ M2: Domain           │ enums + DTOs + validator + state machine
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │ M3: Authorization    │ Policies + FormRequests + middleware
                    └──────────┬───────────┘
                               │
            ┌──────────────────┼──────────────────┐
            │                  │                  │
   ┌────────▼─────┐   ┌────────▼─────┐   ┌───────▼─────────┐
   │ M4: MDs      │   │ M5: API Coach│   │ M6: API Admin   │
   │ offline      │   │              │   │                 │
   └──────────────┘   └────────┬─────┘   └───────┬─────────┘
                               │                  │
                               └────────┬─────────┘
                                        │
                          ┌─────────────▼─────────────┐
                          │ M7: Frontend Foundation   │ tokens + types + store + router
                          └─────────────┬─────────────┘
                                        │
                ┌───────────────────────┼───────────────────────┐
                │                       │                       │
       ┌────────▼─────────┐  ┌──────────▼─────────┐  ┌──────────▼─────────┐
       │ M8: Onboarding   │  │ M9: Strategy Hub   │  │ M10: Frontend Admin│
       └────────┬─────────┘  └──────────┬─────────┘  └──────────┬─────────┘
                │                       │                       │
                └───────────────────────┼───────────────────────┘
                                        │
                          ┌─────────────▼─────────────┐
                          │ M11: Operations & E2E     │ cron + integration + deploy
                          └───────────────────────────┘
```

**Paralelización posible:**
- M5 + M6 después de M3
- M4 después de M2 (independiente del resto)
- M8 + M9 + M10 después de M7

---

## Convenciones del plan

- **Paths**: relativos a la raíz del repo (`C:\Users\GODSF\Herd\wellcore-laravel`).
- **Commits**: cada tarea termina con commit; mensajes en español, sin emojis, prefijo conventional (`feat`, `test`, `docs`, `refactor`, `chore`).
- **Tests**: Pest (descubre tests en `tests/` automáticamente). Comando base: `vendor/bin/pest --filter '{TestName}'`.
- **Migrations**: `php artisan make:migration` + edits + `php artisan migrate`.
- **Si Herd está abajo**: el path del PHP CLI es `C:\Users\GODSF\.config\herd\bin\php.bat` (Windows). En el plan asumimos que Herd está corriendo y `php` está en PATH.
- **MD offline files** van a `C:\Users\GODSF\Downloads\SISTEMA-CREACION-MARKETING-COACHES\`, NO al repo. Sin git commit.

---

# MÓDULO 0 — Foundations

**Deps:** ninguna
**Output:** config files + JSON Schema formal + dependencias instaladas + Fraunces font + feature flag

**Files affected:**
- Create: `config/marketing.php`
- Modify: `config/features.php` (o crearlo si no existe)
- Create: `schemas/coach_drop_v1.schema.json`
- Modify: `composer.json` (+ `composer.lock`)
- Modify: `package.json` (+ `package-lock.json`)
- Modify: `resources/css/app.css` (Fraunces import)

---

### Task 0.1: Crear `config/marketing.php`

**Files:**
- Create: `config/marketing.php`

- [ ] **Step 1: Escribir test que valida config carga**

Create `tests/Unit/Config/MarketingConfigTest.php`:

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('loads marketing.attribution.line from config', function () {
    expect(config('marketing.attribution.line'))
        ->toBe('Por Daniel · Equipo Estrategia WellCore');
});

it('respects MARKETING_ATTRIBUTION_LINE env override', function () {
    config(['marketing.attribution.line' => 'Override Line']);
    expect(config('marketing.attribution.line'))->toBe('Override Line');
});
```

- [ ] **Step 2: Run test — debe fallar (config no existe)**

Run: `vendor/bin/pest --filter MarketingConfigTest`
Expected: FAIL `Undefined index "marketing"` o similar.

- [ ] **Step 3: Crear `config/marketing.php`**

```php
<?php

declare(strict_types=1);

return [
    'attribution' => [
        'line' => env('MARKETING_ATTRIBUTION_LINE', 'Por Daniel · Equipo Estrategia WellCore'),
    ],
];
```

- [ ] **Step 4: Run test — pasa**

Run: `vendor/bin/pest --filter MarketingConfigTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add config/marketing.php tests/Unit/Config/MarketingConfigTest.php
git commit -m "feat(marketing): config attribution line con override por env"
```

---

### Task 0.2: Feature flag `FEATURE_COACH_STRATEGY_ENABLED`

**Files:**
- Modify or create: `config/features.php`
- Modify: `.env.example`

- [ ] **Step 1: Verificar si `config/features.php` existe**

Run: `ls config/features.php 2>/dev/null && echo EXISTS || echo MISSING`

- [ ] **Step 2: Si MISSING, crear con bloque base; si EXISTS, agregar key**

```php
<?php

declare(strict_types=1);

return [
    'coach_strategy_enabled' => env('FEATURE_COACH_STRATEGY_ENABLED', false),
];
```

(Si ya existe, hacer Edit para agregar la key `coach_strategy_enabled => env(...)` en el array.)

- [ ] **Step 3: Agregar a `.env.example`**

```
FEATURE_COACH_STRATEGY_ENABLED=false
```

- [ ] **Step 4: Test que valida feature flag default false**

Create `tests/Unit/Config/FeaturesConfigTest.php`:

```php
<?php

declare(strict_types=1);

it('coach_strategy_enabled defaults to false', function () {
    expect(config('features.coach_strategy_enabled'))->toBeFalse();
});
```

Run: `vendor/bin/pest --filter FeaturesConfigTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add config/features.php .env.example tests/Unit/Config/FeaturesConfigTest.php
git commit -m "feat(features): flag coach_strategy_enabled default false"
```

---

### Task 0.3: Instalar `opis/json-schema`

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Agregar dependencia**

Run: `composer require opis/json-schema:^2.3`

- [ ] **Step 2: Verificar instalación**

Run: `composer show opis/json-schema`
Expected: muestra versión 2.3.x.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore(deps): opis/json-schema para validacion de coach_drop_v1"
```

---

### Task 0.4: Instalar `html-to-image` (npm)

**Files:**
- Modify: `package.json`

- [ ] **Step 1: Instalar**

Run: `npm install html-to-image@^1.11`

- [ ] **Step 2: Verificar**

Run: `npm ls html-to-image`
Expected: muestra versión 1.11.x.

- [ ] **Step 3: Commit**

```bash
git add package.json package-lock.json
git commit -m "chore(deps): html-to-image para PNG export de stories"
```

---

### Task 0.5: Instalar `json-schema-to-typescript` (devDep)

- [ ] **Step 1: Instalar**

Run: `npm install --save-dev json-schema-to-typescript@^15`

- [ ] **Step 2: Agregar script `gen:schema-types` a package.json**

Edit `package.json` agregando en `"scripts"`:

```json
"gen:schema-types": "json2ts -i schemas/coach_drop_v1.schema.json -o resources/js/vue/types/coach-drop-v1.generated.ts --bannerComment '// AUTO-GENERATED. Do not edit. Run npm run gen:schema-types.'"
```

- [ ] **Step 3: Commit**

```bash
git add package.json package-lock.json
git commit -m "chore(deps): json-schema-to-typescript + script gen:schema-types"
```

---

### Task 0.6: Crear `schemas/coach_drop_v1.schema.json`

**Files:**
- Create: `schemas/coach_drop_v1.schema.json`

- [ ] **Step 1: Crear el archivo JSON Schema completo**

```json
{
  "$schema": "https://json-schema.org/draft-07/schema#",
  "$id": "https://wellcorefitness.com/schemas/coach_drop_v1.json",
  "title": "Coach Drop V1",
  "type": "object",
  "required": ["schema_version", "brief", "reels", "stories", "checklist", "bank", "hashtags"],
  "additionalProperties": false,
  "properties": {
    "schema_version": { "const": "coach_drop_v1" },
    "brief": {
      "type": "object",
      "required": ["title", "objective", "priority_offer", "key_message", "target_metric", "weekly_theme", "framing_copy"],
      "additionalProperties": false,
      "properties": {
        "title": { "type": "string", "minLength": 1, "maxLength": 120 },
        "objective": { "type": "string", "minLength": 1, "maxLength": 500 },
        "priority_offer": { "enum": ["esencial", "metodo", "elite", "presencial", "otro"] },
        "key_message": { "type": "string", "minLength": 1, "maxLength": 280 },
        "target_metric": { "type": "string", "minLength": 1, "maxLength": 200 },
        "weekly_theme": { "type": "string", "minLength": 1, "maxLength": 120 },
        "framing_copy": { "type": "string", "minLength": 1, "maxLength": 400 }
      }
    },
    "reels": {
      "type": "array",
      "minItems": 2,
      "maxItems": 2,
      "items": { "$ref": "#/$defs/reelScript" }
    },
    "stories": {
      "type": "array",
      "minItems": 7,
      "maxItems": 7,
      "items": { "$ref": "#/$defs/storyDay" }
    },
    "checklist": {
      "type": "object",
      "required": ["phases"],
      "properties": {
        "phases": {
          "type": "array",
          "minItems": 4,
          "maxItems": 4,
          "items": { "$ref": "#/$defs/checklistPhase" }
        }
      }
    },
    "bank": {
      "type": "object",
      "required": ["alt_hooks", "alt_ctas", "alt_captions"],
      "properties": {
        "alt_hooks":    { "type": "array", "minItems": 5, "maxItems": 5, "items": { "type": "string", "minLength": 1, "maxLength": 240 } },
        "alt_ctas":     { "type": "array", "minItems": 3, "maxItems": 3, "items": { "type": "string", "minLength": 1, "maxLength": 240 } },
        "alt_captions": { "type": "array", "minItems": 3, "maxItems": 3, "items": { "type": "string", "minLength": 1, "maxLength": 1000 } }
      }
    },
    "hashtags": {
      "type": "object",
      "required": ["sets"],
      "properties": {
        "sets": {
          "type": "array",
          "minItems": 1,
          "maxItems": 6,
          "items": {
            "type": "object",
            "required": ["name", "tags"],
            "properties": {
              "name": { "type": "string", "minLength": 1, "maxLength": 80 },
              "tags": { "type": "array", "minItems": 1, "maxItems": 30, "items": { "type": "string", "pattern": "^#[A-Za-z0-9_]+$", "maxLength": 60 } }
            }
          }
        }
      }
    }
  },
  "$defs": {
    "reelScript": {
      "type": "object",
      "required": ["key", "type", "title", "format_meta", "hook", "timecode_table", "caption", "music_note", "production_notes"],
      "properties": {
        "key": { "enum": ["reel_1", "reel_2"] },
        "type": { "enum": ["educativo", "conversion"] },
        "title": { "type": "string", "minLength": 1, "maxLength": 160 },
        "format_meta": {
          "type": "object",
          "required": ["duration_sec_min", "duration_sec_max", "platforms", "bpm_hint"],
          "properties": {
            "duration_sec_min": { "type": "integer", "minimum": 8, "maximum": 90 },
            "duration_sec_max": { "type": "integer", "minimum": 8, "maximum": 90 },
            "platforms": { "type": "array", "minItems": 1, "items": { "enum": ["instagram", "tiktok", "youtube"] } },
            "bpm_hint": { "type": "string", "maxLength": 30 }
          }
        },
        "hook": {
          "type": "object",
          "required": ["text", "rationale"],
          "properties": {
            "text": { "type": "string", "minLength": 1, "maxLength": 240 },
            "rationale": { "type": "string", "minLength": 1, "maxLength": 400 }
          }
        },
        "timecode_table": {
          "type": "array", "minItems": 3, "maxItems": 12,
          "items": {
            "type": "object",
            "required": ["time", "dialogue", "visual", "edit_notes"],
            "properties": {
              "time": { "type": "string", "pattern": "^\\d{2}:\\d{2}-\\d{2}:\\d{2}$" },
              "dialogue": { "type": "string", "minLength": 1, "maxLength": 600 },
              "visual": { "type": "string", "minLength": 1, "maxLength": 400 },
              "edit_notes": { "type": "string", "minLength": 1, "maxLength": 600 }
            }
          }
        },
        "caption": { "type": "string", "minLength": 1, "maxLength": 2200 },
        "music_note": { "type": "string", "minLength": 1, "maxLength": 300 },
        "production_notes": { "type": "string", "minLength": 1, "maxLength": 600 }
      }
    },
    "storyDay": {
      "type": "object",
      "required": ["day", "pillar", "slides", "dm_followup_hint"],
      "properties": {
        "day": { "enum": ["LUN", "MAR", "MIE", "JUE", "VIE", "SAB", "DOM"] },
        "pillar": { "enum": ["activacion", "nutricion", "spotlight", "bts", "qa", "motivacion", "reset"] },
        "slides": {
          "type": "array", "minItems": 1, "maxItems": 3,
          "items": {
            "type": "object",
            "required": ["kind", "text", "visual_hint", "sticker"],
            "properties": {
              "kind": { "enum": ["text", "template", "visual"] },
              "text": { "type": "string", "minLength": 1, "maxLength": 800 },
              "visual_hint": { "type": "string", "minLength": 1, "maxLength": 400 },
              "sticker": { "enum": ["poll", "slider", "question", "none"] }
            }
          }
        },
        "dm_followup_hint": { "type": "string", "maxLength": 400 }
      }
    },
    "checklistPhase": {
      "type": "object",
      "required": ["key", "title", "items"],
      "properties": {
        "key": { "enum": ["pre", "cam", "edit", "pub"] },
        "title": { "type": "string", "minLength": 1, "maxLength": 80 },
        "items": {
          "type": "array", "minItems": 1, "maxItems": 20,
          "items": {
            "type": "object",
            "required": ["title", "detail"],
            "properties": {
              "title": { "type": "string", "minLength": 1, "maxLength": 200 },
              "detail": { "type": "string", "minLength": 1, "maxLength": 400 },
              "subitems": { "type": "array", "items": { "type": "string", "maxLength": 200 }, "maxItems": 6 }
            }
          }
        }
      }
    }
  }
}
```

- [ ] **Step 2: Validar JSON sintáctico**

Run: `php -r "json_decode(file_get_contents('schemas/coach_drop_v1.schema.json'), true); echo json_last_error_msg();"`
Expected: `No error`.

- [ ] **Step 3: Commit**

```bash
git add schemas/coach_drop_v1.schema.json
git commit -m "feat(schemas): coach_drop_v1.schema.json formal JSON Schema"
```

---

### Task 0.7: Agregar Fraunces Italic a `resources/css/app.css`

**Files:**
- Modify: `resources/css/app.css`

- [ ] **Step 1: Editar app.css agregando import al inicio (después de `@import "tailwindcss";`)**

Old:
```css
@import "tailwindcss";
```

New:
```css
@import "tailwindcss";
@import url("https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@1,300;1,400;1,500&display=swap");
```

- [ ] **Step 2: Agregar token al `@theme` block**

Buscar el bloque `@theme` y agregar:

```css
--font-editorial: "Fraunces", ui-serif, Georgia, serif;
--color-wc-gold: #C8A769;
```

- [ ] **Step 3: Build assets**

Run: `npm run build`
Expected: build sin errores.

- [ ] **Step 4: Commit**

```bash
git add resources/css/app.css public/build
git commit -m "feat(design): Fraunces Italic + token wc-gold para Strategy Hub"
```

---

### Task 0.8: Sello del módulo M0

- [ ] **Step 1: Verificar que todos los pasos del módulo pasan**

Run: `vendor/bin/pest --filter Config`
Expected: 3 tests passing (MarketingConfig 2 + FeaturesConfig 1).

- [ ] **Step 2: Tag de avance**

Run: `git tag -a m0-foundations -m "Modulo 0 completo"`

---

# MÓDULO 1 — Database Layer

**Deps:** M0
**Output:** 3 migraciones aditivas + 3 modelos Eloquent + 3 factories.

**Files affected:**
- Create: `database/migrations/2026_04_26_000001_create_coach_marketing_profiles_table.php`
- Create: `database/migrations/2026_04_26_000002_create_coach_content_drops_table.php`
- Create: `database/migrations/2026_04_26_000003_create_coach_content_piece_states_table.php`
- Create: `app/Models/CoachMarketingProfile.php`
- Create: `app/Models/CoachContentDrop.php`
- Create: `app/Models/CoachContentPieceState.php`
- Create: `database/factories/CoachMarketingProfileFactory.php`
- Create: `database/factories/CoachContentDropFactory.php`
- Create: `database/factories/CoachContentPieceStateFactory.php`

---

### Task 1.1: Migración `coach_marketing_profiles`

**Files:**
- Create: `database/migrations/2026_04_26_000001_create_coach_marketing_profiles_table.php`

- [ ] **Step 1: Generar archivo**

Run: `php artisan make:migration create_coach_marketing_profiles_table --create=coach_marketing_profiles`

(Renombrar el archivo generado al timestamp `2026_04_26_000001_*` si Laravel usó otro timestamp, para garantizar orden.)

- [ ] **Step 2: Implementar `up()`**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_marketing_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coach_id')->unique();

            // Identidad
            $table->string('brand_name', 120);
            $table->string('city', 80)->nullable();
            $table->char('country_code', 2)->nullable();

            // Especialidad
            $table->enum('specialty_primary', [
                'fuerza','hipertrofia','recomposicion',
                'perdida_grasa','mujeres_postparto','funcional','otro'
            ]);
            $table->string('specialty_primary_other', 80)->nullable();
            $table->enum('specialty_secondary', [
                'fuerza','hipertrofia','recomposicion',
                'perdida_grasa','mujeres_postparto','funcional','otro'
            ])->nullable();
            $table->string('specialty_secondary_other', 80)->nullable();
            $table->text('differentiator');

            // Audiencia
            $table->enum('audience_age_range', ['18-25','25-35','35-45','45+']);
            $table->enum('audience_gender', ['mujeres','hombres','mixto']);
            $table->string('audience_pain_main', 200);
            $table->enum('audience_offer_main', ['esencial','metodo','elite','presencial','otro']);

            // Metodologías y temas
            $table->json('preferred_methodologies');
            $table->json('preferred_methodologies_other')->nullable();
            $table->json('content_topics');
            $table->json('content_topics_other')->nullable();

            // Voz
            $table->json('voice_adjectives');
            $table->json('voice_samples')->nullable();

            // Ofertas
            $table->json('active_offers');

            // Top posts
            $table->json('top_working_posts')->nullable();

            // Meta
            $table->timestamp('completed_at')->nullable()->index();
            $table->enum('last_updated_by', ['coach','admin']);
            $table->unsignedBigInteger('last_admin_editor_id')->nullable();

            $table->timestamps();

            $table->foreign('coach_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Aditivo: NO drop salvo en rollback explicito local.
        Schema::dropIfExists('coach_marketing_profiles');
    }
};
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: `Migrated: 2026_04_26_000001_create_coach_marketing_profiles_table`.

- [ ] **Step 4: Test de existencia**

Create `tests/Feature/Database/CoachMarketingProfilesTableTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('coach_marketing_profiles table exists with required columns', function () {
    expect(Schema::hasTable('coach_marketing_profiles'))->toBeTrue();

    $cols = ['coach_id','brand_name','specialty_primary','differentiator',
             'audience_age_range','audience_offer_main','preferred_methodologies',
             'voice_adjectives','active_offers','completed_at','last_updated_by'];

    foreach ($cols as $col) {
        expect(Schema::hasColumn('coach_marketing_profiles', $col))
            ->toBeTrue("column {$col} missing");
    }
});

it('coach_id has unique constraint', function () {
    $indexes = collect(Schema::getIndexes('coach_marketing_profiles'));
    expect($indexes->contains(fn($i) => in_array('coach_id', $i['columns']) && $i['unique']))
        ->toBeTrue();
});
```

Run: `vendor/bin/pest --filter CoachMarketingProfilesTableTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_26_000001_create_coach_marketing_profiles_table.php tests/Feature/Database/CoachMarketingProfilesTableTest.php
git commit -m "feat(db): tabla coach_marketing_profiles (FK admins)"
```

---

### Task 1.2: Migración `coach_content_drops`

- [ ] **Step 1: Generar**

Run: `php artisan make:migration create_coach_content_drops_table --create=coach_content_drops`

(Renombrar timestamp a `2026_04_26_000002_*`.)

- [ ] **Step 2: Implementar**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_content_drops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coach_id');

            // Calendario
            $table->unsignedSmallInteger('iso_year');
            $table->unsignedTinyInteger('iso_week');
            $table->date('week_starts_on');

            // Estado
            $table->enum('status', [
                'pending','generating','in_review',
                'approved','ready','in_progress',
                'completed','archived'
            ])->default('pending');

            // Contenido
            $table->json('content');
            $table->json('intake_snapshot');
            $table->string('schema_version', 20)->default('coach_drop_v1');

            // Audit
            $table->string('generated_by_session_id', 80)->nullable();
            $table->json('original_content')->nullable();
            $table->json('admin_edits_diff')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by_id')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['coach_id','iso_year','iso_week'], 'uniq_coach_week');
            $table->index(['status','iso_year','iso_week'], 'idx_status_week');

            $table->foreign('coach_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_content_drops');
    }
};
```

- [ ] **Step 3: Migrate**

Run: `php artisan migrate`

- [ ] **Step 4: Test**

Create `tests/Feature/Database/CoachContentDropsTableTest.php`:

```php
<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Schema;

it('coach_content_drops exists with all required columns', function () {
    expect(Schema::hasTable('coach_content_drops'))->toBeTrue();
    $cols = ['coach_id','iso_year','iso_week','week_starts_on','status','content',
             'intake_snapshot','schema_version','original_content','admin_edits_diff'];
    foreach ($cols as $c) {
        expect(Schema::hasColumn('coach_content_drops', $c))->toBeTrue("missing {$c}");
    }
});

it('has unique (coach_id, iso_year, iso_week)', function () {
    $idx = collect(Schema::getIndexes('coach_content_drops'));
    expect($idx->contains(fn($i) => $i['name'] === 'uniq_coach_week' && $i['unique']))
        ->toBeTrue();
});
```

Run: `vendor/bin/pest --filter CoachContentDropsTableTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_26_000002_create_coach_content_drops_table.php tests/Feature/Database/CoachContentDropsTableTest.php
git commit -m "feat(db): tabla coach_content_drops con unique semana"
```

---

### Task 1.3: Migración `coach_content_piece_states`

- [ ] **Step 1: Generar y ajustar timestamp a `2026_04_26_000003_*`**

Run: `php artisan make:migration create_coach_content_piece_states_table --create=coach_content_piece_states`

- [ ] **Step 2: Implementar**

```php
<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_content_piece_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('drop_id');
            $table->unsignedBigInteger('coach_id');

            $table->enum('piece_type', ['reel','story','checklist_phase']);
            $table->string('piece_key', 40);

            $table->enum('state', ['pending','in_progress','published','skipped'])->default('pending');
            $table->string('published_url', 500)->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('state_changed_at')->nullable();
            $table->timestamps();

            $table->unique(['drop_id','piece_type','piece_key'], 'uniq_piece');
            $table->foreign('drop_id')->references('id')->on('coach_content_drops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_content_piece_states');
    }
};
```

- [ ] **Step 3: Migrate + Test (`Schema::hasTable` + `uniq_piece`)**

Run: `php artisan migrate` + crear `tests/Feature/Database/CoachContentPieceStatesTableTest.php` similar a anteriores. Run: `vendor/bin/pest --filter CoachContentPieceStatesTableTest`. Expected: PASS.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_04_26_000003_create_coach_content_piece_states_table.php tests/Feature/Database/CoachContentPieceStatesTableTest.php
git commit -m "feat(db): tabla coach_content_piece_states"
```

---

### Task 1.4: Modelo `CoachMarketingProfile`

**Files:**
- Create: `app/Models/CoachMarketingProfile.php`

- [ ] **Step 1: Test rojo**

Create `tests/Feature/Models/CoachMarketingProfileTest.php`:

```php
<?php
declare(strict_types=1);
use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use App\Enums\UserRole;

it('creates profile with array casts working', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Coach]);
    $profile = CoachMarketingProfile::create([
        'coach_id' => $admin->id,
        'brand_name' => 'Andrea Vega',
        'specialty_primary' => 'fuerza',
        'differentiator' => 'Coach técnica',
        'audience_age_range' => '25-35',
        'audience_gender' => 'mujeres',
        'audience_pain_main' => 'No baja de peso',
        'audience_offer_main' => 'metodo',
        'preferred_methodologies' => ['sobrecarga_progresiva','deficit_calorico'],
        'content_topics' => ['mitos_fitness','transformaciones'],
        'voice_adjectives' => ['directo','tecnico','cercano'],
        'active_offers' => [['name'=>'Método','price'=>120,'currency'=>'USD','promo'=>null]],
        'last_updated_by' => 'coach',
    ]);
    expect($profile->preferred_methodologies)->toBeArray()->toContain('deficit_calorico');
    expect($profile->voice_adjectives)->toHaveCount(3);
});
```

Run: `vendor/bin/pest --filter CoachMarketingProfileTest`
Expected: FAIL (`Class CoachMarketingProfile not found`).

- [ ] **Step 2: Implementar el modelo**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\LastUpdatedBy;
use App\Enums\Marketing\SpecialtyPrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CoachMarketingProfile extends Model
{
    use HasFactory;

    protected $table = 'coach_marketing_profiles';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'specialty_primary' => SpecialtyPrimary::class,
            'specialty_secondary' => SpecialtyPrimary::class,
            'audience_age_range' => AudienceAgeRange::class,
            'audience_gender' => AudienceGender::class,
            'audience_offer_main' => AudienceOfferMain::class,
            'preferred_methodologies' => 'array',
            'preferred_methodologies_other' => 'array',
            'content_topics' => 'array',
            'content_topics_other' => 'array',
            'voice_adjectives' => 'array',
            'voice_samples' => 'array',
            'active_offers' => 'array',
            'top_working_posts' => 'array',
            'completed_at' => 'datetime',
            'last_updated_by' => LastUpdatedBy::class,
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }
}
```

> **Note:** los enums de `App\Enums\Marketing\*` se crean en M2. Este test fallará temporalmente hasta entonces. Usar `'string'` cast como stub si se necesita correr el test antes; o saltar este test al final del módulo M2.

- [ ] **Step 3: Stub temporal de enums**

Para no bloquear, crear stubs vacíos (se completan en M2):

```bash
mkdir -p app/Enums/Marketing
```

Crear archivos stub con cuerpo mínimo (todos `enum X: string { /* casos en M2 */ }`).

Mejor enfoque: **mover el test del modelo al final de M2** y aquí solo crear el modelo + correr `php artisan tinker --execute='new App\Models\CoachMarketingProfile;'`.

Decisión: **mover test al M2 (Task 2.X) cuando enums existan**.

- [ ] **Step 4: Verificar carga del modelo**

Run: `php artisan tinker --execute='dump((new App\Models\CoachMarketingProfile)->getTable());'`
Expected: imprime `coach_marketing_profiles`.

- [ ] **Step 5: Commit**

```bash
git add app/Models/CoachMarketingProfile.php
git commit -m "feat(models): CoachMarketingProfile con casts a enums Marketing"
```

---

### Task 1.5: Modelo `CoachContentDrop`

**Files:**
- Create: `app/Models/CoachContentDrop.php`

- [ ] **Step 1: Implementar**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Marketing\DropStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CoachContentDrop extends Model
{
    use HasFactory;

    protected $table = 'coach_content_drops';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'iso_year' => 'integer',
            'iso_week' => 'integer',
            'week_starts_on' => 'date',
            'status' => DropStatus::class,
            'content' => 'array',
            'intake_snapshot' => 'array',
            'original_content' => 'array',
            'admin_edits_diff' => 'array',
            'generated_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by_id');
    }

    public function pieceStates(): HasMany
    {
        return $this->hasMany(CoachContentPieceState::class, 'drop_id');
    }
}
```

- [ ] **Step 2: Verificar carga**

Run: `php artisan tinker --execute='dump((new App\Models\CoachContentDrop)->getCasts());'`
Expected: imprime array con casts incluyendo `status => App\Enums\Marketing\DropStatus`.

- [ ] **Step 3: Commit**

```bash
git add app/Models/CoachContentDrop.php
git commit -m "feat(models): CoachContentDrop con casts JSON + enum DropStatus"
```

---

### Task 1.6: Modelo `CoachContentPieceState`

- [ ] **Step 1: Implementar**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Marketing\PieceState;
use App\Enums\Marketing\PieceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CoachContentPieceState extends Model
{
    use HasFactory;

    protected $table = 'coach_content_piece_states';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'piece_type' => PieceType::class,
            'state' => PieceState::class,
            'state_changed_at' => 'datetime',
        ];
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(CoachContentDrop::class, 'drop_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/CoachContentPieceState.php
git commit -m "feat(models): CoachContentPieceState"
```

---

### Task 1.7: Factories

**Files:**
- Create: `database/factories/CoachMarketingProfileFactory.php`
- Create: `database/factories/CoachContentDropFactory.php`
- Create: `database/factories/CoachContentPieceStateFactory.php`

- [ ] **Step 1: `CoachMarketingProfileFactory`**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CoachMarketingProfileFactory extends Factory
{
    protected $model = CoachMarketingProfile::class;

    public function definition(): array
    {
        return [
            'coach_id' => Admin::factory()->state(['role' => UserRole::Coach]),
            'brand_name' => fake()->name(),
            'city' => fake()->city(),
            'country_code' => 'CO',
            'specialty_primary' => 'fuerza',
            'differentiator' => fake()->sentence(8),
            'audience_age_range' => '25-35',
            'audience_gender' => 'mixto',
            'audience_pain_main' => fake()->sentence(6),
            'audience_offer_main' => 'metodo',
            'preferred_methodologies' => ['sobrecarga_progresiva','deficit_calorico'],
            'content_topics' => ['mitos_fitness','transformaciones'],
            'voice_adjectives' => ['directo','tecnico','cercano'],
            'active_offers' => [['name'=>'Método','price'=>120,'currency'=>'USD','promo'=>null]],
            'last_updated_by' => 'coach',
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(['completed_at' => now()]);
    }
}
```

- [ ] **Step 2: `CoachContentDropFactory`**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CoachContentDropFactory extends Factory
{
    protected $model = CoachContentDrop::class;

    public function definition(): array
    {
        $monday = now()->startOfWeek();
        return [
            'coach_id' => Admin::factory()->state(['role' => UserRole::Coach]),
            'iso_year' => (int) $monday->isoFormat('GGGG'),
            'iso_week' => (int) $monday->isoFormat('W'),
            'week_starts_on' => $monday->toDateString(),
            'status' => DropStatus::Pending,
            'content' => self::stubContent(),
            'intake_snapshot' => ['brand_name' => 'Stub'],
            'schema_version' => 'coach_drop_v1',
        ];
    }

    public function pending(): static  { return $this->state(['status' => DropStatus::Pending]); }
    public function inReview(): static { return $this->state(['status' => DropStatus::InReview, 'generated_at' => now()]); }
    public function ready(): static    { return $this->state(['status' => DropStatus::Ready, 'approved_at' => now(), 'ready_at' => now()]); }
    public function completed(): static{ return $this->state(['status' => DropStatus::Completed, 'completed_at' => now()]); }

    private static function stubContent(): array
    {
        // Mínimo válido contra schema, abreviado por espacio
        return [
            'schema_version' => 'coach_drop_v1',
            'brief' => [
                'title'=>'Brief stub','objective'=>'Stub objective','priority_offer'=>'metodo',
                'key_message'=>'Stub','target_metric'=>'Stub','weekly_theme'=>'Stub','framing_copy'=>'Stub'
            ],
            'reels' => [/* 2 reels minimos */],
            'stories' => [/* 7 stories LUN-DOM */],
            'checklist' => ['phases' => [/* 4 phases */]],
            'bank' => ['alt_hooks' => array_fill(0,5,'h'), 'alt_ctas' => array_fill(0,3,'c'), 'alt_captions' => array_fill(0,3,'cap')],
            'hashtags' => ['sets' => [['name'=>'set1','tags'=>['#a']]]],
        ];
    }
}
```

> **Nota:** los stubs reels/stories/phases vacíos NO pasan el JSON Schema. Se completarán a stubs válidos en Task 2.X cuando el validador esté escrito; mientras tanto, los tests que necesitan factory completa usarán datos válidos explícitos.

- [ ] **Step 3: `CoachContentPieceStateFactory`**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Marketing\PieceState;
use App\Enums\Marketing\PieceType;
use App\Models\CoachContentDrop;
use App\Models\CoachContentPieceState;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CoachContentPieceStateFactory extends Factory
{
    protected $model = CoachContentPieceState::class;

    public function definition(): array
    {
        return [
            'drop_id' => CoachContentDrop::factory(),
            'coach_id' => fn(array $a) => CoachContentDrop::find($a['drop_id'])->coach_id ?? 1,
            'piece_type' => PieceType::Reel,
            'piece_key' => 'reel_1',
            'state' => PieceState::Pending,
        ];
    }

    public function published(string $url = 'https://instagram.com/p/abc'): static
    {
        return $this->state([
            'state' => PieceState::Published,
            'published_url' => $url,
            'state_changed_at' => now(),
        ]);
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add database/factories/CoachMarketingProfileFactory.php database/factories/CoachContentDropFactory.php database/factories/CoachContentPieceStateFactory.php
git commit -m "feat(factories): factories de marketing con states comunes"
```

---

### Task 1.8: Sello del módulo M1

- [ ] **Step 1: Tag**

Run: `git tag -a m1-database -m "Modulo 1 completo"`

---

# MÓDULO 2 — Domain Layer

**Deps:** M1
**Output:** 8 enums tipados + 8 DTOs `final readonly` + DropSchemaValidator + DropStateMachine.

**Files affected:**
- Create: `app/Enums/Marketing/DropStatus.php`
- Create: `app/Enums/Marketing/PieceType.php`
- Create: `app/Enums/Marketing/PieceState.php`
- Create: `app/Enums/Marketing/SpecialtyPrimary.php`
- Create: `app/Enums/Marketing/AudienceAgeRange.php`
- Create: `app/Enums/Marketing/AudienceGender.php`
- Create: `app/Enums/Marketing/AudienceOfferMain.php`
- Create: `app/Enums/Marketing/LastUpdatedBy.php`
- Create: `app/DataTransferObjects/Marketing/CoachDropV1.php`
- Create: `app/DataTransferObjects/Marketing/BriefSection.php`
- Create: `app/DataTransferObjects/Marketing/ReelScript.php`
- Create: `app/DataTransferObjects/Marketing/ScriptTimecodeRow.php`
- Create: `app/DataTransferObjects/Marketing/StoryDay.php`
- Create: `app/DataTransferObjects/Marketing/StorySlide.php`
- Create: `app/DataTransferObjects/Marketing/ProductionChecklist.php`
- Create: `app/DataTransferObjects/Marketing/ChecklistPhase.php`
- Create: `app/DataTransferObjects/Marketing/ChecklistItem.php`
- Create: `app/DataTransferObjects/Marketing/WeeklyBank.php`
- Create: `app/DataTransferObjects/Marketing/HashtagSets.php`
- Create: `app/DataTransferObjects/Marketing/MarketingProfile.php`
- Create: `app/Services/Marketing/DropSchemaValidator.php`
- Create: `app/Services/Marketing/DropStateMachine.php`
- Create: `app/Exceptions/Marketing/InvalidDropTransition.php`
- Create: `app/Exceptions/Marketing/InvalidDropSchema.php`

---

### Task 2.1: Enum `DropStatus`

- [ ] **Step 1: Test**

Create `tests/Unit/Enums/Marketing/DropStatusTest.php`:

```php
<?php
declare(strict_types=1);
use App\Enums\Marketing\DropStatus;

it('returns true on isVisibleToCoach for ready/in_progress/completed/archived', function () {
    expect(DropStatus::Ready->isVisibleToCoach())->toBeTrue()
        ->and(DropStatus::InProgress->isVisibleToCoach())->toBeTrue()
        ->and(DropStatus::Completed->isVisibleToCoach())->toBeTrue()
        ->and(DropStatus::Archived->isVisibleToCoach())->toBeTrue();
});

it('returns false on isVisibleToCoach for pre-approval states', function () {
    expect(DropStatus::Pending->isVisibleToCoach())->toBeFalse()
        ->and(DropStatus::Generating->isVisibleToCoach())->toBeFalse()
        ->and(DropStatus::InReview->isVisibleToCoach())->toBeFalse()
        ->and(DropStatus::Approved->isVisibleToCoach())->toBeFalse();
});
```

Run: FAIL (clase no existe).

- [ ] **Step 2: Implementar**

```php
<?php

declare(strict_types=1);

namespace App\Enums\Marketing;

enum DropStatus: string
{
    case Pending = 'pending';
    case Generating = 'generating';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Ready = 'ready';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';

    public function isVisibleToCoach(): bool
    {
        return match ($this) {
            self::Ready, self::InProgress, self::Completed, self::Archived => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Generating => 'Generando',
            self::InReview => 'En revisión',
            self::Approved => 'Aprobado',
            self::Ready => 'Listo',
            self::InProgress => 'En progreso',
            self::Completed => 'Completado',
            self::Archived => 'Archivado',
        };
    }
}
```

- [ ] **Step 3: Run test — pasa. Commit**

```bash
git add app/Enums/Marketing/DropStatus.php tests/Unit/Enums/Marketing/DropStatusTest.php
git commit -m "feat(enums): DropStatus con isVisibleToCoach + label"
```

---

### Task 2.2: Enums `PieceType`, `PieceState`

- [ ] **Step 1: Crear `app/Enums/Marketing/PieceType.php`**

```php
<?php
declare(strict_types=1);
namespace App\Enums\Marketing;
enum PieceType: string {
    case Reel = 'reel';
    case Story = 'story';
    case ChecklistPhase = 'checklist_phase';
}
```

- [ ] **Step 2: Crear `app/Enums/Marketing/PieceState.php`**

```php
<?php
declare(strict_types=1);
namespace App\Enums\Marketing;
enum PieceState: string {
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Published = 'published';
    case Skipped = 'skipped';
}
```

- [ ] **Step 3: Test instanciación**

Create `tests/Unit/Enums/Marketing/PieceEnumsTest.php`:

```php
<?php
declare(strict_types=1);
use App\Enums\Marketing\PieceType;
use App\Enums\Marketing\PieceState;

it('PieceType has 3 cases', fn() => expect(count(PieceType::cases()))->toBe(3));
it('PieceState has 4 cases', fn() => expect(count(PieceState::cases()))->toBe(4));
```

Run: PASS.

- [ ] **Step 4: Commit**

```bash
git add app/Enums/Marketing/PieceType.php app/Enums/Marketing/PieceState.php tests/Unit/Enums/Marketing/PieceEnumsTest.php
git commit -m "feat(enums): PieceType + PieceState"
```

---

### Task 2.3: Enums de intake (`SpecialtyPrimary`, `AudienceAgeRange`, `AudienceGender`, `AudienceOfferMain`, `LastUpdatedBy`)

- [ ] **Step 1: Crear los 5 archivos**

`app/Enums/Marketing/SpecialtyPrimary.php`:
```php
<?php
declare(strict_types=1);
namespace App\Enums\Marketing;
enum SpecialtyPrimary: string {
    case Fuerza = 'fuerza';
    case Hipertrofia = 'hipertrofia';
    case Recomposicion = 'recomposicion';
    case PerdidaGrasa = 'perdida_grasa';
    case MujeresPostparto = 'mujeres_postparto';
    case Funcional = 'funcional';
    case Otro = 'otro';
}
```

`app/Enums/Marketing/AudienceAgeRange.php`:
```php
<?php
declare(strict_types=1);
namespace App\Enums\Marketing;
enum AudienceAgeRange: string {
    case A18_25 = '18-25';
    case A25_35 = '25-35';
    case A35_45 = '35-45';
    case A45Plus = '45+';
}
```

`app/Enums/Marketing/AudienceGender.php`:
```php
<?php
declare(strict_types=1);
namespace App\Enums\Marketing;
enum AudienceGender: string {
    case Mujeres = 'mujeres';
    case Hombres = 'hombres';
    case Mixto = 'mixto';
}
```

`app/Enums/Marketing/AudienceOfferMain.php`:
```php
<?php
declare(strict_types=1);
namespace App\Enums\Marketing;
enum AudienceOfferMain: string {
    case Esencial = 'esencial';
    case Metodo = 'metodo';
    case Elite = 'elite';
    case Presencial = 'presencial';
    case Otro = 'otro';
}
```

`app/Enums/Marketing/LastUpdatedBy.php`:
```php
<?php
declare(strict_types=1);
namespace App\Enums\Marketing;
enum LastUpdatedBy: string {
    case Coach = 'coach';
    case Admin = 'admin';
}
```

- [ ] **Step 2: Smoke test**

Create `tests/Unit/Enums/Marketing/IntakeEnumsTest.php`:

```php
<?php
declare(strict_types=1);
use App\Enums\Marketing\SpecialtyPrimary;
use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\LastUpdatedBy;

it('all intake enums load', function () {
    expect(SpecialtyPrimary::Fuerza->value)->toBe('fuerza')
        ->and(AudienceAgeRange::A25_35->value)->toBe('25-35')
        ->and(AudienceGender::Mujeres->value)->toBe('mujeres')
        ->and(AudienceOfferMain::Metodo->value)->toBe('metodo')
        ->and(LastUpdatedBy::Coach->value)->toBe('coach');
});
```

Run: PASS.

- [ ] **Step 3: Commit**

```bash
git add app/Enums/Marketing tests/Unit/Enums/Marketing/IntakeEnumsTest.php
git commit -m "feat(enums): enums de intake (specialty, audience, last_updated_by)"
```

---

### Task 2.4: DTO `ScriptTimecodeRow` (granular antes que ReelScript)

- [ ] **Step 1: Crear `app/DataTransferObjects/Marketing/ScriptTimecodeRow.php`**

```php
<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class ScriptTimecodeRow
{
    public function __construct(
        public string $time,
        public string $dialogue,
        public string $visual,
        public string $editNotes,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            time: $a['time'],
            dialogue: $a['dialogue'],
            visual: $a['visual'],
            editNotes: $a['edit_notes'],
        );
    }

    public function toArray(): array
    {
        return [
            'time' => $this->time,
            'dialogue' => $this->dialogue,
            'visual' => $this->visual,
            'edit_notes' => $this->editNotes,
        ];
    }
}
```

- [ ] **Step 2: Test roundtrip**

Create `tests/Unit/Dto/Marketing/ScriptTimecodeRowTest.php`:

```php
<?php
declare(strict_types=1);
use App\DataTransferObjects\Marketing\ScriptTimecodeRow;

it('roundtrips from/to array', function () {
    $a = ['time'=>'00:00-00:03','dialogue'=>'D','visual'=>'V','edit_notes'=>'E'];
    expect(ScriptTimecodeRow::fromArray($a)->toArray())->toBe($a);
});
```

Run: PASS.

- [ ] **Step 3: Commit**

```bash
git add app/DataTransferObjects/Marketing/ScriptTimecodeRow.php tests/Unit/Dto/Marketing/ScriptTimecodeRowTest.php
git commit -m "feat(dto): ScriptTimecodeRow"
```

---

### Task 2.5: DTOs restantes — `BriefSection`, `ReelScript`, `StorySlide`, `StoryDay`, `ChecklistItem`, `ChecklistPhase`, `ProductionChecklist`, `WeeklyBank`, `HashtagSets`, `CoachDropV1`

> **Patrón uniforme**: cada DTO `final readonly` con `__construct` + `static fromArray(array): self` + `toArray(): array`. Los `array<int, X>` se hidratan con `array_map(X::fromArray(...), $a['items'])`.

- [ ] **Step 1: BriefSection**

```php
<?php
declare(strict_types=1);
namespace App\DataTransferObjects\Marketing;

final readonly class BriefSection
{
    public function __construct(
        public string $title,
        public string $objective,
        public string $priorityOffer,
        public string $keyMessage,
        public string $targetMetric,
        public string $weeklyTheme,
        public string $framingCopy,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            title: $a['title'], objective: $a['objective'],
            priorityOffer: $a['priority_offer'], keyMessage: $a['key_message'],
            targetMetric: $a['target_metric'], weeklyTheme: $a['weekly_theme'],
            framingCopy: $a['framing_copy'],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title, 'objective' => $this->objective,
            'priority_offer' => $this->priorityOffer, 'key_message' => $this->keyMessage,
            'target_metric' => $this->targetMetric, 'weekly_theme' => $this->weeklyTheme,
            'framing_copy' => $this->framingCopy,
        ];
    }
}
```

- [ ] **Step 2: ReelScript (incluye `array<ScriptTimecodeRow>`)**

```php
<?php
declare(strict_types=1);
namespace App\DataTransferObjects\Marketing;

final readonly class ReelScript
{
    public function __construct(
        public string $key,
        public string $type,
        public string $title,
        public array $formatMeta,         // { duration_sec_min, duration_sec_max, platforms[], bpm_hint }
        public array $hook,               // { text, rationale }
        /** @var array<int, ScriptTimecodeRow> */ public array $timecodeTable,
        public string $caption,
        public string $musicNote,
        public string $productionNotes,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            key: $a['key'], type: $a['type'], title: $a['title'],
            formatMeta: $a['format_meta'], hook: $a['hook'],
            timecodeTable: array_map(fn($r) => ScriptTimecodeRow::fromArray($r), $a['timecode_table']),
            caption: $a['caption'], musicNote: $a['music_note'],
            productionNotes: $a['production_notes'],
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key, 'type' => $this->type, 'title' => $this->title,
            'format_meta' => $this->formatMeta, 'hook' => $this->hook,
            'timecode_table' => array_map(fn(ScriptTimecodeRow $r) => $r->toArray(), $this->timecodeTable),
            'caption' => $this->caption, 'music_note' => $this->musicNote,
            'production_notes' => $this->productionNotes,
        ];
    }
}
```

- [ ] **Step 3-9: StorySlide, StoryDay, ChecklistItem, ChecklistPhase, ProductionChecklist, WeeklyBank, HashtagSets**

Aplicar mismo patrón. Ver schema en `schemas/coach_drop_v1.schema.json` para forma exacta. Ejemplo `StorySlide`:

```php
final readonly class StorySlide
{
    public function __construct(
        public string $kind, public string $text,
        public string $visualHint, public string $sticker,
    ) {}
    public static function fromArray(array $a): self { return new self($a['kind'], $a['text'], $a['visual_hint'], $a['sticker']); }
    public function toArray(): array { return ['kind'=>$this->kind,'text'=>$this->text,'visual_hint'=>$this->visualHint,'sticker'=>$this->sticker]; }
}
```

`StoryDay`:
```php
final readonly class StoryDay
{
    public function __construct(
        public string $day, public string $pillar,
        /** @var array<int, StorySlide> */ public array $slides,
        public string $dmFollowupHint,
    ) {}
    public static function fromArray(array $a): self {
        return new self(
            $a['day'], $a['pillar'],
            array_map(fn($s) => StorySlide::fromArray($s), $a['slides']),
            $a['dm_followup_hint'] ?? '',
        );
    }
    public function toArray(): array {
        return ['day'=>$this->day,'pillar'=>$this->pillar,
                'slides'=>array_map(fn($s)=>$s->toArray(),$this->slides),
                'dm_followup_hint'=>$this->dmFollowupHint];
    }
}
```

`ChecklistItem`:
```php
final readonly class ChecklistItem {
    public function __construct(public string $title, public string $detail, public array $subitems = []) {}
    public static function fromArray(array $a): self { return new self($a['title'], $a['detail'], $a['subitems'] ?? []); }
    public function toArray(): array { return ['title'=>$this->title,'detail'=>$this->detail,'subitems'=>$this->subitems]; }
}
```

`ChecklistPhase`:
```php
final readonly class ChecklistPhase {
    public function __construct(public string $key, public string $title,
        /** @var array<int,ChecklistItem> */ public array $items) {}
    public static function fromArray(array $a): self {
        return new self($a['key'], $a['title'],
            array_map(fn($i) => ChecklistItem::fromArray($i), $a['items']));
    }
    public function toArray(): array {
        return ['key'=>$this->key,'title'=>$this->title,
                'items'=>array_map(fn($i)=>$i->toArray(),$this->items)];
    }
}
```

`ProductionChecklist`:
```php
final readonly class ProductionChecklist {
    public function __construct(/** @var array<int,ChecklistPhase> */ public array $phases) {}
    public static function fromArray(array $a): self {
        return new self(array_map(fn($p) => ChecklistPhase::fromArray($p), $a['phases']));
    }
    public function toArray(): array {
        return ['phases'=>array_map(fn($p)=>$p->toArray(),$this->phases)];
    }
}
```

`WeeklyBank`:
```php
final readonly class WeeklyBank {
    public function __construct(public array $altHooks, public array $altCtas, public array $altCaptions) {}
    public static function fromArray(array $a): self { return new self($a['alt_hooks'], $a['alt_ctas'], $a['alt_captions']); }
    public function toArray(): array { return ['alt_hooks'=>$this->altHooks,'alt_ctas'=>$this->altCtas,'alt_captions'=>$this->altCaptions]; }
}
```

`HashtagSets`:
```php
final readonly class HashtagSets {
    public function __construct(public array $sets) {}
    public static function fromArray(array $a): self { return new self($a['sets']); }
    public function toArray(): array { return ['sets'=>$this->sets]; }
}
```

- [ ] **Step 10: CoachDropV1 (top-level)**

```php
<?php
declare(strict_types=1);
namespace App\DataTransferObjects\Marketing;

final readonly class CoachDropV1
{
    public function __construct(
        public string $schemaVersion,
        public BriefSection $brief,
        /** @var array<int, ReelScript> */ public array $reels,
        /** @var array<int, StoryDay> */ public array $stories,
        public ProductionChecklist $checklist,
        public WeeklyBank $bank,
        public HashtagSets $hashtags,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            schemaVersion: $a['schema_version'],
            brief: BriefSection::fromArray($a['brief']),
            reels: array_map(fn($r) => ReelScript::fromArray($r), $a['reels']),
            stories: array_map(fn($s) => StoryDay::fromArray($s), $a['stories']),
            checklist: ProductionChecklist::fromArray($a['checklist']),
            bank: WeeklyBank::fromArray($a['bank']),
            hashtags: HashtagSets::fromArray($a['hashtags']),
        );
    }

    public function toArray(): array
    {
        return [
            'schema_version' => $this->schemaVersion,
            'brief' => $this->brief->toArray(),
            'reels' => array_map(fn(ReelScript $r) => $r->toArray(), $this->reels),
            'stories' => array_map(fn(StoryDay $s) => $s->toArray(), $this->stories),
            'checklist' => $this->checklist->toArray(),
            'bank' => $this->bank->toArray(),
            'hashtags' => $this->hashtags->toArray(),
        ];
    }
}
```

- [ ] **Step 11: Test roundtrip integral**

Create `tests/Unit/Dto/Marketing/CoachDropV1RoundtripTest.php`:

```php
<?php
declare(strict_types=1);
use App\DataTransferObjects\Marketing\CoachDropV1;

it('CoachDropV1 fromArray + toArray roundtrips a full payload', function () {
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    $dto = CoachDropV1::fromArray($payload);
    expect($dto->toArray())->toBe($payload);
});
```

Crear fixture en `tests/fixtures/coach_drop_v1_valid.json` con ejemplo válido completo (1 KB, mínimo válido contra schema). Ejemplo abreviado:

```json
{
  "schema_version": "coach_drop_v1",
  "brief": {"title":"T","objective":"O","priority_offer":"metodo","key_message":"K","target_metric":"M","weekly_theme":"W","framing_copy":"F"},
  "reels": [
    {"key":"reel_1","type":"educativo","title":"R1","format_meta":{"duration_sec_min":30,"duration_sec_max":40,"platforms":["instagram"],"bpm_hint":"100"},"hook":{"text":"H","rationale":"R"},"timecode_table":[{"time":"00:00-00:03","dialogue":"D","visual":"V","edit_notes":"E"},{"time":"00:03-00:08","dialogue":"D2","visual":"V2","edit_notes":"E2"},{"time":"00:08-00:30","dialogue":"D3","visual":"V3","edit_notes":"E3"}],"caption":"C","music_note":"M","production_notes":"P"},
    {"key":"reel_2","type":"conversion","title":"R2","format_meta":{"duration_sec_min":30,"duration_sec_max":40,"platforms":["instagram"],"bpm_hint":"100"},"hook":{"text":"H","rationale":"R"},"timecode_table":[{"time":"00:00-00:03","dialogue":"D","visual":"V","edit_notes":"E"},{"time":"00:03-00:08","dialogue":"D2","visual":"V2","edit_notes":"E2"},{"time":"00:08-00:30","dialogue":"D3","visual":"V3","edit_notes":"E3"}],"caption":"C","music_note":"M","production_notes":"P"}
  ],
  "stories": [
    {"day":"LUN","pillar":"activacion","slides":[{"kind":"text","text":"T","visual_hint":"V","sticker":"poll"}],"dm_followup_hint":""},
    {"day":"MAR","pillar":"nutricion","slides":[{"kind":"text","text":"T","visual_hint":"V","sticker":"none"}],"dm_followup_hint":""},
    {"day":"MIE","pillar":"spotlight","slides":[{"kind":"text","text":"T","visual_hint":"V","sticker":"none"}],"dm_followup_hint":""},
    {"day":"JUE","pillar":"bts","slides":[{"kind":"text","text":"T","visual_hint":"V","sticker":"none"}],"dm_followup_hint":""},
    {"day":"VIE","pillar":"qa","slides":[{"kind":"text","text":"T","visual_hint":"V","sticker":"question"}],"dm_followup_hint":""},
    {"day":"SAB","pillar":"motivacion","slides":[{"kind":"text","text":"T","visual_hint":"V","sticker":"none"}],"dm_followup_hint":""},
    {"day":"DOM","pillar":"reset","slides":[{"kind":"text","text":"T","visual_hint":"V","sticker":"slider"}],"dm_followup_hint":""}
  ],
  "checklist": {"phases":[
    {"key":"pre","title":"Pre","items":[{"title":"X","detail":"D"}]},
    {"key":"cam","title":"Cam","items":[{"title":"X","detail":"D"}]},
    {"key":"edit","title":"Edit","items":[{"title":"X","detail":"D"}]},
    {"key":"pub","title":"Pub","items":[{"title":"X","detail":"D"}]}
  ]},
  "bank": {"alt_hooks":["a","b","c","d","e"],"alt_ctas":["x","y","z"],"alt_captions":["1","2","3"]},
  "hashtags": {"sets":[{"name":"set1","tags":["#a","#b"]}]}
}
```

Run: `vendor/bin/pest --filter CoachDropV1Roundtrip`
Expected: PASS.

- [ ] **Step 12: Commit**

```bash
git add app/DataTransferObjects/Marketing tests/Unit/Dto tests/fixtures/coach_drop_v1_valid.json
git commit -m "feat(dto): DTOs final readonly del coach_drop_v1 con roundtrip test"
```

---

### Task 2.6: DTO `MarketingProfile`

- [ ] **Step 1: Crear**

```php
<?php
declare(strict_types=1);
namespace App\DataTransferObjects\Marketing;

final readonly class MarketingProfile
{
    public function __construct(
        public string $brandName,
        public ?string $city,
        public ?string $countryCode,
        public string $specialtyPrimary,
        public ?string $specialtySecondary,
        public string $differentiator,
        public string $audienceAgeRange,
        public string $audienceGender,
        public string $audiencePainMain,
        public string $audienceOfferMain,
        /** @var array<int,string> */ public array $preferredMethodologies,
        /** @var array<int,string> */ public array $contentTopics,
        /** @var array<int,string> */ public array $voiceAdjectives,
        /** @var array<int,array{caption:string,source_url:?string,note:?string}> */ public array $voiceSamples,
        /** @var array<int,array{name:string,price:float|int,currency:string,promo:?string}> */ public array $activeOffers,
        public array $topWorkingPosts = [],
    ) {}

    public static function fromModel(\App\Models\CoachMarketingProfile $m): self
    {
        return new self(
            brandName: $m->brand_name,
            city: $m->city,
            countryCode: $m->country_code,
            specialtyPrimary: $m->specialty_primary?->value ?? 'otro',
            specialtySecondary: $m->specialty_secondary?->value,
            differentiator: $m->differentiator,
            audienceAgeRange: $m->audience_age_range->value,
            audienceGender: $m->audience_gender->value,
            audiencePainMain: $m->audience_pain_main,
            audienceOfferMain: $m->audience_offer_main->value,
            preferredMethodologies: $m->preferred_methodologies ?? [],
            contentTopics: $m->content_topics ?? [],
            voiceAdjectives: $m->voice_adjectives ?? [],
            voiceSamples: $m->voice_samples ?? [],
            activeOffers: $m->active_offers ?? [],
            topWorkingPosts: $m->top_working_posts ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'brand_name' => $this->brandName, 'city' => $this->city, 'country_code' => $this->countryCode,
            'specialty_primary' => $this->specialtyPrimary, 'specialty_secondary' => $this->specialtySecondary,
            'differentiator' => $this->differentiator,
            'audience_age_range' => $this->audienceAgeRange, 'audience_gender' => $this->audienceGender,
            'audience_pain_main' => $this->audiencePainMain, 'audience_offer_main' => $this->audienceOfferMain,
            'preferred_methodologies' => $this->preferredMethodologies, 'content_topics' => $this->contentTopics,
            'voice_adjectives' => $this->voiceAdjectives, 'voice_samples' => $this->voiceSamples,
            'active_offers' => $this->activeOffers, 'top_working_posts' => $this->topWorkingPosts,
        ];
    }
}
```

- [ ] **Step 2: Test fromModel**

Create `tests/Unit/Dto/Marketing/MarketingProfileTest.php` (factory + assert toArray contiene brand_name).

- [ ] **Step 3: Commit**

```bash
git add app/DataTransferObjects/Marketing/MarketingProfile.php tests/Unit/Dto/Marketing/MarketingProfileTest.php
git commit -m "feat(dto): MarketingProfile + fromModel"
```

---

### Task 2.7: `DropSchemaValidator`

**Files:**
- Create: `app/Services/Marketing/DropSchemaValidator.php`
- Create: `app/Exceptions/Marketing/InvalidDropSchema.php`

- [ ] **Step 1: Excepción**

```php
<?php
declare(strict_types=1);
namespace App\Exceptions\Marketing;

final class InvalidDropSchema extends \DomainException
{
    /** @param array<int,array{path:string,message:string}> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Drop JSON failed schema validation: ' . count($errors) . ' error(s)');
    }
}
```

- [ ] **Step 2: Test rojo**

Create `tests/Unit/Marketing/DropSchemaValidatorTest.php`:

```php
<?php
declare(strict_types=1);
use App\Services\Marketing\DropSchemaValidator;
use App\Exceptions\Marketing\InvalidDropSchema;

it('passes a valid coach_drop_v1 payload', function () {
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    $validator = new DropSchemaValidator();
    $validator->validate($payload);  // no throw
    expect(true)->toBeTrue();
});

it('throws on missing required field with detailed path', function () {
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    unset($payload['brief']);
    $validator = new DropSchemaValidator();
    expect(fn() => $validator->validate($payload))
        ->toThrow(InvalidDropSchema::class);
});

it('rejects unknown schema_version', function () {
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    $payload['schema_version'] = 'coach_drop_v9';
    expect(fn() => (new DropSchemaValidator())->validate($payload))
        ->toThrow(InvalidDropSchema::class);
});
```

Run: FAIL.

- [ ] **Step 3: Implementar**

```php
<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Exceptions\Marketing\InvalidDropSchema;
use Opis\JsonSchema\Validator;

final class DropSchemaValidator
{
    public function __construct(
        private readonly string $schemaPath = '',
    ) {}

    public function validate(array $payload, string $version = 'coach_drop_v1'): void
    {
        $path = $this->schemaPath ?: base_path("schemas/{$version}.schema.json");
        if (!is_file($path)) {
            throw new \RuntimeException("Schema file not found: {$path}");
        }

        $validator = new Validator();
        $result = $validator->validate(json_decode(json_encode($payload)), file_get_contents($path));

        if ($result->isValid()) {
            return;
        }

        $errors = [];
        $error = $result->error();
        $this->collect($error, $errors);

        throw new InvalidDropSchema($errors);
    }

    private function collect(mixed $error, array &$out, string $prefix = ''): void
    {
        if ($error === null) return;
        $path = $prefix . '/' . implode('/', $error->data()->fullPath());
        $out[] = ['path' => $path, 'message' => $error->message()];
        foreach ($error->subErrors() ?? [] as $sub) {
            $this->collect($sub, $out, $prefix);
        }
    }
}
```

- [ ] **Step 4: Run test — pasa**

Run: `vendor/bin/pest --filter DropSchemaValidator`
Expected: 3 passing.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Marketing/DropSchemaValidator.php app/Exceptions/Marketing/InvalidDropSchema.php tests/Unit/Marketing/DropSchemaValidatorTest.php
git commit -m "feat(marketing): DropSchemaValidator con opis/json-schema"
```

---

### Task 2.8: `DropStateMachine`

**Files:**
- Create: `app/Services/Marketing/DropStateMachine.php`
- Create: `app/Exceptions/Marketing/InvalidDropTransition.php`

- [ ] **Step 1: Excepción**

```php
<?php
declare(strict_types=1);
namespace App\Exceptions\Marketing;
use App\Enums\Marketing\DropStatus;

final class InvalidDropTransition extends \DomainException
{
    public function __construct(public readonly DropStatus $from, public readonly DropStatus $to)
    {
        parent::__construct("Invalid drop transition {$from->value} -> {$to->value}");
    }
}
```

- [ ] **Step 2: Test**

Create `tests/Unit/Marketing/DropStateMachineTest.php`:

```php
<?php
declare(strict_types=1);
use App\Enums\Marketing\DropStatus;
use App\Models\CoachContentDrop;
use App\Models\Admin;
use App\Services\Marketing\DropStateMachine;
use App\Exceptions\Marketing\InvalidDropTransition;
use App\Enums\UserRole;

uses(Tests\TestCase::class)->in(__DIR__);

it('allows pending -> generating', function () {
    $admin = Admin::factory()->create();
    $drop = CoachContentDrop::factory()->pending()->create();
    (new DropStateMachine())->transition($drop, DropStatus::Generating, $admin);
    expect($drop->fresh()->status)->toBe(DropStatus::Generating);
});

it('rejects pending -> completed', function () {
    $admin = Admin::factory()->create();
    $drop = CoachContentDrop::factory()->pending()->create();
    expect(fn() => (new DropStateMachine())->transition($drop, DropStatus::Completed, $admin))
        ->toThrow(InvalidDropTransition::class);
});

it('records timestamp at transition', function () {
    $admin = Admin::factory()->create();
    $drop = CoachContentDrop::factory()->inReview()->create();
    (new DropStateMachine())->transition($drop, DropStatus::Approved, $admin);
    $drop->refresh();
    expect($drop->approved_at)->not->toBeNull()
        ->and($drop->approved_by_id)->toBe($admin->id);
});
```

- [ ] **Step 3: Implementar**

```php
<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Enums\Marketing\DropStatus;
use App\Exceptions\Marketing\InvalidDropTransition;
use App\Models\Admin;
use App\Models\CoachContentDrop;

final class DropStateMachine
{
    /** @var array<string, array<int, DropStatus>> */
    private const TRANSITIONS = [
        'pending'     => [DropStatus::Generating],
        'generating'  => [DropStatus::InReview, DropStatus::Pending],
        'in_review'   => [DropStatus::Approved, DropStatus::Pending],
        'approved'    => [DropStatus::Ready],
        'ready'       => [DropStatus::InProgress, DropStatus::Archived],
        'in_progress' => [DropStatus::Completed, DropStatus::Archived],
        'completed'   => [DropStatus::Archived],
        'archived'    => [],
    ];

    public function transition(CoachContentDrop $drop, DropStatus $next, Admin $actor): void
    {
        $allowed = self::TRANSITIONS[$drop->status->value] ?? [];
        if (!in_array($next, $allowed, strict: true)) {
            throw new InvalidDropTransition($drop->status, $next);
        }

        $drop->status = $next;

        match ($next) {
            DropStatus::Generating => $drop->generated_at ??= now(),
            DropStatus::InReview => $drop->reviewed_at = now(),
            DropStatus::Approved => [
                $drop->approved_at = now(),
                $drop->approved_by_id = $actor->id,
            ],
            DropStatus::Ready => $drop->ready_at = now(),
            DropStatus::Completed => $drop->completed_at = now(),
            default => null,
        };

        $drop->save();
    }
}
```

- [ ] **Step 4: Test pasa. Commit**

```bash
git add app/Services/Marketing/DropStateMachine.php app/Exceptions/Marketing/InvalidDropTransition.php tests/Unit/Marketing/DropStateMachineTest.php
git commit -m "feat(marketing): DropStateMachine con transiciones tipadas"
```

---

### Task 2.9: Sello del módulo M2

- [ ] **Step 1: Run test suite del módulo**

Run: `vendor/bin/pest tests/Unit/Enums/Marketing tests/Unit/Dto tests/Unit/Marketing`
Expected: todos verdes.

- [ ] **Step 2: Tag**

```bash
git tag -a m2-domain -m "Modulo 2 (Domain) completo"
```

---

# MÓDULO 3 — Authorization & Validation Layer

**Deps:** M2
**Output:** Policies + Form Requests + middleware Vue Router-side está en M7; este módulo cubre el lado backend.

**Files affected:**
- Create: `app/Policies/Coach/CoachContentDropPolicy.php`
- Create: `app/Policies/Coach/CoachMarketingProfilePolicy.php`
- Create: `app/Policies/Admin/Marketing/AdminDropPolicy.php`
- Create: `app/Http/Requests/Coach/StoreMarketingProfileRequest.php`
- Create: `app/Http/Requests/Coach/UpdateMarketingProfileRequest.php`
- Create: `app/Http/Requests/Coach/UpdateMarketingProfileDraftRequest.php`
- Create: `app/Http/Requests/Coach/MarkPiecePublishedRequest.php`
- Create: `app/Http/Requests/Admin/Marketing/UpdateDropContentRequest.php`
- Create: `app/Http/Requests/Admin/Marketing/ApproveDropRequest.php`
- Create: `app/Http/Requests/Admin/Marketing/RequestRegenerateRequest.php`
- Modify: `app/Providers/AuthServiceProvider.php` (registrar policies)

---

### Task 3.1: `CoachContentDropPolicy` — IDOR-proof

- [ ] **Step 1: Test rojo**

Create `tests/Feature/Coach/Marketing/CoachStrategyAccessTest.php`:

```php
<?php
declare(strict_types=1);
use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Policies\Coach\CoachContentDropPolicy;

it('coach can view own ready drop', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coach->id]);
    expect((new CoachContentDropPolicy())->view($coach, $drop))->toBeTrue();
});

it('coach CANNOT view another coachs drop (IDOR)', function () {
    $a = Admin::factory()->create(['role' => UserRole::Coach]);
    $b = Admin::factory()->create(['role' => UserRole::Coach]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $b->id]);
    expect((new CoachContentDropPolicy())->view($a, $drop))->toBeFalse();
});

it('coach cannot view in_review drop (only ready+)', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    $drop = CoachContentDrop::factory()->inReview()->create(['coach_id' => $coach->id]);
    expect((new CoachContentDropPolicy())->view($coach, $drop))->toBeFalse();
});

it('non-coach role denied', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin]);
    $drop = CoachContentDrop::factory()->ready()->create();
    expect((new CoachContentDropPolicy())->view($admin, $drop))->toBeFalse();
});
```

- [ ] **Step 2: Implementar**

```php
<?php

declare(strict_types=1);

namespace App\Policies\Coach;

use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;

final class CoachContentDropPolicy
{
    public function view(Admin $user, CoachContentDrop $drop): bool
    {
        return $user->role === UserRole::Coach
            && $drop->coach_id === $user->id
            && $drop->status->isVisibleToCoach();
    }

    public function markPiecePublished(Admin $user, CoachContentDrop $drop): bool
    {
        return $this->view($user, $drop) && $drop->status === DropStatus::Ready
            || ($this->view($user, $drop) && $drop->status === DropStatus::InProgress);
    }
}
```

- [ ] **Step 3: Run test pasa. Commit**

```bash
git add app/Policies/Coach/CoachContentDropPolicy.php tests/Feature/Coach/Marketing/CoachStrategyAccessTest.php
git commit -m "feat(security): CoachContentDropPolicy IDOR-proof"
```

---

### Task 3.2: `CoachMarketingProfilePolicy`

- [ ] **Step 1: Implementar**

```php
<?php
declare(strict_types=1);
namespace App\Policies\Coach;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;

final class CoachMarketingProfilePolicy
{
    public function view(Admin $user, CoachMarketingProfile $profile): bool
    {
        return $user->role === UserRole::Coach && $profile->coach_id === $user->id;
    }

    public function update(Admin $user, CoachMarketingProfile $profile): bool
    {
        return $this->view($user, $profile);
    }
}
```

- [ ] **Step 2: Test IDOR (símil a 3.1) + Commit**

```bash
git add app/Policies/Coach/CoachMarketingProfilePolicy.php tests/Feature/Coach/Marketing/MarketingProfilePolicyTest.php
git commit -m "feat(security): CoachMarketingProfilePolicy"
```

---

### Task 3.3: `AdminDropPolicy`

```php
<?php
declare(strict_types=1);
namespace App\Policies\Admin\Marketing;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;

final class AdminDropPolicy
{
    public function view(Admin $user, CoachContentDrop $drop): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Superadmin], strict: true);
    }

    public function update(Admin $user, CoachContentDrop $drop): bool { return $this->view($user, $drop); }
    public function approve(Admin $user, CoachContentDrop $drop): bool { return $this->view($user, $drop); }
    public function requestRegenerate(Admin $user, CoachContentDrop $drop): bool { return $this->view($user, $drop); }
}
```

Test + Commit:
```bash
git add app/Policies/Admin/Marketing/AdminDropPolicy.php tests/Feature/Admin/Marketing/AdminDropPolicyTest.php
git commit -m "feat(security): AdminDropPolicy"
```

---

### Task 3.4: Form Requests del coach

> Patrón: cada Form Request `final` con `authorize(): bool` + `rules(): array`. Para enums, usa `Rules\Enum`.

- [ ] **Step 1: `StoreMarketingProfileRequest`**

```php
<?php
declare(strict_types=1);
namespace App\Http\Requests\Coach;

use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\SpecialtyPrimary;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class StoreMarketingProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Coach;
    }

    public function rules(): array
    {
        return [
            'brand_name' => ['required','string','max:120'],
            'city' => ['nullable','string','max:80'],
            'country_code' => ['nullable','string','size:2'],
            'specialty_primary' => ['required', new Enum(SpecialtyPrimary::class)],
            'specialty_primary_other' => ['nullable','string','max:80'],
            'specialty_secondary' => ['nullable', new Enum(SpecialtyPrimary::class)],
            'specialty_secondary_other' => ['nullable','string','max:80'],
            'differentiator' => ['required','string','min:20','max:1000'],
            'audience_age_range' => ['required', new Enum(AudienceAgeRange::class)],
            'audience_gender' => ['required', new Enum(AudienceGender::class)],
            'audience_pain_main' => ['required','string','max:200'],
            'audience_offer_main' => ['required', new Enum(AudienceOfferMain::class)],
            'preferred_methodologies' => ['required','array','min:1','max:10'],
            'preferred_methodologies.*' => ['string','max:80'],
            'preferred_methodologies_other' => ['nullable','array','max:5'],
            'content_topics' => ['required','array','min:1','max:10'],
            'content_topics.*' => ['string','max:80'],
            'content_topics_other' => ['nullable','array','max:5'],
            'voice_adjectives' => ['required','array','size:3'],
            'voice_adjectives.*' => ['string','max:30'],
            'voice_samples' => ['nullable','array','max:3'],
            'voice_samples.*.caption' => ['required','string','max:2200'],
            'voice_samples.*.source_url' => ['nullable','url'],
            'voice_samples.*.note' => ['nullable','string','max:200'],
            'active_offers' => ['required','array','min:1','max:3'],
            'active_offers.*.name' => ['required','string','max:80'],
            'active_offers.*.price' => ['required','numeric','min:0'],
            'active_offers.*.currency' => ['required','string','size:3'],
            'active_offers.*.promo' => ['nullable','string','max:200'],
            'top_working_posts' => ['nullable','array','max:3'],
            'top_working_posts.*.url' => ['required','url'],
            'top_working_posts.*.why_worked' => ['required','string','max:300'],
        ];
    }
}
```

- [ ] **Step 2: Tests**

Create `tests/Feature/Coach/Marketing/MarketingProfileValidationTest.php`:

```php
<?php
declare(strict_types=1);
use function Pest\Laravel\actingAs;
use App\Enums\UserRole;
use App\Models\Admin;

it('rejects payload missing brand_name', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    actingAs($coach)
        ->postJson('/api/v/coach/marketing-profile', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['brand_name','specialty_primary']);
});

it('requires exactly 3 voice_adjectives', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    actingAs($coach)
        ->postJson('/api/v/coach/marketing-profile', ['voice_adjectives' => ['a','b']])
        ->assertJsonValidationErrors(['voice_adjectives']);
});
```

(El test fallará hasta que el endpoint exista en M5 — anotar como blocked-by-M5 o ejecutar en M5.)

- [ ] **Step 3: Commit**

```bash
git add app/Http/Requests/Coach/StoreMarketingProfileRequest.php
git commit -m "feat(security): StoreMarketingProfileRequest validation rules"
```

---

### Task 3.5: Form Requests restantes

- [ ] **Step 1: `UpdateMarketingProfileRequest`** — extiende `StoreMarketingProfileRequest`, métodos `rules` con `sometimes` por campo si querés permitir update parcial. Recomendado: full replacement = mismo rules, no partial.

```php
final class UpdateMarketingProfileRequest extends StoreMarketingProfileRequest {}
```

- [ ] **Step 2: `UpdateMarketingProfileDraftRequest`** — todos los campos `sometimes|nullable`:

```php
<?php
declare(strict_types=1);
namespace App\Http\Requests\Coach;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateMarketingProfileDraftRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === UserRole::Coach; }

    public function rules(): array
    {
        // Todos opcionales - draft permite parciales
        return [
            'brand_name' => ['sometimes','string','max:120'],
            'city' => ['sometimes','nullable','string','max:80'],
            // ... mismos campos pero todos sometimes/nullable
        ];
    }
}
```

(Repetir todos los campos del Store con prefijo `sometimes`.)

- [ ] **Step 3: `MarkPiecePublishedRequest`**

```php
<?php
declare(strict_types=1);
namespace App\Http\Requests\Coach;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class MarkPiecePublishedRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === UserRole::Coach; }
    public function rules(): array
    {
        return [
            'url' => ['nullable','url','max:500'],
            'notes' => ['nullable','string','max:1000'],
        ];
    }
}
```

- [ ] **Step 4: `UpdateDropContentRequest`** (admin)

```php
<?php
declare(strict_types=1);
namespace App\Http\Requests\Admin\Marketing;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateDropContentRequest extends FormRequest
{
    public function authorize(): bool {
        return in_array($this->user()?->role, [UserRole::Admin, UserRole::Superadmin], true);
    }
    public function rules(): array
    {
        return ['content' => ['required','array']];
    }
}
```

(La validación profunda contra schema la hace el controller invocando `DropSchemaValidator`.)

- [ ] **Step 5: `ApproveDropRequest`** y **`RequestRegenerateRequest`**

```php
final class ApproveDropRequest extends FormRequest {
    public function authorize(): bool { return in_array($this->user()?->role, [UserRole::Admin, UserRole::Superadmin], true); }
    public function rules(): array { return []; }
}

final class RequestRegenerateRequest extends FormRequest {
    public function authorize(): bool { return in_array($this->user()?->role, [UserRole::Admin, UserRole::Superadmin], true); }
    public function rules(): array { return ['reason' => ['nullable','string','max:500']]; }
}
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Requests
git commit -m "feat(security): Form Requests para coach + admin marketing endpoints"
```

---

### Task 3.6: Registrar Policies en `AuthServiceProvider`

- [ ] **Step 1: Editar `app/Providers/AuthServiceProvider.php`**

Agregar al array `$policies`:

```php
\App\Models\CoachContentDrop::class => \App\Policies\Coach\CoachContentDropPolicy::class,
\App\Models\CoachMarketingProfile::class => \App\Policies\Coach\CoachMarketingProfilePolicy::class,
```

Para `AdminDropPolicy` se invoca explícitamente en controllers admin (mismo modelo target, pero policy distinta), o se usa `Gate::define`. Patrón recomendado: `Gate::define` en `boot()`:

```php
public function boot(): void
{
    $this->registerPolicies();
    Gate::define('admin.marketing.viewDrop', [AdminDropPolicy::class, 'view']);
    Gate::define('admin.marketing.updateDrop', [AdminDropPolicy::class, 'update']);
    Gate::define('admin.marketing.approveDrop', [AdminDropPolicy::class, 'approve']);
    Gate::define('admin.marketing.requestRegenerate', [AdminDropPolicy::class, 'requestRegenerate']);
}
```

- [ ] **Step 2: Test gate**

Create `tests/Feature/Auth/PoliciesRegistrationTest.php`:

```php
it('CoachContentDrop policy resolves', function () {
    expect(Gate::getPolicyFor(\App\Models\CoachContentDrop::class))
        ->toBeInstanceOf(\App\Policies\Coach\CoachContentDropPolicy::class);
});
```

Run: PASS.

- [ ] **Step 3: Commit**

```bash
git add app/Providers/AuthServiceProvider.php tests/Feature/Auth/PoliciesRegistrationTest.php
git commit -m "feat(security): registrar policies de marketing"
```

---

### Task 3.7: Sello del módulo M3

```bash
vendor/bin/pest tests/Feature/Coach/Marketing tests/Feature/Admin/Marketing tests/Feature/Auth
git tag -a m3-authorization -m "Modulo 3 (Authorization) completo"
```

---

# MÓDULO 4 — Sistema offline MDs

**Deps:** M2 (necesita el JSON Schema de M0/M2 para los archivos `20a/20b/21`).
**Output:** 22 archivos en `C:\Users\GODSF\Downloads\SISTEMA-CREACION-MARKETING-COACHES\`. **NO se commitean al repo** — viven fuera del codebase como sistema operativo.

**Convención** (idéntica a `SISTEMA-CREACION-PLANES`): Markdown puro sin frontmatter, naming kebab-case con números, contenido = instrucciones para Claude + ejemplos JSON inline + plantillas + referencias. Credenciales NUNCA en MDs.

**Files affected:**
- Create folder: `C:\Users\GODSF\Downloads\SISTEMA-CREACION-MARKETING-COACHES\`
- Create 20 MDs + 1 .txt (ver lista abajo)

---

### Task 4.1: Crear carpeta + `00-INDEX.md`

- [ ] **Step 1: Crear carpeta**

```bash
mkdir -p "C:/Users/GODSF/Downloads/SISTEMA-CREACION-MARKETING-COACHES"
```

- [ ] **Step 2: `00-INDEX.md` — orquestador**

Crear con esta estructura (~200-300 líneas finales):

```markdown
# SISTEMA CREACIÓN MARKETING COACHES — INDEX

**Versión:** 1.0
**Fecha:** 2026-04-26

## Resumen ejecutivo
Sistema espejo de SISTEMA-CREACION-PLANES, dedicado a producir el drop semanal
de marketing personalizado por coach (`coach_drop_v1`).

## Estructura del sistema (4 bloques + prompt maestro)

### BLOQUE A: Workflow y reglas de operación
- 00-INDEX.md (este archivo)
- 01-PASO-A-PASO.md ⭐
- 02-CREDENCIALES.md

### BLOQUE B: Voz, identidad y prohibiciones
- 05-VOZ-WELLCORE.md
- 06-VOZ-COACH.md ⭐ (90% del contenido sale de aquí)
- 07-PROHIBICIONES.md ⭐

### BLOQUE C: Reglas por tipo de pieza
- 10-REGLAS-BRIEF.md
- 11-REGLAS-REEL.md ⭐
- 12-REGLAS-STORIES-DIARIAS.md ⭐
- 13-CHECKLIST-PRODUCCION-REEL.md
- 14-BANCO-SEMANAL-ALTERNATIVOS.md
- 15-HASHTAGS-Y-SETS.md

### BLOQUE D: Schemas JSON canónicos
- 20-DATA-MODEL-MARKETING.md
- 20a-SCHEMA-COACH-DROP-V1.md ⭐
- 20b-SCHEMA-INTAKE.md
- 21-VALIDACION-PRE-INSERT.md

### BLOQUE E: Operación y mejora continua
- 30-COMO-MONTAR-EN-DB.md ⭐
- 31-CHECKLIST-VERIFICACION-DASHBOARD.md
- 32-CALENDARIO-EDITORIAL-90DIAS.md
- 33-MEJORA-CONTINUA.md

### Prompt maestro
- PROMPT-CLAUDE-CODE-NUEVA-SESION.txt ⭐⭐ (pegar al inicio de cada sesión)

## Orden de lectura por caso de uso

### Coach nuevo (primer drop)
00 → 01 → 06 → 07 → (10-15) → 20a → 30 → 31

### Coach recurrente (drop semanal normal)
00 → 01 → 06 → (11-15) → 20a → 30

### Regeneración por feedback de Daniel
00 → 33 → 06 → 07 → (área específica del feedback) → 30

### Cambio de oferta del coach
00 → 06 → 20b (verificar intake actualizado) → (10-15) → 20a → 30

## Reglas de oro (10)

1. NUNCA mencionar IA / Claude / IA al coach. Atribución pública: "Equipo Estrategia WellCore".
2. Voz coach (90%) NO voz marca; usa voice_samples si existen.
3. Cada pieza debe ser específica al nicho del coach, no fitness genérico.
4. Cita un dato verificable o ejemplo concreto, nunca "motivación abstracta".
5. CTA por pieza: UNA acción, no varias.
6. Hashtags: pattern `#[A-Za-z0-9_]+`, sin emojis ni espacios.
7. Reels: exactamente 2 por drop (1 educativo + 1 conversión).
8. Stories: exactamente 7 (LUN→DOM), pillar único por día.
9. JSON valida contra coach_drop_v1.schema.json antes de INSERT.
10. status='in_review' al insertar, NUNCA auto-aprobar.

## Ubicaciones críticas

| Recurso | Path |
|---|---|
| Schema formal | `wellcore-laravel/schemas/coach_drop_v1.schema.json` |
| Modelo Eloquent | `wellcore-laravel/app/Models/CoachContentDrop.php` |
| Validator service | `wellcore-laravel/app/Services/Marketing/DropSchemaValidator.php` |
| Tabla destino | DB `wellcore_fitness.coach_content_drops` |
| Pestaña que lo renderiza | `/coach/strategy` (Vue) |
```

- [ ] **Step 3: Escribir el archivo en disco** (sin git commit; vive fuera del repo)

```bash
# Desde Bash en el repo, podemos escribir al path Downloads
# (path con espacios, usar quotes)
```

Usar la herramienta `Write` apuntando a `C:\Users\GODSF\Downloads\SISTEMA-CREACION-MARKETING-COACHES\00-INDEX.md` con el contenido arriba.

- [ ] **Step 4: NO commit (archivo fuera del repo)**

---

### Task 4.2: `01-PASO-A-PASO.md`

Sección por sección (~250 líneas):

```markdown
# 01 — Paso a paso de generación de drop

## Workflow de 7 fases

### F0 — Verificar intake completo
- Ejecutar tinker: `App\Models\CoachMarketingProfile::where('coach_id', X)->first()->isComplete()`
- Si `false` → ABORTAR con mensaje "Coach no ha completado Brand Profile".

### F1 — Lectura MDs
Según caso (ver 00-INDEX): leer Bloque A → B → C → D en ese orden.

### F2 — Diseñar drop
Brief estratégico → 2 reels → 7 stories → checklist → banco → hashtags.
Cada pieza debe poder atribuirse al intake del coach (specialty, audiencia, oferta).

### F3 — Armar JSON coach_drop_v1
Plantilla en `22-TEMPLATES-JSON-LISTOS.md` (alterno: 20a-SCHEMA si no hay templates).
Reemplazar `{{PLACEHOLDERS}}` con datos del intake.

### F4 — INSERT vía script PHP heredoc
Ver MD 30 para script completo. Status=`in_review`, `original_content=content`,
`intake_snapshot=...current...`.

### F5 — Invalidar caches
Llave: `coach_drop_v3:{coach_id}:{year}:{week}`.

### F6 — Notificar admin
Database Notification al admin role=Admin/Superadmin.

### F7 — Esperar approval
Claude NO auto-aprueba. Termina sesión.

## Checklist de cierre (12 items)
- [ ] Intake completo verificado
- [ ] JSON valida contra coach_drop_v1.schema.json
- [ ] Voz del coach respetada (voice_samples si existen)
- [ ] Hooks específicos al nicho, no genéricos
- [ ] CTA único por pieza
- [ ] Hashtags pattern correcto
- [ ] 2 reels (1 edu + 1 conv) presentes
- [ ] 7 stories con días únicos LUN→DOM
- [ ] checklist 4 fases (pre/cam/edit/pub)
- [ ] bank: 5 hooks + 3 CTAs + 3 captions
- [ ] hashtags: ≥1 set
- [ ] status='in_review' después del INSERT
```

Crear archivo. NO commit.

---

### Task 4.3: `02-CREDENCIALES.md`

```markdown
# 02 — Credenciales y accesos

**NUNCA poner credenciales aquí.** Solo referencias.

## Memory de Claude Code
- `credentials_services.md` — Mailjet, Wompi, panel, GitHub, email
- `feedback_easypanel_buttons.md` — UIDs exactos para Run en EasyPanel
- `reference_container_paths.md` — `/code` path en container
- `feedback_db_safety.md` — DB compartida, solo aditivas

## Recursos externos
- Panel: https://panel.wellcorefitness.com
- Plataforma: https://wellcorefitness.com
- DB MySQL: `wellcore_fitness` (host en EasyPanel container)
- GitHub repo: wellcore-laravel (OAuth via Gmail)

## Reglas de seguridad
- Antes de INSERT, leer `feedback_db_safety` y `reference_container_paths`.
- NO usar Rebuild Docker (memory `feedback_deploy_approach`).
- NO correr `npm run build` en EasyPanel (memory `feedback_npm_build_oom`).
```

Crear archivo. NO commit.

---

### Task 4.4: `05-VOZ-WELLCORE.md`

```markdown
# 05 — Voz WellCore (10% del contenido)

## Cuándo usar voz WellCore (no voz coach)
- Brief estratégico (framing_copy, weekly_theme): voz neutra de la marca.
- Notas de producción genéricas en reels.
- Footer/disclaimer si aplica.

## Características
- Tuteo formal pero cercano.
- Tono: directo, científico, sin marketing barato.
- Sin hipérboles, sin "cambia tu vida ya".
- Vocabulario: técnico cuando aplica, accesible siempre.
- Sin emojis (excepto ✓ y → en checklists).

## Ejemplos OK (voz WellCore)
- "Esta semana tu prioridad es la oferta Método. Apunta tu audiencia a la
  conversación 'transformación con seguimiento real'."
- "Objetivo de la semana: 5 DMs entrantes calificados."

## Ejemplos NO OK
- "¡Vamos a romperla esta semana!" (cliché)
- "Sé la mejor versión de ti" (genérico)
- "Despierta tu poder interior" (motivacional vacío)
```

Crear. NO commit.

---

### Task 4.5: `06-VOZ-COACH.md` ⭐ — el más importante

```markdown
# 06 — Voz del coach (90% del contenido)

## Fuente de voz
1. **Voice samples del intake** (PRIMARIO): si existen, leer las 2-3 captions y
   replicar cadencia, vocabulario, longitud, uso de emojis.
2. **voice_adjectives** (SECUNDARIO): los 3 adjetivos calibran tono general.
3. **specialty + content_topics + audience_pain**: dictan QUÉ dice el coach.

## Cómo extraer voz de las samples
Para cada caption sample del coach, extraer:
- Largo promedio de oración (corta/media/larga)
- Uso de listas (sí/no, cuántos puntos)
- Emojis (cuáles, frecuencia)
- Pregunta retórica o afirmación directa
- Llamado a acción (cómo lo formula)
- Términos técnicos vs llanos

Replicar esos patrones en hooks, captions, stories del drop.

## Si NO hay voice samples
Usar solo voice_adjectives + specialty + ejemplos del nicho.
**Bandera roja**: drop sin voice samples → marcar en `admin_edits_diff`
campos donde tono pueda variar más, para revisión más cuidadosa de Daniel.

## Voz por specialty (cuando faltan samples)

### Fuerza
- Lenguaje: técnico de levantamiento, KGs, RM, tempo
- Cadencia: corta, directa, "haz X reps de Y"
- Tono: serio pero motivacional al final

### Mujeres post-parto
- Lenguaje: empático, técnico medido
- Cadencia: media, oraciones explicativas
- Tono: cálido, consciente del contexto vital

### Recomposición
- Lenguaje: balance proteínas/calorías, datos
- Cadencia: media-larga
- Tono: educativo, anti-mito

(... agregar para cada specialty del enum)

## Vocabulario prohibido (sin importar voz)
- "transformá tu vida" / "cambiá tu vida"
- "secret" / "trick" / "hack"
- "no excusas" / "sin pretextos"
- Emoji 🔥 / 💪 al inicio (solo después)
- "Bro" / "Mi gente" como saludo (a menos que voice samples lo confirmen)
```

Crear. NO commit.

---

### Task 4.6: `07-PROHIBICIONES.md` ⭐

```markdown
# 07 — Prohibiciones (15 críticas)

1. ❌ NUNCA mencionar IA / Claude / GPT / "asistente automatizado".
2. ❌ NUNCA usar plantillas idénticas entre coaches (cada drop debe sentirse hecho).
3. ❌ NUNCA generar sin intake completo (`completed_at IS NOT NULL`).
4. ❌ NUNCA recomendar herramienta/app sin saber si el coach la usa.
5. ❌ NUNCA citar datos sin fuente verificable (estudio + año).
6. ❌ NUNCA usar voz neutra cuando hay voice samples.
7. ❌ NUNCA copiar contenido de otro coach (audita el banco antes).
8. ❌ NUNCA prometer resultados específicos ("baja 5 kg en X").
9. ❌ NUNCA tocar temas tabú del coach (declarados en intake).
10. ❌ NUNCA hashtag con caracteres no `[A-Za-z0-9_]`.
11. ❌ NUNCA story con día duplicado (LUN→DOM únicos).
12. ❌ NUNCA reel sin timecode_table de mínimo 3 filas.
13. ❌ NUNCA brief sin priority_offer del enum.
14. ❌ NUNCA INSERT sin pasar por `DropSchemaValidator`.
15. ❌ NUNCA aprobar (status=ready) desde sesión Claude. Solo Daniel via UI admin.
```

Crear. NO commit.

---

### Task 4.7-4.12: Bloque C — Reglas por tipo de pieza

Crear los 6 archivos siguiendo este patrón estructural (resumido por archivo):

- [ ] **`10-REGLAS-BRIEF.md`** (~150 líneas)
  - Estructura: title (≤120), objective (oración accionable), priority_offer (enum),
    key_message (≤280 chars), target_metric (qué medir), weekly_theme (tema visual/conceptual),
    framing_copy (Fraunces Italic, opens emocional sin perder profesionalismo).
  - Ejemplos diligenciados (3 — uno por specialty distinta).

- [ ] **`11-REGLAS-REEL.md`** (~300 líneas, espejo del HTML 01-)
  - Hook 0-3s: dato contundente o curiosidad.
  - Estructura segmentos: hook → problema → solución → demo → cierre/CTA.
  - timecode_table: tiempo `MM:SS-MM:SS`, dialogue, visual, edit_notes.
  - format_meta: duration_sec_min/max (educativo 30-45s, conversion 25-40s).
  - Caption: hook caption → 3-5 bullets → CTA único → hashtags.
  - Reglas distintas por type: educativo (cita estudio) vs conversion (oferta clara).
  - Ejemplo completo (1 reel educativo + 1 conversion).

- [ ] **`12-REGLAS-STORIES-DIARIAS.md`** (~280 líneas, espejo del HTML 04-)
  - 7 días, 7 pilares: LUN=activación, MAR=nutrición, MIE=spotlight,
    JUE=bts, VIE=qa, SAB=motivacion, DOM=reset.
  - 1-3 slides por día, sticker por día (poll/slider/question/none).
  - dm_followup_hint: instrucción para responder a interacciones.
  - Ejemplos por pilar (3 por cada uno).

- [ ] **`13-CHECKLIST-PRODUCCION-REEL.md`** (~200 líneas, espejo del HTML 05-)
  - 4 fases: pre / cam / edit / pub.
  - Items + subitems por fase. Ver HTML `05-checklist-produccion-reel.html`
    como source y traducirlo a estructura JSON.

- [ ] **`14-BANCO-SEMANAL-ALTERNATIVOS.md`** (~150 líneas)
  - 5 alt_hooks: variantes del hook principal del reel #1.
  - 3 alt_ctas: variantes del CTA del reel #2.
  - 3 alt_captions: caption alternativo en case el principal no convierte.
  - Reglas: cada alternativo debe ser distinto en aproximación, no solo wording.

- [ ] **`15-HASHTAGS-Y-SETS.md`** (~120 líneas)
  - 4 sets curados: General+Nicho, Local (ciudad/país), Educativo, Engagement.
  - Pattern `#[A-Za-z0-9_]+` estricto.
  - Mix grande (1-5 tags) + medio (5-15) + nicho (15-30).
  - Ejemplos por specialty.

Crear los 6 archivos. NO commit.

---

### Task 4.13-4.16: Bloque D — Schemas JSON

- [ ] **`20-DATA-MODEL-MARKETING.md`** (~150 líneas)
  - Visión general de las 3 tablas + diagrama relación + cómo se mapean al JSON.

- [ ] **`20a-SCHEMA-COACH-DROP-V1.md`** ⭐ (~400 líneas)
  - Espejo HUMANO del JSON Schema formal `schemas/coach_drop_v1.schema.json`.
  - Cada campo: descripción, tipo, longitud, ejemplo válido, ejemplo inválido.
  - Tabla de placeholders comunes con ejemplos de relleno.

- [ ] **`20b-SCHEMA-INTAKE.md`** (~200 líneas)
  - Mapping de campos coach_marketing_profiles → cómo Claude los lee.
  - Cómo derivar dimensiones (audiencia → reels/stories), (specialty → temas), etc.

- [ ] **`21-VALIDACION-PRE-INSERT.md`** (~150 líneas)
  - Checklist técnico: 12 puntos a validar manualmente antes de correr el script.
  - Errores comunes y mensajes esperados del DropSchemaValidator.

Crear los 4. NO commit.

---

### Task 4.17-4.20: Bloque E — Operación y mejora continua

- [ ] **`30-COMO-MONTAR-EN-DB.md`** ⭐ (~250 líneas)
  - Script PHP heredoc completo para inserción (ver §6 del spec, sub-pasos 1-7).
  - Comando completo de tinker (con escapes correctos para Windows/Linux).
  - Cómo se invalida cache después.
  - Cómo se notifica admin.
  - Ejemplo de script real con comentarios.

- [ ] **`31-CHECKLIST-VERIFICACION-DASHBOARD.md`** (~180 líneas)
  - Checklist visual MCP Chrome DevTools post-INSERT:
    1. Login como admin → /admin/marketing/queue
    2. Verificar drop aparece con status=in_review
    3. Click "Revisar" → verificar split view renderiza intake + content
    4. Editar texto, click "Aprobar" → status=ready
    5. Login como ese coach → /coach/strategy
    6. Verificar hero, secciones 01-06, fila stories Lun-Dom
    7. Marcar pieza publicada → progress bar avanza
  - Errores comunes y screenshots esperados.

- [ ] **`32-CALENDARIO-EDITORIAL-90DIAS.md`** (~200 líneas)
  - Pilares anuales WellCore.
  - Tema mensual sugerido (Q1, Q2, Q3, Q4).
  - Temas semanales sugeridos por specialty (52 semanas × 7 specialties).
  - Cómo decidir el `weekly_theme` del brief partiendo de aquí.

- [ ] **`33-MEJORA-CONTINUA.md`** (~150 líneas)
  - Cómo leer `admin_edits_diff` cada 4 semanas.
  - Patrones a buscar: si Daniel siempre edita campo X, los MDs C deben mejorar la regla de X.
  - Cómo proponer cambios al sistema MD basados en señales reales.
  - Cadencia: mensual review.

Crear los 4. NO commit.

---

### Task 4.21: `PROMPT-CLAUDE-CODE-NUEVA-SESION.txt` ⭐⭐

**Files:**
- Create: `C:\Users\GODSF\Downloads\SISTEMA-CREACION-MARKETING-COACHES\PROMPT-CLAUDE-CODE-NUEVA-SESION.txt`

Estructura completa con 12 secciones (~600-900 líneas):

```text
═══════════════════════════════════════════════════════════════════════════════
PROMPT — Sesión nueva de Claude Code para SISTEMA-CREACION-MARKETING-COACHES
═══════════════════════════════════════════════════════════════════════════════

[0] PROYECTO Y STACK
   - WellCore Fitness — plataforma fitness LATAM (Laravel 13 + Vue 3 SPA + MySQL)
   - Repo principal: C:\Users\GODSF\Herd\wellcore-laravel
   - Frontend coach: 100% Vue 3 SPA (NO Livewire legacy)
   - Endpoint central: GET /api/v/coach/strategy/current
   - Tabla destino: wellcore_fitness.coach_content_drops
   - Sistema MDs: C:\Users\GODSF\Downloads\SISTEMA-CREACION-MARKETING-COACHES\

[1] SISTEMA DE MDs — UBICACIÓN Y ESTRUCTURA
   ⭐ Marcados los 8 críticos
   - 00-INDEX.md
   - 01-PASO-A-PASO.md ⭐
   - 02-CREDENCIALES.md
   - 05-VOZ-WELLCORE.md
   - 06-VOZ-COACH.md ⭐
   - 07-PROHIBICIONES.md ⭐
   - 10/11/12/13/14/15-... (Bloque C)
   - 11 ⭐ 12 ⭐
   - 20-DATA-MODEL.md
   - 20a-SCHEMA-COACH-DROP-V1.md ⭐
   - 20b-SCHEMA-INTAKE.md
   - 21-VALIDACION-PRE-INSERT.md
   - 30-COMO-MONTAR-EN-DB.md ⭐
   - 31, 32, 33

[2] ORDEN DE LECTURA OBLIGATORIO
   PASO 1: Leer 00-INDEX completo
   PASO 2: Confirmar caso de uso (coach nuevo / recurrente / regen / cambio oferta)
   PASO 3: Leer 01-PASO-A-PASO
   PASO 4: Pedir al admin coach_id + iso_year + iso_week
   PASO 5: Verificar intake completo (F0)
   PASO 6: Leer voz (05 + 06)
   PASO 7: Leer prohibiciones (07)
   PASO 8: Leer reglas de las piezas (10-15)
   PASO 9: Leer schema (20a)
   PASO 10: Diseñar drop conforme a (1)-(9)
   PASO 11: Validar mentalmente contra checklist 21
   PASO 12: Armar JSON
   PASO 13: Generar script tinker (30)
   PASO 14: Pedir aprobación al admin para correr el script
   PASO 15: Después de INSERT, invalidar cache + notificar
   PASO 16: Terminar sesión (NO aprobar)

[3] CONTEXTO TÉCNICO IMPORTANTE
   - DB compartida wellcore_fitness con app vanilla — solo migraciones aditivas.
   - Schema validation server-side via DropSchemaValidator (opis/json-schema).
   - Coach = admins.id con role='coach'. NO existe tabla 'coaches'.
   - JSON Schema formal: schemas/coach_drop_v1.schema.json
   - Cache key: coach_drop_v3:{coach_id}:{year}:{week}, TTL 5min.
   - status='in_review' al insertar SIEMPRE.

[4] INTAKE DEL COACH — QUÉ NECESITAR
   Antes de generar, ejecutar tinker:
     `App\Models\CoachMarketingProfile::where('coach_id', X)->first()`
   Verificar `completed_at IS NOT NULL`.
   Si NULL → ABORTAR.

   Campos del intake (los 12 + voice samples):
   1. brand_name
   2. specialty_primary (+ secondary opcional)
   3. differentiator
   4. audience_age_range
   5. audience_gender
   6. audience_pain_main
   7. audience_offer_main
   8. preferred_methodologies[]
   9. content_topics[]
   10. voice_adjectives[3]
   11. active_offers[1-3]
   12. (voice_samples[] opcional pero crítico para calidad)

[5] WORKFLOW DE 7 FASES
   F0: Verificar intake (tinker)
   F1: Lectura MDs (orden de [2])
   F2: Diseñar drop (brief + 2 reels + 7 stories + checklist + bank + hashtags)
   F3: Armar JSON (templates de 20a)
   F4: INSERT vía script PHP heredoc (30-COMO-MONTAR-EN-DB.md)
        - status='in_review'
        - original_content = content (snapshot)
        - intake_snapshot = perfil actual del coach
   F5: Invalidar cache: Cache::forget("coach_drop_v3:{$coach_id}:{$year}:{$week}")
   F6: Notificar admin (Database Notification)
   F7: Esperar approval — Claude NO auto-aprueba

[6] CREDENCIALES Y ACCESOS
   Ver memory de Claude Code (referencias en 02-CREDENCIALES.md).
   - Panel: panel.wellcorefitness.com (credenciales en memory)
   - DB: tinker en EasyPanel container path /code

[7] JSON CANÓNICO COACH_DROP_V1 — RESUMEN RÁPIDO
   Top-level keys: schema_version, brief, reels[2], stories[7], checklist, bank, hashtags
   Detalle completo en 20a-SCHEMA-COACH-DROP-V1.md.
   Ejemplo inline mínimo:
   ```json
   { "schema_version":"coach_drop_v1",
     "brief": {...},
     "reels": [<reel_1>, <reel_2>],
     "stories": [<7 días LUN→DOM>],
     "checklist": {"phases":[<pre>,<cam>,<edit>,<pub>]},
     "bank": {"alt_hooks":[5], "alt_ctas":[3], "alt_captions":[3]},
     "hashtags": {"sets":[...]}
   }
   ```

[8] SI ALGO FALLA — DEBUGGING
   - "Coach no completó profile" → pedir al admin que el coach complete onboarding antes.
   - "Schema validation falló" → leer mensaje detallado del validator, fix campo.
   - "uniq_coach_week violation" → ya hay drop esa semana; usar UPSERT en script.
   - "FK admins(id) violation" → coach_id incorrecto o admin inactivo.
   - Cache no invalida → flush global Cache::flush() (último recurso).

[9] CHECKLIST FINAL ANTES DE MARCAR IN_REVIEW (12 items)
   ✓ Intake verificado (completed_at)
   ✓ Schema valida
   ✓ 2 reels (1 edu + 1 conv)
   ✓ 7 stories (días únicos)
   ✓ 4 fases checklist
   ✓ 5+3+3 banco
   ✓ ≥1 set hashtags pattern correcto
   ✓ Voz coach (90%) si voice_samples
   ✓ Cita verificable en reel educativo
   ✓ CTA único por pieza
   ✓ Sin tabús del coach
   ✓ INSERT exitoso, cache flushed

[10] CASOS DE USO
   A) Coach nuevo (primer drop)
   B) Coach recurrente (siguiente semana, sin cambios)
   C) Regeneración por feedback de Daniel
   D) Cambio de oferta (priority_offer cambia)
   E) Bajo engagement (revisar voz/temas)

[11] NO HACER NUNCA (15)
   (Ver 07-PROHIBICIONES.md, copiar lista textual aquí)

[12] ¿QUÉ HACER AHORA?
   Saludar al admin con:
   "Hola Daniel. Sistema de marketing cargado. ¿Qué coach trabajamos hoy
    y para qué semana ISO?"

═══════════════════════════════════════════════════════════════════════════════
NOTAS PARA EL HUMANO
═══════════════════════════════════════════════════════════════════════════════
- Pegar este archivo COMPLETO al inicio de cada sesión nueva.
- Este prompt + leer los 20 MDs = todo lo que Claude necesita.
- Si la sesión pierde contexto, pegar de nuevo.
- Última actualización: 2026-04-26
```

Crear archivo. NO commit.

---

### Task 4.22: Sello del módulo M4

- [ ] **Step 1: Verificar que los 22 archivos existen**

```bash
ls "C:/Users/GODSF/Downloads/SISTEMA-CREACION-MARKETING-COACHES" | wc -l
```
Expected: 22.

- [ ] **Step 2: Tag (sin commit del repo, pero git tag para hito)**

```bash
git tag -a m4-mds-offline -m "Modulo 4 (Sistema offline MDs) completo"
```

---

# MÓDULO 5 — API Coach

**Deps:** M3
**Output:** Controllers + Resources + routes para `/api/v/coach/marketing-profile/*` y `/api/v/coach/strategy/*`.

**Files affected:**
- Create: `app/Http/Controllers/Api/Coach/MarketingProfileController.php`
- Create: `app/Http/Controllers/Api/Coach/StrategyController.php`
- Create: `app/Http/Controllers/Api/Coach/PieceStateController.php`
- Create: `app/Http/Resources/Coach/Marketing/MarketingProfileResource.php`
- Create: `app/Http/Resources/Coach/Marketing/CoachDropResource.php`
- Create: `app/Http/Resources/Coach/Marketing/CoachDropSummaryResource.php`
- Create: `app/Http/Resources/Coach/Marketing/PieceStateResource.php`
- Modify: `routes/api.php` (o `routes/api_coach.php` si existe)

---

### Task 5.1: `MarketingProfileResource`

```php
<?php
declare(strict_types=1);
namespace App\Http\Resources\Coach\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CoachMarketingProfile */
final class MarketingProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand_name' => $this->brand_name,
            'city' => $this->city,
            'country_code' => $this->country_code,
            'specialty_primary' => $this->specialty_primary?->value,
            'specialty_primary_other' => $this->specialty_primary_other,
            'specialty_secondary' => $this->specialty_secondary?->value,
            'specialty_secondary_other' => $this->specialty_secondary_other,
            'differentiator' => $this->differentiator,
            'audience_age_range' => $this->audience_age_range?->value,
            'audience_gender' => $this->audience_gender?->value,
            'audience_pain_main' => $this->audience_pain_main,
            'audience_offer_main' => $this->audience_offer_main?->value,
            'preferred_methodologies' => $this->preferred_methodologies ?? [],
            'preferred_methodologies_other' => $this->preferred_methodologies_other ?? [],
            'content_topics' => $this->content_topics ?? [],
            'content_topics_other' => $this->content_topics_other ?? [],
            'voice_adjectives' => $this->voice_adjectives ?? [],
            'voice_samples' => $this->voice_samples ?? [],
            'active_offers' => $this->active_offers ?? [],
            'top_working_posts' => $this->top_working_posts ?? [],
            'completed_at' => $this->completed_at?->toIso8601String(),
            'is_complete' => $this->isComplete(),
        ];
    }
}
```

Commit:
```bash
git add app/Http/Resources/Coach/Marketing/MarketingProfileResource.php
git commit -m "feat(api): MarketingProfileResource"
```

---

### Task 5.2: `MarketingProfileController`

```php
<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StoreMarketingProfileRequest;
use App\Http\Requests\Coach\UpdateMarketingProfileDraftRequest;
use App\Http\Resources\Coach\Marketing\MarketingProfileResource;
use App\Models\CoachMarketingProfile;
use App\Enums\Marketing\LastUpdatedBy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class MarketingProfileController extends Controller
{
    public function show(Request $request): MarketingProfileResource | JsonResponse
    {
        $coach = Auth::user();
        $profile = CoachMarketingProfile::where('coach_id', $coach->id)->first();

        if (!$profile) {
            return response()->json(['data' => null], 200);
        }
        return new MarketingProfileResource($profile);
    }

    public function store(StoreMarketingProfileRequest $request): MarketingProfileResource
    {
        $coach = Auth::user();
        $data = $request->validated();
        $data['coach_id'] = $coach->id;
        $data['last_updated_by'] = LastUpdatedBy::Coach;
        $data['completed_at'] = now();

        $profile = CoachMarketingProfile::updateOrCreate(
            ['coach_id' => $coach->id],
            $data,
        );

        return new MarketingProfileResource($profile);
    }

    public function updateDraft(UpdateMarketingProfileDraftRequest $request): MarketingProfileResource
    {
        $coach = Auth::user();
        $data = $request->validated();
        $data['last_updated_by'] = LastUpdatedBy::Coach;
        // No completed_at — sigue null hasta submit final.

        $profile = CoachMarketingProfile::updateOrCreate(
            ['coach_id' => $coach->id],
            $data,
        );
        return new MarketingProfileResource($profile);
    }
}
```

Routes (en `routes/api.php`, agregar bloque):

```php
Route::middleware(['auth:wellcore', 'role:coach'])->prefix('api/v/coach/marketing-profile')->group(function () {
    Route::get('/', [MarketingProfileController::class, 'show']);
    Route::put('/', [MarketingProfileController::class, 'store']);
    Route::patch('/draft', [MarketingProfileController::class, 'updateDraft']);
});
```

(Verificar nombre del middleware exacto. En este codebase puede ser `wellcore.guard` u otro — leer `app/Http/Kernel.php` y `app/Auth/`.)

Test feature:
```php
it('coach can submit complete profile', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    actingAs($coach)->putJson('/api/v/coach/marketing-profile', [/* full payload */])
        ->assertOk()
        ->assertJsonPath('data.is_complete', true);
});

it('rejects coach from another role', function () {
    $admin = Admin::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin)->getJson('/api/v/coach/marketing-profile')->assertForbidden();
});
```

Commit:
```bash
git add app/Http/Controllers/Api/Coach/MarketingProfileController.php routes/api.php tests/Feature/Coach/Marketing/MarketingProfileEndpointTest.php
git commit -m "feat(api): endpoints coach marketing-profile (GET/PUT/PATCH draft)"
```

---

### Task 5.3: `CoachDropResource` (sin campos sensibles)

```php
<?php
declare(strict_types=1);
namespace App\Http\Resources\Coach\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CoachContentDrop */
final class CoachDropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'week_starts_on' => $this->week_starts_on?->toDateString(),
            'status' => $this->status->value,
            'content' => $this->content,            // pasa tal cual; el JSON ya está validado
            'schema_version' => $this->schema_version,
            'attribution' => config('marketing.attribution.line'),
            'ready_at' => $this->ready_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'pieces' => PieceStateResource::collection($this->whenLoaded('pieceStates')),
            // NO expone: admin_edits_diff, generated_by_session_id, original_content,
            //            intake_snapshot, reviewed_by_id, approved_by_id, generated_at,
            //            reviewed_at, approved_at.
        ];
    }
}
```

`CoachDropSummaryResource` (para historial — payload chico):

```php
<?php
declare(strict_types=1);
namespace App\Http\Resources\Coach\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CoachContentDrop */
final class CoachDropSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'week_starts_on' => $this->week_starts_on?->toDateString(),
            'status' => $this->status->value,
            'brief_title' => data_get($this->content, 'brief.title'),
            'pieces_completed' => $this->pieceStates->where('state', 'published')->count(),
            'pieces_total' => $this->pieceStates->count(),
        ];
    }
}
```

`PieceStateResource`:

```php
<?php
declare(strict_types=1);
namespace App\Http\Resources\Coach\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CoachContentPieceState */
final class PieceStateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'piece_type' => $this->piece_type->value,
            'piece_key' => $this->piece_key,
            'state' => $this->state->value,
            'published_url' => $this->published_url,
            'state_changed_at' => $this->state_changed_at?->toIso8601String(),
        ];
    }
}
```

Commit:
```bash
git add app/Http/Resources/Coach/Marketing
git commit -m "feat(api): Resources del coach (Drop + Summary + PieceState) sin campos sensibles"
```

---

### Task 5.4: `StrategyController`

```php
<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Http\Resources\Coach\Marketing\CoachDropResource;
use App\Http\Resources\Coach\Marketing\CoachDropSummaryResource;
use App\Models\CoachContentDrop;
use App\Enums\Marketing\DropStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

final class StrategyController extends Controller
{
    public function current(Request $request): CoachDropResource | JsonResponse
    {
        $coach = Auth::user();
        $monday = now()->startOfWeek();
        $year = (int) $monday->isoFormat('GGGG');
        $week = (int) $monday->isoFormat('W');

        $drop = Cache::remember(
            "coach_drop_v3:{$coach->id}:{$year}:{$week}",
            300,
            function () use ($coach, $year, $week) {
                return CoachContentDrop::with('pieceStates')
                    ->where('coach_id', $coach->id)
                    ->where('iso_year', $year)
                    ->where('iso_week', $week)
                    ->whereIn('status', [
                        DropStatus::Ready->value,
                        DropStatus::InProgress->value,
                        DropStatus::Completed->value,
                    ])
                    ->first();
            }
        );

        if (!$drop) {
            return response()->json(['data' => null], 200);
        }
        $this->authorize('view', $drop);
        return new CoachDropResource($drop);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $coach = Auth::user();
        $perPage = min((int) $request->query('per_page', 20), 50);

        $drops = CoachContentDrop::with('pieceStates')
            ->where('coach_id', $coach->id)
            ->whereIn('status', [
                DropStatus::Ready->value, DropStatus::InProgress->value,
                DropStatus::Completed->value, DropStatus::Archived->value,
            ])
            ->orderByDesc('iso_year')->orderByDesc('iso_week')
            ->paginate($perPage);

        return CoachDropSummaryResource::collection($drops);
    }

    public function show(Request $request, CoachContentDrop $drop): CoachDropResource
    {
        $this->authorize('view', $drop);
        $drop->load('pieceStates');
        return new CoachDropResource($drop);
    }
}
```

Routes:
```php
Route::middleware(['auth:wellcore','role:coach','complete-brand-profile'])->prefix('api/v/coach/strategy')->group(function () {
    Route::get('current', [StrategyController::class, 'current']);
    Route::get('history', [StrategyController::class, 'history']);
    Route::get('drops/{drop}', [StrategyController::class, 'show']);
});
```

Test (incluye IDOR + cache + pagination):

```php
it('coach gets current drop only when ready+', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    $monday = now()->startOfWeek();
    CoachContentDrop::factory()->ready()->create([
        'coach_id' => $coach->id,
        'iso_year' => (int)$monday->isoFormat('GGGG'),
        'iso_week' => (int)$monday->isoFormat('W'),
    ]);
    actingAs($coach)->getJson('/api/v/coach/strategy/current')->assertOk()
        ->assertJsonStructure(['data' => ['id','status','content','attribution']]);
});

it('rejects access to drop of another coach (IDOR)', function () {
    $a = Admin::factory()->create(['role' => UserRole::Coach]);
    $b = Admin::factory()->create(['role' => UserRole::Coach]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $b->id]);
    actingAs($a)->getJson("/api/v/coach/strategy/drops/{$drop->id}")->assertForbidden();
});

it('history paginates with per_page max 50', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    CoachContentDrop::factory()->ready()->count(30)->create(['coach_id' => $coach->id]);
    actingAs($coach)->getJson('/api/v/coach/strategy/history?per_page=10')
        ->assertOk()->assertJsonCount(10, 'data');
});
```

Commit:
```bash
git add app/Http/Controllers/Api/Coach/StrategyController.php routes/api.php tests/Feature/Coach/Marketing/StrategyEndpointsTest.php
git commit -m "feat(api): StrategyController (current/history/show) con cache y IDOR-proof"
```

---

### Task 5.5: `PieceStateController`

```php
<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\MarkPiecePublishedRequest;
use App\Http\Resources\Coach\Marketing\PieceStateResource;
use App\Models\CoachContentDrop;
use App\Models\CoachContentPieceState;
use App\Enums\Marketing\PieceState;
use Illuminate\Support\Facades\Cache;

final class PieceStateController extends Controller
{
    public function publish(MarkPiecePublishedRequest $request, CoachContentDrop $drop, string $pieceKey): PieceStateResource
    {
        $this->authorize('markPiecePublished', $drop);

        $state = CoachContentPieceState::updateOrCreate(
            ['drop_id' => $drop->id, 'piece_type' => $this->detectType($pieceKey), 'piece_key' => $pieceKey],
            [
                'coach_id' => $drop->coach_id,
                'state' => PieceState::Published,
                'published_url' => $request->validated('url'),
                'notes' => $request->validated('notes'),
                'state_changed_at' => now(),
            ],
        );

        Cache::forget("coach_drop_v3:{$drop->coach_id}:{$drop->iso_year}:{$drop->iso_week}");

        return new PieceStateResource($state);
    }

    public function skip(CoachContentDrop $drop, string $pieceKey): PieceStateResource
    {
        $this->authorize('markPiecePublished', $drop);
        $state = CoachContentPieceState::updateOrCreate(
            ['drop_id' => $drop->id, 'piece_type' => $this->detectType($pieceKey), 'piece_key' => $pieceKey],
            ['coach_id' => $drop->coach_id, 'state' => PieceState::Skipped, 'state_changed_at' => now()],
        );
        Cache::forget("coach_drop_v3:{$drop->coach_id}:{$drop->iso_year}:{$drop->iso_week}");
        return new PieceStateResource($state);
    }

    public function inProgress(CoachContentDrop $drop, string $pieceKey): PieceStateResource
    {
        $this->authorize('markPiecePublished', $drop);
        $state = CoachContentPieceState::updateOrCreate(
            ['drop_id' => $drop->id, 'piece_type' => $this->detectType($pieceKey), 'piece_key' => $pieceKey],
            ['coach_id' => $drop->coach_id, 'state' => PieceState::InProgress, 'state_changed_at' => now()],
        );
        return new PieceStateResource($state);
    }

    private function detectType(string $key): \App\Enums\Marketing\PieceType
    {
        return match (true) {
            str_starts_with($key, 'reel_') => \App\Enums\Marketing\PieceType::Reel,
            str_starts_with($key, 'story_') => \App\Enums\Marketing\PieceType::Story,
            str_starts_with($key, 'phase_') => \App\Enums\Marketing\PieceType::ChecklistPhase,
            default => throw new \InvalidArgumentException("Unknown piece key prefix: {$key}"),
        };
    }
}
```

Routes:
```php
Route::middleware(['auth:wellcore','role:coach'])->prefix('api/v/coach/strategy/drops/{drop}/pieces')->group(function () {
    Route::post('{pieceKey}/publish', [PieceStateController::class, 'publish']);
    Route::post('{pieceKey}/skip', [PieceStateController::class, 'skip']);
    Route::post('{pieceKey}/in-progress', [PieceStateController::class, 'inProgress']);
});
```

Test:
```php
it('coach marks piece as published', function () {
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $coach->id]);
    actingAs($coach)->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/reel_1/publish",
        ['url' => 'https://instagram.com/p/abc'])
        ->assertOk()->assertJsonPath('data.state', 'published');
});

it('blocks IDOR cross-coach piece state', function () {
    $a = Admin::factory()->create(['role' => UserRole::Coach]);
    $b = Admin::factory()->create(['role' => UserRole::Coach]);
    $drop = CoachContentDrop::factory()->ready()->create(['coach_id' => $b->id]);
    actingAs($a)->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/reel_1/publish")
        ->assertForbidden();
});
```

Commit:
```bash
git add app/Http/Controllers/Api/Coach/PieceStateController.php routes/api.php tests/Feature/Coach/Marketing/PieceStateEndpointTest.php
git commit -m "feat(api): PieceStateController (publish/skip/in-progress) IDOR-proof"
```

---

### Task 5.6: Sello del módulo M5

```bash
vendor/bin/pest tests/Feature/Coach/Marketing
git tag -a m5-api-coach -m "Modulo 5 (API Coach) completo"
```

---

# MÓDULO 6 — API Admin

**Deps:** M3
**Output:** Controllers + Resources + routes admin para queue, drop review, profile edit.

**Files affected:**
- Create: `app/Http/Controllers/Api/Admin/Marketing/QueueController.php`
- Create: `app/Http/Controllers/Api/Admin/Marketing/DropReviewController.php`
- Create: `app/Http/Controllers/Api/Admin/Marketing/CoachProfileController.php`
- Create: `app/Http/Resources/Admin/Marketing/AdminDropResource.php`
- Create: `app/Http/Resources/Admin/Marketing/QueueRowResource.php`
- Create: `app/Services/Marketing/DropDiffCalculator.php`
- Modify: `routes/api.php`

---

### Task 6.1: `AdminDropResource` (incluye campos sensibles)

```php
<?php
declare(strict_types=1);
namespace App\Http\Resources\Admin\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CoachContentDrop */
final class AdminDropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coach_id' => $this->coach_id,
            'coach' => [
                'id' => $this->coach?->id,
                'name' => $this->coach?->name,
                'role' => $this->coach?->role?->value,
            ],
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'status' => $this->status->value,
            'content' => $this->content,
            'original_content' => $this->original_content,
            'intake_snapshot' => $this->intake_snapshot,
            'admin_edits_diff' => $this->admin_edits_diff,
            'generated_by_session_id' => $this->generated_by_session_id,
            'generated_at' => $this->generated_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'ready_at' => $this->ready_at?->toIso8601String(),
        ];
    }
}
```

`QueueRowResource`:

```php
<?php
declare(strict_types=1);
namespace App\Http\Resources\Admin\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CoachContentDrop */
final class QueueRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coach' => ['id' => $this->coach?->id, 'name' => $this->coach?->name],
            'iso_year' => $this->iso_year,
            'iso_week' => $this->iso_week,
            'status' => $this->status->value,
            'last_action_at' => ($this->reviewed_at ?? $this->generated_at ?? $this->created_at)?->toIso8601String(),
        ];
    }
}
```

Commit:
```bash
git add app/Http/Resources/Admin/Marketing
git commit -m "feat(api): AdminDropResource + QueueRowResource"
```

---

### Task 6.2: `QueueController`

```php
<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Marketing\QueueRowResource;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Enums\UserRole;
use App\Enums\Marketing\DropStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class QueueController extends Controller
{
    public function index(Request $request): array
    {
        abort_unless(in_array($request->user()->role, [UserRole::Admin, UserRole::Superadmin], true), 403);

        $query = CoachContentDrop::with('coach')
            ->orderByDesc('iso_year')
            ->orderByDesc('iso_week');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($coachId = $request->query('coach_id')) {
            $query->where('coach_id', $coachId);
        }
        if ($year = $request->query('iso_year')) {
            $query->where('iso_year', $year);
        }
        if ($week = $request->query('iso_week')) {
            $query->where('iso_week', $week);
        }

        $rows = $query->paginate(50);

        $monday = now()->startOfWeek();
        $coachesWithoutDropThisWeek = Admin::where('role', UserRole::Coach)
            ->whereNotIn('id', function ($q) use ($monday) {
                $q->select('coach_id')->from('coach_content_drops')
                  ->where('iso_year', (int)$monday->isoFormat('GGGG'))
                  ->where('iso_week', (int)$monday->isoFormat('W'));
            })
            ->count();

        return [
            'data' => QueueRowResource::collection($rows),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'total' => $rows->total(),
                'pending_review_count' => CoachContentDrop::where('status', DropStatus::InReview)->count(),
                'coaches_without_drop_this_week' => $coachesWithoutDropThisWeek,
            ],
        ];
    }
}
```

Routes + test (queue lista, contadores correctos, role check). Commit.

```bash
git add app/Http/Controllers/Api/Admin/Marketing/QueueController.php routes/api.php tests/Feature/Admin/Marketing/QueueEndpointTest.php
git commit -m "feat(api): admin marketing queue con stats top"
```

---

### Task 6.3: `DropDiffCalculator` service

Necesario para calcular `admin_edits_diff` al approve.

```php
<?php
declare(strict_types=1);
namespace App\Services\Marketing;

final class DropDiffCalculator
{
    /**
     * Diff plano por path leaf-level. Retorna array de cambios:
     * [{path: 'brief.title', original: 'X', edited: 'Y'}, ...]
     */
    public function diff(array $original, array $edited): array
    {
        $diffs = [];
        $this->walk($original, $edited, '', $diffs);
        return $diffs;
    }

    private function walk(mixed $a, mixed $b, string $path, array &$out): void
    {
        if (is_array($a) && is_array($b) && !$this->isList($a) && !$this->isList($b)) {
            $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
            foreach ($keys as $k) {
                $this->walk($a[$k] ?? null, $b[$k] ?? null, $path === '' ? (string)$k : "{$path}.{$k}", $out);
            }
            return;
        }

        if ($a !== $b) {
            $out[] = ['path' => $path, 'original' => $a, 'edited' => $b];
        }
    }

    private function isList(array $arr): bool
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
```

Test:
```php
it('detects single field change', function () {
    $diff = (new DropDiffCalculator())->diff(
        ['brief' => ['title' => 'A']],
        ['brief' => ['title' => 'B']],
    );
    expect($diff)->toBe([['path' => 'brief.title', 'original' => 'A', 'edited' => 'B']]);
});

it('treats list arrays as leaves (no per-index diff)', function () {
    $diff = (new DropDiffCalculator())->diff(
        ['bank' => ['alt_hooks' => ['a','b','c']]],
        ['bank' => ['alt_hooks' => ['a','x','c']]],
    );
    expect($diff[0]['path'])->toBe('bank.alt_hooks');
});
```

Commit:
```bash
git add app/Services/Marketing/DropDiffCalculator.php tests/Unit/Marketing/DropDiffCalculatorTest.php
git commit -m "feat(marketing): DropDiffCalculator path-based"
```

---

### Task 6.4: `DropReviewController`

```php
<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Marketing\ApproveDropRequest;
use App\Http\Requests\Admin\Marketing\RequestRegenerateRequest;
use App\Http\Requests\Admin\Marketing\UpdateDropContentRequest;
use App\Http\Resources\Admin\Marketing\AdminDropResource;
use App\Models\CoachContentDrop;
use App\Enums\Marketing\DropStatus;
use App\Services\Marketing\DropDiffCalculator;
use App\Services\Marketing\DropSchemaValidator;
use App\Services\Marketing\DropStateMachine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

final class DropReviewController extends Controller
{
    public function __construct(
        private readonly DropSchemaValidator $validator,
        private readonly DropStateMachine $stateMachine,
        private readonly DropDiffCalculator $diff,
    ) {}

    public function show(CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.viewDrop', $drop);
        $drop->load('coach', 'pieceStates');
        return new AdminDropResource($drop);
    }

    public function updateContent(UpdateDropContentRequest $request, CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.updateDrop', $drop);
        $payload = $request->validated('content');
        $this->validator->validate($payload);
        $drop->content = $payload;
        $drop->save();
        $this->forgetCache($drop);
        return new AdminDropResource($drop->refresh());
    }

    public function approve(ApproveDropRequest $request, CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.approveDrop', $drop);
        $admin = Auth::user();

        // Calcular diff final-vs-original
        $drop->admin_edits_diff = $this->diff->diff(
            $drop->original_content ?? $drop->content,
            $drop->content,
        );
        $drop->save();

        // Transición in_review → approved → ready
        $this->stateMachine->transition($drop, DropStatus::Approved, $admin);
        $this->stateMachine->transition($drop->fresh(), DropStatus::Ready, $admin);

        $this->forgetCache($drop);
        return new AdminDropResource($drop->fresh()->load('coach','pieceStates'));
    }

    public function requestRegenerate(RequestRegenerateRequest $request, CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.requestRegenerate', $drop);
        $admin = Auth::user();

        $this->stateMachine->transition($drop, DropStatus::Pending, $admin);
        $this->forgetCache($drop);
        return new AdminDropResource($drop->fresh());
    }

    private function forgetCache(CoachContentDrop $drop): void
    {
        Cache::forget("coach_drop_v3:{$drop->coach_id}:{$drop->iso_year}:{$drop->iso_week}");
    }
}
```

Routes:
```php
Route::middleware(['auth:wellcore'])->prefix('api/v/admin/marketing/drops')->group(function () {
    Route::get('{drop}', [DropReviewController::class, 'show']);
    Route::put('{drop}/content', [DropReviewController::class, 'updateContent']);
    Route::post('{drop}/approve', [DropReviewController::class, 'approve']);
    Route::post('{drop}/request-regenerate', [DropReviewController::class, 'requestRegenerate']);
});
```

Tests críticos:
- show retorna campos sensibles (admin_edits_diff, intake_snapshot)
- updateContent valida schema (rechaza JSON inválido con 422)
- approve calcula diff y persiste
- approve mueve a ready
- requestRegenerate vuelve a pending
- coach role NO puede acceder (forbidden)

Commit:
```bash
git add app/Http/Controllers/Api/Admin/Marketing/DropReviewController.php routes/api.php tests/Feature/Admin/Marketing/DropReviewTest.php
git commit -m "feat(api): DropReviewController con approve+diff y requestRegenerate"
```

---

### Task 6.5: `CoachProfileController` (admin edit del intake)

```php
<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coach\StoreMarketingProfileRequest;
use App\Http\Resources\Coach\Marketing\MarketingProfileResource;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use App\Enums\UserRole;
use App\Enums\Marketing\LastUpdatedBy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class CoachProfileController extends Controller
{
    public function show(Admin $coach): MarketingProfileResource | JsonResponse
    {
        abort_unless(in_array(Auth::user()->role, [UserRole::Admin, UserRole::Superadmin], true), 403);
        abort_unless($coach->role === UserRole::Coach, 404);

        $profile = CoachMarketingProfile::where('coach_id', $coach->id)->first();
        if (!$profile) return response()->json(['data' => null]);
        return new MarketingProfileResource($profile);
    }

    public function update(StoreMarketingProfileRequest $request, Admin $coach): MarketingProfileResource
    {
        abort_unless(in_array(Auth::user()->role, [UserRole::Admin, UserRole::Superadmin], true), 403);
        abort_unless($coach->role === UserRole::Coach, 404);

        $admin = Auth::user();
        $data = $request->validated();
        $data['coach_id'] = $coach->id;
        $data['last_updated_by'] = LastUpdatedBy::Admin;
        $data['last_admin_editor_id'] = $admin->id;
        if (!isset($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        $profile = CoachMarketingProfile::updateOrCreate(['coach_id' => $coach->id], $data);
        return new MarketingProfileResource($profile);
    }
}
```

Routes + tests (admin can read any coach profile, coach forbidden, audit field set). Commit.

```bash
git add app/Http/Controllers/Api/Admin/Marketing/CoachProfileController.php routes/api.php tests/Feature/Admin/Marketing/AdminCoachProfileTest.php
git commit -m "feat(api): admin edit del intake del coach con audit"
```

---

### Task 6.6: Sello del módulo M6

```bash
vendor/bin/pest tests/Feature/Admin/Marketing
git tag -a m6-api-admin -m "Modulo 6 (API Admin) completo"
```

---

# MÓDULO 7 — Frontend Foundation

**Deps:** M5, M6 (necesita endpoints existentes para los stores)
**Output:** Tailwind tokens + Fraunces + types generados desde JSON Schema + Pinia store + router middleware + nav item.

**Files affected:**
- Modify: `resources/css/app.css` (tokens day-coded ya creados en M0; agregar más)
- Create: `resources/js/vue/types/coach-drop-v1.generated.ts` (auto)
- Create: `resources/js/vue/types/marketing.ts` (manual extensions)
- Create: `resources/js/vue/stores/coachStrategy.ts`
- Create: `resources/js/vue/api/coachStrategy.ts`
- Modify: `resources/js/vue/router/index.js` (add routes + middleware)
- Modify: `resources/js/vue/layouts/CoachLayout.vue` (nav item)
- Modify: `resources/js/vue/app.js` (hidratar store on mount)

---

### Task 7.1: Tokens día-codeados en `app.css`

Agregar al `@theme` block:

```css
--color-wc-day-lun: #DC2626;
--color-wc-day-mar: #10B981;
--color-wc-day-mie: #F59E0B;
--color-wc-day-jue: #3B82F6;
--color-wc-day-vie: #A78BFA;
--color-wc-day-sab: #EC4899;
--color-wc-day-dom: #14B8A6;
```

Build + commit.

---

### Task 7.2: Generar tipos desde JSON Schema

```bash
npm run gen:schema-types
```

Verificar `resources/js/vue/types/coach-drop-v1.generated.ts` existe y compila.

Commit:
```bash
git add resources/js/vue/types/coach-drop-v1.generated.ts
git commit -m "feat(types): tipos TS generados desde coach_drop_v1.schema.json"
```

---

### Task 7.3: Tipos extendidos (`marketing.ts`)

```ts
import type { CoachDropV1 } from './coach-drop-v1.generated';

export type DropStatus =
  | 'pending' | 'generating' | 'in_review' | 'approved'
  | 'ready' | 'in_progress' | 'completed' | 'archived';

export interface MarketingProfile {
  id: number;
  brand_name: string;
  city: string | null;
  country_code: string | null;
  specialty_primary: string | null;
  specialty_primary_other: string | null;
  specialty_secondary: string | null;
  differentiator: string;
  audience_age_range: '18-25' | '25-35' | '35-45' | '45+';
  audience_gender: 'mujeres' | 'hombres' | 'mixto';
  audience_pain_main: string;
  audience_offer_main: 'esencial' | 'metodo' | 'elite' | 'presencial' | 'otro';
  preferred_methodologies: string[];
  content_topics: string[];
  voice_adjectives: [string, string, string];
  voice_samples: { caption: string; source_url: string | null; note: string | null }[];
  active_offers: { name: string; price: number; currency: string; promo: string | null }[];
  top_working_posts: { url: string; why_worked: string }[];
  completed_at: string | null;
  is_complete: boolean;
}

export interface PieceState {
  piece_type: 'reel' | 'story' | 'checklist_phase';
  piece_key: string;
  state: 'pending' | 'in_progress' | 'published' | 'skipped';
  published_url: string | null;
  state_changed_at: string | null;
}

export interface CoachDrop {
  id: number;
  iso_year: number;
  iso_week: number;
  week_starts_on: string;
  status: DropStatus;
  schema_version: 'coach_drop_v1';
  content: CoachDropV1;
  attribution: string;
  ready_at: string | null;
  completed_at: string | null;
  pieces: PieceState[];
}

export interface CoachDropSummary {
  id: number;
  iso_year: number;
  iso_week: number;
  week_starts_on: string;
  status: DropStatus;
  brief_title: string | null;
  pieces_completed: number;
  pieces_total: number;
}
```

Commit.

---

### Task 7.4: API client

```ts
// resources/js/vue/api/coachStrategy.ts
import axios from 'axios';
import type { CoachDrop, CoachDropSummary, MarketingProfile, PieceState } from '../types/marketing';

const api = axios.create({
  baseURL: '/api/v/coach',
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
});

api.interceptors.request.use(cfg => {
  const token = window.__WC_SESSION?.token;
  if (token) cfg.headers.Authorization = `Bearer ${token}`;
  return cfg;
});

export const coachStrategyApi = {
  async getProfile(): Promise<MarketingProfile | null> {
    const { data } = await api.get<{ data: MarketingProfile | null }>('/marketing-profile');
    return data.data ?? null;
  },
  async submitProfile(payload: Partial<MarketingProfile>): Promise<MarketingProfile> {
    const { data } = await api.put<{ data: MarketingProfile }>('/marketing-profile', payload);
    return data.data;
  },
  async saveDraft(patch: Partial<MarketingProfile>): Promise<MarketingProfile> {
    const { data } = await api.patch<{ data: MarketingProfile }>('/marketing-profile/draft', patch);
    return data.data;
  },
  async getCurrentDrop(): Promise<CoachDrop | null> {
    const { data } = await api.get<{ data: CoachDrop | null }>('/strategy/current');
    return data.data ?? null;
  },
  async getHistory(page = 1, perPage = 20): Promise<{ data: CoachDropSummary[]; meta: { total: number; current_page: number } }> {
    const { data } = await api.get('/strategy/history', { params: { page, per_page: perPage } });
    return data;
  },
  async getDropById(id: number): Promise<CoachDrop> {
    const { data } = await api.get<{ data: CoachDrop }>(`/strategy/drops/${id}`);
    return data.data;
  },
  async publishPiece(dropId: number, pieceKey: string, body: { url?: string; notes?: string } = {}): Promise<PieceState> {
    const { data } = await api.post<{ data: PieceState }>(`/strategy/drops/${dropId}/pieces/${pieceKey}/publish`, body);
    return data.data;
  },
  async skipPiece(dropId: number, pieceKey: string): Promise<PieceState> {
    const { data } = await api.post<{ data: PieceState }>(`/strategy/drops/${dropId}/pieces/${pieceKey}/skip`);
    return data.data;
  },
  async inProgressPiece(dropId: number, pieceKey: string): Promise<PieceState> {
    const { data } = await api.post<{ data: PieceState }>(`/strategy/drops/${dropId}/pieces/${pieceKey}/in-progress`);
    return data.data;
  },
};
```

Commit.

---

### Task 7.5: Pinia store `coachStrategy`

```ts
// resources/js/vue/stores/coachStrategy.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { coachStrategyApi } from '../api/coachStrategy';
import type { CoachDrop, CoachDropSummary, MarketingProfile, PieceState } from '../types/marketing';

export const useCoachStrategyStore = defineStore('coachStrategy', () => {
  const profile = ref<MarketingProfile | null>(null);
  const isLoadingProfile = ref(false);
  const currentDrop = ref<CoachDrop | null>(null);
  const isLoadingDrop = ref(false);
  const history = ref<CoachDropSummary[]>([]);
  const historyMeta = ref<{ total: number; current_page: number }>({ total: 0, current_page: 1 });

  const isProfileComplete = computed(() => profile.value?.is_complete === true);

  async function fetchProfile(): Promise<void> {
    isLoadingProfile.value = true;
    try { profile.value = await coachStrategyApi.getProfile(); }
    finally { isLoadingProfile.value = false; }
  }

  async function submitProfile(payload: Partial<MarketingProfile>): Promise<void> {
    profile.value = await coachStrategyApi.submitProfile(payload);
  }

  async function saveProfileDraft(patch: Partial<MarketingProfile>): Promise<void> {
    profile.value = await coachStrategyApi.saveDraft(patch);
  }

  async function fetchCurrentDrop(): Promise<void> {
    isLoadingDrop.value = true;
    try { currentDrop.value = await coachStrategyApi.getCurrentDrop(); }
    finally { isLoadingDrop.value = false; }
  }

  async function fetchHistory(page = 1): Promise<void> {
    const res = await coachStrategyApi.getHistory(page);
    history.value = res.data;
    historyMeta.value = res.meta;
  }

  async function markPiecePublished(pieceKey: string, url?: string, notes?: string): Promise<void> {
    if (!currentDrop.value) return;
    // Optimistic
    const prev = currentDrop.value.pieces.find(p => p.piece_key === pieceKey);
    const optimistic: PieceState = {
      piece_type: prev?.piece_type ?? 'reel',
      piece_key: pieceKey,
      state: 'published',
      published_url: url ?? null,
      state_changed_at: new Date().toISOString(),
    };
    const idx = currentDrop.value.pieces.findIndex(p => p.piece_key === pieceKey);
    if (idx >= 0) currentDrop.value.pieces[idx] = optimistic;
    else currentDrop.value.pieces.push(optimistic);

    try {
      const fresh = await coachStrategyApi.publishPiece(currentDrop.value.id, pieceKey, { url, notes });
      const i = currentDrop.value.pieces.findIndex(p => p.piece_key === pieceKey);
      if (i >= 0) currentDrop.value.pieces[i] = fresh;
    } catch (e) {
      // Rollback
      if (prev) {
        const i = currentDrop.value.pieces.findIndex(p => p.piece_key === pieceKey);
        if (i >= 0) currentDrop.value.pieces[i] = prev;
      }
      throw e;
    }
  }

  return {
    profile, isLoadingProfile, currentDrop, isLoadingDrop, history, historyMeta,
    isProfileComplete,
    fetchProfile, submitProfile, saveProfileDraft,
    fetchCurrentDrop, fetchHistory, markPiecePublished,
  };
});
```

Commit.

---

### Task 7.6: Router middleware `requireCompleteBrandProfile`

Modificar `resources/js/vue/router/index.js`:

```js
import { useCoachStrategyStore } from '../stores/coachStrategy';

router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresBrandProfile) {
    const auth = useAuthStore();
    if (auth.userType !== 'coach') return next({ name: 'login' });

    const store = useCoachStrategyStore();
    if (!store.profile) await store.fetchProfile();
    if (!store.isProfileComplete) {
      return next({ name: 'coach-onboarding-brand-profile' });
    }
  }
  next();
});
```

Agregar rutas:
```js
{ path: '/coach/onboarding/brand-profile', name: 'coach-onboarding-brand-profile',
  component: () => import('../pages/Coach/Onboarding/BrandProfile.vue'),
  meta: { auth: true, role: 'coach' } },
{ path: '/coach/strategy', name: 'coach-strategy',
  component: () => import('../pages/Coach/Strategy.vue'),
  meta: { auth: true, role: 'coach', requiresBrandProfile: true } },
{ path: '/admin/marketing/queue', name: 'admin-marketing-queue',
  component: () => import('../pages/Admin/Marketing/Queue.vue'),
  meta: { auth: true, role: 'admin' } },
{ path: '/admin/marketing/drops/:id', name: 'admin-marketing-drop-review',
  component: () => import('../pages/Admin/Marketing/DropReview.vue'),
  meta: { auth: true, role: 'admin' }, props: true },
```

Commit.

---

### Task 7.7: Nav item "Estrategia" en `CoachLayout.vue`

Modificar el bloque de items "Principal" agregando entre "Inicio" y "Clientes":

```vue
<RouterLink
  :to="{ name: 'coach-strategy' }"
  class="nav-item"
  active-class="nav-item--active"
>
  <span class="nav-item__icon"><!-- icon SVG --></span>
  <span class="nav-item__label">Estrategia</span>
  <span v-if="showNewBadge" class="nav-item__badge">Nuevo</span>
</RouterLink>
```

Lógica `showNewBadge`: `computed(() => Date.now() < new Date('2026-05-26').getTime())` (1 mes desde el feature flag flip).

Commit.

---

### Task 7.8: Hidratar store en `app.js`

Modificar `resources/js/vue/app.js` después de `mount()`:

```js
import { useAuthStore } from './stores/auth';
import { useCoachStrategyStore } from './stores/coachStrategy';

const pinia = createPinia();
app.use(pinia);

// Hydrate
const auth = useAuthStore();
auth.init();
if (auth.userType === 'coach') {
  useCoachStrategyStore().fetchProfile().catch(() => null);
}

app.use(router);
app.mount('#vue-app');
```

Build + commit.

---

### Task 7.9: Sello del módulo M7

```bash
npm run build
git tag -a m7-frontend-foundation -m "Modulo 7 (Frontend Foundation) completo"
```

---

# MÓDULO 8 — Frontend Coach Onboarding

**Deps:** M7
**Output:** `BrandProfile.vue` + 6 secciones + auto-save 500ms.

**Files affected:**
- Create: `resources/js/vue/pages/Coach/Onboarding/BrandProfile.vue`
- Create: `resources/js/vue/components/coach/onboarding/BrandProfileForm.vue`
- Create: `resources/js/vue/components/coach/onboarding/ProfileSection01Identity.vue`
- Create: `resources/js/vue/components/coach/onboarding/ProfileSection02Specialty.vue`
- Create: `resources/js/vue/components/coach/onboarding/ProfileSection03Audience.vue`
- Create: `resources/js/vue/components/coach/onboarding/ProfileSection04MethodsTopics.vue`
- Create: `resources/js/vue/components/coach/onboarding/ProfileSection05Voice.vue`
- Create: `resources/js/vue/components/coach/onboarding/ProfileSection06OffersAndPosts.vue`

---

### Task 8.1: `BrandProfile.vue` (page container)

```vue
<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';
import BrandProfileForm from '../../../components/coach/onboarding/BrandProfileForm.vue';
import CoachLayout from '../../../layouts/CoachLayout.vue';

const store = useCoachStrategyStore();
const router = useRouter();

onMounted(() => store.fetchProfile());

async function handleSubmit(payload) {
  await store.submitProfile(payload);
  router.push({ name: 'coach-strategy' });
}
</script>

<template>
  <CoachLayout>
    <div class="strategy-page mx-auto max-w-3xl px-6 py-12">
      <header class="mb-12">
        <p class="font-mono text-xs uppercase tracking-[0.2em] text-wc-text-tertiary">
          WC · ONBOARDING / BRAND-PROFILE
        </p>
        <h1 class="mt-3 font-display text-5xl uppercase tracking-tight">
          Tu perfil de marca
        </h1>
        <p class="mt-3 font-editorial italic text-lg text-wc-text-secondary">
          WellCore necesita conocerte para construir tu estrategia personalizada.
        </p>
      </header>

      <BrandProfileForm
        :initial="store.profile"
        @submit="handleSubmit"
      />
    </div>
  </CoachLayout>
</template>
```

Commit.

---

### Task 8.2: `BrandProfileForm.vue` (orquestador con auto-save)

```vue
<script setup lang="ts">
import { ref, watch, reactive } from 'vue';
import { debounce } from 'lodash-es';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';
import ProfileSection01Identity from './ProfileSection01Identity.vue';
import ProfileSection02Specialty from './ProfileSection02Specialty.vue';
import ProfileSection03Audience from './ProfileSection03Audience.vue';
import ProfileSection04MethodsTopics from './ProfileSection04MethodsTopics.vue';
import ProfileSection05Voice from './ProfileSection05Voice.vue';
import ProfileSection06OffersAndPosts from './ProfileSection06OffersAndPosts.vue';

const props = defineProps<{ initial: any | null }>();
const emit = defineEmits<{ (e: 'submit', payload: any): void }>();

const store = useCoachStrategyStore();
const isSaving = ref(false);

const form = reactive({
  brand_name: props.initial?.brand_name ?? '',
  city: props.initial?.city ?? '',
  country_code: props.initial?.country_code ?? '',
  specialty_primary: props.initial?.specialty_primary ?? null,
  specialty_primary_other: props.initial?.specialty_primary_other ?? '',
  specialty_secondary: props.initial?.specialty_secondary ?? null,
  specialty_secondary_other: props.initial?.specialty_secondary_other ?? '',
  differentiator: props.initial?.differentiator ?? '',
  audience_age_range: props.initial?.audience_age_range ?? null,
  audience_gender: props.initial?.audience_gender ?? null,
  audience_pain_main: props.initial?.audience_pain_main ?? '',
  audience_offer_main: props.initial?.audience_offer_main ?? null,
  preferred_methodologies: props.initial?.preferred_methodologies ?? [],
  preferred_methodologies_other: props.initial?.preferred_methodologies_other ?? [],
  content_topics: props.initial?.content_topics ?? [],
  content_topics_other: props.initial?.content_topics_other ?? [],
  voice_adjectives: props.initial?.voice_adjectives ?? [],
  voice_samples: props.initial?.voice_samples ?? [],
  active_offers: props.initial?.active_offers ?? [{ name: '', price: 0, currency: 'COP', promo: null }],
  top_working_posts: props.initial?.top_working_posts ?? [],
});

const saveDraft = debounce(async () => {
  isSaving.value = true;
  try { await store.saveProfileDraft(form); }
  finally { isSaving.value = false; }
}, 500);

watch(form, () => saveDraft(), { deep: true });

function handleSubmit() {
  emit('submit', form);
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-12">
    <ProfileSection01Identity v-model="form" />
    <ProfileSection02Specialty v-model="form" />
    <ProfileSection03Audience v-model="form" />
    <ProfileSection04MethodsTopics v-model="form" />
    <ProfileSection05Voice v-model="form" />
    <ProfileSection06OffersAndPosts v-model="form" />

    <div class="sticky bottom-6 flex items-center justify-between rounded-xl bg-wc-bg-secondary border border-wc-border p-4">
      <span class="font-mono text-xs text-wc-text-tertiary">
        {{ isSaving ? 'GUARDANDO...' : 'AUTO-GUARDADO ACTIVO' }}
      </span>
      <button type="submit"
        class="rounded-lg bg-wc-accent px-6 py-3 font-display text-sm uppercase tracking-wide text-white hover:opacity-90">
        Activar mi Estrategia
      </button>
    </div>
  </form>
</template>
```

Commit.

---

### Task 8.3-8.8: Las 6 secciones del intake

Cada sección sigue el mismo patrón. Ejemplo `ProfileSection01Identity.vue`:

```vue
<script setup lang="ts">
const props = defineProps<{ modelValue: any }>();
const emit = defineEmits<{ (e: 'update:modelValue', val: any): void }>();
function update(field: string, value: any) {
  emit('update:modelValue', { ...props.modelValue, [field]: value });
}
</script>

<template>
  <section class="border-t border-wc-border pt-12">
    <header class="mb-6">
      <p class="font-mono text-xs uppercase tracking-[0.15em] text-wc-accent">01 / IDENTIDAD</p>
      <h2 class="mt-2 font-display text-3xl uppercase">Tu marca personal</h2>
      <p class="mt-2 font-editorial italic text-base text-wc-text-secondary">
        Cómo te encuentran y de dónde eres.
      </p>
    </header>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs uppercase tracking-wide text-wc-text-tertiary mb-1">Nombre de marca</label>
        <input :value="modelValue.brand_name" @input="(e: any) => update('brand_name', e.target.value)"
          class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3" />
      </div>
      <div>
        <label class="block text-xs uppercase tracking-wide text-wc-text-tertiary mb-1">Ciudad</label>
        <input :value="modelValue.city" @input="(e: any) => update('city', e.target.value)"
          class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3" />
      </div>
    </div>
  </section>
</template>
```

Aplicar mismo patrón a las demás secciones (02 specialty con select+textarea, 03 audience con 4 chips groups, 04 methods/topics multi-select chips, 05 voice 3-chips + textarea voice samples, 06 offers/posts con repeater).

Commit cada sección por separado o agrupadas si son cortas.

---

### Task 8.9: Sello del módulo M8

```bash
npm run build
git tag -a m8-frontend-onboarding -m "Modulo 8 (Frontend Onboarding) completo"
```

---

# MÓDULO 9 — Frontend Coach Strategy Hub

**Deps:** M7
**Output:** `Strategy.vue` + 12+ componentes con dirección visual Editorial Production Document.

**Files affected:** ver §10.2 del spec — todos los `resources/js/vue/components/coach/strategy/*`.

---

### Task 9.1: `Strategy.vue` (page container con tabs)

```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useCoachStrategyStore } from '../../stores/coachStrategy';
import CoachLayout from '../../layouts/CoachLayout.vue';
import StrategyHero from '../../components/coach/strategy/StrategyHero.vue';
import StrategyEmptyState from '../../components/coach/strategy/StrategyEmptyState.vue';
import SectionDivider from '../../components/coach/strategy/SectionDivider.vue';
import BriefSection from '../../components/coach/strategy/BriefSection.vue';
import ReelScriptCard from '../../components/coach/strategy/ReelScriptCard.vue';
import StoriesWeekRow from '../../components/coach/strategy/StoriesWeekRow.vue';
import ProductionChecklistCard from '../../components/coach/strategy/ProductionChecklistCard.vue';
import WeeklyBankCard from '../../components/coach/strategy/WeeklyBankCard.vue';
import HashtagSetCard from '../../components/coach/strategy/HashtagSetCard.vue';
import StrategyHistoryList from '../../components/coach/strategy/StrategyHistoryList.vue';

const store = useCoachStrategyStore();
const tab = ref<'this-week'|'history'>('this-week');

onMounted(async () => {
  await store.fetchCurrentDrop();
  if (tab.value === 'history' && store.history.length === 0) await store.fetchHistory();
});
</script>

<template>
  <CoachLayout>
    <div class="strategy-page relative min-h-screen">
      <div class="mx-auto max-w-5xl px-6 py-12">

        <nav class="mb-8 flex gap-6 border-b border-wc-border">
          <button @click="tab = 'this-week'" :class="['py-3 font-mono text-xs uppercase tracking-[0.15em]',
            tab==='this-week' ? 'text-wc-text border-b-2 border-wc-accent' : 'text-wc-text-tertiary']">
            Esta semana
          </button>
          <button @click="async () => { tab = 'history'; if (!store.history.length) await store.fetchHistory(); }"
            :class="['py-3 font-mono text-xs uppercase tracking-[0.15em]',
            tab==='history' ? 'text-wc-text border-b-2 border-wc-accent' : 'text-wc-text-tertiary']">
            Historial
          </button>
        </nav>

        <template v-if="tab==='this-week'">
          <template v-if="store.isLoadingDrop">
            <div class="font-mono text-sm text-wc-text-tertiary">Cargando...</div>
          </template>
          <template v-else-if="!store.currentDrop">
            <StrategyEmptyState />
          </template>
          <template v-else>
            <StrategyHero :drop="store.currentDrop" />

            <SectionDivider number="01" title="BRIEF" sub="de la semana" />
            <BriefSection :brief="store.currentDrop.content.brief" />

            <SectionDivider number="02" title="REELS" sub="dos guiones de producción" />
            <ReelScriptCard v-for="reel in store.currentDrop.content.reels" :key="reel.key"
              :reel="reel" :drop-id="store.currentDrop.id"
              :state="store.currentDrop.pieces.find(p => p.piece_key === reel.key)?.state ?? 'pending'" />

            <SectionDivider number="03" title="STORIES" sub="siete piezas Lun → Dom" />
            <StoriesWeekRow :stories="store.currentDrop.content.stories" :drop-id="store.currentDrop.id" :pieces="store.currentDrop.pieces" />

            <SectionDivider number="04" title="CHECKLIST" sub="producción de reel" />
            <ProductionChecklistCard :checklist="store.currentDrop.content.checklist"
              :drop-id="store.currentDrop.id" :pieces="store.currentDrop.pieces" />

            <SectionDivider number="05" title="BANCO SEMANAL" sub="alternativos si la idea principal no encaja" />
            <WeeklyBankCard :bank="store.currentDrop.content.bank" />

            <SectionDivider number="06" title="HASHTAGS" sub="sets curados por tema" />
            <HashtagSetCard :hashtags="store.currentDrop.content.hashtags" />
          </template>
        </template>

        <StrategyHistoryList v-else />
      </div>
    </div>
  </CoachLayout>
</template>

<style scoped>
.strategy-page { background: #09090B; }
.strategy-page::before {
  content: ''; position: fixed; inset: 0; pointer-events: none;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'><filter id='n'><feTurbulence baseFrequency='0.85' numOctaves='2'/></filter><rect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/></svg>");
  mix-blend-mode: overlay; z-index: 1;
}
.strategy-page::after {
  content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%);
  width: 80%; height: 400px; pointer-events: none;
  background: radial-gradient(ellipse at center, rgba(220,38,38,0.08), transparent 60%);
  z-index: 0;
}
</style>
```

Commit.

---

### Task 9.2-9.13: Componentes hijo

Crear cada uno con el patrón visual del spec §9.4-9.7. Lista resumida con responsabilidad y referencia al spec:

| Componente | Líneas | Responsabilidad |
|---|---|---|
| `StrategyHero.vue` | 60-80 | Metadata strip + título Oswald + subhead Fraunces + attribution + progress strip |
| `StrategyEmptyState.vue` | 30 | "Tu drop está siendo preparado..." con CTA "Ver drop anterior" |
| `SectionDivider.vue` | 30 | "01 / BRIEF" con `/` rojo + Fraunces Italic subtitle |
| `BriefSection.vue` | 60 | Título + objetivo + chips priority_offer + key_message + framing_copy Fraunces |
| `ReelScriptCard.vue` | 150-200 | Strip metadata mono + hook block + tabla timecode + caption + música + production_notes + action row con `PieceMarkPublishedButton` |
| `ReelTimecodeTable.vue` | 80 | Tabla 4 cols (tiempo mono, diálogo Raleway, visual Raleway gris, edit Fraunces Italic ámbar) |
| `StoriesWeekRow.vue` | 100 | Grid responsive `lg:grid-cols-7 md:grid-cols-4 grid-cols-1` con scroll-snap en mobile |
| `StoryDayCard.vue` | 80-100 | Card 9:16 con day-coding semántico + preview text + click abre drawer |
| `StoryDrawer.vue` | 120 | Drawer lateral con template completo + `[Copiar texto]` + `[Descargar PNG]` (usa html-to-image) + checklist DM follow-up |
| `ProductionChecklistCard.vue` | 100 | 4 fases en accordion con items checkable persistidos en piece_states |
| `WeeklyBankCard.vue` | 80 | 3 columnas (hooks/CTAs/captions) con copiar al clipboard |
| `HashtagSetCard.vue` | 60 | Sets en tabs, copy-to-clipboard por set |
| `PieceMarkPublishedButton.vue` | 60 | Botón con menú: Publicado / Skip / In progress + opcional URL |
| `StrategyHistoryList.vue` | 80 | Cards summary con click → modal con drop completo |

**Patrón visual común a todas:**
- Background card: `bg-wc-bg-secondary border border-wc-border rounded-xl`
- Padding asimétrico: `pl-12 pr-6 py-8`
- Hover lift: `transform: translateY(-2px); box-shadow: 0 0 32px rgba(220,38,38,0.08)` con `transition: 240ms cubic-bezier(0.16,1,0.3,1)`
- Headers: Oswald 700 uppercase
- Labels mono: JetBrains Mono uppercase tracking-[0.15em] color text-wc-text-tertiary
- Quotes/notas: Fraunces Italic

**Implementación detallada para `StoryDrawer.vue` (export PNG):**

```ts
import { toPng } from 'html-to-image';

async function downloadPng() {
  const node = document.getElementById('story-export-template');
  if (!node) return;
  const dataUrl = await toPng(node, { width: 1080, height: 1920, pixelRatio: 1 });
  const link = document.createElement('a');
  link.download = `wellcore-story-${day}.png`;
  link.href = dataUrl;
  link.click();
}
```

El template oculto:
```vue
<div id="story-export-template" style="position:fixed;left:-9999px;top:0;width:1080px;height:1920px;background:#09090B;color:#FAFAFA;font-family:'Oswald';padding:120px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;">
  <h1 style="font-size:96px;line-height:1;font-weight:700;text-transform:uppercase;letter-spacing:0.02em;">{{ slide.text }}</h1>
</div>
```

Crear los 13 componentes uno por uno con tests visuales mínimos (montar y verificar render). Commits agrupados (3-4 componentes por commit).

---

### Task 9.14: Sello del módulo M9

```bash
npm run build
git tag -a m9-frontend-strategy-hub -m "Modulo 9 (Frontend Strategy Hub) completo"
```

---

# MÓDULO 10 — Frontend Admin

**Deps:** M7
**Output:** Queue + Drop Review + Coach Profile Edit páginas admin.

**Files affected:**
- Create: `resources/js/vue/pages/Admin/Marketing/Queue.vue`
- Create: `resources/js/vue/pages/Admin/Marketing/DropReview.vue`
- Create: `resources/js/vue/pages/Admin/Marketing/CoachProfileEdit.vue`
- Create: `resources/js/vue/api/adminMarketing.ts`
- Create: `resources/js/vue/stores/adminMarketing.ts`

---

### Task 10.1: `adminMarketingApi` y store

API client similar a M7 pero apunta a `/api/v/admin/marketing/*`. Métodos: `getQueue`, `getDrop`, `updateDropContent`, `approveDrop`, `requestRegenerate`, `getCoachProfile`, `updateCoachProfile`.

Store Pinia `adminMarketing` con queue + selectedDrop + filters.

---

### Task 10.2: `Queue.vue`

Stats top + filtros + tabla:

```vue
<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAdminMarketingStore } from '../../../stores/adminMarketing';

const store = useAdminMarketingStore();
const filters = ref({ status: '', coach_id: '', iso_year: '', iso_week: '' });

onMounted(() => store.fetchQueue(filters.value));
</script>

<template>
  <AdminLayout>
    <div class="strategy-page mx-auto max-w-6xl px-6 py-12">
      <header class="mb-12">
        <p class="font-mono text-xs uppercase tracking-[0.2em] text-wc-text-tertiary">WC · ADMIN / MARKETING / QUEUE</p>
        <h1 class="mt-3 font-display text-5xl uppercase">Cola de drops</h1>
      </header>

      <div class="grid grid-cols-3 gap-6 mb-8">
        <StatCard :n="store.meta.pending_review_count" label="Pending review" accent />
        <StatCard :n="store.meta.coaches_without_drop_this_week" label="Sin drop esta semana" />
        <StatCard :n="store.meta.total" label="Total drops históricos" />
      </div>

      <div class="mb-6 flex gap-3">
        <select v-model="filters.status" @change="store.fetchQueue(filters)">
          <option value="">Todos los estados</option>
          <option value="in_review">In review</option>
          <option value="ready">Ready</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <table class="w-full">
        <thead>
          <tr><th>Coach</th><th>Semana</th><th>Estado</th><th>Última acción</th><th>Acción</th></tr>
        </thead>
        <tbody>
          <tr v-for="row in store.queue" :key="row.id">
            <td>{{ row.coach.name }}</td>
            <td>{{ row.iso_year }}-W{{ row.iso_week }}</td>
            <td><StatusPill :status="row.status" /></td>
            <td class="font-mono text-xs">{{ row.last_action_at }}</td>
            <td><RouterLink :to="`/admin/marketing/drops/${row.id}`">Revisar →</RouterLink></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>
```

Commit.

---

### Task 10.3: `DropReview.vue` (split view)

```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAdminMarketingStore } from '../../../stores/adminMarketing';

const route = useRoute();
const router = useRouter();
const store = useAdminMarketingStore();
const id = Number(route.params.id);

onMounted(() => store.fetchDrop(id));

async function approve() {
  if (!confirm('¿Aprobar y publicar este drop al coach?')) return;
  await store.approveDrop(id);
  router.push({ name: 'admin-marketing-queue' });
}

async function regenerate() {
  const reason = prompt('Razón para regenerar (opcional):');
  await store.requestRegenerate(id, reason ?? undefined);
  router.push({ name: 'admin-marketing-queue' });
}

async function saveContent(newContent) {
  await store.updateDropContent(id, newContent);
}
</script>

<template>
  <AdminLayout>
    <div v-if="store.selectedDrop" class="grid grid-cols-2 gap-8 px-8 py-12">
      <aside>
        <h2 class="font-display text-2xl uppercase mb-4">Intake del coach</h2>
        <pre class="bg-wc-bg-secondary p-4 rounded-lg text-xs font-mono overflow-auto">
          {{ JSON.stringify(store.selectedDrop.intake_snapshot, null, 2) }}
        </pre>
      </aside>
      <main>
        <h2 class="font-display text-2xl uppercase mb-4">Drop content (editable)</h2>
        <DropContentEditor :content="store.selectedDrop.content" @save="saveContent" />
        <div class="mt-8 flex gap-3">
          <button @click="approve" class="bg-wc-accent text-white px-6 py-3 rounded-lg uppercase">Aprobar y publicar</button>
          <button @click="regenerate" class="border border-wc-border px-6 py-3 rounded-lg uppercase">Pedir regenerar</button>
        </div>
      </main>
    </div>
  </AdminLayout>
</template>
```

`DropContentEditor.vue`: textareas para cada sección editables (brief, reels, stories, etc.), con botón save por sección.

Commit.

---

### Task 10.4: `CoachProfileEdit.vue`

Reusa `BrandProfileForm.vue` de M8 pero apuntando a endpoints admin.

Commit.

---

### Task 10.5: Sello del módulo M10

```bash
npm run build
git tag -a m10-frontend-admin -m "Modulo 10 (Frontend Admin) completo"
```

---

# MÓDULO 11 — Operations & E2E

**Deps:** M5, M6, M7-M10
**Output:** Cron + integration test full-flow + build local + push.

**Files affected:**
- Create: `app/Console/Commands/ArchiveOldDropsCommand.php`
- Modify: `app/Console/Kernel.php` (schedule)
- Create: `tests/Feature/Marketing/EndToEndFlowTest.php`

---

### Task 11.1: `ArchiveOldDropsCommand`

```php
<?php
declare(strict_types=1);
namespace App\Console\Commands;

use App\Enums\Marketing\DropStatus;
use App\Models\CoachContentDrop;
use Illuminate\Console\Command;

final class ArchiveOldDropsCommand extends Command
{
    protected $signature = 'wellcore:archive-old-drops {--days=30 : Días desde completed antes de archivar}';
    protected $description = 'Archiva drops completados con más de N días';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = CoachContentDrop::where('status', DropStatus::Completed)
            ->where('completed_at', '<=', $cutoff)
            ->update(['status' => DropStatus::Archived]);

        $this->info("Archivados {$count} drops completados antes de {$cutoff}.");
        return self::SUCCESS;
    }
}
```

Schedule en `Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('wellcore:archive-old-drops')->dailyAt('03:00');
}
```

Test:
```php
it('archives drops completed >= 30 days ago', function () {
    CoachContentDrop::factory()->completed()->create(['completed_at' => now()->subDays(31)]);
    CoachContentDrop::factory()->completed()->create(['completed_at' => now()->subDays(10)]);
    artisan('wellcore:archive-old-drops')->assertSuccessful();
    expect(CoachContentDrop::where('status','archived')->count())->toBe(1);
});
```

Commit.

---

### Task 11.2: Integration test full-flow

```php
<?php
declare(strict_types=1);
use function Pest\Laravel\actingAs;
use App\Models\Admin;
use App\Models\CoachMarketingProfile;
use App\Models\CoachContentDrop;
use App\Enums\UserRole;
use App\Enums\Marketing\DropStatus;
use App\Services\Marketing\DropSchemaValidator;

it('end-to-end: onboarding → INSERT via tinker → admin approve → coach sees → mark published → archived', function () {
    // 1. Coach completa onboarding
    $coach = Admin::factory()->create(['role' => UserRole::Coach]);
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/intake_complete.json')), true);
    actingAs($coach)->putJson('/api/v/coach/marketing-profile', $payload)->assertOk();
    $profile = CoachMarketingProfile::where('coach_id', $coach->id)->firstOrFail();
    expect($profile->isComplete())->toBeTrue();

    // 2. Simular INSERT vía tinker (script PHP heredoc)
    $admin = Admin::factory()->create(['role' => UserRole::Admin]);
    $monday = now()->startOfWeek();
    $content = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);

    (new DropSchemaValidator())->validate($content);
    $drop = CoachContentDrop::create([
        'coach_id' => $coach->id,
        'iso_year' => (int)$monday->isoFormat('GGGG'),
        'iso_week' => (int)$monday->isoFormat('W'),
        'week_starts_on' => $monday->toDateString(),
        'status' => DropStatus::InReview,
        'content' => $content,
        'original_content' => $content,
        'intake_snapshot' => $profile->toArray(),
        'schema_version' => 'coach_drop_v1',
        'generated_at' => now(),
    ]);

    // 3. Admin lo aprueba
    actingAs($admin)->postJson("/api/v/admin/marketing/drops/{$drop->id}/approve")->assertOk();
    expect($drop->fresh()->status)->toBe(DropStatus::Ready);

    // 4. Coach lo ve
    $resp = actingAs($coach)->getJson('/api/v/coach/strategy/current')->assertOk();
    expect($resp->json('data.id'))->toBe($drop->id);

    // 5. Coach marca pieza
    actingAs($coach)->postJson("/api/v/coach/strategy/drops/{$drop->id}/pieces/reel_1/publish",
        ['url' => 'https://instagram.com/p/abc'])->assertOk();

    // 6. Force completed + archive
    $drop->update(['status' => DropStatus::Completed, 'completed_at' => now()->subDays(31)]);
    artisan('wellcore:archive-old-drops')->assertSuccessful();
    expect($drop->fresh()->status)->toBe(DropStatus::Archived);
});
```

Crear fixture `tests/fixtures/intake_complete.json` con un payload válido completo.

Run test → PASS. Commit.

---

### Task 11.3: Build local + commit assets

```bash
npm run build
git add public/build
git commit -m "build: assets compilados Strategy Hub"
```

---

### Task 11.4: Push

```bash
git push origin main
```

(NO deploy automático. Memory `feedback_push_not_deploy` y `feedback_no_npm_build`.)

---

### Task 11.5: Verificación en EasyPanel + MCP Chrome DevTools

- [ ] Step 1: En EasyPanel, ejecutar `gitpull-load` (NO `npm run build`).
- [ ] Step 2: Migrate en producción: `php artisan migrate --force`.
- [ ] Step 3: Activar feature flag: `MARKETING_ATTRIBUTION_LINE` (default OK), `FEATURE_COACH_STRATEGY_ENABLED=true` en `.env` del container.
- [ ] Step 4: MCP Chrome DevTools — navegar a `wellcorefitness.com`, login como coach, verificar:
  - `/coach/strategy` redirige a `/coach/onboarding/brand-profile` si profile incompleto.
  - Onboarding completa → redirige a `/coach/strategy` empty state premium.
  - Login admin → `/admin/marketing/queue` muestra stats + tabla.
- [ ] Step 5: Insertar drop de prueba vía tinker en EasyPanel:

```bash
php artisan tinker --execute='
$payload = json_decode(file_get_contents("schemas/coach_drop_v1.schema.json"), true);
// usar fixture coach_drop_v1_valid + intake snapshot
// (script heredoc completo en MD 30)
'
```

- [ ] Step 6: Admin revisa + aprueba. Coach refrezca → ve drop completo con dirección visual Editorial Production Document.

- [ ] Step 7: Captura screenshots de cada surface y guardar en `audit-design/strategy-hub-launch/`.

---

### Task 11.6: Sello del plan

```bash
git tag -a v1.0-coach-strategy-hub -m "Coach Strategy Hub Fase 1 + 1.5 — release v1"
git push origin --tags
```

---

# Self-Review del plan

**Spec coverage check:**
- §3 Architecture → M0+M1+M2+M3+M5+M6+M7 ✓
- §4 Data model → M1 ✓
- §5 JSON contract → M0.6 + M2.7 ✓
- §6 MD system → M4 ✓
- §7 UX coach → M8+M9 ✓
- §8 UX admin → M10 ✓
- §9 Visual direction → M0.7+M7.1+M9 ✓
- §10 Quality bar → M2+M3+M5+M6+M7 (Strict types, DTOs, Policies, Resources, Pinia, types from schema) ✓
- §11 API endpoints → M5+M6 ✓
- §12 Agent matrix → implícita en cada módulo (la-XX dispatcheable por subagent-driven exec) ✓
- §13 Memory hooks → M11.4 (push not deploy), M0+M11 (no npm build) ✓
- §14 Out of scope → no implementado, correcto ✓
- §16 Definition of done → M11.2 cubre los 12 criterios ✓

**Placeholder scan:** ningún TBD/TODO encontrado. Algunas referencias a "ver MD 30" donde el detalle vive en E:\, intencional.

**Type consistency:** `CoachDropV1`, `MarketingProfile`, `DropStatus`, `PieceState` consistentes en PHP enums + DTOs + TS types.

**Scope:** Plan focalizado en Fase 1 + 1.5. Story Editor (Fase 3) y Claude API auto-generación (Fase 2-D) explícitamente fuera.

---

## Plan complete

Plan saved to `docs/superpowers/plans/2026-04-26-coach-strategy-hub-implementation.md`. Two execution options:

**1. Subagent-Driven (recommended)** - Dispatch fresh subagent per task, review between tasks, fast iteration. Cada subagent puede ser un agente especializado de la matriz Laravel (la-02-backend, la-03-vue3, la-05-security, la-06-database, la-14-testing, etc.).

**2. Inline Execution** - Execute tasks in this session using executing-plans, batch execution with checkpoints for review.

¿Cuál?
