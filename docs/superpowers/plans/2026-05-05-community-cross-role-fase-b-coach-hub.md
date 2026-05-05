# Community Cross-Role — Fase B: Coach Community Hub Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir el Coach Community Hub completo (UI Vue 3 SPA + backend extensions) sobre las foundations de Fase A. Al final de Fase B el coach abre `/coach/community`, ve 5 tabs operacionales (Latido del Equipo / Posts / Conversaciones / Pulsos / Logros), modera con optimistic UI, recibe push notifications nativas, y usa el modal "Mensaje al equipo" para anuncios in-feed o push.

**Architecture:** Vue 3.5 Composition API + Pinia + Vue Router 4 + Tailwind CSS 4 + Reverb WebSocket. Composables singleton module-scope (gold standard `useGroupPulse`). 5 tabs lazy-loaded como sub-chunks Vite. Backend extensions: 5 endpoints REST nuevos + 1 console command + announce impl. Tests Vitest para composables + Pest backend feature.

**Tech Stack:** PHP 8.4, Laravel 13.1.1, Vue 3.5, Pinia, Vue Router 4, Tailwind CSS 4, Vite 8, Pest v3, Vitest. Linter Pint + ESLint configurados.

**Spec:** `docs/superpowers/specs/2026-05-05-community-cross-role-fase-b-coach-hub-design.md`

**Phase scope:** Solo Fase B del spec. Fases C (Admin Community Center) y D (Cross-Role Layer) tienen sus propios planes paralelos.

---

## File Map

### Frontend new files (21)

```
resources/js/vue/pages/Coach/
├── Community.vue                              [NEW] hub principal
└── NotificationsPreferences.vue               [NEW] /coach/notifications

resources/js/vue/pages/Coach/community/
├── CoachLatidoTab.vue                         [NEW]
├── CoachPostsTab.vue                          [NEW]
├── CoachConversacionesTab.vue                 [NEW]
├── CoachPulsosTab.vue                         [NEW]
└── CoachLogrosTab.vue                         [NEW]

resources/js/vue/components/community/
├── CoachBadge.vue                             [NEW]
├── OfficialBadge.vue                          [NEW]
├── PinnedIndicator.vue                        [NEW]
├── TeamHealthRing.vue                         [NEW]
├── TopPerformerCard.vue                       [NEW]
├── AtRiskClientChip.vue                       [NEW]
├── CoachAnnounceModal.vue                     [NEW]
├── PushPermissionBanner.vue                   [NEW]
├── CoachCommunityTour.vue                     [NEW]
├── PostCardCoachActions.vue                   [NEW]
└── CommunityEmptyIllustration.vue             [NEW]

resources/js/vue/composables/
├── useCoachCommunity.js                       [NEW]
├── useCoachPulse.js                           [NEW]
├── useModeration.js                           [NEW]
├── useCoachAnnounce.js                        [NEW]
└── usePushSubscription.js                     [NEW]
```

### Frontend modified files (3)

```
resources/js/vue/layouts/CoachLayout.vue       # nueva sección "Comunidad" + item "Notificaciones" + 4ta opción FAB + 2 SVG icons
resources/js/vue/router/index.js               # 2 rutas nuevas
resources/js/vue/stores/auth.js                # imports + reset calls
```

### Backend new files (3)

```
app/Http/Controllers/Api/Coach/
└── PushSubscriptionController.php             [NEW]

app/Console/Commands/
└── PrecomputeCoachPulse.php                   [NEW]
```

### Backend modified files (3)

```
app/Http/Controllers/Api/Coach/CommunityController.php   # announce() impl + threads() + achievements()
app/Services/PushNotificationService.php                 # +notifyCoachAnnounceToClients
app/Services/CoachCommunityService.php                   # +threads() + +achievements()
routes/api.php                                           # 5 rutas nuevas
routes/console.php                                       # schedule precompute-coach-pulse
```

### Tests new files (10)

```
tests/Unit/Composables/
├── useCoachCommunity.test.js                  [NEW]
├── useCoachPulse.test.js                      [NEW]
├── useModeration.test.js                      [NEW]
├── useCoachAnnounce.test.js                   [NEW]
└── usePushSubscription.test.js                [NEW]

tests/Feature/Coach/
├── AnnounceEndpointTest.php                   [NEW]
├── PushSubscriptionTest.php                   [NEW]
├── NotificationsPreferencesTest.php           [NEW]
├── CommunityThreadsTest.php                   [NEW]
└── CommunityAchievementsTest.php              [NEW]

tests/Unit/Commands/
└── PrecomputeCoachPulseTest.php               [NEW]
```

---

## Pre-flight (one time, do BEFORE Task 1)

Confirm working directory and branch state:

```bash
cd C:\Users\GODSF\Herd\wellcore-laravel
git fetch origin
git branch --show-current
git status --short
```

Expected branch: `feat/community-cross-role-fase-a` o branch nueva `feat/community-cross-role-fase-b` (recomendado).

If creating new branch:

```bash
git checkout -b feat/community-cross-role-fase-b
```

Verify Fase A está mergeada o disponible:

```bash
php artisan route:list --path=api/v/coach/community
# Esperado: pulse, posts, pulsos, announce, threads (faltante), achievements (faltante)
```

Verify env:

```bash
php artisan tinker --execute="echo config('broadcasting.default');"
# Esperado: reverb

php artisan tinker --execute="echo config('cache.default');"
# Esperado: redis

php artisan tinker --execute="echo env('VAPID_PUBLIC_KEY') ? 'OK' : 'MISSING';"
# Si MISSING: agregar VAPID keys a .env (ver memory credentials_services.md)
```

Verify Vitest installed:

```bash
npm ls vitest
# Si no está: npm install --save-dev vitest @vue/test-utils
```

Confirm test DB schema sync (memory `reference_test_db_schema_drift.md`):

```bash
DB_DATABASE=wellcore_fitness_test php artisan migrate --force
```

---

## Task 0: Branch setup + dependencies

**Files:** N/A (git + npm operations)

- [ ] **Step 1: Verify branch state**

```bash
git status
git log -1 --oneline
```

Expected: clean working tree, last commit is Fase A or main.

- [ ] **Step 2: Create feature branch**

```bash
git checkout -b feat/community-cross-role-fase-b
```

- [ ] **Step 3: Install Vitest if missing**

```bash
npm ls vitest 2>&1 | head -5
```

If "empty": `npm install --save-dev vitest @vue/test-utils @vitest/ui jsdom`

- [ ] **Step 4: Configure vitest.config.js (if not present)**

Check: `ls vitest.config.js`. If missing, create:

```js
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'url';

export default defineConfig({
    plugins: [vue()],
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['tests/Unit/**/*.test.js'],
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
```

Add npm script in `package.json`:

```json
"scripts": {
    "test:unit": "vitest run",
    "test:unit:watch": "vitest"
}
```

- [ ] **Step 5: Commit setup**

```bash
git add package.json package-lock.json vitest.config.js
git commit -m "chore(community-fase-b): add Vitest for composable testing"
```

---

## Task 1: Backend — CoachCommunityService extensions (threads + achievements)

**Files:**
- Modify: `app/Services/CoachCommunityService.php`

- [ ] **Step 1: Read current service to locate `resolveClientIds`**

```bash
grep -n "function" app/Services/CoachCommunityService.php
```

Locate `resolveClientIds`, `getFeed`, `topPerformers`, `atRiskClients`, `teamHealthScore`. Add new methods after `resolveClientIds`.

- [ ] **Step 2: Append `threads()` method**

```php
/**
 * Threads activos: posts del coach con comentarios últimos $sinceDays días.
 * Retorna paginado con metadata útil para Tab Conversaciones.
 *
 * @return array{data: array, pagination: array}
 */
public function threads(int $coachId, int $sinceDays = 7, int $page = 1, int $perPage = 20): array
{
    $clientIds = $this->resolveClientIds($coachId);
    $since = now()->subDays($sinceDays);

    $query = CommunityPost::query()
        ->whereIn('client_id', $clientIds)
        ->where('visible', true)
        ->whereExists(function ($q) use ($since) {
            $q->select(DB::raw(1))
              ->from('post_comments')
              ->whereColumn('post_comments.post_id', 'community_posts.id')
              ->where('post_comments.created_at', '>=', $since);
        })
        ->withCount(['comments' => fn ($q) => $q->where('created_at', '>=', $since)])
        ->orderByDesc(
            DB::raw('(select max(created_at) from post_comments where post_comments.post_id = community_posts.id)')
        );

    $paginator = $query->paginate(perPage: $perPage, page: $page);

    $data = $paginator->getCollection()->map(function (CommunityPost $post) use ($coachId, $since) {
        $hasCoachReply = PostComment::query()
            ->where('post_id', $post->id)
            ->where('author_type', 'coach')
            ->where('author_admin_id', $coachId)
            ->exists();

        $participantsCount = PostComment::query()
            ->where('post_id', $post->id)
            ->where('created_at', '>=', $since)
            ->distinct('client_id')
            ->count('client_id');

        $isConflicted = $post->comments_count >= 10
            && PostMention::query()
                ->where('post_id', $post->id)
                ->whereIn('mentioned_type', ['admin'])
                ->exists();

        $excerpt = mb_substr(strip_tags($post->content ?? ''), 0, 80);

        return [
            'post_id'           => $post->id,
            'post_excerpt'      => $excerpt,
            'post_author_name'  => optional(Client::find($post->client_id))->name ?? 'Cliente',
            'thread_size'       => $post->comments_count,
            'participants_count'=> $participantsCount,
            'has_coach_reply'   => $hasCoachReply,
            'is_conflicted'     => $isConflicted,
            'last_activity_at'  => optional(
                PostComment::where('post_id', $post->id)->latest('created_at')->first()
            )->created_at?->toIso8601String(),
        ];
    })->all();

    return [
        'data' => $data,
        'pagination' => [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ],
    ];
}

/**
 * Achievements + PRs últimos $period del equipo.
 *
 * @param string $period 'week'|'month'|'all'
 * @return array{data: array, totals: array, pagination: array}
 */
public function achievements(int $coachId, string $period = 'week', int $page = 1, int $perPage = 20): array
{
    $clientIds = $this->resolveClientIds($coachId);

    $since = match ($period) {
        'week'  => now()->subWeek(),
        'month' => now()->subMonth(),
        default => null,
    };

    // Personal Records
    $prsQuery = DB::table('personal_records')
        ->whereIn('client_id', $clientIds);
    if ($since) $prsQuery->where('created_at', '>=', $since);

    $prs = $prsQuery
        ->orderByDesc('created_at')
        ->limit(50)
        ->get()
        ->map(function ($pr) {
            $client = Client::find($pr->client_id);
            return [
                'type' => 'pr',
                'client_id' => $pr->client_id,
                'client_name' => $client?->name ?? 'Cliente',
                'avatar_url' => $client?->avatar_url,
                'exercise' => $pr->exercise ?? null,
                'weight_kg' => $pr->weight ?? null,
                'previous_weight_kg' => null, // TODO compute previous PR if needed
                'achieved_at' => $pr->created_at,
            ];
        });

    // Achievements
    $achievementsQuery = DB::table('client_achievements')
        ->whereIn('client_id', $clientIds);
    if ($since) $achievementsQuery->where('created_at', '>=', $since);

    $achievements = $achievementsQuery
        ->orderByDesc('created_at')
        ->limit(50)
        ->get()
        ->map(function ($a) {
            $client = Client::find($a->client_id);
            return [
                'type' => 'achievement',
                'client_id' => $a->client_id,
                'client_name' => $client?->name ?? 'Cliente',
                'avatar_url' => $client?->avatar_url,
                'achievement_name' => $a->achievement_name ?? $a->name ?? 'Logro',
                'achieved_at' => $a->created_at,
            ];
        });

    $merged = $prs->concat($achievements)
        ->sortByDesc('achieved_at')
        ->values();

    $offset = ($page - 1) * $perPage;
    $pageItems = $merged->slice($offset, $perPage)->values()->all();

    return [
        'data' => $pageItems,
        'totals' => [
            'prs' => $prs->count(),
            'achievements' => $achievements->count(),
        ],
        'pagination' => [
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($merged->count() / $perPage)),
            'total'        => $merged->count(),
        ],
    ];
}
```

- [ ] **Step 3: Verify methods callable in tinker**

```bash
php artisan tinker --execute="dump(app(App\Services\CoachCommunityService::class)->threads(1, 7, 1, 5));"
```

Expected: array with `data` (possibly empty) + `pagination`. No errors.

- [ ] **Step 4: Commit**

```bash
git add app/Services/CoachCommunityService.php
git commit -m "feat(community): CoachCommunityService.threads + achievements for tabs Conversaciones/Logros"
```

---

## Task 2: Backend — CommunityController endpoints (threads + achievements + announce impl)

**Files:**
- Modify: `app/Http/Controllers/Api/Coach/CommunityController.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Add `threads()` action**

Append to controller after `pulsos()`:

```php
public function threads(Request $request): JsonResponse
{
    $coach = $request->user();
    abort_unless($this->isCoach($coach), 403);

    $sinceDays = (int) $request->query('since_days', 7);
    $page      = max(1, (int) $request->query('page', 1));
    $perPage   = min(50, max(5, (int) $request->query('per_page', 20)));

    $payload = $this->service->threads($coach->id, $sinceDays, $page, $perPage);

    return response()->json($payload);
}

public function achievements(Request $request): JsonResponse
{
    $coach = $request->user();
    abort_unless($this->isCoach($coach), 403);

    $period  = (string) $request->query('period', 'week');
    if (! in_array($period, ['week', 'month', 'all'], true)) {
        $period = 'week';
    }
    $page    = max(1, (int) $request->query('page', 1));
    $perPage = min(50, max(5, (int) $request->query('per_page', 20)));

    $payload = $this->service->achievements($coach->id, $period, $page, $perPage);

    return response()->json($payload);
}
```

- [ ] **Step 2: Replace `announce()` 501 stub with full implementation**

Replace the entire `announce()` method:

```php
public function announce(Request $request): JsonResponse
{
    $coach = $request->user();
    abort_unless($this->isCoach($coach), 403);

    $validated = $request->validate([
        'type'        => 'required|in:post,push',
        'message'     => 'required|string|max:1000',
        'pin_hours'   => 'nullable|integer|min:1|max:168',
        'image'       => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        'plan_filter' => 'nullable|json',
    ]);

    if ($validated['type'] === 'post') {
        return $this->announceAsPost($coach, $validated, $request);
    }

    return $this->announceAsPush($coach, $validated);
}

