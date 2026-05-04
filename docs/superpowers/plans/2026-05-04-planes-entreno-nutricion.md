# Planes ENTRENO + NUTRICIÓN Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Agregar 2 planes de vertical única (ENTRENO $170k/mes y NUTRICIÓN $153k/mes con promo Mayo) a la oferta WellCore: pricing dinámico en `/planes`, checkout funcional, dashboard cliente con tabs bloqueadas con upsell, y sistema de creación de planes (MDs en E:\) actualizado para sesiones futuras.

**Architecture:** Strangler-fig respetado — extender `config/plans.php` (single source of truth) + `PlanType` enum + tablas hash de acceso en `PlanViewer.vue`. Sin migraciones destructivas; si `clients.plan` es ENUM, migration aditiva. Slugs DB simétricos `entreno_solo` / `nutricion_solo` evitan colisión con `assigned_plans.plan_type='nutricion'`.

**Tech Stack:** Laravel 13.1 / PHP 8.4 / Vue 3.5 + Pinia + Vue Router 4 / Livewire 3 + Alpine / Tailwind CSS 4 / MySQL `wellcore_fitness` / Vite 8 / Wompi.

**Spec:** `docs/superpowers/specs/2026-05-04-planes-entreno-nutricion-design.md` (aprobado por Daniel, 2026-05-04)

---

## File Structure

### Archivos a crear (4)

| Path | Responsabilidad |
|------|-----------------|
| `database/migrations/2026_05_04_120000_extend_clients_plan_enum_for_vertical_plans.php` | Migration aditiva (solo si `clients.plan` es ENUM) |
| `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\28-PLANES-VERTICAL-UNICA.md` | Documento dedicado a planes vertical única para sesiones Claude Code futuras |
| `~/.claude/projects/.../memory/project_planes_vertical_unica.md` | Memory file documentando lanzamiento |
| (opcional) `resources/js/vue/components/Client/TabLockUpsell.vue` | Sub-componente reutilizable inline a PlanViewer.vue (decisión durante Task 18) |

### Archivos a modificar (15)

| Path | Naturaleza del cambio |
|------|----------------------|
| `config/plans.php` | Agregar 2 entradas |
| `app/Enums/PlanType.php` | Agregar 2 cases + label() |
| `app/Services/PricingService.php` | `BILLABLE_PLANS` |
| `app/Services/PlanLockService.php` | `isMonthlyPlan()` |
| `app/Console/Commands/AutoRenewalCommand.php` | `$monthlyPlans` |
| `app/Http/Controllers/Public/PlanesController.php` | Build prices/totals/savings 5 planes, pasar `plansComplete` + `plansSimple` |
| `resources/views/public/planes.blade.php` | Sección `tiers-simple` + JSON-LD + Alpine planName |
| `resources/css/v2-public.css` | Bloque `/* === TIERS SIMPLES === */` |
| `lang/es/planes.php` | Claves `entreno_*`, `nutricion_*`, `simple_section_*` |
| `lang/en/planes.php` | Mismo set traducido |
| `app/Livewire/Checkout.php` | `getPlans()` + `prefillFromAuthenticatedClient()` |
| `app/Livewire/InscriptionForm.php` | Validación `'plan'` |
| `app/Http/Controllers/Api/PublicFormController.php` | Validación `'plan'` |
| `app/Http/Controllers/Api/AdminController.php` | 3 validaciones |
| `app/Livewire/Admin/InvitationManager.php` + `SendPlanInvitation.php` | Validaciones |
| `resources/js/vue/pages/Public/InscriptionForm.vue` | Agregar 2 planes + stepOrder condicional |
| `resources/js/vue/pages/Client/PlanViewer.vue` | Computed canAccess*, isTabLocked, planTypeLabel, TabLockUpsell |

### Archivos del SISTEMA-CREACION-PLANES (E:\) a modificar (4)

| Path | Cambios |
|------|---------|
| `00-INDEX.md` | Tabla tipos plan_type + orden de lectura + referencia MD 28 |
| `04-REGLAS-POR-TIPO-DE-PLAN.md` | Mapping + duraciones + secciones 7 y 8 + regla maestra |
| `23-NAMING-CANONICO-Y-ALIAS.md` | Enum plan_type + nota dos namespaces |
| `01-PASO-A-PASO.md` | FASE 0 + FASE 1 tabla + FASE 4.5 nota |

---

## Pre-flight checks

### Task 0: Inspección DB y rama de trabajo

**Files:** ninguno (solo lectura)

- [ ] **Step 0.1: Verificar tipo de columna `clients.plan`**

Run desde el contenedor de producción (EasyPanel → wellcorefitness → Console → bash):
```bash
cd /code && php artisan tinker --execute="dump(\DB::select('SHOW CREATE TABLE clients')[0]->{'Create Table'});"
```

O desde local con Herd:
```bash
php artisan tinker --execute="dump(\DB::select('SHOW CREATE TABLE clients')[0]->{'Create Table'});"
```

Buscar la línea de la columna `plan`. Posibles resultados:
- `\`plan\` enum('esencial','metodo','elite','rise','presencial','trial')` → ENUM, requiere migration
- `\`plan\` varchar(...)` → VARCHAR, no requiere migration

Anotar el resultado en un comentario al final de este task antes de continuar.

- [ ] **Step 0.2: Confirmar rama de trabajo**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git status && git log -1 --oneline
```

Expected: rama `main`, working tree con cambios actuales (settings.local.json y archivos del proyecto). Si hay cambios pendientes que se quieran preservar, hacer `git stash` antes de empezar tareas — el plan se ejecuta sobre `main` directamente según workflow del proyecto.

- [ ] **Step 0.3: Verificar `composer install` y `npm install` están al día**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && composer install --no-interaction && npm install
```

Expected: dependencias instaladas sin errores.

---

## Phase 1 — Backend foundation

### Task 1: Agregar entradas a `config/plans.php`

**Files:**
- Modify: `config/plans.php`

- [ ] **Step 1.1: Leer el archivo actual para confirmar estructura**

Run:
```bash
cat "C:/Users/GODSF/Herd/wellcore-laravel/config/plans.php"
```

Expected: ver entradas para `esencial`, `metodo`, `elite`, `rise`, `presencial`, `trial`.

- [ ] **Step 1.2: Insertar entradas de `entreno_solo` y `nutricion_solo`**

Editar `config/plans.php` insertando estas dos entradas después de la entrada `elite` (líneas ~50) y antes de `rise`:

```php
    'entreno_solo' => [
        'name' => 'Entreno',
        'price_cop' => 170000,
        'price_cop_original' => 200000,
        'price_usd' => 42,
        'price_usd_original' => 49,
        'desc' => 'Solo entrenamiento — coach humano ajusta cada mes',
        'includes' => ['entrenamiento'],
        'features_count' => 7,
        'tier' => 'simple',
    ],
    'nutricion_solo' => [
        'name' => 'Nutrición',
        'price_cop' => 153000,
        'price_cop_original' => 180000,
        'price_usd' => 37,
        'price_usd_original' => 44,
        'desc' => 'Solo nutrición — coach humano ajusta cada mes',
        'includes' => ['nutricion'],
        'features_count' => 7,
        'tier' => 'simple',
    ],
```

- [ ] **Step 1.3: Verificar que el archivo parsea**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php -r "var_dump(array_keys(require 'config/plans.php'));"
```

Expected: array que contiene `'entreno_solo'` y `'nutricion_solo'` entre las keys.

- [ ] **Step 1.4: Limpiar config cache**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan config:clear
```

Expected: "Configuration cache cleared successfully."

- [ ] **Step 1.5: Smoke check via tinker**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="echo config('plans.entreno_solo.price_cop'), PHP_EOL, config('plans.nutricion_solo.price_cop'), PHP_EOL;"
```

Expected: `170000` y `153000`.

---

### Task 2: Extender enum `PlanType`

**Files:**
- Modify: `app/Enums/PlanType.php`

- [ ] **Step 2.1: Leer archivo actual**

Run:
```bash
cat "C:/Users/GODSF/Herd/wellcore-laravel/app/Enums/PlanType.php"
```

Expected: 6 cases (Esencial, Metodo, Elite, Rise, Presencial, Trial).

- [ ] **Step 2.2: Agregar 2 cases nuevos y actualizar `label()`**

Reemplazar contenido completo del archivo:

```php
<?php

namespace App\Enums;

enum PlanType: string
{
    case Esencial = 'esencial';
    case Metodo = 'metodo';
    case Elite = 'elite';
    case EntrenoSolo = 'entreno_solo';
    case NutricionSolo = 'nutricion_solo';
    case Rise = 'rise';
    case Presencial = 'presencial';
    case Trial = 'trial';

    public function label(): string
    {
        return match ($this) {
            self::Esencial => 'Esencial',
            self::Metodo => 'Metodo',
            self::Elite => 'Elite',
            self::EntrenoSolo => 'Entreno',
            self::NutricionSolo => 'Nutrición',
            self::Rise => 'Rise',
            self::Presencial => 'Presencial',
            self::Trial => 'Trial',
        };
    }
}
```

- [ ] **Step 2.3: Verificar enum**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="echo \App\Enums\PlanType::EntrenoSolo->value, ' = ', \App\Enums\PlanType::EntrenoSolo->label(), PHP_EOL; echo \App\Enums\PlanType::NutricionSolo->value, ' = ', \App\Enums\PlanType::NutricionSolo->label(), PHP_EOL;"
```

Expected:
```
entreno_solo = Entreno
nutricion_solo = Nutrición
```

---

### Task 3: Migration aditiva (solo si `clients.plan` es ENUM)

**Files:**
- Create (condicional): `database/migrations/2026_05_04_120000_extend_clients_plan_enum_for_vertical_plans.php`

> **Skip esta tarea si Step 0.1 reveló VARCHAR.** Saltar directamente a Task 4.

- [ ] **Step 3.1: Crear migration**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan make:migration extend_clients_plan_enum_for_vertical_plans
```

Expected: archivo creado en `database/migrations/`.

- [ ] **Step 3.2: Reemplazar contenido de la migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration ADITIVA — agrega 2 valores nuevos al enum clients.plan.
 *
 * NO destructiva: respeta valores existentes ('esencial','metodo','elite','rise','presencial','trial').
 * Solo agrega 'entreno_solo' y 'nutricion_solo' para soportar planes de vertical única.
 *
 * Si la columna ya es VARCHAR (no ENUM), esta migration es no-op idempotente.
 */
return new class extends Migration {
    public function up(): void
    {
        $columnInfo = collect(DB::select("SHOW COLUMNS FROM clients WHERE Field = 'plan'"))->first();

        if (! $columnInfo) {
            return;
        }

        $type = strtolower((string) $columnInfo->Type);

        if (! str_starts_with($type, 'enum')) {
            // Columna ya VARCHAR u otro tipo flexible — no requiere ALTER
            return;
        }

        DB::statement("ALTER TABLE clients MODIFY COLUMN plan ENUM('esencial','metodo','elite','rise','presencial','trial','entreno_solo','nutricion_solo') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        // Rollback: solo si NO hay clientes activos en los 2 nuevos planes.
        $count = DB::table('clients')
            ->whereIn('plan', ['entreno_solo', 'nutricion_solo'])
            ->count();

        if ($count > 0) {
            throw new \RuntimeException(
                "No se puede rollback: hay {$count} clientes con plan entreno_solo/nutricion_solo. Migrar esos clientes a otro plan antes."
            );
        }

        DB::statement("ALTER TABLE clients MODIFY COLUMN plan ENUM('esencial','metodo','elite','rise','presencial','trial') NULL DEFAULT NULL");
    }
};
```

- [ ] **Step 3.3: Correr migration en local**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan migrate
```

Expected: "Migrating: 2026_05_04_120000_extend_clients_plan_enum_for_vertical_plans" → "Migrated".

- [ ] **Step 3.4: Verificar enum extendido**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="dump(collect(\DB::select(\"SHOW COLUMNS FROM clients WHERE Field = 'plan'\"))->first()->Type);"
```

Expected: el string del enum debe incluir `entreno_solo` y `nutricion_solo`.

---

### Task 4: Extender `PricingService::BILLABLE_PLANS`

**Files:**
- Modify: `app/Services/PricingService.php:9`

- [ ] **Step 4.1: Editar la constante**

Reemplazar línea 9:

```php
    private const BILLABLE_PLANS = ['esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo', 'rise'];
```

