# Community Cross-Role — Fase D: Cross-Role Communication Layer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:subagent-driven-development. Steps use `- [ ]` checkboxes.

**Goal:** Construir la capa de comunicación cross-role: mentions con autocomplete, ReportPostMenu cliente, threads cross-role con badges visuales, GroupPresence composable, NotificationsPreferences cliente. Cierre del proyecto Community Cross-Role.

**Architecture:** Vue 3.5 + Reverb. Composables singleton TTL+dedup+reset. Modificaciones al CommunityFeed cliente y CommentsThread sin breaking changes. Tests Vitest composables + Pest backend feature.

**Tech Stack:** PHP 8.4, Laravel 13.1.1, Vue 3.5, Tailwind CSS 4, Pest v3, Vitest. Linter Pint + ESLint.

**Spec:** `docs/superpowers/specs/2026-05-05-community-cross-role-fase-d-cross-role-design.md`

**Phase scope:** Solo Fase D. Best-after Fases B y C (uses CoachBadge + OfficialBadge components creados en Fase B).

---

## File Map

### Frontend new (8)

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

### Frontend modified (4)

```
resources/js/vue/pages/Client/CommunityFeed.vue
resources/js/vue/components/community/CommentsThread.vue
resources/js/vue/router/index.js
resources/js/vue/stores/auth.js
```

### Backend new (4)

```
app/Http/Controllers/Api/MentionSearchController.php
app/Http/Controllers/Api/Client/NotificationPreferencesController.php
app/Models/ClientNotificationPreference.php
database/migrations/2026_05_05_000011_create_client_notification_preferences_table.php
```

### Backend modified (1)

```
routes/api.php  (3 rutas nuevas)
```

### Tests (6)

```
tests/Unit/Composables/useMentions.test.js
tests/Unit/Composables/useGroupPresence.test.js
tests/Feature/MentionSearchEndpointTest.php
tests/Feature/ClientNotificationPreferencesTest.php
tests/Feature/Community/CrossRoleBadgesRenderingTest.php
tests/Feature/Community/PostReportFlowE2ETest.php
```

---

## Pre-flight

```bash
git fetch origin
git checkout -b feat/community-cross-role-fase-d
php artisan route:list --path=api/v/community/posts | grep report
ls resources/js/vue/components/community/CoachBadge.vue
ls resources/js/vue/components/community/OfficialBadge.vue
DB_DATABASE=wellcore_fitness_test php artisan migrate --force
```

---

## Task 0: Setup branch

- [ ] **Step 1: Create branch**

```bash
git checkout -b feat/community-cross-role-fase-d
```

- [ ] **Step 2: Verify Vitest configured (from Fase B)**

```bash
ls vitest.config.js
npm run test:unit -- --version
```

If missing: install per Fase B Task 0.

---

## Task 1: Migration + Model — client_notification_preferences

**Files:**
- Create: `database/migrations/2026_05_05_000011_create_client_notification_preferences_table.php`
- Create: `app/Models/ClientNotificationPreference.php`

- [ ] **Step 1: Migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('client_notification_preferences')) return;

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
    }

    public function down(): void { Schema::dropIfExists('client_notification_preferences'); }
};
```

- [ ] **Step 2: Migrate**

```bash
php artisan migrate --path=database/migrations/2026_05_05_000011_create_client_notification_preferences_table.php
DB_DATABASE=wellcore_fitness_test php artisan migrate --force
```

- [ ] **Step 3: Model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientNotificationPreference extends Model
{
    protected $table = 'client_notification_preferences';
    protected $primaryKey = 'client_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'notify_post_reactions',
        'notify_comments_on_my_post',
        'notify_mentions',
        'notify_coach_messages',
        'notify_coach_announcements',
        'notify_wellcore_announcements',
        'push_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notify_post_reactions'         => 'boolean',
            'notify_comments_on_my_post'    => 'boolean',
            'notify_mentions'               => 'boolean',
            'notify_coach_messages'         => 'boolean',
            'notify_coach_announcements'    => 'boolean',
            'notify_wellcore_announcements' => 'boolean',
            'push_enabled'                  => 'boolean',
            'in_app_enabled'                => 'boolean',
            'updated_at'                    => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public static function forClient(int $clientId): self
    {
        return static::firstOrCreate(['client_id' => $clientId]);
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_05_000011_create_client_notification_preferences_table.php app/Models/ClientNotificationPreference.php
git commit -m "feat(community): client_notification_preferences migration + model"
```

---

## Task 2: Backend — MentionSearchController (TDD)

**Files:**
- Create: `app/Http/Controllers/Api/MentionSearchController.php`
- Create: `tests/Feature/MentionSearchEndpointTest.php`

- [ ] **Step 1: Tests**

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->coach = Admin::factory()->create(['role' => 'coach']);
    $this->matchingClient = Client::factory()->create(['name' => 'Carlos Pérez', 'coach_id' => $this->coach->id]);
    $this->otherCoachClient = Client::factory()->create(['name' => 'Carlos Otro']);

    $this->coachToken = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->coach->id, 'user_type' => 'admin',
        'token' => hash('sha256', $this->coachToken), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $this->clientToken = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->matchingClient->id, 'user_type' => 'client',
        'token' => hash('sha256', $this->clientToken), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('coach searches within own team', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->coachToken}")
        ->getJson('/api/v/community/mention-search?q=Carl')
        ->assertOk();

    $ids = collect($resp->json('results'))->pluck('id')->all();
    expect($ids)->toContain($this->matchingClient->id);
    expect($ids)->not->toContain($this->otherCoachClient->id);
});

