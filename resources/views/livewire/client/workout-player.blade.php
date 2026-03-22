<div
    class="min-h-screen pb-32"
    x-data="workoutPlayer()"
    x-init="initAnimations()"
    x-on:open-rest-timer.window="startRestTimer($event.detail.seconds)"
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
                    @php
                        $dayMuscle = $day['grupo_muscular'] ?? $day['muscle_group'] ?? null;
                        $dayLabel  = $day['nombre'] ?? $day['name'] ?? $day['dia'] ?? null;
                    @endphp
                    <button
                        wire:click="switchDay({{ $index }})"
                        @click="clearRestTimer()"
                        @class([
                            'btn-press shrink-0 flex flex-col items-center gap-0.5 rounded-xl px-4 py-2.5 transition-all',
                            'bg-wc-accent text-white shadow-lg shadow-wc-accent/25' => $currentDayIndex === $index,
                            'bg-wc-bg-tertiary border border-wc-border text-wc-text-secondary hover:text-wc-text hover:border-wc-text-tertiary' => $currentDayIndex !== $index && !$isActive,
                            'bg-wc-bg-tertiary border border-wc-border text-wc-text-tertiary opacity-50 cursor-not-allowed' => $currentDayIndex !== $index && $isActive,
                        ])
                        @if($currentDayIndex !== $index && $isActive) title="No puedes cambiar de día con un entrenamiento en curso" @endif
                    >
                        <span class="font-display text-base tracking-wider leading-none">DIA {{ $index + 1 }}</span>
                        @if($dayMuscle)
                            <span class="text-[9px] font-medium uppercase tracking-wider leading-none opacity-75 max-w-[80px] truncate">
                                {{ $dayMuscle }}
                            </span>
                        @elseif($dayLabel)
                            <span class="text-[9px] font-medium uppercase tracking-wider leading-none opacity-75 max-w-[80px] truncate">
                                {{ $dayLabel }}
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
                    // ── Key normalization (handles both Spanish and English JSON keys) ──
                    $exName     = $exercise['nombre']      ?? $exercise['name']         ?? $exercise['ejercicio']    ?? 'Ejercicio';
                    $exSeries   = $exercise['series']      ?? $exercise['sets']          ?? null;
                    $exReps     = $exercise['repeticiones']?? $exercise['reps']          ?? null;
                    $exDescanso = $exercise['descanso']    ?? $exercise['rest']          ?? $exercise['rest_seconds'] ?? null;
                    $exRir      = $exercise['rir']         ?? null;
                    $exNotas    = $exercise['notas']       ?? $exercise['notes']         ?? null;
                    $exEquip    = $exercise['equipo']      ?? $exercise['equipment']     ?? null;
                    $exMuscle   = $exercise['musculo']     ?? $exercise['muscle_group']  ?? null;

                    // Block type
                    $blockType      = strtolower($exercise['bloque'] ?? $exercise['block_type'] ?? 'normal');
                    $isInBlock      = in_array($blockType, ['superset', 'circuito']);
                    $blockGroupId   = $exercise['grupo_id'] ?? $exercise['group_id'] ?? null;
                    $prevBlock      = $exIndex > 0 ? (strtolower($exercises[$exIndex - 1]['bloque'] ?? $exercises[$exIndex - 1]['block_type'] ?? 'normal')) : null;
                    $prevGroupId    = $exIndex > 0 ? ($exercises[$exIndex - 1]['grupo_id'] ?? $exercises[$exIndex - 1]['group_id'] ?? null) : null;
                    $isFirstInBlock = $isInBlock && (!$prevBlock || !in_array($prevBlock, ['superset','circuito']) || $prevGroupId !== $blockGroupId);

                    // RIR color
                    $rirClass = '';
                    if ($exRir !== null) {
                        $rirClass = $exRir >= 3 ? 'bg-emerald-500/10 text-emerald-400' : ($exRir >= 2 ? 'bg-amber-500/10 text-amber-400' : 'bg-red-500/10 text-red-400');
                    }

                    // Video / image support
                    $exVideoUrl   = $exercise['video_url'] ?? $exercise['video'] ?? null;
                    $exImageUrl   = $exercise['image_url'] ?? $exercise['imagen'] ?? $exercise['thumbnail_url'] ?? null;
                    $exThumb      = null;
                    if ($exImageUrl) {
                        $exThumb = $exImageUrl;
                    } elseif ($exVideoUrl && preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $exVideoUrl, $_ytm)) {
                        $exThumb = 'https://img.youtube.com/vi/' . $_ytm[1] . '/mqdefault.jpg';
                    }
                @endphp

                {{-- Block label (superset/circuit) --}}
                @if($isFirstInBlock)
                    <div class="flex items-center gap-3" data-animate="fadeInUp">
                        <span class="rounded-full border border-wc-accent/30 bg-wc-accent/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-wc-accent">
                            {{ $blockType === 'superset' ? '⚡ SUPERSET' : '🔄 CIRCUITO' }}
                        </span>
                        <div class="h-px flex-1 bg-wc-accent/15"></div>
                    </div>
                @endif

                {{-- Exercise card --}}
                <div
                    class="overflow-hidden rounded-2xl border bg-wc-bg-tertiary transition-all
                        {{ $isInBlock ? 'ml-3 border-l-[3px] border-l-wc-accent border-wc-border/70' : 'border-wc-border' }}"
                    data-animate="fadeInUp"
                    data-animate-delay="{{ min(($exIndex + 1) * 80, 500) }}"
                >
                    <div class="flex items-stretch">
                        {{-- Thumbnail column with number overlay --}}
                        <div class="relative w-20 shrink-0 overflow-hidden bg-wc-bg-secondary">
                            @if($exThumb)
                                <img src="{{ $exThumb }}" alt="{{ $exName }}"
                                    class="h-full w-full object-cover opacity-90"/>
                            @else
                                <div class="flex h-full w-full min-h-[80px] flex-col items-center justify-center bg-gradient-to-b from-wc-bg-secondary to-wc-bg">
                                    <svg class="h-7 w-7 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                </div>
                            @endif
                            {{-- Number overlay --}}
                            <div class="absolute bottom-2 left-2 flex h-6 w-6 items-center justify-center rounded-lg bg-wc-bg/80 backdrop-blur-sm border border-wc-border/30">
                                <span class="font-data text-xs font-black leading-none text-wc-accent">{{ $exIndex + 1 }}</span>
                            </div>
                            {{-- Video play icon if has video --}}
                            @if($exVideoUrl)
                                <div class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-wc-bg/80 backdrop-blur-sm">
                                    <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1 p-3">
                            {{-- Name + muscle group --}}
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <h3 class="font-display text-xl tracking-wide leading-tight text-wc-text uppercase">
                                    {{ $exName }}
                                </h3>
                                @if($exMuscle)
                                    <span class="shrink-0 rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                                        {{ $exMuscle }}
                                    </span>
                                @endif
                            </div>

                            {{-- Coach notes --}}
                            @if($exNotas)
                                <p class="mt-1 text-xs leading-relaxed text-wc-text-tertiary">{{ $exNotas }}</p>
                            @endif

                            {{-- Chips row --}}
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                {{-- Sets × Reps — most prominent --}}
                                @if($exSeries || $exReps)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-wc-accent/10 px-3 py-1.5">
                                        <span class="font-data text-sm font-black text-wc-accent">{{ $exSeries ?? '?' }}</span>
                                        <span class="text-xs text-wc-accent/60">&times;</span>
                                        <span class="font-data text-sm font-black text-wc-accent">{{ $exReps ?? '?' }}</span>
                                        <span class="text-[10px] text-wc-accent/60 ml-0.5">reps</span>
                                    </span>
                                @endif

                                {{-- Rest --}}
                                @if($exDescanso)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-wc-bg-secondary px-2.5 py-1.5 text-xs text-wc-text-secondary">
                                        <svg class="h-3 w-3 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        {{ $exDescanso }}
                                    </span>
                                @endif

                                {{-- RIR --}}
                                @if($exRir !== null)
                                    <span class="rounded-lg px-2.5 py-1.5 text-[11px] font-bold {{ $rirClass }}">
                                        RIR {{ $exRir }}
                                    </span>
                                @endif

                                {{-- Equipment --}}
                                @if($exEquip)
                                    <span class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] text-wc-text-tertiary">
                                        {{ $exEquip }}
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
                        $totalSets = $ex['series'] ?? $ex['sets'] ?? 3;
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
                    $totalSets = $exercise['series'] ?? $exercise['sets'] ?? 3;
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
                                        {{ $exercise['nombre'] ?? $exercise['name'] ?? $exercise['ejercicio'] ?? 'Ejercicio' }}
                                    </h3>
                                </div>

                                {{-- Badges row --}}
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    {{-- Equipment --}}
                                    @php $exEquipDisplay = $exercise['equipo'] ?? $exercise['equipment'] ?? null; @endphp
                                    @if(!empty($exEquipDisplay))
                                        <span class="inline-flex items-center rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                                            {{ $exEquipDisplay }}
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
                                    @php $exRestDisplay = $exercise['descanso'] ?? $exercise['rest'] ?? $exercise['rest_seconds'] ?? null; @endphp
                                    @if(!empty($exRestDisplay))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            {{ is_numeric($exRestDisplay) ? $exRestDisplay . 's' : $exRestDisplay }} descanso
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
                        @php $exNotesDisplay = $exercise['notas'] ?? $exercise['notes'] ?? null; @endphp
                        @if(!empty($exNotesDisplay))
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
                                    {{ $exNotesDisplay }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Set grid --}}
                    <div class="border-t border-wc-border">
                        {{-- Table header --}}
                        <div class="grid gap-1 px-3 py-2 bg-wc-bg-secondary/50"
                             style="grid-template-columns: 40px 72px 1fr 1fr 48px">
                            <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Set</span>
                            <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Anterior</span>
                            <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Peso (kg)</span>
                            <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Reps</span>
                            <span class="sr-only">Completar</span>
                        </div>

                        {{-- Set rows --}}
                        @for($setNum = 1; $setNum <= $totalSets; $setNum++)
                            @php
                                $currentSet   = $exSetData[$setNum] ?? [];
                                $isCompleted  = !empty($currentSet['completed']);
                                $isPr         = !empty($currentSet['is_pr']);
                                $setWeight    = $currentSet['weight'] ?? $currentSet['weight_kg'] ?? '';
                                $setReps      = $currentSet['reps'] ?? '';

                                // Parse reps: "8-10" → 8, "12" → 12, empty → exercise target first number
                                $repsRaw      = $setReps !== '' ? $setReps : ($exercise['repeticiones'] ?? $exercise['reps'] ?? 10);
                                preg_match('/\d+/', (string) $repsRaw, $_m);
                                $repsInitial  = isset($_m[0]) ? (int) $_m[0] : 10;
                            @endphp
                            <div
                                class="grid gap-1 items-center px-3 py-2 transition-colors
                                    {{ $isCompleted ? 'bg-emerald-500/5' : '' }}
                                    {{ $setNum < $totalSets ? 'border-b border-wc-border/50' : '' }}"
                                style="grid-template-columns: 40px 72px 1fr 1fr 48px"
                                x-data="{
                                    weight: {{ (float)($setWeight ?: 0) }},
                                    reps: {{ $repsInitial }},
                                    completed: {{ $isCompleted ? 'true' : 'false' }},
                                    isPr: {{ $isPr ? 'true' : 'false' }},
                                    justCompleted: false
                                }"
                            >
                                {{-- Set number --}}
                                <div class="flex flex-col items-center justify-center gap-0.5">
                                    <span class="font-data text-sm font-bold {{ $isCompleted ? 'text-emerald-400' : 'text-wc-text-tertiary' }}">
                                        {{ $setNum }}
                                    </span>
                                    @if($isPr)
                                        <span
                                            class="inline-flex items-center gap-0.5 rounded-md px-1 py-0.5 text-[8px] font-black leading-none text-black"
                                            style="background: linear-gradient(135deg, #facc15, #f59e0b); animation: prPulse 1s ease-out;"
                                        >
                                            ★ PR
                                        </span>
                                    @endif
                                </div>

                                {{-- ANTERIOR (target from last session) --}}
                                <div class="flex items-center justify-center">
                                    @php
                                        $prevWeight = $currentSet['target_weight'] ?? null;
                                        $prevReps   = $currentSet['target_reps']   ?? null;
                                    @endphp
                                    @if($prevWeight || $prevReps)
                                        <span class="text-center font-data text-[11px] font-medium text-wc-text/30 tabular-nums leading-tight">
                                            {{ $prevWeight ? number_format($prevWeight, 1).'kg' : '—' }}<br>
                                            <span class="text-[9px]">× {{ $prevReps ?? '?' }}</span>
                                        </span>
                                    @else
                                        <span class="font-data text-[11px] text-wc-text/20">—</span>
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
    {{-- REST TIMER BOTTOM SHEET                                      --}}
    {{-- ============================================================ --}}
    <div
        x-show="restActive"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-0 inset-x-0 z-50 rounded-t-3xl border-t border-wc-border bg-wc-bg-secondary shadow-2xl"
        x-cloak
    >
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 -z-10 bg-black/40 backdrop-blur-sm"
            @click="skipRest()"
        ></div>

        <div class="px-6 pt-4 pb-6 safe-area-pb">
            {{-- Handle --}}
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-wc-border"></div>

            {{-- Label --}}
            <p class="text-center text-[10px] font-bold uppercase tracking-widest text-wc-text-tertiary mb-5">Tiempo de Descanso</p>

            {{-- SVG Ring with countdown --}}
            <div class="flex justify-center mb-5">
                <div class="relative">
                    <svg width="148" height="148" viewBox="0 0 148 148" class="-rotate-90">
                        {{-- Track circle --}}
                        <circle cx="74" cy="74" r="58" fill="none" stroke-width="6"
                            class="text-wc-border" style="stroke: currentColor; opacity: 0.25"/>
                        {{-- Draining progress circle --}}
                        <circle
                            cx="74" cy="74" r="58"
                            fill="none"
                            stroke="#DC2626"
                            stroke-width="6"
                            stroke-linecap="round"
                            :stroke-dasharray="2 * Math.PI * 58"
                            :stroke-dashoffset="restTotal > 0 ? (2 * Math.PI * 58) * (restSeconds / restTotal) : 0"
                            style="transition: stroke-dashoffset 0.95s linear;"
                        />
                    </svg>
                    {{-- Countdown display --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="font-data text-4xl font-black tabular-nums leading-none text-wc-text" x-text="restDisplay"></span>
                        <span class="mt-1 text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">seg</span>
                    </div>
                </div>
            </div>

            {{-- Adjust +/- buttons --}}
            <div class="mb-4 flex items-center justify-center gap-4">
                <button
                    @click="adjustRest(-15)"
                    class="btn-press flex h-11 w-24 items-center justify-center rounded-xl border border-wc-border bg-wc-bg font-data text-sm font-bold text-wc-text-secondary hover:text-wc-text transition-colors"
                >−15s</button>
                <button
                    @click="adjustRest(15)"
                    class="btn-press flex h-11 w-24 items-center justify-center rounded-xl border border-wc-border bg-wc-bg font-data text-sm font-bold text-wc-text-secondary hover:text-wc-text transition-colors"
                >+15s</button>
            </div>

            {{-- Skip button --}}
            <button
                @click="skipRest()"
                class="btn-press w-full rounded-2xl bg-wc-accent py-4 text-center font-display text-xl tracking-widest text-white shadow-lg shadow-wc-accent/25 hover:bg-wc-accent-hover transition-colors"
            >
                SALTAR DESCANSO
            </button>
        </div>
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
                $totalSetsAll += $ex['series'] ?? $ex['sets'] ?? 3;
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

                // ── Rest Timer state ──────────────────────────────────────────
                restActive: false,
                restSeconds: 0,
                restTotal: 0,
                restInterval: null,

                get restDisplay() {
                    const m = Math.floor(this.restSeconds / 60);
                    const s = this.restSeconds % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },

                startRestTimer(seconds) {
                    this.clearRestTimer();
                    this.restSeconds = parseInt(seconds) || 60;
                    this.restTotal   = this.restSeconds;
                    this.restActive  = true;
                    this.restInterval = setInterval(() => {
                        if (this.restSeconds > 0) {
                            this.restSeconds--;
                        } else {
                            this.clearRestTimer();
                        }
                    }, 1000);
                },

                skipRest() {
                    this.clearRestTimer();
                },

                adjustRest(delta) {
                    const newVal = this.restSeconds + delta;
                    this.restSeconds = Math.max(5, newVal);
                    if (this.restSeconds > this.restTotal) {
                        this.restTotal = this.restSeconds;
                    }
                },

                clearRestTimer() {
                    if (this.restInterval) {
                        clearInterval(this.restInterval);
                        this.restInterval = null;
                    }
                    this.restActive  = false;
                    this.restSeconds = 0;
                    this.restTotal   = 0;
                },

                // ── Session elapsed timer ─────────────────────────────────────
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
                    if (!this._observers) this._observers = [];

                    const observeNew = () => {
                        const elements = document.querySelectorAll('[data-animate]:not(.animate-in)');
                        if (!elements.length) return;

                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    entry.target.classList.add('animate-in');
                                    observer.unobserve(entry.target);
                                }
                            });
                        }, { threshold: 0.05, rootMargin: '0px 0px 0px 0px' });

                        elements.forEach(el => observer.observe(el));
                        this._observers.push(observer);
                    };

                    this.$nextTick(observeNew);

                    // Remove any previous livewire:updated listener before adding a new one
                    if (this._liveListener) {
                        document.removeEventListener('livewire:updated', this._liveListener);
                    }
                    this._liveListener = () => this.$nextTick(observeNew);
                    document.addEventListener('livewire:updated', this._liveListener);
                },

                destroy() {
                    this.stopTimer();
                    this.clearRestTimer();

                    // Disconnect all IntersectionObservers
                    if (this._observers) {
                        this._observers.forEach(obs => obs.disconnect());
                        this._observers = [];
                    }

                    // Remove the livewire:updated listener
                    if (this._liveListener) {
                        document.removeEventListener('livewire:updated', this._liveListener);
                        this._liveListener = null;
                    }
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

        /* PR badge pulse animation */
        @keyframes prPulse {
            0%   { transform: scale(1); box-shadow: 0 0 0 0 rgba(250,204,21,0.5); }
            50%  { transform: scale(1.15); box-shadow: 0 0 0 4px rgba(250,204,21,0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(250,204,21,0); }
        }

        /* PR badge golden shimmer */
        @keyframes prShine {
            0% { background-position: -100% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</div>
