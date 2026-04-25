# Plan — Invitación Coach + Wompi + Email Branded

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Habilitar al coach para invitar prospectos por email desde su dashboard, generando automáticamente un link de pago Wompi; al aprobarse el pago, el cliente queda creado/activado y asignado al coach que lo invitó.

**Architecture:** Strangler Fig sobre DB compartida — dos migraciones aditivas (`coach_invitations`, `client_coach`), un servicio `CoachInvitationService`, extensión mínima al `WebhookController` (sin tocar `WompiService`), y un SPA Vue 3 en ruta nueva `/coach/invitations`.

**Tech Stack:** Laravel 13 + PHP 8.4, Vue 3.5 + Pinia + TypeScript, MySQL, Mailjet (queue), Wompi payment links API, Tailwind CSS 4, la-01..la-15 agents.

---

## 0. Resumen ejecutivo

1. Se crean dos tablas nuevas aditivas: `coach_invitations` (invitación + link Wompi + estado) y `client_coach` (pivot explícito coach↔cliente).
2. El coach llena un formulario Vue 3 con email, plan y mensaje personalizado; el backend genera el link de pago Wompi, crea un `Payment` pendiente y envía el email branded.
3. El destinatario recibe un email con CTA → `/invitacion/{code}` → redirect a Wompi payment link.
4. Al pagar, el webhook Wompi detecta el prefijo `WCI-` en la referencia, llama a `CoachInvitationService::handlePaymentApproved()`, crea/activa el cliente y registra la asignación en `client_coach`.
5. El coach ve el estado de sus invitaciones (enviada/abierta/clickeada/pagada/expirada) en `/coach/invitations` y puede reenviar o cancelar.

---

## 1. Decisiones técnicas resueltas

### §5.1 — Persistencia de la asignación coach↔cliente

| | **Opción A — Tabla pivot `client_coach`** | **Opción B — Reutilizar `assigned_plans.assigned_by`** | **Opción C — `clients.coach_id` nullable** |
|---|---|---|---|
| **Pros** | Relación explícita, historial completo, permite source/UTM, N coaches por cliente en el futuro | Sin schema change, cero migraciones | Simplicísimo, un campo |
| **Contras** | Requiere migración nueva (aditiva) | `assigned_plans` tiene N rows por cliente (entrenamiento, nutrición, hábitos); `assigned_by` no es unívoco para "coach del cliente" | Limita a 1 coach por cliente para siempre; difícil de deshacer |
| **Riesgo** | Bajo | Alto — lógica implícita y acoplada | Alto — decisión irreversible |

**✅ Recomendación: Opción A.** La tabla `client_coach` es pequeña, aditiva (no toca tablas existentes), permite campo `source='coach_invitation'` para analytics, y deja abierta la posibilidad de múltiples coaches por cliente en planes Premium. El acoplamiento de la Opción B es una deuda técnica costosa; la Opción C es irreversible y demasiado restrictiva.

---

### §5.2 — Invitación a email que ya es cliente existente

**Regla de negocio definida:**

```
IF client.status == 'activo' AND plan activo no expirado:
    → BLOQUEAR con HTTP 422
    → Mensaje al coach: "Este email ya pertenece a un cliente activo en WellCore.
      Para cambiar su plan o coach, usa el panel de Administración."
    
IF client existe BUT status != 'activo' OR plan expirado:
    → PERMITIR con WARNING en la respuesta
    → Mensaje al coach: "Este email pertenece a un cliente inactivo.
      Si confirma, se generará un link de pago para reactivar su cuenta.
      El cliente quedará asignado a tu perfil."
    → Al pagar: reactivar cliente + cambiar coach en client_coach

IF no existe ningún cliente con ese email:
    → PERMITIR (flujo normal, se crea cliente al pagar)
```

---

### §5.3 — Tracking de "email abierto" y "link clickeado"

| | **Pixel tracking custom** | **Mailjet webhooks** | **Solo trackear paid** |
|---|---|---|---|
| **Pros** | Simple, nativo, cero dependencias externas | Más confiable, cross-device | Sin overhead |
| **Contras** | iOS 15+/Gmail pre-carga pueden dar falsos positivos | Requiere webhook Mailjet + endpoint adicional, configuración extra | Cero insight del funnel |
| **Riesgo** | Bajo (datos aproximados pero útiles) | Medio (complejidad V1) | Alto (imposible optimizar sin datos) |

**✅ Recomendación MVP:**
- **Click tracking**: SIEMPRE. La URL `/invitacion/{code}` ya redirige a Wompi — aprovechamos para actualizar `status = link_clicked` y `clicked_at`. Costo cero.
- **Open tracking**: Pixel 1x1 PNG vía `GET /invitacion-pixel/{code}`. Útil como señal aproximada. Se informa en UI: "tracking aproximado".
- **Mailjet webhooks**: Diferir a V2.

---

### §5.4 — Expiración: Wompi payment link vs invitación

**Política:**
- `coach_invitations.expires_at` = `created_at + 7 días` por defecto (configurable al crear). Presencial = 14 días.
- `WompiService::createPaymentLink()` recibe `expires_at` = el mismo que la invitación. Link y invitación expiran al mismo tiempo.
- Un `CoachInvitationsExpireJob` corre **diariamente** (00:05 UTC) y cambia a `status = expired` donde `expires_at < now()` y `status NOT IN ('paid', 'cancelled')`.
- Si el link Wompi expira antes (edge case: Wompi no soporta >30 días), el `resend` lo regenera.
- Endpoint `GET /invitacion/{code}` verifica expiración ANTES de redirigir; si expiró → vista `invitacion-expired.blade.php` con mensaje "Esta invitación venció. Contacta a tu coach."

---

### §5.5 — Límite de invitaciones (anti-spam/anti-abuso)

**Rate limits definidos:**
- **50 invitaciones por día** por coach (reset a medianoche UTC). Throttle key: `coach-inv-day:{admin_id}`.
- **200 invitaciones por mes** por coach. Check en `CoachInvitationService::enforceRateLimit()`.
- **3 reenvíos máximo** por invitación individual. Check en `coach_invitations.resend_count`.
- **Rate limit de IP** en `/invitacion/{code}`: `throttle:120,1` (120 requests/min por IP) para evitar bruteforce.

**Justificación del 50/día, 200/mes:** Un coach activo con 20 clientes nuevos/mes usa 20 invitaciones. El cap de 200/mes es 10× uso normal. El cap de 50/día bloquea spam masivo si la cuenta es comprometida, sin afectar uso legítimo.

---

### §5.6 — Comisión del coach por cliente captado vía invitación

| | **Alimentar sistema `referrals`** | **Flujo separado con analytics propios** |
|---|---|---|
| **Pros** | Reutiliza infraestructura existente | Semántica correcta (coach ≠ cliente referidor), no contamina datos |
| **Contras** | `referrals` es cliente→cliente; importar coach→prospecto es semánticamente incorrecto | Requiere query adicional en analytics |

**✅ Recomendación: Flujos 100% separados.** Las invitaciones del coach NO crean registros en `referrals`. El `CoachProfile.referral_code` y `referral_commission` son para el sistema de referidos cliente→cliente. Las invitaciones se contabilizan vía `coach_invitations WHERE coach_id = ? AND status = 'paid'`. La comisión del coach por sus captaciones se difiere a V2 (requiere definición de negocio sobre el porcentaje y quién lo paga).

---

### §5.7 — Tab en `/coach/profile` vs ruta nueva `/coach/invitations`

| | **Tab dentro de `/coach/profile`** | **Ruta nueva `/coach/invitations`** |
|---|---|---|
| **Pros** | Menos cambios en routing/nav | URL propia, bookmarkeable, historial de navegación, lazy loading independiente |
| **Contras** | `CoachProfilePage` ya gestiona bio/marca/branding; añadir lista paginada + formulario lo haría enorme | Requiere añadir entrada al sidebar y ruta en web.php |
| **SEO** | N/A (auth-only) | N/A (auth-only) |

**✅ Recomendación: Ruta nueva `/coach/invitations`.** Todas las secciones del portal coach son rutas independientes (`/coach/dashboard`, `/coach/clients`, `/coach/kanban`…). `CoachProfilePage` debe mantenerse enfocado en datos de perfil/marca. El bundle de invitaciones se carga solo al visitar la ruta (lazy loading). Costo: 1 entrada en sidebar + 1 ruta en `routes/web.php`.

---

## 2. Esquema de base de datos

