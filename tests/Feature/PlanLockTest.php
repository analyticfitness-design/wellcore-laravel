<?php

use App\Models\AssignedPlan;
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
    test('detects RENEWAL- prefix as renewal', function () {
        $payment = new \App\Models\Payment(['wompi_reference' => 'RENEWAL-123-ABCD1234-1745500000']);

        expect($payment->isRenewal())->toBeTrue();
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
    test('esencial promo price matches the 15% discount calculation', function () {
        $original = config('plans.esencial.price_cop_original');
        $current = config('plans.esencial.price_cop');

        expect($original)->toBe(299000);
        expect($current)->toBe(254150);
        // Verifica que el descuento es ~15%
        expect(round(($original - $current) / $original * 100))->toBe(15.0);
    });

    test('metodo promo price matches the 15% discount calculation', function () {
        expect(config('plans.metodo.price_cop'))->toBe(339150);
        expect(config('plans.metodo.price_cop_original'))->toBe(399000);
    });

    test('elite promo price matches the 15% discount calculation', function () {
        expect(config('plans.elite.price_cop'))->toBe(466650);
        expect(config('plans.elite.price_cop_original'))->toBe(549000);
    });

    test('promo block is active with april end date', function () {
        expect(config('plans.promo.active'))->toBeTrue();
        expect(config('plans.promo.discount_pct'))->toBe(15);
        expect(config('plans.promo.ends_at'))->toBe('2026-04-30');
    });
});
