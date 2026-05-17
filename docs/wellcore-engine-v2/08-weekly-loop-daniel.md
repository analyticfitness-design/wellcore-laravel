# 08 — Manual operativo de Daniel (weekly loop)

> Documento de diseño. Define cómo se opera el motor v2 día a día y semana a semana.

## TL;DR

El motor v2 se opera vía **slash commands de Claude Code** que envuelven artisan commands subyacentes — no escribes SQL ni recuerdas flags, solo dices `/plan-create`, `/methodology-add`, `/lint-rule-add`, `/corpus-reindex`, `/engine-health`, `/kb-snapshot`, `/plan-rollback`. Cada slash command es **conversacional**: Claude Code te hace 3-5 preguntas, valida, ejecuta, te muestra el output. **Reproducibilidad** es la propiedad clave — cada run queda registrado en `plan_engine_runs` con `intake_dto_json` completo, así que regenerar el mismo plan dentro de 6 meses es 1 comando. **3 cron tasks** automatizan lo que no quieres olvidar: backup semanal del corpus (domingo 3am), re-verify de GIFs (semanal lunes 4am), health snapshot diario (8am). Diferencia operativa vs flujo actual: hoy creas un plan tirando 27 MDs al contexto de Claude Code en cada sesión nueva; con motor v2, el contexto vive en `wellcore_kb` y se consulta con SQL. **El LLM solo orquesta, no improvisa** — si necesita conocimiento que no está en kb, te delega (stop conditions doc 05 §6).

---

## 1. Definición operativa del "motor v2 local"

Qué hace que sea **un motor** y no un **prompt elaborado**:

| Propiedad | Cómo se cumple |
|-----------|----------------|
| **Reproducible** | Cada run guarda `intake_dto_json` + `prompt_version` + `methodology_chosen_id` en `plan_engine_runs`. Reset a 0 y re-correr genera output equivalente (no bit-exact por LLM, pero estructuralmente igual). |
| **Versionado** | Prompts viven en código (`app/PlanEngine/Prompts/V1/`), metodologías en DB con `version` column, runs marcan `plan_engine_version='v2.0'`. Diff entre versiones es git diff + SQL diff. |
| **Auditable** | `plan_engine_runs` tiene 16 columnas de observability: timings por stage, tokens, errores, lint findings, screenshots. Daniel puede decir "regeneremos el plan del cliente X del 17 de mayo igual que entonces" y tenemos los inputs. |
| **No-improvisado** | El LLM solo se invoca dentro de tools con `tool_choice` forzado (doc 05 §2). No hay prompt libre que diga "haz un plan". Hay schemas que validan output. |
| **Operable sin reabrir 27 MDs** | El contexto que el LLM necesita se inyecta desde `wellcore_kb` por SQL — no relees MDs en cada sesión. |
| **Reversible** | Killswitch env var (doc 07 §4) + rollback 3 niveles (doc 07 §7). |

**Versus el flujo actual**:

| Aspecto | Flujo actual ("Claude Code humano") | Motor v2 |
|---------|-------------------------------------|----------|
| Contexto | Releer 27 MDs cada sesión | Query SQL a `wellcore_kb` |
| Reproducibilidad | Ninguna — cada sesión diferente | Cada run tiene snapshot completo de inputs |
| Versionado | "v2.5 del prompt" tracking manual | Columna `prompt_version` en runs |
| Error rate visible | Solo lo que el humano nota post-hoc | Linter + verify + dashboard |
| Cost por plan | ~$1.35 estimado | ~$0.18-0.26 estimado |
| Tiempo por plan | 45-100 min (intake + reading + drafting + verifying) | ~90 segundos máquina + ~5-10 min revisión humana |
| Cómo se agrega conocimiento | Editar uno de los 27 MDs y esperar a que Claude lo lea en próxima sesión | INSERT en `wellcore_kb` + hot-reload |

---

## 2. Slash commands a crear (catálogo MVP)

Estos slash commands viven en `.claude/commands/` del repo Laravel. Cada uno es un archivo markdown con la instrucción para Claude Code.

