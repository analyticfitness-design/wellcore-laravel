<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Http\Controllers\Api\Concerns\AuthenticatesVueRequests;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\AuthToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Break-glass admin utilities — catalog + SSE run + audit history.
 * Only superadmin can execute destructive tools. All other admins can run read-only tools.
 */
class AdminToolsController extends Controller
{
    use AuthenticatesVueRequests;

    protected static array $CATALOG = [
        // Cache
        [
            'id'              => 'redis-clear',
            'title'           => 'Limpiar Cache Redis',
            'description'     => 'Borra todo el cache Redis del admin. Ejecutar solo si hay datos stale visibles en la UI.',
            'category'        => 'Cache',
            'icon'            => 'database',
            'destructive'     => true,
            'requires_params' => false,
            'params_schema'   => [],
        ],
        [
            'id'              => 'opcache-reset',
            'title'           => 'Reset OPcache PHP',
            'description'     => 'Invalida el cache de bytecode PHP compilado. Util despues de un deploy cuando el codigo nuevo no se refleja.',
            'category'        => 'Cache',
            'icon'            => 'chip',
            'destructive'     => false,
            'requires_params' => false,
            'params_schema'   => [],
        ],
        [
            'id'              => 'artisan-optimize',
            'title'           => 'Optimizar Config',
            'description'     => 'Ejecuta config:cache + route:cache + view:cache de Laravel. Reduce carga de arranque por request.',
            'category'        => 'Cache',
            'icon'            => 'lightning',
            'destructive'     => false,
            'requires_params' => false,
            'params_schema'   => [],
        ],
        // DB
        [
            'id'              => 'db-check',
            'title'           => 'Diagnostico DB',
            'description'     => 'Verifica la conexion a MySQL, cuenta registros clave y mide latencia de query.',
            'category'        => 'DB',
            'icon'            => 'circle-stack',
            'destructive'     => false,
            'requires_params' => false,
            'params_schema'   => [],
        ],
        [
            'id'              => 'db-config',
            'title'           => 'Info Conexion DB',
            'description'     => 'Muestra host, puerto, base de datos y version del servidor MySQL activo.',
            'category'        => 'DB',
            'icon'            => 'information-circle',
            'destructive'     => false,
            'requires_params' => false,
            'params_schema'   => [],
        ],
        // Auth
        [
            'id'              => 'reset-admin-password',
            'title'           => 'Reset Password Admin',
            'description'     => 'Reemplaza la contrasena de un admin en la base de datos. Util cuando Daniel pierde acceso.',
            'category'        => 'Auth',
            'icon'            => 'key',
            'destructive'     => true,
            'requires_params' => true,
            'params_schema'   => [
                ['name' => 'email',        'label' => 'Email del admin',              'type' => 'email',    'required' => true],
                ['name' => 'new_password', 'label' => 'Nueva contrasena (min 8)',     'type' => 'password', 'required' => true],
            ],
        ],
        [
            'id'              => 'generate-auth-token',
            'title'           => 'Generar Auth Token',
            'description'     => 'Crea un token de sesion de 30 dias para un admin. Util para integraciones o acceso manual.',
            'category'        => 'Auth',
            'icon'            => 'shield-check',
            'destructive'     => false,
            'requires_params' => true,
            'params_schema'   => [
                ['name' => 'email', 'label' => 'Email del admin', 'type' => 'email', 'required' => true],
            ],
        ],
        // Import
        [
            'id'              => 'import-clients-csv',
            'title'           => 'Importar Clientes CSV',
            'description'     => 'Valida y procesa un CSV de clientes. Header requerido: nombre, email, plan, coach_email.',
            'category'        => 'Import',
            'icon'            => 'users',
            'destructive'     => true,
            'requires_params' => true,
            'params_schema'   => [
                ['name' => 'csv_content', 'label' => 'Contenido CSV', 'type' => 'textarea', 'required' => true, 'placeholder' => "nombre,email,plan,coach_email\nJuan,juan@mail.com,metodo,coach@mail.com"],
            ],
        ],
        [
            'id'              => 'import-payments-csv',
            'title'           => 'Importar Pagos CSV',
            'description'     => 'Registra pagos desde un CSV. Header requerido: client_email, amount, method, date.',
            'category'        => 'Import',
            'icon'            => 'document-text',
            'destructive'     => true,
            'requires_params' => true,
            'params_schema'   => [
                ['name' => 'csv_content', 'label' => 'Contenido CSV', 'type' => 'textarea', 'required' => true, 'placeholder' => "client_email,amount,method,date\njuan@mail.com,120000,wompi,2026-04-01"],
            ],
        ],
        // Backup
        [
            'id'              => 'export-backup',
            'title'           => 'Export Data Backup',
            'description'     => 'Exporta un resumen de clientes, pagos e inscripciones activos. Solo lectura.',
            'category'        => 'Backup',
            'icon'            => 'archive-box',
            'destructive'     => false,
            'requires_params' => false,
            'params_schema'   => [],
        ],
        // Email
        [
            'id'              => 'test-smtp',
            'title'           => 'Test SMTP Email',
            'description'     => 'Envia un email de prueba al email del admin logueado para verificar el servidor de correo.',
            'category'        => 'Email',
            'icon'            => 'envelope',
            'destructive'     => false,
            'requires_params' => false,
            'params_schema'   => [],
        ],
        [
            'id'              => 'resend-voucher',
            'title'           => 'Reenviar Comprobante',
            'description'     => 'Busca el ultimo pago de un cliente y reenvia el comprobante a su email.',
            'category'        => 'Email',
            'icon'            => 'paper-airplane',
            'destructive'     => false,
            'requires_params' => true,
            'params_schema'   => [
                ['name' => 'client_email', 'label' => 'Email del cliente', 'type' => 'email', 'required' => true],
            ],
        ],
    ];

