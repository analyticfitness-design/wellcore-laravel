<?php

/**
 * Architecture Tests (Pest Arch Plugin)
 *
 * Enforce structural constraints across the WellCore codebase.
 * All tests run against the app/ source directory declared in phpunit.xml.
 */

describe('Architecture', function () {

    arch('models extend Eloquent Model')
        ->expect('App\Models')
        ->toExtend('Illuminate\Database\Eloquent\Model');

    arch('controllers extend base Controller')
        ->expect('App\Http\Controllers')
        ->toExtend('App\Http\Controllers\Controller')
        ->ignoring('App\Http\Controllers\Controller'); // base class itself is abstract

    arch('events implement ShouldBroadcast')
        ->expect('App\Events')
        ->toImplement('Illuminate\Contracts\Broadcasting\ShouldBroadcast');

    arch('services are not dependent on Livewire')
        ->expect('App\Services')
        ->not->toUse('Livewire\Component');

    arch('enums are backed enums')
        ->expect('App\Enums')
        ->toBeEnums();

    arch('livewire components extend Component')
        ->expect('App\Livewire')
        ->toExtend('Livewire\Component');

    arch('no debugging statements in production code')
        ->expect('App')
        ->not->toUse(['dd', 'dump', 'ray', 'var_dump', 'print_r']);

    arch('middleware classes are not invokable controllers')
        ->expect('App\Http\Middleware')
        ->not->toExtend('App\Http\Controllers\Controller');

    arch('services do not use HTTP request objects directly')
        ->expect('App\Services')
        ->not->toUse('Illuminate\Http\Request');

    arch('enums do not use Eloquent')
        ->expect('App\Enums')
        ->not->toUse('Illuminate\Database\Eloquent\Model');

});
