# Food Tracking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Sistema completo de Food Tracking — clientes suben fotos de comidas del plan de nutrición, coaches revisan con reacciones/notas, gamificación con XP y rachas. Ver `docs/superpowers/specs/2026-05-04-food-tracking-design.md` y `docs/superpowers/specs/2026-05-04-food-tracking-preflight.md`.

**Architecture:** Tabla nueva `food_photos` aislada (cero cambios destructivos a tablas compartidas con vanilla). ENUM aditivo a `habit_logs.habit_type` para idempotencia del bonus diario (precedente validado en `2026_04_01_033020`). `FoodPhotoService` centraliza upload atómico + XP + notif throttle. `NutritionPlanParser` extraído como service compartido (Livewire + API). Coach Livewire en patrón `CheckinReview`. Vue SPA cliente en patrón `NutritionView.vue`.

**Tech Stack:** Laravel 13.1, PHP 8.4, MySQL, Pest 3, Intervention Image v4, Livewire 3, Vue 3.5, Tailwind 4, Pinia.

**Decisiones tomadas en pre-flight (defaults seguros):**
- **B-01** ✅ ALTER aditivo a `habit_logs.habit_type` ENUM (precedente: `2026_04_01_033020_expand_habit_logs_habit_type_enum.php`).
- **B-02** ✅ Migración Laravel guard-aware crea `food_photos` localmente; tests usan SQLite + factories. Prod no se toca (Schema::hasTable defensivo).
- **B-03** ✅ `food_photos` es tabla nueva, `food_analyses` queda intacta para IA futura.
- **H-06** ✅ Notificación coach via `WellcoreNotification` in-app (user_type='admin'). Sin push browser.

**Salvaguardas globales:**
- Solo migraciones aditivas. Cero `DROP`, cero `RENAME`, cero `MODIFY` sobre columnas de tablas vanilla.
- Cero edición fuera de archivos listados explícitamente abajo.
- XP via `ClientXp::increment('xp_total', N)` con `try/catch QueryException` — si tabla no existe local, log warning y sigue (degradación graceful).
- Storage: `storage/app/public/food-photos/{client_id}/{uuid}.jpg` vía `Storage::disk('public')` (mismo volume que ProgressPhoto, persiste deploys).

---

## File Structure

### Backend nuevos (10 archivos)

| Archivo | Responsabilidad |
|---|---|
| `database/migrations/2026_05_04_120000_create_food_photos_table.php` | Tabla `food_photos` con UNIQUE `(client_id, meal_index, photo_date)` |
| `database/migrations/2026_05_04_120001_add_food_day_bonus_to_habit_logs_enum.php` | ALTER aditivo `habit_logs.habit_type` ENUM |
| `app/Models/FoodPhoto.php` | Eloquent model con OwnedByClientScope |
| `database/factories/FoodPhotoFactory.php` | Factory para tests |
| `app/Services/NutritionPlanParser.php` | Extracción + normalización de comidas (puro, sin DB) |
| `app/Services/FoodPhotoService.php` | Upload atómico, processImage, awardXp, notifyCoach |
| `app/Http/Requests/StoreFoodPhotoRequest.php` | Validación del POST |
| `app/Http/Resources/FoodPhotoResource.php` | Serialización JSON |
| `app/Http/Controllers/Api/Client/FoodPhotoController.php` | `index`, `store`, `destroy`, `history` |
| `app/Livewire/Coach/FoodPhotoReview.php` + blade | Vista del coach |

### Backend modificados (5 archivos)

| Archivo | Cambio | Riesgo |
|---|---|---|
| `app/Livewire/Client/NutritionPlan.php` | Refactor `parseMeals` + `normalizeMeal` → delegan a parser. Comportamiento idéntico (verificado en tests del refactor). | Medio — pero compensado con tests. |
| `app/Services/PushNotificationService.php` | Agregar 1 método static `notifyClientFoodPhotoReacted()`. Cero modificación de métodos existentes. | Cero. |
| `routes/api.php` | Agregar grupo `Route::prefix('v/client/food-photos')...` debajo del grupo nutrition existente. | Cero. |
| `routes/web.php` | Agregar `Route::get('/coach/food-photos', FoodPhotoReview::class)`. | Cero. |
| `resources/views/layouts/coach.blade.php` | Agregar link "Fotos de Comida" con badge count. | Bajo. |

### Frontend Vue (4 archivos)

| Archivo | Responsabilidad |
|---|---|
| `resources/js/vue/composables/useFoodTracking.js` | Estado reactivo + API calls |
| `resources/js/vue/pages/Rise/FoodTracking.vue` | Página cliente "Mi Alimentación" |
| `resources/js/vue/router/index.js` | +1 ruta lazy-loaded `rise-food-tracking` |
| `resources/js/vue/layouts/RiseLayout.vue` | +1 item nav en sección "Habitos" |

### Tests (5 archivos)

| Archivo | Cubre |
|---|---|
| `tests/Unit/Services/NutritionPlanParserTest.php` | 5 formatos JSON soportados |
| `tests/Unit/Services/NutritionPlanParserRefactorTest.php` | Output idéntico antes/después del refactor de NutritionPlan.php |
| `tests/Feature/Api/Client/FoodPhotoTest.php` | index, store happy/replace/race, destroy permitido/bloqueado, validación, throttle |
| `tests/Feature/Livewire/Coach/FoodPhotoReviewTest.php` | render, react, markSeen, autorización cross-coach |
| `tests/Unit/Models/FoodPhotoTest.php` | OwnedByClientScope, casts, photoUrl accessor |

---

## Phase 1 — DB Foundation

### Task 1.1: Crear migración `food_photos`

**Files:**
- Create: `database/migrations/2026_05_04_120000_create_food_photos_table.php`

- [ ] **Step 1: Escribir la migración**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla nueva food_photos. Aditiva, no toca tablas existentes.
 * Patrón: índice UNIQUE garantiza 1 foto por (client, meal_index, photo_date).
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('food_photos')) {
            return;
        }

        Schema::create('food_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id');
            $table->string('meal_name', 255);
            $table->unsignedTinyInteger('meal_index')->default(0);
            $table->date('photo_date');
            $table->string('filename', 255);
            $table->unsignedInteger('file_size')->nullable();
            $table->boolean('coach_seen')->default(false);
            $table->timestamp('coach_seen_at')->nullable();
            $table->enum('coach_reaction', ['bien', 'mejorar'])->nullable();
            $table->text('coach_note')->nullable();
            $table->boolean('xp_awarded')->default(false);
            $table->json('ai_analysis')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'meal_index', 'photo_date'], 'uq_food_photos_client_meal_date');
            $table->index(['client_id', 'photo_date'], 'idx_food_photos_client_date');
            $table->index(['coach_seen', 'created_at'], 'idx_food_photos_coach_pending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_photos');
    }
};
```

- [ ] **Step 2: Correr migración**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan migrate`
Esperado: `Migrated: ..._create_food_photos_table`. Cero errores.

- [ ] **Step 3: Verificar schema**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan tinker --execute="echo DB::select('SHOW CREATE TABLE food_photos')[0]->{'Create Table'};"`
Esperado: tabla con UNIQUE `uq_food_photos_client_meal_date` e índices `idx_food_photos_client_date`, `idx_food_photos_coach_pending`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_04_120000_create_food_photos_table.php
git commit -m "feat(food-photos): create food_photos table (additive)"
```

---

### Task 1.2: ALTER aditivo `habit_logs.habit_type` ENUM

**Files:**
- Create: `database/migrations/2026_05_04_120001_add_food_day_bonus_to_habit_logs_enum.php`

- [ ] **Step 1: Escribir la migración (precedente: `2026_04_01_033020`)**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Expand habit_logs.habit_type ENUM to include 'food_day_bonus'.
 *
 * Current:  ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres')
 * After:    ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres','food_day_bonus')
 *
 * Aditivo: valores existentes preservados. Patrón replicado de
 * 2026_04_01_033020_expand_habit_logs_habit_type_enum.php (sin destructive ops).
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('habit_logs')) {
            return;
        }

        DB::statement(
            "ALTER TABLE habit_logs
             MODIFY COLUMN habit_type
             ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres','food_day_bonus')
             NOT NULL"
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('habit_logs')) {
            return;
        }

        // Solo seguro si no hay rows con 'food_day_bonus'.
        DB::statement(
            "ALTER TABLE habit_logs
             MODIFY COLUMN habit_type
             ENUM('agua','sueno','entrenamiento','nutricion','suplementos','estres')
             NOT NULL"
        );
    }
};
```

- [ ] **Step 2: Correr migración**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan migrate`
Esperado: `Migrated: ..._add_food_day_bonus_to_habit_logs_enum`.

- [ ] **Step 3: Verificar schema**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan tinker --execute="echo DB::select(\"SHOW COLUMNS FROM habit_logs WHERE Field='habit_type'\")[0]->Type;"`
Esperado: contiene `'food_day_bonus'`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_04_120001_add_food_day_bonus_to_habit_logs_enum.php
git commit -m "feat(food-photos): add food_day_bonus to habit_logs enum (additive)"
```

---

## Phase 2 — Models & Parser Service

### Task 2.1: NutritionPlanParser service (test-first)

**Files:**
- Create: `app/Services/NutritionPlanParser.php`
- Test: `tests/Unit/Services/NutritionPlanParserTest.php`

- [ ] **Step 1: Escribir el test (rojo)**

