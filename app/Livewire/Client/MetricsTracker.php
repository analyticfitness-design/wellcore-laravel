<?php

namespace App\Livewire\Client;

use App\Models\BiometricLog;
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

        $logDate = now()->toDateString();

        Metric::updateOrCreate(
            ['client_id' => $clientId, 'log_date' => $logDate],
            [
                'peso' => $this->peso,
                'porcentaje_musculo' => $this->porcentajeMusculo !== '' ? $this->porcentajeMusculo : null,
                'porcentaje_grasa' => $this->porcentajeGrasa !== '' ? $this->porcentajeGrasa : null,
                'notas' => $this->notas !== '' ? $this->notas : null,
            ]
        );

        // Sync weight to biometric_logs so Dashboard reads the same value
        if ((float) $this->peso > 0) {
            BiometricLog::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'log_date'  => $logDate,
                ],
                ['weight_kg' => (float) $this->peso]
            );
        }

        // Invalidate cached render data so next render reflects the new entry
        Cache::forget("metrics_charts_{$clientId}");
        Cache::forget("metrics_render_{$clientId}");

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

        // All render data cached together for 5 minutes (TTL 300s).
        // Cache is busted immediately in saveMetric() on every write.
        $renderKey = "metrics_render_{$clientId}";

        $renderData = Cache::remember($renderKey, 300, function () use ($clientId) {
            // Recent history table — last 20 entries
            $history = Metric::where('client_id', $clientId)
                ->orderByDesc('log_date')
                ->limit(20)
                ->get();

            // Last 10 weight entries for the inline mini-chart (oldest first)
            $chartData = Metric::where('client_id', $clientId)
                ->whereNotNull('peso')
                ->orderByDesc('log_date')
                ->limit(10)
                ->get()
                ->reverse()
                ->values();

            // Stats: weight one month ago for the delta indicator
            $currentWeight  = $history->first()?->peso;
            $monthAgoWeight = Metric::where('client_id', $clientId)
                ->whereNotNull('peso')
                ->where('log_date', '<=', now()->subMonth()->toDateString())
                ->orderByDesc('log_date')
                ->value('peso');

            $weightChange = ($currentWeight && $monthAgoWeight)
                ? round((float) $currentWeight - (float) $monthAgoWeight, 2)
                : null;

            return compact('history', 'chartData', 'currentWeight', 'weightChange');
        });

        // Chart.js data has a separate cache key so it can be invalidated independently
        $chartsKey = "metrics_charts_{$clientId}";

        $charts = Cache::remember($chartsKey, 300, function () use ($clientId) {
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
            'history'           => $renderData['history'],
            'chartData'         => $renderData['chartData'],
            'currentWeight'     => $renderData['currentWeight'],
            'weightChange'      => $renderData['weightChange'],
            'weightTrend'       => $charts['weightTrend'],
            'weeklyCheckins'    => $charts['weeklyCheckins'],
            'latestComposition' => $charts['composition'],
            'trainingVolume'    => $charts['trainingVolume'],
        ]);
    }
}
