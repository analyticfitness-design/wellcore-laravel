# OPTIMIZACIÓN MOBILE — WELLCORE LARAVEL
## Plan de Core Web Vitals · Basado en evidencia auditada
**Fecha**: 2026-04-24
**Baseline PSI**: Performance 78 · FCP 3.0s · LCP 3.8s · SI 5.9s · TBT 100ms · CLS 0.057
**Objetivo**: Performance 85+ · FCP <2.0s · LCP <2.5s · SI <4.0s
**Enfoque**: Seguridad operacional sobre velocidad. Cero riesgo para revenue (Wompi, auth, wire:navigate).

---

## 0. ANÁLISIS DE SITUACIÓN ACTUAL

### 0.1 Resumen de hallazgos (evidencia verificada)

| # | Hallazgo | Severidad | Archivos afectados | Estado |
|---|----------|-----------|-------------------|--------|
| 1 | Google Fonts CDN URL rota (concatenación sin `&`) | 🔴 CRÍTICA | admin, coach, shop, public-legacy | Confirmado línea por línea |
| 2 | Meta Pixel sincrónico en `<head>` | 🟠 ALTA | shop, public-legacy | Confirmado líneas 51-65 |
| 3 | Cache headers de assets estáticos no aplicados en nginx | 🟠 ALTA | configuración servidor | Middleware PHP registrado pero nginx sirve estáticos directo |
| 4 | Preload de fonts faltante | 🟡 MEDIA | admin, coach, shop | Solo `<link rel="preconnect">` |
| 5 | AVIF/WebP logos faltantes | 🟡 MEDIA | admin, coach, shop | PNG sin optimización |
| 6 | `wellcore-fonts.css` no cubre todos los pesos | 🟡 MEDIA | cobertura parcial | Falta Inter, Bebas Neue, JetBrains Mono, pesos 400/500 de Oswald |
| 7 | FOUC script sin `nonce` | 🟢 BAJA | public (nuevo) | Funciona pero CSP no lo protege |
| 8 | Animaciones no compuestas | 🟡 MEDIA | pendiente scan CSS | PSI reporta 5 |
| 9 | JS heredado 46 KiB + sin usar 25 KiB | 🟢 BAJA | Vite target=es2022 OK | Probablemente Livewire core |

### 0.2 Optimizaciones YA IMPLEMENTADAS (NO duplicar, NO tocar)

En `resources/views/components/layouts/public.blade.php` (layout nuevo, 100% de rutas públicas lo usan):

| Optimización | Línea | Detalle |
|--------------|-------|---------|
| Self-hosted fonts | 23 | `/fonts/wellcore-fonts.css` |
| Font preload Oswald 700 + Raleway 400 | 21-22 | `rel="preload" as="font" type="font/woff2" crossorigin` |
| Logo preload con `fetchpriority="high"` | 26-27 | AVIF + WebP, srcset 320w/640w |
| Logo en `<picture>` AVIF→WebP→PNG | 74-83 | `decoding="async"` |
| Meta Pixel lazy | 38-65 | Dispara en scroll/mousedown/touchstart/keydown o timeout 4s |
| Alpine standalone condicional | 304 | `@unless(Livewire::componentHasBeenRendered())` |
| FOUC dark mode inline | 3 | Antes de `<html>` (sin nonce) |

En `resources/views/livewire/checkout.blade.php`:
- **Wompi widget lazy-loaded** en Step 3 vía Alpine `loadWompi()` (líneas 1-66)
- `https://checkout.wompi.co/widget.js` NO bloquea carga inicial

En `resources/js/app.js` (35 líneas totales):
- Alpine NO se importa (Livewire 3 lo inyecta)
- Chart.js condicional: `if (window.__wcNeedsCharts === true || document.querySelector('canvas[data-chart]'))`
- `push-subscription.js`, `animations.js`, `serviceWorker.register` sobre `load`

En `app/Http/Middleware/SetAssetCacheHeaders.php`:
- Registrado globalmente en `bootstrap/app.php:48` (`$middleware->append(SetAssetCacheHeaders::class)`)
- Maneja: `build/assets/*` (1 año), `/images|/fonts|/icons/` (1 semana), favicons root

