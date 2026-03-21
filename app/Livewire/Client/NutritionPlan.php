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
    public string $planType = 'nutricion';

    // Macro data
    public int $proteinGrams = 0;
    public int $carbGrams = 0;
    public int $fatGrams = 0;
    public bool $hasMacros = false;

    // Calculated fields
    public int $totalCalories = 0;
    public array $macroPercentages = ['protein' => 0, 'carbs' => 0, 'fat' => 0];

    // Water tracker
    public int $waterGoalMl = 2500;
    public int $waterConsumedMl = 0;

    // Weight goal
    public ?float $weightGoalKg = null;
    public ?float $currentWeightKg = null;

    // Meal log
    public array $mealLog = [];

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

        // Extract macro data
        if ($this->plan && isset($this->plan['macros'])) {
            $macros = $this->plan['macros'];
            $this->proteinGrams = (int) ($macros['proteina'] ?? 0);
            $this->carbGrams = (int) ($macros['carbohidratos'] ?? 0);
            $this->fatGrams = (int) ($macros['grasas'] ?? 0);
            $this->hasMacros = ($this->proteinGrams + $this->carbGrams + $this->fatGrams) > 0;
        }

        // Fallback example values when no real macro data exists
        if (! $this->hasMacros && $this->plan) {
            $this->proteinGrams = 130;
            $this->carbGrams = 200;
            $this->fatGrams = 65;
            $this->hasMacros = true;
        }

        // Calculate total calories from macros (protein*4 + carbs*4 + fat*9)
        if ($this->hasMacros) {
            $planCalories = isset($this->plan['macros']) ? (int) ($this->plan['macros']['calorias'] ?? 0) : 0;
            $calculatedCalories = ($this->proteinGrams * 4) + ($this->carbGrams * 4) + ($this->fatGrams * 9);
            $this->totalCalories = ($planCalories > 0) ? $planCalories : $calculatedCalories;
        }

        // Calculate macro percentages
        $this->macroPercentages = $this->calculateMacroPercentages();

        // Water tracker
        $this->loadWaterData($clientId);

        // Weight goal
        $this->loadWeightData($clientId);

        // Meal log from plan
        if ($this->plan && isset($this->plan['comidas'])) {
            $this->mealLog = $this->plan['comidas'];
        }
    }

    public function toggleWater(int $amount = 250): void
    {
        $clientId = auth('wellcore')->id();
        $today = now()->toDateString();

        // Find or create today's water log
        $log = HabitLog::where('client_id', $clientId)
            ->where('habit_type', 'agua')
            ->where('log_date', $today)
            ->first();

        if ($log) {
            $log->value = $log->value + $amount;
            $log->save();
        } else {
            HabitLog::create([
                'client_id' => $clientId,
                'log_date' => $today,
                'habit_type' => 'agua',
                'value' => $amount,
            ]);
        }

        $this->waterConsumedMl = $this->waterConsumedMl + $amount;
    }

    private function calculateMacroPercentages(): array
    {
        $totalGrams = $this->proteinGrams + $this->carbGrams + $this->fatGrams;
        if ($totalGrams === 0) {
            return ['protein' => 0, 'carbs' => 0, 'fat' => 0];
        }

        return [
            'protein' => (int) round(($this->proteinGrams / $totalGrams) * 100),
            'carbs' => (int) round(($this->carbGrams / $totalGrams) * 100),
            'fat' => (int) round(($this->fatGrams / $totalGrams) * 100),
        ];
    }

    private function loadWaterData(int $clientId): void
    {
        // Water goal from plan or default
        if ($this->plan && isset($this->plan['agua_ml'])) {
            $this->waterGoalMl = (int) $this->plan['agua_ml'];
        }

        // Today's water consumption from habit_logs
        $todayWater = HabitLog::where('client_id', $clientId)
            ->where('habit_type', 'agua')
            ->where('log_date', now()->toDateString())
            ->first();

        $this->waterConsumedMl = $todayWater ? (int) $todayWater->value : 0;
    }

    private function loadWeightData(int $clientId): void
    {
        // Goal from plan or profile
        if ($this->plan && isset($this->plan['peso_objetivo'])) {
            $this->weightGoalKg = (float) $this->plan['peso_objetivo'];
        } else {
            $profile = ClientProfile::where('client_id', $clientId)->first();
            if ($profile && isset($profile->objetivo) && is_numeric($profile->objetivo)) {
                $this->weightGoalKg = (float) $profile->objetivo;
            }
        }

        // Current weight from latest biometric log
        $latestBiometric = BiometricLog::where('client_id', $clientId)
            ->whereNotNull('weight_kg')
            ->where('weight_kg', '>', 0)
            ->latest('log_date')
            ->first();

        if ($latestBiometric) {
            $this->currentWeightKg = (float) $latestBiometric->weight_kg;
        } else {
            // Fallback to profile weight
            $profile = $profile ?? ClientProfile::where('client_id', $clientId)->first();
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
