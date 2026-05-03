# Coach Contract Gate — Plan de Hardening Robusto

> **Status:** Approved for implementation
> **Date:** 2026-04-26
> **Origen:** Auditoría exhaustiva post-deploy con 6 agentes especializados (Security, Backend, Frontend, Database, Performance, Testing)
> **Deploy actual:** Coach Contract Gate v1.0 LIVE en producción desde commit `e9b409a2`

## Resumen ejecutivo

Tras el deploy a producción, una auditoría con 6 agentes especializados identificó **96 hallazgos** en el feature Coach Contract Gate. Los hallazgos cruzados (que múltiples auditores señalaron de forma independiente) son los más críticos.

| Severidad | Cantidad | Bloquea gate-on prod | Impacto principal |
|-----------|---------:|:--------------------:|-------------------|
| Crítico   | 15       | SÍ                   | Evidencia legal falsificable + race conditions + privilege escalation accidental |
| Alto      | 22       | NO (urgente)         | Performance, robustez, recovery |
| Medio     | 26       | NO                   | UX, observabilidad, mejoras |
| Bajo      | 21       | NO                   | Limpieza técnica |
| Info      | 12       | NO                   | Documentación |

**Veredicto global:** El gate funciona. NO hay vulnerabilidades remotamente explotables hoy. Los hallazgos críticos son sobre **defensa en profundidad**, **validez legal de la evidencia** (Ley 527/1581 de Colombia) y **robustez ante fallos**. Los Críticos deben fixearse ANTES de la primera disputa legal real con un coach.

---

## Hallazgos cruzados (identificados por múltiples agentes — máxima prioridad)

### 🔴 CC-01: PostMessage sin validación de origen
**Identificado por:** Security H-02 + Frontend C-1
**Archivo:** `resources/js/vue/components/coach/CoachContractGate.vue:19-23`

El listener acepta `wc-contract-end` desde cualquier ventana. Browser extensions, popups u otros frames pueden disparar `markScrollComplete()` sin que el coach haya scrolleado. **Esto invalida la evidencia legal de "scroll completo" bajo Ley 527.**

### 🔴 CC-02: Singleton state leak entre logout/login
**Identificado por:** Frontend C-2 + Security H-12
**Archivo:** `resources/js/vue/composables/useContractGate.js:4-9`

Refs a nivel de módulo no se resetean en logout. Si dos coaches usan el mismo navegador consecutivamente, el segundo hereda `scrollCompleted=true` del primero. **Bypass parcial del gate en kioscos / equipos compartidos.**

### 🔴 CC-03: Race condition en primer mount del CoachLayout
**Identificado por:** Frontend C-3
**Archivo:** `resources/js/vue/router/index.js:174-183`

El router guard valida `gate.requires.value` antes de que `refresh()` complete. La primera entrada a `/coach` deja pasar al usuario, los componentes hijos disparan APIs gateadas → cascada de 403 simultáneos → N llamadas paralelas a `/contract/status` (cada una de 40KB).

### 🔴 CC-04: `recordDecline()` no es atómico
**Identificado por:** Backend C-01 + Database H-11
**Archivo:** `app/Services/CoachContractService.php:66-97`

3 escrituras DB sin transacción (insert acceptance + update admin + delete tokens). Si MySQL pierde conexión entre pasos, queda estado inconsistente: coach declined con cuenta activa o tokens vivos.

### 🔴 CC-05: Middleware aplica gate a admin/superadmin/jefe
**Identificado por:** Backend C-02
**Archivo:** `app/Http/Middleware/EnsureCoachContractAccepted.php:18-36`

Daniel.esparza (superadmin) ve el modal y si rechaza, **se cierra a sí mismo el portal**. El gate debe aplicar SOLO a `role='coach'`. Verificado durante deploy: el endpoint retornó `requires_acceptance: true` para Daniel.

### 🔴 CC-06: TrustProxies sin headers explícitos
**Identificado por:** Security H-03
**Archivo:** `bootstrap/app.php:32`

En EasyPanel/Docker, `$request->ip()` puede retornar la IP del proxy interno (172.x.x.x) en lugar de la IP real del coach. **La columna `ip_address` en producción puede estar registrando IPs internas, invalidando la evidencia legal.**