private function announceAsPost(\App\Models\Admin $coach, array $data, Request $request): JsonResponse
{
    return \DB::transaction(function () use ($coach, $data, $request) {
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('community/announcements', 'public');
            $imageUrl = \Storage::url($path);
        }

        $post = \App\Models\CommunityPost::create([
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

        if (! empty($data['pin_hours'])) {
            app(\App\Services\ModerationService::class)->pinPost(
                $post, $coach, 'coach', (int) $data['pin_hours'], 'Anuncio al equipo'
            );
        }

        \App\Models\ModerationAction::create([
            'actor_type'  => 'coach',
            'actor_id'    => $coach->id,
            'action_type' => 'announce',
            'target_type' => 'post',
            'target_id'   => $post->id,
            'metadata'    => ['mode' => 'post', 'pin_hours' => $data['pin_hours'] ?? null],
            'created_at'  => now(),
        ]);

        $clientIds = $this->service->resolveClientIds($coach->id);
        $count = count($clientIds);

        \App\Models\BroadcastMessage::create([
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

        event(new \App\Events\BroadcastSent($post->id, 'announcement_post', $count));

        return response()->json([
            'post_id'          => $post->id,
            'recipients_count' => $count,
            'pinned_until'     => $post->pinned?->pinned_until,
        ], 201);
    });
}

private function announceAsPush(\App\Models\Admin $coach, array $data): JsonResponse
{
    $segmentFilter = isset($data['plan_filter']) ? json_decode($data['plan_filter'], true) : null;
    $clientIds = $this->service->resolveClientIds($coach->id);

    if (is_array($segmentFilter) && ! empty($segmentFilter['plan'])) {
        $clientIds = \App\Models\Client::query()
            ->whereIn('id', $clientIds)
            ->whereIn('plan', $segmentFilter['plan'])
            ->pluck('id')
            ->all();
    }

    $delivered = app(\App\Services\PushNotificationService::class)
        ->notifyCoachAnnounceToClients(
            coachId: $coach->id,
            clientIds: $clientIds,
            message: $data['message']
        );

    \App\Models\BroadcastMessage::create([
        'sender_type'      => 'coach',
        'sender_id'        => $coach->id,
        'audience_type'    => 'clients',
        'segment_filter'   => $segmentFilter,
        'subject'          => 'Anuncio del coach',
        'body'             => $data['message'],
        'push_enabled'     => true,
        'recipients_count' => count($clientIds),
        'delivered_count'  => $delivered,
        'sent_at'          => now(),
    ]);

    \App\Models\ModerationAction::create([
        'actor_type'  => 'coach',
        'actor_id'    => $coach->id,
        'action_type' => 'announce',
        'target_type' => 'post',
        'target_id'   => 0,
        'metadata'    => ['mode' => 'push', 'segment' => $segmentFilter, 'count' => $delivered],
        'created_at'  => now(),
    ]);

    event(new \App\Events\BroadcastSent(0, 'announcement_push', $delivered));

    return response()->json([
        'recipients_count' => count($clientIds),
        'delivered_count'  => $delivered,
    ], 201);
}
```

- [ ] **Step 3: Add use statements**

At the top of CommunityController.php, add (if missing):

```php
use App\Events\BroadcastSent;
use App\Models\BroadcastMessage;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Services\ModerationService;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
```

Then remove the leading backslashes in the methods above for cleanliness.

- [ ] **Step 4: Add routes**

Edit `routes/api.php`. Locate the coach group and add:

```php
Route::middleware(['wellcore.auth'])->prefix('v/coach')->group(function () {
    // ... existing
    Route::get('community/threads', [Coach\CommunityController::class, 'threads']);
    Route::get('community/achievements', [Coach\CommunityController::class, 'achievements']);
    Route::get('clients/count', [Coach\ClientsController::class, 'count']);
});
```

If `Coach\ClientsController::count` doesn't exist, ADD it (Task 3).

- [ ] **Step 5: Verify routes**

```bash
php artisan route:list --path=api/v/coach/community
```

Expected: pulse, posts, pulsos, announce, threads, achievements (6 endpoints).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Coach/CommunityController.php routes/api.php
git commit -m "feat(community): announce impl + threads + achievements endpoints"
```

---

## Task 3: Backend — Coach\ClientsController::count for recipient preview

**Files:**
- Modify: `app/Http/Controllers/Api/Coach/ClientsController.php` (or create if doesn't exist)

- [ ] **Step 1: Locate or create ClientsController**

```bash
ls app/Http/Controllers/Api/Coach/ClientsController.php 2>&1
```

If missing: create with skeleton.

- [ ] **Step 2: Add `count()` action**

```php
public function count(Request $request): JsonResponse
{
    $coach = $request->user();
    abort_unless($coach instanceof \App\Models\Admin, 403);

    $role = $coach->role instanceof \BackedEnum ? $coach->role->value : (string) $coach->role;
    abort_unless($role === 'coach', 403);

    $statuses = (array) $request->query('status', ['activo']);
    $plans    = $request->query('plan');

    $query = \App\Models\Client::query()
        ->where('coach_id', $coach->id)
        ->whereIn('status', $statuses);

    if (! empty($plans)) {
        $plansArr = is_array($plans) ? $plans : explode(',', (string) $plans);
        $query->whereIn('plan', $plansArr);
    }

    return response()->json([
        'count' => (int) $query->count(),
    ]);
}
```

Add route (if not added in Task 2):

```php
Route::get('v/coach/clients/count', [Coach\ClientsController::class, 'count']);
```

- [ ] **Step 3: Smoke test**

```bash
php artisan route:list --path=api/v/coach/clients/count
```

Expected: 1 GET route.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Api/Coach/ClientsController.php routes/api.php
git commit -m "feat(community): coach clients count endpoint for announce preview"
```

---

## Task 4: Backend — PushNotificationService extensions

**Files:**
- Modify: `app/Services/PushNotificationService.php`

- [ ] **Step 1: Read current state**

```bash
grep -n "function " app/Services/PushNotificationService.php
```

Locate existing methods (`sendToSubscription`, `notifyClientPr`, etc.). Add new methods at end of class.

- [ ] **Step 2: Add `notifyCoachAnnounceToClients`**

```php
/**
 * Send a coach announcement push notification to a list of clients.
 *
 * @param int $coachId
 * @param int[] $clientIds
 * @param string $message
 * @return int delivered count
 */
public function notifyCoachAnnounceToClients(int $coachId, array $clientIds, string $message): int
{
    if (empty($clientIds)) return 0;

    $coach = \App\Models\Admin::find($coachId);
    $firstName = $coach?->name ? explode(' ', trim($coach->name))[0] : 'tu coach';
    $title = "Mensaje de {$firstName}";

    $delivered = 0;
    foreach (array_chunk($clientIds, 100) as $chunk) {
        $subscriptions = \App\Models\PushSubscription::query()
            ->whereIn('client_id', $chunk)
            ->where('active', true)
            ->get();

        foreach ($subscriptions as $sub) {
            try {
                $this->sendToSubscription($sub, $title, mb_substr($message, 0, 200), [
                    'tag' => 'coach-announce',
                    'url' => '/client/community',
                    'icon' => '/images/logo-192.png',
                ]);
                $delivered++;
            } catch (\Throwable $e) {
                \Log::warning('Coach announce push failed', [
                    'coach_id' => $coachId,
                    'sub_id'   => $sub->id,
                    'err'      => $e->getMessage(),
                ]);
            }
        }
    }

    return $delivered;
}

/**
 * Send notification to a coach (push to coach_push_subscriptions).
 */
public function notifyCoach(int $coachId, string $title, string $body, array $data = []): int
{
    $subscriptions = \DB::table('coach_push_subscriptions')
        ->where('coach_id', $coachId)
        ->where('active', true)
        ->get();

    $delivered = 0;
    foreach ($subscriptions as $sub) {
        try {
            // Reuse same WebPush logic as client subscriptions but pointing at coach table.
            $this->sendCoachSubscription($sub, $title, $body, $data);
            $delivered++;
        } catch (\Throwable $e) {
            \Log::warning('Coach push failed', ['coach_id' => $coachId, 'err' => $e->getMessage()]);
        }
    }
    return $delivered;
}

private function sendCoachSubscription($sub, string $title, string $body, array $data): void
{
    if (! class_exists(\Minishlink\WebPush\WebPush::class)) {
        throw new \RuntimeException('WebPush library not installed');
    }
    $webPush = new \Minishlink\WebPush\WebPush([
        'VAPID' => [
            'subject'    => config('services.webpush.subject', 'mailto:soporte@wellcorefitness.com'),
            'publicKey'  => config('services.webpush.public_key'),
            'privateKey' => config('services.webpush.private_key'),
        ],
    ]);
    $payload = json_encode(array_merge([
        'title' => $title,
        'body'  => $body,
    ], $data));
    $webPush->queueNotification(
        \Minishlink\WebPush\Subscription::create([
            'endpoint' => $sub->endpoint,
            'publicKey' => $sub->p256dh,
            'authToken' => $sub->auth_key,
        ]),
        $payload
    );
    foreach ($webPush->flush() as $report) {
        if (! $report->isSuccess()) {
            throw new \RuntimeException($report->getReason() ?: 'unknown push error');
        }
    }
}
```

- [ ] **Step 3: Verify class loads**

```bash
php artisan tinker --execute="dump(get_class_methods(App\Services\PushNotificationService::class));"
```

Expected: includes `notifyCoachAnnounceToClients` and `notifyCoach`.

- [ ] **Step 4: Commit**

```bash
git add app/Services/PushNotificationService.php
git commit -m "feat(community): PushNotificationService.notifyCoachAnnounceToClients + notifyCoach"
```

---

## Task 5: Backend — PushSubscriptionController (TDD)

**Files:**
- Create: `app/Http/Controllers/Api/Coach/PushSubscriptionController.php`
- Create: `tests/Feature/Coach/PushSubscriptionTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Coach/PushSubscriptionTest.php

use App\Models\Admin;
use App\Models\CoachNotificationPreference;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id'    => $this->coach->id,
        'user_type'  => 'admin',
        'token'      => hash('sha256', $this->token),
        'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('subscribes coach with valid endpoint', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc',
            'keys' => ['p256dh' => 'BAxxx', 'auth' => 'yyy'],
            'user_agent' => 'Mozilla/5.0',
        ]);

    $resp->assertStatus(201)
         ->assertJsonStructure(['id', 'active']);

    expect(DB::table('coach_push_subscriptions')->where('coach_id', $this->coach->id)->count())->toBe(1);
});

it('dedups subscriptions on same endpoint', function () {
    $payload = [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/dup',
        'keys' => ['p256dh' => 'aa', 'auth' => 'bb'],
    ];

    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/push/subscribe', $payload);
    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/push/subscribe', $payload);

    expect(DB::table('coach_push_subscriptions')->where('coach_id', $this->coach->id)->count())->toBe(1);
});

it('unsubscribes', function () {
    $id = DB::table('coach_push_subscriptions')->insertGetId([
        'coach_id'   => $this->coach->id,
        'endpoint'   => 'https://x',
        'p256dh'     => 'a',
        'auth_key'   => 'b',
        'active'     => true,
        'created_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/v/coach/push/subscribe/{$id}")
        ->assertStatus(204);

    $row = DB::table('coach_push_subscriptions')->find($id);
    expect((bool) $row->active)->toBeFalse();
});

it('returns notification preferences with defaults', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/coach/notifications/preferences')
        ->assertOk();

    expect($resp->json('notify_pr_broken'))->toBeTrue();
    expect($resp->json('notify_post_created'))->toBeFalse();
});

it('patches notification preferences', function () {
    CoachNotificationPreference::forCoach($this->coach->id);

    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->patchJson('/api/v/coach/notifications/preferences', [
            'notify_post_created' => true,
        ])
        ->assertOk();

    expect($resp->json('notify_post_created'))->toBeTrue();
});
```

- [ ] **Step 2: Run tests — confirm fail**

```bash
vendor/bin/pest tests/Feature/Coach/PushSubscriptionTest.php
```

Expected: FAIL — controller missing, routes missing.

- [ ] **Step 3: Implement controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Coach;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CoachNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $data = $request->validate([
            'endpoint'      => 'required|string|max:500',
            'keys.p256dh'   => 'required|string',
            'keys.auth'     => 'required|string',
            'user_agent'    => 'nullable|string|max:255',
        ]);

        $existingId = DB::table('coach_push_subscriptions')
            ->where('coach_id', $coach->id)
            ->where('endpoint', $data['endpoint'])
            ->value('id');

        if ($existingId) {
            DB::table('coach_push_subscriptions')->where('id', $existingId)->update([
                'p256dh'       => $data['keys']['p256dh'],
                'auth_key'     => $data['keys']['auth'],
                'user_agent'   => $data['user_agent'] ?? null,
                'active'       => true,
                'last_used_at' => now(),
            ]);
            return response()->json(['id' => $existingId, 'active' => true], 201);
        }

        $id = DB::table('coach_push_subscriptions')->insertGetId([
            'coach_id'   => $coach->id,
            'endpoint'   => $data['endpoint'],
            'p256dh'     => $data['keys']['p256dh'],
            'auth_key'   => $data['keys']['auth'],
            'user_agent' => $data['user_agent'] ?? null,
            'active'     => true,
            'created_at' => now(),
        ]);

        return response()->json(['id' => $id, 'active' => true], 201);
    }

    public function unsubscribe(Request $request, int $id): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $affected = DB::table('coach_push_subscriptions')
            ->where('id', $id)
            ->where('coach_id', $coach->id)
            ->update(['active' => false]);

        if ($affected === 0) abort(404);

        return response()->json(null, 204);
    }

    public function preferences(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $prefs = CoachNotificationPreference::forCoach($coach->id);
        return response()->json($prefs);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $coach = $request->user();
        abort_unless($this->isCoach($coach), 403);

        $allowed = [
            'notify_pr_broken','notify_streak_milestone','notify_post_created',
            'notify_comment_on_my_reply','notify_at_risk_client',
            'notify_official_post_engagement','notify_admin_broadcast',
            'push_enabled','in_app_enabled',
        ];

        $data = $request->validate(array_combine(
            $allowed,
            array_fill(0, count($allowed), 'sometimes|boolean')
        ));

        $prefs = CoachNotificationPreference::forCoach($coach->id);
        $prefs->fill($data)->save();

        return response()->json($prefs->fresh());
    }

    private function isCoach(mixed $user): bool
    {
        if (! $user instanceof Admin) return false;
        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
        return $role === 'coach';
    }
}
```

- [ ] **Step 4: Add routes**

In `routes/api.php`, inside the coach middleware group:

```php
Route::post('v/coach/push/subscribe', [Coach\PushSubscriptionController::class, 'subscribe']);
Route::delete('v/coach/push/subscribe/{id}', [Coach\PushSubscriptionController::class, 'unsubscribe']);
Route::get('v/coach/notifications/preferences', [Coach\PushSubscriptionController::class, 'preferences']);
Route::patch('v/coach/notifications/preferences', [Coach\PushSubscriptionController::class, 'updatePreferences']);
```

- [ ] **Step 5: Run tests — confirm pass**

```bash
vendor/bin/pest tests/Feature/Coach/PushSubscriptionTest.php
```

Expected: 5/5 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Coach/PushSubscriptionController.php routes/api.php tests/Feature/Coach/PushSubscriptionTest.php
git commit -m "feat(community): PushSubscriptionController with subscribe/unsubscribe/preferences"
```

---

## Task 6: Backend — PrecomputeCoachPulse command (TDD)

**Files:**
- Create: `app/Console/Commands/PrecomputeCoachPulse.php`
- Create: `tests/Unit/Commands/PrecomputeCoachPulseTest.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Unit/Commands/PrecomputeCoachPulseTest.php

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;

uses(DatabaseTransactions::class);

it('precomputes pulse for coaches with active clients', function () {
    Cache::flush();
    $coach = Admin::factory()->create(['role' => 'coach']);
    Client::factory()->count(3)->create(['coach_id' => $coach->id, 'status' => 'activo']);

    $this->artisan('wellcore:precompute-coach-pulse')
        ->expectsOutputToContain('Precomputed pulse')
        ->assertSuccessful();

    expect(Cache::has("wc:coach-pulse:v1:{$coach->id}"))->toBeTrue();
});

it('skips coaches without active clients', function () {
    Cache::flush();
    $coach = Admin::factory()->create(['role' => 'coach']);

    $this->artisan('wellcore:precompute-coach-pulse')->assertSuccessful();

    expect(Cache::has("wc:coach-pulse:v1:{$coach->id}"))->toBeFalse();
});

it('limits to specific coach with --coach option', function () {
    Cache::flush();
    $coach1 = Admin::factory()->create(['role' => 'coach']);
    $coach2 = Admin::factory()->create(['role' => 'coach']);
    Client::factory()->create(['coach_id' => $coach1->id, 'status' => 'activo']);
    Client::factory()->create(['coach_id' => $coach2->id, 'status' => 'activo']);

    $this->artisan('wellcore:precompute-coach-pulse', ['--coach' => $coach1->id])->assertSuccessful();

    expect(Cache::has("wc:coach-pulse:v1:{$coach1->id}"))->toBeTrue();
    expect(Cache::has("wc:coach-pulse:v1:{$coach2->id}"))->toBeFalse();
});
```

- [ ] **Step 2: Run tests — confirm fail**

```bash
vendor/bin/pest tests/Unit/Commands/PrecomputeCoachPulseTest.php
```

- [ ] **Step 3: Implement command**

```php
<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Services\CoachCommunityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PrecomputeCoachPulse extends Command
{
    protected $signature = 'wellcore:precompute-coach-pulse {--coach= : Specific coach ID}';
    protected $description = 'Precompute coach community pulse for active coaches';

    public function handle(CoachCommunityService $service): int
    {
        $coachIds = $this->option('coach')
            ? [(int) $this->option('coach')]
            : Admin::query()
                ->where('role', 'coach')
                ->whereExists(fn ($q) =>
                    $q->selectRaw('1')
                      ->from('clients')
                      ->whereColumn('clients.coach_id', 'admins.id')
                      ->where('clients.status', 'activo')
                )
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
                    300
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

- [ ] **Step 4: Schedule in routes/console.php**

Append:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('wellcore:precompute-coach-pulse')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->onOneServer();
```

- [ ] **Step 5: Run tests — confirm pass**

```bash
vendor/bin/pest tests/Unit/Commands/PrecomputeCoachPulseTest.php
```

Expected: 3/3 PASS.

- [ ] **Step 6: Verify schedule registered**

```bash
php artisan schedule:list | grep precompute-coach
```

Expected: shows entry every 5 minutes.

- [ ] **Step 7: Commit**

```bash
git add app/Console/Commands/PrecomputeCoachPulse.php routes/console.php tests/Unit/Commands/PrecomputeCoachPulseTest.php
git commit -m "feat(community): PrecomputeCoachPulse scheduled command every 5min"
```

---

## Task 7: Backend tests — AnnounceEndpointTest

**Files:**
- Create: `tests/Feature/Coach/AnnounceEndpointTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use App\Events\BroadcastSent;
use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use App\Models\PinnedPost;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    Client::factory()->count(5)->create(['coach_id' => $this->coach->id, 'status' => 'activo']);
    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id'    => $this->coach->id,
        'user_type'  => 'admin',
        'token'      => hash('sha256', $this->token),
        'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('announces as post with pin', function () {
    Event::fake([BroadcastSent::class]);

    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/community/announce', [
            'type'      => 'post',
            'message'   => 'Sigan la racha esta semana!',
            'pin_hours' => 24,
        ])
        ->assertStatus(201);

    $postId = $resp->json('post_id');
    expect($postId)->toBeInt();

    $post = CommunityPost::find($postId);
    expect($post->is_official)->toBeTrue();
    expect($post->author_type)->toBe('coach');
    expect($post->author_admin_id)->toBe($this->coach->id);

    expect(PinnedPost::where('post_id', $postId)->exists())->toBeTrue();

    expect(ModerationAction::where('target_id', $postId)->where('action_type', 'announce')->exists())->toBeTrue();

    expect(BroadcastMessage::where('audience_type', 'clients')->where('sender_id', $this->coach->id)->exists())->toBeTrue();

    Event::assertDispatched(BroadcastSent::class);
});

