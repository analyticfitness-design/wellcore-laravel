<?php

declare(strict_types=1);

namespace App\Services\LintEngine;

use App\Services\LintEngine\Contracts\FixApplier;
use InvalidArgumentException;

/**
 * Registry de auto-fixers del LintEngine.
 *
 * Espejo de ValidatorRegistry. Cada fixer se registra una vez al boot y se
 * recupera por nombre cuando un AutoFix.type en una rule lo necesita.
 */
final class FixerRegistry
{
    /** @var array<string, FixApplier> */
    private array $fixers = [];

    public function register(FixApplier $fixer): void
    {
        $name = $fixer->name();
        if (isset($this->fixers[$name])) {
            throw new InvalidArgumentException("Fixer '$name' ya registrado.");
        }
        $this->fixers[$name] = $fixer;
    }

    public function get(string $name): FixApplier
    {
        if (! isset($this->fixers[$name])) {
            throw new InvalidArgumentException("Fixer '$name' no encontrado. Registrados: " . implode(', ', array_keys($this->fixers)));
        }
        return $this->fixers[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->fixers[$name]);
    }

    /** @return string[] */
    public function names(): array
    {
        return array_keys($this->fixers);
    }
}
