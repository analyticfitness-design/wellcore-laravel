<?php

declare(strict_types=1);

it('loads marketing.attribution.line from config', function () {
    expect(config('marketing.attribution.line'))
        ->toBe('Por Daniel · Equipo Estrategia WellCore');
});

it('respects MARKETING_ATTRIBUTION_LINE env override', function () {
    config(['marketing.attribution.line' => 'Override Line']);
    expect(config('marketing.attribution.line'))->toBe('Override Line');
});
