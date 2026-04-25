# Plan de implementación: Fix Top Nav Bar Cliente + Auditoría Defensiva

> **Para:** Claude Code Sonnet 4.6 — modo max effort
> **Repositorio:** `C:\Users\GODSF\Herd\wellcore-laravel` (Laravel 13 + Vue 3 SPA + Livewire legacy coexistentes vía Strangler Fig)
> **Branch sugerida:** `fix/client-topnav-overflow`
> **Compatibilidad:** dual layout — el cliente puede entrar por SPA Vue (`ClientLayout.vue`) o por Livewire legacy (`resources/views/layouts/client.blade.php`). Ambos tienen el mismo bug. Hay que arreglar los dos.

---

## 1. Contexto del bug reportado

El usuario reportó que en mobile, cuando el badge del topbar muestra texto largo del estilo:

> `Semana 1 · Fase: Adaptación neuromuscular`

…el badge **rompe el layout del top nav bar**: empuja la campana de notificaciones, el toggle de tema y el avatar fuera del viewport. En la captura se ve cómo el ícono hamburger se corta por la izquierda y el toggle de tema se corta por la derecha.

Capturas de referencia: `C:\Users\GODSF\Downloads\WhatsApp Image 2026-04-25 at 2.07.41 AM.jpeg` y `…2.07.38 AM.jpeg`.

---

## 2. Causa raíz (ya diagnosticada — no la re-investigues, solo verifica)

### 2.1 Bug primario — `white-space: nowrap` sin contención

Archivo: `resources/css/app.css:603-618`

```css
.tb-phase {
  font-size: 14px; font-weight: 600;
  color: var(--color-wc-text);
  background: rgba(220,38,38,.14);
  border: 1px solid rgba(220,38,38,.22);
  padding: 6px 16px;
  border-radius: 9999px;
  display: flex; align-items: center; gap: 7px;
  white-space: nowrap;          /* ← culpable: jamás se trunca       */
  /* falta:  min-width: 0; overflow: hidden; text-overflow: ellipsis; max-width: … */
}
.tb-phase::before { … }
```

`white-space: nowrap` impide el wrap, y al no tener `min-width: 0` + `overflow: hidden` + `max-width`, el badge crece según el contenido y empuja a sus hermanos en el flex container del header.

### 2.2 Bug secundario — el badge se ve en mobile aunque tenga `hidden sm:flex`

Archivos:
- `resources/js/vue/layouts/ClientLayout.vue:327`
- `resources/views/layouts/client.blade.php:348`

```html
<div class="tb-phase hidden sm:flex">…</div>
```

Tailwind `hidden` = `display: none`. Pero `.tb-phase` declara `display: flex` con la **misma especificidad** y, según el orden de capas en el CSS compilado, puede ganarle a `hidden`. Eso explica por qué el badge aparece en pantallas <640px en las capturas, contradiciendo la intención del `hidden`.

**Soluciones posibles** (elige la del punto 3):
- Quitar `display: flex` del `.tb-phase` y dejar que Tailwind controle el display vía `hidden sm:flex`. Riesgo: rompe el layout interno del badge (gap entre ::before y texto).
- Usar `inline-flex` y mover la regla a `@layer components` para asegurar que `hidden` (utility layer, posterior) gane.
- Reescribir el componente con clases utility puras (recomendado a largo plazo).

### 2.3 Bug terciario — lógica del badge en Livewire es incorrecta

`resources/views/layouts/client.blade.php:336-348`:

```php
$assignedStart = $client->plan->created_at ?? now();
$weekNum = max(1, (int) ceil(now()->diffInDays($assignedStart) / 7));
$weekNum = min($weekNum, 12);
$phaseMap = [1 => 'Adaptación', …, 10 => 'Peak', 11 => 'Peak', 12 => 'Peak'];
```

