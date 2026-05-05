# Community Cross-Role — Fase B: Coach Community Hub Design

> **Status:** Awaiting user review before transition to writing-plans
> **Author:** Daniel Esparza + Claude Opus 4.7
> **Date:** 2026-05-05
> **Mode:** Autonomous, premium UX/UI, scope intentional from spec original
> **Predecessor:** Fase A backend foundations (16 endpoints + 9 migrations + 6 events) → ya en `feat/community-cross-role-fase-a` (48 commits)
> **Successor:** Fase C Admin Community Center (depende solo de Fase A backend, paralelizable con Fase B)

---

## Goal

Construir la **interfaz Vue 3 SPA** que el coach usa diariamente para entender, moderar y participar en la comunidad de SUS clientes. La Fase A dejó listos los endpoints REST + Reverb channels + cache strategy. Fase B levanta sobre ellos un hub visual premium con calidad equivalente al Dashboard cliente (`pages/Client/Dashboard.vue`) y un nivel de detalle UX/UI superior — animaciones físicas, optimistic UI, real-time inteligente, mobile-first responsive, dark/light theme coherente, latino neutro.

Al final de Fase B el coach tiene:

- Página `/coach/community` con 5 sub-tabs operacionales (Latido del Equipo / Posts / Conversaciones / Pulsos / Logros)
- Acceso prominente desde sidebar (nueva sección "Comunidad")
- Botón flotante "Mensaje al equipo" → modal con dos modos (anuncio in-feed vs push)
- Push notifications browser nativas con UX no-intrusiva
- Real-time updates inteligentes (auto-prepend cerca del top, toast flotante si no)
- Actions de moderación con optimistic UI + revert + audit trail
- Empty states + loading skeletons + error boundaries específicos por tab
- Onboarding tour de 4 pasos en primera visita (skippable)

---

## Estado actual (post-Fase A, 2026-05-05)

### Backend ya disponible (consumido por Fase B)

```
Endpoints REST:
  GET  /api/v/coach/community/pulse          → team_health_score, top_performers, at_risk_clients, computed_at
  GET  /api/v/coach/community/posts          → posts paginados con filter all|pinned|reported|achievements|prs
  GET  /api/v/coach/community/pulsos         → pulsos activos del equipo (24-48h)
  POST /api/v/coach/community/announce       → 501 actualmente — IMPLEMENTAR EN FASE B
  POST /api/v/coach/posts/{id}/pin           → pin con hours + note
  POST /api/v/coach/posts/{id}/unpin
  POST /api/v/coach/posts/{id}/make-official → coach pick + author_type=coach
  DELETE /api/v/coach/posts/{id}             → soft delete + reason

Reverb channels:
  coach.{coachId}.community  → events: PostPinned, PostMadeOfficial, CoachCommunityActivity, PostReported
  user.coach.{coachId}       → events: MentionCreated (cross-role layer)

Cache:
  wc:coach-pulse:v1:{coach_id}            → 60s TTL backend, 25s TTL frontend (in-flight dedup)
  wc:coach-community-feed:v1:{id}:{filter}:{page}  → 30s TTL (lazy pattern; Fase B no lo cachea client-side)

Models nuevos disponibles:
  PinnedPost, PostReport, ModerationAction, BroadcastMessage,
  CoachNotificationPreference, PostMention

Models extendidos:
  CommunityPost.author_type ('client'|'coach'|'admin')
  CommunityPost.author_admin_id (nullable)
  CommunityPost.is_official (boolean)
  CommunityPost.is_global (boolean)
  PostComment.author_type
  PostComment.author_admin_id

Tabla coach_push_subscriptions ya creada con índices uq_coach_endpoint + idx_coach_subs.
```

### Lo que Fase B debe construir (gaps frontend)

- **Vue page hub**: `resources/js/vue/pages/Coach/Community.vue` (no existe — la ruta `/coach/community` no está registrada en router)
- **5 sub-tab components** en `resources/js/vue/pages/Coach/community/`
- **3 composables** singleton pattern: `useCoachCommunity`, `useCoachPulse`, `useModeration`
- **Modal "Mensaje al equipo"**: `CoachAnnounceModal.vue`
- **Components compartidos**: `CoachBadge`, `OfficialBadge`, `PinnedIndicator`, `TeamHealthRing`, `TopPerformerCard`, `AtRiskClientChip`, `PushPermissionBanner`, `CoachCommunityTour`
- **Sidebar update**: agregar item "Comunidad" en `CoachLayout.vue` + ícono SVG
- **Router update**: registrar `/coach/community` con `meta.auth=true`
- **Auth store update**: hooks de invalidate cache para nuevos composables (paralelo a `resetGroupPulse`)
- **Backend `announce` endpoint**: convertir 501 → implementación funcional (post oficial + opcional push batch)
- **Scheduled command**: `app/Console/Commands/PrecomputeCoachPulse.php` cada 5min para coaches con clientes activos
- **Push subscription UI**: banner inline + endpoint `POST /api/v/coach/push/subscribe` + endpoint `DELETE /api/v/coach/push/subscribe/{id}`
- **Notifications preferences page**: `/coach/notifications` con toggles granulares (extender ruta nueva)

---

## Decisiones de diseño (autónomas, premium)

| # | Decisión | Justificación |
|---|---------|---------------|
| 1 | Sidebar "Comunidad" como **sección nueva propia** entre Principal y Gestión, no dentro de Principal | Le da peso visual y claridad de IA. Es un módulo nuevo de engagement social, no un tool diario rutinario como Mensajes. Sección con un solo item es válido (igual que "Personal" → solo Notas) |
| 2 | Default landing tab = **Latido del Equipo** | Intelligence-first: cuando coach abre, primer instinto es "¿cómo está mi equipo hoy?". Si hay clientes en riesgo, salta a la vista. Posts queda a un click |
| 3 | Tab order: **Latido → Posts → Conversaciones → Pulsos → Logros** | Tier-based: Tier 1 intelligence (Latido), Tier 2 action+depth (Posts, Conversaciones), Tier 3 engagement (Pulsos, Logros). Mismo orden que spec original |
| 4 | Sub-tabs en **mobile**: scroll horizontal con sticky header (no dropdown) | Patrón Twitter/Instagram, mejor para discovery. Dropdown esconde features y reduce engagement |
| 5 | Bottom-nav móvil: **NO añadir slot** para Comunidad | Solo 4 slots + FAB. Coach accede vía hamburger sidebar. Trade-off acceptable: Comunidad es 2-3x/día, no 10x/día como Check-ins |
| 6 | FAB sheet móvil: **agregar 4ta opción** "Mensaje al equipo" | Discoverability sin ocupar bottom nav. Activa el modal `CoachAnnounceModal` directamente |
| 7 | Real-time strategy: **threshold-based prepend vs toast** | Si scroll en top 200px, auto-prepend con animación slide-down. Si lejos, toast flotante "X nuevos posts" con click-to-scroll-up. UX matches Twitter/Instagram, evita pérdida de scroll position |
| 8 | Push permission UX: **banner inline discreto** (no modal pop-up) | Modal pop-up se rechaza por reflex. Banner inline al top del Latido tab respeta el flujo. Botón "Activar ahora" o "Más tarde" (recordar 7 días localStorage) |
| 9 | Notifications preferences: **ruta nueva `/coach/notifications`** (no en Profile) | Separación de concerns: Profile es identidad/marca, Notifications es config técnica. Permite linkear directo desde push permission flow |
| 10 | Modal "Mensaje al equipo": **2-modos toggle** (Anuncio | Push) con preview live + recipient count | Coach decide si quiere fricción social (anuncio in-feed) vs notificación intrusiva (push). Recipients count en vivo evita anuncios sorpresa |
| 11 | Composables: **5 singleton module-scope** con TTL + Promise dedup + reset hooks | Replica gold standard `useGroupPulse.js`. Garantiza zero duplicate requests entre cards en mismo page load |
| 12 | Animations: **useStaggerIn + useHaptics + spring transitions** | Replica calidad del Dashboard cliente. Stagger entry premium feel. Haptic feedback en moderation actions = táctil profesional |
| 13 | Loading states: **skeleton específico por tab**, no genérico | Latido = ring + 3 cards skeleton. Posts = 3 post cards skeleton shimmer. Pulsos = grid circles. Empty state con illustration + CTA |
| 14 | Optimistic UI en moderation: pin/unpin/delete/official inmediatos en feed con revert si falla | Sensación de respuesta instantánea. Revert con toast + haptic error si servidor rechaza |
| 15 | Vitest tests: **SÍ para 5 composables clave** (happy + error + reset hook) | Testing es trivial con composables singleton bien diseñados. Catch regresiones de impersonation cache leak |
| 16 | Onboarding tour: **4 pasos** primera visita (Skippable, localStorage `coach_community_tour_seen`) | Patrón coach-onboarding-tour ya existe. Replicar reduce learning curve |
| 17 | Theme: **dark + light coherente** desde día uno | Memory `feedback_ds_v1_light_theme.md`: cada fase debe configurar light coherente. No dejar tokens dark-only |
| 18 | Copy: **latino neutro** (tú/puedes), nunca peninsular ni voseo | Memory `feedback_idioma_latino_neutro.md` |
| 19 | Audit logging: extiende `moderation_actions` con `action_type='announce'` cuando coach manda mensaje al equipo | Audit trail completo. Admin lo verá en Fase C |
| 20 | Implementación `announce` endpoint en backend Fase B (no Fase A) | Endpoint actualmente 501. Razón: requiere ModerationService.makeOfficial + opcional PushNotificationService.batch + BroadcastMessage row + BroadcastSent event |

