<div class="space-y-6">
    {{-- Title --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">METRICAS CORPORALES</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Registra y monitorea tu composición corporal</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
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

    {{-- Success overlay handled by fullscreen achievement modal below --}}

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
                            <th class="px-3 py-3 font-medium sm:px-5">Fecha</th>
                            <th class="px-3 py-3 font-medium sm:px-5">Peso</th>
                            <th class="px-3 py-3 font-medium sm:px-5">Musc%</th>
                            <th class="px-3 py-3 font-medium sm:px-5">Grasa%</th>
                            <th class="hidden px-3 py-3 font-medium sm:table-cell sm:px-5">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach ($history as $entry)
                            <tr class="hover:bg-wc-bg-secondary/50">
                                <td class="whitespace-nowrap px-3 py-3 font-data text-wc-text sm:px-5">
                                    {{ $entry->log_date->format('d/m/Y') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-data font-semibold text-wc-text sm:px-5">
                                    {{ $entry->peso ? number_format((float) $entry->peso, 1) . ' kg' : '--' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-data text-wc-text sm:px-5">
                                    {{ $entry->porcentaje_musculo ? number_format((float) $entry->porcentaje_musculo, 1) . '%' : '--' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-data text-wc-text sm:px-5">
                                    {{ $entry->porcentaje_grasa ? number_format((float) $entry->porcentaje_grasa, 1) . '%' : '--' }}
                                </td>
                                <td class="hidden max-w-[200px] truncate px-3 py-3 text-wc-text-secondary sm:table-cell sm:px-5"
                                    title="{{ $entry->notas ?? '' }}">
                                    {{ $entry->notas ?? '--' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ===== ACHIEVEMENT OVERLAY: MÉTRICAS ===== --}}
    <style>
        @keyframes wc-confetti-fall {
            0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }
        .wc-confetti { position: absolute; top: -10px; width: 10px; height: 10px; }
        @keyframes wc-emoji-bounce {
            0%, 100% { transform: scale(1) rotate(-3deg); }
            50%       { transform: scale(1.15) rotate(3deg); }
        }
        .wc-emoji-bounce { animation: wc-emoji-bounce 2s ease-in-out infinite; display: inline-block; }
    </style>
    <div
        x-data="{
            show: @entangle('showSuccess'),
            confetti: false,
            init() {
                this.$watch('show', v => { if (v) { this.confetti = true; setTimeout(() => this.confetti = false, 4000); } });
            }
        }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background: rgba(0,0,0,0.85);"
        @keydown.escape.window="$wire.dismissSuccess()"
        x-cloak
    >
        {{-- Confetti --}}
        <div x-show="confetti" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="wc-confetti" style="left:8%;background:#DC2626;animation:wc-confetti-fall 2.8s ease-in forwards 0.1s;"></div>
            <div class="wc-confetti" style="left:22%;background:#F59E0B;animation:wc-confetti-fall 3.2s ease-in forwards 0.3s;border-radius:50%;"></div>
            <div class="wc-confetti" style="left:38%;background:#10B981;animation:wc-confetti-fall 2.5s ease-in forwards 0s;"></div>
            <div class="wc-confetti" style="left:52%;background:#DC2626;animation:wc-confetti-fall 3s ease-in forwards 0.5s;border-radius:50%;"></div>
            <div class="wc-confetti" style="left:65%;background:#8B5CF6;animation:wc-confetti-fall 2.7s ease-in forwards 0.2s;"></div>
            <div class="wc-confetti" style="left:78%;background:#F59E0B;animation:wc-confetti-fall 3.4s ease-in forwards 0.4s;border-radius:50%;"></div>
            <div class="wc-confetti" style="left:90%;background:#10B981;animation:wc-confetti-fall 2.6s ease-in forwards 0.15s;"></div>
            <div class="wc-confetti" style="left:45%;background:#8B5CF6;animation:wc-confetti-fall 3.1s ease-in forwards 0.6s;"></div>
        </div>

        {{-- Card --}}
        <div
            class="relative w-full max-w-sm overflow-hidden rounded-2xl text-center"
            style="background: linear-gradient(160deg, #0C1015 0%, #131F2B 50%, #0C1015 100%);"
            x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            role="dialog"
            aria-modal="true"
            aria-labelledby="metrics-success-title"
        >
            <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% -5%, rgba(255,255,255,0.08) 0%, transparent 60%);" aria-hidden="true"></div>

            <div class="relative z-10 p-8">
                <span class="wc-emoji-bounce block text-6xl mb-4" aria-hidden="true">📊</span>

                <div class="mb-3 flex items-center justify-center gap-2">
                    <span class="font-display text-xl tracking-[0.25em] text-white/90">WELLCORE</span>
                    <span class="h-2 w-2 rounded-full bg-white/30" aria-hidden="true"></span>
                </div>

                <h2 id="metrics-success-title" class="font-sans text-2xl font-bold text-white mb-2">¡Métricas guardadas!</h2>

                @if ($lastPeso !== '' && (float) $lastPeso > 0)
                    <div class="my-5 rounded-xl border border-white/10 bg-white/[0.06] px-5 py-4">
                        <p class="font-data text-3xl font-bold text-white">{{ number_format((float) $lastPeso, 1) }} <span class="text-lg font-normal text-white/50">kg</span></p>
                        <p class="mt-0.5 text-xs text-white/50">peso registrado</p>
                    </div>
                @else
                    <div class="my-5"></div>
                @endif

                <p class="mb-6 text-sm text-white/70">El seguimiento consistente es la base del progreso.</p>

                <button
                    wire:click="dismissSuccess"
                    class="w-full rounded-xl bg-wc-accent px-6 py-3 font-display text-lg tracking-wider text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-black"
                >
                    ¡PERFECTO!
                </button>
            </div>
        </div>
    </div>
    {{-- ===== /ACHIEVEMENT OVERLAY: MÉTRICAS ===== --}}

    {{-- ===== ONBOARDING TUTORIAL: MÉTRICAS ===== --}}
    @if($showTutorial)
    <div
        x-data="{ step: 1, total: 3 }"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
        @keydown.escape.window="$wire.dismissTutorial()"
    >
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-lg tracking-widest text-wc-text">TUS MÉTRICAS</h3>
                <button @click="$wire.dismissTutorial()" class="text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="step === 1">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Registra tu peso</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Pésate en ayunas, después de ir al baño y antes de desayunar. Siempre a la misma hora para tener datos comparables semana a semana.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 2">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Las fluctuaciones son normales</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">El peso puede variar 1-3 kg en un día por agua, comida y sal. Lo que importa es la tendencia de semanas, no el número de un día específico.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 3">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">El peso no es todo</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">La escala no distingue músculo de grasa. Registra también tus medidas y fotos de progreso — la transformación visual siempre supera a los números.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-center gap-1.5">
                <template x-for="i in total" :key="i">
                    <div class="h-1.5 rounded-full transition-all" :class="i === step ? 'bg-wc-accent w-4' : 'bg-wc-bg-tertiary w-1.5'"></div>
                </template>
            </div>

            <div class="mt-5 flex gap-3">
                <button x-show="step > 1" @click="step--" class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors" type="button">Atrás</button>
                <button x-show="step < total" @click="step++" class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors" type="button">Siguiente</button>
                <button x-show="step === total" @click="$wire.dismissTutorial()" class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors" type="button">¡Entendido!</button>
            </div>
        </div>
    </div>
    @endif
    {{-- ===== /ONBOARDING TUTORIAL: MÉTRICAS ===== --}}
</div>
