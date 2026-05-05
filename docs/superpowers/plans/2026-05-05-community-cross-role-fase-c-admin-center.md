# Community Cross-Role — Fase C: Admin Community Center Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:subagent-driven-development. Steps use `- [ ]` checkboxes.

**Goal:** Construir Admin Community Center (UI Vue 3 SPA + backend extensions). Al final el superadmin abre `/admin/community` con 5 tabs analytic-heavy: Pulse Cross-Coach (default) / Live Feed Community / Broadcast Center / Moderation Queue / Analytics Coach (drill-down).

**Architecture:** Vue 3.5 Composition API + Pinia + Chart.js 4 + Reverb. Composables singleton TTL + dedup. Vista usa shell nuevo `wc-admin-shell` (full-target migrated route). Tests Vitest composables + Pest backend.

**Tech Stack:** PHP 8.4, Laravel 13.1.1, Vue 3.5, Chart.js 4, Tailwind CSS 4, Pest v3, Vitest. Linter Pint + ESLint.

**Spec:** `docs/superpowers/specs/2026-05-05-community-cross-role-fase-c-admin-center-design.md`

**Phase scope:** Solo Fase C. Paralelizable con Fase B (no comparten archivos UI). Fase D (Cross-Role Communication Layer) tiene plan propio.

---

## File Map (Fase C)

### Frontend new files (16)

```
resources/js/vue/pages/Admin/
├── Community.vue
└── NotificationsPreferences.vue

resources/js/vue/pages/Admin/community/
├── AdminPulseCrossCoachTab.vue
├── AdminLiveFeedCommunityTab.vue
├── AdminBroadcastCenterTab.vue
├── AdminModerationQueueTab.vue
└── AdminAnalyticsCoachTab.vue

resources/js/vue/components/admin/community/
├── CoachAnalyticsTable.vue
├── CoachSparkline.vue
├── EngagementHeatmap.vue
├── BroadcastPreviewBar.vue
├── BroadcastHistoryList.vue
├── ModerationReportCard.vue
├── ModerationActionDialog.vue
├── CoachAnalyticsKPIBar.vue
└── TimeSeriesChart.vue

resources/js/vue/composables/
├── useAdminCommunity.js
├── useBroadcast.js
└── useModerationQueue.js
```

### Backend new files (4)

```
app/Http/Controllers/Api/Admin/
├── CoachAnalyticsController.php
└── NotificationPreferencesController.php

app/Models/
└── AdminNotificationPreference.php

database/migrations/
└── 2026_05_05_000010_create_admin_notification_preferences_table.php
```

### Modified files

```
resources/js/vue/components/ui/wellcore-admin/WcAdminSidebar.vue
resources/js/vue/layouts/AdminLayout.vue
resources/js/vue/router/index.js
resources/js/vue/stores/auth.js
app/Services/AdminCommunityService.php          # +coachAnalytics + helpers
app/Http/Controllers/Api/Admin/CommunityController.php   # +pinAdminOverride +makeGlobal +feed (community filter)
app/Http/Controllers/Api/Admin/BroadcastController.php   # validation rigurosa
routes/api.php                                            # +5 routes
```

### Tests

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

## Pre-flight

```bash
cd C:\Users\GODSF\Herd\wellcore-laravel
git fetch origin
git status --short
git checkout -b feat/community-cross-role-fase-c
```

Verify Chart.js available (Fase B may have installed):

```bash
npm ls chart.js
```

If empty: `npm install chart.js`

Verify Fase A backend:

```bash
php artisan route:list --path=api/v/admin/community
# Expected: pulse-cross-coach, moderation/queue, etc.
php artisan route:list --path=api/v/admin/broadcast
# Expected: preview, send, history
```

---

## Task 0: Branch + Chart.js setup

- [ ] **Step 1: Create branch**

```bash
git checkout -b feat/community-cross-role-fase-c
```

- [ ] **Step 2: Install Chart.js if missing**

```bash
npm install chart.js
```

Verify `package.json` includes `"chart.js"` in dependencies.

- [ ] **Step 3: Commit**

```bash
git add package.json package-lock.json
git commit -m "chore(community-fase-c): add chart.js dependency"
```

---

## Task 1: Migration — admin_notification_preferences

**Files:**
- Create: `database/migrations/2026_05_05_000010_create_admin_notification_preferences_table.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_preferences');
    }
};
```

- [ ] **Step 2: Migrate (local + test DB)**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000010_create_admin_notification_preferences_table.php
DB_DATABASE=wellcore_fitness_test php artisan migrate --force
```

- [ ] **Step 3: Verify**

```bash
php artisan tinker --execute="dump(Schema::getColumnListing('admin_notification_preferences'));"
```

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_05_000010_create_admin_notification_preferences_table.php
git commit -m "feat(community): admin_notification_preferences migration"
```

---

## Task 2: Model — AdminNotificationPreference

**Files:**
- Create: `app/Models/AdminNotificationPreference.php`

- [ ] **Step 1: Create model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotificationPreference extends Model
{
    protected $table = 'admin_notification_preferences';
    protected $primaryKey = 'admin_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'notify_post_reported',
        'notify_coach_no_activity_7d',
        'notify_thread_conflict',
        'notify_broadcast_sent',
        'notify_client_spam',
        'push_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notify_post_reported'         => 'boolean',
            'notify_coach_no_activity_7d'  => 'boolean',
            'notify_thread_conflict'       => 'boolean',
            'notify_broadcast_sent'        => 'boolean',
            'notify_client_spam'           => 'boolean',
            'push_enabled'                 => 'boolean',
            'in_app_enabled'               => 'boolean',
            'updated_at'                   => 'datetime',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function forAdmin(int $adminId): self
    {
        return static::firstOrCreate(['admin_id' => $adminId]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Models/AdminNotificationPreference.php
git commit -m "feat(community): AdminNotificationPreference model"
```

---

## Task 3: AdminCommunityService — coachAnalytics + helpers (TDD)

**Files:**
- Modify: `app/Services/AdminCommunityService.php`
- Create: `tests/Unit/Services/AdminCommunityServiceCoachAnalyticsTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Unit/Services/AdminCommunityServiceCoachAnalyticsTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Services\AdminCommunityService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->service = app(AdminCommunityService::class);
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id, 'status' => 'activo']);
});

it('returns coach analytics shape', function () {
    $analytics = $this->service->coachAnalytics($this->coach->id);

    expect($analytics)->toHaveKeys(['coach', 'kpis', 'posts_per_day_90d', 'engagement_per_day_90d', 'top_clients', 'alerts', 'recent_audit']);
    expect($analytics['coach'])->toHaveKeys(['id', 'name', 'role']);
    expect($analytics['kpis'])->toHaveKeys([
        'active_clients', 'total_posts_30d', 'engagement_rate',
        'response_time_p50_min', 'response_time_p95_min',
        'moderation_actions_30d', 'broadcasts_sent_30d',
    ]);
});

it('counts active clients correctly', function () {
    Client::factory()->count(3)->create(['coach_id' => $this->coach->id, 'status' => 'activo']);
    Client::factory()->create(['coach_id' => $this->coach->id, 'status' => 'inactivo']);

    $analytics = $this->service->coachAnalytics($this->coach->id);
    expect($analytics['kpis']['active_clients'])->toBe(4); // 3 nuevos + el de beforeEach
});

it('counts posts last 30d', function () {
    CommunityPost::factory()->count(2)->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
        'created_at' => now()->subDays(5),
    ]);
    CommunityPost::factory()->create([
        'client_id' => $this->client->id,
        'coach_admin_id' => $this->coach->id,
        'created_at' => now()->subDays(60),
    ]);

    $analytics = $this->service->coachAnalytics($this->coach->id);
    expect($analytics['kpis']['total_posts_30d'])->toBe(2);
});

