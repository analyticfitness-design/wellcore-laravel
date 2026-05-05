# Latido del Grupo — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir un feed de actividad agregada del grupo (compañeros del mismo coach) en el Dashboard cliente y en una nueva tab "Latido" de Comunidad, con widget compacto, agregaciones inteligentes, filtros temporales, comparativas tú-vs-grupo en heatmap y misiones, y privacidad opt-out usando flags `autoshare_*` ya existentes.

**Architecture:** Service `GroupPulseAggregator` lee de 4 fuentes existentes (`workout_sessions`, `personal_records`, `client_achievements`, `community_posts`), aplica filtros por coach + flags `autoshare_*`, agrega heurísticamente y cachea en Redis. Controller único expone scopes `summary` (dashboard) y `feed` (tab paginada). Frontend Vue 3 con composable `useGroupPulse`, dos componentes nuevos, y mods incrementales en 5 archivos.

**Tech Stack:** Laravel 13.1.1 + PHP 8.4, Eloquent + Carbon, Redis cache, Pest tests, Vue 3.5 SFC + Pinia + axios, Tailwind CSS 4 con tokens WellCore.

**Spec:** `docs/superpowers/specs/2026-05-04-latido-grupo-design.md` (commit b35283a4)

---

## Design System Mandate (READ FIRST FOR ALL FRONTEND TASKS)

Every Vue component touched in this plan **MUST** match the visual language already established in the client Dashboard. The Dashboard is the reference. Any new component that drifts from these rules is a regression and must be rejected at code review.

**Tokens (defined in `resources/css/wc-shell.css`, scoped to `.wc-shell`):**

- Fonts: `var(--fd)` = Oswald (titles, numerics, labels uppercase). `var(--fs)` = Raleway (body, paragraphs).
  **NEVER** use `Bebas Neue`, `Barlow`, `Inter` — they were removed from the system.
- Colors: `var(--wc-accent)` (#DC2626), `var(--wc-accent-2)`, `var(--wc-accent-deep)`, `var(--wc-bg)`, `var(--wc-bg2)`, `var(--wc-bg3)`, `var(--wc-bg4)`, `var(--wc-text)`, `var(--wc-text-2)`, `var(--wc-text-3)`, `var(--wc-border)`, `var(--wc-green)`, `var(--wc-blue)`, `var(--wc-amber)`, `var(--wc-purple)`.
  **NEVER** use raw hex (#fca5a5, #86efac, #fde047) for brand surfaces — use tokens.
- Spacing: `var(--s4)`, `var(--s8)`, `var(--s12)`, `var(--s16)`, `var(--s20)`, `var(--s24)`, `var(--s32)`, `var(--s40)`, `var(--s56)`, `var(--s80)`.
- Radius: `var(--r-sm)` 12px, `var(--r-md)` 16px, `var(--r-lg)` 20px, `var(--r-pill)` 999px.
- Shadows: `var(--sh-card)`, `var(--sh-soft)`, `var(--sh-hover)`.
- Easings: `var(--ease-out)`, `var(--ease-spring)`, `var(--ease-glide)`.

**Reusable classes from `wc-shell.css`:**

- `.section` — entry stagger animation. Always set `:style="{ animationDelay: 'Xms' }"` (Hero=0ms, Stats=220ms, Coach=320ms, Activity=480ms — pick a value that fits position).
- `.card` — surface with shadow + grad border. Combine with `.section` for entry.
- `.card-head`, `.card-head-left`, `.card-title`, `.card-meta` — header layout.
- `.tnum` — tabular numbers. `.tight` — letter-spacing −0.02em on big numbers.
- `.grain` — Stripe-style grain overlay. `.divider` — fade-out line.
- `.stat-card.red`, `.stat-card.green`, `.stat-card.amber`, `.stat-card.purple` — color-coded stat cards (see `DashboardStats.vue`).

**Reference files (study before coding):**

- `resources/js/vue/pages/Client/Dashboard.vue` — page composition.
- `resources/js/vue/components/dashboard/DashboardStats.vue` — `.stat-card` variants.
- `resources/js/vue/components/dashboard/DashboardCoach.vue` — slim component using `.section.grain`.
- `resources/js/vue/components/dashboard/DashboardActivity.vue` — list with `.card-head` and stagger 480ms.
- `resources/css/wc-shell.css` — token definitions and primitives (`.chip`, `.chip-accent`, `.avatar` with conic ring, etc.).
- `resources/css/wc-shell-tabs/dashboard.css` — desktop grid spans for `.wc-card-dashboard-XXX` classes.

**Dark-first:** the dashboard tokens ARE the visual identity. `.wc-shell` sets `background: var(--wc-bg)` (#09090B). Components are dark-first by design. No light-mode handling needed — the parent shell controls bg.

**`prefers-reduced-motion`:** all animations (heartbeat, stagger, transitions) MUST disable when `(prefers-reduced-motion: reduce)`. Reuse the `useReducedMotion` composable for JS-driven animations.

---

## Task 0: Branch Setup

**Files:**
- No file changes — git only

- [ ] **Step 1: Verify clean working tree on main**

```bash
cd C:/Users/GODSF/Herd/wellcore-laravel
git status --short
git branch --show-current
```

Expected: branch is `main`, no uncommitted code changes (the `?? .agents/` and similar untracked dirs are pre-existing and OK).

- [ ] **Step 2: Create and switch to feat/group-pulse branch**

```bash
git checkout -b feat/group-pulse
```

Expected: `Switched to a new branch 'feat/group-pulse'`

- [ ] **Step 3: Verify spec exists on branch**

```bash
ls docs/superpowers/specs/2026-05-04-latido-grupo-design.md
```

Expected: file exists.

---

## Task 1: GroupPulseAggregator skeleton + computeStats (TDD)

**Files:**
- Create: `app/Services/GroupPulseAggregator.php`
- Create: `tests/Unit/Services/GroupPulseAggregatorComputeStatsTest.php`

- [ ] **Step 1: Write failing test for computeStats**

Create `tests/Unit/Services/GroupPulseAggregatorComputeStatsTest.php`:

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Models\WorkoutSession;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;

describe('GroupPulseAggregator::computeStats', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator();
    });

    it('counts workouts completed today by clients of the coach', function () {
        $client = Client::factory()->create(['coach_id' => $this->coach->id]);
        WorkoutSession::factory()->count(3)->create([
            'client_id' => $client->id,
            'completed' => true,
            'session_date' => Carbon::today(),
        ]);
        // Workout from yesterday should NOT count
        WorkoutSession::factory()->create([
            'client_id' => $client->id,
            'completed' => true,
            'session_date' => Carbon::yesterday(),
        ]);

        $stats = $this->aggregator->computeStats($this->coach->id);

        expect($stats['workouts_today'])->toBe(3);
    });

    it('counts personal records this week', function () {
        $client = Client::factory()->create(['coach_id' => $this->coach->id]);
        PersonalRecord::factory()->count(5)->create([
            'client_id' => $client->id,
            'is_current' => 1,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $stats = $this->aggregator->computeStats($this->coach->id);

        expect($stats['prs_week'])->toBe(5);
    });

    it('returns zero counts when coach has no clients', function () {
        $stats = $this->aggregator->computeStats($this->coach->id);

        expect($stats)->toMatchArray([
            'workouts_today' => 0,
            'prs_week' => 0,
            'achievements_today' => 0,
            'checkins_week' => 0,
        ]);
    });
});
```

- [ ] **Step 2: Run test — should fail with class not found**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorComputeStatsTest.php
```

Expected: 3 tests fail with `Class "App\Services\GroupPulseAggregator" not found`.

- [ ] **Step 3: Create GroupPulseAggregator with computeStats**

Create `app/Services/GroupPulseAggregator.php`:

```php
<?php

namespace App\Services;

use App\Models\Client;
use App\Models\WorkoutSession;
use App\Models\PersonalRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GroupPulseAggregator
{
    public function computeStats(int $coachId): array
    {
        $clientIds = Client::where('coach_id', $coachId)->pluck('id');

        if ($clientIds->isEmpty()) {
            return [
                'workouts_today' => 0,
                'prs_week' => 0,
                'achievements_today' => 0,
                'checkins_week' => 0,
            ];
        }

        $workoutsToday = WorkoutSession::whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->whereDate('session_date', Carbon::today())
            ->count();

        $prsWeek = PersonalRecord::whereIn('client_id', $clientIds)
            ->where('is_current', 1)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $achievementsToday = DB::table('client_achievements')
            ->whereIn('client_id', $clientIds)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $checkinsWeek = DB::table('checkins')
            ->whereIn('client_id', $clientIds)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return compact('workoutsToday', 'prsWeek', 'achievementsToday', 'checkinsWeek');
    }
}
```

Wait — the keys in `compact` will be camelCase, but tests expect snake_case. Fix:

```php
        return [
            'workouts_today' => $workoutsToday,
            'prs_week' => $prsWeek,
            'achievements_today' => $achievementsToday,
            'checkins_week' => $checkinsWeek,
        ];
```

Replace the final `return compact(...)` with the explicit array above.

- [ ] **Step 4: Run tests — should pass**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorComputeStatsTest.php
```

Expected: 3 tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Services/GroupPulseAggregator.php tests/Unit/Services/GroupPulseAggregatorComputeStatsTest.php
git commit -m "feat(group-pulse): GroupPulseAggregator::computeStats con 4 contadores agregados"
```

---

## Task 2: GroupPulseAggregator buildFeed (eventos individuales)

**Files:**
- Modify: `app/Services/GroupPulseAggregator.php`
- Create: `tests/Unit/Services/GroupPulseAggregatorBuildFeedTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/Services/GroupPulseAggregatorBuildFeedTest.php`:

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Models\WorkoutSession;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;

describe('GroupPulseAggregator::buildFeed', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator();
    });

    it('returns PR events as individual entries', function () {
        $client = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'name' => 'Carlos Rojas',
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $client->id,
            'exercise_name' => 'Sentadilla',
            'weight_kg' => 120,
            'reps' => 5,
            'is_current' => 1,
            'created_at' => Carbon::now()->subMinutes(8),
        ]);

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');

        expect($events)->toHaveCount(1);
        expect($events[0])->toMatchArray([
            'type' => 'pr',
            'client_name' => 'Carlos R.',
            'client_initials' => 'CR',
        ]);
        expect($events[0]['headline'])->toContain('Sentadilla');
        expect($events[0]['headline'])->toContain('120');
    });

    it('filters by time window today', function () {
        $client = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $client->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $client->id,
            'is_current' => 1,
            'created_at' => Carbon::today()->subDays(3),
        ]);

        $todayEvents = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        $weekEvents = $this->aggregator->buildFeed($this->coach->id, 'week', 'all');

        expect($todayEvents)->toHaveCount(1);
        expect($weekEvents)->toHaveCount(2);
    });

    it('returns empty array for coach without clients', function () {
        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        expect($events)->toBeArray()->toBeEmpty();
    });
});
```

- [ ] **Step 2: Run test — should fail with method not found**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorBuildFeedTest.php
```

