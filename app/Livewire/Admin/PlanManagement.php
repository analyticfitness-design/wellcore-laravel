<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\PlanTemplate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Planes'])]
class PlanManagement extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $typeFilter     = 'all';
    public string $coachFilter    = 'all';
    public string $publicFilter   = 'all';
    public string $aiFilter       = 'all';
    public string $sortBy         = 'created_at';
    public string $sortDir        = 'desc';

    // Create / Edit modal
    public bool    $showFormModal    = false;
    public ?int    $editingId        = null;
    public string  $formName         = '';
    public string  $formPlanType     = 'entrenamiento';
    public string  $formMethodology  = '';
    public string  $formDescription  = '';
    public string  $formContentJson  = '';
    public bool    $formIsPublic     = false;
    public string  $formCoachId      = '';

    // View content modal
    public bool $showViewModal   = false;
    public ?int $viewingId       = null;

    // Delete confirmation
    public bool $showDeleteModal = false;
    public ?int $deletingId      = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCoachFilter(): void
    {
        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
    }

    // --- Create ---

    public function openCreate(): void
    {
        $this->reset([
            'editingId', 'formName', 'formPlanType', 'formMethodology',
            'formDescription', 'formContentJson', 'formIsPublic', 'formCoachId',
        ]);
        $this->formPlanType = 'entrenamiento';
        $this->formCoachId  = (string) (auth('wellcore')->id() ?? '');
        $this->showFormModal = true;
    }

    // --- Edit ---

    public function openEdit(int $id): void
    {
        $plan = PlanTemplate::findOrFail($id);

        $this->editingId       = $id;
        $this->formName        = $plan->name;
        $this->formPlanType    = $plan->plan_type;
        $this->formMethodology = $plan->methodology ?? '';
        $this->formDescription = $plan->description ?? '';
        $this->formContentJson = is_array($plan->content_json)
            ? json_encode($plan->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : ($plan->content_json ?? '');
        $this->formIsPublic    = (bool) $plan->is_public;
        $this->formCoachId     = (string) ($plan->coach_id ?? '');

        $this->showFormModal = true;
    }

    public function closeForm(): void
    {
        $this->showFormModal = false;
        $this->editingId     = null;
        $this->resetErrorBag();
    }

    public function savePlan(): void
    {
        $this->validate([
            'formName'        => 'required|string|max:160',
            'formPlanType'    => 'required|in:entrenamiento,nutricion,habitos,suplementacion,ciclo',
            'formMethodology' => 'nullable|string|max:255',
            'formDescription' => 'nullable|string|max:5000',
            'formContentJson' => 'required|string',
            'formCoachId'     => 'nullable|integer|exists:admins,id',
        ], [
            'formName.required'        => 'El nombre es obligatorio.',
            'formPlanType.required'    => 'Selecciona un tipo de plan.',
            'formContentJson.required' => 'El contenido JSON es obligatorio.',
        ]);

        // Validate JSON
        $decoded = json_decode($this->formContentJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('formContentJson', 'El contenido no es JSON valido: ' . json_last_error_msg());
            return;
        }

        $data = [
            'name'         => $this->formName,
            'plan_type'    => $this->formPlanType,
            'methodology'  => $this->formMethodology ?: null,
            'description'  => $this->formDescription ?: null,
            'content_json' => $decoded,
            'is_public'    => $this->formIsPublic,
            'coach_id'     => $this->formCoachId ?: null,
        ];

        if ($this->editingId) {
            $plan = PlanTemplate::findOrFail($this->editingId);
            $plan->update($data);
        } else {
            $data['ai_generated'] = false;
            PlanTemplate::create($data);
        }

        $this->closeForm();
    }

    // --- View ---

    public function openView(int $id): void
    {
        $this->viewingId    = $id;
        $this->showViewModal = true;
    }

    public function closeView(): void
    {
        $this->showViewModal = false;
        $this->viewingId     = null;
    }

    // --- Delete ---

    public function confirmDelete(int $id): void
    {
        $this->deletingId    = $id;
        $this->showDeleteModal = true;
    }

    public function closeDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    public function deletePlan(): void
    {
        if ($this->deletingId) {
            PlanTemplate::where('id', $this->deletingId)->delete();
        }
        $this->closeDelete();
    }

    public function render()
    {
        $query = PlanTemplate::query()
            ->with('coach')
            ->orderBy($this->sortBy, $this->sortDir);

        if ($this->typeFilter !== 'all') {
            $query->where('plan_type', $this->typeFilter);
        }

        if ($this->coachFilter !== 'all') {
            $query->where('coach_id', $this->coachFilter);
        }

        if ($this->publicFilter !== 'all') {
            $query->where('is_public', $this->publicFilter === 'yes');
        }

        if ($this->aiFilter !== 'all') {
            $query->where('ai_generated', $this->aiFilter === 'yes');
        }

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('methodology', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $plans = $query->paginate(20);

        $stats = [
            'total'          => PlanTemplate::count(),
            'entrenamiento'  => PlanTemplate::where('plan_type', 'entrenamiento')->count(),
            'nutricion'      => PlanTemplate::where('plan_type', 'nutricion')->count(),
            'habitos'        => PlanTemplate::where('plan_type', 'habitos')->count(),
            'suplementacion' => PlanTemplate::where('plan_type', 'suplementacion')->count(),
            'ciclo'          => PlanTemplate::where('plan_type', 'ciclo')->count(),
            'ai_generated'   => PlanTemplate::where('ai_generated', true)->count(),
        ];

        // Coaches for filter dropdown
        $coaches = Admin::whereIn('role', ['coach', 'superadmin', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name']);

        // Viewing plan
        $viewingPlan = $this->viewingId
            ? PlanTemplate::with('coach')->find($this->viewingId)
            : null;

        return view('livewire.admin.plan-management', [
            'plans'       => $plans,
            'stats'       => $stats,
            'coaches'     => $coaches,
            'viewingPlan' => $viewingPlan,
        ]);
    }
}
