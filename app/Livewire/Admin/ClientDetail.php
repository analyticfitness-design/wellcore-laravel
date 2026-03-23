<?php

namespace App\Livewire\Admin;

use App\Enums\ClientStatus;
use App\Enums\PlanType;
use App\Models\Admin;
use App\Models\AssignedPlan;
use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Detalle Cliente'])]
class ClientDetail extends Component
{
    public int $clientId;
    public string $tab = 'info';

    // Client editable fields
    public string $editStatus = '';
    public string $editPlan = '';

    // Assign coach
    public int $selectedCoachId = 0;
    public string $assignPlanType = 'entrenamiento';
    public bool $showCoachModal = false;

    // Flash messages
    public string $successMessage = '';

    public function mount(int $clientId): void
    {
        $client = Client::findOrFail($clientId);
        $this->clientId = $client->id;
        $this->editStatus = $client->status?->value ?? '';
        $this->editPlan = $client->plan?->value ?? '';
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function updateStatus(): void
    {
        $client = Client::findOrFail($this->clientId);
        $client->status = $this->editStatus;
        $client->save();

        $this->successMessage = 'Estado actualizado a ' . ClientStatus::from($this->editStatus)->label();
    }

    public function updatePlan(): void
    {
        $client = Client::findOrFail($this->clientId);
        $client->plan = $this->editPlan;
        $client->save();

        $this->successMessage = 'Plan actualizado a ' . PlanType::from($this->editPlan)->label();
    }

    public function openCoachModal(): void
    {
        $this->showCoachModal = true;
        $this->selectedCoachId = 0;
        $this->assignPlanType = 'entrenamiento';
    }

    public function closeCoachModal(): void
    {
        $this->showCoachModal = false;
    }

    public function assignCoach(): void
    {
        $this->validate([
            'selectedCoachId' => 'required|integer|min:1',
        ]);

        try {
            $coach = Admin::where('id', $this->selectedCoachId)
                ->where('role', 'coach')
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->addError('selectedCoachId', 'Coach no encontrado.');
            return;
        }

        // Deactivate existing active plan of same type for this client
        AssignedPlan::where('client_id', $this->clientId)
            ->where('plan_type', $this->assignPlanType)
            ->where('active', true)
            ->update(['active' => false]);

        // Create new assignment
        AssignedPlan::create([
            'client_id' => $this->clientId,
            'plan_type' => $this->assignPlanType,
            'content' => json_encode(['coach_assigned' => true, 'notes' => 'Asignado desde admin panel']),
            'version' => 1,
            'assigned_by' => $coach->id,
            'valid_from' => now()->toDateString(),
            'active' => true,
        ]);

        $this->showCoachModal = false;
        $this->successMessage = 'Coach ' . $coach->name . ' asignado correctamente al plan de ' . $this->assignPlanType;
    }

    public function dismissMessage(): void
    {
        $this->successMessage = '';
    }

    public function render()
    {
        $client = Client::with([
            'assignedPlans' => fn ($q) => $q->orderByDesc('created_at'),
            'assignedPlans.assignedBy',
            'checkins' => fn ($q) => $q->orderByDesc('checkin_date'),
            'payments' => fn ($q) => $q->orderByDesc('created_at'),
            'weightLogs' => fn ($q) => $q->orderByDesc('date')->limit(20),
            'progressPhotos' => fn ($q) => $q->orderByDesc('photo_date'),
            'biometricLogs' => fn ($q) => $q->orderByDesc('log_date')->limit(20),
        ])->findOrFail($this->clientId);

        $coaches = Admin::where('role', 'coach')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Determine the currently assigned coach (from active plan)
        $currentCoach = $client->assignedPlans
            ->where('active', true)
            ->first()
            ?->assignedBy;

        return view('livewire.admin.client-detail', [
            'client' => $client,
            'coaches' => $coaches,
            'currentCoach' => $currentCoach,
        ]);
    }
}