### 🔴 CC-07: Cache de hasAccepted ausente
**Identificado por:** Performance H-01
**Archivo:** `app/Services/CoachContractService.php:38-45`

El middleware corre 1 query DB por request. Dashboard hace 8-15 calls paralelos = 3000+ queries/min en hora pico solo para verificar contrato. **Solucionable con Redis cache de 24h post-acceptance.**

### 🔴 CC-08: `/contract/status` payload de 50KB
**Identificado por:** Performance H-02
**Archivo:** `app/Http/Controllers/Api/Coach/ContractController.php:31-35`

Cuando `requires_acceptance=true`, retorna 40KB de HTML embebido en JSON. Combinado con CC-03 (race), un dashboard cold-start puede transferir 400-500KB innecesarios. **Separar en `/status` (80B) + `/html` con ETag.**

### 🔴 CC-09: Iframe sandbox `allow-same-origin allow-scripts`
**Identificado por:** Security H-01 + Frontend A-5
**Archivo:** `CoachContractGate.vue:88-93`

Combinación más permisiva del sandbox. El día que el contrato HTML venga de DB con interpolación de datos del coach (`{{coach.name}}`), un XSS tiene acceso completo a `localStorage` (token bearer) del padre.

### 🔴 CC-10: Content-hash race condition
**Identificado por:** Security H-06
**Archivo:** `CoachContractService.php:32-36`

Si el blade se modifica entre el GET status (T+0) y el POST accept (T+5min), el coach acepta el HTML A pero la evidencia guarda el hash B. **Solución: cliente envía hash y backend valida match.**

### 🔴 CC-11: `isGateEnabled` fail-open
**Identificado por:** Security H-05
**Archivo:** `CoachContractService.php:17-20`

Si `config:cache` falla o se borra, `config('wellcore.coach_contract.enabled')` retorna `null` → service castea a `false` → gate desactivado silencioso. **Para cumplimiento legal, debe fail-closed.**

### 🔴 CC-12: Tests críticos faltantes
**Identificado por:** Testing A.1, A.2, A.5, A.8
**Archivo:** `tests/Feature/Coach/ContractAcceptanceTest.php`

Faltan: `version_mismatch` no probado, 401 sin token no validado, decline-after-accept (flujo legal más importante), hash SHA-256 verification.

### 🔴 CC-13: `admins.inactive_reason` ausente
**Identificado por:** Database H-03 + Backend implícito
**Archivo:** Schema producción

Service hace `Admin::update(['active' => false])` sin registrar motivo. **No hay forma de distinguir "rechazó contrato" vs "baja manual" 6 meses después.**

### 🔴 CC-14: Recovery from decline no documentado
**Identificado por:** Backend M-02 + Database H-13
**Archivo:** N/A (proceso operativo)

Si Carlos rechaza por error, no hay endpoint admin ni runbook. Único recovery actual: SQL crudo, sin auditoría.

### 🔴 CC-15: Audit log ausente
**Identificado por:** Security H-09 + Database H-09

Solo se guarda resultado final en `coach_contract_acceptances`. Si la fila se borra (DBA, ataque interno, replicación corrupta), no hay rastro paralelo. **Para Ley 1581 + retención 5 años, falta log inmutable externo.**

---

## Plan de implementación por fases

### FASE 1 — Críticos bloqueantes (esta semana, ~6 horas)

**Bloquea:** Cualquier disputa legal con coach. Hardening de seguridad.

