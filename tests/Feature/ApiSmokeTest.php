<?php

use App\Models\Admin;
use App\Models\AuthToken;
use App\Models\Client;

/**
 * API Smoke Tests (E.5)
 *
 * Happy path + sad path for critical API endpoints.
 * Auth layer: AuthenticatesVueRequests reads bearerToken() directly.
 * No EnsureAuthenticated middleware on /api/v/client/* or /api/v/admin/*.
 *
 * Token lifecycle: created per-test, deleted in afterEach via $this->tokens[].
 */
describe('API Smoke Tests', function () {

    // Track created tokens for cleanup
    beforeEach(function () {
        $this->tokens = [];
    });

    afterEach(function () {
        if (! empty($this->tokens)) {
            AuthToken::whereIn('token', $this->tokens)->delete();
        }
    });

    // ─── Sad paths — unauthenticated / unauthorized ──────────────────────

    test('GET /api/v/client/dashboard without auth returns 401 json', function () {
        // EnsureAuthenticated middleware returns 'Unauthenticated.' when no token is present.
        $this->getJson('/api/v/client/dashboard')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    });

    test('GET /api/v/admin/dashboard without auth returns 401 json', function () {
        // EnsureAuthenticated middleware returns 'Unauthenticated.' when no token is present.
        $this->getJson('/api/v/admin/dashboard')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    });

    test('POST /api/v/wompi/webhook with invalid payload returns 403', function () {
        // Wompi webhook validates HMAC signature — unsigned payload must be rejected.
        $this->postJson('/webhooks/wompi', [
            'event' => 'transaction.updated',
            'data'  => ['transaction' => ['id' => 'fake-smoke-test', 'status' => 'APPROVED']],
        ])->assertStatus(403);
    });

    // ─── Expired token ────────────────────────────────────────────────────

    test('GET /api/v/client/dashboard with expired token returns 401 json', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens[] = $token;

        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->subMinutes(10),
        ]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/dashboard')
            ->assertStatus(401);
    });

    // ─── Happy paths ──────────────────────────────────────────────────────

    test('GET /api/v/client/dashboard with valid client token returns 200 with expected keys', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        // Skip inactive clients — they return 403
        if ($client->status?->value !== 'activo' && (string) $client->status !== 'activo') {
            // Try to find an active client
            $activeClient = Client::where('status', 'activo')->first();
            if (! $activeClient) {
                $this->markTestSkipped('No active clients in database');
            }
            $client = $activeClient;
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens[] = $token;

        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/dashboard');

        $response->assertStatus(200);

        // Verify top-level keys present in the dashboard payload
        $response->assertJsonStructure([
            'greeting',
            'clientName',
            'planLabel',
            'streakDays',
            'checkinsThisMonth',
            'xpTotal',
            'level',
            'weekDays',
            'recentActivity',
            'dailyMissions',
        ]);
    });

    test('GET /api/v/admin/dashboard with valid admin token returns 200 with production/financial/operational keys', function () {
        $admin = Admin::whereIn('role', ['admin', 'superadmin', 'jefe'])->first();
        if (! $admin) {
            $this->markTestSkipped('No admin/superadmin/jefe in database');
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens[] = $token;

        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/admin/dashboard');

        $response->assertStatus(200);

        // Verify the three main dashboard sections are present
        $response->assertJsonStructure([
            'production' => [
                'plan_tickets_pendientes',
                'plan_tickets_en_revision',
                'checkins_sin_responder_global',
                'support_tickets_abiertos',
            ],
            'financial' => [
                'mrr_actual_cop',
                'mrr_mes_anterior_cop',
                'mrr_delta_pct',
                'pagos_pendientes_cop',
            ],
            'operational' => [
                'clientes_activos',
                'clientes_nuevos_mes',
                'coaches_activos',
                'tasa_retencion_mes_pct',
            ],
        ]);
    });

    // ─── Role isolation — client cannot access admin endpoint ────────────

    test('client token cannot access admin dashboard (returns 401 or 403)', function () {
        $client = Client::first();
        if (! $client) {
            $this->markTestSkipped('No clients in database');
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens[] = $token;

        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        // AdminController::resolveAdminOrFail() aborts with 403 when userType is Client
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/admin/dashboard')
            ->assertStatus(403);
    });

    test('admin token cannot access client dashboard (returns 401 or 403)', function () {
        $admin = Admin::first();
        if (! $admin) {
            $this->markTestSkipped('No admins in database');
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens[] = $token;

        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        // ClientController::resolveClientOrFail() aborts with 403 when userType is Admin
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/dashboard')
            ->assertStatus(403);
    });

    // ─── Coach endpoint smoke ─────────────────────────────────────────────

    test('GET /api/v/coach/dashboard without auth returns 401 json', function () {
        // EnsureAuthenticated middleware returns 'Unauthenticated.' when no token is present.
        $this->getJson('/api/v/coach/dashboard')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    });

    // ─── Training endpoint smoke (E.5) ────────────────────────────────────

    test('GET /api/v/client/training without auth returns 401 json', function () {
        $this->getJson('/api/v/client/training')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    });

    test('GET /api/v/client/training with valid active client token returns 200 with days array', function () {
        $client = Client::where('status', 'activo')->first();
        if (! $client) {
            $this->markTestSkipped('No active clients in database');
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens[] = $token;

        AuthToken::create([
            'user_type'  => 'client',
            'user_id'    => $client->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/training');

        $response->assertStatus(200);

        // TrainingController::training() always returns a 7-element 'days' array
        // (ISO week calendar) and metadata keys.
        $response->assertJsonStructure([
            'days' => [
                '*' => ['date', 'dayNumber', 'dayName', 'isToday', 'completed'],
            ],
        ]);

        expect($response->json('days'))->toHaveCount(7);
    });

    test('GET /api/v/client/training with admin token returns 403', function () {
        $admin = Admin::whereIn('role', ['admin', 'superadmin', 'jefe'])->first();
        if (! $admin) {
            $this->markTestSkipped('No admin in database');
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens[] = $token;

        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'expires_at' => now()->addDay(),
        ]);

        // resolveClientOrFail() aborts with 403 for admin user_type.
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v/client/training')
            ->assertStatus(403);
    });

});
