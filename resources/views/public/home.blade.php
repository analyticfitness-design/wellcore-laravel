<x-layouts.public>
    <x-slot:title>WellCore Fitness - Coaching 1:1 Basado en Ciencia</x-slot:title>
    <x-slot:description>Coaching fitness 1:1 basado en ciencia. Entrenamiento y nutricion personalizados. 94% adherencia. Sin milagros, solo resultados reales.</x-slot:description>

    {{-- Reading progress bar --}}
    <div class="scroll-progress"></div>

    {{-- ================================================================== --}}
    {{-- 1. RISE BANNER                                                     --}}
    {{-- ================================================================== --}}
    <div class="border-b border-wc-border bg-wc-accent/5">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2 sm:px-6 lg:px-8">
            <p class="text-xs text-wc-text-secondary">
                <span class="font-semibold text-wc-accent">Programa RISE</span> — 30 dias de transformacion guiada.
            </p>
            <a href="{{ route('reto-rise') }}" class="btn-press rounded bg-wc-accent px-3 py-1 text-xs font-semibold text-white hover:bg-wc-accent-hover">UNIRME</a>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- 2. HERO                                                            --}}
    {{-- ================================================================== --}}
    <section class="hero-gradient relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
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
                {{-- Left --}}
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-4 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Primera plataforma LATAM &middot; Nivel Internacional &middot; 2026</span>
                    </div>

                    <h1 class="mt-6 font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-8xl">
                        SIN MILAGROS,<br>
                        <span class="italic text-wc-text-secondary">solo</span>
                        <span class="text-gradient-accent font-bold text-wc-accent">CIENCIA</span>
                    </h1>

                    <p class="mt-6 max-w-xl text-lg text-wc-text-secondary">
                        La primera plataforma de coaching fitness en Latinoamerica con nivel internacional. Entrenamiento y nutricion 1:1 basados en ciencia real, creada por y para latinos.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-6" data-animate="fadeIn">
                        <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                            <span class="counter-highlight font-data text-lg font-bold text-wc-accent" data-counter="94" data-counter-suffix="%">94%</span> Adherencia
                        </div>
                        <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                            <span class="counter-highlight font-data text-lg font-bold text-wc-accent">1:1</span> Coaching
                        </div>
                        <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                            <span class="counter-highlight font-data text-lg font-bold text-wc-accent" data-counter="100" data-counter-suffix="%">100%</span> Personalizado
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('inscripcion') }}" class="pulse-glow btn-press inline-flex w-full items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover sm:w-auto">
                            Comenzar Ahora
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <a href="{{ route('planes') }}" class="btn-press inline-flex items-center justify-center rounded-full px-8 py-3.5 text-base font-semibold text-wc-text hover:bg-wc-bg-secondary">
                            Ver Planes
                        </a>
                    </div>

                    <p class="mt-3 text-xs text-wc-text-tertiary">Sin tarjeta &middot; 100% sin compromiso</p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Esencial <span class="font-data font-semibold text-wc-text">$299k</span></span>
                        <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Metodo <span class="font-data font-semibold text-wc-text">$399k</span></span>
                        <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Elite <span class="font-data font-semibold text-wc-text">$549k</span></span>
                    </div>
                </div>

                {{-- Right — Dashboard Mockup --}}
                <div class="hidden lg:block">
                    <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-3 w-3 rounded-full bg-red-500"></span>
                            <span class="h-3 w-3 rounded-full bg-yellow-500"></span>
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">app.wellcorefitness.com</span>
                            </div>
                        </div>
                        {{-- Dashboard content --}}
                        <div class="flex">
                            {{-- Sidebar placeholder --}}
                            <div class="w-14 shrink-0 border-r border-wc-border bg-wc-bg-secondary p-2">
                                <div class="mb-3 h-8 w-8 rounded-lg bg-wc-accent/20"></div>
                                <div class="space-y-2">
                                    <div class="h-6 w-6 mx-auto rounded bg-wc-bg-tertiary"></div>
                                    <div class="h-6 w-6 mx-auto rounded bg-wc-accent/10"></div>
                                    <div class="h-6 w-6 mx-auto rounded bg-wc-bg-tertiary"></div>
                                    <div class="h-6 w-6 mx-auto rounded bg-wc-bg-tertiary"></div>
                                </div>
                            </div>
                            {{-- Main area --}}
                            <div class="flex-1 p-4">
                                {{-- Stats row --}}
                                <div class="grid grid-cols-4 gap-2">
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2 text-center">
                                        <p class="font-data text-lg font-bold text-wc-accent">1,250</p>
                                        <p class="text-[10px] text-wc-text-tertiary">XP Total</p>
                                    </div>
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2 text-center">
                                        <p class="font-data text-lg font-bold text-emerald-400">94%</p>
                                        <p class="text-[10px] text-wc-text-tertiary">Adherencia</p>
                                    </div>
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2 text-center">
                                        <p class="font-data text-lg font-bold text-wc-text">18</p>
                                        <p class="text-[10px] text-wc-text-tertiary">Semana</p>
                                    </div>
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2 text-center">
                                        <p class="font-data text-lg font-bold text-amber-400">5</p>
                                        <p class="text-[10px] text-wc-text-tertiary">Racha dias</p>
                                    </div>
                                </div>
                                {{-- Progress bar --}}
                                <div class="mt-3 rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-wc-text-secondary">Progreso semanal</span>
                                        <span class="font-data font-semibold text-wc-text">4/5</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full rounded-full bg-wc-bg-secondary">
                                        <div class="h-2 w-4/5 rounded-full bg-wc-accent"></div>
                                    </div>
                                </div>
                                {{-- Mission card --}}
                                <div class="mt-3 rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                                        <span class="text-xs font-semibold text-wc-accent">Mision de hoy</span>
                                    </div>
                                    <p class="mt-1 text-xs text-wc-text-secondary">3 series de sentadilla — antes de las 8pm</p>
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
    {{-- 3. SOCIAL PROOF BAR                                                --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" data-animate="fadeIn">
            <p class="text-center text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Basado en ciencia, no en promesas</p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">
                <span>NSCA</span>
                <span class="text-wc-border">|</span>
                <span>ISSN</span>
                <span class="text-wc-border">|</span>
                <span>ACSM</span>
                <span class="text-wc-border">|</span>
                <span>Precision Nutrition</span>
                <span class="text-wc-border">|</span>
                <span>PubMed &middot; Evidencia</span>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 4. WHY WELLCORE                                                    --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Por que WellCore</p>
            <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">RESULTADOS QUE SE SOSTIENEN.</h2>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">No contamos con trucos rapidos. Contamos con metodo, seguimiento y ciencia aplicada a tu cuerpo. Cada decision tiene una razon.</p>

            {{-- Stats grid --}}
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-4" data-animate="fadeIn">
                <div class="scroll-reveal card-hover-lift card-glow rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent"><span data-counter="94" data-counter-suffix="%">0%</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">Adherencia</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Nuestros clientes mantienen el plan semana a semana.</p>
                </div>
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-text"><span data-counter="20" data-counter-suffix="+">0</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">Clientes</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Activos con planes personalizados en ejecucion.</p>
                </div>
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-text"><span data-counter="8" data-counter-suffix="sem">0</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">Resultados</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Cambios visibles y medibles en composicion corporal.</p>
                </div>
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-text"><span data-counter="100" data-counter-suffix="%">0%</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">Personalizado</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Nada generico. Tu plan es tuyo y solo tuyo.</p>
                </div>
            </div>

            {{-- 3 Pillars --}}
            <div class="stagger-grid mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach([
                    ['01', 'Ciencia, no intuicion', 'Cada decision de entrenamiento y nutricion esta respaldada por evidencia publicada. Sin mitos, sin dietas milagro.', 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5', 100],
                    ['02', 'Atencion 1:1 real', 'Tu coach te conoce por nombre, sabe tu historial y responde tus dudas. No eres un numero en una base de datos.', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z', 200],
                    ['03', 'Entiendes el por que', 'Te explicamos la logica detras de cada indicacion. Aprendes a entrenar y comer bien de por vida.', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342', 300],
                ] as [$num, $title, $desc, $iconPath, $delay])
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-8 transition-colors hover:border-wc-accent/30" data-animate="fadeInUp" data-animate-delay="{{ $delay }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
                        </div>
                        <span class="font-data text-2xl font-bold text-wc-accent/20">{{ $num }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 5. COMMUNITY                                                       --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Comunidad</p>
            <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">NO ENTRENAS SOLO. JAMAS.</h2>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">Una comunidad real de personas comprometidas con su transformacion. Retos, logros y soporte mutuo — no likes vacios.</p>

            {{-- Community stats --}}
            <div class="mt-8 flex flex-wrap gap-6" data-animate="fadeIn">
                <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                    <span class="counter-highlight font-data text-lg font-bold text-wc-accent" data-counter="20" data-counter-suffix="+">0</span> Miembros
                </div>
                <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                    <span class="counter-highlight font-data text-lg font-bold text-wc-accent" data-counter="3">0</span> Retos activos
                </div>
                <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                    <span class="counter-highlight font-data text-lg font-bold text-wc-accent" data-counter="94" data-counter-suffix="%">0%</span> Adherencia
                </div>
                <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                    <span class="counter-highlight font-data text-lg font-bold text-wc-accent" data-counter="500" data-counter-suffix="+">0</span> Logros
                </div>
            </div>

            {{-- 3 feature cards --}}
            <div class="stagger-grid mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                {{-- Activity Feed --}}
                <div class="scroll-reveal card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-6" data-animate="fadeInUp" data-animate-delay="100">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        <h3 class="text-base font-semibold text-wc-text">Actividad en vivo</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center gap-3 rounded-lg bg-wc-bg p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-400/10">
                                <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-wc-text">Maria completo su check-in</p>
                                <p class="text-[10px] text-wc-text-tertiary">Hace 12 min</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-wc-bg p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-400/10">
                                <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-2.77.672 6.023 6.023 0 0 1-2.77-.672" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-wc-text">Juan desbloqueo logro "Racha 7"</p>
                                <p class="text-[10px] text-wc-text-tertiary">Hace 1 hora</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-wc-bg p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-wc-text">Carlos subio al leaderboard</p>
                                <p class="text-[10px] text-wc-text-tertiary">Hace 3 horas</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Challenges --}}
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-6" data-animate="fadeInUp" data-animate-delay="200">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                        <h3 class="text-base font-semibold text-wc-text">Retos y desafios</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-wc-text">Reto 30 dias sin azucar</p>
                                <span class="rounded-full bg-emerald-400/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Activo</span>
                            </div>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">14 participantes &middot; 18 dias restantes</p>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-wc-text">Reto RISE — Transformacion</p>
                                <span class="rounded-full bg-emerald-400/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Activo</span>
                            </div>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">22 participantes &middot; 24 dias restantes</p>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-wc-text">Reto Fuerza 5x5</p>
                                <span class="rounded-full bg-amber-400/10 px-2 py-0.5 text-[10px] font-semibold text-amber-400">Proximo</span>
                            </div>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">Inicia en 5 dias &middot; 8 inscritos</p>
                        </div>
                    </div>
                </div>

                {{-- Gamification --}}
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-6" data-animate="fadeInUp" data-animate-delay="300">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                        <h3 class="text-base font-semibold text-wc-text">Gamificacion</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <p class="text-xs font-medium text-wc-text">Sistema de XP</p>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">Gana puntos por cada check-in, entrenamiento y logro desbloqueado.</p>
                            <div class="mt-2 flex gap-1">
                                <span class="rounded bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent">+50 XP check-in</span>
                                <span class="rounded bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent">+100 XP racha</span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <p class="text-xs font-medium text-wc-text">Insignias y logros</p>
                            <div class="mt-2 flex gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-400/10 text-xs">
                                    <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-2.77.672 6.023 6.023 0 0 1-2.77-.672" /></svg>
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-400/10 text-xs">
                                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-wc-accent/10 text-xs">
                                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <p class="text-xs font-medium text-wc-text">Leaderboard semanal</p>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">Compite con la comunidad. Top 3 ganan reconocimiento.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 6. COMO FUNCIONA                                                   --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Como funciona</p>
            <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">TU TRANSFORMACION EN 4 FASES.</h2>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">Un proceso estructurado donde cada semana tiene un proposito. Sin improvisacion, sin recetas genericas.</p>

            <div class="stagger-grid mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['01', 'Diagnostico', 'Semana 1', 'Evaluacion completa: historial, habitos, metas, metabolismo y estilo de vida. Nada se asume.', 100],
                    ['02', 'Plan personalizado', 'Semana 1-2', 'Diseno del protocolo de entrenamiento y nutricion hecho a tu medida, con la logica explicada.', 200],
                    ['03', 'Ejecucion', 'Semanas 2-8', 'Acompanamiento semanal con check-ins, ajustes en tiempo real y soporte directo de tu coach.', 300],
                    ['04', 'Evolucion', 'Continuo', 'Ajustes basados en datos reales de tu progreso. El plan evoluciona contigo permanentemente.', 400],
                ] as [$num, $title, $time, $desc, $delay])
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-6 transition-colors hover:border-wc-accent/30" data-animate="fadeInUp" data-animate-delay="{{ $delay }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                            <span class="font-data text-sm font-bold text-wc-accent">{{ $num }}</span>
                        </div>
                        <span class="text-xs font-medium text-wc-text-tertiary">{{ $time }}</span>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-wc-text">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ $desc }}</p>
                </div>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('proceso') }}" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                    Ver proceso completo
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 7. PLANS                                                           --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="scaleIn">
            <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Planes</p>
            <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">ELIGE TU PLAN</h2>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">Sin contratos de permanencia. Cancela cuando quieras.</p>

            <div class="stagger-grid mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                {{-- ESENCIAL --}}
                <div class="scroll-reveal card-hover-lift flex flex-col rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-animate="fadeInUp" data-animate-delay="100">
                    <div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">ESENCIAL</h3>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="font-data text-4xl font-bold text-wc-text">$299,000</span>
                            <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                        </div>
                    </div>
                    <div class="mt-6 flex-1 space-y-3">
                        @foreach([
                            [true, 'Entrenamiento personalizado desde cero'],
                            [true, 'Portal de cliente 24/7'],
                            [true, 'Evaluacion inicial + diagnostico'],
                            [true, 'Biblioteca de ejercicios con video'],
                            [true, 'Seguimiento de metricas y progreso'],
                            [true, 'Mediciones corporales + fotos'],
                            [true, 'Comunidad y chat grupal'],
                            [true, 'Ajuste mensual del programa'],
                            [true, 'Soporte por mensaje — respuesta 48h'],
                            [false, 'Nutricion personalizada'],
                            [false, 'Check-in semanal en vivo'],
                        ] as [$included, $feature])
                        <div class="flex items-start gap-2">
                            @if($included)
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                <span class="text-sm text-wc-text-secondary">{{ $feature }}</span>
                            @else
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                <span class="text-sm text-wc-text-tertiary line-through">{{ $feature }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('inscripcion') }}?plan=esencial" class="btn-press mt-8 inline-flex w-full items-center justify-center rounded-lg border border-wc-border px-8 py-3 text-base font-medium text-wc-text hover:bg-wc-bg-secondary">
                        Comenzar Esencial
                    </a>
                </div>

                {{-- METODO --}}
                <div class="scroll-reveal card-hover-lift card-glow relative flex flex-col rounded-xl border-2 border-wc-accent bg-wc-bg-tertiary p-8" data-animate="fadeInUp" data-animate-delay="200">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="rounded-full bg-wc-accent px-4 py-1 text-xs font-semibold text-white">Mejor Valor</span>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">METODO</h3>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="font-data text-4xl font-bold text-wc-text">$399,000</span>
                            <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                        </div>
                    </div>
                    <div class="mt-6 flex-1 space-y-3">
                        @foreach([
                            [true, 'Todo lo del plan Esencial +'],
                            [true, 'Nutricion 100% personalizada'],
                            [true, 'Macros y calorias ajustadas'],
                            [true, 'Recetas adaptadas'],
                            [true, 'Guia de habitos y estilo de vida'],
                            [true, 'Seguimiento de sueño y estres'],
                            [true, 'Reporte mensual de progreso'],
                            [true, 'Ajuste quincenal'],
                            [true, 'Soporte — respuesta 24h'],
                            [false, 'Check-in semanal en vivo'],
                            [false, 'Videollamada mensual'],
                        ] as [$included, $feature])
                        <div class="flex items-start gap-2">
                            @if($included)
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                <span class="text-sm text-wc-text-secondary">{{ $feature }}</span>
                            @else
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                <span class="text-sm text-wc-text-tertiary line-through">{{ $feature }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('inscripcion') }}?plan=metodo" class="btn-press mt-8 inline-flex w-full items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                        Comenzar Metodo
                    </a>
                </div>

                {{-- ELITE --}}
                <div class="scroll-reveal card-hover-lift relative flex flex-col rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-animate="fadeInUp" data-animate-delay="300">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="rounded-full border border-wc-accent bg-wc-accent/10 px-4 py-1 text-xs font-semibold text-wc-accent">SOLO 5 CUPOS</span>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">ELITE</h3>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="font-data text-4xl font-bold text-wc-text">$549,000</span>
                            <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                        </div>
                    </div>
                    <div class="mt-6 flex-1 space-y-3">
                        @foreach([
                            [true, 'Todo lo del plan Metodo +'],
                            [true, 'Check-in semanal en vivo'],
                            [true, 'Videollamada mensual'],
                            [true, 'Ajuste semanal'],
                            [true, 'Linea directa WhatsApp'],
                            [true, 'Analisis composicion corporal'],
                            [true, 'Estrategia suplementacion'],
                            [true, 'Ciclo hormonal personalizado'],
                            [true, 'Bloodwork — analisis laboratorio'],
                            [true, 'Plan de viaje y contingencia'],
                            [true, 'Soporte prioritario — respuesta 8h'],
                        ] as [$included, $feature])
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('inscripcion') }}?plan=elite" class="btn-press mt-8 inline-flex w-full items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                        Comenzar Elite
                    </a>
                </div>
            </div>

            <p class="mt-8 text-center text-xs text-wc-text-tertiary">Cancelacion sin penalizacion. Reembolsos evaluados antes de entregar el plan.</p>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 8. RESULTS / TESTIMONIALS                                          --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Casos Reales</p>
            <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">RESULTADOS REALES</h2>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">Sin filtros, sin edicion. Clientes reales con planes WellCore que transformaron su cuerpo y sus habitos.</p>

            <div class="stagger-grid mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach([
                    ['ML', 'Maria L.', '-12kg', '4 meses', 'Plan Metodo', 'Por primera vez entendi por que hacia cada cosa. Eso lo cambio todo. No fue una dieta, fue un cambio de mentalidad.', 100],
                    ['JC', 'Juan C.', '+8kg musculo', '6 meses', 'Plan Elite', 'Sin suplementos raros, solo entrenamiento inteligente y buena nutricion. El seguimiento semanal marco la diferencia.', 200],
                    ['AP', 'Andrea P.', '-9kg grasa', '12 semanas', 'Plan Metodo', 'La guia de habitos fue lo que cambio todo. Ahora entreno por conviccion, no por obligacion.', 300],
                ] as [$initials, $name, $stat, $duration, $plan, $quote, $delay])
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-8" data-animate="fadeInUp" data-animate-delay="{{ $delay }}">
                    {{-- Before/After interactive slider --}}
                    <x-before-after-slider height="h-44" />
                    {{-- Stat badges --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">{{ $stat }}</span>
                        <span class="rounded-full bg-wc-bg-tertiary px-3 py-1 text-xs text-wc-text-secondary">{{ $duration }}</span>
                        <span class="rounded-full bg-wc-bg-tertiary px-3 py-1 text-xs text-wc-text-secondary">{{ $plan }}</span>
                    </div>
                    {{-- Stars --}}
                    <div class="mt-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    {{-- Quote --}}
                    <p class="mt-4 text-sm text-wc-text-secondary">"{{ $quote }}"</p>
                    {{-- Author --}}
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">{{ $initials }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">{{ $name }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ $stat }} &middot; {{ $plan }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 9. COACHES                                                         --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                {{-- Left --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Trabaja con nosotros</p>
                    <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">ERES COACH? UNETE AL METODO.</h2>
                    <p class="mt-4 max-w-xl text-sm text-wc-text-tertiary">Buscamos coaches con formacion real, comprometidos con la evidencia y dispuestos a entregar resultados medibles. Si te importa la ciencia mas que el marketing, queremos conocerte.</p>

                    <div class="mt-6 flex flex-wrap gap-4 text-sm text-wc-text-secondary">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Pasion por la evidencia
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Compromiso
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Mentalidad sin atajos
                        </span>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('coaches') }}" class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                            Postulate
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>

                {{-- Right — Coach Portal Mockup --}}
                <div class="hidden lg:block" data-animate="slideInRight">
                    <div class="rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-3 w-3 rounded-full bg-red-500"></span>
                            <span class="h-3 w-3 rounded-full bg-yellow-500"></span>
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">coach.wellcorefitness.com</span>
                            </div>
                        </div>
                        {{-- Coach dashboard --}}
                        <div class="p-4">
                            {{-- Stats row --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-accent">18</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Clientes</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-emerald-400">$5.7M</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Mes</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-text">91%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Adherencia</p>
                                </div>
                            </div>
                            {{-- Client list --}}
                            <div class="mt-3 rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="text-xs font-semibold text-wc-text">Clientes activos</p>
                                <div class="mt-2 space-y-2">
                                    @foreach([
                                        ['Maria G.', '88%', 'text-amber-400'],
                                        ['Juan R.', '94%', 'text-emerald-400'],
                                        ['Andrea M.', '100%', 'text-emerald-400'],
                                    ] as [$clientName, $clientAdherence, $color])
                                    <div class="flex items-center justify-between rounded bg-wc-bg px-2 py-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-5 w-5 rounded-full bg-wc-accent/10"></div>
                                            <span class="text-[11px] text-wc-text-secondary">{{ $clientName }}</span>
                                        </div>
                                        <span class="font-data text-[11px] font-semibold {{ $color }}">{{ $clientAdherence }}</span>
                                    </div>
                                    @endforeach
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
    {{-- 10. BLOG PREVIEW                                                   --}}
    {{-- ================================================================== --}}
    @php
        $articles = array_slice(\App\Http\Controllers\BlogController::getArticles(), 0, 3);
    @endphp
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Conocimiento</p>
                    <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">APRENDE CON WELLCORE</h2>
                </div>
                <a href="{{ route('blog.index') }}" class="hidden items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover sm:inline-flex">
                    Ver Todos
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>

            <div class="stagger-grid mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach($articles as $index => $article)
                <a href="{{ route('blog.show', $article['slug']) }}" class="scroll-reveal card-hover-lift group rounded-xl border border-wc-border bg-wc-bg transition-colors hover:border-wc-accent/30" data-animate="fadeInUp" data-animate-delay="{{ ($index + 1) * 100 }}">
                    {{-- Image placeholder --}}
                    <div class="relative h-48 overflow-hidden rounded-t-xl bg-gradient-to-br from-wc-accent/10 via-wc-bg-tertiary to-wc-bg-secondary">
                        <div class="absolute left-4 top-4">
                            <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">{{ $article['category'] }}</span>
                        </div>
                    </div>
                    {{-- Content --}}
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-wc-text group-hover:text-wc-accent">{{ $article['title'] }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm text-wc-text-secondary">{{ $article['excerpt'] }}</p>
                        <div class="mt-4 flex items-center gap-3 text-xs text-wc-text-tertiary">
                            <span>{{ \Carbon\Carbon::parse($article['date'])->format('d M Y') }}</span>
                            <span>&middot;</span>
                            <span>{{ $article['reading_time'] }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-8 text-center sm:hidden">
                <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                    Ver Todos
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 11. FAQ                                                            --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg" x-data="{ active: null }">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">FAQ</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PREGUNTAS FRECUENTES</h2>
            </div>

            <div class="mx-auto mt-12 max-w-3xl divide-y divide-wc-border">
                @foreach([
                    ['Necesito experiencia previa?', 'No. Trabajamos con todos los niveles, desde personas que nunca han hecho ejercicio hasta atletas avanzados. El plan se construye desde cero segun tu historial, tu condicion actual y tus metas especificas.'],
                    ['Como funciona el seguimiento?', 'Cada semana nos envias tu check-in: peso, fotos opcionales, nivel de energia, como se sintio el entrenamiento y cualquier novedad. Con esa informacion ajustamos el plan para la semana siguiente.'],
                    ['Necesito comprar suplementos?', 'No. WellCore no vende suplementos ni gana comision por recomendarlos. Los resultados se logran con entrenamiento, nutricion y descanso, no con polvos magicos.'],
                    ['Puedo cancelar cuando quiera?', 'Si. No hay contratos de permanencia ni cargos ocultos. Cancelas con un mensaje antes de tu siguiente ciclo de facturacion.'],
                    ['En cuanto tiempo vere resultados?', 'Depende de tu punto de partida y tu consistencia. En general: 4-6 semanas para notar cambios en energia y fuerza; 8-12 semanas para cambios visuales claros.'],
                    ['Puedo entrenar en casa?', 'Si. El programa se adapta completamente al equipamiento disponible: gimnasio completo, home gym con mancuernas o solo peso corporal.'],
                    ['Que pasa si viajo o tengo un evento?', 'Tu coach ajusta el plan con anticipacion. Tenemos protocolos para viajes, eventos sociales y periodos de alta carga laboral. Nada se rompe por una semana diferente.'],
                    ['Los precios incluyen IVA?', 'Si. Todos los precios publicados son finales. No hay cargos adicionales, sorpresas ni letras pequenas.'],
                ] as $index => [$question, $answer])
                <div>
                    <button x-on:click="active = active === {{ $index }} ? null : {{ $index }}" class="flex w-full items-center justify-between py-5 text-left">
                        <span class="text-sm font-semibold text-wc-text">{{ $question }}</span>
                        <svg class="ml-4 h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200" :class="{ 'rotate-180': active === {{ $index }} }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="active === {{ $index }}" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $answer }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 12. FINAL CTA                                                      --}}
    {{-- ================================================================== --}}
    <section class="gradient-animated bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="scaleIn">
            <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg p-10 sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
                <div class="relative text-center">
                    <div class="inline-flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-4 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Lanzamiento 2026 — Acceso Fundador</span>
                    </div>
                    <h2 class="mt-6 font-display text-3xl tracking-wide text-wc-text sm:text-5xl">LISTO PARA TRANSFORMARTE?</h2>
                    <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">La primera plataforma de coaching fitness hecha en LATAM con estandares internacionales. Creemos que los latinos merecen acceso a coaching de elite sin barreras.</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('planes') }}" class="pulse-glow btn-press inline-flex items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                            Ver Planes
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <a href="{{ route('inscripcion') }}" class="btn-press inline-flex items-center justify-center rounded-lg border border-wc-border px-8 py-3 text-base font-medium text-wc-text hover:bg-wc-bg-secondary">
                            Consulta Gratuita
                        </a>
                    </div>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-wc-text-tertiary">
                        <span>Seguimiento 1:1 real</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span>Sin contratos forzados</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span>Acceso fundador</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
