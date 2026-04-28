<?php

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Enums\UserType;
use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;
use App\Models\ImpersonationLog;
use Illuminate\Support\Facades\DB;

it('chain superadmin to coach to client preserves root token', function () {
    $super = Admin::create([
        'username' => 'super_'.uniqid(), 'name' => 'Super',
        'password_hash' => password_hash('x', PASSWORD_BCRYPT),
        'role' => UserRole::Superadmin, 'active' => true,
    ]);
    $coach = Admin::create([
        'username' => 'coach_'.uniqid(), 'name' => 'Pedro',
        'password_hash' => password_hash('x', PASSWORD_BCRYPT),
        'role' => UserRole::Coach, 'active' => true,
    ]);
    $client = Client::create([
        'name' => 'Juan', 'email' => 'juan_'.uniqid().'@x.test',
        'plan' => PlanType::Metodo->value,
        'password_hash' => password_hash('x', PASSWORD_BCRYPT),
    ]);
    // Link client to coach via plan_tickets (only linkage table available in test DB)
    DB::table('plan_tickets')->insert([
        'coach_id'           => $coach->id,
        'coach_name'         => $coach->name,
        'client_id'          => $client->id,
        'client_name'        => $client->name,
        'plan_type'          => 'entrenamiento',
        'category'           => 'plan_nuevo',
        'status'             => 'borrador',
        'datos_generales'    => '{}',
        'plan_entrenamiento' => '{}',
        'created_at'         => now(),
        'updated_at'         => now(),
    ]);

    $superToken = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => UserType::Admin->value, 'user_id' => $super->id,
        'token'      => $superToken, 'expires_at' => now()->addDays(7),
    ]);

    // Step 1: superadmin -> coach
    $r1 = $this->withHeaders(['Authorization' => "Bearer $superToken", 'Accept' => 'application/json'])
               ->postJson("/api/v/admin/coaches/{$coach->id}/impersonate");
    $r1->assertOk();
    $coachImpToken = $r1->json('token');

    // Step 2: as the impersonated coach -> client
    $r2 = $this->withHeaders(['Authorization' => "Bearer $coachImpToken", 'Accept' => 'application/json'])
               ->postJson("/api/v/coach/clients/{$client->id}/impersonate");
    $r2->assertOk();
    $clientImpToken = $r2->json('token');

    $log2 = ImpersonationLog::query()->where('token', $clientImpToken)->first();
    expect($log2->actor_type)->toBe('admin');
    expect((int) $log2->actor_id)->toBe((int) $super->id);
    expect($log2->via_actor_type)->toBe('admin');
    expect((int) $log2->via_actor_id)->toBe((int) $coach->id);

    // Step 3: end -> should restore root, close BOTH logs, delete BOTH impersonation tokens
    $r3 = $this->withHeaders(['Authorization' => "Bearer $clientImpToken", 'Accept' => 'application/json'])
               ->postJson('/api/v/admin/impersonate/end');
    $r3->assertOk()->assertJson(['root_token' => $superToken]);

    expect(AuthToken::where('token', $coachImpToken)->first())->toBeNull();
    expect(AuthToken::where('token', $clientImpToken)->first())->toBeNull();
    expect(AuthToken::where('token', $superToken)->first())->not->toBeNull();
    expect(ImpersonationLog::where('token', $coachImpToken)->first()->ended_at)->not->toBeNull();
    expect(ImpersonationLog::where('token', $clientImpToken)->first()->ended_at)->not->toBeNull();
});

it('logout during impersonation redirects to stop and preserves root', function () {
    $super = Admin::create([
        'username' => 'sup_'.uniqid(), 'name' => 'S',
        'password_hash' => password_hash('x', PASSWORD_BCRYPT),
        'role' => UserRole::Superadmin, 'active' => true,
    ]);
    $coach = Admin::create([
        'username' => 'co_'.uniqid(), 'name' => 'C',
        'password_hash' => password_hash('x', PASSWORD_BCRYPT),
        'role' => UserRole::Coach, 'active' => true,
    ]);
    $superToken = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type' => UserType::Admin->value, 'user_id' => $super->id,
        'token' => $superToken, 'expires_at' => now()->addDays(7),
    ]);

    $r1 = $this->withHeaders(['Authorization' => "Bearer $superToken", 'Accept' => 'application/json'])
               ->postJson("/api/v/admin/coaches/{$coach->id}/impersonate");
    $impToken = $r1->json('token');

    $this->withHeaders(['Authorization' => "Bearer $impToken", 'Accept' => 'application/json'])
         ->postJson('/api/v/auth/logout')
         ->assertOk()
         ->assertJson(['redirect_url' => '/admin/coaches']);

    expect(AuthToken::where('token', $superToken)->first())->not->toBeNull();
    expect(AuthToken::where('token', $impToken)->first())->toBeNull();
});

it('audit helper auto attaches impersonation_log_id during chain', function () {
    $super = Admin::create([
        'username' => 's_'.uniqid(), 'name' => 'S',
        'password_hash' => password_hash('x', PASSWORD_BCRYPT),
        'role' => UserRole::Superadmin, 'active' => true,
    ]);
    $coach = Admin::create([
        'username' => 'c_'.uniqid(), 'name' => 'C',
        'password_hash' => password_hash('x', PASSWORD_BCRYPT),
        'role' => UserRole::Coach, 'active' => true,
    ]);
    $superToken = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type' => UserType::Admin->value, 'user_id' => $super->id,
        'token' => $superToken, 'expires_at' => now()->addDays(7),
    ]);

    $r = $this->withHeaders(['Authorization' => "Bearer $superToken", 'Accept' => 'application/json'])
              ->postJson("/api/v/admin/coaches/{$coach->id}/impersonate");
    $logId = $r->json('log_id');

    session(['wc_impersonation_chain' => [
        ['log_id' => $logId, 'token' => $r->json('token'), 'target_type' => 'admin', 'target_id' => $coach->id, 'target_name' => 'C'],
    ]]);

    $caller = new class { use \App\Traits\Auditable; public function go() { $this->audit('test.during.imp'); } };
    $caller->go();

    $auditRow = \App\Models\AuditLog::query()->latest('id')->first();
    expect($auditRow->diff['impersonation_log_id'] ?? null)->toBe($logId);
});