Problemas:
- `$client->plan` es un `enum` (PHP enum), no un modelo Eloquent. `->created_at` no existe en un enum → siempre cae al fallback `now()` → `$weekNum` = 1 siempre. Verificar con `dd($client->plan)`.
- El cap a 12 semanas y el `phaseMap` hardcoded son **inconsistentes con el plan real del cliente**. Cada plan tiene sus propias semanas y fases en la base de datos (`plan_weeks`, `plan_phases` o equivalentes — confirmar nombres).
- La lógica del Vue (`ClientLayout.vue:68-83`) lee de `/api/v/client/dashboard` que devuelve `currentWeek` y `phaseName`. Esa es la fuente de verdad. El Livewire debería consumir el mismo endpoint o un helper backend equivalente.

### 2.4 Bug cuaternario — race conditions en el mounted del ClientLayout.vue

`ClientLayout.vue:41-84`: hace **3 llamadas serializadas** (`account-status`, `my-coach`, `dashboard`). Debería paralelizar con `Promise.allSettled` para reducir TTI mobile. Además ninguna usa `AbortController`, así que si el usuario navega rápido entre rutas hay *zombie requests*.

---

## 3. Cambios requeridos (orden estricto)

### Paso A — Fix CSS del badge (raíz del bug visual)

**Archivo:** `resources/css/app.css:603-618`

Reemplaza el bloque entero por:

```css
.tb-phase {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-wc-text);
  background: rgba(220,38,38,.14);
  border: 1px solid rgba(220,38,38,.22);
  padding: 6px 16px;
  border-radius: 9999px;
  display: inline-flex;          /* inline-flex permite que `hidden` gane */
  align-items: center;
  gap: 7px;

  /* contención responsive */
  min-width: 0;                  /* permite encogerse dentro del flex padre */
  max-width: clamp(140px, 38vw, 360px); /* topa crecimiento; ajusta si rompe en sm-md */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tb-phase::before {
  content: "";
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--color-wc-accent);
  flex-shrink: 0;                /* el dot nunca se encoge */
}
```

**Nota técnica:** `inline-flex` es importante. Cuando el padre aplica `hidden` (`display: none`), gana siempre. Cuando aplica `sm:flex`, sobrescribe `inline-flex` con `flex`, que es lo que queremos en >=640px. En <640px (`hidden`), el badge desaparece como debe.

### Paso B — Hacer truncable el contenedor padre del badge en ambos layouts

El padre del badge debe permitir compresión. Ahora mismo no tiene `min-w-0`, y eso hace que el ellipsis nunca se active.

**B.1 — `resources/js/vue/layouts/ClientLayout.vue:316-330`**

```vue
<header class="sticky z-30 flex h-16 items-center justify-between border-b border-wc-border bg-wc-bg/80 px-4 backdrop-blur-xl sm:px-6 gap-3"
        :class="isImpersonating ? 'top-10' : 'top-0'">
  <!-- Left: hamburger + plan phase -->
  <div class="flex min-w-0 flex-1 items-center gap-3">
    <button … class="shrink-0 …">…</button>
    <div v-if="planPhaseText" class="tb-phase hidden min-w-0 sm:flex">
      <span class="truncate">{{ planPhaseText }}</span>
    </div>
  </div>

  <!-- Right: dark mode, user info -->
  <div class="flex shrink-0 items-center gap-3">
    …
  </div>
</header>
```

Cambios clave:
- `<header>` recibe `gap-3` para separar las dos secciones cuando se compriman.
- Sección izquierda recibe `min-w-0 flex-1` → puede encogerse y absorbe espacio sobrante.
- Sección derecha recibe `shrink-0` → nunca se comprime (los íconos siempre visibles).
- `button` (hamburger) recibe `shrink-0` por la misma razón.
- El badge recibe `min-w-0` (el utility duplica la regla del CSS pero es defensivo) y wrap el texto en `<span class="truncate">` para que el ellipsis aplique al texto sin afectar al `::before` dot.

**B.2 — `resources/views/layouts/client.blade.php:323-350`**

Aplicar exactamente el mismo patrón al `<header>` Livewire. Estructura final:

```blade
<header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-wc-border bg-wc-bg/80 backdrop-blur-xl px-4 sm:px-6">
  <div class="flex min-w-0 flex-1 items-center gap-3">
    <button x-on:click="sidebarOpen = !sidebarOpen" class="btn-press shrink-0 flex h-9 w-9 …">…</button>

    @if($client && $client->plan)
      {{-- ⚠ FIX: usar fuente de verdad del backend en vez de cálculo en blade --}}
      <div class="tb-phase hidden min-w-0 sm:flex">
        <span class="truncate">{{ $planPhaseText ?? 'Plan activo' }}</span>
      </div>
    @endif
  </div>

  <div class="flex shrink-0 items-center gap-3">…</div>
</header>
```

### Paso C — Centralizar la lógica del texto del badge en backend

El cálculo de `weekNum + phaseName` no puede vivir en el blade. Dos opciones:

**Opción C.1 (rápida y suficiente):** Crear un helper `ClientPlanPhaseService` que devuelva un string ya formateado.

```php
// app/Services/Client/ClientPlanPhaseService.php
namespace App\Services\Client;

use App\Models\Client;

final class ClientPlanPhaseService
{
    public function topbarLabel(Client $client): ?string
    {
        // 1. Si no tiene plan asignado → null
        $assignedPlan = $client->assignedPlan ?? null;
        if (!$assignedPlan) return null;

        // 2. Calcular semana actual respetando la fecha de inicio real del plan asignado
        $start = $assignedPlan->started_at ?? $assignedPlan->created_at ?? null;
        if (!$start) return $client->plan?->label();

        $weekNum = max(1, (int) ceil(now()->diffInDays($start) / 7));

        // 3. Resolver fase real desde plan_phases (o el modelo equivalente)
        $phase = $assignedPlan->phases()
            ->where('start_week', '<=', $weekNum)
            ->where('end_week', '>=', $weekNum)
            ->value('name');

        return $phase
            ? "Semana {$weekNum} · Fase: {$phase}"
            : "Semana {$weekNum}";
    }
}
```

> **TODO para Sonnet:** confirma los nombres reales de los modelos/relaciones (`assignedPlan`, `phases`, columnas `started_at`/`start_week`/`end_week`). Si no existen, mira cómo lo hace el endpoint `/api/v/client/dashboard` y replica esa lógica. NO inventes columnas. NO crees migraciones nuevas.

Inyecta el servicio y pasa el string al layout:

```php
// app/Http/View/Composers/ClientLayoutComposer.php
namespace App\Http\View\Composers;

use App\Services\Client\ClientPlanPhaseService;
use Illuminate\View\View;

final class ClientLayoutComposer
{
    public function __construct(private ClientPlanPhaseService $phaseService) {}

    public function compose(View $view): void
    {
        $client = auth('wellcore')->user();
        $view->with('planPhaseText', $client ? $this->phaseService->topbarLabel($client) : null);
    }
}
```

Registra el composer en `App\Providers\AppServiceProvider::boot()`:

```php
View::composer('layouts.client', ClientLayoutComposer::class);
View::composer('components.layouts.client', ClientLayoutComposer::class); // si aplica
```

**Opción C.2 (si el usuario prefiere no añadir clases):** mover el cálculo a un Blade Component y exponer `$planPhaseText` desde ahí. Cualquiera funciona; C.1 es más limpia.

### Paso D — Endurecer el fetch del badge en Vue

`ClientLayout.vue:41-84`. Reemplaza el bloque del `onMounted` por:

```js
onMounted(async () => {
    const ac = new AbortController();
    onUnmounted(() => ac.abort());

    // Paralelo, no serial — gana ~200ms en mobile
    const [statusRes, coachRes, dashRes] = await Promise.allSettled([
        api.get('/api/v/client/account-status', { signal: ac.signal }),
        api.get('/api/v/client/my-coach',       { signal: ac.signal }),
        api.get('/api/v/client/dashboard',      { signal: ac.signal }),
    ]);

    // account-status
    if (statusRes.status === 'rejected') {
        const err = statusRes.reason;
        if (err?.response?.status === 403 && err.response?.data?.inactive) {
            accountInactive.value = true;
            accountStatusValue.value = err.response.data.status || 'inactivo';
        }
    }
    accountCheckDone.value = true;

    // my-coach
    if (coachRes.status === 'fulfilled' && coachRes.value?.status === 200 && coachRes.value.data) {
        coachBrand.value = coachRes.value.data;
    }

    // dashboard → planPhaseText
    if (dashRes.status === 'fulfilled' && dashRes.value?.status === 200) {
        const d = dashRes.value.data;
        if (d?.currentWeek) {
            const phase = d.phaseName ? ` · Fase: ${d.phaseName}` : '';
            planPhaseText.value = `Semana ${d.currentWeek}${phase}`;
        } else if (d?.planLabel) {
            planPhaseText.value = d.planLabel;
        }
    }

    // celebraciones
    initMedals().catch(() => {});
});
```

Justificación:
- `Promise.allSettled` paraleliza sin que un fallo aborte los demás.
- `AbortController` evita callbacks huérfanos al navegar rápido.
- Mantiene el comportamiento de "silent" en errores no críticos (coach branding, dashboard fetch).

### Paso E — Aplicar el mismo patrón a layouts hermanos para evitar regresión

Los layouts coach, RISE y admin posiblemente comparten `.tb-phase` (no confirmado al 100%). Hacer:

```bash
grep -rn "tb-phase" resources/
```

Si aparece en `RiseLayout.vue`, `coach.blade.php`, etc., aplicar la misma estructura `min-w-0 flex-1` / `shrink-0` al header. **No edites otros archivos sin verificar antes con grep que usan la clase.**

---

## 4. Plan de pruebas obligatorio

Antes de marcar el ticket como cerrado:

1. **Ejecutar `npm run dev`** y abrir `http://wellcore-laravel.test/client` (impersona como cualquier cliente con plan asignado — usar `daniel.esparza` / `RISE2026Admin!SuperPower` y luego impersonar).
2. **Verificar 4 viewports en DevTools** (Chrome DevTools MCP):
   - 360×640 (Galaxy S Mini, viewport mínimo realista)
   - 390×844 (iPhone 14)
   - 430×932 (iPhone 14 Pro Max)
   - 768×1024 (iPad portrait)
3. **Casos de prueba:**
   - Cliente sin plan → badge no se renderiza, layout estable.
   - Cliente con plan pero sin fase nombrada → badge muestra solo "Semana X".
   - Cliente con plan + fase larga ("Adaptación neuromuscular") → badge se trunca con ellipsis, íconos del lado derecho 100% visibles.
   - Cliente con impersonación admin activa → banner amarillo arriba, topbar offset 40px, badge truncado igual.
   - Cliente con CoachImpersonationBanner + admin impersonation simultáneos → ambos banners apilados sin solaparse (verifica z-index 90/100).
4. **Lighthouse mobile** (Chrome DevTools MCP) en `/client`. Apuntar a:
   - CLS < 0.05 (sin shifts del topbar al cargar el badge)
   - LCP < 2.5s
5. **Capturas before/after** del topbar en 360px guardadas en `_screenshots_perf/` con nombres:
   - `before-topnav-overflow-360.png`
   - `after-topnav-truncated-360.png`

---

## 5. Auditoría defensiva del cliente — bugs adicionales detectados

> Estos son bugs/anti-patterns ya detectados durante el diagnóstico. **No están bloqueando** la fix del topbar, pero conviene cerrarlos en el mismo PR si caben en el scope, o crear tickets separados.

### 5.1 NotificationBell — silent failures + polling agresivo

`resources/js/vue/components/NotificationBell.vue:34-61`

- `fetchNotifications` traga errores en silencio (`catch {}`). En producción si el endpoint cae, el cliente nunca se entera. **Fix:** logear con `console.warn` y exponer un estado `error` para mostrar un dot rojo discreto.
- `markAsRead` y `markAllAsRead` no manejan errores en absoluto — si el server falla, el UI muestra como "leída" una notificación que sigue marcada como nueva en DB. **Fix:** envolver en try/catch + revertir estado optimista.
- Polling cada 90s sigue corriendo aunque la pestaña esté oculta. **Fix:** suspender con `document.visibilityState === 'hidden'` y reanudar en `visibilitychange`.

