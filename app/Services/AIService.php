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
