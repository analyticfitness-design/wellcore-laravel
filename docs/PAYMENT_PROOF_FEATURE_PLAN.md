# Plan de Implementación — Comprobantes de Pago Externo

**Feature:** Pestaña en perfil de coach para subir comprobantes de pagos externos (no-Wompi), abrir ticket de revisión al superadmin, y generar invitación sin pago tras aprobación.

**Fecha:** 2026-04-26
**Owner:** Daniel Esparza
**Estado:** Plan aprobado — listo para implementación
**Doc relacionado:** este documento es la fuente única de verdad (single source of truth) para la feature

---

## 1. Contexto y objetivo

### Problema
Hoy WellCore solo procesa pagos vía **Wompi** (pasarela). Cuando un cliente paga por **transferencia, efectivo, Nequi, u otro medio externo**, no hay un flujo automatizado: el coach debe contactar manualmente al superadmin, enviarle el comprobante por chat, y esperar que el superadmin cree una invitación a mano.

### Solución
Una pestaña nueva **"Comprobantes"** en el perfil del coach (`CoachProfile.vue`) donde:
1. El coach sube **foto del comprobante** + **nombre del cliente** + **email del cliente** + **plan** + nota opcional.
2. Se abre un **ticket** que el superadmin revisa en su dashboard.
3. Al aprobar, el sistema crea una **invitación con coach asignado** (vía `CoachInvitation`) y envía el link de acceso al cliente automáticamente.
4. Al rechazar, el coach recibe email con la razón y puede re-subir.

### Valor de negocio
- **Coach:** elimina fricción de coordinación 1:1 con superadmin para cada pago externo.
- **Superadmin:** centraliza revisiones en un dashboard con filtros, evita perder comprobantes en chats.
- **Cliente:** recibe acceso más rápido (mismo flujo que pago Wompi exitoso).
- **Negocio:** preserva atribución de coach (comisiones), audit trail, anti-fraude.

---

## 2. Auditoría del estado actual

### 2.1 Lo que ya existe y vamos a reusar

| Componente | Path | Uso en esta feature |
|---|---|---|
| `CoachInvitation` (model + tabla) | `app/Models/CoachInvitation.php` | Invitación creada al aprobar comprobante (preserva atribución coach → comisiones) |
| `CoachInvitationService::handlePaymentApproved()` | `app/Services/CoachInvitationService.php:219` | Crea/activa `Client`, vincula `ClientCoach`, marca invitación |
| `Payment` (model + tabla) | `app/Models/Payment.php` | Crear payment con `wompi_reference="MAN-{uuid}"` y `status=Approved` |
| `PaymentStatus` (enum) | `app/Enums/PaymentStatus.php` | Estados del payment (Approved al aprobar comprobante) |
| `PlanType` (enum) | `app/Enums/PlanType.php` | Plan del cliente |
| `PlanTicketAttachment` (patrón) | `app/Models/PlanTicketAttachment.php` | Patrón probado de attachments con disco configurable |
| `ImagePipelineService` | `app/Services/ImagePipelineService.php` | Conversión a WebP + fallback (probado en logos coach) |
| `WellcoreNotification` (`user_type='admin'`) | `app/Models/WellcoreNotification.php` | Notificar al superadmin in-app sin push |
| `CoachProfile.vue` (página coach con tabs) | `resources/js/vue/pages/Coach/CoachProfile.vue` | Anclar la nueva pestaña "Comprobantes" |
| `CoachLayout.vue` | `resources/js/vue/layouts/CoachLayout.vue` | Layout wrapper |
| Endpoint pattern de upload | `app/Http/Controllers/Api/CoachBrandController.php:99` | Plantilla de validación + storage |
| Middleware `coach.contract` | `routes/api.php:184-259` | Garantiza que solo coaches con contrato vigente acceden |
| `CoachClientInvitation` (mailable) | `app/Mail/CoachClientInvitation.php` | Inspiración para `PaymentProofApproved` mailable |
| `AuditService::logAction()` | `app/Services/AuditService.php` | Audit log de aprobaciones/rechazos |
| `Admin/Dashboard.php` (Livewire) | `app/Livewire/Admin/Dashboard.php` | Anclar widget "Comprobantes pendientes" |

