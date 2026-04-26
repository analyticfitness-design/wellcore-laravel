# Baseline PSI — URLs Públicas WellCore
**Fecha:** 2026-04-26  
**Condición:** Mobile, Slow 4G, CPU 4×throttled (PageSpeed Insights lab data)  
**Referencia homepage:** Performance 74-77 / A11y 100 / BP 100 / SEO 100

---

## Tabla de baselines

| URL | Perf | A11y | BP | SEO | Notas |
|-----|-----:|-----:|---:|----:|-------|
| `/` (homepage) | 74-77 | 100 | 100 | 100 | ✅ DONE |
| `/planes` | **72** | **89** | 100 | 100 | 15× text-wc-accent |
| `/metodo` | **74** | **96** | 100 | 100 | 36× text-wc-accent, Alpine FAQ |
| `/proceso` | **68** | **89** | 100 | 100 | 47× text-wc-accent, anchors #fase-XX |
| `/coaches` | **74** | **90** | 100 | 100 | 16× text-wc-accent |
| `/nosotros` | **74** | **92** | 100 | 100 | 30× text-wc-accent |
| `/presencial` | **74** | **96** | 100 | 100 | 4× text-wc-accent |
| `/lanzamiento` | **74** | 100 | 100 | 100 | 27× accent pero ya pasa A11y — verificar opacity-40 |
| `/fit` | **56** | **96** | 100 | 100 | x-intersect plugin faltante, 3× opacity-40/60 |
| `/faq` | **60** | **96** | 100 | 100 | DOM 1002 líneas, Alpine heavy — NO content-visibility en tabs |
| `/blog` | **70** | 100 | 100 | 100 | 2× text-wc-accent |
| `/blog/show` | **73** | **90** | 100 | 100 | 4× text-wc-accent, slug: progressive-overload-guia-completa |
| `/terminos` | **76** | **92** | 100 | 100 | 3× text-wc-accent |
| `/privacidad` | **75** | **92** | 100 | 100 | 2× text-wc-accent |
| `/politica-cookies` | **73** | **92** | 100 | 100 | 2× text-wc-accent |
| `/reembolsos` | **75** | **92** | 100 | 100 | 2× text-wc-accent |
| `/pago-exitoso` | **78** | **94** | 100 | 100 | 11× text-wc-accent + inline script sin nonce |

---

## Observaciones generales

### Performance
- Rango: 56–78 (sin optimizaciones Fase A)
- Más baja: `/fit` (56) — x-intersect Alpine bug + opacity
- Más baja grupo 2: `/faq` (60) — DOM masivo 1002 líneas
- `/proceso` (68) — densidad máxima de accent (47 instancias)
- Legales tienen buen perf natural (76-78) por ser páginas simples

### Accessibility
- BP=100 y SEO=100 en TODAS las páginas ✅ (ya heredado del layout público)
- A11y ya en 100: `/lanzamiento`, `/blog` ✅
- A11y con mayor problema: `/planes` y `/proceso` (89) — densidad de text-wc-accent
- Patrón uniforme: el único fallo de A11y es `color-contrast` por `text-wc-accent` en dark mode

### Impacto esperado Fase A (sin Critical CSS)
- A11y: todas → 100 (fix text-wc-accent + opacity)
- Performance: +3 a +8 puntos por URL (content-visibility)
- `/fit`: requiere fix adicional de x-intersect

---

## Prioridad de ejecución Fase A

1. 🔴 `/planes` — 72/89 → objetivo ≥78/100
2. 🔴 `/metodo` — 74/96 → objetivo ≥80/100
3. 🔴 `/proceso` — 68/89 → objetivo ≥75/100
4. 🔴 `/coaches` — 74/90 → objetivo ≥79/100
5. 🔴 `/nosotros` — 74/92 → objetivo ≥79/100
6. 🟠 `/presencial` — 74/96 → objetivo ≥79/100
7. 🟠 `/lanzamiento` — 74/100 → objetivo ≥79/100 (solo perf)
8. 🟠 `/fit` — 56/96 → objetivo ≥65/100 (fix x-intersect + opacity)
9. 🟠 `/faq` — 60/96 → objetivo ≥65/100 (solo CTA section)
10. 🟢 `/blog` — 70/100 → objetivo ≥75/100
11. 🟢 `/blog/show` — 73/90 → objetivo ≥77/100
12. 🟢 Legales (batch) — 73-76/92 → objetivo ≥78/100
13. 🟢 `/pago-exitoso` — 78/94 → objetivo ≥80/100 (nonce + accent)