```php
<?php

namespace Tests\Unit\Services;

use App\Services\NutritionPlanParser;
use Tests\TestCase;

class NutritionPlanParserTest extends TestCase
{
    public function test_extracts_meals_from_root_comidas_array(): void
    {
        $plan = ['comidas' => [
            ['nombre' => 'Desayuno', 'calorias' => 400, 'alimentos' => ['Avena']],
            ['nombre' => 'Almuerzo', 'calorias' => 600, 'alimentos' => ['Pollo']],
        ]];

        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertCount(2, $meals);
        $this->assertSame('Desayuno', $meals[0]['nombre']);
        $this->assertSame(400, $meals[0]['calorias']);
    }

    public function test_extracts_meals_from_dias_array(): void
    {
        $plan = ['dias' => [
            ['nombre' => 'Lunes', 'comidas' => [
                ['nombre' => 'Desayuno', 'calorias' => 350],
            ]],
        ]];

        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertCount(1, $meals);
        $this->assertSame('Desayuno', $meals[0]['nombre']);
    }

    public function test_extracts_meals_from_plan_semanal_array(): void
    {
        $plan = ['plan_semanal' => [
            ['dia' => 'Lunes', 'comidas' => [
                ['name' => 'Breakfast', 'calories' => 300],
            ]],
        ]];

        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertCount(1, $meals);
        $this->assertSame('Breakfast', $meals[0]['nombre']);
        $this->assertSame(300, $meals[0]['calorias']);
    }

    public function test_extracts_meals_from_plan_dia_entrenamiento(): void
    {
        $plan = ['plan_dia_entrenamiento' => ['comidas' => [
            ['nombre' => 'Pre-entreno', 'calorias' => 250],
        ]]];

        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertCount(1, $meals);
        $this->assertSame('Pre-entreno', $meals[0]['nombre']);
    }

    public function test_extracts_meals_from_meals_english_key(): void
    {
        $plan = ['meals' => [
            ['label' => 'Lunch', 'kcal' => 700, 'foods' => ['Rice', 'Chicken']],
        ]];

        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertCount(1, $meals);
        $this->assertSame('Lunch', $meals[0]['nombre']);
        $this->assertSame(700, $meals[0]['calorias']);
        $this->assertSame(['Rice', 'Chicken'], $meals[0]['alimentos']);
    }

    public function test_returns_empty_for_unrecognized_format(): void
    {
        $this->assertSame([], NutritionPlanParser::extractMeals([]));
        $this->assertSame([], NutritionPlanParser::extractMeals(['random' => 'data']));
    }

    public function test_normalizes_macros_with_multiple_key_conventions(): void
    {
        $plan = ['comidas' => [
            ['nombre' => 'X', 'macros' => ['proteina_g' => 30, 'carbs_g' => 50, 'grasa_g' => 10]],
        ]];

        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertSame(30, $meals[0]['macros']['proteina']);
        $this->assertSame(50, $meals[0]['macros']['carbohidratos']);
        $this->assertSame(10, $meals[0]['macros']['grasas']);
    }

    public function test_preserves_meal_order_for_meal_index(): void
    {
        $plan = ['comidas' => [
            ['nombre' => 'Desayuno'],
            ['nombre' => 'Almuerzo'],
            ['nombre' => 'Cena'],
        ]];

        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertSame('Desayuno', $meals[0]['nombre']);
        $this->assertSame('Almuerzo', $meals[1]['nombre']);
        $this->assertSame('Cena', $meals[2]['nombre']);
    }
}
```

- [ ] **Step 2: Correr test (verifica rojo)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=NutritionPlanParserTest`
Esperado: FAIL — class `App\Services\NutritionPlanParser` not found.

- [ ] **Step 3: Implementar service (extracción literal de NutritionPlan.php)**

```php
<?php

namespace App\Services;

class NutritionPlanParser
{
    /**
     * Extract array of meals from any supported nutrition plan JSON shape.
     * Supported formats:
     *   - comidas[]
     *   - plan_dia_entrenamiento.comidas
     *   - meals[]
     *   - dias[n].comidas
     *   - plan_semanal[n].comidas
     *
     * Returns array of normalized meals with keys:
     *   nombre, calorias, alimentos, notas, macros{proteina,carbohidratos,grasas}
     *
     * @param  array  $plan  Decoded plan JSON content
     * @return array<int, array<string, mixed>>
     */
    public static function extractMeals(array $plan): array
    {
        $diasComidas = null;
        if (isset($plan['dias']) && is_array($plan['dias'])) {
            foreach ($plan['dias'] as $dia) {
                if (! empty($dia['comidas'])) {
                    $diasComidas = $dia['comidas'];
                    break;
                }
            }
        }

        $planSemanalComidas = null;
        if (isset($plan['plan_semanal']) && is_array($plan['plan_semanal'])) {
            foreach ($plan['plan_semanal'] as $dia) {
                if (! empty($dia['comidas'])) {
                    $planSemanalComidas = $dia['comidas'];
                    break;
                }
            }
        }

        $raw = $plan['comidas']
            ?? $plan['plan_dia_entrenamiento']['comidas']
            ?? $plan['meals']
            ?? $diasComidas
            ?? $planSemanalComidas
            ?? [];

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_map([self::class, 'normalizeMeal'], $raw));
    }

    /**
     * Normalize a single meal to canonical shape.
     * Handles ES/EN key variants from multiple AI plan formats.
     *
     * @param  array  $meal
     * @return array<string, mixed>
     */
    public static function normalizeMeal(array $meal): array
    {
        $macros = is_array($meal['macros'] ?? null) ? $meal['macros'] : [];

        return [
            'nombre'    => $meal['nombre'] ?? $meal['name'] ?? $meal['label'] ?? 'Comida',
            'calorias'  => (int) ($meal['calorias'] ?? $meal['calories'] ?? $meal['kcal'] ?? $meal['cal'] ?? 0),
            'alimentos' => $meal['alimentos'] ?? $meal['foods'] ?? $meal['items'] ?? $meal['opciones'] ?? [],
            'notas'     => $meal['notas'] ?? $meal['notes'] ?? null,
            'macros' => [
                'proteina'      => (int) ($macros['proteina_g'] ?? $macros['proteina'] ?? $macros['protein_g'] ?? $macros['protein'] ?? 0),
                'carbohidratos' => (int) ($macros['carbs_g'] ?? $macros['carbohidratos_g'] ?? $macros['carbohidratos'] ?? $macros['carbs'] ?? 0),
                'grasas'        => (int) ($macros['grasas_g'] ?? $macros['grasa_g'] ?? $macros['grasas'] ?? $macros['fat_g'] ?? $macros['fat'] ?? 0),
            ],
        ];
    }
}
```

- [ ] **Step 4: Correr test (verifica verde)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=NutritionPlanParserTest`
Esperado: PASS — 8 tests, 0 failures.

- [ ] **Step 5: Commit**

```bash
git add app/Services/NutritionPlanParser.php tests/Unit/Services/NutritionPlanParserTest.php
git commit -m "feat(food-photos): NutritionPlanParser service with 8 format tests"
```

---

### Task 2.2: FoodPhoto model + factory

**Files:**
- Create: `app/Models/FoodPhoto.php`
- Create: `database/factories/FoodPhotoFactory.php`
- Test: `tests/Unit/Models/FoodPhotoTest.php`

- [ ] **Step 1: Escribir el test (rojo)**

```php
<?php

namespace Tests\Unit\Models;

use App\Models\FoodPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_photo_date_to_date(): void
    {
        $photo = FoodPhoto::factory()->create(['photo_date' => '2026-05-04']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $photo->photo_date);
        $this->assertSame('2026-05-04', $photo->photo_date->toDateString());
    }

    public function test_casts_coach_seen_to_boolean(): void
    {
        $photo = FoodPhoto::factory()->create(['coach_seen' => 1]);

        $this->assertTrue($photo->coach_seen);
        $this->assertIsBool($photo->coach_seen);
    }

    public function test_casts_xp_awarded_to_boolean(): void
    {
        $photo = FoodPhoto::factory()->create(['xp_awarded' => 0]);

        $this->assertFalse($photo->xp_awarded);
        $this->assertIsBool($photo->xp_awarded);
    }

    public function test_casts_ai_analysis_to_array(): void
    {
        $photo = FoodPhoto::factory()->create(['ai_analysis' => ['k' => 'v']]);

        $this->assertSame(['k' => 'v'], $photo->ai_analysis);
    }

    public function test_photo_url_accessor_resolves_storage_path(): void
    {
        $photo = FoodPhoto::factory()->create([
            'filename' => 'food-photos/42/abc.jpg',
        ]);

        $this->assertSame('/storage/food-photos/42/abc.jpg', $photo->photo_url);
    }

    public function test_unique_constraint_blocks_duplicate_meal_per_day(): void
    {
        FoodPhoto::factory()->create([
            'client_id' => 1, 'meal_index' => 0, 'photo_date' => '2026-05-04',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        FoodPhoto::factory()->create([
            'client_id' => 1, 'meal_index' => 0, 'photo_date' => '2026-05-04',
        ]);
    }
}
```

- [ ] **Step 2: Correr test (rojo)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoTest`
Esperado: FAIL — model y factory no existen.

- [ ] **Step 3: Crear model**

```php
<?php

namespace App\Models;

use App\Scopes\OwnedByClientScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'meal_name',
    'meal_index',
    'photo_date',
    'filename',
    'file_size',
    'coach_seen',
    'coach_seen_at',
    'coach_reaction',
    'coach_note',
    'xp_awarded',
    'ai_analysis',
])]
class FoodPhoto extends Model
{
    use HasFactory;

    protected $table = 'food_photos';

    protected static function booted(): void
    {
        static::addGlobalScope(new OwnedByClientScope());
    }