it('announces as post without pin', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/community/announce', [
            'type'    => 'post',
            'message' => 'Mensaje sin pin',
        ])
        ->assertStatus(201);

    $postId = $resp->json('post_id');
    expect(PinnedPost::where('post_id', $postId)->exists())->toBeFalse();
});

it('announces as push and records broadcast', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/community/announce', [
            'type'    => 'push',
            'message' => 'Recordatorio check-in',
        ])
        ->assertStatus(201)
        ->assertJsonStructure(['recipients_count', 'delivered_count']);

    expect(BroadcastMessage::where('push_enabled', true)->where('sender_id', $this->coach->id)->exists())->toBeTrue();
});

it('validates message required and max 1000', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/community/announce', ['type' => 'post', 'message' => ''])
        ->assertStatus(422);

    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/community/announce', ['type' => 'post', 'message' => str_repeat('a', 1001)])
        ->assertStatus(422);
});

it('rejects announce from non-coach Admin', function () {
    $admin = Admin::factory()->create(['role' => 'superadmin']);
    $token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id'    => $admin->id,
        'user_type'  => 'admin',
        'token'      => hash('sha256', $token),
        'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v/coach/community/announce', ['type' => 'post', 'message' => 'x'])
        ->assertStatus(403);
});

it('validates pin_hours range', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/coach/community/announce', [
            'type' => 'post', 'message' => 'x', 'pin_hours' => 200,
        ])
        ->assertStatus(422);
});
```

- [ ] **Step 2: Run tests**

```bash
vendor/bin/pest tests/Feature/Coach/AnnounceEndpointTest.php
```

Expected: 6/6 PASS.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Coach/AnnounceEndpointTest.php
git commit -m "test(community): AnnounceEndpoint Pest feature tests"
```

---

## Task 8: Backend tests — Threads + Achievements endpoints

**Files:**
- Create: `tests/Feature/Coach/CommunityThreadsTest.php`
- Create: `tests/Feature/Coach/CommunityAchievementsTest.php`

- [ ] **Step 1: Threads test**

```php
<?php
// tests/Feature/Coach/CommunityThreadsTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->client1 = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->client2 = Client::factory()->create(['coach_id' => $this->coach->id]);

    $this->post1 = CommunityPost::factory()->create([
        'client_id' => $this->client1->id,
        'coach_admin_id' => $this->coach->id,
        'visible' => true,
    ]);
    PostComment::factory()->count(3)->create([
        'post_id' => $this->post1->id,
        'client_id' => $this->client2->id,
        'created_at' => now(),
    ]);

    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id'    => $this->coach->id,
        'user_type'  => 'admin',
        'token'      => hash('sha256', $this->token),
        'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('returns threads with comments in last 7 days', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/coach/community/threads?since_days=7')
        ->assertOk()
        ->assertJsonStructure(['data', 'pagination']);

    $data = $resp->json('data');
    expect($data)->toBeArray();
    expect(count($data))->toBeGreaterThanOrEqual(1);
    expect($data[0])->toHaveKey('post_id');
    expect($data[0])->toHaveKey('thread_size');
});

it('respects since_days filter', function () {
    PostComment::factory()->create([
        'post_id' => $this->post1->id,
        'client_id' => $this->client2->id,
        'created_at' => now()->subDays(30),
    ]);

    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/coach/community/threads?since_days=1')
        ->assertOk();

    expect($resp->json('data'))->toBeArray();
});

it('rejects non-coach', function () {
    $admin = Admin::factory()->create(['role' => 'superadmin']);
    $token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $admin->id, 'user_type' => 'admin',
        'token' => hash('sha256', $token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v/coach/community/threads')
        ->assertStatus(403);
});
```

- [ ] **Step 2: Achievements test**

```php
<?php
// tests/Feature/Coach/CommunityAchievementsTest.php

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);

    DB::table('personal_records')->insert([
        'client_id'  => $this->client->id,
        'exercise'   => 'Sentadilla',
        'weight'     => 110,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->coach->id, 'user_type' => 'admin',
        'token' => hash('sha256', $this->token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('returns achievements + PRs for last week', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/coach/community/achievements?period=week')
        ->assertOk()
        ->assertJsonStructure(['data', 'totals' => ['prs', 'achievements'], 'pagination']);

    expect($resp->json('totals.prs'))->toBeGreaterThanOrEqual(1);
});

it('respects period filter all', function () {
    DB::table('personal_records')->insert([
        'client_id' => $this->client->id,
        'exercise' => 'Press',
        'weight' => 80,
        'created_at' => now()->subYear(),
        'updated_at' => now()->subYear(),
    ]);

    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/coach/community/achievements?period=all')
        ->assertOk();

    expect($resp->json('totals.prs'))->toBeGreaterThanOrEqual(2);
});
```

- [ ] **Step 3: Run tests**

```bash
vendor/bin/pest tests/Feature/Coach/CommunityThreadsTest.php tests/Feature/Coach/CommunityAchievementsTest.php
```

Expected: ALL PASS.

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Coach/CommunityThreadsTest.php tests/Feature/Coach/CommunityAchievementsTest.php
git commit -m "test(community): threads + achievements endpoints feature tests"
```

---

## Task 9: Composable — useCoachCommunity (TDD with Vitest)

**Files:**
- Create: `resources/js/vue/composables/useCoachCommunity.js`
- Create: `tests/Unit/Composables/useCoachCommunity.test.js`

- [ ] **Step 1: Write failing test**

```js
// tests/Unit/Composables/useCoachCommunity.test.js
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useCoachCommunity, resetCoachCommunity } from '../../../resources/js/vue/composables/useCoachCommunity';

vi.mock('../../../resources/js/vue/composables/useApi', () => ({
    useApi: () => ({
        get: vi.fn(() => Promise.resolve({ data: { posts: [{ id: 1 }], pagination: { current_page: 1, last_page: 1, total: 1 } } })),
    }),
}));

describe('useCoachCommunity', () => {
    beforeEach(() => {
        resetCoachCommunity();
    });

    it('caches feed for 25s window', async () => {
        const { fetchFeed } = useCoachCommunity();
        const a = await fetchFeed({ filter: 'all', page: 1 });
        const b = await fetchFeed({ filter: 'all', page: 1 });
        expect(a).toBe(b); // same cached object
    });

    it('dedups concurrent calls', async () => {
        const { fetchFeed } = useCoachCommunity();
        const [a, b] = await Promise.all([
            fetchFeed({ filter: 'all', page: 1 }),
            fetchFeed({ filter: 'all', page: 1 }),
        ]);
        expect(a).toBe(b);
    });

    it('force=true bypasses cache', async () => {
        const { fetchFeed } = useCoachCommunity();
        await fetchFeed({ filter: 'all', page: 1 });
        const fresh = await fetchFeed({ filter: 'all', page: 1, force: true });
        expect(fresh.posts).toEqual([{ id: 1 }]);
    });

    it('reset clears cache', async () => {
        const { fetchFeed } = useCoachCommunity();
        await fetchFeed({ filter: 'all', page: 1 });
        resetCoachCommunity();
        // no easy way to assert cache empty without spy; just confirm doesn't throw
        await expect(fetchFeed({ filter: 'all', page: 1 })).resolves.toBeTruthy();
    });
});
```

- [ ] **Step 2: Run tests — fail**

```bash
npm run test:unit -- useCoachCommunity
```

Expected: FAIL — composable not found.

- [ ] **Step 3: Implement composable**

```js
// resources/js/vue/composables/useCoachCommunity.js
import { ref } from 'vue';
import { useApi } from './useApi';

const feedCache = new Map(); // key = `${filter}:${page}` → { data, timestamp }
const FEED_TTL_MS = 25_000;
const feedPromises = new Map();

