<?php
/**
 * WellCore dangerous-actions-blocker — PreToolUse hook
 * Exit 0 = allow · Exit 2 = block (stderr shown to Claude)
 */
$input = json_decode(file_get_contents('php://stdin'), true) ?? [];
$toolName = $input['tool_name'] ?? '';
$toolInput = $input['tool_input'] ?? [];

// === BASH ===
if ($toolName === 'Bash') {
    $cmd = $toolInput['command'] ?? '';

    $blocked = [
        'rm -rf /'           => 'recursive delete from root',
        'rm -rf ~'           => 'recursive delete home',
        ':(){:|:&};:'        => 'fork bomb',
        'DROP DATABASE'      => 'drops entire database — DB compartida con vanilla PHP',
        'DROP TABLE'         => 'destruye tabla — solo migraciones aditivas',
        'killall php-fpm'    => '2026-05-06: tumbó AWS host 22 min — NEVER',
        'killall *php-fpm*'  => '2026-05-06: tumbó AWS host 22 min — NEVER',
        '--no-preserve-root' => 'rm flag inseguro',
        'git reset --hard'   => 'descarta cambios no commiteados — irreversible',
        'git clean -fd'      => 'elimina archivos sin trackear — irreversible',
        'git clean -f'       => 'elimina archivos sin trackear — irreversible',
        'git branch -D'      => 'elimina rama sin merge check — usar -d primero',
        'git checkout .'     => 'descarta todos los cambios del working tree',
        'git restore .'      => 'descarta todos los cambios del working tree',
    ];

    foreach ($blocked as $pattern => $reason) {
        if (stripos($cmd, $pattern) !== false) {
            fwrite(STDERR, "BLOCKED: '{$pattern}' — {$reason}\n");
            exit(2);
        }
    }

    if (preg_match('/git\s+push.+(-f|--force).+(main|master)/i', $cmd)) {
        fwrite(STDERR, "BLOCKED: Force push a main/master está prohibido\n");
        exit(2);
    }

    // npm build está prohibido SOLO en el container EasyPanel
    if (preg_match('/npm run build/i', $cmd) && preg_match('/easypanel|docker exec|container/i', $cmd)) {
        fwrite(STDERR, "BLOCKED: npm run build en container — tumba AWS host. Build SIEMPRE local en la máquina.\n");
        exit(2);
    }

    // =========================================================
    // SUPPLY CHAIN GUARD — CVE-2026-45321 (Mini Shai-Hulud)
    // Intercepta installs que agregan paquetes nuevos.
    // npm ci / npm install (sin args) usan lockfile → safe.
    // npm install <pkg> / pnpm add <pkg> → superficie de ataque.
    // =========================================================

    // Detectar si el comando instala paquetes NUEVOS (no solo "npm install" sin args)
    $isAddingPackages = preg_match(
        '/\b(npm\s+(install|i|add)|pnpm\s+(add|install|i)|yarn\s+add|bun\s+add)\s+(?!--)(@?[\w\-\.\/]+)/i',
        $cmd
    );

    if ($isAddingPackages) {
        // === HARD BLOCK: paquetes comprometidos confirmados ===
        // Campaña Mini Shai-Hulud — mayo 2026
        // 42 paquetes @tanstack + UiPath + Mistral AI + Guardrails AI + OpenSearch afectados
        $compromisedScopes = ['@tanstack/'];
        $compromisedPackages = [
            'uipath', 'guardrails-ai', 'guardrails',
            'mistralai', '@mistralai/',
            'opensearch-project', 'squawk',
        ];

        foreach ($compromisedScopes as $scope) {
            if (stripos($cmd, $scope) !== false) {
                fwrite(STDERR,
                    "BLOCKED [SUPPLY CHAIN]: El scope '{$scope}' fue comprometido en CVE-2026-45321 (Mini Shai-Hulud, mayo 2026).\n" .
                    "84 artefactos maliciosos robaban AWS/GCP/K8s/npm tokens.\n" .
                    "Verifica el postmortem: https://tanstack.com/blog/npm-supply-chain-compromise-postmortem\n"
                );
                exit(2);
            }
        }

        foreach ($compromisedPackages as $pkg) {
            if (stripos($cmd, $pkg) !== false) {
                fwrite(STDERR,
                    "BLOCKED [SUPPLY CHAIN]: '{$pkg}' está en la lista de paquetes comprometidos (CVE-2026-45321).\n"
                );
                exit(2);
            }
        }

        // === SOFT BLOCK: paquete nuevo no registrado en package.json ===
        // Si Claude intenta instalar algo que NO está en el lockfile actual,
        // lo bloqueamos para que sea una decisión consciente.
        $packageJsonPath = __DIR__ . '/../../package.json';
        if (file_exists($packageJsonPath)) {
            $pkgJson = json_decode(file_get_contents($packageJsonPath), true) ?? [];
            $knownDeps = array_merge(
                array_keys($pkgJson['dependencies'] ?? []),
                array_keys($pkgJson['devDependencies'] ?? [])
            );

            // Extraer nombres de paquete del comando (sin flags ni el comando mismo)
            $stripped = preg_replace(
                '/\b(npm\s+(install|i|add)|pnpm\s+(add|install|i)|yarn\s+add|bun\s+add)\b/i',
                '',
                $cmd
            );
            preg_match_all('/(?:^|\s)(@?[\w\-][\w\-\.\/]*)(?=\s|$)/', $stripped, $found);
            $candidates = array_filter(
                $found[1] ?? [],
                fn($p) => strlen($p) > 1 && $p[0] !== '-' && !preg_match('/^\d+/', $p)
            );

            $unknown = array_values(array_diff(array_unique($candidates), $knownDeps));

            if (!empty($unknown)) {
                fwrite(STDERR,
                    "BLOCKED [SUPPLY CHAIN GUARD]: Intentando instalar paquete(s) NUEVOS no en package.json: " .
                    implode(', ', $unknown) . "\n\n" .
                    "Para agregar un paquete nuevo de forma segura:\n" .
                    "  1. Agrégalo manualmente a package.json (dependencies o devDependencies)\n" .
                    "  2. Ejecuta: npm install  (sin args — resuelve el lockfile)\n" .
                    "  3. Revisa el diff en package-lock.json antes de commitearlo\n\n" .
                    "Esto previene que un agent dispare installs silenciosos (ref: CVE-2026-45321).\n"
                );
                exit(2);
            }
        }
    }
}

// === EDIT / WRITE ===
if (in_array($toolName, ['Edit', 'Write', 'MultiEdit'])) {
    $path = $toolInput['file_path'] ?? '';

    // Bloquear vanilla PHP app
    if (
        stripos($path, 'wellcorefitness') !== false &&
        stripos($path, 'wellcore-laravel') === false
    ) {
        fwrite(STDERR, "BLOCKED: No modificar la app vanilla en wellcorefitness — solo wellcore-laravel\n");
        exit(2);
    }

    // Bloquear edición directa de .env de producción
    $filename = basename($path);
    if (in_array($filename, ['.env', '.env.production'])) {
        fwrite(STDERR, "BLOCKED: No editar {$filename} directamente — usar EasyPanel env vars\n");
        exit(2);
    }
}

exit(0);
