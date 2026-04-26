# Mobile UI Bugs Fixes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix 9 reported mobile UI/UX defects across the WellCore client and coach portals (FAB collisions, broken routes, dashboard staleness, dead nutrition link, email logo).

**Architecture:** Tactical, localized changes. Touches 8 Vue files plus 1 Blade email. No DB migrations, no new endpoints, no new major components. Each fix is independent and can ship/revert separately. One backend audit step for the dashboard discrepancy.

**Tech Stack:** Vue 3.5 + TypeScript + Pinia + Vue Router 4, Tailwind CSS 4, Laravel 13.1.1 + PHP 8.4, PHPUnit (Feature), Vite 8.

**Companion spec:** `docs/superpowers/specs/2026-04-25-mobile-ui-bugs-fixes-design.md`

---

## File map

| File | Touched in task | Why |
|------|----------------|-----|
| `resources/js/vue/components/dashboard/DashboardFab.vue` | Task 1 | Lift FAB above workout bottom bar |
| `resources/js/vue/layouts/CoachLayout.vue` | Tasks 2, 7 | FAB target + impersonation pad |
| `resources/js/vue/pages/Coach/Dashboard.vue` | Tasks 3, 4 | Tickets shortcut + polling/refresh |
| `app/Http/Controllers/Api/CoachController.php` | Task 4 | Audit only — no changes expected |
| `resources/js/vue/pages/Client/CheckinForm.vue` | Task 5 | Mobile sticky CTA z-index/position |
| `resources/js/vue/composables/useImpersonation.js` | Task 6 (NEW) | Reactive flag for any impersonation |
| `resources/js/vue/layouts/ClientLayout.vue` | Task 6 | Apply pad/shift when impersonating |
| `resources/js/vue/components/dashboard/DashboardMissions.vue` | Task 7 | Mission route map |
| `resources/js/vue/pages/Client/PlanViewer.vue` | Task 7 | Read `?tab=` query |
| `resources/js/vue/router/index.js` | Task 8 | Redirect dead `/client/nutrition` |
| `resources/js/vue/pages/Client/NutritionPlan.vue` | Task 8 | Delete if no remaining usage |
| `resources/views/emails/coach-credentials.blade.php` | Task 9 | Real WellCore logo |

---

## Task 1: Fix 1 — Client FAB collides with active workout bottom bar

**Files:**
- Modify: `resources/js/vue/components/dashboard/DashboardFab.vue`

- [ ] **Step 1: Add the route awareness import**

In `resources/js/vue/components/dashboard/DashboardFab.vue`, replace the existing imports block (around lines 1–7):

```js
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';

const router = useRouter();
const route = useRoute();
const haptics = useHaptics();
```

- [ ] **Step 2: Add a computed for the bottom offset**

Immediately after `const open = ref(false);` and `const fabEl = ref(null);`, add:

```js
// When inside a started workout, lift the FAB above the bottom action bar
// (Abandonar / Completar Sesión) anchored at bottom-0 of the workout view.
const isInActiveWorkout = computed(() => /^\/client\/workout(\/|$)/.test(route.path));
const bottomOffset = computed(() => isInActiveWorkout.value ? '11rem' : '5rem');
```

- [ ] **Step 3: Use the computed offset in the FAB container style**

Replace the existing `:style` block on the FAB container (currently around line 99–101) with:

```vue
<div
    ref="fabEl"
    class="fixed z-50 flex flex-col items-end gap-3 lg:hidden"
    :style="{
      right: 'calc(1rem + env(safe-area-inset-right))',
      bottom: `calc(${bottomOffset} + env(safe-area-inset-bottom))`
    }"
  >
```

- [ ] **Step 4: Verify the build compiles**

Run: `npm run build`
Expected: Build succeeds without errors.

- [ ] **Step 5: Manual verification (DevTools mobile)**

1. Run `npm run dev`
2. Open `http://wellcore-laravel.test/client/workout/1` on iPhone 14 Pro viewport.
3. Tap "Iniciar entrenamiento" so the bottom bar (`Abandonar` / `Completar Sesión`) appears.
4. Confirm the red `+` FAB is clearly above the bottom bar — no overlap.
5. Navigate to `/client` (dashboard). FAB should sit at its original `5rem` height.

