# 02 — Estado actual del sistema de planes WellCore

> Documento de diseño. Snapshot 2026-05-16. Solo lectura.

## TL;DR

Hoy coexisten varios caminos para crear un plan, pero solo **tres están activos en el scope del motor v2**: (a) el flujo "Claude Code humano" que lee 27 MDs y hace INSERT vía PDO puro o tinker — dominante, (b) el panel admin Vue (`AdminController` API REST) para asignación manual, (c) el panel coach Livewire (`PlansManager::assignTemplate`) que asigna templates existentes a clientes. Los AI generators del admin/coach **están fuera de scope** — Daniel confirmó 2026-05-16. Los tres caminos activos escriben a `assigned_plans.content` como JSON libre, sin validación de schema en DB ni en aplicación. El schema real verificado contra producción (Chrome MCP, 2026-05-16) confirma: **`version` SÍ existe**, `updated_at` no, y hay una columna `ai_generation_id` que el motor v2 dejará NULL (decisión registrada). El volumen es chico — **29 clientes con plan activo, 90 filas activas** después del cleanup de 5 filas con `plan_type=''` que ejecuté con autorización. El caso Cristian (2026-04-25) demostró que el parser tolera estructuras malformadas y la UI las renderiza mal en silencio — exactamente lo que el linter pre-INSERT del motor v2 va a atrapar.

---

## 1. Cómo se crea un plan HOY (el flujo "humano + Claude Code")

