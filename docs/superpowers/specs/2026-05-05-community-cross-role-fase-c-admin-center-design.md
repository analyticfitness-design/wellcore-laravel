# Community Cross-Role — Fase C: Admin Community Center Design

> **Status:** Awaiting user review before transition to writing-plans
> **Author:** Daniel Esparza + Claude Opus 4.7
> **Date:** 2026-05-05
> **Mode:** Autonomous, premium UX/UI dark-first (admin atmosphere)
> **Dependency:** Fase A backend (16 endpoints) — disponible
> **Parallelizable:** Con Fase B (no comparten archivos UI excepto router/auth.js)
> **Successor:** Fase D Cross-Role Communication Layer

---

## Goal

Construir el **Admin Community Center** — la vista cross-coach que el superadmin usa para entender, moderar y comunicar masivamente a la comunidad WellCore. Calidad equivalente al admin dashboard nuevo (`pages/Admin/Dashboard.vue`) usando el shell `wc-admin-shell` (Fase 2+ migration).

Al final de Fase C el superadmin tiene:

- Página `/admin/community` con 5 sub-tabs analytic-heavy (Pulse Cross-Coach / Live Feed Community / Broadcast Center / Moderation Queue / Analytics Coach drill-down)
- Sidebar admin: nueva sección "Comunidad" con item único + ícono dedicado
- Pulse Cross-Coach: tabla coaches × engagement metrics + sparklines + alertas
- Broadcast Center: 3 audiences (clientes / coaches / all_communities) con segmentación rich + dry-run preview + history
- Moderation Queue: posts reportados con drill-down threads + actions inline
- Analytics Coach drill-down: full vista de un coach específico (impersonate button + métricas 90d)
- Live Feed Community: extensión filtrada del LiveFeed existente con coach selector
- Real-time updates via `admin.community` channel
- Audit log integration con filter `type=moderation`

---

## Estado actual (relevante para Fase C)

### Backend Fase A disponible (consumido por Fase C)

```
GET  /api/v/admin/community/pulse-cross-coach?period=day|week|month
     → coaches[], time_series, moderation_queue_count, totals
     Cache: wc:admin-community-analytics:v1:{period} TTL 300s

POST /api/v/admin/broadcast/preview      → recipient count for given audience+segment
POST /api/v/admin/broadcast/send         → BroadcastMessage row + dispatch
GET  /api/v/admin/broadcast/history      → paginated past broadcasts

GET  /api/v/admin/community/moderation/queue
     → reports[] with status=pending, ordered by urgency
POST /api/v/admin/community/moderation/{report}/dismiss
POST /api/v/admin/community/moderation/{report}/action
     → action: 'hide' | 'ban_client' | 'warn_client'

Reverb channels:
  admin.community → events: PostReported, PostMadeOfficial, BroadcastSent

Models:
  BroadcastMessage, PostReport, ModerationAction, CoachNotificationPreference
```

### Backend Fase C extensions necesarias

```
GET  /api/v/admin/community/coaches/{coach_id}/analytics
     → 90-day metrics, posts/day, engagement rate, response times, top clients
     Cache: wc:admin-coach-analytics:v1:{coach_id} TTL 600s

GET  /api/v/admin/community/feed?coach_id=&type=&page=
     → community-filtered live feed (extends current AdminController::feed)

GET  /api/v/admin/notifications/preferences (admin scope)
PATCH /api/v/admin/notifications/preferences
     → tabla `admin_notification_preferences` nueva (paralela a coach)

POST /api/v/admin/community/posts/{id}/pin     (override coach scope)
POST /api/v/admin/community/posts/{id}/make-global  (post WellCore en TODAS las comunidades)
```

### Frontend nuevo (Fase C)

- Page `/admin/community` con shell wc-admin-shell (migrated route)
- 5 tab components
- Charts: `Chart.js` lightweight (verificar si ya está; si no, importar tree-shakeable)
- Composables: `useAdminCommunity`, `useBroadcast`, `useModerationQueue`
- Components compartidos: `CoachAnalyticsTable`, `BroadcastPreviewBar`, `ModerationReportCard`, `CoachSparkline`, `EngagementHeatmap`, `BroadcastHistoryList`
- Modal: `AdminBroadcastModal` (alternative a tab — TBD: tab por consistencia spec original)
- Sidebar: agregar item "Comunidad" en `WcAdminSidebar.vue` + entry en `MIGRATED_ROUTES` de AdminLayout

---

## Decisiones de diseño (autónomas, premium)

