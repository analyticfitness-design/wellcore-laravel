# PLAN DE IMPLEMENTACIÓN — WellCore Laravel

> **Auditor revisor:** Arquitecto Senior Laravel (Strangler Fig / Enterprise)
> **Fecha:** 2026-04-23
> **Versión:** 1.0 — rectificación crítica de `AUDITORIA_WELLCORE_LARAVEL.md` + `ANALISIS_RIESGO_PLAN_WELLCORE.md`
> **Entrada:** 2 documentos de auditoría previos (Kimi Code CLI)
> **Salida:** Este plan operativo con validación contra código actual

---

## 0. Contexto y Supuestos Validados

Verificaciones ejecutadas leyendo el código real (no confiando en logs históricos):

### Arquitectura confirmada
- **Laravel 13.1.1 + PHP 8.4** en `C:\Users\GODSF\Herd\wellcore-laravel`
- **DB MySQL `wellcore_fitness`** compartida con app vanilla PHP en `C:\Users\GODSF\Herd\wellcorefitness` (intocable)
- **Auth multimodal** vía `WellCoreGuard` que lee tokens desde 3 fuentes:
  - Session (`session('wc_token')`) → Livewire / web tradicional
  - Bearer header (`Authorization: Bearer <token>`) → Vue SPA
  - Cookie (`$request->cookie('wc_token')`) → compatibilidad app vanilla
- **Vue SPA** consume `/api/v/client|coach|admin|rise/*` con Bearer token; cada controller usa trait `AuthenticatesVueRequests::resolveClientOrFail()` / `resolveAdminOrFail()` que retorna 401/403 JSON
- **Livewire 3** usa middleware `auth:wellcore` vía grupo `web`
- **Rutas web `/client/*`, `/coach/*`, `/admin/*`, `/rise/*`** (routes/web.php:176-240) sirven el **shell HTML vacío** de la SPA — no exponen datos (toda API pide Bearer)

### Tipos de DB clave (confirmados leyendo migraciones existentes)
- `clients.id` → `INT UNSIGNED` (legacy, documentado en migración `2026_04_19_200000_create_medals_tables.php:46-48`)
- `medals`, `client_medals`, `workout_*`, `payments`, `payment_logs` → tablas **nuevas Laravel** (migraciones CREATE)
- `clients`, `habit_logs`, `ejercicio_videos`, `admins`, `auth_tokens`, `inscriptions` → tablas **compartidas con vanilla** (solo ALTER, nunca CREATE en Laravel)

### Ambiente real de `.env` (local dev)
- `APP_ENV=local`, `APP_DEBUG=true`
- `CACHE_STORE=array` (no persiste)
- `SESSION_DRIVER=file`, `SESSION_ENCRYPT=false`
- Sin `config/cors.php`

---

## 1. Hallazgos Descartados / Falsos Positivos

Tras revisión exhaustiva del código actual, los siguientes hallazgos del plan original se descartan o matizan:

### 1.1 ❌ HIGH-BUG-02 "Payment::plan relación no definida" — **FALSO POSITIVO**
- **Evidencia:** `app/Models/Payment.php:36` castea `'plan' => PlanType::class` (enum cast). El campo `plan` NO se usa como relación Eloquent en ninguna parte del codebase actual (`grep "with('plan')"`, `grep "load('plan')"` → 0 resultados).
- **Por qué falló el plan original:** El log citado (2026-03-23) reflejó un bug histórico ya corregido, no un problema activo.
- **Acción:** Ninguna. Si el método `plan()` se agregara ahora, chocaría con la columna casteada.

### 1.2 ❌ LOW-SEC-12 "Rutas web portal sin auth:wellcore" — **FALSO POSITIVO DE DISEÑO**
- **Evidencia:** `routes/web.php:176-240` usa `Route::view('/client', 'vue')` sin middleware porque sirve el **bundle JS/HTML vacío de la SPA**. Toda data está detrás de `/api/v/*` con validación Bearer. Agregar auth a estas rutas requeriría session (que la SPA no usa) → rompería la carga inicial.
- **Acción:** Documentar el diseño (auth en API layer + Vue Router client-side). No tocar.

### 1.3 ⚠️ MED-SEC-07 "Mass Assignment en múltiples modelos" — **PELIGROSO ejecutar tal cual**
- **Evidencia de dependencia crítica:**
  - `AuthToken::create([... 'token' => $token ...])` se usa en **8 ubicaciones**: `AuthController:61`, `Login.php:68`, `ImpersonateController:60`, `GoogleAuthController:61`, `CoachImpersonateController:48`, `CoachController:1546`, `AuthTest`, `AuthFlowTest`. Quitar `'token'` de `$fillable` **rompe todo login**.
  - `Admin::create([... 'role' => UserRole::Coach ...])` se usa en 3 ubicaciones (`AdminCoachManagementController:116`, `AdminController:1071`, `CoachManagement.php:100`). Quitar `'role'` **rompe creación de coaches**.
  - `Client::create([... 'plan' => ..., 'status' => ... ...])` se usa en `ClientIntakeForm:410`, `AdminController:1695`, `PublicFormController:460`. Quitar esos campos **rompe inscripciones**.