### 5.2 CoachImpersonationBanner — memory leak

`resources/js/vue/components/CoachImpersonationBanner.vue:60-64`

- Registra `window.addEventListener('storage', refreshState)` en `onMounted` pero **nunca lo remueve** en `onUnmounted`. Cada vez que el componente se desmonta-remonta, se acumulan listeners.

```js
onMounted(() => {
  refreshState();
  window.addEventListener('storage', refreshState);
});
onUnmounted(() => {
  window.removeEventListener('storage', refreshState);
});
```

### 5.3 Stack de banners — z-index mal coordinado

Tres banners pueden coincidir en pantalla:
- `CoachImpersonationBanner` → `fixed top-0 z-[100]`
- Admin impersonation banner inline en `ClientLayout.vue:216` → `fixed top-0 z-[90]`
- Topbar `<header>` → `sticky top-0 z-30` (con `top-10` cuando isImpersonating)

Si admin impersona Y el coach también marcó al cliente como impersonado en localStorage, los dos banners se apilan en `top-0` y se solapan (z-100 cubre z-90, pero ocupan el mismo espacio físico). Esto es un edge case raro pero existe. **Fix:** decidir prioridad — cuando `isImpersonating` admin sea true, ocultar el banner de coach.

### 5.4 Layout Livewire — pull-to-refresh + swipe nav interfieren

`resources/views/layouts/client.blade.php:390-515`

Hay **dos handlers de `touchstart` distintos** en elementos hermanos:
1. Pull-to-refresh (líneas 397-431)
2. Swipe nav lateral (líneas 458-512)

Ambos llaman `e.preventDefault()` o `passive` en distintas condiciones. En páginas con scroll horizontal interno (gráficos, tablas) los swipes a veces se cancelan. **Fix sugerido:** unificar en un solo Alpine component que gestione ambos gestos con prioridad clara (vertical > horizontal por encima de cierto threshold).

### 5.5 Dashboard endpoint duplicado en flujo

`ClientLayout.vue:69` ya hace `GET /api/v/client/dashboard` para el badge. Si la página `/client` (DashboardHero, etc.) también llama el mismo endpoint en su propio `onMounted`, hay **fetch duplicado** en el primer paint. **Fix:** mover el fetch a un store Pinia (`useDashboardStore`) cacheado por 30s y consumirlo desde ambos lugares.

### 5.6 Lógica de plan en blade (5.3 ya cubre el cálculo, esto es complementario)

`resources/views/layouts/client.blade.php:336-348`

`$client->plan` es probablemente un PHP enum. `->created_at` no existe → siempre cae a `now()` → `$weekNum` = 1 siempre. Por eso quizá el usuario nunca ha visto el bug en Livewire (siempre dice "Semana 1"). Verificar imprimiendo `dd($client->plan, $client->plan->created_at ?? 'sin fecha')`.

### 5.7 useMedals — composable con `inflight` que no se limpia tras error

`resources/js/vue/composables/useMedals.js:71-120`

El singleton de medallas guarda `inflight` para deduplicar fetches concurrentes, pero si la promesa rechaza, `inflight` se queda con la promesa rechazada. Todos los fetches siguientes reciben ese reject sin reintento real.

```js
async function fetchMedals(...) {
  if (inflight) return inflight;
  inflight = (async () => {
    try { /* ... */ }
    catch (err) {
      // ⚠ FIX: limpiar inflight para permitir reintento
      throw err;
    } finally {
      inflight = null;     // ← añadir esto
    }
  })();
  return inflight;
}
```

### 5.8 Race conditions: fetch sin AbortController en páginas críticas

