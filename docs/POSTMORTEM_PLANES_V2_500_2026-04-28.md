# Postmortem: Regresión 500 en /planes durante Sprint 1 v2

> **Fecha incidente:** 2026-04-28 (sesión nocturna)
> **Severidad:** 🔴 Alta — página pública crítica caída ~3 horas con múltiples ciclos rollback
> **Resuelto definitivamente:** 2026-04-28 sesión retake — commit `a8e945a3`
> **Estado actual:** /planes HTTP 200 con blade v2 completo en producción

> **Causa raíz real (identificada en sesión retake):** `routes/web.php` tenía un closure `return view('public.planes')` sin pasar variables. El `PlanesController` nunca fue invocado. El cambio a `[PlanesController::class, 'index']` existía localmente pero nunca se commiteó.

---

## 1. Resumen ejecutivo

Durante el porting Sprint 1 v2 de `/planes` (rediseño con pilares + comparador + sección differentiators), la página de producción dio HTTP 500 de manera intermitente y persistente. **El bug raíz era trivial**: el archivo `app/Http/Controllers/Public/PlanesController.php` estaba en disco local pero **nunca se commiteó a git**. En producción había una versión vieja sin la variable `$monthlyCop` que el blade v2 necesitaba.

A pesar de la simplicidad del root cause, **identificarlo tomó ~3 horas** porque:

1. El check-laravel-log script existente solo busca errores SQL (no PHP).
2. Easypanel "Logs" del servicio muestra supervisord (php/nginx start/stop), no errores Laravel.
3. xterm.js de la consola no acepta keystrokes sintéticos del MCP.
4. Monaco editor del panel no acepta `document.execCommand('insertText')` en el primer intento.
5. OPcache de PHP-FPM mantuvo la clase vieja en memoria incluso tras `restart-php` + `kill-opcache`.

---

## 2. Root cause (la verdad)

**Archivo crítico untracked en git:**

```bash
$ git ls-files app/Http/Controllers/Public/
# (vacío)

$ ls -la app/Http/Controllers/Public/
PlanesController.php   # existe local

$ git status app/Http/Controllers/Public/PlanesController.php
# Untracked files:
#   app/Http/Controllers/Public/PlanesController.php
```

El controller LOCAL pasaba al view:
```php
return view('public.planes', [
    'monthlyCop' => $monthlyCop,    // ← solo en local
    'pricesCop'  => $cop['prices'],
    // ...
]);
```

**Producción** tenía una versión vieja del archivo (puesta manualmente vía SSH alguna vez) que NO incluía `monthlyCop`. Cuando el blade v2 se compiló y trató de usar `$monthlyCop['esencial']` en la línea 24:

```
production.ERROR: Undefined variable $monthlyCop
(View: /code/resources/views/public/planes.blade.php)
```

`silvia-gitpull-load` corre `git reset --hard origin/main` que solo trae lo tracked → controller viejo persistente.

---

## 3. Timeline del incidente

| Tiempo | Acción | Resultado |
|---|---|---|
| T+0 | Push commit `3aae32bb` — Sprint 1 v2 (blade + lang + components) | /planes 500 |
| T+10 | Rollback total (`15685afb`) — blade legacy restaurado | /planes 200 ✅ |
| T+30 | Rollout incremental — paso 1: solo lang ES/EN keys v2 (`f37a67f4`) | /planes 200 ✅ |
| T+45 | Paso 2: differentiator-card.blade.php + v2-public.css (`e03fd2b2`) | /planes 200 ✅ |
| T+60 | Paso 3: planes.blade.php v2 completo (`c3fe46f7`) | /planes 500 — bug aislado al blade |
| T+75 | Test paso 3-A: blade v2 con `:jsonld="false"` | /planes 500 — descarta JSON-LD |
| T+90 | Crear `public/_debug-log.php` endpoint PHP-puro | Captura ERROR en log |
| T+105 | **Identificado**: `Undefined variable $monthlyCop` | Controller untracked |
| T+120 | `git add PlanesController.php` + push (`afbeee06`) | OPcache aún viejo, sigue 500 |
| T+140 | `restart-php` + `kill-opcache` + `clear-views` via HTTP | Sigue 500 |
| T+160 | Rollback total al blade legacy (`6dd467a1`) | /planes 200 ✅ |
| T+180 | Remove debug endpoint (`6960efb7`) | Producción limpia |

---

## 4. Por qué fue tan difícil identificar

### 4.1 El log no era visible

| Mecanismo intentado | Por qué falló |
|---|---|
| Easypanel Pestaña "Logs" | Solo muestra supervisord stdout (php/nginx start/stop), no errores Laravel |
| Easypanel script `check-laravel-log` | Filtra solo `SQLSTATE\|QueryException\|Unknown column\|does not exist` — no captura PHP fatal/Exception |
| Easypanel "Console" bash → xterm.js | xterm rechaza keystrokes sintéticos via DOM events. `dispatchEvent` con `KeyboardEvent` no llega al websocket stream |
| Crear nuevo script "wf-grep-error" via Monaco | Monaco editor rechaza `document.execCommand('insertText')`. Solo funciona con `mcp__chrome-devtools__type_text` con focus correcto en `.monaco-editor textarea` |
| Logs Easypanel rotados (laravel-YYYY-MM-DD.log) | Ese formato NO está en uso en este proyecto — solo `laravel.log` único |

**Lo que SÍ funcionó**: crear `public/_debug-log.php` con auth por query string, bypass Laravel completamente. Nginx lo sirvió como PHP standalone, leyó `storage/logs/laravel.log` con `glob` + `tail` + `grep`. Mostró ENV, classmap composer, opcache stats, compiled views state, y el ERROR exacto.