- **Búsqueda de vulnerabilidad real:** NO se encontró **ningún** `->create($request->all())` ni `->update($request->validated())` sin filtrar campos en todo el codebase. Todos los usos construyen arrays manuales desde datos validados.
- **Conclusión:** El mass assignment es **teórico** (no explotable), pero el $fillable actual es **carga funcional crítica**. **NO tocar.**
- **Acción alternativa:** Documentar en cada controller sensible que los valores de campos privilegiados deben construirse desde datos controlados, nunca desde `$request->all()`. Añadir test de regresión.

### 1.4 ⚠️ CRIT-01 "Aplicar auth:wellcore a grupos API" — **ESTRATEGIA PELIGROSA**
- **Problema con la recomendación original:** `WellCoreGuard::getTokenFromRequest()` lee de 3 fuentes. Si se aplica `auth:wellcore` al grupo `/api/v/client` y un request del Vue SPA llega sin Bearer pero con cookie `wc_token` del dominio vanilla, el guard lo aceptaría → comportamiento inconsistente con el trait actual que **solo** valida Bearer.
- **Dato clave:** Los controllers API ya validan con `resolveClientOrFail()` / `resolveAdminOrFail()` que abortan con 401/403 JSON. El único riesgo real es que un dev nuevo olvide usar el trait en un método.
- **Acción pragmática (Fase C):** Crear un middleware `ApiBearerAuth` **nuevo** que valide SOLO Bearer header y registrarlo en los grupos como **capa de defensa adicional** compatible con el trait. No usar `auth:wellcore` por la ambigüedad de fuentes.

### 1.5 ⚠️ FASE 2.13 "Mover videos a disco privado sin endpoint de serve" — **ORDEN INVERTIDO**
- El plan original pide cambiar `store('public')` → `store('private')` primero. Si se hace así, los videos subidos a partir del cambio serán inaccesibles.
- **Acción correcta (Fase C):** (1) crear endpoint `GET /api/v/client/checkins/{id}/stream` autenticado que retorne `Storage::disk('private')->response(...)`, (2) actualizar frontend Vue para usar ese endpoint, (3) **solo entonces** cambiar el disco del upload, (4) migrar (copiar) archivos existentes con comando artisan.

### 1.6 ❌ HIGH-BUG-05 "OptimizeImages --force no existe" — **FALSO POSITIVO MENOR**
- **Evidencia:** `OptimizeImages.php:18-22` define `--dry-run`, `--path`, `--max-width`, `--quality`. No tiene `--force`, correcto. El error del log ocurrió porque algún script externo lo invocaba mal. Basta con arreglar el invocador (probablemente un alias local), **no el command**.

### 1.7 ❌ Reescribir CSP sin nonces (quitar unsafe-inline/eval) — **ALTO RIESGO para features intocables**
- **Evidencia:** Livewire 3 inyecta scripts inline en cada request (`window.livewire_app_url`, component data). Alpine.js construye directivas `x-data` dinámicamente → requiere `unsafe-eval`. Wompi checkout inyecta iframe + scripts de `checkout.wompi.co`.
- **Acción pragmática:** Endurecer CSP sin eliminar unsafe-* por ahora. Implementar nonces en una fase D futura con staging + E2E completo.

---

## 2. Hallazgos Confirmados y Priorizados

Orden por impacto real (UX + seguridad + estabilidad):

### 🔴 CRÍTICOS INMEDIATOS

| ID | Archivo:línea | Impacto |
|----|---------------|---------|
| **C1** Contraseña hardcodeada `WellCore2026!` | `AdminController.php:1700` | Cualquiera con acceso al repo conoce cred default de clientes |
| **C2** `admin_token` del POST body aceptado sin validar rol | `EnsureAuthenticated.php:65-71` | **Escalación privilegios**: cliente envía su token como `admin_token` → queda logueado como admin a ojos de WellCoreGuard |
| **C3** Migración `page_visits` con FK incompatible (`bigint unsigned` vs `clients.id INT UNSIGNED`) | `2026_03_27_000001_create_page_visits_table.php:16,42` | La tabla **no existe en DB** → cada request falla SQL 1146 en `TrackUtmParameters:99`, `InscriptionForm:207`, `WompiService:451` |
| **C4** `.mcp.json` configura `laravel-boost` inexistente | `.mcp.json:3-8` | 30+ errores "boost namespace not defined" en logs (fácil de limpiar) |
| **C5** `APP_DEBUG=true` en `.env` | `.env` | Stack traces expuestos si `.env` llega a prod (solo afecta dev ahora) |
| **C6** `ChatMessage` insert en test falla por columna `content` inexistente | `tests/Feature/ChatbotTest.php:28-29` vs migración de `chat_messages` | Test fail + schema desincronizado |

### 🟠 ALTOS

