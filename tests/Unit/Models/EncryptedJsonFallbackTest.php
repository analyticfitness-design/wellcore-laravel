<?php

use App\Casts\EncryptedJsonFallback;
use App\Models\PaymentLog;

describe('EncryptedJsonFallback cast', function () {
    test('encrypts data on write and decrypts on read via model', function () {
        $log = new PaymentLog();
        $cast = new EncryptedJsonFallback();

        $data = ['event' => 'test', 'amount' => 95000];
        $encrypted = $cast->set($log, 'payload', $data, []);

        expect($encrypted)->toBeString()
            ->not->toContain('test')   // no plaintext
            ->not->toContain('95000');

        $decrypted = $cast->get($log, 'payload', $encrypted, []);
        expect($decrypted)->toBe($data);
    });

    test('falls back to plain JSON for legacy plaintext rows', function () {
        $log = new PaymentLog();
        $cast = new EncryptedJsonFallback();

        $plaintext = json_encode(['legacy' => true, 'amount' => 50000]);
        $result = $cast->get($log, 'payload', $plaintext, []);

        expect($result)->toBe(['legacy' => true, 'amount' => 50000]);
    });

    test('returns null when value is null', function () {
        $log = new PaymentLog();
        $cast = new EncryptedJsonFallback();

        expect($cast->get($log, 'payload', null, []))->toBeNull();
        expect($cast->set($log, 'payload', null, []))->toBeNull();
    });

    test('encrypted output is different for each call (non-deterministic)', function () {
        $log = new PaymentLog();
        $cast = new EncryptedJsonFallback();
        $data = ['key' => 'value'];

        $enc1 = $cast->set($log, 'payload', $data, []);
        $enc2 = $cast->set($log, 'payload', $data, []);

        // Laravel Crypt uses random IVs — ciphertext differs per call
        expect($enc1)->not->toBe($enc2);
    });
});
