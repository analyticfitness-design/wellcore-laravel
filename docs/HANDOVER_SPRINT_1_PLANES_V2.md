# HANDOVER: Sprint 1 v2 `/planes` — terminar el deploy

> **Para:** próxima sesión de Claude Code (Sonnet 4.6 max effort recomendado)
> **De:** sesión Claude Opus 4.7 (1M context) — 2026-04-28
> **Estado producción al cierre:** `/planes` HTTP 200 con **blade legacy** (no v2). Bug raíz identificado, fix listo en repo, falta aplicar.
> **Tiempo estimado:** 15-25 minutos con container restart limpio.

---

## ⚡ TL;DR — qué hacer

1. **Container restart completo** del servicio `wellcorefitness` desde EasyPanel UI (no solo php-fpm).
2. **Cherry-pick del blade v2** desde commit `c3fe46f7`:
   ```bash
   cd C:\Users\GODSF\Herd\wellcore-laravel
   git checkout c3fe46f7 -- resources/views/public/planes.blade.php
   git add resources/views/public/planes.blade.php
   git commit -m "feat(planes): re-aplicar blade v2 tras container restart limpio"
   git push origin main
   ```
3. **Disparar `silvia-gitpull-load`** desde EasyPanel.
4. **Verificar** `https://wellcorefitness.com/planes` → debe ser HTTP 200 con `Cada quincena`, `Voice Logger`, `Compara los planes`.
5. Si funciona → ✅ Sprint 1 `/planes` terminado.
6. Si falla → leer postmortem (`docs/POSTMORTEM_PLANES_V2_500_2026-04-28.md`) y aplicar runbook §6.2.

---

## 1. Contexto del Sprint 1

### 1.1 Objetivo del Sprint 1
Portar 3 páginas públicas del rediseño v2: **`/planes`** + `/` (home) + `/fit`. Esta sesión avanzó **únicamente `/planes`**; las otras dos quedaron sin tocar.

### 1.2 Lo que está hecho del Sprint 1 (ya en producción)

✅ **Sprint 0 entero** (commit `780fd93f`): tokens MASTER, atmósfera global, override `.fit-page`, 6 componentes Blade reusables, layout editorial, `@alpinejs/intersect`, alias `[data-delay]`.

✅ **Sprint 1 paso 1** (`f37a67f4`): `lang/es/planes.php` + `lang/en/planes.php` con todas las keys v2 (`pillars`, `comp_rows`, `differentiators_*`, `faq_list` con 8 preguntas, `testimonios_list`).

✅ **Sprint 1 paso 2** (`e03fd2b2`): `<x-public.differentiator-card>` componente Blade reusable + CSS `.differentiators` styles en `resources/css/v2-public.css`.

✅ **Controller commiteado** (`afbeee06`): `app/Http/Controllers/Public/PlanesController.php` ahora SÍ está en git con `monthlyCop`, `pricesCop`, `totalsCop`, `savingsCop` para ES + USD.

✅ **Postmortem documentado** (`2b90da01`): `docs/POSTMORTEM_PLANES_V2_500_2026-04-28.md` con root cause y runbook.

### 1.3 Lo que FALTA aplicar

❌ **El blade `resources/views/public/planes.blade.php` v2** — actualmente es la versión LEGACY (sin pillars, sin comparador, sin sección differentiators). El código v2 existe en el commit `c3fe46f7` listo para cherry-pick.

❌ **Validación visual** end-to-end de la página v2 en producción.

❌ **Sprint 1 página 2 (`/`)** y **página 3 (`/fit`)** — no se han tocado en absoluto.

---

## 2. El bug raíz (entender antes de actuar)

**El controller `PlanesController.php` NO estaba en git** durante todo el Sprint 0 + Sprint 1. Existía local, funcionaba local, pero `silvia-gitpull-load` (que hace `git reset --hard origin/main`) traía a prod una versión vieja del controller que NO pasaba `$monthlyCop` al view.

El blade v2 usa `$monthlyCop` en el JSON-LD inicial:
```php
'description' => 'Planes de coaching fitness personalizado desde $'.number_format($monthlyCop['esencial'], 0, ',', '.').' COP/mes.',
```

