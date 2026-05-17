# 09 — Open questions, risks, y backlog

> Documento de diseño. Lo que NO está decidido + riesgos + decisiones que Daniel debe tomar para Sprint 0.

## TL;DR

Este doc cierra los 9 docs del motor v2 listando explícitamente **lo que no está resuelto**. Hay **5 decisiones de producto** que necesito de Daniel antes del Sprint 0 (RISE? bloodwork? ciclo? coaches no-Anderson? medir baseline real?), **5 asunciones técnicas** que debería validar (versión MySQL Herd, RAM, espacio disco, etc.), **6 áreas del repo** donde leí poco y podría estar errando (tests existentes, Vue SPA frontend, AIService, AdminController completo, etc.), y un **catálogo consolidado de 18 preguntas abiertas** distribuidas entre 4 horizontes (Sprint 0 críticas / Sprint 1-3 dev / decisiones de producto / Sprint 6+ diferidas). Más un **registro de 8 riesgos** clasificados por severidad × probabilidad. Más una **acción concreta**: medir el cost real del flujo actual con las próximas 3 generaciones que se hagan — sin eso, no se puede celebrar el "-70% token cost" con honestidad. Sin licencias problemáticas: HF es Apache 2.0 y solo portamos el patrón conceptual (stages + linter como gate), no código TypeScript — no hay obligación de atribución. Para arrancar el Sprint 0, leer el §11 (acción inmediata) — son 4 tareas concretas en orden.

---

## 1. Decisiones de producto — APLICADAS 2026-05-17 (defaults recomendados)

Daniel autorizó aplicar los defaults recomendados ("haz lo que falte" 2026-05-17). Decisiones registradas:

| # | Decisión | Aplicada |
|---|----------|----------|
| **D1** | ¿RISE entra en MVP? | ❌ **NO en MVP** — RISE es premium y baja frecuencia (~2-3 clientes), el ROI de automatizar es bajo. Mantener flujo manual. Diferido a Sprint 12+. |
| **D2** | ¿Bloodwork Elite entra en MVP? | ❌ **NO en MVP** — solo 1-2 clientes Elite con bloodwork, manual sigue viable. Diferido a Sprint 10+. |
| **D3** | ¿`ciclo` hormonal entra en MVP? | ✅ **SÍ** — el costo marginal es bajo (1 metodología más en el seed) y demuestra que el motor cubre Elite. El seed inicial agrega 1 metodología `ciclo_hormonal_basico` (vertical=ciclo). |
| **D4** | ¿Coaches distintos de Anderson? | ✅ **Voz neutra "tu coach"** siempre. Firma queda con `assigned_by` admin_id. La voz WellCore es marca (memoria `feedback_voz_wellcore_no_anderson.md`). Cualquier coach puede asignar planes generados sin necesidad de adaptar la voz. |
| **D5** | ¿Medir baseline real? | ✅ **SÍ** — template creado en `docs/wellcore-engine-v2/baseline-manual-measurements.md`. Daniel completa las próximas 3 generaciones de planes manuales con tokens y tiempos reales. Resultado se compara contra cost del motor v2 en Sprint 4. |

**Implicaciones para el seed inicial del Sprint 0**:
- 8 metodologías (no 7) — incluye `ciclo_hormonal_basico` por D3
- Sin templates RISE ni Bloodwork
- Voz de todos los principios + tools usa "tu coach" / "te recomendamos" / "Daniel" — nunca firma "Anderson" en sistema
- Template baseline esperando primeras 3 mediciones de Daniel

---

## 2. Asunciones técnicas que debería validar

| # | Asunción | Cómo verificar | Riesgo si está mal |
|---|----------|----------------|--------------------|
| **A1** | Herd local de Daniel tiene MySQL 8 (no 5.7) | `mysql --version` desde terminal | Bajo — el schema del kb es compatible con 5.7+, solo perdemos algunas features (window functions) |
| **A2** | Laptop de Daniel tiene ≥8GB RAM libre para correr LLM client + Chrome MCP + corpus_embeddings cosine similarity | Activity Monitor / Task Manager mientras corre 1 plan | Medio — si quema swap, generación tarda 5x más; mitigación: aumentar swap o diferir corpus_embeddings |
| **A3** | Anthropic API key actual (`ANTHROPIC_API_KEY` en `.env`) tiene presupuesto suficiente | Anthropic Console → billing | Bajo — el cost estimado del motor v2 es ~$5-10/mes en steady state |
| **A4** | Voyage AI pricing actual (Sprint 3+ RAG) sigue siendo ~$0.06/MTok para 3.5 | https://docs.voyageai.com/pricing | Bajo — alternativa Anthropic embeddings, costo similar |
| **A5** | Disco local de Daniel tiene ≥5GB libres para corpus + screenshots verify + backups GPG | `df -h` | Bajo — limpieza periódica de screenshots viejos resuelve |
| **A6** | El `WC_ENGINE_V2_ENABLED` env var no entra en conflicto con otras config existentes | `grep WC_ENGINE .env` | Cero — variable nueva, namespaced |
| **A7** | Chrome MCP funciona offline (sin reconectar a un server) cuando Daniel está en avión / sin internet | Test desconectar wifi mid-generation | Medio — VERIFY stage falla, motor reporta `BrowserSessionError`, plan ya está INSERTED. Mitigación: VERIFY es gate blando |

