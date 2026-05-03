# 🔒 WELLCORE LARAVEL — SECURITY AUDIT & IMPLEMENTATION PLAN

**Document Version:** 1.0.0  
**Date:** 2026-04-23  
**Target Agent:** Claude Code Opus 4.7 XHigh  
**Classification:** CRITICAL — Production Hardening  
**Compatibility Constraint:** ZERO breaking changes to database schema, ZERO UX disruption, ZERO visual/design regression  
**Platform:** Laravel 13.1.1 + PHP 8.4 + MySQL 8.x + Vue 3 SPA + Livewire Legacy  

---

## 1. EXECUTIVE SUMMARY

This document provides a **complete security audit** of the WellCore Laravel platform and a **step-by-step, zero-downtime implementation plan** to harden:

- **Security Headers** (HSTS, CSP, X-Frame-Options, etc.)
- **CORS** (cross-origin resource sharing)
- **Application-Level Row-Level Security** (MySQL equivalent via Laravel)
- **Auth & API route protection**
- **Session & cookie hardening**
- **Database connection security**
- **NGINX hardening**

> ⚠️ **CRITICAL ARCHITECTURAL NOTE — MySQL & RLS:**  
> MySQL 8.x does **NOT** support native Row-Level Security (RLS) like PostgreSQL.  
> This plan implements **Application-Level RLS** via Laravel Global Scopes + Policies + Query Constraints.  
> This is the industry-standard approach for MySQL-backed Laravel applications and provides equivalent protection at the application layer.

---

## 2. ASSET INVENTORY & CURRENT STATE

### 2.1 Technology Stack
| Layer | Technology | Version |
|-------|-----------|---------|
| Framework | Laravel | 13.1.1 |
| PHP | PHP | 8.4 |
| Database | MySQL | 8.x |
| Frontend | Vue 3 SPA + Livewire 3 | 3.5 / 3.x |
| CSS | Tailwind CSS | 4 |
| Build | Vite | 8 |
| Web Server | NGINX (via EasyPanel) | — |
| Cache | Redis (configured, not default) | — |

### 2.2 Authentication Architecture
- **Custom Guard:** `WellCoreGuard` (reads from `auth_tokens` table)
- **Token Strategy:** 64-char hex (`bin2hex(random_bytes(32))`), 7-day expiry
- **Token Sources (in order):** Laravel session → Bearer header → `admin_token` POST body → `wc_token` cookie
- **Password Hashing:** `password_hash()` / `password_verify()` (BCRYPT)
- **Compat Layer:** Cookie `wc_token` is **UNENCRYPTED** (shared with legacy vanilla PHP app)
- **Session Driver:** `file` (per `.env`), not encrypted (`SESSION_ENCRYPT=false`)

### 2.3 Route Architecture
- **Web routes (`routes/web.php`):** Serve Vue SPA shells (`view('vue')`) for `/client`, `/admin`, `/coach`, `/rise`
- **API routes (`routes/api.php`):** All data fetching. Auth is **NOT enforced at route level** — each controller uses traits (`AuthenticatesVueRequests`) to resolve user manually
- **Public API:** `/api/ejercicios/*`, `/api/v/public/*`, `/api/v/shop/*`, `/api/chat`, `/api/newsletter`
- **Auth API:** `/api/v/auth/*` (web session based)

---

## 3. RISK MATRIX

| ID | Finding | Severity | CVSS-like | Impact |
|----|---------|----------|-----------|--------|
| R1 | `trustProxies(at: '*')` — any IP can spoof X-Forwarded-* headers | 🔴 **CRITICAL** | 7.5 | IP spoofing, rate limit bypass, forced HTTP downgrade |
| R2 | API routes (`/api/v/client/*`, `/api/v/coach/*`, `/api/v/admin/*`) have NO auth middleware — rely on controller-level manual auth | 🟠 **HIGH** | 6.5 | If developer forgets trait, endpoint is fully open (fail-open) |
| R3 | No HSTS header — production site vulnerable to SSL stripping | 🟠 **HIGH** | 6.1 | Man-in-the-middle, downgrade attacks |
| R4 | `wc_token` cookie is unencrypted (excluded from `encryptCookies`) | 🟡 **MEDIUM** | 5.3 | XSS can steal session token |
| R5 | No application-level RLS — coaches/admins can query any client data if controller has bug | 🟡 **MEDIUM** | 5.0 | IDOR (Insecure Direct Object Reference), horizontal privilege escalation |
| R6 | Session files not encrypted (`SESSION_ENCRYPT=false`) | 🟡 **MEDIUM** | 4.8 | Local file read exposes active sessions |
| R7 | `password_hash` is fillable in Client/Admin models | 🟡 **MEDIUM** | 4.3 | Mass assignment risk if validation bypassed |
| R8 | No `Clear-Site-Data` on logout | 🟢 **LOW** | 3.5 | Browser cache may retain sensitive data post-logout |
| R9 | CORS config includes local dev origin in repo | 🟢 **LOW** | 2.5 | Information disclosure, minor misconfiguration |
| R10 | `rehashPasswordIfRequired()` not implemented | 🟢 **LOW** | 2.0 | BCRYPT cost factor updates won't auto-rehash |

