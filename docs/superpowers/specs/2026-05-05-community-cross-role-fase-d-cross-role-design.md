# Community Cross-Role — Fase D: Cross-Role Communication Layer Design

> **Status:** Awaiting user review before transition to writing-plans
> **Author:** Daniel Esparza + Claude Opus 4.7
> **Date:** 2026-05-05
> **Mode:** Autonomous, premium UX/UI multi-portal
> **Dependency:** Fase A backend foundations (PostMention model + resolver service ya existen)
> **Best after:** Fases B y C completed (uses CoachBadge + OfficialBadge components)

---

## Goal

Conectar los tres portales (cliente / coach / admin) con la **capa de comunicación cross-role**. Es el cierre del proyecto Community Cross-Role:

1. **Mentions con autocomplete** — `@cliente_X`, `@coach`, `@admin` en posts y comments
2. **Threads cross-role con badges visuales** — cuando coach o admin comenta, aparece arriba con badge correspondiente
3. **ReportPostMenu cliente** — menu reporte de posts con razones (spam/offensive/off_topic/other)
4. **GroupPresence cross-role** — composable que filtra `online-users` channel por rol
5. **Notifications preferences cliente** — page `/client/notifications` con toggles + tabla `client_notification_preferences`
6. **Mention search endpoint** — `GET /api/v/community/mention-search?q=`

Al final de Fase D los tres portales hablan entre sí con UX coherente: cliente menciona coach → coach recibe push + in-app; admin postea oficial → todos clientes lo ven con badge; cliente reporta → admin queue (Fase C) lo recibe.

---

## Estado actual

### Backend disponible (Fase A)

```
Models:
  PostMention (post_id, comment_id, mentioner_*, mentioned_*)
  PostReport

Services:
  MentionResolverService::extract($body)
  MentionResolverService::persistForPost($post, $mentionerType, $mentionerId)
  MentionResolverService::searchMentionTargets($query, $scopeCoachId)

Events:
  MentionCreated dispatched cuando mentions creadas
  PostReported endpoint POST /api/v/community/posts/{id}/report

Reverb:
  user.{type}.{id} channel para MentionCreated targeted
```

### Frontend disponible

- Components Fase B: CoachBadge.vue, OfficialBadge.vue (creados Fase B Task 14)
- Client/CommunityFeed.vue — feed sin mentions ni report menu
- components/community/CommentsThread.vue — sin distinción por author_type

### Gaps Fase D

- No existe MentionInput.vue (textarea wrapper con autocomplete)
- No existe endpoint GET /api/v/community/mention-search
- No existe ReportPostMenu.vue cliente
- No existe useGroupPresence.js composable
- No existe tabla client_notification_preferences
- No existe page /client/notifications
- CommunityFeed cliente NO renderiza badges en comments
- CommentsThread NO ordena coach/admin comments arriba

---

## Decisiones de diseño (autónomas, premium)

| # | Decisión | Justificación |
|---|---------|---------------|
| 1 | MentionInput.vue como wrapper de textarea (no replace) | Drop-in replacement sin breaking changes |
| 2 | Autocomplete dropdown SQL search por prefix con scope coach (cliente) o all (admin) | Server-side scope ya en Fase A |
| 3 | 3 tipos: `@cliente_X` (id), `@nombre` (search), `@coach`/`@admin`/`@wellcore` (special tokens) | Regex `/@(cliente_\d+|coach|admin|wellcore|[a-zA-Z0-9_]+)/` |
| 4 | Mention rendering: chips clickables → router push profile | Pattern Twitter/Instagram |
| 5 | Coach/admin comments aparecen ARRIBA del thread | Backend scopes byCoach/byAdmin ya existen |
| 6 | CoachBadge y OfficialBadge reutilizados de Fase B | Components ya existen |
| 7 | ReportPostMenu menu en cada post (own o ajeno) | Backend dedup ya en Fase A |
| 8 | Modal report: 4 razones predefinidas + textarea opcional | Razones enum Fase A |
| 9 | GroupPresence singleton con reactive Map por rol | Patrón useGroupPulse |
| 10 | Tabla client_notification_preferences NUEVA aditiva | Mantener 3 tablas separadas (coach, admin, cliente) |
| 11 | Page /client/notifications | Cierra simetría 3 portales |
| 12 | Endpoint mention-search con scope dinámico según rol caller | Coach: sus clientes · Admin: todos · Cliente: clientes mismo coach |
| 13 | Mention rendering visual con chips colored por tipo (cliente=blue, coach=amber, admin=red) | Distinción visual rápida |
| 14 | Cross-role notification flow ya implementado en Fase A | Frontend agrega NotificationBell pulse al recibir MentionCreated |
| 15 | Composable useMentions: extract + render + autocomplete API | Reusable across components |
| 16 | No tocar admin LiveFeed para badges | Fase C ya integra contexts |
| 17 | Security: regex sanitize backend allowlist | Fase A ya rechaza malformed |
| 18 | Mention search: si query <3 chars, no API call | Reduce noise |