- [ ] **Step 4.2: Smoke check via tinker**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="\$p = app(\App\Services\PricingService::class); dump(\$p->allPrices());"
```

Expected: array con 6 entradas (esencial, metodo, elite, entreno_solo, nutricion_solo, rise) con sus precios.

---

## Phase 2 — Página `/planes` pública

### Task 5: Refactor `PlanesController` para 5 planes

**Files:**
- Modify: `app/Http/Controllers/Public/PlanesController.php`

- [ ] **Step 5.1: Reemplazar contenido del archivo**

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PricingService;

class PlanesController extends Controller
{
    /**
     * Render /planes con pricing dinámico (5 planes × 3 períodos × 2 monedas).
     *
     * - 3 planes "completos": esencial, metodo, elite (sistema completo)
     * - 2 planes "simples": entreno_solo, nutricion_solo (vertical única)
     *
     * Source of truth = PricingService::priceCop() / priceUsd().
     * Los descuentos por período (-10% trim / -20% anual) se aplican aquí —
     * NO en PricingService — para evitar afectar otras vistas que solo usan
     * el precio mensual (admin, cliente, schema.org).
     */
    public function index(PricingService $pricing)
    {
        $plansComplete = ['esencial', 'metodo', 'elite'];
        $plansSimple   = ['entreno_solo', 'nutricion_solo'];
        $plans         = array_merge($plansComplete, $plansSimple);

        $periods = ['mensual', 'trimestral', 'anual'];
        $months  = ['mensual' => 1, 'trimestral' => 3, 'anual' => 12];

        $applyDiscount = static function (int $monthly, string $period): int {
            return match ($period) {
                'trimestral' => (int) round($monthly * 0.9),
                'anual'      => (int) round($monthly * 0.8),
                default      => $monthly,
            };
        };

        $monthlyCop = [];
        $monthlyUsd = [];
        foreach ($plans as $plan) {
            $monthlyCop[$plan] = $pricing->priceCop($plan);
            $monthlyUsd[$plan] = $pricing->priceUsd($plan);
        }

        $build = static function (array $monthlyByPlan) use ($plans, $periods, $months, $applyDiscount): array {
            $prices = [];
            $totals = [];
            $savings = [];
            foreach ($plans as $plan) {
                foreach ($periods as $period) {
                    $perMonth = $applyDiscount($monthlyByPlan[$plan], $period);
                    $prices[$plan][$period]  = $perMonth;
                    $totals[$plan][$period]  = $perMonth * $months[$period];
                    $savings[$plan][$period] = ($monthlyByPlan[$plan] - $perMonth) * $months[$period];
                }
            }
            return compact('prices', 'totals', 'savings');
        };

        $cop = $build($monthlyCop);
        $usd = $build($monthlyUsd);

        return view('public.planes', [
            'plansComplete' => $plansComplete,
            'plansSimple'   => $plansSimple,
            'monthlyCop'    => $monthlyCop,
            'monthlyUsd'    => $monthlyUsd,
            'pricesCop'     => $cop['prices'],
            'totalsCop'     => $cop['totals'],
            'savingsCop'    => $cop['savings'],
            'pricesUsd'     => $usd['prices'],
            'totalsUsd'     => $usd['totals'],
            'savingsUsd'    => $usd['savings'],
            'promoActive'   => $pricing->isPromoActive(),
            'discountPct'   => (int) config('plans.promo.discount_pct', 0),
            'promoLabel'    => (string) config('plans.promo.label', ''),
        ]);
    }
}
```

- [ ] **Step 5.2: Smoke test del controller via curl/browser**

Run dev server:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan serve --port=8000
```

En otra terminal:
```bash
curl -s http://127.0.0.1:8000/planes | grep -o "tier-track\|t-card-esencial\|t-card-metodo\|t-card-elite" | sort -u
```

Expected: la página renderiza sin error 500. Las 3 cards principales aparecen. (Las 2 nuevas no aparecerán hasta Task 9).

---

### Task 6: Lang ES — claves nuevas

**Files:**
- Modify: `lang/es/planes.php`

- [ ] **Step 6.1: Agregar claves al final del array, antes del cierre `];`**

Insertar antes de la línea final `];`:

```php
    // =========================================================
    // V2 — Planes simples (vertical única)
    // =========================================================
    'simple_section_eyebrow' => 'PLANES SIMPLES · UNA VERTICAL',
    'simple_section_h2'      => '¿Solo necesitas una?',
    'simple_section_sub'     => 'Plan de entrenamiento o nutrición por separado, con tu coach humano y ajuste mensual. Cuando estés listo, subes a un plan completo.',

    // ENTRENO (solo entrenamiento)
    'entreno_solo_name'  => 'ENTRENO',
    'entreno_solo_desc'  => 'Solo entrenamiento — tu coach ajusta cada mes.',
    'entreno_solo_quote' => 'Para quien tiene la nutrición resuelta y solo necesita un plan de entrenamiento que se ajuste cada mes con tu coach.',
    'entreno_solo_pillars' => [
        'Entrenamiento personalizado · ejercicios con video o demostración, registro de pesos, récords automáticos',
        'Tu coach humano ajusta el plan cada mes · revisa tu check-in y adapta volumen, ejercicios y progresiones',
        'Acceso completo a la plataforma · Voice Logger, variaciones, comunidad, misiones diarias y XP',
    ],
    'entreno_solo_cta' => 'Comenzar Entreno',

    // NUTRICIÓN (solo nutrición)
    'nutricion_solo_name'  => 'NUTRICIÓN',
    'nutricion_solo_desc'  => 'Solo nutrición — tu coach ajusta cada mes.',
    'nutricion_solo_quote' => 'Para quien ya entrena bien y necesita una estrategia nutricional con macros, plan de comidas y ajuste mensual.',
    'nutricion_solo_pillars' => [
        'Nutrición 100% personalizada · macros, plan de comidas con 3 opciones por plato, agua diaria',
        'Tu coach humano ajusta el plan cada mes · revisa tu check-in y adapta calorías, macros y horarios',
        'Acceso completo a la plataforma · 3 opciones por plato, agua diaria, comunidad y XP',
    ],
    'nutricion_solo_cta' => 'Comenzar Nutrición',
```

- [ ] **Step 6.2: Verificar parseo del lang**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="echo __('planes.entreno_solo_name'), ' / ', __('planes.nutricion_solo_name'), PHP_EOL;"
```

Expected: `ENTRENO / NUTRICIÓN`.

---

### Task 7: Lang EN — claves nuevas

**Files:**
- Modify: `lang/en/planes.php`

- [ ] **Step 7.1: Leer estructura actual**

Run:
```bash
head -50 "C:/Users/GODSF/Herd/wellcore-laravel/lang/en/planes.php"
```

- [ ] **Step 7.2: Agregar claves traducidas**

Insertar antes de la línea final `];` del archivo:

```php
    // =========================================================
    // V2 — Simple plans (single vertical)
    // =========================================================
    'simple_section_eyebrow' => 'SIMPLE PLANS · SINGLE VERTICAL',
    'simple_section_h2'      => 'Need just one?',
    'simple_section_sub'     => 'Training-only or nutrition-only plan, with your human coach and monthly adjustments. When you are ready, you upgrade to a complete plan.',

    // ENTRENO (training only)
    'entreno_solo_name'  => 'TRAINING',
    'entreno_solo_desc'  => 'Training only — your coach adjusts every month.',
    'entreno_solo_quote' => 'For those who have nutrition handled and only need a training plan that adjusts monthly with your coach.',
    'entreno_solo_pillars' => [
        'Personalized training · exercises with video or demo, weight logging, automatic records',
        'Your human coach adjusts the plan every month · reviews your check-in and adapts volume, exercises and progressions',
        'Full platform access · Voice Logger, variations, community, daily missions and XP',
    ],
    'entreno_solo_cta' => 'Start Training',

    // NUTRICIÓN (nutrition only)
    'nutricion_solo_name'  => 'NUTRITION',
    'nutricion_solo_desc'  => 'Nutrition only — your coach adjusts every month.',
    'nutricion_solo_quote' => 'For those who train well and need a nutritional strategy with macros, meal plan and monthly adjustments.',
    'nutricion_solo_pillars' => [
        '100% personalized nutrition · macros, meal plan with 3 options per dish, daily water',
        'Your human coach adjusts the plan every month · reviews your check-in and adapts calories, macros and timing',
        'Full platform access · 3 options per dish, daily water, community and XP',
    ],
    'nutricion_solo_cta' => 'Start Nutrition',
```

- [ ] **Step 7.3: Verificar parseo en EN**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="app()->setLocale('en'); echo __('planes.entreno_solo_name'), ' / ', __('planes.nutricion_solo_name'), PHP_EOL;"
```

Expected: `TRAINING / NUTRITION`.

---

### Task 8: CSS — bloque `tiers-simple`

**Files:**
- Modify: `resources/css/v2-public.css`

- [ ] **Step 8.1: Localizar el bloque actual de `.t-card`**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && grep -n "t-card-esencial\|t-card-metodo\|t-card-elite\|tier-track" resources/css/v2-public.css | head -20
```

Anotar la línea donde termina el bloque de `.t-card-elite` (antes del comparador o de la siguiente sección).

- [ ] **Step 8.2: Agregar bloque tiers-simple**

Insertar al final del bloque de tier-cards (después de `.t-card-elite { ... }`), respetando el formato del archivo:

```css
/* ===========================================================
   TIERS SIMPLES — Planes vertical única (entreno_solo / nutricion_solo)
   Hermanas en par, más livianas, sin scroll-snap, grid 2-cols.
   =========================================================== */
.tiers-simple {
    padding: 4rem 1rem 2rem;
    max-width: 1100px;
    margin: 0 auto;
}

.tiers-simple-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.tiers-simple-eyebrow {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 0.75rem;
    letter-spacing: 0.18em;
    color: var(--wc-text-muted, rgba(255,255,255,0.6));
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.tiers-simple-h2 {
    font-family: var(--font-display, 'Bebas Neue', 'Oswald', sans-serif);
    font-size: clamp(2rem, 4.5vw, 3rem);
    line-height: 1;
    color: var(--wc-text, #fff);
    margin: 0 0 0.6rem;
    letter-spacing: -0.01em;
}

.tiers-simple-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: clamp(0.95rem, 1.6vw, 1.05rem);
    line-height: 1.5;
    color: var(--wc-text-muted, rgba(255,255,255,0.7));
    max-width: 580px;
    margin: 0 auto;
}

.tiers-simple-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem;
    margin-top: 2rem;
}

@media (max-width: 720px) {
    .tiers-simple-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

.t-card-simple {
    position: relative;
    background: var(--wc-bg-secondary, #111);
    border: 1px solid var(--wc-border, rgba(255,255,255,0.08));
    border-radius: 14px;
    padding: 1.6rem 1.4rem 1.6rem;
    display: flex;
    flex-direction: column;
    transition: border-color 200ms ease, transform 200ms ease;
}

.t-card-simple:hover {
    border-color: rgba(220, 38, 38, 0.35);
}

.t-card-simple.is-selected {
    border-color: var(--wc-accent, #DC2626);
}

.t-card-simple-eyebrow {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 0.7rem;
    letter-spacing: 0.22em;
    color: var(--wc-accent, #DC2626);
    text-transform: uppercase;
    margin-bottom: 0.6rem;
}

.t-card-simple-name {
    font-family: var(--font-display, 'Oswald', 'Bebas Neue', sans-serif);
    font-weight: 700;
    font-size: clamp(1.6rem, 3vw, 2.1rem);
    line-height: 1;
    color: var(--wc-text, #fff);
    margin: 0 0 1rem;
    letter-spacing: -0.01em;
}

.t-card-simple-price-block {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    flex-wrap: wrap;
    margin-bottom: 0.4rem;
}

.t-card-simple-price-sym {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 1.1rem;
    color: var(--wc-text-muted, rgba(255,255,255,0.65));
}

.t-card-simple-price-num {
    font-family: var(--font-display, 'Oswald', sans-serif);
    font-weight: 700;
    font-size: clamp(1.9rem, 3.4vw, 2.4rem);
    color: var(--wc-text, #fff);
    line-height: 1;
}

.t-card-simple-cop {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 0.85rem;
    color: var(--wc-text-muted, rgba(255,255,255,0.65));
    text-transform: lowercase;
}

.t-card-simple-note {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 0.78rem;
    color: var(--wc-text-muted, rgba(255,255,255,0.55));
    min-height: 1.2em;
    margin-bottom: 1rem;
}

.t-card-simple-quote {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 0.92rem;
    line-height: 1.5;
    color: var(--wc-text-muted, rgba(255,255,255,0.78));
    margin: 0 0 1.1rem;
}

.t-card-simple-pillars {
    list-style: none;
    padding: 0;
    margin: 0 0 1.4rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.t-card-simple-pillar {
    position: relative;
    padding-left: 1rem;
    font-family: 'Inter', sans-serif;
    font-size: 0.86rem;
    line-height: 1.45;
    color: var(--wc-text, rgba(255,255,255,0.88));
}

.t-card-simple-pillar::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.55em;
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 50%;
    background: var(--wc-accent, #DC2626);
}

.t-card-simple-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: auto;
    padding: 0.75rem 1.2rem;
    border: 1px solid var(--wc-border, rgba(255,255,255,0.18));
    border-radius: 10px;
    background: transparent;
    color: var(--wc-text, #fff);
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-weight: 600;
    font-size: 0.88rem;
    letter-spacing: 0.04em;
    text-decoration: none;
    text-transform: uppercase;
    transition: background-color 180ms ease, border-color 180ms ease;
}

.t-card-simple-cta:hover {
    background: rgba(220, 38, 38, 0.1);
    border-color: var(--wc-accent, #DC2626);
}
```

