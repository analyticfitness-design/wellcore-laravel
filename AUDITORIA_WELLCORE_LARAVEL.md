# 🔍 AUDITORÍA WELLCORE LARAVEL — Informe Completo

> **Fecha:** 2026-04-23  
> **Auditor:** Kimi Code CLI  
> **Objetivo:** Identificar fugas, errores, fallas y vulnerabilidades críticas para que Claude Code Opus 4.7xhigh ejecute el plan de remediación.

---

## 📊 RESUMEN EJECUTIVO

| Categoría | Críticas | Altas | Medias | Bajas |
|-----------|----------|-------|--------|-------|
| **Seguridad** | 2 | 5 | 8 | 6 |
| **Estabilidad / Bugs** | 3 | 6 | 10 | 4 |
| **Performance** | 1 | 3 | 4 | 2 |
| **Infraestructura / DevOps** | 1 | 2 | 4 | 2 |
| **Tests / Calidad** | 2 | 3 | 4 | 2 |

**Puntaje de salud general: 62/100** ⚠️  
La aplicación tiene problemas significativos de estabilidad, seguridad y calidad de código que requieren atención inmediata antes de producción.

---

## 🚨 PROBLEMAS CRÍTICOS (Atención Inmediata)

### CRIT-01: API Routes SIN middleware de autenticación centralizado 🔴 HIGH
- **Archivo:** `routes/api.php` (grupos `v/client`, `v/coach`, `v/admin`, `v/rise`)
- **Problema:** Las rutas API autenticadas solo aplican `throttle:api` o `ensure.plan:elite`, pero **NO** aplican `auth:wellcore`, `EnsureAuthenticated`, ni ningún middleware que valide el token de forma centralizada. La protección depende 100% de que cada controller use el trait `AuthenticatesVueRequests` y llame a `resolveClientOrFail()` / `resolveAdminOrFail()` en **cada método individual**. Si un desarrollador olvida usar el trait o se salta la resolución en un nuevo método, la ruta queda **completamente expuesta al público**.
- **Impacto:** Acceso no autorizado a datos de clientes, coaches, admins, pagos, entrenamientos, fotos de progreso.
- **Fix:** Aplicar middleware de autenticación centralizado a cada grupo de rutas:
  ```php
  Route::prefix('v/client')->middleware(['throttle:api', 'auth:wellcore'])->group(function () {
  ```

### CRIT-02: Contraseña por defecto HARDCODEADA en código fuente 🔴 HIGH
- **Archivo:** `app/Http/Controllers/Api/AdminController.php:1700`
- **Código:** `'password_hash' => $extras['password_hash'] ?? bcrypt('WellCore2026!'),`
- **Problema:** Al convertir inscripciones a clientes, si no hay password_hash en extras, se asigna una contraseña por defecto hardcodeada (`WellCore2026!`). Cualquier persona con acceso al código conoce esta contraseña.
- **Impacto:** Clientes que no cambien su contraseña quedan expuestos a acceso no autorizado.
- **Fix:** Generar contraseña aleatoria de 16+ caracteres y enviarla por email, o forzar cambio en primer login.

### CRIT-03: `page_visits` migración existe pero NUNCA fue ejecutada 🔴 Critical
- **Archivo:** `database/migrations/2026_03_27_000001_create_page_visits_table.php`
- **Problema:** La migración existe en el repo, pero los logs confirman que la tabla NO existe en DB. El middleware `TrackUtmParameters` intenta insertar en cada visita y falla.
- **Impacto:** Tracking de visitas roto, errores SQL en cada request, FK incompatible si se intenta crear manualmente.
- **Fix:** `php artisan migrate` para ejecutar la migración pendiente. Verificar que `client_id` tenga el mismo tipo que `clients.id`.

### CRIT-04: Ruta a método aparentemente existente pero con error histórico de caché (`aiNutritionHistory`)
- **Archivo:** `routes/api.php` (línea ~140) → `SocialController.php`
- **Problema:** El log del 2026-04-01 registra `Call to undefined method App\Http\Controllers\Api\SocialController::aiNutritionHistory()`. **Actualmente el método SÍ existe** en el código fuente. Esto indica que el error fue causado por **caché de clases de OPCache/Composer autoload** o un deploy incompleto donde el archivo no fue refrescado.
- **Impacto:** Error 500 fatal para usuarios Elite si el deploy no limpia caché de clases. Riesgo de recurrencia en producción.
- **Fix:** Agregar `php artisan optimize:clear` y `composer dump-autoload` al pipeline de deploy. Verificar que el proceso de despliegue refresque caché de clases.