| Slash command | Para qué | Sprint en que se entrega |
|---------------|----------|--------------------------|
| `/plan-create` | Generar UN plan para UN cliente | Sprint 4 |
| `/plan-rollback` | Desactivar plan v2 + reactivar anterior | Sprint 4 |
| `/methodology-add` | Agregar metodología nueva al corpus | Sprint 5 |
| `/kb-capture-template` | Marcar un plan real como template exitoso | Sprint 5 |
| `/lint-rule-add` | Agregar lint rule nueva | Sprint 5 |
| `/corpus-reindex` | Re-generar embeddings tras cambio en kb | Sprint 6+ |
| `/engine-health` | Dashboard CLI de salud del motor | Sprint 4 |
| `/kb-snapshot` | Backup cifrado del corpus al repo | Sprint 5 |

### 2.1 `/plan-create` (el más usado — Sprint 4)

**Conversación esperada**:

```
> /plan-create

Claude: ¿Qué cliente?
> Lizeth Chávez, id 98

Claude: OK, Lizeth está activa, plan Esencial, último plan vencía 2026-05-15.
        Cargué su perfil. ¿Qué vertical? (entrenamiento / nutricion /
        combinado / suplementacion / habitos)
> combinado

Claude: ¿Algún cambio respecto a su perfil actual?
        - Objetivo: recomposicion (sin cambio)
        - Nivel: intermedio (sin cambio)
        - Días: 5 (sin cambio)
        - Lugar: gym
        - Lesiones: ninguna
> agregar dolor lumbar leve

Claude: Anotado: dolor lumbar leve. Voy a ejecutar el motor v2.
        Si todo va bien, vas a ver 3 INSERT (entrenamiento + nutricion +
        suplementacion). Estimado: 90 segundos.

[ejecuta]

Claude:
  ✓ INTAKE      (12ms)
  ✓ SELECT      (45ms)   methodology=body_part_split_5d (score 0.91)
                          razón: matches goal + level + days, lumbar excluye good_morning
  ✓ COMPOSE     (42s)    Sonnet+Haiku, $0.21 tokens, prompt_cache_hit=0.78
  ✓ VALIDATE    (180ms)  30 rules · 0 errors · 1 warning (cardio_excessive día 3)
  ✓ PERSIST     (250ms)  3 INSERT exitosos, ids [451, 452, 453]
  ✓ VERIFY      (28s)    Chrome MCP impersonó cliente, 12/12 checklist OK

  Plan listo. Run id 421 (wellcore_kb.plan_engine_runs).
  ¿Querés que envíe WhatsApp al cliente con el template estándar?
> no, lo mando yo después
```

**Implementación del slash command** (`.claude/commands/plan-create.md`):

```markdown
---
description: Generar un plan WellCore para un cliente usando el motor v2
---

Sos el orquestador del motor v2. El usuario quiere crear un plan.

1. Preguntá: nombre o id del cliente
2. Resolvé el client_id, mostrá perfil actual desde wellcore_fitness.clients
3. Preguntá: vertical (entrenamiento / nutricion / combinado / suplementacion / habitos)
4. Preguntá: cambios respecto al perfil actual (con defaults visibles)
5. Confirmá el intake antes de gastar tokens
6. Ejecutá: php artisan engine:create-plan --client-id={} --vertical={} --json-intake='{...}'
7. Mostrá output con timings, lint findings, ids de los INSERTs
8. Ofrecé enviar WhatsApp (opcional)
```

### 2.2 `/plan-rollback`

```
> /plan-rollback

Claude: ¿Plan id o cliente?
> plan 452

Claude: Plan 452 = nutrición de Lizeth Chávez (id 98), generado por motor v2 el 2026-05-17.
        ¿Confirmás desactivar?
> si

Claude: ✓ UPDATE assigned_plans SET active=0 WHERE id=452
        ✓ Plan anterior manual (id 410) reactivado
        ✓ Caches invalidados: client_plan_v3_98, wp:plan:98, dashboard:98
        ✓ Marcado plan_engine_runs.id=421 con rollback_at=NOW, rollback_reason=NULL
        Hecho. ¿Querés agregar razón del rollback para análisis?
> el cliente reportó dolor lumbar peor con la dieta alta proteína
Claude: Anotado.
```