    protected function resolveAdmin(Request $request): Admin
    {
        $auth = $this->resolveAuthUser($request);
        if (! $auth || $auth['userType'] !== UserType::Admin) {
            abort(401, 'Token invalido o expirado.');
        }
        $admin = $auth['user'];
        $role  = $admin->role?->value ?? $admin->role ?? '';
        if (! in_array($role, ['admin', 'superadmin', 'jefe'])) {
            abort(403, 'No tienes permisos de administrador.');
        }
        return $admin;
    }

    protected function isSuperadmin(Admin $admin): bool
    {
        $role = $admin->role?->value ?? $admin->role ?? '';
        return in_array($role, ['superadmin', 'jefe']);
    }

    /** GET /api/v/admin/tools */
    public function catalog(Request $request): JsonResponse
    {
        $admin        = $this->resolveAdmin($request);
        $isSuperadmin = $this->isSuperadmin($admin);

        return response()->json([
            'tools'        => static::$CATALOG,
            'isSuperadmin' => $isSuperadmin,
        ]);
    }

    /** GET /api/v/admin/tools/history */
    public function history(Request $request): JsonResponse
    {
        $this->resolveAdmin($request);

        $entries = AuditLog::where('action', 'like', 'tool.%')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['actor_name', 'action', 'target_label', 'diff', 'created_at']);