### 2.2 Brechas a cubrir

| Brecha | Solución |
|---|---|
| No existe modelo `PaymentProof` ni tabla equivalente | Crear migración aditiva `payment_proofs` |
| No hay disco privado configurado para comprobantes | Configurar disco `payment_proofs` (local privado o S3 cifrado) |
| `WellcoreNotification` no tiene UI bell para admin (solo cliente) | MVP: widget en dashboard + email; V2: bell admin |
| Sin rate limiting específico para upload de comprobantes | Throttle 10/día/coach + 30/hora |
| Sin policy para `PaymentProof` | Crear `PaymentProofPolicy` (coach solo ve los suyos, admin ve todos) |
| El email de invitación post-aprobación manual no existe | Crear `PaymentProofApproved` mailable |
| Sin comando para auto-expirar comprobantes viejos | Crear `wellcore:expire-payment-proofs` (cron diario) |

---

## 3. Decisiones tomadas (sobre las opciones planteadas)

| # | Decisión | Opción elegida | Justificación |
|---|---|---|---|
| 1 | Modelo de invitación al aprobar | **A) `CoachInvitation`** | Preserva atribución coach → comisiones, email automático, integra `ClientCoach` |
| 2 | Quién selecciona el plan | **A) Coach al subir** | Coach ya conoce al cliente, evita ida-y-vuelta |
| 3 | Disco para comprobantes | **A) Privado + URL firmada** | Datos sensibles (cuentas, montos), URL temporal de 5 min |
| 4 | Auto-expiración del ticket | **A) 7 días sin revisión** | Fuerza SLA al admin, evita acumulación |
| 5 | Quién envía link al cliente | **A) Email automático** | Cero fricción, paridad con flujo Wompi |

---

## 4. Arquitectura

### 4.1 Diagrama de flujo

```
┌──────────────────────────────────────────────────────────────────┐
│ COACH (Vue 3 — pestaña "Comprobantes" en /coach/profile)        │
│ ┌────────────────────────────────────────────────────────────┐  │
│ │ Form: foto + nombre cliente + email cliente + plan + nota  │  │
│ │ + medio de pago (transfer/efectivo/nequi/otro) + monto opc │  │
│ └────────────────────────────────────────────────────────────┘  │
└──────────────────────────┬───────────────────────────────────────┘
                           │ POST /api/v/coach/payment-proofs
                           │ (multipart/form-data)
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│ PaymentProofController@store                                     │
│ ─ throttle: 10/día, 30/hora por coach                            │
│ ─ validate: mime jpg/png/pdf, max 10MB, email válido,            │
│   plan en enum, dedupe (no aceptar mismo email pendiente)        │
│ ─ Storage::disk('payment_proofs')->putFile() → privado           │
│ ─ PaymentProof::create(status=pendiente, expires_at=now+7d)      │
│ ─ WellcoreNotification::create(user_type=admin, type=...)        │
│ ─ Mail::to(superadmin)->send(PaymentProofPending)  [opcional]    │
│ ─ AuditService::logAction('payment_proof.submitted')             │
│ ─ return: 201 + payload                                          │
└──────────────────────────┬───────────────────────────────────────┘
                           │
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│ SUPERADMIN (Livewire — /admin/payment-proofs)                   │
│ ─ Lista filtrable: pendiente / aprobado / rechazado / expirado   │
│ ─ Click en fila → modal con thumbnail (URL firmada 5 min)        │
│ ─ Datos: coach, cliente, email, plan, monto, fecha, nota         │
│ ─ Botones: [Aprobar] [Rechazar (con razón)] [Solicitar info]     │
│ ─ Widget en /admin/dashboard: badge con count pendientes         │
└─────────────┬─────────────────────────────────────┬──────────────┘
              │ approve(id)                          │ reject(id, reason)
              ▼                                     ▼
┌────────────────────────────────────┐ ┌────────────────────────────┐
│ ApprovePaymentProofAction          │ │ RejectPaymentProofAction   │
│ DB::transaction:                    │ │ ─ status=rechazado         │
│ 1. status=aprobado, reviewed_by=*  │ │ ─ review_note=razón        │
│ 2. CoachInvitation::create(        │ │ ─ Mail to coach            │
│      coach_id, email, name, plan,  │ │ ─ Audit log                │
│      wompi_reference="MAN-{uuid}") │ └────────────────────────────┘
│ 3. Payment::create(status=Approved │
│      wompi_reference, plan, ...)   │
│ 4. CoachInvitationService::        │
│      handlePaymentApproved(        │
│        payment, invitation)        │
│    → crea Client                   │
│    → vincula ClientCoach           │
│    → marca CoachInvitation=Paid    │
│ 5. Mail::PaymentProofApproved      │
│      al cliente (con link login)   │
│ 6. Notify coach (in-app + email)   │
│ 7. Audit log                       │
└────────────────────────────────────┘
```