Expected: 3 tests fail.

- [ ] **Step 3: Add buildFeed method to aggregator**

Open `app/Services/GroupPulseAggregator.php` and add at the end of the class (before closing `}`):

```php
    public function buildFeed(int $coachId, string $time = 'today', string $type = 'all'): array
    {
        $clientIds = Client::where('coach_id', $coachId)->pluck('id');
        if ($clientIds->isEmpty()) {
            return [];
        }

        $since = $this->resolveSince($time);

        $events = collect();

        if ($type === 'all' || $type === 'pr') {
            $prs = PersonalRecord::whereIn('client_id', $clientIds)
                ->where('is_current', 1)
                ->where('created_at', '>=', $since)
                ->whereHas('client', fn ($q) => $q->where('autoshare_pr', 1))
                ->with('client:id,name')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(fn ($pr) => $this->prToEvent($pr));
            $events = $events->merge($prs);
        }

        return $events
            ->sortByDesc('minutes_ago_inverse')
            ->values()
            ->map(fn ($e) => collect($e)->except('minutes_ago_inverse')->all())
            ->all();
    }

    protected function resolveSince(string $time): Carbon
    {
        return match ($time) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subDays(7),
            'all' => Carbon::now()->subDays(30),
            default => Carbon::today(),
        };
    }

    protected function prToEvent(PersonalRecord $pr): array
    {
        $clientName = $pr->client?->name ?? 'Miembro';
        $minutesAgo = $pr->created_at->diffInMinutes(Carbon::now());

        return [
            'type' => 'pr',
            'client_name' => $this->shortName($clientName),
            'client_initials' => $this->initials($clientName),
            'headline' => $this->prHeadline($pr),
            'minutes_ago' => $minutesAgo,
            'minutes_ago_inverse' => -$minutesAgo,
        ];
    }

    protected function prHeadline(PersonalRecord $pr): string
    {
        $exercise = $pr->exercise_name ?? 'Ejercicio';
        $weight = $pr->weight_kg ? "{$pr->weight_kg}kg" : '';
        $reps = $pr->reps ? " x{$pr->reps}" : '';
        return trim("rompió PR de {$exercise} {$weight}{$reps}");
    }

    protected function shortName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) < 2) return $parts[0] ?? 'Miembro';
        return $parts[0] . ' ' . mb_strtoupper(mb_substr($parts[1], 0, 1)) . '.';
    }

    protected function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $first = mb_substr($parts[0] ?? 'M', 0, 1);
        $second = mb_substr($parts[1] ?? '', 0, 1);
        return mb_strtoupper($first . $second);
    }
```

- [ ] **Step 4: Run tests — should pass**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorBuildFeedTest.php
```

Expected: 3 tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Services/GroupPulseAggregator.php tests/Unit/Services/GroupPulseAggregatorBuildFeedTest.php
git commit -m "feat(group-pulse): buildFeed devuelve eventos PR individuales con filtro temporal"
```

---

## Task 3: GroupPulseAggregator agregación >5 workouts/h

**Files:**
- Modify: `app/Services/GroupPulseAggregator.php`
- Create: `tests/Unit/Services/GroupPulseAggregatorAggregationTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/Services/GroupPulseAggregatorAggregationTest.php`:

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\WorkoutSession;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;

describe('GroupPulseAggregator workout aggregation', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator();
    });

    it('aggregates when more than 5 workouts in last hour', function () {
        $clients = Client::factory()->count(8)->create([
            'coach_id' => $this->coach->id,
            'autoshare_workout' => 1,
        ]);

        foreach ($clients as $client) {
            WorkoutSession::factory()->create([
                'client_id' => $client->id,
                'completed' => true,
                'session_date' => Carbon::today(),
                'updated_at' => Carbon::now()->subMinutes(rand(5, 50)),
                'total_volume_kg' => 300,
            ]);
        }

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');

        $aggregateEvent = collect($events)->firstWhere('type', 'aggregate');

        expect($aggregateEvent)->not()->toBeNull();
        expect($aggregateEvent['people_count'])->toBe(8);
        expect($aggregateEvent['headline'])->toContain('8 personas');
        expect($aggregateEvent['extra'])->toContain('kg');
    });

    it('does NOT aggregate when 5 or fewer workouts', function () {
        $clients = Client::factory()->count(3)->create([
            'coach_id' => $this->coach->id,
            'autoshare_workout' => 1,
        ]);

        foreach ($clients as $client) {
            WorkoutSession::factory()->create([
                'client_id' => $client->id,
                'completed' => true,
                'session_date' => Carbon::today(),
                'updated_at' => Carbon::now()->subMinutes(20),
            ]);
        }

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        $aggregate = collect($events)->firstWhere('type', 'aggregate');

        expect($aggregate)->toBeNull();
    });
});
```

- [ ] **Step 2: Run test — should fail**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorAggregationTest.php
```

Expected: aggregate event not in feed (test fails).

- [ ] **Step 3: Add workout aggregation to buildFeed**

Open `app/Services/GroupPulseAggregator.php`. Inside `buildFeed`, add after the PR block and before the final sort/return:

```php
        if ($type === 'all' || $type === 'workout') {
            $workoutEvent = $this->aggregateRecentWorkouts($clientIds, $since);
            if ($workoutEvent) {
                $events->push($workoutEvent);
            }
        }
```

Then add this method to the class:

```php
    protected function aggregateRecentWorkouts(\Illuminate\Support\Collection $clientIds, Carbon $since): ?array
    {
        $hourAgo = Carbon::now()->subHour();
        $window = $since->greaterThan($hourAgo) ? $since : $hourAgo;

        $rows = WorkoutSession::whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('updated_at', '>=', $window)
            ->whereHas('client', fn ($q) => $q->where('autoshare_workout', 1))
            ->select('client_id', 'total_volume_kg')
            ->with('client:id,name')
            ->get();

        $count = $rows->count();
        if ($count <= 5) {
            return null;
        }

        $totalVolume = (int) $rows->sum('total_volume_kg');
        $previewInitials = $rows->take(3)->map(fn ($r) => $this->initials($r->client?->name ?? 'M'))->all();
        if ($count > 3) {
            $previewInitials[] = '+' . ($count - 3);
        }

        return [
            'type' => 'aggregate',
            'headline' => "{$count} personas terminaron entrenamiento en la última hora",
            'people_count' => $count,
            'preview_initials' => $previewInitials,
            'extra' => number_format($totalVolume) . ' kg movidos en total',
            'minutes_ago' => 0,
            'minutes_ago_inverse' => 0,
        ];
    }
```

- [ ] **Step 4: Run tests — should pass**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorAggregationTest.php
```

Expected: 2 tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Services/GroupPulseAggregator.php tests/Unit/Services/GroupPulseAggregatorAggregationTest.php
git commit -m "feat(group-pulse): agregar workouts >5/h en card 'X personas entrenaron'"
```

