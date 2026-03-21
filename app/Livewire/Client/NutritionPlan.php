<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class NutritionPlan extends Component
{
    public ?array $plan = null;
    public string $planType = 'nutricion';

    // Macro data for donut chart
    public int $proteinGrams = 0;
    public int $carbGrams = 0;
    public int $fatGrams = 0;
    public bool $hasMacros = false;

    public function mount(): void
    {
        $assignedPlan = AssignedPlan::where('client_id', auth('wellcore')->id())
            ->where('plan_type', 'nutricion')
            ->where('active', true)
            ->latest()
            ->first();

        if ($assignedPlan && $assignedPlan->content) {
            $this->plan = is_array($assignedPlan->content)
                ? $assignedPlan->content
                : json_decode($assignedPlan->content, true);
        }

        // Extract macro data for donut chart
        if ($this->plan && isset($this->plan['macros'])) {
            $macros = $this->plan['macros'];
            $this->proteinGrams = (int) ($macros['proteina'] ?? 0);
            $this->carbGrams = (int) ($macros['carbohidratos'] ?? 0);
            $this->fatGrams = (int) ($macros['grasas'] ?? 0);
            $this->hasMacros = ($this->proteinGrams + $this->carbGrams + $this->fatGrams) > 0;
        }

        // Fallback example values when no real macro data exists
        if (!$this->hasMacros && $this->plan) {
            $this->proteinGrams = 130;
            $this->carbGrams = 200;
            $this->fatGrams = 65;
            $this->hasMacros = true;
        }
    }

    public function render()
    {
        return view('livewire.client.nutrition-plan');
    }
}