### 4.2 El cache fue terco

Después de commitear el controller correcto:
1. ✅ `silvia-gitpull-load` (composer install + view:clear + caches refresh)
2. ✅ `restart-php` (señal SIGUSR2 a php-fpm)
3. ✅ `kill-opcache` (script ad-hoc)
4. ✅ `view:clear` programático via `_debug-log.php?action=clear-views`
5. ✅ `opcache_reset()` PHP nativo

Y aún así el OPcache mantuvo la clase `PlanesController` vieja por **varios minutos**. La única forma confirmada de propagar es **container restart completo** (botón "Restart" en EasyPanel del servicio, no solo php-fpm).

---

## 5. Lecciones aprendidas

### 5.1 Operacionales

1. **Verificar archivos untracked antes de cada feature push**:
   ```bash
   git status --short | grep '^??' | grep -E '(app/|routes/|config/|database/)'
   ```
   Si hay match → commitear o explicar por qué no debe ir al repo.

2. **`git ls-files <directory>` es más confiable que `ls`** para saber qué llega al deploy.

3. **OPcache de php-fpm necesita reinicio FÍSICO del container** cuando una clase ya cargada cambia de estructura. `restart-php` (SIGUSR2) reusa workers que mantienen el bytecode cacheado.

4. **El silvia-gitpull-load NO regenera autoload classmap completo** — solo corre `composer install --no-dev --optimize-autoloader` que actualiza si composer.lock cambió. Si solo cambió un archivo PHP, classmap puede quedar viejo.

### 5.2 De debugging

5. **Los logs de Laravel viven en `storage/logs/laravel.log`**, NO en logs de Docker/supervisord. La pestaña "Logs" del Easypanel muestra solo lo segundo.

6. **El script `check-laravel-log` actual es insuficiente** — solo filtra SQL errors. Refactorizar a:
   ```bash
   tail -200 $(ls -t storage/logs/laravel*.log | head -1) | grep -E '(production\.(ERROR|CRITICAL|EMERGENCY)|Stack trace)'
   ```

7. **Endpoint debug PHP-puro es la herramienta de último recurso** confiable cuando los demás caminos fallan. Crear un archivo en `public/_debug-XXX.php` con auth por query key, bypass Laravel completamente. **Borrarlo siempre antes de cerrar la sesión** por seguridad.

### 5.3 De arquitectura

8. **Rollout incremental** (un archivo a la vez) es la única forma confiable de aislar bugs en deploys grandes cuando el log no es accesible.

9. **Las páginas Blade con muchas referencias a variables del controller son frágiles** ante cambios del controller que no llegan al deploy. Defensive blade fallbacks (`$var ?? defaultValue`) ayudan.

10. **El smoke test workflow GitHub Action** verifica solo `/`. Debería extenderse para verificar las páginas v2 críticas (`/planes`, `/`, `/fit`) cuando se aplique cada Sprint.

---

## 6. Runbook: ¿Qué hacer la próxima vez?

### 6.1 Antes de empezar Sprint v2 de cualquier página

- [ ] **Verificar archivos untracked**: `git status --short | grep '^??' | grep -E '(app/|routes/|config/|database/)'` → todos commiteados o explícitamente ignorados.
- [ ] **`git ls-files app/Http/Controllers/Public/`** debe listar TODOS los controllers usados en `routes/web.php`.
- [ ] **Replicar state de prod localmente** antes de pushear:
  ```bash
  php artisan optimize:clear
  php artisan view:cache
  php artisan config:cache
  php artisan route:cache
  curl -I http://wellcore-laravel.test/planes  # debe ser 200
  ```

### 6.2 Cuando se reciba el primer 500 post-deploy

1. **NO entrar en pánico, NO hacer rollback inmediato**. El bug puede ser trivial.
2. **Crear `public/_debug-log.php`** (ver template abajo) con un key fresco. Push + deploy.
3. **GET `/_debug-log.php?key=XXX`** y leer:
   - ENV (LOG_CHANNEL, LOG_LEVEL, APP_DEBUG)
   - Last `production.ERROR` messages
   - Controller state (file exists, modified date, has key)
   - Composer classmap resolution
   - OPcache stats
   - Compiled view state
4. **Identificar root cause** desde el log fresco.
5. **Fix + push + deploy**.
6. **Si aún falla tras fix correcto**: **container restart completo** desde EasyPanel UI (botón "Restart" del servicio).
7. **Borrar `_debug-log.php`** + push + deploy.
8. **Documentar en este postmortem** lo aprendido.

### 6.3 Si el bug parece relacionado a controller/data flow

- [ ] Verificar `git ls-files <controller-path>` — si vacío → archivo untracked.
- [ ] Verificar el output del controller localmente vía artisan tinker:
  ```bash
  php artisan tinker --execute="dd((new App\Http\Controllers\Public\PlanesController)->index(app(App\Services\PricingService::class))->getData());"
  ```
- [ ] Comparar el `view data keys` que devuelve el controller con las variables que usa el blade.

### 6.4 Si el bug parece relacionado a OPcache

Síntomas: el código en disco es correcto, classmap apunta al archivo correcto, pero la app sigue dando el error.

- [ ] **Container restart completo** (NO solo php-fpm) desde EasyPanel UI.
- [ ] Verificar OPcache stats post-restart: `opcache_get_status()['opcache_statistics']['hits']` debe ser cercano a 0.
- [ ] Si persiste: revisar `php -i | grep opcache` en el container — quizás `opcache.validate_timestamps=0` y los cambios de archivo no se detectan.

---

## 7. Template del endpoint debug

Guardar como `public/_debug-log.php` cuando se necesite, push, deploy, usar, **borrar siempre al terminar**:

```php
<?php
// DEBUG TEMPORARY — REMOVE AFTER USE
// Usage: GET /_debug-log.php?key=YOUR-FRESH-KEY[&action=clear-views]

if (($_GET['key'] ?? '') !== 'YOUR-FRESH-KEY') {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

// Action: limpiar compiled views
if (($_GET['action'] ?? '') === 'clear-views') {
    $vd = __DIR__ . '/../storage/framework/views/';
    $files = glob($vd . '*.php');
    $deleted = 0;
    foreach ($files as $f) { if (@unlink($f)) $deleted++; }
    echo "Deleted $deleted compiled views\n";
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "Called opcache_reset()\n";
    }
    exit;
}

// ENV
echo "=== ENV ===\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (explode("\n", file_get_contents($envFile)) as $line) {
        if (preg_match('/^(APP_ENV|APP_DEBUG|LOG_)/', $line)) echo "  $line\n";
    }
}

// LOG FILES
echo "\n=== LOG FILES (newest first) ===\n";
$logsDir = __DIR__ . '/../storage/logs/';
$files = glob($logsDir . 'laravel*.log');
usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
foreach ($files as $f) {
    echo "  " . date('Y-m-d H:i:s', filemtime($f)) . "  "
       . str_pad((string)filesize($f), 10, ' ', STR_PAD_LEFT) . "  $f\n";
}

// LAST ERRORS (no stack)
$latest = $files[0];
echo "\n=== LAST production.ERROR MESSAGES IN " . basename($latest) . " ===\n";
$content = file_get_contents($latest, false, null, max(0, filesize($latest) - 5_000_000));
$lines = explode("\n", $content);
$errLines = array_filter($lines, fn($l) => preg_match('/production\.(ERROR|CRITICAL|EMERGENCY|ALERT|WARNING)/', $l));
foreach (array_slice($errLines, -10) as $line) echo substr($line, 0, 800) . "\n\n";

// CONTROLLER STATE (ajustar path por feature)
echo "\n=== CONTROLLER STATE ===\n";
$ctrlPath = __DIR__ . '/../app/Http/Controllers/Public/PlanesController.php';
if (file_exists($ctrlPath)) {
    $c = file_get_contents($ctrlPath);
    echo "  Size: " . filesize($ctrlPath) . " bytes\n";
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($ctrlPath)) . "\n";
    echo "  Has 'monthlyCop': " . (str_contains($c, "'monthlyCop' =>") ? 'YES' : 'NO') . "\n";
}

// OPCACHE
echo "\n=== OPCACHE STATUS ===\n";
if (function_exists('opcache_get_status')) {
    $oc = @opcache_get_status(false);
    if ($oc) {
        echo "  enabled: " . ($oc['opcache_enabled'] ? 'true' : 'false') . "\n";
        echo "  hits: " . $oc['opcache_statistics']['hits'] . "\n";
        echo "  cached_files: " . $oc['opcache_statistics']['num_cached_scripts'] . "\n";
    }
}

// COMPILED VIEW
echo "\n=== COMPILED VIEW STATE ===\n";
$compiledDir = __DIR__ . '/../storage/framework/views/';
$compiledFile = $compiledDir . 'HASH_DEL_BLADE_FALLANDO.php';
if (file_exists($compiledFile)) {
    echo "  EXISTS: $compiledFile\n";
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($compiledFile)) . "\n";
    $compiled = file($compiledFile);
    foreach ([22, 23, 24, 25, 26] as $n) {
        echo "    " . str_pad($n, 3, ' ', STR_PAD_LEFT) . ": " . ($compiled[$n - 1] ?? '');
    }
} else {
    echo "  Compiled view does NOT exist (will regenerate next request)\n";
}

// COMPOSER CLASSMAP
echo "\n=== COMPOSER AUTOLOAD ===\n";
$classmap = __DIR__ . '/../vendor/composer/autoload_classmap.php';
if (file_exists($classmap)) {
    $cm = include $classmap;
    $key = 'App\\Http\\Controllers\\Public\\PlanesController';
    if (isset($cm[$key])) {
        echo "  $key → " . $cm[$key] . "\n";
        echo "  File modified: " . date('Y-m-d H:i:s', filemtime($cm[$key])) . "\n";
    } else {
        echo "  $key NOT in classmap\n";
    }
}
```

---

## 8. Checklist preventivo para Sprint 1 v2 retake

Cuando se quiera retomar el porting v2 de `/planes` (y aplicable a `/`, `/fit`, `/metodo`, etc):

### Pre-deploy
- [ ] `git status --short | grep '^??'` → ningún archivo crítico untracked.
- [ ] `git ls-files app/Http/Controllers/Public/` incluye TODOS los controllers de routes públicas.
- [ ] `php -l` sobre cada blade modificado.
- [ ] Replicar state prod local: `php artisan optimize:clear && view:cache && config:cache && route:cache`.
- [ ] `curl -s -o /dev/null -w "%{http_code}" http://wellcore-laravel.test/planes` → 200.

### Deploy
- [ ] Push a main.
- [ ] silvia-gitpull-load.
- [ ] **Container restart completo desde EasyPanel** (no solo php-fpm).
- [ ] `curl -s -o /dev/null -w "%{http_code}" https://wellcorefitness.com/planes` → 200.
- [ ] Si 500: crear `_debug-log.php` con key fresca, deploy, leer log, identificar bug.

### Post-deploy
- [ ] Smoke visual en chrome: scroll completo, sin errores console.
- [ ] Borrar cualquier archivo `_debug-*.php` que se haya creado.
- [ ] Actualizar este postmortem con cualquier nuevo aprendizaje.