| # | Decisión | Justificación |
|---|---------|---------------|
| 1 | Vista usa **shell nuevo `wc-admin-shell`** (no legacy admin-shell) | Es ruta nueva — debe nacer en el shell target Claude Design. No `cosmetic:true` — full target |
| 2 | Sidebar admin: **agregar item "Comunidad" en sección "Operaciones"** | Es operacional (modera + broadcast). Replica patrón Live Feed (también está en Operaciones según código) |
| 3 | Default landing tab = **Pulse Cross-Coach** | Analytics-first es el mindset admin. Si moderation queue tiene reports pendientes, surface visible inmediato vía badge en tab |
| 4 | Tab order: **Pulse Cross-Coach → Live Feed Community → Broadcast Center → Moderation Queue → Analytics Coach** | Spec original. Pulse primero (intelligence), Live Feed (real-time monitoring), Broadcast (action), Moderation (response), Analytics drill-down (deep-dive) |
| 5 | **Broadcast Center es tab**, no modal flotante | Admin pasa más tiempo en broadcast que coach. Tab dedicado da preview persistente + history visible |
| 6 | **Analytics Coach es tab drill-down**: empty state si no hay coach seleccionado | Click en row de tabla Pulse Cross-Coach → setea `selectedCoachId` → Analytics tab activa con datos |
| 7 | **Charts: Chart.js 4** (vanilla, tree-shakeable) | Verificar si proyecto ya lo usa (búsqueda en composables). Si no, importar `chart.js/auto` con bundle <30KB |
| 8 | **Sparklines** (mini-charts) inline en cada coach row de la tabla | Visual density premium — coach ve 30-day trend de un vistazo |
| 9 | **Heatmap actividad coach × hora** en Pulse Cross-Coach | SVG custom 7×24 grid con opacity según activity. Replica patrón Latido Coach |
| 10 | **Moderation Queue ordenado por urgency**: multi-reportados primero, recents segundo | UX: lo crítico arriba. Computed `urgency = report_count + (recent_factor)` |
| 11 | **Broadcast preview live debounced 500ms** | Cada cambio de segment recalcula recipient count. Más debounce que coach (500ms) porque admin tiene queries más pesadas |
| 12 | **Broadcast confirmation step** si recipients > 50 | Admin tiene blast radius mayor. Extra friction para evitar disasters |
| 13 | **Broadcast history**: paginated últimos 50 + filter por sender_type | Trazabilidad completa. Click row → expand details + recipient breakdown |
| 14 | **Analytics Coach drill-down**: 6 sub-secciones (Overview / Posts / Engagement / Clients / Audit Trail / Impersonate) | Profundidad del cargado | una vez en este modo, el admin tiene todo lo que necesita |
| 15 | **Real-time `admin.community` channel**: events PostReported/PostMadeOfficial/BroadcastSent → flash en tab pertinente | UX premium: "Tu equipo Maria moderó un post" sin recargar |
| 16 | **Theme: dark-only** para admin (forzado en AdminLayout) | Memory `feedback_ds_v1_light_theme.md` aclara: el admin actual fuerza dark `onMounted`. Respetar |
| 17 | **Composables**: `useAdminCommunity`, `useBroadcast`, `useModerationQueue` (3 nuevos) singleton TTL + dedup | Igual patrón Fase B |
| 18 | **Notifications preferences admin**: ruta nueva `/admin/notifications` con tabla `admin_notification_preferences` (migración aditiva) | Aprovechar momentum de Fase B/C para shipping admin notifs |
| 19 | **Audit Log integration**: extender filter type=moderation en `/admin/audit-log` (página ya existe) | No crear nueva página — leverage existente |
| 20 | **Coach impersonation desde Analytics Coach**: botón usa flow existente `useImpersonation` composable | Reutilizar — no duplicar |

---

## Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│           /admin/community (route, wc-admin-shell)               │
│                                                                  │
│ ┌──────────────────────────────────────────────────────────────┐ │
│ │ AdminLayout (wc-admin-shell + WcAdminSidebar)                │ │
│ │ └─ slot ─→ Community.vue (page hub)                          │ │
│ │           ├─ TabsHeader (5 tabs, sticky)                     │ │
│ │           ├─ <Transition>                                     │ │
│ │           │  ├─ AdminPulseCrossCoachTab.vue (default)        │ │
│ │           │  ├─ AdminLiveFeedCommunityTab.vue                 │ │
│ │           │  ├─ AdminBroadcastCenterTab.vue                   │ │
│ │           │  ├─ AdminModerationQueueTab.vue                   │ │
│ │           │  └─ AdminAnalyticsCoachTab.vue                    │ │
│ │           └─ ToastContainer                                  │ │
│ └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│ Composables singleton (module-scope):                            │
│  • useAdminCommunity (pulse cross-coach + analytics coach)       │
│  • useBroadcast (preview + send + history)                       │
│  • useModerationQueue (queue + actions)                          │
│                                                                  │
│ Pinia (existing extended):                                        │
│  • auth.js — agrega 3 reset hooks                                │
│  • adminCommunity (Pinia store NUEVO — opcional, podríamos       │
│    mantener composables-only) → uso composables-only por         │
│    consistencia con Fase B                                        │
│                                                                  │
│ Real-time (Echo):                                                 │
│  • admin.community → events: PostReported, BroadcastSent,        │
│    PostMadeOfficial, CoachCommunityActivity (admin scope)        │
│                                                                  │
│ Backend extensions:                                               │
│  • GET /api/v/admin/community/coaches/{id}/analytics             │
│  • POST /api/v/admin/community/posts/{id}/pin (admin override)   │
│  • POST /api/v/admin/community/posts/{id}/make-global            │
│  • GET /api/v/admin/notifications/preferences                    │
│  • PATCH /api/v/admin/notifications/preferences                  │
│  • Migration: admin_notification_preferences (aditiva)           │
└──────────────────────────────────────────────────────────────────┘
```

### File Map (Fase C)

#### Frontend new files (16)

```
resources/js/vue/pages/Admin/
├── Community.vue                                  [NEW]
└── NotificationsPreferences.vue                   [NEW] /admin/notifications

resources/js/vue/pages/Admin/community/
├── AdminPulseCrossCoachTab.vue                    [NEW]
├── AdminLiveFeedCommunityTab.vue                  [NEW]
├── AdminBroadcastCenterTab.vue                    [NEW]
├── AdminModerationQueueTab.vue                    [NEW]
└── AdminAnalyticsCoachTab.vue                     [NEW]

resources/js/vue/components/admin/community/
├── CoachAnalyticsTable.vue                        [NEW]
├── CoachSparkline.vue                             [NEW]
├── EngagementHeatmap.vue                          [NEW]
├── BroadcastPreviewBar.vue                        [NEW]
├── BroadcastHistoryList.vue                       [NEW]
├── ModerationReportCard.vue                       [NEW]
├── ModerationActionDialog.vue                     [NEW]
└── CoachAnalyticsKPIBar.vue                       [NEW]

