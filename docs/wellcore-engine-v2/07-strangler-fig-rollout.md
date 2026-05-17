# 07 — Strangler Fig rollout (plan de coexistencia)

> Documento de diseño. Define cómo el motor v2 convive con el flujo actual sin romperlo.

## TL;DR

El motor v2 reemplaza el **cerebro** que genera el JSON, NO el frontend Vue ni el schema `assigned_plans` (excepto 2 columnas aditivas: `idempotency_key` y `plan_engine_version` — **postergadas al Sprint 4**, decisión Daniel 2026-05-16). Rollout en 6 sprints de 1 semana: **Sprint 0** instala DB local + seed inicial (cero toque a prod); **Sprint 1** corre el linter contra 5-10 fixtures reales (cero riesgo a prod, valor inmediato); **Sprints 2-3** construyen SELECT/COMPOSE/PERSIST/VERIFY con dry-run a archivo (sin INSERT); **Sprint 4** ejecuta la migration aditiva + dispara con 1 cliente piloto que Daniel elige manualmente; **Sprint 5** escala a 3 clientes; **Sprint 6+** rollout gradual al universo de 29 clientes activos. Killswitch operativo: env var `WC_ENGINE_V2_ENABLED=false` en `.env` → motor desactivado en <1min sin downtime. Rollback en 3 niveles según severidad. Métricas a observar las primeras 4 semanas: token cost por plan (<$0.30 target), % linter passed primer intento (>85%), % verify visual passed (>90%), tiempo total (<90s), tasa "Daniel editó a mano post-generación" (<20%). Anderson (coach principal) NO se entera hasta Sprint 6 / Fase 3 — el motor v2 es uso interno, no afecta su workflow hasta que esté calibrado.

---

## 1. Premisa: qué cambia y qué NO cambia

| Componente | Cambia? | Justificación |
|------------|---------|---------------|
| Frontend Vue 3 SPA del cliente | **NO** | El JSON canónico sigue siendo 16a/b/c/d. El motor v2 produce el MISMO shape que hoy. Cero risk para el cliente. |
| Schema `assigned_plans` | Solo 2 columnas nuevas (aditivas) | `idempotency_key` (doc 04 §15.4) + `plan_engine_version` (este doc §3). Cumple ADR-0003 (solo aditivas). |
| `clients`, `coaches`, `admins`, `exercise_aliases`, `workout_*`, `checkins`, `payments` | **NO** | Cero cambios — el motor v2 las consulta read-only. |
| `PlansManager::assignTemplate` (panel coach) | **NO** | Sigue funcional. Los coaches no notan cambio. |
| `POST /api/v/admin/clients/{id}/assign-plan` (panel admin Vue) | **NO** | Endpoint sigue activo, ahora recibe JSONs del motor v2 o manuales indistintamente. |
| Flujo "Claude Code humano" (lee 27 MDs + INSERT manual) | **SE REEMPLAZA** | Es lo que el motor v2 sustituye. Los MDs quedan como documentación humana. |
| Tablas nuevas en `wellcore_kb` (local Daniel) | **SÍ — 8 tablas** | Doc 03. NO toca `wellcore_fitness`. |

**Mensaje crítico**: ningún cliente debería notar que se cambió el cerebro. Si lo notan = bug del motor v2.

---

## 2. La migración aditiva (postergada al Sprint 4 — decisión Daniel 2026-05-16)

> ⚠️ **Cambio respecto a versión original del doc**: esta migración se ejecuta en **Sprint 4** (no Sprint 0). Sprints 0-3 corren sin tocar el schema de prod. Implicación: durante Sprints 0-3 no hay idempotency strict ni audit de `plan_engine_version` — mitigado por (a) file lock del orchestrator (doc 04 §11) que previene duplicados accidentales y (b) sprints 0-3 hacen dry-run a archivo, no INSERT a prod.

Una sola migración Laravel sobre `wellcore_fitness` (DB compartida — cumple ADR-0003 estricto):

