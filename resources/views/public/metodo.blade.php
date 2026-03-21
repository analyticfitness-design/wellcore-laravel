<x-layouts.public>
    <x-slot:title>El Metodo - Protocolo de Entrenamiento Basado en Evidencia | WellCore Fitness</x-slot:title>
    <x-slot:description>Protocolo cientifico de entrenamiento personalizado al 100%. 5 pilares basados en evidencia, seguimiento 1:1 con coach real. 87% adherencia promedio.</x-slot:description>

    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => 'WellCore Fitness — El Metodo',
        'url' => url('/metodo'),
        'description' => 'Protocolo cientifico de entrenamiento personalizado al 100%. 5 pilares basados en evidencia, seguimiento 1:1 con coach real.',
        'teaches' => [
            'Entrenamiento de fuerza basado en evidencia',
            'Nutricion personalizada y periodizacion calorica',
            'Habitos de recuperacion y manejo del estres',
            'Composicion corporal y seguimiento de progreso',
            'Psicologia del rendimiento y adherencia',
        ],
        'educationalCredentialAwarded' => 'Transformacion fisica medible con metodologia cientifica',
        'provider' => [
            '@type' => 'Organization',
            'name' => 'WellCore Fitness',
            'url' => url('/'),
        ],
        'offers' => [
            '@type' => 'Offer',
            'name' => 'Coaching 1:1 con El Metodo WellCore',
            'description' => 'Protocolo de 5 pilares: entrenamiento, nutricion, habitos, recuperacion y mentalidad.',
            'url' => url('/planes'),
            'priceCurrency' => 'COP',
            'price' => '299000',
        ],
    ]" />

    {{-- Hero Section --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.45"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36" data-animate="fadeInUp">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">{{ __('metodo.hero.label') }}</p>
                <h1 class="mt-4 font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-8xl">
                    <span class="text-gradient-accent">{{ __('metodo.hero.title') }}</span>
                </h1>
                <p class="mt-2 text-xl font-medium text-wc-text-secondary sm:text-2xl">
                    {{ __('metodo.hero.subtitle') }}
                </p>
                <p class="mx-auto mt-6 max-w-xl text-lg text-wc-text-secondary">
                    {{ __('metodo.hero.description') }}
                </p>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Stats Bar --}}
    <section class="border-y border-wc-border bg-wc-bg">
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-3">
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent">
                        <span class="counter-highlight" data-counter="87" data-counter-suffix="%">87%</span>
                    </p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">{{ __('metodo.stats.adherence') }}</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent">
                        <span class="counter-highlight" data-counter="12" data-counter-suffix=" sem">12 sem</span>
                    </p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">{{ __('metodo.stats.visible_results') }}</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent">1:1</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">{{ __('metodo.stats.attention') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Section 01: El Problema --}}
    <section class="scroll-reveal bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-4 flex items-center gap-3">
                <span class="font-data text-sm font-semibold text-wc-accent">01</span>
                <div class="h-px flex-1 bg-wc-border"></div>
            </div>
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('metodo.problem.title') }}</h2>
            <p class="mt-2 text-lg text-wc-text-secondary">{{ __('metodo.problem.subtitle') }}</p>

            <div class="mt-8 rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 sm:p-8">
                <p class="text-lg font-medium text-wc-text">
                    {!! __('metodo.problem.intro', ['percent' => '<span class="counter-highlight font-data text-2xl font-bold text-wc-accent" data-counter="80" data-counter-suffix="%">80%</span>']) !!}
                </p>
            </div>

            <div class="stagger-grid mt-12 grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- Failure Point 1 --}}
                <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-8" data-animate="fadeInUp" style="animation-delay: 100ms">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.problem.fp1.title') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.problem.fp1.description') }}
                    </p>
                    <div class="mt-5 border-t border-wc-border pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-wc-accent">{{ __('metodo.problem.solution_label') }}</p>
                        <p class="mt-1 text-sm font-medium text-wc-text">{{ __('metodo.problem.fp1.solution') }}</p>
                    </div>
                </div>

                {{-- Failure Point 2 --}}
                <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-8" data-animate="fadeInUp" style="animation-delay: 200ms">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.problem.fp2.title') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.problem.fp2.description') }}
                    </p>
                    <div class="mt-5 border-t border-wc-border pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-wc-accent">{{ __('metodo.problem.solution_label') }}</p>
                        <p class="mt-1 text-sm font-medium text-wc-text">{{ __('metodo.problem.fp2.solution') }}</p>
                    </div>
                </div>

                {{-- Failure Point 3 --}}
                <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-8" data-animate="fadeInUp" style="animation-delay: 300ms">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.problem.fp3.title') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.problem.fp3.description') }}
                    </p>
                    <div class="mt-5 border-t border-wc-border pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-wc-accent">{{ __('metodo.problem.solution_label') }}</p>
                        <p class="mt-1 text-sm font-medium text-wc-text">{{ __('metodo.problem.fp3.solution') }}</p>
                    </div>
                </div>
            </div>

            {{-- Supporting Stats --}}
            <div class="mt-14 grid grid-cols-2 gap-4 sm:grid-cols-5 sm:gap-6">
                <div class="rounded-xl border border-wc-border bg-wc-bg p-5 text-center">
                    <p class="font-data text-2xl font-bold text-wc-text">8/10</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('metodo.problem.stats.s1_label') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg p-5 text-center">
                    <p class="font-data text-2xl font-bold text-wc-text">
                        <span class="counter-highlight" data-counter="67" data-counter-suffix="%">67%</span>
                    </p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('metodo.problem.stats.s2_label') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg p-5 text-center">
                    <p class="font-data text-2xl font-bold text-wc-text">
                        <span class="counter-highlight" data-counter="54" data-counter-suffix="%">54%</span>
                    </p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('metodo.problem.stats.s3_label') }}</p>
                </div>
                <div class="rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center">
                    <p class="font-data text-2xl font-bold text-wc-accent">
                        <span class="counter-highlight" data-counter="87" data-counter-suffix="%">87%</span>
                    </p>
                    <p class="mt-1 text-xs text-wc-text-secondary">{{ __('metodo.problem.stats.s4_label') }}</p>
                </div>
                <div class="col-span-2 rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center sm:col-span-1">
                    <p class="font-data text-2xl font-bold text-wc-accent">3.2x</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">{{ __('metodo.problem.stats.s5_label') }}</p>
                </div>
            </div>
            <p class="mt-6 text-xs text-wc-text-tertiary">
                {{ __('metodo.problem.source') }}
            </p>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Section 02: Los 5 Pilares --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-4 flex items-center gap-3">
                <span class="font-data text-sm font-semibold text-wc-accent">02</span>
                <div class="h-px flex-1 bg-wc-border"></div>
            </div>
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('metodo.pillars.title') }}</h2>
            <p class="mt-2 text-lg text-wc-text-secondary">{{ __('metodo.pillars.subtitle') }}</p>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">
                {{ __('metodo.pillars.note') }}
            </p>

            <div class="stagger-grid mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- P01: Sobrecarga Progresiva --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow group rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" style="animation-delay: 100ms">
                    <div class="flex items-center gap-3">
                        <span class="font-data text-xs font-bold text-wc-accent">P01</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.pillars.p1.name') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.pillars.p1.description') }}
                    </p>
                </div>

                {{-- P02: Periodizacion Inteligente --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow group rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" style="animation-delay: 200ms">
                    <div class="flex items-center gap-3">
                        <span class="font-data text-xs font-bold text-wc-accent">P02</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.pillars.p2.name') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.pillars.p2.description') }}
                    </p>
                </div>

                {{-- P03: Nutricion de Precision --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow group rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" style="animation-delay: 300ms">
                    <div class="flex items-center gap-3">
                        <span class="font-data text-xs font-bold text-wc-accent">P03</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.pillars.p3.name') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.pillars.p3.description') }}
                    </p>
                </div>

                {{-- P04: Recuperacion Optimizada --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow group rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" style="animation-delay: 400ms">
                    <div class="flex items-center gap-3">
                        <span class="font-data text-xs font-bold text-wc-accent">P04</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.pillars.p4.name') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.pillars.p4.description') }}
                    </p>
                </div>

                {{-- P05: Adherencia Conductual --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow group rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" style="animation-delay: 500ms">
                    <div class="flex items-center gap-3">
                        <span class="font-data text-xs font-bold text-wc-accent">P05</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('metodo.pillars.p5.name') }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('metodo.pillars.p5.description') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Section 03: Comparativa --}}
    <section class="scroll-reveal bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-4 flex items-center gap-3">
                <span class="font-data text-sm font-semibold text-wc-accent">03</span>
                <div class="h-px flex-1 bg-wc-border"></div>
            </div>
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('metodo.comparison.title') }}</h2>
            <p class="mt-2 text-lg text-wc-text-secondary">{{ __('metodo.comparison.subtitle') }}</p>

            {{-- Comparison Table: Desktop --}}
            <div class="mt-12 hidden sm:block">
                <div class="overflow-x-auto rounded-xl border border-wc-border">
                    <table class="w-full min-w-[640px] border-collapse">
                        <thead>
                            <tr class="border-b border-wc-border bg-wc-bg/50">
                                <th class="px-6 py-4 text-left text-sm font-medium text-wc-text-tertiary">{{ __('metodo.comparison.col_feature') }}</th>
                                <th class="bg-wc-accent/5 px-6 py-4 text-center text-sm font-semibold text-wc-accent border-x border-wc-accent/20">{{ __('metodo.comparison.col_wellcore') }}</th>
                                <th class="px-6 py-4 text-center text-sm font-medium text-wc-text-tertiary">{{ __('metodo.comparison.col_app') }}</th>
                                <th class="px-6 py-4 text-center text-sm font-medium text-wc-text-tertiary">{{ __('metodo.comparison.col_gym') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-wc-border">
                            @foreach (['r1','r2','r3','r4','r5','r6'] as $i => $rk)
                            @php $delays = [50,100,150,200,250,300]; @endphp
                            <tr data-animate="fadeInUp" style="animation-delay: {{ $delays[$i] }}ms">
                                <td class="px-6 py-4 text-sm text-wc-text">{{ __('metodo.comparison.rows.'.$rk.'.feature') }}</td>
                                <td class="bg-wc-accent/5 px-6 py-4 text-center border-x border-wc-accent/20">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-wc-accent">
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        {{ __('metodo.comparison.rows.'.$rk.'.wellcore') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php $appVal = __('metodo.comparison.rows.'.$rk.'.app'); @endphp
                                    <span class="inline-flex items-center gap-1 text-sm text-wc-text-tertiary">
                                        @if(str_starts_with($appVal, 'No'))
                                            <svg class="h-4 w-4 shrink-0 text-red-400/70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                        @endif
                                        {{ $appVal }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php $gymVal = __('metodo.comparison.rows.'.$rk.'.gym'); @endphp
                                    <span class="inline-flex items-center gap-1 text-sm text-wc-text-tertiary">
                                        @if($gymVal === 'No')
                                            <svg class="h-4 w-4 shrink-0 text-red-400/70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                        @endif
                                        {{ $gymVal }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Comparison Table: Mobile (stacked cards) --}}
            <div class="mt-12 space-y-4 sm:hidden">
                @foreach (['r1','r2','r3','r4','r5','r6'] as $rk)
                    @php
                        $appVal = __('metodo.comparison.rows.'.$rk.'.app');
                        $gymVal = __('metodo.comparison.rows.'.$rk.'.gym');
                    @endphp
                    <div class="rounded-xl border border-wc-border bg-wc-bg p-5">
                        <p class="text-sm font-semibold text-wc-text">{{ __('metodo.comparison.rows.'.$rk.'.feature') }}</p>
                        <div class="mt-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-wc-text-tertiary">{{ __('metodo.comparison.col_wellcore') }}</span>
                                <span class="inline-flex items-center gap-1 text-sm font-medium text-wc-accent">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ __('metodo.comparison.rows.'.$rk.'.wellcore') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-wc-text-tertiary">{{ __('metodo.comparison.col_app') }}</span>
                                <span class="inline-flex items-center gap-1 text-sm text-wc-text-tertiary">
                                    @if(str_starts_with($appVal, 'No'))
                                        <svg class="h-3.5 w-3.5 shrink-0 text-red-400/70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    @endif
                                    {{ $appVal }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-wc-text-tertiary">{{ __('metodo.comparison.col_gym') }}</span>
                                <span class="text-sm text-wc-text-tertiary">{{ $gymVal }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-xs text-wc-text-tertiary">
                {{ __('metodo.comparison.footnote') }}
            </p>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Section 04: FAQ --}}
    <section class="bg-wc-bg" x-data="{ active: null }">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-4 flex items-center gap-3">
                <span class="font-data text-sm font-semibold text-wc-accent">04</span>
                <div class="h-px flex-1 bg-wc-border"></div>
            </div>
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('metodo.faq.title') }}</h2>
            <p class="mt-2 text-lg text-wc-text-secondary">
                {{ __('metodo.faq.subtitle') }}
            </p>

            <div class="mt-12 max-w-3xl divide-y divide-wc-border">
                {{-- Q1 --}}
                <div class="scroll-reveal" data-animate="fadeInUp" style="animation-delay: 50ms">
                    <button x-on:click="active = active === 1 ? null : 1" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('metodo.faq.q1.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300 ease-in-out" :class="{ 'rotate-180': active === 1 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">
                                {{ __('metodo.faq.q1.answer') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Q2 --}}
                <div class="scroll-reveal" data-animate="fadeInUp" style="animation-delay: 100ms">
                    <button x-on:click="active = active === 2 ? null : 2" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('metodo.faq.q2.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300 ease-in-out" :class="{ 'rotate-180': active === 2 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">
                                {{ __('metodo.faq.q2.answer') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Q3 --}}
                <div class="scroll-reveal" data-animate="fadeInUp" style="animation-delay: 150ms">
                    <button x-on:click="active = active === 3 ? null : 3" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('metodo.faq.q3.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300 ease-in-out" :class="{ 'rotate-180': active === 3 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">
                                {{ __('metodo.faq.q3.answer') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Q4 --}}
                <div class="scroll-reveal" data-animate="fadeInUp" style="animation-delay: 200ms">
                    <button x-on:click="active = active === 4 ? null : 4" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('metodo.faq.q4.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300 ease-in-out" :class="{ 'rotate-180': active === 4 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">
                                {{ __('metodo.faq.q4.answer') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <a href="{{ route('faq') }}" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                    {{ __('metodo.faq.see_all') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Final CTA --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/8 via-wc-bg-tertiary to-wc-bg-secondary pointer-events-none"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="relative overflow-hidden rounded-2xl border border-wc-accent/20 bg-wc-bg p-10 shadow-2xl sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-wc-accent/3 pointer-events-none"></div>
                <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-wc-accent/5 blur-3xl pointer-events-none" aria-hidden="true"></div>
                <div class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-wc-accent/3 blur-2xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative text-center">
                    <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">{{ __('metodo.cta.label') }}</p>
                    <h2 class="mt-4 font-display text-3xl tracking-wide text-wc-text sm:text-5xl">{{ __('metodo.cta.title') }}</h2>
                    <p class="mx-auto mt-6 max-w-lg text-wc-text-secondary">
                        {{ __('metodo.cta.description') }}
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('planes') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                            {{ __('metodo.cta.btn_primary') }}
                        </a>
                        <a href="{{ route('proceso') }}" class="btn-press inline-flex items-center justify-center rounded-full border border-wc-border px-8 py-3.5 text-base font-semibold text-wc-text hover:bg-wc-bg-secondary hover:border-wc-accent/30 transition-colors">
                            {{ __('metodo.cta.btn_secondary') }}
                        </a>
                    </div>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-wc-text-tertiary">
                        <span>{{ __('metodo.cta.trust1') }}</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span>{{ __('metodo.cta.trust2') }}</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span>{{ __('metodo.cta.trust3') }}</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span>{{ __('metodo.cta.trust4') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
