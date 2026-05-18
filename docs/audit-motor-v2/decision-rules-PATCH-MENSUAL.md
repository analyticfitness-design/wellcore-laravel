# Patch retroactivo — Modelo mensual de planes WellCore

**Fecha:** 2026-05-17
**Trigger:** Daniel autoritativa 2026-05-17 → "Todos los planes son MENSUALES. No hay trimestral o semestral. Solo vendemos membresías mensuales."
**Memoria asociada:** `feedback_planes_mensuales_solamente.md`

## Impacto

Los chunks 01-07 referencian `duracion_semanas` 8, 12, 16. Esto es legacy del MD 04 que describe los bloques **conceptuales**, no la duración del plan asignado en DB. La verdad es:

- Cada plan en `assigned_plans.content.duracion_semanas` = **4 semanas SIEMPRE**.
- Los bloques "12 semanas" del Método se materializan como **3 planes mensuales consecutivos** con metadata.
- Los planes "Elite 12 sem" del caso Daniel son **3 planes mensuales** orquestados por el coach humano.

## Chunks afectados y ajustes

### Chunk 01 (Categoría A — Selección de metodología)

- **A6** `select-metodologia-12sem-metodo-tier`:
  - **Antes**: input `duracion_semanas_solicitada=12` + `tier=metodo`
  - **Después**: input `tier=metodo` + `bloque_progresion=metodo-12sem` (metadata del cliente, no del plan)
  - El plan asignado sigue siendo 4 sem; la meta-rule de secuencia (E2 nuevo) define qué fase del bloque toca este mes.

### Chunk 02 (Categoría A + B)

- **A7** `select-metodologia-4sem-elite-mujer`:
  - **Antes**: input `duracion_semanas_solicitada=4` + `tier=elite` + `gender=femenino`
  - **Después**: input `tier=elite` + `gender=femenino` + `nivel=intermedio/avanzado`. La duración 4 sem ya es default.
- **A8** `select-metodologia-cliente-vuelve-pausa-larga` y **A9** `select-metodologia-recomposicion-8sem`:
  - **A9 antes**: input `duracion_semanas_solicitada≥8`
  - **A9 después**: input simplemente `objective=recomposicion + level=intermedio/avanzado`. El "8 sem" es bloque conceptual de 2 meses (recomp-8sem = 2 planes mensuales).

### Chunk 04 (Categoría C — Macros ajustes objetivo)

- **C5** `ajuste-deficit-perdida-agresiva` campo `duracion_maxima_recomendada_semanas: 8`:
  - **Mantener** como guardrail informativo. El motor lo expone al coach: "no sostenidamente más de 2 meses" — el coach decide cuándo cambiar.
- **C7** `ajuste-aumento-limpio` campo `duracion_recomendada_semanas: [12,24]`:
  - **Reinterpretar** como "fase de 3-6 meses consecutivos" (3-6 planes mensuales).
- **C8** `ajuste-aumento-agresivo` campo `duracion_recomendada_semanas: [8,16]`:
  - **Reinterpretar** como "fase de 2-4 meses". `duracion_maxima_recomendada_semanas` cambia a `duracion_maxima_recomendada_meses: 4`.

### Chunk 08 (Categoría E — Periodización) — REDISEÑADO

Ver `decision-rules-CHUNK-08.json` (rediseño mensual).

- E1 antes: `periodizacion-4sem-esencial-1-2-1` (1 Adapt + 2 Hipert + 1 Peak en un plan de 4 sem). Esto **se mantiene** porque ya era plan mensual.
- E2 antes: `periodizacion-12sem-metodo-3-3-3-3` (3+3+3+3 fases en un plan de 12 sem). **Eliminada como rule de plan único**. Reemplazada por **meta-rule de secuencia**: define qué fase del bloque toca cada mes (mes 1 = Adapt, mes 2 = Hipert, mes 3 = Fuerza, mes 4 = Peak).
- E3 antes: `periodizacion-4sem-elite-agresiva-1-2-1`. **Se mantiene** — ya era 4 sem.
- E4 antes: `periodizacion-12sem-elite-validacion-manual`. **Eliminada como rule de plan único**. Reemplazada por meta-rule + flag `needs_daniel_validation` en cada mes Elite.
- E5 (volumen ascendente semanal), E6 (RIR descendente por bloque), E7 (deload obligatorio): ajustados al contexto intra-mes.

## Nuevo modelo de E (Categoría Periodización)

7 rules nuevas:

| # | slug | input → output |
|---|------|---|
| E1 | `periodizacion-1mes-fase-adaptacion` | tier + nuevo cliente → 4 sem fase Adaptación (RIR 3 → 3 → 3 → 2) |
| E2 | `periodizacion-1mes-fase-hipertrofia` | tier + ya pasó adaptación → 4 sem fase Hipertrofia (RIR 3 → 2 → 2 → 1) |
| E3 | `periodizacion-1mes-fase-fuerza` | tier=metodo + bloque mes 3 → 4 sem fase Fuerza Máxima (RIR 2 → 1 → 1 → 0) |
| E4 | `periodizacion-1mes-fase-peak` | tier=metodo + bloque mes 4 → 4 sem fase Peak (RIR 1 → 0 → 0 → test) |
| E5 | `meta-secuencia-bloque-metodo-12sem` | tier=metodo → secuencia recomendada de fases mes a mes |
| E6 | `volumen-ascendente-intra-mes-5-10pct` | dentro del mes → series totales suben 5-10%/sem |
| E7 | `deload-mes-cada-3-4-meses-consecutivos` | cliente lleva 3-4 meses entrenando sin pausa → próximo mes es Deload completo (-30% volumen) |

## Compatibilidad downstream

- El motor v2 sigue produciendo JSON con la misma estructura (`semanas[]` con 4 entries).
- El frontend Vue lee `duracion_semanas: 4` siempre — no se rompe nada visualmente.
- El coach humano coordina la secuencia multi-mes al asignar el plan del mes siguiente (lee `siguiente_fase_recomendada` del plan anterior).
- En sprint posterior, automatizar: cuando un cliente renueva membresía, el motor v2 lee `assigned_plans.content.siguiente_fase_recomendada` del plan anterior y arma el nuevo plan con esa fase.

## Acción

- ✅ Memoria autoritativa guardada (`feedback_planes_mensuales_solamente.md`).
- ✅ Patch documentado (este archivo).
- ⏳ Chunk 8 rediseñado producido (`decision-rules-CHUNK-08.json` mensual).
- ⏳ Compilado final Fase 4 aplicará patch retroactivo a chunks 01-04 al consolidar.