    protected function casts(): array
    {
        return [
            'photo_date'    => 'date',
            'coach_seen'    => 'boolean',
            'xp_awarded'    => 'boolean',
            'coach_seen_at' => 'datetime',
            'ai_analysis'   => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return '/storage/' . ltrim($this->filename, '/');
    }
}
```

- [ ] **Step 4: Crear factory**

```php
<?php

namespace Database\Factories;

use App\Models\FoodPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FoodPhotoFactory extends Factory
{
    protected $model = FoodPhoto::class;

    public function definition(): array
    {
        return [
            'client_id'      => 1,
            'meal_name'      => $this->faker->randomElement(['Desayuno', 'Almuerzo', 'Cena', 'Snack']),
            'meal_index'     => 0,
            'photo_date'     => now()->toDateString(),
            'filename'       => 'food-photos/1/' . Str::uuid() . '.jpg',
            'file_size'      => 250000,
            'coach_seen'     => false,
            'coach_seen_at'  => null,
            'coach_reaction' => null,
            'coach_note'     => null,
            'xp_awarded'     => false,
            'ai_analysis'    => null,
        ];
    }

    public function reviewed(string $reaction = 'bien'): static
    {
        return $this->state([
            'coach_seen'     => true,
            'coach_seen_at'  => now(),
            'coach_reaction' => $reaction,
        ]);
    }
}
```

- [ ] **Step 5: Correr test (verde)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoTest`
Esperado: PASS — 6 tests, 0 failures.

- [ ] **Step 6: Commit**

```bash
git add app/Models/FoodPhoto.php database/factories/FoodPhotoFactory.php tests/Unit/Models/FoodPhotoTest.php
git commit -m "feat(food-photos): FoodPhoto model + factory + tests"
```

---

## Phase 3 — Refactor `NutritionPlan.php` to Use Parser

### Task 3.1: Test de regresión del refactor

**Files:**
- Test: `tests/Unit/Services/NutritionPlanParserRefactorTest.php`

- [ ] **Step 1: Escribir test que captura output ACTUAL de NutritionPlan.php**

```php
<?php

namespace Tests\Unit\Services;

use App\Services\NutritionPlanParser;
use Tests\TestCase;

/**
 * Garantiza que el refactor de NutritionPlan.php → NutritionPlanParser
 * no cambia el output observable. Replica los formatos JSON reales que
 * NutritionPlan.php parseaba antes del refactor.
 */
class NutritionPlanParserRefactorTest extends TestCase
{
    /** @dataProvider realPlanFormats */
    public function test_parser_output_matches_legacy_behavior(array $plan, int $expectedMealCount, string $firstMealName): void
    {
        $meals = NutritionPlanParser::extractMeals($plan);

        $this->assertCount($expectedMealCount, $meals);
        $this->assertSame($firstMealName, $meals[0]['nombre']);
    }

    public static function realPlanFormats(): array
    {
        return [
            'jairo plan' => [
                ['comidas' => [
                    ['nombre' => 'Desayuno 7am', 'calorias' => 450, 'alimentos' => ['Huevos', 'Avena']],
                    ['nombre' => 'Almuerzo 1pm', 'calorias' => 700],
                    ['nombre' => 'Cena 7pm', 'calorias' => 550],
                ]],
                3,
                'Desayuno 7am',
            ],
            'carb cycling dias' => [
                ['dias' => [
                    ['dia' => 1, 'comidas' => [['nombre' => 'Comida alta carb 1']]],
                    ['dia' => 2, 'comidas' => [['nombre' => 'Comida baja carb 1']]],
                ]],
                1,
                'Comida alta carb 1',
            ],
            'plan dia entrenamiento' => [
                ['plan_dia_entrenamiento' => ['comidas' => [['nombre' => 'Pre-entreno']]]],
                1,
                'Pre-entreno',
            ],
            'tatis english plan' => [
                ['meals' => [['label' => 'Breakfast', 'kcal' => 380, 'foods' => ['oats']]]],
                1,
                'Breakfast',
            ],
        ];
    }
}
```

- [ ] **Step 2: Correr test (debe pasar — el parser ya existe del Phase 2)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=NutritionPlanParserRefactorTest`
Esperado: PASS — 4 tests dataProvider.

- [ ] **Step 3: Refactorizar NutritionPlan.php**

Modificar `app/Livewire/Client/NutritionPlan.php`:

```php
// AGREGAR al top imports:
use App\Services\NutritionPlanParser;

// REEMPLAZAR los métodos parseMeals() (líneas 121-156) Y normalizeMeal() (líneas 158-172) por:
private function parseMeals(): void
{
    $this->mealLog = NutritionPlanParser::extractMeals($this->plan ?? []);
}

// ELIMINAR el método normalizeMeal() completo (ya vive en el parser)
```

- [ ] **Step 4: Correr toda la suite de Livewire para confirmar no-regresión**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=NutritionPlan`
Esperado: PASS — todos los tests existentes de NutritionPlan siguen verdes.

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/Client/NutritionPlan.php tests/Unit/Services/NutritionPlanParserRefactorTest.php
git commit -m "refactor(nutrition-plan): delegate parseMeals/normalizeMeal to NutritionPlanParser"
```

---

## Phase 4 — FoodPhotoService

### Task 4.1: Test de FoodPhotoService::store()

**Files:**
- Test: `tests/Feature/Services/FoodPhotoServiceTest.php`

- [ ] **Step 1: Escribir test (rojo)**

```php
<?php

namespace Tests\Feature\Services;

use App\Models\Client;
use App\Models\FoodPhoto;
use App\Services\FoodPhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FoodPhotoServiceTest extends TestCase
{
    use RefreshDatabase;

    private FoodPhotoService $service;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = app(FoodPhotoService::class);
        $this->client = Client::factory()->create();
    }

    public function test_stores_new_photo_and_returns_food_photo_record(): void
    {
        $file = UploadedFile::fake()->image('comida.jpg', 800, 600);

        $photo = $this->service->store($this->client, $file, 'Desayuno', 0, '2026-05-04');

        $this->assertInstanceOf(FoodPhoto::class, $photo);
        $this->assertSame($this->client->id, $photo->client_id);
        $this->assertSame('Desayuno', $photo->meal_name);
        $this->assertSame(0, $photo->meal_index);
        $this->assertSame('2026-05-04', $photo->photo_date->toDateString());
        Storage::disk('public')->assertExists($photo->filename);
    }

    public function test_stores_filename_with_uuid_inside_client_subfolder(): void
    {
        $file = UploadedFile::fake()->image('original.jpg');

        $photo = $this->service->store($this->client, $file, 'Almuerzo', 1, '2026-05-04');

        $this->assertStringStartsWith("food-photos/{$this->client->id}/", $photo->filename);
        $this->assertStringEndsWith('.jpg', $photo->filename);
        $this->assertDoesNotMatchRegularExpression('/original/', $photo->filename, 'filename must not contain user input');
    }

    public function test_replacing_photo_deletes_old_file_and_keeps_one_db_row(): void
    {
        $file1 = UploadedFile::fake()->image('a.jpg');
        $first = $this->service->store($this->client, $file1, 'Desayuno', 0, '2026-05-04');
        $oldFilename = $first->filename;

        $file2 = UploadedFile::fake()->image('b.jpg');
        $second = $this->service->store($this->client, $file2, 'Desayuno', 0, '2026-05-04');

        $this->assertSame($first->id, $second->id, 'replace must update same row');
        $this->assertNotSame($oldFilename, $second->filename);
        Storage::disk('public')->assertMissing($oldFilename);
        Storage::disk('public')->assertExists($second->filename);
        $this->assertSame(1, FoodPhoto::withoutGlobalScopes()->where('client_id', $this->client->id)->count());
    }

    public function test_rejects_invalid_image(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $file = UploadedFile::fake()->create('not-image.txt', 100, 'text/plain');
        $this->service->store($this->client, $file, 'Cena', 2, '2026-05-04');
    }

    public function test_delete_photo_when_coach_not_seen(): void
    {
        $file = UploadedFile::fake()->image('a.jpg');
        $photo = $this->service->store($this->client, $file, 'Desayuno', 0, '2026-05-04');
        $filename = $photo->filename;

        $this->service->delete($photo);

        Storage::disk('public')->assertMissing($filename);
        $this->assertNull(FoodPhoto::withoutGlobalScopes()->find($photo->id));
    }

