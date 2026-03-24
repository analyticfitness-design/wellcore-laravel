<div
    x-data="{
        feedbackSaved: false,
        showConfetti: true,
        init() {
            // Trigger confetti for 4 seconds
            setTimeout(() => this.showConfetti = false, 4000);

            // Re-observe counters after Livewire hydration
            this.$nextTick(() => {
                document.querySelectorAll('[data-counter]').forEach(el => {
                    if (!el.dataset.counterAnimated) {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    const target = parseInt(entry.target.dataset.counter);
                                    if (!isNaN(target)) {
                                        let start = 0;
                                        const suffix = entry.target.dataset.counterSuffix || '';
                                        const prefix = entry.target.dataset.counterPrefix || '';
                                        const duration = 1500;
                                        const step = (timestamp) => {
                                            if (!start) start = timestamp;
                                            const progress = Math.min((timestamp - start) / duration, 1);
                                            const eased = 1 - Math.pow(1 - progress, 3);
                                            entry.target.textContent = prefix + Math.floor(eased * target).toLocaleString() + suffix;
                                            if (progress < 1) requestAnimationFrame(step);
                                            else entry.target.textContent = prefix + target.toLocaleString() + suffix;
                                        };
                                        requestAnimationFrame(step);
                                    }
                                    observer.unobserve(entry.target);
                                }
                            });
                        }, { threshold: 0.3 });
                        observer.observe(el);
                    }
                });
            });
        }
    }"
    x-on:feedback-saved.window="feedbackSaved = true; setTimeout(() => feedbackSaved = false, 3000)"
    class="space-y-8 pb-24 lg:pb-8"