Patrón repetido en:
- `resources/js/vue/pages/Client/CommunityFeed.vue:67-105` (`fetchFeed` sin cancel)
- `resources/js/vue/pages/Client/MetricsTracker.vue:79-99` (`fetchMetrics` sin cancel)

Si el usuario navega rápido, la respuesta del fetch huérfano llega y muta el estado de un componente desmontado o de la siguiente página. Vue avisa con `[Vue warn]: Unhandled error...` en consola.

**Fix patrón:** envolver fetches en un composable `useCancellableFetch` reutilizable o, mínimo, agregar:

```js
let aborter;
async function fetchFeed() {
  aborter?.abort();
  aborter = new AbortController();
  try {
    const res = await api.get('/api/v/client/community/feed', { signal: aborter.signal });
    if (!aborter.signal.aborted) feed.value = res.data;
  } catch (err) {
    if (err.name !== 'CanceledError' && err.name !== 'AbortError') throw err;
  }
}
onUnmounted(() => aborter?.abort());
```

### 5.9 Timers no limpiados en `onUnmounted`

- `pages/Client/CheckinForm.vue:157-165` — `confettiTimer = setTimeout(...)` dentro de `watch(showSuccess)` y nunca se cancela. Además, si `showSuccess` parpadea true→false→true rápido, los timers se acumulan.
- `pages/Client/MetricsTracker.vue:50-56` — `confettiTimer`, `successTimer` y la instancia de Chart.js sin destroy en unmount.

**Fix patrón:**

```js
let confettiTimer = null;
watch(showSuccess, (v) => {
  if (confettiTimer) clearTimeout(confettiTimer);   // limpia anterior
  if (v) confettiTimer = setTimeout(() => { /* ... */ }, 3000);
});
onBeforeUnmount(() => {
  clearTimeout(confettiTimer);
  weightChartInstance?.destroy();
});
```

### 5.10 Router watcher en `ClientLayout.vue` con cleanup incorrecto

`ClientLayout.vue:147-153`:

```js
const unwatch = router.afterEach(() => { ... });
onUnmounted(() => { if (unwatch) unwatch(); });
```

`router.afterEach` retorna un *unsubscribe function* — el código actual ya lo invoca correctamente. **Sin embargo**, el agent reportó posibles problemas si `unwatch` no se cierra como closure. Verifica con `console.log(typeof unwatch)` en runtime; si no es función, ajustar a:

```js
const unwatch = router.afterEach(...);
onUnmounted(() => unwatch?.());
```

### 5.11 `localStorage` sin try/catch — Safari private mode crashea

Lugares afectados:
- `CoachImpersonationBanner.vue:10-12` (lectura)
- `RenewalBanner.vue` watch (escritura via `localStorage.setItem`)
- `ClientSettings.vue:51-62` (`JSON.parse` sin validación)

Safari iOS en modo privado **lanza `QuotaExceededError`** al intentar `setItem`. Crashea el component si no está protegido.

**Fix patrón** — wrapper utilitario `resources/js/vue/utils/safeStorage.js`:

```js
export const safeStorage = {
  get(key, fallback = null) {
    try { return localStorage.getItem(key); } catch { return fallback; }
  },
  set(key, value) {
    try { localStorage.setItem(key, value); return true; } catch { return false; }
  },
  remove(key) {
    try { localStorage.removeItem(key); } catch {}
  },
  getJSON(key, fallback = null) {
    try {
      const raw = localStorage.getItem(key);
      return raw ? JSON.parse(raw) : fallback;
    } catch { return fallback; }
  },
};
```

Reemplazar todas las llamadas a `localStorage.*` directas en componentes Vue del cliente.

### 5.12 Imágenes sin `width`/`height` → CLS

`pages/Client/PlanViewer.vue:1224` (y posiblemente otras tarjetas con thumbnails).

Cuando la imagen carga, el contenedor crece y empuja el resto del contenido = Cumulative Layout Shift. Lighthouse penaliza ≥ 0.1.

**Fix:** envolver en contenedor con `aspect-ratio` fijo, o añadir atributos:

```html
<img :src="..." alt="..." width="640" height="360"
     class="w-full h-auto aspect-video object-cover" loading="lazy" />
```

### 5.13 `DashboardHero.vue` — `backdrop-filter` inline no reactivo a tema

`components/dashboard/DashboardHero.vue:18` usa `style="backdrop-filter:blur(24px) saturate(1.8);"`. Si quieres cambiar el efecto en light vs dark, no puedes desde CSS por la specificity de inline. Mover a clase utility (`backdrop-blur-2xl backdrop-saturate-200` o equivalentes Tailwind v4) o variable CSS.

### 5.14 `ReferralProgram.vue` — estado `copied` huérfano en error

`pages/Client/ReferralProgram.vue:44-49`. El `.catch()` muestra toast pero no resetea `copied.value`. Si el clipboard API falla, el botón se queda en estado "copiado" para siempre.

**Fix:**

```js
navigator.clipboard.writeText(code)
  .then(() => { copied.value = true; setTimeout(() => copied.value = false, 1500); })
  .catch((err) => {
    copied.value = false;     // ← añadir
    toast.error('No se pudo copiar');
  });
```

### 5.15 `pt-10` condicional en main wrapper

`ClientLayout.vue:310`. Cuando hay impersonación, el wrapper recibe `pt-10` para offset el banner. Pero el banner es `py-2` con texto `text-sm font-medium` que en pantallas pequeñas puede romper a 2 líneas y ocupar 56px+, dejando contenido tapado por el banner.

**Fix:** usar `padding-top: env(banner-height, 2.5rem)` con CSS custom prop seteada por el banner via `useBannerHeight` composable; o garantizar que el banner sea siempre `whitespace-nowrap` + `truncate`.

### 5.16 Conflicto `touch-action: pan-y` con scroll horizontal interno

`views/layouts/client.blade.php:458`. El `<main>` aplica `style="touch-action: pan-y"` para que el swipe-nav lateral funcione, pero esto **bloquea** el panning horizontal en hijos como tablas, gráficos o carruseles. Resultado: el usuario no puede scrollear horizontalmente una tabla porque el touch se captura para swipe-nav.

**Fix:** quitar `pan-y` del `<main>` y aplicar `touch-action: manipulation` solo a un wrapper específico que envuelve el indicador de swipe. Hacer que los gestos horizontales se evalúen primero por dirección dominante (delta-X vs delta-Y).

---

## 6. Reglas obligatorias para Sonnet

1. **No crear migraciones destructivas.** Es un proyecto que comparte DB con la app PHP vanilla en `C:\Users\GODSF\Herd\wellcorefitness`.
2. **No tocar `C:\Users\GODSF\Herd\wellcorefitness`** — solo modificar el directorio `wellcore-laravel`.
3. **No correr `npm run build`** — siempre `npm run dev` para verificar y `git push` para deploy (memoria del usuario).
4. **No commitear `public/build/`** salvo que el flujo de deploy lo requiera (verifica con el usuario antes).
5. **No eliminar la lógica del Livewire layout** aunque parezca legacy — sigue activa para ciertos clientes durante la migración Strangler Fig.
6. **Tests:** ejecutar `php artisan test --filter=ClientLayout` (si existe). Si no, crear test feature mínimo verificando que `/client` responde 200 con el header.
7. **Compatibilidad dark/light:** verificar el badge en ambos modos. El color `rgba(220,38,38,.14)` tiene contraste decente en dark pero puede verse débil en light — confirma con captura.
8. **Delegar a agentes Laravel cuando aplique** (memoria del proyecto):
   - `la-03-vue3` para los cambios en `ClientLayout.vue`
   - `la-04-tailwind-ds` para la edición del CSS
   - `la-02-backend` si toca crear el `ClientPlanPhaseService`
9. **No mostrar emojis** en código ni en commits (memoria del usuario).
10. **Mensaje de commit final** estilo:
    ```
    fix(client): topbar phase badge no longer overflows on mobile

    - .tb-phase: add min-width:0, max-width clamp, truncation
    - both layouts: add min-w-0 flex-1 to left section, shrink-0 to right
    - centralize phase label in backend (Vue ya lo hacía, Livewire no)
    - dashboard fetch: parallel + AbortController in mounted

    Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
    ```

