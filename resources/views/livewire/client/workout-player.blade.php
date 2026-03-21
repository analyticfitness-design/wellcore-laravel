<div
    class="min-h-screen pb-32"
    x-data="workoutPlayer()"
    x-init="initAnimations()"
>
    {{-- ============================================================ --}}
    {{-- EMPTY STATE — No plan assigned                               --}}
    {{-- ============================================================ --}}
    @if(empty($days))
        <div class="flex min-h-[60vh] items-center justify-center px-4">
            <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-tertiary p-10 text-center" data-animate="fadeInUp">
                {{-- Clipboard icon --}}
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-bg-secondary">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                </div>
                <h2 class="mt-5 font-display text-2xl tracking-wide text-wc-text">TU PLAN VIENE EN CAMINO</h2>
                <p class="mt-2 text-sm text-wc-text-secondary">Tu coach esta preparando tu plan de entrenamiento personalizado.</p>
                <p class="mt-4 text-xs text-wc-text-tertiary">Cuando tu plan este listo, lo veras aqui.</p>
                <div class="mt-6 h-1 w-16 mx-auto rounded-full bg-gradient-to-r from-wc-accent/40 to-wc-accent/10"></div>
            </div>
        </div>
    @else

    {{-- ============================================================ --}}
    {{-- HEADER — Day selector pills                                  --}}
    {{-- ============================================================ --}}
    <div class="sticky top-0 z-30 bg-wc-bg/95 backdrop-blur-md border-b border-wc-border" data-animate="fadeInDown">
        <div class="px-4 py-3">
            {{-- Day pills --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none -mx-1 px-1">
                @foreach($days as $index => $day)
                    <button
                        wire:click="switchDay({{ $index }})"
                        class="btn-press shrink-0 flex flex-col items-center gap-0.5 rounded-xl px-4 py-2.5 transition-all
                            {{ $currentDayIndex === $index
                                ? 'bg-wc-accent text-white shadow-lg shadow-wc-accent/25'
                                : 'bg-wc-bg-tertiary border border-wc-border text-wc-text-secondary hover:text-wc-text hover:border-wc-text-tertiary' }}"
                    >
                        <span class="font-display text-base tracking-wider leading-none">DIA {{ $index + 1 }}</span>
                        @if(!empty($day['muscle_group']))
                            <span class="text-[9px] font-medium uppercase tracking-wider leading-none opacity-75 max-w-[80px] truncate">
                                {{ $day['muscle_group'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Active session timer (only when workout is active) --}}
            @if($isActive)
                <div class="mt-2 flex items-center justify-between rounded-lg bg-wc-accent/10 border border-wc-accent/20 px-3 py-2">
                    <div class="flex items-center gap-2">
                        {{-- Pulsing dot --}}
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-wc-accent opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-wc-accent"></span>
                        </span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-wc-accent">En curso</span>
                    </div>
                    <span class="font-data text-lg font-bold text-wc-accent tabular-nums" x-text="elapsedDisplay"></span>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MAIN CONTENT                                                 --}}
    {{-- ============================================================ --}}
    <div class="px-4 mt-4 space-y-4">

        {{-- ======================================================== --}}
        {{-- PRE-WORKOUT STATE                                        --}}
        {{-- ======================================================== --}}
        @if(!$isActive)

            {{-- Progress summary --}}
            <div class="flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3" data-animate="fadeInUp">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                    </svg>
                    <span class="text-sm text-wc-text-secondary">
                        <span class="font-data font-semibold text-wc-text">{{ count($exercises) }}</span> ejercicios
                        <span class="text-wc-text-tertiary mx-1">&middot;</span>
                        <span class="text-wc-text-tertiary">~{{ max(count($exercises) * 8, 20) }} min estimado</span>
                    </span>
                </div>
            </div>

            {{-- Exercise preview cards --}}
            @foreach($exercises as $exIndex => $exercise)
                @php
                    $blockType = $exercise['block_type'] ?? null;
                    $isFirstInBlock = $blockType && ($exIndex === 0 || ($exercises[$exIndex - 1]['block_type'] ?? null) !== $blockType || ($exercises[$exIndex - 1]['block_id'] ?? null) !== ($exercise['block_id'] ?? null));
                    $isInBlock = $blockType && in_array($blockType, ['superset', 'circuito']);
                @endphp

                {{-- Block label (superset/circuit) --}}
                @if($isFirstInBlock && $isInBlock)
                    <div class="flex items-center gap-2 mt-2" data-animate="fadeInUp" data-animate-delay="{{ min(($exIndex + 1) * 100, 600) }}">
                        <span class="rounded-full bg-wc-accent/15 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-wc-accent">
                            {{ $blockType === 'superset' ? 'SUPERSET' : 'CIRCUITO' }}
                        </span>
                        <div class="h-px flex-1 bg-wc-accent/15"></div>
                    </div>
                @endif

                <div
                    class="rounded-2xl border bg-wc-bg-tertiary p-4 transition-all
                        {{ $isInBlock ? 'border-l-2 border-l-wc-accent border-wc-border ml-2' : 'border-wc-border' }}"
                    data-animate="fadeInUp"
                    data-animate-delay="{{ min(($exIndex + 1) * 100, 600) }}"
                >
                    <div class="flex items-start gap-3">
                        {{-- Exercise number --}}
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-wc-bg-secondary">
                            <span class="font-data text-sm font-bold text-wc-text-secondary">{{ $exIndex + 1 }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            {{-- Exercise name --}}
                            <h3 class="font-display text-lg tracking-wide leading-tight text-wc-text uppercase">
                                {{ $exercise['name'] ?? 'Ejercicio' }}
                            </h3>

                            {{-- Meta row --}}
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                {{-- Sets x Reps --}}
                                <span class="inline-flex items-center rounded-lg bg-wc-bg-secondary px-2 py-0.5 text-xs font-medium text-wc-text-secondary">
                                    <span class="font-data font-semibold text-wc-text">{{ $exercise['sets'] ?? 3 }}</span>
                                    <span class="mx-0.5 text-wc-text-tertiary">&times;</span>
                                    <span class="font-data font-semibold text-wc-text">{{ $exercise['reps'] ?? 10 }}</span>
                                </span>

                                {{-- Equipment --}}
                                @if(!empty($exercise['equipment']))
                                    <span class="inline-flex items-center rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                                        {{ $exercise['equipment'] }}
                                    </span>
                                @endif

                                {{-- Rest --}}
                                @if(!empty($exercise['rest_seconds']))
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        {{ $exercise['rest_seconds'] }}s
                                    </span>
                                @endif

                                {{-- RIR --}}
                                @if(isset($exercise['rir']))
                                    @php
                                        $rirColor = $exercise['rir'] >= 3 ? 'text-emerald-400' : ($exercise['rir'] >= 2 ? 'text-yellow-400' : 'text-red-400');
                                    @endphp
                                    <span class="inline-flex items-center rounded-lg bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-bold {{ $rirColor }}">
                                        RIR {{ $exercise['rir'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- START WORKOUT CTA --}}
            <div class="pt-2 pb-4" data-animate="fadeInUp" data-animate-delay="400">
                <button
                    wire:click="startWorkout()"
                    class="btn-press btn-ripple w-full rounded-2xl bg-wc-accent py-4 text-center shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover transition-colors pulse-glow"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75"
                >
                    <span wire:loading.remove class="font-display text-xl tracking-widest text-white">INICIAR ENTRENAMIENTO</span>
                    <span wire:loading class="inline-flex items-center gap-2 text-white">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span class="font-display text-xl tracking-widest">PREPARANDO...</span>
                    </span>
                </button>
            </div>

        @else

        {{-- ======================================================== --}}
        {{-- ACTIVE WORKOUT STATE                                     --}}
        {{-- ======================================================== --}}

            {{-- Progress bar --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3" data-animate="fadeInUp">
                @php
                    $completedExercises = 0;
                    foreach ($exercises as $ei => $ex) {
                        $allSetsForEx = $setData[$ei] ?? [];
                        $setsCompleted = collect($allSetsForEx)->where('completed', true)->count();
                        $totalSets = $ex['sets'] ?? 3;
                        if ($setsCompleted >= $totalSets) $completedExercises++;
                    }
                    $totalExercises = count($exercises);
                    $progressPct = $totalExercises > 0 ? round(($completedExercises / $totalExercises) * 100) : 0;
                @endphp
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-wc-text-secondary">
                        <span class="font-data font-semibold text-wc-text">{{ $completedExercises }}</span>/{{ $totalExercises }} ejercicios completados
                    </span>
                    <span class="font-data text-xs font-bold text-wc-accent">{{ $progressPct }}%</span>
                </div>
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700 ease-out"
                        style="width: {{ $progressPct }}%"
                    ></div>
                </div>
            </div>

            {{-- Exercise cards --}}
            @foreach($exercises as $exIndex => $exercise)
                @php
                    $totalSets = $exercise['sets'] ?? 3;
                    $exSetData = $setData[$exIndex] ?? [];
                    $setsCompletedCount = collect($exSetData)->where('completed', true)->count();
                    $allComplete = $setsCompletedCount >= $totalSets;
                    $blockType = $exercise['block_type'] ?? null;
                    $isFirstInBlock = $blockType && ($exIndex === 0 || ($exercises[$exIndex - 1]['block_type'] ?? null) !== $blockType || ($exercises[$exIndex - 1]['block_id'] ?? null) !== ($exercise['block_id'] ?? null));
                    $isInBlock = $blockType && in_array($blockType, ['superset', 'circuito']);
                @endphp

                {{-- Block label --}}
                @if($isFirstInBlock && $isInBlock)
                    <div class="flex items-center gap-2 mt-3" data-animate="fadeInUp" data-animate-delay="{{ min(($exIndex + 1) * 100, 600) }}">
                        <span class="rounded-full bg-wc-accent/15 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-wc-accent">
                            {{ $blockType === 'superset' ? 'SUPERSET' : 'CIRCUITO' }}
                        </span>
                        <div class="h-px flex-1 bg-wc-accent/15"></div>
                    </div>
                @endif

                <div
                    id="exercise-{{ $exIndex }}"
                    class="rounded-2xl border bg-wc-bg-tertiary overflow-hidden transition-all
                        {{ $isInBlock ? 'ml-2' : '' }}
                        {{ $allComplete
                            ? 'border-l-[3px] border-l-emerald-500 border-emerald-500/20'
                            : 'border-wc-border' }}"
                    data-animate="fadeInUp"
                    data-animate-delay="{{ min(($exIndex + 1) * 100, 600) }}"
                >
                    {{-- Exercise header --}}
                    <div class="p-4 pb-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-xs font-bold text-wc-text-tertiary font-data">
                                        {{ $exIndex + 1 }}
                                    </span>
                                    <h3 class="font-display text-lg tracking-wide leading-tight text-wc-text uppercase">
                                        {{ $exercise['name'] ?? 'Ejercicio' }}
                                    </h3>
                                </div>

                                {{-- Badges row --}}
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    {{-- Equipment --}}
                                    @if(!empty($exercise['equipment']))
                                        <span class="inline-flex items-center rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                                            {{ $exercise['equipment'] }}
                                        </span>
                                    @endif

                                    {{-- RIR --}}
                                    @if(isset($exercise['rir']))
                                        @php
                                            $rirColor = $exercise['rir'] >= 3 ? 'text-emerald-400 bg-emerald-500/10' : ($exercise['rir'] >= 2 ? 'text-yellow-400 bg-yellow-500/10' : 'text-red-400 bg-red-500/10');
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $rirColor }}">
                                            RIR {{ $exercise['rir'] }}
                                        </span>
                                    @endif

                                    {{-- Rest --}}
                                    @if(!empty($exercise['rest_seconds']))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            {{ $exercise['rest_seconds'] }}s descanso
                                        </span>
                                    @endif

                                    {{-- Completed badge --}}
                                    @if($allComplete)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[10px] font-bold text-emerald-400">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            Completado
                                        </span>
                                    @endif
                                </div>

                                {{-- Last weight used --}}
                                @if(!empty($exercise['last_weight']) && !empty($exercise['last_reps']))
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <svg class="h-3 w-3 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span class="text-[11px] text-wc-text-tertiary">
                                            Ultimo: <span class="font-data font-semibold text-wc-text-secondary">{{ $exercise['last_weight'] }} kg</span>
                                            <span class="text-wc-text-tertiary">&times;</span>
                                            <span class="font-data font-semibold text-wc-text-secondary">{{ $exercise['last_reps'] }}</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Coach notes (collapsible) --}}
                        @if(!empty($exercise['notes']))
                            <div x-data="{ open: false }" class="mt-3">
                                <button
                                    @click="open = !open"
                                    class="flex items-center gap-1 text-[11px] font-medium text-wc-text-tertiary hover:text-wc-text-secondary transition-colors"
                                >
                                    <svg class="h-3 w-3 transition-transform" :class="open && 'rotate-90'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                    Notas del coach
                                </button>
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="mt-1.5 rounded-lg bg-wc-bg-secondary px-3 py-2 text-xs leading-relaxed text-wc-text-tertiary"
                                    x-cloak
                                >
                                    {{ $exercise['notes'] }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Set grid --}}
                    <div class="border-t border-wc-border">
                        {{-- Table header --}}
                        <div class="grid grid-cols-[44px_1fr_1fr_48px] gap-1 px-4 py-2 bg-wc-bg-secondary/50">
                            <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Set</span>
                            <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Peso (kg)</span>
                            <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Reps</span>
                            <span class="sr-only">Completar</span>
                        </div>

                        {{-- Set rows --}}
                        @for($setNum = 1; $setNum <= $totalSets; $setNum++)
                            @php
                                $currentSet = $exSetData[$setNum] ?? [];
                                $isCompleted = !empty($currentSet['completed']);
                                $isPr = !empty($currentSet['is_pr']);
                                $setWeight = $currentSet['weight_kg'] ?? '';
                                $setReps = $currentSet['reps'] ?? '';
                            @endphp
                            <div
                                class="grid grid-cols-[44px_1fr_1fr_48px] gap-1 items-center px-4 py-2 transition-colors
                                    {{ $isCompleted ? 'bg-emerald-500/5' : '' }}
                                    {{ $setNum < $totalSets ? 'border-b border-wc-border/50' : '' }}"
                                x-data="{
                                    weight: {{ $setWeight ?: 0 }},
                                    reps: {{ $setReps ?: ($exercise['reps'] ?? 10) }},
                                    completed: {{ $isCompleted ? 'true' : 'false' }},
                                    isPr: {{ $isPr ? 'true' : 'false' }},
                                    justCompleted: false
                                }"
                            >
                                {{-- Set number --}}
                                <div class="flex items-center justify-center">
                                    <span class="font-data text-sm font-bold {{ $isCompleted ? 'text-emerald-400' : 'text-wc-text-tertiary' }}">
                                        {{ $setNum }}
                                    </span>
                                    @if($isPr)
                                        <span class="badge-shine ml-0.5 rounded bg-gradient-to-r from-yellow-500 to-amber-400 px-1 py-0.5 text-[8px] font-black text-black leading-none">
                                            PR!
                                        </span>
                                    @endif
                                </div>

                                {{-- Weight input with +/- --}}
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        @click="weight = Math.max(0, weight - 2.5)"
                                        class="btn-press flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                                        :disabled="completed"
                                        :class="completed && 'opacity-30 pointer-events-none'"
                                        aria-label="Reducir peso"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                    </button>
                                    <input
                                        type="number"
                                        step="0.5"
                                        min="0"
                                        x-model.number="weight"
                                        wire:model.live.debounce.500ms="setData.{{ $exIndex }}.{{ $setNum }}.weight_kg"
                                        class="h-8 w-16 rounded-lg border border-wc-border bg-wc-bg px-1 text-center font-data text-sm font-semibold text-wc-text focus:border-wc-accent focus:outline-none tabular-nums
                                            {{ $isCompleted ? 'opacity-60' : '' }}"
                                        :disabled="completed"
                                        placeholder="0"
                                    />
                                    <button
                                        @click="weight += 2.5"
                                        class="btn-press flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                                        :disabled="completed"
                                        :class="completed && 'opacity-30 pointer-events-none'"
                                        aria-label="Aumentar peso"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    </button>
                                </div>

                                {{-- Reps input with +/- --}}
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        @click="reps = Math.max(0, reps - 1)"
                                        class="btn-press flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                                        :disabled="completed"
                                        :class="completed && 'opacity-30 pointer-events-none'"
                                        aria-label="Reducir reps"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                    </button>
                                    <input
                                        type="number"
                                        min="0"
                                        x-model.number="reps"
                                        wire:model.live.debounce.500ms="setData.{{ $exIndex }}.{{ $setNum }}.reps"
                                        class="h-8 w-14 rounded-lg border border-wc-border bg-wc-bg px-1 text-center font-data text-sm font-semibold text-wc-text focus:border-wc-accent focus:outline-none tabular-nums
                                            {{ $isCompleted ? 'opacity-60' : '' }}"
                                        :disabled="completed"
                                        placeholder="0"
                                    />
                                    <button
                                        @click="reps += 1"
                                        class="btn-press flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                                        :disabled="completed"
                                        :class="completed && 'opacity-30 pointer-events-none'"
                                        aria-label="Aumentar reps"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    </button>
                                </div>

                                {{-- Complete set button --}}
                                <div class="flex items-center justify-center">
                                    <button
                                        x-show="!completed"
                                        @click="
                                            if (weight > 0 && reps > 0) {
                                                $wire.completeSet({{ $exIndex }}, {{ $setNum }}, weight, reps);
                                                completed = true;
                                                justCompleted = true;
                                                setTimeout(() => justCompleted = false, 800);
                                            }
                                        "
                                        class="btn-press flex h-9 w-9 items-center justify-center rounded-xl border-2 border-wc-border text-wc-text-tertiary hover:border-emerald-500 hover:text-emerald-400 transition-all"
                                        :class="(weight <= 0 || reps <= 0) && 'opacity-30 cursor-not-allowed'"
                                        aria-label="Completar serie"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </button>
                                    <div
                                        x-show="completed"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500 text-white"
                                        :class="justCompleted && 'animate-bounce'"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="scale-50 opacity-0"
                                        x-transition:enter-end="scale-100 opacity-100"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endforeach

        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- STICKY BOTTOM BAR (active workout)                           --}}
    {{-- ============================================================ --}}
    @if($isActive)
        @php
            $totalSetsAll = 0;
            $completedSetsAll = 0;
            $totalRepsAll = 0;
            $totalVolumeAll = 0;
            $hasAtLeastOnePerExercise = true;

            foreach ($exercises as $ei => $ex) {
                $exSets = $setData[$ei] ?? [];
                $totalSetsAll += $ex['sets'] ?? 3;
                $exHasOne = false;
                foreach ($exSets as $sn => $sd) {
                    if (!empty($sd['completed'])) {
                        $completedSetsAll++;
                        $totalRepsAll += (int)($sd['reps'] ?? 0);
                        $totalVolumeAll += ((float)($sd['weight_kg'] ?? 0)) * ((int)($sd['reps'] ?? 0));
                        $exHasOne = true;
                    }
                }
                if (!$exHasOne) $hasAtLeastOnePerExercise = false;
            }
        @endphp

        <div class="fixed bottom-0 inset-x-0 z-40 border-t border-wc-border bg-wc-bg/95 backdrop-blur-md safe-area-pb">
            <div class="px-4 py-3">
                {{-- Session stats --}}
                <div class="mb-2.5 flex items-center justify-center gap-4 text-center">
                    <div>
                        <span class="font-data text-sm font-bold text-wc-text">{{ $completedSetsAll }}</span>
                        <span class="text-[10px] text-wc-text-tertiary ml-0.5">sets</span>
                    </div>
                    <div class="h-3 w-px bg-wc-border"></div>
                    <div>
                        <span class="font-data text-sm font-bold text-wc-text">{{ $totalRepsAll }}</span>
                        <span class="text-[10px] text-wc-text-tertiary ml-0.5">reps</span>
                    </div>
                    <div class="h-3 w-px bg-wc-border"></div>
                    <div>
                        <span class="font-data text-sm font-bold text-wc-text">{{ number_format($totalVolumeAll, 0) }}</span>
                        <span class="text-[10px] text-wc-text-tertiary ml-0.5">kg vol.</span>
                    </div>
                </div>

                {{-- Complete session button --}}
                <button
                    wire:click="completeWorkout()"
                    @if(!$hasAtLeastOnePerExercise) disabled @endif
                    class="btn-press w-full rounded-2xl py-3.5 text-center font-display text-lg tracking-widest transition-all
                        {{ $hasAtLeastOnePerExercise
                            ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/25 hover:bg-emerald-500'
                            : 'bg-wc-bg-secondary text-wc-text-tertiary cursor-not-allowed' }}"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75"
                >
                    <span wire:loading.remove>COMPLETAR SESION</span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        GUARDANDO...
                    </span>
                </button>
            </div>
        </div>
    @endif

    @endif {{-- end of !empty($days) --}}

    {{-- ============================================================ --}}
    {{-- ALPINE.JS — Session timer + scroll animations                --}}
    {{-- ============================================================ --}}
    <script>
        function workoutPlayer() {
            return {
                elapsed: 0,
                timerInterval: null,

                get elapsedDisplay() {
                    const h = Math.floor(this.elapsed / 3600);
                    const m = Math.floor((this.elapsed % 3600) / 60);
                    const s = this.elapsed % 60;
                    const mm = String(m).padStart(2, '0');
                    const ss = String(s).padStart(2, '0');
                    return h > 0
                        ? String(h) + ':' + mm + ':' + ss
                        : mm + ':' + ss;
                },

                init() {
                    // Start timer if workout is active
                    @if($isActive && $startTime)
                        this.elapsed = Math.floor((Date.now() / 1000) - {{ $startTime }});
                        this.startTimer();
                    @endif
                },

                startTimer() {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timerInterval = setInterval(() => {
                        this.elapsed++;
                    }, 1000);
                },

                stopTimer() {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                        this.timerInterval = null;
                    }
                },

                initAnimations() {
                    this.$nextTick(() => {
                        const elements = document.querySelectorAll('[data-animate]');
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    entry.target.classList.add('animate-in');
                                    observer.unobserve(entry.target);
                                }
                            });
                        }, { threshold: 0.1, rootMargin: '0px 0px -20px 0px' });

                        elements.forEach(el => observer.observe(el));
                    });
                },

                destroy() {
                    this.stopTimer();
                }
            };
        }
    </script>

    <style>
        /* Hide number input spinners for cleaner look */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Hide scrollbar for day pills */
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }

        /* Safe area padding for iOS bottom bar */
        .safe-area-pb { padding-bottom: env(safe-area-inset-bottom, 0px); }

        /* PR badge golden shimmer */
        @keyframes prShine {
            0% { background-position: -100% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</div>
