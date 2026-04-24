# ⚠️ ANÁLISIS DE RIESGO — Plan de Remediación WellCore Laravel

> **Propósito:** Revisar exhaustivamente cada tarea del plan de auditoría para garantizar que NINGUNA dañe producción, borre datos, cambie estilos, o rompa funcionalidad existente.
>
> **Fecha:** 2026-04-23  
> **Auditor:** Kimi Code CLI  
> **Contexto crítico:** App en patrón Strangler Fig (comparte DB con vanilla PHP). NO se permiten migraciones destructivas.

---

## 🚦 SISTEMA DE CLASIFICACIÓN DE RIESGO

| Icono | Nivel | Significado |
|-------|-------|-------------|
| 🟢 | **Seguro** | Sin riesgo de dañar producción. Puede ejecutarse directamente. |
| 🟡 | **Precaución** | Riesgo bajo-moderado. Requiere verificación específica antes/after. |
| 🔴 | **ALTO RIESGO** | Puede romper producción, borrar datos, o bloquear usuarios. Requiere backup + prueba en staging. |
| ⚫ | **NO EJECUTAR** | Demasiado riesgoso para este proyecto (Strangler Fig, DB compartida, legacy). |

---

## 🔧 FASE 1: ESTABILIDAD CRÍTICA