### 2.1 Migración `coach_invitations` (DDL completo)

Archivo: `database/migrations/2026_05_01_000001_create_coach_invitations_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('coach_id');
            $table->char('code', 32)->unique();          // bin2hex(random_bytes(16))
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('plan');                       // PlanType enum value
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('COP');
            $table->string('subject');
            $table->text('intro_message')->nullable();
            $table->string('cta_label', 100)->default('Comenzar mi plan ahora');
            $table->string('wompi_payment_link_id', 100)->nullable();
            $table->text('wompi_payment_link_url')->nullable();
            $table->string('wompi_reference', 40)->nullable()->unique();  // WCI-{code}
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('status', 20)->default('sent');
            $table->unsignedTinyInteger('resend_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->json('meta')->nullable();             // UTMs, source, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('coach_id')->references('id')->on('admins');
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();

            $table->index(['coach_id', 'status'], 'ci_coach_status_idx');
            $table->index(['email', 'status'], 'ci_email_status_idx');
            $table->index('expires_at', 'ci_expires_idx');
            $table->index('created_at', 'ci_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_invitations');
    }
};
```

### 2.2 Migración `client_coach` (pivot explícito coach↔cliente)

Archivo: `database/migrations/2026_05_01_000002_create_client_coach_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_coach', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('admin_id');       // coach (admins.id)
            $table->timestamp('assigned_at')->useCurrent();
            $table->string('source', 30)->default('manual'); // 'coach_invitation'|'manual'|'migration'
            $table->unsignedBigInteger('coach_invitation_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();
            $table->foreign('coach_invitation_id')
                  ->references('id')->on('coach_invitations')->nullOnDelete();

            $table->index(['client_id', 'active'], 'cc_client_active_idx');
            $table->index('admin_id', 'cc_admin_idx');
            $table->index('source', 'cc_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_coach');
    }
};
```

### 2.3 Justificación de índices

| Índice | Tabla | Justificación |
|--------|-------|---------------|
| `(coach_id, status)` | coach_invitations | Query principal del coach: listar sus invitaciones por estado |
| `(email, status)` | coach_invitations | Verificar si email ya es cliente activo/ya fue invitado |
| `expires_at` | coach_invitations | Job diario de expiración: `WHERE expires_at < NOW() AND status NOT IN (...)` |
| `code UNIQUE` | coach_invitations | Lookup en `/invitacion/{code}` — O(1) por UUID pública |
| `wompi_reference UNIQUE` | coach_invitations | Lookup en webhook: detectar si referencia es `WCI-*` |
| `(client_id, active)` | client_coach | Encontrar coach activo de un cliente: `WHERE client_id=? AND active=1` |
| `admin_id` | client_coach | Listar clientes de un coach (inverse) |

---

## 3. Modelos y relaciones

### `app/Models/CoachInvitation.php`

```php
<?php

namespace App\Models;

use App\Enums\CoachInvitationStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'coach_id', 'code', 'email', 'name', 'plan', 'amount', 'currency',
    'subject', 'intro_message', 'cta_label',
    'wompi_payment_link_id', 'wompi_payment_link_url', 'wompi_reference',
    'payment_id', 'client_id', 'status', 'resend_count',
    'sent_at', 'opened_at', 'clicked_at', 'paid_at', 'expires_at', 'cancelled_at', 'meta',
])]
class CoachInvitation extends Model
{
    use SoftDeletes;

    protected $table = 'coach_invitations';

    protected function casts(): array
    {
        return [
            'plan'         => PlanType::class,
            'status'       => CoachInvitationStatus::class,
            'amount'       => 'decimal:2',
            'sent_at'      => 'datetime',
            'opened_at'    => 'datetime',
            'clicked_at'   => 'datetime',
            'paid_at'      => 'datetime',
            'expires_at'   => 'datetime',
            'cancelled_at' => 'datetime',
            'meta'         => 'array',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function coachProfile(): HasOneThrough
    {
        return $this->hasOneThrough(CoachProfile::class, Admin::class, 'id', 'admin_id', 'coach_id', 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function canResend(): bool
    {
        return in_array($this->status->value, ['expired', 'failed'])
            && $this->resend_count < 3;
    }

    public function invitationUrl(): string
    {
        return url('/invitacion/' . $this->code);
    }

    public function pixelUrl(): string
    {
        return url('/invitacion-pixel/' . $this->code);
    }
}
```

### `app/Models/ClientCoach.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'admin_id', 'assigned_at', 'source', 'coach_invitation_id', 'active'])]
class ClientCoach extends Model
{
    protected $table = 'client_coach';

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'active'      => 'boolean',
        ];
    }

    public function client(): BelongsTo   { return $this->belongsTo(Client::class); }
    public function coach(): BelongsTo    { return $this->belongsTo(Admin::class, 'admin_id'); }
    public function invitation(): BelongsTo { return $this->belongsTo(CoachInvitation::class, 'coach_invitation_id'); }
}
```

### `app/Enums/CoachInvitationStatus.php`

```php
<?php

namespace App\Enums;

enum CoachInvitationStatus: string
{
    case Sent        = 'sent';
    case Opened      = 'opened';
    case LinkClicked = 'link_clicked';
    case Paid        = 'paid';
    case Expired     = 'expired';
    case Cancelled   = 'cancelled';
    case Failed      = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled]);
    }

    public function canResend(): bool
    {
        return in_array($this, [self::Expired, self::Failed]);
    }

    public function label(): string
    {
        return match ($this) {
            self::Sent        => 'Enviada',
            self::Opened      => 'Abierta',
            self::LinkClicked => 'Link visitado',
            self::Paid        => 'Pagada',
            self::Expired     => 'Expirada',
            self::Cancelled   => 'Cancelada',
            self::Failed      => 'Fallida',
        };
    }
}
```

---

## 4. State machine

```
[CREADA]
    |
    +--send()--> [sent] --pixel open--> [opened] --click /invitacion/{code}--> [link_clicked]
                    |                       |                                         |
                    |                       |                                         |
                    +---expires_at < now()--+------------------+                     |
                    |   (job diario)                           v                     |
                    |                                      [expired] <---------------+
                    |                                          |
                    +--coach cancel()---> [cancelled] <--------+
                    |                                          |
                    |                              resend() re-abre a [sent]
                    |
                    +--Wompi APPROVED webhook--> [paid] (TERMINAL)
                    +--Wompi DECLINED/ERROR webhook--> [failed]
                                                           |
                                                       resend() re-abre a [sent]
```

### Tabla de transiciones completa

| Estado origen | Trigger | Estado destino | Actor | Side effects |
|---|---|---|---|---|
| — | `CoachInvitationService::create()` | `sent` | Coach (HTTP) | Crear Payment(pending), crear Wompi link, enviar email (queue), escribir `sent_at` |
| `sent` | `GET /invitacion-pixel/{code}` | `opened` | Sistema | Escribir `opened_at` (sync, ligero) |
| `sent` / `opened` | `GET /invitacion/{code}` | `link_clicked` | Sistema | Escribir `clicked_at` (sync), redirect Wompi URL |
| cualquier no-terminal | `expires_at < now()` | `expired` | Job diario | Bulk update, sin emails |
| `sent`/`opened`/`link_clicked` | Wompi webhook APPROVED | `paid` | Webhook (HTTP) | `DB::transaction`: crear/activar cliente, `payment.client_id`, `client_coach`, `paid_at`, welcome email (queue) |
| `sent`/`opened`/`link_clicked` | Wompi webhook DECLINED/ERROR/VOIDED | `failed` | Webhook (HTTP) | Actualizar `status = failed` |
| cualquier no-terminal + `expired`/`failed` | `DELETE /v/coach/invitations/{id}` | `cancelled` | Coach | Escribir `cancelled_at`; si link activo, intentar anular en Wompi (best-effort, no bloquear) |
| `expired` / `failed` | `POST /v/coach/invitations/{id}/resend` | `sent` | Coach | Generar nuevo Wompi link, reset `expires_at` (+7 días), incrementar `resend_count`, reenviar email, escribir nuevo `sent_at` |

### Idempotencia en webhook

El webhook Wompi puede llegar múltiples veces para la misma transacción.

```php
// En CoachInvitationService::handlePaymentApproved():
// Guard: si invitation.status ya es 'paid', retornar sin hacer nada (idempotente)
if ($invitation->status === CoachInvitationStatus::Paid) {
    Log::info('CoachInvitation already paid — ignoring duplicate webhook', ['id' => $invitation->id]);
    return;
}
// La lógica existente en WompiService::handleWebhook() ya tiene su propio guard:
// ($newStatus === PaymentStatus::Approved && $oldStatus !== PaymentStatus::Approved)
// Así que no hay doble procesamiento.
```

### Atomicidad en transición → `paid`

```php
// CoachInvitationService::handlePaymentApproved()
DB::transaction(function () use ($invitation, $payment) {
    // 1. Crear o activar cliente
    $client = $this->createOrActivateClient($invitation);
    
    // 2. Vincular cliente al payment
    $payment->update(['client_id' => $client->id]);
    
    // 3. Desactivar asignaciones previas de coach
    ClientCoach::where('client_id', $client->id)->update(['active' => false]);
    
    // 4. Crear asignación nueva
    ClientCoach::create([
        'client_id'             => $client->id,
        'admin_id'              => $invitation->coach_id,
        'source'                => 'coach_invitation',
        'coach_invitation_id'   => $invitation->id,
        'assigned_at'           => now(),
        'active'                => true,
    ]);
    
    // 5. Marcar invitación como pagada
    $invitation->update([
        'status'    => CoachInvitationStatus::Paid,
        'paid_at'   => now(),
        'client_id' => $client->id,
        'payment_id'=> $payment->id,
    ]);
});