```php
<?php
// database/migrations/2026_05_20_000000_add_engine_columns_to_assigned_plans.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('assigned_plans')) return;

        Schema::table('assigned_plans', function (Blueprint $t) {
            if (! Schema::hasColumn('assigned_plans', 'idempotency_key')) {
                $t->string('idempotency_key', 64)->nullable()->after('ai_generation_id');
                $t->unique('idempotency_key', 'uniq_assigned_plans_idempotency');
            }
            if (! Schema::hasColumn('assigned_plans', 'plan_engine_version')) {
                $t->string('plan_engine_version', 16)->nullable()->after('idempotency_key');
                $t->index('plan_engine_version', 'idx_assigned_plans_engine_version');
            }
        });
    }

    public function down(): void {
        Schema::table('assigned_plans', function (Blueprint $t) {
            $t->dropUnique('uniq_assigned_plans_idempotency');
            $t->dropColumn('idempotency_key');
            $t->dropIndex('idx_assigned_plans_engine_version');
            $t->dropColumn('plan_engine_version');
        });
    }
};
```

**Propiedades**:
- 100% aditiva: agrega columnas nullable + 1 unique index + 1 index normal.
- Idempotente: `hasColumn` guards previenen re-runs accidentales.
- Cumple ADR-0003: no toca tipos existentes, no drop, no rename.
- Vanilla PHP NO la requiere: las columnas nuevas son nullable, el `INSERT` vanilla no las menciona y MySQL acepta default NULL.
- Tiempo estimado en prod: ~5s (186 filas, sin reescribir data).
- Rollback safe: el `down()` revierte sin pérdida de data porque solo borra columnas que no existían antes.

**Cuándo se ejecuta**: **Sprint 4, día 1** (no Sprint 0). Justo antes de arrancar el rollout con el primer cliente piloto. Daniel da el go con el cliente elegido manualmente y la migration corre primero.

**Cómo se ejecuta**: 
```bash
# Local primero
php artisan migrate

# Push a GitHub
git add database/migrations/2026_05_20_000000_add_engine_columns_to_assigned_plans.php
git commit -m "feat(engine-v2): add idempotency_key + plan_engine_version columns (aditiva)"
git push origin main

# Prod: EasyPanel → silvia-gitpull-load → console del container:
cd /code && php artisan migrate --force
```

---

## 3. Cómo se marca un plan como "generado por motor v2"

**Decisión**: columna `plan_engine_version` en `assigned_plans` (no en `clients`).

**Por qué columna en `assigned_plans` y NO en `clients`**:

| Alternativa | Pro | Contra |
|-------------|-----|--------|
| Columna en `assigned_plans` (elegida) | Atómico por plan; mix por cliente (este plan v2, próximo manual); audit completo de qué engine generó qué | Requiere migration aditiva |
| Columna `plan_engine_optin` en `clients` | Sin migration en `assigned_plans` | Decisión por-cliente rígida — no permite mezcla; un cliente con flag ON no puede recibir un plan manual de emergencia |
| Tabla nueva `plan_engine_optin_clients` | Listado explícito de candidatos | Overhead de tabla extra para algo que es un boolean+versión |

**Valores de `plan_engine_version`**:
- `NULL` → plan generado por el flujo manual (default)
- `"v2.0"` → motor v2 inicial
- `"v2.1"` → motor v2 con cambios (post primer iteración de rules/prompts)

Versionar el motor permite trazabilidad: si se cambia el prompt entre v2.0 y v2.1, se puede correlacionar calidad por versión.

---

## 4. Killswitch operativo

Variable de entorno en `.env`:

```
# Cuando esté en false, el motor v2 NO genera nada — vuelve al flujo manual
WC_ENGINE_V2_ENABLED=false
```

**Comportamiento por valor**:

| Valor | Efecto |
|-------|--------|
| `false` (default) | Cualquier llamada al motor v2 (CLI, slash command) responde "motor desactivado, usa flujo manual" sin tocar DBs ni LLM |
| `true` | Motor v2 activo. Los runs proceden normalmente |

**Quién puede tocar el flag**: solo Daniel (es .env local de su laptop).

**Cómo se aplica**: lectura en cada arranque del orchestrator:

```php
final class PlanEngineOrchestrator {
    public function executeJob(array $rawInput, string $createdBy): PlanEngineRun {
        if (! config('plan_engine.enabled')) {
            throw new EngineDisabledError(
                "Motor v2 desactivado vía WC_ENGINE_V2_ENABLED. Usá flujo manual."
            );
        }
        // ... resto del flujo
    }
}
```