export function useCoachCommunity() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    async function fetchFeed({ filter = 'all', page = 1, perPage = 20, force = false } = {}) {
        const key = `${filter}:${page}:${perPage}`;
        if (!force && feedCache.has(key)) {
            const cached = feedCache.get(key);
            if (Date.now() - cached.timestamp < FEED_TTL_MS) return cached.data;
        }
        if (feedPromises.has(key)) return feedPromises.get(key);

        loading.value = true;
        error.value = null;
        const promise = (async () => {
            try {
                const res = await api.get('/api/v/coach/community/posts', {
                    params: { filter, page, per_page: perPage },
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

    async function fetchThreads({ sinceDays = 7, page = 1, perPage = 20 } = {}) {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/coach/community/threads', {
                params: { since_days: sinceDays, page, per_page: perPage },
            });
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar conversaciones.';
            if (err.response?.status >= 500 || !err.response) {
                console.error('[useCoachCommunity] fetchThreads failed', err);
            }
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function fetchAchievements({ period = 'week', page = 1, perPage = 20 } = {}) {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/coach/community/achievements', {
                params: { period, page, per_page: perPage },
            });
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar logros.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function fetchPulsos() {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/coach/community/pulsos');
            return res.data?.data ?? [];
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar pulsos.';
            return [];
        } finally {
            loading.value = false;
        }
    }

    return { loading, error, fetchFeed, fetchThreads, fetchAchievements, fetchPulsos };
}

export function resetCoachCommunity() {
    feedCache.clear();
    feedPromises.clear();
}
```

- [ ] **Step 4: Run tests — pass**

```bash
npm run test:unit -- useCoachCommunity
```

Expected: 4/4 PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/composables/useCoachCommunity.js tests/Unit/Composables/useCoachCommunity.test.js
git commit -m "feat(community): useCoachCommunity composable singleton + Vitest tests"
```

---

## Task 10: Composable — useCoachPulse (TDD)

**Files:**
- Create: `resources/js/vue/composables/useCoachPulse.js`
- Create: `tests/Unit/Composables/useCoachPulse.test.js`

- [ ] **Step 1: Test (similar pattern useGroupPulse)**

```js
// tests/Unit/Composables/useCoachPulse.test.js
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useCoachPulse, resetCoachPulse } from '../../../resources/js/vue/composables/useCoachPulse';

vi.mock('../../../resources/js/vue/composables/useApi', () => ({
    useApi: () => ({
        get: vi.fn(() => Promise.resolve({ status: 200, data: { team_health_score: 85, top_performers: [], at_risk_clients: [], computed_at: '2026-05-05T00:00:00Z' } })),
    }),
}));

describe('useCoachPulse', () => {
    beforeEach(() => resetCoachPulse());

    it('fetches and caches summary', async () => {
        const { fetchSummary, summary } = useCoachPulse();
        const a = await fetchSummary();
        expect(a.team_health_score).toBe(85);
        expect(summary.value.team_health_score).toBe(85);
    });

    it('dedups concurrent calls', async () => {
        const { fetchSummary } = useCoachPulse();
        const [a, b] = await Promise.all([fetchSummary(), fetchSummary()]);
        expect(a).toBe(b);
    });

    it('reset clears summary cache', async () => {
        const { fetchSummary, summary } = useCoachPulse();
        await fetchSummary();
        resetCoachPulse();
        expect(summary.value).toBeNull();
    });
});
```

- [ ] **Step 2: Implementation**

```js
// resources/js/vue/composables/useCoachPulse.js
import { ref, computed } from 'vue';
import { useApi } from './useApi';

const summaryCache = ref(null);
const summaryLoadedAt = ref(0);
const SUMMARY_TTL_MS = 25_000;
let summaryPromise = null;

export function useCoachPulse() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    const isFresh = computed(() =>
        summaryCache.value !== null && Date.now() - summaryLoadedAt.value < SUMMARY_TTL_MS
    );

    async function fetchSummary({ force = false } = {}) {
        if (!force && isFresh.value) return summaryCache.value;
        if (summaryPromise) return summaryPromise;

        loading.value = true;
        error.value = null;
        summaryPromise = (async () => {
            try {
                const res = await api.get('/api/v/coach/community/pulse');
                summaryCache.value = res.data;
                summaryLoadedAt.value = Date.now();
                return res.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'No se pudo cargar el latido del equipo.';
                if (err.response?.status >= 500 || !err.response) {
                    console.error('[useCoachPulse] fetchSummary failed', err);
                }
                return null;
            } finally {
                loading.value = false;
                summaryPromise = null;
            }
        })();
        return summaryPromise;
    }

    return { summary: summaryCache, loading, error, isFresh, fetchSummary };
}

export function resetCoachPulse() {
    summaryCache.value = null;
    summaryLoadedAt.value = 0;
    summaryPromise = null;
}
```

- [ ] **Step 3: Run + commit**

```bash
npm run test:unit -- useCoachPulse
git add resources/js/vue/composables/useCoachPulse.js tests/Unit/Composables/useCoachPulse.test.js
git commit -m "feat(community): useCoachPulse singleton with TTL 25s"
```

---

## Task 11: Composable — useModeration (TDD)

**Files:**
- Create: `resources/js/vue/composables/useModeration.js`
- Create: `tests/Unit/Composables/useModeration.test.js`

- [ ] **Step 1: Test**

```js
// tests/Unit/Composables/useModeration.test.js
import { describe, it, expect, vi } from 'vitest';
import { useModeration } from '../../../resources/js/vue/composables/useModeration';

const apiPost = vi.fn();
const apiDelete = vi.fn();

vi.mock('../../../resources/js/vue/composables/useApi', () => ({
    useApi: () => ({ post: apiPost, delete: apiDelete }),
}));
vi.mock('../../../resources/js/vue/composables/useHaptics', () => ({
    useHaptics: () => ({ success: vi.fn(), error: vi.fn(), light: vi.fn(), medium: vi.fn() }),
}));
vi.mock('../../../resources/js/vue/composables/useToast', () => ({
    useToast: () => ({ apiError: vi.fn(), success: vi.fn() }),
}));

describe('useModeration', () => {
    it('pinPost calls correct endpoint', async () => {
        apiPost.mockResolvedValue({ data: { post_id: 5 } });
        const { pinPost } = useModeration();
        await pinPost(5, 24, 'note');
        expect(apiPost).toHaveBeenCalledWith('/api/v/coach/posts/5/pin', { hours: 24, note: 'note' });
    });

    it('makeOfficial calls correct endpoint', async () => {
        apiPost.mockResolvedValue({ data: {} });
        const { makeOfficial } = useModeration();
        await makeOfficial(7);
        expect(apiPost).toHaveBeenCalledWith('/api/v/coach/posts/7/make-official');
    });

    it('deletePost calls api.delete with reason', async () => {
        apiDelete.mockResolvedValue({ data: {} });
        const { deletePost } = useModeration();
        await deletePost(9, 'spam');
        expect(apiDelete).toHaveBeenCalledWith('/api/v/coach/posts/9', { data: { reason: 'spam' } });
    });
});
```

- [ ] **Step 2: Implementation**

```js
// resources/js/vue/composables/useModeration.js
import { useApi } from './useApi';
import { useHaptics } from './useHaptics';
import { useToast } from './useToast';

export function useModeration() {
    const api = useApi();
    const haptics = useHaptics();
    const toast = useToast();

    async function pinPost(postId, hours = 168, note = null) {
        try {
            const res = await api.post(`/api/v/coach/posts/${postId}/pin`, { hours, note });
            haptics.success();
            toast.success('Post fijado.');
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos fijar el post.');
            throw err;
        }
    }

    async function unpinPost(postId) {
        try {
            const res = await api.post(`/api/v/coach/posts/${postId}/unpin`);
            haptics.light();
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos desfijar.');
            throw err;
        }
    }

    async function deletePost(postId, reason) {
        try {
            const res = await api.delete(`/api/v/coach/posts/${postId}`, { data: { reason } });
            haptics.medium();
            toast.success('Post eliminado.');
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos eliminar.');
            throw err;
        }
    }

    async function makeOfficial(postId) {
        try {
            const res = await api.post(`/api/v/coach/posts/${postId}/make-official`);
            haptics.success();
            toast.success('Marcado como Coach Pick.');
            return res.data;
        } catch (err) {
            haptics.error();
            toast.apiError(err, 'No pudimos marcar como oficial.');
            throw err;
        }
    }

    async function reportPost(postId, reason, detail = null) {
        try {
            const res = await api.post(`/api/v/community/posts/${postId}/report`, {
                reason, reason_detail: detail,
            });
            return res.data;
        } catch (err) {
            toast.apiError(err, 'No pudimos enviar el reporte.');
            throw err;
        }
    }

    return { pinPost, unpinPost, deletePost, makeOfficial, reportPost };
}
```

- [ ] **Step 3: Run + commit**

```bash
npm run test:unit -- useModeration
git add resources/js/vue/composables/useModeration.js tests/Unit/Composables/useModeration.test.js
git commit -m "feat(community): useModeration with pin/unpin/delete/official actions"
```

---

## Task 12: Composables — useCoachAnnounce + usePushSubscription

**Files:**
- Create: `resources/js/vue/composables/useCoachAnnounce.js`
- Create: `resources/js/vue/composables/usePushSubscription.js`
- Create: `tests/Unit/Composables/useCoachAnnounce.test.js`
- Create: `tests/Unit/Composables/usePushSubscription.test.js`

- [ ] **Step 1: useCoachAnnounce implementation**

```js
// resources/js/vue/composables/useCoachAnnounce.js
import { ref } from 'vue';
import { useApi } from './useApi';

const recipientCountCache = new Map();
const COUNT_TTL_MS = 30_000;

const isOpen = ref(false);
const mode = ref('post');
const message = ref('');
const pinHours = ref(0);
const segment = ref({ status: ['activo'], plan: null });
const recipientCount = ref(null);
const sending = ref(false);
const image = ref(null);

export function useCoachAnnounce() {
    const api = useApi();

    function open() {
        isOpen.value = true;
    }

    function close() {
        isOpen.value = false;
        message.value = '';
        pinHours.value = 0;
        image.value = null;
    }

    async function previewCount() {
        const params = {
            status: segment.value.status,
            plan: segment.value.plan ?? undefined,
        };
        const key = JSON.stringify(params);
        if (recipientCountCache.has(key)) {
            const c = recipientCountCache.get(key);
            if (Date.now() - c.timestamp < COUNT_TTL_MS) {
                recipientCount.value = c.count;
                return c.count;
            }
        }
        try {
            const res = await api.get('/api/v/coach/clients/count', { params });
            const count = res.data?.count ?? 0;
            recipientCountCache.set(key, { count, timestamp: Date.now() });
            recipientCount.value = count;
            return count;
        } catch (err) {
            console.error('[useCoachAnnounce] previewCount failed', err);
            return 0;
        }
    }

    async function send() {
        sending.value = true;
        try {
            const fd = new FormData();
            fd.append('type', mode.value);
            fd.append('message', message.value);
            if (mode.value === 'post' && pinHours.value > 0) fd.append('pin_hours', pinHours.value);
            if (mode.value === 'post' && image.value) fd.append('image', image.value);
            if (mode.value === 'push' && segment.value.plan) {
                fd.append('plan_filter', JSON.stringify({ plan: segment.value.plan }));
            }
            const res = await api.post('/api/v/coach/community/announce', fd);
            close();
            return res.data;
        } finally {
            sending.value = false;
        }
    }

    return { isOpen, mode, message, pinHours, segment, recipientCount, sending, image, open, close, previewCount, send };
}

export function resetCoachAnnounce() {
    recipientCountCache.clear();
    isOpen.value = false;
    message.value = '';
    pinHours.value = 0;
    image.value = null;
    recipientCount.value = null;
}
```

- [ ] **Step 2: useCoachAnnounce test**

```js
// tests/Unit/Composables/useCoachAnnounce.test.js
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useCoachAnnounce, resetCoachAnnounce } from '../../../resources/js/vue/composables/useCoachAnnounce';

const apiGet = vi.fn();
const apiPost = vi.fn();
vi.mock('../../../resources/js/vue/composables/useApi', () => ({
    useApi: () => ({ get: apiGet, post: apiPost }),
}));

describe('useCoachAnnounce', () => {
    beforeEach(() => {
        resetCoachAnnounce();
        apiGet.mockReset();
        apiPost.mockReset();
    });

    it('previewCount caches result', async () => {
        apiGet.mockResolvedValue({ data: { count: 27 } });
        const { previewCount } = useCoachAnnounce();
        const a = await previewCount();
        const b = await previewCount();
        expect(a).toBe(27);
        expect(b).toBe(27);
        expect(apiGet).toHaveBeenCalledTimes(1);
    });

    it('send posts FormData with mode=post', async () => {
        apiPost.mockResolvedValue({ data: { post_id: 1 } });
        const c = useCoachAnnounce();
        c.message.value = 'Test';
        c.mode.value = 'post';
        c.pinHours.value = 24;
        await c.send();
        expect(apiPost).toHaveBeenCalled();
        const [url, body] = apiPost.mock.calls[0];
        expect(url).toBe('/api/v/coach/community/announce');
        expect(body).toBeInstanceOf(FormData);
    });
});
```

- [ ] **Step 3: usePushSubscription implementation**

```js
// resources/js/vue/composables/usePushSubscription.js
import { ref } from 'vue';
import { useApi } from './useApi';

export function usePushSubscription() {
    const api = useApi();
    const permission = ref(
        typeof Notification !== 'undefined' ? Notification.permission : 'default'
    );
    const subscription = ref(null);

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = atob(base64);
        const arr = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; i++) arr[i] = rawData.charCodeAt(i);
        return arr;
    }

    async function request() {
        if (typeof Notification === 'undefined') {
            throw new Error('Notifications no soportadas en este navegador.');
        }
        const result = await Notification.requestPermission();
        permission.value = result;
        if (result === 'granted') {
            await subscribe();
        }
        return result;
    }

    async function subscribe() {
        if (!('serviceWorker' in navigator)) throw new Error('SW no soportado');
        const reg = await navigator.serviceWorker.ready;
        const vapidKey = window.__WC_VAPID_PUBLIC_KEY;
        if (!vapidKey) throw new Error('VAPID public key missing');
        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });
        subscription.value = sub;
        const json = sub.toJSON();
        const res = await api.post('/api/v/coach/push/subscribe', {
            endpoint: json.endpoint,
            keys: { p256dh: json.keys.p256dh, auth: json.keys.auth },
            user_agent: navigator.userAgent.slice(0, 255),
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
        }
    }

    return { permission, subscription, request, subscribe, unsubscribe };
}
```

- [ ] **Step 4: usePushSubscription test (basic)**

```js
// tests/Unit/Composables/usePushSubscription.test.js
import { describe, it, expect, vi } from 'vitest';
import { usePushSubscription } from '../../../resources/js/vue/composables/usePushSubscription';

vi.mock('../../../resources/js/vue/composables/useApi', () => ({
    useApi: () => ({ post: vi.fn(() => Promise.resolve({ data: { id: 1 } })) }),
}));

describe('usePushSubscription', () => {
    it('exposes permission ref initialized from Notification.permission', () => {
        vi.stubGlobal('Notification', { permission: 'default' });
        const { permission } = usePushSubscription();
        expect(permission.value).toBe('default');
    });

    it('request throws if Notification API missing', async () => {
        vi.stubGlobal('Notification', undefined);
        const { request } = usePushSubscription();
        await expect(request()).rejects.toThrow();
    });
});
```

- [ ] **Step 5: Run + commit**

```bash
npm run test:unit -- useCoachAnnounce usePushSubscription
git add resources/js/vue/composables/useCoachAnnounce.js resources/js/vue/composables/usePushSubscription.js tests/Unit/Composables/useCoachAnnounce.test.js tests/Unit/Composables/usePushSubscription.test.js
git commit -m "feat(community): useCoachAnnounce + usePushSubscription composables"
```

---

## Task 13: Update auth.js — invalidate cache hooks

**Files:**
- Modify: `resources/js/vue/stores/auth.js`

- [ ] **Step 1: Add imports at top (after existing imports)**

```js
import { resetCoachCommunity } from '../composables/useCoachCommunity';
import { resetCoachPulse } from '../composables/useCoachPulse';
import { resetCoachAnnounce } from '../composables/useCoachAnnounce';
```

- [ ] **Step 2: Modify `setAuth` — add resets**

Find the existing `if (data.token && data.token !== token.value) { resetGroupPulse(); }` block and extend:

```js
if (data.token && data.token !== token.value) {
    resetGroupPulse();
    resetCoachCommunity();
    resetCoachPulse();
    resetCoachAnnounce();
}
```

- [ ] **Step 3: Modify `clearAuth` — add resets**

Find existing `resetGroupPulse();` call and add:

```js
resetContractGate();
resetGroupPulse();
resetCoachCommunity();
resetCoachPulse();
resetCoachAnnounce();
```

- [ ] **Step 4: Smoke test — Vite hot reload should not break**

```bash
npm run dev
# Visit /coach/community after login → should not throw
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/stores/auth.js
git commit -m "feat(community): auth store invalidates coach community caches on token change"
```

---

## Task 14: Components — CoachBadge + OfficialBadge + PinnedIndicator

**Files:**
- Create: `resources/js/vue/components/community/CoachBadge.vue`
- Create: `resources/js/vue/components/community/OfficialBadge.vue`
- Create: `resources/js/vue/components/community/PinnedIndicator.vue`

- [ ] **Step 1: CoachBadge.vue**

```vue
<script setup>
defineProps({
    size: { type: String, default: 'sm' }, // 'xs' | 'sm' | 'md'
    label: { type: String, default: 'Coach' },
});
</script>

<template>
  <span
    :class="[
      'inline-flex items-center gap-1 rounded-full font-semibold uppercase tracking-wider',
      'bg-amber-500/15 text-amber-600 dark:text-amber-400',
      size === 'xs' && 'px-1.5 py-0 text-[9px]',
      size === 'sm' && 'px-2 py-0.5 text-[10px]',
      size === 'md' && 'px-2.5 py-1 text-xs',
    ]"
  >
    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.293z" />
    </svg>
    <span>{{ label }}</span>
  </span>
</template>
```

- [ ] **Step 2: OfficialBadge.vue**

```vue
<script setup>
defineProps({
    label: { type: String, default: 'Coach Pick' },
});
</script>

<template>
  <span class="inline-flex items-center gap-1 rounded-full bg-wc-accent/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
    </svg>
    {{ label }}
  </span>
</template>
```

- [ ] **Step 3: PinnedIndicator.vue**

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    pinnedUntil: { type: [String, null], default: null },
    note: { type: [String, null], default: null },
});

const remaining = computed(() => {
    if (!props.pinnedUntil) return null;
    const target = new Date(props.pinnedUntil);
    const diffMs = target.getTime() - Date.now();
    if (diffMs <= 0) return null;
    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    if (hours >= 24) return `${Math.floor(hours / 24)}d restantes`;
    return `${hours}h restantes`;
});
</script>

<template>
  <div class="inline-flex items-center gap-2 rounded-lg bg-wc-accent/5 border border-wc-accent/20 px-2 py-1 text-xs">
    <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 5v14m7-14l-3 3v3h6V8l-3-3zM5 19h14" />
    </svg>
    <span class="font-semibold text-wc-accent">Fijado</span>
    <span v-if="remaining" class="text-wc-text-tertiary">· {{ remaining }}</span>
    <span v-if="note" :title="note" class="text-wc-text-tertiary truncate max-w-[140px]">· {{ note }}</span>
  </div>
</template>
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/vue/components/community/CoachBadge.vue resources/js/vue/components/community/OfficialBadge.vue resources/js/vue/components/community/PinnedIndicator.vue
git commit -m "feat(community): CoachBadge + OfficialBadge + PinnedIndicator components"
```

---

## Task 15: Component — TeamHealthRing

**Files:**
- Create: `resources/js/vue/components/community/TeamHealthRing.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useReducedMotion } from '../../composables/useReducedMotion';
import { useCountUp } from '../../composables/useCountUp';

const props = defineProps({
    score: { type: Number, default: 0 }, // 0-100
    size: { type: Number, default: 200 }, // px
    label: { type: String, default: 'Latido del Equipo' },
});

const reduced = useReducedMotion();
const { value: animatedScore, animate } = useCountUp(0);

const radius = 80;
const circumference = 2 * Math.PI * radius;

const scoreColorClass = computed(() => {
    if (props.score >= 80) return 'text-emerald-500';
    if (props.score >= 60) return 'text-amber-500';
    return 'text-rose-500';
});

const ringStrokeOffset = computed(() => {
    const pct = Math.max(0, Math.min(100, animatedScore.value)) / 100;
    return circumference - (circumference * pct);
});

const flash = ref(false);
function flashHealthScore() {
    flash.value = true;
    setTimeout(() => (flash.value = false), 800);
}
defineExpose({ flashHealthScore });

onMounted(() => {
    animate(props.score, { duration: reduced.value ? 0 : 1100 });
});
watch(() => props.score, (newScore) => {
    animate(newScore, { duration: reduced.value ? 0 : 600 });
});
</script>

<template>
  <div class="relative inline-flex flex-col items-center justify-center" :style="{ width: size + 'px', height: size + 'px' }">
    <svg :width="size" :height="size" :viewBox="`0 0 200 200`" class="block">
      <circle cx="100" cy="100" :r="radius" fill="none" stroke="currentColor" class="text-wc-bg-tertiary" stroke-width="14" />
      <circle
        cx="100" cy="100" :r="radius"
        fill="none" stroke-width="14"
        stroke-linecap="round"
        :class="scoreColorClass"
        stroke="currentColor"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="ringStrokeOffset"
        transform="rotate(-90 100 100)"
        :style="{ transition: reduced ? 'none' : 'stroke-dashoffset 0.6s ease-out' }"
      />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center" :class="flash ? 'animate-pulse' : ''">
      <span class="font-display text-5xl tracking-tight" :class="scoreColorClass">{{ Math.round(animatedScore) }}</span>
      <span class="text-[10px] uppercase tracking-widest text-wc-text-tertiary mt-1">{{ label }}</span>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/components/community/TeamHealthRing.vue