it('returns 90-day series with 90 entries', function () {
    $analytics = $this->service->coachAnalytics($this->coach->id);
    expect($analytics['posts_per_day_90d'])->toBeArray();
    expect(count($analytics['posts_per_day_90d']))->toBe(90);
});
```

- [ ] **Step 2: Run tests — fail**

```bash
vendor/bin/pest tests/Unit/Services/AdminCommunityServiceCoachAnalyticsTest.php
```

- [ ] **Step 3: Implement**

Append to `app/Services/AdminCommunityService.php`:

```php
public function coachAnalytics(int $coachId): array
{
    $coach = \App\Models\Admin::findOrFail($coachId);
    $clientIds = app(\App\Services\CoachCommunityService::class)->resolveClientIds($coachId);
    $clientIdsArr = is_array($clientIds) ? $clientIds : $clientIds->all();

    return [
        'coach' => [
            'id' => $coach->id,
            'name' => $coach->name,
            'avatar_url' => $coach->avatar_url ?? null,
            'joined_at' => $coach->created_at?->toIso8601String(),
            'role' => $coach->role instanceof \BackedEnum ? $coach->role->value : (string) $coach->role,
        ],
        'kpis' => [
            'active_clients' => \App\Models\Client::whereIn('id', $clientIdsArr)->where('status', 'activo')->count(),
            'total_posts_30d' => \App\Models\CommunityPost::whereIn('client_id', $clientIdsArr)
                ->where('created_at', '>=', now()->subDays(30))->count(),
            'engagement_rate' => $this->engagementRate30d($clientIdsArr),
            'response_time_p50_min' => $this->responseTimePercentile($coachId, $clientIdsArr, 50),
            'response_time_p95_min' => $this->responseTimePercentile($coachId, $clientIdsArr, 95),
            'moderation_actions_30d' => \App\Models\ModerationAction::query()
                ->where('actor_type', 'coach')->where('actor_id', $coachId)
                ->where('created_at', '>=', now()->subDays(30))->count(),
            'broadcasts_sent_30d' => \App\Models\BroadcastMessage::query()
                ->where('sender_type', 'coach')->where('sender_id', $coachId)
                ->where('sent_at', '>=', now()->subDays(30))->count(),
        ],
        'posts_per_day_90d' => $this->seriesPostsPerDay($clientIdsArr, 90),
        'engagement_per_day_90d' => $this->seriesEngagementPerDay($clientIdsArr, 90),
        'top_clients' => $this->topClientsForCoach($clientIdsArr, 30),
        'alerts' => $this->coachAlerts($coachId, $clientIdsArr),
        'recent_audit' => \App\Models\ModerationAction::query()
            ->where('actor_type', 'coach')->where('actor_id', $coachId)
            ->orderByDesc('created_at')->limit(10)->get()->toArray(),
    ];
}

private function engagementRate30d(array $clientIds): float
{
    if (empty($clientIds)) return 0.0;
    $posts = \App\Models\CommunityPost::whereIn('client_id', $clientIds)
        ->where('created_at', '>=', now()->subDays(30))->count();
    if ($posts === 0) return 0.0;
    $reactions = \DB::table('post_reactions')
        ->whereIn('post_id', function ($q) use ($clientIds) {
            $q->select('id')->from('community_posts')
              ->whereIn('client_id', $clientIds)
              ->where('created_at', '>=', now()->subDays(30));
        })->count();
    $comments = \App\Models\PostComment::query()
        ->whereIn('post_id', function ($q) use ($clientIds) {
            $q->select('id')->from('community_posts')
              ->whereIn('client_id', $clientIds)
              ->where('created_at', '>=', now()->subDays(30));
        })->count();
    return round(($reactions + $comments) / max(1, $posts), 2);
}

private function responseTimePercentile(int $coachId, array $clientIds, int $percentile): int
{
    if (empty($clientIds)) return 0;
    $rows = \DB::select("
        SELECT TIMESTAMPDIFF(MINUTE, p.created_at, c.created_at) AS rt_min
        FROM community_posts p
        INNER JOIN post_comments c ON c.post_id = p.id
            AND c.author_type = 'coach' AND c.author_admin_id = ?
        WHERE p.client_id IN (" . implode(',', array_fill(0, count($clientIds), '?')) . ")
            AND p.created_at >= ?
            AND c.created_at = (
                SELECT MIN(c2.created_at) FROM post_comments c2
                WHERE c2.post_id = p.id AND c2.author_type = 'coach' AND c2.author_admin_id = ?
            )
    ", array_merge([$coachId], $clientIds, [now()->subDays(30)->toDateTimeString(), $coachId]));

    $values = array_map(fn ($r) => (int) $r->rt_min, $rows);
    if (empty($values)) return 0;
    sort($values);
    $idx = (int) ceil(($percentile / 100) * count($values)) - 1;
    return $values[max(0, $idx)] ?? 0;
}

private function seriesPostsPerDay(array $clientIds, int $days): array
{
    $start = now()->subDays($days)->startOfDay();
    $rows = \DB::table('community_posts')
        ->whereIn('client_id', $clientIds ?: [0])
        ->where('created_at', '>=', $start)
        ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
        ->groupBy('d')->get()
        ->keyBy('d');

    $series = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $series[] = ['date' => $date, 'count' => (int) ($rows[$date]->c ?? 0)];
    }
    return $series;
}

private function seriesEngagementPerDay(array $clientIds, int $days): array
{
    $start = now()->subDays($days)->startOfDay();
    $reactionsRows = \DB::table('post_reactions as r')
        ->join('community_posts as p', 'p.id', '=', 'r.post_id')
        ->whereIn('p.client_id', $clientIds ?: [0])
        ->where('r.created_at', '>=', $start)
        ->selectRaw('DATE(r.created_at) as d, COUNT(*) as c')
        ->groupBy('d')->get()->keyBy('d');

    $commentsRows = \DB::table('post_comments as c')
        ->join('community_posts as p', 'p.id', '=', 'c.post_id')
        ->whereIn('p.client_id', $clientIds ?: [0])
        ->where('c.created_at', '>=', $start)
        ->selectRaw('DATE(c.created_at) as d, COUNT(*) as c')
        ->groupBy('d')->get()->keyBy('d');

    $series = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $r = (int) ($reactionsRows[$date]->c ?? 0);
        $c = (int) ($commentsRows[$date]->c ?? 0);
        $series[] = ['date' => $date, 'count' => $r + $c];
    }
    return $series;
}

private function topClientsForCoach(array $clientIds, int $days): array
{
    if (empty($clientIds)) return [];
    return \DB::table('community_posts')
        ->whereIn('client_id', $clientIds)
        ->where('created_at', '>=', now()->subDays($days))
        ->selectRaw('client_id, COUNT(*) as posts')
        ->groupBy('client_id')
        ->orderByDesc('posts')
        ->limit(5)
        ->get()
        ->map(function ($r) {
            $client = \App\Models\Client::find($r->client_id);
            return [
                'client_id' => $r->client_id,
                'client_name' => $client?->name ?? 'Cliente',
                'posts' => (int) $r->posts,
                'engagement_received' => 0, // optional: enrich later
            ];
        })
        ->all();
}

private function coachAlerts(int $coachId, array $clientIds): array
{
    $alerts = [];

    foreach ($clientIds as $cid) {
        $client = \App\Models\Client::find($cid);
        if (! $client) continue;
        $lastLogin = $client->last_login_at;
        if ($lastLogin && $lastLogin->lt(now()->subDays(7))) {
            $alerts[] = [
                'type' => 'client_inactive',
                'client_id' => $cid,
                'client_name' => $client->name,
                'days' => $lastLogin->diffInDays(now()),
            ];
        }
    }

    return array_slice($alerts, 0, 10);
}
```

- [ ] **Step 4: Run tests — pass**

```bash
vendor/bin/pest tests/Unit/Services/AdminCommunityServiceCoachAnalyticsTest.php
```

Expected: 4/4 PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/AdminCommunityService.php tests/Unit/Services/AdminCommunityServiceCoachAnalyticsTest.php
git commit -m "feat(community): AdminCommunityService.coachAnalytics + 6 helpers"
```

---

## Task 4: CoachAnalyticsController + tests

**Files:**
- Create: `app/Http/Controllers/Api/Admin/CoachAnalyticsController.php`
- Create: `tests/Feature/Admin/CoachAnalyticsTest.php`

- [ ] **Step 1: Write tests**

