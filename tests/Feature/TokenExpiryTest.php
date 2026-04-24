<?php

use App\Http\Middleware\EnsureAuthenticated;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Token Expiry & Auth Escalation Tests (E.4)
 *
 * Covers:
 *  - Expired tokens are rejected by API layer (401 JSON)
 *  - Valid tokens are accepted by API layer (200/403)
 *  - C.1 fix: client token sent as admin_token body param is rejected by EnsureAuthenticated
 *  - Legitimate admin impersonation: admin token as admin_token body param is accepted
 *  - Bearer token is accepted by the API controller's resolveAuthUser()
 *  - Missing/malformed tokens return 401 JSON
 *
 * Auth architecture:
 *  - /api/v/client/*, /api/v/admin/*, /api/v/coach/*: NO Laravel middleware auth — controllers
 *    call AuthenticatesVueRequests::resolveAuthUser() which reads bearerToken() directly.
 *  - EnsureAuthenticated (alias: 'auth') is NOT used on api.php routes.
 *    It's registered for web routes using 'auth' (no guard suffix), currently none active.
 *  - WellCoreGuard backs 'auth:wellcore' (web logout / impersonation routes).
 *
 * C.1 tests exercise EnsureAuthenticated by invoking it directly on a synthetic request,
 * as there are no current web routes that use the bare 'auth' alias.
 */
describe('Token Expiry', function () {

    // ─── Expired token — API endpoints ──────────────────────────────────

    test('expired client token returns 401 on client api endpoint', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/dashboard')
            ->assertStatus(401);

        AuthToken::where('token', $token)->delete();
    });

    test('expired admin token returns 401 on admin api endpoint', function () {
        $admin = Admin::first();
        if (! $admin) {
            $this->markTestSkipped('No admins in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/admin/dashboard')
            ->assertStatus(401);

        AuthToken::where('token', $token)->delete();
    });

    // ─── SPA shells always 200 (no auth middleware on web Route::view routes) ────

    test('expired token still loads client spa shell (auth is api-layer only)', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->subMinutes(5),
        ]);

        // SPA shells use Route::view — no auth middleware. Always 200.
        // Vue Router handles the 401 from API calls on the client side.
        $this->withSession(['wc_token' => $token])
            ->get('/client')
            ->assertStatus(200);

        AuthToken::where('token', $token)->delete();
    });

    // ─── Valid token — API endpoints ─────────────────────────────────────

    test('valid client bearer token is accepted by client api endpoint', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/dashboard');

        // 200 for active clients; 403 for inactive. Either means the token was resolved.
        expect($response->status())->toBeIn([200, 403]);

        AuthToken::where('token', $token)->delete();
    });

    // ─── Missing / malformed tokens ───────────────────────────────────────

    test('missing bearer token returns 401 json on client api endpoint', function () {
        // Controllers call abort(401, 'Token invalido o expirado.') — not the Laravel default message.
        $this->getJson('/api/v/client/dashboard')
            ->assertStatus(401)
            ->assertJson(['message' => 'Token invalido o expirado.']);
    });

    test('missing bearer token returns 401 json on admin api endpoint', function () {
        // Controllers call abort(401, 'Token invalido o expirado.') — not the Laravel default message.
        $this->getJson('/api/v/admin/dashboard')
            ->assertStatus(401)
            ->assertJson(['message' => 'Token invalido o expirado.']);
    });

    test('malformed bearer token returns 401 json on client api endpoint', function () {
        $this->withHeaders(['Authorization' => 'Bearer not-a-real-token-xyz'])
            ->getJson('/api/v/client/dashboard')
            ->assertStatus(401);
    });

    // ─── Fix C.1 — admin_token body param security (EnsureAuthenticated unit-style) ──────

    /**
     * EnsureAuthenticated::resolveToken() validates that a token sent via the
     * admin_token POST body param belongs to an admin before seeding the session.
     * A client token must be rejected (returns null) — it must not gain elevated access.
     *
     * These tests call the middleware directly with a synthetic Request since no current
     * web route uses the bare 'auth' alias. This validates the fix at the middleware level.
     */

    test('EnsureAuthenticated rejects client token sent as admin_token body param (fix C.1)', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        // Build a POST request with admin_token body param (client token)
        $request = Request::create('/fake-protected-route', 'POST', ['admin_token' => $token]);
        $request->headers->set('Accept', 'application/json');

        $middleware = new EnsureAuthenticated();
        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        // Client token as admin_token must not seed session and must return 401
        expect($response->status())->toBe(401);

        AuthToken::where('token', $token)->delete();
    });

    test('EnsureAuthenticated accepts valid admin token sent as admin_token body param', function () {
        $admin = Admin::whereIn('role', ['admin', 'superadmin', 'jefe'])->first();
        if (! $admin) {
            $this->markTestSkipped('No admin/superadmin/jefe in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        // Build a POST request with admin_token body param (admin token)
        $request = Request::create('/fake-protected-route', 'POST', ['admin_token' => $token]);
        $request->headers->set('Accept', 'application/json');

        // Session must be started for the middleware to seed it
        $request->setLaravelSession(app('session')->driver());

        $middleware = new EnsureAuthenticated();
        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        // Admin token as admin_token must be accepted (next() called → 200)
        expect($response->status())->toBe(200);

        AuthToken::where('token', $token)->delete();
    });

    test('EnsureAuthenticated rejects expired token from Bearer header', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->subMinutes(1),
        ]);

        $request = Request::create('/fake-protected-route', 'GET');
        $request->headers->set('Authorization', "Bearer {$token}");
        $request->headers->set('Accept', 'application/json');

        $middleware = new EnsureAuthenticated();
        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        expect($response->status())->toBe(401);

        AuthToken::where('token', $token)->delete();
    });

    test('EnsureAuthenticated accepts valid token from Bearer header', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        $request = Request::create('/fake-protected-route', 'GET');
        $request->headers->set('Authorization', "Bearer {$token}");
        $request->headers->set('Accept', 'application/json');

        $middleware = new EnsureAuthenticated();
        $response = $middleware->handle($request, fn ($req) => response()->json(['ok' => true]));

        expect($response->status())->toBe(200);

        AuthToken::where('token', $token)->delete();
    });

});
