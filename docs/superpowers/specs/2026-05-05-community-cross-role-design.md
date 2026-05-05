# Community Cross-Role — Conexión Cliente ↔ Coach ↔ Superadmin

> **Status:** Awaiting user review before transition to writing-plans
> **Author:** Daniel Esparza + Claude Opus 4.7
> **Date:** 2026-05-05
> **Mode:** Autónomo, sin MVP — versión completa

---

## Goal

Cerrar el gap descubierto por la auditoría: el coach hoy NO ve la actividad de comunidad de sus clientes y el superadmin NO puede comunicarse masivamente con ellos. Construir tres features integradas que conectan los tres roles en un solo sistema coherente:

1. **Coach Community Hub** — el coach ve, modera y participa en la comunidad de SUS clientes.
2. **Admin Community Center** — el superadmin ve cross-coach analytics, modera, y broadcast a clientes/coaches.
3. **Cross-Role Communication Layer** — mentions, threads cross-role, presencia, notificaciones unificadas.

---

## Estado actual (auditoría 2026-05-05)

### Lo que YA existe y reutilizamos

**Sistema community sólido:**
- 6 tablas (`community_posts`, `post_reactions`, `post_comments`, `client_pulsos`, `client_pulso_views`, `client_pulso_reactions`)
- Coach-scoped via `community_posts.coach_admin_id` + 3-fallback (`client_coach.admin_id` → `clients.coach_id` → `assigned_plans.assigned_by` → `coach_messages.coach_id`)
- 7 broadcast events Reverb funcionales (`PostReactionToggled`, `CommentAdded`, `NewMessageSent`, `ChatReactionToggled`, `NotificationReceived`, `UserPresenceUpdated`, `UserTyping`)
- Push notifications real con WebPush + VAPID (`PushNotificationService` con 8 métodos)
- 2 sistemas notifications: `community_notifications` (nueva) + `notifications` (legacy vanilla, dual-write)

**Coach portal funcional:**
- 23 rutas, sidebar con 5 secciones, mobile bottom nav, FAB sheet
- Mensajes 1-a-1 (`CoachMessage`) + broadcast a clientes (`CoachController::broadcast`)
- Plan tickets, kanban clientes, check-ins review, food photos review, brand profile, strategy hub

**Admin portal funcional:**
- 23 páginas Vue, `LiveFeed` con 10 tipos de eventos (incluye community posts), `ChatAnalytics`, `ClientDetail::activity`, `AuditLog`
- Coach impersonation (superadmin only) con `ImpersonationLog` chained
- `AdminController::feed` con 5 tipos de eventos cross-platform

### Gaps críticos detectados

**Coach NO tiene:**
- Visibilidad de posts/PRs/achievements de sus clientes (cero feed/timeline community)
- Notificaciones de actividad community (solo plan-tickets y mensajes 1-a-1)
- Privilegios de moderación (no puede pin/delete/marcar)
- Capacidad de postear como "voz del coach" en la comunidad
- Vista de pulsos de clientes (las stories 24-48h)

**Admin NO tiene:**
- Capacidad de broadcast a clientes (solo coach lo tiene)
- Capacidad de broadcast a coaches
- Dashboard de community engagement por coach (sólo posts individuales en LiveFeed plano)
- Moderation queue (posts reportados, threads conflictivos)
- Posts oficiales WellCore que aparecen en todas las comunidades

**Cross-role missing:**
- Sin sistema de mentions (`@cliente`, `@coach`, `@admin`)
- Sin presence visible cross-rol ("Coach está activo ahora")
- Sin reply diferenciado por rol con badges visuales
- Sin audit log de acciones moderación coach
- Push notifications solo a clientes — coaches no las reciben

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     CLIENTE (existente)                     │
│  Dashboard · Latido del Grupo · Comunidad Tabs              │
│  Posts · Pulsos · Reacciones · Comentarios                  │
└──────────┬──────────────────────────────────────────┬───────┘
           │                                          │
           │ posts/reactions/comments                 │ mentions/replies
           ▼                                          ▼
┌─────────────────────────┐              ┌──────────────────────────┐
│  COACH COMMUNITY HUB    │ ◄─pin/del──► │ NOTIFICATIONS UNIFIED    │
│  (NEW — feature 1)      │              │ (extended)               │
│                         │              │  · Push a coaches NEW    │
│  Tab Latido Equipo     │              │  · Mentions cross-role   │
│  Tab Posts (modera)    │              │  · Granular preferences  │
│  Tab Conversaciones    │              │                          │
│  Tab Pulsos            │              └──────────────────────────┘
│  Tab Logros            │
│  Botón Mensaje Equipo  │              ┌──────────────────────────┐
└──────────┬──────────────┘              │  REVERB CHANNELS NEW     │
           │                              │  · coach.{id}.community  │
           │ moderation actions/audit     │  · admin.community       │
           ▼                              │  · community.global      │