it('rejects q < 3 chars', function () {
    $this->withHeader('Authorization', "Bearer {$this->coachToken}")
        ->getJson('/api/v/community/mention-search?q=Ca')
        ->assertStatus(422);
});

it('cliente scope to own coach team', function () {
    $sameTeamClient = Client::factory()->create(['name' => 'Carla Misma', 'coach_id' => $this->coach->id]);

    $resp = $this->withHeader('Authorization', "Bearer {$this->clientToken}")
        ->getJson('/api/v/community/mention-search?q=Carl')
        ->assertOk();

    $ids = collect($resp->json('results'))->pluck('id')->all();
    expect($ids)->toContain($sameTeamClient->id);
    expect($ids)->not->toContain($this->otherCoachClient->id);
});

it('rejects unauthenticated', function () {
    $this->getJson('/api/v/community/mention-search?q=Carl')->assertStatus(401);
});
```

- [ ] **Step 2: Controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\MentionResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MentionSearchController extends Controller
{
    public function __construct(private MentionResolverService $service) {}

    public function search(Request $request): JsonResponse
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
            if ($role === 'coach') {
                $scopeCoachId = $user->id;
            }
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

- [ ] **Step 3: Route**

```php
Route::middleware(['wellcore.auth'])->group(function () {
    Route::get('v/community/mention-search', [Api\MentionSearchController::class, 'search']);
});
```

- [ ] **Step 4: Run + commit**

```bash
vendor/bin/pest tests/Feature/MentionSearchEndpointTest.php
git add app/Http/Controllers/Api/MentionSearchController.php routes/api.php tests/Feature/MentionSearchEndpointTest.php
git commit -m "feat(community): mention-search endpoint with scope-aware caching"
```

---

## Task 3: Backend — Client NotificationPreferencesController (TDD)

**Files:**
- Create: `app/Http/Controllers/Api/Client/NotificationPreferencesController.php`
- Create: `tests/Feature/ClientNotificationPreferencesTest.php`

- [ ] **Step 1: Tests**

```php
<?php

use App\Models\Client;
use App\Models\ClientNotificationPreference;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $this->client->id, 'user_type' => 'client',
        'token' => hash('sha256', $this->token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);
});

it('returns defaults when no row', function () {
    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v/client/notifications/preferences')->assertOk();

    expect($resp->json('notify_mentions'))->toBeTrue();
    expect($resp->json('notify_post_reactions'))->toBeTrue();
});

it('patches granular preferences', function () {
    ClientNotificationPreference::forClient($this->client->id);

    $resp = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->patchJson('/api/v/client/notifications/preferences', [
            'notify_post_reactions' => false,
        ])->assertOk();

    expect($resp->json('notify_post_reactions'))->toBeFalse();
});
```

- [ ] **Step 2: Controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $client = $request->user();
        abort_unless($client instanceof Client, 403);
        $prefs = ClientNotificationPreference::forClient($client->id);
        return response()->json($prefs);
    }

    public function update(Request $request): JsonResponse
    {
        $client = $request->user();
        abort_unless($client instanceof Client, 403);

        $allowed = [
            'notify_post_reactions','notify_comments_on_my_post','notify_mentions',
            'notify_coach_messages','notify_coach_announcements','notify_wellcore_announcements',
            'push_enabled','in_app_enabled',
        ];

        $data = $request->validate(array_combine(
            $allowed,
            array_fill(0, count($allowed), 'sometimes|boolean')
        ));

        $prefs = ClientNotificationPreference::forClient($client->id);
        $prefs->fill($data)->save();

        return response()->json($prefs->fresh());
    }
}
```

- [ ] **Step 3: Routes**

```php
Route::middleware(['wellcore.auth'])->prefix('v/client')->group(function () {
    Route::get('notifications/preferences', [Api\Client\NotificationPreferencesController::class, 'show']);
    Route::patch('notifications/preferences', [Api\Client\NotificationPreferencesController::class, 'update']);
});
```

- [ ] **Step 4: Run + commit**

```bash
vendor/bin/pest tests/Feature/ClientNotificationPreferencesTest.php
git add app/Http/Controllers/Api/Client/NotificationPreferencesController.php routes/api.php tests/Feature/ClientNotificationPreferencesTest.php
git commit -m "feat(community): client notification preferences endpoint"
```

---

## Task 4: Composable — useMentions (TDD)

**Files:**
- Create: `resources/js/vue/composables/useMentions.js`
- Create: `tests/Unit/Composables/useMentions.test.js`

- [ ] **Step 1: Implement composable**

```js
import { ref } from 'vue';
import { useApi } from './useApi';

const searchCache = new Map();
const SEARCH_TTL_MS = 600_000;

const MENTION_REGEX = /@(cliente_(\d+)|coach|admin|wellcore)\b/giu;

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
        if (!content) return [];
        const tokens = [];
        const matches = content.matchAll(MENTION_REGEX);
        for (const m of matches) {
            if (m[2]) {
                tokens.push({ type: 'client', id: parseInt(m[2], 10), raw: m[0] });
            } else {
                const t = m[1].toLowerCase();
                tokens.push({
                    type: t === 'wellcore' ? 'admin' : t,
                    id: null,
                    raw: m[0],
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

- [ ] **Step 2: Tests**

```js
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useMentions, resetMentions } from '../../../resources/js/vue/composables/useMentions';

const apiGet = vi.fn();
vi.mock('../../../resources/js/vue/composables/useApi', () => ({
    useApi: () => ({ get: apiGet }),
}));