### 2.3 `/methodology-add`

```
> /methodology-add

Claude: ¿Vertical? (entrenamiento / nutricion / suplementacion / habitos / ciclo)
> entrenamiento

Claude: ¿Slug (snake_case)?
> phul_4d

Claude: ¿Nombre display?
> PHUL (Power Hypertrophy Upper Lower) 4 días

Claude: ¿Días/semana min y max?
> 4 y 4

Claude: ¿Nivel target? (principiante / intermedio / avanzado / any)
> intermedio

Claude: ¿Goal target?
> hipertrofia

Claude: ¿Periodización default? Sugerencia para 4 semanas Adaptación→Hipertrofia→Fuerza→Peak.
        ¿Acepto o ajustás?
> aceptá

Claude: Descripción larga (markdown, principios + cuándo usar):
> [pegas texto]

Claude: Voy a INSERT en wellcore_kb.methodologies con status='experimental'.
        ¿Confirmás?
> si

Claude: ✓ INSERT id=8
        ¿Querés agregar methodology_rules ahora? (recomendado para que el SELECT stage la considere)
```

Sigue con preguntas de methodology_rules (al menos 1 hard filter por days_min/max).

### 2.4 `/lint-rule-add`

```
> /lint-rule-add

Claude: ¿Code de la rule (snake_case con prefijo categoría)?
> heur_uses_rpe_instead_rir

Claude: ¿Severity? (error / warning / info)
> warning

Claude: ¿Vertical? (entrenamiento / nutricion / suplementacion / habitos / ciclo / null=todos)
> entrenamiento

Claude: ¿Check type? (schema / heuristic / external_head / sql / llm_review)
> heuristic

Claude: ¿Descripción human-readable?
> Coach usó RPE como metodología cuando debería ser RIR

Claude: Te armo un check_definition_json base para heuristic con regex.
        ¿Qué pattern buscar?
> \bRPE\b en notas_coach, tips, ejercicios.notas

Claude: [genera JSON]

Claude: ¿Fix hint?
> RPE no es nuestra metodología, usar RIR (Reps In Reserve)

Claude: ¿Activar inmediato o dejar en observation (enabled=false)?
> observation 

Claude: ✓ INSERT id=31 (enabled=false)
        ¿Querés correr dry-run contra últimos 10 runs para ver cuántos habría flageado?
> si

Claude: [corre] Resultado: 0 de 10 runs habrían flageado. 
        Rule guardada. Cuando la quieras activar: UPDATE lint_rules SET enabled=1 WHERE id=31
```

### 2.5 `/engine-health`

```
> /engine-health

Claude: [ejecuta php artisan kb:health --window=7d]

═══ MOTOR V2 HEALTH (últimos 7 días) ═══════════════════════
  4 runs · 4 completed · 0 failed · 0 requires_review

  COST
    Avg tokens/run:      8420 input + 2150 cached + 2890 output
    Avg cost/run:        $0.21  ✓ (target <$0.30)
    Reducción baseline:  84%    ✓ (target >70%)

  QUALITY
    Linter pass 1st try: 4/4 (100%)  ✓
    Verify visual ok:    4/4 (100%)  ✓
    Daniel editó:        0/4 (0%)    ✓

  PERFORMANCE
    Avg generation time: 78s
    Slowest run:         112s (id=425, methodology=body_part_split_5d)

  TOP LINT FINDINGS (warnings)
    heur_monotonia_3x12:    1 finding
    heur_cardio_excessive:  1 finding

═══════════════════════════════════════════════════════════
RESULT: ✓ Healthy

¿Querés ver alguna métrica específica más en detalle?
```

### 2.6 `/kb-snapshot`