| # | Tarea | Riesgo | Análisis Detallado | Mitigación Recomendada |
|---|-------|--------|-------------------|----------------------|
| 1.1 | Fix middleware auth en rutas API | 🔴 **ALTO** | Agregar `'auth:wellcore'` a `v/client`, `v/coach`, `v/admin` en `routes/api.php` parece lógico, pero el sistema actual usa el trait `AuthenticatesVueRequests` que resuelve el token **solo del Bearer header**. El middleware `auth:wellcore` (WellCoreGuard) resuelve de múltiples fuentes (session, cookie, admin_token body). Podría haber edge cases donde: (a) el Vue SPA hace requests sin Bearer pero con cookie de vanilla app, (b) el middleware `auth` y el trait hacen doble validación causando loops o errores 401 inesperados, (c) rutas públicas dentro de estos grupos queden bloqueadas. | **NO agregar `auth:wellcore` globalmente a los grupos de rutas.** En su lugar: (1) Crear un middleware dedicado `ApiAuthenticate` que valide SOLO Bearer token y retorne 401 JSON. (2) Aplicarlo grupo por grupo. (3) Probar exhaustivamente login/logout en Vue SPA. (4) Verificar que impersonación admin sigue funcionando. |
| 1.2 | Eliminar contraseña hardcodeada | 🟡 **Precaución** | Cambiar `bcrypt('WellCore2026!')` por generación aleatoria + email es correcto, pero si el envío de email falla, el cliente queda sin saber su contraseña. También afecta flujo de inscripciones nuevas. | Implementar con fallback: si email falla, mostrar contraseña temporal en pantalla al admin que crea la inscripción. O mejor: forzar cambio de contraseña en primer login con token mágico. Probar en staging primero. |
| 1.3 | Fix ruta aiNutritionHistory + caché | 🟢 **Seguro** | Agregar `php artisan optimize:clear` al pipeline de deploy no afecta runtime. Es una mejora de proceso. | Ejecutar directamente. Asegurar que el pipeline CI/CD incluya este paso. |
| 1.4 | Ejecutar migración page_visits | 🟡 **Precaución** | La migración existe en `database/migrations/` pero nunca corrió. Según AGENTS.md: "DO NOT create destructive migrations". Ejecutar `php artisan migrate` para UNA tabla nueva es seguro, **PERO** la migración tiene una FK a `clients(id)` que en el log mostró incompatibilidad de tipos. Si `clients.id` es `INT` y `page_visits.client_id` se define como `BIGINT UNSIGNED`, la migración fallará. | **Antes de ejecutar:** (1) Verificar tipo exacto de `clients.id` en MySQL: `DESCRIBE clients;`. (2) Verificar que la migración use el MISMO tipo. (3) Ejecutar en staging primero. (4) Si hay datos legacy en vanilla PHP que dependan de `clients.id`, confirmar que el tipo coincide. |
| 1.5 | Fix coach-features.blade.php SlotProxy | 🟢 **Seguro** | Renombrar `$slots` → `$availabilitySlots` es un refactor local de una vista y su controller. No afecta DB ni otros componentes. | Ejecutar directamente. Probar la vista de coach después. |
| 1.6 | Fix relación plan en Payment | 🟡 **Precaución** | No se encontró ningún `with('plan')` o `load('plan')` en los controllers API. El error podría venir de un Livewire component o de código que ya fue corregido. Crear un método `plan()` relación en `Payment.php` es técnicamente seguro, pero si `plan` ya es una propiedad/columna (enum cast), crear un método con el mismo nombre podría causar conflictos. | **Opción A (segura):** Buscar el origen exacto del error en logs. Si no se reproduce, documentar y monitorear. **Opción B:** Si se necesita relación, usar nombre diferente como `assignedPlan()` para evitar conflicto con la columna `plan`. |
| 1.7 | Aumentar memory_limit | 🟡 **Precaución** | Cambiar `memory_limit` en `php.ini` es seguro en general, pero en shared hosting o con restricciones del proveedor cloud podría no ser aplicable. Además, si el problema es un leak de memoria real (ej. carga de imágenes grandes), aumentar el límite solo lo oculta. | Aumentar a `512M` en `.user.ini` (nivel de proyecto). Monitorear si el consumo sigue creciendo. Si sí, investigar el leak real (probablemente en procesamiento de imágenes o export CSV). |
| 1.8 | Fix migración FK incompatible | 🔴 **ALTO** | Modificar una migración EXISTENTE que ya fue ejecutada en algún entorno podría causar inconsistencias. Si la migración ya corrió en staging/dev pero falló en prod, modificarla requeriría `php artisan migrate:rollback` o edición manual de la tabla `migrations`. En DB compartida con vanilla PHP, tocar migraciones es peligroso. | **NO modificar migraciones ya existentes.** En su lugar: (1) Crear una NUEVA migración que altere `page_visits.client_id` al tipo correcto. (2) O si la tabla no existe aún, eliminar la migración vieja y crear una nueva corregida. (3) Verificar en staging ANTES de producción. |
| 1.9 | Fix integrity constraint fitcron_slug | 🟡 **Precaución** | Hacer `fitcron_slug` nullable es un `ALTER TABLE`. En DB compartida con vanilla PHP, esto podría afectar la app vanilla si también usa esta tabla/columna. | **Verificar primero:** ¿La app vanilla PHP usa `ejercicio_videos`? Si sí, consultar con el equipo de vanilla antes de alterar. Alternativa más segura: agregar validación en el controller Laravel para rechazar inserts sin `fitcron_slug` (sin tocar DB). |
| 1.10 | Fix data truncated habit_type | 🟢 **Seguro** | Es un fix de validación en controller/enum. No toca DB. | Ejecutar directamente. Verificar que el enum `HabitType` acepte `'entrenamiento'`. |

**⚠️ Riesgo acumulado Fase 1:** Medio-Alto. Las tareas 1.1, 1.8 y 1.9 pueden romper auth o DB.

---

## 🛡️ FASE 2: SEGURIDAD

