# Coach Contract Acceptance Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a digital acceptance gate for the WellCore coach Alliance Agreement (Acuerdo de Alianza Comercial). Block coach portal access until each coach scrolls the contract to the end, accepts (or declines), and persists legally usable evidence (timestamp, IP, user-agent, content hash, version).

**Architecture:** Two additive migrations (new `coach_contract_acceptances` table + `coaches.inactive_reason` column when missing) → service layer + REST endpoints + middleware (Laravel) → reactive composable + full-screen Vue modal mounted in `CoachLayout` → router/axios hooks to react to a `403 contract_required` from the API. Versioned contract HTML lives under `resources/views/legal/`. All gated behind a `COACH_CONTRACT_GATE_ENABLED` env flag.

**Tech Stack:** Laravel 13.1.1 + PHP 8.4, MySQL (shared `wellcore_fitness` schema), Vue 3.5 + Pinia + Vue Router, Tailwind CSS 4, PHPUnit Feature tests, Vite 8.

**Companion spec:** `docs/superpowers/specs/2026-04-25-coach-contract-acceptance-design.md`

---

## File map

| Path | Created/Modified | Responsibility |
|------|------------------|----------------|
| `config/wellcore.php` | Modify | Add `coach_contract` config block + flag |
| `database/migrations/2026_04_25_create_coach_contract_acceptances_table.php` | Create | Acceptance evidence table |
| `database/migrations/2026_04_25_add_inactive_reason_to_coaches_table.php` | Create | Idempotent column for decline reason |
| `app/Models/CoachContractAcceptance.php` | Create | Eloquent model |
| `app/Services/CoachContractService.php` | Create | Versioning, hashing, persistence, decline cleanup |
| `app/Http/Controllers/Api/Coach/ContractController.php` | Create | `status` / `accept` / `decline` endpoints |
| `app/Http/Middleware/EnsureCoachContractAccepted.php` | Create | Blocks coach API when no acceptance row |
| `app/Http/Kernel.php` or `bootstrap/app.php` | Modify | Register the middleware alias |
| `routes/api.php` | Modify | Wire endpoints + apply middleware |
| `resources/views/legal/coach-contract-v1.0.blade.php` | Create | Final HTML of v1.0 (legal-grade) |
| `resources/js/vue/composables/useContractGate.js` | Create | Reactive gate state |
| `resources/js/vue/components/coach/CoachContractGate.vue` | Create | Full-screen modal with iframe + scroll detector |
| `resources/js/vue/layouts/CoachLayout.vue` | Modify | Mount the gate above all UI |
| `resources/js/vue/router/index.js` | Modify | `beforeEach` guard while gate is open |
| `resources/js/vue/stores/auth.js` (or axios setup) | Modify | Catch `403 contract_required` and trigger gate |
| `tests/Feature/Coach/ContractAcceptanceTest.php` | Create | 8 acceptance scenarios |

---

## Phase 0 — Legal HTML

### Task 1: Build the contract v1.0 HTML

**Files:**
- Create: `resources/views/legal/coach-contract-v1.0.blade.php`

- [ ] **Step 1: Copy the source HTML as the base**

The reference HTML lives at `C:\Users\GODSF\Music\INTERFAZ Y MEJORIAS\VINCULACION_COACH_WELLCORE.html`. Copy its full contents (DOCTYPE through closing `</html>`) into the new blade file.

```bash
mkdir -p resources/views/legal
cp "/c/Users/GODSF/Music/INTERFAZ Y MEJORIAS/VINCULACION_COACH_WELLCORE.html" resources/views/legal/coach-contract-v1.0.blade.php
```

- [ ] **Step 2: Append Section 8 — Aspectos legales**

In `resources/views/legal/coach-contract-v1.0.blade.php`, find the closing `</section>` of section 7 ("Tu dashboard de coach"). Immediately before the `<section class="section">` that contains the CTA-final block, insert this new section:

```html
<!-- 8. ASPECTOS LEGALES -->
<section class="section">
    <div class="section-number">— 08 —</div>
    <h2>Aspectos <span class="accent">legales</span></h2>
    <p class="section-lead">Marco normativo colombiano que rige esta alianza. Léelo: protege a ambas partes.</p>

    <h3>8.1 Naturaleza no laboral</h3>
    <p>Esta es una <strong>alianza comercial</strong>, no un contrato de trabajo. No existe subordinación, horario fijo ni exclusividad. El coach actúa como aliado independiente. En consecuencia, no aplican las disposiciones del Código Sustantivo del Trabajo (CST art. 23) ni se generan prestaciones sociales, parafiscales o seguridad social a cargo de WellCore Fitness.</p>

    <h3>8.2 Régimen tributario</h3>
    <p>El coach declara ser responsable de su propio régimen tributario ante la DIAN (RUT, retención en la fuente, IVA cuando aplique, régimen simple si está inscrito). WellCore expedirá los soportes de pago correspondientes a las comisiones del coach según las disposiciones tributarias vigentes.</p>

    <h3>8.3 Tratamiento de datos personales (Habeas Data)</h3>
    <p>De conformidad con la Ley 1581 de 2012 y el Decreto 1377 de 2013, el coach <strong>autoriza expresamente</strong> a WellCore Fitness para el tratamiento de sus datos personales con fines operativos, comerciales y de seguridad. Adicionalmente, el coach se compromete a respetar la política de privacidad de los datos de los clientes a los que tenga acceso a través de la plataforma, y a no usarlos por fuera del propósito de la asesoría.</p>

    <h3>8.4 Comercio electrónico y validez del documento digital</h3>
    <p>De acuerdo con la Ley 527 de 1999 (artículos 5 a 7), este documento aceptado en formato digital tiene la <strong>misma fuerza vinculante</strong> que un documento físico firmado. La aceptación electrónica constituye un mensaje de datos válido para todos los efectos legales.</p>

    <h3>8.5 Propiedad intelectual</h3>
    <p>WellCore Fitness conserva todos los derechos sobre los planes, metodologías, marca, materiales gráficos y contenidos de la plataforma (Ley 23 de 1982, Decisión Andina 351). El coach recibe una <strong>licencia de uso no exclusiva, no transferible y revocable</strong> exclusivamente para vender y entregar los servicios de WellCore dentro de la plataforma. Cualquier uso fuera de este alcance requiere autorización escrita.</p>

    <h3>8.6 Confidencialidad reforzada</h3>
    <p>Las obligaciones de confidencialidad descritas en la sección 06 se mantienen vigentes durante <strong>dos (2) años posteriores</strong> a la terminación de la alianza, por cualquier causa. El incumplimiento da derecho a WellCore Fitness a iniciar las acciones civiles y penales que correspondan según la legislación colombiana.</p>

    <h3>8.7 Resolución de conflictos</h3>
    <p>Cualquier controversia derivada de esta alianza se intentará resolver primero por <strong>conciliación</strong> ante un centro autorizado de la cámara de comercio del domicilio principal de WellCore Fitness. Si la conciliación fracasa, las partes acudirán a la jurisdicción ordinaria colombiana o, si así lo acuerdan por escrito, a un tribunal de arbitramento conforme a la Ley 1563 de 2012.</p>
</section>

<!-- 9. CLÁUSULA DE ACEPTACIÓN DIGITAL -->
<section class="section">
    <div class="section-number">— 09 —</div>
    <h2>Aceptación <span class="accent">digital y evidencia</span></h2>
    <p class="section-lead">Cómo se registra tu aceptación y qué evidencia queda guardada.</p>

    <div class="clause">
        <h3>Manifestación expresa de aceptación</h3>
        <p>Al hacer clic en <strong>"Aceptar y continuar"</strong> dentro de la plataforma WellCore, el coach manifiesta de forma libre, expresa e informada su aceptación íntegra del presente acuerdo en todas sus secciones.</p>
        <p>Para que la aceptación sea válida, el sistema verifica que el coach haya recorrido el documento hasta el final ("scroll completo"). Si rechaza el acuerdo o cierra la sesión sin aceptarlo, su cuenta de coach quedará <strong>inactiva</strong> y no podrá acceder al portal hasta que aceptar o contactar al administrador.</p>

        <h3>Evidencia almacenada</h3>
        <p>WellCore Fitness conserva, por cada aceptación o rechazo, los siguientes datos como prueba electrónica conforme a la Ley 527 de 1999:</p>
        <ul class="role-list">
            <li>Identificador del coach y versión del acuerdo aceptado</li>
            <li>Marca temporal UTC de la aceptación o rechazo</li>
            <li>Dirección IP desde la cual se realizó la acción</li>
            <li>User-agent del navegador / dispositivo</li>
            <li>Hash SHA-256 del HTML del documento al momento de la aceptación</li>
            <li>Indicador de scroll completo del documento</li>
        </ul>
    </div>
</section>
```

