<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use App\Models\BiometricLog;
use App\Models\ClientProfile;
use App\Models\HabitLog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class NutritionPlan extends Component
{
    public ?array $plan = null;

    // Macro data
    public int $proteinGrams = 0;
    public int $carbGrams = 0;
    public int $fatGrams = 0;
    public int $totalCalories = 0;
    public bool $hasMacros = false;
    public array $macroPercentages = ['protein' => 0, 'carbs' => 0, 'fat' => 0];

    // Water tracker
    public int $waterGoalMl = 2500;
    public int $waterConsumedMl = 0;

    // Weight
    public ?float $weightGoalKg = null;
    public ?float $currentWeightKg = null;

    // Meals + extras
    public array $mealLog = [];
    public ?string $coachNotes = null;
    public ?string $planObjetivo = null;
    public ?array $restDayInfo = null;
    public ?string $hydrationNote = null;

    public function mount(): void
    {
        $clientId = auth('wellcore')->id();

        $assignedPlan = AssignedPlan::where('client_id', $clientId)
            ->where('plan_type', 'nutricion')
            ->where('active', true)
            ->latest()
            ->first();

        if ($assignedPlan && $assignedPlan->content) {
            $this->plan = is_array($assignedPlan->content)
                ? $assignedPlan->content
                : json_decode($assignedPlan->content, true);
        }

        if ($this->plan) {
            $this->parseMacros();
            $this->parseMeals();
            $this->parseExtras();
        }

        if ($clientId) {
            $this->loadWaterData($clientId);
            $this->loadWeightData($clientId);
        }
    }

    // ─── Data parsing ─────────────────────────────────────────────────────────

    private function parseMacros(): void
    {
        $macros = $this->plan['macros'] ?? [];

        // Support multiple key conventions: proteina_g | proteina | protein
        $this->proteinGrams = (int) ($macros['proteina_g'] ?? $macros['proteina'] ?? $macros['protein'] ?? 0);
        $this->carbGrams    = (int) ($macros['carbohidratos_g'] ?? $macros['carbs_g'] ?? $macros['carbohidratos'] ?? $macros['carbs'] ?? 0);
        $this->fatGrams     = (int) ($macros['grasas_g'] ?? $macros['grasas'] ?? $macros['fat'] ?? 0);
        $this->hasMacros    = ($this->proteinGrams + $this->carbGrams + $this->fatGrams) > 0;

        // Calories: explicit in plan > calculated from macros
        $planCalories = (int) ($this->plan['calorias_diarias']
            ?? $this->plan['calorias']
            ?? $macros['calorias']
            ?? 0);

        $this->totalCalories = $planCalories > 0
            ? $planCalories
            : ($this->proteinGrams * 4) + ($this->carbGrams * 4) + ($this->fatGrams * 9);

        $this->macroPercentages = $this->calculateMacroPercentages();
    }

    private function parseMeals(): void
    {
        // Try: root['comidas'] → plan_dia_entrenamiento['comidas'] → meals
        $raw = $this->plan['comidas']
            ?? $this->plan['plan_dia_entrenamiento']['comidas']
            ?? $this->plan['meals']
            ?? [];

        $this->mealLog = array_map([$this, 'normalizeMeal'], $raw);
    }

    private function normalizeMeal(array $meal): array
    {
        $macros = $meal['macros'] ?? [];
        return [
            'nombre'    => $meal['nombre'] ?? $meal['name'] ?? 'Comida',
            'calorias'  => (int) ($meal['calorias'] ?? $meal['calories'] ?? $meal['kcal'] ?? 0),
            'alimentos' => $meal['alimentos'] ?? $meal['foods'] ?? $meal['items'] ?? [],
            'notas'     => $meal['notas'] ?? $meal['notes'] ?? null,
            'macros'    => [
                'proteina'     => (int) ($macros['proteina_g'] ?? $macros['proteina'] ?? $macros['protein_g'] ?? $macros['protein'] ?? 0),
                'carbohidratos'=> (int) ($macros['carbs_g'] ?? $macros['carbohidratos_g'] ?? $macros['carbohidratos'] ?? $macros['carbs'] ?? 0),
                'grasas'       => (int) ($macros['grasas_g'] ?? $macros['grasas'] ?? $macros['fat_g'] ?? $macros['fat'] ?? 0),
            ],
        ];
    }

    private function parseExtras(): void
    {
        $this->coachNotes   = $this->plan['notas_coach'] ?? $this->plan['coach_notes'] ?? null;
        $this->planObjetivo = $this->plan['objetivo'] ?? $this->plan['objetivo_general'] ?? null;

        // Rest day summary
        if (isset($this->plan['plan_dia_descanso'])) {
            $rest = $this->plan['plan_dia_descanso'];
            $this->restDayInfo = [
                'descripcion'       => $rest['descripcion'] ?? null,
                'calorias_objetivo' => (int) ($rest['calorias_objetivo'] ?? 0),
                'ajustes'           => $rest['ajustes'] ?? [],
            ];
        }

        // Hydration note
        if (isset($this->plan['hidratacion'])) {
            $h = $this->plan['hidratacion'];
            $liters = (float) ($h['agua_minima_litros'] ?? 0);
            if ($liters > 0) {
                $this->waterGoalMl = (int) ($liters * 1000);
            }
            $this->hydrationNote = $h['electrolitos'] ?? null;
        }
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    public function toggleWater(int $amount = 250): void
    {
        $clientId = auth('wellcore')->id();
        $today    = now()->toDateString();

        $log = HabitLog::where('client_id', $clientId)
            ->where('habit_type', 'agua')
            ->where('log_date', $today)
            ->first();

        if ($log) {
            $log->value += $amount;
            $log->save();
        } else {
            HabitLog::create([
                'client_id'  => $clientId,
                'log_date'   => $today,
                'habit_type' => 'agua',
                'value'      => $amount,
            ]);
        }

        $this->waterConsumedMl += $amount;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function calculateMacroPercentages(): array
    {
        $total = $this->proteinGrams + $this->carbGrams + $this->fatGrams;
        if ($total === 0) {
            return ['protein' => 0, 'carbs' => 0, 'fat' => 0];
        }

        return [
            'protein' => (int) round(($this->proteinGrams / $total) * 100),
            'carbs'   => (int) round(($this->carbGrams / $total) * 100),
            'fat'     => (int) round(($this->fatGrams / $total) * 100),
        ];
    }

    private function loadWaterData(int $clientId): void
    {
        $todayWater = HabitLog::where('client_id', $clientId)
            ->where('habit_type', 'agua')
            ->where('log_date', now()->toDateString())
            ->first();

        $this->waterConsumedMl = $todayWater ? (int) $todayWater->value : 0;
    }

    private function loadWeightData(int $clientId): void
    {
        if ($this->plan && isset($this->plan['peso_objetivo'])) {
            $this->weightGoalKg = (float) $this->plan['peso_objetivo'];
        } else {
            $profile = ClientProfile::where('client_id', $clientId)->first();
            if ($profile && is_numeric($profile->objetivo ?? null)) {
                $this->weightGoalKg = (float) $profile->objetivo;
            }
        }

        $latest = BiometricLog::where('client_id', $clientId)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->latest('log_date')
            ->first();

        if ($latest) {
            $this->currentWeightKg = (float) $latest->weight_kg;
        } else {
            $profile ??= ClientProfile::where('client_id', $clientId)->first();
            if ($profile && $profile->peso) {
                $this->currentWeightKg = (float) $profile->peso;
            }
        }
    }

    public function render()
    {
        return view('livewire.client.nutrition-plan');
    }
}