### 4.2 Decisiones de diseño clave

- **Reuso de `CoachInvitationService::handlePaymentApproved()`:** evita duplicar la lógica de creación de cliente + asignación de coach. Esta función ya está probada en producción con el flujo Wompi.
- **`Payment` con `wompi_reference="MAN-{uuid}"`:** prefijo `MAN-` (manual) para distinguir de `WCI-` (Wompi Coach Invitation). Permite filtrar reportes financieros y trazar pagos manuales.
- **Audit log obligatorio en cada transición:** quién subió, quién aprobó/rechazó, cuándo, razón. Compliance y anti-fraude.
- **Disco privado con URL firmada temporal:** los comprobantes pueden contener números de cuenta, nombres completos, montos. Nunca exponerlos públicamente.

---

## 5. Esquema de base de datos

### 5.1 Migración aditiva

**Archivo:** `database/migrations/2026_04_27_000000_create_payment_proofs_table.php`

```php
Schema::create('payment_proofs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('coach_id')->constrained('admins')->cascadeOnDelete();
    $table->string('client_email', 255);
    $table->string('client_name', 255);
    $table->enum('plan', ['rise', 'esencial', 'metodo', 'elite', 'presencial']);
    $table->decimal('amount', 10, 2)->nullable();
    $table->char('currency', 3)->default('COP');
    $table->string('payment_method', 50)->nullable();   // transferencia, efectivo, nequi, otro
    $table->string('file_path', 500);
    $table->string('file_disk', 20)->default('payment_proofs');
    $table->string('file_mime', 50);
    $table->unsignedInteger('file_size');
    $table->text('coach_note')->nullable();
    $table->enum('status', ['pendiente', 'aprobado', 'rechazado', 'expirado'])
          ->default('pendiente');
    $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
    $table->text('review_note')->nullable();
    $table->foreignId('coach_invitation_id')->nullable()
          ->constrained('coach_invitations')->nullOnDelete();
    $table->foreignId('payment_id')->nullable()
          ->constrained('payments')->nullOnDelete();
    $table->timestamp('submitted_at');
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();

    $table->index('status');
    $table->index(['coach_id', 'status']);
    $table->index('client_email');
    $table->index('expires_at');
});
```

⚠️ **REGLA CRÍTICA:** ver `feedback_db_safety.md` — esta tabla es **100% nueva** y aditiva. NO se modifican `payments`, `clients`, `admins`, `coach_invitations`. Las FKs apuntan a tablas existentes pero no las alteran.

### 5.2 Configuración de disco privado

**Archivo:** `config/filesystems.php` (añadir bloque)

```php
'payment_proofs' => [
    'driver' => 'local',
    'root' => storage_path('app/private/payment_proofs'),
    'visibility' => 'private',
    'throw' => false,
],
```

Si en el futuro se migra a S3 con KMS, cambiar driver a `s3` con bucket cifrado.