Documentado en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\01-PASO-A-PASO.md` y orquestado por `PROMPT-CLAUDE-CODE-NUEVA-SESION.txt` v2.5. Es el flujo dominante hoy — los otros 3 caminos están subutilizados.

| Fase | Qué pasa | Quién |
|------|----------|-------|
| 0 INTAKE | Daniel pega datos del cliente al prompt | Humano |
| 1 LECTURA | LLM lee 27 MDs (incluso si va a generar un plan trivial) | LLM — **caro en tokens** |
| 2 DISEÑO | LLM elige split/periodización/ejercicios desde memoria + MDs | LLM — **fuente de monotonía** |
| 3 ARMAR JSON | LLM escribe JSON canónico de 16a/b/c/d | LLM |
| 4 INSERT | Script PHP en `bootstrap/insert_{cliente}_plans.php` con PDO puro, hardcodea credenciales, push a GitHub, ejecuta vía `silvia-gitpull-load` | Humano + Claude Code |
| 5 CACHE | `php artisan cache:clear` o `Cache::forget()` selectivos | Claude Code |
| 6 VERIFY | Chrome MCP impersona cliente, screenshot, checklist de 6 ítems (post-Cristian) | Claude Code |
| 7 ENTREGA | `WellcoreNotification::create()` + WhatsApp manual | Humano |

**Tiempo total**: 45-100 min por plan (más para plan combinado). **Token cost típico**: ~150-300K tokens input + ~50-100K output (estimación basada en releer 27 MDs).

---

## 2. Los caminos paralelos en código (clasificados por scope motor v2)

Encontrados con `grep AssignedPlan::create app/` y clasificados según la decisión de producto 2026-05-16 de Daniel ("AIPlanGenerator no se usa más por ahora"):

| Estado | Camino | Archivo | Línea | Cómo se dispara |
|--------|--------|---------|-------|-----------------|
| 🟢 Activo | API admin (Vue panel) | `app/Http/Controllers/Api/AdminController.php` | 1832 | `POST /api/v/admin/clients/{id}/assign-plan` — asignación manual de plan desde Vue admin |
| 🟢 Activo | Panel coach — assign template | `app/Livewire/Coach/PlansManager.php` | 434 | Coach elige template existente + cliente + asigna |
| 🔴 Fuera de scope | Generador IA streaming admin | `app/Http/Controllers/Api/AdminController.php` | 2992, 3089 | Endpoints SSE del generador admin |
| 🔴 Fuera de scope | Generador IA Livewire admin | `app/Livewire/Admin/AIPlanGenerator.php` | 418 | Wizard 4 pasos (CLAUDE.md / MD 24) |
| 🔴 Fuera de scope | Generador IA streaming v2 | `app/Http/Controllers/Api/AdminAIGeneratorController.php` | 321 | SSE streaming alternativo del panel admin |
| 🔴 Fuera de scope | Panel coach — AI generation | `app/Livewire/Coach/PlansManager.php` | 776 | `saveGeneratedPlan` con `ai_generated=true` |

**Decisión de producto registrada (2026-05-16)**: los flujos AI generator del admin Y del coach (los 🔴) quedan fuera del scope del motor v2. El motor v2 reemplaza al flujo "Claude Code humano" (§1) y deja intactos los 2 flujos 🟢 (asignación manual desde Vue admin + assign template desde coach panel) — esos siguen siendo la forma estándar de asignar un plan ya generado a un cliente.

**Hallazgo crítico**: los 2 endpoints 🟢 activos (AdminController:1832, PlansManager:434) **siguen usando `'version' => …`**, igual que los 🔴 deprecated. Por lo tanto el drift `version` afecta a flujos en producción, no solo a código muerto. Ver §5.

---

## 3. Riesgos del sistema actual (rankeados por daño esperado × probabilidad)

| # | Riesgo | Daño | Probabilidad | Evidencia |
|---|--------|------|--------------|-----------|
| 1 | **Falta de validation pre-INSERT** → plan entra a producción con campos faltantes y la UI renderiza mal en silencio | Alto (cliente pierde confianza, soporte) | Alta (ya pasó) | Caso Cristian 2026-04-25: 6 errores que el parser JSON tolera pero la UI no renderiza |
| 2 | **Monotonía metodológica** — el LLM repite 3×12-15 con RIR 2-3 por default | Medio (los planes pierden valor percibido) | Muy alta | Daniel reporta como motivo principal del motor v2 |
| 3 | **Token cost** ~150-300K input por plan (27 MDs releídos de cero) | Medio-alto (costo operativo creciente) | Cada generación | Estimación basada en tamaño de MDs en `E:\...\SISTEMA-CREACION-PLANES\` |
| 4 | **Drift docs ↔ código** — 6 endpoints usan `version` que la doc dice no existir | Alto si los endpoints fallan (panel coach roto) | Desconocida hasta verificar | §5 de este doc |
| 5 | **GIFs sin validación** — alias que no existe en repo se inserta como URL rota | Medio (cuadros vacíos en WorkoutPlayer) | Media | Caso Cristian error #2; ya hay regla "LEY GIF" en 16a pero no enforced |
| 6 | **Caches no invalidados** después de update | Medio (cliente ve plan viejo 5 min) | Recurrente | Mencionado en MDs 17 y troubleshooting |
| 7 | **`fase` sin tilde / nombre no oficial** → topbar dinámico se degrada | Bajo-medio (visual roto) | Recurrente | MD 08 lista los 9 nombres oficiales; ningún check lo enforced |
| 8 | **Plan previo no desactivado** → 2 planes activos del mismo tipo | Alto (UI elige uno arbitrariamente) | Baja si se sigue el flujo | Documentado en checklist MD 18 |
| 9 | **Bypass del checklist VERIFY** — sesión anterior a Cristian saltó verificación visual | Alto | Probable bajo presión | Es lo que motivó la regla "NUNCA entregar sin Chrome MCP" |

**Conclusión**: los riesgos #1, #2, #5, #7 son **exactamente lo que un linter pre-INSERT atrapa**. Sprint 1 del rollout (doc 07) debe construir el linter aislado y correrlo contra los 5-10 JSONs viejos para demostrar valor antes de tocar nada.

---

## 4. Tablas que NO podemos tocar (motivo + dueño)

| Tabla | Por qué intocable | Quién la usa |
|-------|-------------------|--------------|
| `assigned_plans` | Núcleo del producto, vanilla PHP la lee y escribe | Vanilla PHP + Laravel + Vue SPA |
| `clients` | Auth, billing, perfil — multi-app | Vanilla PHP + Laravel |
| `auth_tokens` | WellCoreGuard depende de schema actual (ADR 0002) | Laravel (vanilla también la consulta) |
| `exercise_aliases` | Fuente de GIFs canónicos | Vanilla PHP + Laravel |
| `workout_sessions`, `workout_logs` | Tracking de entrenos del cliente | Vanilla + Laravel + WorkoutPlayer |
| `checkins` | Métricas de progreso, base de dashboards | Vanilla + Laravel |
| `payments`, `wompi_*` | Billing, integración con pasarela | Vanilla principalmente |
| `coaches`, `admins` | Auth + permissions | Vanilla + Laravel |
| `plan_templates` | Templates reusables por coaches | Laravel (modelo `PlanTemplate`) |

**Regla derivada para el motor v2**: el motor crea tablas nuevas en una DB SEPARADA (`wellcore_kb` local). Si en algún momento se necesita tabla nueva en `wellcore_fitness`, va por migración aditiva con guardas — y solo si hay justificación irreducible (ej. `plan_engine_runs` para observability del rollout — doc 04 lo discute).

---

## 5. Schema real de `assigned_plans` — drift sin resolver

### Lo que el código asume

`app/Models/AssignedPlan.php:13-22` declara `#[Fillable]` con: `client_id, plan_type, content, version, assigned_by, valid_from, expires_at, active`.

