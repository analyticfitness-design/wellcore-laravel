# WellCore Admin · Primitives UI Index

> Catálogo de los 14 primitives `Wc*` en `resources/js/vue/components/ui/wellcore-admin/`.
> Cada primitive replica una sección del HTML target Claude Design literal.
> Última actualización: **2026-05-03** (Fase 7).

---

## 1. WcAdminCard.vue

Wrapper genérico de tarjeta con `gb2` (más prominente) o `gb3` (sutil) gradient border.

**Props**:
- `title`: string — título uppercase pequeño
- `meta`: string — texto secundario derecha
- `link`: string — texto del link clickeable
- `variant`: `'gb2' | 'gb3'` (default `gb3`)
- `cardClass`: string — clase extra para grid spans

**Slots**: `head` (override del title), default (contenido del card)

**Eventos**: `link` (cuando se clickea el link)

---

## 2. WcAdminKpi.vue

KPI card desktop con sparkline mini (100×28).

**Props**:
- `variant`: `'amber' | 'green' | 'blue' | 'red'` — color de la barra izquierda
- `label`: string (required)
- `value`: string | number (required)
- `unit`: string — sufijo del número (ej. "%")
- `sub`: string — descripción (acepta `v-html`)
- `delta`: string — pill de delta
- `deltaVariant`: `'up' | 'flat' | 'warn'`
- `sparkPath`: string — SVG path d=""
- `sparkColor`: string — override stroke

---

## 3. WcAdminAlertChip.vue

Chip de alerta con leading-color stripe (amber o blue).

**Props**:
- `variant`: `'amber' | 'blue'`
- `label`: string (required)
- `value`: string | number (required)
- `sub`: string

**Slots**: `icon` (custom SVG; default = clock o warning según variant)

---

## 4. WcAdminFeedRow.vue

Fila de feed (pago o inscripción) con grid columns: led + icon + body + amount/cta.

**Props**:
- `variant`: `'pago' | 'insc'`
- `name`: string (required)
- `plan`, `when`, `amount`, `pending`, `meta`: string
- `ctaText`: string — texto del botón "Contactar"

**Slots**: `icon`

**Eventos**: `cta`, `click`

---

## 5. WcAdminFeedGroup.vue

Grupo de feed-rows con header (título + count-pill + when).

**Props**:
- `title`: string (required)
- `count`: string | number
- `countVariant`: `'green' | 'blue'`
- `when`: string

**Slots**: default (FeedRows)

---

## 6. WcAdminToolRow.vue

Fila de herramienta con icon + name + meta + chevron.

**Props**:
- `to`: string | object — Vue Router target
- `iconVariant`: `'red' | 'green' | 'blue' | 'amber' | 'purple' | 'gold' | ''`
- `name`: string (required)
- `meta`: string — sufijo (ej. "12", "3 pend.", "Beta")
- `pulse`: boolean — pulse-dot indicador

**Slots**: `icon`

---

## 7. WcAdminEmpty.vue

Empty state con SVG art + título + hint + CTA.

**Props**:
- `title`: string (required, acepta HTML con `v-html`)
- `hint`: string
- `ctaText`: string

**Slots**: `art` (custom SVG)

**Eventos**: `cta`

---

## 8. WcAdminSparkline.vue

Sparkline SVG dinámica.

**Props**:
- `data`: number[] — array de valores 0..N
- `variant`: `'mrr' | 'mini'` — `mrr` 320×56 con gradient + fill, `mini` 100×28 stroke only
- `color`: `'red' | 'green' | 'blue' | 'amber'`

Internamente normaliza `data` al viewBox y dibuja path + fill + circle dot.

---

## 9. WcAdminDonut.vue

Donut SVG multi-segment con stroke-dasharray.

**Props**:
- `segments`: `[{ color, value }, ...]` (required)
- `total`: number — si no se pasa, suma los segments
- `centerLabel`: string — texto bajo el número central

Renderiza un círculo background + N círculos (uno por segment) con `stroke-dasharray` y `stroke-dashoffset` calculados.

---

## 10. WcAdminProgressBar.vue

Barra de progreso con shimmer animation.

**Props**:
- `percent`: number (0..100)
- `tall`: boolean — `true` = 8px height (default 5px)

---

## 11. WcAdminCommandPalette.vue (Cmd+K)

Modal palette con search + secciones (Sugerencias, Navegación).

**Props**:
- `shortcuts`: `[{ id, label, route, meta, icon, section }, ...]`

**Eventos** (expuestos via `defineExpose`):
- `open()` / `close()`

**Listener**: `keydown` global. Cmd+K (o Ctrl+K) toggles. Escape cierra.

---

## 12. WcAdminSidebar.vue

Sidebar fija desktop / drawer mobile con 9 secciones × 20+ items.

**Props**:
- `open`: boolean — drawer mobile abierto
- `userName`, `userRole`: string

**Eventos**: `close`, `logout`

**Mapa de iconos**: `ICON_PATHS` con 22 paths Heroicons stroke-1.6 (home, lightning, users, form, card, user-plus, mail, check, headset, megaphone, clipboard, sparkles, chart, ticket, inbox, stats, target, share, wrench, shield, settings).

**Estructura**:
- `.side-brand` — logo + name + role-chip + collapse-btn (desktop) / close-drawer (mobile)
- `.side-scroll` — items
- `.side-foot` — avatar + nombre + rol + gear (logout)

---

## 13. WcAdminTopBar.vue

Topbar mobile (sticky 56px) y desktop (sticky 64px) en el mismo SFC, switch via `@media`.

**Props**:
- `userName`, `userRole`: string
- `notifBadge`: number | string

**Eventos**: `toggleSidebar`, `openSearch`

**Slots**: ninguno (markup fijo)

**Mobile** lleva: hamburger + brand-mark logo + brand-name + role-chip + cmdk-pill + bell (NotificationBell) + avatar.
**Desktop** lleva: tb-eye (Command Center · fecha) + cmdk-pill + bell + clock JetBrains Mono.

**Bell**: usa `<NotificationBell endpoint="/api/v/admin/notifications" :poll-interval="60000" />` — dropdown completo, fetch real, mark-as-read, click-outside.

---

## 14. WcAdminBottomNav.vue

Mobile only (hidden desktop via CSS). 5 tabs con cap indicator.

**Props**: ninguno

**Items hardcoded**: Dashboard, Clientes, Pagos, Feed, Más.

**Iconos**: SVG inline por item (`item.icon === 'dashboard' | 'users' | 'card' | 'live' | 'more'`).

---

## Convenciones generales

1. **NO Tailwind utilities** dentro del scope `.wc-admin-shell`. Solo classes target literales.
2. **`<script setup>` con `defineProps` + `defineEmits` + `defineExpose`** (no Options API).
3. **SVG icons**: `viewBox="0 0 24 24"`, `fill="none"`, `stroke="currentColor"`, `stroke-width="1.6"` o `1.8`. **Width/height** explícitos en attrs para evitar layout shift.
4. **CSS de los primitives**: vive en `wc-admin-shell.css` (universal) — los SFCs solo declaran markup. Excepto cuando hay reglas mobile/desktop específicas dentro de `<style scoped>` (ej. `WcAdminTopBar` tiene `.topbar-mobile/desktop { display }`).
5. **Animations** definidas en `wc-admin-shell-tabs/dashboard.css` con prefijo `wcAdmin*` (`wcAdminPulse`, `wcAdminShimmer`, `wcAdminPulseRing`, `wcAdminCmdIn`) para evitar colisión con animaciones globales del cliente.