| ID | Hallazgo | Archivo | Esfuerzo |
|----|----------|---------|----------|
| F1.1 | Filtrar middleware por `role='coach'` | `EnsureCoachContractAccepted.php` + `ContractController.php` | 15 min |
| F1.2 | `recordDecline` en `DB::transaction` con `lockForUpdate` | `CoachContractService.php` | 10 min |
| F1.3 | TrustProxies con headers explícitos | `bootstrap/app.php` | 5 min |
| F1.4 | `isGateEnabled` fail-closed | `CoachContractService.php` | 5 min |
| F1.5 | Quitar `allow-same-origin` del iframe | `CoachContractGate.vue` | 2 min |
| F1.6 | Validar `e.origin` y `e.source` en `handleMessage` | `CoachContractGate.vue` | 10 min |
| F1.7 | Reset state singleton en `clearAuth()` | `useContractGate.js` + `auth.js` | 15 min |
| F1.8 | Async router guard con dedup | `router/index.js` + `useContractGate.js` | 20 min |
| F1.9 | Migración aditiva `admins.inactive_reason` | nueva migración | 10 min |
| F1.10 | `recordDecline` setea `inactive_reason='contract_declined'` | `CoachContractService.php` | 5 min |
| F1.11 | Validar IP real en producción (test manual) | curl + DB query | 5 min |
| F1.12 | 4 tests críticos faltantes (A.1, A.2, A.5, A.8) | `ContractAcceptanceTest.php` | ~1h |

**Validación post-fase 1:**
- `php artisan test --filter=ContractAcceptanceTest` → 16/16 verde (12 actuales + 4 nuevos)
- Manual: login Daniel.esparza → NO ve modal (filtrado por rol)
- Manual: aceptar contrato como coach real → fila correcta en DB con IP pública

---

### FASE 2 — Altos urgentes (próxima semana, ~10 horas)

**Bloquea:** Performance escala / payload, recovery operativo, evidencia legal robusta.

| ID | Hallazgo | Archivo | Esfuerzo |
|----|----------|---------|----------|
| F2.1 | Cache `hasAcceptedCurrentVersion` en Redis (24h TTL) | `CoachContractService.php` | 20 min |
| F2.2 | Memoización + Redis tags `getContractHtml` y `getCurrentContentHash` | `CoachContractService.php` | 30 min |
| F2.3 | Separar `/contract/status` y `/contract/html` con ETag | `ContractController.php` + routes | 1h |
| F2.4 | Cambiar iframe a `:src` (no srcdoc) + actualizar postMessage origin check | `CoachContractGate.vue` | 30 min |
| F2.5 | Promise dedup en `useContractGate.refresh()` | `useContractGate.js` | 15 min |
| F2.6 | Capturar `RuntimeException` en controller con 503 user-friendly | `ContractController.php` | 20 min |
| F2.7 | Comando `wellcore:verify-coach-contract` en deploy pipeline | nuevo command | 30 min |
| F2.8 | Validar `count()` afectado en `Admin::update()` con log | `CoachContractService.php` | 10 min |
| F2.9 | Endpoint `GET /coach/contract/history` para auditoría coach | `ContractController.php` + routes | 30 min |
| F2.10 | Endpoints admin gestión + reactivación | nuevo `Admin\ContractAcceptanceController.php` | 1.5h |
| F2.11 | Soft-delete en `coach_contract_acceptances` para recovery | migración + model | 30 min |
| F2.12 | Decline confirmation modal: a11y completo (role, focus trap, ESC) | `CoachContractGate.vue` | 1h |
| F2.13 | Quitar `window.location.reload()` post-accept | `CoachContractGate.vue` | 10 min |
| F2.14 | Botón "Reintentar" en error UI + verificación post-accept | `CoachContractGate.vue` + composable | 30 min |
| F2.15 | Comparar `to.fullPath === from.fullPath` en router guard | `router/index.js` | 5 min |
| F2.16 | Hash content validation: cliente envía hash, backend hace `hash_equals` | controller + composable | 1h |
| F2.17 | Tests Altos faltantes (A.3, A.4, A.6, A.7, A.10, A.12) | `ContractAcceptanceTest.php` | 2h |

**Validación post-fase 2:**
- Performance test: dashboard cold-start <2s en 3G simulado
- Cache hit rate >99% en steady-state
- Recovery test: admin reactiva coach declined, gate vuelve a aparecer

---

### FASE 3 — Medios (sprint siguiente, ~15 horas)

**Mejoras de observabilidad, UX, evolución futura.**

