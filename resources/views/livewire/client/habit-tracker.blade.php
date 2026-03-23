<div class="space-y-6" x-data="{ expandedTip: null }">

    {{-- Confetti celebration --}}
    @if($showConfetti)
    <div class="pointer-events-none fixed inset-0 z-50" x-data x-init="setTimeout(() => $wire.set('showConfetti', false), 3000)">
        @for($i = 0; $i < 12; $i++)
        <div class="absolute animate-confetti-{{ $i % 4 }}" style="left: {{ rand(10, 90) }}%; top: -10px; animation-delay: {{ $i * 0.1 }}s">
            <div class="h-2 w-2 rounded-full {{ ['bg-wc-accent', 'bg-emerald-500', 'bg-amber-400', 'bg-violet-500'][$i % 4] }}"></div>
        </div>
        @endfor
    </div>
    @endif

    {{-- Title --}}
    <div data-animate="fadeInUp">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">HÁBITOS DIARIOS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">
            {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
        </p>
    </div>

    {{-- Today's Progress Ring --}}
    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-animate-delay="100">
        <div class="flex items-center gap-5">
            {{-- SVG Progress Ring --}}
            <div class="relative h-20 w-20 shrink-0">
                @php
                    $pctToday = $totalHabits > 0 ? ($completedToday / $totalHabits) : 0;
                    $circumference = 2 * 3.14159 * 34;
                    $offset = $circumference * (1 - $pctToday);
                @endphp
                <svg class="h-full w-full -rotate-90" viewBox="0 0 80 80">
                    <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="5" class="text-wc-bg-secondary" />
                    <circle cx="40" cy="40" r="34" fill="none" stroke-width="5" stroke-linecap="round"
                        class="{{ $completedToday === $totalHabits ? 'text-emerald-500' : 'text-wc-accent' }}"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        style="transition: stroke-dashoffset 0.8s ease-out" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="font-data text-lg font-bold {{ $completedToday === $totalHabits ? 'text-emerald-500' : 'text-wc-text' }}">{{ $completedToday }}/{{ $totalHabits }}</span>
                </div>
            </div>

            <div class="flex-1">
                <p class="text-sm font-medium text-wc-text">Progreso de hoy</p>
                <p class="text-xs text-wc-text-tertiary mt-0.5">
                    @if($completedToday === $totalHabits)
                        ¡Todos tus hábitos completados! Excelente trabajo.
                    @elseif($completedToday > 0)
                        {{ $totalHabits - $completedToday }} hábito{{ ($totalHabits - $completedToday) > 1 ? 's' : '' }} pendiente{{ ($totalHabits - $completedToday) > 1 ? 's' : '' }}
                    @else
                        Comienza tu día marcando tus hábitos
                    @endif
                </p>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full transition-all duration-700 {{ $completedToday === $totalHabits ? 'bg-emerald-500' : 'bg-wc-accent' }}"
                        style="width: {{ $totalHabits > 0 ? round(($completedToday / $totalHabits) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Habit Cards with Streaks & Rings --}}
    <div class="space-y-3">
        @foreach ($todayHabits as $type => $habit)
            <div data-animate="fadeInUp" data-animate-delay="{{ ($loop->index + 1) * 100 }}">
                <button
                    wire:click="toggleHabit('{{ $type }}')"
                    class="w-full flex items-center gap-4 rounded-[--radius-card] border p-4 transition-all btn-press
                        {{ $habit['completed']
                            ? 'border-emerald-500/30 bg-emerald-500/5'
                            : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-text-tertiary' }}"
                >
                    {{-- Icon --}}
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-all duration-300
                        {{ $habit['completed'] ? 'bg-emerald-500 text-white scale-110' : 'bg-wc-bg-secondary text-wc-text-tertiary' }}"
                    >
                        @switch($habit['icon'])
                            @case('water')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c0 0-6.75 8.25-6.75 12.75a6.75 6.75 0 0 0 13.5 0C18.75 10.5 12 2.25 12 2.25Z" /></svg>
                                @break
                            @case('moon')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                                @break
                            @case('dumbbell')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                                @break
                            @case('apple')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                @break
                            @case('pill')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6 5.25 5.25M4.5 19.5l6.75-6.75m-3.75 3.75 9-9a3.182 3.182 0 0 0 0-4.5 3.182 3.182 0 0 0-4.5 0l-9 9a3.182 3.182 0 0 0 0 4.5 3.182 3.182 0 0 0 4.5 0Z" /></svg>
                                @break
                        @endswitch
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 text-left min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium {{ $habit['completed'] ? 'text-emerald-400' : 'text-wc-text' }}">
                                {{ $habit['label'] }}
                            </p>
                            @if($habit['streak'] > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-400">
                                    <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 0 0-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 0 0-.613 3.58 2.64 2.64 0 0 1-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 0 0 5.05 6.05 6.981 6.981 0 0 0 3 11a7 7 0 1 0 11.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03ZM12.12 15.12A3 3 0 0 1 7 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0 1 13 13a2.99 2.99 0 0 1-.879 2.121Z"/></svg>
                                    {{ $habit['streak'] }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-1">
                            <p class="text-xs {{ $habit['completed'] ? 'text-emerald-500/70' : 'text-wc-text-tertiary' }}">
                                {{ $habit['completed'] ? 'Completado' : 'Pendiente' }}
                            </p>
                            {{-- Mini compliance bar --}}
                            <div class="flex items-center gap-1.5 flex-1 max-w-[120px]">
                                <div class="h-1 flex-1 rounded-full bg-wc-bg-secondary overflow-hidden">
                                    <div class="h-full rounded-full {{ $habit['compliance'] >= 80 ? 'bg-emerald-500' : ($habit['compliance'] >= 50 ? 'bg-amber-400' : 'bg-wc-accent') }}"
                                        style="width: {{ $habit['compliance'] }}%"></div>
                                </div>
                                <span class="font-data text-[10px] text-wc-text-tertiary">{{ $habit['compliance'] }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Checkbox --}}
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border transition-all
                        {{ $habit['completed'] ? 'border-emerald-500 bg-emerald-500' : 'border-wc-border bg-wc-bg-secondary' }}">
                        @if($habit['completed'])
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        @endif
                    </div>
                </button>

                {{-- Coach Tip (expandable) --}}
                <button @click="expandedTip = expandedTip === '{{ $type }}' ? null : '{{ $type }}'"
                    class="mt-1 ml-16 flex items-center gap-1 text-[10px] text-wc-text-tertiary hover:text-wc-text-secondary transition-colors">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    Consejo de tu coach
                </button>
                <div x-show="expandedTip === '{{ $type }}'" x-transition x-cloak
                    class="ml-16 mt-1 rounded-lg bg-wc-accent/5 border border-wc-accent/10 px-3 py-2">
                    <p class="text-xs text-wc-text-secondary">{{ $habit['tip'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Weekly Overview --}}
    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-animate-delay="400">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Resumen semanal</h2>

        <div class="grid grid-cols-7 gap-2">
            @foreach ($weeklyData as $day)
                <div class="flex flex-col items-center gap-2 {{ $day['isFuture'] ? 'opacity-35' : '' }}">
                    <span class="text-[10px] font-medium uppercase text-wc-text-tertiary">{{ $day['dayName'] }}</span>
                    @php
                        $pct = (!$day['isFuture'] && $day['total'] > 0) ? ($day['completed'] / $day['total']) : 0;
                    @endphp
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full border-2 transition-colors
                        {{ $day['isToday'] ? 'border-wc-accent' : 'border-wc-border' }}
                        {{ $pct >= 1 ? 'bg-emerald-500 border-emerald-500' : ($pct > 0 ? 'bg-wc-accent/10' : '') }}">
                        <span class="font-data text-xs font-semibold {{ $pct >= 1 ? 'text-white' : ($day['isToday'] ? 'text-wc-accent' : 'text-wc-text') }}">
                            {{ $day['dayNumber'] }}
                        </span>
                    </div>
                    <span class="font-data text-[10px] {{ $pct >= 1 ? 'text-emerald-500 font-semibold' : 'text-wc-text-tertiary' }}">
                        @if($day['isFuture'])
                            &mdash;
                        @else
                            {{ $day['completed'] }}/{{ $day['total'] }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 30-Day Heatmap --}}
    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-animate-delay="500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Últimos 30 días</h2>
            <div class="flex items-center gap-1 text-[10px] text-wc-text-tertiary">
                <span>Menos</span>
                @foreach ([0, 1, 2, 3, 4] as $level)
                    <div class="h-3 w-3 rounded-sm {{ [
                        'bg-wc-bg-secondary',
                        'bg-emerald-900',
                        'bg-emerald-700',
                        'bg-emerald-500',
                        'bg-emerald-400',
                    ][$level] }}"></div>
                @endforeach
                <span>Más</span>
            </div>
        </div>

        <div class="grid grid-cols-10 gap-1 sm:grid-cols-15">
            @foreach ($heatmapData as $cell)
                <div class="h-6 w-full rounded-sm transition-colors {{ [
                    'bg-wc-bg-secondary',
                    'bg-emerald-900',
                    'bg-emerald-700',
                    'bg-emerald-500',
                    'bg-emerald-400',
                ][$cell['level']] }}"
                    title="{{ $cell['date'] }}: {{ $cell['count'] }}/{{ $cell['total'] }}"
                ></div>
            @endforeach
        </div>
    </div>

    {{-- ===== ONBOARDING TUTORIAL: HÁBITOS ===== --}}
    @if($showTutorial)
    <div
        x-data="{ step: 1, total: 3 }"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
        @keydown.escape.window="$wire.dismissTutorial()"
    >
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">

            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display text-lg tracking-widest text-wc-text">HÁBITOS DIARIOS</h3>
                <button @click="$wire.dismissTutorial()" class="text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="step === 1">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Los hábitos hacen los resultados</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">El 80% de tu transformación viene de hábitos fuera del gym: dormir bien, hidratarte, cumplir tus macros. El entrenamiento es el 20%.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 2">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">Marca cada hábito completado</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Toca el hábito cuando lo completes en el día. El sistema registra tu racha semanal — una racha larga es el indicador más fuerte de progreso sostenido.</p>
                    </div>
                </div>
            </div>

            <div x-show="step === 3">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                    <div>
                        <p class="font-semibold text-wc-text text-sm">No rompas la cadena</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Si fallas un día, no te preocupes — nunca falles dos días seguidos. La consistencia imperfecta supera siempre a la perfección intermitente.</p>
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
                <button x-show="step === total" @click="$wire.dismissTutorial()" class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors" type="button">¡Listo, a construir hábitos!</button>
            </div>
        </div>
    </div>
    @endif
    {{-- ===== /ONBOARDING TUTORIAL: HÁBITOS ===== --}}

    {{-- Confetti CSS --}}
    <style>
        @keyframes confetti-fall-0 { 0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; } 100% { transform: translateY(110vh) rotate(720deg); opacity: 0; } }
        @keyframes confetti-fall-1 { 0% { transform: translateY(-10vh) rotate(0deg) translateX(-20px); opacity: 1; } 100% { transform: translateY(110vh) rotate(540deg) translateX(20px); opacity: 0; } }
        @keyframes confetti-fall-2 { 0% { transform: translateY(-10vh) rotate(0deg) translateX(15px); opacity: 1; } 100% { transform: translateY(110vh) rotate(-360deg) translateX(-15px); opacity: 0; } }
        @keyframes confetti-fall-3 { 0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; } 100% { transform: translateY(110vh) rotate(900deg); opacity: 0; } }
        .animate-confetti-0 { animation: confetti-fall-0 2.5s ease-in forwards; }
        .animate-confetti-1 { animation: confetti-fall-1 2.8s ease-in forwards; }
        .animate-confetti-2 { animation: confetti-fall-2 2.2s ease-in forwards; }
        .animate-confetti-3 { animation: confetti-fall-3 3s ease-in forwards; }
    </style>
</div>
