<?php

use App\Models\AssignedPlan;
use App\Services\PricingService;
use Carbon\Carbon;

describe('AssignedPlan::isExpired', function () {
    test('returns false when expires_at is null', function () {
        $plan = new AssignedPlan(['expires_at' => null]);

        expect($plan->isExpired())->toBeFalse();
    });

    test('returns true when expires_at is today', function () {
        Carbon::setTestNow('2026-04-24 12:00:00');
        $plan = new AssignedPlan(['expires_at' => '2026-04-24']);

        expect($plan->isExpired())->toBeTrue();

        Carbon::setTestNow();
    });

    test('returns true when expires_at is yesterday', function () {
        Carbon::setTestNow('2026-04-24 12:00:00');
        $plan = new AssignedPlan(['expires_at' => '2026-04-23']);

        expect($plan->isExpired())->toBeTrue();

        Carbon::setTestNow();
    });

    test('returns false when expires_at is tomorrow', function () {
        Carbon::setTestNow('2026-04-24 12:00:00');
        $plan = new AssignedPlan(['expires_at' => '2026-04-25']);

        expect($plan->isExpired())->toBeFalse();

        Carbon::setTestNow();
    });
});

describe('AssignedPlan::daysUntilExpiry', function () {
    test('returns null when expires_at is null', function () {
        $plan = new AssignedPlan(['expires_at' => null]);

        expect($plan->daysUntilExpiry())->toBeNull();
    });

    test('returns positive days when plan is active', function () {
        Carbon::setTestNow('2026-04-24 10:00:00');
        $plan = new AssignedPlan(['expires_at' => '2026-05-10']);

        expect($plan->daysUntilExpiry())->toBe(16);

        Carbon::setTestNow();
    });

    test('returns 0 when plan expires today', function () {
        Carbon::setTestNow('2026-04-24 10:00:00');
        $plan = new AssignedPlan(['expires_at' => '2026-04-24']);

        expect($plan->daysUntilExpiry())->toBe(0);

        Carbon::setTestNow();
    });

    test('returns negative days when plan already expired', function () {
        Carbon::setTestNow('2026-04-24 10:00:00');
        $plan = new AssignedPlan(['expires_at' => '2026-04-20']);

        expect($plan->daysUntilExpiry())->toBe(-4);

        Carbon::setTestNow();
    });
});

describe('Payment::isRenewal', function () {
    // P2.7: strict regex — RENEWAL-{id}-{32hexchars}-{epoch}
    test('detects valid RENEWAL- reference as renewal', function () {
        $ref = 'RENEWAL-123-' . str_repeat('A', 32) . '-1745500000';
        $payment = new \App\Models\Payment(['wompi_reference' => $ref]);

        expect($payment->isRenewal())->toBeTrue();
    });

    test('rejects short hex segment (old 8-char format)', function () {
        $payment = new \App\Models\Payment(['wompi_reference' => 'RENEWAL-123-ABCD1234-1745500000']);

        expect($payment->isRenewal())->toBeFalse();
    });

    test('rejects lowercase hex chars', function () {
        $ref = 'RENEWAL-123-' . str_repeat('a', 32) . '-1745500000';
        $payment = new \App\Models\Payment(['wompi_reference' => $ref]);

        expect($payment->isRenewal())->toBeFalse();
    });

    test('detects WC- prefix as NOT renewal', function () {
        $payment = new \App\Models\Payment(['wompi_reference' => 'WC-A1B2C3D4-1234567890']);

        expect($payment->isRenewal())->toBeFalse();
    });

    test('returns false when reference is null', function () {
        $payment = new \App\Models\Payment(['wompi_reference' => null]);

        expect($payment->isRenewal())->toBeFalse();
    });

    test('returns false when reference is empty', function () {
        $payment = new \App\Models\Payment(['wompi_reference' => '']);

        expect($payment->isRenewal())->toBeFalse();
    });
});

describe('config/plans.php promo values', function () {
    test('promo prices honor discount_pct formula', function () {
        $pct = config('plans.promo.discount_pct');
        foreach (['esencial', 'metodo', 'elite'] as $plan) {
            $orig = config("plans.{$plan}.price_cop_original");
            $current = config("plans.{$plan}.price_cop");
            $expected = (int) round($orig * (100 - $pct) / 100);
            expect($current)->toBe($expected, "Plan {$plan}: expected {$expected}, got {$current}");
        }
    })->skip(fn () => ! app(PricingService::class)->isPromoActive(), 'Promo not active');

    test('promo block config is well-formed', function () {
        expect(config('plans.promo.active'))->toBeTrue();
        expect(config('plans.promo.discount_pct'))->toBeInt()->toBeGreaterThan(0);
        expect(config('plans.promo.ends_at'))->toBeString();
    });
});

describe('PricingService', function () {
    test('returns promo price during active promo', function () {
        Carbon::setTestNow('2026-04-27 12:00:00'); // dentro de la promo
        $pricing = app(PricingService::class);
        expect($pricing->isPromoActive())->toBeTrue();
        expect($pricing->priceCop('esencial'))->toBe(config('plans.esencial.price_cop'));
        Carbon::setTestNow();
    });

    test('returns original price after promo ends', function () {
        Carbon::setTestNow('2026-05-01 00:01:00'); // un minuto después del 30 Apr
        $pricing = app(PricingService::class);
        expect($pricing->isPromoActive())->toBeFalse();
        expect($pricing->priceCop('esencial'))->toBe(config('plans.esencial.price_cop_original'));
        Carbon::setTestNow();
    });

    test('discountPercent returns correct percentage during promo', function () {
        Carbon::setTestNow('2026-04-27');
        $pricing = app(PricingService::class);
        expect($pricing->discountPercent('esencial'))->toBe(15);
        Carbon::setTestNow();
    });

    test('discountPercent returns 0 after promo ends', function () {
        Carbon::setTestNow('2026-05-01 00:01:00');
        $pricing = app(PricingService::class);
        expect($pricing->discountPercent('esencial'))->toBe(0);
        Carbon::setTestNow();
    });

    test('priceFor returns same as priceCop', function () {
        $pricing = app(PricingService::class);
        expect($pricing->priceFor('metodo'))->toBe($pricing->priceCop('metodo'));
    });

    test('originalPriceFor always returns non-discounted price', function () {
        $pricing = app(PricingService::class);
        expect($pricing->originalPriceFor('metodo'))->toBe(config('plans.metodo.price_cop_original'));
    });
});