`AssignedPlan.php:29` define `public $timestamps = false` — confirma que **no hay `updated_at`** (Laravel no toca timestamps).

`casts()` (AssignedPlan.php:31-40) lista: `content → array`, `valid_from / expires_at → date`, `active → boolean`, `created_at → datetime`. NO castea `version`.

### Lo que las migraciones Laravel verifican

Solo 3 migraciones tocan la tabla — todas aditivas:

| Archivo | Cambio |
|---------|--------|
| `2026_03_22_000100_add_index_assigned_plans_client_type_active.php` | Índice `idx_ap_client_type_active(client_id, plan_type, active)` |
| `2026_03_22_000401_add_index_assigned_plans_assigned_by.php` | Índice en `assigned_by` |
| `2026_04_19_120000_expand_assigned_plans_plan_type_enum_add_ciclo.php` | ENUM `plan_type` → `('entrenamiento','nutricion','habitos','suplementacion','ciclo')` |
| `2026_04_24_120000_add_expires_at_to_assigned_plans.php` | Columna `expires_at DATE NULL` + backfill |

**No hay `CREATE TABLE assigned_plans` en Laravel** — la tabla la creó el vanilla PHP fuera del flujo Laravel. Por lo tanto, las migrations Laravel **no son fuente de verdad** del schema completo.

### Lo que la documentación afirma

`PROMPT-CLAUDE-CODE-NUEVA-SESION.txt:142-146` y `:387-390`, vigente desde 2026-04-25 post-Cristian:

> ⚠️ TABLA `assigned_plans` columnas reales (verificadas): id, client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at. **NO tiene `updated_at` ni `version`.** Si los incluyes en INSERT, falla.

`LECCIONES_APRENDIDAS_CRISTIAN.md:198` repite lo mismo.

### Lo que el código de producción ejecuta hoy

Endpoints activos (🟢 en §2) que envían `version`:
- `AdminController.php:1836` (`'version' => 1`) — asignación manual desde Vue admin
- `PlansManager.php:438` (`'version' => $latestVersion + 1`) — assign template desde panel coach
- `PlansManager.php:430-432` además **lee** `max('version')` antes de insertar

Endpoints deprecated (🔴 en §2) que también lo envían: `AdminController.php:2996, 3093`, `AdminAIGeneratorController.php:325`, `AIPlanGenerator.php:418`, `PlansManager.php:780, 772-774`.

Si la columna `version` no existiera, los queries `max('version')` también fallarían — no solo los INSERT. Los coaches habrían reportado el bug. Por lo tanto, la hipótesis (B) del cuadro siguiente es **poco probable pero no imposible** (puede ser que el panel coach se use vía templates pre-creados que no disparan ese código en producción).

### Verificación dura (2026-05-16, vía Chrome MCP → EasyPanel → container console)

**Hipótesis A confirmada**: la columna `version` SÍ existe en producción. La doc post-Cristian estaba mal en ese punto específico.

Output real de `SHOW CREATE TABLE assigned_plans` en producción:

```sql
CREATE TABLE `assigned_plans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int unsigned NOT NULL,
  `plan_type` enum('entrenamiento','nutricion','habitos','suplementacion','ciclo') NOT NULL,
  `content` longtext,
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `assigned_by` int unsigned DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ai_generation_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`, `plan_type`),
  KEY `idx_ap_client_type_active` (`client_id`, `plan_type`, `active`),
  KEY `idx_aplans_assigned_by` (`assigned_by`),
  KEY `assigned_plans_expires_at_index` (`expires_at`),
  KEY `idx_ap_assignedby_active` (`assigned_by`, `active`),
  KEY `idx_ap_client_validfrom` (`client_id`, `active`, `valid_from`),
  CONSTRAINT `assigned_plans_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assigned_plans_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