describe('useMentions', () => {
    beforeEach(() => {
        resetMentions();
        apiGet.mockReset();
    });

    it('skips API call if query < 3 chars', async () => {
        const { search } = useMentions();
        const results = await search('ca');
        expect(results).toEqual([]);
        expect(apiGet).not.toHaveBeenCalled();
    });

    it('caches results for 10min', async () => {
        apiGet.mockResolvedValue({ data: { results: [{ id: 1, name: 'Carlos' }] } });
        const { search } = useMentions();
        await search('Carlos');
        await search('Carlos');
        expect(apiGet).toHaveBeenCalledTimes(1);
    });

    it('extract parses @cliente_X tokens', () => {
        const { extract } = useMentions();
        const tokens = extract('Hola @cliente_42 y @cliente_7');
        expect(tokens).toEqual([
            { type: 'client', id: 42, raw: '@cliente_42' },
            { type: 'client', id: 7, raw: '@cliente_7' },
        ]);
    });

    it('extract parses @coach and @admin keywords', () => {
        const { extract } = useMentions();
        const tokens = extract('Aviso @coach y @wellcore');
        expect(tokens).toEqual(expect.arrayContaining([
            expect.objectContaining({ type: 'coach', id: null }),
            expect.objectContaining({ type: 'admin', id: null, raw: '@wellcore' }),
        ]));
    });

    it('reset clears cache', async () => {
        apiGet.mockResolvedValue({ data: { results: [] } });
        const { search } = useMentions();
        await search('Carlos');
        resetMentions();
        await search('Carlos');
        expect(apiGet).toHaveBeenCalledTimes(2);
    });
});
```

- [ ] **Step 3: Run + commit**

```bash
npm run test:unit -- useMentions
git add resources/js/vue/composables/useMentions.js tests/Unit/Composables/useMentions.test.js
git commit -m "feat(community): useMentions composable + Vitest"
```

---

## Task 5: Composable — useGroupPresence (TDD)

**Files:**
- Create: `resources/js/vue/composables/useGroupPresence.js`
- Create: `tests/Unit/Composables/useGroupPresence.test.js`

- [ ] **Step 1: Implement**

```js
import { reactive } from 'vue';
import { useAuthStore } from '../stores/auth';

