<?php

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;

/**
 * Auth Flow Tests (E.4)
 *
 * SPA design: /client, /coach, /admin, /rise serve the Vue SPA shell (Route::view, no
 * server-side auth middleware). Auth is enforced at the API layer (/api/v/*) via Bearer
 * token. Vue Router handles client-side redirect to /login when the API returns 401.
 *
 * Additional E.4 scenarios added here:
 *  - Login API invalid credentials (wrong password / unknown identity)
 *  - Login API missing fields (validation)
 *  - Coach-role admin bearer token accessing /coach SPA shell
 *  - Coach-role admin bearer token accessing /api/v/coach/dashboard
 *  - Impersonation: coach-issued client token can access /api/v/client/dashboard
 */
describe('Auth Flow', function () {

    test('unauthenticated user can load client spa shell', function () {
        $this->get('/client')->assertStatus(200);
    });

    test('unauthenticated user can load coach spa shell', function () {
        $this->get('/coach')->assertStatus(200);
    });

    test('unauthenticated user can load admin spa shell', function () {
        $this->get('/admin')->assertStatus(200);
    });

    test('unauthenticated user can load rise spa shell', function () {
        $this->get('/rise')->assertStatus(200);
    });

    test('login page renders spa shell', function () {
        // /login serves the Vue SPA shell — "Iniciar" text rendered client-side by Vue.
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('vue-app');
    });

    test('forgot password page is accessible to guests', function () {
        $this->get('/forgot-password')->assertStatus(200);
    });

    test('authenticated client can access client dashboard', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type' => 'client',
            'user_id' => $client->id,
            'token' => $token,
            'expires_at' => now()->addDay(),
        ]);

        $this->withSession(['wc_token' => $token])
            ->get('/client')
            ->assertStatus(200);

        AuthToken::where('token', $token)->delete();
    });

    test('authenticated admin can access admin dashboard', function () {
        $admin = Admin::first();
        if (! $admin) {
            $this->markTestSkipped('No admins in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type' => 'admin',
            'user_id' => $admin->id,
            'token' => $token,
            'expires_at' => now()->addDay(),
        ]);

        $this->withSession(['wc_token' => $token])
            ->get('/admin')
            ->assertStatus(200);

        AuthToken::where('token', $token)->delete();
    });

    test('invalid session token still loads spa shell (api will reject with 401)', function () {
        $token = bin2hex(random_bytes(32));

        // /client is the Vue SPA shell — always 200. The invalid token is rejected
        // when Vue makes API calls which return 401, triggering client-side redirect.
        $this->withSession(['wc_token' => $token])
            ->get('/client')
            ->assertStatus(200);
    });

    test('authenticated user on login page is redirected away', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type' => 'client',
            'user_id' => $client->id,
            'token' => $token,
            'expires_at' => now()->addDay(),
        ]);

        $this->withSession(['wc_token' => $token])
            ->get('/login')
            ->assertRedirect('/client');

        AuthToken::where('token', $token)->delete();
    });

});

// ─── E.4 — Login API with invalid credentials ────────────────────────────────

describe('Login API — invalid credentials', function () {

    /**
     * POST /api/v/auth/login — AuthController::login()
     * Returns 422 JSON with a message for wrong password or unknown identity.
     * (Never 401 — validation errors and credential failures both return 422.)
     */

    test('login with wrong password returns 422 json', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $this->postJson('/api/v/auth/login', [
            'identity' => $client->email,
            'password' => 'definitely-wrong-password-xyz',
        ])->assertStatus(422)
            ->assertJsonStructure(['message']);
    });

    test('login with nonexistent identity returns 422 json', function () {
        $this->postJson('/api/v/auth/login', [
            'identity' => 'nonexistent_user_' . uniqid() . '@example.com',
            'password' => 'anyPassword123',
        ])->assertStatus(422)
            ->assertJsonStructure(['message']);
    });

    test('login with missing identity field returns 422 json', function () {
        $this->postJson('/api/v/auth/login', [
            'password' => 'somepassword',
        ])->assertStatus(422)
            ->assertJsonStructure(['message']);
    });

    test('login with missing password field returns 422 json', function () {
        $this->postJson('/api/v/auth/login', [
            'identity' => 'someone@example.com',
        ])->assertStatus(422)
            ->assertJsonStructure(['message']);
    });

    test('login with empty body returns 422 json', function () {
        $this->postJson('/api/v/auth/login', [])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    });

});

// ─── E.4 — Coach-role bearer token ───────────────────────────────────────────

describe('Coach role bearer token access', function () {

    /**
     * Admin records with role='coach' use the admin user_type in auth_tokens.
     * CoachController::resolveCoachOrFail() accepts coach/admin/superadmin/jefe roles.
     * /coach SPA shell is Route::view — always 200 regardless of token.
     */

    test('coach-role admin token can load the /coach spa shell', function () {
        $coach = Admin::where('role', UserRole::Coach)->first();
        if (! $coach) {
            $this->markTestSkipped('No coach-role admin in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $coach->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        $this->withSession(['wc_token' => $token])
            ->get('/coach')
            ->assertStatus(200);

        AuthToken::where('token', $token)->delete();
    });

    test('coach-role admin bearer token is accepted by /api/v/coach/dashboard', function () {
        $coach = Admin::where('role', UserRole::Coach)->first();
        if (! $coach) {
            $this->markTestSkipped('No coach-role admin in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $coach->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        // 200 is the expected happy path; allow 500 only if a DB relation is absent
        // (this is a smoke test — not a data integrity test).
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/coach/dashboard')
            ->assertStatus(200);

        AuthToken::where('token', $token)->delete();
    });

    test('coach-role admin token is rejected by /api/v/client/dashboard (403)', function () {
        $coach = Admin::where('role', UserRole::Coach)->first();
        if (! $coach) {
            $this->markTestSkipped('No coach-role admin in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $coach->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        // ClientController::resolveClientOrFail() aborts with 403 for admin user_type.
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/dashboard')
            ->assertStatus(403);

        AuthToken::where('token', $token)->delete();
    });

});

// ─── E.4 — Admin/coach impersonation token ───────────────────────────────────

describe('Impersonation token access', function () {

    /**
     * CoachController::impersonate() issues a short-lived AuthToken with
     * user_type='client' for the target client. That impersonation token must:
     *  - be accepted by /api/v/client/dashboard (200 for active clients)
     *  - be rejected by /api/v/admin/dashboard (403 — wrong user_type)
     *
     * This test simulates the token created by impersonate() directly, without
     * going through the POST endpoint (which requires a real coach session).
     */

    test('impersonation token (client user_type) is accepted by client dashboard', function () {
        $client = Client::where('status', 'activo')->first();
        if (! $client) {
            $this->markTestSkipped('No active clients in database');
        }

        // Mirror what CoachController::impersonate() does: issue a client-type token
        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addHours(2), // short-lived, like the real impersonation token
        ]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/dashboard')
            ->assertStatus(200);

        AuthToken::where('token', $token)->delete();
    });

    test('impersonation token (client user_type) is rejected by admin dashboard (403)', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addHours(2),
        ]);

        // AdminController::resolveAdminOrFail() must reject client-type tokens with 403.
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/admin/dashboard')
            ->assertStatus(403);

        AuthToken::where('token', $token)->delete();
    });

});