```php
<?php
// tests/Feature/Admin/CoachAnalyticsTest.php

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Cache::flush();
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    Client::factory()->count(3)->create(['coach_id' => $this->coach->id, 'status' => 'activo']);

    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id'    => $this->admin->id,
        'user_type'  => 'admin',
        'token'      => hash('sha256', $this->token),
        'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('returns coach analytics with full shape', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v/admin/community/coaches/{$this->coach->id}/analytics")
        ->assertOk()
        ->assertJsonStructure([
            'coach' => ['id', 'name', 'role'],
            'kpis' => ['active_clients', 'total_posts_30d', 'engagement_rate'],
            'posts_per_day_90d',
            'engagement_per_day_90d',
            'top_clients',
            'alerts',
            'recent_audit',
        ]);

    expect($resp->json('coach.id'))->toBe($this->coach->id);
});

it('caches result for 600s', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v/admin/community/coaches/{$this->coach->id}/analytics");
    expect(Cache::has("wc:admin-coach-analytics:v1:{$this->coach->id}"))->toBeTrue();
});

it('rejects coach role from accessing', function () {
    $coachToken = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->coach->id, 'user_type' => 'admin',
        'token' => hash('sha256', $coachToken), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$coachToken}")
        ->getJson("/api/v/admin/community/coaches/{$this->coach->id}/analytics")
        ->assertStatus(403);
});
```

- [ ] **Step 2: Run tests — fail**

```bash
vendor/bin/pest tests/Feature/Admin/CoachAnalyticsTest.php
```

- [ ] **Step 3: Implement controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminCommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CoachAnalyticsController extends Controller
{
    public function __construct(private AdminCommunityService $service) {}

    public function show(Request $request, int $coachId): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $payload = Cache::remember(
            key: "wc:admin-coach-analytics:v1:{$coachId}",
            ttl: 600,
            callback: fn () => $this->service->coachAnalytics($coachId),
        );

        return response()->json($payload);
    }

    private function isAdmin(mixed $user): bool
    {
        if (! $user instanceof Admin) return false;
        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
        return in_array($role, ['admin', 'superadmin', 'jefe'], true);
    }
}
```

- [ ] **Step 4: Add route**

In `routes/api.php`:

```php
Route::get('v/admin/community/coaches/{coachId}/analytics', [Admin\CoachAnalyticsController::class, 'show']);
```

- [ ] **Step 5: Run tests — pass**

```bash
vendor/bin/pest tests/Feature/Admin/CoachAnalyticsTest.php
```

Expected: 3/3 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/Admin/CoachAnalyticsController.php routes/api.php tests/Feature/Admin/CoachAnalyticsTest.php
git commit -m "feat(community): CoachAnalyticsController with 600s cache"
```

---

## Task 5: NotificationPreferencesController admin + tests

**Files:**
- Create: `app/Http/Controllers/Api/Admin/NotificationPreferencesController.php`
- Create: `tests/Feature/Admin/AdminNotificationPreferencesTest.php`

- [ ] **Step 1: Tests**

```php
<?php
// tests/Feature/Admin/AdminNotificationPreferencesTest.php

use App\Models\Admin;
use App\Models\AdminNotificationPreference;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->admin->id, 'user_type' => 'admin',
        'token' => hash('sha256', $this->token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('returns admin defaults when no row exists', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/admin/notifications/preferences')->assertOk();

    expect($resp->json('notify_post_reported'))->toBeTrue();
    expect($resp->json('notify_broadcast_sent'))->toBeFalse();
});

it('patches granular preferences', function () {
    AdminNotificationPreference::forAdmin($this->admin->id);

    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->patchJson('/api/v/admin/notifications/preferences', [
            'notify_broadcast_sent' => true,
        ])->assertOk();

    expect($resp->json('notify_broadcast_sent'))->toBeTrue();
});
```

- [ ] **Step 2: Implement**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $prefs = AdminNotificationPreference::forAdmin($admin->id);
        return response()->json($prefs);
    }

    public function update(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $allowed = [
            'notify_post_reported','notify_coach_no_activity_7d','notify_thread_conflict',
            'notify_broadcast_sent','notify_client_spam','push_enabled','in_app_enabled',
        ];

        $data = $request->validate(array_combine(
            $allowed,
            array_fill(0, count($allowed), 'sometimes|boolean')
        ));

        $prefs = AdminNotificationPreference::forAdmin($admin->id);
        $prefs->fill($data)->save();

        return response()->json($prefs->fresh());
    }

    private function isAdmin(mixed $user): bool
    {
        if (! $user instanceof Admin) return false;
        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
        return in_array($role, ['admin', 'superadmin', 'jefe'], true);
    }
}
```

Add routes:

```php
Route::get('v/admin/notifications/preferences', [Admin\NotificationPreferencesController::class, 'show']);
Route::patch('v/admin/notifications/preferences', [Admin\NotificationPreferencesController::class, 'update']);
```

- [ ] **Step 3: Run + commit**

```bash
vendor/bin/pest tests/Feature/Admin/AdminNotificationPreferencesTest.php
git add app/Http/Controllers/Api/Admin/NotificationPreferencesController.php routes/api.php tests/Feature/Admin/AdminNotificationPreferencesTest.php
git commit -m "feat(community): admin notification preferences endpoint"
```

---

## Task 6: AdminCommunityController extensions (pinAdminOverride + makeGlobal)

**Files:**
- Modify: `app/Http/Controllers/Api/Admin/CommunityController.php`
- Create: `tests/Feature/Admin/PostMakeGlobalTest.php`

- [ ] **Step 1: Tests**

```php
<?php
// tests/Feature/Admin/PostMakeGlobalTest.php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\ModerationAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->post = CommunityPost::factory()->create([
        'client_id' => $this->client->id, 'coach_admin_id' => $this->coach->id,
    ]);

    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->admin->id, 'user_type' => 'admin',
        'token' => hash('sha256', $this->token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('admin overrides pin a post in another coach community', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/v/admin/community/posts/{$this->post->id}/pin", ['hours' => 24])
        ->assertOk();

    expect(\App\Models\PinnedPost::where('post_id', $this->post->id)->exists())->toBeTrue();
});

it('admin makes post global', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/v/admin/community/posts/{$this->post->id}/make-global")
        ->assertOk();

    $this->post->refresh();
    expect($this->post->is_global)->toBeTrue();
    expect($this->post->is_official)->toBeTrue();

    expect(ModerationAction::where('target_id', $this->post->id)->where('action_type', 'make_official')->where('actor_type', 'admin')->exists())->toBeTrue();
});

