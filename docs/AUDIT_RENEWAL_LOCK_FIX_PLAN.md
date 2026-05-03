# Plan de Implementación — Hardening del Sistema de Renovación + Lock + Promo

**Generado:** 2026-04-26
**Auditoría base:** 4 agentes especializados (la-09-payments, la-05-security, la-02-backend, la-14-testing)
**Para:** Claude Sonnet (max effort) — implementar fixes en orden estricto
**Repo:** C:\Users\GODSF\Herd\wellcore-laravel

> **Reglas inviolables:**
> - **NUNCA** ejecutar `npm-build` en EasyPanel (tumba el VPS — confirmado 2 veces). Compilar local con `npm run build` (~2s) → commit `public/build/` → push → `silvia-gitpull-load`.
> - **NO crear migraciones destructivas**. Solo aditivas con backfill.
> - DB es compartida con vanilla PHP. Tests Feature actualmente escriben en prod (`Pest.php` línea 17 tiene `RefreshDatabase` comentado) — **PELIGRO**.
> - Test credentials: `Daniel.esparza` / `KingLord6962`
> - Trabajar en orden de prioridad: P0 → P1 → P2 → P3.

---

## Resumen Ejecutivo

| Severidad | Cantidad | Estado actual |
|-----------|----------|---------------|
| **CRITICAL** | 4 | Hay IDOR explotable, monto manipulable, lock no enforced server-side |
| **HIGH** | 14 | Revenue leak, race conditions, lógica de fechas incorrecta |
| **MEDIUM** | 12 | UX, cache stale, double processing |
| **LOW** | 6 | Cosméticos, hardening preventivo |

**Cobertura tests del sistema renewal/lock: ~10%** (solo lo unitario puro). 0 tests Feature del flujo crítico.

---

## P0 — BLOCKERS (implementar HOY antes de cualquier otra cosa)

### P0.1 — Fix IDOR `renewalClientId` manipulable + `/renovar` sin auth

**Severidad:** CRITICAL
**Archivos:** `app/Livewire/Checkout.php`, `routes/web.php`
**Riesgo:** Cualquier guest o cliente autenticado puede crear `Payment` con `client_id` arbitrario. Combinado con webhook aprobado, se renueva el plan de otra cuenta. **Account takeover de suscripción.**

**Acción 1 — Agregar middleware auth a `/renovar`:**
```php
// routes/web.php — línea ~147
Route::get('/renovar', Checkout::class)
    ->name('renovar')
    ->middleware('auth:wellcore')
    ->defaults('renewal', 1);
```

**Acción 2 — Bloquear `renewalClientId` contra manipulación + re-resolver desde guard en cada paso crítico:**
```php
// app/Livewire/Checkout.php

use Livewire\Attributes\Locked;

// Cambiar:
public ?int $renewalClientId = null;
// Por:
#[Locked]
public ?int $renewalClientId = null;
```

```php
// En prepareWompiPayment() — ANTES de Payment::create:
protected function prepareWompiPayment(): void
{
    $wompi = app(WompiService::class);

    // SECURITY: re-resolver cliente desde guard. NO confiar en property serializada.
    if ($this->isRenewal) {
        $guard = app(\App\Auth\WellCoreGuard::class);
        $authedClient = $guard->user();

        if (! $authedClient instanceof \App\Models\Client) {
            $this->paymentError = 'Sesion expirada. Inicia sesion para renovar.';
            return;
        }

        // SOBRESCRIBIR siempre — el guard es la única fuente de verdad
        $this->renewalClientId = $authedClient->id;
    }

    $total = $this->getTotal();
    // ... resto igual
}
```

**Acción 3 — Validar reference matchea client_id en `ActivateRenewalAction`:**
```php
// app/Actions/ActivateRenewalAction.php — execute() inicio
public function execute(Payment $payment): ?AssignedPlan
{
    if (! $payment->client_id) {
        \Log::warning('ActivateRenewalAction: payment sin client_id', ['ref' => $payment->wompi_reference]);
        return null;
    }

    // SECURITY: validar que el client_id en reference matchea el client_id del payment.
    // Reference format: RENEWAL-{client_id}-{hex8}-{ts}
    if (preg_match('/^RENEWAL-(\d+)-/', (string) $payment->wompi_reference, $m)) {
        $refClientId = (int) $m[1];
        if ($refClientId !== (int) $payment->client_id) {
            \Log::critical('Renewal reference mismatch — possible attack', [
                'payment_id' => $payment->id,
                'payment_client' => $payment->client_id,
                'reference_client' => $refClientId,
            ]);
            return null;
        }
    }

    // ... resto igual
}
```

**Test (crear `tests/Feature/Security/RenewalIDORTest.php`):**
```php
test('guest cannot access /renovar', function () {
    $this->get('/renovar')->assertRedirect('/login');
});

test('authenticated client A cannot create payment for client B', function () {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    actingAsClient($clientA);

    Livewire::test(Checkout::class)
        ->set('isRenewal', true)
        ->set('renewalClientId', $clientB->id)  // intento de IDOR
        ->set('plan', 'metodo')
        ->call('proceedToPayment');

    // El Payment debe quedar con client_id de A, NO de B
    $payment = Payment::latest()->first();
    expect($payment->client_id)->toBe($clientA->id);
});

test('renewal reference client_id mismatch is rejected by ActivateRenewalAction', function () {
    $payment = Payment::factory()->create([
        'client_id' => 99,
        'wompi_reference' => 'RENEWAL-42-ABCD-1234',  // mismatch
    ]);
    expect((new ActivateRenewalAction(...))->execute($payment))->toBeNull();
});
```

**Verificación post-fix:**
- `/renovar` sin auth → redirect a /login
- Manipulación de Livewire snapshot → renewalClientId se sobrescribe en server
- Webhook con reference de cliente equivocado → log critical y no extiende

---

### P0.2 — Fix manipulación de monto (descuento client-side)

**Severidad:** CRITICAL
**Archivos:** `app/Livewire/Checkout.php`, `app/Services/WompiService.php`
**Riesgo:** Atacante manipula `descuento` en snapshot Livewire, paga $1 COP por plan, signature válida.

**Acción 1 — `descuento` y `price` deben ser `#[Locked]` y recalcularse server-side antes de firmar:**
```php
// app/Livewire/Checkout.php

use Livewire\Attributes\Locked;

#[Locked] public int $price = 0;
#[Locked] public int $descuento = 0;
```