---

## Architecture

```
┌────────────────────────────────────────────────────────────────────────┐
│                       /coach/community  (route)                        │
│                                                                        │
│  ┌──────────────────────────────────────────────────────────────┐      │
│  │  CoachLayout.vue (sidebar + topbar + bottom nav)             │      │
│  │  └── slot ─→ Community.vue (page hub)                        │      │
│  │             ├── PushPermissionBanner.vue (conditional)       │      │
│  │             ├── TabsHeader (5 sub-tabs sticky)               │      │
│  │             ├── <Transition>                                  │      │
│  │             │   ├── CoachLatidoTab.vue (default)             │      │
│  │             │   ├── CoachPostsTab.vue                         │      │
│  │             │   ├── CoachConversacionesTab.vue                │      │
│  │             │   ├── CoachPulsosTab.vue                        │      │
│  │             │   └── CoachLogrosTab.vue                        │      │
│  │             ├── FAB "Mensaje al equipo" (mobile only)         │      │
│  │             ├── CoachAnnounceModal.vue                        │      │
│  │             └── CoachCommunityTour.vue (first-visit only)     │      │
│  └──────────────────────────────────────────────────────────────┘      │
│                                                                        │
│  Composables singleton (module-scope, TTL + dedup):                    │
│   • useCoachCommunity (feed pagination, post actions, real-time)       │
│   • useCoachPulse (team health, top performers, at-risk clients)       │
│   • useModeration (pin/unpin/delete/official with optimistic UI)       │
│   • useCoachAnnounce (modal state, recipient count, send flow)         │
│   • usePushSubscription (browser permission, sub/unsub)                │
│                                                                        │
│  Stores Pinia:                                                         │
│   • auth.js (extended) — setAuth/clearAuth invalidate los 5 caches     │
│                                                                        │
│  Real-time (Echo):                                                     │
│   • coach.{coachId}.community → PostPinned, PostMadeOfficial,          │
│     CoachCommunityActivity, PostReported                               │
│   • Per-post subscription in CoachPostsTab (post.id channels)          │
│                                                                        │
│  Backend:                                                              │
│   • POST /api/v/coach/community/announce (NEW IMPL)                    │
│   • POST /api/v/coach/push/subscribe (NEW)                             │
│   • DELETE /api/v/coach/push/subscribe/{id} (NEW)                      │
│   • GET /api/v/coach/notifications/preferences (NEW)                   │
│   • PATCH /api/v/coach/notifications/preferences (NEW)                 │
│   • app/Console/Commands/PrecomputeCoachPulse.php (NEW)                │
└────────────────────────────────────────────────────────────────────────┘
```

### File Map (Fase B)

#### Frontend new files (16)

```
resources/js/vue/pages/Coach/
├── Community.vue                              [NEW] hub principal con tabs + transiciones

resources/js/vue/pages/Coach/community/
├── CoachLatidoTab.vue                         [NEW] team health ring + top 3 + at-risk
├── CoachPostsTab.vue                          [NEW] feed paginado con poderes mod
├── CoachConversacionesTab.vue                 [NEW] threads cronológicos últimos 7d
├── CoachPulsosTab.vue                         [NEW] grid pulsos activos
├── CoachLogrosTab.vue                         [NEW] PRs + achievements semanales

resources/js/vue/components/community/
├── CoachBadge.vue                             [NEW] chip "Coach" amarillo
├── OfficialBadge.vue                          [NEW] chip "WellCore Coach Pick" rojo
├── PinnedIndicator.vue                        [NEW] icono pin + tooltip
├── TeamHealthRing.vue                         [NEW] SVG ring con score 0-100 animado
├── TopPerformerCard.vue                       [NEW] avatar + métrica + click-to-message
├── AtRiskClientChip.vue                       [NEW] chip rojo + CTA "Mensaje rápido"
├── CoachAnnounceModal.vue                     [NEW] modal 2-modos
├── PushPermissionBanner.vue                   [NEW] banner inline dismissible
├── CoachCommunityTour.vue                     [NEW] tour 4 pasos primera visita
├── PostCardCoachActions.vue                   [NEW] action bar overlay (pin/del/official)

resources/js/vue/composables/
├── useCoachCommunity.js                       [NEW] singleton feed + actions
├── useCoachPulse.js                           [NEW] singleton pulse + auto-refresh
├── useModeration.js                           [NEW] optimistic pin/unpin/del/official
├── useCoachAnnounce.js                        [NEW] modal state + recipient count + send
├── usePushSubscription.js                     [NEW] browser permission + sub/unsub

resources/js/vue/pages/Coach/
└── NotificationsPreferences.vue               [NEW] page /coach/notifications
```

#### Frontend modified files (3)

```
resources/js/vue/layouts/CoachLayout.vue       [MODIFY] agregar sección "Comunidad" en navSections + 4ta opción FAB
resources/js/vue/router/index.js               [MODIFY] registrar /coach/community + /coach/notifications
resources/js/vue/stores/auth.js                [MODIFY] importar 5 reset functions; llamar en setAuth + clearAuth
```

#### Backend new files (3)

```
app/Http/Controllers/Api/Coach/
└── PushSubscriptionController.php             [NEW] subscribe + unsubscribe + preferences

app/Console/Commands/
└── PrecomputeCoachPulse.php                   [NEW] cada 5min, calcula pulse para coaches activos

routes/api.php                                  [MODIFY] 4 rutas nuevas (announce impl + push + prefs)
```

#### Backend modified files (3)

```
app/Http/Controllers/Api/Coach/CommunityController.php   [MODIFY] reemplazar 501 stub de announce() con impl
app/Services/PushNotificationService.php                 [MODIFY] notifyCoachAnnounceToClients (batch)
app/Console/Kernel.php (o routes/console.php)            [MODIFY] schedule precompute-coach-pulse cada 5min
```

#### Tests new files (10)

```
tests/Unit/Composables/
├── useCoachCommunity.test.js                  [NEW] Vitest happy + error + reset
├── useCoachPulse.test.js                      [NEW] Vitest happy + dedup + reset
├── useModeration.test.js                      [NEW] Vitest optimistic + revert
├── useCoachAnnounce.test.js                   [NEW] Vitest preview count + send
└── usePushSubscription.test.js                [NEW] Vitest permission stub

tests/Feature/Coach/
├── AnnounceEndpointTest.php                   [NEW] Pest happy path + push + audit
├── PushSubscriptionTest.php                   [NEW] Pest sub + unsub + dedup endpoint
└── NotificationsPreferencesTest.php           [NEW] Pest GET + PATCH

tests/Unit/Commands/
└── PrecomputeCoachPulseTest.php               [NEW] Pest schedule + cache write

tests/Feature/Coach/
└── CommunityHubE2ESmokeTest.php               [NEW] Pest smoke flujo coach-login → tabs cargan
```

---

## Information Architecture (IA)

### Sidebar coach (CoachLayout.vue)

Antes:

```
Aprendizaje: Onboarding
Principal: Inicio, Clientes, Check-ins, Fotos de Comida, Mensajes
Gestión: Tickets, Planes, Kanban, Comprobantes
Crecimiento: Estrategia, Broadcast, Invitaciones, Analítica
Personal: Notas
```

Después:

```
Aprendizaje: Onboarding
Principal: Inicio, Clientes, Check-ins, Fotos de Comida, Mensajes
COMUNIDAD: Comunidad   ← nueva sección, 1 item, badge "Nuevo" 14 días
Gestión: Tickets, Planes, Kanban, Comprobantes
Crecimiento: Estrategia, Broadcast, Invitaciones, Analítica
Personal: Notas, Notificaciones   ← nuevo item en Personal
```

**Ícono "Comunidad"**: SVG outline group de 3 personas conectadas por líneas (custom; estilo Heroicons outline 1.5 stroke).

**Ícono "Notificaciones"**: SVG outline bell con onda, similar al NotificationBell del topbar pero outline.

**Badge "Nuevo"**: idéntico patrón `isNew: true` ya implementado en sidebar (Onboarding y Estrategia lo usan). Auto-expira: localStorage `coach_community_seen_first_visit` → quita el badge tras primera visita.

### Mobile bottom nav (CoachLayout.vue)

Sin cambios:

```
Inicio · Clientes · [FAB] · Check-ins · Mensajes
```

**FAB sheet** (mobile only) — añadir 4ta opción AL TOPE del sheet (más prominente):

```
🟥 Mensaje al equipo  ← nueva, prominente, abre CoachAnnounceModal
─────────────────────
👤 Agregar cliente
✉️ Enviar broadcast
✅ Revisar check-ins
```

Activación de modal: `fabOpen.value=false; announceModalOpen.value=true;` con haptic light.

### Tabs en /coach/community