┌─────────────────────────┐              └──────────────────────────┘
│ ADMIN COMMUNITY CENTER  │
│ (NEW — feature 2)       │              ┌──────────────────────────┐
│                         │              │  AUDIT & MODERATION      │
│  Tab Pulse Cross-Coach │ ◄─audit────► │  · post_reports          │
│  Tab Live Feed Filt.   │              │  · pinned_posts          │
│  Tab Broadcast Center  │              │  · moderation_actions    │
│  Tab Moderation Queue  │              │  · broadcast_messages    │
│  Tab Analytics Coach   │              │                          │
└─────────────────────────┘              └──────────────────────────┘
```

### Componentes nuevos por capa

**Backend (8 archivos nuevos + 5 modificados):**

```
app/Services/
├── CoachCommunityService.php             [NEW] feed agregado para coach
├── AdminCommunityService.php             [NEW] cross-coach analytics
├── BroadcastService.php                  [NEW] mass messaging engine
├── ModerationService.php                 [NEW] pin/report/delete/audit
└── MentionResolverService.php            [NEW] parse + dispatch mentions

app/Http/Controllers/Api/
├── Coach/CommunityController.php         [NEW] coach community endpoints
├── Coach/ModerationController.php        [NEW] pin/delete/official posts
├── Admin/CommunityController.php         [NEW] cross-coach dashboard
├── Admin/BroadcastController.php         [NEW] broadcast send + history
├── Admin/ModerationQueueController.php   [NEW] reports inbox
└── PostReportController.php              [NEW] cliente reporta post

app/Models/
├── PinnedPost.php                        [NEW]
├── PostReport.php                        [NEW]
├── ModerationAction.php                  [NEW] audit log moderación
├── BroadcastMessage.php                  [NEW] audit broadcast sent
├── CoachNotificationPreference.php       [NEW] toggles coach push
└── PostMention.php                       [NEW] @mentions

app/Events/                               [NEW broadcast events]
├── CoachCommunityActivity.php
├── PostPinned.php
├── PostReported.php
├── PostMadeOfficial.php
├── BroadcastSent.php
└── MentionCreated.php

app/Policies/
└── CommunityPostPolicy.php               [NEW] canModerate/canPin/canMakeOfficial

database/migrations/                      [aditivas, sin destructivas]
├── 2026_05_05_create_pinned_posts.php
├── 2026_05_05_create_post_reports.php
├── 2026_05_05_create_moderation_actions.php
├── 2026_05_05_create_broadcast_messages.php
├── 2026_05_05_create_coach_notification_preferences.php
├── 2026_05_05_create_post_mentions.php
└── 2026_05_05_extend_community_posts_official.php  # add is_official + author_type + pinned_at
```

**Frontend (12 componentes Vue nuevos + 4 modificados):**

```
resources/js/vue/pages/Coach/
├── Community.vue                         [NEW] hub principal coach con tabs
├── community/
│   ├── CoachLatidoTab.vue                [NEW] equivalente coach del Latido
│   ├── CoachPostsTab.vue                 [NEW] feed con poderes moderación
│   ├── CoachConversacionesTab.vue        [NEW] threads recientes
│   ├── CoachPulsosTab.vue                [NEW] pulsos activos clientes
│   └── CoachLogrosTab.vue                [NEW] logros + shout-outs
├── BroadcastCenter.vue                   [MODIFY] agregar segmentación por status/plan

resources/js/vue/pages/Admin/
├── Community.vue                         [NEW] hub principal admin
├── community/
│   ├── AdminPulseCrossCoachTab.vue       [NEW] dashboard analytics
│   ├── AdminCommunityFeedTab.vue         [NEW] live feed filtrado
│   ├── AdminBroadcastTab.vue             [NEW] broadcast center
│   └── AdminModerationQueueTab.vue       [NEW] reportes inbox

resources/js/vue/components/community/
├── CoachBadge.vue                        [NEW] badge "Coach" en reacciones/comentarios
├── OfficialBadge.vue                     [NEW] badge "WellCore" en posts oficiales
├── PinnedIndicator.vue                   [NEW] icono fijado
├── ReportPostMenu.vue                    [NEW] menú reportar
└── MentionInput.vue                      [NEW] @mention autocomplete

resources/js/vue/composables/
├── useCoachCommunity.js                  [NEW] composable coach
├── useAdminCommunity.js                  [NEW] composable admin
├── useBroadcast.js                       [NEW] broadcast send + history
├── useModeration.js                      [NEW] pin/report/delete actions
└── useMentions.js                        [NEW] parse + autocomplete