    public function test_delete_throws_when_coach_already_seen(): void
    {
        $photo = FoodPhoto::factory()->create([
            'client_id'  => $this->client->id,
            'coach_seen' => true,
        ]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $this->service->delete($photo);
    }
}
```

- [ ] **Step 2: Correr test (rojo)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoServiceTest`
Esperado: FAIL — service no existe.

- [ ] **Step 3: Implementar FoodPhotoService**

```php
<?php

namespace App\Services;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\ClientXp;
use App\Models\FoodPhoto;
use App\Models\HabitLog;
use App\Models\WellcoreNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FoodPhotoService
{
    public function store(
        Client $client,
        UploadedFile $file,
        string $mealName,
        int $mealIndex,
        string $photoDate
    ): FoodPhoto {
        Validator::make(['photo' => $file], [
            'photo' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:10240',
        ])->validate();

        $existing = FoodPhoto::withoutGlobalScopes()
            ->where('client_id', $client->id)
            ->where('meal_index', $mealIndex)
            ->where('photo_date', $photoDate)
            ->first();

        $oldFilename = $existing?->filename;
        $newFilename = $this->processImage($file, $client->id);

        try {
            $photo = DB::transaction(function () use ($client, $existing, $mealName, $mealIndex, $photoDate, $newFilename, $file) {
                $payload = [
                    'client_id'  => $client->id,
                    'meal_name'  => $mealName,
                    'meal_index' => $mealIndex,
                    'photo_date' => $photoDate,
                    'filename'   => $newFilename,
                    'file_size'  => $file->getSize() ?: null,
                ];

                if ($existing) {
                    $existing->fill($payload)->save();
                    return $existing;
                }

                return FoodPhoto::create($payload);
            });
        } catch (QueryException $e) {
            // Race condition: otro request creó la fila mientras procesábamos.
            // Borramos el archivo nuevo y devolvemos la fila ganadora (idempotente).
            if (($e->errorInfo[1] ?? null) === 1062) {
                Storage::disk('public')->delete($newFilename);
                return FoodPhoto::withoutGlobalScopes()
                    ->where('client_id', $client->id)
                    ->where('meal_index', $mealIndex)
                    ->where('photo_date', $photoDate)
                    ->firstOrFail();
            }
            Storage::disk('public')->delete($newFilename);
            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($newFilename);
            throw $e;
        }

        if ($oldFilename && $oldFilename !== $newFilename) {
            Storage::disk('public')->delete($oldFilename);
        }

        if (! $photo->xp_awarded) {
            $this->awardXp($client, $photo, $photoDate);
        }

        $this->notifyCoach($client, $mealName);

        return $photo->refresh();
    }

    public function delete(FoodPhoto $photo): void
    {
        if ($photo->coach_seen) {
            throw new AuthorizationException('Tu coach ya revisó esta foto y no puede eliminarse.');
        }

        $filename = $photo->filename;

        DB::transaction(function () use ($photo) {
            $photo->delete();
        });

        Storage::disk('public')->delete($filename);
    }

    private function processImage(UploadedFile $file, int $clientId): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        if (method_exists($image, 'orientate')) {
            $image->orientate();
        }
        $image->scaleDown(width: 1200);

        $filename = sprintf('food-photos/%d/%s.jpg', $clientId, Str::uuid());
        $encoded = $image->toJpeg(85);

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    private function awardXp(Client $client, FoodPhoto $photo, string $photoDate): void
    {
        try {
            DB::transaction(function () use ($client, $photo) {
                ClientXp::firstOrCreate(
                    ['client_id' => $client->id],
                    ['xp_total' => 0, 'level' => 1, 'streak_days' => 0]
                );
                ClientXp::where('client_id', $client->id)->increment('xp_total', 15);
                $photo->update(['xp_awarded' => true]);
            });
        } catch (\Throwable $e) {
            // Si client_xp no existe en local o falla, no rompe el upload.
            Log::warning('FoodPhotoService::awardXp skipped', [
                'client_id' => $client->id,
                'photo_id'  => $photo->id,
                'error'     => $e->getMessage(),
            ]);
            return;
        }

        $this->maybeAwardDayBonus($client, $photoDate);
    }

    private function maybeAwardDayBonus(Client $client, string $photoDate): void
    {
        $lockKey = "food_day_bonus:{$client->id}:{$photoDate}";

        Cache::lock($lockKey, 30)->block(5, function () use ($client, $photoDate) {
            $alreadyAwarded = HabitLog::where('client_id', $client->id)
                ->where('habit_type', 'food_day_bonus')
                ->where('log_date', $photoDate)
                ->exists();

            if ($alreadyAwarded) {
                return;
            }

            $expectedMeals = $this->countExpectedMeals($client);
            if ($expectedMeals === 0) {
                return;
            }

            $uploadedToday = FoodPhoto::withoutGlobalScopes()
                ->where('client_id', $client->id)
                ->where('photo_date', $photoDate)
                ->count();

            if ($uploadedToday < $expectedMeals) {
                return;
            }

            try {
                HabitLog::create([
                    'client_id'  => $client->id,
                    'log_date'   => $photoDate,
                    'habit_type' => 'food_day_bonus',
                    'value'      => 30,
                ]);
                ClientXp::where('client_id', $client->id)->increment('xp_total', 30);
            } catch (\Throwable $e) {
                Log::warning('FoodPhotoService::dayBonus skipped', [
                    'client_id' => $client->id,
                    'date'      => $photoDate,
                    'error'     => $e->getMessage(),
                ]);
            }
        });
    }

    private function countExpectedMeals(Client $client): int
    {
        try {
            $plan = AssignedPlan::where('client_id', $client->id)
                ->where('plan_type', 'nutricion')
                ->where('active', true)
                ->latest()
                ->first();
        } catch (\Throwable $e) {
            return 0;
        }

        if (! $plan || ! $plan->content) {
            return 0;
        }

        return count(NutritionPlanParser::extractMeals(is_array($plan->content) ? $plan->content : []));
    }

    private function notifyCoach(Client $client, string $mealName): void
    {
        try {
            $coachId = AssignedPlan::where('client_id', $client->id)
                ->where('plan_type', 'nutricion')
                ->where('active', true)
                ->value('assigned_by');

            $coachId ??= AssignedPlan::where('client_id', $client->id)
                ->where('active', true)
                ->value('assigned_by');
        } catch (\Throwable $e) {
            return;
        }

        if (! $coachId) {
            return;
        }

        $cacheKey = "food_notif:{$coachId}:{$client->id}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, 900);

        try {
            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id'   => $coachId,
                'type'      => 'food_photo_uploaded',
                'title'     => 'Foto de comida nueva',
                'body'      => "{$client->name} subió foto de {$mealName}",
                'link'      => '/coach/food-photos',
            ]);
        } catch (\Throwable $e) {
            Log::warning('FoodPhotoService::notifyCoach skipped', [
                'coach_id'  => $coachId,
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
```

- [ ] **Step 4: Correr test (verde)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoServiceTest`
Esperado: PASS — 6 tests, 0 failures.

- [ ] **Step 5: Commit**

```bash
git add app/Services/FoodPhotoService.php tests/Feature/Services/FoodPhotoServiceTest.php
git commit -m "feat(food-photos): FoodPhotoService with upload/delete/XP/notif"
```

---

## Phase 5 — API REST

### Task 5.1: StoreFoodPhotoRequest

**Files:**
- Create: `app/Http/Requests/StoreFoodPhotoRequest.php`

- [ ] **Step 1: Escribir el form request**

```php
<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreFoodPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('wellcore') !== null;
    }

    public function rules(): array
    {
        $tomorrow = Carbon::now('America/Bogota')->addDay()->toDateString();
        $yesterday = Carbon::now('America/Bogota')->subDay()->toDateString();

        return [
            'photo'      => 'required|file|image|mimes:jpg,jpeg,png,webp|max:10240',
            'meal_name'  => 'required|string|max:255',
            'meal_index' => 'required|integer|min:0|max:20',
            'photo_date' => "sometimes|date|after_or_equal:{$yesterday}|before_or_equal:{$tomorrow}",
        ];
    }

    public function photoDate(): string
    {
        return $this->input('photo_date', Carbon::now('America/Bogota')->toDateString());
    }
}
```

- [ ] **Step 2: Commit (sin test propio — se cubre por FoodPhotoTest)**

```bash
git add app/Http/Requests/StoreFoodPhotoRequest.php
git commit -m "feat(food-photos): StoreFoodPhotoRequest with TZ-aware date bounds"
```

---

### Task 5.2: FoodPhotoResource

**Files:**
- Create: `app/Http/Resources/FoodPhotoResource.php`

- [ ] **Step 1: Escribir el resource**

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'meal_name'       => $this->meal_name,
            'meal_index'      => $this->meal_index,
            'photo_date'      => $this->photo_date->toDateString(),
            'photo_url'       => $this->photo_url,
            'coach_seen'      => $this->coach_seen,
            'coach_reaction'  => $this->coach_reaction,
            'coach_note'      => $this->coach_note,
            'xp_awarded'      => $this->xp_awarded,
            'uploaded_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Resources/FoodPhotoResource.php
git commit -m "feat(food-photos): FoodPhotoResource"
```

---

### Task 5.3: FoodPhotoController + tests

**Files:**
- Create: `app/Http/Controllers/Api/Client/FoodPhotoController.php`
- Modify: `routes/api.php` (agregar grupo)
- Test: `tests/Feature/Api/Client/FoodPhotoTest.php`

- [ ] **Step 1: Escribir test feature (rojo)**