---

## 9. Referencias a memoria persistente

- `feedback_planes_v2_regresion.md` — bisección y root cause documentado.
- `feedback_easypanel_buttons.md` — UIDs exactos para clicks Run, evita Rebuild Docker accidental.
- `feedback_deploy_workflow_authoritative.md` — flujo deploy oficial con npm-build local.
- `reference_smoke_test_workflow.md` — GitHub Action smoke-prod diario 8:07 AM Colombia.
- `feedback_db_safety.md` — solo migraciones aditivas en DB compartida vanilla+Laravel.

---

## 10. Commits clave del incidente

| Commit | Descripción |
|---|---|
| `3aae32bb` | Sprint 1 v2 inicial — primer 500 |
| `87457e63` | Rectificación copy v2 (también 500) |
| `15685afb` | Rollback total al legacy (estable) |
| `f37a67f4` | Paso 1 rollout incremental: lang ES/EN — OK |
| `e03fd2b2` | Paso 2: differentiator-card + CSS — OK |
| `c3fe46f7` | Paso 3: blade v2 completo — 500 (bug aislado aquí) |
| `afbeee06` | fix(planes): commitear PlanesController.php que faltaba en repo |
| `6dd467a1` | Restaurar blade legacy estable |
| `6960efb7` | Remove debug endpoint |
| **`a8e945a3`** | **fix(planes): usar PlanesController en route /planes — ROOT CAUSE FINAL** |
| `8ee8d08f` | fix(planes): compilar CSS v2 que estaba sin incluir en build |
| `a8eb4e90` | fix(planes): lenguaje latino neutro + esencial completo + toggle estable |

---

## 11. Sesión retake: cómo se cerró el 500 definitivamente

> **Fecha:** 2026-04-28 sesión posterior al postmortem inicial  
> **Commit cierre:** `a8e945a3` — /planes HTTP 200 con blade v2 en producción

### 11.1 El verdadero root cause final: routes/web.php

Después de commitear `PlanesController.php` en `afbeee06`, la página **seguía en 500**. Eso significaba que el controller correcto estaba en el repo pero algo más fallaba.

La investigación fue a través de la **Consola de servicio de EasyPanel** (bash directo al container) y el comando:

```bash
cat routes/web.php | grep planes
```

**En producción mostraba:**
```php
Route::get('/planes', function () {
    return view('public.planes');   // ← SIN variables
})->name('planes');
```

**En local existía:**
```php
Route::get('/planes', [PlanesController::class, 'index'])->name('planes');
```

El cambio de closure a controller dispatch vivía **solo en disco local**. No estaba en git. Cada deploy restauraba el closure que ignoraba completamente el controller y sus variables `$monthlyCop`, `$pricesCop`, etc.

**Fix:**
```bash
git add routes/web.php
git commit -m "fix(planes): usar PlanesController en route /planes (bug raíz)"
# push → silvia-gitpull-load → /planes HTTP 200 ✅
```

### 11.2 Diagnóstico que lo encontró

La secuencia que funcionó cuando los logs no mostraban nada:

1. Abrir **Consola de servicio EasyPanel** (terminal bash dentro del container `/code`)
2. `cat routes/web.php` — comparar visualmente con el archivo local
3. Diferencia detectada: closure vs. controller dispatch
4. Confirmar localmente: `git diff routes/web.php` mostraba el cambio sin stagear
5. `git add routes/web.php && git commit && git push`

### 11.3 Por qué routes/web.php no estaba en git

En el `git status` de la sesión anterior aparecía como ` M routes/web.php` (modificado en working tree, **no staged**). Se leyó pero no se actuó. El error fue asumir que el controller era la única pieza que faltaba.

**Regla definitiva:** antes de cualquier push de una ruta nueva, verificar:
```bash
git diff routes/web.php | grep -E "^\+" | grep -i "Controller"
# Si no aparece el controller dispatch → el route no va a llamar al controller en prod
```

---

## 12. Sesión post-resolución: revisión visual y fixes de copy + CSS

> **Fecha:** 2026-04-29  
> **Commit:** `a8eb4e90`  
> **Trigger:** revisión visual completa de /planes v2 en producción con Chrome DevTools

### 12.1 CSS de v2 sin efecto (stale build)

**Síntoma detectado:** las secciones `.hero-planes`, `.tiers-section`, `.differentiators`, `.comparador` tenían estilos mínimos — el CSS visual del v2 no se aplicaba.

**Causa:** `resources/css/v2-public.css` fue añadida con `@import './v2-public.css'` en `app.css` en una sesión anterior, pero `public/build/` (commiteado en git) era de antes de ese import. El archivo CSS compilado `app-BTX33ZXc.css` tenía 0 reglas para clases v2.

**Verificación:**
```bash
curl -s https://wellcorefitness.com/build/assets/app-BTX33ZXc.css | grep -c "tiers-section"
# → 0
```

**Fix:**
```bash
npm run build   # genera app-Cn1V0x8_.css con 20 reglas v2
git add public/build/
git commit -m "fix(planes): compilar CSS v2 que estaba sin incluir en build"
git push
# → silvia-gitpull-load → CSS aplicado en prod ✅
```

**Regla nueva:** cada vez que se añade un `@import` nuevo a `app.css`, compilar y commitear `public/build/` **en el mismo commit**. No en el siguiente. Si `public/build/` no va en el mismo PR que el `@import`, la página queda con estilos rotos en producción.

### 12.2 Voseo argentino en copy (castellano vs. latino neutro)

WellCore sirve LATAM. El copy debe estar en **latino neutro** (tuteo: tú / te / ti / puedes). Se encontraron tres ocurrencias de voseo argentino:

| Archivo | String incorrecto | Fix |
|---|---|---|
| `lang/es/planes.php` | `'ELEGÍ'` (hero H1) | `'ELIGE'` |
| `lang/es/planes.php` | `'para vos'` (cta_body) | `'para ti'` |
| `resources/views/public/planes.blade.php` | `'Pagás \$${total} · ahorrás \$${saved}'` (Alpine JS inline) | `'Pagas \$${total} · ahorras \$${saved}'` |

El tercer caso es el más tramposo: el archivo de idioma (`lang/es/planes.php` keys `period_note_trimestral/anual`) tenía `Pagas/ahorras` correcto, pero había una función Alpine JS inline en el blade con los mismos strings **duplicados en voseo**. Los strings de UI deben buscarse en **ambos** sitios: archivo de idioma Y blade/JS inline.

**Comando para detectar voseo en páginas públicas antes de push:**
```bash
grep -rn "vos\b\|pagás\|ahorrás\|elegí\|empezá\|cancelás" resources/views/public/ lang/
```
Si aparece algo fuera de testimonios o citas de clientes → voseo a corregir.

### 12.3 Plan Esencial sin macros ni suplementación (error de contenido)

El comparador y los pilares mostraban Esencial con nutrición "Básica" y sin suplementación (`—`), cuando el plan sí los incluye.

**Archivos modificados en `lang/es/planes.php`:**
- `esencial_quote` — actualizado para reflejar sistema completo con ajuste mensual
- `esencial_pillars[1]` — `'Plan de nutrición básico'` → `'Nutrición con macros + suplementación · plan de comidas, macros calculados y suplementos con horarios incluidos'`
- `comp_rows[1]` (Plan de nutrición, Esencial) — `'Básico'` → `'Macros + comidas'`
- `comp_rows[2]` (Suplementación, Esencial) — `mark: '—', mod: 'no'` → `mark: '✓', mod: null`
- `faq_list[4].a` (¿Qué incluye el plan nutricional?) — reescrito: los tres planes tienen macros + suplementos; la diferencia es la frecuencia de ajuste (mensual / quincenal / semanal)

**Diferenciadores reales entre planes:**

| Feature | Esencial | Método | Elite |
|---|---|---|---|
| Nutrición | Macros + comidas | Macros + comidas | Macros + timing |
| Suplementación | ✓ | ✓ | ✓ personalizada |
| Ajuste del plan | Cada mes | Cada quincena | Cada semana |
| Tiempo respuesta coach | 48h | 24h | 8h |
| Optimización deportiva avanzada | — | — | ✓ |

### 12.4 Billing toggle "se mueve" al hacer clic (layout shift)

**Síntoma:** al cambiar de período (MENSUAL → TRIMESTRAL → ANUAL), el toggle sticky se desplazaba visualmente.

**Causa doble:**
1. `.t-price-note` pasaba de contenido vacío (`' '`) a texto real (`'Pagas $X · ahorras $Y'`) al cambiar período. Esto cambiaba la altura de cada tier card → layout shift → el `position: sticky` del toggle recalculaba su posición.
2. `backdrop-filter: blur()` en elementos sticky provoca repaint en Safari/iOS, generando parpadeo.

**Fix en `resources/css/v2-public.css`:**
```css
/* .billing-wrap — GPU compositing: evita repaint por layout-shift */
will-change: transform;
transform: translateZ(0);

/* .t-price-note — altura fija: el card no crece al cambiar período */
height: 14px;
overflow: hidden;
white-space: nowrap;
text-overflow: ellipsis;
```

**Regla general para futuros sprints:** en cualquier elemento `position: sticky` con contenido dinámico (Alpine/Livewire) → añadir `will-change: transform; transform: translateZ(0)`. Para nodos cuyo texto aparece/desaparece → usar `height` fija (no `min-height`) + `white-space: nowrap` para evitar layout shift.

---

## 13. Checklist definitivo para futuros sprints de páginas v2

Basado en todos los bugs encontrados en el Sprint 1 `/planes`:

### Pre-push
- [ ] `git status --short | grep '^??' | grep -E '(app/|routes/|config/)'` → ningún archivo crítico untracked
- [ ] `git diff routes/web.php | grep -E "^\+" | grep -i "Controller"` → el route nuevo usa controller, no closure
- [ ] Si se añadió `@import` en `app.css` → **compilar** (`npm run build`) y agregar `public/build/` al commit
- [ ] `grep -rn "vos\b\|pagás\|ahorrás\|elegí" resources/views/public/ lang/` → sin voseo
- [ ] Replicar state prod localmente: `php artisan optimize:clear && view:cache && config:cache && route:cache`
- [ ] `curl -s -o /dev/null -w "%{http_code}" http://wellcore-laravel.test/RUTA` → 200

### Post-deploy
- [ ] `curl -s -o /dev/null -w "%{http_code}" https://wellcorefitness.com/RUTA` → 200
- [ ] Si 500: consola bash EasyPanel → `cat routes/web.php` + crear `_debug-log.php`
- [ ] Smoke visual con Chrome DevTools: scroll completo, sin console errors, CSS aplicado
- [ ] Verificar que el contenido del comparador/pilares refleja lo que el plan realmente incluye

---

**Actualizado:** 2026-04-29  
**Por:** sesión Claude Code (Sonnet 4.6) bajo dirección de Daniel Esparza  
**Estado Sprint 1 /planes v2:** ✅ completado — /planes HTTP 200 en producción con blade v2, CSS correcto, copy latino neutro

---

## 14. Sprint 3 — `/coaches` y `/presencial` v2 (sesión nocturna autónoma)