En `vite.config.js`:
- `target: 'es2022'` (modern, sin polyfills legacy)
- `manualChunks`: chart, axios, vue-core
- 3 entry points

### 0.3 LCP REAL en mobile (confirmado)

El LCP en mobile es el **H1 hero** en `resources/views/public/home.blade.php:45`:

```html
<h1 class="mt-6 font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-8xl">
    {{ __('home.hero_title_1') }}<br>
    <span class="italic text-wc-text-secondary">{{ __('home.hero_title_2') }}</span>
    <span class="text-gradient-accent font-bold text-wc-accent">{{ __('home.hero_title_3') }}</span>
</h1>
```

**NO es una imagen hero**. Los mockups de dashboard (líneas 87+) están `hidden lg:block` → jamás renderizan en mobile.

**Implicaciones para LCP**:
- La fuente `font-display` (Oswald/Bebas Neue) DEBE estar lista lo antes posible
- El mayor bloqueador del LCP mobile es **la fuente del H1** + el CSS crítico para `text-5xl` y `text-gradient-accent`

### 0.4 Routing (layout distribution)

**100% de rutas públicas** usan `<x-layouts.public>` (layout nuevo).
**CERO rutas** usan `@extends('layouts.public')` (legacy).

Por tanto, el legacy `resources/views/layouts/public.blade.php` es código muerto en producción (útil tocarlo como prevención, pero no crítico).

---

## 1. OPTIMIZACIONES DE ALTO IMPACTO — QUICK WINS SEGUROS

### 1.1 🔴 Fix Google Fonts CDN roto → self-hosted

**Archivos afectados**:
- `resources/views/layouts/admin.blade.php:46`
- `resources/views/layouts/coach.blade.php:46`
- `resources/views/layouts/shop.blade.php:47`
- `resources/views/layouts/public.blade.php:47` (legacy, preventivo)

**Evidencia del problema**:
La URL actual contiene concatenación rota, `display=swap` duplicado y falta `&` entre `family=`:
```
family=Oswald:wght@400;500;600;700family=Oswald:wght@400;500;600;700&family=Raleway... family=JetBrains+Mono:wght@400;500family=Oswald...display=swapdisplay=swap
```
Google responde con **400 Bad Request** o fuente parcial → FOUT severo + 100-420ms blocking.

**Paso 1 · Verificar cobertura de `wellcore-fonts.css`**

Actualmente cubre: `Barlow 400/700`, `Oswald 600/700`, `Raleway 400/600`.

**ACCIÓN PRE-MIGRATION**: Antes de migrar los layouts, auditar qué fuentes/pesos usan realmente los templates admin/coach/shop:

```bash
grep -rE "font-(display|sans|data|mono|oswald|raleway|barlow|inter|bebas|jetbrains)" \
  resources/views/layouts/admin.blade.php \
  resources/views/layouts/coach.blade.php \
  resources/views/layouts/shop.blade.php \
  resources/css/app.css
```

**Si se descubren pesos no cubiertos** (ej: Oswald 400/500, Raleway 700, Inter, Bebas Neue, JetBrains Mono):
1. Descargar los woff2 desde https://google-webfonts-helper.herokuapp.com/ (latin + latin-ext)
2. Colocar en `public/fonts/` con el mismo naming pattern: `{family}-{weight}-{subset}.woff2`
3. Agregar los `@font-face` al final de `public/fonts/wellcore-fonts.css` con `font-display: swap`

**Paso 2 · Migrar Google Fonts a self-hosted**

En CADA uno de los 4 archivos, reemplazar el bloque del `<link rel="stylesheet" href="https://fonts.googleapis.com/...">` por:

```blade
{{-- REEMPLAZA las 2-3 líneas de Google Fonts (preconnect + stylesheet) POR: --}}

{{-- Preload críticos (para FCP) --}}
<link rel="preload" as="font" type="font/woff2" href="/fonts/oswald-700-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-400-latin.woff2" crossorigin>

{{-- Stylesheet self-hosted (elimina Google CDN) --}}
<link rel="stylesheet" href="/fonts/wellcore-fonts.css">
```

