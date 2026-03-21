<div
    x-data="{
        waterConsumed: @entangle('waterConsumedMl'),
        waterGoal: {{ $waterGoalMl }},
        mealOpen: {},
        animateRing: false,
        animateBars: false,
        init() {
            setTimeout(() => { this.animateRing = true }, 200);
            setTimeout(() => { this.animateBars = true }, 500);
        },
        get waterDropsFilled() {
            return Math.min(8, Math.round((this.waterConsumed / this.waterGoal) * 8));
        },
        get waterPercent() {
            return Math.min(100, Math.round((this.waterConsumed / this.waterGoal) * 100));
        }
    }"
>
    {{-- Header --}}
    <div class="mb-8" data-animate="fadeInUp">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">NUTRICI&Oacute;N</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu plan nutricional personalizado por tu coach</p>
    </div>

    @if($plan)
        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- CALORIE RING — SVG circular progress               --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        @if($hasMacros)
            <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6"
                 data-animate="fadeInUp" data-animate-delay="100">
                <div class="flex flex-col items-center">
                    {{-- SVG Ring --}}
                    <div class="relative" style="width: 200px; height: 200px;">
                        <svg viewBox="0 0 200 200" class="w-full h-full" style="transform: rotate(-90deg);">
                            {{-- Background circle --}}
                            <circle
                                cx="100" cy="100" r="85"
                                fill="none"
                                stroke="currentColor"
                                class="text-wc-border"
                                stroke-width="12"
                            />
                            {{-- Progress circle with green gradient --}}
                            <defs>
                                <linearGradient id="calorieGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#22C55E" />
                                    <stop offset="100%" stop-color="#10B981" />
                                </linearGradient>
                            </defs>
                            <circle
                                cx="100" cy="100" r="85"
                                fill="none"
                                stroke="url(#calorieGradient)"
                                stroke-width="12"
                                stroke-linecap="round"
                                :stroke-dasharray="2 * Math.PI * 85"
                                :stroke-dashoffset="animateRing ? 0 : 2 * Math.PI * 85"
                                style="transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);"
                            />
                        </svg>
                        {{-- Center text --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center" style="transform: none;">
                            <span class="font-data text-4xl font-bold text-wc-text tabular-nums">{{ number_format($totalCalories) }}</span>
                            <span class="text-xs font-medium uppercase tracking-widest text-wc-text-tertiary">kcal</span>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-wc-text-secondary">Objetivo calórico diario</p>
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- MACRO BARS — 3 horizontal progress bars             --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        @if($hasMacros)
            <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5"
                 data-animate="fadeInUp" data-animate-delay="200">
                <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">MACRONUTRIENTES</h3>
                <div class="space-y-4">
                    {{-- Protein --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: #DC2626;"></span>
                                <span class="text-sm font-medium text-wc-text">Proteína</span>
                                <span class="text-xs text-wc-text-tertiary">{{ $macroPercentages['protein'] }}%</span>
                            </div>
                            <span class="font-data text-sm font-semibold text-wc-text tabular-nums">{{ $proteinGrams }}g</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div
                                class="h-full rounded-full"
                                style="background: #DC2626; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);"
                                :style="{ width: animateBars ? '{{ $macroPercentages['protein'] }}%' : '0%' }"
                            ></div>
                        </div>
                    </div>

                    {{-- Carbs --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: #3B82F6;"></span>
                                <span class="text-sm font-medium text-wc-text">Carbohidratos</span>
                                <span class="text-xs text-wc-text-tertiary">{{ $macroPercentages['carbs'] }}%</span>
                            </div>
                            <span class="font-data text-sm font-semibold text-wc-text tabular-nums">{{ $carbGrams }}g</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div
                                class="h-full rounded-full"
                                style="background: #3B82F6; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1) 0.1s;"
                                :style="{ width: animateBars ? '{{ $macroPercentages['carbs'] }}%' : '0%' }"
                            ></div>
                        </div>
                    </div>

                    {{-- Fat --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: #F59E0B;"></span>
                                <span class="text-sm font-medium text-wc-text">Grasas</span>
                                <span class="text-xs text-wc-text-tertiary">{{ $macroPercentages['fat'] }}%</span>
                            </div>
                            <span class="font-data text-sm font-semibold text-wc-text tabular-nums">{{ $fatGrams }}g</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div
                                class="h-full rounded-full"
                                style="background: #F59E0B; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1) 0.2s;"
                                :style="{ width: animateBars ? '{{ $macroPercentages['fat'] }}%' : '0%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- WATER TRACKER                                       --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5"
             data-animate="fadeInUp" data-animate-delay="300">
            <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">HIDRATACI&Oacute;N</h3>

            {{-- Big consumed / goal --}}
            <div class="text-center mb-4">
                <span class="font-data text-3xl font-bold text-wc-text tabular-nums" x-text="waterConsumed.toLocaleString()">{{ $waterConsumedMl }}</span>
                <span class="text-sm text-wc-text-tertiary"> / {{ number_format($waterGoalMl) }} mL</span>
            </div>

            {{-- Water drops --}}
            <div class="flex items-center justify-center gap-2 mb-4">
                <template x-for="i in 8" :key="i">
                    <div class="flex flex-col items-center">
                        <svg class="w-7 h-9 transition-all duration-300"
                             :class="i <= waterDropsFilled ? 'scale-110' : 'opacity-30'"
                             viewBox="0 0 24 32" fill="none">
                            <path
                                d="M12 2C12 2 4 13 4 20C4 24.4183 7.58172 28 12 28C16.4183 28 20 24.4183 20 20C20 13 12 2 12 2Z"
                                :fill="i <= waterDropsFilled ? '#3B82F6' : 'currentColor'"
                                :class="i <= waterDropsFilled ? '' : 'text-wc-border'"
                                stroke="none"
                            />
                            <template x-if="i <= waterDropsFilled">
                                <path
                                    d="M9 20C9 17 12 12 12 12"
                                    stroke="rgba(255,255,255,0.4)"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    fill="none"
                                />
                            </template>
                        </svg>
                    </div>
                </template>
            </div>

            {{-- Progress bar --}}
            <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary mb-4">
                <div
                    class="h-full rounded-full transition-all duration-500 ease-out"
                    style="background: linear-gradient(90deg, #3B82F6, #06B6D4);"
                    :style="{ width: waterPercent + '%' }"
                ></div>
            </div>

            {{-- Add water button --}}
            <div class="flex justify-center">
                <button
                    wire:click="toggleWater(250)"
                    class="inline-flex items-center gap-2 rounded-lg border border-blue-500/30 bg-blue-500/10 px-5 py-2.5 text-sm font-semibold text-blue-400 transition hover:bg-blue-500/20 active:scale-95"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    250 mL
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- WEIGHT GOAL WIDGET                                  --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        @if($currentWeightKg || $weightGoalKg)
            <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5"
                 data-animate="fadeInUp" data-animate-delay="400">
                <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">PESO</h3>
                <div class="grid grid-cols-2 gap-4">
                    {{-- Current weight --}}
                    @if($currentWeightKg)
                        <div class="rounded-lg bg-wc-bg-secondary p-4 text-center">
                            <p class="text-xs uppercase tracking-wider text-wc-text-tertiary mb-1">Actual</p>
                            <p class="font-data text-2xl font-bold text-wc-text tabular-nums">{{ number_format($currentWeightKg, 1) }}</p>
                            <p class="text-xs text-wc-text-tertiary">kg</p>
                        </div>
                    @endif

                    {{-- Goal weight --}}
                    @if($weightGoalKg)
                        <div class="rounded-lg bg-wc-bg-secondary p-4 text-center">
                            <p class="text-xs uppercase tracking-wider text-wc-text-tertiary mb-1">Objetivo</p>
                            <p class="font-data text-2xl font-bold text-wc-text tabular-nums">{{ number_format($weightGoalKg, 1) }}</p>
                            <p class="text-xs text-wc-text-tertiary">kg</p>
                        </div>
                    @endif
                </div>

                {{-- Delta --}}
                @if($currentWeightKg && $weightGoalKg)
                    @php
                        $delta = round($weightGoalKg - $currentWeightKg, 1);
                        $isGain = $delta > 0;
                        $isLoss = $delta < 0;
                        $atGoal = $delta == 0;
                    @endphp
                    <div class="mt-4 flex items-center justify-center gap-3 rounded-lg border border-wc-border px-4 py-3">
                        @if($atGoal)
                            <div class="flex items-center gap-2 text-emerald-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-semibold">Estás en tu peso objetivo</span>
                            </div>
                        @else
                            <span class="font-data text-lg font-bold tabular-nums {{ $isGain ? 'text-blue-400' : 'text-emerald-400' }}">
                                {{ $isGain ? '+' : '' }}{{ number_format($delta, 1) }} kg
                            </span>
                            <span class="text-xs text-wc-text-tertiary">
                                {{ $isGain ? 'por ganar' : 'por perder' }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- MEAL PLAN CARDS                                     --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        @if(count($mealLog) > 0)
            <div class="mb-6" data-animate="fadeInUp" data-animate-delay="500">
                <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">COMIDAS DEL D&Iacute;A</h3>
                <div class="space-y-3">
                    @foreach($mealLog as $i => $comida)
                        <div
                            class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden"
                            x-data="{ open: false }"
                        >
                            {{-- Meal header (always visible) --}}
                            <button
                                @click="open = !open"
                                class="flex w-full items-center justify-between p-4 text-left transition hover:bg-wc-bg-secondary/50"
                            >
                                <div class="flex items-center gap-3">
                                    {{-- Meal icon --}}
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ match(true) {
                                        str_contains(strtolower($comida['nombre'] ?? ''), 'desayuno') => 'bg-amber-500/15 text-amber-400',
                                        str_contains(strtolower($comida['nombre'] ?? ''), 'almuerzo') => 'bg-emerald-500/15 text-emerald-400',
                                        str_contains(strtolower($comida['nombre'] ?? ''), 'cena') => 'bg-indigo-500/15 text-indigo-400',
                                        str_contains(strtolower($comida['nombre'] ?? ''), 'snack') || str_contains(strtolower($comida['nombre'] ?? ''), 'merienda') => 'bg-pink-500/15 text-pink-400',
                                        default => 'bg-wc-accent/15 text-wc-accent',
                                    } }}">
                                        @switch(true)
                                            @case(str_contains(strtolower($comida['nombre'] ?? ''), 'desayuno'))
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                                </svg>
                                                @break
                                            @case(str_contains(strtolower($comida['nombre'] ?? ''), 'almuerzo'))
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z" />
                                                </svg>
                                                @break
                                            @case(str_contains(strtolower($comida['nombre'] ?? ''), 'cena'))
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                                </svg>
                                                @break
                                            @default
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265z" />
                                                </svg>
                                        @endswitch
                                    </div>
                                    <div>
                                        <p class="font-display text-base tracking-wide text-wc-text">
                                            {{ strtoupper($comida['nombre'] ?? 'COMIDA ' . ($i + 1)) }}
                                        </p>
                                        @if(isset($comida['hora']))
                                            <p class="text-xs text-wc-text-tertiary">{{ $comida['hora'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if(isset($comida['calorias']))
                                        <span class="font-data text-sm font-semibold text-wc-text-secondary tabular-nums">{{ $comida['calorias'] }} kcal</span>
                                    @endif
                                    <svg
                                        class="h-5 w-5 text-wc-text-tertiary transition-transform duration-200"
                                        :class="{ 'rotate-180': open }"
                                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                            </button>

                            {{-- Meal details (collapsible) --}}
                            <div
                                x-show="open"
                                x-collapse
                                class="border-t border-wc-border"
                            >
                                <div class="p-4 space-y-3">
                                    {{-- Foods list --}}
                                    @if(isset($comida['alimentos']))
                                        <ul class="space-y-2">
                                            @foreach($comida['alimentos'] as $alimento)
                                                <li class="flex items-start gap-2.5 text-sm text-wc-text-secondary">
                                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-wc-accent"></span>
                                                    <span>{{ is_array($alimento) ? ($alimento['nombre'] ?? '') . (isset($alimento['cantidad']) ? ' — ' . $alimento['cantidad'] : '') : $alimento }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    {{-- Meal macros --}}
                                    @if(isset($comida['macros']))
                                        <div class="flex flex-wrap gap-3 pt-2">
                                            @if(isset($comida['macros']['proteina']))
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium" style="background: rgba(220,38,38,0.1); color: #F87171;">
                                                    P: {{ $comida['macros']['proteina'] }}g
                                                </span>
                                            @endif
                                            @if(isset($comida['macros']['carbohidratos']))
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium" style="background: rgba(59,130,246,0.1); color: #60A5FA;">
                                                    C: {{ $comida['macros']['carbohidratos'] }}g
                                                </span>
                                            @endif
                                            @if(isset($comida['macros']['grasas']))
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium" style="background: rgba(245,158,11,0.1); color: #FBBF24;">
                                                    G: {{ $comida['macros']['grasas'] }}g
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Notes --}}
                                    @if(isset($comida['notas']))
                                        <p class="text-xs italic text-wc-text-tertiary pt-1">{{ $comida['notas'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($plan && !isset($plan['comidas']))
            {{-- Raw plan content fallback --}}
            <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6"
                 data-animate="fadeInUp" data-animate-delay="500">
                <h3 class="mb-3 font-display text-lg tracking-wide text-wc-text">DETALLES DEL PLAN</h3>
                <div class="prose prose-sm max-w-none text-wc-text-secondary">
                    {!! is_string($plan) ? nl2br(e($plan)) : '<pre class="text-xs text-wc-text-secondary whitespace-pre-wrap">' . json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>' !!}
                </div>
            </div>
        @endif

    @else
        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- EMPTY STATE                                         --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center" data-animate="fadeInUp">
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
</div>