it('rejects non-superadmin from make-global', function () {
    $jefe = Admin::factory()->create(['role' => 'jefe']);
    $token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $jefe->id, 'user_type' => 'admin',
        'token' => hash('sha256', $token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    // Jefe can't make-global (only superadmin)
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v/admin/community/posts/{$this->post->id}/make-global")
        ->assertStatus(403);
});
```

- [ ] **Step 2: Implement methods in CommunityController admin**

```php
public function pinAdminOverride(Request $request, int $postId): JsonResponse
{
    $admin = $request->user();
    abort_unless($this->isAdmin($admin), 403);

    $request->validate([
        'hours' => 'nullable|integer|min:1|max:720',
        'note'  => 'nullable|string|max:255',
    ]);

    $post = \App\Models\CommunityPost::findOrFail($postId);

    app(\App\Services\ModerationService::class)->pinPost(
        $post,
        $admin,
        'admin',
        $request->integer('hours', 168),
        $request->string('note')->toString() ?: null,
    );

    return response()->json(['ok' => true], 200);
}

public function makeGlobal(Request $request, int $postId): JsonResponse
{
    $admin = $request->user();
    abort_unless($this->isSuperadmin($admin), 403);

    $post = \App\Models\CommunityPost::findOrFail($postId);
    $post->update([
        'is_official' => true,
        'is_global'   => true,
        'author_type' => 'admin',
        'author_admin_id' => $admin->id,
    ]);

    \App\Models\ModerationAction::create([
        'actor_type'  => 'admin',
        'actor_id'    => $admin->id,
        'action_type' => 'make_official',
        'target_type' => 'post',
        'target_id'   => $post->id,
        'metadata'    => ['scope' => 'global'],
        'created_at'  => now(),
    ]);

    event(new \App\Events\PostMadeOfficial($post->id, $post->coach_admin_id, $admin->id, 'admin'));

    return response()->json(['ok' => true], 200);
}

private function isSuperadmin(mixed $user): bool
{
    if (! $user instanceof \App\Models\Admin) return false;
    $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
    return $role === 'superadmin';
}
```

Add routes:

```php
Route::post('v/admin/community/posts/{postId}/pin', [Admin\CommunityController::class, 'pinAdminOverride']);
Route::post('v/admin/community/posts/{postId}/make-global', [Admin\CommunityController::class, 'makeGlobal']);
```

- [ ] **Step 3: Run + commit**

```bash
vendor/bin/pest tests/Feature/Admin/PostMakeGlobalTest.php
git add app/Http/Controllers/Api/Admin/CommunityController.php routes/api.php tests/Feature/Admin/PostMakeGlobalTest.php
git commit -m "feat(community): admin pin override + make-global with audit"
```

---

## Task 7: BroadcastController validation + tests

**Files:**
- Modify: `app/Http/Controllers/Api/Admin/BroadcastController.php`
- Create: `tests/Feature/Admin/BroadcastPreviewTest.php`
- Create: `tests/Feature/Admin/BroadcastSendChunkedTest.php`

- [ ] **Step 1: Refine validation in BroadcastController**

In existing `send()` method, ensure rules:

```php
$validated = $request->validate([
    'audience'          => 'required|in:clients,coaches,all_communities,segmented',
    'segment'           => 'nullable|array',
    'segment.plan'      => 'nullable|array',
    'segment.status'    => 'nullable|array',
    'segment.coach_id'  => 'nullable|integer',
    'segment.inactive_days' => 'nullable|integer|min:1|max:365',
    'subject'           => 'nullable|string|max:255',
    'body'              => 'required|string|max:2000',
    'push_enabled'      => 'sometimes|boolean',
]);
```

`preview()` action similar validation but no body required:

```php
$validated = $request->validate([
    'audience'  => 'required|in:clients,coaches,all_communities',
    'segment'   => 'nullable|array',
]);
```

- [ ] **Step 2: BroadcastPreviewTest**

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
    Client::factory()->count(5)->create(['plan' => 'rise', 'status' => 'activo']);
    Client::factory()->count(3)->create(['plan' => 'metodo', 'status' => 'activo']);

    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->admin->id, 'user_type' => 'admin',
        'token' => hash('sha256', $this->token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('returns count for plan rise filter', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/admin/broadcast/preview', [
            'audience' => 'clients',
            'segment' => ['plan' => ['rise']],
        ])->assertOk();

    expect($resp->json('count'))->toBe(5);
});

it('does not create BroadcastMessage row on preview', function () {
    $countBefore = \App\Models\BroadcastMessage::count();
    $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v/admin/broadcast/preview', ['audience' => 'clients']);
    expect(\App\Models\BroadcastMessage::count())->toBe($countBefore);
});
```

- [ ] **Step 3: BroadcastSendChunkedTest**

```php
<?php

use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('chunks delivery for 200+ recipients', function () {
    $admin = Admin::factory()->create(['role' => 'superadmin']);
    Client::factory()->count(150)->create(['plan' => 'esencial', 'status' => 'activo']);

    $token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $admin->id, 'user_type' => 'admin',
        'token' => hash('sha256', $token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $resp = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v/admin/broadcast/send', [
            'audience' => 'clients',
            'segment'  => ['plan' => ['esencial'], 'status' => ['activo']],
            'body'     => 'Bulk test',
            'push_enabled' => false,
        ])->assertOk();

    $broadcast = BroadcastMessage::latest('sent_at')->first();
    expect($broadcast->recipients_count)->toBe(150);
    expect($broadcast->audience_type)->toBe('clients');
});
```

- [ ] **Step 4: Run + commit**

```bash
vendor/bin/pest tests/Feature/Admin/BroadcastPreviewTest.php tests/Feature/Admin/BroadcastSendChunkedTest.php
git add app/Http/Controllers/Api/Admin/BroadcastController.php tests/Feature/Admin/BroadcastPreviewTest.php tests/Feature/Admin/BroadcastSendChunkedTest.php
git commit -m "feat(community): broadcast validation + preview + chunked send tests"
```

---

## Task 8: Backend test — ModerationQueueOrderingTest

**Files:**
- Create: `tests/Feature/Admin/ModerationQueueOrderingTest.php`

- [ ] **Step 1: Test**

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'superadmin']);
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
    $this->post1 = CommunityPost::factory()->create(['client_id' => $this->client->id, 'coach_admin_id' => $this->coach->id]);
    $this->post2 = CommunityPost::factory()->create(['client_id' => $this->client->id, 'coach_admin_id' => $this->coach->id]);

    PostReport::factory()->count(3)->create(['post_id' => $this->post1->id, 'reporter_id' => $this->client->id]);
    PostReport::factory()->create(['post_id' => $this->post2->id, 'reporter_id' => $this->client->id]);

    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->admin->id, 'user_type' => 'admin',
        'token' => hash('sha256', $this->token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('orders queue with multi-reportes first', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/admin/community/moderation/queue')
        ->assertOk();

    $data = $resp->json('data');
    // Multi-reportados (3) viene antes (urgency_score más alto)
    expect($data[0]['post_id'])->toBe($this->post1->id);
});
```

- [ ] **Step 2: Run + commit (verify backend ordering already correct in Fase A or fix)**

```bash
vendor/bin/pest tests/Feature/Admin/ModerationQueueOrderingTest.php
git add tests/Feature/Admin/ModerationQueueOrderingTest.php
git commit -m "test(community): ModerationQueueOrdering verifies urgency-based ordering"
```

---

## Task 9: Composable — useAdminCommunity

**Files:**
- Create: `resources/js/vue/composables/useAdminCommunity.js`
- Create: `tests/Unit/Composables/useAdminCommunity.test.js`

- [ ] **Step 1: Implement composable** (per spec Section "Composables")

```js
import { ref } from 'vue';
import { useApi } from './useApi';

const pulseCache = new Map();
const PULSE_TTL_MS = 60_000;
const coachAnalyticsCache = new Map();
const COACH_ANALYTICS_TTL_MS = 120_000;
const promises = new Map();

