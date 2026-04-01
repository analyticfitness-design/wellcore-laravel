# Plan de Continuación — WellCore Vue 3 Migration + Font Redesign
**Fecha:** 2026-04-01 | **Para:** Próxima sesión de Claude Code

---

## CONTEXTO DE LO QUE YA SE HIZO (no repetir)

### Componentes Vue 3 ya creados y funcionando ✅
| Componente | Ruta Vue | API |
|---|---|---|
| RecipeDatabase.vue | `/v/client/recipes` | Estático (20 recetas + 4 metas) |
| AudioPlayer.vue | `/v/client/audio` | Estático (15 sesiones, Web Audio API) |
| VideoLibrary.vue | `/v/client/videos` | GET `/api/v/client/videos` |
| Academia.vue | `/v/client/academia` | GET `/api/v/client/academia` |
| Mindfulness.vue | `/v/client/mindfulness` | Estático (timer + breathing engine) |

### API endpoints ya agregados en SocialController + api.php
- `GET /api/v/client/videos` — CoachVideoTip model
- `GET /api/v/client/academia` — AcademyContent model
- `GET /api/v/client/video-checkins` — VideoCheckin history + monthly count
- `POST /api/v/client/video-checkin` — upload multipart/form-data (4/mes límite)

### Fixes de infraestructura ya hechos
- `la-03-vue3.md` reescrito a 276 líneas (era 1922 — no cargaba). **Verificar que ahora aparece en la lista de agentes al iniciar sesión.**
- Router (`resources/js/vue/router/index.js`) — rutas de los 5 componentes agregadas

---

## AGENTES DISPONIBLES EN ESTA SESIÓN
Antes de empezar, verificar con `Agent(subagent_type="la-03-vue3", ...)` que ya carga.
Si no carga, hacer todo directamente — el patrón es el mismo que en los componentes existentes.

---

## TAREA 1 — MIGRACIÓN DE FUENTES (PRIORIDAD ALTA)

### Objetivo
- **Títulos/headings** (`font-display`): cambiar de **Bebas Neue** → **Oswald**
- **Cuerpo/body** (`font-sans`): cambiar de **Inter** → **Raleway**
- **Sin cambios**: `font-mono` (JetBrains Mono), `font-data` (Barlow)

### Pasos

**Paso 1A — Actualizar vue.blade.php**
Archivo: `resources/views/vue.blade.php` línea 20.
Reemplazar la línea de Google Fonts por:
```html
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Raleway:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
```

**Paso 1B — Actualizar los 18 layouts Livewire** (reemplazar la misma línea en cada uno):
- `resources/views/layouts/client.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/layouts/coach.blade.php`
- `resources/views/layouts/rise.blade.php`
- `resources/views/layouts/shop.blade.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/components/layouts/client.blade.php`
- `resources/views/components/layouts/admin.blade.php`
- `resources/views/components/layouts/coach.blade.php`
- `resources/views/components/layouts/rise.blade.php`
- `resources/views/components/layouts/shop.blade.php`
- `resources/views/components/layouts/public.blade.php`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/errors/503.blade.php`
- `resources/views/errors/500.blade.php`
- `resources/views/errors/403.blade.php`
- `resources/views/errors/404.blade.php`

**Grep para encontrar la línea exacta en cada archivo:**
```bash
grep -r "googleapis.com/css2" resources/views/ -l
```

**Paso 1C — Actualizar `resources/css/app.css`**
En el bloque `@theme`, cambiar:
```css
/* ANTES */
--font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif, ...;
--font-display: 'Bebas Neue', Impact, sans-serif;

/* DESPUÉS */
--font-sans: 'Raleway', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
--font-display: 'Oswald', Impact, sans-serif;
```

**Paso 1D — Ejecutar build y verificar visualmente con Chrome DevTools**

Después del build (`npm run dev` ya corriendo con hot reload), verificar en Chrome:
1. Navegar a `http://wellcore-laravel.test/v/client` (dashboard)
2. Screenshot full page
3. Navegar a `http://wellcore-laravel.test/v/client/mindfulness` (tiene muchos headings)
4. Screenshot

