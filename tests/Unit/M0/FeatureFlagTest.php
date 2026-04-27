<?php

declare(strict_types=1);

it('coach_strategy_enabled defaults to false', function () {
    expect(config('features.coach_strategy_enabled'))->toBeFalse();
});
