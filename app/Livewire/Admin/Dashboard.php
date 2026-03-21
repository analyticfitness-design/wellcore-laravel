<?php

namespace App\Livewire\Admin;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\Inscription;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    // Summary stats
    public int $activeClients = 0;
    public string $monthlyRevenue = '0';
    public int $pendingCheckins = 0;
    public int $newInscriptions = 0;

    // Client breakdown
    public int $clientsActivo = 0;
    public int $clientsInactivo = 0;
    public int $clientsPendiente = 0;
    public int $clientsSuspendido = 0;
    public int $totalClients = 0;

    // Recent activity
    public array $recentInscriptions = [];
    public array $recentPayments = [];

    public function mount(): void
    {
        $this->loadStats();
        $this->loadClientBreakdown();
        $this->loadRecentActivity();
    }

    protected function loadStats(): void
    {
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            return [
                'activeClients' => Client::where('status', 'activo')->count(),
                'monthlyRevenue' => number_format(
                    (float) Payment::where('status', 'approved')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('amount'),
                    0, ',', '.'
                ),
                'pendingCheckins' => Checkin::whereNull('coach_reply')->count(),
                'newInscriptions' => Inscription::where('created_at', '>=', now()->startOfMonth())->count(),
            ];
        });

        $this->activeClients = $stats['activeClients'];
        $this->monthlyRevenue = $stats['monthlyRevenue'];
        $this->pendingCheckins = $stats['pendingCheckins'];
        $this->newInscriptions = $stats['newInscriptions'];
    }

    protected function loadClientBreakdown(): void
    {
        $this->clientsActivo = Client::where('status', 'activo')->count();
        $this->clientsInactivo = Client::where('status', 'inactivo')->count();
        $this->clientsPendiente = Client::where('status', 'pendiente')->count();
        $this->clientsSuspendido = Client::where('status', 'suspendido')->count();
        $this->totalClients = Client::count();
    }

    protected function loadRecentActivity(): void
    {
        $this->recentInscriptions = Inscription::latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($i) => [
                'nombre' => trim(($i->nombre ?? '') . ' ' . ($i->apellido ?? '')),
                'email' => $i->email ?? '',
                'plan' => $i->plan?->label() ?? '-',
                'status' => $i->status ?? '-',
                'timeAgo' => $i->created_at?->diffForHumans() ?? '-',
            ])
            ->toArray();

        $this->recentPayments = Payment::where('status', 'approved')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'buyerName' => $p->buyer_name ?? $p->email ?? '-',
                'plan' => $p->plan?->label() ?? '-',
                'amount' => number_format((float) $p->amount, 0, ',', '.'),
                'method' => $p->payment_method ?? '-',
                'timeAgo' => $p->created_at?->diffForHumans() ?? '-',
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
