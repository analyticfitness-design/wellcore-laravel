<?php

namespace App\Livewire\Client;

use App\Models\FoodAnalysis;
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

    // Photo upload (for future AI use)
    public $photo = null;

    // Flash
    public bool $saved = false;

    // AI availability
    public bool $aiAvailable = false;

    public function mount(): void
    {
        $this->aiAvailable = !empty(config('services.anthropic.key')) || !empty(env('ANTHROPIC_API_KEY'));
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
            'client_id' => $clientId,
            'food_name' => $this->food_name,
            'calories' => $this->calories,
            'protein' => $this->protein,
            'carbs' => $this->carbs,
            'fat' => $this->fat,
            'source' => 'manual',
        ]);

        $this->reset(['food_name', 'calories', 'protein', 'carbs', 'fat']);
        $this->saved = true;
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

    public function removePhoto(): void
    {
        $this->photo = null;
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
