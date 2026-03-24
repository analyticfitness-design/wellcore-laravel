<?php

namespace App\Livewire\Admin;

use App\Enums\PlanType;
use App\Models\Invitation;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Invitaciones'])]
class InvitationManager extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $statusFilter = 'all';
    public string $sortBy       = 'created_at';
    public string $sortDir      = 'desc';

    // Create modal
    public bool $showCreateModal = false;
    public string $newPlan       = 'esencial';
    public string $newEmailHint  = '';
    public string $newNote       = '';
    public string $newExpiresAt  = '';

    // After creation: show the generated link
    public ?string $createdCode    = null;
    public ?string $createdIntakeUrl = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
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

    public function openCreateModal(): void
    {
        $this->reset(['newPlan', 'newEmailHint', 'newNote', 'newExpiresAt']);
        $this->newPlan = 'esencial';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->createdCode     = null;
        $this->createdIntakeUrl = null;
        $this->resetValidation();
    }

    public function createInvitation(): void
    {
        $this->validate([
            'newPlan'      => 'required|in:esencial,metodo,elite,presencial',
            'newEmailHint' => 'nullable|string|max:255',
            'newNote'      => 'nullable|string|max:500',
            'newExpiresAt' => 'nullable|date|after:today',
        ], [
            'newPlan.required'    => 'Selecciona un plan.',
            'newPlan.in'          => 'El plan seleccionado no es valido.',
            'newExpiresAt.after'  => 'La fecha de expiracion debe ser futura.',
        ]);

        // Generate unique 12-char uppercase code
        do {
            $code = strtoupper(Str::random(12));
        } while (Invitation::where('code', $code)->exists());

        Invitation::create([
            'code'       => $code,
            'plan'       => $this->newPlan,
            'email_hint' => $this->newEmailHint ?: null,
            'note'       => $this->newNote ?: null,
            'status'     => 'pending',
            'created_by' => auth('wellcore')->id(),
            'created_at' => now(),
            'expires_at' => $this->newExpiresAt ?: null,
        ]);

        // Store the link to display after creation
        $this->createdCode     = $code;
        $this->createdIntakeUrl = route('invite.intake', ['code' => $code]);

        $this->reset(['newPlan', 'newEmailHint', 'newNote', 'newExpiresAt']);
        $this->newPlan = 'esencial';
    }

    public function deleteInvitation(int $id): void
    {
        $invitation = Invitation::findOrFail($id);

        // Only allow deleting pending invitations
        $status = $invitation->status instanceof PlanType
            ? $invitation->status
            : $invitation->getRawOriginal('status');

        if ($status === 'pending') {
            $invitation->delete();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Invitation::query()
            ->with(['createdBy', 'usedBy'])
            ->orderBy($this->sortBy, $this->sortDir);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                  ->orWhere('email_hint', 'like', "%{$s}%");
            });
        }

        $invitations = $query->paginate(20);

        $stats = [
            'total'   => Invitation::count(),
            'pending' => Invitation::where('status', 'pending')->count(),
            'used'    => Invitation::where('status', 'used')->count(),
            'expired' => Invitation::where('status', 'expired')->count(),
        ];

        return view('livewire.admin.invitation-manager', [
            'invitations' => $invitations,
            'stats'       => $stats,
            'planCases'   => [
                PlanType::Esencial,
                PlanType::Metodo,
                PlanType::Elite,
                PlanType::Presencial,
            ],
        ]);
    }
}