resources/js/vue/composables/
├── useAdminCommunity.js                           [NEW]
├── useBroadcast.js                                [NEW]
└── useModerationQueue.js                          [NEW]
```

#### Frontend modified files (3)

```
resources/js/vue/components/ui/wellcore-admin/WcAdminSidebar.vue   # add item Comunidad + Notificaciones
resources/js/vue/layouts/AdminLayout.vue                            # add to MIGRATED_ROUTES
resources/js/vue/router/index.js                                    # add 2 routes
resources/js/vue/stores/auth.js                                     # imports + 3 reset calls
```

#### Backend new files (2)

```
app/Http/Controllers/Api/Admin/
├── CoachAnalyticsController.php                   [NEW] drill-down endpoint
└── NotificationPreferencesController.php          [NEW] admin prefs

database/migrations/
└── 2026_05_05_000010_create_admin_notification_preferences_table.php  [NEW]

app/Models/
└── AdminNotificationPreference.php                [NEW]
```

#### Backend modified files (3)

```
app/Http/Controllers/Api/Admin/CommunityController.php       # +pinPostAdminOverride + +makeGlobal
app/Http/Controllers/Api/Admin/BroadcastController.php       # validate + improved segmentation logic
app/Services/AdminCommunityService.php                       # +coachAnalytics method
routes/api.php                                                # 5 new routes
```

#### Tests new files (10)

```
tests/Unit/Composables/
├── useAdminCommunity.test.js
├── useBroadcast.test.js
└── useModerationQueue.test.js

tests/Feature/Admin/
├── CoachAnalyticsTest.php
├── BroadcastPreviewTest.php
├── BroadcastSendChunkedTest.php
├── ModerationQueueOrderingTest.php
├── AdminNotificationPreferencesTest.php
└── PostMakeGlobalTest.php

tests/Unit/Services/
└── AdminCommunityServiceCoachAnalyticsTest.php
```

---

## Information Architecture

### Sidebar admin (WcAdminSidebar.vue)

Add new entry in **Operaciones** section (or wherever admin sidebar groups things, replicating CoachLayout pattern):

```js
{
    label: 'Operaciones',
    items: [
        { name: 'Live Feed', to: '/admin/feed', ... },
        { name: 'Comunidad', to: '/admin/community', icon: 'community', isNew: true },  // NEW
        // existing items...
    ],
}
```

Add in `Personal` (or similar):

```js
{ name: 'Notificaciones', to: '/admin/notifications', icon: 'bell' }
```

### Tabs sticky en /admin/community

Same pattern as Coach Hub:

```
Desktop:
┌─────────────────────────────────────────────────────────────────┐
│ [Pulse Cross-Coach] [Live Feed] [Broadcast] [Moderation]        │
│ [Analytics Coach: María Pérez ▼]                                 │
└─────────────────────────────────────────────────────────────────┘
```

Mobile: scroll horizontal sticky.

URL hash sync: `/admin/community#pulse` ↔ `/admin/community#broadcast`. Analytics tab incluye coach context: `/admin/community#analytics-12` (coach_id 12 in URL).

Active tab badge: Moderation tab shows `[2]` count of pending reports (real-time).

---

## Tabs detalle

### Tab 1: Pulse Cross-Coach (default)

**Source**: `GET /api/v/admin/community/pulse-cross-coach?period=week|month|all` cache 300s.

**Shape data** (Fase A devuelve):

```json
{
  "coaches": [
    {
      "coach_id": 12, "coach_name": "Carlos Pérez", "avatar_url": "...",
      "active_clients_count": 27, "total_posts_count": 143,
      "engagement_rate": 0.62, "response_time_p50_min": 22, "response_time_p95_min": 180,
      "posts_per_day_30d": [3, 2, 5, 7, 4, ...],
      "alert": null  // or "no_activity_7d" | "client_spam" | "thread_conflict"
    }
  ],
  "time_series": { "posts_per_day": [...], "labels": [...] },
  "moderation_queue_count": 7,
  "totals": { "active_communities": 8, "posts_30d": 1284, "engagements_30d": 5680 }
}
```

**Layout**:

```
┌────────────────────────────────────────────────────────────────────────┐
│  KPI strip (4 cards):                                                  │
│  [8 comunidades] [1,284 posts 30d] [5,680 engag] [7 reportes pendientes]│
│                                                                        │
│  Series gráfica (Chart.js): posts/día últimos 30d                      │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │     /\        /\                                                │  │
│  │    /  \  /\  /  \                                              │  │
│  │  _/____\/__\/____\______                                       │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                        │
│  Tabla coaches × engagement (CoachAnalyticsTable):                     │
│  ┌──────┬─────────┬────────┬───────┬─────────┬──────────┬──────────┐  │
│  │Coach │Clientes │Posts   │ Engag │Resp p50 │Sparkline │Acciones  │  │
│  ├──────┼─────────┼────────┼───────┼─────────┼──────────┼──────────┤  │
│  │Carlos│27       │143     │62%    │22min    │~~~~/\~~  │[Drill] [⚠] │  │
│  │María │31       │89      │54%    │38min    │~~~__/~~  │[Drill]    │  │
│  │Luis  │8        │12      │30%    │240min   │~_______  │[Drill] [🔥7d] │  │
│  └──────┴─────────┴────────┴───────┴─────────┴──────────┴──────────┘  │
│                                                                        │
│  [Heatmap actividad cross-coach × hora]                                │
│  [Period selector: Semana | Mes | Todo]                                │
│  Refresca 300s · [↻ Actualizar]                                        │
└────────────────────────────────────────────────────────────────────────┘
```

**Components**:
- `<CoachAnalyticsKPIBar :totals="totals" :pending="moderation_queue_count" />`
- `<TimeSeriesChart :series="time_series" />` (Chart.js)
- `<CoachAnalyticsTable :coaches="coaches" @drill-down="onDrillDown" />`
- `<EngagementHeatmap :data="heatmap_data" />` (NEW backend endpoint or compute frontend from time_series)

**Drill-down click**: `@drill-down="(coachId) => switchTab('analytics', coachId)"` — switches Analytics tab + sets context.

