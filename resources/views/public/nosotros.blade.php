<x-layouts.public>
    <x-slot:title>{{ __('nosotros.title') }}</x-slot:title>

    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'WellCore Fitness',
        'url' => url('/'),
        'logo' => url('/images/logo-dark.png'),
        'description' => 'WellCore Fitness es la primera plataforma de coaching fitness online 1:1 de Latinoamerica basada en ciencia. Fundada en Bucaramanga, Colombia, servimos a clientes en toda la region.',
        'foundingDate' => '2021',
        'foundingLocation' => [
            '@type' => 'Place',
            'name' => 'Bucaramanga, Santander, Colombia',
        ],
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Bucaramanga',
            'addressRegion' => 'Santander',
            'addressCountry' => 'CO',
        ],
        'areaServed' => [
            '@type' => 'Place',
            'name' => 'Latinoamerica',
        ],
        'sameAs' => [
            'https://www.instagram.com/wellcore.fitness/',
            'https://www.youtube.com/@Wellcorefitness',
        ],
        'knowsAbout' => [
            'Entrenamiento de fuerza',
            'Nutricion deportiva',
            'Coaching fitness personalizado',
            'Transformacion corporal',
        ],
    ]" />

    {{-- 1. HERO --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.1"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8" data-animate="fadeInUp">
            <h1 class="font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">
                <span class="text-gradient-accent">{{ __('nosotros.hero_h1') }}</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary sm:text-xl">
                {{ __('nosotros.hero_sub') }}
            </p>
            {{-- Animated accent divider --}}
            <div class="mx-auto mt-8 flex justify-center">
                <div class="section-divider-hero h-1 w-16 rounded-full bg-wc-accent transition-all duration-700 ease-out"
                     x-data
                     x-intersect.once="$el.style.width = '80px'"></div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- 2. MISSION / VISION --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('nosotros.essence_h2') }}</h2>
                <p class="mt-2 text-wc-text-secondary">{{ __('nosotros.essence_sub') }}</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 lg:grid-cols-2">
                {{-- Mision --}}
                <div class="card-hover-lift card-glow scroll-reveal-left rounded-xl border border-wc-border bg-wc-bg-tertiary p-8"
                     data-animate="slideInLeft">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.047 8.287 8.287 0 009 9.601a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.468 5.99 5.99 0 00-1.925 3.547 5.975 5.975 0 01-2.133-1.001A3.75 3.75 0 0012 18z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">{{ __('nosotros.mision_h3') }}</h3>
                    </div>
                    <p class="mt-6 leading-relaxed text-wc-text-secondary">
                        {{ __('nosotros.mision_p1') }}
                    </p>
                    <p class="mt-4 leading-relaxed text-wc-text-secondary">
                        {{ __('nosotros.mision_p2') }}
                    </p>
                </div>

                {{-- Vision --}}
                <div class="card-hover-lift card-glow scroll-reveal-right rounded-xl border border-wc-border bg-wc-bg-tertiary p-8"
                     data-animate="slideInRight">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">{{ __('nosotros.vision_h3') }}</h3>
                    </div>
                    <p class="mt-6 leading-relaxed text-wc-text-secondary">
                        {{ __('nosotros.vision_p1') }}
                    </p>
                    <p class="mt-4 leading-relaxed text-wc-text-secondary">
                        {{ __('nosotros.vision_p2') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- 3. TEAM --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('nosotros.team_h2') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('nosotros.team_sub') }}</p>
            </div>

            {{-- Founder - Full Feature Card --}}
            <div class="card-hover-lift mt-14 rounded-xl border border-wc-border bg-wc-bg p-8 transition-all duration-300 hover:border-wc-accent/40 sm:p-10"
                 data-animate="scaleIn">
                <div class="flex flex-col items-center gap-8 md:flex-row md:items-start">
                    <div class="flex-shrink-0">
                        <div class="flex h-28 w-28 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-accent/10 transition-all duration-300 hover:shadow-lg hover:shadow-wc-accent/20">
                            <span class="font-display text-4xl text-wc-accent">DE</span>
                        </div>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">{{ __('nosotros.founder_name') }}</h3>
                        <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('nosotros.founder_role') }}</p>
                        <p class="mt-4 leading-relaxed text-wc-text-secondary">
                            {{ __('nosotros.founder_bio') }}
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-3 md:justify-start">
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">{{ __('nosotros.founder_tag1') }}</span>
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">{{ __('nosotros.founder_tag2') }}</span>
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">{{ __('nosotros.founder_tag3') }}</span>
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">{{ __('nosotros.founder_tag4') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Other Coaches - Compact Cards --}}
            <div class="stagger-grid mt-8 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Coach 2: Nutricionista --}}
                <div x-data="{ hovered: false }"
                     @mouseenter="hovered = true"
                     @mouseleave="hovered = false"
                     class="card-hover-lift relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg p-8 text-center"
                     data-animate="fadeInUp"
                     data-animate-delay="100">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-wc-accent/30 bg-wc-accent/10">
                        <span class="font-display text-2xl text-wc-accent">NC</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.coach2_name') }}</h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('nosotros.coach2_role') }}</p>
                    <p class="mt-4 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('nosotros.coach2_bio') }}
                    </p>
                    {{-- Hover overlay --}}
                    <div class="absolute inset-0 flex items-end justify-center rounded-xl bg-gradient-to-t from-wc-accent/10 to-transparent pb-6 opacity-0 transition-opacity duration-300"
                         :class="{ 'opacity-100': hovered }">
                        <span class="text-xs font-semibold uppercase tracking-wider text-wc-accent">{{ __('nosotros.coach2_hover') }}</span>
                    </div>
                </div>

                {{-- Coach 3: Strength Coach --}}
                <div x-data="{ hovered: false }"
                     @mouseenter="hovered = true"
                     @mouseleave="hovered = false"
                     class="card-hover-lift relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg p-8 text-center"
                     data-animate="fadeInUp"
                     data-animate-delay="200">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-wc-accent/30 bg-wc-accent/10">
                        <span class="font-display text-2xl text-wc-accent">SC</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.coach3_name') }}</h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('nosotros.coach3_role') }}</p>
                    <p class="mt-4 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('nosotros.coach3_bio') }}
                    </p>
                    {{-- Hover overlay --}}
                    <div class="absolute inset-0 flex items-end justify-center rounded-xl bg-gradient-to-t from-wc-accent/10 to-transparent pb-6 opacity-0 transition-opacity duration-300"
                         :class="{ 'opacity-100': hovered }">
                        <span class="text-xs font-semibold uppercase tracking-wider text-wc-accent">{{ __('nosotros.coach3_hover') }}</span>
                    </div>
                </div>

                {{-- Coach 4: Mindset Coach --}}
                <div x-data="{ hovered: false }"
                     @mouseenter="hovered = true"
                     @mouseleave="hovered = false"
                     class="card-hover-lift relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg p-8 text-center"
                     data-animate="fadeInUp"
                     data-animate-delay="300">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-wc-accent/30 bg-wc-accent/10">
                        <span class="font-display text-2xl text-wc-accent">MC</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.coach4_name') }}</h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('nosotros.coach4_role') }}</p>
                    <p class="mt-4 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('nosotros.coach4_bio') }}
                    </p>
                    {{-- Hover overlay --}}
                    <div class="absolute inset-0 flex items-end justify-center rounded-xl bg-gradient-to-t from-wc-accent/10 to-transparent pb-6 opacity-0 transition-opacity duration-300"
                         :class="{ 'opacity-100': hovered }">
                        <span class="text-xs font-semibold uppercase tracking-wider text-wc-accent">{{ __('nosotros.coach4_hover') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- 4. TIMELINE 2024-2026 --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('nosotros.history_h2') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('nosotros.history_sub') }}</p>
            </div>

            <div class="relative mx-auto mt-14 max-w-3xl">
                {{-- Vertical Line with scroll-driven drawing --}}
                <div class="absolute left-4 top-0 h-full w-px bg-wc-border sm:left-1/2 sm:-translate-x-px">
                    <div class="line-draw-scroll h-full w-full origin-top bg-wc-accent/40 transition-transform duration-1000"></div>
                </div>

                {{-- 2024 Q1 — odd: slideInLeft --}}
                <div class="scroll-reveal relative mb-12 flex items-start gap-6 sm:gap-0" data-animate="slideInLeft">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12 sm:text-right">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl1_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl1_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl1_desc') }}
                        </p>
                    </div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 cursor-pointer items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg transition-transform duration-300 hover:scale-110 sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:hidden">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl1_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl1_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl1_desc') }}
                        </p>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-12"></div>
                </div>

                {{-- 2024 Q3 — even: slideInRight --}}
                <div class="scroll-reveal relative mb-12 flex items-start gap-6 sm:gap-0" data-animate="slideInRight">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12"></div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 cursor-pointer items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg transition-transform duration-300 hover:scale-110 sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:w-1/2 sm:pl-12">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl2_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl2_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl2_desc') }}
                        </p>
                    </div>
                </div>

                {{-- 2025 Q1 — odd: slideInLeft --}}
                <div class="scroll-reveal relative mb-12 flex items-start gap-6 sm:gap-0" data-animate="slideInLeft">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12 sm:text-right">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl3_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl3_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl3_desc') }}
                        </p>
                    </div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 cursor-pointer items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg transition-transform duration-300 hover:scale-110 sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:hidden">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl3_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl3_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl3_desc') }}
                        </p>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-12"></div>
                </div>

                {{-- 2025 Q3 — even: slideInRight --}}
                <div class="scroll-reveal relative mb-12 flex items-start gap-6 sm:gap-0" data-animate="slideInRight">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12"></div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 cursor-pointer items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg transition-transform duration-300 hover:scale-110 sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:w-1/2 sm:pl-12">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl4_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl4_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl4_desc') }}
                        </p>
                    </div>
                </div>

                {{-- 2026 Q1 — odd: slideInLeft — current/final milestone --}}
                <div class="scroll-reveal relative flex items-start gap-6 sm:gap-0" data-animate="slideInLeft">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12 sm:text-right">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl5_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl5_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl5_desc') }}
                        </p>
                    </div>
                    {{-- pulse-glow on the current/final dot --}}
                    <div class="pulse-glow relative z-10 flex h-8 w-8 flex-shrink-0 cursor-pointer items-center justify-center rounded-full border-2 border-wc-accent bg-wc-accent transition-transform duration-300 hover:scale-110 sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-white"></div>
                    </div>
                    <div class="flex-1 sm:hidden">
                        <span class="font-data text-sm font-semibold text-wc-accent">{{ __('nosotros.tl5_date') }}</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">{{ __('nosotros.tl5_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            {{ __('nosotros.tl5_desc') }}
                        </p>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-12"></div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- 5. STATS GRID --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('nosotros.stats_h2') }}</h2>
                <p class="mt-2 text-wc-text-secondary">{{ __('nosotros.stats_sub') }}</p>
            </div>

            <div class="stagger-grid mt-14 grid grid-cols-2 gap-8 lg:grid-cols-4">
                {{-- Clientes --}}
                <div class="text-center" data-animate="fadeInUp" data-animate-delay="100">
                    <p class="counter-highlight font-data text-4xl font-bold text-wc-accent sm:text-5xl"
                       data-counter="500" data-counter-suffix="+">500+</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">{{ __('nosotros.stat_clients') }}</p>
                </div>

                {{-- Paises --}}
                <div class="text-center" data-animate="fadeInUp" data-animate-delay="200">
                    <p class="counter-highlight font-data text-4xl font-bold text-wc-accent sm:text-5xl"
                       data-counter="5" data-counter-suffix="">5</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">{{ __('nosotros.stat_countries') }}</p>
                </div>

                {{-- Coaches --}}
                <div class="text-center" data-animate="fadeInUp" data-animate-delay="300">
                    <p class="counter-highlight font-data text-4xl font-bold text-wc-accent sm:text-5xl"
                       data-counter="15" data-counter-suffix="+">15+</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">{{ __('nosotros.stat_coaches') }}</p>
                </div>

                {{-- Adherencia --}}
                <div class="text-center" data-animate="fadeInUp" data-animate-delay="400">
                    <p class="counter-highlight font-data text-4xl font-bold text-wc-accent sm:text-5xl"
                       data-counter="94" data-counter-suffix="%">94%</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">{{ __('nosotros.stat_adherence') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- 6. VALUES GRID --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('nosotros.values_h2') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('nosotros.values_sub') }}</p>
            </div>

            <div class="stagger-grid mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                {{-- Ciencia --}}
                <div class="card-hover-lift scroll-reveal-scale rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center"
                     data-animate="scaleIn"
                     data-animate-delay="100">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.val1_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('nosotros.val1_desc') }}
                    </p>
                </div>

                {{-- Transparencia --}}
                <div class="card-hover-lift scroll-reveal-scale rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center"
                     data-animate="scaleIn"
                     data-animate-delay="200">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.val2_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('nosotros.val2_desc') }}
                    </p>
                </div>

                {{-- Personalizacion --}}
                <div class="card-hover-lift scroll-reveal-scale rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center"
                     data-animate="scaleIn"
                     data-animate-delay="300">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.val3_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('nosotros.val3_desc') }}
                    </p>
                </div>

                {{-- Comunidad --}}
                <div class="card-hover-lift scroll-reveal-scale rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center"
                     data-animate="scaleIn"
                     data-animate-delay="400">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.val4_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('nosotros.val4_desc') }}
                    </p>
                </div>

                {{-- Resultados --}}
                <div class="card-hover-lift scroll-reveal-scale rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center"
                     data-animate="scaleIn"
                     data-animate-delay="500">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('nosotros.val5_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('nosotros.val5_desc') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- 7. CTA --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        {{-- Decorative gradient orbs --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-32 -top-32 h-64 w-64 rounded-full bg-wc-accent/5 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 h-80 w-80 rounded-full bg-wc-accent/8 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/2 h-96 w-96 -translate-x-1/2 -translate-y-1/2 rounded-full bg-wc-accent/3 blur-3xl"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8" data-animate="fadeInUp">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('nosotros.cta_h2') }}</h2>
            <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">
                {{ __('nosotros.cta_sub') }}
            </p>
            <div class="mt-8">
                <a href="{{ route('planes') }}"
                   class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20 transition-all duration-200 hover:bg-wc-accent/90 hover:shadow-xl hover:shadow-wc-accent/30">
                    {{ __('nosotros.cta_button') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