| # | Tarea | Riesgo | Análisis Detallado | Mitigación Recomendada |
|---|-------|--------|-------------------|----------------------|
| 2.1 | Actualizar dependencias vulnerables | 🟡 **Precaución** | `npm audit fix` y `composer update` pueden introducir breaking changes. Vite 8.0.0-8.0.4 tiene vulnerabilidades de path traversal. `npm audit fix` podría actualizar a Vite 8.0.5+ que podría tener cambios en la API de plugins o configuración. | (1) Ejecutar en rama separada. (2) Correr `npm run build` y verificar que no hay errores. (3) Probar HMR (`npm run dev`). (4) Ejecutar tests de frontend si existen. (5) `composer update` en staging primero. |
| 2.2 | Crear config CORS explícita | 🟢 **Seguro** | Crear `config/cors.php` con `allowed_origins` restringidos es puramente aditivo. Laravel sin config CORS usa defaults permisivos. Agregar config restringe, no expande. | Ejecutar directamente. Asegurar que `wellcore-laravel.test` y dominios de prod estén en la lista. |
| 2.3 | Harden CSP headers | 🔴 **ALTO** | Eliminar `unsafe-inline` y `unsafe-eval` de CSP puede romper COMPLETAMENTE la aplicación si: (a) Alpine.js usa `eval()` internamente, (b) Livewire 3 inline scripts dependen de `unsafe-inline`, (c) Vue 3 runtime usa `new Function()` que requiere `unsafe-eval`, (d) Wompi checkout inyecta scripts inline. | **NO eliminar `unsafe-inline` / `unsafe-eval` sin pruebas exhaustivas.** Estrategia segura: (1) Implementar nonces para scripts inline de Livewire/Blade. (2) Verificar si Wompi checkout funciona con nonces. (3) Probar TODO el flujo de compra. (4) Alpine.js sin `unsafe-eval` requiere ` Alpine.buildSelector() ` o similar. **Esta tarea debe hacerse en staging con tests E2E.** |
| 2.4 | Remover X-XSS-Protection deprecado | 🟢 **Seguro** | Eliminar un header HTTP legacy. No afecta funcionalidad. | Ejecutar directamente. |
| 2.5 | Encriptar sesiones | 🔴 **ALTO** | Cambiar `SESSION_ENCRYPT=false` a `true` invalida TODAS las sesiones existentes. Todos los usuarios logueados serán deslogueados inmediatamente. En producción, esto es una interrupción de servicio. Además, si hay algún bug en la encriptación (key incorrecta, cipher no soportado), los usuarios no podrán loguearse. | (1) Ejecutar en horario de bajo tráfico. (2) Avisar a usuarios con mantenimiento programado. (3) Verificar que `APP_KEY` está correctamente configurado y tiene 32 chars. (4) Probar login/logout completo en staging con `SESSION_ENCRYPT=true`. (5) Tener plan de rollback rápido. |
| 2.6 | Aplicar CSP middleware globalmente | 🟡 **Precaución** | El middleware `ContentSecurityPolicy` YA está registrado en `$middleware->web(append: [...])`. Los tests fallan porque probablemente la ruta `/` no está en el grupo `web` o hay algún otro problema. Aplicarlo "globalmente" a todas las rutas podría afectar rutas API (que no necesitan CSP) o causar headers duplicados. | **No tocar el registro del middleware.** En su lugar: (1) Depurar por qué `SecurityHeadersTest` falla para `/`. (2) Verificar que la ruta `/` pasa por middleware web. (3) Corregir el test si el problema es del test, no del middleware. |
| 2.7 | Auditar mass assignment | 🔴 **ALTO** | Cambiar `$fillable` en modelos es EXTREMADAMENTE peligroso si no se revisa TODOS los usos de `Model::create()` y `$model->update()`. Ejemplos concretos encontrados: <br>- `AuthToken::create([...])` se usa en 6+ lugares (`AuthController`, `Login.php`, `ImpersonateController`, `GoogleAuthController`, `CoachImpersonateController`, `CoachController`) con `token` explícito. Si se quita `token` de `$fillable`, TODO el sistema de autenticación se ROMPE. <br>- `Admin::create` en `AdminCoachManagementController` pasa array manual, pero si se quita `role` de `$fillable`, la creación de coaches falla. <br>- `Client::create` en `AdminController.php:1695` pasa `plan`, `status`, `password_hash`. Si se quitan de `$fillable`, la conversión de inscripciones falla. | **NO tocar `$fillable` sin un refactor exhaustivo.** Estrategia segura: (1) Para cada modelo, listar TODOS los `create()` y `update()` en el codebase. (2) Si el array se construye manualmente desde validated data, es seguro. (3) Si se pasa `$request->all()` o `$request->validated()` directo, es vulnerable. (4) Mejor alternativa: en los pocos endpoints que pasan input directo, usar `$model->fill($validated)->save()` con validación estricta en lugar de tocar `$fillable` globalmente. |
| 2.8 | Revisar file uploads | 🟢 **Seguro** | Agregar más validación (mime real, extensión forzada) es aditivo y no rompe nada existente. | Ejecutar directamente. No cambiar rutas de almacenamiento existentes sin migración de archivos. |
| 2.9 | Revisar IDOR en endpoints | 🟢 **Seguro** | Agregar verificaciones de ownership es aditivo. No rompe nada. | Ejecutar directamente. Solo agrega `where('client_id', $clientId)` o similar. |
| 2.10 | Sanitizar body_html de AcademyContent | 🟡 **Precaución** | Cambiar `{!! $selectedContent->body_html !!}` a `{{ }}` rompería el rendering de HTML en la academia si el contenido usa etiquetas HTML. Si se usa HTML Purifier, hay que asegurar que permite las etiquetas que se usan realmente (p, br, ul, li, strong, etc.). | Implementar HTML Purifier con lista blanca de etiquetas permitidas. Probar con contenido existente de la academia en staging. |
| 2.11 | Fix admin_token en body sin validación de rol | 🟢 **Seguro** | Agregar validación de `user_type = 'admin'` antes de aceptar `admin_token` del body es aditivo y más restrictivo. No rompe flujos legítimos. | Ejecutar directamente. Probar impersonación de admin después. |
| 2.12 | Fix rate limiter api por IP | 🟢 **Seguro** | Cambiar la clave del rate limiter de `$request->ip()` a `user_id ?? ip` es más justo y no afecta usuarios normales. Podría permitir ligeramente más requests por IP para usuarios no autenticados, pero el límite de 60/min sigue aplicando. | Ejecutar directamente. Monitorear logs de rate limiting después. |
| 2.13 | Mover video check-ins a disco privado | 🔴 **ALTO** | Cambiar `store(..., 'public')` a `store(..., 'private')` en `VideoCheckinUpload.php` hará que TODOS los videos NUEVOS no sean accesibles por URL directa. Los videos EXISTENTES en disco público seguirán accesibles. Además, si no se crea un endpoint para servir videos desde disco privado, los clientes no podrán ver sus propios videos. | **NO ejecutar sin crear el endpoint de serve primero.** Plan: (1) Crear endpoint `Route::get('/video-checkins/{id}/stream', ...)` con auth que sirva `Storage::disk('private')->response($path)`. (2) Actualizar frontend para usar el nuevo endpoint. (3) Migrar videos existentes de public a private (opcional). (4) Solo entonces cambiar el upload. |

