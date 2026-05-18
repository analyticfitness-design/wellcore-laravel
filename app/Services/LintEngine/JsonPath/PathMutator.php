<?php

declare(strict_types=1);

namespace App\Services\LintEngine\JsonPath;

/**
 * Mutador inmutable de paths concretos.
 *
 * Recibe un path como "$.semanas[2].fase" (sin wildcards) y retorna una copia
 * del documento con el valor reemplazado en esa ruta. NO muta el original.
 *
 * Soporta:
 *   $.key
 *   $.a.b.c
 *   $.semanas[2].fase
 *   $.comidas[0].macros.proteina
 */
final class PathMutator
{
    /**
     * Reemplaza el valor en el path. Si el path no existe, lanza una excepción.
     */
    public function setAtPath(array $document, string $path, mixed $value): array
    {
        $tokens = $this->tokenize($path);
        return $this->setRecursive($document, $tokens, $value);
    }

    /**
     * Lee el valor en path. Retorna null si no existe.
     */
    public function getAtPath(array $document, string $path): mixed
    {
        $tokens = $this->tokenize($path);
        $current = $document;
        foreach ($tokens as $tok) {
            if (! is_array($current)) return null;
            if (! array_key_exists($tok, $current)) return null;
            $current = $current[$tok];
        }
        return $current;
    }

    /**
     * Borra el key en path (si es un objeto). Para arrays, remueve el índice.
     */
    public function deleteAtPath(array $document, string $path): array
    {
        $tokens = $this->tokenize($path);
        if ($tokens === []) return $document;
        return $this->deleteRecursive($document, $tokens);
    }

    /**
     * @return array<int, string|int> Tokens normalizados (strings o ints para índices)
     */
    private function tokenize(string $path): array
    {
        if ($path === '' || $path === '$') return [];
        $work = $path;
        if (str_starts_with($work, '$')) {
            $work = substr($work, 1);
        }
        $tokens = [];
        $i = 0;
        $len = strlen($work);
        while ($i < $len) {
            $ch = $work[$i];
            if ($ch === '.') {
                $i++;
                $name = '';
                while ($i < $len && $work[$i] !== '.' && $work[$i] !== '[') {
                    $name .= $work[$i];
                    $i++;
                }
                if ($name !== '') $tokens[] = $name;
                continue;
            }
            if ($ch === '[') {
                $end = strpos($work, ']', $i);
                if ($end === false) break;
                $inner = trim(substr($work, $i + 1, $end - $i - 1));
                $i = $end + 1;
                if (ctype_digit($inner)) {
                    $tokens[] = (int) $inner;
                } elseif (preg_match("/^['\"](.+)['\"]$/", $inner, $m) === 1) {
                    $tokens[] = $m[1];
                }
                continue;
            }
            $i++;
        }
        return $tokens;
    }

    /**
     * @param array<int, string|int> $tokens
     */
    private function setRecursive(array $doc, array $tokens, mixed $value): array
    {
        if ($tokens === []) {
            return is_array($value) ? $value : $doc;
        }
        $head = array_shift($tokens);

        if ($tokens === []) {
            $doc[$head] = $value;
            return $doc;
        }

        $next = $doc[$head] ?? [];
        if (! is_array($next)) {
            $next = [];
        }
        $doc[$head] = $this->setRecursive($next, $tokens, $value);
        return $doc;
    }

    /**
     * @param array<int, string|int> $tokens
     */
    private function deleteRecursive(array $doc, array $tokens): array
    {
        $head = array_shift($tokens);
        if (! array_key_exists($head, $doc)) {
            return $doc;
        }
        if ($tokens === []) {
            unset($doc[$head]);
            return $doc;
        }
        if (is_array($doc[$head])) {
            $doc[$head] = $this->deleteRecursive($doc[$head], $tokens);
        }
        return $doc;
    }
}