**Acción 2 — Recalcular precio desde config en `prepareWompiPayment` (no confiar en `$this->price`):**
```php
protected function prepareWompiPayment(): void
{
    // ... auth check del P0.1 arriba

    // SECURITY: recomputar precio canónico desde config, no de la prop public
    $canonicalPrice = (int) config("plans.{$this->plan}.price_cop", 0);
    if ($canonicalPrice <= 0) {
        $this->paymentError = 'Plan invalido.';
        return;
    }

    // Re-validar descuento (solo aceptar si el código es válido — almacenar uses en sesión)
    $finalDiscount = $this->resolveServerSideDiscount($canonicalPrice);

    $total = max(1500, $canonicalPrice - $finalDiscount); // mínimo 1500 COP

    $this->price = $canonicalPrice;          // sync display
    $this->descuento = $finalDiscount;
    $this->amountInCents = $total * 100;
    // ... resto igual
}

protected function resolveServerSideDiscount(int $canonicalPrice): int
{
    // Solo aplicar el descuento si el código está validado y aplicable
    if (! $this->codigoDescuento) return 0;
    $codigos = ['WELLCORE10' => 10, 'RISE20' => 20, 'FITSTART' => 15];
    $code = strtoupper(trim($this->codigoDescuento));
    if (! isset($codigos[$code])) return 0;
    return (int) round($canonicalPrice * $codigos[$code] / 100);
}
```

**Acción 3 — Validar amount_in_cents en webhook:**
```php
// app/Services/WompiService.php — handleWebhook(), después de buscar el Payment
$expectedAmountCents = (int) ((float) $payment->amount * 100);
$receivedAmountCents = (int) $amountInCents;

if (abs($receivedAmountCents - $expectedAmountCents) > 100) {  // tolerancia 1 COP
    $this->logEvent('webhook.amount_mismatch', [
        'payment_id' => $payment->id,
        'expected_cents' => $expectedAmountCents,
        'received_cents' => $receivedAmountCents,
    ]);
    \Log::critical('Wompi webhook amount mismatch', [
        'payment_id' => $payment->id,
        'expected' => $expectedAmountCents,
        'received' => $receivedAmountCents,
    ]);
    return false;  // no procesar
}
```

**Tests:**
```php
test('descuento manipulado en snapshot no afecta amountInCents', function () {
    Livewire::test(Checkout::class)
        ->set('plan', 'metodo')
        ->set('descuento', 999999)  // intento de descuento absurdo
        ->set('terminos', true)
        ->set('nombre', 'X')->set('email', 'x@x.com')->set('whatsapp', '123')
        ->set('pais', 'colombia')->set('objetivo', 'fuerza')
        ->call('proceedToPayment')
        ->tap(fn ($t) => expect($t->get('amountInCents'))
            ->toBeGreaterThanOrEqual(150000)); // ≥1500 COP × 100
});

test('webhook rejects payment with amount mismatch', function () {
    $payment = Payment::factory()->create(['amount' => 254150, 'wompi_reference' => 'WC-X']);
    $payload = [...]; // con amount_in_cents = 1000 (manipulado)
    expect($wompiService->handleWebhook($payload))->toBeFalse();
});
```

---

### P0.3 — Aplicar `plan.lock:strict` a rutas API protegidas

**Severidad:** CRITICAL
**Archivo:** `routes/api.php`
**Riesgo:** **El lock es solo cosmético**. Cliente con plan expirado puede inspeccionar DOM, eliminar `LockOverlay` y seguir usando `/api/v/client/dashboard`, `/training`, `/workout/start`, etc. **El sistema entero es bypass-able desde el navegador.**

**Acción — Agregar middleware al grupo de rutas pagadas:**
```php
// routes/api.php

// Endpoints SIEMPRE accesibles (incluso con plan expirado):
//   /plan-status (Vue SPA lo necesita para detectar el lock)
//   /account-status (login health check)
//   /notifications (cliente debe ver "tu plan expiró")
//   /my-coach (cliente debe poder contactar coach)

// Grupo PROTEGIDO por plan-lock (respond 403 si expirado):
Route::prefix('v/client')
    ->middleware(['auth:wellcore', 'plan.lock:strict', 'throttle:api'])
    ->group(function () {
        // Phase 5 - Training (mover desde el grupo soft):
        Route::get('/plan', [TrainingController::class, 'plan']);
        Route::get('/training', [TrainingController::class, 'training']);
        Route::post('/training/toggle', [TrainingController::class, 'toggleTrainingDay']);
        Route::get('/workout/{day?}', [TrainingController::class, 'workout'])->where('day', '[0-9]+');
        Route::post('/workout/start', [TrainingController::class, 'startWorkout']);
        Route::post('/workout/complete-set', [TrainingController::class, 'completeSet']);
        Route::post('/workout/uncomplete-set', [TrainingController::class, 'uncompleteSet']);
        Route::post('/workout/abandon', [TrainingController::class, 'abandonWorkout']);
        Route::post('/workout/finish', [TrainingController::class, 'finishWorkout']);
        Route::get('/workout-summary/{sessionId}', [TrainingController::class, 'workoutSummary'])->where('sessionId', '[0-9]+|latest');
        Route::post('/checkin', [TrainingController::class, 'submitCheckin']);

        // Nutrition
        Route::get('/nutrition/macros-today', [NutritionController::class, 'macrosToday']);
        Route::post('/nutrition/swap', [NutritionController::class, 'createSwap']);
        Route::delete('/nutrition/swap/{id}', [NutritionController::class, 'deleteSwap']);
        Route::post('/ai-nutrition/estimate', [NutritionController::class, 'estimateFood'])->middleware('ensure.plan:elite');
    });

// Grupo NO protegido (status, profile, settings):
Route::prefix('v/client')
    ->middleware(['auth:wellcore', 'throttle:api'])
    ->group(function () {
        Route::get('/account-status', [ClientController::class, 'accountStatus']);
        Route::get('/plan-status', [ClientController::class, 'planStatus']);
        Route::get('/dashboard', [ClientController::class, 'dashboard']);  // dashboard sí accesible (datos viejos OK)
        Route::get('/notifications', [ClientController::class, 'notifications']);
        Route::get('/my-coach', [ClientController::class, 'myCoach']);
        // ... profile, settings, etc.
    });
```