**Por qué env var y NO feature flag DB-driven**: necesitamos un kill switch que funcione **incluso si la DB local está rota**. Env var es la opción más resiliente.

---

## 5. Plan de rollout — 6 sprints

| Sprint | Semana | Entregable principal | Clientes afectados | Riesgo a prod |
|--------|--------|----------------------|-----------------------|---------------|
| **0** | 1 | DB `wellcore_kb` local instalada · seed inicial (7 metodologías, 50 ejercicios, 15 principios, 5 templates) · env var killswitch · **(SIN migration a prod)** | 0 | **Cero** (todo local) |
| **1** | 2 | **Linter aislado** corre contra 5-10 fixtures de planes reales (Cristian roto + 5 buenos) · golden test asserts | 0 | **Cero** (no toca prod) |
| **2** | 3 | Stages INTAKE + SELECT + COMPOSE con LLM real (Sonnet+Haiku) · output a archivo, NO INSERT · revisión manual de 3 JSONs generados | 0 | Cero |
| **3** | 4 | Stages PERSIST + VERIFY · orchestrator completo con cleanup · **dry-run** a archivo (sin INSERT real todavía) | 0 | Cero |
| **4** | 5 | **Día 1: migration aditiva** (idempotency_key + plan_engine_version) → **Rollout Fase 1** — 1 cliente piloto que Daniel elige manualmente · `plan_engine_version='v2.0'` · ojo humano sobre el resultado · primera medición de métricas | 1 | **Medio** (1 ALTER + 1 plan real en prod) |
| **5** | 6 | **Rollout Fase 2** — 3 clientes adicionales (1 entrenamiento + 1 nutrición + 1 combinado) · observación semanal · iteración de rules según findings | 4 acumulado | Medio |
| **6+** | 7-10 | **Rollout Fase 3** — gradual a 15-25 clientes (renovaciones primero, nuevos segundo) · **Daniel informa a Anderson** · activar `corpus_embeddings` (RAG) · LLM review rules opt-in | 25+ | Decrece |
| **10+** | continuo | Steady state · iteración de rules · agregar metodologías nuevas semanales | Universo completo | Bajo |

**Total para tener 100% del universo cubierto: ~10 semanas**. Coincide con el "5-6 semanas para MVP" del prompt original de Daniel + 4 semanas de ramp-up cuidadoso.

---

## 6. Quién entra y quién NO al motor v2

### Entran (en orden de prioridad por sprint)

| Sprint | Criterio de elegibilidad | Estimado de clientes | Quién elige |
|--------|--------------------------|----------------------|-------------|
| 4 | 1 cliente piloto · perfil "clásico" (intermedio, 4-5 días, sin lesiones complejas) | 1 | **Daniel manual** (decisión 2026-05-16) |
| 5 | 3 clientes adicionales: 1 entrenamiento solo, 1 nutrición solo, 1 combinado | 3 | Daniel manual |
| 6 | Todas las renovaciones de planes Esencial + Método + Entreno_solo + Nutricion_solo | ~15-20 | Auto (query por `assigned_plans.expires_at` próximo) + Daniel veta |
| 7+ | Clientes nuevos onboarding (después de pago Wompi) | progresivo | Auto + Daniel veta |

### NO entran (en MVP)

| Cohorte | Razón | Cuándo revisitar |
|---------|-------|------------------|
| Planes `rise` | Schema distinto (`rise_programs.personalized_program`), no `assigned_plans` | Sprint 12+ |
| Planes Elite con `bloodwork` | Tabla `bloodwork_results` separada, escalation a Opus no calibrada | Sprint 10+ |
| Clientes `status != activo` | Sin sentido generar plan para cliente en pausa | Cuando se reactiven |
| Clientes con plan generado en últimas 72h | Hay plan reciente válido, esperar renovación natural | — |

---

## 7. Plan de rollback (3 niveles)

### Nivel 1 — plan individual roto (escenario más probable)

