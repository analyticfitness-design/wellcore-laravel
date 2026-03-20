<div class="space-y-6" wire:loading.class="opacity-60">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Analytics</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Rendimiento y metricas de tu equipo</p>
        </div>

        {{-- Date Range Selector --}}
        <div class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
            @foreach (['month' => 'Mes', 'quarter' => 'Trimestre', 'year' => 'Ano', 'all' => 'Todo'] as $range => $label)
                <button
                    wire:click="switchDateRange('{{ $range }}')"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors
                           {{ $dateRange === $range
                               ? 'bg-wc-accent text-white shadow-sm'
                               : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Loading indicator --}}
    <div wire:loading class="flex items-center gap-2 text-sm text-wc-text-tertiary">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        Actualizando metricas...
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">

        {{-- Retention Rate --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Retencion</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <p class="font-data text-3xl font-bold text-wc-text">{{ $retentionRate }}%</p>
                @if ($retentionTrend === 'up')
                    <span class="mb-1 flex items-center text-xs font-medium text-emerald-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                    </span>
                @elseif ($retentionTrend === 'down')
                    <span class="mb-1 flex items-center text-xs font-medium text-red-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" /></svg>
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ $activeClients }} activos / {{ $totalClients }} total</p>
        </div>

        {{-- Response Time --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Respuesta</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <p class="font-data text-3xl font-bold text-wc-text">{{ $avgResponseHours }}h</p>
                @if ($responseTrend === 'up')
                    <span class="mb-1 flex items-center text-xs font-medium text-emerald-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                    </span>
                @elseif ($responseTrend === 'down')
                    <span class="mb-1 flex items-center text-xs font-medium text-red-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" /></svg>
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">promedio de respuesta</p>
        </div>

        {{-- Checkin Reply Rate --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Check-ins</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <p class="font-data text-3xl font-bold text-wc-text">{{ $checkinReplyRate }}%</p>
            </div>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ $repliedCheckins }} respondidos / {{ $totalCheckins }}</p>
        </div>

        {{-- Bienestar Score --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Bienestar</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10">
                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <p class="font-data text-3xl font-bold text-wc-text">{{ $avgBienestar }}</p>
                <span class="mb-1 text-sm text-wc-text-tertiary">/10</span>
                @if ($bienestarTrend === 'up')
                    <span class="mb-1 flex items-center text-xs font-medium text-emerald-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                    </span>
                @elseif ($bienestarTrend === 'down')
                    <span class="mb-1 flex items-center text-xs font-medium text-red-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" /></svg>
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">promedio de clientes</p>
        </div>

        {{-- Training Adherence --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Entrenamiento</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <p class="font-data text-3xl font-bold text-wc-text">{{ $avgDiasEntrenados }}</p>
                <span class="mb-1 text-sm text-wc-text-tertiary">dias/sem</span>
                @if ($trainingTrend === 'up')
                    <span class="mb-1 flex items-center text-xs font-medium text-emerald-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                    </span>
                @elseif ($trainingTrend === 'down')
                    <span class="mb-1 flex items-center text-xs font-medium text-red-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" /></svg>
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">promedio por cliente</p>
        </div>

        {{-- Nutrition Adherence --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Nutricion</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-lime-500/10">
                    <svg class="h-4 w-4 text-lime-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <p class="font-data text-3xl font-bold text-wc-text">{{ $nutritionAdherenceRate }}%</p>
            </div>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">adherencia al plan</p>
        </div>

        {{-- Revenue --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ingresos</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <p class="font-data text-3xl font-bold text-wc-text">${{ number_format($totalRevenue, 0, ',', '.') }}</p>
                @if ($revenueTrend === 'up')
                    <span class="mb-1 flex items-center text-xs font-medium text-emerald-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                    </span>
                @elseif ($revenueTrend === 'down')
                    <span class="mb-1 flex items-center text-xs font-medium text-red-500">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" /></svg>
                    </span>
                @endif
            </div>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ $payingClients }} clientes con pagos</p>
        </div>

        {{-- Messages --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mensajes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-500/10">
                    <svg class="h-4 w-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $totalMessages }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ $messagesSent }} enviados / {{ $messagesReceived }} recibidos</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Bienestar Trend Chart --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Tendencia de Bienestar</h2>
            <p class="text-xs text-wc-text-tertiary">Promedio semanal de bienestar (1-10)</p>

            <div class="mt-4 flex items-end gap-1.5" style="height: 160px;">
                @php
                    $maxVal = max(array_column($bienestarChart, 'value') ?: [1]);
                    $maxVal = max($maxVal, 1);
                @endphp
                @foreach ($bienestarChart as $bar)
                    <div class="group relative flex flex-1 flex-col items-center justify-end h-full">
                        @if ($bar['value'] > 0)
                            <div class="absolute -top-5 hidden group-hover:block rounded bg-wc-bg-secondary border border-wc-border px-1.5 py-0.5 text-[10px] font-data font-semibold text-wc-text shadow-lg z-10">
                                {{ $bar['value'] }}
                            </div>
                            <div
                                class="w-full rounded-t bg-amber-500/70 hover:bg-amber-500 transition-colors cursor-default"
                                style="height: {{ ($bar['value'] / 10) * 100 }}%"
                            ></div>
                        @else
                            <div class="w-full rounded-t bg-wc-border" style="height: 2px"></div>
                        @endif
                        <span class="mt-1.5 text-[9px] text-wc-text-tertiary">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Message Activity Chart --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Actividad de Mensajes</h2>
            <div class="mt-1 flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-xs text-wc-text-tertiary">
                    <span class="inline-block h-2.5 w-2.5 rounded-sm bg-sky-500"></span>
                    Enviados
                </span>
                <span class="flex items-center gap-1.5 text-xs text-wc-text-tertiary">
                    <span class="inline-block h-2.5 w-2.5 rounded-sm bg-violet-500"></span>
                    Recibidos
                </span>
            </div>

            <div class="mt-4 flex items-end gap-1.5" style="height: 160px;">
                @php
                    $maxMsg = max(array_merge(
                        array_column($messageChart, 'sent'),
                        array_column($messageChart, 'received'),
                        [1]
                    ));
                @endphp
                @foreach ($messageChart as $bar)
                    <div class="group relative flex flex-1 flex-col items-center justify-end h-full">
                        <div class="flex w-full gap-0.5 items-end h-full">
                            <div class="flex-1 flex flex-col justify-end h-full">
                                @if ($bar['sent'] > 0)
                                    <div
                                        class="w-full rounded-t bg-sky-500/70 hover:bg-sky-500 transition-colors"
                                        style="height: {{ ($bar['sent'] / $maxMsg) * 100 }}%"
                                        title="Enviados: {{ $bar['sent'] }}"
                                    ></div>
                                @else
                                    <div class="w-full rounded-t bg-wc-border" style="height: 2px"></div>
                                @endif
                            </div>
                            <div class="flex-1 flex flex-col justify-end h-full">
                                @if ($bar['received'] > 0)
                                    <div
                                        class="w-full rounded-t bg-violet-500/70 hover:bg-violet-500 transition-colors"
                                        style="height: {{ ($bar['received'] / $maxMsg) * 100 }}%"
                                        title="Recibidos: {{ $bar['received'] }}"
                                    ></div>
                                @else
                                    <div class="w-full rounded-t bg-wc-border" style="height: 2px"></div>
                                @endif
                            </div>
                        </div>
                        <span class="mt-1.5 text-[9px] text-wc-text-tertiary">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Detail Sections --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Client Overview --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Resumen de Clientes</h2>

            <div class="mt-4 space-y-3">
                {{-- Status breakdown --}}
                <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                    <div class="flex items-center gap-2">
                        <div class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-sm text-wc-text">Activos</span>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text">{{ $activeClients }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                    <div class="flex items-center gap-2">
                        <div class="h-2.5 w-2.5 rounded-full bg-gray-400"></div>
                        <span class="text-sm text-wc-text">Inactivos</span>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text">{{ $inactiveClients }}</span>
                </div>

                {{-- Retention bar --}}
                <div class="mt-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-wc-text-tertiary">Tasa de retencion</span>
                        <span class="font-data font-semibold text-emerald-500">{{ $retentionRate }}%</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-wc-border">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $retentionRate }}%"></div>
                    </div>
                </div>

                {{-- Plan Distribution --}}
                @if (count($planDistribution) > 0)
                    <div class="mt-3 border-t border-wc-border pt-3">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Distribucion por plan</p>
                        @foreach ($planDistribution as $plan)
                            <div class="mt-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-wc-text-secondary">{{ $plan['plan'] }}</span>
                                    <span class="font-data font-medium text-wc-text">{{ $plan['count'] }} ({{ $plan['percentage'] }}%)</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-wc-border">
                                    <div class="h-full rounded-full bg-wc-accent/60 transition-all duration-500" style="width: {{ $plan['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Response Performance --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Rendimiento de Respuesta</h2>

            <div class="mt-4 space-y-4">
                {{-- Avg Response Time --}}
                <div class="text-center rounded-lg border border-wc-border bg-wc-bg-secondary p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tiempo promedio</p>
                    <p class="mt-2 font-data text-4xl font-bold {{ $avgResponseHours <= 24 ? 'text-emerald-500' : ($avgResponseHours <= 48 ? 'text-amber-500' : 'text-red-500') }}">
                        {{ $avgResponseHours }}h
                    </p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">
                        @if ($avgResponseHours <= 24)
                            Excelente respuesta
                        @elseif ($avgResponseHours <= 48)
                            Buen ritmo
                        @else
                            Necesita mejorar
                        @endif
                    </p>
                </div>

                {{-- Reply Rate --}}
                <div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-wc-text-tertiary">Tasa de respuesta</span>
                        <span class="font-data font-semibold {{ $checkinReplyRate >= 80 ? 'text-emerald-500' : ($checkinReplyRate >= 50 ? 'text-amber-500' : 'text-red-500') }}">{{ $checkinReplyRate }}%</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-wc-border">
                        <div class="h-full rounded-full transition-all duration-500 {{ $checkinReplyRate >= 80 ? 'bg-emerald-500' : ($checkinReplyRate >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $checkinReplyRate }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-wc-text-tertiary">{{ $repliedCheckins }} de {{ $totalCheckins }} check-ins respondidos</p>
                </div>

                {{-- Checkin Completion --}}
                <div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-wc-text-tertiary">Completitud check-ins</span>
                        <span class="font-data font-semibold text-wc-text">{{ $checkinCompletionRate }}%</span>
                    </div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-wc-border">
                        <div class="h-full rounded-full bg-violet-500 transition-all duration-500" style="width: {{ min($checkinCompletionRate, 100) }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-wc-text-tertiary">{{ $actualCheckins }} de {{ $expectedCheckins }} esperados</p>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Ingresos</h2>

            <div class="mt-4 space-y-4">
                {{-- Monthly Revenue Highlight --}}
                <div class="text-center rounded-lg border border-wc-border bg-wc-bg-secondary p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Este mes</p>
                    <p class="mt-2 font-data text-4xl font-bold text-wc-accent">${{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">COP</p>
                </div>

                {{-- Revenue Chart (CSS bars) --}}
                @if (count($revenueChart) > 0)
                    @php
                        $maxRev = max(array_column($revenueChart, 'value') ?: [1]);
                        $maxRev = max($maxRev, 1);
                    @endphp
                    <div class="flex items-end gap-1.5" style="height: 100px;">
                        @foreach ($revenueChart as $bar)
                            <div class="group relative flex flex-1 flex-col items-center justify-end h-full">
                                @if ($bar['value'] > 0)
                                    <div class="absolute -top-5 hidden group-hover:block rounded bg-wc-bg-secondary border border-wc-border px-1.5 py-0.5 text-[10px] font-data font-semibold text-wc-text shadow-lg z-10">
                                        ${{ number_format($bar['value'], 0, ',', '.') }}
                                    </div>
                                    <div
                                        class="w-full rounded-t bg-wc-accent/60 hover:bg-wc-accent transition-colors cursor-default"
                                        style="height: {{ ($bar['value'] / $maxRev) * 100 }}%"
                                    ></div>
                                @else
                                    <div class="w-full rounded-t bg-wc-border" style="height: 2px"></div>
                                @endif
                                <span class="mt-1.5 text-[9px] text-wc-text-tertiary whitespace-nowrap">{{ $bar['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Revenue stats --}}
                <div class="border-t border-wc-border pt-3 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-wc-text-tertiary">Total periodo</span>
                        <span class="font-data font-semibold text-wc-text">${{ number_format($totalRevenue, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-wc-text-tertiary">Clientes pagando</span>
                        <span class="font-data font-semibold text-wc-text">{{ $payingClients }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Clients --}}
    @if (count($topClients) > 0)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">Top Clientes por Entrenamiento</h2>
                    <p class="text-xs text-wc-text-tertiary">Mayor promedio de dias entrenados por semana</p>
                </div>
                <a href="{{ route('coach.clients') }}" class="text-xs font-medium text-wc-accent hover:underline">Ver todos</a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-wc-border text-xs uppercase tracking-wider text-wc-text-tertiary">
                            <th class="pb-2 pr-4">Cliente</th>
                            <th class="pb-2 pr-4">Plan</th>
                            <th class="pb-2 pr-4 text-center">Dias/sem</th>
                            <th class="pb-2 pr-4 text-center">Bienestar</th>
                            <th class="pb-2 text-center">Check-ins</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach ($topClients as $idx => $client)
                            <tr class="text-wc-text">
                                <td class="py-2.5 pr-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full
                                            {{ $idx === 0 ? 'bg-amber-500/20 text-amber-500' : ($idx === 1 ? 'bg-gray-400/20 text-gray-400' : 'bg-wc-bg-secondary text-wc-text-tertiary') }}">
                                            <span class="text-xs font-semibold">{{ $idx + 1 }}</span>
                                        </div>
                                        <span class="font-medium">{{ $client['name'] }}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="inline-flex rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">
                                        {{ ucfirst($client['plan']) }}
                                    </span>
                                </td>
                                <td class="py-2.5 pr-4 text-center">
                                    <span class="font-data font-semibold {{ $client['avg_dias'] >= 4 ? 'text-emerald-500' : ($client['avg_dias'] >= 3 ? 'text-amber-500' : 'text-wc-text') }}">
                                        {{ $client['avg_dias'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 pr-4 text-center">
                                    <span class="font-data font-semibold">{{ $client['avg_bienestar'] }}</span>
                                    <span class="text-[10px] text-wc-text-tertiary">/10</span>
                                </td>
                                <td class="py-2.5 text-center font-data">{{ $client['checkins'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Empty state --}}
    @if ($totalClients === 0)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            <h3 class="mt-4 font-display text-xl text-wc-text">Sin datos disponibles</h3>
            <p class="mt-2 text-sm text-wc-text-tertiary">Aun no tienes clientes asignados. Las metricas apareceran cuando tengas clientes con actividad.</p>
            <a href="{{ route('coach.dashboard') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                Ir al dashboard
            </a>
        </div>
    @endif
</div>