**Test:**
```php
test('client with expired plan cannot access protected endpoints', function () {
    $client = Client::factory()->create(['plan' => 'metodo']);
    AssignedPlan::factory()->for($client)->create(['expires_at' => now()->subDay()]);
    actingAsClient($client);

    $this->getJson('/api/v/client/training')->assertStatus(403);
    $this->postJson('/api/v/client/workout/start', [...])->assertStatus(403);
});

test('client with expired plan CAN still access status endpoints', function () {
    // ... mismo cliente expirado
    $this->getJson('/api/v/client/plan-status')->assertOk();
    $this->getJson('/api/v/client/account-status')->assertOk();
});
```

---

### P0.4 — Idempotencia webhook (race condition extiende plan 60 días)

**Severidad:** HIGH (pero tan crítico que va en P0)
**Archivos:** DB schema (migration aditiva), `app/Services/WompiService.php`
**Riesgo:** Wompi reintenta webhooks. Dos requests concurrentes leen `oldStatus=Pending`, ambos pasan el guard, `ActivateRenewalAction::execute()` corre 2 veces → plan extendido **60 días** + 2 emails + 2x WellCoins.

**Acción 1 — Migration: UNIQUE index en `payments.wompi_reference`:**
```php
// database/migrations/2026_04_26_010000_add_unique_index_to_payments_wompi_reference.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Limpiar duplicados existentes (mantener el más reciente por reference)
        DB::statement("
            DELETE p1 FROM payments p1
            INNER JOIN payments p2
            WHERE p1.id < p2.id
              AND p1.wompi_reference = p2.wompi_reference
              AND p1.wompi_reference IS NOT NULL
        ");

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('wompi_reference', 'ux_payments_wompi_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('ux_payments_wompi_reference');
        });
    }
};
```

**Acción 2 — `lockForUpdate` dentro de la transacción:**
```php
// app/Services/WompiService.php — handleWebhook()

DB::transaction(function () use ($reference, $newStatus, $transactionId, $paymentMethodType) {
    // SELECT ... FOR UPDATE — serializa actualizaciones del mismo Payment
    $payment = Payment::where('wompi_reference', $reference)
        ->lockForUpdate()
        ->first();

    if (! $payment) {
        return false;
    }

    $oldStatus = $payment->status;

    // Idempotencia: si ya estaba aprobado, no hacer nada
    if ($oldStatus === PaymentStatus::Approved) {
        $this->logEvent('webhook.duplicate_ignored', [
            'reference' => $reference,
            'payment_id' => $payment->id,
        ]);
        return;
    }

    $payment->update([
        'status' => $newStatus,
        'wompi_transaction_id' => $transactionId,
        'payment_method' => $paymentMethodType ?? $payment->payment_method,
    ]);

    if ($newStatus === PaymentStatus::Approved && $payment->client_id) {
        Client::where('id', $payment->client_id)->update(['status' => 'activo']);
    }

    // Mover automation FUERA de la transaction usando afterCommit:
    if ($newStatus === PaymentStatus::Approved) {
        DB::afterCommit(fn () => $this->runPostApprovalAutomation($payment));
    }
});
```

**Tests:**
```php
test('duplicate webhook for same reference does not extend plan twice', function () {
    $client = Client::factory()->create(['plan' => 'metodo']);
    AssignedPlan::factory()->for($client)->create(['expires_at' => '2026-04-01']);
    $payment = Payment::factory()->pending()->for($client)->create([
        'wompi_reference' => 'RENEWAL-' . $client->id . '-ABCD-1234',
    ]);

    $payload = $this->makeWompiPayload('APPROVED', $payment->wompi_reference);

    // Primer webhook
    $this->postJson('/webhooks/wompi', $payload, $this->wompiHeaders($payload))->assertOk();
    $expiresAfter1st = $client->assignedPlans->first()->fresh()->expires_at;

    // Segundo webhook idéntico (Wompi retry)
    $this->postJson('/webhooks/wompi', $payload, $this->wompiHeaders($payload))->assertOk();
    $expiresAfter2nd = $client->assignedPlans->first()->fresh()->expires_at;

    expect($expiresAfter1st)->toEqual($expiresAfter2nd);  // no extendido 2 veces
});

test('payments table has UNIQUE constraint on wompi_reference', function () {
    Payment::create(['wompi_reference' => 'WC-DUPE', /* ... */]);
    expect(fn () => Payment::create(['wompi_reference' => 'WC-DUPE', /* ... */]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
```

---

## P1 — HIGH (esta semana)

### P1.1 — Fix `ActivateRenewalAction` quita días pre-pagados

**Severidad:** HIGH
**Archivo:** `app/Actions/ActivateRenewalAction.php`
**Bug:** `update(valid_from=today, expires_at=today+30)` borra días pre-pagados si renueva temprano.

**Fix:**
```php
public function execute(Payment $payment): ?AssignedPlan
{
    // ... guards de P0.1

    return DB::transaction(function () use ($payment) {
        $today = Carbon::now()->toDateString();

        // Buscar planes activos para extender
        $activePlans = AssignedPlan::query()
            ->forClient($payment->client_id)
            ->active()
            ->get();

        if ($activePlans->isEmpty()) {
            // Cliente sin planes activos pagó renovación → notif crítica al admin
            \App\Models\WellcoreNotification::create([
                'user_type' => \App\Enums\UserType::Admin,
                'user_id' => 1,
                'type' => 'renewal_no_active_plan',
                'title' => 'ALERTA: Renovación sin plan activo',
                'body' => "Cliente #{$payment->client_id} pagó \${$payment->amount} pero no tiene planes activos. Asignar plan urgente.",
            ]);
            \Log::error('Renewal without active plans', [
                'payment_id' => $payment->id,
                'client_id' => $payment->client_id,
            ]);
            return null;
        }

        // Para CADA plan, calcular nuevo expires_at = max(current_expires_at, today) + 30 días
        // Esto preserva los días pre-pagados si el cliente renueva temprano.
        foreach ($activePlans as $plan) {
            $current = $plan->expires_at ? Carbon::parse($plan->expires_at) : Carbon::now();
            $base = $current->greaterThan(Carbon::now()) ? $current : Carbon::now();
            $newExpiresAt = $base->copy()->addDays(30)->toDateString();

            $plan->update([
                // valid_from se mantiene si plan estaba vigente, se actualiza si expiró:
                'valid_from' => $current->lessThan(Carbon::now())
                    ? $today
                    : $plan->valid_from,
                'expires_at' => $newExpiresAt,
            ]);
        }

        $latest = $activePlans->fresh()->sortByDesc('expires_at')->first();

        if ($payment->client) {
            $this->lockService->flushCache($payment->client);
        }

        return $latest;
    });
}
```

