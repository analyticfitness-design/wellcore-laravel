# Spec — Content Pipeline System v1.0

**Fecha:** 2026-05-08
**Autor:** Daniel Esparza + Claude Opus 4.7
**Estado:** v1.0 implementado — pipeline 12 fases + BD output + skill wrapper
**Skill location:** `~/.claude/skills/content-pipeline/SKILL.md`
**System files:** `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA DE PIPELINE DE CONTENIDO\` (12 MDs)
**BD output:** `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA DE PIPELINE DE CONTENIDO\BASE DE DATOS DE INFORMACIÓN FILTRADA PARA CREAR HTMLS O REELS\`

---

## Contexto

Daniel pidió un segundo sistema de agentes (después del research-validator) que:
1. Tome claims validados de BD APLICABLE.
2. Genere contenido en 4 formatos: blog, reel, carrusel, coach script.
3. Refute con Perplexity Pro vía Chrome MCP antes de publicar.
4. Valide voz coach colombiano-LATAM (basado en `05-VOZ-WELLCORE.md` + `06-VOZ-COACH.md`).
5. Filtre por rubric de calidad premium.
6. Persista en BD propia para consumo de sistemas downstream.
7. Funcione en Claude Code Y Kimi Code con paridad funcional.

## Decisiones tomadas con criterio

| Decisión | Razón |
|----------|-------|
| Renombrar carpeta de "AGENTES REFUTADORES" a "PIPELINE DE CONTENIDO" | Nombre más preciso semánticamente; refutación es UNA fase, no todo el sistema |
| 12 agentes (no 7 como propuesta inicial) | Daniel pidió "todo lo que considerés" — agregué Trend Scout, Localization, Compliance, SEO |
| Multi-formato en UN run (blog + reel + carrusel + coach-script) | Maximiza ROI del research validado; reusa el mismo claim para 4 outputs |
| Trigger manual (claim ID o tema libre) | Más control, menos surprises, debuggeable v1.0 |
| Drafters paralelos en Claude (Agent tool); secuencial en Kimi | Aprovecha capacidad nativa de Claude; degrada gracefully en Kimi |
| BD output con misma arquitectura que research-validator (manifest + INDEX dual md/json) | Coherencia entre sistemas; sistemas downstream usan mismo patrón |
| Premium rubric 5D × 20 puntos | Reproducibilidad cross-runs (anti-anchoring herendado de research-validator) |
| Loop back en Premium Validator (max 2 iteraciones) | Balance calidad/costo |

---

## Arquitectura

### Diagrama de alto nivel

```
USER INPUT (tema o claim ID)
    │
    ▼
[1] TOPIC RESOLVER → busca claims en BD APLICABLE
[2] TREND SCOUT → X/Reddit/TikTok signals
[3] IDEA GENERATOR → 5-8 ángulos
[4] VIABILITY SCORER → filtra a top 2-3
[5] REFUTER → Perplexity Pro coherence check (Chrome MCP)
[6] CONTENT DRAFTER (paralelo Claude / secuencial Kimi):
    ├── Blog Drafter (800-1500w)
    ├── Reel Drafter (30-60s)
    ├── Carrusel Drafter (7-10 slides)
    └── Coach Script Drafter (300w)
[7] VOICE-TONE ALIGNER → 90% coach + 10% marca + colombiano-LATAM
[8] LOCALIZATION VALIDATOR → peninsular/argentino/gringo
[9] COMPLIANCE VALIDATOR → claims peligrosos + disclaimers
[10] SEO OPTIMIZER → keywords, meta, headings (solo si blog)
[11] PREMIUM QUALITY → rubric 5D, loop back si <70
[12] ARCHIVIST → persiste en BD output + INDEX dual
    │
    ▼
USER OUTPUT (4 formatos + visual brief + premium score + path BD)
```

### Componentes (12 MDs + skill + BD output)

```
SISTEMA DE PIPELINE DE CONTENIDO/
├── README.md
├── 00-PROMPT-MASTER.md
├── 01-CONTEXT-CLAUDE-OPUS.md
├── 02-CONTEXT-KIMI-CLI.md
├── 03-PIPELINE-WORKFLOW.md
├── 04-AGENT-ROLES.md
├── 05-CONTENT-FORMATS.md
├── 06-VOICE-TONE-RULES.md
├── 07-PREMIUM-QUALITY-CHECKLIST.md
├── 08-BD-INTEGRATION.md
├── 09-OUTPUT-FORMAT.md
├── 10-USAGE-EXAMPLES.md
├── 11-KIMI-PROMPT.md
└── BASE DE DATOS DE INFORMACIÓN FILTRADA PARA CREAR HTMLS O REELS/
    ├── INDEX.md
    ├── INDEX.json
    ├── README.md
    ├── _schema/
    │   ├── content-piece.schema.json
    │   └── index.schema.json
    ├── _scripts/
    │   └── rebuild-index.py
    ├── blog-posts/_index.md
    ├── reels/_index.md
    ├── carruseles/_index.md
    ├── coach-scripts/_index.md
    └── otros/_index.md