| ID | Archivo:línea | Impacto |
|----|---------------|---------|
| **A1** `CACHE_STORE=array` | `.env` | `Cache::remember` inefectivo, queries redundantes |
| **A2** `SESSION_DRIVER=file` | `.env` | Sesión no compartida entre instancias; OK en un solo servidor actual |
| **A3** `SESSION_ENCRYPT=false` | `config/session.php` default | Session hijacking si leen cookie o filesystem |
| **A4** Rate limiter API solo por IP | `AppServiceProvider.php:41-43` | Usuario legítimo en NAT comparte cuota |
| **A5** Video check-ins en disco `public` | `VideoCheckinUpload.php:65` | URLs directas adivinables |
| **A6** `fitcron_slug` NOT NULL pero inserts sin slug | migración `ejercicio_videos` + seeder | SQL 1048 en imports |
| **A7** Coach-features blade `$slots->count()` colisión Livewire 3 | `coach-features.blade.php:376` + `CoachFeatures.php:699-710` | Posible error render en contextos con SupportSlots |
| **A8** Wompi webhook + updates sin `DB::transaction` | `WompiService.php:346,384,418,451` | Estado inconsistente si falla a mitad del proceso |
| **A9** CSP incluye `X-XSS-Protection` deprecado | `ContentSecurityPolicy.php:33` | Introduce XS-Leaks en navegadores modernos |
| **A10** `body_html` raw en Academia | `academia.blade.php:291` | XSS si el contenido es user-controlled (verificar origen) |
| **A11** Commands de Mail sin try-catch | `BehavioralTriggersCommand.php:79`, `AutoRenewalCommand.php:46`, `WeeklySummaryCommand.php:63` | Comando entero crashea si Mailjet falla |
| **A12** Sin `config/cors.php` | N/A | Laravel default usa `allowed_origins: ['*']` si no hay config |

### 🟡 MEDIOS

| ID | Archivo:línea | Impacto |
|----|---------------|---------|
| **M1** `aiNutritionHistory` histórico por OPCache stale | `routes/api.php:140` (método existe en `SocialController:1346`) | Regresión posible si deploy no limpia caché |
| **M2** Dependencias npm/composer con CVEs | `composer.json`, `package.json` | Requiere `composer audit` / `npm audit` para confirmar estado actual |
| **M3** Comparaciones loose (`==`) en Analytics/HabitTracker | `Analytics.php:518`, `HabitTracker.php:75,136` | Bugs tipo `"0" == false` |
| **M4** URLs hardcodeadas en Mail classes | `GiftPlanInvitation:59`, `NewCoachCredentials:41`, `PlanInvitation:53` | Config duplicada |
| **M5** Commands con PII hardcodeada | `FixRisePrograms.php:38…`, `DiagnoseClients.php:19-24` | Privacidad + no reutilizable |
| **M6** Casting inconsistente de `amount` en Payment | `Payment.php:39` vs `WompiService::amount_in_cents` | Ambigüedad pesos vs centavos |
| **M7** Memory limit 128M insuficiente para tests | `php.ini` | Suite completa crashea al final |
| **M8** `habit_type` enum sin casting en modelo | `HabitLog.php` | Data truncation silenciosa si valor inválido |
| **M9** `reset-password` sin rate limit | `routes/api.php:47` | Password reset spam |

### 🟢 BAJOS / HOUSEKEEPING

| ID | Tarea |
|----|-------|
| **B1** Agregar `public/robots.txt` bloqueando rutas privadas |
| **B2** Documentar `.env.example` con todas las variables usadas |
| **B3** Actualizar scripts/aliases que usan `route:list --columns` |
| **B4** Añadir `php artisan optimize:clear` + `composer dump-autoload` al pipeline deploy (previene regresión tipo M1) |
| **B5** Archivar commands one-off en `app/Console/Commands/archive/` |

---

## 3. Fases de Implementación

> **Regla transversal:** git commit antes de cada fase, `php artisan test` entre fases, push a main solo tras verificación (regla de memoria `feedback_deploy_workflow_v2.md`).

### Fase A — Quick Wins (0–2 horas, riesgo cero)

Tareas no invasivas que eliminan ruido y deuda trivial.

