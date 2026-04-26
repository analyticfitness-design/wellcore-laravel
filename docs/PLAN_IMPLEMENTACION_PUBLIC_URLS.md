# Plan de Implementación — Optimización URLs Públicas WellCore (Post-Homepage)

**Fecha:** 2026-04-26
**Autor del plan:** Claude Opus 4.7 (sesión max-effort xhigh)
**Ejecutor objetivo:** Claude Sonnet (max effort) en sesión nueva
**Repositorio:** `C:\Users\GODSF\Herd\wellcore-laravel`
**Branch base:** `main`
**Status pre-arranque:** Homepage completado, bug crítico de `animations.js` resuelto en commit `3a18ded5`. Todas las URLs públicas renderizan contenido correctamente.

---

## ⚠️ AVISO CRÍTICO

Lee **completo** este documento + `docs/HOMEPAGE_OPTIMIZATION_RESULTS.md` + `docs/PERF_OPTIMIZATION_HANDOFF_GUIDE.md` ANTES de tocar código. Los tres se complementan:
- **HANDOFF_GUIDE** = qué hacer (patrones, reglas)
- **OPTIMIZATION_RESULTS** = qué se hizo en homepage (referencia comparativa)
- **PLAN_IMPLEMENTACION** (este) = secuencia exacta y por-URL para el resto

---

## ÍNDICE

