<?php

declare(strict_types=1);

namespace App\Services\LintEngine;

use App\Services\LintEngine\Contracts\Validator;
use InvalidArgumentException;

/**
 * Registry de validators del LintEngine.
 *
 * Cada validator se registra una vez (al boot del service provider) y se
 * recupera por nombre cuando una rule lo necesita.
 *
 * Permite extensión: agregar un nuevo validator es solo register() + crear la clase.
 */
final class ValidatorRegistry
{
    /** @var array<string, Validator> */
    private array $validators = [];

    public function register(Validator $validator): void
    {
        $name = $validator->name();
        if (isset($this->validators[$name])) {
            throw new InvalidArgumentException("Validator '$name' ya registrado.");
        }
        $this->validators[$name] = $validator;
    }

    public function get(string $name): Validator
    {
        if (! isset($this->validators[$name])) {
            throw new InvalidArgumentException("Validator '$name' no encontrado. Registrados: " . implode(', ', array_keys($this->validators)));
        }
        return $this->validators[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->validators[$name]);
    }

    /** @return string[] */
    public function names(): array
    {
        return array_keys($this->validators);
    }
}
