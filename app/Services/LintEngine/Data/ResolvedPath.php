<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Data;

/**
 * Resultado de resolver un JSONPath contra un documento.
 *
 * Cada match incluye:
 *  - $path: la ruta concreta resuelta (ej. "$.semanas[2].fase")
 *  - $value: el valor encontrado en esa ruta
 *  - $exists: true si la ruta existe, false si no
 *  - $parent: referencia al objeto padre (útil para checks de keys)
 *  - $key: el último segmento de la ruta (key string o índice int)
 */
final readonly class ResolvedPath
{
    public function __construct(
        public string $path,
        public mixed $value,
        public bool $exists,
        public mixed $parent = null,
        public string|int|null $key = null,
    ) {
    }

    public static function missing(string $path): self
    {
        return new self($path, null, false);
    }

    public static function found(string $path, mixed $value, mixed $parent = null, string|int|null $key = null): self
    {
        return new self($path, $value, true, $parent, $key);
    }
}
