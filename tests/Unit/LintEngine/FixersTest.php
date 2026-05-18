<?php

declare(strict_types=1);

use App\Services\LintEngine\Data\FixContext;
use App\Services\LintEngine\Data\LintRuleMeta;
use App\Services\LintEngine\Data\Violation;
use App\Services\LintEngine\Fixers\FuzzyReplaceFixer;
use App\Services\LintEngine\Fixers\RegexReplaceTableFixer;
use App\Services\LintEngine\Fixers\RemoveSentenceContainingTriggerFixer;
use App\Services\LintEngine\Fixers\RenameKeysFixer;
use App\Services\LintEngine\Fixers\RewriteDomainFixer;
use App\Services\LintEngine\JsonPath\PathMutator;

beforeEach(function () {
    $this->mutator = new PathMutator();
});

function fixCtx(array $plan, string $path, array $autoFixDef, string $ruleCode = 'test_rule', bool $autoFix = true): FixContext
{
    $violation = new Violation($ruleCode, 'error', $path, 'msg', null, null, $autoFix);
    $rule = new LintRuleMeta($ruleCode, null, 'error', 'desc', 'schema', 'hint', $autoFix);
    return new FixContext($plan, $violation, $rule, $autoFixDef);
}

it('RenameKeysFixer renombra keys según mapping', function () {
    $fixer = new RenameKeysFixer($this->mutator);
    $plan = ['comidas' => [['macros' => ['proteina_g' => 30, 'carbohidratos_g' => 60, 'grasas' => 20]]]];
    $ctx = fixCtx($plan, '$.comidas[0].macros', [
        'mapping' => ['proteina_g' => 'proteina', 'carbohidratos_g' => 'carbohidratos'],
    ]);

    $result = $fixer->apply($ctx);
    expect($result)->not()->toBeNull();
    expect($result->fixedPlan['comidas'][0]['macros'])->toHaveKeys(['proteina', 'carbohidratos', 'grasas']);
    expect($result->fixedPlan['comidas'][0]['macros'])->not()->toHaveKey('proteina_g');
});

it('RenameKeysFixer retorna null si no hay cambios', function () {
    $fixer = new RenameKeysFixer($this->mutator);
    $plan = ['x' => ['proteina' => 30]];
    $ctx = fixCtx($plan, '$.x', ['mapping' => ['old_key' => 'new_key']]);
    expect($fixer->apply($ctx))->toBeNull();
});

it('FuzzyReplaceFixer corrige acentos missing', function () {
    $fixer = new FuzzyReplaceFixer($this->mutator);
    $plan = ['semanas' => [['fase' => 'adaptacion']]];
    $ctx = fixCtx($plan, '$.semanas[0].fase', [
        'max_distance' => 2,
        'min_confidence' => 0.8,
        'allowed_values' => ['Adaptación', 'Hipertrofia', 'Fuerza', 'Peak'],
    ]);

    $result = $fixer->apply($ctx);
    expect($result)->not()->toBeNull();
    expect($result->fixedPlan['semanas'][0]['fase'])->toBe('Adaptación');
});

it('FuzzyReplaceFixer preserva el sufijo · RIR X', function () {
    $fixer = new FuzzyReplaceFixer($this->mutator);
    $plan = ['semanas' => [['fase' => 'hipertrofia · RIR 2']]];
    $ctx = fixCtx($plan, '$.semanas[0].fase', [
        'max_distance' => 2,
        'allowed_values' => ['Adaptación', 'Hipertrofia'],
    ]);

    $result = $fixer->apply($ctx);
    expect($result)->not()->toBeNull();
    expect($result->fixedPlan['semanas'][0]['fase'])->toBe('Hipertrofia · RIR 2');
});

it('FuzzyReplaceFixer skip si valor ya válido', function () {
    $fixer = new FuzzyReplaceFixer($this->mutator);
    $plan = ['semanas' => [['fase' => 'Adaptación']]];
    $ctx = fixCtx($plan, '$.semanas[0].fase', [
        'allowed_values' => ['Adaptación', 'Hipertrofia'],
    ]);

    expect($fixer->apply($ctx))->toBeNull();
});

it('RegexReplaceTableFixer reemplaza vosotros→ustedes preservando capitalización', function () {
    $fixer = new RegexReplaceTableFixer($this->mutator);
    $plan = ['notas_coach' => 'Vosotros podéis hacerlo. vosotros lo lograréis.'];
    $ctx = fixCtx($plan, '$.notas_coach', [
        'replacements' => ['vosotros' => 'ustedes'],
    ]);

    $result = $fixer->apply($ctx);
    expect($result)->not()->toBeNull();
    expect($result->fixedPlan['notas_coach'])->toContain('Ustedes');
    expect($result->fixedPlan['notas_coach'])->toContain('ustedes');
    expect($result->fixedPlan['notas_coach'])->not()->toContain('Vosotros');
});

it('RewriteDomainFixer corrige URL incorrecta', function () {
    $fixer = new RewriteDomainFixer($this->mutator);
    $plan = ['gif_url' => 'https://wellcorefitness.com/storage/exercises/press.gif'];
    $ctx = fixCtx($plan, '$.gif_url', [
        'from_pattern' => '^https?://(?:www\\.)?wellcorefitness\\.com/storage/exercises/',
        'to_prefix' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/',
    ]);

    $result = $fixer->apply($ctx);
    expect($result)->not()->toBeNull();
    expect($result->fixedPlan['gif_url'])->toBe(
        'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/press.gif'
    );
});

it('RewriteDomainFixer retorna null si URL no matchea from_pattern', function () {
    $fixer = new RewriteDomainFixer($this->mutator);
    $plan = ['gif_url' => 'https://example.com/other/path.gif'];
    $ctx = fixCtx($plan, '$.gif_url', [
        'from_pattern' => '^https?://wellcorefitness\\.com/',
        'to_prefix' => 'https://raw.githubusercontent.com/',
    ]);

    expect($fixer->apply($ctx))->toBeNull();
});

it('RemoveSentenceContainingTriggerFixer remueve oraciones con IA', function () {
    $fixer = new RemoveSentenceContainingTriggerFixer($this->mutator);
    $plan = ['notas_coach' => 'Este es tu plan. Fue generado por IA con Claude. Sigue las series anotadas.'];
    $ctx = fixCtx($plan, '$.notas_coach', []);

    $result = $fixer->apply($ctx);
    expect($result)->not()->toBeNull();
    expect($result->fixedPlan['notas_coach'])->not()->toContain('IA');
    expect($result->fixedPlan['notas_coach'])->not()->toContain('Claude');
    expect($result->fixedPlan['notas_coach'])->toContain('tu plan');
    expect($result->fixedPlan['notas_coach'])->toContain('series anotadas');
});

it('RemoveSentenceContainingTriggerFixer retorna null si no hay triggers', function () {
    $fixer = new RemoveSentenceContainingTriggerFixer($this->mutator);
    $plan = ['notas_coach' => 'Plan limpio sin menciones. Solo coaching directo.'];
    $ctx = fixCtx($plan, '$.notas_coach', []);

    expect($fixer->apply($ctx))->toBeNull();
});