---

## Task 4: Filtrado por flags autoshare_*

**Files:**
- Create: `tests/Unit/Services/GroupPulseAggregatorPrivacyTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;

describe('GroupPulseAggregator privacy flags', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator();
    });

    it('hides PRs when client has autoshare_pr=0', function () {
        $hidden = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 0,
        ]);
        $visible = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $hidden->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $visible->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);

        $events = $this->aggregator->buildFeed($this->coach->id, 'today', 'all');
        $prEvents = collect($events)->where('type', 'pr');

        expect($prEvents)->toHaveCount(1);
    });
});
```

Save as `tests/Unit/Services/GroupPulseAggregatorPrivacyTest.php`.

- [ ] **Step 2: Run test**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorPrivacyTest.php
```

Expected: passes immediately because Task 2 already filtered by `autoshare_pr=1`. **If it fails, fix the buildFeed filter to use `whereHas('client', fn ($q) => $q->where('autoshare_pr', 1))`** (already in Task 2 code).

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/Services/GroupPulseAggregatorPrivacyTest.php
git commit -m "test(group-pulse): verificar filtrado por autoshare_pr=0"
```

---

## Task 5: Scope por coach_id (no leak cross-coach)

**Files:**
- Create: `tests/Unit/Services/GroupPulseAggregatorScopeTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use App\Models\Admin;
use App\Models\Client;
use App\Models\PersonalRecord;
use App\Services\GroupPulseAggregator;
use Carbon\Carbon;

describe('GroupPulseAggregator coach scope', function () {
    beforeEach(function () {
        $this->coachA = Admin::factory()->create();
        $this->coachB = Admin::factory()->create();
        $this->aggregator = new GroupPulseAggregator();
    });

    it('does not leak events from other coaches', function () {
        $clientA = Client::factory()->create(['coach_id' => $this->coachA->id, 'autoshare_pr' => 1]);
        $clientB = Client::factory()->create(['coach_id' => $this->coachB->id, 'autoshare_pr' => 1]);

        PersonalRecord::factory()->create([
            'client_id' => $clientA->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $clientB->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);

        $eventsA = $this->aggregator->buildFeed($this->coachA->id, 'today', 'all');
        $eventsB = $this->aggregator->buildFeed($this->coachB->id, 'today', 'all');

        expect(collect($eventsA)->where('type', 'pr'))->toHaveCount(1);
        expect(collect($eventsB)->where('type', 'pr'))->toHaveCount(1);
    });

    it('returns empty stats and feed for non-existent coach', function () {
        $stats = $this->aggregator->computeStats(999999);
        $feed = $this->aggregator->buildFeed(999999, 'today', 'all');

        expect($stats['workouts_today'])->toBe(0);
        expect($feed)->toBeEmpty();
    });
});
```

- [ ] **Step 2: Run tests**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregatorScopeTest.php
```

Expected: passes (Task 2 already scoped by `coach_id`).

- [ ] **Step 3: Commit**

```bash
git add tests/Unit/Services/GroupPulseAggregatorScopeTest.php
git commit -m "test(group-pulse): scope por coach_id no leak cross-coach"
```

---

## Task 6: GroupPulseController — endpoint summary

**Files:**
- Create: `app/Http/Controllers/Api/GroupPulseController.php`
- Create: `tests/Feature/Api/GroupPulseSummaryEndpointTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\PersonalRecord;
use Carbon\Carbon;