**⚠️ Riesgo acumulado Fase 2:** Alto. Las tareas 2.3, 2.5, 2.7 y 2.13 pueden romper producción gravemente.

---

## 🧪 FASE 3: TESTS Y CALIDAD

| # | Tarea | Riesgo | Análisis Detallado | Mitigación Recomendada |
|---|-------|--------|-------------------|----------------------|
| 3.1 | Fix PaymentFlowTest | 🟢 **Seguro** | Corregir tests es seguro. No afecta producción. | Ejecutar directamente. |
| 3.2 | Fix SecurityHeadersTest | 🟢 **Seguro** | Corregir tests es seguro. No afecta producción. | Ejecutar directamente. |
| 3.3 | Fix PublicPagesTest::rise page loads | 🟢 **Seguro** | Corregir tests es seguro. No afecta producción. | Ejecutar directamente. |
| 3.4 | Fix fatal error en test suite | 🟢 **Seguro** | Aumentar memory limit para tests no afecta producción (a menos que se cambie el `php.ini` de producción). | Cambiar en `phpunit.xml` o `.env.testing`. No tocar `php.ini` de producción. |
| 3.5 | Agregar tests de autenticación | 🟢 **Seguro** | Agregar tests nuevos es seguro. | Ejecutar directamente. |
| 3.6 | Agregar tests de API crítica | 🟢 **Seguro** | Agregar tests nuevos es seguro. | Ejecutar directamente. |
| 3.7 | Fix ExportService try-catch | 🟢 **Seguro** | Agregar manejo de errores es aditivo y más seguro. No rompe nada. | Ejecutar directamente. |
| 3.8 | Fix Mail commands try-catch | 🟢 **Seguro** | Agregar manejo de errores en commands de mail es aditivo. Si el mail fallaba antes, el comando crasheaba. Ahora solo loggeará. | Ejecutar directamente. Probar que los commands siguen enviando mail correctamente. |
| 3.9 | Fix WompiService transacciones | 🟡 **Precaución** | Agrupar updates en `DB::transaction()` cambia el comportamiento de commit. Si antes un update parcial era "aceptable" (ej. Payment actualizado pero PaymentLog no), ahora todo falla o todo succeed. Esto es más correcto, pero si hay algún bug oculto donde un update legítimo fallaba silenciosamente, ahora se verá. | Ejecutar en staging primero. Probar webhooks de Wompi con transacciones de prueba. Monitorear logs de webhook después del deploy. |

