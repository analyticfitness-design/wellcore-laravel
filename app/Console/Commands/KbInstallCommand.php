<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * kb:install — crea la DB wellcore_kb, corre las 8 migrations, y opcionalmente el seed.
 *
 * Uso:
 *   php artisan kb:install               # crea DB + migra (sin seed)
 *   php artisan kb:install --seed        # crea DB + migra + seed inicial
 *   php artisan kb:install --fresh       # drop + create + migra
 *
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §2
 */
final class KbInstallCommand extends Command
{
    protected $signature = 'kb:install
                            {--seed : Corre también el seed inicial después de migrar}
                            {--fresh : Drop la DB si existe y crea limpia}';

    protected $description = 'Crea la DB wellcore_kb local, corre migrations y opcionalmente el seed inicial del motor v2';

    public function handle(): int
    {
        $dbName = (string) config('database.connections.kb.database');
        $this->info("═══ kb:install — DB destino: $dbName ═══");

        if (! $this->ensureDatabaseExists($dbName)) {
            return self::FAILURE;
        }

        $this->info('Corriendo migrations: php artisan migrate --database=kb --path=database/migrations-kb');
        $exitCode = Artisan::call('migrate', [
            '--database' => 'kb',
            '--path' => 'database/migrations-kb',
            '--force' => true,
        ], $this->output);

        if ($exitCode !== 0) {
            $this->error('Migrations fallaron.');
            return self::FAILURE;
        }

        if ($this->option('seed')) {
            $this->info('');
            $this->info('Corriendo seed inicial...');
            Artisan::call('kb:seed', [], $this->output);
        }

        $this->info('');
        $this->info("✓ wellcore_kb está lista. Próximo paso: corre `php artisan kb:status` para verificar.");
        return self::SUCCESS;
    }

    /**
     * Crea la DB si no existe. Usa PDO directo a MySQL sin DB seleccionada para poder crearla.
     */
    private function ensureDatabaseExists(string $dbName): bool
    {
        // Validamos el nombre con un regex estricto para evitar inyección en DDL
        if (preg_match('/^[A-Za-z0-9_]+$/', $dbName) !== 1) {
            $this->error("Nombre de DB inválido: $dbName (solo alfanumérico y guiones bajos).");
            return false;
        }

        try {
            $host = (string) config('database.connections.kb.host');
            $port = (string) config('database.connections.kb.port');
            $user = (string) config('database.connections.kb.username');
            $pass = (string) config('database.connections.kb.password');

            $pdo = new \PDO("mysql:host=$host;port=$port", $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            if ($this->option('fresh')) {
                $this->warn("Drop DB `$dbName` (--fresh)");
                $pdo->query("DROP DATABASE IF EXISTS `$dbName`");
            }

            $pdo->query(
                "CREATE DATABASE IF NOT EXISTS `$dbName` " .
                "DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );

            $this->info("✓ DB `$dbName` existe (o creada).");
            return true;
        } catch (\PDOException $e) {
            $this->error("No pude conectar/crear la DB local: " . $e->getMessage());
            $this->warn("Verifica que MySQL local (Herd) esté corriendo y que las credenciales KB_DB_* en .env sean correctas.");
            return false;
        }
    }
}
