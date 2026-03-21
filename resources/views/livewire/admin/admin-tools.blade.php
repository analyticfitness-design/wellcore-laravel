<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">HERRAMIENTAS ADMIN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Revenue, logs del sistema y estado de salud</p>
    </div>

    {{-- CSV Export Panel --}}
    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Exportar Datos (CSV)</h3>
        <p class="mb-4 text-xs text-wc-text-tertiary">Descarga datos en formato CSV compatible con Excel. Los archivos incluyen todos los registros.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.export.clients') }}"
               class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-bg-secondary px-4 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary border border-wc-border transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar Clientes
            </a>
            <a href="{{ route('admin.export.payments') }}"
               class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-bg-secondary px-4 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary border border-wc-border transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar Pagos
            </a>
            <a href="{{ route('admin.export.checkins') }}"
               class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-bg-secondary px-4 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary border border-wc-border transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar Check-ins
            </a>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 rounded-[--radius-card] border border-wc-border bg-wc-bg-secondary p-1">
        <button
            wire:click="setTab('revenue')"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors
                   {{ $tab === 'revenue' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            Revenue Charts
        </button>
        <button
            wire:click="setTab('logs')"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors
                   {{ $tab === 'logs' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            Logs
        </button>
        <button
            wire:click="setTab('health')"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors
                   {{ $tab === 'health' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
            Health Check
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         REVENUE TAB
         ═══════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'revenue')
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">MRR (30 dias)</p>
                <p class="mt-1 font-data text-3xl font-semibold text-wc-text">${{ $mrr }}</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">COP</p>
            </div>
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total este mes</p>
                <p class="mt-1 font-data text-3xl font-semibold text-emerald-500">${{ $monthTotal }}</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">COP</p>
            </div>
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total este ano</p>
                <p class="mt-1 font-data text-3xl font-semibold text-sky-500">${{ $yearTotal }}</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">COP</p>
            </div>
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pagos aprobados</p>
                <p class="mt-1 font-data text-3xl font-semibold text-wc-text">{{ $approvedCount }}</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Este mes</p>
            </div>
        </div>

        {{-- Charts --}}
        <div
            x-data="revenueCharts()"
            x-init="initCharts()"
            data-monthly='@json($monthlyRevenue)'
            data-plans='@json($planBreakdown)'
            data-methods='@json($methodBreakdown)'
            data-statuses='@json($statusDistribution)'
            class="space-y-4"
        >
            {{-- Monthly Revenue Bar Chart --}}
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Revenue Mensual (Ultimos 12 meses)</h3>
                <div class="relative h-72">
                    <canvas x-ref="monthlyChart"></canvas>
                </div>
            </div>

            {{-- Plan Breakdown + Payment Methods side by side --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                {{-- Doughnut: plan breakdown --}}
                <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Revenue por Plan</h3>
                    <div class="relative mx-auto h-64 max-w-xs">
                        <canvas x-ref="planChart"></canvas>
                    </div>
                    <div class="mt-4 space-y-2">
                        @foreach ($planBreakdown as $pb)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-wc-text-secondary">{{ $pb['plan'] }}</span>
                                <span class="font-data font-semibold text-wc-text">${{ number_format($pb['total'], 0, ',', '.') }} <span class="text-xs font-normal text-wc-text-tertiary">({{ $pb['count'] }})</span></span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Horizontal Bar: payment methods --}}
                <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Metodos de Pago</h3>
                    <div class="relative h-64">
                        <canvas x-ref="methodChart"></canvas>
                    </div>
                    <div class="mt-4 space-y-2">
                        @foreach ($methodBreakdown as $mb)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-wc-text-secondary">{{ $mb['method'] }}</span>
                                <span class="font-data font-semibold text-wc-text">${{ number_format($mb['total'], 0, ',', '.') }} <span class="text-xs font-normal text-wc-text-tertiary">({{ $mb['count'] }})</span></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Payment Status Distribution --}}
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Distribucion de Estado de Pagos</h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($statusDistribution as $sd)
                        <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-center">
                            <p class="font-data text-2xl font-semibold text-wc-text">{{ $sd['count'] }}</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">{{ $sd['status'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Monthly Breakdown Table --}}
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Desglose Mensual</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-wc-border text-left">
                                <th class="px-3 py-2 font-medium text-wc-text-tertiary">Mes</th>
                                <th class="px-3 py-2 font-medium text-wc-text-tertiary text-right">Revenue</th>
                                <th class="px-3 py-2 font-medium text-wc-text-tertiary text-right">Pagos</th>
                                <th class="px-3 py-2 font-medium text-wc-text-tertiary text-right">Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($monthlyRevenue as $mr)
                                <tr class="border-b border-wc-border/50 hover:bg-wc-bg-secondary/50">
                                    <td class="px-3 py-2 text-wc-text">{{ $mr['label'] }}</td>
                                    <td class="px-3 py-2 text-right font-data font-semibold text-wc-text">${{ number_format($mr['total'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-data text-wc-text-secondary">{{ $mr['count'] }}</td>
                                    <td class="px-3 py-2 text-right font-data text-wc-text-secondary">
                                        ${{ $mr['count'] > 0 ? number_format($mr['total'] / $mr['count'], 0, ',', '.') : '0' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-8 text-center text-wc-text-tertiary">Sin datos de revenue</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Chart.js Initialization Script --}}
            <template x-if="false">
                <div></div>
            </template>
        </div>

        <script>
            function revenueCharts() {
                return {
                    charts: {},

                    initCharts() {
                        this.$nextTick(() => {
                            const isDark = document.documentElement.classList.contains('dark');
                            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
                            const textColor = isDark ? 'rgba(255,255,255,0.5)' : 'rgba(0,0,0,0.5)';

                            this.initMonthlyChart(gridColor, textColor);
                            this.initPlanChart(textColor);
                            this.initMethodChart(gridColor, textColor);
                        });
                    },

                    initMonthlyChart(gridColor, textColor) {
                        const raw = this.$el.dataset.monthly;
                        const data = raw ? JSON.parse(raw) : [];
                        if (!data.length || !this.$refs.monthlyChart) return;

                        this.charts.monthly = new Chart(this.$refs.monthlyChart, {
                            type: 'bar',
                            data: {
                                labels: data.map(d => d.label),
                                datasets: [{
                                    label: 'Revenue (COP)',
                                    data: data.map(d => d.total),
                                    backgroundColor: 'rgba(220, 38, 38, 0.7)',
                                    borderColor: 'rgba(220, 38, 38, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: ctx => '$' + new Intl.NumberFormat('es-CO').format(ctx.raw) + ' COP'
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            callback: v => '$' + new Intl.NumberFormat('es-CO', { notation: 'compact' }).format(v)
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { color: textColor, maxRotation: 45 }
                                    }
                                }
                            }
                        });
                    },

                    initPlanChart(textColor) {
                        const raw = this.$el.dataset.plans;
                        const data = raw ? JSON.parse(raw) : [];
                        if (!data.length || !this.$refs.planChart) return;

                        const colors = [
                            'rgba(220, 38, 38, 0.8)',   // red — esencial
                            'rgba(14, 165, 233, 0.8)',  // sky — metodo
                            'rgba(168, 85, 247, 0.8)',  // purple — elite
                            'rgba(16, 185, 129, 0.8)',  // emerald — rise
                            'rgba(245, 158, 11, 0.8)',  // amber — presencial
                            'rgba(107, 114, 128, 0.8)', // gray — otro
                        ];

                        this.charts.plan = new Chart(this.$refs.planChart, {
                            type: 'doughnut',
                            data: {
                                labels: data.map(d => d.plan),
                                datasets: [{
                                    data: data.map(d => d.total),
                                    backgroundColor: colors.slice(0, data.length),
                                    borderWidth: 0,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '60%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { color: textColor, padding: 12, usePointStyle: true, pointStyle: 'circle' }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: ctx => ctx.label + ': $' + new Intl.NumberFormat('es-CO').format(ctx.raw) + ' COP'
                                        }
                                    }
                                }
                            }
                        });
                    },

                    initMethodChart(gridColor, textColor) {
                        const raw = this.$el.dataset.methods;
                        const data = raw ? JSON.parse(raw) : [];
                        if (!data.length || !this.$refs.methodChart) return;

                        const methodColors = [
                            'rgba(14, 165, 233, 0.7)',  // sky
                            'rgba(168, 85, 247, 0.7)',  // purple
                            'rgba(16, 185, 129, 0.7)',  // emerald
                            'rgba(245, 158, 11, 0.7)',  // amber
                            'rgba(220, 38, 38, 0.7)',   // red
                            'rgba(107, 114, 128, 0.7)', // gray
                        ];

                        this.charts.method = new Chart(this.$refs.methodChart, {
                            type: 'bar',
                            data: {
                                labels: data.map(d => d.method),
                                datasets: [{
                                    label: 'Revenue (COP)',
                                    data: data.map(d => d.total),
                                    backgroundColor: methodColors.slice(0, data.length),
                                    borderWidth: 0,
                                    borderRadius: 4,
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: ctx => '$' + new Intl.NumberFormat('es-CO').format(ctx.raw) + ' COP'
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            callback: v => '$' + new Intl.NumberFormat('es-CO', { notation: 'compact' }).format(v)
                                        }
                                    },
                                    y: {
                                        grid: { display: false },
                                        ticks: { color: textColor }
                                    }
                                }
                            }
                        });
                    },

                    destroy() {
                        Object.values(this.charts).forEach(c => c?.destroy());
                    }
                };
            }
        </script>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         LOGS TAB
         ═══════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'logs')
        <div class="space-y-4">
            {{-- Filter Bar --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
                    @foreach (['all' => 'Todos', 'ERROR' => 'Error', 'WARNING' => 'Warning', 'INFO' => 'Info'] as $level => $label)
                        <button
                            wire:click="setLogFilter('{{ $level }}')"
                            class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors
                                   {{ $logFilter === $level ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <button
                    wire:click="refreshLogs"
                    class="flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                    </svg>
                    Refrescar
                </button>

                <span class="text-xs text-wc-text-tertiary">
                    {{ count($logEntries) }} entradas {{ $logFilter !== 'all' ? '(' . $logFilter . ')' : '' }} de {{ $logLineCount }} lineas
                </span>
            </div>

            {{-- Log Viewer --}}
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary">
                <div class="max-h-[600px] overflow-y-auto p-4 space-y-1.5">
                    @forelse ($logEntries as $entry)
                        <div class="rounded-lg border px-3 py-2 font-mono text-xs leading-relaxed
                            {{ match($entry['level']) {
                                'ERROR' => 'border-red-500/20 bg-red-500/5 text-red-400',
                                'WARNING' => 'border-amber-500/20 bg-amber-500/5 text-amber-400',
                                'INFO' => 'border-sky-500/20 bg-sky-500/5 text-sky-400',
                                'DEBUG' => 'border-gray-500/20 bg-gray-500/5 text-gray-400',
                                default => 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary',
                            } }}">
                            <div class="flex items-start gap-3">
                                {{-- Level Badge --}}
                                <span class="mt-0.5 inline-flex shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase
                                    {{ match($entry['level']) {
                                        'ERROR' => 'bg-red-500/20 text-red-400',
                                        'WARNING' => 'bg-amber-500/20 text-amber-400',
                                        'INFO' => 'bg-sky-500/20 text-sky-400',
                                        default => 'bg-gray-500/20 text-gray-400',
                                    } }}">
                                    {{ $entry['level'] }}
                                </span>

                                {{-- Timestamp --}}
                                <span class="shrink-0 text-wc-text-tertiary">{{ $entry['timestamp'] }}</span>

                                {{-- Message (truncated, expandable) --}}
                                <div x-data="{ expanded: false }" class="min-w-0 flex-1">
                                    <div
                                        :class="expanded ? '' : 'line-clamp-2'"
                                        class="break-all text-inherit"
                                    >{{ $entry['message'] }}</div>
                                    @if (strlen($entry['message']) > 200)
                                        <button
                                            x-on:click="expanded = !expanded"
                                            class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text"
                                            x-text="expanded ? 'Colapsar' : 'Expandir'"
                                        ></button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <svg class="mx-auto h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <p class="mt-3 text-sm text-wc-text-tertiary">
                                @if ($logFilter !== 'all')
                                    Sin entradas de tipo {{ $logFilter }}
                                @else
                                    Archivo de log vacio o no encontrado
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         HEALTH CHECK TAB
         ═══════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'health')
        <div class="space-y-4">
            {{-- Refresh Button --}}
            <div class="flex items-center justify-between">
                <p class="text-sm text-wc-text-secondary">Estado general del sistema</p>
                <button
                    wire:click="refreshHealth"
                    class="flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
                >
                    <svg class="h-3.5 w-3.5" wire:loading.class="animate-spin" wire:target="refreshHealth" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                    </svg>
                    Refrescar
                </button>
            </div>

            {{-- Health Cards Grid --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($healthChecks as $check)
                    <div class="rounded-[--radius-card] border bg-wc-bg-tertiary p-4
                        {{ match($check['status']) {
                            'ok' => 'border-emerald-500/30',
                            'warning' => 'border-amber-500/30',
                            'error' => 'border-red-500/30',
                            default => 'border-wc-border',
                        } }}">
                        <div class="flex items-start gap-3">
                            {{-- Status indicator --}}
                            <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full
                                {{ match($check['status']) {
                                    'ok' => 'bg-emerald-500/20',
                                    'warning' => 'bg-amber-500/20',
                                    'error' => 'bg-red-500/20',
                                    default => 'bg-gray-500/20',
                                } }}">
                                @if ($check['status'] === 'ok')
                                    <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @elseif ($check['status'] === 'warning')
                                    <svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                @else
                                    <svg class="h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-semibold text-wc-text">{{ $check['label'] }}</h4>
                                    <span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-bold uppercase
                                        {{ match($check['status']) {
                                            'ok' => 'bg-emerald-500/10 text-emerald-500',
                                            'warning' => 'bg-amber-500/10 text-amber-500',
                                            'error' => 'bg-red-500/10 text-red-500',
                                            default => 'bg-gray-500/10 text-gray-500',
                                        } }}">
                                        {{ $check['status'] }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-wc-text-secondary break-all">{{ $check['value'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- System Info --}}
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Informacion del Sistema</h3>
                <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                    <div>
                        <dt class="text-wc-text-tertiary">Servidor</dt>
                        <dd class="font-data font-medium text-wc-text">{{ php_uname('n') }}</dd>
                    </div>
                    <div>
                        <dt class="text-wc-text-tertiary">OS</dt>
                        <dd class="font-data font-medium text-wc-text">{{ PHP_OS }}</dd>
                    </div>
                    <div>
                        <dt class="text-wc-text-tertiary">PHP SAPI</dt>
                        <dd class="font-data font-medium text-wc-text">{{ php_sapi_name() }}</dd>
                    </div>
                    <div>
                        <dt class="text-wc-text-tertiary">Timezone</dt>
                        <dd class="font-data font-medium text-wc-text">{{ config('app.timezone') }}</dd>
                    </div>
                    <div>
                        <dt class="text-wc-text-tertiary">Environment</dt>
                        <dd class="font-data font-medium text-wc-text">{{ app()->environment() }}</dd>
                    </div>
                    <div>
                        <dt class="text-wc-text-tertiary">Debug Mode</dt>
                        <dd class="font-data font-medium {{ config('app.debug') ? 'text-amber-500' : 'text-emerald-500' }}">
                            {{ config('app.debug') ? 'ON' : 'OFF' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    @endif
</div>
