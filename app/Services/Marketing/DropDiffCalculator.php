<?php

declare(strict_types=1);

namespace App\Services\Marketing;

final class DropDiffCalculator
{
    /**
     * Diff plano por path leaf-level.
     * Retorna array de cambios: [{path: 'brief.title', original: 'X', edited: 'Y'}, ...]
     * Arrays de listas se tratan como hojas (no se hace diff por índice).
     */
    public function diff(array $original, array $edited): array
    {
        $diffs = [];
        $this->walk($original, $edited, '', $diffs);

        return $diffs;
    }

    private function walk(mixed $a, mixed $b, string $path, array &$out): void
    {
        if (is_array($a) && is_array($b) && ! $this->isList($a) && ! $this->isList($b)) {
            $keys = array_unique(array_merge(array_keys($a), array_keys($b)));

            foreach ($keys as $k) {
                $this->walk(
                    $a[$k] ?? null,
                    $b[$k] ?? null,
                    $path === '' ? (string) $k : "{$path}.{$k}",
                    $out,
                );
            }

            return;
        }

        if ($a !== $b) {
            $out[] = ['path' => $path, 'original' => $a, 'edited' => $b];
        }
    }

    private function isList(array $arr): bool
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
