<?php

namespace App\Livewire\Admin;

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

        return view('livewire.admin.client-table', [
            'clients' => $clients,
        ]);
    }
}