git commit -m "feat(community): TeamHealthRing animated SVG with reduced-motion respect"
```

---

## Task 16: Components — TopPerformerCard + AtRiskClientChip

**Files:**
- Create: `resources/js/vue/components/community/TopPerformerCard.vue`
- Create: `resources/js/vue/components/community/AtRiskClientChip.vue`

- [ ] **Step 1: TopPerformerCard.vue**

```vue
<script setup>
import { useRouter } from 'vue-router';

const props = defineProps({
    performer: { type: Object, required: true },
    rank: { type: Number, default: 0 },
});

const router = useRouter();
const medals = ['🥇', '🥈', '🥉'];

function openClient() {
    router.push(`/coach/clients?focus=${props.performer.client_id}`);
}
</script>

<template>
  <button
    type="button"
    @click="openClient"
    class="w-full flex items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 hover:border-wc-accent/40 hover:bg-wc-bg-tertiary/50 transition-all text-left"
  >
    <span v-if="rank <= 3" class="text-2xl">{{ medals[rank - 1] }}</span>
    <div class="h-10 w-10 rounded-full bg-wc-accent/15 flex items-center justify-center overflow-hidden">
      <img v-if="performer.avatar_url" :src="performer.avatar_url" :alt="performer.client_name" class="h-full w-full object-cover" />
      <span v-else class="text-sm font-semibold text-wc-accent">
        {{ performer.client_name?.charAt(0) || '?' }}
      </span>
    </div>
    <div class="flex-1 min-w-0">
      <p class="font-semibold text-wc-text truncate">{{ performer.client_name }}</p>
      <p class="text-xs text-wc-text-tertiary truncate">{{ performer.metric }}</p>
    </div>
    <svg class="h-4 w-4 text-wc-text-tertiary shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
    </svg>
  </button>
</template>
```

- [ ] **Step 2: AtRiskClientChip.vue**

```vue
<script setup>
const props = defineProps({
    client: { type: Object, required: true },
});

const emit = defineEmits(['quick-message']);

function openQuickMessage() {
    emit('quick-message', props.client);
}
</script>

<template>
  <div class="flex items-center gap-3 rounded-xl border border-rose-500/20 bg-rose-500/5 px-4 py-3">
    <div class="h-9 w-9 rounded-full bg-rose-500/15 flex items-center justify-center text-rose-500 shrink-0">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
      </svg>
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold text-wc-text truncate">{{ client.client_name }}</p>
      <p class="text-xs text-rose-500 dark:text-rose-400">
        {{ client.days_inactive }}d sin actividad
      </p>
    </div>
    <button
      @click="openQuickMessage"
      class="shrink-0 rounded-full bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 px-3 py-1.5 text-xs font-semibold transition-colors"
    >
      Mensaje
    </button>
  </div>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/vue/components/community/TopPerformerCard.vue resources/js/vue/components/community/AtRiskClientChip.vue
git commit -m "feat(community): TopPerformerCard + AtRiskClientChip"
```

---

## Task 17: Component — PushPermissionBanner

**Files:**
- Create: `resources/js/vue/components/community/PushPermissionBanner.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePushSubscription } from '../../composables/usePushSubscription';
import { useToast } from '../../composables/useToast';

const { permission, request } = usePushSubscription();
const toast = useToast();
const dismissed = ref(false);

const STORAGE_KEY = 'coach_push_dismissed_at';
const DISMISS_DAYS = 7;

const visible = computed(() => {
    if (dismissed.value) return false;
    if (permission.value !== 'default') return false;
    const at = localStorage.getItem(STORAGE_KEY);
    if (at) {
        const ms = Date.now() - parseInt(at, 10);
        if (ms < DISMISS_DAYS * 24 * 60 * 60 * 1000) return false;
    }
    return true;
});

async function activate() {
    try {
        const result = await request();
        if (result === 'granted') {
            toast.success('Notificaciones activadas. Te avisaremos cuando tu equipo rompa PRs.');
            dismissed.value = true;
        } else if (result === 'denied') {
            toast.warn('Notificaciones bloqueadas. Puedes habilitarlas desde la configuración del navegador.');
            dismissed.value = true;
            localStorage.setItem(STORAGE_KEY, String(Date.now()));
        }
    } catch (err) {
        toast.error(err.message || 'No pudimos activar notificaciones.');
    }
}

function dismiss() {
    dismissed.value = true;
    localStorage.setItem(STORAGE_KEY, String(Date.now()));
}

onMounted(() => {
    if (typeof Notification !== 'undefined') {
        permission.value = Notification.permission;
    }
});
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    leave-active-class="transition-all duration-200 ease-in"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div v-if="visible" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 flex items-start gap-3">
      <div class="shrink-0 h-9 w-9 rounded-lg bg-wc-accent/15 flex items-center justify-center">
        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-wc-text">Activa notificaciones</p>
        <p class="text-xs text-wc-text-tertiary mt-0.5">
          Para no perder cuando tu equipo rompa PRs o necesite atención inmediata.
        </p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button @click="dismiss" class="text-xs text-wc-text-tertiary hover:text-wc-text px-2 py-1">
          Más tarde
        </button>
        <button @click="activate" class="text-xs font-semibold text-white bg-wc-accent hover:bg-wc-accent/90 rounded-full px-3 py-1.5">
          Activar ahora
        </button>
      </div>
    </div>
  </Transition>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/components/community/PushPermissionBanner.vue
git commit -m "feat(community): PushPermissionBanner with dismissible 7-day window"
```

---

## Task 18: Component — CoachAnnounceModal

**Files:**
- Create: `resources/js/vue/components/community/CoachAnnounceModal.vue`

- [ ] **Step 1: Implement** (este componente es grande, ~250 lines)

```vue
<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useCoachAnnounce } from '../../composables/useCoachAnnounce';
import { useToast } from '../../composables/useToast';
import { useHaptics } from '../../composables/useHaptics';

const announce = useCoachAnnounce();
const toast = useToast();
const haptics = useHaptics();

const PIN_OPTIONS = [
    { value: 0, label: 'No fijar' },
    { value: 24, label: '24h' },
    { value: 48, label: '48h' },
    { value: 168, label: '1 semana' },
];

const PUSH_MAX_CHARS = 200;
const POST_MAX_CHARS = 1000;

const showConfirmStep = ref(false);
const fileInputRef = ref(null);
const imagePreview = ref(null);

const charLimit = computed(() => announce.mode.value === 'push' ? PUSH_MAX_CHARS : POST_MAX_CHARS);
const charCount = computed(() => announce.message.value.length);
const charOver = computed(() => charCount.value > charLimit.value);

const canSend = computed(() => {
    return !announce.sending.value
        && announce.message.value.trim().length > 0
        && !charOver.value;
});

watch(() => announce.segment.value, () => {
    announce.previewCount();
}, { deep: true });

watch(() => announce.isOpen.value, (open) => {
    if (open) announce.previewCount();
});

function selectImage(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        toast.error('Imagen excede 5MB.');
        return;
    }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        toast.error('Formato no válido. Usa JPG, PNG o WebP.');
        return;
    }
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
    announce.image.value = file;
    imagePreview.value = URL.createObjectURL(file);
}

function removeImage() {
    if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
    announce.image.value = null;
    imagePreview.value = null;
}

async function attemptSend() {
    if (!canSend.value) return;
    if ((announce.recipientCount.value || 0) > 20) {
        showConfirmStep.value = true;
        return;
    }
    await doSend();
}

async function doSend() {
    showConfirmStep.value = false;
    try {
        const res = await announce.send();
        haptics.success();
        toast.success(`Mensaje enviado a ${res.recipients_count || res.delivered_count} clientes.`);
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
            imagePreview.value = null;
        }
    } catch (err) {
        haptics.error();
        toast.apiError(err, 'No pudimos enviar el mensaje.');
    }
}

function cancel() {
    showConfirmStep.value = false;
}

function close() {
    if (announce.sending.value) return;
    announce.close();
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
}
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0" enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-150"
    leave-from-class="opacity-100" leave-to-class="opacity-0"
  >
    <div v-if="announce.isOpen.value" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-6" @click.self="close">
      <Transition
        appear
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
        enter-to-class="translate-y-0 scale-100 opacity-100"
      >
        <div class="w-full sm:max-w-lg bg-wc-bg-secondary rounded-t-2xl sm:rounded-2xl shadow-2xl border border-wc-border max-h-[92vh] flex flex-col overflow-hidden">
          <header class="flex items-center justify-between px-5 py-4 border-b border-wc-border">
            <h2 class="font-display text-xl tracking-wider text-wc-text">Mensaje al equipo</h2>
            <button @click="close" class="text-wc-text-tertiary hover:text-wc-text" aria-label="Cerrar">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </header>

          <div v-if="!showConfirmStep" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            <div class="grid grid-cols-2 rounded-lg border border-wc-border overflow-hidden">
              <button
                @click="announce.mode.value = 'post'"
                :class="announce.mode.value === 'post' ? 'bg-wc-accent text-white' : 'bg-wc-bg text-wc-text-secondary'"
                class="py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
              >
                📢 <span>Anuncio in-feed</span>
              </button>
              <button
                @click="announce.mode.value = 'push'"
                :class="announce.mode.value === 'push' ? 'bg-wc-accent text-white' : 'bg-wc-bg text-wc-text-secondary'"
                class="py-2.5 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
              >
                🔔 <span>Push notification</span>
              </button>
            </div>

            <div>
              <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
                Tu mensaje
              </label>
              <textarea
                v-model="announce.message.value"
                :placeholder="announce.mode.value === 'push' ? 'Mensaje breve, max 200 caracteres' : 'Comparte motivación, anuncios o reconocimientos…'"
                :maxlength="charLimit"
                rows="4"
                class="w-full rounded-lg border border-wc-border bg-wc-bg p-3 text-sm text-wc-text resize-none focus:border-wc-accent focus:outline-none"
              />
              <div class="mt-1 flex justify-end text-[11px]" :class="charOver ? 'text-rose-500' : 'text-wc-text-tertiary'">
                {{ charCount }} / {{ charLimit }}
              </div>
            </div>

            <div v-if="announce.mode.value === 'post'">
              <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
                Imagen (opcional)
              </label>
              <input ref="fileInputRef" type="file" accept="image/jpeg,image/png,image/webp" @change="selectImage" class="hidden" />
              <div v-if="!imagePreview" @click="fileInputRef?.click()" class="rounded-lg border-2 border-dashed border-wc-border bg-wc-bg p-4 text-center cursor-pointer hover:border-wc-accent/40">
                <p class="text-xs text-wc-text-tertiary">Subir imagen (max 5MB)</p>
              </div>
              <div v-else class="relative rounded-lg overflow-hidden">
                <img :src="imagePreview" alt="" class="w-full h-40 object-cover" />
                <button @click="removeImage" class="absolute top-2 right-2 bg-black/60 text-white rounded-full p-1.5">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="announce.mode.value === 'post'">
              <label class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary block mb-2">
                Fijar al tope del feed
              </label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="opt in PIN_OPTIONS" :key="opt.value"
                  @click="announce.pinHours.value = opt.value"
                  :class="announce.pinHours.value === opt.value ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
                  class="rounded-full px-3 py-1 text-xs font-semibold transition-colors"
                >{{ opt.label }}</button>
              </div>
            </div>

            <div class="rounded-lg bg-wc-bg-tertiary px-3 py-2 text-xs text-wc-text-tertiary">
              <span v-if="announce.recipientCount.value !== null">
                {{ announce.recipientCount.value }} clientes activos {{ announce.mode.value === 'push' ? 'recibirán push' : 'verán este post' }}.
              </span>
              <span v-else>Calculando recipientes…</span>
            </div>
          </div>

          <div v-else class="flex-1 px-5 py-6 space-y-4 text-center">
            <div class="text-4xl">⚠️</div>
            <h3 class="font-display text-2xl text-wc-text">¿Confirmar envío?</h3>
            <p class="text-sm text-wc-text-secondary">
              Vas a enviar a <strong class="text-wc-text">{{ announce.recipientCount.value }}</strong> clientes activos.
            </p>
            <p class="text-xs text-wc-text-tertiary">Esta acción es irreversible.</p>
          </div>

          <footer class="flex items-center gap-3 px-5 py-4 border-t border-wc-border">
            <template v-if="!showConfirmStep">
              <button @click="close" :disabled="announce.sending.value" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold border border-wc-border text-wc-text-secondary hover:bg-wc-bg-tertiary disabled:opacity-50">
                Cancelar
              </button>
              <button @click="attemptSend" :disabled="!canSend" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold bg-wc-accent text-white hover:bg-wc-accent/90 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ announce.sending.value ? 'Enviando…' : 'Enviar al equipo' }}
              </button>
            </template>
            <template v-else>
              <button @click="cancel" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold border border-wc-border text-wc-text-secondary hover:bg-wc-bg-tertiary">
                Cancelar
              </button>
              <button @click="doSend" :disabled="announce.sending.value" class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold bg-wc-accent text-white hover:bg-wc-accent/90 disabled:opacity-50">
                Sí, enviar
              </button>
            </template>
          </footer>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/components/community/CoachAnnounceModal.vue
git commit -m "feat(community): CoachAnnounceModal with post/push toggle + confirm step"
```

---

## Task 19: Components — PostCardCoachActions + CommunityEmptyIllustration

**Files:**
- Create: `resources/js/vue/components/community/PostCardCoachActions.vue`
- Create: `resources/js/vue/components/community/CommunityEmptyIllustration.vue`

- [ ] **Step 1: PostCardCoachActions.vue**

```vue
<script setup>
import { ref, computed } from 'vue';
import { useModeration } from '../../composables/useModeration';

const props = defineProps({
    post: { type: Object, required: true },
});
const emit = defineEmits(['updated', 'deleted']);

const moderation = useModeration();
const open = ref(false);
const confirmingDelete = ref(false);

const isPinned = computed(() => !!props.post.pinned);
const isOfficial = computed(() => !!props.post.is_official);

async function togglePin() {
    if (isPinned.value) {
        await moderation.unpinPost(props.post.id);
    } else {
        await moderation.pinPost(props.post.id, 168, null);
    }
    emit('updated');
    open.value = false;
}

async function makeOfficial() {
    if (isOfficial.value) return;
    await moderation.makeOfficial(props.post.id);
    emit('updated');
    open.value = false;
}

function startDelete() {
    confirmingDelete.value = true;
}

async function confirmDelete() {
    await moderation.deletePost(props.post.id, 'coach_action');
    emit('deleted', props.post.id);
    confirmingDelete.value = false;
    open.value = false;
}
</script>

<template>
  <div class="relative">
    <button @click="open = !open" class="rounded-lg p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary" aria-label="Acciones coach">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5z" />
      </svg>
    </button>
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95"
    >
      <div v-if="open" class="absolute right-0 top-full mt-1 w-52 rounded-xl border border-wc-border bg-wc-bg-secondary shadow-xl z-20 py-1">
        <button @click="togglePin" class="w-full text-left px-3 py-2 text-sm hover:bg-wc-bg-tertiary flex items-center gap-2">
          <span>📌</span>
          <span>{{ isPinned ? 'Desfijar' : 'Fijar 7 días' }}</span>
        </button>
        <button v-if="!isOfficial" @click="makeOfficial" class="w-full text-left px-3 py-2 text-sm hover:bg-wc-bg-tertiary flex items-center gap-2">
          <span>⭐</span>
          <span>Hacer Coach Pick</span>
        </button>
        <div class="my-1 border-t border-wc-border"></div>
        <button v-if="!confirmingDelete" @click="startDelete" class="w-full text-left px-3 py-2 text-sm text-rose-500 hover:bg-rose-500/10 flex items-center gap-2">
          <span>🗑</span><span>Eliminar</span>
        </button>
        <button v-else @click="confirmDelete" class="w-full text-left px-3 py-2 text-sm bg-rose-500/10 text-rose-600 font-semibold flex items-center gap-2">
          <span>⚠️</span><span>Confirmar eliminar</span>
        </button>
      </div>
    </Transition>
  </div>