---

## Architecture

```
┌────────────────────────────────────────────────────────────┐
│              Cross-Role Communication Layer                │
│                                                            │
│  Cliente CommunityFeed.vue (modified):                     │
│   ├── PostComposer → uses MentionInput                     │
│   ├── PostCard → renders mentions + ReportPostMenu         │
│   └── CommentsThread → CoachBadge/OfficialBadge inline     │
│                                                            │
│  Coach Hub Fase B: ya integra CoachBadge                   │
│  Admin Hub Fase C: ya render badges                        │
│                                                            │
│  Components compartidos:                                    │
│   - MentionInput.vue (NEW — used 3 portales)               │
│   - ReportPostMenu.vue (NEW — cliente only)                │
│   - MentionRenderer.vue (NEW — render chips)               │
│   - OnlineRoleIndicator.vue (NEW — pres dot indicator)     │
│                                                            │
│  Composables:                                               │
│   - useMentions (extract + autocomplete)                    │
│   - useGroupPresence (Echo PresenceChannel filter)          │
│                                                            │
│  Backend:                                                   │
│   - GET /api/v/community/mention-search                     │
│   - Migration client_notification_preferences (aditiva)     │
│   - ClientNotificationPreference model                      │
│   - Client\NotificationPreferencesController                │
│                                                            │
│  Real-time:                                                 │
│   - user.{type}.{id} → MentionCreated (Fase A existing)    │
│   - online-users PresenceChannel (Fase A existing)          │
└────────────────────────────────────────────────────────────┘
```

### File Map (Fase D)

#### Frontend new files (8)

```
resources/js/vue/components/community/
├── MentionInput.vue
├── MentionRenderer.vue
├── ReportPostMenu.vue
└── OnlineRoleIndicator.vue

resources/js/vue/pages/Client/
└── NotificationsPreferences.vue

resources/js/vue/composables/
├── useMentions.js
└── useGroupPresence.js
```

#### Frontend modified files (4)

```
resources/js/vue/pages/Client/CommunityFeed.vue
resources/js/vue/components/community/CommentsThread.vue
resources/js/vue/router/index.js
resources/js/vue/stores/auth.js
```

#### Backend new files (4)

```
app/Http/Controllers/Api/MentionSearchController.php
app/Http/Controllers/Api/Client/NotificationPreferencesController.php
app/Models/ClientNotificationPreference.php
database/migrations/2026_05_05_000011_create_client_notification_preferences_table.php
```

#### Backend modified files (1)

```
routes/api.php  (3 nuevas rutas)
```

#### Tests new files (6)

```
tests/Unit/Composables/
├── useMentions.test.js
└── useGroupPresence.test.js

tests/Feature/
├── MentionSearchEndpointTest.php
└── ClientNotificationPreferencesTest.php

tests/Feature/Community/
├── CrossRoleBadgesRenderingTest.php
└── PostReportFlowE2ETest.php
```

---

## Components detalle

### MentionInput.vue

Drop-in wrapper de textarea con autocomplete dropdown. API similar a `<input v-model>`:

```html
<MentionInput v-model="content" placeholder="Escribe..." :max-length="500"
              :scope="'coach-team'" @mention="onMention" />
```