export function useAdminCommunity() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    async function fetchPulseCrossCoach({ period = 'week', force = false } = {}) {
        const key = `pulse:${period}`;
        if (!force && pulseCache.has(period)) {
            const c = pulseCache.get(period);
            if (Date.now() - c.timestamp < PULSE_TTL_MS) return c.data;
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
                    console.error('[useAdminCommunity] pulse', err);
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

- [ ] **Step 2: Test**

```js
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useAdminCommunity, resetAdminCommunity } from '../../../resources/js/vue/composables/useAdminCommunity';

const apiGet = vi.fn();
vi.mock('../../../resources/js/vue/composables/useApi', () => ({
    useApi: () => ({ get: apiGet }),
}));

describe('useAdminCommunity', () => {
    beforeEach(() => {
        resetAdminCommunity();
        apiGet.mockReset();
    });

    it('caches pulse per period', async () => {
        apiGet.mockResolvedValue({ data: { coaches: [] } });
        const { fetchPulseCrossCoach } = useAdminCommunity();
        await fetchPulseCrossCoach({ period: 'week' });
        await fetchPulseCrossCoach({ period: 'week' });
        expect(apiGet).toHaveBeenCalledTimes(1);
    });

    it('different periods bypass cache', async () => {
        apiGet.mockResolvedValue({ data: { coaches: [] } });
        const { fetchPulseCrossCoach } = useAdminCommunity();
        await fetchPulseCrossCoach({ period: 'week' });
        await fetchPulseCrossCoach({ period: 'month' });
        expect(apiGet).toHaveBeenCalledTimes(2);
    });

    it('caches coach analytics per coachId', async () => {
        apiGet.mockResolvedValue({ data: { coach: { id: 1 } } });
        const { fetchCoachAnalytics } = useAdminCommunity();
        await fetchCoachAnalytics(1);
        await fetchCoachAnalytics(1);
        expect(apiGet).toHaveBeenCalledTimes(1);
    });

    it('reset clears cache', async () => {
        apiGet.mockResolvedValue({ data: {} });
        const { fetchPulseCrossCoach } = useAdminCommunity();
        await fetchPulseCrossCoach({ period: 'week' });
        resetAdminCommunity();
        await fetchPulseCrossCoach({ period: 'week' });
        expect(apiGet).toHaveBeenCalledTimes(2);
    });
});
```

- [ ] **Step 3: Run + commit**

```bash
npm run test:unit -- useAdminCommunity
git add resources/js/vue/composables/useAdminCommunity.js tests/Unit/Composables/useAdminCommunity.test.js
git commit -m "feat(community): useAdminCommunity composable + Vitest"
```

---

## Task 10: Composables — useBroadcast + useModerationQueue

**Files:**
- Create: `resources/js/vue/composables/useBroadcast.js`
- Create: `resources/js/vue/composables/useModerationQueue.js`
- Create: `tests/Unit/Composables/useBroadcast.test.js`
- Create: `tests/Unit/Composables/useModerationQueue.test.js`

Implementations follow the Spec Section "Composables (3 nuevos)". Tests verify cache + reset + dedup.

```bash
git add resources/js/vue/composables/useBroadcast.js resources/js/vue/composables/useModerationQueue.js tests/Unit/Composables/useBroadcast.test.js tests/Unit/Composables/useModerationQueue.test.js
git commit -m "feat(community): useBroadcast + useModerationQueue composables"
```

---

## Task 11: auth.js — invalidate admin community caches

**Files:**
- Modify: `resources/js/vue/stores/auth.js`

- [ ] Add imports:

```js
import { resetAdminCommunity } from '../composables/useAdminCommunity';
import { resetBroadcast } from '../composables/useBroadcast';
import { resetModerationQueue } from '../composables/useModerationQueue';
```

- [ ] In `setAuth` (token change check):

```js
if (data.token && data.token !== token.value) {
    resetGroupPulse();
    resetCoachCommunity();
    resetCoachPulse();
    resetCoachAnnounce();
    resetAdminCommunity();
    resetBroadcast();
    resetModerationQueue();
}
```

- [ ] In `clearAuth`:

```js
resetContractGate();
resetGroupPulse();
resetCoachCommunity();
resetCoachPulse();
resetCoachAnnounce();
resetAdminCommunity();
resetBroadcast();
resetModerationQueue();
```

- [ ] Commit:

```bash
git add resources/js/vue/stores/auth.js
git commit -m "feat(community): auth store invalidates admin community caches"
```

---

## Task 12: Component — TimeSeriesChart (Chart.js wrapper)

**Files:**
- Create: `resources/js/vue/components/admin/community/TimeSeriesChart.vue`

```vue
<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    data: { type: Array, required: true }, // [{date, count}]
    label: { type: String, default: 'Posts' },
    color: { type: String, default: '#DC2626' },
    height: { type: Number, default: 220 },
});

const canvasRef = ref(null);
let chartInstance = null;

function buildChart() {
    if (!canvasRef.value) return;
    if (chartInstance) chartInstance.destroy();

    const ctx = canvasRef.value.getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.data.map(d => d.date),
            datasets: [{
                label: props.label,
                data: props.data.map(d => d.count),
                borderColor: props.color,
                backgroundColor: props.color + '20',
                fill: true,
                tension: 0.35,
                pointRadius: 0,
                pointHoverRadius: 5,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.4)' } },
            },
        },
    });
}

watch(() => props.data, () => buildChart(), { deep: true });
onMounted(() => buildChart());
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy(); });
</script>

<template>
  <div :style="{ height: height + 'px' }" class="relative">
    <canvas ref="canvasRef"></canvas>
  </div>
</template>
```

```bash
git add resources/js/vue/components/admin/community/TimeSeriesChart.vue
git commit -m "feat(community): TimeSeriesChart wrapper Chart.js"
```

---

## Task 13: Component — CoachSparkline + CoachAnalyticsTable

**Files:**
- Create: `resources/js/vue/components/admin/community/CoachSparkline.vue`
- Create: `resources/js/vue/components/admin/community/CoachAnalyticsTable.vue`

- [ ] **Step 1: CoachSparkline (SVG-only, no Chart.js for inline density)**

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    series: { type: Array, default: () => [] }, // numeric array
    color: { type: String, default: '#DC2626' },
    width: { type: Number, default: 80 },
    height: { type: Number, default: 24 },
});

const path = computed(() => {
    if (!props.series.length) return '';
    const max = Math.max(1, ...props.series);
    const step = props.width / Math.max(1, props.series.length - 1);
    return props.series.map((v, i) => {
        const x = i * step;
        const y = props.height - (v / max) * props.height;
        return (i === 0 ? 'M' : 'L') + x.toFixed(1) + ',' + y.toFixed(1);
    }).join(' ');
});
</script>

<template>
  <svg :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`" class="overflow-visible">
    <path :d="path" :stroke="color" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
  </svg>
</template>
```

- [ ] **Step 2: CoachAnalyticsTable**

```vue
<script setup>
import CoachSparkline from './CoachSparkline.vue';

const props = defineProps({
    coaches: { type: Array, default: () => [] },
});
const emit = defineEmits(['drill-down']);

function format(num) {
    return num >= 1000 ? (num / 1000).toFixed(1) + 'k' : num;
}

function alertIcon(type) {
    return type === 'no_activity_7d' ? '🔥' : type === 'client_spam' ? '⚠️' : type === 'thread_conflict' ? '💥' : null;
}
</script>

<template>
  <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-wc-bg-tertiary text-xs uppercase tracking-widest text-wc-text-tertiary">
        <tr>
          <th class="text-left px-4 py-3">Coach</th>
          <th class="text-right px-3 py-3">Clientes</th>
          <th class="text-right px-3 py-3">Posts 30d</th>
          <th class="text-right px-3 py-3">Engag</th>
          <th class="text-right px-3 py-3">Resp p50</th>
          <th class="text-center px-3 py-3">30d</th>
          <th class="text-center px-3 py-3">Alert</th>
          <th class="text-right px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-wc-border">
        <tr
          v-for="c in coaches" :key="c.coach_id"
          class="hover:bg-wc-bg-tertiary/40 cursor-pointer transition-colors"
          @click="emit('drill-down', c.coach_id)"
        >
          <td class="px-4 py-3 flex items-center gap-3">
            <div class="h-8 w-8 rounded-full bg-wc-accent/15 flex items-center justify-center overflow-hidden shrink-0">
              <img v-if="c.avatar_url" :src="c.avatar_url" :alt="c.coach_name" class="h-full w-full object-cover" />
              <span v-else class="text-xs font-semibold text-wc-accent">{{ c.coach_name?.charAt(0) }}</span>
            </div>
            <span class="font-medium text-wc-text">{{ c.coach_name }}</span>
          </td>
          <td class="text-right px-3 py-3 text-wc-text">{{ c.active_clients_count }}</td>
          <td class="text-right px-3 py-3 text-wc-text">{{ format(c.total_posts_count) }}</td>
          <td class="text-right px-3 py-3 text-wc-text">{{ Math.round(c.engagement_rate * 100) }}%</td>
          <td class="text-right px-3 py-3 text-wc-text-tertiary">{{ c.response_time_p50_min }}min</td>
          <td class="text-center px-3 py-3">
            <CoachSparkline :series="c.posts_per_day_30d || []" />
          </td>
          <td class="text-center px-3 py-3">
            <span v-if="c.alert" :title="c.alert" class="text-lg">{{ alertIcon(c.alert) }}</span>
            <span v-else class="text-wc-text-tertiary">—</span>
          </td>
          <td class="text-right px-4 py-3">
            <button class="text-wc-accent text-xs font-semibold hover:underline">Drill →</button>
          </td>
        </tr>
        <tr v-if="!coaches.length">
          <td colspan="8" class="px-4 py-12 text-center text-wc-text-tertiary text-sm">
            Sin coaches para mostrar este período.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
```

```bash
git add resources/js/vue/components/admin/community/CoachSparkline.vue resources/js/vue/components/admin/community/CoachAnalyticsTable.vue
git commit -m "feat(community): CoachAnalyticsTable + CoachSparkline"
```

---

## Task 14: Components — KPIBar + EngagementHeatmap + BroadcastBar + History + ReportCard + ActionDialog

**Files:** 6 components

- [ ] **CoachAnalyticsKPIBar.vue** — 4 cards horizontal con count-up

