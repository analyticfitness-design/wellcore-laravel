<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Contracts\Validator;
use App\Services\LintEngine\Data\LintContext;
use App\Services\LintEngine\Data\ResolvedPath;
use App\Services\LintEngine\Data\Violation;
use App\Services\LintEngine\JsonPath\JsonPathResolver;

abstract class BaseValidator implements Validator
{
    public function __construct(
        protected readonly JsonPathResolver $resolver,
    ) {
    }

    /**
     * @return ResolvedPath[]
     */
    protected function resolvePath(array $document, string $jsonPath): array
    {
        return $this->resolver->resolve($document, $jsonPath);
    }

    protected function makeViolation(LintContext $ctx, string $path, string $message, mixed $foundValue = null, array $hintPlaceholders = []): Violation
    {
        return new Violation(
            ruleCode: $ctx->rule->code,
            severity: $ctx->rule->severity,
            jsonPath: $path,
            message: $message,
            fixHint: $ctx->renderFixHint($hintPlaceholders),
            foundValue: $foundValue,
            autoFixAvailable: $ctx->rule->autoFixAvailable,
        );
    }

    /**
     * Helper: ¿el valor es "vacío" según los criterios del linter?
     * Considera vacío: null, "", [], whitespace-only string.
     */
    protected function isEmpty(mixed $value): bool
    {
        if ($value === null) return true;
        if (is_string($value) && trim($value) === '') return true;
        if (is_array($value) && $value === []) return true;
        return false;
    }
}
