# Mobile UI Bugs — 9 Fixes (April 2026)

**Status:** Approved for implementation
**Date:** 2026-04-25
**Owner:** WellCore Laravel team
**Companion spec:** `2026-04-25-coach-contract-acceptance-design.md`

## Context

Tactical fixes reported during mobile QA on 2026-04-25 across the client and coach portals. All issues are localized — no DB migrations, no new endpoints, no new major components. The 9 fixes touch 8 Vue files plus one Blade email template. Each fix is independent and can ship separately.

The bigger coach contract feature reported in the same QA session is documented in a separate spec (see companion).

## Goals

1. Restore broken navigation paths on mobile coach FAB and quick actions.
2. Eliminate visual collisions between fixed UI elements (FAB ↔ workout bottom bar, impersonation banner ↔ topnav, check-in CTA ↔ bottom nav).
3. Make the coach dashboard reflect real-time client data (no more "0 clientes" when there are 2).
4. Replace dead client routes (`/client/nutrition`) with the canonical `/client/plan?tab=nutrition`.
5. Use the real WellCore light logo in the coach credentials email instead of an inline ASCII placeholder.

## Non-goals

- WebSocket-based realtime (Laravel Reverb is in stack but out of scope here — see Fix 4 trade-off).
- Refactoring the Livewire legacy partials in `resources/views/livewire/coach/partials/`.
- New component design system tokens — fixes use existing `wc-*` Tailwind tokens.

## The 9 fixes

### Fix 1 — Client FAB collides with active workout bottom bar

- **Symptom:** While in `/client/workout/*` with workout started, the round red FAB ("+") at `bottom: 5rem` overlaps with the bottom action bar (`Abandonar` / `Completar Sesión`) anchored at `bottom-0`. Visual collision blocks the "Completar Sesión" CTA.
- **Files:** `resources/js/vue/components/dashboard/DashboardFab.vue`
- **Approach:** Detect when route is `/client/workout/*` via `useRoute()`. When detected, lift the FAB from `bottom: calc(5rem + safe-area)` to `bottom: calc(11rem + safe-area)` so the FAB sits ~64 px above the bottom bar. Apply via `:style` computed.
- **Acceptance:**
  - On `/client/workout/...`, FAB sits clearly above the bottom action bar with no overlap on iPhone 14 Pro viewport.
  - On all other client routes, FAB position is unchanged.

### Fix 2 — Coach FAB "Agregar cliente" routes to wrong page

- **Symptom:** Tapping the mobile FAB on coach dashboard → "Agregar cliente" → "Invitar nuevo cliente al programa" navigates to `/coach/clients` (client list). Should go to invitation flow.
- **Files:** `resources/js/vue/layouts/CoachLayout.vue` line ~425
- **Approach:** Change `to="/coach/clients"` to `to="/coach/invitations"`.
- **Acceptance:** Tapping the entry opens the invitations page (`InvitationManager.vue`).

### Fix 3 — Coach Dashboard "Tickets" mobile shortcut doesn't open create flow

- **Symptom:** On `/coach` mobile dashboard, the "Tickets" quick action card routes to `/coach/plan-tickets` (list). Per QA, it should land on the new-ticket form.
- **Files:** `resources/js/vue/pages/Coach/Dashboard.vue` line ~209
- **Approach:** Change `to="/coach/plan-tickets"` to `to="/coach/plan-tickets/nuevo"`.
- **Acceptance:** Tapping the "Tickets" tile opens the create-ticket form. List access remains via the right-rail "Ver todos →" link.

### Fix 4 — Coach Dashboard shows "0 clientes activos" while client list shows 2

- **Symptom:** `/coach` (Dashboard) renders "0 CLIENTES ACTIVOS" but `/coach/clients` lists 2 active clients (case observed on coachdann / KingLord6962). Counters across the dashboard appear stale.
- **Files:**
  - Frontend: `resources/js/vue/pages/Coach/Dashboard.vue`
  - Backend audit: `app/Http/Controllers/Api/CoachController.php` (`dashboard()` and `clients()` both call `getCoachClientIds()`)