// 6. Fuera de la transacción: emails (queue) — fallar aquí no revierte el pago
Mail::to($invitation->email)->queue(new WelcomeMail(
    clientName: $invitation->name ?? 'Cliente',
    planName: $invitation->plan->label(),
    coachName: $invitation->coach->name,
));
```

---

## 5. Servicios y Actions

### 5.1 `CoachInvitationService` — firma completa de métodos

Archivo: `app/Services/CoachInvitationService.php`

```php
<?php

namespace App\Services;

use App\Enums\CoachInvitationStatus;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientCoach;
use App\Models\CoachInvitation;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CoachInvitationService
{
    public function __construct(
        private WompiService $wompi,
    ) {}

    /**
     * Crea la invitación, genera el payment link Wompi y envía el email.
     * Lanza RateLimitException si el coach excedió su cuota.
     * Lanza InvitationBlockedException si el email es de un cliente activo.
     *
     * @param  array{email: string, name: ?string, plan: string, amount: int,
     *               subject: string, intro_message: ?string, cta_label: ?string,
     *               expires_at: ?\Carbon\Carbon}  $data
     */
    public function create(Admin $coach, array $data): CoachInvitation {}

    /**
     * Renderiza el HTML del email de invitación sin crear ni enviar nada.
     * Usado por el endpoint de preview.
     *
     * @param  array{email: string, name: ?string, plan: string,
     *               subject: string, intro_message: ?string, cta_label: ?string}  $data
     */
    public function renderPreview(Admin $coach, array $data): string {}

    /**
     * Reenvía la invitación: genera nuevo payment link (si expiró), reset expires_at,
     * incrementa resend_count y envía de nuevo el email.
     * Solo permitido si status ∈ {expired, failed} y resend_count < 3.
     */
    public function resend(CoachInvitation $invitation): void {}

    /**
     * Marca la invitación como cancelada.
     * Si el link Wompi está activo, intenta anularlo (best-effort, no lanza en fallo).
     */
    public function cancel(CoachInvitation $invitation): void {}

    /**
     * Resuelve una invitación por su code público.
     * Retorna null si no existe o está soft-deleted.
     */
    public function resolveByCode(string $code): ?CoachInvitation {}

    /**
     * Actualiza opened_at (solo si aún no está marcada como opened/link_clicked/paid).
     */
    public function trackOpen(CoachInvitation $invitation): void {}

    /**
     * Actualiza clicked_at y avanza status a link_clicked.
     * Retorna la URL de Wompi a la que redirigir.
     */
    public function trackClickAndGetUrl(CoachInvitation $invitation): string {}

    /**
     * Procesamiento post-pago aprobado.
     * Llamado desde WebhookController DESPUÉS de WompiService::handleWebhook().
     * Idempotente — verifica status antes de actuar.
     */
    public function handlePaymentApproved(Payment $payment, CoachInvitation $invitation): void {}

    /**
     * Expira en bulk todas las invitaciones vencidas. Llamado por el job.
     * Retorna el número de registros actualizados.
     */
    public function expireOverdue(): int {}

    // ----------- PRIVATE HELPERS -----------

    /** Crea el Payment pendiente + genera el Wompi payment link. */
    private function createPaymentAndLink(CoachInvitation $invitation): void {}

    /** Envía el CoachClientInvitation mailable al destinatario (queue). */
    private function sendEmail(CoachInvitation $invitation): void {}

    /**
     * Crea el cliente si no existe, o reactiva si estaba inactivo.
     * Si existe y está activo lanza InvitationBlockedException.
     */
    private function createOrActivateClient(CoachInvitation $invitation): Client {}

    /** Verifica rate limits del coach. Lanza RateLimitException si supera. */
    private function enforceRateLimit(Admin $coach): void {}
}
```

### 5.2 Extensión a `WompiService` (delta — MÍNIMA)

**NO se modifica `WompiService::handleWebhook()`.**

Solo se añade un método público utilitario:

```php
// Añadir a WompiService (al final de la clase):

/**
 * Determina si una referencia pertenece a una invitación de coach.
 * Prefijo: "WCI-" (coach invitation).
 */
public function isCoachInvitationReference(string $reference): bool
{
    return str_starts_with($reference, 'WCI-');
}

/**
 * Extrae el invitation code de una referencia WCI-{code}.
 */
public function extractInvitationCode(string $reference): string
{
    return substr($reference, 4); // remueve "WCI-"
}
```

### 5.3 Webhook handler delta — `WebhookController`

Modificar `app/Http/Controllers/WebhookController.php` (solo el bloque `transaction.updated`):

```php
use App\Models\CoachInvitation;
use App\Models\Payment;
use App\Services\CoachInvitationService;

if ($event === 'transaction.updated') {
    $reference   = $payload['data']['transaction']['reference'] ?? null;
    $wompiStatus = $payload['data']['transaction']['status'] ?? '';

    // --- Coach Invitation pre-hook ---
    // Debe correr ANTES de handleWebhook() para que client_id esté set en Payment
    // cuando runPostApprovalAutomation() intente enviar emails.
    $coachInvitation = null;
    if ($reference && $wompi->isCoachInvitationReference($reference) && $wompiStatus === 'APPROVED') {
        $code = $wompi->extractInvitationCode($reference);
        $coachInvitation = CoachInvitation::where('code', $code)->first();
        if ($coachInvitation && $coachInvitation->status !== \App\Enums\CoachInvitationStatus::Paid) {
            $payment = Payment::where('wompi_reference', $reference)->first();
            if ($payment) {
                app(CoachInvitationService::class)->handlePaymentApproved($payment, $coachInvitation);
            }
        }
    }
    // --- fin pre-hook ---

    $processed = $wompi->handleWebhook($payload);

    return response()->json([
        'status'  => $processed ? 'ok' : 'ignored',
        'message' => $processed
            ? 'Transaccion actualizada'
            : 'Transaccion no encontrada o evento no aplicable',
    ]);
}
```

**Nota:** `handlePaymentApproved()` ejecuta su propia `DB::transaction()` que incluye crear/activar el cliente y actualizar `payment.client_id`. Cuando `WompiService::handleWebhook()` corra después, el `$payment->client_id` ya estará seteado, permitiendo que `runPostApprovalAutomation()` envíe el `WelcomeMail` correctamente.

> **ADVERTENCIA DE ORDEN:** El pre-hook debe estar explícitamente antes del `handleWebhook()` call. Nunca invertir el orden.

---

## 6. Endpoints REST

### Resumen de rutas

```
POST   /v/coach/invitations              — crear invitación
GET    /v/coach/invitations              — listar (paginado)
GET    /v/coach/invitations/{id}         — detalle
POST   /v/coach/invitations/{id}/resend  — reenviar
DELETE /v/coach/invitations/{id}         — cancelar
POST   /v/coach/invitations/preview      — preview HTML (sin crear ni enviar)