- [ ] **Step 3: Add the scroll-end sentinel**

Inside `resources/views/legal/coach-contract-v1.0.blade.php`, find the existing `<footer class="footer">` element near the end of the body. Immediately BEFORE it, insert:

```html
<div id="contract-end-sentinel" style="height:1px;width:100%;"></div>

<!-- Scroll-end notifier for the gate iframe (postMessage to parent) -->
<script>
    (function () {
        var sentinel = document.getElementById('contract-end-sentinel');
        if (!sentinel) return;
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        try {
                            window.parent.postMessage({ type: 'wc-contract-end' }, '*');
                        } catch (e) { /* parent may be cross-origin in dev — ignore */ }
                        io.disconnect();
                    }
                });
            }, { threshold: 0.1 });
            io.observe(sentinel);
        } else {
            // Older browsers fallback: post on document scroll-bottom
            window.addEventListener('scroll', function () {
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 4) {
                    try { window.parent.postMessage({ type: 'wc-contract-end' }, '*'); } catch (e) {}
                }
            }, { passive: true });
        }
    })();
</script>
```

- [ ] **Step 4: Update the footer with version + draft notice**

Replace the contents of the existing `<footer class="footer">…</footer>` with:

```html
<footer class="footer">
    <div class="footer-logo">WELL<span class="accent">CORE</span> FITNESS</div>
    <div class="footer-meta">
        Acuerdo de alianza comercial · Coaches · Versión 1.0 · 25 de abril de 2026<br>
        Documento sujeto a revisión legal final · Uso exclusivo del equipo WellCore
    </div>
</footer>
```

- [ ] **Step 5: Render-test the contract HTML**

Add a temporary route to render and inspect:

In `routes/web.php`, add at the end (this is temporary — remove in Step 6):

```php
Route::get('/_dev/coach-contract', function () {
    abort_unless(app()->environment('local'), 404);
    return view('legal.coach-contract-v1.0');
})->name('_dev.coach-contract');
```

Run `php artisan serve`, open `http://wellcore-laravel.test/_dev/coach-contract`. Verify:
- All 9 sections render with correct WellCore styling.
- The footer shows "Versión 1.0 · 25 de abril de 2026".
- Scrolling to the bottom: open DevTools console, paste `window.addEventListener('message',e=>console.log(e.data))` then scroll — expect a `{type:'wc-contract-end'}` message logged.

- [ ] **Step 6: Remove the dev route**

Delete the `_dev/coach-contract` route added in Step 5. The contract is from now on rendered ONLY via the API.

- [ ] **Step 7: Commit**

```bash
git add resources/views/legal/coach-contract-v1.0.blade.php
git commit -m "feat(legal): add coach alliance agreement v1.0 with Colombian legal scaffolding"
```

---

## Phase 1 — Database (additive migrations)

### Task 2: Create the `coach_contract_acceptances` table

**Files:**
- Create: `database/migrations/2026_04_25_create_coach_contract_acceptances_table.php`

- [ ] **Step 1: Generate the migration file**

```bash
php artisan make:migration create_coach_contract_acceptances_table
```

Note the generated filename (timestamp prefix). For this plan we assume it becomes `2026_04_25_HHMMSS_create_coach_contract_acceptances_table.php`.

- [ ] **Step 2: Replace the migration body**

Open the generated file and set its body to:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('coach_contract_acceptances')) {
            return;
        }

        Schema::create('coach_contract_acceptances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coach_id')->index();
            $table->string('contract_version', 20);
            $table->enum('status', ['accepted', 'declined']);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->char('content_hash', 64);
            $table->boolean('scroll_completed')->default(false);
            $table->timestamps();

            $table->unique(['coach_id', 'contract_version'], 'cca_coach_version_unique');
            $table->index('contract_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_contract_acceptances');
    }
};
```

- [ ] **Step 3: Run the migration**

```bash
php artisan migrate
```

Expected output: `INFO  Running migrations.` followed by the create line for the new table. Confirm:

```bash
php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasTable('coach_contract_acceptances') ? 'OK' : 'MISSING';"
```

Expected: `OK`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/*_create_coach_contract_acceptances_table.php
git commit -m "feat(db): create coach_contract_acceptances table for digital acceptance evidence"
```

---

### Task 3: Add `inactive_reason` column to `coaches`

**Files:**
- Create: `database/migrations/2026_04_25_add_inactive_reason_to_coaches_table.php`

- [ ] **Step 1: Generate migration**

```bash
php artisan make:migration add_inactive_reason_to_coaches_table
```

- [ ] **Step 2: Replace body with idempotent column add**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('coaches')) {
            return; // shared schema may not have it yet — defensive
        }
        if (Schema::hasColumn('coaches', 'inactive_reason')) {
            return; // already added by vanilla app or earlier migration
        }

        Schema::table('coaches', function (Blueprint $table) {
            $table->string('inactive_reason', 50)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('coaches')) {
            return;
        }
        if (! Schema::hasColumn('coaches', 'inactive_reason')) {
            return;
        }

        Schema::table('coaches', function (Blueprint $table) {
            $table->dropColumn('inactive_reason');
        });
    }
};
```

- [ ] **Step 3: Run + verify**

```bash
php artisan migrate
php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('coaches','inactive_reason') ? 'OK' : 'MISSING';"
```

Expected: `OK`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/*_add_inactive_reason_to_coaches_table.php
git commit -m "feat(db): add coaches.inactive_reason column (idempotent)"
```

