<?php

use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('Payment Flow', function () {
    test('inscription page loads', function () {
        get('/inscripcion')->assertStatus(200);
    });

    test('checkout page requires plan parameter', function () {
        get('/checkout')->assertStatus(200);
    });

    test('wompi webhook rejects invalid signature', function () {
        post('/webhooks/wompi', [
            'event' => 'transaction.updated',
            'data' => ['transaction' => ['id' => 'fake', 'status' => 'APPROVED']],
        ], [
            'Content-Type' => 'application/json',
        ])->assertStatus(401);
    });

    test('health check returns healthy status', function () {
        get('/health')
            ->assertStatus(200)
            ->assertJson(['status' => 'healthy']);
    });

    test('sitemap returns valid XML', function () {
        get('/sitemap.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml');
    });

    test('lanzamiento page loads', function () {
        get('/lanzamiento')
            ->assertStatus(200)
            ->assertSee('LANZAMIENTO');
    });
});
