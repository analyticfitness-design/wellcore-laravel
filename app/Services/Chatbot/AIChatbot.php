<?php

namespace App\Services\Chatbot;

use App\Contracts\ChatbotInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatbot implements ChatbotInterface
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->model = config('services.anthropic.model', 'claude-haiku-4-5-20251001');
    }

    public function respond(string $message, array $context = []): string
    {
        if (!$this->isAvailable()) {
            return 'El asistente de IA no esta disponible en este momento. Por favor contacta a info@wellcorefitness.com';
        }

        try {
            $systemPrompt = $this->buildSystemPrompt($context);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => 500,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? 'No pude generar una respuesta.';
            }

            Log::warning('AI Chatbot error', ['status' => $response->status()]);
            return 'Hubo un problema al procesar tu pregunta. Intenta de nuevo.';
        } catch (\Exception $e) {
            Log::error('AI Chatbot exception', ['error' => $e->getMessage()]);
            return 'Error de conexion. Por favor intenta mas tarde.';
        }
    }

    public function provider(): string
    {
        return 'claude';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    private function buildSystemPrompt(array $context): string
    {
        $pageUrl = $context['page_url'] ?? '/';

        return <<<PROMPT
Eres el asistente virtual de WellCore Fitness, una plataforma de coaching fitness 1:1 basada en ciencia para Latinoamerica.

INFORMACION CLAVE:
- Planes: Esencial (\$299,000 COP/mes), Metodo (\$399,000 COP/mes), Elite (\$549,000 COP/mes)
- Programa RISE: \$99,900 COP pago unico, 30 dias
- Presencial: Solo en Bucaramanga, Colombia
- Metodologia: 5 pilares (Sobrecarga Progresiva, Periodizacion, Nutricion, Recuperacion, Adherencia)
- Garantia: 7 dias de reembolso
- Sin contratos de permanencia
- Coaching 1:1 con coach certificado
- Pago seguro via Wompi

REGLAS:
- Responde SOLO en español
- Se conciso (max 2-3 oraciones)
- No inventes informacion que no tengas
- Si no sabes algo, sugiere contactar info@wellcorefitness.com o WhatsApp
- Se amable y motivacional
- El usuario esta en la pagina: {$pageUrl}
PROMPT;
    }
}