**Síntoma**: 1 cliente recibió plan motor v2 que el linter aprobó, pasó verify, pero el cliente reporta que algo no le calza (ej. "el ejercicio X me lastima la rodilla y no debería estar acá").

**Acción** (post Sprint 4, con `plan_engine_version` ya en prod):
```sql
-- Desactivar el plan roto
UPDATE assigned_plans SET active=0 WHERE id=<plan_v2_id>;

-- Reactivar el plan anterior manual del mismo plan_type del cliente (si existe)
UPDATE assigned_plans
SET active=1
WHERE client_id=<X> AND plan_type=<Y>
  AND id = (SELECT id FROM (
    SELECT id FROM assigned_plans
    WHERE client_id=<X> AND plan_type=<Y> AND active=0 AND plan_engine_version IS NULL
    ORDER BY created_at DESC LIMIT 1
  ) AS t);

-- Invalidar caches
-- Cache::forget("client_plan_v3_{X}"); etc.
```

O regenerar manual con el flujo "Claude Code humano" tradicional.

**Tiempo total**: <5 min.

**Nota**: durante Sprints 0-3 (sin columna `plan_engine_version`) el rollback se hace por `id` del plan + Daniel sabe cuáles fueron v2 porque están registrados en `wellcore_kb.plan_engine_runs.assigned_plan_id`.

### Nivel 2 — bug sistémico afectando varios planes recientes

**Síntoma**: Daniel descubre que una rule del linter tenía un bypass o que COMPOSE estaba generando algo subtly malo. N planes ya están en producción.

**Acción**:
```sql
-- Identificar los afectados
SELECT id, client_id, plan_type, created_at
FROM assigned_plans
WHERE plan_engine_version = 'v2.0'
  AND created_at > '2026-XX-XX'  -- desde que se detectó el bug
ORDER BY created_at DESC;

-- Para cada uno: desactivar + alertar al cliente + regenerar
-- (script en bootstrap/rollback_engine_v2_batch.php)
```

**Mientras tanto**:
```bash
# Killswitch off
echo 'WC_ENGINE_V2_ENABLED=false' >> .env
```

**Tiempo total**: 5-15 min para N planes.

### Nivel 3 — motor v2 completamente roto (worst case, raro)

**Síntoma**: cualquier intento de generar plan crashea, o genera basura, o tumba el LLM.

**Acción inmediata**:
```bash
# 1. Killswitch en .env
sed -i 's/WC_ENGINE_V2_ENABLED=true/WC_ENGINE_V2_ENABLED=false/' .env

# 2. Restart de cualquier worker (si hay queue corriendo)
php artisan queue:restart

# 3. Reportar a quien corresponda
```

**Recovery**:
- Daniel vuelve al flujo manual "Claude Code humano" (sigue funcionando, no se desconectó).
- Fix del motor v2 en branch separado, deploy a producción de la laptop solo cuando esté probado contra fixtures + 1 cliente piloto interno.

**Tiempo de "estoy de vuelta operativo": <1 min** (env var toggle).

---

## 8. Métricas — el dashboard de salud del motor

Las primeras 4 semanas post-Fase 1, Daniel mira el dashboard cada mañana.

Las métricas se derivan de `wellcore_kb.plan_engine_runs` (tabla del doc 04 §10):

| Métrica | Cálculo | Target | Alerta si... |
|---------|---------|--------|--------------|
| **Token cost promedio por plan** | `AVG((prompt_tokens_used - cached_tokens_used) * $3/1M + cached_tokens_used * $0.30/1M + completion_tokens_used * $15/1M)` para Sonnet, ajustado por Haiku | <$0.30 | >$0.50 |
| **Reducción vs baseline manual** | `(baseline_avg - engine_avg) / baseline_avg` (baseline = $1.35 estimado del doc 05 §8) | >70% | <60% |
| **% planes que pasan linter primer intento** | `COUNT(status='completed' AND JSON_EXTRACT(lint_findings_json,'$.errorCount')=0) / COUNT(*)` | >85% | <70% |
| **% planes que pasan verify visual** | `COUNT(JSON_EXTRACT(verify_result_json,'$.visualOk')=true) / COUNT(status='completed')` | >90% | <80% |
| **Tiempo medio de generación** | `AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at))` | <90s | >180s |
| **Tasa "Daniel editó a mano post-generación"** | manual flag en `daniel_edited_after_generation` boolean (Daniel marca con `php artisan kb:mark-edited --run=421`) | <20% | >40% |
| **Casos `requires_human_review`** | `COUNT(status='requires_review') / COUNT(*)` | <10% | >25% |

