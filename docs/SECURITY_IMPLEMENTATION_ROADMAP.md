# SECURITY IMPLEMENTATION ROADMAP — WellCore Laravel

**Versión:** 1.0
**Fecha:** 2026-04-24
**Autor:** Claude Code Opus 4.7 XHigh (análisis sobre `docs/SECURITY_AUDIT_AND_IMPLEMENTATION_PLAN.md` + revisión directa del codebase)
**Ámbito:** Hardening de seguridad con CERO regresión para vanilla PHP, Vue SPA, Livewire, Wompi, chatbot y rutas públicas.
**Modo de uso:** Cada fase es independiente. NO ejecutar sin luz verde explícita por fase. El agente debe ejecutar *UNA* fase a la vez y validar antes de continuar.

---

## 0. RESUMEN EJECUTIVO DE RIESGOS REALES

Tras validar el audit contra el codebase (Apr 2026), estos son los 5 riesgos verdaderamente críticos, priorizados por **impacto × exposición × facilidad de explotación**:

1. **R1 — `trustProxies(at: '*')`** (`bootstrap/app.php:31`).
   Cualquier cliente puede spoofear `X-Forwarded-For` y `X-Forwarded-Proto`. Consecuencia: bypass de rate-limit por IP, IP logging corrupto en `auth_tokens.ip_address`, y en escenarios específicos, URL generation cree que el request es HTTPS cuando no lo es. **Severidad: CRÍTICA**.

2. **R2 — Rutas API sin `auth:wellcore`** (`routes/api.php:67, 89, 113, 148, 155, 181, 243`).
   La autenticación vive en un trait de controller (`AuthenticatesVueRequests::resolveClientOrFail`). Si un desarrollador olvida el trait en un método, el endpoint queda ABIERTO. **Severidad: ALTA (fail-open latente)**.

3. **R3 — Falta HSTS y cabeceras de logout sin Clear-Site-Data** (`ContentSecurityPolicy.php`, `routes/web.php:249`).
   Exposición a SSL stripping y a cache residual post-logout. **Severidad: MEDIA-ALTA**.

4. **R4 — CORS incluye origen local en build de producción** (`config/cors.php:9`).
   Información de infraestructura filtrada. **Severidad: BAJA** pero trivial de arreglar.

5. **R5 — Sesiones sin encriptar y sin RLS aplicativo** (`.env: SESSION_ENCRYPT=false`; sin Global Scopes).
   Si un atacante lee el disco (storage/framework/sessions) ve tokens en claro. Si un bug en un controller olvida un filtro por `client_id`, un cliente autenticado puede leer datos de otro. **Severidad: MEDIA** (requiere pivoting o bug previo).

---

## 1. ANÁLISIS CRÍTICO DEL PLAN ORIGINAL

Resumen de lo que el audit acierta, lo que está impreciso y lo que es directamente incorrecto.

### 1.1 Lo que el audit acierta

- Diagnóstico general: `trustProxies('*')`, falta de HSTS, ausencia de `auth:wellcore` en rutas API, sesiones no encriptadas, falta de RLS aplicativo. **Correcto**.
- La cabecera `Clear-Site-Data` en logout. **Correcto**.
- La adición de `Strict-Transport-Security` sólo en producción. **Correcto**.
- La necesidad de devolver JSON 401 en rutas API al fallar auth. **Correcto en intención**.

### 1.2 Lo que el audit tiene IMPRECISO

| Punto | Audit dice | Realidad verificada |
|------|-----------|---------------------|
| Expiración de tokens | 30 días | **7 días** (`AuthController::login` línea 67 usa `now()->addDays(7)`). CLAUDE.md también está desactualizado — no tocar hasta alinear. |
| `wc_token` está en `encryptCookies(except: […])` | Sí (§2.2, §5.3) | **NO**. `bootstrap/app.php:34` no incluye `wc_token`. Por tanto Laravel intenta desencriptarlo y falla silenciosamente. La ruta cookie en `EnsureAuthenticated` y `WellCoreGuard` es efectivamente **código muerto** para auth vanilla. |
| Infra asumida | Cloudflare o AWS ALB | **EasyPanel + Docker + Traefik** (ver `docs/nginx-config-easypanel.conf`). Los CIDRs de Cloudflare del audit son **incorrectos** para este stack. |
| El handler `AuthenticationException → JSON 401` en `bootstrap/app.php` arregla rutas API sin auth | Sí (§3.1 Opción A) | **NO** funciona. `EnsureAuthenticated` **no lanza** `AuthenticationException`, retorna una respuesta directa. El exception handler no se dispara. La corrección real es modificar `EnsureAuthenticated` para devolver JSON cuando `$request->is('api/*')`. |
| `OwnedByCoachScope` vía `coach_id` directo | Aplicable a múltiples modelos | **NO aplicable** genéricamente. `CoachController::getCoachClientIds` UNIONA 5 fuentes (`clients.coach_id` opcional + `assigned_plans.assigned_by` + `coach_messages.coach_id` + `coach_notes.coach_id` + `plan_tickets.coach_id`). Aplicar `OwnedByCoachScope` a Checkin, Metric, WorkoutSession etc. **romperá** dashboard de coach y admin. |

### 1.3 Lo que el audit NO menciona y debería

- **EasyPanel / Docker internal networking**: al endurecer `trustProxies`, el health-check `/up` y `/health` deben seguir respondiendo desde la IP de Traefik, que está en red privada Docker (típicamente `172.16.0.0/12`).
- **`EnsureRole::handle()`** retorna `redirect('/login')` si no hay usuario (línea 15). Para rutas API necesita devolver JSON.
- **`EnsurePlan`** pasa adelante si no hay usuario o si el usuario no es Client. No es un control de auth y no debe ejecutarse sin `auth:wellcore` delante (confirmado en audit, pero sin explicarlo).
- **Impersonation de coach (superadmin → coach)**: vive en `CoachImpersonateController`. Rutas en `routes/web.php:264-267`. Usa sesión PHP + `role:superadmin` middleware. **No hay que tocarlo**.
- **La cookie `wc_token` set por la app vanilla** no es leída por Laravel (EncryptCookies descarta por DecryptException). Hardening de cookie es LOW-VALUE hasta que se unifique cookie handling con vanilla — punto de negocio, no de seguridad prioritaria.
- **Logout Livewire**: existe `Livewire\Auth\Login` y blade views — Livewire aún maneja login en paralelo. Cambiar `/logout` en web.php afecta también a estos flujos.