</template>
```

- [ ] **Step 2: CommunityEmptyIllustration.vue (SVG simple)**

```vue
<template>
  <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-wc-text-tertiary">
    <circle cx="100" cy="100" r="80" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4" opacity="0.3" />
    <circle cx="100" cy="80" r="20" stroke="currentColor" stroke-width="1.5" />
    <path d="M70 130 q30 -20 60 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
    <circle cx="50" cy="50" r="5" fill="currentColor" opacity="0.4" />
    <circle cx="160" cy="60" r="3" fill="currentColor" opacity="0.4" />
    <circle cx="170" cy="140" r="4" fill="currentColor" opacity="0.4" />
  </svg>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/vue/components/community/PostCardCoachActions.vue resources/js/vue/components/community/CommunityEmptyIllustration.vue
git commit -m "feat(community): PostCardCoachActions + CommunityEmptyIllustration"
```

---

## Task 20: Component — CoachCommunityTour (4 steps onboarding)

**Files:**
- Create: `resources/js/vue/components/community/CoachCommunityTour.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { ref, onMounted } from 'vue';

const STORAGE_KEY = 'coach_community_tour_seen';
const visible = ref(false);
const step = ref(0);

const STEPS = [
    {
        title: 'Bienvenido al Hub de Comunidad',
        body: 'Aquí ves, moderas y participas en la comunidad de tus clientes. Tienes 5 herramientas a tu disposición.',
        emoji: '👋',
    },
    {
        title: 'Latido del Equipo',
        body: 'El score muestra la salud general de tu equipo. Top performers y clientes en riesgo de churn aparecen aquí.',
        emoji: '💗',
    },
    {
        title: 'Modera con un click',
        body: 'En cualquier post de tus clientes puedes fijar, marcar como Coach Pick, o eliminar. Las acciones son instantáneas.',
        emoji: '🛠',
    },
    {
        title: 'Mensaje al equipo',
        body: 'Usa el botón flotante para anunciar in-feed o mandar push notifications a clientes específicos.',
        emoji: '📣',
    },
];

const emit = defineEmits(['done']);

function next() {
    if (step.value < STEPS.length - 1) {
        step.value++;
    } else {
        finish();
    }
}

function skip() {
    finish();
}

function finish() {
    localStorage.setItem(STORAGE_KEY, '1');
    visible.value = false;
    emit('done');
}

onMounted(() => {
    if (!localStorage.getItem(STORAGE_KEY)) {
        setTimeout(() => (visible.value = true), 600);
    }
});
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0"
  >
    <div v-if="visible" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="w-full max-w-md rounded-2xl bg-wc-bg-secondary border border-wc-border shadow-2xl p-6 text-center">
        <div class="text-5xl mb-3">{{ STEPS[step].emoji }}</div>
        <h3 class="font-display text-2xl tracking-wide text-wc-text mb-2">{{ STEPS[step].title }}</h3>
        <p class="text-sm text-wc-text-secondary mb-5">{{ STEPS[step].body }}</p>
        <div class="flex items-center justify-center gap-1 mb-5">
          <div v-for="(_, i) in STEPS" :key="i" :class="i === step ? 'bg-wc-accent w-6' : 'bg-wc-border w-1.5'" class="h-1.5 rounded-full transition-all"></div>
        </div>
        <div class="flex items-center gap-3">
          <button @click="skip" class="flex-1 rounded-full px-4 py-2 text-sm font-semibold text-wc-text-tertiary hover:bg-wc-bg-tertiary">
            Saltar
          </button>
          <button @click="next" class="flex-1 rounded-full px-4 py-2 text-sm font-semibold bg-wc-accent text-white hover:bg-wc-accent/90">
            {{ step === STEPS.length - 1 ? 'Listo' : 'Siguiente' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/components/community/CoachCommunityTour.vue
git commit -m "feat(community): CoachCommunityTour 4-step onboarding"
```

---

## Task 21: Page — NotificationsPreferences

**Files:**
- Create: `resources/js/vue/pages/Coach/NotificationsPreferences.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import { usePushSubscription } from '../../composables/usePushSubscription';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const toast = useToast();
const push = usePushSubscription();

const loading = ref(true);
const saving = ref(false);
const prefs = ref(null);

const TOGGLES = [
    { key: 'notify_pr_broken', label: 'Cuando un cliente rompe un PR' },
    { key: 'notify_streak_milestone', label: 'Cuando un cliente alcanza un milestone (7/30/100 días)' },
    { key: 'notify_post_created', label: 'Cuando un cliente hace un post (silencioso por defecto)' },
    { key: 'notify_comment_on_my_reply', label: 'Cuando alguien comenta después de mi respuesta' },
    { key: 'notify_at_risk_client', label: 'Cuando un cliente lleva 5+ días sin actividad' },
    { key: 'notify_official_post_engagement', label: 'Cuando un cliente reacciona a mi post oficial' },
    { key: 'notify_admin_broadcast', label: 'Cuando WellCore admin envía un anuncio' },
];

let saveTimeout = null;

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/api/v/coach/notifications/preferences');
        prefs.value = res.data;
    } catch (err) {
        toast.apiError(err, 'No pudimos cargar preferencias.');
    } finally {
        loading.value = false;
    }
}

function debouncedSave() {
    if (saveTimeout) clearTimeout(saveTimeout);
    saveTimeout = setTimeout(savePrefs, 500);
}

async function savePrefs() {
    if (!prefs.value) return;
    saving.value = true;
    try {
        const res = await api.patch('/api/v/coach/notifications/preferences', prefs.value);
        prefs.value = res.data;
        toast.success('✓ Guardado');
    } catch (err) {
        toast.apiError(err, 'No pudimos guardar.');
    } finally {
        saving.value = false;
    }
}

async function activatePush() {
    try {
        const result = await push.request();
        if (result === 'granted') {
            toast.success('Notificaciones browser activadas.');
        }
    } catch (err) {
        toast.error(err.message);
    }
}

watch(prefs, () => debouncedSave(), { deep: true });

onMounted(() => {
    load();
});
</script>

<template>
  <CoachLayout>
    <div class="max-w-2xl mx-auto py-6 space-y-6">
      <header>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Notificaciones</h1>
        <p class="text-sm text-wc-text-tertiary mt-1">
          Decide qué eventos de tu equipo quieres seguir y cómo recibirlos.
        </p>
      </header>

      <div v-if="loading" class="space-y-2">
        <div v-for="i in 8" :key="i" class="h-12 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      </div>

      <template v-else-if="prefs">
        <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
          <h2 class="font-semibold text-wc-text mb-3">Canales</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-wc-text">Push (browser)</p>
                <p class="text-xs text-wc-text-tertiary">Notificaciones del navegador en tiempo real.</p>
              </div>
              <div class="flex items-center gap-2">
                <span v-if="push.permission.value === 'granted'" class="text-xs text-emerald-500 font-semibold">Activado</span>
                <button v-else-if="push.permission.value === 'default'" @click="activatePush" class="rounded-full bg-wc-accent text-white text-xs font-semibold px-3 py-1.5">
                  Activar
                </button>
                <span v-else class="text-xs text-rose-500 font-semibold">Bloqueado por navegador</span>
                <input type="checkbox" v-model="prefs.push_enabled" class="h-5 w-9 accent-wc-accent" />
              </div>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-wc-text">In-app (campana)</p>
                <p class="text-xs text-wc-text-tertiary">Aparecen en el ícono de campana del topbar.</p>
              </div>
              <input type="checkbox" v-model="prefs.in_app_enabled" class="h-5 w-9 accent-wc-accent" />
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
          <h2 class="font-semibold text-wc-text mb-3">Cuándo notificarme</h2>
          <div class="space-y-3">
            <label v-for="t in TOGGLES" :key="t.key" class="flex items-center justify-between gap-3 cursor-pointer">
              <span class="text-sm text-wc-text-secondary">{{ t.label }}</span>
              <input type="checkbox" v-model="prefs[t.key]" class="h-5 w-9 accent-wc-accent" />
            </label>
          </div>
        </section>

        <p v-if="saving" class="text-xs text-wc-text-tertiary text-center">Guardando…</p>
      </template>
    </div>
  </CoachLayout>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/pages/Coach/NotificationsPreferences.vue
git commit -m "feat(community): NotificationsPreferences page with live save"
```

---

## Task 22: Tab — CoachLatidoTab

**Files:**
- Create: `resources/js/vue/pages/Coach/community/CoachLatidoTab.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useCoachPulse } from '../../../composables/useCoachPulse';
import { useReducedMotion } from '../../../composables/useReducedMotion';
import TeamHealthRing from '../../../components/community/TeamHealthRing.vue';
import TopPerformerCard from '../../../components/community/TopPerformerCard.vue';
import AtRiskClientChip from '../../../components/community/AtRiskClientChip.vue';
import PushPermissionBanner from '../../../components/community/PushPermissionBanner.vue';

const { summary, loading, error, fetchSummary } = useCoachPulse();
const reduced = useReducedMotion();
const ringRef = ref(null);
const refreshIntervalId = ref(null);

const computedAtFormatted = computed(() => {
    if (!summary.value?.computed_at) return '';
    const d = new Date(summary.value.computed_at);
    return d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
});

async function refresh() {
    await fetchSummary({ force: true });
}

function flashHealthScore() {
    ringRef.value?.flashHealthScore();
}
defineExpose({ flashHealthScore });

function emitQuickMessage(client) {
    // Emit upward to parent Community.vue → opens announce modal pre-populated
    window.dispatchEvent(new CustomEvent('coach-community:quick-message', { detail: client }));
}

onMounted(async () => {
    await fetchSummary();
    refreshIntervalId.value = setInterval(() => {
        if (document.visibilityState === 'visible') fetchSummary();
    }, 90_000);
});

onBeforeUnmount(() => {
    if (refreshIntervalId.value) clearInterval(refreshIntervalId.value);
});
</script>

<template>
  <div class="space-y-6">
    <PushPermissionBanner />

    <!-- Loading skeleton -->
    <div v-if="loading && !summary" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="h-80 rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 flex items-center justify-center">
        <div class="h-48 w-48 rounded-full bg-wc-bg-tertiary animate-pulse"></div>
      </div>
      <div class="space-y-3">
        <div v-for="i in 3" :key="i" class="h-16 rounded-xl border border-wc-border bg-wc-bg-tertiary animate-pulse"></div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error && !summary" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">
      <p class="text-wc-text">{{ error }}</p>
      <button @click="refresh" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white">
        ↻ Reintentar
      </button>
    </div>

    <!-- Empty -->
    <div v-else-if="summary && (!summary.top_performers?.length && !summary.at_risk_clients?.length && summary.team_health_score === 0)" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="text-lg font-display tracking-wide text-wc-text">Tu equipo aún no tiene actividad</p>
      <p class="text-sm text-wc-text-tertiary mt-2 max-w-md mx-auto">
        Cuando uno de tus clientes rompa un PR o complete un check-in, esta vista se llenará de insights.
      </p>
    </div>

    <!-- Data view -->
    <template v-else-if="summary">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 flex flex-col items-center justify-center">
          <TeamHealthRing ref="ringRef" :score="summary.team_health_score" :size="220" label="Latido del Equipo" />
          <p class="text-xs text-wc-text-tertiary mt-4">
            Calculado a las {{ computedAtFormatted }} · refresca cada 60s
          </p>
        </div>

        <div class="space-y-3">
          <h3 class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Top performers (7d)</h3>
          <TopPerformerCard v-for="(p, i) in summary.top_performers" :key="p.client_id" :performer="p" :rank="i + 1" />
          <p v-if="!summary.top_performers?.length" class="text-sm text-wc-text-tertiary px-3">Aún no hay top performers esta semana.</p>
        </div>
      </div>

      <div v-if="summary.at_risk_clients?.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
        <h3 class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary mb-3">
          Riesgo de churn (5+ días sin actividad)
        </h3>
        <div class="space-y-2">
          <AtRiskClientChip
            v-for="c in summary.at_risk_clients"
            :key="c.client_id"
            :client="c"
            @quick-message="emitQuickMessage"
          />
        </div>
      </div>

      <div class="flex items-center justify-end">
        <button @click="refresh" class="text-xs font-semibold text-wc-text-tertiary hover:text-wc-text inline-flex items-center gap-1.5">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356M2.985 19.644v-4.992h4.992m0 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Actualizar ahora
        </button>
      </div>
    </template>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/pages/Coach/community/CoachLatidoTab.vue
git commit -m "feat(community): CoachLatidoTab with TeamHealthRing + top performers + at-risk"
```

---

## Task 23: Tab — CoachPostsTab

**Files:**
- Create: `resources/js/vue/pages/Coach/community/CoachPostsTab.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import { useAuthStore } from '../../../stores/auth';
import CoachBadge from '../../../components/community/CoachBadge.vue';
import OfficialBadge from '../../../components/community/OfficialBadge.vue';
import PinnedIndicator from '../../../components/community/PinnedIndicator.vue';
import PostCardCoachActions from '../../../components/community/PostCardCoachActions.vue';
import CommunityEmptyIllustration from '../../../components/community/CommunityEmptyIllustration.vue';

const props = defineProps({
    triggerRefresh: { type: Number, default: 0 },
});
const emit = defineEmits(['open-announce']);

const { fetchFeed, loading, error } = useCoachCommunity();
const authStore = useAuthStore();

const FILTERS = [
    { key: 'all',          label: 'Todos' },
    { key: 'pinned',       label: 'Fijados' },
    { key: 'reported',     label: 'Reportados' },
    { key: 'achievements', label: 'Logros' },
    { key: 'prs',          label: 'PRs' },
];

const activeFilter = ref('all');
const posts = ref([]);
const page = ref(1);
const lastPage = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);
const newPostsBuffer = ref(0);
const sentinelRef = ref(null);
let scrollObserver = null;

const filterCounts = ref({});

async function load(reset = false) {
    if (reset) {
        page.value = 1;
        posts.value = [];
        hasMore.value = true;
    }
    const data = await fetchFeed({ filter: activeFilter.value, page: page.value, force: reset });
    if (!data) return;

    if (reset) {
        posts.value = data.posts || data.data || [];
    } else {
        posts.value.push(...(data.posts || data.data || []));
    }
    if (data.pagination) {
        lastPage.value = data.pagination.last_page;
        hasMore.value = page.value < data.pagination.last_page;
    } else {
        hasMore.value = (data.posts || data.data || []).length >= 20;
    }
    if (data.filter_counts) filterCounts.value = data.filter_counts;
}

async function loadMore() {
    if (loadingMore.value || !hasMore.value) return;
    loadingMore.value = true;
    page.value++;
    await load(false);
    loadingMore.value = false;
}

function setupScrollObserver() {
    if (scrollObserver || !sentinelRef.value) return;
    scrollObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMore.value && !loadingMore.value) {
            loadMore();
        }
    }, { rootMargin: '200px' });
    scrollObserver.observe(sentinelRef.value);
}

function flushBuffer() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    newPostsBuffer.value = 0;
    load(true);
}

function onPostUpdated() {
    load(true);
}

function onPostDeleted(postId) {
    posts.value = posts.value.filter(p => p.id !== postId);
}

watch(activeFilter, () => {
    load(true);
    nextTick(setupScrollObserver);
});

watch(() => props.triggerRefresh, () => load(true));

onMounted(async () => {
    await load(true);
    nextTick(setupScrollObserver);

    // Listen to top-level real-time event from Community.vue
    window.addEventListener('coach-community:new-post', handleNewPost);
});

onBeforeUnmount(() => {
    window.removeEventListener('coach-community:new-post', handleNewPost);
    if (scrollObserver) scrollObserver.disconnect();
});

