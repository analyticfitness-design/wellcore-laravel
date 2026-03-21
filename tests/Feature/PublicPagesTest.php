<?php

/**
 * Public Pages Tests
 *
 * Verify all public-facing marketing pages load correctly (HTTP 200).
 */

test('home page loads', function () {
    $this->get('/')->assertStatus(200);
});

test('metodo page loads', function () {
    $this->get('/metodo')->assertStatus(200);
});

test('proceso page loads', function () {
    $this->get('/proceso')->assertStatus(200);
});

test('planes page loads', function () {
    $this->get('/planes')->assertStatus(200);
});

test('nosotros page loads', function () {
    $this->get('/nosotros')->assertStatus(200);
});

test('faq page loads', function () {
    $this->get('/faq')->assertStatus(200);
});

test('rise page loads', function () {
    $this->get('/reto-rise')->assertStatus(200);
});

test('blog page loads', function () {
    $this->get('/blog')->assertStatus(200);
});

test('coaches page loads', function () {
    $this->get('/coaches')->assertStatus(200);
});

test('fit page loads', function () {
    $this->get('/fit')->assertStatus(200);
});

test('presencial page loads', function () {
    $this->get('/presencial')->assertStatus(200);
});

test('terminos page loads', function () {
    $this->get('/terminos')->assertStatus(200);
});

test('privacidad page loads', function () {
    $this->get('/privacidad')->assertStatus(200);
});

test('politica cookies page loads', function () {
    $this->get('/politica-cookies')->assertStatus(200);
});

test('reembolsos page loads', function () {
    $this->get('/reembolsos')->assertStatus(200);
});