| # | Tarea | Archivos | Criterio de éxito | Rollback |
|---|-------|----------|-------------------|----------|
| A.1 | Eliminar `laravel-boost` de `.mcp.json` | `.mcp.json:3-8` | `grep boost:mcp .` → 0 (excepto histórico). Sin más errores en logs. | `git revert` del cambio a `.mcp.json` |
| A.2 | Agregar `public/robots.txt` | nuevo `public/robots.txt` | Curl `https://wellcorefitness.com/robots.txt` responde 200 con `Disallow: /client/`, `/admin/`, `/coach/`, `/rise/` | `rm public/robots.txt` |
| A.3 | Documentar `.env.example` | `.env.example` | Todas las vars que `config/wellcore.php` y código usan están listadas con valores dummy | `git revert` |
| A.4 | Remover header `X-XSS-Protection` | `ContentSecurityPolicy.php:33` | Test `SecurityHeadersTest` no espera más este header. Curl no retorna `X-XSS-Protection` | `git revert` |
| A.5 | Agregar `optimize:clear` + `composer dump-autoload` al script `silvia-gitpull-load` | EasyPanel script | Tras next gitpull, `Route::list` refresca sin reiniciar contenedor | Editar script en EasyPanel revirtiendo los dos comandos |
| A.6 | Actualizar scripts con `route:list --columns` → sin `--columns` | Buscar `route:list --columns` en `.claude/`, `.vscode/`, `bin/` | Comando ejecuta sin error "option does not exist" | `git revert` |
| A.7 | Archivar commands one-off con PII a `app/Console/Commands/archive/` | `FixRisePrograms.php`, `DumpSilviaPlan.php`, `DiagnoseClients.php`, `FixDanielCiclo.php` | `php artisan list` no los muestra. Tests pasan. | `git mv archive/X.php app/Console/Commands/X.php` |
| A.8 | Agregar `OptimizeImages --force` opción | `OptimizeImages.php:18-22` añadir `{--force : Sobrescribe archivos existentes}` + lógica | `php artisan wellcore:optimize-images --force --dry-run` no falla | `git revert` |
| A.9 | Fix `ChatMessage` column `content` (o columna real) | Leer migración real de `chat_messages` y alinear modelo/test. No ALTER DB. | `php artisan test tests/Feature/ChatbotTest.php` verde | `git revert` |

**Tiempo estimado: 1h 30min. Sin staging. Sin downtime.**

---

### Fase B — Estabilidad (2–4 horas, bajo riesgo)

Bug fixes que no cambian arquitectura ni schema.

| # | Tarea | Archivos | Criterio | Rollback |
|---|-------|----------|----------|----------|
| B.1 | Renombrar `$slots` → `$availabilitySlots` en coach-features | `CoachFeatures.php:699-710`, `coach-features.blade.php:376,379,383` | Vista de disponibilidad coach renderiza sin error. Livewire test pasa. | `git revert` |
| B.2 | Envolver `ExportService::export()` en try-catch con logging | `ExportService.php:175-210` | Export CSV con archivo problemático loggea error y responde 500 JSON controlado | `git revert` |
| B.3 | Envolver `Mail::queue()` en commands con try-catch | `BehavioralTriggersCommand.php:79`, `AutoRenewalCommand.php:46`, `WeeklySummaryCommand.php:63` | Simular fallo Mailjet (mock) → command continúa y loggea | `git revert` |
| B.4 | Fix `fitcron_slug` insert (validación en controller / seeder) | Buscar `EjercicioVideo::create` sin slug | Import ejercicios sin slug falla con 422 explícito (no 1048) | `git revert` |
| B.5 | Añadir casting de `habit_type` en modelo (string con validación en Enum app) | Crear `app/Enums/HabitType.php` con valores actuales de DB (`agua, sueno, entrenamiento, nutricion, suplementos, estres`), castear en `HabitLog.php` | Insertar habit_type inválido falla con ValueError controlado | `git revert` |
| B.6 | Fix comparaciones loose `===` donde aplique | `Analytics.php:518`, `CoachFeatures.php:661`, `HabitTracker.php:75,136` — revisar caso por caso | Test existente (o nuevo) cubre caso. No bulk replace. | `git revert` del cambio afectado |
| B.7 | Envolver `WompiService` webhook updates en `DB::transaction()` | `WompiService.php:340-460` agrupar `Payment::update`, `Client::update`, `PageVisit::update`, logging atómico | Simular excepción a mitad de webhook → rollback completo, estado consistente | `git revert` |
| B.8 | Fix casting `amount` en Payment: documentar que `amount` = pesos (COP), `amount_in_cents` = centavos para Wompi | `Payment.php` PHPDoc + `WompiService` comentario | Test: `Payment::first()->amount` siempre pesos | `git revert` |
| B.9 | Rate limit a `reset-password` | `routes/api.php:47` añadir `->middleware('throttle:login')` | 6 requests/min/IP rechazados. Test verde. | `git revert` |
| B.10 | Fix memory_limit testing en `phpunit.xml` (ini setting) | `phpunit.xml` agregar `<php><ini name="memory_limit" value="512M"/></php>` | Suite completa corre sin fatal error | Revertir xml |

**Tiempo estimado: 3h 30min. Sin staging (todo reversible sin datos afectados).**

---

### Fase C — Seguridad Pragmática (4–8 horas, riesgo medio)

Fixes de seguridad que NO rompen features existentes. Requieren tests E2E locales.