```
Desktop (≥ lg):
┌─────────────────────────────────────────────────────────────┐
│ [Latido del Equipo] [Posts] [Conversaciones] [Pulsos] [Logros] │
│  ↑active (border-bottom 2px wc-accent + text-wc-text)          │
└─────────────────────────────────────────────────────────────┘

Mobile:
┌──────────────────────────────────────────────────────┐
│ ←  [Latido] [Posts] [Conversaciones] [Pulsos] [Logros] →│  scroll horizontal sticky
└──────────────────────────────────────────────────────┘
```

Sticky header: `sticky top-16 z-20 bg-wc-bg/80 backdrop-blur-xl border-b border-wc-border`.

Active tab: `border-b-2 border-wc-accent text-wc-text font-semibold`.
Inactive: `text-wc-text-tertiary hover:text-wc-text-secondary border-b-2 border-transparent`.

URL hash sync: `/coach/community#latido` ↔ `/coach/community#posts`. Permite shareable links + browser back/forward.

Tab transition: `<Transition mode="out-in">` con `enter-from-class="opacity-0 translate-x-4"` `enter-active-class="duration-200"`. Slide horizontal según dirección (next tab = slide left).

---

## Tabs detalle

### Tab 1: Latido del Equipo (default landing)

**Source**: `GET /api/v/coach/community/pulse` cache 60s + frontend cache 25s.

**Shape data**:

```json
{
  "team_health_score": 78,
  "top_performers": [
    {"client_id": 12, "client_name": "Carlos Pérez", "avatar_url": "...",
     "metric": "5 entrenamientos · 2 PRs", "score": 92},
    ...
  ],
  "at_risk_clients": [
    {"client_id": 27, "client_name": "Ana Ruiz", "avatar_url": "...",
     "days_inactive": 6, "last_login_at": "2026-04-29T..."},
    ...
  ],
  "computed_at": "2026-05-05T14:32:00-05:00"
}
```

**Layout** (desktop):

```
┌─────────────────────────────────────────────────────────────────┐
│ [PushPermissionBanner if applicable]                            │
│                                                                 │
│  ╭──────────────╮   ╭───── Top performers (7d) ─────╮          │
│  │              │   │  🥇 Carlos Pérez · 5 sesiones │          │
│  │  78 / 100    │   │  🥈 María Lopez · 4 sesiones  │          │
│  │   wc-accent  │   │  🥉 Pedro Sosa · 3 sesiones   │          │
│  │              │   ╰───────────────────────────────╯          │
│  │ Latido del   │                                              │
│  │ Equipo       │   ╭──── Riesgo de churn (5d) ────╮           │
│  ╰──────────────╯   │ ⚠️  Ana Ruiz   · 6d inactivo │           │
│                     │ ⚠️  Luis Quesada · 5d         │           │
│                     │ [+ Mensaje rápido]            │           │
│                     ╰───────────────────────────────╯          │
│                                                                 │
│  ┌─ Heatmap actividad equipo ────────────────────────────────┐  │
│  │ [Lun ████░░░] [Mar ██░░░░░] [Mié █████░] ...           │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Computed at: 14:32 · Refresca cada 60s · [↻ Actualizar]       │
└─────────────────────────────────────────────────────────────────┘
```

**Components consumidos**:
- `<TeamHealthRing :score="78" />` — SVG ring 200x200 animado entry (count up 0→78 con `useCountUp`), gradiente wc-accent → emerald si score≥80, amber 60-79, rose si <60.
- `<TopPerformerCard v-for="p in top_performers" />` — avatar 40px + nombre + métrica + tap → `/coach/clients/{id}`.
- `<AtRiskClientChip v-for="c in at_risk_clients" :client="c" @message="openQuickMessage" />` — chip rojo con badge "6d", click abre quick-message modal pre-populated con plantilla.
- Heatmap: SVG grid 7×24 con opacity por activity count. Tooltip on hover "Mar 14:00 · 3 entrenamientos".

**Loading**: skeleton ring (gray pulse) + 3 card skeletons + 2 chip skeletons + heatmap rectangles.

**Empty state**: "Tu equipo aún no tiene actividad esta semana. Activa notificaciones para enterarte cuando rompan PRs." con CTA `[Activar notificaciones]` que abre push permission flow.

**Real-time**: escucha `coach.{coachId}.community` event `CoachCommunityActivity`. Cuando llega event PR o achievement, animación pulse en TeamHealthRing + count-up del score si cambia.

**Auto-refresh**: cada 90s en background si tab activa. Si tab inactiva (visibilitychange), suspende.

### Tab 2: Posts (feed completo + moderación)

**Source**: `GET /api/v/coach/community/posts?filter=all|pinned|reported|achievements|prs&page=1&per_page=20`.

**Filter chips** al top:

```
[Todos (143)] [Fijados (3)] [Reportados (2)] [Logros (28)] [PRs (12)]
```

Counts en vivo desde response.

**Post card layout** (replica patrón cliente CommunityFeed con extras coach):

```
┌─────────────────────────────────────────────────────────────┐
│ 📌 Fijado · 2d restantes                ← PinnedIndicator   │
│ ┌──┐  Carlos Pérez · hace 2h     [⚙ Acciones coach ▾]      │
│ │👤│  PR de Sentadilla 110kg                                │
│ └──┘                                                        │
│                                                             │
│  [Imagen post si aplica]                                    │
│                                                             │
│  💪 5  🔥 3  👍 2  💬 8 comentarios                        │
│  [▾ Ver thread]                                             │
└─────────────────────────────────────────────────────────────┘
```

**Action bar overlay coach** (`PostCardCoachActions.vue`):
- ⚙ Botón hamburger → dropdown con:
  - 📌 Fijar / Desfijar (toggle según state)
  - ⭐ Hacer oficial
  - 💬 Comentar como coach (badge "Coach" amarillo en comentario)
  - ❤️ Reaccionar (con badge "Coach")
  - 🗑 Eliminar (confirmación inline doble-click pattern)

**Optimistic UI**: pin click → inmediato `📌 Fijado · 168h` + haptic light. Si fail, revert + toast error.

**Real-time per-post**: cada post se subscribe a `community-post.{id}` (igual que cliente). Eventos `post-pinned`, `post-reaction-toggled`, `post-comment-added` actualizan in-place.

**Top-level real-time**: `coach.{coachId}.community` channel `PostReported` → si nuevo report, prepend "💬 1 reporte nuevo" al filter chip "Reportados (2→3)" + flash animación 1s.

**Infinite scroll**: sentinel con IntersectionObserver al fondo del feed. Auto-fetch siguiente página.

**Auto-prepend logic** (real-time CoachCommunityActivity event_type='post_created'):

```js
const SCROLL_THRESHOLD_PX = 200;
if (window.scrollY < SCROLL_THRESHOLD_PX) {
  posts.value.unshift(newPost);
  haptics.light();
  // Slide-down animation via stagger refresh
} else {
  newPostsBufferCount.value += 1;
  // Show floating toast at top: "🔄 3 nuevos posts" → click scrolls top + flushes buffer
}
```

**Empty state filter "Reportados (0)"**: "Tu equipo no tiene reportes pendientes. Buen trabajo manteniendo la comunidad sana 🛡️".

**Loading skeleton**: 3 post-card skeletons con shimmer (avatar circle + 2 líneas + image rect 16:9 + reaction row).

### Tab 3: Conversaciones (threads cronológicos 7d)

**Source nuevo endpoint**: `GET /api/v/coach/community/threads?since_days=7&page=1`.

**NOTA**: Este endpoint NO existe en Fase A. Necesita ser creado en Fase B (extension del CoachCommunityService).

**Shape**:

```json
{
  "data": [
    {
      "post_id": 1234,
      "post_excerpt": "PR de Sentadilla 110kg",
      "post_author_name": "Carlos Pérez",
      "thread_size": 8,
      "last_activity_at": "2026-05-05T13:22:00-05:00",
      "participants_count": 4,
      "has_coach_reply": true,
      "is_conflicted": false
    }
  ],
  "pagination": {...}
}
```

**Layout**:

```
┌─────────────────────────────────────────────────────────────┐
│ [Todos] [Sin respuesta de coach] [+50 comentarios] [Conflictos]│
│                                                             │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Carlos · "PR de Sentadilla 110kg" · 8 comentarios · 4   │ │
│ │ participantes · hace 2h                                  │ │
│ │ [Ver thread →]                            [💬 Coach respondió] │
│ └─────────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ Ana · "Comida del día" · 3 comentarios · 2 part. · 5h    │ │
│ │ [Ver thread →]                            [⚠️ Sin respuesta] │ │
│ └─────────────────────────────────────────────────────────┘ │
│ ...                                                         │
└─────────────────────────────────────────────────────────────┘
```

Click en card → expansión inline con full thread (`<CommentsThread />` reusando component existente del cliente, con prop `isCoachContext`).

**`isCoachContext` ext** en `CommentsThread.vue`: si true, agrega botón "Responder como coach" con badge amarillo + posición top del thread.

**Conflicto detection** (backend en CoachCommunityService): thread con ≥10 comentarios donde 2+ usuarios diferentes mencionan negative sentiment OR contiene `@admin`/`@wellcore` mentions. Flag `is_conflicted=true`.

