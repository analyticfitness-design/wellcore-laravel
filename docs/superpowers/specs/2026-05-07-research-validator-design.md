# Spec — Research Validator System v1.0

**Fecha:** 2026-05-07
**Autor:** Daniel Esparza + Claude Opus 4.7
**Estado:** Implementado
**Skill location:** `~/.claude/skills/research-validator/SKILL.md`
**System files:** `E:\WELLCORE FITNESS PLATAFORMA\RESEARCH-VALIDATOR-SYSTEM\`

---

## Contexto

Daniel pidió un sistema avanzado de investigación científica que:
1. Valide información con Perplexity Pro vía Chrome DevTools MCP.
2. Use múltiples agentes/ventanas en paralelo.
3. Sea portable entre Claude Code Opus 4.7 y Kimi CLI.
4. Trabaje "como un magíster en investigación" — refutación activa, no solo confirmación.
5. Funcione para nichos importantes / temas relevantes (multi-dominio agnóstico).

Decisiones tomadas con criterio (sin preguntar):

| Decisión | Razón |
|----------|-------|
| Multi-dominio agnóstico (no solo fitness) | Daniel respondió esto antes de pedir "no preguntes". Confirmado. |
| Adversarial debate (red-team agent) | Daniel respondió esto antes de pedir "no preguntes". El nivel de rigor más útil para detectar bullshit. |
| Skill nuevo + sistema portable en E:\ | Daniel respondió esto antes de pedir "no preguntes" (skill superpowers). Pero como debe ser portable a Kimi también, implementé como sistema portable en E:\ con skill wrapper en .claude/skills/. |
| 12 MDs separados vs 1 monolítico | Separation of concerns. Cada MD tiene una responsabilidad. Carga lazy posible. |
| 6 agentes paralelos (3 affirmer + 3 refuter) | Maximiza cobertura de evidencia con wall time aceptable (~5-8min). |
| Score 0-100 con rubric explícita | Reproducibilidad — distintos LLMs deberían dar scores ±10 puntos. |
| Anti-Max safeguards en cada query | Prevenir cargos accidentales en Perplexity. |
| Output JSON estricto + markdown final | JSON para integraciones programáticas, markdown para humanos. |
| MD adicional para Kimi setup | Daniel pidió específicamente: cómo instalar agentes/skills en Kimi Code. |

---

## Arquitectura

### Diagrama de alto nivel

```
USER INPUT
    │
    ▼
┌─────────────────────┐
│ FASE 1              │  Question Refiner (1 LLM call)
│ Refine to PICO/PECO │
└─────────────────────┘
    │
    ▼ [JSON sub_claims]
┌─────────────────────┐
│ FASE 2+3 PARALELO   │  6 subagentes simultáneos:
│ ┌─────────────────┐ │  - A1, A2, A3 (Affirmer × 3)
│ │ Affirmer × 3    │ │  - R1, R2, R3 (Refuter × 3)
│ │ Refuter × 3     │ │  Cada uno usa 1 tab Chrome MCP Perplexity
│ └─────────────────┘ │
└─────────────────────┘
    │
    ▼ [6 JSONs evidence]
┌─────────────────────┐
│ FASE 4              │  Arbiter (1 LLM call)
│ Score 0-100         │  Aplica rubric + flags
└─────────────────────┘
    │
    ▼ [JSON scores + verdict]
┌─────────────────────┐
│ FASE 5              │  Synthesizer (1 LLM call)
│ Markdown report     │
└─────────────────────┘
    │
    ▼
