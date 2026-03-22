<?php

namespace App\Livewire\Client;

use App\Models\FoodAnalysis;
use App\Services\AIService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.client', ['title' => 'AI Nutricion'])]
class AINutrition extends Component
{
    use WithFileUploads;

    // Tab state
    public string $activeTab = 'manual';

    // Manual form fields
    public string $food_name = '';
    public ?int $calories = null;
    public ?float $protein = null;
    public ?float $carbs = null;
    public ?float $fat = null;

    // Photo upload
    public $photo = null;

    // Flash
    public bool $saved = false;

    // AI availability
    public bool $aiAvailable = false;

    // AI analysis state
    public bool $isAnalyzing = false;
    public ?array $analysisResult = null;
    public string $analysisError = '';

    public function mount(): void
    {
        $this->aiAvailable = !empty(config('wellcore.ai.api_key'));
    }

    protected function rules(): array
    {
        return [
            'food_name' => 'required|string|max:200',
            'calories' => 'required|integer|min:0|max:9999',
            'protein' => 'required|numeric|min:0|max:999',
            'carbs' => 'required|numeric|min:0|max:999',
            'fat' => 'required|numeric|min:0|max:999',
        ];
    }

    protected function messages(): array
    {
        return [
            'food_name.required' => 'El nombre de la comida es obligatorio.',
            'calories.required' => 'Las calorias son obligatorias.',
            'calories.integer' => 'Las calorias deben ser un numero entero.',
            'protein.required' => 'La proteina es obligatoria.',
            'carbs.required' => 'Los carbohidratos son obligatorios.',
            'fat.required' => 'La grasa es obligatoria.',
        ];
    }

    public function saveManual(): void
    {
        $this->validate();

        $clientId = auth('wellcore')->id();

        FoodAnalysis::create([
            'client_id'   => $clientId,
            'food_name'   => $this->food_name,
            'calories'    => $this->calories,
            'protein'     => $this->protein,
            'carbs'       => $this->carbs,
            'fat'         => $this->fat,
            'source'      => $this->analysisResult ? 'ai' : 'manual',
            'ai_response' => $this->analysisResult ?: null,
        ]);

        $this->reset(['food_name', 'calories', 'protein', 'carbs', 'fat']);
        $this->analysisResult = null;
        $this->photo          = null;
        $this->saved          = true;
        $this->dispatch('food-saved');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedPhoto(): void
    {
        $this->validate([
            'photo' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
    }

    public function analyzePhoto(): void
    {
        $this->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);

        $this->isAnalyzing   = true;
        $this->analysisError = '';
        $this->analysisResult = null;

        try {
            $path      = $this->photo->getRealPath();
            $base64    = base64_encode(file_get_contents($path));
            $mimeType  = $this->photo->getMimeType(); // 'image/jpeg', 'image/png', 'image/webp'

            $aiService = app(AIService::class);

            $systemPrompt = 'Eres un nutricionista experto. Analiza imágenes de alimentos y devuelve SOLO un JSON válido con la información nutricional estimada. No incluyas texto adicional fuera del JSON.';

            $userMessage = 'Analiza esta imagen de comida y devuelve un JSON con este formato exacto:
{
  "food_name": "Nombre del alimento o plato",
  "calories": 350,
  "protein_g": 25.5,
  "carbs_g": 30.0,
  "fat_g": 12.0,
  "confidence": "high|medium|low",
  "notes": "Nota opcional sobre la estimación"
}
Estima para una porción estándar visible en la imagen.';

            $rawResponse = $aiService->analyzeImage($base64, $mimeType, $systemPrompt, $userMessage, 512);

            if (!$rawResponse) {
                $this->analysisError = 'No se pudo analizar la imagen. Intenta de nuevo.';
                $this->isAnalyzing   = false;
                return;
            }

            // Extract the JSON block — Claude may wrap it in markdown fences or add prose
            preg_match('/\{.*\}/s', $rawResponse, $matches);
            $json = $matches[0] ?? $rawResponse;
            $data = json_decode($json, true);

            if (!$data || !isset($data['calories'])) {
                $this->analysisError = 'No se pudo interpretar el análisis. Ingresa los datos manualmente.';
                $this->isAnalyzing   = false;
                return;
            }

            $this->analysisResult = $data;

            // Pre-fill the manual form with AI values so the client can verify before saving
            $this->food_name = $data['food_name'] ?? '';
            $this->calories  = (int) ($data['calories'] ?? 0);
            $this->protein   = (float) ($data['protein_g'] ?? 0);
            $this->carbs     = (float) ($data['carbs_g'] ?? 0);
            $this->fat       = (float) ($data['fat_g'] ?? 0);

            // Switch to manual tab so the client can confirm and save
            $this->activeTab = 'manual';

        } catch (\Exception $e) {
            Log::error('AINutrition analyzePhoto error', ['message' => $e->getMessage()]);
            $this->analysisError = 'Error al procesar la imagen. Intenta de nuevo.';
        } finally {
            $this->isAnalyzing = false;
        }
    }

    public function removePhoto(): void
    {
        $this->photo          = null;
        $this->analysisResult = null;
        $this->analysisError  = '';
    }

    public function deleteEntry(int $id): void
    {
        FoodAnalysis::where('client_id', auth('wellcore')->id())
            ->where('id', $id)
            ->delete();
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        // Daily summary (today)
        $todayEntries = FoodAnalysis::where('client_id', $clientId)
            ->whereDate('created_at', today())
            ->get();

        $dailySummary = [
            'calories' => (int) $todayEntries->sum('calories'),
            'protein' => round((float) $todayEntries->sum('protein'), 1),
            'carbs' => round((float) $todayEntries->sum('carbs'), 1),
            'fat' => round((float) $todayEntries->sum('fat'), 1),
            'count' => $todayEntries->count(),
        ];

        // History: last 20 entries
        $history = FoodAnalysis::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('livewire.client.ai-nutrition', [
            'dailySummary' => $dailySummary,
            'history' => $history,
        ]);
    }
}