**Alerts column** (rightmost): si `alert='no_activity_7d'` → ícono 🔥 amber tooltip "Sin actividad 7 días". Si `client_spam` → ⚠ rose. Si `thread_conflict` → 💥 púrpura.

**Real-time**: escucha `admin.community` event PostReported → flash badge moderation_queue_count en KPI strip + en tab Moderation.

### Tab 2: Live Feed Community (extender existing)

**Source**: `GET /api/v/admin/community/feed?coach_id=&type=&page=` (extender `AdminController::feed` con `?type=community` filter).

**Layout**: similar a `pages/Admin/LiveFeed.vue` existente pero filtrado a `type='community'`. Sin reinventar — reutilizar component `LiveFeedRow.vue` si existe.

**Filtros nuevos**:
- Selector coach (dropdown con todos los coaches activos)
- Filter post type: all / achievement / pr / photo / text
- Filter mod status: all / official / pinned / reported

**Real-time**: events `community-post-created` y `coach-community-activity` desde `admin.community` channel.

**Empty state**: "Sin actividad de comunidad este filtro. Cambia los filtros o espera el próximo evento."

### Tab 3: Broadcast Center

**Source**: combinación de:
- POST `/api/v/admin/broadcast/preview` (live debounced)
- POST `/api/v/admin/broadcast/send`
- GET `/api/v/admin/broadcast/history`

**Layout**:

```
┌────────────────────────────────────────────────────────────┐
│  ┌─────────── Composer ──────────────┐ ┌── History ──┐    │
│  │ Audience: [● Clientes] [○ Coaches]│ │  Últimos 50 │    │
│  │           [○ All Communities]      │ │             │    │
│  │                                    │ │ ┌─────────┐ │    │
│  │ Segmentación:                      │ │ │Coach D  │ │    │
│  │  Plan: [▼ todos]                   │ │ │27 clien │ │    │
│  │  Status: [▼ activo]                │ │ │ 1d ago  │ │    │
│  │  Coach asignado: [▼]               │ │ └─────────┘ │    │
│  │  Inactivo +N días: [___]           │ │ ┌─────────┐ │    │
│  │                                    │ │ │ ...     │ │    │
│  │ Asunto: [_____________]            │ │ └─────────┘ │    │
│  │ Mensaje:                           │ │             │    │
│  │ [_________________________]        │ │  [Ver más]  │    │
│  │                                    │ │             │    │
│  │ [✓] Push notification              │ └─────────────┘    │
│  │                                    │                    │
│  │ Recipientes: 27 clientes activos   │                    │
│  │ con plan Método y status activo    │                    │
│  │                                    │                    │
│  │ [Vista previa] [Enviar broadcast]  │                    │
│  └────────────────────────────────────┘                    │
└────────────────────────────────────────────────────────────┘
```

**Composer**:
- Audience selector toggle. State separado per audience type.
- Segmentation: dropdown plan, status, coach, días inactivos. State updates → debounced 500ms previewCount.
- Subject (opcional, max 255).
- Body (required, max 2000).
- Push toggle (default false). Si true muestra "También enviará push notification" indicator.
- "Enviar broadcast" → confirmation modal si recipients > 50.

**History panel**:
- Lista paginada lado derecho.
- Cada row: sender (badge admin/coach), audience, recipients_count, sent_at human ("hace 2h"), filter badge (segmento).
- Click row → expand detalle: subject + body + delivered_count + breakdown.
- Filter por sender_type: all / admin / coach.

**Backend Fase C extension**: BroadcastController existing `send()` valida más rigurosamente (subject max 255, body max 2000, audience required, etc.).

### Tab 4: Moderation Queue

**Source**: `GET /api/v/admin/community/moderation/queue?status=pending&page=`.

**Shape**:

```json
{
  "data": [
    {
      "report_id": 22,
      "post_id": 1234,
      "post_excerpt": "Contenido reportado...",
      "post_author_name": "Cliente X",
      "coach_name": "Coach D",
      "coach_id": 12,
      "reason": "offensive",
      "reason_detail": "Texto explicando",
      "report_count": 3,
      "reporters": [{ "client_id": 5, "client_name": "..." }, ...],
      "created_at": "2026-05-05T...",
      "urgency_score": 92  // computed: report_count * 20 + recent_factor
    }
  ],
  "pagination": {...}
}
```

**Layout**:

```
┌──────────────────────────────────────────────────────────────────┐
│ Filter: [Todos] [Multi-reporte] [Recientes] [Por coach: ▼]       │
│                                                                  │
│ ┌──────────────────────────────────────────────────────────────┐ │
│ │ ⚠️ 3 reportes · Cliente X (Coach D) · hace 2h                │ │
│ │ "Contenido reportado..."                                     │ │
│ │ Reason: offensive · Detail: "Texto explicando"               │ │
│ │ Reporters: [👤 Ana] [👤 Luis] [👤 Pedro]                     │ │
│ │ [Ver thread completo ▼]                                      │ │
│ │                                                              │ │
│ │ Acciones: [Dismiss] [Hide] [Ban cliente] [Warn]              │ │
│ └──────────────────────────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────────────────────────┐ │
│ │ 1 reporte · Cliente Y (Coach M) · hace 5h                    │ │
│ │ "Spam de promo"                                              │ │
│ │ Reason: spam                                                 │ │
│ │ [Ver thread] [Dismiss] [Hide]                                │ │
│ └──────────────────────────────────────────────────────────────┘ │
│ ...                                                              │
└──────────────────────────────────────────────────────────────────┘
```

**Component**: `<ModerationReportCard :report="r" @action="handleAction" @expand="loadThread" />`.

**Expansion**: click "Ver thread completo" → fetch `GET /api/v/admin/community/posts/{post_id}/thread` (extends existing endpoint with admin scope). Modal o drawer.