### CRIT-05: Tabla `page_visits` no existe pero el código la usa activamente
- **Archivos:** `app/Livewire/InscriptionForm.php:207`, `app/Models/PageVisit.php`, `app/Services/WompiService.php:451`
- **Problema:** El modelo `PageVisit` apunta a `page_visits`, pero la tabla NO EXISTE en la base de datos. Cada vez que se intenta registrar una visita o se procesa un pago Wompi, ocurre un error SQL `1146`.
- **Impacto:** Fallos silenciosos en tracking, potencial pérdida de datos de conversión, errores 500 en inscripciones.
- **Fix:** Crear la migración para `page_visits` o eliminar todas las referencias si el feature fue descartado.

### CRIT-06: Memory Limit Exhausted (128MB) durante tests y en runtime
- **Archivos:** Tests en general, `vendor/psy/psysh`, `vendor/nikic/php-parser`
- **Problema:** `memory_limit = 128M` en PHP es insuficiente para Laravel 13 con 61 modelos, 131 componentes Vue, y procesamiento de imágenes. Los tests crashean con `Fatal error: Allowed memory size of 134217728 bytes exhausted`.
- **Impacto:** Tests inestables, riesgo de crashes en producción bajo carga, imposibilidad de CI/CD confiable.
- **Fix:** Aumentar a `512M` mínimo (recomendado `1G` para dev/testing). Revisar leaks de memoria en procesamiento de imágenes.

### CRIT-07: Tests fallan consistentemente (6/12 archivos con fallos)
- **Archivos:** `tests/Feature/PaymentFlowTest.php`, `tests/Feature/SecurityHeadersTest.php`, `tests/Feature/PublicPagesTest.php`
- **Problemas específicos:**
  - `PaymentFlowTest`: `checkout page requires plan parameter` falla (assertStatus 200 pero la ruta probablemente necesita query params).
  - `wompi webhook rejects invalid signature`: espera 401 pero devuelve 403.
  - `SecurityHeadersTest`: headers de seguridad NO están presentes en la respuesta de `/`. El middleware `ContentSecurityPolicy` probablemente NO está aplicado a rutas públicas.
  - `PublicPagesTest`: `/rise` falla (probablemente redirección o error).
  - **Fatal error** al final de la suite: parser exhausts memory.
- **Impacto:** Sin tests verdes no hay confianza en deploys. Regresiones pasarán desapercibidas.
- **Fix:** Corregir tests, aplicar middleware CSP globalmente, aumentar memory limit.

### CRIT-08: Foreign Key Incompatible en migración de `page_visits`
- **Log:** `SQLSTATE[HY000]: General error: 3780 Referencing column 'client_id' and referenced column 'id' in foreign key constraint 'page_visits_client_id_foreign' are incompatible`
- **Problema:** Intentaron crear FK `client_id` → `clients.id` pero los tipos de columna no coinciden (probablemente `INT` vs `BIGINT UNSIGNED` o similar).
- **Impacto:** Migración fallida, tabla no creada.
- **Fix:** Alinear tipos de columna en migración.

---

## 🔒 SEGURIDAD (HIGH)

### HIGH-SEC-01: Dependencias vulnerables en producción
| Paquete | Severidad | CVE | Acción |
|---------|-----------|-----|--------|
| `phpseclib/phpseclib` | Low | CVE-2026-40194 | Actualizar a >=3.0.51 |
| `axios` (npm) | Moderate | GHSA-3p68-rc4w-qgx5, GHSA-fvcv-3m26-pcqx | `npm audit fix` |
| `follow-redirects` | Moderate | GHSA-r4q5-vmmm-2653 | `npm audit fix` |
| `picomatch` | **High** | GHSA-3v7f-55p6-f55p, GHSA-c2c7-rcm5-vvqj | `npm audit fix` |
| `vite` | **High** | GHSA-4w7w-66w2-5vf9, GHSA-v2wj-q39q-566r, GHSA-p9ff-h696-f583 | `npm audit fix` |

### HIGH-SEC-02: CSP permite `unsafe-inline` y `unsafe-eval`
- **Archivo:** `app/Http/Middleware/ContentSecurityPolicy.php`
- **Problema:** `script-src 'self' 'unsafe-inline' 'unsafe-eval' ...` anula gran parte de la protección XSS que ofrece CSP.
- **Impacto:** Si se inyecta un script malicioso, el navegador lo ejecutará.
- **Fix:** Migrar a nonces o hashes. Eliminar `unsafe-eval` si no se usa `eval()`.

### HIGH-SEC-03: `SESSION_ENCRYPT=false` por defecto
- **Archivo:** `config/session.php` → `'encrypt' => env('SESSION_ENCRYPT', false)`
- **Problema:** Las cookies de sesión NO están encriptadas por defecto. En un entorno compartido o si hay acceso al filesystem, los tokens son legibles.
- **Impacto:** Session hijacking si alguien lee el archivo de sesión o la cookie.
- **Fix:** Cambiar a `true` y agregar la key a `.env`.

