<div class="space-y-6">

    {{-- Header --}}
    <div data-animate="fadeInUp">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">SUPLEMENTACIÓN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu protocolo de suplementación diseñado por tu coach</p>
    </div>

    @if($supplementPlan)

        {{-- Date Selector --}}
        <div class="flex items-center justify-between rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary px-4 py-3" data-animate="fadeInUp" data-animate-delay="100">
            <button wire:click="goToDate('prev')" class="btn-press flex h-8 w-8 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            </button>

            <div class="text-center">
                <p class="font-data text-sm font-semibold text-wc-text">
                    {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                </p>
                @if(!$isToday)
                    <button wire:click="goToToday" class="text-[10px] text-wc-accent hover:underline">Ir a hoy</button>
                @else
                    <p class="text-[10px] text-wc-accent font-medium">Hoy</p>
                @endif
            </div>

            <button
                wire:click="goToDate('next')"
                @disabled($isToday)
                class="btn-press flex h-8 w-8 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-secondary transition-colors
                    {{ $isToday ? 'opacity-30 cursor-not-allowed' : 'hover:text-wc-text' }}"
                @if($isToday) title="Ya estás en el día de hoy" @endif
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </button>
        </div>

        {{-- Progress Overview --}}
        <div class="grid grid-cols-2 gap-3" data-animate="fadeInUp" data-animate-delay="200">
            {{-- Daily Progress --}}
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Hoy</p>
                <div class="mt-2 flex items-end gap-2">
                    <span class="font-data text-3xl font-bold {{ $completedToday === $totalToday && $totalToday > 0 ? 'text-emerald-500' : 'text-wc-text' }}">
                        {{ $completedToday }}/{{ $totalToday }}
                    </span>
                </div>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full transition-all duration-500 {{ $completedToday === $totalToday && $totalToday > 0 ? 'bg-emerald-500' : 'bg-wc-accent' }}"
                        style="width: {{ $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0 }}%"></div>
                </div>
            </div>

            {{-- Weekly Adherence --}}
            <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Semana</p>
                <div class="mt-2 flex items-end gap-1">
                    <span class="font-data text-3xl font-bold {{ $weeklyAdherence >= 80 ? 'text-emerald-500' : ($weeklyAdherence >= 50 ? 'text-amber-400' : 'text-wc-accent') }}">
                        {{ $weeklyAdherence }}%
                    </span>
                    <span class="mb-1 text-xs text-wc-text-tertiary">adherencia</span>
                </div>
                {{-- Mini sparkline --}}
                <div class="mt-2 flex items-end gap-0.5 h-4">
                    @foreach($dailyAdherence as $day)
                        <div class="flex-1 rounded-t-sm transition-all {{ $day['isSelected'] ? 'bg-wc-accent' : ($day['pct'] >= 100 ? 'bg-emerald-500' : ($day['pct'] > 0 ? 'bg-wc-bg-secondary' : 'bg-wc-bg-secondary/50')) }}"
                            style="height: {{ max(15, $day['pct']) }}%"
                            title="{{ $day['day'] }}: {{ $day['pct'] }}%"></div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Supplement Cards by Timing --}}
        @php
            $timingOrder = ['manana' => 'Mañana', 'pre' => 'Pre-entreno', 'tarde' => 'Tarde', 'post' => 'Post-entreno', 'noche' => 'Noche'];
            $timingIcons = [
                'manana' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>',
                'pre' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>',
                'tarde' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>',
                'post' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>',
                'noche' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>',
            ];

            // Group supplements by their first timing
            $grouped = [];
            foreach ($supplements as $supp) {
                foreach ($supp['timings'] as $ts) {
                    $grouped[$ts['timing']][] = array_merge($supp, ['currentTiming' => $ts]);
                }
            }
        @endphp

        @php $isFutureDate = $selectedDate > today()->toDateString(); @endphp

        @foreach($timingOrder as $timingKey => $timingLabel)
            @if(isset($grouped[$timingKey]))
                <div data-animate="fadeInUp" data-animate-delay="{{ $loop->index * 100 + 300 }}">
                    {{-- Timing Header --}}
                    <div class="mb-2 flex items-center gap-2">
                        <span class="text-wc-text-tertiary">{!! $timingIcons[$timingKey] ?? '' !!}</span>
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">{{ $timingLabel }}</h2>
                    </div>

                    <div class="space-y-2">
                        @foreach($grouped[$timingKey] as $supp)
                            <button
                                @if(!$isFutureDate) wire:click="toggleSupplement('{{ $supp['name'] }}', '{{ $timingKey }}')" @endif
                                @disabled($isFutureDate)
                                class="w-full flex items-center gap-3 rounded-[--radius-card] border p-4 transition-all btn-press
                                    {{ $isFutureDate
                                        ? 'border-wc-border bg-wc-bg-tertiary opacity-40 cursor-not-allowed'
                                        : ($supp['currentTiming']['taken']
                                            ? 'border-emerald-500/30 bg-emerald-500/5'
                                            : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-text-tertiary') }}"
                                @if($isFutureDate) title="No puedes registrar suplementos en fechas futuras" aria-disabled="true" @endif
                            >
                                {{-- Pill icon --}}
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-all duration-300
                                    {{ $supp['currentTiming']['taken'] ? 'bg-emerald-500 text-white' : 'bg-wc-bg-secondary text-wc-text-tertiary' }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6 5.25 5.25M4.5 19.5l6.75-6.75m-3.75 3.75 9-9a3.182 3.182 0 0 0 0-4.5 3.182 3.182 0 0 0-4.5 0l-9 9a3.182 3.182 0 0 0 0 4.5 3.182 3.182 0 0 0 4.5 0Z" />
                                    </svg>
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 text-left min-w-0">
                                    <p class="text-sm font-medium truncate {{ $supp['currentTiming']['taken'] ? 'text-emerald-400' : 'text-wc-text' }}">
                                        {{ $supp['name'] }}
                                    </p>
                                    <p class="text-xs text-wc-text-tertiary truncate">{{ $supp['dose'] }}</p>
                                </div>

                                {{-- Checkbox --}}
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border transition-all
                                    {{ $supp['currentTiming']['taken'] ? 'border-emerald-500 bg-emerald-500' : 'border-wc-border bg-wc-bg-secondary' }}">
                                    @if($supp['currentTiming']['taken'])
                                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @endif
                                </div>
                            </button>

                            {{-- Coach notes --}}
                            @if($supp['notes'])
                                <div class="ml-13 rounded-lg bg-wc-accent/5 border border-wc-accent/10 px-3 py-2">
                                    <p class="text-[11px] text-wc-text-secondary">{{ $supp['notes'] }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Weekly Adherence Detail --}}
        <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-animate-delay="600">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Adherencia semanal</h2>
            <div class="grid grid-cols-7 gap-2">
                @foreach($dailyAdherence as $day)
                    @php
                        $sparkDate = \Carbon\Carbon::parse($selectedDate)->subDays(6 - $loop->index)->format('Y-m-d');
                        $sparkIsFuture = $sparkDate > today()->toDateString();
                    @endphp
                    <button
                        @if(!$sparkIsFuture) wire:click="$set('selectedDate', '{{ $sparkDate }}')" @endif
                        @disabled($sparkIsFuture)
                        class="flex flex-col items-center gap-1.5 rounded-lg p-2 transition-colors
                            {{ $day['isSelected'] ? 'bg-wc-accent/10 border border-wc-accent/20' : ($sparkIsFuture ? 'opacity-30 cursor-not-allowed' : 'hover:bg-wc-bg-secondary') }}"
                        @if($sparkIsFuture) title="Fecha futura" @endif
                    >
                        <span class="text-[10px] font-medium uppercase text-wc-text-tertiary">{{ $day['day'] }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full
                            {{ $day['pct'] >= 100 ? 'bg-emerald-500 text-white' : ($day['pct'] > 0 ? 'bg-wc-bg-secondary text-wc-text' : 'bg-wc-bg-secondary/50 text-wc-text-tertiary') }}">
                            <span class="font-data text-xs font-semibold">{{ $day['date'] }}</span>
                        </div>
                        <span class="font-data text-[10px] {{ $day['pct'] >= 100 ? 'text-emerald-500 font-semibold' : 'text-wc-text-tertiary' }}">{{ $day['pct'] }}%</span>
                    </button>
                @endforeach
            </div>
        </div>

    @else
        {{-- Empty State --}}
        <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6 5.25 5.25M4.5 19.5l6.75-6.75m-3.75 3.75 9-9a3.182 3.182 0 0 0 0-4.5 3.182 3.182 0 0 0-4.5 0l-9 9a3.182 3.182 0 0 0 0 4.5 3.182 3.182 0 0 0 4.5 0Z" />
                </svg>
            </div>
            <h3 class="font-display text-xl tracking-wide text-wc-text">TU PLAN DE SUPLEMENTACIÓN</h3>
            <p class="mt-2 text-sm text-wc-text-tertiary">Tu coach está preparando tu protocolo de suplementación personalizado</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Cuando esté listo, podrás hacer seguimiento diario aquí</p>
        </div>
    @endif

    {{-- ===== ONBOARDING TUTORIAL: SUPLEMENTOS ===== --}}
    @if($showTutorial)
    <div
        x-data="{ step: 1, total: 3 }"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
        @keydown.escape.window="$wire.dismissTutorial()"
    >
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-lg tracking-widest text-wc-text">SUPLEMENTACIÓN</h3>
                <button @click="$wire.dismissTutorial()" class="text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="step === 1">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Solo lo que necesitas</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Tu coach seleccionó únicamente suplementos con evidencia científica sólida. Sin ventas, sin obligaciones — solo lo que realmente funciona para tus metas.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 2">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Horarios importan</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Cada suplemento tiene un horario recomendado (mañana, pre-entrenamiento, noche). Respetar el timing maximiza la absorción y el efecto.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 3">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Marca lo que tomaste</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Toca cada suplemento para marcarlo como tomado. El tracking te ayuda a mantener consistencia — la clave para ver resultados reales.</p>
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
    {{-- ===== /ONBOARDING TUTORIAL: SUPLEMENTOS ===== --}}
</div>
