<?php

use function Pest\Laravel\get;

describe('Security Headers', function () {
    test('public pages have X-Content-Type-Options header', function () {
        get('/')->assertHeader('X-Content-Type-Options', 'nosniff');
    });

    test('public pages have X-Frame-Options header', function () {
        get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    });

    test('public pages have Referrer-Policy header', function () {
        get('/')->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    });

    test('robots.txt blocks private areas', function () {
        $response = get('/robots.txt');
        $content = $response->getContent();
        expect($content)->toContain('Disallow: /client/');
        expect($content)->toContain('Disallow: /admin/');
        expect($content)->toContain('Disallow: /coach/');
    });
});
