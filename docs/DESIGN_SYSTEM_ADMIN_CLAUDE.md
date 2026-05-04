# WellCore Admin · Design System (Claude Design)

> Documento de referencia para diseñar/extender el portal admin de WellCore migrado al target Claude Design.
> Última actualización: **2026-05-03** (Fase 7 polish)

---

## 1. Filosofía

El admin es la **herramienta operativa** de Daniel (CEO). Densidad alta, dark-first, datos financieros sensibles, RBAC estricto.

- **Dark-only**: el admin fuerza `<html class="dark">` al montar el layout. No hay toggle.
- **Densidad**: cards con padding 14-20px, números 28-44px, labels uppercase 10-11px.
- **Identidad WellCore**: rojo `#DC2626` accent + atmosphere radial-gradient + tipografía Oswald/Raleway/Barlow/JetBrains Mono.

---

## 2. Stack visual

| Capa | Archivo |
|---|---|
| Tokens + reset | `resources/css/wc-admin-shell.css` |
| Dashboard layout | `resources/css/wc-admin-shell-tabs/dashboard.css` |
| Vistas operacionales (cosmetic) | `resources/css/wc-admin-shell-tabs/operaciones.css` |
| Layout Vue | `resources/js/vue/layouts/AdminLayout.vue` |
| Primitives admin | `resources/js/vue/components/ui/wellcore-admin/Wc*.vue` |
| Dashboard sections | `resources/js/vue/components/admin/dashboard/Admin*.vue` |
| Legacy (rollback) | `resources/css/admin-atmosphere.css` + `components/admin/dashboard/Admin{TopBar,Sidebar,BottomNav,Greeting,...}.vue` |

---

## 3. Wrapper y modifiers

```html
<div class="wc-admin-shell wc-admin-shell--{tab} [admin-shell?]">
```

| Modifier | Aplica a | Cosmetic |
|---|---|---|
| `--dashboard` | `/admin` | ❌ (full target) |
| `--clients`, `--payments`, `--inscriptions`, `--payment-proofs`, `--invitations` | Operaciones financieras | ✅ |
| `--coaches`, `--plan-tickets`, `--stats-tickets`, `--tickets`, `--client-requests` | Equipo | ✅ |
| `--campaigns`, `--rise`, `--plans`, `--marketing`, `--ai-generator`, `--referrals`, `--chat-analytics`, `--formularios` | Marketing/RISE/Planes | ✅ |
| `--tools`, `--settings`, `--audit-log`, `--live-feed` | Sistema | ✅ |

**Cuando `cosmetic=true`**, el wrapper también lleva `.admin-shell` para que los estilos legacy de `admin-atmosphere.css` apliquen al contenido del slot. Esto preserva el contenido legacy de cada vista intacto mientras se renderiza el chrome target nuevo (sidebar/topbar/bottomnav).

---

## 4. Tokens

```css
.wc-admin-shell {
  /* Backgrounds */
  --wc-bg: #09090B;        /* base */
  --wc-bg2: #111113;       /* cards principales */
  --wc-bg3: #18181B;       /* cards secundarias */
  --wc-bg4: #1E1E22;       /* tier 4 */

  /* Accent */
  --wc-accent: #DC2626;
  --wc-accent-2: #EF4444;
  --wc-accent-deep: #7F1D1D;

  /* Text */
  --wc-text: #FAFAFA;
  --wc-text-2: #A1A1AA;
  --wc-text-3: #71717A;
  --wc-text-4: #52525B;
  --wc-border: #27272A;

  /* Status */
  --wc-green: #10B981;
  --wc-blue: #3B82F6;
  --wc-amber: #F59E0B;
  --wc-purple: #A78BFA;
  --wc-gold: #C8A769;

  /* Fuentes */
  --fd: 'Oswald';           /* display titulares uppercase */
  --fs: 'Raleway';          /* body */
  --fm: 'Barlow';           /* tabular numbers */
  --fc: 'JetBrains Mono';   /* clock + axis */

  /* Layout */
  --wc-admin-side-w: 240px;
  --wc-admin-topbar-h: 64px;
}
```

---

## 5. Reglas duras (no negociables)

1. **`<script setup>` intacto** en todas las vistas. Solo `<template>` y `<style>` se tocan.
2. **Polling 30s** del `useAdminDashboardStore` no se rompe.
3. **Webhooks Wompi/Stripe + audit log** se preservan (lógica backend).
4. **RBAC `v-if="hasRole(...)"`** se mantiene literal.
5. **Tokens del target**: usar `--wc-*` en componentes nuevos. NO inventar `--c-*` propios.
6. **NO Tailwind utilities dentro del `.wc-admin-shell`** — solo classes target literales (`.hero`, `.mrr-card`, `.feed-row`, etc.).
7. **NO `:has()` para grid spans** — usar class names específicas (`wc-admin-card-{kind}`).
8. **NO clases CSS de 1-2 letras** dentro del shell — colisionan con icon fonts globales (e.g. `.ph` colisiona con Phosphor Icons). Prefijar con `wc-` o `cmdk-`.