**Paso 1E — Verificar mobile (Chrome DevTools viewport)**
Usar `mcp__chrome-devtools__emulate` para simular iPhone 12 (375px):
```
mcp__chrome-devtools__emulate({ device: "iPhone 12" })
```
O resize a 375px:
```
mcp__chrome-devtools__resize_page({ width: 375, height: 812 })
```
Tomar screenshots de:
- Dashboard `/v/client`
- Mindfulness `/v/client/mindfulness`
- Workout player `/v/client/workout`
- Nutrition `/v/client/nutrition`

**Ajustes tipográficos móvil a revisar:**
- `font-display text-3xl` en headings — puede quedar muy grande en móvil → considerar `text-2xl sm:text-3xl`
- `font-display text-4xl` si existe → `text-3xl sm:text-4xl`
- Los títulos con Oswald quedan más angostos que Bebas Neue, verificar que el line-height sea correcto
- Raleway en `text-xs` en móvil — verificar legibilidad
- Letter-spacing: Oswald tiene menos necesidad de `tracking-wide` que Bebas Neue — revisar si quedan bien o ajustar

---

## TAREA 2 — REVISAR COMPONENTES YA MIGRADOS

Con el agente la-03-vue3 o directamente, revisar estos archivos:

### Lista de revisión rápida
| Archivo | Qué revisar |
|---|---|
| AudioPlayer.vue | `onBeforeUnmount` cleanup de AudioContext ✓ ya tiene |
| Mindfulness.vue | `onBeforeUnmount` cleanup ✓ ya tiene — revisar si `v-show` vs `v-if` en sección de breathing |
| VideoLibrary.vue | Error state en API, deduplicar `fetchVideos` |
| Academia.vue | `v-show` en expanded detail panel |
| RecipeDatabase.vue | Modal keyboard trap (ESC key) |

**Fixes conocidos que aplicar:**
1. `Mindfulness.vue` línea ~170: el config panel usa `v-if="!running && !paused"` — está bien, no hay media en ese panel
2. `Academia.vue`: el panel de detalle usa `v-if="selectedContent"` — si tiene `<video>` embebido, cambiar a `v-show`
3. Verificar que todos los componentes tienen `<style scoped>` con las transiciones `.fade-enter-active` etc.

---

## TAREA 3 — COMPONENTES PENDIENTES DE MIGRAR

### 3A. VideoCheckinUpload.vue (SIGUIENTE EN COLA)
**Ruta:** `/v/client/video-checkin`
**API ya lista:**
- `GET /api/v/client/video-checkins` → `{ checkins, monthly_count, monthly_limit: 4 }`
- `POST /api/v/client/video-checkin` → multipart, campos: `media_file`, `exercise_name`, `notes`

**Características del componente:**
- Drag-and-drop zone con `@dragover.prevent`, `@dragleave.prevent`, `@drop.prevent`
- Preview: `<img>` (imagen) o ícono de video (video), usando `URL.createObjectURL(file)`
- `onBeforeUnmount` → `URL.revokeObjectURL(url)` al destruir o cambiar archivo
- Monthly usage badge: "2/4 este mes" con color según uso
- Success notification auto-dismiss 5s
- History accordion: `v-show` (no `v-if`) para el contenido expandido (tiene `<video>`)
- Left border color: `border-l-yellow-500` (pending), `border-l-emerald-500` (coach_reviewed), `border-l-blue-500` (ai_reviewed)
- Validación client-side: extensiones, tamaño (100MB video, 10MB imagen)
- `FormData` para el POST — NO setear Content-Type manualmente
- Después de submit exitoso: reset form + re-fetch history

**Agregar ruta al router:**
```js
{ path: '/v/client/video-checkin', name: 'client-video-checkin', component: () => import('../pages/Client/VideoCheckinUpload.vue'), meta: { auth: true, title: 'Video Check-in — WellCore' } },
```

