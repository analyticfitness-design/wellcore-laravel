<div wire:poll.30s="refreshStats" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Panel de Administracion</h1>
            <div class="mt-1 flex items-center gap-3">
                <p class="text-sm text-wc-text-tertiary">Resumen general de WellCore Fitness</p>
                <div class="flex items-center gap-1.5 text-xs text-wc-text-tertiary">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span>En vivo</span>
                    @if($lastRefresh)
                        <span class="text-wc-text-tertiary/60">&middot; {{ $lastRefresh }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.clients') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Ver clientes
            </a>
            <a href="{{ route('admin.payments') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                Ver pagos
            </a>
        </div>
    </div>

    {{-- Summary stats cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Active clients --}}
        <div class="card-hover-lift stat-glow-emerald rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes activos</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $activeClients }}" class="counter-highlight">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">en total</p>
        </div>

        {{-- Monthly revenue --}}
        <div class="card-hover-lift stat-glow-red rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ingresos del mes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $monthlyRevenue }}" data-counter-prefix="$" class="counter-highlight">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">COP este mes</p>
        </div>

        {{-- Pending check-ins --}}
        <div class="card-hover-lift stat-glow-amber rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Check-ins pendientes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $pendingCheckins }}" class="counter-highlight">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">sin responder</p>
        </div>

        {{-- New inscriptions --}}
        <div class="card-hover-lift stat-glow-violet rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Inscripciones</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $newInscriptions }}" class="counter-highlight">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">nuevas este mes</p>
        </div>
    </div>

    {{-- Pending Rewards Alert --}}
    @if($pendingRewards->count() > 0)
    <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="font-semibold text-amber-500">{{ $pendingRewards->count() }} recompensa(s) de referidos pendiente(s)</p>
            <a href="{{ route('admin.referral-rewards') }}" class="text-sm text-wc-accent hover:underline">Ver todas &rarr;</a>
        </div>
        <div class="space-y-1.5">
            @foreach($pendingRewards as $r)
                <p class="text-sm text-wc-text-secondary">{{ $r->referrer?->name ?? 'Desconocido' }} &rarr; {{ $r->referred_email }}</p>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Charts Section: Revenue + Client Growth + Plan Distribution --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Revenue Trend (Line Chart) --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2"
             x-data="{ chart: null }"
             x-init="
                 (function() {
                     var data = @js($revenueChartData);
                     if (!data.length) return;
                     var canvas = $refs.revenueCanvas;
                     var existing = Chart.getChart(canvas);
                     if (existing) existing.destroy();
                     chart = new Chart(canvas, {
                         type: 'line',
                         data: {
                             labels: data.map(function(d) {
                                 var parts = d.month.split('-');
                                 var months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                                 return months[parseInt(parts[1]) - 1] + ' ' + parts[0].slice(2);
                             }),
                             datasets: [{
                                 label: 'Ingresos (COP)',
                                 data: data.map(function(d) { return d.total; }),
                                 borderColor: '#DC2626',
                                 backgroundColor: 'rgba(220, 38, 38, 0.08)',
                                 fill: true,
                                 tension: 0.4,
                                 pointRadius: 4,
                                 pointHoverRadius: 7,
                                 pointBackgroundColor: '#DC2626',
                                 pointBorderColor: '#DC2626',
                                 borderWidth: 2.5,
                             }]
                         },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             plugins: {
                                 legend: { display: false },
                                 tooltip: {
                                     callbacks: {
                                         label: function(ctx) { return '$' + new Intl.NumberFormat('es-CO').format(ctx.raw); }
                                     }
                                 }
                             },
                             scales: {
                                 y: {
                                     beginAtZero: true,
                                     grid: { color: 'rgba(63, 63, 70, 0.15)' },
                                     ticks: {
                                         callback: function(v) { return '$' + new Intl.NumberFormat('es-CO', {notation:'compact'}).format(v); }
                                     }
                                 },
                                 x: { grid: { display: false } }
                             }
                         }
                     });
                 })()
             "
             @before-livewire-snapshot.window="if (chart) { chart.destroy(); chart = null; }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-wc-text">Ingresos Mensuales</h3>
                <span class="text-xs text-wc-text-tertiary">Ultimos 6 meses</span>
            </div>
            @if(count($revenueChartData) > 0)
                <div class="chart-container relative h-52">
                    <canvas x-ref="revenueCanvas"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-52 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de ingresos</p>
                </div>
            @endif
        </div>

        {{-- Plan Distribution (Doughnut Chart) --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5"
             x-data="{ chart: null }"
             x-init="
                 (function() {
                     var data = @js($planDistributionData);
                     if (!data.length) return;
                     var colors = ['#DC2626', '#8B5CF6', '#F59E0B', '#10B981', '#0EA5E9', '#EC4899'];
                     var canvas = $refs.planCanvas;
                     var existing = Chart.getChart(canvas);
                     if (existing) existing.destroy();
                     chart = new Chart(canvas, {
                         type: 'doughnut',
                         data: {
                             labels: data.map(function(d) { return d.name; }),
                             datasets: [{
                                 data: data.map(function(d) { return d.count; }),
                                 backgroundColor: colors.slice(0, data.length),
                                 borderWidth: 0,
                                 hoverOffset: 6,
                             }]
                         },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             cutout: '65%',
                             plugins: {
                                 legend: {
                                     position: 'bottom',
                                     labels: {
                                         padding: 12,
                                         usePointStyle: true,
                                         pointStyleWidth: 8,
                                         font: { size: 11, family: 'Raleway, sans-serif' }
                                     }
                                 }
                             }
                         }
                     });
                 })()
             "
             @before-livewire-snapshot.window="if (chart) { chart.destroy(); chart = null; }">
            <h3 class="text-sm font-semibold text-wc-text mb-4">Distribucion de Planes</h3>
            @if(count($planDistributionData) > 0)
                <div class="chart-container relative h-52">
                    <canvas x-ref="planCanvas"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-52 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de planes</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Client Growth (Bar Chart) — full width --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5"
         x-data="{ chart: null }"
         x-init="
             (function() {
                 var data = @js($clientGrowthData);
                 if (!data.length) return;
                 var canvas = $refs.growthCanvas;
                 var existing = Chart.getChart(canvas);
                 if (existing) existing.destroy();
                 chart = new Chart(canvas, {
                     type: 'bar',
                     data: {
                         labels: data.map(function(d) {
                             var parts = d.month.split('-');
                             var months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                             return months[parseInt(parts[1]) - 1] + ' ' + parts[0].slice(2);
                         }),
                         datasets: [{
                             label: 'Nuevos clientes',
                             data: data.map(function(d) { return d.count; }),
                             backgroundColor: 'rgba(220, 38, 38, 0.7)',
                             hoverBackgroundColor: '#DC2626',
                             borderRadius: 6,
                             borderSkipped: false,
                             maxBarThickness: 48,
                         }]
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                             legend: { display: false },
                         },
                         scales: {
                             y: {
                                 beginAtZero: true,
                                 grid: { color: 'rgba(63, 63, 70, 0.15)' },
                                 ticks: { stepSize: 1, precision: 0 }
                             },
                             x: { grid: { display: false } }
                         }
                     }
                 });
             })()
         "
         @before-livewire-snapshot.window="if (chart) { chart.destroy(); chart = null; }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-wc-text">Crecimiento de Clientes</h3>
            <span class="text-xs text-wc-text-tertiary">Ultimos 6 meses</span>
        </div>
        @if(count($clientGrowthData) > 0)
            <div class="chart-container relative h-48">
                <canvas x-ref="growthCanvas"></canvas>
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-48 text-center">
                <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de crecimiento</p>
            </div>
        @endif
    </div>

    {{-- Client breakdown + Quick links --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Client status breakdown --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Estado de Clientes</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Distribucion por estado — {{ $totalClients }} clientes en total</p>

            <div class="mt-5 space-y-3">
                {{-- Activos --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Activos</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsActivo / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsActivo }}</span>
                </div>

                {{-- Inactivos --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Inactivos</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-zinc-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsInactivo / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsInactivo }}</span>
                </div>

                {{-- Pendientes --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Pendientes</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsPendiente / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsPendiente }}</span>
                </div>

                {{-- Suspendidos --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Suspendidos</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-red-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsSuspendido / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsSuspendido }}</span>
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-5 flex flex-wrap items-center gap-4 text-xs text-wc-text-tertiary">
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                    Activos
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-zinc-500"></div>
                    Inactivos
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-amber-500"></div>
                    Pendientes
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-red-500"></div>
                    Suspendidos
                </div>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Accesos rapidos</h2>

            <div class="mt-4 space-y-2">
                <a href="{{ route('admin.clients') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Gestionar Clientes
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.payments') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    Ver Pagos
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.inscriptions') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-sky-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Ver Inscripciones
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.coaches') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-orange-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                    Gestionar Coaches
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.tickets') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                    Tickets de Soporte
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Recent inscriptions + Recent payments --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Recent inscriptions --}}
        <div class="card-hover-lift rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg tracking-wide text-wc-text">Inscripciones Recientes</h2>
                <a href="{{ route('admin.inscriptions') }}" class="text-xs font-medium text-red-500 hover:text-red-400 transition-colors">Ver todas</a>
            </div>

            @if(count($recentInscriptions) > 0)
                <div class="mt-4 space-y-3">
                    @foreach($recentInscriptions as $inscription)
                        <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 transition-colors hover:bg-wc-bg-secondary/50">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500/10">
                                <span class="text-xs font-semibold text-sky-500">{{ substr($inscription['nombre'], 0, 1) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-wc-text">{{ $inscription['nombre'] }}</p>
                                <p class="truncate text-xs text-wc-text-tertiary">{{ $inscription['email'] }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-flex rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-semibold text-sky-500">{{ $inscription['plan'] }}</span>
                                <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $inscription['timeAgo'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-col items-center py-4 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin inscripciones recientes</p>
                </div>
            @endif
        </div>

        {{-- Recent payments --}}
        <div class="card-hover-lift rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg tracking-wide text-wc-text">Pagos Recientes</h2>
                <a href="{{ route('admin.payments') }}" class="text-xs font-medium text-red-500 hover:text-red-400 transition-colors">Ver todos</a>
            </div>

            @if(count($recentPayments) > 0)
                <div class="mt-4 space-y-3">
                    @foreach($recentPayments as $payment)
                        <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 transition-colors hover:bg-wc-bg-secondary/50">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/10">
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-wc-text">{{ $payment['buyerName'] }}</p>
                                <p class="text-xs text-wc-text-tertiary">{{ $payment['plan'] }} &middot; {{ $payment['method'] }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-data text-sm font-semibold text-emerald-500">${{ $payment['amount'] }}</span>
                                <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $payment['timeAgo'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-col items-center py-4 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin pagos recientes</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Activity Timeline --}}
    <div class="card-hover-lift rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-lg tracking-wide text-wc-text">Linea de Actividad</h2>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">Actividad reciente de clientes en la plataforma</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-wc-text-tertiary mr-1">Filtrar:</span>
                @foreach(['todos' => 'Todos', 'checkin' => 'Check-ins', 'training' => 'Entrenos', 'payment' => 'Pagos', 'registration' => 'Nuevos', 'xp' => 'XP'] as $key => $label)
                    <button wire:click="filterTimeline('{{ $key }}')"
                        class="btn-press rounded-lg px-2.5 py-1 text-xs font-medium transition-colors
                        {{ $timelineFilter === $key
                            ? 'bg-wc-accent text-white shadow-sm'
                            : 'bg-wc-bg-secondary text-wc-text-secondary hover:bg-wc-bg hover:text-wc-text' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-5">
            @forelse($activityTimeline as $activity)
                <div class="flex gap-3 py-3 transition-colors hover:bg-wc-bg-secondary/30 rounded-lg px-1 {{ !$loop->last ? 'border-b border-wc-border' : '' }}" wire:key="timeline-{{ $loop->index }}">
                    {{-- Activity icon --}}
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                        @switch($activity['type'])
                            @case('checkin') bg-emerald-500/10 @break
                            @case('training') bg-blue-500/10 @break
                            @case('payment') bg-amber-500/10 @break
                            @case('registration') bg-purple-500/10 @break
                            @case('xp') bg-yellow-500/10 @break
                            @default bg-wc-bg-secondary @break
                        @endswitch
                    ">
                        @switch($activity['type'])
                            @case('checkin')
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                @break
                            @case('training')
                                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                                @break
                            @case('payment')
                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                                @break
                            @case('registration')
                                <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                                @break
                            @case('xp')
                                <svg class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                                @break
                        @endswitch
                    </div>

                    {{-- Activity content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-wc-text">
                            <span class="font-medium">{{ $activity['client_name'] }}</span>
                            @switch($activity['type'])
                                @case('checkin')
                                    <span class="text-wc-text-secondary">completo un check-in</span>
                                    @break
                                @case('training')
                                    <span class="text-wc-text-secondary">registro entrenamiento</span>
                                    @break
                                @case('payment')
                                    <span class="text-wc-text-secondary">realizo un pago</span>
                                    @if($activity['amount'])
                                        <span class="font-data font-semibold text-emerald-500">${{ number_format((float)$activity['amount'], 0, ',', '.') }}</span>
                                    @endif
                                    @break
                                @case('registration')
                                    <span class="text-wc-text-secondary">se registro en la plataforma</span>
                                    @break
                                @case('xp')
                                    <span class="text-wc-text-secondary">gano</span>
                                    @if($activity['amount'])
                                        <span class="font-data font-semibold text-yellow-500">+{{ $activity['amount'] }} XP</span>
                                    @endif
                                    @break
                            @endswitch
                        </p>
                        <div class="mt-0.5 flex items-center gap-2">
                            <p class="text-xs text-wc-text-tertiary">{{ $activity['time_ago'] }}</p>
                            @if($activity['detail'])
                                <span class="text-wc-text-tertiary/40">&middot;</span>
                                <p class="text-xs text-wc-text-tertiary">{{ $activity['detail'] }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Type badge --}}
                    <div class="shrink-0 hidden sm:block">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold
                            @switch($activity['type'])
                                @case('checkin') bg-emerald-500/10 text-emerald-500 @break
                                @case('training') bg-blue-500/10 text-blue-500 @break
                                @case('payment') bg-amber-500/10 text-amber-500 @break
                                @case('registration') bg-purple-500/10 text-purple-500 @break
                                @case('xp') bg-yellow-500/10 text-yellow-500 @break
                                @default bg-wc-bg-secondary text-wc-text-tertiary @break
                            @endswitch
                        ">
                            @switch($activity['type'])
                                @case('checkin') Check-in @break
                                @case('training') Entreno @break
                                @case('payment') Pago @break
                                @case('registration') Nuevo @break
                                @case('xp') XP @break
                            @endswitch
                        </span>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-8 text-center">
                    <svg class="h-10 w-10 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin actividad reciente</p>
                    <p class="mt-0.5 text-xs text-wc-text-tertiary/60">La actividad de los clientes aparecera aqui</p>
                </div>
            @endforelse
        </div>

        @if(count($activityTimeline) >= 30)
            <div class="mt-4 border-t border-wc-border pt-3 text-center">
                <p class="text-xs text-wc-text-tertiary">Mostrando las ultimas 30 actividades</p>
            </div>
        @endif
    </div>

</div>
