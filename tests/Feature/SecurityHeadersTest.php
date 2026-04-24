<?php

describe('Security Headers', function () {
    test('public pages have X-Content-Type-Options header', function () {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff');
    });

    test('public pages have X-Frame-Options header', function () {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    });

    test('public pages have Referrer-Policy header', function () {
        $this->get('/')->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    });

    test('CSP header contains a nonce in script-src', function () {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        expect($csp)->not->toBeNull();

        // Nonce format: 'nonce-<base64>' inside the script-src directive.
        // base64_encode(random_bytes(16)) produces 24 characters of [A-Za-z0-9+/=].
        expect($csp)->toMatch("/'nonce-[A-Za-z0-9+\/=]+'/");
    });

    test('CSP script-src retains unsafe-inline and unsafe-eval', function () {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        // unsafe-inline must remain as a safety net for older browsers.
        expect($csp)->toContain("'unsafe-inline'");

        // unsafe-eval must remain for Alpine.js.
        expect($csp)->toContain("'unsafe-eval'");
    });

    test('robots.txt blocks private areas', function () {
        $response = $this->get('/robots.txt');
        $content = $response->getContent();
        expect($content)->toContain('Disallow: /client/');
        expect($content)->toContain('Disallow: /admin/');
        expect($content)->toContain('Disallow: /coach/');
    });
});