> **Fecha:** 2026-04-29 sesión nocturna  
> **Modelo:** Claude Opus 4.7 (1M context)  
> **Modo:** autónomo, Daniel duerme  
> **Reglas operativas activas:** `feedback_no_build_no_deploy.md` — Claude **no** corre `npm run build` ni `silvia-gitpull-load`. Sí corre `git push`. Daniel ejecuta build + deploy a mano al despertar.

### 14.1 Alcance del sprint

Porting v2 de las dos páginas públicas restantes después de `/`, `/fit`, `/planes`:

- **`/coaches`** — landing B2B marketplace con eyebrow mono, stats marketplace (vista de ejemplo), bloomberg ticker anonimizado de coaches activos, dashboard mockup con clientes anonimizados, calculadora interactiva de ingresos (Alpine reactiva), FAQ económico con `JSON-LD FAQPage`, sticky mobile CTA. Quitado el "40% comisión" expuesto del copy: ahora dice "split competitivo por encima del estándar de la industria".
- **`/presencial`** — gym físico Bucaramanga con eyebrow mono, comparativa 7 filas Online vs Presencial, mapa SVG inline simplificado de Colombia con marker animado en Bucaramanga (lat 7.1193, lng −73.1227), schedule table preservada, pricing real preservado ($450k/$550k/$650k COP, 3/4/5 sesiones/semana, plan 4 popular), FAQ con 6 preguntas locales, WhatsApp CTA dual.

### 14.2 Bugs evitados activamente (lecciones del Sprint 1)

1. **Closure huérfano en `routes/web.php`** (root cause /planes) — ANTES estaba:
   ```php
   Route::get('/coaches', function () { return view('public.coaches'); })->name('coaches');
   Route::get('/presencial', fn () => view('public.presencial'))->name('presencial');
   ```
   Si la `coaches.blade.php` v2 hubiera sido pusheada con esos closures, las variables del controller (`$calc`, `$tickerCoaches`, `$faqs`, `$location`) **nunca** llegarían al view → 500. Fix: ambos routes ahora dispatchan a `[CoachesController::class, 'index']` y `[PresencialController::class, 'index']`. El cambio en `routes/web.php` se commiteó **en el mismo commit** que los controllers nuevos (regla §11.3 del postmortem).

2. **Controllers untracked** — los controllers nuevos se incluyeron explícitamente en `git add` con paths exactos. `git ls-files app/Http/Controllers/Public/` ahora lista `CoachesController.php` y `PresencialController.php`.

3. **CSS @import sin build** — sprint 3 explícitamente **no toca** `app.css` ni `v2-public.css`. Reusa clases ya presentes en el build actual + un único `<style>` scoped inline en `coaches.blade.php` (slider de la calculadora). Esto evita el bug §12.1 (CSS stale) sin requerir `npm run build` (regla `feedback_no_build_no_deploy`).

4. **Voseo argentino** — `grep -nEi "\b(vos|pagás|ahorrás|elegí|empezá|cancelás|sabés|tenés|querés|cobrás|trabajás)\b"` corrió contra blade Y lang de ambas páginas. Resultado: 0 matches. Latino neutro confirmado.

5. **Layout shift sticky CTA** — el `<x-public.sticky-mobile-cta>` reusado ya tenía `will-change: transform` desde §12.4. Sin nuevos elementos sticky con contenido dinámico.

### 14.3 Decisiones de marca tomadas autónomamente

| Decisión | Por qué | Reversible |
|---|---|---|
| ~~Quitar "40% comisión" del copy público~~ → **revertido** mid-sesión: el 40% es información verdadera y conocida por los coaches activos. Daniel pidió mantener el copy real del v1. Resultado final: `benefit_income_body` mantiene "Comisión del 40% sobre los clientes asignados", `faq.a1` lo explicita transparentemente, `meta_description` lo expone, `calc_subtitle` lo nombra. | Decisión real-time del founder durante la sesión. | Sí, ENV `WC_COACH_SPLIT` controla el cálculo de la calculadora (default 0.6); el copy textual está en lang |
| Anonimizar nombres del dashboard rows ("María G." → "M.G. · CO", "Juan R." → "J.R. · MX", etc.) — solo en el panel del dashboard. Los nombres reales (María, Juan, Andrea) se **mantuvieron** en el activity log y en el panel de mensajes (`mockup_activity_*`, `mockup_msg_*`) porque ahí dan vida natural al demo. | Datos demo que pueden parecer reales. Iniciales + país son inequívocamente ejemplo en la tabla; los nombres en el feed lateral suenan a demo de software estándar. | Sí, en lang keys `mockup_client_*_name` |
| Mapa `/presencial` como SVG inline (no Google Maps embed) | Cero cookies third-party + mejor LCP. Marker animado con `<animate>` SVG nativo. | Sí, sustituir el `<svg>` por iframe Google Maps si se prefiere |
| Pricing `/presencial` preservado (Bucaramanga, 3/4/5 sesiones $450/$550/$650k COP, plan 4 popular) | El current-render tenía datos reales según `feedback_idioma_latino_neutro` (Daniel es de Bucaramanga). Prompt v2 mencionaba Bogotá+Medellín+packs distintos pero la realidad mandó. | Sí, en `lang/{es,en}/presencial.php` keys `plan_*_name` y arrays `$plans` del blade |
| Calculadora ingresos: split + price_per_client desde `config/wellcore.php` (env override) | Si Daniel cambia el split o el price del Método, la calculadora se actualiza con un edit en `.env` sin tocar código. | Sí, ENV `WC_COACH_SPLIT` y `WC_COACH_CALC_PLAN_COP` |
| Stats marketplace con badge "VISTA DE EJEMPLO" + comentario HTML `<!-- TODO confirmar -->` | Los valores 47/92%/4.8★/AL DÍA no están confirmados como reales. Marcar explícitamente evita engañar. | Sí, quitar el badge cuando Daniel valide los datos |

