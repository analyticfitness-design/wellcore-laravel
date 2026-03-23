<div
    x-data="{
        waterConsumed: @entangle('waterConsumedMl'),
        waterGoal: {{ $waterGoalMl }},
        animateBars: false,
        init() { setTimeout(() => this.animateBars = true, 300) },
        get waterPercent() { return Math.min(100, Math.round((this.waterConsumed / this.waterGoal) * 100)) },
        get waterDropsFilled() { return Math.min(8, Math.round((this.waterConsumed / this.waterGoal) * 8)) }
    }"
>
    {{-- ─── HEADER ──────────────────────────────────────────────────────── --}}
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">NUTRICI&Oacute;N</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu plan nutricional personalizado por tu coach</p>
    </div>

    @if($plan)

        {{-- ─── MACRO STAT CARDS ─────────────────────────────────────────── --}}
        @if($hasMacros)
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">

            {{-- Calorías --}}
            <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
                <div class="absolute inset-x-0 top-0 h-0.5" style="background:#10B981;"></div>
                <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Calorías</p>
                <p class="font-data mt-1 text-3xl font-bold tabular-nums text-wc-text">{{ number_format($totalCalories) }}</p>
                <p class="mt-0.5 text-xs font-medium" style="color:#10B981;">kcal / día</p>
            </div>

            {{-- Proteína --}}
            <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
                <div class="absolute inset-x-0 top-0 h-0.5 bg-wc-accent"></div>
                <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Proteína</p>
                <p class="font-data mt-1 text-3xl font-bold tabular-nums text-wc-text">{{ $proteinGrams }}<span class="text-lg font-normal">g</span></p>
                <p class="mt-0.5 text-xs font-medium text-wc-accent">{{ $macroPercentages['protein'] }}% del total</p>
            </div>

            {{-- Carbos --}}
            <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
                <div class="absolute inset-x-0 top-0 h-0.5" style="background:#3B82F6;"></div>
                <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Carbos</p>
                <p class="font-data mt-1 text-3xl font-bold tabular-nums text-wc-text">{{ $carbGrams }}<span class="text-lg font-normal">g</span></p>
                <p class="mt-0.5 text-xs font-medium" style="color:#3B82F6;">{{ $macroPercentages['carbs'] }}% del total</p>
            </div>

            {{-- Grasas --}}
            <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
                <div class="absolute inset-x-0 top-0 h-0.5" style="background:#F59E0B;"></div>
                <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Grasas</p>
                <p class="font-data mt-1 text-3xl font-bold tabular-nums text-wc-text">{{ $fatGrams }}<span class="text-lg font-normal">g</span></p>
                <p class="mt-0.5 text-xs font-medium" style="color:#F59E0B;">{{ $macroPercentages['fat'] }}% del total</p>
            </div>
        </div>

        {{-- Macro visual bars (thin, compact) --}}
        <div class="mb-6 flex h-2 w-full overflow-hidden rounded-full"
             x-data="{}" x-init="setTimeout(() => $el.classList.add('ready'), 200)">
            <div class="h-full bg-wc-accent transition-all duration-700 delay-100"
                 :style="{ width: animateBars ? '{{ $macroPercentages['protein'] }}%' : '0%' }"></div>
            <div class="h-full transition-all duration-700 delay-200"
                 :style="{ background: '#3B82F6', width: animateBars ? '{{ $macroPercentages['carbs'] }}%' : '0%' }"></div>
            <div class="h-full transition-all duration-700 delay-300"
                 :style="{ background: '#F59E0B', width: animateBars ? '{{ $macroPercentages['fat'] }}%' : '0%' }"></div>
        </div>
        @endif

        {{-- ─── OBJETIVO ──────────────────────────────────────────────────── --}}
        @if($planObjetivo)
        <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Objetivo del plan</p>
                    <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">{{ $planObjetivo }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- ─── COMIDAS DEL DÍA ───────────────────────────────────────────── --}}
        @if(count($mealLog) > 0)
        <div class="mb-6">
            <h3 class="mb-3 font-display text-xl tracking-wide text-wc-text">COMIDAS DEL D&Iacute;A</h3>
            <div class="space-y-2">
                @foreach($mealLog as $i => $comida)
                @php
                    $nombre = strtolower($comida['nombre'] ?? '');
                    $isDesayuno   = str_contains($nombre, 'desayuno');
                    $isAlmuerzo   = str_contains($nombre, 'almuerzo') || str_contains($nombre, 'post-entreno');
                    $isCena       = str_contains($nombre, 'cena');
                    $isPreEntreno = str_contains($nombre, 'pre-entreno') || str_contains($nombre, 'pre entreno');
                    $iconBg = match(true) {
                        $isDesayuno   => 'bg-amber-500/10 text-amber-400',
                        $isPreEntreno => 'bg-green-500/10 text-green-400',
                        $isAlmuerzo   => 'bg-blue-500/10 text-blue-400',
                        $isCena       => 'bg-indigo-500/10 text-indigo-400',
                        default       => 'bg-wc-accent/10 text-wc-accent',
                    };
                    // extract time from name if present
                    preg_match('/(\d{1,2}:\d{2}(?:am|pm)?)/i', $comida['nombre'] ?? '', $timeMatch);
                    $timeStr = $timeMatch[1] ?? null;
                    $mealName = $timeStr ? trim(str_replace(['—', $timeStr], '', $comida['nombre'])) : ($comida['nombre'] ?? '');
                @endphp
                <div
                    class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary"
                    x-data="{ open: false }"
                >
                    {{-- Header --}}
                    <button
                        @click="open = !open"
                        class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-wc-bg-tertiary"
                    >
                        {{-- Number badge --}}
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $iconBg }}">
                            <span class="font-data text-sm font-bold">{{ $i + 1 }}</span>
                        </div>

                        {{-- Name + time --}}
                        <div class="min-w-0 flex-1">
                            <p class="font-display text-sm tracking-wide text-wc-text truncate">
                                {{ strtoupper($mealName) }}
                            </p>
                            @if($timeStr)
                                <p class="text-[11px] text-wc-text-tertiary">{{ $timeStr }}</p>
                            @endif
                        </div>

                        {{-- Macro chips --}}
                        <div class="hidden items-center gap-1.5 sm:flex">
                            @if(($comida['macros']['proteina'] ?? 0) > 0)
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                      style="background:rgba(220,38,38,0.12); color:#F87171;">
                                    P {{ $comida['macros']['proteina'] }}g
                                </span>
                            @endif
                            @if(($comida['macros']['carbohidratos'] ?? 0) > 0)
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                      style="background:rgba(59,130,246,0.12); color:#60A5FA;">
                                    C {{ $comida['macros']['carbohidratos'] }}g
                                </span>
                            @endif
                            @if(($comida['macros']['grasas'] ?? 0) > 0)
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                      style="background:rgba(245,158,11,0.12); color:#FBBF24;">
                                    G {{ $comida['macros']['grasas'] }}g
                                </span>
                            @endif
                        </div>

                        {{-- Calories + chevron --}}
                        <div class="ml-2 flex items-center gap-3 shrink-0">
                            @if(($comida['calorias'] ?? 0) > 0)
                                <span class="font-data text-sm font-bold tabular-nums text-wc-text">
                                    {{ $comida['calorias'] }}<span class="text-xs font-normal text-wc-text-tertiary"> kcal</span>
                                </span>
                            @endif
                            <svg class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200"
                                 :class="{ 'rotate-180': open }"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </button>

                    {{-- Body --}}
                    <div x-show="open" x-collapse class="border-t border-wc-border">
                        <div class="p-4 space-y-3">

                            {{-- Mobile macro chips --}}
                            <div class="flex flex-wrap gap-1.5 sm:hidden">
                                @if(($comida['macros']['proteina'] ?? 0) > 0)
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                          style="background:rgba(220,38,38,0.12); color:#F87171;">
                                        P {{ $comida['macros']['proteina'] }}g
                                    </span>
                                @endif
                                @if(($comida['macros']['carbohidratos'] ?? 0) > 0)
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                          style="background:rgba(59,130,246,0.12); color:#60A5FA;">
                                        C {{ $comida['macros']['carbohidratos'] }}g
                                    </span>
                                @endif
                                @if(($comida['macros']['grasas'] ?? 0) > 0)
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                          style="background:rgba(245,158,11,0.12); color:#FBBF24;">
                                        G {{ $comida['macros']['grasas'] }}g
                                    </span>
                                @endif
                            </div>

                            {{-- Alimentos --}}
                            @if(!empty($comida['alimentos']))
                            <ul class="space-y-1.5">
                                @foreach($comida['alimentos'] as $alimento)
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                                    @php
                                        if (is_array($alimento)) {
                                            $aName = $alimento['nombre'] ?? $alimento['alimento'] ?? $alimento['name'] ?? '';
                                            $aQty  = $alimento['cantidad'] ?? $alimento['porcion'] ?? $alimento['quantity'] ?? $alimento['amount'] ?? '';
                                            $aText = $aName && $aQty ? "$aName — $aQty" : ($aName ?: $aQty);
                                        } else {
                                            $aText = (string) $alimento;
                                        }
                                    @endphp
                                    <span class="text-sm leading-relaxed text-wc-text-secondary">{{ $aText }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @endif

                            {{-- Nota --}}
                            @if(!empty($comida['notas']))
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3.5 py-3">
                                <p class="text-xs leading-relaxed text-wc-text-tertiary">{{ $comida['notas'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ─── HIDRATACIÓN ────────────────────────────────────────────────── --}}
        <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-display text-xl tracking-wide text-wc-text">HIDRATACI&Oacute;N</h3>
                <div class="flex items-baseline gap-1">
                    <span class="font-data text-2xl font-bold tabular-nums text-wc-text"
                          x-text="(waterConsumed / 1000).toFixed(1)">{{ number_format($waterConsumedMl / 1000, 1) }}</span>
                    <span class="text-xs text-wc-text-tertiary">/ {{ number_format($waterGoalMl / 1000, 1) }}L</span>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mb-4 h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-tertiary">
                <div class="h-full rounded-full transition-all duration-500"
                     style="background: linear-gradient(90deg, #3B82F6, #06B6D4);"
                     :style="{ width: waterPercent + '%' }"></div>
            </div>

            {{-- Drops --}}
            <div class="mb-4 flex items-center justify-center gap-2">
                <template x-for="i in 8" :key="i">
                    <svg class="h-7 w-5 transition-all duration-300"
                         :class="i <= waterDropsFilled ? 'opacity-100' : 'opacity-20'"
                         viewBox="0 0 20 28" fill="none">
                        <path d="M10 2C10 2 3 11 3 17C3 20.866 6.134 24 10 24C13.866 24 17 20.866 17 17C17 11 10 2 10 2Z"
                              :fill="i <= waterDropsFilled ? '#3B82F6' : '#374151'" />
                    </svg>
                </template>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex gap-2">
                    <button wire:click="toggleWater(250)"
                        class="flex-1 rounded-lg border border-blue-500/30 bg-blue-500/10 py-2.5 text-sm font-semibold text-blue-400 transition active:scale-95 hover:bg-blue-500/20">
                        + 250 mL
                    </button>
                    <button wire:click="toggleWater(500)"
                        class="flex-1 rounded-lg border border-blue-500/30 bg-blue-500/10 py-2.5 text-sm font-semibold text-blue-400 transition active:scale-95 hover:bg-blue-500/20">
                        + 500 mL
                    </button>
                </div>
                @if($hydrationNote)
                <p class="text-[11px] text-wc-text-tertiary text-center">{{ $hydrationNote }}</p>
                @endif
            </div>
        </div>

        {{-- ─── DÍA DE DESCANSO ───────────────────────────────────────────── --}}
        @if($restDayInfo)
        <div class="mb-6" x-data="{ open: false }">
            <button @click="open = !open"
                class="flex w-full items-center justify-between rounded-xl border border-wc-border bg-wc-bg-secondary px-5 py-4 text-left hover:bg-wc-bg-tertiary transition">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background:rgba(139,92,246,0.12);">
                        <svg class="h-4 w-4" style="color:#A78BFA;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-display text-sm tracking-wide text-wc-text">DÍA DE DESCANSO</p>
                        @if($restDayInfo['calorias_objetivo'] > 0)
                        <p class="text-xs text-wc-text-tertiary">~{{ number_format($restDayInfo['calorias_objetivo']) }} kcal</p>
                        @endif
                    </div>
                </div>
                <svg class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200" :class="{ 'rotate-180': open }"
                     fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                <div class="p-4 space-y-2">
                    @if($restDayInfo['descripcion'])
                    <p class="text-sm text-wc-text-secondary">{{ $restDayInfo['descripcion'] }}</p>
                    @endif
                    @if(!empty($restDayInfo['ajustes']))
                    <ul class="space-y-1.5 mt-2">
                        @foreach($restDayInfo['ajustes'] as $ajuste)
                        <li class="flex items-start gap-2 text-sm text-wc-text-tertiary">
                            <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-purple-400"></span>
                            {{ $ajuste }}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ─── NOTAS DEL COACH ────────────────────────────────────────────── --}}
        @if($coachNotes)
        <div class="mb-6 overflow-hidden rounded-xl border border-wc-accent/30 bg-wc-bg-secondary">
            <div class="flex items-center gap-3 border-b border-wc-accent/20 px-5 py-3.5">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                    </svg>
                </div>
                <p class="font-display text-sm tracking-wide text-wc-accent">NOTAS DE TU COACH</p>
            </div>
            <div class="p-5">
                <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $coachNotes }}</p>
            </div>
        </div>
        @endif

        {{-- ─── PESO ───────────────────────────────────────────────────────── --}}
        @if($currentWeightKg || $weightGoalKg)
        <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
            <h3 class="mb-4 font-display text-xl tracking-wide text-wc-text">PESO</h3>
            <div class="grid grid-cols-2 gap-3">
                @if($currentWeightKg)
                <div class="rounded-lg bg-wc-bg-tertiary p-4 text-center">
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Actual</p>
                    <p class="font-data mt-1 text-2xl font-bold tabular-nums text-wc-text">{{ number_format($currentWeightKg, 1) }}</p>
                    <p class="text-xs text-wc-text-tertiary">kg</p>
                </div>
                @endif
                @if($weightGoalKg)
                <div class="rounded-lg bg-wc-bg-tertiary p-4 text-center">
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Objetivo</p>
                    <p class="font-data mt-1 text-2xl font-bold tabular-nums text-wc-text">{{ number_format($weightGoalKg, 1) }}</p>
                    <p class="text-xs text-wc-text-tertiary">kg</p>
                </div>
                @endif
            </div>
            @if($currentWeightKg && $weightGoalKg)
            @php $delta = round($weightGoalKg - $currentWeightKg, 1); @endphp
            <div class="mt-3 flex items-center justify-center rounded-lg border border-wc-border px-4 py-3">
                @if($delta == 0)
                    <span class="text-sm font-semibold text-emerald-400">✓ En tu peso objetivo</span>
                @else
                    <span class="font-data text-lg font-bold tabular-nums {{ $delta > 0 ? 'text-blue-400' : 'text-emerald-400' }}">
                        {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 1) }} kg
                    </span>
                    <span class="ml-2 text-xs text-wc-text-tertiary">{{ $delta > 0 ? 'por ganar' : 'por perder' }}</span>
                @endif
            </div>
            @endif
        </div>
        @endif

    @else
        {{-- ─── EMPTY STATE ──────────────────────────────────────────────── --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/10">
                <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265z" />
                </svg>
            </div>
            <h3 class="font-display text-xl text-wc-text">TU PLAN EST&Aacute; EN CAMINO</h3>
            <p class="mt-2 max-w-xs mx-auto text-sm text-wc-text-secondary">
                Tu coach está diseñando tu plan de nutrición. Te notificaremos cuando esté listo.
            </p>
        </div>
    @endif

    {{-- ===== ONBOARDING TUTORIAL: NUTRICIÓN ===== --}}
    @if($showTutorial)
    <div
        x-data="{ step: 1, total: 3 }"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
        @keydown.escape.window="$wire.dismissTutorial()"
    >
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-lg tracking-widest text-wc-text">TU NUTRICIÓN</h3>
                <button @click="$wire.dismissTutorial()" class="text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="step === 1">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Tu plan de macros</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Aquí encontrarás tus objetivos diarios de proteína, carbohidratos y grasas. Tu coach los calculó específicamente para tus metas.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 2">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Hidratación</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Registra cada vaso de agua que tomas. La hidratación adecuada mejora el rendimiento hasta un 20% y acelera la recuperación muscular.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 3">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Sigue el plan con consistencia</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">No necesitas ser perfecto — apunta a cumplir tus macros el 80% del tiempo. La consistencia a largo plazo supera la perfección a corto plazo.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-center gap-1.5">
                <template x-for="i in total" :key="i">
                    <div class="h-1.5 rounded-full transition-all" :class="i === step ? 'bg-wc-accent w-4' : 'bg-wc-bg-tertiary w-1.5'"></div>
                </template>
            </div>

            <div class="mt-5 flex gap-3">
                <button x-show="step > 1" @click="step--" class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors" type="button">Atrás</button>
                <button x-show="step < total" @click="step++" class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors" type="button">Siguiente</button>
                <button x-show="step === total" @click="$wire.dismissTutorial()" class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors" type="button">¡Entendido!</button>
            </div>
        </div>
    </div>
    @endif
    {{-- ===== /ONBOARDING TUTORIAL: NUTRICIÓN ===== --}}
</div>