GET    /invitacion/{code}                — ruta pública: tracking click + redirect a Wompi
GET    /invitacion-pixel/{code}          — tracking open (1x1 PNG)
```

Todas las rutas autenticadas van bajo `middleware(['auth:wellcore', 'throttle:api', 'role:coach,admin,superadmin,jefe'])`.

---

### `POST /v/coach/invitations`

**Auth:** `role:coach,admin,superadmin,jefe`  
**Rate limit adicional:** `throttle:50,1440` por coach (50/día)  
**Form Request:** `StoreInvitationRequest`

**Request body:**
```json
{
  "email": "maria@ejemplo.com",
  "name": "María García",
  "plan": "metodo",
  "subject": "Te invito a transformar tu cuerpo con WellCore",
  "intro_message": "Hola María, llevo 6 meses trabajando con clientes como tú...",
  "cta_label": "Comenzar mi plan ahora",
  "expires_in_days": 7
}
```

**Validation (StoreInvitationRequest):**
```php
return [
    'email'           => ['required', 'email:rfc,dns', 'max:255'],
    'name'            => ['nullable', 'string', 'max:255'],
    'plan'            => ['required', Rule::enum(PlanType::class)->except([PlanType::Trial])],
    'subject'         => ['required', 'string', 'max:255'],
    'intro_message'   => ['nullable', 'string', 'max:2000'],
    'cta_label'       => ['nullable', 'string', 'max:100'],
    'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
];
```

**Sanitización de `intro_message`:** `strip_tags($data['intro_message'])` en el Service antes de guardar.

**Response 201:**
```json
{
  "data": {
    "id": 42,
    "uuid": "01926c...",
    "code": "a1b2c3d4...",
    "email": "maria@ejemplo.com",
    "name": "María García",
    "plan": "metodo",
    "amount": 339150.00,
    "status": "sent",
    "subject": "Te invito a transformar...",
    "intro_message": "Hola María...",
    "cta_label": "Comenzar mi plan ahora",
    "wompi_payment_link_url": "https://checkout.wompi.co/l/...",
    "invitation_url": "https://wellcorefitness.com/invitacion/a1b2c3d4...",
    "expires_at": "2026-05-08T05:00:00Z",
    "sent_at": "2026-05-01T14:23:00Z",
    "resend_count": 0
  }
}
```

**Errores:**
```json
// 422 — email de cliente activo
{ "message": "Este email ya pertenece a un cliente activo en WellCore. Para cambiar su plan o coach, usa el panel de Administración.", "error_code": "CLIENT_ACTIVE" }

// 422 — email de cliente inactivo (advertencia, pero se permite si confirmed=true)
{ "message": "Este email pertenece a un cliente inactivo.", "error_code": "CLIENT_INACTIVE", "requires_confirmation": true }

// 429 — rate limit
{ "message": "Has alcanzado el límite de 50 invitaciones por día.", "retry_after": 3600 }

// 503 — Wompi no disponible
{ "message": "No se pudo generar el link de pago. Intenta en unos minutos.", "error_code": "WOMPI_UNAVAILABLE" }
```

---

### `GET /v/coach/invitations`

**Auth:** `role:coach,admin,superadmin,jefe` (coach solo ve las suyas; admin+ ve todas)  
**Query params:** `?status=sent&plan=metodo&page=1&per_page=20&sort=created_at&order=desc`

**Response 200:**
```json
{
  "data": [ { /* CoachInvitation resource */ } ],
  "meta": { "current_page": 1, "per_page": 20, "total": 47, "last_page": 3 },
  "stats": { "sent": 10, "opened": 8, "link_clicked": 5, "paid": 3, "expired": 2, "cancelled": 1 }
}
```

---

### `GET /v/coach/invitations/{id}`

**Auth:** Policy `CoachInvitationPolicy::view()` (coach solo ve las suyas; admin+ ve todas)

**Response 200:** Recurso completo incluyendo `preview_html` (HTML del email enviado, escapado en JSON).

---

### `POST /v/coach/invitations/{id}/resend`

**Auth:** Policy `CoachInvitationPolicy::resend()`  
**Rate limit:** Verificación adicional en service: `resend_count < 3`

**Response 200:**
```json
{ "message": "Invitación reenviada.", "data": { "resend_count": 2, "new_expires_at": "2026-05-15T05:00:00Z", "new_wompi_url": "https://checkout.wompi.co/l/..." } }
```

**Errores:**
```json
// 422 — no se puede reenviar (status no permite)
{ "message": "Solo se pueden reenviar invitaciones expiradas o fallidas.", "error_code": "INVALID_STATUS" }
// 422 — máximo de reenvíos
{ "message": "Has alcanzado el máximo de 3 reenvíos para esta invitación.", "error_code": "MAX_RESENDS" }
```

---

### `DELETE /v/coach/invitations/{id}`

**Auth:** Policy `CoachInvitationPolicy::cancel()`

**Response 200:**
```json
{ "message": "Invitación cancelada.", "data": { "status": "cancelled", "cancelled_at": "2026-05-01T..." } }
```

**Errores:**
```json
// 422 — ya pagada o ya cancelada
{ "message": "No se puede cancelar una invitación que ya fue pagada o cancelada.", "error_code": "INVALID_STATUS" }
```

---

### `POST /v/coach/invitations/preview`

**Auth:** `role:coach,admin,superadmin,jefe`  
**Propósito:** Renderiza el HTML del email SIN crear la invitación ni enviar nada.  
**Form Request:** `PreviewInvitationRequest` (mismos campos que Store minus `expires_in_days`)

**Response 200:**
```json
{ "html": "<!DOCTYPE html>..." }
```

El HTML se renderiza con `View::make('emails.coach-client-invitation', [...])->render()`. El frontend lo muestra en `<iframe sandbox="allow-same-origin">` para aislar los estilos table-based del email del CSS de la app.

---

### `GET /invitacion/{code}` (ruta pública web)

**Auth:** Ninguna  
**Rate limit:** `throttle:120,1` (120 req/min por IP — anti-bruteforce)

**Flujo:**
1. Buscar `CoachInvitation::where('code', $code)->first()`
2. Si no existe → 404 view
3. Si `status === 'cancelled'` → view "invitación cancelada"
4. Si `expires_at->isPast()` y `status !== 'paid'` → view "invitación expirada" (con CTA "Contacta a tu coach")
5. Si `status === 'paid'` → view "ya pagada" (con CTA "Inicia sesión")
6. Actualizar `status = link_clicked`, `clicked_at = now()` (si status era `sent` o `opened`)
7. Redirect 302 a `$invitation->wompi_payment_link_url`

**Response:** Redirect 302 o Blade view de error.

---

### `GET /invitacion-pixel/{code}` (tracking open, 1x1 PNG)

**Auth:** Ninguna  
**Rate limit:** `throttle:60,1` por IP

**Flujo:**
1. Buscar invitación por code
2. Si existe y `status === 'sent'`: actualizar `status = opened`, `opened_at = now()`
3. Siempre retornar imagen GIF transparente 1x1:
```php
return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200)
    ->header('Content-Type', 'image/gif')
    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
    ->header('Pragma', 'no-cache');
```

---

### `POST /webhooks/wompi` (extensión al existente)

Ver §5.3. El endpoint **no cambia su firma ni ruta**. Solo se añade el pre-hook antes de `handleWebhook()`.

---

## 7. Frontend Vue 3

### 7.1 Árbol de componentes

```
/coach/invitations (ruta nueva)
└── InvitationManager.vue           # Página contenedora, carga store, tabs
    ├── InvitationList.vue           # Lista paginada con filtros + stats badges
    │   └── InvitationStatusBadge.vue # Badge de color por status
    └── InvitationForm.vue           # Formulario crear + preview
        └── EmailPreview.vue         # <iframe sandbox> con HTML del email