| ID | Hallazgo | Archivo | Esfuerzo |
|----|----------|---------|----------|
| F3.1 | Tabla `coach_contract_audit_log` (eventos granulares) | nueva migración | 30 min |
| F3.2 | Listener async para audit log en accept/decline | nuevo Listener | 1h |
| F3.3 | `Log::channel('audit')` con sink externo (CloudWatch/syslog) | `logging.php` + service | 1h |
| F3.4 | Captura device_fingerprint client-side (FingerprintJS) | composable + migración | 2h |
| F3.5 | GeoIP lookup server-side (MaxMind GeoLite2 local) | nuevo service + migración | 2h |
| F3.6 | Refactor: split en `CoachContractService` + Actions | restructurar | 2-3h |
| F3.7 | Eventos `CoachContractAccepted/Declined` + Listeners | nuevos events | 1h |
| F3.8 | Enum `CoachContractStatus` + `AuthTokenUserType` | nuevos enums | 30 min |
| F3.9 | FormRequest `AcceptContractRequest` | nuevo request | 15 min |
| F3.10 | Skeleton/spinner mientras iframe carga | `CoachContractGate.vue` | 30 min |
| F3.11 | Header responsive en mobile (<360px) | `CoachContractGate.vue` | 30 min |
| F3.12 | Dark mode coordinado entre app y iframe | postMessage de tema | 1h |
| F3.13 | Reducir Google Fonts (4→2 familias) en blade | `coach-contract-v1.0.blade.php` | 30 min |
| F3.14 | sessionStorage cache (90s TTL) para hard reloads | `useContractGate.js` | 30 min |
| F3.15 | Migrar a Pinia store + `readonly()` en refs | `stores/contractGate.js` | 1.5h |
| F3.16 | i18n: blades por locale (es/pt) + fallback | service + nuevos blades | 2h |
| F3.17 | Pre-launch script: verify deploy en CI/CD | scripts/deploy.sh | 30 min |
| F3.18 | Migrar tests a Pest (consistencia con resto) | `ContractAcceptanceTest.php` | 1h |
| F3.19 | `CoachContractAcceptanceFactory` | nueva factory | 15 min |
| F3.20 | Comando `wellcore:audit-contract-integrity` (orphans) + schedule diario | nuevo command | 45 min |

**Validación post-fase 3:**
- Audit log con 5+ eventos por aceptación
- Reportes admin muestran fingerprint + país por aceptación
- Tests >25 (incluyendo Pest sugerido)

---

### FASE 4 — Bajos + cleanup (próximo trimestre, ~8 horas)

| ID | Hallazgo | Archivo | Esfuerzo |
|----|----------|---------|----------|
| F4.1 | Eliminar migración muerta `coaches.inactive_reason` | DB cleanup | 15 min |
| F4.2 | Comparar legacy migration vs schema producción real | dump + diff | 1h |
| F4.3 | Eliminar dead code `is_draft` flag | `config/wellcore.php` | 5 min |
| F4.4 | Indices: `(coach_id, contract_version, status)` covering | nueva migración | 15 min |
| F4.5 | Index `(status, declined_at)` para reportes admin | nueva migración | 15 min |
| F4.6 | Index `(user_type, user_id)` en auth_tokens (validar EXPLAIN antes) | nueva migración | 30 min |
| F4.7 | Snapshot `coach_email_at_signing` + `coach_name_at_signing` | migración + service | 30 min |
| F4.8 | `user_agent VARCHAR(2000)` en lugar de TEXT | migración | 5 min |
| F4.9 | Status enum: agregar `pending`, `superseded` | migración (ALTER MODIFY) | 5 min |
| F4.10 | Scopes y relaciones en model `CoachContractAcceptance` | model | 15 min |
| F4.11 | Confirmación server-side en decline (`confirm: 'RECHAZAR'`) | controller + composable | 30 min |
| F4.12 | Rate limit dedicado `contract-status` y `decline` | RateLimiter + routes | 30 min |
| F4.13 | E2E con Chrome DevTools MCP (2 escenarios) | nuevo test/script | 2h |
| F4.14 | Vitest tests del componente Vue | nuevos specs | 1.5h |
| F4.15 | Backup retention 7 años + encryption-at-rest documentado | LA-07 coordination | 1h |
| F4.16 | Política Habeas Data documentada | docs/LEGAL_DATA_RETENTION.md | 30 min |
| F4.17 | DB user dedicado `wellcore_legal_writer` | LA-05 + LA-06 | 1h |

---

