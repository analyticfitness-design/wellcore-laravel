# Spec — Impersonificación de Coaches desde Superadmin

**Fecha:** 2026-04-27
**Autor:** Daniel Esparza
**Estado:** Diseño aprobado — pendiente plan de implementación
**Branch sugerida:** `feat/coach-impersonation`

---

## 1. Resumen ejecutivo

Permitir al `superadmin` entrar al portal de cualquier coach (o admin/jefe) directamente desde el panel `Admin/CoachManagement.vue`, con auditoría completa, soporte para encadenar a un cliente del coach, y restauración limpia de la sesión original.

El sistema replica el patrón ya probado de `Coach → Cliente` (`CoachController::impersonate` API JSON) y lo generaliza para soportar `Superadmin → Coach` y la cadena `Superadmin → Coach → Cliente`. Reemplaza el `CoachImpersonateController` actual que tiene varios bugs (sesión rota al volver, sin auditoría, incompatible con la SPA Vue).

---

## 2. Decisiones de producto

| # | Decisión | Valor |
|---|---|---|
| 1 | Alcance | Refactor backend completo + UI nueva + auditoría + banner |
| 2 | Roles autorizados a impersonificar | Solo `superadmin` |
| 3 | Permisos del impersonador | Modo espejo (read + write completo) con auditoría por acción |
| 4 | Doble impersonificación | Permitida en cadena `superadmin → coach → cliente`. El botón "Volver" siempre regresa al superadmin original (saltando el coach intermedio) |
| 5 | Notificación al coach | Silencioso total. No hay aviso al coach. Auditoría queda registrada y consultable. |
| 6 | TTL token coach | 60 minutos |
| 7 | TTL token cliente (cadena) | 30 minutos |
| 8 | Renovación de token | No permitida (extender = stop + start nuevo) |
| 9 | Self-impersonate | Bloqueado |
| 10 | Impersonificar a otro superadmin | Bloqueado |
| 11 | Coach inactivo (`active = false`) | Permitido (caso de soporte: investigar coach desactivado) |

---

## 3. Arquitectura general

### 3.1 Diagrama de flujo