- [ ] **Step 6: Commit**

```bash
git add resources/js/vue/components/dashboard/DashboardFab.vue
git commit -m "fix(client-fab): lift FAB above bottom action bar during workout"
```

---

## Task 2: Fix 2 — Coach FAB "Agregar cliente" routes to wrong page

**Files:**
- Modify: `resources/js/vue/layouts/CoachLayout.vue`

- [ ] **Step 1: Locate the FAB sheet entry**

In `resources/js/vue/layouts/CoachLayout.vue`, find the `<RouterLink>` for "Agregar cliente" (around line 425). It currently reads:

```vue
<RouterLink to="/coach/clients" @click="fabOpen = false" class="...">
```

(Note: the source file shows a backslash `\coach\clients` due to Windows path autoformat; verify exact characters before editing.)

- [ ] **Step 2: Change the target route**

Replace the `to` value to point at the invitations manager:

```vue
<RouterLink to="/coach/invitations" @click="fabOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-card hover:bg-wc-bg-tertiary transition-colors">
```

Keep all other attributes (`@click`, `class`) untouched.

- [ ] **Step 3: Manual verification**

1. Run `npm run build` then `npm run dev` (or rebuild assets).
2. Login as coach.
3. On mobile viewport, tap the floating `+` button on `/coach`.
4. Tap "Agregar cliente · Invitar nuevo cliente al programa".
5. Expect navigation to `/coach/invitations` (Invitations Manager view).

- [ ] **Step 4: Commit**

```bash
git add resources/js/vue/layouts/CoachLayout.vue
git commit -m "fix(coach-fab): route 'Agregar cliente' to /coach/invitations"
```

---

## Task 3: Fix 3 — Coach Dashboard "Tickets" mobile shortcut

**Files:**
- Modify: `resources/js/vue/pages/Coach/Dashboard.vue`

- [ ] **Step 1: Locate the mobile Tickets quick-action**

In `resources/js/vue/pages/Coach/Dashboard.vue`, find the mobile quick-actions strip (look for the section under the comment `<!-- Quick actions strip (scroll horizontal) -->`, around line 198). The Tickets `<RouterLink>` is around line 209 and reads:

```vue
<RouterLink to="/coach/plan-tickets" class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 min-w-[72px] h-[72px]">
```

- [ ] **Step 2: Change the target to the create form**

Update the `to` attribute:

```vue
<RouterLink to="/coach/plan-tickets/nuevo" class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 min-w-[72px] h-[72px]">
```

Keep all SVGs, label, classes untouched.

- [ ] **Step 3: Verify the create route exists in the Vue router**

Run: `grep -n "plan-tickets/nuevo\|plan-tickets-new\|coach-plan-tickets" resources/js/vue/router/index.js`
Expected: A route definition like `{ path: '/coach/plan-tickets/nuevo', ... }`. If the route does not exist, the fix needs the route to be added before this task ships — flag and pause.

- [ ] **Step 4: Manual verification**