---

## 3. Áreas del repo wellcore-laravel donde leí poco

Cuanto más áreas leídas, más confianza en el diseño. Estas son las áreas que **no profundicé** y donde mis asunciones podrían ser incorrectas:

| # | Área | Por qué importa | Riesgo concreto |
|---|------|-----------------|-----------------|
| **R1** | `app/Services/AIService.php` (solo lo mencioné por memoria `reference_sse_pattern.md`) | El motor v2 reutilizaría esta clase para Tool Use. Si tiene quirks (rate limiting, retry policy distinta a la que diseñé) el motor v2 los hereda. | Posibles conflictos con el retry policy del doc 04 §15.3 |
| **R2** | `app/Http/Controllers/Api/AdminController.php` (3000+ líneas, leí solo los 6 INSERTs de assigned_plans) | El endpoint `/api/v/admin/clients/{id}/assign-plan` puede tener middleware, validation, side effects que ignoro | Bajo — el motor v2 hace INSERT directo PDO, no usa el endpoint |
| **R3** | `resources/js/vue/pages/Client/PlanViewer.vue` + `NutritionPlan.vue` (no leí código, asumí basado en MDs) | Las rules del linter (especialmente schema) asumen el shape exacto que la UI consume. Si hay fallbacks/parsing distinto a los MDs, mis rules pueden generar falsos positivos o pasar bugs | Medio — Sprint 1 (linter contra fixtures reales) lo va a destapar |
| **R4** | `database/migrations/` (leí 4 de assigned_plans, hay ~50 en total) | Pueden existir constraints, triggers, o cosas que afectan al INSERT que asumí limpio | Bajo — Sprint 0 sync schema prod↔test va a destapar inconsistencias |
| **R5** | `app/Models/PlanTemplate.php` (solo lo mencioné) | El panel coach usa este modelo. El motor v2 NO toca esa tabla pero si Daniel quiere "guardar plan motor v2 como template también para reuso", hace falta integración | Bajo — fuera de MVP |
| **R6** | `tests/` (no leí los tests existentes) | El motor v2 va a agregar tests nuevos. Si los tests existentes cubren cosas que el motor v2 rompería (no debería, pero), Sprint 1 lo destapa | Bajo — tests existentes probablemente no cruzan path con motor v2 |

**Acción mitigadora**: cada uno de estos puntos lo cubre el agente especialista del Sprint correspondiente:
- la-02-backend lee R1 + R5 en Sprint 0
- la-03-vue3 lee R3 en Sprint 1 (cuando se construye el linter)
- la-06-database lee R4 en Sprint 0 (cuando sincroniza schema)
- la-14-testing lee R6 en Sprint 1 (cuando se agregan tests del motor)

---

## 4. Licencias y compliance

### 4.1 HyperFrames (Apache 2.0)

