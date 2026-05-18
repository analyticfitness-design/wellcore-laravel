<?php

declare(strict_types=1);

use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\LintEngine\Data\AppliedFix;
use App\Services\LintEngine\Data\LintResult;
use App\Services\LintEngine\Data\Violation;
use App\Services\PersistEngine\Data\PersistInput;
use App\Services\PersistEngine\PersistService;

/**
 * Tests del PersistService — unit, no toca DB real (mockea ComposedPlan::create).
 *
 * Cubre derivación de status (composed/validated/exported/rejected) según
 * presencia de lint pre/post y export_path.
 */

beforeEach(function () {
    $this->service = new PersistService();
});

function makeInput(
    ?LintResult $before = null,
    ?LintResult $after = null,
    ?string $exportPath = null,
    array $fixesApplied = [],
): PersistInput {
    return new PersistInput(
        profile: ClientProfile::fromArray(['vertical' => 'entrenamiento', 'goal' => 'hipertrofia', 'level' => 'intermedio', 'days' => 5]),
        methodologySlug: 'body_part_split_5d',
        composeResult: new ComposeResult(
            planJson: ['plan_type' => 'entrenamiento', 'titulo' => 'Test', 'semanas' => []],
            warnings: [],
            durationMs: 12.5,
        ),
        lintBefore: $before,
        lintAfter: $after,
        fixesApplied: $fixesApplied,
        clientHandle: 'test',
        exportPath: $exportPath,
    );
}

function makeLint(int $errors = 0, int $warnings = 0): LintResult
{
    $violations = [];
    for ($i = 0; $i < $errors; $i++) {
        $violations[] = new Violation('rule_err', 'error', '$.foo', 'msg', null, null, false);
    }
    for ($i = 0; $i < $warnings; $i++) {
        $violations[] = new Violation('rule_warn', 'warning', '$.bar', 'msg', null, null, false);
    }
    return new LintResult($violations, 5, 0, 1.0);
}

it('deriveStatus retorna "exported" cuando hay export_path y lint pasa', function () {
    $input = makeInput(after: makeLint(errors: 0), exportPath: 'plan.json');
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('deriveStatus');
    $method->setAccessible(true);
    expect($method->invoke($this->service, $input))->toBe('exported');
});

it('deriveStatus retorna "rejected" cuando hay export_path pero lint sigue con errors', function () {
    $input = makeInput(after: makeLint(errors: 2), exportPath: 'plan.json');
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('deriveStatus');
    $method->setAccessible(true);
    expect($method->invoke($this->service, $input))->toBe('rejected');
});

it('deriveStatus retorna "validated" cuando lint post pasa sin export', function () {
    $input = makeInput(after: makeLint(errors: 0));
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('deriveStatus');
    $method->setAccessible(true);
    expect($method->invoke($this->service, $input))->toBe('validated');
});

it('deriveStatus retorna "rejected" si lint post tiene errors', function () {
    $input = makeInput(after: makeLint(errors: 3));
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('deriveStatus');
    $method->setAccessible(true);
    expect($method->invoke($this->service, $input))->toBe('rejected');
});

it('deriveStatus retorna "validated" si solo hay lint pre y pasa', function () {
    $input = makeInput(before: makeLint(errors: 0));
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('deriveStatus');
    $method->setAccessible(true);
    expect($method->invoke($this->service, $input))->toBe('validated');
});

it('deriveStatus retorna "composed" si solo lint pre con errors (sin después)', function () {
    $input = makeInput(before: makeLint(errors: 1));
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('deriveStatus');
    $method->setAccessible(true);
    expect($method->invoke($this->service, $input))->toBe('composed');
});

it('deriveStatus retorna "composed" cuando no hay lint ni export', function () {
    $input = makeInput();
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('deriveStatus');
    $method->setAccessible(true);
    expect($method->invoke($this->service, $input))->toBe('composed');
});

it('lintToArray devuelve null si lint es null', function () {
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('lintToArray');
    $method->setAccessible(true);
    expect($method->invoke($this->service, null))->toBeNull();
});

it('lintToArray serializa summary + violations', function () {
    $lint = makeLint(errors: 1, warnings: 2);
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('lintToArray');
    $method->setAccessible(true);
    $result = $method->invoke($this->service, $lint);

    expect($result)->toBeArray();
    expect($result['summary']['errors'])->toBe(1);
    expect($result['summary']['warnings'])->toBe(2);
    expect($result['violations'])->toHaveCount(3);
});

it('fixesToArray devuelve null si lista vacía', function () {
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('fixesToArray');
    $method->setAccessible(true);
    expect($method->invoke($this->service, []))->toBeNull();
});

it('fixesToArray serializa cada AppliedFix', function () {
    $fixes = [
        new AppliedFix('rule_a', 'rename_keys', '$.x', 'old', 'new', 'renamed x', ['x' => 'new']),
        new AppliedFix('rule_b', 'fuzzy_replace', '$.y', 'oldy', 'newy', 'replaced y', ['y' => 'newy']),
    ];
    $reflect = new ReflectionClass($this->service);
    $method = $reflect->getMethod('fixesToArray');
    $method->setAccessible(true);
    $result = $method->invoke($this->service, $fixes);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(2);
    expect($result[0]['rule_code'])->toBe('rule_a');
    expect($result[1]['fixer_name'])->toBe('fuzzy_replace');
});
