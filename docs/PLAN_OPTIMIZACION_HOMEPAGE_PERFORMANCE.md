# Plan de optimización — Homepage WellCore Mobile (objetivo ≥90)

**Fecha:** 2026-04-25
**Score actual mobile:** 69 / 100 (Lighthouse, PageSpeed Insights)
**Score objetivo:** ≥90 / 100
**Scope:** Solo homepage público (`/`) — **NO toca dashboards, app/Livewire/Client, app/Livewire/Coach, app/Livewire/Admin, models, DB ni auth**.

---

## 1. Diagnóstico (datos reales del reporte mobile)

### Métricas Lab (Lighthouse)

| Métrica | Valor | Estado | Comentario |
|---------|-------|--------|------------|
| FCP | 0.8 s | ✅ pass | Bien |
| **LCP** | **1.2 s** | ⚠️ average | Element render delay 1,670 ms |
| TBT | 110 ms | ✅ pass | Bien |
| CLS | 0 | ✅ pass | Excelente |
| Speed Index | 1.5 s | ⚠️ average | Marginalmente |

### Categorías

| Categoría | Score |
|-----------|-------|
| **Performance** | **69** |
| Accessibility | 91 |
| Best Practices | 92 |
| SEO | 92 |

### LCP Breakdown (clave del 69)

```
Subparte                                Duración
Time to First Byte                       20 ms   ✅
Resource load delay                     190 ms   ⚠️
Resource load duration                   60 ms   ✅
Element render delay                  1,670 ms   ❌❌❌
```

**Elemento LCP detectado:**
```html
<img src=".../images/hero/dashboard-mobile.avif"
     alt="" width="280" height="575"
     loading="eager" fetchpriority="high">
```
Ya está bien optimizado (AVIF 17 KB, dimensiones, `fetchpriority="high"`). El problema NO es la imagen sino que **el navegador no puede pintarla** porque sigue construyendo CSSOM bloqueado por fuentes.

---

## 2. Top oportunidades (priorizadas por impacto/esfuerzo)

| # | Oportunidad | Ahorro | Esfuerzo | Archivos a tocar |
|---|------------|--------|----------|------------------|
| 1 | **Eliminar render-blocking de `wellcore-fonts.css`** | **1,200 ms** | M | `public/fonts/wellcore-fonts.css`, `resources/views/components/layouts/public.blade.php` |
| 2 | **Cache headers correctos** (alpine, fonts, imágenes) | 77 KiB visitas repetidas | S | nginx EasyPanel o `.htaccess` |
| 3 | **Romper cadena de fuentes serial** (HTML→CSS→4 woff2) | 469 ms ruta crítica | S | `components/layouts/public.blade.php` |
| 4 | **Reducir CSS sin usar** | 17 KiB | M | `resources/css/app.css` (1590 líneas) |
| 5 | **Reducir JS sin usar** | 26 KiB | M | `resources/js/app.js`, Vite manualChunks |
| 6 | **Composite animations** (360-401 elementos) | TBT/INP | M | clases CSS de home (counters, ticker) |
| 7 | **Forced reflow 148 ms** | TBT | M | identificar script (probable counter `data-counter`) |
| 8 | **DOM size 1,004 elementos** | rendering perf | L | `resources/views/public/home.blade.php` (905 líneas) |
| 9 | Image delivery (logos legacy) | 13 KiB | S | `resources/views/components/layouts/public.blade.php` |

> S = pequeño (<1h), M = medio (1-3h), L = grande (>3h)

---

## 3. Plan por fases

### FASE 1 — Quick wins (esperado: +10-12 pts → ~80)

**Tiempo estimado: 2-3h. Riesgo: muy bajo.**

#### 1.1 Liberar render del CSS de fuentes

**Problema actual** (`components/layouts/public.blade.php:21-23`):
```html
<link rel="preload" as="font" type="font/woff2" href="/fonts/oswald-700-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-400-latin.woff2" crossorigin>
<link rel="stylesheet" href="/fonts/wellcore-fonts.css">  <!-- BLOQUEA 1,200 ms -->
```

**Cambios:**
1. Los preloads están **desalineados con la realidad above-the-fold**. Lighthouse muestra que la home descarga `oswald-600`, `raleway-500`, `raleway-600`, `raleway-700` — no `oswald-700` ni `raleway-400`. Cambiar:

```html
<!-- Preload SOLO de las 4-5 fuentes críticas above-the-fold (latin, sin cyrillic/vietnamese) -->
<link rel="preload" as="font" type="font/woff2" href="/fonts/oswald-600-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-500-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-600-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-700-latin.woff2" crossorigin>

<!-- Inline críticos: SOLO @font-face latin de las 4 fuentes above-fold -->
<style>
  @font-face{font-family:'Oswald';font-style:normal;font-weight:600;font-display:swap;src:url('/fonts/oswald-600-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
  @font-face{font-family:'Raleway';font-style:normal;font-weight:500;font-display:swap;src:url('/fonts/raleway-500-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
  @font-face{font-family:'Raleway';font-style:normal;font-weight:600;font-display:swap;src:url('/fonts/raleway-600-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
  @font-face{font-family:'Raleway';font-style:normal;font-weight:700;font-display:swap;src:url('/fonts/raleway-700-latin.woff2') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}
</style>

<!-- Resto del catálogo: NO bloqueante (truco media=print) -->
<link rel="preload" href="/fonts/wellcore-fonts.css" as="style">
<link rel="stylesheet" href="/fonts/wellcore-fonts.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/fonts/wellcore-fonts.css"></noscript>
```