**Test:**
```php
test('renewal extends from current expires_at when plan still active (preserves prepaid days)', function () {
    Carbon::setTestNow('2026-04-26');
    $client = Client::factory()->create(['plan' => 'metodo']);
    $plan = AssignedPlan::factory()->for($client)->create(['expires_at' => '2026-05-10']);
    $payment = Payment::factory()->renewal()->approved()->for($client)->create();

    (new ActivateRenewalAction(...))->execute($payment);

    expect($plan->fresh()->expires_at->toDateString())->toBe('2026-06-09'); // 2026-05-10 + 30
});

test('renewal of expired plan extends from today (not from past expires_at)', function () {
    Carbon::setTestNow('2026-04-26');
    $plan = AssignedPlan::factory()->create(['expires_at' => '2026-03-01']); // expirado
    // ...
    expect($plan->fresh()->expires_at->toDateString())->toBe('2026-05-26'); // today + 30
});

test('renewal with no active plans creates admin alert and returns null', function () {
    // ... cliente sin planes
    expect((new ActivateRenewalAction(...))->execute($payment))->toBeNull();
    expect(WellcoreNotification::where('type', 'renewal_no_active_plan')->exists())->toBeTrue();
});
```

---

### P1.2 — Fix `PlanLockService::getActivePlan` order incorrecto

**Severidad:** HIGH
**Archivo:** `app/Services/PlanLockService.php` línea 43-47
**Bug:** `orderByDesc('expires_at')` → un plan viejo con `expires_at` lejano (ej. 2027) gana al recién creado. Cliente nunca se lockea.

**Fix:**
```php
public function getActivePlan(Client $client): ?AssignedPlan
{
    if (! $this->isMonthlyPlan($client)) {
        return null;
    }

    // Para determinar lock, usar el plan que expira ANTES (el primer en bloquear).
    // Si hay 3 planes con expires_at distintos, el primero que expire dispara el lock.
    return AssignedPlan::query()
        ->forClient($client->id)
        ->active()
        ->whereNotNull('expires_at')
        ->orderBy('expires_at', 'asc')        // ← cambio clave
        ->first();
}
```

**Test:**
```php
test('getActivePlan returns plan with EARLIEST expires_at (first to lock)', function () {
    $client = Client::factory()->create(['plan' => 'metodo']);
    AssignedPlan::factory()->for($client)->create(['expires_at' => '2026-12-31']);
    AssignedPlan::factory()->for($client)->create(['expires_at' => '2026-05-01']);  // este

    expect((new PlanLockService)->getActivePlan($client)->expires_at->toDateString())
        ->toBe('2026-05-01');
});
```

---

### P1.3 — Hook `creating` no aplica +30 días a planes trial

**Severidad:** HIGH
**Archivo:** `app/Models/AssignedPlan.php`
**Bug:** Trial dura 3 días pero hook le pone 30 → cliente trial obtiene 30 días gratis.

**Fix:**
```php
// app/Models/AssignedPlan.php
protected static function booted(): void
{
    static::creating(function (AssignedPlan $plan) {
        // SECURITY: hard cap valid_from no más de 7 días en futuro
        if ($plan->valid_from) {
            $from = Carbon::parse($plan->valid_from);
            if ($from->greaterThan(Carbon::now()->addDays(7))) {
                throw new \InvalidArgumentException(
                    'valid_from no puede ser más de 7 días en el futuro'
                );
            }
        }

        if ($plan->expires_at) {
            return; // respetar valor explícito
        }

        $from = $plan->valid_from ? Carbon::parse($plan->valid_from) : Carbon::now();

        // Duración por tipo: trial=3, rise=30, presencial=30, monthly=30
        $duration = match (true) {
            str_contains((string) $plan->plan_type, 'trial') => 3,
            default => 30,  // entrenamiento, nutricion, habitos, suplementacion, rise
        };

        $plan->expires_at = $from->copy()->addDays($duration)->toDateString();
    });

    // Flush cache cuando se modifican planes
    static::saved(function (AssignedPlan $plan) {
        if ($plan->client_id) {
            \Illuminate\Support\Facades\Cache::forget("plan_lock_status:{$plan->client_id}");
        }
    });
    static::deleted(function (AssignedPlan $plan) {
        if ($plan->client_id) {
            \Illuminate\Support\Facades\Cache::forget("plan_lock_status:{$plan->client_id}");
        }
    });
}
```

**Tests:**
```php
test('trial plans get 3 days expiry, not 30', function () {
    $plan = AssignedPlan::factory()->create([
        'plan_type' => 'trial',
        'valid_from' => '2026-04-26',
        'expires_at' => null,
    ]);
    expect($plan->fresh()->expires_at->toDateString())->toBe('2026-04-29');
});

test('hook rejects valid_from too far in future (>7 days)', function () {
    expect(fn () => AssignedPlan::factory()->create(['valid_from' => '2099-01-01']))
        ->toThrow(\InvalidArgumentException::class);
});

test('saving an AssignedPlan flushes plan_lock cache', function () {
    Cache::put('plan_lock_status:42', ['stale' => true], 300);
    AssignedPlan::factory()->for(Client::find(42))->create();
    expect(Cache::has('plan_lock_status:42'))->toBeFalse();
});
```

---

### P1.4 — Coach asigna plan ≠ regalar 30 días

**Severidad:** HIGH
**Archivos:** `app/Livewire/Coach/PlansManager.php`, `app/Http/Controllers/Api/AdminController.php`
**Bug:** Cuando coach actualiza el plan, el nuevo se crea con `valid_from=today` y el hook calcula `expires_at=today+30`. Cliente gana días "gratis" cada vez que el coach edita.

**Fix:** Al crear nuevo plan reemplazo dentro del periodo activo, copiar `expires_at` del plan que se desactiva:
```php
// app/Livewire/Coach/PlansManager.php — y todos los lugares similares
// Buscar el plan activo PRE-existente para preservar expires_at
$existing = AssignedPlan::where('client_id', $clientId)
    ->where('plan_type', $planType)
    ->where('active', true)
    ->latest('id')
    ->first();

AssignedPlan::where('client_id', $clientId)
    ->where('plan_type', $planType)
    ->where('active', true)
    ->update(['active' => false]);

$newPlan = AssignedPlan::create([
    'client_id' => $clientId,
    'plan_type' => $planType,
    'content' => $newContent,
    'version' => ($existing?->version ?? 0) + 1,
    'valid_from' => now()->toDateString(),
    // CLAVE: heredar expires_at del plan anterior si todavía vigente
    'expires_at' => $existing?->expires_at && $existing->expires_at->greaterThan(now())
        ? $existing->expires_at
        : null,  // si null, el hook calculará now() + 30
    'active' => true,
    'assigned_by' => $coachId,
]);
```

