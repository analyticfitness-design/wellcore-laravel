<?php

use App\Services\PricingService;
use Carbon\Carbon;

describe('PricingService — promo-aware pricing', function () {
    afterEach(fn () => Carbon::setTestNow());

    test('isPromoActive returns true when within promo window', function () {
        Carbon::setTestNow('2026-04-27 12:00:00');
        expect(app(PricingService::class)->isPromoActive())->toBeTrue();
    });

    test('isPromoActive returns false after promo ends_at day', function () {
        Carbon::setTestNow('2026-05-01 00:01:00');
        expect(app(PricingService::class)->isPromoActive())->toBeFalse();
    });

    test('isPromoActive is inclusive of the ends_at day until end-of-day', function () {
        Carbon::setTestNow('2026-04-30 23:59:59');
        expect(app(PricingService::class)->isPromoActive())->toBeTrue();
    });

    test('priceCop returns promo price during active window', function () {
        Carbon::setTestNow('2026-04-27');
        $pricing = app(PricingService::class);
        expect($pricing->priceCop('esencial'))->toBe(config('plans.esencial.price_cop'));
    });

    test('priceCop returns original price after promo window', function () {
        Carbon::setTestNow('2026-05-01 00:01:00');
        $pricing = app(PricingService::class);
        expect($pricing->priceCop('esencial'))->toBe(config('plans.esencial.price_cop_original'));
    });

    test('priceFor is an alias for priceCop', function () {
        $pricing = app(PricingService::class);
        foreach (['esencial', 'metodo', 'elite', 'rise'] as $plan) {
            expect($pricing->priceFor($plan))->toBe($pricing->priceCop($plan));
        }
    });

    test('originalPriceFor always returns non-discounted price regardless of promo', function () {
        $pricing = app(PricingService::class);
        Carbon::setTestNow('2026-04-27'); // during promo
        expect($pricing->originalPriceFor('metodo'))->toBe(config('plans.metodo.price_cop_original'));
        Carbon::setTestNow('2026-05-02'); // after promo
        expect($pricing->originalPriceFor('metodo'))->toBe(config('plans.metodo.price_cop_original'));
    });

    test('discountPercent returns configured pct during promo', function () {
        Carbon::setTestNow('2026-04-27');
        $pricing = app(PricingService::class);
        $expected = config('plans.promo.discount_pct');
        foreach (['esencial', 'metodo', 'elite'] as $plan) {
            expect($pricing->discountPercent($plan))->toBe($expected);
        }
    });

    test('discountPercent returns 0 after promo ends', function () {
        Carbon::setTestNow('2026-05-01 00:01:00');
        $pricing = app(PricingService::class);
        foreach (['esencial', 'metodo', 'elite'] as $plan) {
            expect($pricing->discountPercent($plan))->toBe(0);
        }
    });

    test('priceCop returns 0 for unknown plan', function () {
        $pricing = app(PricingService::class);
        expect($pricing->priceCop('unknown_plan'))->toBe(0);
    });

    test('allPrices returns array with all plan keys', function () {
        $pricing = app(PricingService::class);
        $prices = $pricing->allPrices();
        expect($prices)->toHaveKeys(['esencial', 'metodo', 'elite', 'rise']);
    });

    test('configFor returns full plan config array', function () {
        $pricing = app(PricingService::class);
        $cfg = $pricing->configFor('metodo');
        expect($cfg)->toBeArray()
            ->toHaveKey('price_cop')
            ->toHaveKey('price_cop_original')
            ->toHaveKey('name');
    });

    test('configFor returns null for unknown plan', function () {
        $pricing = app(PricingService::class);
        expect($pricing->configFor('bogus'))->toBeNull();
    });
});