### 1.4 Recomendación de fases del audit que NO deben ejecutarse ahora

- **§5.2 `SESSION_SAME_SITE=strict`** → Riesgo alto de romper Google OAuth (`GoogleAuthController`). **BLOCKED — requiere verificación manual** del flujo OAuth antes de intentar.
- **§5.3 HardenLegacyCookie** → Como Laravel no puede leer la cookie (EncryptCookies la descarta), el middleware NUNCA re-emite. Es humo. **BLOCKED — no ejecutar sin migrar primero la app vanilla**.
- **§6.2 DB user restringido** → Operación DBA, no de código. **BLOCKED** hasta coordinación con sysadmin / EasyPanel.
- **§6.3 General query log** → Impacto de performance en producción alto. **NO ejecutar en producción live**. Correr sólo en staging.

---

## 2. CHECKLIST DE NO-TOCAR (REGLAS ABSOLUTAS)

Antes de CUALQUIER cambio, confirmar que el cambio NO:

- [ ] Modifica tablas existentes de `wellcore_fitness` (ningún `ALTER`, ningún `DROP`).
- [ ] Toca archivos en `C:\Users\GODSF\Herd\wellcorefitness` (vanilla PHP).
- [ ] Cambia CSS, blade layouts de marketing, componentes Vue ni lógica Livewire.
- [ ] Rompe endpoints públicos: `/api/ejercicios/*`, `/api/chat`, `/api/newsletter`, `/webhooks/wompi`, `/api/v/public/*`, `/api/v/shop/*`, `/health`, `/sitemap.xml`, `/robots.txt`, `/uploads/photos/{filename}`, `/media/gif/{slug}`.
- [ ] Rompe impersonation admin→client (`ImpersonateController`) ni coach→client (`CoachController::impersonate`) ni superadmin→coach (`CoachImpersonateController`).
- [ ] Modifica lógica de pagos (`WompiService`, `WebhookController::wompi`, rutas `/checkout/*`).
- [ ] Agrega `debug` en respuestas JSON en producción (mantener guard `if (! app()->environment('production'))`).
- [ ] Cambia `APP_KEY` (invalidaría sesiones y tokens cifrados).
- [ ] Cambia el algoritmo de hash de password sin migración planificada.
- [ ] Desactiva throttling en rutas públicas.

---

## 3. ORDEN RECOMENDADO DE EJECUCIÓN (por riesgo/regresión)

| # | Fase | Riesgo que cierra | Riesgo de regresión | Tiempo |
|---|------|-------------------|---------------------|--------|
| A | **HSTS + Clear-Site-Data + X-XSS-Protection** | Medio-Alto | Mínimo | 10 min |
| B | **CORS tighten (production-only origins)** | Bajo | Mínimo | 5 min |
| C | **`EnsureAuthenticated` devuelve JSON para `api/*`** (prerequisito de D) | Medio | Mínimo | 10 min |
| D | **`auth:wellcore` en grupos API `v/client`, `v/rise`, `v/coach`, `v/admin`** | Alto | Bajo | 15 min |
| E | **`role:` middleware en `v/coach` y `v/admin`** (defense-in-depth) | Medio | Bajo | 10 min |
| F | **`trustProxies` restringido a red privada Docker** | Crítico | Medio (validar /health y IP logging) | 15 min |
| G | **`SESSION_ENCRYPT=true`** (.env prod) | Medio | Bajo (invalida sesiones activas una vez) | 5 min |
| H | **Global Scope `OwnedByClientScope` en 5 modelos core** | Medio | Medio (validar queries admin) | 30 min |
| I | **Policy `ClientPolicy` + `PlanTicketPolicy` + registro en `AppServiceProvider`** | Medio | Bajo | 20 min |
| J | **NGINX security headers (nginx-config-easypanel.conf)** | Bajo | Bajo | 10 min |
| K | **Script `scripts/security-headers-check.php`** | — (verificación) | — | 10 min |

**Orden recomendado de despliegue:** A → B → C → D → E → G → F → H → I → J → K.

> F (`trustProxies`) va DESPUÉS de D/E porque el cambio requiere validar que los health-checks de EasyPanel siguen funcionando; si hay rollback, los middlewares auth ya están activos y protegen.

---

## 4. FASES DETALLADAS

Cada fase incluye: **objetivo**, **archivo**, **BEFORE/AFTER**, **validación**, **rollback**, **impacto**.

---

### FASE A — HSTS + Clear-Site-Data + X-XSS-Protection

**Objetivo:** Cerrar SSL stripping y limpiar browser state en logout.

#### A.1 Agregar HSTS y X-XSS-Protection

**Archivo:** `app/Http/Middleware/ContentSecurityPolicy.php`

**BEFORE (líneas 41-45):**
```php
$response->header('Content-Security-Policy', $csp);
$response->header('X-Content-Type-Options', 'nosniff');
$response->header('X-Frame-Options', 'SAMEORIGIN');
$response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
```

**AFTER:**
```php
$response->header('Content-Security-Policy', $csp);
$response->header('X-Content-Type-Options', 'nosniff');
$response->header('X-Frame-Options', 'SAMEORIGIN');
$response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
$response->header('X-XSS-Protection', '1; mode=block');

if (app()->environment('production')) {
    $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
}
```

**Validación (cliente HTTP externo):**
```bash
curl -sI https://wellcorefitness.com/ | grep -iE "strict-transport|x-xss|x-frame"
# Esperado: strict-transport-security: max-age=31536000; includeSubDomains; preload
#           x-xss-protection: 1; mode=block
#           x-frame-options: SAMEORIGIN
```

**Rollback:** revertir las 4 líneas agregadas.

**Impacto:** DB=ninguno · UX=ninguno · Vanilla=ninguno (middleware solo activo en rutas Laravel) · Vue=ninguno · Livewire=ninguno.

---

#### A.2 Clear-Site-Data en logout web

**Archivo:** `routes/web.php`