---

## Phase 2 — Backend (config + service + controller + middleware)

### Task 4: Configure feature flag and contract version

**Files:**
- Modify: `config/wellcore.php`
- Modify: `.env.example` (if present)

- [ ] **Step 1: Add the config block**

Open `config/wellcore.php`. At the end of the returned array, add:

```php
    /*
    |--------------------------------------------------------------------------
    | Coach Contract Acceptance Gate
    |--------------------------------------------------------------------------
    | Controls the digital acceptance flow of the Coach Alliance Agreement.
    | When 'enabled' is false, the gate API short-circuits and the middleware
    | becomes a no-op.
    */
    'coach_contract' => [
        'enabled'  => env('COACH_CONTRACT_GATE_ENABLED', false),
        'version'  => env('COACH_CONTRACT_VERSION', '1.0'),
        'is_draft' => env('COACH_CONTRACT_IS_DRAFT', true),
    ],
```

- [ ] **Step 2: Document the env keys**

If `.env.example` exists at the repo root, append:

```
# Coach Contract Acceptance gate
COACH_CONTRACT_GATE_ENABLED=false
COACH_CONTRACT_VERSION=1.0
COACH_CONTRACT_IS_DRAFT=true
```

If `.env.example` does not exist, skip — the docs in Step 1 are sufficient.

- [ ] **Step 3: Smoke test config**

```bash
php artisan config:clear
php artisan tinker --execute="echo config('wellcore.coach_contract.version').' enabled='.var_export(config('wellcore.coach_contract.enabled'),true);"
```

Expected: `1.0 enabled=false`.

- [ ] **Step 4: Commit**

```bash
git add config/wellcore.php .env.example
git commit -m "feat(config): add coach_contract feature-flag block (default disabled)"
```

---

### Task 5: Create the Eloquent model + service (TDD)

**Files:**
- Create: `app/Models/CoachContractAcceptance.php`
- Create: `app/Services/CoachContractService.php`
- Create: `tests/Feature/Coach/ContractAcceptanceTest.php`

- [ ] **Step 1: Write the first failing test**

Create `tests/Feature/Coach/ContractAcceptanceTest.php`:

```php
<?php

namespace Tests\Feature\Coach;

use App\Models\Coach;
use App\Services\CoachContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ContractAcceptanceTest extends TestCase
{
    public function test_get_current_version_reads_from_config(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $service = app(CoachContractService::class);

        $this->assertSame('1.0', $service->getCurrentVersion());
    }
}
```

- [ ] **Step 2: Run the test, confirm it fails**

```bash
php artisan test --filter=test_get_current_version_reads_from_config
```

Expected: FAIL — `Class "App\\Services\\CoachContractService" not found` or similar resolution error.

- [ ] **Step 3: Create the service to make this test pass**

Create `app/Services/CoachContractService.php`:

```php
<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\CoachContractAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class CoachContractService
{
    public function getCurrentVersion(): string
    {
        return (string) config('wellcore.coach_contract.version', '1.0');
    }

    public function isGateEnabled(): bool
    {
        return (bool) config('wellcore.coach_contract.enabled', false);
    }

    public function getContractHtml(string $version): string
    {
        $template = 'legal.coach-contract-v' . $version;

        if (! View::exists($template)) {
            throw new \RuntimeException("Contract HTML for version {$version} not found ({$template}).");
        }

        return view($template)->render();
    }

    public function getCurrentContentHash(): string
    {
        return hash('sha256', $this->getContractHtml($this->getCurrentVersion()));
    }

    public function hasAcceptedCurrentVersion(int $coachId): bool
    {
        return CoachContractAcceptance::query()
            ->where('coach_id', $coachId)
            ->where('contract_version', $this->getCurrentVersion())
            ->where('status', 'accepted')
            ->exists();
    }

    public function recordAcceptance(int $coachId, Request $request, bool $scrollCompleted): CoachContractAcceptance
    {
        return CoachContractAcceptance::query()->updateOrCreate(
            [
                'coach_id'         => $coachId,
                'contract_version' => $this->getCurrentVersion(),
            ],
            [
                'status'           => 'accepted',
                'accepted_at'      => now(),
                'declined_at'      => null,
                'ip_address'       => $request->ip() ?? '0.0.0.0',
                'user_agent'       => substr((string) $request->userAgent(), 0, 4000),
                'content_hash'     => $this->getCurrentContentHash(),
                'scroll_completed' => $scrollCompleted,
            ]
        );
    }

    public function recordDecline(int $coachId, Request $request): CoachContractAcceptance
    {
        $row = CoachContractAcceptance::query()->updateOrCreate(
            [
                'coach_id'         => $coachId,
                'contract_version' => $this->getCurrentVersion(),
            ],
            [
                'status'           => 'declined',
                'accepted_at'      => null,
                'declined_at'      => now(),
                'ip_address'       => $request->ip() ?? '0.0.0.0',
                'user_agent'       => substr((string) $request->userAgent(), 0, 4000),
                'content_hash'     => $this->getCurrentContentHash(),
                'scroll_completed' => false,
            ]
        );

        // Deactivate coach + revoke tokens (auth_tokens is the custom table read by WellCoreGuard)
        Coach::query()->where('id', $coachId)->update([
            'status'          => 'inactivo',
            'inactive_reason' => 'contract_declined',
        ]);

        DB::table('auth_tokens')
            ->where('user_id', $coachId)
            ->where('user_type', 'coach')
            ->delete();

        return $row;
    }
}
```

Also create `app/Models/CoachContractAcceptance.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachContractAcceptance extends Model
{
    protected $table = 'coach_contract_acceptances';

    protected $fillable = [
        'coach_id',
        'contract_version',
        'status',
        'accepted_at',
        'declined_at',
        'ip_address',
        'user_agent',
        'content_hash',
        'scroll_completed',
    ];

    protected $casts = [
        'accepted_at'      => 'datetime',
        'declined_at'      => 'datetime',
        'scroll_completed' => 'boolean',
    ];
}
```

- [ ] **Step 4: Re-run the first test**

```bash
php artisan test --filter=test_get_current_version_reads_from_config
```

Expected: PASS.

- [ ] **Step 5: Add test — `hasAcceptedCurrentVersion` returns false when no row**

Append to `ContractAcceptanceTest.php`:

```php
    public function test_has_accepted_current_version_returns_false_when_no_row(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $coach = Coach::factory()->create();

        $service = app(CoachContractService::class);

        $this->assertFalse($service->hasAcceptedCurrentVersion($coach->id));
    }
```