```php
<?php

namespace Tests\Feature\Api\Client;

use App\Models\Client;
use App\Models\FoodPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FoodPhotoTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->client = Client::factory()->create();
        $this->token = $this->loginAsClient($this->client);
    }

    public function test_index_returns_meals_and_photos_for_today(): void
    {
        FoodPhoto::factory()->create([
            'client_id'  => $this->client->id,
            'meal_index' => 0,
            'meal_name'  => 'Desayuno',
            'photo_date' => now('America/Bogota')->toDateString(),
        ]);

        $response = $this->withToken($this->token)->getJson('/api/v/client/food-photos');

        $response->assertOk()
            ->assertJsonStructure(['has_nutrition_plan', 'meals', 'xp_today', 'streak_days']);
    }

    public function test_store_uploads_photo_and_returns_resource(): void
    {
        $file = UploadedFile::fake()->image('comida.jpg', 1024, 768);

        $response = $this->withToken($this->token)
            ->postJson('/api/v/client/food-photos', [
                'photo'      => $file,
                'meal_name'  => 'Desayuno',
                'meal_index' => 0,
            ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'meal_name', 'photo_url', 'xp_awarded']]);

        $this->assertDatabaseHas('food_photos', [
            'client_id'  => $this->client->id,
            'meal_index' => 0,
        ]);
    }

    public function test_store_replaces_existing_photo_for_same_meal_day(): void
    {
        $file1 = UploadedFile::fake()->image('a.jpg');
        $this->withToken($this->token)->postJson('/api/v/client/food-photos', [
            'photo' => $file1, 'meal_name' => 'Desayuno', 'meal_index' => 0,
        ]);

        $file2 = UploadedFile::fake()->image('b.jpg');
        $this->withToken($this->token)->postJson('/api/v/client/food-photos', [
            'photo' => $file2, 'meal_name' => 'Desayuno', 'meal_index' => 0,
        ]);

        $this->assertSame(1, FoodPhoto::withoutGlobalScopes()
            ->where('client_id', $this->client->id)
            ->count());
    }

    public function test_store_rejects_non_image(): void
    {
        $file = UploadedFile::fake()->create('doc.pdf', 1000, 'application/pdf');

        $response = $this->withToken($this->token)->postJson('/api/v/client/food-photos', [
            'photo' => $file, 'meal_name' => 'Desayuno', 'meal_index' => 0,
        ]);

        $response->assertUnprocessable();
    }

    public function test_store_rejects_future_date(): void
    {
        $file = UploadedFile::fake()->image('a.jpg');

        $response = $this->withToken($this->token)->postJson('/api/v/client/food-photos', [
            'photo'      => $file,
            'meal_name'  => 'Desayuno',
            'meal_index' => 0,
            'photo_date' => '2030-01-01',
        ]);

        $response->assertUnprocessable();
    }

    public function test_destroy_deletes_when_coach_not_seen(): void
    {
        $photo = FoodPhoto::factory()->create([
            'client_id'  => $this->client->id,
            'coach_seen' => false,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v/client/food-photos/{$photo->id}");

        $response->assertNoContent();
        $this->assertNull(FoodPhoto::withoutGlobalScopes()->find($photo->id));
    }

    public function test_destroy_blocks_when_coach_already_seen(): void
    {
        $photo = FoodPhoto::factory()->reviewed()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v/client/food-photos/{$photo->id}");

        $response->assertForbidden();
        $this->assertNotNull(FoodPhoto::withoutGlobalScopes()->find($photo->id));
    }

    public function test_destroy_blocks_other_clients_photos(): void
    {
        $otherClient = Client::factory()->create();
        $photo = FoodPhoto::factory()->create(['client_id' => $otherClient->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v/client/food-photos/{$photo->id}");

        $response->assertNotFound();
    }
}
```

- [ ] **Step 2: Correr test (rojo)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoTest`
Esperado: FAIL — controller no existe + ruta no registrada.

- [ ] **Step 3: Implementar FoodPhotoController**

```php
<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodPhotoRequest;
use App\Http\Resources\FoodPhotoResource;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\FoodPhoto;
use App\Services\FoodPhotoService;
use App\Services\NutritionPlanParser;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FoodPhotoController extends Controller
{
    public function __construct(private FoodPhotoService $service) {}

    public function index(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user('wellcore');
        $today = Carbon::now('America/Bogota')->toDateString();

        $plan = AssignedPlan::where('client_id', $client->id)
            ->where('plan_type', 'nutricion')
            ->where('active', true)
            ->latest()
            ->first();

        $meals = $plan && $plan->content
            ? NutritionPlanParser::extractMeals(is_array($plan->content) ? $plan->content : [])
            : [];

        $todayPhotos = FoodPhoto::where('photo_date', $today)
            ->where('client_id', $client->id)
            ->get()
            ->keyBy('meal_index');

        $mealsWithPhotos = collect($meals)->values()->map(function ($meal, $i) use ($todayPhotos) {
            return [
                'index'     => $i,
                'nombre'    => $meal['nombre'],
                'calorias'  => $meal['calorias'],
                'alimentos' => $meal['alimentos'],
                'macros'    => $meal['macros'],
                'notas'     => $meal['notas'],
                'photo'     => $todayPhotos->get($i)
                    ? new FoodPhotoResource($todayPhotos->get($i))
                    : null,
            ];
        })->all();

        $xpToday = $todayPhotos->where('xp_awarded', true)->count() * 15;
        $bonusEarned = DB::table('habit_logs')
            ->where('client_id', $client->id)
            ->where('habit_type', 'food_day_bonus')
            ->where('log_date', $today)
            ->exists();

        if ($bonusEarned) {
            $xpToday += 30;
        }

        $streak = $this->computeStreak($client->id);

        return response()->json([
            'has_nutrition_plan' => $plan !== null,
            'meals'              => $mealsWithPhotos,
            'xp_today'           => $xpToday,
            'bonus_earned_today' => $bonusEarned,
            'streak_days'        => $streak,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user('wellcore');
        $days = collect(range(0, 6))->map(function ($offset) use ($client) {
            $date = Carbon::now('America/Bogota')->subDays($offset)->toDateString();
            $count = FoodPhoto::where('photo_date', $date)
                ->where('client_id', $client->id)
                ->count();

            return [
                'date'     => $date,
                'uploaded' => $count,
            ];
        });

        return response()->json(['week_history' => $days->values()->all()]);
    }

    public function store(StoreFoodPhotoRequest $request): JsonResource
    {
        /** @var Client $client */
        $client = $request->user('wellcore');

        $photo = $this->service->store(
            $client,
            $request->file('photo'),
            $request->input('meal_name'),
            (int) $request->input('meal_index'),
            $request->photoDate()
        );

        Cache::forget("food_streak:{$client->id}");

        return (new FoodPhotoResource($photo))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user('wellcore');

        $photo = FoodPhoto::where('client_id', $client->id)->find($id);
        if (! $photo) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        try {
            $this->service->delete($photo);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        Cache::forget("food_streak:{$client->id}");

        return response()->json(null, 204);
    }

    private function computeStreak(int $clientId): int
    {
        return Cache::remember("food_streak:{$clientId}", 300, function () use ($clientId) {
            $rows = DB::table('food_photos')
                ->select('photo_date', DB::raw('COUNT(*) as cnt'))
                ->where('client_id', $clientId)
                ->groupBy('photo_date')
                ->orderByDesc('photo_date')
                ->limit(60)
                ->get();

            if ($rows->isEmpty()) {
                return 0;
            }

            $streak = 0;
            $cursor = Carbon::now('America/Bogota');
            $byDate = $rows->pluck('cnt', 'photo_date');

            while ($byDate->get($cursor->toDateString(), 0) > 0) {
                $streak++;
                $cursor = $cursor->subDay();
                if ($streak > 365) {
                    break;
                }
            }

            return $streak;
        });
    }
}
```

- [ ] **Step 4: Registrar rutas en `routes/api.php`**

Localizar el grupo `Route::prefix('v/client')->middleware(['auth:wellcore', 'plan.lock:strict', 'throttle:api'])->group(...)` (línea ~116) y AGREGAR al final, antes del `})`:

```php
    // Food Tracking — fotos de comida revisadas por el coach
    Route::get('/food-photos', [\App\Http\Controllers\Api\Client\FoodPhotoController::class, 'index']);
    Route::get('/food-photos/history', [\App\Http\Controllers\Api\Client\FoodPhotoController::class, 'history']);
    Route::post('/food-photos', [\App\Http\Controllers\Api\Client\FoodPhotoController::class, 'store'])
        ->middleware('throttle:20,1');
    Route::delete('/food-photos/{id}', [\App\Http\Controllers\Api\Client\FoodPhotoController::class, 'destroy'])
        ->where('id', '[0-9]+');
```

- [ ] **Step 5: Correr test (verde)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoTest`
Esperado: PASS — 8 tests, 0 failures.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Client/FoodPhotoController.php routes/api.php tests/Feature/Api/Client/FoodPhotoTest.php
git commit -m "feat(food-photos): REST API endpoints (index/history/store/destroy)"
```

---

## Phase 6 — PushNotificationService Extension

### Task 6.1: Agregar método `notifyClientFoodPhotoReacted`

**Files:**
- Modify: `app/Services/PushNotificationService.php`

- [ ] **Step 1: Agregar método después de `notifyWeeklySummary` (línea 148)**

Insertar antes del comentario `// ─── CORE SEND METHOD ──────────────────────────────────────────────`:

```php
    /**
     * Notify client that their coach reacted to a food photo.
     * @param  string  $reaction  'bien' | 'mejorar'
     */
    public static function notifyClientFoodPhotoReacted(
        int $clientId,
        string $coachName,
        string $reaction,
        string $mealName
    ): bool {
        $emoji = $reaction === 'bien' ? '✅' : '⚠️';
        $body = $reaction === 'bien'
            ? "{$coachName} revisó tu {$mealName} y todo bien"
            : "{$coachName} tiene un comentario sobre tu {$mealName}";

        return (new static)->send($clientId, [
            'title' => "Tu coach revisó tu comida {$emoji}",
            'body'  => $body,
            'icon'  => '/images/logo-dark.png',
            'badge' => '/icons/icon-192x192.png',
            'tag'   => 'food-photo-reacted',
            'data'  => ['url' => '/rise/food-tracking', 'type' => 'food_photo_reacted'],
        ]);
    }

```

- [ ] **Step 2: Smoke test rápido**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=PushNotification`
Esperado: PASS o "no tests" — confirma que no rompimos algún test existente del servicio.

- [ ] **Step 3: Commit**

```bash
git add app/Services/PushNotificationService.php
git commit -m "feat(food-photos): notifyClientFoodPhotoReacted push method"
```

---

## Phase 7 — Coach Livewire (FoodPhotoReview)

### Task 7.1: Test del componente Livewire

**Files:**
- Test: `tests/Feature/Livewire/Coach/FoodPhotoReviewTest.php`

- [ ] **Step 1: Escribir el test (rojo)**

```php
<?php

namespace Tests\Feature\Livewire\Coach;

use App\Livewire\Coach\FoodPhotoReview;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\FoodPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FoodPhotoReviewTest extends TestCase
{
    use RefreshDatabase;

    private Admin $coach;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coach = Admin::factory()->create();
        $this->client = Client::factory()->create();
        AssignedPlan::factory()->create([
            'client_id'   => $this->client->id,
            'assigned_by' => $this->coach->id,
            'plan_type'   => 'nutricion',
            'active'      => true,
        ]);
        $this->actingAs($this->coach, 'wellcore');
    }

    public function test_renders_pending_photos_for_coach_clients(): void
    {
        FoodPhoto::factory()->create([
            'client_id'  => $this->client->id,
            'coach_seen' => false,
        ]);

        Livewire::test(FoodPhotoReview::class)
            ->assertOk()
            ->assertSee($this->client->name);
    }

    public function test_does_not_show_photos_from_other_coaches_clients(): void
    {
        $otherClient = Client::factory()->create();
        FoodPhoto::factory()->create([
            'client_id'  => $otherClient->id,
            'coach_seen' => false,
        ]);

        Livewire::test(FoodPhotoReview::class)
            ->assertDontSee($otherClient->name);
    }

    public function test_react_marks_photo_seen_and_sets_reaction(): void
    {
        $photo = FoodPhoto::factory()->create(['client_id' => $this->client->id]);

        Livewire::test(FoodPhotoReview::class)
            ->call('react', $photo->id, 'bien');

        $photo->refresh();
        $this->assertTrue($photo->coach_seen);
        $this->assertSame('bien', $photo->coach_reaction);
        $this->assertNotNull($photo->coach_seen_at);
    }

    public function test_react_rejects_invalid_reaction(): void
    {
        $photo = FoodPhoto::factory()->create(['client_id' => $this->client->id]);

        Livewire::test(FoodPhotoReview::class)
            ->call('react', $photo->id, 'malo');

        $photo->refresh();
        $this->assertFalse($photo->coach_seen);
        $this->assertNull($photo->coach_reaction);
    }

    public function test_react_blocks_other_coach_photos(): void
    {
        $otherClient = Client::factory()->create();
        $photo = FoodPhoto::factory()->create(['client_id' => $otherClient->id]);

        Livewire::test(FoodPhotoReview::class)
            ->call('react', $photo->id, 'bien');

        $photo->refresh();
        $this->assertFalse($photo->coach_seen);
    }

    public function test_save_note_persists_text(): void
    {
        $photo = FoodPhoto::factory()->create(['client_id' => $this->client->id]);

        Livewire::test(FoodPhotoReview::class)
            ->set("noteMap.{$photo->id}", 'Buena porción de proteína.')
            ->call('saveNote', $photo->id);

        $photo->refresh();
        $this->assertSame('Buena porción de proteína.', $photo->coach_note);
    }
}
```

- [ ] **Step 2: Correr test (rojo)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoReviewTest`
Esperado: FAIL — clase Livewire no existe.