`C:\Users\GODSF\Music\LIKE A PRO DESING STUDIO\packages\` — HF es Apache 2.0.

**Lo que portamos del HF al motor v2**:
- ✅ El **patrón conceptual** de 6 stages aisladas con cleanup centralizado en orchestrator (doc 04 §9)
- ✅ La **semántica** de linter como gate (`ok = errorCount === 0`) (doc 06 §1)
- ✅ La **estructura del `LintFinding`** (code/severity/message/jsonPath/fixHint) (doc 04 §6)
- ✅ La **semántica de `safe_cleanup`** que swallow secondary errors para no enmascarar el original (doc 04 §9)

**Lo que NO portamos**:
- ❌ Código TypeScript literal (cero copy/paste)
- ❌ Algoritmos específicos de video (frame capture, ffmpeg, HDR)
- ❌ Nombres de funciones HF directamente

**Conclusión legal**: Apache 2.0 permite uso de las ideas sin atribución (es la finalidad de Apache 2.0 — open source liberal). NO necesitamos `NOTICE` file ni mencionar HF en producción. Sí podemos mencionarlo en docs internos como inspiración (lo cual estos docs ya hacen).

### 4.2 Anthropic API

Ya en uso por WellCore vía `AIService`. Los terms cubren uso comercial. El motor v2 no agrega nuevo riesgo legal.

### 4.3 Voyage AI (Sprint 3+ RAG)

Si activamos RAG con Voyage embeddings:
- Terms permiten uso comercial sin atribución
- Pricing per-token, sin minimum
- Sin lock-in (los embeddings se guardan en `wellcore_kb`, podemos cambiar a OpenAI ada-002 o local sentence-transformers en cualquier momento)

### 4.4 Datos de clientes en el corpus

Cuando capturamos planes reales como templates (doc 08 §5), **anonimizamos**: nombres → genéricos, peso/altura → rangos, ciudad → región. Esto cubre la regla "datos personales no salen del scope del cliente".

---

## 5. Preguntas abiertas consolidadas (de docs 01-08)

### 5.1 Críticas para Sprint 0 (resolver antes de empezar)

| Q | Doc origen | Quién decide |
|---|-----------|--------------|
| ¿RISE en MVP? (D1 arriba) | doc 02 §7 | Daniel |
| ¿Bloodwork en MVP? (D2 arriba) | doc 02 §7 | Daniel |
| ¿`ciclo` en MVP? (D3 arriba) | doc 02 §7 | Daniel |
| ¿Voz coach Anderson vs neutra? (D4 arriba) | implícito | Daniel + memoria existente |
| ¿Medimos baseline real? (D5 arriba) | §8 abajo | Daniel |
| Sync schema prod↔test antes de Sprint 4 (mig aditiva) | doc 07 §13 | la-06-database |
| Modelos Eloquent del kb | doc 03 §7 | la-02-backend en Sprint 0 |

### 5.2 Sprint 1-3 development (resolver durante desarrollo)

| Q | Doc origen | Cómo se resuelve |
|---|-----------|-------------------|
| Performance benchmark linter contra plan grande | doc 06 §14 | Sprint 1 — medir contra Cristian fixture |
| Performance benchmark stages | doc 03 §7 | Sprint 2 — medir end-to-end |
| Catálogo completo patrones marketing prohibidos | doc 06 §14 | Sprint 1-3 — iterar según observation |
| Threshold fuzzy match auto-fix (0.85 actual) | doc 06 §14 | Sprint 1-3 — ajustar si falsos pos/neg |
| Webhooks post-run | doc 04 §15.5 | Sprint 6+ — solo si Daniel los quiere |
| Few-shot vs zero-shot en COMPOSE | doc 05 §10.5 | Sprint 2 — A/B test sobre fixtures |
| Cost monitoring real-time (abort si runaway) | doc 05 §10.4 | Sprint 1 — implementar en orchestrator |

### 5.3 Decisiones de producto pendientes (cuando estés listo)

| Q | Doc origen | Default mientras decidís |
|---|-----------|--------------------------|
| Coaches distintos de Anderson usan el motor? | doc 07 §9 | NO en MVP, solo Daniel ejecuta |
| Sincronización corpus entre 2 laptops (Daniel + Anderson) | doc 08 §12 | No aplicable hasta D4 sea SÍ |
| Notificaciones automáticas al cliente post-asignación | doc 07 §13 | NO — Daniel manda WhatsApp manual |
| Coexistencia v2.0/v2.1 — ¿runs viejos se "promueven"? | doc 07 §13 | NO — quedan marcados con su versión |
| Rules cross-vertical (entrenamiento+nutricion consistencia) | doc 06 §14 | NO en MVP |
| Multi-tenant (vender el motor a otros estudios) | doc 03 §7 | NO premature |

### 5.4 Diferidas (Sprint 6+ o más allá)

| Q | Doc origen | Cuándo revisitar |
|---|-----------|------------------|
| Embedding model exacto (Voyage 3.5 vs lite vs Anthropic) | doc 05 §10.1 | Sprint 3+ cuando se active RAG |
| Confidence marker `_low_confidence_` para escalation Opus | doc 05 §10.2 | Sprint 4 cuando arranque rollout real |
| LLM review rules opt-in | doc 06 §7 | Sprint 6+ |
| Slash command postmortem | doc 08 §12 | Sprint 6+ |
| Voice interface | doc 08 §12 | si se justifica |
| A/B test motor v2 vs manual | doc 07 §13 | si métricas básicas dejan dudas |

**Total: 25 preguntas abiertas distribuidas en 4 horizontes**. De estas, **7 son blockers de Sprint 0** (sección 5.1).

---

## 6. Registro de riesgos (severidad × probabilidad)

| # | Riesgo | Severidad | Probabilidad | Mitigación |
|---|--------|-----------|--------------|------------|
| **RG1** | Motor v2 produce plan que pasa linter+verify pero el cliente lo nota raro | Media | Media | Dashboard salud + Daniel review primeros 4 sprints + rollback nivel 1 (<5 min) |
| **RG2** | Migration aditiva del Sprint 4 rompe vanilla PHP en producción | Alta | Muy baja | Aditiva pura (columnas nullable + indexes), vanilla no la consulta |
| **RG3** | Herd MySQL local de Daniel se cae mid-generation | Baja | Baja | File lock + cleanup `finally` + retry manual no genera duplicados (idempotency_key post-Sprint 4) |
| **RG4** | Prompt caching no hace el hit rate esperado → cost real >$0.50/plan | Media | Media | Monitoreo `cost/run` desde día 1 + alerta en `/engine-health` |
| **RG5** | LLM en loop costoso (retries infinitos o tools mal definidas) | Media | Baja | Cost budget per run + max 1 retry por tool (doc 04 §15.3) |
| **RG6** | Credenciales prod en `.env` local de Daniel se filtran | Alta | Baja | Ya existe el risk con el flujo actual, no aumenta |
| **RG7** | Prompt injection vía `intake.injuries` | Baja | Muy baja | Defensa 2 capas (doc 06 §8) + motor corre local, no expuesto |
| **RG8** | Scope creep — Daniel quiere agregar feature mid-Sprint que requiere rediseño | Media | Alta | Docs 04-08 son spec versionada — cualquier cambio requiere update de spec primero antes de código |

**Total risk score** (sum severidad × probabilidad asignando 1-3): **Bajo-Medio agregado**. Ningún riesgo individual es "Alta × Alta".

---

## 7. Baseline real del flujo actual — medición ANTES del Sprint 4

Para celebrar honestamente el "-70% cost", necesitamos baseline real. Template para que captures las próximas 3 generaciones que hagas con el flujo manual actual:

```
═══ BASELINE MANUAL — Generación #1 ═══
Fecha: YYYY-MM-DD
Cliente: ____ (id ____)
Vertical: ____