Aplicar mismo patrón en:
- `app/Http/Controllers/Api/AdminController.php:850, 1540, 2309`
- `app/Livewire/Admin/AIPlanGenerator.php:412`
- `app/Livewire/Admin/ClientDetail.php:105`
- `app/Livewire/Coach/PlansManager.php:371, 697`

**Test:**
```php
test('coach updating plan does NOT extend client expiry', function () {
    Carbon::setTestNow('2026-04-26');
    $client = Client::factory()->create();
    $original = AssignedPlan::factory()->for($client)->create([
        'plan_type' => 'entrenamiento',
        'expires_at' => '2026-05-10',
    ]);

    // Coach reemplaza el plan
    actingAsCoach();
    Livewire::test(PlansManager::class)
        ->call('assignNewVersion', $client->id, 'entrenamiento', $newContent);

    $newPlan = AssignedPlan::where('client_id', $client->id)
        ->where('active', true)->first();

    // expires_at debe ser HEREDADO, no recalculado
    expect($newPlan->expires_at->toDateString())->toBe('2026-05-10');
});
```

---

### P1.5 — Backfill datos legacy + bloqueo si cliente paga sin plan asignado

**Severidad:** HIGH
**Archivos:** Migration aditiva + `app/Services/PlanLockService.php`
**Bug:** Clientes legacy de vanilla PHP tienen `expires_at=NULL` → nunca lockeados. Cliente paga `clients.plan='esencial'` pero coach no asignó `AssignedPlan` → nunca lockeado.

**Fix 1 — Migration de backfill universal:**
```php
// database/migrations/2026_04_26_020000_backfill_assigned_plans_expires_at.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Backfill TODOS los activos sin expires_at
        DB::statement("
            UPDATE assigned_plans
            SET expires_at = DATE_ADD(
                COALESCE(valid_from, created_at, NOW()),
                INTERVAL 30 DAY
            )
            WHERE expires_at IS NULL
              AND active = 1
        ");
    }

    public function down(): void
    {
        // No revertir — sería destructivo
    }
};
```

**Fix 2 — `PlanLockService` retorna `is_locked=true` si cliente paga pero no tiene `AssignedPlan` (forced onboarding):**
```php
// app/Services/PlanLockService.php — computeStatus()
private function computeStatus(Client $client): array
{
    if (! $this->isMonthlyPlan($client)) {
        return [
            'has_plan' => false,
            'is_locked' => false,
            // ...
        ];
    }

    $plan = $this->getActivePlan($client);

    if (! $plan) {
        // Cliente con plan mensual pero SIN assigned_plan → requires onboarding
        return [
            'has_plan' => false,
            'is_locked' => true,
            'is_in_grace' => false,
            'days_until_expiry' => null,
            'expires_at' => null,
            'plan_type' => $client->plan?->value,
            'reason' => 'awaiting_coach_assignment',  // distinguir de "expired"
        ];
    }

    // ... resto igual
}
```

**Frontend `LockOverlay.vue` debe mostrar mensaje distinto cuando `reason === 'awaiting_coach_assignment'`:**
> "Tu coach está preparando tu plan personalizado. Te avisamos cuando esté listo."

**Test:**
```php
test('client with paid plan but no AssignedPlan is locked with awaiting reason', function () {
    $client = Client::factory()->create(['plan' => 'metodo']);
    // NO crear AssignedPlan

    $status = (new PlanLockService)->status($client);
    expect($status['is_locked'])->toBeTrue();
    expect($status['reason'])->toBe('awaiting_coach_assignment');
});
```

---

### P1.6 — Promo expira automáticamente

**Severidad:** HIGH
**Archivos:** `app/Services/PricingService.php` (NUEVO), `config/plans.php`, todos los lugares que leen `config('plans.X.price_cop')`
**Bug:** El 1 mayo a las 00:00 los precios siguen siendo promo (254150) hasta que un humano edite el archivo.

**Acción 1 — Crear `PricingService`:**
```php
// app/Services/PricingService.php
<?php

namespace App\Services;

use Carbon\Carbon;

class PricingService
{
    /**
     * Devuelve el precio actual COP del plan considerando si la promo está activa.
     */
    public function priceCop(string $plan): int
    {
        $cfg = config("plans.{$plan}");

        if (! $cfg) {
            return 0;
        }

        if ($this->isPromoActive()) {
            return (int) ($cfg['price_cop'] ?? 0);
        }

        return (int) ($cfg['price_cop_original'] ?? $cfg['price_cop'] ?? 0);
    }

    public function priceUsd(string $plan): int
    {
        $cfg = config("plans.{$plan}");
        if (! $cfg) return 0;

        return $this->isPromoActive()
            ? (int) ($cfg['price_usd'] ?? 0)
            : (int) ($cfg['price_usd_original'] ?? $cfg['price_usd'] ?? 0);
    }

    public function isPromoActive(): bool
    {
        if (! config('plans.promo.active', false)) {
            return false;
        }

        $endsAt = config('plans.promo.ends_at');
        if (! $endsAt) {
            return true; // sin fecha de fin = promo indefinida
        }

        return Carbon::now()->lessThanOrEqualTo(Carbon::parse($endsAt)->endOfDay());
    }

    public function discountPercent(string $plan): int
    {
        if (! $this->isPromoActive()) return 0;

        $orig = (int) config("plans.{$plan}.price_cop_original", 0);
        $current = (int) config("plans.{$plan}.price_cop", 0);

        if ($orig <= 0 || $current >= $orig) return 0;

        return (int) round(($orig - $current) / $orig * 100);
    }
}
```

**Acción 2 — Reemplazar TODOS los `config("plans.*.price_cop")`:**
```bash
# Buscar todas las ocurrencias:
grep -rn "config('plans.*price_cop'" app/ resources/views/ | grep -v _original
```

Reemplazar por `app(PricingService::class)->priceCop($plan)`.

**Lugares conocidos a actualizar:**
- `app/Livewire/Checkout.php:54` (en `getPlans()`)
- `app/Services/CurrencyService.php:90`
- `app/Console/Commands/AutoRenewalCommand.php:160`
- `resources/views/public/home.blade.php:52-54` (JSON-LD)
- `resources/views/public/planes.blade.php:16-18` (JSON-LD)
- `resources/views/public/metodo.blade.php:30` (JSON-LD)

