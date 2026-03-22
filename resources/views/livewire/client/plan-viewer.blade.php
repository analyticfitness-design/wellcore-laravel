<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu programación personalizada, diseñada por tu coach</p>
    </div>

    {{-- Tabs --}}
    @php
        $canAccessNutricion = in_array($clientPlanType, ['metodo', 'elite', 'presencial', 'rise']);
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
    <div class="mb-6 flex gap-1 overflow-x-auto rounded-xl border border-wc-border bg-wc-bg-secondary p-1">
        @foreach($tabs as $key => $label)
            @php
                $locked = (in_array($key, ['nutricion','suplementacion']) && !$canAccessNutricion)
                       || (in_array($key, ['ciclo','bloodwork']) && !$canAccessElite);
            @endphp
            <button
                @if(!$locked) wire:click="setTab('{{ $key }}')" @endif
                @class([
                    'shrink-0 flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap',
                    'bg-wc-bg-tertiary text-wc-text shadow-sm' => $activeTab === $key,
                    'text-wc-text-tertiary hover:text-wc-text-secondary' => $activeTab !== $key && !$locked,
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

    {{-- Tab Content --}}

    {{-- ==================== TAB: ENTRENAMIENTO ==================== --}}
    @if($activeTab === 'entrenamiento')
        @if($trainingPlan)
            @php
                $dias = $trainingPlan['dias'] ?? [];
                $planObjetivoE = $trainingPlan['objetivo'] ?? $trainingPlan['objetivo_general'] ?? null;
                $totalExercises = 0;
                foreach ($dias as $d) {
                    $totalExercises += count($d['ejercicios'] ?? []);
                }
                $estimatedWeeklyMin = max(count($dias) * 45, 20);

                // Muscle group color map (partial match)
                $mgPalette = [
                    'pecho'      => ['bg' => 'bg-rose-500/10',    'text' => 'text-rose-400'],
                    'espalda'    => ['bg' => 'bg-sky-500/10',     'text' => 'text-sky-400'],
                    'pierna'     => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400'],
                    'cuadricep'  => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400'],
                    'isquio'     => ['bg' => 'bg-teal-500/10',    'text' => 'text-teal-400'],
                    'glut'       => ['bg' => 'bg-lime-500/10',    'text' => 'text-lime-400'],
                    'hombro'     => ['bg' => 'bg-amber-500/10',   'text' => 'text-amber-400'],
                    'brazo'      => ['bg' => 'bg-violet-500/10',  'text' => 'text-violet-400'],
                    'bicep'      => ['bg' => 'bg-violet-500/10',  'text' => 'text-violet-400'],
                    'tricep'     => ['bg' => 'bg-purple-500/10',  'text' => 'text-purple-400'],
                    'core'       => ['bg' => 'bg-cyan-500/10',    'text' => 'text-cyan-400'],
                    'full'       => ['bg' => 'bg-orange-500/10',  'text' => 'text-orange-400'],
                    'funcional'  => ['bg' => 'bg-orange-500/10',  'text' => 'text-orange-400'],
                    'cardio'     => ['bg' => 'bg-pink-500/10',    'text' => 'text-pink-400'],
                ];
                $getMgColor = function(?string $mg) use ($mgPalette): array {
                    if (!$mg) return ['bg' => 'bg-wc-bg-secondary', 'text' => 'text-wc-text-tertiary'];
                    $lower = mb_strtolower($mg);
                    foreach ($mgPalette as $key => $colors) {
                        if (str_contains($lower, $key)) return $colors;
                    }
                    return ['bg' => 'bg-wc-bg-secondary', 'text' => 'text-wc-text-secondary'];
                };
            @endphp

            {{-- Plan summary stats — horizontal with icon accent --}}
            <div class="mb-5 grid grid-cols-3 gap-2">
                <div class="flex flex-col items-center gap-1 rounded-2xl border border-wc-border bg-wc-bg-tertiary p-3">
                    <span class="font-data text-3xl font-black text-wc-text">{{ count($dias) }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Días/sem</span>
                </div>
                <div class="flex flex-col items-center gap-1 rounded-2xl border border-wc-border bg-wc-bg-tertiary p-3">
                    <span class="font-data text-3xl font-black text-wc-text">{{ $totalExercises }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Ejercicios</span>
                </div>
                <div class="flex flex-col items-center gap-1 rounded-2xl border border-wc-accent/25 bg-wc-accent/8 p-3">
                    <span class="font-data text-3xl font-black text-wc-accent">~{{ $estimatedWeeklyMin }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-wc-accent/60">Min/sem</span>
                </div>
            </div>

            {{-- Objetivo banner --}}
            @if($planObjetivoE)
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-wc-accent/20 bg-gradient-to-r from-wc-accent/8 to-transparent px-4 py-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-wc-text">{{ $planObjetivoE }}</p>
                </div>
            @endif

            {{-- Accordion day cards --}}
            @if(count($dias) > 0)
                <div class="space-y-2" x-data="{ openDay: 0 }">
                    @foreach($dias as $diaIndex => $dia)
                        @php
                            $ejercicios  = $dia['ejercicios'] ?? [];
                            $dayName     = $dia['nombre'] ?? $dia['name'] ?? $dia['dia'] ?? 'Día ' . ($diaIndex + 1);
                            $muscleGroup = $dia['grupo_muscular'] ?? $dia['muscle_group'] ?? null;
                            $mgColors    = $getMgColor($muscleGroup);
                            $dayMin      = max(count($ejercicios) * 6, 15);
                            $dayNotes    = $dia['notas'] ?? $dia['notes'] ?? null;
                            // Left accent color from muscle group text class
                            $borderColor = match(true) {
                                str_contains($mgColors['text'], 'rose')    => '#f43f5e',
                                str_contains($mgColors['text'], 'sky')     => '#38bdf8',
                                str_contains($mgColors['text'], 'emerald') => '#34d399',
                                str_contains($mgColors['text'], 'teal')    => '#2dd4bf',
                                str_contains($mgColors['text'], 'lime')    => '#a3e635',
                                str_contains($mgColors['text'], 'amber')   => '#fbbf24',
                                str_contains($mgColors['text'], 'violet')  => '#a78bfa',
                                str_contains($mgColors['text'], 'purple')  => '#c084fc',
                                str_contains($mgColors['text'], 'cyan')    => '#22d3ee',
                                str_contains($mgColors['text'], 'orange')  => '#fb923c',
                                str_contains($mgColors['text'], 'pink')    => '#f472b6',
                                default                                    => '#dc2626',
                            };
                        @endphp

                        <div class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary"
                             style="border-left: 3px solid {{ $borderColor }}">

                            {{-- Header --}}
                            <button
                                type="button"
                                @click="openDay = openDay === {{ $diaIndex }} ? -1 : {{ $diaIndex }}"
                                class="flex w-full items-center gap-3 px-4 py-3.5 text-left transition-colors hover:bg-wc-bg-secondary/30"
                            >
                                {{-- Day badge --}}
                                <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl"
                                     style="background: {{ $borderColor }}1a">
                                    <span class="text-[8px] font-bold uppercase leading-none tracking-widest"
                                          style="color: {{ $borderColor }}99">DÍA</span>
                                    <span class="font-display text-lg leading-none" style="color: {{ $borderColor }}">{{ $diaIndex + 1 }}</span>
                                </div>

                                {{-- Name + meta --}}
                                <div class="min-w-0 flex-1">
                                    <p class="font-display text-base tracking-wide text-wc-text">{{ strtoupper($dayName) }}</p>
                                    <div class="mt-0.5 flex items-center gap-2">
                                        <span class="text-[11px] text-wc-text-tertiary">{{ count($ejercicios) }} ejercicios · ~{{ $dayMin }} min</span>
                                        @if($muscleGroup)
                                            <span class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $mgColors['bg'] }} {{ $mgColors['text'] }}">
                                                {{ $muscleGroup }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Chevron --}}
                                <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                                     :class="{ 'rotate-180': openDay === {{ $diaIndex }} }"
                                     fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Collapsible exercise list --}}
                            <div
                                x-show="openDay === {{ $diaIndex }}"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-2"
                                x-cloak
                            >
                                {{-- Day notes --}}
                                @if($dayNotes)
                                    <div class="border-t border-wc-border bg-wc-bg-secondary/30 px-4 py-2">
                                        <p class="text-xs italic text-wc-text-tertiary">{{ $dayNotes }}</p>
                                    </div>
                                @endif

                                {{-- Exercise rows --}}
                                <div class="divide-y divide-wc-border/50 border-t border-wc-border">
                                    @forelse($ejercicios as $ejIdx => $ej)
                                        @php
                                            $ejName   = is_array($ej) ? ($ej['nombre'] ?? $ej['name'] ?? $ej['ejercicio'] ?? 'Ejercicio') : (string) $ej;
                                            $ejSeries = is_array($ej) ? ($ej['series']      ?? $ej['sets']  ?? null) : null;
                                            $ejReps   = is_array($ej) ? ($ej['repeticiones'] ?? $ej['reps']  ?? null) : null;
                                            $ejRest   = is_array($ej) ? ($ej['descanso']     ?? $ej['rest']  ?? $ej['rest_seconds'] ?? null) : null;
                                            $ejRir    = is_array($ej) ? ($ej['rir']          ?? null) : null;
                                            $ejNotas  = is_array($ej) ? ($ej['notas']        ?? $ej['notes'] ?? null) : null;
                                            $rirClass = $ejRir !== null
                                                ? ($ejRir >= 3 ? 'bg-emerald-500/15 text-emerald-400' : ($ejRir >= 2 ? 'bg-amber-500/15 text-amber-400' : 'bg-red-500/15 text-red-400'))
                                                : '';
                                            $hasNotes = !empty($ejNotas);
                                        @endphp
                                        <div x-data="{ showNote: false }">
                                            {{-- Main row --}}
                                            <div class="flex items-center gap-3 px-4 py-2.5">
                                                {{-- Index --}}
                                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary font-data text-[11px] font-black text-wc-text-tertiary">
                                                    {{ $ejIdx + 1 }}
                                                </span>

                                                {{-- Name --}}
                                                <p class="min-w-0 flex-1 text-sm font-semibold leading-snug text-wc-text">{{ $ejName }}</p>

                                                {{-- Right chips --}}
                                                <div class="flex shrink-0 items-center gap-1">
                                                    @if($ejSeries || $ejReps)
                                                        <span class="rounded-lg bg-wc-bg px-2.5 py-1 font-data text-xs font-black text-wc-text tabular-nums">
                                                            @if($ejSeries && $ejReps){{ $ejSeries }}<span class="text-wc-text-tertiary">×</span>{{ $ejReps }}
                                                            @elseif($ejSeries){{ $ejSeries }}s
                                                            @else{{ $ejReps }}r
                                                            @endif
                                                        </span>
                                                    @endif

                                                    @if($ejRest)
                                                        <span class="flex items-center gap-0.5 rounded-lg bg-wc-bg px-2 py-1 text-[10px] font-medium text-wc-text-tertiary">
                                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                            </svg>
                                                            {{ is_numeric($ejRest) ? $ejRest.'s' : $ejRest }}
                                                        </span>
                                                    @endif

                                                    @if($ejRir !== null)
                                                        <span class="rounded-lg px-2 py-1 text-[10px] font-black {{ $rirClass }}">RIR{{ $ejRir }}</span>
                                                    @endif

                                                    @if($hasNotes)
                                                        <button @click="showNote = !showNote"
                                                                class="flex h-6 w-6 items-center justify-center rounded-lg bg-wc-bg text-wc-text-tertiary transition-colors hover:text-wc-text"
                                                                :class="showNote ? 'text-wc-accent' : ''">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Coach note (collapsible) --}}
                                            @if($hasNotes)
                                                <div x-show="showNote" x-cloak
                                                     x-transition:enter="transition ease-out duration-150"
                                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                                     x-transition:enter-end="opacity-100 translate-y-0"
                                                     class="mx-4 mb-2.5 rounded-lg bg-wc-bg-secondary/50 px-3 py-2 text-xs leading-relaxed text-wc-text-secondary">
                                                    {{ $ejNotas }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="px-4 py-3 text-xs text-wc-text-tertiary">Sin ejercicios registrados.</p>
                                    @endforelse
                                </div>

                                {{-- Per-day CTA --}}
                                <div class="border-t border-wc-border px-4 py-3">
                                    <a wire:navigate href="{{ route('client.workout', ['day' => $diaIndex + 1]) }}"
                                        class="btn-press flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-bold tracking-wide transition-all"
                                        style="background: {{ $borderColor }}18; color: {{ $borderColor }}">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                        ENTRENAR DÍA {{ $diaIndex + 1 }}
                                    </a>
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
                    // Normalize: support multiple key conventions
                    $supObjetivo     = $supplementPlan['objetivo'] ?? $supplementPlan['objetivo_general'] ?? null;
                    $supCoachNotes   = $supplementPlan['notas_coach'] ?? $supplementPlan['coach_notes'] ?? $supplementPlan['notas'] ?? null;
                    $supList         = $supplementPlan['suplementos'] ?? $supplementPlan['supplements'] ?? $supplementPlan['protocolo'] ?? [];
                    $supTimingGroups = $supplementPlan['timing'] ?? $supplementPlan['horarios'] ?? null;

                    // Timing icon map
                    $timingIcons = [
                        'mañana'            => '🌅',
                        'manana'            => '🌅',
                        'morning'           => '🌅',
                        'pre-entrenamiento' => '⚡',
                        'pre entreno'       => '⚡',
                        'pre-workout'       => '⚡',
                        'preworkout'        => '⚡',
                        'post-entrenamiento'=> '🔄',
                        'post entreno'      => '🔄',
                        'post-workout'      => '🔄',
                        'postworkout'       => '🔄',
                        'con comidas'       => '🍽️',
                        'con comida'        => '🍽️',
                        'with meals'        => '🍽️',
                        'noche'             => '🌙',
                        'night'             => '🌙',
                        'antes de dormir'   => '🌙',
                        'bedtime'           => '🌙',
                        'diario'            => '📅',
                        'daily'             => '📅',
                        'cualquier momento' => '📅',
                    ];
                    $getTimingIcon = fn($timing) => $timingIcons[mb_strtolower(trim($timing ?? ''))] ?? '💊';
                @endphp

                <div class="space-y-5">
                    {{-- Objetivo --}}
                    @if($supObjetivo)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Objetivo del protocolo</p>
                                    <p class="mt-0.5 text-sm font-medium text-wc-text">{{ $supObjetivo }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Supplement list --}}
                    @if(count($supList) > 0)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                            <div class="flex items-center justify-between border-b border-wc-border px-5 py-4">
                                <h3 class="font-display text-lg tracking-wide text-wc-text">SUPLEMENTOS</h3>
                                <span class="rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-semibold text-wc-accent">
                                    {{ count($supList) }} suplementos
                                </span>
                            </div>
                            <div class="divide-y divide-wc-border">
                                @foreach($supList as $idx => $sup)
                                    @php
                                        $supName   = is_array($sup) ? ($sup['nombre'] ?? $sup['name'] ?? $sup['suplemento'] ?? "Suplemento " . ($idx + 1)) : $sup;
                                        $supDosis  = is_array($sup) ? ($sup['dosis'] ?? $sup['dose'] ?? $sup['cantidad'] ?? null) : null;
                                        $supMom    = is_array($sup) ? ($sup['momento'] ?? $sup['timing'] ?? $sup['horario'] ?? $sup['cuando'] ?? null) : null;
                                        $supNotas  = is_array($sup) ? ($sup['notas'] ?? $sup['notes'] ?? $sup['beneficio'] ?? $sup['benefit'] ?? null) : null;
                                        $timingIcon = $getTimingIcon($supMom);
                                    @endphp
                                    <div class="flex items-start gap-4 px-5 py-4">
                                        {{-- Index badge --}}
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary">
                                            <span class="font-data text-xs font-bold text-wc-accent">{{ $idx + 1 }}</span>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                                <span class="font-semibold text-wc-text">{{ $supName }}</span>
                                                @if($supDosis)
                                                    <span class="rounded bg-wc-bg-secondary px-2 py-0.5 font-data text-xs font-semibold text-wc-accent">{{ $supDosis }}</span>
                                                @endif
                                            </div>

                                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                                @if($supMom)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs text-wc-text-secondary">
                                                        <span>{{ $timingIcon }}</span>
                                                        <span>{{ $supMom }}</span>
                                                    </span>
                                                @endif
                                                @if($supNotas)
                                                    <span class="text-xs text-wc-text-tertiary">{{ $supNotas }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Timing groups (if structured as timing/horarios) --}}
                    @if($supTimingGroups && is_array($supTimingGroups))
                        <div class="space-y-3">
                            <h3 class="font-display text-sm tracking-wider text-wc-text-tertiary uppercase px-1">PROTOCOLO POR MOMENTO</h3>
                            @foreach($supTimingGroups as $moment => $items)
                                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                                    <p class="mb-3 font-display text-sm tracking-wide text-wc-text">
                                        {{ $getTimingIcon($moment) }} {{ strtoupper($moment) }}
                                    </p>
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

                    {{-- Coach notes --}}
                    @if($supCoachNotes)
                        <div class="rounded-xl border-l-4 border-wc-accent bg-wc-bg-tertiary p-5">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-accent">Notas del coach</p>
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $supCoachNotes }}</p>
                        </div>
                    @endif

                    {{-- Empty list fallback --}}
                    @if(count($supList) === 0 && !$supTimingGroups)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                            <p class="text-sm text-wc-text-secondary">Tu coach está preparando tu protocolo de suplementación.</p>
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
            {{-- Compliance bar --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-display text-lg tracking-wide text-wc-text">CUMPLIMIENTO MENSUAL</h3>
                        <p class="mt-0.5 text-sm text-wc-text-secondary">Dias con al menos 1 habito registrado este mes</p>
                    </div>
                    <span class="font-data text-3xl font-bold text-wc-accent">{{ $habitCompliance }}%</span>
                </div>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-wc-accent transition-all" style="width: {{ $habitCompliance }}%"></div>
                </div>
            </div>

            {{-- Habit Cards --}}
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($habitData as $habit)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-display text-base tracking-wide text-wc-text">{{ strtoupper($habit['label']) }}</h4>
                                <p class="mt-1 text-xs text-wc-text-tertiary">
                                    Racha: <span class="font-data font-semibold text-wc-text">{{ $habit['streak'] }} dias</span>
                                </p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-bg-secondary">
                                @if($habit['icon'] === 'droplet')
                                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c-4.97 0-9-4.03-9-9 0-3.87 4.5-9.5 7.68-12.38a1.74 1.74 0 012.64 0C16.5 2.5 21 8.13 21 12c0 4.97-4.03 9-9 9z"/></svg>
                                @elseif($habit['icon'] === 'moon')
                                    <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/></svg>
                                @elseif($habit['icon'] === 'utensils')
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v6m0 0c1.66 0 3-1.34 3-3S13.66 2 12 2s-3 1.34-3 3 1.34 3 3 3zm0 0v14m6-20v8a2 2 0 01-2 2h-1v10"/></svg>
                                @elseif($habit['icon'] === 'brain')
                                    <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-xs text-wc-text-tertiary">
                                Promedio: <span class="font-data font-semibold text-wc-text">{{ $habit['average'] }}/10</span>
                            </p>
                        </div>

                        {{-- Last 7 days dots --}}
                        <div class="mt-3 flex items-end gap-1.5">
                            @foreach($habit['last7'] as $day)
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div
                                        class="h-6 w-full rounded-sm transition-all {{ $day['value'] > 0 ? '' : 'bg-wc-bg-secondary' }}"
                                        style="{{ $day['value'] > 0 ? 'background-color: rgba(220, 38, 38, ' . min($day['value'] / 10, 1) . ');' : '' }}"
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
        @else
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
                        { color: '#f87171', start: 0,       days: 5        },
                        { color: '#4ade80', start: 5,        days: 8        },
                        { color: '#fbbf24', start: 13,       days: 3        },
                        { color: '#c084fc', start: 16,       days: cl - 16  },
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
                        {{-- Ring --}}
                        <div class="relative shrink-0">
                            <svg width="140" height="140" viewBox="0 0 140 140" class="-rotate-90">
                                {{-- Track --}}
                                <circle cx="70" cy="70" r="54" fill="none" stroke-width="10"
                                    style="stroke: currentColor; opacity: 0.12;" class="text-wc-text"/>
                                {{-- Phase arcs (always visible) --}}
                                <template x-for="arc in phaseArcs" :key="arc.color">
                                    <circle
                                        cx="70" cy="70" r="54"
                                        fill="none"
                                        :stroke="arc.color"
                                        stroke-width="10"
                                        stroke-linecap="butt"
                                        :stroke-dasharray="arc.dasharray"
                                        :stroke-dashoffset="arc.dashoffset"
                                    />
                                </template>
                                {{-- Current day dot marker --}}
                                <template x-if="currentDay">
                                    <circle
                                        cx="70" cy="70" r="54"
                                        fill="none"
                                        stroke="white"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                        :stroke-dasharray="'4 ' + (2 * Math.PI * 54)"
                                        :stroke-dashoffset="dotOffset"
                                        style="filter: drop-shadow(0 0 4px rgba(255,255,255,0.9));"
                                    />
                                </template>
                            </svg>
                            {{-- Center text --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-2xl" x-text="phaseData.emoji"></span>
                                <span class="font-data text-2xl font-black leading-none" :class="phaseData.text" x-text="currentDay"></span>
                                <span class="text-[10px] text-wc-text-tertiary font-medium uppercase tracking-wide">día</span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="mt-4 sm:mt-0 text-center sm:text-left flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-wc-text-tertiary mb-1">Fase actual</p>
                            <h2 class="font-display text-4xl tracking-wide leading-none" :class="phaseData.text" x-text="phaseData.name"></h2>
                            <p class="mt-2 font-data text-sm text-wc-text-secondary">
                                Día <span class="font-bold text-wc-text" x-text="currentDay"></span>
                                de <span x-text="cycleLength"></span>
                            </p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">
                                Próximo ciclo en <span x-text="daysUntilNext" class="font-semibold text-wc-text-secondary"></span> días
                            </p>

                            {{-- Energy dots --}}
                            <div class="mt-3 flex items-center gap-1.5">
                                <span class="text-[10px] font-medium text-wc-text-tertiary uppercase tracking-wider">Energía</span>
                                <div class="flex gap-0.5">
                                    <template x-for="i in 10">
                                        <div class="h-2 w-2 rounded-full transition-colors"
                                            :class="i <= phaseData.energy ? phaseData.text.replace('text-','bg-') : 'bg-wc-bg-secondary'"></div>
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

            {{-- Config form (collapsible) --}}
            <div x-show="showConfig"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5"
            >
                <h3 class="font-display text-base tracking-wide text-wc-text mb-4">CONFIGURACIÓN DEL CICLO</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">
                            📅 Fecha de inicio del último ciclo
                        </label>
                        <input
                            type="date"
                            x-model="startDate"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1.5">
                            🔄 Duración del ciclo (días)
                        </label>
                        <input
                            type="number"
                            x-model.number="cycleLength"
                            min="21" max="40"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        />
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <button
                        @click="save()"
                        class="btn-press rounded-lg bg-wc-accent px-5 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors"
                    >Guardar</button>
                    <p class="text-xs text-wc-text-tertiary">Los datos se guardan localmente en tu dispositivo.</p>
                </div>
            </div>

            {{-- Recommendations cards --}}
            <template x-if="currentDay && phaseData">
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Training --}}
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-lg"
                                :class="phaseData.bg">🏋️</span>
                            <h4 class="font-display text-sm tracking-wide text-wc-text">ENTRENAMIENTO</h4>
                        </div>
                        <p class="text-sm leading-relaxed text-wc-text-secondary" x-text="phaseData.train"></p>
                    </div>
                    {{-- Nutrition --}}
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-lg"
                                :class="phaseData.bg">🥗</span>
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
                    <div class="h-full transition-all" style="width: 18%; background:#f87171;" title="Menstrual"></div>
                    <div class="h-full transition-all" style="width: 32%; background:#4ade80;" title="Folicular"></div>
                    <div class="h-full transition-all" style="width: 11%; background:#fbbf24;" title="Ovulatoria"></div>
                    <div class="h-full flex-1" style="background:#c084fc;" title="Lutea"></div>
                </div>
                <p class="text-[9px] font-medium text-wc-text-tertiary mb-3 text-center uppercase tracking-widest">Toca una fase para ver detalle</p>
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
                        <div
                            x-data="{ flipped: false }"
                            @click="flipped = !flipped"
                            class="cursor-pointer select-none"
                            style="perspective: 700px; height: 130px;"
                        >
                            <div
                                style="position: relative; width: 100%; height: 100%; transform-style: preserve-3d; transition: transform 0.45s cubic-bezier(.4,0,.2,1);"
                                :style="flipped ? 'transform:rotateY(180deg)' : 'transform:rotateY(0deg)'"
                            >
                                {{-- Front --}}
                                <div class="absolute inset-0 rounded-xl p-3 {{ $pc['border'] }} {{ $pc['bg_f'] }}"
                                    style="backface-visibility:hidden;">
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <div class="h-2 w-2 shrink-0 rounded-full {{ $pc['dot'] }}"></div>
                                        <p class="text-xs font-semibold {{ $pc['text'] }}">{{ $pc['name'] }}</p>
                                    </div>
                                    <p class="text-[10px] text-wc-text-tertiary">Días {{ $pc['days'] }}</p>
                                    <p class="text-[10px] text-wc-text-tertiary mt-0.5">{{ $pc['sub'] }}</p>
                                    <div class="mt-3 flex items-center gap-1 {{ $pc['text'] }} opacity-50">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span class="text-[9px] font-medium">Ver detalle</span>
                                    </div>
                                </div>
                                {{-- Back --}}
                                <div class="absolute inset-0 overflow-y-auto rounded-xl p-3 {{ $pc['border'] }} {{ $pc['bg_b'] }}"
                                    style="backface-visibility:hidden; transform:rotateY(180deg);">
                                    <p class="text-[10px] font-bold {{ $pc['text'] }} mb-1">🏋️ Entreno</p>
                                    <p class="text-[10px] text-wc-text-secondary leading-relaxed">{{ $pc['train'] }}</p>
                                    <div class="mt-2 border-t {{ $pc['border'] }} pt-2">
                                        <p class="text-[10px] font-bold {{ $pc['text'] }} mb-1">🥗 Nutrición</p>
                                        <p class="text-[10px] text-wc-text-secondary leading-relaxed">{{ $pc['nutr'] }}</p>
                                    </div>
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