If `Coach::factory()` does not exist in the project, replace with a manual insert into `coaches`:

```php
$coachId = \DB::table('coaches')->insertGetId([
    'username' => 'plan_test_'.uniqid(),
    'email'    => 'plan_test_'.uniqid().'@example.com',
    'password_hash' => password_hash('x', PASSWORD_BCRYPT),
    'status'   => 'activo',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

Adjust required columns based on what `coaches` actually has — confirm with `Schema::hasColumn` or a quick `tinker`.

- [ ] **Step 6: Run test, expect PASS (since no rows exist)**

```bash
php artisan test --filter=test_has_accepted_current_version_returns_false_when_no_row
```

Expected: PASS.

- [ ] **Step 7: Add test — `recordAcceptance` writes the row with hash + IP + UA**

```php
    public function test_record_acceptance_persists_evidence(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $coach = Coach::factory()->create();
        $request = Request::create('/api/v/coach/contract/accept', 'POST', [], [], [], [
            'REMOTE_ADDR'     => '203.0.113.42',
            'HTTP_USER_AGENT' => 'PHPUnit/Test (Plan)',
        ]);

        $service = app(CoachContractService::class);

        $row = $service->recordAcceptance($coach->id, $request, true);

        $this->assertSame('accepted', $row->status);
        $this->assertSame('203.0.113.42', $row->ip_address);
        $this->assertSame('PHPUnit/Test (Plan)', $row->user_agent);
        $this->assertTrue($row->scroll_completed);
        $this->assertNotEmpty($row->content_hash);
        $this->assertSame(64, strlen($row->content_hash));
        $this->assertTrue($service->hasAcceptedCurrentVersion($coach->id));
    }
```

- [ ] **Step 8: Run + expect PASS**

```bash
php artisan test --filter=test_record_acceptance_persists_evidence
```

Expected: PASS. If the test fails because `legal.coach-contract-v1.0` view is missing, confirm Phase 0 Task 1 was executed and committed.

- [ ] **Step 9: Add test — `recordDecline` deactivates coach and revokes tokens**

```php
    public function test_record_decline_deactivates_coach_and_revokes_tokens(): void
    {
        config(['wellcore.coach_contract.version' => '1.0']);

        $coach = Coach::factory()->create(['status' => 'activo']);

        \DB::table('auth_tokens')->insert([
            'user_id'   => $coach->id,
            'user_type' => 'coach',
            'token'     => str_repeat('a', 64),
            'expires_at'=> now()->addDays(30),
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        $request = Request::create('/api/v/coach/contract/decline', 'POST', [], [], [], [
            'REMOTE_ADDR'     => '198.51.100.7',
            'HTTP_USER_AGENT' => 'PHPUnit/Decline',
        ]);

        $service = app(CoachContractService::class);
        $row = $service->recordDecline($coach->id, $request);

        $this->assertSame('declined', $row->status);

        $coachAfter = $coach->fresh();
        $this->assertSame('inactivo', $coachAfter->status);
        $this->assertSame('contract_declined', $coachAfter->inactive_reason);

        $tokensLeft = \DB::table('auth_tokens')
            ->where('user_id', $coach->id)
            ->where('user_type', 'coach')
            ->count();
        $this->assertSame(0, $tokensLeft);
    }
```

If `auth_tokens` has additional non-null columns (per CLAUDE.md: 64-char hex token, 30-day expiry), adjust the insert payload accordingly. Run a quick `tinker` to inspect schema before committing the test.

- [ ] **Step 10: Run + expect PASS**

```bash
php artisan test --filter=test_record_decline_deactivates_coach_and_revokes_tokens
```

Expected: PASS.

- [ ] **Step 11: Add test — version bump forces re-acceptance**

```php
    public function test_version_bump_forces_re_acceptance(): void
    {
        $coach = Coach::factory()->create();

        config(['wellcore.coach_contract.version' => '1.0']);
        $service = app(CoachContractService::class);
        $service->recordAcceptance($coach->id, Request::create('/'), true);
        $this->assertTrue($service->hasAcceptedCurrentVersion($coach->id));

        // Simulate a future v1.1 — also need a blade for it for hashing in this test
        // we can stub by binding a fake CoachContractService::getContractHtml return.
        config(['wellcore.coach_contract.version' => '1.1']);

        // The hash call will fail unless v1.1 blade exists; we test only the lookup branch.
        $this->assertFalse($service->hasAcceptedCurrentVersion($coach->id));
    }
```

- [ ] **Step 12: Run + expect PASS**

```bash
php artisan test --filter=test_version_bump_forces_re_acceptance
```

Expected: PASS.

- [ ] **Step 13: Run all tests in this file**

```bash
php artisan test --filter=ContractAcceptanceTest
```

Expected: 5 tests pass.

- [ ] **Step 14: Commit**

```bash
git add app/Models/CoachContractAcceptance.php app/Services/CoachContractService.php tests/Feature/Coach/ContractAcceptanceTest.php
git commit -m "feat(coach-contract): service + model + service-level tests"
```

---

### Task 6: Create the API controller (TDD)

**Files:**
- Create: `app/Http/Controllers/Api/Coach/ContractController.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Write a failing test for `GET /status` while gate is disabled**

Append to `tests/Feature/Coach/ContractAcceptanceTest.php`:

```php
    public function test_status_endpoint_reports_no_requirement_when_gate_disabled(): void
    {
        config(['wellcore.coach_contract.enabled' => false]);

        $coach = Coach::factory()->create();
        $token = $this->issueCoachToken($coach);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v/coach/contract/status');

        $response->assertOk()
            ->assertJson([
                'requires_acceptance' => false,
            ]);
    }
```

Add a helper at the bottom of the test class:

```php
    protected function issueCoachToken(Coach $coach): string
    {
        $token = bin2hex(random_bytes(32));
        \DB::table('auth_tokens')->insert([
            'user_id'   => $coach->id,
            'user_type' => 'coach',
            'token'     => $token,
            'expires_at'=> now()->addDays(30),
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
        return $token;
    }
```

If the existing `tests/Feature/AuthFlowTest.php` already defines a token helper, mirror its exact column names — that file is the source of truth for the schema.

- [ ] **Step 2: Run test, confirm 404 (route missing yet)**

```bash
php artisan test --filter=test_status_endpoint_reports_no_requirement_when_gate_disabled
```

Expected: FAIL — `404 Not Found` for `/api/v/coach/contract/status`.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/Api/Coach/ContractController.php`:

```php
<?php

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Services\CoachContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct(private readonly CoachContractService $service)
    {
    }

    public function status(Request $request): JsonResponse
    {
        $coach = $request->user('wellcore');

        if (! $coach || ! $this->service->isGateEnabled()) {
            return response()->json([
                'requires_acceptance' => false,
                'version'             => $this->service->getCurrentVersion(),
            ]);
        }

        $version = $this->service->getCurrentVersion();
        $needs   = ! $this->service->hasAcceptedCurrentVersion($coach->id);

        return response()->json([
            'requires_acceptance' => $needs,
            'version'             => $version,
            'html'                => $needs ? $this->service->getContractHtml($version) : null,
        ]);
    }

    public function accept(Request $request): JsonResponse
    {
        $coach = $request->user('wellcore');
        if (! $coach) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $data = $request->validate([
            'version'          => ['required', 'string'],
            'scroll_completed' => ['required', 'boolean'],
        ]);

        if ($data['version'] !== $this->service->getCurrentVersion()) {
            return response()->json(['error' => 'version_mismatch'], 422);
        }

        if (! $data['scroll_completed']) {
            return response()->json(['error' => 'scroll_not_completed'], 422);
        }

        $this->service->recordAcceptance($coach->id, $request, true);

        return response()->json(['ok' => true]);
    }

    public function decline(Request $request): JsonResponse
    {
        $coach = $request->user('wellcore');
        if (! $coach) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $this->service->recordDecline($coach->id, $request);

        return response()->json(['ok' => true, 'logged_out' => true]);
    }
}
```

The guard name `'wellcore'` must match the actual guard registered in `config/auth.php`. Run `grep -n "WellCoreGuard\|'wellcore'" config/auth.php app/Auth/WellCoreGuard.php` to confirm. If the guard is registered under a different name, replace `'wellcore'` accordingly.

- [ ] **Step 4: Wire routes**

In `routes/api.php`, locate the existing coach API group (search for `prefix('v/coach')` or `'v/coach'`). Inside that group, add:

```php
    Route::get('/contract/status',  [\App\Http\Controllers\Api\Coach\ContractController::class, 'status']);
    Route::post('/contract/accept', [\App\Http\Controllers\Api\Coach\ContractController::class, 'accept']);
    Route::post('/contract/decline',[\App\Http\Controllers\Api\Coach\ContractController::class, 'decline']);
```

Place these BEFORE the middleware group from Task 7 — the contract endpoints must remain reachable even when the gate is required.

- [ ] **Step 5: Run the status test, expect PASS**

```bash
php artisan test --filter=test_status_endpoint_reports_no_requirement_when_gate_disabled
```

Expected: PASS.

- [ ] **Step 6: Add test — `status` requires acceptance when gate enabled and no row**

```php
    public function test_status_endpoint_reports_requires_acceptance_when_gate_enabled(): void
    {
        config([
            'wellcore.coach_contract.enabled' => true,
            'wellcore.coach_contract.version' => '1.0',
        ]);

        $coach = Coach::factory()->create();
        $token = $this->issueCoachToken($coach);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v/coach/contract/status');

        $response->assertOk()
            ->assertJsonPath('requires_acceptance', true)
            ->assertJsonPath('version', '1.0')
            ->assertJsonStructure(['html']);

        $this->assertNotEmpty($response->json('html'));
    }
```

Run + expect PASS:

```bash
php artisan test --filter=test_status_endpoint_reports_requires_acceptance_when_gate_enabled
```

- [ ] **Step 7: Add test — accept endpoint records and clears the requirement**

```php
    public function test_accept_endpoint_records_and_clears_requirement(): void
    {
        config([
            'wellcore.coach_contract.enabled' => true,
            'wellcore.coach_contract.version' => '1.0',
        ]);

        $coach = Coach::factory()->create();
        $token = $this->issueCoachToken($coach);

        $accept = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v/coach/contract/accept', [
                'version'          => '1.0',
                'scroll_completed' => true,
            ]);

        $accept->assertOk()->assertJson(['ok' => true]);

        $status = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v/coach/contract/status');

        $status->assertOk()->assertJsonPath('requires_acceptance', false);
    }