```
┌─────────────────────────────────────────────────────────────────────┐
│ Vue SPA — Admin/CoachManagement.vue                                  │
│  [Editar] [Reset pass] [Desactivar] [👁 Ver portal]  ← nuevo botón   │
└──────────────────────┬──────────────────────────────────────────────┘
                       │ axios.post (con modal de confirmación)
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ POST /api/v/admin/coaches/{id}/impersonate         (nuevo endpoint) │
│ Middleware: api.bearer + role:superadmin + throttle:impersonate     │
│   • Valida: target.role ∈ {coach, jefe, admin}, no es self          │
│   • Si NO hay impersonificación activa → guarda wc_root_token       │
│   • Si YA hay (encadenado) → no toca wc_root_token                  │
│   • Genera AuthToken nuevo (TTL 60min) con impersonation_log_id     │
│   • Crea ImpersonationLog (target_type='admin', via_actor si aplica)│
│   • Retorna JSON: { token, redirect_url, log_id, expires_at }       │
└─────────────────────────────────────────────────────────────────────┘
                       │ JSON response
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ Vue SPA actualiza localStorage:                                      │
│   wc_root_token (1ª vez), wc_token, wc_user_type='admin',           │
│   wc_user_name, wc_user_portal='/coach',                             │
│   wc_impersonation_chain (array JSON)                                │
│ Hard redirect → /coach (carga CoachLayout)                          │
└─────────────────────────────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ Banner SuperadminImpersonationBanner.vue (montado en CoachLayout,   │
│   ClientLayout, RiseLayout):                                         │
│   "Viendo como Pedro Rodríguez (coach) · 47:23"                      │
│   [← Volver al panel admin]  → POST /api/v/admin/impersonate/end    │
└─────────────────────────────────────────────────────────────────────┘
                       │ click "Volver"
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ POST /api/v/admin/impersonate/end           (nuevo endpoint stop)   │
│   • Lee wc_impersonation_chain de la sesión                         │
│   • Cierra TODOS los logs en cadena (ended_at = now())              │
│   • Borra los AuthToken creados durante impersonificación           │
│   • Restaura sesión PHP usando wc_root_token + datos del superadmin │
│   • Limpia slots wc_admin_token, wc_root_*, wc_impersonation_chain  │
│   • Retorna JSON: { restored: true, redirect_url: '/admin/coaches' }│
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 Componentes nuevos

| Tipo | Path |
|---|---|
| Controller | `app/Http/Controllers/Api/AdminImpersonateController.php` |
| Vue component | `resources/js/vue/components/SuperadminImpersonationBanner.vue` |
| Migración 1 | `database/migrations/2026_04_27_NNNNNN_extend_impersonation_logs_for_admin_targets.php` |
| Migración 2 | `database/migrations/2026_04_27_NNNNNN_add_impersonation_log_id_to_auth_tokens.php` |
| Comando | `app/Console/Commands/CloseOrphanedImpersonations.php` |
| Comando | `app/Console/Commands/ImpersonationReport.php` |

### 3.3 Componentes modificados

| Path | Cambio |
|---|---|
| `app/Http/Controllers/CoachImpersonateController.php` | Deprecar — start/stop redirigen al nuevo API. Mantener temporalmente para no romper enlaces viejos. |
| `app/Http/Controllers/Api/CoachController.php::impersonate` y `endImpersonation` | (a) Detectar si el coach actual está siendo impersonificado por superadmin → registrar log con `actor = superadmin`, `via_actor = coach`. (b) **Migrar de `wc_admin_token` (slot único) al modelo unificado de `wc_root_token` + `wc_impersonation_chain`**, alineándose con el nuevo controller. Sin esto, la cadena no funciona. |
| `resources/js/vue/pages/Admin/CoachManagement.vue` | Agregar botón "Ver Portal" + modal de confirmación |
| `resources/js/vue/layouts/CoachLayout.vue` | Montar `SuperadminImpersonationBanner` |
| `resources/js/vue/layouts/ClientLayout.vue` | Mismo |
| `resources/js/vue/layouts/RiseLayout.vue` | Mismo |
| `resources/js/vue/composables/useImpersonation.js` | Agregar flag `isImpersonatingCoach` y unificar lectura de `wc_impersonation_chain` |
| `resources/js/vue/stores/auth.js` (Pinia) | Agregar acciones `startImpersonation(payload)` y `endImpersonation()` que (a) hacen el axios call al backend, (b) actualizan el state interno del store, (c) sincronizan con `localStorage`. La SPA NO debe escribir directamente a `localStorage` desde componentes — todo pasa por el store para mantener single source of truth. `useApi.js` se queda intacto: ya lee de `authStore.token` y eso basta. |
| `app/Models/ImpersonationLog.php` | Agregar fillable y scopes para nuevos campos |
| `app/Providers/AppServiceProvider.php` | Configurar rate limiter `impersonate` (5/min, 50/día) |
| `routes/api.php` | Registrar las dos rutas nuevas |
| `app/Http/Controllers/Api/AuthController.php::logout` | **Crítico — bug pre-existente**. Hoy hace `session()->flush()` lo cual destruye `wc_root_token`. Cambiar a: si el token activo tiene `impersonation_log_id`, NO hacer `flush()` — en su lugar, delegar al endpoint stop (cierra logs en cadena, restaura `wc_root_token`, retorna `{ redirect: '/admin/coaches' }`). Si NO tiene `impersonation_log_id`, comportamiento actual sin cambios. |
| `resources/views/vue.blade.php` | **Crítico — reinyecta `__WC_SESSION` en cada cold-load de la SPA**. Hoy expone solo `token, userType, userId, userName, portal, impersonating, adminToken`. Agregar: `rootToken`, `rootUserId`, `rootUserName`, `impersonationChain`. Sin esto, la SPA recargada en `/coach` no sabría que está bajo impersonificación encadenada y el banner se renderizaría mal. |

---

## 4. Modelo de datos

### 4.1 Migración aditiva a `impersonation_logs`

```php
Schema::table('impersonation_logs', function (Blueprint $table) {
    // Generalizar el target (hoy implícitamente 'client')
    $table->string('target_type', 20)->default('client')->after('actor_name');
    $table->unsignedBigInteger('target_id')->nullable()->after('target_type')->index();
    $table->string('target_name', 150)->nullable()->after('target_id');

    // Cadena: registrar al coach intermediario cuando aplica
    $table->string('via_actor_type', 20)->nullable()->after('actor_name');
    $table->unsignedBigInteger('via_actor_id')->nullable()->after('via_actor_type');
    $table->string('via_actor_name', 150)->nullable()->after('via_actor_id');

    $table->index(['target_type', 'target_id']);
});
```

**Estrategia de retro-compatibilidad:**
- Los campos `target_client_id` / `target_client_name` se mantienen en la tabla.
- Los logs nuevos pueblan ambos: `target_id` (genérico) y `target_client_id` (cuando target es cliente).
- Queries legacy siguen funcionando sin cambios.

### 4.2 Migración a `auth_tokens` — kill switch operacional

```php
Schema::table('auth_tokens', function (Blueprint $table) {
    $table->unsignedBigInteger('impersonation_log_id')->nullable()->after('expires_at')->index();
});
```

Permite:
- Distinguir tokens legítimos de tokens emitidos por impersonificación.
- Revocación masiva en incidente: `AuthToken::whereNotNull('impersonation_log_id')->delete()`.

### 4.3 Modelo `App\Models\ImpersonationLog`

```php
#[Fillable([
    'actor_type', 'actor_id', 'actor_name',
    'via_actor_type', 'via_actor_id', 'via_actor_name',
    'target_type', 'target_id', 'target_name',
    'target_client_id', 'target_client_name',  // legacy
    'token', 'started_at', 'ended_at', 'ip', 'user_agent',
])]
class ImpersonationLog extends Model
{
    protected $table = 'impersonation_logs';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function scopeOpenChainOf($query, string $actorType, int $actorId)
    {
        return $query->where('actor_type', $actorType)
                     ->where('actor_id', $actorId)
                     ->whereNull('ended_at');
    }
}
```

### 4.4 Slots de sesión PHP + localStorage SPA

| Clave | Set en | Reset en | Significado |
|---|---|---|---|
| `wc_root_token` | start (1ª vez) | end final | Token original del superadmin |
| `wc_root_user_id` | start (1ª vez) | end final | ID del superadmin original |
| `wc_root_user_name` | start (1ª vez) | end final | Nombre para el botón "Volver al panel admin" |
| `wc_token` | cada start | end final | Token activo (cambia por nivel) |
| `wc_user_type` | cada start | end final | `'admin'` o `'client'` |
| `wc_user_id` | cada start | end final | ID del usuario actualmente impersonificado |
| `wc_user_name` | cada start | end final | Nombre activo |
| `wc_user_portal` | cada start | end final | `/coach`, `/client`, `/rise` |
| `wc_impersonation_chain` | cada start | end final | Array JSON con `{ level, log_id, token, target_type, target_id, target_name, via_actor_* }` |

**Ejemplo de `wc_impersonation_chain` después de `superadmin → coach Pedro → cliente Juan`:**

```json
[
  { "level": 1, "log_id": 42, "token": "abc123...", "target_type": "admin",
    "target_id": 7, "target_name": "Pedro Rodríguez" },
  { "level": 2, "log_id": 43, "token": "def456...", "target_type": "client",
    "target_id": 99, "target_name": "Juan Pérez",
    "via_actor_type": "admin", "via_actor_id": 7, "via_actor_name": "Pedro Rodríguez" }
]
```

### 4.5 Datos NO almacenados

- No se loguean páginas visitadas durante la impersonificación (privacy + verbosidad).
- No se loguean diffs de cambios — esa responsabilidad la sigue cumpliendo el helper `audit()` existente (trait `App\Traits\Auditable`).

### 4.6 Cómo se enlazan las acciones a la impersonificación

**Hallazgo del review:** la tabla `audit_logs` (escrita por el trait `Auditable`) NO tiene columna `impersonation_log_id`. Tiene un campo `diff` (JSON nullable) donde se guardan datos contextuales.

**Decisión (sin migración a `audit_logs`):** convención — cuando hay impersonificación activa, los controladores que invocan `audit()` deben incluir el `impersonation_log_id` dentro del array `diff`:

```php
// patrón estándar a usar en TODOS los controllers que escriben durante impersonificación
$this->audit('checkin.respond', $checkin, [
    'response_text' => $response,
    'impersonation_log_id' => session('wc_impersonation_chain.0.log_id'),  // null si no hay
], $checkin->title);
```

Para evitar repetir la lógica en cada controller, se agrega un método helper al trait `Auditable`:

```php
// app/Traits/Auditable.php — agregar
protected function currentImpersonationLogId(): ?int
{
    $chain = session('wc_impersonation_chain', []);
    return is_array($chain) && !empty($chain)
        ? (int) end($chain)['log_id'] ?? null
        : null;
}
```

Y `audit()` se modifica para que **automáticamente** lo incluya en el `diff` cuando aplica, sin requerir cambios en cada call site existente.

---

## 5. UI / UX

### 5.1 Botón en `Admin/CoachManagement.vue`

Posición: columna **Acciones**, después de Editar / Reset / Desactivar.

```
[✏ Editar] [🔑 Reset] [⊗ Desactivar] [👁 Ver portal]
```

**Visibilidad:**
- ✅ Visible si `coach.role` ∈ `{coach, jefe, admin}`
- ❌ Oculto si `coach.role === 'superadmin'`
- ❌ Oculto si `coach.id === currentUser.id`
- ⚠️ Visible (con badge "Inactivo") si `coach.active === false`

Tooltip: *"Ver el portal del coach (queda registrado en auditoría)"*.

### 5.2 Modal de confirmación

```
┌────────────────────────────────────────────────────┐
│ VER PORTAL DE COACH                                │
│                                                    │
│ Estás por entrar al portal de:                     │
│   👤 Pedro Rodríguez (@pedro)                      │
│                                                    │
│ • Verás todo lo que ve Pedro                       │
│ • Las acciones quedan en su nombre, marcadas como  │
│   impersonificación en la auditoría                │
│ • Puedes volver cuando quieras desde el banner     │
│ • Sesión: 60 minutos                               │
│                                                    │
│              [Cancelar]  [Entrar al portal]        │
└────────────────────────────────────────────────────┘
```

### 5.3 Banner `SuperadminImpersonationBanner.vue`

Banner persistente fijo top, estilo `bg-wc-accent` (igual al `CoachImpersonationBanner.vue` existente), con tres variantes según contexto leído desde `wc_impersonation_chain`:

| Variante | Texto |
|---|---|
| Coach directo | `Viendo como Pedro Rodríguez (coach) · 47:23` |
| Cadena coach→cliente | `Viendo como Juan Pérez (cliente) vía Pedro Rodríguez (coach)` |
| Admin/jefe directo | `Viendo como Carlos López (admin) · 47:23` |

Botón derecho siempre: `[← Volver al panel admin]` → `POST /api/v/admin/impersonate/end`.

**Comportamientos:**
- Cuenta regresiva visible del token (60min coach, 30min cliente). Cuando bajan a ≤5min, banner pulsa amarillo.
- Si expira mientras está activo: banner cambia a "Sesión expirada", botón fuerza stop limpio.
- Banner se monta en `CoachLayout.vue`, `RiseLayout.vue`, `ClientLayout.vue`. El composable `useImpersonation()` decide visibilidad leyendo `wc_root_token`.

### 5.4 Estados de error

| Caso | UX |
|---|---|
| Coach desactivado | Modal aclara *"Este coach está inactivo en la plataforma. Estás entrando solo en modo soporte para investigar."* — permitir continuar |
| Token expira en navegación | Banner → "Sesión expirada. Volver al panel." Acciones bloqueadas. Click → endpoint stop |
| Rate-limited (`throttle:impersonate`) | Toast: *"Demasiados intentos. Espera 1 minuto."* |
| 403 inesperado | Toast: *"No tienes permiso para impersonificar este usuario."* |
| Pierde red durante stop | Reintenta 2 veces; si falla, ofrece logout manual con botón "Cerrar sesión y volver" |

### 5.5 Out of scope (futuras iteraciones)

- Indicador de "impersonificaciones activas en este momento" en dashboard admin.
- Vista web del reporte de impersonificación (solo CLI por ahora).
- Notificación al coach sobre impersonificación pasada.

---

## 6. Seguridad

### 6.1 Defensa en profundidad — 4 capas

```
[Capa 1: Grupo de ruta] el grupo /api/v/admin ya tiene 'auth:wellcore' + 'role:admin,superadmin,jefe'
[Capa 1b: Ruta]         agregar 'role:superadmin' + 'throttle:impersonate' al endpoint específico.
                        Laravel acumula middlewares — pasa ambos chequeos en orden.