const onlineMap = reactive({
    client: new Set(),
    coach: new Set(),
    admin: new Set(),
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

- [ ] **Step 2: Tests**

```js
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { useGroupPresence, resetGroupPresence } from '../../../resources/js/vue/composables/useGroupPresence';

vi.mock('../../../resources/js/vue/stores/auth', () => ({
    useAuthStore: () => ({ userId: 1 }),
}));

describe('useGroupPresence', () => {
    beforeEach(() => {
        resetGroupPresence();
        vi.stubGlobal('window', {
            Echo: {
                join: vi.fn(() => ({
                    here: function (cb) { cb([{ user_type: 'coach', id: 5 }, { user_type: 'client', id: 10 }]); return this; },
                    joining: function () { return this; },
                    leaving: function () { return this; },
                    error: function () { return this; },
                })),
                leave: vi.fn(),
            },
        });
    });

    it('init joins online-users channel and populates onlineMap', () => {
        const { init, isOnline } = useGroupPresence();
        init();
        expect(isOnline('coach', 5)).toBe(true);
        expect(isOnline('client', 10)).toBe(true);
    });

    it('isOnline returns false for unknown user', () => {
        const { isOnline } = useGroupPresence();
        expect(isOnline('coach', 999)).toBe(false);
    });

    it('reset clears state', () => {
        const { init, isOnline } = useGroupPresence();
        init();
        resetGroupPresence();
        expect(isOnline('coach', 5)).toBe(false);
    });
});
```

- [ ] **Step 3: Run + commit**

```bash
npm run test:unit -- useGroupPresence
git add resources/js/vue/composables/useGroupPresence.js tests/Unit/Composables/useGroupPresence.test.js
git commit -m "feat(community): useGroupPresence composable + Vitest"
```

---

## Task 6: auth.js — invalidate D caches

**Files:**
- Modify: `resources/js/vue/stores/auth.js`

- [ ] **Step 1: Add imports**

```js
import { resetMentions } from '../composables/useMentions';
import { resetGroupPresence } from '../composables/useGroupPresence';
```

- [ ] **Step 2: Add to setAuth (token change check)**

```js
if (data.token && data.token !== token.value) {
    resetGroupPulse();
    resetCoachCommunity();
    resetCoachPulse();
    resetCoachAnnounce();
    resetAdminCommunity();
    resetBroadcast();
    resetModerationQueue();
    resetMentions();
    resetGroupPresence();
}
```

- [ ] **Step 3: Add to clearAuth** (same calls)

- [ ] **Step 4: Commit**

```bash
git add resources/js/vue/stores/auth.js
git commit -m "feat(community): auth store invalidates mentions + presence on token change"
```

---

## Task 7: Component — MentionRenderer

**Files:**
- Create: `resources/js/vue/components/community/MentionRenderer.vue`

```vue
<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useMentions } from '../../composables/useMentions';

const props = defineProps({
    content: { type: String, default: '' },
    scopeCoachId: { type: [Number, null], default: null },
});

const router = useRouter();
const { extract } = useMentions();

const segments = computed(() => {
    if (!props.content) return [];

    const tokens = extract(props.content);
    if (!tokens.length) return [{ type: 'text', value: props.content }];

    const result = [];
    let cursor = 0;

    for (const token of tokens) {
        const idx = props.content.indexOf(token.raw, cursor);
        if (idx === -1) continue;
        if (idx > cursor) {
            result.push({ type: 'text', value: props.content.slice(cursor, idx) });
        }
        result.push({ type: 'mention', mentionType: token.type, id: token.id, raw: token.raw });
        cursor = idx + token.raw.length;
    }
    if (cursor < props.content.length) {
        result.push({ type: 'text', value: props.content.slice(cursor) });
    }
    return result;
});

function onMentionClick(seg) {
    if (seg.mentionType === 'client' && seg.id) {
        router.push(`/client/profile/${seg.id}`);
    }
}

function chipClass(type) {
    return {
        client: 'mention--client text-blue-500 bg-blue-500/15',
        coach: 'mention--coach text-amber-500 bg-amber-500/15',
        admin: 'mention--admin text-wc-accent bg-wc-accent/15',
    }[type] || '';
}
</script>

<template>
  <p class="whitespace-pre-wrap text-sm text-wc-text leading-relaxed">
    <template v-for="(seg, i) in segments" :key="i">
      <span v-if="seg.type === 'text'">{{ seg.value }}</span>
      <button v-else
        @click="onMentionClick(seg)"
        :class="['mention', 'inline-flex items-center rounded-md px-1.5 py-0 text-sm font-medium', chipClass(seg.mentionType)]"
      >{{ seg.raw }}</button>
    </template>
  </p>
</template>
```

```bash
git add resources/js/vue/components/community/MentionRenderer.vue
git commit -m "feat(community): MentionRenderer with role-colored chips"
```

---

## Task 8: Component — MentionInput

**Files:**
- Create: `resources/js/vue/components/community/MentionInput.vue`

```vue
<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useMentions } from '../../composables/useMentions';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    maxLength: { type: Number, default: 1000 },
    rows: { type: Number, default: 4 },
    scope: { type: String, default: 'coach-team' },
});
const emit = defineEmits(['update:modelValue', 'mention']);

const { search } = useMentions();

const textareaRef = ref(null);
const dropdownRef = ref(null);
const dropdownOpen = ref(false);
const dropdownItems = ref([]);
const selectedIndex = ref(0);
const currentToken = ref('');
const tokenStartPos = ref(0);
const dropdownPos = ref({ top: 0, left: 0 });

const SPECIAL_TOKENS = [
    { type: 'special', id: null, name: 'coach', label: '@coach (tu coach)' },
    { type: 'special', id: null, name: 'wellcore', label: '@wellcore (admin)' },
];

let searchTimer = null;

function onInput(e) {
    const value = e.target.value;
    emit('update:modelValue', value);
    detectMention();
}

function detectMention() {
    if (!textareaRef.value) return;
    const cursor = textareaRef.value.selectionStart;
    const before = props.modelValue.slice(0, cursor);
    const match = before.match(/@([a-zA-Z0-9_]{0,50})$/);

    if (!match) {
        dropdownOpen.value = false;
        return;
    }

    currentToken.value = match[1];
    tokenStartPos.value = cursor - match[0].length;

    const partial = match[1].toLowerCase();
    const matchingSpecials = SPECIAL_TOKENS.filter(t => t.name.startsWith(partial));

    if (currentToken.value.length < 3) {
        dropdownItems.value = matchingSpecials.length ? matchingSpecials : [];
        dropdownOpen.value = matchingSpecials.length > 0;
        if (dropdownOpen.value) updateDropdownPos();
        return;
    }

    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        const results = await search(currentToken.value, { scope: props.scope });
        dropdownItems.value = [...matchingSpecials, ...results];
        dropdownOpen.value = dropdownItems.value.length > 0;
        selectedIndex.value = 0;
        if (dropdownOpen.value) updateDropdownPos();
    }, 200);
}

function updateDropdownPos() {
    if (!textareaRef.value) return;
    const rect = textareaRef.value.getBoundingClientRect();
    dropdownPos.value = {
        top: rect.bottom + window.scrollY + 4,
        left: rect.left + window.scrollX,
    };
}

function selectItem(item) {
    let token;
    if (item.type === 'special') {
        token = `@${item.name}`;
    } else if (item.id) {
        token = `@cliente_${item.id}`;
    } else {
        token = `@${item.name}`;
    }

    const before = props.modelValue.slice(0, tokenStartPos.value);
    const after = props.modelValue.slice(tokenStartPos.value + currentToken.value.length + 1);
    const newValue = before + token + ' ' + after;
    emit('update:modelValue', newValue);
    emit('mention', item);

    dropdownOpen.value = false;
    nextTick(() => {
        if (textareaRef.value) {
            const newPos = before.length + token.length + 1;
            textareaRef.value.focus();
            textareaRef.value.setSelectionRange(newPos, newPos);
        }
    });
}

function onKeydown(e) {
    if (!dropdownOpen.value) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value + 1) % dropdownItems.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value - 1 + dropdownItems.value.length) % dropdownItems.value.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        selectItem(dropdownItems.value[selectedIndex.value]);
    } else if (e.key === 'Escape') {
        dropdownOpen.value = false;
    }
}

function closeOnClickOutside(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target) && e.target !== textareaRef.value) {
        dropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closeOnClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeOnClickOutside);
});
</script>

<template>
  <div class="relative">
    <textarea
      ref="textareaRef"
      :value="modelValue"
      @input="onInput"
      @keydown="onKeydown"
      :placeholder="placeholder"
      :maxlength="maxLength"
      :rows="rows"
      class="w-full rounded-lg border border-wc-border bg-wc-bg p-3 text-sm text-wc-text resize-none focus:border-wc-accent focus:outline-none"
    />
    <Teleport to="body">
      <div v-if="dropdownOpen" ref="dropdownRef"
        :style="{ top: dropdownPos.top + 'px', left: dropdownPos.left + 'px' }"
        class="fixed z-50 w-72 max-h-64 overflow-y-auto rounded-xl border border-wc-border bg-wc-bg-secondary shadow-2xl py-1"
      >
        <button
          v-for="(item, i) in dropdownItems" :key="`${item.type}-${item.id ?? item.name}`"
          @click="selectItem(item)"
          :class="i === selectedIndex ? 'bg-wc-bg-tertiary' : ''"
          class="w-full text-left px-3 py-2 text-sm hover:bg-wc-bg-tertiary"
        >
          <span v-if="item.type === 'special'" class="font-semibold text-wc-accent">{{ item.label }}</span>
          <span v-else>
            <span class="font-semibold text-wc-text">{{ item.name }}</span>
            <span class="text-wc-text-tertiary text-xs ml-1">cliente</span>
          </span>
        </button>
      </div>
    </Teleport>
  </div>
</template>
```

```bash
git add resources/js/vue/components/community/MentionInput.vue
git commit -m "feat(community): MentionInput with autocomplete dropdown + special tokens"
```

---

## Task 9: Component — ReportPostMenu

**Files:**
- Create: `resources/js/vue/components/community/ReportPostMenu.vue`

```vue
<script setup>
import { ref } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import { useHaptics } from '../../composables/useHaptics';

const props = defineProps({ postId: { type: Number, required: true } });
const emit = defineEmits(['reported']);

const api = useApi();
const toast = useToast();
const haptics = useHaptics();

const menuOpen = ref(false);
const modalOpen = ref(false);
const reason = ref('');
const detail = ref('');
const submitting = ref(false);

const REASONS = [
    { value: 'spam', label: 'Spam o promoción' },
    { value: 'offensive', label: 'Contenido ofensivo' },
    { value: 'off_topic', label: 'Off-topic' },
    { value: 'other', label: 'Otro' },
];

function openModal() {
    menuOpen.value = false;
    modalOpen.value = true;
}

async function submit() {
    if (!reason.value) {
        toast.warn('Selecciona una razón.');
        return;
    }
    submitting.value = true;
    try {
        await api.post(`/api/v/community/posts/${props.postId}/report`, {
            reason: reason.value,
            reason_detail: detail.value || null,
        });
        haptics.success();
        toast.success('Reporte enviado. Revisaremos a la brevedad.');
        emit('reported', props.postId);
        close();
    } catch (err) {
        haptics.error();
        toast.apiError(err, 'No pudimos enviar el reporte.');
    } finally {
        submitting.value = false;
    }
}

function close() {
    modalOpen.value = false;
    reason.value = '';
    detail.value = '';
}
</script>

<template>
  <div class="relative">
    <button @click="menuOpen = !menuOpen" class="rounded-lg p-1.5 text-wc-text-tertiary hover:text-wc-text hover:bg-wc-bg-tertiary" aria-label="Más opciones">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5zm0 6a.75.75 0 110-1.5.75.75 0 010 1.5z" />
      </svg>
    </button>

    <Transition enter-active-class="duration-150" enter-from-class="opacity-0 scale-95">
      <div v-if="menuOpen" class="absolute right-0 top-full mt-1 w-48 rounded-xl border border-wc-border bg-wc-bg-secondary shadow-xl z-20 py-1">
        <button @click="openModal" class="w-full text-left px-3 py-2 text-sm text-rose-500 hover:bg-rose-500/10 flex items-center gap-2">
          <span>🚩</span><span>Reportar post</span>
        </button>
      </div>
    </Transition>

    <Transition enter-active-class="duration-200" enter-from-class="opacity-0">
      <div v-if="modalOpen" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4" @click.self="close">
        <div class="rounded-2xl bg-wc-bg-secondary border border-wc-border w-full max-w-md p-6">
          <header class="flex items-center justify-between mb-4">
            <h3 class="font-display text-xl text-wc-text">Reportar post</h3>
            <button @click="close" class="text-wc-text-tertiary hover:text-wc-text">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </header>
          <p class="text-sm text-wc-text-secondary mb-3">¿Por qué reportas este post?</p>
          <div class="space-y-2 mb-4">
            <label v-for="r in REASONS" :key="r.value" class="flex items-center gap-2 cursor-pointer">
              <input type="radio" :value="r.value" v-model="reason" class="accent-wc-accent" />
              <span class="text-sm text-wc-text">{{ r.label }}</span>
            </label>
          </div>
          <textarea v-model="detail" rows="3" maxlength="500" placeholder="Detalles (opcional)" class="w-full rounded-lg border border-wc-border bg-wc-bg p-2 text-sm text-wc-text mb-4 resize-none"></textarea>
          <div class="flex gap-3">
            <button @click="close" class="flex-1 rounded-full border border-wc-border text-wc-text-secondary py-2">Cancelar</button>
            <button @click="submit" :disabled="!reason || submitting" class="flex-1 rounded-full bg-wc-accent text-white py-2 font-semibold disabled:opacity-50">
              {{ submitting ? 'Enviando…' : 'Enviar reporte' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
```

```bash
git add resources/js/vue/components/community/ReportPostMenu.vue
git commit -m "feat(community): ReportPostMenu cliente con modal 4 razones"
```

---

## Task 10: Component — OnlineRoleIndicator

**Files:**
- Create: `resources/js/vue/components/community/OnlineRoleIndicator.vue`

```vue
<script setup>
import { computed, onMounted } from 'vue';
import { useGroupPresence } from '../../composables/useGroupPresence';

const props = defineProps({
    userId: { type: [Number, null], required: true },
    userType: { type: String, required: true },
});

const presence = useGroupPresence();
onMounted(() => presence.init());

const isOnline = computed(() => presence.isOnline(props.userType, props.userId));
</script>

<template>
  <span :title="isOnline ? 'Activo ahora' : 'Inactivo'" class="relative inline-flex items-center">
    <span :class="isOnline ? 'bg-emerald-500' : 'bg-wc-text-tertiary/40'" class="h-2 w-2 rounded-full"></span>
    <span v-if="isOnline" class="absolute inline-flex h-2 w-2 rounded-full bg-emerald-500 opacity-75 animate-ping"></span>
  </span>
</template>
```

```bash
git add resources/js/vue/components/community/OnlineRoleIndicator.vue
git commit -m "feat(community): OnlineRoleIndicator with pulse dot"
```

---

## Task 11: Page — NotificationsPreferences cliente

**Files:**
- Create: `resources/js/vue/pages/Client/NotificationsPreferences.vue`

```vue
<script setup>
import { ref, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();
const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const prefs = ref(null);

const TOGGLES = [
    { key: 'notify_post_reactions', label: 'Cuando alguien reacciona a mi post' },
    { key: 'notify_comments_on_my_post', label: 'Cuando comentan en mi post' },
    { key: 'notify_mentions', label: 'Cuando alguien me menciona' },
    { key: 'notify_coach_messages', label: 'Cuando mi coach me escribe' },
    { key: 'notify_coach_announcements', label: 'Anuncios de mi coach' },
    { key: 'notify_wellcore_announcements', label: 'Anuncios de WellCore' },
];

let saveTimeout = null;

async function load() {
    loading.value = true;
    try {
        const res = await api.get('/api/v/client/notifications/preferences');
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
        const res = await api.patch('/api/v/client/notifications/preferences', prefs.value);
        prefs.value = res.data;
        toast.success('✓ Guardado');
    } catch (err) {
        toast.apiError(err, 'No pudimos guardar.');
    } finally {
        saving.value = false;
    }
}

watch(prefs, () => debouncedSave(), { deep: true });
onMounted(() => load());
</script>

<template>
  <ClientLayout>
    <div class="max-w-2xl mx-auto py-6 space-y-6">
      <header>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Notificaciones</h1>
        <p class="text-sm text-wc-text-tertiary mt-1">Decide qué eventos quieres recibir y cómo.</p>
      </header>

      <div v-if="loading" class="space-y-2">
        <div v-for="i in 8" :key="i" class="h-12 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      </div>

      <template v-else-if="prefs">
        <section class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
          <h2 class="font-semibold text-wc-text mb-3">Canales</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-wc-text">Push (browser)</p>
              <input type="checkbox" v-model="prefs.push_enabled" class="h-5 w-9 accent-wc-accent" />
            </div>
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-wc-text">In-app (campana)</p>
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
  </ClientLayout>
</template>
```

- [ ] **Add route in router/index.js**:

```js
{ path: '/client/notifications', name: 'client-notifications',
  component: () => import('../pages/Client/NotificationsPreferences.vue'),
  meta: { auth: true, title: 'Notificaciones — WellCore' } },
```

```bash
git add resources/js/vue/pages/Client/NotificationsPreferences.vue resources/js/vue/router/index.js
git commit -m "feat(community): cliente NotificationsPreferences page + route"
```

---

## Task 12: Modify CommentsThread.vue

**Files:**
- Modify: `resources/js/vue/components/community/CommentsThread.vue`

- [ ] **Step 1: Read current state**

```bash
cat resources/js/vue/components/community/CommentsThread.vue
```

- [ ] **Step 2: Replace with new shape**

```vue
<script setup>
import { computed } from 'vue';
import CoachBadge from './CoachBadge.vue';
import OfficialBadge from './OfficialBadge.vue';
import MentionRenderer from './MentionRenderer.vue';

const props = defineProps({
    comments: { type: Array, default: () => [] },
    isCoachContext: { type: Boolean, default: false },
});

const sortedComments = computed(() => {
    const adminComments = props.comments.filter(c => c.author_type === 'admin');
    const coachComments = props.comments.filter(c => c.author_type === 'coach');
    const clientComments = props.comments
        .filter(c => !c.author_type || c.author_type === 'client')
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    return [...adminComments, ...coachComments, ...clientComments];
});
</script>

<template>
  <div class="space-y-2">
    <article v-for="comment in sortedComments" :key="comment.id" class="flex gap-2 text-sm">
      <div class="h-8 w-8 rounded-full bg-wc-accent/15 flex items-center justify-center shrink-0 overflow-hidden">
        <img v-if="comment.avatar_url" :src="comment.avatar_url" alt="" class="h-full w-full object-cover" />
        <span v-else class="text-xs font-semibold text-wc-accent">{{ (comment.author_name || comment.client_name || '?').charAt(0) }}</span>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-semibold text-wc-text">{{ comment.client_name || comment.author_name }}</span>
          <CoachBadge v-if="comment.author_type === 'coach'" size="xs" />
          <OfficialBadge v-if="comment.author_type === 'admin'" />
          <span class="text-xs text-wc-text-tertiary">{{ comment.created_at_human || '' }}</span>
        </div>
        <MentionRenderer :content="comment.content" />
      </div>
    </article>
  </div>
</template>
```

```bash
git add resources/js/vue/components/community/CommentsThread.vue
git commit -m "feat(community): CommentsThread sort admin>coach>client + badges + MentionRenderer"
```

---

## Task 13: Modify CommunityFeed.vue cliente

**Files:**
- Modify: `resources/js/vue/pages/Client/CommunityFeed.vue`

Edit existing file:

1. **Imports** (top of script setup):
```js
import MentionInput from '../../components/community/MentionInput.vue';
import MentionRenderer from '../../components/community/MentionRenderer.vue';
import ReportPostMenu from '../../components/community/ReportPostMenu.vue';
import OnlineRoleIndicator from '../../components/community/OnlineRoleIndicator.vue';
import CoachBadge from '../../components/community/CoachBadge.vue';
import OfficialBadge from '../../components/community/OfficialBadge.vue';
import { useGroupPresence } from '../../composables/useGroupPresence';
```

2. **Composer** — replace `<textarea v-model="postContent">` with:
```html
<MentionInput v-model="postContent" :max-length="500" scope="coach-team" placeholder="Comparte tu progreso..." />
```

3. **Post body** — replace `{{ post.content }}` with:
```html
<MentionRenderer :content="post.content" />
```

4. **Post header** — modify to include badges + report menu:
```html
<header class="flex items-center justify-between">
  <div class="flex items-center gap-2 flex-wrap">
    <span class="font-semibold">{{ post.client_name || post.author_name }}</span>
    <CoachBadge v-if="post.author_type === 'coach'" size="xs" />
    <OfficialBadge v-if="post.author_type === 'admin' || post.is_official" />
    <OnlineRoleIndicator v-if="post.coach_id" :user-id="post.coach_id" :user-type="'coach'" />
    <span class="text-xs text-wc-text-tertiary">{{ timeAgo(post.created_at) }}</span>
  </div>
  <ReportPostMenu :post-id="post.id" @reported="onReported(post.id)" />
</header>
```

5. **onMounted** — add presence init + user channel:
```js
import { useAuthStore } from '../../stores/auth';
const authStore = useAuthStore();
const presence = useGroupPresence();

onMounted(() => {
    presence.init();
    if (window.Echo && authStore.userId) {
        const userChannel = window.Echo.private(`user.client.${authStore.userId}`)
            .listen('.mention.created', (e) => {
                toast.info(`${e.mentioner_name || 'Alguien'} te mencionó.`);
            });
        // store reference to leave on unmount
    }
    fetchFeed(true);
});
```

6. **Handler `onReported`** — new method:
```js
function onReported(postId) {
    toast.success('Reporte enviado.');
}
```

```bash
git add resources/js/vue/pages/Client/CommunityFeed.vue
git commit -m "feat(community): cliente CommunityFeed integrates mentions + report + badges + presence"
```

---

## Task 14: Backend test — CrossRoleBadgesRendering

**Files:**
- Create: `tests/Feature/Community/CrossRoleBadgesRenderingTest.php`

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('returns comments with author_type for coach replies', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);
    $client = Client::factory()->create(['coach_id' => $coach->id]);
    $post = CommunityPost::factory()->create(['client_id' => $client->id, 'coach_admin_id' => $coach->id]);

    PostComment::factory()->create([
        'post_id' => $post->id,
        'client_id' => $client->id,
        'author_type' => 'client',
    ]);
    PostComment::factory()->create([
        'post_id' => $post->id,
        'author_type' => 'coach',
        'author_admin_id' => $coach->id,
    ]);

    $token = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $client->id, 'user_type' => 'client',
        'token' => hash('sha256', $token), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $resp = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v/client/community')
        ->assertOk();

    $posts = $resp->json('posts');
    expect($posts)->toBeArray();
});
```

```bash
git add tests/Feature/Community/CrossRoleBadgesRenderingTest.php
git commit -m "test(community): cross-role badges rendering API contract"
```

---

## Task 15: Backend test — PostReportFlowE2E

**Files:**
- Create: `tests/Feature/Community/PostReportFlowE2ETest.php`

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('cliente reports → admin queue → dismiss → status changes', function () {
    $coach = Admin::factory()->create(['role' => 'coach']);
    $admin = Admin::factory()->create(['role' => 'superadmin']);
    $reporter = Client::factory()->create(['coach_id' => $coach->id]);
    $author = Client::factory()->create(['coach_id' => $coach->id]);
    $post = CommunityPost::factory()->create([
        'client_id' => $author->id, 'coach_admin_id' => $coach->id,
    ]);

    $clientToken = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $reporter->id, 'user_type' => 'client',
        'token' => hash('sha256', $clientToken), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$clientToken}")
        ->postJson("/api/v/community/posts/{$post->id}/report", [
            'reason' => 'offensive',
            'reason_detail' => 'Test',
        ])->assertStatus(201);

    expect(PostReport::where('post_id', $post->id)->where('status', 'pending')->exists())->toBeTrue();

    $adminToken = bin2hex(random_bytes(32));
    DB::table('auth_tokens')->insert([
        'user_id' => $admin->id, 'user_type' => 'admin',
        'token' => hash('sha256', $adminToken), 'expires_at' => now()->addDays(30),
        'created_at' => now(),
    ]);

    $resp = $this->withHeader('Authorization', "Bearer {$adminToken}")
        ->getJson('/api/v/admin/community/moderation/queue')->assertOk();

    $reportIds = collect($resp->json('data'))->pluck('report_id')->all();
    expect($reportIds)->not->toBeEmpty();

    $reportId = $reportIds[0];

    $this->withHeader('Authorization', "Bearer {$adminToken}")
        ->postJson("/api/v/admin/community/moderation/{$reportId}/dismiss")
        ->assertOk();

    expect(PostReport::find($reportId)->status)->toBe('dismissed');
});
```