resources/js/vue/layouts/CoachLayout.vue  [MODIFY] agregar item Comunidad
resources/js/vue/router/index.js          [MODIFY] /coach/community + /admin/community
resources/js/vue/pages/Client/CommunityFeed.vue [MODIFY] @mention support + report menu
```

---

## Feature 1: Coach Community Hub

### Tab "Latido del Equipo" (mirror coach del Latido cliente)

Reutiliza `GroupPulseAggregator` con scope=coach (no scope=client). Diferencias:

- Sin `userVsGroup` — el coach no se compara consigo mismo
- **Top 3 performers** del equipo (ordenado por workouts/PRs últimos 7d)
- **Riesgo de churn** — clientes con 0 actividad últimos 5d marcados rojo
- **Momento más activo del día** — heatmap hour×dayOfWeek
- **BPM "del equipo"** — promedio actividad de TODOS sus clientes (no individual)

Endpoint nuevo: `GET /api/v/coach/community/pulse` — devuelve shape similar a `summary` pero con keys adicionales `top_performers`, `at_risk_clients`, `most_active_hour`, `team_health_score`.

Cache: `wc:coach-pulse:v1:{coach_id}` TTL 60s. Precompute via scheduled command paralelo al de cliente.

### Tab "Posts" (feed completo + moderación)

Lista paginada (load-more igual que cliente) de TODOS los posts de SUS clientes. Por cada post el coach puede:

- **Reaccionar** (con badge "Coach" amarillo en la reacción)
- **Comentar** (badge "Coach" en el comentario, prioridad alfa: aparece arriba)
- **Pin** — fija al tope del feed cliente por 7 días default (configurable)
- **Eliminar** — soft delete (`visible=false`), entry en `moderation_actions` con razón
- **Hacer oficial** — convierte el post a "voz del coach" (badge "Coach Pick" + aparece en feed cliente con destaque)
- **Mencionar a otro cliente** — `@cliente_carlos` → notif a Carlos
- **Asignar logro manual** — abre modal de achievements del cliente, marca uno como completado manualmente

Endpoint nuevo: `GET /api/v/coach/community/posts?filter=all|pinned|reported|achievements|prs` con paginación.

### Tab "Conversaciones" (threads recientes)

Vista cronológica de TODOS los comentarios en posts de sus clientes (últimos 7d, agrupados por post). Util para:

- Ver discusiones que arman entre clientes
- Detectar threads conflictivos
- Replicar como coach con badge

Endpoint: `GET /api/v/coach/community/threads?since=7d`.

### Tab "Pulsos" (stories activos del equipo)

Grid de pulsos activos (24-48h) de los clientes. Coach puede:

- Reaccionar (badge "Coach")
- Ver con prioridad — clientes que rompieron PR salen primero
- Compartir el pulso al feed (lo destaca como post oficial)

Endpoint: `GET /api/v/coach/community/pulsos?status=active`.

### Tab "Logros del equipo" (achievements + shout-outs)

Lista de achievements + PRs últimos 7d. Coach puede:

- "Felicitar" — crea un comentario auto en el post original con texto custom + badge Coach
- "Compartir al equipo" — crea post oficial "Felicitaciones a Carlos por su PR de Sentadilla" en el feed cliente
- Ver clientes con MÁS achievements del mes (gamificación interna)

Endpoint: `GET /api/v/coach/community/achievements?period=week|month`.

### Botón flotante "Mensaje al equipo"

Modal con dos opciones:

- **Anuncio en feed** — crea post oficial con badge "Coach" en el feed de la comunidad. Aparece pinned 24h.
- **Push notification** — envía push a todos los clientes activos. Reusa `PushNotificationService::sendBatch`.

Endpoint: `POST /api/v/coach/community/announce` con `{type: 'post'|'push', message, pin_hours}`.

### Notificaciones para coach (push + in-app)

Nuevo modelo `CoachNotificationPreference` con toggles:

- `notify_pr_broken` (default true) — cliente rompió PR
- `notify_streak_milestone` (default true) — cliente llegó a 7/30/100 días
- `notify_post_created` (default false, ruidoso) — cliente posteó
- `notify_comment_on_my_reply` (default true) — alguien comentó después del coach
- `notify_at_risk_client` (default true) — cliente con 5d sin actividad
- `notify_official_post_engagement` (default true) — cliente reacciona a post oficial

Endpoint: `PATCH /api/v/coach/preferences/notifications`.

`PushNotificationService` extendido con métodos coach:
- `notifyCoachClientPr()`
- `notifyCoachClientStreakMilestone()`
- `notifyCoachAtRiskClient()`
- `notifyCoachClientPostNeedsResponse()`

Push subscriptions: nueva tabla `coach_push_subscriptions` (paralela a `push_subscriptions`).

---

## Feature 2: Admin Community Center

### Tab "Pulse Cross-Coach" (analytics dashboard)

Dashboard ejecutivo para el superadmin con:

- **Tabla coaches × engagement metrics**:
  - Posts/día (cliente del coach)
  - Engagement rate (reactions / posts) últimos 7d
  - Response time del coach a comentarios cliente (median, p95)
  - Clientes activos (5+ posts mes) vs total
  - Top 3 clientes contribuyentes
- **Gráficas**:
  - Series tiempo: posts/día últimos 30d (todas comunidades)
  - Heatmap actividad coach × hora día
  - Funnel: % clientes que postean / reaccionan / comentan
- **Alertas**:
  - Coach sin actividad en su community 7+ días
  - Cliente con 10+ posts/día (posible spam)
  - Thread con 50+ comentarios (revisar)

Endpoint: `GET /api/v/admin/community/pulse-cross-coach?period=week|month|all`.

### Tab "Live Feed Community" (extender LiveFeed)

LiveFeed actual ya tiene `community` como filter type. Extensión:

- Añadir filtro **por coach** — selecciona coach del dropdown, ve solo posts de SUS clientes
- Añadir filtro **por reaction type** — solo "achievement", "pr"
- Real-time via `admin.community` channel (broadcast events `CoachCommunityActivity`)

Modificación en `app/Livewire/Admin/LiveFeed.php` + `resources/js/vue/pages/Admin/LiveFeed.vue` (ya existen ambas implementaciones — extendemos las dos).

### Tab "Broadcast Center" (mass messaging)

Tres tipos de broadcast:

1. **A clientes** — mensaje + push opcional. Segmentación:
   - Por plan (RISE/Elite/Esencial/Método/Presencial)
   - Por status (Activo/Inactivo/Pendiente)
   - Por coach asignado
   - Por días desde último login (engagement targeting)
2. **A coaches** — mensaje a todos los coaches o subset
3. **Post oficial WellCore** — aparece en TODAS las comunidades de TODOS los coaches con badge "WellCore Team"

Tabla `broadcast_messages` registra:
- `sender_type` (admin), `sender_id`, `audience_type` (clients|coaches|all_communities), `segment` (json), `subject`, `body`, `push_enabled` (bool), `recipients_count`, `sent_at`

Endpoint: `POST /api/v/admin/broadcast/send`.

UI: form con preview de recipients count en vivo (count antes de send) + dry-run.

### Tab "Moderation Queue" (reports inbox)

Posts reportados por clientes (nueva feature `PostReport`). Cliente reporta vía menu en post: "Reportar — spam / ofensivo / off-topic". Ese post entra al queue admin.

Admin ve:
- Lista paginada por urgency (recents primero, multi-reported destacado)
- Click en post → ve full thread + reportes + reportadores
- Acciones: dismiss / hide post / ban client (temporal)
- Audit en `moderation_actions`

Endpoint: `GET /api/v/admin/community/moderation/queue` + `POST /api/v/admin/community/moderation/{post_id}/action`.

### Tab "Analytics Coach" (drill-down individual)

Click en un coach desde Pulse Cross-Coach → vista detallada:
- Su community completa (todos posts, todos comentarios)
- Gráfica engagement últimos 90d
- Lista clientes con bandera roja (at-risk, sin actividad, etc.)
- Botón "Mensaje al coach" (1-a-1 admin → coach)
- Botón "Impersonar coach" (existe ya, integrar)

Endpoint: `GET /api/v/admin/community/coaches/{coach_id}/analytics`.

---

## Feature 3: Cross-Role Communication Layer

### Mentions (@cliente, @coach, @admin)

En cualquier post o comentario, syntax `@nombre` o `@cliente_id` resuelve a un usuario. Disparos:

- `@cliente_carlos` en post → notifs in-app + push a Carlos
- `@coach` (sin nombre) → notifs al coach del cliente que postea
- `@admin` o `@wellcore` → notifs al admin (queue moderación)

Tabla `post_mentions`:
- `id`, `post_id` (nullable), `comment_id` (nullable), `mentioned_user_type` (client|coach|admin), `mentioned_user_id`, `mentioner_user_type`, `mentioner_user_id`, `created_at`

Frontend: `MentionInput.vue` con autocomplete cuando se escribe `@`. Resuelve via endpoint `GET /api/v/community/mention-search?q=`.

### Threads cross-role con badges

Cuando un coach o admin comenta, el comentario:

- Muestra badge visual ("Coach" amarillo, "WellCore" rojo)
- Aparece **arriba** de comentarios de clientes en el thread
- Notifica al post owner + cualquier mentioned user

Modificación en `community_posts` y `post_comments`:
- Add column `author_type` (enum: 'client'|'coach'|'admin', default 'client')
- Add column `author_admin_id` (nullable, para coach/admin posts/comments)

### Real-time presence cross-role

Reverb `online-users` channel ya existe (PresenceChannel). Extender con:

- En coach community hub, mostrar "X clientes activos ahora"
- En admin community center, mostrar coaches online + clientes online (separados)
- En cliente, "Tu coach está conectado" (si coach está en presence channel)

Frontend: `useGroupPresence.js` composable que escucha `online-users` y filtra por rol.

### Notificaciones unificadas con preferencias granular

Nueva tabla `notification_preferences` (paralela a `coach_notification_preferences` pero general):

- `user_type`, `user_id`, `notification_type`, `channel` (push|in_app|both|off), `enabled`

Page nueva en cada portal: "Preferencias de notificaciones" con matriz tipo × canal.

### Audit log de moderación

Nueva tabla `moderation_actions`:
- `id`, `actor_type` (admin|coach), `actor_id`, `action_type` (pin|unpin|delete|make_official|dismiss_report|hide), `target_type` (post|comment), `target_id`, `reason` (string nullable), `metadata` (json), `created_at`

Visible en admin AuditLog con filter type=moderation.

---

## Data Flow

### Escenario 1: Cliente postea PR

```
Cliente posts PR → CommunityPost created → SocialController::communityCreate
  ├─→ broadcast PostReactionToggled (channel community-post.{id})
  ├─→ broadcast CoachCommunityActivity (channel coach.{coach_id}.community) [NEW]
  ├─→ broadcast AdminCommunityActivity (channel admin.community) [NEW]
  ├─→ insert into community_notifications (post owner has none)
  ├─→ if coach has notify_pr_broken=true:
  │     └─→ PushNotificationService::notifyCoachClientPr [NEW METHOD]
  │           └─→ push + in-app notification al coach
  └─→ if mentions detected via MentionResolverService:
        └─→ post_mentions inserted, push notif a mentioned users