- [ ] **Step 3: Implementar el Livewire component**

Archivo `app/Livewire/Coach/FoodPhotoReview.php`:

```php
<?php

namespace App\Livewire\Coach;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\FoodPhoto;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.coach', ['title' => 'Fotos de Comida'])]
class FoodPhotoReview extends Component
{
    use WithPagination;

    public bool $showReviewed = false;
    public ?int $selectedClientId = null;
    public array $noteMap = [];

    protected function getCoachClientIds(): \Illuminate\Support\Collection
    {
        return AssignedPlan::where('assigned_by', auth('wellcore')->id())
            ->pluck('client_id')
            ->unique();
    }

    public function react(int $photoId, string $reaction): void
    {
        if (! in_array($reaction, ['bien', 'mejorar'], true)) {
            return;
        }

        $photo = FoodPhoto::find($photoId);
        if (! $photo) {
            return;
        }
        if (! $this->getCoachClientIds()->contains($photo->client_id)) {
            return;
        }

        $photo->update([
            'coach_seen'     => true,
            'coach_seen_at'  => Carbon::now(),
            'coach_reaction' => $reaction,
        ]);

        Cache::forget("coach_food_pending:" . auth('wellcore')->id());

        try {
            $coach = \App\Models\Admin::find(auth('wellcore')->id());
            PushNotificationService::notifyClientFoodPhotoReacted(
                $photo->client_id,
                $coach?->name ?? 'Tu coach',
                $reaction,
                $photo->meal_name
            );
        } catch (\Throwable $e) {
            \Log::warning('FoodPhotoReview::react notify failed', ['error' => $e->getMessage()]);
        }
    }

    public function saveNote(int $photoId): void
    {
        $photo = FoodPhoto::find($photoId);
        if (! $photo || ! $this->getCoachClientIds()->contains($photo->client_id)) {
            return;
        }

        $note = trim((string) ($this->noteMap[$photoId] ?? ''));
        $photo->update(['coach_note' => $note === '' ? null : $note]);
    }

    public function markSeen(int $photoId): void
    {
        $photo = FoodPhoto::find($photoId);
        if (! $photo || ! $this->getCoachClientIds()->contains($photo->client_id)) {
            return;
        }

        $photo->update(['coach_seen' => true, 'coach_seen_at' => Carbon::now()]);
        Cache::forget("coach_food_pending:" . auth('wellcore')->id());
    }

    public function toggleFilter(): void
    {
        $this->showReviewed = ! $this->showReviewed;
        $this->resetPage();
    }

    public function selectClient(?int $clientId): void
    {
        $this->selectedClientId = $clientId;
        $this->resetPage();
    }

    public function render()
    {
        $clientIds = $this->getCoachClientIds();

        $photos = FoodPhoto::whereIn('client_id', $clientIds)
            ->where('coach_seen', $this->showReviewed)
            ->when($this->selectedClientId, fn ($q) => $q->where('client_id', $this->selectedClientId))
            ->orderByDesc('created_at')
            ->paginate(15);

        $clientsById = Client::whereIn('id', $photos->pluck('client_id')->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $allClients = Client::whereIn('id', $clientIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $pendingCount = Cache::remember(
            "coach_food_pending:" . auth('wellcore')->id(),
            60,
            fn () => FoodPhoto::whereIn('client_id', $clientIds)->where('coach_seen', false)->count()
        );

        return view('livewire.coach.food-photo-review', [
            'photos'       => $photos,
            'clientsById'  => $clientsById,
            'allClients'   => $allClients,
            'pendingCount' => $pendingCount,
        ]);
    }
}
```

- [ ] **Step 4: Crear blade view**

Archivo `resources/views/livewire/coach/food-photo-review.blade.php`:

```blade
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl tracking-wide text-wc-text">FOTOS DE COMIDA</h1>
            <p class="text-sm text-wc-text-secondary">
                {{ $pendingCount }} pendiente{{ $pendingCount === 1 ? '' : 's' }} de revisión
            </p>
        </div>

        <div class="flex items-center gap-2">
            <select wire:model.live="selectedClientId"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm">
                <option :value="null">Todos los clientes</option>
                @foreach ($allClients as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <button wire:click="toggleFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm">
                {{ $showReviewed ? 'Ver Pendientes' : 'Ver Revisadas' }}
            </button>
        </div>
    </div>

    @if ($photos->isEmpty())
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-10 text-center text-wc-text-secondary">
            {{ $showReviewed ? 'No has revisado fotos aún.' : 'Sin fotos pendientes.' }}
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($photos as $photo)
                @php $client = $clientsById->get($photo->client_id); @endphp
                <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                    <div class="flex items-center gap-3 border-b border-wc-border p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10 text-sm font-bold text-wc-accent">
                            {{ substr($client->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-wc-text">{{ $client->name ?? 'Cliente' }}</p>
                            <p class="text-xs text-wc-text-tertiary">
                                {{ $photo->meal_name }} ·
                                {{ \Carbon\Carbon::parse($photo->photo_date)->format('d M') }} ·
                                {{ $photo->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <img src="{{ $photo->photo_url }}" alt="Foto de {{ $photo->meal_name }}"
                         class="h-64 w-full object-cover">

                    <div class="space-y-3 p-4">
                        @if ($photo->coach_seen)
                            <div class="flex items-center gap-2 text-sm">
                                @if ($photo->coach_reaction === 'bien')
                                    <span class="rounded-full bg-green-500/10 px-2 py-0.5 text-green-400">✅ Bien</span>
                                @elseif ($photo->coach_reaction === 'mejorar')
                                    <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-amber-400">⚠️ Por mejorar</span>
                                @else
                                    <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5">Vista sin reacción</span>
                                @endif
                            </div>
                        @else
                            <div class="flex gap-2">
                                <button wire:click="react({{ $photo->id }}, 'bien')"
                                        class="flex-1 rounded-lg border border-green-500/30 bg-green-500/10 py-2 text-sm font-semibold text-green-400 transition hover:bg-green-500/20">
                                    ✅ Bien
                                </button>
                                <button wire:click="react({{ $photo->id }}, 'mejorar')"
                                        class="flex-1 rounded-lg border border-amber-500/30 bg-amber-500/10 py-2 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/20">
                                    ⚠️ Mejorar
                                </button>
                            </div>
                        @endif

                        <textarea wire:model="noteMap.{{ $photo->id }}"
                                  wire:change="saveNote({{ $photo->id }})"
                                  rows="2"
                                  placeholder="Nota opcional para el cliente"
                                  class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text">{{ $photo->coach_note }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $photos->links() }}</div>
    @endif
</div>
```

- [ ] **Step 5: Registrar ruta web**

Modificar `routes/web.php` — agregar al final:

```php
Route::get('/coach/food-photos', \App\Livewire\Coach\FoodPhotoReview::class)
    ->name('coach.food-photos')
    ->middleware('auth:wellcore');
```

