<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SessionStartTest — Sprint 5 (login Livewire iOS-feel)
|--------------------------------------------------------------------------
| Cubre 10 escenarios de paridad funcional vs la SPA Vue anterior para
| App\Livewire\Auth\Login. Validar 19 gaps cerrados en commit adce276e.
*/

use App\Enums\PlanType;
use App\Enums\UserType;
use App\Livewire\Auth\Login;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    RateLimiter::clear('wc-login:127.0.0.1');
});

it('renders form on /login with hero copy', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('INICIAR')
        ->assertSee('Sin')
        ->assertSee('ciencia');
});

it('rejects invalid credentials with no token created', function () {
    $tokensBefore = AuthToken::count();

    Livewire::test(Login::class)
        ->set('identity', 'no-such-user@example.com')
        ->set('password', 'wrong-pass')
        ->call('login')
        ->assertSet('errorMessage', 'No encontramos una cuenta con esas credenciales.')
        ->assertSet('loginSuccess', false);

    expect(AuthToken::count())->toBe($tokensBefore);
});

it('starts session for admin and creates token with all fields', function () {
    $admin = Admin::factory()->superadmin()->create([
        'username'      => 'sesstest_'.uniqid(),
        'password_hash' => Hash::make('Secret123!'),
    ]);

    Livewire::test(Login::class)
        ->set('identity', $admin->username)
        ->set('password', 'Secret123!')
        ->call('login')
        ->assertDispatched('login-success')
        ->assertSet('loginSuccess', true)
        ->assertSet('errorMessage', '');

    $token = AuthToken::where('user_type', UserType::Admin->value)
        ->where('user_id', $admin->id)
        ->latest('id')
        ->first();

    expect($token)->not->toBeNull();
    expect($token->token)->toHaveLength(64);
    expect($token->fingerprint)->not->toBeNull();
    expect($token->ip_address)->not->toBeNull();
    expect($token->last_used_at)->not->toBeNull();
    expect($token->expires_at)->not->toBeNull();
});

it('sets all session keys for SPA compatibility', function () {
    $admin = Admin::factory()->superadmin()->create([
        'username'      => 'sesskeys_'.uniqid(),
        'password_hash' => Hash::make('Secret123!'),
    ]);

    Livewire::test(Login::class)
        ->set('identity', $admin->username)
        ->set('password', 'Secret123!')
        ->call('login');

    expect(session('wc_token'))->not->toBeNull();
    expect(session('wc_user_type'))->toBe(UserType::Admin->value);
    expect(session('wc_user_id'))->toBe($admin->id);
    expect(session('wc_user_portal'))->toBe('/admin');
});

it('rate limits after 5 failed attempts per IP', function () {
    $key = 'wc-login:127.0.0.1';
    RateLimiter::clear($key);

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('identity', 'ghost-'.$i.'@example.com')
            ->set('password', 'wrong')
            ->call('login');
    }

    expect(RateLimiter::tooManyAttempts($key, 5))->toBeTrue();

    Livewire::test(Login::class)
        ->set('identity', 'ghost-final@example.com')
        ->set('password', 'wrong')
        ->call('login')
        ->assertSet('errorMessage', fn ($msg) => str_contains($msg, 'Demasiados intentos'));
});

it('detects must_change_password and propagates flag', function () {
    $admin = Admin::factory()->superadmin()->create([
        'username'             => 'mustchange_'.uniqid(),
        'password_hash'        => Hash::make('Secret123!'),
        'must_change_password' => true,
    ]);

    Livewire::test(Login::class)
        ->set('identity', $admin->username)
        ->set('password', 'Secret123!')
        ->call('login')
        ->assertDispatched('login-success', forcePasswordChange: true);
});

it('redirects admin coach to /coach', function () {
    $coach = Admin::factory()->coach()->create([
        'username'      => 'coachredir_'.uniqid(),
        'password_hash' => Hash::make('Secret123!'),
    ]);

    Livewire::test(Login::class)
        ->set('identity', $coach->username)
        ->set('password', 'Secret123!')
        ->call('login')
        ->assertDispatched('login-success', redirectUrl: '/coach', userPortal: '/coach')
        ->assertRedirect('/coach');
});

it('redirects rise client to /rise', function () {
    $client = Client::factory()->create([
        'email'         => 'rise_'.uniqid().'@example.com',
        'password_hash' => Hash::make('Secret123!'),
        'plan'          => PlanType::Rise->value,
    ]);

    Livewire::test(Login::class)
        ->set('identity', $client->email)
        ->set('password', 'Secret123!')
        ->call('login')
        ->assertDispatched('login-success', redirectUrl: '/rise', userPortal: '/rise')
        ->assertRedirect('/rise');
});

it('redirects regular client to /client', function () {
    $client = Client::factory()->create([
        'email'         => 'metodo_'.uniqid().'@example.com',
        'password_hash' => Hash::make('Secret123!'),
        'plan'          => PlanType::Metodo->value,
    ]);

    Livewire::test(Login::class)
        ->set('identity', $client->email)
        ->set('password', 'Secret123!')
        ->call('login')
        ->assertDispatched('login-success', redirectUrl: '/client', userPortal: '/client')
        ->assertRedirect('/client');
});

it('rememberMe extends token to 30 days', function () {
    $admin = Admin::factory()->superadmin()->create([
        'username'      => 'remember_'.uniqid(),
        'password_hash' => Hash::make('Secret123!'),
    ]);

    Livewire::test(Login::class)
        ->set('identity', $admin->username)
        ->set('password', 'Secret123!')
        ->set('rememberMe', false)
        ->call('login');

    $tokenShort = AuthToken::where('user_id', $admin->id)
        ->where('user_type', UserType::Admin->value)
        ->latest('id')->first();

    $daysShort = now()->diffInDays($tokenShort->expires_at, false);
    expect($daysShort)->toBeGreaterThanOrEqual(6)
        ->and($daysShort)->toBeLessThanOrEqual(7);

    Livewire::test(Login::class)
        ->set('identity', $admin->username)
        ->set('password', 'Secret123!')
        ->set('rememberMe', true)
        ->call('login');

    $tokenLong = AuthToken::where('user_id', $admin->id)
        ->where('user_type', UserType::Admin->value)
        ->latest('id')->first();

    $daysLong = now()->diffInDays($tokenLong->expires_at, false);
    expect($daysLong)->toBeGreaterThanOrEqual(29)
        ->and($daysLong)->toBeLessThanOrEqual(30);
});