Tokens según Claude Code:
  Input tokens:      _____
  Output tokens:     _____
  Total cost:        $___ (calcular según pricing del modelo usado)

Tiempo:
  Intake completion: ___ min
  Lectura MDs:       ___ min
  Diseño:            ___ min
  Drafting JSON:     ___ min
  Validación:        ___ min
  Total:             ___ min

Resultado:
  ¿Pasó verify visual primer intento? SÍ/NO
  ¿Daniel editó post-generación? SÍ/NO + qué editó
```

Hazlo en 3 generaciones distintas (idealmente 1 entrenamiento + 1 nutrición + 1 combinado) y guárdalo en `docs/wellcore-engine-v2/baseline-manual-measurements.md`. Ese es el número contra el cual mide el motor v2.

**Por qué importa**: si tu baseline real es $0.60 (no $1.35 estimado), el motor v2 a $0.21 es "-65%", no "-87%". Sigue siendo bueno, pero honestidad > marketing interno.

---

## 8. Roadmap post-MVP (Sprint 10+ — backlog explícito)

Lista de cosas que NO entran en MVP pero quedan reconocidas:

### Sprint 10-12 (calibración + features secundarias)
- LLM review rules opt-in (doc 06 §7)
- RAG completo con `corpus_embeddings` activo (doc 03 §3.8)
- Slash command `/plan-postmortem` (doc 08 §12)
- Vue local dashboard `/dev/engine-health` (doc 08 §10.2)
- Snapshot YAML de lint rules (doc 06 §10)

### Sprint 12+ (expansión)
- RISE en motor v2 (si D1 cambia)
- Bloodwork Elite en motor v2 (si D2 cambia)
- Coaches distintos de Anderson (si D4 cambia)
- Sincronización corpus entre laptops (si D4 cambia)
- Notificaciones automáticas WhatsApp post-asignación

### Backlog conceptual (sin sprint asignado)
- Voice interface (Sprint 20+ si justificado)
- A/B test motor v2 vs manual (solo si métricas básicas dejan dudas)
- Multi-tenant (premature hasta validar que el motor escala)
- Webhooks post-run para integraciones externas

---

## 9. Cierre de cada doc — recap rápido

| Doc | Estado | Decisiones tomadas en el doc |
|-----|--------|------------------------------|
| 01 — HF pattern extraction | ✅ | (sin decisiones — solo extracción) |
| 02 — WellCore current state | ✅ | DESCRIBE ejecutado, cleanup 5 filas plan_type='', drift `version` resuelto, `ai_generation_id`=NULL |
| 03 — Knowledge base schema | ✅ | 8 tablas, conexión `kb` separada, seeding Sprint 0 |
| 04 — Stages architecture | ✅ | Orchestrator dueño handles, file lock concurrencia, retry policy (1), idempotency_key |
| 05 — Decision engine | ✅ | 13 decisiones (9 reglas + 3 híbridas + 1 LLM), multi-model Sonnet+Haiku, prompt caching |
| 06 — Lint rules catalog | ✅ | 30 rules iniciales en 5 categorías, DB-driven, 8 con auto-fix |
| 07 — Strangler fig rollout | ✅ | Migration postergada Sprint 4, killswitch env var, Anderson informado Sprint 6, cliente piloto Daniel manual |
| 08 — Weekly loop Daniel | ✅ | 7 slash commands, 3 cron tasks, dashboard CLI |
| 09 — Open questions & risks | ✅ | Este doc — 5 D pendientes, 7 A asunciones, 6 R áreas no leídas, 8 RG riesgos, 25 Q abiertas |

---

## 10. Lo que NO está resuelto en este doc

Por definición, este doc lista lo que NO está resuelto en los otros 8. Lo que NO está resuelto en este mismo:

1. **Quién implementa qué en cada sprint** — el prompt original sugiere agentes específicos (la-02-backend, la-06-database, la-14-testing, etc.). Voy a proponerte mapping concreto en el Sprint 0 plan kick-off, no acá.
2. **Estimación de horas por sprint** — los sprints están en semanas elapsed, no en horas-hombre. Depende de si trabajás 4h/día o 8h/día y qué tan in-context te encontrás.
3. **Criterio de éxito formal del MVP** — sugiero: "10 planes generados consecutivos sin rollback ni `Daniel editó >50%`". Confirmar criterio antes del Sprint 4.

---

## 11. Acción inmediata para arrancar Sprint 0

Para empezar AHORA mismo, ejecuta esto en orden:

### 11.1 Respondé las 5 decisiones de producto del §1

Mandame 5 letras: `a/b/c` para D1, D2, D3, D4, D5. Si dudás, decí "default" y aplicamos mi recomendación.

### 11.2 Verifica las 7 asunciones técnicas del §2

```bash
# A1: MySQL version
mysql --version