```
> /kb-snapshot

Claude: Voy a hacer backup cifrado del corpus a storage/.
        Necesito la passphrase GPG (no la guardes en el repo).
> [Daniel ingresa]

Claude: ✓ mysqldump --no-create-info --skip-triggers wellcore_kb | gzip | gpg → 
          storage/wellcore-kb-seed-2026-05-17.sql.gz.gpg (1.2 MB)
        ✓ git add storage/wellcore-kb-seed-2026-05-17.sql.gz.gpg
        ✓ git commit -m "kb: snapshot 2026-05-17"
        ¿Querés que pushee a GitHub?
> si

Claude: ✓ git push origin main
        Snapshot disponible. En laptop nueva: git pull + gpg -d | mysql wellcore_kb
```

### 2.7 `/corpus-reindex` (Sprint 6+)

```
> /corpus-reindex

Claude: ¿Solo lo que cambió desde la última vez, o todo?
> solo lo nuevo

Claude: Detecto 3 cambios desde último reindex (2026-05-14):
        - methodology id=8 (phul_4d) — nueva
        - plan_template_local id=12 — nueva (caso Adriana exitoso)
        - principle id=18 — modificado
        
        Total chunks a re-embedear: 47.
        Estimated cost Voyage 3.5: $0.003.
        ¿Procedo?
> si

Claude: ✓ Generados 47 embeddings (1024 dims cada uno)
        ✓ INSERT/UPDATE en corpus_embeddings
        Tiempo: 28s. Costo real: $0.0028.
```

---

## 3. Workflow A: generar UN plan (uso diario)

Ya descrito arriba en §2.1. Tiempo total esperado: **5-10 min** de Daniel (intake + review post-generación) + **90s** de máquina.

**Casos edge frecuentes**:

| Caso | Qué hacer |
|------|-----------|
| Linter encuentra 1 error | Daniel ve el finding con fixHint, decide si: (a) regenerar con motor v2 (con el feedback), (b) editar el JSON a mano, (c) abortar y crear con flujo manual |
| Verify falla pero plan ya INSERTÓ | `/plan-rollback` inmediato. Daniel investiga screenshot del verify. |
| Motor se queda colgado en COMPOSE >5min | Ctrl+C → cancela vía `RunCancelledError`. Motor cleanup y loguea. |
| Cliente no existe | Slash command lo detecta en step 2, falla rápido sin gastar tokens |
| Cliente tiene plan vigente que no vence pronto | Slash command pregunta confirmación antes de generar uno nuevo (override) |

---

## 4. Workflow B: agregar metodología (semanal)

Frecuencia esperada: **0-2 por semana** después del seed inicial. Más durante el primer mes.

**Ejemplo concreto**: Daniel lee un paper de Schoenfeld 2026 sobre PHUL → decide agregar al corpus.

```
> /methodology-add
[conversación del §2.3]
```

Después:
- (Opcional) `/corpus-reindex` para que aparezca en RAG retrieval
- (Opcional) Generar 1-2 planes de prueba con clientes test para ver cómo se ve
- Si OK: cambiar `status` de `experimental` a `active` con un UPDATE manual
- Documentar en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\08-METODOLOGIAS.md` (mantener docs humanas sincronizadas)

---

## 5. Workflow C: capturar plan exitoso como template

Frecuencia: **1-2 por mes**.

```
> /kb-capture-template

Claude: ¿De qué plan? (id o cliente)
> Adriana, su plan actual de hipertrofia que está funcionando muy bien

Claude: Encontré: assigned_plans id=410, cliente Adriana (id=88), 
        plan_type=entrenamiento, vigente desde 2026-04-20, version=3.
        ¿Confirmás capturarlo como template?
> si

Claude: ¿Quality score (0-100)?
> 85

Claude: ¿Tags?
> hipertrofia, intermedio, 5d, recomposicion

Claude: Voy a:
        1. Copiar assigned_plans.content
        2. Anonimizar nombre, peso, altura, edad
        3. INSERT en wellcore_kb.plan_templates_local con source='from_real_client'
        4. Generar embeddings de chunks principales

> si

Claude: ✓ Template id=12 creado
        ✓ 14 chunks embedeados ($0.0009 Voyage)
        Próxima generación con perfil similar a Adriana va a considerar este template como starting point.