⚠️ Añadir a `.gitignore`: `storage/app/private/payment_proofs/*`

---

## 6. Contratos de API

### 6.1 Coach endpoints

#### `POST /api/v/coach/payment-proofs`
**Auth:** `auth:wellcore` + `role:coach,admin,superadmin,jefe` + `coach.contract`
**Throttle:** `10,1440` (10 por día) + `30,60` (30 por hora)
**Body:** `multipart/form-data`

| Campo | Tipo | Validación |
|---|---|---|
| `file` | File | required, image/pdf, max:10240 (10MB) |
| `client_name` | String | required, max:255 |
| `client_email` | String | required, email, max:255, unique check (no pendiente del mismo coach) |
| `plan` | String | required, in: rise,esencial,metodo,elite,presencial |
| `amount` | Decimal | nullable, min:0 |
| `payment_method` | String | nullable, in: transferencia,efectivo,nequi,otro |
| `coach_note` | String | nullable, max:1000 |

**Respuesta 201:**
```json
{
  "id": 123,
  "status": "pendiente",
  "submitted_at": "2026-04-27T10:00:00Z",
  "expires_at": "2026-05-04T10:00:00Z",
  "client_email": "...",
  "plan": "metodo"
}
```

**Errores:**
- `422` validación falla
- `409` ya existe comprobante pendiente para ese email
- `429` rate limit

#### `GET /api/v/coach/payment-proofs`
Lista paginada de los comprobantes del coach autenticado.
Query params: `status`, `from_date`, `to_date`, `page`, `per_page`.

#### `GET /api/v/coach/payment-proofs/{id}`
Detalle (solo si `coach_id == auth()->id()` — enforced por `PaymentProofPolicy`).

### 6.2 Admin endpoints

#### `GET /api/v/admin/payment-proofs`
Lista paginada de TODOS los comprobantes. Filtros: `status`, `coach_id`, `from_date`, `to_date`.

#### `GET /api/v/admin/payment-proofs/{id}/file`
Devuelve **URL firmada temporal** (5 min) para ver el archivo:
```json
{ "url": "https://...?signature=...&expires=1714210800" }
```

#### `POST /api/v/admin/payment-proofs/{id}/approve`
Dispara `ApprovePaymentProofAction`.

#### `POST /api/v/admin/payment-proofs/{id}/reject`
**Body:** `{ "review_note": "razón clara" }`
Dispara `RejectPaymentProofAction`.

---

## 7. Flujos de UI

### 7.1 Coach — pestaña "Comprobantes"

**Anclaje:** `resources/js/vue/pages/Coach/CoachProfile.vue` añade tab `comprobantes` a la lista existente (`profile`, `referrals`, `revenue` → ahora también `comprobantes`).

**Componentes nuevos:**
- `resources/js/vue/components/Coach/PaymentProofUploader.vue` — formulario + dropzone + preview
- `resources/js/vue/components/Coach/PaymentProofList.vue` — tabla historial con badges de estado

**Estados visuales (Tailwind + design tokens WellCore):**
- `pendiente` → badge amarillo (`bg-yellow-500/15 text-yellow-300`)
- `aprobado` → badge verde
- `rechazado` → badge rojo (`bg-wc-accent/15 text-wc-accent`) con tooltip que muestra `review_note`
- `expirado` → badge gris

### 7.2 Admin — sección "Comprobantes pendientes"

**Anclaje:** `app/Livewire/Admin/PaymentProofReview.php` (nuevo) accesible desde `/admin/payment-proofs`.

**Widget en `Admin/Dashboard.php`:**
```blade
<div class="card">
  <h3>Comprobantes pendientes</h3>
  <div class="text-3xl font-bold">{{ $pendingProofsCount }}</div>
  <a href="/admin/payment-proofs?status=pendiente">Revisar →</a>
</div>
```

**Modal de revisión:**
- Thumbnail del comprobante (carga via URL firmada)
- Datos: coach, cliente, email, plan, monto, fecha, nota
- Botones: Aprobar / Rechazar (con textarea para razón) / Cerrar
- Confirmación antes de aprobar (irreversible — crea cliente)

