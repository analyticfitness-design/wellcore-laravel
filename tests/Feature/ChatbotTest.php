<?php

/**
 * Chatbot API Tests
 *
 * Verify the public chatbot endpoint responds correctly to various inputs.
 */

test('chatbot responds to plans question', function () {
    $response = $this->postJson('/api/chat', [
        'message' => 'cuanto cuestan los planes?',
        'session_id' => 'test_' . time(),
    ]);

    $response->assertStatus(200)->assertJsonStructure(['message']);
    $this->assertStringContainsString('299', $response->json('message'));
});

test('chatbot responds to greeting', function () {
    $response = $this->postJson('/api/chat', [
        'message' => 'hola',
        'session_id' => 'test_' . time(),
    ]);

    $response->assertStatus(200)->assertJsonStructure(['message']);
});

test('chatbot handles unknown question', function () {
    $response = $this->postJson('/api/chat', [
        'message' => 'xyz random nonsense',
        'session_id' => 'test_' . time(),
    ]);

    $response->assertStatus(200)->assertJsonStructure(['message']);
});