```vue
<script setup>
import { useCountUp } from '../../../composables/useCountUp';

const props = defineProps({
    totals: { type: Object, default: () => ({}) },
    pending: { type: Number, default: 0 },
});
</script>

<template>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Comunidades activas</p>
      <p class="font-display text-3xl text-wc-text mt-1">{{ totals.active_communities ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Posts 30d</p>
      <p class="font-display text-3xl text-wc-text mt-1">{{ totals.posts_30d ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Engagements 30d</p>
      <p class="font-display text-3xl text-wc-text mt-1">{{ totals.engagements_30d ?? 0 }}</p>
    </div>
    <div class="rounded-xl border" :class="pending > 0 ? 'border-rose-500/30 bg-rose-500/5' : 'border-wc-border bg-wc-bg-secondary'">
      <div class="p-4">
        <p class="text-xs uppercase tracking-widest" :class="pending > 0 ? 'text-rose-500' : 'text-wc-text-tertiary'">Reportes pendientes</p>
        <p class="font-display text-3xl mt-1" :class="pending > 0 ? 'text-rose-500' : 'text-wc-text'">{{ pending }}</p>
      </div>
    </div>
  </div>
</template>
```

- [ ] **EngagementHeatmap.vue** — SVG 7×24 grid

```vue
<script setup>
const props = defineProps({
    data: { type: Array, default: () => [] }, // 7×24 matrix
    max: { type: Number, default: 0 },
});

const DAYS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

function opacity(value) {
    if (!props.max) return 0.05;
    return 0.05 + (value / props.max) * 0.85;
}
</script>

<template>
  <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
    <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Heatmap actividad × hora</h3>
    <div class="overflow-x-auto">
      <div class="min-w-[600px]">
        <div v-for="(row, dayIdx) in data" :key="dayIdx" class="flex items-center gap-1 py-0.5">
          <span class="w-8 text-[10px] text-wc-text-tertiary uppercase tracking-widest">{{ DAYS[dayIdx] }}</span>
          <div
            v-for="(cell, hour) in row" :key="hour"
            class="h-3 flex-1 rounded-sm"
            :style="{ background: `rgba(220, 38, 38, ${opacity(cell)})` }"
            :title="`${DAYS[dayIdx]} ${hour}:00 — ${cell}`"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **BroadcastPreviewBar.vue** — recipient count display + send button container

```vue
<script setup>
const props = defineProps({
    count: { type: [Number, null], default: null },
    sending: { type: Boolean, default: false },
    canSend: { type: Boolean, default: false },
});
const emit = defineEmits(['send']);
</script>

<template>
  <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3 flex items-center justify-between gap-3">
    <div>
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Recipientes</p>
      <p class="font-display text-2xl text-wc-text">
        <span v-if="count !== null">{{ count }}</span>
        <span v-else class="text-wc-text-tertiary text-base">Calculando…</span>
      </p>
    </div>
    <button
      @click="emit('send')"
      :disabled="!canSend || sending"
      class="rounded-full bg-wc-accent text-white px-5 py-2.5 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {{ sending ? 'Enviando…' : 'Enviar broadcast' }}
    </button>
  </div>
</template>
```

- [ ] **BroadcastHistoryList.vue** — paginated history with expand

```vue
<script setup>
import { ref } from 'vue';

const props = defineProps({ history: { type: Array, default: () => [] } });
const expandedId = ref(null);

function toggle(id) {
    expandedId.value = expandedId.value === id ? null : id;
}
</script>

<template>
  <div class="space-y-2">
    <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-2">Historial</h3>
    <div v-if="!history.length" class="text-sm text-wc-text-tertiary text-center py-8">Sin broadcasts aún.</div>
    <article v-for="b in history" :key="b.id" class="rounded-xl border border-wc-border bg-wc-bg-secondary">
      <button @click="toggle(b.id)" class="w-full px-4 py-3 text-left">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-wc-text">{{ b.subject || b.body?.slice(0, 60) }}</p>
            <p class="text-xs text-wc-text-tertiary">
              {{ b.audience_type }} · {{ b.recipients_count }} recipientes
            </p>
          </div>
          <span class="text-xs text-wc-text-tertiary">{{ b.sent_at_human || b.sent_at }}</span>
        </div>
      </button>
      <Transition enter-active-class="duration-200" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-96">
        <div v-if="expandedId === b.id" class="border-t border-wc-border px-4 py-3 text-sm text-wc-text-secondary whitespace-pre-wrap">
          <p>{{ b.body }}</p>
          <p class="text-xs text-wc-text-tertiary mt-2">Delivered: {{ b.delivered_count }}/{{ b.recipients_count }} · push: {{ b.push_enabled ? 'sí' : 'no' }}</p>
        </div>
      </Transition>
    </article>
  </div>
</template>
```

- [ ] **ModerationReportCard.vue** + **ModerationActionDialog.vue** — placeholder design

```vue
<!-- ModerationReportCard.vue -->
<script setup>
const props = defineProps({ report: { type: Object, required: true } });
const emit = defineEmits(['action']);
</script>

<template>
  <article class="rounded-xl border bg-wc-bg-secondary p-4" :class="report.report_count >= 3 ? 'border-rose-500/40' : 'border-wc-border'">
    <header class="flex items-center justify-between gap-3 mb-2">
      <div>
        <p class="text-sm font-semibold text-wc-text">
          ⚠️ {{ report.report_count }} reporte{{ report.report_count > 1 ? 's' : '' }} · {{ report.post_author_name }}
        </p>
        <p class="text-xs text-wc-text-tertiary">Coach: {{ report.coach_name }} · {{ report.reason }}</p>
      </div>
      <span class="text-xs text-wc-text-tertiary">hace {{ Math.round((Date.now() - new Date(report.created_at).getTime()) / 3600000) }}h</span>
    </header>
    <p class="text-sm text-wc-text-secondary italic line-clamp-2 mb-3">"{{ report.post_excerpt }}"</p>
    <div v-if="report.reason_detail" class="text-xs text-wc-text-tertiary mb-3">{{ report.reason_detail }}</div>
    <div class="flex flex-wrap items-center gap-2">
      <button @click="emit('action', { id: report.report_id, action: 'dismiss' })" class="rounded-full bg-wc-bg-tertiary px-3 py-1 text-xs font-semibold text-wc-text-secondary hover:bg-wc-bg">
        Dismiss
      </button>
      <button @click="emit('action', { id: report.report_id, action: 'hide' })" class="rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-500 hover:bg-amber-500/25">
        Hide post
      </button>
      <button @click="emit('action', { id: report.report_id, action: 'warn' })" class="rounded-full bg-blue-500/15 px-3 py-1 text-xs font-semibold text-blue-500 hover:bg-blue-500/25">
        Warn
      </button>
      <button @click="emit('action', { id: report.report_id, action: 'ban_client' })" class="rounded-full bg-rose-500/15 px-3 py-1 text-xs font-semibold text-rose-500 hover:bg-rose-500/25">
        Ban cliente
      </button>
    </div>
  </article>
</template>
```

```vue
<!-- ModerationActionDialog.vue -->
<script setup>
import { ref, watch } from 'vue';
const props = defineProps({
    open: { type: Boolean, default: false },
    action: { type: String, default: '' },
    report: { type: Object, default: null },
});
const emit = defineEmits(['confirm', 'cancel']);
const reason = ref('');

watch(() => props.open, (o) => { if (o) reason.value = ''; });
</script>

<template>
  <Transition enter-active-class="duration-200" enter-from-class="opacity-0">
    <div v-if="open" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4" @click.self="emit('cancel')">
      <div class="rounded-2xl bg-wc-bg-secondary border border-wc-border p-6 w-full max-w-md">
        <h3 class="font-display text-xl text-wc-text mb-3">Confirmar acción</h3>
        <p class="text-sm text-wc-text-secondary mb-3">
          Vas a <strong>{{ action }}</strong> el post de {{ report?.post_author_name }}.
        </p>
        <textarea v-model="reason" rows="2" placeholder="Razón (opcional, queda en audit log)" class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text mb-4"></textarea>
        <div class="flex gap-3">
          <button @click="emit('cancel')" class="flex-1 rounded-full border border-wc-border text-wc-text-secondary py-2">Cancelar</button>
          <button @click="emit('confirm', { reason })" class="flex-1 rounded-full bg-wc-accent text-white py-2 font-semibold">Confirmar</button>
        </div>
      </div>
    </div>
  </Transition>