**Test:**
```php
test('PricingService returns promo price during promo window', function () {
    Carbon::setTestNow('2026-04-26 12:00');  // dentro de la promo abril
    expect(app(PricingService::class)->priceCop('metodo'))->toBe(339150);
});

test('PricingService returns original price after promo ends', function () {
    Carbon::setTestNow('2026-05-01 00:01');  // post-promo
    expect(app(PricingService::class)->priceCop('metodo'))->toBe(399000);
});

test('isPromoActive returns false when promo.active = false', function () {
    config(['plans.promo.active' => false]);
    expect(app(PricingService::class)->isPromoActive())->toBeFalse();
});
```

---

### P1.7 — Timezone Colombia en cron + cache fresh

**Severidad:** HIGH
**Archivos:** `config/app.php`, `app/Console/Commands/AutoRenewalCommand.php`
**Bug:** Cron corre 07:00 UTC = 02:00 COL. Cliente recibe email "expira hoy" 22h antes de tiempo.

**Fix 1 — Setear timezone:**
```php
// config/app.php
'timezone' => 'America/Bogota',
```

**Fix 2 — Usar timezone del cliente en `AutoRenewalCommand`:**
```php
// app/Console/Commands/AutoRenewalCommand.php
$today = Carbon::now($client->timezone ?? 'America/Bogota')->startOfDay();
```

**Fix 3 — Mismo en `PlanLockService`:**
```php
public function isExpired(): bool
{
    if (! $this->expires_at) return false;
    $tz = optional($this->client)->timezone ?? 'America/Bogota';
    return Carbon::parse($this->expires_at)->startOfDay()
        ->lessThanOrEqualTo(Carbon::now($tz)->startOfDay());
}
```

---

### P1.8 — Webhook event whitelist

**Severidad:** HIGH
**Archivo:** `app/Services/WompiService.php`
**Bug:** Solo procesa `transaction.updated`. Eventos de chargeback/refund se pierden silenciosamente.

**Fix:**
```php
public function handleWebhook(array $payload): bool
{
    $event = $payload['event'] ?? '';

    return match ($event) {
        'transaction.updated' => $this->processTransactionUpdate($payload),
        'transaction.created' => $this->logEvent('webhook.created_ignored', ['event' => $event]) ?? true,
        default => $this->logUnsupportedEvent($event, $payload),
    };
}

private function logUnsupportedEvent(string $event, array $payload): bool
{
    $this->logEvent('webhook.unsupported_event', [
        'event' => $event,
        'transaction_id' => data_get($payload, 'data.transaction.id'),
    ]);
    \Log::warning('Wompi unsupported event', ['event' => $event]);

    // Notificar admin para investigar
    \App\Models\WellcoreNotification::create([
        'user_type' => \App\Enums\UserType::Admin,
        'user_id' => 1,
        'type' => 'wompi_unsupported_event',
        'title' => 'Wompi: evento no soportado',
        'body' => "Evento '{$event}' recibido pero no procesado. Investigar.",
    ]);

    return false;
}
```

---

## P2 — MEDIUM (próximo sprint)

### P2.1 — `aplicarDescuento` aplicar sobre precio original (no apilar)
**Archivo:** `app/Livewire/Checkout.php`
**Política:** "best discount wins" — comparar promo % vs código %, aplicar el mayor sobre `price_cop_original`.

```php
public function aplicarDescuento(): void
{
    $codigos = ['WELLCORE10' => 10, 'RISE20' => 20, 'FITSTART' => 15];
    $code = strtoupper(trim($this->codigoDescuento));
    $codePct = $codigos[$code] ?? 0;

    if ($codePct === 0) {
        $this->descuento = 0;
        $this->descuentoMensaje = 'Codigo no valido';
        return;
    }

    $original = (int) config("plans.{$this->plan}.price_cop_original");
    $promoPct = app(PricingService::class)->discountPercent($this->plan);
    $bestPct = max($codePct, $promoPct);

    // Recalcular precio aplicando solo el mejor descuento
    $newPrice = (int) round($original * (100 - $bestPct) / 100);

    $this->price = $newPrice;
    $this->descuento = 0;  // ya aplicado en $price
    $this->descuentoMensaje = "Descuento {$bestPct}% aplicado (mejor opcion)";
}
```

### P2.2 — Limpieza de payments pending huérfanos
**Crear:** `app/Console/Commands/CleanupPendingPaymentsCommand.php`
```php
// Ejecutar diario
$expired = Payment::where('status', PaymentStatus::Pending)
    ->where('created_at', '<', now()->subHours(24))
    ->update(['status' => PaymentStatus::Voided]);
```

### P2.3 — Encrypted payment_logs.payload
```php
// app/Models/PaymentLog.php — casts
'payload' => 'encrypted:array',
```
+ migration que sanitize datos existentes (whitelist de campos).

### P2.4 — Reduce auth_token TTL de 30 días a 7 días + refresh

### P2.5 — RenewalBanner localStorage key incluir client_id

### P2.6 — Validar currency en webhook

```php
// WompiService::handleWebhook
$txCurrency = data_get($payload, 'data.transaction.currency', 'COP');
if ($txCurrency !== ($payment->currency ?? 'COP')) {
    \Log::critical('Currency mismatch in webhook', [...]);
    return false;
}
```

### P2.7 — `isRenewal` regex estricta
```php
public function isRenewal(): bool
{
    return is_string($this->wompi_reference)
        && preg_match('/^RENEWAL-\d+-[A-F0-9]{8}-\d{10}$/', $this->wompi_reference);
}
```

### P2.8 — Random 128-bit en RENEWAL- reference
```php
$this->paymentReference = sprintf(
    'RENEWAL-%d-%s-%d',
    $this->renewalClientId,
    strtoupper(bin2hex(random_bytes(16))),  // 128 bits
    time()
);
```

---

## P3 — TESTING INFRASTRUCTURE (CRÍTICO antes de escalar tests)

### P3.1 — Crear DB de testing aislada

**Riesgo actual:** Tests Feature que persistan datos escriben en `wellcore_fitness` (DB de prod). `RefreshDatabase` está comentado en `tests/Pest.php:17`. **PELIGRO ALTO de corromper prod.**

**Fix:**
```bash
# 1. Crear DB de tests en MySQL local Y en EasyPanel
mysql -u root -p -e "CREATE DATABASE wellcore_fitness_test;"

# 2. Importar schema (solo estructura, sin datos)
mysqldump -u wellcorefitness -p --no-data wellcore_fitness | mysql -u root wellcore_fitness_test
```