---

## 4. IMPLEMENTATION PHASES

> **RULE FOR ALL PHASES:**
> - Make changes in this order: **local → test → staging → production**
> - Each phase includes a **VALIDATION SCRIPT** and a **ROLLBACK PROCEDURE**
> - No database migrations that modify existing tables (per `AGENTS.md`)
> - No changes to Vue components, Blade layouts, CSS, or JavaScript bundles
> - All changes are additive (new middleware, new config, new scopes) or safe replacements

---

## PHASE 0: PREPARATION & BASELINE

**Goal:** Establish a safe working baseline before any hardening changes.

### Step 0.1 — Branch & Backup
```bash
git checkout -b security/hardening-2026-Q2
```

### Step 0.2 — Run Baseline Tests
```bash
# Run existing test suite to ensure nothing is broken BEFORE changes
php artisan test
npm run build
```
**Expected:** All tests pass, build succeeds.

### Step 0.3 — Document Current Environment
Verify `.env` values (do NOT commit `.env`):
```bash
# Confirm these are set correctly for your environment
grep -E "^(APP_ENV|APP_DEBUG|APP_URL|SESSION_ENCRYPT|SESSION_SECURE_COOKIE|SESSION_HTTP_ONLY|SESSION_SAME_SITE)" .env
```

**Required production values BEFORE proceeding:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wellcorefitness.com
SESSION_ENCRYPT=true          # PHASE 5 will set this
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

---

## PHASE 1: SECURITY HEADERS & CORS HARDENING

**Goal:** Add HSTS, refine CSP, harden CORS for production.

### 1.1 Add HSTS to ContentSecurityPolicy Middleware

**File:** `app/Http/Middleware/ContentSecurityPolicy.php`

**Current state (lines 41-45):**
```php
$response->header('Content-Security-Policy', $csp);
$response->header('X-Content-Type-Options', 'nosniff');
$response->header('X-Frame-Options', 'SAMEORIGIN');
$response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation()');
```

**Action:** Insert HSTS header BEFORE the `return $response;` on line 48.

**New code to add after line 45:**
```php
        // HSTS: enforce HTTPS for 1 year, include subdomains, allow preload
        // ONLY in production to avoid breaking local HTTP development
        if (app()->environment('production')) {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
```

**Validation:**
```bash
# After deploying, verify headers
curl -I -s https://wellcorefitness.com | grep -i "strict-transport-security"
# Expected: strict-transport-security: max-age=31536000; includeSubDomains; preload
```

**Rollback:** Remove the 4 lines added. The header disappears on next deploy.

### 1.2 Add Clear-Site-Data on Logout

**File:** `routes/web.php`

**Current state (lines 248-257):**
```php
Route::middleware('auth:wellcore')->group(function () {
    Route::post('/logout', function () {
        $token = session('wc_token');
        if ($token) {
            AuthToken::where('token', $token)->delete();
        }
        session()->flush();

        return redirect('/login');
    })->name('logout');
```

**Action:** Replace the logout closure with headers:

```php
Route::middleware('auth:wellcore')->group(function () {
    Route::post('/logout', function () {
        $token = session('wc_token');
        if ($token) {
            AuthToken::where('token', $token)->delete();
        }
        session()->flush();

        return redirect('/login')
            ->header('Clear-Site-Data', '"cache", "cookies", "storage"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    })->name('logout');
```

**UX Impact:** None. Users still redirect to `/login`. Browser simply clears cached data.

**Validation:**
```bash
curl -I -s -X POST https://wellcorefitness.com/logout -H "Authorization: Bearer <test_token>"
# Should see: Clear-Site-Data: "cache", "cookies", "storage"
```

### 1.3 Harden CORS Configuration for Production

**File:** `config/cors.php`

**Current state:**
```php
'allowed_origins' => [
    'http://wellcore-laravel.test',
    'https://wellcorefitness.com',
    'https://www.wellcorefitness.com',
],
```

**Action:** Make the local origin conditional:

```php
'allowed_origins' => array_filter([
    app()->environment('local') ? 'http://wellcore-laravel.test' : null,
    'https://wellcorefitness.com',
    'https://www.wellcorefitness.com',
]),
```

**Alternative (if array_filter causes issues with config caching):**
```php
'allowed_origins' => app()->environment('local')
    ? ['http://wellcore-laravel.test', 'https://wellcorefitness.com', 'https://www.wellcorefitness.com']
    : ['https://wellcorefitness.com', 'https://www.wellcorefitness.com'],
```

**UX Impact:** None for production users. Local dev still works.

### 1.4 Add `X-XSS-Protection` (Legacy Browser Support)

**File:** `app/Http/Middleware/ContentSecurityPolicy.php`

**Action:** Add after the HSTS block (or after Permissions-Policy):