**Action dialog** (`ModerationActionDialog.vue`): confirma con razón optional + impact warning.
- "Dismiss": cierra report sin acción. Audit `dismiss_report`.
- "Hide": post `visible=false`. Cliente original recibe email opcional. Audit `hide_for_review`.
- "Ban cliente": `clients.status='suspendido'` por X días (default 7). Audit `ban_client`.
- "Warn": email warn + flag en cliente. Audit `warn_client`.

**Real-time**: nuevo PostReport aparece arriba con animación slide-down + sound (opcional, debe ser opt-in en preferences) + flash KPI count.

### Tab 5: Analytics Coach (drill-down)

**Source**: `GET /api/v/admin/community/coaches/{coach_id}/analytics` cache 600s.

**Shape**:

```json
{
  "coach": { "id": 12, "name": "Carlos Pérez", "avatar_url": "...", "joined_at": "...", "role": "coach" },
  "kpis": {
    "active_clients": 27, "total_posts_30d": 143, "engagement_rate": 0.62,
    "response_time_p50_min": 22, "response_time_p95_min": 180,
    "moderation_actions_30d": 4, "broadcasts_sent_30d": 2
  },
  "posts_per_day_90d": [...],  // 90 days array
  "engagement_per_day_90d": [...],
  "top_clients": [
    { "client_id": 5, "client_name": "Ana", "posts": 28, "engagement_received": 142 }
  ],
  "alerts": [
    { "type": "client_inactive", "client_id": 8, "client_name": "Pedro", "days": 12 }
  ],
  "recent_audit": [
    { "action_type": "pin", "target_id": 1234, "created_at": "...", "reason": "..." }
  ]
}
```

**Empty state** (no coach selected): "Selecciona un coach desde la tabla Pulse Cross-Coach para ver el análisis detallado." con CTA "Ir a Pulse Cross-Coach".

**Layout** (con coach selected):

```
┌──────────────────────────────────────────────────────────────────┐
│ ← Back to Pulse Cross-Coach                                      │
│                                                                  │
│ ┌─ Header coach ────────────────────────────────────────────┐    │
│ │ 👤 Carlos Pérez · Coach desde abr 2026                    │    │
│ │ [Mensaje al coach] [Impersonar] [Ver community pública]   │    │
│ └────────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─ KPI bar (CoachAnalyticsKPIBar) ──────────────────────────┐    │
│ │ 27 clientes | 143 posts 30d | 62% engagement | 22min p50 │    │
│ └────────────────────────────────────────────────────────────┘    │
│                                                                  │
│ Sub-secciones:                                                   │
│ [Overview] [Posts] [Engagement] [Clients] [Audit Trail]          │
│                                                                  │
│ Overview (default):                                              │
│  • Time series posts/día 90d (Chart.js)                          │
│  • Time series engagement 90d                                    │
│  • Lista alertas                                                 │
│                                                                  │
│ Posts: lista todos sus community posts con filtros               │
│ Engagement: heatmap + breakdown reactions/comments               │
│ Clients: tabla top contributors + at-risk                        │
│ Audit Trail: ModerationAction log filtered actor=this coach      │
└──────────────────────────────────────────────────────────────────┘
```

**Action "Mensaje al coach"**: opens admin → coach broadcast modal pre-populated. Reuses `AdminBroadcastModal` (subset de Tab Broadcast Center).

**Action "Impersonar"**: usa `useImpersonation` composable existente con `type='admin'` para superadmin → coach. Redirige a `/coach/community` impersonando.

---

## Modal Admin Broadcast (en tab, no separate)

Composer es inline en tab. Confirmation step si recipients > 50:

```
⚠️ Vas a enviar a 247 clientes activos

  - 152 con plan RISE
  - 45 con plan Método
  - 50 con plan Esencial

  Push notification: ✓ activado

  ¿Confirmar envío?

  [Cancelar]  [Sí, enviar broadcast]
```

---

## Composables (3 nuevos)

### useAdminCommunity.js

```js
const pulseCache = new Map(); // period → { data, timestamp }
const PULSE_TTL_MS = 60_000; // 1min frontend (300s backend)
const coachAnalyticsCache = new Map(); // coachId → { data, timestamp }
const COACH_ANALYTICS_TTL_MS = 120_000;
const promises = new Map();

export function useAdminCommunity() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    async function fetchPulseCrossCoach({ period = 'week', force = false } = {}) {
        const key = `pulse:${period}`;
        if (!force && pulseCache.has(period)) {
            const cached = pulseCache.get(period);
            if (Date.now() - cached.timestamp < PULSE_TTL_MS) return cached.data;
        }
        if (promises.has(key)) return promises.get(key);

        loading.value = true;
        const promise = (async () => {
            try {
                const res = await api.get('/api/v/admin/community/pulse-cross-coach', { params: { period } });
                pulseCache.set(period, { data: res.data, timestamp: Date.now() });
                return res.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'Pulse cross-coach failed.';
                if (err.response?.status >= 500 || !err.response) {
                    console.error('[useAdminCommunity] pulse failed', err);
                }
                return null;
            } finally {
                loading.value = false;
                promises.delete(key);
            }
        })();
        promises.set(key, promise);
        return promise;
    }

    async function fetchCoachAnalytics(coachId, { force = false } = {}) {
        const key = `coach:${coachId}`;
        if (!force && coachAnalyticsCache.has(coachId)) {
            const c = coachAnalyticsCache.get(coachId);
            if (Date.now() - c.timestamp < COACH_ANALYTICS_TTL_MS) return c.data;
        }
        if (promises.has(key)) return promises.get(key);

        loading.value = true;
        const promise = (async () => {
            try {
                const res = await api.get(`/api/v/admin/community/coaches/${coachId}/analytics`);
                coachAnalyticsCache.set(coachId, { data: res.data, timestamp: Date.now() });
                return res.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'Coach analytics failed.';
                return null;
            } finally {
                loading.value = false;
                promises.delete(key);
            }
        })();
        promises.set(key, promise);
        return promise;
    }

    async function fetchCommunityFeed({ coachId = null, type = null, page = 1 } = {}) {
        const params = { page };
        if (coachId) params.coach_id = coachId;
        if (type) params.type = type;
        try {
            const res = await api.get('/api/v/admin/community/feed', { params });
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Feed failed.';
            return null;
        }
    }

    return { loading, error, fetchPulseCrossCoach, fetchCoachAnalytics, fetchCommunityFeed };
}

export function resetAdminCommunity() {
    pulseCache.clear();
    coachAnalyticsCache.clear();
    promises.clear();
}
```