| # | Tarea | Archivos | Criterio | Rollback |
|---|-------|----------|----------|----------|
| C.1 | **C2 escalación privilegios**: validar rol del `admin_token` en body | `EnsureAuthenticated.php:65-71` → antes de `session(['wc_token' => $bodyToken])`, hacer `$t = AuthToken::where('token', $bodyToken)->where('expires_at','>',now())->first(); if(!$t || $t->user_type !== 'admin') return next($request);` | Cliente enviando admin_token con su propio token no logueado como admin. Impersonación admin normal sigue funcionando. | `git revert` |
| C.2 | **C1 password hardcoded**: eliminar `bcrypt('WellCore2026!')` default | `AdminController.php:1700` → generar password aleatorio 16 chars con `Str::random(16)`, guardarla hasheada, **enviarla por email al cliente** vía Mailjet con fallback a retorno JSON al admin creador | Inscripción convertida → cliente recibe credenciales por mail, o admin las ve en respuesta si mail falla. Password conocido ya no viene de código. | `git revert` (password queda sin cambiar en DB; clientes previos siguen con `WellCore2026!`, hay que rotar manualmente tras aprobar fix) |
| C.3 | Crear migración correctiva para `page_visits` compatible | Nueva migración `2026_04_24_000001_fix_page_visits_client_id_type.php` que haga: (1) si tabla no existe, la crea con `$table->unsignedInteger('client_id')->nullable()` (tipo INT), (2) si existe, `ALTER TABLE page_visits MODIFY client_id INT UNSIGNED NULL` | `php artisan migrate` completa sin error. `DESCRIBE page_visits` muestra `client_id INT UNSIGNED`. FK añade correctamente. | `php artisan migrate:rollback` |
| C.4 | Rate limiter API por user_id∥ip | `AppServiceProvider.php:41-43` → `$key = optional($request->user())->id ?? $request->ip();` | Dos usuarios detrás de misma IP tienen cuotas independientes cuando están logueados | `git revert` |
| C.5 | Crear `config/cors.php` con allowed_origins explícitos | nuevo `config/cors.php` con `['wellcore-laravel.test', 'https://wellcorefitness.com', 'https://www.wellcorefitness.com']` y paths `['api/*']` | CORS preflight OPTIONS desde origen no autorizado → 403 | `git rm config/cors.php` |
| C.6 | `SESSION_ENCRYPT=true` | `.env` en dev, luego `.env.production` template | Login continúa funcionando en fresh browser. Tests auth verdes. | Cambiar a `false` en `.env` |
| C.7 | File uploads: reforzar validación mime real | `SocialController::uploadPhoto`, `RiseController::uploadPhoto`, `CoachPlanTicketController::uploadAttachment`, `CoachBrandController::uploadLogo`, `VideoCheckinUpload.php` | Cada upload usa `->mimes([...])` + `->extensions([...])` + `Mime type real con Symfony\HttpFoundation\File::guessMimeType()`. Max size explícito. | `git revert` |
| C.8 | IDOR check en endpoints con `{id}` | Buscar controllers API con `$id` que retornan modelos sin `where('client_id', $authedClient->id)`. Principal: `SocialController`, `AdminController` donde corresponda a client. | Test: cliente A no puede leer recurso de cliente B → 403. Admin sí puede (por rol). | `git revert` |
| C.9 | `body_html` Academia: HTML Purifier con allowlist | `academia.blade.php:291` + instalar `mews/purifier` o usar `strip_tags($x, '<p><br><strong><em><ul><li><h1><h2><h3><a><code><pre><blockquote>')` | Contenido con `<script>` no ejecuta. Contenido legítimo renderiza. | `git revert` + `composer remove` si purifier |
| C.10 | Migrar video check-ins a disco privado (**orden crítico**): (1) endpoint stream, (2) frontend Vue, (3) flip disco, (4) migrar archivos existentes | `SocialController` (nuevo `streamCheckin`), `VideoCheckinUpload.php:65`, Vue component, nuevo command `php artisan checkins:migrate-public-to-private` | Video nuevo NO accesible por URL pública. Se accede solo via `/api/v/client/checkins/{id}/stream` autenticado. Videos viejos migrados. | Paso a paso inverso: (a) restaurar archivos, (b) flip disco a `public`, (c) revert Vue, (d) remover endpoint |
| C.11 | Crear middleware `ApiBearerAuth` (defensa adicional, no reemplazo de trait) | nuevo `app/Http/Middleware/ApiBearerAuth.php` que valide Bearer y añada `$request->setUserResolver(...)`. Registrar en grupos `v/client`, `v/coach`, `v/admin`, `v/rise` en `routes/api.php` | Request sin Bearer → 401 JSON inmediato (antes de llegar a controller). Con Bearer válido → pasa al controller como antes. | Remover middleware del grupo |

**Tiempo estimado: 6 horas. Validación local + smoke en prod tras deploy, sin requerir staging completo.**

---

### Fase D — Arquitectura (8–16 horas, requiere staging + ventana de mantenimiento)

Cambios que afectan sesión activa, CSP dura, o migración de cache. **NO ejecutar sin backup de DB y ventana anunciada.**

> ⚠️ **Warning downtime:** Tareas D.1, D.2, D.4 invalidan sesiones → usuarios deslogueados. Ejecutar en ventana 02:00–04:00 COT.