```php
$response->header('X-XSS-Protection', '1; mode=block');
```

> Note: Modern browsers ignore this (CSP is preferred), but security scanners flag its absence.

---

## PHASE 2: NETWORK / PROXY HARDENING

**Goal:** Fix `trustProxies('*')` — the #1 critical vulnerability.

### 2.1 Restrict Trusted Proxies

**File:** `bootstrap/app.php`

**Current state (line 31):**
```php
$middleware->trustProxies(at: '*');
```

**Action:** Replace with actual proxy IPs.

**Option A — If behind Cloudflare (RECOMMENDED):**
```php
// Cloudflare IPv4 + IPv6 ranges (updated 2026)
// Source: https://www.cloudflare.com/ips/
$middleware->trustProxies(at: [
    '173.245.48.0/20',
    '103.21.244.0/22',
    '103.22.200.0/22',
    '103.31.4.0/22',
    '141.101.64.0/18',
    '108.162.192.0/18',
    '190.93.240.0/20',
    '188.114.96.0/20',
    '197.234.240.0/22',
    '198.41.128.0/17',
    '162.158.0.0/15',
    '104.16.0.0/13',
    '104.24.0.0/14',
    '172.64.0.0/13',
    '131.0.72.0/22',
    '2400:cb00::/32',
    '2606:4700::/32',
    '2803:f800::/32',
    '2405:b500::/32',
    '2405:8100::/32',
    '2a06:98c0::/29',
    '2c0f:f248::/32',
]);
```

**Option B — If behind AWS ALB / ELB:**
```php
// Use the ALB security group CIDR or specific subnet
// Example: internal VPC CIDR
$middleware->trustProxies(at: ['10.0.0.0/8']);
```

**Option C — If direct server (no reverse proxy):**
```php
// No trusted proxies — Laravel sees the direct connection
$middleware->trustProxies(at: []);
```

> ⚠️ **CRITICAL:** You MUST determine your actual infrastructure. Ask the sysadmin or check server logs for `X-Forwarded-For` sources. If unsure, use Option A (Cloudflare) as it's the most common LATAM setup.

**Validation:**
```php
// Add temporary test route or tinker:
Route::get('/debug/ip', function (Request $request) {
    return [
        'ip' => $request->ip(),
        'xff' => $request->header('X-Forwarded-For'),
        'xfp' => $request->header('X-Forwarded-Proto'),
        'trusted' => $request->isFromTrustedProxy(),
    ];
})->middleware('throttle:10,1');
```

**Rollback:** Change back to `at: '*'` (temporary, not recommended long-term).

---

## PHASE 3: AUTH & API ROUTE HARDENING

**Goal:** Move from "fail-open" (controller-level auth) to "fail-closed" (middleware-level auth + controller-level validation).

### 3.1 Create a Unified API Auth Middleware

The current `ApiBearerAuth` only validates the token exists but does NOT authenticate the user into Laravel's auth system. We need a middleware that:
1. Validates Bearer token
2. Authenticates user via `WellCoreGuard`
3. Sets up the auth context for the request

**File:** `app/Http/Middleware/ApiWellCoreAuth.php` (NEW)

```php
<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiWellCoreAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = substr($header, 7);
        $authToken = AuthToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $authToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Seed the session so WellCoreGuard can resolve the user
        // This is required because the Vue SPA sends Bearer tokens
        // but Laravel's auth('wellcore') needs the token in session
        // or the guard needs to read from the request.
        //
        // SAFER APPROACH: temporarily bind the token to the request
        // so WellCoreGuard can pick it up without polluting global session.

        // Since WellCoreGuard reads from session() first, then header, then cookie,
        // and the current guard does NOT read from $request->header('Authorization'),
        // we need to EITHER:
        //   A) Seed session (current pattern, but may affect concurrent requests)
        //   B) Modify WellCoreGuard to check $request->header('Authorization')
        //
        // Option B is cleaner and stateless. We'll implement it in 3.2.

        return $next($request);
    }
}
```

Wait — the current `WellCoreGuard::getTokenFromRequest()` does NOT read from `$this->request->header('Authorization')` in the standard way. It reads from:
1. `session('wc_token')`
2. `$this->request->header('Authorization')` — actually yes, it does! Line 96-98.

Let me re-read the guard... Yes, it DOES read from the request header. So `ApiBearerAuth` is actually **redundant** with `WellCoreGuard` for token validation. The issue is that routes don't USE `auth:wellcore` middleware.

So the fix is simpler:

### 3.1 SIMPLIFIED: Apply `auth:wellcore` to API Route Groups

**File:** `routes/api.php`

**Problem:** The client, coach, and admin API groups only have `'throttle:api'` but no auth middleware.

**Analysis of why this was done:** The auth API group (`/api/v/auth/*`) needs web session middleware (cookies). The other groups use Bearer tokens. The `WellCoreGuard` reads Bearer tokens, so `auth:wellcore` middleware SHOULD work.