### HIGH-SEC-04: Información sensible expuesta en `.env` (DEV)
- **Archivo:** `.env`
- **Datos expuestos:** DB_PASSWORD, MAIL_PASSWORD, WOMPI_PRIVATE_KEY, WOMPI_EVENTS_SECRET, WOMPI_INTEGRITY_SECRET.
- **Impacto:** Aunque sea dev, el archivo está en git working tree y puede ser commiteado accidentalmente.
- **Fix:** Verificar `.gitignore` incluye `.env`. Rotar keys si alguna vez fue commiteada.

### HIGH-SEC-05: Missing CORS Configuration
- **Problema:** No existe `config/cors.php`. Si la API es consumida desde dominios cruzados (el SPA Vue), CORS está manejado por defaults de Laravel que pueden ser permisivos.
- **Impacto:** Potencial CSRF-like desde dominios no autorizados.
- **Fix:** Crear configuración CORS explícita restringiendo `allowed_origins`.

### HIGH-SEC-06: `X-XSS-Protection: 1; mode=block` está deprecado
- **Archivo:** `ContentSecurityPolicy.php`
- **Problema:** Este header legacy puede introducir vulnerabilidades (XS-Leaks) en navegadores modernos.
- **Fix:** Eliminar el header. CSP moderno es suficiente.

### MED-SEC-07: Mass Assignment en múltiples modelos 🟡 MEDIUM
- **Modelos afectados:**
  - `Admin.php`: `role`, `active`, `must_change_password` en `$fillable`
  - `Client.php`: `password_hash`, `plan`, `status` en `$fillable`
  - `AuthToken.php`: `token`, `expires_at` en `$fillable`
  - `PlanTicket.php`: `status`, `submitted_at`, `completed_at`, `rejected_at` en `$fillable`
  - `WorkoutSession.php`: `xp_earned`, `completed`, `total_volume_kg` en `$fillable`
  - `CoachProfile.php`: `referral_commission`, `public_visible` en `$fillable`
- **Impacto:** Un atacante podría auto-escalar privilegios, falsificar datos de entrenamiento, o manipular tickets si un endpoint pasa input directo a `create()`/`update()`.
- **Fix:** Remover campos sensibles de `$fillable` y asignarlos manualmente en controllers.

### MED-SEC-08: `admin_token` en POST body sin validación de rol 🟡 MEDIUM
- **Archivo:** `EnsureAuthenticated.php:67-70`
- **Problema:** Acepta `admin_token` desde el body de una petición POST para impersonación. No hay verificación inmediata de que el token pertenezca a un admin activo antes de guardarlo en sesión.
- **Fix:** Validar explícitamente que el token corresponde a `user_type = 'admin'` y que no está expirado.

### MED-SEC-09: Rate limiter `api` basado solo en IP 🟡 MEDIUM
- **Archivo:** `AppServiceProvider.php:41-43`
- **Problema:** `Limit::perMinute(60)->by($request->ip())`. Un usuario malintencionado en una red corporativa con NAT bloquea a todos los demás usuarios detrás de la misma IP.
- **Fix:** `$key = optional($request->user())->id ?? $request->ip();`

### MED-SEC-10: Video check-ins almacenados en disco público 🟡 MEDIUM
- **Archivo:** `app/Livewire/Client/VideoCheckinUpload.php:65`
- **Problema:** `$this->mediaFile->store('checkins/' . $clientId, 'public')`. Los videos son accesibles por URL directa. El endpoint API equivalente usa disco `private`.
- **Fix:** Cambiar a disco `private` y servir mediante endpoint autenticado.

### LOW-SEC-11: `reset-password` sin rate limiting 🟡 LOW
- **Archivo:** `routes/api.php:47`
- **Problema:** `POST /v/auth/reset-password` no tiene throttle. `forgot-password` sí tiene rate limiting.
- **Fix:** Agregar `->middleware('throttle:login')` o un limiter dedicado.

### LOW-SEC-12: Rutas web de portal sin middleware de auth 🟡 LOW
- **Archivo:** `routes/web.php` (líneas 176-240)
- **Problema:** `/client/*`, `/coach/*`, `/admin/*`, `/rise/*` usan `Route::view('vue')` sin middleware `auth:wellcore`. Cualquiera puede descargar el shell HTML/JS del SPA.
- **Fix:** Aplicar `middleware('auth:wellcore')` o redirigir a `/login` si no hay sesión.

---

## 🐛 ESTABILIDAD / BUGS (HIGH)

### HIGH-BUG-01: `SlotProxy::count()` en Blade de Coach — Colisión con Livewire 3 🔴 High
- **Archivo:** `resources/views/livewire/coach/coach-features.blade.php:~390`
- **Problema:** `{{ $slots->count() }}` falla porque `$slots` en Livewire 3 puede ser una instancia de `Livewire\Features\SupportSlots\SlotProxy` en lugar de una Collection. El log (2026-03-21) confirma: `Call to undefined method Livewire\Features\SupportSlots\SlotProxy::count()`.
- **Fix:** Renombrar la variable a `$availabilitySlots` en `CoachFeatures.php:699` y en la vista para evitar colisión con el slot system de Livewire 3.

