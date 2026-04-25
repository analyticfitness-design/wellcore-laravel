<?php

use App\Enums\UserType;
use App\Models\Admin;
use App\Models\AuthToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function actingAsSuperadmin(Admin $admin): Tests\TestCase
{
    $token = bin2hex(random_bytes(32));
    AuthToken::create([
        'user_type'  => 'admin',
        'user_id'    => $admin->id,
        'token'      => $token,
        'expires_at' => now()->addDay(),
    ]);
    return test()->withHeaders(['Authorization' => "Bearer {$token}"]);
}

it('superadmin puede obtener preview HTML del email de credenciales de coach', function () {
    $superadmin = Admin::factory()->create(['role' => 'superadmin', 'active' => true]);

    $response = actingAsSuperadmin($superadmin)
        ->postJson('/api/v/admin/coaches/manage/preview', [
            'name'     => 'María García',
            'username' => 'maria.garcia',
            'email'    => 'maria@ejemplo.com',
        ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['html'])
             ->assertJsonPath('html', fn ($html) => str_contains($html, 'María García'));
});

it('retorna 401 sin token', function () {
    $this->postJson('/api/v/admin/coaches/manage/preview', [
        'name'     => 'Test',
        'username' => 'test',
        'email'    => 'test@test.com',
    ])->assertStatus(401);
});

it('retorna 422 si faltan campos requeridos', function () {
    $superadmin = Admin::factory()->create(['role' => 'superadmin', 'active' => true]);

    actingAsSuperadmin($superadmin)
        ->postJson('/api/v/admin/coaches/manage/preview', [])
        ->assertStatus(422);
});