| # | Tarea | Archivos | Criterio | Rollback | Downtime |
|---|-------|----------|----------|----------|----------|
| D.1 | Migrar `CACHE_STORE` → `redis` (provisionar Redis en EasyPanel primero) | Nueva instancia Redis EasyPanel, `.env` prod `CACHE_STORE=redis`, `REDIS_HOST=...` | `Cache::put/get` persiste entre requests. `Cache::remember('x',60,fn()=>1)` funciona. | `.env` → `CACHE_STORE=file`, `php artisan cache:clear` | 0 min (cache nuevo vacío, se repobla on-demand) |
| D.2 | Migrar `SESSION_DRIVER` → `database` con tabla `sessions` | `php artisan session:table` + migrate + `.env` prod `SESSION_DRIVER=database` | Login funciona tras cambio. Tabla `sessions` se popula. | `.env` → `SESSION_DRIVER=file`. Migración de tabla `sessions` es aditiva (no rollback DB). | Ventana: usuarios relogueados |
| D.3 | Auditar índices críticos DB (migration aditiva con `ALGORITHM=INPLACE, LOCK=NONE`) | Nueva migración creando índices faltantes en `auth_tokens(token)`, `auth_tokens(expires_at)`, `clients(email,status)`, `payments(client_id,status)`, `checkins(client_id,date)` — solo si no existen (`Schema::hasIndex`) | Query `EXPLAIN` muestra uso de índice. Dashboard admin <2s | Rollback drop solo índices nuevos | 0 min si INPLACE |
| D.4 | Endurecer CSP con nonces (experimental, E2E obligatorio) | `ContentSecurityPolicy.php`, blades que usan scripts inline, Alpine init, Livewire bootstrap, Wompi iframe | Flujo completo: login web, login SPA, checkout Wompi, chat Livewire, Alpine components, subida foto → todos funcionan con CSP sin `unsafe-inline` (dejar `unsafe-eval` por Alpine) | Revertir header CSP al anterior permisivo | Ventana: requiere staging con tests E2E Playwright |
| D.5 | `composer audit` + `npm audit fix` en rama separada | `composer.json`, `package-lock.json` | `composer audit` 0 high. `npm audit --production` 0 high. Build + dev corren. Smoke E2E pasa. | `git revert` lockfiles y reinstalar | 0 min con preview deploy |

**Tiempo estimado: 12 horas netas + ventana de mantenimiento.**

---

### Fase E — Optimización Post-Estabilidad (16+ horas, solo tras Fases A–D verdes)

Performance y limpieza arquitectónica.

| # | Tarea | Criterio |
|---|-------|----------|
| E.1 | Eager loading en AdminController endpoints que devuelven colecciones de clientes (N+1) | Query count por endpoint disminuye 50%+ via `with([...])` |
| E.2 | `Cache::remember` agregados admin (stats dashboard) TTL 60s | Dashboard admin carga <1.5s |
| E.3 | Normalizar URLs hardcodeadas en Mail classes → `config('wellcore.base_url')` | `GiftPlanInvitation`, `NewCoachCredentials`, `PlanInvitation`, `TerraService` |
| E.4 | Tests de auth (AuthFlow, TokenExpiry, Impersonation) | Cobertura auth >80% |
| E.5 | Tests API crítica (`ClientController::dashboard`, `AdminController::stats`, `TrainingController::workoutFlow`, `WompiService::handleWebhook`) | Smoke coverage en happy + sad paths |
| E.6 | `git filter-repo` para purgar emails de clientes commiteados en commands archivados | Requiere ventana: todos los devs deben re-clonar tras filter-repo |

---

## 4. Matriz de Riesgo Detallada

| # | Fase | Tarea | Riesgo UX | Riesgo DB | Riesgo Funcionalidad | Rollback |
|---|------|-------|-----------|-----------|---------------------|----------|
| A.1 | A | Remover laravel-boost | 🟢 | 🟢 | 🟢 | git revert |
| A.2 | A | robots.txt | 🟢 | 🟢 | 🟢 | rm |
| A.4 | A | Quitar X-XSS-Protection | 🟢 | 🟢 | 🟢 | git revert |
| B.1 | B | Renombrar $slots | 🟡 (vista coach cambia render internamente) | 🟢 | 🟡 | git revert |
| B.7 | B | DB::transaction Wompi | 🟢 | 🟡 (bloqueos más cortos esperados; verificar deadlock) | 🟡 | git revert |
| C.1 | C | Rol admin_token | 🟢 | 🟢 | 🟡 (probar impersonación real) | git revert |
| C.2 | C | Password aleatorio default | 🟡 (admin ve cred en pantalla) | 🟢 | 🟡 (depende Mail) | git revert + rotar manualmente |
| C.3 | C | Migración fix page_visits | 🟢 | 🟠 (ALTER COLUMN en tabla vacía = seguro) | 🟡 (TrackUtm empieza a escribir) | migrate:rollback |
| C.6 | C | SESSION_ENCRYPT=true | 🟡 (sesiones actuales invalidadas) | 🟢 | 🟡 | env revert |
| C.10 | C | Videos private | 🟡 (videos nuevos solo via endpoint) | 🟢 | 🟠 (si endpoint falla, videos inaccesibles) | flip disco + revertir Vue + restaurar archivos |
| C.11 | C | ApiBearerAuth middleware | 🟢 | 🟢 | 🟠 (si el middleware falla en un edge case, grupo entero queda 401) | remover del grupo |
| D.1 | D | Redis cache | 🟢 | 🟢 | 🔴 (si Redis down → 500 si hay Cache::remember que no cae a DB gracefully) | env CACHE_STORE=file |
| D.2 | D | Sessions DB | 🟠 (todos relogueados) | 🟡 (nueva tabla, aditiva) | 🟠 | env SESSION_DRIVER=file |
| D.4 | D | CSP hardened | 🔴 (si nonces mal → JS roto = app vacía) | 🟢 | 🔴 | revertir header |