**BEFORE (líneas 249-257):**
```php
Route::post('/logout', function () {
    $token = session('wc_token');
    if ($token) {
        AuthToken::where('token', $token)->delete();
    }
    session()->flush();

    return redirect('/login');
})->name('logout');
```

**AFTER:**
```php
Route::post('/logout', function () {
    $token = session('wc_token');
    if ($token) {
        AuthToken::where('token', $token)->delete();
    }
    session()->flush();

    return redirect('/login')
        ->header('Clear-Site-Data', '"cache", "cookies", "storage"')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
        ->header('Pragma', 'no-cache');
})->name('logout');
```

> **Nota:** NO tocar `/api/v/auth/logout` (AuthController::logout). Esa ruta ya devuelve JSON; Clear-Site-Data se activa en la navegación del navegador principal, no en el XHR del logout Vue. El flujo actual del SPA ya navega a `/login` después del POST XHR — los headers del redirect web son los que aplican.

**Validación:**
```bash
curl -sI -X POST https://wellcorefitness.com/logout \
  -H "Cookie: laravel_session=<session-id>" \
  -H "X-CSRF-TOKEN: <csrf>"
# Esperado (tras redirect 302): clear-site-data: "cache", "cookies", "storage"
```

**Rollback:** eliminar las 3 llamadas `->header(...)`.

**Impacto:** DB=ninguno · UX=ninguno (usuario sigue llegando a /login) · Vanilla=ninguno · Vue=ninguno · Livewire=ninguno (Livewire Auth/Login sigue funcionando).

---

### FASE B — CORS conditional en producción

**Objetivo:** Que el origen local NO aparezca en la config de producción.

**Archivo:** `config/cors.php`

**BEFORE (líneas 8-12):**
```php
'allowed_origins' => [
    'http://wellcore-laravel.test',
    'https://wellcorefitness.com',
    'https://www.wellcorefitness.com',
],
```

**AFTER:**
```php
'allowed_origins' => array_values(array_filter([
    app()->environment('local') ? 'http://wellcore-laravel.test' : null,
    'https://wellcorefitness.com',
    'https://www.wellcorefitness.com',
])),
```

**Validación:**
```bash
# En producción
curl -sI -X OPTIONS https://wellcorefitness.com/api/v/client/dashboard \
  -H "Origin: http://wellcore-laravel.test" \
  -H "Access-Control-Request-Method: GET"
# Esperado: SIN Access-Control-Allow-Origin en la respuesta (rechazado)

curl -sI -X OPTIONS https://wellcorefitness.com/api/v/client/dashboard \
  -H "Origin: https://wellcorefitness.com" \
  -H "Access-Control-Request-Method: GET"
# Esperado: Access-Control-Allow-Origin: https://wellcorefitness.com
```

**Rollback:** revertir el array a los 3 orígenes originales.

**Impacto:** DB=ninguno · UX=ninguno · Vanilla=ninguno (vanilla no usa esta config) · Vue=ninguno en prod · Livewire=ninguno.

> ⚠️ **Limpiar config cache** antes de validar: `php artisan config:clear && php artisan config:cache`.

---

### FASE C — `EnsureAuthenticated` devuelve JSON para rutas API

**Objetivo:** Preparar el terreno para D. Sin esta fix, un request a `/api/v/*` sin `Accept: application/json` recibiría `redirect('/login')` como 302 + HTML, lo que rompe SPA y mobile clients.

**Archivo:** `app/Http/Middleware/EnsureAuthenticated.php`

**BEFORE (líneas 15-22):**
```php
if (! $token) {
    if ($request->expectsJson()) {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    return redirect('/login');
}
```

**AFTER:**
```php
if (! $token) {
    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    return redirect('/login');
}
```

**BEFORE (líneas 37-42):**
```php
if ($request->expectsJson()) {
    return response()->json(['error' => 'Token expired or invalid.'], 401);
}

return redirect('/login');
```

**AFTER:**
```php
if ($request->expectsJson() || $request->is('api/*')) {
    return response()->json(['message' => 'Token expired or invalid.'], 401);
}

return redirect('/login');
```

> **Nota de uniformidad:** se cambia `'error'` por `'message'` para alinear con la convención del resto de la API (`{message: ...}` en `ApiBearerAuth`, `AuthController`, trait de Vue).

**Validación:**
```bash
# Sin Accept JSON, debe seguir devolviendo 401 JSON porque is('api/*') es true
curl -sI https://wellcorefitness.com/api/v/client/dashboard
# Esperado: HTTP/1.1 401  (JSON body: {"message":"Unauthenticated."})

# Ruta web sin token → sigue redirigiendo
curl -sI https://wellcorefitness.com/client
# Esperado: 302 Location: /login
```

**Rollback:** revertir los dos bloques.

**Impacto:** DB=ninguno · UX=ninguno (rutas web siguen redirigiendo) · Vanilla=ninguno (vanilla no pasa por este middleware) · Vue=puede mejorar (mensajes unificados) · Livewire=ninguno.

---

### FASE D — `auth:wellcore` en grupos API

**Objetivo:** Defense-in-depth a nivel de ruta. Si un desarrollador olvida `resolveClientOrFail`, el middleware ya bloqueó el request.

**Archivo:** `routes/api.php`

> **Nota técnica:** el alias `auth` mapea a `EnsureAuthenticated` que no usa guards estándar. El parámetro `:wellcore` es cosmético — se acepta porque todo el codebase ya usa esa nomenclatura (`routes/web.php:248`). Mantener consistencia.

**Cambios (7 grupos, línea por línea):**

