<div class="space-y-6">

    {{-- Greeting section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
                {{ $greeting }}, {{ $coachName }}
            </h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Panel de coach — resumen de tu equipo</p>
        </div>

        {{-- Quick actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('coach.checkins') }}"
               class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Revisar check-ins
            </a>
            <a href="{{ route('coach.messages') }}"
               class="btn-press inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                Enviar mensaje
            </a>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Active Clients --}}
        <div class="card-hover-lift stat-glow-emerald rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes activos</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $activeClients }}">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">asignados a ti</p>
        </div>

        {{-- Pending Check-ins --}}
        <div class="card-hover-lift stat-glow-amber rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Check-ins pendientes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text {{ $pendingCheckins > 0 ? 'text-orange-500' : '' }}"><span data-counter="{{ $pendingCheckins }}">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">sin responder</p>
        </div>

        {{-- Unread Messages --}}
        <div class="card-hover-lift stat-glow-red rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mensajes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text {{ $unreadMessages > 0 ? 'text-wc-accent' : '' }}"><span data-counter="{{ $unreadMessages }}">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">no leidos</p>
        </div>

        {{-- Plans This Month --}}
        <div class="card-hover-lift stat-glow-violet rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Planes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $plansThisMonth }}">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">asignados este mes</p>
        </div>
    </div>

    {{-- Charts: Client Progress + Check-in Frequency --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Client Progress (Horizontal Bar) --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5"
             x-data="{
                 chart: null,
                 init() {
                     const data = @js($clientProgressData);
                     if (!data.length) return;
                     const ctx = this.$refs.progressCanvas.getContext('2d');
                     this.chart = new Chart(ctx, {
                         type: 'bar',
                         data: {
                             labels: data.map(d => d.name),
                             datasets: [{
                                 label: 'Sesiones',
                                 data: data.map(d => d.sessions),
                                 backgroundColor: 'rgba(220, 38, 38, 0.7)',
                                 hoverBackgroundColor: '#DC2626',
                                 borderRadius: 4,
                                 borderSkipped: false,
                             }]
                         },
                         options: {
                             indexAxis: 'y',
                             responsive: true,
                             maintainAspectRatio: false,
                             plugins: { legend: { display: false } },
                             scales: {
                                 x: {
                                     beginAtZero: true,
                                     grid: { color: 'rgba(63, 63, 70, 0.15)' },
                                     ticks: { stepSize: 1, precision: 0 }
                                 },
                                 y: { grid: { display: false } }
                             }
                         }
                     });
                 },
                 destroy() { this.chart?.destroy(); }
             }"
             x-init="init()"
             @before-livewire-snapshot.window="destroy()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-wc-text">Progreso de Clientes</h3>
                <span class="text-xs text-wc-text-tertiary">Ultimas 4 semanas</span>
            </div>
            @if(count($clientProgressData) > 0)
                <div class="chart-container relative h-52">
                    <canvas x-ref="progressCanvas"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-52 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de entrenamiento</p>
                </div>
            @endif
        </div>

        {{-- Check-in Frequency (Line Chart) --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5"
             x-data="{
                 chart: null,
                 init() {
                     const data = @js($checkinFrequencyData);
                     if (!data.length) return;
                     const ctx = this.$refs.checkinCanvas.getContext('2d');
                     this.chart = new Chart(ctx, {
                         type: 'line',
                         data: {
                             labels: data.map(d => d.week),
                             datasets: [{
                                 label: 'Check-ins',
                                 data: data.map(d => d.count),
                                 borderColor: '#8B5CF6',
                                 backgroundColor: 'rgba(139, 92, 246, 0.08)',
                                 fill: true,
                                 tension: 0.4,
                                 pointRadius: 4,
                                 pointHoverRadius: 7,
                                 pointBackgroundColor: '#8B5CF6',
                                 pointBorderColor: '#8B5CF6',
                                 borderWidth: 2.5,
                             }]
                         },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             plugins: { legend: { display: false } },
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
                 },
                 destroy() { this.chart?.destroy(); }
             }"
             x-init="init()"
             @before-livewire-snapshot.window="destroy()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-wc-text">Frecuencia de Check-ins</h3>
                <span class="text-xs text-wc-text-tertiary">Ultimas 8 semanas</span>
            </div>
            @if(count($checkinFrequencyData) > 0)
                <div class="chart-container relative h-52">
                    <canvas x-ref="checkinCanvas"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-52 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de check-ins</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Clients needing attention + Recent messages --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Clients needing attention --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg tracking-wide text-wc-text">Clientes que necesitan atencion</h2>
                <a href="{{ route('coach.clients') }}" class="text-xs font-medium text-wc-accent hover:underline">Ver todos</a>
            </div>

            @if(count($attentionClients) > 0)
                <div class="mt-4 space-y-3">
                    @foreach($attentionClients as $client)
                        <div class="card-hover-lift flex items-center gap-4 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                            {{-- Avatar --}}
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                                <span class="text-sm font-semibold text-wc-accent">{{ substr($client['name'], 0, 1) }}</span>
                            </div>

                            {{-- Info --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-wc-text truncate">{{ $client['name'] }}</p>
                                    <span class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">
                                        {{ $client['plan'] }}
                                    </span>
                                </div>
                                <div class="mt-0.5 flex items-center gap-3 text-xs text-wc-text-tertiary">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3 w-3 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        {{ $client['pending_checkins'] }} check-in{{ $client['pending_checkins'] > 1 ? 's' : '' }} pendiente{{ $client['pending_checkins'] > 1 ? 's' : '' }}
                                    </span>
                                    <span>{{ $client['oldest_checkin'] }}</span>
                                </div>
                            </div>

                            {{-- Action --}}
                            <a href="{{ route('coach.checkins') }}"
                               class="btn-press flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-accent hover:border-wc-accent/30 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-col items-center py-8 text-center">
                    <svg class="h-10 w-10 text-emerald-500/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Todos los check-ins respondidos</p>
                    <p class="text-xs text-wc-text-tertiary">Buen trabajo</p>
                </div>
            @endif
        </div>

        {{-- Recent messages --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg tracking-wide text-wc-text">Mensajes recientes</h2>
                <a href="{{ route('coach.messages') }}" class="text-xs font-medium text-wc-accent hover:underline">Ver todos</a>
            </div>

            @if(count($recentMessages) > 0)
                <ul class="mt-4 space-y-3">
                    @foreach($recentMessages as $msg)
                        <li class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $msg['is_read'] ? 'bg-wc-bg-secondary' : 'bg-wc-accent/10' }}">
                                <svg class="h-3.5 w-3.5 {{ $msg['is_read'] ? 'text-wc-text-tertiary' : 'text-wc-accent' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-wc-text {{ !$msg['is_read'] ? 'text-wc-accent' : '' }}">{{ $msg['client_name'] }}</p>
                                <p class="text-xs text-wc-text-secondary truncate">{{ $msg['message'] }}</p>
                                <p class="text-[11px] text-wc-text-tertiary">{{ $msg['time_ago'] }}</p>
                            </div>
                            @if(!$msg['is_read'])
                                <div class="mt-2 h-2 w-2 shrink-0 rounded-full bg-wc-accent"></div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="mt-6 flex flex-col items-center py-4 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin mensajes recientes</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick actions (mobile) --}}
    <div class="grid grid-cols-1 gap-3 sm:hidden">
        <a href="{{ route('coach.checkins') }}"
           class="btn-press flex items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Revisar check-ins
        </a>
        <a href="{{ route('coach.messages') }}"
           class="btn-press flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
            Enviar mensaje
        </a>
        <a href="{{ route('coach.clients') }}"
           class="btn-press flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            Ver mis clientes
        </a>
    </div>

</div>