```

**Hallazgos confirmados (verdad operativa)**:

| Atributo | Realidad en prod |
|----------|------------------|
| `version smallint unsigned NOT NULL DEFAULT 1` | ✅ EXISTE — los 7 endpoints que la usan funcionan |
| `updated_at` | ❌ NO EXISTE — `AssignedPlan.php:29` con `$timestamps = false` es correcto |
| `ai_generation_id INT NULL` | 🆕 **Columna no documentada en ningún MD** — probablemente FK a tabla de runs del AI generator. Investigar en doc 02 followup |
| `content` | `LONGTEXT` (no JSON typed) — Eloquent castea a array vía `$casts` |
| Storage | InnoDB · utf8mb4 · `utf8mb4_0900_ai_ci` (accent-insensitive, MySQL 8) |
| FK | `client_id` → `clients(id)` CASCADE · `assigned_by` → `admins(id)` SET NULL |
| Índices | 6 índices (PK + 5 secundarios), 3 más de los que aparecen en migrations Laravel — los otros 3 los creó vanilla PHP o un DBA fuera de Laravel migrations |
| `AUTO_INCREMENT=181` | ~180 filas históricas creadas |

**Implicaciones para el motor v2**:

- El motor v2 puede usar versionado completo desde el día 1. La columna `version` está disponible y los endpoints existentes ya esperan que se pase.
- La columna `ai_generation_id` requiere investigación adicional — si era FK a una tabla del AI generator (que Daniel marcó como out of scope), ¿la motor v2 la deja en NULL, la usa para trazabilidad, o la ignora? Pendiente para doc 04.
- La doc del sistema MDs (PROMPT v2.5 líneas 142-146 + LECCIONES_APRENDIDAS_CRISTIAN.md:198) requiere corrección — los humanos están escribiendo planes asumiendo que `version` no existe.

### Drift adicional (menor pero a registrar)

El ENUM real de `plan_type` (migración 2026-04-19) es:
`('entrenamiento','nutricion','habitos','suplementacion','ciclo')`

`00-INDEX.md:175-194` documenta como valores válidos **además**: `ciclo_hormonal`, `bloodwork`. Esos dos NO están en el ENUM — un INSERT con `plan_type='bloodwork'` lo rechaza MySQL.

**Implicación**: el motor v2 NO puede generar planes `bloodwork` directamente vía `assigned_plans` hasta que se expanda el ENUM. La feature Bloodwork Elite usa una tabla aparte (`BloodworkResult`), confirmar.

---

## 6. Volumen actual (verificado 2026-05-16 contra producción)

Output real:

| `plan_type` | `active` | filas |
|-------------|----------|-------|
| `''` (vacío) | 1 | **5** ⚠️ data quality issue |
| `''` (vacío) | 0 | 5 |
| `entrenamiento` | 1 | 26 |
| `entrenamiento` | 0 | 33 |
| `nutricion` | 1 | 23 |
| `nutricion` | 0 | 27 |
| `habitos` | 1 | 12 |
| `habitos` | 0 | 8 |
| `suplementacion` | 1 | 17 |
| `suplementacion` | 0 | 8 |
| `ciclo` | 1 | 2 |
| `ciclo` | 0 | 0 |

**Clientes únicos con al menos un plan activo: 29**. Total filas históricas: ~186 (AUTO_INCREMENT=181).

**Implicaciones para el rollout (doc 07)**:

- El blast radius es **muy chico** — 29 clientes activos. El motor v2 puede arrancar con 1 cliente piloto y escalar a los 29 en semanas, no meses.
- El plan más usado es `entrenamiento` (26 activos), seguido de `nutricion` (23) y `suplementacion` (17). El MVP debe priorizar las 3 verticales del plan Esencial.
- `ciclo` tiene solo 2 planes activos — feature muy nueva (abril 2026), no es crítica para MVP.

### Anomalía detectada y resuelta — data quality cleanup ejecutado 2026-05-16

Había **5 filas activas con `plan_type=''`** (string vacío). El ENUM debería rechazarlo, pero la migration 2026-04-19 usó `SET SESSION sql_mode = ''` antes del ALTER, lo que permite valores vacíos.

Filas afectadas:

| id | client_id | created_at |
|----|-----------|------------|
| 7 | 10 | 2028-03-08 06:11:13 |
| 8 | 11 | 2028-03-08 06:18:38 |
| 9 | 15 | 2028-03-08 06:18:48 |
| 40 | 29 | 2028-03-17 18:50:41 |
| 47 | 37 | 2028-03-18 09:48:18 |

Fechas anómalas (año 2028) sugieren seed de tests histórico que nunca se limpió. Daniel autorizó el cleanup masivo (2026-05-16). Ejecutado vía Chrome MCP → EasyPanel console dentro de transacción con guardrail (`commit solo si rowcount == 5, sino rollback`):

```sql
START TRANSACTION;
UPDATE assigned_plans SET active=0 WHERE plan_type='' AND active=1;
-- AFFECTED: 5 → COMMITTED
```

**Conteo post-cleanup**: 90 filas activas (no 95) repartidas en los 5 plan_type oficiales del ENUM. Las 5 filas que estaban `active=1` con `plan_type=''` ahora son `active=0` (conservadas para audit trail, no eliminadas).

**Implicación para el motor v2**:
- El linter (doc 06) **debe rechazar** `plan_type ∉ {entrenamiento, nutricion, habitos, suplementacion, ciclo}` con severidad ERROR. No reabrir la regresión.
- Cualquier INSERT futuro con `plan_type=''` también será rechazado por el linter (defense in depth contra el ENUM permisivo).

---

## 7. Lo que NO está resuelto en este doc

1. ~~**Schema real de `assigned_plans`**~~ ✅ **RESUELTO 2026-05-16** vía Chrome MCP → EasyPanel container. Ver §5 y §6.
2. ~~**Volumen actual de planes activos**~~ ✅ **RESUELTO**: 29 clientes con plan activo, 95 filas activas totales. Ver §6.
3. ~~**`ai_generation_id` (columna nueva descubierta)**~~ ✅ **DECIDIDO 2026-05-16**: el motor v2 deja `ai_generation_id` NULL siempre. Razón: conceptualmente el motor v2 NO es un "AI generator" del scope deprecated — es un authoring toolchain. Si en el futuro se quiere trazabilidad, se agrega columna nueva `plan_engine_run_id` que apunte a `plan_engine_runs` (doc 04).
4. ~~**15 filas con `plan_type=''` activas**~~ ✅ **RESUELTO 2026-05-16**: cleanup ejecutado, eran 5 filas (no 15), todas marcadas `active=0` dentro de transacción con guardrail. Ver §6.
5. **Uso real de los 2 caminos 🟢 activos** — ¿con qué frecuencia los admins asignan vía Vue panel y con qué frecuencia los coaches asignan templates? Útil para priorización del rollout (doc 07). Lo medimos con `SELECT assigned_by, COUNT(*) FROM assigned_plans WHERE created_at > NOW() - INTERVAL 30 DAY GROUP BY assigned_by`.
6. **Bloodwork y ciclo_hormonal** — el ENUM real es `{entrenamiento, nutricion, habitos, suplementacion, ciclo}`. La feature Bloodwork Elite usa tabla aparte (`bloodwork_results`, según `reference_plan_creation_system.md` 2026-05-04). El motor v2 NO necesita generar planes `bloodwork` vía `assigned_plans`. Decisión de producto: ¿el motor v2 cubre `ciclo` en MVP o lo difiere? Doc 09 lo lista.
7. **Token cost real** — estimé 150-300K tokens por plan, no medí. Antes del doc 05 (decision engine) querría una medición real de 3 generaciones recientes para fijar la baseline contra la cual celebrar "-70%".
8. **Necesidad de actualizar el sistema MDs** — `PROMPT-CLAUDE-CODE-NUEVA-SESION.txt` v2.5 y `LECCIONES_APRENDIDAS_CRISTIAN.md` afirman incorrectamente que `version` no existe. Si seguimos generando planes con el flujo "Claude Code humano", todavía gana el "include version" porque la columna sí existe — pero alguien debería corregir esos MDs para futuras sesiones que no sepan del motor v2.

## Próximo doc

**`03-knowledge-base-schema.md`** — Schema completo de la DB local `wellcore_kb`:
- Migraciones Laravel aditivas (corren contra MySQL local de Daniel, NO contra producción).
- Las 8 tablas: `methodologies`, `methodology_rules`, `exercise_metadata`, `principles`, `plan_templates`, `decision_rules`, `lint_rules`, `corpus_embeddings`.
- Tipos de columna, índices, versionado.
- Sincronización local ↔ producción si en el futuro Daniel quiere portar el motor v2 a un VPS distinto.

**Bloqueado por**: respuesta al `DESCRIBE assigned_plans` del §5. Sin esa verificación, el doc 03 puede asumir mal y arrastrar el error a los docs 04-09.

Espero OK de Daniel + resultado del `DESCRIBE` antes de avanzar al doc 03.