**Internal logic**:
- Tracks cursor position
- Detecta `@` typed + 0-50 chars after (regex `/@([a-zA-Z0-9_]{0,50})$/`)
- Si 3+ chars, llama `useMentions().search(query)` debounce 200ms
- Muestra dropdown debajo cursor (Teleport + getBoundingClientRect)
- Arrow keys navegan, Enter selecciona, Esc cierra
- Selected → reemplaza partial token con full `@cliente_42` o `@coach`

**Special tokens** (siempre mostrar si match prefix):
- `@coach` → mention coach del current user
- `@admin` / `@wellcore` → mention WellCore admin team

**Accessibility**: ARIA combobox + listbox roles, live announcements.

### MentionRenderer.vue

Pure functional. Recibe content string + parsea + renderiza chips:

```html
<MentionRenderer :content="post.content" :scope-coach-id="coachId" />
```

CSS:
- `.mention--client { color: rgb(59 130 246); background: rgb(59 130 246 / 0.15); }`
- `.mention--coach { color: rgb(245 158 11); background: rgb(245 158 11 / 0.15); }`
- `.mention--admin { color: rgb(220 38 38); background: rgb(220 38 38 / 0.15); }`

Click chip → router push (cliente: profile · coach: clients · admin: nothing).

### ReportPostMenu.vue

Dropdown trigger + modal. Cliente only (no coach/admin).

Trigger: 3-dot icon en post header.

Modal opciones:
```
- Spam o promoción
- Contenido ofensivo
- Off-topic
- Otro (especifica abajo)
[Detalles textarea opcional]
```

Submit → POST /api/v/community/posts/{id}/report (Fase A).
Toast: "Reporte enviado. Revisaremos a la brevedad."

### OnlineRoleIndicator.vue

Dot + tooltip "Activo ahora" / "Inactivo".

```html
<OnlineRoleIndicator :user-id="42" :user-type="'coach'" />
```

Verde si `useGroupPresence().isOnline(type, id) === true`, gris si offline.

### CommentsThread.vue modifications

1. **Sort comments**: admin → coach → client DESC

```js
const sortedComments = computed(() => {
    const adminComments = props.comments.filter(c => c.author_type === 'admin');
    const coachComments = props.comments.filter(c => c.author_type === 'coach');
    const clientComments = props.comments
        .filter(c => !c.author_type || c.author_type === 'client')
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    return [...adminComments, ...coachComments, ...clientComments];
});
```

2. **Render badge inline**:

```html
<span class="font-semibold">{{ comment.client_name || comment.author_name }}</span>
<CoachBadge v-if="comment.author_type === 'coach'" size="xs" />
<OfficialBadge v-if="comment.author_type === 'admin'" />
```

3. **Render mentions in body** con MentionRenderer.

---

## Composables

### useMentions.js

```js
import { ref } from 'vue';
import { useApi } from './useApi';

const searchCache = new Map();
const SEARCH_TTL_MS = 600_000;

export function useMentions() {
    const api = useApi();
    const loading = ref(false);

    async function search(query, { scope = null } = {}) {
        const trimmed = (query || '').trim().toLowerCase();
        if (trimmed.length < 3) return [];

        const key = `${scope || 'all'}:${trimmed}`;
        if (searchCache.has(key)) {
            const c = searchCache.get(key);
            if (Date.now() - c.timestamp < SEARCH_TTL_MS) return c.results;
        }

        loading.value = true;
        try {
            const res = await api.get('/api/v/community/mention-search', {
                params: { q: trimmed, scope: scope || undefined },
            });
            const results = res.data?.results || [];
            searchCache.set(key, { results, timestamp: Date.now() });
            return results;
        } catch (err) {
            console.error('[useMentions] search failed', err);
            return [];
        } finally {
            loading.value = false;
        }
    }

    function extract(content) {
        const tokens = [];
        const regex = /@(cliente_(\d+)|coach|admin|wellcore)\b/giu;
        let match;
        while ((match = regex.exec(content)) !== null) {
            if (match[2]) {
                tokens.push({ type: 'client', id: parseInt(match[2], 10), raw: match[0] });
            } else {
                const t = match[1].toLowerCase();
                tokens.push({
                    type: t === 'wellcore' ? 'admin' : t,
                    id: null,
                    raw: match[0],
                });
            }
        }
        return tokens;
    }

    return { loading, search, extract };
}

export function resetMentions() {
    searchCache.clear();
}
```