### Tareas DESCARTADAS (no ejecutar)
| Tarea original | Razón |
|----------------|-------|
| "Quitar `token`, `role`, `plan`, `status`, `must_change_password` de `$fillable`" | 8+ endpoints dependen. Refactor exhaustivo requerido, beneficio nulo (no hay mass assign real). |
| "Agregar `auth:wellcore` a grupos API directamente" | Colisión con trait + guard multimodal. Preferir middleware `ApiBearerAuth` nuevo. |
| "Cambiar `{!! body_html !!}` → `{{ body_html }}`" | Rompe rendering HTML legítimo. Usar Purifier con allowlist. |
| "Modificar migración existente de `page_visits`" | Regla absoluta: nunca modificar migraciones ejecutadas. Crear una nueva que haga ALTER. |

---

## 5. Validaciones Obligatorias por Fase

### Antes de pasar de A → B
- [ ] `php artisan test` verde (incluyendo ChatbotTest)
- [ ] `php artisan optimize:clear && php artisan route:list` sin errores
- [ ] Logs laravel.log no muestran `boost namespace`
- [ ] Curl de `robots.txt` en staging retorna contenido esperado
- [ ] Commit + push + silvia-gitpull-load OK

### Antes de pasar de B → C
- [ ] Todos los tests de B verdes
- [ ] Memory 512M aplicado, suite completa corre sin fatal
- [ ] Simular webhook Wompi con fallo intermedio → rollback atómico observado en logs
- [ ] Coach disponibilidad renderiza (vista coach-features)

### Antes de pasar de C → D
- [ ] Login funciona con token Bearer válido (Vue SPA) + session (Livewire)
- [ ] Impersonación admin funciona; cliente intentando `admin_token` con su propio token es **rechazado** como admin
- [ ] `TrackUtmParameters` escribe en `page_visits` sin error SQL
- [ ] WompiService flujo completo (webhook + DB updates) atómico
- [ ] Password nuevo cliente: llega por email o está visible al admin en respuesta
- [ ] File uploads con extensión falsa (.jpg con bytes PHP) rechazados por mime real
- [ ] Video check-ins: subida nueva NO accesible por URL directa, sí por endpoint autenticado

### Antes de ejecutar D
- [ ] Backup completo DB `wellcore_fitness` con mysqldump
- [ ] Backup `.env` actual
- [ ] Redis provisioned y accesible (`php artisan tinker` → `Cache::put('test',1); Cache::get('test') === 1`)
- [ ] Staging con tests E2E Playwright verde (checkout Wompi, login, dashboard, subida foto)
- [ ] Ventana de mantenimiento anunciada a usuarios (mínimo 24h antes)

### Validación final global (tras D)
- [ ] `php artisan test` → todo verde, sin fatal
- [ ] `composer audit` → 0 vulnerabilidades high/critical
- [ ] `npm audit --production` → 0 vulnerabilidades high/critical
- [ ] Login/logout smooth en Chrome + Safari
- [ ] Dashboard admin <2s con 1000+ clientes
- [ ] Checkout Wompi completo (crear plan, pagar sandbox, webhook, asignar plan)
- [ ] Subida foto progreso + check-in video funcionan
- [ ] Impersonación admin funciona y cliente->admin rechazado
- [ ] Logs sin errores SQL 1146 (page_visits), sin 23000 (fitcron_slug), sin 01000 (habit_type), sin boost

---

## 6. Plan de Rollback por Escenario

### Escenario 1: Tras Fase A/B, usuario reporta error
- `git log --oneline -10` → identificar commit sospechoso
- `git revert <commit>` (nunca `reset --hard` en main)
- `git push`
- EasyPanel → `silvia-gitpull-load`
- Verificar user-flow afectado

### Escenario 2: Fase C.2 (password hardcoded removido), Mail no llega
- No revertir código (no ayuda a clientes ya creados sin conocer su password)
- Ejecutar artisan tinker en EasyPanel console:
  ```
  $c = Client::find($id); $temp = Str::random(12); $c->password_hash = Hash::make($temp); $c->must_change_password = true; $c->save(); echo "Temp: $temp";
  ```
- Enviar credencial temporal al cliente por WhatsApp (canal alterno)

### Escenario 3: Fase C.3 migración page_visits falla
- `php artisan migrate:rollback --step=1`
- Si la FK sigue rota, **NO borrar la tabla page_visits** si ya tiene datos
- Revisar `DESCRIBE clients` con mysqldump para confirmar tipo exacto
- Re-crear migración con tipo correcto

### Escenario 4: Fase C.6 (SESSION_ENCRYPT=true) bloquea login
- Verificar `APP_KEY` en `.env` no cambió
- Si login falla solo para sesiones viejas: **esperar**, se renovarán con re-login
- Si login falla para sesiones nuevas: revertir `SESSION_ENCRYPT=false`, investigar cipher