---

## 8. Archivos a crear / modificar

### 8.1 Backend (PHP)

| Archivo | Acción | Agente |
|---|---|---|
| `database/migrations/2026_04_27_000000_create_payment_proofs_table.php` | CREAR | `la-06-database` |
| `app/Models/PaymentProof.php` | CREAR | `la-02-backend` |
| `app/Enums/PaymentProofStatus.php` | CREAR | `la-02-backend` |
| `app/Enums/PaymentProofMethod.php` | CREAR | `la-02-backend` |
| `app/Http/Controllers/Api/PaymentProofController.php` | CREAR (store, index, show) | `la-15-api` |
| `app/Http/Controllers/Api/Admin/PaymentProofReviewController.php` | CREAR (list, approve, reject, file) | `la-15-api` |
| `app/Http/Requests/StorePaymentProofRequest.php` | CREAR | `la-15-api` |
| `app/Http/Requests/RejectPaymentProofRequest.php` | CREAR | `la-15-api` |
| `app/Actions/ApprovePaymentProofAction.php` | CREAR | `la-02-backend` |
| `app/Actions/RejectPaymentProofAction.php` | CREAR | `la-02-backend` |
| `app/Policies/PaymentProofPolicy.php` | CREAR | `la-05-security` |
| `app/Mail/PaymentProofApproved.php` + view | CREAR | `la-02-backend` |
| `app/Mail/PaymentProofRejected.php` + view | CREAR | `la-02-backend` |
| `app/Mail/PaymentProofPending.php` + view (al admin) | CREAR | `la-02-backend` |
| `app/Console/Commands/ExpirePaymentProofsCommand.php` | CREAR (`wellcore:expire-payment-proofs`) | `la-02-backend` |
| `app/Console/Kernel.php` (o `routes/console.php`) | MODIFICAR (schedule daily) | `la-07-devops` |
| `routes/api.php` | AÑADIR rutas | `la-15-api` |
| `config/filesystems.php` | AÑADIR disco `payment_proofs` | `la-07-devops` |
| `.gitignore` | AÑADIR `storage/app/private/payment_proofs/*` | `la-07-devops` |

### 8.2 Frontend (Vue 3 + Livewire)

| Archivo | Acción | Agente |
|---|---|---|
| `resources/js/vue/pages/Coach/CoachProfile.vue` | MODIFICAR (añadir tab) | `la-03-vue3` |
| `resources/js/vue/components/Coach/PaymentProofUploader.vue` | CREAR | `la-03-vue3` |
| `resources/js/vue/components/Coach/PaymentProofList.vue` | CREAR | `la-03-vue3` |
| `resources/js/vue/composables/usePaymentProofs.ts` | CREAR (Pinia store o composable) | `la-03-vue3` |
| `app/Livewire/Admin/PaymentProofReview.php` | CREAR | `la-02-backend` |
| `resources/views/livewire/admin/payment-proof-review.blade.php` | CREAR | `la-04-tailwind-ds` |
| `app/Livewire/Admin/Dashboard.php` | MODIFICAR (widget pendientes) | `la-02-backend` |
| `resources/views/livewire/admin/dashboard.blade.php` | MODIFICAR (slot widget) | `la-04-tailwind-ds` |
| `routes/web.php` | AÑADIR ruta `/admin/payment-proofs` | `la-15-api` |

### 8.3 Tests

| Archivo | Acción | Agente |
|---|---|---|
| `tests/Feature/PaymentProofUploadTest.php` | CREAR | `la-14-testing` |
| `tests/Feature/PaymentProofApprovalTest.php` | CREAR | `la-14-testing` |
| `tests/Feature/PaymentProofRejectionTest.php` | CREAR | `la-14-testing` |
| `tests/Feature/PaymentProofExpirationTest.php` | CREAR | `la-14-testing` |
| `tests/Feature/PaymentProofPolicyTest.php` | CREAR | `la-14-testing` |
| `tests/Unit/ApprovePaymentProofActionTest.php` | CREAR | `la-14-testing` |

