<?php

namespace App\Livewire\Client;

use App\Models\AssignedPlan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class PlanViewer extends Component
{
    public ?array $trainingPlan = null;
    public ?array $nutritionPlan = null;
    public ?array $supplementPlan = null;
    public string $activeTab = 'entrenamiento';

    public function mount(): void
    {
        $clientId = auth('wellcore')->id();

        $plans = AssignedPlan::where('client_id', $clientId)
            ->where('active', true)
            ->get();

        foreach ($plans as $plan) {
            $content = is_array($plan->content)
                ? $plan->content
                : json_decode($plan->content, true);

            match ($plan->plan_type) {
                'entrenamiento' => $this->trainingPlan = $content,
                'nutricion' => $this->nutritionPlan = $content,
                'suplementacion' => $this->supplementPlan = $content,
                default => null,
            };
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.client.plan-viewer');
    }
}