```

---

## Costos y wall time

| Métrica | Claude Code | Kimi CLI |
|---------|-------------|----------|
| Wall time por pieza | 10-18 min | 20-30 min |
| Costo por pieza | ~$5-8 USD | ~$3-5 USD |
| Output | 4 formatos + visual brief + premium score | Idem |

A 30 piezas/mes en Kimi: ~$90-150 USD/mes.

---

## Diferencias clave con research-validator

| Aspecto | research-validator | content-pipeline |
|---------|-------------------|------------------|
| Input | claim | claim ID o tema libre |
| Fases | 6 | 12 |
| Output | reporte markdown + manifest claim | 4 formatos contenido + visual brief + manifest pieza |
| BD propia | sí (BD APLICABLE) | sí (BD INFORMACIÓN FILTRADA) |
| Paralelismo en Claude | 6 subagentes (Affirmer + Refuter) | 4 subagentes (Drafters por formato) |
| Costo aprox | $2-4 | $5-8 |

---

## Trade-offs aceptados

| Trade-off | Razón |
|-----------|-------|
| 12 agentes en lugar de 7 (más costo) | Daniel pidió "todo lo que considerés"; agregué Trend Scout, Localization, Compliance, SEO |
| Wall time mayor que research-validator | Multi-formato + más validaciones |
| Loop back en Premium puede agregar 3min | Balance calidad/velocidad |
| Voice-Tone Aligner secuencial (no paralelo) | Necesita ver los 4 formatos juntos para coherencia cross-formato |
| Sin auto-publish | Out of scope v1.0; persiste en BD para consumo manual o futuro auto-publisher |

---

## Cómo se conecta con research-validator (upstream)

```
┌────────────────────┐
│ research-validator │  ← v1.1 con Fase 6 archivist
│  (sistema 1)       │
└────────────────────┘
       │
       ▼ persiste claims
┌──────────────────┐
│ BD APLICABLE     │  ← INDEX.json es contrato API
└──────────────────┘
       │
       ▼ consume claims (lectura)
┌────────────────────┐
│ content-pipeline  │  ← v1.0 (este sistema)
│  (sistema 2)       │
└────────────────────┘
       │
       ▼ persiste contenido
┌──────────────────────────┐
│ BD INFORMACIÓN FILTRADA  │  ← INDEX.json contrato API estable
└──────────────────────────┘
       │
       ▼ consume (futuro)
┌────────────────────────────┐
│ Photoshop / Premiere MCP / │
│ manual creator / auto-publisher│
└────────────────────────────┘
```

---

## Tests / Validación

### Test 1 — End-to-end con claim ID existente

```
1. Pegar 00-PROMPT-MASTER.md.
2. "Pasame contenido sobre el claim nut_2026-05-07_creatina-fuerza en modo autónomo".
3. Esperar ~10-18 min.
4. Verificar:
   - 4 formatos en content.md
   - manifest.yaml válido contra schema
   - INDEX maestros actualizados (0 → 1)
   - Premium score >= 70 (idealmente 80-95)
   - Compliance score 100 (con disclaimer si aplica)
   - Voice tone score >= 85
```

### Test 2 — Tema libre con gap

```
1. "Pasame contenido sobre lengthened partials hipertrofia".
2. Si BD APLICABLE no tiene claim relacionado → Topic Resolver debería derivar a research-validator.
3. Verificar mensaje claro al usuario.
```

### Test 3 — Cross-CLI parity

```
1. Correr Test 1 en Claude Code Opus 4.7.
2. Correr Test 1 en Kimi CLI.
3. Comparar:
   - Premium scores deberían diferir ±10 puntos.
   - Estructura de los 4 formatos similar.
   - Voice tone scores coincidir.
