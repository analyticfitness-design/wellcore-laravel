<?php

declare(strict_types=1);

namespace App\Services\LintEngine\JsonPath;

use App\Services\LintEngine\Data\ResolvedPath;

/**
 * Resolver de JSONPath simplificado para el LintEngine.
 *
 * Soporta los patterns usados por las rules MVP:
 *  - $.key                       → key del root
 *  - $.a.b.c                     → path anidado
 *  - $.semanas[*]                → iterar elementos del array
 *  - $.semanas[*].fase           → wildcard + key
 *  - $.semanas[*].dias[*].nombre → wildcards chained
 *  - $..ejercicios               → descendant (recursive) — busca key en cualquier nivel
 *  - $..ejercicios[*].notas      → descendant + wildcard
 *
 * NO soporta: filtros [?(@.foo > 1)], slices [1:3], unión [a,b]. No se necesitan.
 */
final class JsonPathResolver
{
    /**
     * Resuelve un JSONPath contra un documento y retorna todos los matches.
     *
     * @return ResolvedPath[]
     */
    public function resolve(array $document, string $jsonPath): array
    {
        $tokens = $this->tokenize($jsonPath);
        if ($tokens === []) {
            return [];
        }

        // Inicia con el documento root como único contexto
        $contexts = [
            ['path' => '$', 'value' => $document, 'parent' => null, 'key' => null],
        ];

        foreach ($tokens as $token) {
            $contexts = $this->stepAll($contexts, $token);
            if ($contexts === []) {
                break;
            }
        }

        return array_map(
            fn (array $c) => ResolvedPath::found($c['path'], $c['value'], $c['parent'] ?? null, $c['key'] ?? null),
            $contexts
        );
    }

    /**
     * Tokeniza un JSONPath en pasos discretos.
     *
     * @return array<int, array{type: string, name?: string}>
     */
    private function tokenize(string $path): array
    {
        if ($path === '' || $path === '$') {
            return [];
        }

        // Normaliza inicio
        if (! str_starts_with($path, '$')) {
            $path = '$' . (str_starts_with($path, '.') || str_starts_with($path, '[') ? '' : '.') . $path;
        }

        $tokens = [];
        $i = 1; // skip "$"
        $len = strlen($path);

        while ($i < $len) {
            $ch = $path[$i];

            // descendant: ..
            if ($ch === '.' && $i + 1 < $len && $path[$i + 1] === '.') {
                $i += 2;
                $name = '';
                while ($i < $len && $path[$i] !== '.' && $path[$i] !== '[') {
                    $name .= $path[$i];
                    $i++;
                }
                if ($name === '*') {
                    $tokens[] = ['type' => 'descendant_wildcard'];
                } else {
                    $tokens[] = ['type' => 'descendant', 'name' => $name];
                }
                continue;
            }

            // child: .key
            if ($ch === '.') {
                $i++;
                $name = '';
                while ($i < $len && $path[$i] !== '.' && $path[$i] !== '[') {
                    $name .= $path[$i];
                    $i++;
                }
                if ($name === '*') {
                    $tokens[] = ['type' => 'wildcard'];
                } else {
                    $tokens[] = ['type' => 'child', 'name' => $name];
                }
                continue;
            }

            // bracket: [*] or [N] or ['key']
            if ($ch === '[') {
                $end = strpos($path, ']', $i);
                if ($end === false) {
                    break; // malformed
                }
                $inner = trim(substr($path, $i + 1, $end - $i - 1));
                $i = $end + 1;
                if ($inner === '*') {
                    $tokens[] = ['type' => 'wildcard'];
                } elseif (preg_match("/^['\"](.+)['\"]$/", $inner, $m) === 1) {
                    $tokens[] = ['type' => 'child', 'name' => $m[1]];
                } elseif (ctype_digit($inner)) {
                    $tokens[] = ['type' => 'index', 'name' => $inner];
                }
                continue;
            }

            // unknown char — skip
            $i++;
        }

        return $tokens;
    }

    /**
     * @param array<int, array{path: string, value: mixed, parent: mixed, key: mixed}> $contexts
     * @param array{type: string, name?: string} $token
     * @return array<int, array{path: string, value: mixed, parent: mixed, key: mixed}>
     */
    private function stepAll(array $contexts, array $token): array
    {
        $next = [];
        foreach ($contexts as $ctx) {
            $next = array_merge($next, $this->step($ctx, $token));
        }
        return $next;
    }

    private function step(array $ctx, array $token): array
    {
        $value = $ctx['value'];
        $type = $token['type'];

        return match ($type) {
            'child' => $this->stepChild($ctx, (string) $token['name']),
            'index' => $this->stepIndex($ctx, (int) $token['name']),
            'wildcard' => $this->stepWildcard($ctx),
            'descendant' => $this->stepDescendant($ctx, (string) $token['name']),
            'descendant_wildcard' => $this->stepDescendantWildcard($ctx),
            default => [],
        };
    }

    private function stepChild(array $ctx, string $key): array
    {
        if (! is_array($ctx['value']) || ! array_key_exists($key, $ctx['value'])) {
            return [];
        }
        return [[
            'path' => $ctx['path'] . '.' . $key,
            'value' => $ctx['value'][$key],
            'parent' => $ctx['value'],
            'key' => $key,
        ]];
    }

    private function stepIndex(array $ctx, int $index): array
    {
        if (! is_array($ctx['value']) || ! array_key_exists($index, $ctx['value'])) {
            return [];
        }
        return [[
            'path' => $ctx['path'] . '[' . $index . ']',
            'value' => $ctx['value'][$index],
            'parent' => $ctx['value'],
            'key' => $index,
        ]];
    }

    private function stepWildcard(array $ctx): array
    {
        if (! is_array($ctx['value'])) {
            return [];
        }
        $out = [];
        foreach ($ctx['value'] as $k => $v) {
            $segment = is_int($k) ? "[$k]" : ".$k";
            $out[] = [
                'path' => $ctx['path'] . $segment,
                'value' => $v,
                'parent' => $ctx['value'],
                'key' => $k,
            ];
        }
        return $out;
    }

    private function stepDescendant(array $ctx, string $key): array
    {
        $matches = [];
        $this->walkDescendant($ctx['value'], $ctx['path'], $key, $matches);
        return $matches;
    }

    private function walkDescendant(mixed $value, string $path, string $key, array &$matches): void
    {
        if (! is_array($value)) {
            return;
        }
        foreach ($value as $k => $v) {
            $segment = is_int($k) ? "[$k]" : ".$k";
            $currentPath = $path . $segment;
            if ($k === $key || (is_string($k) && $k === $key)) {
                $matches[] = [
                    'path' => $currentPath,
                    'value' => $v,
                    'parent' => $value,
                    'key' => $k,
                ];
            }
            $this->walkDescendant($v, $currentPath, $key, $matches);
        }
    }

    private function stepDescendantWildcard(array $ctx): array
    {
        $matches = [];
        $this->walkAll($ctx['value'], $ctx['path'], $matches);
        return $matches;
    }

    private function walkAll(mixed $value, string $path, array &$matches): void
    {
        if (! is_array($value)) {
            return;
        }
        foreach ($value as $k => $v) {
            $segment = is_int($k) ? "[$k]" : ".$k";
            $currentPath = $path . $segment;
            $matches[] = [
                'path' => $currentPath,
                'value' => $v,
                'parent' => $value,
                'key' => $k,
            ];
            $this->walkAll($v, $currentPath, $matches);
        }
    }
}
