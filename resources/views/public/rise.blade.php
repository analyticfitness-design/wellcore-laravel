<x-layouts.public>
    <x-slot:title>RISE - Reto 30 Dias de Transformacion | WellCore Fitness</x-slot:title>
    <x-slot:description>RISE: el reto de transformacion real de WellCore. 30 dias, personalizado, con ciencia. Entrenamiento 1:1, nutricion, habitos y seguimiento. $99.900 COP pago unico.</x-slot:description>

    {{-- ================================================================== --}}
    {{-- 1. HERO (remastered — hero-gradient, extra orbs, pulse-glow)        --}}
    {{-- ================================================================== --}}
    <section class="relative overflow-hidden hero-gradient">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/8 via-transparent to-transparent"></div>
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.1"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36" data-animate="fadeInUp">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                {{-- Left — Text --}}
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">Reto Activo &middot; Marzo 2026</p>
                    <h1 class="mt-4 font-display text-6xl leading-none tracking-wide text-wc-text sm:text-7xl lg:text-9xl">
                        RISE.<br>
                        <span class="text-gradient-accent">30 DIAS.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg text-wc-text-secondary">
                        El reto de transformacion real de WellCore. Personalizado, con ciencia, sin atajos. Todos los niveles. Gym o casa.
                    </p>

                    {{-- Countdown Timer --}}
                    <div class="mt-8 pulse-glow inline-block rounded-xl p-1" x-data="countdown('2026-03-31T23:59:59')" x-init="start()">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cierra inscripcion en:</p>
                        <div class="flex gap-3">
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="days">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">Dias</p>
                            </div>
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="hours">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">Horas</p>
                            </div>
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="minutes">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">Min</p>
                            </div>
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="seconds">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">Seg</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('planes') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                            Unirme al Reto RISE
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <p class="mt-3 text-xs text-wc-text-tertiary">Sin compromiso &middot; Pago unico $99.900</p>
                    </div>
                </div>

                {{-- Right — RISE Dashboard Mockup --}}
                <div class="hidden lg:block">
                    <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">rise.wellcorefitness.com</span>
                            </div>
                        </div>
                        {{-- Dashboard content --}}
                        <div class="p-5 space-y-4">
                            {{-- Header --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-display text-lg tracking-wide text-wc-text">RISE <span class="text-wc-text-tertiary">&mdash;</span> Semana 2 de 4</h3>
                                </div>
                                <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-[10px] font-semibold text-wc-accent">Activo</span>
                            </div>

                            {{-- Progress bar --}}
                            <div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-wc-text-secondary">Progreso general</span>
                                    <span class="font-data font-semibold text-wc-text">50%</span>
                                </div>
                                <div class="mt-1.5 h-2 w-full rounded-full bg-wc-bg-secondary">
                                    <div class="h-2 w-1/2 rounded-full bg-wc-accent"></div>
                                </div>
                            </div>

                            {{-- Stats grid 2x2 --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-text">12 <span class="text-sm font-normal text-wc-text-tertiary">/ 30</span></p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">Dia</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-emerald-400">85%</p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">Adherencia</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-accent">4.2 <span class="text-sm font-normal text-wc-text-tertiary">kg</span></p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">Perdidos</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-amber-400">8/8</p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">Habitos hoy</p>
                                </div>
                            </div>

                            {{-- Daily checklist card --}}
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span class="text-xs font-semibold text-wc-text">Misiones de hoy</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        <span class="text-xs text-wc-text-secondary line-through">Entrenamiento completado</span>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        <span class="text-xs text-wc-text-secondary line-through">2L agua</span>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-4 w-4 shrink-0 rounded-full border-2 border-wc-border"></div>
                                        <span class="text-xs text-wc-text-secondary">Foto de progreso</span>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-4 w-4 shrink-0 rounded-full border-2 border-wc-border"></div>
                                        <span class="text-xs text-wc-text-secondary">Check-in semanal</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- 1B. SOCIAL PROOF COUNTER (NEW)                                      --}}
    {{-- ================================================================== --}}
    <section class="border-y border-wc-border bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-8 text-center">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm text-wc-text-secondary"><span class="font-data font-bold text-wc-text counter-highlight" data-counter="47" data-counter-suffix="+">0+</span> personas se inscribieron esta semana</span>
                </div>
                <div class="hidden h-4 w-px bg-wc-border sm:block"></div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-wc-text-secondary">Cupos restantes: <span class="font-data font-bold text-wc-accent">12</span></span>
                </div>
                <div class="hidden h-4 w-px bg-wc-border sm:block"></div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <span class="text-sm text-wc-text-secondary">Verificado por <span class="font-semibold text-wc-text">WellCore</span></span>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 2. QUE INCLUYE (remastered — stagger-grid, card-hover-lift)         --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">QUE INCLUYE</h2>
                <p class="mt-2 text-lg text-wc-text-secondary">4 pilares. 1 metodo.</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">Todo lo que necesitas para transformar tu cuerpo en 30 dias.</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 stagger-grid">
                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Entrenamiento</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Programa 1:1 disenado para ti. Gym o casa. Sin importar tu nivel.</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="200">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Nutricion</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Guia de alimentacion real para los 30 dias. Sin dietas extremas.</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="300">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Habitos</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Sistema diario de habitos para garantizar tu constancia.</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="400">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Seguimiento</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Revision de tu progreso durante los 30 dias del reto.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 3. TU DASHBOARD RISE (remastered — animate-float, animate-float-slow) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Experiencia</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">TU DASHBOARD RISE</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm text-wc-text-tertiary">
                    Controla cada aspecto de tu reto desde un panel disenado para mantenerte enfocado. Disponible en cualquier dispositivo.
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                {{-- LEFT — Phone mockup --}}
                <div class="flex justify-center">
                    <div class="animate-float mx-auto w-[280px] rounded-[2.5rem] border-[6px] border-wc-border bg-wc-bg-tertiary p-2 shadow-2xl shadow-wc-accent/10">
                        {{-- Notch --}}
                        <div class="mx-auto mb-2 h-5 w-24 rounded-full bg-wc-bg-secondary"></div>
                        {{-- Screen content --}}
                        <div class="rounded-[2rem] bg-wc-bg p-4 space-y-3">
                            {{-- Day heading --}}
                            <div class="text-center">
                                <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">Reto RISE</p>
                                <p class="font-display text-3xl tracking-wide text-wc-text">DIA 15</p>
                            </div>

                            {{-- Progress ring --}}
                            <div class="flex justify-center">
                                <div class="relative flex h-20 w-20 items-center justify-center">
                                    <svg class="h-20 w-20 -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-wc-bg-secondary" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                        <path class="text-wc-accent" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="50, 100" stroke-linecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    </svg>
                                    <span class="absolute font-data text-lg font-bold text-wc-text">50%</span>
                                </div>
                            </div>

                            {{-- Habit checklist --}}
                            <div class="space-y-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-wc-text-tertiary">Habitos de hoy</p>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-emerald-400/20">
                                        <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <span class="text-[11px] text-wc-text-secondary">Entrenamiento</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-emerald-400/20">
                                        <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <span class="text-[11px] text-wc-text-secondary">2L agua</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-emerald-400/20">
                                        <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <span class="text-[11px] text-wc-text-secondary">8h sueño</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 shrink-0 rounded border border-wc-border"></div>
                                    <span class="text-[11px] text-wc-text-secondary">Foto progreso</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 shrink-0 rounded border border-wc-border"></div>
                                    <span class="text-[11px] text-wc-text-secondary">Registro comidas</span>
                                </div>
                            </div>

                            {{-- Streak badge --}}
                            <div class="flex justify-center">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent/10 px-3 py-1">
                                    <svg class="h-3.5 w-3.5 text-wc-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M12.75 3.03v.568c0 .334.148.65.405.864A6.75 6.75 0 0 1 9.75 15.75a.75.75 0 0 1 0-1.5 5.25 5.25 0 0 0 4.659-7.643.75.75 0 0 0-1.334.21A4.508 4.508 0 0 1 8.25 10.5a.75.75 0 0 1 0-1.5 3 3 0 0 0 2.903-3.75.75.75 0 0 0-.746-.692h-.107a7.502 7.502 0 0 0-2.55 14.578.75.75 0 0 1-.212 1.467A9.001 9.001 0 0 1 12.75 3.03Z"/></svg>
                                    <span class="text-[11px] font-semibold text-wc-accent">Racha: 12 dias</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT — Desktop/Tablet mockup --}}
                <div>
                    <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">rise.wellcorefitness.com/dashboard</span>
                            </div>
                        </div>
                        {{-- Dashboard content --}}
                        <div class="p-5 space-y-4">
                            {{-- Top stats row --}}
                            <div class="grid grid-cols-4 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-wc-accent">50%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Progreso</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-emerald-400">85%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Adherencia</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-amber-400">12</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Racha dias</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-wc-text">-4.2<span class="text-xs font-normal text-wc-text-tertiary">kg</span></p>
                                    <p class="text-[10px] text-wc-text-tertiary">Peso</p>
                                </div>
                            </div>

                            {{-- Weekly calendar grid --}}
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-wc-text-tertiary">Semana 2</p>
                                <div class="grid grid-cols-7 gap-1">
                                    @php $daysOfWeek = ['Lun','Mar','Mie','Jue','Vie','Sab','Dom']; @endphp
                                    @foreach ($daysOfWeek as $i => $day)
                                        <div class="text-center">
                                            <p class="text-[9px] text-wc-text-tertiary">{{ $day }}</p>
                                            @if ($i < 3)
                                                <div class="mx-auto mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-400/20">
                                                    <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                </div>
                                            @elseif ($i === 3)
                                                <div class="mx-auto mt-1 flex h-7 w-7 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-accent/10">
                                                    <span class="font-data text-[10px] font-bold text-wc-accent">15</span>
                                                </div>
                                            @else
                                                <div class="mx-auto mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-wc-bg-secondary">
                                                    <span class="font-data text-[10px] text-wc-text-tertiary">{{ 15 + ($i - 3) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Bottom row: Measurements + Training --}}
                            <div class="grid grid-cols-2 gap-3">
                                {{-- Measurements card --}}
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-wc-text-tertiary">Mediciones</p>
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-wc-text-secondary">Peso</span>
                                            <span class="flex items-center gap-1 text-[11px]">
                                                <span class="font-data font-semibold text-wc-text">78.3 kg</span>
                                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-wc-text-secondary">Cintura</span>
                                            <span class="flex items-center gap-1 text-[11px]">
                                                <span class="font-data font-semibold text-wc-text">86 cm</span>
                                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-wc-text-secondary">Cadera</span>
                                            <span class="flex items-center gap-1 text-[11px]">
                                                <span class="font-data font-semibold text-wc-text">98 cm</span>
                                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Training log card --}}
                                <div class="rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-3">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-wc-accent">Entrenamiento</p>
                                    <p class="text-xs font-semibold text-wc-text">Semana 2 &mdash; Upper Body A</p>
                                    <div class="mt-2 space-y-1">
                                        <div class="flex items-center justify-between text-[10px] text-wc-text-secondary">
                                            <span>Press banca</span>
                                            <span class="font-data font-semibold text-wc-text">4x10</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-wc-text-secondary">
                                            <span>Remo con barra</span>
                                            <span class="font-data font-semibold text-wc-text">4x10</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-wc-text-secondary">
                                            <span>Press militar</span>
                                            <span class="font-data font-semibold text-wc-text">3x12</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 4. SEGUIMIENTO EN TIEMPO REAL (remastered — stagger-grid, card-glow) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeIn">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Tecnologia</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">SEGUIMIENTO EN TIEMPO REAL</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm text-wc-text-tertiary">
                    Tu dashboard RISE rastrea cada habito, cada entrenamiento y cada medicion automaticamente. Datos reales para decisiones reales.
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-3 stagger-grid">
                {{-- Tracking Diario --}}
                <div class="card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-delay="100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Tracking Diario</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Log de habitos, peso, agua y entrenamiento. Cada dia queda registrado automaticamente en tu panel.
                    </p>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Registro de habitos diarios
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Consumo de agua y sueño
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Log de entrenamiento
                        </div>
                    </div>
                </div>

                {{-- Mediciones Semanales --}}
                <div class="card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-delay="200">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Mediciones Semanales</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Peso, medidas corporales y fotos de progreso automaticas. Tu evolucion documentada semana a semana.
                    </p>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Peso y composicion corporal
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Circunferencias: cintura, cadera, brazo
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Fotos comparativas automaticas
                        </div>
                    </div>
                </div>

                {{-- Analisis de Progreso --}}
                <div class="card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-delay="300">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Analisis de Progreso</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Graficas de tendencia y comparativas semana a semana. Visualiza tu transformacion con datos claros.
                    </p>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Graficas de tendencia de peso
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Comparativas semanales
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            Score de adherencia historico
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 5. PARA QUIEN ES (remastered — scroll-reveal, counter-highlight)    --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInRight">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PARA QUIEN ES</h2>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="scroll-reveal flex gap-4" data-delay="100">
                    <span class="counter-highlight font-data text-4xl font-bold text-wc-accent/20">01</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">Todos los niveles</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">Principiante, intermedio o avanzado. El reto se adapta a ti, no al reves.</p>
                    </div>
                </div>
                <div class="scroll-reveal flex gap-4" data-delay="200">
                    <span class="counter-highlight font-data text-4xl font-bold text-wc-accent/20">02</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">Gym o casa</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">Sin equipamiento especial requerido. Tu espacio, tu ritmo.</p>
                    </div>
                </div>
                <div class="scroll-reveal flex gap-4" data-delay="300">
                    <span class="counter-highlight font-data text-4xl font-bold text-wc-accent/20">03</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">30 dias reales</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">Un compromiso de un mes. Resultados medibles. Sin promesas vacias.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 6. TESTIMONIOS (remastered — card-hover-lift, scroll-reveal, stagger) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">QUIENES YA LO VIVIERON</h2>

            <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-delay="100">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "Los 30 dias me demostraron que si era posible. El plan estaba hecho para mi, no un copy-paste."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">LM</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Laura M.</p>
                            <p class="text-xs text-wc-text-tertiary">-8 kg &middot; Participante RISE</p>
                        </div>
                    </div>
                </div>

                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-delay="200">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "La guia de habitos fue el diferencial. Por primera vez la constancia no fue un problema."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">CR</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Carlos R.</p>
                            <p class="text-xs text-wc-text-tertiary">+5 kg musculo &middot; Participante RISE</p>
                        </div>
                    </div>
                </div>

                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-delay="300">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "Funciona sin importar el lugar. Lo hice desde casa y los resultados fueron reales."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">AP</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Ana P.</p>
                            <p class="text-xs text-wc-text-tertiary">Entrenamiento en casa &middot; Participante RISE</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 7. PRICING (remastered — card-glow, pulse-glow, badge-shine)        --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="scaleIn">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">INVERSION</h2>
                <p class="mt-2 text-lg text-wc-text-secondary">Una sola inversion.</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">Sin cuotas, sin sorpresas, sin contratos.</p>
            </div>

            <div class="mx-auto mt-12 max-w-md">
                <div class="card-glow pulse-glow rounded-2xl border-2 border-wc-accent bg-wc-bg p-8 text-center">
                    <span class="badge-shine inline-block rounded-full bg-wc-accent/10 px-4 py-1 text-xs font-semibold text-wc-accent">Precio especial Marzo 2026</span>

                    <div class="mt-6">
                        <span class="font-data text-5xl font-bold text-wc-text">$99.900</span>
                        <p class="mt-1 text-sm text-wc-text-tertiary">COP &middot; Pago unico &middot; 30 dias completos</p>
                    </div>

                    <ul class="mt-8 space-y-3 text-left">
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Entrenamiento Personalizado 1:1
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Guia de Nutricion para los 30 dias
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Guia de Habitos diarios
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Seguimiento durante todo el reto
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Dashboard RISE con tracking en tiempo real
                        </li>
                    </ul>

                    <a href="{{ route('planes') }}" class="btn-press pulse-glow mt-8 flex w-full items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                        Quiero unirme al RISE
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>

                    <p class="mt-4 text-xs text-wc-text-tertiary">Pago seguro Wompi &middot; SSL 256-bit &middot; Soporte incluido</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 8. URGENCY CTA (remastered — gradient bg, orbs, btn-press, badge-shine) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="relative overflow-hidden rounded-2xl border border-wc-accent/30 bg-wc-bg-tertiary p-10 sm:p-16">
                {{-- Gradient background --}}
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/10 via-transparent to-wc-accent/5"></div>
                {{-- Decorative orbs --}}
                <div aria-hidden="true" class="pointer-events-none absolute -left-16 -top-16 h-64 w-64 rounded-full bg-wc-accent/5 blur-3xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-wc-accent/8 blur-3xl"></div>
                <div class="relative text-center">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-5xl">CUPOS LIMITADOS</h2>
                    <p class="mt-4 text-lg text-wc-text-secondary">
                        Cierra el <span class="badge-shine font-semibold text-wc-accent">31 de Marzo 2026</span>
                    </p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-wc-text-tertiary">
                        No esperes al ultimo dia. Los cupos del RISE son limitados.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('planes') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                            Inscribirme ahora
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Countdown Alpine.js Component --}}
    <script>
        function countdown(deadline) {
            return {
                days: '00', hours: '00', minutes: '00', seconds: '00',
                start() {
                    const update = () => {
                        const now = new Date().getTime();
                        const end = new Date(deadline).getTime();
                        const diff = end - now;
                        if (diff <= 0) { this.days = '00'; this.hours = '00'; this.minutes = '00'; this.seconds = '00'; return; }
                        this.days = String(Math.floor(diff / 86400000)).padStart(2, '0');
                        this.hours = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                        this.minutes = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                        this.seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                    };
                    update();
                    setInterval(update, 1000);
                }
            };
        }
    </script>

</x-layouts.public>