### 3B. EvidenceHacks.vue
**Leer primero:** `app/Livewire/Client/EvidenceHacks.php` + `resources/views/livewire/client/evidence-hacks.blade.php`
Leer en chunks (`limit: 150` para la blade) para no exceder tokens.

### 3C. RestTimer — DECISIÓN PENDIENTE
El componente original es un overlay activado por evento `open-rest-timer`.
Ya existe `WorkoutTimer.vue` como página standalone en `/v/client/timer`.
**Decisión:** ¿crear como overlay Vue dentro de WorkoutPlayer.vue o skip?
Revisar si WorkoutPlayer.vue ya tiene timer integrado.

### 3D. NotificationBell
**Archivo:** `app/Livewire/Client/NotificationBell.php`
Probablemente va en el ClientLayout, no como página standalone.
Verificar si ya está integrada en `resources/js/vue/layouts/ClientLayout.vue` o similar.

### 3E. PlanOnboarding
**Archivo:** `app/Livewire/Client/PlanOnboarding.php`
Probablemente es un modal/wizard que aparece al primer login.
Leer el PHP para entender el trigger.

---

## TAREA 4 — TESTING MOBILE CON CHROME DEVTOOLS

Después de cada componente creado/modificado:

```
# Navegar
mcp__chrome-devtools__navigate_page({ type: "url", url: "http://wellcore-laravel.test/v/client/RUTA" })

# Simular móvil (375px iPhone)
mcp__chrome-devtools__resize_page({ width: 375, height: 812 })

# Screenshot
mcp__chrome-devtools__take_screenshot({ fullPage: true })

# Volver a desktop
mcp__chrome-devtools__resize_page({ width: 1280, height: 800 })
```

**Páginas prioritarias para verificar en móvil:**
1. `/v/client` — Dashboard
2. `/v/client/mindfulness` — Muchos headings con nueva fuente
3. `/v/client/workout` — Workout player
4. `/v/client/nutrition` — Plan de nutrición
5. `/v/client/checkin` — Check-in form
6. Cualquier componente recién creado

---

## ORDEN DE EJECUCIÓN RECOMENDADO

```
1. Verificar que la-03-vue3 ya carga (test con Agent tool)
2. TAREA 1 — Fonts (Oswald + Raleway) → build → screenshot desktop → screenshot móvil
3. TAREA 2 — Revisar componentes existentes → fixes rápidos
4. TAREA 3A — VideoCheckinUpload.vue → test Chrome
5. TAREA 3B — EvidenceHacks.vue → test Chrome
6. TAREA 3C — RestTimer decision → implement o skip
7. TAREA 3D/3E — NotificationBell + PlanOnboarding si aplican
8. TAREA 4 — Ronda final de testing móvil en todas las páginas principales
```

---

## ARCHIVOS CLAVE DE REFERENCIA

| Archivo | Propósito |
|---|---|
| `resources/css/app.css` | Design tokens, fuentes, dark mode |
| `resources/views/vue.blade.php` | HTML shell del SPA Vue |
| `resources/js/vue/router/index.js` | Rutas Vue |
| `resources/js/vue/composables/useApi.js` | Axios instance con Bearer token |
| `resources/js/vue/pages/Client/` | Páginas del cliente |
| `app/Http/Controllers/Api/SocialController.php` | API videos + academia + video-checkin |
| `routes/api.php` | Todas las rutas API |

---

## CREDENCIALES DE PRUEBA
- **URL local:** `http://wellcore-laravel.test`
- **Cliente de prueba:** `juan.perez@email.com` / `Test1234!` (id: 1)
- **Superadmin:** `daniel.esparza` / `RISE2026Admin!SuperPower`

---

## NOTAS IMPORTANTES
- **NUNCA** modificar `C:\Users\GODSF\Herd\wellcorefitness` (app vanilla PHP)
- **NUNCA** crear migraciones destructivas
- Siempre `git push` al final, nunca deploy automático
- El agente `la-03-vue3` ahora tiene 276 líneas — debe cargar al reiniciar sesión
- Si `la-03-vue3` no carga, usar el agente `general-purpose` o hacerlo directamente