**BUT** there's a subtle issue: `EnsureAuthenticated` middleware (aliased as `auth`) is designed for web routes — it redirects to `/login` on failure. For API routes, we need JSON 401 responses.

So we have TWO options:

**Option A (RECOMMENDED):** Use `auth:wellcore` middleware on API groups but configure unauthenticated responses to be JSON.

In `bootstrap/app.php`, within `withExceptions()`, add:
```php
$exceptions->render(function (AuthenticationException $e, Request $request) {
    if ($request->is('api/*') || $request->is('api/v/*')) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
    return null; // Let Laravel handle non-API auth exceptions normally
});
```

Then apply middleware to route groups in `routes/api.php`:

**For Client routes (line 67):**
```php
// BEFORE:
Route::prefix('v/client')->middleware('throttle:api')->group(function () {

// AFTER:
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**For Training routes (line 89):**
```php
// BEFORE:
Route::prefix('v/client')->middleware('throttle:api')->group(function () {

// AFTER:
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**For Social & Resources (line 113):**
```php
// BEFORE:
Route::prefix('v/client')->middleware('throttle:api')->group(function () {

// AFTER:
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**For Medals (line 148):**
```php
// BEFORE:
Route::prefix('v/client')->middleware('throttle:api')->group(function () {

// AFTER:
Route::prefix('v/client')->middleware(['auth:wellcore', 'throttle:api'])->group(function () {
```

**For RISE (line 155):**
```php
// BEFORE:
Route::prefix('v/rise')->middleware(['throttle:api', 'ensure.plan:metodo,elite,rise,presencial'])->group(function () {

// AFTER:
Route::prefix('v/rise')->middleware(['auth:wellcore', 'throttle:api', 'ensure.plan:metodo,elite,rise,presencial'])->group(function () {
```

**For Coach (line 181):**
```php
// BEFORE:
Route::prefix('v/coach')->middleware('throttle:api')->group(function () {

// AFTER:
Route::prefix('v/coach')->middleware(['auth:wellcore', 'throttle:api', 'role:coach,admin,superadmin,jefe'])->group(function () {
```

**For Admin (line 243):**
```php
// BEFORE:
Route::prefix('v/admin')->middleware('throttle:api')->group(function () {

// AFTER:
Route::prefix('v/admin')->middleware(['auth:wellcore', 'throttle:api', 'role:admin,superadmin,jefe'])->group(function () {
```

**CRITICAL SAFETY NOTE:**
The controllers currently use `resolveClientOrFail()`, `resolveCoachOrFail()`, etc. These methods should continue to work because `auth:wellcore` will populate `auth('wellcore')->user()`. The controller methods will get the same user, just with the guarantee that unauthenticated requests never reach the controller.

**However**, verify that `resolveClientOrFail()` uses `auth('wellcore')->user()` or similar. If it independently resolves the token, it will still work but now with defense-in-depth.

**Validation:**
```bash
# Test unauthenticated request to client API
curl -s https://wellcorefitness.com/api/v/client/dashboard
# Expected BEFORE: possibly returns data or empty (if controller didn't check)
# Expected AFTER: {"message":"Unauthenticated."} 401

# Test with valid token
curl -s -H "Authorization: Bearer <valid_token>" https://wellcorefitness.com/api/v/client/dashboard
# Expected: normal dashboard data 200
```

**Rollback:** Remove `'auth:wellcore'` and role middleware from route groups in `routes/api.php`.

### 3.2 Remove Redundant `ApiBearerAuth` Middleware

After applying `auth:wellcore` to routes, `ApiBearerAuth` is no longer needed anywhere. It was not aliased to routes in `api.php` anyway. Keep the file for now (no harm) or delete it in a later cleanup.

---

## PHASE 4: APPLICATION-LEVEL ROW-LEVEL SECURITY (RLS)

**Goal:** Ensure users can only access THEIR data, even if they bypass controller logic.

**MySQL Limitation:** MySQL does not have native RLS. We implement Laravel-native RLS via:
1. **Global Scopes** — auto-filter all queries by ownership
2. **Policies** — authorize actions (view, update, delete) per-resource
3. **Query Scopes** — reusable ownership constraints

### 4.1 Create Base RLS Scope

**File:** `app/Scopes/OwnedByClientScope.php` (NEW)

```php
<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OwnedByClientScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('wellcore')->user();

        // Only apply when a client is authenticated
        // Admins and coaches bypass this scope (they need to see all clients)
        if ($user instanceof \App\Models\Client) {
            $builder->where($model->qualifyColumn('client_id'), $user->id);
        }
    }
}
```

**File:** `app/Scopes/OwnedByCoachScope.php` (NEW)

```php
<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OwnedByCoachScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('wellcore')->user();

        if ($user instanceof \App\Models\Admin && in_array($user->role?->value, ['coach', 'admin', 'superadmin', 'jefe'])) {
            $builder->where($model->qualifyColumn('coach_id'), $user->id);
        }
    }
}
```

### 4.2 Apply Scopes to Sensitive Models

**CRITICAL:** Before applying scopes, verify that each model has the correct foreign key column. Check the database schema or model definitions.

**Example for Client-owned models:**

**File:** `app/Models/WorkoutSession.php`

Add to the model class:
```php
use App\Scopes\OwnedByClientScope;
use Illuminate\Database\Eloquent\Builder;

protected static function booted(): void
{
    static::addGlobalScope(new OwnedByClientScope);
}

// Allow admin/coach to bypass for legitimate queries
public function scopeWithoutOwnership(Builder $query): Builder
{
    return $query->withoutGlobalScope(OwnedByClientScope::class);
}
```

**Models to evaluate for Client ownership scope:**
- `WorkoutSession` — `client_id`?
- `WorkoutSet` / `WorkoutDay` — via `WorkoutSession`?
- `Checkin` — `client_id`?
- `ProgressPhoto` — `client_id`?
- `Metric` — `client_id`?
- `ClientRequest` — `client_id`?
- `PlanTicket` — `client_id`?
- `Notification` — `client_id` or `user_id`?
- `CommunityPost` / `CommunityComment` — `client_id`?
- `ClientNote` (coach notes about client) — this is OWNED by coach, VISIBLE to client

> ⚠️ **DO NOT apply scopes blindly.** For each model:
> 1. Check the actual column name in the database
> 2. Check if admins/coaches legitimately query ALL records
> 3. If coaches need full access, add `withoutGlobalScope()` in their controllers
> 4. If clients need limited access (e.g., only their own notifications), the scope is correct

### 4.3 Create Laravel Policies for Critical Resources

**File:** `app/Policies/ClientPolicy.php` (NEW)

```php
<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Client;

class ClientPolicy
{
    public function view(Client|Admin $user, Client $client): bool
    {
        // Clients can only view themselves
        if ($user instanceof Client) {
            return $user->id === $client->id;
        }

        // Coaches can view their assigned clients
        if ($user instanceof Admin && $user->role?->value === 'coach') {
            return $client->coach_id === $user->id;
        }

        // Admins can view all
        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }

    public function update(Client|Admin $user, Client $client): bool
    {
        if ($user instanceof Client) {
            return $user->id === $client->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }
}
```

**File:** `app/Policies/PlanTicketPolicy.php` (NEW)

```php
<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Client;
use App\Models\PlanTicket;

class PlanTicketPolicy
{
    public function view(Client|Admin $user, PlanTicket $ticket): bool
    {
        if ($user instanceof Client) {
            return $ticket->client_id === $user->id;
        }

        if ($user instanceof Admin && $user->role?->value === 'coach') {
            return $ticket->coach_id === $user->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }

    public function update(Client|Admin $user, PlanTicket $ticket): bool
    {
        if ($user instanceof Client) {
            return false; // Clients cannot update tickets
        }

        if ($user instanceof Admin && $user->role?->value === 'coach') {
            return $ticket->coach_id === $user->id;
        }

        return in_array($user->role?->value, ['admin', 'superadmin', 'jefe']);
    }
}
```

**Register policies** in a new or existing provider. Since there's no `AuthServiceProvider`, add to `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // ... existing boot code ...

    Gate::policy(\App\Models\Client::class, \App\Policies\ClientPolicy::class);
    Gate::policy(\App\Models\PlanTicket::class, \App\Policies\PlanTicketPolicy::class);
    // Add more as needed
}
```

### 4.4 Enforce Policies in Controllers

In controllers that fetch single resources by ID, add authorization checks:

**Example in `ClientController`:**
```php
public function profile(Request $request)
{
    $client = $this->resolveClientOrFail();
    // If resolveClientOrFail already returns the authenticated client,
    // no additional check is needed. But for methods that accept an ID:
}
```

For methods that accept an ID parameter (like `viewPhoto($id)`), add:
```php
$photo = ProgressPhoto::findOrFail($id);
$this->authorize('view', $photo); // uses ProgressPhotoPolicy
```

---

## PHASE 5: SESSION & COOKIE HARDENING

**Goal:** Encrypt sessions, harden cookies, reduce XSS impact.

### 5.1 Enable Session Encryption

**File:** `.env` (production only)

```env
SESSION_ENCRYPT=true
```

**Impact:** Session data stored in files will be encrypted with `APP_KEY`. If an attacker reads session files, they cannot decrypt without the app key.

**UX Impact:** None. Fully transparent to users.

**Validation:**
```bash
# After change, verify session files are encrypted
cat storage/framework/sessions/<session_id>
# Should be gibberish (encrypted), not plaintext JSON
```

### 5.2 Harden Session Cookie (SameSite)

**File:** `.env` (production)

```env
SESSION_SAME_SITE=strict
```

**Caution:** `strict` may break OAuth flows (Google login) because the initial redirect from Google is cross-site. Test Google login thoroughly.

**If Google login breaks, use:**
```env
SESSION_SAME_SITE=lax
```

### 5.3 Evaluate `wc_token` Cookie Encryption

**Current:** `wc_token` is in `encryptCookies(except: [...])` because the legacy vanilla PHP app sets it unencrypted.

**Risk:** If XSS occurs, JavaScript can read `document.cookie` and steal the token.

**Options:**

**Option A — Keep as-is (if vanilla compat is required indefinitely):**
Add `HttpOnly` flag via NGINX or middleware to at least prevent JS access.

**Option B — Migrate to encrypted cookie (if vanilla PHP app can be updated):**
Remove `'wc_token'` from the except list in `bootstrap/app.php`.
This requires the vanilla app to encrypt cookies with Laravel's `APP_KEY` (unlikely).

**Option C — Hybrid (RECOMMENDED for gradual migration):**
Keep the unencrypted cookie for vanilla compat, but:
1. Set `HttpOnly` on the cookie via the vanilla app
2. Add `Secure` flag (HTTPS only)
3. Reduce token lifetime from 7 days to 1 day for cookie-based sessions
4. Add device fingerprinting validation (already partially done via `fingerprint` in `AuthToken`)

**Action for Option C:**
In `AuthController::login()`, when setting the cookie (if cookie is set server-side), add flags. However, the cookie appears to be set by the vanilla PHP app, not Laravel.

**Mitigation in Laravel:** Add middleware to refresh cookie flags:

**File:** `app/Http/Middleware/HardenLegacyCookie.php` (NEW)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HardenLegacyCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If wc_token cookie exists, re-issue it with HttpOnly and Secure flags
        // This requires Laravel to be able to read the unencrypted cookie first
        $token = $request->cookie('wc_token');
        if ($token && app()->environment('production')) {
            $response->cookie(
                'wc_token',
                $token,
                60 * 24 * 7, // 7 days
                '/',
                null,
                true,  // secure
                true,  // httpOnly
                false, // raw
                'Lax'  // sameSite
            );
        }

        return $response;
    }
}
```

Register in `bootstrap/app.php`:
```php
$middleware->web(append: [
    // ... existing ...
    \App\Http\Middleware\HardenLegacyCookie::class,
]);
```

**Validation:**
```bash
curl -I -s https://wellcorefitness.com -H "Cookie: wc_token=test123"
# Look for Set-Cookie header with HttpOnly and Secure flags
```

---

## PHASE 6: DATABASE CONNECTION HARDENING

**Goal:** Harden the database connection without schema changes.

### 6.1 Enable SSL for MySQL Connection

**File:** `.env` (production)

```env
# If your MySQL server supports SSL (most managed DBs do)
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

**File:** `config/database.php` — already supports this via `env('MYSQL_ATTR_SSL_CA')` on line 63.

**If no CA file exists on the server, generate or obtain it from your hosting provider.**

### 6.2 Create a Read-Restricted DB User (Optional, Advanced)

Instead of using `root`, create a dedicated application user:

```sql
-- Run on MySQL as admin (NOT a migration — manual DBA operation)
CREATE USER 'wellcore_app'@'127.0.0.1' IDENTIFIED BY '<strong_random_password>';
GRANT SELECT, INSERT, UPDATE, DELETE ON wellcore_fitness.* TO 'wellcore_app'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Then update `.env`:
```env
DB_USERNAME=wellcore_app
DB_PASSWORD=<strong_random_password>
```

**Benefit:** If the app is compromised, the attacker cannot DROP tables or modify schema.

### 6.3 Enable MySQL Query Logging for Security Audit (Temporary)

```sql
-- Temporary, for audit only. Disable after 24-48 hours due to performance impact.
SET GLOBAL general_log = 'ON';
SET GLOBAL log_output = 'TABLE'; -- Logs to mysql.general_log (queryable)
```

Monitor for:
- Queries without `WHERE` clauses on sensitive tables
- Unusual `SELECT *` patterns from new IP addresses
- Failed login attempts

---

## PHASE 7: NGINX HARDENING

**Goal:** Add security headers at the web server layer for defense-in-depth.

**File:** `docs/nginx-config-easypanel.conf` (the production NGINX config)

**Current state:** No security headers in the server block.

**Action:** Add security headers to the main server block. These complement (not replace) the Laravel middleware headers. NGINX headers will appear on ALL responses including static files.

**Insert after `client_max_body_size 20M;`:**

```nginx
# ── Security Headers ────────────────────────────────────────
# NOTE: These are backup headers. Primary headers come from Laravel.
# NGINX adds these to static files (images, CSS, JS) that bypass PHP.

add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

# HSTS — only if SSL is terminated at NGINX and you're sure all subdomains use HTTPS
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

# Hide NGINX version
server_tokens off;
```

**Complete updated `nginx-config-easypanel.conf`:**

```nginx
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root {{ document_root }};

    index index.php index.html;

    server_name _;

    client_max_body_size 20M;

    # ── Security Headers ────────────────────────────────────────
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    server_tokens off;

    # ── Compresión ──────────────────────────────────────────────
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_comp_level 6;
    gzip_proxied any;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/json
        application/xml
        application/wasm
        image/svg+xml
        font/ttf
        font/otf;

    # ── Vite: hashed assets (app-abc123.js) → inmutable 1 año ──
    location ^~ /build/assets/ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable" always;
        access_log off;
        try_files $uri =404;
    }

    # ── Fonts self-hosted → inmutable 1 año ───────────────────
    location ^~ /fonts/ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable" always;
        access_log off;
        try_files $uri =404;
    }

    # ── Imágenes, íconos, SVG → 30 días ───────────────────────
    location ~* \.(png|jpe?g|gif|webp|avif|svg|ico)$ {
        expires 30d;
        add_header Cache-Control "public, max-age=2592000" always;
        access_log off;
        try_files $uri /index.php?$query_string;
    }

    # ── /js/*.js (Alpine self-hosted y similares) → 30 días ───
    location ~* ^/js/.*\.js$ {
        expires 30d;
        add_header Cache-Control "public, max-age=2592000" always;
        access_log off;
        try_files $uri =404;
    }

    # ── Laravel router ────────────────────────────────────────
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass {{ fpm_socket }};
    }
}
```

> ⚠️ **IMPORTANT:** If you're behind Cloudflare or another CDN, HSTS should ideally be set at the CDN level, not NGINX. However, setting it in both places is safe (browsers use the most restrictive value).

---

## PHASE 8: FINAL VALIDATION & MONITORING

### 8.1 Automated Security Headers Test

Create a simple test script:

**File:** `scripts/security-headers-check.php` (NEW)

```php
<?php

$requiredHeaders = [
    'content-security-policy' => null, // just check presence
    'strict-transport-security' => 'max-age=31536000',
    'x-content-type-options' => 'nosniff',
    'x-frame-options' => 'sameorigin',
    'referrer-policy' => 'strict-origin-when-cross-origin',
];

$domain = $argv[1] ?? 'https://wellcorefitness.com';
$ch = curl_init($domain);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$headers = strtolower($response);

$pass = true;
foreach ($requiredHeaders as $header => $value) {
    if (str_contains($headers, $header)) {
        echo "✅ {$header}\n";
    } else {
        echo "❌ MISSING: {$header}\n";
        $pass = false;
    }
}

exit($pass ? 0 : 1);
```

Run:
```bash
php scripts/security-headers-check.php https://wellcorefitness.com
```

### 8.2 CORS Validation

```bash
# Preflight request
curl -I -s -X OPTIONS \
  -H "Origin: https://evil.com" \
  -H "Access-Control-Request-Method: POST" \
  https://wellcorefitness.com/api/v/client/dashboard

# Expected: NO Access-Control-Allow-Origin header (origin rejected)

# Valid origin
curl -I -s -X OPTIONS \
  -H "Origin: https://wellcorefitness.com" \
  -H "Access-Control-Request-Method: POST" \
  https://wellcorefitness.com/api/v/client/dashboard

# Expected: Access-Control-Allow-Origin: https://wellcorefitness.com
```

### 8.3 API Auth Validation

```bash
# Without token — should return 401
curl -s -o /dev/null -w "%{http_code}" https://wellcorefitness.com/api/v/client/dashboard
# Expected: 401

# Without token to coach endpoint
curl -s -o /dev/null -w "%{http_code}" https://wellcorefitness.com/api/v/coach/dashboard
# Expected: 401

# Without token to admin endpoint
curl -s -o /dev/null -w "%{http_code}" https://wellcorefitness.com/api/v/admin/dashboard
# Expected: 401
```

### 8.4 RLS Validation (Application-Level)

```bash
# Authenticate as Client A, try to access Client B's workout session
# This should return 403 or empty result depending on implementation
curl -s -H "Authorization: Bearer <CLIENT_A_TOKEN>" \
  https://wellcorefitness.com/api/v/client/workout-summary/99999
# (where 99999 belongs to Client B)
# Expected: 403 or 404 (not 200 with data)
```

---

## 9. ROLLBACK PROCEDURES

| Phase | Rollback Action | Time to Rollback |
|-------|----------------|------------------|
| 1.1 HSTS | Remove HSTS block from `ContentSecurityPolicy.php` | 1 min |
| 1.2 Clear-Site-Data | Revert logout closure in `routes/web.php` | 1 min |
| 1.3 CORS | Revert `config/cors.php` | 1 min |
| 2.1 Trust Proxies | Change back to `at: '*'` | 1 min |
| 3.1 API Auth | Remove `auth:wellcore` and role middleware from `routes/api.php` | 2 min |
| 4.1 RLS Scopes | Remove `static::addGlobalScope()` from models | 5 min |
| 5.1 Session Encrypt | Set `SESSION_ENCRYPT=false` in `.env` | 1 min |
| 5.3 Cookie Hardener | Remove `HardenLegacyCookie` from `bootstrap/app.php` | 1 min |
| 7 NGINX | Restore previous NGINX config in EasyPanel | 2 min |

**Emergency full rollback:**
```bash
git checkout main
git branch -D security/hardening-2026-Q2
```

---

## 10. POST-IMPLEMENTATION CHECKLIST

- [ ] All existing PHPUnit tests pass (`php artisan test`)
- [ ] Vue SPA builds without errors (`npm run build`)
- [ ] Login works for clients (web + API)
- [ ] Login works for coaches (web + API)
- [ ] Login works for admins (web + API)
- [ ] Google OAuth login works
- [ ] Impersonation (admin → client) works
- [ ] Impersonation (coach → client) works
- [ ] Logout clears session and redirects
- [ ] File uploads still work (progress photos, logos, checkins)
- [ ] Wompi webhooks still process payments
- [ ] Chatbot API responds
- [ ] Newsletter subscription works
- [ ] Public forms work (`/api/v/public/*`)
- [ ] Shop API works (`/api/v/shop/*`)
- [ ] Security headers present on all responses
- [ ] CORS rejects invalid origins
- [ ] API returns 401 for unauthenticated requests
- [ ] RLS prevents cross-user data access
- [ ] No 500 errors in `storage/logs/laravel.log`
- [ ] PageSpeed/Lighthouse score unchanged or improved
- [ ] Mobile UX unchanged

---

## 11. ONGOING SECURITY TASKS (Not part of this deployment)

These are recommended but NOT in scope for this zero-downtime hardening:

1. **Implement `rehashPasswordIfRequired()`** in `WellCoreUserProvider` — update BCRYPT cost factor over time
2. **Add Form Request classes** for all API endpoints to centralize validation
3. **Add Laravel Policies** for ALL models (not just Client and PlanTicket)
4. **Implement device fingerprinting** on token validation (check `fingerprint` column)
5. **Add 2FA for admin accounts** (TOTP)
6. **Audit log all admin actions** (write to immutable log store)
7. **Rotate `APP_KEY`** annually (requires re-encrypting any encrypted data)
8. **Migrate from MySQL to PostgreSQL** if native RLS is desired (MAJOR project)
9. **Add WAF rules** (Cloudflare Pro/Enterprise or AWS WAF)
10. **Quarterly penetration testing** with external vendor

---

## APPENDIX A: COMPLETE FILE CHANGE SUMMARY

| File | Action | Lines Changed |
|------|--------|--------------|
| `app/Http/Middleware/ContentSecurityPolicy.php` | Add HSTS, X-XSS-Protection | +8 |
| `routes/web.php` | Harden logout response headers | +4 |
| `config/cors.php` | Conditional local origin | +3 |
| `bootstrap/app.php` | Restrict trustProxies, add API auth exception handler | +10 |
| `routes/api.php` | Add `auth:wellcore` + role middleware to all protected groups | ~12 groups |
| `app/Scopes/OwnedByClientScope.php` | NEW | ~20 |
| `app/Scopes/OwnedByCoachScope.php` | NEW | ~20 |
| `app/Policies/ClientPolicy.php` | NEW | ~30 |
| `app/Policies/PlanTicketPolicy.php` | NEW | ~35 |
| `app/Providers/AppServiceProvider.php` | Register policies | +3 |
| `app/Http/Middleware/HardenLegacyCookie.php` | NEW | ~25 |
| `bootstrap/app.php` | Register HardenLegacyCookie | +1 |
| `.env` | SESSION_ENCRYPT=true, SESSION_SAME_SITE | +2 |
| `docs/nginx-config-easypanel.conf` | Add security headers, server_tokens off | +8 |
| `scripts/security-headers-check.php` | NEW validation script | ~30 |

---

## APPENDIX B: ARCHITECTURAL DECISION RECORDS (ADRs)

### ADR-1: Why Application-Level RLS instead of MySQL RLS
- **Context:** MySQL 8.x does not support native CREATE ROW SECURITY POLICY
- **Decision:** Implement RLS via Laravel Global Scopes + Policies
- **Consequences:** Protection is at application layer. Direct DB access bypasses RLS. Mitigated by using restricted DB user.

### ADR-2: Why auth middleware on API routes is safe
- **Context:** Controllers currently resolve auth manually via traits
- **Decision:** Add `auth:wellcore` middleware as defense-in-depth
- **Consequences:** Unauthenticated requests fail at middleware (401) before reaching controller. Controllers still work as before.

### ADR-3: Why HSTS in both Laravel and NGINX
- **Context:** Static files bypass PHP/Laravel
- **Decision:** Set headers in both layers
- **Consequences:** Duplicated headers on HTML responses (harmless). Static files get headers from NGINX only.

### ADR-4: Why wc_token cookie remains unencrypted
- **Context:** Legacy vanilla PHP app shares this cookie
- **Decision:** Keep unencrypted but harden flags (HttpOnly, Secure, SameSite)
- **Consequences:** XSS can still read the cookie if injected before the hardening middleware runs. Long-term: migrate vanilla app to use Laravel-encrypted cookies or eliminate shared cookie.

---

*End of Security Audit & Implementation Plan*