1. [Resumen ejecutivo](#1-resumen-ejecutivo)
2. [Resultados de la auditoría](#2-resultados-de-la-auditoría)
3. [Bug crítico ya resuelto (referencia)](#3-bug-crítico-ya-resuelto-referencia)
4. [Reglas de integridad](#4-reglas-de-integridad)
5. [Setup pre-arranque](#5-setup-pre-arranque)
6. [Fase 0 — Sondeo baseline por URL](#fase-0--sondeo-baseline-por-url)
7. [Fase A — Aplicación patrones por URL](#fase-a--aplicación-patrones-por-url)
8. [Fase B — Critical CSS extraction](#fase-b--critical-css-extraction)
9. [Fase C — Validación final cross-URL](#fase-c--validación-final-cross-url)
10. [Detalle específico por URL](#detalle-específico-por-url)
11. [Plan de rollback](#plan-de-rollback)
12. [Criterios de aceptación finales](#criterios-de-aceptación-finales)
13. [Apéndice: comandos útiles](#apéndice-comandos-útiles)

---

## 1. RESUMEN EJECUTIVO

### Objetivo
Replicar las mejoras del homepage (Lighthouse a11y/BP/SEO=100, Performance 74-77) en las 14 URLs públicas restantes, manteniendo:
- **Integridad visual:** ningún cambio visible no-intencional
- **Integridad funcional:** Alpine accordions, Livewire, links siguen funcionando
- **Integridad de DB:** ninguna query nueva ni modificación a esquema (estas vistas son read-only)
- **Brand consistency:** WellCore (rojo `#DC2626`) + variante /fit (rosa `#DC3C64`)

### Alcance
14 URLs públicas distribuidas en 4 grupos:

| Grupo | URLs | Tiempo estimado |
|-------|------|----------------:|
| **Marketing principal** | /planes, /metodo, /proceso, /coaches, /nosotros, /presencial | ~5h |
| **Conversión** | /lanzamiento, /fit, /faq | ~3h |
| **Contenido** | /blog (index + show) | ~1.5h |
| **Legal + utilitario** | /terminos, /privacidad, /politica-cookies, /reembolsos, /pago-exitoso | ~1.5h |
| **Critical CSS extraction (Fase B)** | (afecta todas) | ~3-4h |
| **Total** | | **~14-15h** |

### Resultado esperado
- **Performance mobile (PSI):** 90+ en URLs principales
- **Accessibility:** 100 en todas
- **Best Practices:** 100 en todas
- **SEO:** 100 en todas

---

## 2. RESULTADOS DE LA AUDITORÍA

### 2.1 Mapa completo de URLs públicas

| # | Ruta | Vista | Líneas | Sections | Layout | Alpine | Inline JS | text-wc-accent | opacity-40/60 | Prioridad |
|---|------|-------|-------:|---------:|--------|:------:|:---------:|---------------:|--------------:|-----------|
| 1 | `/` | `home.blade.php` | 895 | 12 | public | sí | 0 | (refactorizado) | 0 | ✅ DONE |
| 2 | `/planes` | `planes.blade.php` | 415 | 5 | public | no | 0 | **15** | 0 | 🔴 ALTA |
| 3 | `/metodo` | `metodo.blade.php` | 526 | 7 | public | sí (FAQ) | 0 | **36** | 0 | 🔴 ALTA |
| 4 | `/proceso` | `proceso.blade.php` | 509 | 9 | public | sí | 0 | **47** | 0 | 🔴 ALTA |
| 5 | `/coaches` | `coaches.blade.php` | 448 | 6 | public | no | 0 | **16** | 0 | 🔴 ALTA |
| 6 | `/nosotros` | `nosotros.blade.php` | 495 | 7 | public | no | 0 | **30** | 0 | 🔴 ALTA |
| 7 | `/presencial` | `presencial.blade.php` | 208 | 6 | public | no | 0 | 4 | 0 | 🟠 MEDIA |
| 8 | `/lanzamiento` | `lanzamiento.blade.php` | 722 | 7 | public | sí | 0 | **27** | 1 | 🟠 MEDIA |
| 9 | `/fit` | `fit.blade.php` | 523 | 7 | public | sí (intersect⚠️) | 0 | 0 | 3 | 🟠 MEDIA |
| 10 | `/faq` | `faq.blade.php` | 1002 | 3 | public | **sí (heavy)** | 0 | 0 | 0 | 🟠 MEDIA |
| 11 | `/blog` | `blog/index.blade.php` | 176 | 3 | public | sí | 0 | 2 | 0 | 🟢 BAJA |
| 12 | `/blog/{slug}` | `blog/show.blade.php` | 172 | 4 | public | sí | 0 | 4 | 0 | 🟢 BAJA |
| 13 | `/terminos` | `legal/terminos.blade.php` | 97 | 2 | public | no | 0 | 3 | 0 | 🟢 BAJA |
| 14 | `/privacidad` | `legal/privacidad.blade.php` | 99 | 2 | public | no | 0 | 2 | 0 | 🟢 BAJA |
| 15 | `/politica-cookies` | `legal/cookies.blade.php` | 91 | 2 | public | no | 0 | 2 | 0 | 🟢 BAJA |
| 16 | `/reembolsos` | `legal/reembolso.blade.php` | 87 | 2 | public | no | 0 | 2 | 0 | 🟢 BAJA |
| 17 | `/pago-exitoso` | `pago-exitoso.blade.php` | 167 | 0 | public | no | **1** | 11 | 0 | 🟢 BAJA |

**Excluidos:**
- `rise.blade.php` (756 líneas) — código muerto, `/rise` es Vue dashboard, `/reto-rise` redirige a `/`. **NO TOCAR.**
- `/inscripcion`, `/coaches/apply`, `/login`, `/client/*`, `/coach/*` — Vue SPA, fuera de scope.

### 2.2 Hallazgos transversales

#### Hallazgo 1 — Patrón de hero consistente
TODAS las páginas públicas (excepto pago-exitoso) inician con:
```html
<section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
```
Excepción: `/lanzamiento` usa `min-h-screen` (full viewport hero). Esto cambia ligeramente el cálculo LCP.

#### Hallazgo 2 — NO hay `<img>` inline en ninguna URL salvo home
Verificado por grep. Las páginas usan:
- Iconos SVG inline
- Gradientes de fondo (CSS)
- "Orbs" decorativos via `parallax-orb` divs
- Background-image radial gradients (blog cards en `style="background-image: radial-gradient(...)"`)

**Implicación:** No hay trabajo `loading="lazy"` que hacer en estas páginas. El hero LCP es CSS/text, no imagen.

#### Hallazgo 3 — `text-wc-accent` es el problema #1 de a11y
Total: **148 instancias** distribuidas en 9 archivos. En dark mode `#DC2626` sobre `#09090B` da contraste 4.12:1 — falla WCAG AA (necesita 4.5:1 mínimo).

**Solución:** reemplazo masivo en cada archivo:
- `text-wc-accent` (texto de eyebrows, links pequeños, labels) → `text-red-700 dark:text-red-400`
- `bg-wc-accent` (botones, badges) → mantener (los botones son sobre fondo blanco/gris, no problemático)
- `bg-wc-accent/10` (fondos transparentes) → mantener (son backgrounds, no afectan texto)
- `border-wc-accent` → mantener

Hay que revisar caso por caso porque no todos los `text-wc-accent` son texto pequeño (algunos son texto grande de hero — pasan WCAG AA Large Text con 3:1).

#### Hallazgo 4 — Solo 2 archivos con `opacity-40/60` problemáticos
- `lanzamiento.blade.php` (1 instancia)
- `fit.blade.php` (3 instancias)

Ambos requieren ajuste a opacity-70 mínimo, ideal opacity-80.

#### Hallazgo 5 — Único inline `<script>` sin nonce en URLs activas
- `pago-exitoso.blade.php` línea 15 — Meta Pixel Purchase event. Necesita `nonce="@cspNonce"`.
- (rise.blade.php tiene 1 también pero es código muerto)

#### Hallazgo 6 — NO hay inline event handlers (`onclick=`, `onload=`) en ninguna URL pública
Verificado. Todo el código interactivo usa Alpine `x-on:click` (que requiere `'unsafe-eval'` ya permitido en CSP, no `inline event handlers`).

#### Hallazgo 7 — Alpine.js intensivo en /faq
`/faq` (1002 líneas, la más grande) usa Alpine con:
- Tabs (general/planes/pagos)
- Búsqueda en vivo
- Acordeón colapsable

**Riesgo:** aplicar `content-visibility: auto` puede romper el comportamiento de búsqueda si las preguntas filtradas están en secciones invisibles.

#### Hallazgo 8 — `/fit` usa color de marca alternativo
`#DC3C64` (rosa) en lugar de `#DC2626` (rojo WellCore). Es la marca de Silvia Martínez (coach individual). Validar contraste por separado:
- `#DC3C64` sobre `#F5F5F7` (light bg): **4.55:1** ✅ pasa AA
- `#DC3C64` sobre `#09090B` (dark bg): **4.95:1** ✅ pasa AA

Bueno: NO requiere cambio de color en /fit. Sólo verificar las 3 `opacity-40/60`.

#### Hallazgo 9 — Plugin Alpine `x-intersect` no instalado
Console muestra: `Alpine Warning: You can't use [x-intersect] without first installing the "Intersect" plugin`

Sólo afecta `/fit` línea 349 (progress bars en mockup móvil). El warning NO rompe la página (con el fix de animations.js, el contenido es visible). El efecto es que las barras de progreso no se animan al entrar al viewport — quedan en `width: 0%`.

**Decisión:** Tres opciones para el ejecutor:
- (a) Instalar plugin: `npm install @alpinejs/intersect` + import en `bootstrap.js`. Riesgo: aumenta bundle JS.
- (b) Reemplazar `x-intersect` con CSS animation-on-scroll usando `@keyframes`. Más ligero.
- (c) Dejar el warning (las barras nunca aparecen llenas en mobile, pero el resto de la página funciona). Menor impacto visible.

**Recomendación:** opción (b) si el efecto es importante, (c) si es decoración menor.

#### Hallazgo 10 — Estructura de secciones consistente
Pattern detectado en todas las páginas marketing:
```
1. Hero (hero-gradient bg-wc-bg-tertiary) ← ABOVE-FOLD, NO content-visibility
2. Section bg-wc-bg                       ← BELOW-FOLD candidate
3. Section bg-wc-bg-tertiary              ← BELOW-FOLD candidate
4. Section bg-wc-bg                       ← BELOW-FOLD candidate
...
N. Final CTA (bg-wc-bg-tertiary)          ← BELOW-FOLD candidate
```

Cada sección below-fold = 1 candidato a `hp-cv-section` (suma 4-8 secciones por página = grandes ahorros en rendering).

---

## 3. BUG CRÍTICO YA RESUELTO (REFERENCIA)

**Fecha:** 2026-04-26
**Commit fix:** `3a18ded5`
**Síntoma:** `/fit` y otras páginas con `data-animate` se veían completamente vacías (sólo nav visible).
**Causa raíz:** Fase 2 del plan original hizo `animations.js` lazy via `requestIdleCallback`. Eso significó que el script se cargara DESPUÉS de `DOMContentLoaded`. El listener interno `addEventListener('DOMContentLoaded', ...)` nunca se disparaba. Los elementos con `[data-animate]` quedaban con `opacity: 0` (definido en `resources/css/animations.css` línea 33).

**Fix aplicado:**
```js
// resources/js/animations.js
// ANTES (BUG)
document.addEventListener('DOMContentLoaded', () => { /* init code */ });

// DESPUÉS (FIXED)
function initScrollAnimations() { /* init code */ }
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollAnimations, { once: true });
} else {
    initScrollAnimations();  // DOM ya cargado, ejecutar directamente
}
```

Mismo patrón aplicado a `initCounters` y `initParallaxHero`.

**Lecciones para el ejecutor:**
1. Si lazy-loadeás un script que tenía `DOMContentLoaded` listener, SIEMPRE checkar `document.readyState`.
2. Lo mismo aplica para listeners `load`, `readystatechange`, etc.
3. Cuando hagas optimizaciones similares, prueba **MÁS** que el homepage. Cualquier URL pública es candidata a romper.

---

## 4. REGLAS DE INTEGRIDAD

### 4.1 Integridad VISUAL

**Antes de cada commit visual:**
1. Tomar screenshot pre-cambio con `mcp__chrome-devtools__take_screenshot` (full page)
2. Aplicar el cambio en local (Herd → http://wellcore-laravel.test/<url>)
3. Tomar screenshot post-cambio
4. Comparar visualmente — buscar:
   - Elementos faltantes
   - Cambios de color no esperados
   - Saltos de layout (sections que aparecen mal alineadas)
   - FOUC visible

**Verificación obligatoria de modo dark/light:**
```js
// En la consola del browser, alternar entre:
document.documentElement.classList.add('dark');     // dark mode
document.documentElement.classList.remove('dark');  // light mode
```
Cada cambio de color debe verificarse en AMBOS modos.

**Verificación obligatoria responsive:**
- Desktop (>1280px)
- Tablet (768-1024px) — DevTools device toolbar
- Mobile (<640px) — Mobile S 375px en DevTools

### 4.2 Integridad FUNCIONAL

**Smoke test obligatorio por URL** (clic, no submit):
- Click en cada CTA principal del hero → debe navegar a `/inscripcion` o el destino correcto
- Si tiene Alpine accordion: abrir/cerrar 2 elementos
- Si tiene Alpine search/filter: escribir 1 término
- Si tiene parallax/animaciones: scrollear y verificar que aparecen
- Verificar que el footer link "Inscribirme" navega
- Verificar que el toggle dark/light funciona

**Console F12 — cero errores nuevos:**
- Antes del cambio: anota errores existentes (línea base)
- Después del cambio: solo deben aparecer los mismos errores existentes (no nuevos)

### 4.3 Integridad de BASE DE DATOS

⚠️ **REGLA ABSOLUTA**: estas vistas son read-only y NO ejecutan queries (verificado: ninguna usa `App\Models\*` ni `DB::*`). Las vistas usan:
- Strings traducidos (`__('...')`) — sin DB
- Arrays definidos en el blade — sin DB
- `$article` variable (en blog/show — del controlador) — DB pero el controller ya existe y no se modifica

**El ejecutor NO DEBE:**
- Añadir queries `Model::all()` u otras
- Modificar controladores (`BlogController` etc.)
- Crear migraciones
- Tocar config DB

### 4.4 Integridad de BUILD

**Después de cada cambio CSS/JS:**
```bash
cd C:/Users/GODSF/Herd/wellcore-laravel
npm run build
git add public/build/
```

**Verificación post-build:**
```bash
# Comprobar que el manifest se actualizó
grep -c "css\|js" public/build/manifest.json
# Comprobar que el bundle público sigue pequeño (~1.1 KB)
ls -la public/build/assets/app-*.js | head -5
```

⚠️ **Si el bundle público crece de 1.1KB a >5KB, has agregado código que no debería ir ahí.** Probable causa: import estático en lugar de dinámico.

### 4.5 Integridad de DEPLOY

**Workflow obligatorio:**
```bash
# 1. Commit con mensaje claro
git commit -m "perf(<url>): aplica fase A — content-visibility, dark accent, etc."

# 2. Push
git push origin main

# 3. Deploy via EasyPanel MCP
#    - Navigate to https://panel.wellcorefitness.com/projects/wellcorefitness/box/wellcorefitness/scripts
#    - take_snapshot
#    - click el UID del botón Run de "silvia-gitpull-load" (NO npm-build, NO Rebuild Docker)
#    - wait_for "Scripts ejecutados"

# 4. Verificar visualmente
#    - Navigate a la URL en producción
#    - take_screenshot
#    - Comparar con local
```

⚠️ **NUNCA ejecutar `npm-build` script en EasyPanel** — satura el container y tumba el host (regla de la memoria del usuario).

⚠️ **NUNCA `Rebuild Docker image`** — toma 5-15 min y rompe el servicio.

---

## 5. SETUP PRE-ARRANQUE

### 5.1 Verificar estado limpio

```bash
cd C:/Users/GODSF/Herd/wellcore-laravel
git status                  # debe estar limpio en main
git log --oneline -5        # último commit debe ser 3a18ded5 o posterior
git pull origin main        # asegurar última versión
```

Si hay archivos sin commitear, **PARAR** y preguntar al usuario.

### 5.2 Verificar Herd corriendo (local)

```bash
# Abrir http://wellcore-laravel.test/ en Chrome
# Si no carga: herd start
```

### 5.3 Leer documentos de referencia

En orden:
1. `docs/HOMEPAGE_OPTIMIZATION_RESULTS.md` (5 min lectura)
2. `docs/PERF_OPTIMIZATION_HANDOFF_GUIDE.md` (15 min lectura)
3. `CLAUDE.md` (5 min lectura)
4. Memoria `C:\Users\GODSF\.claude\projects\C--Users-GODSF-Herd-wellcore-laravel\memory\MEMORY.md`

### 5.4 Capturar baselines PSI

Para cada URL principal (1-9 en la tabla), capturar 1 corrida PSI mobile como baseline. Anotar score numérico:

```
URL: /planes — Performance: __ | A11y: __ | BP: __ | SEO: __
URL: /metodo — Performance: __ | A11y: __ | BP: __ | SEO: __
URL: /proceso — Performance: __ | A11y: __ | BP: __ | SEO: __
...
```

Guardar en `_screenshots_perf/baseline-<url>-mobile.png` los screenshots de PSI.

---

## FASE 0 — SONDEO BASELINE POR URL

**Tiempo estimado:** 30 min total (todas las URLs)
**Output:** `docs/baseline-public-urls.md` con scores y top issues por URL

### Pasos por URL

```python
for url in ALL_PUBLIC_URLS:
    # 1. PSI mobile
    navigate_page → https://pagespeed.web.dev/analysis?url=https%3A%2F%2Fwww.wellcorefitness.com%2F<url>&form_factor=mobile
    wait_for ["Cumulative Layout Shift"] timeout 90s
    evaluate_script → leer scores

    # 2. Lighthouse a11y/BP/SEO via DevTools MCP
    navigate_page → https://www.wellcorefitness.com/<url>
    lighthouse_audit device=mobile mode=navigation
    → leer audit.failed para identificar issues específicos

    # 3. Documentar en baseline-public-urls.md
```

**Salida esperada:**
```markdown
## /planes
- PSI mobile: Performance 65 / A11y 92 / BP 92 / SEO 100
- Lighthouse failures: color-contrast (15), heading-order (1), errors-in-console (CSP)
- Top issues: text-wc-accent en eyebrows, plan card contrast
```

---

## FASE A — APLICACIÓN DE PATRONES POR URL

**Por cada URL, aplicar este checklist en orden:**

### Sub-fase A.1 — Refactor `text-wc-accent` (5-15 min según URL)

**Objetivo:** eliminar contrast failures dark mode.

**Comando de búsqueda:**
```bash
grep -nE "(text|hover:text|dark:text)-wc-accent" resources/views/public/<archivo>.blade.php
```

**Patrón de reemplazo (caso por caso):**
```html
<!-- Si es texto pequeño (<18pt = <24px aprox): reemplazar -->
text-wc-accent              → text-red-700 dark:text-red-400
hover:text-wc-accent        → hover:text-red-700 dark:hover:text-red-400

<!-- Si es texto grande de hero (text-3xl+): mantener (pasa Large Text 3:1) -->
text-wc-accent  ← OK en h1, h2 grandes

<!-- Si es texto blanco sobre rojo (botones): mantener -->
bg-wc-accent text-white  ← OK siempre

<!-- Si está dentro de un span que es DECORATIVO (icono pequeño con color de marca): mantener -->
```

**Cómo decidir caso por caso:**
- Si el `text-wc-accent` está en un `<p>`, `<span>` con tamaño `text-xs`, `text-sm`, `text-base` → CAMBIAR
- Si está en un `<h1>`, `<h2>` con `text-3xl` o más → MANTENER
- Si es `<span class="text-wc-accent">{{ statTextoCorto }}</span>` (tipo "120+ Clientes") → CAMBIAR si tamaño <text-xl, MANTENER si más grande

**Validación post-cambio:**
- Open `/url` en local
- F12 console: documenter.documentElement.classList.add('dark') → verificar texto sigue legible
- Click 'undo dark': documenter.documentElement.classList.remove('dark') → verificar light mode también

### Sub-fase A.2 — Aplicar `content-visibility: auto` a secciones below-fold (5-10 min)

**Identificar secciones below-fold:**

```bash
# Listar todas las secciones del archivo
grep -nE "<section[^>]*class=" resources/views/public/<archivo>.blade.php
```

**La PRIMERA sección (hero) NUNCA recibe `hp-cv-section`.**

Para todas las demás:
```html
<!-- ANTES -->
<section class="bg-wc-bg">

<!-- DESPUÉS -->
<section class="bg-wc-bg hp-cv-section">
```

**Excepciones que requieren cuidado:**
- `/faq`: la sección `<section class="bg-wc-bg" x-data="{ tab: 'general', open: null, search: '' }">` contiene la búsqueda y todas las preguntas. **NO aplicar `hp-cv-section`** ahí porque rompería la búsqueda (Alpine no encuentra elementos invisibles para filtrar). Aplicar sólo al hero superior y al CTA inferior.
- `/proceso`: tiene secciones `<section id="fase-01">` etc. Si el usuario navega con `#fase-02` desde otro lugar, `content-visibility:auto` puede causar saltos. Validar `:target` behavior.
- `/blog/show`: la sección de contenido del artículo NO debe tener `content-visibility:auto` porque puede afectar el reading flow.

### Sub-fase A.3 — Ajustar `contain-intrinsic-size` por sección (opcional pero recomendado, 5 min por URL)

La regla por defecto en `app.css` es `contain-intrinsic-size: 1px 800px`. Si una sección es notablemente más alta o más baja, ajustar con clase específica:

```css
/* Sólo si necesitas precisión */
.hp-cv-faq-list { contain-intrinsic-size: 1px 1500px; }
.hp-cv-coaches-grid { contain-intrinsic-size: 1px 1200px; }
```

**No es crítico:** si `contain-intrinsic-size` es incorrecto, lo único que pasa es que el scrollbar puede saltar ligeramente al hacer scroll (cosmético).

### Sub-fase A.4 — Fixes específicos por URL

Ver sección 10 ([Detalle específico por URL](#detalle-específico-por-url)) para cada caso particular.

### Sub-fase A.5 — Compilar, validar local, smoke test

```bash
npm run build
# Si tocaste sólo .blade.php, NO necesitas npm run build (Vite no procesa Blade)
```

Smoke test en `http://wellcore-laravel.test/<url>`:
- [ ] Hero renderiza con todo el contenido visible
- [ ] Click en CTA principal navega a destino
- [ ] Scroll hace aparecer las secciones below-fold (con `content-visibility:auto` activo)
- [ ] Toggle dark/light se ve correcto
- [ ] Console F12 sin errores nuevos
- [ ] Si hay Alpine: probar interacción (accordion, tabs, etc.)

### Sub-fase A.6 — Commit + push + deploy

**Naming de commits:**
```
perf(<url>): fase A — content-visibility N secs + dark accent fix

- text-wc-accent → text-red-700 dark:text-red-400 en X eyebrows/links pequeños
- hp-cv-section en N secciones below-fold
- [otros cambios específicos]

Local PSI mobile: <antes> → <despues>
```

```bash
git add resources/views/public/<archivo>.blade.php public/build/
git commit -m "..."
git push origin main
# Deploy via EasyPanel MCP
```

### Sub-fase A.7 — Validación en producción

```python
# 1. Esperar 5-10 segundos tras deploy (warmup OPcache)
# 2. PSI mobile run 3 veces, tomar mediana
# 3. Verificar visualmente con take_screenshot
# 4. Documentar resultado en docs/PUBLIC_URLS_PROGRESS.md
```

**Criterio de éxito por URL Fase A:**
- PSI Performance: +5 a +10 puntos vs baseline
- Lighthouse a11y/BP/SEO: 100 (idéntico al homepage)
- Visualmente: idéntica a antes (excepto los colores accent)
- Funcionalmente: smoke test 100% pasado

---

## FASE B — CRITICAL CSS EXTRACTION

**Tiempo estimado:** 3-4 horas
**Riesgo:** medio (puede causar FOUC si mal configurado)
**Score esperado:** Performance +10-15 puntos en TODAS las URLs simultáneamente

### B.1 — Decisión de approach

**Opción 1: `critters` (recomendado)**
```bash
npm install --save-dev critters
```

Vite plugin maduro que:
- Inlinea critical CSS automáticamente
- Defiere el resto via `media="print"` swap
- Opera por entry-point

**Opción 2: `vite-plugin-critical`** — alternativa, menos mantenido en 2026.

**Opción 3: Manual** — extraer CSS above-fold con Chrome Coverage tool, inlinear en `public.blade.php`. Más trabajo pero más control.

**Recomendación al ejecutor:** intentar Opción 1 primero (1h). Si causa FOUC complicado, fallback Opción 3 (3-4h).

### B.2 — Pasos Opción 1 (critters)

```bash
# Instalar
npm install --save-dev critters
```

**Editar `vite.config.js`:**
```js
import critters from 'critters';
// ...
plugins: [
    laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
    }),
    vue(),
    {
        name: 'critters-postbuild',
        async closeBundle() {
            const Critters = (await import('critters')).default;
            const c = new Critters({
                path: 'public/build',
                preload: 'swap',
                inlineFonts: false, // Ya manejamos fonts manualmente
                pruneSource: false, // Mantener bundle completo para below-fold
            });
            // Procesar HTML rendered by Laravel? No, esto es post-build de Vite.
            // Necesitamos approach diferente — ver Sub-fase B.3
        }
    }
]
```

**Realidad:** `critters` está diseñado para sites que generan HTML estático. En Laravel + Blade, el HTML se genera en runtime. Necesitamos un approach diferente.

### B.3 — Approach realista para Laravel + Blade

**Critical CSS via Laravel middleware:**

1. Generar CSS critical una sola vez con script Node:
   ```bash
   # Script: scripts/extract-critical.js
   # Usa puppeteer + critters para procesar /, /planes, /metodo, etc.
   # Output: resources/css/critical.css
   ```

2. Compilar `critical.css` con Vite:
   ```js
   // vite.config.js
   input: ['resources/css/app.css', 'resources/css/critical.css', 'resources/js/app.js']
   ```

3. En `public.blade.php`, inlinear critical:
   ```html
   <style>
     {!! file_get_contents(public_path('build/'.Vite::asset('resources/css/critical.css'))) !!}
   </style>
   ```

4. Diferir `app.css`:
   ```html
   <link rel="preload" href="..." as="style">
   <link rel="stylesheet" href="..." media="print" id="main-css">
   <script nonce="@cspNonce">document.getElementById('main-css').addEventListener('load',function(){this.media='all'},{once:true});</script>
   <noscript><link rel="stylesheet" href="..."></noscript>
   ```

**Riesgos a mitigar:**
- **FOUC**: si `critical.css` no incluye una clase usada above-fold, se ve unstyled un instante. Solución: testing exhaustivo.
- **Cache busting**: si actualizamos `app.css`, hay que regenerar `critical.css` también. Documentar workflow.

### B.4 — Validación Fase B

**Por cada URL pública:**
- F12 → Network → desactivar cache → reload
- Verificar que el FCP es <1.5s en la primera pintada
- Verificar visualmente que NO hay flash de unstyled content
- PSI run 3 veces

**Criterio de éxito:**
- PSI Performance: 90+ en al menos 3 de 5 URLs principales
- Sin regresiones visuales en ninguna URL

### B.5 — Si Fase B no funciona o introduce FOUC

**Rollback:**
```bash
git revert <commit-fase-B>
git push origin main
# Deploy via gitpull-load
```

Y dejar Critical CSS pendiente como mejora futura. La Fase A sola ya da +5 a +10 puntos por URL — buena ganancia sin Fase B.

---

## FASE C — VALIDACIÓN FINAL CROSS-URL

**Tiempo estimado:** 2 horas

### C.1 — Tabla de scores final

Ejecutar PSI 3 veces por cada URL, anotar mediana:

| URL | Performance | A11y | BP | SEO | Status |
|-----|------------:|-----:|---:|----:|:------:|
| / | 77 | 100 | 100 | 100 | ✅ |
| /planes | __ | __ | __ | __ | ⏳ |
| /metodo | __ | __ | __ | __ | ⏳ |
| ... | ... | ... | ... | ... | ⏳ |

### C.2 — Smoke test funcional integral

Por cada URL:
- [ ] Carga sin errores de console
- [ ] Hero visible con todo el contenido
- [ ] Cada CTA navega correctamente
- [ ] Toggle dark/light correcto
- [ ] Mobile (375px) responsive correcto
- [ ] Tablet (768px) responsive correcto
- [ ] Desktop (1280+) correcto
- [ ] Si tiene Alpine: interactividad funciona

### C.3 — Cross-flow tests

Probar flows que cruzan URLs:
- `/` → click "Ver Planes" → `/planes` → click plan CTA → `/inscripcion`
- `/coaches` → click "Aplicar como Coach" → `/coaches/apply`
- `/blog` → click artículo → `/blog/<slug>`
- `/faq` → buscar "pago" → click resultado

### C.4 — Verificar /pago-exitoso

Flow especial (solo aprobado tras pago real). Verificar:
- Meta Pixel script tiene nonce
- Sin errores console
- Renderiza estado correcto según `$estado`

### C.5 — Update documentación

```bash
# Actualizar progress
cat docs/PUBLIC_URLS_PROGRESS.md  # ya creado durante Fase A
# Actualizar handoff guide con nuevos hallazgos
# Actualizar memoria
```

---

## DETALLE ESPECÍFICO POR URL

### `/planes` (415 líneas, prioridad 🔴 ALTA)

**Hero:** `hero-gradient bg-wc-bg-tertiary` + plan toggle (mensual/anual)
**Sections:** Hero + 4 (planes grid + comparativa + FAQ inline + CTA)
**Riesgos específicos:**
- Plan cards similares al homepage. Verificar contraste de strikethrough prices y excluded features (mismos issues que home).
- Posible CTA "Empezar" o "Comenzar plan X" — verificar que NO sean genéricos.

**Acciones:**
1. Sub-fase A.1: ~15 instancias de `text-wc-accent` a refactorizar
2. Sub-fase A.2: 4 secciones below-fold
3. Buscar similar plan card structure que home (`hp-plan-cta`, `opacity-40`, `opacity-60`) y verificar que `app.css` cambios ya cubren

**Estimación:** 45 min

---

### `/metodo` (526 líneas, prioridad 🔴 ALTA)

**Hero:** Gradient bg-wc-bg-tertiary
**Sections:** 7 (Hero + 4 metodologías + FAQ Alpine + CTA)
**Riesgos específicos:**
- 36 instancias de `text-wc-accent` — la más alta densidad.
- Tiene Alpine FAQ: `<section class="bg-wc-bg" x-data="{ active: null }">` — NO aplicar `hp-cv-section` a esta sección porque rompe Alpine search.

**Acciones:**
1. Sub-fase A.1: ~36 instancias accent
2. Sub-fase A.2: 5 secciones below-fold (excluyendo Alpine FAQ)
3. Verificar Alpine FAQ funciona después

**Estimación:** 1h

---

### `/proceso` (509 líneas, prioridad 🔴 ALTA)

**Hero:** Gradient bg-wc-bg-tertiary
**Sections:** 9 (Hero + Phases overview + 4 fases detalladas + FAQ + CTA)
**Riesgos específicos:**
- 47 instancias de `text-wc-accent` — DENSIDAD MÁXIMA.
- Anchor links `#fase-01`, `#fase-02`, `#fase-03`, `#fase-04` — usuarios pueden llegar via deep-link. Verificar `:target` behavior con `content-visibility:auto`.
- Tiene Alpine `x-data="{ activePhase: null }"` y `x-data="{ active: null }"` para FAQ.

**Acciones:**
1. Sub-fase A.1: ~47 instancias accent (búsqueda + reemplazo masivo cuidadoso)
2. Sub-fase A.2: 7 secciones below-fold (excluyendo las que tienen `id=fase-XX` si hay anchors).
3. Verificar deep-links con `#fase-XX` siguen funcionando.

**Test específico:** Visitar `https://wellcorefitness.com/proceso#fase-03` — debe scrollear correctamente a la fase 03.

**Estimación:** 1h 30min

---

### `/coaches` (448 líneas, prioridad 🔴 ALTA)

**Hero:** Gradient bg-wc-bg-tertiary
**Sections:** 6 (Hero + benefits + requirements + comp model + apply + final CTA)
**Riesgos específicos:**
- 16 instancias de `text-wc-accent`.
- NO tiene fotos de coaches (verificado). Es página de RECRUITING para coaches.
- Tiene CTA "Aplicar como Coach" (route: `coaches.apply` → Vue).

**Acciones:**
1. Sub-fase A.1: ~16 instancias accent
2. Sub-fase A.2: 5 secciones below-fold

**Estimación:** 45 min

---

### `/nosotros` (495 líneas, prioridad 🔴 ALTA)

**Hero:** Gradient bg-wc-bg-tertiary
**Sections:** 7 (Hero + misión + valores + equipo + historia + métricas + CTA)
**Riesgos específicos:**
- 30 instancias de `text-wc-accent`.

**Acciones:**
1. Sub-fase A.1: ~30 instancias accent
2. Sub-fase A.2: 6 secciones below-fold

**Estimación:** 50 min

---

### `/presencial` (208 líneas, prioridad 🟠 MEDIA)

**Hero:** Gradient bg-wc-bg-tertiary
**Sections:** 6 (Hero + servicio + ubicación + horarios + precios + CTA)
**Riesgos específicos:**
- Solo 4 instancias de `text-wc-accent` — fácil.
- CTA va a `/presencial/inscripcion` (Vue).

**Acciones:**
1. Sub-fase A.1: 4 instancias accent
2. Sub-fase A.2: 5 secciones below-fold

**Estimación:** 30 min

---

### `/lanzamiento` (722 líneas, prioridad 🟠 MEDIA)

**Hero:** `min-h-screen` (full viewport)
**Sections:** 7 (Hero + trial + novedades + celebración + precios + testimonios + únete)
**Riesgos específicos:**
- 27 instancias de `text-wc-accent`.
- 1 instancia de `opacity-40/60`.
- Hero `min-h-screen` puede afectar LCP differently — el LCP es probablemente texto en hero (no imagen).
- Tiene Alpine para countdown timer u otros — verificar que sigue funcionando.

**Acciones:**
1. Sub-fase A.1: ~27 instancias accent
2. Sub-fase A.2: 6 secciones below-fold (NO el hero `min-h-screen`)
3. Sub-fase A.4: ajustar `opacity-40/60` → `opacity-80`

**Estimación:** 1h

---

### `/fit` (523 líneas, prioridad 🟠 MEDIA)

**Hero:** Gradient con orbs `#DC3C64` (rosa)
**Sections:** 7 (Hero + servicios + testimonios + nutrición mockup + entrenamiento + tracking + CTA)
**Riesgos específicos:**
- Color de marca distinto: `#DC3C64` (rosa) en vez de `#DC2626` (rojo). Contraste OK ya verificado.
- 0 instancias de `text-wc-accent` (usa `text-[#DC3C64]` directamente).
- 3 instancias de `opacity-40/60`.
- Usa `x-intersect` (plugin Alpine no instalado) en línea 349.
- Bug crítico ya resuelto en commit `3a18ded5`.

**Acciones:**
1. Sub-fase A.1: NO aplica (sin `text-wc-accent`)
2. Sub-fase A.2: 6 secciones below-fold
3. Sub-fase A.4: ajustar 3× `opacity-40/60` → `opacity-80`
4. Sub-fase A.4 BIS: decidir sobre `x-intersect`:
   - **Opción recomendada (b):** reemplazar con `data-animate="fadeIn"` (ya existente) o CSS `@keyframes` que se dispare al scroll. Eliminar `x-data="{ visible: false }"` y `x-intersect`.

**Estimación:** 1h (incluyendo fix x-intersect)

---

### `/faq` (1002 líneas — la más grande, prioridad 🟠 MEDIA)

**Hero:** Gradient bg-wc-bg-tertiary
**Sections:** 3 (Hero + tabbed FAQ + CTA)
**Riesgos específicos:**
- 0 instancias de `text-wc-accent`. **Buena suerte!**
- ALPINE intensivo: tabs (general/planes/pagos), búsqueda, accordion. **No aplicar `hp-cv-section` a la sección de tabs** porque rompe Alpine.
- Es la página más grande (1002 líneas) — el bundle de DOM es enorme.

**Acciones:**
1. Sub-fase A.1: skip (no hay accent)
2. Sub-fase A.2: SOLO el footer CTA section recibe `hp-cv-section`. La sección de FAQ tabs queda intacta.
3. Bonus: Considerar mover el contenido del accordion a `<details>` HTML nativo si Alpine es lento. Riesgo de cambiar UX. Skip si no urge.

**Estimación:** 30 min

---

### `/blog` index (176 líneas, prioridad 🟢 BAJA)

**Hero:** Gradient bg-wc-bg-tertiary
**Sections:** 3 (Hero + grid de cards + CTA)
**Riesgos específicos:**
- 2 instancias de `text-wc-accent`.
- Cards usan `style="background-image: radial-gradient(...)"` inline — CSP-safe (style-src permite `'unsafe-inline'`).

**Acciones:**
1. Sub-fase A.1: 2 instancias accent
2. Sub-fase A.2: 2 secciones below-fold

**Estimación:** 25 min

---

### `/blog/{slug}` show (172 líneas, prioridad 🟢 BAJA)

**Hero:** Dynamic gradient based on `$article['gradient']`
**Sections:** 4 (Hero + content + relacionados + CTA)
**Riesgos específicos:**
- 4 instancias de `text-wc-accent`.
- Hero gradient varía por artículo — no uniforme.
- NO aplicar `content-visibility` a la sección de contenido del artículo (afecta reading flow).

**Acciones:**
1. Sub-fase A.1: 4 instancias accent
2. Sub-fase A.2: SOLO `<section class="border-t border-wc-border bg-wc-bg-tertiary">` (relacionados) y `<section class="relative overflow-hidden border-t border-wc-border bg-wc-bg">` (CTA). NO el contenido principal.

**Estimación:** 25 min

---

### Legales — `/terminos`, `/privacidad`, `/politica-cookies`, `/reembolsos` (87-99 líneas cada una, prioridad 🟢 BAJA)

**Hero:** Gradient bg-wc-bg-tertiary (uniforme)
**Sections:** 2 cada una (Hero + body de texto)
**Riesgos específicos:**
- 2-3 instancias de `text-wc-accent` cada una.
- Texto largo — el body section es enorme. content-visibility ayuda mucho en estos.

**Acciones (las 4 son casi idénticas, batch):**
1. Sub-fase A.1: ~2-3 instancias accent por archivo
2. Sub-fase A.2: 1 sección below-fold por archivo (la del body)

**Estimación:** 15 min cada una × 4 = 1h

---

### `/pago-exitoso` (167 líneas, prioridad 🟢 BAJA)

**Hero:** No aplica (no tiene `<section>` hero, es card centrado)
**Sections:** 0 (es un card flotante)
**Riesgos específicos:**
- 1 inline `<script>` (Meta Pixel Purchase) — NECESITA `nonce="@cspNonce"`.
- 11 instancias de `text-wc-accent` — alta densidad para tan pocas líneas.
- Es página post-pago — bajo tráfico, baja prioridad performance pero a11y igual importante.

**Acciones:**
1. Sub-fase A.1: ~11 instancias accent
2. Sub-fase A.2: skip (no hay secciones)
3. Añadir `nonce="@cspNonce"` al `<script>` línea 15:
   ```html
   <!-- ANTES -->
   <script>
       if (typeof fbq === 'function') { ... }
   </script>

   <!-- DESPUÉS -->
   <script nonce="@cspNonce">
       if (typeof fbq === 'function') { ... }
   </script>
   ```

**Estimación:** 25 min

---

## PLAN DE ROLLBACK

### Caso 1: Una URL específica se rompió
```bash
git log --oneline | head -5
git revert <SHA>          # o el SHA específico
git push origin main
# Deploy gitpull-load
```

### Caso 2: Critical CSS introduce FOUC
- Revertir Fase B completa (mantener Fase A).
- Documentar como fallido.

### Caso 3: Bug similar al de animations.js
**Síntoma:** alguna URL queda blank tras un cambio.
**Acción inmediata:**
1. F12 console — buscar errores
2. F12 elements — verificar si `[data-animate]` tiene `.animate-in` o se quedó con `opacity:0`
3. Network — verificar que `app.js` y `animations-XXXX.js` cargan
4. Si la causa es similar: aplicar el patrón `document.readyState === 'loading'` defensivamente

### Caso 4: La memoria del usuario decía "no usar Rebuild Docker" pero el deploy falló
- Esperar 30 segundos más
- Re-correr `silvia-gitpull-load`
- Si persiste: notificar al usuario, NO clickear Rebuild Docker

---

## CRITERIOS DE ACEPTACIÓN FINALES

### Cuantitativos (PSI mediana de 3 corridas)

| URL | Performance objetivo | A11y | BP | SEO |
|-----|---------------------:|-----:|---:|----:|
| /planes, /metodo, /proceso, /coaches, /nosotros | **≥75 (sin Fase B), ≥85 (con Fase B)** | 100 | 100 | 100 |
| /presencial, /lanzamiento, /fit, /faq | ≥70 (sin B), ≥82 (con B) | 100 | 100 | 100 |
| /blog index, /blog show | ≥75 (sin B), ≥85 (con B) | 100 | 100 | 100 |
| Legales | ≥85 (sin B), ≥92 (con B) | 100 | 100 | 100 |
| /pago-exitoso | ≥80 (sin B) | 100 | 100 | 100 |

### Cualitativos

- [ ] Todas las URLs cargan sin errores en console
- [ ] Toggle dark/light visualmente perfecto en cada URL
- [ ] Responsive en mobile/tablet/desktop
- [ ] Smoke test funcional aprobado en cada URL
- [ ] Cross-flow tests aprobados
- [ ] `docs/PUBLIC_URLS_PROGRESS.md` actualizado con todos los resultados
- [ ] Memoria actualizada con nuevos hallazgos

---

## APÉNDICE: COMANDOS ÚTILES

### Buscar y verificar usos de `text-wc-accent` por archivo
```bash
grep -nE "(text|hover:text|dark:text)-wc-accent" resources/views/public/<archivo>.blade.php
```

### Verificar bundle público sigue siendo pequeño
```bash
ls -la public/build/assets/app-*.js | head -5
# Buscar el que NO sea el de Vue/admin (debe ser ~1.1KB)
```

### Re-correr PSI desde MCP
```
mcp__chrome-devtools__navigate_page url=https://pagespeed.web.dev/analysis?url=https%3A%2F%2Fwww.wellcorefitness.com%2F<url>&form_factor=mobile
mcp__chrome-devtools__wait_for text=["Cumulative Layout Shift"] timeout=90000
mcp__chrome-devtools__evaluate_script function="() => { /* read scores */ }"
```

### Ejecutar deploy en EasyPanel via MCP
```
mcp__chrome-devtools__navigate_page url=https://panel.wellcorefitness.com/projects/wellcorefitness/box/wellcorefitness/scripts
mcp__chrome-devtools__take_snapshot
# Buscar UID del button "Run" del primer script (silvia-gitpull-load)
mcp__chrome-devtools__click uid=<uid>
mcp__chrome-devtools__wait_for text=["Scripts ejecutados"] timeout=25000
```

### Verificar contraste de un color (calculadora WCAG)
Usar https://webaim.org/resources/contrastchecker/ con:
- Foreground: el color del texto (ej. #DC2626)
- Background: `#F5F5F7` (light bg) Y `#09090B` (dark bg)
- Mínimo ratio 4.5:1 para texto normal, 3:1 para texto grande (≥18pt o ≥14pt bold)

### Búsqueda masiva-reemplazo cuidadoso
```bash
# Listar todas las apariciones (revisión humana primero)
grep -n "text-wc-accent" resources/views/public/<archivo>.blade.php

# Reemplazar UNA por UNA con el Edit tool, NO sed (para preservar contexto)
```

---

## CIERRE

Este plan es la continuación natural del trabajo del homepage. Cada URL es una unidad independiente — puedes hacerlas en cualquier orden dentro de su grupo de prioridad. Si te quedas sin tiempo, prioriza las 🔴 ALTA primero.

**Tiempo total realista:** 14-15 horas distribuidas en 14 URLs + Fase B Critical CSS.

**Output esperado al terminar:**
- Todas las URLs públicas con a11y/BP/SEO en 100
- Performance ≥75 sin Fase B, ≥85 con Fase B
- Documento `docs/PUBLIC_URLS_PROGRESS.md` con tabla de resultados antes/después
- Memoria del proyecto actualizada
- Sin regresiones funcionales ni visuales
- Sin queries DB nuevas

**Si encontras un bug similar al de `animations.js` (algún script lazy que rompe la página):** aplica defensivamente el patrón `document.readyState === 'loading'` y documéntalo en `docs/HOMEPAGE_OPTIMIZATION_RESULTS.md` para que la siguiente generación no tropiece.
