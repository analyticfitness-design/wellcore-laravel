<?php

/**
 * SEO Tests
 *
 * Verify structured data, Open Graph meta tags, canonical URLs,
 * and sitemap completeness across public marketing pages.
 */

describe('SEO', function () {

    test('home has JSON-LD structured data', function () {
        $this->get('/')->assertSee('application/ld+json', false);
    });

    test('home has Open Graph meta tags', function () {
        $response = $this->get('/');
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('og:image', false);
    });

    test('home has canonical URL', function () {
        $this->get('/')->assertSee('rel="canonical"', false);
    });

    test('planes has OfferCatalog schema', function () {
        $this->get('/planes')->assertSee('OfferCatalog', false);
    });

    test('faq has FAQPage schema', function () {
        $this->get('/faq')->assertSee('FAQPage', false);
    });

    test('sitemap includes all public pages', function () {
        $response = $this->get('/sitemap.xml');
        $response->assertSee('/metodo', false);
        $response->assertSee('/planes', false);
        $response->assertSee('/proceso', false);
        $response->assertSee('/blog', false);
    });

    test('sitemap.xml returns valid XML with correct content-type', function () {
        $this->get('/sitemap.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', false);
    });

    test('home has Twitter card meta tags', function () {
        $response = $this->get('/');
        $response->assertSee('twitter:card', false);
        $response->assertSee('twitter:title', false);
    });

    test('planes page has Open Graph meta tags', function () {
        $response = $this->get('/planes');
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
    });

    test('faq page has canonical URL', function () {
        $this->get('/faq')->assertSee('rel="canonical"', false);
    });

});