>

    {{-- ─── Confetti Particles (CSS-only) ─── --}}
    <template x-if="showConfetti">
        <div class="pointer-events-none fixed inset-0 z-50 overflow-hidden" aria-hidden="true">
            <div class="confetti-particle" style="left: 8%; background: #DC2626; animation: confettiFall 2.8s ease-in forwards 0.1s;"></div>
            <div class="confetti-particle" style="left: 22%; background: #F59E0B; animation: confettiFall 3.2s ease-in forwards 0.3s; border-radius: 50%;"></div>
            <div class="confetti-particle" style="left: 38%; background: #10B981; animation: confettiFall 2.5s ease-in forwards 0s;"></div>
            <div class="confetti-particle" style="left: 52%; background: #DC2626; animation: confettiFall 3s ease-in forwards 0.5s; border-radius: 50%;"></div>
            <div class="confetti-particle" style="left: 65%; background: #8B5CF6; animation: confettiFall 2.7s ease-in forwards 0.2s;"></div>
            <div class="confetti-particle" style="left: 78%; background: #F59E0B; animation: confettiFall 3.4s ease-in forwards 0.4s; border-radius: 50%;"></div>
            <div class="confetti-particle" style="left: 90%; background: #10B981; animation: confettiFall 2.6s ease-in forwards 0.15s;"></div>
            <div class="confetti-particle" style="left: 45%; background: #8B5CF6; animation: confettiFall 3.1s ease-in forwards 0.6s;"></div>
        </div>
    </template>

    {{-- ─── Motivational Hero ─── --}}
    <style>
        @keyframes confettiFall {
            0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }
        .confetti-particle {
            position: absolute;
            top: -10px;
            width: 10px;
            height: 10px;
        }
        @keyframes heroTrophyBounce {
            0%, 100% { transform: scale(1) rotate(-3deg); }
            50%       { transform: scale(1.15) rotate(3deg); }
        }
        .hero-trophy { animation: heroTrophyBounce 2s ease-in-out infinite; display: inline-block; }
    </style>

    @php
        $setsCompleted = $stats['sets_completed'] ?? 0;
        $motivationalPhrase = match(true) {
            $setsCompleted >= 15 => '¡BESTIA ABSOLUTA! SESIÓN ÉPICA.',
            $setsCompleted >= 10 => '¡MÁQUINA! HOY GANASTE.',
            $setsCompleted >= 5  => '¡ASÍ SE HACE! SIGUE ADELANTE.',
            default              => '¡COMPLETADO! CADA REP CUENTA.',
        };
        $heroEmoji = $setsCompleted >= 10 ? '🏆' : '💥';
    @endphp

    <div class="relative overflow-hidden rounded-2xl"
         style="min-height: 220px; background: linear-gradient(135deg, #0f0f0f 0%, #1a0000 50%, #DC2626 100%);"
         data-animate="fadeInUp">
        {{-- Decorative radial glow --}}
        <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% 0%, rgba(220,38,38,0.4) 0%, transparent 70%);"></div>

        {{-- Grid lines decoration --}}
        <div class="pointer-events-none absolute inset-0 opacity-10"
             style="background-image: repeating-linear-gradient(0deg, rgba(255,255,255,0.15) 0px, transparent 1px, transparent 40px, rgba(255,255,255,0.15) 41px), repeating-linear-gradient(90deg, rgba(255,255,255,0.15) 0px, transparent 1px, transparent 40px, rgba(255,255,255,0.15) 41px);"></div>

        <div class="relative z-10 flex flex-col items-center justify-center px-6 py-10 text-center">
            {{-- Trophy / Fire emoji --}}
            <span class="hero-trophy text-6xl sm:text-7xl mb-4" aria-hidden="true">{{ $heroEmoji }}</span>

            {{-- WellCore brand --}}
            <div class="flex items-center gap-2 mb-3">
                <span class="font-display text-2xl tracking-[0.25em] text-white/80 sm:text-3xl">WELLCORE</span>
                <span class="inline-block h-3 w-3 rounded-full bg-wc-accent shadow-lg shadow-wc-accent/60" aria-hidden="true"></span>
            </div>

            {{-- Motivational phrase --}}
            <p class="font-display text-3xl tracking-widest text-white sm:text-4xl drop-shadow-lg">
                {{ $motivationalPhrase }}
            </p>

            {{-- Session label --}}
            <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5">
                <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wider text-white/80">Sesión Completada</span>
            </div>

            @if($session->day_name)
                <p class="mt-3 text-base font-medium text-white/70">
                    {{ $session->day_name }}
                </p>
            @endif

            @if($session->session_date)
                <p class="mt-1 text-sm text-white/50">
                    {{ $session->session_date->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                </p>
            @endif
        </div>
    </div>

    {{-- ─── Celebration Header (kept for fallback accessibility) ─── --}}
    <div class="sr-only">
        <h1>SESIÓN COMPLETADA — {{ $motivationalPhrase }}</h1>
    </div>

    {{-- ─── Stats Grid (2x3, Ladder-inspired) ─── --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 stagger-grid" data-animate="fadeInUp" data-animate-delay="200">
        {{-- Duración --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Duración</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">{{ $stats['duration'] }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">min</p>
        </div>

        {{-- Volumen Total --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Volumen Total</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">
                <span data-counter="{{ $stats['volume'] }}">0</span>
            </p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">kg</p>
        </div>

        {{-- Reps Totales --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Reps Totales</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">
                <span data-counter="{{ $stats['reps'] }}">0</span>
            </p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">reps</p>
        </div>

        {{-- Sets Completados --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Sets</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">
                <span data-counter="{{ $stats['sets_completed'] }}">0</span><span class="text-lg text-wc-text-tertiary">/{{ $stats['sets_total'] }}</span>
            </p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">completados</p>
        </div>

        {{-- XP Ganados --}}
        <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-4 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-accent">XP Ganados</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-accent sm:text-4xl">
                <span data-counter="{{ $xpEarned }}" data-counter-prefix="+">0</span>
            </p>
            <p class="mt-0.5 text-xs text-wc-accent/70">XP</p>
        </div>

        {{-- Ejercicios --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Ejercicios</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text sm:text-4xl">
                <span data-counter="{{ $stats['exercises_count'] }}">0</span>
            </p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">completados</p>
        </div>
    </div>

    {{-- ─── PR Achievements ─── --}}
    @if(count($prs) > 0)
        <div data-animate="scaleIn" data-animate-delay="400">
            <div class="relative overflow-hidden rounded-xl border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-yellow-500/5 to-amber-600/10 p-5 badge-shine">
                {{-- Header --}}
                <div class="mb-4 flex items-center gap-3">
                    {{-- Trophy SVG --}}
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20">
                        <svg class="h-7 w-7 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5 3h14c.6 0 1 .4 1 1v2c0 2.8-2.2 5-5 5h-.2c-.5 1.5-1.5 2.7-2.8 3.4V18h3c.6 0 1 .4 1 1v2H7v-2c0-.6.4-1 1-1h3v-3.6c-1.3-.7-2.3-1.9-2.8-3.4H8c-2.8 0-5-2.2-5-5V4c0-.6.4-1 1-1Zm1 2v1c0 1.7 1.3 3 3 3h.1C9 8.4 9 7.7 9 7V5H6Zm12 0h-3v2c0 .7 0 1.4-.1 2H15c1.7 0 3-1.3 3-3V5Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-display text-lg tracking-wider text-amber-500">
                            ¡NUEVO RÉCORD PERSONAL!
                        </p>
                        <p class="text-xs text-amber-500/70">
                            {{ count($prs) }} {{ count($prs) === 1 ? 'récord superado' : 'récords superados' }} en esta sesión
                        </p>
                    </div>
                </div>

                {{-- PR List --}}
                <div class="space-y-2">
                    @foreach($prs as $pr)
                        <div class="flex items-center gap-3 rounded-lg bg-amber-500/10 px-4 py-3">
                            <svg class="h-5 w-5 shrink-0 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-wc-text truncate">{{ $pr['exercise'] }}</p>
                            </div>
                            <p class="font-data text-sm font-bold text-amber-500 whitespace-nowrap">
                                {{ number_format($pr['weight'], 1) }} kg &times; {{ $pr['reps'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ─── Feeling Selector (1-5 Scale) ─── --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-animate-delay="300">
        <h3 class="mb-4 text-center font-display text-xl tracking-wide text-wc-text">¿CÓMO TE SENTISTE?</h3>

        <div class="flex items-center justify-center gap-3 sm:gap-5">
            @php
                $feelings = [
                    1 => ['emoji' => "\u{1F62B}", 'label' => 'Muy difícil'],
                    2 => ['emoji' => "\u{1F615}", 'label' => 'Difícil'],
                    3 => ['emoji' => "\u{1F610}", 'label' => 'Normal'],
                    4 => ['emoji' => "\u{1F60A}", 'label' => 'Bien'],
                    5 => ['emoji' => "\u{1F4AA}", 'label' => 'Increíble'],
                ];
            @endphp

            @foreach($feelings as $value => $data)
                <button
                    wire:click="$set('feeling', {{ $value }})"
                    class="flex flex-col items-center gap-1.5 rounded-xl px-3 py-3 transition-all duration-200
                        {{ $feeling === $value
                            ? 'scale-110 bg-wc-accent/10 ring-2 ring-wc-accent/50'
                            : 'hover:bg-wc-bg-secondary hover:scale-105' }}"
                    title="{{ $data['label'] }}"
                >
                    <span class="text-2xl sm:text-3xl" aria-hidden="true">{{ $data['emoji'] }}</span>
                    <span class="text-[10px] font-medium {{ $feeling === $value ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">
                        {{ $data['label'] }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ─── Session Notes ─── --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-animate-delay="400">
        <label for="session-notes" class="mb-2 block text-sm font-medium text-wc-text">
            Notas de la sesión <span class="text-wc-text-tertiary">(opcional)</span>
        </label>
        <textarea
            wire:model="notes"
            id="session-notes"
            rows="3"
            maxlength="1000"
            placeholder="¿Cómo te sentiste? Notas de la sesión..."
            class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
        ></textarea>
        <p class="mt-1 text-right text-xs text-wc-text-tertiary">
            {{ strlen($notes) }}/1000
        </p>
    </div>

    {{-- ─── Save & Actions ─── --}}
    <div class="space-y-3" data-animate="fadeInUp" data-animate-delay="500">
        {{-- Success toast --}}
        <div
            x-show="feedbackSaved"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-2 rounded-lg bg-green-500/10 border border-green-500/20 px-4 py-3"
            x-cloak
        >
            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium text-green-500">Retroalimentación guardada</span>
        </div>

        {{-- Save Button --}}
        <button
            wire:click="saveFeedback"
            wire:loading.attr="disabled"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent px-6 py-4 font-display text-lg tracking-wider text-white transition-colors hover:bg-wc-accent-hover disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="saveFeedback">GUARDAR</span>
            <span wire:loading wire:target="saveFeedback" class="flex items-center gap-2">
                <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Guardando...
            </span>
        </button>

        {{-- Secondary Actions --}}
        <div class="flex flex-col gap-3 sm:flex-row">
            <button
                wire:click="shareToCommunity"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-tertiary px-5 py-3 text-sm font-medium text-wc-text transition-colors hover:border-wc-accent/30 hover:text-wc-accent"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>
                Compartir en Comunidad
            </button>

            <a
                href="{{ route('client.dashboard') }}"
                wire:navigate
                class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-tertiary px-5 py-3 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Volver al Dashboard
            </a>
        </div>
    </div>

    {{-- ─── Session History ─── --}}
    @if(count($sessionHistory) > 0)
        <div data-animate="fadeInUp" data-animate-delay="600">
            <div class="section-divider mb-6"></div>

            <h3 class="mb-4 font-display text-xl tracking-wide text-wc-text">HISTORIAL RECIENTE</h3>

            {{-- Mini Session Cards (last 5) --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach(array_slice($sessionHistory, 0, 5) as $past)
                    <div class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/20">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary">
                            <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-wc-text truncate">{{ $past['day_name'] }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ $past['date'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-data text-sm font-bold text-wc-text">{{ $past['duration'] }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ number_format($past['volume']) }} kg</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Volume Trend (Pure CSS Bars) --}}
            @php
                $historyForChart = array_reverse(array_slice($sessionHistory, 0, 5));
                $maxVolume = max(array_column($historyForChart, 'volume') ?: [1]);
                if ($maxVolume === 0) $maxVolume = 1;
            @endphp

            @if(count($historyForChart) >= 2)
                <div class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                    <p class="mb-4 text-sm font-semibold text-wc-text">Tendencia de volumen</p>
                    <div class="flex items-end gap-2 sm:gap-3" style="height: 120px;">
                        @foreach($historyForChart as $entry)
                            @php
                                $heightPct = ($entry['volume'] / $maxVolume) * 100;
                                $heightPct = max($heightPct, 8); // min visible height
                            @endphp
                            <div class="flex flex-1 flex-col items-center gap-1">
                                <span class="font-data text-[10px] font-bold text-wc-text-tertiary">{{ number_format($entry['volume']) }}</span>
                                <div
                                    class="w-full rounded-t-md bg-wc-accent/70 transition-all duration-700"
                                    style="height: {{ $heightPct }}%;"
                                ></div>
                                <span class="text-[10px] text-wc-text-tertiary truncate max-w-full">{{ $entry['date'] }}</span>
                            </div>
                        @endforeach

                        {{-- Current session bar (highlighted) --}}
                        @php
                            $currentPct = ($stats['volume'] / $maxVolume) * 100;
                            $currentPct = min($currentPct, 100);
                            $currentPct = max($currentPct, 8);
                        @endphp
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="font-data text-[10px] font-bold text-wc-accent">{{ number_format($stats['volume']) }}</span>
                            <div
                                class="w-full rounded-t-md bg-wc-accent transition-all duration-700"
                                style="height: {{ $currentPct }}%;"
                            ></div>
                            <span class="text-[10px] font-semibold text-wc-accent">Hoy</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Link to full training view --}}
            <div class="mt-4 text-center">
                <a
                    href="{{ route('client.training') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-wc-accent hover:underline"
                >
                    Ver historial completo
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>
    @endif

</div>