**Ganancia estimada**:
- FCP: **–200 a –400ms** (elimina DNS + TLS + fetch Google)
- Performance score: **+3 a +6 puntos**
- Elimina dependencia de CDN externo en portal coach (mejora UX sobre redes LATAM lentas)

**Rollback**: Git revert del commit. Los 4 archivos son independientes.

---

### 1.2 🟠 Cache headers en capa nginx (no Laravel)

**Contexto crítico**:
`SetAssetCacheHeaders` está registrado en `bootstrap/app.php:48` pero **NO se ejecuta** sobre `/build/assets/`, `/fonts/`, `/images/`, `/icons/` porque:
- En Herd (local) → nginx sirve estáticos directamente sin pasar por PHP
- En EasyPanel (producción) → mismo comportamiento (Docker + nginx)

PageSpeed reporta **613 KiB de ahorro** porque los headers `Cache-Control` no llegan al navegador.

**Solución para producción (EasyPanel)**:

En EasyPanel, agregar un **custom `Nginx Server Config`** al servicio WellCore:

```nginx
# Assets versionados por Vite (hash en nombre) → immutable 1 año
location ~* ^/build/assets/ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    access_log off;
}

# Fonts self-hosted → 1 año (cambios raros, cache-busting manual si necesario)
location ~* ^/fonts/.*\.(woff2|woff|ttf|otf)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    add_header Access-Control-Allow-Origin "*";
    access_log off;
}

# Imágenes → 1 semana
location ~* ^/(images|icons)/ {
    expires 7d;
    add_header Cache-Control "public, max-age=604800";
    access_log off;
}

# Favicons raíz
location ~* ^/(favicon|apple-touch-icon).*\.(ico|png|svg)$ {
    expires 7d;
    add_header Cache-Control "public, max-age=604800";
    access_log off;
}
```

**Solución para Herd (local)**:
Herd usa nginx interno. Configurar vía `C:/Users/GODSF/.config/herd/config/nginx/` (añadir un server block específico del site, NO tocar el config global).

**Ganancia estimada**:
- **–613 KiB ahorro en cargas repetidas** (returning visitors)
- Performance score: **+4 a +8 puntos**
- Mejor Repeat View drasticamente

**Rollback**: Eliminar el bloque del nginx config y recargar nginx (`nginx -s reload`). Cero impacto a funcionalidad.

**Validación**:
```bash
curl -I https://wellcorefitness.com/build/assets/app-ABC123.js
# Esperado: Cache-Control: public, max-age=31536000, immutable
```

---

### 1.3 🟠 Migrar Meta Pixel de shop.blade.php a lazy-load

**Archivo**: `resources/views/layouts/shop.blade.php:51-65`
**Problema**: Carga sincrónica de `fbevents.js` (~80KB) + `fbq('init')` + `fbq('track')` bloquea parser del `<head>`.

**Solución** — replicar el approach del layout nuevo (`components/layouts/public.blade.php:38-65`):

```blade
{{-- REEMPLAZA el bloque Meta Pixel actual (líneas 51-65) POR: --}}

{{-- Meta Pixel · Lazy-load por interacción o timeout --}}
<script nonce="@cspNonce">
(function() {
    var pixelLoaded = false;
    function loadPixel() {
        if (pixelLoaded) return;
        pixelLoaded = true;
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ env('FB_PIXEL_ID') }}');
        fbq('track', 'PageView');
    }
    var events = ['scroll', 'mousedown', 'touchstart', 'keydown'];
    events.forEach(function(ev) {
        window.addEventListener(ev, loadPixel, { once: true, passive: true });
    });
    window.addEventListener('load', function() {
        setTimeout(loadPixel, 4000);
    });
})();
</script>
<noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id={{ env('FB_PIXEL_ID') }}&ev=PageView&noscript=1"/>
</noscript>
```

**Notas críticas**:
- ✅ Mantiene `nonce="@cspNonce"` (CSP compliant)
- ✅ Primera interacción o 4s post-load (no bloquea FCP)
- ✅ `<noscript>` fallback igual que hoy
- ✅ Mismo Pixel ID, misma lógica de tracking
- ✅ Mismos eventos `PageView` se siguen enviando