[Capa 2: Controller]    re-verifica $user->role === UserRole::Superadmin
[Capa 3: Target]        target debe ser Admin con rol ∈ {coach, jefe, admin}, no self
[Capa 4: Estado]        si encadenando, validar wc_root_token aún válido (existe en auth_tokens, no expirado)
```

### 6.2 Rate limiter `impersonate`

**Nota:** el rate limiter `impersonate` **ya existe** en `AppServiceProvider.php:155` con `Limit::perMinute(10)`. NO se cambia ese valor (rompería el flujo legítimo `coach → cliente` que también lo usa). En su lugar, **se agrega un segundo límite diario**:

```php
// app/Providers/AppServiceProvider.php — modificar el RateLimiter existente
RateLimiter::for('impersonate', function ($request) {
    $userId = optional($request->user())->id;
    $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

    return [
        Limit::perMinute(10)->by($key),    // existente, no tocar
        Limit::perDay(50)->by($key),       // NUEVO — defensa adicional contra abuso sostenido
    ];
});
```

**Riesgo aceptado documentado:** la ruta `/api/v/coach/clients/{id}/impersonate` también usa este throttle. Cuando un superadmin que está impersonificando un coach llama a esa ruta para encadenar a un cliente, el `request->user()` resuelve al **coach intermedio** (no al superadmin), por lo que el throttle se computa contra el coach. Es un caso raro y baja severidad (el superadmin tendría que cambiar de coach intermedio constantemente para evadirlo). No es bloqueante para esta entrega; se monitorea vía `wellcore:impersonation-report`.

### 6.3 Propiedades del token de impersonificación

| Propiedad | Valor |
|---|---|
| Longitud | 64 chars hex (32 bytes random) |
| TTL coach target | 60 minutos |
| TTL cliente target (cadena) | 30 minutos |
| Marca distintiva | `auth_tokens.impersonation_log_id` (FK lógica nullable) |
| Renovación | NO permitida |
| Borrado al stop | Inmediato (`AuthToken::where(...)->delete()`) |

### 6.4 Logging de seguridad

**Hallazgo del review:** el canal `security` NO existe hoy en `config/logging.php` (canales actuales: `single`, `daily`, `slack`, `papertrail`, `stderr`, `syslog`, `errorlog`, `null`, `emergency`, `sentry`, `stack`).

**Decisión:** agregar un canal `security` dedicado a `config/logging.php`, con archivo separado y retención más larga que el log normal.

```php
// config/logging.php — agregar dentro de 'channels' =>
'security' => [
    'driver' => 'daily',
    'path' => storage_path('logs/security.log'),
    'level' => env('LOG_SECURITY_LEVEL', 'info'),
    'days' => env('LOG_SECURITY_DAYS', 90),  // 90 días para auditoría retroactiva
    'replace_placeholders' => true,
],
```

**Eventos a loguear** vía `Log::channel('security')->info(...)`:

- `IMPERSONATE_START` — superadmin_id, target_id, target_type, IP, user_agent, log_id
- `IMPERSONATE_END` — superadmin_id, log_id, duración total en segundos
- `IMPERSONATE_DENIED` — intento bloqueado por validación (rol/target/self)
- `IMPERSONATE_RATE_LIMITED` — intento bloqueado por throttle
- `IMPERSONATE_CHAIN_DETECTED` — inicio de 2º nivel encadenado
- `IMPERSONATE_LOGOUT_REDIRECTED` — un logout fue redirigido a stop por tener `impersonation_log_id`

### 6.5 Comandos artisan

- `php artisan wellcore:close-orphaned-impersonations` (scheduled diario): cierra logs con `ended_at = NULL` cuyo token ya expiró.
- `php artisan wellcore:impersonation-report --days=7`: tabla con superadmin, total, coaches únicos, duración promedio, alertas (>5 impersonificaciones a un mismo coach en 24h, sesiones >50min, etc.).

---

## 7. Testing

### 7.1 Tests obligatorios — `tests/Feature/Admin/ImpersonateCoachTest.php`

```
✅ superadmin can start coach impersonation
✅ admin (non-super) cannot start coach impersonation                    [403]
✅ coach cannot start coach impersonation                                [403]
✅ unauthenticated cannot                                                [401]
✅ cannot impersonate yourself                                           [422]
✅ cannot impersonate a superadmin target                                [422]
✅ cannot impersonate a non-existent admin                               [404]
✅ rate limit kicks in after 10 starts in 1 min (alineado con limiter real) [429]
✅ rate limit kicks in after 50 starts in 1 day                          [429]
✅ start creates ImpersonationLog with target_type='admin'
✅ start creates AuthToken with impersonation_log_id linked
✅ end closes the log with ended_at and deletes the AuthToken
✅ end is idempotent (call twice = no error, second is no-op)
✅ wc_root_token is preserved across single-level impersonation
✅ chain: superadmin → coach → client preserves wc_root_token
✅ chain: end closes BOTH logs and deletes BOTH tokens
✅ end with no active impersonation is a no-op (no error, no log change)
✅ expired impersonation token returns 401 on protected route
✅ token created during impersonation cannot be renewed via login flow
```

### 7.2 Tests de auditoría — `tests/Feature/Admin/ImpersonationAuditTest.php`

```
✅ audit() helper auto-attaches impersonation_log_id when chain active
✅ legacy target_client_id is still populated when target is client
✅ logs query by actor_id returns only that admin's history
```

### 7.3 Casos edge documentados

| Edge case | Comportamiento esperado |
|---|---|
| Superadmin cierra browser sin "Volver" | Token expira en TTL natural. Log con `ended_at = NULL` se cierra por comando scheduled diario. |
| Coach borrado en BD durante impersonificación | Siguiente request → 404. Banner: "Sesión inválida". Botón fuerza stop. |
| Superadmin se desactiva mientras impersonifica | Stop forzado por middleware al detectar `active = false` en `wc_root_user_id`. |
| Token filtrado/copiado | TTL duro lo limita. Auditoría muestra IP del posible filtrador. |
| 2 superadmins impersonificando al mismo coach | Permitido. Cadenas de tokens y logs independientes. Sin locking. |
| Coach hace logout durante impersonificación de superadmin | Endpoint logout detecta `impersonation_log_id` en token y trata como stop, no logout real. |

---

## 8. Plan de migración / rollout

1. Deploy de migraciones (aditivas, sin downtime).
2. Deploy de backend (controlador + comandos artisan + rate limiter).
3. Deploy de frontend (botón + banner + composable).
4. Tests E2E manuales en staging:
   - Superadmin → coach → volver
   - Superadmin → coach → cliente → volver (debe regresar al superadmin, no al coach)
   - Coach intentando impersonificar (debe fallar)
   - Token expirado mientras navega
5. Deploy a producción.
6. Monitoreo de `security.log` durante primeras 48h.
7. Después de 7 días: ejecutar `php artisan wellcore:impersonation-report --days=7` para verificar que el patrón es razonable.

---

## 9. Métricas de éxito

- Cero errores 500 en `/api/v/admin/coaches/{id}/impersonate` durante la primera semana.
- Tiempo promedio de "stop" (cuando se hace click en "Volver" hasta restauración completa) < 1.5s.
- Cero casos de "sesión rota" reportados (síntoma: superadmin tiene que cerrar sesión manualmente para volver al panel).
- 100% de los logs de impersonificación tienen `ended_at` poblado (vía endpoint stop o vía comando scheduled).

---

## 10. Riesgos identificados

| Riesgo | Probabilidad | Mitigación |
|---|---|---|
| Token de impersonificación filtrado a alguien no superadmin | Baja | TTL corto + canal seguro de transporte (HTTPS) + flag `impersonation_log_id` permite revocación masiva en incidente |
| `wc_root_token` se pierde por bug en SPA → superadmin no puede volver | Media | Backup adicional en sesión PHP del lado servidor; endpoint stop puede reconstruir desde sesión PHP si localStorage está sucio |
| Cadena de logs queda inconsistente (un log abierto, otro cerrado) | Baja | Endpoint stop usa transacción DB; comando scheduled diario corrige inconsistencias residuales |
| Bug en Patrón B existente (`CoachController::impersonate`) al detectar superadmin impersonificando | Media | Tests unitarios específicos para el caso "coach actual es target de impersonificación activa" |

---

## 11. Hallazgos del review de integridad (2026-04-27)

Tras verificar el spec contra el código real del proyecto, se identificaron y corrigieron 6 inconsistencias:

| # | Hallazgo | Acción aplicada |
|---|---|---|
| 1 | El rate limiter `impersonate` ya existe con `Limit::perMinute(10)` (no 5 como suponía el spec original) | Spec ajustado a `10/min + 50/día`, manteniendo retrocompatibilidad con `coach→cliente` |
| 2 | Canal de log `security` NO existe en `config/logging.php` | Sección 6.4 agrega definición del canal con retención 90 días |
| 3 | `AuthController::logout` hace `session()->flush()` lo cual destruiría `wc_root_token` (bug pre-existente) | Sección 3.3 documenta la modificación crítica al logout |
| 4 | Trait `Auditable` no tiene campo `impersonation_log_id` propio | Sección 4.6 nueva: usar campo `diff` (JSON) para guardarlo, helper `currentImpersonationLogId()` agregado al trait |
| 5 | `CoachController::impersonate` usa `wc_admin_token` (slot único). Cambiar SIN migrarlo rompe el flujo coach→cliente legítimo | Sección 3.3 explicita: el refactor migra AMBOS controladores al modelo unificado `wc_root_token + wc_impersonation_chain` |
| 6 | El throttle `impersonate` se computa por `request->user()->id`, no por superadmin original cuando hay cadena | Sección 6.2 documenta como riesgo aceptado, monitoreado vía comando `wellcore:impersonation-report` |
| 7 | `vue.blade.php` reinyecta `__WC_SESSION` con campos legacy (`adminToken`, `impersonating`) pero NO los nuevos slots de cadena | Sección 3.3 agrega modificación al blade |
| 8 | El grupo `/api/v/admin` usa `auth:wellcore` (no `api.bearer` como decía el spec) y `role:admin,superadmin,jefe` | Sección 6.1 alineada con realidad del routing |
| 9 | `useApi.js:82` lee el token de `authStore.token` (Pinia) | El `authStore` debe ser quien actualiza el token al cambiar de impersonificación. NO modificar `useApi.js` directamente; modificar el `authStore` para que reaccione a los slots de localStorage |

---

## Apéndice A — Referencias al código existente (verificadas)

### Controllers e impersonificación
- `ImpersonateController` actual (admin → cliente, Patrón A): `app/Http/Controllers/ImpersonateController.php`
- `CoachController::impersonate` (coach → cliente, Patrón B, modelo a replicar): `app/Http/Controllers/Api/CoachController.php:1712`
- `CoachController::endImpersonation`: `app/Http/Controllers/Api/CoachController.php:1780`
- `CoachImpersonateController` actual (a deprecar): `app/Http/Controllers/CoachImpersonateController.php`
- `AuthController::logout` (a modificar — ver sección 3.3): `app/Http/Controllers/Api/AuthController.php:94`

### Auth y middleware (verificados)
- `WellCoreGuard` (custom guard token-based): `app/Auth/WellCoreGuard.php`
- `EnsureRole` middleware (registro como `role:`): `app/Http/Middleware/EnsureRole.php`
- `ApiBearerAuth` middleware (registro como `api.bearer`): `app/Http/Middleware/ApiBearerAuth.php`

### Frontend Vue
- Banner existente (coach → cliente): `resources/js/vue/components/CoachImpersonationBanner.vue`
- Composable existente: `resources/js/vue/composables/useImpersonation.js`
- Layouts donde montar el banner nuevo: `resources/js/vue/layouts/{CoachLayout,ClientLayout,RiseLayout}.vue`
- Panel admin de coaches (donde va el botón): `resources/js/vue/pages/Admin/CoachManagement.vue`
- Patrón de form-submit para impersonificación admin→cliente: `resources/js/vue/pages/Admin/ClientDetail.vue:130`

### Modelos y migraciones
- Modelo de log existente: `app/Models/ImpersonationLog.php`
- Migración original: `database/migrations/2026_04_19_170100_create_impersonation_logs_table.php`
- Migración de `auth_tokens`: `database/migrations/2026_01_01_000001_create_legacy_admins_and_auth_tokens_tables.php`
- Migración auxiliar `auth_tokens.last_used_at`: `database/migrations/2026_04_21_210000_add_last_used_at_and_index_to_auth_tokens.php`

### Auditoría y rate limiting
- Trait `Auditable` (helper `audit()`): `app/Traits/Auditable.php:19`
- Modelo `AuditLog`: `app/Models/AuditLog.php`
- Rate limiter `impersonate` ya configurado: `app/Providers/AppServiceProvider.php:155`

### Rutas relevantes
- `routes/api.php:276` — `POST /api/v/coach/clients/{id}/impersonate` (con throttle)
- `routes/api.php:277` — `POST /api/v/coach/impersonate/end`
- `routes/web.php:296` — `POST /admin/impersonate/{clientId}` (admin → cliente, Patrón A)
- `routes/web.php:301` — `POST /admin/coach-impersonate/{adminId}` (a deprecar)
- `routes/web.php:309` — `POST /admin/impersonate/stop` (admin → cliente stop)
- `routes/web.php:312` — `POST /admin/coach-impersonate/stop` (a deprecar)