Resultado: `production.ERROR: Undefined variable $monthlyCop`.

**Fix aplicado**: `git add app/Http/Controllers/Public/PlanesController.php` + commit `afbeee06`. El controller ahora SÍ está en git tracked.

**El obstáculo restante**: aún tras commitear el controller, OPcache de PHP-FPM mantuvo la clase vieja en memoria. `restart-php` (SIGUSR2) + `kill-opcache` + `view:clear` + `opcache_reset()` programático **no propagaron**. La hipótesis (no probada en sesión anterior por tiempo): **container restart completo** desde EasyPanel sí garantiza propagación.

---

## 3. Plan exacto para terminar `/planes` v2

### Paso A: container restart limpio

1. Ir a https://panel.wellcorefitness.com/projects/wellcorefitness/box/wellcorefitness/scripts (EasyPanel UI).
2. Login si hace falta: `info@wellcorefitness.com` / `fYCVgn4XZ7twq34`.
3. Click en el botón **"Restart"** del servicio (uid varía — está cerca de "Stop", "Logs", "Console" en la barra superior del servicio `wellcorefitness`).
4. Esperar ~10 segundos a que el container vuelva.
5. Verificar OPcache fresco haciendo GET a la home:
   ```bash
   curl -s -o /dev/null -w "%{http_code}" https://wellcorefitness.com/
   ```
   Debe ser 200.

### Paso B: cherry-pick del blade v2

```bash
cd C:\Users\GODSF\Herd\wellcore-laravel
git checkout c3fe46f7 -- resources/views/public/planes.blade.php
```

Verificar localmente que renderea OK con view:cache (replica de prod):

```bash
PHP="C:/Users/GODSF/.config/herd/bin/php.bat"
"$PHP" artisan optimize:clear
"$PHP" artisan view:cache
"$PHP" artisan config:cache
"$PHP" artisan route:cache
curl -s -o /tmp/p.html -w "HTTP %{http_code} · %{size_download}b\n" "http://wellcore-laravel.test/planes"
```

Esperado: `HTTP 200 · ~111000 bytes`. Si HTTP 500 local → bug en blade.

### Paso C: commit + push

```bash
git add resources/views/public/planes.blade.php
git commit -m "feat(planes): re-aplicar blade v2 tras container restart

Cherry-pick del commit c3fe46f7 que contiene el blade v2 completo:
- Hero brutal Oswald 5-line
- Billing toggle sticky
- Tier cards scroll-snap mobile / grid desktop
- Comparador 6x4 con copy rectificado
- Sección differentiators con voice-logger featured
- TestimoniosTicker
- FAQ accordion con JSON-LD
- CTAFinal precio dinamico
- Sticky mobile CTA con @alpinejs/intersect

Pre-requisito: PlanesController.php commiteado (afbeee06) +
container restart limpio para propagar OPcache.

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
git push origin main
```

### Paso D: deploy

En EasyPanel UI: click **Run** del script `silvia-gitpull-load`. Espera ~15 segundos.

### Paso E: validación

Abrí https://wellcorefitness.com/planes en chrome (incógnito mejor):

- ✅ HTTP 200, página renderea
- ✅ Texto visible: "Cada quincena", "Tu coach ajusta tu plan", "Compara los planes"
- ✅ Sección "EN TODOS LOS PLANES" con 8 cards (Voice Logger destacado con badge "Primero en LATAM")
- ✅ Tier cards: Esencial / Método (badge "Más elegido") / Elite con mini-ring SVG
- ✅ FAQ accordion con 8 preguntas
- ✅ Console clean (0 errors, 0 warnings)
- ✅ Atmósfera: radial orb rojo top-left + grain noise

Curl rápido:
```bash
curl -sk "https://wellcorefitness.com/planes" | grep -oE "Cada quincena|Voice Logger|Plan de nutrici" | sort -u
```

Esperado output:
```
Cada quincena
Plan de nutrici
Voice Logger
```

---

## 4. ¿Y si falla? Runbook

### Si HTTP 500 post-deploy