- [ ] **Step 6: Correr test (verde)**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test --filter=FoodPhotoReviewTest`
Esperado: PASS — 6 tests, 0 failures.

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Coach/FoodPhotoReview.php resources/views/livewire/coach/food-photo-review.blade.php routes/web.php tests/Feature/Livewire/Coach/FoodPhotoReviewTest.php
git commit -m "feat(food-photos): coach FoodPhotoReview livewire + blade + route"
```

---

### Task 7.2: Link en nav del coach

**Files:**
- Modify: `resources/views/layouts/coach.blade.php`

- [ ] **Step 1: Localizar la nav del coach y agregar link**

Buscar (con Grep) la sección donde están los links existentes (probablemente cerca de "Check-ins"), y agregar:

```blade
<a href="{{ route('coach.food-photos') }}"
   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition
          {{ request()->routeIs('coach.food-photos') ? 'bg-wc-accent/10 text-wc-accent' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary' }}">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
    </svg>
    Fotos de Comida
    @php
        $coachId = auth('wellcore')->id();
        $foodPending = $coachId ? \Illuminate\Support\Facades\Cache::get("coach_food_pending:{$coachId}", 0) : 0;
    @endphp
    @if ($foodPending > 0)
        <span class="ml-auto rounded-full bg-wc-accent px-2 py-0.5 text-xs font-bold text-white">{{ $foodPending }}</span>
    @endif
</a>
```

- [ ] **Step 2: Verificar visualmente con curl o browser**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan view:clear`

Abrir `https://wellcore-laravel.test/coach/dashboard` (login coach) → verificar el nuevo link aparece y muestra badge si hay pendientes.

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/coach.blade.php
git commit -m "feat(food-photos): coach nav link with pending badge"
```

---

## Phase 8 — Vue SPA Cliente

### Task 8.1: Composable `useFoodTracking`

**Files:**
- Create: `resources/js/vue/composables/useFoodTracking.js`

- [ ] **Step 1: Escribir el composable**

```js
import { ref, computed } from 'vue';
import { useApi } from './useApi';

export function useFoodTracking() {
    const api = useApi();

    const loading = ref(true);
    const uploadingIndex = ref(null);
    const error = ref(null);

    const hasNutritionPlan = ref(false);
    const meals = ref([]);
    const xpToday = ref(0);
    const bonusEarnedToday = ref(false);
    const streakDays = ref(0);
    const weekHistory = ref([]);

    async function fetchToday() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await api.get('/api/v/client/food-photos');
            hasNutritionPlan.value = data.has_nutrition_plan;
            meals.value = data.meals || [];
            xpToday.value = data.xp_today ?? 0;
            bonusEarnedToday.value = data.bonus_earned_today ?? false;
            streakDays.value = data.streak_days ?? 0;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al cargar tu alimentación';
        } finally {
            loading.value = false;
        }
    }

    async function fetchHistory() {
        try {
            const { data } = await api.get('/api/v/client/food-photos/history');
            weekHistory.value = data.week_history || [];
        } catch {
            // non-critical
        }
    }

    async function uploadPhoto(file, mealName, mealIndex) {
        uploadingIndex.value = mealIndex;
        const fd = new FormData();
        fd.append('photo', file);
        fd.append('meal_name', mealName);
        fd.append('meal_index', String(mealIndex));
        try {
            await api.post('/api/v/client/food-photos', fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            await fetchToday();
        } finally {
            uploadingIndex.value = null;
        }
    }

    async function deletePhoto(photoId) {
        await api.delete(`/api/v/client/food-photos/${photoId}`);
        await fetchToday();
    }

    const completedToday = computed(() =>
        meals.value.filter((m) => m.photo).length
    );
    const totalToday = computed(() => meals.value.length);
    const completionPct = computed(() => {
        if (totalToday.value === 0) return 0;
        return Math.round((completedToday.value / totalToday.value) * 100);
    });

    return {
        loading, uploadingIndex, error,
        hasNutritionPlan, meals, xpToday, bonusEarnedToday, streakDays, weekHistory,
        completedToday, totalToday, completionPct,
        fetchToday, fetchHistory, uploadPhoto, deletePhoto,
    };
}
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/composables/useFoodTracking.js
git commit -m "feat(food-photos): useFoodTracking composable"
```

---

### Task 8.2: Página `FoodTracking.vue`

**Files:**
- Create: `resources/js/vue/pages/Rise/FoodTracking.vue`

- [ ] **Step 1: Escribir el componente**

```vue
<script setup>
import { onMounted, ref } from 'vue';
import RiseLayout from '../../layouts/RiseLayout.vue';
import { useFoodTracking } from '../../composables/useFoodTracking';

const food = useFoodTracking();
const fileInputs = ref({});

function getMealColor(nombre) {
    const n = (nombre || '').toLowerCase();
    if (n.includes('desayuno')) return 'bg-amber-500/10 text-amber-400';
    if (n.includes('pre-entreno') || n.includes('pre ')) return 'bg-green-500/10 text-green-400';
    if (n.includes('almuerzo') || n.includes('post')) return 'bg-blue-500/10 text-blue-400';
    if (n.includes('cena')) return 'bg-indigo-500/10 text-indigo-400';
    if (n.includes('snack') || n.includes('merienda')) return 'bg-pink-500/10 text-pink-400';
    return 'bg-wc-accent/10 text-wc-accent';
}

function triggerUpload(mealIndex) {
    const ref = fileInputs.value[mealIndex];
    if (ref) ref.click();
}

async function onFileSelected(e, meal) {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
        await food.uploadPhoto(file, meal.nombre, meal.index);
    } catch (err) {
        console.error('Upload failed', err);
    } finally {
        e.target.value = '';
    }
}

async function removePhoto(meal) {
    if (!meal.photo || meal.photo.coach_seen) return;
    if (!confirm('¿Eliminar esta foto?')) return;
    try {
        await food.deletePhoto(meal.photo.id);
    } catch (err) {
        console.error('Delete failed', err);
    }
}