### useBroadcast.js

```js
const previewCache = new Map();
const PREVIEW_TTL_MS = 30_000;

const isOpen = ref(false); // for inline composer state
const audience = ref('clients'); // 'clients' | 'coaches' | 'all_communities'
const segment = ref({ plan: null, status: ['activo'], coach_id: null, inactive_days: null });
const subject = ref('');
const body = ref('');
const pushEnabled = ref(false);
const recipientCount = ref(null);
const sending = ref(false);
const history = ref([]);

export function useBroadcast() {
    const api = useApi();

    async function previewCount() {
        const params = { audience: audience.value, ...segment.value };
        const key = JSON.stringify(params);
        if (previewCache.has(key)) {
            const c = previewCache.get(key);
            if (Date.now() - c.timestamp < PREVIEW_TTL_MS) {
                recipientCount.value = c.count;
                return c.count;
            }
        }
        try {
            const res = await api.post('/api/v/admin/broadcast/preview', params);
            const count = res.data?.count ?? 0;
            previewCache.set(key, { count, timestamp: Date.now() });
            recipientCount.value = count;
            return count;
        } catch (err) {
            console.error('[useBroadcast] preview failed', err);
            return 0;
        }
    }

    async function send() {
        sending.value = true;
        try {
            const payload = {
                audience: audience.value,
                segment: segment.value,
                subject: subject.value,
                body: body.value,
                push_enabled: pushEnabled.value,
            };
            const res = await api.post('/api/v/admin/broadcast/send', payload);
            // reset composer
            subject.value = '';
            body.value = '';
            pushEnabled.value = false;
            return res.data;
        } finally {
            sending.value = false;
        }
    }

    async function fetchHistory({ page = 1, senderType = null } = {}) {
        const params = { page };
        if (senderType) params.sender_type = senderType;
        try {
            const res = await api.get('/api/v/admin/broadcast/history', { params });
            history.value = res.data?.data || res.data?.history || [];
            return res.data;
        } catch (err) {
            console.error('[useBroadcast] history failed', err);
            return null;
        }
    }

    return { isOpen, audience, segment, subject, body, pushEnabled, recipientCount, sending, history, previewCount, send, fetchHistory };
}

export function resetBroadcast() {
    previewCache.clear();
    audience.value = 'clients';
    segment.value = { plan: null, status: ['activo'], coach_id: null, inactive_days: null };
    subject.value = '';
    body.value = '';
    pushEnabled.value = false;
    recipientCount.value = null;
    history.value = [];
}
```

### useModerationQueue.js

```js
const queueCache = ref(null);
const queueLoadedAt = ref(0);
const QUEUE_TTL_MS = 30_000;

export function useModerationQueue() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);
    const queue = queueCache;
    const pendingCount = computed(() => queue.value?.data?.length || 0);

    async function fetchQueue({ force = false } = {}) {
        if (!force && queue.value && Date.now() - queueLoadedAt.value < QUEUE_TTL_MS) return queue.value;
        loading.value = true;
        try {
            const res = await api.get('/api/v/admin/community/moderation/queue');
            queue.value = res.data;
            queueLoadedAt.value = Date.now();
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Queue failed.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function dismissReport(reportId) {
        const res = await api.post(`/api/v/admin/community/moderation/${reportId}/dismiss`);
        await fetchQueue({ force: true });
        return res.data;
    }

    async function actionReport(reportId, action, reason = null) {
        const res = await api.post(`/api/v/admin/community/moderation/${reportId}/action`, { action, reason });
        await fetchQueue({ force: true });
        return res.data;
    }

    return { queue, pendingCount, loading, error, fetchQueue, dismissReport, actionReport };
}

export function resetModerationQueue() {
    queueCache.value = null;
    queueLoadedAt.value = 0;
}
```

---

## Backend extensions

### AdminCommunityService::coachAnalytics

Nuevo método en `app/Services/AdminCommunityService.php`. Calcula 90-day metrics + KPIs + alerts + audit recent.