### useGroupPresence.js

```js
import { reactive } from 'vue';
import { useAuthStore } from '../stores/auth';

const onlineMap = reactive({
    client: new Set(), coach: new Set(), admin: new Set(),
});
let presenceChannel = null;
let initialized = false;

export function useGroupPresence() {
    const authStore = useAuthStore();

    function init() {
        if (initialized || !window.Echo) return;
        initialized = true;
        presenceChannel = window.Echo.join('online-users')
            .here((users) => users.forEach(u => addUser(u)))
            .joining((user) => addUser(user))
            .leaving((user) => removeUser(user))
            .error((err) => console.error('[useGroupPresence]', err));
    }

    function addUser(user) {
        const type = user.user_type || user.type;
        const id = user.id || user.user_id;
        if (!type || !id) return;
        if (onlineMap[type]) onlineMap[type].add(parseInt(id, 10));
    }

    function removeUser(user) {
        const type = user.user_type || user.type;
        const id = user.id || user.user_id;
        if (!type || !id) return;
        if (onlineMap[type]) onlineMap[type].delete(parseInt(id, 10));
    }

    function isOnline(type, id) {
        return onlineMap[type]?.has(parseInt(id, 10)) ?? false;
    }

    function countByRole(type) {
        return onlineMap[type]?.size ?? 0;
    }

    return { onlineMap, init, isOnline, countByRole };
}

export function resetGroupPresence() {
    if (presenceChannel && window.Echo) {
        try { window.Echo.leave('online-users'); } catch (e) {}
    }
    presenceChannel = null;
    initialized = false;
    onlineMap.client.clear();
    onlineMap.coach.clear();
    onlineMap.admin.clear();
}
```

---

## Backend extensions

### MentionSearchController

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\MentionResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MentionSearchController extends Controller
{
    public function __construct(private MentionResolverService $service) {}

    public function search(Request $request)
    {
        $request->validate([
            'q'     => 'required|string|min:3|max:50',
            'scope' => 'nullable|string|in:coach-team,all',
        ]);

        $user = $request->user();
        if (! $user) abort(401);

        $query = (string) $request->query('q');
        $scope = (string) $request->query('scope', 'coach-team');

        $scopeCoachId = null;
        if ($user instanceof Admin) {
            $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
            if ($role === 'coach') $scopeCoachId = $user->id;
        } else {
            $scopeCoachId = $user->coach_id ?? null;
        }

        $results = Cache::remember(
            'wc:mention-search:v1:' . md5("{$scope}:{$scopeCoachId}:{$query}"),
            ttl: 300,
            callback: fn () => $this->service->searchMentionTargets($query, $scopeCoachId),
        );

        return response()->json(['results' => $results]);
    }
}
```

### Migration `client_notification_preferences`

```php
Schema::create('client_notification_preferences', function (Blueprint $table) {
    $table->unsignedInteger('client_id')->primary();
    $table->boolean('notify_post_reactions')->default(true);
    $table->boolean('notify_comments_on_my_post')->default(true);
    $table->boolean('notify_mentions')->default(true);
    $table->boolean('notify_coach_messages')->default(true);
    $table->boolean('notify_coach_announcements')->default(true);
    $table->boolean('notify_wellcore_announcements')->default(true);
    $table->boolean('push_enabled')->default(true);
    $table->boolean('in_app_enabled')->default(true);
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
});
```

### Client\NotificationPreferencesController

Mirror del coach + admin pattern. GET + PATCH on `/api/v/client/notifications/preferences`.

```php
class NotificationPreferencesController extends Controller
{
    public function show(Request $request) {
        $client = $request->user();
        abort_unless($client instanceof \App\Models\Client, 403);
        $prefs = ClientNotificationPreference::firstOrCreate(['client_id' => $client->id]);
        return response()->json($prefs);
    }

