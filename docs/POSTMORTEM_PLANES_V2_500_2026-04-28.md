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