---

## 7. Definition of Done

### PR principal — Fix del topbar (debe entrar en el primer push)

- [x] CSS `.tb-phase` actualizado en `app.css` (`min-width:0`, `max-width: clamp(...)`, `overflow:hidden`, `text-overflow:ellipsis`, `inline-flex`)
- [x] `ClientLayout.vue` topbar con `min-w-0 flex-1` / `shrink-0` correctos
- [x] `views/layouts/client.blade.php` topbar con la misma estructura
- [x] `ClientPlanPhaseService` + composer creados (Opción C.1)
- [x] `onMounted` del `ClientLayout.vue` paraleliza fetches con `Promise.allSettled` + `AbortController`
- [ ] Verificación visual en 4 viewports (capturas guardadas)
- [ ] Lighthouse mobile sin regresión (CLS < 0.05)
- [ ] PR pushed con descripción que linkee a las 2 capturas before/after
- [ ] No hay errores en consola del browser al cargar `/client` con cliente real

### PR/commit secundario — Hardening crítico (mismo branch o branch consecutiva)

- [x] **5.1** NotificationBell: try/catch real + estado de error + pause polling cuando `document.hidden`
- [x] **5.2** CoachImpersonationBanner: `removeEventListener('storage')` en `onUnmounted`
- [x] **5.7** useMedals: `inflight = null` en `finally` — YA EXISTIA (no requirió cambio)
- [x] **5.8** Crear `useCancellableFetch` composable y migrar `CommunityFeed` + `MetricsTracker`
- [x] **5.9** Limpiar timers en `CheckinForm` + `MetricsTracker` — ya tenían onBeforeUnmount; mejorado watch en CheckinForm
- [x] **5.11** Crear `safeStorage` util y reemplazar todos los `localStorage.*` directos en `ClientSettings.vue`; `RenewalBanner.vue` ya tenía try/catch propio

### Tickets separados (no bloquear el fix del topbar)

- [ ] **5.3** Stack de banners: decidir prioridad admin > coach impersonation
- [ ] **5.4** Unificar pull-to-refresh + swipe nav en `views/layouts/client.blade.php`
- [ ] **5.5** Mover `dashboard` fetch a Pinia store cacheado
- [ ] **5.10** Verificar runtime el cleanup del router watcher
- [ ] **5.12** Imágenes en `PlanViewer` con `width/height` o `aspect-ratio` (CLS fix)
- [ ] **5.13** `DashboardHero` backdrop-filter a clase Tailwind
- [ ] **5.14** `ReferralProgram.vue:44-49` resetear `copied` en catch
- [ ] **5.15** Banner height responsive con CSS custom prop
- [ ] **5.16** `touch-action` reescrito en main wrapper Livewire

---

## 8. Apéndice — comandos útiles

```bash
# 1. Buscar todos los usos del badge antes de tocar
grep -rn "tb-phase" resources/

# 2. Ver estructura actual de plan/fases en DB
php artisan tinker
>>> \DB::select('SHOW TABLES LIKE "%plan%"');
>>> \DB::select('DESCRIBE plan_phases');  # si existe

# 3. Servir y probar
php artisan serve   # o usar Herd: wellcore-laravel.test
npm run dev

# 4. Lighthouse vía MCP Chrome DevTools (si está disponible)
#    → mcp__chrome-devtools__lighthouse_audit en /client

# 5. Push (NO deploy) — el deploy lo activa el usuario manualmente
git add -A
git commit -m "fix(client): topbar phase badge overflow"
git push origin fix/client-topnav-overflow
```

---

**Listo. Cuando termines este plan, vuelve y reporta:**
1. Capturas before/after en 360px y 430px (dark + light).
2. Confirmación de cada item del checklist Definition of Done.
3. Cualquier hallazgo nuevo que requiera ticket separado.