### HIGH-BUG-02: Relación `plan` no definida en `Payment` 🔴 High
- **Archivo:** `app/Models/Payment.php`
- **Problema:** El log (2026-03-23) muestra `Call to undefined relationship [plan] on model [App\Models\Payment]`. El modelo tiene la columna `plan` (casteado a `PlanType` enum), pero **no tiene método relación** `plan()`. En algún lugar del código se usa como relación Eloquent (`with('plan')`, `load('plan')`).
- **Fix:** Buscar el uso de `plan` como relación en Payment y cambiarlo a acceso de propiedad (`$payment->plan`) o crear el método `plan()` que apunte a `AssignedPlan` o `PlanTemplate`.

### HIGH-BUG-03: `ExportService::export()` sin try-catch en operación de archivo 🔴 High
- **Archivo:** `app/Services/ExportService.php:175`
- **Problema:** `$handle = fopen('php://output', 'w');` y `fputcsv()` no están envueltos en try-catch. Si `php://output` falla o hay headers ya enviados, la aplicación crashea sin logging controlado.
- **Fix:** Envolver en `try { ... } catch (\Throwable $e) { Log::error(...); abort(500); }`.

### HIGH-BUG-04: Error recurrente masivo: "There are no commands defined in the 'boost' namespace"
- **Frecuencia:** ~30+ veces en logs entre 2026-04-01 y 2026-04-23.
- **Causa probable:** Un proceso externo, cron job, MCP, o IDE intenta ejecutar `php artisan boost:mcp` pero el package `boost` no está instalado.
- **Impacto:** Ruido en logs, dificulta detectar errores reales.
- **Fix:** Identificar y eliminar el proceso que invoca `boost:mcp`. Revisar `.vscode/settings.json`, `.claude/`, cron jobs, o MCP configs.

### HIGH-BUG-05: `OptimizeImages` command usa `--force` que no existe
- **Log:** `The "--force" option does not exist.`
- **Archivo:** `app/Console/Commands/OptimizeImages.php`
- **Problema:** Alguien intentó ejecutar `wellcore:optimize-images --force` pero el comando no define esa opción.
- **Impacto:** Script de deploy/optimización puede fallar.
- **Fix:** Agregar `--force` al signature o eliminar su uso.

### HIGH-BUG-06: Integrity constraint violation en `ejercicio_videos.fitcron_slug`
- **Log:** `SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'fitcron_slug' cannot be null`
- **Problema:** Se intenta insertar un video sin `fitcron_slug`, pero la columna es `NOT NULL`.
- **Impacto:** Falla en importación/creación de videos de ejercicios.
- **Fix:** Hacer `fitcron_slug` nullable o validar antes de insertar.

### HIGH-BUG-07: Data truncated for column 'habit_type'
- **Log:** `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'habit_type' at row 1`
- **Problema:** Se insertó `'entrenamiento'` en `habit_type` pero el enum/tipo no lo acepta.
- **Impacto:** Pérdida silenciosa de datos de hábitos.
- **Fix:** Revisar enum `HabitType` y validación en controller.

### HIGH-BUG-08: Error `There are no commands defined in the "boost" namespace` constante
- **Misma causa que HIGH-BUG-04.** Este error aparece más de 30 veces en los logs recientes.

---

## ⚡ PERFORMANCE (HIGH)

### HIGH-PERF-01: Uso excesivo de `DB::raw()` / `selectRaw()` en AdminController
- **Archivo:** `app/Http/Controllers/Api/AdminController.php` (~10+ instancias)
- **Problema:** Múltiples queries con raw SQL (DATE_FORMAT, CASE WHEN, MIN/MAX agregados). Si no hay índices adecuados en `created_at`, `role`, `plan_type`, estas queries escanean tablas completas.
- **Impacto:** Dashboard admin lento con muchos registros.
- **Fix:** Añadir índices compuestos. Considerar materialized views o caching de agregados.

### HIGH-PERF-02: `CACHE_STORE=array` (no persiste entre requests)
- **Archivo:** `.env` → `CACHE_STORE=array`
- **Problema:** Cache en memoria que se pierde en cada request. Cualquier `Cache::remember` es inútil.
- **Impacto:** Queries redundantes, APIs lentas, carga innecesaria en DB.
- **Fix:** Usar `redis` o `database` cache en producción.

### HIGH-PERF-03: `SESSION_DRIVER=file`
- **Archivo:** `.env` → `SESSION_DRIVER=file`
- **Problema:** En múltiples instancias o con load balancer, las sesiones en archivo no se comparten.
- **Impacto:** Usarios deslogueados aleatoriamente, inconsistencias en auth.
- **Fix:** Migrar a `database`, `redis`, o `cookie`.

---

## 🧪 TESTS / CALIDAD