**⚠️ Riesgo acumulado Fase 3:** Bajo. Solo 3.9 requiere precaución moderada.

---

## ⚡ FASE 4: PERFORMANCE

| # | Tarea | Riesgo | Análisis Detallado | Mitigación Recomendada |
|---|-------|--------|-------------------|----------------------|
| 4.1 | Migrar cache a Redis/Database | 🔴 **ALTO** | Cambiar `CACHE_STORE=array` a `redis` o `database` requiere que el servicio esté configurado y funcionando. Si Redis no está instalado o la config es incorrecta, TODAS las operaciones de caché fallarán. En producción, esto podría causar errores 500 masivos. | (1) Verificar que Redis está instalado y accesible: `php artisan cache:clear`. (2) Probar en staging primero. (3) Tener plan de rollback cambiando a `array` o `file`. (4) Monitorear logs de caché después del deploy. |
| 4.2 | Migrar sesiones a Database/Redis | 🔴 **ALTO** | Cambiar `SESSION_DRIVER=file` a `database` o `redis` invalida TODAS las sesiones activas. Todos los usuarios serán deslogueados. Además, `database` requiere que la tabla `sessions` exista (Laravel la crea con `php artisan session:table`). Si no existe, login falla. | (1) Crear migración de sessions si no existe: `php artisan session:table`. (2) Ejecutar en horario de bajo tráfico. (3) Avisar a usuarios. (4) Probar login/logout completo en staging. (5) Si usa Redis, verificar conectividad primero. |
| 4.3 | Auditar índices de DB | 🟡 **Precaución** | Crear índices en tablas grandes puede bloquear escritas mientras se construye. En MySQL, `ALTER TABLE ADD INDEX` en tablas con millones de registros puede tomar minutos y bloquear. La app vanilla PHP también escribe en estas tablas. | (1) Usar `ALGORITHM=INPLACE, LOCK=NONE` si MySQL 8+ lo soporta. (2) Ejecutar en horario de bajo tráfico. (3) Crear migraciones no destructivas con `if (!Schema::hasIndex(...))`. (4) Monitorear tiempo de ejecución. |
| 4.4 | Revisar N+1 en AdminController | 🟢 **Seguro** | Agregar `with([...])` es aditivo. Solo reduce queries. No rompe nada. | Ejecutar directamente. Verificar que los eager loads no carguen demasiados datos. |
| 4.5 | Cachear agregados admin | 🟢 **Seguro** | Agregar `Cache::remember` es aditivo. Si la caché falla, cae gracefully a DB. | Ejecutar directamente. Asegurar TTL razonable (30-60s para stats en tiempo real). |
| 4.6 | Archivar/Eliminar commands one-off con PII | 🟢 **Seguro** | Mover archivos a `archive/` no afecta producción. `git filter-repo` modifica historial git, lo cual es seguro si todos los devs re-clonan. | (1) Mover archivos a `archive/` primero. (2) Ejecutar `git filter-repo` en rama separada. (3) Avisar a todo el equipo que deben hacer `git clone` fresco. (4) NO ejecutar `git filter-repo` en la rama principal activa sin backup. |
| 4.7 | Normalizar URLs hardcodeadas | 🟢 **Seguro** | Mover URLs de código a `config()` no afecta funcionalidad si los valores default son los mismos. | Ejecutar directamente. Verificar que los mails siguen generando URLs correctas. |

