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

    {{-- Coach Score Hero --}}
    @if ($coachScore > 0)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    {{-- Score Ring --}}
                    <div class="relative flex h-20 w-20 shrink-0 items-center justify-center">
                        <svg class="h-20 w-20 -rotate-90" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="6"
                                class="text-wc-border" />
                            <circle cx="40" cy="40" r="34" fill="none" stroke-width="6"
                                stroke-dasharray="{{ $coachScore * 2.136 }} 213.6"
                                stroke-linecap="round"
                                class="{{ $coachScore >= 75 ? 'text-emerald-500' : ($coachScore >= 50 ? 'text-amber-500' : 'text-red-500') }}" />
                        </svg>
                        <span class="absolute font-data text-xl font-bold text-wc-text">{{ number_format($coachScore, 0) }}</span>
                    </div>
                    <div>
                        <h2 class="font-display text-lg tracking-wide text-wc-text">Coach Score</h2>
                        <p class="text-sm font-semibold {{ $coachScore >= 75 ? 'text-emerald-500' : ($coachScore >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                            {{ $coachScoreLabel }}
                        </p>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary">Puntuacion compuesta de rendimiento</p>
                    </div>
                </div>

                {{-- Score Breakdown Mini --}}
                <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-xs sm:grid-cols-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-wc-text-tertiary">Respuesta</span>
                        <span class="font-data font-semibold {{ $avgResponseHours <= 24 ? 'text-emerald-500' : ($avgResponseHours <= 48 ? 'text-amber-500' : 'text-red-500') }}">{{ $avgResponseHours }}h</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-wc-text-tertiary">Reply Rate</span>
                        <span class="font-data font-semibold text-wc-text">{{ $checkinReplyRate }}%</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-wc-text-tertiary">Retencion</span>
                        <span class="font-data font-semibold text-wc-text">{{ $retentionRate }}%</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-wc-text-tertiary">Bienestar</span>
                        <span class="font-data font-semibold text-wc-text">{{ $avgBienestar }}/10</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-wc-text-tertiary">Check-ins</span>
                        <span class="font-data font-semibold text-wc-text">{{ $checkinCompletionRate }}%</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-wc-text-tertiary">SLA</span>
                        <span class="font-data font-semibold {{ in_array($slaGrade, ['A+', 'A']) ? 'text-emerald-500' : ($slaGrade === 'B' ? 'text-amber-500' : 'text-red-500') }}">{{ $slaGrade }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

    {{-- ═══ Client Comparison Tool ═══ --}}
    @if (count($myClientsList) >= 2)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5" id="comparison-tool">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">Comparar Clientes</h2>
                    <p class="text-xs text-wc-text-tertiary">Selecciona 2 a 4 clientes para comparar metricas lado a lado</p>
                </div>
                @if (count($selectedClients) > 0)
                    <button
                        wire:click="clearComparison"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg transition-colors"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Limpiar
                    </button>
                @endif
            </div>

            {{-- Client Selector Pills --}}
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($myClientsList as $mc)
                    @php
                        $isSelected = in_array($mc['id'], $selectedClients);
                        $selIdx = $isSelected ? array_search($mc['id'], $selectedClients) : null;
                        $colorMap = ['bg-wc-accent', 'bg-sky-500', 'bg-violet-500', 'bg-amber-500'];
                        $pillColor = $isSelected ? ($colorMap[$selIdx] ?? 'bg-gray-500') : '';
                        $isFull = count($selectedClients) >= 4 && !$isSelected;
                    @endphp
                    <button
                        wire:click="toggleClientComparison({{ $mc['id'] }})"
                        @if ($isFull) disabled @endif
                        class="group relative inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium transition-all
                            {{ $isSelected
                                ? $pillColor . ' text-white shadow-sm'
                                : ($isFull
                                    ? 'bg-wc-bg-secondary text-wc-text-tertiary cursor-not-allowed opacity-50 border border-wc-border'
                                    : 'bg-wc-bg-secondary text-wc-text-secondary hover:bg-wc-bg hover:text-wc-text border border-wc-border hover:border-wc-text-tertiary') }}"
                    >
                        {{-- Avatar circle --}}
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[9px] font-bold
                            {{ $isSelected ? 'bg-white/20 text-white' : 'bg-wc-bg-tertiary text-wc-text-tertiary' }}">
                            {{ $mc['initials'] }}
                        </span>
                        {{ $mc['name'] }}
                        @if ($isSelected)
                            <svg class="h-3 w-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Selection hint --}}
            @if (count($selectedClients) < 2)
                <div class="mt-4 flex items-center justify-center gap-2 rounded-lg border border-dashed border-wc-border bg-wc-bg-secondary/50 py-6">
                    <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    <p class="text-sm text-wc-text-tertiary">
                        Selecciona al menos <span class="font-semibold text-wc-text-secondary">2 clientes</span> para comparar
                        @if (count($selectedClients) === 1)
                            <span class="text-wc-accent">(1 seleccionado)</span>
                        @endif
                    </p>
                </div>
            @endif

            {{-- Comparison Results --}}
            @if ($showComparison && count($comparisonData) >= 2)
                <div class="mt-5 space-y-0" wire:loading.class="opacity-50">

                    {{-- Legend --}}
                    <div class="mb-4 flex flex-wrap items-center gap-3">
                        @php
                            $colorDotMap = ['bg-wc-accent', 'bg-sky-500', 'bg-violet-500', 'bg-amber-500'];
                        @endphp
                        @foreach ($comparisonData as $idx => $cd)
                            <span class="inline-flex items-center gap-1.5 text-xs text-wc-text-secondary">
                                <span class="inline-block h-2.5 w-2.5 rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }}"></span>
                                {{ $cd['name'] }}
                                <span class="text-[10px] text-wc-text-tertiary">({{ ucfirst($cd['plan']) }})</span>
                            </span>
                        @endforeach
                    </div>

                    {{-- Comparison Table --}}
                    <div class="-mx-5 overflow-x-auto px-5">
                        <table class="w-full min-w-[540px] text-sm">
                            <thead>
                                <tr class="border-b border-wc-border">
                                    <th class="pb-2.5 pr-4 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Metrica</th>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <th class="pb-2.5 px-3 text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold text-white {{ $colorDotMap[$idx] ?? 'bg-gray-500' }}">
                                                    {{ $cd['initials'] }}
                                                </span>
                                                <span class="text-xs font-medium text-wc-text truncate max-w-[100px]">{{ $cd['name'] }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-wc-border">

                                {{-- Peso actual --}}
                                @php
                                    $weights = array_filter(array_column($comparisonData, 'weight_kg'));
                                    $maxWeight = !empty($weights) ? max($weights) : 1;
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Peso actual</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['weight_kg'])
                                                <div class="flex flex-col items-center gap-1.5">
                                                    <div class="flex w-full items-center gap-2">
                                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                            <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                                 style="width: {{ $maxWeight > 0 ? (($cd['weight_kg'] / $maxWeight) * 100) : 0 }}%"></div>
                                                        </div>
                                                        <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ number_format($cd['weight_kg'], 1) }}</span>
                                                    </div>
                                                    <span class="text-[10px] text-wc-text-tertiary">kg</span>
                                                    @if ($cd['weight_delta'] !== null)
                                                        <span class="text-[10px] font-medium {{ $cd['weight_delta'] < 0 ? 'text-emerald-500' : ($cd['weight_delta'] > 0 ? 'text-amber-500' : 'text-wc-text-tertiary') }}">
                                                            {{ $cd['weight_delta'] > 0 ? '+' : '' }}{{ number_format($cd['weight_delta'], 1) }} kg
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- % Grasa Corporal --}}
                                @php
                                    $bfs = array_filter(array_column($comparisonData, 'body_fat_pct'));
                                    $maxBf = !empty($bfs) ? max($bfs) : 1;
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">% Grasa</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['body_fat_pct'])
                                                <div class="flex flex-col items-center gap-1.5">
                                                    <div class="flex w-full items-center gap-2">
                                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                            <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                                 style="width: {{ $maxBf > 0 ? (($cd['body_fat_pct'] / $maxBf) * 100) : 0 }}%"></div>
                                                        </div>
                                                        <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ number_format($cd['body_fat_pct'], 1) }}</span>
                                                    </div>
                                                    <span class="text-[10px] text-wc-text-tertiary">%</span>
                                                    @if ($cd['bf_delta'] !== null)
                                                        <span class="text-[10px] font-medium {{ $cd['bf_delta'] < 0 ? 'text-emerald-500' : ($cd['bf_delta'] > 0 ? 'text-amber-500' : 'text-wc-text-tertiary') }}">
                                                            {{ $cd['bf_delta'] > 0 ? '+' : '' }}{{ number_format($cd['bf_delta'], 1) }}%
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Cintura --}}
                                @php
                                    $waists = array_filter(array_column($comparisonData, 'waist_cm'));
                                    $maxWaist = !empty($waists) ? max($waists) : 1;
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Cintura</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['waist_cm'])
                                                <div class="flex w-full items-center gap-2">
                                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                        <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                             style="width: {{ $maxWaist > 0 ? (($cd['waist_cm'] / $maxWaist) * 100) : 0 }}%"></div>
                                                    </div>
                                                    <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ number_format($cd['waist_cm'], 1) }} <span class="text-[10px] text-wc-text-tertiary">cm</span></span>
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Bienestar --}}
                                @php
                                    $bienestars = array_filter(array_column($comparisonData, 'avg_bienestar'));
                                    $maxBien = 10; // Scale is 1-10
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Bienestar</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['avg_bienestar'] > 0)
                                                <div class="flex flex-col items-center gap-1.5">
                                                    <div class="flex w-full items-center gap-2">
                                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                            <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                                 style="width: {{ ($cd['avg_bienestar'] / $maxBien) * 100 }}%"></div>
                                                        </div>
                                                        <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ $cd['avg_bienestar'] }}</span>
                                                    </div>
                                                    <span class="text-[10px] text-wc-text-tertiary">/10</span>
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Dias entrenados --}}
                                @php
                                    $dias = array_filter(array_column($comparisonData, 'avg_dias'));
                                    $maxDias = 7; // Max 7 days/week
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Dias/semana</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['avg_dias'] > 0)
                                                <div class="flex flex-col items-center gap-1.5">
                                                    <div class="flex w-full items-center gap-2">
                                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                            <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                                 style="width: {{ ($cd['avg_dias'] / $maxDias) * 100 }}%"></div>
                                                        </div>
                                                        <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ $cd['avg_dias'] }}</span>
                                                    </div>
                                                    <span class="text-[10px] text-wc-text-tertiary">dias</span>
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Nutricion --}}
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Nutricion</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['nutrition_rate'] > 0)
                                                <div class="flex flex-col items-center gap-1.5">
                                                    <div class="flex w-full items-center gap-2">
                                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                            <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                                 style="width: {{ $cd['nutrition_rate'] }}%"></div>
                                                        </div>
                                                        <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ $cd['nutrition_rate'] }}%</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Training Completion --}}
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Completitud</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['training_rate'] > 0)
                                                <div class="flex flex-col items-center gap-1.5">
                                                    <div class="flex w-full items-center gap-2">
                                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                            <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                                 style="width: {{ $cd['training_rate'] }}%"></div>
                                                        </div>
                                                        <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ $cd['training_rate'] }}%</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Check-ins --}}
                                @php
                                    $checkins = array_column($comparisonData, 'checkins');
                                    $maxCheckins = max($checkins ?: [1]);
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Check-ins</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            <div class="flex w-full items-center gap-2">
                                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                    <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                         style="width: {{ $maxCheckins > 0 ? (($cd['checkins'] / $maxCheckins) * 100) : 0 }}%"></div>
                                                </div>
                                                <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ $cd['checkins'] }}</span>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Sueno promedio --}}
                                @php
                                    $sleeps = array_filter(array_column($comparisonData, 'avg_sleep'));
                                    $maxSleep = !empty($sleeps) ? max(max($sleeps), 1) : 1;
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Sueno prom.</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['avg_sleep'] > 0)
                                                <div class="flex w-full items-center gap-2">
                                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                        <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                             style="width: {{ ($cd['avg_sleep'] / $maxSleep) * 100 }}%"></div>
                                                    </div>
                                                    <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ number_format($cd['avg_sleep'], 1) }}h</span>
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Pasos promedio --}}
                                @php
                                    $steps = array_filter(array_column($comparisonData, 'avg_steps'));
                                    $maxSteps = !empty($steps) ? max($steps) : 1;
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Pasos prom.</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            @if ($cd['avg_steps'] > 0)
                                                <div class="flex w-full items-center gap-2">
                                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                        <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                             style="width: {{ $maxSteps > 0 ? (($cd['avg_steps'] / $maxSteps) * 100) : 0 }}%"></div>
                                                    </div>
                                                    <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ number_format($cd['avg_steps'], 0, '', '.') }}</span>
                                                </div>
                                            @else
                                                <span class="block text-center font-data text-xs text-wc-text-tertiary">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Personal Records --}}
                                @php
                                    $prs = array_column($comparisonData, 'pr_count');
                                    $maxPr = max($prs ?: [1]);
                                @endphp
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-7.54 0" />
                                            </svg>
                                            <span class="text-xs font-medium text-wc-text-secondary">Records</span>
                                        </div>
                                    </td>
                                    @foreach ($comparisonData as $idx => $cd)
                                        <td class="py-3 px-3">
                                            <div class="flex w-full items-center gap-2">
                                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-wc-bg-secondary">
                                                    <div class="h-full rounded-full {{ $colorDotMap[$idx] ?? 'bg-gray-500' }} transition-all duration-500"
                                                         style="width: {{ $maxPr > 0 ? (($cd['pr_count'] / $maxPr) * 100) : 0 }}%"></div>
                                                </div>
                                                <span class="font-data text-xs font-semibold tabular-nums text-wc-text">{{ $cd['pr_count'] }}</span>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    {{-- Summary insight --}}
                    <div class="mt-4 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                        <p class="text-xs text-wc-text-tertiary">
                            <svg class="mr-1 inline h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            Comparando {{ count($comparisonData) }} clientes en el periodo:
                            <span class="font-medium text-wc-text-secondary">
                                {{ match($dateRange) {
                                    'month' => 'Ultimo mes',
                                    'quarter' => 'Ultimo trimestre',
                                    'year' => 'Ultimo ano',
                                    'all' => 'Todo el historial',
                                    default => $dateRange,
                                } }}
                            </span>.
                            Las barras muestran valores relativos entre los clientes seleccionados.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @endif
    {{-- ═══ End Client Comparison Tool ═══ --}}

    {{-- ═══ Enhanced Analytics Sections ═══ --}}

    {{-- SLA Breakdown + Adherence Row --}}
    @if ($totalClients > 0)
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- SLA Breakdown --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-lg tracking-wide text-wc-text">SLA de Respuesta</h2>
                        <p class="text-xs text-wc-text-tertiary">Distribucion de tiempos de respuesta a check-ins</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ in_array($slaGrade, ['A+', 'A']) ? 'bg-emerald-500/10' : ($slaGrade === 'B' ? 'bg-amber-500/10' : 'bg-red-500/10') }}">
                        <span class="font-data text-sm font-bold {{ in_array($slaGrade, ['A+', 'A']) ? 'text-emerald-500' : ($slaGrade === 'B' ? 'text-amber-500' : 'text-red-500') }}">{{ $slaGrade }}</span>
                    </div>
                </div>

                @php
                    $slaTotal = $slaUnder12h + $sla12to24h + $sla24to48h + $slaOver48h;
                @endphp

                @if ($slaTotal > 0)
                    {{-- Stacked bar --}}
                    <div class="mt-4 flex h-4 w-full overflow-hidden rounded-full bg-wc-border">
                        @if ($slaUnder12h > 0)
                            <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ ($slaUnder12h / $slaTotal) * 100 }}%" title="< 12h: {{ $slaUnder12h }}"></div>
                        @endif
                        @if ($sla12to24h > 0)
                            <div class="h-full bg-sky-500 transition-all duration-500" style="width: {{ ($sla12to24h / $slaTotal) * 100 }}%" title="12-24h: {{ $sla12to24h }}"></div>
                        @endif
                        @if ($sla24to48h > 0)
                            <div class="h-full bg-amber-500 transition-all duration-500" style="width: {{ ($sla24to48h / $slaTotal) * 100 }}%" title="24-48h: {{ $sla24to48h }}"></div>
                        @endif
                        @if ($slaOver48h > 0)
                            <div class="h-full bg-red-500 transition-all duration-500" style="width: {{ ($slaOver48h / $slaTotal) * 100 }}%" title="> 48h: {{ $slaOver48h }}"></div>
                        @endif
                    </div>

                    {{-- Legend --}}
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div class="flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></div>
                            <span class="text-xs text-wc-text-secondary">< 12h</span>
                            <span class="ml-auto font-data text-xs font-semibold text-wc-text">{{ $slaUnder12h }}</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-sky-500"></div>
                            <span class="text-xs text-wc-text-secondary">12-24h</span>
                            <span class="ml-auto font-data text-xs font-semibold text-wc-text">{{ $sla12to24h }}</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></div>
                            <span class="text-xs text-wc-text-secondary">24-48h</span>
                            <span class="ml-auto font-data text-xs font-semibold text-wc-text">{{ $sla24to48h }}</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2">
                            <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-red-500"></div>
                            <span class="text-xs text-wc-text-secondary">> 48h</span>
                            <span class="ml-auto font-data text-xs font-semibold text-wc-text">{{ $slaOver48h }}</span>
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-lg border border-wc-border bg-wc-bg-secondary p-4 text-center">
                        <p class="text-sm text-wc-text-tertiary">Sin datos de respuesta en este periodo</p>
                    </div>
                @endif
            </div>

            {{-- Biometric Insights --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-lg tracking-wide text-wc-text">Insights Biometricos</h2>
                        <p class="text-xs text-wc-text-tertiary">{{ $clientsWithBiometrics }} clientes con datos biometricos</p>
                    </div>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                        <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    {{-- Weight Change --}}
                    <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-center">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Cambio Peso Prom.</p>
                        <p class="mt-1 font-data text-2xl font-bold {{ $avgWeightChange < 0 ? 'text-emerald-500' : ($avgWeightChange > 0 ? 'text-amber-500' : 'text-wc-text') }}">
                            {{ $avgWeightChange > 0 ? '+' : '' }}{{ $avgWeightChange }}kg
                        </p>
                    </div>

                    {{-- Body Fat --}}
                    <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-center">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Grasa Corporal</p>
                        <p class="mt-1 font-data text-2xl font-bold text-wc-text">
                            {{ $avgBodyFat > 0 ? $avgBodyFat . '%' : 'N/A' }}
                        </p>
                    </div>

                    {{-- Sleep --}}
                    <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-center">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Sueno Promedio</p>
                        <p class="mt-1 font-data text-2xl font-bold {{ $avgSleepHours >= 7 ? 'text-emerald-500' : ($avgSleepHours >= 6 ? 'text-amber-500' : 'text-red-500') }}">
                            {{ $avgSleepHours > 0 ? $avgSleepHours . 'h' : 'N/A' }}
                        </p>
                    </div>

                    {{-- Clients Tracked --}}
                    <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-center">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Con Biometricos</p>
                        <p class="mt-1 font-data text-2xl font-bold text-wc-text">
                            {{ $clientsWithBiometrics }}/{{ $activeClients }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- Adherence by Client --}}
    @if (count($adherenceByClient) > 0)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">Adherencia por Cliente</h2>
                    <p class="text-xs text-wc-text-tertiary">Check-ins completados vs esperados en el periodo</p>
                </div>
            </div>

            <div class="mt-4 space-y-2.5">
                @foreach ($adherenceByClient as $client)
                    @php
                        $pct = $client['expected'] > 0 ? min(($client['checkin_count'] / $client['expected']) * 100, 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-28 shrink-0 truncate text-sm text-wc-text" title="{{ $client['name'] }}">{{ $client['name'] }}</div>
                        <div class="flex-1 h-3 rounded-full bg-wc-bg-secondary overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="font-data text-xs font-medium text-wc-text-secondary tabular-nums w-10 text-right">{{ $client['checkin_count'] }}/{{ $client['expected'] }}</span>
                        {{-- Training rate mini badge --}}
                        <span class="hidden sm:inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold
                            {{ $client['training_rate'] >= 80 ? 'bg-emerald-500/10 text-emerald-500' : ($client['training_rate'] >= 50 ? 'bg-amber-500/10 text-amber-500' : 'bg-red-500/10 text-red-500') }}"
                            title="Tasa de entrenamiento completado">
                            {{ $client['training_rate'] }}%
                        </span>
                        {{-- Bienestar mini --}}
                        <span class="hidden sm:inline font-data text-[11px] tabular-nums {{ $client['avg_bienestar'] >= 7 ? 'text-emerald-500' : ($client['avg_bienestar'] >= 5 ? 'text-amber-500' : 'text-red-500') }}"
                            title="Bienestar promedio">
                            {{ $client['avg_bienestar'] > 0 ? $client['avg_bienestar'] : '-' }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div class="mt-3 flex items-center gap-4 border-t border-wc-border pt-3">
                <span class="flex items-center gap-1.5 text-[10px] text-wc-text-tertiary">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                    75%+ Excelente
                </span>
                <span class="flex items-center gap-1.5 text-[10px] text-wc-text-tertiary">
                    <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                    50-74% Regular
                </span>
                <span class="flex items-center gap-1.5 text-[10px] text-wc-text-tertiary">
                    <span class="inline-block h-2 w-2 rounded-full bg-red-500"></span>
                    &lt;50% Baja
                </span>
            </div>
        </div>
    @endif

    {{-- Training Adherence Heatmap --}}
    @if (count($trainingHeatmap) > 0)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">Heatmap de Entrenamiento</h2>
                    <p class="text-xs text-wc-text-tertiary">Sesiones completadas por cliente (ultimas 4 semanas)</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="pb-2 pr-4 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                            @foreach ($heatmapWeeks as $week)
                                <th class="pb-2 px-2 text-center text-[10px] font-medium text-wc-text-tertiary whitespace-nowrap">{{ $week['label'] }}</th>
                            @endforeach
                            <th class="pb-2 pl-3 text-center text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach ($trainingHeatmap as $row)
                            <tr>
                                <td class="py-2 pr-4 text-sm text-wc-text">
                                    <div class="w-24 truncate" title="{{ $row['name'] }}">{{ $row['name'] }}</div>
                                </td>
                                @php $rowTotal = 0; @endphp
                                @foreach ($row['weeks'] as $week)
                                    @php
                                        $rowTotal += $week['completed'];
                                        $intensity = $week['total'] > 0 ? ($week['completed'] / $week['total']) : 0;
                                    @endphp
                                    <td class="px-2 py-2 text-center">
                                        <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-md text-[10px] font-data font-semibold
                                            @if ($week['total'] === 0)
                                                bg-wc-bg-secondary text-wc-text-tertiary
                                            @elseif ($intensity >= 0.8)
                                                bg-emerald-500/20 text-emerald-500
                                            @elseif ($intensity >= 0.5)
                                                bg-amber-500/20 text-amber-500
                                            @else
                                                bg-red-500/20 text-red-500
                                            @endif
                                        " title="{{ $week['completed'] }}/{{ $week['total'] }} completados">
                                            @if ($week['total'] > 0)
                                                {{ $week['completed'] }}/{{ $week['total'] }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                                <td class="py-2 pl-3 text-center">
                                    <span class="font-data text-sm font-semibold text-wc-text">{{ $rowTotal }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="mt-3 flex items-center gap-4 border-t border-wc-border pt-3">
                <span class="flex items-center gap-1.5 text-[10px] text-wc-text-tertiary">
                    <span class="inline-block h-3 w-3 rounded bg-emerald-500/20"></span>
                    80%+ completado
                </span>
                <span class="flex items-center gap-1.5 text-[10px] text-wc-text-tertiary">
                    <span class="inline-block h-3 w-3 rounded bg-amber-500/20"></span>
                    50-79%
                </span>
                <span class="flex items-center gap-1.5 text-[10px] text-wc-text-tertiary">
                    <span class="inline-block h-3 w-3 rounded bg-red-500/20"></span>
                    &lt;50%
                </span>
                <span class="flex items-center gap-1.5 text-[10px] text-wc-text-tertiary">
                    <span class="inline-block h-3 w-3 rounded bg-wc-bg-secondary"></span>
                    Sin datos
                </span>
            </div>
        </div>
    @endif

    {{-- At-Risk Clients --}}
    @if (count($atRiskClients) > 0)
        <div class="rounded-card border border-red-500/30 bg-wc-bg-tertiary p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-500/10">
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">Clientes en Riesgo</h2>
                    <p class="text-xs text-wc-text-tertiary">{{ count($atRiskClients) }} clientes que necesitan atencion</p>
                </div>
            </div>

            <div class="mt-4 space-y-2">
                @foreach ($atRiskClients as $risk)
                    <div class="flex items-start gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full
                            {{ $risk['severity'] >= 3 ? 'bg-red-500/20 text-red-500' : ($risk['severity'] >= 2 ? 'bg-amber-500/20 text-amber-500' : 'bg-amber-500/10 text-amber-400') }}">
                            <span class="text-[10px] font-bold">{{ $risk['severity'] }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-wc-text truncate">{{ $risk['name'] }}</span>
                                <span class="shrink-0 inline-flex rounded-full bg-wc-accent/10 px-1.5 py-0.5 text-[9px] font-semibold text-wc-accent">
                                    {{ ucfirst($risk['plan']) }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                @foreach ($risk['reasons'] as $reason)
                                    <span class="inline-flex items-center rounded-md bg-red-500/5 px-2 py-0.5 text-[10px] text-red-400 ring-1 ring-inset ring-red-500/20">
                                        {{ $reason }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ End Enhanced Analytics ═══ --}}

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
