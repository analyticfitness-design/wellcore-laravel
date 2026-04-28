<?php

use App\Enums\UserRole;
use App\Enums\UserType;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\ImpersonationLog;

function makeImpersonateSuperadmin(): array
{
    $superadmin = Admin::create([
        'username'      => 'super_test_'.uniqid(),
        'name'          => 'Super Test',
        'password_hash' => password_hash('test', PASSWORD_BCRYPT),
        'role'          => UserRole::Superadmin,
        'active'        => true,
    ]);
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => UserType::Admin->value,
        'user_id'    => $superadmin->id,
        'token'      => $token,
        'expires_at' => now()->addDays(7),
    ]);
    return [$superadmin, $token];
}

function makeImpersonateCoach(): Admin
{
    return Admin::create([
        'username'      => 'coach_test_'.uniqid(),
        'name'          => 'Pedro Test',
        'password_hash' => password_hash('test', PASSWORD_BCRYPT),
        'role'          => UserRole::Coach,
        'active'        => true,
    ]);
}

it('superadmin can start coach impersonation', function () {
    [$superadmin, $superToken] = makeImpersonateSuperadmin();
    $coach = makeImpersonateCoach();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$superToken,
        'Accept'        => 'application/json',
    ])->postJson("/api/v/admin/coaches/{$coach->id}/impersonate");

    $response->assertOk()
        ->assertJsonStructure(['token', 'redirect_url', 'log_id', 'expires_at'])
        ->assertJson(['redirect_url' => '/coach']);

    $this->assertDatabaseHas('impersonation_logs', [
        'actor_id'    => $superadmin->id,
        'target_type' => 'admin',
        'target_id'   => $coach->id,
    ]);
});

it('admin non-super cannot impersonate', function () {
    makeImpersonateSuperadmin();
    $coach = makeImpersonateCoach();

    $admin = Admin::create([
        'username'      => 'plain_admin_'.uniqid(),
        'name'          => 'Admin Plain',
        'password_hash' => password_hash('test', PASSWORD_BCRYPT),
        'role'          => UserRole::Admin,
        'active'        => true,
    ]);
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => UserType::Admin->value,
        'user_id'    => $admin->id,
        'token'      => $token,
        'expires_at' => now()->addDays(7),
    ]);

    $this->withHeaders(['Authorization' => "Bearer $token", 'Accept' => 'application/json'])
         ->postJson("/api/v/admin/coaches/{$coach->id}/impersonate")
         ->assertStatus(403);
});

it('unauthenticated cannot impersonate', function () {
    $coach = makeImpersonateCoach();

    $this->postJson("/api/v/admin/coaches/{$coach->id}/impersonate")
         ->assertStatus(401);
});

it('cannot impersonate yourself', function () {
    [$superadmin, $superToken] = makeImpersonateSuperadmin();

    $this->withHeaders(['Authorization' => "Bearer {$superToken}", 'Accept' => 'application/json'])
         ->postJson("/api/v/admin/coaches/{$superadmin->id}/impersonate")
         ->assertStatus(422);
});

it('cannot impersonate a superadmin', function () {
    [$_super, $superToken] = makeImpersonateSuperadmin();

    $other = Admin::create([
        'username'      => 'super2_'.uniqid(),
        'name'          => 'Super Two',
        'password_hash' => password_hash('test', PASSWORD_BCRYPT),
        'role'          => UserRole::Superadmin,
        'active'        => true,
    ]);

    $this->withHeaders(['Authorization' => "Bearer {$superToken}", 'Accept' => 'application/json'])
         ->postJson("/api/v/admin/coaches/{$other->id}/impersonate")
         ->assertStatus(422);
});

it('cannot impersonate nonexistent admin', function () {
    [$_super, $superToken] = makeImpersonateSuperadmin();

    $this->withHeaders(['Authorization' => "Bearer {$superToken}", 'Accept' => 'application/json'])
         ->postJson('/api/v/admin/coaches/9999999/impersonate')
         ->assertStatus(404);
});

it('start creates auth_token with impersonation_log_id', function () {
    [$_super, $superToken] = makeImpersonateSuperadmin();
    $coach = makeImpersonateCoach();

    $this->withHeaders(['Authorization' => "Bearer {$superToken}", 'Accept' => 'application/json'])
         ->postJson("/api/v/admin/coaches/{$coach->id}/impersonate")
         ->assertOk();

    $token = AuthToken::where('user_id', $coach->id)->latest('id')->first();
    expect($token)->not->toBeNull();
    expect($token->impersonation_log_id)->not->toBeNull();
});

it('end closes log and deletes impersonation token', function () {
    [$_super, $superToken] = makeImpersonateSuperadmin();
    $coach = makeImpersonateCoach();

    $resp = $this->withHeaders(['Authorization' => "Bearer {$superToken}", 'Accept' => 'application/json'])
                 ->postJson("/api/v/admin/coaches/{$coach->id}/impersonate");

    $coachToken = $resp->json('token');
    $logId      = $resp->json('log_id');

    $this->withHeaders(['Authorization' => "Bearer $coachToken", 'Accept' => 'application/json'])
         ->postJson('/api/v/admin/impersonate/end')
         ->assertOk()
         ->assertJson(['ok' => true]);

    expect(ImpersonationLog::find($logId)->ended_at)->not->toBeNull();
    expect(AuthToken::where('token', $coachToken)->first())->toBeNull();
});

it('end is idempotent when no chain', function () {
    [$_super, $superToken] = makeImpersonateSuperadmin();

    $this->withHeaders(['Authorization' => "Bearer {$superToken}", 'Accept' => 'application/json'])
         ->postJson('/api/v/admin/impersonate/end')
         ->assertOk()
         ->assertJson(['ok' => true, 'noop' => true]);
});
