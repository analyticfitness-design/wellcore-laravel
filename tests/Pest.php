<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests use the wellcore_fitness_test DB (see phpunit.xml).
| That DB must have the production schema (run: mysqldump --no-data wellcore_fitness | mysql wellcore_fitness_test).
| RefreshDatabase is intentionally kept OFF until the test DB has the full
| vanilla-PHP schema (clients, assigned_plans, etc.) — P3.1 in the audit plan.
|
*/

pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Architecture');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Authenticate a Client for the current test via Bearer token.
 */
function actingAsClient(\App\Models\Client $client): \App\Models\Client
{
    $token = \App\Models\AuthToken::create([
        'token'      => bin2hex(random_bytes(32)),
        'user_type'  => 'client',
        'user_id'    => $client->id,
        'ip_address' => '127.0.0.1',
        'expires_at' => now()->addDay(),
        'created_at' => now(),
    ]);
    test()->withHeaders(['Authorization' => "Bearer {$token->token}"]);

    return $client;
}