        return response()->json([
            'history' => $entries->map(fn ($e) => [
                'actor_name'     => $e->actor_name,
                'action'         => $e->action,
                'tool_id'        => str_replace('tool.', '', $e->action),
                'target_label'   => $e->target_label,
                'status'         => $e->diff['status'] ?? 'unknown',
                'duration_ms'    => $e->diff['duration_ms'] ?? null,
                'output_preview' => mb_substr($e->diff['output'] ?? '', 0, 200),
                'created_at'     => $e->created_at?->toISOString(),
            ]),
        ]);
    }

    /** POST /api/v/admin/tools/{id}/run — SSE stream */
    public function run(string $id, Request $request): StreamedResponse
    {
        $admin = $this->resolveAdmin($request);
        $tool  = collect(static::$CATALOG)->firstWhere('id', $id);

        if (! $tool) {
            abort(404, 'Herramienta no encontrada.');
        }

        if ($tool['destructive'] && ! $this->isSuperadmin($admin)) {
            abort(403, 'Solo Superadmin puede ejecutar esta herramienta.');
        }

        $params    = $request->all();
        $startedAt = microtime(true);

        $response = new StreamedResponse(function () use ($tool, $params, $admin, $startedAt) {
            while (ob_get_level() > 0) { @ob_end_flush(); }
            @ini_set('zlib.output_compression', '0');

            echo ": stream-open\n\n";
            @ob_flush();
            @flush();

            $outputLines = [];
            $status      = 'success';

            $emit = function (string $text) use (&$outputLines) {
                $outputLines[] = $text;
                echo 'data: ' . json_encode(['type' => 'output', 'text' => $text], JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();
            };

            try {
                $this->executeTool($tool['id'], $params, $emit, $admin);
            } catch (\Throwable $e) {
                $emit("\nERROR: " . $e->getMessage() . "\n");
                $status = 'failed';
                Log::error('AdminTools run exception', ['tool' => $tool['id'], 'err' => $e->getMessage()]);
            }

            $durationMs = (int) ((microtime(true) - $startedAt) * 1000);
            $outputText = implode('', $outputLines);

            try {
                AuditLog::create([
                    'actor_type'   => 'admin',
                    'actor_id'     => $admin->id,
                    'actor_name'   => $admin->name ?? $admin->email ?? 'admin',
                    'action'       => 'tool.' . $tool['id'],
                    'target_type'  => 'tool',
                    'target_label' => $tool['title'],
                    'diff'         => [
                        'params'      => array_filter($params, fn ($k) => ! in_array($k, ['new_password', 'csv_content']), ARRAY_FILTER_USE_KEY),
                        'output'      => mb_substr($outputText, 0, 1000),
                        'duration_ms' => $durationMs,
                        'status'      => $status,
                    ],
                    'ip'           => request()->ip(),
                    'created_at'   => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('AdminTools audit log failed', ['err' => $e->getMessage()]);
            }

            echo 'data: ' . json_encode([
                'type'        => 'done',
                'status'      => $status,
                'duration_ms' => $durationMs,
            ], JSON_UNESCAPED_UNICODE) . "\n\n";
            @ob_flush();
            @flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    protected function executeTool(string $id, array $params, callable $emit, Admin $admin): void
    {
        match ($id) {
            'redis-clear'          => $this->toolRedisClear($emit),
            'opcache-reset'        => $this->toolOpcacheReset($emit),
            'artisan-optimize'     => $this->toolArtisanOptimize($emit),
            'db-check'             => $this->toolDbCheck($emit),
            'db-config'            => $this->toolDbConfig($emit),
            'reset-admin-password' => $this->toolResetAdminPassword($params, $emit),
            'generate-auth-token'  => $this->toolGenerateAuthToken($params, $emit),
            'import-clients-csv'   => $this->toolImportClientsCsv($params, $emit),
            'import-payments-csv'  => $this->toolImportPaymentsCsv($params, $emit),
            'export-backup'        => $this->toolExportBackup($emit),
            'test-smtp'            => $this->toolTestSmtp($emit, $admin),
            'resend-voucher'       => $this->toolResendVoucher($params, $emit),
            default                => $emit("Tool '{$id}' no implementada.\n"),
        };
    }

    // ─── Tool implementations ─────────────────────────────────────────────────

    private function toolRedisClear(callable $emit): void
    {
        $emit("Conectando a Redis...\n");
        try {
            Cache::store('redis')->flush();
            $emit("Cache Redis eliminado correctamente.\n");
        } catch (\Throwable) {
            $emit("Redis no accesible. Limpiando cache default...\n");
            Cache::flush();
            $emit("Cache default vaciado.\n");
        }
        $emit("Listo.\n");
    }

    private function toolOpcacheReset(callable $emit): void
    {
        if (! function_exists('opcache_reset')) {
            $emit("OPcache no disponible en este entorno.\n");
            return;
        }
        $status = @opcache_get_status(false);
        if (! $status) {
            $emit("OPcache no esta activo.\n");
            return;
        }
        $cached = $status['opcache_statistics']['num_cached_scripts'] ?? 0;
        $emit("Archivos en cache: {$cached}\n");
        $emit("Reseteando OPcache...\n");
        $result = opcache_reset();
        $emit($result ? "OPcache reseteado correctamente.\n" : "Reset retorno false (normal en FPM multi-worker — cada worker debe recibir el reset).\n");
    }

    private function toolArtisanOptimize(callable $emit): void
    {
        foreach (['config:cache', 'route:cache', 'view:cache'] as $cmd) {
            $emit("Ejecutando {$cmd}...\n");
            Artisan::call($cmd);
            $out = trim(Artisan::output());
            $emit(($out ?: "{$cmd} completado.") . "\n");
        }
        $emit("\nOptimizacion completada.\n");
    }

    private function toolDbCheck(callable $emit): void
    {
        $emit("Verificando conexion MySQL...\n");
        DB::connection()->getPdo();
        $emit("Conexion exitosa.\n");

        $v = DB::selectOne('SELECT VERSION() as v');
        $emit("Version MySQL: {$v->v}\n");

        $t0 = microtime(true);
        DB::selectOne('SELECT 1');
        $emit("Latencia de query: " . round((microtime(true) - $t0) * 1000, 2) . "ms\n\n");

        $emit("Contando registros clave:\n");
        foreach (['clients' => 'Clientes', 'payments' => 'Pagos', 'admins' => 'Admins', 'auth_tokens' => 'Tokens activos', 'inscriptions' => 'Inscripciones'] as $table => $label) {
            try {
                $count = DB::table($table)->count();
                $emit("  {$label}: {$count}\n");
            } catch (\Throwable) {
                $emit("  {$label}: tabla no accesible\n");
            }
        }
        $emit("\nDiagnostico completado.\n");
    }

    private function toolDbConfig(callable $emit): void
    {
        $c = config('database.connections.mysql');
        $emit("Host:          " . ($c['host'] ?? 'N/A') . "\n");
        $emit("Puerto:        " . ($c['port'] ?? '3306') . "\n");
        $emit("Base de datos: " . ($c['database'] ?? 'N/A') . "\n");
        $emit("Usuario:       " . ($c['username'] ?? 'N/A') . "\n");
        $emit("Charset:       " . ($c['charset'] ?? 'N/A') . "\n");
        $emit("Collation:     " . ($c['collation'] ?? 'N/A') . "\n");
        $emit("Driver:        " . ($c['driver'] ?? 'N/A') . "\n");
    }

    private function toolResetAdminPassword(array $params, callable $emit): void
    {
        $email   = trim($params['email'] ?? '');
        $newPass = $params['new_password'] ?? '';
        if (! $email) throw new \RuntimeException('El campo email es requerido.');
        if (strlen($newPass) < 8) throw new \RuntimeException('La nueva contrasena debe tener al menos 8 caracteres.');

        $emit("Buscando admin: {$email}...\n");
        $admin = Admin::where('email', $email)->first();
        if (! $admin) throw new \RuntimeException("No se encontro admin con email '{$email}'.");

        $role = $admin->role?->value ?? $admin->role;
        $emit("Admin encontrado: " . ($admin->name ?? $admin->username) . " (rol: {$role})\n");
        $emit("Actualizando password_hash...\n");

        $admin->password_hash = Hash::make($newPass);
        $admin->save();

        $emit("Password actualizado correctamente.\n");
        $emit("Nota: tokens de sesion existentes siguen validos hasta su vencimiento natural.\n");
    }

    private function toolGenerateAuthToken(array $params, callable $emit): void
    {
        $email = trim($params['email'] ?? '');
        if (! $email) throw new \RuntimeException('El campo email es requerido.');

        $emit("Buscando admin: {$email}...\n");
        $admin = Admin::where('email', $email)->first();
        if (! $admin) throw new \RuntimeException("No se encontro admin con email '{$email}'.");

        $emit("Admin encontrado: " . ($admin->name ?? $admin->username) . "\n");
        $emit("Generando token (64-char hex, 30 dias)...\n");

        $token = bin2hex(random_bytes(32));
        AuthToken::create([
            'user_type'  => 'admin',
            'user_id'    => $admin->id,
            'token'      => $token,
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
        ]);

        $emit("Token generado:\n{$token}\n\n");
        $emit("Expira: " . now()->addDays(30)->toDateTimeString() . "\n");
        $emit("GUARDA este token — no se puede recuperar despues.\n");
    }

    private function toolImportClientsCsv(array $params, callable $emit): void
    {
        $csv = $params['csv_content'] ?? '';
        if (! $csv) throw new \RuntimeException('Contenido CSV requerido.');

        $lines  = explode("\n", trim($csv));
        $header = str_getcsv(array_shift($lines));
        $emit("Header detectado: " . implode(', ', $header) . "\n");

        $missing = array_diff(['nombre', 'email', 'plan', 'coach_email'], $header);
        if ($missing) throw new \RuntimeException("Header incompleto. Faltan: " . implode(', ', $missing));

        $emit("Filas a procesar: " . count($lines) . "\n\n");
        $ok = $errors = 0;

        foreach ($lines as $i => $line) {
            if (! trim($line)) continue;
            $row = array_combine($header, str_getcsv($line));
            $rowNum = $i + 1;
            if (! filter_var($row['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                $emit("  Fila {$rowNum}: EMAIL INVALIDO ({$row['email']}). Saltada.\n");
                $errors++;
                continue;
            }
            $emit("  Fila {$rowNum}: {$row['email']} — validado OK\n");
            $ok++;
        }

        $emit("\nResumen: {$ok} validas, {$errors} con errores.\n");
        $emit("AVISO: La insercion real requiere mapeo completo al schema de la DB. Contacta al equipo tecnico para ejecutar el import final.\n");
    }

    private function toolImportPaymentsCsv(array $params, callable $emit): void
    {
        $csv = $params['csv_content'] ?? '';
        if (! $csv) throw new \RuntimeException('Contenido CSV requerido.');

        $lines  = explode("\n", trim($csv));
        $header = str_getcsv(array_shift($lines));
        $emit("Header detectado: " . implode(', ', $header) . "\n");

        $missing = array_diff(['client_email', 'amount', 'method', 'date'], $header);
        if ($missing) throw new \RuntimeException("Header incompleto. Faltan: " . implode(', ', $missing));

        $emit("Filas a procesar: " . count($lines) . "\n\n");
        $ok = $errors = 0;

        foreach ($lines as $i => $line) {
            if (! trim($line)) continue;
            $row    = array_combine($header, str_getcsv($line));
            $rowNum = $i + 1;
            if (! filter_var($row['client_email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                $emit("  Fila {$rowNum}: EMAIL INVALIDO. Saltada.\n");
                $errors++;
                continue;
            }
            $amount = number_format((float) ($row['amount'] ?? 0), 0, ',', '.');
            $emit("  Fila {$rowNum}: {$row['client_email']} \${$amount} — validado OK\n");
            $ok++;
        }

        $emit("\nResumen: {$ok} validas, {$errors} con errores.\n");
        $emit("AVISO: La insercion real de pagos requiere integracion con el schema completo. Contacta al equipo tecnico.\n");
    }

    private function toolExportBackup(callable $emit): void
    {
        $emit("Exportando datos de produccion...\n\n");
        $emit("Timestamp: " . now()->toDateTimeString() . "\n\n");

        foreach ([
            'clients'      => 'Clientes',
            'payments'     => 'Pagos',
            'admins'       => 'Admins',
            'inscriptions' => 'Inscripciones',
        ] as $table => $label) {
            try {
                $count = DB::table($table)->count();
                $emit("{$label}: {$count} registros\n");
            } catch (\Throwable) {
                $emit("{$label}: no accesible\n");
            }
        }

        $emit("\nPara un backup completo en formato SQL, usar mysqldump desde EasyPanel:\n");
        $emit("mysqldump -u USER -p wellcore_fitness > backup_" . now()->format('Ymd_His') . ".sql\n");
        $emit("\nContacta al equipo tecnico para descargar el dump via consola EasyPanel.\n");
    }

    private function toolTestSmtp(callable $emit, Admin $admin): void
    {
        $toEmail = $admin->email ?? '';
        if (! $toEmail) throw new \RuntimeException('El admin no tiene email configurado en su perfil.');

        $emit("Host SMTP: " . config('mail.mailers.smtp.host', 'N/A') . "\n");
        $emit("Puerto:    " . config('mail.mailers.smtp.port', 'N/A') . "\n");
        $emit("Enviando email de prueba a: {$toEmail}...\n\n");

        Mail::raw(
            'Prueba de conexion SMTP desde WellCore Admin. Si recibes este mensaje, el servidor de correo funciona correctamente.',
            fn ($m) => $m->to($toEmail)->subject('Test SMTP — WellCore Admin')
        );

        $emit("Email enviado exitosamente.\n");
        $emit("Revisa la bandeja de entrada de {$toEmail}.\n");
    }

    private function toolResendVoucher(array $params, callable $emit): void
    {
        $clientEmail = trim($params['client_email'] ?? '');
        if (! $clientEmail) throw new \RuntimeException('El campo client_email es requerido.');
        if (! filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) throw new \RuntimeException('Email invalido.');

        $emit("Buscando cliente: {$clientEmail}...\n");
        $client = DB::table('clients')->where('email', $clientEmail)->first();
        if (! $client) throw new \RuntimeException("No se encontro cliente con email '{$clientEmail}'.");

        $emit("Cliente encontrado: " . ($client->name ?? $client->email) . " (ID: {$client->id})\n");
        $emit("Buscando ultimo pago...\n");

        $payment = DB::table('payments')
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->first();

        if (! $payment) throw new \RuntimeException("El cliente no tiene pagos registrados.");

        $amount = number_format((float) ($payment->amount ?? 0), 0, ',', '.');
        $emit("Pago: ID {$payment->id} — \${$amount} — {$payment->created_at}\n");
        $emit("Reenviando comprobante...\n");

        $clientName = $client->name ?? $client->email;
        Mail::raw(
            "Comprobante de pago WellCore Fitness\n\nCliente: {$clientName}\nMonto: \${$amount}\nFecha: {$payment->created_at}\nID de pago: {$payment->id}\n\nGracias por ser parte de WellCore.",
            fn ($m) => $m->to($clientEmail)->subject('Comprobante de pago — WellCore Fitness')
        );

        $emit("Comprobante enviado a {$clientEmail}.\n");
    }
}