---

## 9. Implementación por fases

### Fase 0 — Fundamentos (3-4h)
- [ ] Migración `payment_proofs` (aditiva)
- [ ] Configurar disco `payment_proofs` privado
- [ ] Crear `PaymentProof` model + enums
- [ ] Crear `PaymentProofPolicy`
- [ ] Tests unitarios del model
- [ ] **Verificación:** `php artisan migrate` sin romper tablas existentes

**Dispatch:**
```
Agent(la-06-database, "Crear migración payment_proofs según docs/PAYMENT_PROOF_FEATURE_PLAN.md sección 5.1")
Agent(la-02-backend, "Crear PaymentProof model + enums según sección 5.1, con $fillable, casts, relationships, scopes")
Agent(la-05-security, "Crear PaymentProofPolicy según reglas de sección 6 (coach solo ve los suyos, admin ve todos)")
Agent(la-07-devops, "Configurar disco payment_proofs privado en config/filesystems.php según sección 5.2 + actualizar .gitignore")
```

### Fase 1 — Coach side: subida (4-6h)
- [ ] `PaymentProofController@store` con validaciones + throttle
- [ ] `PaymentProofController@index/show`
- [ ] `StorePaymentProofRequest`
- [ ] Rutas en `routes/api.php`
- [ ] `PaymentProofUploader.vue` con preview + dropzone
- [ ] `PaymentProofList.vue` con badges
- [ ] Nueva tab en `CoachProfile.vue`
- [ ] **Verificación:** coach puede subir, ve solo propios, error 409 en duplicado

**Dispatch:**
```
Agent(la-15-api, "Crear PaymentProofController + StorePaymentProofRequest + rutas según sección 6.1")
Agent(la-03-vue3, "Crear PaymentProofUploader.vue + PaymentProofList.vue + integrar tab en CoachProfile.vue según sección 7.1")
```

### Fase 2 — Admin side: revisión (4-5h)
- [ ] Livewire `PaymentProofReview` con filtros
- [ ] URL firmada temporal para visualizar archivo
- [ ] Modal con thumbnail + datos
- [ ] Widget en `Admin/Dashboard.php` con badge count
- [ ] Ruta `/admin/payment-proofs`
- [ ] **Verificación:** superadmin ve todos, URL firmada expira en 5 min

**Dispatch:**
```
Agent(la-02-backend, "Crear Livewire PaymentProofReview + widget Dashboard según sección 7.2")
Agent(la-04-tailwind-ds, "Crear vistas blade para PaymentProofReview con design tokens WellCore")
Agent(la-15-api, "Crear PaymentProofReviewController@file con URL firmada de 5 min")
```

### Fase 3 — Aprobación (orquestación core) (3-4h)
- [ ] `ApprovePaymentProofAction` (DB transaction completa)
- [ ] `Mail::PaymentProofApproved` al cliente con link
- [ ] Notificación al coach (in-app + email)
- [ ] Audit log via `AuditService::logAction()`
- [ ] Test E2E: subir → aprobar → cliente entra con link

**Dispatch:**
```
Agent(la-02-backend, "Crear ApprovePaymentProofAction según diagrama sección 4.1, reusando CoachInvitationService::handlePaymentApproved()")
Agent(la-02-backend, "Crear Mail PaymentProofApproved con link de invitación al cliente")
Agent(la-14-testing, "Crear tests Feature para flujo completo aprobación según sección 8.3")
```

### Fase 4 — Rechazo + edge cases (2-3h)
- [ ] `RejectPaymentProofAction` con razón obligatoria
- [ ] `Mail::PaymentProofRejected` al coach
- [ ] Comando `wellcore:expire-payment-proofs` (cron diario)
- [ ] Schedule en `Console/Kernel.php`
- [ ] Re-subida tras rechazo (puede subir nuevo para mismo email)