## Implementación recomendada

### Orden de ejecución por agente

| Fase | Agente principal | Coordina con |
|------|-----------------|--------------|
| F1.1, F1.2, F1.4, F1.8, F1.10, F1.16 | la-02-backend | la-05-security |
| F1.3, F1.5, F1.6, F1.7, F2.4, F2.12, F2.13 | la-03-vue3 | la-05-security |
| F1.9, F4.4-F4.10 | la-06-database | la-02-backend |
| F1.11 | la-07-devops | la-05-security |
| F1.12, F2.17 | la-14-testing | la-02-backend |
| F2.1-F2.3, F3.13 | la-10-performance | la-02-backend |
| F3.1-F3.5, F3.16 | la-18-enterprise | la-05-security |

### Workflow recomendado

1. **Crear branch:** `git checkout -b hardening/coach-contract`
2. **Ejecutar Fase 1 task por task** vía `superpowers:subagent-driven-development`
3. **Validar tests** después de cada task: `php artisan test --filter=ContractAcceptanceTest`
4. **Smoke test manual** al final de Fase 1: login Daniel + login coach real
5. **Push + deploy** Fase 1 antes de empezar Fase 2 (hardening urgente prod)
6. **Fase 2** en branch separada, deploy gradual
7. **Fase 3 y 4** como mejoras incrementales

### Métricas de éxito

| Métrica | Antes | Target post-Fase 2 |
|---------|-------|--------------------|
| Tests pasando | 12 | 28+ |
| Queries DB en hot path | 1/request | 0.005/request (cache hit) |
| Payload `/contract/status` | ~50KB | ~80 bytes |
| LCP modal en 3G | ~3.5s | ~1.2s |
| Coverage del controller | ~60% | >90% |
| Coverage del middleware | ~80% | >95% |
| Hallazgos críticos abiertos | 15 | 0 |
| Hallazgos altos abiertos | 22 | <5 |

### Riesgos del plan

1. **F2.3 (separar endpoints):** breaking change de API. Coordinar con frontend para deploy simultáneo.
2. **F3.6 (refactor a Actions):** big refactor que toca tests existentes. Requiere TDD estricto.
3. **F3.4-F3.5 (fingerprint + GeoIP):** dependencias externas (npm + MaxMind). Aumenta superficie de ataque.
4. **F1.11 (validación IP):** si la IP en producción es de proxy, hay que coordinar con LA-07 para configurar X-Forwarded-For en el proxy stack de EasyPanel.

### Rollback strategy

- **Fase 1:** todos los cambios son retro-compatibles. Si algo rompe, revert del commit + redeploy.
- **Fase 2:** F2.3 requiere backward-compat: mantener `/status` con HTML embedded por 1 release deprecated.
- **Fase 3-4:** features aditivas, rollback granular por hallazgo.

---

## Apéndice A: hallazgos completos por agente

### Security Audit (la-05-security) — 14 hallazgos
- H-01 [Alto] Iframe sandbox + srcdoc XSS escalable
- H-02 [Alto] postMessage sin validación origin
- H-03 [Alto] $request->ip() en proxy
- H-04 [Medio] HTML servido pre-acceptance
- H-05 [Medio] isGateEnabled fail-open
- H-06 [Medio] Content-hash race condition
- H-07 [Medio] Decline irreversible
- H-08 [Medio] Superadmin/jefe ven modal
- H-09 [Medio] Audit log ausente
- H-10 [Bajo] FK ausente coach_id
- H-11 [Bajo] Decline sin server-side confirmation
- H-12 [Bajo] Composable singleton leak
- H-13 [Bajo] try/catch vacío en logout
- H-14 [Bajo] Interceptor 403 puede generar loops

### Backend Audit (la-02-backend) — 16 hallazgos
- C-01 [Crítico] recordDecline no atómico
- C-02 [Crítico] Middleware no filtra por rol
- A-01 [Alto] Render blade en cada request
- A-02 [Alto] RuntimeException no capturada
- A-03 [Alto] Admin::update sin validar count
- A-04 [Alto] Falta verify-coach-contract command
- A-05 [Alto] SRP violation en service
- M-01 [Medio] Falta endpoint history coach
- M-02 [Medio] Falta endpoints admin
- M-03 [Medio] Dead code is_draft
- M-04 [Medio] Magic string user_type='admin'
- M-05 [Medio] Falta i18n LATAM/Brasil
- B-01 [Bajo] Sin enum status
- B-02 [Bajo] Model sin scopes/relations
- B-03 [Bajo] FormRequest faltante
- B-04 [Bajo] Decisión orphan rows