1. Login as coach on mobile viewport.
2. Tap the "Tickets" tile in the quick-actions strip.
3. Expect to land on the new-ticket form (not the list).
4. Confirm the right-rail "Ver todos →" link still navigates to the list.

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/pages/Coach/Dashboard.vue
git commit -m "fix(coach-dashboard): mobile Tickets shortcut opens create form"
```

---

## Task 4: Fix 4 — Coach Dashboard real-time + "0 clientes" stale counter

**Files:**
- Modify: `resources/js/vue/pages/Coach/Dashboard.vue`
- Audit: `app/Http/Controllers/Api/CoachController.php` (no changes expected)

- [ ] **Step 1: Backend consistency check**

Open `app/Http/Controllers/Api/CoachController.php`. Verify both `dashboard()` (around line 112) and `clients()` (around line 437) call `getCoachClientIds($coachId)` and apply the same filter `Client::whereIn('id', $clientIds)->where('status', 'activo')`. Read the two sections side-by-side and confirm there is no extra filter on `dashboard()` that would shrink the set.

If the filters are identical: continue.
If they differ: the spec says the dashboard count must equal the client list count. Adjust whichever is wrong so they share the exact same query (extract a private method if useful).

- [ ] **Step 2: Spot-check the data with the live database**

Login to the coach account `coachdann` (password: KingLord6962 — see CLAUDE.md test credentials). Run from another terminal:

```bash
php artisan tinker --execute="\$coachId = \App\Models\Coach::where('username','coachdann')->value('id'); \$ctrl = new \App\Http\Controllers\Api\CoachController; \$method = (new \ReflectionClass(\$ctrl))->getMethod('getCoachClientIds'); \$method->setAccessible(true); \$ids = \$method->invoke(\$ctrl, \$coachId); \$count = \App\Models\Client::whereIn('id',\$ids)->where('status','activo')->count(); echo \"clientIds=\".\$ids->count().\" active=\".\$count.\"\n\";"
```

Expected: prints something like `clientIds=2 active=2`. Record the numbers in your work log.

If the counts already match what the dashboard shows in the UI: the bug is purely on the frontend. If they don't match: there's a real backend mismatch — investigate and fix in this same task before moving to the frontend changes.

- [ ] **Step 3: Refactor counter animation — set value first, animate as cosmetic**

In `resources/js/vue/pages/Coach/Dashboard.vue`, find `animateCounter` (around line 50) and `setupCounterObserver` (around line 69). Replace the entire `setupCounterObserver` function and its caller block in `loadDashboard` with the simpler approach below.

Replace `setupCounterObserver()` (lines 69–90) with:

```js
function syncCounters() {
    // Single source of truth: animatedCounters mirrors stats once data arrives.
    // The IntersectionObserver was unreliable on mobile when the API resolved
    // after the observer fired (showed 0 even though stats had real values).
    const targets = {
        activeClients: stats.value.activeClients,
        pendingCheckins: stats.value.pendingCheckins,
        unreadMessages: stats.value.unreadMessages,
        ticketsThisMonth: stats.value.ticketsThisMonth,
        openTickets: openTickets.value,
    };
    Object.entries(targets).forEach(([key, value]) => {
        animateCounter(key, value, 800);
    });
}
```

Remove the line `let counterObserver = null;` (it's no longer needed).

Inside `loadDashboard()`, find the lines:

```js
        await nextTick();
        setupCounterObserver();
```

Replace with:

```js
        await nextTick();
        syncCounters();
```

In `onBeforeUnmount`, remove `counterObserver?.disconnect();` (now obsolete) but keep the `counterAnimationFrames` cleanup.

- [ ] **Step 4: Add polling + visibility refresh**

Inside `<script setup>` in the same file, after the `onMounted(loadDashboard);` call (around line 160), add:

```js
const POLL_INTERVAL_MS = 30_000;
let pollTimer = null;

