<?php

use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;

/**
 * Auth Flow Tests
 *
 * Verify the WellCoreGuard authentication middleware correctly protects
 * dashboard routes and redirects unauthenticated visitors to /login.
 */

describe('Auth Flow', function () {

    test('unauthenticated user is redirected from client dashboard', function () {
        $this->get('/client')->assertRedirect('/login');
    });

    test('unauthenticated user is redirected from coach dashboard', function () {
        $this->get('/coach')->assertRedirect('/login');
    });

    test('unauthenticated user is redirected from admin dashboard', function () {
        $this->get('/admin')->assertRedirect('/login');
    });

    test('unauthenticated user is redirected from rise dashboard', function () {
        $this->get('/rise')->assertRedirect('/login');
    });

    test('login page renders with sign-in form', function () {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Iniciar');
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
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
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
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        $this->withSession(['wc_token' => $token])
            ->get('/admin')
            ->assertStatus(200);

        AuthToken::where('token', $token)->delete();
    });

    test('expired token is rejected and redirected to login', function () {
        $token = bin2hex(random_bytes(32));

        // Do not create the token in DB — simulate an expired / invalid token
        $this->withSession(['wc_token' => $token])
            ->get('/client')
            ->assertRedirect('/login');
    });

    test('authenticated user on login page is redirected away', function () {
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

        $this->withSession(['wc_token' => $token])
            ->get('/login')
            ->assertRedirect('/client');

        AuthToken::where('token', $token)->delete();
    });

});
