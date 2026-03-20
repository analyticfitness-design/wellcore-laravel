<?php

use App\Models\Client;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Payment;
use App\Enums\PlanType;
use App\Enums\UserRole;

test('client model reads from database', function () {
    $count = Client::count();
    expect($count)->toBeGreaterThanOrEqual(0);
});

test('admin model reads from database', function () {
    $count = Admin::count();
    expect($count)->toBeGreaterThanOrEqual(0);
});

test('client plan casts to enum', function () {
    $client = Client::first();
    if (!$client) {
        $this->markTestSkipped('No clients');
    }
    expect($client->plan)->toBeInstanceOf(PlanType::class);
});

test('admin role casts to enum', function () {
    $admin = Admin::first();
    if (!$admin) {
        $this->markTestSkipped('No admins');
    }
    expect($admin->role)->toBeInstanceOf(UserRole::class);
});

test('auth token expires correctly', function () {
    $token = new AuthToken();
    $token->expires_at = now()->subHour();
    expect($token->isExpired())->toBeTrue();

    $token->expires_at = now()->addHour();
    expect($token->isExpired())->toBeFalse();
});
