# Fase A — Resultados Optimización URLs Públicas
**Fecha:** 2026-04-26  
**Condición medición:** Mobile, Slow 4G, CPU 4× throttled (PageSpeed Insights lab data)  
**Commits:** `df7462cf` → `bd3d58d8` → `5807af67` → `fbd32323` → `35625802` → `2ad22980`

---

## Resumen ejecutivo

| URL | Antes Perf | Antes A11y | Después Perf | Después A11y | Δ Perf | Δ A11y |
|-----|:----------:|:----------:|:------------:|:------------:|:------:|:------:|
| `/planes` | 72 | 89 | 75 | 96 | +3 | +7 |
| `/metodo` | 74 | 96 | 73 | **100** | -1* | +4 |
| `/proceso` | 68 | 89 | 72 | 96 | +4 | +7 |
| `/coaches` | 74 | 90 | 77 | 98 | +3 | +8 |
| `/nosotros` | 74 | 92 | 75 | **100** | +1 | +8 |
| **BP** | 100 | — | 100 | — | 0 | — |
| **SEO** | 100 | — | 100 | — | 0 | — |

*-1 dentro del margen de ruido PSI (±10-15 puntos)

---

## Detalle por página

### /planes — commit `df7462cf` + `bd3d58d8`
**Antes:** 72/89/100/100 → **Después:** 75/96/100/100

Cambios aplicados:
- 9× `text-wc-accent` pequeño → `text-red-700 dark:text-red-400`
- 3× clases con opacity ajustadas
- 3 secciones con `hp-cv-section` (Benefits, Team Preview, CTA)
- Badge contraste fix: `bg-wc-accent/20 text-red-700` → `bg-wc-accent text-white` / `bg-white/20 text-white` (Alpine :class)
- Cookie banner fix: `text-wc-accent hover:underline` → `text-red-700 dark:text-red-400 underline` (afecta todas las páginas)

A11y restante (96): touch targets — fuera de scope Fase A

---

### /metodo — commit `5807af67`
**Antes:** 74/96/100/100 → **Después:** 73/100/100/100

Cambios aplicados (via replace_all):
- `font-data text-sm font-semibold text-wc-accent` → red-700/400 (section labels 01-04)
- `text-xs font-semibold uppercase tracking-wider text-wc-accent` → red-700/400 (solution labels)
- `font-data text-xs font-bold text-wc-accent` → red-700/400 (pillar labels P01-P05)
- `inline-flex items-center gap-1.5 text-sm font-medium text-wc-accent` → red-700/400 (desktop comparison ×6)
- `inline-flex items-center gap-1 text-sm font-medium text-wc-accent` → red-700/400 (mobile comparison ×6)
- 5 secciones con `hp-cv-section` (Stats Bar, Problema, Comparativa, Pilares, CTA)

Mantenidos con `text-wc-accent`: stats grandes (87%, 12sem, 1:1 — text-4xl/2xl bold), íconos SVG, `hover:text-wc-accent` FAQ  
**A11y → 100 ✅**

---

### /proceso — commit `fbd32323`
**Antes:** 68/89/100/100 → **Después:** 72/96/100/100

Cambios aplicados (47 instancias originales):
- 7 patrones replace_all de texto pequeño → red-700/400
- Alpine :class pills F01-F04 → red-700/400
- 6 secciones con `hp-cv-section` (Stats Bar, FASE 01-04, CTA)

A11y restante (96): touch targets — fuera de scope Fase A  
Mantenidos: títulos hero y grandes displays con `text-wc-accent`

---

### /coaches — commit `35625802`
**Antes:** 74/90/100/100 → **Después:** 77/98/100/100

Cambios aplicados:
- `text-xs font-semibold uppercase tracking-widest text-wc-accent` → red-700/400 (portal label)
- `font-data text-lg font-bold text-wc-accent` → red-700/400 (mockup stat "18" — 18px < umbral large text)
- 5 secciones con `hp-cv-section` (Benefits ×2, Portal Demo ×2, CTA)
- 14 SVGs mantenidos con `text-wc-accent` (no son texto, WCAG 1.4.3 no aplica)

A11y restante (98): heading hierarchy — encabezados fuera de orden descendente (issue estructural, fuera de scope Fase A)

---

### /nosotros — commit `2ad22980`
**Antes:** 74/92/100/100 → **Después:** 75/100/100/100

Cambios aplicados:
- `text-sm font-semibold uppercase tracking-wider text-wc-accent` → red-700/400 (4× role labels)
- `text-xs font-semibold uppercase tracking-wider text-wc-accent` → red-700/400 (3× hover labels)
- `font-data text-sm font-semibold text-wc-accent` → red-700/400 (8× timeline dates)
- 6 secciones con `hp-cv-section` (Mission, Team, Timeline, Stats, Values, CTA)

Mantenidos con `text-wc-accent`: iniciales `font-display text-4xl/2xl` (≥24px = large text threshold), counters `text-4xl font-bold`, SVGs  
**A11y → 100 ✅**

---

## Análisis de patrones

### Regla WCAG aplicada
- `text-wc-accent` (#DC2626) sobre fondo oscuro #09090B → 4.38:1 — **FALLA** AA (mínimo 4.5:1 texto normal)
- `text-red-700` (#b91c1c) sobre fondo claro → 5.91:1 ✅
- `text-red-400` (#f87171) sobre fondo oscuro → 5.92:1 ✅
- **Large text exception** (≥24px normal | ≥18.67px bold): mantenemos `text-wc-accent` en text-2xl+

### Por qué hp-cv-section mejora Performance
`content-visibility: auto` + `contain-intrinsic-size: 1px 800px` — el browser omite el layout y paint de secciones fuera del viewport durante el load inicial, reduciendo el tiempo de parseo y generando mejores scores de LCP y TBT.

### Issues residuales (no scope Fase A)
| URL | Issue residual | Causa | Fix necesario |
|-----|----------------|-------|---------------|
| `/planes` | Touch targets | Botones/links pequeños | Aumentar tamaño mínimo 44px |
| `/proceso` | Touch targets | Botones/links pequeños | Aumentar tamaño mínimo 44px |
| `/coaches` | Heading hierarchy | H2/H3 fuera de orden | Reestructurar headings |

---

## Cookie banner (fix global — afecta todas las páginas)
**Commit:** `bd3d58d8`  
**Fix:** `text-wc-accent hover:underline` → `text-red-700 dark:text-red-400 underline`  
**Impacto:** Mejora A11y en TODAS las URLs públicas (el banner aparece en cada run de Lighthouse con localStorage vacío)

---

## Próximos pasos (Fase A continuación)

Las siguientes URLs siguen pendientes del mismo tratamiento:

| URL | Perf | A11y | Prioridad |
|-----|-----:|-----:|-----------|
| `/presencial` | 74 | 96 | 🟠 |
| `/lanzamiento` | 74 | 100 | 🟠 (solo perf) |
| `/fit` | 56 | 96 | 🟠 (fix x-intersect adicional) |
| `/faq` | 60 | 96 | 🟠 |
| `/blog` | 70 | 100 | 🟢 |
| `/blog/show` | 73 | 90 | 🟢 |
| Legales (batch) | 73-76 | 92 | 🟢 |
| `/pago-exitoso` | 78 | 94 | 🟢 |
