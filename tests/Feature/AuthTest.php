<?php

use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;

test('login page loads', function () {
    $this->get('/login')->assertStatus(200);
});

test('public home page loads', function () {
    $this->get('/')->assertStatus(200);
});

test('planes page loads', function () {
    $this->get('/planes')->assertStatus(200);
});

test('faq page loads', function () {
    $this->get('/faq')->assertStatus(200);
});

test('client dashboard requires authentication', function () {
    $this->get('/client')->assertRedirect('/login');
});

test('admin dashboard requires authentication', function () {
    $this->get('/admin')->assertRedirect('/login');
});

test('coach dashboard requires authentication', function () {
    $this->get('/coach')->assertRedirect('/login');
});

test('authenticated client can access dashboard', function () {
    $client = Client::first();
    if (!$client) {
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

    // Cleanup
    AuthToken::where('token', $token)->delete();
});

test('authenticated admin can access admin dashboard', function () {
    $admin = Admin::first();
    if (!$admin) {
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

test('test dashboard page loads', function () {
    $this->get('/test')->assertStatus(200);
});