```

### Escenario 2: Coach hace post oficial

```
Coach calls POST /api/v/coach/community/announce {type:'post', message, pin_hours:24}
  ├─→ CoachCommunityController::announce
  ├─→ CommunityPost created with author_type='coach', author_admin_id=coach_id, is_official=true
  ├─→ PinnedPost inserted (pinned_until = now + 24h)
  ├─→ broadcast PostMadeOfficial (channel community-post.{id} + admin.community)
  ├─→ broadcast PostPinned (channel coach.{coach_id}.community)
  ├─→ ModerationAction logged (type=make_official + pin)
  └─→ all clients of coach: push notification "Tu coach publicó un anuncio" (if their pref enabled)
```

### Escenario 3: Admin broadcast a todos los coaches

```
Admin calls POST /api/v/admin/broadcast/send {audience:'coaches', message, push:true}
  ├─→ BroadcastController::send
  ├─→ BroadcastService::dispatchToCoaches
  ├─→ for each coach:
  │     ├─→ CoachMessage created (direction='admin_to_coach')
  │     └─→ if push pref enabled: PushNotificationService::notifyCoachBroadcast
  ├─→ BroadcastMessage row inserted (audit trail)
  └─→ broadcast BroadcastSent (channel admin.community) for live confirmation
```

---

## Privacy & Authorization

### Coach scope (estricto)

Coach solo accede a:
- Posts donde `coach_admin_id = coach.id` (vía `community_posts`)
- Comentarios en esos posts
- Pulsos de clientes en `client_coach.admin_id = coach.id`
- Mensajes en `coach_messages.coach_id = coach.id`

Nuevo middleware `CoachScopeMiddleware` que inyecta scope automático en todas las queries `Coach\*Controller`.

Policy `CommunityPostPolicy`:
- `canModerate(Admin $coach, CommunityPost $post)` → `$post->coach_admin_id === $coach->id`
- `canPin($coach, $post)` → mismo + role=coach
- `canMakeOfficial($coach, $post)` → mismo + role=coach
- `canDelete($coach, $post)` → mismo + (role=coach OR role=admin)

### Admin override

Admin (role=admin|superadmin) puede:
- Ver community de cualquier coach (pasa scope coach via query param `?coach_id=`)
- Pin/delete cualquier post (override policy)
- Crear post oficial WellCore que aparece en TODAS las comunidades (`is_global=true`)

### Cliente reportes

Cliente solo puede reportar posts visibles en SU comunidad (scope normal). El reporte va a `post_reports` con status=pending, no afecta visibilidad inmediatamente.

### Admin impersonation extendido

`AdminImpersonateController` actual soporta coaches solo para superadmin. Mantener. Logged via `ImpersonationLog`.

---

## Real-Time Channels (Reverb)

### Nuevos channels

```php
// routes/channels.php (extender)

