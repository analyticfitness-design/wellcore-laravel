<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('wellcore.ai.api_key', '');
        $this->model = config('wellcore.ai.model', 'claude-haiku-4-5-20251001');
        $this->baseUrl = config('wellcore.ai.base_url', 'https://api.anthropic.com');
    }

    public function generateText(string $systemPrompt, string $userMessage, int $maxTokens = 4096): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('AI Service: API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(120)->post("{$this->baseUrl}/v1/messages", [
                'model' => $this->model,
                'max_tokens' => $maxTokens,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text');
            }

            Log::error('AI Service error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('AI Service exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function generatePlan(string $planType, array $clientData): ?array
    {
        $systemPrompt = $this->getPlanSystemPrompt($planType);
        $userMessage = $this->formatClientDataForPlan($clientData);

        $response = $this->generateText($systemPrompt, $userMessage, 8192);
        if (!$response) return null;

        // Try to extract JSON from response
        $jsonMatch = [];
        if (preg_match('/\{[\s\S]*\}/m', $response, $jsonMatch)) {
            $decoded = json_decode($jsonMatch[0], true);
            if ($decoded) return $decoded;
        }

        return ['raw_content' => $response];
    }

    /**
     * Analyze an image using Claude's vision capabilities.
     *
     * @param string $base64Image Base64-encoded image data
     * @param string $mediaType   MIME type: 'image/jpeg', 'image/png', 'image/webp'
     * @param string $systemPrompt
     * @param string $userMessage
     * @param int    $maxTokens
     */
    public function analyzeImage(
        string $base64Image,
        string $mediaType,
        string $systemPrompt,
        string $userMessage,
        int $maxTokens = 1024
    ): ?string {
        if (empty($this->apiKey)) {
            Log::warning('AI Service: API key not configured for image analysis');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post("{$this->baseUrl}/v1/messages", [
                'model'      => $this->model,
                'max_tokens' => $maxTokens,
                'system'     => $systemPrompt,
                'messages'   => [
                    [
                        'role'    => 'user',
                        'content' => [
                            [
                                'type'   => 'image',
                                'source' => [
                                    'type'       => 'base64',
                                    'media_type' => $mediaType,
                                    'data'       => $base64Image,
                                ],
                            ],
                            [
                                'type' => 'text',
                                'text' => $userMessage,
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text');
            }

            Log::error('AI Service image analysis error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('AI Service image exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Stream text generation from Anthropic Messages API using SSE.
     * Uses native cURL because Laravel's Http facade does not stream SSE cleanly.
     * $onDelta receives each text delta; return false to cancel cooperatively.
     *
     * @param  callable(string $delta): bool   $onDelta
     * @return array{ok:bool, chars:int, error:?string}
     */
    public function streamText(
        string $systemPrompt,
        string $userMessage,
        int $maxTokens,
        callable $onDelta,
    ): array {
        if (empty($this->apiKey)) {
            Log::warning('AI Service: API key not configured (stream)');
            return ['ok' => false, 'chars' => 0, 'error' => 'AI service not configured'];
        }

        $payload = json_encode([
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'stream'     => true,
            'system'     => $systemPrompt,
            'messages'   => [['role' => 'user', 'content' => $userMessage]],
        ], JSON_UNESCAPED_UNICODE);

        $buffer = '';
        $totalChars = 0;
        $aborted = false;
        $errorMsg = null;

        $ch = curl_init("{$this->baseUrl}/v1/messages");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
                'content-type: application/json',
                'accept: text/event-stream',
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 180,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_WRITEFUNCTION  => function ($curl, $chunk) use (
                &$buffer, &$totalChars, &$aborted, &$errorMsg, $onDelta
            ) {
                $buffer .= $chunk;
                while (($eolPos = strpos($buffer, "\n")) !== false) {
                    $line = rtrim(substr($buffer, 0, $eolPos), "\r");
                    $buffer = substr($buffer, $eolPos + 1);
                    if ($line === '' || str_starts_with($line, ':') || str_starts_with($line, 'event:')) {
                        continue;
                    }
                    if (! str_starts_with($line, 'data:')) {
                        continue;
                    }
                    $json = trim(substr($line, 5));
                    if ($json === '' || $json === '[DONE]') {
                        continue;
                    }
                    $event = json_decode($json, true);
                    if (! is_array($event)) {
                        continue;
                    }
                    $type = $event['type'] ?? null;
                    if ($type === 'content_block_delta') {
                        $delta = $event['delta']['text'] ?? '';
                        if ($delta !== '') {
                            $totalChars += strlen($delta);
                            $continue = $onDelta($delta);
                            if ($continue === false) {
                                $aborted = true;
                                return -1;
                            }
                        }
                    } elseif ($type === 'error') {
                        $errorMsg = $event['error']['message'] ?? 'Unknown stream error';
                    }
                }
                return strlen($chunk);
            },
        ]);

        $ok = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($aborted) {
            return ['ok' => false, 'chars' => $totalChars, 'error' => 'aborted'];
        }
        if ($httpCode >= 400 || $errorMsg) {
            $err = $errorMsg ?? "HTTP {$httpCode}";
            Log::error('AI Service stream error', ['status' => $httpCode, 'err' => $err, 'curl' => $curlErr]);
            return ['ok' => false, 'chars' => $totalChars, 'error' => $err];
        }
        if ($ok === false && ! $aborted) {
            return ['ok' => false, 'chars' => $totalChars, 'error' => $curlErr ?: 'Stream failed'];
        }

        return ['ok' => true, 'chars' => $totalChars, 'error' => null];
    }

    protected function getPlanSystemPrompt(string $planType): string
    {
        return match($planType) {
            'entrenamiento' => 'Eres un coach de fitness experto. Genera un plan de entrenamiento personalizado en formato JSON con la estructura: {"dias": [{"nombre": "Dia 1 - Pecho y Triceps", "grupo_muscular": "...", "ejercicios": [{"nombre": "...", "series": 4, "repeticiones": "8-12", "descanso": "90s"}]}]}. Usa periodizacion ondulante. Responde SOLO con JSON.',
            'nutricion' => 'Eres un nutricionista deportivo. Genera un plan de nutricion personalizado en formato JSON con: {"macros": {"calorias": 2500, "proteina": "180g", "carbohidratos": "300g", "grasas": "70g"}, "comidas": [{"nombre": "Desayuno", "hora": "7:00", "alimentos": [{"nombre": "Avena", "cantidad": "80g"}]}]}. Responde SOLO con JSON.',
            'suplementacion' => 'Eres un especialista en suplementacion deportiva basada en evidencia. Genera un protocolo de suplementacion en formato JSON. Responde SOLO con JSON.',
            default => 'Eres un coach de fitness experto. Responde en formato JSON.',
        };
    }

    protected function formatClientDataForPlan(array $data): string
    {
        $lines = ["Datos del cliente:"];
        foreach ($data as $key => $value) {
            if (is_array($value)) $value = implode(', ', $value);
            $lines[] = "- {$key}: {$value}";
        }
        return implode("\n", $lines);
    }
}