```

---

## 6. Workflow D: agregar lint rule

Ya descrito en §2.4. Frecuencia: **1-3 por semana en los primeros 30 días post-rollout**, después decrece.

**Práctica recomendada**: cada vez que el motor v2 genera un plan que editas a mano, pregúntate "¿qué rule habría atrapado esto?". Esa rule es candidato a agregar.

---

## 7. Workflow E: monitoreo de salud

Frecuencia: **diaria los primeros 30 días, luego semanal**.

```
> /engine-health

[output del §2.5]
```

**Cuándo accionar sobre las métricas**:

| Métrica | Threshold | Acción |
|---------|-----------|--------|
| Cost/run > $0.50 | 2 runs consecutivos | Investigar prompt — quizás el caching no está hitting o un retry loop |
| Linter pass < 70% | Sostenido 7 días | Revisar las top failures, ajustar prompt de COMPOSE o agregar rules más estrictas en COMPOSE para evitar regen |
| Verify pass < 80% | Cualquiera | Investigar screenshots fallados, probable bug del Vue SPA o rule del linter incompleta |
| Daniel editó > 40% | Sostenido 7 días | Señal de que el motor genera "ok pero no genial". Iterar prompts. |
| requires_review > 25% | Cualquiera | Stop conditions demasiado estrictas, relajar |

---

## 8. Workflow F: rollback de un plan

Ya descrito en §2.2. Frecuencia esperada: **<1 por mes** si el linter está bien calibrado.

**Decisión post-rollback**: en el slash command Daniel marca razón del rollback. Estas razones se acumulan en `plan_engine_runs.rollback_reason` y se revisan en `/engine-health --rollbacks` para detectar patrones.

---

## 9. Cron tasks (las 3 que no se pueden olvidar)

Usando el cron del macOS de Daniel o un script cron en la laptop:

### 9.1 Backup semanal del corpus — domingo 3:00 AM

```bash
# crontab -e
0 3 * * 0 cd /Users/GODSF/Herd/wellcore-laravel && /usr/local/bin/php artisan kb:backup --gpg-recipient=daniel@wellcore --silent
```

El comando `kb:backup`:
- Hace `mysqldump --no-create-info wellcore_kb | gzip | gpg --recipient daniel@wellcore`
- Output a `storage/wellcore-kb-seed-<date>.sql.gz.gpg`
- Si exit 0: commit + push silencioso
- Si exit ≠ 0: log a `storage/logs/kb-backup-failures.log` + notifica via Mailjet

Sin la passphrase interactiva (usa GPG pinentry-mac sin password o key pre-cacheada en agent).

### 9.2 Re-verify de GIFs — lunes 4:00 AM

```bash
0 4 * * 1 cd /Users/GODSF/Herd/wellcore-laravel && /usr/local/bin/php artisan kb:verify-gifs --silent
```

El comando `kb:verify-gifs`:
- HEAD check a todas las URLs de `exercise_metadata.gif_url` que no se verificaron en últimos 7 días
- UPDATE `gif_url_status` con `ok | broken | missing` + `gif_url_verified_at = NOW()`
- Si encuentra >5 broken: notifica via Mailjet (el repo GitHub puede haber renombrado/eliminado archivos)

### 9.3 Health snapshot diario — 8:00 AM

```bash
0 8 * * * cd /Users/GODSF/Herd/wellcore-laravel && /usr/local/bin/php artisan kb:health --window=1d --output=storage/health-snapshots/$(date +%Y-%m-%d).json
```

Output JSON con todas las métricas del día. Se acumulan en `storage/health-snapshots/` para tendencias mensuales (el dashboard Vue del Sprint 7+ los lee).

---

## 10. Estado de "salud del cerebro" — dashboards

### 10.1 CLI (Sprint 4 — MVP)

`/engine-health` con flags:

```bash
/engine-health                  # últimos 7d default
/engine-health --window=30d     # últimos 30d
/engine-health --rollbacks      # foco en rollbacks
/engine-health --by-vertical    # break down por vertical
/engine-health --top-rules      # top 10 lint rules disparadas
/engine-health --json           # output JSON para parsing
```

### 10.2 Vue local (Sprint 7+ — opcional)

Página `/dev/engine-health` accesible solo desde `127.0.0.1` (firewall + middleware `RequireLocalhost`):

- Time series chart de cost/run, linter pass rate, verify pass rate
- Heatmap de cuándo se generan más planes (lunes mañana? jueves tarde?)
- Top 10 lint rules disparadas (con drill-down al run)
- Lista de runs requires_review pendientes de Daniel

Tech: el dashboard usa los `storage/health-snapshots/*.json` del cron task del §9.3. NO consulta `plan_engine_runs` en tiempo real (eso lo hace `/engine-health` CLI).

---

## 11. Definición operativa de "LLM local" (cierre conceptual)

**El motor v2 NO es** un wrapper de la API Anthropic con prompts más largos.

**El motor v2 ES**:

```
  Una orquestación local que vive en la laptop de Daniel, compuesta por:
  
  1. wellcore_kb: DB MySQL local con conocimiento curado del dominio
     (metodologías, ejercicios, principios, templates exitosos).
  
  2. Stages tipadas: 6 funciones puras que pasan DTOs readonly entre sí,
     orquestadas por una clase que es dueña de los handles externos.
  
  3. LLM tool-constrained: Claude (Sonnet/Haiku/Opus según tarea) invocado
     SOLO dentro de tools con tool_choice forzado, schemas estrictos.
     Imposible generar output fuera del shape canónico.
  
  4. Linter pre-INSERT: 30 rules que bloquean planes inválidos antes de
     tocar producción. DB-driven, hot-reload.
  
  5. Observability completa: cada run guarda input + output + métricas +
     screenshots en wellcore_kb.plan_engine_runs.
  
  Daniel + Claude Code agregan/modifican el conocimiento (kb) SIN redeploy
  ni cambio de código del motor. Las generaciones son reproducibles.
```

**Lo que NO es**:
- Un agente autónomo que decide solo cuándo crear planes (sigue siendo Daniel quien dispara).
- Un servicio runtime en producción (vive en la laptop, output va a prod vía PDO).
- Un reemplazo del flujo manual (coexisten — flujo manual sigue funcionando como fallback).
- Un sistema que aprende automáticamente (RAG es retrieval, no entrenamiento; los embeddings se calculan cuando Daniel dispara reindex).

---

## 12. Lo que NO está resuelto en este doc

1. **Cómo se distribuye a un coach 2do** — si en Sprint 12+ Anderson empieza a usar el motor también desde su laptop, hace falta sincronización del corpus entre laptops. Opciones: cron rsync, o pull desde repo en cada arranque. Pendiente decisión.
2. **Slash command para análisis post-mortem** — `/plan-postmortem 421` que vea todo el `plan_engine_runs.id=421` con drill-down de cada stage. Sería útil pero no crítico MVP. Sprint 6+.
3. **Voice interface** — si en algún momento se quiere crear planes hablándole a Claude desde el celular, hay que adaptar el flujo a audio. Sprint 12+ si se justifica.
4. **Notebook viewer del corpus** — herramienta para que Daniel "navegue" `wellcore_kb` visualmente (ej. dbeaver o phpMyAdmin). Por ahora `php artisan tinker` + queries SQL crudo. Si frustra, instalar TablePlus o similar.

## Próximo doc

**`09-open-questions-and-risks.md`** — Lo que NO sabemos y necesitamos validar:
- Decisiones de producto pendientes (RISE, bloodwork, coaches no-Anderson)
- Asunciones que hice y deberías validar (Herd MySQL versión, RAM disponible, etc.)
- Áreas del repo donde leí poco y podría estar errando
- Cualquier conflicto de licencia Apache 2.0 al portar el patrón HF (probablemente ninguno)
- Las preguntas abiertas de los docs 04-08 consolidadas
- Métricas pre-rollout que necesitamos capturar para tener baseline (cost actual real, tiempo actual real)

Espero OK de Daniel para avanzar al doc 09 (el último).