    public function update(Request $request) {
        $client = $request->user();
        abort_unless($client instanceof \App\Models\Client, 403);
        $allowed = [
            'notify_post_reactions','notify_comments_on_my_post','notify_mentions',
            'notify_coach_messages','notify_coach_announcements','notify_wellcore_announcements',
            'push_enabled','in_app_enabled',
        ];
        $data = $request->validate(array_combine(
            $allowed, array_fill(0, count($allowed), 'sometimes|boolean')
        ));
        $prefs = ClientNotificationPreference::firstOrCreate(['client_id' => $client->id]);
        $prefs->fill($data)->save();
        return response()->json($prefs->fresh());
    }
}
```

### Routes

```php
Route::middleware(['wellcore.auth'])->group(function () {
    Route::get('v/community/mention-search', [Api\MentionSearchController::class, 'search']);
});

Route::middleware(['wellcore.auth'])->prefix('v/client')->group(function () {
    Route::get('notifications/preferences', [Api\Client\NotificationPreferencesController::class, 'show']);
    Route::patch('notifications/preferences', [Api\Client\NotificationPreferencesController::class, 'update']);
});
```

---

## CommunityFeed.vue cliente — modificaciones

### Composer (antes/después)

Antes:
```html
<textarea v-model="postContent" maxlength="500" />
```

Después:
```html
<MentionInput v-model="postContent" :max-length="500" scope="coach-team" />
```

### Post body (antes/después)

Antes:
```html
<p>{{ post.content }}</p>
```

Después:
```html
<MentionRenderer :content="post.content" />
```

### Post header (antes/después)

Antes:
```html
<header>
  <span>{{ post.client_name }}</span>
  <span class="time">{{ timeAgo }}</span>
</header>
```

Después:
```html
<header class="flex items-center justify-between">
  <div class="flex items-center gap-2">
    <span>{{ post.client_name || post.author_name }}</span>
    <CoachBadge v-if="post.author_type === 'coach'" size="xs" />
    <OfficialBadge v-if="post.author_type === 'admin' || post.is_official" />
    <span class="time">{{ timeAgo }}</span>
  </div>
  <ReportPostMenu :post-id="post.id" @reported="onReported(post.id)" />
</header>
```

### Comments thread

Pasa `:isCoachContext="false"` (default cliente). CommentsThread.vue lee `comment.author_type` y aplica:
- Sort priority (admin > coach > client)
- Renderiza CoachBadge / OfficialBadge inline
- Usa MentionRenderer for body

---

## Real-time integrations

### Listening user.{type}.{id} channel

Cliente CommunityFeed.vue onMounted:

```js
const userChannel = window.Echo.private(`user.client.${authStore.userId}`)
    .listen('.mention.created', (e) => {
        notificationBell.refresh();
        toast.info(`${e.mentioner_name} te mencionó en un post.`);
    });