// Coach escucha actividad de SUS clientes
Broadcast::channel('coach.{coachId}.community', function ($user, int $coachId) {
    if (!($user instanceof Admin)) return false;
    return $user->id === $coachId && in_array($user->role, ['coach', 'admin', 'superadmin']);
});

// Admin escucha actividad GLOBAL community
Broadcast::channel('admin.community', function ($user) {
    return $user instanceof Admin && in_array($user->role, ['admin', 'superadmin', 'jefe']);
});

// Posts oficiales globales (WellCore) - todos clientes lo reciben
Broadcast::channel('community.global', function ($user) {
    return true; // todos autenticados
});
```

### Nuevos events

```php
// CoachCommunityActivity — granular, por tipo
class CoachCommunityActivity implements ShouldBroadcast {
    public function __construct(
        public int $coachId,
        public string $eventType, // 'post_created' | 'pr_broken' | 'achievement' | 'comment'
        public int $clientId,
        public string $clientName,
        public array $payload, // shape depende de eventType
    ) {}

    public function broadcastOn() {
        return new PrivateChannel("coach.{$this->coachId}.community");
    }
}

// PostPinned, PostReported, PostMadeOfficial, BroadcastSent, MentionCreated
// (5 más, similar shape)
```

### Listeners to create

- `NotifyCoachOnClientActivity` — escucha events cliente, dispatch push si coach pref enabled
- `NotifyMentionedUsers` — escucha `MentionCreated`, push a mentioned

---

## Caching Strategy

### Coach pulse cache

Key: `wc:coach-pulse:v1:{coach_id}`
TTL: 60s
Precompute: `wellcore:precompute-coach-pulse` cada 5min para coaches con clientes activos

### Admin cross-coach analytics

Key: `wc:admin-community-analytics:v1:{period}` (period = day|week|month)
TTL: 300s (analytics no cambia tan rápido)
Refresh: lazy on miss + invalidate cuando broadcast se envía

### Coach community feed (posts paginados)

Key: `wc:coach-community-feed:v1:{coach_id}:{filter}:{page}`
TTL: 30s
Invalidate: cuando se crea/modifica post de cliente del coach (via event listener)

### Mention search autocomplete

Key: `wc:mention-search:v1:{query}` (query lowercased, max 3 chars min)
TTL: 600s
Per-user: no, search es público dentro del coach scope

---

## Migrations (todas aditivas — cumple CLAUDE.md)

```sql
-- 2026_05_05_create_pinned_posts.php
CREATE TABLE pinned_posts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT NOT NULL,
    pinned_by_type ENUM('coach', 'admin') NOT NULL,
    pinned_by_id INT NOT NULL,
    pinned_at TIMESTAMP NOT NULL,
    pinned_until TIMESTAMP NULL,
    note VARCHAR(255) NULL,
    INDEX idx_pinned_active (post_id, pinned_until),
    INDEX idx_pinned_by (pinned_by_id, pinned_by_type)
);