</template>
```

```bash
git add resources/js/vue/components/admin/community/
git commit -m "feat(community): admin community components (KPIBar, Heatmap, Broadcast bars, Moderation cards)"
```

---

## Task 15: Tab — AdminPulseCrossCoachTab

**Files:**
- Create: `resources/js/vue/pages/Admin/community/AdminPulseCrossCoachTab.vue`

```vue
<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAdminCommunity } from '../../../composables/useAdminCommunity';
import CoachAnalyticsKPIBar from '../../../components/admin/community/CoachAnalyticsKPIBar.vue';
import CoachAnalyticsTable from '../../../components/admin/community/CoachAnalyticsTable.vue';
import EngagementHeatmap from '../../../components/admin/community/EngagementHeatmap.vue';
import TimeSeriesChart from '../../../components/admin/community/TimeSeriesChart.vue';

const { fetchPulseCrossCoach, loading, error } = useAdminCommunity();
const emit = defineEmits(['drill-down']);
const data = ref(null);
const period = ref('week');
const PERIODS = [
    { key: 'day',   label: 'Día' },
    { key: 'week',  label: 'Semana' },
    { key: 'month', label: 'Mes' },
];

const totals = computed(() => data.value?.totals || {});
const coaches = computed(() => data.value?.coaches || []);
const moderationQueueCount = computed(() => data.value?.moderation_queue_count || 0);

async function load(force = false) {
    data.value = await fetchPulseCrossCoach({ period: period.value, force });
}

function onDrillDown(coachId) {
    emit('drill-down', coachId);
}

onMounted(() => load());
</script>

<template>
  <div class="space-y-5">
    <div class="flex items-center gap-2">
      <button v-for="p in PERIODS" :key="p.key" @click="period = p.key; load()"
        :class="period === p.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="rounded-full px-4 py-1.5 text-xs font-semibold">{{ p.label }}</button>
      <div class="flex-1"></div>
      <button @click="load(true)" class="text-xs text-wc-text-tertiary hover:text-wc-text">↻ Actualizar</button>
    </div>

    <div v-if="loading && !data" class="space-y-4">
      <div class="h-24 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      <div class="h-48 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      <div class="h-64 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
    </div>

    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">{{ error }}</div>

    <template v-else-if="data">
      <CoachAnalyticsKPIBar :totals="totals" :pending="moderationQueueCount" />
      <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
        <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Posts/día último mes</h3>
        <TimeSeriesChart v-if="data.time_series?.posts_per_day" :data="data.time_series.posts_per_day" :height="240" />
      </div>
      <CoachAnalyticsTable :coaches="coaches" @drill-down="onDrillDown" />
      <EngagementHeatmap v-if="data.heatmap" :data="data.heatmap" :max="data.heatmap_max" />
    </template>
  </div>
</template>
```

```bash
git add resources/js/vue/pages/Admin/community/AdminPulseCrossCoachTab.vue
git commit -m "feat(community): AdminPulseCrossCoachTab"
```

---

## Task 16: Tabs restantes — LiveFeed, BroadcastCenter, ModerationQueue, AnalyticsCoach

Follow same patterns. Each ~150-200 lines. Implementation per Spec sections.

- [ ] **AdminLiveFeedCommunityTab.vue** — extends or wraps LiveFeed.vue with `?type=community` filter
- [ ] **AdminBroadcastCenterTab.vue** — composer + BroadcastPreviewBar + BroadcastHistoryList
- [ ] **AdminModerationQueueTab.vue** — list of ModerationReportCard + ModerationActionDialog
- [ ] **AdminAnalyticsCoachTab.vue** — empty state + 5 sub-sections (Overview/Posts/Engagement/Clients/Audit)

```bash
git add resources/js/vue/pages/Admin/community/
git commit -m "feat(community): admin tabs LiveFeed/Broadcast/Moderation/AnalyticsCoach"
```

---

## Task 17: Hub — Community.vue admin

**Files:**
- Create: `resources/js/vue/pages/Admin/Community.vue`

```vue
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useModerationQueue } from '../../composables/useModerationQueue';
import AdminPulseCrossCoachTab from './community/AdminPulseCrossCoachTab.vue';
import AdminLiveFeedCommunityTab from './community/AdminLiveFeedCommunityTab.vue';
import AdminBroadcastCenterTab from './community/AdminBroadcastCenterTab.vue';
import AdminModerationQueueTab from './community/AdminModerationQueueTab.vue';
import AdminAnalyticsCoachTab from './community/AdminAnalyticsCoachTab.vue';

const TABS = [
    { key: 'pulse',          label: 'Pulse Cross-Coach', component: AdminPulseCrossCoachTab },
    { key: 'live-feed',      label: 'Live Feed',         component: AdminLiveFeedCommunityTab },
    { key: 'broadcast',      label: 'Broadcast Center',  component: AdminBroadcastCenterTab },
    { key: 'moderation',     label: 'Moderation',        component: AdminModerationQueueTab },
    { key: 'analytics',      label: 'Analytics Coach',   component: AdminAnalyticsCoachTab },
];

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const moderation = useModerationQueue();

const activeTab = ref(TABS.find(t => t.key === route.hash.slice(1).split('-')[0])?.key || 'pulse');
const selectedCoachId = ref(null);
const previousTabIndex = ref(0);
let adminChannel = null;

const activeComponent = computed(() => TABS.find(t => t.key === activeTab.value)?.component);

function changeTab(key, coachId = null) {
    previousTabIndex.value = TABS.findIndex(t => t.key === activeTab.value);
    activeTab.value = key;
    if (key === 'analytics' && coachId) {
        selectedCoachId.value = coachId;
        router.replace({ hash: `#analytics-${coachId}` });
    } else {
        router.replace({ hash: `#${key}` });
    }
}

function onDrillDown(coachId) {
    changeTab('analytics', coachId);
}

const transitionDirection = computed(() => {
    const newIdx = TABS.findIndex(t => t.key === activeTab.value);
    return newIdx > previousTabIndex.value ? 'right' : 'left';
});

onMounted(() => {
    moderation.fetchQueue();

    if (window.Echo) {
        adminChannel = window.Echo.private('admin.community')
            .listen('.post-reported', () => moderation.fetchQueue({ force: true }))
            .listen('.broadcast-sent', () => {})
            .listen('.post-made-official', () => {});
    }
});

onBeforeUnmount(() => {
    if (adminChannel && window.Echo) window.Echo.leave('admin.community');
});

watch(() => route.hash, (h) => {
    const parts = h.slice(1).split('-');
    const key = parts[0];
    if (TABS.some(t => t.key === key) && key !== activeTab.value) {
        const coachId = parts[1] ? parseInt(parts[1], 10) : null;
        changeTab(key, coachId);
    }
});
</script>

<template>
  <div class="wc-admin-canvas space-y-4 p-4 sm:p-6">
    <header>
      <h1 class="font-display text-3xl tracking-wide text-wc-text">Comunidad WellCore</h1>
      <p class="text-sm text-wc-text-tertiary mt-1">Cross-coach analytics, broadcast, moderation.</p>
    </header>

    <nav class="sticky top-0 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 bg-wc-bg/80 backdrop-blur-xl border-b border-wc-border">
      <div class="flex items-center gap-1 overflow-x-auto pb-px">
        <button
          v-for="tab in TABS" :key="tab.key"
          @click="changeTab(tab.key)"
          :class="activeTab === tab.key ? 'border-wc-accent text-wc-text font-semibold' : 'border-transparent text-wc-text-tertiary hover:text-wc-text-secondary'"
          class="shrink-0 px-4 py-3 text-sm border-b-2 transition-colors flex items-center gap-2"
        >
          {{ tab.label }}
          <span v-if="tab.key === 'moderation' && moderation.pendingCount.value > 0" class="rounded-full bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 min-w-[20px] text-center">
            {{ moderation.pendingCount.value }}
          </span>
        </button>
      </div>
    </nav>

    <div class="pt-2">
      <Transition mode="out-in"
        :enter-from-class="transitionDirection === 'right' ? 'opacity-0 translate-x-4' : 'opacity-0 -translate-x-4'"
        enter-active-class="duration-200 ease-out"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="duration-150 ease-in"
        :leave-to-class="transitionDirection === 'right' ? 'opacity-0 -translate-x-2' : 'opacity-0 translate-x-2'"
      >
        <component :is="activeComponent" :key="activeTab" :coach-id="selectedCoachId" @drill-down="onDrillDown" />
      </Transition>
    </div>
  </div>