```

Run + expect PASS.

- [ ] **Step 8: Add test — accept rejects scroll_completed=false**

```php
    public function test_accept_endpoint_rejects_when_scroll_not_completed(): void
    {
        config([
            'wellcore.coach_contract.enabled' => true,
            'wellcore.coach_contract.version' => '1.0',
        ]);

        $coach = Coach::factory()->create();
        $token = $this->issueCoachToken($coach);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v/coach/contract/accept', [
                'version'          => '1.0',
                'scroll_completed' => false,
            ]);

        $response->assertStatus(422)->assertJsonPath('error', 'scroll_not_completed');
    }
```

Run + expect PASS.

- [ ] **Step 9: Run the whole test class**

```bash
php artisan test --filter=ContractAcceptanceTest
```

Expected: all 9 tests so far pass.

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/Api/Coach/ContractController.php routes/api.php tests/Feature/Coach/ContractAcceptanceTest.php
git commit -m "feat(coach-contract): controller + endpoints + endpoint tests"
```

---

### Task 7: Middleware that blocks coach API when contract not accepted

**Files:**
- Create: `app/Http/Middleware/EnsureCoachContractAccepted.php`
- Modify: `bootstrap/app.php` (Laravel 11+ middleware registration)
- Modify: `routes/api.php`

- [ ] **Step 1: Failing test — gate enabled, dashboard request returns 403 contract_required**

Append to `tests/Feature/Coach/ContractAcceptanceTest.php`:

```php
    public function test_middleware_blocks_dashboard_when_contract_not_accepted(): void
    {
        config([
            'wellcore.coach_contract.enabled' => true,
            'wellcore.coach_contract.version' => '1.0',
        ]);

        $coach = Coach::factory()->create();
        $token = $this->issueCoachToken($coach);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v/coach/dashboard');

        $response->assertStatus(403)
            ->assertJsonPath('contract_required', true)
            ->assertJsonPath('version', '1.0');
    }

    public function test_middleware_allows_dashboard_after_acceptance(): void
    {
        config([
            'wellcore.coach_contract.enabled' => true,
            'wellcore.coach_contract.version' => '1.0',
        ]);

        $coach = Coach::factory()->create();
        $token = $this->issueCoachToken($coach);

        // Pre-accept directly
        app(\App\Services\CoachContractService::class)
            ->recordAcceptance($coach->id, \Illuminate\Http\Request::create('/'), true);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v/coach/dashboard');

        $response->assertStatus(200);
    }

    public function test_middleware_does_not_block_contract_endpoints(): void
    {
        config([
            'wellcore.coach_contract.enabled' => true,
            'wellcore.coach_contract.version' => '1.0',
        ]);

        $coach = Coach::factory()->create();
        $token = $this->issueCoachToken($coach);

        $status = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v/coach/contract/status');

        $status->assertOk();
    }
```

- [ ] **Step 2: Run, expect FAIL on first two (still no middleware)**

```bash
php artisan test --filter=test_middleware_blocks_dashboard_when_contract_not_accepted
```

Expected: FAIL — dashboard returns 200 (middleware not registered yet).

- [ ] **Step 3: Create the middleware**

`app/Http/Middleware/EnsureCoachContractAccepted.php`:

```php
<?php

namespace App\Http\Middleware;

use App\Services\CoachContractService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCoachContractAccepted
{
    public function __construct(private readonly CoachContractService $service)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->service->isGateEnabled()) {
            return $next($request);
        }

        $coach = $request->user('wellcore');

        if (! $coach) {
            return $next($request); // let auth middleware handle this
        }

        if ($this->service->hasAcceptedCurrentVersion($coach->id)) {
            return $next($request);
        }

        return response()->json([
            'contract_required' => true,
            'version'           => $this->service->getCurrentVersion(),
        ], 403);
    }
}
```

The guard name must match what `request->user('wellcore')` resolves to elsewhere in the codebase. If the controllers use `auth('coach')->user()` instead, switch the call here.

- [ ] **Step 4: Register the middleware alias**

In `bootstrap/app.php`, find the `withMiddleware` block and add the alias inside `$middleware->alias([...])`:

```php
$middleware->alias([
    // ...existing aliases...
    'coach.contract' => \App\Http\Middleware\EnsureCoachContractAccepted::class,
]);
```

If the project uses the legacy Laravel 10-style `app/Http/Kernel.php`, add `'coach.contract' => …` to the `$routeMiddleware` array there instead.

- [ ] **Step 5: Apply the middleware in routes/api.php**

In `routes/api.php`, find the existing coach API group. Wrap dashboard / clients / messages / etc. inside a sub-group that uses the new middleware, but KEEP `/contract/*` outside of it:

```php
Route::prefix('v/coach')->middleware([/* existing auth middleware */])->group(function () {
    // contract endpoints — never gated by themselves
    Route::get('/contract/status',  [\App\Http\Controllers\Api\Coach\ContractController::class, 'status']);
    Route::post('/contract/accept', [\App\Http\Controllers\Api\Coach\ContractController::class, 'accept']);
    Route::post('/contract/decline',[\App\Http\Controllers\Api\Coach\ContractController::class, 'decline']);

    // everything else gated by contract acceptance
    Route::middleware('coach.contract')->group(function () {
        // ...existing coach routes (dashboard, clients, messages, plan-tickets, etc.)
    });
});
```

The exact pre-existing structure of the coach group must be preserved — this step ONLY moves all non-contract routes inside the new `middleware('coach.contract')` sub-group. Use `grep -n "Route::.*coach" routes/api.php` to enumerate the existing routes before refactoring.

- [ ] **Step 6: Run the three middleware tests, expect PASS**

```bash
php artisan test --filter=test_middleware_blocks_dashboard_when_contract_not_accepted
php artisan test --filter=test_middleware_allows_dashboard_after_acceptance
php artisan test --filter=test_middleware_does_not_block_contract_endpoints
```

Expected: all pass.

- [ ] **Step 7: Run the entire feature suite to confirm no regressions**

```bash
php artisan test --testsuite=Feature
```

Expected: all tests still pass. If a previously-green test fails because it called a coach API without setting up an acceptance row, that's the gate working correctly — update the test to either disable the flag or pre-accept.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Middleware/EnsureCoachContractAccepted.php bootstrap/app.php routes/api.php tests/Feature/Coach/ContractAcceptanceTest.php
git commit -m "feat(coach-contract): middleware blocks coach API until acceptance"
```

---

## Phase 3 — Frontend (Vue gate)

### Task 8: Create the `useContractGate` composable

**Files:**
- Create: `resources/js/vue/composables/useContractGate.js`

- [ ] **Step 1: Write the composable**

```js
import { ref, computed } from 'vue';
import { useApi } from './useApi';

const requires        = ref(false);
const version         = ref('');
const html            = ref('');
const scrollCompleted = ref(false);
const submitting      = ref(false);
const error           = ref(null);

export function useContractGate() {
    const api = useApi();

    async function refresh() {
        error.value = null;
        try {
            const { data } = await api.get('/api/v/coach/contract/status');
            requires.value = !!data.requires_acceptance;
            version.value  = data.version || '';
            html.value     = data.html || '';
            if (!requires.value) {
                scrollCompleted.value = false;
            }
        } catch (e) {
            // 401 is normal pre-login; ignore. Anything else surfaces.
            if (e?.response?.status !== 401) {
                error.value = e?.response?.data?.error || 'No fue posible verificar el contrato.';
            }
        }
    }

    async function accept() {
        if (!scrollCompleted.value) return false;
        submitting.value = true;
        error.value = null;
        try {
            await api.post('/api/v/coach/contract/accept', {
                version: version.value,
                scroll_completed: true,
            });
            requires.value = false;
            html.value = '';
            scrollCompleted.value = false;
            return true;
        } catch (e) {
            error.value = e?.response?.data?.error || 'No fue posible registrar la aceptación.';
            return false;
        } finally {
            submitting.value = false;
        }
    }

    async function decline() {
        submitting.value = true;
        error.value = null;
        try {
            await api.post('/api/v/coach/contract/decline');
            return true;
        } catch (e) {
            error.value = e?.response?.data?.error || 'No fue posible registrar el rechazo.';
            return false;
        } finally {
            submitting.value = false;
        }
    }

    function markScrollComplete() {
        scrollCompleted.value = true;
    }

    return {
        requires,
        version,
        html,
        scrollCompleted: computed(() => scrollCompleted.value),
        submitting,
        error,
        refresh,
        accept,
        decline,
        markScrollComplete,
    };
}
```

`useApi` is the existing composable used elsewhere (`resources/js/vue/composables/useApi.js`). Verify its export signature with `grep -n "export.*useApi" resources/js/vue/composables/useApi.js`. If it's a default export, change the import accordingly.

- [ ] **Step 2: Build assets**

```bash
npm run build
```

Expected: build succeeds.

- [ ] **Step 3: Commit**

```bash
git add resources/js/vue/composables/useContractGate.js
git commit -m "feat(coach-contract): useContractGate composable"
```

---

### Task 9: Create the `CoachContractGate` modal component

**Files:**
- Create: `resources/js/vue/components/coach/CoachContractGate.vue`

- [ ] **Step 1: Write the component**

```vue
<script setup>
import { onMounted, onBeforeUnmount, ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useContractGate } from '../../composables/useContractGate';
import { useAuthStore } from '../../stores/auth';

const router    = useRouter();
const authStore = useAuthStore();
const gate      = useContractGate();

const accepted        = ref(false); // checkbox state
const showDeclineConfirm = ref(false);
const messageListenerAttached = ref(false);

const acceptDisabled = computed(() =>
    !gate.scrollCompleted.value || !accepted.value || gate.submitting.value
);

function handleMessage(e) {
    if (e?.data && e.data.type === 'wc-contract-end') {
        gate.markScrollComplete();
    }
}

async function handleAccept() {
    const ok = await gate.accept();
    if (ok) {
        // Reload so the rest of the portal mounts cleanly without the gate.
        window.location.reload();
    }
}