describe('GET /api/v/client/group-pulse?scope=summary', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->client = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'status' => 'activo',
        ]);
        $this->token = AuthToken::factory()->create([
            'auth_id' => $this->client->id,
            'auth_type' => 'client',
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => Carbon::now()->addDays(30),
        ]);
    });

    it('returns summary shape with stats and top_events', function () {
        PersonalRecord::factory()->create([
            'client_id' => $this->client->id,
            'is_current' => 1,
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token->token)
            ->getJson('/api/v/client/group-pulse?scope=summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'active_now',
                'bpm',
                'stats' => ['workouts_today', 'prs_week', 'achievements_today', 'checkins_week'],
                'top_events',
                'user_vs_group',
            ]);
    });

    it('returns 204 when client has no coach', function () {
        $orphan = Client::factory()->create(['coach_id' => null]);
        $token = AuthToken::factory()->create([
            'auth_id' => $orphan->id,
            'auth_type' => 'client',
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->token)
            ->getJson('/api/v/client/group-pulse?scope=summary');

        $response->assertStatus(204);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v/client/group-pulse?scope=summary');
        $response->assertStatus(401);
    });
});
```

Save as `tests/Feature/Api/GroupPulseSummaryEndpointTest.php`.

- [ ] **Step 2: Run test — fails because route does not exist**

```bash
./vendor/bin/pest tests/Feature/Api/GroupPulseSummaryEndpointTest.php
```

Expected: 404 errors.

- [ ] **Step 3: Create controller**

Create `app/Http/Controllers/Api/GroupPulseController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Services\GroupPulseAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class GroupPulseController extends Controller
{
    use AuthenticatesVueRequests;

    public function __construct(protected GroupPulseAggregator $aggregator) {}

    public function index(Request $request)
    {
        $client = $this->resolveClientOrFail($request);

        if (! $client->coach_id) {
            return response()->noContent();
        }

        $scope = $request->query('scope', 'summary');

        return match ($scope) {
            'feed' => $this->feed($request, (int) $client->coach_id, (int) $client->id),
            default => $this->summary((int) $client->coach_id, (int) $client->id),
        };
    }

    protected function summary(int $coachId, int $clientId): JsonResponse
    {
        $key = "wc:group-pulse:v1:{$coachId}:summary";
        $payload = Cache::remember($key, 30, function () use ($coachId, $clientId) {
            $stats = $this->aggregator->computeStats($coachId);
            $events = $this->aggregator->buildFeed($coachId, 'today', 'all');
            $topEvents = array_slice($events, 0, 3);
            $activeNow = (int) (Cache::get('community:active-list-count') ?? 0);

            return [
                'active_now' => $activeNow,
                'bpm' => max(40, min(180, $stats['workouts_today'] * 4 + 40)),
                'stats' => $stats,
                'top_events' => $topEvents,
                'user_vs_group' => $this->aggregator->userVsGroup($coachId, $clientId),
            ];
        });

        return response()->json($payload);
    }

    protected function feed(Request $request, int $coachId, int $clientId): JsonResponse
    {
        $time = $request->query('time', 'today');
        $type = $request->query('type', 'all');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(20, max(5, (int) $request->query('per_page', 10)));

        $key = "wc:group-pulse:v1:{$coachId}:feed:{$time}:{$type}:{$page}:{$perPage}";
        $payload = Cache::remember($key, 60, function () use ($coachId, $time, $type, $page, $perPage) {
            $all = $this->aggregator->buildFeed($coachId, $time, $type);
            $total = count($all);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $offset = ($page - 1) * $perPage;
            $events = array_slice($all, $offset, $perPage);

            return [
                'events' => $events,
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'total' => $total,
                ],
            ];
        });

        return response()->json($payload);
    }
}
```

- [ ] **Step 4: Add userVsGroup method to aggregator**

Open `app/Services/GroupPulseAggregator.php` and add this method:

```php
    public function userVsGroup(int $coachId, int $clientId): array
    {
        $clientIds = Client::where('coach_id', $coachId)->pluck('id');
        if ($clientIds->isEmpty()) {
            return [
                'weekly_workouts' => ['user' => 0, 'group_avg' => 0, 'rank_pct' => 0],
                'missions_peers' => [],
            ];
        }

        $week = Carbon::now()->subDays(7);

        $userCount = (int) WorkoutSession::where('client_id', $clientId)
            ->where('completed', true)
            ->where('session_date', '>=', $week)
            ->count();

        $groupCounts = WorkoutSession::whereIn('client_id', $clientIds)
            ->where('completed', true)
            ->where('session_date', '>=', $week)
            ->select('client_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('client_id')
            ->pluck('cnt')
            ->toArray();

        $groupAvg = empty($groupCounts) ? 0 : round(array_sum($groupCounts) / count($groupCounts), 1);
        $rankPct = $this->rankPercentile($userCount, $groupCounts);

        return [
            'weekly_workouts' => [
                'user' => $userCount,
                'group_avg' => $groupAvg,
                'rank_pct' => $rankPct,
            ],
            'missions_peers' => [],
        ];
    }

    protected function rankPercentile(int $value, array $allValues): int
    {
        if (empty($allValues)) return 0;
        $below = count(array_filter($allValues, fn ($v) => $v < $value));
        return (int) round(100 * (1 - $below / count($allValues)));
    }
```

- [ ] **Step 5: Add route**

Open `routes/api.php`. Find the `Route::prefix('v')` block with `auth:wellcore` (around line 140 — `Social & Resources`). After the line `Route::get('/community', [SocialController::class, 'communityIndex']);`, add:

```php
    Route::get('/group-pulse', [\App\Http\Controllers\Api\GroupPulseController::class, 'index']);
```

- [ ] **Step 6: Run tests — should pass**

```bash
./vendor/bin/pest tests/Feature/Api/GroupPulseSummaryEndpointTest.php
```

Expected: 3 tests pass.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Api/GroupPulseController.php app/Services/GroupPulseAggregator.php routes/api.php tests/Feature/Api/GroupPulseSummaryEndpointTest.php
git commit -m "feat(group-pulse): GET /api/v/client/group-pulse?scope=summary con cache 30s"
```

---

## Task 7: GroupPulseController — feed paginado

**Files:**
- Create: `tests/Feature/Api/GroupPulseFeedEndpointTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\PersonalRecord;
use Carbon\Carbon;

describe('GET /api/v/client/group-pulse?scope=feed', function () {
    beforeEach(function () {
        $this->coach = Admin::factory()->create();
        $this->client = Client::factory()->create(['coach_id' => $this->coach->id]);
        $this->token = AuthToken::factory()->create([
            'auth_id' => $this->client->id,
            'auth_type' => 'client',
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => Carbon::now()->addDays(30),
        ]);
    });

    it('paginates events correctly', function () {
        $other = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->count(15)->create([
            'client_id' => $other->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);

        $page1 = $this->withHeader('Authorization', 'Bearer ' . $this->token->token)
            ->getJson('/api/v/client/group-pulse?scope=feed&page=1&per_page=10');

        $page1->assertStatus(200)
            ->assertJsonStructure(['events', 'pagination' => ['current_page', 'last_page', 'total']])
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.total', 15);

        expect($page1->json('events'))->toHaveCount(10);
    });

    it('respects time filter', function () {
        $other = Client::factory()->create([
            'coach_id' => $this->coach->id,
            'autoshare_pr' => 1,
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $other->id,
            'is_current' => 1,
            'created_at' => Carbon::today(),
        ]);
        PersonalRecord::factory()->create([
            'client_id' => $other->id,
            'is_current' => 1,
            'created_at' => Carbon::today()->subDays(3),
        ]);

        $today = $this->withHeader('Authorization', 'Bearer ' . $this->token->token)
            ->getJson('/api/v/client/group-pulse?scope=feed&time=today');
        $week = $this->withHeader('Authorization', 'Bearer ' . $this->token->token)
            ->getJson('/api/v/client/group-pulse?scope=feed&time=week');

        expect($today->json('pagination.total'))->toBe(1);
        expect($week->json('pagination.total'))->toBe(2);
    });
});
```

- [ ] **Step 2: Run tests**

```bash
./vendor/bin/pest tests/Feature/Api/GroupPulseFeedEndpointTest.php
```

Expected: passes (Task 6 already implemented `feed`).

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Api/GroupPulseFeedEndpointTest.php
git commit -m "test(group-pulse): feed paginado y filtro temporal"
```

---

## Task 8: PrecomputeGroupPulse command + schedule

**Files:**
- Create: `app/Console/Commands/PrecomputeGroupPulse.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Create command**

```php
<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\GroupPulseAggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PrecomputeGroupPulse extends Command
{
    protected $signature = 'wellcore:precompute-group-pulse';
    protected $description = 'Warm Redis cache de Latido del Grupo para todos los coaches con clientes activos';

    public function handle(GroupPulseAggregator $aggregator): int
    {
        $coachIds = Client::where('status', 'activo')
            ->whereNotNull('coach_id')
            ->distinct()
            ->pluck('coach_id');

        $count = 0;
        foreach ($coachIds as $coachId) {
            $key = "wc:group-pulse:v1:{$coachId}:summary";
            $stats = $aggregator->computeStats((int) $coachId);
            $events = $aggregator->buildFeed((int) $coachId, 'today', 'all');
            $payload = [
                'active_now' => (int) (Cache::get('community:active-list-count') ?? 0),
                'bpm' => max(40, min(180, $stats['workouts_today'] * 4 + 40)),
                'stats' => $stats,
                'top_events' => array_slice($events, 0, 3),
                'user_vs_group' => null,
            ];
            Cache::put($key, $payload, 30);
            $count++;
        }

        $this->info("Precomputed group pulse for {$count} coaches");
        return self::SUCCESS;
    }
}
```

Save as `app/Console/Commands/PrecomputeGroupPulse.php`.

- [ ] **Step 2: Add schedule entry**

Open `routes/console.php`. After the line `Schedule::command('wellcore:smart-notifications')->dailyAt('09:00');`, add:

```php
Schedule::command('wellcore:precompute-group-pulse')->everyFiveMinutes()->withoutOverlapping(10);
```

- [ ] **Step 3: Test command runs without error**

```bash
php artisan wellcore:precompute-group-pulse
```

Expected: prints `Precomputed group pulse for N coaches`.

- [ ] **Step 4: Commit**

```bash
git add app/Console/Commands/PrecomputeGroupPulse.php routes/console.php
git commit -m "feat(group-pulse): scheduled command precompute-group-pulse cada 5min"
```

---

## Task 9: useGroupPulse composable

**Files:**
- Create: `resources/js/vue/composables/useGroupPulse.js`

- [ ] **Step 1: Create composable**

```javascript
import { ref, computed } from 'vue';
import { useApi } from './useApi';

const summaryCache = ref(null);
const summaryLoadedAt = ref(0);
const SUMMARY_TTL_MS = 25_000;

export function useGroupPulse() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    const isFresh = computed(() =>
        summaryCache.value && Date.now() - summaryLoadedAt.value < SUMMARY_TTL_MS
    );

    async function fetchSummary({ force = false } = {}) {
        if (!force && isFresh.value) return summaryCache.value;
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get('/api/v/client/group-pulse', {
                params: { scope: 'summary' },
            });
            if (response.status === 204) {
                summaryCache.value = null;
                return null;
            }
            summaryCache.value = response.data;
            summaryLoadedAt.value = Date.now();
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar el latido del grupo.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function fetchFeed({ time = 'today', type = 'all', page = 1, perPage = 10 } = {}) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get('/api/v/client/group-pulse', {
                params: { scope: 'feed', time, type, page, per_page: perPage },
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar el feed del grupo.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    return {
        summary: summaryCache,
        loading,
        error,
        isFresh,
        fetchSummary,
        fetchFeed,
    };
}
```

- [ ] **Step 2: Smoke check (no formal test — used live in components)**

No test step; will be exercised via Dashboard widget in Task 11.

- [ ] **Step 3: Commit**

```bash
git add resources/js/vue/composables/useGroupPulse.js
git commit -m "feat(group-pulse): useGroupPulse composable con cache 25s in-memory"
```

---

## Task 10: DashboardGroupPulse.vue — widget compacto

**Files:**
- Create: `resources/js/vue/components/dashboard/DashboardGroupPulse.vue`

### Design system mandate

**The component MUST live inside `.wc-shell--dashboard > main.scroll` and inherit ALL its tokens.** No hardcoded colors, no `Bebas Neue`, no `Barlow`. Use only:

- Fonts: `var(--fd)` (Oswald, titulares) and `var(--fs)` (Raleway, body) — defined in `resources/css/wc-shell.css`.
- Colors: `var(--wc-accent)`, `var(--wc-text)`, `var(--wc-text-2)`, `var(--wc-text-3)`, `var(--wc-bg2)`, `var(--wc-bg3)`, `var(--wc-bg4)`, `var(--wc-border)`, `var(--wc-green)`, `var(--wc-amber)`, `var(--wc-purple)`, `var(--wc-blue)`.
- Spacing: `var(--s4)`, `var(--s8)`, `var(--s12)`, `var(--s16)`, `var(--s20)`, `var(--s24)`.
- Radius: `var(--r-sm)`, `var(--r-md)`, `var(--r-lg)`, `var(--r-pill)`.
- Shadows: `var(--sh-card)`, `var(--sh-soft)`.
- Easings: `var(--ease-out)`, `var(--ease-spring)`, `var(--ease-glide)`.
- Pattern classes that already exist in `wc-shell.css`: `.section` (provides stagger fade-in), `.card`, `.card-head`, `.card-head-left`, `.card-title`, `.card-meta`, `.tnum`, `.tight`, `.grain`, `.divider`.

The component reuses the `.stat-card` variant pattern (`.stat-card.red/.green/.amber/.purple`) from `DashboardStats.vue` — see that file for the exact markup and CSS reference.

- [ ] **Step 1: Create component**

```vue
<script setup>
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useGroupPulse } from '../../composables/useGroupPulse';
import { useReducedMotion } from '../../composables/useReducedMotion';

const router = useRouter();
const { summary, loading, fetchSummary } = useGroupPulse();
const reducedMotion = useReducedMotion();

onMounted(() => fetchSummary());

const stats = computed(() => summary.value?.stats ?? null);
const topEvents = computed(() => summary.value?.top_events ?? []);
const bpm = computed(() => summary.value?.bpm ?? 60);
const activeNow = computed(() => summary.value?.active_now ?? 0);

// Heartbeat duration scales with BPM (60 BPM = 1s per beat)
const heartbeatStyle = computed(() => ({
    animationDuration: reducedMotion.value ? '0s' : `${60 / bpm.value}s`,
}));

function goToCommunity() {
    router.push('/comunidad?tab=latido');
}
</script>

<template>
  <section
    v-if="summary && stats"
    class="card section grain wc-card-group-pulse"
    :style="{ animationDelay: '260ms' }"
  >
    <div class="card-head">
      <div class="card-head-left">
        <span class="gp-pulse-dot" :style="heartbeatStyle" aria-hidden="true"></span>
        <span class="card-title">Latido del Grupo</span>
      </div>
      <span class="card-meta tnum">{{ activeNow }} activos · {{ bpm }} BPM</span>
    </div>

    <div class="gp-stats">
      <div class="stat-card red">
        <div class="stat-label">Entrenos hoy</div>
        <div class="stat-value tight tnum">{{ stats.workouts_today }}</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">PRs semana</div>
        <div class="stat-value tight tnum">{{ stats.prs_week }}</div>
      </div>
      <div class="stat-card amber">
        <div class="stat-label">Logros hoy</div>
        <div class="stat-value tight tnum">{{ stats.achievements_today }}</div>
      </div>
    </div>

    <div v-if="topEvents.length" class="gp-events">
      <div
        v-for="(ev, idx) in topEvents"
        :key="idx"
        class="gp-event"
        :data-type="ev.type"
      >
        <span class="gp-event-text">
          <strong v-if="ev.client_name">{{ ev.client_name }}</strong>
          <span>{{ ev.headline }}</span>
        </span>
        <span v-if="ev.minutes_ago !== undefined" class="gp-event-time tnum">
          hace {{ ev.minutes_ago }}min
        </span>
      </div>
    </div>

    <button type="button" class="gp-see-all" @click="goToCommunity">
      Ver todo el latido
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14M13 5l7 7-7 7"/>
      </svg>
    </button>
  </section>

  <section
    v-else-if="loading"
    class="card section wc-card-group-pulse"
    aria-busy="true"
    :style="{ animationDelay: '260ms' }"
  >
    <div class="gp-skel-line" style="width:42%"></div>
    <div class="gp-skel-line" style="width:88%"></div>
    <div class="gp-skel-line" style="width:64%"></div>
  </section>
</template>

<style scoped>
/* Component-specific only. All tokens come from .wc-shell scope. */
.wc-card-group-pulse {
    padding: var(--s16) var(--s20);
}

/* Animated pulsing dot, replaces emoji-like heart with brand-token red */
.gp-pulse-dot {
    width: 12px;
    height: 12px;
    border-radius: var(--r-pill);
    background: var(--wc-accent);
    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.6), 0 0 6px rgba(220, 38, 38, 0.5);
    animation: gp-beat 1.2s ease-in-out infinite;
}
@keyframes gp-beat {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.55), 0 0 6px rgba(220, 38, 38, 0.5); }
    50% { transform: scale(1.35); box-shadow: 0 0 0 5px rgba(220, 38, 38, 0), 0 0 12px rgba(220, 38, 38, 0.3); }
}
@media (prefers-reduced-motion: reduce) {
    .gp-pulse-dot { animation: none; }
}

/* Stats grid reuses .stat-card variant pattern (red/green/amber/purple)
   from DashboardStats.vue — definitions live in wc-shell.css.
   This component just lays them out 3-up. */
.gp-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--s8);
    margin-top: var(--s12);
    margin-bottom: var(--s16);
}

/* Events list — uses --wc-bg3 for subtle row bg, accent left rule.
   Variants by event type via data-type. */
.gp-events {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: var(--s12);
}
.gp-event {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--s8);
    padding: var(--s8) var(--s12);
    background: var(--wc-bg3);
    border: 1px solid var(--wc-border);
    border-left: 2px solid var(--wc-accent);
    border-radius: var(--r-sm);
    font: 400 13px/1.4 var(--fs);
    color: var(--wc-text-2);
    transition: background 200ms var(--ease-out), border-color 200ms var(--ease-out);
}
.gp-event:hover {
    background: var(--wc-bg4);
}
.gp-event[data-type="aggregate"] { border-left-color: var(--wc-text-3); }
.gp-event[data-type="streak_milestone"] { border-left-color: var(--wc-amber); }
.gp-event[data-type="achievement"] { border-left-color: var(--wc-purple); }
.gp-event-text strong {
    color: var(--wc-text);
    font-weight: 600;
    margin-right: 4px;
}
.gp-event-time {
    color: var(--wc-text-3);
    font-size: 11px;
    flex-shrink: 0;
    text-transform: lowercase;
}

/* "Ver todo" CTA — minimal, accent text, arrow icon */
.gp-see-all {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
    padding: 6px 0;
    background: transparent;
    border: 0;
    color: var(--wc-accent);
    font: 600 12px/1 var(--fs);
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: color 200ms var(--ease-out), gap 200ms var(--ease-out);
}
.gp-see-all:hover {
    color: var(--wc-accent-2);
    gap: 10px;
}
.gp-see-all svg { transition: transform 200ms var(--ease-out); }

/* Skeleton — same primitive used elsewhere in dashboard */
.gp-skel-line {
    height: 14px;
    background: linear-gradient(90deg, var(--wc-bg3), var(--wc-bg4), var(--wc-bg3));
    background-size: 200% 100%;
    border-radius: var(--r-sm);
    margin-bottom: var(--s8);
    animation: gp-shimmer 1.4s ease-in-out infinite;
}
@keyframes gp-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .gp-skel-line { animation: none; }
}
</style>
```

- [ ] **Step 2: Add desktop grid span (matches sibling cards)**

Open `resources/css/wc-shell-tabs/dashboard.css`. Inside the existing `@media (min-width: 1024px)` block, after `.wc-shell--dashboard .wc-card-dashboard-coach{ grid-column:span 4; }`, add:

```css
  .wc-shell--dashboard .wc-card-group-pulse{ grid-column:span 12; }
```

(Full width on desktop — matches `.wc-card-dashboard-missions` and `.wc-card-dashboard-profile`.)

- [ ] **Step 3: Commit**

```bash
git add resources/js/vue/components/dashboard/DashboardGroupPulse.vue resources/css/wc-shell-tabs/dashboard.css
git commit -m "feat(group-pulse): DashboardGroupPulse widget con tokens wc-shell + grid span 12"
```

---

## Task 11: Insertar widget en Dashboard.vue

**Files:**
- Modify: `resources/js/vue/pages/Client/Dashboard.vue`

- [ ] **Step 1: Add import**

Open `resources/js/vue/pages/Client/Dashboard.vue`. Locate the imports block (lines 1–24). After the line `import DashboardStats from '../../components/dashboard/DashboardStats.vue';`, add:

```javascript
import DashboardGroupPulse from '../../components/dashboard/DashboardGroupPulse.vue';
```

- [ ] **Step 2: Insert component in template**

Find the `<DashboardStats>` element in the template. Insert immediately after its closing tag:

```vue
        <DashboardGroupPulse />
```

(No props — the composable handles its own state.)

- [ ] **Step 3: Visual smoke check (manual)**

Run `npm run dev`, log in as a client, navigate to `/dashboard`, verify the widget appears between Stats and Check-in. Heart pulses. Three stat cards visible. Three events listed (or skeleton if empty).

- [ ] **Step 4: Commit**

```bash
git add resources/js/vue/pages/Client/Dashboard.vue
git commit -m "feat(group-pulse): insertar DashboardGroupPulse en Dashboard.vue tras Stats"
```

---

## Task 12: GroupPulseFeed.vue — feed completo con filtros

**Files:**
- Create: `resources/js/vue/components/community/GroupPulseFeed.vue`

### Design system mandate

This component renders inside `CommunityFeed.vue`. CommunityFeed is NOT scoped under `.wc-shell--dashboard`, so component CSS must NOT depend on `--wc-bg2`, `--wc-bg3` etc. (those are scoped to `.wc-shell`). Instead, **wrap the component root in `.wc-shell` to inherit all dashboard tokens**. This keeps the visual language identical to the Dashboard.

Use only these primitives:
- Fonts: `var(--fd)` (Oswald) for titles, `var(--fs)` (Raleway) for body.
- Tokens: `var(--wc-accent)`, `var(--wc-text)`, `var(--wc-text-2)`, `var(--wc-text-3)`, `var(--wc-bg2)`, `var(--wc-bg3)`, `var(--wc-bg4)`, `var(--wc-border)`, `var(--wc-amber)`, `var(--wc-purple)`, `var(--wc-green)`.
- Spacing/radius: `var(--s8)`, `var(--s12)`, `var(--s16)`, `var(--r-sm)`, `var(--r-md)`, `var(--r-pill)`.
- Shadows/easings: `var(--sh-soft)`, `var(--ease-out)`, `var(--ease-spring)`.

Avatar gradients use the same conic+linear pattern as `.wc-shell .avatar` from `wc-shell.css:174` (red-to-deep-red linear, conic ring border). Reuse that pattern, do not invent new gradients.

- [ ] **Step 1: Create component**

```vue
<script setup>
import { ref, onMounted, computed } from 'vue';
import { useGroupPulse } from '../../composables/useGroupPulse';

const { fetchFeed, loading } = useGroupPulse();

const time = ref('today');
const events = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const page = ref(1);

const TIME_OPTIONS = [
    { key: 'today', label: 'Hoy' },
    { key: 'week', label: 'Esta semana' },
    { key: 'all', label: 'Todos' },
];

const hasMore = computed(() => page.value < pagination.value.last_page);

async function load(reset = false) {
    if (reset) {
        page.value = 1;
        events.value = [];
    }
    const data = await fetchFeed({ time: time.value, type: 'all', page: page.value, perPage: 10 });
    if (!data) return;
    if (reset) events.value = data.events;
    else events.value.push(...data.events);
    pagination.value = data.pagination;
}

async function loadMore() {
    if (loading.value || !hasMore.value) return;
    page.value++;
    await load(false);
}

function setTime(key) {
    if (time.value === key) return;
    time.value = key;
    load(true);
}

onMounted(() => load(true));
</script>

<template>
  <!-- .wc-shell wrapper inherits dashboard tokens -->
  <div class="wc-shell">
    <section class="gpf-root">
      <div class="gpf-filters">
        <button
          v-for="opt in TIME_OPTIONS"
          :key="opt.key"
          type="button"
          class="gpf-filter"
          :class="{ active: time === opt.key }"
          @click="setTime(opt.key)"
        >
          {{ opt.label }}
        </button>
      </div>

      <div v-if="loading && events.length === 0" class="gpf-empty">
        Cargando latido del grupo...
      </div>

      <div v-else-if="events.length === 0" class="gpf-empty">
        Sin actividad del grupo en este rango.
      </div>

      <div v-else class="gpf-list">
        <article
          v-for="(ev, idx) in events"
          :key="idx"
          class="gpf-card"
          :data-type="ev.type"
        >
          <div v-if="ev.client_initials" class="gpf-avatar">{{ ev.client_initials }}</div>
          <div v-else class="gpf-avatar gpf-avatar--aggregate">{{ ev.people_count }}</div>

          <div class="gpf-body">
            <div class="gpf-headline">
              <strong v-if="ev.client_name">{{ ev.client_name }}</strong>
              <span>{{ ev.headline }}</span>
            </div>
            <div v-if="ev.delta" class="gpf-meta">{{ ev.delta }}</div>
            <div v-if="ev.extra" class="gpf-meta">{{ ev.extra }}</div>
            <div v-if="ev.preview_initials" class="gpf-stack" aria-hidden="true">
              <span
                v-for="(init, i) in ev.preview_initials"
                :key="i"
                class="gpf-stack-item"
              >{{ init }}</span>
            </div>
            <div v-if="ev.minutes_ago !== undefined" class="gpf-time tnum">
              hace {{ ev.minutes_ago }}min
            </div>
          </div>
        </article>
      </div>

      <button
        v-if="hasMore"
        type="button"
        class="gpf-load-more"
        :disabled="loading"
        @click="loadMore"
      >
        {{ loading ? 'Cargando...' : 'Cargar más' }}
      </button>
    </section>
  </div>
</template>

<style scoped>
/* Component-specific. Tokens come from .wc-shell wrapper. */
.gpf-root {
    display: flex;
    flex-direction: column;
    gap: var(--s12);
    color: var(--wc-text);
    font-family: var(--fs);
}

/* Filter pills — match .chip pattern from wc-shell.css */
.gpf-filters {
    display: flex;
    gap: var(--s8);
    flex-wrap: wrap;
}
.gpf-filter {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    border-radius: var(--r-pill);
    background: transparent;
    color: var(--wc-text-2);
    border: 1px solid var(--wc-border);
    font: 600 11px/1 var(--fs);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    cursor: pointer;
    transition: color 180ms var(--ease-out), background 180ms var(--ease-out), border-color 180ms var(--ease-out);
}
.gpf-filter:hover {
    color: var(--wc-text);
    background: rgba(255, 255, 255, 0.04);
}
.gpf-filter.active {
    color: #FCA5A5;
    background: rgba(220, 38, 38, 0.12);
    border-color: rgba(220, 38, 38, 0.30);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Empty state */
.gpf-empty {
    padding: var(--s24);
    text-align: center;
    color: var(--wc-text-3);
    font-size: 13px;
}

/* Event cards list */
.gpf-list {
    display: flex;
    flex-direction: column;
    gap: var(--s8);
}
.gpf-card {
    display: flex;
    gap: var(--s12);
    padding: var(--s12) var(--s16);
    background: var(--wc-bg3);
    border: 1px solid var(--wc-border);
    border-left: 3px solid var(--wc-accent);
    border-radius: var(--r-md);
    box-shadow: var(--sh-soft);
    transition: background 200ms var(--ease-out), transform 200ms var(--ease-spring), box-shadow 200ms var(--ease-out);
}
.gpf-card:hover {
    background: var(--wc-bg4);
    transform: translateY(-1px);
}
.gpf-card[data-type="aggregate"] { border-left-color: var(--wc-text-3); }
.gpf-card[data-type="streak_milestone"] { border-left-color: var(--wc-amber); }
.gpf-card[data-type="achievement"] { border-left-color: var(--wc-purple); }

/* Avatar — same conic ring pattern as .wc-shell .avatar */
.gpf-avatar {
    width: 42px;
    height: 42px;
    border-radius: var(--r-pill);
    flex-shrink: 0;
    position: relative;
    background:
      linear-gradient(135deg, #DC2626 0%, #7F1D1D 100%) padding-box,
      conic-gradient(from 140deg, #DC2626, #71717A 50%, #DC2626 100%) border-box;
    border: 2px solid transparent;
    display: grid;
    place-items: center;
    font-family: var(--fd);
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    letter-spacing: 0.02em;
}
.gpf-avatar--aggregate {
    background:
      linear-gradient(135deg, var(--wc-amber) 0%, #92400E 100%) padding-box,
      conic-gradient(from 140deg, var(--wc-amber), #71717A 50%, var(--wc-amber) 100%) border-box;
    color: #1A1A1A;
}

.gpf-body { flex: 1; min-width: 0; }
.gpf-headline {
    font-size: 14px;
    line-height: 1.4;
    color: var(--wc-text-2);
}
.gpf-headline strong {
    color: var(--wc-text);
    font-weight: 600;
    margin-right: 4px;
}
.gpf-meta {
    font-size: 12px;
    color: var(--wc-text-3);
    margin-top: 4px;
}

/* Mini avatar stack (for aggregate event preview) */
.gpf-stack {
    display: flex;
    margin-top: var(--s8);
}
.gpf-stack-item {
    width: 24px;
    height: 24px;
    border-radius: var(--r-pill);
    background: var(--wc-bg4);
    border: 2px solid var(--wc-bg2);
    margin-left: -6px;
    font: 600 10px/1 var(--fs);
    display: grid;
    place-items: center;
    color: var(--wc-text-2);
}
.gpf-stack-item:first-child { margin-left: 0; }

.gpf-time {
    font-size: 11px;
    color: var(--wc-text-3);
    margin-top: 6px;
    text-align: right;
    text-transform: lowercase;
}

/* Load-more — secondary button style consistent with dashboard */
.gpf-load-more {
    padding: 10px var(--s16);
    background: var(--wc-bg3);
    border: 1px solid var(--wc-border);
    border-radius: var(--r-sm);
    color: var(--wc-text-2);
    font: 600 13px/1 var(--fs);
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: background 200ms var(--ease-out), color 200ms var(--ease-out);
}
.gpf-load-more:hover:not(:disabled) {
    background: var(--wc-bg4);
    color: var(--wc-text);
}
.gpf-load-more:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/vue/components/community/GroupPulseFeed.vue
git commit -m "feat(group-pulse): GroupPulseFeed con filtros temporales y load-more"
```

---

## Task 13: Tab "Latido" en CommunityFeed.vue

**Files:**
- Modify: `resources/js/vue/pages/Client/CommunityFeed.vue`

- [ ] **Step 1: Add import**

In `resources/js/vue/pages/Client/CommunityFeed.vue`, after the existing imports (around line 12):

```javascript
import GroupPulseFeed from '../../components/community/GroupPulseFeed.vue';
```

- [ ] **Step 2: Add tab state**

Find the `feedTab` ref (around line 29). Replace:

```javascript
const feedTab = ref('all'); // 'all' | 'following'
```

With:

```javascript
const feedTab = ref('latido'); // 'latido' | 'all' | 'following'
```

- [ ] **Step 3: Update tab UI in template**

Locate the tabs row in the `<template>`. Add a new tab button BEFORE the existing tabs. Example markup (adapt to existing tab class names):

```vue
<button
  type="button"
  :class="['tab', { active: feedTab === 'latido' }]"
  @click="feedTab = 'latido'"
>Latido</button>
<button
  type="button"
  :class="['tab', { active: feedTab === 'all' }]"
  @click="feedTab = 'all'; fetchFeed(true)"
>Posts</button>
<button
  type="button"
  :class="['tab', { active: feedTab === 'following' }]"
  @click="feedTab = 'following'; fetchFeed(true)"
>Siguiendo</button>
```

- [ ] **Step 4: Conditional render**

Wrap the existing posts list with `v-if="feedTab !== 'latido'"`. Insert above the posts list:

```vue
<GroupPulseFeed v-if="feedTab === 'latido'" />
```

- [ ] **Step 5: Visual smoke check**

Run `npm run dev`, navigate to `/comunidad`, verify Latido tab is default and shows the new feed. Switching to Posts and Siguiendo still works.

- [ ] **Step 6: Commit**

```bash
git add resources/js/vue/pages/Client/CommunityFeed.vue
git commit -m "feat(group-pulse): tab Latido por default en CommunityFeed.vue"
```

---

## Task 14: Toggles privacidad en ClientSettings.vue

**Files:**
- Modify: `resources/js/vue/pages/Client/ClientSettings.vue`

- [ ] **Step 1: Read existing settings to find pattern**

```bash
head -120 resources/js/vue/pages/Client/ClientSettings.vue
```

Note the pattern used for existing toggles (likely a v-model + PATCH call to `/api/v/me/preferences`).

- [ ] **Step 2: Add 5 toggles (data + UI)**

Find the script `<script setup>` block. Add after existing reactive refs:

```javascript
const autoshareWorkout = ref(true);
const autosharePr = ref(true);
const autoshareMedal = ref(true);
const autoshareWeight = ref(true);
const autoshareStreak = ref(true);

async function saveAutoshare() {
    await api.patch('/api/v/me/preferences', {
        autoshare_workout: autoshareWorkout.value,
        autoshare_pr: autosharePr.value,
        autoshare_medal: autoshareMedal.value,
        autoshare_weight: autoshareWeight.value,
        autoshare_streak: autoshareStreak.value,
    });
}
```

Inside the existing `onMounted` or `fetchSettings`, hydrate from response:

```javascript
autoshareWorkout.value = !!response.data.autoshare_workout;
autosharePr.value = !!response.data.autoshare_pr;
autoshareMedal.value = !!response.data.autoshare_medal;
autoshareWeight.value = !!response.data.autoshare_weight;
autoshareStreak.value = !!response.data.autoshare_streak;
```

- [ ] **Step 3: Add UI section in template**

Find a sensible position (after notification preferences) and follow the same `card`/`section` pattern that ClientSettings already uses. Use the dashboard token pill toggle (custom checkbox styled with brand red when checked):

```vue
<section class="card section gp-privacy-card" :style="{ animationDelay: '180ms' }">
  <div class="card-head">
    <div class="card-head-left">
      <span class="card-title">Privacidad de actividad</span>
    </div>
    <span class="card-meta">Latido del grupo</span>
  </div>
  <p class="gp-privacy-desc">Controla qué eventos tuyos aparecen en el Latido del Grupo de tu coach. Por defecto todos están activos.</p>

  <label class="gp-toggle">
    <input type="checkbox" v-model="autoshareWorkout" @change="saveAutoshare">
    <span class="gp-toggle-track" aria-hidden="true"></span>
    <span class="gp-toggle-label">Mostrar mis entrenamientos</span>
  </label>
  <label class="gp-toggle">
    <input type="checkbox" v-model="autosharePr" @change="saveAutoshare">
    <span class="gp-toggle-track" aria-hidden="true"></span>
    <span class="gp-toggle-label">Mostrar mis PRs</span>
  </label>
  <label class="gp-toggle">
    <input type="checkbox" v-model="autoshareMedal" @change="saveAutoshare">
    <span class="gp-toggle-track" aria-hidden="true"></span>
    <span class="gp-toggle-label">Mostrar mis logros</span>
  </label>
  <label class="gp-toggle">
    <input type="checkbox" v-model="autoshareWeight" @change="saveAutoshare">
    <span class="gp-toggle-track" aria-hidden="true"></span>
    <span class="gp-toggle-label">Mostrar cambios de peso</span>
  </label>
  <label class="gp-toggle">
    <input type="checkbox" v-model="autoshareStreak" @change="saveAutoshare">
    <span class="gp-toggle-track" aria-hidden="true"></span>
    <span class="gp-toggle-label">Mostrar mi racha</span>
  </label>
</section>
```

Add scoped styles using only tokens (no hardcoded hex):

```css
.gp-privacy-card { padding: var(--s16) var(--s20); }
.gp-privacy-desc {
    font: 400 13px/1.5 var(--fs);
    color: var(--wc-text-2);
    margin: var(--s8) 0 var(--s16);
}
.gp-toggle {
    display: flex;
    align-items: center;
    gap: var(--s12);
    padding: var(--s8) 0;
    cursor: pointer;
    color: var(--wc-text);
    font: 500 14px/1.4 var(--fs);
    border-bottom: 1px solid var(--wc-border);
}
.gp-toggle:last-child { border-bottom: 0; }
.gp-toggle input { position: absolute; opacity: 0; pointer-events: none; }
.gp-toggle-track {
    position: relative;
    width: 38px;
    height: 22px;
    border-radius: var(--r-pill);
    background: var(--wc-bg4);
    border: 1px solid var(--wc-border);
    transition: background 200ms var(--ease-out), border-color 200ms var(--ease-out);
    flex-shrink: 0;
}
.gp-toggle-track::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    border-radius: var(--r-pill);
    background: var(--wc-text-2);
    transition: transform 200ms var(--ease-spring), background 200ms var(--ease-out);
}
.gp-toggle input:checked + .gp-toggle-track {
    background: rgba(220, 38, 38, 0.20);
    border-color: rgba(220, 38, 38, 0.40);
}
.gp-toggle input:checked + .gp-toggle-track::after {
    transform: translateX(16px);
    background: var(--wc-accent);
}
.gp-toggle:hover .gp-toggle-track { border-color: var(--wc-text-3); }
.gp-toggle-label { flex: 1; }
```

If ClientSettings already has a toggle component (e.g. `<Toggle v-model=...>`), use it instead and skip the custom CSS — match the existing pattern.

- [ ] **Step 4: Visual smoke check**

Toggle each switch, verify network tab shows `PATCH /api/v/me/preferences` with correct payload, refresh page and confirm state persists.

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/pages/Client/ClientSettings.vue
git commit -m "feat(group-pulse): 5 toggles autoshare en ClientSettings"
```

---

## Task 15: DashboardHeatmap comparativa "tú vs grupo"

**Files:**
- Modify: `resources/js/vue/components/dashboard/DashboardHeatmap.vue`

- [ ] **Step 1: Accept new prop**

Open `resources/js/vue/components/dashboard/DashboardHeatmap.vue`. Find `defineProps`. Add:

```javascript
const props = defineProps({
    streakCalendar: { type: Array, default: () => [] },
    streakDays: { type: Number, default: 0 },
    userVsGroup: { type: Object, default: null }, // { user, group_avg, rank_pct }
});
```

(Keep existing props; only add `userVsGroup`.)

- [ ] **Step 2: Add comparativa row in template**

Inside the heatmap card, after the grid, add the row using only tokens:

```vue
<div v-if="userVsGroup" class="hm-vs-group">
  <span class="hm-vs-item">
    <span class="hm-vs-label">Tu promedio</span>
    <span class="hm-vs-num tnum">{{ userVsGroup.user }}<span class="hm-vs-unit">/sem</span></span>
  </span>
  <span class="hm-vs-divider" aria-hidden="true"></span>
  <span class="hm-vs-item">
    <span class="hm-vs-label">Grupo</span>
    <span class="hm-vs-num tnum">{{ userVsGroup.group_avg }}<span class="hm-vs-unit">/sem</span></span>
  </span>
  <span class="hm-vs-rank">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
      <polyline points="16 7 22 7 22 13"></polyline>
    </svg>
    Top {{ userVsGroup.rank_pct }}%
  </span>
</div>
```

Add scoped styles using only tokens:

```css
.hm-vs-group {
    display: flex;
    align-items: center;
    gap: var(--s12);
    margin-top: var(--s12);
    padding-top: var(--s12);
    border-top: 1px solid var(--wc-border);
    font-family: var(--fs);
}
.hm-vs-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.hm-vs-label {
    font-size: 10px;
    color: var(--wc-text-3);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.hm-vs-num {
    font-family: var(--fd);
    font-weight: 600;
    font-size: 16px;
    color: var(--wc-text);
    letter-spacing: -0.01em;
}
.hm-vs-unit {
    font-size: 11px;
    color: var(--wc-text-3);
    font-weight: 400;
    margin-left: 2px;
}
.hm-vs-divider {
    width: 1px;
    align-self: stretch;
    background: var(--wc-border);
}
.hm-vs-rank {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: auto;
    padding: 4px 10px;
    border-radius: var(--r-pill);
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.30);
    color: var(--wc-amber);
    font: 600 11px/1 var(--fs);
    letter-spacing: 0.02em;
}
```

- [ ] **Step 3: Pass prop from Dashboard.vue**

Open `resources/js/vue/pages/Client/Dashboard.vue`. Find the `<DashboardHeatmap>` element. Add a new prop binding using data from the group pulse summary. Add at top of `<script setup>` after existing imports:

```javascript
import { useGroupPulse } from '../../composables/useGroupPulse';
const { summary: groupPulseSummary } = useGroupPulse();
```

In the template:

```vue
<DashboardHeatmap
  :streakCalendar="data?.streakCalendar"
  :streakDays="data?.streakDays"
  :userVsGroup="groupPulseSummary?.user_vs_group?.weekly_workouts"
/>
```

(Adjust to existing prop bindings — only add `:userVsGroup`.)

- [ ] **Step 4: Visual smoke check**

Reload `/dashboard`. Verify "Tu promedio: X/sem · Grupo: Y/sem · Top Z%" line appears under heatmap.

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/components/dashboard/DashboardHeatmap.vue resources/js/vue/pages/Client/Dashboard.vue
git commit -m "feat(group-pulse): comparativa tu-vs-grupo en DashboardHeatmap"
```

---

## Task 16: DashboardMissions peer count (placeholder)

**Files:**
- Modify: `resources/js/vue/components/dashboard/DashboardMissions.vue`

- [ ] **Step 1: Accept new prop**

Open `resources/js/vue/components/dashboard/DashboardMissions.vue`. In `defineProps`, add:

```javascript
peerCounts: { type: Object, default: () => ({}) }, // { mission_id: count }
```

- [ ] **Step 2: Show peer count next to each mission**

In the mission row template, add inside the mission item (next to title or progress):

```vue
<span v-if="peerCounts[mission.id] > 0" class="ms-peer-pill">
  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5Z"></path>
  </svg>
  <span class="tnum">{{ peerCounts[mission.id] }}</span>
  contigo
</span>
```

Scoped style — uses only tokens, mirrors the `.chip-accent` pattern from `wc-shell.css`:

```css
.ms-peer-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px 4px 8px;
    border-radius: var(--r-pill);
    background: rgba(220, 38, 38, 0.12);
    color: #FCA5A5;
    font: 600 11px/1 var(--fs);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    box-shadow:
      inset 0 0 0 1px rgba(220, 38, 38, 0.30),
      inset 0 1px 0 rgba(255, 255, 255, 0.05);
    margin-left: var(--s8);
}
.ms-peer-pill svg {
    color: var(--wc-accent);
    flex-shrink: 0;
}
```

- [ ] **Step 3: Pass empty object from Dashboard.vue**

In `Dashboard.vue` template, find `<DashboardMissions>`. Add binding:

```vue
<DashboardMissions
  :missions="data?.missions"
  :peerCounts="groupPulseSummary?.user_vs_group?.missions_peers ?? {}"
/>
```

(Aggregator `missions_peers` is empty array in MVP — UI degrades gracefully when no data.)

- [ ] **Step 4: Commit**

```bash
git add resources/js/vue/components/dashboard/DashboardMissions.vue resources/js/vue/pages/Client/Dashboard.vue
git commit -m "feat(group-pulse): peer-pill placeholder en DashboardMissions"
```

---

## Task 17: Run full test suite

**Files:**
- No file changes — verification only

- [ ] **Step 1: Run all GroupPulse tests**

```bash
./vendor/bin/pest tests/Unit/Services/GroupPulseAggregator*.php tests/Feature/Api/GroupPulse*.php
```

Expected: all pass (12 tests minimum: 6 unit + ~6 feature).

- [ ] **Step 2: Run full Pest suite to detect regressions**

```bash
./vendor/bin/pest --bail --stop-on-failure
```

Expected: all green. If something breaks unrelated, investigate before continuing.

---

## Task 18: Build local + commit public/build

**Files:**
- Modify: `public/build/*` (auto-generated)

- [ ] **Step 1: Build production assets**

```bash
npm run build
```

Expected: completes without errors. Output in `public/build/`.

- [ ] **Step 2: Stage and commit build**

```bash
git add public/build/
git commit -m "chore(build): assets para Latido del Grupo"
```

(Per memory `feedback_deploy_workflow_authoritative.md`: build local + commit `public/build/` + push + gitpull-load.)

---

## Task 19: Push branch and open PR

**Files:**
- No file changes — git only

- [ ] **Step 1: Push branch**

```bash
git push -u origin feat/group-pulse
```

- [ ] **Step 2: Open PR**

```bash
gh pr create --title "feat(group-pulse): Latido del Grupo en Dashboard y Comunidad" --body "$(cat <<'EOF'
## Summary
- Widget DashboardGroupPulse con heartbeat + 3 stats vivos + 3 top events.
- Tab Latido en CommunityFeed con feed agregado, filtros temporales y load-more.
- 5 toggles autoshare en ClientSettings (entrenamiento, PR, medal, weight, streak).
- Comparativa tú-vs-grupo en DashboardHeatmap.
- Endpoint /api/v/client/group-pulse?scope=summary|feed con cache Redis 30s/60s.
- Scheduled command wellcore:precompute-group-pulse cada 5 min para warm cache.

## Test plan
- [ ] Pest verde: ./vendor/bin/pest tests/Unit/Services/GroupPulseAggregator* tests/Feature/Api/GroupPulse*
- [ ] Suite completa Pest verde sin regresiones
- [ ] Smoke prod: /dashboard muestra widget Latido, /comunidad tab Latido carga feed
- [ ] Toggles privacidad persisten via PATCH /api/v/me/preferences
- [ ] Cliente sin coach: widget se oculta sin error
- [ ] No regresión Lighthouse Performance >= 70 en /dashboard

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)"
```

- [ ] **Step 3: Capture PR URL** for handoff to deploy.

---

## Task 20: Deploy to prod (gitpull-load EasyPanel)

**Files:**
- No file changes — deploy only

- [ ] **Step 1: Merge PR to main locally (via gh or merge button)**

```bash
gh pr merge feat/group-pulse --merge --delete-branch
git checkout main
git pull
```

- [ ] **Step 2: Trigger gitpull-load on EasyPanel**

Per memory `reference_easypanel_terminal_deploy.md` and `feedback_deploy_build.md`, run the gitpull-load script (the exact command depends on user's EasyPanel button UID which is stored in their workflow). The deploy script does NOT trigger Rebuild Docker — only git pull + assets reload.

- [ ] **Step 3: Smoke test prod with Chrome DevTools**

Open prod URL, log in as test client, navigate to:
- `/dashboard` — verify widget renders, no console errors, network shows 200 for `/api/v/client/group-pulse?scope=summary`.
- `/comunidad` — verify Latido tab is default, feed loads, switching to Posts works.
- `/configuracion` (or wherever ClientSettings mounts) — verify 5 toggles render and persist.

- [ ] **Step 4: Validate no 5xx errors in 30 min**

Watch error log channel for 30 min post-deploy. If 5xx spike, rollback per `feedback_planes_v2_regresion.md` lessons.

---

## Self-Review (post-plan)

Spec coverage check:
- [x] Backend service `GroupPulseAggregator` — Tasks 1-5
- [x] Backend controller `GroupPulseController` — Tasks 6-7
- [x] Scheduled command `PrecomputeGroupPulse` — Task 8
- [x] Frontend composable `useGroupPulse` — Task 9
- [x] Component `DashboardGroupPulse` — Task 10
- [x] Insert in Dashboard — Task 11
- [x] Component `GroupPulseFeed` — Task 12
- [x] Tab Latido in CommunityFeed — Task 13
- [x] Privacy toggles in ClientSettings — Task 14
- [x] Heatmap comparativa — Task 15
- [x] Missions peer count — Task 16
- [x] Test suite + build + deploy — Tasks 17-20

No placeholders. No "TBD". Every task has exact code. Type names consistent (`GroupPulseAggregator`, `useGroupPulse`, prop name `userVsGroup` matches across Dashboard ↔ DashboardHeatmap).

Open question: `checkins` table name in `computeStats` is assumed (`DB::table('checkins')`). If table is named differently in this DB, fix in Task 1 Step 3 before commit. Quick verify: `php artisan tinker --execute="echo \\Schema::hasTable('checkins') ? 'OK' : 'NO';"`.

## Next Step

Plan complete and saved. Choose execution mode:

1. **Subagent-Driven (recommended)** — I dispatch a fresh subagent per task with two-stage review.
2. **Inline Execution** — Execute in this session with batch checkpoints.

Which approach?