**Filter "Sin respuesta de coach"**: posts donde `has_coach_reply=false` y last_activity > 3h (señal: cliente esperando atención).

**Empty state**: "No hay conversaciones recientes en tu comunidad. Anímalos a interactuar con un mensaje al equipo 🚀" con CTA `[Mensaje al equipo]`.

### Tab 4: Pulsos (stories activos del equipo)

**Source**: `GET /api/v/coach/community/pulsos`.

**Layout**: Grid responsive de cards stories (igual patrón cliente CommunityFeed `storiesMembers`).

```
Desktop:                      Mobile:
┌───┬───┬───┬───┐             ┌─────────────┐
│ ⚪ │ ⚪ │ ⚪ │ ⚪ │             │ ⚪ Carlos    │
│Car │Mar │Pdr │Lui │             │ ⚪ María     │
└───┴───┴───┴───┘             │ ⚪ Pedro     │
                              └─────────────┘
```

**Pulso ring** (`PulsoRing.vue` ya existe del cliente, reutilizar): círculo 80px con gradient wc-accent, avatar dentro, count "5h restantes" abajo.

**Tap**: abre `PulsoViewer.vue` (ya existe), con prop `isCoachMode=true` que añade botones:
- "⭐ Compartir al feed" — convierte pulso en post oficial coach
- "❤️ Reaccionar" con badge Coach
- "🚩 Reportar" (si pulso ofensivo)

**Sort priority**: clientes que rompieron PR en últimas 24h primero (servidor lo decide via `priority` field en response).

**Real-time**: nuevo pulso de cliente → `CoachCommunityActivity` event_type='pulso_created' → prepend al grid + haptic light.

**Empty state**: "Ningún cliente activo subió un pulso aún. Los pulsos duran 24-48h y son la forma más visual de compartir progreso. Comparte el link de tu comunidad para anímarles."

### Tab 5: Logros del equipo (achievements + PRs semanales)

**Source nuevo endpoint**: `GET /api/v/coach/community/achievements?period=week|month|all&page=1`.

**NOTA**: Endpoint nuevo Fase B. Backend retorna achievements + PRs últimos 7d/30d.

**Shape**:

```json
{
  "data": [
    {
      "type": "pr",
      "client_id": 12,
      "client_name": "Carlos Pérez",
      "avatar_url": "...",
      "exercise": "Sentadilla",
      "weight_kg": 110,
      "previous_weight_kg": 105,
      "post_id": 1234,
      "achieved_at": "2026-05-04T..."
    },
    {
      "type": "achievement",
      "client_id": 27,
      "client_name": "Ana Ruiz",
      "achievement_name": "30 días consecutivos",
      "achieved_at": "2026-05-03T..."
    },
    ...
  ],
  "totals": {"prs": 12, "achievements": 28},
  "pagination": {...}
}
```

**Layout**: 

```
┌──────────────────────────────────────────────────────────────┐
│ [Esta semana (40)] [Este mes (118)] [Histórico]              │
│                                                              │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ 🏋️ Carlos Pérez · PR de Sentadilla                      │  │
│ │     105kg → 110kg (+5kg)  · hace 1d                     │  │
│ │     [💬 Felicitar como coach] [⭐ Compartir al feed]    │  │
│ └────────────────────────────────────────────────────────┘  │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ 🏆 Ana Ruiz · 30 días consecutivos                      │  │
│ │     hace 2d                                             │  │
│ │     [💬 Felicitar como coach] [⭐ Compartir al feed]    │  │
│ └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

**Action "Felicitar"**: opens inline mini-form con texto pre-populated ("¡Felicidades por tu PR de Sentadilla! Esa fuerza es resultado del trabajo consistente. 💪") editable, submit → crea `PostComment` en post original con `author_type='coach'` + badge.

**Action "Compartir al feed"**: crea CommunityPost oficial con texto template ("🏆 Felicitaciones a Carlos por su PR de Sentadilla 110kg. ¡Equipo en racha!") + imagen del avatar, `is_official=true`, opcional pin 24h.

**Sort**: by `achieved_at desc`. PRs y achievements intercalados naturalmente.

**Loading**: 4 card skeletons.

**Empty state semana**: "Tu equipo aún no tiene logros esta semana. Sé proactivo: revisa quién está cerca de un PR y motivalo con un mensaje 🚀".

**Gamification interna**: badge "🔥 Equipo en racha" si totals.prs ≥ 10 esta semana (banner top de la tab).

---

## Modal "Mensaje al equipo" (CoachAnnounceModal.vue)

### Activación

- Desktop: NO hay FAB en desktop. Botón flotante "Mensaje al equipo" en bottom-right en la página `/coach/community` (sticky), `bg-wc-accent rounded-full px-5 py-3 shadow-lg` con ícono megáfono.
- Mobile: FAB sheet 4ta opción "Mensaje al equipo".

### Layout

```
┌─────────────────────────────────────────────────────┐
│ Mensaje al equipo                              [✕]  │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌─────────────────┬──────────────────┐             │
│  │ 📢 Anuncio       │ 🔔 Push          │  ← toggle  │
│  │ in-feed activo   │                  │             │
│  └─────────────────┴──────────────────┘             │
│                                                     │
│  ── Modo "Anuncio" ──                                │
│                                                     │
│  Tu mensaje:                                        │
│  ┌─────────────────────────────────────┐            │
│  │ Sigan la racha esta semana! Vamos   │            │
│  │ por más PRs 💪                      │            │
│  └─────────────────────────────────────┘            │
│  142 / 1000 caracteres                              │
│                                                     │
│  ┌──────── Imagen opcional ────────┐                │
│  │  [+] Subir imagen (max 5MB)     │                │
│  └─────────────────────────────────┘                │
│                                                     │
│  Fijar al tope del feed:                            │
│  [○ No fijar] [● 24h] [○ 48h] [○ 1 semana]          │
│                                                     │
│  ┌─── Vista previa ───┐                             │
│  │  📌 Fijado 24h      │                            │
│  │  ⭐ Coach Pick       │                            │
│  │  Coach D · ahora     │                            │
│  │  Sigan la racha...   │                            │
│  └─────────────────────┘                            │
│                                                     │
│  Recipients: 27 clientes activos verán este post   │
│                                                     │
│  [Cancelar]            [Enviar al equipo]           │
└─────────────────────────────────────────────────────┘
```

```
── Modo "Push" ──

Tu mensaje:
┌────────────────────┐
│ Recordatorio:       │
│ check-in mañana 9am │
└────────────────────┘
156 / 200 caracteres (max para push)

Recipients filter:
○ Todos los clientes activos (27)
○ Solo clientes con plan: [▼ Selector]
○ Solo clientes inactivos +3d (5)

┌── Vista previa push ──┐
│  WellCore Coaching     │
│  Recordatorio: check-  │
│  in mañana 9am         │
└────────────────────────┘

Recipients en vivo: 27 clientes recibirán push

[Cancelar]              [Enviar push]
```

### Lógica

- Toggle sticky entre modos. Cada modo guarda state separado en composable.
- Recipients count: live debounced (300ms) call a `GET /api/v/coach/clients/count?status=activo&plan=...`. Cache en memo según filtros.
- Imagen: validation idéntica a CommunityFeed cliente (`POST_MEDIA_MIMES = ['image/jpeg', 'image/png', 'image/webp']`, max 5MB).
- Preview live: usa `PostCard` micro-template con datos del form.
- Submit: `POST /api/v/coach/community/announce` con FormData (incluye image si aplica).
- Backend: implementación en CommunityController::announce (ahora 501 → impl).
- Después del send: cierra modal + toast success "✅ Tu mensaje llegó a 27 clientes" + haptic success + refetch del feed Posts tab + marca cache invalido.

### Confirmation step (premium UX)

Antes del submit final, modal de confirmación overlay si recipients > 20:

```
⚠️ Vas a enviar a 27 clientes

  - 18 con plan Método activo
  - 9 con plan RISE activo

  ¿Confirmar envío?

  [Cancelar]  [Sí, enviar al equipo]
```

Esto previene anuncios sorpresa con audiencia mal calculada.

---

## Push notifications coach (UX completa)

### Subscription flow

1. **First visit a `/coach/community`** (latido tab):
   - Si `Notification.permission === 'default'` Y no `localStorage.coach_push_dismissed_at` o dismissal antiguo (>7d):
     - Mostrar `<PushPermissionBanner>` al top del Latido tab.
   - Banner UI:
     ```
     🔔 Activa notificaciones para no perder cuando tu equipo rompa PRs o
        necesite atención inmediata.
     [Activar ahora] [Más tarde]
     ```
   - "Activar ahora" → `Notification.requestPermission()`:
     - granted: subscribe to PushManager + envía endpoint a `POST /api/v/coach/push/subscribe`.
     - denied: oculta banner permanentemente + toast "OK, sin notificaciones. Puedes activarlas desde tu navegador."
   - "Más tarde": `localStorage.coach_push_dismissed_at = Date.now()`. Banner reaparece en 7 días.

2. **Si permission === 'granted'** pero no hay subscription server-side: re-subscribir silenciosamente al cargar.

3. **Si permission === 'denied'**: banner explainer en `/coach/notifications` con instrucciones por browser ("Click en el ícono de candado al lado de la URL → Permitir notificaciones").

### Endpoint backend

```php
// POST /api/v/coach/push/subscribe
{
  "endpoint": "https://fcm.googleapis.com/fcm/send/...",
  "keys": {
    "p256dh": "BAxxx...",
    "auth": "yyy..."
  },
  "user_agent": "Mozilla/5.0..."
}
→ 201 { "id": 42, "active": true }