### Comando CLI para ver el dashboard

```bash
php artisan kb:health --window=7d
```

Output esperado:

```
═══ MOTOR V2 HEALTH (últimos 7 días) ═══════════════════════
  4 runs · 4 completed · 0 failed · 0 requires_review

  COST
    Avg tokens/run:      8420 input + 2150 cached + 2890 output
    Avg cost/run:        $0.21  ✓ (target <$0.30)
    Reducción baseline:  84%    ✓ (target >70%)

  QUALITY
    Linter pass 1st try: 4/4 (100%)  ✓ (target >85%)
    Verify visual ok:    4/4 (100%)  ✓ (target >90%)
    Daniel editó:        0/4 (0%)    ✓ (target <20%)

  PERFORMANCE
    Avg generation time: 78s          ✓ (target <90s)
    Slowest run:         112s (id=425, methodology=body_part_split_5d)

  TOP LINT FINDINGS (warnings)
    heur_monotonia_3x12:  1 finding
    heur_cardio_excessive: 1 finding

═══════════════════════════════════════════════════════════
RESULT: ✓ Healthy
```

### Dashboard Vue local (Sprint 7+ opcional)

Si Daniel quiere visualización gráfica: página `/dev/engine-health` solo accesible desde `127.0.0.1` (no expuesta a prod). Charts de Recharts/Chart.js con time series de las métricas.

---

## 9. Notificación a coaches

**Política**: los coaches NO ven cambios en su panel. El motor v2 corre 100% local en la laptop de Daniel. Los coaches siguen asignando templates con `PlansManager::assignTemplate` igual que siempre.

**Cuándo decirle a Anderson** (coach principal):

| Momento | Mensaje |
|---------|---------|
| Sprint 4 (Fase 1) | NO se le dice nada. El motor v2 está generando 1 plan al mes — invisible. |
| Sprint 6 (Fase 3) | "Estamos calibrando un sistema interno que automatiza parte de la creación de planes. Vas a ver algunos planes con calidad similar o mejor a los manuales. Avísame si notás algo distinto en la estructura o la voz." |
| Sprint 10+ | "El sistema está estable. La mayoría de los planes nuevos los genera el motor; tú sigues pudiendo crear manual o ajustar después de que el motor termina." |

**Lo que Anderson NO necesita saber** (memoria `feedback_ia_confidencial.md`):
- Que es IA / Claude / Anthropic.
- Cómo funcionan las stages internamente.

**Lo que SÍ necesita saber**:
- Que algunos planes vienen de un sistema interno y que su feedback es bienvenido.

---

## 10. Migración del catálogo MD → `wellcore_kb` (Sprint 0-1)