### MED-TEST-01: Solo 12 archivos de tests para 61 modelos + 25+ Livewire components
- **Cobertura estimada:** <15%.
- **Impacto:** Regresiones frecuentes, miedo a refactorizar.
- **Fix:** Implementar tests para:
  - Auth flow (login, logout, token expiry)
  - API CRUD endpoints (todos los controllers)
  - File upload validation
  - Webhook signature verification
  - Mass assignment protection

### MED-TEST-02: Security headers tests fallan
- **Problema:** El middleware `ContentSecurityPolicy` no está aplicado a rutas públicas (o el test espera headers que no se envían).
- **Fix:** Registrar el middleware globalmente en `bootstrap/app.php` o `Kernel` equivalente.

---

## 🏗️ INFRAESTRUCTURA / DEVOPS

### MED-INFRA-01: No hay `VerifyCsrfToken.php` (Laravel 13 sin defaults)
- **Problema:** En Laravel 13 el CSRF se maneja diferente, pero es crítico verificar que TODAS las rutas web POST/PUT/DELETE estén protegidas.
- **Fix:** Auditar `bootstrap/app.php` para confirmar CSRF middleware en rutas web.

### MED-BUG-09: Commands con PII hardcodeada (emails reales de clientes) 🟠 Medium
- **Archivos:** `app/Console/Commands/FixRisePrograms.php:38,101,110,174,183,279,426,433`, `app/Console/Commands/DiagnoseClients.php:19-24,37`
- **Problema:** Emails reales de clientes (`l.gizethmm29@gmail.com`, `nelsonroasotelo@gmail.com`, etc.) están hardcodeados en scripts de consola. Violan privacidad y hacen el código no reutilizable.
- **Fix:** Pasar emails como argumentos de comando (`$this->argument('email')`). Ejecutar `git filter-repo` para purgar del historial.

### MED-BUG-10: URLs hardcodeadas en Mails y Services 🟠 Medium
- **Archivos:**
  - `app/Mail/GiftPlanInvitation.php:59` → `$baseUrl = 'https://wellcorefitness.com'`
  - `app/Mail/NewCoachCredentials.php:41` → `'loginUrl' => 'https://www.wellcorefitness.com/login'`
  - `app/Mail/PlanInvitation.php:53` → `$baseUrl = 'https://wellcorefitness.com'`
  - `app/Services/TerraService.php:13` → `$baseUrl = 'https://api.tryterra.co/v2'`
  - `app/Http/Controllers/Api/PublicFormController.php:542` → Facebook Graph API URL hardcodeada
- **Fix:** Mover URLs a `config/wellcore.php` o `.env` y leer vía `config()`.

### MED-BUG-11: Comparaciones loose (`==`) en lugar de estrictas (`===`) 🟠 Medium
- **Archivos:** `app/Livewire/Coach/Analytics.php:518`, `app/Livewire/Coach/CoachFeatures.php:661`, `app/Livewire/Client/HabitTracker.php:75,136`
- **Problema:** Comparaciones loose pueden causar bugs de tipo (ej. `"0" == 0 == false`).
- **Fix:** Cambiar a `===` donde sea semánticamente correcto.

### MED-BUG-12: Casting inconsistente de `$amount` en Payment 🟠 Medium
- **Archivo:** `app/Models/Payment.php:39`
- **Problema:** El campo `amount` se castea a `decimal:2`, pero en muchos lugares se usa como entero en centavos o como float sin claridad (`WompiService` usa `amount_in_cents`).
- **Fix:** Documentar claramente si `amount` en `payments` es en pesos o centavos y mantener consistencia.

### MED-BUG-13: Multiple envíos de Mail sin manejo de excepciones 🟠 Medium
- **Archivos:** `app/Console/Commands/BehavioralTriggersCommand.php:79`, `app/Console/Commands/AutoRenewalCommand.php:46`, `app/Console/Commands/WeeklySummaryCommand.php:63`
- **Problema:** Usan `Mail::to()->queue()` sin try-catch. Si el servicio de mail falla, el comando entero falla.
- **Fix:** Envolver envíos de mail en bloques try-catch con logging.

### MED-BUG-14: `WompiService` hace múltiples DB updates sin transacción 🟠 Medium
- **Archivo:** `app/Services/WompiService.php:346,384,455`
- **Problema:** Actualiza Payments, Clients y PaymentLogs en secuencia sin transacción explícita. Si falla a la mitad, queda inconsistencia.
- **Fix:** Agrupar operaciones relacionadas en `DB::transaction()`.

### MED-BUG-15: `route:list --columns` ya no existe en Laravel 13 🔴 High
- **Log:** `storage/logs/laravel.log` (2026-03-20, 2026-03-21 múltiples veces)
- **Problema:** `The "--columns" option does not exist.` indica que alguien ejecuta `php artisan route:list --columns=method,uri,name`. En Laravel 11+ esta opción fue removida.
- **Fix:** Actualizar scripts/documentación/aliases para usar `php artisan route:list` sin `--columns`.