// DELETE /api/v/coach/push/subscribe/{id}
→ 204

// GET /api/v/coach/notifications/preferences
→ 200 { "notify_pr_broken": true, "notify_streak_milestone": true,
        "notify_post_created": false, "notify_comment_on_my_reply": true,
        "notify_at_risk_client": true, "notify_official_post_engagement": true,
        "notify_admin_broadcast": true, "push_enabled": true, "in_app_enabled": true }

// PATCH /api/v/coach/notifications/preferences
{ "notify_post_created": true }
→ 200 { ...updated row }
```

### Page `/coach/notifications`

```
Notificaciones del coach
─────────────────────────

Canal de envío:
[●] Push (browser)        [✓ Activado · Ver suscripciones]
[●] In-app (campana)

Cuándo notificarme:
[✓] Cuando un cliente rompe un PR
[✓] Cuando un cliente alcanza un milestone (7/30/100 días)
[ ] Cuando un cliente hace un post (silencioso por defecto)
[✓] Cuando alguien comenta después de mi respuesta
[✓] Cuando un cliente lleva 5+ días sin actividad
[✓] Cuando un cliente reacciona a mi post oficial
[✓] Cuando WellCore admin envía un anuncio

[Guardar preferencias]
```

Live save: `PATCH` debounced 500ms tras cambio de toggle. Toast subtle "✓ Guardado".

---

## Composables (5 nuevos, 1 modificado)

### useCoachCommunity.js

```js
// Singleton module-scope, igual patrón useGroupPulse
const feedCache = new Map(); // key = `${filter}:${page}` → { data, timestamp }
const FEED_TTL_MS = 25_000;
let feedPromises = new Map(); // dedup in-flight per key

export function useCoachCommunity() {
  const api = useApi();
  const loading = ref(false);
  const error = ref(null);

  async function fetchFeed({ filter = 'all', page = 1, perPage = 20, force = false }) {
    const key = `${filter}:${page}`;
    if (!force && feedCache.has(key) && Date.now() - feedCache.get(key).timestamp < FEED_TTL_MS) {
      return feedCache.get(key).data;
    }
    if (feedPromises.has(key)) return feedPromises.get(key);

    loading.value = true;
    const promise = (async () => {
      try {
        const res = await api.get('/api/v/coach/community/posts', {
          params: { filter, page, per_page: perPage }
        });
        feedCache.set(key, { data: res.data, timestamp: Date.now() });
        return res.data;
      } catch (err) {
        error.value = err.response?.data?.message || 'No se pudo cargar el feed.';
        if (err.response?.status >= 500 || !err.response) {
          console.error('[useCoachCommunity] fetchFeed failed', err);
        }
        return null;
      } finally {
        loading.value = false;
        feedPromises.delete(key);
      }
    })();
    feedPromises.set(key, promise);
    return promise;
  }

  return { loading, error, fetchFeed };
}

export function resetCoachCommunity() {
  feedCache.clear();
  feedPromises.clear();
}
```

### useCoachPulse.js

Patrón idéntico a useGroupPulse pero para `/api/v/coach/community/pulse`. TTL 25s singleton + dedup. Reset hook para impersonation.

### useModeration.js

Optimistic UI con revert + audit. No cachea (es action-only).

```js
export function useModeration() {
  const api = useApi();
  const haptics = useHaptics();
  const toast = useToast();

  async function pinPost(postId, hours = 168, note = null) {
    // optimistic: caller debe pasar reactiveCallback
    try {
      const res = await api.post(`/api/v/coach/posts/${postId}/pin`, { hours, note });
      haptics.success();
      return res.data;
    } catch (err) {
      haptics.error();
      toast.apiError(err, 'No pudimos fijar el post.');
      throw err;
    }
  }
  // unpinPost, deletePost, makeOfficial similar
  return { pinPost, unpinPost, deletePost, makeOfficial };
}
```

### useCoachAnnounce.js

```js
const recipientCountCache = new Map(); // segmentKey → { count, timestamp }
const COUNT_TTL_MS = 30_000;

export function useCoachAnnounce() {
  const api = useApi();
  const isOpen = ref(false);
  const mode = ref('post'); // 'post' | 'push'
  const message = ref('');
  const pinHours = ref(0); // 0 = no pin
  const segment = ref({ status: ['activo'], plan: null });
  const recipientCount = ref(null);
  const sending = ref(false);

  async function previewCount() {
    const key = JSON.stringify(segment.value);
    if (recipientCountCache.has(key) && Date.now() - recipientCountCache.get(key).timestamp < COUNT_TTL_MS) {
      recipientCount.value = recipientCountCache.get(key).count;
      return;
    }
    const res = await api.get('/api/v/coach/clients/count', { params: segment.value });
    recipientCount.value = res.data.count;
    recipientCountCache.set(key, { count: res.data.count, timestamp: Date.now() });
  }

  async function send({ image }) {
    sending.value = true;
    try {
      const fd = new FormData();
      fd.append('type', mode.value);
      fd.append('message', message.value);
      if (mode.value === 'post' && pinHours.value > 0) fd.append('pin_hours', pinHours.value);
      if (mode.value === 'post' && image) fd.append('image', image);
      if (mode.value === 'push' && segment.value.plan) fd.append('plan_filter', JSON.stringify(segment.value.plan));
      const res = await api.post('/api/v/coach/community/announce', fd);
      isOpen.value = false;
      message.value = '';
      return res.data;
    } finally {
      sending.value = false;
    }
  }

  return { isOpen, mode, message, pinHours, segment, recipientCount, sending, previewCount, send };
}

export function resetCoachAnnounce() {
  recipientCountCache.clear();
}
```

### usePushSubscription.js

```js
export function usePushSubscription() {
  const api = useApi();
  const permission = ref(typeof Notification !== 'undefined' ? Notification.permission : 'default');
  const subscription = ref(null);

  async function request() {
    if (typeof Notification === 'undefined') {
      throw new Error('Notifications not supported in this browser');
    }
    const result = await Notification.requestPermission();
    permission.value = result;
    if (result === 'granted') {
      await subscribe();
    }
    return result;
  }

  async function subscribe() {
    const reg = await navigator.serviceWorker.ready;
    const vapidKey = window.__WC_VAPID_PUBLIC_KEY || ''; // injected by blade
    const sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: vapidKey
    });
    subscription.value = sub;
    const json = sub.toJSON();
    const res = await api.post('/api/v/coach/push/subscribe', {
      endpoint: json.endpoint,
      keys: { p256dh: json.keys.p256dh, auth: json.keys.auth },
      user_agent: navigator.userAgent.slice(0, 255)
    });
    return res.data;
  }

  async function unsubscribe() {
    if (!subscription.value) {
      const reg = await navigator.serviceWorker.ready;
      subscription.value = await reg.pushManager.getSubscription();
    }
    if (subscription.value) {
      await subscription.value.unsubscribe();
      // Backend dedup endpoint on coach_id+endpoint
    }
  }

  return { permission, subscription, request, subscribe, unsubscribe };
}
```

### Modificación a auth.js (Pinia store)

Importar 5 funciones de reset y llamarlas en setAuth + clearAuth:

```js
import { resetGroupPulse } from '../composables/useGroupPulse';
import { resetCoachCommunity } from '../composables/useCoachCommunity';
import { resetCoachPulse } from '../composables/useCoachPulse';
import { resetCoachAnnounce } from '../composables/useCoachAnnounce';
// (useModeration y usePushSubscription no tienen cache singleton — no necesitan reset)

function setAuth(data) {
  if (data.token && data.token !== token.value) {
    resetGroupPulse();
    resetCoachCommunity();
    resetCoachPulse();
    resetCoachAnnounce();
  }
  // ... rest
}

function clearAuth() {
  resetGroupPulse();
  resetCoachCommunity();
  resetCoachPulse();
  resetCoachAnnounce();
  // ... rest
}
```

---

## Backend (announce + push + preferences + scheduled command)

### CommunityController::announce — implementación final

```php
public function announce(Request $request): JsonResponse
{
    $coach = $request->user();
    abort_unless($this->isCoach($coach), 403);

    $validated = $request->validate([
        'type'      => 'required|in:post,push',
        'message'   => 'required|string|max:1000',
        'pin_hours' => 'nullable|integer|min:1|max:168',
        'image'     => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        'plan_filter' => 'nullable|json',
    ]);

    if ($validated['type'] === 'post') {
        return $this->announceAsPost($coach, $validated, $request);
    }

    return $this->announceAsPush($coach, $validated);
}