```xml
<!-- phpunit.xml — agregar override -->
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_DATABASE" value="wellcore_fitness_test"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="MAIL_MAILER" value="array"/>
</php>
```

```php
// tests/Pest.php — descomentar línea 17:
pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
```

### P3.2 — Crear `AssignedPlanFactory`

```php
// database/factories/AssignedPlanFactory.php
<?php
namespace Database\Factories;

use App\Models\AssignedPlan;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignedPlanFactory extends Factory
{
    protected $model = AssignedPlan::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'plan_type' => 'entrenamiento',
            'content' => ['weeks' => []],
            'version' => 1,
            'valid_from' => now()->toDateString(),
            'expires_at' => null, // hook calculará
            'active' => true,
            'assigned_by' => null,
        ];
    }

    public function active(): static { return $this->state(fn() => ['active' => true]); }
    public function expired(): static { return $this->state(fn() => ['expires_at' => now()->subDay()]); }
    public function expiresAt(string $date): static { return $this->state(fn() => ['expires_at' => $date]); }
    public function trial(): static { return $this->state(fn() => ['plan_type' => 'trial']); }
}
```

### P3.3 — Helper `actingAsClient()` en `tests/Pest.php`

```php
// tests/Pest.php
function actingAsClient(\App\Models\Client $client): \App\Models\Client
{
    $token = \App\Models\AuthToken::create([
        'token' => bin2hex(random_bytes(32)),
        'user_type' => 'client',
        'user_id' => $client->id,
        'expires_at' => now()->addDay(),
    ]);
    test()->withHeaders(['Authorization' => "Bearer {$token->token}"]);
    return $client;
}
```

### P3.4 — Coverage instalado

```bash
composer require --dev pcov/clobber
```

```xml
<!-- phpunit.xml -->
<coverage>
    <report>
        <html outputDirectory="coverage-html"/>
        <text outputFile="coverage.txt"/>
    </report>
</coverage>
<source>
    <include>
        <directory>app</directory>
    </include>
</source>
```

### P3.5 — Tests faltantes a escribir (en orden)

**Bloque A — Unit/PlanLockService** (`tests/Feature/Services/PlanLockServiceTest.php`):
- `getActivePlan returns null for rise/presencial/trial`
- `getActivePlan returns plan with EARLIEST expires_at` (P1.2)
- `isLocked true when plan expired`
- `isLocked false during grace period`
- `isInGracePeriod boundary tests` (datasets 1-7 days)
- `status() caches for 5 min`
- `flushCache invalidates key`

**Bloque B — Unit/ActivateRenewalAction** (`tests/Feature/Actions/ActivateRenewalActionTest.php`):
- `extends ALL active assigned plans`
- `preserves prepaid days when renewing early` (P1.1)
- `extends from today when plan already expired`
- `creates admin alert when no active plans` (P1.1)
- `is transactional`
- `flushes lock cache`

**Bloque C — Feature/RenewalWebhook** (`tests/Feature/Payment/WompiRenewalWebhookTest.php`):
- `RENEWAL- webhook activates renewal` (skip welcome email)
- `duplicate webhook does NOT extend twice` (P0.4)
- `webhook with reference client_id mismatch is rejected` (P0.1)
- `webhook with amount mismatch is rejected` (P0.2)
- `unsupported event creates admin notif` (P1.8)

**Bloque D — Feature/CheckPlanLock middleware** (`tests/Feature/Middleware/CheckPlanLockTest.php`):
- `soft mode injects attribute, never blocks`
- `strict mode returns 403 JSON for api/*`
- `strict mode redirects HTML to /renovar`
- `protected endpoints reject expired clients` (P0.3)
- `status endpoints accessible even with expired plan`

**Bloque E — Feature/AutoRenewalCommand**:
- `queues PlanExpiring 5 days before expiry`
- `dedupe: client with 3 plans gets 1 email`
- `idempotent: same day no double email`
- `flushes cache when expires today`
- `respects client timezone` (P1.7)

**Bloque F — Feature/Checkout (renewal)**:
- `mount /renovar prefills auth client`
- `prepareWompiPayment generates RENEWAL- ref`
- `unauthenticated /renovar redirects to login` (P0.1)
- `manipulated renewalClientId is overridden` (P0.1)
- `manipulated descuento is rejected` (P0.2)

**Bloque G — Unit/AssignedPlan booted**:
- `auto sets expires_at = valid_from + 30`
- `respects explicit expires_at`
- `trial gets 3 days` (P1.3)
- `rejects valid_from > 7 days future`
- `saved() flushes cache`

**Bloque H — Unit/PricingService**:
- `returns promo price during window`
- `returns original after promo ends` (P1.6)
- `discountPercent calculation`

**Bloque I — Refactor PlanLockTest existente**:
- Cambiar tests hardcoded a fórmula:
```php
test('promo prices honor discount_pct', function () {
    foreach (['esencial','metodo','elite'] as $plan) {
        $orig = config("plans.{$plan}.price_cop_original");
        $current = config("plans.{$plan}.price_cop");
        $pct = config('plans.promo.discount_pct');
        $expected = (int) round($orig * (100 - $pct) / 100);
        expect($current)->toBe($expected);
    }
})->skip(fn () => ! app(PricingService::class)->isPromoActive(), 'Promo not active');
```

**Total tests a escribir: ~45-50.**

---

## Orden de implementación recomendado

```
DÍA 1 — Blockers críticos
├── P0.1 — Auth + Locked properties (1-2h)
├── P0.2 — Amount validation (1h)
├── P0.3 — plan.lock:strict en rutas API (1h, requiere QA)
├── P0.4 — Migration UNIQUE + lockForUpdate (1-2h)
└── Compilar local + push + gitpull-load + migrate-only

DÍA 2 — High priority
├── P1.1 — ActivateRenewalAction preserva días (2h + tests)
├── P1.2 — getActivePlan order (30min + test)
├── P1.3 — Hook trial 3 días + cache flush (1h + tests)
└── P1.5 — Backfill legacy + lock awaiting onboarding (2h)

DÍA 3 — Robustez
├── P1.4 — Coach/admin no regalan días (3h, varios archivos)
├── P1.6 — PricingService + reemplazar config() calls (4h)
├── P1.7 — Timezone Colombia (30min)
└── P1.8 — Webhook event whitelist (1h)

DÍA 4 — Testing infrastructure
├── P3.1 — DB testing aislada (CRÍTICO antes de seguir)
├── P3.2 — AssignedPlanFactory
├── P3.3 — Helper actingAsClient
├── P3.4 — Coverage tooling
└── Bloque A + B tests (PlanLockService + ActivateRenewalAction)

DÍA 5 — Tests críticos
├── Bloque C (Webhook renewal)
├── Bloque D (Middleware)
└── Bloque F (Checkout renewal)

DÍA 6 — Tests restantes
├── Bloques E, G, H, I

DÍA 7 — Medium priority
└── P2.1 a P2.8
```

