<?php

describe('Payment Flow', function () {
    test('inscription page loads', function () {
        $this->get('/inscripcion')->assertStatus(200);
    });

    test('presencial inscription page loads', function () {
        $this->get('/presencial/inscripcion')->assertStatus(200);
    });

    test('wompi webhook rejects invalid signature with 403', function () {
        $this->post('/webhooks/wompi', [
            'event' => 'transaction.updated',
            'data' => ['transaction' => ['id' => 'fake', 'status' => 'APPROVED']],
        ], [
            'Content-Type' => 'application/json',
        ])->assertStatus(403);
    });

    test('health check returns healthy status', function () {
        $this->get('/health')
            ->assertStatus(200)
            ->assertJson(['status' => 'healthy']);
    });

    test('sitemap returns valid XML', function () {
        $this->get('/sitemap.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml');
    });

    test('lanzamiento page loads', function () {
        $this->get('/lanzamiento')
            ->assertStatus(200)
            ->assertSee('LANZAMIENTO');
    });
});