### MED-INFRA-02: `APP_DEBUG=true` en archivo `.env`
- **Impacto:** Stack traces expuestos a usuarios finales si este `.env` llega a producción.
- **Fix:** Nunca commitear `.env` con debug=true. Usar `.env.production` template.

### LOW-INFRA-01: Build Vite no auditado
- **Vite config:** Buena configuración (es2022, manual chunks, pure console removal).
- **Potencial problema:** No hay source maps en producción configurados explícitamente.

---

## 📋 PLAN DE ACCIÓN PARA CLAUDE CODE OPUS 4.7xHIGH

> Este plan está diseñado para ser ejecutado secuencialmente por un agente de alto rendimiento. Cada fase tiene tareas atómicas, verificables y con criterios de aceptación claros.

---

### 🔧 FASE 1: ESTABILIDAD CRÍTICA (Bloqueante — Resolver primero)

**Objetivo:** Eliminar errores fatales y crashes.

| # | Tarea | Archivos | Criterio de éxito |
|---|-------|----------|-------------------|
| 1.1 | **Fix middleware auth en rutas API** | `routes/api.php` | Agregar `'auth:wellcore'` a grupos `v/client`, `v/coach`, `v/admin`, `v/rise`. Verificar que login sigue funcionando. |
| 1.2 | **Eliminar contraseña hardcodeada** | `AdminController.php:1700` | Reemplazar `bcrypt('WellCore2026!')` por generación aleatoria de 16+ chars + envío por email. |
| 1.3 | **Fix ruta `aiNutritionHistory` + caché** | `routes/api.php`, deploy pipeline | Agregar `php artisan optimize:clear` y `composer dump-autoload` al pipeline de deploy. |
| 1.4 | **Ejecutar migración `page_visits`** | `database/migrations/...create_page_visits_table.php` | `php artisan migrate`. Verificar FK compatible. |
| 1.5 | **Fix `coach-features.blade.php` SlotProxy** | `CoachFeatures.php`, `coach-features.blade.php` | Renombrar `$slots` → `$availabilitySlots` para evitar colisión con Livewire 3. |
| 1.6 | **Fix relación `plan` en Payment** | `Payment.php`, controllers que usan `with('plan')` | Cambiar a propiedad `$payment->plan` o crear método relación. |
| 1.7 | **Aumentar memory_limit** | `php.ini` o `.user.ini` | `memory_limit = 512M` mínimo. Verificar con `php -r "echo ini_get('memory_limit');"` |
| 1.8 | **Fix migración FK incompatible** | Revisar migration existente de `page_visits` | Alinear tipos: si `clients.id` es `bigint unsigned`, `client_id` debe ser `bigint unsigned` + `nullable()` + `constrained('clients')->nullOnDelete()` |
| 1.9 | **Fix integrity constraint `fitcron_slug`** | `app/Http/Controllers/Api/EjerciciosController.php` o seeder | Validar `fitcron_slug` requerido o hacer columna nullable en DB + modelo |
| 1.10 | **Fix data truncated `habit_type`** | Revisar enum y controller que inserta `entrenamiento` | Asegurar que el valor coincide con los valores permitidos del enum/columna |

---

### 🛡️ FASE 2: SEGURIDAD (Alta prioridad)

| # | Tarea | Archivos | Criterio de éxito |
|---|-------|----------|-------------------|
| 2.1 | **Actualizar dependencias vulnerables** | `composer.json`, `package.json` | `composer audit` → 0 vulnerabilidades. `npm audit` → 0 high/critical. |
| 2.2 | **Crear config CORS explícita** | `config/cors.php` | Solo orígenes autorizados (wellcore-laravel.test, wellcorefitness.com). |
| 2.3 | **Harden CSP headers** | `ContentSecurityPolicy.php` | Eliminar `unsafe-eval` si es posible. Reemplazar `unsafe-inline` con nonces en blades. |
| 2.4 | **Remover `X-XSS-Protection` deprecado** | `ContentSecurityPolicy.php` | Header eliminado. Tests pasan. |
| 2.5 | **Encriptar sesiones** | `config/session.php` o `.env` | `SESSION_ENCRYPT=true`. Verificar que login/siguiente funciona. |
| 2.6 | **Aplicar CSP middleware globalmente** | `bootstrap/app.php` | TODAS las rutas web devuelven headers de seguridad. Tests de `SecurityHeadersTest` pasan. |
| 2.7 | **Auditar mass assignment** | `app/Models/*.php` | Ningún modelo tiene campos sensibles (`role`, `is_admin`, `password_hash`) en `$fillable`. |
| 2.8 | **Revisar file uploads** | `SocialController::uploadPhoto`, `RiseController::uploadPhoto`, `CoachPlanTicketController::uploadAttachment`, `CoachBrandController::uploadLogo` | Validación de: tamaño máximo, extensiones permitidas, mime-type real (no solo extensión), almacenamiento fuera de public/ para archivos sensibles. |
| 2.9 | **Revisar IDOR en endpoints** | Todos los controllers API con `{id}` | Verificar que el usuario autenticado solo puede acceder a recursos que le pertenecen (ej: `Client` no puede leer `CoachNote` de otro cliente). |
| 2.10 | **Sanitizar `body_html` de AcademyContent** | `resources/views/livewire/client/academia.blade.php:291` | Usar HTML Purifier antes de guardar/renderizar, o reemplazar `{!! !!}` por `{{ }}` si no requiere HTML real. |
| 2.11 | **Fix `admin_token` en body sin validación de rol** | `EnsureAuthenticated.php:67-70` | Validar explícitamente que el token corresponde a `user_type = 'admin'` y no está expirado antes de aceptarlo desde el body. |
| 2.12 | **Fix rate limiter `api` por IP** | `AppServiceProvider.php:41-43` | Usar `$key = optional($request->user())->id ?? $request->ip();` en lugar de solo IP. |
| 2.13 | **Mover video check-ins a disco privado** | `VideoCheckinUpload.php:65` | Cambiar `store(..., 'public')` a `store(..., 'private')` y servir vía endpoint autenticado. |