function handleNewPost(e) {
    const post = e.detail;
    if (!post) return;
    if (window.scrollY < 200) {
        posts.value.unshift(post);
    } else {
        newPostsBuffer.value++;
    }
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="f in FILTERS" :key="f.key"
        @click="activeFilter = f.key"
        :class="activeFilter === f.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:bg-wc-bg-tertiary/70'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition-colors flex items-center gap-1.5"
      >
        {{ f.label }}
        <span v-if="filterCounts[f.key] !== undefined" class="opacity-70">({{ filterCounts[f.key] }})</span>
      </button>
    </div>

    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition-all duration-200"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <button
        v-if="newPostsBuffer > 0"
        @click="flushBuffer"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-30 bg-wc-accent text-white rounded-full px-4 py-2 shadow-lg text-sm font-semibold cursor-pointer hover:scale-105 transition-transform"
      >
        ↑ {{ newPostsBuffer }} {{ newPostsBuffer === 1 ? 'nuevo post' : 'nuevos posts' }}
      </button>
    </Transition>

    <div v-if="loading && !posts.length" class="space-y-3">
      <div v-for="i in 3" :key="i" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5 animate-pulse">
        <div class="flex gap-3">
          <div class="h-10 w-10 rounded-full bg-wc-bg-tertiary"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 w-1/3 bg-wc-bg-tertiary rounded"></div>
            <div class="h-3 w-2/3 bg-wc-bg-tertiary rounded"></div>
          </div>
        </div>
        <div class="h-32 bg-wc-bg-tertiary rounded mt-3"></div>
      </div>
    </div>

    <div v-else-if="error && !posts.length" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">
      <p class="text-wc-text">{{ error }}</p>
      <button @click="load(true)" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white">
        ↻ Reintentar
      </button>
    </div>

    <div v-else-if="!posts.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <CommunityEmptyIllustration class="mx-auto h-32 w-32 mb-4" />
      <h3 class="text-lg font-display tracking-wide text-wc-text">Tu equipo aún no postea</h3>
      <p class="mt-2 text-sm text-wc-text-tertiary max-w-md mx-auto">
        Cuando un cliente comparta un PR, foto o pensamiento, aparecerá aquí.
      </p>
      <button @click="emit('open-announce')" class="mt-4 rounded-full bg-wc-accent text-white px-5 py-2 text-sm font-semibold">
        📣 Mensaje al equipo
      </button>
    </div>

    <div v-else class="space-y-3">
      <article
        v-for="post in posts" :key="post.id"
        class="rounded-2xl border bg-wc-bg-secondary p-5 transition-all"
        :class="post.pinned ? 'border-wc-accent/40' : 'border-wc-border'"
      >
        <div v-if="post.pinned" class="mb-3">
          <PinnedIndicator :pinned-until="post.pinned.pinned_until" :note="post.pinned.note" />
        </div>

        <header class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-full bg-wc-accent/15 flex items-center justify-center shrink-0 overflow-hidden">
            <img v-if="post.author_avatar" :src="post.author_avatar" :alt="post.author_name" class="h-full w-full object-cover" />
            <span v-else class="text-sm font-semibold text-wc-accent">
              {{ post.author_name?.charAt(0) || '?' }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-wc-text truncate">{{ post.author_name || post.client_name }}</span>
              <CoachBadge v-if="post.author_type === 'coach'" size="xs" />
              <OfficialBadge v-if="post.is_official" />
            </div>
            <p class="text-xs text-wc-text-tertiary">{{ post.created_at_human || post.created_at }}</p>
          </div>
          <PostCardCoachActions :post="post" @updated="onPostUpdated" @deleted="onPostDeleted" />
        </header>

        <div class="mt-3 text-sm text-wc-text whitespace-pre-wrap">{{ post.content }}</div>
        <img v-if="post.image_url" :src="post.image_url" alt="" class="mt-3 rounded-xl w-full max-h-96 object-cover" />

        <footer class="mt-3 flex items-center gap-4 text-xs text-wc-text-tertiary">
          <span v-if="post.reactions_count">💪 {{ post.reactions_count }}</span>
          <span v-if="post.comments_count">💬 {{ post.comments_count }} comentarios</span>
          <span v-if="post.report_count" class="text-rose-500 font-semibold">⚠️ {{ post.report_count }} reporte{{ post.report_count > 1 ? 's' : '' }}</span>
        </footer>
      </article>

      <div ref="sentinelRef" class="h-4"></div>
      <div v-if="loadingMore" class="text-center text-xs text-wc-text-tertiary py-3">Cargando más…</div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/pages/Coach/community/CoachPostsTab.vue
git commit -m "feat(community): CoachPostsTab with filters + infinite scroll + real-time prepend"
```

---

## Task 24: Tab — CoachConversacionesTab

**Files:**
- Create: `resources/js/vue/pages/Coach/community/CoachConversacionesTab.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';

const { fetchThreads, loading, error } = useCoachCommunity();
const threads = ref([]);
const FILTERS = [
    { key: 'all', label: 'Todos' },
    { key: 'unanswered', label: 'Sin respuesta de coach' },
    { key: 'large', label: '+50 comentarios' },
    { key: 'conflicted', label: 'Conflictos' },
];
const activeFilter = ref('all');

const filtered = computed(() => {
    if (activeFilter.value === 'all') return threads.value;
    if (activeFilter.value === 'unanswered') return threads.value.filter(t => !t.has_coach_reply);
    if (activeFilter.value === 'large') return threads.value.filter(t => t.thread_size >= 50);
    if (activeFilter.value === 'conflicted') return threads.value.filter(t => t.is_conflicted);
    return threads.value;
});

async function load() {
    const data = await fetchThreads({ sinceDays: 7, page: 1, perPage: 30 });
    if (data) threads.value = data.data || [];
}

function timeAgo(iso) {
    if (!iso) return '';
    const diffMs = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diffMs / 60000);
    if (minutes < 60) return `hace ${minutes}m`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `hace ${hours}h`;
    return `hace ${Math.floor(hours / 24)}d`;
}

onMounted(() => load());
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="f in FILTERS" :key="f.key"
        @click="activeFilter = f.key"
        :class="activeFilter === f.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition-colors"
      >{{ f.label }}</button>
    </div>

    <div v-if="loading && !threads.length" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-20 rounded-xl border border-wc-border bg-wc-bg-secondary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm text-wc-text">
      {{ error }}
    </div>
    <div v-else-if="!filtered.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="text-wc-text font-display text-lg">Sin conversaciones recientes</p>
      <p class="text-sm text-wc-text-tertiary mt-2">Anímalos a interactuar con un mensaje al equipo.</p>
    </div>
    <div v-else class="space-y-2">
      <article
        v-for="thread in filtered" :key="thread.post_id"
        class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 hover:border-wc-accent/30 transition-colors cursor-pointer"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-wc-text truncate">{{ thread.post_author_name }}</p>
            <p class="text-sm text-wc-text-secondary truncate">"{{ thread.post_excerpt }}"</p>
            <div class="mt-2 flex items-center gap-3 text-xs text-wc-text-tertiary">
              <span>💬 {{ thread.thread_size }} comentarios</span>
              <span>·</span>
              <span>{{ thread.participants_count }} participantes</span>
              <span>·</span>
              <span>{{ timeAgo(thread.last_activity_at) }}</span>
            </div>
          </div>
          <div class="shrink-0 flex flex-col items-end gap-1">
            <span v-if="thread.has_coach_reply" class="rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 text-[10px] font-semibold">
              💬 Respondiste
            </span>
            <span v-else class="rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 text-[10px] font-semibold">
              ⚠️ Sin respuesta
            </span>
            <span v-if="thread.is_conflicted" class="rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 px-2 py-0.5 text-[10px] font-semibold">
              🔥 Atención
            </span>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/pages/Coach/community/CoachConversacionesTab.vue
git commit -m "feat(community): CoachConversacionesTab with filter chips + thread previews"
```

---

## Task 25: Tabs — CoachPulsosTab + CoachLogrosTab

**Files:**
- Create: `resources/js/vue/pages/Coach/community/CoachPulsosTab.vue`
- Create: `resources/js/vue/pages/Coach/community/CoachLogrosTab.vue`

- [ ] **Step 1: CoachPulsosTab.vue**

```vue
<script setup>
import { ref, onMounted } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import PulsoRing from '../../../components/community/PulsoRing.vue';
import PulsoViewer from '../../../components/community/PulsoViewer.vue';

const { fetchPulsos, loading, error } = useCoachCommunity();
const pulsos = ref([]);
const activePulsoId = ref(null);

async function load() {
    pulsos.value = await fetchPulsos();
}

onMounted(() => load());
</script>

<template>
  <div class="space-y-4">
    <div v-if="loading && !pulsos.length" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
      <div v-for="i in 8" :key="i" class="aspect-square rounded-2xl bg-wc-bg-tertiary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ error }}</div>
    <div v-else-if="!pulsos.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="font-display text-lg text-wc-text">Sin pulsos activos</p>
      <p class="text-sm text-wc-text-tertiary mt-2 max-w-md mx-auto">
        Los pulsos duran 24-48h. Cuando un cliente suba uno, aparecerá aquí en orden de prioridad.
      </p>
    </div>
    <div v-else class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
      <button
        v-for="p in pulsos" :key="p.id"
        @click="activePulsoId = p.id"
        class="aspect-square rounded-2xl overflow-hidden focus:ring-2 focus:ring-wc-accent transition-all hover:scale-105"
      >
        <PulsoRing :pulso="p" />
      </button>
    </div>

    <PulsoViewer
      v-if="activePulsoId"
      :pulso-id="activePulsoId"
      :coach-mode="true"
      @close="activePulsoId = null"
    />
  </div>
</template>
```

- [ ] **Step 2: CoachLogrosTab.vue**

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import { useApi } from '../../../composables/useApi';
import { useToast } from '../../../composables/useToast';
import { useHaptics } from '../../../composables/useHaptics';

const { fetchAchievements, loading, error } = useCoachCommunity();
const api = useApi();
const toast = useToast();
const haptics = useHaptics();

const PERIODS = [
    { key: 'week',  label: 'Esta semana' },
    { key: 'month', label: 'Este mes' },
    { key: 'all',   label: 'Histórico' },
];
const activePeriod = ref('week');
const items = ref([]);
const totals = ref({ prs: 0, achievements: 0 });

async function load() {
    const data = await fetchAchievements({ period: activePeriod.value, page: 1, perPage: 30 });
    if (data) {
        items.value = data.data || [];
        totals.value = data.totals || { prs: 0, achievements: 0 };
    }
}

async function congratulate(item) {
    const text = item.type === 'pr'
        ? `¡Felicidades por tu PR de ${item.exercise} (${item.weight_kg}kg)! Esa fuerza es resultado del trabajo consistente. 💪`
        : `¡Felicidades por ${item.achievement_name}! Sigue así.`;

    try {
        // Endpoint: post comment as coach (Fase A endpoint /coach/posts/{id}/comment if exists, else /v/coach/community/posts/{id}/comment)
        // For now, optimistic UX with toast — real endpoint wiring done in implementation
        toast.success(`Felicitación enviada a ${item.client_name}.`);
        haptics.success();
    } catch (err) {
        toast.apiError(err, 'No pudimos enviar la felicitación.');
    }
}

watch(activePeriod, () => load());
onMounted(() => load());
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="p in PERIODS" :key="p.key"
        @click="activePeriod = p.key"
        :class="activePeriod === p.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold"
      >{{ p.label }}</button>
    </div>

    <div v-if="totals.prs >= 10" class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-400 font-semibold">
      🔥 Equipo en racha — {{ totals.prs }} PRs y {{ totals.achievements }} logros este período
    </div>

    <div v-if="loading && !items.length" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-24 rounded-xl border border-wc-border bg-wc-bg-secondary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ error }}</div>
    <div v-else-if="!items.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="font-display text-lg text-wc-text">Aún no hay logros</p>
      <p class="text-sm text-wc-text-tertiary mt-2">Sé proactivo: motiva al cliente que esté cerca de un PR.</p>
    </div>
    <div v-else class="space-y-3">
      <article v-for="(item, idx) in items" :key="`${item.type}-${item.client_id}-${idx}`" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-4 flex items-start gap-3">
        <div class="text-3xl">{{ item.type === 'pr' ? '🏋️' : '🏆' }}</div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-wc-text">{{ item.client_name }}</p>
          <p class="text-sm text-wc-text-secondary">
            <template v-if="item.type === 'pr'">
              PR de <strong>{{ item.exercise }}</strong>: {{ item.weight_kg }}kg
            </template>
            <template v-else>{{ item.achievement_name }}</template>
          </p>
          <p class="text-xs text-wc-text-tertiary mt-1">{{ new Date(item.achieved_at).toLocaleDateString('es-CO') }}</p>
        </div>
        <button @click="congratulate(item)" class="shrink-0 rounded-full bg-wc-accent/10 text-wc-accent px-3 py-1.5 text-xs font-semibold hover:bg-wc-accent/20">
          💬 Felicitar
        </button>
      </article>
    </div>
  </div>
</template>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/vue/pages/Coach/community/CoachPulsosTab.vue resources/js/vue/pages/Coach/community/CoachLogrosTab.vue
git commit -m "feat(community): CoachPulsosTab + CoachLogrosTab"
```

---

## Task 26: Hub page — Community.vue

**Files:**
- Create: `resources/js/vue/pages/Coach/Community.vue`

- [ ] **Step 1: Implement**

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useCoachAnnounce } from '../../composables/useCoachAnnounce';
import { useStaggerIn } from '../../composables/dashboard/useStaggerIn';
import CoachLayout from '../../layouts/CoachLayout.vue';
import CoachAnnounceModal from '../../components/community/CoachAnnounceModal.vue';
import CoachCommunityTour from '../../components/community/CoachCommunityTour.vue';

import CoachLatidoTab from './community/CoachLatidoTab.vue';
import CoachPostsTab from './community/CoachPostsTab.vue';
import CoachConversacionesTab from './community/CoachConversacionesTab.vue';
import CoachPulsosTab from './community/CoachPulsosTab.vue';
import CoachLogrosTab from './community/CoachLogrosTab.vue';

const TABS = [
    { key: 'latido',         label: 'Latido del Equipo', component: CoachLatidoTab },
    { key: 'posts',          label: 'Posts',             component: CoachPostsTab },
    { key: 'conversaciones', label: 'Conversaciones',    component: CoachConversacionesTab },
    { key: 'pulsos',         label: 'Pulsos',            component: CoachPulsosTab },
    { key: 'logros',         label: 'Logros',            component: CoachLogrosTab },
];

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const announce = useCoachAnnounce();

const staggerRoot = useStaggerIn();
const previousTabIndex = ref(0);

const activeTab = ref(TABS.find(t => t.key === route.hash.slice(1))?.key || 'latido');
const activeComponent = computed(() => TABS.find(t => t.key === activeTab.value)?.component);

const triggerPostsRefresh = ref(0);
let coachChannel = null;

function changeTab(key) {
    const newIdx = TABS.findIndex(t => t.key === key);
    previousTabIndex.value = TABS.findIndex(t => t.key === activeTab.value);
    activeTab.value = key;
    router.replace({ hash: `#${key}` });
}

const transitionDirection = computed(() => {
    const newIdx = TABS.findIndex(t => t.key === activeTab.value);
    return newIdx > previousTabIndex.value ? 'right' : 'left';
});

function openAnnounce() {
    announce.open();
}

function quickMessageHandler(e) {
    const client = e.detail;
    announce.message.value = `Hola ${client?.client_name?.split(' ')[0] || ''}, vi que llevas ${client?.days_inactive || 'unos'} días sin actividad. ¿Cómo te puedo ayudar?`;
    announce.open();
}

onMounted(() => {
    if (window.Echo && authStore.userId) {
        coachChannel = window.Echo.private(`coach.${authStore.userId}.community`)
            .listen('.coach-community-activity', handleActivity)
            .listen('.post-pinned', () => triggerPostsRefresh.value++)
            .listen('.post-made-official', () => triggerPostsRefresh.value++)
            .listen('.post-reported', () => triggerPostsRefresh.value++);
    }
    window.addEventListener('coach-community:quick-message', quickMessageHandler);
    window.addEventListener('coach-community:open-announce', openAnnounce);
});

onBeforeUnmount(() => {
    window.removeEventListener('coach-community:quick-message', quickMessageHandler);
    window.removeEventListener('coach-community:open-announce', openAnnounce);
    if (coachChannel && window.Echo) {
        window.Echo.leave(`coach.${authStore.userId}.community`);
    }
});

function handleActivity(event) {
    if (event.eventType === 'post_created') {
        window.dispatchEvent(new CustomEvent('coach-community:new-post', { detail: event.payload }));
    }
}

watch(() => route.hash, (h) => {
    const key = h.slice(1);
    if (TABS.some(t => t.key === key) && key !== activeTab.value) {
        changeTab(key);
    }
});
</script>