USER OUTPUT (markdown + JSON consolidado opcional)
```

### Componentes (12 MDs + skill + spec)

```
E:\WELLCORE FITNESS PLATAFORMA\RESEARCH-VALIDATOR-SYSTEM\
├── README.md                    # Overview + quick start
├── 00-PROMPT-MASTER.md          # Prompt master copy-paste a CLI
├── 01-CONTEXT-CLAUDE-OPUS.md    # Adapter Claude Code (Agent tool, MCP)
├── 02-CONTEXT-KIMI-CLI.md       # Adapter Kimi CLI (paridad funcional)
├── 03-PROTOCOL-ADVERSARIAL.md   # 5 fases detalladas con I/O JSON
├── 04-EVIDENCE-HIERARCHY.md     # 6 tiers GRADE-lite + downgrade/upgrade flags
├── 05-AGENT-ROLES.md            # System prompts para Refiner, Affirmer×3, Refuter×3, Arbiter, Synthesizer
├── 06-CHROME-MCP-PARALLEL.md    # Patrón multi-tab Perplexity con anti-Max safeguards
├── 07-EVIDENCE-SCORING.md       # Rubric 0-100 con 6 componentes + flags
├── 08-OUTPUT-FORMAT.md          # JSON schemas + markdown templates
├── 09-BIAS-GUARDRAILS.md        # Anti-bullshit: p-hacking, COI, file-drawer, retracciones
├── 10-USAGE-EXAMPLES.md         # 3 casos worked-out (creatina, meditación, IF)
└── 11-KIMI-CODE-SETUP.md        # Instalación Kimi + MCP + agentes/skills

C:\Users\GODSF\.claude\skills\research-validator\
└── SKILL.md                     # Wrapper auto-trigger en Claude Code