Los 27 MDs viven en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\`. Para el motor v2 necesitamos extraer:

| Origen | Destino | Cantidad target |
|--------|---------|-----------------|
| `08-METODOLOGIAS.md` + intuición Daniel | `methodologies` | 7 metodologías iniciales |
| `05-LENGUAJE-Y-VOZ.md` + `08-METODOLOGIAS.md` (principios scattered) | `principles` | 15 principios |
| `20-EJERCICIOS-VARIACIONES-TECNICA.md` (catálogo 265 GIFs) + `assigned_plans.content` históricos | `exercise_metadata` | 50 ejercicios curados (los más usados) |
| `CASOS-REALES/*.json` (planes reales exitosos) | `plan_templates_local` | 5 templates iniciales |

**Process**:

```bash
# Sprint 0 día 2-3 — Claude Code propone seed migrations
php artisan kb:propose-seed --from=md --output=storage/kb-seed-proposal-<date>.sql

# Daniel revisa el SQL, edita lo que necesite
# Luego ejecuta:
php artisan migrate --database=kb --path=database/migrations-kb
mysql -u root wellcore_kb < storage/kb-seed-proposal-<date>.sql
```

**Los MDs NO se borran** — quedan como documentación humana de referencia. El motor v2 NO los lee directamente.

**Sincronización futura**: si Daniel cambia algo en los MDs (ej. agrega nombre oficial de fase nueva), tiene que reflejarlo también en `wellcore_kb` con un INSERT/UPDATE. Eventualmente se invierte: los MDs se actualizan desde `wellcore_kb` con un comando export.

---

## 11. Costos del rollout

**Costos directos del motor v2** (LLM):

| Sprint | Generaciones | Cost/gen | Total sprint |
|--------|--------------|----------|--------------|
| 0-3 | 0 (solo desarrollo) | — | $0 |
| 4 | 1-3 (piloto + tests) | $0.25 | <$1 |
| 5 | 5-10 | $0.25 | <$3 |
| 6 | 20-30 | $0.20 | <$6 |
| 7+ | ~10/semana steady | $0.18 | $7/mes |

**vs baseline**: el flujo "Claude Code humano" actual consume estimado ~$30-50/mes en tokens. Motor v2 reduce a ~$7-10/mes. **Ahorro neto anual: ~$300-500**.

**Costos indirectos**:
- Tiempo de Daniel desarrollando el motor: 5-6 semanas part-time.
- Costo de cómputo Voyage AI para embeddings (Sprint 3+): ~$5/mes para corpus de tamaño WellCore.

**Costos evitados**:
- Caso Cristian re-trabajado: ~2-4 horas de Daniel cada vez. Si pasa 1 vez al mes = 24-48h/año.

---

## 12. Decisiones aplicadas (2026-05-16)

1. ~~**Migration aditiva en Sprint 0**~~ ✅ **DECIDIDO**: postergada al **Sprint 4**. Sprints 0-3 corren sin tocar prod. Mitigación durante esos sprints: file lock del orchestrator previene duplicados accidentales + dry-run a archivo, no INSERT real.
2. ~~**Comunicación a coaches**~~ ✅ **DECIDIDO**: Daniel informa a Anderson en **Sprint 6 / Fase 3** (no antes). Mensaje: "estamos probando un sistema interno que automatiza parte de la creación de planes — avísame si notás algo distinto".
3. ~~**Cliente piloto Sprint 4**~~ ✅ **DECIDIDO**: Daniel lo elige manualmente. Cuando arranque Sprint 4, Daniel me pasa el `client_id` y procedemos.

## 13. Lo que NO está resuelto en este doc

1. **Coexistencia con plan_engine v2.1, v2.2, …** — cuando publiques v2.1, ¿los runs v2.0 anteriores se quedan marcados como v2.0 o se "promueven"? Política recomendada: se quedan como v2.0 (auditoría). Pendiente confirmación.
2. **Backfill de `plan_engine_version=NULL` para planes históricos** — no hace falta. Los runs viejos quedan NULL = manual. Cero acción.
3. **Schema diff entre prod y test** — la memoria `reference_test_db_schema_drift.md` dice que la DB de test se desincroniza con prod. Antes del Sprint 0, sincronizar para que la migration del §2 corra sin sorpresas en CI.
4. **Notificaciones automáticas a clientes cuando se les asigna plan v2** — por ahora no. El cliente ve el nuevo plan en la UI; Daniel le manda WhatsApp manual. Sprint 12+ podría automatizar.
5. **A/B test motor v2 vs manual** — interesante pero complejo (mismo input, dos engines, comparar). Solo si las métricas básicas del §8 nos dejan con dudas sobre calidad. Por ahora confiamos en el linter + verify.

## Próximo doc

**`08-weekly-loop-daniel.md`** — Manual operativo de Daniel:
- Workflow semanal: cómo agregar metodología nueva, capturar template real, re-embedear corpus.
- Workflow caso-por-caso: cómo generar UN plan para UN cliente con el motor v2 (CLI + slash commands).
- Slash commands a crear: `/plan-create`, `/methodology-add`, `/lint-rule-add`, `/corpus-reindex`, `/engine-health`.
- "Salud del cerebro": dashboard CLI + opcional Vue local.
- Definición operativa de "motor LLM local" — qué hace reproducible, qué no.

Espero OK de Daniel para avanzar al doc 08.