-- 2026_05_05_create_post_reports.php
CREATE TABLE post_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT NOT NULL,
    reporter_id INT NOT NULL,
    reason ENUM('spam', 'offensive', 'off_topic', 'other') NOT NULL,
    reason_detail VARCHAR(500) NULL,
    status ENUM('pending', 'dismissed', 'actioned') DEFAULT 'pending',
    reviewed_by_admin_id INT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reports_pending (status, created_at),
    INDEX idx_reports_post (post_id)
);

-- 2026_05_05_create_moderation_actions.php
CREATE TABLE moderation_actions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    actor_type ENUM('coach', 'admin') NOT NULL,
    actor_id INT NOT NULL,
    action_type ENUM('pin', 'unpin', 'delete', 'restore', 'make_official', 'dismiss_report', 'hide_for_review') NOT NULL,
    target_type ENUM('post', 'comment') NOT NULL,
    target_id BIGINT NOT NULL,
    reason VARCHAR(500) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mod_actor (actor_id, actor_type, created_at),
    INDEX idx_mod_target (target_type, target_id)
);

-- 2026_05_05_create_broadcast_messages.php
CREATE TABLE broadcast_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sender_type ENUM('admin', 'coach') NOT NULL,
    sender_id INT NOT NULL,
    audience_type ENUM('clients', 'coaches', 'all_communities', 'segmented') NOT NULL,
    segment_filter JSON NULL,
    subject VARCHAR(255) NULL,
    body TEXT NOT NULL,
    push_enabled BOOLEAN DEFAULT FALSE,
    recipients_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_broadcast_sender (sender_type, sender_id),
    INDEX idx_broadcast_sent (sent_at)
);

-- 2026_05_05_create_coach_notification_preferences.php
CREATE TABLE coach_notification_preferences (
    coach_id INT PRIMARY KEY,
    notify_pr_broken BOOLEAN DEFAULT TRUE,
    notify_streak_milestone BOOLEAN DEFAULT TRUE,
    notify_post_created BOOLEAN DEFAULT FALSE,
    notify_comment_on_my_reply BOOLEAN DEFAULT TRUE,
    notify_at_risk_client BOOLEAN DEFAULT TRUE,
    notify_official_post_engagement BOOLEAN DEFAULT TRUE,
    notify_admin_broadcast BOOLEAN DEFAULT TRUE,
    push_enabled BOOLEAN DEFAULT TRUE,
    in_app_enabled BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2026_05_05_create_post_mentions.php
CREATE TABLE post_mentions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT NULL,
    comment_id BIGINT NULL,
    mentioner_type ENUM('client', 'coach', 'admin') NOT NULL,
    mentioner_id INT NOT NULL,
    mentioned_type ENUM('client', 'coach', 'admin') NOT NULL,
    mentioned_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mention_target (mentioned_type, mentioned_id, created_at),
    INDEX idx_mention_post (post_id),
    INDEX idx_mention_comment (comment_id)
);

