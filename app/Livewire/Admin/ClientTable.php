<?php

namespace App\Livewire\Admin;

use App\Enums\ClientStatus;
use App\Enums\UserRole;
use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Clientes'])]
class ClientTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $planFilter = '';

    #[Url]
    public string $statusFilter = '';

    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';

    // Deactivate confirmation state
    public bool    $showDeactivateModal  = false;
    public ?int    $deactivateClientId   = null;
    public string  $deactivateClientName = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPlanFilter(): void
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
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->planFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    // --- Deactivate client (superadmin only) ---

    public function confirmDeactivate(int $clientId): void
    {
        $admin = auth('wellcore')->user();

        if (! $admin || $admin->role !== UserRole::Superadmin) {
            $this->dispatch('toast', type: 'error', message: 'No tienes permisos para realizar esta accion.');
            return;
        }

        $client = Client::find($clientId);

        if (! $client) {
            $this->dispatch('toast', type: 'error', message: 'Cliente no encontrado.');
            return;
        }

        $this->deactivateClientId   = $clientId;
        $this->deactivateClientName = $client->name ?? 'Cliente';
        $this->showDeactivateModal  = true;
    }

    public function cancelDeactivate(): void
    {
        $this->showDeactivateModal  = false;
        $this->deactivateClientId   = null;
        $this->deactivateClientName = '';
    }

    public function deactivateClient(): void
    {
        $admin = auth('wellcore')->user();

        if (! $admin || $admin->role !== UserRole::Superadmin) {
            $this->dispatch('toast', type: 'error', message: 'No tienes permisos para realizar esta accion.');
            $this->cancelDeactivate();
            return;
        }

        if (! $this->deactivateClientId) {
            $this->cancelDeactivate();
            return;
        }

        $client = Client::find($this->deactivateClientId);

        if (! $client) {
            $this->dispatch('toast', type: 'error', message: 'Cliente no encontrado.');
            $this->cancelDeactivate();
            return;
        }

        $name = $client->name ?? 'Cliente';

        $client->update(['status' => ClientStatus::Inactivo->value]);

        $this->cancelDeactivate();

        $this->dispatch('toast', type: 'success', message: "Cliente \"{$name}\" marcado como inactivo.");
    }

    public function render()
    {
        $query = Client::query();

        // Search
        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('client_code', 'like', "%{$search}%");
            });
        }

        // Plan filter
        if ($this->planFilter !== '') {
            $query->where('plan', $this->planFilter);
        }

        // Status filter
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        // Sorting
        $query->orderBy($this->sortBy, $this->sortDir);

        $clients = $query->paginate(25);

        $isSuperadmin = auth('wellcore')->check()
            && auth('wellcore')->user()->role === UserRole::Superadmin;

        return view('livewire.admin.client-table', [
            'clients'      => $clients,
            'isSuperadmin' => $isSuperadmin,
        ]);
    }
}
