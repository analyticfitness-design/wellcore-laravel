# Guía de Handoff — Optimización Performance WellCore Otras URLs

**Para:** Próximo agente Sonnet (max effort) que continuará optimizando URLs públicas de WellCore más allá del homepage.
**Fecha:** 2026-04-26
**Autor:** Claude Opus 4.7 (sesión que ejecutó el plan original del homepage)
**Contexto previo:** `C:\Users\GODSF\Downloads\PLAN_OPTIMIZACION_HOMEPAGE_WELLCORE.md` + `docs/HOMEPAGE_OPTIMIZATION_RESULTS.md`

---

## ÍNDICE

1. [Estado actual](#1-estado-actual)
2. [Stack y arquitectura](#2-stack-y-arquitectura)
3. [URLs candidatas a optimizar](#3-urls-candidatas-a-optimizar)
4. [Reglas inviolables](#4-reglas-inviolables)
5. [Lo que YA está optimizado (no repetir)](#5-lo-que-ya-está-optimizado-no-repetir)
6. [Metodología que funcionó (4 fases)](#6-metodología-que-funcionó-4-fases)
7. [Patrones de código exactos](#7-patrones-de-código-exactos)
8. [Cómo deployar](#8-cómo-deployar)
9. [Cómo validar](#9-cómo-validar)
10. [Trampas y errores que evitar](#10-trampas-y-errores-que-evitar)
11. [Pendiente crítico: Critical CSS](#11-pendiente-crítico-critical-css)
12. [Credenciales y accesos](#12-credenciales-y-accesos)
13. [Plan sugerido por URL](#13-plan-sugerido-por-url)

---

## 1. ESTADO ACTUAL

### Homepage `/` — Lighthouse mobile (PSI):

| Categoría | Antes | Después |
|-----------|------:|--------:|
| Performance | 69 | **74-77** ¹ |
| Accessibility | 91 | **100** |
| Best Practices | 92 | **100** |
| SEO | 92 | **100** |

¹ PSI lab data es ruidoso (3 corridas: 48/74/77, primera fue cold-load outlier por SW + chunks).

### Resto de URLs — Sin medir aún

Las URLs públicas comparten `resources/views/components/layouts/public.blade.php`, lo que significa que **ya heredan** los fixes de:
- Fuentes no-bloqueantes
- CSP nonces (Best Practices)
- Cache headers (1 año + immutable)
- DOM/CSS reducido en homepage

Pero **NO heredan** los fixes específicos del homepage (`hp-cv-section`, `.hp-eyebrow` colors, link-text "Inscribirme") porque están en las vistas individuales.

---

## 2. STACK Y ARQUITECTURA

```
Backend     : Laravel 13.1.1 + PHP 8.4
Frontend    : Blade + Alpine.js 3 + Tailwind CSS 4 (público)
              Vue 3.5 + Vue Router 4 + Pinia (privado, solo /client|/coach|/admin|/rise)
Build       : Vite 8 (target es2022, manualChunks configurados)
DB          : MySQL `wellcore_fitness` COMPARTIDA con app vanilla legacy
              ⚠️ NUNCA tocar la app vanilla en C:\Users\GODSF\Herd\wellcorefitness
Hosting     : EasyPanel (Docker container) + NGINX delante
              Ruta dentro del container: /code (NO /var/www/html)
Deploy      : git push origin main + script silvia-gitpull-load via Chrome DevTools MCP
              ⚠️ NUNCA Rebuild Docker, NUNCA npm-build en EasyPanel
Local dev   : Herd → http://wellcore-laravel.test
```

### Patrón Strangler Fig
La app PHP vanilla en `C:\Users\GODSF\Herd\wellcorefitness` y la Laravel comparten DB. Sólo trabajamos en Laravel. NO tocar la otra.

---

## 3. URLS CANDIDATAS A OPTIMIZAR

Todas usan `components/layouts/public.blade.php` como layout. Ordenadas por prioridad estimada (tráfico/conversión):

### Alta prioridad (likely high traffic)
1. `/planes` — `resources/views/public/planes.blade.php` (probablemente larga, parecida a la sección Plans del home)
2. `/metodo` — `resources/views/public/metodo.blade.php`
3. `/proceso` — `resources/views/public/proceso.blade.php`
4. `/coaches` — `resources/views/public/coaches.blade.php`
5. `/nosotros` — `resources/views/public/nosotros.blade.php`

### Media
6. `/faq` — `resources/views/public/faq.blade.php`
7. `/blog` — `resources/views/public/blog/index.blade.php` y `show.blade.php`
8. `/presencial` — `resources/views/public/presencial.blade.php`

### Baja (legales, poca lógica)
9. `/lanzamiento`, `/fit`
10. `/terminos`, `/privacidad`, `/politica-cookies`, `/reembolsos`

### Ruta a usar siempre que necesites listar todas las rutas:
```bash
grep -rn "Route::get.*public\." routes/web.php
```

---

## 4. REGLAS INVIOLABLES

### ❌ NUNCA tocar
```
app/Livewire/**                          # 25 componentes dashboard
app/Models/**                            # 61 Eloquent models
app/Auth/**                              # WellCoreGuard custom
app/Services/**                          # AIService, WompiService, etc.
app/Http/Middleware/EnsureAuthenticated.php
config/database.php
.env (excepto agregar variables NO críticas)
database/migrations/**                   # DB compartida con vanilla

resources/views/components/layouts/admin.blade.php
resources/views/components/layouts/coach.blade.php
resources/views/components/layouts/client.blade.php
resources/views/components/layouts/rise.blade.php
resources/views/components/layouts/shop.blade.php
resources/views/components/layouts/app.blade.php

resources/js/vue/**                      # SPA Vue (no es público)

C:\Users\GODSF\Herd\wellcorefitness      # App vanilla legacy

Tablas MySQL wellcore_fitness            # Sólo migraciones aditivas con luz verde explícita
```

### ✅ SÍ se puede modificar
```
resources/views/public/*.blade.php       # Cada vista pública
resources/views/components/layouts/public.blade.php  # Layout compartido (con cuidado, afecta TODAS)
resources/css/app.css                    # CSS global (cuidado: compartido con dashboards)
public/build/**                          # Compilados, se commitean
public/fonts/**                          # CSS y woff2 de fuentes
app/Http/Middleware/SetAssetCacheHeaders.php  # Headers cache
app/Http/Middleware/ContentSecurityPolicy.php  # CSP (con cuidado)
vite.config.js                           # Config build
```

### Reglas operativas (de la memoria del usuario)
1. **NO usar Rebuild Docker** en EasyPanel — preferir bash/consola
2. **NO correr `npm run build` en EasyPanel** — satura container y tumba host. Compilar **local**, commitear `public/build/`, y hacer `gitpull-load` via MCP
3. **Nunca deploy concurrente** — solo `git push` y `silvia-gitpull-load`
4. **NO pedir permiso entre pasos** — ejecutar tareas autónomas y continuas, salvo entre fases (donde sí se valida)
5. **Optimizar imágenes a <1500px** antes de leerlas con Read tool
6. **IA/Claude Code es info interna** — coaches y clientes NUNCA deben saber. Reemplazar por "equipo técnico" en docs externos

---

## 5. LO QUE YA ESTÁ OPTIMIZADO (NO REPETIR)

### En `components/layouts/public.blade.php` (afecta TODAS las URLs públicas)

#### Head — fuentes no-bloqueantes ✅
```html
<style>
  @font-face{font-family:'Oswald';font-style:normal;font-weight:600;font-display:swap;src:url('/fonts/oswald-600-latin.woff2') format('woff2');unicode-range:...;}
  @font-face{font-family:'Raleway';font-style:normal;font-weight:500;...}
  @font-face{font-family:'Raleway';font-style:normal;font-weight:600;...}
  @font-face{font-family:'Raleway';font-style:normal;font-weight:700;...}
</style>
<link rel="preload" as="font" type="font/woff2" href="/fonts/oswald-600-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-500-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-600-latin.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/fonts/raleway-700-latin.woff2" crossorigin>
<link rel="preload" href="/fonts/wellcore-fonts.css" as="style">
<link rel="stylesheet" href="/fonts/wellcore-fonts.css" media="print" id="wc-fonts-css">
<script nonce="@cspNonce">document.getElementById('wc-fonts-css').addEventListener('load',function(){this.media='all'},{once:true});</script>
<noscript><link rel="stylesheet" href="/fonts/wellcore-fonts.css"></noscript>
```

#### CSP nonces ✅
- Middleware `app/Http/Middleware/ContentSecurityPolicy.php` genera nonce por request
- Blade directive `@cspNonce` lo emite en views
- Livewire ya configurado vía `Livewire::useScriptTagAttributes()` en `AppServiceProvider`
- TODOS los inline `<script>` en components/layouts/public.blade.php llevan `nonce="@cspNonce"`

#### JS code-split ✅
`resources/js/app.js` carga conditionally:
- `animations.js` lazy via `requestIdleCallback`
- `push-subscription.js` solo en `/client|/coach|/admin|/rise`
- `coach-dashboard.js` solo en `/coach/*`
- Bundle público homepage = 1.1KB

#### Cache headers ✅
`SetAssetCacheHeaders` middleware: 1 año + immutable para `/build/assets/`, `/js/`, `/fonts/`, `/images/`, `/icons/`

#### CSS dead code purge (parcial) ✅
- 22 reglas `.hp-res-*` eliminadas de `app.css`
- Pero quedan otras secciones potencialmente sin uso (revisar por URL)

#### Fonts cyrillic/vietnamese ✅
14 archivos woff2 borrados — NO restaurar (LATAM no los necesita)

---

## 6. METODOLOGÍA QUE FUNCIONÓ (4 FASES)

Aplicar en este orden a cada URL nueva. **Validar entre fases**.

### FASE 1 — Quick wins layout-level (ya hecho globalmente)
- Si la URL tiene `<head>` propio (sospechosamente algunas), verificar que use el layout `public`
- Si tiene preloads/scripts inline propios, agregar `nonce="@cspNonce"`
- Para imágenes hero: `loading="eager" fetchpriority="high"` SOLO en la imagen LCP, todas las demás `loading="lazy" decoding="async"`

### FASE 2 — JS y reflow específicos de la URL
- Buscar `transition: all` en CSS específica de la URL → reemplazar por propiedades específicas (`transform`, `opacity`, `background-color`)
- Buscar inline `onclick=`, `onload=`, etc. en blade → refactorizar a `addEventListener` o usar Alpine `x-on:click`
- Si la URL carga JS específico vía `@push('scripts')`, validar que tenga `nonce="@cspNonce"`

### FASE 3 — DOM y CSS sin usar
**Patrón principal: `content-visibility: auto`**

Para CADA sección below-fold (todo lo que no se ve en el primer viewport):
1. Identificar la clase wrapper de la sección
2. Añadir clase `hp-cv-section` al wrapper

```html
<section class="existing-section-class hp-cv-section">
  <!-- contenido -->
</section>
```

La regla CSS `.hp-cv-section { content-visibility: auto; contain-intrinsic-size: 1px 800px; }` ya existe en `app.css`.

⚠️ **NO aplicar al hero, nav, ni footer visible en viewport inicial**.

**DOM cleanup:**
- Wrappers `<div>` redundantes (un div dentro de otro div sin propósito)
- Items decorativos (múltiples spans vacíos)
- Listas que se pueden colapsar a un sólo elemento

**CSS cleanup:**
- Buscar selectores `.hp-*` u otros únicos a la URL en `app.css`
- Verificar que se usen en algún Blade/JS con grep
- Si NO se usan → eliminar

### FASE 4 — A11y / Best Practices
**Contraste:**
- `text-wc-accent` (#DC2626) en dark mode → falla WCAG AA (4.12:1 sobre #09090B)
- Reemplazar con: `text-red-700 dark:text-red-400` (7+:1 ambos modos)
- O usar la clase `.hp-eyebrow` ya corregida

**Texto crossed-out / opacity:**
- `opacity-40` falla contraste → usar `opacity-70` mínimo, `opacity-80` para más seguro
- `opacity-60` con `line-through` falla → `opacity-90` mínimo

**Heading order:**
- Footer históricamente usaba `<h4>`, ahora son `<h3>` ✅
- Validar que cada vista no salte niveles (h1 → h2 → h3, no saltar a h4)

**Link text genérico:**
- Lighthouse blocklist ES incluye traducciones de "start" → "Empezar", "Comenzar", "Iniciar"
- Si una URL usa estos como CTA, cambiar a "Inscribirme", "Solicitar", "Ver más", etc.
- Para "Empezar" específicamente, ya cambiamos `nav.empezar` a "Inscribirme" globalmente

**Aria-labels:**
- Botones con SVG-only → `aria-hidden="true"` en el SVG
- Botones con texto + badge numérico → `aria-hidden="true"` en el badge
- Sticky/floating buttons → match aria-label con visible text

---

## 7. PATRONES DE CÓDIGO EXACTOS

### Patrón A — Sección below-fold con content-visibility
```html
<section class="hp-sec hp-cv-section">  <!-- añadir hp-cv-section -->
  <div class="hp-wrap">
    ...
  </div>
</section>
```

### Patrón B — Imagen no-hero
```html
<!-- ANTES -->
<img src="..." alt="...">

<!-- DESPUÉS -->
<img src="..." alt="..." loading="lazy" decoding="async" width="..." height="...">
```
**Importante:** width/height previenen CLS.

### Patrón C — Eyebrow con contraste correcto
```html
<!-- ANTES (falla en dark mode) -->
<p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">{{ __('section.eyebrow') }}</p>

<!-- DESPUÉS -->
<p class="text-xs font-semibold uppercase tracking-widest text-red-700 dark:text-red-400">{{ __('section.eyebrow') }}</p>

<!-- O usar clase global ya correcta -->
<p class="hp-eyebrow">{{ __('section.eyebrow') }}</p>
```

### Patrón D — Inline script con CSP nonce
```html
<!-- ANTES (CSP block) -->
<script>
  console.log('hello');
</script>

<!-- DESPUÉS -->
<script nonce="@cspNonce">
  console.log('hello');
</script>
```

### Patrón E — Reemplazar inline event handlers
```html
<!-- ANTES (CSP block sin unsafe-hashes) -->
<button onclick="doSomething()">Click</button>
<link rel="stylesheet" href="..." onload="this.media='all'">

<!-- DESPUÉS opción 1: Alpine -->
<button x-on:click="doSomething()">Click</button>

<!-- DESPUÉS opción 2: addEventListener -->
<button id="my-btn">Click</button>
<script nonce="@cspNonce">
  document.getElementById('my-btn').addEventListener('click', doSomething);
</script>
```

### Patrón F — JS code-split en app.js
```js
// Bundle por route (ya implementado)
if (location.pathname.startsWith('/coach')) {
    import('./coach-dashboard');
}
// Lazy load no-crítico
if ('requestIdleCallback' in window) {
    requestIdleCallback(() => import('./animations.js'), { timeout: 2000 });
}
```

### Patrón G — Cache header middleware
Ya está hecho en `SetAssetCacheHeaders.php`. No tocar a menos que añadas un nuevo prefijo.

### Patrón H — Touch target (Lighthouse a11y)
```html
<!-- Botones deben ser ≥48×48 px en mobile -->
<button class="min-h-[48px] min-w-[48px] p-3">...</button>
<a class="inline-flex h-12 w-12 items-center justify-center">...</a>
```

---

## 8. CÓMO DEPLOYAR

### Workflow completo (desde local con cambios)

```bash
# 1. Compilar assets si hay cambios CSS/JS/Vue
cd C:/Users/GODSF/Herd/wellcore-laravel
npm run build

# 2. Stagear archivos (selectivo, NO usar git add .)
git add resources/views/public/<archivo-modificado>.blade.php
git add resources/css/app.css                      # si tocó CSS
git add public/build/                              # SIEMPRE si compiló

# 3. Commit (estilo: tipo(scope): descripción)
git commit -m "$(cat <<'EOF'
perf(planes): aplica optimizaciones a /planes

- content-visibility en N secciones below-fold
- text-wc-accent → dark:text-red-400 en eyebrows
- DOM: -X nodos
EOF
)"

# 4. Push
git push origin main
```

### Deploy en EasyPanel via Chrome DevTools MCP

```
1. mcp__chrome-devtools__navigate_page → https://panel.wellcorefitness.com/projects/wellcorefitness/box/wellcorefitness/scripts
2. mcp__chrome-devtools__take_snapshot → obtener UID exacto del botón Run de "silvia-gitpull-load" (es el primero, suele ser uid=3_36 o similar)
3. mcp__chrome-devtools__click con ese UID
4. mcp__chrome-devtools__wait_for con texts ["Scripts ejecutados"]
5. Verificar tiempo del run: 5-15 segundos normal. >30s = problema.
```

⚠️ **NUNCA** clickear un selector SVG genérico — activa Rebuild Docker y tumba el sitio 5-10 minutos.

⚠️ **NUNCA** correr `npm-build` en EasyPanel.

### Si el deploy falla

```bash
# Rollback inmediato
git revert HEAD --no-edit
git push origin main
# Y deploy via silvia-gitpull-load otra vez
```

---

## 9. CÓMO VALIDAR

### A. Chrome DevTools MCP — Lighthouse audit (excluye Performance)
```
mcp__chrome-devtools__lighthouse_audit
  device: mobile
  mode: navigation
```
Te da Accessibility, Best Practices, SEO. Útil para validar a11y rápido.

### B. Chrome DevTools MCP — Performance trace (sin throttling)
```
mcp__chrome-devtools__navigate_page → URL
mcp__chrome-devtools__performance_start_trace
  reload: true
  autoStop: true
```
Te da LCP, render delay, RenderBlocking, DOM size, CLS. Buen indicador desktop pero NO refleja mobile throttled.

### C. PageSpeed Insights real (lo que cuenta)
```
mcp__chrome-devtools__navigate_page → 
  https://pagespeed.web.dev/analysis?url=https%3A%2F%2Fwww.wellcorefitness.com%2F<URL>&form_factor=mobile

mcp__chrome-devtools__wait_for → ["Cumulative Layout Shift"] (timeout 90s)

mcp__chrome-devtools__evaluate_script → leer .lh-gauge__percentage
```

⚠️ **PSI lab data es ruidoso ±10-15 puntos**. Correr 3 veces y tomar la mediana, o el rango estable.

### D. Local con Herd
```bash
# Asegurarse Herd corre
herd start
# Abrir http://wellcore-laravel.test/<url>
# F12 → Console: cero errores nuevos
# F12 → Network: cache headers correctos en assets
# F12 → Lighthouse panel: corrida local rápida
```

---

## 10. TRAMPAS Y ERRORES QUE EVITAR

### 10.1 — Borrar woff2 en uso
Las 14 woff2 cyrillic/vietnamese fueron borradas porque LATAM no las necesita. NO borrar más sin verificar `unicode-range` real de los caracteres usados en el sitio.

### 10.2 — `npm run build` en EasyPanel
**NUNCA**. Satura el container Docker y tumba el host 5-15 min. Compilar local + commit `public/build/` + `silvia-gitpull-load`.

### 10.3 — Rebuild Docker en EasyPanel
**NUNCA** clickear botones SVG genéricos en el panel. Verificar que el UID que vas a clickear corresponda al texto "Run" del script específico.

### 10.4 — `content-visibility: auto` en viewport inicial
Causa flash en hero/nav. Solo aplicar a secciones below-fold.

### 10.5 — `transition: all`
Fuerza reflow no-compositado. Usar siempre propiedades específicas. Las pocas que quedan en `app.css` (líneas 640, 732, 757, 784, 821) son del dashboard, NO de páginas públicas.

### 10.6 — Inline event handlers (`onclick=`, `onload=`) con CSP nonce activo
Bloqueados sin `'unsafe-hashes'`. Refactorizar a `addEventListener` con `<script nonce="@cspNonce">`.

### 10.7 — `loading="lazy"` en imagen LCP
Penaliza el LCP. La imagen del hero ALWAYS lleva `loading="eager" fetchpriority="high"`.

### 10.8 — Cambiar visible text a "Empezar"
Lighthouse blocklist ES considera "Empezar" / "Comenzar" / "Iniciar" como genéricos (mapeados de "start"). Usar "Inscribirme", "Solicitar", "Solicitar plan", etc.

### 10.9 — Service Worker concurrente
El SW se registra al `load` event. Si Lighthouse runs cold, ese registro contribuye al TBT (vimos un outlier de 850ms TBT en run 1 vs 20ms en run 3). Si quieres eliminar este outlier, mover a `setTimeout(() => navigator.serviceWorker.register('/sw.js'), 3000)` en `resources/js/app.js`.

### 10.10 — Tocar `app.css` sin verificar que afecta dashboards
`app.css` es global. Las clases `.hp-*` son SOLO del homepage/públicas, pero hay otras (`.al-*`, `.wday`, `.prog-*`, etc.) que son del dashboard cliente. NO eliminar sin grep contra TODOS los blades + todos los Vue files.

### 10.11 — Translations compartidas
`nav.empezar` se usa en nav, footer, sticky CTA, etc. Cambiar la traducción afecta TODAS las páginas públicas. Validar manualmente.

### 10.12 — Tailwind 4 dark mode
Tailwind 4 usa `class` strategy con `.dark` en `<html>`. Las utilidades `dark:bg-X` y `dark:text-Y` funcionan, PERO el sitio inicia en dark mode por defecto (línea 3 de public.blade.php). Validar contraste en AMBOS modos.

### 10.13 — `git add .`
NUNCA. Hay archivos de planning, debug, prompts, etc. en la raíz que NO deben commitearse. Stagear archivo por archivo con `git add resources/views/public/<file>` etc.

### 10.14 — PSI da Performance baja por ruido
Si la primera corrida da 48-50, NO entrar en pánico. Re-correr 2-3 veces. El score estable suele ser 70-77. Si las 3 corridas son <60 hay problema real.

---

## 11. PENDIENTE CRÍTICO: CRITICAL CSS

**Lo que falta para llegar a Performance ≥90 mobile.**

### Por qué es necesario
El CSS bundle (`app-XXXXX.css`) compilado por Tailwind 4 es **343KB sin minify, ~50KB gzipped**. En mobile throttled (Slow 4G + 4× CPU), tarda ~500ms en descargar y bloquea el render. Es el cuello de botella para FCP/LCP.

### Approach recomendado: `vite-plugin-critical` o `critters`

```bash
# Opción A: critters (más simple, integrado)
npm install --save-dev critters

# Editar vite.config.js
import { critters } from 'critters'
// ...
plugins: [
  laravel({...}),
  critters({
    preload: 'swap',
    inlineFonts: false,  // ya manejamos fonts manualmente
    pruneSource: true,
  })
]
```

```bash
# Opción B: vite-plugin-critical
npm install --save-dev vite-plugin-critical
```

### Approach manual (si no quieres tooling)

1. Abrir `/` en Chrome → F12 → More tools → Coverage
2. Reload page → ver % Unused Bytes en `app.css`
3. Identificar selectores cubiertos en above-fold
4. Crear `resources/css/critical.css` con esos selectores
5. En `public.blade.php` head, inline el critical.css:
```html
<style>{!! file_get_contents(public_path('build/critical.css')) !!}</style>
```
6. Diferir `app.css` con la misma técnica que las fonts:
```html
<link rel="preload" href="..." as="style">
<link rel="stylesheet" href="..." media="print" id="main-css">
<script nonce="@cspNonce">document.getElementById('main-css').addEventListener('load',function(){this.media='all'},{once:true});</script>
```

⚠️ **Riesgo:** FOUC (Flash Of Unstyled Content). Validar visualmente en cada URL antes de mergear.

### Impacto esperado
- FCP: 3.0s → ~1.5s (-1.5s)
- LCP: 4.7s → ~2.5s (-2.2s)
- Performance score: 77 → 88-92

### Tiempo estimado: 3-4 horas con tests

---

## 12. CREDENCIALES Y ACCESOS

Ver memoria `credentials_services.md` en `C:\Users\GODSF\.claude\projects\C--Users-GODSF-Herd-wellcore-laravel\memory\` para todos los detalles. Lo esencial:

- **Login admin WellCore:** `daniel.esparza` / `RISE2026Admin!SuperPower`
- **EasyPanel:** `info@wellcorefitness.com` / (ver memoria)
- **MySQL prod:** `wellcorefitness` / (ver memoria) en host `wellcorefitness_wellcorefitness-mysql`
- **Redis prod:** host `wellcorefitness_wellcorefitness-redis` puerto 6379
- **GitHub:** auth via Google `analyticfitness@gmail.com`

---

## 13. PLAN SUGERIDO POR URL

### Sub-fase A — Sondeo rápido por URL (15 min cada una)
Para cada URL en orden de prioridad:
1. PSI mobile baseline (1 corrida)
2. Lighthouse mobile (a11y/BP/SEO via DevTools MCP)
3. Performance trace desktop
4. Identificar los top 3 issues

### Sub-fase B — Aplicar patrones (30-60 min cada URL)
Para cada URL:
1. **content-visibility** en secciones below-fold (Patrón A)
2. **loading="lazy" decoding="async"** en images no-hero (Patrón B)
3. **Eyebrows / `text-wc-accent`** → dark mode-friendly (Patrón C)
4. **Inline scripts** → `nonce="@cspNonce"` (Patrón D)
5. **Inline event handlers** → addEventListener (Patrón E)
6. **DOM cleanup** — wrappers redundantes
7. **Heading order** — verificar h1 → h2 → h3 sin saltos
8. **Touch targets** — botones ≥48×48 mobile

### Sub-fase C — Validar y commit (15 min cada URL)
1. `npm run build` (si tocó CSS/JS)
2. PSI mobile post (3 corridas, mediana)
3. Smoke test funcional manual de la URL
4. Commit con mensaje claro
5. Deploy via EasyPanel
6. PSI verification post-deploy

### Sub-fase D — Critical CSS (después de optimizar todas las URLs individuales)
- Hacer Critical CSS extraction como tarea separada
- Afecta TODAS las URLs públicas
- Mayor impacto pero mayor riesgo

---

## OUTPUT ESPERADO DEL HANDOFF

Cuando termines con cada URL, agregar al final de `docs/HOMEPAGE_OPTIMIZATION_RESULTS.md` (o crear `docs/PUBLIC_URLS_OPTIMIZATION.md`):

```markdown
## URL: /<nombre>
- PSI antes: Performance X / A11y Y / BP Z / SEO W
- PSI después: Performance X' / A11y Y' / BP Z' / SEO W'
- Cambios principales: ...
- Commit: <SHA>
```

---

## CIERRE

El homepage quedó en estado excelente: a11y/BP/SEO en 100, Performance 74-77 (vs 69 baseline). El próximo paso lógico es replicar la metodología en el resto de URLs públicas. La parte más difícil ya está hecha (layout compartido, CSP, fonts, JS code-split, cache headers). Lo que queda es cosmético y específico por URL.

Si tienes dudas durante la sesión, **leer primero**:
- `docs/HOMEPAGE_OPTIMIZATION_RESULTS.md` — resultados detallados del homepage
- `CLAUDE.md` — reglas del proyecto Laravel y dispatch de agentes
- Las memorias en `C:\Users\GODSF\.claude\projects\C--Users-GODSF-Herd-wellcore-laravel\memory\`

**Buena suerte. El usuario prefiere ejecución autónoma — no pedir permiso entre pasos, solo entre fases mayores.**