```php
public function coachAnalytics(int $coachId): array
{
    $coach = Admin::findOrFail($coachId);
    $clientIds = app(CoachCommunityService::class)->resolveClientIds($coachId);

    $kpis = [
        'active_clients' => Client::whereIn('id', $clientIds)->where('status', 'activo')->count(),
        'total_posts_30d' => CommunityPost::whereIn('client_id', $clientIds)
            ->where('created_at', '>=', now()->subDays(30))->count(),
        'engagement_rate' => $this->engagementRate30d($clientIds),
        'response_time_p50_min' => $this->responseTimePercentile($coachId, 50),
        'response_time_p95_min' => $this->responseTimePercentile($coachId, 95),
        'moderation_actions_30d' => ModerationAction::byActor('coach', $coachId)
            ->where('created_at', '>=', now()->subDays(30))->count(),
        'broadcasts_sent_30d' => BroadcastMessage::where('sender_type', 'coach')
            ->where('sender_id', $coachId)->where('sent_at', '>=', now()->subDays(30))->count(),
    ];

    $postsPerDay = $this->seriesPostsPerDay($clientIds, 90);
    $engagementPerDay = $this->seriesEngagementPerDay($clientIds, 90);
    $topClients = $this->topClientsForCoach($clientIds, 30);
    $alerts = $this->coachAlerts($coachId, $clientIds);
    $recentAudit = ModerationAction::byActor('coach', $coachId)
        ->orderByDesc('created_at')->limit(10)->get()->toArray();

    return [
        'coach' => [
            'id' => $coach->id,
            'name' => $coach->name,
            'avatar_url' => $coach->avatar_url ?? null,
            'joined_at' => $coach->created_at?->toIso8601String(),
            'role' => $coach->role instanceof \BackedEnum ? $coach->role->value : $coach->role,
        ],
        'kpis' => $kpis,
        'posts_per_day_90d' => $postsPerDay,
        'engagement_per_day_90d' => $engagementPerDay,
        'top_clients' => $topClients,
        'alerts' => $alerts,
        'recent_audit' => $recentAudit,
    ];
}

// Helpers privados:
private function engagementRate30d(array $clientIds): float { /* posts vs reactions+comments ratio */ }
private function responseTimePercentile(int $coachId, int $percentile): int { /* median min between client post → coach comment */ }
private function seriesPostsPerDay(array $clientIds, int $days): array { /* group by date */ }
private function seriesEngagementPerDay(array $clientIds, int $days): array { /* group by date */ }
private function topClientsForCoach(array $clientIds, int $days): array { /* top contributors */ }
private function coachAlerts(int $coachId, array $clientIds): array { /* compute current alerts */ }
```

### CoachAnalyticsController

```php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminCommunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CoachAnalyticsController extends Controller
{
    public function __construct(private AdminCommunityService $service) {}

    public function show(Request $request, int $coachId)
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $payload = Cache::remember(
            "wc:admin-coach-analytics:v1:{$coachId}",
            ttl: 600,
            callback: fn () => $this->service->coachAnalytics($coachId)
        );

        return response()->json($payload);
    }

    private function isAdmin(mixed $user): bool
    {
        if (! $user instanceof \App\Models\Admin) return false;
        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
        return in_array($role, ['admin', 'superadmin', 'jefe'], true);
    }
}
```

### Migration `admin_notification_preferences`

```php
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('admin_notification_preferences')) return;

        Schema::create('admin_notification_preferences', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->primary();
            $table->boolean('notify_post_reported')->default(true);
            $table->boolean('notify_coach_no_activity_7d')->default(true);
            $table->boolean('notify_thread_conflict')->default(true);
            $table->boolean('notify_broadcast_sent')->default(false);
            $table->boolean('notify_client_spam')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void { Schema::dropIfExists('admin_notification_preferences'); }
};
```

### NotificationPreferencesController (admin)

Mirror del coach: GET + PATCH on `/api/v/admin/notifications/preferences`.

### routes/api.php — agregar

```php
// Admin community extensions
Route::middleware(['wellcore.auth'])->prefix('v/admin')->group(function () {
    Route::get('community/coaches/{coach}/analytics', [Admin\CoachAnalyticsController::class, 'show']);
    Route::get('community/feed', [Admin\CommunityController::class, 'feed']); // si no existe
    Route::post('community/posts/{post}/pin', [Admin\CommunityController::class, 'pinAdminOverride']);
    Route::post('community/posts/{post}/make-global', [Admin\CommunityController::class, 'makeGlobal']);
    Route::get('notifications/preferences', [Admin\NotificationPreferencesController::class, 'show']);
    Route::patch('notifications/preferences', [Admin\NotificationPreferencesController::class, 'update']);
});
```

---

## Real-time strategy

### Channel subscription en Community.vue admin

```js
onMounted(() => {
    if (!window.Echo) return;
    adminChannel = window.Echo.private('admin.community')
        .listen('.post-reported', () => {
            moderationQueue.fetchQueue({ force: true });
            // Flash badge in tab Moderation
            window.dispatchEvent(new CustomEvent('admin-community:flash', { detail: 'moderation' }));
        })
        .listen('.broadcast-sent', () => {
            broadcast.fetchHistory();
        })
        .listen('.post-made-official', () => {
            adminCommunity.fetchPulseCrossCoach({ force: true });
        });
});
```

### Tab badges real-time

Tab "Moderation" muestra badge `[N]` con `pendingCount` reactivo de `useModerationQueue`. Cuando cambia → tab pulse animation.

---

## Animations + Interactions

- Tab transitions: igual patrón Coach (direction-aware slide).
- KPI count-up entry: `useCountUp` por cada métrica.
- Sparklines: SVG path animado entry stroke-dashoffset 0 → full.
- Heatmap cells: stagger fade-in.
- Moderation card new entry: slide-down con flash bg pulse 800ms.
- Broadcast send success: confetti `useConfetti` (composable existente) + haptic success.
- Reduced motion respetado: `useReducedMotion`.

---

## Privacy & Authorization

- Vista `/admin/community` requires `meta.auth=true` + role ∈ `['admin', 'superadmin', 'jefe']`. Backend enforces — frontend trust.
- `AdminCommunityService::coachAnalytics` acepta cualquier coachId (admin override). Respeta scope: solo retorna data de community, no PII sensibles.
- Coach impersonation desde Analytics tab: usa `useImpersonation` existing — superadmin only enforced by API.
- Audit: TODA acción admin logged en `moderation_actions` con `actor_type='admin'`.

---

## Testing Strategy

### Backend (Pest, ~12 tests)

- `tests/Feature/Admin/CoachAnalyticsTest.php`
  - returns shape with all KPIs + 90d series
  - caches with 600s TTL
  - rejects non-admin
  - handles coach without clients (empty arrays)
- `tests/Feature/Admin/BroadcastPreviewTest.php`
  - count matches segmentation
  - no creates BroadcastMessage row
- `tests/Feature/Admin/BroadcastSendChunkedTest.php`
  - 200+ recipients chunked correctly
  - delivered_count <= recipients_count
- `tests/Feature/Admin/ModerationQueueOrderingTest.php`
  - multi-reportes urgency_score higher
  - ordered desc by urgency