### Frontend Audit (la-03-vue3) — 17 hallazgos
- C-1 [Crítico] handleMessage sin origin validation
- C-2 [Crítico] State leak singleton
- C-3 [Crítico] Race condition router guard
- A-1 [Alto] window.location.reload() pesado
- A-2 [Alto] Decline modal sin a11y
- A-3 [Alto] Sin reintento tras error
- A-4 [Alto] router guard query string bypass
- A-5 [Alto] Iframe allow-same-origin
- M-1 [Medio] Sin loading skeleton iframe
- M-2 [Medio] accept() doble guard
- M-3 [Medio] Debería ser Pinia store
- M-4 [Medio] Inconsistencia computed/raw refs
- M-5 [Medio] Header roto en mobile chico
- B-1 [Bajo] Dark mode iframe
- B-2 [Bajo] messageListenerAttached innecesario
- B-3 [Bajo] Dynamic import en interceptor
- B-4 [Bajo] Versión duplicada h2 vs label

### Database Audit (la-06-database) — 15 hallazgos
- H-01 [Medio] Indice subóptimo gate query
- H-02 [Bajo] Falta indice status
- H-03 [Alto] admins.inactive_reason ausente
- H-04 [Bajo] Migración coaches dead code
- H-05 [Alto] Schema legacy drift vs prod
- H-06 [Medio] Falta device_fingerprint
- H-07 [Medio] Falta geo_country
- H-08 [Bajo] Status enum sin pending/superseded
- H-09 [Alto] Sin tabla audit log
- H-10 [Medio] Sin registro intentos fallidos
- H-11 [Medio] Race condition accept/decline
- H-12 [Bajo] user_agent TEXT vs VARCHAR
- H-13 [Alto] Recovery from decline no procedimentado
- H-14 [Medio] Sin validación integridad coach_id
- H-15 [Crítico] Data retention y backup compliance

### Performance Audit (la-10-performance) — 15 hallazgos
- H-01 [Crítico] Cache hasAccepted ausente
- H-02 [Crítico] Payload /contract/status 50KB
- H-03 [Alto] Render blade 2x por accept
- H-04 [Alto] Race condition status fetches en 403
- H-05 [Alto] Falta index user_type+user_id auth_tokens
- H-06 [Medio] Hash en decline también renderiza
- H-07 [Medio] Falta sessionStorage cache
- H-08 [Medio] Iframe srcdoc re-render flash
- H-09 [Medio] 4 familias Google Fonts
- H-10 [Bajo] isGateEnabled config en cada request
- H-11 [Bajo] Index status opcional
- H-12 [Bajo] Chunk útil pequeño
- H-13/14/15 [Info] Router/layout/OPCache OK

### Testing Audit (la-14-testing) — 19 gaps
- A.1 [Crítico] version_mismatch no probado
- A.2 [Crítico] 401 sin token (3 endpoints)
- A.3 [Alto] Token expirado
- A.4 [Alto] Cliente accediendo /contract/*
- A.5 [Crítico] decline después de accept
- A.6 [Alto] Idempotencia accept
- A.7 [Alto] Middleware gate disabled explícito
- A.8 [Crítico] Hash SHA-256 verification
- A.9 [Medio] UA truncation 4000 chars
- A.10 [Alto] getContractHtml versión inexistente
- A.11 [Medio] status no devuelve html cuando aceptó
- A.12 [Alto] decline endpoint happy path
- B.1 [Medio] decline → re-accept impedido
- B.2 [Medio] Migrar a Pest
- B.3 [Bajo] Factory acceptances
- B.4 [Bajo] Dataset versiones
- B.5 [Bajo] Race condition (skip)
- C.1 [Alto] E2E Chrome DevTools MCP
- C.2 [Medio] Vitest del componente