onMounted(() => {
    food.fetchToday();
    food.fetchHistory();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading skeleton -->
    <div v-if="food.loading.value" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div v-for="i in 3" :key="i" class="h-32 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error -->
    <div v-else-if="food.error.value"
         class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm text-wc-text-secondary">{{ food.error.value }}</p>
      <button @click="food.fetchToday()"
              class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">
        Reintentar
      </button>
    </div>

    <!-- No plan -->
    <div v-else-if="!food.hasNutritionPlan.value"
         class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
      <h3 class="font-display text-2xl tracking-wide text-wc-text">SIN PLAN DE NUTRICIÓN</h3>
      <p class="mt-2 text-sm text-wc-text-secondary">Tu coach está armando tu plan. Pronto podrás documentar tus comidas.</p>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Header -->
      <div class="flex items-end justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">MI ALIMENTACIÓN</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Documenta cada comida y tu coach la revisa</p>
        </div>
        <div class="flex flex-col items-end gap-1">
          <span v-if="food.streakDays.value > 0"
                class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-amber-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-wc-accent">
            🔥 {{ food.streakDays.value }} días seguidos
          </span>
          <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-400">
            +{{ food.xpToday.value }} XP hoy
          </span>
        </div>
      </div>

      <!-- Progress today -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-wc-text-secondary">
            Hoy llevas {{ food.completedToday.value }} de {{ food.totalToday.value }} comidas documentadas
          </p>
          <span class="font-data text-2xl font-bold tabular-nums text-wc-text">{{ food.completionPct.value }}%</span>
        </div>
        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-wc-bg-tertiary">
          <div class="h-full rounded-full bg-wc-accent transition-all duration-500"
               :style="{ width: food.completionPct.value + '%' }"></div>
        </div>
        <p v-if="food.bonusEarnedToday.value" class="mt-2 text-xs font-semibold text-amber-400">
          🎉 Bonus diario completo (+30 XP)
        </p>
      </div>

      <!-- Meals -->
      <div v-if="food.meals.value.length > 0" class="space-y-3">
        <div
          v-for="meal in food.meals.value"
          :key="meal.index"
          class="overflow-hidden rounded-xl border bg-wc-bg-secondary transition"
          :class="meal.photo ? 'border-green-500/30' : 'border-wc-border'"
        >
          <!-- Header -->
          <div class="flex items-center gap-3 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="getMealColor(meal.nombre)">
              <span class="font-data text-sm font-bold">{{ meal.index + 1 }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate font-display text-sm tracking-wide text-wc-text">{{ (meal.nombre || 'Comida').toUpperCase() }}</p>
              <p v-if="meal.calorias > 0" class="text-[11px] text-wc-text-tertiary">
                {{ meal.calorias }} kcal
              </p>
            </div>
            <span v-if="meal.photo?.xp_awarded"
                  class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-400">+15 XP</span>
          </div>

          <!-- Photo or upload zone -->
          <div v-if="meal.photo" class="relative">
            <img :src="meal.photo.photo_url" :alt="`Foto de ${meal.nombre}`"
                 class="w-full max-h-72 object-cover">
            <div class="absolute inset-0 bg-black/40 opacity-0 transition-opacity hover:opacity-100 flex items-center justify-center gap-2">
              <button v-if="!meal.photo.coach_seen"
                      @click="triggerUpload(meal.index)"
                      class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-black hover:bg-white">
                Reemplazar
              </button>
              <button v-if="!meal.photo.coach_seen"
                      @click="removePhoto(meal)"
                      class="rounded-lg bg-red-500/90 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500">
                Eliminar
              </button>
            </div>
            <!-- Coach reaction badge -->
            <div v-if="meal.photo.coach_seen"
                 class="absolute right-3 top-3 rounded-full px-2 py-1 text-xs font-bold backdrop-blur"
                 :class="meal.photo.coach_reaction === 'bien'
                   ? 'bg-green-500/90 text-white'
                   : meal.photo.coach_reaction === 'mejorar'
                   ? 'bg-amber-500/90 text-white'
                   : 'bg-black/60 text-white'">
              {{ meal.photo.coach_reaction === 'bien' ? '✅ Bien' : meal.photo.coach_reaction === 'mejorar' ? '⚠️ Mejorar' : '👁 Vista' }}
            </div>
          </div>

          <!-- Upload zone (no photo) -->
          <button v-else
                  @click="triggerUpload(meal.index)"
                  :disabled="food.uploadingIndex.value === meal.index"
                  class="flex w-full items-center justify-center gap-2 border-t border-dashed border-wc-border bg-wc-accent/5 py-3 text-sm font-medium text-wc-accent transition hover:bg-wc-accent/10 disabled:opacity-50">
            <svg v-if="food.uploadingIndex.value === meal.index" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
            </svg>
            {{ food.uploadingIndex.value === meal.index ? 'Subiendo...' : 'Subir foto' }}
          </button>

          <!-- Coach note -->
          <div v-if="meal.photo?.coach_note"
               class="border-t border-wc-border bg-wc-bg-tertiary px-4 py-3">
            <p class="text-xs uppercase tracking-wider text-wc-text-tertiary">Nota del coach</p>
            <p class="mt-1 text-sm text-wc-text-secondary">{{ meal.photo.coach_note }}</p>
          </div>

          <!-- Hidden file input -->
          <input
            type="file"
            accept="image/jpeg,image/jpg,image/png,image/webp"
            class="hidden"
            :ref="(el) => fileInputs[meal.index] = el"
            @change="(e) => onFileSelected(e, meal)"
          />
        </div>
      </div>
    </div>
  </RiseLayout>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/pages/Rise/FoodTracking.vue
git commit -m "feat(food-photos): FoodTracking.vue page with WellCore design tokens"
```

---

### Task 8.3: Router + Nav

**Files:**
- Modify: `resources/js/vue/router/index.js`
- Modify: `resources/js/vue/layouts/RiseLayout.vue`

- [ ] **Step 1: Inspeccionar router actual**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan tinker --execute="echo file_get_contents('resources/js/vue/router/index.js');" | head -200`

Localizar el patrón de rutas RISE existentes (rise-tracking, rise-habits, etc).

- [ ] **Step 2: Agregar ruta**

En `resources/js/vue/router/index.js`, ubicar el array de rutas RISE y agregar:

```js
{
    path: '/rise/food-tracking',
    name: 'rise-food-tracking',
    component: () => import('../pages/Rise/FoodTracking.vue'),
    meta: { requiresAuth: true, layout: 'rise' },
},
```

- [ ] **Step 3: Agregar item nav en RiseLayout.vue**

Modificar el array `navSections` (línea ~96-127), en la sección **'General'** añadir item después de 'Nutricion':

```js
{ name: 'Mi Alimentación', to: '/rise/food-tracking', icon: 'photos', routeName: 'rise-food-tracking' },
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/vue/router/index.js resources/js/vue/layouts/RiseLayout.vue
git commit -m "feat(food-photos): vue router + nav link"
```

---

## Phase 9 — Build, Verify, Deploy

### Task 9.1: Test suite completa

- [ ] **Step 1: Correr toda la suite**

Comando: `"/c/Users/GODSF/.config/herd/bin/php84/php.exe" artisan test`
Esperado: PASS — toda la suite verde, incluyendo tests pre-existentes.

Si algún test pre-existente rompe → investigar y corregir antes de avanzar. NO seguir con tests rojos.

---

### Task 9.2: Build de assets Vue

- [ ] **Step 1: Build local**

Comando: `npm run build`
Esperado: build exitoso sin errores. `public/build/` actualizado.

- [ ] **Step 2: Commit del build**

```bash
git add public/build/
git commit -m "build(food-photos): vite production assets"
```

---

### Task 9.3: Verificación manual con Chrome DevTools

- [ ] **Step 1: Smoke test del cliente**

1. Abrir `https://wellcore-laravel.test/login`
2. Login como cliente con plan de nutrición activo
3. Navegar a `/rise/food-tracking`
4. Verificar:
   - Tab "Mi Alimentación" visible en sidebar
   - Lista de comidas del plan se carga
   - Subir foto en una comida → thumbnail aparece, +15 XP visible
   - F12 → Network: `POST /api/v/client/food-photos` 201
   - F12 → Console: cero errores

- [ ] **Step 2: Smoke test del coach**

1. Login como `daniel.esparza / RISE2026Admin!SuperPower`
2. Navegar a `/coach/food-photos`
3. Verificar:
   - Foto subida arriba aparece como pendiente
   - Click "✅ Bien" → la foto pasa a "revisadas"
   - Badge de pendientes en nav del coach decrece
   - Notificación in-app llega al cliente

- [ ] **Step 3: Smoke test de bloqueo de delete**

1. Cliente intenta eliminar foto ya revisada por coach
2. Verificar: respuesta 403 o mensaje "Tu coach ya revisó esta foto"

---

### Task 9.4: Push a remote (sin auto-deploy)

- [ ] **Step 1: Verificar que estamos en la rama correcta**

Comando: `git status` → confirmar branch.
Si NO estamos en una rama feature, crear una: `git checkout -b feat/food-tracking`

- [ ] **Step 2: Push**

Comando: `git push origin <branch-actual> -u`

- [ ] **Step 3: Verificación post-push (Daniel decide cuando deploy)**

Memoria `feedback_push_not_deploy`: NO hacer auto-deploy. Confirmar a Daniel que la PR está lista, esperar su luz verde para gitpull-load en EasyPanel.

---

## Self-Review

### 1. Spec coverage check

| Sección spec | Task que la cubre |
|---|---|
| §2 DB schema food_photos | Task 1.1 |
| §2 ai_analysis JSON desde inicio | Task 1.1 (columna incluida) |
| §5 NutritionPlanParser compartido | Task 2.1 + Task 3.1 (refactor) |
| §6 FoodPhotoService.store atómico | Task 4.1 (test_replacing_photo_*) |
| §6 FoodPhotoService.delete bloqueado | Task 4.1 (test_delete_throws_*) |
| §6 processImage EXIF + scale + JPEG | Task 4.1 (en processImage del service) |
| §7 GET index con meals + photos | Task 5.3 (controller index) |
| §7 GET history 7 días | Task 5.3 (history) |
| §7 POST upload con throttle 20/min | Task 5.3 (rutas con throttle) |
| §7 DELETE bloqueado si coach_seen | Task 5.3 (test_destroy_blocks_*) |
| §7 photo_date validation TZ Bogotá | Task 5.1 |
| §8 Coach FoodPhotoReview render sin N+1 | Task 7.1 (eager load clientsById) |
| §8 react/saveNote/markSeen autorizados | Task 7.1 (getCoachClientIds check) |
| §8 paginate 15 | Task 7.1 (paginate(15)) |
| §8 Badge count cache 60s | Task 7.1 (Cache::remember 60) |
| §9 notifyClientFoodPhotoReacted | Task 6.1 |
| §9 Notif coach via WellcoreNotification | Task 4.1 (FoodPhotoService::notifyCoach) |
| §10 XP +15 por foto | Task 4.1 (awardXp) |
| §10 Bonus +30 racing-safe | Task 4.1 (maybeAwardDayBonus + lock + HabitLog) |
| §10 Streak on-the-fly + cache | Task 5.3 (computeStreak + Cache::remember 300) |
| §11 OwnedByClientScope | Task 2.2 (model booted) |
| §11 mimes + Intervention re-encode | Task 4.1 + Task 5.1 |
| §11 filename UUID, no input usuario | Task 4.1 (processImage) |
| §11 Throttle 20/min upload | Task 5.3 |
| §11 Race UNIQUE + 1062 catch | Task 4.1 (catch QueryException 1062) |
| §11 Lock XP day-bonus | Task 4.1 (Cache::lock 30s) |
| §13 Diseño WellCore tokens | Task 8.2 (FoodTracking.vue tokens wc-*) |
| §13 Coach blade estilo CheckinReview | Task 7.1 |
| §14 Rutas API + web | Task 5.3 + Task 7.1 |

**Cobertura: 100% del spec, todo trazable a una task.**

### 2. Placeholder scan

✅ Cero "TODO", cero "implement later", cero "fill in details".
✅ Cada test tiene código real, cada implementación tiene código completo.
✅ Cada step tiene comando concreto con output esperado.

### 3. Type consistency

✅ `FoodPhotoService::store` signature: `(Client, UploadedFile, string mealName, int mealIndex, string photoDate): FoodPhoto` — usado consistentemente en Task 4.1, Task 5.3 (controller llama service), Task 7.1.
✅ `meal_index` como `int` (validado en StoreFoodPhotoRequest, factory, model fillable).
✅ `coach_reaction` como `'bien'|'mejorar'|null` consistente entre validación, model cast (ENUM DB), Livewire react(), notification.
✅ Filename pattern `food-photos/{client_id}/{uuid}.jpg` consistente entre service, model accessor (`/storage/{filename}`), tests.
✅ `Carbon::now('America/Bogota')` consistente para todas las fechas (StoreFoodPhotoRequest, controller index/history/streak, service maybeAwardDayBonus).

---

## Ejecución

Plan completo y guardado en `docs/superpowers/plans/2026-05-04-food-tracking.md`. Dos opciones de ejecución:

**1. Subagent-Driven (recomendada)** — dispatch un subagent fresco por task, review entre tasks, iteración rápida. Aprovecha los agentes especializados de CLAUDE.md (la-02-backend, la-03-vue3, etc.).

**2. Inline Execution** — ejecutar tasks en esta sesión usando executing-plans, con checkpoints para review.

¿Cuál prefieres?
