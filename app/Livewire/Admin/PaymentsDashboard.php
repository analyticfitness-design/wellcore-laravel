<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Pagos'])]
class PaymentsDashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    // Stats
    public string $totalRevenue = '0';
    public string $monthRevenue = '0';
    public int $pendingPayments = 0;
    public string $avgPerClient = '0';

    public function mount(): void
    {
        $this->loadStats();
    }

    protected function loadStats(): void
    {
        $this->totalRevenue = number_format(
            (float) Payment::where('status', 'approved')->sum('amount'),
            0, ',', '.'
        );

        $this->monthRevenue = number_format(
            (float) Payment::where('status', 'approved')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            0, ',', '.'
        );

        $this->pendingPayments = Payment::where('status', 'pending')->count();

        $activeClients = Client::where('status', 'activo')->count();
        $totalApproved = (float) Payment::where('status', 'approved')->sum('amount');

        $this->avgPerClient = $activeClients > 0
            ? number_format($totalApproved / $activeClients, 0, ',', '.')
            : '0';
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Payment::query()->with('client');

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $payments = $query->latest('created_at')->paginate(25);

        return view('livewire.admin.payments-dashboard', [
            'payments' => $payments,
        ]);
    }
}