---

## Criterios de aceptación

✅ **Build green:** `php artisan test` pasa todos (incluyendo nuevos)
✅ **Coverage:** ≥80% en `app/Services/PlanLockService.php`, `app/Actions/ActivateRenewalAction.php`, `app/Services/WompiService.php` rama renewal
✅ **Lint:** `php artisan pint --test` clean
✅ **Migrations:** `php artisan migrate --pretend` muestra solo aditivas
✅ **Manual smoke test (en consola del container EasyPanel):**
```bash
# Verificar que cliente con plan vigente NO está locked
php artisan tinker --execute="
  \$c = App\Models\Client::find(46);
  echo json_encode(app(App\Services\PlanLockService::class)->status(\$c), JSON_PRETTY_PRINT);
"

# Verificar que descuento codigo no compone con promo
php artisan tinker --execute="
  echo app(App\Services\PricingService::class)->priceCop('metodo');
"
```

✅ **Production deploy:**
```bash
# 1. npm run build LOCAL
# 2. git add public/build/ && git commit
# 3. git push origin main
# 4. EasyPanel: silvia-gitpull-load (NUNCA npm-build)
# 5. Consola container: cd /code && php artisan migrate --force
# 6. Consola container: php artisan cache:clear
# 7. Verificar /renovar, /api/v/client/plan-status, smoke test arriba
```

---

## Anexo A — Lista completa de hallazgos por agente

### Auditoría payments (la-09-payments)
- C-1: Manipulación monto Livewire (FIXED en P0.2)
- C-2: Forgery RENEWAL- (FIXED en P0.1)
- H-1: Sin UNIQUE wompi_reference (FIXED en P0.4)
- H-2: ActivateRenewalAction quita días (FIXED en P1.1)
- H-3: Renewal sin client_id silente (FIXED en P0.1)
- H-4: Promo no expira (FIXED en P1.6)
- H-5: Descuentos compuestos (FIXED en P2.1)
- M-1: Automation fuera de transaction (FIXED en P0.4 con afterCommit)
- M-2: Doble pending (FIXED en P2.2)
- M-3: Cliente sin planes paga (FIXED en P1.1)
- M-4: Plan mismatch admin
- M-5: events_secret vacío silente (FIXED en P1.8 con health check)
- M-6: PII en payment_logs (FIXED en P2.3)
- L-1: isRenewal regex (FIXED en P2.7)
- L-2: amountInCents = 0 (FIXED en P0.2)
- L-3: Currency hardcoded
- L-4: Webhook expuesto sin IP allowlist

### Auditoría seguridad (la-05-security)
- C-01: IDOR Livewire (FIXED en P0.1)
- C-02: Amount manipulation (FIXED en P0.2)
- H-01: /renovar sin auth (FIXED en P0.1)
- H-02: plan.lock no enforced (FIXED en P0.3) ← **EL MÁS IMPORTANTE**
- H-03: Cache stale coach (FIXED en P1.3 con saved hook)
- H-04: valid_from sin límite (FIXED en P1.3)
- H-05: events_secret vacío (FIXED en P1.8)
- M-01: RenewalBanner localStorage (FIXED en P2.5)
- M-02: LockOverlay removable (mitigado por P0.3 server-side)
- M-03: Bearer en localStorage (FIXED en P2.4)
- M-04: Race webhook (FIXED en P0.4)
- M-05: PII payment_logs (FIXED en P2.3)
- M-06: Predictable RENEWAL- ts (FIXED en P2.8)
- L-01: plan_type expone interno
- L-02: Polling no se detiene en logout
- L-03: Timezone inconsistencia (FIXED en P1.7)
- L-04: RenewalBanner spam

### Auditoría backend (la-02-backend)
- A1: Renewal regala/quita días (FIXED en P1.1)
- A2: Trial 30 días (FIXED en P1.3)
- A5: orderByDesc incorrecto (FIXED en P1.2)
- B7: Pierde historial al renovar (FIXED en P1.1 con preservar plan viejo)
- C8: Timezone UTC (FIXED en P1.7)
- D13: amount=0 (FIXED en P0.2)
- D14: IDOR renewalClientId (FIXED en P0.1)
- E16: Webhook event filtering (FIXED en P1.8)
- E17: Voided→Approved manda Welcome (FIXED en P0.4)
- F20: Promo no expira (FIXED en P1.6)
- F22: Tests hardcoded (FIXED en P3.5 bloque I)
- G23: Backfill legacy (FIXED en P1.5)
- G24: Cliente paga sin assigned_plan (FIXED en P1.5)
- H25: Coach regala días (FIXED en P1.4)
- I28: Hardcoded admin user_id=1
- I30: Try/catch bloquea steps siguientes

### Auditoría testing (la-14-testing)
- Cobertura ~10% (FIXED en P3 completo)
- DB shared con prod (FIXED en P3.1)
- AssignedPlanFactory falta (FIXED en P3.2)
- ~45 tests faltantes (FIXED en P3.5)
- PlanLockTest hardcoded (FIXED en P3.5 bloque I)
- Sin coverage tooling (FIXED en P3.4)

---

## Anexo B — Comandos rápidos para Claude Sonnet

```bash
# Inicio
cd C:\Users\GODSF\Herd\wellcore-laravel
git pull origin main

# Lint cualquier archivo PHP modificado
/c/Users/GODSF/.config/herd/bin/php.bat -l <archivo>

# Test local
/c/Users/GODSF/.config/herd/bin/php.bat artisan test --filter=PlanLock
/c/Users/GODSF/.config/herd/bin/php.bat artisan test  # full

# Compilar local (NUNCA en server)
npm run build  # ~2 segundos con Vite

# Deploy seguro
git add app/ database/migrations/ tests/ resources/js/ public/build/
git commit -m "fix(audit): aplica P0.x ..."
git push origin main
# Luego en EasyPanel: silvia-gitpull-load (NUNCA npm-build)
# Luego en consola container: cd /code && php artisan migrate --force
```

---

**Fin del plan. ~50 fixes priorizados, ~45 tests faltantes, ~7 días de trabajo.**