1. **NO entres en pánico**. Aplica el runbook del postmortem:
2. Crear `public/_debug-log.php` con template del postmortem §7 (auth con key fresca).
3. Push + silvia-gitpull-load.
4. GET `/_debug-log.php?key=TU-KEY` y leer:
   - Last `production.ERROR` messages
   - Controller state (file modified, has 'monthlyCop')
   - Composer classmap
   - OPcache stats
   - Compiled view state
5. Si error es "Undefined variable XXX" → controller no pasa esa variable, agregar al controller o al blade fallback defensivo.
6. Si error es "Class not found" → `git ls-files <path>` para verificar tracking; commitear si untracked.
7. Después del fix → **container restart completo** (no solo php-fpm).
8. Borrar `_debug-log.php` + push + deploy.

### Si la sintaxis del blade es la causa

```bash
# Ver el error específico del Blade en local
PHP="C:/Users/GODSF/.config/herd/bin/php.bat"
"$PHP" artisan view:clear
"$PHP" artisan view:cache 2>&1 | tail -20
```

Si view:cache falla local con error de sintaxis → ahí está el bug. Arreglar y repushear.

### Si solo es OPcache stale

1. EasyPanel → servicio `wellcorefitness` → botón **Restart** (no Stop).
2. Esperar 10s.
3. Re-verificar /planes.

---

## 5. Después de `/planes` OK — siguiente

### Sprint 1 página 2: `/` (home)

Ver `IMPLEMENTATION-PLAN-MASTER.md` §"Sprint 1" en `E:\WELLCORE FITNESS PLATAFORMA\V2 WELLCOREFITNESS STANDART\PAGINAS PUBLICAS\PROMPTS MASTER DEL PLAN DE IMPLEEMNTACIÓN\`.

**Antes de empezar:**
- [ ] Esperar Claude Design submission de Daniel para el rediseño del home (mencionado en SPRINT-0-PROMPT-CLAUDE-CODE).
- [ ] Validar pricing real con Daniel (3 planes × 3 períodos × 2 monedas).

### Sprint 1 página 3: `/fit`

Sub-brand `Silvia` con override rosa `#DC3C64`. Ver paquete `11-fit/` en `E:\WELLCORE FITNESS PLATAFORMA\V2 WELLCOREFITNESS STANDART\PAGINAS PUBLICAS\11-fit\` cuando exista.

**Antes de empezar:**
- [ ] Foto Silvia autorizada para `/fit`.
- [ ] Tono: primera persona Silvia, NO girl-boss, NO motivacional.

---

## 6. Lecciones críticas a internalizar (NO ignorar)

### 6.1 ANTES de cualquier push de feature

```bash
git status --short | grep '^??' | grep -E '(app/|routes/|config/|database/)'
```

Si devuelve algún archivo → **commitearlo o explicar por qué NO debe ir**. **Nunca asumir que un archivo local llega a producción si no está tracked**.

### 6.2 Replicar state de prod localmente antes del push

```bash
PHP="C:/Users/GODSF/.config/herd/bin/php.bat"
"$PHP" artisan optimize:clear
"$PHP" artisan view:cache && "$PHP" artisan config:cache && "$PHP" artisan route:cache
curl -s -o /dev/null -w "%{http_code}" "http://wellcore-laravel.test/RUTA"
```

Si local con cache no devuelve 200 → no push. El bug se manifiesta en prod sí o sí.

### 6.3 Para deploys que cambian estructura de clases (controllers nuevos, métodos nuevos)

Después del `silvia-gitpull-load`, **siempre hacer container restart**. NO confiar en `restart-php` solo. PHP-FPM puede mantener bytecode cacheado en workers que no se recargan con SIGUSR2.

### 6.4 Si la pestaña "Logs" del EasyPanel no muestra Laravel errors

Es porque muestra solo supervisord (php/nginx start/stop). Para Laravel errors:
- Crear `public/_debug-log.php` con template del postmortem §7.
- O extender el script `check-laravel-log` para incluir `production.ERROR` no solo SQL.

### 6.5 Idioma del copy