**Línea 67** (Client básico):
```diff
- Route::prefix('v/client')->middleware('throttle:api')->group(function () {
+ Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**Línea 89** (Training):
```diff
- Route::prefix('v/client')->middleware('throttle:api')->group(function () {
+ Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**Línea 113** (Social & Resources):
```diff
- Route::prefix('v/client')->middleware('throttle:api')->group(function () {
+ Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**Línea 148** (Medals):
```diff
- Route::prefix('v/client')->middleware('throttle:api')->group(function () {
+ Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**Línea 155** (RISE):
```diff
- Route::prefix('v/rise')->middleware(['throttle:api', 'ensure.plan:metodo,elite,rise,presencial'])->group(function () {
+ Route::prefix('v/rise')->middleware(['auth:wellcore', 'throttle:api', 'ensure.plan:metodo,elite,rise,presencial'])->group(function () {
```

**Línea 181** (Coach — sólo auth, `role:` en Fase E):
```diff
- Route::prefix('v/coach')->middleware('throttle:api')->group(function () {
+ Route::prefix('v/coach')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**Línea 243** (Admin — sólo auth, `role:` en Fase E):
```diff
- Route::prefix('v/admin')->middleware('throttle:api')->group(function () {
+ Route::prefix('v/admin')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**Validación:**
```bash
# Sin token: todos deben devolver 401 JSON
for path in "v/client/dashboard" "v/client/training" "v/rise/dashboard" "v/coach/dashboard" "v/admin/dashboard" "v/client/medals"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "https://wellcorefitness.com/api/$path")
  echo "$path -> $code (expected 401)"
done

# Con token válido de cliente: /v/client/dashboard debe devolver 200
TOKEN="<token-valido-de-cliente>"
curl -sI -H "Authorization: Bearer $TOKEN" https://wellcorefitness.com/api/v/client/dashboard
# Esperado: HTTP/1.1 200

# Con token válido de cliente: /v/coach/dashboard debe devolver 401/403
curl -sI -H "Authorization: Bearer $TOKEN" https://wellcorefitness.com/api/v/coach/dashboard
# Esperado: 401 (auth pasa pero el trait resolveCoachOrFail aborta) hasta que Fase E agrega role:
```

**Rollback:** quitar `'auth:wellcore'` de cada grupo.

**Impacto:** DB=ninguno · UX=ninguno (usuarios autenticados no notan nada) · Vanilla=ninguno (rutas API son sólo Laravel) · Vue=mejor consistencia de 401 · Livewire=ninguno · **Impersonation**: protegido — `auth:wellcore` lee session('wc_token') que es el token del cliente impersonado, funciona ✓.

> ⚠️ **Caveat crítico**: confirmar antes de deploy que `config/auth.php` sigue teniendo el guard `wellcore`. Si no existiera, Laravel generaría "Auth guard [wellcore] is not defined" — aunque el alias `auth` mapea a `EnsureAuthenticated` que NO usa Illuminate\Auth\Middleware\Authenticate, el parámetro `wellcore` se pasa como string vacío y se descarta. Validar con un smoke test en staging primero.

---

### FASE E — `role:` middleware en `v/coach` y `v/admin`

**Objetivo:** Bloquear tokens de cliente intentando acceder a endpoints de coach/admin a nivel de ruta.

**Archivo:** `routes/api.php`

**Línea 181:**
```diff
- Route::prefix('v/coach')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
+ Route::prefix('v/coach')->middleware(['auth:wellcore', 'throttle:api', 'role:coach,admin,superadmin,jefe'])->group(function () {
```

**Línea 243:**
```diff
- Route::prefix('v/admin')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
+ Route::prefix('v/admin')->middleware(['auth:wellcore', 'throttle:api', 'role:admin,superadmin,jefe'])->group(function () {
```

> **Pre-requisito:** confirmar que `EnsureRole::handle` devuelve JSON 403 si `$request->expectsJson() || $request->is('api/*')`. Revisar `app/Http/Middleware/EnsureRole.php`. Si sólo usa `abort(403)`, Laravel emite JSON únicamente cuando el cliente envía `Accept: application/json`. **Recomiendo endurecer EnsureRole antes:**

**Archivo:** `app/Http/Middleware/EnsureRole.php`

**BEFORE (líneas 13-17):**
```php
$user = auth('wellcore')->user();
if (!$user) {
    return redirect('/login');
}
```

**AFTER:**
```php
$user = auth('wellcore')->user();
if (!$user) {
    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
    return redirect('/login');
}
```

**BEFORE (línea 26):**
```php
if (!in_array($userRole, $roles)) {
    abort(403);
}
```

**AFTER:**
```php
if (!in_array($userRole, $roles)) {
    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json(['message' => 'No tienes permisos para acceder a este recurso.'], 403);
    }
    abort(403);
}
```

**Validación:**
```bash
# Token cliente -> /api/v/coach/dashboard debe devolver 403 JSON (no 200)
CLIENT_TOKEN="<token-de-client>"
curl -s -o /dev/null -w "%{http_code}" -H "Authorization: Bearer $CLIENT_TOKEN" https://wellcorefitness.com/api/v/coach/dashboard
# Esperado: 403

# Token coach -> /api/v/coach/dashboard debe devolver 200
COACH_TOKEN="<token-de-coach>"
curl -sI -H "Authorization: Bearer $COACH_TOKEN" https://wellcorefitness.com/api/v/coach/dashboard
# Esperado: 200

# Token admin -> /api/v/admin/dashboard debe devolver 200
ADMIN_TOKEN="<token-de-admin>"
curl -sI -H "Authorization: Bearer $ADMIN_TOKEN" https://wellcorefitness.com/api/v/admin/dashboard
# Esperado: 200
```

**Rollback:** quitar `'role:...'` de los grupos y revertir `EnsureRole::handle`.

**Impacto:** DB=ninguno · UX=ninguno (coaches/admins legítimos no notan nada) · Vanilla=ninguno · Vue=puede mejorar consistencia de errores · Livewire=ninguno · **Impersonation coach→client**: cuando un coach está impersonando a un cliente, `session('wc_token')` es el token del cliente y `auth('wellcore')->user()` devuelve Client. Si el SPA del cliente nunca llama a `/api/v/coach/*`, no hay impacto. ✓

---

### FASE F — `trustProxies` restringido

**Objetivo:** Dejar de confiar en headers `X-Forwarded-*` de cualquier origen. Sólo confiar en la red interna Docker/Traefik.

**Archivo:** `bootstrap/app.php`

**BEFORE (línea 31):**
```php
$middleware->trustProxies(at: '*');
```

**AFTER (EasyPanel + Docker + Traefik):**
```php
// EasyPanel/Docker/Traefik: confiar sólo en redes privadas RFC 1918 + loopback.
// NO incluir '*' — permitiría spoofing de X-Forwarded-For desde cualquier IP.
$middleware->trustProxies(
    at: [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.1',
    ],
    headers: Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO,
);
```

> Requiere `use Illuminate\Http\Request;` en imports — ya está importado en `bootstrap/app.php:17` ✓.

**Validación:**
```bash
# 1. Health check debe seguir OK
curl -sI https://wellcorefitness.com/health
# Esperado: 200 + JSON {"status":"healthy"}

# 2. Crear temporalmente un endpoint debug en routes/web.php:
# Route::get('/debug/ip', function (Request $request) {
#     return [
#         'ip' => $request->ip(),
#         'xff' => $request->header('X-Forwarded-For'),
#         'scheme' => $request->getScheme(),
#         'trusted' => method_exists($request, 'isFromTrustedProxy')
#             ? $request->isFromTrustedProxy() : null,
#     ];
# })->middleware('throttle:10,1');

# Luego acceder desde el exterior
curl -s "https://wellcorefitness.com/debug/ip"
# Esperado: ip = tu IP pública real; scheme = "https"; trusted = true

# 3. Confirmar que nuevos logins guardan la IP real en auth_tokens.ip_address
```

**Rollback:** revertir a `$middleware->trustProxies(at: '*');`.

**Impacto:**
- DB=ninguno.
- UX=ninguno si Traefik está en la red privada (caso normal).
- Vanilla=ninguno (vanilla no pasa por este middleware).
- Vue=ninguno.
- Livewire=ninguno.
- **Riesgo**: si el deploy de EasyPanel cambia la red Docker o agrega un LB externo con IPs públicas, los request perderán `X-Forwarded-Proto` y Laravel puede generar URLs HTTP en vez de HTTPS. **Mitigación:** `URL::forceScheme('https')` ya está en `AppServiceProvider::boot()` línea 29 — protege contra esto ✓.

> ⚠️ **Blocker conocido**: si después del deploy `/health` responde 503 o las IPs quedan como `127.0.0.1`, es que Traefik no está en el rango privado esperado. En ese caso, agregar temporalmente el CIDR real de Traefik al array o volver a `'*'` y investigar la infra.

---

### FASE G — `SESSION_ENCRYPT=true`

**Objetivo:** Cifrar el contenido de `storage/framework/sessions/*` con `APP_KEY`.

**Archivo:** `.env` (sólo producción — NO commit).

**BEFORE:**
```env
SESSION_ENCRYPT=false
```

**AFTER:**
```env
SESSION_ENCRYPT=true
```

**Validación:**
```bash
# Tras deploy + limpiar cache de config (php artisan config:cache)
# inspeccionar un archivo de sesión nuevo
ls -t storage/framework/sessions/ | head -1 | xargs -I {} cat storage/framework/sessions/{}
# Esperado: contenido cifrado (base64 + salt), NO plaintext JSON con "wc_token":"..."
```

**Rollback:** `SESSION_ENCRYPT=false` + `php artisan config:cache`.

**Impacto:**
- DB=ninguno.
- UX=**sesiones activas se invalidan una vez** — los usuarios deberán volver a logearse después del deploy. Advertir al equipo y hacerlo en ventana de bajo tráfico (p.ej. 3 AM hora LATAM).
- Vanilla=ninguno (vanilla maneja sesiones PHP nativas por separado).
- Vue=ninguno después del re-login.
- Livewire=ninguno después del re-login.

> **Nota**: si se migra a `SESSION_DRIVER=database` o `redis` en el futuro (ver memoria `feedback_deploy_approach`), la encripción aplica igual. El `.env.example` ya menciona este paso.

---

### FASE H — Global Scope `OwnedByClientScope` en modelos core

**Objetivo:** Defense-in-depth. Si un controller olvida filtrar por `client_id`, el scope lo hace automáticamente para sesiones de Client.

#### H.1 Crear el scope

**Archivo NUEVO:** `app/Scopes/OwnedByClientScope.php`
```php
<?php

namespace App\Scopes;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * RLS aplicativo: limita las queries al `client_id` del usuario autenticado
 * SÓLO cuando el usuario autenticado es un Client. Admins y coaches no
 * son afectados (el scope se auto-desactiva) porque sus controllers
 * ya filtran por listas de client_id via getCoachClientIds().
 *
 * Rutas CLI/queue/webhook (sin auth) tampoco son afectadas: el scope se
 * vuelve no-op cuando auth()->user() es null.
 */
class OwnedByClientScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('wellcore')->user();

        if ($user instanceof Client) {
            $builder->where($model->qualifyColumn('client_id'), $user->id);
        }
    }
}
```

#### H.2 Aplicar a 5 modelos de alto riesgo

Modelos seleccionados por: (a) contienen datos sensibles y (b) columna `client_id` directa verificada.

| Modelo | Tabla | Columna | Motivo |
|--------|-------|---------|--------|
| `Checkin` | `checkins` | `client_id` | check-ins semanales con datos de bienestar |
| `Metric` | `metrics` | `client_id` | peso, composición corporal |
| `ProgressPhoto` | `progress_photos` | `client_id` | fotos privadas |
| `WorkoutSession` | `workout_sessions` | `client_id` | historial entrenamiento |
| `BiometricLog` | `biometric_logs` | `client_id` | datos biométricos sensibles |

**Aplicar en cada modelo** (ejemplo con Checkin):

**Archivo:** `app/Models/Checkin.php`

**BEFORE (después de línea 28 `public $timestamps = false;`):**
```php
public $timestamps = false;

protected function casts(): array
```

**AFTER:**
```php
public $timestamps = false;

protected static function booted(): void
{
    static::addGlobalScope(new \App\Scopes\OwnedByClientScope());
}

protected function casts(): array
```

Repetir el bloque `booted()` en los otros 4 modelos.

#### H.3 Escape hatch para queries admin/coach que usen modelos con scope (validar cada uso)

**No se requiere** cambio adicional en `CoachController` ni `AdminController` porque ambos operan con `auth('wellcore')->user() instanceof Admin` — el scope es no-op para ellos.

Sin embargo, si algún Job / Observer / Command ejecuta en CLI con un Client presente vía `Auth::login($client)`, ese contexto heredaría el scope. **Verificar antes del deploy:**

```bash
grep -rn "Auth::login\|auth()->login" app/Console app/Jobs app/Observers
```

Si hay resultados, evaluar caso por caso si conviene `Model::withoutGlobalScope(\App\Scopes\OwnedByClientScope::class)`.

**Validación:**
```bash
# 1. Smoke test: cliente A loguea, lista sus check-ins
TOKEN_A="<token-client-a>"
curl -s -H "Authorization: Bearer $TOKEN_A" https://wellcorefitness.com/api/v/client/dashboard | jq '.weekly_summary'
# Esperado: datos del cliente A

# 2. Coach loguea, ve todos sus clientes (scope NO debe activarse)
TOKEN_COACH="<token-coach>"
curl -s -H "Authorization: Bearer $TOKEN_COACH" https://wellcorefitness.com/api/v/coach/dashboard | jq '.pendingCheckins'
# Esperado: número > 0 si hay check-ins pendientes, no 0

# 3. Ejecutar el test suite
php artisan test --filter=Checkin
```

**Rollback:** eliminar `protected static function booted()` de los 5 modelos.

**Impacto:**
- DB=ninguno (queries Eloquent agregan WHERE client_id, no alteran schema).
- UX=ninguno si los controllers ya filtran por client_id (doble filtro es idempotente).
- Vanilla=ninguno (vanilla no usa Eloquent).
- Vue=ninguno.
- Livewire=**validar** los 11 componentes en `app/Livewire/Client/*` — si usan `Checkin::where('client_id', ...)` funcionarán igual; si usan `Checkin::all()` empezarán a filtrar automáticamente (lo cual es deseable).
- **Riesgo conocido**: si un controller admin hace `Checkin::find($id)` con un ID que pertenece a otro cliente mientras la sesión es de cliente (impersonation en reversa — coach logueado como cliente volviendo a acción admin), el scope puede ocultar registros. Mitigación: los flujos de admin usan sesión Admin, no Client, así que este caso no ocurre.

---

### FASE I — Policies para `Client` y `PlanTicket`

**Objetivo:** Autorización explícita en flujos de acceso por ID (IDOR prevention).

#### I.1 Crear ClientPolicy

**Archivo NUEVO:** `app/Policies/ClientPolicy.php`
```php
<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Client;

class ClientPolicy
{
    public function view(Client|Admin $user, Client $client): bool
    {
        if ($user instanceof Client) {
            return $user->id === $client->id;
        }

        // Coach: sólo sus clientes asignados
        if ($user instanceof Admin && $user->role?->value === 'coach') {
            // Reusa la misma lógica multi-fuente que CoachController::getCoachClientIds.
            // Para policy simple, consultamos sólo el pivot más directo.
            if (\Illuminate\Support\Facades\Schema::hasColumn('clients', 'coach_id')
                && $client->coach_id === $user->id) {
                return true;
            }
            return \App\Models\AssignedPlan::where('assigned_by', $user->id)
                ->where('client_id', $client->id)
                ->exists();
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }

    public function update(Client|Admin $user, Client $client): bool
    {
        if ($user instanceof Client) {
            return $user->id === $client->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }
}
```

#### I.2 Crear PlanTicketPolicy

**Archivo NUEVO:** `app/Policies/PlanTicketPolicy.php`
```php
<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Client;
use App\Models\PlanTicket;

class PlanTicketPolicy
{
    public function view(Client|Admin $user, PlanTicket $ticket): bool
    {
        if ($user instanceof Client) {
            return $ticket->client_id === $user->id;
        }

        if ($user instanceof Admin && $user->role?->value === 'coach') {
            return $ticket->coach_id === $user->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }

    public function update(Client|Admin $user, PlanTicket $ticket): bool
    {
        if ($user instanceof Client) {
            return false;
        }

        if ($user instanceof Admin && $user->role?->value === 'coach') {
            return $ticket->coach_id === $user->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }
}
```

#### I.3 Registrar en `AppServiceProvider::boot`

**Archivo:** `app/Providers/AppServiceProvider.php`

**BEFORE (líneas 26-36):**
```php
public function boot(): void
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }

    $this->configureRateLimiting();
    $this->configureCsp();

    WorkoutSession::observe(WorkoutSessionObserver::class);
}
```

**AFTER:**
```php
public function boot(): void
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }

    $this->configureRateLimiting();
    $this->configureCsp();
    $this->registerPolicies();

    WorkoutSession::observe(WorkoutSessionObserver::class);
}

protected function registerPolicies(): void
{
    \Illuminate\Support\Facades\Gate::policy(
        \App\Models\Client::class,
        \App\Policies\ClientPolicy::class
    );
    \Illuminate\Support\Facades\Gate::policy(
        \App\Models\PlanTicket::class,
        \App\Policies\PlanTicketPolicy::class
    );
}
```

**Validación:**
```bash
# Test manual de IDOR: cliente A intenta ver ticket de cliente B
TOKEN_A="<token-client-a>"
curl -s -o /dev/null -w "%{http_code}" -H "Authorization: Bearer $TOKEN_A" \
  https://wellcorefitness.com/api/v/client/tickets
# Esperado: 200 con sólo tickets del cliente A

# Test suite
php artisan test --filter=Policy
```

> **Nota:** las policies por sí solas no bloquean nada hasta que se llamen desde controllers (`$this->authorize('view', $ticket)`). En esta fase se registran para estar disponibles — las llamadas `authorize()` se añaden en una segunda pasada cuando se tengan identificados los métodos con parámetros ID (fuera del alcance de este roadmap para evitar tocar todos los controllers).

**Rollback:** eliminar policy files + revertir `AppServiceProvider::boot`.

**Impacto:** DB=ninguno · UX=ninguno (policies inactivas hasta llamar authorize) · Vanilla=ninguno · Vue=ninguno · Livewire=ninguno.

---

### FASE J — NGINX security headers

**Objetivo:** Que los archivos estáticos (imágenes, CSS, fuentes) también emitan headers de seguridad. Esto cubre archivos que NO pasan por PHP/Laravel.

**Archivo:** `docs/nginx-config-easypanel.conf`

**BEFORE (líneas 14-16):**
```nginx
server_name _;

client_max_body_size 20M;

# ── Compresión ──────────────────────────────────────────────
```

**AFTER:**
```nginx
server_name _;

client_max_body_size 20M;

# ── Security Headers (backup — primary vienen de Laravel CSP middleware) ──
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
server_tokens off;

# ── Compresión ──────────────────────────────────────────────
```

**Validación:**
```bash
# Después de aplicar vía EasyPanel (nunca rebuild, sólo reload nginx)
curl -sI https://wellcorefitness.com/fonts/inter.woff2 | grep -iE "x-frame|hsts|strict-transport"
# Esperado: strict-transport-security: max-age=31536000; includeSubDomains; preload
#           x-frame-options: SAMEORIGIN

# server_tokens off
curl -sI https://wellcorefitness.com/ | grep -i "^server:"
# Esperado: server: nginx (sin versión)
```

**Rollback:** quitar las líneas agregadas en EasyPanel.

**Impacto:** DB=ninguno · UX=ninguno · Vanilla=ninguno (NGINX vanilla es otro container) · Vue=ninguno · Livewire=ninguno.

> **Nota de deploy:** según memoria (`feedback_deploy_approach`, `feedback_easypanel_buttons`) el deploy de NGINX se hace vía consola EasyPanel, NO rebuild Docker. Usar UID exacto del botón "Reload nginx".

---

### FASE K — Script de verificación `scripts/security-headers-check.php`

**Objetivo:** Tener un verificador automatizable post-deploy.

**Archivo NUEVO:** `scripts/security-headers-check.php` (usa `file_get_contents` con stream context, no requiere curl).
```php
<?php

/**
 * Security headers verifier.
 * Usage: php scripts/security-headers-check.php https://wellcorefitness.com
 * Exits 0 if all required headers present, 1 otherwise.
 */

$requiredHeaders = [
    'content-security-policy'   => null,
    'strict-transport-security' => 'max-age=31536000',
    'x-content-type-options'    => 'nosniff',
    'x-frame-options'           => 'sameorigin',
    'referrer-policy'           => 'strict-origin-when-cross-origin',
    'permissions-policy'        => 'camera=()',
];

$domain = $argv[1] ?? 'https://wellcorefitness.com';

$context = stream_context_create([
    'http'  => ['method' => 'HEAD', 'timeout' => 10, 'ignore_errors' => true],
    'https' => ['method' => 'HEAD', 'timeout' => 10, 'ignore_errors' => true],
]);

$fp = @fopen($domain, 'r', false, $context);
if ($fp === false) {
    echo "Error fetching {$domain}" . PHP_EOL;
    exit(2);
}
$meta = stream_get_meta_data($fp);
fclose($fp);

$headerLines = strtolower(implode("\n", $meta['wrapper_data'] ?? []));

$pass = true;
foreach ($requiredHeaders as $header => $expectedSubstring) {
    if (!str_contains($headerLines, $header . ':')) {
        echo "MISSING: {$header}" . PHP_EOL;
        $pass = false;
        continue;
    }
    if ($expectedSubstring !== null && !str_contains($headerLines, strtolower($expectedSubstring))) {
        echo "WEAK: {$header} no contiene '{$expectedSubstring}'" . PHP_EOL;
        $pass = false;
        continue;
    }
    echo "OK: {$header}" . PHP_EOL;
}

exit($pass ? 0 : 1);
```

**Validación:**
```bash
php scripts/security-headers-check.php https://wellcorefitness.com
# Esperado (tras Fase A+J aplicadas): OK en todas las líneas, exit 0
```

**Rollback:** `rm scripts/security-headers-check.php`.

**Impacto:** ninguno (script auxiliar).

---

## 5. POST-IMPLEMENTATION CHECKLIST COMPLETO

Tras aplicar todas las fases, ejecutar en orden:

- [ ] `php artisan test` pasa.
- [ ] `npm run build` local exitoso (si cambios incluyen assets).
- [ ] **Login cliente web** — credenciales de prueba funcionan.
- [ ] Login cliente SPA (POST `/api/v/auth/login`) → `/client` dashboard carga.
- [ ] Login coach SPA → `/coach` dashboard carga.
- [ ] Login admin SPA → `/admin` dashboard carga.
- [ ] Google OAuth login (`GoogleAuthController`) funciona end-to-end.
- [ ] **Impersonation admin → cliente** (POST `/admin/impersonate/{clientId}`) funciona.
- [ ] **Impersonation coach → cliente** (POST `/api/v/coach/clients/{id}/impersonate`) funciona.
- [ ] **Impersonation superadmin → coach** (POST `/admin/coach-impersonate/{adminId}`) funciona.
- [ ] End-impersonation en todos los tres casos restaura la sesión original.
- [ ] Logout web (POST `/logout`) limpia sesión + emite `Clear-Site-Data`.
- [ ] Logout SPA (POST `/api/v/auth/logout`) devuelve 200 JSON.
- [ ] Upload de foto de progreso funciona (cliente).
- [ ] Check-in semanal (POST `/api/v/client/checkin`) funciona.
- [ ] Wompi webhook (`POST /webhooks/wompi`) sigue respondiendo con firma válida.
- [ ] Chatbot API (`POST /api/chat`) sigue funcionando sin auth.
- [ ] Newsletter (`POST /api/newsletter` vía web routes) funciona.
- [ ] Rutas públicas Vue (`/api/v/public/*`, `/api/v/shop/*`) no requieren auth.
- [ ] `curl -I /api/v/client/dashboard` sin token → 401 JSON.
- [ ] `curl -I /api/v/coach/dashboard` con token cliente → 403 JSON.
- [ ] `curl -I /api/v/admin/dashboard` con token coach → 403 JSON.
- [ ] Header `Strict-Transport-Security` presente en producción.
- [ ] Header `X-Frame-Options: SAMEORIGIN` en todas las respuestas.
- [ ] `scripts/security-headers-check.php https://wellcorefitness.com` devuelve exit 0.
- [ ] `storage/logs/laravel.log` sin errores 500 nuevos.
- [ ] PageSpeed/Lighthouse score móvil no regresa (>= baseline pre-cambios).
- [ ] Dark mode + Livewire (`Livewire\Client\Dashboard`) funcionan.

---

## 6. ROLLBACK MATRIX

| Fase | Comando rollback | Tiempo |
|------|------------------|--------|
| A | `git checkout -- app/Http/Middleware/ContentSecurityPolicy.php routes/web.php` | 1 min |
| B | `git checkout -- config/cors.php && php artisan config:cache` | 2 min |
| C | `git checkout -- app/Http/Middleware/EnsureAuthenticated.php` | 1 min |
| D | `git checkout -- routes/api.php` | 1 min |
| E | `git checkout -- routes/api.php app/Http/Middleware/EnsureRole.php` | 2 min |
| F | `git checkout -- bootstrap/app.php` | 1 min |
| G | editar `.env` prod: `SESSION_ENCRYPT=false` + `php artisan config:cache` | 1 min |
| H | `git checkout -- app/Scopes app/Models/Checkin.php app/Models/Metric.php app/Models/ProgressPhoto.php app/Models/WorkoutSession.php app/Models/BiometricLog.php` | 2 min |
| I | `rm -rf app/Policies app/Scopes && git checkout app/Providers/AppServiceProvider.php` | 2 min |
| J | revertir config NGINX desde EasyPanel UI (restaurar la config previa) | 3 min |
| K | `rm scripts/security-headers-check.php` | 10 s |

**Rollback total:** `git checkout main && git branch -D security/hardening-2026-Q2` + revertir NGINX config + `.env` session flag + `php artisan config:cache`.

---

## 7. FASES MARCADAS COMO "BLOCKED — REQUIERE VERIFICACIÓN MANUAL"

Estas fases del audit original NO se ejecutan en este roadmap:

1. **§5.2 `SESSION_SAME_SITE=strict`** — romperá Google OAuth. Requiere test manual del flujo OAuth en staging antes de intentar. Hasta entonces mantener `lax`.

2. **§5.3 `HardenLegacyCookie`** — no tiene efecto porque `wc_token` no está en `encryptCookies(except:[])` y por tanto Laravel no puede leer la cookie vanilla. Requiere decisión de producto:
   - Opción A: unificar auth (deprecar cookie vanilla, todo vía Bearer/session Laravel).
   - Opción B: agregar `wc_token` al `except` para que Laravel la lea y harden — requiere coordinación con vanilla.

3. **§6.1 MySQL SSL** — depende del managed DB de EasyPanel. Verificar primero si el DB host soporta SSL y dónde está el CA cert.

4. **§6.2 DB user restringido** — operación DBA, requiere cambio en `.env` prod y coordinación.

5. **§6.3 General query log** — alto costo de performance. Correr sólo en staging por 24h.

6. **Políticas para TODOS los modelos** (§4.4 del audit, ongoing §11.3) — fuera de alcance de este roadmap. Requiere auditoría por método de controller, mínimo 2 sprints.

---

## 8. RECOMENDACIÓN OPERATIVA

**Estrategia recomendada:** ejecutar por fases con validación entre cada una. Nada de big-bang.

1. **Batch 1 (hoy, si hay luz verde):** A → B → C → D → E. Son los cambios de mayor valor con menor riesgo de regresión. Tiempo total: ~45 min + smoke test.
2. **Batch 2 (siguiente ventana):** F (trustProxies) + G (session encrypt). Requieren ventana de bajo tráfico (invalida sesiones activas) y validar /health con nuevo trustProxies.
3. **Batch 3 (fin de semana):** H + I + J + K. RLS + Policies + NGINX + script. Requiere correr suite completa de tests + recorrido QA manual.

**Bloqueadores importantes detectados durante el análisis:**

- **Antes de Fase F**: confirmar con operaciones qué CIDR usa Traefik en EasyPanel. Si está en una red custom fuera de RFC 1918, ajustar la lista.
- **Antes de Fase G**: coordinar ventana de mantenimiento — sesiones activas se invalidan.
- **Antes de Fase D**: verificar que el SPA Vue actual envía `Authorization: Bearer <token>` en TODAS las llamadas `/api/v/client/*`. Si algún componente depende sólo de `session('wc_token')` sin header, agregar `auth:wellcore` le cortará el acceso. `WellCoreGuard::getTokenFromRequest` lo lee igual, pero si el token se perdió del header el cliente ve 401. **Mitigación:** `EnsureAuthenticated` ya lee sesión PHP como fallback (línea 50-56). ✓

---

## 9. DECISIONES EXPLÍCITAS (frente al plan original)

| Decisión | Motivo |
|----------|--------|
| **Descartar** el handler `AuthenticationException → JSON 401` en `bootstrap/app.php` | No se dispara porque `EnsureAuthenticated` no lanza la excepción; en su lugar, modificar `EnsureAuthenticated` directamente (Fase C). |
| **Descartar** `OwnedByCoachScope` global | La ownership coach→client es UNION de 5 fuentes; un scope simple `coach_id` rompería dashboard coach. Usar Policies para coach. |
| **Diferir** hardening de cookie `wc_token` | Laravel ya no la puede leer (EncryptCookies la descarta). El middleware `HardenLegacyCookie` del audit no tendría efecto. Requiere decisión de producto sobre la interop con vanilla. |
| **Cambiar** CIDRs de Cloudflare por redes privadas RFC 1918 | La infra real es EasyPanel + Docker + Traefik, no Cloudflare. |
| **Descartar** `SESSION_SAME_SITE=strict` en esta fase | Rompe Google OAuth sin verificación manual previa. |
| **Agregar** endurecimiento de `EnsureRole` (devolver JSON para api/*) | El audit no lo menciona pero es necesario para Fase E. |

---

## 10. RÚBRICA DE ÉXITO

Esta implementación se considera exitosa si:

1. Todas las fases A-K se aplican en producción sin rollback.
2. El checklist §5 pasa al 100%.
3. `scripts/security-headers-check.php` devuelve exit 0 contra producción.
4. La severidad promedio del audit baja de CRÍTICA/ALTA a MEDIA/BAJA.
5. Zero regresiones reportadas en funcionalidad existente (clientes, coaches, admins, vanilla interop, pagos, chatbot).
6. No se tocó ni un archivo de la app vanilla PHP.

---

*Fin del roadmap. Listo para ejecución por fases con aprobación explícita por fase.*
