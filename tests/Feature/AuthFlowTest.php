<?php

use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;

/**
 * Auth Flow Tests
 *
 * SPA design: /client, /coach, /admin, /rise serve the Vue SPA shell (Route::view, no
 * server-side auth middleware). Auth is enforced at the API layer (/api/v/*) via Bearer
 * token. Vue Router handles client-side redirect to /login when the API returns 401.
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
