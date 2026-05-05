<?php

use App\Models\FoodPhoto;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe('FoodPhoto model', function () {
    test('casts photo_date to Carbon date', function () {
        $photo = FoodPhoto::factory()->create(['photo_date' => '2026-05-04']);
        expect($photo->photo_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($photo->photo_date->toDateString())->toBe('2026-05-04');
    });

    test('casts coach_seen to boolean', function () {
        $photo = FoodPhoto::factory()->create(['coach_seen' => 1]);
        expect($photo->coach_seen)->toBeBool()->toBeTrue();
    });

    test('casts xp_awarded to boolean', function () {
        $photo = FoodPhoto::factory()->create(['xp_awarded' => 0]);
        expect($photo->xp_awarded)->toBeBool()->toBeFalse();
    });

    test('casts ai_analysis to array', function () {
        $photo = FoodPhoto::factory()->create(['ai_analysis' => ['k' => 'v']]);
        expect($photo->ai_analysis)->toBe(['k' => 'v']);
    });

    test('photo_url accessor resolves storage path', function () {
        $photo = FoodPhoto::factory()->create([
            'filename' => 'food-photos/42/abc.jpg',
        ]);
        expect($photo->photo_url)->toBe('/storage/food-photos/42/abc.jpg');
    });

    test('UNIQUE constraint blocks duplicate meal per day', function () {
        FoodPhoto::factory()->create([
            'client_id' => 1, 'meal_index' => 0, 'photo_date' => '2026-05-04',
        ]);
        expect(fn () => FoodPhoto::factory()->create([
            'client_id' => 1, 'meal_index' => 0, 'photo_date' => '2026-05-04',
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });
});
