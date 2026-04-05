<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu programación personalizada, diseñada por tu coach</p>
    </div>

    {{-- Tabs --}}
    @php
        $canAccessNutricion = in_array($clientPlanType, ['esencial', 'metodo', 'elite', 'presencial', 'rise']);
        $canAccessElite     = in_array($clientPlanType, ['elite']);
        $tabs = [
            'entrenamiento' => 'Entrenamiento',
            'habitos'       => 'Hábitos',
            'nutricion'     => 'Nutrición',
            'suplementacion'=> 'Suplementos',
            'ciclo'         => 'Ciclo',
            'bloodwork'     => 'Bloodwork',
        ];
    @endphp
    <div class="mb-6 rounded-card border border-wc-border bg-wc-bg-tertiary p-1 wc-glass" role="tablist" aria-label="Secciones del plan">
        <div class="flex gap-1 overflow-x-auto">
            @foreach($tabs as $key => $label)
                @php
                    $locked = (in_array($key, ['nutricion','suplementacion']) && !$canAccessNutricion)
                           || (in_array($key, ['ciclo','bloodwork']) && !$canAccessElite);
                @endphp
                <button
                    @if(!$locked) wire:click="setTab('{{ $key }}')" @endif
                    role="tab"
                    aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}"
                    @class([
                        'shrink-0 flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-1',
                        'bg-wc-accent text-white shadow-sm' => $activeTab === $key,
                        'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary' => $activeTab !== $key && !$locked,
                        'cursor-not-allowed opacity-40' => $locked,
                    ])
                >
                    {{ $label }}
                    @if($locked)
                        <span class="ml-1 text-xs">🔒</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tab Content --}}

    {{-- ==================== TAB: ENTRENAMIENTO ==================== --}}
    @if($activeTab === 'entrenamiento')
        @if($trainingPlan)
            @php
                $semanas = $trainingPlan['semanas'] ?? [];
                $planObjetivoE = $trainingPlan['objetivo'] ?? $trainingPlan['objetivo_general'] ?? null;
                $planFrecuencia = $trainingPlan['frecuencia'] ?? null;
                $planSplit = $trainingPlan['split'] ?? $trainingPlan['metodologia'] ?? null;

                // Count totals from first week (representative)
                $firstWeekDias = !empty($semanas) ? ($semanas[0]['dias'] ?? []) : [];
                $totalDaysPerWeek = count($firstWeekDias);
                $totalExercises = 0;
                foreach ($firstWeekDias as $d) {
                    $totalExercises += count($d['ejercicios'] ?? []);
                }
                $estimatedWeeklyMin = max($totalDaysPerWeek * 45, 20);

                // Type badge colors for day types
                $tipoBadgeClass = function(?string $tipo): string {
                    return match(strtolower($tipo ?? '')) {
                        'empuje', 'push'    => 'bg-orange-500/10 text-orange-400',
                        'jale', 'pull'      => 'bg-blue-500/10 text-blue-400',
                        'piernas', 'legs', 'pierna' => 'bg-violet-500/10 text-violet-400',
                        'full', 'full body' => 'bg-emerald-500/10 text-emerald-400',
                        'cardio'            => 'bg-sky-500/10 text-sky-400',
                        'upper', 'tren superior' => 'bg-rose-500/10 text-rose-400',
                        'lower', 'tren inferior' => 'bg-teal-500/10 text-teal-400',
                        default             => 'bg-wc-accent/10 text-wc-accent',
                    };
                };
            @endphp

            {{-- ─── PROGRAM OVERVIEW CARD (premium) ──────────────────────── --}}
            <div class="relative mb-6 overflow-hidden rounded-card border border-wc-accent/20 bg-gradient-to-br from-wc-accent/8 via-wc-bg-tertiary to-transparent p-5 sm:p-6 wc-topline wc-grain">
                {{-- Premium decorative orbs --}}
                <div class="wc-orb-tr"></div>
                <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/6"></div>

                <div class="relative">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            {{-- Plan badge --}}
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-red-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                                    <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    Plan {{ ucfirst($clientPlanType) }}
                                </span>
                                <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Activo</span>
                            </div>

                            {{-- Start date --}}
                            @if($planStartDate)
                                <p class="mt-2 text-sm text-wc-text-secondary">Inicio: {{ $planStartDate }}</p>
                            @endif

                            {{-- Program attributes --}}
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if($totalDaysPerWeek > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-wc-accent/10 px-2.5 py-1 text-xs font-medium text-wc-accent">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                        {{ $planFrecuencia ?? $totalDaysPerWeek . ' dias/semana' }}
                                    </span>
                                @endif
                                @if($planSplit)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                                        <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" />
                                        </svg>
                                        {{ $planSplit }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Week counter --}}
                        @if($totalWeeks > 1)
                            <div class="flex shrink-0 flex-col items-end">
                                <div class="flex items-baseline gap-1">
                                    <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ $currentWeek }}</span>
                                    <span class="text-sm text-wc-text-tertiary">/ {{ $totalWeeks }}</span>
                                </div>
                                <p class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">Semana actual</p>
                            </div>
                        @endif
                    </div>

                    {{-- Progress bar --}}
                    @if($totalWeeks > 1)
                        <div class="mt-5">
                            <div class="flex items-center justify-between text-[11px] text-wc-text-tertiary mb-1.5">
                                <span>Progreso del programa</span>
                                <span class="font-data font-semibold text-wc-accent">{{ number_format($progressPct, 0) }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                                <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700"
                                     style="width: {{ $progressPct }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ─── OBJETIVO BANNER ──────────────────────────────────────── --}}
            @if($planObjetivoE)
                <div class="mb-5 flex items-start gap-3 rounded-card border border-wc-accent/20 bg-wc-accent/5 p-4">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-accent/70">Objetivo del plan</p>
                        <p class="mt-0.5 text-sm leading-relaxed text-wc-text-secondary">{{ $planObjetivoE }}</p>
                    </div>
                </div>
            @endif

            {{-- ─── WEEKS ACCORDION ──────────────────────────────────────── --}}
            @if(!empty($semanas))
                <div class="space-y-3">
                    @foreach($semanas as $semana)
                        @php
                            $weekNum = $semana['numero'] ?? ($loop->iteration);
                            $isCurrentWeek = $weekNum == $currentWeek;
                            $fase = $semana['fase'] ?? null;
                            $weekDias = $semana['dias'] ?? [];
                        @endphp

                        <div
                            x-data="{ open: {{ $isCurrentWeek ? 'true' : 'false' }} }"
                            class="overflow-hidden rounded-card border transition-colors {{ $isCurrentWeek ? 'border-wc-accent/30 bg-wc-accent/5 wc-topline' : 'border-wc-border bg-wc-bg-tertiary' }}"
                        >
                            {{-- Week header --}}
                            <button
                                x-on:click="open = !open"
                                class="flex w-full items-center justify-between px-4 py-4 text-left transition-colors hover:bg-black/5 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-wc-accent"
                                :aria-expanded="open ? 'true' : 'false'"
                            >
                                <div class="flex items-center gap-3">
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
                                        @if(!empty($weekDias))
                                            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ count($weekDias) }} dia{{ count($weekDias) !== 1 ? 's' : '' }} de entrenamiento</p>
                                        @endif
                                    </div>
                                </div>

                                <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                                     :class="{ 'rotate-180': open }"
                                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            {{-- Week body with days --}}
                            <div x-show="open" x-collapse>
                                <div class="space-y-3 border-t border-wc-border/50 px-4 pb-4 pt-4">
                                    @forelse($weekDias as $dia)
                                        @php
                                            $tipoDia = $dia['tipo'] ?? $dia['grupo_muscular'] ?? $dia['muscle_group'] ?? null;
                                            $ejercicios = $dia['ejercicios'] ?? [];
                                            $dayName = $dia['nombre'] ?? $dia['name'] ?? $dia['dia'] ?? ('Dia ' . ($loop->iteration));
                                            $duracion = $dia['duracion'] ?? (count($ejercicios) > 0 ? '~' . max(count($ejercicios) * 6, 15) . ' min' : null);
                                            $warmup = $dia['calentamiento'] ?? $dia['warmup'] ?? null;
                                            $cooldown = $dia['vuelta_calma'] ?? $dia['cooldown'] ?? null;
                                        @endphp

                                        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary wc-lift">
                                            {{-- Day header --}}
                                            <div class="flex items-center justify-between gap-3 px-4 py-3.5">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                                                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" />
                                                        </svg>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-wc-text">{{ $dayName }}</p>
                                                        @if(!empty($ejercicios))
                                                            <p class="text-xs text-wc-text-tertiary">{{ count($ejercicios) }} ejercicio{{ count($ejercicios) !== 1 ? 's' : '' }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex shrink-0 items-center gap-2">
                                                    @if($tipoDia)
                                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $tipoBadgeClass($tipoDia) }}">
                                                            {{ $tipoDia }}
                                                        </span>
                                                    @endif
                                                    @if($duracion)
                                                        <span class="hidden rounded-full bg-wc-bg-tertiary px-2.5 py-1 text-[10px] font-medium text-wc-text-tertiary sm:inline-flex items-center gap-1">
                                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                            {{ $duracion }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Warmup card --}}
                                            @if($warmup)
                                                <div class="flex items-start gap-3 border-t border-amber-500/20 bg-gradient-to-r from-amber-500/8 to-transparent px-4 py-3">
                                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/15">
                                                        <svg class="h-3.5 w-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Calentamiento</p>
                                                        <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ $warmup }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Workout launch button --}}
                                            @if(!empty($ejercicios))
                                                <div class="border-t border-wc-border/40 px-4 py-2.5">
                                                    <a wire:navigate href="{{ route('client.workout', ['day' => $loop->iteration]) }}"
                                                       class="flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors btn-ripple btn-press shadow-lg shadow-wc-accent/20">
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
                                                        @php
                                                            $ejName   = is_array($ejercicio) ? ($ejercicio['nombre'] ?? $ejercicio['name'] ?? $ejercicio['ejercicio'] ?? 'Ejercicio') : (string) $ejercicio;
                                                            $ejSeries = is_array($ejercicio) ? ($ejercicio['series'] ?? $ejercicio['sets'] ?? null) : null;
                                                            $ejReps   = is_array($ejercicio) ? ($ejercicio['repeticiones'] ?? $ejercicio['reps'] ?? null) : null;
                                                            $ejRest   = is_array($ejercicio) ? ($ejercicio['descanso'] ?? $ejercicio['rest'] ?? $ejercicio['rest_seconds'] ?? null) : null;
                                                            $ejRir    = is_array($ejercicio) ? ($ejercicio['rir'] ?? null) : null;
                                                            $ejNotas  = is_array($ejercicio) ? ($ejercicio['notas'] ?? $ejercicio['notes'] ?? null) : null;
                                                            $rirClass = $ejRir !== null
                                                                ? ($ejRir >= 3 ? 'bg-emerald-500/15 text-emerald-400' : ($ejRir >= 2 ? 'bg-amber-500/15 text-amber-400' : 'bg-red-500/15 text-red-400'))
                                                                : '';
                                                        @endphp
                                                        <div class="flex items-start gap-3 px-4 py-3">
                                                            {{-- Exercise number --}}
                                                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-wc-accent/10 font-data text-[11px] font-bold text-wc-accent">
                                                                {{ $loop->iteration }}
                                                            </span>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-wc-text">{{ $ejName }}</p>
                                                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                                    @if($ejSeries || $ejReps)
                                                                        <span class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[11px] font-semibold text-wc-text-secondary">
                                                                            @if($ejSeries && $ejReps){{ $ejSeries }} x {{ $ejReps }}
                                                                            @elseif($ejSeries){{ $ejSeries }} series
                                                                            @else{{ $ejReps }} reps
                                                                            @endif
                                                                        </span>
                                                                    @endif
                                                                    @if($ejRest)
                                                                        <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[11px] text-wc-text-tertiary">
                                                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                            </svg>
                                                                            {{ is_numeric($ejRest) ? $ejRest.'s' : $ejRest }}
                                                                        </span>
                                                                    @endif
                                                                    @if($ejRir !== null)
                                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $rirClass }}">RIR{{ $ejRir }}</span>
                                                                    @endif
                                                                </div>
                                                                @if(!empty($ejNotas))
                                                                    <p class="mt-1.5 text-xs italic leading-relaxed text-wc-text-tertiary">{{ $ejNotas }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Cooldown card --}}
                                            @if($cooldown)
                                                <div class="flex items-start gap-3 border-t border-sky-500/20 bg-gradient-to-r from-sky-500/8 to-transparent px-4 py-3">
                                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/15">
                                                        <svg class="h-3.5 w-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-[10px] font-bold uppercase tracking-wider text-sky-400">Vuelta a la calma</p>
                                                        <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ $cooldown }}</p>
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
                <x-empty-state title="PLAN EN PREPARACIÓN" message="Tu coach está diseñando tu plan de entrenamiento. Te notificaremos cuando esté listo." />
            @endif

        @else
            <x-empty-state title="PLAN EN PREPARACIÓN" message="Tu coach está diseñando tu plan de entrenamiento. Te notificaremos cuando esté listo." />
        @endif

    {{-- ==================== TAB: NUTRICION ==================== --}}
    @elseif($activeTab === 'nutricion')
        @if($canAccessNutricion)
            @livewire('client.nutrition-plan')
        @else
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Nutrición Premium</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Método y Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
            </div>
        @endif

    {{-- ==================== TAB: SUPLEMENTACION ==================== --}}
    @elseif($activeTab === 'suplementacion')
        @if($canAccessNutricion)
            @if($supplementPlan)
                @php
                    $supDescripcion     = $supplementPlan['descripcion_protocolo'] ?? $supplementPlan['descripcion'] ?? null;
                    $supPerfilCliente   = $supplementPlan['perfil_cliente'] ?? null;
                    $supAdvertencia     = $supplementPlan['advertencia'] ?? null;
                    $supCoachNotes      = $supplementPlan['notas_coach'] ?? $supplementPlan['coach_notes'] ?? $supplementPlan['notas'] ?? null;
                    $supMensajeFinal    = $supplementPlan['mensaje_final'] ?? null;

                    // Estructura 1: categorias[].suplementos (formato Jairo/IA)
                    $supCategorias = $supplementPlan['categorias'] ?? null;

                    // Estructura 2: lista plana de suplementos
                    $supList = $supplementPlan['suplementos'] ?? $supplementPlan['supplements'] ?? $supplementPlan['protocolo'] ?? [];

                    // Timing — acepta tanto 'timing_diario' como 'timing' o 'horarios'
                    $supTimingDiario    = $supplementPlan['timing_diario'] ?? null;
                    $supTimingGroups    = $supplementPlan['timing'] ?? $supplementPlan['horarios'] ?? null;

                    // Sinergias
                    $supSinergias = $supplementPlan['sinergias'] ?? null;

                    $timingIcons = [
                        'mañana' => '🌅', 'manana' => '🌅', 'morning' => '🌅',
                        'pre-entreno' => '⚡', 'pre entreno' => '⚡', 'pre-workout' => '⚡',
                        'post-entreno' => '🔄', 'post entreno' => '🔄', 'post-workout' => '🔄',
                        'con comidas' => '🍽️', 'con comida' => '🍽️',
                        'noche' => '🌙', 'night' => '🌙', 'antes de dormir' => '🌙',
                        'diario' => '📅', 'daily' => '📅',
                    ];
                    $getTimingIcon = fn($timing) => collect($timingIcons)->first(
                        fn($v, $k) => str_contains(mb_strtolower($timing ?? ''), $k), '💊'
                    );

                    $prioridadColor = fn($p) => match(mb_strtolower($p ?? '')) {
                        'esencial' => ['bg' => 'bg-wc-accent/10', 'text' => 'text-wc-accent', 'border' => 'border-wc-accent/30'],
                        'recomendado' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/25'],
                        default => ['bg' => 'bg-wc-bg-secondary', 'text' => 'text-wc-text-tertiary', 'border' => 'border-wc-border'],
                    };
                @endphp

                <div class="space-y-5">
                    {{-- Descripción / perfil del cliente --}}
                    @if($supDescripcion)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Protocolo</p>
                                    <p class="mt-0.5 text-sm font-medium text-wc-text">{{ $supDescripcion }}</p>
                                    @if($supPerfilCliente)
                                        <p class="mt-1 text-xs text-wc-text-tertiary">{{ $supPerfilCliente }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Advertencia --}}
                    @if($supAdvertencia)
                        <div class="rounded-xl border border-amber-500/30 bg-amber-500/8 p-4">
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                <p class="text-xs leading-relaxed text-amber-300">{{ $supAdvertencia }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- ESTRUCTURA A: Categorías (formato IA nuevo) --}}
                    @if($supCategorias && is_array($supCategorias))
                        @php
                            $catIconMap = [
                                'rendimiento'  => ['icon' => '⚡', 'color' => 'text-amber-400', 'bg' => 'bg-amber-500/10', 'border' => 'border-amber-500/20'],
                                'protección'   => ['icon' => '🛡️', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20'],
                                'proteccion'   => ['icon' => '🛡️', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20'],
                                'salud'        => ['icon' => '❤️', 'color' => 'text-sky-400',     'bg' => 'bg-sky-500/10',     'border' => 'border-sky-500/20'],
                                'recuperación' => ['icon' => '🔄', 'color' => 'text-purple-400',  'bg' => 'bg-purple-500/10',  'border' => 'border-purple-500/20'],
                                'recuperacion' => ['icon' => '🔄', 'color' => 'text-purple-400',  'bg' => 'bg-purple-500/10',  'border' => 'border-purple-500/20'],
                            ];
                            $getCatStyle = function(string $nombre) use ($catIconMap): array {
                                $lower = mb_strtolower($nombre);
                                foreach ($catIconMap as $key => $style) {
                                    if (str_contains($lower, $key)) return $style;
                                }
                                return ['icon' => '💊', 'color' => 'text-wc-text-secondary', 'bg' => 'bg-wc-bg-secondary', 'border' => 'border-wc-border'];
                            };
                        @endphp

                        <div class="space-y-4">
                            @foreach($supCategorias as $categoria)
                                @php
                                    $catNombre = $categoria['nombre'] ?? 'Suplementos';
                                    $catStyle  = $getCatStyle($catNombre);
                                    $catSups   = $categoria['suplementos'] ?? [];
                                @endphp
                                <div class="overflow-hidden rounded-xl border {{ $catStyle['border'] }} bg-wc-bg-tertiary">
                                    {{-- Category header --}}
                                    <div class="flex items-center gap-2 border-b {{ $catStyle['border'] }} px-5 py-3 {{ $catStyle['bg'] }}">
                                        <span class="text-base">{{ $catStyle['icon'] }}</span>
                                        <h3 class="font-display text-sm tracking-wider {{ $catStyle['color'] }}">{{ strtoupper($catNombre) }}</h3>
                                        <span class="ml-auto rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-semibold text-wc-text-tertiary">
                                            {{ count($catSups) }} item{{ count($catSups) !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    {{-- Supplements in category --}}
                                    <div class="divide-y divide-wc-border">
                                        @foreach($catSups as $idx => $sup)
                                            @php
                                                $supNombre    = is_array($sup) ? ($sup['nombre'] ?? $sup['name'] ?? "Suplemento " . ($idx+1)) : $sup;
                                                $supDosis     = is_array($sup) ? ($sup['dosis'] ?? $sup['dose'] ?? null) : null;
                                                $supTiming    = is_array($sup) ? ($sup['timing'] ?? $sup['momento'] ?? $sup['horario'] ?? null) : null;
                                                $supNotas     = is_array($sup) ? ($sup['notas'] ?? $sup['notes'] ?? null) : null;
                                                $supPrioridad = is_array($sup) ? ($sup['prioridad'] ?? null) : null;
                                                $prioStyle    = $prioridadColor($supPrioridad);
                                                $timIcon      = $getTimingIcon($supTiming);
                                            @endphp
                                            <div class="px-5 py-4">
                                                <div class="flex flex-wrap items-start gap-x-3 gap-y-1">
                                                    <span class="font-semibold text-wc-text">{{ $supNombre }}</span>
                                                    @if($supDosis)
                                                        <span class="rounded bg-wc-bg-secondary px-2 py-0.5 font-data text-xs font-bold text-wc-accent">{{ $supDosis }}</span>
                                                    @endif
                                                    @if($supPrioridad)
                                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $prioStyle['text'] }} {{ $prioStyle['bg'] }}">
                                                            {{ $supPrioridad }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($supTiming)
                                                    <p class="mt-1.5 inline-flex items-center gap-1 text-xs text-wc-text-secondary">
                                                        <span>{{ $timIcon }}</span>
                                                        <span>{{ $supTiming }}</span>
                                                    </p>
                                                @endif
                                                @if($supNotas)
                                                    <p class="mt-1 text-xs leading-relaxed text-wc-text-tertiary">{{ $supNotas }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    {{-- ESTRUCTURA B: Lista plana --}}
                    @elseif(count($supList) > 0)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                            <div class="flex items-center justify-between border-b border-wc-border px-5 py-4">
                                <h3 class="font-display text-lg tracking-wide text-wc-text">SUPLEMENTOS</h3>
                                <span class="rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-semibold text-wc-accent">{{ count($supList) }}</span>
                            </div>
                            <div class="divide-y divide-wc-border">
                                @foreach($supList as $idx => $sup)
                                    @php
                                        $supNombre = is_array($sup) ? ($sup['nombre'] ?? $sup['name'] ?? "Suplemento ".($idx+1)) : $sup;
                                        $supDosis  = is_array($sup) ? ($sup['dosis'] ?? $sup['dose'] ?? null) : null;
                                        $supMom    = is_array($sup) ? ($sup['momento'] ?? $sup['timing'] ?? $sup['horario'] ?? null) : null;
                                        $supNotas  = is_array($sup) ? ($sup['notas'] ?? $sup['notes'] ?? null) : null;
                                    @endphp
                                    <div class="flex items-start gap-4 px-5 py-4">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary">
                                            <span class="font-data text-xs font-bold text-wc-accent">{{ $idx + 1 }}</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-baseline gap-2">
                                                <span class="font-semibold text-wc-text">{{ $supNombre }}</span>
                                                @if($supDosis)<span class="rounded bg-wc-bg-secondary px-2 py-0.5 font-data text-xs font-semibold text-wc-accent">{{ $supDosis }}</span>@endif
                                            </div>
                                            @if($supMom)<p class="mt-1 text-xs text-wc-text-secondary">{{ $getTimingIcon($supMom) }} {{ $supMom }}</p>@endif
                                            @if($supNotas)<p class="mt-1 text-xs text-wc-text-tertiary">{{ $supNotas }}</p>@endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                            <p class="text-sm text-wc-text-secondary">Tu coach está preparando tu protocolo de suplementación.</p>
                        </div>
                    @endif

                    {{-- Timing Diario (nuevo formato: timing_diario[]) --}}
                    @if($supTimingDiario && is_array($supTimingDiario))
                        <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                            <div class="border-b border-wc-border px-5 py-4">
                                <h3 class="font-display text-sm tracking-wider text-wc-text">PROTOCOLO DIARIO</h3>
                            </div>
                            <div class="divide-y divide-wc-border">
                                @foreach($supTimingDiario as $momento)
                                    @php
                                        $momentoLabel = is_array($momento) ? ($momento['momento'] ?? '') : '';
                                        $momentoSups  = is_array($momento) ? ($momento['suplementos'] ?? '') : $momento;
                                    @endphp
                                    <div class="flex items-start gap-4 px-5 py-3.5">
                                        <span class="mt-0.5 shrink-0 text-base">{{ $getTimingIcon($momentoLabel) }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-wc-text">{{ $momentoLabel }}</p>
                                            <p class="mt-0.5 text-xs text-wc-text-secondary">{{ $momentoSups }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Timing Groups (formato antiguo) --}}
                    @if($supTimingGroups && is_array($supTimingGroups))
                        <div class="space-y-3">
                            <h3 class="font-display text-sm tracking-wider text-wc-text-tertiary uppercase px-1">PROTOCOLO POR MOMENTO</h3>
                            @foreach($supTimingGroups as $moment => $items)
                                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                                    <p class="mb-3 font-display text-sm tracking-wide text-wc-text">{{ $getTimingIcon($moment) }} {{ strtoupper($moment) }}</p>
                                    <ul class="space-y-1.5">
                                        @foreach((array) $items as $item)
                                            <li class="flex items-center gap-2 text-sm text-wc-text-secondary">
                                                <span class="h-1.5 w-1.5 rounded-full bg-wc-accent shrink-0"></span>
                                                {{ is_array($item) ? ($item['nombre'] ?? $item['name'] ?? json_encode($item)) : $item }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Sinergias --}}
                    @if($supSinergias && is_array($supSinergias))
                        <div class="overflow-hidden rounded-xl border border-sky-500/20 bg-sky-500/5">
                            <div class="border-b border-sky-500/20 px-5 py-3">
                                <h3 class="font-display text-sm tracking-wider text-sky-400">SINERGIAS CLAVE</h3>
                            </div>
                            <div class="divide-y divide-sky-500/10">
                                @foreach($supSinergias as $sinergia)
                                    <div class="px-5 py-4">
                                        <p class="text-sm font-semibold text-sky-300">{{ $sinergia['titulo'] ?? '' }}</p>
                                        <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">{{ $sinergia['explicacion'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Coach notes --}}
                    @if($supCoachNotes)
                        <div class="rounded-xl border-l-4 border-wc-accent bg-wc-bg-tertiary p-5">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-accent">Notas del coach</p>
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $supCoachNotes }}</p>
                            @if($supMensajeFinal)
                                <p class="mt-3 text-xs italic text-wc-text-tertiary">{{ $supMensajeFinal }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <x-empty-state title="PLAN EN PREPARACIÓN" message="Tu coach está preparando tu plan de suplementación. Te notificaremos cuando esté listo." />
            @endif
        @else
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Suplementación Premium</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Método y Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
            </div>
        @endif

    {{-- ==================== TAB: HABITOS ==================== --}}
    @elseif($activeTab === 'habitos')
        <div class="space-y-6">
            {{-- Compliance bar (premium) --}}
            <div class="relative overflow-hidden rounded-card border border-wc-accent/20 bg-gradient-to-br from-wc-accent/8 via-wc-bg-tertiary to-transparent p-5">
                <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-wc-accent/6"></div>
                <div class="pointer-events-none absolute -right-2 -top-2 h-12 w-12 rounded-full bg-wc-accent/10"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <h3 class="font-display text-lg tracking-wide text-wc-text">CUMPLIMIENTO MENSUAL</h3>
                        <p class="mt-0.5 text-sm text-wc-text-secondary">Dias con al menos 1 habito registrado este mes</p>
                    </div>
                    <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ $habitCompliance }}%</span>
                </div>
                <div class="relative mt-3 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700" style="width: {{ $habitCompliance }}%"></div>
                </div>
            </div>

            {{-- Habit Cards (premium with accent colors per type) --}}
            @php
                $habitAccents = [
                    'agua'          => ['color' => 'blue',   'bg' => 'bg-blue-500/10',   'border' => 'border-blue-500/25',   'text' => 'text-blue-400',   'bar' => 'bg-blue-500'],
                    'sueno'         => ['color' => 'indigo', 'bg' => 'bg-indigo-500/10', 'border' => 'border-indigo-500/25', 'text' => 'text-indigo-400', 'bar' => 'bg-indigo-500'],
                    'entrenamiento' => ['color' => 'red',    'bg' => 'bg-wc-accent/10',  'border' => 'border-wc-accent/25',  'text' => 'text-wc-accent',  'bar' => 'bg-wc-accent'],
                    'nutricion'     => ['color' => 'green',  'bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400', 'bar' => 'bg-emerald-500'],
                    'suplementos'   => ['color' => 'purple', 'bg' => 'bg-violet-500/10', 'border' => 'border-violet-500/25', 'text' => 'text-violet-400', 'bar' => 'bg-violet-500'],
                ];
            @endphp
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($habitData as $habit)
                    @php $hAccent = $habitAccents[$habit['type']] ?? $habitAccents['entrenamiento']; @endphp
                    <div class="rounded-card border {{ $hAccent['border'] }} {{ $hAccent['bg'] }} p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-display text-base tracking-wide text-wc-text">{{ strtoupper($habit['label']) }}</h4>
                                <p class="mt-1 text-xs text-wc-text-tertiary">
                                    Racha: <span class="font-data font-semibold {{ $hAccent['text'] }}">{{ $habit['streak'] }} dias</span>
                                </p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $hAccent['bg'] }}">
                                @if($habit['icon'] === 'droplet')
                                    <svg class="h-5 w-5 {{ $hAccent['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c-4.97 0-9-4.03-9-9 0-3.87 4.5-9.5 7.68-12.38a1.74 1.74 0 012.64 0C16.5 2.5 21 8.13 21 12c0 4.97-4.03 9-9 9z"/></svg>
                                @elseif($habit['icon'] === 'moon')
                                    <svg class="h-5 w-5 {{ $hAccent['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/></svg>
                                @elseif($habit['icon'] === 'dumbbell')
                                    <svg class="h-5 w-5 {{ $hAccent['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                                @elseif($habit['icon'] === 'utensils')
                                    <svg class="h-5 w-5 {{ $hAccent['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v6m0 0c1.66 0 3-1.34 3-3S13.66 2 12 2s-3 1.34-3 3 1.34 3 3 3zm0 0v14m6-20v8a2 2 0 01-2 2h-1v10"/></svg>
                                @elseif($habit['icon'] === 'pill')
                                    <svg class="h-5 w-5 {{ $hAccent['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6 6.5 6.5-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364L10.5 6Z M17 6.5l-4.5 4.5" /></svg>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-xs text-wc-text-tertiary">
                                Promedio: <span class="font-data font-semibold text-wc-text">{{ $habit['average'] }}/10</span>
                            </p>
                        </div>

                        {{-- Last 7 days bars --}}
                        <div class="mt-3 flex items-end gap-1.5">
                            @foreach($habit['last7'] as $day)
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div
                                        class="h-6 w-full rounded-sm transition-all duration-500 {{ $day['value'] > 0 ? $hAccent['bar'] : 'bg-wc-bg-secondary' }}"
                                        style="{{ $day['value'] > 0 ? 'opacity: ' . max(0.3, min($day['value'] / 10, 1)) . ';' : '' }}"
                                        title="{{ $day['date'] }}: {{ $day['value'] }}/10"
                                    ></div>
                                    <span class="text-[10px] text-wc-text-tertiary">{{ $day['date'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if(empty($habitData) || collect($habitData)->every(fn($h) => $h['streak'] === 0 && $h['average'] == 0))
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                    <p class="text-sm text-wc-text-secondary">Aún no tienes hábitos registrados en los últimos 30 días.</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Registra tus hábitos diarios desde la pantalla principal.</p>
                </div>
            @endif
        </div>

    {{-- ==================== TAB: CICLO HORMONAL ==================== --}}
    @elseif($activeTab === 'ciclo')
        @if(!$canAccessElite)
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Ciclo Hormonal Personalizado</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible exclusivamente en el plan Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade a Elite</a>
            </div>
        @elseif($cicloPlan && isset($cicloPlan['compounds']))
        {{-- ═══════════════════════════════════════════════════════════
             CICLO MASCULINO — Protocolo de Ciclo de Esteroides / AE
             ═══════════════════════════════════════════════════════════ --}}
        @php
            $cicloNombre      = $cicloPlan['name'] ?? $cicloPlan['nombre'] ?? 'Protocolo Hormonal';
            $cicloDuracion    = $cicloPlan['duration'] ?? $cicloPlan['duracion'] ?? null;
            $cicloDescripcion = $cicloPlan['descripcion_protocolo'] ?? $cicloPlan['descripcion'] ?? null;
            $cicloWarning     = $cicloPlan['warning'] ?? $cicloPlan['advertencia'] ?? null;
            $cicloMetricas    = $cicloPlan['metricas'] ?? null;
            $cicloCompounds   = $cicloPlan['compounds'] ?? [];
            $cicloPhases      = $cicloPlan['phases'] ?? $cicloPlan['fases'] ?? [];
            $cicloPct         = $cicloPlan['pct'] ?? [];
            $cicloLabs        = $cicloPlan['labs'] ?? [];
            $cicloEfectos     = $cicloPlan['efectos_secundarios'] ?? [];
            $cicloMonitoreo   = $cicloPlan['monitoreo_diario'] ?? [];
            $cicloEmergencia  = $cicloPlan['emergencia'] ?? [];
            $cicloCoachNotes  = $cicloPlan['notas_coach'] ?? null;

            $phaseColors = [
                'iniciación'  => ['bg' => 'bg-sky-500/10',     'border' => 'border-sky-500/25',    'text' => 'text-sky-400',     'dot' => 'bg-sky-400'],
                'iniciacion'  => ['bg' => 'bg-sky-500/10',     'border' => 'border-sky-500/25',    'text' => 'text-sky-400',     'dot' => 'bg-sky-400'],
                'pico'        => ['bg' => 'bg-wc-accent/8',    'border' => 'border-wc-accent/25',  'text' => 'text-wc-accent',   'dot' => 'bg-wc-accent'],
                'tapering'    => ['bg' => 'bg-amber-500/10',   'border' => 'border-amber-500/25',  'text' => 'text-amber-400',   'dot' => 'bg-amber-400'],
                'pct'         => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/25','text' => 'text-emerald-400', 'dot' => 'bg-emerald-400'],
                'post'        => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/25','text' => 'text-emerald-400', 'dot' => 'bg-emerald-400'],
            ];
            $getPhaseColor = function(string $nombre) use ($phaseColors): array {
                $lower = mb_strtolower($nombre);
                foreach ($phaseColors as $key => $color) {
                    if (str_contains($lower, $key)) return $color;
                }
                return ['bg' => 'bg-wc-bg-secondary', 'border' => 'border-wc-border', 'text' => 'text-wc-text-secondary', 'dot' => 'bg-wc-text-tertiary'];
            };
        @endphp

        <div class="space-y-5">

            {{-- Warning médico --}}
            @if($cicloWarning)
                <div class="rounded-xl border border-amber-500/30 bg-amber-500/8 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        <p class="text-xs leading-relaxed text-amber-300">{{ $cicloWarning }}</p>
                    </div>
                </div>
            @endif

            {{-- Header del protocolo --}}
            <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-wc-text-tertiary">Protocolo Activo</p>
                        <h2 class="mt-1 font-display text-2xl tracking-wide text-wc-text">{{ strtoupper($cicloNombre) }}</h2>
                        @if($cicloDuracion)
                            <p class="mt-1 text-sm text-wc-text-secondary">Duración: <span class="font-semibold text-wc-text">{{ $cicloDuracion }}</span></p>
                        @endif
                        @if($cicloDescripcion)
                            <p class="mt-2 text-xs leading-relaxed text-wc-text-tertiary">{{ $cicloDescripcion }}</p>
                        @endif
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L5 14.5m14.8.8 1.402 1.402c1 1 .03 2.798-1.421 2.798H4.062c-1.451 0-2.42-1.798-1.42-2.798L4 14.5" /></svg>
                    </div>
                </div>

                {{-- Metrics strip --}}
                @if($cicloMetricas)
                    <div class="mt-4 grid grid-cols-4 gap-2 border-t border-wc-border pt-4">
                        @if(isset($cicloMetricas['duracion']))
                            <div class="text-center"><p class="font-data text-lg font-black text-wc-accent">{{ $cicloMetricas['duracion'] }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Duración</p></div>
                        @endif
                        @if(isset($cicloMetricas['compuestos']))
                            <div class="text-center"><p class="font-data text-lg font-black text-wc-text">{{ $cicloMetricas['compuestos'] }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Compuestos</p></div>
                        @endif
                        @if(isset($cicloMetricas['fases']))
                            <div class="text-center"><p class="font-data text-lg font-black text-wc-text">{{ $cicloMetricas['fases'] }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Fases</p></div>
                        @endif
                        @if(isset($cicloMetricas['labs_requeridos']))
                            <div class="text-center"><p class="font-data text-lg font-black text-wc-text">{{ $cicloMetricas['labs_requeridos'] }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Labs req.</p></div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Compounds Table --}}
            @if(count($cicloCompounds) > 0)
                <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <div class="border-b border-wc-border px-5 py-4">
                        <h3 class="font-display text-sm tracking-wider text-wc-text">COMPUESTOS</h3>
                    </div>
                    <div class="divide-y divide-wc-border">
                        @foreach($cicloCompounds as $compound)
                            @php
                                $cNombre = is_array($compound) ? ($compound['nombre'] ?? $compound['name'] ?? '') : $compound;
                                $cDosis  = is_array($compound) ? ($compound['dosis'] ?? $compound['dose'] ?? null) : null;
                                $cFreq   = is_array($compound) ? ($compound['frecuencia'] ?? $compound['frequency'] ?? null) : null;
                                $cSems   = is_array($compound) ? ($compound['semanas'] ?? $compound['weeks'] ?? null) : null;
                                $cNotas  = is_array($compound) ? ($compound['notas'] ?? $compound['notes'] ?? null) : null;
                            @endphp
                            <div class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-wc-text">{{ $cNombre }}</span>
                                    @if($cDosis)
                                        <span class="rounded bg-wc-accent/10 px-2 py-0.5 font-data text-xs font-bold text-wc-accent">{{ $cDosis }}</span>
                                    @endif
                                    @if($cSems)
                                        <span class="rounded-full border border-wc-border px-2 py-0.5 text-[10px] text-wc-text-tertiary">Sem {{ $cSems }}</span>
                                    @endif
                                </div>
                                @if($cFreq)
                                    <p class="mt-1 text-xs text-wc-text-secondary">🗓️ {{ $cFreq }}</p>
                                @endif
                                @if($cNotas)
                                    <p class="mt-1 text-xs leading-relaxed text-wc-text-tertiary">{{ $cNotas }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Phase timeline --}}
            @if(count($cicloPhases) > 0)
                <div>
                    <h3 class="mb-3 font-display text-sm tracking-wider text-wc-text-tertiary uppercase px-1">FASES DEL CICLO</h3>
                    {{-- Visual timeline bar --}}
                    @php $totalPhases = count($cicloPhases); @endphp
                    <div class="mb-4 flex h-2.5 w-full overflow-hidden rounded-full">
                        @foreach($cicloPhases as $pi => $phase)
                            @php
                                $pc = $getPhaseColor($phase['nombre'] ?? $phase['name'] ?? '');
                                $dotBg = $pc['dot'];
                            @endphp
                            <div class="h-full flex-1 {{ $dotBg }}" style="opacity: {{ 0.5 + ($pi / $totalPhases) * 0.5 }};"></div>
                        @endforeach
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($cicloPhases as $phase)
                            @php
                                $pNombre = is_array($phase) ? ($phase['nombre'] ?? $phase['name'] ?? '') : $phase;
                                $pSems   = is_array($phase) ? ($phase['semanas'] ?? $phase['weeks'] ?? null) : null;
                                $pDesc   = is_array($phase) ? ($phase['descripcion'] ?? $phase['description'] ?? null) : null;
                                $pc      = $getPhaseColor($pNombre);
                            @endphp
                            <div class="rounded-xl border {{ $pc['border'] }} {{ $pc['bg'] }} p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="h-2 w-2 rounded-full {{ $pc['dot'] }} shrink-0"></div>
                                    <p class="font-display text-sm tracking-wide {{ $pc['text'] }}">{{ strtoupper($pNombre) }}</p>
                                    @if($pSems)
                                        <span class="ml-auto text-[10px] text-wc-text-tertiary">Sem {{ $pSems }}</span>
                                    @endif
                                </div>
                                @if($pDesc)
                                    <p class="text-xs leading-relaxed text-wc-text-secondary">{{ $pDesc }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- PCT --}}
            @if(count($cicloPct) > 0)
                <div class="overflow-hidden rounded-xl border border-emerald-500/20 bg-emerald-500/5">
                    <div class="border-b border-emerald-500/20 px-5 py-3">
                        <h3 class="font-display text-sm tracking-wider text-emerald-400">POST CYCLE THERAPY (PCT)</h3>
                    </div>
                    <div class="divide-y divide-emerald-500/10">
                        @foreach($cicloPct as $pct)
                            @php
                                $pctNombre = is_array($pct) ? ($pct['nombre'] ?? $pct['name'] ?? '') : $pct;
                                $pctDosis  = is_array($pct) ? ($pct['dosis'] ?? $pct['dose'] ?? null) : null;
                                $pctSems   = is_array($pct) ? ($pct['semanas'] ?? $pct['weeks'] ?? null) : null;
                            @endphp
                            <div class="flex items-center gap-4 px-5 py-3.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/15">
                                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-wc-text text-sm">{{ $pctNombre }}</p>
                                    @if($pctDosis)<p class="text-xs text-emerald-400 font-data font-bold mt-0.5">{{ $pctDosis }}</p>@endif
                                    @if($pctSems)<p class="text-[11px] text-wc-text-tertiary mt-0.5">{{ $pctSems }}</p>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Laboratorios requeridos --}}
            @if(count($cicloLabs) > 0)
                <div class="overflow-hidden rounded-xl border border-sky-500/20 bg-sky-500/5">
                    <div class="border-b border-sky-500/20 px-5 py-3">
                        <h3 class="font-display text-sm tracking-wider text-sky-400">ANÁLISIS DE LABORATORIO</h3>
                    </div>
                    <div class="divide-y divide-sky-500/10">
                        @foreach($cicloLabs as $lab)
                            @php
                                $labNombre    = is_array($lab) ? ($lab['nombre'] ?? $lab['name'] ?? '') : $lab;
                                $labCuando    = is_array($lab) ? ($lab['cuando'] ?? $lab['when'] ?? null) : null;
                                $labMarcadores = is_array($lab) ? ($lab['marcadores'] ?? $lab['markers'] ?? null) : null;
                            @endphp
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-semibold text-sky-300 text-sm">{{ $labNombre }}</p>
                                    @if($labCuando)
                                        <span class="shrink-0 rounded-full bg-sky-500/15 px-2.5 py-0.5 text-[10px] font-bold text-sky-400 uppercase tracking-wide">{{ $labCuando }}</span>
                                    @endif
                                </div>
                                @if($labMarcadores)
                                    <p class="mt-1.5 text-xs leading-relaxed text-wc-text-tertiary">{{ $labMarcadores }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Monitoreo diario --}}
            @if(count($cicloMonitoreo) > 0)
                <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <div class="border-b border-wc-border px-5 py-4">
                        <h3 class="font-display text-sm tracking-wider text-wc-text">MONITOREO DIARIO</h3>
                    </div>
                    <div class="divide-y divide-wc-border">
                        @foreach($cicloMonitoreo as $item)
                            @php
                                $mItem       = is_array($item) ? ($item['item'] ?? '') : $item;
                                $mFrecuencia = is_array($item) ? ($item['frecuencia'] ?? null) : null;
                                $mDetalle    = is_array($item) ? ($item['detalle'] ?? null) : null;
                            @endphp
                            <div class="flex items-start gap-3 px-5 py-3.5">
                                <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded border border-wc-accent/40 bg-wc-accent/10">
                                    <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75"/></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-wc-text">{{ $mItem }}</p>
                                        @if($mFrecuencia)
                                            <span class="text-[10px] text-wc-text-tertiary">· {{ $mFrecuencia }}</span>
                                        @endif
                                    </div>
                                    @if($mDetalle)
                                        <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ $mDetalle }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Efectos secundarios y manejo --}}
            @if(count($cicloEfectos) > 0)
                <div x-data="{ openEfecto: null }" class="space-y-2">
                    <h3 class="font-display text-sm tracking-wider text-wc-text-tertiary uppercase px-1">EFECTOS SECUNDARIOS & MANEJO</h3>
                    @foreach($cicloEfectos as $idx => $efecto)
                        @php
                            $eEfecto = is_array($efecto) ? ($efecto['efecto'] ?? '') : $efecto;
                            $eManejo = is_array($efecto) ? ($efecto['manejo'] ?? $efecto['management'] ?? null) : null;
                        @endphp
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                            <button
                                @click="openEfecto = openEfecto === {{ $idx }} ? null : {{ $idx }}"
                                class="flex w-full items-center justify-between px-5 py-3.5 text-left"
                            >
                                <span class="text-sm font-medium text-wc-text">{{ $eEfecto }}</span>
                                <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform"
                                    :class="openEfecto === {{ $idx }} ? 'rotate-180' : ''"
                                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <div x-show="openEfecto === {{ $idx }}"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="border-t border-wc-border px-5 py-3.5 bg-wc-bg-secondary">
                                <p class="text-xs leading-relaxed text-wc-text-secondary">{{ $eManejo }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Emergencias --}}
            @if(count($cicloEmergencia) > 0)
                <div class="overflow-hidden rounded-xl border border-red-500/30 bg-red-500/5">
                    <div class="border-b border-red-500/30 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"/></svg>
                            <h3 class="font-display text-sm tracking-wider text-red-400">SEÑALES DE EMERGENCIA</h3>
                        </div>
                    </div>
                    <div class="divide-y divide-red-500/15">
                        @foreach($cicloEmergencia as $emerg)
                            @php
                                $eSintoma = is_array($emerg) ? ($emerg['sintoma'] ?? '') : $emerg;
                                $eAccion  = is_array($emerg) ? ($emerg['accion'] ?? null) : null;
                            @endphp
                            <div class="px-5 py-4">
                                <p class="text-sm font-semibold text-red-300">⚠️ {{ $eSintoma }}</p>
                                @if($eAccion)
                                    <p class="mt-1.5 text-xs leading-relaxed text-wc-text-secondary">{{ $eAccion }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Coach notes --}}
            @if($cicloCoachNotes)
                <div class="rounded-xl border-l-4 border-wc-accent bg-wc-bg-tertiary p-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-accent">Notas del coach</p>
                    <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $cicloCoachNotes }}</p>
                </div>
            @endif

        </div>

        @else
        {{-- ═══════════════════════════════════════════════════════════
             CICLO FEMENINO — Tracker de ciclo menstrual (sin cambios)
             ═══════════════════════════════════════════════════════════ --}}
        <div
            x-data="{
                startDate: localStorage.getItem('wc_cycle_start') || '',
                cycleLength: parseInt(localStorage.getItem('wc_cycle_length')) || 28,
                showConfig: !localStorage.getItem('wc_cycle_start'),
                get currentDay() {
                    if (!this.startDate) return null;
                    const start = new Date(this.startDate + 'T00:00:00');
                    const today = new Date(); today.setHours(0,0,0,0);
                    const diff = Math.floor((today - start) / 86400000);
                    const d = (diff % this.cycleLength) + 1;
                    return d > 0 ? d : null;
                },
                get phaseKey() {
                    const d = this.currentDay;
                    if (!d) return '';
                    if (d <= 5) return 'menstrual';
                    if (d <= 13) return 'folicular';
                    if (d <= 16) return 'ovulatoria';
                    return 'lutea';
                },
                get phaseData() {
                    const map = {
                        menstrual:  { name: 'Menstrual',  emoji: '🌑', ring: '#f87171', bg: 'bg-red-500/10',    border: 'border-red-500/25',    text: 'text-red-400',    train: 'Ejercicio de baja intensidad: yoga, caminata ligera, estiramientos. Reduce cargas y escucha tu cuerpo.', nutrition: 'Aumenta hierro y magnesio. Prioriza alimentos anti-inflamatorios: salmon, nueces, verduras de hoja.', energy: 3 },
                        folicular:  { name: 'Folicular',  emoji: '🌱', ring: '#4ade80', bg: 'bg-green-500/10',  border: 'border-green-500/25',  text: 'text-green-400',  train: 'Tu energia aumenta. Ideal para fuerza, HIIT y aumentar cargas. Aprovecha la ventana anabolica.', nutrition: 'Soporta la sintesis de estrogeno con zinc y B6. Proteina moderada-alta para soportar el volumen.', energy: 7 },
                        ovulatoria: { name: 'Ovulatoria', emoji: '✨', ring: '#fbbf24', bg: 'bg-amber-500/10',  border: 'border-amber-500/25',  text: 'text-amber-400',  train: 'Pico maximo de energia y fuerza. Momento ideal para PRs, sesiones de alta intensidad y nuevos records.', nutrition: 'Mantiene proteina alta. Antioxidantes para reducir inflamacion post-esfuerzo. Hidratacion optima.', energy: 10 },
                        lutea:      { name: 'Lutea',      emoji: '🌙', ring: '#c084fc', bg: 'bg-purple-500/10', border: 'border-purple-500/25', text: 'text-purple-400', train: 'Energia moderada. Enfocate en tecnica, estabilidad y recuperacion activa. Reduce intensidad al final.', nutrition: 'Sube el apetito, es normal. Prioriza fibra, magnesio y calcio para reducir sintomas de SPM.', energy: 6 },
                    };
                    return map[this.phaseKey] || null;
                },
                get progressPct() {
                    if (!this.currentDay) return 0;
                    return Math.round((this.currentDay / this.cycleLength) * 100);
                },
                get daysUntilNext() {
                    if (!this.currentDay) return null;
                    return this.cycleLength - this.currentDay + 1;
                },
                get phaseArcs() {
                    const r = 54; const circ = 2 * Math.PI * r;
                    const cl = this.cycleLength; const gap = 2.5;
                    return [
                        { color: '#f87171', start: 0,  days: 5       },
                        { color: '#4ade80', start: 5,  days: 8       },
                        { color: '#fbbf24', start: 13, days: 3       },
                        { color: '#c084fc', start: 16, days: cl - 16 },
                    ].map(p => {
                        const arcLen = Math.max(0, (p.days / cl) * circ - gap);
                        return {
                            color: p.color,
                            dasharray: arcLen.toFixed(1) + ' ' + (circ * 2).toFixed(1),
                            dashoffset: (-(p.start / cl) * circ).toFixed(1),
                        };
                    });
                },
                get dotOffset() {
                    if (!this.currentDay) return null;
                    const r = 54; const circ = 2 * Math.PI * r;
                    return (-(((this.currentDay - 0.5) / this.cycleLength) * circ)).toFixed(1);
                },
                save() {
                    localStorage.setItem('wc_cycle_start', this.startDate);
                    localStorage.setItem('wc_cycle_length', this.cycleLength);
                    this.showConfig = false;
                },
            }"
            class="space-y-5"
        >
            {{-- Hero: Circular ring + phase display --}}
            <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-6"
                :class="phaseData ? phaseData.bg + ' ' + phaseData.border : 'bg-wc-bg-tertiary border-wc-border'">

                <template x-if="currentDay && phaseData">
                    <div class="flex flex-col items-center sm:flex-row sm:items-center sm:gap-8">
                        <div class="relative shrink-0">
                            <svg width="140" height="140" viewBox="0 0 140 140" class="-rotate-90">
                                <circle cx="70" cy="70" r="54" fill="none" stroke-width="10" style="stroke: currentColor; opacity: 0.12;" class="text-wc-text"/>
                                <template x-for="arc in phaseArcs" :key="arc.color">
                                    <circle cx="70" cy="70" r="54" fill="none" :stroke="arc.color" stroke-width="10" stroke-linecap="butt" :stroke-dasharray="arc.dasharray" :stroke-dashoffset="arc.dashoffset"/>
                                </template>
                                <template x-if="currentDay">
                                    <circle cx="70" cy="70" r="54" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" :stroke-dasharray="'4 ' + (2 * Math.PI * 54)" :stroke-dashoffset="dotOffset" style="filter: drop-shadow(0 0 4px rgba(255,255,255,0.9));"/>
                                </template>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-2xl" x-text="phaseData.emoji"></span>
                                <span class="font-data text-2xl font-black leading-none" :class="phaseData.text" x-text="currentDay"></span>
                                <span class="text-[10px] text-wc-text-tertiary font-medium uppercase tracking-wide">día</span>
                            </div>
                        </div>
                        <div class="mt-4 sm:mt-0 text-center sm:text-left flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-wc-text-tertiary mb-1">Fase actual</p>
                            <h2 class="font-display text-4xl tracking-wide leading-none" :class="phaseData.text" x-text="phaseData.name"></h2>
                            <p class="mt-2 font-data text-sm text-wc-text-secondary">Día <span class="font-bold text-wc-text" x-text="currentDay"></span> de <span x-text="cycleLength"></span></p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Próximo ciclo en <span x-text="daysUntilNext" class="font-semibold text-wc-text-secondary"></span> días</p>
                            <div class="mt-3 flex items-center gap-1.5">
                                <span class="text-[10px] font-medium text-wc-text-tertiary uppercase tracking-wider">Energía</span>
                                <div class="flex gap-0.5">
                                    <template x-for="i in 10">
                                        <div class="h-2 w-2 rounded-full transition-colors" :class="i <= phaseData.energy ? phaseData.text.replace('text-','bg-') : 'bg-wc-bg-secondary'"></div>
                                    </template>
                                </div>
                            </div>
                            <button @click="showConfig = !showConfig" class="mt-3 text-[11px] text-wc-text-tertiary hover:text-wc-text-secondary transition-colors flex items-center gap-1">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                Ajustar configuración
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="!currentDay">
                    <div class="py-4 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                            <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </div>
                        <p class="font-display text-lg tracking-wide text-wc-text">CONFIGURA TU CICLO</p>
                        <p class="mt-1 text-sm text-wc-text-secondary">Ingresa la fecha del inicio de tu último ciclo para ver tu fase actual.</p>
                    </div>
                </template>
            </div>

            {{-- Config form --}}
            <div x-show="showConfig"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-base tracking-wide text-wc-text mb-4">CONFIGURACIÓN DEL CICLO</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">📅 Fecha de inicio del último ciclo</label>
                        <input type="date" x-model="startDate" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">🔄 Duración del ciclo (días)</label>
                        <input type="number" x-model.number="cycleLength" min="21" max="40" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"/>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <button @click="save()" class="btn-press rounded-lg bg-wc-accent px-5 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">Guardar</button>
                    <p class="text-xs text-wc-text-tertiary">Los datos se guardan localmente en tu dispositivo.</p>
                </div>
            </div>

            {{-- Recommendations --}}
            <template x-if="currentDay && phaseData">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-lg" :class="phaseData.bg">🏋️</span>
                            <h4 class="font-display text-sm tracking-wide text-wc-text">ENTRENAMIENTO</h4>
                        </div>
                        <p class="text-sm leading-relaxed text-wc-text-secondary" x-text="phaseData.train"></p>
                    </div>
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-lg" :class="phaseData.bg">🥗</span>
                            <h4 class="font-display text-sm tracking-wide text-wc-text">NUTRICIÓN</h4>
                        </div>
                        <p class="text-sm leading-relaxed text-wc-text-secondary" x-text="phaseData.nutrition"></p>
                    </div>
                </div>
            </template>

            {{-- Phase timeline reference --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-sm tracking-wide text-wc-text mb-4">FASES DEL CICLO</h3>
                <div class="mb-4 flex h-3 w-full overflow-hidden rounded-full">
                    <div class="h-full transition-all" style="width: 18%; background:#f87171;"></div>
                    <div class="h-full transition-all" style="width: 32%; background:#4ade80;"></div>
                    <div class="h-full transition-all" style="width: 11%; background:#fbbf24;"></div>
                    <div class="h-full flex-1" style="background:#c084fc;"></div>
                </div>
                @php
                    $phaseCards = [
                        ['dot' => 'bg-red-400',    'text' => 'text-red-400',    'border' => 'border-red-500/25',    'bg_f' => 'bg-red-500/10',    'bg_b' => 'bg-red-500/20',    'name' => 'Menstrual',  'days' => '1–5',  'sub' => 'Descanso activo',    'train' => 'Yoga, caminata, movilidad. Reduce cargas. Recuperación activa prioritaria.', 'nutr' => 'Hierro, magnesio, omega-3 y alimentos anti-inflamatorios.'],
                        ['dot' => 'bg-green-400',  'text' => 'text-green-400',  'border' => 'border-green-500/25',  'bg_f' => 'bg-green-500/10',  'bg_b' => 'bg-green-500/20',  'name' => 'Folicular',  'days' => '6–13', 'sub' => 'Fuerza e intensidad',  'train' => 'Fuerza máxima, HIIT, aumentar cargas. Ventana anabólica óptima.', 'nutr' => 'Proteína alta, zinc, vitamina B6 para síntesis de estrógeno.'],
                        ['dot' => 'bg-amber-400',  'text' => 'text-amber-400',  'border' => 'border-amber-500/25',  'bg_f' => 'bg-amber-500/10',  'bg_b' => 'bg-amber-500/20',  'name' => 'Ovulatoria', 'days' => '14–16','sub' => 'Pico de rendimiento', 'train' => 'Pico de fuerza y energía. Ideal para PRs y nuevos récords.', 'nutr' => 'Antioxidantes, proteína alta, hidratación óptima.'],
                        ['dot' => 'bg-purple-400', 'text' => 'text-purple-400', 'border' => 'border-purple-500/25', 'bg_f' => 'bg-purple-500/10', 'bg_b' => 'bg-purple-500/20', 'name' => 'Lútea',      'days' => '17–28','sub' => 'Técnica y estabilidad', 'train' => 'Técnica, estabilidad, recuperación activa. Reduce intensidad al final.', 'nutr' => 'Fibra, magnesio y calcio. El apetito sube — es normal.'],
                    ];
                @endphp
                <div class="grid grid-cols-2 gap-3">
                    @foreach($phaseCards as $pc)
                        <div x-data="{ flipped: false }" @click="flipped = !flipped" class="cursor-pointer select-none" style="perspective: 700px; height: 130px;">
                            <div style="position: relative; width: 100%; height: 100%; transform-style: preserve-3d; transition: transform 0.45s cubic-bezier(.4,0,.2,1);" :style="flipped ? 'transform:rotateY(180deg)' : 'transform:rotateY(0deg)'">
                                <div class="absolute inset-0 rounded-xl p-3 {{ $pc['border'] }} {{ $pc['bg_f'] }}" style="backface-visibility:hidden;">
                                    <div class="flex items-center gap-1.5 mb-1.5"><div class="h-2 w-2 shrink-0 rounded-full {{ $pc['dot'] }}"></div><p class="text-xs font-semibold {{ $pc['text'] }}">{{ $pc['name'] }}</p></div>
                                    <p class="text-[10px] text-wc-text-tertiary">Días {{ $pc['days'] }}</p>
                                    <p class="text-[10px] text-wc-text-tertiary mt-0.5">{{ $pc['sub'] }}</p>
                                </div>
                                <div class="absolute inset-0 overflow-y-auto rounded-xl p-3 {{ $pc['border'] }} {{ $pc['bg_b'] }}" style="backface-visibility:hidden; transform:rotateY(180deg);">
                                    <p class="text-[10px] font-bold {{ $pc['text'] }} mb-1">🏋️ Entreno</p>
                                    <p class="text-[10px] text-wc-text-secondary leading-relaxed">{{ $pc['train'] }}</p>
                                    <div class="mt-2 border-t {{ $pc['border'] }} pt-2"><p class="text-[10px] font-bold {{ $pc['text'] }} mb-1">🥗 Nutrición</p><p class="text-[10px] text-wc-text-secondary leading-relaxed">{{ $pc['nutr'] }}</p></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    {{-- ==================== TAB: BLOODWORK ==================== --}}
    @elseif($activeTab === 'bloodwork')
        @if(!$canAccessElite)
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Bloodwork &amp; Analisis Laboratorio</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible exclusivamente en el plan Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade a Elite</a>
            </div>
        @else
        @php
            // ── Latest-per-test summary cards ──────────────────────────
            $latestByTest = [];
            foreach (array_reverse($bloodworkResults) as $r) {
                if (!isset($latestByTest[$r['test_name']])) {
                    $latestByTest[$r['test_name']] = $r;
                }
            }
            // Helper: determine status from reference_range string "lo-hi unit"
            $bwStatus = function(array $r): string {
                $range = $r['reference_range'] ?? '';
                $val   = (float) ($r['value'] ?? 0);
                if (!$range || $val <= 0) return 'neutral';
                preg_match('/(\d+[\.,]?\d*)\s*[-–]\s*(\d+[\.,]?\d*)/', $range, $m);
                if (!isset($m[1], $m[2])) return 'neutral';
                $lo = (float) str_replace(',', '.', $m[1]);
                $hi = (float) str_replace(',', '.', $m[2]);
                if ($val >= $lo && $val <= $hi) return 'ok';
                return 'flag';
            };
        @endphp

        <div class="space-y-6">

            {{-- Latest values summary (only if we have results) --}}
            @if(count($latestByTest) > 0)
                <div>
                    <h3 class="font-display text-sm tracking-wide text-wc-text-secondary mb-3">ÚLTIMOS VALORES</h3>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($latestByTest as $testName => $r)
                            @php
                                $status = $bwStatus($r);
                                // Compute spectrum bar position
                                $specPct = null;
                                $rangeStr = $r['reference_range'] ?? '';
                                $bwVal = (float) ($r['value'] ?? 0);
                                if ($rangeStr && $bwVal > 0) {
                                    preg_match('/(\d+[\.,]?\d*)\s*[-–]\s*(\d+[\.,]?\d*)/', $rangeStr, $sm);
                                    if (isset($sm[1], $sm[2])) {
                                        $smLo = (float) str_replace(',', '.', $sm[1]);
                                        $smHi = (float) str_replace(',', '.', $sm[2]);
                                        $smRange = $smHi - $smLo;
                                        if ($smRange > 0) {
                                            $visMin = $smLo - $smRange * 0.4;
                                            $visMax = $smHi + $smRange * 0.4;
                                            $specPct = max(2, min(98, ($bwVal - $visMin) / ($visMax - $visMin) * 100));
                                        }
                                    }
                                }
                            @endphp
                            <div class="rounded-xl border bg-wc-bg-tertiary p-3.5
                                {{ $status === 'ok'   ? 'border-emerald-500/25' : ($status === 'flag' ? 'border-amber-500/30' : 'border-wc-border') }}">
                                <div class="flex items-start justify-between gap-1 mb-2">
                                    <p class="text-[11px] font-medium text-wc-text-secondary leading-tight">{{ $testName }}</p>
                                    @if($status === 'ok')
                                        <span class="shrink-0 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500/20">
                                            <svg class="h-2.5 w-2.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        </span>
                                    @elseif($status === 'flag')
                                        <span class="shrink-0 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500/20">
                                            <svg class="h-2.5 w-2.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-baseline gap-1">
                                    <span class="font-data text-xl font-black text-wc-text tabular-nums">{{ $r['value'] }}</span>
                                    <span class="text-[10px] text-wc-text-tertiary">{{ $r['unit'] }}</span>
                                </div>
                                @if($specPct !== null)
                                    <div class="mt-2 relative h-1.5 w-full overflow-hidden rounded-full"
                                        style="background: linear-gradient(to right, #ef4444 0%, #fbbf24 20%, #4ade80 32%, #4ade80 68%, #fbbf24 80%, #ef4444 100%);">
                                        <div class="absolute top-0 h-full w-0.5 rounded-full bg-white shadow"
                                            style="left: {{ round($specPct, 1) }}%; transform: translateX(-50%);"></div>
                                    </div>
                                @elseif(!empty($r['reference_range']))
                                    <p class="mt-1 text-[9px] text-wc-text-tertiary">Ref: {{ $r['reference_range'] }}</p>
                                @endif
                                <p class="mt-1 text-[9px] text-wc-text-tertiary">{{ \Carbon\Carbon::parse($r['test_date'])->format('d/m/Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Add result form --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5"
                x-data="{ open: {{ count($bloodworkResults) === 0 ? 'true' : 'false' }} }">
                <button
                    @click="open = !open"
                    class="flex w-full items-center justify-between"
                >
                    <h3 class="font-display text-base tracking-wide text-wc-text">AGREGAR RESULTADO</h3>
                    <svg class="h-5 w-5 text-wc-text-tertiary transition-transform" :class="open && 'rotate-180'"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">

                    @if($bwShowSuccess)
                        <div class="mt-4 flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-400">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Resultado guardado correctamente.
                        </div>
                    @endif

                    <form wire:submit="saveBloodwork" class="mt-4 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">🧪 Prueba</label>
                                <select
                                    wire:model="bwTestName"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                                >
                                    <option value="">Seleccionar prueba...</option>
                                    <optgroup label="Metabolismo">
                                        <option>Glucosa</option>
                                        <option>HbA1c</option>
                                        <option>Insulina</option>
                                    </optgroup>
                                    <optgroup label="Lípidos">
                                        <option>Colesterol Total</option>
                                        <option>HDL</option>
                                        <option>LDL</option>
                                        <option>Trigliceridos</option>
                                    </optgroup>
                                    <optgroup label="Hormonas">
                                        <option>Testosterona</option>
                                        <option>TSH</option>
                                        <option>T3 Libre</option>
                                        <option>T4 Libre</option>
                                        <option>Cortisol</option>
                                        <option>DHEA-S</option>
                                    </optgroup>
                                    <optgroup label="Hematología">
                                        <option>Hemoglobina</option>
                                        <option>Hematocrito</option>
                                        <option>Ferritina</option>
                                        <option>Hierro</option>
                                    </optgroup>
                                    <optgroup label="Vitaminas y Minerales">
                                        <option>Vitamina D</option>
                                        <option>Vitamina B12</option>
                                        <option>Zinc</option>
                                        <option>Magnesio</option>
                                    </optgroup>
                                    <optgroup label="Función Renal/Hepática">
                                        <option>Creatinina</option>
                                        <option>ALT/TGP</option>
                                        <option>AST/TGO</option>
                                    </optgroup>
                                </select>
                                @error('bwTestName') <span class="mt-1 block text-xs text-red-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">📅 Fecha</label>
                                <input
                                    type="date"
                                    wire:model="bwTestDate"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                                />
                                @error('bwTestDate') <span class="mt-1 block text-xs text-red-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">📊 Valor</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="bwValue"
                                    placeholder="ej: 95.5"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                                />
                                @error('bwValue') <span class="mt-1 block text-xs text-red-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">🔬 Unidad</label>
                                <input
                                    type="text"
                                    wire:model="bwUnit"
                                    placeholder="ej: mg/dL"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                                />
                                @error('bwUnit') <span class="mt-1 block text-xs text-red-400">{{ $message }}</span> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">📋 Rango de referencia <span class="font-normal text-wc-text-tertiary">(opcional — ej: 70-100)</span></label>
                                <input
                                    type="text"
                                    wire:model="bwReferenceRange"
                                    placeholder="ej: 70-100 mg/dL"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-press rounded-xl bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Guardar Resultado</span>
                            <span wire:loading class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Guardando...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Results list --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-wc-border">
                    <h3 class="font-display text-base tracking-wide text-wc-text">HISTORIAL</h3>
                    @if(count($bloodworkResults) > 0)
                        <span class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-xs font-medium text-wc-text-secondary">
                            {{ count($bloodworkResults) }} registros
                        </span>
                    @endif
                </div>

                @if(count($bloodworkResults) > 0)
                    <div>
                        @foreach(array_reverse($bloodworkResults) as $result)
                            @php
                                $status = $bwStatus($result);
                                $histSpecPct = null;
                                $histRange = $result['reference_range'] ?? '';
                                $histVal = (float) ($result['value'] ?? 0);
                                if ($histRange && $histVal > 0) {
                                    preg_match('/(\d+[\.,]?\d*)\s*[-–]\s*(\d+[\.,]?\d*)/', $histRange, $hm);
                                    if (isset($hm[1], $hm[2])) {
                                        $hLo = (float) str_replace(',', '.', $hm[1]);
                                        $hHi = (float) str_replace(',', '.', $hm[2]);
                                        $hRange = $hHi - $hLo;
                                        if ($hRange > 0) {
                                            $hVisMin = $hLo - $hRange * 0.4;
                                            $hVisMax = $hHi + $hRange * 0.4;
                                            $histSpecPct = max(2, min(98, ($histVal - $hVisMin) / ($hVisMax - $hVisMin) * 100));
                                        }
                                    }
                                }
                            @endphp
                            <div class="px-5 py-3.5 {{ $result !== end($bloodworkResults) ? 'border-b border-wc-border/60' : '' }}">
                            <div class="flex items-center gap-4">
                                {{-- Status dot --}}
                                <div class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full
                                    {{ $status === 'ok'   ? 'bg-emerald-500/15' : ($status === 'flag' ? 'bg-amber-500/15' : 'bg-wc-bg-secondary') }}">
                                    @if($status === 'ok')
                                        <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    @elseif($status === 'flag')
                                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                    @else
                                        <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    @endif
                                </div>

                                {{-- Test name + date --}}
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-wc-text truncate">{{ $result['test_name'] }}</p>
                                    <p class="text-[11px] text-wc-text-tertiary">{{ \Carbon\Carbon::parse($result['test_date'])->format('d M Y') }}</p>
                                </div>

                                {{-- Value + unit --}}
                                <div class="shrink-0 text-right">
                                    <p class="font-data text-base font-bold text-wc-text tabular-nums">
                                        {{ $result['value'] }}
                                        <span class="text-xs font-normal text-wc-text-tertiary">{{ $result['unit'] }}</span>
                                    </p>
                                    @if(!empty($result['reference_range']))
                                        <p class="text-[10px] text-wc-text-tertiary">{{ $result['reference_range'] }}</p>
                                    @endif
                                </div>

                                {{-- Delete --}}
                                <button
                                    wire:click="deleteBloodwork({{ $result['id'] }})"
                                    wire:confirm="¿Eliminar este resultado?"
                                    class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-red-500/10 hover:text-red-400 transition-colors"
                                    title="Eliminar"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </div>{{-- end flex row --}}
                            {{-- Spectrum bar (below the row) --}}
                            @if($histSpecPct !== null)
                                <div class="mt-2 px-12 relative h-1.5 w-full overflow-hidden rounded-full"
                                    style="background: linear-gradient(to right, #ef4444 0%, #fbbf24 20%, #4ade80 32%, #4ade80 68%, #fbbf24 80%, #ef4444 100%);">
                                    <div class="absolute top-0 h-full w-0.5 rounded-full bg-white shadow"
                                        style="left: {{ round($histSpecPct, 1) }}%; transform: translateX(-50%);"></div>
                                </div>
                            @endif
                            </div>{{-- end outer row wrapper --}}
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                            <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                        </div>
                        <p class="font-display text-base tracking-wide text-wc-text">SIN RESULTADOS AÚN</p>
                        <p class="mt-1 text-sm text-wc-text-secondary">Agrega tus resultados de laboratorio para llevar un seguimiento de tu salud.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>