C:\Users\GODSF\Herd\wellcore-laravel\docs\superpowers\specs\
└── 2026-05-07-research-validator-design.md   # Este archivo
```

---

## Data flow detallado

### Phase 1 — Question Refinement
- **Input:** string libre del usuario
- **Output:** JSON `{ main_claim, research_format, sub_claims[2-5], search_queries }`
- **Tiempo:** ~30s
- **Tokens:** ~1k input, ~1k output

### Phase 2+3 — Parallel Evidence Gathering
- **Input:** sub_claims de Fase 1
- **Output:** 6 JSONs (3 affirmer + 3 refuter) con evidencia estructurada
- **Tiempo:** 3-5min wall (paralelo)
- **Tokens:** ~3×8k input × 6 = 48k input, ~3×3k × 6 = 18k output
- **Chrome MCP queries:** 6-18 tabs Perplexity Pro abiertas simultáneamente

### Phase 4 — Arbitration
- **Input:** 6 JSONs de Fase 2+3
- **Output:** JSON con scores 0-100, verdict tags, flags
- **Tiempo:** ~1-2min
- **Tokens:** ~30k input, ~3k output

### Phase 5 — Synthesis
- **Input:** Arbiter JSON + (opcionalmente) los 6 evidence JSONs
- **Output:** Markdown report (800-1500 palabras)
- **Tiempo:** ~1-2min
- **Tokens:** ~35k input, ~5k output

**Total wall time:** 5-8 minutos
**Total tokens:** ~140k input, ~28k output
**Costo Claude Opus 4.7:** ~$2-4 USD por reporte completo
**Costo Kimi K2:** ~$1-3 USD (típicamente más barato)

---

## Decisiones técnicas críticas

### 1. ¿Por qué adversarial en lugar de single-pass?

Single-pass research da el sesgo del modelo + el sesgo de la fuente principal. Adversarial fuerza al sistema a buscar evidencia EN CONTRA explícitamente, lo cual:
- Detecta file-drawer effect.
- Encuentra retracciones que el affirmer ignora.
- Calibra la confidence honestamente.

### 2. ¿Por qué 3 affirmers + 3 refuters (no 1+1)?

Distintas queries traen distintas evidencias:
- A1 (conservador) trae solo Tier 1-2.
- A2 (estándar) trae Tier 2-3.
- A3 (agresivo) trae todo incluyendo preprints.

Igual para refuters (retraction watch / contraevidencia / file-drawer). Cobertura redundante pero no duplicada.

### 3. ¿Por qué Chrome MCP en lugar de API Perplexity?

- API cuesta extra ($).
- Browser usa la cuenta Pro existente del usuario.
- Anti-Max safeguards más fáciles en browser que en API.

### 4. ¿Por qué scoring numérico 0-100 en lugar de cualitativo?

- Reproducibilidad: dos runs distintos deberían dar scores ±10 puntos.
- Tabla de mapeo a verdict tags clara.
- Útil para integraciones programáticas (filtrar por score>X, etc.).

### 5. ¿Por qué portable entre Claude Code y Kimi en lugar de un único CLI?

- Vendor independence.
- Costos: Kimi suele ser más barato.
- Rate limits distintos.
- Si una API cae, fallback al otro.

---

## Trade-offs aceptados

| Trade-off | Razón |
|-----------|-------|
| 12 MDs en vez de 1 monolítico | Más complejo de mantener, pero modular y lazy-loadable |
| Wall time 5-8min en vez de instantáneo | Necesario para paralelismo + 6 queries Perplexity |
| Costo $2-4 por reporte | Aceptable para uso ocasional, puede no escalar a 100s/día |
| Output JSON estricto puede fallar en Kimi | Workaround documentado (re-prompt) |
| No accede a PubMed full-text directo | Limitación de Perplexity Pro como motor |

---

## Tests / Validación

### Test 1 — Calibración con 3 ejemplos canónicos

Correr los 3 ejemplos de `10-USAGE-EXAMPLES.md` y verificar:

| Claim | Score esperado | Verdict esperado |
|-------|---------------|------------------|
| Creatina aumenta fuerza | 70-85 (range) | WELL_SUPPORTED |
| Meditación 10min reduce stress | 45-60 | PARTIALLY_SUPPORTED |
| IF supera CR isocalórico | 25-40 | MOSTLY_REFUTED |

Si el sistema devuelve scores fuera de estos rangos en >2 ejemplos, hay drift en los prompts → ajustar `05-AGENT-ROLES.md`.

### Test 2 — Anti-Max safeguard

Configurar una sesión Perplexity con cuenta Max temporalmente (sandboxed) y verificar que el sistema:
1. Detecta el tier "Max".
2. Aborta con error claro.
3. NO clickea Deep Research/Comet.

### Test 3 — Failure recovery

Apagar Chrome MCP server mid-query y verificar que:
1. El sistema detecta el fallo.
2. Reporta gracefully al usuario.
3. Ofrece alternativa (modo secuencial, API con permiso, etc.).

### Test 4 — Cross-CLI parity

Correr el mismo claim en Claude Code Opus 4.7 y Kimi CLI. Comparar:
- Scores deberían diferir ±10 puntos.
- Verdict tags deberían coincidir.
- Si difieren más, drift de prompts entre CLIs → revisar adapters.

---

## Open questions / Future work

1. **Integración con NotebookLM:** después del reporte, ¿automatizar generación de podcast/infografía via `research-pipeline`? Decisión: NO en v1.0 — agregar en v1.1 si Daniel lo pide.

2. **Caché de Perplexity threads:** si el mismo claim se investiga 2 veces, ¿reusar threads? Decisión: NO en v1.0 — implica tracking de claim hashes. Agregar en v2.0.

3. **Web UI dashboard:** mostrar reportes históricos, tracking de claims, etc. Decisión: out of scope v1.0. Posible v2.0.

4. **Auto-actualización de evidencia:** re-correr periódicamente claims antiguos para detectar nueva evidencia. Decisión: out of scope v1.0.

5. **Integración con Obsidian:** guardar reportes en vault. Decisión: trivial agregar en v1.1 si Daniel lo pide.

6. **Soporte para idiomas no-inglés en queries:** Perplexity soporta multi-idioma pero los seeds de query están en inglés. Decisión: parametrizable en v1.1.

---

## Validación post-implementación

✅ 12 MDs creados en E:\
✅ SKILL.md wrapper en ~/.claude/skills/research-validator/
✅ Spec design en wellcore-laravel/docs/superpowers/specs/
✅ Memory entries actualizadas
⏳ Pendiente: test E2E con un claim real (Daniel debe correrlo)
⏳ Pendiente: smoke test en Kimi CLI (cuando Daniel tenga Kimi instalado)

---

## Cómo usar (para Daniel)

### En Claude Code (este session o futura):

```
> /research-validator
> Investiga adversarialmente: "[tu claim]"
```

Si el slash command no auto-completa, simplemente decime:
```
> Activá el skill research-validator e investiga: "..."
```

### En Kimi Code (cuando lo tengas):

```
> Seguí 11-KIMI-CODE-SETUP.md una vez para instalar.
> Después: kimi chat → /skill research-validator → investigá tu claim.
```

### Sin skill activado (cualquier CLI):

```
> [pegar contenido de E:\WELLCORE FITNESS PLATAFORMA\RESEARCH-VALIDATOR-SYSTEM\00-PROMPT-MASTER.md]
> Investiga: "..."
```