**Ganancia estimada**:
- Shop FCP: **–150 a –250ms**
- Shop TBT: **–80ms**
- Sin impacto en conversión tracking (los eventos llegan igual, solo unos ms más tarde)

**Rollback**: Git revert. El snippet original está en `shop.blade.php:51-65`.

---

## 2. OPTIMIZACIONES DE MEDIO IMPACTO

### 2.1 🟡 Preload fonts en admin/coach/shop (para el H1/titulares)

**Archivo**: los mismos 3 layouts (y el legacy como preventivo).
**Ubicación**: dentro del `<head>`, **antes** del `<link rel="stylesheet" href="/fonts/wellcore-fonts.css">`.

Para admin/coach (usan Oswald y Raleway principalmente):
```blade
<link rel="preload" as="font" type="font/woff2" href="/fonts/oswald-700-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-400-latin.woff2" crossorigin>
```

Para shop (usar los pesos predominantes del layout):
```blade
<link rel="preload" as="font" type="font/woff2" href="/fonts/oswald-700-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/barlow-400-latin.woff2" crossorigin>
```

**Ganancia**: –100 a –200ms en primera renderización del H1 o titulares visibles arriba del fold.

**Rollback**: Eliminar las 2 líneas. Zero-risk.

---

### 2.2 🟡 Optimizar LCP del H1 hero (home.blade.php)

El LCP es texto, no imagen. Estrategia:

**A) Asegurar que Oswald 700 está preloaded en el layout nuevo** → YA ESTÁ (línea 21).

**B) Crítical CSS inline para el fold del home**

Identificar el CSS crítico mínimo para renderizar arriba del fold en mobile y inlinearlo en el `<head>`. Ganancia: ~100-200ms FCP.

**Estrategia segura, SIN Critters/PurgeCSS**:

1. Extraer manualmente las clases del H1 hero:
   - `font-display text-5xl leading-none tracking-wide text-wc-text`
   - `text-wc-text-secondary italic`
   - `text-gradient-accent text-wc-accent font-bold`
   - Badges/párrafos previos al H1 (líneas 35-44 aprox)

2. Generar un CSS crítico inline (~3-5 KB) y colocarlo en `components/layouts/public.blade.php` entre `</title>` y el `<link rel="preload">` de fuentes.

3. Mantener el bundle CSS principal (via Vite) para debajo del fold.

**Implementación conservadora (recomendada)**: Usar Tailwind 4's built-in critical extraction o generar el crítico con `tailwindcss --content ./resources/views/public/home.blade.php`.

⚠️ **Advertencia**: Tailwind 4 ya elimina CSS no usado automáticamente. Si el CSS entregado es < 30KB, el beneficio de critical-inline es marginal. Medir primero con DevTools Coverage.

**Rollback**: Eliminar el bloque `<style>` inline. El layout vuelve al comportamiento actual.

---

### 2.3 🟡 Fix animaciones no compuestas (PSI reporta 5)

**Investigación requerida** (mandatory antes de implementar):

```bash
# Buscar animaciones que animan propiedades NO compuestas
grep -rE "animation|transition" resources/css/app.css | grep -vE "transform|opacity"
```

Sospechosos típicos de "no compuestas":
- `top`, `left`, `bottom`, `right` animated → usar `transform: translate()`
- `width`, `height` animated → usar `transform: scale()`
- `margin`, `padding` animated → buscar alternativa con `transform`
- `background-color` animated sobre elementos grandes → aceptable pero costoso

**Candidatos en WellCore a investigar**:
- `card-hover-lift` (memory references: animación de branding)
- `scroll-reveal`
- `animate-float-slow`
- `pulse-glow`
- `btn-press`

