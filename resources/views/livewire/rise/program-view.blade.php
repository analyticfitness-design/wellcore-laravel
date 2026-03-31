<div class="space-y-6">

    {{-- ─── PAGE HEADER ──────────────────────────────────────────────────────── --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">MI PROGRAMA</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Tu plan RISE personalizado de {{ $totalWeeks }} semanas.</p>
    </div>

    @if($hasProgram)

        {{-- ─── PROGRAM OVERVIEW CARD ───────────────────────────────────────── --}}
        <div class="relative overflow-hidden rounded-card border border-wc-accent/20 bg-gradient-to-br from-wc-accent/8 via-amber-400/4 to-transparent p-5 sm:p-6">
            {{-- Decorative orbs --}}
            <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/6"></div>
            <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-wc-accent/10"></div>

            <div class="relative">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-amber-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                                <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                Programa RISE
                            </span>
                            @if($status)
                                <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Activo</span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm text-wc-text-secondary">
                            @if($startDate && $endDate)
                                {{ $startDate }} &mdash; {{ $endDate }}
                            @else
                                Plan en curso
                            @endif
                        </p>

                        {{-- Program attributes --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($experienceLevel)
                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                                    <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    {{ ucfirst($experienceLevel) }}
                                </span>
                            @endif
                            @if($trainingLocation)
                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                                    <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    {{ ucfirst($trainingLocation) }}
                                </span>
                            @endif
                            @if($gender)
                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                                    <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    {{ ucfirst($gender) }}
                                </span>
                            @endif
                            @if($trainingPlan && isset($trainingPlan['frecuencia']))
                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-accent/10 px-2.5 py-1 text-xs font-medium text-wc-accent">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    {{ $trainingPlan['frecuencia'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Week counter --}}
                    <div class="flex shrink-0 flex-col items-end">
                        <div class="flex items-baseline gap-1">
                            <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ $currentWeek }}</span>
                            <span class="text-sm text-wc-text-tertiary">/ {{ $totalWeeks }}</span>
                        </div>
                        <p class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">Semana actual</p>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="mt-5">
                    <div class="flex items-center justify-between text-[11px] text-wc-text-tertiary mb-1.5">
                        <span>Progreso del programa</span>
                        <span class="font-data font-semibold text-wc-accent">{{ number_format($progressPct, 0) }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                        <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-amber-400 transition-all duration-700"
                             style="width: {{ $progressPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── TAB NAVIGATION ──────────────────────────────────────────────── --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-1" role="tablist" aria-label="Secciones del programa">
            <div class="grid grid-cols-3 gap-1">
                {{-- Training tab --}}
                <button
                    wire:click="setTab('training')"
                    role="tab"
                    aria-selected="{{ $activeTab === 'training' ? 'true' : 'false' }}"
                    aria-controls="panel-training"
                    class="flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-1
                        {{ $activeTab === 'training'
                            ? 'bg-wc-accent text-white shadow-sm'
                            : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary' }}"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" />
                    </svg>
                    <span class="hidden sm:inline">Entrenamiento</span>
                    <span class="sm:hidden text-xs">Entreno</span>
                </button>

                {{-- Nutrition tab --}}
                <button
                    wire:click="setTab('nutrition')"
                    role="tab"
                    aria-selected="{{ $activeTab === 'nutrition' ? 'true' : 'false' }}"
                    aria-controls="panel-nutrition"
                    class="flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-1
                        {{ $activeTab === 'nutrition'
                            ? 'bg-wc-accent text-white shadow-sm'
                            : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary' }}"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                    </svg>
                    <span class="hidden sm:inline">Nutricion</span>
                    <span class="sm:hidden text-xs">Nutricion</span>
                </button>

                {{-- Habits tab --}}
                <button
                    wire:click="setTab('habits')"
                    role="tab"
                    aria-selected="{{ $activeTab === 'habits' ? 'true' : 'false' }}"
                    aria-controls="panel-habits"
                    class="flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-1
                        {{ $activeTab === 'habits'
                            ? 'bg-wc-accent text-white shadow-sm'
                            : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary' }}"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                    <span class="text-xs sm:text-sm">Habitos</span>
                </button>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB: ENTRENAMIENTO                                                 --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'training')
            <div id="panel-training" role="tabpanel" aria-labelledby="tab-training" class="space-y-4">

                @if($trainingPlan)
                    {{-- Training plan objective banner --}}
                    @if(isset($trainingPlan['objetivo']))
                        <div class="flex items-start gap-3 rounded-card border border-wc-accent/20 bg-wc-accent/5 p-4">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-accent/70">Objetivo del plan</p>
                                <p class="mt-0.5 text-sm leading-relaxed text-wc-text-secondary">{{ $trainingPlan['objetivo'] }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Weeks accordion --}}
                    @if(!empty($trainingPlan['semanas']) && is_array($trainingPlan['semanas']))
                        <div class="space-y-3">
                            @foreach($trainingPlan['semanas'] as $semana)
                                @php
                                    $weekNum = $semana['numero'] ?? ($loop->iteration);
                                    $isCurrentWeek = $weekNum === $currentWeek;
                                    $fase = $semana['fase'] ?? null;
                                    $dias = $semana['dias'] ?? [];
                                @endphp

                                <div
                                    x-data="{ open: {{ $isCurrentWeek ? 'true' : 'false' }} }"
                                    class="overflow-hidden rounded-card border transition-colors {{ $isCurrentWeek ? 'border-wc-accent/30 bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary' }}"
                                >
                                    {{-- Week header --}}
                                    <button
                                        x-on:click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-4 text-left transition-colors hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-wc-accent"
                                        :aria-expanded="open ? 'true' : 'false'"
                                        aria-controls="week-{{ $weekNum }}-body"
                                    >
                                        <div class="flex items-center gap-3">
                                            {{-- Week number badge --}}
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg font-data text-sm font-bold
                                                {{ $isCurrentWeek ? 'bg-wc-accent text-white' : 'bg-wc-bg-secondary text-wc-text-tertiary' }}">
                                                {{ $weekNum }}
                                            </div>

                                            <div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-sm font-semibold text-wc-text">Semana {{ $weekNum }}</span>
                                                    @if($isCurrentWeek)
                                                        <span class="rounded-full bg-wc-accent px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Semana actual</span>
                                                    @endif
                                                    @if($fase)
                                                        <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary">{{ $fase }}</span>
                                                    @endif
                                                </div>
                                                @if(!empty($dias))
                                                    <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ count($dias) }} dia{{ count($dias) !== 1 ? 's' : '' }} de entrenamiento</p>
                                                @endif
                                            </div>
                                        </div>

                                        <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                                             :class="{ 'rotate-180': open }"
                                             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>

                                    {{-- Week body --}}
                                    <div
                                        x-show="open"
                                        x-collapse
                                        id="week-{{ $weekNum }}-body"
                                    >
                                        <div class="space-y-3 border-t border-wc-border/50 px-4 pb-4 pt-4">
                                            @forelse($dias as $dia)
                                                @php
                                                    $tipoDia = $dia['tipo'] ?? null;
                                                    $tipoBadgeClass = match(strtolower($tipoDia ?? '')) {
                                                        'empuje', 'push'    => 'bg-orange-500/10 text-orange-400',
                                                        'jale', 'pull'      => 'bg-blue-500/10 text-blue-400',
                                                        'piernas', 'legs'   => 'bg-violet-500/10 text-violet-400',
                                                        'full', 'full body' => 'bg-emerald-500/10 text-emerald-400',
                                                        'cardio'            => 'bg-sky-500/10 text-sky-400',
                                                        default             => 'bg-wc-accent/10 text-amber-400',
                                                    };
                                                    $ejercicios = $dia['ejercicios'] ?? [];
                                                @endphp

                                                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary">
                                                    {{-- Day header --}}
                                                    <div class="flex items-center justify-between gap-3 px-4 py-3.5">
                                                        <div class="flex items-center gap-3 min-w-0">
                                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-bg-tertiary">
                                                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" />
                                                                </svg>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="truncate text-sm font-semibold text-wc-text">
                                                                    {{ $dia['nombre'] ?? ('Dia ' . ($loop->iteration)) }}
                                                                </p>
                                                                @if(!empty($ejercicios))
                                                                    <p class="text-xs text-wc-text-tertiary">{{ count($ejercicios) }} ejercicio{{ count($ejercicios) !== 1 ? 's' : '' }}</p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="flex shrink-0 items-center gap-2">
                                                            @if($tipoDia)
                                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $tipoBadgeClass }}">
                                                                    {{ $tipoDia }}
                                                                </span>
                                                            @endif
                                                            @if(isset($dia['duracion']))
                                                                <span class="hidden rounded-full bg-wc-bg-tertiary px-2.5 py-1 text-[10px] font-medium text-wc-text-tertiary sm:inline-flex items-center gap-1">
                                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                    </svg>
                                                                    {{ $dia['duracion'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Duration on mobile --}}
                                                    @if(isset($dia['duracion']))
                                                        <div class="flex items-center gap-1.5 border-t border-wc-border/40 px-4 py-2 sm:hidden">
                                                            <svg class="h-3 w-3 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                            <span class="text-xs text-wc-text-tertiary">{{ $dia['duracion'] }}</span>
                                                        </div>
                                                    @endif

                                                    {{-- Warmup card --}}
                                                    @if(!empty($dia['calentamiento'] ?? $dia['warmup'] ?? null))
                                                        <div class="flex items-start gap-3 border-t border-amber-500/20 bg-gradient-to-r from-amber-500/8 to-transparent px-4 py-3">
                                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/15">
                                                                <svg class="h-3.5 w-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Calentamiento</p>
                                                                <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ $dia['calentamiento'] ?? $dia['warmup'] }}</p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Workout launch button --}}
                                                    @if(!empty($ejercicios))
                                                        <div class="border-t border-wc-border/40 px-4 py-2.5">
                                                            <a wire:navigate href="{{ route('rise.workout', ['day' => $loop->iteration]) }}"
                                                               class="flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
                                                                </svg>
                                                                Entrenar este dia
                                                            </a>
                                                        </div>
                                                    @endif

                                                    {{-- Exercises list --}}
                                                    @if(!empty($ejercicios))
                                                        <div class="divide-y divide-wc-border/40 border-t border-wc-border/40">
                                                            @foreach($ejercicios as $ejercicio)
                                                                <div class="flex items-start gap-3 px-4 py-3">
                                                                    {{-- Exercise number --}}
                                                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-wc-accent/10 font-data text-[11px] font-bold text-wc-accent">
                                                                        {{ $loop->iteration }}
                                                                    </span>

                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm font-medium text-wc-text">
                                                                            {{ $ejercicio['nombre'] ?? 'Ejercicio' }}
                                                                        </p>

                                                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                                            @if(isset($ejercicio['series']) && isset($ejercicio['repeticiones']))
                                                                                <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[11px] font-semibold text-wc-text-secondary">
                                                                                    {{ $ejercicio['series'] }} x {{ $ejercicio['repeticiones'] }}
                                                                                </span>
                                                                            @elseif(isset($ejercicio['series']))
                                                                                <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[11px] font-semibold text-wc-text-secondary">
                                                                                    {{ $ejercicio['series'] }} series
                                                                                </span>
                                                                            @endif

                                                                            @if(isset($ejercicio['descanso']))
                                                                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[11px] text-wc-text-tertiary">
                                                                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                                    </svg>
                                                                                    {{ $ejercicio['descanso'] }}
                                                                                </span>
                                                                            @endif
                                                                        </div>

                                                                        @if(!empty($ejercicio['notas']))
                                                                            <p class="mt-1.5 text-xs italic leading-relaxed text-wc-text-tertiary">
                                                                                {{ $ejercicio['notas'] }}
                                                                            </p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    {{-- Cooldown card --}}
                                                    @if(!empty($dia['vuelta_calma'] ?? $dia['cooldown'] ?? null))
                                                        <div class="flex items-start gap-3 border-t border-sky-500/20 bg-gradient-to-r from-sky-500/8 to-transparent px-4 py-3">
                                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/15">
                                                                <svg class="h-3.5 w-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-[10px] font-bold uppercase tracking-wider text-sky-400">Vuelta a la calma</p>
                                                                <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ $dia['vuelta_calma'] ?? $dia['cooldown'] }}</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-6 text-center">
                                                    <p class="text-sm text-wc-text-tertiary">Sin dias asignados esta semana.</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- No weeks data --}}
                        @include('livewire.rise.partials.program-empty', [
                            'icon' => 'dumbbell',
                            'title' => 'Plan en preparacion',
                            'message' => 'Tu coach esta preparando tu plan de entrenamiento semana a semana.',
                        ])
                    @endif

                @else
                    {{-- No training plan at all --}}
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-wc-accent/10">
                            <svg class="h-7 w-7 text-wc-accent/60" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" />
                            </svg>
                        </div>
                        <h3 class="font-display text-lg tracking-wide text-wc-text">Tu coach esta preparando tu plan</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">
                            El plan de entrenamiento personalizado estara disponible muy pronto. Te notificaremos cuando este listo.
                        </p>
                    </div>
                @endif
            </div>
        @endif


        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB: NUTRICION                                                     --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'nutrition')
            <div id="panel-nutrition" role="tabpanel" aria-labelledby="tab-nutrition" class="space-y-5">

                @if($nutritionPlan)
                    @php
                        $cals     = $nutritionPlan['calorias_diarias'] ?? 0;
                        $prot     = $nutritionPlan['proteina_g'] ?? 0;
                        $carbs    = $nutritionPlan['carbohidratos_g'] ?? 0;
                        $fats     = $nutritionPlan['grasas_g'] ?? 0;
                        $totalMacroKcal = ($prot * 4) + ($carbs * 4) + ($fats * 9);
                        $protPct  = $totalMacroKcal > 0 ? round(($prot * 4 / $totalMacroKcal) * 100) : 0;
                        $carbsPct = $totalMacroKcal > 0 ? round(($carbs * 4 / $totalMacroKcal) * 100) : 0;
                        $fatsPct  = $totalMacroKcal > 0 ? round(($fats * 9 / $totalMacroKcal) * 100) : 0;
                    @endphp

                    {{-- Calories hero + macro cards --}}
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        {{-- Calories --}}
                        @if($cals > 0)
                            <div class="relative overflow-hidden rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:col-span-1">
                                <div class="absolute inset-x-0 top-0 h-0.5 bg-emerald-500"></div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Calorias</p>
                                <p class="font-data mt-2 text-3xl font-bold tabular-nums text-wc-text">{{ number_format($cals) }}</p>
                                <p class="mt-0.5 text-xs font-medium text-emerald-400">kcal / dia</p>
                            </div>
                        @endif

                        {{-- Proteina --}}
                        @if($prot > 0)
                            <div class="relative overflow-hidden rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                                <div class="absolute inset-x-0 top-0 h-0.5" style="background: #F97316;"></div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Proteina</p>
                                <p class="font-data mt-2 text-3xl font-bold tabular-nums text-wc-text">{{ $prot }}<span class="text-base font-normal">g</span></p>
                                <p class="mt-0.5 text-xs font-medium" style="color: #F97316;">{{ $protPct }}% del total</p>
                            </div>
                        @endif

                        {{-- Carbohidratos --}}
                        @if($carbs > 0)
                            <div class="relative overflow-hidden rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                                <div class="absolute inset-x-0 top-0 h-0.5 bg-blue-500"></div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Carbos</p>
                                <p class="font-data mt-2 text-3xl font-bold tabular-nums text-wc-text">{{ $carbs }}<span class="text-base font-normal">g</span></p>
                                <p class="mt-0.5 text-xs font-medium text-blue-400">{{ $carbsPct }}% del total</p>
                            </div>
                        @endif

                        {{-- Grasas --}}
                        @if($fats > 0)
                            <div class="relative overflow-hidden rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                                <div class="absolute inset-x-0 top-0 h-0.5 bg-amber-400"></div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Grasas</p>
                                <p class="font-data mt-2 text-3xl font-bold tabular-nums text-wc-text">{{ $fats }}<span class="text-base font-normal">g</span></p>
                                <p class="mt-0.5 text-xs font-medium text-amber-400">{{ $fatsPct }}% del total</p>
                            </div>
                        @endif
                    </div>

                    {{-- Macro proportion bar --}}
                    @if($totalMacroKcal > 0)
                        <div
                            x-data="{ animate: false }"
                            x-init="setTimeout(() => animate = true, 150)"
                        >
                            <div class="mb-2 flex items-center justify-between text-[11px] text-wc-text-tertiary">
                                <span>Distribucion de macros</span>
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-full" style="background:#F97316;"></span>P</span>
                                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-full bg-blue-500"></span>C</span>
                                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-full bg-amber-400"></span>G</span>
                                </div>
                            </div>
                            <div class="flex h-3 w-full overflow-hidden rounded-full">
                                <div class="h-full transition-all duration-700"
                                     :style="{ width: animate ? '{{ $protPct }}%' : '0%', background: '#F97316' }"></div>
                                <div class="h-full transition-all duration-700 delay-100"
                                     :style="{ width: animate ? '{{ $carbsPct }}%' : '0%', background: '#3B82F6' }"></div>
                                <div class="h-full transition-all duration-700 delay-200"
                                     :style="{ width: animate ? '{{ $fatsPct }}%' : '0%', background: '#F59E0B' }"></div>
                            </div>
                        </div>
                    @endif

                    {{-- Objective --}}
                    @if(!empty($nutritionPlan['objetivo']))
                        <div class="flex items-start gap-3 rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo nutricional</p>
                                <p class="mt-0.5 text-sm text-wc-text-secondary">{{ $nutritionPlan['objetivo'] }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Nutritional tips --}}
                    @if(!empty($nutritionPlan['tips']) && is_array($nutritionPlan['tips']))
                        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                            <h3 class="font-display text-lg tracking-wide text-wc-text">TIPS NUTRICIONALES</h3>
                            <ul class="mt-4 space-y-2.5">
                                @foreach($nutritionPlan['tips'] as $tip)
                                    <li class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                                            <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        </div>
                                        <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $tip }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Suggested meals accordion --}}
                    @if(!empty($nutritionPlan['comidas_sugeridas']) && is_array($nutritionPlan['comidas_sugeridas']))
                        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                            <h3 class="font-display text-lg tracking-wide text-wc-text">COMIDAS SUGERIDAS</h3>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Opciones de alimentos por momento del dia</p>

                            <div class="mt-4 space-y-2">
                                @foreach($nutritionPlan['comidas_sugeridas'] as $comida)
                                    @php
                                        $nombreComida = $comida['nombre'] ?? 'Comida';
                                        $opciones = $comida['opciones'] ?? [];
                                        $nombreLower = strtolower($nombreComida);
                                        $iconBgClass = match(true) {
                                            str_contains($nombreLower, 'desayuno')        => 'bg-wc-accent/10 text-amber-400',
                                            str_contains($nombreLower, 'almuerzo')
                                                || str_contains($nombreLower, 'comida')   => 'bg-blue-500/10 text-blue-400',
                                            str_contains($nombreLower, 'cena')            => 'bg-indigo-500/10 text-indigo-400',
                                            str_contains($nombreLower, 'pre-entreno')
                                                || str_contains($nombreLower, 'pre ')     => 'bg-emerald-500/10 text-emerald-400',
                                            str_contains($nombreLower, 'post')            => 'bg-orange-500/10 text-orange-400',
                                            str_contains($nombreLower, 'snack')
                                                || str_contains($nombreLower, 'merienda') => 'bg-pink-500/10 text-pink-400',
                                            default                                       => 'bg-wc-accent/10 text-amber-400',
                                        };
                                    @endphp

                                    <div
                                        x-data="{ open: false }"
                                        class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary"
                                    >
                                        <button
                                            x-on:click="open = !open"
                                            class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-wc-accent"
                                            :aria-expanded="open ? 'true' : 'false'"
                                        >
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $iconBgClass }}">
                                                <span class="font-data text-sm font-bold">{{ $loop->iteration }}</span>
                                            </div>

                                            <div class="flex-1">
                                                <p class="font-display text-sm tracking-wide text-wc-text">{{ strtoupper($nombreComida) }}</p>
                                                @if(!empty($opciones))
                                                    <p class="text-[11px] text-wc-text-tertiary">{{ count($opciones) }} opcion{{ count($opciones) !== 1 ? 'es' : '' }}</p>
                                                @endif
                                            </div>

                                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                                                 :class="{ 'rotate-180': open }"
                                                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>

                                        <div x-show="open" x-collapse class="border-t border-wc-border/50">
                                            <ul class="space-y-2 p-4">
                                                @foreach($opciones as $opcion)
                                                    <li class="flex items-start gap-2.5">
                                                        <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                                                        <span class="text-sm leading-relaxed text-wc-text-secondary">{{ $opcion }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                @else
                    {{-- No nutrition plan --}}
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-wc-accent/10">
                            <svg class="h-7 w-7 text-wc-accent/60" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-lg tracking-wide text-wc-text">Tu plan esta en camino</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">
                            Tu coach esta disenando tu plan de nutricion personalizado. Te notificaremos cuando este listo.
                        </p>
                    </div>
                @endif
            </div>
        @endif


        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB: HABITOS                                                       --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'habits')
            <div id="panel-habits" role="tabpanel" aria-labelledby="tab-habits" class="space-y-4">

                @if(!empty($habitsPlan))
                    <p class="text-sm text-wc-text-tertiary">
                        {{ count($habitsPlan) }} habito{{ count($habitsPlan) !== 1 ? 's' : '' }} diseñados para tu estilo de vida
                    </p>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($habitsPlan as $habito)
                            @php
                                $nombreHabito = $habito['nombre'] ?? 'Habito';
                                $descripcion  = $habito['descripcion'] ?? null;
                                $razon        = $habito['razon'] ?? null;
                                $frecuencia   = $habito['frecuencia'] ?? null;

                                // Assign a consistent accent color by loop position
                                $accentColors = [
                                    'amber'   => ['border' => 'border-wc-accent/25',   'bg' => 'bg-wc-accent/5',    'icon_bg' => 'bg-wc-accent/10',    'icon_text' => 'text-wc-accent',    'badge' => 'bg-wc-accent/10 text-wc-accent'],
                                    'emerald' => ['border' => 'border-emerald-500/25', 'bg' => 'bg-emerald-500/5',  'icon_bg' => 'bg-emerald-500/10',  'icon_text' => 'text-emerald-400',  'badge' => 'bg-emerald-500/10 text-emerald-400'],
                                    'blue'    => ['border' => 'border-blue-500/25',    'bg' => 'bg-blue-500/5',     'icon_bg' => 'bg-blue-500/10',     'icon_text' => 'text-blue-400',     'badge' => 'bg-blue-500/10 text-blue-400'],
                                    'violet'  => ['border' => 'border-violet-500/25',  'bg' => 'bg-violet-500/5',   'icon_bg' => 'bg-violet-500/10',   'icon_text' => 'text-violet-400',   'badge' => 'bg-violet-500/10 text-violet-400'],
                                    'sky'     => ['border' => 'border-sky-500/25',     'bg' => 'bg-sky-500/5',      'icon_bg' => 'bg-sky-500/10',      'icon_text' => 'text-sky-400',      'badge' => 'bg-sky-500/10 text-sky-400'],
                                    'orange'  => ['border' => 'border-orange-500/25',  'bg' => 'bg-orange-500/5',   'icon_bg' => 'bg-orange-500/10',   'icon_text' => 'text-orange-400',   'badge' => 'bg-orange-500/10 text-orange-400'],
                                ];
                                $colorKeys = array_keys($accentColors);
                                $color = $accentColors[$colorKeys[$loop->index % count($colorKeys)]];
                            @endphp

                            <div class="flex flex-col rounded-card border {{ $color['border'] }} {{ $color['bg'] }} p-5">
                                {{-- Habit header --}}
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $color['icon_bg'] }}">
                                        <svg class="h-5 w-5 {{ $color['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-wc-text leading-tight">{{ $nombreHabito }}</h3>
                                        @if($frecuencia)
                                            <span class="mt-1.5 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $color['badge'] }}">
                                                {{ $frecuencia }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Description --}}
                                @if($descripcion)
                                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">{{ $descripcion }}</p>
                                @endif

                                {{-- Reason / why it matters --}}
                                @if($razon)
                                    <div class="mt-3 flex items-start gap-2 rounded-lg border border-wc-border/50 bg-wc-bg-secondary/50 p-3">
                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                        </svg>
                                        <p class="text-xs leading-relaxed text-wc-text-tertiary">{{ $razon }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                @else
                    {{-- No habits plan --}}
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-wc-accent/10">
                            <svg class="h-7 w-7 text-wc-accent/60" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-lg tracking-wide text-wc-text">Habitos en preparacion</h3>
                        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">
                            Tu coach esta definiendo los habitos clave para tu transformacion. Estaran disponibles pronto.
                        </p>
                    </div>
                @endif
            </div>
        @endif

    @else
        {{-- ─── NO ACTIVE PROGRAM ───────────────────────────────────────────── --}}
        <div class="rounded-card border border-wc-accent/20 bg-wc-accent/5 px-6 py-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-8 w-8 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
            <h3 class="font-display text-xl tracking-wide text-wc-text">No tienes un programa RISE activo</h3>
            <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">
                Contacta a tu coach para activar tu programa RISE de {{ $totalWeeks }} semanas.
            </p>
        </div>
    @endif

</div>
