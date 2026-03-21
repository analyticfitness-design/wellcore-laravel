<?php

/**
 * Newsletter API Tests
 *
 * Verify the newsletter subscription endpoint validates and stores emails.
 */

test('newsletter subscribes valid email', function () {
    $response = $this->postJson('/api/newsletter', [
        'email' => 'test_' . time() . '@example.com',
    ]);

    $response->assertStatus(200)->assertJson(['success' => true]);
});

test('newsletter rejects invalid email', function () {
    $response = $this->postJson('/api/newsletter', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422);
});
