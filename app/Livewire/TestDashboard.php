<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Checkin;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TestDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $planFilter = '';
    public string $statusFilter = '';

    // Stats
    public int $totalClients = 0;
    public int $activeClients = 0;
    public int $totalAdmins = 0;
    public int $totalPayments = 0;
    public int $totalCheckins = 0;
    public string $dbVersion = '';
    public int $tableCount = 0;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->totalClients = Client::count();
        $this->activeClients = Client::where('status', 'activo')->count();
        $this->totalAdmins = Admin::count();
        $this->totalPayments = Payment::count();
        $this->totalCheckins = Checkin::count();
        $this->dbVersion = DB::selectOne('SELECT VERSION() as v')->v ?? 'N/A';
        $this->tableCount = count(DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'wellcore_fitness'"));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPlanFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('client_code', 'like', "%{$this->search}%");
            }))
            ->when($this->planFilter, fn ($q) => $q->where('plan', $this->planFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        $plans = DB::table('clients')->select('plan')->distinct()->pluck('plan')->filter();
        $statuses = DB::table('clients')->select('status')->distinct()->pluck('status')->filter();

        return view('livewire.test-dashboard', [
            'clients' => $clients,
            'plans' => $plans,
            'statuses' => $statuses,
        ])->layout('layouts.app', ['title' => 'Test Dashboard — WellCore']);
    }
}