- **Root cause analysis:** The `IntersectionObserver` (lines 73–89) animates `0 → activeClients` only once when the stats grid enters the viewport. If the API resolves after the observer fires, or the threshold of 0.2 isn't met, the displayed counter stays at 0 even though `stats.value.activeClients` has the correct number.
- **Approach (option D — polling + focus refresh):**
  1. Stop relying on the `IntersectionObserver` for the *value*. Set `animatedCounters[key] = stats[key]` directly when API resolves; keep the easing animation as cosmetic only.
  2. Add a 30 s polling interval on `/api/v/coach/dashboard` while `document.visibilityState === 'visible'`.
  3. Listen to `visibilitychange` — pause the interval when hidden, fire one immediate refresh when visible again.
  4. Clean up both in `onBeforeUnmount` (interval id + listener).
  5. Audit `getCoachClientIds()` and the `Client::whereIn(...)->where('status', 'activo')` query in both `dashboard()` and `clients()` endpoints with coachdann's data — they must return identical sets.
- **Acceptance:**
  - With coach A having N clients, both `/coach` and `/coach/clients` show N (consistent).
  - Counters refresh ≤ 30 s after a check-in / message / new client event in the DB.
  - Pull-to-refresh-equivalent works: switch tab away, switch back, dashboard reflects fresh data.

### Fix 5 — Check-in semanal mobile has no "Siguiente" button