onBeforeUnmount(() => {
    if (userChannel) window.Echo.leave(`user.client.${authStore.userId}`);
});
```

Same pattern coach + admin layouts.

### GroupPresence integration

ClientLayout.vue (or AppShell) calls `useGroupPresence().init()` once on mount. Other components consume `isOnline(type, id)`.

CommunityFeed cliente: header del post con coach del cliente — show `<OnlineRoleIndicator :user-id="post.coach_id" :user-type="'coach'" />`.

---

## Testing Strategy

### Backend (Pest, ~6 tests)

- `MentionSearchEndpointTest.php`: matches with q, rejects q<3, admin all scope, cliente scoped
- `ClientNotificationPreferencesTest.php`: defaults if no row, patches granular
- `tests/Feature/Community/PostReportFlowE2ETest.php`: cliente reporta → row → admin queue → dismiss flow
- `tests/Feature/Community/CrossRoleBadgesRenderingTest.php`: API response with author_type='coach' renders badge

### Frontend (Vitest, ~4 tests)

- `useMentions.test.js`: caches 10min, skip <3 chars, extract parses correctly
- `useGroupPresence.test.js`: init joins channel, isOnline reactive, reset clears

### E2E manual

- Cliente escribe `@carl` → dropdown matches
- Submit post → backend persiste mention → Carlos recibe push
- Cliente ajeno reporta post → admin queue Fase C lo recibe
- Coach respondió comment → cliente ve "Carlos Coach" badge arriba del thread
- Admin postea oficial global → todos clientes ven badge "WellCore"
- /client/notifications → toggles + live save

---

## Privacy & Authorization

- mention-search scope: cliente solo dentro coach team. Admin: all. Coach: solo sus clientes.
- ReportPost: cliente reporta cualquier post visible en su feed.
- Mentions: backend filtra mentioned users según scope antes persist (regex sanitize allowlist).
- Presence: usuario solo ve presence de others same coach scope. PresenceChannel auth backend Fase A.

---

## Risks & Mitigations

| Risk | Severity | Mitigation |
|------|----------|------------|
| Autocomplete dropdown laggy con 100+ matches | Medio | Limit 10 results service · debounce 200ms |
| Mention spam | Medio | Backend dedup post_mentions table o rate-limit endpoint |
| XSS via mention content | Alto | Vue auto-escape + backend allowlist regex |
| Presence channel saturation | Medio | Echo PresenceChannel handles · Reverb throttle |
| Cache leak entre impersonations | Medio | resetMentions/resetGroupPresence in auth |
| Comments sort breaks UI cliente | Alto | Test snapshot antes/después |

---

## What's NOT in scope

- Voice notes en mentions
- Read receipts en mentions
- Mention notifications digest
- Mention edit / delete history
- Granular mention settings
- Auto-translation cross-language

---

## Definition of Done — Fase D

### Frontend
- [ ] MentionInput.vue funcional con autocomplete
- [ ] MentionRenderer.vue chips con colores por rol
- [ ] ReportPostMenu.vue modal + dropdown
- [ ] OnlineRoleIndicator.vue tooltip
- [ ] CommunityFeed.vue cliente integrado: MentionInput, MentionRenderer, ReportPostMenu, badges
- [ ] CommentsThread.vue ordena admin > coach > client + badges
- [ ] NotificationsPreferences cliente page
- [ ] 2 composables singleton + reset hooks
- [ ] auth.js extendido 2 reset calls (presence + mentions)
- [ ] Real-time: user.client.{id} listened en CommunityFeed
- [ ] No regresión CommunityFeed cliente

### Backend
- [ ] Migration client_notification_preferences aditiva
- [ ] ClientNotificationPreference model
- [ ] MentionSearchController + cache 300s
- [ ] Client\NotificationPreferencesController
- [ ] 3 routes nuevas

### Testing
- [ ] 4 Pest tests verde
- [ ] 4 Vitest tests verde
- [ ] No regresión Pest suite
- [ ] Pint OK

### Operations
- [ ] Cache wc:mention-search:v1:* 300s
- [ ] Lighthouse Performance ≥ 70 cliente CommunityFeed
- [ ] Console clean

### Documentación
- [ ] Spec doc commiteado
- [ ] Plan doc commiteado
- [ ] CLAUDE.md actualizado con sección "Community Cross-Role complete (A+B+C+D)"

---

## Self-Review

**1. Spec coverage:** mentions ✅ · threads cross-role badges ✅ · ReportPostMenu ✅ · GroupPresence ✅ · client notifications page ✅ · mention-search endpoint ✅

**2. Placeholder scan:** sin TBDs estructurales. Code samples completos.

**3. Internal consistency:** 4 components matchean 4 file map · 2 composables matchean 2 reset hooks · 3 backend new files matchean 3 routes · 4 frontend modificados matchean ediciones específicas

**4. Premium quality:** autocomplete UX premium · cross-role badges visualmente distintivos · presence reactivo · cache strategy · testing happy + edge

**5. Cross-fase integration:** uses CoachBadge/OfficialBadge from Fase B ✅ · admin moderation queue Fase C recibe post-reports ✅ · auth.js reset hooks consolidados (B+C+D) ✅

Spec listo para transición a writing-plans.