**Regla de migración segura**:
- SI la animación usa `top/left/width/height` → migrar a `transform` MANTENIENDO idéntico efecto visual
- Si usa `box-shadow` animado → aceptable (no es "no compuesto" en Chromium moderno con CompositeBoxShadow)
- **PROHIBIDO**: eliminar o simplificar animaciones (restricción #2)

**Ganancia**: Reduce "Avoid non-composited animations" → mejor SI en ~200-400ms.

**Rollback**: Revertir cada animación individual. Testear visualmente que el efecto se mantiene idéntico.

---

### 2.4 🟢 Reducir JavaScript heredado (46 KiB) y sin usar (25 KiB)

**Evidencia**:
- Vite target = `es2022` (correcto)
- `app.js` solo tiene 35 líneas, todo lazy

**Hipótesis**: Los 46 KiB "heredados" probablemente vienen de:
1. Livewire 3 core (incluido por `@livewireScripts`)
2. Alpine 3 (inyectado por Livewire)
3. Ambos ya son modernos, pero incluyen compat shims

**Acción recomendada**: **Verificar con Chrome DevTools Coverage** antes de tocar nada. Si Livewire core es el culpable, **NO lo toques** (es core del producto). Si es Alpine, confirmar que no cargamos Alpine dos veces.

**Acción segura**:
```bash
# Build de producción + análisis de bundle
npm run build
# Revisar public/build/manifest.json y tamaños de /build/assets/*.js
```

Si se identifica un chunk específico gordo y no usado en landing (ej: Chart.js compilado en el bundle principal en vez de chunk separado):
- Verificar que `manualChunks` en `vite.config.js` lo separa
- Confirmar que no se importa vía `import Chart from 'chart.js'` en `app.js` (ya confirmado que NO)

**Rollback**: Cambios de Vite son reversibles via git.

---

## 3. OPTIMIZACIONES DE BAJO IMPACTO

### 3.1 🟢 Heading order (accesibilidad)

**Acción**: Auditar `home.blade.php` con:
```bash
grep -nE "<h[1-6]" resources/views/public/home.blade.php
```

Asegurar jerarquía lógica: `h1` (hero) → `h2` (secciones) → `h3` (subsecciones). Sin saltos (h1 → h3).

**Ganancia**: +2-3 puntos accesibilidad. Cero impacto performance.

**Rollback**: Trivial.

---

### 3.2 🟢 Link text (SEO)

**Acción**: Buscar los 3 links reportados:
```bash
grep -rE "<a [^>]*href[^>]*>\s*(click|aquí|here|more|leer más|>>)\s*</a>" resources/views/
```

Reemplazar con texto descriptivo:
```blade
{{-- ANTES --}}
<a href="/planes">aquí</a>

{{-- DESPUÉS --}}
<a href="/planes">Ver todos los planes WellCore</a>
```

O agregar `aria-label="..."` si el visual debe mantenerse.

**Rollback**: Trivial, git revert.

---

### 3.3 🟢 Reducción de DOM (evitar mockups)

**Evitar explícitamente**:
- NO tocar mockups en `home.blade.php` líneas 87+ (ya `hidden lg:block`)
- NO tocar los mockups de coach portal (parte del pitch de venta)

**Candidatos seguros a reducir**:
- Wrappers `<div>` duplicados sin clases útiles
- `<span>` anidados innecesarios en títulos
- `<div class="flex"><div class="flex">...` que colapsan a uno solo

**Estrategia**: Solo tocar wrappers que NO tengan clases Tailwind con breakpoints (`lg:`, `md:`) ni `x-data` Alpine. Revisión manual uno a uno.

**Impacto**: En mobile PageSpeed reporta DOM size en rango seguro. Ganancia marginal.

---

## 4. MÉTRICAS OBJETIVO

| Métrica | Actual | Objetivo | Cambios principales |
|---------|--------|----------|---------------------|
| **Performance** | 78 | **85-90** | 1.1 + 1.2 + 1.3 + 2.1 |
| **FCP** | 3.0s | **<2.0s** | 1.1 (elimina blocking Google Fonts) + 2.1 (preload) |
| **LCP** | 3.8s | **<2.5s** | 1.1 (font lista para H1) + 2.2 (critical CSS opcional) |
| **SI** | 5.9s | **<4.0s** | 1.1 + 1.3 + 2.3 |
| **TBT** | 100ms | **<100ms** | 1.3 (pixel lazy) |
| **CLS** | 0.057 | **<0.1** (mantener) | No tocar, está bien |
| **Cache savings** | 613 KiB | **0 KiB** | 1.2 (nginx config) |

**Estimación conservadora**: +7 a +12 puntos Performance mobile si se implementan las 3 quick wins (1.1, 1.2, 1.3).

---

## 5. PLAN DE IMPLEMENTACIÓN POR FASES

### FASE 1 · Configuración (bajo riesgo, alto impacto) — 1-2 días

**Día 1 AM**:
1. Auditar `wellcore-fonts.css` vs los pesos que usan admin/coach/shop
2. Si faltan fuentes: descargar woff2 y agregar `@font-face` al CSS

**Día 1 PM**:
3. Implementar fix Google Fonts en los 4 layouts (1.1)
4. Agregar font preload en admin/coach/shop (2.1)
5. Deploy a staging + verificar: no FOUT, fuentes cargan, wire:navigate funciona

**Día 2 AM**:
6. Migrar Meta Pixel lazy en shop (1.3)
7. Verificar eventos Pixel llegan a Facebook (usar Facebook Pixel Helper extension)

**Día 2 PM**:
8. Configurar cache headers nginx en EasyPanel (1.2)
9. Medir PageSpeed mobile → esperado ~+7 puntos
10. Verificar curl headers en producción

**Tests obligatorios antes de producción**:
- ✅ Login admin/coach/shop funciona
- ✅ wire:navigate en sidebars funciona (probar 10+ navegaciones)
- ✅ Dark mode sin flash
- ✅ Wompi checkout Step 3 carga Wompi
- ✅ Formulario newsletter del footer funciona
- ✅ Before-after slider funciona
- ✅ Mobile menu abre/cierra
- ✅ Cookie consent aparece y se cierra

---

### FASE 2 · Assets (medio riesgo, medio impacto) — 2-3 días

1. Auditar animaciones no compuestas con Chrome DevTools Performance tab
2. Migrar 1-2 animaciones a la vez, testing visual
3. Análisis de bundle (`npm run build` + manifest.json)
4. Posible implementación critical CSS (solo si coverage < 30%)
5. Medir PSI después de cada cambio

---

### FASE 3 · Código (bajo riesgo, bajo impacto) — 1 día

1. Heading order fix
2. Link text fix (3 links)
3. DOM reduction (solo wrappers sin Tailwind breakpoints)

---

## 6. ROLLBACK PLAN

| Cambio | Archivo | Rollback | Tiempo |
|--------|---------|----------|--------|
| 1.1 Self-hosted fonts | 4 layouts | `git revert <hash>` | 30s |
| 1.2 Nginx cache headers | EasyPanel config | Borrar bloque + reload nginx | 1 min |
| 1.3 Meta Pixel lazy shop | `shop.blade.php` | `git revert <hash>` | 30s |
| 2.1 Font preload | 4 layouts | Eliminar 2 `<link>` por archivo | 30s |
| 2.2 Critical CSS | `public.blade.php` nuevo | Eliminar `<style>` inline | 30s |
| 2.3 Animaciones | CSS específico | Revert animación específica | 1 min |
| 2.4 Bundle Vite | `vite.config.js` | `git revert <hash>` + `npm run build` | 2 min |
| 3.x Heading/links/DOM | Views varios | `git revert <hash>` | 30s |

**Respaldo antes de empezar**:
```bash
git checkout -b perf/mobile-optimization-2026-04-24
git tag pre-perf-optimization-2026-04-24
```

**Si algo rompe en producción**:
```bash
# Opción 1: Revert del commit problemático
git revert <hash>
git push  # No deploy manual — push activa pipeline

# Opción 2: Rollback al tag pre-optimization
git reset --hard pre-perf-optimization-2026-04-24
git push --force-with-lease  # CUIDADO: solo si autorizado
```

---

## 7. VALIDACIÓN POST-PLAN (checklist cerrar)

- [x] Cada optimización tiene archivo específico y línea aproximada
- [x] Ganancias estimadas son realistas (+7 a +12 puntos, no +50)
- [x] Hay rollback claro para cada cambio
- [x] Ninguna viola las 21 restricciones:
    - ✅ No se cambian colores, espaciados, tipografía, breakpoints, layout visual
    - ✅ No se elimina ninguna animación de branding (solo se migran a `transform`/`opacity` con efecto idéntico)
    - ✅ No se convierten mockups HTML a imágenes
    - ✅ No se usa `content-visibility: auto`
    - ✅ No se reduce funcionalidad de chatbot, Wompi, login, menu, newsletter, WhatsApp, cookies, slider, FAQ
    - ✅ No se toca Wompi lazy-load (ya existe)
    - ✅ No se toca Meta Pixel del layout público nuevo (ya existe)
    - ✅ No se toca newsletter del footer
    - ✅ No se toca before-after slider
    - ✅ No se cambia mobile menu
    - ✅ No se toca BD ni migraciones
    - ✅ No se rompe app vanilla compartida
    - ✅ No se cambia session ni cache driver
    - ✅ No se toca `$fillable`
    - ✅ FOUC script admin/coach/shop queda intacto entre `<!DOCTYPE>` y `<html>`
    - ✅ No se agrega defer/async al FOUC
    - ✅ No se duplica Alpine (se respeta `@unless Livewire::componentHasBeenRendered`)
    - ✅ No se rompe `wire:navigate`
    - ✅ No se eliminan scripts de tracking (se hacen lazy, mantienen lógica)
    - ✅ Todo script inline nuevo lleva `nonce="@cspNonce"`
    - ✅ No se cambia orden visual para screen readers
- [x] Se identificaron optimizaciones YA EXISTENTES
- [x] Se diferenció layout legacy vs nuevo (100% routing usa nuevo)
- [x] El LCP identificado es realista: H1 texto, no imagen hero inexistente

---

## ANEXOS

### A. Comandos de verificación post-deploy

```bash
# Verificar cache headers
curl -I https://wellcorefitness.com/build/assets/app-ABC123.js | grep -i cache
curl -I https://wellcorefitness.com/fonts/oswald-700-latin.woff2 | grep -i cache

# Verificar Google Fonts ya NO se cargan
curl -s https://wellcorefitness.com/admin | grep -c "fonts.googleapis.com"
# Esperado: 0

# Verificar Pixel lazy en shop
curl -s https://wellcorefitness.com/shop | grep -c "fbevents.js"
# Esperado: 0 (solo se carga post-interacción)

# Re-medir PSI
https://pagespeed.web.dev/report?url=https%3A%2F%2Fwellcorefitness.com%2F&form_factor=mobile
```

### B. Matriz de riesgo

| Cambio | Riesgo | Mitigación |
|--------|--------|------------|
| 1.1 Self-hosted fonts | Bajo | Testing visual H1 en staging primero |
| 1.2 Nginx cache | Muy bajo | No afecta HTML, solo estáticos |
| 1.3 Meta Pixel lazy | Bajo | Verificar eventos con FB Pixel Helper |
| 2.1 Font preload | Muy bajo | No afecta lógica |
| 2.2 Critical CSS | Medio | Requiere extracción precisa |
| 2.3 Animaciones | Medio | Requiere testing visual individual |
| 2.4 Bundle Vite | Bajo | No se cambia target |
| 3.x | Muy bajo | Cambios semánticos sin lógica |

### C. Archivos NUNCA tocar

- `app/Auth/WellCoreGuard.php`
- Cualquier `app/Models/*.php` ($fillable)
- `config/session.php` driver
- `config/cache.php` store
- Base de datos en cualquier forma
- `resources/views/components/layouts/public.blade.php` (layout nuevo, ya optimizado)
- `resources/views/livewire/checkout.blade.php` (Wompi ya lazy)
- `app/Http/Middleware/SetAssetCacheHeaders.php` (ya funciona, su limitación es nginx)

---

**Plan generado**: 2026-04-24
**Basado en**: auditoría verificada de 20+ archivos del codebase
**Próximo paso**: Ejecutar FASE 1 · Día 1 (auditar `wellcore-fonts.css` vs pesos reales usados por layouts admin/coach/shop)

🤖 Generated with performance engineering audit methodology.