---

## 6. Anti-patterns que causaron bugs (resueltos)

| Anti-pattern | Bug visible | Fix |
|---|---|---|
| Reglas CSS sidebar/topbar scopeadas a `--dashboard` solamente | Iconos sidebar 240×259px en `/admin/clients` (no había reglas mobile/desktop universales) | Mover reglas estructurales del shell a `wc-admin-shell.css` universal |
| Reglas `.cmd-overlay` solo en `--dashboard` | Palette Cmd+K visible permanentemente en `/admin/coaches`, `/admin/tickets`, etc. | Reglas universales con `display:none` por default + `.open` para mostrar |
| Clase `.ph` colisiona con Phosphor Icons font | Texto "Buscar acciones, clientes, herramientas…" renderizado como emojis 🚗 | `font-family: var(--fs) !important` + override |
| `<button class="bell">` sin `@click` handler | Campana inútil en mobile + desktop | Reemplazar por `<NotificationBell endpoint="/api/v/admin/notifications" />` |
| Logo PNG `logo-blanco-sombras.png` 1.5MB sin redimensionar | LCP lento, transferencia pesada | `sharp` resize a 128×128 PNG (4.2KB) + WebP (5.4KB), `<picture>` con fallback |
| Backend keys español (`mrr_actual_cop`) vs frontend keys inglés (`mrr_current`) | Dashboard mostraba `$0` en todas las cards | `computed()` mapper en `Dashboard.vue` |

---

## 7. Endpoint backend admin (referencia)

`GET /api/v/admin/dashboard` retorna:

```js
{
  greeting: "Buenas noches, Daniel — CEO",
  production: { plan_tickets_pendientes, plan_tickets_en_revision, ... },
  financial: {
    mrr_actual_cop,           // → mrr.current
    mrr_mes_anterior_cop,     // → mrr.previous
    mrr_delta_pct,            // → mrr.deltaPercent
    pagos_pendientes_cop,
    nuevas_inscripciones_este_mes,
  },
  operational: {
    clientes_activos, clientes_nuevos_mes, coaches_activos,
    tasa_retencion_mes_pct,   // → retention.percent
  },
  alerts: [],
  top_coaches_month: [],
  clientBreakdown: {
    activo,                   // → active (no "active")
    inactivo,                 // → inactive
    pendiente, suspendido, total,
  },
  recentInscriptions: [{ nombre, email, plan, status, timeAgo, id }],
  recentPayments: [{ buyerName, plan, amount, method, timeAgo }],  // amount es STRING formateado
  revenueChartData: [{ month: '2026-05', total: 762658 }, ...],
  planDistributionData: [{ name, value, color? }],
}
```

Para componentes nuevos que consuman este endpoint, usar `computed()` mappers en la página (no en el primitive). Patrón en `pages/Admin/Dashboard.vue`.

---

## 8. Workflow de migración (Strangler Fig)

1. Agregar entrada a `MIGRATED_ROUTES` en `AdminLayout.vue`:
   ```js
   { match: (p) => p.startsWith('/admin/{nueva-ruta}'), tab: '{nueva-ruta}', cosmetic: true }
   ```
2. Si necesita estilos específicos: crear `wc-admin-shell-tabs/{tab}.css` + `@import` en `app.css`.
3. Si requiere refactor profundo del template: cambiar `cosmetic: false` y refactorizar `<template>` con primitives `Wc*`.
4. Build + commit + push + deploy + verify Chrome DevTools.

Las vistas no en `MIGRATED_ROUTES` siguen renderizando el shell legacy intacto (rollback automático).

---

## 9. Verificación visual obligatoria

Cada PR debe verificar en **mobile 414×896** + **desktop 1440×900**:

- ✅ Console clean (0 errors, 0 warnings)
- ✅ `wrapperClasses` incluye `wc-admin-shell` + modifier correcto
- ✅ Sidebar 9 secciones con iconos Heroicons (no rect placeholder)
- ✅ Hamburger mobile + close-drawer + overlay funcionan
- ✅ Cmd+K palette oculto por default, abre con shortcut
- ✅ Bottomnav mobile / sidebar fijo desktop según viewport
- ✅ Polling 30s vivo (Dashboard) — Network tab cada 30s GET `/api/v/admin/dashboard`
- ✅ Datos reales del backend (no `$0` ni `0%`)

---

## 10. Cosas que NO se hicieron en Fase 7 (deuda futura)

- Refactor profundo del `<template>` de las 22 vistas cosmetic. Cada una sigue con su markup legacy. Reusar `WcAdminCard/Kpi/FeedRow/etc.` mejorará consistencia visual del contenido (no solo el chrome).
- Lighthouse audit mobile + desktop por vista. Targets: Performance ≥75, A11y ≥95.
- Cross-browser test Safari iOS 17, Chrome Android, Edge.
- `axe DevTools` accessibility audit per view.
- Iconos del sidebar son Heroicons stroke-1.6. Algunos podrían refinarse (ej. RISE → `sparkles` por defecto, podría ser un icono más distintivo).
