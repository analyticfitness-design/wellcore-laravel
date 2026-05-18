<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

it('defaults to es when no cookie and no user', function (): void {
    app()->setLocale('es');
    $request = Request::create('/');

    (new SetLocale())->handle($request, fn () => null);

    expect(app()->getLocale())->toBe('es');
});

it('respects wc_locale cookie when no auth user', function (): void {
    $request = Request::create('/');
    $request->cookies->set('wc_locale', 'en');

    (new SetLocale())->handle($request, fn () => null);

    expect(app()->getLocale())->toBe('en');
});

it('ignores unsupported cookie values and stays in default', function (): void {
    app()->setLocale('es');
    $request = Request::create('/');
    $request->cookies->set('wc_locale', 'fr');

    (new SetLocale())->handle($request, fn () => null);

    expect(app()->getLocale())->toBe('es');
});

it('prefers authenticated user locale over cookie', function (): void {
    $user = new class () {
        public string $locale = 'en';
    };

    $request = Request::create('/');
    $request->cookies->set('wc_locale', 'es');
    $request->setUserResolver(fn () => $user);

    (new SetLocale())->handle($request, fn () => null);

    expect(app()->getLocale())->toBe('en');
});

it('falls back to cookie when user locale is unsupported', function (): void {
    $user = new class () {
        public string $locale = 'pt';
    };

    $request = Request::create('/');
    $request->cookies->set('wc_locale', 'en');
    $request->setUserResolver(fn () => $user);

    (new SetLocale())->handle($request, fn () => null);

    expect(app()->getLocale())->toBe('en');
});
