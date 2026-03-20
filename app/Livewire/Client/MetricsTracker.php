<?php

namespace App\Livewire\Client;

use App\Models\Metric;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Metricas Corporales — WellCore'])]
class MetricsTracker extends Component
{
    #[Validate('required|numeric|min:20|max:300')]
    public string $peso = '';

    #[Validate('nullable|numeric|min:0|max:100')]
    public string $porcentajeMusculo = '';

    #[Validate('nullable|numeric|min:0|max:100')]
    public string $porcentajeGrasa = '';

    #[Validate('nullable|string|max:500')]
    public string $notas = '';

    public bool $showSuccess = false;

    public function saveMetric(): void
    {
        $this->validate();

        $clientId = auth('wellcore')->id();

        Metric::create([
            'client_id' => $clientId,
            'log_date' => now()->toDateString(),
            'peso' => $this->peso,
            'porcentaje_musculo' => $this->porcentajeMusculo !== '' ? $this->porcentajeMusculo : null,
            'porcentaje_grasa' => $this->porcentajeGrasa !== '' ? $this->porcentajeGrasa : null,
            'notas' => $this->notas !== '' ? $this->notas : null,
        ]);

        $this->reset(['peso', 'porcentajeMusculo', 'porcentajeGrasa', 'notas']);
        $this->showSuccess = true;
    }

    public function dismissSuccess(): void
    {
        $this->showSuccess = false;
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        $history = Metric::where('client_id', $clientId)
            ->orderByDesc('log_date')
            ->limit(20)
            ->get();

        // Last 10 weight entries for the chart (reversed so oldest is first)
        $chartData = Metric::where('client_id', $clientId)
            ->whereNotNull('peso')
            ->orderByDesc('log_date')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        // Stats
        $currentWeight = $history->first()?->peso;
        $monthAgoWeight = Metric::where('client_id', $clientId)
            ->whereNotNull('peso')
            ->where('log_date', '<=', now()->subMonth()->toDateString())
            ->orderByDesc('log_date')
            ->first()?->peso;

        $weightChange = ($currentWeight && $monthAgoWeight)
            ? round((float) $currentWeight - (float) $monthAgoWeight, 2)
            : null;

        return view('livewire.client.metrics-tracker', [
            'history' => $history,
            'chartData' => $chartData,
            'currentWeight' => $currentWeight,
            'weightChange' => $weightChange,
        ]);
    }
}