- [ ] **Step 8.3: Build de Vite para validar el CSS**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && npm run build 2>&1 | tail -30
```

Expected: build exitoso sin errores de CSS. Output finaliza con tabla de assets generados (incluido `public/build/...`).

---

### Task 9: Blade — sección `tiers-simple` + JSON-LD + Alpine planName

**Files:**
- Modify: `resources/views/public/planes.blade.php`

- [ ] **Step 9.1: Actualizar JSON-LD `OfferCatalog` (líneas ~17-19)**

Buscar el array `itemListElement` y reemplazarlo con:

```php
            'itemListElement' => [
                ['@type' => 'Offer', 'name' => 'Esencial',  'price' => (string) $monthlyCop['esencial'],       'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Metodo',    'price' => (string) $monthlyCop['metodo'],         'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Elite',     'price' => (string) $monthlyCop['elite'],          'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Entreno',   'price' => (string) $monthlyCop['entreno_solo'],   'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Nutricion', 'price' => (string) $monthlyCop['nutricion_solo'], 'priceCurrency' => 'COP'],
            ],
```

- [ ] **Step 9.2: Actualizar `planName()` del Alpine root (líneas ~64-67)**

Buscar el método `planName(p)` y reemplazarlo con:

```php
            planName(p) {
                const names = {
                    esencial: @js(__('planes.esencial_name')),
                    metodo: @js(__('planes.metodo_name')),
                    elite: @js(__('planes.elite_name')),
                    entreno_solo: @js(__('planes.entreno_solo_name')),
                    nutricion_solo: @js(__('planes.nutricion_solo_name')),
                };
                return names[p] || '';
            },
```

- [ ] **Step 9.3: Insertar sección `tiers-simple` justo después de `</section>` de TierCards (línea ~187, después del `@endforeach</div></section>` que cierra el track principal) y antes de `<x-public.s-divider :label="__('planes.divider_comparador')" />`**

Insertar este bloque:

```blade
        {{-- ═══ Section divider · Planes simples (vertical única) ═══ --}}
        <section class="tiers-simple" id="tier-simple" data-animate>
            <div class="tiers-simple-header">
                <p class="tiers-simple-eyebrow">{{ __('planes.simple_section_eyebrow') }}</p>
                <h2 class="tiers-simple-h2">{{ __('planes.simple_section_h2') }}</h2>
                <p class="tiers-simple-sub">{{ __('planes.simple_section_sub') }}</p>
            </div>

            <div class="tiers-simple-grid">
                @foreach($plansSimple as $i => $plan)
                    <article
                        class="t-card-simple t-card-simple-{{ $plan }}"
                        data-plan="{{ $plan }}"
                        data-animate
                        @if($i > 0) data-stagger="{{ $i }}" @endif
                        @click="selectPlan('{{ $plan }}')"
                        :class="{ 'is-selected': selectedPlan === '{{ $plan }}' }"
                    >
                        <div class="t-card-simple-eyebrow">· {{ __("planes.{$plan}_name") }}</div>

                        <div class="t-card-simple-name">{{ __("planes.{$plan}_name") }}</div>

                        <div class="t-card-simple-price-block">
                            <span class="t-card-simple-price-sym">$</span>
                            <span class="t-card-simple-price-num" x-text="priceOf('{{ $plan }}')">{{ number_format($pricesCop[$plan]['mensual'], 0, ',', '.') }}</span>
                            <span class="t-card-simple-cop">{{ __('planes.cop_mes') }}</span>
                        </div>
                        <p class="t-card-simple-note" x-text="noteOf('{{ $plan }}')">&nbsp;</p>

                        <p class="t-card-simple-quote">{{ __("planes.{$plan}_quote") }}</p>

                        <ul class="t-card-simple-pillars">
                            @foreach(__("planes.{$plan}_pillars") as $pillar)
                                <li class="t-card-simple-pillar">{{ $pillar }}</li>
                            @endforeach
                        </ul>

                        <a
                            :href="`{{ route('pagar') }}?plan={{ $plan }}&period=${period}`"
                            class="t-card-simple-cta"
                            @click.stop
                        >
                            {{ __("planes.{$plan}_cta") }}
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
```

- [ ] **Step 9.4: Smoke test browser**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan view:clear && php artisan serve --port=8000 &
sleep 3
curl -s http://127.0.0.1:8000/planes | grep -E "tiers-simple|t-card-simple-entreno_solo|t-card-simple-nutricion_solo" | head -10
```

Expected: ver al menos 5 líneas con `tiers-simple` o `t-card-simple-...`.