**Dispatch:**
```
Agent(la-02-backend, "Crear RejectPaymentProofAction + Mail PaymentProofRejected + comando ExpirePaymentProofsCommand")
Agent(la-07-devops, "Schedule wellcore:expire-payment-proofs daily en Console/Kernel.php")
```

### Fase 5 — Endurecimiento (3-4h)
- [ ] `AuditService::logAction()` en submit/approve/reject/expire
- [ ] Compresión de imágenes via `ImagePipelineService`
- [ ] Anti-fraude: hash perceptual del archivo, detectar email duplicado pendiente
- [ ] Rate limit per-coach (override si superadmin)
- [ ] Documentar runbook en `docs/PAYMENT_PROOF_RUNBOOK.md`

**Dispatch:**
```
Agent(la-05-security, "Endurecer flujo: audit log completo, rate limits, validación anti-fraude según sección 11")
Agent(la-10-performance, "Integrar ImagePipelineService para compresión imágenes en upload")
```

---

## 10. Estrategia de testing

### Cobertura objetivo: 85%+ en lógica core

```php
// tests/Feature/PaymentProofApprovalTest.php

test('superadmin aprueba comprobante crea cliente con coach asignado', function () {
    $coach = Admin::factory()->coach()->create();
    $proof = PaymentProof::factory()->pendiente()->create(['coach_id' => $coach->id]);

    actingAs(Admin::factory()->superadmin()->create())
        ->postJson("/api/v/admin/payment-proofs/{$proof->id}/approve")
        ->assertOk();

    $proof->refresh();
    expect($proof->status)->toBe(PaymentProofStatus::Aprobado);
    expect($proof->coach_invitation_id)->not->toBeNull();
    expect($proof->payment_id)->not->toBeNull();

    $client = Client::where('email', $proof->client_email)->first();
    expect($client)->not->toBeNull();
    expect($client->status)->toBe(ClientStatus::Activo);

    $clientCoach = ClientCoach::where('client_id', $client->id)->first();
    expect($clientCoach->coach_id)->toBe($coach->id);
    expect($clientCoach->source)->toBe('payment_proof');

    Mail::assertSent(PaymentProofApproved::class);
});
```

### Tests críticos (mínimo viable)
1. Coach NO puede ver comprobantes de otro coach (Policy)
2. Cliente NO puede ver `/api/v/coach/payment-proofs/*` (Auth + role)
3. URL firmada del archivo expira en 5 min
4. Subir mismo email mientras hay pendiente → 409
5. Aprobar es idempotente (segunda llamada → 422 "ya aprobado")
6. Tras rechazar, coach puede re-subir nuevo
7. Comando `wellcore:expire-payment-proofs` marca > 7 días como expirado

---

## 11. Consideraciones de seguridad

| Riesgo | Mitigación |
|---|---|
| Coach sube comprobantes falsos (fraude interno) | Audit log completo + rate limit 10/día/coach + alerta a admin si >5 rechazos seguidos |
| Comprobante con datos sensibles cae en repo de assets | Disco `local` privado + `.gitignore` `storage/app/private/payment_proofs/*` |
| URL del archivo se filtra por chat/email | URL firmada con expiración 5 min — no es URL permanente |
| Inyección SQL / XSS en `coach_note` o `review_note` | Eloquent prepared statements + Blade escaping (`{{ }}`) automático |
| IDOR: coach intenta ver `/payment-proofs/{otro_id}` | `PaymentProofPolicy::view()` valida `coach_id == auth()->id()` |
| Replay attack en aprobación | DB transaction + check `status === pendiente` antes de aprobar |
| MIME spoofing (sube .exe renombrado a .jpg) | Validación `image|mimes:jpg,jpeg,png,pdf` + verificación real con `getimagesize()` |
| Subir archivos masivos (DoS storage) | `max:10240` (10MB) + throttle 10/día/coach |
| Email del cliente con caracteres maliciosos | Validación `email` + sanitización antes de pasar a `Mail::to()` |