-- 2026_05_05_create_coach_push_subscriptions.php
CREATE TABLE coach_push_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    coach_id INT NOT NULL,
    endpoint VARCHAR(500) NOT NULL,
    p256dh TEXT NOT NULL,
    auth_key TEXT NOT NULL,
    user_agent VARCHAR(255) NULL,
    active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_coach_endpoint (coach_id, endpoint(255)),
    INDEX idx_coach_subs (coach_id, active)
);

-- 2026_05_05_extend_community_posts_official.php (con guards Schema::hasColumn)
ALTER TABLE community_posts
    ADD COLUMN author_type ENUM('client', 'coach', 'admin') DEFAULT 'client',
    ADD COLUMN author_admin_id INT NULL,
    ADD COLUMN is_official BOOLEAN DEFAULT FALSE,
    ADD COLUMN is_global BOOLEAN DEFAULT FALSE,
    ADD INDEX idx_posts_official (is_official, is_global, created_at);

-- 2026_05_05_extend_post_comments_author.php (con guards)
ALTER TABLE post_comments
    ADD COLUMN author_type ENUM('client', 'coach', 'admin') DEFAULT 'client',
    ADD COLUMN author_admin_id INT NULL,
    ADD INDEX idx_comments_author (author_type, author_admin_id);
```

---

## Testing Strategy

### Backend (Pest)

**Unit tests (services):**
- `CoachCommunityServiceTest` — feed paginated, filters, top performers, at-risk
- `AdminCommunityServiceTest` — cross-coach metrics, response time calc
- `BroadcastServiceTest` — segmentación correcta, count vs delivered, dry-run
- `ModerationServiceTest` — pin/unpin, audit log, policy enforcement
- `MentionResolverServiceTest` — parsing @cliente_X, autocomplete

**Feature tests (endpoints):**
- `CoachCommunityEndpointsTest` — auth, scope coach, paginación
- `AdminCommunityEndpointsTest` — superadmin scope override
- `BroadcastEndpointsTest` — segmentación, dry-run, recipients_count exacto
- `ModerationQueueTest` — flujo report → pending → actioned
- `MentionsEndpointTest` — autocomplete, mentions creadas, notifs disparadas

**Policy tests:**
- `CommunityPostPolicyTest` — coach solo modera sus posts, admin override

**Realtime tests (broadcast):**
- `CoachCommunityActivityBroadcastTest` — event dispatched correctly to channel
- `PostPinnedBroadcastTest`, etc.

### Frontend (Vitest, opcional)

Composables clave con tests unitarios:
- `useCoachCommunity` — fetch + cache
- `useBroadcast` — segment preview + send
- `useMentions` — parsing + autocomplete

### E2E (manual smoke después deploy)

- Coach login → Tab Comunidad aparece
- Coach ve post de cliente → click pin → cliente refresca y ve post pinned
- Coach pone reaction → cliente lo ve con badge "Coach"
- Admin Broadcast Center → envía a todos clientes → llega push
- Cliente reporta post → admin ve en queue → dismisses → audit log entry

---

## Rollout Phases

Sin MVP, pero implementación phased para reducir riesgo:

**Fase A — Backend foundations (sprint 1, ~1 semana)**
- Migrations 8 tablas nuevas + 2 ALTER aditivos
- 5 Services nuevos con tests Pest unit
- 5 Controllers Coach + 4 Controllers Admin con tests feature
- 1 Policy nueva
- 6 Events broadcast nuevos
- 2 Listeners nuevos
- Cache strategy (3 namespaces)

**Fase B — Coach Community Hub (sprint 2, ~1 semana)**
- 1 page Vue Coach/Community.vue + 5 sub-tabs
- 3 composables nuevos
- 3 components shared (badges, indicators)
- Scheduled commands (precompute coach pulse)
- Push notifications coach (extensión PushNotificationService)
- Smoke test prod

**Fase C — Admin Community Center (sprint 3, ~1 semana)**
- 1 page Vue Admin/Community.vue + 5 sub-tabs
- Extension de LiveFeed.vue con filtros
- 2 composables (admin community, broadcast)
- Modal segmentación broadcast con preview
- Audit log integration
- Smoke test prod

**Fase D — Cross-Role layer (sprint 4, ~1 semana)**
- Mentions: backend resolver + frontend MentionInput
- Threads cross-role badges UI
- Presence cross-role
- Notifications preferences page (cliente + coach + admin)
- Migration `community_posts.author_type` + display badges
- Smoke test prod

**Total estimado: 4 sprints (~4 semanas)** trabajando con agentes Laravel especializados (la-02-backend, la-03-vue3, la-12-realtime, la-14-testing) en paralelo.

---

## Definition of Done

Backend:
- [ ] 11 migrations aditivas aplicadas en local + test DB sin errores
- [ ] 5 services con tests Pest unit verde
- [ ] 9 controllers con tests Pest feature verde
- [ ] 1 policy con test
- [ ] 6 events broadcast registrados en `routes/channels.php`
- [ ] 2 listeners registrados en `EventServiceProvider`
- [ ] PushNotificationService extendido con 4 métodos coach
- [ ] BroadcastService funcional con segmentación + dry-run
- [ ] ModerationService con audit log completo

Frontend:
- [ ] Coach: Tab "Comunidad" en sidebar `CoachLayout.vue`
- [ ] `pages/Coach/Community.vue` con 5 sub-tabs funcionales
- [ ] Admin: Tab "Comunidad" en sidebar admin
- [ ] `pages/Admin/Community.vue` con 5 sub-tabs funcionales
- [ ] BroadcastCenter modal con segmentación live preview
- [ ] Cliente: `@mention` autocomplete + report menu en posts
- [ ] Cliente: badges "Coach" y "WellCore" en reacciones/comentarios
- [ ] Notification preferences page en cada portal
- [ ] Build local Vite verde, manifest sin warnings

Realtime:
- [ ] `coach.{coachId}.community` channel con auth correcta
- [ ] `admin.community` channel con role check
- [ ] `community.global` channel sin restricción
- [ ] 6 events broadcast verificados en network tab Reverb

Tests:
- [ ] 30+ tests Pest unit/feature verde
- [ ] Smoke test E2E prod con Chrome DevTools (todos los flujos)

Operations:
- [ ] Cache namespaces declarados (`wc:coach-pulse`, `wc:admin-community-analytics`, `wc:coach-community-feed`)
- [ ] Scheduled command `wellcore:precompute-coach-pulse` cada 5min
- [ ] No regresión Lighthouse Performance ≥ 70 en `/coach/community` y `/admin/community`
- [ ] Audit log capturando 100% de acciones moderación

Documentación:
- [ ] Spec doc commiteado (este archivo)
- [ ] Plan implementation doc en `docs/superpowers/plans/`
- [ ] CLAUDE.md actualizado con sección "Community Cross-Role"

---

## Risks & Mitigations

| Risk | Severity | Mitigation |
|------|----------|------------|
| Coach feed query lento con 50+ clientes | Alto | Cache 30s + precompute cada 5min + indexes en `community_posts.coach_admin_id` (verificar que existe) |
| Broadcast envía 27+ clientes simultáneo, satura Wompi/email externo | Medio | Chunked send (100 por batch) + queue Laravel con throttle |
| Reverb broadcast saturation con N coaches × M eventos | Medio | Granular channels (`coach.{id}.community` separados) — cada coach solo recibe los suyos |
| Push notifications spam ahogan al coach | Medio | Default toggles conservadores (post_created=false), preferences UI prominente |
| Mentions XSS via username | Bajo | Sanitizar via Vue auto-escape + backend allowlist regex `[a-zA-Z0-9_]` |
| Coach abusa privilegios (delete posts injustamente) | Medio | `moderation_actions` audit log + admin queue review |
| Migration de `community_posts.author_type` rompe queries existentes | Alto | Default 'client' cubre todos los rows existentes — no breaks |
| Realtime channel auth bypass (coach espía otra community) | Alto | Tests específicos `tests/Feature/Channels/CoachCommunityChannelAuthTest` |
| Cache stampede on broadcast | Medio | `Cache::lock()` en analytics queries |

---

## What's NOT in scope (intentional)

A pesar de "sin MVP", algunas cosas SÍ las dejo fuera porque son features completos separados:

- **Live video/streaming** — no relevante para community textual/photo
- **Voice notes en chat** — fase 5+ si se valida
- **AI moderation automática** — bias risk, prefer human-in-the-loop
- **Encrypted DMs entre clientes** — privacy más compleja, otro proyecto
- **Marketplace de challenges** entre coaches — alcance B2B no validado aún
- **Community gamification leaderboard global** — privacidad cross-coach
- **External integrations** (Slack/Discord notif) — out of platform scope

---

## Open Questions (none — autonomous mode)

El usuario aprobó "autónomo, sin MVP, la mejor opción". Las decisiones de alcance están tomadas y justificadas en cada sección. Si tras revisar este spec quiere ajustar (ej. quitar mentions, cambiar audience del broadcast, simplificar moderation queue), aplicamos los cambios antes de transición a writing-plans.

---

## Self-Review (post-write)

**Placeholder scan:** sin TBDs ni TODOs ni "se decidirá luego".
**Internal consistency:** arquitectura ASCII matchea descripciones por feature, migrations matchean los models y services referenciados.
**Scope check:** muy grande — 4 sprints. Pero está decompuesto en fases A-D que pueden mergearse independientemente. Cada fase produce sub-feature shippable.
**Ambiguity check:** decisiones tomadas explícitamente (ej. coach `notify_post_created` default false, broadcast chunked 100, mention regex `[a-zA-Z0-9_]`).