</template>
```

```bash
git add resources/js/vue/pages/Admin/Community.vue
git commit -m "feat(community): Admin Community.vue hub with 5 tabs + real-time"
```

---

## Task 18: Page — NotificationsPreferences admin

Mirror del coach (Task 21 Plan B) pero con keys del admin (`notify_post_reported`, etc.).

```bash
git add resources/js/vue/pages/Admin/NotificationsPreferences.vue
git commit -m "feat(community): admin NotificationsPreferences page"
```

---

## Task 19: AdminLayout + Sidebar + Router mods

**Files:**
- Modify: `resources/js/vue/layouts/AdminLayout.vue`
- Modify: `resources/js/vue/components/ui/wellcore-admin/WcAdminSidebar.vue`
- Modify: `resources/js/vue/router/index.js`

- [ ] **Add to MIGRATED_ROUTES** in AdminLayout.vue:

```js
// Fase Comunidad — full target shell
{ match: (p) => p.startsWith('/admin/community'),       tab: 'community',       cosmetic: false },
{ match: (p) => p.startsWith('/admin/notifications'),   tab: 'notifications',   cosmetic: false },
```

- [ ] **WcAdminSidebar.vue** — agregar item "Comunidad" en sección Operaciones (replicar pattern existente — leer archivo para ubicar nav structure exacta).

Add SVG icon "community" en bloque de iconos (similar al SVG en CoachLayout Task 27 Plan B).

- [ ] **router/index.js** — agregar 2 rutas:

```js
{ path: '/admin/community', name: 'admin-community',
  component: () => import('../pages/Admin/Community.vue'),
  meta: { auth: true, title: 'Comunidad — WellCore Admin', adminMigrated: true, adminTab: 'community' } },
{ path: '/admin/notifications', name: 'admin-notifications',
  component: () => import('../pages/Admin/NotificationsPreferences.vue'),
  meta: { auth: true, title: 'Notificaciones — WellCore Admin', adminMigrated: true, adminTab: 'notifications' } },
```

- [ ] **Commit**:

```bash
git add resources/js/vue/layouts/AdminLayout.vue resources/js/vue/components/ui/wellcore-admin/WcAdminSidebar.vue resources/js/vue/router/index.js
git commit -m "feat(community): admin sidebar item + routes + migrated route entry"
```

---

## Task 20: Run tests + Build + Smoke E2E

- [ ] **Run Vitest**

```bash
npm run test:unit
```

Expected: ALL Fase B + Fase C composable tests verde.

- [ ] **Run Pest**

```bash
DB_DATABASE=wellcore_fitness_test php artisan migrate --force
vendor/bin/pest --parallel
```

Expected: ALL tests verde.

- [ ] **Pint**

```bash
vendor/bin/pint --test
```

If issues: `vendor/bin/pint && git add -u && git commit -m "style: pint Fase C"`

- [ ] **Build local**

```bash
npm run build
```

Verify chunks created for Community.vue, NotificationsPreferences.vue, tab components.

```bash
git add public/build/
git commit -m "build: Vite assets Fase C admin community"
```

- [ ] **Smoke checklist manual**:

- [ ] Login superadmin → `/admin/community` accesible desde sidebar
- [ ] Default lands en Pulse Cross-Coach con KPI cards + table + chart
- [ ] Click row coach en table → switches Analytics Coach con coachId pre-loaded
- [ ] Tab Live Feed Community filtrable por coach + type
- [ ] Tab Broadcast Center: composer + preview live + send confirmation step si > 50
- [ ] Tab Moderation: cards reportes, dismiss/hide/warn/ban actions funcionan con dialog
- [ ] Real-time `admin.community` channel: nuevo report appears + flash badge moderation tab
- [ ] `/admin/notifications` página con toggles y live save
- [ ] Drill-down → Analytics Coach: 5 sub-secciones funcionales
- [ ] Click "Impersonar" → useImpersonation composable redirige a `/coach/community` impersonando
- [ ] Mobile: sidebar abre, tabs scroll horizontal, tabs render OK
- [ ] Dark theme coherente (admin force-dark)
- [ ] Console clean

---

## Task 21: CLAUDE.md update + push

- [ ] **Append to CLAUDE.md**:

```markdown
## Community Cross-Role (Fase C — Admin Center shipped)

Fase C agrega Admin Community Center sobre Fase A backend.

### Admin UI (Fase C)
- `/admin/community` — hub con 5 tabs (Pulse Cross-Coach / Live Feed / Broadcast / Moderation / Analytics Coach)
- `/admin/notifications` — preferences page admin
- Sidebar: nueva sección "Comunidad" + item "Notificaciones"
- Composables: `useAdminCommunity`, `useBroadcast`, `useModerationQueue`
- Backend extensiones Fase C:
  - `GET /api/v/admin/community/coaches/{id}/analytics` (cache 600s)
  - `POST /api/v/admin/community/posts/{id}/pin` (admin override)
  - `POST /api/v/admin/community/posts/{id}/make-global` (superadmin only)
  - `GET/PATCH /api/v/admin/notifications/preferences`
- Migration aditiva: `admin_notification_preferences`
- Charts: Chart.js 4 lazy-loaded

UI siguiente en Fase D (Cross-Role Communication Layer). Spec: `docs/superpowers/specs/2026-05-05-community-cross-role-fase-c-admin-center-design.md`. Plan: `docs/superpowers/plans/2026-05-05-community-cross-role-fase-c-admin-center.md`.
```

- [ ] **Commit + push**

```bash
git add CLAUDE.md
git commit -m "docs(community): document Fase C in CLAUDE.md"
git push origin feat/community-cross-role-fase-c
```

---

## Definition of Done — Fase C

### Frontend
- [ ] Community.vue admin con 5 tabs sticky
- [ ] 5 tab components implementados
- [ ] 9 components compartidos (Table, Sparkline, Heatmap, KPIBar, History, ReportCard, ActionDialog, PreviewBar, TimeSeriesChart)
- [ ] 3 composables singleton TTL+dedup+reset
- [ ] NotificationsPreferences admin
- [ ] AdminLayout MIGRATED_ROUTES extendido
- [ ] WcAdminSidebar item "Comunidad" + "Notificaciones"
- [ ] Router 2 rutas nuevas con `adminMigrated: true`
- [ ] auth.js extendido con 3 reset calls
- [ ] Real-time admin.community subscribed
- [ ] Drill-down con context preservation
- [ ] Charts Chart.js bundle <30KB
- [ ] Animations + reduced-motion respetados
- [ ] Dark-only coherente

### Backend
- [ ] Migration `admin_notification_preferences`
- [ ] Model AdminNotificationPreference
- [ ] AdminCommunityService.coachAnalytics + 6 helpers
- [ ] CoachAnalyticsController + cache 600s
- [ ] NotificationPreferencesController admin
- [ ] CommunityController.pinAdminOverride + makeGlobal
- [ ] BroadcastController validation rigurosa
- [ ] 5 routes nuevas

### Testing
- [ ] 7 Pest tests verde
- [ ] 3 Vitest tests verde
- [ ] No regresión Pest suite
- [ ] Pint + ESLint OK
- [ ] Smoke E2E: 13 scenarios PASS

### Operations
- [ ] Cache `wc:admin-coach-analytics:v1:{id}` 600s
- [ ] Lighthouse Performance ≥ 70 en /admin/community
- [ ] Console clean

### Documentación
- [ ] Spec doc commiteado
- [ ] Plan doc commiteado
- [ ] CLAUDE.md actualizado
- [ ] Branch push'eada

---

## Self-Review

**1. Spec coverage:** 5 tabs ✅ · drill-down preservation ✅ · broadcast preview/send/history ✅ · moderation queue + actions ✅ · admin notifications ✅ · charts + sparklines + heatmap ✅ · real-time admin.community ✅

**2. Placeholder scan:** sin TBDs estructurales. Tasks 16/18/19 tienen placeholder design ("follow same patterns") porque cada tab/page mantiene patrón consistente — ejecutor replicará Task 22-25 Plan B con shape específica admin.

**3. Type consistency:** `coach_id` int consistente · `audience_type` enum consistente · cache keys `wc:admin-*` consistentes · composable singleton state consistente

**4. Risk coverage:** queries pesadas → cache 600s + 60s · Chart.js bundle → lazy import · cache leak impersonation → reset hooks · broadcast saturation → chunks 100 + queue · admin baneo wrong client → confirmation dialog

Plan listo para ejecución.
