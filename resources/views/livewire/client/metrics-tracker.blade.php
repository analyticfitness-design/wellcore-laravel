<div class="space-y-6">
    {{-- Title --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">METRICAS CORPORALES</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Registra y monitorea tu composición corporal</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        {{-- Current Weight --}}
        <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Peso actual</p>
            <p class="mt-1 font-data text-3xl font-semibold text-wc-text">
                {{ $currentWeight ? number_format((float) $currentWeight, 1) : '--' }}
                <span class="text-base font-normal text-wc-text-tertiary">kg</span>
            </p>
        </div>

        {{-- Monthly Change --}}
        <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Cambio mensual</p>
            <p class="mt-1 font-data text-3xl font-semibold {{ $weightChange !== null ? ($weightChange > 0 ? 'text-amber-500' : ($weightChange < 0 ? 'text-emerald-500' : 'text-wc-text')) : 'text-wc-text' }}">
                @if ($weightChange !== null)
                    {{ $weightChange > 0 ? '+' : '' }}{{ number_format($weightChange, 1) }}
                    <span class="text-base font-normal text-wc-text-tertiary">kg</span>
                @else
                    --
                @endif
            </p>
        </div>

        {{-- Goal Placeholder --}}
        <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Objetivo</p>
            <p class="mt-1 font-data text-3xl font-semibold text-wc-text-tertiary">--
                <span class="text-base font-normal">kg</span>
            </p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Consulta con tu coach</p>
        </div>
    </div>

    {{-- Success Message --}}
    @if ($showSuccess)
        <div class="flex items-center justify-between rounded-[--radius-card] border border-emerald-500/30 bg-emerald-500/10 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm font-medium text-emerald-400">Métrica registrada correctamente.</span>
            </div>
            <button wire:click="dismissSuccess" class="text-wc-text-tertiary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Log Form --}}
    <form wire:submit="saveMetric" class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Nuevo registro</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Peso --}}
            <div>
                <label for="peso" class="mb-1 block text-sm font-medium text-wc-text">Peso (kg) <span class="text-wc-accent">*</span></label>
                <input
                    type="number"
                    id="peso"
                    wire:model="peso"
                    step="0.1"
                    min="20"
                    max="300"
                    placeholder="75.0"
                    class="w-full rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                @error('peso')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- % Musculo --}}
            <div>
                <label for="porcentajeMusculo" class="mb-1 block text-sm font-medium text-wc-text">% Musculo</label>
                <input
                    type="number"
                    id="porcentajeMusculo"
                    wire:model="porcentajeMusculo"
                    step="0.1"
                    min="0"
                    max="100"
                    placeholder="40.0"
                    class="w-full rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                @error('porcentajeMusculo')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- % Grasa --}}
            <div>
                <label for="porcentajeGrasa" class="mb-1 block text-sm font-medium text-wc-text">% Grasa</label>
                <input
                    type="number"
                    id="porcentajeGrasa"
                    wire:model="porcentajeGrasa"
                    step="0.1"
                    min="0"
                    max="100"
                    placeholder="18.0"
                    class="w-full rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                @error('porcentajeGrasa')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notas --}}
            <div>
                <label for="notas" class="mb-1 block text-sm font-medium text-wc-text">Notas</label>
                <input
                    type="text"
                    id="notas"
                    wire:model="notas"
                    placeholder="En ayunas, post-entrenamiento..."
                    class="w-full rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                @error('notas')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-5">
            <button
                type="submit"
                class="btn-press rounded-[--radius-button] bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg disabled:opacity-50"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="saveMetric">Guardar registro</span>
                <span wire:loading wire:target="saveMetric" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Guardando...
                </span>
            </button>
        </div>
    </form>

    {{-- Weight Chart (CSS bar chart) --}}
    @if ($chartData->isNotEmpty())
        <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Tendencia de peso</h2>

            @php
                $weights = $chartData->pluck('peso')->map(fn ($w) => (float) $w);
                $maxWeight = $weights->max();
                $minWeight = $weights->min();
                $range = $maxWeight - $minWeight;
                if ($range === 0.0) $range = 1;
            @endphp

            <div class="flex items-end gap-1 sm:gap-2" style="height: 120px;">
                @foreach ($chartData as $entry)
                    @php
                        $pct = (((float) $entry->peso - $minWeight) / $range) * 70 + 30;
                    @endphp
                    <div class="group relative flex flex-1 flex-col items-center justify-end" style="height: 100%;">
                        {{-- Tooltip --}}
                        <div class="pointer-events-none absolute -top-8 z-10 hidden rounded bg-wc-bg-secondary px-2 py-1 text-xs font-medium text-wc-text shadow-lg group-hover:block">
                            {{ number_format((float) $entry->peso, 1) }} kg
                        </div>
                        {{-- Bar --}}
                        <div
                            class="w-full rounded-t bg-wc-accent/80 transition-all group-hover:bg-wc-accent"
                            style="height: {{ $pct }}%;"
                        ></div>
                        {{-- Label --}}
                        <span class="mt-1 text-[10px] text-wc-text-tertiary">
                            {{ $entry->log_date->format('d/m') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-2 flex justify-between text-xs text-wc-text-tertiary">
                <span>Min: {{ number_format($minWeight, 1) }} kg</span>
                <span>Max: {{ number_format($maxWeight, 1) }} kg</span>
            </div>
        </div>
    @endif

    {{-- Chart.js Charts Section --}}
    <div
        class="grid grid-cols-1 gap-4 lg:grid-cols-2"
        x-data="metricsCharts()"
        x-init="initCharts()"
        data-weight-trend='@json($weightTrend)'
        data-weekly-checkins='@json($weeklyCheckins)'
        data-composition='@json($latestComposition)'
        data-training-volume='@json($trainingVolume)'
    >
        {{-- 1. Weight Trend (Line) --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Peso Corporal</h3>
            <p class="text-xs text-wc-text-secondary">Últimos 90 días</p>
            <div class="relative mt-4" style="height:180px">
                <canvas x-ref="weightChart"></canvas>
                <p x-show="!hasWeight" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                    Sin datos de peso aún
                </p>
            </div>
        </div>

        {{-- 2. Weekly Check-ins (Bar) --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Check-ins Semanales</h3>
            <p class="text-xs text-wc-text-secondary">Ultimas 12 semanas</p>
            <div class="relative mt-4" style="height:180px">
                <canvas x-ref="checkinChart"></canvas>
                <p x-show="!hasCheckins" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                    Sin check-ins recientes
                </p>
            </div>
        </div>

        {{-- 3. Body Composition (Doughnut) --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Composición Corporal</h3>
            <p class="text-xs text-wc-text-secondary">Ultima medicion</p>
            <div class="relative mt-4 mx-auto flex items-center justify-center" style="height:180px;max-width:260px">
                <canvas x-ref="compositionChart"></canvas>
                <p x-show="!hasComposition" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                    Sin datos de composición
                </p>
            </div>
        </div>

        {{-- 4. Training Volume (Line) --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Volumen de Entrenamiento</h3>
            <p class="text-xs text-wc-text-secondary">Sesiones por semana</p>
            <div class="relative mt-4" style="height:180px">
                <canvas x-ref="trainingChart"></canvas>
                <p x-show="!hasTraining" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                    Sin sesiones registradas
                </p>
            </div>
        </div>
    </div>

    <script>
    function metricsCharts() {
        return {
            hasWeight: false,
            hasCheckins: false,
            hasComposition: false,
            hasTraining: false,

            initCharts() {
                if (typeof Chart === 'undefined') return;

                // WellCore global defaults
                Chart.defaults.color = '#a3a3a3';
                Chart.defaults.borderColor = '#262626';
                Chart.defaults.font.family = "'Barlow', sans-serif";
                Chart.defaults.font.size = 11;

                this.createWeightChart();
                this.createCheckinChart();
                this.createCompositionChart();
                this.createTrainingChart();
            },

            createWeightChart() {
                const raw = this.$el.dataset.weightTrend;
                const data = raw ? JSON.parse(raw) : [];
                this.hasWeight = data.length > 0;
                if (!this.hasWeight) return;

                Chart.getChart(this.$refs.weightChart)?.destroy();
                new Chart(this.$refs.weightChart, {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [{
                            label: 'Peso (kg)',
                            data: data.map(d => d.value),
                            borderColor: '#DC2626',
                            backgroundColor: 'rgba(220,38,38,0.08)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#DC2626',
                            pointBorderColor: '#DC2626',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.parsed.y} kg`
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
                            y: { beginAtZero: false, grid: { color: '#262626' } }
                        }
                    }
                });
            },

            createCheckinChart() {
                const raw = this.$el.dataset.weeklyCheckins;
                const data = raw ? JSON.parse(raw) : [];
                this.hasCheckins = data.length > 0;
                if (!this.hasCheckins) return;

                // Convert YEARWEEK codes to readable labels (e.g. 202601 → "S1")
                const labels = data.map((d, i) => {
                    const yw = String(d.week);
                    const week = parseInt(yw.slice(4), 10);
                    return `S${week}`;
                });

                Chart.getChart(this.$refs.checkinChart)?.destroy();
                new Chart(this.$refs.checkinChart, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Check-ins',
                            data: data.map(d => d.cnt),
                            backgroundColor: 'rgba(220,38,38,0.55)',
                            borderColor: '#DC2626',
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, grid: { color: '#262626' }, ticks: { stepSize: 1 } }
                        }
                    }
                });
            },

            createCompositionChart() {
                const raw = this.$el.dataset.composition;
                const comp = raw && raw !== 'null' ? JSON.parse(raw) : null;
                this.hasComposition = !!comp;
                if (!this.hasComposition) return;

                Chart.getChart(this.$refs.compositionChart)?.destroy();
                new Chart(this.$refs.compositionChart, {
                    type: 'doughnut',
                    data: {
                        labels: ['Grasa', 'Musculo', 'Otro'],
                        datasets: [{
                            data: [comp.grasa, comp.musculo, comp.otro],
                            backgroundColor: ['#DC2626', '#3B82F6', '#525252'],
                            borderColor: '#171717',
                            borderWidth: 2,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: { padding: 12, boxWidth: 10 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.label}: ${ctx.parsed}%`
                                }
                            }
                        },
                        cutout: '65%',
                    }
                });
            },

            createTrainingChart() {
                const raw = this.$el.dataset.trainingVolume;
                const data = raw ? JSON.parse(raw) : [];
                this.hasTraining = data.length > 0;
                if (!this.hasTraining) return;

                const labels = data.map(d => {
                    const yw = String(d.week);
                    const week = parseInt(yw.slice(4), 10);
                    return `S${week}`;
                });

                Chart.getChart(this.$refs.trainingChart)?.destroy();
                new Chart(this.$refs.trainingChart, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Sesiones',
                            data: data.map(d => d.sessions),
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#3B82F6',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#262626' },
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            },
        };
    }
    </script>

    {{-- History Table --}}
    @if ($history->isNotEmpty())
        <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary">
            <div class="border-b border-wc-border px-5 py-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Historial</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-wc-border text-left text-xs uppercase tracking-wider text-wc-text-tertiary">
                            <th class="px-5 py-3 font-medium">Fecha</th>
                            <th class="px-5 py-3 font-medium">Peso</th>
                            <th class="px-5 py-3 font-medium">Musculo%</th>
                            <th class="px-5 py-3 font-medium">Grasa%</th>
                            <th class="px-5 py-3 font-medium">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach ($history as $entry)
                            <tr class="hover:bg-wc-bg-secondary/50">
                                <td class="whitespace-nowrap px-5 py-3 font-data text-wc-text">
                                    {{ $entry->log_date->format('d/m/Y') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 font-data font-semibold text-wc-text">
                                    {{ $entry->peso ? number_format((float) $entry->peso, 1) . ' kg' : '--' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 font-data text-wc-text">
                                    {{ $entry->porcentaje_musculo ? number_format((float) $entry->porcentaje_musculo, 1) . '%' : '--' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 font-data text-wc-text">
                                    {{ $entry->porcentaje_grasa ? number_format((float) $entry->porcentaje_grasa, 1) . '%' : '--' }}
                                </td>
                                <td class="max-w-[200px] truncate px-5 py-3 text-wc-text-secondary">
                                    {{ $entry->notas ?? '--' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