```

Archivos:
- `resources/js/vue/coach/invitations/InvitationManager.vue`
- `resources/js/vue/coach/invitations/InvitationList.vue`
- `resources/js/vue/coach/invitations/InvitationForm.vue`
- `resources/js/vue/coach/invitations/EmailPreview.vue`
- `resources/js/vue/coach/invitations/InvitationStatusBadge.vue`
- `resources/js/vue/stores/invitationsStore.ts`
- `resources/js/vue/composables/useInvitations.ts`

### 7.2 Pinia store + composable

**`invitationsStore.ts`:**
```typescript
export const useInvitationsStore = defineStore('invitations', () => {
  const invitations = ref<CoachInvitation[]>([]);
  const stats = ref<InvitationStats>({ sent: 0, opened: 0, link_clicked: 0, paid: 0, expired: 0, cancelled: 0 });
  const pagination = ref<PaginationMeta | null>(null);
  const loading = ref(false);
  const filters = ref<InvitationFilters>({ status: '', plan: '', page: 1, per_page: 20 });

  async function fetchInvitations(): Promise<void> {}
  async function createInvitation(data: CreateInvitationData): Promise<CoachInvitation> {}
  async function previewInvitation(data: PreviewInvitationData): Promise<string> {}
  async function resendInvitation(id: number): Promise<void> {}
  async function cancelInvitation(id: number): Promise<void> {}

  return { invitations, stats, pagination, loading, filters,
           fetchInvitations, createInvitation, previewInvitation, resendInvitation, cancelInvitation };
});
```

**`useInvitations.ts` (composable):**
```typescript
export function useInvitations() {
  const store = useInvitationsStore();
  
  const hasActiveFilters = computed((): boolean => !!store.filters.status || !!store.filters.plan);
  
  function handleCreateSuccess(invitation: CoachInvitation): void {
    store.invitations.unshift(invitation);
    // show success toast
  }
  
  function handleApiError(error: AxiosError): string {
    // Mapear error_code a mensajes amigables en español
  }
  
  return { store, hasActiveFilters, handleCreateSuccess, handleApiError };
}
```

**TypeScript interfaces:**
```typescript
interface CoachInvitation {
  id: number;
  uuid: string;
  code: string;
  email: string;
  name: string | null;
  plan: PlanType;
  amount: number;
  status: InvitationStatus;
  subject: string;
  intro_message: string | null;
  cta_label: string;
  wompi_payment_link_url: string | null;
  invitation_url: string;
  expires_at: string;
  sent_at: string | null;
  opened_at: string | null;
  clicked_at: string | null;
  paid_at: string | null;
  resend_count: number;
}