- `tests/Feature/Admin/AdminNotificationPreferencesTest.php`
  - GET defaults if no row
  - PATCH persists granular
- `tests/Feature/Admin/PostMakeGlobalTest.php`
  - creates global post visible in all communities
  - audit log entry
  - rejects non-superadmin
- `tests/Unit/Services/AdminCommunityServiceCoachAnalyticsTest.php`
  - response_time_p50 calculation
  - engagement_rate calculation
  - alerts generation logic

### Frontend (Vitest, ~6 tests)

- `useAdminCommunity.test.js`: pulse caches per period, coach analytics caches per coachId, reset clears
- `useBroadcast.test.js`: previewCount caches, send resets composer, fetchHistory populates
- `useModerationQueue.test.js`: pendingCount computed reactively, dismissReport refreshes queue

### E2E manual

Smoke checklist al final del plan (Task 30 equivalente).

---

## Definition of Done — Fase C

### Frontend
- [ ] Community.vue admin con 5 tabs sticky
- [ ] 5 tab components implementados
- [ ] 8 components compartidos (Table, Sparkline, Heatmap, KPIBar, History, ReportCard, ActionDialog, PreviewBar)
- [ ] 3 composables singleton con TTL + dedup + reset
- [ ] NotificationsPreferences admin page
- [ ] Sidebar item "Comunidad" en WcAdminSidebar + entry en MIGRATED_ROUTES
- [ ] Router `/admin/community` + `/admin/notifications`
- [ ] auth.js extendido con 3 reset calls (admin community)
- [ ] Real-time admin.community channel subscribed
- [ ] Drill-down Pulse → Analytics Coach con context preservation
- [ ] Charts Chart.js bundle <30KB
- [ ] Animations + reduced-motion respetados
- [ ] Dark-only (admin atmosphere) coherente

### Backend
- [ ] AdminCommunityService::coachAnalytics + 6 helpers privados
- [ ] CoachAnalyticsController con cache 600s
- [ ] AdminCommunityController extensions: pinAdminOverride, makeGlobal
- [ ] NotificationPreferencesController admin
- [ ] Migration admin_notification_preferences (aditiva con guards)
- [ ] AdminNotificationPreference model
- [ ] 5 routes nuevas registradas
- [ ] BroadcastController: validation rigurosa subject<=255 body<=2000

### Testing
- [ ] 12 Pest tests verde
- [ ] 6 Vitest tests verde
- [ ] No regresión Pest suite completa
- [ ] Pint OK + ESLint OK
- [ ] Smoke E2E manual: 14 scenarios PASS

### Operations
- [ ] Cache `wc:admin-coach-analytics:v1:{id}` 600s
- [ ] Lighthouse Performance ≥ 70 en /admin/community
- [ ] Console clean

### Documentación
- [ ] Spec doc commiteado
- [ ] Plan doc commiteado
- [ ] CLAUDE.md actualizado con sección Fase C

---

## Risks & Mitigations

| Risk | Severity | Mitigation |
|------|----------|------------|
| Queries pesadas Pulse Cross-Coach con 30+ coaches | Alto | Cache 300s + precompute scheduled diario al inicio |
| Chart.js bundle infla SPA | Medio | Lazy import per-component, tree-shake unused features |
| Broadcast send a 500+ saturates queue | Alto | Chunks 100 + queue Laravel + BroadcastSent listener async |
| Moderation action incorrecta (admin baneo wrong client) | Alto | Confirmation dialog with client name + impact warning |
| Coach analytics drill-down con coach sin data | Bajo | Empty states per sub-section + alerts tab vacío handled |
| Real-time channel admin con 100+ events/min satura UI | Medio | Debounce flashes 1s + max 5 visible toasts |
| Composable cache leak entre admin sessions | Alto | Reset en auth.setAuth/clearAuth |

---

## What's NOT in scope (intentional)

- Mentions cross-role autocomplete (Fase D)
- Threads cross-role badges en cliente view (Fase D)
- Notifications general preferences (cliente + coach + admin unified) (Fase D)
- Admin-to-coach 1-on-1 messaging UI (existe parcial en CoachManagement, no expandir)
- Public reports dashboard (B2B, fase 5+)
- Scheduled broadcasts (set "send at" time) — UX premium pero no crítico Fase C
- Broadcast templates library — no validado need

---

## Self-Review

**1. Placeholder scan:** sin TBDs. Comments en backend services helpers `/* ... */` son intencionalmente abstracciones — el plan llenará con SQL.

**2. Internal consistency:**
- 5 tabs matchean 5 sub-tab components ✅
- 3 composables matchean 3 reset hooks en auth.js ✅
- Backend new endpoints matchean controller methods + routes ✅
- Drill-down flow Pulse Cross-Coach → Analytics Coach con coachId preservation ✅

**3. Scope check:** Fase C es similar a B en magnitud (~25-30 tasks). Independent de Fase B (paralelizable).

**4. Ambiguity check:**
- Cache TTL específicos (300s pulse, 600s coach analytics, 30s queue, 30s broadcast preview)
- Confirmation threshold 50 broadcasts (vs 20 coach announce — admin tiene más blast radius)
- Tab order explícito
- Default landing tab Pulse Cross-Coach explícito

**5. Premium quality check:**
- Sparklines, KPI count-up, charts, heatmap ✅
- Real-time admin.community + tab badges ✅
- Drill-down con context preservation ✅
- Confirmation gates en actions críticas ✅
- Audit log integration ✅
- Coach impersonation reuse existing flow ✅

**6. Backend extensions Fase C clarificadas:**
- `coachAnalytics()` + 6 helpers (con SQL pendiente en plan)
- `pinAdminOverride` + `makeGlobal` controller actions
- Migration aditiva `admin_notification_preferences`
- 5 endpoints nuevos

Spec listo para transición a writing-plans.