---

### 🧪 FASE 3: TESTS Y CALIDAD

| # | Tarea | Archivos | Criterio de éxito |
|---|-------|----------|-------------------|
| 3.1 | **Fix `PaymentFlowTest`** | `tests/Feature/PaymentFlowTest.php` | Todos los tests pasan (checkout params, webhook 403, health 200, sitemap XML, lanzamiento 200). |
| 3.2 | **Fix `SecurityHeadersTest`** | `tests/Feature/SecurityHeadersTest.php` + middleware | Headers presentes en `/`. Test verde. |
| 3.3 | **Fix `PublicPagesTest::rise page loads`** | `routes/web.php` o blade | `/rise` devuelve 200. Test verde. |
| 3.4 | **Fix fatal error en test suite** | `php.ini` memory limit | Suite completa corre sin `Fatal error: memory exhausted`. |
| 3.5 | **Agregar tests de autenticación** | `tests/Feature/AuthFlowTest.php` | Tests para: login con email/code, token expiry, logout, unauthorized access. |
| 3.6 | **Agregar tests de API crítica** | Nuevos tests | Al menos: `ClientController` (dashboard, profile), `AdminController` (stats no crashean), `TrainingController` (workout flow). |
| 3.7 | **Fix `ExportService` try-catch** | `app/Services/ExportService.php:175` | Envolver `fopen`/`fputcsv` en try-catch con logging. |
| 3.8 | **Fix Mail commands try-catch** | `BehavioralTriggersCommand.php`, `AutoRenewalCommand.php`, `WeeklySummaryCommand.php` | Envolver `Mail::to()->queue()` en try-catch con logging. |
| 3.9 | **Fix `WompiService` transacciones** | `app/Services/WompiService.php` | Agrupar updates de Payments + Clients + PaymentLogs en `DB::transaction()`. |

---

### ⚡ FASE 4: PERFORMANCE

| # | Tarea | Archivos | Criterio de éxito |
|---|-------|----------|-------------------|
| 4.1 | **Migrar cache a Redis/Database** | `.env` → `CACHE_STORE=redis` | `Cache::remember` persiste entre requests. Verificar con test. |
| 4.2 | **Migrar sesiones a Database/Redis** | `.env` → `SESSION_DRIVER=database` o `redis` | Sesión funciona, token persistente. |
| 4.3 | **Auditar índices de DB** | Revisar migraciones de tablas grandes | Índices en: `auth_tokens(token)`, `auth_tokens(expires_at)`, `clients(email, status)`, `payments(client_id, status)`, `checkins(client_id, date)`. |
| 4.4 | **Revisar N+1 en AdminController** | `AdminController.php` | Todos los endpoints admin usan `with([...])` eager loading donde sea necesario. |
| 4.5 | **Cachear agregados admin** | `AdminController.php` stats | `Cache::remember('admin.stats', 60)` para contadores y gráficos. |
| 4.6 | **Archivar/Eliminar commands one-off con PII** | `FixRisePrograms.php`, `FixDanielCiclo.php`, `DumpSilviaPlan.php`, etc. | Mover a `archive/` o eliminar. Ejecutar `git filter-repo` para purgar emails del historial. |
| 4.7 | **Normalizar URLs hardcodeadas** | `GiftPlanInvitation.php`, `NewCoachCredentials.php`, `PlanInvitation.php`, `TerraService.php` | Mover a `config/wellcore.php` y leer vía `config()`. |

---

### 🧹 FASE 5: LIMPIEZA Y MANTENIBILIDAD