- [ ] **Step 9.5: Build assets**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && npm run build
```

Expected: build exitoso. Asset hash de `app.css` cambia.

---

## Phase 3 — Checkout y formularios

### Task 10: Checkout Livewire — agregar planes

**Files:**
- Modify: `app/Livewire/Checkout.php:77, 128`

- [ ] **Step 10.1: Editar línea 77 (`getPlans()` foreach)**

Reemplazar:
```php
        foreach (['rise', 'esencial', 'metodo', 'elite'] as $key) {
```

Con:
```php
        foreach (['rise', 'esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo'] as $key) {
```

- [ ] **Step 10.2: Editar línea 128 (`prefillFromAuthenticatedClient`)**

Reemplazar:
```php
            if (in_array($planValue, ['esencial', 'metodo', 'elite'], true)) {
                $this->selectPlan($planValue);
            }
```

Con:
```php
            if (in_array($planValue, ['esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo'], true)) {
                $this->selectPlan($planValue);
            }
```

- [ ] **Step 10.3: Smoke test del checkout**

Run dev server (si no está corriendo) y abrir en navegador:
```
http://127.0.0.1:8000/pagar?plan=entreno_solo
http://127.0.0.1:8000/pagar?plan=nutricion_solo
```

Expected: la página carga, Step 2 visible con el plan correcto seleccionado.

---

### Task 11: InscriptionForm Livewire — validación

**Files:**
- Modify: `app/Livewire/InscriptionForm.php:72`

- [ ] **Step 11.1: Editar línea 72**

Reemplazar:
```php
            0 => ['plan' => 'required|in:esencial,metodo,elite'],
```

Con:
```php
            0 => ['plan' => 'required|in:esencial,metodo,elite,entreno_solo,nutricion_solo'],
```

- [ ] **Step 11.2: Smoke test parsing**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php -l app/Livewire/InscriptionForm.php
```

Expected: `No syntax errors detected`.

---

### Task 12: PublicFormController — validación API

**Files:**
- Modify: `app/Http/Controllers/Api/PublicFormController.php:44`

- [ ] **Step 12.1: Editar línea 44**

Reemplazar:
```php
            'plan' => 'required|in:esencial,metodo,elite',
```

Con:
```php
            'plan' => 'required|in:esencial,metodo,elite,entreno_solo,nutricion_solo',
```

- [ ] **Step 12.2: Smoke test parsing**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php -l app/Http/Controllers/Api/PublicFormController.php
```

Expected: `No syntax errors detected`.

---

### Task 13: Validaciones admin (3 controllers)

**Files:**
- Modify: `app/Http/Controllers/Api/AdminController.php:1991, 3032, 3096`
- Modify: `app/Livewire/Admin/InvitationManager.php:71`
- Modify: `app/Livewire/Admin/SendPlanInvitation.php:68`

- [ ] **Step 13.1: AdminController línea 1991**

Reemplazar:
```php
            'plan' => 'required|in:rise,esencial,metodo,elite,presencial',
```

Con:
```php
            'plan' => 'required|in:rise,esencial,metodo,elite,presencial,entreno_solo,nutricion_solo',
```

- [ ] **Step 13.2: AdminController línea 3032**

Misma sustitución que 13.1.

- [ ] **Step 13.3: AdminController línea 3096**

Misma sustitución que 13.1.

- [ ] **Step 13.4: InvitationManager.php línea 71**

Reemplazar:
```php
            'newPlan'      => 'required|in:rise,esencial,metodo,elite,presencial',
```

Con:
```php
            'newPlan'      => 'required|in:rise,esencial,metodo,elite,presencial,entreno_solo,nutricion_solo',
```

- [ ] **Step 13.5: SendPlanInvitation.php línea 68**

Reemplazar:
```php
            'selectedPlan' => 'required|in:rise,esencial,metodo,elite,presencial',
```

Con:
```php
            'selectedPlan' => 'required|in:rise,esencial,metodo,elite,presencial,entreno_solo,nutricion_solo',
```

- [ ] **Step 13.6: Lint check de los 3 archivos**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php -l app/Http/Controllers/Api/AdminController.php && php -l app/Livewire/Admin/InvitationManager.php && php -l app/Livewire/Admin/SendPlanInvitation.php
```

Expected: 3 líneas con `No syntax errors detected`.

---

### Task 14: InscriptionForm.vue — agregar 2 planes + step condicional

**Files:**
- Modify: `resources/js/vue/pages/Public/InscriptionForm.vue`

- [ ] **Step 14.1: Agregar entradas al array `plans` (líneas 81-103)**

Buscar el array `plans` (alrededor de línea 81). Mantener las 3 entradas actuales (precios viejos) — están fuera de alcance arreglarlas (decisión P10 del spec). Agregar al final del array, antes de `]`:

```javascript
  {
    id: 'entreno_solo',
    name: 'Entreno',
    price: '$170,000',
    description: 'Solo plan de entrenamiento — coach humano ajusta cada mes.',
    features: ['Plan de entrenamiento personalizado', 'Coach humano con ajuste mensual', 'Acceso a plataforma (sin nutrición ni hábitos)'],
  },
  {
    id: 'nutricion_solo',
    name: 'Nutrición',
    price: '$153,000',
    description: 'Solo plan nutricional — coach humano ajusta cada mes.',
    features: ['Plan nutricional con macros', '3 opciones por plato', 'Coach humano con ajuste mensual'],
  },
```

- [ ] **Step 14.2: Modificar `stepOrder` (líneas ~110-120) para soportar planes vertical única**

Reemplazar el computed `stepOrder` con:

```javascript
const stepOrder = computed(() => {
  if (!isInvitation.value) {
    // Public mode: si plan es entreno_solo, omitir Step 5 (Nutrición)
    // Si plan es nutricion_solo, omitir Steps 2,3,4 (Experiencia, Preferencias, Lesiones)
    if (form.value.plan === 'entreno_solo') {
      return [0, 1, 2, 3, 4, 6, 7]; // skip 5 (Nutrition)
    }
    if (form.value.plan === 'nutricion_solo') {
      return [0, 1, 5, 6, 7]; // skip 2,3,4 (Experience, Preferences, Injuries)
    }
    return [0, 1, 2, 3, 4, 5, 6, 7];
  }
  const plan = invitationData.value?.plan;
  // Step 8 is the new "Avanzado" (Elite-only) step, inserted between Estilo de vida (6) and Final (7).
  if (plan === 'elite') {
    return [1, 2, 3, 4, 5, 6, 8, 7];
  }
  if (plan === 'entreno_solo') {
    return [1, 2, 3, 4, 6, 7];
  }
  if (plan === 'nutricion_solo') {
    return [1, 5, 6, 7];
  }
  return [1, 2, 3, 4, 5, 6, 7];
});
```

- [ ] **Step 14.3: Lint check del .vue**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && npm run build 2>&1 | tail -20
```

Expected: build exitoso, sin errores de Vue. (Las warnings de TypeScript pueden aparecer pero no deben ser errores fatales).

---

## Phase 4 — Dashboard cliente con tabs lock

### Task 15: PlanViewer.vue — extender computed canAccess*

**Files:**
- Modify: `resources/js/vue/pages/Client/PlanViewer.vue:374-386`

- [ ] **Step 15.1: Reemplazar bloque de computeds y `isTabLocked`**

Localizar las líneas 374-386 (los 3 computed actuales `canAccessNutricion`, `canAccessElite`, función `isTabLocked`). Reemplazar el bloque completo con:

```javascript
const canAccessEntrenamiento = computed(() => {
  // Todos los planes con plan asignado pueden ver entrenamiento EXCEPTO nutricion_solo
  return clientPlanType.value !== 'nutricion_solo';
});

const canAccessNutricion = computed(() => {
  // Esencial+, RISE, presencial, nutricion_solo
  return ['esencial', 'metodo', 'elite', 'presencial', 'rise', 'nutricion_solo'].includes(clientPlanType.value);
});

const canAccessHabitos = computed(() => {
  // Hábitos NO disponibles para entreno_solo ni nutricion_solo (son verticales únicas)
  return !['entreno_solo', 'nutricion_solo', 'trial'].includes(clientPlanType.value);
});

const canAccessSuplementacion = computed(() => {
  // Suplementación NO disponible para entreno_solo ni nutricion_solo
  return !['entreno_solo', 'nutricion_solo', 'trial'].includes(clientPlanType.value);
});

const canAccessElite = computed(() => {
  return ['elite'].includes(clientPlanType.value);
});

function isTabLocked(key) {
  if (key === 'entrenamiento' && !canAccessEntrenamiento.value) return true;
  if (key === 'nutricion' && !canAccessNutricion.value) return true;
  if (key === 'habitos' && !canAccessHabitos.value) return true;
  if (key === 'suplementacion' && !canAccessSuplementacion.value) return true;
  if (['ciclo', 'bloodwork'].includes(key) && !canAccessElite.value) return true;
  return false;
}
```

- [ ] **Step 15.2: Verificar que la matriz de §6.4.1 del spec se cumple**

Validación manual: revisar que para cada combinación `clientPlanType` + tab, `isTabLocked()` devuelve el valor esperado de la tabla §6.4.1. Anotar en este task que se revisó.

---

### Task 16: PlanViewer.vue — `planTypeLabel` map

**Files:**
- Modify: `resources/js/vue/pages/Client/PlanViewer.vue:414-417`

- [ ] **Step 16.1: Reemplazar el map de `planTypeLabel`**

Buscar el computed `planTypeLabel` (alrededor de línea 414):

```javascript
const planTypeLabel = computed(() => {
  const t = (clientPlanType.value || '').toLowerCase();
  const map = { basico: 'Esencial', metodo: 'Método', elite: 'Elite', rise: 'RISE', presencial: 'Presencial', trial: 'Trial' };
  return map[t] || (t ? t.charAt(0).toUpperCase() + t.slice(1) : '');
});
```

Reemplazar con:

```javascript
const planTypeLabel = computed(() => {
  const t = (clientPlanType.value || '').toLowerCase();
  const map = {
    basico: 'Esencial',
    esencial: 'Esencial',
    metodo: 'Método',
    elite: 'Elite',
    rise: 'RISE',
    presencial: 'Presencial',
    trial: 'Trial',
    entreno_solo: 'Entreno',
    nutricion_solo: 'Nutrición',
  };
  return map[t] || (t ? t.charAt(0).toUpperCase() + t.slice(1) : '');
});
```

---

### Task 17: PlanViewer.vue — TabLockUpsell card inline

**Files:**
- Modify: `resources/js/vue/pages/Client/PlanViewer.vue` (template + script setup)

- [ ] **Step 17.1: Localizar el bloque de tab `entrenamiento` (línea ~1033)**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && grep -n "v-if=\"activeTab === 'entrenamiento'\"\|v-else-if=\"activeTab === 'habitos'\"\|v-else-if=\"activeTab === 'nutricion'\"" resources/js/vue/pages/Client/PlanViewer.vue
```

Anotar las líneas donde aparecen los `v-if` / `v-else-if` para cada tab.

- [ ] **Step 17.2: Modificar el bloque del tab `entrenamiento` para mostrar TabLockUpsell si está lockeado**

Localizar el bloque que empieza con `<div v-if="activeTab === 'entrenamiento'">` y reemplazar la apertura por:

```html
<div v-if="activeTab === 'entrenamiento'">
  <div v-if="isTabLocked('entrenamiento')" class="tab-lock-upsell">
    <div class="tab-lock-upsell-icon">🔒</div>
    <h3 class="tab-lock-upsell-title">Tu plan no incluye entrenamiento</h3>
    <p class="tab-lock-upsell-body">Tu plan {{ planTypeLabel }} se enfoca en nutrición. Suma entrenamiento con un plan completo desde $84.000/mes más con Plan Esencial.</p>
    <a href="/planes#tier-cards" class="tab-lock-upsell-cta">Ver Plan Esencial</a>
  </div>
  <template v-else>
    <!-- BEGIN entrenamiento original content -->
```

Y al final del bloque (justo antes del cierre `</div>` que cierra el bloque del tab), agregar:

```html
    <!-- END entrenamiento original content -->
  </template>
</div>
```

- [ ] **Step 17.3: Repetir para tab `habitos` (línea ~1338)**

Misma estructura, con copy:
- title: `Tu plan no incluye hábitos`
- body: `Tu plan {{ planTypeLabel }} no incluye seguimiento de hábitos. Súmalo con Plan Esencial desde $84.000/mes más.`

- [ ] **Step 17.4: Repetir para tab `nutricion` (línea ~1438)**

Misma estructura, con copy:
- title: `Tu plan no incluye nutrición`
- body: `Tu plan {{ planTypeLabel }} se enfoca en entrenamiento. Suma nutrición con un plan completo desde $84.000/mes más con Plan Esencial.`

- [ ] **Step 17.5: Repetir para tab `suplementacion` (línea ~1948)**

Misma estructura, con copy:
- title: `Tu plan no incluye suplementación`
- body: `El protocolo de suplementación con horarios viene en Plan Esencial. Súmalo desde $84.000/mes más.`

- [ ] **Step 17.6: Tabs `ciclo` y `bloodwork` ya tienen su propio bloqueo Elite. Confirmar que el `isTabLocked` actualizado mantiene el comportamiento previo para esas tabs**

Validación manual: el bloque `v-else-if="activeTab === 'ciclo'"` y `v-else-if="activeTab === 'bloodwork'"` mantienen su lógica original (ya tenían lock para non-elite).

- [ ] **Step 17.7: Agregar estilos del TabLockUpsell en `<style>` de PlanViewer.vue**

Localizar la sección `<style scoped>` o `<style>` al final del archivo. Si no existe, crear `<style scoped>` antes del cierre `</template>`. Agregar:

```css
.tab-lock-upsell {
  max-width: 480px;
  margin: 3rem auto;
  padding: 2.2rem 1.6rem;
  text-align: center;
  background: var(--wc-bg-secondary, #111);
  border: 1px solid var(--wc-border, rgba(255,255,255,0.08));
  border-radius: 14px;
}

.tab-lock-upsell-icon {
  font-size: 2.4rem;
  margin-bottom: 0.8rem;
  opacity: 0.7;
}

.tab-lock-upsell-title {
  font-family: var(--font-display, 'Oswald', 'Bebas Neue', sans-serif);
  font-weight: 700;
  font-size: 1.4rem;
  margin: 0 0 0.6rem;
  color: var(--wc-text, #fff);
  letter-spacing: -0.01em;
}

.tab-lock-upsell-body {
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  line-height: 1.55;
  color: var(--wc-text-muted, rgba(255,255,255,0.72));
  margin: 0 0 1.4rem;
}

.tab-lock-upsell-cta {
  display: inline-flex;
  align-items: center;
  padding: 0.75rem 1.4rem;
  background: var(--wc-accent, #DC2626);
  color: #fff;
  font-family: var(--font-data, 'Barlow', sans-serif);
  font-weight: 600;
  font-size: 0.92rem;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 10px;
  transition: filter 180ms ease;
}

.tab-lock-upsell-cta:hover {
  filter: brightness(1.1);
}
```

- [ ] **Step 17.8: Build assets**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && npm run build 2>&1 | tail -15
```

Expected: build exitoso.

---

### Task 18: PlanViewer.vue — `setTab` permitir click en tabs bloqueadas

**Files:**
- Modify: `resources/js/vue/pages/Client/PlanViewer.vue:388-395`

- [ ] **Step 18.1: Reemplazar función `setTab`**

Localizar el bloque (línea ~388):
```javascript
function setTab(key) {
  if (!isTabLocked(key)) {
    activeTab.value = key;
    if (key === 'habitos' && habitsLive.value === null) {
      fetchHabits();
    }
  }
}
```

Reemplazar con:
```javascript
function setTab(key) {
  // Permitir activar la tab incluso si está locked — el contenido renderiza TabLockUpsell.
  // Esto es upsell educativo (decisión P11 del spec).
  activeTab.value = key;
  if (key === 'habitos' && !isTabLocked(key) && habitsLive.value === null) {
    fetchHabits();
  }
}
```

- [ ] **Step 18.2: Verificar que el tab visualmente sigue mostrando candado para tabs locked**

El template existente (~línea 1027) ya pinta `<span v-if="isTabLocked(tab.key)" class="ml-1 text-xs">🔒</span>`. Eso se mantiene — el cambio solo permite hacer click.

---

## Phase 5 — Servicios de mantenimiento (lock + auto-renewal)

### Task 19: PlanLockService — `isMonthlyPlan`

**Files:**
- Modify: `app/Services/PlanLockService.php:55`

- [ ] **Step 19.1: Editar línea 55**

Reemplazar:
```php
        return in_array($this->clientPlanValue($client), ['esencial', 'metodo', 'elite'], true);
```

Con:
```php
        return in_array($this->clientPlanValue($client), ['esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo'], true);
```

- [ ] **Step 19.2: Actualizar el docblock de `isMonthlyPlan` (líneas 49-52) y el de `getActivePlan` (línea 32) para reflejar los 5 planes mensuales**

Reemplazar el docblock de `isMonthlyPlan`:

```php
    /**
     * ¿El cliente tiene un plan mensual sujeto al lock de 30 días?
     * Aplica a: esencial, metodo, elite, entreno_solo, nutricion_solo.
     * NO aplica a: rise (one-time 30 días), presencial (rango), trial (3 días).
     */
```

- [ ] **Step 19.3: Smoke test parsing**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php -l app/Services/PlanLockService.php
```

Expected: `No syntax errors detected`.

---

### Task 20: AutoRenewalCommand — `$monthlyPlans`

**Files:**
- Modify: `app/Console/Commands/AutoRenewalCommand.php:60`

- [ ] **Step 20.1: Editar línea 60**

Reemplazar:
```php
        $monthlyPlans = ['esencial', 'metodo', 'elite'];
```

Con:
```php
        $monthlyPlans = ['esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo'];
```

- [ ] **Step 20.2: Smoke test parsing**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php -l app/Console/Commands/AutoRenewalCommand.php
```

Expected: `No syntax errors detected`.

---

### Task 21: PlanTicketExportService — verificación

**Files:**
- Read first: `app/Services/PlanTicketExportService.php:355`

- [ ] **Step 21.1: Inspeccionar el contexto de la línea 355**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && sed -n '340,380p' app/Services/PlanTicketExportService.php
```

Determinar si los archivos `plan-esencial.md`, `plan-metodo.md`, `plan-elite.md` existen físicamente en algún storage del proyecto (`storage/`, `resources/`, etc.).

- [ ] **Step 21.2: Si los archivos existen y se leen, crear stubs para los 2 nuevos**

Buscar:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && find . -name "plan-esencial.md" -not -path "./node_modules/*" 2>/dev/null
```

- Si los archivos existen físicamente: crear `plan-entreno-solo.md` y `plan-nutricion-solo.md` con contenido stub (1 párrafo describiendo el plan + referencia al MD 28 del SISTEMA-CREACION-PLANES). Luego actualizar el array de línea 355.
- Si NO existen físicamente: solo agregar las claves al array para mantener consistencia, sin crear archivos. Verificar que el código no `file_get_contents()` esos archivos sin guard.

- [ ] **Step 21.3: Editar línea 355 si la modificación es segura**

Si la inspección de Step 21.1 muestra que el array es solo metadata (no se lee del filesystem), reemplazar:

```php
                'plan_guides' => ['plan-esencial.md', 'plan-metodo.md', 'plan-elite.md'],
```

Con:
```php
                'plan_guides' => ['plan-esencial.md', 'plan-metodo.md', 'plan-elite.md', 'plan-entreno-solo.md', 'plan-nutricion-solo.md'],
```

- [ ] **Step 21.4: Smoke test parsing**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php -l app/Services/PlanTicketExportService.php
```

Expected: `No syntax errors detected`.

---

## Phase 6 — SISTEMA-CREACION-PLANES (E:\)

### Task 22: 00-INDEX.md — actualizar

**Files:**
- Modify: `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\00-INDEX.md`

- [ ] **Step 22.1: Localizar la tabla "TIPOS DE `plan_type` EN `assigned_plans`" y agregar nota de doble namespace antes de la tabla**

Buscar la línea con `## TIPOS DE \`plan_type\` EN \`assigned_plans\``. Justo debajo del título y antes de "Valores válidos", insertar:

```markdown
> ⚠️ **DOBLE NAMESPACE — leer con atención:**
> El string `plan_type` significa cosas distintas según la columna:
>
> - **`clients.plan`** → **tier comercial** que pagó el cliente: `esencial`, `metodo`, `elite`, `entreno_solo`, `nutricion_solo`, `rise`, `presencial`, `trial`.
> - **`assigned_plans.plan_type`** → **tipo de contenido** del plan asignado: `entrenamiento`, `nutricion`, `suplementacion`, `habitos`, `ciclo_hormonal`, `bloodwork`.
>
> Un mismo cliente con `clients.plan = 'esencial'` recibe **2 filas** en `assigned_plans` (una con `plan_type='entrenamiento'`, otra con `plan_type='nutricion'`). Un cliente con `clients.plan = 'entreno_solo'` recibe **1 sola fila** (`plan_type='entrenamiento'`). Ver MD 28 para reglas completas.
```

- [ ] **Step 22.2: Localizar "ORDEN DE LECTURA POR TIPO DE PLAN" y agregar 2 secciones**

Después de `### Plan Combinado (entrenamiento + nutrición)` y antes de `### Plan de Suplementación`, insertar:

```markdown
### Plan ENTRENO solo (vertical única — `clients.plan='entreno_solo'`)
1. `00` → `01` → `05` → **`28-PLANES-VERTICAL-UNICA.md`** (CRÍTICO)
2. `16a` (schema entrenamiento) → `20` (ejercicios detalle) → `21` (notas del coach)
3. `04` → `07` (cardio) → `08` (metodología)
4. `22` (templates listos)
5. `17` (montar en DB) — **solo 1 fila en `assigned_plans` con `plan_type='entrenamiento'`**
6. `11` (verificar UI) → `18` (checklist)
7. `09` (notificaciones — frecuencia ajustada para vertical única)

### Plan NUTRICIÓN solo (vertical única — `clients.plan='nutricion_solo'`)
1. `00` → `01` → `05` → **`28-PLANES-VERTICAL-UNICA.md`** (CRÍTICO)
2. `16b` (schema nutrición) → `21` (notas del coach)
3. `04`
4. `22` (templates listos)
5. `17` (montar en DB) — **solo 1 fila en `assigned_plans` con `plan_type='nutricion'`**
6. `12` (verificar UI) → `18` (checklist)
7. `09` (notificaciones — frecuencia ajustada para vertical única)
```

- [ ] **Step 22.3: Agregar referencia al MD 28 en la tabla de BLOQUE A o BLOQUE C**

En la sección "BLOQUE C — Reglas técnicas", agregar al final de la tabla:

```markdown
| 28 | `28-PLANES-VERTICAL-UNICA.md` ⭐ | **Reglas para planes ENTRENO solo y NUTRICIÓN solo** — vertical única, 1 fila en assigned_plans, intake reducido, tabs bloqueadas en cliente |
```

---

### Task 23: 04-REGLAS-POR-TIPO-DE-PLAN.md — actualizar

**Files:**
- Modify: `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\04-REGLAS-POR-TIPO-DE-PLAN.md`

- [ ] **Step 23.1: Actualizar tabla "MAPPING OFICIAL `plan_type` → LABEL"**

Localizar la tabla y agregar 2 filas después de `elite`:

```markdown
| `entreno_solo` ⭐ | **Entreno** | — (vertical única — solo 1 fila assigned_plans `plan_type='entrenamiento'`) |
| `nutricion_solo` ⭐ | **Nutrición** | — (vertical única — solo 1 fila assigned_plans `plan_type='nutricion'`) |
```

- [ ] **Step 23.2: Actualizar tabla "DURACIONES OFICIALES POR `plan_type`"**

Agregar después de `elite`:

```markdown
| `entreno_solo` | 4-8 (default 4) | 4 sem: 1 Adaptación + 2 Hipertrofia + 1 Peak (igual que esencial) |
| `nutricion_solo` | 4-8 (default 4) | Sin fases de entreno — usar `fase` para hito nutricional ej. "Definición / Mantenimiento / Volumen" |
```

- [ ] **Step 23.3: Actualizar TABLA RESUMEN**

Agregar después de `Cardio standalone`:

```markdown
| Entreno solo (vertical) | 4 semanas | ✅ JSON | `assigned_plans` con `plan_type='entrenamiento'` (1 fila) | Solo entreno, sin tips de comida |
| Nutrición solo (vertical) | 4 semanas | ✅ JSON | `assigned_plans` con `plan_type='nutricion'` (1 fila) | Solo nutrición, sin recordatorios de cardio |
```

- [ ] **Step 23.4: Agregar sección "7. PLAN ENTRENO SOLO" antes de "REGLA MAESTRA — IDENTIFICAR EL TIPO"**

```markdown
## 7. PLAN ENTRENO SOLO (vertical única)

`clients.plan = 'entreno_solo'`. El cliente paga $170k/mes (con promo Mayo, $200k original) por un plan SOLO de entrenamiento con coach humano y ajuste mensual. **NO** incluye nutrición, hábitos, suplementación, ciclo hormonal ni bloodwork.

### 7.1 Intake mínimo

Igual que **Plan de Entrenamiento** (sección 1.1) MENOS:
- ❌ NO preguntar alergias alimentarias
- ❌ NO preguntar presupuesto suplementos
- ❌ NO preguntar horario laboral (no se usa para timing de comidas)
- ❌ NO preguntar dieta actual

Lo que SÍ se mantiene:
- ✅ Sexo, edad, peso, estatura (necesarios para volumen y RIR adecuados)
- ✅ Días disponibles, lugar, equipamiento, lesiones
- ✅ Objetivo, nivel, experiencia

### 7.2 Qué crear

**1 fila** en `assigned_plans` con:
- `plan_type = 'entrenamiento'`
- `content` = JSON siguiendo schema 16a (entrenamiento canónico)
- `valid_from` = hoy
- `expires_at` = +30 días
- `active = true`
- Marcar inactive cualquier fila previa con `plan_type='entrenamiento'` y mismo `client_id`

### 7.3 Qué NO crear

- ❌ NO crear fila con `plan_type='nutricion'`
- ❌ NO crear fila con `plan_type='suplementacion'`
- ❌ NO crear fila con `plan_type='habitos'`
- ❌ NO sugerir suplementación en `notas_coach` (el cliente NO pagó por eso)
- ❌ NO sugerir comidas específicas en `notas_coach` (el cliente NO pagó por nutrición)

### 7.4 Coach message — incentivar upsell sin presionar

El `notas_coach` del JSON puede mencionar (al final, en último párrafo) que la nutrición es 50% de los resultados — pero NUNCA prescribir comidas. Sugerir explícitamente subir a Esencial:

> *"El entreno está listo. Recordá que el plan que tenés (Entreno) se enfoca solo en lo del gym. Si en algún momento querés sumar nutrición personalizada con macros, hablá conmigo y te paso a Plan Esencial — son solo $84.000 más al mes."*

### 7.5 Diferencias con Plan de Entrenamiento estándar

| Aspecto | Plan Entrenamiento (dentro de Esencial+) | Plan ENTRENO SOLO |
|---------|------------------------------------------|-------------------|
| `clients.plan` | `esencial` / `metodo` / `elite` | `entreno_solo` |
| Filas en `assigned_plans` | 2-3 (entrenamiento + nutricion + opcional suplementacion) | **1 sola** (entrenamiento) |
| Tabs visibles en `/client/plan` | Entrenamiento ✅ + Nutrición ✅ + Hábitos ✅ + ... | Solo Entrenamiento ✅, resto 🔒 con upsell |
| Notas del coach mencionan nutrición/hábitos | Sí (forman parte del sistema) | No (cliente no contrató eso) |

---

## 8. PLAN NUTRICIÓN SOLO (vertical única)

`clients.plan = 'nutricion_solo'`. El cliente paga $153k/mes (con promo Mayo, $180k original) por un plan SOLO de nutrición con coach humano y ajuste mensual. **NO** incluye entrenamiento, hábitos, suplementación, ciclo ni bloodwork.

### 8.1 Intake mínimo

Igual que **Plan Nutricional** (sección 2.1) MENOS:
- ❌ NO preguntar días disponibles (no entrena con WellCore)
- ❌ NO preguntar lugar (gym/casa)
- ❌ NO preguntar equipamiento
- ❌ NO preguntar lesiones (a menos que afecten dietas — ej. cirugía bariátrica, gastritis crónica, ERGE)

Lo que SÍ se mantiene:
- ✅ Peso, estatura, edad, sexo, nivel de actividad
- ✅ Objetivo, alergias, intolerancias
- ✅ País, presupuesto, horario laboral

### 8.2 Qué crear

**1 fila** en `assigned_plans` con:
- `plan_type = 'nutricion'`
- `content` = JSON siguiendo schema 16b (nutrición canónica)
- `valid_from` = hoy
- `expires_at` = +30 días
- `active = true`
- Marcar inactive cualquier fila previa con `plan_type='nutricion'` y mismo `client_id`

**Importante para el cálculo de macros:** preguntar al cliente si entrena en otro lugar (gym independiente, otro coach). Si entrena, usar factor de actividad correspondiente (Moderado 1.55 o Activo 1.725). Si NO entrena, usar Sedentario (1.2) o Ligero (1.375). Anotar en `notas_coach` qué se asumió.

### 8.3 Qué NO crear

- ❌ NO crear fila con `plan_type='entrenamiento'`
- ❌ NO crear fila con `plan_type='suplementacion'`
- ❌ NO crear fila con `plan_type='habitos'`
- ❌ NO sugerir rutinas o ejercicios en `notas_coach`

### 8.4 Coach message — incentivar upsell

> *"El plan nutricional está listo. Recordá que tenés el plan de Nutrición — el entreno no está incluido. Si entrenás en otro lugar, perfecto, sumá los macros que diseñamos a tu rutina. Si en algún momento querés que también te diseñe el entreno, te paso a Plan Esencial por $84.000 más al mes."*

### 8.5 Diferencias con Plan Nutricional estándar

| Aspecto | Plan Nutrición (dentro de Esencial+) | Plan NUTRICIÓN SOLO |
|---------|--------------------------------------|----------------------|
| `clients.plan` | `esencial` / `metodo` / `elite` | `nutricion_solo` |
| Filas en `assigned_plans` | 2-3 (entrenamiento + nutricion + opcional suplementacion) | **1 sola** (nutricion) |
| Tabs visibles en `/client/plan` | Entrenamiento ✅ + Nutrición ✅ + Hábitos ✅ + ... | Solo Nutrición ✅, resto 🔒 con upsell |
| Notas del coach mencionan entreno/hábitos | Sí (forman parte del sistema) | No (cliente no contrató eso) |
```

- [ ] **Step 23.5: Actualizar la tabla "REGLA MAESTRA — IDENTIFICAR EL TIPO"**

Agregar al final de la tabla:

```markdown
| "plan de entrenamiento solo", "solo entreno", "sin nutrición", "plan vertical entrenamiento" | **ENTRENO SOLO** (`entreno_solo`) |
| "plan de nutrición solo", "solo dieta", "solo macros", "plan vertical nutrición" | **NUTRICIÓN SOLO** (`nutricion_solo`) |
```

---

### Task 24: 23-NAMING-CANONICO-Y-ALIAS.md — actualizar

**Files:**
- Modify: `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\23-NAMING-CANONICO-Y-ALIAS.md`

- [ ] **Step 24.1: Actualizar entrada `plan_type` en tabla ROOT del JSON**

Localizar la fila:
```markdown
| `plan_type` | — | string enum | `esencial`, `metodo`, `elite`, `rise`, `presencial`, `trial` |
```

Reemplazar con:
```markdown
| `plan_type` | — | string enum | `esencial`, `metodo`, `elite`, `entreno_solo`, `nutricion_solo`, `rise`, `presencial`, `trial`. **OJO: este es `clients.plan` (tier comercial), NO confundir con `assigned_plans.plan_type` (contenido)** |
```

- [ ] **Step 24.2: Agregar nota destacada al inicio del documento**

Después del párrafo de la "REGLA" inicial, insertar:

```markdown
> ⚠️ **DOBLE NAMESPACE crítico:**
>
> El término `plan_type` aparece en dos contextos distintos del sistema:
>
> 1. **Tier comercial** (`clients.plan`, también opcionalmente replicado en JSON como `plan_type` root) — define QUÉ COMPRÓ el cliente. Valores: `esencial`, `metodo`, `elite`, `entreno_solo`, `nutricion_solo`, `rise`, `presencial`, `trial`.
> 2. **Tipo de contenido** (`assigned_plans.plan_type`) — define el TIPO DE PLAN ASIGNADO. Valores: `entrenamiento`, `nutricion`, `suplementacion`, `habitos`, `ciclo_hormonal`, `bloodwork`.
>
> No son lo mismo. El string `nutricion` puede estar en ambos lados. Cuando hagas queries o validaciones, asegurate de saber a cuál te referís.
```

---

### Task 25: 01-PASO-A-PASO.md — actualizar

**Files:**
- Modify: `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\01-PASO-A-PASO.md`

- [ ] **Step 25.1: Actualizar FASE 0 — "Tipo de plan solicitado"**

Localizar:
```markdown
- [ ] Tipo de plan solicitado: Entrenamiento / Nutrición / Combinado / Suplementación / RISE
```

Reemplazar con:
```markdown
- [ ] Tipo de plan solicitado: Entrenamiento / Nutrición / Combinado / Suplementación / RISE / **Entreno solo (vertical)** / **Nutrición solo (vertical)**
```

- [ ] **Step 25.2: Actualizar FASE 1 tabla "Según tipo de plan"**

Agregar 2 filas al final de la tabla:

```markdown
| **Entreno solo** (vertical única) | **`28-PLANES-VERTICAL-UNICA`**, `16a`, `20`, `07`, `08`, `11`, `22` |
| **Nutrición solo** (vertical única) | **`28-PLANES-VERTICAL-UNICA`**, `16b`, `12`, `22` |
```

- [ ] **Step 25.3: Actualizar FASE 4.5 — agregar nota crítica sobre vertical única**

Localizar la sección "### 4.5 Si es plan combinado, repetir para nutrición". Después del bloque actual, agregar:

```markdown
### 4.6 Si es plan de vertical única (entreno_solo o nutricion_solo)

**NO repetir el INSERT.** Solo se crea **1 fila** en `assigned_plans`:

- `entreno_solo` → 1 fila con `plan_type='entrenamiento'`
- `nutricion_solo` → 1 fila con `plan_type='nutricion'`

Marcar como `active=false` cualquier fila previa con el MISMO `plan_type` y `client_id`. NO marcar inactive las del otro tipo (ej. si vino de un plan combinado anterior, las filas del otro tipo quedan tal como estaban — el `expires_at` natural las desactiva con el tiempo).

**Verificación post-INSERT:** verificar que `assigned_plans` tiene la fila esperada y que `clients.plan` quedó en `'entreno_solo'` o `'nutricion_solo'`. Si el flujo de checkout no setea `clients.plan` automáticamente, hacerlo manualmente:

```php
\App\Models\Client::find($clientId)->update(['plan' => 'entreno_solo']);
```

Ver MD 28 para reglas completas y diferencias con plan combinado.
```

---

### Task 26: Crear `28-PLANES-VERTICAL-UNICA.md`

**Files:**
- Create: `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\28-PLANES-VERTICAL-UNICA.md`

- [ ] **Step 26.1: Crear archivo con contenido completo**

Crear archivo `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\28-PLANES-VERTICAL-UNICA.md` con:

```markdown
# 28 — PLANES VERTICAL ÚNICA (Entreno solo · Nutrición solo)

**Lanzados:** 2026-05-04
**Slugs DB:** `entreno_solo` / `nutricion_solo` (en `clients.plan`)

Documento dedicado a los 2 planes nuevos que cubren UNA sola vertical (entrenamiento o nutrición), no el sistema completo.

---

## 1. Qué son

| Plan | Slug DB | Precio mensual (promo Mayo) | Original | Coach humano | Ajuste |
|------|---------|----------------------------:|---------:|:------------:|--------|
| ENTRENO | `entreno_solo` | **$170.000 COP** | $200.000 | ✅ | Mensual |
| NUTRICIÓN | `nutricion_solo` | **$153.000 COP** | $180.000 | ✅ | Mensual |

Ambos llevan **el mismo nivel de coach humano** que Esencial (el plan completo más barato), pero cubren UNA sola vertical en vez de las tres (entreno + nutrición + hábitos).

USD:
- ENTRENO: $42 (promo) / $49 (original)
- NUTRICIÓN: $37 (promo) / $44 (original)

Períodos disponibles (mismas mecánicas que Esencial/Método/Elite):
- Mensual (precio listado arriba)
- Trimestral (-10%): ENTRENO $153k/mes · NUTRICIÓN $137,7k/mes
- Anual (-20%): ENTRENO $136k/mes · NUTRICIÓN $122,4k/mes

---

## 2. Cuándo aplican

Ideal para:
- Cliente que **ya tiene la otra vertical resuelta** (ej. tiene nutricionista personal, o entrena con otro coach pero quiere cambiar nutrición)
- Cliente que quiere **probar el sistema** con menor compromiso económico antes de subir a Esencial
- Cliente con **objetivo muy específico de una vertical** (ej. atleta con plan nutricional independiente quiere solo programa de fuerza)

NO ideal para:
- Cliente principiante que necesita guía completa → recomendar Esencial
- Cliente con múltiples objetivos (estética + salud + rendimiento) → recomendar Método o Elite

---

## 3. Mapping crítico clients.plan ↔ assigned_plans.plan_type

```
clients.plan = 'entreno_solo'     →  1 fila assigned_plans con plan_type='entrenamiento'
clients.plan = 'nutricion_solo'   →  1 fila assigned_plans con plan_type='nutricion'
clients.plan = 'esencial'         →  2 filas (entrenamiento + nutricion)
clients.plan = 'metodo'           →  2-3 filas (entrenamiento + nutricion + suplementacion opcional)
clients.plan = 'elite'            →  3-5 filas (todos los anteriores + ciclo + bloodwork)
```

**REGLA DE ORO:** un cliente con plan de vertical única **NUNCA** debe tener una fila activa de la otra vertical. Si la tiene de un plan anterior (downgrade), marcarla `active=false`.

---

## 4. Intake reducido

### 4.1 ENTRENO SOLO

**SÍ preguntar:**
- Sexo, edad, peso, estatura
- Días disponibles a la semana (3, 4, 5, 6)
- Tiempo por sesión
- Lugar: Gym / Casa / Híbrido
- Objetivo: Hipertrofia / Fuerza / Resistencia / Recomp / Mantenimiento
- Nivel: Principiante / Intermedio / Avanzado
- Equipamiento (si es casa)
- Lesiones / contraindicaciones físicas

**NO preguntar:**
- ❌ Alergias alimentarias
- ❌ Presupuesto suplementos
- ❌ Horario laboral (no se usa para timing de comidas)
- ❌ Dieta actual / experiencia con macros
- ❌ Comidas al día / comer fuera

### 4.2 NUTRICIÓN SOLO

**SÍ preguntar:**
- Sexo, edad, peso, estatura
- Nivel de actividad: Sedentario / Ligero / Moderado / Activo / Muy activo
- Si entrena en otro lado (afecta GET) y con qué frecuencia/intensidad
- Objetivo: Aumento / Pérdida / Recomp / Mantenimiento
- Alergias e intolerancias
- Alimentos NO consume (vegano, vegetariano, religiones)
- País, presupuesto, horario laboral

**NO preguntar:**
- ❌ Días disponibles para entrenar con WellCore (no entrena con WellCore)
- ❌ Lugar de entreno (gym/casa)
- ❌ Equipamiento
- ❌ Lesiones físicas (a menos que afecten dietas — ej. cirugía bariátrica, ERGE, gastritis crónica)

---

## 5. Tabs que ve el cliente en `/client/plan`

| `clients.plan` | Entreno | Hábitos | Nutrición | Suplementos | Ciclo | Bloodwork |
|---------------|:---:|:---:|:---:|:---:|:---:|:---:|
| `esencial` | ✅ | ✅ | ✅ | ✅ | 🔒 | 🔒 |
| `metodo` | ✅ | ✅ | ✅ | ✅ | 🔒 | 🔒 |
| `elite` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **`entreno_solo`** ⭐ | ✅ | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 |
| **`nutricion_solo`** ⭐ | 🔒 | 🔒 | ✅ | 🔒 | 🔒 | 🔒 |

Las tabs 🔒 muestran un componente `<TabLockUpsell>` con CTA *"Suma X con Plan Esencial desde $84.000/mes más"*. El click sí permite navegar a la tab (para ver el upsell), pero el contenido es la card de upsell, no el plan real.

---

## 6. Coach message — incentivar upsell sin presionar

### 6.1 Para ENTRENO SOLO

Cierre del `notas_coach`:

> *"El plan está listo. Recordá que el plan que tenés (Entreno) se enfoca solo en lo del gym — la nutrición no está incluida. Si en algún momento querés sumar plan de comidas con macros y ajuste mensual, hablá conmigo y te paso a Esencial. Son $84k más al mes y te llevás el sistema completo."*

### 6.2 Para NUTRICIÓN SOLO

Cierre del `notas_coach`:

> *"El plan nutricional está listo. Tu plan (Nutrición) se enfoca solo en macros y comidas. Si entrenás en otro lugar, perfecto, sumá los macros que diseñamos a tu rutina. Si querés que también te diseñe el entreno, te paso a Esencial por $84k más al mes."*

**NO HACER:**
- ❌ Presionar al cliente con discount tácticas
- ❌ Decir que el plan es "incompleto" o "limitado" — es un plan VÁLIDO en sí mismo
- ❌ Mencionar features de la otra vertical en el plan (no prescribir comidas en plan ENTRENO; no prescribir entrenamiento en plan NUTRICIÓN)

---

## 7. Notificaciones

Frecuencia ajustada para vertical única (ver MD 09 para detalles generales):

### 7.1 ENTRENO SOLO
- ✅ Recordatorios de entreno los días programados
- ✅ Check-in semanal (mediciones + foto + sensaciones)
- ✅ Recordatorio de cardio post-pesas
- ❌ NO enviar tips de comida
- ❌ NO enviar recordatorios de macros / hidratación específica

### 7.2 NUTRICIÓN SOLO
- ✅ Recordatorios de comidas (timing por comida)
- ✅ Recordatorio de hidratación diaria
- ✅ Check-in semanal (peso + adherencia + sensaciones)
- ❌ NO enviar recordatorios de cardio
- ❌ NO enviar recordatorios de entreno

---

## 8. NO HACER — checklist de errores comunes

1. ❌ Crear 2 filas en `assigned_plans` (debe ser solo 1)
2. ❌ Olvidar setear `clients.plan` en el slug correcto (`entreno_solo` o `nutricion_solo`)
3. ❌ Incluir suplementación en el plan (NO la pagaron)
4. ❌ Mencionar la vertical NO contratada en `notas_coach` con prescripciones
5. ❌ Mezclar tier comercial con contenido en queries (ej. `where('plan', 'nutricion')` puede traer planes nutricionales de Esencial+ Y planes nutricion_solo si la columna es ambigua — usar `clients.plan` vs `assigned_plans.plan_type` correctamente)
6. ❌ Enviar notificaciones de la vertical no contratada
7. ❌ Forzar al cliente a hacer check-in de la vertical no contratada
8. ❌ Asumir que el cliente tiene plan de hábitos asignado (NO lo tiene)

---

## 9. Diferencias clave con plan combinado

| Aspecto | Combinado (Esencial+) | Vertical única |
|---------|----------------------|----------------|
| Slug en `clients.plan` | `esencial`, `metodo`, `elite` | `entreno_solo`, `nutricion_solo` |
| Filas activas en `assigned_plans` | 2-5 (depende del tier) | **1 sola** |
| Tabs visibles en cliente | 4-6 desbloqueadas | 1 desbloqueada + 5 con 🔒 upsell |
| Coach message | Conecta entreno + nutrición + hábitos | Foco en una sola vertical, deja puerta abierta a upsell |
| Intake | Completo (entreno + nutrición + lifestyle) | Reducido a la vertical específica |
| Notificaciones | Cubren las 3 verticales | Solo la vertical contratada |
| Precio mensual (promo) | $254k/$339k/$466k | $170k/$153k |
| Cliente potencial | Quien quiere sistema completo | Quien tiene la otra vertical resuelta o quiere probar |

---

## 10. Templates JSON copy-paste

### 10.1 Template mínimo viable — ENTRENO SOLO

```json
{
  "plan_type": "entreno_solo",
  "titulo": "Plan de Entrenamiento — {NOMBRE_CLIENTE}",
  "objetivo": "Hipertrofia con foco en glúteos y posterior",
  "metodologia": "Upper/Lower 4 días",
  "frecuencia": "4 días/semana",
  "duracion_semanas": 4,
  "fecha_inicio": "2026-05-06",
  "notas_coach": "{NOMBRE}, este plan está diseñado para que entrenés 4 veces por semana, con sesiones de ~75 min. Cada semana sube intensidad y baja reps — eso fuerza adaptación. La nutrición es 50% de los resultados; cuando estés listo para sumarla, te paso a Plan Esencial por $84k más al mes.",
  "tips": [
    "Calienta 5-7 min antes de cada sesión — no te saltes esto",
    "Anota peso y reps reales con Voice Logger en cada serie",
    "Si una sesión la haces a casa, usa las variaciones que vienen abajo del ejercicio",
    "Cardio post-pesas: 30 min caminadora a 10% incline, intensidad RPE 5"
  ],
  "semanas": [
    {
      "numero": 1,
      "fase": "Adaptación",
      "dias": [
        {
          "nombre": "Lunes — Upper A",
          "tipo": "upper",
          "calentamiento": "5 min remo + activación banda elástica (15 reps × 3 movimientos)",
          "ejercicios": [
            {
              "nombre": "Press banca con barra",
              "series": 3,
              "repeticiones": "10-12",
              "descanso": "90s",
              "rir": 3,
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-barra.gif",
              "notas": "Codos a 45°, no rebotes la barra en el pecho"
            }
          ]
        }
      ]
    }
  ]
}
```

### 10.2 Template mínimo viable — NUTRICIÓN SOLO

```json
{
  "plan_type": "nutricion_solo",
  "titulo": "Plan Nutricional — {NOMBRE_CLIENTE}",
  "objetivo": "Recomposición — bajar grasa preservando músculo",
  "objetivo_cal": 2200,
  "macros": {
    "proteina_g": 145,
    "carbohidratos_g": 240,
    "grasas_g": 65
  },
  "hidratacion": {
    "agua_minima_litros": 3.0
  },
  "fecha_inicio": "2026-05-06",
  "notas_coach": "{NOMBRE}, este plan tiene 2200 kcal con macros en recomposición — proteína alta (145g) para preservar músculo, carbos moderados, grasas necesarias. 5 comidas al día con 3 opciones por plato (cambias receta con un click). El entreno no está incluido en tu plan — si entrenás en otro lugar, perfecto, los macros se ajustan. Si querés que también te diseñe el entreno, te paso a Esencial por $84k más al mes.",
  "tips": [
    "Pesa los alimentos en gramos los primeros 14 días — después ya tienes el ojo entrenado",
    "Hidratación: 3L mínimo, sumando agua + infusiones sin azúcar",
    "Si comes fuera: prioriza proteína en gramos visuales (palma de mano = 100g)",
    "Cheat meal: 1 vez por semana, 1 comida (no día) — preferiblemente post-entreno"
  ],
  "comidas": [
    {
      "nombre": "Desayuno",
      "tipo": "desayuno",
      "hora": "7:00 AM",
      "calorias": 500,
      "macros": { "proteina_g": 35, "carbohidratos_g": 60, "grasas_g": 12 },
      "alimentos": [
        { "nombre": "Avena", "cantidad": "60g" },
        { "nombre": "Clara de huevo", "cantidad": "4 unidades" },
        { "nombre": "Banano", "cantidad": "1 unidad mediana" }
      ]
    }
  ]
}
```

---

## 11. Validación pre-INSERT específica para vertical única

Además del checklist de MD 23, verificar:

- [ ] `clients.plan` está en `'entreno_solo'` o `'nutricion_solo'` (no `'esencial'` u otro)
- [ ] Solo SE VA A INSERTAR 1 fila en `assigned_plans`, no 2
- [ ] El `plan_type` de la fila a insertar coincide con la vertical:
  - `entreno_solo` → `plan_type='entrenamiento'`
  - `nutricion_solo` → `plan_type='nutricion'`
- [ ] `notas_coach` NO prescribe contenido de la vertical no contratada
- [ ] No se va a crear fila de `suplementacion` ni `habitos` ni nada más

Si algún check falla, NO hacer INSERT. Diagnosticar primero.

---

**Próximo paso después de leer este MD:** ir al template JSON apropiado (16a o 16b según vertical) y construir el plan respetando las reglas de §4 (intake), §6 (notas_coach) y §11 (validación pre-INSERT).
```

- [ ] **Step 26.2: Verificar que el archivo se creó correctamente**

Run:
```bash
ls -la "E:/WELLCORE FITNESS PLATAFORMA/SISTEMA-CREACION-PLANES/28-PLANES-VERTICAL-UNICA.md"
wc -l "E:/WELLCORE FITNESS PLATAFORMA/SISTEMA-CREACION-PLANES/28-PLANES-VERTICAL-UNICA.md"
```

Expected: archivo existe, ≥250 líneas.

---

### Task 27: Memory updates

**Files:**
- Modify: `~/.claude/projects/C--Users-GODSF-Herd-wellcore-laravel/memory/reference_plan_creation_system.md`
- Create: `~/.claude/projects/C--Users-GODSF-Herd-wellcore-laravel/memory/project_planes_vertical_unica.md`
- Modify: `~/.claude/projects/C--Users-GODSF-Herd-wellcore-laravel/memory/MEMORY.md`

- [ ] **Step 27.1: Leer memory existente**

Run:
```bash
cat "C:/Users/GODSF/.claude/projects/C--Users-GODSF-Herd-wellcore-laravel/memory/reference_plan_creation_system.md"
```

- [ ] **Step 27.2: Actualizar `reference_plan_creation_system.md`**

Reemplazar el contenido (manteniendo frontmatter y agregando los 2 nuevos tipos):

```markdown
---
name: Sistema de Creación de Planes — referencia
description: 11 MDs en E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\ que SIEMPRE leer antes de crear cualquier plan. Cubre todos los tipos incluido vertical única.
type: reference
---

# Sistema de Creación de Planes (E:\)

Antes de crear CUALQUIER plan (entrenamiento, nutrición, combinado, cardio, RISE, recomposición, **entreno solo, nutrición solo**), **leer SIEMPRE primero** los MDs ubicados en:

`E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\`

## Orden recomendado

1. **`00-INDEX.md`** — orientación general
2. **`01-PASO-A-PASO.md`** — workflow oficial
3. **`05-LENGUAJE-Y-VOZ.md`** — voz Anderson Ardila
4. **`04-REGLAS-POR-TIPO-DE-PLAN.md`** — reglas por tipo
5. **`23-NAMING-CANONICO-Y-ALIAS.md`** — naming
6. **`28-PLANES-VERTICAL-UNICA.md`** ⭐ — para entreno_solo / nutricion_solo
7. Schema correspondiente: `16a` (entreno), `16b` (nutrición), `16c` (suplementación), `16d` (hábitos/ciclo/bloodwork)

## Tipos de plan soportados

| Tipo | `clients.plan` | Filas en `assigned_plans` |
|------|---------------|---------------------------|
| Esencial / Método / Elite | `esencial`/`metodo`/`elite` | 2-5 (combinado) |
| RISE | `rise` | 1 (entrenamiento + nutrición + hábitos en 1 JSON) |
| Presencial | `presencial` | 2 (combinado) |
| Trial | `trial` | 1 (entrenamiento) |
| **ENTRENO solo** ⭐ | `entreno_solo` | **1** (solo entrenamiento) |
| **NUTRICIÓN solo** ⭐ | `nutricion_solo` | **1** (solo nutrición) |

## Doble namespace `plan_type` (CRÍTICO)

- `clients.plan` → tier comercial
- `assigned_plans.plan_type` → tipo de contenido

NO confundir.

**No crear planes sin haber consultado este sistema.** Evita errores repetidos de formato, voz, GIFs, metodología y mappings DB.
```

- [ ] **Step 27.3: Crear `project_planes_vertical_unica.md`**

Crear con contenido:

```markdown
---
name: Planes vertical única lanzados
description: ENTRENO solo y NUTRICIÓN solo lanzados 2026-05-04. Slugs entreno_solo/nutricion_solo. Sistema-Creacion-Planes ya soporta los 2 nuevos tipos via MD 28.
type: project
---

# Planes ENTRENO solo + NUTRICIÓN solo

**Lanzados:** 2026-05-04
**Spec:** `docs/superpowers/specs/2026-05-04-planes-entreno-nutricion-design.md`
**Plan:** `docs/superpowers/plans/2026-05-04-planes-entreno-nutricion.md`

## Estado

- ✅ Diseño + spec aprobados (2026-05-04)
- ✅ Sistema-Creacion-Planes (E:\) actualizado con MD 28 + updates a 00, 01, 04, 23
- ✅ Memory `reference_plan_creation_system.md` actualizado para reflejar nuevos tipos
- 🔄 Implementación en progreso (ver plan)

## Slugs DB

- `clients.plan = 'entreno_solo'` → 1 fila `assigned_plans` con `plan_type='entrenamiento'`
- `clients.plan = 'nutricion_solo'` → 1 fila `assigned_plans` con `plan_type='nutricion'`

**Why:** Permite ofrecer puerta de entrada de bajo compromiso ($170k/$153k vs $254k de Esencial). Upsell natural a Esencial por solo $84k más al mes.

**How to apply:** Al pedir crear plan para un cliente, primero verificar `clients.plan`. Si es `entreno_solo` o `nutricion_solo`, aplicar reglas de MD 28 (intake reducido, 1 sola fila, coach message con upsell sin presión, sin contenido de la vertical no contratada).

## Pricing

| Plan | Mensual (promo) | Trimestral (-10%) | Anual (-20%) |
|------|----------------:|-------------------:|--------------:|
| ENTRENO ($200k orig) | **$170k** | $153k/mes | $136k/mes |
| NUTRICIÓN ($180k orig) | **$153k** | $137,7k/mes | $122,4k/mes |

Promo Mayo 2026 (-15%) hasta 2026-05-31.

## Tabs bloqueadas en `/client/plan`

- `entreno_solo`: solo Entrenamiento ✅, resto 🔒 con upsell a Esencial
- `nutricion_solo`: solo Nutrición ✅, resto 🔒 con upsell a Esencial
```

- [ ] **Step 27.4: Actualizar índice `MEMORY.md`**

Editar `~/.claude/projects/C--Users-GODSF-Herd-wellcore-laravel/memory/MEMORY.md` y agregar línea (manteniendo orden alfabético/cronológico del archivo):

```
- [project_planes_vertical_unica.md](project_planes_vertical_unica.md) — ENTRENO/NUTRICIÓN solo lanzados 2026-05-04, slugs entreno_solo/nutricion_solo, MD 28 actualizado
```

---

## Phase 7 — Validación funcional + build + commit

### Task 28: Smoke test local — `/planes`

**Files:** ninguno (solo verificación)

- [ ] **Step 28.1: Build production**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && npm run build
```

Expected: build exitoso. Output incluye nuevos asset hashes para `app.css` (con tiers-simple).

- [ ] **Step 28.2: Levantar servidor**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan view:clear && php artisan config:clear && php artisan serve --port=8000
```

En otra terminal:
```bash
curl -s http://127.0.0.1:8000/planes -o /tmp/planes.html
echo "--- Cards completas ---"
grep -E "t-card-(esencial|metodo|elite)" /tmp/planes.html | head -3
echo "--- Cards simples ---"
grep -E "t-card-simple-(entreno_solo|nutricion_solo)" /tmp/planes.html | head -3
echo "--- Section heading ---"
grep -E "PLANES SIMPLES" /tmp/planes.html | head -1
echo "--- JSON-LD ---"
grep -E "Entreno|Nutricion" /tmp/planes.html | grep -E "@type" | head -2
```

Expected: las 3 cards completas + 2 cards simples + heading + 2 entradas JSON-LD presentes.

- [ ] **Step 28.3: Verificar en browser visual**

Abrir `http://127.0.0.1:8000/planes` en Chrome. Verificar:
- 3 cards (Esencial / Método / Elite) en sección superior
- Divider eyebrow "PLANES SIMPLES · UNA VERTICAL" debajo
- 2 cards (ENTRENO / NUTRICIÓN) en grid 2-cols (desktop) o stacked (mobile via DevTools)
- Toggle billing al cambiar (mensual / trimestral / anual) actualiza precios de las 5 cards
- CTA de cards simples lleva a `/pagar?plan=entreno_solo&period=mensual` (verificar en HTML inspector)

---

### Task 29: Smoke test `/pagar` para los 2 nuevos planes

- [ ] **Step 29.1: Test entreno_solo**

Abrir `http://127.0.0.1:8000/pagar?plan=entreno_solo` en browser.

Verificar:
- Step 2 visible (no Step 1) — ya seleccionó plan via query param
- "Entreno" aparece como nombre del plan
- Precio mostrado: $170.000

- [ ] **Step 29.2: Test nutricion_solo**

Abrir `http://127.0.0.1:8000/pagar?plan=nutricion_solo`.

Mismas verificaciones, precio $153.000.

- [ ] **Step 29.3: Test renovación**

Si tienes credenciales de un cliente test con `plan='entreno_solo'`, login + abrir `/renovar`. Verificar que prefilla con plan correcto.

---

### Task 30: Smoke test `/client/plan` — tabs lock

- [ ] **Step 30.1: Crear cliente test con `entreno_solo`**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="
\$c = \App\Models\Client::firstOrCreate(
  ['email' => 'test_entreno@wellcore.test'],
  ['name' => 'Test Entreno', 'phone' => '+57 300 0000001', 'plan' => 'entreno_solo']
);
echo 'Cliente test ID: ' . \$c->id . PHP_EOL;
echo 'Plan: ' . \$c->plan . PHP_EOL;
"
```

Expected: cliente creado o encontrado, plan='entreno_solo'.

- [ ] **Step 30.2: Asignar plan de entrenamiento al cliente test**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && php artisan tinker --execute="
\$c = \App\Models\Client::where('email','test_entreno@wellcore.test')->first();
\App\Models\AssignedPlan::create([
  'client_id' => \$c->id,
  'plan_type' => 'entrenamiento',
  'content' => json_encode([
    'plan_type' => 'entreno_solo',
    'titulo' => 'Plan Test',
    'objetivo' => 'Test',
    'metodologia' => 'Upper/Lower',
    'frecuencia' => '4 dias',
    'duracion_semanas' => 4,
    'fecha_inicio' => date('Y-m-d'),
    'notas_coach' => 'Test plan',
    'semanas' => [['numero'=>1,'fase'=>'Adaptación','dias'=>[['nombre'=>'Lunes','ejercicios'=>[['nombre'=>'Press','series'=>3,'repeticiones'=>'10','descanso'=>'90s']]]]]]
  ]),
  'assigned_by' => 7,
  'valid_from' => date('Y-m-d'),
  'expires_at' => date('Y-m-d', strtotime('+30 days')),
  'active' => true,
]);
echo 'Plan asignado.' . PHP_EOL;
"
```

- [ ] **Step 30.3: Login como cliente test y verificar dashboard**

Manual via browser:
1. Logout si hay sesión
2. Login con `test_entreno@wellcore.test` (password: setear primero o usar impersonación admin)
3. Ir a `/client/plan`
4. Verificar:
   - Tab "Entrenamiento" desbloqueada con contenido del plan
   - Tabs "Hábitos", "Nutrición", "Suplementos" con candado 🔒
   - Click en "Nutrición" muestra TabLockUpsell con CTA "Ver Plan Esencial"
   - Click en "Ver Plan Esencial" navega a `/planes#tier-cards`

- [ ] **Step 30.4: Repetir para `nutricion_solo`**

Repetir Steps 30.1-30.3 con email `test_nutricion@wellcore.test`, plan `nutricion_solo`, asignando un plan de tipo `nutricion`.

Verificar que solo "Nutrición" está desbloqueada.

---

### Task 31: Verify acceptance criteria del spec

**Files:** referencia `docs/superpowers/specs/2026-05-04-planes-entreno-nutricion-design.md` §10

- [ ] **Step 31.1: Recorrer cada item del checklist §10 del spec y marcarlo**

Abrir el spec y verificar cada acceptance criteria:

**Página `/planes`:**
- [ ] Cards aparecen entre TierCards y Comparador
- [ ] Precios reaccionan al toggle global
- [ ] CTAs llevan al checkout correcto
- [ ] Lighthouse no regresión (correr Chrome DevTools Lighthouse)
- [ ] JSON-LD incluye 5 planes
- [ ] Sticky CTA bottom mobile muestra nombre correcto

**Checkout `/pagar`:**
- [ ] Acepta `?plan=entreno_solo` y `?plan=nutricion_solo`
- [ ] Step 2 valida correctamente
- [ ] Wompi se prepara con monto correcto

**Inscripción `/inscripcion`:**
- [ ] Form muestra 5 opciones en Step 0
- [ ] `entreno_solo` salta Step 5
- [ ] `nutricion_solo` salta Steps 2-4
- [ ] POST API valida y persiste

**Dashboard:**
- [ ] `entreno_solo` solo Entrenamiento desbloqueada
- [ ] `nutricion_solo` solo Nutrición desbloqueada
- [ ] Click en tab bloqueada muestra TabLockUpsell
- [ ] Header dinámico muestra label correcto

**Auto-renovación:**
- [ ] AutoRenewalCommand procesa los 2 nuevos planes (test dry-run)

**Sistema-Creacion-Planes:**
- [ ] MD 28 creado con contenido completo
- [ ] MDs 00, 01, 04, 23 actualizados
- [ ] Memory actualizado

**HabitLog check:**
- [ ] Cliente con `entreno_solo` o `nutricion_solo` no recibe error en dashboard

- [ ] **Step 31.2: Si algún check falla, anotar y resolver antes de continuar**

---

### Task 32: Build final + commit

- [ ] **Step 32.1: Build assets**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && npm run build
```

Expected: build exitoso. `public/build/` actualizado.

- [ ] **Step 32.2: Verificar git status**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git status
```

Expected: ver lista de archivos modificados que cubre todos los cambios del plan.

- [ ] **Step 32.3: Stage por grupos lógicos**

Stage 1 — Backend foundation:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add config/plans.php app/Enums/PlanType.php app/Services/PricingService.php app/Services/PlanLockService.php app/Console/Commands/AutoRenewalCommand.php
```

Si hay migration creada:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add database/migrations/2026_05_04_120000_extend_clients_plan_enum_for_vertical_plans.php
```

- [ ] **Step 32.4: Commit backend foundation**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git commit -m "$(cat <<'EOF'
feat(plans): backend foundation para planes vertical única (ENTRENO/NUTRICIÓN)

- config/plans.php: agregar entradas entreno_solo (170k/200k) y nutricion_solo (153k/180k)
- PlanType enum: 2 cases nuevos + label()
- PricingService: BILLABLE_PLANS extendido a 6
- PlanLockService: isMonthlyPlan acepta entreno_solo y nutricion_solo (lock 30 días)
- AutoRenewalCommand: monthlyPlans extendido (auto-renew aplica a vertical única)
- Migration aditiva (si clients.plan es ENUM) — no destructiva

Spec: docs/superpowers/specs/2026-05-04-planes-entreno-nutricion-design.md

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 32.5: Stage 2 — Página /planes pública**

```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add app/Http/Controllers/Public/PlanesController.php resources/views/public/planes.blade.php resources/css/v2-public.css lang/es/planes.php lang/en/planes.php
```

Commit:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git commit -m "$(cat <<'EOF'
feat(planes): sección "Planes simples" con cards ENTRENO/NUTRICIÓN

- PlanesController: build prices/totals/savings para 5 planes (3 completos + 2 simples)
- planes.blade.php: nueva <section class="tiers-simple"> entre TierCards y Comparador
- v2-public.css: bloque /* === TIERS SIMPLES === */ con grid 2-cols responsive
- lang/es + lang/en: claves entreno_solo_*, nutricion_solo_*, simple_section_*
- JSON-LD OfferCatalog: 5 entradas (3 completos + 2 simples)
- Alpine planName: extendido para sticky CTA bottom

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 32.6: Stage 3 — Checkout y formularios**

```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add app/Livewire/Checkout.php app/Livewire/InscriptionForm.php app/Http/Controllers/Api/PublicFormController.php app/Http/Controllers/Api/AdminController.php app/Livewire/Admin/InvitationManager.php app/Livewire/Admin/SendPlanInvitation.php resources/js/vue/pages/Public/InscriptionForm.vue
```

Commit:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git commit -m "$(cat <<'EOF'
feat(checkout/forms): aceptar entreno_solo y nutricion_solo en flujos de inscripción

- Checkout Livewire: getPlans() incluye los 2 nuevos + renovación
- InscriptionForm Livewire: validación 'plan' acepta 5 valores
- PublicFormController API: validación inscripción
- AdminController: 3 endpoints de validación admin actualizados
- InvitationManager + SendPlanInvitation: validaciones extendidas
- InscriptionForm.vue: 2 planes nuevos en step 0 + stepOrder condicional
  (entreno_solo salta Step 5; nutricion_solo salta Steps 2-4)

Out of scope (P10 spec): precios hardcodeados en InscriptionForm.vue líneas 83-103
quedan para PR posterior.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 32.7: Stage 4 — Dashboard cliente**

```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add resources/js/vue/pages/Client/PlanViewer.vue
```

Commit:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git commit -m "$(cat <<'EOF'
feat(client/dashboard): tabs bloqueadas con TabLockUpsell para vertical única

PlanViewer.vue:
- canAccessEntrenamiento, canAccessNutricion, canAccessHabitos, canAccessSuplementacion
  computeds reflejan matriz §6.4.1 del spec
- isTabLocked extendido para 6 tabs
- planTypeLabel map agrega entreno_solo→'Entreno', nutricion_solo→'Nutrición'
- TabLockUpsell card inline en cada tab bloqueada (entrenamiento/habitos/nutricion/suplementacion)
  con copy contextual + CTA "Ver Plan Esencial"
- setTab permite click en tabs lockeadas (UX educativo, decisión P11 spec)
- Estilos .tab-lock-upsell* agregados

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 32.8: Stage 5 — PlanTicketExportService (si aplica)**

Solo si Task 21 modificó el archivo:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add app/Services/PlanTicketExportService.php
git commit -m "$(cat <<'EOF'
chore(plan-tickets): plan_guides incluye entreno_solo y nutricion_solo

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 32.9: Stage 6 — public/build**

```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add public/build/
git commit -m "$(cat <<'EOF'
build(assets): rebuild Vite — incluye TIERS SIMPLES CSS + Vue PlanViewer changes

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 32.10: Stage 7 — spec + plan docs**

```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git add docs/superpowers/
git commit -m "$(cat <<'EOF'
docs(specs): planes ENTRENO/NUTRICIÓN — spec + implementation plan

- spec: 2026-05-04-planes-entreno-nutricion-design.md
- plan: 2026-05-04-planes-entreno-nutricion.md (32 tasks)

Decisiones brainstorm: sección aparte (no integrada en grilla 3),
naming ENTRENO/NUTRICIÓN, coach humano + ajuste mensual igual que Esencial,
3 períodos con descuentos, slugs entreno_solo/nutricion_solo, cards hermanas
2-cols, tabs bloqueadas con TabLockUpsell educativo.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 32.11: Verificar git log**

Run:
```bash
cd "C:/Users/GODSF/Herd/wellcore-laravel" && git log --oneline -10
```

Expected: 5-7 commits nuevos en orden lógico, todos con `Co-Authored-By: Claude Opus 4.7`.

- [ ] **Step 32.12: Push a remote (decisión Daniel)**

**NO ejecutar `git push` automáticamente.** Pedir confirmación a Daniel antes:

> "Listo el plan completo. ¿Procedo con `git push origin main`? Después de push, ¿corremos `gitpull-load` en EasyPanel para deploy?"

---

## Rollback plan

Si algo se rompe en producción tras deploy:

1. **Identificar commit problemático** via `git log --oneline -10`
2. **Revertir commit específico**:
   ```bash
   git revert <commit-sha>
   git push origin main
   ```
3. **Rebuild + redeploy**: `npm run build && git add public/build && git commit && git push && gitpull-load`
4. Si afecta `clients.plan` (migration ENUM): ver `down()` de la migration. NO ejecutar si hay clientes activos en `entreno_solo`/`nutricion_solo` — la migration lo previene con throw.
5. **Cache cleanup**: `php artisan config:clear && php artisan cache:clear && php artisan view:clear`

---

## Self-review (completado por Claude antes de entregar)

✅ **Spec coverage:** Cada sección del spec mapea a tasks específicos:
- Spec §3 (naming) → Tasks 1, 2
- Spec §4 (pricing) → Task 1
- Spec §5 (copy) → Tasks 6, 7
- Spec §6.1 (backend) → Tasks 1-5, 19-21
- Spec §6.2 (frontend público) → Tasks 8, 9
- Spec §6.3 (checkout/forms) → Tasks 10-14
- Spec §6.4 (dashboard) → Tasks 15-18
- Spec §6.5 (otros) → Task 21
- Spec §7 (SISTEMA-CREACION-PLANES) → Tasks 22-27
- Spec §10 (acceptance) → Task 31

✅ **Placeholder scan:** No hay TBD/TODO/"implement later". Todos los steps tienen código completo.

✅ **Type consistency:** Slugs `entreno_solo`/`nutricion_solo` consistentes en todas las tasks. Computeds `canAccess*` mantienen naming a través de Tasks 15-17.

✅ **Naming consistency:** `clientPlanType.value` (Vue), `$client->plan` (PHP), `clients.plan` (DB column) — usados consistentemente.

---

## Execution Handoff

**Plan complete and saved to `docs/superpowers/plans/2026-05-04-planes-entreno-nutricion.md`. Two execution options:**

**1. Subagent-Driven (recommended)** — Despacho un subagent fresco por task, review entre tasks, iteración rápida. Ideal porque hay 32 tasks de naturalezas distintas (PHP, Vue, CSS, MDs externos).

**2. Inline Execution** — Ejecuto tasks en esta sesión usando executing-plans, batch con checkpoints.

**¿Cuál prefieres?**

Si Subagent-Driven: invoco `superpowers:subagent-driven-development`.
Si Inline: invoco `superpowers:executing-plans`.