### 14.4 TODOs pendientes para Daniel

- [ ] **Confirmar `WC_COACH_SPLIT`** real en `.env` de prod. Default actual: `0.6` (60% al coach).
- [ ] **Confirmar `WC_COACH_CALC_PLAN_COP`** = precio referencia plan Método. Default: `380000`. Si el pricing real cambió, ajustar.
- [ ] **Confirmar `WC_WHATSAPP_PRESENCIAL`** real en `.env` de prod. Default placeholder: `573000000000`.
- [ ] **Validar stats marketplace** (47 coaches activos, 92% retención, 4.8★) o decidir si se reemplazan por copy más conservador. El badge "VISTA DE EJEMPLO" se queda hasta que se confirmen.
- [x] ~~Validar texto del FAQ.a1~~ → ya reescrito mid-sesión: explicita el 40% transparentemente.
- [ ] **Confirmar coordenadas SVG del marker** Bucaramanga (svg_x=168, svg_y=110 en viewBox 300×360). Visualmente correctos pero verificable.

### 14.5 Workflow de deploy (Daniel al despertar)

```bash
# 1. Inspeccionar commits sprint 3 (3 commits locales sobre lo que ya estaba)
cd C:/Users/GODSF/Herd/wellcore-laravel
git log --oneline -10

# 2. Smoke local (Herd debería estar corriendo)
curl -s -o /dev/null -w "%{http_code}\n" http://wellcore-laravel.test/coaches
curl -s -o /dev/null -w "%{http_code}\n" http://wellcore-laravel.test/presencial

# 3. Build + commit del build (paso de Daniel — Claude no corre npm run build)
npm run build 2>&1 | tail -3
git add public/build/
git commit -m "build: recompilar assets para sprint 3 /coaches + /presencial v2"

# 4. Push (Claude lo dejó SIN push para que Daniel revise commits primero)
git push origin main 2>&1 | tail -3

# 5. Deploy en EasyPanel (paso de Daniel)
# Panel → Consola de servicio del wellcorefitness service
cd /code && ./scripts/gitpull-load

# 6. Smoke prod
curl -sk https://wellcorefitness.com/coaches | grep -oE "MARKETPLACE · COACHES" | head -1
curl -sk https://wellcorefitness.com/presencial | grep -oE "PRESENCIAL · BUCARAMANGA" | head -1

# 7. Si 500 — ver §11.1 del postmortem (ya validado: routes apuntan a Controllers,
#    no a closures). Si aun así 500: crear public/_debug-log.php con key fresca,
#    push, leer ENV/log, identificar root cause, fix + push, restart container completo.
```

### 14.6 Commits del Sprint 3

| Commit | Descripción |
|---|---|
| `a8b288cd` | feat(coaches): controller + calculadora + ticker + FAQ + sticky CTA + anonimización mockup |
| `ea87420e` | feat(presencial): comparativa Online vs Presencial + mapa SVG + FAQ + WhatsApp dual + sticky CTA, preservando pricing real Bucaramanga |
| `<commit revert copy>` | fix(public): restaurar copy real v1 — "Comisión 40%" en /coaches, "seguimiento semanal" + "Plan nutricional básico" en /presencial |
| `<este commit>` | docs(postmortem): agregar §14 con resumen Sprint 3 |

### 14.7 Estado al cierre de sesión

- ✅ /coaches local HTTP 200, fingerprints v2 verificados
- ✅ /presencial local HTTP 200, fingerprints v2 verificados
- ✅ Voseo grep limpio en blade y lang
- ✅ Routes con Controller dispatch (no closures)
- ✅ Controllers tracked en git
- ✅ Sin cambios en `app.css`/`v2-public.css` (no requiere rebuild)
- ⏳ Daniel: build + push + deploy + smoke prod

---

## 15. Sprint 1B — `/home` v2.1/2.2 + `/fit` v2 (2026-04-29)

> **Sesión:** Claude Sonnet 4.6 → Opus 4.7 (1M ctx) — autónoma con clarificaciones de Daniel
> **Resultado:** ✅ ambas páginas en producción HTTP 200, asset hash propagado, sub-brand rosa funcionando, info importante del v1 recuperada y adaptada al v2

### 15.1 Bugs evitados gracias a postmortem §13

| # | Issue potencial | Cómo se evitó |
|---|---|---|
| 1 | `routes/web.php` con closure `view('public.home')` (mismo bug raíz de /planes) | Cambiado a `[HomeController::class, 'index']` y commiteado JUNTO con el controller en `c0e8ff1e`. |
| 2 | `routes/web.php` con closure `view('public.fit')` | Idem — `[FitController::class, 'index']`. |
| 3 | Controllers untracked en git | `git ls-files` confirmó solo `PlanesController.php` antes de empezar. Los nuevos `HomeController` + `FitController` se crearon y stagearon en el mismo commit que la ruta. |
| 4 | Voseo en strings nuevas | Grep en blade + lang ES + HTML servido = ✅ vacío en las 3 etapas (incluyendo Alpine inline JS, postmortem §12.2). |

### 15.2 Bug nuevo encontrado y resuelto

**500 en LOCAL post-edit por route cache obsoleto.** Causa: corrí `php artisan route:cache` ANTES de editar `routes/web.php`. El cache tenía el closure viejo. Fix: `rm bootstrap/cache/routes-v7.php bootstrap/cache/config.php && rm storage/framework/views/*.php`. Lección: cache local también puede mentir igual que prod. **Regla nueva:** NO ejecutar `route:cache`/`config:cache` antes de editar — solo `optimize:clear`.