**⚠️ Riesgo acumulado Fase 4:** Alto. Las tareas 4.1, 4.2 y 4.3 pueden causar downtime.

---

## 🧹 FASE 5: LIMPIEZA Y MANTENIBILIDAD

| # | Tarea | Riesgo | Análisis Detallado | Mitigación Recomendada |
|---|-------|--------|-------------------|----------------------|
| 5.1 | Eliminar ruido de logs: boost namespace | 🟢 **Seguro** | Buscar y eliminar el proceso que invoca `boost:mcp` es seguro. Podría ser un cron job, un MCP server, o una configuración de IDE. | `grep -r "boost:mcp" .` en todo el proyecto. Eliminar el proceso/config. No afecta código. |
| 5.2 | Fix OptimizeImages command signature | 🟢 **Seguro** | Agregar `--force` a un command de Artisan es aditivo. No rompe nada. | Ejecutar directamente. |
| 5.3 | Revisar `{!! !!}` en blades | 🟡 **Precaución** | Revisar es seguro, pero CAMBIAR `{!! !!}` a `{{ }}` en blades de FAQ o emails podría romper el rendering si el contenido espera HTML. Los emails (`plan-invitation.blade.php`) usan `{!! $plan['intro'] !!}` que probablemente contiene HTML formateado. | Solo revisar y documentar. NO cambiar a `{{ }}` sin verificar cada caso individual. Para `faq.blade.php`, si las traducciones NO contienen HTML de usuario, es seguro. |
| 5.4 | Documentar `.env` requerido | 🟢 **Seguro** | Editar `.env.example` no afecta producción. | Ejecutar directamente. |
| 5.5 | Agregar robots.txt | 🟢 **Seguro** | Agregar `public/robots.txt` es aditivo. No rompe nada. | Ejecutar directamente. |
| 5.6 | Fix comparaciones loose | 🟡 **Precaución** | Cambiar `==` a `===` puede cambiar comportamiento si había type juggling intencional. Ejemplo: `$lastMonthRevenue == 0` donde `$lastMonthRevenue` podría ser `"0"` o `0.0` o `null`. Con `===` solo coincide con `0` (int). | Cambiar uno por uno, ejecutando tests después de cada cambio. No hacer bulk replace. |
| 5.7 | Documentar casting de amount en Payment | 🟢 **Seguro** | Documentar es seguro. No afecta código. | Ejecutar directamente. |
| 5.8 | Fix route:list --columns en scripts | 🟢 **Seguro** | Actualizar scripts/documentación es seguro. | Ejecutar directamente. |
| 5.9 | Archivar commands one-off con PII | 🟢 **Seguro** | Mover archivos a `archive/` es seguro. | Ejecutar directamente. |
| 5.10 | Normalizar URLs hardcodeadas | 🟢 **Seguro** | Mover URLs a config es seguro. | Ejecutar directamente. |

**⚠️ Riesgo acumulado Fase 5:** Bajo. Solo 5.3 y 5.6 requieren precaución.

---

## 📋 RESUMEN EJECUTIVO DE RIESGOS

### 🔴 Tareas con ALTO RIESGO (requieren staging + backup)