### Escenario 5: Fase D.1 (Redis) causa errores 500
- `.env` → `CACHE_STORE=file`
- `php artisan cache:clear`
- `php artisan config:clear`
- Reiniciar PHP-FPM en container: artisan script `restart-php`
- Investigar conectividad Redis (credentials, network)

### Escenario 6: Fase D.2 (Sessions DB) bloquea login masivo
- `.env` → `SESSION_DRIVER=file`
- No borrar tabla `sessions` (queda aditiva, sin tocar)
- Usuarios vuelven a relogearse en file

### Escenario 7: Fase D.4 (CSP hardened) rompe checkout Wompi / dashboard
- Revertir `ContentSecurityPolicy.php` al policy anterior con `unsafe-inline`/`unsafe-eval`
- Abrir DevTools Console para ver qué directiva bloqueó
- Añadir nonce/hash específico antes de reintentar

### Escenario 8: Fase D.5 (`npm audit fix`) rompe build
- `git checkout package.json package-lock.json node_modules`
- `npm ci`
- `npm run build`
- Actualizar paquetes uno por uno en rama separada

---

## 7. Dependencias entre Tareas

```
A.1 (remover boost)  ─────→  limpieza logs ─────→  todos los siguientes más fáciles de debuggear
A.5 (pipeline clear)  ─────→  prerequisito para evitar regresiones OPCache en cualquier deploy futuro

B.7 (tx Wompi)  depende de  →  nada (cambio interno)
B.4 (fitcron)   depende de  →  tests verdes (A.9)

C.1 (admin_token rol)  CRÍTICO ANTES DE  →  cualquier cambio en EnsureAuthenticated
C.2 (password aleatorio)  depende de  →  Mailjet accessible (probar en A con envío de test)
C.3 (migración page_visits)  CRÍTICO ANTES DE  →  C.8 (IDOR — porque TrackUtm debe estar escribiendo)
C.10 (videos private)  ORDEN ESTRICTO:
   (a) endpoint stream
   (b) Vue frontend usa endpoint
   (c) flip disco en upload
   (d) migrar archivos existentes
   CUALQUIER OTRO ORDEN ROMPE

D.1 (Redis)  depende de  →  C.6 (SESSION_ENCRYPT) NO; puede ser independiente
D.2 (Sessions DB)  depende de  →  D.1 (Redis) NO; pero idealmente se hacen juntas
D.4 (CSP hardened)  depende de  →  staging E2E Playwright (no existe actualmente → crear en E)

E.4, E.5 (tests auth/API)  deben preceder  →  cualquier cambio futuro en auth/API
```

### Ruta crítica (no paralelizable)
```
A.1 + A.5  →  B.1..B.10  →  C.1  →  C.3  →  C.11  →  D.1  →  D.2  →  D.4  →  E.*
```

### Paralelizables
- A.2, A.3, A.4, A.6, A.7, A.8, A.9 (todas en Fase A)
- B.2, B.3, B.5, B.6, B.8, B.9, B.10
- C.4, C.5, C.7, C.8, C.9 (entre sí)

---

## Validación final del plan (self-review)

### Checklist
- [x] Cada tarea tiene archivo + línea aproximada + criterio medible + rollback claro
- [x] Fase A y B **no requieren staging** (todas reversibles sin datos afectados)
- [x] Fase D tiene **warnings explícitos de downtime** (D.1, D.2, D.4)
- [x] No hay dependencias circulares (verificado en sección 7)
- [x] Total horas estimadas: A (1.5h) + B (3.5h) + C (6h) + D (12h + ventana) + E (16h+) = **~40h de trabajo puro** + ventanas de mantenimiento
- [x] Tareas descartadas documentadas con razón (sección 4)
- [x] Regla `NUNCA modificar migración existente` respetada (C.3 crea nueva, no modifica `2026_03_27_000001`)
- [x] Regla `NUNCA tocar $fillable` respetada (sección 1.3 desarma la recomendación)
- [x] Regla `deploy = build local + commit public/build + push + silvia-gitpull-load` implícita en todas las fases
- [x] No se aplica `auth:wellcore` a grupos API (se crea `ApiBearerAuth` en su lugar — C.11)

### Observaciones arquitectónicas
1. La **fortaleza del stack actual es el trait `AuthenticatesVueRequests`**. No romper ese contrato.
2. La **debilidad real de seguridad** que nadie había identificado con severidad correcta: **C.1 (admin_token escalation)**. Era "MED" en el plan original, es **CRÍTICO** tras la validación del código.
3. La **regresión más probable tras deploys futuros** es OPCache stale (caso aiNutritionHistory). A.5 la previene permanentemente.
4. El **trabajo inútil más alto-costo** del plan original era "quitar campos de $fillable". Hubiera requerido reescribir 20+ endpoints para cero ganancia real.

---

*Plan revisado, rectificado y validado contra código fuente 2026-04-23. Listo para ejecución secuencial A → B → C → D → E con checkpoints de validación obligatorios entre fases.*