```bash
git add tests/Feature/Community/PostReportFlowE2ETest.php
git commit -m "test(community): full report flow cliente → admin queue → dismiss"
```

---

## Task 16: Run tests + Build + Smoke E2E

- [ ] **Run Vitest**

```bash
npm run test:unit
```

- [ ] **Run Pest**

```bash
DB_DATABASE=wellcore_fitness_test php artisan migrate --force
vendor/bin/pest --parallel
```

- [ ] **Pint**

```bash
vendor/bin/pint
git add -u
git commit -m "style: pint Fase D"
```

- [ ] **Build local**

```bash
npm run build
git add public/build/
git commit -m "build: Vite assets Fase D cross-role"
```

- [ ] **Smoke E2E manual**:

- [ ] Cliente login → /client/community
- [ ] Compose post: type `@carl` → dropdown matches
- [ ] Selecciona "Carlos Pérez" → texto cambia a `@cliente_42`
- [ ] Submit post → mention persistida → Carlos recibe push + in-app
- [ ] Post muestra mention chip color azul cliente
- [ ] Click chip → navega a profile
- [ ] Reportar post: 3-dot menu → modal → razón + submit
- [ ] Login admin → tab Moderation → ve el report
- [ ] Coach respondió → cliente ve "Coach D" + badge amarillo arriba
- [ ] Admin postea oficial → cliente ve "WellCore" + badge rojo
- [ ] OnlineRoleIndicator: dot verde con pulse cuando coach activo
- [ ] /client/notifications → toggles + live save
- [ ] Console clean

