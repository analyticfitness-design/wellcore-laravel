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
    }

    public function render()
    {
        return view('livewire.client.nutrition-plan');
    }
}