function startPolling() {
    stopPolling();
    pollTimer = setInterval(() => {
        if (document.visibilityState === 'visible') {
            loadDashboard();
        }
    }, POLL_INTERVAL_MS);
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

function handleVisibility() {
    if (document.visibilityState === 'visible') {
        loadDashboard();
        startPolling();
    } else {
        stopPolling();
    }
}

onMounted(() => {
    startPolling();
    document.addEventListener('visibilitychange', handleVisibility);
});

onBeforeUnmount(() => {
    stopPolling();
    document.removeEventListener('visibilitychange', handleVisibility);
});
```

There are now two `onMounted` calls — Vue allows that and runs them in order. Keep the existing `onMounted(loadDashboard);` as-is.

- [ ] **Step 5: Build assets**

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 6: Manual verification — counter correctness**

1. `npm run dev`, login as coachdann.
2. Land on `/coach`. Confirm the "CLIENTES ACTIVOS" tile shows 2 (or whatever count the spot-check returned in Step 2), not 0.
3. Confirm the other 3 tiles (CHECK-INS, MENSAJES, TICKETS ABIERTOS) match `/coach/clients` and `/coach/plan-tickets` data.

- [ ] **Step 7: Manual verification — polling**

1. With the dashboard open, open Chrome DevTools → Network → filter by `dashboard`.
2. Wait 60 seconds with the tab visible.
3. Expect 2 new requests to `/api/v/coach/dashboard` (one per 30 s interval).

- [ ] **Step 8: Manual verification — visibility refresh**

1. Switch to another tab for 5 seconds, then come back.
2. Expect an immediate request to `/api/v/coach/dashboard`.
3. Switch away again. Wait 60 s. Expect zero new requests while away.

- [ ] **Step 9: Commit**

```bash
git add resources/js/vue/pages/Coach/Dashboard.vue
git commit -m "fix(coach-dashboard): live counters via polling + visibility refresh"
```

If Step 1/2 surfaced a backend mismatch, also include `app/Http/Controllers/Api/CoachController.php` in the same commit.

---

## Task 5: Fix 5 — Check-in semanal mobile sticky CTA hidden

**Files:**
- Modify: `resources/js/vue/pages/Client/CheckinForm.vue`

- [ ] **Step 1: Raise the z-index of the mobile sticky CTA**

Open `resources/js/vue/pages/Client/CheckinForm.vue`. Find the mobile sticky CTA block at line ~583:

```vue
<div class="fixed inset-x-0 bottom-0 z-30 border-t border-wc-border bg-wc-bg/95 backdrop-blur sm:hidden" v-if="!loading && !error">
```

Replace the `class` attribute with the new positioning:

```vue
<div class="fixed inset-x-0 z-40 border-t border-wc-border bg-wc-bg/95 backdrop-blur sm:hidden" style="bottom: calc(4rem + env(safe-area-inset-bottom));" v-if="!loading && !error">
```

Key changes:
- `z-30` → `z-40` (above the client bottom-nav).
- `bottom-0` removed; replaced with inline `style="bottom: calc(4rem + env(safe-area-inset-bottom));"` so the CTA sits above the layout's bottom navigation.

- [ ] **Step 2: Add bottom padding so form fields don't hide behind the CTA**

In the same file, find the wizard's outer container — the element that wraps the `<Transition name="wizard-fade">` (typically a `<div>` near line ~340 with the form panels inside). Add `pb-32 sm:pb-0` to its class list so it leaves ~8 rem of breathing room on mobile only.

If the wrapper currently has `class="..."`, append: `... pb-32 sm:pb-0`. If unsure which wrapper is correct, choose the one immediately surrounding the `<CheckinProgress>` + `<Transition>` block.

- [ ] **Step 3: Build assets**

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 4: Manual verification on iPhone viewport**

1. Login as `cristian` (test client; or impersonate via coach).
2. Navigate to `/client/checkin` on iPhone 14 Pro viewport.
3. On step 1 (Bienestar): "Siguiente paso" button visible at the bottom, NOT covered by `Dashboard / Plan / Métricas / Chat / Perfil` nav.
4. Tap "Siguiente paso" → step 2 (Entreno) loads.
5. Verify "Atrás" + "Siguiente paso" both visible.
6. Repeat for step 3 (Nutrición) → step 4 (Notas).
7. On step 4, the button reads "Enviar check-in" or "Disponible el viernes" depending on the day.

- [ ] **Step 5: Commit**

```bash
git add resources/js/vue/pages/Client/CheckinForm.vue
git commit -m "fix(client-checkin): mobile sticky CTA above bottom-nav with form padding"
```

---

## Task 6: Fix 6 — Coach impersonation banner overlaps client topnav

**Files:**
- Create: `resources/js/vue/composables/useImpersonation.js`
- Modify: `resources/js/vue/layouts/ClientLayout.vue`
- Modify: `resources/js/vue/layouts/RiseLayout.vue` (only if it has a similar header sticky pattern)

- [ ] **Step 1: Create the impersonation composable**

Write `resources/js/vue/composables/useImpersonation.js` with this content:

```js
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

// Module-level singleton — every layout that uses this composable
// reads/writes the same refs so cross-tab updates stay consistent.
const isImpersonatingByAdmin = ref(false);
const isImpersonatingByCoach = ref(false);

function refreshFromStorage() {
    isImpersonatingByAdmin.value = localStorage.getItem('wc_impersonating_by_admin') === '1'
        || localStorage.getItem('admin_impersonating') === '1';
    isImpersonatingByCoach.value = localStorage.getItem('wc_impersonating_by_coach') === '1';
}

let listenerAttached = false;

export function useImpersonation() {
    const anyImpersonation = computed(() => isImpersonatingByAdmin.value || isImpersonatingByCoach.value);

    onMounted(() => {
        refreshFromStorage();
        if (!listenerAttached) {
            window.addEventListener('storage', refreshFromStorage);
            listenerAttached = true;
        }
    });

    onBeforeUnmount(() => {
        // Listener stays attached for the lifetime of the page since
        // multiple components share the singleton state.
    });

    return {
        isImpersonatingByAdmin,
        isImpersonatingByCoach,
        anyImpersonation,
        refresh: refreshFromStorage,
    };
}
```

Note on the admin localStorage key: confirm by `grep -n "admin_impersonating\|wc_impersonating_by_admin" resources/js/vue` and adjust the key name if the project uses a different one. If only one of the two keys is in active use, leave the other line as a defensive fallback.

- [ ] **Step 2: Wire the composable into ClientLayout.vue**

Open `resources/js/vue/layouts/ClientLayout.vue`. In the `<script setup>` block, near the existing imports, add:

```js
import { useImpersonation } from '../composables/useImpersonation';
```

Below the existing `const sidebarOpen = ref(false);` (around line 26), add:

```js
const { anyImpersonation } = useImpersonation();
```

- [ ] **Step 3: Replace the existing topnav offset logic**

Find the sticky header element (around line 319) currently using:

```vue
<header class="sticky z-30 flex h-16 items-center justify-between gap-3 border-b border-wc-border bg-wc-bg/80 px-4 backdrop-blur-xl sm:px-6" :class="isImpersonating ? 'top-10' : 'top-0'">
```

Replace `:class="isImpersonating ? 'top-10' : 'top-0'"` with:

```vue
:class="anyImpersonation ? 'top-10' : 'top-0'"
```

Find the existing admin impersonation banner near line 219:

```vue
<div v-if="isImpersonating" class="fixed top-0 left-0 right-0 z-[90] flex items-center justify-center gap-3 bg-amber-500 px-4 py-2 text-sm font-medium text-black">
```

Leave it as-is (admin banner is amber and stays z-90; coach banner is already z-100 and renders above it on the rare nested case). The header offset is now driven by `anyImpersonation`, so both impersonation modes get the right pad.

- [ ] **Step 4: Pad the main content area when impersonating**

Find the main content wrapper / the element that wraps the `<router-view>` or `<slot>` of the layout. If the wrapper does not already account for the impersonation banner height, add a class:

```vue
:class="anyImpersonation ? 'pt-10' : ''"
```

Apply this to whichever element holds the page content (typically `<main>` or the parent `<div>` of the routed view).

- [ ] **Step 5: Apply same pattern to RiseLayout.vue if it has the same structure**

Run: `grep -n "isImpersonating\|sticky.*top" resources/js/vue/layouts/RiseLayout.vue`

If output shows the same sticky/impersonation pattern, repeat Steps 2–4 inside `RiseLayout.vue`. If RiseLayout doesn't use the sticky-with-impersonation pattern, skip — flag in commit message.

- [ ] **Step 6: Build assets**

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 7: Manual verification**

1. Login as a coach with active clients (`coachdann` / `KingLord6962`).
2. Navigate to `/coach/clients`, choose a client, tap "Ver como cliente".
3. Confirm the red banner "Viendo como [name]" is at the top.
4. Confirm BELOW it, the client header (hamburger menu, notifications bell, dark-mode toggle, avatar) is fully visible — not hidden behind the banner.
5. Scroll down — the header stays sticky at `top-10`, banner stays at `top-0`. No overlap.
6. Tap "Volver al portal coach" — verify the banner disappears and the header snaps back to `top-0`.
7. Repeat sanity check for admin impersonation if available.

- [ ] **Step 8: Commit**

```bash
git add resources/js/vue/composables/useImpersonation.js resources/js/vue/layouts/ClientLayout.vue
# also RiseLayout.vue if Step 5 modified it
git commit -m "fix(impersonation): shift client topnav when coach (or admin) is impersonating"
```

---

## Task 7: Fix 7 — Daily missions route to dead endpoints

**Files:**
- Modify: `resources/js/vue/components/dashboard/DashboardMissions.vue`
- Modify (if needed): `resources/js/vue/pages/Client/PlanViewer.vue`

- [ ] **Step 1: Update the mission route map**

In `resources/js/vue/components/dashboard/DashboardMissions.vue`, find the `missionRouteMap` (lines 14–20):

```js
const missionRouteMap = {
    training: '/client/training',
    checkin: '/client/checkin',
    weight: '/client/metrics',
    nutrition: '/client/nutrition',
};
```

Replace with:

```js
const missionRouteMap = {
    training:  '/client/plan?tab=training',
    checkin:   '/client/checkin',
    weight:    '/client/metrics',
    nutrition: '/client/plan?tab=nutrition',
};
```

- [ ] **Step 2: Verify PlanViewer.vue reads `?tab=` from the query string**

Run: `grep -n "route.query.tab\|query.tab\|currentTab\|activeTab" resources/js/vue/pages/Client/PlanViewer.vue | head -10`

If the output shows logic that reads `route.query.tab` and sets the active tab on mount + on change, no further work is needed — go to Step 4.

If `PlanViewer.vue` does NOT respond to `?tab=`, continue to Step 3.

- [ ] **Step 3: Add `?tab=` reactivity to PlanViewer.vue**

Open `resources/js/vue/pages/Client/PlanViewer.vue`. Locate the existing tab state ref (likely `activeTab`, `currentTab`, or similar — call this `tabRef` below).

Add near the imports:

```js
import { useRoute } from 'vue-router';
import { watch, onMounted } from 'vue';
const route = useRoute();
```

(Skip any line already present.)

Below the tab state declaration, add:

```js
const VALID_TABS = ['training', 'nutrition'];

function setTabFromQuery() {
    const requested = route.query.tab;
    if (typeof requested === 'string' && VALID_TABS.includes(requested)) {
        // tabRef is the existing tab state ref; rename if your local var differs
        tabRef.value = requested;
    }
}

onMounted(setTabFromQuery);
watch(() => route.query.tab, setTabFromQuery);
```

Adjust `tabRef.value = requested` to match the actual setter pattern in this component (could be a function call instead of direct assignment). If the component uses an enum/string of tab keys different from `'training'`/`'nutrition'`, map them — e.g. if internal keys are `'entreno'`/`'nutricion'`, add a translation step.

- [ ] **Step 4: Build assets**

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 5: Manual verification**

1. Login as a client with a plan assigned (`cristian` via test creds, or a fresh client).
2. Land on `/client`. Confirm the "Misiones diarias" card lists training + nutrition missions.
3. Tap "Completar entrenamiento" → expect to land on `/client/plan?tab=training` with the training tab active.
4. Tap "Revisar plan de nutrición" → expect `/client/plan?tab=nutrition` with the nutrition tab active.
5. Use browser back, then alternate between the two missions — the tab must update each time.

- [ ] **Step 6: Commit**

```bash
git add resources/js/vue/components/dashboard/DashboardMissions.vue
# also PlanViewer.vue if step 3 modified it
git commit -m "fix(client-missions): route training/nutrition to /client/plan with tab query"
```

---

## Task 8: Fix 8 — Remove orphaned `/client/nutrition` route

**Files:**
- Modify: `resources/js/vue/router/index.js`
- Possibly delete: `resources/js/vue/pages/Client/NutritionPlan.vue`

- [ ] **Step 1: Find the `/client/nutrition` route entry**

Run: `grep -n "client/nutrition\|NutritionPlan" resources/js/vue/router/index.js`

Note the line number(s).

- [ ] **Step 2: Replace the route definition with a redirect**

In `resources/js/vue/router/index.js`, replace the existing `/client/nutrition` route entry with:

```js
{ path: '/client/nutrition', redirect: '/client/plan?tab=nutrition' },
```

Keep this redirect for at least 60 days so legacy email links and push-notification deeplinks still resolve.

- [ ] **Step 3: Check whether NutritionPlan.vue is still imported elsewhere**

Run: `grep -rn "NutritionPlan" resources/js/vue --include="*.vue" --include="*.js"`

If the only reference was the now-removed router entry, the file can be deleted.
If there are other references (e.g. a `<NutritionPlan>` tag in another component, an import in some test file), DO NOT delete — leave the file in place and skip Step 4.

- [ ] **Step 4 (conditional): Delete the orphan component**

If Step 3 confirmed no remaining references:

```bash
rm resources/js/vue/pages/Client/NutritionPlan.vue
```

- [ ] **Step 5: Build assets**

Run: `npm run build`
Expected: build succeeds. If a deletion broke an import, the build will fail — restore the file.

- [ ] **Step 6: Manual verification**

1. In the browser, navigate explicitly to `/client/nutrition`.
2. Expect immediate redirect to `/client/plan?tab=nutrition`.
3. Expect no 404 in the console.

- [ ] **Step 7: Commit**

```bash
git add resources/js/vue/router/index.js
# Add NutritionPlan.vue to staging only if it was deleted in Step 4
git commit -m "fix(client-router): redirect /client/nutrition to /client/plan?tab=nutrition"
```

---

## Task 9: Fix 9 — Coach credentials email shows real WellCore logo

**Files:**
- Modify: `resources/views/emails/coach-credentials.blade.php`

- [ ] **Step 1: Verify the logo image exists in public**

Run: `ls -la public/images/logo-light.png`
Expected: file exists (confirmed during exploration).

- [ ] **Step 2: Replace the inline placeholder with the real logo**

In `resources/views/emails/coach-credentials.blade.php`, find lines 8–12:

```html
<!-- Logo header -->
<tr><td style="padding:20px 0;text-align:center;">
    <div style="display:inline-block;background:#DC2626;width:40px;height:40px;border-radius:8px;line-height:40px;text-align:center;color:white;font-weight:bold;font-size:20px;">W</div>
    <span style="color:#FAFAFA;font-size:20px;font-weight:bold;letter-spacing:3px;margin-left:10px;vertical-align:middle;">WELLCORE</span>
</td></tr>
```

Replace with:

```html
<!-- Logo header -->
<tr><td style="padding:20px 0;text-align:center;">
    <img src="https://wellcorefitness.com/images/logo-light.png"
         alt="WellCore Fitness" width="180"
         style="display:inline-block;height:auto;max-width:180px;border:0;outline:none;text-decoration:none;" />
</td></tr>
```

- [ ] **Step 3: Manual verification**

1. From the admin portal, send a test coach invitation (or trigger the credential email) to a sandbox address.
2. Open the email. Confirm the WellCore light logo image renders at ~180 px wide. (If the receiving client blocks images, the alt text "WellCore Fitness" should display.)
3. Open `/coach/invitations` → preview an invitation. Confirm the in-app preview iframe also shows the real logo.

- [ ] **Step 4: Commit**

```bash
git add resources/views/emails/coach-credentials.blade.php
git commit -m "fix(emails): use real WellCore logo in coach-credentials template"
```

---

## Final verification before deploy

- [ ] **Run the existing test suite**

```bash
php artisan test --testsuite=Feature
```

Expected: all existing tests pass. Note: this plan does not add new tests — fixes are visual/navigational and existing Feature suite must continue green.

- [ ] **Production build sanity check**

```bash
npm run build
```

Expected: build succeeds, no compile warnings introduced by these changes.

- [ ] **Push & deploy**

Per project memory, never trigger Rebuild Docker:

```bash
git push origin main
```

Then in EasyPanel, run the `gitpull-load` script via MCP. Verify the deploy by reloading the production URL and confirming each fix on a real device.

- [ ] **Post-deploy smoke test (production)**

1. Login as a real coach (or QA account).
2. Open `/coach` mobile — verify counters match `/coach/clients`, polling works.
3. Tap mobile FAB → "Agregar cliente" → lands on `/coach/invitations`.
4. Tap "Tickets" tile → lands on create form.
5. Impersonate a client → topnav fully visible.
6. Open `/client/checkin` → all "Siguiente" buttons present.
7. Tap "Revisar plan de nutrición" → lands on `Mi Plan` nutrition tab.
8. Tap "Completar entrenamiento" → lands on `Mi Plan` training tab.
9. Visit `/client/nutrition` → redirects to `/client/plan?tab=nutrition`.
10. Trigger a coach invitation → real logo appears in preview + email.

If any step fails, revert the offending commit (each fix is a single commit) and reopen the corresponding task.