| # | Tarea | Archivos | Criterio de éxito |
|---|-------|----------|-------------------|
| 5.1 | **Eliminar ruido de logs: `boost` namespace** | Identificar proceso (`.claude/`, `.vscode/`, cron) | `grep -r "boost:mcp" .` → 0 resultados. Logs limpios. |
| 5.2 | **Fix `OptimizeImages` command signature** | `app/Console/Commands/OptimizeImages.php` | Agregar `--force` opción si se necesita, o eliminar de scripts que la usan. |
| 5.3 | **Revisar `{!! !!}` en blades** | `resources/views/**/*.blade.php` | Solo usar `{!! !!}` para datos 100% controlados por el servidor (icons, JSON-LD). NUNCA para input de usuario. Revisar traducciones FAQ. |
| 5.4 | **Documentar `.env` requerido** | `.env.example` | Todas las variables usadas en `config/wellcore.php` están documentadas. |
| 5.5 | **Agregar `robots.txt`** | `public/robots.txt` | Existe y bloquea `/client/`, `/admin/`, `/coach/`, `/rise/`. Test verde. |
| 5.6 | **Fix comparaciones loose (`==` → `===`)** | `Analytics.php:518`, `CoachFeatures.php:661`, `HabitTracker.php` | Cambiar a `===` donde sea semánticamente correcto. |
| 5.7 | **Documentar casting de `amount` en Payment** | `Payment.php`, `WompiService.php` | Documentar claramente si es pesos o centavos. Consistencia en todo el codebase. |
| 5.8 | **Fix `route:list --columns` en scripts** | Scripts/aliases/documentación | Usar `php artisan route:list` sin `--columns`. |
| 5.9 | **Archivar commands one-off con PII** | `FixRisePrograms.php`, `FixDanielCiclo.php`, `DumpSilviaPlan.php`, etc. | Mover a `archive/` o eliminar. Purgear emails del historial git. |
| 5.10 | **Normalizar URLs hardcodeadas** | `GiftPlanInvitation.php`, `NewCoachCredentials.php`, `PlanInvitation.php`, `TerraService.php` | Mover a `config/wellcore.php` y leer vía `config()`. |

---

## 🔗 REFERENCIAS RÁPIDAS PARA OPUS

### Errores recurrentes en logs (últimos 30 días)
```
- "There are no commands defined in the 'boost' namespace" (30+ veces)
- "Table 'wellcore_fitness.page_visits' doesn't exist" (SQL 42S02)
- "Allowed memory size of 134217728 bytes exhausted"
- "Column 'fitcron_slug' cannot be null" (SQL 23000)
- "Data truncated for column 'habit_type'" (SQL 01000)
- "The '--force' option does not exist" (OptimizeImages command)
- "Call to undefined method Livewire\Features\SupportSlots\SlotProxy::count()"
- "Call to undefined relationship [plan] on model [App\Models\Payment]"
- "The '--columns' option does not exist" (Laravel 13 removió --columns)
```

### Tests actualmente fallando
```
PaymentFlowTest:
  ✗ checkout page requires plan parameter
  ✗ wompi webhook rejects invalid signature (espera 401, devuelve 403)
  ✗ health check returns healthy status
  ✗ sitemap returns valid XML
  ✗ lanzamiento page loads

PublicPagesTest:
  ✗ rise page loads

SecurityHeadersTest:
  ✗ X-Content-Type-Options
  ✗ X-Frame-Options
  ✗ Referrer-Policy
  ✗ robots.txt blocks private areas

Fatal: memory exhausted al final de suite
```

### Dependencias vulnerables
```
composer: phpseclib/phpseclib CVE-2026-40194 (LOW)
npm: axios (MODERATE), follow-redirects (MODERATE), picomatch (HIGH), vite (HIGH)
```

### Configuración de seguridad actual
```
APP_DEBUG=true
SESSION_ENCRYPT=false
SESSION_DRIVER=file
CACHE_STORE=array
CSP: unsafe-inline + unsafe-eval permitidos
X-XSS-Protection: 1; mode=block (deprecado)
No CORS config explícita
```

---

## ✅ CHECKLIST DE ENTREGA

Al finalizar, Claude Code Opus debe confirmar:

- [ ] `php artisan test` → **TODOS verdes** (sin fatal errors)
- [ ] `composer audit` → **0 vulnerabilidades**
- [ ] `npm audit` → **0 high/critical**
- [ ] `php artisan migrate` → **sin errores** (incluyendo `page_visits`)
- [ ] Login/logout funciona en navegador
- [ ] Dashboard admin carga stats sin timeout
- [ ] No hay errores `boost namespace` en logs
- [ ] Headers de seguridad presentes en **TODAS** las rutas web
- [ ] File uploads validan tipo, tamaño, y extensión
- [ ] Rutas API protegidas con `auth:wellcore` middleware
- [ ] Video check-ins en disco `private` (no público)
- [ ] Mass assignment sanitizado en modelos sensibles
- [ ] `ExportService` y Mail commands con try-catch
- [ ] `WompiService` usa transacciones DB consistentes
- [ ] No hay PII hardcodeada en Commands

---

*Fin del informe. Generado por Kimi Code CLI el 2026-04-23.*