**Impacto esperado:** `-1,000 ms` de render-blocking, LCP element render delay baja de 1,670 ms a ~400-600 ms. Score mobile +8-10 pts.

#### 1.2 Cache headers para assets estáticos

**Problema:** `alpine.min.js` (43 KB) y `wellcore-fonts.css` (17 KB) llegan **sin Cache-Control**. Cada visita los re-descarga.

**Solución (nginx EasyPanel)** — añadir en el server block:
```nginx
location ~* \.(woff2?|avif|webp|svg|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}
```

Si no hay acceso a nginx desde EasyPanel, alternativa con middleware Laravel:
```php
// app/Http/Middleware/StaticAssetsCacheHeaders.php
// Aplicado solo a /fonts/*, /js/alpine.min.js, /images/*
```

**Importante:** los assets versionados (Vite hash) ya están en `public/build/*` con cache OK. Los que necesitan fix son los **no versionados** servidos desde `public/`.

**Impacto:** TTI mejora en visitas repetidas. No afecta primera visita pero Lighthouse lo cuenta.

#### 1.3 Lazy load del logo del nav (no es LCP)

`components/layouts/public.blade.php:74-83` — el logo tiene `fetchpriority="high"`. Pero el LCP es el dashboard hero, no el logo. Compite por bandwidth.

```diff
- <img ... fetchpriority="high" decoding="async">
+ <img ... decoding="async">
```

Y quitar los 2 `<link rel="preload">` de logos (líneas 26-27) — el preload del logo lo descarga ANTES del CSS de fuentes, robando bandwidth al LCP image.

**Impacto:** LCP image gana prioridad real. -100-200 ms LCP.

---

### FASE 2 — Optimización de animaciones y JS (esperado: +5-7 pts → ~87)

**Tiempo estimado: 3-5h. Riesgo: bajo (se puede revertir clase por clase).**

#### 2.1 Composite animations (401 elementos animados)

Lighthouse reporta 401 elementos con animaciones no compuestas. Auditar `resources/css/app.css` líneas 970-1280 (clases `.hp-*`):
- Animaciones que usen `top`, `left`, `width`, `height`, `margin`, `padding`, `box-shadow`, `background-position` → migrar a `transform` y `opacity`.
- Añadir `will-change: transform` ÚNICAMENTE a elementos animados visibles (max ~20).
- Wrap secciones below-fold con `content-visibility: auto`:
  ```css
  .hp-section-below-fold { content-visibility: auto; contain-intrinsic-size: 800px; }
  ```

#### 2.2 Identificar y eliminar Forced Reflow (148 ms "[unattributed]")

Probables culpables:
- `data-counter` animations en hero (8, 100, 20, 94, 100%)
- Alpine `x-data` con measurements (chatOpen, mobileMenu)
- ScrollObserver de testimonios

**Cómo encontrarlo:**
```bash
# Performance trace en Chrome DevTools, filtrar por "Layout" / "Forced reflow"
# Probable archivo: resources/js/counters.js o similar
```

Patrón seguro:
```js
// MAL — fuerza reflow en cada frame
el.style.width = el.offsetWidth + 1 + 'px';

// BIEN — usar requestAnimationFrame + read-then-write
requestAnimationFrame(() => {
  const w = el.offsetWidth; // read
  requestAnimationFrame(() => {
    el.style.width = (w + 1) + 'px'; // write
  });
});
```

#### 2.3 Code-split JS (26 KiB sin usar)

`resources/js/app.js` se carga entero en el homepage. Mover features no críticas a chunks lazy:
```js
// En counters.js o donde corresponda:
if (document.querySelector('[data-counter]')) {
  import('./counters').then(m => m.init());
}
if (document.querySelector('.hp-testimonials')) {
  import('./testimonials').then(m => m.init());
}
```

Configurar Vite (`vite.config.js`):
```js
build: {
  rollupOptions: {
    output: {
      manualChunks: {
        // Separa Vue/Pinia del bundle del homepage público
        vue: ['vue', 'pinia', 'vue-router'],
        chart: ['chart.js'], // si aplica
      }
    }
  }
}
```

---

### FASE 3 — Reducción de DOM y CSS sin usar (esperado: +3-5 pts → ~92)

**Tiempo estimado: 4-6h. Riesgo: medio (cambios en home.blade.php).**

#### 3.1 Lazy mount de secciones below-fold

`resources/views/public/home.blade.php` tiene 905 líneas y produce 1,004 elementos DOM. Estrategia:

1. Identificar las secciones below-fold (testimonios, FAQ embebido, footer extendido).
2. Wrappear cada una con `content-visibility: auto` + `contain-intrinsic-size`.
3. Imágenes below-fold: `loading="lazy"` + `decoding="async"` (verificar que no estén ya).

#### 3.2 PurgeCSS / Tailwind purge agresivo

`resources/css/app.css` (1590 líneas) trae 17 KiB sin usar en mobile. Auditar:
- Clases `.hp-*` que solo aplican al admin/coach (probables sobras de copiar).
- Animations / keyframes definidos pero no usados.

```bash
# Verificar
npx tailwindcss --content "resources/views/public/home.blade.php" --minify -o /tmp/check.css
diff <(grep -oE '\b\w+' /tmp/check.css | sort -u) <(grep -oE '\b\w+' public/build/assets/app-*.css | sort -u)
```

---

### FASE 4 — Pulido final (esperado: +1-2 pts → ≥93)

**Tiempo: 1-2h.**

#### 4.1 Fix accesibilidad (sube Accessibility 91→95+, no Performance directamente)
- Contraste insuficiente (2 ocurrencias) → ajustar tokens en `resources/css/app.css` `@theme` block.
- 3 vínculos sin texto descriptivo → añadir `aria-label`.
- Headings desordenados (`h1>h3` saltando `h2`) → reordenar.
- Touch targets <48px → revisar nav mobile.

#### 4.2 Eliminar errores de consola (Best Practices)
Abrir DevTools en homepage prod y resolver cada warning/error.

#### 4.3 Image delivery 13 KiB
Lighthouse marca 13 KiB de ahorro en imágenes. Probable culpable: logos del footer aún en webp full-size cuando podrían ser AVIF responsive.

---

## 4. Plan de validación (anti-regresiones)

Antes de cada deploy:

1. **Chrome DevTools en local**: `npm run dev` + Lighthouse mobile → comparar score antes/después.
2. **Verificar visualmente** en local con `darkMode` ON y OFF.
3. **Smoke test rutas críticas que comparten `components/layouts/public.blade.php`**:
   - `/` (home)
   - `/planes`
   - `/nosotros`
   - `/metodo`
   - `/coaches`
   - `/blog`
4. **Verificar que NO se rompe** Alpine en formularios (`@unless(\Livewire\Livewire::componentHasBeenRendered())` line 317).
5. **Push a main, gitpull-load via EasyPanel MCP**, re-correr PageSpeed real en `https://www.wellcorefitness.com/`.

---

## 5. Lo que NO se va a tocar (garantía de integridad)

- ❌ `app/Livewire/Client/**` (11 componentes dashboard cliente)
- ❌ `app/Livewire/Admin/**`
- ❌ `app/Livewire/Coach/**`
- ❌ `app/Livewire/Rise/**`
- ❌ `app/Models/**`
- ❌ `database/migrations/**`
- ❌ `app/Auth/**`, `app/Http/Middleware/Ensure*`
- ❌ Tablas MySQL `wellcore_fitness`
- ❌ Layouts `client.blade.php`, `admin.blade.php`, `coach.blade.php`, `rise.blade.php`, `shop.blade.php`, `app.blade.php`

**Solo se modifica:**
- ✏️ `resources/views/components/layouts/public.blade.php` (head + preloads)
- ✏️ `resources/views/public/home.blade.php` (lazy sections opcional fase 3)
- ✏️ `public/fonts/wellcore-fonts.css` (opcional limpieza)
- ✏️ `resources/css/app.css` (purge fase 3)
- ✏️ `vite.config.js` (manualChunks fase 2)
- ✏️ Posible nuevo middleware `StaticAssetsCacheHeaders` o config nginx

---

## 6. Decisiones que necesito de Daniel antes de implementar

1. **Acceso nginx EasyPanel**: ¿prefieres middleware Laravel para cache headers, o tocamos nginx? (middleware es más portable, nginx es más eficiente).
2. **Limpieza de fuentes**: ¿borramos los woff2 de `cyrillic*`/`vietnamese*` del repo? El sitio es LATAM-only por ahora — son ~25 archivos × ~20 KB = 500 KB de assets sin uso. Dejaríamos solo `latin` y `latin-ext`.
3. **Ejecución por fases o todo junto**: ¿implementamos Fase 1 sola y validamos el salto a ~80, o vamos directo Fase 1+2 al PR?
4. **Agente delegado**: ¿uso `la-10-performance` para implementar (recomendado por CLAUDE.md) o lo hago directamente?

---

## 7. Resultado esperado

| Fase | Score mobile esperado | Tiempo | Riesgo |
|------|----------------------|--------|--------|
| Inicial | 69 | — | — |
| Después Fase 1 | ~80 | 2-3h | muy bajo |
| Después Fase 2 | ~87 | +3-5h | bajo |
| Después Fase 3 | ~92 | +4-6h | medio |
| Después Fase 4 | **≥93** | +1-2h | bajo |

**Total estimado:** 10-16 horas de trabajo distribuido en 4 PRs.