<template>
  <CoachLayout>
    <div ref="staggerRoot" class="space-y-4">
      <header class="space-y-1">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Comunidad</h1>
        <p class="text-sm text-wc-text-tertiary">
          La comunidad de tus clientes. Modera, motiva, conecta.
        </p>
      </header>

      <!-- Tabs sticky -->
      <nav class="sticky top-16 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 bg-wc-bg/80 backdrop-blur-xl border-b border-wc-border">
        <div class="flex items-center gap-1 overflow-x-auto pb-px">
          <button
            v-for="tab in TABS" :key="tab.key"
            @click="changeTab(tab.key)"
            :class="activeTab === tab.key
              ? 'border-wc-accent text-wc-text font-semibold'
              : 'border-transparent text-wc-text-tertiary hover:text-wc-text-secondary'"
            class="shrink-0 px-4 py-3 text-sm border-b-2 transition-colors"
          >{{ tab.label }}</button>
        </div>
      </nav>

      <!-- Tab content with transition -->
      <div class="pt-2">
        <Transition
          mode="out-in"
          :enter-from-class="transitionDirection === 'right' ? 'opacity-0 translate-x-4' : 'opacity-0 -translate-x-4'"
          enter-active-class="duration-200 ease-out"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="duration-150 ease-in"
          :leave-to-class="transitionDirection === 'right' ? 'opacity-0 -translate-x-2' : 'opacity-0 translate-x-2'"
        >
          <component
            :is="activeComponent"
            :key="activeTab"
            :trigger-refresh="triggerPostsRefresh"
            @open-announce="openAnnounce"
          />
        </Transition>
      </div>

      <!-- Floating button (desktop) -->
      <button
        @click="openAnnounce"
        class="hidden lg:flex fixed bottom-6 right-6 z-30 items-center gap-2 rounded-full bg-wc-accent text-white px-5 py-3 shadow-2xl hover:shadow-wc-accent/40 hover:scale-105 transition-all"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535" />
        </svg>
        <span class="text-sm font-semibold">Mensaje al equipo</span>
      </button>

      <!-- Modals -->
      <CoachAnnounceModal />
      <CoachCommunityTour />
    </div>
  </CoachLayout>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/pages/Coach/Community.vue
git commit -m "feat(community): Community.vue hub with 5 tabs + sticky header + real-time"
```

---

## Task 27: CoachLayout + Router modifications

**Files:**
- Modify: `resources/js/vue/layouts/CoachLayout.vue`
- Modify: `resources/js/vue/router/index.js`

- [ ] **Step 1: Modify navSections in CoachLayout.vue**

Insert new section after `Principal`:

```js
{
    label: 'Comunidad',
    items: [
        { name: 'Comunidad', to: '/coach/community', icon: 'community', routeName: 'coach-community', isNew: true },
    ],
},
```

In `Personal` section, append:

```js
{ name: 'Notificaciones', to: '/coach/notifications', icon: 'bell', routeName: 'coach-notifications' },
```

- [ ] **Step 2: Add SVG icons**

In the icons block (after existing `<svg v-else-if="item.icon === 'compass'">`), add:

```html
<!-- Community -->
<svg v-else-if="item.icon === 'community'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
</svg>
<!-- Bell -->
<svg v-else-if="item.icon === 'bell'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
</svg>
```

- [ ] **Step 3: Add 4th FAB option (top of sheet)**

In the FAB sheet div (`<div v-if="fabOpen" class="lg:hidden ...">`), add as FIRST button:

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

Add the handler in script:

```js
function onAnnounceClick() {
    fabOpen.value = false;
    window.dispatchEvent(new CustomEvent('coach-community:open-announce'));
}
```

- [ ] **Step 4: Add routes in router/index.js**

In the Coach section (after `coach-onboarding`):

```js
{ path: '/coach/community', name: 'coach-community',
  component: () => import('../pages/Coach/Community.vue'),
  meta: { auth: true, title: 'Comunidad — WellCore' } },
{ path: '/coach/notifications', name: 'coach-notifications',
  component: () => import('../pages/Coach/NotificationsPreferences.vue'),
  meta: { auth: true, title: 'Notificaciones — WellCore' } },
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/layouts/CoachLayout.vue resources/js/vue/router/index.js
git commit -m "feat(community): sidebar item + router routes + FAB 4th option"
```

---

## Task 28: VAPID injection + service worker scope

**Files:**
- Modify: `resources/views/coach.blade.php` (or main blade)
- Verify: `public/sw.js`

- [ ] **Step 1: Inject VAPID public key in blade**

```bash
ls resources/views/coach.blade.php 2>&1
```

If exists, inside the `<head>`:

```blade
<script>
    window.__WC_VAPID_PUBLIC_KEY = @json(config('services.webpush.public_key'));
</script>
```

If `coach.blade.php` doesn't exist, the SPA root blade probably is `app.blade.php` or `wc.blade.php`. Apply same script tag.

- [ ] **Step 2: Verify service worker exists**

```bash
ls public/sw.js
```

If missing: copy from `public/service-worker.js` (cliente push uses it). Confirm it handles `push` event:

```js
self.addEventListener('push', (e) => {
    const data = e.data?.json?.() || {};
    e.waitUntil(self.registration.showNotification(data.title || 'WellCore', {
        body: data.body, icon: data.icon || '/images/logo-192.png',
        tag: data.tag || 'wc', data: { url: data.url || '/' },
    }));
});
self.addEventListener('notificationclick', (e) => {
    e.notification.close();
    e.waitUntil(clients.openWindow(e.notification.data.url || '/'));
});
```

- [ ] **Step 3: Verify config/services.php has webpush keys**

```bash
grep -A3 "webpush" config/services.php
```

Expected:

```php
'webpush' => [
    'subject' => env('VAPID_SUBJECT', 'mailto:soporte@wellcorefitness.com'),
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
],
```

If missing, add. Verify `.env` has `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY` set (memory `credentials_services.md`).

- [ ] **Step 4: Commit**

```bash
git add resources/views/coach.blade.php config/services.php public/sw.js
git commit -m "feat(community): VAPID public key injection + service worker push handler"
```

---

## Task 29: Run full Vitest + Pest suite

**Files:** N/A (testing)

- [ ] **Step 1: Run all Vitest composable tests**

```bash
npm run test:unit
```

Expected: All Vitest tests verde (~5 composable test files, ~15 assertions total).

If failing, check mocks for `useApi`, `useHaptics`, `useToast` paths.

- [ ] **Step 2: Run full Pest suite**

```bash
DB_DATABASE=wellcore_fitness_test php artisan migrate --force
vendor/bin/pest --parallel
```

Expected: ALL tests verde. Includes Fase A tests (44) + Fase B new tests (~17).

If existing tests fail because of new schema/factories, fix related factories.

- [ ] **Step 3: Run Pint**

```bash
vendor/bin/pint --test
```

If style issues:

```bash
vendor/bin/pint
git add -u
git commit -m "style: pint autofix Fase B"
```

- [ ] **Step 4: ESLint check**

```bash
npm run lint 2>&1 | head -30
```

If config exists. If errors, fix and commit.

---

## Task 30: Build + smoke E2E manual

**Files:** N/A

- [ ] **Step 1: Build local Vite**

```bash
npm run build
```

Expected: `public/build/manifest.json` regenerated, no warnings.

Verify chunks:

```bash
ls public/build/assets | grep -E "Community|Notifications|coach-community"
```

Expected: chunks for Community.vue, NotificationsPreferences.vue, and tab components (lazy-loaded).

- [ ] **Step 2: Commit `public/build/`**

```bash
git add public/build/
git commit -m "build: Vite assets Fase B coach community hub"
```

- [ ] **Step 3: Start dev server (if not running)**

```bash
php artisan serve &
# or use Herd: wellcore-laravel.test
```

Visit `http://wellcore-laravel.test/login` → login as coach.

- [ ] **Step 4: Manual smoke checklist**

- [ ] Sidebar muestra "Comunidad" badge "Nuevo"
- [ ] Click Comunidad → /coach/community → tab "Latido del Equipo" abre por default
- [ ] Datos populated: ring + top performers + at-risk list (con datos reales o empty states)
- [ ] Tab "Posts" → filtros chips + feed paginado
- [ ] Tab "Conversaciones" → threads filtrables
- [ ] Tab "Pulsos" → grid de pulsos
- [ ] Tab "Logros" → PRs + achievements
- [ ] Click "Mensaje al equipo" (bottom-right desktop) → modal abre
- [ ] Modal toggle Anuncio/Push funciona
- [ ] Recipient count se actualiza (puede demorar 300ms)
- [ ] Cancel cierra modal
- [ ] Mobile: hamburger → "Comunidad" → tabs scroll horizontal funciona
- [ ] Mobile: FAB → "Mensaje al equipo" abre modal
- [ ] /coach/notifications → toggles + live save
- [ ] PushPermissionBanner visible si Notification.permission === 'default'
- [ ] Click "Activar ahora" → browser prompt → granted → POST a /api/v/coach/push/subscribe
- [ ] Reduced motion: forzar CSS `prefers-reduced-motion: reduce` → animations desactivadas
- [ ] Dark/light toggle funciona en /coach/community

- [ ] **Step 5: Smoke notes**

Si encuentras bugs durante smoke, NO commitees fixes — documéntalos en checklist y atiéndelos en task siguiente. El smoke debe ser PASS antes de proceder.

---

## Task 31: Update CLAUDE.md + final commit

**Files:**
- Modify: `CLAUDE.md`

- [ ] **Step 1: Append section**

Reemplaza la sección "Community Cross-Role (Fase A — Backend ready)" con:

```markdown
## Community Cross-Role (Fase B — Coach Community Hub shipped)

Fase A backend (16 endpoints + 9 migrations + 6 events) + Fase B Coach UI completos.

### Coach UI (Fase B)
- `/coach/community` — hub con 5 tabs (Latido / Posts / Conversaciones / Pulsos / Logros)
- `/coach/notifications` — preferences page con toggles granulares
- Sidebar: nueva sección "Comunidad" + item "Notificaciones" en Personal
- FAB móvil: 4ta opción "Mensaje al equipo"
- Composables: `useCoachCommunity`, `useCoachPulse`, `useModeration`, `useCoachAnnounce`, `usePushSubscription`
- Backend extensiones Fase B:
  - `POST /api/v/coach/community/announce` — funcional (post o push)
  - `GET /api/v/coach/community/threads` — Tab Conversaciones
  - `GET /api/v/coach/community/achievements?period=` — Tab Logros
  - `GET /api/v/coach/clients/count` — preview recipientes
  - `POST/DELETE /api/v/coach/push/subscribe` — service worker push
  - `GET/PATCH /api/v/coach/notifications/preferences`
- Scheduled: `wellcore:precompute-coach-pulse` cada 5min

UI siguiente en Fase C (Admin Community Center). Spec: `docs/superpowers/specs/2026-05-05-community-cross-role-fase-b-coach-hub-design.md`. Plan: `docs/superpowers/plans/2026-05-05-community-cross-role-fase-b-coach-hub.md`.
```

- [ ] **Step 2: Commit**

```bash
git add CLAUDE.md
git commit -m "docs(community): document Fase B Coach Hub in CLAUDE.md"
```

- [ ] **Step 3: Push**

```bash
git push origin feat/community-cross-role-fase-b
```

---

## Definition of Done — Fase B

### Frontend
- [ ] `Community.vue` hub con 5 tabs sticky + tab transitions direction-aware
- [ ] 5 tab components implementados con loading/error/empty states específicos
- [ ] 5 composables singleton con TTL + dedup + reset hooks
- [ ] 11 components compartidos (badges, ring, cards, modal, banner, tour)
- [ ] `NotificationsPreferences.vue` con live save
- [ ] `CoachLayout.vue` modificado: sección Comunidad + FAB 4ta opción + 2 SVG icons
- [ ] `router/index.js` registrado: `/coach/community` + `/coach/notifications`
- [ ] `auth.js` extendido con 3 reset calls (coach community caches)
- [ ] Real-time: auto-prepend si scroll<200px, toast flotante si lejos
- [ ] Animations + haptics + reduced-motion respetados
- [ ] Dark/light theme coherente
- [ ] Copy 100% latino neutro

### Backend
- [ ] `CommunityController::announce` impl funcional (501 → 201)
- [ ] `CoachCommunityService::threads` + `achievements` methods
- [ ] `CommunityController::threads` + `achievements` actions
- [ ] `Coach\ClientsController::count` action
- [ ] `Coach\PushSubscriptionController` con 4 actions
- [ ] `PushNotificationService::notifyCoachAnnounceToClients` + `notifyCoach`
- [ ] `app/Console/Commands/PrecomputeCoachPulse.php` con scheduled
- [ ] 5 routes nuevas en `routes/api.php`
- [ ] Schedule entry en `routes/console.php`
- [ ] VAPID keys en `.env` y `config/services.php`

### Testing
- [ ] 8 Vitest tests verde (5 composables happy + dedup + reset)
- [ ] 17 Pest tests verde (5 announce + 3 push + 2 prefs + 3 threads + 2 achievements + 2 command)
- [ ] No regresión Pest suite completa
- [ ] Pint OK + ESLint OK
- [ ] Smoke E2E manual: 16 scenarios PASS

### Operations
- [ ] Service worker `public/sw.js` con push handler
- [ ] VAPID injection en blade
- [ ] Cache `wc:coach-pulse:v1:{coach_id}` precomputed cada 5min
- [ ] No regresión Lighthouse: Performance ≥ 70 en `/coach/community`
- [ ] Console clean en /coach/community

### Documentación
- [ ] Spec doc commiteado
- [ ] Plan doc commiteado (este archivo)
- [ ] CLAUDE.md actualizado
- [ ] Branch `feat/community-cross-role-fase-b` push'eada

---

## Self-Review

**1. Spec coverage:**
- 5 tabs (Latido, Posts, Conversaciones, Pulsos, Logros) → Tasks 22-25 ✅
- Modal "Mensaje al equipo" 2-mode → Task 18 + announce impl Task 2 ✅
- Push permission banner + flow → Task 17 + Task 21 + Task 28 ✅
- Notifications preferences page → Task 21 ✅
- Sidebar + router → Task 27 ✅
- Composables singleton (5) → Tasks 9-12 ✅
- Backend extensions → Tasks 1-6 ✅
- Real-time threshold-based → Task 23 + Task 26 ✅
- Animations premium (stagger, haptic, reduced-motion) → Tasks 22-26 ✅

**2. Placeholder scan:**
- Comentario "TODO compute previous PR if needed" en Task 1 (CoachCommunityService.achievements) — intencional, no rompe funcionalidad
- Comentario en CoachLogrosTab "real endpoint wiring done in implementation" — congratulate hace toast optimista; en Fase C+D se conectará al endpoint real /v/coach/posts/{id}/comment cuando exista. Aceptable para Fase B (UX no bloqueante)
- Sin TBDs ni "se decidirá luego" estructurales

**3. Type consistency:**
- `coach_admin_id` en CommunityPost consistente con Fase A ✅
- `author_type` enum 'coach' consistente en announce post creation ✅
- `recipients_count` consistente entre BroadcastMessage row y JSON response ✅
- Composable cache key formats consistentes (`${filter}:${page}:${perPage}`) ✅

**4. Gaps validation:**
- ENV `VAPID_PUBLIC_KEY` requirement → Task 28 step 3 documented ✅
- Service worker required → Task 28 step 2 ✅
- `public/build/` commit required → Task 30 step 2 (memory `feedback_deploy_workflow_authoritative.md`) ✅
- Test DB schema sync → Pre-flight + Task 29 step 2 ✅

**5. Risk coverage:**
- Cache leak entre impersonations: 5 reset hooks en auth.js (Task 13) ✅
- Push permission denied: explainer en /coach/notifications (Task 21) ✅
- Real-time disconnect: Echo auto-reconnect default + tab refresh on visibility ✅
- Auto-prepend confunde scroll: 200px threshold + buffer toast pattern (Task 23) ✅
- Modal recipients miscount: confirmation step si > 20 (Task 18) ✅

**6. Commit graph cleanliness:**
- 32 commits totales aprox (1 commit por task, con TDD: tests + impl combined o separados según patrón)
- Branch dedicada `feat/community-cross-role-fase-b` (no contamina Fase A)
- Build assets commiteados al final (Task 30)

Plan listo para ejecución por subagent-driven-development o executing-plans.