- **Symptom:** On mobile, the check-in wizard renders the form but no way to advance through steps 1→4. Desktop has the buttons; mobile does not.
- **Files:** `resources/js/vue/pages/Client/CheckinForm.vue` line ~583
- **Root cause:** The mobile sticky CTA is at `z-30`, but `ClientLayout.vue`'s bottom navigation ("Dashboard, Plan, Métricas, Chat, Perfil") sits at higher z-index and visually covers it. Form content also lacks bottom padding so the wizard's last input is hidden behind whatever bottom UI is rendered.
- **Approach:**
  1. Raise sticky CTA to `z-40` (or equivalent — must be higher than the bottom-nav).
  2. Position the CTA at `bottom: 4rem` (above the layout's bottom nav, not anchored to `bottom-0`).
  3. Add `pb-32` (or equivalent) to the wizard's outer container so form fields don't hide behind the CTA.
- **Acceptance:**
  - On iPhone viewport, all 4 steps show their "Siguiente" / "Atrás" buttons.
  - The last form field is fully visible above the CTA on each step.
  - Submission button works on step 4 ("Enviar check-in" or "Disponible el viernes" depending on date).

### Fix 6 — Coach impersonation banner overlaps client topnav

- **Symptom:** When a coach uses "Ver como cliente", the red "Viendo como cristian" banner at `top-0 z-[100]` overlaps the client portal's sticky header and slides above the sidebar trigger. Coach can't access nav controls.
- **Files:**
  - `resources/js/vue/components/CoachImpersonationBanner.vue`
  - `resources/js/vue/layouts/ClientLayout.vue` line ~319
  - (Possibly) `resources/js/vue/layouts/RiseLayout.vue` if symmetric
- **Root cause:** `ClientLayout` only shifts its `<header class="sticky">` to `top-10` when `isImpersonating` (admin flag). Coach impersonation lives in `localStorage.wc_impersonating_by_coach` — the layout doesn't read it.
- **Approach:**
  1. Create `resources/js/vue/composables/useImpersonation.js` exposing reactive flags `isImpersonatingByAdmin`, `isImpersonatingByCoach`, and `anyImpersonation` (computed). Watch `storage` event for cross-tab sync.
  2. `ClientLayout.vue` consumes `anyImpersonation`. When true, header gets `top-10`, sidebar trigger shifts down accordingly, content area gets `pt-10`.
  3. Apply same change to `RiseLayout.vue` if it has impersonation support.
- **Acceptance:**
  - Coach impersonating a client sees a red banner above an unobstructed client topnav.
  - Admin impersonation behavior is unchanged (no regression).
  - Banner stays visible while scrolling.

### Fix 7 — Daily missions route to dead endpoints

- **Symptom:** From the client dashboard, "Revisar plan de nutrición" navigates to `/client/nutrition` (an orphaned page). "Completar entrenamiento" navigates to `/client/training` (calendar) instead of the training tab inside Mi Plan.
- **Files:** `resources/js/vue/components/dashboard/DashboardMissions.vue` lines 14–20
- **Approach:**
  ```js
  const missionRouteMap = {
    training:  '/client/plan?tab=training',
    checkin:   '/client/checkin',
    weight:    '/client/metrics',
    nutrition: '/client/plan?tab=nutrition',
  };
  ```
  Verify `PlanViewer.vue` reacts to `route.query.tab` and selects the corresponding tab on mount + on query change. If it doesn't, add a `watch(() => route.query.tab, ...)` that calls the existing tab setter.
- **Acceptance:**
  - Tap on "Revisar plan de nutrición" lands on `Mi Plan` with the Nutrición tab active.
  - Tap on "Completar entrenamiento" lands on `Mi Plan` with the Entrenamiento tab active.

### Fix 8 — Remove orphaned `/client/nutrition` route

- **Symptom:** The route `/client/nutrition` is dead-ish — kept around but should not be reachable. Per QA, it confuses both clients and links from older emails.
- **Files:**
  - `resources/js/vue/router/index.js`
  - `resources/js/vue/pages/Client/NutritionPlan.vue` (deletion candidate)
  - `app/Http/Controllers/Api/NutritionController.php` (verify if any endpoint depends on it)
- **Approach:**
  1. Replace the existing route entry with a redirect: `{ path: '/client/nutrition', redirect: '/client/plan?tab=nutrition' }`.
  2. If `NutritionPlan.vue` is no longer imported anywhere, delete it (grep `NutritionPlan` to confirm).
  3. Keep the redirect for at least 60 days so old links from emails / push notifications still work.
- **Acceptance:**
  - Direct navigation to `/client/nutrition` lands on `/client/plan?tab=nutrition`.
  - No build errors after removing `NutritionPlan.vue` if it's deletion-eligible.

### Fix 9 — Coach credentials email shows ASCII placeholder instead of real logo

- **Symptom:** The email preview shows a red square with "W" + the word "WELLCORE" in plain text instead of the official light logo.
- **Files:** `resources/views/emails/coach-credentials.blade.php` lines 8–12
- **Approach:** Replace the inline `<div>W</div> <span>WELLCORE</span>` with the same `<img>` pattern already used by `gift-plan-invitation.blade.php` and `plan-invitation.blade.php`:
  ```html
  <img src="https://wellcorefitness.com/images/logo-light.png"
       alt="WellCore Fitness" width="180"
       style="display:inline-block;height:auto;max-width:180px;" />
  ```
  Verify `public/images/logo-light.png` exists (it does — confirmed during exploration).
- **Acceptance:**
  - Preview UI (`Coach/Invitations/EmailPreview.vue`) renders the actual logo image.
  - Real email send (Mailjet) uses the same image; falls back gracefully if blocked by mail clients.

## Cross-cutting concerns

### Build & deploy

- Build assets locally: `npm run build` (project memory: never run npm build inside EasyPanel container — it OOMs the host).
- Commit `public/build/`.
- `git push` to main.
- Trigger `gitpull-load` script in EasyPanel via MCP. Do NOT use Rebuild Docker.

### Verification

- Manual mobile QA in Chrome DevTools iPhone 14 Pro viewport for fixes #1, #5, #6, #7.
- Visual regression snapshots saved under `_screenshots_perf/2026-04-25-bugfixes/` per fix.
- Smoke test via Playwright MCP:
  1. Login as coach (KingLord6962 credentials) → verify dashboard counts match `/coach/clients`.
  2. Impersonate a client → verify topnav unobstructed.
  3. Open check-in semanal on mobile viewport → verify "Siguiente" button works.
- For Fix 4, log Chrome DevTools network panel during 60 s of focus to confirm 2 polling hits + 1 visibility-change hit.

### Testing

- No new automated tests — these are visual / navigational fixes. Existing Pest feature tests must continue passing.
- Backend audit for Fix 4 may surface a real data discrepancy. If so, document in a sub-issue and fix in this same plan iteration.

### Rollback

Each fix is a small, independent diff. If something regresses in production, revert the offending commit. Polling for Fix 4 has a feature flag implicit in the config: if `pollIntervalMs = 0`, polling is disabled (default to 30000).

## Agent delegation

| Fix | Primary agent |
|-----|---------------|
| 1, 5, 6 | la-04-tailwind-ds (z-index, layout) |
| 2, 3, 7, 8 | la-03-vue3 (Vue routing, components) |
| 4 | la-03-vue3 + la-15-api (frontend polling + API audit) |
| 9 | la-03-vue3 (Blade email template) |

Multiple fixes can be parallelized — they touch different files. Keep one PR per logical group (e.g., one PR for "Coach FAB / Tickets routing", another for "Mobile collisions").

## Open questions

None at spec time. All architectural and product decisions confirmed during brainstorming on 2026-04-25.
