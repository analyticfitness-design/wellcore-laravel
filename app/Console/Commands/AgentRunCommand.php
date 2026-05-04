<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ClientStatus;
use App\Models\Admin;
use App\Models\Client;
use App\Services\ManagedAgentService;
use Illuminate\Console\Command;

final class AgentRunCommand extends Command
{
    protected $signature = 'agent:run
                                {task : checkin-analysis | coach-report | adhoc}
                                {--coach-id= : ID del coach (para coach-report)}
                                {--prompt=   : Prompt libre (para adhoc)}
                                {--system=   : System prompt (para adhoc)}';

    protected $description = 'Ejecuta una tarea con Managed Agents de Anthropic.';

    public function handle(ManagedAgentService $agent): int
    {
        $task = $this->argument('task');

        $result = match ($task) {
            'checkin-analysis' => $this->runCheckinAnalysis($agent),
            'coach-report'     => $this->runCoachReport($agent),
            'adhoc'            => $this->runAdHoc($agent),
            default            => $this->invalidTask($task),
        };

        if ($result === null) {
            $this->error('El agente no retorno resultado. Revisa laravel.log para detalles.');
            $this->line('');
            $this->line('Posibles causas:');
            $this->line('  - ANTHROPIC_API_KEY no configurada en .env');
            $this->line('  - La beta de Managed Agents no esta activa en tu cuenta');
            $this->line('  - Error de red al conectar con api.anthropic.com');
            return self::FAILURE;
        }

        if ($result === 'invalid_task') {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('─── Resultado del Agente ──────────────────────────────────');
        $this->line($result);
        $this->info('───────────────────────────────────────────────────────────');
        $this->newLine();

        // Guardar en storage para referencia
        $filename = storage_path('logs/agent_' . $task . '_' . now()->format('Y-m-d_His') . '.txt');
        file_put_contents($filename, $result);
        $this->line("Resultado guardado en: {$filename}");

        return self::SUCCESS;
    }

    private function runCheckinAnalysis(ManagedAgentService $agent): ?string
    {
        $this->info('Obteniendo clientes activos de la BD...');

        $clientIds = Client::where('status', ClientStatus::Activo)
            ->limit(50)
            ->pluck('id')
            ->toArray();

        if (empty($clientIds)) {
            $this->warn('No hay clientes activos en la BD.');
            return null;
        }

        $this->info("Analizando " . count($clientIds) . " clientes...");
        $this->line('(Esto puede tomar 30-120 segundos)');

        return $agent->analyzeClientCheckins($clientIds);
    }

    private function runCoachReport(ManagedAgentService $agent): ?string
    {
        $coachId = (int) $this->option('coach-id');
        if (! $coachId) {
            $this->error('Debes especificar --coach-id=N');
            return 'invalid_task';
        }

        $coach = Admin::find($coachId);
        if (! $coach) {
            $this->error("Coach ID {$coachId} no encontrado.");
            return 'invalid_task';
        }

        $this->info("Generando reporte para {$coach->name}...");
        return $agent->generateCoachWeeklyReport($coachId, $coach->name);
    }

    private function runAdHoc(ManagedAgentService $agent): ?string
    {
        $prompt = $this->option('prompt');
        $system = $this->option('system') ?? 'Eres un asistente de analisis de datos de WellCore Fitness.';

        if (! $prompt) {
            $this->error('Debes especificar --prompt="tu tarea aqui"');
            return 'invalid_task';
        }

        $this->info('Ejecutando tarea ad-hoc...');
        return $agent->runAdHoc($system, $prompt, 'Ad-hoc ' . now()->toDateString());
    }

    private function invalidTask(string $task): string
    {
        $this->error("Tarea '{$task}' no reconocida. Opciones: checkin-analysis, coach-report, adhoc");
        return 'invalid_task';
    }
}