private function announceAsPost(Admin $coach, array $data, Request $request): JsonResponse
{
    return DB::transaction(function () use ($coach, $data, $request) {
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('community/announcements', 'public');
            $imageUrl = Storage::url($path);
        }

        $post = CommunityPost::create([
            'client_id'       => null,
            'coach_admin_id'  => $coach->id,
            'author_type'     => 'coach',
            'author_admin_id' => $coach->id,
            'is_official'     => true,
            'is_global'       => false,
            'content'         => $data['message'],
            'image_url'       => $imageUrl,
            'visible'         => true,
        ]);

        if (!empty($data['pin_hours'])) {
            app(ModerationService::class)->pinPost(
                $post, $coach, 'coach', (int) $data['pin_hours'], 'Anuncio al equipo'
            );
        }

        ModerationAction::create([
            'actor_type'  => 'coach',
            'actor_id'    => $coach->id,
            'action_type' => 'announce',
            'target_type' => 'post',
            'target_id'   => $post->id,
            'metadata'    => ['mode' => 'post', 'pin_hours' => $data['pin_hours'] ?? null],
            'created_at'  => now(),
        ]);

        $clientIds = app(CoachCommunityService::class)->resolveClientIds($coach->id);
        $count = count($clientIds);

        BroadcastMessage::create([
            'sender_type'      => 'coach',
            'sender_id'        => $coach->id,
            'audience_type'    => 'clients',
            'segment_filter'   => null,
            'subject'          => null,
            'body'             => $data['message'],
            'push_enabled'     => false,
            'recipients_count' => $count,
            'delivered_count'  => $count,
            'sent_at'          => now(),
        ]);

        event(new BroadcastSent($post->id, 'announcement_post', $count));

        return response()->json([
            'post_id' => $post->id,
            'recipients_count' => $count,
            'pinned_until' => $post->pinned?->pinned_until,
        ], 201);
    });
}

private function announceAsPush(Admin $coach, array $data): JsonResponse
{
    $segmentFilter = isset($data['plan_filter']) ? json_decode($data['plan_filter'], true) : null;
    $clientIds = app(CoachCommunityService::class)->resolveClientIds(
        $coach->id, planFilter: $segmentFilter
    );

    $count = app(PushNotificationService::class)->notifyCoachAnnounceToClients(
        coachId: $coach->id,
        clientIds: $clientIds,
        message: $data['message']
    );

    BroadcastMessage::create([
        'sender_type'      => 'coach',
        'sender_id'        => $coach->id,
        'audience_type'    => 'clients',
        'segment_filter'   => $segmentFilter,
        'subject'          => 'Anuncio del coach',
        'body'             => $data['message'],
        'push_enabled'     => true,
        'recipients_count' => count($clientIds),
        'delivered_count'  => $count,
        'sent_at'          => now(),
    ]);

    ModerationAction::create([
        'actor_type'  => 'coach',
        'actor_id'    => $coach->id,
        'action_type' => 'announce',
        'target_type' => 'post',
        'target_id'   => 0, // no post — push only
        'metadata'    => ['mode' => 'push', 'segment' => $segmentFilter, 'count' => $count],
        'created_at'  => now(),
    ]);

    event(new BroadcastSent(0, 'announcement_push', $count));

    return response()->json([
        'recipients_count' => count($clientIds),
        'delivered_count' => $count,
    ], 201);
}
```

### PushNotificationService::notifyCoachAnnounceToClients

Nuevo método en service. Itera clientes en chunks de 100 (memory `feedback_npm_build_NEVER` no aplica aquí — es runtime, no build).

```php
public function notifyCoachAnnounceToClients(int $coachId, array $clientIds, string $message): int
{
    $delivered = 0;
    $coach = Admin::find($coachId);
    $title = 'Mensaje de tu coach' . ($coach?->name ? ' ' . explode(' ', $coach->name)[0] : '');

    foreach (array_chunk($clientIds, 100) as $chunk) {
        $subscriptions = PushSubscription::query()
            ->whereIn('client_id', $chunk)
            ->where('active', true)
            ->get();

        foreach ($subscriptions as $sub) {
            try {
                $this->sendToSubscription($sub, $title, $message, [
                    'tag' => 'coach-announce',
                    'url' => '/client/community',
                ]);
                $delivered++;
            } catch (\Throwable $e) {
                Log::warning('Push delivery failed', ['sub_id' => $sub->id, 'err' => $e->getMessage()]);
            }
        }
    }

    return $delivered;
}
```

### PrecomputeCoachPulse command

```php
namespace App\Console\Commands;

use App\Models\Admin;
use App\Services\CoachCommunityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PrecomputeCoachPulse extends Command
{
    protected $signature = 'wellcore:precompute-coach-pulse {--coach= : Limit to specific coach ID}';
    protected $description = 'Precompute coach community pulse for active coaches (every 5min)';

    public function handle(CoachCommunityService $service): int
    {
        $coachIds = $this->option('coach')
            ? [(int) $this->option('coach')]
            : Admin::query()
                ->where('role', 'coach')
                ->whereExists(function ($q) {
                    $q->selectRaw('1')
                      ->from('clients')
                      ->whereColumn('clients.coach_id', 'admins.id')
                      ->where('clients.status', 'activo');
                })
                ->pluck('id')
                ->all();

        $count = 0;
        foreach ($coachIds as $coachId) {
            try {
                Cache::put(
                    "wc:coach-pulse:v1:{$coachId}",
                    [
                        'team_health_score' => $service->teamHealthScore($coachId),
                        'top_performers'    => $service->topPerformers($coachId, days: 7, limit: 3),
                        'at_risk_clients'   => $service->atRiskClients($coachId, days: 5),
                        'computed_at'       => now()->toIso8601String(),
                    ],
                    ttl: 300 // 5min — survive next precompute cycle if it fails
                );
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed coach {$coachId}: {$e->getMessage()}");
            }
        }

        $this->info("Precomputed pulse for {$count} coaches.");
        return self::SUCCESS;
    }
}
```

### routes/api.php / routes/console.php updates

```php
// routes/api.php — agregar al grupo coach
Route::middleware(['wellcore.auth'])->prefix('v/coach')->group(function () {
    // ... existing routes
    Route::get('clients/count', [ClientsController::class, 'count']);
    Route::post('community/announce', [CommunityController::class, 'announce']);  // ya existe ruta, solo cambia impl
    Route::post('push/subscribe', [PushSubscriptionController::class, 'subscribe']);
    Route::delete('push/subscribe/{id}', [PushSubscriptionController::class, 'unsubscribe']);
    Route::get('notifications/preferences', [PushSubscriptionController::class, 'preferences']);
    Route::patch('notifications/preferences', [PushSubscriptionController::class, 'updatePreferences']);
});

// routes/console.php — agregar
Schedule::command('wellcore:precompute-coach-pulse')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->onOneServer();
```

---

## Real-time strategy

### Channel subscription lifecycle

`Community.vue` mounted → subscribe `coach.{coachId}.community`:

```js
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
let coachChannel = null;

onMounted(() => {
  if (!window.Echo) return;
  const coachId = authStore.userId;
  coachChannel = window.Echo.private(`coach.${coachId}.community`)
    .listen('.coach-community-activity', handleActivity)
    .listen('.post-pinned', handlePinUpdate)
    .listen('.post-reported', handleReport)
    .listen('.post-made-official', handleOfficial);
});

onBeforeUnmount(() => {
  if (coachChannel && window.Echo) {
    window.Echo.leave(`coach.${authStore.userId}.community`);
  }
});
```

### Event handlers per type

```js
function handleActivity(e) {
  // e = { eventType, clientId, clientName, payload }
  if (e.eventType === 'post_created') {
    if (currentTab.value === 'posts') {
      maybeAutoPrepend(e.payload);
    }
    // En cualquier tab, refresca cache pulse
    coachPulse.fetchSummary({ force: true });
  }
  if (e.eventType === 'pr_broken' || e.eventType === 'achievement') {
    // Pulse anim en TeamHealthRing
    if (latidoTabRef.value) latidoTabRef.value.flashHealthScore();
  }
}

function maybeAutoPrepend(post) {
  if (window.scrollY < 200 && currentTab.value === 'posts') {
    posts.value.unshift(post);
    haptics.light();
  } else {
    newPostsBuffer.value += 1;
  }
}
```

### Toast flotante "X nuevos posts"

`<Transition enter-from-class="-translate-y-full opacity-0" enter-active-class="duration-300">` 

```html
<div v-if="newPostsBuffer > 0"
     class="fixed top-20 left-1/2 -translate-x-1/2 z-40 bg-wc-accent text-white
            rounded-full px-4 py-2 shadow-lg text-sm font-semibold cursor-pointer
            hover:scale-105 transition-transform"
     @click="flushBuffer">
  ↑ {{ newPostsBuffer }} {{ newPostsBuffer === 1 ? 'nuevo post' : 'nuevos posts' }}
</div>
```

`flushBuffer()`: scroll smooth top + refetch posts feed force=true + reset buffer + haptic medium.

### NotificationBell coach ya existente

`CoachLayout.vue` usa `<NotificationBell endpoint="/api/v/coach/notifications" />`. Sin cambios — ya consume las community_notifications backend.

---

## Loading + Error states (premium per-tab)

### Latido tab loading

```html
<div v-if="loading" class="space-y-6">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Ring skeleton -->
    <div class="h-64 rounded-xl border border-wc-border bg-wc-bg-secondary p-6 flex items-center justify-center">
      <div class="h-40 w-40 rounded-full bg-wc-bg-tertiary animate-pulse"></div>
    </div>
    <!-- Top performers skeleton -->
    <div class="space-y-3">
      <div v-for="i in 3" :key="i" class="h-16 rounded-xl border border-wc-border bg-wc-bg-tertiary animate-pulse"></div>
    </div>
  </div>