Siempre **latino neutro** (tú/puedes/conoces). NO castellano peninsular (vosotros/guay), NO voseo argentino (vos/podés/querés). Daniel es de Bucaramanga, Colombia. Memoria `feedback_idioma_latino_neutro.md` lo detalla.

### 6.6 Fuentes en código

`--font-display: 'Oswald'`, `--font-sans: 'Raleway'`. NO Bebas/Inter primarios — esos son fallback. Memoria `feedback_fonts_real.md`.

---

## 7. Documentos a leer ANTES de arrancar

### Obligatorios (5 minutos lectura cada uno)

1. **`docs/POSTMORTEM_PLANES_V2_500_2026-04-28.md`** — todo el contexto del bug + runbook.
2. **`CLAUDE.md`** raíz — reglas del proyecto, stack, agentes especialistas.
3. **`MEMORY.md`** + memorias relevantes (especialmente `feedback_planes_v2_regresion`, `feedback_easypanel_buttons`, `feedback_deploy_workflow_authoritative`).

### Si retomas TODO Sprint 1 (no solo `/planes`)

4. **`E:\WELLCORE FITNESS PLATAFORMA\V2 WELLCOREFITNESS STANDART\PAGINAS PUBLICAS\PROMPTS MASTER DEL PLAN DE IMPLEEMNTACIÓN\IMPLEMENTATION-PLAN-MASTER.md`** — orden de las 11 páginas + dependencias + riesgos.
5. **`MASTER-DESIGN-SYSTEM-V2.md`** — paleta, fonts, componentes premium.
6. **`RULES-DEPLOY.md`** — workflow autoritativo.
7. **`RULES-DESIGN.md`** + **`RULES-RESPONSIVE.md`** — reglas duras.

---

## 8. Credenciales (si necesarias)

Las credenciales viven en memoria `credentials_services.md`. Acceso típico:

- **EasyPanel UI:** `info@wellcorefitness.com` / `fYCVgn4XZ7twq34`
- **WellCore superadmin:** `Daniel.esparza` / `KingLord6962`
- **GitHub:** login con Gmail `analyticfitness@gmail.com` / `Wellcore6962`

---

## 9. Estado del repo al cierre

```
HEAD producción: 2b90da01 docs: postmortem regresion 500 /planes Sprint 1 v2

Últimos commits relevantes:
2b90da01 docs: postmortem regresion 500 /planes Sprint 1 v2  ← este handover
6960efb7 chore: remove temp debug endpoint /_debug-log.php
6dd467a1 fix(planes): restaurar blade legacy estable urgente   ← /planes prod actual usa este
afbeee06 fix(planes): commitear PlanesController.php que faltaba en repo
e03fd2b2 feat(planes): paso 2 rollout - componente differentiator-card + CSS
f37a67f4 feat(planes): paso 1 rollout incremental - lang files con keys v2
c3fe46f7 feat(planes): paso 3 final rollout - planes.blade.php v2 completo  ← cherry-pick este
```

**Branch `debug/planes-v2-500`**: eliminado.
**Archivos untracked relevantes**: revisar con `git status --short | grep '^??'`.

---

## 10. Cuando termines, ACTUALIZAR

- [ ] Postmortem `docs/POSTMORTEM_PLANES_V2_500_2026-04-28.md` con el resultado del retake (¿funcionó el container restart? ¿qué falló?).
- [ ] Memoria `feedback_planes_v2_regresion.md` con confirmación de fix definitivo o nuevos hallazgos.
- [ ] Smoke test workflow GitHub Action (`.github/workflows/smoke-prod.yml`) — extender para verificar `/planes` específicamente, no solo `/`.
- [ ] Si funciona, **borrar este handover** o moverlo a `docs/_archive/` para no confundir sesiones futuras con instrucciones obsoletas.

---

**Generado por:** Claude Opus 4.7 sesión 2026-04-28
**Para:** próxima sesión Claude Sonnet 4.6 max effort
**Permiso:** todas las acciones autorizadas por Daniel Esparza (CEO WellCore Fitness) en sesión previa. Verificar en CLAUDE.md raíz que las reglas siguen vigentes.

🟢 **Buena suerte. El bug ya está identificado, el fix ya está commiteado, falta solo el container restart limpio.**