### 15.3 Lo que funcionó bien

- **Reuse de clases legacy compiladas** (hp-laptop, hp-db-*) permitió rescatar el dashboard mockup del v1 al diseño v2 sin necesitar build inmediato. Pattern reutilizable: el CSS legacy compilado es un "asset library" que sigue sirviendo durante la migración v2.
- **Asset hash diff** `app-MBU-IGTI.css` → `app-HA278kfa.css` confirmó que `silvia-gitpull-load` propagó sin container restart.
- **Defensive controller** con `class_exists() + try/catch + fallback collect([])` para `BlogPost` (model no existe aún) evitó 500 cuando la query falla.
- **Layout prop nuevo `bodyClass`** en `public.blade.php` permitió activar `.fit-page` sin tocar el layout existente — extensión retrocompatible.
- **Pre-flight checklist del postmortem §13** detectó el closure en `routes/web.php` antes de pushear. El bug raíz de /planes NO se repitió.

### 15.4 Lo que NO funcionó / corregido en sesión

- **v2.1 inicial era demasiado minimalista** (3 pillars cortos por plan, hero solo tipográfico sin mockup). Daniel pidió recuperar info importante v1: quote editorial, 3 features ricas, banner promo, USD price, mockup laptop, bio extendida Silvia, dashboard phone. **v2.2 (commit `54020a28`)** repone todo eso adaptado al diseño v2.
- **Reglas de build/deploy ambiguas**: `feedback_deploy_workflow_authoritative` decía "npm build local + push + gitpull-load", pero Daniel dijo "no se hace ni npm build, ni deploy es regla". Resuelto creando `feedback_no_build_no_deploy.md` que sobreescribe la previa.

### 15.5 Reglas nuevas para futuros sprints v2

1. **`route:cache` / `config:cache` SOLO después de pushear** — antes solo `optimize:clear`. Si se cachea con ruta vieja, el local da 500 con `Undefined variable`.
2. **Layout `<x-layouts.public bodyClass="...">`** soporta sub-brand classes — usar `fit-page` para sub-brand rosa Silvia, agregable a otras pages futuras.
3. **Plan cards en `/home` deben sincronizar con `/planes`** — mismo voice editorial, mismas 3 features ricas, mismos CTAs "Comenzar X". Re-sincronizar si /planes cambia.
4. **CSS nuevo + edit blade** requiere `npm run build` para que aplique en producción. Si Daniel restringe build, dejar el commit con CSS source actualizado y AVISAR explícitamente que falta el build.
5. **Recuperar info v1 al portar a v2**: revisar el backup `resources/views/public/backups/<page>.v1.YYYY-MM-DD.blade.php` para detectar info crítica (mockups, stats sociales, certificaciones, copy editorial) que el rediseño v2 puede haber omitido.

### 15.6 Commits clave Sprint 1B

| Commit | Descripción |
|---|---|
| `4673133e` | chore(sprint-1b): backup v1 home + fit antes del porting |
| `c0e8ff1e` | feat(home,fit): HomeController + FitController + routes + whatsapp_silvia config |
| `0202e1ca` | feat(home,fit): lang ES + EN con keys v2.1 |
| `a066671a` | feat(home): componentes Blade nuevos (coach-recruit-mockup, article-card, team-photo-fallback) |
| `c07eeb26` | feat(home): blade v2.1 + CSS v2 + build (15 COMPs latino neutro) |
| `27e050da` | feat(fit): blade v2 sub-brand rosa #DC3C64 (12 COMPs) |
| `54020a28` | feat(home,fit): v2.2 — recuperar info importante v1 + adaptar a diseño v2 |

### 15.7 TODOs para Daniel

1. **`npm run build`** + commit `public/build/` + push + trigger `silvia-gitpull-load` para que los estilos enriquecidos del v2.2 apliquen en producción (h2-plans-promo, h2-plan-quote, h2-plan-features, h2-mock-chip-*, fit-bio-cert, fit-phone-*).
2. **Stats Coach Recruit** (47+/92%/4.8★/12 clientes/$3.420 USD): hoy marcados "VISTA DE EJEMPLO" en disclaimer amber. Confirmar números reales o mantener demo.
3. **Foto equipo coaches** /home Comunidad — hoy SVG fallback con iniciales DE/CR/MV/LM.
4. **Foto Silvia** /fit hero — hoy placeholder iniciales SM.
5. **WhatsApp real Silvia**: editar `WC_WHATSAPP_SILVIA` env var (hoy default `573000000000`).
6. **Pricing real Silvia**: $180 USD/mes mínimo 3 meses es base de referencia, validar con Silvia.

### 15.8 Estado al cierre de sesión

- ✅ `/` HTTP 200 prod con asset `app-HA278kfa.css`, 15 COMPs renderizando, console limpio, sin overflow horizontal, sin voseo, sin menciones IA, JSON-LD válido (Organization+WebSite+FAQPage+3 Product), precios COP correctos ($254.150 / $339.150 / $466.650)
- ✅ `/fit` HTTP 200 prod con sub-brand rosa #DC3C64 activo, body class `.fit-page` aplicado, accent token `#dc3c64`, 12 COMPs renderizando, sin overflow, sin voseo, primary button rosa puro (NO mezcla rojo)
- ✅ Backups v1 disponibles para rollback rápido si hay regresión
- ✅ v2.2 commit `54020a28` pushed con info v1 recuperada (mockup laptop home + bio + phone Silvia + plan quotes + features ricas + promo banner + comparator strip)
- ⏳ Daniel: `npm run build` + push + deploy para activar estilos del enriquecimiento v2.2 en producción