⚠️ **Compliance:** los comprobantes pueden contener PII (nombres, números de cuenta). Considerar política de retención (ej. eliminar archivo tras 90 días de aprobado, mantener solo metadata).

---

## 12. Rollout y monitoreo

### 12.1 Despliegue
1. Compilar local: `npm run build` (ver `feedback_no_npm_build.md` — NO compilar en EasyPanel)
2. Commit `public/build/`
3. `git push origin main`
4. EasyPanel: ejecutar `gitpull-load` vía MCP (no Rebuild Docker — ver `feedback_deploy_approach.md`)
5. Ejecutar migración: `php artisan migrate --force` desde consola del container
6. Verificar: `php artisan tinker` → `PaymentProof::count()` debe devolver 0

### 12.2 Métricas a monitorear primera semana
- Comprobantes subidos / día
- Tiempo promedio aprobación (target: < 24h)
- Tasa de rechazo (target: < 15%)
- Errores 5xx en endpoints `/payment-proofs/*`

### 12.3 Plan de rollback
Si algo se rompe:
1. Desactivar tab en `CoachProfile.vue` (feature flag o comentar import)
2. Comprobantes ya subidos quedan en BD (no se pierden)
3. Migración es aditiva — no hay nada que revertir en otras tablas

---

## 13. Apéndice: comandos de dispatch listos

### Arrancar Fase 0 (todo en paralelo, son independientes):
```
Agent(la-06-database, "Implementar migración payment_proofs según docs/PAYMENT_PROOF_FEATURE_PLAN.md sección 5.1. Aditiva, NO tocar tablas existentes.")
Agent(la-02-backend, "Implementar PaymentProof model + PaymentProofStatus enum + PaymentProofMethod enum según docs/PAYMENT_PROOF_FEATURE_PLAN.md secciones 5.1 y 8.1")
Agent(la-05-security, "Implementar PaymentProofPolicy según docs/PAYMENT_PROOF_FEATURE_PLAN.md sección 6 (coach solo propios, admin todos)")
Agent(la-07-devops, "Añadir disco 'payment_proofs' privado en config/filesystems.php + actualizar .gitignore según docs/PAYMENT_PROOF_FEATURE_PLAN.md sección 5.2")
```

### Arrancar Fase 1 (después de Fase 0):
```
Agent(la-15-api, "Implementar PaymentProofController + StorePaymentProofRequest + rutas según docs/PAYMENT_PROOF_FEATURE_PLAN.md secciones 6.1 y 8.1")
Agent(la-03-vue3, "Implementar tab Comprobantes en CoachProfile.vue + PaymentProofUploader.vue + PaymentProofList.vue según docs/PAYMENT_PROOF_FEATURE_PLAN.md secciones 7.1 y 8.2")
```

(Las fases siguientes están detalladas en sección 9.)

---

## 14. Estimación total

| Fase | Horas |
|---|---|
| Fase 0 — Fundamentos | 3-4h |
| Fase 1 — Coach side | 4-6h |
| Fase 2 — Admin side | 4-5h |
| Fase 3 — Aprobación | 3-4h |
| Fase 4 — Rechazo + cron | 2-3h |
| Fase 5 — Endurecimiento | 3-4h |
| **Total** | **~20-26h** (3-4 días) |

---

## 15. Definition of Done

- [ ] Coach puede subir comprobante desde su perfil
- [ ] Superadmin ve lista filtrable de comprobantes en su dashboard
- [ ] Aprobar crea Client + ClientCoach + envía email al cliente con link
- [ ] Rechazar envía email al coach con razón
- [ ] Comprobantes vencen a los 7 días sin revisión
- [ ] Tests con cobertura > 85% en lógica core
- [ ] Documentación en este MD actualizada con cualquier desviación
- [ ] Audit log funciona en submit/approve/reject/expire
- [ ] Disco privado configurado en producción con permisos correctos
- [ ] Migración corrida en producción sin afectar otras tablas
- [ ] Verificación E2E con Chrome DevTools en producción (ver `feedback_push_workflow.md`)