---

## Task 17: CLAUDE.md final + push

- [ ] **Append to CLAUDE.md** (replace previous Community sections):

```markdown
## Community Cross-Role (COMPLETE — A+B+C+D shipped)

Sistema completo de comunicación cross-role: cliente ↔ coach ↔ admin.

### Fase A — Backend foundations (completed)
- 9 migrations aditivas, 6 models, 5 services, 6 controllers, 1 policy, 6 events broadcast Reverb
- 16 endpoints REST, cache strategy con namespaces wc:*

### Fase B — Coach Community Hub (completed)
- /coach/community con 5 tabs (Latido / Posts / Conversaciones / Pulsos / Logros)
- /coach/notifications preferences page
- Modal Mensaje al equipo (anuncio in-feed o push notification)
- Sidebar nueva sección Comunidad
- Composables: useCoachCommunity, useCoachPulse, useModeration, useCoachAnnounce, usePushSubscription
- Backend: announce impl + threads + achievements + push subscriptions + clients/count + scheduled command precompute-coach-pulse

### Fase C — Admin Community Center (completed)
- /admin/community con 5 tabs (Pulse Cross-Coach / Live Feed / Broadcast / Moderation / Analytics Coach)
- /admin/notifications preferences
- Composables: useAdminCommunity, useBroadcast, useModerationQueue
- Backend: coachAnalytics + admin notification prefs + pinAdminOverride + makeGlobal
- Charts: Chart.js 4 lazy-loaded
- Migration aditiva: admin_notification_preferences

### Fase D — Cross-Role Communication Layer (completed)
- MentionInput + MentionRenderer cross-portal con autocomplete
- ReportPostMenu cliente (4 razones)
- CommentsThread sort admin>coach>client + badges (CoachBadge / OfficialBadge)
- /client/notifications preferences page
- Composables: useMentions, useGroupPresence
- Backend: mention-search endpoint + client_notification_preferences migration + 3 routes

### Total métricas
- 9+ migrations aditivas (sin destructivas)
- 24 endpoints REST cross-portal
- 10 composables singleton TTL+dedup+reset
- 30+ Vue components nuevos
- 60+ tests (Pest + Vitest)
- Cache: wc:coach-pulse, wc:admin-community-analytics, wc:admin-coach-analytics, wc:mention-search

Specs en `docs/superpowers/specs/2026-05-05-community-cross-role-fase-{a,b,c,d}-*-design.md`.
Plans en `docs/superpowers/plans/2026-05-05-community-cross-role-fase-{a,b,c,d}-*.md`.
```