# A2: RAM disponible
free -h    # Linux
top         # macOS / Windows

# A3: Anthropic budget
# Visitar https://console.anthropic.com/settings/billing

# A5: Disco libre
df -h
```

Pasame el output (o decí "todo OK").

### 11.3 Lanzá la captura de baseline real

Las próximas 3 generaciones de planes que hagas con flujo manual, completa el template del §7 y guarda en `docs/wellcore-engine-v2/baseline-manual-measurements.md`. Avísame cuando esté listo (o si lo prefieres saltar y arrancar igual con estimaciones).

### 11.4 Sprint 0 kickoff

Cuando termines 11.1-11.3, te paso el plan concreto del Sprint 0:
- Migration aditiva — `database/migrations-kb/2026_XX_XX_*_create_*.php` × 8 tablas (delegado a la-06-database)
- Seed inicial — extracción desde MDs hacia `wellcore_kb` (delegado a la-02-backend + la-11-ai-architect)
- Slash commands `/plan-create`, `/engine-health` boilerplate (delegado a Daniel/Claude Code)
- Setup `.env` con `KB_DB_*` + `WC_ENGINE_V2_ENABLED=false`

Tiempo estimado del Sprint 0: **1 semana part-time** (~10-15 horas Daniel + Claude Code).

---

## Próximo paso

**No hay próximo doc.** Este es el último.

Cuando completes las 3 acciones del §11, te entrego el plan ejecutable del Sprint 0 con tasks concretas, agentes asignados, y criterios de aceptación. A partir de ahí pasamos de "diseño en markdown" a "implementación en código" — manteniendo `architecture-reviewer` como gate antes de cualquier cambio que toque >3 archivos o introduzca nueva abstracción.

Listo para Sprint 0 cuando Daniel diga.