type InvitationStatus = 'sent' | 'opened' | 'link_clicked' | 'paid' | 'expired' | 'cancelled' | 'failed';
type PlanType = 'esencial' | 'metodo' | 'elite' | 'rise' | 'presencial';
```

### 7.3 Routing decision (resultado: ruta nueva)

Ver §5.7. Se añade:

**`routes/web.php`** (ruta nueva — sirve el wrapper Vue):
```php
Route::view('/coach/invitations', 'vue')->middleware(['auth.wellcore']);
```

**Vue Router** (en el objeto de rutas existente del coach SPA):
```javascript
{
  path: '/coach/invitations',
  component: () => import('@/coach/invitations/InvitationManager.vue'),
  meta: { requiresAuth: true, roles: ['coach', 'admin', 'superadmin', 'jefe'], title: 'Mis Invitaciones' },
},
```

**Sidebar del coach** — añadir entrada "Invitaciones" con ícono `envelope` después de "Mi perfil".

### 7.4 Wireframes textuales

#### Pantalla A — Lista de invitaciones (`InvitationList.vue`)

```
╔══════════════════════════════════════════════════════════════╗
║  MIS INVITACIONES                              [+ Nueva]     ║
╠══════════════════════════════════════════════════════════════╣
║  Stats: [Enviadas: 10] [Abiertas: 8] [Pagadas: 3] [...]     ║
╠══════════════════════════════════════════════════════════════╣
║  Filtros: [Todos ▼] [Todos los planes ▼]   [Buscar email]   ║
╠══════════════════════════════════════════════════════════════╣
║  email             plan     estado          fecha     acción ║
║  maria@ej.com     Método   ● Pagada         01/05     [Ver]  ║
║  carlos@ej.com    Esencial  ○ Abierta        30/04     [↺][✕]║
║  ana@ej.com       Elite    ○ Enviada         29/04     [↺][✕]║
║  pedro@ej.com     Método   ⚠ Expirada        20/04     [↺]  ║
╠══════════════════════════════════════════════════════════════╣
║                    [ Anterior ] 1 de 3 [ Siguiente ]         ║
╚══════════════════════════════════════════════════════════════╝
```

#### Pantalla B — Formulario crear invitación (`InvitationForm.vue`)

```
╔══════════════════════════════════════════════════════════════╗
║  NUEVA INVITACIÓN                                            ║
╠══════════════════════════════════════════════════════════════╣
║  Email del prospecto: [___________________________]          ║
║  Nombre (opcional):   [___________________________]          ║
║  Plan:  [Método ▼ $339.150 COP/mes]                          ║
║  ─────────────────────────────────────────────────────────  ║
║  Personaliza el email                                        ║
║  Asunto: [Te invito a transformar tu cuerpo — WellCore]      ║
║  Mensaje: [___________________________]  240/2000 chars       ║
║  CTA:     [Comenzar mi plan ahora    ]                       ║
║  ─────────────────────────────────────────────────────────  ║
║  [Vista previa]    ← debe presionarse al menos 1 vez         ║
║                                                              ║
║  [CANCELAR]                [ENVIAR INVITACIÓN  →]            ║
║                             (deshabilitado hasta preview)    ║
╚══════════════════════════════════════════════════════════════╝
```

#### Pantalla C — Preview del email (`EmailPreview.vue`)

```
╔══════════════════════════════════════════════════════════════╗
║  VISTA PREVIA DEL EMAIL                               [✕]    ║
╠══════════════════════════════════════════════════════════════╣
║  ┌──────────────────────────────────────────────────────┐   ║
║  │  <iframe sandbox="allow-same-origin" srcdoc="...">  │   ║
║  │   [HTML del email renderizado — estilos aislados]   │   ║
║  │  </iframe>                                          │   ║
║  └──────────────────────────────────────────────────────┘   ║
║  ℹ Tracking de apertura es aproximado (iOS 15+ lo bloquea)  ║
║                                                              ║
║  [Volver y editar]        [Confirmar y enviar  →]            ║
╚══════════════════════════════════════════════════════════════╝
```

---

## 8. Email

### 8.1 Layout maestro nuevo

Archivo: `resources/views/emails/layouts/wellcore-base.blade.php`

Estructura HTML (inline styles — compatible Gmail/Outlook 2019+/Apple Mail/Yahoo):

```blade
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="dark">
  <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelPerInch>96</o:PixelPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
  <title>@yield('title', 'WellCore Fitness')</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600&display=swap');
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #09090B; }
    .email-body { background-color: #09090B; padding: 24px 16px; }
    .container { max-width: 600px; margin: 0 auto; }
    .card { background-color: #18181B; border-radius: 8px; overflow: hidden; }
    .header { background-color: #09090B; padding: 24px; text-align: center; border-bottom: 1px solid #27272A; }
    .content { padding: 32px 24px; }
    .footer { background-color: #09090B; padding: 24px; text-align: center; border-top: 1px solid #27272A; }
    .btn-primary { display: inline-block; background-color: #DC2626; color: #FAFAFA; text-decoration: none;
                   font-family: Arial, sans-serif; font-weight: bold; font-size: 16px;
                   padding: 16px 32px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    h1, h2 { font-family: Arial, sans-serif; color: #FAFAFA; }
    p { color: #A1A1AA; line-height: 1.6; font-size: 15px; }
    .accent { color: #DC2626; }
    .muted { color: #71717A; font-size: 13px; }
  </style>
</head>
<body>
  <div class="email-body">
    <div class="container">
      <!-- Header con logo -->
      <div class="header">
        <img src="{{ asset('images/wellcore-logo-email.png') }}" alt="WellCore Fitness" width="140" style="display:block;margin:0 auto;">
      </div>
      
      <!-- Card principal -->
      <div class="card">
        <div class="content">
          @yield('content')
        </div>
      </div>
      
      <!-- Footer -->
      <div class="footer">
        @yield('footer_extra')
        <p class="muted">
          © {{ date('Y') }} WellCore Fitness · Todos los derechos reservados<br>
          Bucaramanga, Colombia<br>
          <a href="{{ config('wellcore.base_url') }}/privacidad" style="color:#71717A;">Privacidad</a> ·
          <a href="{{ config('wellcore.base_url') }}/terminos" style="color:#71717A;">Términos</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
```

### 8.2 Mailable + plantilla de invitación

**`app/Mail/CoachClientInvitation.php`:**
```php
<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\CoachInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachClientInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachInvitation $invitation,
        public Admin $coach,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->invitation->subject,
        );
    }

    public function content(): Content
    {
        $planCatalog = $this->getPlanDetails($this->invitation->plan->value);

        return new Content(
            view: 'emails.coach-client-invitation',
            with: [
                'invitation'    => $this->invitation,
                'coach'         => $this->coach,
                'coachProfile'  => $this->coach->coachProfile,
                'planDetails'   => $planCatalog,
                'invitationUrl' => $this->invitation->invitationUrl(),
                'pixelUrl'      => $this->invitation->pixelUrl(),
            ],
        );
    }

    private function getPlanDetails(string $plan): array { /* ... mismos datos que PlanInvitation ... */ }
}
```

**`resources/views/emails/coach-client-invitation.blade.php`:**

```blade
@extends('emails.layouts.wellcore-base')

@section('title', $invitation->subject)

@section('content')
<!-- 1. Hero: foto coach + saludo + mensaje personalizado -->
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    @if($coachProfile?->photo_url)
    <td width="72" valign="top">
      <img src="{{ $coachProfile->photo_url }}" width="60" height="60"
           style="border-radius:50%;border:2px solid #DC2626;" alt="{{ $coach->name }}">
    </td>
    @endif
    <td valign="top" style="padding-left:16px;">
      <p style="color:#FAFAFA;font-weight:600;margin:0 0 4px;">
        {{ $coach->name }} · Coach WellCore
      </p>
      @if($invitation->intro_message)
      <p style="margin:0;color:#A1A1AA;">{{ nl2br(e($invitation->intro_message)) }}</p>
      @endif
    </td>
  </tr>
</table>

<!-- 2. Plan card -->
<div style="background:#09090B;border-radius:8px;padding:24px;margin:24px 0;border:1px solid #DC2626;">
  <p style="color:#DC2626;font-weight:bold;text-transform:uppercase;margin:0 0 8px;font-size:12px;">
    PLAN SELECCIONADO
  </p>
  <h2 style="color:#FAFAFA;margin:0 0 8px;font-size:24px;">{{ $planDetails['name'] }}</h2>
  <p style="color:#FAFAFA;font-size:22px;font-weight:bold;margin:0 0 16px;">
    ${{ number_format($invitation->amount, 0, '.', '.') }} {{ $invitation->currency }}/mes
  </p>
  <ul style="padding:0;margin:0;list-style:none;">
    @foreach($planDetails['features'] as $feature)
    <li style="color:#A1A1AA;padding:6px 0;border-bottom:1px solid #27272A;">
      <span style="color:#DC2626;">✓</span> {!! $feature !!}
    </li>
    @endforeach
  </ul>
</div>

<!-- 3. CTA principal -->
<div style="text-align:center;margin:32px 0;">
  <a href="{{ $invitationUrl }}" class="btn-primary">
    {{ strtoupper($invitation->cta_label) }}
  </a>
  <p class="muted" style="margin-top:12px;">
    Oferta válida hasta {{ $invitation->expires_at->format('d \d\e F \d\e Y') }}
  </p>
</div>

<!-- 4. Trust signals -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
  <tr>
    <td style="text-align:center;padding:8px;">
      <p class="muted">🔒 Pago seguro vía Wompi · PSE · Nequi · Tarjeta</p>
    </td>
  </tr>
</table>

<!-- Pixel de tracking (1x1, invisible) -->
<img src="{{ $pixelUrl }}" width="1" height="1" style="display:block;width:1px;height:1px;" alt="">
@endsection

@section('footer_extra')
<p class="muted">Recibiste este mensaje porque {{ $coach->name }} te envió una invitación personal.</p>
@endsection
```

### 8.3 Política de fuentes, colores, dark mode y compatibilidad

| Token | Valor | Uso |
|-------|-------|-----|
| `wc-bg` | `#09090B` | Background general email |
| `wc-bg-secondary` | `#18181B` | Cards |
| `wc-accent` | `#DC2626` | CTAs, iconos, bordes de énfasis |
| `wc-text` | `#FAFAFA` | Títulos y texto principal |
| `wc-text-muted` | `#A1A1AA` | Texto secundario |
| `wc-border` | `#27272A` | Separadores |

**Fuentes:** Google Fonts `Bebas Neue` + `Inter` vía `@import` en `<style>` + fallback `Arial, sans-serif` inline en todos los elementos. Outlook MSO usa siempre el fallback Arial.

**Dark mode:** Incluir `<meta name="color-scheme" content="dark">`. No usar CSS `@media (prefers-color-scheme: dark)` en el email — la plantilla ya es dark by default.

**Compatibilidad verificada (tabla mental):**
- ✅ Gmail web: tablas + inline styles → OK
- ✅ Gmail iOS/Android: sin Google Fonts → fallback Arial, colores inline → OK
- ✅ Outlook 2019+: MSO conditionals, tablas, fallback fonts → OK
- ✅ Apple Mail: soporta web fonts y dark mode → OK
- ✅ Yahoo Mail: tablas + inline → OK

---

## 9. Seguridad (checklist §10 del prompt)

| # | Requisito | Implementación | Estado |
|---|-----------|----------------|--------|
| 1 | CSRF en POST/PATCH/DELETE | Todos los endpoints API usan `auth:wellcore` Bearer token (stateless → no CSRF). Rutas públicas `/invitacion/` son GET-only (no modifican estado, solo leen/redirigen). ✅ | Cubierto |
| 2 | Rate limit anti-spam (por coach) | `throttle:50,1440` en `POST /v/coach/invitations` + check mensual en service. | Cubierto |
| 3 | Rate limit anti-bruteforce (IP) | `throttle:120,1` en `/invitacion/{code}` y `/invitacion-pixel/{code}`. | Cubierto |
| 4 | Validación email RFC + DNS MX | `'email:rfc,dns'` en `StoreInvitationRequest`. DNS check puede fallar en sandbox — documentar. | Cubierto |
| 5 | Sanitización de `intro_message` | `strip_tags($data['intro_message'])` en service antes de guardar. `nl2br(e($message))` al renderizar. | Cubierto |
| 6 | Verificación firma Wompi | Existente `WompiService::verifyWebhookSignature()` — NO tocar ni degradar. | Cubierto |
| 7 | Token único invitación CSPRNG | `bin2hex(random_bytes(16))` = 32 hex chars de entropía real (128 bits). NO usar `Str::random()`. | Cubierto |
| 8 | Authorization Policy | `CoachInvitationPolicy`: coach solo CRU sus propias invitaciones; admin+ ve/opera todo. `Gate::authorize()` en cada método del controller. | Cubierto |
| 9 | Audit log | `AuditService::logAction()` en: create, cancel, resend, paid (webhook). Patrón idéntico al existente en `runPostApprovalAutomation()`. | Cubierto |
| 10 | No leak de PII en logs | `Log::info()` solo registra `invitation_id`, `coach_id`, `status`. Nunca loguear el email completo en texto plano, ni la URL del payment link. | Cubierto |
| 11 | Resend con throttle | `resend_count < 3` verificado en service + en Policy. | Cubierto |
| 12 | Webhook idempotency key | Guard en `handlePaymentApproved()`: `if ($invitation->status === Paid) return;`. La dedup existente en `WompiService` también aplica. | Cubierto |
| 13 | No exponer payment links en logs | `wompi_payment_link_url` se guarda en DB pero no se loguea. En respuestas API solo se incluye si el coach es el dueño. | Cubierto |

---

## 10. Tests

### Tests Feature (Pest)

Archivo: `tests/Feature/Coach/InvitationTest.php`

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CoachInvitation;
use App\Enums\CoachInvitationStatus;
use App\Enums\PlanType;
use App\Services\CoachInvitationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

// --- T1: Crear invitación con email nuevo ---
it('coach puede crear invitación para email nuevo', function () {
    // Arrange
    Queue::fake();
    $coach = Admin::factory()->coach()->create();
    Http::fake(['*wompi*' => Http::response([
        'data' => ['id' => 'lnk_123', 'url' => 'https://checkout.wompi.co/l/abc']
    ], 200)]);

    // Act
    $response = $this->actingAsCoach($coach)
        ->postJson('/api/v/coach/invitations', [
            'email'   => 'nuevo@prospecto.com',
            'plan'    => 'metodo',
            'subject' => 'Te invito',
        ]);

    // Assert
    $response->assertStatus(201)
             ->assertJsonPath('data.status', 'sent')
             ->assertJsonPath('data.email', 'nuevo@prospecto.com');

    $this->assertDatabaseHas('coach_invitations', ['email' => 'nuevo@prospecto.com', 'status' => 'sent']);
    $this->assertDatabaseHas('payments', ['wompi_reference' => 'WCI-' . $response->json('data.code')]);
    Queue::assertPushed(\App\Mail\CoachClientInvitation::class);
});

// --- T2: Bloquear invitación a cliente activo ---
it('bloquea invitación si email pertenece a cliente activo', function () {
    // Arrange
    $coach  = Admin::factory()->coach()->create();
    $client = Client::factory()->active()->create(['email' => 'activo@cliente.com']);

    // Act
    $response = $this->actingAsCoach($coach)
        ->postJson('/api/v/coach/invitations', [
            'email' => 'activo@cliente.com',
            'plan'  => 'metodo',
            'subject' => 'Test',
        ]);

    // Assert
    $response->assertStatus(422)
             ->assertJsonPath('error_code', 'CLIENT_ACTIVE');
});

// --- T3: Webhook aprueba pago → cliente creado + coach asignado ---
it('webhook aprobado crea cliente y asigna coach', function () {
    // Arrange
    $coach      = Admin::factory()->coach()->create();
    $invitation = CoachInvitation::factory()->sent()->for($coach)->create([
        'email'         => 'nuevo@cliente.com',
        'wompi_reference'=> 'WCI-abc123',
    ]);
    // Payment pendiente creado cuando se generó la invitación
    $payment = Payment::factory()->pending()->create(['wompi_reference' => 'WCI-abc123']);

    // Act — simular webhook Wompi APPROVED
    $this->postJson('/webhooks/wompi', wompiWebhookPayload('WCI-abc123', 'APPROVED'))
         ->assertStatus(200);

    // Assert
    $invitation->refresh();
    expect($invitation->status)->toBe(CoachInvitationStatus::Paid);
    expect($invitation->client_id)->not->toBeNull();

    $this->assertDatabaseHas('client_coach', [
        'admin_id' => $coach->id,
        'source'   => 'coach_invitation',
        'active'   => true,
    ]);

    $this->assertDatabaseHas('clients', ['email' => 'nuevo@cliente.com', 'status' => 'activo']);
});

// --- T4: Webhook idempotente (llega 2 veces) ---
it('webhook duplicado no duplica cliente ni coach assignment', function () {
    // Arrange
    $coach      = Admin::factory()->coach()->create();
    $invitation = CoachInvitation::factory()->paid()->for($coach)->create(['wompi_reference' => 'WCI-dup']);
    $payment    = Payment::factory()->approved()->create(['wompi_reference' => 'WCI-dup']);

    // Act — enviar webhook 2 veces
    $webhookPayload = wompiWebhookPayload('WCI-dup', 'APPROVED');
    $this->postJson('/webhooks/wompi', $webhookPayload)->assertStatus(200);
    $this->postJson('/webhooks/wompi', $webhookPayload)->assertStatus(200);

    // Assert — solo 1 registro en client_coach
    $this->assertDatabaseCount('client_coach', 1);
});

// --- T5: Rate limit de invitaciones (50/día) ---
it('bloquea al coach si supera 50 invitaciones en un día', function () {
    // Arrange
    $coach = Admin::factory()->coach()->create();
    CoachInvitation::factory()->count(50)->for($coach)->sentToday()->create();

    // Act
    $response = $this->actingAsCoach($coach)
        ->postJson('/api/v/coach/invitations', basicInvitationPayload());

    // Assert
    $response->assertStatus(429);
});

// --- T6: Reenviar invitación expirada ---
it('reenviar invitación expirada genera nuevo payment link', function () {
    // Arrange
    Queue::fake();
    Http::fake(['*wompi*' => Http::response(['data' => ['id' => 'lnk_new', 'url' => 'https://checkout.wompi.co/l/new']], 200)]);
    $coach      = Admin::factory()->coach()->create();
    $invitation = CoachInvitation::factory()->expired()->for($coach)->create(['resend_count' => 1]);

    // Act
    $response = $this->actingAsCoach($coach)
        ->postJson("/api/v/coach/invitations/{$invitation->id}/resend");

    // Assert
    $response->assertStatus(200)->assertJsonPath('data.resend_count', 2);
    $invitation->refresh();
    expect($invitation->status)->toBe(CoachInvitationStatus::Sent);
    expect($invitation->expires_at->isFuture())->toBeTrue();
    Queue::assertPushed(\App\Mail\CoachClientInvitation::class);
});

// --- T7: Ruta pública tracking click ---
it('/invitacion/{code} registra click y redirige a Wompi', function () {
    // Arrange
    $invitation = CoachInvitation::factory()->sent()->create([
        'wompi_payment_link_url' => 'https://checkout.wompi.co/l/test',
    ]);

    // Act
    $response = $this->get('/invitacion/' . $invitation->code);

    // Assert
    $response->assertRedirect('https://checkout.wompi.co/l/test');
    $invitation->refresh();
    expect($invitation->status)->toBe(CoachInvitationStatus::LinkClicked);
    expect($invitation->clicked_at)->not->toBeNull();
});

// --- T8: Ruta pública invitación expirada → vista de error ---
it('/invitacion/{code} muestra error si invitación expirada', function () {
    // Arrange
    $invitation = CoachInvitation::factory()->expired()->create();

    // Act
    $response = $this->get('/invitacion/' . $invitation->code);

    // Assert
    $response->assertStatus(200)->assertViewIs('coach.invitation-expired');
});

// --- T9: Coach no puede ver invitaciones de otro coach ---
it('coach no puede acceder a invitaciones de otro coach', function () {
    // Arrange
    $coach1 = Admin::factory()->coach()->create();
    $coach2 = Admin::factory()->coach()->create();
    $inv    = CoachInvitation::factory()->for($coach2)->create();

    // Act
    $response = $this->actingAsCoach($coach1)
        ->getJson("/api/v/coach/invitations/{$inv->id}");

    // Assert
    $response->assertStatus(403);
});

// --- T10: Cancelar invitación ---
it('coach puede cancelar invitación enviada', function () {
    // Arrange
    $coach = Admin::factory()->coach()->create();
    $inv   = CoachInvitation::factory()->sent()->for($coach)->create();

    // Act
    $response = $this->actingAsCoach($coach)
        ->deleteJson("/api/v/coach/invitations/{$inv->id}");

    // Assert
    $response->assertStatus(200)->assertJsonPath('data.status', 'cancelled');
    $inv->refresh();
    expect($inv->cancelled_at)->not->toBeNull();
});
```

### Tests Unit

Archivo: `tests/Unit/Services/CoachInvitationServiceTest.php`

```php
// T-U1: enforceRateLimit lanza excepción si coach tiene 50 invitaciones hoy
it('enforceRateLimit lanza si supera límite diario', function () {
    $coach = Admin::factory()->coach()->create();
    CoachInvitation::factory()->count(50)->for($coach)->sentToday()->create();
    
    expect(fn() => app(CoachInvitationService::class)->create($coach, basicData()))
        ->toThrow(RateLimitException::class);
});

// T-U2: expireOverdue retorna el número correcto de registros actualizados
it('expireOverdue actualiza solo invitaciones vencidas no terminales', function () {
    CoachInvitation::factory()->count(3)->expired()->create();   // ya expired
    CoachInvitation::factory()->count(2)->sent()->pastDue()->create(); // vencidas sin actualizar
    CoachInvitation::factory()->count(1)->paid()->create();      // no debe tocar

    $count = app(CoachInvitationService::class)->expireOverdue();
    expect($count)->toBe(2);
});

// T-U3: CoachInvitationStatus::isTerminal() correcto
it('estados terminales son paid y cancelled', function () {
    expect(CoachInvitationStatus::Paid->isTerminal())->toBeTrue();
    expect(CoachInvitationStatus::Cancelled->isTerminal())->toBeTrue();
    expect(CoachInvitationStatus::Sent->isTerminal())->toBeFalse();
    expect(CoachInvitationStatus::Expired->isTerminal())->toBeFalse();
});
```

---

## 11. Plan de migración de datos

No hay datos existentes que migrar. Las tablas son nuevas. No se modifica ninguna tabla existente.

**Verificación pre-deploy:**
```sql
-- Confirmar que no existe tabla con estos nombres (debería retornar empty)
SELECT TABLE_NAME FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'wellcore_fitness'
  AND TABLE_NAME IN ('coach_invitations', 'client_coach');
```

---

## 12. Plan de despliegue

**Orden obligatorio (sin `npm run build` local):**

1. **Local:** Ejecutar migraciones en dev (`wellcore-laravel.test`) y verificar
2. **Local:** `git add` + `git commit` de todo el código (migraciones, modelos, service, controller, views, Vue, tests)
3. **Local:** `git push origin main`
4. **EasyPanel:** Ejecutar script `gitpull-load` vía consola bash del container (NO Rebuild Docker)
5. **EasyPanel:** Consola del container → `php artisan migrate --force` (solo las 2 nuevas migraciones)
6. **EasyPanel:** Verificar que el job `CoachInvitationsExpireJob` está registrado en el scheduler
7. **Verificar en `wellcorefitness.com`** con las pruebas manuales de §QA abajo

**Rollback:**
- Si las migraciones fallan: `php artisan migrate:rollback --step=2`
- Las 2 tablas nuevas son DROP-safe (no tocan tablas existentes)
- El delta en `WebhookController` es backwards-compatible (el `if` solo actúa en referencias `WCI-*`)

---

## 13. Riesgos abiertos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| Wompi no soporta `expires_at` > 30 días | Baja | Medio | Forzar `expires_in_days <= 28` en validación; documentar |
| Pixel de tracking bloqueado por iOS 15+/Gmail pre-carga | Alta | Bajo | Informar en UI: "tracking de apertura es aproximado" |
| Webhook llega antes de que `handlePaymentApproved` termine | Muy baja | Alto | `DB::transaction()` y guard idempotente en `handlePaymentApproved` |
| Coach invita a email que cambia de `@` antes de pagar | Muy baja | Bajo | El email en `coach_invitations` no cambia; se crea cliente con ese email original |
| `CoachInvitation::create()` falla a mitad (Payment creado pero Wompi no) | Baja | Medio | Wrap todo en `DB::transaction()` + rollback si `createPaymentLink()` falla; devolver 503 al coach |
| Rate limit de 50/día bloquea a admin que gestiona invitaciones de múltiples coaches | Media | Medio | El rate limit aplica por `coach_id` (admin_id), no por IP. Un admin que opera como coach tiene su propio límite |
| DNS check de email rechaza dominios válidos en sandbox | Media | Bajo | Usar `'email:rfc'` en sandbox (`APP_ENV=local`), `'email:rfc,dns'` en producción. Configurar via env |

---

## 14. Out-of-scope (V2)

- Editor global de plantillas de email tipo Mailchimp
- Comisión del coach por clientes captados (requiere definición de % y procesamiento de pagos)
- Mailjet open/click webhooks para tracking preciso de apertura
- Invitaciones en bulk (CSV de emails)
- Plantillas predefinidas guardadas por coach
- Analytics avanzados de conversión por coach (embudo completo)
- Suscripciones recurrentes vía invitación (Wompi no soporta en V1)
- Refactorizar los 9 Mailables existentes para usar `wellcore-base.blade.php` (deuda técnica, se paga en V2)
- Límites configurables por admin en panel (hoy hardcodeados en service)

---

## 15. Estimación y orden de ejecución

```
Semana 1 — Backend (paralelo)
├── la-06-database  : Migraciones (2h)                      [bloquea todo]
├── la-02-backend   : Modelos + Enum + CoachInvitationService (6h)
└── la-05-security  : StoreInvitationRequest + Policy (2h)

Semana 1 — Backend (secuencial, tras migraciones)
├── la-15-api       : InvitationController + rutas API (4h)
├── la-09-payments  : Extensión WompiService + webhook delta (3h)
└── la-04-tailwind-ds: Email layout + CoachClientInvitation mailable + blade (4h)

Semana 2 — Frontend (paralelo al backend-testing)
├── la-03-vue3      : InvitationManager + Form + List + Preview + Store + Composable (10h)
└── la-14-testing   : Feature tests + Unit tests (6h)

Semana 2 — Cierre
├── la-10-performance: Verificar índices, N+1 en listar (1h)
└── la-07-devops    : Deploy script verificación (1h)

Total estimado: ~39h de agente (paralelizable a ~20h wall-clock)
```

**Gantt textual:**
```
D1  [la-06-database: migraciones] ←── bloquea D2+
D2  [la-02-backend: modelos+service] + [la-05-security: requests+policy]  (paralelo)
D3  [la-15-api: controller+rutas] + [la-09-payments: webhook delta]       (paralelo)
D4  [la-04-tailwind-ds: email layout+mailable+blade]
D5  [la-03-vue3: componentes+store] (requiere endpoints listos)
D6  [la-14-testing: tests feature+unit]
D7  [la-10-performance: review] + [la-07-devops: deploy] + QA manual
```

---

## 16. Criterios de aceptación (Given/When/Then)

**CA-1: Crear invitación nueva**
- **Given** un coach autenticado con < 50 invitaciones hoy
- **When** POST `/v/coach/invitations` con email nuevo, plan `metodo`, subject
- **Then** status 201, `coach_invitations` tiene el registro con `status=sent`, `payments` tiene registro con `status=pending` y `wompi_reference=WCI-{code}`, email encolado, `wompi_payment_link_url` no vacío

**CA-2: Bloqueo cliente activo**
- **Given** un coach autenticado y un cliente activo con email `activo@test.com`
- **When** POST `/v/coach/invitations` con `email: activo@test.com`
- **Then** status 422, `error_code: CLIENT_ACTIVE`, sin registros creados en DB

**CA-3: Tracking de click**
- **Given** una invitación `sent` con code `abc123`
- **When** GET `/invitacion/abc123` (no autenticado)
- **Then** redirect 302 a la URL de Wompi, `coach_invitations.status=link_clicked`, `clicked_at` seteado

**CA-4: Pago aprobado → cliente creado y coach asignado**
- **Given** invitación `link_clicked` con email de persona sin cuenta, `wompi_reference=WCI-abc123`
- **When** POST `/webhooks/wompi` con payload APPROVED para `WCI-abc123` (firma válida)
- **Then** `coach_invitations.status=paid`, `clients` tiene registro con ese email y `status=activo`, `client_coach` tiene registro con `coach_id` correcto y `source=coach_invitation`, `WelcomeMail` encolado

**CA-5: Idempotencia webhook**
- **Given** invitación ya en status `paid`
- **When** POST `/webhooks/wompi` con mismo payload APPROVED (segunda vez)
- **Then** status 200, sin cambios en DB, sin emails duplicados

**CA-6: Expiración**
- **Given** invitación `sent` con `expires_at` en el pasado
- **When** `CoachInvitationsExpireJob::dispatch()` corre
- **Then** `coach_invitations.status=expired`

**CA-7: Reenvío**
- **Given** invitación `expired` con `resend_count=1`
- **When** POST `/v/coach/invitations/{id}/resend`
- **Then** status 200, `status=sent`, `resend_count=2`, nuevo `wompi_payment_link_url` (diferente al anterior), `expires_at` extendido, email encolado

**CA-8: Rate limit**
- **Given** coach que ya tiene 50 invitaciones creadas hoy
- **When** POST `/v/coach/invitations`
- **Then** status 429 con mensaje de límite diario

**CA-9: Preview HTML**
- **Given** coach autenticado
- **When** POST `/v/coach/invitations/preview` con datos del formulario
- **Then** status 200, `html` es string no vacío que contiene el email, sin registros en DB, sin emails enviados

**CA-10: Authorization**
- **Given** coach_A autenticado, invitación perteneciente a coach_B
- **When** GET `/v/coach/invitations/{id_de_B}`
- **Then** status 403

---

## QA Manual pre-deploy (en `wellcore-laravel.test`)

1. Login como coach → navegar a `/coach/invitations` → confirmar que carga sin errores.
2. Crear invitación con email de Gmail real → confirmar que llega el email branded con CTA correcto.
3. Hacer clic en "Vista previa" → confirmar que el iframe muestra el email con foto del coach.
4. Intentar enviar sin hacer preview → confirmar que el botón "Enviar" está deshabilitado.
5. Crear invitación con email de cliente activo → confirmar error 422 con mensaje claro.
6. En Wompi sandbox, completar el pago → confirmar que el cliente queda creado en DB con status `activo`.
7. Verificar `client_coach` tiene el registro correcto con `source=coach_invitation`.
8. Enviar segundo webhook (simular con `curl`) → confirmar que no se duplica el cliente.
9. Reenviar una invitación expirada → confirmar que se genera nuevo link y se envía email.
10. Cancelar una invitación enviada → confirmar que `status=cancelled`.

---

*Plan generado por el equipo técnico WellCore — 2026-04-24*  
*Basado en auditoría de `app/Services/WompiService.php`, `app/Models/Invitation.php`, `app/Http/Controllers/WebhookController.php`, `routes/api.php` y 9 mailables existentes.*