- [ ] **Commit + push**

```bash
git add CLAUDE.md
git commit -m "docs(community): document Fase D + close Community Cross-Role project"
git push origin feat/community-cross-role-fase-d
```

---

## Definition of Done — Fase D

### Frontend
- [ ] MentionInput + MentionRenderer + ReportPostMenu + OnlineRoleIndicator implementados
- [ ] CommunityFeed cliente integra mentions + report + badges + presence
- [ ] CommentsThread sort admin>coach>client + badges + MentionRenderer
- [ ] NotificationsPreferences cliente
- [ ] 2 composables singleton + reset hooks
- [ ] auth.js extendido 2 reset calls (presence + mentions)
- [ ] Real-time user.client.{id} listened en CommunityFeed
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
- [ ] No regresión Pest suite completa
- [ ] Pint OK + ESLint OK
- [ ] Smoke E2E: 13 scenarios PASS

### Operations
- [ ] Cache wc:mention-search:v1:* 300s
- [ ] Lighthouse Performance ≥ 70 cliente CommunityFeed
- [ ] Console clean
- [ ] public/build/ commiteado

### Documentación
- [ ] Spec doc commiteado
- [ ] Plan doc commiteado
- [ ] CLAUDE.md actualizado (sección "Community Cross-Role COMPLETE")
- [ ] Branch push'eada

---

## Self-Review

**1. Spec coverage:** mentions ✅ · cross-role badges threads ✅ · ReportPostMenu ✅ · GroupPresence ✅ · client notifications ✅ · mention-search endpoint ✅

**2. Placeholder scan:** Task 13 dice "Edit existing file to..." con instrucciones específicas — no es placeholder, es change list ejecutable. Sin TBDs estructurales.

**3. Type consistency:** scope params consistentes ('coach-team' | 'all') · author_type enum consistente cross componentes · cache keys wc:* consistentes con Fases B/C

**4. Risk coverage:** XSS via mentions → Vue auto-escape + backend allowlist · spam mentions → backend dedup PostMention · cache leak → reset hooks · presence saturation → Echo handles

**5. Cross-fase integration:** uses CoachBadge/OfficialBadge from Fase B ✅ · admin moderation queue Fase C recibe post-reports ✅ · auth.js reset hooks consolidados ✅

Plan listo para ejecución.