```

---

## Open questions / Future work

1. **Auto-publish** — out of scope v1.0. Posible v2.0 con integración a IG/TikTok/blog.
2. **Trend Scout más sofisticado** — actualmente WebFetch básico. Posible mejora: integración con APIs oficiales de cada plataforma.
3. **Performance Tracker** — medir engagement post-publish y feedback al sistema. Out of scope v1.0.
4. **Visual generation automatic** — actualmente solo brief, no imagen. Posible v2.0 con Photoshop MCP integration.
5. **Cache de Drafters** — si el mismo claim genera contenido similar 2 veces, reusar. Out of scope v1.0.

---

## Validación post-implementación

✅ 12 MDs creados en `SISTEMA DE PIPELINE DE CONTENIDO/`
✅ SKILL.md wrapper en `~/.claude/skills/content-pipeline/`
✅ Spec design en `wellcore-laravel/docs/superpowers/specs/`
✅ BD output con estructura completa (5 dominios, 2 schemas, script Python)
✅ INDEX.md + INDEX.json vacíos iniciales (válidos contra schema)
✅ **Test E2E con claim real ejecutado en Kimi (2026-05-08)** — 12 fases completas, 6 formatos generados, premium 82.

---

## Lecciones del primer test E2E + mejoras v1.2 aplicadas

### Hallazgos del run (claim ent_2026-05-08_maquinas-vs-pesos-libres)

**Lo que funcionó:**
- Caveats integrados en cuerpo de TODOS los 6 formatos (verificado retroactivamente).
- Sub-claims del source claim cubiertos (sc1, sc4, sc5 mencionados explícitamente).
- Trend coherence: saturated angle evitado, gap aprovechado.
- Citations específicas con autor/año/journal verificables.

**Lo que falló:**
- **Localization Validator v1.1 dio 100/100 pero coló voseo argentino sistémico** (12+ violaciones: "tenés", "querés", "podés", "creés", "escribime", "armo"). Score era falso positivo.
- Premium 82 sobre source claim 60 sin warning explícito → riesgo de over-confidence.
- compare_table en story slide 04 heredó nombres `value_normal`/`value_deload` del template DELOAD original.
- Topic Resolver añadió related_claim spurious (creatina, no relacionado).

### Mejoras v1.2 aplicadas

1. **Localization Validator reforzado** (`04-AGENT-ROLES.md`):
   - Regex para verbos voseo conjugados en -ás/-és/-ís.
   - Imperativos voseo en -á/-é/-í.
   - Lista expandida de verbos comunes con tabla voseo→tuteo.
   - `rewrite_required: true` cuando voseo_violations > 3 → loop back automático a Voice Aligner.

2. **Self-Audit Agent (Fase 13 NUEVA)** (`04-AGENT-ROLES.md` + `03-PIPELINE-WORKFLOW.md`):
   - Corre antes de Archivist.
   - 4 checks: caveat_integration, trend_coherence, sub_claim_coverage, evidence_alignment.
   - Output: `audit-report.yaml`.
   - Loop back a fase específica si check high-severity falla.

3. **Premium Validator dimensión 6 condicional** (`07-PREMIUM-QUALITY-CHECKLIST.md`):
   - Caveat Integration (max 20 pts) cuando source_claim_score < 75.
   - Verifica caveats en cuerpo, no solo footer.
   - Si D6 < 14 → loop back a Drafter.

4. **Schema manifest extendido** (`09-OUTPUT-FORMAT.md` + `_schema/content-piece.schema.json`):
   - Campo `warnings` con sub-categorías (evidence_alignment, caveat_integration, localization_violations, trend_coherence).
   - Campo `source_claim_snapshot` para detectar drift cross-time.
   - Campo `audit_report` ref al YAML de Self-Audit.

5. **Story compare_table genérico** (`12-STORIES-DESIGN-SYSTEM.md` + `_plantillas-html/story-template/04-APLICACION.html`):
   - Renombrado `value_normal`/`value_deload` a `value_a`/`value_b` genéricos.
   - Plantilla HTML mantiene retro-compatibilidad con clase `.deload` (legacy DELOAD week).

### Update retroactivo del run actual (cp_ent_2026-05-08_maquinas-vs-pesos-libres-hipertrofia)

- **manifest.yaml** actualizado con warnings (high severity para localization + evidence_alignment).
- **audit-report.yaml** generado retroactivamente con los 5 checks.
- **INDEX.json** actualizado con `ready_for_publish: false` + `ready_for_publish_blocker: voseo_argentino_systemic`.

⏳ **Pendiente para Daniel:** decidir si re-correr la pieza con v1.2 (preferible) o fix manual de voseo (~10 min).

---

## Cómo usar (para Daniel)

### En Claude Code (este session o futura):

```
/content-pipeline

> Pasame contenido sobre el claim "nut_2026-05-07_creatina-fuerza" en modo autónomo
```

### En Kimi Code:

```
1. Pegar 11-KIMI-PROMPT.md como primer mensaje.
2. Esperar setup OK (~2-3 min).
3. "Pasame contenido sobre [tema o claim]".
4. Esperar ~20-30 min.
```

### Sin skill activado (cualquier CLI):

```
[pegar contenido de 00-PROMPT-MASTER.md]
> Pasame contenido sobre [tema o claim ID]
```
