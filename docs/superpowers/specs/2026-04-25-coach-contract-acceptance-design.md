# Coach Contract Acceptance — Digital Alliance Agreement

**Status:** Approved for implementation
**Date:** 2026-04-25
**Owner:** WellCore Laravel team
**Companion spec:** `2026-04-25-mobile-ui-bugs-fixes-design.md`

## Context

WellCore needs every coach to formally accept the digital "Acuerdo de Alianza Comercial" before working in the platform. This is a commercial alliance, **not** an employment contract — coaches are independent partners who promote, sell, and accompany WellCore plans, with a 60/40 revenue split.

The base content already exists as a designed HTML at:
`C:\Users\GODSF\Music\INTERFAZ Y MEJORIAS\VINCULACION_COACH_WELLCORE.html`

It covers 7 sections (roles, what WellCore does/doesn't provide, requirements, duties, payment policy, confidentiality + 12 termination causes, dashboard tour). What it does **not** yet cover is the legal scaffolding required for a digitally accepted commercial agreement under Colombian law to be enforceable.

The acceptance flow must:
1. Block any coach (existing or new) from using the portal until they accept the current contract version.
2. Force the coach to scroll to the end before the accept/decline controls activate.
3. Persist legally usable evidence: timestamp, IP, user-agent, content hash, scroll-completion flag, contract version.
4. Treat decline as immediate and irreversible — coach account becomes inactive.
5. Re-prompt coaches when WellCore publishes a new contract version (v1.1, v2.0, …).

## Goals

- Make every coach accept v1.0 of the contract before resuming portal access.
- Generate a defensible audit trail for each acceptance (Ley 527 / 1999 — message-of-data evidentiary equivalence).
- Lay foundations for future contract versioning without schema changes.

## Non-goals

- Not implementing biometric or notarized signature — electronic acceptance is sufficient under Ley 527.
- Not designing an admin UI to view acceptance records (out of scope; a single phpMyAdmin query suffices for now).
- Not building bulk-notification email about the new contract for existing coaches — see *Open questions* §3.

## Phase 0 — Legal research (must precede coding)

The base HTML covers the business but lacks legal blindaje. Before building, the contract must include the following clauses, drafted to align with Colombian law:

| Topic | Law | What to add |
|-------|-----|-------------|
| Non-employment relationship | CST art. 23 / Código de Comercio | Express clause: "alianza comercial sin subordinación, sin horario, sin exclusividad", to prevent reclassification as a labor contract. |
| Tax responsibility | DIAN, régimen simple / RUT | Coach declares responsibility for own tax regime (RUT, retención, IVA where applicable). |
| Habeas Data | Ley 1581/2012 + Decreto 1377/2013 | Coach authorizes WellCore to process personal data; coach commits to the privacy policy of clients they access. |
| Electronic commerce | Ley 527/1999 art. 5–7 | Digital acceptance has the same legal effect as a physical signature; the data message is equivalent to an original. |
| Intellectual property | Ley 23/1982, Decisión Andina 351 | WellCore retains all rights over plans, brand, and materials; coach receives a non-exclusive license to use them within the platform. |
| Confidentiality | Código de Comercio + Ley 1581 | Reinforce existing section 6 with a duration (2 years post-termination) and consequences (civil action). |
| Dispute resolution | Ley 1563/2012 | Compromissory clause: arbitration or conciliation at the relevant chamber of commerce before ordinary jurisdiction. |
| Electronic acceptance evidence | Ley 527 + jurisprudence CSJ | Store: UTC timestamp, IP, user-agent, SHA-256 hash of the accepted HTML, document version, scroll-completion flag. |

**Output of Phase 0:** a final HTML at `resources/views/legal/coach-contract-v1.0.blade.php`. It mirrors the visual style of the source HTML but adds two new sections at the end:
- **Section 8 — "Aspectos legales"** containing the 7 first rows above.
- **Clause 9 — "Aceptación digital y evidencia"** containing the last row.

The HTML includes a sentinel `<div id="contract-end-sentinel"></div>` immediately before the footer, used by the frontend scroll detector.

**Disclaimer:** I am useful for legal research but not a substitute for a Colombian-licensed attorney. The contract ships with a "Sujeto a revisión legal" footer until reviewed by counsel. Until that review, the version stays as a draft v1.0 in production but the engineering plumbing is ready.

## Phase 1 — Database (additive only)

Two additive migrations on the shared `wellcore_fitness` schema. No FKs are enforced at DB level (schema is shared with the legacy vanilla-PHP app and migrations stay non-destructive).

**Migration 1:** `database/migrations/2026_04_25_create_coach_contract_acceptances_table.php`

```
coach_contract_acceptances
─────────────────────────
id                 bigIncrements
coach_id           unsignedBigInt    indexed
contract_version   string(20)        e.g. "1.0", "1.1"
status             enum('accepted', 'declined')
accepted_at        timestamp nullable
declined_at        timestamp nullable
ip_address         string(45)        IPv6-safe
user_agent         text
content_hash       char(64)          SHA-256 of the rendered contract HTML
scroll_completed   boolean           evidence the coach reached the end
created_at, updated_at

UNIQUE (coach_id, contract_version)
```

Indexes: `coach_id`, `contract_version`.

**Migration 2:** `database/migrations/2026_04_25_add_inactive_reason_to_coaches_table.php`

If `coaches.inactive_reason` does not already exist (verify with `Schema::hasColumn('coaches', 'inactive_reason')`), add a nullable `string(50)` column. Used to record why a coach account was deactivated (e.g. `contract_declined`, `manual_admin`, `policy_violation`). The migration must short-circuit if the column already exists — it must be safe to run on a database where vanilla PHP may have already added it.

## Phase 2 — Backend

**Service:** `app/Services/CoachContractService.php`

Public methods:
- `getCurrentVersion(): string` — reads from `config('wellcore.coach_contract_version')`, default `"1.0"`.
- `getContractHtml(string $version): string` — renders `resources/views/legal/coach-contract-v{version}.blade.php`. Throws if missing.
- `getCurrentContentHash(): string` — `hash('sha256', $this->getContractHtml($this->getCurrentVersion()))`.
- `hasAcceptedCurrentVersion(int $coachId): bool` — query `coach_contract_acceptances` where `(coach_id, version, status='accepted')`.
- `recordAcceptance(int $coachId, Request $r, bool $scrollCompleted): void`
- `recordDecline(int $coachId, Request $r): void` — inserts decline row, sets `coaches.status='inactivo'`, sets `coaches.inactive_reason='contract_declined'`, revokes all auth tokens for the coach by deleting rows from `auth_tokens` (the custom table read by `WellCoreGuard`) where `user_id = coachId AND user_type = 'coach'`.

**Controller:** `app/Http/Controllers/Api/Coach/ContractController.php`
- `GET /api/v/coach/contract/status` →
  ```json
  { "requires_acceptance": true, "version": "1.0", "html": "<!doctype html>..." }
  ```
- `POST /api/v/coach/contract/accept` →
  body `{ "version": "1.0", "scroll_completed": true }` → 200 on success, 422 if `scroll_completed` is false or `version` mismatches current.
- `POST /api/v/coach/contract/decline` → 200 + token revocation. Frontend receives the response and forces logout.

**Middleware:** `app/Http/Middleware/EnsureCoachContractAccepted.php`
- Applies to all `/api/v/coach/*` routes EXCEPT `contract/*` and `auth/logout`.
- If `!hasAcceptedCurrentVersion($coachId)`, return:
  ```json
  HTTP 403
  { "contract_required": true, "version": "1.0" }
  ```
- Frontend interceptor catches `contract_required: true` and mounts the gate.

**Routes** registered in `routes/api.php` under the existing coach group.

## Phase 3 — Frontend (Vue 3)

**New component:** `resources/js/vue/components/coach/CoachContractGate.vue`

Mounted at the top of `CoachLayout.vue` (same pattern as `CoachImpersonationBanner`). Behavior:

1. On mount, calls `GET /api/v/coach/contract/status`.
2. If `requires_acceptance: true`, opens a full-screen modal at `z-[200]` (above everything including impersonation banner).
3. Modal anatomy:
   - Header with "Acuerdo de Alianza Comercial · WellCore Fitness · v1.0".
   - Body containing an `<iframe sandbox="allow-same-origin allow-scripts" :srcdoc="html">` to isolate contract CSS from the modal.
   - The iframe HTML includes a script that posts a `window.parent.postMessage({type:'contract-end'},'*')` when the sentinel `<div id="contract-end-sentinel">` becomes visible (IntersectionObserver inside the iframe).
   - Parent listens for `message` events and toggles `scrollCompleted = true`.
4. While `!scrollCompleted`:
   - Checkbox disabled and grey.
   - Buttons disabled.
   - Helper text: "Lee el documento hasta el final para activar la aceptación."
5. When `scrollCompleted = true`:
   - Checkbox enabled — label "He leído y acepto el Acuerdo de Alianza Comercial v1.0".
   - **Aceptar y continuar** (primary, red) — disabled until checkbox is checked → on click `POST /accept` → close modal → reload page so the rest of the portal mounts with the now-cleared gate.
   - **Rechazar y dar de baja mi cuenta** (text link, neutral) — opens a secondary confirmation dialog: *"Esta acción es definitiva. Tu cuenta quedará inactiva y no podrás recuperarla sin contactar al admin."* If confirmed → `POST /decline` → `auth.logout()` → router push to `/login?reason=contract_declined`.

**Router guard:** `resources/js/vue/router/index.js`. Add a `beforeEach` that, when the gate is active (Pinia flag), only allows navigation to the current route or to `/logout`.

**Composable:** `resources/js/vue/composables/useContractGate.js` exposes:
- `requires` (ref<boolean>)
- `version` (ref<string>)
- `html` (ref<string>)
- `scrollCompleted` (ref<boolean>)
- `accept()`, `decline()`, `refresh()`

Auth store interceptor (`resources/js/vue/stores/auth.js` or wherever axios interceptors live) catches `403 { contract_required: true }` and triggers `useContractGate().refresh()`.

## Phase 4 — Email touch-up

In `resources/views/emails/coach-credentials.blade.php` add a paragraph near the bottom of the welcome content:

> "Al ingresar por primera vez verás el Acuerdo de Alianza Comercial. Léelo con calma — es la base legal de tu vinculación al equipo. Si tienes dudas, escríbenos antes de aceptar."

This change is intentionally bundled with **Spec 1, Fix 9** (logo replacement) so the same email template gets one coordinated update.

## Phase 5 — Versioning future-proofing

When WellCore publishes v1.1:
1. Add `resources/views/legal/coach-contract-v1.1.blade.php`.
2. Bump `config('wellcore.coach_contract_version')` to `"1.1"`.
3. Deploy.

Existing acceptances of v1.0 stay valid as historical evidence. The unique constraint `(coach_id, contract_version)` allows v1.0 + v1.1 rows to coexist per coach, and `hasAcceptedCurrentVersion` only checks the current version. Coaches automatically see the gate on next request to a coach API endpoint.

## Testing

**Pest feature tests:** `tests/Feature/CoachContractAcceptanceTest.php`
- `it_blocks_api_when_not_accepted`
- `it_allows_after_accepting_current_version`
- `it_rejects_acceptance_with_scroll_completed_false`
- `it_deactivates_account_on_decline`
- `it_revokes_tokens_on_decline`
- `it_records_ip_user_agent_and_content_hash`
- `it_requires_re_acceptance_when_version_bumps`
- `it_allows_logout_route_even_when_gate_is_active`

**Vue smoke test (manual or Playwright MCP):**
- Coach without an acceptance row → modal appears.
- Buttons disabled until scroll-end.
- Click *Accept* → modal closes → dashboard renders.
- Click *Decline* → confirm → logout → login screen with `?reason=contract_declined`.

**Legal sanity check:**
- Run the contract HTML by `la-05-security` agent for compliance review before marking v1.0 as final.
- Mark v1.0 as **draft** in `config/wellcore.php` until reviewed by counsel; once reviewed, flip the flag.

## Rollout plan

1. Add config keys to `config/wellcore.php`:
   ```
   'coach_contract' => [
       'enabled' => env('COACH_CONTRACT_GATE_ENABLED', false),
       'version' => env('COACH_CONTRACT_VERSION', '1.0'),
       'is_draft' => env('COACH_CONTRACT_IS_DRAFT', true),
   ],
   ```
2. Ship migrations + backend + middleware with the flag defaulting to `false`. Middleware short-circuits when disabled.
3. Ship the frontend gate gated by the same config (exposed via `/api/v/coach/contract/status` returning `requires_acceptance: false` when disabled).
4. Test in staging with internal coach accounts.
5. Flip `COACH_CONTRACT_GATE_ENABLED=true` in production. All coaches see the gate on next request.
6. Monitor decline rate for the first 72 h. If unexpectedly high (> 5%), flip the flag back off and investigate UX or content.

## Build & deploy

Same workflow as Spec 1: build local, commit `public/build/`, `git push`, `gitpull-load` via EasyPanel MCP. The migration is additive and runs safely with `php artisan migrate` inside the container.

## Risks and decisions

1. **Legal validation:** v1.0 ships as **draft** until reviewed by a Colombian-licensed attorney. The eight Phase-0 clauses are research-grade, not certified.
2. **Existing coaches:** when the flag flips to true, all currently active coaches will see the modal on next login. We are NOT sending an email warning 24 h in advance in this version — see *Open questions* §3.
3. **Iframe vs v-html:** chose **iframe + postMessage** to isolate contract CSS from the modal. v-html would be simpler but couples the contract's typography to the modal's stylesheet.
4. **Decline irreversibility:** declining sets the account to inactive. Recovery requires admin intervention. This is intentional per stakeholder confirmation on 2026-04-25.

## Agent delegation

| Phase | Primary agent |
|-------|---------------|
| 0 (legal research + HTML) | la-05-security + la-04-tailwind-ds (visual) |
| 1 (migration) | la-06-database |
| 2 (service / controller / middleware) | la-02-backend + la-05-security |
| 3 (Vue gate) | la-03-vue3 |
| 4 (email touch-up) | la-03-vue3 |
| Tests | la-14-testing |

Phase 0 must complete before Phase 1. Phases 1–3 can overlap once Phase 0 ends (independent file sets).

## Open questions

1. **Final legal review provider** — internal counsel, external firm, or postpone until launch? *Decision pending; ships as draft.*
2. **Contract content updates** — who owns versioning? Suggest: a `CONTRACT_OWNERS.md` listing approvers (CEO + legal).
3. **Pre-rollout coach notification** — optional 24 h email warning before flipping the flag. Recommend: yes, but track as a follow-up issue rather than a blocker for this spec.