</div>
```

### Posts tab loading

3 cards skeleton con shimmer (avatar circle 40px + title 60% + body 80% + image rect 16:9 + reaction row).

### Error states

Cada tab implementa boundary:

```html
<div v-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">
  <p class="text-wc-text">{{ error }}</p>
  <button @click="retry" class="mt-3 inline-flex items-center gap-2 rounded-lg
    bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-wc-accent/90">
    ↻ Reintentar
  </button>
</div>
```

### Empty states

Cada tab tiene empty state custom con illustration SVG + copy + CTA contextual. Ejemplo Posts vacíos:

```html
<div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
  <CommunityEmptyIllustration class="mx-auto h-32 w-32 mb-4" />
  <h3 class="text-lg font-display tracking-wide text-wc-text">Tu equipo aún no postea</h3>
  <p class="mt-2 text-sm text-wc-text-tertiary">
    Cuando uno de tus clientes comparta un PR, foto o pensamiento, aparecerá aquí.
    Anímalos con un mensaje al equipo.
  </p>
  <button @click="openAnnounce" class="mt-4 inline-flex items-center gap-2
    rounded-full bg-wc-accent px-5 py-2 text-sm font-semibold text-white">
    📣 Mensaje al equipo
  </button>
</div>
```

---

## Animations + Interactions

### Stagger entry (replicar useStaggerIn del cliente)

`Community.vue` usa `useStaggerIn` en root:

```js
import { useStaggerIn } from '../../composables/dashboard/useStaggerIn';
const staggerRoot = useStaggerIn();
```

Children directos (TabsHeader, banner, current tab) reciben `data-stagger-index` automáticamente.

### Tab transition

```html
<Transition mode="out-in"
  enter-from-class="opacity-0 translate-x-4"
  enter-active-class="duration-200 ease-out"
  leave-active-class="duration-150 ease-in"
  leave-to-class="opacity-0 -translate-x-2">
  <component :is="currentTabComponent" :key="currentTab" />
</Transition>
```

Direction: si nextTab está más a la derecha, slide-from-right; si izquierda, slide-from-left. Computed por `tabOrder.indexOf(prevTab) - tabOrder.indexOf(currentTab)`.

### Pin action animation

Al pin: ícono pin SVG aparece con `<Transition>` + scale 0.5 → 1 + bounce.

```css
.pin-enter-active { animation: pin-bounce 400ms ease-out; }
@keyframes pin-bounce {
  0%   { transform: scale(0) rotate(-15deg); opacity: 0; }
  60%  { transform: scale(1.2) rotate(8deg); opacity: 1; }
  100% { transform: scale(1) rotate(0); opacity: 1; }
}
```

### Make official animation

"Coach Pick" badge stamp effect: scale 1.5 → 1 + opacity 0 → 1 + slight rotate. ~300ms.

### Haptic feedback

- Tab switch: `haptics.light()`
- Pin success: `haptics.success()`
- Delete confirm: `haptics.medium()`
- Error revert: `haptics.error()`
- Real-time new post arrival: `haptics.light()`
- Announce send success: `haptics.success()`

Reduced motion: `useReducedMotion()` retorna `prefersReducedMotion` ref. Si true:
- Disable stagger entry (`fade-only`)
- Disable pin bounce (instant)
- Disable scale animations (use opacity only)

---

## Routing + sidebar mod

### router/index.js — agregar 2 rutas

```js
{ path: '/coach/community',
  name: 'coach-community',
  component: () => import('../pages/Coach/Community.vue'),
  meta: { auth: true, title: 'Comunidad — WellCore' } },
{ path: '/coach/notifications',
  name: 'coach-notifications',
  component: () => import('../pages/Coach/NotificationsPreferences.vue'),
  meta: { auth: true, title: 'Notificaciones — WellCore' } },
```

### CoachLayout.vue — modificar navSections

Insertar después de "Principal":

```js
{
  label: 'Comunidad',
  items: [
    {
      name: 'Comunidad',
      to: '/coach/community',
      icon: 'community',
      routeName: 'coach-community',
      isNew: true,
    },
  ],
},
```

Y en "Personal":

```js
{
  label: 'Personal',
  items: [
    { name: 'Notas', to: '/coach/notes', icon: 'notes', routeName: 'coach-notes' },
    { name: 'Notificaciones', to: '/coach/notifications', icon: 'bell', routeName: 'coach-notifications' },
  ],
},
```

Agregar 2 nuevos SVG icons en el block de iconos:
- `<svg v-else-if="item.icon === 'community'">` — group de 3 personas conectadas
- `<svg v-else-if="item.icon === 'bell'">` — bell outline

### FAB sheet — agregar 4ta opción al TOPE

Antes de "Agregar cliente":

```html
<button @click="onAnnounceClick" class="flex items-center gap-3 px-4 py-3 rounded-card hover:bg-wc-bg-tertiary transition-colors w-full text-left">
  <div class="w-10 h-10 rounded-lg bg-wc-accent/15 flex items-center justify-center shrink-0">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-wc-accent)" stroke-width="2" stroke-linecap="round">
      <path d="M3 11l18-5v12L3 13v-2zm0 0v3a2 2 0 002 2h3"></path>
    </svg>
  </div>
  <div>
    <div class="font-medium text-wc-text text-sm">Mensaje al equipo</div>
    <div class="text-xs text-wc-text-tertiary">Anuncio in-feed o push</div>
  </div>
</button>
```

`onAnnounceClick`: cierra fab + emite evento global → `Community.vue` recibe y abre modal.

Patrón usar event bus liviano via mitt o `provide/inject` + reactive ref en App.vue.

---

## Privacy & Authorization (re-verificación Fase A)

Coach scope ya enforced en backend (`CoachCommunityService::resolveClientIds` + Policy `CommunityPostPolicy`). Frontend solo confía en backend — no tiene lógica de permisos propia.

Adicional Fase B:
- `/coach/community` ruta tiene `meta.auth=true`. Router guard ya filtra por userType='admin' (clientes bloqueados).
- Coach con role distinto a 'coach' (solo admin/superadmin sin clientes asignados): backend retorna 403 → frontend redirige a `/coach` con toast "Sin clientes asignados aún".

`PostCardCoachActions.vue` siempre renderiza (no esconde acciones por permission frontend). Si action falla 403, toast "Acción no autorizada" + revert.

---

## Testing Strategy

### Backend (Pest, ~12 tests nuevos)

- `tests/Feature/Coach/AnnounceEndpointTest.php`
  - it announces as post with pin (creates CommunityPost + PinnedPost + ModerationAction + BroadcastMessage row + dispatches BroadcastSent)
  - it announces as push (sends to subscriptions + delivered_count + audit log)
  - it validates message required + max 1000
  - it rejects announce from non-coach Admin
  - it includes image when provided
- `tests/Feature/Coach/PushSubscriptionTest.php`
  - it subscribes coach with valid endpoint+keys (creates row, dedup en uq_coach_endpoint)
  - it dedups same endpoint (UPSERT)
  - it unsubscribes (sets active=false)
- `tests/Feature/Coach/NotificationsPreferencesTest.php`
  - it returns defaults if no row exists (firstOrCreate pattern)
  - it patches granular toggles
- `tests/Unit/Commands/PrecomputeCoachPulseTest.php`
  - it precomputes for all coaches with active clients
  - it skips coaches without clients
  - it caches with TTL 300s
- `tests/Feature/Coach/CommunityHubE2ESmokeTest.php`
  - smoke: GET /api/v/coach/community/pulse → 200 with shape
  - smoke: GET /api/v/coach/community/posts → 200 with pagination
  - smoke: POST /api/v/coach/community/announce type=post → 201

Helpers: usar pattern `auth_tokens` Bearer ya documentado en `tests/Feature/Coach/CommunityEndpointsTest.php` Fase A.

### Frontend (Vitest, ~8 tests nuevos)

```
tests/Unit/Composables/
├── useCoachCommunity.test.js
│   ├── caches result for 25s (calls API once if called twice in window)
│   ├── deduplicates concurrent calls (single in-flight Promise)
│   ├── force=true bypasses cache
│   └── resetCoachCommunity() clears cache and promises
├── useCoachPulse.test.js (similar shape)
├── useModeration.test.js
│   ├── pinPost calls API + haptic success on success
│   ├── pinPost throws + haptic error on 4xx
│   └── makeOfficial calls correct endpoint
├── useCoachAnnounce.test.js
│   ├── previewCount caches by segment key for 30s
│   ├── send calls correct endpoint with mode=post
│   └── send calls correct endpoint with mode=push + plan_filter
└── usePushSubscription.test.js
    ├── request returns Notification.permission state
    └── subscribe calls api.post with endpoint + keys