| # | Tarea | Riesgo principal |
|---|-------|-----------------|
| **1.1** | Fix middleware auth en rutas API | Romper auth de Vue SPA, bloquear rutas legítimas |
| **1.8** | Fix migración FK incompatible | Corromper tabla de migraciones, inconsistencia DB |
| **2.3** | Harden CSP headers | Romper Alpine.js, Livewire, Wompi checkout, Vue 3 |
| **2.5** | Encriptar sesiones | Desloguear a TODOS los usuarios, posible bloqueo de login |
| **2.7** | Auditar mass assignment | ROMPER TODO el sistema de auth si se quita `token` de `$fillable` |
| **2.13** | Mover video check-ins a disco privado | Videos nuevos inaccesibles sin endpoint de serve |
| **4.1** | Migrar cache a Redis/Database | Errores 500 masivos si Redis/DB no está configurado |
| **4.2** | Migrar sesiones a Database/Redis | Desloguear a TODOS los usuarios, requerir tabla sessions |
| **4.3** | Auditar índices de DB | Bloquear escritas en tablas grandes durante minutos |

### ⚫ Tareas que NO se recomiendan para este proyecto

| # | Tarea | Motivo |
|---|-------|--------|
| **1.8** | Modificar migración existente de `page_visits` | En DB compartida con vanilla PHP, modificar migraciones es peligroso. Mejor crear migración nueva. |
| **2.7** | Quitar campos de `$fillable` en modelos | Demasiado riesgoso sin refactor exhaustivo de TODOS los `create()`/`update()`. Mejor validar en controllers. |

---

## ✅ RECOMENDACIONES PARA CLAUDE CODE OPUS

### 1. Orden de ejecución seguro

```
FASE 5 (Limpieza) → FASE 3 (Tests) → FASE 4 (Performance, solo 4.4/4.5/4.6/4.7)
→ FASE 2 (Seguridad baja: 2.1/2.2/2.4/2.6/2.8/2.9/2.10/2.11/2.12)
→ FASE 1 (Estabilidad: 1.3/1.5/1.6/1.7/1.9/1.10)
→ [STAGING] FASE 1 restante (1.1/1.2/1.4/1.8)
→ [STAGING] FASE 2 restante (2.3/2.5/2.7/2.13)
→ [STAGING] FASE 4 restante (4.1/4.2/4.3)
```

### 2. Verificaciones obligatorias antes de deploy a prod

Para CADA tarea 🔴:
- [ ] Backup de DB antes de cualquier migración
- [ ] Backup de `.env` antes de cambios de config
- [ ] Tests pasan en staging
- [ ] Login/logout funciona en staging
- [ ] Flujo de compra (Wompi) funciona en staging
- [ ] Impersonación admin funciona en staging
- [ ] Video check-ins funcionan en staging
- [ ] Dashboard admin carga en <3 segundos en staging

### 3. Tareas que pueden ejecutarse DIRECTAMENTE (🟢)

Todas las tareas marcadas 🟢 en las 5 fases pueden ejecutarse sin riesgo significativo.

### 4. Tareas que requieren VALIDACIÓN ESPECÍFICA

| Tarea | Validación requerida |
|-------|---------------------|
| 1.1 auth middleware | Probar TODOS los flujos: login web, login API, impersonación, cookie vanilla |
| 1.4 page_visits migrate | `DESCRIBE clients;` → comparar tipos con migración |
| 2.3 CSP harden | Probar checkout Wompi, Alpine.js interactivity, Livewire forms |
| 2.5 SESSION_ENCRYPT | Probar login con tokens existentes y tokens nuevos |
| 2.7 mass assignment | Hacer grep de TODOS los `Model::create` y `$model->update` antes de tocar `$fillable` |
| 2.13 video private | Crear endpoint de stream ANTES de cambiar disco |
| 4.1 cache Redis | `php artisan cache:clear` con Redis configurado |
| 4.2 sessions DB | `php artisan session:table` + migrate + probar login |
| 4.3 DB índices | Ejecutar en horario de bajo tráfico con `LOCK=NONE` |

---

*Fin del análisis de riesgo. Generado por Kimi Code CLI el 2026-04-23.*
