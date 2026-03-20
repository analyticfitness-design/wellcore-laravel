<?php

namespace App\Livewire\Client;

use App\Models\Checkin;
use App\Models\Metric;
use App\Models\TrainingLog;
use Illuminate\Support\Facades\Cache;
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

        // --- Chart.js data (cached 5 minutes) ---
        $cacheKey = "metrics_charts_{$clientId}";

        $charts = Cache::remember($cacheKey, 300, function () use ($clientId) {
            // 1. Weight trend — last 90 days (Line chart)
            $weightTrend = Metric::where('client_id', $clientId)
                ->whereNotNull('peso')
                ->where('log_date', '>=', now()->subDays(90))
                ->orderBy('log_date')
                ->get(['log_date', 'peso'])
                ->map(fn ($m) => [
                    'date'  => $m->log_date->format('d/m'),
                    'value' => (float) $m->peso,
                ]);

            // 2. Weekly check-ins — last 12 weeks (Bar chart)
            $weeklyCheckins = Checkin::where('client_id', $clientId)
                ->where('checkin_date', '>=', now()->subWeeks(12))
                ->selectRaw('YEARWEEK(checkin_date, 1) as yw, COUNT(*) as cnt')
                ->groupBy('yw')
                ->orderBy('yw')
                ->get()
                ->map(fn ($r) => [
                    'week' => (string) $r->yw,
                    'cnt'  => (int) $r->cnt,
                ]);

            // 3. Body composition — latest entry with both values (Doughnut)
            $latestComposition = Metric::where('client_id', $clientId)
                ->whereNotNull('porcentaje_grasa')
                ->whereNotNull('porcentaje_musculo')
                ->orderByDesc('log_date')
                ->first(['porcentaje_grasa', 'porcentaje_musculo', 'log_date']);

            $composition = $latestComposition ? [
                'grasa'   => (float) $latestComposition->porcentaje_grasa,
                'musculo' => (float) $latestComposition->porcentaje_musculo,
                'otro'    => max(0, round(100 - (float) $latestComposition->porcentaje_grasa - (float) $latestComposition->porcentaje_musculo, 1)),
                'date'    => $latestComposition->log_date->format('d/m/Y'),
            ] : null;

            // 4. Training volume — last 12 weeks (Line chart)
            $trainingVolume = TrainingLog::where('client_id', $clientId)
                ->where('completed', true)
                ->where('log_date', '>=', now()->subWeeks(12))
                ->selectRaw('YEARWEEK(log_date, 1) as yw, COUNT(*) as sessions')
                ->groupBy('yw')
                ->orderBy('yw')
                ->get()
                ->map(fn ($r) => [
                    'week'     => (string) $r->yw,
                    'sessions' => (int) $r->sessions,
                ]);

            return compact('weightTrend', 'weeklyCheckins', 'composition', 'trainingVolume');
        });

        return view('livewire.client.metrics-tracker', [
            'history'         => $history,
            'chartData'       => $chartData,
            'currentWeight'   => $currentWeight,
            'weightChange'    => $weightChange,
            'weightTrend'     => $charts['weightTrend'],
            'weeklyCheckins'  => $charts['weeklyCheckins'],
            'latestComposition' => $charts['composition'],
            'trainingVolume'  => $charts['trainingVolume'],
        ]);
    }
}