```

Mock Notification API + axios via Vitest's vi.mock + vi.stubGlobal.

### E2E manual (post-deploy smoke)

1. Coach login `daniel.esparza` → sidebar muestra "Comunidad" badge "Nuevo" → click → Latido tab abre
2. Datos populated (team_health_score, top performers, at-risk)
3. Click tab Posts → feed paginado con filter chips
4. Click "Mensaje al equipo" → modal abre → send → success toast → feed refresh
5. Mobile: hamburger → "Comunidad" → tabs scroll horizontal funcionan
6. FAB sheet móvil → "Mensaje al equipo" → modal mismo flow
7. Push permission "Activar ahora" → browser prompt → granted → notification arrives en evento PR

---

## Definition of Done — Fase B

### Frontend
- [ ] `Community.vue` hub con 5 tabs sticky + tab transitions
- [ ] 5 tab components (`CoachLatidoTab`, `CoachPostsTab`, `CoachConversacionesTab`, `CoachPulsosTab`, `CoachLogrosTab`) implementados con loading/error/empty states
- [ ] 5 composables singleton (`useCoachCommunity`, `useCoachPulse`, `useModeration`, `useCoachAnnounce`, `usePushSubscription`) con TTL + dedup
- [ ] 11 components compartidos: `CoachBadge`, `OfficialBadge`, `PinnedIndicator`, `TeamHealthRing`, `TopPerformerCard`, `AtRiskClientChip`, `CoachAnnounceModal`, `PushPermissionBanner`, `CoachCommunityTour`, `PostCardCoachActions`, `CommunityEmptyIllustration`
- [ ] `NotificationsPreferences.vue` page con toggles granulares + live save
- [ ] `CoachLayout.vue` modificado: nueva sección "Comunidad", item en Personal "Notificaciones", FAB 4ta opción "Mensaje al equipo", 2 SVG icons nuevos
- [ ] `router/index.js` registrado: `/coach/community` + `/coach/notifications`
- [ ] `auth.js` extendido: imports + reset calls en setAuth/clearAuth
- [ ] Tab transitions con direction-aware slide
- [ ] Real-time: auto-prepend si scroll<200px, toast flotante si lejos
- [ ] Animations: useStaggerIn + pin bounce + official stamp + haptic feedback
- [ ] Reduced motion respetado (no animations harsh si prefers-reduced-motion)
- [ ] Theme dark + light coherente en todas las vistas (test toggle manualmente)
- [ ] Copy 100% latino neutro (sin "vosotros", sin "vos", sin "ustedes" peninsular)
- [ ] Build local Vite verde, manifest sin warnings
- [ ] Bundle size new pages combinado < 60KB gzip

### Backend
- [ ] `CommunityController::announce` impl funcional (ya no 501)
- [ ] `PushNotificationService::notifyCoachAnnounceToClients` implementado con chunks 100
- [ ] `PushSubscriptionController` con 4 actions (subscribe, unsubscribe, preferences, updatePreferences)
- [ ] `app/Console/Commands/PrecomputeCoachPulse.php` con `everyFiveMinutes()` schedule
- [ ] `routes/api.php` 5 rutas nuevas
- [ ] `routes/console.php` schedule entry
- [ ] Endpoint `GET /api/v/coach/clients/count?status=...&plan=...` para preview recipients (puede ir en CoachClientsController existente)
- [ ] Endpoint `GET /api/v/coach/community/threads` para Tab Conversaciones
- [ ] Endpoint `GET /api/v/coach/community/achievements?period=` para Tab Logros

### Testing
- [ ] 12 Pest tests verde (5 backend feature + 1 unit command + smoke E2E)
- [ ] 8 Vitest tests verde (5 composables happy + reset + dedup)
- [ ] No regresión Pest suite completa (`vendor/bin/pest --parallel`)
- [ ] Pint OK
- [ ] Smoke E2E manual: 7 scenarios documented arriba

### Operations
- [ ] Service worker registrado en `public/sw.js` (verificar ya existe del cliente; si no, scope coach)
- [ ] VAPID public key inyectado en blade `wc.blade.php` o `coach.blade.php` como `window.__WC_VAPID_PUBLIC_KEY`
- [ ] Cache namespace `wc:coach-pulse:v1:{coach_id}` precomputado para todos los coaches activos a los 5min
- [ ] No regresión Lighthouse: Performance ≥ 70 en `/coach/community`
- [ ] Console clean en /coach/community (no warnings, no errores 4xx/5xx)

### Documentación
- [ ] Spec doc commiteado (este archivo)
- [ ] Plan implementation doc en `docs/superpowers/plans/`
- [ ] CLAUDE.md actualizado con sección "Community Cross-Role Fase B"

---

## Risks & Mitigations

| Risk | Severity | Mitigation |
|------|----------|------------|
| Coach con 50+ clientes recibe spam de push notifications | Alto | Default `notify_post_created=false`, preferences UI prominente, bundle digest mode futuro |
| Auto-prepend si scroll en top puede saltar el post leyendo | Medio | Threshold 200px conservador. Si user scroll lento, eliminar auto-prepend tras 30s sin movimiento |
| Modal announce con count mal calculado (cliente filter cambió) | Medio | Confirmation step si recipients > 20 con desglose por plan |
| Push permission denied + coach no sabe re-habilitar | Medio | Page `/coach/notifications` con instrucciones browser-specific (Chrome, Safari, Firefox) |
| Tab transitions con bug en mobile (scroll position se pierde) | Medio | Cada tab guarda su scroll position en `keep-alive` con max 5 instances |
| Real-time WebSocket disconnect → coach no ve eventos nuevos | Alto | Echo auto-reconnect + on reconnect refetch todos los tabs activos |
| announce endpoint envía push a clientes que ya unsubscribed | Bajo | PushSubscription.active=false filter + try/catch on send con cleanup |
| ScheduleCoachPulse cron precompute satura DB con 100+ coaches | Medio | `withoutOverlapping(10)` + chunks de 50 coaches por run + onOneServer |
| Composable singleton cache leak entre impersonations | Alto | Reset hooks integrados en auth.setAuth/clearAuth (replicar pattern useGroupPulse) |
| Vue lazy import del Community.vue agrega 60KB+ al bundle | Medio | Dynamic import per-tab → cada tab es chunk independiente. Code splitting por tab |
| FAB 4ta opción rompe layout móvil con scroll | Bajo | Bottom sheet ya soporta overflow scroll |

---

## What's NOT in scope (intentional)

- **Cross-coach analytics dashboard** — es Fase C Admin Community Center
- **Live video/streaming** — fuera de spec original
- **Voice notes** — fuera de spec original
- **AI auto-moderation** — bias risk
- **Mention autocomplete cross-role** (`@cliente_X`) — es Fase D Cross-Role Layer
- **Threads cross-role badges in client view** — es Fase D
- **Notifications preferences general (cliente + admin)** — es Fase D
- **Public profile pages coach (linkable from posts)** — fuera de scope
- **Calendar view de actividad equipo** — fuera de scope, podría ser fase 5+

---

## Self-Review (post-write)

**1. Placeholder scan:** Sin TBDs ni TODOs ni "se decidirá luego". Los `// ... rest` en code samples son corte intencional para legibilidad — el plan llenará el resto.

**2. Internal consistency:**
- Architecture diagram matchea componentes listados ✅
- 5 composables matchean 5 reset hooks en auth.js mod ✅
- 5 tabs matchean 5 sub-tab components ✅
- Endpoints announce/push/preferences matchean controller methods + tests ✅
- Sidebar new section "Comunidad" + new item "Notificaciones" matchean nuevas rutas en router ✅

**3. Scope check:** Es una fase grande (~1 semana sprint) pero focada en Coach Hub. Se decompose en 30-40 tasks en el plan. NO incluye Admin (Fase C) ni Cross-Role (Fase D). Boundaries claros.

**4. Ambiguity check:**
- Real-time threshold 200px (no "near top" ambiguo)
- Cache TTL 25s frontend / 60s backend específicos
- Push permission dismissed 7 días (no "más tarde" ambiguo)
- 5MB max image (no "tamaño razonable")
- 200 chars max push body (no "breve")
- 5 sub-tabs orden explícito (Latido → Posts → Conversaciones → Pulsos → Logros)
- Default landing tab = Latido (no implicit)

**5. Premium quality check:**
- Stagger entry, haptic feedback, optimistic UI, animation library — sí ✅
- Mobile-first responsive sticky header tabs — sí ✅
- Dark + light coherent — sí ✅
- Latino neutro copy — sí ✅
- Service worker push native — sí ✅
- Vitest composable testing — sí ✅
- Real-time inteligente threshold-based — sí ✅
- Empty/loading/error states custom por tab — sí ✅
- Onboarding tour 4-pasos — sí ✅

**6. Backend extensions Fase B (clarificar):**
- `CommunityController::announce` impl: 501 → 201 con post+pin+broadcast
- `PushSubscriptionController` nuevo (4 actions)
- `CoachClientsController::count` (puede ya existir, validar)
- `CommunityController::threads` nuevo endpoint Tab Conversaciones
- `CommunityController::achievements` nuevo endpoint Tab Logros
- `PrecomputeCoachPulse` console command
- `PushNotificationService::notifyCoachAnnounceToClients` nuevo método

Spec listo para transición a writing-plans.
