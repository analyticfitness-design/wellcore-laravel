<?php

declare(strict_types=1);

use App\PlanEngine\Normalization\VocabularyNormalizer;

describe('VocabularyNormalizer::goal', function () {
    it('mapea perder_grasa al canonical perdida_grasa', function () {
        expect(VocabularyNormalizer::goal('perder_grasa'))->toBe('perdida_grasa');
    });

    it('mapea variantes de perdida_grasa al canonical', function () {
        expect(VocabularyNormalizer::goal('Definicion'))->toBe('perdida_grasa');
        expect(VocabularyNormalizer::goal('cutting'))->toBe('perdida_grasa');
        expect(VocabularyNormalizer::goal('disminuir porcentaje de grasa'))->toBe('perdida_grasa');
    });

    it('mapea ganar_masa a hipertrofia', function () {
        expect(VocabularyNormalizer::goal('ganar_masa'))->toBe('hipertrofia');
        expect(VocabularyNormalizer::goal('volumen'))->toBe('hipertrofia');
        expect(VocabularyNormalizer::goal('Bulking'))->toBe('hipertrofia');
    });

    it('respeta el canonical si ya viene canonical', function () {
        expect(VocabularyNormalizer::goal('perdida_grasa'))->toBe('perdida_grasa');
        expect(VocabularyNormalizer::goal('hipertrofia'))->toBe('hipertrofia');
    });

    it('retorna null para null', function () {
        expect(VocabularyNormalizer::goal(null))->toBeNull();
    });

    it('tolera acentos y mayúsculas', function () {
        expect(VocabularyNormalizer::goal('DEFINICIÓN'))->toBe('perdida_grasa');
    });
});

describe('VocabularyNormalizer::gender', function () {
    it('mapea femenino a F', function () {
        expect(VocabularyNormalizer::gender('femenino'))->toBe('F');
        expect(VocabularyNormalizer::gender('mujer'))->toBe('F');
        expect(VocabularyNormalizer::gender('Female'))->toBe('F');
        expect(VocabularyNormalizer::gender('f'))->toBe('F');
    });

    it('mapea masculino a M', function () {
        expect(VocabularyNormalizer::gender('Masculino'))->toBe('M');
        expect(VocabularyNormalizer::gender('hombre'))->toBe('M');
        expect(VocabularyNormalizer::gender('m'))->toBe('M');
    });
});

describe('VocabularyNormalizer::level', function () {
    it('mapea variantes en español/inglés', function () {
        expect(VocabularyNormalizer::level('Avanzado'))->toBe('avanzado');
        expect(VocabularyNormalizer::level('advanced'))->toBe('avanzado');
        expect(VocabularyNormalizer::level('intermediate'))->toBe('intermedio');
        expect(VocabularyNormalizer::level('Beginner'))->toBe('principiante');
    });
});

describe('VocabularyNormalizer::place', function () {
    it('mapea gimnasio a gym', function () {
        expect(VocabularyNormalizer::place('Gimnasio'))->toBe('gym');
        expect(VocabularyNormalizer::place('comercial'))->toBe('gym');
    });

    it('mapea casa/home/domicilio', function () {
        expect(VocabularyNormalizer::place('home'))->toBe('casa');
        expect(VocabularyNormalizer::place('domicilio'))->toBe('casa');
    });
});

describe('VocabularyNormalizer fallback', function () {
    it('devuelve la clave canonicalizada cuando no hay mapping', function () {
        // String no mapeado: vuelve normalizado para que el enum downstream falle predecible
        expect(VocabularyNormalizer::goal('xyz unknown'))->toBe('xyz_unknown');
    });

    it('retorna null para strings vacíos', function () {
        expect(VocabularyNormalizer::goal(''))->toBeNull();
        expect(VocabularyNormalizer::goal('   '))->toBeNull();
    });
});