function openDeclineConfirm() {
    showDeclineConfirm.value = true;
}

async function handleDecline() {
    const ok = await gate.decline();
    if (ok) {
        try { await authStore.logout(); } catch (_) {}
        router.push({ path: '/login', query: { reason: 'contract_declined' } });
    }
}

onMounted(async () => {
    if (!messageListenerAttached.value) {
        window.addEventListener('message', handleMessage);
        messageListenerAttached.value = true;
    }
    await gate.refresh();
});

onBeforeUnmount(() => {
    if (messageListenerAttached.value) {
        window.removeEventListener('message', handleMessage);
        messageListenerAttached.value = false;
    }
});

watch(() => gate.requires.value, (val) => {
    if (!val) {
        accepted.value = false;
        showDeclineConfirm.value = false;
    }
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="gate.requires.value"
            class="fixed inset-0 z-[200] flex flex-col bg-wc-bg/95 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="contract-gate-title"
        >
            <!-- Header -->
            <header class="border-b border-wc-border bg-wc-bg-secondary px-4 py-3 sm:px-6">
                <h2 id="contract-gate-title" class="font-display text-lg uppercase tracking-wider text-wc-text">
                    Acuerdo de Alianza Comercial · WellCore Fitness
                </h2>
                <p class="mt-1 text-xs text-wc-text-tertiary">
                    Versión {{ gate.version.value || '1.0' }} · Lee hasta el final para activar la aceptación
                </p>
            </header>

            <!-- Iframe with contract HTML -->
            <div class="flex-1 overflow-hidden bg-black p-2 sm:p-4">
                <iframe
                    sandbox="allow-same-origin allow-scripts"
                    :srcdoc="gate.html.value"
                    class="h-full w-full rounded-md border border-wc-border bg-wc-bg"
                    title="Contrato del coach"
                ></iframe>
            </div>

            <!-- Footer with controls -->
            <footer class="border-t border-wc-border bg-wc-bg-secondary px-4 py-4 sm:px-6">
                <div class="mx-auto max-w-3xl space-y-3">
                    <p
                        v-if="!gate.scrollCompleted.value"
                        class="text-xs text-wc-text-tertiary"
                    >
                        Lee el documento hasta el final para activar la aceptación.
                    </p>

                    <label class="flex items-start gap-3 text-sm text-wc-text">
                        <input
                            type="checkbox"
                            v-model="accepted"
                            :disabled="!gate.scrollCompleted.value || gate.submitting.value"
                            class="mt-0.5 h-4 w-4 rounded border-wc-border bg-wc-bg-tertiary text-wc-accent focus:ring-2 focus:ring-wc-accent disabled:opacity-40"
                        />
                        <span :class="gate.scrollCompleted.value ? 'text-wc-text' : 'text-wc-text-tertiary'">
                            He leído y acepto el Acuerdo de Alianza Comercial v{{ gate.version.value || '1.0' }}.
                        </span>
                    </label>

                    <p v-if="gate.error.value" class="text-xs text-red-500">{{ gate.error.value }}</p>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            type="button"
                            @click="openDeclineConfirm"
                            :disabled="gate.submitting.value"
                            class="text-xs text-wc-text-tertiary underline hover:text-wc-text disabled:opacity-50"
                        >
                            Rechazar y dar de baja mi cuenta
                        </button>

                        <button
                            type="button"
                            @click="handleAccept"
                            :disabled="acceptDisabled"
                            class="rounded-button bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ gate.submitting.value ? 'Enviando...' : 'Aceptar y continuar' }}
                        </button>
                    </div>
                </div>
            </footer>

            <!-- Decline confirmation dialog -->
            <div
                v-if="showDeclineConfirm"
                class="fixed inset-0 z-[210] flex items-center justify-center bg-black/70 p-4"
            >
                <div class="w-full max-w-md rounded-card border border-wc-border bg-wc-bg-secondary p-5 shadow-2xl">
                    <h3 class="font-display text-base uppercase text-wc-text">¿Rechazar el acuerdo?</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Esta acción es <strong class="text-wc-text">definitiva</strong>. Tu cuenta quedará inactiva y no podrás recuperarla sin contactar al administrador.
                    </p>
                    <div class="mt-4 flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showDeclineConfirm = false"
                            :disabled="gate.submitting.value"
                            class="rounded-button border border-wc-border px-4 py-2 text-sm text-wc-text hover:bg-wc-bg-tertiary"
                        >
                            Volver al acuerdo
                        </button>
                        <button
                            type="button"
                            @click="handleDecline"
                            :disabled="gate.submitting.value"
                            class="rounded-button bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            Sí, rechazar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
```

If the auth store does not export `logout`, replace `authStore.logout()` with the actual logout helper in use (e.g. clearing localStorage tokens manually).

- [ ] **Step 2: Build assets**

```bash
npm run build
```

Expected: build succeeds.

- [ ] **Step 3: Commit**

```bash
git add resources/js/vue/components/coach/CoachContractGate.vue
git commit -m "feat(coach-contract): full-screen acceptance gate component"
```

---

### Task 10: Mount the gate in CoachLayout + router guard

**Files:**
- Modify: `resources/js/vue/layouts/CoachLayout.vue`
- Modify: `resources/js/vue/router/index.js`

- [ ] **Step 1: Mount the gate at the top of CoachLayout**

Open `resources/js/vue/layouts/CoachLayout.vue`. In the `<script setup>` block, near the existing imports, add:

```js
import CoachContractGate from '../components/coach/CoachContractGate.vue';
import { useContractGate } from '../composables/useContractGate';

const contractGate = useContractGate();
```

In the `<template>`, find the very first wrapper element (the outermost `<div>` of the layout). Immediately inside it, BEFORE all other children, add:

```vue
<CoachContractGate />
```

Keep the rest of the layout untouched. The component uses `<Teleport to="body">` so its visual placement does not depend on layout DOM position.

- [ ] **Step 2: Add a router guard**

In `resources/js/vue/router/index.js`, find the `router.beforeEach` block (or create one if absent). Add:

```js
import { useContractGate } from '../composables/useContractGate';

router.beforeEach((to, from, next) => {
    // Only gate coach routes
    if (!to.path.startsWith('/coach')) {
        return next();
    }
    const gate = useContractGate();
    if (gate.requires.value) {
        // Gate is open — allow only same-page reload + login redirect
        if (to.path === from.path || to.path === '/login') {
            return next();
        }
        return next(false); // block navigation
    }
    next();
});
```

If a `beforeEach` already exists, merge the logic (call `next()` only once at the end).

- [ ] **Step 3: Build assets**

```bash
npm run build
```

Expected: build succeeds.

- [ ] **Step 4: Manual smoke test (gate disabled)**

1. With `COACH_CONTRACT_GATE_ENABLED=false` (default), login as coach.
2. Confirm the dashboard loads normally with no modal.
3. The status endpoint returns `requires_acceptance: false`; the gate component does not render.

- [ ] **Step 5: Manual smoke test (gate enabled, fresh coach)**

1. Set `COACH_CONTRACT_GATE_ENABLED=true` in `.env`. Run `php artisan config:clear`.
2. Pick a coach who has no row in `coach_contract_acceptances` (any test coach).
3. Login. Expect the gate modal to render full-screen with the iframe loaded.
4. Try to click `Aceptar` without scrolling — disabled.
5. Scroll the iframe to the end. The helper text disappears and the checkbox becomes enabled.
6. Tick the checkbox. The `Aceptar y continuar` button enables.
7. Click. Expect the modal to close + page reload + dashboard accessible.
8. Check DB: `select * from coach_contract_acceptances where coach_id = ?` shows the row with `status='accepted'`, `scroll_completed=1`, IP, user-agent, content_hash populated.

- [ ] **Step 6: Manual smoke test (decline)**

1. With another fresh coach, login → gate appears.
2. Click `Rechazar y dar de baja mi cuenta` → confirmation dialog appears.
3. Click `Sí, rechazar`.
4. Expect logout + redirect to `/login?reason=contract_declined`.
5. Try to login again with the same coach: backend should reject because `coaches.status='inactivo'` and tokens are gone.

- [ ] **Step 7: Commit**

```bash
git add resources/js/vue/layouts/CoachLayout.vue resources/js/vue/router/index.js
git commit -m "feat(coach-contract): mount gate in CoachLayout + router guard"
```

---

### Task 11: Axios interceptor for `403 contract_required`

**Files:**
- Modify: existing axios setup (likely `resources/js/vue/composables/useApi.js` or `resources/js/vue/api/axios.js` — confirm)

- [ ] **Step 1: Find the axios setup**

Run: `grep -rn "axios.create\|interceptors.response" resources/js/vue --include="*.js" --include="*.ts"`
Note the file containing the response interceptor. For this plan we assume `resources/js/vue/composables/useApi.js`.

- [ ] **Step 2: Add gate refresh to the response error interceptor**

In the located file, find the existing `axios.interceptors.response.use(...)` (or equivalent). Inside the error branch, before the existing 401 handling, add:

```js
if (error?.response?.status === 403 && error.response?.data?.contract_required) {
    // Lazy import to avoid circular dependency in tooling
    import('./useContractGate').then(({ useContractGate }) => {
        useContractGate().refresh();
    });
}
```

Adjust the relative path of the import based on where the axios setup lives.

- [ ] **Step 3: Build assets**

```bash
npm run build
```

Expected: build succeeds.

- [ ] **Step 4: Manual verification**

1. With gate enabled and a coach who has not accepted, login.
2. The gate component on mount calls `/contract/status` → it sees `requires_acceptance=true` directly.
3. Independently: simulate any request that hits a gated endpoint without the modal (theoretically blocked by router) — the interceptor catches the 403 and triggers a gate refresh as a safety net.

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/composables/useApi.js
# or whichever file holds the interceptor
git commit -m "feat(coach-contract): axios interceptor catches 403 contract_required"
```

---

## Phase 4 — Email touch-up

### Task 12: Add contract notice to the credentials email

**Files:**
- Modify: `resources/views/emails/coach-credentials.blade.php`

- [ ] **Step 1: Add a paragraph near the bottom**

In `resources/views/emails/coach-credentials.blade.php`, find the section under `@unless($isReset)` containing the "Onboarding" heading (around line 70). Immediately AFTER the existing onboarding paragraph, add:

```html
<p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:20px 0 8px 0;">Acuerdo de Alianza Comercial</p>
<p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.6;margin:0 0 8px 0;">
  Al ingresar por primera vez veras el Acuerdo de Alianza Comercial. Lealo con calma — es la base legal de tu vinculacion al equipo. Si tienes dudas, escribenos antes de aceptar.
</p>
```

(ASCII-only on purpose; some mail clients mangle accented characters in HTML email bodies. Match the existing style of the template.)

- [ ] **Step 2: Manual verification**

Trigger a coach credentials email and confirm the new paragraph renders directly under the existing onboarding section.

- [ ] **Step 3: Commit**

```bash
git add resources/views/emails/coach-credentials.blade.php
git commit -m "feat(emails): announce contract acceptance step in coach credentials email"
```

---

## Phase 5 — Verification, rollout, and deploy

### Task 13: Run full test suite + manual rollout dry-run

- [ ] **Step 1: Full feature suite**

```bash
php artisan test --testsuite=Feature
```

Expected: all tests pass.

- [ ] **Step 2: Production build**

```bash
npm run build
```

Expected: build succeeds with the gate component bundled.

- [ ] **Step 3: Staging dry-run**

1. Deploy current branch to staging (or local with the flag enabled).
2. Use 3 different test coach accounts:
   - Coach A: never accepted → gate appears on login.
   - Coach B: accepts → gate disappears, dashboard works.
   - Coach C: declines → account deactivates, login fails next time with "cuenta inactiva" or 401.
3. Verify rows in `coach_contract_acceptances` for each.
4. Note IP, user-agent, content_hash sanity (hash is 64 hex chars).

- [ ] **Step 4: Push to main**

```bash
git push origin main
```

- [ ] **Step 5: Deploy to production with flag OFF**

In EasyPanel:
1. Run `gitpull-load` script via MCP (do NOT use Rebuild Docker).
2. Run `php artisan migrate` inside the container — both new migrations are additive.
3. Run `php artisan config:clear`.
4. Confirm `COACH_CONTRACT_GATE_ENABLED=false` in production env. The middleware short-circuits while the env is unset/false.

- [ ] **Step 6: Flip the flag**

Once Steps 1–5 are confirmed clean:
1. Set `COACH_CONTRACT_GATE_ENABLED=true` in production env.
2. Run `php artisan config:clear`.
3. All currently active coaches will see the gate on next request.

- [ ] **Step 7: Monitor for 72 h**

Track:
- Decline rate (rows with `status='declined'`).
- Acceptance rate.
- Coach support tickets mentioning "contrato" or "acuerdo".

If decline rate > 5% in 72 h, flip the env flag back to `false`, run `config:clear`, and investigate UX/content before re-enabling.

- [ ] **Step 8: Final commit if any monitoring docs need to be added**

If you create a runbook entry or rollout note, commit it under `docs/runbooks/`. Otherwise this task ends at Step 7.

---

## Open follow-ups (out of scope, listed for tracking)

- **Final legal review by Colombian counsel.** Mark v1.0 as definitive (`COACH_CONTRACT_IS_DRAFT=false`) only after counsel sign-off.
- **24 h pre-rollout email notice to existing coaches.** Optional, recommended; track as a separate plan.
- **Admin UI to inspect acceptance rows.** Out of scope; phpMyAdmin query suffices for now.
- **WebSocket-based realtime for the dashboard counters.** Tracked under the companion mobile-bugs spec; not part of this plan.
