<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Wrapper para la Anthropic Managed Agents API (beta 2026-04).
 *
 * Flujo de uso:
 *   1. Agente   -> se crea una vez, ID persistido en ANTHROPIC_AGENT_ID
 *   2. Entorno  -> se crea una vez, ID persistido en ANTHROPIC_ENV_ID
 *   3. Sesion   -> se crea por tarea, efimera
 *   4. SSE      -> se abre antes de enviar el mensaje, se lee hasta idle
 */
final class ManagedAgentService
{
    private const BASE      = 'https://api.anthropic.com';
    private const VERSION   = '2023-06-01';
    private const BETA      = 'managed-agents-2026-04-01';
    private const TIMEOUT   = 300;

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('wellcore.ai.api_key', '');
    }

    // ─── API publica ───────────────────────────────────────────

    public function analyzeClientCheckins(array $clientIds, string $coachName = ''): ?string
    {
        $ids   = implode(', ', $clientIds);
        $coach = $coachName ? "Coach: {$coachName}." : '';

        return $this->runSession(
            agentId: $this->resolveAgentId('checkin-analyzer', $this->checkinAnalyzerSystem()),
            envId:   $this->resolveEnvId(),
            title:   'Daily Check-in Analysis ' . now()->toDateString(),
            message: "{$coach} Analiza los clientes con IDs: {$ids}. " .
                     "Reporta: tasa de adherencia (%), clientes en riesgo de churn (menos de 3 check-ins en 14 dias), " .
                     "y un mensaje de motivacion personalizado para cada uno en riesgo.",
        );
    }

    public function generateCoachWeeklyReport(int $coachId, string $coachName): ?string
    {
        return $this->runSession(
            agentId: $this->resolveAgentId('coach-reporter', $this->coachReporterSystem()),
            envId:   $this->resolveEnvId(),
            title:   "Coach Report {$coachId} " . now()->toDateString(),
            message: "Genera el reporte semanal del coach {$coachName} (ID: {$coachId}). " .
                     "Incluye: tickets cerrados, SLA de respuesta, tasa de retencion, " .
                     "y calculo de comision (60% revenue + 40% bonus si retencion >= 90%).",
        );
    }

    public function runAdHoc(string $system, string $task, string $title = 'Ad-hoc'): ?string
    {
        $agentId = $this->createAgent('adhoc-' . now()->timestamp, $system);
        if (! $agentId) return null;

        return $this->runSession(
            agentId: $agentId,
            envId:   $this->resolveEnvId(),
            title:   $title,
            message: $task,
        );
    }

    // ─── Nucleo: sesion SSE ───────────────────────────────────

    private function runSession(string $agentId, string $envId, string $title, string $message): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('ManagedAgentService: ANTHROPIC_API_KEY no configurada');
            return null;
        }

        $session = $this->post('/v1/sessions', [
            'agent'          => $agentId,
            'environment_id' => $envId,
            'title'          => $title,
        ]);

        if (! $session || empty($session['id'])) {
            Log::error('ManagedAgentService: no se pudo crear sesion', compact('agentId', 'envId'));
            return null;
        }

        $sessionId = $session['id'];
        Log::info("ManagedAgentService: sesion creada {$sessionId}");

        $sent = $this->post("/v1/sessions/{$sessionId}/events", [
            'events' => [[
                'type'    => 'user.message',
                'content' => [['type' => 'text', 'text' => $message]],
            ]],
        ]);

        if (! $sent) {
            Log::error('ManagedAgentService: no se pudo enviar mensaje', compact('sessionId'));
            return null;
        }

        return $this->streamSession($sessionId);
    }

    private function streamSession(string $sessionId): ?string
    {
        $output = '';
        $buffer = '';
        $done   = false;

        $ch = curl_init(self::BASE . "/v1/sessions/{$sessionId}/events/stream");
        curl_setopt_array($ch, [
            CURLOPT_HTTPGET        => true,
            CURLOPT_HTTPHEADER     => $this->headers(['accept' => 'text/event-stream']),
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) use (&$output, &$buffer, &$done) {
                $buffer .= $chunk;
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line   = rtrim(substr($buffer, 0, $pos), "\r");
                    $buffer = substr($buffer, $pos + 1);

                    if (! str_starts_with($line, 'data:')) continue;

                    $json  = trim(substr($line, 5));
                    if ($json === '' || $json === '[DONE]') continue;

                    $event = json_decode($json, true);
                    if (! is_array($event)) continue;

                    $type = $event['type'] ?? '';

                    if ($type === 'agent.message') {
                        foreach ($event['content'] ?? [] as $block) {
                            $output .= $block['text'] ?? '';
                        }
                    } elseif (in_array($type, ['session.status_idle', 'session.completed'], true)) {
                        $done = true;
                        return -1;
                    } elseif ($type === 'error') {
                        Log::error('ManagedAgentService SSE error', $event);
                    }
                }
                return strlen($chunk);
            },
        ]);

        curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 400) {
            Log::error('ManagedAgentService stream HTTP error', compact('httpCode', 'sessionId'));
            return null;
        }

        if ($curlErr && ! $done) {
            Log::error('ManagedAgentService cuRL error', compact('curlErr', 'sessionId'));
            return null;
        }

        Log::info("ManagedAgentService: sesion {$sessionId} OK", ['chars' => strlen($output)]);
        return $output ?: null;
    }

    // ─── Provisioning ────────────────────────────────────────

    private function resolveAgentId(string $slug, string $system): string
    {
        $envKey = 'ANTHROPIC_AGENT_' . strtoupper(str_replace('-', '_', $slug));
        $cached = env($envKey) ?: Cache::get("managed_agent_id:{$slug}");

        if ($cached) return (string) $cached;

        $id = $this->createAgent($slug, $system);
        if ($id) {
            Cache::put("managed_agent_id:{$slug}", $id, now()->addDays(30));
            Log::info("ManagedAgentService: agente '{$slug}' creado -> {$id}. Agrega: {$envKey}={$id}");
        }
        return $id ?? '';
    }

    private function resolveEnvId(): string
    {
        $cached = env('ANTHROPIC_ENV_ID') ?: Cache::get('managed_agent_env_id');
        if ($cached) return (string) $cached;

        $id = $this->createEnvironment();
        if ($id) {
            Cache::put('managed_agent_env_id', $id, now()->addDays(30));
            Log::info("ManagedAgentService: entorno creado -> {$id}. Agrega: ANTHROPIC_ENV_ID={$id}");
        }
        return $id ?? '';
    }

    private function createAgent(string $name, string $system): ?string
    {
        $result = $this->post('/v1/agents', [
            'name'   => "WellCore {$name}",
            'model'  => config('wellcore.ai.model', 'claude-opus-4-7'),
            'system' => $system,
            'tools'  => [['type' => 'agent_toolset_20260401']],
        ]);
        return $result['id'] ?? null;
    }

    private function createEnvironment(): ?string
    {
        $result = $this->post('/v1/environments', [
            'name'   => 'wellcore-production',
            'config' => ['type' => 'cloud', 'networking' => ['type' => 'unrestricted']],
        ]);
        return $result['id'] ?? null;
    }

    // ─── HTTP helper ──────────────────────────────────────────

    private function post(string $path, array $body): ?array
    {
        try {
            $ch = curl_init(self::BASE . $path);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => $this->headers(),
                CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $raw      = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                Log::error("ManagedAgentService cURL: {$curlErr}", compact('path'));
                return null;
            }

            $decoded = json_decode($raw ?: '', true);
            if ($httpCode >= 400) {
                Log::error("ManagedAgentService HTTP {$httpCode}", ['path' => $path, 'response' => $decoded]);
                return null;
            }

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::error('ManagedAgentService exception', ['path' => $path, 'msg' => $e->getMessage()]);
            return null;
        }
    }

    /** @return list<string> */
    private function headers(array $extra = []): array
    {
        $headers = [
            'x-api-key: '         . $this->apiKey,
            'anthropic-version: ' . self::VERSION,
            'anthropic-beta: '    . self::BETA,
            'content-type: application/json',
        ];
        foreach ($extra as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }
        return $headers;
    }

    // ─── System prompts ───────────────────────────────────────

    private function checkinAnalyzerSystem(): string
    {
        return 'Eres el Analizador de Adherencia de WellCore Fitness, plataforma de coaching personalizado en LATAM. ' .
               'Clasificas clientes por riesgo de abandono: ' .
               'RIESGO ALTO: menos de 3 check-ins en 14 dias; ' .
               'RIESGO MODERADO: entre 3 y 5; ' .
               'BUENA ADHERENCIA: mas de 5. ' .
               'Para cada cliente en riesgo, propones un mensaje motivacional breve (maximo 2 lineas) en espanol latino neutro. ' .
               'No menciones IA ni algoritmo. Redacta como si fuera el equipo de WellCore. ' .
               'Responde con: resumen estadistico, tabla de riesgo alto, tabla de riesgo moderado.';
    }

    private function coachReporterSystem(): string
    {
        return 'Eres el Analista de Desempenio de Coaches de WellCore Fitness. ' .
               'Generas reportes semanales con: tickets cerrados, SLA de respuesta (objetivo < 24h), ' .
               'tasa de retencion de clientes, y calculo de comision (60% revenue + 40% bonus si retencion >= 90%). ' .
               'Responde en espanol latino neutro. Formato limpio con secciones claras.';
    }
}
